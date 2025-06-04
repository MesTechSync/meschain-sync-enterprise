interface APIConfig {
  baseURL: string;
  token: string;
  userRole: string;
}

interface APIResponse<T = any> {
  success: boolean;
  data: T;
  message?: string;
  error?: string;
}

interface DashboardMetrics {
  total_sales: number;
  active_products: number;
  api_status: 'online' | 'offline' | 'warning';
  marketplace_count: number;
  response_time: number;
}

interface SalesChartData {
  labels: string[];
  values: number[];
}

interface MarketplaceData {
  name: string;
  sales: number;
  status: 'online' | 'offline' | 'warning';
  color: string;
}

interface DropshippingProduct {
  id: string;
  name: string;
  supplier: string;
  basePrice: number;
  sellingPrice: number;
  margin: number;
  stock: number;
  sales: number;
  image: string;
  marketplaces: string[];
}

class APIService {
  private config: APIConfig;
  private cache: Map<string, { data: any; timestamp: number }> = new Map();
  private readonly CACHE_DURATION = 5 * 60 * 1000; // 5 dakika

  constructor() {
    this.config = {
      baseURL: (window as any).MESCHAIN_CONFIG?.apiBase || '/admin/extension/module/meschain',
      token: (window as any).MESCHAIN_CONFIG?.token || '',
      userRole: (window as any).MESCHAIN_CONFIG?.userRole || 'admin'
    };
  }

