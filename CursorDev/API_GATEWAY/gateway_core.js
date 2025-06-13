/**
 * @file gateway_core.js
 * @description Core API Gateway functionality for MesChain API Gateway
 * @version 1.0.0
 * @author Cursor AI Team
 * @date June 13, 2025
 */

const express = require('express');
const helmet = require('helmet');
const cors = require('cors');
const compression = require('compression');
const { v4: uuidv4 } = require('uuid');
const morgan = require('morgan');
const Redis = require('redis');
const prometheus = require('prom-client');

// Load internal modules
const OAuth2Provider = require('./oauth2_provider');
const JWTSecurityProvider = require('./jwt_security_provider');
const AdvancedRateLimiter = require('./advanced_rate_limiter');
const ServiceMeshIntegration = require('./service_mesh_integration');

/**
 * Secure API Gateway Core
 * Enterprise-grade API Gateway with OAuth 2.0, JWT, Rate Limiting, and Service Mesh integration
 */
class GatewayCore {
  constructor(options = {}) {
    this.app = express();
    this.port = options.port || process.env.API_GATEWAY_PORT || 3000;
    this.environment = options.environment || process.env.NODE_ENV || 'production';
    this.routes = new Map();
    
    // Initialize Redis connection
    this.redis = this._initializeRedis(options.redisOptions);
    
    // Initialize metrics collection
    this.metrics = this._initializeMetrics();
    
    // Initialize security modules
    this.oauth = new OAuth2Provider({ 
      redis: this.redis,
      tokenSecret: options.tokenSecret || process.env.TOKEN_SECRET
    });
    
    this.jwt = new JWTSecurityProvider({
      redis: this.redis,
      keyPath: options.keyPath || './keys',
      issuer: options.issuer || 'meschain-api-gateway',
      audience: options.audience || 'meschain-clients'
    });
    
    this.rateLimiter = new AdvancedRateLimiter({
      redis: this.redis,
      defaultLimit: options.defaultRateLimit || 100,
      defaultWindow: options.rateWindow || 60,
      sensitiveRoutes: options.sensitiveRoutes || ['/api/auth', '/api/user', '/api/admin'],
      ipWhitelist: options.ipWhitelist || []
    });
    
    this.serviceMesh = new ServiceMeshIntegration({
      redis: this.redis,
      meshType: options.meshType || 'istio',
      serviceName: options.serviceName || 'api-gateway',
      namespace: options.namespace || 'default',
      prometheus: this.metrics
    });
    
    console.log('🚀 Gateway Core initialized');
  }

  /**
   * Initialize Redis connection
   * @param {Object} options - Redis options
   * @returns {Object|null} - Redis client
   * @private
   */
  _initializeRedis(options = {}) {
    try {
      const redisUrl = options.url || process.env.REDIS_URL || 'redis://localhost:6379';
      const redis = Redis.createClient({ url: redisUrl });
      
      redis.on('error', (err) => {
        console.error('Redis connection error:', err);
      });
      
      redis.connect().then(() => {
        console.log('✅ Connected to Redis');
      }).catch(err => {
        console.error('❌ Redis connection failed:', err);
      });
      
      return redis;
    } catch (error) {
      console.error('Redis initialization error:', error);
      return null;
    }
  }

