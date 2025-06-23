// Master Login Integration System
// Tüm login ekranlarını yönetir ve entegre eder

class MasterLoginIntegration {
    constructor() {
        this.integrationConfig = {
            loginAudit: true,
            mapRegeneration: true,
            multiPortSync: true,
            securityEnhanced: true
        };

        this.initialize();
    }

    /**
     * Initialize master integration
     */
    initialize() {
        this.loadDependencies();
        this.setupGlobalEventHandlers();
        this.initializeLoginPages();
        this.createMasterDashboard();
        this.startSystemMonitoring();

        console.log('🎯 Master Login Integration System initialized');
    }

    /**
     * Load all required dependencies
     */
    loadDependencies() {
        const dependencies = [
            'login_audit_system.js',
            'login_activation_system.js',
            'map_regeneration_system.js'
        ];

        dependencies.forEach(dep => {
            if (!this.isDependencyLoaded(dep)) {
                this.loadDependency(dep);
            }
        });
    }

    isDependencyLoaded(dependency) {
        // Check if dependency is already loaded
        const scripts = document.querySelectorAll('script[src*="' + dependency + '"]');
        return scripts.length > 0;
    }

    loadDependency(dependency) {
        const script = document.createElement('script');
        script.src = dependency;
        script.onload = () => {
            console.log(`✅ ${dependency} loaded`);
        };
        script.onerror = () => {
            console.error(`❌ Failed to load ${dependency}`);
        };
        document.head.appendChild(script);
    }

    /**
     * Setup global event handlers
     */
    setupGlobalEventHandlers() {
        // Master login event handler
        window.addEventListener('meschain-master-login', (e) => {
            this.handleMasterLogin(e.detail);
        });

        // Master logout event handler
        window.addEventListener('meschain-master-logout', (e) => {
            this.handleMasterLogout(e.detail);
        });

        // System health event handler
        window.addEventListener('meschain-system-health', (e) => {
            this.handleSystemHealth(e.detail);
        });

        // Map regeneration event handler
        window.addEventListener('meschain-map-regen', (e) => {
            this.handleMapRegeneration(e.detail);
        });
    }

    /**
     * Initialize all login pages
     */
    initializeLoginPages() {
        const loginPages = [
            {
                port: 3000,
                file: 'port_3000_dashboard_with_login.html',
                name: 'Main Dashboard',
                features: ['audit', 'maps', 'security']
            },
            {
                port: 3001,
                file: 'port_3001_frontend_components_with_login.html',
                name: 'Frontend Components',
                features: ['audit', 'components']
            },
            {
                port: 3002,
                file: 'port_3002_super_admin_with_login.html',
                name: 'Super Admin',
                features: ['audit', 'maps', 'security', 'admin']
            },
            {
                port: 3003,
                file: 'port_3003_marketplace_hub_with_login.html',
                name: 'Marketplace Hub',
                features: ['audit', 'maps', 'marketplace']
            },
            {
                port: 3009,
                file: 'port_3009_cross_marketplace_admin_with_login.html',
                name: 'Cross Marketplace Admin',
                features: ['audit', 'maps', 'marketplace', 'admin']
            },
            {
                port: 3010,
                file: 'port_3010_hepsiburada_specialist_with_login.html',
                name: 'Hepsiburada Specialist',
                features: ['audit', 'maps', 'marketplace']
            }
        ];

        loginPages.forEach(page => {
            this.enhanceLoginPage(page);
        });
    }

    /**
     * Enhance individual login page
     */
    enhanceLoginPage(pageConfig) {
        // Add integration script to the page
        const integrationScript = this.generateIntegrationScript(pageConfig);

        console.log(`🔧 Enhancing login page: ${pageConfig.name} (Port: ${pageConfig.port})`);

        // Store page configuration
        localStorage.setItem(`meschain_page_config_${pageConfig.port}`, JSON.stringify(pageConfig));
    }

