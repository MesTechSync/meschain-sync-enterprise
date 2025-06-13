// 🌐 CURSOR TEAM PHASE 3: API GATEWAY IMPLEMENTATION
// Centralized API management, routing, authentication, and rate limiting
// Enterprise-grade gateway with Kong.js and Express.js

const express = require('express');
const httpProxy = require('http-proxy-middleware');
const rateLimit = require('express-rate-limit');
const jwt = require('jsonwebtoken');
const Redis = require('redis');
const prometheus = require('prom-client');
const cors = require('cors');
const helmet = require('helmet');
const compression = require('compression');

/**
 * 🚀 MESCHAIN API GATEWAY - ENTERPRISE GRADE
 * Features: Load balancing, Authentication, Rate limiting, Monitoring
 * Target: Single entry point for all microservices
 */
class MesChainAPIGateway {
    constructor(options = {}) {
        this.app = express();
        this.port = options.port || 8080;
        this.services = new Map();
        this.routes = new Map();
        this.middleware = [];
        this.healthChecks = new Map();
        
        // Redis for caching and rate limiting
        this.redis = null;
        
        // Metrics collection
        this.metrics = {
            httpRequestsTotal: new prometheus.Counter({
                name: 'gateway_http_requests_total',
                help: 'Total number of HTTP requests',
                labelNames: ['method', 'route', 'status_code', 'service']
            }),
            httpRequestDuration: new prometheus.Histogram({
                name: 'gateway_http_request_duration_seconds',
                help: 'Duration of HTTP requests in seconds',
                labelNames: ['method', 'route', 'service'],
                buckets: [0.1, 0.5, 1, 2, 5, 10]
            }),
            activeConnections: new prometheus.Gauge({
                name: 'gateway_active_connections',
                help: 'Number of active connections'
            })
        };

        // Service registry
        this.serviceRegistry = {
            // Core Business Services
            'user-management': {
                name: 'user-management',
                baseUrl: 'http://user-management:3001',
                healthCheck: '/health',
                timeout: 10000,
                retries: 3,
                circuitBreaker: {
                    failureThreshold: 5,
                    timeout: 60000,
                    resetTimeout: 30000
                },
                loadBalancer: {
                    strategy: 'round-robin',
                    instances: [
                        'http://user-management-1:3001',
                        'http://user-management-2:3001'
                    ]
                }
            },
            'order-processing': {
                name: 'order-processing',
                baseUrl: 'http://order-processing:3002',
                healthCheck: '/health',
                timeout: 15000,
                retries: 3,
                circuitBreaker: {
                    failureThreshold: 3,
                    timeout: 30000,
                    resetTimeout: 15000
                },
                loadBalancer: {
                    strategy: 'least-connections',
                    instances: [
                        'http://order-processing-1:3002',
                        'http://order-processing-2:3002',
                        'http://order-processing-3:3002'
                    ]
                }
            },
            'product-catalog': {
                name: 'product-catalog',
                baseUrl: 'http://product-catalog:3003',
                healthCheck: '/health',
                timeout: 8000,
                retries: 2,
                circuitBreaker: {
                    failureThreshold: 5,
                    timeout: 45000,
                    resetTimeout: 20000
                }
            },
            'inventory-service': {
                name: 'inventory-service',
                baseUrl: 'http://inventory-service:3004',
                healthCheck: '/health',
                timeout: 5000,
                retries: 3
            },
            'payment-service': {
                name: 'payment-service',
                baseUrl: 'http://payment-service:3005',
                healthCheck: '/health',
                timeout: 20000,
                retries: 2,
                circuitBreaker: {
                    failureThreshold: 2,
                    timeout: 120000,
                    resetTimeout: 60000
                }
            },
            'notification-service': {
                name: 'notification-service',
                baseUrl: 'http://notification-service:3006',
                healthCheck: '/health',
                timeout: 10000,
                retries: 2
            },
            // Marketplace Integration Services
            'trendyol-integration': {
                name: 'trendyol-integration',
                baseUrl: 'http://trendyol-integration:3101',
                healthCheck: '/health',
                timeout: 30000,
                retries: 3
            },
            'amazon-integration': {
                name: 'amazon-integration',
                baseUrl: 'http://amazon-integration:3102',
                healthCheck: '/health',
                timeout: 30000,
                retries: 3
            },
            'n11-integration': {
                name: 'n11-integration',
                baseUrl: 'http://n11-integration:3103',
                healthCheck: '/health',
                timeout: 25000,
                retries: 2
            },
            'hepsiburada-integration': {
                name: 'hepsiburada-integration',
                baseUrl: 'http://hepsiburada-integration:3104',
                healthCheck: '/health',
                timeout: 25000,
                retries: 2
            },
            'ozon-integration': {
                name: 'ozon-integration',
                baseUrl: 'http://ozon-integration:3105',
                healthCheck: '/health',
                timeout: 35000,
                retries: 3
            },
            // Platform Services
            'auth-service': {
                name: 'auth-service',
                baseUrl: 'http://auth-service:3201',
                healthCheck: '/health',
                timeout: 5000,
                retries: 3,
                loadBalancer: {
                    strategy: 'round-robin',
                    instances: [
                        'http://auth-service-1:3201',
                        'http://auth-service-2:3201',
                        'http://auth-service-3:3201'
                    ]
                }
            },
            'analytics-service': {
                name: 'analytics-service',
                baseUrl: 'http://analytics-service:3301',
                healthCheck: '/health',
                timeout: 30000,
                retries: 2
            },
            'reporting-service': {
                name: 'reporting-service',
                baseUrl: 'http://reporting-service:3302',
                healthCheck: '/health',
                timeout: 60000,
                retries: 1
            },
            'file-storage': {
                name: 'file-storage',
                baseUrl: 'http://file-storage:3401',
                healthCheck: '/health',
                timeout: 15000,
                retries: 2
            },
            'search-service': {
                name: 'search-service',
                baseUrl: 'http://search-service:3501',
                healthCheck: '/health',
                timeout: 10000,
                retries: 2
            }
        };

        this.initialize();
    }

