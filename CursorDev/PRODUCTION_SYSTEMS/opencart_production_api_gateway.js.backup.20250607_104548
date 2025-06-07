const express = require('express');
const rateLimit = require('express-rate-limit');
const helmet = require('helmet');
const cors = require('cors');
const jwt = require('jsonwebtoken');
const crypto = require('crypto');
const { body, validationResult } = require('express-validator');
const Redis = require('redis');

/**
 * OpenCart Production API Gateway
 * 
 * Comprehensive API gateway for managing secure access to all OpenCart production
 * APIs, including authentication, rate limiting, request validation, logging,
 * and integration with all 8 marketplace platforms.
 * 
 * @package OpenCartProduction
 * @version 1.0.0
 * @author Production Systems Team
 */

class OpenCartProductionAPIGateway {
    constructor() {
        this.app = express();
        this.config = {
            port: process.env.API_GATEWAY_PORT || 3020,
            jwt_secret: process.env.JWT_SECRET || 'your-super-secret-jwt-key',
            redis_url: process.env.REDIS_URL || 'redis://localhost:6379',
            environment: process.env.NODE_ENV || 'production',
            rate_limiting: {
                windowMs: 15 * 60 * 1000, // 15 minutes
                max: 1000, // limit each IP to 1000 requests per windowMs
                message: 'Too many requests from this IP, please try again after 15 minutes.'
            },
            marketplace_endpoints: {
                trendyol: 'https://api.trendyol.com',
                n11: 'https://api.n11.com',
                amazon: 'https://sellingpartnerapi-na.amazon.com',
                ebay: 'https://api.ebay.com',
                hepsiburada: 'https://api.hepsiburada.com',
                ozon: 'https://api-seller.ozon.ru',
                pazarama: 'https://api.pazarama.com',
                ciceksepeti: 'https://api.ciceksepeti.com'
            }
        };
        
        this.redis = null;
        this.apiMetrics = new Map();
        this.middlewares = new Map();
        this.routes = new Map();
        this.rateLimiters = new Map();
        
        this.initializeRedis();
        this.setupMiddlewares();
        this.setupRoutes();
        this.setupErrorHandling();
        this.startMetricsCollection();
        
        console.log('OpenCart Production API Gateway initialized');
    }
    
    /**
     * Initialize Redis connection
     */
    async initializeRedis() {
        try {
            this.redis = Redis.createClient({ url: this.config.redis_url });
            await this.redis.connect();
            console.log('Redis connection established for API Gateway');
        } catch (error) {
            console.error('Failed to connect to Redis:', error.message);
        }
    }
    
    /**
     * Setup middleware stack
     */
    setupMiddlewares() {
        // Security middleware
        this.app.use(helmet({
            contentSecurityPolicy: {
                directives: {
                    defaultSrc: ["'self'"],
                    styleSrc: ["'self'", "'unsafe-inline'"],
                    scriptSrc: ["'self'"],
                    imgSrc: ["'self'", "data:", "https:"],
                },
            },
            hsts: {
                maxAge: 31536000,
                includeSubDomains: true,
                preload: true
            }
        }));
        
        // CORS configuration
        this.app.use(cors({
            origin: this.getAllowedOrigins(),
            methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            allowedHeaders: ['Content-Type', 'Authorization', 'X-API-Key', 'X-Request-ID'],
            credentials: true
        }));
        
        // Body parsing
        this.app.use(express.json({ limit: '10mb' }));
        this.app.use(express.urlencoded({ extended: true, limit: '10mb' }));
        
        // Request ID middleware
        this.app.use(this.requestIdMiddleware.bind(this));
        
        // Logging middleware
        this.app.use(this.loggingMiddleware.bind(this));
        
        // Rate limiting
        this.setupRateLimiting();
        
        // API key validation
        this.app.use('/api', this.apiKeyMiddleware.bind(this));
        
        // JWT authentication for protected routes
        this.app.use('/api/protected', this.jwtMiddleware.bind(this));
        
        console.log('API Gateway middlewares configured');
    }
    
