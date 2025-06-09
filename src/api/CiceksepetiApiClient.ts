/**
 * Çiçeksepeti Marketplace API Client
 * MezBjen Team - Marketplace Expansion Mission
 * 
 * @package MesChain-Sync Enterprise
 * @version 3.0.7.2 - ÇIÇEKSEPETI Integration
 * @author MezBjen Development Team
 * @date 2025-06-09
 */

import { BaseMarketplaceApiClient, AuthConfig, AuthToken, Product, ProductData, Order, OrderData, RateLimitConfig } from './BaseMarketplaceApiClient';
import axios, { AxiosRequestConfig } from 'axios';

// ===============================================
// ÇIÇEKSEPETI SPECIFIC INTERFACES
// ===============================================

export interface CiceksepetiConfig extends AuthConfig {
  apiToken: string;
  storeId: string;
  webhookSecret?: string;
  environment: 'sandbox' | 'production';
}

export interface CiceksepetiAuthToken extends AuthToken {
  apiKey: string;
  storeId: string;
  permissions: string[];
}

export interface CiceksepetiProduct extends Product {
  ciceksepetiId: string;
  categoryId: string;
  categoryName: string;
  brand: string;
  model: string;
  barcode: string;
  mainImage: string;
  additionalImages: string[];
  attributes: CiceksepetiProductAttribute[];
  variants: CiceksepetiProductVariant[];
  seoInfo: {
    metaTitle?: string;
    metaDescription?: string;
    keywords?: string[];
  };
  dimensions: {
    weight?: number;
    width?: number;
    height?: number;
    depth?: number;
  };
  approvalStatus: 'PENDING' | 'APPROVED' | 'REJECTED' | 'NEEDS_UPDATE';
  visibility: 'VISIBLE' | 'HIDDEN' | 'OUT_OF_STOCK';
}

export interface CiceksepetiProductAttribute {
  attributeId: string;
  attributeName: string;
  value: string;
  isRequired: boolean;
}

export interface CiceksepetiProductVariant {
  variantId: string;
  sku: string;
  price: number;
  discountPrice?: number;
  stock: number;
  barcode?: string;
  attributes: Record<string, string>;
}

export interface CiceksepetiOrder extends Order {
  ciceksepetiOrderId: string;
  orderNumber: string;
  customerInfo: {
    customerId: string;
    firstName: string;
    lastName: string;
    email: string;
    phone: string;
  };
  billingAddress: CiceksepetiAddress;
  shippingAddress: CiceksepetiAddress;
  orderItems: CiceksepetiOrderItem[];
  payment: {
    method: string;
    status: 'PENDING' | 'PAID' | 'CANCELLED' | 'REFUNDED';
    amount: number;
    currency: string;
  };
  shipping: {
    method: string;
    cost: number;
    trackingNumber?: string;
    carrierName?: string;
    estimatedDelivery?: string;
  };
  orderDate: Date;
  totalAmount: number;
  taxAmount: number;
  discountAmount: number;
  commissionRate: number;
}

export interface CiceksepetiAddress {
  fullName: string;
  company?: string;
  addressLine1: string;
  addressLine2?: string;
  city: string;
  district: string;
  postalCode: string;
  country: string;
  phone?: string;
}

export interface CiceksepetiOrderItem {
  orderItemId: string;
  productId: string;
  variantId?: string;
  sku: string;
  productName: string;
  quantity: number;
  unitPrice: number;
  totalPrice: number;
  taxRate: number;
  commissionRate: number;
}

export interface CiceksepetiCategory {
  id: string;
  name: string;
  parentId?: string;
  level: number;
  isActive: boolean;
  attributes: CiceksepetiCategoryAttribute[];
}

export interface CiceksepetiCategoryAttribute {
  id: string;
  name: string;
  type: 'TEXT' | 'NUMBER' | 'SELECT' | 'MULTI_SELECT' | 'BOOLEAN';
  isRequired: boolean;
  options?: string[];
}

export interface CiceksepetiStockUpdateResult {
  success: boolean;
  updatedItems: Array<{
    sku: string;
    oldStock: number;
    newStock: number;
  }>;
  errors: Array<{
    sku: string;
    error: string;
  }>;
}

