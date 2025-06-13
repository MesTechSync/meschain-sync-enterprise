/**
 * @file advanced_rate_limiter.js
 * @description Advanced Rate Limiting with dynamic throttling and adaptive limits
 * @version 1.0.0
 * @author Cursor AI Team
 * @date June 13, 2025
 */

const Redis = require('redis');

/**
 * Advanced Rate Limiter with multiple strategies and dynamic adaptation
 */
class AdvancedRateLimiter {
  constructor(options = {}) {
    this.redis = options.redis;
    this.fallbackMemory = new Map();
    this.fallbackTimers = new Map();
    
    // Default configuration
    this.config = {
      defaultLimit: options.defaultLimit || 100,
      defaultWindow: options.defaultWindow || 60, // 60 seconds
      burstMultiplier: options.burstMultiplier || 2,
      penaltyMultiplier: options.penaltyMultiplier || 0.5,
      adaptiveScaling: options.adaptiveScaling !== false,
      sensitiveRoutes: options.sensitiveRoutes || ['/api/auth', '/api/user', '/api/admin'],
      sensitiveRoutesMultiplier: options.sensitiveRoutesMultiplier || 0.2, // 20% of default
      ipWhitelist: options.ipWhitelist || [],
      routeRules: options.routeRules || {},
      userLimits: options.userLimits || {},
      clientLimits: options.clientLimits || {}
    };
    
    // Track system load for adaptive limits
    this.systemLoad = {
      startTime: Date.now(),
      requestCount: 0,
      errorCount: 0,
      avgResponseTime: 100, // milliseconds
      peakResponseTime: 0
    };
    
    console.log('Advanced Rate Limiter initialized');
  }

  /**
   * Main rate limiting function
   * @param {Object} req - Express request object
   * @param {Object} res - Express response object
   * @param {Function} next - Express next middleware
   * @returns {Promise<void>}
   */
  async middleware(req, res, next) {
    const startTime = Date.now();
    
    try {
      // Skip whitelisted IPs
      if (this.isWhitelisted(req.ip)) {
        return next();
      }
      
      // Generate limit key based on identity factors
      const key = this.generateLimitKey(req);
      
      // Get applicable limit for this request
      const limit = this.getApplicableLimit(req);
      
      // Check if rate limit is exceeded
      const { allowed, current, resetTime } = await this.checkRateLimit(key, limit);
      
      // Set rate limit headers
      res.setHeader('X-RateLimit-Limit', limit.limit);
      res.setHeader('X-RateLimit-Remaining', Math.max(0, limit.limit - current));
      res.setHeader('X-RateLimit-Reset', resetTime);
      
      // If limit exceeded, return 429
      if (!allowed) {
        // Track for system metrics
        this.trackRequest(Date.now() - startTime, true);
        
        // Apply dynamic penalty for abusers
        if (limit.increasePenalty) {
          await this.applyPenalty(key, req.ip);
        }
        
        return res.status(429).json({
          error: 'Rate limit exceeded',
          message: `Too many requests. Please try again after ${Math.ceil((resetTime - Date.now()) / 1000)} seconds.`,
          retry_after: Math.ceil((resetTime - Date.now()) / 1000)
        });
      }
      
      // Add response tracker
      const originalSend = res.send;
      res.send = function(...args) {
        const responseTime = Date.now() - startTime;
        this.trackRequest(responseTime, res.statusCode >= 400);
        originalSend.apply(res, args);
      }.bind(this);
      
      next();
    } catch (error) {
      console.error('Rate limiting error:', error);
      // Don't block the request on rate limiting errors
      next();
    }
  }

