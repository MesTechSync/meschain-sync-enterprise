interface TrendyolConfig {
  apiKey: string;
  apiSecret: string;
  supplierId: string;
  baseURL: string;
  environment: 'sandbox' | 'production';
}

interface TrendyolProduct {
  id: string;
  barcode: string;
  title: string;
  brand: string;
  category: string;
  price: number;
  discountedPrice?: number;
  stock: number;
  images: string[];
  description: string;
  attributes: Record<string, any>;
  status: 'active' | 'passive' | 'rejected';
  lastUpdateDate: string;
}

interface TrendyolOrder {
  orderNumber: string;
  orderDate: string;
  status: 'Created' | 'Picking' | 'Picked' | 'Shipped' | 'Delivered' | 'Cancelled';
  totalPrice: number;
  customerName: string;
  customerEmail: string;
  shippingAddress: any;
  items: TrendyolOrderItem[];
}

interface TrendyolOrderItem {
  productId: string;
  productName: string;
  quantity: number;
  price: number;
  barcode: string;
}

interface TrendyolAPIResponse<T = any> {
  success: boolean;
  data: T | null;
  message?: string;
  error?: string;
  totalElements?: number;
  totalPages?: number;
}

class TrendyolAPIService {
  private config: TrendyolConfig;
  private cache: Map<string, { data: any; timestamp: number }> = new Map();
  private readonly CACHE_DURATION = 2 * 60 * 1000; // 2 dakika (Trendyol için kısa cache)

  constructor() {
    this.config = {
      apiKey: process.env.REACT_APP_TRENDYOL_API_KEY || '',
      apiSecret: process.env.REACT_APP_TRENDYOL_API_SECRET || '',
      supplierId: process.env.REACT_APP_TRENDYOL_SUPPLIER_ID || '',
      baseURL: process.env.REACT_APP_TRENDYOL_API_BASE || 'https://api.trendyol.com',
      environment: (process.env.REACT_APP_TRENDYOL_ENV as 'sandbox' | 'production') || 'sandbox'
    };
  }

  private generateAuthHeader(): string {
    const timestamp = Date.now().toString();
    const signature = btoa(`${this.config.apiKey}:${this.config.apiSecret}:${timestamp}`);
    return `Basic ${signature}`;
  }

  private async request<T = any>(
    endpoint: string,
    options: RequestInit = {}
  ): Promise<TrendyolAPIResponse<T>> {
    const url = `${this.config.baseURL}${endpoint}`;
    
    const defaultHeaders = {
      'Content-Type': 'application/json',
      'Authorization': this.generateAuthHeader(),
      'User-Agent': 'MesChain-Sync/3.1.0',
      'X-Supplier-Id': this.config.supplierId,
    };

    try {
      const response = await fetch(url, {
        ...options,
        headers: {
          ...defaultHeaders,
          ...options.headers,
        },
      });

      if (!response.ok) {
        // Trendyol API error handling
        const errorData = await response.json().catch(() => ({}));
        throw new Error(`Trendyol API Error ${response.status}: ${errorData.message || response.statusText}`);
      }

      const data = await response.json();
      
      return {
        success: true,
        data: data.content || data,
        totalElements: data.totalElements,
        totalPages: data.totalPages,
        message: 'Success'
      };
    } catch (error) {
      console.error(`Trendyol API Request Error [${endpoint}]:`, error);
      
      // Fallback to demo data if API fails
      const demoData = this.getDemoDataFallback(endpoint);
      
      return {
        success: false,
        data: demoData,
        error: error instanceof Error ? error.message : 'Trendyol API connection failed'
      };
    }
  }

  private getCachedData<T>(key: string): T | null {
    const cached = this.cache.get(key);
    if (cached && Date.now() - cached.timestamp < this.CACHE_DURATION) {
      return cached.data;
    }
    return null;
  }

  private setCachedData<T>(key: string, data: T): void {
    this.cache.set(key, {
      data,
      timestamp: Date.now()
    });
  }

  private getDemoDataFallback(endpoint: string): any {
    // Demo data fallback when API is offline
    if (endpoint.includes('/products')) {
      return {
        products: [
          {
            id: 'demo-1',
            title: 'Demo Ürün 1 (API Offline)',
            brand: 'Demo Brand',
            price: 99.99,
            stock: 50,
            status: 'active',
            images: ['/demo-product-1.jpg']
          },
          {
            id: 'demo-2', 
            title: 'Demo Ürün 2 (API Offline)',
            brand: 'Demo Brand',
            price: 149.99,
            stock: 25,
            status: 'active',
            images: ['/demo-product-2.jpg']
          }
        ]
      };
    }
    
    if (endpoint.includes('/orders')) {
      return {
        orders: [
          {
            orderNumber: 'DEMO-001',
            orderDate: new Date().toISOString(),
            status: 'Created',
            totalPrice: 199.98,
            customerName: 'Demo Müşteri (API Offline)'
          }
        ]
      };
    }

    return null;
  }

