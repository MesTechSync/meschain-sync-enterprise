// Login Audit & Tracking System
// Bu sistem tüm login ekranlarını aktif hale getirir ve kullanıcı girişlerini izler

class LoginAuditSystem {
    constructor() {
        this.auditLog = [];
        this.activeUsers = new Map();
        this.loginSessions = new Map();
        this.maxAuditEntries = 1000;

        this.initializeAuditSystem();
        this.startSessionMonitoring();

        console.log('🔐 Login Audit System initialized');
    }

    /**
     * Initialize the audit system
     */
    initializeAuditSystem() {
        this.createAuditUI();
        this.setupEventListeners();
        this.loadExistingAuditData();
        this.startAuditCleanup();
    }

    /**
     * Log login attempt
     */
    logLoginAttempt(userData) {
        const auditEntry = {
            id: this.generateAuditId(),
            timestamp: new Date().toISOString(),
            type: 'LOGIN_ATTEMPT',
            username: userData.username,
            success: false,
            ipAddress: this.getClientIP(),
            userAgent: navigator.userAgent,
            port: this.getCurrentPort(),
            sessionId: this.generateSessionId(),
            details: {
                rememberMe: userData.rememberMe || false,
                loginMethod: userData.loginMethod || 'standard',
                previousLoginTime: this.getLastLoginTime(userData.username)
            }
        };

        this.auditLog.push(auditEntry);
        this.updateAuditUI();
        this.saveAuditData();

        console.log('🔍 Login attempt logged:', auditEntry);
        return auditEntry;
    }

    /**
     * Log successful login
     */
    logSuccessfulLogin(userData, token) {
        const auditEntry = {
            id: this.generateAuditId(),
            timestamp: new Date().toISOString(),
            type: 'LOGIN_SUCCESS',
            username: userData.username,
            success: true,
            ipAddress: this.getClientIP(),
            userAgent: navigator.userAgent,
            port: this.getCurrentPort(),
            sessionId: this.generateSessionId(),
            token: token ? token.substring(0, 10) + '...' : null,
            details: {
                role: userData.role || 'user',
                department: userData.department || 'unknown',
                lastLoginTime: userData.lastLoginTime,
                sessionDuration: 0,
                loginDuration: Date.now() - (userData.loginStartTime || Date.now())
            }
        };

        this.auditLog.push(auditEntry);
        this.activeUsers.set(userData.username, auditEntry);
        this.loginSessions.set(auditEntry.sessionId, auditEntry);

        this.updateAuditUI();
        this.saveAuditData();
        this.sendLoginNotification(auditEntry);

        console.log('✅ Successful login logged:', auditEntry);
        return auditEntry;
    }

    /**
     * Log logout
     */
    logLogout(userData) {
        const auditEntry = {
            id: this.generateAuditId(),
            timestamp: new Date().toISOString(),
            type: 'LOGOUT',
            username: userData.username,
            success: true,
            ipAddress: this.getClientIP(),
            port: this.getCurrentPort(),
            sessionId: userData.sessionId,
            details: {
                sessionDuration: this.calculateSessionDuration(userData.sessionId),
                logoutReason: userData.logoutReason || 'user_initiated'
            }
        };

        this.auditLog.push(auditEntry);
        this.activeUsers.delete(userData.username);
        this.loginSessions.delete(userData.sessionId);

        this.updateAuditUI();
        this.saveAuditData();

        console.log('👋 Logout logged:', auditEntry);
        return auditEntry;
    }

    /**
     * Get active users
     */
    getActiveUsers() {
        return Array.from(this.activeUsers.values());
    }

