import { EventEmitter } from 'events';

/**
 * API Gateway Service
 * API ağ geçidi ve yönlendirme servisi
 * G2: Enterprise Integration & Scalability - Component 6/6
 */

export interface APIRoute {
  id: string;
  path: string;
  method: 'GET' | 'POST' | 'PUT' | 'DELETE' | 'PATCH' | 'OPTIONS';
  targetUrl: string;
  isActive: boolean;
  authentication: AuthenticationConfig;
  rateLimit: RateLimitConfig;
  transformation: TransformationConfig;
  caching: CachingConfig;
  monitoring: MonitoringConfig;
}

export interface AuthenticationConfig {
  required: boolean;
  methods: ('API_KEY' | 'BEARER_TOKEN' | 'BASIC' | 'OAUTH2' | 'JWT')[];
  roles?: string[];
  permissions?: string[];
}

export interface RateLimitConfig {
  enabled: boolean;
  requestsPerMinute: number;
  requestsPerHour: number;
  burstLimit: number;
  keyGenerator: 'IP' | 'USER' | 'API_KEY' | 'CUSTOM';
}

export interface TransformationConfig {
  request: RequestTransformation;
  response: ResponseTransformation;
}

export interface RequestTransformation {
  headers: HeaderTransformation[];
  body: BodyTransformation;
  queryParams: QueryTransformation[];
}

export interface ResponseTransformation {
  headers: HeaderTransformation[];
  body: BodyTransformation;
  statusCode?: number;
}

export interface HeaderTransformation {
  action: 'ADD' | 'REMOVE' | 'MODIFY';
  name: string;
  value?: string;
  condition?: string;
}

export interface BodyTransformation {
  enabled: boolean;
  template?: string;
  script?: string;
  format: 'JSON' | 'XML' | 'FORM' | 'CUSTOM';
}

export interface QueryTransformation {
  action: 'ADD' | 'REMOVE' | 'MODIFY';
  name: string;
  value?: string;
  condition?: string;
}

export interface CachingConfig {
  enabled: boolean;
  ttl: number; // seconds
  key: string;
  conditions: CacheCondition[];
  invalidation: InvalidationConfig;
}

export interface CacheCondition {
  field: string;
  operator: 'EQUALS' | 'CONTAINS' | 'STARTS_WITH';
  value: string;
}

export interface InvalidationConfig {
  events: string[];
  patterns: string[];
  automatic: boolean;
}

export interface MonitoringConfig {
  enabled: boolean;
  metrics: string[];
  logging: LoggingConfig;
  alerts: AlertConfig[];
}

export interface LoggingConfig {
  level: 'DEBUG' | 'INFO' | 'WARN' | 'ERROR';
  includeHeaders: boolean;
  includeBody: boolean;
  sensitiveFields: string[];
}

export interface AlertConfig {
  condition: string;
  threshold: number;
  window: number; // minutes
  action: 'EMAIL' | 'WEBHOOK' | 'SMS';
  recipients: string[];
}

export interface LoadBalancingConfig {
  algorithm: 'ROUND_ROBIN' | 'WEIGHTED' | 'LEAST_CONNECTIONS' | 'IP_HASH' | 'RANDOM';
  targets: LoadBalancingTarget[];
  healthCheck: HealthCheckConfig;
  failover: FailoverConfig;
}

export interface LoadBalancingTarget {
  id: string;
  url: string;
  weight: number;
  isHealthy: boolean;
  responseTime: number;
  activeConnections: number;
}

export interface HealthCheckConfig {
  enabled: boolean;
  interval: number; // seconds
  timeout: number; // seconds
  path: string;
  expectedStatus: number;
  failureThreshold: number;
}

export interface FailoverConfig {
  enabled: boolean;
  strategy: 'IMMEDIATE' | 'GRADUAL' | 'CIRCUIT_BREAKER';
  fallbackUrl?: string;
  retryAttempts: number;
  retryDelay: number; // seconds
}