// ===============================================
// ÇIÇEKSEPETI API CLIENT IMPLEMENTATION
// ===============================================

export class CiceksepetiApiClient extends BaseMarketplaceApiClient {
  private config: CiceksepetiConfig;
  private readonly baseUrl: string;
  private readonly rateLimitConfig: RateLimitConfig = {
    requestsPerHour: 2000,
    burstLimit: 100,
    retryAfter: 1800
  };

  constructor(config: CiceksepetiConfig) {
    super();
    this.config = config;
    this.baseUrl = config.environment === 'production' 
      ? 'https://api.ciceksepeti.com/v2'
      : 'https://sandbox-api.ciceksepeti.com/v2';
    
    // Initialize HTTP client with interceptors
    this.initializeHttpClient();
    
    // Initialize rate limiter
    this.initializeRateLimiter(this.rateLimitConfig);
    
    this.emit('client_initialized', { marketplace: 'CICEKSEPETI', environment: config.environment });
  }

  /**
   * Token-based Authentication
   */
  async authenticate(): Promise<CiceksepetiAuthToken> {
    try {
      // Çiçeksepeti uses bearer token authentication
      const response = await this.httpClient.get('/seller/auth/validate', {
        headers: {
          'Authorization': `Bearer ${this.config.apiToken}`,
          'X-Store-Id': this.config.storeId
        }
      });

      const token: CiceksepetiAuthToken = {
        accessToken: this.config.apiToken,
        apiKey: this.config.apiToken,
        storeId: this.config.storeId,
        permissions: response.data.permissions || [],
        expiresAt: new Date(Date.now() + (24 * 60 * 60 * 1000)) // 24 hours
      };

      this.currentToken = token;
      this.emit('authentication_success', { marketplace: 'CICEKSEPETI', storeId: this.config.storeId });
      
      return token;
    } catch (error) {
      this.emit('authentication_error', { marketplace: 'CICEKSEPETI', error: error.message });
      throw new Error(`Çiçeksepeti authentication failed: ${error.message}`);
    }
  }

  /**
   * Get seller products with filtering options
   */
  async getProducts(params: {
    page?: number;
    limit?: number;
    categoryId?: string;
    status?: 'ACTIVE' | 'INACTIVE' | 'PENDING';
    searchTerm?: string;
    modifiedAfter?: string;
  } = {}): Promise<CiceksepetiProduct[]> {
    await this.rateLimiter.waitForAvailability();
    
    try {
      const response = await this.authenticatedRequest('GET', '/seller/products', { params });
      
      const products: CiceksepetiProduct[] = response.data.products.map(this.mapCiceksepetiProduct);
      
      this.emit('products_fetched', { 
        marketplace: 'CICEKSEPETI', 
        count: products.length,
        params 
      });
      
      return products;
    } catch (error) {
      this.emit('products_fetch_error', { marketplace: 'CICEKSEPETI', error: error.message });
      throw error;
    }
  }

  /**
   * Create new product on Çiçeksepeti
   */
  async createProduct(productData: ProductData): Promise<CiceksepetiProduct> {
    await this.rateLimiter.waitForAvailability();
    
    try {
      const ciceksepetiProductData = this.transformToCiceksepetiFormat(productData);
      
      const response = await this.authenticatedRequest('POST', '/seller/products', ciceksepetiProductData);
      
      const product = this.mapCiceksepetiProduct(response.data);
      
      this.emit('product_created', { 
        marketplace: 'CICEKSEPETI', 
        productId: product.id,
        sku: product.sku 
      });
      
      return product;
    } catch (error) {
      this.emit('product_creation_error', { 
        marketplace: 'CICEKSEPETI', 
        error: error.message,
        productData 
      });
      throw error;
    }
  }

