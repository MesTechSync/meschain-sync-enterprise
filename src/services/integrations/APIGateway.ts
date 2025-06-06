/**
 * Advanced API Gateway - Centralized API management and routing
 * Handles request routing, authentication, rate limiting, caching, and monitoring
 * 
 * @author MesChain Team
 * @version 3.0.0
 * @since 2025-01-15
 */

import { EventEmitter } from 'events';
import express, { Request, Response, NextFunction, Application } from 'express';
import rateLimit from 'express-rate-limit';
import compression from 'compression';
import cors from 'cors';
import helmet from 'helmet';
import jwt from 'jsonwebtoken';
import Redis from 'ioredis';
import axios, { AxiosResponse } from 'axios';
import { performance } from 'perf_hooks';
import crypto from 'crypto';

// Types and Interfaces
export interface APIRoute {
  id: string;
  path: string;
  method: 'GET' | 'POST' | 'PUT' | 'DELETE' | 'PATCH';
  targetUrl: string;
  marketplace: string;
  auth: {
    required: boolean;
    type: 'bearer' | 'apikey' | 'oauth' | 'hmac';
    roles?: string[];
  };
  rateLimit: {
    windowMs: number;
    max: number;
  };
  cache: {
    enabled: boolean;
    ttl: number; // seconds
    keys: string[];
  };
  transform: {
    request?: (data: any) => any;
    response?: (data: any) => any;
  };
  monitoring: {
    enabled: boolean;
    alertThreshold: number; // response time in ms
  };
}

export interface APIMetrics {
  routeId: string;
  totalRequests: number;
  successfulRequests: number;
  failedRequests: number;
  averageResponseTime: number;
  lastRequestTime: Date;
  errors: Array<{
    timestamp: Date;
    error: string;
    statusCode: number;
  }>;
  rateLimitHits: number;
  cacheHits: number;
  cacheMisses: number;
}

export interface AuthToken {
  userId: string;
  roles: string[];
  marketplaces: string[];
  expiresAt: number;
  iat: number;
}

export interface RequestLog {
  id: string;
  routeId: string;
  method: string;
  path: string;
  ip: string;
  userAgent: string;
  userId?: string;
  timestamp: Date;
  responseTime: number;
  statusCode: number;
  requestSize: number;
  responseSize: number;
  cached: boolean;
  error?: string;
}

export class APIGateway extends EventEmitter {
  private app: Application;
  private server: any;
  private redis: Redis;
  private routes: Map<string, APIRoute> = new Map();
  private metrics: Map<string, APIMetrics> = new Map();
  private requestLogs: RequestLog[] = [];
  private jwtSecret: string;
  private port: number;

  constructor(config: {
    port: number;
    jwtSecret: string;
    redisUrl?: string;
  }) {
    super();
    
    this.port = config.port;
    this.jwtSecret = config.jwtSecret;
    this.app = express();
    
    // Initialize Redis for caching
    this.redis = new Redis(config.redisUrl || 'redis://localhost:6379');
    
    this.setupMiddleware();
    this.setupRoutes();
    this.startMetricsCollector();
  }

  /**
   * Setup Express middleware
   */
  private setupMiddleware(): void {
    // Security middleware
    this.app.use(helmet());
    this.app.use(cors({
      origin: process.env.ALLOWED_ORIGINS?.split(',') || '*',
      credentials: true
    }));

    // Compression
    this.app.use(compression());

    // Body parsing
    this.app.use(express.json({ limit: '10mb' }));
    this.app.use(express.urlencoded({ extended: true, limit: '10mb' }));

    // Request logging middleware
    this.app.use((req: Request, res: Response, next: NextFunction) => {
      const startTime = performance.now();
      
      res.on('finish', () => {
        const responseTime = performance.now() - startTime;
        this.logRequest(req, res, responseTime);
      });

      next();
    });

    // Global rate limiting
    const globalLimiter = rateLimit({
      windowMs: 15 * 60 * 1000, // 15 minutes
      max: 1000, // requests per window
      message: 'Too many requests from this IP',
      standardHeaders: true,
      legacyHeaders: false,
    });
    this.app.use(globalLimiter);
  }