export interface APIMetrics {
  routeId: string;
  totalRequests: number;
  successfulRequests: number;
  failedRequests: number;
  averageResponseTime: number;
  p95ResponseTime: number;
  p99ResponseTime: number;
  errorRate: number;
  throughput: number; // requests per second
  cacheHitRate: number;
  bandwidthUsage: number; // bytes
}

export interface GatewayConfig {
  cors: CORSConfig;
  security: SecurityConfig;
  throttling: ThrottlingConfig;
  middleware: MiddlewareConfig[];
  plugins: PluginConfig[];
}

export interface CORSConfig {
  enabled: boolean;
  origins: string[];
  methods: string[];
  headers: string[];
  credentials: boolean;
  maxAge: number;
}

export interface SecurityConfig {
  headers: SecurityHeader[];
  validation: ValidationConfig;
  sanitization: SanitizationConfig;
}

export interface SecurityHeader {
  name: string;
  value: string;
  condition?: string;
}

export interface ValidationConfig {
  enabled: boolean;
  schemas: SchemaValidation[];
  strictMode: boolean;
}

export interface SchemaValidation {
  path: string;
  method: string;
  requestSchema?: object;
  responseSchema?: object;
}

export interface SanitizationConfig {
  enabled: boolean;
  rules: SanitizationRule[];
}

export interface SanitizationRule {
  field: string;
  action: 'REMOVE' | 'ENCODE' | 'VALIDATE' | 'TRANSFORM';
  pattern?: string;
  replacement?: string;
}

export interface ThrottlingConfig {
  enabled: boolean;
  globalLimit: number; // requests per minute
  perUserLimit: number;
  perIPLimit: number;
  burstLimit: number;
  windowSize: number; // minutes
}

export interface MiddlewareConfig {
  name: string;
  enabled: boolean;
  order: number;
  configuration: Record<string, any>;
}

export interface PluginConfig {
  name: string;
  version: string;
  enabled: boolean;
  configuration: Record<string, any>;
}

export class APIGatewayService extends EventEmitter {
  private routes: Map<string, APIRoute> = new Map();
  private loadBalancers: Map<string, LoadBalancingConfig> = new Map();
  private metrics: Map<string, APIMetrics> = new Map();
  private cache: Map<string, any> = new Map();
  private gatewayConfig: GatewayConfig;
  private rateLimitCounters: Map<string, number> = new Map();
  private metricsInterval: NodeJS.Timeout | null = null;

  constructor() {
    super();
    this.gatewayConfig = this.initializeGatewayConfig();
    this.startMetricsCollection();
    this.setupMiddleware();
  }

  /**
   * API route ekle
   */
  async addRoute(route: Omit<APIRoute, 'id'>): Promise<string> {
    try {
      const id = this.generateId();
      const newRoute: APIRoute = {
        ...route,
        id
      };

      this.validateRoute(newRoute);
      this.routes.set(id, newRoute);
      
      // Initialize metrics for the route
      this.initializeRouteMetrics(id);
      
      this.emit('routeAdded', newRoute);
      console.log(`✅ API route added: ${route.method} ${route.path}`);
      
      return id;
    } catch (error) {
      console.error('❌ Error adding route:', error);
      throw error;
    }
  }

