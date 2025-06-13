/**
 * @file gateway_core.test.js
 * @description Integration tests for Gateway Core
 */

const request = require('supertest');
const GatewayCore = require('../gateway_core');

// Mock dependencies
jest.mock('../oauth2_provider');
jest.mock('../jwt_security_provider');
jest.mock('../advanced_rate_limiter');
jest.mock('../service_mesh_integration');

// Mock Redis
jest.mock('redis', () => {
  return {
    createClient: jest.fn().mockReturnValue({
      connect: jest.fn().mockResolvedValue(),
      on: jest.fn(),
    })
  };
});

describe('GatewayCore', () => {
  let gatewayCore;
  
  beforeEach(async () => {
    // Create gateway instance
    gatewayCore = new GatewayCore({
      port: 3000,
      environment: 'test'
    });
    
    // Initialize gateway
    await gatewayCore.initialize();
  });
  
  afterEach(async () => {
    // Stop server if running
    if (gatewayCore.server) {
      await gatewayCore.stop();
    }
  });
  
  describe('Basic Gateway Functionality', () => {
    it('should setup and start successfully', async () => {
      // Execute
      await gatewayCore.start();
      
      // Assert
      expect(gatewayCore.server).toBeDefined();
      expect(gatewayCore.server.listening).toBe(true);
    });
    
    it('should handle health check endpoint', async () => {
      // Execute
      const app = gatewayCore.getApp();
      const response = await request(app).get('/health');
      
      // Assert
      expect(response.status).toBe(200);
      expect(response.body).toHaveProperty('status', 'healthy');
      expect(response.body).toHaveProperty('environment', 'test');
    });
    
    it('should handle metrics endpoint', async () => {
      // Execute
      const app = gatewayCore.getApp();
      const response = await request(app).get('/metrics');
      
      // Assert
      expect(response.status).toBe(200);
      expect(response.header['content-type']).toMatch(/text\/plain/);
    });
    
    it('should handle 404s properly', async () => {
      // Execute
      const app = gatewayCore.getApp();
      const response = await request(app).get('/non-existent-path');
      
      // Assert
      expect(response.status).toBe(404);
      expect(response.body).toHaveProperty('error', 'Resource not found');
    });
  });
  
  describe('Security Headers', () => {
    it('should set secure headers on responses', async () => {
      // Execute
      const app = gatewayCore.getApp();
      const response = await request(app).get('/health');
      
      // Assert
      expect(response.header).toHaveProperty('x-content-type-options', 'nosniff');
      expect(response.header).toHaveProperty('strict-transport-security');
      expect(response.header).toHaveProperty('x-frame-options');
      expect(response.header).toHaveProperty('x-xss-protection');
    });
  });
  
  describe('Middleware Chain', () => {
    it('should add request ID to all requests', async () => {
      // Execute
      const app = gatewayCore.getApp();
      const response = await request(app).get('/health');
      
      // Assert
      expect(response.header).toHaveProperty('x-request-id');
    });
    
    it('should respect provided request ID', async () => {
      // Execute
      const requestId = 'test-request-id';
      const app = gatewayCore.getApp();
      const response = await request(app)
        .get('/health')
        .set('X-Request-ID', requestId);
      
      // Assert
      expect(response.header['x-request-id']).toBe(requestId);
    });
  });
  
  describe('Error Handling', () => {
    it('should handle errors properly', async () => {
      // Setup
      const app = gatewayCore.getApp();
      
      // Add route that throws error
      app.get('/error-test', () => {
        throw new Error('Test error');
      });
      
      // Execute
      const response = await request(app).get('/error-test');
      
      // Assert
      expect(response.status).toBe(500);
      expect(response.body).toHaveProperty('error');
      expect(response.body).toHaveProperty('message');
      expect(response.body).toHaveProperty('request_id');
    });
    
    it('should sanitize error messages in production', async () => {
      // Setup
      const prodGateway = new GatewayCore({
        port: 3001,
        environment: 'production'
      });
      
      await prodGateway.initialize();
      const app = prodGateway.getApp();
      
      // Add route that throws error
      app.get('/error-test', () => {
        throw new Error('Sensitive information');
      });
      
      // Execute
      const response = await request(app).get('/error-test');
      
      // Assert
      expect(response.status).toBe(500);
      expect(response.body.message).toBe('Internal server error');
      expect(response.body.message).not.toContain('Sensitive information');
      
      // Cleanup
      await prodGateway.stop();
    });
  });
  
  describe('Redis Integration', () => {
    it('should handle missing Redis gracefully', () => {
      // Setup
      const noRedisGateway = new GatewayCore({
        port: 3002,
        environment: 'test',
        redisOptions: null
      });
      
      // Assert
      expect(noRedisGateway.redis).toBeNull();
    });
  });
});
