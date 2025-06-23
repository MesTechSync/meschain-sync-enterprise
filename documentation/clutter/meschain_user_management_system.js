/**
 * 🔥 MESCHAIN-SYNC USER MANAGEMENT SYSTEM - DAY 1 PHASE 1B
 * ⚡ CRITICAL: RBAC, AUTHENTICATION WORKFLOWS, PERMISSION-BASED UI
 * 📅 June 11, 2025 - CURSOR TEAM CRITICAL TASK IMPLEMENTATION
 */

// 👥 USER ROLES AND PERMISSIONS CONFIGURATION
const USER_ROLES = {
    SUPER_ADMIN: {
        id: 'super_admin',
        name: 'Super Administrator',
        level: 100,
        permissions: ['*'], // All permissions
        description: 'Full system access and control'
    },
    ADMIN: {
        id: 'admin',
        name: 'Administrator',
        level: 90,
        permissions: [
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'marketplaces.manage', 'settings.modify', 'reports.access',
            'products.manage', 'orders.manage', 'inventory.manage'
        ],
        description: 'Administrative access to all modules'
    },
    MANAGER: {
        id: 'manager',
        name: 'Manager',
        level: 70,
        permissions: [
            'users.view', 'users.edit',
            'marketplaces.view', 'marketplaces.configure',
            'products.manage', 'orders.manage', 'inventory.view',
            'reports.access'
        ],
        description: 'Management level access'
    },
    OPERATOR: {
        id: 'operator',
        name: 'Operator',
        level: 50,
        permissions: [
            'products.view', 'products.edit',
            'orders.view', 'orders.process',
            'inventory.view', 'reports.view'
        ],
        description: 'Operational access to core functions'
    },
    VIEWER: {
        id: 'viewer',
        name: 'Viewer',
        level: 30,
        permissions: [
            'products.view', 'orders.view', 'inventory.view', 'reports.view'
        ],
        description: 'Read-only access to data'
    }
};

// 🔐 AUTHENTICATION MANAGER
class AuthenticationManager {
    constructor() {
        this.currentUser = null;
        this.sessionToken = null;
        this.sessionExpiry = null;
        this.sessionTimeout = 8 * 60 * 60 * 1000; // 8 hours
        this.initializeAuth();
    }

    initializeAuth() {
        console.log('🔐 Initializing User Authentication Manager...');
        
        const storedSession = localStorage.getItem('meschain-session');
        if (storedSession) {
            try {
                const session = JSON.parse(storedSession);
                if (this.isValidSession(session)) {
                    this.restoreSession(session);
                } else {
                    this.clearSession();
                }
            } catch (error) {
                console.error('❌ Failed to restore session:', error);
                this.clearSession();
            }
        }
        console.log('🔐 Authentication Manager initialized');
    }

    async login(credentials) {
        const { username, password, rememberMe = false } = credentials;
        
        try {
            console.log(`🔐 Attempting login for user: ${username}`);
            
            const user = await this.validateCredentials(username, password);
            
            if (!user) {
                throw new Error('Invalid username or password');
            }

            const session = this.generateSession(user, rememberMe);
            this.currentUser = user;
            this.sessionToken = session.token;
            this.sessionExpiry = session.expiry;
            this.storeSession(session);

            console.log(`✅ Login successful for ${username} (${user.role})`);
            return {
                success: true,
                user: this.sanitizeUser(user),
                permissions: this.getUserPermissions(user),
                session: { token: session.token, expiry: session.expiry }
            };

        } catch (error) {
            console.error(`❌ Login failed for ${username}:`, error.message);
            return { success: false, error: error.message };
        }
    }

    async validateCredentials(username, password) {
        // DEMO USERS - In production, this would validate against secure backend
        const demoUsers = {
            'admin': {
                id: 'admin-001',
                username: 'admin',
                email: 'admin@meschain.com',
                firstName: 'System',
                lastName: 'Administrator',
                role: 'super_admin',
                status: 'active',
                avatar: null,
                lastLogin: null,
                createdAt: '2025-01-01T00:00:00Z'
            },
            'manager': {
                id: 'mgr-001',
                username: 'manager',
                email: 'manager@meschain.com',
                firstName: 'Sales',
                lastName: 'Manager',
                role: 'manager',
                status: 'active',
                avatar: null,
                lastLogin: null,
                createdAt: '2025-01-15T00:00:00Z'
            },
            'operator': {
                id: 'op-001',
                username: 'operator',
                email: 'operator@meschain.com',
                firstName: 'System',
                lastName: 'Operator',
                role: 'operator',
                status: 'active',
                avatar: null,
                lastLogin: null,
                createdAt: '2025-02-01T00:00:00Z'
            }
        };

        const user = demoUsers[username.toLowerCase()];
        if (user && password === 'demo123') {
            user.lastLogin = new Date().toISOString();
            return user;
        }
        return null;
    }