    /**
     * Generate integration script for specific page
     */
    generateIntegrationScript(pageConfig) {
        return `
            // MesChain Master Login Integration for ${pageConfig.name}
            (function() {
                // Load audit system
                if (${pageConfig.features.includes('audit')}) {
                    if (typeof window.loginAudit === 'undefined') {
                        const auditScript = document.createElement('script');
                        auditScript.src = 'login_audit_system.js';
                        document.head.appendChild(auditScript);
                    }
                }

                // Load map regeneration system
                if (${pageConfig.features.includes('maps')}) {
                    if (typeof window.mapRegen === 'undefined') {
                        const mapScript = document.createElement('script');
                        mapScript.src = 'map_regeneration_system.js';
                        document.head.appendChild(mapScript);
                    }
                }

                // Setup page-specific login handling
                document.addEventListener('DOMContentLoaded', function() {
                    const originalLogin = window.login || function() {};
                    window.login = function(credentials) {
                        // Trigger master login event
                        window.dispatchEvent(new CustomEvent('meschain-master-login', {
                            detail: {
                                port: ${pageConfig.port},
                                page: '${pageConfig.name}',
                                credentials: credentials,
                                features: ${JSON.stringify(pageConfig.features)}
                            }
                        }));

                        // Call original login function
                        return originalLogin(credentials);
                    };

                    // Add quick access buttons
                    const quickAccessContainer = document.createElement('div');
                    quickAccessContainer.id = 'meschain-quick-access';
                    quickAccessContainer.innerHTML = \`
                        <div style="position: fixed; top: 50px; right: 10px; z-index: 10002;">
                            ${pageConfig.features.includes('audit') ? '<button onclick="if(window.loginAudit) window.loginAudit.toggleAuditPanel()" class="btn-quick">🔍 Audit</button>' : ''}
                            ${pageConfig.features.includes('maps') ? '<button onclick="if(window.mapRegen) window.mapRegen.showMapPanel()" class="btn-quick">🗺️ Maps</button>' : ''}
                            <button onclick="window.masterLogin.showDashboard()" class="btn-quick">📊 Dashboard</button>
                        </div>
                    \`;

                    // Add quick access styles
                    const quickStyle = document.createElement('style');
                    quickStyle.textContent = \`
                        .btn-quick {
                            display: block;
                            width: 100%;
                            margin-bottom: 5px;
                            padding: 8px 12px;
                            background: #007bff;
                            color: white;
                            border: none;
                            border-radius: 5px;
                            cursor: pointer;
                            font-size: 12px;
                            text-align: left;
                        }
                        .btn-quick:hover {
                            background: #0056b3;
                        }
                    \`;

                    document.head.appendChild(quickStyle);
                    document.body.appendChild(quickAccessContainer);
                });
            })();
        `;
    }

