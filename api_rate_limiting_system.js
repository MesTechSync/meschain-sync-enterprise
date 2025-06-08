/**
 * 🛡️ MesChain-Sync Advanced API Rate Limiting System
 * Tarih: 7 Haziran 2025
 * Amaç: Comprehensive API rate limiting, throttling ve abuse protection
 */

const rateLimit = require('express-rate-limit');
const slowDown = require('express-slow-down');
const express = require('express');

class AdvancedRateLimitingSystem {
    constructor() {
        this.limiters = new Map();
        this.slowDownLimiters = new Map();
        this.abuseDetection = new Map();
        this.customLimits = new Map();
        
        this.config = {
            // Default Rate Limits (per minute)
            defaultLimits: {
                guest: 100,        // Anonymous users
                user: 500,         // Authenticated users  
                premium: 1000,     // Premium users
                admin: 5000,       // Admin users
                system: 10000      // System/internal calls
            },
            
            // Endpoint-specific limits
            endpointLimits: {
                '/api/auth/login': { limit: 10, window: 15 * 60 * 1000 }, // 10 per 15 minutes
                '/api/auth/register': { limit: 5, window: 60 * 60 * 1000 }, // 5 per hour
                '/api/password/reset': { limit: 3, window: 60 * 60 * 1000 }, // 3 per hour
                '/api/marketplace/sync': { limit: 50, window: 60 * 1000 }, // 50 per minute
                '/api/products/bulk': { limit: 10, window: 60 * 1000 }, // 10 per minute
                '/api/orders/process': { limit: 100, window: 60 * 1000 }, // 100 per minute
                '/api/analytics/report': { limit: 20, window: 60 * 1000 }, // 20 per minute
                '/api/file/upload': { limit: 5, window: 60 * 1000 }, // 5 per minute
                '/api/webhook/*': { limit: 200, window: 60 * 1000 } // 200 per minute
            },
            
            // Marketplace-specific limits
            marketplaceLimits: {
                trendyol: { limit: 100, window: 60 * 1000 },
                n11: { limit: 80, window: 60 * 1000 },
                amazon: { limit: 60, window: 60 * 1000 },
                ebay: { limit: 90, window: 60 * 1000 },
                hepsiburada: { limit: 70, window: 60 * 1000 },
                ozon: { limit: 50, window: 60 * 1000 }
            },
            
            // Abuse detection thresholds
            abuseThresholds: {
                rapid_requests: 20,     // More than 20 requests in 10 seconds
                error_rate: 0.5,        // More than 50% error rate
                bandwidth_mb: 100,      // More than 100MB in 5 minutes
                unique_ips: 50          // More than 50 unique IPs from same user
            },
            
            // Slow down configuration
            slowDown: {
                delayAfter: 50,         // Start slowing down after 50 requests
                delayMs: 500,           // Add 500ms delay per request
                maxDelayMs: 20000       // Maximum 20 second delay
            }
        };
        
        this.initializeRateLimiting();
    }

    /**
     * 🚀 Initialize Rate Limiting System
     */
    async initializeRateLimiting() {
        console.log('🛡️ Advanced Rate Limiting System başlatılıyor...');
        
        try {
            // Create rate limiters
            this.createRateLimiters();
            
            // Create slow down limiters
            this.createSlowDownLimiters();
            
            // Initialize abuse detection
            this.initializeAbuseDetection();
            
            // Start monitoring
            this.startMonitoring();
            
            console.log('✅ Rate Limiting System hazır!');
            
        } catch (error) {
            console.error('❌ Rate Limiting System initialization hatası:', error);
        }
    }