    /**
     * 🔧 Initialize API Gateway
     */
    async initialize() {
        console.log('🚀 Initializing MesChain API Gateway...');
        
        // Connect to Redis
        await this.connectRedis();
        
        // Setup middleware
        this.setupMiddleware();
        
        // Setup routes
        this.setupRoutes();
        
        // Setup health monitoring
        this.setupHealthMonitoring();
        
        // Setup metrics endpoint
        this.setupMetrics();
        
        console.log('✅ API Gateway initialized successfully');
    }

    /**
     * 🔗 Connect to Redis for caching and rate limiting
     */
    async connectRedis() {
        try {
            this.redis = Redis.createClient({
                host: process.env.REDIS_HOST || 'redis',
                port: process.env.REDIS_PORT || 6379,
                password: process.env.REDIS_PASSWORD,
                db: process.env.REDIS_DB || 1
            });

            await this.redis.connect();
            console.log('✅ Connected to Redis for gateway caching');
        } catch (error) {
            console.error('❌ Redis connection failed:', error);
            // Continue without Redis (degraded mode)
        }
    }

    /**
     * 🛡️ Setup security and performance middleware
     */
    setupMiddleware() {
        // Security headers
        this.app.use(helmet({
            contentSecurityPolicy: {
                directives: {
                    defaultSrc: ["'self'"],
                    styleSrc: ["'self'", "'unsafe-inline'"],
                    scriptSrc: ["'self'"],
                    imgSrc: ["'self'", "data:", "https:"]
                }
            },
            hsts: {
                maxAge: 31536000,
                includeSubDomains: true,
                preload: true
            }
        }));

        // CORS configuration
        this.app.use(cors({
            origin: (origin, callback) => {
                const allowedOrigins = [
                    'https://admin.meschain.com',
                    'https://dashboard.meschain.com',
                    'https://app.meschain.com'
                ];
                
                if (!origin || allowedOrigins.includes(origin)) {
                    callback(null, true);
                } else {
                    callback(new Error('Not allowed by CORS'));
                }
            },
            credentials: true,
            methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With']
        }));

        // Compression
        this.app.use(compression());

        // Body parsing
        this.app.use(express.json({ limit: '10mb' }));
        this.app.use(express.urlencoded({ extended: true, limit: '10mb' }));

        // Request ID and logging
        this.app.use((req, res, next) => {
            req.requestId = require('crypto').randomUUID();
            req.startTime = Date.now();
            
            console.log(`📥 ${req.method} ${req.url} [${req.requestId}]`);
            next();
        });

        // Active connections tracking
        this.app.use((req, res, next) => {
            this.metrics.activeConnections.inc();
            res.on('finish', () => {
                this.metrics.activeConnections.dec();
            });
            next();
        });
    }