  /**
   * Update existing product
   */
  async updateProduct(productId: string, productData: Partial<ProductData>): Promise<CiceksepetiProduct> {
    await this.rateLimiter.waitForAvailability();
    
    try {
      const ciceksepetiUpdateData = this.transformToCiceksepetiFormat(productData);
      
      const response = await this.authenticatedRequest('PUT', `/seller/products/${productId}`, ciceksepetiUpdateData);
      
      const product = this.mapCiceksepetiProduct(response.data);
      
      this.emit('product_updated', { 
        marketplace: 'CICEKSEPETI', 
        productId: product.id 
      });
      
      return product;
    } catch (error) {
      this.emit('product_update_error', { 
        marketplace: 'CICEKSEPETI', 
        productId,
        error: error.message 
      });
      throw error;
    }
  }

  /**
   * Delete product from Çiçeksepeti
   */
  async deleteProduct(productId: string): Promise<boolean> {
    await this.rateLimiter.waitForAvailability();
    
    try {
      await this.authenticatedRequest('DELETE', `/seller/products/${productId}`);
      
      this.emit('product_deleted', { 
        marketplace: 'CICEKSEPETI', 
        productId 
      });
      
      return true;
    } catch (error) {
      this.emit('product_deletion_error', { 
        marketplace: 'CICEKSEPETI', 
        productId,
        error: error.message 
      });
      throw error;
    }
  }

  /**
   * Get seller orders with filtering
   */
  async getOrders(params: {
    page?: number;
    limit?: number;
    status?: 'NEW' | 'CONFIRMED' | 'SHIPPED' | 'DELIVERED' | 'CANCELLED';
    startDate?: string;
    endDate?: string;
    orderNumber?: string;
  } = {}): Promise<CiceksepetiOrder[]> {
    await this.rateLimiter.waitForAvailability();
    
    try {
      const response = await this.authenticatedRequest('GET', '/seller/orders', { params });
      
      const orders: CiceksepetiOrder[] = response.data.orders.map(this.mapCiceksepetiOrder);
      
      this.emit('orders_fetched', { 
        marketplace: 'CICEKSEPETI', 
        count: orders.length,
        params 
      });
      
      return orders;
    } catch (error) {
      this.emit('orders_fetch_error', { marketplace: 'CICEKSEPETI', error: error.message });
      throw error;
    }
  }

  /**
   * Update order status with shipping information
   */
  async updateOrderStatus(orderId: string, status: string, shippingInfo?: any): Promise<CiceksepetiOrder> {
    await this.rateLimiter.waitForAvailability();
    
    try {
      const updateData = {
        status,
        ...(shippingInfo && { 
          shipping: {
            trackingNumber: shippingInfo.trackingNumber,
            carrierName: shippingInfo.carrierName,
            estimatedDelivery: shippingInfo.estimatedDelivery
          }
        })
      };
      
      const response = await this.authenticatedRequest('PUT', `/seller/orders/${orderId}/status`, updateData);
      
      const order = this.mapCiceksepetiOrder(response.data);
      
      this.emit('order_status_updated', { 
        marketplace: 'CICEKSEPETI', 
        orderId,
        newStatus: status 
      });
      
      return order;
    } catch (error) {
      this.emit('order_update_error', { 
        marketplace: 'CICEKSEPETI', 
        orderId,
        error: error.message 
      });
      throw error;
    }
  }

  /**
   * Confirm order (required for Çiçeksepeti)
   */
  async confirmOrder(orderId: string): Promise<CiceksepetiOrder> {
    await this.rateLimiter.waitForAvailability();
    
    try {
      const response = await this.authenticatedRequest('PUT', `/seller/orders/${orderId}/confirm`);
      
      const order = this.mapCiceksepetiOrder(response.data);
      
      this.emit('order_confirmed', { 
        marketplace: 'CICEKSEPETI', 
        orderId 
      });
      
      return order;
    } catch (error) {
      this.emit('order_confirmation_error', { 
        marketplace: 'CICEKSEPETI', 
        orderId,
        error: error.message 
      });
      throw error;
    }
  }