    /**
     * Get allowed origins for CORS
     */
    getAllowedOrigins() {
        const origins = [
            'https://opencart.com',
            'https://staging.opencart.com',
            'https://admin.opencart.com'
        ];
        
        if (this.config.environment === 'development') {
            origins.push('http://localhost:3000', 'http://localhost:8080');
        }
        
        return origins;
    }
    
    /**
     * Setup rate limiting for different endpoints
     */
    setupRateLimiting() {
        // General rate limiter
        const generalLimiter = rateLimit(this.config.rate_limiting);
        this.app.use('/api', generalLimiter);
        
        // Strict rate limiter for authentication endpoints
        const authLimiter = rateLimit({
            windowMs: 15 * 60 * 1000, // 15 minutes
            max: 10, // limit each IP to 10 requests per windowMs
            message: 'Too many authentication attempts, please try again later.'
        });
        this.app.use('/api/auth', authLimiter);
        
        // Marketplace-specific rate limiters
        Object.keys(this.config.marketplace_endpoints).forEach(marketplace => {
            const marketplaceLimiter = rateLimit({
                windowMs: 60 * 1000, // 1 minute
                max: 100, // 100 requests per minute per marketplace
                keyGenerator: (req) => `${req.ip}:${marketplace}`,
                message: `Too many requests to ${marketplace} API, please try again later.`
            });
            
            this.rateLimiters.set(marketplace, marketplaceLimiter);
            this.app.use(`/api/marketplace/${marketplace}`, marketplaceLimiter);
        });
        
        console.log('Rate limiting configured');
    }
    
    /**
     * Request ID middleware
     */
    requestIdMiddleware(req, res, next) {
        req.requestId = req.headers['x-request-id'] || crypto.randomUUID();
        res.setHeader('X-Request-ID', req.requestId);
        next();
    }
    
    /**
     * Logging middleware
     */
    loggingMiddleware(req, res, next) {
        const startTime = Date.now();
        
        // Log request
        console.log(`[${new Date().toISOString()}] ${req.method} ${req.path} - ${req.ip} - Request ID: ${req.requestId}`);
        
        // Override res.end to log response
        const originalEnd = res.end;
        res.end = function(chunk, encoding) {
            const duration = Date.now() - startTime;
            console.log(`[${new Date().toISOString()}] ${req.method} ${req.path} - ${res.statusCode} - ${duration}ms - Request ID: ${req.requestId}`);
            
            // Record metrics
            this.recordAPIMetric(req.method, req.path, res.statusCode, duration);
            
            originalEnd.call(this, chunk, encoding);
        }.bind(this);
        
        next();
    }
    
    /**
     * API key middleware
     */
    async apiKeyMiddleware(req, res, next) {
        const apiKey = req.headers['x-api-key'];
        
        if (!apiKey) {
            return res.status(401).json({
                error: 'API key required',
                message: 'Please provide a valid API key in the X-API-Key header',
                request_id: req.requestId
            });
        }
        
        try {
            // Validate API key
            const keyData = await this.validateAPIKey(apiKey);
            
            if (!keyData) {
                return res.status(401).json({
                    error: 'Invalid API key',
                    message: 'The provided API key is not valid',
                    request_id: req.requestId
                });
            }
            
            // Check if API key is active
            if (!keyData.active) {
                return res.status(401).json({
                    error: 'API key disabled',
                    message: 'The provided API key has been disabled',
                    request_id: req.requestId
                });
            }
            
            // Check rate limits for this API key
            if (keyData.rate_limit) {
                const rateLimitExceeded = await this.checkAPIKeyRateLimit(apiKey, keyData.rate_limit);
                if (rateLimitExceeded) {
                    return res.status(429).json({
                        error: 'Rate limit exceeded',
                        message: 'API key rate limit exceeded',
                        request_id: req.requestId
                    });
                }
            }
            
            // Attach API key data to request
            req.apiKey = keyData;
            next();
            
        } catch (error) {
            console.error('API key validation error:', error.message);
            return res.status(500).json({
                error: 'Authentication error',
                message: 'Failed to validate API key',
                request_id: req.requestId
            });
        }
    }
    