  /**
   * API isteğini işle
   */
  async handleRequest(request: any): Promise<any> {
    const startTime = Date.now();
    const routeMatch = this.findMatchingRoute(request.path, request.method);
    
    if (!routeMatch) {
      throw new Error('Route not found');
    }

    const route = this.routes.get(routeMatch.routeId)!;

    try {
      // Authentication check
      if (route.authentication.required) {
        await this.authenticateRequest(request, route.authentication);
      }

      // Rate limiting
      if (route.rateLimit.enabled) {
        await this.checkRateLimit(request, route.rateLimit);
      }

      // Check cache
      if (route.caching.enabled && request.method === 'GET') {
        const cachedResponse = this.getCachedResponse(request, route.caching);
        if (cachedResponse) {
          this.updateMetrics(route.id, true, Date.now() - startTime, true);
          return cachedResponse;
        }
      }

      // Transform request
      const transformedRequest = this.transformRequest(request, route.transformation.request);

      // Forward request to target
      const response = await this.forwardRequest(transformedRequest, route.targetUrl);

      // Transform response
      const transformedResponse = this.transformResponse(response, route.transformation.response);

      // Cache response if applicable
      if (route.caching.enabled && request.method === 'GET') {
        this.cacheResponse(request, transformedResponse, route.caching);
      }

      // Update metrics
      this.updateMetrics(route.id, response.status < 400, Date.now() - startTime, false);

      return transformedResponse;

    } catch (error) {
      this.updateMetrics(route.id, false, Date.now() - startTime, false);
      throw error;
    }
  }

  /**
   * Route'ı doğrula
   */
  private validateRoute(route: APIRoute): void {
    if (!route.path) {
      throw new Error('Route path is required');
    }

    if (!route.targetUrl) {
      throw new Error('Target URL is required');
    }

    try {
      new URL(route.targetUrl);
    } catch {
      throw new Error('Invalid target URL');
    }
  }

  /**
   * Eşleşen route'u bul
   */
  private findMatchingRoute(path: string, method: string): { routeId: string; params: Record<string, string> } | null {
    for (const [routeId, route] of this.routes) {
      if (!route.isActive) continue;
      if (route.method !== method && route.method !== 'OPTIONS') continue;

      const routeMatch = this.matchPath(path, route.path);
      if (routeMatch) {
        return { routeId, params: routeMatch.params };
      }
    }

    return null;
  }

  /**
   * Path matching
   */
  private matchPath(requestPath: string, routePath: string): { params: Record<string, string> } | null {
    // Simple path matching - in real implementation, use more sophisticated routing
    const requestSegments = requestPath.split('/').filter(s => s);
    const routeSegments = routePath.split('/').filter(s => s);

    if (requestSegments.length !== routeSegments.length) {
      return null;
    }

    const params: Record<string, string> = {};

    for (let i = 0; i < routeSegments.length; i++) {
      const routeSegment = routeSegments[i];
      const requestSegment = requestSegments[i];

      if (routeSegment.startsWith(':')) {
        // Parameter segment
        const paramName = routeSegment.slice(1);
        params[paramName] = requestSegment;
      } else if (routeSegment !== requestSegment) {
        return null;
      }
    }

    return { params };
  }

  /**
   * İsteği doğrula
   */
  private async authenticateRequest(request: any, authConfig: AuthenticationConfig): Promise<void> {
    if (!authConfig.required) return;

    // Simple authentication check - in real implementation, integrate with auth providers
    const authHeader = request.headers['authorization'];
    
    if (!authHeader) {
      throw new Error('Authorization header required');
    }

    // Mock authentication validation
    if (authConfig.methods.includes('BEARER_TOKEN')) {
      if (!authHeader.startsWith('Bearer ')) {
        throw new Error('Invalid authorization format');
      }
      
      const token = authHeader.slice(7);
      if (!this.validateToken(token)) {
        throw new Error('Invalid token');
      }
    }

    // Role/permission checks would be implemented here
    console.log('✅ Request authenticated');
  }

  /**
   * Token doğrula
   */
  private validateToken(token: string): boolean {
    // Mock token validation
    return token.length > 10;
  }

  /**
   * Rate limit kontrolü
   */
  private async checkRateLimit(request: any, rateLimitConfig: RateLimitConfig): Promise<void> {
    if (!rateLimitConfig.enabled) return;

    const key = this.generateRateLimitKey(request, rateLimitConfig.keyGenerator);
    const now = Date.now();
    const windowStart = Math.floor(now / 60000) * 60000; // 1-minute window
    const counterKey = `${key}:${windowStart}`;

    const currentCount = this.rateLimitCounters.get(counterKey) || 0;

    if (currentCount >= rateLimitConfig.requestsPerMinute) {
      throw new Error('Rate limit exceeded');
    }

    this.rateLimitCounters.set(counterKey, currentCount + 1);

    // Clean up old counters
    setTimeout(() => {
      this.rateLimitCounters.delete(counterKey);
    }, 60000);
  }