  /**
   * Bulk stock synchronization
   */
  async bulkUpdateStock(stockUpdates: Array<{ sku: string; stock: number }>): Promise<CiceksepetiStockUpdateResult> {
    await this.rateLimiter.waitForAvailability();
    
    try {
      const response = await this.authenticatedRequest('POST', '/seller/stock/sync', {
        stockUpdates
      });
      
      const result: CiceksepetiStockUpdateResult = {
        success: response.data.success,
        updatedItems: response.data.updated || [],
        errors: response.data.errors || []
      };
      
      this.emit('stock_bulk_updated', { 
        marketplace: 'CICEKSEPETI', 
        totalItems: stockUpdates.length,
        successCount: result.updatedItems.length,
        errorCount: result.errors.length 
      });
      
      return result;
    } catch (error) {
      this.emit('stock_bulk_update_error', { 
        marketplace: 'CICEKSEPETI', 
        error: error.message 
      });
      throw error;
    }
  }

  /**
   * Update single product stock
   */
  async updateStock(sku: string, stock: number): Promise<boolean> {
    await this.rateLimiter.waitForAvailability();
    
    try {
      await this.authenticatedRequest('PUT', `/seller/stock/${sku}`, { stock });
      
      this.emit('stock_updated', { 
        marketplace: 'CICEKSEPETI', 
        sku,
        newStock: stock 
      });
      
      return true;
    } catch (error) {
      this.emit('stock_update_error', { 
        marketplace: 'CICEKSEPETI', 
        sku,
        error: error.message 
      });
      throw error;
    }
  }

  /**
   * Get marketplace categories
   */
  async getCategories(parentId?: string): Promise<CiceksepetiCategory[]> {
    await this.rateLimiter.waitForAvailability();
    
    try {
      const params = parentId ? { parentId } : {};
      const response = await this.authenticatedRequest('GET', '/categories', { params });
      
      const categories: CiceksepetiCategory[] = response.data.categories;
      
      this.emit('categories_fetched', { 
        marketplace: 'CICEKSEPETI', 
        count: categories.length 
      });
      
      return categories;
    } catch (error) {
      this.emit('categories_fetch_error', { marketplace: 'CICEKSEPETI', error: error.message });
      throw error;
    }
  }

  /**
   * Get category attributes for product creation
   */
  async getCategoryAttributes(categoryId: string): Promise<CiceksepetiCategoryAttribute[]> {
    await this.rateLimiter.waitForAvailability();
    
    try {
      const response = await this.authenticatedRequest('GET', `/categories/${categoryId}/attributes`);
      
      const attributes: CiceksepetiCategoryAttribute[] = response.data.attributes;
      
      this.emit('category_attributes_fetched', { 
        marketplace: 'CICEKSEPETI', 
        categoryId,
        attributeCount: attributes.length 
      });
      
      return attributes;
    } catch (error) {
      this.emit('category_attributes_fetch_error', { 
        marketplace: 'CICEKSEPETI', 
        categoryId,
        error: error.message 
      });
      throw error;
    }
  }

  /**
   * Get available brands for category
   */
  async getBrands(categoryId?: string): Promise<Array<{ id: string; name: string }>> {
    await this.rateLimiter.waitForAvailability();
    
    try {
      const params = categoryId ? { categoryId } : {};
      const response = await this.authenticatedRequest('GET', '/brands', { params });
      
      this.emit('brands_fetched', { 
        marketplace: 'CICEKSEPETI', 
        count: response.data.brands.length 
      });
      
      return response.data.brands;
    } catch (error) {
      this.emit('brands_fetch_error', { marketplace: 'CICEKSEPETI', error: error.message });
      throw error;
    }
  }

  // ===============================================
  // PRIVATE HELPER METHODS
  // ===============================================

  private mapCiceksepetiProduct(ciceksepetiData: any): CiceksepetiProduct {
    return {
      id: ciceksepetiData.id,
      ciceksepetiId: ciceksepetiData.ciceksepetiId,
      sku: ciceksepetiData.sku,
      title: ciceksepetiData.title,
      description: ciceksepetiData.description,
      price: ciceksepetiData.price,
      discountPrice: ciceksepetiData.discountPrice,
      stock: ciceksepetiData.stock,
      categoryId: ciceksepetiData.categoryId,
      categoryName: ciceksepetiData.categoryName,
      brand: ciceksepetiData.brand,
      model: ciceksepetiData.model,
      barcode: ciceksepetiData.barcode,
      mainImage: ciceksepetiData.mainImage,
      additionalImages: ciceksepetiData.additionalImages || [],
      attributes: ciceksepetiData.attributes || [],
      variants: ciceksepetiData.variants || [],
      seoInfo: ciceksepetiData.seoInfo || {},
      dimensions: ciceksepetiData.dimensions || {},
      approvalStatus: ciceksepetiData.approvalStatus,
      visibility: ciceksepetiData.visibility,
      createdAt: new Date(ciceksepetiData.createdAt),
      updatedAt: new Date(ciceksepetiData.updatedAt)
    };
  }

