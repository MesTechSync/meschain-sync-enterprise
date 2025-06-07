/**
 * MesChain-Sync Enterprise Authentication Middleware
 * Unified login system for all enterprise services
 * Version: 4.0 Enterprise
 * 
 * Supports:
 * - Multi-role authentication
 * - Session management 
 * - Security logging
 * - Real-time monitoring
 * - Cross-service authentication
 */

class MesChainAuth {
    constructor() {
        this.apiBaseUrl = '/admin/extension/module/meschain/api';
        this.sessionTimeout = 30 * 60 * 1000; // 30 minutes
        this.currentUser = null;
        this.sessionToken = null;
        this.loginAttempts = new Map();
        this.maxLoginAttempts = 5;
        this.lockoutDuration = 15 * 60 * 1000; // 15 minutes
        
        // User roles and permissions
        this.userRoles = {
            'super_admin': { 
                level: 100, 
                permissions: ['*'],
                icon: '👑',
                color: '#e74c3c',
                name: 'Süper Admin'
            },
            'admin': { 
                level: 80, 
                permissions: ['user_management', 'system_config', 'reports'],
                icon: '👨‍💼',
                color: '#3498db', 
                name: 'Admin'
            },
            'marketplace_manager': { 
                level: 60, 
                permissions: ['marketplace_management', 'product_sync', 'orders'],
                icon: '🛒',
                color: '#2ecc71',
                name: 'Pazaryeri Yöneticisi'
            },
            'technical': { 
                level: 60, 
                permissions: ['api_management', 'webhook_management', 'technical_support'],
                icon: '👨‍🔧',
                color: '#f39c12',
                name: 'Teknik Personel'
            },
            'dropshipper': { 
                level: 40, 
                permissions: ['dropshipping', 'limited_marketplace'],
                icon: '📦',
                color: '#2ecc71',
                name: 'Dropshipper'
            },
            'viewer': { 
                level: 20, 
                permissions: ['view_dashboard', 'view_reports'],
                icon: '👁️',
                color: '#95a5a6',
                name: 'Görüntüleyici'
            }
        };

        this.initializeAuth();
    }

    /**
     * Initialize authentication system
     */
    async initializeAuth() {
        console.log('🔐 Initializing MesChain Authentication System...');
        
        // Check for existing session
        await this.checkExistingSession();
        
        // Setup session monitoring
        this.setupSessionMonitoring();
        
        // Setup security event listeners
        this.setupSecurityListeners();
        
        console.log('✅ Authentication system initialized');
    }

    /**
     * Check for existing session
     */
    async checkExistingSession() {
        const token = localStorage.getItem('meschain_session_token');
        const userData = localStorage.getItem('meschain_user_data');
        
        if (token && userData) {
            try {
                this.sessionToken = token;
                this.currentUser = JSON.parse(userData);
                
                // Validate session with backend
                const isValid = await this.validateSession(token);
                if (isValid) {
                    this.onLoginSuccess(this.currentUser);
                    return true;
                } else {
                    this.clearSession();
                }
            } catch (error) {
                console.warn('Session validation error:', error);
                this.clearSession();
            }
        }
        return false;
    }