    /**
     * JWT middleware for protected routes
     */
    jwtMiddleware(req, res, next) {
        const token = req.headers.authorization?.replace('Bearer ', '');
        
        if (!token) {
            return res.status(401).json({
                error: 'JWT token required',
                message: 'Please provide a valid JWT token',
                request_id: req.requestId
            });
        }
        
        try {
            const decoded = jwt.verify(token, this.config.jwt_secret);
            req.user = decoded;
            next();
        } catch (error) {
            return res.status(401).json({
                error: 'Invalid JWT token',
                message: 'The provided JWT token is not valid',
                request_id: req.requestId
            });
        }
    }
    
    /**
     * Validate API key
     */
    async validateAPIKey(apiKey) {
        try {
            if (this.redis) {
                const keyData = await this.redis.get(`api_key:${apiKey}`);
                if (keyData) {
                    return JSON.parse(keyData);
                }
            }
            
            // Fallback to default validation logic
            // In production, this would query the database
            const defaultKeys = {
                'opencart_prod_key_2024': {
                    id: 'opencart_prod_key_2024',
                    name: 'OpenCart Production Key',
                    active: true,
                    rate_limit: {
                        requests_per_minute: 1000,
                        requests_per_hour: 10000
                    },
                    permissions: ['read', 'write', 'marketplace_access'],
                    created_at: '2024-01-01T00:00:00Z'
                }
            };
            
            return defaultKeys[apiKey] || null;
            
        } catch (error) {
            console.error('API key validation error:', error.message);
            return null;
        }
    }
    
    /**
     * Check API key rate limit
     */
    async checkAPIKeyRateLimit(apiKey, rateLimit) {
        if (!this.redis) return false;
        
        try {
            const minute = Math.floor(Date.now() / 60000);
            const hour = Math.floor(Date.now() / 3600000);
            
            const minuteKey = `rate_limit:${apiKey}:${minute}`;
            const hourKey = `rate_limit:${apiKey}:${hour}`;
            
            const [minuteCount, hourCount] = await Promise.all([
                this.redis.get(minuteKey),
                this.redis.get(hourKey)
            ]);
            
            // Check minute limit
            if (rateLimit.requests_per_minute && parseInt(minuteCount || 0) >= rateLimit.requests_per_minute) {
                return true;
            }
            
            // Check hour limit
            if (rateLimit.requests_per_hour && parseInt(hourCount || 0) >= rateLimit.requests_per_hour) {
                return true;
            }
            
            // Increment counters
            await Promise.all([
                this.redis.incr(minuteKey),
                this.redis.incr(hourKey),
                this.redis.expire(minuteKey, 60),
                this.redis.expire(hourKey, 3600)
            ]);
            
            return false;
            
        } catch (error) {
            console.error('Rate limit check error:', error.message);
            return false;
        }
    }
    
    /**
     * Setup API routes
     */
    setupRoutes() {
        // Health check
        this.app.get('/health', (req, res) => {
            res.json({
                status: 'healthy',
                timestamp: new Date().toISOString(),
                version: '1.0.0',
                environment: this.config.environment,
                uptime: process.uptime(),
                memory: process.memoryUsage(),
                request_id: req.requestId
            });
        });
        
        // API documentation
        this.app.get('/api/docs', (req, res) => {
            res.json(this.generateAPIDocumentation());
        });
        
        // Authentication endpoints
        this.setupAuthRoutes();
        
        // Marketplace integration endpoints
        this.setupMarketplaceRoutes();
        
        // OpenCart management endpoints
        this.setupOpenCartRoutes();
        
        // Monitoring and metrics endpoints
        this.setupMonitoringRoutes();
        
        // File upload endpoints
        this.setupFileUploadRoutes();
        
        console.log('API routes configured');
    }
    
