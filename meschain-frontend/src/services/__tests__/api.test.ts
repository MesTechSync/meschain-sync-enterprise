import axios from 'axios';
import { apiService } from '../api';
import { createMockApiResponse, mockApiError } from '../../setupTests';

jest.mock('axios');
const mockedAxios = axios as jest.Mocked<typeof axios>;

describe('API Service Tests', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    // Setup default axios mock
    mockedAxios.create.mockReturnValue({
      get: jest.fn(),
      post: jest.fn(),
      put: jest.fn(),
      delete: jest.fn(),
      patch: jest.fn(),
      interceptors: {
        request: { use: jest.fn() },
        response: { use: jest.fn() }
      }
    } as any);
  });

  describe('Authentication', () => {
    it('includes authorization header when token is present', async () => {
      const mockInstance = {
        get: jest.fn().mockResolvedValue(createMockApiResponse({})),
        post: jest.fn(),
        put: jest.fn(),
        delete: jest.fn(),
        interceptors: {
          request: { use: jest.fn() },
          response: { use: jest.fn() }
        }
      };
      
      mockedAxios.create.mockReturnValue(mockInstance as any);
      
      const api = apiService;
      api.setAuthToken('test-token');
      
      await api.get('/test');
      
      expect(mockInstance.get).toHaveBeenCalledWith('/test', {
        headers: { Authorization: 'Bearer test-token' }
      });
    });

    it('handles token refresh on 401 error', async () => {
      const mockInstance = {
        get: jest.fn()
          .mockRejectedValueOnce(mockApiError('Unauthorized', 401))
          .mockResolvedValueOnce(createMockApiResponse({ access_token: 'new-token' }))
          .mockResolvedValueOnce(createMockApiResponse({ data: 'success' })),
        post: jest.fn().mockResolvedValue(createMockApiResponse({ access_token: 'new-token' })),
        put: jest.fn(),
        delete: jest.fn(),
        interceptors: {
          request: { use: jest.fn() },
          response: { use: jest.fn() }
        }
      };
      
      mockedAxios.create.mockReturnValue(mockInstance as any);
      
      const api = apiService;
      const result = await api.get('/protected');
      
      expect(mockInstance.post).toHaveBeenCalledWith('/auth/refresh');
      expect(result.data).toBe('success');
    });

    it('redirects to login on persistent 401 error', async () => {
      const mockInstance = {
        get: jest.fn().mockRejectedValue(mockApiError('Unauthorized', 401)),
        post: jest.fn().mockRejectedValue(mockApiError('Refresh failed', 401)),
        put: jest.fn(),
        delete: jest.fn(),
        interceptors: {
          request: { use: jest.fn() },
          response: { use: jest.fn() }
        }
      };
      
      mockedAxios.create.mockReturnValue(mockInstance as any);
      
      // Mock window.location
      delete (window as any).location;
      window.location = { href: '' } as any;
      
      const api = apiService;
      
      try {
        await api.get('/protected');
      } catch (error) {
        expect(window.location.href).toBe('/admin/index.php?route=common/login');
      }
    });
  });

  describe('Dashboard API', () => {
    it('fetches dashboard statistics', async () => {
      const mockStats = {
        totalSales: 150000,
        totalOrders: 1250,
        totalProducts: 450,
        activeMarketplaces: 5,
        todaySales: 5600,
        pendingOrders: 28,
        lowStockProducts: 12,
        conversionRate: 3.4
      };

      const mockInstance = {
        get: jest.fn().mockResolvedValue(createMockApiResponse(mockStats)),
        post: jest.fn(),
        put: jest.fn(),
        delete: jest.fn(),
        interceptors: {
          request: { use: jest.fn() },
          response: { use: jest.fn() }
        }
      };
      
      mockedAxios.create.mockReturnValue(mockInstance as any);
      
      const api = apiService;
      const result = await api.getDashboardStats();
      
      expect(mockInstance.get).toHaveBeenCalledWith('/dashboard/stats');
      expect(result).toEqual(mockStats);
    });

    it('fetches recent orders', async () => {
      const mockOrders = [
        {
          id: 'ORD-001',
          customerName: 'John Doe',
          amount: 299.99,
          status: 'completed',
          marketplace: 'trendyol',
          date: '2024-06-03T10:30:00Z'
        },
        {
          id: 'ORD-002',
          customerName: 'Jane Smith',
          amount: 150.00,
          status: 'pending',
          marketplace: 'hepsiburada',
          date: '2024-06-03T09:15:00Z'
        }
      ];

      const mockInstance = {
        get: jest.fn().mockResolvedValue(createMockApiResponse(mockOrders)),
        post: jest.fn(),
        put: jest.fn(),
        delete: jest.fn(),
        interceptors: {
          request: { use: jest.fn() },
          response: { use: jest.fn() }
        }
      };
      
      mockedAxios.create.mockReturnValue(mockInstance as any);
      
      const api = apiService;
      const result = await api.getRecentOrders({ limit: 10 });
      
      expect(mockInstance.get).toHaveBeenCalledWith('/orders/recent', {
        params: { limit: 10 }
      });
      expect(result).toHaveLength(2);
      expect(result[0].id).toBe('ORD-001');
    });
  });

  describe('Marketplace API', () => {
    it('fetches marketplace status', async () => {
      const mockStatus = {
        trendyol: { connected: true, lastSync: '2024-06-03T10:00:00Z', health: 'good' },
        hepsiburada: { connected: false, lastSync: '2024-06-02T15:30:00Z', health: 'error' },
        n11: { connected: true, lastSync: '2024-06-03T09:45:00Z', health: 'warning' }
      };

      const mockInstance = {
        get: jest.fn().mockResolvedValue(createMockApiResponse(mockStatus)),
        post: jest.fn(),
        put: jest.fn(),
        delete: jest.fn(),
        interceptors: {
          request: { use: jest.fn() },
          response: { use: jest.fn() }
        }
      };
      
      mockedAxios.create.mockReturnValue(mockInstance as any);
      
      const api = apiService;
      const result = await api.getMarketplaceStatus();
      
      expect(mockInstance.get).toHaveBeenCalledWith('/marketplace/status');
      expect(result.trendyol.connected).toBe(true);
      expect(result.hepsiburada.connected).toBe(false);
    });

    it('syncs marketplace data', async () => {
      const mockSyncResult = {
        status: 'success',
        syncedProducts: 150,
        syncedOrders: 45,
        errors: [],
        duration: 12.5
      };

      const mockInstance = {
        get: jest.fn(),
        post: jest.fn().mockResolvedValue(createMockApiResponse(mockSyncResult)),
        put: jest.fn(),
        delete: jest.fn(),
        interceptors: {
          request: { use: jest.fn() },
          response: { use: jest.fn() }
        }
      };
      
      mockedAxios.create.mockReturnValue(mockInstance as any);
      
      const api = apiService;
      const result = await api.syncMarketplace('trendyol', { fullSync: true });
      
      expect(mockInstance.post).toHaveBeenCalledWith('/marketplace/trendyol/sync', {
        fullSync: true
      });
      expect(result.status).toBe('success');
      expect(result.syncedProducts).toBe(150);
    });
  });

  describe('Product API', () => {
    it('fetches products with filters', async () => {
      const mockProducts = [
        {
          id: 'PROD-001',
          name: 'Test Product 1',
          sku: 'SKU-001',
          price: 99.99,
          stock: 50,
          category: 'Electronics',
          marketplaces: ['trendyol', 'hepsiburada']
        },
        {
          id: 'PROD-002',
          name: 'Test Product 2',
          sku: 'SKU-002',
          price: 149.99,
          stock: 25,
          category: 'Fashion',
          marketplaces: ['n11']
        }
      ];

      const mockInstance = {
        get: jest.fn().mockResolvedValue(createMockApiResponse({
          products: mockProducts,
          total: 100,
          page: 1,
          limit: 20
        })),
        post: jest.fn(),
        put: jest.fn(),
        delete: jest.fn(),
        interceptors: {
          request: { use: jest.fn() },
          response: { use: jest.fn() }
        }
      };
      
      mockedAxios.create.mockReturnValue(mockInstance as any);
      
      const api = apiService;
      const result = await api.getProducts({
        page: 1,
        limit: 20,
        category: 'Electronics',
        marketplace: 'trendyol'
      });
      
      expect(mockInstance.get).toHaveBeenCalledWith('/products', {
        params: {
          page: 1,
          limit: 20,
          category: 'Electronics',
          marketplace: 'trendyol'
        }
      });
      expect(result.products).toHaveLength(2);
    });

    it('creates new product', async () => {
      const newProduct = {
        name: 'New Product',
        sku: 'NEW-001',
        price: 199.99,
        stock: 100,
        category: 'Electronics',
        description: 'New product description'
      };

      const mockCreatedProduct = {
        id: 'PROD-003',
        ...newProduct,
        createdAt: '2024-06-03T10:00:00Z'
      };

      const mockInstance = {
        get: jest.fn(),
        post: jest.fn().mockResolvedValue(createMockApiResponse(mockCreatedProduct)),
        put: jest.fn(),
        delete: jest.fn(),
        interceptors: {
          request: { use: jest.fn() },
          response: { use: jest.fn() }
        }
      };
      
      mockedAxios.create.mockReturnValue(mockInstance as any);
      
      const api = apiService;
      const result = await api.createProduct(newProduct);
      
      expect(mockInstance.post).toHaveBeenCalledWith('/products', newProduct);
      expect(result.id).toBe('PROD-003');
      expect(result.name).toBe('New Product');
    });

    it('updates existing product', async () => {
      const productUpdate = {
        price: 179.99,
        stock: 75
      };

      const mockInstance = {
        get: jest.fn(),
        post: jest.fn(),
        put: jest.fn().mockResolvedValue(createMockApiResponse({ success: true })),
        delete: jest.fn(),
        interceptors: {
          request: { use: jest.fn() },
          response: { use: jest.fn() }
        }
      };
      
      mockedAxios.create.mockReturnValue(mockInstance as any);
      
      const api = apiService;
      const result = await api.updateProduct('PROD-001', productUpdate);
      
      expect(mockInstance.put).toHaveBeenCalledWith('/products/PROD-001', productUpdate);
      expect(result.success).toBe(true);
    });
  });

  describe('Error Handling', () => {
    it('handles network errors', async () => {
      const mockInstance = {
        get: jest.fn().mockRejectedValue(new Error('Network Error')),
        post: jest.fn(),
        put: jest.fn(),
        delete: jest.fn(),
        interceptors: {
          request: { use: jest.fn() },
          response: { use: jest.fn() }
        }
      };
      
      mockedAxios.create.mockReturnValue(mockInstance as any);
      
      const api = apiService;
      
      await expect(api.get('/test')).rejects.toThrow('Network Error');
    });

    it('handles server errors with custom messages', async () => {
      const mockInstance = {
        get: jest.fn().mockRejectedValue(mockApiError('Internal Server Error', 500)),
        post: jest.fn(),
        put: jest.fn(),
        delete: jest.fn(),
        interceptors: {
          request: { use: jest.fn() },
          response: { use: jest.fn() }
        }
      };
      
      mockedAxios.create.mockReturnValue(mockInstance as any);
      
      const api = apiService;
      
      try {
        await api.get('/test');
      } catch (error: any) {
        expect(error.response.status).toBe(500);
        expect(error.response.data.message).toBe('Internal Server Error');
      }
    });

    it('handles timeout errors', async () => {
      const mockInstance = {
        get: jest.fn().mockRejectedValue({ code: 'ECONNABORTED' }),
        post: jest.fn(),
        put: jest.fn(),
        delete: jest.fn(),
        interceptors: {
          request: { use: jest.fn() },
          response: { use: jest.fn() }
        }
      };
      
      mockedAxios.create.mockReturnValue(mockInstance as any);
      
      const api = apiService;
      
      try {
        await api.get('/test');
      } catch (error: any) {
        expect(error.code).toBe('ECONNABORTED');
      }
    });
  });

  describe('Request Interceptors', () => {
    it('adds request timestamp', async () => {
      const mockInstance = {
        get: jest.fn().mockResolvedValue(createMockApiResponse({})),
        post: jest.fn(),
        put: jest.fn(),
        delete: jest.fn(),
        interceptors: {
          request: { 
            use: jest.fn().mockImplementation((onFulfilled) => {
              // Simulate interceptor behavior
              const config = { headers: {} };
              const modifiedConfig = onFulfilled(config);
              expect(modifiedConfig.headers['X-Request-Timestamp']).toBeDefined();
            })
          },
          response: { use: jest.fn() }
        }
      };
      
      mockedAxios.create.mockReturnValue(mockInstance as any);
      
      const api = apiService;
      await api.get('/test');
    });

    it('adds request ID for tracking', async () => {
      const mockInstance = {
        get: jest.fn().mockResolvedValue(createMockApiResponse({})),
        post: jest.fn(),
        put: jest.fn(),
        delete: jest.fn(),
        interceptors: {
          request: { 
            use: jest.fn().mockImplementation((onFulfilled) => {
              const config = { headers: {} };
              const modifiedConfig = onFulfilled(config);
              expect(modifiedConfig.headers['X-Request-ID']).toBeDefined();
            })
          },
          response: { use: jest.fn() }
        }
      };
      
      mockedAxios.create.mockReturnValue(mockInstance as any);
      
      const api = apiService;
      await api.get('/test');
    });
  });

  describe('Response Interceptors', () => {
    it('logs response times', async () => {
      const consoleSpy = jest.spyOn(console, 'debug').mockImplementation(() => {});
      
      const mockInstance = {
        get: jest.fn().mockResolvedValue(createMockApiResponse({})),
        post: jest.fn(),
        put: jest.fn(),
        delete: jest.fn(),
        interceptors: {
          request: { use: jest.fn() },
          response: { 
            use: jest.fn().mockImplementation((onFulfilled) => {
              const response = { 
                data: {}, 
                config: { headers: { 'X-Request-Timestamp': Date.now() - 100 } }
              };
              onFulfilled(response);
              expect(consoleSpy).toHaveBeenCalledWith(
                expect.stringContaining('Response time:')
              );
            })
          }
        }
      };
      
      mockedAxios.create.mockReturnValue(mockInstance as any);
      
      const api = apiService;
      await api.get('/test');
      
      consoleSpy.mockRestore();
    });
  });
});

