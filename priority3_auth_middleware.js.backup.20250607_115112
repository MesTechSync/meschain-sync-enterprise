// MesChain Enterprise Priority 3 - Authentication Integration
// This file contains the authentication integration logic for all Priority 3 services
// Version: 1.0.0 - June 6, 2025

const path = require('path');
const fs = require('fs');

/**
 * MesChain Authentication Middleware for Priority 3 Services
 * Integrates with existing MesChain Auth System
 */
class Priority3AuthMiddleware {
    constructor(config) {
        // Support both object config and individual parameters for backward compatibility
        if (typeof config === 'string') {
            // Old format: new Priority3AuthMiddleware(serviceName, port)
            this.serviceName = arguments[0];
            this.port = arguments[1];
            this.serviceType = 'generic';
            this.requiredRoles = ['admin'];
            this.permissions = { 'admin': ['*'] };
        } else {
            // New format: new Priority3AuthMiddleware({ serviceName, port, ... })
            this.serviceName = config.serviceName;
            this.port = config.port;
            this.serviceType = config.serviceType || 'generic';
            this.requiredRoles = config.requiredRoles || ['admin'];
            this.permissions = config.permissions || { 'admin': ['*'] };
        }
        
        this.authSystemPath = path.join(__dirname, 'CursorDev', 'AUTH_SYSTEM', 'meschain_auth.js');
        this.isAuthSystemAvailable = fs.existsSync(this.authSystemPath);
        
        console.log(`🔐 Initializing MesChain Auth for ${this.serviceName} (Port ${this.port})`);
        if (this.isAuthSystemAvailable) {
            console.log(`✅ MesChain Auth System detected`);
        } else {
            console.log(`⚠️ MesChain Auth System not found, using fallback`);
        }
    }

    /**
     * Main authentication middleware (alias for authenticate)
     */
    requireAuth() {
        return this.authenticate();
    }

    /**
     * Main authentication middleware
     */
    authenticate() {
        return (req, res, next) => {
            // Check for session token in various places
            const sessionToken = this.extractSessionToken(req);
            
            if (!sessionToken) {
                return this.handleUnauthenticated(req, res);
            }
            
            // Validate session (in production, this would call the actual auth system)
            if (this.validateSession(sessionToken)) {
                req.user = this.getUserFromToken(sessionToken);
                req.user.service = this.serviceName;
                req.user.port = this.port;
                next();
            } else {
                return this.handleInvalidSession(req, res);
            }
        };
    }

    /**
     * Extract session token from request
     */
    extractSessionToken(req) {
        // Try multiple sources for the token
        let token = req.headers['x-session-token'] || 
               req.cookies?.meschain_session_token ||
               req.query.token ||
               req.headers.authorization?.replace('Bearer ', '');
               
        // If cookies didn't work, try parsing raw cookie header manually
        if (!token && req.headers.cookie) {
            const cookieHeader = req.headers.cookie;
            const match = cookieHeader.match(/meschain_session_token=([^;]+)/);
            if (match) {
                token = decodeURIComponent(match[1]);
            }
        }
               
        return token;
    }

    /**
     * Handle unauthenticated requests
     */
    handleUnauthenticated(req, res) {
        if (this.isHTMLRequest(req)) {
            return res.redirect(`/login?redirect=${encodeURIComponent(req.originalUrl)}&service=${this.serviceName}`);
        }
        
        return res.status(401).json({
            success: false,
            error: 'Authentication required',
            loginUrl: `/login?service=${this.serviceName}`,
            service: this.serviceName,
            port: this.port
        });
    }

    /**
     * Handle invalid session
     */
    handleInvalidSession(req, res) {
        if (this.isHTMLRequest(req)) {
            return res.redirect(`/login?expired=true&service=${this.serviceName}`);
        }
        
        return res.status(401).json({
            success: false,
            error: 'Session expired',
            loginUrl: `/login?service=${this.serviceName}`,
            service: this.serviceName
        });
    }

    /**
     * Check if request expects HTML response
     */
    isHTMLRequest(req) {
        return req.headers.accept && req.headers.accept.includes('text/html');
    }