  // Product Management
  async getProducts(filters?: {
    page?: number;
    size?: number;
    approved?: boolean;
    barcode?: string;
  }): Promise<TrendyolAPIResponse<{ products: TrendyolProduct[] }>> {
    const cacheKey = `products-${JSON.stringify(filters || {})}`;
    const cached = this.getCachedData<{ products: TrendyolProduct[] }>(cacheKey);
    
    if (cached) {
      return { success: true, data: cached };
    }

    const queryParams = new URLSearchParams();
    if (filters?.page) queryParams.append('page', filters.page.toString());
    if (filters?.size) queryParams.append('size', filters.size.toString());
    if (filters?.approved !== undefined) queryParams.append('approved', filters.approved.toString());
    if (filters?.barcode) queryParams.append('barcode', filters.barcode);

    const endpoint = `/sapigw/suppliers/${this.config.supplierId}/products?${queryParams.toString()}`;
    const response = await this.request<{ products: TrendyolProduct[] }>(endpoint);
    
    if (response.success && response.data) {
      this.setCachedData(cacheKey, response.data);
    }
    
    return response;
  }

  async getProductById(productId: string): Promise<TrendyolAPIResponse<TrendyolProduct>> {
    const endpoint = `/sapigw/suppliers/${this.config.supplierId}/products/${productId}`;
    return this.request<TrendyolProduct>(endpoint);
  }

  async updateProductPrice(productId: string, price: number): Promise<TrendyolAPIResponse<any>> {
    const endpoint = `/sapigw/suppliers/${this.config.supplierId}/products/price-and-inventory`;
    
    return this.request(endpoint, {
      method: 'POST',
      body: JSON.stringify({
        items: [{
          barcode: productId,
          quantity: 0, // Sadece fiyat güncellemesi
          salePrice: price,
          listPrice: price
        }]
      })
    });
  }

  async updateProductStock(productId: string, stock: number): Promise<TrendyolAPIResponse<any>> {
    const endpoint = `/sapigw/suppliers/${this.config.supplierId}/products/price-and-inventory`;
    
    return this.request(endpoint, {
      method: 'POST',
      body: JSON.stringify({
        items: [{
          barcode: productId,
          quantity: stock
        }]
      })
    });
  }

  // Order Management
  async getOrders(filters?: {
    status?: string;
    startDate?: string;
    endDate?: string;
    page?: number;
    size?: number;
  }): Promise<TrendyolAPIResponse<{ orders: TrendyolOrder[] }>> {
    const queryParams = new URLSearchParams();
    if (filters?.status) queryParams.append('status', filters.status);
    if (filters?.startDate) queryParams.append('startDate', filters.startDate);
    if (filters?.endDate) queryParams.append('endDate', filters.endDate);
    if (filters?.page) queryParams.append('page', filters.page.toString());
    if (filters?.size) queryParams.append('size', filters.size.toString());

    const endpoint = `/sapigw/suppliers/${this.config.supplierId}/orders?${queryParams.toString()}`;
    return this.request<{ orders: TrendyolOrder[] }>(endpoint);
  }

  async getOrderById(orderNumber: string): Promise<TrendyolAPIResponse<TrendyolOrder>> {
    const endpoint = `/sapigw/suppliers/${this.config.supplierId}/orders/${orderNumber}`;
    return this.request<TrendyolOrder>(endpoint);
  }

  async updateOrderStatus(orderNumber: string, status: string): Promise<TrendyolAPIResponse<any>> {
    const endpoint = `/sapigw/suppliers/${this.config.supplierId}/orders/${orderNumber}/status`;
    
    return this.request(endpoint, {
      method: 'PUT',
      body: JSON.stringify({ status })
    });
  }

  // Analytics & Reports
  async getSalesAnalytics(filters?: {
    startDate?: string;
    endDate?: string;
  }): Promise<TrendyolAPIResponse<any>> {
    const queryParams = new URLSearchParams();
    if (filters?.startDate) queryParams.append('startDate', filters.startDate);
    if (filters?.endDate) queryParams.append('endDate', filters.endDate);

    const endpoint = `/sapigw/suppliers/${this.config.supplierId}/finance/settlement-reports?${queryParams.toString()}`;
    return this.request(endpoint);
  }

  async getPerformanceMetrics(): Promise<TrendyolAPIResponse<any>> {
    const endpoint = `/sapigw/suppliers/${this.config.supplierId}/performance`;
    return this.request(endpoint);
  }

  // Webhook Management
  async registerWebhook(webhookUrl: string, events: string[]): Promise<TrendyolAPIResponse<any>> {
    const endpoint = `/sapigw/suppliers/${this.config.supplierId}/webhooks`;
    
    return this.request(endpoint, {
      method: 'POST',
      body: JSON.stringify({
        url: webhookUrl,
        events: events
      })
    });
  }

  // Health Check
  async testConnection(): Promise<TrendyolAPIResponse<any>> {
    try {
      const response = await this.getProducts({ page: 0, size: 1 });
      return {
        success: response.success,
        data: {
          status: response.success ? 'connected' : 'failed',
          message: response.success ? 'Trendyol API bağlantısı başarılı' : 'Trendyol API bağlantısı başarısız',
          timestamp: new Date().toISOString()
        }
      };
    } catch (error) {
      return {
        success: false,
        data: {
          status: 'failed',
          message: 'Trendyol API bağlantı testi başarısız',
          error: error instanceof Error ? error.message : 'Unknown error'
        }
      };
    }
  }

  // Cache Management
  clearCache(): void {
    this.cache.clear();
  }

  getCacheSize(): number {
    return this.cache.size;
  }

  // Configuration
  updateConfig(newConfig: Partial<TrendyolConfig>): void {
    this.config = { ...this.config, ...newConfig };
  }

  getConfig(): TrendyolConfig {
    return { ...this.config };
  }
}

// Singleton instance
export const trendyolAPI = new TrendyolAPIService();
export default trendyolAPI;
export type { TrendyolProduct, TrendyolOrder, TrendyolAPIResponse, TrendyolConfig }; 