  /**
   * Generate unique limit key based on identity factors
   * @param {Object} req - Express request
   * @returns {string} - Limit key
   */
  generateLimitKey(req) {
    let key = `rate:${req.ip}`;
    
    // Add authenticated user ID if available
    if (req.user && req.user.id) {
      key += `:user:${req.user.id}`;
    }
    
    // Add API client ID if available
    if (req.apiKey && req.apiKey.id) {
      key += `:client:${req.apiKey.id}`;
    }
    
    // Add route specific identifier
    const routeId = req.route ? (req.baseUrl + req.route.path).replace(/\//g, '_') : req.path.replace(/\//g, '_');
    key += `:route:${routeId}`;
    
    return key;
  }

  /**
   * Get applicable rate limit for this request
   * @param {Object} req - Express request
   * @returns {Object} - Rate limit configuration
   */
  getApplicableLimit(req) {
    let limit = { 
      limit: this.config.defaultLimit,
      window: this.config.defaultWindow,
      increasePenalty: false
    };
    
    // Check if route has specific rules
    const path = req.path;
    const matchedRoute = Object.keys(this.config.routeRules)
      .find(route => {
        if (route.includes('*')) {
          const routeRegex = new RegExp('^' + route.replace(/\*/g, '.*') + '$');
          return routeRegex.test(path);
        }
        return route === path;
      });
    
    if (matchedRoute) {
      const routeRule = this.config.routeRules[matchedRoute];
      limit.limit = routeRule.limit || limit.limit;
      limit.window = routeRule.window || limit.window;
    }
    
    // Check for sensitive routes
    if (this.config.sensitiveRoutes.some(route => path.startsWith(route))) {
      limit.limit = Math.floor(limit.limit * this.config.sensitiveRoutesMultiplier);
      limit.increasePenalty = true;
    }
    
    // User specific limits override route limits
    if (req.user && req.user.id && this.config.userLimits[req.user.id]) {
      const userLimit = this.config.userLimits[req.user.id];
      limit.limit = userLimit.limit || limit.limit;
      limit.window = userLimit.window || limit.window;
    }
    
    // API client specific limits have highest priority
    if (req.apiKey && req.apiKey.id && this.config.clientLimits[req.apiKey.id]) {
      const clientLimit = this.config.clientLimits[req.apiKey.id];
      limit.limit = clientLimit.limit || limit.limit;
      limit.window = clientLimit.window || limit.window;
    }
    
    // Apply adaptive scaling based on system load if enabled
    if (this.config.adaptiveScaling) {
      limit = this.applyAdaptiveScaling(limit);
    }
    
    return limit;
  }

  /**
   * Apply adaptive scaling based on system load
   * @param {Object} limit - Current limit configuration
   * @returns {Object} - Adjusted limit configuration
   */
  applyAdaptiveScaling(limit) {
    const adjustedLimit = { ...limit };
    
    // Get current system metrics
    const systemLoadFactor = this.calculateSystemLoadFactor();
    
    // Adjust limit based on load factor
    // As system load increases, rate limits decrease
    if (systemLoadFactor > 1) {
      // System is under heavy load, reduce limits
      adjustedLimit.limit = Math.max(1, Math.floor(adjustedLimit.limit / systemLoadFactor));
    } else if (systemLoadFactor < 0.5) {
      // System is underutilized, allow more requests (up to burst limit)
      const burstLimit = Math.floor(adjustedLimit.limit * this.config.burstMultiplier);
      adjustedLimit.limit = Math.min(burstLimit, Math.floor(adjustedLimit.limit / systemLoadFactor));
    }
    
    return adjustedLimit;
  }

  /**
   * Calculate system load factor based on metrics
   * @returns {number} - Load factor (> 1 means heavy load)
   */
  calculateSystemLoadFactor() {
    const now = Date.now();
    const runningTime = (now - this.systemLoad.startTime) / 1000; // in seconds
    
    if (runningTime < 10) {
      return 1; // Not enough data yet
    }
    
    // Calculate request rate per second
    const requestRate = this.systemLoad.requestCount / runningTime;
    
    // Calculate error rate
    const errorRate = this.systemLoad.errorCount / Math.max(1, this.systemLoad.requestCount);
    
    // Calculate response time factor
    const responseTimeFactor = this.systemLoad.avgResponseTime / 100; // 100ms is baseline
    
    // Higher weights for error rate and response time
    const loadFactor = (requestRate * 0.2) + (errorRate * 0.5) + (responseTimeFactor * 0.3);
    
    return Math.max(0.1, Math.min(10, loadFactor)); // Clamp between 0.1 and 10
  }

  /**
   * Track request for system metrics
   * @param {number} responseTime - Request response time
   * @param {boolean} isError - Whether request resulted in error
   */
  trackRequest(responseTime, isError) {
    this.systemLoad.requestCount++;
    
    if (isError) {
      this.systemLoad.errorCount++;
    }
    
    // Update response time metrics
    const oldAvg = this.systemLoad.avgResponseTime;
    const count = this.systemLoad.requestCount;
    
    // Exponential moving average with 0.05 smoothing factor
    this.systemLoad.avgResponseTime = (oldAvg * 0.95) + (responseTime * 0.05);
    
    // Update peak response time
    this.systemLoad.peakResponseTime = Math.max(this.systemLoad.peakResponseTime, responseTime);
    
    // Reset metrics periodically
    const now = Date.now();
    if (now - this.systemLoad.startTime > 3600000) { // 1 hour
      this.resetSystemMetrics();
    }
  }

  /**
   * Reset system metrics
   */
  resetSystemMetrics() {
    this.systemLoad = {
      startTime: Date.now(),
      requestCount: 0,
      errorCount: 0,
      avgResponseTime: this.systemLoad.avgResponseTime, // Keep the last average
      peakResponseTime: 0
    };
  }

  /**
   * Check if IP is whitelisted
   * @param {string} ip - IP address
   * @returns {boolean} - Whether IP is whitelisted
   */
  isWhitelisted(ip) {
    return this.config.ipWhitelist.includes(ip);
  }

  /**
   * Apply penalty to abusive clients
   * @param {string} key - Rate limit key
   * @param {string} ip - Client IP
   */
  async applyPenalty(key, ip) {
    try {
      // Add IP to penalty list
      const penaltyKey = `penalty:${ip}`;
      const penaltyDuration = 3600; // 1 hour penalty
      
      if (this.redis) {
        // Increment penalty count
        const count = await this.redis.incr(penaltyKey);
        
        // Set expiry if this is the first penalty
        if (count === 1) {
          await this.redis.expire(penaltyKey, penaltyDuration);
        }
        
        // Add dynamic penalty based on repeat offenses
        const dynamicPenalty = Math.min(24 * 3600, count * 3600); // Max 24 hours
        await this.redis.setEx(`ban:${ip}`, dynamicPenalty, '1');
      } else {
        // Memory fallback
        const count = (this.fallbackMemory.get(penaltyKey) || 0) + 1;
        this.fallbackMemory.set(penaltyKey, count);
        
        // Set timeout to clear
        if (this.fallbackTimers.has(penaltyKey)) {
          clearTimeout(this.fallbackTimers.get(penaltyKey));
        }
        
        this.fallbackTimers.set(penaltyKey, setTimeout(() => {
          this.fallbackMemory.delete(penaltyKey);
          this.fallbackTimers.delete(penaltyKey);
        }, penaltyDuration * 1000));
        
        // Add to ban list
        const dynamicPenalty = Math.min(24 * 3600, count * 3600); // Max 24 hours
        this.fallbackMemory.set(`ban:${ip}`, 1);
        
        // Set timeout to clear ban
        if (this.fallbackTimers.has(`ban:${ip}`)) {
          clearTimeout(this.fallbackTimers.get(`ban:${ip}`));
        }
        
        this.fallbackTimers.set(`ban:${ip}`, setTimeout(() => {
          this.fallbackMemory.delete(`ban:${ip}`);
          this.fallbackTimers.delete(`ban:${ip}`);
        }, dynamicPenalty * 1000));
      }
      
      console.warn(`Applied rate limit penalty to IP: ${ip}, key: ${key}`);
    } catch (error) {
      console.error('Error applying penalty:', error);
    }
  }

  /**
   * Check if request exceeds rate limit
   * @param {string} key - Rate limit key
   * @param {Object} limit - Limit configuration
   * @returns {Promise<Object>} - Rate limit status
   */
  async checkRateLimit(key, limit) {
    const now = Date.now();
    const windowKey = `${key}:${Math.floor(now / (limit.window * 1000))}`;
    const resetTime = Math.ceil(now / (limit.window * 1000)) * (limit.window * 1000);
    
    try {
      if (this.redis) {
        // Use Redis for distributed rate limiting
        const count = await this.redis.incr(windowKey);
        
        // Set expiry on first request in window
        if (count === 1) {
          await this.redis.expire(windowKey, limit.window);
        }
        
        return {
          allowed: count <= limit.limit,
          current: count,
          resetTime
        };
      } else {
        // Memory fallback for non-distributed environments
        let count = (this.fallbackMemory.get(windowKey) || 0) + 1;
        this.fallbackMemory.set(windowKey, count);
        
        // Set timeout to clear counter
        if (!this.fallbackTimers.has(windowKey)) {
          this.fallbackTimers.set(windowKey, setTimeout(() => {
            this.fallbackMemory.delete(windowKey);
            this.fallbackTimers.delete(windowKey);
          }, limit.window * 1000));
        }
        
        return {
          allowed: count <= limit.limit,
          current: count,
          resetTime
        };
      }
    } catch (error) {
      console.error('Rate limit check error:', error);
      // Fail open to prevent blocking all traffic on errors
      return {
        allowed: true,
        current: 0,
        resetTime
      };
    }
  }

  /**
   * Create Express middleware function
   * @param {Object} options - Options override for this route
   * @returns {Function} - Express middleware
   */
  createMiddleware(options = {}) {
    const routeOptions = { ...this.config, ...options };
    const limiter = new AdvancedRateLimiter(routeOptions);
    
    return (req, res, next) => {
      return limiter.middleware(req, res, next);
    };
  }
}

module.exports = AdvancedRateLimiter;