    /**
     * Validate session token (demo implementation)
     */
    validateSession(token) {
        try {
            // Try to decode the token
            const tokenData = JSON.parse(Buffer.from(token, 'base64').toString());
            
            // Check if token is not too old (24 hours)
            const tokenAge = Date.now() - tokenData.timestamp;
            const maxAge = 24 * 60 * 60 * 1000; // 24 hours
            
            return tokenAge < maxAge;
        } catch (error) {
            // If token can't be decoded, it's invalid
            return false;
        }
    }

    /**
     * Get user data from session token
     */
    getUserFromToken(token) {
        try {
            // Decode the token to get user data
            const tokenData = JSON.parse(Buffer.from(token, 'base64').toString());
            return tokenData.user;
        } catch (error) {
            // Fallback to demo user data
            return {
                id: 'demo_user_' + this.port,
                username: 'demo_user',
                role: this.getDefaultRoleForService(),
                permissions: this.getPermissionsForService(),
                loginTime: new Date().toISOString(),
                sessionToken: token
            };
        }
    }

    /**
     * Get default role based on service
     */
    getDefaultRoleForService() {
        const serviceRoles = {
            'Performance Dashboard': 'performance_analyst',
            'Product Management': 'product_manager',
            'Order Management': 'order_manager',
            'Inventory Management': 'inventory_manager',
            'Amazon Seller Central': 'marketplace_manager',
            'Trendyol Seller Hub': 'marketplace_manager',
            'GittiGidiyor Manager': 'marketplace_manager',
            'N11 Management Console': 'marketplace_manager',
            'eBay Integration Hub': 'marketplace_manager',
            'Trendyol Advanced Testing': 'qa_engineer'
        };
        
        return serviceRoles[this.serviceName] || 'user';
    }

    /**
     * Get permissions based on service
     */
    getPermissionsForService() {
        const servicePermissions = {
            'Performance Dashboard': ['view_performance', 'view_analytics', 'export_reports'],
            'Product Management': ['manage_products', 'bulk_operations', 'sync_products'],
            'Order Management': ['manage_orders', 'process_orders', 'view_customer_data'],
            'Inventory Management': ['manage_inventory', 'stock_operations', 'warehouse_management'],
            'Amazon Seller Central': ['amazon_api', 'amazon_orders', 'amazon_inventory'],
            'Trendyol Seller Hub': ['trendyol_api', 'trendyol_orders', 'trendyol_inventory'],
            'GittiGidiyor Manager': ['gittigidiyor_api', 'gittigidiyor_orders'],
            'N11 Management Console': ['n11_api', 'n11_orders', 'n11_inventory'],
            'eBay Integration Hub': ['ebay_api', 'ebay_global', 'ebay_shipping'],
            'Trendyol Advanced Testing': ['run_tests', 'view_results', 'test_automation']
        };
        
        return servicePermissions[this.serviceName] || ['basic_access'];
    }