  /**
   * Rate limit key oluştur
   */
  private generateRateLimitKey(request: any, keyGenerator: string): string {
    switch (keyGenerator) {
      case 'IP':
        return request.ip || 'unknown';
      case 'USER':
        return request.user?.id || 'anonymous';
      case 'API_KEY':
        return request.headers['x-api-key'] || 'no-key';
      default:
        return 'global';
    }
  }

  /**
   * Cache'den response al
   */
  private getCachedResponse(request: any, cachingConfig: CachingConfig): any | null {
    const cacheKey = this.generateCacheKey(request, cachingConfig.key);
    const cached = this.cache.get(cacheKey);

    if (cached && cached.expiry > Date.now()) {
      return cached.data;
    }

    if (cached && cached.expiry <= Date.now()) {
      this.cache.delete(cacheKey);
    }

    return null;
  }

  /**
   * Response'u cache'le
   */
  private cacheResponse(request: any, response: any, cachingConfig: CachingConfig): void {
    const cacheKey = this.generateCacheKey(request, cachingConfig.key);
    const expiry = Date.now() + (cachingConfig.ttl * 1000);

    this.cache.set(cacheKey, {
      data: response,
      expiry
    });
  }

  /**
   * Cache key oluştur
   */
  private generateCacheKey(request: any, keyTemplate: string): string {
    // Simple cache key generation
    return `${request.method}:${request.path}:${JSON.stringify(request.query || {})}`;
  }

  /**
   * İsteği dönüştür
   */
  private transformRequest(request: any, transformation: RequestTransformation): any {
    const transformed = { ...request };

    // Header transformations
    for (const headerTransform of transformation.headers) {
      switch (headerTransform.action) {
        case 'ADD':
          transformed.headers[headerTransform.name] = headerTransform.value;
          break;
        case 'REMOVE':
          delete transformed.headers[headerTransform.name];
          break;
        case 'MODIFY':
          if (transformed.headers[headerTransform.name]) {
            transformed.headers[headerTransform.name] = headerTransform.value;
          }
          break;
      }
    }

    // Body transformation
    if (transformation.body.enabled && transformation.body.template) {
      transformed.body = this.applyTemplate(request.body, transformation.body.template);
    }

    // Query parameter transformations
    for (const queryTransform of transformation.queryParams) {
      if (!transformed.query) transformed.query = {};
      
      switch (queryTransform.action) {
        case 'ADD':
          transformed.query[queryTransform.name] = queryTransform.value;
          break;
        case 'REMOVE':
          delete transformed.query[queryTransform.name];
          break;
        case 'MODIFY':
          if (transformed.query[queryTransform.name]) {
            transformed.query[queryTransform.name] = queryTransform.value;
          }
          break;
      }
    }

    return transformed;
  }

  /**
   * Response'u dönüştür
   */
  private transformResponse(response: any, transformation: ResponseTransformation): any {
    const transformed = { ...response };

    // Header transformations
    for (const headerTransform of transformation.headers) {
      if (!transformed.headers) transformed.headers = {};
      
      switch (headerTransform.action) {
        case 'ADD':
          transformed.headers[headerTransform.name] = headerTransform.value;
          break;
        case 'REMOVE':
          delete transformed.headers[headerTransform.name];
          break;
        case 'MODIFY':
          if (transformed.headers[headerTransform.name]) {
            transformed.headers[headerTransform.name] = headerTransform.value;
          }
          break;
      }
    }

    // Body transformation
    if (transformation.body.enabled && transformation.body.template) {
      transformed.body = this.applyTemplate(response.body, transformation.body.template);
    }

    // Status code transformation
    if (transformation.statusCode) {
      transformed.status = transformation.statusCode;
    }

    return transformed;
  }