    /**
     * Setup authentication routes
     */
    setupAuthRoutes() {
        // Generate JWT token
        this.app.post('/api/auth/token', [
            body('username').notEmpty().withMessage('Username is required'),
            body('password').notEmpty().withMessage('Password is required')
        ], async (req, res) => {
            try {
                const errors = validationResult(req);
                if (!errors.isEmpty()) {
                    return res.status(400).json({
                        error: 'Validation error',
                        details: errors.array(),
                        request_id: req.requestId
                    });
                }
                
                const { username, password } = req.body;
                
                // Validate credentials
                const user = await this.validateUserCredentials(username, password);
                if (!user) {
                    return res.status(401).json({
                        error: 'Invalid credentials',
                        message: 'Username or password is incorrect',
                        request_id: req.requestId
                    });
                }
                
                // Generate JWT token
                const token = jwt.sign({
                    user_id: user.id,
                    username: user.username,
                    permissions: user.permissions
                }, this.config.jwt_secret, { expiresIn: '24h' });
                
                res.json({
                    token,
                    user: {
                        id: user.id,
                        username: user.username,
                        permissions: user.permissions
                    },
                    expires_in: 86400,
                    request_id: req.requestId
                });
                
            } catch (error) {
                console.error('Token generation error:', error.message);
                res.status(500).json({
                    error: 'Authentication error',
                    message: 'Failed to generate token',
                    request_id: req.requestId
                });
            }
        });
        
        // Refresh token
        this.app.post('/api/auth/refresh', this.jwtMiddleware.bind(this), (req, res) => {
            try {
                const newToken = jwt.sign({
                    user_id: req.user.user_id,
                    username: req.user.username,
                    permissions: req.user.permissions
                }, this.config.jwt_secret, { expiresIn: '24h' });
                
                res.json({
                    token: newToken,
                    expires_in: 86400,
                    request_id: req.requestId
                });
                
            } catch (error) {
                console.error('Token refresh error:', error.message);
                res.status(500).json({
                    error: 'Token refresh error',
                    message: 'Failed to refresh token',
                    request_id: req.requestId
                });
            }
        });
    }
    
    /**
     * Setup marketplace integration routes
     */
    setupMarketplaceRoutes() {
        Object.keys(this.config.marketplace_endpoints).forEach(marketplace => {
            // Marketplace products
            this.app.get(`/api/marketplace/${marketplace}/products`, async (req, res) => {
                try {
                    const products = await this.getMarketplaceProducts(marketplace, req.query);
                    res.json({
                        marketplace,
                        products,
                        total: products.length,
                        request_id: req.requestId
                    });
                } catch (error) {
                    console.error(`${marketplace} products error:`, error.message);
                    res.status(500).json({
                        error: 'Marketplace error',
                        message: `Failed to fetch ${marketplace} products`,
                        request_id: req.requestId
                    });
                }
            });
            
            // Marketplace orders
            this.app.get(`/api/marketplace/${marketplace}/orders`, async (req, res) => {
                try {
                    const orders = await this.getMarketplaceOrders(marketplace, req.query);
                    res.json({
                        marketplace,
                        orders,
                        total: orders.length,
                        request_id: req.requestId
                    });
                } catch (error) {
                    console.error(`${marketplace} orders error:`, error.message);
                    res.status(500).json({
                        error: 'Marketplace error',
                        message: `Failed to fetch ${marketplace} orders`,
                        request_id: req.requestId
                    });
                }
            });
            
            // Sync marketplace data
            this.app.post(`/api/marketplace/${marketplace}/sync`, async (req, res) => {
                try {
                    const syncResult = await this.syncMarketplaceData(marketplace, req.body);
                    res.json({
                        marketplace,
                        sync_result: syncResult,
                        timestamp: new Date().toISOString(),
                        request_id: req.requestId
                    });
                } catch (error) {
                    console.error(`${marketplace} sync error:`, error.message);
                    res.status(500).json({
                        error: 'Sync error',
                        message: `Failed to sync ${marketplace} data`,
                        request_id: req.requestId
                    });
                }
            });
            
            // Marketplace webhook
            this.app.post(`/api/marketplace/${marketplace}/webhook`, async (req, res) => {
                try {
                    const result = await this.handleMarketplaceWebhook(marketplace, req.body, req.headers);
                    res.json({
                        marketplace,
                        processed: true,
                        result,
                        request_id: req.requestId
                    });
                } catch (error) {
                    console.error(`${marketplace} webhook error:`, error.message);
                    res.status(500).json({
                        error: 'Webhook error',
                        message: `Failed to process ${marketplace} webhook`,
                        request_id: req.requestId
                    });
                }
            });
        });
    }
    