  /**
   * Setup routes
   */
  private setupRoutes(): void {
    // Health check
    this.app.get('/health', (req: Request, res: Response) => {
      res.json({
        status: 'healthy',
        timestamp: new Date().toISOString(),
        uptime: process.uptime(),
        routes: this.routes.size,
        memory: process.memoryUsage()
      });
    });

    // Metrics endpoint
    this.app.get('/metrics', this.authMiddleware, (req: Request, res: Response) => {
      res.json({
        routes: Array.from(this.metrics.values()),
        summary: this.getMetricsSummary()
      });
    });

    // Dynamic route handler
    this.app.all('*', this.routeHandler.bind(this));
  }

  /**
   * Register a new API route
   */
  public registerRoute(route: APIRoute): void {
    // Validate route
    this.validateRoute(route);

    // Store route
    this.routes.set(route.id, route);

    // Initialize metrics
    this.metrics.set(route.id, {
      routeId: route.id,
      totalRequests: 0,
      successfulRequests: 0,
      failedRequests: 0,
      averageResponseTime: 0,
      lastRequestTime: new Date(),
      errors: [],
      rateLimitHits: 0,
      cacheHits: 0,
      cacheMisses: 0
    });

    console.log(`✅ Route registered: ${route.method} ${route.path} -> ${route.targetUrl}`);
    this.emit('route:registered', route);
  }

  /**
   * Main route handler
   */
  private async routeHandler(req: Request, res: Response): Promise<void> {
    const startTime = performance.now();
    let route: APIRoute | null = null;

    try {
      // Find matching route
      route = this.findRoute(req.method as any, req.path);
      if (!route) {
        return res.status(404).json({ error: 'Route not found' });
      }

      // Authentication check
      if (route.auth.required) {
        const authResult = await this.authenticate(req, route);
        if (!authResult.success) {
          return res.status(401).json({ error: authResult.error });
        }
        req.user = authResult.user;
      }

      // Rate limiting check
      const rateLimitResult = await this.checkRateLimit(req, route);
      if (!rateLimitResult.allowed) {
        this.updateMetrics(route.id, 'rateLimit');
        return res.status(429).json({ 
          error: 'Rate limit exceeded',
          retryAfter: rateLimitResult.retryAfter
        });
      }

      // Check cache
      let cachedResponse: any = null;
      if (route.cache.enabled && req.method === 'GET') {
        cachedResponse = await this.getFromCache(req, route);
        if (cachedResponse) {
          this.updateMetrics(route.id, 'success', performance.now() - startTime, true);
          return res.json(cachedResponse);
        }
      }

      // Forward request to target
      const response = await this.forwardRequest(req, route);
      
      // Transform response if needed
      let responseData = response.data;
      if (route.transform.response) {
        responseData = route.transform.response(responseData);
      }

      // Cache response if enabled
      if (route.cache.enabled && req.method === 'GET' && response.status < 400) {
        await this.setCache(req, route, responseData);
      }

      // Send response
      res.status(response.status).json(responseData);
      
      // Update metrics
      this.updateMetrics(route.id, 'success', performance.now() - startTime, false);

      // Monitoring alerts
      const responseTime = performance.now() - startTime;
      if (route.monitoring.enabled && responseTime > route.monitoring.alertThreshold) {
        this.emit('alert:slowResponse', {
          routeId: route.id,
          responseTime,
          threshold: route.monitoring.alertThreshold
        });
      }

    } catch (error) {
      console.error('❌ Route handler error:', error);
      
      if (route) {
        this.updateMetrics(route.id, 'failure', performance.now() - startTime, false, error.message);
      }

      const statusCode = error.response?.status || 500;
      res.status(statusCode).json({
        error: 'Internal server error',
        message: process.env.NODE_ENV === 'development' ? error.message : undefined
      });
    }
  }

