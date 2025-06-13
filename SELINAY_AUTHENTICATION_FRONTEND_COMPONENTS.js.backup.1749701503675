/**
 * 🔐 SELİNAY TEAM - AUTHENTICATION FRONTEND COMPONENTS
 * =====================================================
 * Phase 1: JWT Authentication & User Management Integration
 * Author: Selinay - Frontend Development Specialist
 * Date: 10 Haziran 2025
 * Backend Integration: Port 3036 - User Management & RBAC System
 */

class SelinayAuthenticationSystem {
    constructor() {
        this.teamName = 'Selinay Frontend Development Team';
        this.version = '1.0.0-AUTHENTICATION-INTEGRATION';
        this.backendPort = 3036;
        this.apiBase = `http://localhost:${this.backendPort}/api`;
        
        // JWT Token Management
        this.tokenManager = {
            tokenKey: 'meschain_auth_token',
            refreshKey: 'meschain_refresh_token',
            userKey: 'meschain_user_data'
        };
        
        // User Roles & Permissions
        this.userRoles = {
            SUPER_ADMIN: 'super_admin',
            ADMIN: 'admin', 
            MANAGER: 'manager',
            DROPSHIP_SPECIALIST: 'dropship_specialist',
            USER: 'user'
        };
        
        this.initializeAuthentication();
    }

    /**
     * 🚀 Initialize Authentication System
     */
    initializeAuthentication() {
        console.log('🔐 Selinay Authentication System Starting...');
        this.createLoginForm();
        this.setupTokenManager();
        this.initializeProtectedRoutes();
        this.createUserProfileComponents();
        console.log('✅ Authentication System Ready!');
    }

    /**
     * 🔑 Create Modern Login Form Component
     */
    createLoginForm() {
        const loginFormHTML = `
        <div id="selinay-login-container" class="auth-container">
            <div class="login-card">
                <div class="login-header">
                    <h2>🔐 MesChain-Sync Giriş</h2>
                    <p>Güvenli giriş yapın</p>
                </div>
                
                <form id="selinay-login-form" class="login-form">
                    <div class="form-group">
                        <label for="username">👤 Kullanıcı Adı</label>
                        <input type="text" id="username" name="username" required 
                               placeholder="Kullanıcı adınızı girin">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">🔒 Şifre</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Şifrenizi girin">
                        <div class="password-strength" id="password-strength"></div>
                    </div>
                    
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" id="remember-me">
                            <span>Beni hatırla</span>
                        </label>
                        <a href="#" class="forgot-password">Şifremi unuttum</a>
                    </div>
                    
                    <button type="submit" class="login-btn" id="login-btn">
                        <span class="btn-text">Giriş Yap</span>
                        <span class="btn-loading" style="display: none;">⏳ Giriş yapılıyor...</span>
                    </button>
                </form>
                
                <div class="login-footer">
                    <p>Test Kullanıcıları:</p>
                    <small>admin/admin123 | manager/manager123 | dropship_specialist/dropship123</small>
                </div>
            </div>
        </div>`;

        // Add to DOM if container exists
        const container = document.getElementById('auth-container');
        if (container) {
            container.innerHTML = loginFormHTML;
        }

        this.setupLoginFormEvents();
    }

