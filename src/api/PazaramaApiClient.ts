/**
 * Pazarama Marketplace API Client
 * MezBjen Team - Marketplace Expansion Mission
 * 
 * @package MesChain-Sync Enterprise
 * @version 3.0.7.1 - PAZARAMA Integration
 * @author MezBjen Development Team
 * @date 2025-06-09
 */

import { BaseMarketplaceApiClient, AuthConfig, AuthToken, Product, ProductData, Order, OrderData, RateLimitConfig } from './BaseMarketplaceApiClient';
import axios, { AxiosRequestConfig } from 'axios';

// ===============================================
// PAZARAMA SPECIFIC INTERFACES
// ===============================================

export interface PazaramaConfig extends AuthConfig {
  clientId: string;
  clientSecret: string;
  redirectUri: string;
  storeId?: string;
  environment: 'sandbox' | 'production';
}

export interface PazaramaAuthToken extends AuthToken {
  scope: string[];
  tokenType: 'Bearer';
  expiresIn: number;
  refreshToken: string;
}

export interface PazaramaProduct extends Product {
  pazaramaId: string;
  categoryPath: string;
  brand: string;
  model: string;
  barcode: string;
  variations: PazaramaProductVariation[];
  images: PazaramaProductImage[];
  attributes: Record<string, any>;
  approved: boolean;
  listingStatus: 'ACTIVE' | 'INACTIVE' | 'PENDING' | 'REJECTED';
}

export interface PazaramaProductVariation {
  sku: string;
  price: number;
  discountPrice?: number;
  stock: number;
  attributes: Record<string, string>;
}

export interface PazaramaProductImage {
  url: string;
  order: number;
  isMain: boolean;
}

export interface PazaramaOrder extends Order {
  pazaramaOrderId: string;
  customerInfo: {
    name: string;
    email: string;
    phone: string;
  };
  shippingAddress: {
    fullName: string;
    addressLine1: string;
    addressLine2?: string;
    city: string;
    district: string;
    postalCode: string;
    country: string;
  };
  cargoInfo?: {
    trackingNumber: string;
    cargoCompany: string;
    estimatedDelivery: string;
  };
  paymentMethod: string;
  totalAmount: number;
  commission: number;
}

export interface PazaramaBulkOperationResult {
  success: boolean;
  processedCount: number;
  errorCount: number;
  errors: Array<{
    item: any;
    error: string;
  }>;
}

// ===============================================
// PAZARAMA API CLIENT IMPLEMENTATION
// ===============================================

export class PazaramaApiClient extends BaseMarketplaceApiClient {
  private config: PazaramaConfig;
  private readonly baseUrl: string;
  private readonly rateLimitConfig: RateLimitConfig = {
    requestsPerHour: 1000,
    burstLimit: 50,
    retryAfter: 3600
  };

  constructor(config: PazaramaConfig) {
    super();
    this.config = config;
    this.baseUrl = config.environment === 'production' 
      ? 'https://api.pazarama.com/v1'
      : 'https://sandbox-api.pazarama.com/v1';
    
    // Initialize HTTP client with interceptors
    this.initializeHttpClient();
    
    // Initialize rate limiter
    this.initializeRateLimiter(this.rateLimitConfig);
    
    this.emit('client_initialized', { marketplace: 'PAZARAMA', environment: config.environment });
  }

  /**
   * OAuth 2.0 Authentication Flow
   */
  async authenticate(): Promise<PazaramaAuthToken> {
    try {
      const response = await this.httpClient.post('/oauth/token', {
        grant_type: 'client_credentials',
        client_id: this.config.clientId,
        client_secret: this.config.clientSecret,
        scope: 'product:read product:write order:read order:write inventory:manage'
      });

      const token: PazaramaAuthToken = {
        accessToken: response.data.access_token,
        tokenType: 'Bearer',
        expiresIn: response.data.expires_in,
        refreshToken: response.data.refresh_token,
        scope: response.data.scope.split(' '),
        expiresAt: new Date(Date.now() + (response.data.expires_in * 1000))
      };

      this.currentToken = token;
      this.emit('authentication_success', { marketplace: 'PAZARAMA', tokenExpiry: token.expiresAt });
      
      return token;
    } catch (error) {
      this.emit('authentication_error', { marketplace: 'PAZARAMA', error: error.message });
      throw new Error(`Pazarama authentication failed: ${error.message}`);
    }
  }

  /**
   * Get products with advanced filtering
   */
  async getProducts(params: {
    page?: number;
    limit?: number;
    categoryId?: string;
    status?: string;
    searchTerm?: string;
  } = {}): Promise<PazaramaProduct[]> {
    await this.rateLimiter.waitForAvailability();
    
    try {
      const response = await this.authenticatedRequest('GET', '/products', { params });
      
      const products: PazaramaProduct[] = response.data.products.map(this.mapPazaramaProduct);
      
      this.emit('products_fetched', { 
        marketplace: 'PAZARAMA', 
        count: products.length,
        params 
      });
      
      return products;
    } catch (error) {
      this.emit('products_fetch_error', { marketplace: 'PAZARAMA', error: error.message });
      throw error;
    }
  }

