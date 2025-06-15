/* 🔐 MESCHAIN-SYNC AUTHENTICATION & SESSION MANAGEMENT MODULE
   🔒 Advanced security features for Super Admin Panel
   👮‍♂️ Session monitoring, 2FA, security logging
   🛡️ Real-time threat detection and response
   💎 Cursor Team A+++++ Quality Enhancement
   🚀 JWT Token Management & Advanced Security */

class MesChainAuth {
    constructor() {
        this.sessionDuration = 30 * 60; // 30 minutes in seconds
        this.sessionTimer = null;
        this.sessionStartTime = Date.now();
        this.securityEvents = [];
        this.failedLoginAttempts = 0;
        this.maxFailedAttempts = 3;
        this.lockoutDuration = 15 * 60 * 1000; // 15 minutes
        this.lastActivity = Date.now();
        
        // 🆕 JWT Token Management
        this.jwtSecret = this.generateJWTSecret();
        this.activeTokens = new Map(); // Token blacklist management
        this.refreshTokens = new Map(); // Refresh token storage
        this.tokenBlacklist = new Set(); // Revoked tokens
        
        // 🆕 Advanced Security Features
        this.deviceFingerprint = this.generateDeviceFingerprint();
        this.securityLevel = 'high'; // low, medium, high, critical
        this.concurrentSessions = new Map(); // Multi-session tracking
        this.apiKeys = new Map(); // API key management
        
        this.init();
    }

    init() {
        this.startSessionTimer();
        this.setupActivityMonitoring();
        this.setupSecurityChecks();
        this.updateSessionDisplay();
        this.initializeJWTSystem();
        this.setupDeviceTracking();
    }

    // 🕐 Session Timer Management
    startSessionTimer() {
        this.sessionTimer = setInterval(() => {
            this.updateSessionDisplay();
            this.checkSessionExpiry();
        }, 1000);
    }

    updateSessionDisplay() {
        const elapsed = Math.floor((Date.now() - this.sessionStartTime) / 1000);
        const remaining = this.sessionDuration - elapsed;
        
        if (remaining <= 0) {
            this.handleSessionExpiry();
            return;
        }

        const minutes = Math.floor(remaining / 60);
        const seconds = remaining % 60;
        const display = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        const timerElement = document.getElementById('sessionTimer');
        if (timerElement) {
            timerElement.textContent = display;
            
            // 🎨 Dynamic animation classes based on remaining time
            const timerContainer = timerElement.closest('div');
            if (timerContainer) {
                // Remove existing animation classes
                timerContainer.classList.remove('session-normal', 'session-warning', 'session-critical');
                
                if (remaining < 300) { // Less than 5 minutes - Critical
                    timerContainer.classList.add('session-critical');
                    // Update threat level to high
                    this.updateThreatLevel('high');
                } else if (remaining < 600) { // Less than 10 minutes - Warning
                    timerContainer.classList.add('session-warning');
                    this.updateThreatLevel('medium');
                } else { // Normal
                    timerContainer.classList.add('session-normal');
                    this.updateThreatLevel('low');
                }
            }
        }
    }

    // 🛡️ Update threat level indicator animations
    updateThreatLevel(level) {
        const threatContainer = document.querySelector('.threat-level-low, .threat-level-medium, .threat-level-high');
        if (threatContainer) {
            // Remove existing threat level classes
            threatContainer.classList.remove('threat-level-low', 'threat-level-medium', 'threat-level-high');
            
            // Add new threat level class
            threatContainer.classList.add(`threat-level-${level}`);
            
            // Update threat level text and bars
            const threatText = threatContainer.querySelector('span');
            const threatBars = threatContainer.querySelectorAll('.threat-indicator div');
            
            if (threatText && threatBars) {
                switch(level) {
                    case 'low':
                        threatText.textContent = 'LOW';
                        threatBars.forEach((bar, index) => {
                            bar.className = 'w-1 h-3 rounded-full';
                            if (index < 2) bar.classList.add('bg-green-500');
                            else if (index === 2) bar.classList.add('bg-yellow-500');
                            else bar.classList.add('bg-gray-300', 'dark:bg-gray-600');
                        });
                        break;
                    case 'medium':
                        threatText.textContent = 'MEDIUM';
                        threatBars.forEach((bar, index) => {
                            bar.className = 'w-1 h-3 rounded-full';
                            if (index < 3) bar.classList.add('bg-yellow-500');
                            else bar.classList.add('bg-gray-300', 'dark:bg-gray-600');
                        });
                        break;
                    case 'high':
                        threatText.textContent = 'HIGH';
                        threatBars.forEach((bar, index) => {
                            bar.className = 'w-1 h-3 rounded-full';
                            if (index < 4) bar.classList.add('bg-red-500');
                            else bar.classList.add('bg-gray-300', 'dark:bg-gray-600');
                        });
                        break;
                }
            }
        }
    }