    /**
     * 🔐 JWT Authentication middleware
     */
    createAuthMiddleware(options = {}) {
        return async (req, res, next) => {
            try {
                const token = req.headers.authorization?.replace('Bearer ', '');
                
                if (!token && !options.optional) {
                    return res.status(401).json({
                        error: 'Authentication required',
                        code: 'MISSING_TOKEN'
                    });
                }

                if (token) {
                    // Verify token with auth service
                    const authResult = await this.validateTokenWithAuthService(token);
                    
                    if (authResult.valid) {
                        req.user = authResult.user;
                        req.permissions = authResult.permissions;
                    } else if (!options.optional) {
                        return res.status(401).json({
                            error: 'Invalid token',
                            code: 'INVALID_TOKEN'
                        });
                    }
                }

                next();
            } catch (error) {
                console.error('Authentication error:', error);
                if (!options.optional) {
                    return res.status(500).json({
                        error: 'Authentication service unavailable',
                        code: 'AUTH_SERVICE_ERROR'
                    });
                }
                next();
            }
        };
    }

    /**
     * 🛡️ Token validation with auth service
     */
    async validateTokenWithAuthService(token) {
        try {
            // Check cache first
            if (this.redis) {
                const cached = await this.redis.get(`auth:token:${token}`);
                if (cached) {
                    return JSON.parse(cached);
                }
            }

            // Validate with auth service
            const authService = this.serviceRegistry['auth-service'];
            const response = await fetch(`${authService.baseUrl}/api/auth/validate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ token }),
                timeout: authService.timeout
            });

            const result = await response.json();
            
            // Cache result for 5 minutes
            if (this.redis && result.valid) {
                await this.redis.setEx(`auth:token:${token}`, 300, JSON.stringify(result));
            }

            return result;
        } catch (error) {
            console.error('Token validation error:', error);
            return { valid: false, error: 'Validation failed' };
        }
    }

    /**
     * ⚡ Rate limiting middleware
     */
    createRateLimitMiddleware(options = {}) {
        const windowMs = options.windowMs || 15 * 60 * 1000; // 15 minutes
        const max = options.max || 1000; // requests per window
        const keyGenerator = options.keyGenerator || ((req) => {
            return req.user?.id || req.ip;
        });

        return rateLimit({
            windowMs,
            max,
            keyGenerator,
            standardHeaders: true,
            legacyHeaders: false,
            store: this.redis ? new (require('rate-limit-redis'))({
                client: this.redis,
                prefix: 'rl:gateway:'
            }) : undefined,
            message: {
                error: 'Too many requests',
                retryAfter: Math.ceil(windowMs / 1000)
            },
            onLimitReached: (req, res, options) => {
                console.warn(`Rate limit exceeded for ${keyGenerator(req)}`);
            }
        });
    }

    /**
     * 🎯 Setup API routes and proxying
     */
    setupRoutes() {
        // Health check endpoint
        this.app.get('/health', (req, res) => {
            res.json({
                status: 'healthy',
                timestamp: new Date().toISOString(),
                services: this.getServiceHealthSummary()
            });
        });

        // Global rate limiting
        this.app.use('/api', this.createRateLimitMiddleware({
            windowMs: 15 * 60 * 1000, // 15 minutes
            max: 1000
        }));

        // Authentication routes (public)
        this.setupAuthRoutes();

        // User management routes
        this.setupUserRoutes();

        // Business service routes
        this.setupBusinessRoutes();

        // Marketplace integration routes
        this.setupMarketplaceRoutes();

        // Platform service routes
        this.setupPlatformRoutes();

        // File upload routes
        this.setupFileRoutes();

        // Analytics and reporting routes
        this.setupAnalyticsRoutes();

        // Admin routes
        this.setupAdminRoutes();

        // 404 handler
        this.app.use('*', (req, res) => {
            res.status(404).json({
                error: 'Endpoint not found',
                path: req.originalUrl
            });
        });

        // Error handler
        this.app.use(this.errorHandler.bind(this));
    }

    /**
     * 🔐 Authentication routes (public access)
     */
    setupAuthRoutes() {
        this.app.use('/api/auth', 
            this.createRateLimitMiddleware({ max: 100, windowMs: 15 * 60 * 1000 }),
            this.createProxy('auth-service', {
                pathRewrite: { '^/api/auth': '/api/auth' },
                timeout: 5000
            })
        );
    }

    /**
     * 👤 User management routes
     */
    setupUserRoutes() {
        this.app.use('/api/users',
            this.createAuthMiddleware(),
            this.createRateLimitMiddleware({ max: 500 }),
            this.createProxy('user-management', {
                pathRewrite: { '^/api/users': '/api/users' }
            })
        );
    }

    /**
     * 🛍️ Business service routes
     */
    setupBusinessRoutes() {
        // Orders
        this.app.use('/api/orders',
            this.createAuthMiddleware(),
            this.createRateLimitMiddleware({ max: 200 }),
            this.createProxy('order-processing', {
                pathRewrite: { '^/api/orders': '/api/orders' }
            })
        );

        // Products
        this.app.use('/api/products',
            this.createAuthMiddleware({ optional: true }),
            this.createRateLimitMiddleware({ max: 1000 }),
            this.createProxy('product-catalog', {
                pathRewrite: { '^/api/products': '/api/products' }
            })
        );

        // Inventory
        this.app.use('/api/inventory',
            this.createAuthMiddleware(),
            this.createRateLimitMiddleware({ max: 300 }),
            this.createProxy('inventory-service', {
                pathRewrite: { '^/api/inventory': '/api/inventory' }
            })
        );

        // Payments
        this.app.use('/api/payments',
            this.createAuthMiddleware(),
            this.createRateLimitMiddleware({ max: 100 }),
            this.createProxy('payment-service', {
                pathRewrite: { '^/api/payments': '/api/payments' },
                timeout: 20000
            })
        );

        // Notifications
        this.app.use('/api/notifications',
            this.createAuthMiddleware(),
            this.createRateLimitMiddleware({ max: 200 }),
            this.createProxy('notification-service', {
                pathRewrite: { '^/api/notifications': '/api/notifications' }
            })
        );
    }

    /**
     * 🏪 Marketplace integration routes
     */
    setupMarketplaceRoutes() {
        const marketplaces = [
            'trendyol', 'amazon', 'n11', 'hepsiburada', 'ozon'
        ];

        marketplaces.forEach(marketplace => {
            this.app.use(`/api/marketplaces/${marketplace}`,
                this.createAuthMiddleware(),
                this.createRateLimitMiddleware({ max: 100 }),
                this.createProxy(`${marketplace}-integration`, {
                    pathRewrite: { [`^/api/marketplaces/${marketplace}`]: `/api/${marketplace}` },
                    timeout: 30000
                })
            );
        });
    }

    /**
     * 🔧 Platform service routes
     */
    setupPlatformRoutes() {
        // Search
        this.app.use('/api/search',
            this.createAuthMiddleware({ optional: true }),
            this.createRateLimitMiddleware({ max: 500 }),
            this.createProxy('search-service', {
                pathRewrite: { '^/api/search': '/api/search' }
            })
        );
    }

    /**
     * 📁 File upload routes
     */
    setupFileRoutes() {
        this.app.use('/api/files',
            this.createAuthMiddleware(),
            this.createRateLimitMiddleware({ max: 50 }),
            this.createProxy('file-storage', {
                pathRewrite: { '^/api/files': '/api/files' },
                timeout: 60000
            })
        );
    }

    /**
     * 📊 Analytics and reporting routes
     */
    setupAnalyticsRoutes() {
        // Analytics
        this.app.use('/api/analytics',
            this.createAuthMiddleware(),
            this.createRateLimitMiddleware({ max: 200 }),
            this.createProxy('analytics-service', {
                pathRewrite: { '^/api/analytics': '/api/analytics' },
                timeout: 30000
            })
        );

        // Reports
        this.app.use('/api/reports',
            this.createAuthMiddleware(),
            this.createRateLimitMiddleware({ max: 50 }),
            this.createProxy('reporting-service', {
                pathRewrite: { '^/api/reports': '/api/reports' },
                timeout: 60000
            })
        );
    }

    /**
     * 👑 Admin routes (restricted access)
     */
    setupAdminRoutes() {
        this.app.use('/api/admin',
            this.createAuthMiddleware(),
            this.requireAdminAccess.bind(this),
            this.createRateLimitMiddleware({ max: 100 }),
            this.routeAdminRequests.bind(this)
        );
    }

    /**
     * 👑 Admin access control
     */
    requireAdminAccess(req, res, next) {
        if (!req.user || !req.permissions?.includes('admin')) {
            return res.status(403).json({
                error: 'Admin access required',
                code: 'INSUFFICIENT_PERMISSIONS'
            });
        }
        next();
    }

    /**
     * 🎯 Route admin requests to appropriate services
     */
    routeAdminRequests(req, res, next) {
        const path = req.path.replace('/api/admin', '');
        
        if (path.startsWith('/users')) {
            req.url = req.url.replace('/api/admin', '/api');
            return this.createProxy('user-management')(req, res, next);
        } else if (path.startsWith('/analytics')) {
            req.url = req.url.replace('/api/admin', '/api');
            return this.createProxy('analytics-service')(req, res, next);
        } else if (path.startsWith('/system')) {
            // Handle system administration directly
            return this.handleSystemAdmin(req, res);
        }
        
        next();
    }

    /**
     * 🔧 Handle system administration
     */
    async handleSystemAdmin(req, res) {
        const action = req.path.split('/').pop();
        
        switch (action) {
            case 'health':
                return res.json(this.getDetailedHealthStatus());
            case 'metrics':
                return res.json(await this.getSystemMetrics());
            case 'services':
                return res.json(this.getServiceStatus());
            default:
                return res.status(404).json({ error: 'Admin action not found' });
        }
    }

    /**
     * 🔗 Create proxy middleware for service routing
     */
    createProxy(serviceName, options = {}) {
        const service = this.serviceRegistry[serviceName];
        
        if (!service) {
            throw new Error(`Service ${serviceName} not found in registry`);
        }

        const proxyOptions = {
            target: service.baseUrl,
            changeOrigin: true,
            timeout: service.timeout || 10000,
            proxyTimeout: service.timeout || 10000,
            ...options,
            
            onProxyReq: (proxyReq, req, res) => {
                // Add headers
                proxyReq.setHeader('X-Request-ID', req.requestId);
                proxyReq.setHeader('X-Gateway-Version', '1.0.0');
                
                if (req.user) {
                    proxyReq.setHeader('X-User-ID', req.user.id);
                    proxyReq.setHeader('X-User-Permissions', JSON.stringify(req.permissions));
                }
            },
            
            onProxyRes: (proxyRes, req, res) => {
                // Record metrics
                const duration = (Date.now() - req.startTime) / 1000;
                const statusCode = proxyRes.statusCode.toString();
                
                this.metrics.httpRequestsTotal
                    .labels(req.method, req.route?.path || req.path, statusCode, serviceName)
                    .inc();
                    
                this.metrics.httpRequestDuration
                    .labels(req.method, req.route?.path || req.path, serviceName)
                    .observe(duration);
                    
                // Add response headers
                proxyRes.headers['X-Service-Name'] = serviceName;
                proxyRes.headers['X-Response-Time'] = `${duration}s`;
            },
            
            onError: (err, req, res) => {
                console.error(`Proxy error for ${serviceName}:`, err.message);
                
                // Record error metric
                this.metrics.httpRequestsTotal
                    .labels(req.method, req.route?.path || req.path, '503', serviceName)
                    .inc();
                    
                res.status(503).json({
                    error: 'Service temporarily unavailable',
                    service: serviceName,
                    requestId: req.requestId
                });
            }
        };

        return httpProxy.createProxyMiddleware(proxyOptions);
    }

    /**
     * 💓 Setup health monitoring for all services
     */
    setupHealthMonitoring() {
        setInterval(() => {
            this.checkServiceHealth();
        }, 30000); // Check every 30 seconds
        
        // Initial health check
        setTimeout(() => this.checkServiceHealth(), 5000);
    }

    /**
     * 🔍 Check health of all registered services
     */
    async checkServiceHealth() {
        for (const [serviceName, service] of Object.entries(this.serviceRegistry)) {
            try {
                const response = await fetch(`${service.baseUrl}${service.healthCheck}`, {
                    timeout: 5000
                });
                
                const isHealthy = response.status === 200;
                this.healthChecks.set(serviceName, {
                    healthy: isHealthy,
                    lastCheck: new Date().toISOString(),
                    responseTime: response.headers.get('X-Response-Time') || 'unknown'
                });
                
            } catch (error) {
                this.healthChecks.set(serviceName, {
                    healthy: false,
                    lastCheck: new Date().toISOString(),
                    error: error.message
                });
            }
        }
    }

    /**
     * 📊 Setup metrics endpoint
     */
    setupMetrics() {
        this.app.get('/metrics', async (req, res) => {
            res.set('Content-Type', prometheus.register.contentType);
            res.end(await prometheus.register.metrics());
        });
    }

    /**
     * 🚨 Error handler
     */
    errorHandler(error, req, res, next) {
        console.error('Gateway error:', error);
        
        const statusCode = error.status || 500;
        
        res.status(statusCode).json({
            error: 'Internal gateway error',
            requestId: req.requestId,
            timestamp: new Date().toISOString()
        });
    }

    /**
     * 📊 Get service health summary
     */
    getServiceHealthSummary() {
        const summary = {};
        for (const [serviceName, health] of this.healthChecks) {
            summary[serviceName] = health.healthy ? 'healthy' : 'unhealthy';
        }
        return summary;
    }

    /**
     * 📋 Get detailed health status
     */
    getDetailedHealthStatus() {
        return {
            gateway: {
                status: 'healthy',
                uptime: process.uptime(),
                memory: process.memoryUsage(),
                version: '1.0.0'
            },
            services: Object.fromEntries(this.healthChecks),
            totalServices: Object.keys(this.serviceRegistry).length,
            healthyServices: Array.from(this.healthChecks.values()).filter(h => h.healthy).length
        };
    }

    /**
     * 📊 Get system metrics
     */
    async getSystemMetrics() {
        return {
            requests: await prometheus.register.getSingleMetric('gateway_http_requests_total').get(),
            responseTime: await prometheus.register.getSingleMetric('gateway_http_request_duration_seconds').get(),
            activeConnections: await prometheus.register.getSingleMetric('gateway_active_connections').get(),
            timestamp: new Date().toISOString()
        };
    }

    /**
     * 🔧 Get service status
     */
    getServiceStatus() {
        return Object.entries(this.serviceRegistry).map(([name, config]) => ({
            name,
            baseUrl: config.baseUrl,
            health: this.healthChecks.get(name) || { healthy: false, lastCheck: 'never' },
            configuration: {
                timeout: config.timeout,
                retries: config.retries,
                circuitBreaker: config.circuitBreaker,
                loadBalancer: config.loadBalancer
            }
        }));
    }

    /**
     * 🚀 Start the API Gateway
     */
    start() {
        this.app.listen(this.port, () => {
            console.log(`🌐 MesChain API Gateway listening on port ${this.port}`);
            console.log(`📊 Metrics available at http://localhost:${this.port}/metrics`);
            console.log(`💓 Health check available at http://localhost:${this.port}/health`);
        });
    }

    /**
     * 🔄 Graceful shutdown
     */
    async shutdown() {
        console.log('🔄 Shutting down API Gateway...');
        
        if (this.redis) {
            await this.redis.quit();
        }
        
        console.log('✅ API Gateway shutdown complete');
        process.exit(0);
    }
}

// 🚀 Export and usage
module.exports = { MesChainAPIGateway };

// Handle graceful shutdown
process.on('SIGTERM', async () => {
    if (global.gateway) {
        await global.gateway.shutdown();
    }
});

process.on('SIGINT', async () => {
    if (global.gateway) {
        await global.gateway.shutdown();
    }
});

// Start gateway if run directly
if (require.main === module) {
    console.log('🚀 CURSOR TEAM: Starting MesChain API Gateway...');
    
    const gateway = new MesChainAPIGateway({
        port: process.env.GATEWAY_PORT || 8080
    });
    
    global.gateway = gateway;
    
    gateway.initialize().then(() => {
        gateway.start();
        console.log('✅ CURSOR TEAM: API Gateway started successfully!');
    }).catch(error => {
        console.error('❌ Failed to start API Gateway:', error);
        process.exit(1);
    });
} 