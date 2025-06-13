/**
 * @file service_mesh_integration.test.js
 * @description Tests for Service Mesh Integration
 */

const ServiceMeshIntegration = require('../service_mesh_integration');
const nock = require('nock');
const CircuitBreaker = require('opossum');

describe('ServiceMeshIntegration', () => {
  let serviceMesh;
  let mockPrometheus;
  
  beforeEach(() => {
    // Setup mock Prometheus
    mockPrometheus = {
      circuitBreakerEvents: {
        inc: jest.fn()
      },
      serviceCallCounter: {
        inc: jest.fn()
      },
      serviceCallDuration: {
        observe: jest.fn()
      }
    };
    
    // Create service mesh instance
    serviceMesh = new ServiceMeshIntegration({
      meshType: 'istio',
      serviceName: 'test-gateway',
      namespace: 'test',
      prometheus: mockPrometheus
    });
    
    // Register test services
    serviceMesh.registerService({
      id: 'user-service',
      name: 'User Service',
      version: '1.0.0',
      endpoints: ['http://user-service:8080'],
      healthCheckPath: '/health'
    });
    
    serviceMesh.registerService({
      id: 'product-service',
      name: 'Product Service',
      version: '1.0.0',
      endpoints: ['http://product-service:8080'],
      healthCheckPath: '/health'
    });
  });
  
  afterEach(() => {
    jest.resetAllMocks();
    nock.cleanAll();
  });
  
  describe('Service Registration', () => {
    it('should register a service correctly', () => {
      // Setup
      const service = {
        id: 'order-service',
        name: 'Order Service',
        version: '1.0.0',
        endpoints: ['http://order-service:8080']
      };
      
      // Execute
      serviceMesh.registerService(service);
      
      // Assert
      expect(serviceMesh.services.has('order-service')).toBe(true);
      expect(serviceMesh.circuitBreakers.has('order-service')).toBe(true);
    });
  });
  
  describe('Service Discovery', () => {
    it('should discover services from discovery URL', async () => {
      // Setup
      const discoveryURL = 'http://service-registry/services';
      const mockServices = {
        services: [
          {
            id: 'notification-service',
            name: 'Notification Service',
            version: '1.0.0',
            endpoints: ['http://notification-service:8080']
          },
          {
            id: 'payment-service',
            name: 'Payment Service',
            version: '2.0.0',
            endpoints: ['http://payment-service:8080']
          }
        ]
      };
      
      // Mock HTTP request
      nock('http://service-registry')
        .get('/services')
        .reply(200, mockServices);
      
      // Create new instance with discovery URL
      const meshWithDiscovery = new ServiceMeshIntegration({
        meshType: 'istio',
        serviceName: 'test-gateway',
        namespace: 'test',
        discoveryURL: discoveryURL
      });
      
      // Execute
      await meshWithDiscovery.discoverServices();
      
      // Assert
      expect(meshWithDiscovery.services.has('notification-service')).toBe(true);
      expect(meshWithDiscovery.services.has('payment-service')).toBe(true);
    });
    
    it('should handle discovery failure gracefully', async () => {
      // Setup
      const discoveryURL = 'http://service-registry/services';
      
      // Mock HTTP request - error
      nock('http://service-registry')
        .get('/services')
        .replyWithError('Connection refused');
      
      // Create new instance with discovery URL
      const meshWithDiscovery = new ServiceMeshIntegration({
        meshType: 'istio',
        serviceName: 'test-gateway',
        namespace: 'test',
        discoveryURL: discoveryURL
      });
      
      // Execute & Assert
      await expect(meshWithDiscovery.discoverServices())
        .rejects.toThrow();
    });
  });
  
  describe('Circuit Breaker', () => {
    it('should setup circuit breaker for each service', () => {
      // Setup - already done in beforeEach
      
      // Assert
      expect(serviceMesh.circuitBreakers.get('user-service')).toBeInstanceOf(CircuitBreaker);
      expect(serviceMesh.circuitBreakers.get('product-service')).toBeInstanceOf(CircuitBreaker);
    });
    
    it('should open circuit breaker on failures', async () => {
      // Setup
      const serviceId = 'user-service';
      const breaker = serviceMesh.circuitBreakers.get(serviceId);
      
      // Mock the fire method to fail
      jest.spyOn(breaker, 'fire').mockRejectedValue(new Error('Service error'));
      
      // Setup record event spy
      jest.spyOn(serviceMesh, '_recordCircuitEvent');
      
      // Execute
      try {
        await serviceMesh.callService(serviceId, '/users');
      } catch (error) {
        // Expected error
      }
      
      // Trigger enough failures to open circuit
      for (let i = 0; i < 10; i++) {
        try {
          await serviceMesh.callService(serviceId, '/users');
        } catch (error) {
          // Expected error
        }
      }
      
      // Assert
      expect(serviceMesh._recordCircuitEvent).toHaveBeenCalled();
      expect(mockPrometheus.serviceCallCounter.inc).toHaveBeenCalledWith({
        service: serviceId,
        status: 'error'
      });
    });
  });
  
  describe('Service Calls', () => {
    it('should call a service through the mesh', async () => {
      // Setup
      const serviceId = 'user-service';
      const path = '/users';
      const mockResponse = { users: [{ id: 1, name: 'Test User' }] };
      
      // Mock circuit breaker
      const breaker = serviceMesh.circuitBreakers.get(serviceId);
      jest.spyOn(breaker, 'fire').mockResolvedValue(mockResponse);
      
      // Execute
      const result = await serviceMesh.callService(serviceId, path);
      
      // Assert
      expect(result).toEqual(mockResponse);
      expect(mockPrometheus.serviceCallCounter.inc).toHaveBeenCalledWith({
        service: serviceId,
        status: 'success'
      });
      expect(mockPrometheus.serviceCallDuration.observe).toHaveBeenCalled();
    });
    
    it('should handle service call failures', async () => {
      // Setup
      const serviceId = 'user-service';
      const path = '/users';
      
      // Mock circuit breaker
      const breaker = serviceMesh.circuitBreakers.get(serviceId);
      jest.spyOn(breaker, 'fire').mockRejectedValue(new Error('Service unavailable'));
      
      // Execute & Assert
      await expect(serviceMesh.callService(serviceId, path))
        .rejects.toThrow();
      
      expect(mockPrometheus.serviceCallCounter.inc).toHaveBeenCalledWith({
        service: serviceId,
        status: 'error'
      });
    });
    
    it('should throw error for unknown service', async () => {
      // Execute & Assert
      await expect(serviceMesh.callService('unknown-service', '/test'))
        .rejects.toThrow('Service not found');
    });
  });
  
  describe('Health Checks', () => {
    it('should check service health', async () => {
      // Setup
      const serviceId = 'user-service';
      
      // Mock HTTP request
      nock('http://user-service:8080')
        .get('/health')
        .reply(200, { status: 'UP' });
      
      // Execute
      const isHealthy = await serviceMesh.isServiceHealthy(serviceId);
      
      // Assert
      expect(isHealthy).toBe(true);
    });
    
    it('should detect unhealthy service', async () => {
      // Setup
      const serviceId = 'product-service';
      
      // Mock HTTP request
      nock('http://product-service:8080')
        .get('/health')
        .reply(500, { status: 'DOWN' });
      
      // Execute
      const isHealthy = await serviceMesh.isServiceHealthy(serviceId);
      
      // Assert
      expect(isHealthy).toBe(false);
    });
  });
  
  describe('Tracing Headers', () => {
    it('should add correct tracing headers for Istio', () => {
      // Setup
      const headers = {};
      
      // Execute
      serviceMesh._addTracingHeaders(headers);
      
      // Assert
      expect(headers['x-request-id']).toBeDefined();
      expect(headers['x-b3-traceid']).toBeDefined();
      expect(headers['x-b3-spanid']).toBeDefined();
      expect(headers['x-envoy-attempt-count']).toBe('1');
    });
    
    it('should add correct tracing headers for Linkerd', () => {
      // Setup
      const linkerdMesh = new ServiceMeshIntegration({
        meshType: 'linkerd',
        serviceName: 'test-gateway'
      });
      const headers = {};
      
      // Execute
      linkerdMesh._addTracingHeaders(headers);
      
      // Assert
      expect(headers['x-request-id']).toBeDefined();
      expect(headers['x-b3-traceid']).toBeDefined();
      expect(headers['x-b3-spanid']).toBeDefined();
      expect(headers['l5d-dst-service']).toBe('test-gateway');
    });
  });
  
  describe('Service Proxy', () => {
    it('should create a proxy middleware for a service', () => {
      // Setup
      const serviceId = 'user-service';
      
      // Execute
      const middleware = serviceMesh.createServiceProxy(serviceId);
      
      // Assert
      expect(typeof middleware).toBe('function');
    });
    
    it('should throw error for unknown service', () => {
      // Execute & Assert
      expect(() => serviceMesh.createServiceProxy('unknown-service'))
        .toThrow('Service not found');
    });
  });
  
  describe('Service Status', () => {
    it('should get status of all services', async () => {
      // Setup
      jest.spyOn(serviceMesh, 'isServiceHealthy')
        .mockImplementation((serviceId) => {
          return Promise.resolve(serviceId === 'user-service');
        });
      
      // Execute
      const status = await serviceMesh.getServicesStatus();
      
      // Assert
      expect(status['user-service']).toBeDefined();
      expect(status['user-service'].health).toBe('healthy');
      expect(status['product-service']).toBeDefined();
      expect(status['product-service'].health).toBe('unhealthy');
    });
  });
});