    /**
     * 🎯 Create Rate Limiters
     */
    createRateLimiters() {
        // Guest/Anonymous rate limiter
        this.limiters.set('guest', rateLimit({
            windowMs: 60 * 1000, // 1 minute
            max: this.config.defaultLimits.guest,
            message: {
                error: 'Too many requests from this IP',
                type: 'RATE_LIMIT_EXCEEDED',
                limit: this.config.defaultLimits.guest,
                window: '1 minute',
                retryAfter: 60
            },
            standardHeaders: true,
            legacyHeaders: false,
            keyGenerator: (req) => {
                return req.ip + ':guest';
            },
            onLimitReached: (req, res, options) => {
                this.handleRateLimitExceeded(req, 'guest', options);
            }
        }));
        
        // User rate limiter
        this.limiters.set('user', rateLimit({
            windowMs: 60 * 1000,
            max: this.config.defaultLimits.user,
            message: {
                error: 'API rate limit exceeded for user',
                type: 'USER_RATE_LIMIT_EXCEEDED',
                limit: this.config.defaultLimits.user,
                window: '1 minute'
            },
            keyGenerator: (req) => {
                return req.user?.id || req.ip + ':user';
            }
        }));
        
        // Premium user rate limiter
        this.limiters.set('premium', rateLimit({
            windowMs: 60 * 1000,
            max: this.config.defaultLimits.premium,
            message: {
                error: 'Premium API rate limit exceeded',
                type: 'PREMIUM_RATE_LIMIT_EXCEEDED',
                limit: this.config.defaultLimits.premium,
                window: '1 minute'
            },
            keyGenerator: (req) => {
                return req.user?.id + ':premium';
            }
        }));
        
        // Admin rate limiter
        this.limiters.set('admin', rateLimit({
            windowMs: 60 * 1000,
            max: this.config.defaultLimits.admin,
            keyGenerator: (req) => {
                return req.user?.id + ':admin';
            }
        }));
        
        // Endpoint-specific rate limiters
        Object.entries(this.config.endpointLimits).forEach(([endpoint, config]) => {
            this.limiters.set(endpoint, rateLimit({
                windowMs: config.window,
                max: config.limit,
                message: {
                    error: `Rate limit exceeded for ${endpoint}`,
                    type: 'ENDPOINT_RATE_LIMIT_EXCEEDED',
                    endpoint: endpoint,
                    limit: config.limit,
                    window: config.window
                },
                keyGenerator: (req) => {
                    return req.ip + ':' + endpoint;
                }
            }));
        });
        
        // Marketplace-specific rate limiters
        Object.entries(this.config.marketplaceLimits).forEach(([marketplace, config]) => {
            this.limiters.set(`marketplace:${marketplace}`, rateLimit({
                windowMs: config.window,
                max: config.limit,
                message: {
                    error: `${marketplace} API rate limit exceeded`,
                    type: 'MARKETPLACE_RATE_LIMIT_EXCEEDED',
                    marketplace: marketplace,
                    limit: config.limit
                },
                keyGenerator: (req) => {
                    return (req.user?.id || req.ip) + ':' + marketplace;
                }
            }));
        });
    }

    /**
     * 🐌 Create Slow Down Limiters
     */
    createSlowDownLimiters() {
        // General slow down
        this.slowDownLimiters.set('general', slowDown({
            windowMs: 60 * 1000,
            delayAfter: this.config.slowDown.delayAfter,
            delayMs: this.config.slowDown.delayMs,
            maxDelayMs: this.config.slowDown.maxDelayMs,
            keyGenerator: (req) => {
                return req.user?.id || req.ip;
            }
        }));
        
        // Heavy endpoint slow down
        this.slowDownLimiters.set('heavy', slowDown({
            windowMs: 60 * 1000,
            delayAfter: 10, // More aggressive for heavy endpoints
            delayMs: 1000,
            maxDelayMs: 30000,
            keyGenerator: (req) => {
                return req.user?.id || req.ip;
            }
        }));
    }

    /**
     * 🕵️ Initialize Abuse Detection
     */
    initializeAbuseDetection() {
        // Rapid request detection
        this.abuseDetection.set('rapid_requests', new Map());
        
        // Error rate tracking
        this.abuseDetection.set('error_rate', new Map());
        
        // Bandwidth usage tracking
        this.abuseDetection.set('bandwidth', new Map());
        
        // IP diversity tracking
        this.abuseDetection.set('ip_diversity', new Map());
        
        // Clean up abuse detection data every 5 minutes
        setInterval(() => {
            this.cleanupAbuseDetection();
        }, 5 * 60 * 1000);
    }

    /**
     * 🚦 Get Rate Limiter Middleware
     */
    getRateLimiter(type = 'guest', options = {}) {
        const limiter = this.limiters.get(type);
        if (!limiter) {
            console.warn(`⚠️ Rate limiter not found: ${type}, using guest limiter`);
            return this.limiters.get('guest');
        }
        return limiter;
    }