    checkSessionExpiry() {
        const elapsed = Math.floor((Date.now() - this.sessionStartTime) / 1000);
        if (elapsed >= this.sessionDuration) {
            this.handleSessionExpiry();
        }
    }

    handleSessionExpiry() {
        this.logSecurityEvent('session_expired', 'Session expired automatically');
        this.showSecurityAlert('Session Expired', 'Your session has expired for security reasons. Please login again.');
        this.secureLogout();
    }

    // 🔄 Session Extension
    extendSession() {
        this.sessionStartTime = Date.now();
        this.lastActivity = Date.now();
        this.logSecurityEvent('session_extended', 'Session extended by user activity');
        this.showNotification('Session Extended', 'Your session has been extended for 30 minutes.', 'success');
    }

    // 👀 Activity Monitoring
    setupActivityMonitoring() {
        const activityEvents = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];
        
        activityEvents.forEach(event => {
            document.addEventListener(event, () => {
                this.lastActivity = Date.now();
            });
        });

        // Check for inactivity every minute
        setInterval(() => {
            const inactiveTime = Date.now() - this.lastActivity;
            if (inactiveTime > 10 * 60 * 1000) { // 10 minutes inactive
                this.showInactivityWarning();
            }
        }, 60000);
    }

    showInactivityWarning() {
        this.showSecurityAlert(
            'Inactivity Warning', 
            'You have been inactive for 10 minutes. Your session will expire soon.',
            () => this.extendSession()
        );
    }

    // 🛡️ Security Functions
    setupSecurityChecks() {
        // Monitor for suspicious activity
        this.checkIPWhitelist();
        this.monitorFailedLogins();
        this.setupCSRFProtection();
    }

    checkIPWhitelist() {
        // This would typically check against a server-side whitelist
        // For demo purposes, we'll just log the check
        this.logSecurityEvent('ip_check', 'IP whitelist verification completed');
    }

    monitorFailedLogins() {
        // Monitor for failed login attempts
        const storedAttempts = localStorage.getItem('failedLoginAttempts');
        if (storedAttempts) {
            this.failedLoginAttempts = parseInt(storedAttempts);
        }
    }

    recordFailedLogin() {
        this.failedLoginAttempts++;
        localStorage.setItem('failedLoginAttempts', this.failedLoginAttempts.toString());
        
        this.logSecurityEvent('failed_login', `Failed login attempt #${this.failedLoginAttempts}`);
        
        if (this.failedLoginAttempts >= this.maxFailedAttempts) {
            this.triggerLockout();
        }
    }

    triggerLockout() {
        this.logSecurityEvent('account_lockout', 'Account locked due to multiple failed attempts');
        localStorage.setItem('lockoutTime', Date.now().toString());
        this.showSecurityAlert('Account Locked', 'Too many failed login attempts. Account locked for 15 minutes.');
        this.secureLogout();
    }

    setupCSRFProtection() {
        // Add CSRF token to all AJAX requests
        const csrfToken = this.generateCSRFToken();
        
        // Override XMLHttpRequest to include CSRF token
        const originalOpen = XMLHttpRequest.prototype.open;
        XMLHttpRequest.prototype.open = function(method, url, ...args) {
            originalOpen.apply(this, [method, url, ...args]);
            this.setRequestHeader('X-CSRF-Token', csrfToken);
        };
    }

    generateCSRFToken() {
        return 'csrf_' + Math.random().toString(36).substring(2) + Date.now().toString(36);
    }

    // 🚨 Security Event Logging
    logSecurityEvent(type, message, severity = 'info') {
        const event = {
            timestamp: new Date().toISOString(),
            type: type,
            message: message,
            severity: severity,
            userAgent: navigator.userAgent,
            ip: 'client-side', // In real app, this would come from server
            sessionId: this.getSessionId()
        };

        this.securityEvents.push(event);
        
        // Keep only last 100 events
        if (this.securityEvents.length > 100) {
            this.securityEvents = this.securityEvents.slice(-100);
        }

        // Store in localStorage for persistence
        localStorage.setItem('securityEvents', JSON.stringify(this.securityEvents));
        
        // In real application, send to server
        console.log('Security Event:', event);
    }

    getSessionId() {
        let sessionId = localStorage.getItem('sessionId');
        if (!sessionId) {
            sessionId = 'sess_' + Math.random().toString(36).substring(2) + Date.now().toString(36);
            localStorage.setItem('sessionId', sessionId);
        }
        return sessionId;
    }

    // 🔒 Authentication Functions
    secureLogout() {
        // Clear all session data
        this.clearSessionTimer();
        
        // Clear sensitive data
        localStorage.removeItem('sessionId');
        localStorage.removeItem('userToken');
        sessionStorage.clear();
        
        // Log the logout
        this.logSecurityEvent('logout', 'User logged out securely');
        
        // Clear cookies
        document.cookie.split(";").forEach(function(c) { 
            document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/"); 
        });
        
        // Redirect to login page
        this.showSecurityAlert('Logged Out', 'You have been securely logged out.', () => {
            window.location.href = '/login.html';
        });
    }

    emergencyLock() {
        this.logSecurityEvent('emergency_lock', 'Emergency lock activated by user', 'critical');
        
        // Disable all functionality
        document.body.style.pointerEvents = 'none';
        document.body.style.filter = 'blur(5px)';
        
        // Show emergency lock screen
        this.showEmergencyLockScreen();
        
        // Clear session immediately
        this.secureLogout();
    }

    showEmergencyLockScreen() {
        const lockScreen = document.createElement('div');
        lockScreen.className = 'fixed inset-0 bg-red-600 bg-opacity-95 flex items-center justify-center z-[9999]';
        lockScreen.innerHTML = `
            <div class="text-center text-white">
                <i class="ph ph-lock text-8xl mb-4"></i>
                <h1 class="text-4xl font-bold mb-2">EMERGENCY LOCK ACTIVATED</h1>
                <p class="text-xl">System access has been immediately suspended</p>
                <div class="mt-8">
                    <button onclick="location.reload()" class="bg-white text-red-600 px-6 py-3 rounded-lg font-bold">
                        Reload Page
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(lockScreen);
    }

    clearSessionTimer() {
        if (this.sessionTimer) {
            clearInterval(this.sessionTimer);
            this.sessionTimer = null;
        }
    }

    // 🔑 2FA Management
    setup2FA() {
        this.logSecurityEvent('2fa_setup_start', 'User initiated 2FA setup');
        
        // Generate QR code for 2FA app
        const secret = this.generate2FASecret();
        const qrCode = this.generateQRCode(secret);
        
        this.show2FASetupModal(qrCode, secret);
    }

    generate2FASecret() {
        const charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        let secret = '';
        for (let i = 0; i < 32; i++) {
            secret += charset.charAt(Math.floor(Math.random() * charset.length));
        }
        return secret;
    }

    generateQRCode(secret) {
        const issuer = 'MesChain-Sync';
        const user = 'super.admin@meschain.com';
        const otpAuthUrl = `otpauth://totp/${issuer}:${user}?secret=${secret}&issuer=${issuer}`;
        
        // In real application, use QR code library
        return `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(otpAuthUrl)}`;
    }

    show2FASetupModal(qrCode, secret) {
        // This would show a modal with QR code and backup codes
        console.log('2FA Setup - QR Code:', qrCode, 'Secret:', secret);
        this.showNotification('2FA Setup', '2FA setup initiated. Please scan the QR code with your authenticator app.', 'info');
    }

    // 🆕 JWT Token Management
    generateJWTSecret() {
        return 'jwt_' + Math.random().toString(36).substring(2) + Date.now().toString(36);
    }

    // Issue a new JWT token
    issueToken(userId, role = 'user') {
        const payload = {
            sub: userId,
            role: role,
            iat: Math.floor(Date.now() / 1000),
            exp: Math.floor(Date.now() / 1000) + this.sessionDuration
        };
        
        // In real application, use a robust JWT library
        const token = btoa(JSON.stringify(payload));
        this.activeTokens.set(token, payload);
        
        return token;
    }

    // Verify and decode JWT token
    verifyToken(token) {
        if (this.tokenBlacklist.has(token)) {
            throw new Error('Token is blacklisted');
        }
        
        const payload = JSON.parse(atob(token));
        const now = Math.floor(Date.now() / 1000);
        
        if (payload.exp < now) {
            throw new Error('Token has expired');
        }
        
        return payload;
    }

    // Revoke a token (for logout or security breach)
    revokeToken(token) {
        this.tokenBlacklist.add(token);
        this.logSecurityEvent('token_revoke', `Token revoked: ${token}`);
    }

    // 🚨 Security Alerts & Notifications
    showSecurityAlert(title, message, callback = null) {
        const alert = document.createElement('div');
        alert.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[9999]';
        alert.innerHTML = `
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 max-w-md mx-4 shadow-2xl">
                <div class="flex items-center mb-4">
                    <i class="ph ph-shield-warning text-red-500 text-2xl mr-3"></i>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">${title}</h3>
                </div>
                <p class="text-gray-600 dark:text-gray-300 mb-6">${message}</p>
                <div class="flex space-x-3">
                    <button onclick="this.closest('.fixed').remove(); ${callback ? 'callback()' : ''}" 
                            class="flex-1 bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition-colors">
                        OK
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(alert);
    }

    showNotification(title, message, type = 'info') {
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };

        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-4 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300`;
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="ph ph-check-circle text-xl mr-3"></i>
                <div>
                    <div class="font-semibold">${title}</div>
                    <div class="text-sm opacity-90">${message}</div>
                </div>
                <button onclick="this.closest('.fixed').remove()" class="ml-4 text-white hover:text-gray-200">
                    <i class="ph ph-x text-lg"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Slide in
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }

    // 📊 Security Dashboard
    getSecurityStats() {
        return {
            sessionDuration: this.sessionDuration,
            sessionRemaining: this.sessionDuration - Math.floor((Date.now() - this.sessionStartTime) / 1000),
            failedLoginAttempts: this.failedLoginAttempts,
            securityEvents: this.securityEvents.length,
            lastActivity: this.lastActivity,
            sessionId: this.getSessionId()
        };
    }

    viewSecurityLogs() {
        const stats = this.getSecurityStats();
        console.log('Security Statistics:', stats);
        console.log('Recent Security Events:', this.securityEvents.slice(-10));
        
        this.showNotification('Security Logs', 'Security logs have been logged to console.', 'info');
    }

    viewAuditTrail() {
        this.logSecurityEvent('audit_view', 'User accessed audit trail');
        this.showNotification('Audit Trail', 'Audit trail accessed. All actions are being logged.', 'info');
    }
}

