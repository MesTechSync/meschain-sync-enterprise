/**
 * @file advanced_rate_limiter.test.js
 * @description Tests for Advanced Rate Limiter
 */

const AdvancedRateLimiter = require('../advanced_rate_limiter');
const Redis = require('redis-mock');
const EventEmitter = require('events');

describe('AdvancedRateLimiter', () => {
  let rateLimiter;
  let mockRedis;
  let mockReq;
  let mockRes;
  let mockNext;
  
  beforeEach(() => {
    // Setup Redis mock
    mockRedis = Redis.createClient();
    mockRedis.incr = jest.fn();
    mockRedis.expire = jest.fn();
    mockRedis.get = jest.fn();
    mockRedis.set = jest.fn();
    mockRedis.del = jest.fn();
    
    // Create rate limiter instance
    rateLimiter = new AdvancedRateLimiter({
      redis: mockRedis,
      defaultLimit: 100,
      defaultWindow: 60,
      sensitiveRoutes: ['/api/auth', '/api/admin'],
      ipWhitelist: ['127.0.0.1', '192.168.1.1']
    });
    
    // Setup mock Express objects
    mockReq = {
      ip: '10.0.0.1',
      path: '/api/products',
      method: 'GET',
      headers: {},
      connection: new EventEmitter()
    };
    
    mockRes = {
      status: jest.fn().mockReturnThis(),
      set: jest.fn().mockReturnThis(),
      json: jest.fn().mockReturnThis()
    };
    
    mockNext = jest.fn();
  });
  
  afterEach(() => {
    jest.resetAllMocks();
  });
  
  describe('Key Generation', () => {
    it('should generate appropriate rate limit key', () => {
      // Setup
      const req = {
        ip: '10.0.0.1',
        path: '/api/products',
        method: 'GET',
        user: { id: 'user-123' },
        client: { id: 'client-456' }
      };
      
      // Execute
      const key = rateLimiter._generateKey(req);
      
      // Assert
      expect(key).toContain(req.ip);
      expect(key).toContain(req.path);
      expect(key).toContain(req.user.id);
      expect(key).toContain(req.client.id);
    });
    
    it('should handle missing user and client info', () => {
      // Setup
      const req = {
        ip: '10.0.0.1',
        path: '/api/products',
        method: 'GET'
        // No user or client
      };
      
      // Execute
      const key = rateLimiter._generateKey(req);
      
      // Assert
      expect(key).toContain(req.ip);
      expect(key).toContain(req.path);
      expect(key).not.toContain('undefined');
    });
  });
  
  describe('Limit Calculation', () => {
    it('should use lower limits for sensitive routes', () => {
      // Setup
      mockReq.path = '/api/auth/login';
      
      // Execute
      const limit = rateLimiter._getLimit(mockReq);
      
      // Assert
      expect(limit).toBeLessThan(rateLimiter.defaultLimit);
    });
    
    it('should use default limit for regular routes', () => {
      // Setup
      mockReq.path = '/api/products';
      
      // Execute
      const limit = rateLimiter._getLimit(mockReq);
      
      // Assert
      expect(limit).toBe(rateLimiter.defaultLimit);
    });
    
    it('should use higher limits for authorized users', () => {
      // Setup
      mockReq.path = '/api/products';
      mockReq.user = { id: 'user-123', role: 'premium' };
      
      // Execute
      const limit = rateLimiter._getLimit(mockReq);
      
      // Assert
      expect(limit).toBeGreaterThan(rateLimiter.defaultLimit);
    });
  });
  
  describe('Rate Limiting Middleware', () => {
    it('should allow requests under limit', async () => {
      // Setup - request count under limit
      mockRedis.incr.mockImplementation((key, cb) => cb(null, 5));
      mockRedis.expire.mockImplementation((key, exp, cb) => cb(null, 1));
      
      // Execute
      await rateLimiter.middleware(mockReq, mockRes, mockNext);
      
      // Assert
      expect(mockRes.set).toHaveBeenCalledWith('X-RateLimit-Limit', expect.any(Number));
      expect(mockRes.set).toHaveBeenCalledWith('X-RateLimit-Remaining', expect.any(Number));
      expect(mockNext).toHaveBeenCalled();
    });
    
    it('should block requests over limit', async () => {
      // Setup - request count over limit
      mockRedis.incr.mockImplementation((key, cb) => cb(null, 101));
      mockRedis.expire.mockImplementation((key, exp, cb) => cb(null, 1));
      
      // Execute
      await rateLimiter.middleware(mockReq, mockRes, mockNext);
      
      // Assert
      expect(mockRes.status).toHaveBeenCalledWith(429);
      expect(mockRes.json).toHaveBeenCalledWith(expect.objectContaining({
        error: expect.any(String)
      }));
      expect(mockNext).not.toHaveBeenCalled();
    });
    
    it('should always allow whitelisted IPs', async () => {
      // Setup - whitelisted IP but over limit count
      mockReq.ip = '127.0.0.1'; // Whitelisted
      mockRedis.incr.mockImplementation((key, cb) => cb(null, 500)); // Well over limit
      
      // Execute
      await rateLimiter.middleware(mockReq, mockRes, mockNext);
      
      // Assert
      expect(mockNext).toHaveBeenCalled();
    });
    
    it('should penalize abusive clients', async () => {
      // Setup - way over limit
      mockRedis.incr.mockImplementation((key, cb) => cb(null, 1000)); // Extremely over limit
      mockRedis.expire.mockImplementation((key, exp, cb) => cb(null, 1));
      mockRedis.set.mockImplementation((key, val, mode, dur, cb) => cb(null, 'OK'));
      
      // Execute
      await rateLimiter.middleware(mockReq, mockRes, mockNext);
      
      // Assert
      expect(mockRes.status).toHaveBeenCalledWith(429);
      expect(mockRedis.set).toHaveBeenCalled(); // Should set penalty
    });
  });
  
  describe('Dynamic Rate Limiting', () => {
    it('should adjust limits based on system load', async () => {
      // Setup base test
      const originalLimit = rateLimiter._getLimit(mockReq);
      
      // Simulate high system load
      jest.spyOn(rateLimiter, '_getSystemLoad').mockResolvedValue(0.85); // 85% load
      
      // Execute
      const adjustedLimit = await rateLimiter._getDynamicLimit(mockReq);
      
      // Assert
      expect(adjustedLimit).toBeLessThan(originalLimit);
    });
    
    it('should handle Redis failures gracefully', async () => {
      // Setup - Redis error
      mockRedis.incr.mockImplementation((key, cb) => cb(new Error('Redis connection error')));
      
      // Execute
      await rateLimiter.middleware(mockReq, mockRes, mockNext);
      
      // Assert - should still call next even with Redis failure
      expect(mockNext).toHaveBeenCalled();
    });
  });
  
  describe('Custom Middleware Creation', () => {
    it('should allow creation of custom configured middleware', async () => {
      // Setup
      const customMiddleware = rateLimiter.createMiddleware({
        defaultLimit: 50,
        defaultWindow: 30
      });
      
      mockRedis.incr.mockImplementation((key, cb) => cb(null, 30));
      mockRedis.expire.mockImplementation((key, exp, cb) => cb(null, 1));
      
      // Execute
      await customMiddleware(mockReq, mockRes, mockNext);
      
      // Assert
      expect(mockRes.set).toHaveBeenCalledWith('X-RateLimit-Limit', 50);
      expect(mockNext).toHaveBeenCalled();
    });
  });
});