    /**
     * 🎭 Dynamic Rate Limiter Based on User/Context
     */
    getDynamicRateLimiter() {
        return (req, res, next) => {
            let limiterType = 'guest';
            
            // Determine limiter type based on user
            if (req.user) {
                if (req.user.role === 'admin') {
                    limiterType = 'admin';
                } else if (req.user.subscription === 'premium') {
                    limiterType = 'premium';
                } else {
                    limiterType = 'user';
                }
            }
            
            // Check for endpoint-specific limiters
            const endpointLimiter = this.findEndpointLimiter(req.path);
            if (endpointLimiter) {
                return endpointLimiter(req, res, next);
            }
            
            // Check for marketplace-specific limiters
            const marketplaceLimiter = this.findMarketplaceLimiter(req);
            if (marketplaceLimiter) {
                return marketplaceLimiter(req, res, next);
            }
            
            // Apply general rate limiter
            const limiter = this.getRateLimiter(limiterType);
            return limiter(req, res, next);
        };
    }

    /**
     * 🎯 Find Endpoint-Specific Limiter
     */
    findEndpointLimiter(path) {
        for (const [endpoint, limiter] of this.limiters.entries()) {
            if (endpoint.includes('/api/') && this.matchesPath(path, endpoint)) {
                return limiter;
            }
        }
        return null;
    }

    /**
     * 🏪 Find Marketplace-Specific Limiter
     */
    findMarketplaceLimiter(req) {
        const marketplace = req.headers['x-marketplace'] || 
                          req.query.marketplace || 
                          req.body?.marketplace;
        
        if (marketplace && this.limiters.has(`marketplace:${marketplace}`)) {
            return this.limiters.get(`marketplace:${marketplace}`);
        }
        return null;
    }

    /**
     * 🔍 Path Matching Helper
     */
    matchesPath(path, pattern) {
        if (pattern.includes('*')) {
            const regex = new RegExp(pattern.replace(/\*/g, '.*'));
            return regex.test(path);
        }
        return path === pattern;
    }

    /**
     * 🚨 Handle Rate Limit Exceeded
     */
    handleRateLimitExceeded(req, limiterType, options) {
        const clientId = req.user?.id || req.ip;
        const timestamp = Date.now();
        
        // Log rate limit exceeded
        console.warn(`🚨 Rate limit exceeded: ${limiterType} - ${clientId} - ${req.path}`);
        
        // Track abuse patterns
        this.trackAbusePattern(clientId, 'rate_limit_exceeded', {
            limiterType,
            path: req.path,
            timestamp,
            ip: req.ip,
            userAgent: req.get('User-Agent')
        });
    }

    /**
     * 👮 Track Abuse Patterns
     */
    trackAbusePattern(clientId, type, data) {
        const abuseData = this.abuseDetection.get(type) || new Map();
        
        if (!abuseData.has(clientId)) {
            abuseData.set(clientId, []);
        }
        
        const clientData = abuseData.get(clientId);
        clientData.push({
            timestamp: Date.now(),
            ...data
        });
        
        // Keep only last 100 entries per client
        if (clientData.length > 100) {
            clientData.splice(0, clientData.length - 100);
        }
        
        abuseData.set(clientId, clientData);
        this.abuseDetection.set(type, abuseData);
        
        // Check for abuse patterns
        this.checkAbusePatterns(clientId, type, clientData);
    }

    /**
     * 🔍 Check Abuse Patterns
     */
    checkAbusePatterns(clientId, type, data) {
        const now = Date.now();
        const recent = data.filter(d => now - d.timestamp < 5 * 60 * 1000); // Last 5 minutes
        
        let isAbuse = false;
        let abuseReason = '';
        
        // Rapid requests detection
        if (type === 'rate_limit_exceeded') {
            const rapidRequests = recent.filter(d => now - d.timestamp < 10 * 1000); // Last 10 seconds
            if (rapidRequests.length > this.config.abuseThresholds.rapid_requests) {
                isAbuse = true;
                abuseReason = 'Rapid requests detected';
            }
        }
        
        // High error rate detection
        const errorCount = recent.filter(d => d.error).length;
        if (errorCount > 0 && errorCount / recent.length > this.config.abuseThresholds.error_rate) {
            isAbuse = true;
            abuseReason = 'High error rate detected';
        }
        
        if (isAbuse) {
            this.handleAbuseDetected(clientId, abuseReason, recent);
        }
    }

    /**
     * 🚫 Handle Abuse Detected
     */
    handleAbuseDetected(clientId, reason, data) {
        console.error(`🚫 ABUSE DETECTED: ${clientId} - ${reason}`);
        
        // Add to abuse list with temporary ban
        this.addToAbuseList(clientId, reason, 30 * 60 * 1000); // 30 minute ban
    }