  /**
   * Find matching route
   */
  private findRoute(method: string, path: string): APIRoute | null {
    for (const route of this.routes.values()) {
      if (route.method === method && this.matchPath(route.path, path)) {
        return route;
      }
    }
    return null;
  }

  /**
   * Match path with parameters
   */
  private matchPath(routePath: string, requestPath: string): boolean {
    // Convert route path to regex (e.g., /api/:id -> /api/([^/]+))
    const pattern = routePath.replace(/:([^/]+)/g, '([^/]+)');
    const regex = new RegExp(`^${pattern}$`);
    return regex.test(requestPath);
  }

  /**
   * Authentication middleware
   */
  private authMiddleware = async (req: Request, res: Response, next: NextFunction): Promise<void> => {
    try {
      const token = this.extractToken(req);
      if (!token) {
        return res.status(401).json({ error: 'No token provided' });
      }

      const decoded = jwt.verify(token, this.jwtSecret) as AuthToken;
      req.user = decoded;
      next();
    } catch (error) {
      res.status(401).json({ error: 'Invalid token' });
    }
  };

  /**
   * Authenticate request
   */
  private async authenticate(req: Request, route: APIRoute): Promise<{
    success: boolean;
    error?: string;
    user?: AuthToken;
  }> {
    try {
      const token = this.extractToken(req);
      if (!token) {
        return { success: false, error: 'No token provided' };
      }

      let user: AuthToken;

      switch (route.auth.type) {
        case 'bearer':
          user = jwt.verify(token, this.jwtSecret) as AuthToken;
          break;
        case 'apikey':
          user = await this.validateApiKey(token);
          break;
        case 'oauth':
          user = await this.validateOAuthToken(token);
          break;
        case 'hmac':
          user = await this.validateHmacSignature(req);
          break;
        default:
          return { success: false, error: 'Unsupported auth type' };
      }

      // Check roles
      if (route.auth.roles && route.auth.roles.length > 0) {
        const hasRole = route.auth.roles.some(role => user.roles.includes(role));
        if (!hasRole) {
          return { success: false, error: 'Insufficient permissions' };
        }
      }

      // Check marketplace access
      if (!user.marketplaces.includes(route.marketplace)) {
        return { success: false, error: 'Marketplace access denied' };
      }

      return { success: true, user };
    } catch (error) {
      return { success: false, error: 'Authentication failed' };
    }
  }

  /**
   * Extract token from request
   */
  private extractToken(req: Request): string | null {
    const authHeader = req.headers.authorization;
    if (authHeader && authHeader.startsWith('Bearer ')) {
      return authHeader.substring(7);
    }
    
    return req.headers['x-api-key'] as string || null;
  }

  /**
   * Validate API key
   */
  private async validateApiKey(apiKey: string): Promise<AuthToken> {
    // This would typically check against a database
    // For demo purposes, we'll create a mock user
    return {
      userId: 'api-user',
      roles: ['api'],
      marketplaces: ['all'],
      expiresAt: Date.now() + 24 * 60 * 60 * 1000,
      iat: Date.now()
    };
  }

  /**
   * Validate OAuth token
   */
  private async validateOAuthToken(token: string): Promise<AuthToken> {
    // This would validate the token with the OAuth provider
    // For demo purposes, we'll return a mock user
    return {
      userId: 'oauth-user',
      roles: ['user'],
      marketplaces: ['all'],
      expiresAt: Date.now() + 60 * 60 * 1000,
      iat: Date.now()
    };
  }