  /**
   * Initialize metrics collection
   * @returns {Object} - Prometheus metrics
   * @private
   */
  _initializeMetrics() {
    // Initialize Prometheus metrics registry
    const registry = new prometheus.Registry();
    
    // Add default metrics
    prometheus.collectDefaultMetrics({ register: registry });
    
    // Create custom metrics
    const metrics = {
      registry,
      httpRequestDuration: new prometheus.Histogram({
        name: 'http_request_duration_seconds',
        help: 'HTTP request duration in seconds',
        labelNames: ['method', 'route', 'status_code'],
        buckets: [0.01, 0.05, 0.1, 0.5, 1, 2, 5, 10]
      }),
      httpRequestCounter: new prometheus.Counter({
        name: 'http_requests_total',
        help: 'Total number of HTTP requests',
        labelNames: ['method', 'route', 'status_code']
      }),
      httpErrorCounter: new prometheus.Counter({
        name: 'http_errors_total',
        help: 'Total number of HTTP errors',
        labelNames: ['method', 'route', 'status_code', 'error_type']
      }),
      activeConnections: new prometheus.Gauge({
        name: 'http_active_connections',
        help: 'Number of active connections'
      }),
      authFailures: new prometheus.Counter({
        name: 'auth_failures_total',
        help: 'Total number of authentication failures',
        labelNames: ['auth_type', 'reason']
      }),
      rateLimitedRequests: new prometheus.Counter({
        name: 'rate_limited_requests_total',
        help: 'Total number of rate limited requests',
        labelNames: ['client_ip', 'route']
      }),
      serviceCallCounter: new prometheus.Counter({
        name: 'service_calls_total',
        help: 'Total number of service calls',
        labelNames: ['service', 'status']
      }),
      serviceCallDuration: new prometheus.Histogram({
        name: 'service_call_duration_seconds',
        help: 'Service call duration in seconds',
        labelNames: ['service'],
        buckets: [0.01, 0.05, 0.1, 0.5, 1, 2, 5, 10]
      }),
      circuitBreakerEvents: new prometheus.Counter({
        name: 'circuit_breaker_events_total',
        help: 'Total number of circuit breaker events',
        labelNames: ['service', 'event']
      })
    };
    
    // Register custom metrics
    registry.registerMetric(metrics.httpRequestDuration);
    registry.registerMetric(metrics.httpRequestCounter);
    registry.registerMetric(metrics.httpErrorCounter);
    registry.registerMetric(metrics.activeConnections);
    registry.registerMetric(metrics.authFailures);
    registry.registerMetric(metrics.rateLimitedRequests);
    registry.registerMetric(metrics.serviceCallCounter);
    registry.registerMetric(metrics.serviceCallDuration);
    registry.registerMetric(metrics.circuitBreakerEvents);
    
    return metrics;
  }

  /**
   * Configure and initialize the API Gateway
   * @returns {Promise<void>}
   */
  async initialize() {
    console.log('⚙️ Initializing Gateway Core...');
    
    // Setup basic middleware
    this._setupBaseMiddleware();
    
    // Setup security middleware
    this._setupSecurityMiddleware();
    
    // Setup routes
    this._setupBaseRoutes();
    
    // Initialize service mesh
    await this.serviceMesh.initialize();
    
    console.log('✅ Gateway Core initialization complete');
  }

  /**
   * Setup basic middleware
   * @private
   */
  _setupBaseMiddleware() {
    // Parse JSON body
    this.app.use(express.json({ limit: '10mb' }));
    this.app.use(express.urlencoded({ extended: true, limit: '10mb' }));
    
    // Add request ID
    this.app.use((req, res, next) => {
      req.id = req.headers['x-request-id'] || uuidv4();
      res.setHeader('X-Request-ID', req.id);
      next();
    });
    
    // Compression
    this.app.use(compression());
    
    // Logging
    this.app.use(morgan(':method :url :status :response-time ms - :req[X-Request-ID]'));
    
    // Track connections
    this.app.use((req, res, next) => {
      this.metrics.activeConnections.inc();
      res.on('finish', () => {
        this.metrics.activeConnections.dec();
      });
      next();
    });
    
    // Performance metrics
    this.app.use((req, res, next) => {
      const start = Date.now();
      
      res.on('finish', () => {
        const duration = (Date.now() - start) / 1000;
        const route = req.route ? req.baseUrl + req.route.path : req.path;
        
        this.metrics.httpRequestCounter.inc({
          method: req.method,
          route: route,
          status_code: res.statusCode
        });
        
        this.metrics.httpRequestDuration.observe(
          { method: req.method, route: route, status_code: res.statusCode },
          duration
        );
        
        if (res.statusCode >= 400) {
          this.metrics.httpErrorCounter.inc({
            method: req.method,
            route: route,
            status_code: res.statusCode,
            error_type: res.statusCode >= 500 ? 'server_error' : 'client_error'
          });
        }
      });
      
      next();
    });
  }