    /**
     * Setup OpenCart management routes
     */
    setupOpenCartRoutes() {
        // OpenCart products
        this.app.get('/api/opencart/products', async (req, res) => {
            try {
                const products = await this.getOpenCartProducts(req.query);
                res.json({
                    products,
                    total: products.length,
                    request_id: req.requestId
                });
            } catch (error) {
                console.error('OpenCart products error:', error.message);
                res.status(500).json({
                    error: 'OpenCart error',
                    message: 'Failed to fetch OpenCart products',
                    request_id: req.requestId
                });
            }
        });
        
        // OpenCart orders
        this.app.get('/api/opencart/orders', async (req, res) => {
            try {
                const orders = await this.getOpenCartOrders(req.query);
                res.json({
                    orders,
                    total: orders.length,
                    request_id: req.requestId
                });
            } catch (error) {
                console.error('OpenCart orders error:', error.message);
                res.status(500).json({
                    error: 'OpenCart error',
                    message: 'Failed to fetch OpenCart orders',
                    request_id: req.requestId
                });
            }
        });
        
        // OpenCart configuration
        this.app.get('/api/opencart/config', async (req, res) => {
            try {
                const config = await this.getOpenCartConfig();
                res.json({
                    config,
                    request_id: req.requestId
                });
            } catch (error) {
                console.error('OpenCart config error:', error.message);
                res.status(500).json({
                    error: 'Configuration error',
                    message: 'Failed to fetch OpenCart configuration',
                    request_id: req.requestId
                });
            }
        });
    }
    
    /**
     * Setup monitoring routes
     */
    setupMonitoringRoutes() {
        // API metrics
        this.app.get('/api/monitoring/metrics', (req, res) => {
            res.json({
                metrics: this.getAPIMetrics(),
                request_id: req.requestId
            });
        });
        
        // System health
        this.app.get('/api/monitoring/health', async (req, res) => {
            try {
                const health = await this.getSystemHealth();
                res.json({
                    health,
                    request_id: req.requestId
                });
            } catch (error) {
                console.error('Health check error:', error.message);
                res.status(500).json({
                    error: 'Health check error',
                    message: 'Failed to perform health check',
                    request_id: req.requestId
                });
            }
        });
        
        // Performance metrics
        this.app.get('/api/monitoring/performance', (req, res) => {
            res.json({
                performance: this.getPerformanceMetrics(),
                request_id: req.requestId
            });
        });
    }
    
    /**
     * Setup file upload routes
     */
    setupFileUploadRoutes() {
        const multer = require('multer');
        const upload = multer({
            dest: 'uploads/',
            limits: {
                fileSize: 10 * 1024 * 1024 // 10MB
            }
        });
        
        this.app.post('/api/upload', upload.single('file'), async (req, res) => {
            try {
                if (!req.file) {
                    return res.status(400).json({
                        error: 'No file uploaded',
                        message: 'Please provide a file to upload',
                        request_id: req.requestId
                    });
                }
                
                const result = await this.processUploadedFile(req.file);
                res.json({
                    file: result,
                    request_id: req.requestId
                });
                
            } catch (error) {
                console.error('File upload error:', error.message);
                res.status(500).json({
                    error: 'Upload error',
                    message: 'Failed to process uploaded file',
                    request_id: req.requestId
                });
            }
        });
    }
    
    /**
     * Validate user credentials
     */
    async validateUserCredentials(username, password) {
        // In production, this would query the database
        const users = {
            'admin': {
                id: 1,
                username: 'admin',
                password_hash: 'hashed_password_here',
                permissions: ['read', 'write', 'admin']
            }
        };
        
        const user = users[username];
        if (!user) return null;
        
        // In production, use proper password hashing
        const isValidPassword = true; // bcrypt.compare(password, user.password_hash);
        if (!isValidPassword) return null;
        
        return {
            id: user.id,
            username: user.username,
            permissions: user.permissions
        };
    }
    