    /**
     * Generate login page for the service
     */
    generateLoginPage() {
        return (req, res) => {
            const redirectUrl = req.query.redirect || '/';
            const expired = req.query.expired === 'true';
            const serviceName = req.query.service || this.serviceName;
            
            res.send(`
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>MesChain Enterprise Login - ${serviceName}</title>
                    <style>
                        * {
                            margin: 0;
                            padding: 0;
                            box-sizing: border-box;
                        }
                        body {
                            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                            min-height: 100vh;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            position: relative;
                        }
                        .login-container {
                            background: rgba(255, 255, 255, 0.95);
                            padding: 40px;
                            border-radius: 20px;
                            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
                            backdrop-filter: blur(10px);
                            max-width: 450px;
                            width: 100%;
                            margin: 20px;
                        }
                        .header {
                            text-align: center;
                            margin-bottom: 30px;
                        }
                        .logo {
                            font-size: 3em;
                            margin-bottom: 10px;
                        }
                        .title {
                            color: #667eea;
                            font-size: 2.2em;
                            margin-bottom: 5px;
                            font-weight: bold;
                        }
                        .subtitle {
                            color: #666;
                            font-size: 1em;
                            margin-bottom: 10px;
                        }
                        .service-badge {
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                            color: white;
                            padding: 8px 20px;
                            border-radius: 25px;
                            font-size: 0.9em;
                            font-weight: bold;
                            display: inline-block;
                        }
                        .form-group {
                            margin-bottom: 25px;
                        }
                        .form-group label {
                            display: block;
                            margin-bottom: 8px;
                            color: #333;
                            font-weight: 600;
                            font-size: 1.1em;
                        }
                        .form-group input {
                            width: 100%;
                            padding: 15px;
                            border: 2px solid #e0e0e0;
                            border-radius: 10px;
                            font-size: 16px;
                            transition: all 0.3s ease;
                            background: rgba(255, 255, 255, 0.9);
                        }
                        .form-group input:focus {
                            outline: none;
                            border-color: #667eea;
                            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
                        }
                        .login-btn {
                            width: 100%;
                            padding: 15px;
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                            color: white;
                            border: none;
                            border-radius: 10px;
                            font-size: 16px;
                            font-weight: bold;
                            cursor: pointer;
                            transition: all 0.3s ease;
                            text-transform: uppercase;
                            letter-spacing: 1px;
                        }
                        .login-btn:hover {
                            transform: translateY(-2px);
                            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
                        }
                        .login-btn:active {
                            transform: translateY(0);
                        }
                        .service-info {
                            text-align: center;
                            margin-top: 25px;
                            padding-top: 25px;
                            border-top: 1px solid #e0e0e0;
                            color: #666;
                            font-size: 0.9em;
                        }
                        .demo-note {
                            background: rgba(255, 193, 7, 0.1);
                            border: 1px solid rgba(255, 193, 7, 0.3);
                            color: #856404;
                            padding: 15px;
                            border-radius: 10px;
                            margin-bottom: 20px;
                            font-size: 0.9em;
                        }
                        .alert {
                            padding: 15px;
                            border-radius: 10px;
                            margin-bottom: 20px;
                            font-size: 0.9em;
                        }
                        .alert-warning {
                            background: rgba(255, 193, 7, 0.1);
                            border: 1px solid rgba(255, 193, 7, 0.3);
                            color: #856404;
                        }
                        .port-indicator {
                            position: absolute;
                            top: 20px;
                            right: 20px;
                            background: rgba(255, 255, 255, 0.9);
                            padding: 10px 15px;
                            border-radius: 10px;
                            font-size: 0.8em;
                            color: #666;
                            font-weight: bold;
                        }
                    </style>
                </head>
                <body>
                    <div class="port-indicator">Port ${this.port}</div>
                    
                    <div class="login-container">
                        <div class="header">
                            <div class="logo">🔐</div>
                            <h1 class="title">MesChain</h1>
                            <p class="subtitle">Enterprise Authentication</p>
                            <div class="service-badge">${serviceName}</div>
                        </div>

                        ${expired ? '<div class="alert alert-warning">⚠️ Your session has expired. Please login again.</div>' : ''}
                        
                        <div class="demo-note">
                            💡 <strong>Demo Mode:</strong> Enter any username and password to continue.
                            In production, this will use the full MesChain authentication system.
                        </div>

                        <form id="loginForm">
                            <div class="form-group">
                                <label for="username">👤 Username:</label>
                                <input type="text" id="username" name="username" required placeholder="Enter your username">
                            </div>
                            <div class="form-group">
                                <label for="password">🔒 Password:</label>
                                <input type="password" id="password" name="password" required placeholder="Enter your password">
                            </div>
                            <button type="submit" class="login-btn">🚀 Login to ${serviceName}</button>
                        </form>

                        <div class="service-info">
                            <p><strong>Service:</strong> ${serviceName}</p>
                            <p><strong>Port:</strong> ${this.port}</p>
                            <p><strong>System:</strong> MesChain Enterprise v4.0</p>
                            <p><strong>Authentication:</strong> Priority 3 Integration</p>
                        </div>
                    </div>
                    
                    <script>
                        document.getElementById('loginForm').addEventListener('submit', function(e) {
                            e.preventDefault();
                            
                            const username = document.getElementById('username').value;
                            const password = document.getElementById('password').value;
                            
                            if (username && password) {
                                // Generate demo session token
                                const sessionToken = 'meschain_demo_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                                
                                // Set session cookie
                                document.cookie = 'meschain_session_token=' + sessionToken + '; path=/; max-age=3600';
                                
                                // Store user data
                                localStorage.setItem('meschain_user', JSON.stringify({
                                    username: username,
                                    service: '${serviceName}',
                                    port: ${this.port},
                                    loginTime: new Date().toISOString()
                                }));
                                
                                // Show success message briefly
                                const btn = document.querySelector('.login-btn');
                                btn.textContent = '✅ Login Successful!';
                                btn.style.background = 'linear-gradient(135deg, #28a745 0%, #20c997 100%)';
                                
                                // Redirect after brief delay
                                setTimeout(() => {
                                    window.location.href = '${redirectUrl}';
                                }, 1000);
                            } else {
                                alert('Please enter both username and password');
                            }
                        });

                        // Auto-focus username field
                        document.getElementById('username').focus();
                    </script>
                </body>
                </html>
            `);
        };
    }