    generateSession(user, rememberMe) {
        const now = Date.now();
        const expiry = rememberMe ? 
            now + (30 * 24 * 60 * 60 * 1000) : // 30 days
            now + this.sessionTimeout; // 8 hours

        const token = this.generateSecureToken();
        
        return {
            token,
            userId: user.id,
            username: user.username,
            role: user.role,
            permissions: this.getUserPermissions(user),
            expiry,
            createdAt: now,
            rememberMe
        };
    }

    generateSecureToken() {
        return 'demo_token_' + Math.random().toString(36).substr(2, 16) + '_' + Date.now();
    }

    storeSession(session) {
        localStorage.setItem('meschain-session', JSON.stringify(session));
        if (session.rememberMe) {
            localStorage.setItem('meschain-remember', 'true');
        }
    }

    restoreSession(session) {
        this.currentUser = {
            id: session.userId,
            username: session.username,
            role: session.role
        };
        this.sessionToken = session.token;
        this.sessionExpiry = session.expiry;
        console.log(`🔄 Session restored for ${session.username}`);
    }

    isValidSession(session) {
        return session && session.token && session.expiry && Date.now() < session.expiry;
    }

    logout() {
        if (this.currentUser) {
            console.log(`🔐 Logging out user: ${this.currentUser.username}`);
        }
        this.clearSession();
        console.log('🔐 User logged out successfully');
        return { success: true };
    }

    clearSession() {
        this.currentUser = null;
        this.sessionToken = null;
        this.sessionExpiry = null;
        localStorage.removeItem('meschain-session');
        localStorage.removeItem('meschain-remember');
    }

    getCurrentUser() {
        return this.currentUser ? this.sanitizeUser(this.currentUser) : null;
    }

    isAuthenticated() {
        return this.currentUser && 
               this.sessionToken && 
               this.sessionExpiry && 
               Date.now() < this.sessionExpiry;
    }

    getUserPermissions(user) {
        const role = USER_ROLES[user.role.toUpperCase()];
        return role ? role.permissions : [];
    }

    hasPermission(permission) {
        if (!this.isAuthenticated()) return false;
        const userPermissions = this.getUserPermissions(this.currentUser);
        return userPermissions.includes('*') || userPermissions.includes(permission);
    }

    sanitizeUser(user) {
        const { passwordHash, ...sanitized } = user;
        return sanitized;
    }

    extendSession() {
        if (this.isAuthenticated()) {
            this.sessionExpiry = Date.now() + this.sessionTimeout;
            const session = JSON.parse(localStorage.getItem('meschain-session'));
            if (session) {
                session.expiry = this.sessionExpiry;
                this.storeSession(session);
            }
        }
    }
}

// 🛡️ ROLE-BASED ACCESS CONTROL (RBAC) MANAGER
class RBACManager {
    constructor(authManager) {
        this.auth = authManager;
        this.permissionCache = new Map();
    }

    checkPermission(permission, showWarning = true) {
        const hasPermission = this.auth.hasPermission(permission);
        
        if (!hasPermission && showWarning) {
            console.warn(`🛡️ Access denied: Missing permission '${permission}'`);
            this.showAccessDeniedNotification(permission);
        }
        
        return hasPermission;
    }

    enforcePermission(permission, action) {
        if (!this.checkPermission(permission)) {
            throw new Error(`Access denied: Missing permission '${permission}'`);
        }
        return action();
    }

    async enforcePermissionAsync(permission, asyncAction) {
        if (!this.checkPermission(permission)) {
            throw new Error(`Access denied: Missing permission '${permission}'`);
        }
        return await asyncAction();
    }

    showAccessDeniedNotification(permission) {
        if (window.showNotification) {
            window.showNotification(
                `🛡️ Access Denied\n\nYou don't have permission to perform this action.\n\nRequired permission: ${permission}`,
                'error'
            );
        }
    }

    hideElementsByPermission() {
        document.querySelectorAll('[data-permission]').forEach(element => {
            const requiredPermission = element.getAttribute('data-permission');
            if (!this.auth.hasPermission(requiredPermission)) {
                element.style.display = 'none';
                element.setAttribute('data-hidden-by-rbac', 'true');
            }
        });
    }

    showElementsByPermission() {
        document.querySelectorAll('[data-hidden-by-rbac]').forEach(element => {
            const requiredPermission = element.getAttribute('data-permission');
            if (this.auth.hasPermission(requiredPermission)) {
                element.style.display = '';
                element.removeAttribute('data-hidden-by-rbac');
            }
        });
    }