  /**
   * Validate HMAC signature
   */
  private async validateHmacSignature(req: Request): Promise<AuthToken> {
    const signature = req.headers['x-signature'] as string;
    const timestamp = req.headers['x-timestamp'] as string;
    
    if (!signature || !timestamp) {
      throw new Error('Missing HMAC signature or timestamp');
    }

    // Verify timestamp (prevent replay attacks)
    const now = Math.floor(Date.now() / 1000);
    const requestTime = parseInt(timestamp);
    if (Math.abs(now - requestTime) > 300) { // 5 minutes
      throw new Error('Request timestamp too old');
    }

    // Calculate expected signature
    const payload = JSON.stringify(req.body) + timestamp;
    const expectedSignature = crypto
      .createHmac('sha256', this.jwtSecret)
      .update(payload)
      .digest('hex');

    if (signature !== expectedSignature) {
      throw new Error('Invalid HMAC signature');
    }

    return {
      userId: 'hmac-user',
      roles: ['webhook'],
      marketplaces: ['all'],
      expiresAt: Date.now() + 60 * 60 * 1000,
      iat: Date.now()
    };
  }

  /**
   * Check rate limit
   */
  private async checkRateLimit(req: Request, route: APIRoute): Promise<{
    allowed: boolean;
    retryAfter?: number;
  }> {
    const key = `rateLimit:${route.id}:${req.ip}`;
    const window = route.rateLimit.windowMs;
    const limit = route.rateLimit.max;

    try {
      const current = await this.redis.get(key);
      const count = current ? parseInt(current) : 0;

      if (count >= limit) {
        const ttl = await this.redis.ttl(key);
        return { allowed: false, retryAfter: ttl };
      }

      // Increment counter
      const pipeline = this.redis.pipeline();
      pipeline.incr(key);
      pipeline.expire(key, Math.ceil(window / 1000));
      await pipeline.exec();

      return { allowed: true };
    } catch (error) {
      console.error('❌ Rate limit check failed:', error);
      return { allowed: true }; // Fail open
    }
  }

  /**
   * Get from cache
   */
  private async getFromCache(req: Request, route: APIRoute): Promise<any> {
    try {
      const cacheKey = this.generateCacheKey(req, route);
      const cached = await this.redis.get(cacheKey);
      
      if (cached) {
        this.updateMetrics(route.id, 'cache', 0, true);
        return JSON.parse(cached);
      }
      
      this.updateMetrics(route.id, 'cache', 0, false);
      return null;
    } catch (error) {
      console.error('❌ Cache get error:', error);
      return null;
    }
  }

  /**
   * Set cache
   */
  private async setCache(req: Request, route: APIRoute, data: any): Promise<void> {
    try {
      const cacheKey = this.generateCacheKey(req, route);
      await this.redis.setex(cacheKey, route.cache.ttl, JSON.stringify(data));
    } catch (error) {
      console.error('❌ Cache set error:', error);
    }
  }

  /**
   * Generate cache key
   */
  private generateCacheKey(req: Request, route: APIRoute): string {
    const parts = [route.id, req.path];
    
    // Add query parameters if specified in cache keys
    if (route.cache.keys.length > 0) {
      for (const key of route.cache.keys) {
        if (req.query[key]) {
          parts.push(`${key}:${req.query[key]}`);
        }
      }
    }

    return `cache:${parts.join(':')}`;
  }

  /**
   * Forward request to target
   */
  private async forwardRequest(req: Request, route: APIRoute): Promise<AxiosResponse> {
    // Build target URL with path parameters
    let targetUrl = route.targetUrl;
    const pathParams = this.extractPathParams(route.path, req.path);
    
    for (const [key, value] of Object.entries(pathParams)) {
      targetUrl = targetUrl.replace(`:${key}`, value as string);
    }

    // Prepare request data
    let requestData = req.body;
    if (route.transform.request) {
      requestData = route.transform.request(requestData);
    }

    // Forward request
    const config = {
      method: req.method.toLowerCase(),
      url: targetUrl,
      data: requestData,
      params: req.query,
      headers: this.forwardHeaders(req),
      timeout: 30000
    };

    return await axios(config as any);
  }

  /**
   * Extract path parameters
   */
  private extractPathParams(routePath: string, requestPath: string): Record<string, string> {
    const routeParts = routePath.split('/');
    const requestParts = requestPath.split('/');
    const params: Record<string, string> = {};

    for (let i = 0; i < routeParts.length; i++) {
      if (routeParts[i].startsWith(':')) {
        const paramName = routeParts[i].substring(1);
        params[paramName] = requestParts[i];
      }
    }

    return params;
  }