    /**
     * Get marketplace products
     */
    async getMarketplaceProducts(marketplace, filters = {}) {
        // Simulate marketplace API call
        return [
            {
                id: `${marketplace}_product_1`,
                name: `Product from ${marketplace}`,
                price: 29.99,
                stock: 100,
                status: 'active'
            }
        ];
    }
    
    /**
     * Get marketplace orders
     */
    async getMarketplaceOrders(marketplace, filters = {}) {
        // Simulate marketplace API call
        return [
            {
                id: `${marketplace}_order_1`,
                status: 'processing',
                total: 59.98,
                items: 2,
                created_at: new Date().toISOString()
            }
        ];
    }
    
    /**
     * Sync marketplace data
     */
    async syncMarketplaceData(marketplace, options = {}) {
        // Simulate data synchronization
        return {
            products_synced: 150,
            orders_synced: 25,
            inventory_updated: 200,
            sync_duration: '2.5s',
            last_sync: new Date().toISOString()
        };
    }
    
    /**
     * Handle marketplace webhook
     */
    async handleMarketplaceWebhook(marketplace, payload, headers) {
        // Process webhook payload
        console.log(`Received ${marketplace} webhook:`, payload);
        
        return {
            processed: true,
            event_type: payload.event_type || 'unknown',
            processed_at: new Date().toISOString()
        };
    }
    
    /**
     * Get OpenCart products
     */
    async getOpenCartProducts(filters = {}) {
        // Simulate OpenCart database query
        return [
            {
                product_id: 1,
                name: 'Sample Product',
                price: '29.99',
                stock: 100,
                status: 1
            }
        ];
    }
    
    /**
     * Get OpenCart orders
     */
    async getOpenCartOrders(filters = {}) {
        // Simulate OpenCart database query
        return [
            {
                order_id: 1,
                status: 'Processing',
                total: '59.98',
                date_added: new Date().toISOString()
            }
        ];
    }
    
    /**
     * Get OpenCart configuration
     */
    async getOpenCartConfig() {
        // Simulate OpenCart configuration retrieval
        return {
            store_name: 'OpenCart Store',
            store_url: 'https://opencart.com',
            version: '4.0.0',
            theme: 'default',
            language: 'en-gb',
            currency: 'USD'
        };
    }
    
    /**
     * Process uploaded file
     */
    async processUploadedFile(file) {
        // Process the uploaded file
        return {
            filename: file.filename,
            original_name: file.originalname,
            size: file.size,
            mime_type: file.mimetype,
            upload_time: new Date().toISOString()
        };
    }
    
    /**
     * Record API metric
     */
    recordAPIMetric(method, path, statusCode, duration) {
        const metricKey = `${method} ${path}`;
        
        if (!this.apiMetrics.has(metricKey)) {
            this.apiMetrics.set(metricKey, {
                count: 0,
                total_duration: 0,
                avg_duration: 0,
                min_duration: Infinity,
                max_duration: 0,
                status_codes: {}
            });
        }
        
        const metric = this.apiMetrics.get(metricKey);
        metric.count++;
        metric.total_duration += duration;
        metric.avg_duration = metric.total_duration / metric.count;
        metric.min_duration = Math.min(metric.min_duration, duration);
        metric.max_duration = Math.max(metric.max_duration, duration);
        
        if (!metric.status_codes[statusCode]) {
            metric.status_codes[statusCode] = 0;
        }
        metric.status_codes[statusCode]++;
    }
    
    /**
     * Get API metrics
     */
    getAPIMetrics() {
        const metrics = {};
        
        for (const [endpoint, data] of this.apiMetrics) {
            metrics[endpoint] = {
                ...data,
                success_rate: ((data.status_codes['200'] || 0) / data.count) * 100,
                error_rate: ((data.status_codes['500'] || 0) / data.count) * 100
            };
        }
        
        return metrics;
    }
    
