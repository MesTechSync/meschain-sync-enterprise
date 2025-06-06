/**
 * MesChain-Sync Enterprise Login Interface
 * Universal login component for all enterprise services
 * Version: 4.0 Enterprise
 */

class MesChainLoginUI {
    constructor(options = {}) {
        this.options = {
            title: 'MesChain-Sync Enterprise',
            subtitle: 'Güvenli Giriş Sistemi',
            showLogo: true,
            showRememberMe: true,
            showDemoMode: true,
            redirectAfterLogin: true,
            targetService: null, // Port number for service-specific access
            theme: 'modern', // modern, classic, dark
            ...options
        };

        this.auth = window.mesChainAuth;
        this.isLoading = false;
        this.initializeUI();
    }

    /**
     * Initialize login UI
     */
    initializeUI() {
        this.createLoginInterface();
        this.setupEventListeners();
        this.setupAuthEventListeners();
        
        // Auto-redirect if already logged in
        if (this.auth.isLoggedIn() && this.options.redirectAfterLogin) {
            this.redirectAfterLogin();
        }
    }

    /**
     * Create login interface HTML
     */
    createLoginInterface() {
        const loginHTML = `
            <div id="meschain-login-container" class="login-container ${this.options.theme}">
                <div class="login-overlay"></div>
                <div class="login-card">
                    ${this.options.showLogo ? this.renderLogo() : ''}
                    
                    <div class="login-header">
                        <h1 class="login-title">${this.options.title}</h1>
                        <p class="login-subtitle">${this.options.subtitle}</p>
                        ${this.options.targetService ? this.renderServiceInfo() : ''}
                    </div>

                    <form id="meschain-login-form" class="login-form">
                        <div class="form-group">
                            <label for="username" class="form-label">
                                <i class="fas fa-user"></i> Kullanıcı Adı
                            </label>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                class="form-input" 
                                required 
                                placeholder="Kullanıcı adınızı girin"
                                autocomplete="username"
                            >
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i> Şifre
                            </label>
                            <div class="password-input-group">
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    class="form-input" 
                                    required 
                                    placeholder="Şifrenizi girin"
                                    autocomplete="current-password"
                                >
                                <button type="button" class="password-toggle" id="toggle-password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        ${this.options.showRememberMe ? this.renderRememberMe() : ''}

                        <div class="form-group">
                            <button type="submit" class="login-btn" id="login-submit-btn">
                                <span class="btn-text">
                                    <i class="fas fa-sign-in-alt"></i> Giriş Yap
                                </span>
                                <span class="btn-loading" style="display: none;">
                                    <i class="fas fa-spinner fa-spin"></i> Giriş yapılıyor...
                                </span>
                            </button>
                        </div>

                        ${this.options.showDemoMode ? this.renderDemoModeSection() : ''}
                    </form>

                    <div class="login-footer">
                        <div class="security-info">
                            <i class="fas fa-shield-alt"></i>
                            <span>256-bit SSL güvenli bağlantı</span>
                        </div>
                        
                        <div class="login-links">
                            <a href="#" class="link forgot-password">Şifremi Unuttum</a>
                            <a href="#" class="link contact-support">Destek</a>
                        </div>
                    </div>
                </div>

                <!-- Notification Area -->
                <div id="login-notifications" class="notifications-container"></div>
            </div>
        `;

        // Add to body or replace existing content
        const existingContainer = document.getElementById('meschain-login-container');
        if (existingContainer) {
            existingContainer.outerHTML = loginHTML;
        } else {
            document.body.insertAdjacentHTML('beforeend', loginHTML);
        }

        // Load CSS if not already loaded
        this.loadCSS();
    }

    /**
     * Render logo section
     */
    renderLogo() {
        return `
            <div class="login-logo">
                <img src="/CursorDev/ASSETS/meschain-logo.png" alt="MesChain Logo" class="logo-image" onerror="this.style.display='none'">
                <div class="logo-text">
                    <span class="logo-main">MesChain</span>
                    <span class="logo-sub">SYNC</span>
                </div>
            </div>
        `;
    }