  /**
   * Create new product on Pazarama
   */
  async createProduct(productData: ProductData): Promise<PazaramaProduct> {
    await this.rateLimiter.waitForAvailability();
    
    try {
      const pazaramaProductData = this.transformToP azaramaFormat(productData);
      
      const response = await this.authenticatedRequest('POST', '/products', pazaramaProductData);
      
      const product = this.mapPazaramaProduct(response.data);
      
      this.emit('product_created', { 
        marketplace: 'PAZARAMA', 
        productId: product.id,
        sku: product.sku 
      });
      
      return product;
    } catch (error) {
      this.emit('product_creation_error', { 
        marketplace: 'PAZARAMA', 
        error: error.message,
        productData 
      });
      throw error;
    }
  }

  /**
   * Update existing product
   */
  async updateProduct(productId: string, productData: Partial<ProductData>): Promise<PazaramaProduct> {
    await this.rateLimiter.waitForAvailability();
    
    try {
      const pazaramaUpdateData = this.transformToP azaramaFormat(productData);
      
      const response = await this.authenticatedRequest('PUT', `/products/${productId}`, pazaramaUpdateData);
      
      const product = this.mapPazaramaProduct(response.data);
      
      this.emit('product_updated', { 
        marketplace: 'PAZARAMA', 
        productId: product.id 
      });
      
      return product;
    } catch (error) {
      this.emit('product_update_error', { 
        marketplace: 'PAZARAMA', 
        productId,
        error: error.message 
      });
      throw error;
    }
  }

  /**
   * Delete product from Pazarama
   */
  async deleteProduct(productId: string): Promise<boolean> {
    await this.rateLimiter.waitForAvailability();
    
    try {
      await this.authenticatedRequest('DELETE', `/products/${productId}`);
      
      this.emit('product_deleted', { 
        marketplace: 'PAZARAMA', 
        productId 
      });
      
      return true;
    } catch (error) {
      this.emit('product_deletion_error', { 
        marketplace: 'PAZARAMA', 
        productId,
        error: error.message 
      });
      throw error;
    }
  }

  /**
   * Get orders with advanced filtering
   */
  async getOrders(params: {
    page?: number;
    limit?: number;
    status?: string;
    startDate?: string;
    endDate?: string;
  } = {}): Promise<PazaramaOrder[]> {
    await this.rateLimiter.waitForAvailability();
    
    try {
      const response = await this.authenticatedRequest('GET', '/orders', { params });
      
      const orders: PazaramaOrder[] = response.data.orders.map(this.mapPazaramaOrder);
      
      this.emit('orders_fetched', { 
        marketplace: 'PAZARAMA', 
        count: orders.length,
        params 
      });
      
      return orders;
    } catch (error) {
      this.emit('orders_fetch_error', { marketplace: 'PAZARAMA', error: error.message });
      throw error;
    }
  }

  /**
   * Update order status
   */
  async updateOrderStatus(orderId: string, status: string, trackingInfo?: any): Promise<PazaramaOrder> {
    await this.rateLimiter.waitForAvailability();
    
    try {
      const updateData = {
        status,
        ...(trackingInfo && { cargoInfo: trackingInfo })
      };
      
      const response = await this.authenticatedRequest('PUT', `/orders/${orderId}/status`, updateData);
      
      const order = this.mapPazaramaOrder(response.data);
      
      this.emit('order_status_updated', { 
        marketplace: 'PAZARAMA', 
        orderId,
        newStatus: status 
      });
      
      return order;
    } catch (error) {
      this.emit('order_update_error', { 
        marketplace: 'PAZARAMA', 
        orderId,
        error: error.message 
      });
      throw error;
    }
  }

  /**
   * Bulk product operations
   */
  async bulkCreateProducts(products: ProductData[]): Promise<PazaramaBulkOperationResult> {
    const batchSize = 10;
    let totalProcessed = 0;
    let totalErrors = 0;
    const errors: Array<{ item: any; error: string }> = [];

    for (let i = 0; i < products.length; i += batchSize) {
      const batch = products.slice(i, i + batchSize);
      
      try {
        await this.rateLimiter.waitForAvailability();
        
        const pazaramaBatch = batch.map(this.transformToP azaramaFormat);
        
        const response = await this.authenticatedRequest('POST', '/products/bulk', {
          products: pazaramaBatch
        });
        
        totalProcessed += response.data.successCount || 0;
        
        if (response.data.errors) {
          errors.push(...response.data.errors);
          totalErrors += response.data.errors.length;
        }
        
      } catch (error) {
        batch.forEach(product => {
          errors.push({ item: product, error: error.message });
          totalErrors++;
        });
      }
    }

    const result: PazaramaBulkOperationResult = {
      success: totalErrors === 0,
      processedCount: totalProcessed,
      errorCount: totalErrors,
      errors
    };

    this.emit('bulk_operation_completed', { 
      marketplace: 'PAZARAMA', 
      operation: 'CREATE_PRODUCTS',
      result 
    });

    return result;
  }