  /**
   * Setup security middleware
   * @private
   */
  _setupSecurityMiddleware() {
    // Helmet for security headers
    this.app.use(helmet({
      contentSecurityPolicy: {
        directives: {
          defaultSrc: ["'self'"],
          scriptSrc: ["'self'"],
          styleSrc: ["'self'", "'unsafe-inline'"],
          imgSrc: ["'self'", "data:"],
          connectSrc: ["'self'"],
          fontSrc: ["'self'"],
          objectSrc: ["'none'"],
          mediaSrc: ["'self'"],
          frameSrc: ["'none'"]
        }
      },
      crossOriginEmbedderPolicy: true,
      crossOriginOpenerPolicy: true,
      crossOriginResourcePolicy: { policy: 'same-site' },
      dnsPrefetchControl: { allow: false },
      expectCt: { maxAge: 86400, enforce: true },
      frameguard: { action: 'deny' },
      hsts: {
        maxAge: 31536000,
        includeSubDomains: true,
        preload: true
      },
      ieNoOpen: true,
      noSniff: true,
      originAgentCluster: true,
      permittedCrossDomainPolicies: { permittedPolicies: 'none' },
      referrerPolicy: { policy: 'strict-origin-when-cross-origin' },
      xssFilter: true
    }));
    
    // CORS configuration
    this.app.use(cors({
      origin: (origin, callback) => {
        const allowedOrigins = [
          'https://admin.meschain.com',
          'https://app.meschain.com',
          'https://dashboard.meschain.com'
        ];
        
        // Allow development origins in dev environment
        if (this.environment === 'development') {
          allowedOrigins.push('http://localhost:3000', 'http://localhost:8080');
        }
        
        if (!origin || allowedOrigins.includes(origin)) {
          callback(null, true);
        } else {
          callback(new Error('Not allowed by CORS'));
        }
      },
      methods: ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
      allowedHeaders: ['Content-Type', 'Authorization', 'X-API-Key', 'X-Request-ID'],
      exposedHeaders: ['X-Request-ID', 'X-RateLimit-Limit', 'X-RateLimit-Remaining', 'X-RateLimit-Reset'],
      credentials: true,
      maxAge: 86400
    }));
  }

  /**
   * Setup base routes
   * @private
   */
  _setupBaseRoutes() {
    // Health check endpoint
    this.app.get('/health', (req, res) => {
      res.json({
        status: 'healthy',
        timestamp: new Date().toISOString(),
        version: '1.0.0',
        environment: this.environment
      });
    });
    
    // Metrics endpoint
    this.app.get('/metrics', async (req, res) => {
      try {
        const metrics = await this.metrics.registry.metrics();
        res.set('Content-Type', this.metrics.registry.contentType);
        res.send(metrics);
      } catch (error) {
        res.status(500).json({ error: 'Failed to collect metrics' });
      }
    });
    
    // 404 handler
    this.app.use((req, res) => {
      res.status(404).json({
        error: 'Resource not found',
        message: `The requested resource '${req.path}' was not found`
      });
    });
    
    // Error handler
    this.app.use((err, req, res, next) => {
      console.error('Gateway error:', err);
      
      const statusCode = err.statusCode || 500;
      const errorMessage = this.environment === 'production' && statusCode === 500
        ? 'Internal server error'
        : err.message || 'Unknown error';
      
      res.status(statusCode).json({
        error: err.name || 'Error',
        message: errorMessage,
        request_id: req.id
      });
    });
  }

  /**
   * Start the API Gateway server
   * @returns {Promise<void>}
   */
  async start() {
    return new Promise((resolve) => {
      this.server = this.app.listen(this.port, () => {
        console.log(`🚀 API Gateway running on port ${this.port}`);
        resolve();
      });
    });
  }

  /**
   * Stop the API Gateway server
   * @returns {Promise<void>}
   */
  async stop() {
    return new Promise((resolve, reject) => {
      if (!this.server) {
        resolve();
        return;
      }
      
      this.server.close((err) => {
        if (err) {
          reject(err);
          return;
        }
        
        console.log('API Gateway stopped');
        resolve();
      });
    });
  }
  
  /**
   * Get Express app instance
   * @returns {Object} - Express app
   */
  getApp() {
    return this.app;
  }
}

module.exports = GatewayCore;