  private mapCiceksepetiOrder(ciceksepetiData: any): CiceksepetiOrder {
    return {
      id: ciceksepetiData.id,
      ciceksepetiOrderId: ciceksepetiData.ciceksepetiOrderId,
      orderNumber: ciceksepetiData.orderNumber,
      status: ciceksepetiData.status,
      items: ciceksepetiData.orderItems,
      customerInfo: ciceksepetiData.customerInfo,
      billingAddress: ciceksepetiData.billingAddress,
      shippingAddress: ciceksepetiData.shippingAddress,
      orderItems: ciceksepetiData.orderItems,
      payment: ciceksepetiData.payment,
      shipping: ciceksepetiData.shipping,
      orderDate: new Date(ciceksepetiData.orderDate),
      totalAmount: ciceksepetiData.totalAmount,
      taxAmount: ciceksepetiData.taxAmount,
      discountAmount: ciceksepetiData.discountAmount,
      commissionRate: ciceksepetiData.commissionRate,
      createdAt: new Date(ciceksepetiData.createdAt),
      updatedAt: new Date(ciceksepetiData.updatedAt)
    };
  }

  private transformToCiceksepetiFormat(productData: any): any {
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
      mainImage: productData.images?.[0],
      additionalImages: productData.images?.slice(1),
      attributes: productData.attributes,
      variants: productData.variants,
      seoInfo: productData.seoInfo,
      dimensions: productData.dimensions
    };
  }

  /**
   * Initialize HTTP client with Çiçeksepeti-specific configuration
   */
  private initializeHttpClient(): void {
    this.httpClient = axios.create({
      baseURL: this.baseUrl,
      timeout: 30000,
      headers: {
        'Content-Type': 'application/json',
        'User-Agent': 'MesChain-Sync-Enterprise/3.0.7',
        'Accept': 'application/json',
        'X-Store-Id': this.config.storeId
      }
    });

    // Request interceptor for authentication
    this.httpClient.interceptors.request.use(
      (config) => {
        if (this.currentToken) {
          config.headers.Authorization = `Bearer ${this.currentToken.accessToken}`;
        }
        
        this.emit('api_request', { 
          marketplace: 'CICEKSEPETI',
          method: config.method?.toUpperCase(),
          url: config.url 
        });
        
        return config;
      },
      (error) => {
        this.emit('request_error', { marketplace: 'CICEKSEPETI', error: error.message });
        return Promise.reject(error);
      }
    );

    // Response interceptor for error handling
    this.httpClient.interceptors.response.use(
      (response) => {
        this.emit('api_response', { 
          marketplace: 'CICEKSEPETI',
          status: response.status,
          url: response.config.url 
        });
        return response;
      },
      async (error) => {
        if (error.response?.status === 401) {
          // Token validation failed
          this.emit('authentication_required', { marketplace: 'CICEKSEPETI' });
          throw new Error('Authentication required - please check API token and store ID');
        }
        
        if (error.response?.status === 429) {
          // Rate limit exceeded
          const retryAfter = error.response.headers['retry-after'] || this.rateLimitConfig.retryAfter;
          this.emit('rate_limit_exceeded', { 
            marketplace: 'CICEKSEPETI', 
            retryAfter 
          });
          
          // Auto-retry after rate limit reset
          await new Promise(resolve => setTimeout(resolve, retryAfter * 1000));
          return this.httpClient.request(error.config);
        }
        
        this.emit('api_error', { 
          marketplace: 'CICEKSEPETI',
          status: error.response?.status,
          message: error.message,
          url: error.config?.url 
        });
        
        return Promise.reject(error);
      }
    );
  }
}

export default CiceksepetiApiClient;