  private async request<T = any>(
    endpoint: string, 
    options: RequestInit = {}
  ): Promise<APIResponse<T>> {
    const url = `${this.config.baseURL}${endpoint}`;
    
    const defaultHeaders = {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${this.config.token}`,
      'X-User-Role': this.config.userRole,
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
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      const data = await response.json();
      
      return {
        success: true,
        data,
        message: data.message
      };
    } catch (error) {
      console.error(`API Request Error [${endpoint}]:`, error);
      
      return {
        success: false,
        data: null,
        error: error instanceof Error ? error.message : 'Unknown error'
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

  // Dashboard API Methods
  async getDashboardMetrics(): Promise<APIResponse<DashboardMetrics>> {
    const cacheKey = 'dashboard-metrics';
    const cached = this.getCachedData<DashboardMetrics>(cacheKey);
    
    if (cached) {
      return { success: true, data: cached };
    }

    const response = await this.request<DashboardMetrics>('/dashboard/metrics');
    
    if (response.success) {
      this.setCachedData(cacheKey, response.data);
    }
    
    return response;
  }

  async getSalesChartData(): Promise<APIResponse<SalesChartData>> {
    return this.request<SalesChartData>('/dashboard/sales-chart');
  }

  async getMarketplaceData(): Promise<APIResponse<{ marketplaces: MarketplaceData[] }>> {
    return this.request<{ marketplaces: MarketplaceData[] }>('/dashboard/marketplaces');
  }

  // Marketplace API Methods
  async getMarketplaceStatus(marketplace: string): Promise<APIResponse<any>> {
    return this.request(`/marketplace/${marketplace}/status`);
  }

  async syncMarketplace(marketplace: string): Promise<APIResponse<any>> {
    return this.request(`/marketplace/${marketplace}/sync`, {
      method: 'POST'
    });
  }

  async getMarketplaceProducts(marketplace: string, filters?: any): Promise<APIResponse<any>> {
    const queryParams = filters ? `?${new URLSearchParams(filters).toString()}` : '';
    return this.request(`/marketplace/${marketplace}/products${queryParams}`);
  }

  async updateMarketplaceSettings(marketplace: string, settings: any): Promise<APIResponse<any>> {
    return this.request(`/marketplace/${marketplace}/settings`, {
      method: 'PUT',
      body: JSON.stringify(settings)
    });
  }

  // Dropshipping API Methods
  async getDropshippingProducts(filters?: any): Promise<APIResponse<{ products: DropshippingProduct[] }>> {
    const queryParams = filters ? `?${new URLSearchParams(filters).toString()}` : '';
    return this.request<{ products: DropshippingProduct[] }>(`/dropshipping/products${queryParams}`);
  }

  async getDropshippingProfitData(): Promise<APIResponse<any>> {
    return this.request('/dropshipping/profit');
  }

  async getDropshippingOrderData(): Promise<APIResponse<any>> {
    return this.request('/dropshipping/orders');
  }

  async bulkActionProducts(action: string, productIds: string[]): Promise<APIResponse<any>> {
    return this.request('/dropshipping/bulk-action', {
      method: 'POST',
      body: JSON.stringify({ action, productIds })
    });
  }

  async calculateProfit(basePrice: number, sellingPrice: number, marketplace?: string): Promise<APIResponse<any>> {
    return this.request('/dropshipping/calculate-profit', {
      method: 'POST',
      body: JSON.stringify({ basePrice, sellingPrice, marketplace })
    });
  }

  async addProductToDropshipping(productData: any): Promise<APIResponse<any>> {
    return this.request('/dropshipping/add-product', {
      method: 'POST',
      body: JSON.stringify(productData)
    });
  }

  async updateProductPricing(productId: string, pricing: any): Promise<APIResponse<any>> {
    return this.request(`/dropshipping/products/${productId}/pricing`, {
      method: 'PUT',
      body: JSON.stringify(pricing)
    });
  }

  // Order Management API Methods
  async getOrders(filters?: any): Promise<APIResponse<any>> {
    const queryParams = filters ? `?${new URLSearchParams(filters).toString()}` : '';
    return this.request(`/orders${queryParams}`);
  }

  async getOrderDetails(orderId: string): Promise<APIResponse<any>> {
    return this.request(`/orders/${orderId}`);
  }

  async updateOrderStatus(orderId: string, status: string): Promise<APIResponse<any>> {
    return this.request(`/orders/${orderId}/status`, {
      method: 'PUT',
      body: JSON.stringify({ status })
    });
  }

  // User Management API Methods (Super Admin)
  async getUsers(): Promise<APIResponse<any>> {
    return this.request('/users');
  }

  async createUser(userData: any): Promise<APIResponse<any>> {
    return this.request('/users', {
      method: 'POST',
      body: JSON.stringify(userData)
    });
  }

  async updateUserRole(userId: string, role: string): Promise<APIResponse<any>> {
    return this.request(`/users/${userId}/role`, {
      method: 'PUT',
      body: JSON.stringify({ role })
    });
  }

  async deleteUser(userId: string): Promise<APIResponse<any>> {
    return this.request(`/users/${userId}`, {
      method: 'DELETE'
    });
  }

  // System Configuration API Methods
  async getSystemSettings(): Promise<APIResponse<any>> {
    return this.request('/system/settings');
  }

  async updateSystemSettings(settings: any): Promise<APIResponse<any>> {
    return this.request('/system/settings', {
      method: 'PUT',
      body: JSON.stringify(settings)
    });
  }

  async getAPIKeys(): Promise<APIResponse<any>> {
    return this.request('/system/api-keys');
  }

  async updateAPIKey(marketplace: string, apiKey: string): Promise<APIResponse<any>> {
    return this.request(`/system/api-keys/${marketplace}`, {
      method: 'PUT',
      body: JSON.stringify({ apiKey })
    });
  }

  async testAPIConnection(marketplace: string): Promise<APIResponse<any>> {
    return this.request(`/system/test-connection/${marketplace}`, {
      method: 'POST'
    });
  }

  // Reporting API Methods
  async getReports(type: string, filters?: any): Promise<APIResponse<any>> {
    const queryParams = filters ? `?${new URLSearchParams(filters).toString()}` : '';
    return this.request(`/reports/${type}${queryParams}`);
  }

  async exportReport(type: string, format: 'excel' | 'csv', filters?: any): Promise<APIResponse<any>> {
    const queryParams = new URLSearchParams({ format, ...filters }).toString();
    return this.request(`/reports/${type}/export?${queryParams}`);
  }

  // Webhook API Methods
  async getWebhooks(marketplace?: string): Promise<APIResponse<any>> {
    const endpoint = marketplace ? `/webhooks/${marketplace}` : '/webhooks';
    return this.request(endpoint);
  }

  async saveWebhookSettings(marketplace: string, settings: any): Promise<APIResponse<any>> {
    return this.request(`/webhooks/${marketplace}`, {
      method: 'POST',
      body: JSON.stringify(settings)
    });
  }

  async testWebhook(marketplace: string): Promise<APIResponse<any>> {
    return this.request(`/webhooks/${marketplace}/test`, {
      method: 'POST'
    });
  }

  // Utility Methods
  clearCache(): void {
    this.cache.clear();
  }

  getCacheSize(): number {
    return this.cache.size;
  }

  updateConfig(newConfig: Partial<APIConfig>): void {
    this.config = { ...this.config, ...newConfig };
  }

  // Health Check
  async healthCheck(): Promise<APIResponse<any>> {
    return this.request('/health');
  }

  // Real-time Data Subscription (for WebSocket integration)
  subscribeToRealTimeUpdates(callback: (data: any) => void): WebSocket | null {
    try {
      const wsUrl = this.config.baseURL.replace('http', 'ws') + '/ws';
      const ws = new WebSocket(wsUrl);
      
      ws.onopen = () => {
        console.log('WebSocket connection established');
        ws.send(JSON.stringify({
          type: 'auth',
          token: this.config.token,
          role: this.config.userRole
        }));
      };

      ws.onmessage = (event) => {
        try {
          const data = JSON.parse(event.data);
          callback(data);
        } catch (error) {
          console.error('WebSocket message parse error:', error);
        }
      };

      ws.onerror = (error) => {
        console.error('WebSocket error:', error);
      };

      ws.onclose = () => {
        console.log('WebSocket connection closed');
      };

      return ws;
    } catch (error) {
      console.error('WebSocket connection failed:', error);
      return null;
    }
  }
}

// Singleton instance
const apiService = new APIService();

export default apiService;
export type { APIResponse, DashboardMetrics, SalesChartData, MarketplaceData, DropshippingProduct }; 