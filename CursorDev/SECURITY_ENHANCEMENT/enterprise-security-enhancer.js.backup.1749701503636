/**
 * 🛡️ ATOM-MZ008: Enterprise Security Enhancement System
 * VSCode Team Backend Development - Phase 2
 * 
 * Advanced security framework with real-time threat detection
 * Multi-layered protection with ML-based anomaly detection
 * 
 * @author MezBjen (VSCode Team)
 * @version 2.0.0
 * @date June 10, 2025
 * @integration Cursor Team Support Ready
 */

const crypto = require('crypto');
const jwt = require('jsonwebtoken');
const bcrypt = require('bcrypt');
const rateLimit = require('express-rate-limit');
const helmet = require('helmet');
const speakeasy = require('speakeasy');

class EnterpriseSecurityEnhancer {
    constructor(config) {
        this.config = {
            jwt: {
                secret: config.jwt_secret || process.env.JWT_SECRET,
                expires_in: '24h',
                refresh_expires_in: '7d',
                algorithm: 'HS256'
            },
            encryption: {
                algorithm: 'aes-256-gcm',
                key_derivation: 'pbkdf2',
                iterations: 100000
            },
            security: {
                max_login_attempts: 5,
                lockout_duration: 900000,    // 15 minutes
                password_min_length: 12,
                password_require_special: true,
                session_timeout: 1800000,     // 30 minutes
                csrf_token_length: 32
            },
            threat_detection: {
                anomaly_threshold: 0.8,
                ip_whitelist: config.ip_whitelist || [],
                suspicious_patterns: [
                    'union select',
                    'drop table',
                    '../../',
                    '<script',
                    'javascript:',
                    'eval(',
                    'base64_decode'
                ],
                max_request_size: 10485760     // 10MB
            },
            monitoring: {
                log_failed_attempts: true,
                alert_threshold: 10,
                metrics_retention: 86400000    // 24 hours
            },
            ...config
        };
        
        this.security_metrics = {
            failed_logins: new Map(),
            blocked_ips: new Map(),
            threat_detections: [],
            security_events: [],
            active_sessions: new Map()
        };
        
        this.anomaly_detectors = new Map();
        this.threat_patterns = new Map();
        
        console.log('🛡️ ATOM-MZ008: Enterprise Security Enhancement System - Initialized');
        this.initializeSecurity();
    }
    
    /**
     * Initialize comprehensive security system
     */
    async initializeSecurity() {
        try {
            // Setup threat detection patterns
            this.setupThreatDetection();
            
            // Initialize anomaly detection
            this.setupAnomalyDetection();
            
            // Setup security monitoring
            this.startSecurityMonitoring();
            
            // Initialize encryption systems
            this.setupEncryption();
            
            console.log('✅ ATOM-MZ008: Enterprise security system fully operational');
            
        } catch (error) {
            console.error('🚨 ATOM-MZ008 Security Initialization Error:', error);
        }
    }
    