  /**
   * Update inventory stock levels
   */
  async updateInventory(sku: string, stock: number): Promise<boolean> {
    await this.rateLimiter.waitForAvailability();
    
    try {
      await this.authenticatedRequest('PUT', `/inventory/${sku}`, { stock });
      
      this.emit('inventory_updated', { 
        marketplace: 'PAZARAMA', 
        sku,
        newStock: stock 
      });
      
      return true;
    } catch (error) {
      this.emit('inventory_update_error', { 
        marketplace: 'PAZARAMA', 
        sku,
        error: error.message 
      });
      throw error;
    }
  }

  /**
   * Get marketplace categories
   */
  async getCategories(parentId?: string): Promise<any[]> {
    await this.rateLimiter.waitForAvailability();
    
    try {
      const params = parentId ? { parentId } : {};
      const response = await this.authenticatedRequest('GET', '/categories', { params });
      
      this.emit('categories_fetched', { 
        marketplace: 'PAZARAMA', 
        count: response.data.categories.length 
      });
      
      return response.data.categories;
    } catch (error) {
      this.emit('categories_fetch_error', { marketplace: 'PAZARAMA', error: error.message });
      throw error;
    }
  }

  // ===============================================
  // PRIVATE HELPER METHODS
  // ===============================================

  private mapPazaramaProduct(pazaramaData: any): PazaramaProduct {
    return {
      id: pazaramaData.id,
      pazaramaId: pazaramaData.pazaramaId,
      sku: pazaramaData.sku,
      title: pazaramaData.title,
      description: pazaramaData.description,
      price: pazaramaData.price,
      discountPrice: pazaramaData.discountPrice,
      stock: pazaramaData.stock,
      categoryPath: pazaramaData.categoryPath,
      brand: pazaramaData.brand,
      model: pazaramaData.model,
      barcode: pazaramaData.barcode,
      variations: pazaramaData.variations || [],
      images: pazaramaData.images || [],
      attributes: pazaramaData.attributes || {},
      approved: pazaramaData.approved,
      listingStatus: pazaramaData.listingStatus,
      createdAt: new Date(pazaramaData.createdAt),
      updatedAt: new Date(pazaramaData.updatedAt)
    };
  }

  private mapPazaramaOrder(pazaramaData: any): PazaramaOrder {
    return {
      id: pazaramaData.id,
      pazaramaOrderId: pazaramaData.pazaramaOrderId,
      status: pazaramaData.status,
      items: pazaramaData.items,
      totalAmount: pazaramaData.totalAmount,
      commission: pazaramaData.commission,
      customerInfo: pazaramaData.customerInfo,
      shippingAddress: pazaramaData.shippingAddress,
      cargoInfo: pazaramaData.cargoInfo,
      paymentMethod: pazaramaData.paymentMethod,
      createdAt: new Date(pazaramaData.createdAt),
      updatedAt: new Date(pazaramaData.updatedAt)
    };
  }

  private transformToP azaramaFormat(productData: any): any {
    return {
      title: productData.title,
      description: productData.description,
      categoryId: productData.categoryId,
      brand: productData.brand,
      model: productData.model,
      barcode: productData.barcode,
      sku: productData.sku,
      price: productData.price,
      discountPrice: productData.discountPrice,
      stock: productData.stock,
      images: productData.images,
      attributes: productData.attributes,
      variations: productData.variations
    };
  }

  /**
   * Initialize HTTP client with Pazarama-specific configuration
   */
  private initializeHttpClient(): void {
    this.httpClient = axios.create({
      baseURL: this.baseUrl,
      timeout: 30000,
      headers: {
        'Content-Type': 'application/json',
        'User-Agent': 'MesChain-Sync-Enterprise/3.0.7',
        'Accept': 'application/json'
      }
    });

    // Request interceptor for authentication
    this.httpClient.interceptors.request.use(
      (config) => {
        if (this.currentToken) {
          config.headers.Authorization = `Bearer ${this.currentToken.accessToken}`;
        }
        
        this.emit('api_request', { 
          marketplace: 'PAZARAMA',
          method: config.method?.toUpperCase(),
          url: config.url 
        });
        
        return config;
      },
      (error) => {
        this.emit('request_error', { marketplace: 'PAZARAMA', error: error.message });
        return Promise.reject(error);
      }
    );

    // Response interceptor for error handling
    this.httpClient.interceptors.response.use(
      (response) => {
        this.emit('api_response', { 
          marketplace: 'PAZARAMA',
          status: response.status,
          url: response.config.url 
        });
        return response;
      },
      async (error) => {
        if (error.response?.status === 401 && this.currentToken) {
          // Token expired, try to refresh
          try {
            await this.authenticate();
            // Retry the original request
            return this.httpClient.request(error.config);
          } catch (refreshError) {
            this.emit('token_refresh_failed', { 
              marketplace: 'PAZARAMA', 
              error: refreshError.message 
            });
          }
        }
        
        this.emit('api_error', { 
          marketplace: 'PAZARAMA',
          status: error.response?.status,
          message: error.message,
          url: error.config?.url 
        });
        
        return Promise.reject(error);
      }
    );
  }
}

export default PazaramaApiClient;