    /**
     * 📝 Add to Abuse List
     */
    addToAbuseList(clientId, reason, duration) {
        const abuseList = this.abuseDetection.get('abuse_list') || new Map();
        
        abuseList.set(clientId, {
            reason,
            bannedAt: Date.now(),
            bannedUntil: Date.now() + duration,
            strikes: (abuseList.get(clientId)?.strikes || 0) + 1
        });
        
        this.abuseDetection.set('abuse_list', abuseList);
    }

    /**
     * 🚫 Abuse Detection Middleware
     */
    getAbuseDetectionMiddleware() {
        return (req, res, next) => {
            const clientId = req.user?.id || req.ip;
            const abuseList = this.abuseDetection.get('abuse_list') || new Map();
            
            if (abuseList.has(clientId)) {
                const abuseData = abuseList.get(clientId);
                
                if (Date.now() < abuseData.bannedUntil) {
                    return res.status(429).json({
                        error: 'Client temporarily banned due to abuse',
                        type: 'CLIENT_BANNED',
                        reason: abuseData.reason,
                        bannedUntil: new Date(abuseData.bannedUntil).toISOString(),
                        strikes: abuseData.strikes
                    });
                } else {
                    // Ban expired, remove from list
                    abuseList.delete(clientId);
                }
            }
            
            next();
        };
    }

    /**
     * 🧹 Cleanup Abuse Detection Data
     */
    cleanupAbuseDetection() {
        const now = Date.now();
        const maxAge = 24 * 60 * 60 * 1000; // 24 hours
        
        this.abuseDetection.forEach((data, type) => {
            if (data instanceof Map) {
                data.forEach((entries, clientId) => {
                    if (Array.isArray(entries)) {
                        // Clean old entries
                        const filtered = entries.filter(entry => now - entry.timestamp < maxAge);
                        if (filtered.length === 0) {
                            data.delete(clientId);
                        } else {
                            data.set(clientId, filtered);
                        }
                    }
                });
            }
        });
    }

    /**
     * 📊 Get Rate Limiting Statistics
     */
    getStatistics() {
        const stats = {
            limiters: this.limiters.size,
            abuseDetections: 0,
            activeBans: 0,
            totalRequests: 0,
            blockedRequests: 0
        };
        
        // Count abuse detections
        this.abuseDetection.forEach((data, type) => {
            if (data instanceof Map) {
                stats.abuseDetections += data.size;
            }
        });
        
        // Count active bans
        const abuseList = this.abuseDetection.get('abuse_list') || new Map();
        const now = Date.now();
        abuseList.forEach((data) => {
            if (now < data.bannedUntil) {
                stats.activeBans++;
            }
        });
        
        return stats;
    }

    /**
     * 📊 Start Monitoring
     */
    startMonitoring() {
        // Log statistics every 5 minutes
        setInterval(() => {
            const stats = this.getStatistics();
            console.log('📊 Rate Limiting Stats:', stats);
        }, 5 * 60 * 1000);
    }

    /**
     * 🎛️ Express Middleware Setup
     */
    setupMiddleware(app) {
        // Apply abuse detection first
        app.use(this.getAbuseDetectionMiddleware());
        
        // Apply general slow down
        app.use(this.slowDownLimiters.get('general'));
        
        // Apply dynamic rate limiting
        app.use(this.getDynamicRateLimiter());
        
        // Heavy endpoints get extra slow down
        app.use(['/api/products/bulk', '/api/file/upload', '/api/analytics/report'], 
               this.slowDownLimiters.get('heavy'));
        
        console.log('✅ Rate limiting middleware kuruldu');
    }
}

// Export for use in other modules
module.exports = AdvancedRateLimitingSystem;

// Example usage
if (require.main === module) {
    const app = express();
    const rateLimiting = new AdvancedRateLimitingSystem();
    
    // Setup middleware
    rateLimiting.setupMiddleware(app);
    
    // Test endpoints
    app.get('/api/test', (req, res) => {
        res.json({ message: 'Test endpoint', timestamp: new Date() });
    });
    
    app.get('/api/heavy', (req, res) => {
        res.json({ message: 'Heavy endpoint', timestamp: new Date() });
    });
    
    app.get('/stats', (req, res) => {
        res.json(rateLimiting.getStatistics());
    });
    
    const PORT = 3098;
    app.listen(PORT, () => {
        console.log(`🛡️ Rate Limiting Test Server running on http://localhost:${PORT}`);
        console.log(`📊 Statistics: http://localhost:${PORT}/stats`);
    });
} 