    /**
     * Generate logout handler
     */
    generateLogoutHandler() {
        return (req, res) => {
            // Clear session cookie
            res.clearCookie('meschain_session_token');
            
            // Clear any other auth-related cookies
            res.clearCookie('meschain_user_data');
            
            // Redirect to login with logout message
            res.redirect(`/login?logout=true&service=${this.serviceName}`);
        };
    }

    /**
     * Create role-based access control middleware
     */
    requireRole(requiredRole) {
        return (req, res, next) => {
            if (!req.user) {
                return res.status(401).json({
                    success: false,
                    error: 'Authentication required',
                    service: this.serviceName
                });
            }

            const roleHierarchy = {
                'super_admin': 100,
                'admin': 80,
                'marketplace_manager': 60,
                'product_manager': 60,
                'order_manager': 60,
                'inventory_manager': 60,
                'performance_analyst': 50,
                'qa_engineer': 50,
                'user': 20
            };

            const userLevel = roleHierarchy[req.user.role] || 0;
            const requiredLevel = roleHierarchy[requiredRole] || 100;

            if (userLevel >= requiredLevel) {
                next();
            } else {
                return res.status(403).json({
                    success: false,
                    error: 'Insufficient permissions',
                    requiredRole: requiredRole,
                    userRole: req.user.role,
                    service: this.serviceName
                });
            }
        };
    }

    /**
     * Create permission-based access control middleware
     */
    requirePermission(permission) {
        return (req, res, next) => {
            if (!req.user) {
                return res.status(401).json({
                    success: false,
                    error: 'Authentication required',
                    service: this.serviceName
                });
            }

            if (req.user.permissions.includes('*') || req.user.permissions.includes(permission)) {
                next();
            } else {
                return res.status(403).json({
                    success: false,
                    error: 'Permission denied',
                    requiredPermission: permission,
                    userPermissions: req.user.permissions,
                    service: this.serviceName
                });
            }
        };
    }

    /**
     * Generate and serve login page
     */
    getLoginPage() {
        return (req, res) => {
            const loginHtml = `
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Login - ${this.serviceName}</title>
                    <style>
                        * { margin: 0; padding: 0; box-sizing: border-box; }
                        body {
                            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                            min-height: 100vh;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }
                        .login-container {
                            background: white;
                            padding: 2rem;
                            border-radius: 10px;
                            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
                            width: 100%;
                            max-width: 400px;
                        }
                        .login-header {
                            text-align: center;
                            margin-bottom: 2rem;
                        }
                        .login-header h1 {
                            color: #333;
                            font-size: 1.8rem;
                            margin-bottom: 0.5rem;
                        }
                        .login-header p {
                            color: #666;
                            font-size: 0.9rem;
                        }
                        .form-group {
                            margin-bottom: 1rem;
                        }
                        .form-group label {
                            display: block;
                            margin-bottom: 0.5rem;
                            color: #333;
                            font-weight: 500;
                        }
                        .form-group input {
                            width: 100%;
                            padding: 0.75rem;
                            border: 2px solid #ddd;
                            border-radius: 5px;
                            font-size: 1rem;
                            transition: border-color 0.3s;
                        }
                        .form-group input:focus {
                            outline: none;
                            border-color: #667eea;
                        }
                        .login-btn {
                            width: 100%;
                            padding: 0.75rem;
                            background: linear-gradient(45deg, #667eea, #764ba2);
                            color: white;
                            border: none;
                            border-radius: 5px;
                            font-size: 1rem;
                            cursor: pointer;
                            transition: transform 0.2s;
                        }
                        .login-btn:hover {
                            transform: translateY(-2px);
                        }
                        .demo-notice {
                            text-align: center;
                            margin-top: 1rem;
                            padding: 1rem;
                            background: #f8f9fa;
                            border-radius: 5px;
                            border-left: 4px solid #28a745;
                        }
                        .demo-notice h3 {
                            color: #28a745;
                            margin-bottom: 0.5rem;
                        }
                        .demo-credentials {
                            font-size: 0.85rem;
                            color: #666;
                        }
                        .service-info {
                            text-align: center;
                            margin-bottom: 1rem;
                            padding: 0.5rem;
                            background: #e9ecef;
                            border-radius: 5px;
                        }
                        .error-message {
                            color: #dc3545;
                            text-align: center;
                            margin-bottom: 1rem;
                            padding: 0.5rem;
                            background: #f8d7da;
                            border-radius: 5px;
                        }
                    </style>
                </head>
                <body>
                    <div class="login-container">
                        <div class="login-header">
                            <h1>MesChain Enterprise</h1>
                            <p>Access Control System</p>
                        </div>
                        
                        <div class="service-info">
                            <strong>${this.serviceName}</strong><br>
                            <small>Port: ${this.port} | Type: ${this.serviceType}</small>
                        </div>

                        ${req.query.error ? `<div class="error-message">${req.query.error}</div>` : ''}

                        <form method="POST" action="/login">
                            <div class="form-group">
                                <label for="username">Username:</label>
                                <input type="text" id="username" name="username" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="password">Password:</label>
                                <input type="password" id="password" name="password" required>
                            </div>
                            
                            <button type="submit" class="login-btn">Sign In</button>
                        </form>

                        <div class="demo-notice">
                            <h3>🔧 Demo Mode Active</h3>
                            <div class="demo-credentials">
                                <strong>Admin:</strong> admin / admin123<br>
                                <strong>Manager:</strong> manager / manager123<br>
                                <strong>User:</strong> user / user123
                            </div>
                        </div>
                    </div>
                </body>
                </html>
            `;
            
            res.send(loginHtml);
        };
    }