// Performance tests
describe('API Performance Tests', () => {
  it('handles concurrent requests efficiently', async () => {
    const mockInstance = {
      get: jest.fn().mockImplementation(() => 
        new Promise(resolve => 
          setTimeout(() => resolve(createMockApiResponse({})), 10)
        )
      ),
      post: jest.fn(),
      put: jest.fn(),
      delete: jest.fn(),
      interceptors: {
        request: { use: jest.fn() },
        response: { use: jest.fn() }
      }
    };
    
    mockedAxios.create.mockReturnValue(mockInstance as any);
    
    const api = apiService;
    
    const startTime = performance.now();
    
    // Make 10 concurrent requests
    const promises = Array.from({ length: 10 }, (_, i) => 
      api.get(`/test-${i}`)
    );
    
    await Promise.all(promises);
    
    const endTime = performance.now();
    const totalTime = endTime - startTime;
    
    // Should complete all requests in reasonable time
    expect(totalTime).toBeLessThan(100); // Less than 100ms for concurrent requests
    expect(mockInstance.get).toHaveBeenCalledTimes(10);
  });

  it('implements request debouncing for rapid calls', async () => {
    let callCount = 0;
    const mockInstance = {
      get: jest.fn().mockImplementation(() => {
        callCount++;
        return Promise.resolve(createMockApiResponse({}));
      }),
      post: jest.fn(),
      put: jest.fn(),
      delete: jest.fn(),
      interceptors: {
        request: { use: jest.fn() },
        response: { use: jest.fn() }
      }
    };
    
    mockedAxios.create.mockReturnValue(mockInstance as any);
    
    const api = apiService;
    
    // Make rapid successive calls to same endpoint
    for (let i = 0; i < 5; i++) {
      api.get('/search?q=test');
    }
    
    // Wait for debounce
    await new Promise(resolve => setTimeout(resolve, 100));
    
    // Should only make one actual request due to debouncing
    expect(callCount).toBeLessThanOrEqual(1);
  });
}); 