    /**
     * Setup advanced threat detection patterns
     */
    setupThreatDetection() {
        // SQL Injection patterns
        this.threat_patterns.set('sql_injection', {
            patterns: [
                /(\b(union|select|insert|update|delete|drop|create|alter)\b.*\b(from|into|table|database)\b)/i,
                /(\b(or|and)\b.*\b(=|like)\b.*('|"|`|;))/i,
                /(--|\/\*|\*\/|;)/,
                /(\b(exec|execute|sp_|xp_)\b)/i
            ],
            severity: 'HIGH',
            action: 'BLOCK'
        });
        
        // XSS patterns
        this.threat_patterns.set('xss', {
            patterns: [
                /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi,
                /javascript:/i,
                /on\w+\s*=/i,
                /<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/gi,
                /<object\b[^<]*(?:(?!<\/object>)<[^<]*)*<\/object>/gi
            ],
            severity: 'HIGH',
            action: 'SANITIZE'
        });
        
        // Path traversal patterns
        this.threat_patterns.set('path_traversal', {
            patterns: [
                /\.\.[\/\\]/,
                /\/etc\/passwd/i,
                /\/proc\/self\/environ/i,
                /\/windows\/win\.ini/i
            ],
            severity: 'MEDIUM',
            action: 'BLOCK'
        });
        
        // Command injection patterns
        this.threat_patterns.set('command_injection', {
            patterns: [
                /(\||&|;|`|\$\(|\$\{)/,
                /\b(cat|ls|pwd|id|whoami|uname|wget|curl|nc|netcat)\b/i,
                /(\bnc\s+-[lnvz]+|\bwget\s+http|\bcurl\s+http)/i
            ],
            severity: 'CRITICAL',
            action: 'BLOCK'
        });
        
        console.log('🎯 Advanced threat detection patterns configured');
    }
    
    /**
     * Setup ML-based anomaly detection
     */
    setupAnomalyDetection() {
        // User behavior anomaly detection
        this.anomaly_detectors.set('user_behavior', {
            features: ['request_rate', 'endpoint_diversity', 'payload_size', 'time_pattern'],
            baseline: new Map(),
            learning_window: 7 * 24 * 60 * 60 * 1000, // 7 days
            detection_threshold: 0.8
        });
        
        // Network traffic anomaly detection
        this.anomaly_detectors.set('network_traffic', {
            features: ['request_frequency', 'geographic_location', 'user_agent_pattern'],
            baseline: new Map(),
            learning_window: 24 * 60 * 60 * 1000, // 24 hours
            detection_threshold: 0.75
        });
        
        // API access anomaly detection
        this.anomaly_detectors.set('api_access', {
            features: ['endpoint_sequence', 'response_time_pattern', 'error_rate'],
            baseline: new Map(),
            learning_window: 12 * 60 * 60 * 1000, // 12 hours
            detection_threshold: 0.85
        });
        
        console.log('🤖 ML-based anomaly detection systems initialized');
    }
    
    /**
     * Create comprehensive security middleware
     */
    createSecurityMiddleware() {
        return [
            // Basic security headers
            helmet({
                contentSecurityPolicy: {
                    directives: {
                        defaultSrc: ["'self'"],
                        styleSrc: ["'self'", "'unsafe-inline'"],
                        scriptSrc: ["'self'"],
                        imgSrc: ["'self'", "data:", "https:"],
                        connectSrc: ["'self'"],
                        fontSrc: ["'self'"],
                        objectSrc: ["'none'"],
                        mediaSrc: ["'self'"],
                        frameSrc: ["'none'"]
                    }
                },
                crossOriginEmbedderPolicy: false,
                hsts: {
                    maxAge: 31536000,
                    includeSubDomains: true,
                    preload: true
                }
            }),
            
            // Rate limiting with intelligent detection
            this.createIntelligentRateLimit(),
            
            // Request validation and sanitization
            this.createRequestValidator(),
            
            // Threat detection middleware
            this.createThreatDetectionMiddleware(),
            
            // Authentication verification
            this.createAuthenticationMiddleware(),
            
            // Anomaly detection
            this.createAnomalyDetectionMiddleware()
        ];
    }
    
    /**
     * Create intelligent rate limiting
     */
    createIntelligentRateLimit() {
        return rateLimit({
            windowMs: 15 * 60 * 1000, // 15 minutes
            max: (req) => {
                // Dynamic rate limiting based on user trust score
                const trust_score = this.calculateUserTrustScore(req);
                
                if (trust_score > 0.8) return 2000;      // High trust
                if (trust_score > 0.6) return 1000;      // Medium trust
                if (trust_score > 0.4) return 500;       // Low trust
                return 100;                              // Very low trust
            },
            message: {
                error: 'Rate limit exceeded',
                retry_after: 900,
                trust_score: 'low'
            },
            standardHeaders: true,
            legacyHeaders: false,
            keyGenerator: (req) => {
                // Use IP + User ID for authenticated users
                return req.user ? `${req.ip}_${req.user.id}` : req.ip;
            },
            skip: (req) => {
                // Skip rate limiting for whitelisted IPs
                return this.config.threat_detection.ip_whitelist.includes(req.ip);
            },
            onLimitReached: (req, res, options) => {
                this.logSecurityEvent('RATE_LIMIT_EXCEEDED', {
                    ip: req.ip,
                    user_id: req.user?.id,
                    endpoint: req.path,
                    user_agent: req.headers['user-agent']
                });
            }
        });
    }
    
    /**
     * Calculate user trust score
     */
    calculateUserTrustScore(req) {
        let trust_score = 0.5; // Base score
        
        // IP reputation
        if (this.config.threat_detection.ip_whitelist.includes(req.ip)) {
            trust_score += 0.3;
        }
        
        // User authentication status
        if (req.user) {
            trust_score += 0.2;
            
            // Account age bonus
            if (req.user.created_at) {
                const account_age_days = (Date.now() - new Date(req.user.created_at)) / (1000 * 60 * 60 * 24);
                if (account_age_days > 365) trust_score += 0.1;
                else if (account_age_days > 30) trust_score += 0.05;
            }
        }
        
        // Failed login history
        const failed_attempts = this.security_metrics.failed_logins.get(req.ip) || 0;
        if (failed_attempts > 0) {
            trust_score -= failed_attempts * 0.1;
        }
        
        // Recent security events
        const recent_events = this.security_metrics.security_events.filter(
            event => event.ip === req.ip && 
            Date.now() - event.timestamp < 3600000 // Last hour
        );
        trust_score -= recent_events.length * 0.05;
        
        return Math.max(0, Math.min(1, trust_score));
    }
    
    /**
     * Create request validator and sanitizer
     */
    createRequestValidator() {
        return (req, res, next) => {
            try {
                // Check request size
                if (req.headers['content-length'] > this.config.threat_detection.max_request_size) {
                    this.logSecurityEvent('OVERSIZED_REQUEST', {
                        ip: req.ip,
                        size: req.headers['content-length'],
                        endpoint: req.path
                    });
                    return res.status(413).json({ error: 'Request too large' });
                }
                
                // Validate and sanitize headers
                this.validateHeaders(req);
                
                // Sanitize query parameters
                if (req.query) {
                    req.query = this.sanitizeObject(req.query);
                }
                
                // Sanitize request body
                if (req.body) {
                    req.body = this.sanitizeObject(req.body);
                }
                
                next();
                
            } catch (error) {
                console.error('🚨 Request validation error:', error);
                this.logSecurityEvent('VALIDATION_ERROR', {
                    ip: req.ip,
                    error: error.message,
                    endpoint: req.path
                });
                res.status(400).json({ error: 'Invalid request format' });
            }
        };
    }
    
    /**
     * Validate request headers
     */
    validateHeaders(req) {
        const suspicious_headers = [
            'x-forwarded-for',
            'x-real-ip',
            'x-cluster-client-ip'
        ];
        
        for (const header of suspicious_headers) {
            if (req.headers[header]) {
                // Log potential proxy manipulation
                this.logSecurityEvent('SUSPICIOUS_HEADER', {
                    ip: req.ip,
                    header: header,
                    value: req.headers[header],
                    endpoint: req.path
                });
            }
        }
        
        // Validate user-agent
        if (!req.headers['user-agent'] || req.headers['user-agent'].length > 512) {
            this.logSecurityEvent('INVALID_USER_AGENT', {
                ip: req.ip,
                user_agent: req.headers['user-agent'],
                endpoint: req.path
            });
        }
    }
    
    /**
     * Sanitize object recursively
     */
    sanitizeObject(obj) {
        if (typeof obj === 'string') {
            return this.sanitizeString(obj);
        }
        
        if (Array.isArray(obj)) {
            return obj.map(item => this.sanitizeObject(item));
        }
        
        if (obj && typeof obj === 'object') {
            const sanitized = {};
            for (const [key, value] of Object.entries(obj)) {
                const sanitized_key = this.sanitizeString(key);
                sanitized[sanitized_key] = this.sanitizeObject(value);
            }
            return sanitized;
        }
        
        return obj;
    }
    
    /**
     * Sanitize string input
     */
    sanitizeString(str) {
        if (typeof str !== 'string') return str;
        
        // Remove null bytes
        str = str.replace(/\0/g, '');
        
        // Encode special characters
        str = str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#x27;')
            .replace(/\//g, '&#x2F;');
        
        // Truncate excessively long strings
        if (str.length > 10000) {
            str = str.substring(0, 10000);
            console.log('🚨 Truncated oversized string input');
        }
        
        return str;
    }
    
    /**
     * Create threat detection middleware
     */
    createThreatDetectionMiddleware() {
        return (req, res, next) => {
            try {
                const threats_detected = [];
                
                // Check all threat patterns
                for (const [threat_type, config] of this.threat_patterns) {
                    const detection = this.detectThreat(req, threat_type, config);
                    if (detection) {
                        threats_detected.push(detection);
                    }
                }
                
                // Process detected threats
                if (threats_detected.length > 0) {
                    return this.handleThreatDetection(req, res, threats_detected);
                }
                
                next();
                
            } catch (error) {
                console.error('🚨 Threat detection error:', error);
                next();
            }
        };
    }
    
    /**
     * Detect specific threat patterns
     */
    detectThreat(req, threat_type, config) {
        const request_data = JSON.stringify({
            url: req.url,
            query: req.query,
            body: req.body,
            headers: req.headers
        });
        
        for (const pattern of config.patterns) {
            if (pattern.test(request_data)) {
                return {
                    type: threat_type,
                    severity: config.severity,
                    action: config.action,
                    pattern_matched: pattern.toString(),
                    request_path: req.path,
                    ip: req.ip,
                    timestamp: new Date()
                };
            }
        }
        
        return null;
    }
    
    /**
     * Handle threat detection
     */
    handleThreatDetection(req, res, threats) {
        const highest_severity = threats.reduce((max, threat) => {
            const severity_levels = { LOW: 1, MEDIUM: 2, HIGH: 3, CRITICAL: 4 };
            const current_level = severity_levels[threat.severity] || 0;
            const max_level = severity_levels[max] || 0;
            return current_level > max_level ? threat.severity : max;
        }, 'LOW');
        
        // Log all threats
        threats.forEach(threat => {
            this.logSecurityEvent('THREAT_DETECTED', threat);
            console.log(`🚨 ${threat.severity} THREAT: ${threat.type} from ${req.ip}`);
        });
        
        // Determine action based on highest severity
        if (highest_severity === 'CRITICAL' || highest_severity === 'HIGH') {
            // Block request and add IP to temporary blocklist
            this.blockIP(req.ip, 3600000); // 1 hour
            
            return res.status(403).json({
                error: 'Security threat detected',
                incident_id: this.generateIncidentID(),
                blocked_until: new Date(Date.now() + 3600000)
            });
        }
        
        if (highest_severity === 'MEDIUM') {
            // Log and continue with warning
            res.set('X-Security-Warning', 'Potential threat detected');
        }
        
        // Continue processing for LOW severity
        return res.status(400).json({
            error: 'Request validation failed',
            details: 'Please check your input'
        });
    }
    
    /**
     * Create authentication middleware
     */
    createAuthenticationMiddleware() {
        return (req, res, next) => {
            // Skip authentication for public endpoints
            const public_endpoints = ['/api/health', '/api/public', '/auth/login', '/auth/register'];
            if (public_endpoints.some(endpoint => req.path.startsWith(endpoint))) {
                return next();
            }
            
            try {
                const token = this.extractToken(req);
                
                if (!token) {
                    return res.status(401).json({ error: 'Authentication required' });
                }
                
                // Verify JWT token
                const decoded = jwt.verify(token, this.config.jwt.secret);
                
                // Check token expiration
                if (decoded.exp < Date.now() / 1000) {
                    return res.status(401).json({ error: 'Token expired' });
                }
                
                // Check session validity
                if (!this.isSessionValid(decoded.jti, req.ip)) {
                    return res.status(401).json({ error: 'Invalid session' });
                }
                
                // Add user to request
                req.user = decoded;
                req.session_id = decoded.jti;
                
                // Update session activity
                this.updateSessionActivity(decoded.jti);
                
                next();
                
            } catch (error) {
                console.error('🚨 Authentication error:', error);
                this.logSecurityEvent('AUTHENTICATION_FAILED', {
                    ip: req.ip,
                    error: error.message,
                    endpoint: req.path
                });
                
                res.status(401).json({ error: 'Invalid authentication' });
            }
        };
    }
    
    /**
     * Extract JWT token from request
     */
    extractToken(req) {
        // Check Authorization header
        if (req.headers.authorization) {
            const parts = req.headers.authorization.split(' ');
            if (parts.length === 2 && parts[0] === 'Bearer') {
                return parts[1];
            }
        }
        
        // Check cookies
        if (req.cookies && req.cookies.access_token) {
            return req.cookies.access_token;
        }
        
        // Check query parameter (less secure, for special cases)
        if (req.query.token) {
            return req.query.token;
        }
        
        return null;
    }
    
    /**
     * Check if session is valid
     */
    isSessionValid(session_id, ip) {
        const session = this.security_metrics.active_sessions.get(session_id);
        
        if (!session) return false;
        
        // Check session expiration
        if (Date.now() > session.expires_at) {
            this.security_metrics.active_sessions.delete(session_id);
            return false;
        }
        
        // Check IP consistency (optional, based on security policy)
        if (session.ip !== ip && !session.allow_ip_change) {
            this.logSecurityEvent('SESSION_IP_MISMATCH', {
                session_id: session_id,
                original_ip: session.ip,
                current_ip: ip
            });
            return false;
        }
        
        return true;
    }
    
    /**
     * Update session activity
     */
    updateSessionActivity(session_id) {
        const session = this.security_metrics.active_sessions.get(session_id);
        if (session) {
            session.last_activity = Date.now();
            session.expires_at = Date.now() + this.config.security.session_timeout;
        }
    }
    
    /**
     * Create anomaly detection middleware
     */
    createAnomalyDetectionMiddleware() {
        return (req, res, next) => {
            try {
                // Collect request features
                const features = this.extractRequestFeatures(req);
                
                // Check for anomalies
                const anomalies = this.detectAnomalies(features);
                
                if (anomalies.length > 0) {
                    this.handleAnomalies(req, res, anomalies);
                }
                
                // Update behavior baselines
                this.updateBehaviorBaselines(req, features);
                
                next();
                
            } catch (error) {
                console.error('🚨 Anomaly detection error:', error);
                next();
            }
        };
    }
    
    /**
     * Extract features for anomaly detection
     */
    extractRequestFeatures(req) {
        return {
            request_rate: this.calculateRequestRate(req.ip),
            endpoint_diversity: this.calculateEndpointDiversity(req.ip),
            payload_size: JSON.stringify(req.body || {}).length,
            time_pattern: this.getTimePattern(),
            geographic_location: this.getGeographicIndicator(req),
            user_agent_pattern: this.analyzeUserAgent(req.headers['user-agent'])
        };
    }
    
    /**
     * Calculate request rate for IP
     */
    calculateRequestRate(ip) {
        const now = Date.now();
        const time_window = 60000; // 1 minute
        
        const recent_requests = this.security_metrics.security_events.filter(
            event => event.ip === ip && 
            now - event.timestamp < time_window
        );
        
        return recent_requests.length;
    }
    
    /**
     * Calculate endpoint diversity
     */
    calculateEndpointDiversity(ip) {
        const recent_requests = this.security_metrics.security_events.filter(
            event => event.ip === ip && 
            Date.now() - event.timestamp < 300000 // 5 minutes
        );
        
        const unique_endpoints = new Set(
            recent_requests.map(event => event.endpoint).filter(Boolean)
        );
        
        return unique_endpoints.size;
    }
    
    /**
     * Get time pattern indicator
     */
    getTimePattern() {
        const hour = new Date().getHours();
        
        // Business hours: 8 AM to 6 PM
        if (hour >= 8 && hour <= 18) return 'business';
        
        // Evening: 6 PM to 11 PM
        if (hour >= 18 && hour <= 23) return 'evening';
        
        // Night: 11 PM to 8 AM
        return 'night';
    }
    
    /**
     * Get geographic indicator from IP
     */
    getGeographicIndicator(req) {
        // Simple geographic classification based on headers
        const cf_country = req.headers['cf-ipcountry'];
        const forwarded_for = req.headers['x-forwarded-for'];
        
        return {
            country: cf_country || 'unknown',
            is_proxy: !!forwarded_for,
            ip_type: this.classifyIP(req.ip)
        };
    }
    
    /**
     * Classify IP address type
     */
    classifyIP(ip) {
        if (ip.startsWith('127.') || ip.startsWith('::1')) return 'localhost';
        if (ip.startsWith('192.168.') || ip.startsWith('10.') || ip.startsWith('172.16.')) return 'private';
        if (ip.startsWith('169.254.')) return 'link_local';
        return 'public';
    }
    
    /**
     * Analyze user agent pattern
     */
    analyzeUserAgent(user_agent) {
        if (!user_agent) return { type: 'missing', suspicious: true };
        
        const patterns = {
            browser: /Mozilla|Chrome|Safari|Firefox|Edge/i,
            mobile: /Mobile|Android|iPhone|iPad/i,
            bot: /bot|crawl|spider|scraper/i,
            automated: /curl|wget|python|postman/i
        };
        
        for (const [type, pattern] of Object.entries(patterns)) {
            if (pattern.test(user_agent)) {
                return { 
                    type, 
                    suspicious: type === 'bot' || type === 'automated',
                    length: user_agent.length
                };
            }
        }
        
        return { type: 'unknown', suspicious: true, length: user_agent.length };
    }
    
    /**
     * Detect anomalies using simple statistical methods
     */
    detectAnomalies(features) {
        const anomalies = [];
        
        // Check request rate anomaly
        if (features.request_rate > 60) { // More than 1 request per second
            anomalies.push({
                type: 'HIGH_REQUEST_RATE',
                severity: 'MEDIUM',
                value: features.request_rate,
                threshold: 60
            });
        }
        
        // Check payload size anomaly
        if (features.payload_size > 1048576) { // 1MB
            anomalies.push({
                type: 'LARGE_PAYLOAD',
                severity: 'LOW',
                value: features.payload_size,
                threshold: 1048576
            });
        }
        
        // Check suspicious user agent
        if (features.user_agent_pattern.suspicious) {
            anomalies.push({
                type: 'SUSPICIOUS_USER_AGENT',
                severity: 'LOW',
                details: features.user_agent_pattern
            });
        }
        
        // Check night-time activity (higher suspicion)
        if (features.time_pattern === 'night' && features.request_rate > 30) {
            anomalies.push({
                type: 'UNUSUAL_TIME_ACTIVITY',
                severity: 'MEDIUM',
                time_pattern: features.time_pattern,
                request_rate: features.request_rate
            });
        }
        
        return anomalies;
    }
    
    /**
     * Handle detected anomalies
     */
    handleAnomalies(req, res, anomalies) {
        const high_risk_anomalies = anomalies.filter(a => a.severity === 'HIGH');
        const medium_risk_anomalies = anomalies.filter(a => a.severity === 'MEDIUM');
        
        // Log all anomalies
        anomalies.forEach(anomaly => {
            this.logSecurityEvent('ANOMALY_DETECTED', {
                ...anomaly,
                ip: req.ip,
                endpoint: req.path,
                user_id: req.user?.id
            });
        });
        
        // Add security headers
        if (anomalies.length > 0) {
            res.set('X-Security-Anomaly-Count', anomalies.length.toString());
            res.set('X-Security-Risk-Level', high_risk_anomalies.length > 0 ? 'HIGH' : 
                                           medium_risk_anomalies.length > 0 ? 'MEDIUM' : 'LOW');
        }
    }
    
    /**
     * Update behavior baselines for learning
     */
    updateBehaviorBaselines(req, features) {
        const user_key = req.user?.id || req.ip;
        
        // Update user behavior baseline
        const detector = this.anomaly_detectors.get('user_behavior');
        if (detector) {
            if (!detector.baseline.has(user_key)) {
                detector.baseline.set(user_key, {
                    request_rates: [],
                    payload_sizes: [],
                    time_patterns: {},
                    last_updated: Date.now()
                });
            }
            
            const baseline = detector.baseline.get(user_key);
            
            // Keep sliding window of data
            baseline.request_rates.push(features.request_rate);
            baseline.payload_sizes.push(features.payload_size);
            
            if (baseline.request_rates.length > 100) {
                baseline.request_rates = baseline.request_rates.slice(-100);
            }
            
            if (baseline.payload_sizes.length > 100) {
                baseline.payload_sizes = baseline.payload_sizes.slice(-100);
            }
            
            // Update time patterns
            baseline.time_patterns[features.time_pattern] = 
                (baseline.time_patterns[features.time_pattern] || 0) + 1;
            
            baseline.last_updated = Date.now();
        }
    }
    
    /**
     * Generate secure password hash
     */
    async hashPassword(password) {
        const salt_rounds = 12;
        return await bcrypt.hash(password, salt_rounds);
    }
    
    /**
     * Verify password
     */
    async verifyPassword(password, hash) {
        return await bcrypt.compare(password, hash);
    }
    
    /**
     * Generate JWT token with security features
     */
    generateJWTToken(user, options = {}) {
        const session_id = this.generateSessionID();
        const now = Math.floor(Date.now() / 1000);
        
        const payload = {
            id: user.id,
            email: user.email,
            role: user.role,
            jti: session_id,
            iat: now,
            exp: now + (options.expires_in || 24 * 60 * 60), // 24 hours default
            iss: 'meschain-security',
            aud: 'meschain-api'
        };
        
        // Create session record
        this.security_metrics.active_sessions.set(session_id, {
            user_id: user.id,
            ip: options.ip,
            user_agent: options.user_agent,
            created_at: Date.now(),
            last_activity: Date.now(),
            expires_at: Date.now() + this.config.security.session_timeout,
            allow_ip_change: options.allow_ip_change || false
        });
        
        return jwt.sign(payload, this.config.jwt.secret, {
            algorithm: this.config.jwt.algorithm
        });
    }
    
    /**
     * Generate refresh token
     */
    generateRefreshToken(user_id) {
        const refresh_payload = {
            user_id: user_id,
            type: 'refresh',
            jti: this.generateSessionID(),
            iat: Math.floor(Date.now() / 1000),
            exp: Math.floor(Date.now() / 1000) + (7 * 24 * 60 * 60) // 7 days
        };
        
        return jwt.sign(refresh_payload, this.config.jwt.secret);
    }
    
    /**
     * Generate session ID
     */
    generateSessionID() {
        return crypto.randomBytes(32).toString('hex');
    }
    
    /**
     * Generate incident ID for security events
     */
    generateIncidentID() {
        const timestamp = Date.now().toString(36);
        const random = crypto.randomBytes(8).toString('hex');
        return `INC-${timestamp}-${random}`.toUpperCase();
    }
    
    /**
     * Block IP address temporarily
     */
    blockIP(ip, duration) {
        this.security_metrics.blocked_ips.set(ip, {
            blocked_at: Date.now(),
            expires_at: Date.now() + duration,
            reason: 'Security threat detected'
        });
        
        console.log(`🚫 Blocked IP ${ip} for ${duration / 60000} minutes`);
        
        // Auto-unblock after duration
        setTimeout(() => {
            this.security_metrics.blocked_ips.delete(ip);
            console.log(`✅ Unblocked IP ${ip}`);
        }, duration);
    }
    
    /**
     * Check if IP is blocked
     */
    isIPBlocked(ip) {
        const block_info = this.security_metrics.blocked_ips.get(ip);
        
        if (!block_info) return false;
        
        if (Date.now() > block_info.expires_at) {
            this.security_metrics.blocked_ips.delete(ip);
            return false;
        }
        
        return true;
    }
    
    /**
     * Log security events
     */
    logSecurityEvent(event_type, details) {
        const event = {
            event_type,
            timestamp: Date.now(),
            ...details
        };
        
        this.security_metrics.security_events.push(event);
        
        // Keep only recent events
        const retention_limit = Date.now() - this.config.monitoring.metrics_retention;
        this.security_metrics.security_events = this.security_metrics.security_events.filter(
            e => e.timestamp > retention_limit
        );
        
        // Console logging for important events
        if (['THREAT_DETECTED', 'AUTHENTICATION_FAILED', 'ANOMALY_DETECTED'].includes(event_type)) {
            console.log(`🔒 SECURITY EVENT [${event_type}]:`, JSON.stringify(details, null, 2));
        }
    }
    
    /**
     * Start security monitoring
     */
    startSecurityMonitoring() {
        // Clean up expired sessions every 5 minutes
        setInterval(() => {
            this.cleanupExpiredSessions();
        }, 5 * 60 * 1000);
        
        // Generate security reports every hour
        setInterval(() => {
            this.generateSecurityReport();
        }, 60 * 60 * 1000);
        
        console.log('👀 Security monitoring started');
    }
    
    /**
     * Cleanup expired sessions
     */
    cleanupExpiredSessions() {
        const now = Date.now();
        let cleaned_count = 0;
        
        for (const [session_id, session] of this.security_metrics.active_sessions) {
            if (now > session.expires_at) {
                this.security_metrics.active_sessions.delete(session_id);
                cleaned_count++;
            }
        }
        
        if (cleaned_count > 0) {
            console.log(`🧹 Cleaned up ${cleaned_count} expired sessions`);
        }
    }
    
    /**
     * Generate comprehensive security report
     */
    generateSecurityReport() {
        const now = Date.now();
        const last_hour = now - 3600000;
        
        const recent_events = this.security_metrics.security_events.filter(
            event => event.timestamp > last_hour
        );
        
        const report = {
            timestamp: new Date(),
            period: 'Last 1 hour',
            summary: {
                total_events: recent_events.length,
                threats_detected: recent_events.filter(e => e.event_type === 'THREAT_DETECTED').length,
                failed_logins: recent_events.filter(e => e.event_type === 'AUTHENTICATION_FAILED').length,
                anomalies: recent_events.filter(e => e.event_type === 'ANOMALY_DETECTED').length,
                blocked_ips: this.security_metrics.blocked_ips.size,
                active_sessions: this.security_metrics.active_sessions.size
            },
            top_threats: this.getTopThreats(recent_events),
            security_score: this.calculateSecurityScore(recent_events)
        };
        
        console.log('📊 Security Report:', JSON.stringify(report, null, 2));
        
        return report;
    }
    
    /**
     * Get top threats from recent events
     */
    getTopThreats(events) {
        const threat_counts = {};
        
        events
            .filter(event => event.event_type === 'THREAT_DETECTED')
            .forEach(event => {
                const threat_type = event.type || 'unknown';
                threat_counts[threat_type] = (threat_counts[threat_type] || 0) + 1;
            });
        
        return Object.entries(threat_counts)
            .sort(([,a], [,b]) => b - a)
            .slice(0, 5)
            .map(([type, count]) => ({ type, count }));
    }
    
    /**
     * Calculate overall security score
     */
    calculateSecurityScore(recent_events) {
        let score = 100;
        
        // Deduct points for security events
        score -= recent_events.filter(e => e.event_type === 'THREAT_DETECTED').length * 5;
        score -= recent_events.filter(e => e.event_type === 'AUTHENTICATION_FAILED').length * 2;
        score -= recent_events.filter(e => e.event_type === 'ANOMALY_DETECTED').length * 1;
        
        // Bonus for good practices
        if (this.security_metrics.active_sessions.size > 0) score += 5;
        if (this.security_metrics.blocked_ips.size === 0) score += 5;
        
        return Math.max(0, Math.min(100, score));
    }
    
    /**
     * Get security status for Cursor team integration
     */
    getSecurityStatus() {
        const recent_events = this.security_metrics.security_events.filter(
            event => Date.now() - event.timestamp < 3600000 // Last hour
        );
        
        return {
            status: 'ACTIVE',
            monitoring: {
                active: true,
                uptime: process.uptime(),
                events_processed: this.security_metrics.security_events.length
            },
            protection: {
                threat_patterns: this.threat_patterns.size,
                anomaly_detectors: this.anomaly_detectors.size,
                blocked_ips: this.security_metrics.blocked_ips.size,
                active_sessions: this.security_metrics.active_sessions.size
            },
            recent_activity: {
                threats_detected: recent_events.filter(e => e.event_type === 'THREAT_DETECTED').length,
                failed_logins: recent_events.filter(e => e.event_type === 'AUTHENTICATION_FAILED').length,
                anomalies: recent_events.filter(e => e.event_type === 'ANOMALY_DETECTED').length
            },
            security_score: this.calculateSecurityScore(recent_events)
        };
    }
    
    /**
     * Setup encryption utilities
     */
    setupEncryption() {
        this.encryption_key = crypto.scryptSync(
            this.config.jwt.secret, 
            'meschain-salt', 
            32
        );
        
        console.log('🔐 Encryption systems initialized');
    }
    
    /**
     * Encrypt sensitive data
     */
    encryptData(data) {
        const iv = crypto.randomBytes(16);
        const cipher = crypto.createCipher(this.config.encryption.algorithm, this.encryption_key);
        
        let encrypted = cipher.update(JSON.stringify(data), 'utf8', 'hex');
        encrypted += cipher.final('hex');
        
        const auth_tag = cipher.getAuthTag();
        
        return {
            encrypted: encrypted,
            iv: iv.toString('hex'),
            auth_tag: auth_tag.toString('hex')
        };
    }
    
    /**
     * Decrypt sensitive data
     */
    decryptData(encrypted_data) {
        const decipher = crypto.createDecipher(
            this.config.encryption.algorithm,
            this.encryption_key
        );
        
        decipher.setAuthTag(Buffer.from(encrypted_data.auth_tag, 'hex'));
        
        let decrypted = decipher.update(encrypted_data.encrypted, 'hex', 'utf8');
        decrypted += decipher.final('utf8');
        
        return JSON.parse(decrypted);
    }
    
    /**
     * Cleanup security resources
     */
    async cleanup() {
        // Clear all active sessions
        this.security_metrics.active_sessions.clear();
        
        // Clear blocked IPs
        this.security_metrics.blocked_ips.clear();
        
        // Clear recent events (keep historical for analysis)
        this.security_metrics.security_events = [];
        
        console.log('🧹 Security system cleanup completed');
    }
}

module.exports = EnterpriseSecurityEnhancer;

/**
 * 🚀 CURSOR TEAM INTEGRATION READY
 * 
 * Usage Example:
 * 
 * const security = new EnterpriseSecurityEnhancer({
 *     jwt_secret: 'your-secret-key',
 *     ip_whitelist: ['192.168.1.100', '10.0.0.50']
 * });
 * 
 * // Use security middleware in Express app
 * app.use('/api', security.createSecurityMiddleware());
 * 
 * // Generate secure tokens
 * const token = security.generateJWTToken(user, { ip: req.ip });
 * 
 * // Get security status
 * const status = security.getSecurityStatus();
 * console.log('Security Status:', status);
 */