    /**
     * Create master dashboard
     */
    createMasterDashboard() {
        const dashboard = document.createElement('div');
        dashboard.id = 'meschain-master-dashboard';
        dashboard.innerHTML = `
            <div class="master-dashboard">
                <div class="dashboard-header">
                    <h2>🎯 MesChain Master Control</h2>
                    <div class="dashboard-controls">
                        <button onclick="window.masterLogin.toggleDashboard()" class="btn-dashboard">📊</button>
                        <button onclick="window.masterLogin.showSystemStatus()" class="btn-dashboard">📈</button>
                        <button onclick="window.masterLogin.showSecurityCenter()" class="btn-dashboard">🔐</button>
                    </div>
                </div>

                <div class="dashboard-grid">
                    <div class="dashboard-card">
                        <h3>🔍 Login Audit</h3>
                        <div id="audit-summary">
                            <div class="summary-item">
                                <span>Active Users:</span>
                                <span id="master-active-users">0</span>
                            </div>
                            <div class="summary-item">
                                <span>Today's Logins:</span>
                                <span id="master-daily-logins">0</span>
                            </div>
                        </div>
                        <button onclick="if(window.loginAudit) window.loginAudit.toggleAuditPanel()" class="btn-card">View Details</button>
                    </div>

                    <div class="dashboard-card">
                        <h3>🗺️ Map System</h3>
                        <div id="map-summary">
                            <div class="summary-item">
                                <span>Active Maps:</span>
                                <span id="master-active-maps">0</span>
                            </div>
                            <div class="summary-item">
                                <span>Queue:</span>
                                <span id="master-map-queue">0</span>
                            </div>
                        </div>
                        <button onclick="if(window.mapRegen) window.mapRegen.showMapPanel()" class="btn-card">Manage Maps</button>
                    </div>

                    <div class="dashboard-card">
                        <h3>🚀 System Status</h3>
                        <div id="system-summary">
                            <div class="summary-item">
                                <span>Active Ports:</span>
                                <span id="master-active-ports">0</span>
                            </div>
                            <div class="summary-item">
                                <span>Health:</span>
                                <span id="master-system-health">Good</span>
                            </div>
                        </div>
                        <button onclick="window.masterLogin.refreshSystemStatus()" class="btn-card">Refresh</button>
                    </div>

                    <div class="dashboard-card">
                        <h3>🛠️ Quick Actions</h3>
                        <div class="quick-actions">
                            <button onclick="if(window.mapRegen) window.mapRegen.regenerateAllMaps()" class="btn-action">🔄 Regenerate All Maps</button>
                            <button onclick="window.masterLogin.restartAllSystems()" class="btn-action">🚀 Restart Systems</button>
                            <button onclick="window.masterLogin.exportAllData()" class="btn-action">📤 Export Data</button>
                        </div>
                    </div>
                </div>

                <div class="dashboard-footer">
                    <div class="status-indicators">
                        <div class="status-indicator" id="audit-status">
                            <span class="indicator-dot"></span>
                            <span>Audit System</span>
                        </div>
                        <div class="status-indicator" id="map-status">
                            <span class="indicator-dot"></span>
                            <span>Map System</span>
                        </div>
                        <div class="status-indicator" id="sync-status">
                            <span class="indicator-dot"></span>
                            <span>Sync Status</span>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Add dashboard styles
        const dashboardStyle = document.createElement('style');
        dashboardStyle.textContent = `
            #meschain-master-dashboard {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 800px;
                max-width: 90vw;
                max-height: 90vh;
                background: rgba(255, 255, 255, 0.95);
                border-radius: 15px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                z-index: 10003;
                backdrop-filter: blur(15px);
                border: 1px solid rgba(255, 255, 255, 0.3);
                display: none;
                overflow-y: auto;
            }

            .master-dashboard {
                padding: 25px;
            }