  /**
   * Template uygula
   */
  private applyTemplate(data: any, template: string): any {
    // Simple template application - in real implementation, use a template engine
    let result = template;
    
    if (typeof data === 'object') {
      for (const [key, value] of Object.entries(data)) {
        const placeholder = `{{${key}}}`;
        result = result.replace(new RegExp(placeholder, 'g'), String(value));
      }
    }

    try {
      return JSON.parse(result);
    } catch {
      return result;
    }
  }

  /**
   * İsteği ilet
   */
  private async forwardRequest(request: any, targetUrl: string): Promise<any> {
    // Mock HTTP request forwarding
    console.log(`🔄 Forwarding ${request.method} request to: ${targetUrl}`);
    
    // Simulate network delay
    await new Promise(resolve => setTimeout(resolve, Math.random() * 200 + 50));
    
    // Mock response
    const mockResponse = {
      status: Math.random() > 0.1 ? 200 : 500, // 90% success rate
      headers: {
        'content-type': 'application/json',
        'x-gateway-processed': 'true'
      },
      body: {
        success: true,
        data: { processed: true, timestamp: new Date() },
        request_id: this.generateId()
      }
    };

    return mockResponse;
  }

  /**
   * Route metrikleri başlat
   */
  private initializeRouteMetrics(routeId: string): void {
    const metrics: APIMetrics = {
      routeId,
      totalRequests: 0,
      successfulRequests: 0,
      failedRequests: 0,
      averageResponseTime: 0,
      p95ResponseTime: 0,
      p99ResponseTime: 0,
      errorRate: 0,
      throughput: 0,
      cacheHitRate: 0,
      bandwidthUsage: 0
    };

    this.metrics.set(routeId, metrics);
  }

  /**
   * Metrikleri güncelle
   */
  private updateMetrics(routeId: string, success: boolean, responseTime: number, fromCache: boolean): void {
    const metrics = this.metrics.get(routeId);
    if (!metrics) return;

    metrics.totalRequests++;
    
    if (success) {
      metrics.successfulRequests++;
    } else {
      metrics.failedRequests++;
    }

    // Update response time metrics
    const totalResponseTime = metrics.averageResponseTime * (metrics.totalRequests - 1) + responseTime;
    metrics.averageResponseTime = totalResponseTime / metrics.totalRequests;

    // Update error rate
    metrics.errorRate = (metrics.failedRequests / metrics.totalRequests) * 100;

    // Update cache hit rate
    if (fromCache) {
      const totalCacheHits = metrics.cacheHitRate * (metrics.totalRequests - 1) / 100;
      metrics.cacheHitRate = ((totalCacheHits + 1) / metrics.totalRequests) * 100;
    }

    // Throughput will be calculated by metrics collection interval
    
    this.emit('metricsUpdated', { routeId, metrics });
  }

  /**
   * Load balancer ekle
   */
  async addLoadBalancer(routeId: string, config: LoadBalancingConfig): Promise<void> {
    const route = this.routes.get(routeId);
    if (!route) {
      throw new Error('Route not found');
    }

    this.loadBalancers.set(routeId, config);
    
    if (config.healthCheck.enabled) {
      this.startHealthChecks(routeId);
    }

    this.emit('loadBalancerAdded', { routeId, config });
    console.log(`✅ Load balancer added for route: ${route.path}`);
  }

  /**
   * Health check'leri başlat
   */
  private startHealthChecks(routeId: string): void {
    const config = this.loadBalancers.get(routeId);
    if (!config) return;

    setInterval(async () => {
      for (const target of config.targets) {
        try {
          const startTime = Date.now();
          // Mock health check
          await new Promise(resolve => setTimeout(resolve, Math.random() * 100));
          
          const responseTime = Date.now() - startTime;
          target.responseTime = responseTime;
          target.isHealthy = responseTime < config.healthCheck.timeout;
          
        } catch (error) {
          target.isHealthy = false;
        }
      }
    }, config.healthCheck.interval * 1000);
  }