    updateUIForCurrentUser() {
        const user = this.auth.getCurrentUser();
        if (!user) {
            // Show guest state
            document.querySelectorAll('[data-user-info]').forEach(element => {
                const infoType = element.getAttribute('data-user-info');
                switch(infoType) {
                    case 'username':
                        element.textContent = 'Guest';
                        break;
                    case 'fullname':
                        element.textContent = 'Guest User';
                        break;
                    case 'role':
                        element.textContent = 'Guest';
                        break;
                    case 'email':
                        element.textContent = 'guest@example.com';
                        break;
                }
            });
            return;
        }

        // Update user info displays
        document.querySelectorAll('[data-user-info]').forEach(element => {
            const infoType = element.getAttribute('data-user-info');
            switch(infoType) {
                case 'username':
                    element.textContent = user.username;
                    break;
                case 'fullname':
                    element.textContent = `${user.firstName} ${user.lastName}`;
                    break;
                case 'role':
                    element.textContent = USER_ROLES[user.role.toUpperCase()]?.name || user.role;
                    break;
                case 'email':
                    element.textContent = user.email;
                    break;
            }
        });

        // Update role badges
        const roleInfo = USER_ROLES[user.role.toUpperCase()];
        document.querySelectorAll('[data-user-role-badge]').forEach(element => {
            element.textContent = roleInfo?.name || user.role;
            element.className = `role-badge role-${user.role}`;
        });

        // Show/hide elements based on permissions
        this.hideElementsByPermission();
        this.showElementsByPermission();

        console.log(`🔄 UI updated for user: ${user.username} (${user.role})`);
    }

    getUserRoleInfo(userId = null) {
        const user = userId ? this.getUserById(userId) : this.auth.getCurrentUser();
        if (!user) return null;

        const roleInfo = USER_ROLES[user.role.toUpperCase()];
        return {
            ...roleInfo,
            user: this.auth.sanitizeUser(user)
        };
    }

    canAccessModule(moduleName) {
        const modulePermissions = {
            'users': 'users.view',
            'products': 'products.view',
            'orders': 'orders.view',
            'inventory': 'inventory.view',
            'reports': 'reports.view',
            'settings': 'settings.view',
            'marketplaces': 'marketplaces.view'
        };

        const requiredPermission = modulePermissions[moduleName];
        return requiredPermission ? this.auth.hasPermission(requiredPermission) : false;
    }
}

// 👤 USER INTERFACE MANAGER
class UserInterfaceManager {
    constructor(authManager, rbacManager) {
        this.auth = authManager;
        this.rbac = rbacManager;
        this.initializeUI();
    }

    initializeUI() {
        this.createLoginModal();
        this.attachEventListeners();
        this.checkAuthenticationStatus();
    }