  /**
   * Forward headers
   */
  private forwardHeaders(req: Request): Record<string, string> {
    const headers: Record<string, string> = {};
    const allowedHeaders = [
      'content-type',
      'accept',
      'user-agent',
      'x-forwarded-for',
      'x-real-ip'
    ];

    for (const header of allowedHeaders) {
      if (req.headers[header]) {
        headers[header] = req.headers[header] as string;
      }
    }

    return headers;
  }

  /**
   * Log request
   */
  private logRequest(req: Request, res: Response, responseTime: number): void {
    const log: RequestLog = {
      id: `req_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
      routeId: this.findRoute(req.method as any, req.path)?.id || 'unknown',
      method: req.method,
      path: req.path,
      ip: req.ip,
      userAgent: req.get('User-Agent') || '',
      userId: req.user?.userId,
      timestamp: new Date(),
      responseTime,
      statusCode: res.statusCode,
      requestSize: parseInt(req.get('content-length') || '0'),
      responseSize: parseInt(res.get('content-length') || '0'),
      cached: res.get('x-cache') === 'HIT',
      error: res.statusCode >= 400 ? res.statusMessage : undefined
    };

    this.requestLogs.push(log);
    
    // Keep only last 10000 logs
    if (this.requestLogs.length > 10000) {
      this.requestLogs.splice(0, 1000);
    }

    this.emit('request:logged', log);
  }

  /**
   * Update metrics
   */
  private updateMetrics(
    routeId: string, 
    type: 'success' | 'failure' | 'rateLimit' | 'cache',
    responseTime: number = 0,
    cached: boolean = false,
    error?: string
  ): void {
    const metrics = this.metrics.get(routeId);
    if (!metrics) return;

    switch (type) {
      case 'success':
        metrics.totalRequests++;
        metrics.successfulRequests++;
        metrics.lastRequestTime = new Date();
        
        if (cached) {
          metrics.cacheHits++;
        } else {
          metrics.cacheMisses++;
        }

        // Update average response time
        const total = metrics.totalRequests;
        metrics.averageResponseTime = ((metrics.averageResponseTime * (total - 1)) + responseTime) / total;
        break;

      case 'failure':
        metrics.totalRequests++;
        metrics.failedRequests++;
        metrics.lastRequestTime = new Date();
        
        if (error) {
          metrics.errors.push({
            timestamp: new Date(),
            error,
            statusCode: 500
          });

          // Keep only last 100 errors
          if (metrics.errors.length > 100) {
            metrics.errors.splice(0, 10);
          }
        }
        break;

      case 'rateLimit':
        metrics.rateLimitHits++;
        break;

      case 'cache':
        if (cached) {
          metrics.cacheHits++;
        } else {
          metrics.cacheMisses++;
        }
        break;
    }
  }

  /**
   * Get metrics summary
   */
  private getMetricsSummary(): {
    totalRequests: number;
    totalSuccessful: number;
    totalFailed: number;
    averageResponseTime: number;
    cacheHitRate: number;
  } {
    let totalRequests = 0;
    let totalSuccessful = 0;
    let totalFailed = 0;
    let totalResponseTime = 0;
    let totalCacheHits = 0;
    let totalCacheRequests = 0;

    for (const metrics of this.metrics.values()) {
      totalRequests += metrics.totalRequests;
      totalSuccessful += metrics.successfulRequests;
      totalFailed += metrics.failedRequests;
      totalResponseTime += metrics.averageResponseTime * metrics.totalRequests;
      totalCacheHits += metrics.cacheHits;
      totalCacheRequests += metrics.cacheHits + metrics.cacheMisses;
    }

    return {
      totalRequests,
      totalSuccessful,
      totalFailed,
      averageResponseTime: totalRequests > 0 ? totalResponseTime / totalRequests : 0,
      cacheHitRate: totalCacheRequests > 0 ? totalCacheHits / totalCacheRequests : 0
    };
  }

  /**
   * Validate route configuration
   */
  private validateRoute(route: APIRoute): void {
    const required = ['id', 'path', 'method', 'targetUrl', 'marketplace'];
    const missing = required.filter(field => !route[field]);
    
    if (missing.length > 0) {
      throw new Error(`Missing required route fields: ${missing.join(', ')}`);
    }

    if (this.routes.has(route.id)) {
      throw new Error(`Route with id ${route.id} already exists`);
    }
  }

  /**
   * Start metrics collector
   */
  private startMetricsCollector(): void {
    setInterval(() => {
      this.emit('metrics:collected', {
        timestamp: new Date(),
        routes: Array.from(this.metrics.values()),
        summary: this.getMetricsSummary(),
        requestLogs: this.requestLogs.slice(-1000) // Last 1000 requests
      });
    }, 60000); // Every minute
  }

  /**
   * Start the API Gateway server
   */
  public async start(): Promise<void> {
    return new Promise((resolve, reject) => {
      try {
        this.server = this.app.listen(this.port, () => {
          console.log(`🚀 API Gateway started on port ${this.port}`);
          this.emit('server:started', { port: this.port });
          resolve();
        });

        this.server.on('error', (error: Error) => {
          console.error('❌ Server error:', error);
          reject(error);
        });
      } catch (error) {
        reject(error);
      }
    });
  }

  /**
   * Stop the API Gateway server
   */
  public async stop(): Promise<void> {
    return new Promise((resolve) => {
      if (this.server) {
        this.server.close(() => {
          console.log('🛑 API Gateway stopped');
          this.emit('server:stopped');
          resolve();
        });
      } else {
        resolve();
      }
    });
  }

  /**
   * Get route metrics
   */
  public getRouteMetrics(routeId?: string): APIMetrics | APIMetrics[] {
    if (routeId) {
      return this.metrics.get(routeId) || null;
    }
    return Array.from(this.metrics.values());
  }

  /**
   * Get request logs
   */
  public getRequestLogs(filter?: {
    routeId?: string;
    userId?: string;
    statusCode?: number;
    limit?: number;
  }): RequestLog[] {
    let logs = [...this.requestLogs];

    if (filter) {
      if (filter.routeId) {
        logs = logs.filter(log => log.routeId === filter.routeId);
      }
      if (filter.userId) {
        logs = logs.filter(log => log.userId === filter.userId);
      }
      if (filter.statusCode) {
        logs = logs.filter(log => log.statusCode === filter.statusCode);
      }
      if (filter.limit) {
        logs = logs.slice(-filter.limit);
      }
    }

    return logs.sort((a, b) => b.timestamp.getTime() - a.timestamp.getTime());
  }

  /**
   * Clear cache for route
   */
  public async clearCache(routeId?: string): Promise<void> {
    try {
      if (routeId) {
        const pattern = `cache:${routeId}:*`;
        const keys = await this.redis.keys(pattern);
        if (keys.length > 0) {
          await this.redis.del(...keys);
        }
        console.log(`🗑️ Cache cleared for route: ${routeId}`);
      } else {
        const keys = await this.redis.keys('cache:*');
        if (keys.length > 0) {
          await this.redis.del(...keys);
        }
        console.log('🗑️ All cache cleared');
      }
    } catch (error) {
      console.error('❌ Cache clear error:', error);
    }
  }

  /**
   * Generate API documentation
   */
  public generateDocumentation(): {
    title: string;
    version: string;
    routes: Array<{
      id: string;
      method: string;
      path: string;
      description: string;
      authentication: any;
      rateLimit: any;
      cache: any;
    }>;
  } {
    return {
      title: 'MesChain API Gateway',
      version: '3.0.0',
      routes: Array.from(this.routes.values()).map(route => ({
        id: route.id,
        method: route.method,
        path: route.path,
        description: `Proxy to ${route.targetUrl}`,
        authentication: route.auth,
        rateLimit: route.rateLimit,
        cache: route.cache
      }))
    };
  }
}

export default APIGateway;