    /**
     * Handle login form submission
     */
    handleLogin() {
        return (req, res) => {
            const { username, password } = req.body;
            
            // Demo authentication logic (replace with actual auth system)
            const user = this.authenticateUser(username, password);
            
            if (user) {
                // Create session token
                const sessionToken = this.createSessionToken(user);
                
                // Set session cookie
                res.cookie('meschain_session_token', sessionToken, {
                    httpOnly: true,
                    secure: process.env.NODE_ENV === 'production',
                    maxAge: 24 * 60 * 60 * 1000 // 24 hours
                });
                
                // Redirect to main dashboard
                res.redirect('/');
            } else {
                // Redirect back to login with error
                res.redirect('/login?error=Invalid credentials');
            }
        };
    }

    /**
     * Handle logout
     */
    handleLogout() {
        return (req, res) => {
            // Clear session cookie
            res.clearCookie('meschain_session_token');
            
            // Send logout response
            res.json({
                success: true,
                message: 'Logged out successfully',
                service: this.serviceName,
                redirectUrl: '/login'
            });
        };
    }

    /**
     * Demo user authentication (replace with actual auth system)
     */
    authenticateUser(username, password) {
        const demoUsers = {
            'admin': {
                password: 'admin123',
                role: 'super_admin',
                permissions: ['*'],
                name: 'System Administrator'
            },
            'manager': {
                password: 'manager123',
                role: 'admin',
                permissions: this.getPermissionsForRole('admin'),
                name: 'Service Manager'
            },
            'user': {
                password: 'user123',
                role: 'user',
                permissions: this.getPermissionsForRole('user'),
                name: 'Standard User'
            }
        };
        
        const user = demoUsers[username];
        if (user && user.password === password) {
            return {
                id: Date.now(),
                username: username,
                name: user.name,
                role: user.role,
                permissions: user.permissions,
                service: this.serviceName,
                loginTime: new Date().toISOString()
            };
        }
        
        return null;
    }

    /**
     * Get permissions for a role based on service configuration
     */
    getPermissionsForRole(role) {
        return this.permissions[role] || ['view'];
    }

    /**
     * Create session token (in production, use proper JWT or session management)
     */
    createSessionToken(user) {
        const tokenData = {
            user: user,
            service: this.serviceName,
            port: this.port,
            timestamp: Date.now()
        };
        
        // Simple base64 encoding for demo (use proper encryption in production)
        return Buffer.from(JSON.stringify(tokenData)).toString('base64');
    }
}

module.exports = Priority3AuthMiddleware;