  /**
   * En iyi target'ı seç
   */
  private selectTarget(config: LoadBalancingConfig): LoadBalancingTarget | null {
    const healthyTargets = config.targets.filter(t => t.isHealthy);
    
    if (healthyTargets.length === 0) {
      return null;
    }

    switch (config.algorithm) {
      case 'ROUND_ROBIN':
        return this.selectRoundRobin(healthyTargets);
      case 'WEIGHTED':
        return this.selectWeighted(healthyTargets);
      case 'LEAST_CONNECTIONS':
        return this.selectLeastConnections(healthyTargets);
      case 'RANDOM':
        return healthyTargets[Math.floor(Math.random() * healthyTargets.length)];
      default:
        return healthyTargets[0];
    }
  }

  /**
   * Round robin seçimi
   */
  private selectRoundRobin(targets: LoadBalancingTarget[]): LoadBalancingTarget {
    // Simple round robin - in real implementation, maintain state
    return targets[Math.floor(Date.now() / 1000) % targets.length];
  }

  /**
   * Weighted seçim
   */
  private selectWeighted(targets: LoadBalancingTarget[]): LoadBalancingTarget {
    const totalWeight = targets.reduce((sum, target) => sum + target.weight, 0);
    let random = Math.random() * totalWeight;
    
    for (const target of targets) {
      random -= target.weight;
      if (random <= 0) {
        return target;
      }
    }
    
    return targets[0];
  }

  /**
   * En az bağlantılı seçim
   */
  private selectLeastConnections(targets: LoadBalancingTarget[]): LoadBalancingTarget {
    return targets.reduce((min, target) => 
      target.activeConnections < min.activeConnections ? target : min
    );
  }

  /**
   * Gateway konfigürasyonunu başlat
   */
  private initializeGatewayConfig(): GatewayConfig {
    return {
      cors: {
        enabled: true,
        origins: ['*'],
        methods: ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
        headers: ['Content-Type', 'Authorization', 'X-API-Key'],
        credentials: false,
        maxAge: 86400
      },
      security: {
        headers: [
          { name: 'X-Content-Type-Options', value: 'nosniff' },
          { name: 'X-Frame-Options', value: 'DENY' },
          { name: 'X-XSS-Protection', value: '1; mode=block' }
        ],
        validation: {
          enabled: true,
          schemas: [],
          strictMode: false
        },
        sanitization: {
          enabled: true,
          rules: [
            { field: 'script', action: 'REMOVE' },
            { field: 'iframe', action: 'REMOVE' }
          ]
        }
      },
      throttling: {
        enabled: true,
        globalLimit: 10000,
        perUserLimit: 1000,
        perIPLimit: 500,
        burstLimit: 100,
        windowSize: 1
      },
      middleware: [
        { name: 'cors', enabled: true, order: 1, configuration: {} },
        { name: 'security', enabled: true, order: 2, configuration: {} },
        { name: 'rate-limiting', enabled: true, order: 3, configuration: {} },
        { name: 'authentication', enabled: true, order: 4, configuration: {} },
        { name: 'logging', enabled: true, order: 5, configuration: {} }
      ],
      plugins: []
    };
  }

  /**
   * Middleware kurulumu
   */
  private setupMiddleware(): void {
    const middleware = this.gatewayConfig.middleware.sort((a, b) => a.order - b.order);
    
    for (const mw of middleware) {
      if (mw.enabled) {
        console.log(`🔧 Middleware enabled: ${mw.name} (Order: ${mw.order})`);
      }
    }
  }

  /**
   * Metrikleri toplama başlat
   */
  private startMetricsCollection(): void {
    this.metricsInterval = setInterval(() => {
      this.collectMetrics();
    }, 60000); // Her dakika
  }