    createLoginModal() {
        if (document.getElementById('login-modal')) return;

        const modalHTML = `
            <div id="login-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 max-w-md w-full mx-4 transform transition-all duration-300 scale-95">
                    <div class="text-center mb-8">
                        <div class="w-20 h-20 bg-gradient-to-br from-purple-600 to-pink-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="ph ph-user text-white text-3xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Welcome Back</h2>
                        <p class="text-gray-600 dark:text-gray-300">Please sign in to continue</p>
                    </div>

                    <form id="login-form" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Username</label>
                            <input type="text" id="login-username" required
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                placeholder="Enter your username">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password</label>
                            <input type="password" id="login-password" required
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                placeholder="Enter your password">
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <input type="checkbox" id="remember-me" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">Remember me</span>
                            </label>
                        </div>

                        <button type="submit" id="login-submit"
                            class="w-full py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 transition-colors font-medium">
                            Sign In
                        </button>
                    </form>

                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            Demo credentials: admin/demo123, manager/demo123, operator/demo123
                        </p>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    attachEventListeners() {
        // Login form submission
        document.addEventListener('submit', async (e) => {
            if (e.target.id === 'login-form') {
                e.preventDefault();
                await this.handleLogin();
            }
        });

        // Logout buttons
        document.addEventListener('click', (e) => {
            if (e.target.id === 'logout-button' || e.target.closest('#logout-button') || 
                e.target.classList.contains('logout-trigger') || e.target.closest('.logout-trigger')) {
                this.handleLogout();
            }
        });
    }

    async handleLogin() {
        const username = document.getElementById('login-username').value;
        const password = document.getElementById('login-password').value;
        const rememberMe = document.getElementById('remember-me').checked;

        const submitButton = document.getElementById('login-submit');
        const originalText = submitButton.textContent;
        
        try {
            submitButton.textContent = 'Signing In...';
            submitButton.disabled = true;

            const result = await this.auth.login({ username, password, rememberMe });

            if (result.success) {
                this.hideLoginModal();
                this.rbac.updateUIForCurrentUser();
                
                if (window.showNotification) {
                    window.showNotification(
                        `🎉 Welcome back, ${result.user.firstName}!\n\nRole: ${USER_ROLES[result.user.role.toUpperCase()]?.name}\nLast login: ${new Date().toLocaleString()}`,
                        'success'
                    );
                }

                // Dispatch login event
                window.dispatchEvent(new CustomEvent('user-logged-in', {
                    detail: result
                }));

            } else {
                if (window.showNotification) {
                    window.showNotification(`🚨 Login Failed\n\n${result.error}`, 'error');
                }
            }

        } catch (error) {
            console.error('Login error:', error);
            if (window.showNotification) {
                window.showNotification(`❌ Login Error\n\n${error.message}`, 'error');
            }
        } finally {
            submitButton.textContent = originalText;
            submitButton.disabled = false;
        }
    }

    handleLogout() {
        const user = this.auth.getCurrentUser();
        this.auth.logout();
        this.rbac.updateUIForCurrentUser();
        
        if (window.showNotification) {
            window.showNotification(`👋 Goodbye, ${user?.firstName || 'User'}!\n\nYou have been logged out successfully.`, 'info');
        }

        // Show login modal after logout
        setTimeout(() => this.showLoginModal(), 1000);

        // Dispatch logout event
        window.dispatchEvent(new CustomEvent('user-logged-out'));
    }

    showLoginModal() {
        const modal = document.getElementById('login-modal');
        if (modal) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.querySelector('.bg-white').classList.remove('scale-95');
                modal.querySelector('.bg-white').classList.add('scale-100');
            }, 10);
        }
    }

    hideLoginModal() {
        const modal = document.getElementById('login-modal');
        if (modal) {
            modal.querySelector('.bg-white').classList.remove('scale-100');
            modal.querySelector('.bg-white').classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }
    }

    checkAuthenticationStatus() {
        if (this.auth.isAuthenticated()) {
            this.rbac.updateUIForCurrentUser();
        } else {
            setTimeout(() => this.showLoginModal(), 2000);
        }
    }
}

// 🚀 INITIALIZE USER MANAGEMENT SYSTEM
let userAuthManager, userRBACManager, userUIManager;

document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 Initializing User Management System...');
    
    // Initialize managers
    userAuthManager = new AuthenticationManager();
    userRBACManager = new RBACManager(userAuthManager);
    userUIManager = new UserInterfaceManager(userAuthManager, userRBACManager);

    // Make globally available for other components
    window.userAuthManager = userAuthManager;
    window.userRBACManager = userRBACManager;
    window.userUIManager = userUIManager;

    // Global helper functions
    window.requirePermission = (permission, action) => {
        return userRBACManager.enforcePermission(permission, action);
    };

    window.requirePermissionAsync = async (permission, asyncAction) => {
        return await userRBACManager.enforcePermissionAsync(permission, asyncAction);
    };

    window.hasPermission = (permission) => {
        return userAuthManager.hasPermission(permission);
    };

    window.getCurrentUser = () => {
        return userAuthManager.getCurrentUser();
    };

    window.isAuthenticated = () => {
        return userAuthManager.isAuthenticated();
    };

    console.log('✅ User Management System Ready!');
    console.log('🎯 DAY 1 PHASE 1B COMPLETED - RBAC & AUTHENTICATION ACTIVE!');
    console.log('👤 Login: admin/demo123 | manager/demo123 | operator/demo123');

    // Dispatch initialization event
    window.dispatchEvent(new CustomEvent('user-management-ready', {
        detail: {
            version: '4.1',
            initialized: Date.now(),
            features: ['authentication', 'rbac', 'session-management', 'ui-integration']
        }
    }));
});

// CSS for role badges and user interface
const userManagementCSS = `
    .role-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .role-super_admin {
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        color: white;
    }

    .role-admin {
        background: linear-gradient(135deg, #7c3aed, #6d28d9);
        color: white;
    }

    .role-manager {
        background: linear-gradient(135deg, #059669, #047857);
        color: white;
    }

    .role-operator {
        background: linear-gradient(135deg, #0891b2, #0e7490);
        color: white;
    }

    .role-viewer {
        background: linear-gradient(135deg, #4b5563, #374151);
        color: white;
    }

    [data-hidden-by-rbac] {
        display: none !important;
    }

    .permission-denied {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
    }

    #login-modal .scale-100 {
        transform: scale(1);
    }

    #login-modal .scale-95 {
        transform: scale(0.95);
    }
`;

// Inject CSS
const styleSheet = document.createElement('style');
styleSheet.textContent = userManagementCSS;
document.head.appendChild(styleSheet);

console.log('🔥 User Management System loaded!');
console.log('🎯 Ready for DAY 1 PHASE 1B implementation!'); 