    /**
     * Get login history for a user
     */
    getUserLoginHistory(username, limit = 50) {
        return this.auditLog
            .filter(entry => entry.username === username)
            .sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp))
            .slice(0, limit);
    }

    /**
     * Get recent login attempts
     */
    getRecentLoginAttempts(hours = 24) {
        const cutoffTime = new Date(Date.now() - (hours * 60 * 60 * 1000));
        return this.auditLog
            .filter(entry => new Date(entry.timestamp) > cutoffTime)
            .sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
    }

    /**
     * Create audit UI
     */
    createAuditUI() {
        // Create audit panel if it doesn't exist
        if (!document.getElementById('login-audit-panel')) {
            const auditPanel = document.createElement('div');
            auditPanel.id = 'login-audit-panel';
            auditPanel.innerHTML = `
                <div class="audit-panel">
                    <div class="audit-header">
                        <h3>🔐 Login Audit System</h3>
                        <div class="audit-controls">
                            <button onclick="loginAudit.toggleAuditPanel()" class="btn-audit">📊 Panel</button>
                            <button onclick="loginAudit.exportAuditData()" class="btn-audit">📥 Export</button>
                            <button onclick="loginAudit.clearAuditData()" class="btn-audit">🗑️ Clear</button>
                        </div>
                    </div>
                    <div class="audit-stats">
                        <div class="stat-item">
                            <span class="stat-label">Active Users:</span>
                            <span id="active-users-count" class="stat-value">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Total Logins Today:</span>
                            <span id="daily-logins-count" class="stat-value">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Failed Attempts:</span>
                            <span id="failed-logins-count" class="stat-value">0</span>
                        </div>
                    </div>
                    <div id="audit-log-container" class="audit-log">
                        <div class="audit-log-header">Recent Login Activity</div>
                        <div id="audit-log-entries"></div>
                    </div>
                </div>
            `;

            // Add CSS styles
            const style = document.createElement('style');
            style.textContent = `
                #login-audit-panel {
                    position: fixed;
                    top: 10px;
                    right: 10px;
                    width: 350px;
                    background: rgba(255, 255, 255, 0.95);
                    border-radius: 10px;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
                    z-index: 10000;
                    max-height: 80vh;
                    overflow-y: auto;
                    backdrop-filter: blur(10px);
                    border: 1px solid rgba(255, 255, 255, 0.3);
                }

                .audit-panel {
                    padding: 15px;
                }

                .audit-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 15px;
                    border-bottom: 1px solid #eee;
                    padding-bottom: 10px;
                }

                .audit-header h3 {
                    margin: 0;
                    color: #333;
                    font-size: 16px;
                }

                .audit-controls {
                    display: flex;
                    gap: 5px;
                }

                .btn-audit {
                    padding: 5px 10px;
                    background: #667eea;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    font-size: 12px;
                }

                .btn-audit:hover {
                    background: #5a6fd8;
                }

                .audit-stats {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 10px;
                    margin-bottom: 15px;
                }

                .stat-item {
                    background: #f8f9fa;
                    padding: 8px;
                    border-radius: 5px;
                    text-align: center;
                    font-size: 12px;
                }

                .stat-label {
                    display: block;
                    color: #666;
                    margin-bottom: 2px;
                }

                .stat-value {
                    display: block;
                    color: #333;
                    font-weight: bold;
                    font-size: 16px;
                }

                .audit-log {
                    max-height: 300px;
                    overflow-y: auto;
                }

                .audit-log-header {
                    font-weight: bold;
                    margin-bottom: 10px;
                    color: #333;
                    font-size: 14px;
                }

                .audit-entry {
                    padding: 8px;
                    margin-bottom: 5px;
                    border-radius: 5px;
                    font-size: 11px;
                    border-left: 3px solid #ddd;
                }

                .audit-entry.success {
                    background: #d4edda;
                    border-left-color: #28a745;
                }

                .audit-entry.failure {
                    background: #f8d7da;
                    border-left-color: #dc3545;
                }

                .audit-entry.logout {
                    background: #fff3cd;
                    border-left-color: #ffc107;
                }

                .audit-entry-time {
                    color: #666;
                    font-weight: bold;
                }

                .audit-entry-user {
                    color: #333;
                    font-weight: bold;
                }

                .audit-entry-details {
                    color: #666;
                    margin-top: 2px;
                }
            `;

            document.head.appendChild(style);
            document.body.appendChild(auditPanel);
        }
    }

    /**
     * Update audit UI
     */
    updateAuditUI() {
        // Update stats
        document.getElementById('active-users-count').textContent = this.activeUsers.size;

        const today = new Date().toDateString();
        const dailyLogins = this.auditLog.filter(entry =>
            entry.type === 'LOGIN_SUCCESS' &&
            new Date(entry.timestamp).toDateString() === today
        ).length;
        document.getElementById('daily-logins-count').textContent = dailyLogins;

        const failedLogins = this.auditLog.filter(entry =>
            entry.type === 'LOGIN_ATTEMPT' &&
            !entry.success &&
            new Date(entry.timestamp).toDateString() === today
        ).length;
        document.getElementById('failed-logins-count').textContent = failedLogins;

        // Update log entries
        const logContainer = document.getElementById('audit-log-entries');
        const recentEntries = this.auditLog.slice(-20).reverse();

        logContainer.innerHTML = recentEntries.map(entry => {
            const time = new Date(entry.timestamp).toLocaleTimeString();
            const typeClass = entry.type === 'LOGIN_SUCCESS' ? 'success' :
                             entry.type === 'LOGOUT' ? 'logout' : 'failure';
            const icon = entry.type === 'LOGIN_SUCCESS' ? '✅' :
                        entry.type === 'LOGOUT' ? '👋' : '❌';

            return `
                <div class="audit-entry ${typeClass}">
                    <div class="audit-entry-time">${icon} ${time}</div>
                    <div class="audit-entry-user">${entry.username} @ Port ${entry.port}</div>
                    <div class="audit-entry-details">${entry.type} - ${entry.ipAddress}</div>
                </div>
            `;
        }).join('');
    }

    /**
     * Helper functions
     */
    generateAuditId() {
        return 'audit_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    generateSessionId() {
        return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    getClientIP() {
        // In a real environment, this would get the actual IP
        return '192.168.1.' + Math.floor(Math.random() * 255);
    }

    getCurrentPort() {
        return window.location.port || '80';
    }

    getLastLoginTime(username) {
        const userLogins = this.auditLog.filter(entry =>
            entry.username === username && entry.type === 'LOGIN_SUCCESS'
        );
        return userLogins.length > 0 ? userLogins[userLogins.length - 1].timestamp : null;
    }

    calculateSessionDuration(sessionId) {
        const session = this.loginSessions.get(sessionId);
        if (session) {
            return Date.now() - new Date(session.timestamp).getTime();
        }
        return 0;
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Listen for login events
        document.addEventListener('login-attempt', (e) => {
            this.logLoginAttempt(e.detail);
        });

        document.addEventListener('login-success', (e) => {
            this.logSuccessfulLogin(e.detail.user, e.detail.token);
        });

        document.addEventListener('logout', (e) => {
            this.logLogout(e.detail);
        });

        // Monitor page visibility changes
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.pauseSessionMonitoring();
            } else {
                this.resumeSessionMonitoring();
            }
        });
    }

    /**
     * Start session monitoring
     */
    startSessionMonitoring() {
        setInterval(() => {
            this.updateSessionDurations();
            this.checkForInactiveSessions();
        }, 60000); // Check every minute
    }

    updateSessionDurations() {
        this.loginSessions.forEach((session, sessionId) => {
            session.details.sessionDuration = this.calculateSessionDuration(sessionId);
        });
    }

    checkForInactiveSessions() {
        const inactiveThreshold = 30 * 60 * 1000; // 30 minutes
        this.loginSessions.forEach((session, sessionId) => {
            if (session.details.sessionDuration > inactiveThreshold) {
                // Auto-logout inactive sessions
                this.logLogout({
                    username: session.username,
                    sessionId: sessionId,
                    logoutReason: 'auto_timeout'
                });
            }
        });
    }

    /**
     * Save audit data
     */
    saveAuditData() {
        try {
            localStorage.setItem('meschain_login_audit', JSON.stringify({
                auditLog: this.auditLog.slice(-this.maxAuditEntries),
                activeUsers: Array.from(this.activeUsers.entries()),
                loginSessions: Array.from(this.loginSessions.entries()),
                lastUpdated: new Date().toISOString()
            }));
        } catch (error) {
            console.warn('Failed to save audit data:', error);
        }
    }

    /**
     * Load existing audit data
     */
    loadExistingAuditData() {
        try {
            const savedData = localStorage.getItem('meschain_login_audit');
            if (savedData) {
                const data = JSON.parse(savedData);
                this.auditLog = data.auditLog || [];
                this.activeUsers = new Map(data.activeUsers || []);
                this.loginSessions = new Map(data.loginSessions || []);

                console.log('📂 Loaded existing audit data');
                this.updateAuditUI();
            }
        } catch (error) {
            console.warn('Failed to load audit data:', error);
        }
    }

    /**
     * Export audit data
     */
    exportAuditData() {
        const exportData = {
            auditLog: this.auditLog,
            activeUsers: Array.from(this.activeUsers.entries()),
            exportTime: new Date().toISOString(),
            totalEntries: this.auditLog.length
        };

        const blob = new Blob([JSON.stringify(exportData, null, 2)], {
            type: 'application/json'
        });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `meschain_login_audit_${new Date().toISOString().split('T')[0]}.json`;
        a.click();
        URL.revokeObjectURL(url);

        console.log('📥 Audit data exported');
    }

    /**
     * Clear audit data
     */
    clearAuditData() {
        if (confirm('Are you sure you want to clear all audit data?')) {
            this.auditLog = [];
            this.activeUsers.clear();
            this.loginSessions.clear();
            this.saveAuditData();
            this.updateAuditUI();
            console.log('🗑️ Audit data cleared');
        }
    }

    /**
     * Toggle audit panel visibility
     */
    toggleAuditPanel() {
        const panel = document.getElementById('login-audit-panel');
        panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    }

    /**
     * Send login notification
     */
    sendLoginNotification(auditEntry) {
        // Create notification
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification('New Login Detected', {
                body: `${auditEntry.username} logged in from ${auditEntry.ipAddress}`,
                icon: '/favicon.ico'
            });
        }
    }

    /**
     * Start audit cleanup
     */
    startAuditCleanup() {
        // Clean up old entries daily
        setInterval(() => {
            const cutoffTime = new Date(Date.now() - (30 * 24 * 60 * 60 * 1000)); // 30 days
            this.auditLog = this.auditLog.filter(entry =>
                new Date(entry.timestamp) > cutoffTime
            );
            this.saveAuditData();
        }, 24 * 60 * 60 * 1000); // Daily cleanup
    }

    /**
     * Generate login report
     */
    generateLoginReport(days = 7) {
        const cutoffTime = new Date(Date.now() - (days * 24 * 60 * 60 * 1000));
        const recentEntries = this.auditLog.filter(entry =>
            new Date(entry.timestamp) > cutoffTime
        );

        const report = {
            period: `${days} days`,
            totalLogins: recentEntries.filter(e => e.type === 'LOGIN_SUCCESS').length,
            failedAttempts: recentEntries.filter(e => e.type === 'LOGIN_ATTEMPT' && !e.success).length,
            uniqueUsers: [...new Set(recentEntries.map(e => e.username))].length,
            mostActiveUser: this.getMostActiveUser(recentEntries),
            peakHours: this.getPeakLoginHours(recentEntries),
            topPorts: this.getTopLoginPorts(recentEntries)
        };

        console.log('📊 Login Report Generated:', report);
        return report;
    }

    getMostActiveUser(entries) {
        const userCounts = {};
        entries.forEach(entry => {
            if (entry.type === 'LOGIN_SUCCESS') {
                userCounts[entry.username] = (userCounts[entry.username] || 0) + 1;
            }
        });

        return Object.keys(userCounts).reduce((a, b) =>
            userCounts[a] > userCounts[b] ? a : b, null
        );
    }

    getPeakLoginHours(entries) {
        const hourCounts = {};
        entries.forEach(entry => {
            if (entry.type === 'LOGIN_SUCCESS') {
                const hour = new Date(entry.timestamp).getHours();
                hourCounts[hour] = (hourCounts[hour] || 0) + 1;
            }
        });

        return Object.keys(hourCounts).reduce((a, b) =>
            hourCounts[a] > hourCounts[b] ? a : b, null
        );
    }

    getTopLoginPorts(entries) {
        const portCounts = {};
        entries.forEach(entry => {
            if (entry.type === 'LOGIN_SUCCESS') {
                portCounts[entry.port] = (portCounts[entry.port] || 0) + 1;
            }
        });

        return Object.keys(portCounts)
            .sort((a, b) => portCounts[b] - portCounts[a])
            .slice(0, 5);
    }
}

// Initialize the login audit system
window.loginAudit = new LoginAuditSystem();

// Request notification permission
if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}

console.log('🔐 Login Audit & Tracking System loaded successfully!');