    /**
     * Render service-specific information
     */
    renderServiceInfo() {
        const serviceNames = {
            3000: 'Ana Dashboard',
            3001: 'Bileşen Kütüphanesi',
            3002: 'Süper Admin Panel',
            3003: 'Pazaryeri Hub',
            3009: 'Çapraz Platform Admin',
            3010: 'Hepsiburada Uzmanı'
        };

        const serviceName = serviceNames[this.options.targetService] || 'Enterprise Servis';
        
        return `
            <div class="service-info">
                <i class="fas fa-server"></i>
                <span>Erişim: ${serviceName} (Port ${this.options.targetService})</span>
            </div>
        `;
    }

    /**
     * Render remember me option
     */
    renderRememberMe() {
        return `
            <div class="form-group form-options">
                <label class="checkbox-label">
                    <input type="checkbox" id="remember-me" name="remember_me">
                    <span class="checkmark"></span>
                    <span class="checkbox-text">Beni hatırla</span>
                </label>
            </div>
        `;
    }

    /**
     * Render demo mode section
     */
    renderDemoModeSection() {
        return `
            <div class="demo-mode-section">
                <div class="demo-divider">
                    <span>veya</span>
                </div>
                
                <div class="demo-accounts">
                    <h4>Demo Hesaplar:</h4>
                    <div class="demo-account-list">
                        <button type="button" class="demo-account-btn" data-username="super_admin" data-role="super_admin">
                            <i class="fas fa-crown"></i>
                            <span>Super Admin</span>
                            <small>Tüm yetkiler</small>
                        </button>
                        <button type="button" class="demo-account-btn" data-username="admin" data-role="admin">
                            <i class="fas fa-user-tie"></i>
                            <span>Admin</span>
                            <small>Yönetim yetkileri</small>
                        </button>
                        <button type="button" class="demo-account-btn" data-username="marketplace_manager" data-role="marketplace_manager">
                            <i class="fas fa-store"></i>
                            <span>Pazaryeri Manager</span>
                            <small>Pazaryeri yönetimi</small>
                        </button>
                    </div>
                    <p class="demo-note">
                        <i class="fas fa-info-circle"></i>
                        Demo hesaplar için şifre: <code>demo123</code>
                    </p>
                </div>
            </div>
        `;
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Form submission
        document.getElementById('meschain-login-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleLogin();
        });

        // Password toggle
        document.getElementById('toggle-password')?.addEventListener('click', () => {
            this.togglePasswordVisibility();
        });