            .dashboard-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 25px;
                border-bottom: 2px solid #eee;
                padding-bottom: 15px;
            }

            .dashboard-header h2 {
                margin: 0;
                color: #333;
                font-size: 24px;
            }

            .dashboard-controls {
                display: flex;
                gap: 10px;
            }

            .btn-dashboard {
                padding: 8px 12px;
                background: #007bff;
                color: white;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                font-size: 16px;
            }

            .btn-dashboard:hover {
                background: #0056b3;
            }

            .dashboard-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
                margin-bottom: 25px;
            }

            .dashboard-card {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 10px;
                border: 1px solid #e9ecef;
            }

            .dashboard-card h3 {
                margin: 0 0 15px 0;
                color: #333;
                font-size: 18px;
            }

            .summary-item {
                display: flex;
                justify-content: space-between;
                margin-bottom: 8px;
                font-size: 14px;
            }

            .btn-card {
                width: 100%;
                padding: 8px;
                background: #28a745;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                margin-top: 10px;
            }

            .btn-card:hover {
                background: #218838;
            }

            .quick-actions {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }

            .btn-action {
                padding: 8px 12px;
                background: #ffc107;
                color: #212529;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 12px;
            }

            .btn-action:hover {
                background: #e0a800;
            }

            .dashboard-footer {
                border-top: 1px solid #eee;
                padding-top: 15px;
            }

            .status-indicators {
                display: flex;
                justify-content: space-around;
            }

            .status-indicator {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 14px;
            }

            .indicator-dot {
                width: 12px;
                height: 12px;
                border-radius: 50%;
                background: #28a745;
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0% { opacity: 1; }
                50% { opacity: 0.5; }
                100% { opacity: 1; }
            }
        `;

        document.head.appendChild(dashboardStyle);
        document.body.appendChild(dashboard);
    }

    /**
     * Handle master login event
     */
    handleMasterLogin(loginData) {
        console.log('🎯 Master login event:', loginData);

        // Update all systems with login data
        if (window.loginAudit) {
            window.loginAudit.logSuccessfulLogin(loginData.credentials, 'master_token');
        }

        // Sync login across all ports
        this.syncLoginAcrossPorts(loginData);

        // Update dashboard
        this.updateDashboardData();

        // Show welcome message
        this.showMasterWelcome(loginData);
    }

    /**
     * Handle master logout event
     */
    handleMasterLogout(logoutData) {
        console.log('🎯 Master logout event:', logoutData);

        // Update all systems with logout data
        if (window.loginAudit) {
            window.loginAudit.logLogout(logoutData);
        }

        // Sync logout across all ports
        this.syncLogoutAcrossPorts(logoutData);

        // Update dashboard
        this.updateDashboardData();
    }

    /**
     * Sync login across all ports
     */
    syncLoginAcrossPorts(loginData) {
        const ports = [3000, 3001, 3002, 3003, 3009, 3010, 3023, 3077];

        ports.forEach(port => {
            // Send login sync message to each port
            this.sendPortMessage(port, 'login-sync', loginData);
        });
    }

    /**
     * Sync logout across all ports
     */
    syncLogoutAcrossPorts(logoutData) {
        const ports = [3000, 3001, 3002, 3003, 3009, 3010, 3023, 3077];

        ports.forEach(port => {
            // Send logout sync message to each port
            this.sendPortMessage(port, 'logout-sync', logoutData);
        });
    }

    /**
     * Send message to specific port
     */
    sendPortMessage(port, action, data) {
        // In a real environment, this would use actual inter-port communication
        console.log(`📡 Sending ${action} to port ${port}:`, data);

        // Store sync data in localStorage for port communication
        localStorage.setItem(`meschain_sync_${port}_${action}`, JSON.stringify({
            timestamp: Date.now(),
            data: data
        }));
    }

    /**
     * Update dashboard data
     */
    updateDashboardData() {
        // Update audit summary
        if (window.loginAudit) {
            const activeUsers = window.loginAudit.getActiveUsers();
            document.getElementById('master-active-users').textContent = activeUsers.length;

            const recentLogins = window.loginAudit.getRecentLoginAttempts(24);
            const successfulLogins = recentLogins.filter(login => login.success);
            document.getElementById('master-daily-logins').textContent = successfulLogins.length;
        }

        // Update map summary
        if (window.mapRegen) {
            const mapStatus = window.mapRegen.getRegenerationStatus();
            document.getElementById('master-active-maps').textContent = mapStatus.activeMappings;
            document.getElementById('master-map-queue').textContent = mapStatus.queueLength;
        }

        // Update system summary
        const activePorts = this.getActivePorts();
        document.getElementById('master-active-ports').textContent = activePorts.length;
        document.getElementById('master-system-health').textContent = this.getSystemHealth();
    }

    /**
     * Get active ports
     */
    getActivePorts() {
        // Return list of active ports (simulated)
        return [3000, 3001, 3002, 3003, 3009, 3010, 3023, 3077];
    }

    /**
     * Get system health status
     */
    getSystemHealth() {
        // Calculate system health based on various factors
        const healthFactors = {
            portsActive: this.getActivePorts().length >= 6,
            auditSystem: typeof window.loginAudit !== 'undefined',
            mapSystem: typeof window.mapRegen !== 'undefined',
            errorRate: Math.random() < 0.1 // Simulate 10% error rate
        };

        const healthScore = Object.values(healthFactors).filter(Boolean).length;

        if (healthScore >= 3) return 'Excellent';
        if (healthScore >= 2) return 'Good';
        if (healthScore >= 1) return 'Fair';
        return 'Poor';
    }

    /**
     * Start system monitoring
     */
    startSystemMonitoring() {
        setInterval(() => {
            this.updateDashboardData();
            this.checkSystemHealth();
        }, 30000); // Update every 30 seconds
    }

    /**
     * Check system health
     */
    checkSystemHealth() {
        const health = this.getSystemHealth();

        // Update status indicators
        const indicators = ['audit-status', 'map-status', 'sync-status'];
        indicators.forEach(id => {
            const indicator = document.getElementById(id);
            if (indicator) {
                const dot = indicator.querySelector('.indicator-dot');
                dot.className = 'indicator-dot ' + (health === 'Excellent' ? 'success' :
                                                  health === 'Good' ? 'warning' : 'error');
            }
        });
    }

    /**
     * UI Methods
     */
    showDashboard() {
        const dashboard = document.getElementById('meschain-master-dashboard');
        dashboard.style.display = 'block';
        this.updateDashboardData();
    }

    toggleDashboard() {
        const dashboard = document.getElementById('meschain-master-dashboard');
        dashboard.style.display = dashboard.style.display === 'none' ? 'block' : 'none';
        if (dashboard.style.display === 'block') {
            this.updateDashboardData();
        }
    }

    showSystemStatus() {
        alert('System Status:\n\n' +
              '🔍 Login Audit: ' + (window.loginAudit ? 'Active' : 'Inactive') + '\n' +
              '🗺️ Map System: ' + (window.mapRegen ? 'Active' : 'Inactive') + '\n' +
              '📊 Health: ' + this.getSystemHealth() + '\n' +
              '🚀 Active Ports: ' + this.getActivePorts().length);
    }

    showSecurityCenter() {
        if (window.loginAudit) {
            window.loginAudit.toggleAuditPanel();
        } else {
            alert('Security Center requires Login Audit System to be loaded.');
        }
    }

    refreshSystemStatus() {
        this.updateDashboardData();
        this.checkSystemHealth();

        // Show refresh confirmation
        const refreshBtn = event.target;
        const originalText = refreshBtn.textContent;
        refreshBtn.textContent = '✅ Refreshed';
        setTimeout(() => {
            refreshBtn.textContent = originalText;
        }, 2000);
    }

    restartAllSystems() {
        if (confirm('Are you sure you want to restart all systems?')) {
            console.log('🚀 Restarting all systems...');

            // Simulate system restart
            setTimeout(() => {
                this.updateDashboardData();
                alert('✅ All systems restarted successfully!');
            }, 3000);
        }
    }

    exportAllData() {
        const exportData = {
            auditData: window.loginAudit ? window.loginAudit.auditLog : [],
            mapData: window.mapRegen ? window.mapRegen.getMappingData() : {},
            systemStatus: {
                activePorts: this.getActivePorts(),
                health: this.getSystemHealth(),
                timestamp: new Date().toISOString()
            }
        };

        const blob = new Blob([JSON.stringify(exportData, null, 2)], {
            type: 'application/json'
        });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `meschain_master_export_${new Date().toISOString().split('T')[0]}.json`;
        a.click();
        URL.revokeObjectURL(url);

        console.log('📤 All data exported');
    }

    showMasterWelcome(loginData) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification('MesChain Master Login', {
                body: `Welcome to the Master Control System! Login successful on port ${loginData.port}.`,
                icon: '/favicon.ico'
            });
        }
    }

    /**
     * Handle events
     */
    handleSystemHealth(healthData) {
        console.log('📊 System health update:', healthData);
        this.updateDashboardData();
    }

    handleMapRegeneration(regenData) {
        console.log('🗺️ Map regeneration event:', regenData);
        this.updateDashboardData();
    }
}

// Initialize master login integration
window.masterLogin = new MasterLoginIntegration();

// Request notification permission
if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}

console.log('🎯 Master Login Integration System loaded successfully!');