  /**
   * Metrikleri topla
   */
  private collectMetrics(): void {
    const now = Date.now();
    
    for (const [routeId, metrics] of this.metrics) {
      // Calculate throughput (requests per second)
      metrics.throughput = metrics.totalRequests / 60; // Assuming 1-minute window

      // Mock P95 and P99 calculations
      metrics.p95ResponseTime = metrics.averageResponseTime * 1.5;
      metrics.p99ResponseTime = metrics.averageResponseTime * 2;

      // Calculate bandwidth usage (mock)
      metrics.bandwidthUsage = metrics.totalRequests * 1024; // 1KB per request average
    }

    this.emit('metricsCollected', {
      timestamp: now,
      routes: this.metrics.size,
      totalRequests: Array.from(this.metrics.values()).reduce((sum, m) => sum + m.totalRequests, 0)
    });
  }

  /**
   * Plugin ekle
   */
  async addPlugin(plugin: PluginConfig): Promise<void> {
    this.gatewayConfig.plugins.push(plugin);
    
    if (plugin.enabled) {
      await this.loadPlugin(plugin);
    }

    this.emit('pluginAdded', plugin);
    console.log(`🔌 Plugin added: ${plugin.name} v${plugin.version}`);
  }

  /**
   * Plugin yükle
   */
  private async loadPlugin(plugin: PluginConfig): Promise<void> {
    // Mock plugin loading
    console.log(`🔄 Loading plugin: ${plugin.name}`);
    
    await new Promise(resolve => setTimeout(resolve, 500));
    
    console.log(`✅ Plugin loaded: ${plugin.name}`);
  }

  /**
   * ID oluştur
   */
  private generateId(): string {
    return 'gw_' + Math.random().toString(36).substr(2, 9);
  }

  // Public getter methods
  getRoutes(): APIRoute[] {
    return Array.from(this.routes.values());
  }

  getRoute(id: string): APIRoute | undefined {
    return this.routes.get(id);
  }

  getMetrics(): APIMetrics[] {
    return Array.from(this.metrics.values());
  }

  getRouteMetrics(routeId: string): APIMetrics | undefined {
    return this.metrics.get(routeId);
  }

  getGatewayConfig(): GatewayConfig {
    return { ...this.gatewayConfig };
  }

  /**
   * Route'u güncelle
   */
  async updateRoute(routeId: string, updates: Partial<APIRoute>): Promise<void> {
    const route = this.routes.get(routeId);
    if (!route) {
      throw new Error('Route not found');
    }

    const updatedRoute = { ...route, ...updates };
    this.validateRoute(updatedRoute);
    
    this.routes.set(routeId, updatedRoute);
    this.emit('routeUpdated', { routeId, updates });
    
    console.log(`✅ Route updated: ${route.path}`);
  }

  /**
   * Route'u sil
   */
  async deleteRoute(routeId: string): Promise<void> {
    const route = this.routes.get(routeId);
    if (!route) {
      throw new Error('Route not found');
    }

    this.routes.delete(routeId);
    this.metrics.delete(routeId);
    this.loadBalancers.delete(routeId);

    this.emit('routeDeleted', { routeId, route });
    console.log(`🗑️ Route deleted: ${route.path}`);
  }

  /**
   * Cache'i temizle
   */
  clearCache(pattern?: string): void {
    if (pattern) {
      for (const [key] of this.cache) {
        if (key.includes(pattern)) {
          this.cache.delete(key);
        }
      }
    } else {
      this.cache.clear();
    }

    this.emit('cacheCleared', { pattern });
    console.log(`🧹 Cache cleared${pattern ? ` (pattern: ${pattern})` : ''}`);
  }

  /**
   * Kaynakları temizle
   */
  dispose(): void {
    if (this.metricsInterval) {
      clearInterval(this.metricsInterval);
      this.metricsInterval = null;
    }

    this.routes.clear();
    this.loadBalancers.clear();
    this.metrics.clear();
    this.cache.clear();
    this.rateLimitCounters.clear();
    this.removeAllListeners();

    console.log('🧹 APIGatewayService disposed');
  }
}

export default APIGatewayService; 