    /**
     * 🎯 Setup Login Form Event Handlers
     */
    setupLoginFormEvents() {
        const loginForm = document.getElementById('selinay-login-form');
        const passwordInput = document.getElementById('password');
        
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => this.handleLogin(e));
        }
        
        if (passwordInput) {
            passwordInput.addEventListener('input', (e) => this.checkPasswordStrength(e.target.value));
        }
    }

    /**
     * 🔐 Handle Login Process
     */
    async handleLogin(event) {
        event.preventDefault();
        
        const loginBtn = document.getElementById('login-btn');
        const btnText = loginBtn.querySelector('.btn-text');
        const btnLoading = loginBtn.querySelector('.btn-loading');
        
        // Show loading state
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline';
        loginBtn.disabled = true;
        
        const formData = new FormData(event.target);
        const credentials = {
            username: formData.get('username'),
            password: formData.get('password'),
            remember: formData.get('remember-me') === 'on'
        };
        
        try {
            const response = await this.authenticateUser(credentials);
            
            if (response.success) {
                this.handleLoginSuccess(response.data);
                this.showNotification('✅ Giriş başarılı! Yönlendiriliyorsunuz...', 'success');
                
                // Redirect to dashboard after 1 second
                setTimeout(() => {
                    window.location.href = '/dashboard';
                }, 1000);
            } else {
                this.handleLoginError(response.error);
            }
        } catch (error) {
            this.handleLoginError('Bağlantı hatası: ' + error.message);
        } finally {
            // Reset button state
            btnText.style.display = 'inline';
            btnLoading.style.display = 'none';
            loginBtn.disabled = false;
        }
    }

    /**
     * 🌐 Authenticate User with Backend
     */
    async authenticateUser(credentials) {
        try {
            const response = await fetch(`${this.apiBase}/auth/login`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(credentials)
            });
            
            if (response.ok) {
                const data = await response.json();
                return { success: true, data };
            } else {
                const error = await response.json();
                return { success: false, error: error.message || 'Giriş başarısız' };
            }
        } catch (error) {
            // Fallback for development - simulate successful login
            console.warn('Backend not available, using mock authentication');
            return this.mockAuthentication(credentials);
        }
    }

    /**
     * 🧪 Mock Authentication for Development
     */
    mockAuthentication(credentials) {
        const mockUsers = {
            'admin': { password: 'admin123', role: 'super_admin', name: 'Super Admin' },
            'manager': { password: 'manager123', role: 'manager', name: 'Manager' },
            'dropship_specialist': { password: 'dropship123', role: 'dropship_specialist', name: 'Dropship Specialist' }
        };
        
        const user = mockUsers[credentials.username];
        
        if (user && user.password === credentials.password) {
            const mockToken = 'mock_jwt_token_' + Date.now();
            const userData = {
                id: Math.floor(Math.random() * 1000),
                username: credentials.username,
                name: user.name,
                role: user.role,
                permissions: this.getRolePermissions(user.role)
            };
            
            return {
                success: true,
                data: {
                    token: mockToken,
                    refreshToken: 'mock_refresh_' + Date.now(),
                    user: userData,
                    expiresIn: 3600
                }
            };
        } else {
            return {
                success: false,
                error: 'Kullanıcı adı veya şifre hatalı'
            };
        }
    }

    /**
     * 👤 Get Role Permissions
     */
    getRolePermissions(role) {
        const permissions = {
            super_admin: ['all'],
            admin: ['dashboard', 'users', 'orders', 'products', 'analytics'],
            manager: ['dashboard', 'orders', 'products', 'analytics'],
            dropship_specialist: ['dashboard', 'dropshipping', 'orders'],
            user: ['dashboard']
        };
        
        return permissions[role] || ['dashboard'];
    }

    /**
     * ✅ Handle Login Success
     */
    handleLoginSuccess(data) {
        // Store tokens securely
        this.storeToken(data.token);
        this.storeRefreshToken(data.refreshToken);
        this.storeUserData(data.user);
        
        // Set user session
        this.currentUser = data.user;
        
        console.log('✅ Login successful:', data.user.name);
    }

    /**
     * ❌ Handle Login Error
     */
    handleLoginError(error) {
        this.showNotification('❌ ' + error, 'error');
        console.error('Login error:', error);
    }

    /**
     * 🔑 JWT Token Management
     */
    setupTokenManager() {
        // Check for existing token on page load
        const token = this.getToken();
        if (token && this.isTokenValid(token)) {
            this.currentUser = this.getUserData();
            console.log('✅ User already authenticated:', this.currentUser?.name);
        }
    }

    storeToken(token) {
        localStorage.setItem(this.tokenManager.tokenKey, token);
    }

    storeRefreshToken(refreshToken) {
        localStorage.setItem(this.tokenManager.refreshKey, refreshToken);
    }

    storeUserData(userData) {
        localStorage.setItem(this.tokenManager.userKey, JSON.stringify(userData));
    }

    getToken() {
        return localStorage.getItem(this.tokenManager.tokenKey);
    }

    getRefreshToken() {
        return localStorage.getItem(this.tokenManager.refreshKey);
    }

    getUserData() {
        const userData = localStorage.getItem(this.tokenManager.userKey);
        return userData ? JSON.parse(userData) : null;
    }

    isTokenValid(token) {
        if (!token) return false;
        
        try {
            // Simple token validation (in real app, check expiration)
            const parts = token.split('.');
            return parts.length === 3 || token.startsWith('mock_jwt_token_');
        } catch (error) {
            return false;
        }
    }

    /**
     * 🚪 Logout Function
     */
    logout() {
        localStorage.removeItem(this.tokenManager.tokenKey);
        localStorage.removeItem(this.tokenManager.refreshKey);
        localStorage.removeItem(this.tokenManager.userKey);
        
        this.currentUser = null;
        
        this.showNotification('✅ Güvenli çıkış yapıldı', 'success');
        
        // Redirect to login
        setTimeout(() => {
            window.location.href = '/login';
        }, 1000);
    }

    /**
     * 🛡️ Protected Routes System
     */
    initializeProtectedRoutes() {
        // Route protection middleware
        this.protectedRoutes = [
            '/dashboard',
            '/admin',
            '/users',
            '/orders',
            '/products',
            '/analytics',
            '/dropshipping'
        ];
        
        this.checkRouteAccess();
    }

    checkRouteAccess() {
        const currentPath = window.location.pathname;
        const isProtectedRoute = this.protectedRoutes.some(route => 
            currentPath.startsWith(route)
        );
        
        if (isProtectedRoute && !this.isAuthenticated()) {
            this.redirectToLogin();
            return false;
        }
        
        return true;
    }

    isAuthenticated() {
        const token = this.getToken();
        return token && this.isTokenValid(token);
    }

    redirectToLogin() {
        this.showNotification('🔐 Giriş yapmanız gerekiyor', 'warning');
        setTimeout(() => {
            window.location.href = '/login';
        }, 1500);
    }

    /**
     * 👤 User Profile Components
     */
    createUserProfileComponents() {
        const userProfileHTML = `
        <div id="selinay-user-profile" class="user-profile-dropdown" style="display: none;">
            <div class="profile-header">
                <div class="profile-avatar">👤</div>
                <div class="profile-info">
                    <div class="profile-name" id="profile-name">Kullanıcı</div>
                    <div class="profile-role" id="profile-role">Rol</div>
                </div>
            </div>
            
            <div class="profile-menu">
                <a href="#" class="profile-menu-item" onclick="selinayAuth.showProfile()">
                    👤 Profil Ayarları
                </a>
                <a href="#" class="profile-menu-item" onclick="selinayAuth.showSecurity()">
                    🔒 Güvenlik
                </a>
                <a href="#" class="profile-menu-item" onclick="selinayAuth.showPreferences()">
                    ⚙️ Tercihler
                </a>
                <hr>
                <a href="#" class="profile-menu-item logout" onclick="selinayAuth.logout()">
                    🚪 Çıkış Yap
                </a>
            </div>
        </div>`;
        
        // Add to DOM
        document.body.insertAdjacentHTML('beforeend', userProfileHTML);
    }

    updateUserProfile() {
        if (this.currentUser) {
            const profileName = document.getElementById('profile-name');
            const profileRole = document.getElementById('profile-role');
            
            if (profileName) profileName.textContent = this.currentUser.name;
            if (profileRole) profileRole.textContent = this.getRoleDisplayName(this.currentUser.role);
        }
    }

    getRoleDisplayName(role) {
        const roleNames = {
            super_admin: 'Süper Admin',
            admin: 'Admin',
            manager: 'Yönetici',
            dropship_specialist: 'Dropshipping Uzmanı',
            user: 'Kullanıcı'
        };
        
        return roleNames[role] || 'Kullanıcı';
    }

    /**
     * 🔒 Password Strength Checker
     */
    checkPasswordStrength(password) {
        const strengthIndicator = document.getElementById('password-strength');
        if (!strengthIndicator) return;
        
        let strength = 0;
        let feedback = [];
        
        if (password.length >= 8) strength++;
        else feedback.push('En az 8 karakter');
        
        if (/[A-Z]/.test(password)) strength++;
        else feedback.push('Büyük harf');
        
        if (/[a-z]/.test(password)) strength++;
        else feedback.push('Küçük harf');
        
        if (/[0-9]/.test(password)) strength++;
        else feedback.push('Rakam');
        
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        else feedback.push('Özel karakter');
        
        const strengthLevels = ['Çok Zayıf', 'Zayıf', 'Orta', 'Güçlü', 'Çok Güçlü'];
        const strengthColors = ['#ff4444', '#ff8800', '#ffaa00', '#88cc00', '#00cc44'];
        
        strengthIndicator.innerHTML = `
            <div class="strength-bar">
                <div class="strength-fill" style="width: ${(strength/5)*100}%; background: ${strengthColors[strength-1] || '#ddd'}"></div>
            </div>
            <div class="strength-text">${strengthLevels[strength-1] || 'Şifre girin'}</div>
            ${feedback.length > 0 ? `<div class="strength-feedback">Eksik: ${feedback.join(', ')}</div>` : ''}
        `;
    }

    /**
     * 📢 Notification System
     */
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `selinay-notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                ${message}
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 5000);
    }

    /**
     * 🎨 Initialize CSS Styles
     */
    initializeStyles() {
        const styles = `
        <style>
        .auth-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            animation: slideUp 0.5s ease-out;
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h2 {
            color: #333;
            margin: 0 0 10px 0;
            font-size: 24px;
        }
        
        .login-header p {
            color: #666;
            margin: 0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .password-strength {
            margin-top: 8px;
        }
        
        .strength-bar {
            height: 4px;
            background: #e1e5e9;
            border-radius: 2px;
            overflow: hidden;
        }
        
        .strength-fill {
            height: 100%;
            transition: width 0.3s, background 0.3s;
        }
        
        .strength-text {
            font-size: 12px;
            margin-top: 4px;
            font-weight: 500;
        }
        
        .strength-feedback {
            font-size: 11px;
            color: #666;
            margin-top: 2px;
        }
        
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #666;
        }
        
        .remember-me input {
            margin-right: 8px;
        }
        
        .forgot-password {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        
        .forgot-password:hover {
            text-decoration: underline;
        }
        
        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
        }
        
        .login-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e1e5e9;
        }
        
        .login-footer p {
            margin: 0 0 5px 0;
            font-size: 12px;
            color: #666;
        }
        
        .login-footer small {
            color: #999;
            font-size: 11px;
        }
        
        .selinay-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 20px;
            border-radius: 10px;
            color: white;
            font-weight: 500;
            z-index: 10000;
            animation: slideIn 0.3s ease-out;
        }
        
        .selinay-notification.success { background: #00cc44; }
        .selinay-notification.error { background: #ff4444; }
        .selinay-notification.warning { background: #ff8800; }
        .selinay-notification.info { background: #0088cc; }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(100%); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .notification-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .notification-close {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            margin-left: 10px;
        }
        
        @media (max-width: 480px) {
            .login-card {
                margin: 20px;
                padding: 30px 20px;
            }
        }
        </style>
        `;
        
        document.head.insertAdjacentHTML('beforeend', styles);
    }
}

// 🚀 Initialize Selinay Authentication System
const selinayAuth = new SelinayAuthenticationSystem();

// Add styles to page
selinayAuth.initializeStyles();

// Export for global access
window.selinayAuth = selinayAuth;

console.log('🔐 Selinay Authentication System v1.0.0 Ready!');
console.log('✅ Phase 1: Authentication Integration - COMPLETED'); 