        // Demo account buttons
        document.querySelectorAll('.demo-account-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const username = e.currentTarget.dataset.username;
                const role = e.currentTarget.dataset.role;
                this.fillDemoCredentials(username, role);
            });
        });

        // Forgot password
        document.querySelector('.forgot-password')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.showForgotPasswordDialog();
        });

        // Contact support
        document.querySelector('.contact-support')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.showSupportDialog();
        });

        // Enter key in username field focuses password
        document.getElementById('username').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('password').focus();
            }
        });
    }

    /**
     * Setup authentication event listeners
     */
    setupAuthEventListeners() {
        // Listen for auth events
        document.addEventListener('meschain-auth-login-success', (e) => {
            this.onLoginSuccess(e.detail.user);
        });

        document.addEventListener('meschain-auth-loading', (e) => {
            this.setLoadingState(e.detail.loading);
        });

        document.addEventListener('meschain-auth-notification', (e) => {
            this.showNotification(e.detail.message, e.detail.type);
        });
    }

    /**
     * Handle login form submission
     */
    async handleLogin() {
        const form = document.getElementById('meschain-login-form');
        const formData = new FormData(form);
        
        const credentials = {
            username: formData.get('username').trim(),
            password: formData.get('password'),
            rememberMe: formData.get('remember_me') === 'on'
        };

        // Basic validation
        if (!credentials.username || !credentials.password) {
            this.showNotification('Kullanıcı adı ve şifre gereklidir', 'error');
            return;
        }

        // Check service access if specified
        if (this.options.targetService) {
            // Will be checked after login
        }

        try {
            const result = await this.auth.login(credentials);
            
            if (result.success) {
                // Check service access
                if (this.options.targetService && !this.auth.canAccessService(this.options.targetService)) {
                    this.showNotification(`Bu servise erişim yetkiniz bulunmamaktadır (Port ${this.options.targetService})`, 'error');
                    await this.auth.logout();
                    return;
                }

                this.showNotification(
                    result.demo ? 'Demo modunda giriş başarılı' : 'Giriş başarılı', 
                    result.demo ? 'warning' : 'success'
                );
                
                // Will trigger redirect via event listener
            }
        } catch (error) {
            this.showNotification(error.message, 'error');
        }
    }

    /**
     * Handle successful login
     */
    onLoginSuccess(user) {
        console.log('🎉 Login successful:', user);
        
        if (this.options.redirectAfterLogin) {
            setTimeout(() => {
                this.redirectAfterLogin();
            }, 1500);
        }
    }

    /**
     * Redirect after successful login
     */
    redirectAfterLogin() {
        const user = this.auth.getCurrentUser();
        if (!user) return;

        let redirectUrl = '/';

        // Service-specific redirect
        if (this.options.targetService) {
            const serviceUrls = {
                3000: 'http://localhost:3000',
                3001: 'http://localhost:3001',
                3002: 'http://localhost:3002',
                3003: 'http://localhost:3003',
                3009: 'http://localhost:3009',
                3010: 'http://localhost:3010'
            };
            redirectUrl = serviceUrls[this.options.targetService] || redirectUrl;
        } else {
            // Role-based redirect
            const roleRedirects = {
                'super_admin': 'http://localhost:3002',
                'admin': 'http://localhost:3001',
                'marketplace_manager': 'http://localhost:3003',
                'technical': 'http://localhost:3001',
                'dropshipper': 'http://localhost:3000',
                'viewer': 'http://localhost:3000'
            };
            redirectUrl = roleRedirects[user.role] || 'http://localhost:3000';
        }

        console.log('🔄 Redirecting to:', redirectUrl);
        
        // Show redirect message
        this.showNotification('Yönlendiriliyor...', 'info');
        
        // Redirect
        setTimeout(() => {
            window.location.href = redirectUrl;
        }, 1000);
    }

    /**
     * Fill demo credentials
     */
    fillDemoCredentials(username, role) {
        document.getElementById('username').value = username;
        document.getElementById('password').value = 'demo123';
        
        this.showNotification(`${role} demo hesabı seçildi`, 'info');
        
        // Auto-submit after short delay
        setTimeout(() => {
            document.getElementById('meschain-login-form').dispatchEvent(new Event('submit'));
        }, 500);
    }

    /**
     * Toggle password visibility
     */
    togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.getElementById('toggle-password');
        const icon = toggleBtn.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            passwordInput.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }

    /**
     * Set loading state
     */
    setLoadingState(loading) {
        this.isLoading = loading;
        const submitBtn = document.getElementById('login-submit-btn');
        const btnText = submitBtn.querySelector('.btn-text');
        const btnLoading = submitBtn.querySelector('.btn-loading');
        
        if (loading) {
            submitBtn.disabled = true;
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline-flex';
        } else {
            submitBtn.disabled = false;
            btnText.style.display = 'inline-flex';
            btnLoading.style.display = 'none';
        }
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        const container = document.getElementById('login-notifications');
        const notificationId = 'notification-' + Date.now();
        
        const notificationHTML = `
            <div id="${notificationId}" class="notification notification-${type}">
                <div class="notification-content">
                    <i class="fas fa-${this.getNotificationIcon(type)}"></i>
                    <span>${message}</span>
                </div>
                <button class="notification-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', notificationHTML);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            const notification = document.getElementById(notificationId);
            if (notification) {
                notification.remove();
            }
        }, 5000);
    }

    /**
     * Get notification icon based on type
     */
    getNotificationIcon(type) {
        const icons = {
            'success': 'check-circle',
            'error': 'exclamation-circle',
            'warning': 'exclamation-triangle',
            'info': 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    /**
     * Show forgot password dialog
     */
    showForgotPasswordDialog() {
        this.showNotification('Şifre sıfırlama özelliği yakında aktif olacak', 'info');
    }

    /**
     * Show support dialog
     */
    showSupportDialog() {
        this.showNotification('Destek için: support@meschain.com', 'info');
    }

    /**
     * Load CSS styles
     */
    loadCSS() {
        // Check if CSS is already loaded
        if (document.getElementById('meschain-login-css')) return;

        const cssContent = this.getLoginCSS();
        const style = document.createElement('style');
        style.id = 'meschain-login-css';
        style.textContent = cssContent;
        document.head.appendChild(style);
    }

    /**
     * Get login CSS styles
     */
    getLoginCSS() {
        return `
            /* MesChain Login Interface Styles */
            .login-container {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                z-index: 10000;
            }

            .login-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
                animation: float 20s ease-in-out infinite;
            }

            .login-card {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border-radius: 20px;
                padding: 40px;
                width: 100%;
                max-width: 440px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                position: relative;
                margin: 20px;
                border: 1px solid rgba(255, 255, 255, 0.2);
            }

            .login-logo {
                text-align: center;
                margin-bottom: 30px;
            }

            .logo-image {
                width: 60px;
                height: 60px;
                margin-bottom: 10px;
            }

            .logo-text {
                display: flex;
                justify-content: center;
                align-items: baseline;
                gap: 5px;
            }

            .logo-main {
                font-size: 24px;
                font-weight: 700;
                color: #2c3e50;
            }

            .logo-sub {
                font-size: 14px;
                font-weight: 500;
                color: #7f8c8d;
                text-transform: uppercase;
                letter-spacing: 2px;
            }

            .login-header {
                text-align: center;
                margin-bottom: 30px;
            }

            .login-title {
                font-size: 28px;
                font-weight: 700;
                color: #2c3e50;
                margin: 0 0 10px 0;
            }

            .login-subtitle {
                color: #7f8c8d;
                margin: 0;
                font-size: 16px;
            }

            .service-info {
                background: #ecf0f1;
                padding: 10px 15px;
                border-radius: 10px;
                margin-top: 15px;
                display: flex;
                align-items: center;
                gap: 10px;
                font-size: 14px;
                color: #2c3e50;
            }

            .service-info i {
                color: #3498db;
            }

            .form-group {
                margin-bottom: 20px;
            }

            .form-label {
                display: block;
                margin-bottom: 8px;
                font-weight: 600;
                color: #2c3e50;
                font-size: 14px;
            }

            .form-label i {
                margin-right: 5px;
                color: #3498db;
            }

            .form-input {
                width: 100%;
                padding: 15px;
                border: 2px solid #ecf0f1;
                border-radius: 10px;
                font-size: 16px;
                transition: all 0.3s ease;
                background: #fff;
                box-sizing: border-box;
            }

            .form-input:focus {
                outline: none;
                border-color: #3498db;
                box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            }

            .password-input-group {
                position: relative;
            }

            .password-toggle {
                position: absolute;
                right: 15px;
                top: 50%;
                transform: translateY(-50%);
                background: none;
                border: none;
                color: #7f8c8d;
                cursor: pointer;
                padding: 5px;
            }

            .password-toggle:hover {
                color: #3498db;
            }

            .form-options {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .checkbox-label {
                display: flex;
                align-items: center;
                cursor: pointer;
                font-size: 14px;
                color: #2c3e50;
            }

            .checkbox-label input[type="checkbox"] {
                display: none;
            }

            .checkmark {
                width: 18px;
                height: 18px;
                border: 2px solid #bdc3c7;
                border-radius: 4px;
                margin-right: 8px;
                position: relative;
                transition: all 0.3s ease;
            }

            .checkbox-label input:checked + .checkmark {
                background: #3498db;
                border-color: #3498db;
            }

            .checkbox-label input:checked + .checkmark:after {
                content: '✓';
                position: absolute;
                color: white;
                font-size: 12px;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
            }

            .login-btn {
                width: 100%;
                padding: 15px;
                background: linear-gradient(135deg, #3498db, #2980b9);
                color: white;
                border: none;
                border-radius: 10px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
            }

            .login-btn:hover:not(:disabled) {
                background: linear-gradient(135deg, #2980b9, #1f6391);
                transform: translateY(-2px);
                box-shadow: 0 5px 20px rgba(52, 152, 219, 0.3);
            }

            .login-btn:disabled {
                opacity: 0.7;
                cursor: not-allowed;
                transform: none;
            }

            .demo-mode-section {
                margin-top: 30px;
                border-top: 1px solid #ecf0f1;
                padding-top: 20px;
            }

            .demo-divider {
                text-align: center;
                position: relative;
                margin: 20px 0;
            }

            .demo-divider span {
                background: white;
                padding: 0 15px;
                color: #7f8c8d;
                font-size: 14px;
            }

            .demo-divider:before {
                content: '';
                position: absolute;
                top: 50%;
                left: 0;
                right: 0;
                height: 1px;
                background: #ecf0f1;
                z-index: -1;
            }

            .demo-accounts h4 {
                text-align: center;
                color: #2c3e50;
                margin-bottom: 15px;
                font-size: 16px;
            }

            .demo-account-list {
                display: grid;
                grid-template-columns: 1fr;
                gap: 10px;
                margin-bottom: 15px;
            }

            .demo-account-btn {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 12px 15px;
                background: #f8f9fa;
                border: 1px solid #ecf0f1;
                border-radius: 8px;
                cursor: pointer;
                transition: all 0.3s ease;
                text-align: left;
            }

            .demo-account-btn:hover {
                background: #e9ecef;
                border-color: #3498db;
                transform: translateY(-1px);
            }

            .demo-account-btn i {
                font-size: 20px;
                color: #3498db;
                width: 24px;
            }

            .demo-account-btn span {
                font-weight: 600;
                color: #2c3e50;
            }

            .demo-account-btn small {
                color: #7f8c8d;
                font-size: 12px;
                display: block;
            }

            .demo-note {
                text-align: center;
                font-size: 12px;
                color: #7f8c8d;
                margin: 0;
            }

            .demo-note code {
                background: #ecf0f1;
                padding: 2px 6px;
                border-radius: 4px;
                font-family: 'Courier New', monospace;
            }

            .login-footer {
                margin-top: 30px;
                text-align: center;
            }

            .security-info {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                color: #27ae60;
                font-size: 12px;
                margin-bottom: 15px;
            }

            .login-links {
                display: flex;
                justify-content: center;
                gap: 20px;
            }

            .link {
                color: #3498db;
                text-decoration: none;
                font-size: 14px;
                transition: color 0.3s ease;
            }

            .link:hover {
                color: #2980b9;
                text-decoration: underline;
            }

            .notifications-container {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10001;
                max-width: 400px;
            }

            .notification {
                background: white;
                border-radius: 10px;
                padding: 15px;
                margin-bottom: 10px;
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
                border-left: 4px solid #3498db;
                display: flex;
                align-items: center;
                justify-content: space-between;
                animation: slideIn 0.3s ease-out;
            }

            .notification-success { border-left-color: #27ae60; }
            .notification-error { border-left-color: #e74c3c; }
            .notification-warning { border-left-color: #f39c12; }
            .notification-info { border-left-color: #3498db; }

            .notification-content {
                display: flex;
                align-items: center;
                gap: 10px;
                flex: 1;
            }

            .notification-content i {
                font-size: 16px;
            }

            .notification-success i { color: #27ae60; }
            .notification-error i { color: #e74c3c; }
            .notification-warning i { color: #f39c12; }
            .notification-info i { color: #3498db; }

            .notification-close {
                background: none;
                border: none;
                color: #7f8c8d;
                cursor: pointer;
                padding: 5px;
                margin-left: 10px;
            }

            .notification-close:hover {
                color: #2c3e50;
            }

            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }

            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-20px); }
            }

            /* Dark theme */
            .login-container.dark {
                background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            }

            .login-container.dark .login-card {
                background: rgba(44, 62, 80, 0.95);
                color: #ecf0f1;
            }

            .login-container.dark .login-title,
            .login-container.dark .form-label {
                color: #ecf0f1;
            }

            .login-container.dark .form-input {
                background: rgba(52, 73, 94, 0.8);
                border-color: #34495e;
                color: #ecf0f1;
            }

            .login-container.dark .form-input:focus {
                border-color: #3498db;
            }

            /* Responsive design */
            @media (max-width: 480px) {
                .login-card {
                    margin: 10px;
                    padding: 30px 20px;
                }
                
                .login-title {
                    font-size: 24px;
                }
                
                .demo-account-list {
                    grid-template-columns: 1fr;
                }
            }
        `;
    }

    /**
     * Destroy login interface
     */
    destroy() {
        const container = document.getElementById('meschain-login-container');
        if (container) {
            container.remove();
        }
        
        const css = document.getElementById('meschain-login-css');
        if (css) {
            css.remove();
        }
    }
}

// Export for use
window.MesChainLoginUI = MesChainLoginUI;
