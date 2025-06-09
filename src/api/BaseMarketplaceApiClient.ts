/**
 * Base Marketplace API Client Architecture
 * MezBjen Team - Marketplace Expansion Mission
 * 
 * @package MesChain-Sync Enterprise
 * @version 3.0.7.0 - MEZBJEN Marketplace Expansion
 * @author MezBjen Development Team
 * @date 2025-06-09
 */

import axios, { AxiosInstance, AxiosRequestConfig, AxiosResponse } from 'axios';
import { EventEmitter } from 'events';

// ===============================================
// TYPE DEFINITIONS
// ===============================================

export interface AuthConfig {
  clientId?: string;
  clientSecret?: string;
  apiKey?: string;
  storeId?: string;
  redirectUri?: string;
}

export interface AuthToken {
  accessToken: string;
  refreshToken?: string;
  expiresAt: Date;
  tokenType: string;
}

export interface RateLimitConfig {
  maxRequests: number;
  timeWindow: number; // in milliseconds
  retryAfter?: number;
}

export interface ProductData {
  sku: string;
  title: string;
  description: string;
  price: number;
  quantity: number;
  categoryId: string;
  attributes: Record<string, any>;
  images: string[];
}

export interface Product extends ProductData {
  id: string;
  marketplaceId: string;
  status: 'active' | 'inactive' | 'pending';
  createdAt: Date;
  updatedAt: Date;
}

export interface OrderData {
  id: string;
  status: OrderStatus;
  customerId: string;
  items: OrderItem[];
  totalAmount: number;
  shippingAddress: Address;
  createdAt: Date;
}

export interface OrderItem {
  productId: string;
  sku: string;
  quantity: number;
  price: number;
}

export interface Address {
  name: string;
  street: string;
  city: string;
  state: string;
  zipCode: string;
  country: string;
}

export enum OrderStatus {
  PENDING = 'pending',
  CONFIRMED = 'confirmed',
  SHIPPED = 'shipped',
  DELIVERED = 'delivered',
  CANCELLED = 'cancelled'
}

// ===============================================
// RATE LIMITER CLASS
// ===============================================

export class RateLimiter {
  private requests: Date[] = [];
  private readonly maxRequests: number;
  private readonly timeWindow: number;

  constructor(config: RateLimitConfig) {
    this.maxRequests = config.maxRequests;
    this.timeWindow = config.timeWindow;
  }

  async checkLimit(): Promise<boolean> {
    const now = new Date();
    const cutoff = new Date(now.getTime() - this.timeWindow);
    
    // Remove old requests
    this.requests = this.requests.filter(req => req > cutoff);
    
    if (this.requests.length >= this.maxRequests) {
      return false; // Rate limit exceeded
    }
    
    this.requests.push(now);
    return true;
  }

  async waitForAvailability(): Promise<void> {
    while (!(await this.checkLimit())) {
      await new Promise(resolve => setTimeout(resolve, 1000));
    }
  }

  getStatus(): { remaining: number; resetTime: Date } {
    const now = new Date();
    const cutoff = new Date(now.getTime() - this.timeWindow);
    this.requests = this.requests.filter(req => req > cutoff);
    
    return {
      remaining: this.maxRequests - this.requests.length,
      resetTime: this.requests.length > 0 
        ? new Date(this.requests[0].getTime() + this.timeWindow)
        : now
    };
  }
}

// ===============================================
// BASE MARKETPLACE API CLIENT
// ===============================================

export abstract class BaseMarketplaceApiClient extends EventEmitter {
  protected readonly baseUrl: string;
  protected readonly auth: AuthConfig;
  protected readonly rateLimiter: RateLimiter;
  protected readonly httpClient: AxiosInstance;
  protected authToken?: AuthToken;

  constructor(
    baseUrl: string, 
    authConfig: AuthConfig, 
    rateLimitConfig: RateLimitConfig
  ) {
    super();
    this.baseUrl = baseUrl;
    this.auth = authConfig;
    this.rateLimiter = new RateLimiter(rateLimitConfig);
    
    this.httpClient = axios.create({
      baseURL: this.baseUrl,
      timeout: 30000,
      headers: {
        'Content-Type': 'application/json',
        'User-Agent': 'MesChain-Sync-Enterprise/3.0.7'
      }
    });

    this.setupInterceptors();
  }

  // ===============================================
  // ABSTRACT METHODS (Must be implemented by subclasses)
  // ===============================================

  abstract authenticate(): Promise<AuthToken>;
  abstract refreshToken(): Promise<AuthToken>;
  abstract getProducts(params?: any): Promise<Product[]>;
  abstract getProduct(id: string): Promise<Product>;
  abstract createProduct(product: ProductData): Promise<Product>;
  abstract updateProduct(id: string, product: Partial<ProductData>): Promise<Product>;
  abstract deleteProduct(id: string): Promise<boolean>;
  abstract getOrders(params?: any): Promise<OrderData[]>;
  abstract getOrder(id: string): Promise<OrderData>;
  abstract updateOrderStatus(id: string, status: OrderStatus): Promise<OrderData>;