    /**
     * User login
     */
    async login(credentials) {
        const { username, password, rememberMe = false } = credentials;
        
        try {
            // Check login attempts
            if (this.isAccountLocked(username)) {
                throw new Error('Hesap geçici olarak kilitlenmiştir. Lütfen 15 dakika sonra tekrar deneyin.');
            }

            // Show loading state
            this.showLoadingState(true);

            // API authentication attempt
            const response = await fetch(`${this.apiBaseUrl}/auth/login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    username,
                    password,
                    remember_me: rememberMe,
                    timestamp: Date.now(),
                    user_agent: navigator.userAgent,
                    ip_address: await this.getClientIP()
                })
            });

            if (response.ok) {
                const result = await response.json();
                
                if (result.success) {
                    // Reset login attempts
                    this.loginAttempts.delete(username);
                    
                    // Store session data
                    this.sessionToken = result.token;
                    this.currentUser = result.user;
                    
                    // Store in localStorage if remember me
                    if (rememberMe) {
                        localStorage.setItem('meschain_session_token', this.sessionToken);
                        localStorage.setItem('meschain_user_data', JSON.stringify(this.currentUser));
                    } else {
                        sessionStorage.setItem('meschain_session_token', this.sessionToken);
                        sessionStorage.setItem('meschain_user_data', JSON.stringify(this.currentUser));
                    }
                    
                    // Success callback
                    this.onLoginSuccess(this.currentUser);
                    
                    // Log successful login
                    this.logSecurityEvent('LOGIN_SUCCESS', {
                        user_id: this.currentUser.user_id,
                        username: username,
                        timestamp: new Date().toISOString()
                    });
                    
                    return { success: true, user: this.currentUser };
                } else {
                    throw new Error(result.message || 'Giriş başarısız');
                }
            } else {
                throw new Error('Sunucu bağlantı hatası');
            }

        } catch (error) {
            // Handle login failure
            this.handleLoginFailure(username, error.message);
            
            // Try demo mode if API is offline
            if (error.message.includes('sunucu') || error.message.includes('bağlantı')) {
                return await this.tryDemoLogin(credentials);
            }
            
            throw error;
        } finally {
            this.showLoadingState(false);
        }
    }

    /**
     * Demo mode login (when API is offline)
     */
    async tryDemoLogin(credentials) {
        const { username, password } = credentials;
        
        // Demo user accounts
        const demoUsers = {
            'super_admin': {
                user_id: 1,
                username: 'super_admin',
                email: 'admin@meschain.com',
                firstname: 'Super',
                lastname: 'Admin',
                role: 'super_admin',
                status: 'active',
                demo_mode: true
            },
            'admin': {
                user_id: 2,
                username: 'admin',
                email: 'admin@meschain.com',
                firstname: 'System',
                lastname: 'Admin',
                role: 'admin',
                status: 'active',
                demo_mode: true
            },
            'marketplace_manager': {
                user_id: 3,
                username: 'marketplace_manager',
                email: 'manager@meschain.com',
                firstname: 'Marketplace',
                lastname: 'Manager',
                role: 'marketplace_manager',
                status: 'active',
                demo_mode: true
            }
        };

        if (demoUsers[username] && password === 'demo123') {
            this.currentUser = demoUsers[username];
            this.sessionToken = 'demo_token_' + Date.now();
            
            // Store demo session
            sessionStorage.setItem('meschain_session_token', this.sessionToken);
            sessionStorage.setItem('meschain_user_data', JSON.stringify(this.currentUser));
            
            this.onLoginSuccess(this.currentUser);
            
            this.showNotification('Demo modunda giriş yapıldı', 'warning');
            
            return { success: true, user: this.currentUser, demo: true };
        }
        
        throw new Error('Geçersiz kullanıcı adı veya şifre');
    }

    /**
     * User logout
     */
    async logout() {
        try {
            // Notify backend
            if (this.sessionToken && !this.currentUser?.demo_mode) {
                await fetch(`${this.apiBaseUrl}/auth/logout`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${this.sessionToken}`
                    },
                    body: JSON.stringify({
                        timestamp: Date.now()
                    })
                });
            }

            // Log logout event
            this.logSecurityEvent('LOGOUT', {
                user_id: this.currentUser?.user_id,
                username: this.currentUser?.username,
                timestamp: new Date().toISOString()
            });

            // Clear session
            this.clearSession();
            
            // Success callback
            this.onLogoutSuccess();
            
            return { success: true };

        } catch (error) {
            console.warn('Logout API error:', error);
            // Clear session anyway
            this.clearSession();
            this.onLogoutSuccess();
            return { success: true };
        }
    }

    /**
     * Check if user has specific permission
     */
    hasPermission(permission) {
        if (!this.currentUser) return false;
        
        const roleData = this.userRoles[this.currentUser.role];
        if (!roleData) return false;
        
        // Super admin has all permissions
        if (roleData.permissions.includes('*')) return true;
        
        return roleData.permissions.includes(permission);
    }

    /**
     * Check if user can access specific service
     */
    canAccessService(servicePort) {
        if (!this.currentUser) return false;
        
        const userLevel = this.userRoles[this.currentUser.role]?.level || 0;
        
        // Service access levels
        const serviceAccessLevels = {
            3000: 20, // Main Dashboard - Viewer and above
            3001: 60, // Components Hub - Technical and above  
            3002: 100, // Super Admin Panel - Super Admin only
            3003: 40, // Marketplace Hub - Dropshipper and above
            3009: 60, // Cross-Platform Admin - Technical and above
            3010: 40  // Marketplace Specialist - Dropshipper and above
        };
        
        const requiredLevel = serviceAccessLevels[servicePort] || 100;
        return userLevel >= requiredLevel;
    }

    /**
     * Handle login failure
     */
    handleLoginFailure(username, errorMessage) {
        // Track login attempts
        const attempts = this.loginAttempts.get(username) || 0;
        this.loginAttempts.set(username, attempts + 1);
        
        // Log security event
        this.logSecurityEvent('LOGIN_FAILURE', {
            username: username,
            attempt_count: attempts + 1,
            error: errorMessage,
            timestamp: new Date().toISOString(),
            ip_address: 'client_ip' // Would be filled by backend
        });

        // Check if account should be locked
        if (attempts + 1 >= this.maxLoginAttempts) {
            this.lockAccount(username);
        }
    }

    /**
     * Check if account is locked
     */
    isAccountLocked(username) {
        const attempts = this.loginAttempts.get(username) || 0;
        return attempts >= this.maxLoginAttempts;
    }

    /**
     * Lock account temporarily
     */
    lockAccount(username) {
        setTimeout(() => {
            this.loginAttempts.delete(username);
        }, this.lockoutDuration);
        
        this.logSecurityEvent('ACCOUNT_LOCKED', {
            username: username,
            lockout_duration: this.lockoutDuration,
            timestamp: new Date().toISOString()
        });
    }

    /**
     * Validate current session
     */
    async validateSession(token) {
        try {
            const response = await fetch(`${this.apiBaseUrl}/auth/validate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                }
            });
            
            if (response.ok) {
                const result = await response.json();
                return result.valid;
            }
            return false;
        } catch (error) {
            console.warn('Session validation failed:', error);
            return false;
        }
    }

    /**
     * Setup session monitoring
     */
    setupSessionMonitoring() {
        // Auto-logout on session timeout
        if (this.sessionToken && !this.currentUser?.demo_mode) {
            setTimeout(() => {
                this.showNotification('Oturum süresi doldu. Tekrar giriş yapın.', 'warning');
                this.logout();
            }, this.sessionTimeout);
        }

        // Monitor user activity
        let lastActivity = Date.now();
        
        ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
            document.addEventListener(event, () => {
                lastActivity = Date.now();
            }, { passive: true });
        });

        // Check for inactivity every minute
        setInterval(() => {
            if (this.currentUser && Date.now() - lastActivity > this.sessionTimeout) {
                this.showNotification('Uzun süre inaktif olduğunuz için çıkış yapılıyor.', 'info');
                this.logout();
            }
        }, 60000);
    }

    /**
     * Setup security event listeners
     */
    setupSecurityListeners() {
        // Detect multiple tab activity
        window.addEventListener('storage', (e) => {
            if (e.key === 'meschain_session_token' && !e.newValue && this.currentUser) {
                this.showNotification('Başka bir sekmede çıkış yapıldı.', 'info');
                this.clearSession();
                this.onLogoutSuccess();
            }
        });

        // Detect tab close/refresh
        window.addEventListener('beforeunload', () => {
            if (this.currentUser) {
                this.logSecurityEvent('SESSION_END', {
                    user_id: this.currentUser.user_id,
                    timestamp: new Date().toISOString()
                });
            }
        });
    }

    /**
     * Clear session data
     */
    clearSession() {
        this.currentUser = null;
        this.sessionToken = null;
        localStorage.removeItem('meschain_session_token');
        localStorage.removeItem('meschain_user_data');
        sessionStorage.removeItem('meschain_session_token');
        sessionStorage.removeItem('meschain_user_data');
    }

    /**
     * Get client IP address
     */
    async getClientIP() {
        try {
            const response = await fetch('https://api.ipify.org?format=json');
            const data = await response.json();
            return data.ip;
        } catch (error) {
            return 'unknown';
        }
    }

    /**
     * Log security events
     */
    logSecurityEvent(eventType, data) {
        const logEntry = {
            event_type: eventType,
            timestamp: new Date().toISOString(),
            user_agent: navigator.userAgent,
            ...data
        };

        // Store in session storage for later sync
        const logs = JSON.parse(sessionStorage.getItem('meschain_security_logs') || '[]');
        logs.push(logEntry);
        sessionStorage.setItem('meschain_security_logs', JSON.stringify(logs.slice(-100))); // Keep last 100 events

        console.log('🔒 Security Event:', logEntry);
    }

    /**
     * Show loading state
     */
    showLoadingState(show) {
        // Implementation depends on UI framework
        const event = new CustomEvent('meschain-auth-loading', { detail: { loading: show } });
        document.dispatchEvent(event);
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        // Implementation depends on UI framework
        const event = new CustomEvent('meschain-auth-notification', { 
            detail: { message, type } 
        });
        document.dispatchEvent(event);
    }

    /**
     * Success callbacks - to be overridden
     */
    onLoginSuccess(user) {
        const event = new CustomEvent('meschain-auth-login-success', { detail: { user } });
        document.dispatchEvent(event);
    }

    onLogoutSuccess() {
        const event = new CustomEvent('meschain-auth-logout-success');
        document.dispatchEvent(event);
    }

    /**
     * Get current user info
     */
    getCurrentUser() {
        return this.currentUser;
    }

    /**
     * Get current session token
     */
    getSessionToken() {
        return this.sessionToken;
    }

    /**
     * Check if user is logged in
     */
    isLoggedIn() {
        return !!this.currentUser && !!this.sessionToken;
    }

    /**
     * Get user role info
     */
    getUserRoleInfo() {
        if (!this.currentUser) return null;
        return this.userRoles[this.currentUser.role];
    }
}

// Initialize global authentication system
window.MesChainAuth = MesChainAuth;

// Create global instance
window.mesChainAuth = new MesChainAuth();

console.log('🔐 MesChain Authentication System loaded');