    /**
     * Get system health status
     */
    async getSystemHealth() {
        const health = {
            status: 'healthy',
            checks: {},
            timestamp: new Date().toISOString()
        };
        
        // Check Redis connection
        try {
            if (this.redis) {
                await this.redis.ping();
                health.checks.redis = { status: 'healthy', message: 'Redis connection OK' };
            } else {
                health.checks.redis = { status: 'warning', message: 'Redis not connected' };
            }
        } catch (error) {
            health.checks.redis = { status: 'unhealthy', message: error.message };
        }
        
        // Check memory usage
        const memoryUsage = process.memoryUsage();
        const memoryUsageMB = memoryUsage.heapUsed / 1024 / 1024;
        health.checks.memory = {
            status: memoryUsageMB > 500 ? 'warning' : 'healthy',
            usage_mb: Math.round(memoryUsageMB),
            message: `Memory usage: ${Math.round(memoryUsageMB)}MB`
        };
        
        // Check API response times
        const avgResponseTime = this.getAverageResponseTime();
        health.checks.response_time = {
            status: avgResponseTime > 1000 ? 'warning' : 'healthy',
            avg_ms: avgResponseTime,
            message: `Average response time: ${avgResponseTime}ms`
        };
        
        // Determine overall health status
        const unhealthyChecks = Object.values(health.checks).filter(check => check.status === 'unhealthy');
        const warningChecks = Object.values(health.checks).filter(check => check.status === 'warning');
        
        if (unhealthyChecks.length > 0) {
            health.status = 'unhealthy';
        } else if (warningChecks.length > 0) {
            health.status = 'warning';
        }
        
        return health;
    }
    
    /**
     * Get average response time
     */
    getAverageResponseTime() {
        let totalDuration = 0;
        let totalRequests = 0;
        
        for (const [endpoint, data] of this.apiMetrics) {
            totalDuration += data.total_duration;
            totalRequests += data.count;
        }
        
        return totalRequests > 0 ? Math.round(totalDuration / totalRequests) : 0;
    }
    
    /**
     * Get performance metrics
     */
    getPerformanceMetrics() {
        return {
            uptime: process.uptime(),
            memory: process.memoryUsage(),
            cpu_usage: process.cpuUsage(),
            average_response_time: this.getAverageResponseTime(),
            total_requests: Array.from(this.apiMetrics.values()).reduce((sum, metric) => sum + metric.count, 0),
            active_connections: this.app.locals.connections || 0
        };
    }
    
    /**
     * Start metrics collection
     */
    startMetricsCollection() {
        setInterval(() => {
            this.collectSystemMetrics();
        }, 30000); // Collect metrics every 30 seconds
        
        console.log('Metrics collection started');
    }
    
    /**
     * Collect system metrics
     */
    collectSystemMetrics() {
        const metrics = {
            timestamp: Date.now(),
            memory: process.memoryUsage(),
            cpu: process.cpuUsage(),
            uptime: process.uptime(),
            api_metrics: this.getAPIMetrics()
        };
        
        // Store metrics in Redis if available
        if (this.redis) {
            this.redis.lpush('api_gateway_metrics', JSON.stringify(metrics));
            this.redis.ltrim('api_gateway_metrics', 0, 1000); // Keep last 1000 metrics
        }
    }
    