  // ===============================================
  // COMMON METHODS
  // ===============================================

  protected setupInterceptors(): void {
    // Request interceptor
    this.httpClient.interceptors.request.use(
      async (config) => {
        // Wait for rate limiting
        await this.rateLimiter.waitForAvailability();
        
        // Add authentication
        if (this.authToken) {
          config.headers.Authorization = `Bearer ${this.authToken.accessToken}`;
        }

        this.emit('requestStart', { url: config.url, method: config.method });
        return config;
      },
      (error) => {
        this.emit('requestError', error);
        return Promise.reject(error);
      }
    );

    // Response interceptor
    this.httpClient.interceptors.response.use(
      (response) => {
        this.emit('requestSuccess', { 
          url: response.config.url, 
          status: response.status,
          data: response.data 
        });
        return response;
      },
      async (error) => {
        this.emit('requestError', error);

        // Handle authentication errors
        if (error.response?.status === 401) {
          try {
            await this.refreshToken();
            // Retry the original request
            return this.httpClient.request(error.config);
          } catch (refreshError) {
            this.emit('authenticationFailed', refreshError);
            throw refreshError;
          }
        }

        // Handle rate limiting
        if (error.response?.status === 429) {
          const retryAfter = error.response.headers['retry-after'] || 60;
          this.emit('rateLimitExceeded', { retryAfter });
          await new Promise(resolve => setTimeout(resolve, retryAfter * 1000));
          return this.httpClient.request(error.config);
        }

        throw error;
      }
    );
  }

  protected async makeRequest<T>(
    method: string,
    endpoint: string,
    data?: any,
    config?: AxiosRequestConfig
  ): Promise<T> {
    try {
      const response: AxiosResponse<T> = await this.httpClient.request({
        method,
        url: endpoint,
        data,
        ...config
      });
      
      return response.data;
    } catch (error) {
      this.handleError(error);
      throw error;
    }
  }

  protected handleError(error: any): void {
    const errorInfo = {
      message: error.message,
      status: error.response?.status,
      data: error.response?.data,
      url: error.config?.url,
      method: error.config?.method
    };

    this.emit('error', errorInfo);
    
    // Log error for monitoring
    console.error('Marketplace API Error:', errorInfo);
  }

  // ===============================================
  // UTILITY METHODS
  // ===============================================

  async isAuthenticated(): Promise<boolean> {
    if (!this.authToken) return false;
    
    const now = new Date();
    const bufferTime = 5 * 60 * 1000; // 5 minutes buffer
    
    return this.authToken.expiresAt.getTime() > (now.getTime() + bufferTime);
  }

  async ensureAuthenticated(): Promise<void> {
    if (!(await this.isAuthenticated())) {
      this.authToken = await this.authenticate();
    }
  }

  getRateLimitStatus(): { remaining: number; resetTime: Date } {
    return this.rateLimiter.getStatus();
  }

  getBaseUrl(): string {
    return this.baseUrl;
  }

  // ===============================================
  // BULK OPERATIONS
  // ===============================================

  async bulkCreateProducts(products: ProductData[]): Promise<Product[]> {
    const results: Product[] = [];
    const batchSize = 10; // Process in batches to respect rate limits
    
    for (let i = 0; i < products.length; i += batchSize) {
      const batch = products.slice(i, i + batchSize);
      const batchPromises = batch.map(product => this.createProduct(product));
      
      try {
        const batchResults = await Promise.allSettled(batchPromises);
        batchResults.forEach((result, index) => {
          if (result.status === 'fulfilled') {
            results.push(result.value);
          } else {
            this.emit('bulkOperationError', {
              operation: 'createProduct',
              product: batch[index],
              error: result.reason
            });
          }
        });
      } catch (error) {
        this.emit('bulkOperationFailed', { batch, error });
      }
      
      // Small delay between batches
      if (i + batchSize < products.length) {
        await new Promise(resolve => setTimeout(resolve, 1000));
      }
    }
    
    return results;
  }

  async bulkUpdateProducts(updates: Array<{ id: string; data: Partial<ProductData> }>): Promise<Product[]> {
    const results: Product[] = [];
    const batchSize = 10;
    
    for (let i = 0; i < updates.length; i += batchSize) {
      const batch = updates.slice(i, i + batchSize);
      const batchPromises = batch.map(update => 
        this.updateProduct(update.id, update.data)
      );
      
      try {
        const batchResults = await Promise.allSettled(batchPromises);
        batchResults.forEach((result, index) => {
          if (result.status === 'fulfilled') {
            results.push(result.value);
          } else {
            this.emit('bulkOperationError', {
              operation: 'updateProduct',
              update: batch[index],
              error: result.reason
            });
          }
        });
      } catch (error) {
        this.emit('bulkOperationFailed', { batch, error });
      }
      
      if (i + batchSize < updates.length) {
        await new Promise(resolve => setTimeout(resolve, 1000));
      }
    }
    
    return results;
  }
}

export default BaseMarketplaceApiClient;