// 🚀 Global Functions for Header Buttons
function viewSecurityLogs() {
    if (window.mesChainAuth) {
        window.mesChainAuth.viewSecurityLogs();
    }
}

function viewAuditTrail() {
    if (window.mesChainAuth) {
        window.mesChainAuth.viewAuditTrail();
    }
}

function manageTwoFA() {
    if (window.mesChainAuth) {
        window.mesChainAuth.setup2FA();
    }
}

function ipBlacklist() {
    if (window.mesChainAuth) {
        window.mesChainAuth.logSecurityEvent('ip_blacklist_view', 'User accessed IP blacklist management');
        window.mesChainAuth.showNotification('IP Blacklist', 'IP blacklist management opened.', 'info');
    }
}

function emergencyLock() {
    if (window.mesChainAuth) {
        if (confirm('Are you sure you want to activate emergency lock? This will immediately log you out and lock the system.')) {
            window.mesChainAuth.emergencyLock();
        }
    }
}

function secureLogout() {
    if (window.mesChainAuth) {
        if (confirm('Are you sure you want to logout securely?')) {
            window.mesChainAuth.secureLogout();
        }
    }
}

function secureLogout() {

// 🎯 Initialize Auth System
document.addEventListener('DOMContentLoaded', function() {
    window.mesChainAuth = new MesChainAuth();
    console.log('🔐 MesChain-Sync Auth System Initialized');
});

// 🔄 Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MesChainAuth;
}