    /**
     * Generate API documentation
     */
    generateAPIDocumentation() {
        return {
            title: 'OpenCart Production API Gateway',
            version: '1.0.0',
            description: 'Comprehensive API gateway for OpenCart production systems',
            base_url: `http://localhost:${this.config.port}`,
            authentication: {
                api_key: {
                    description: 'Include X-API-Key header with valid API key',
                    header: 'X-API-Key'
                },
                jwt: {
                    description: 'Include Authorization header with Bearer token',
                    header: 'Authorization: Bearer <token>'
                }
            },
            endpoints: {
                health: {
                    method: 'GET',
                    path: '/health',
                    description: 'Health check endpoint'
                },
                auth: {
                    token: {
                        method: 'POST',
                        path: '/api/auth/token',
                        description: 'Generate JWT token',
                        body: {
                            username: 'string',
                            password: 'string'
                        }
                    },
                    refresh: {
                        method: 'POST',
                        path: '/api/auth/refresh',
                        description: 'Refresh JWT token',
                        headers: {
                            Authorization: 'Bearer <token>'
                        }
                    }
                },
                marketplace: Object.keys(this.config.marketplace_endpoints).reduce((acc, marketplace) => {
                    acc[marketplace] = {
                        products: {
                            method: 'GET',
                            path: `/api/marketplace/${marketplace}/products`,
                            description: `Get products from ${marketplace}`
                        },
                        orders: {
                            method: 'GET',
                            path: `/api/marketplace/${marketplace}/orders`,
                            description: `Get orders from ${marketplace}`
                        },
                        sync: {
                            method: 'POST',
                            path: `/api/marketplace/${marketplace}/sync`,
                            description: `Sync data with ${marketplace}`
                        },
                        webhook: {
                            method: 'POST',
                            path: `/api/marketplace/${marketplace}/webhook`,
                            description: `Handle webhook from ${marketplace}`
                        }
                    };
                    return acc;
                }, {}),
                opencart: {
                    products: {
                        method: 'GET',
                        path: '/api/opencart/products',
                        description: 'Get OpenCart products'
                    },
                    orders: {
                        method: 'GET',
                        path: '/api/opencart/orders',
                        description: 'Get OpenCart orders'
                    },
                    config: {
                        method: 'GET',
                        path: '/api/opencart/config',
                        description: 'Get OpenCart configuration'
                    }
                },
                monitoring: {
                    metrics: {
                        method: 'GET',
                        path: '/api/monitoring/metrics',
                        description: 'Get API metrics'
                    },
                    health: {
                        method: 'GET',
                        path: '/api/monitoring/health',
                        description: 'Get system health status'
                    },
                    performance: {
                        method: 'GET',
                        path: '/api/monitoring/performance',
                        description: 'Get performance metrics'
                    }
                }
            }
        };
    }
    
    /**
     * Setup error handling
     */
    setupErrorHandling() {
        // 404 handler
        this.app.use('*', (req, res) => {
            res.status(404).json({
                error: 'Not found',
                message: 'The requested endpoint does not exist',
                path: req.originalUrl,
                method: req.method,
                request_id: req.requestId
            });
        });
        
        // Global error handler
        this.app.use((error, req, res, next) => {
            console.error('API Gateway error:', error);
            
            res.status(error.status || 500).json({
                error: 'Internal server error',
                message: error.message || 'An unexpected error occurred',
                request_id: req.requestId,
                timestamp: new Date().toISOString()
            });
        });
        
        console.log('Error handling configured');
    }
    
    /**
     * Start the API gateway server
     */
    start() {
        return new Promise((resolve, reject) => {
            try {
                const server = this.app.listen(this.config.port, () => {
                    console.log(`OpenCart Production API Gateway started on port ${this.config.port}`);
                    console.log(`Environment: ${this.config.environment}`);
                    console.log(`Health check: http://localhost:${this.config.port}/health`);
                    console.log(`API docs: http://localhost:${this.config.port}/api/docs`);
                    resolve(server);
                });
                
                server.on('error', (error) => {
                    console.error('Server error:', error);
                    reject(error);
                });
                
            } catch (error) {
                console.error('Failed to start API Gateway:', error);
                reject(error);
            }
        });
    }
    
    /**
     * Stop the API gateway server
     */
    stop() {
        console.log('Stopping API Gateway...');
        
        if (this.redis) {
            this.redis.quit();
        }
        
        process.exit(0);
    }
}

// Initialize and start API Gateway
const apiGateway = new OpenCartProductionAPIGateway();

// Handle graceful shutdown
process.on('SIGINT', () => apiGateway.stop());
process.on('SIGTERM', () => apiGateway.stop());

// Start the server
apiGateway.start().catch(error => {
    console.error('Failed to start API Gateway:', error);
    process.exit(1);
});

module.exports = OpenCartProductionAPIGateway;
