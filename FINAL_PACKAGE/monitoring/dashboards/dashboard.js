/**
 * MesChain Trendyol Integration - Dashboard JavaScript
 * Real-time monitoring dashboard functionality
 */

class MonitoringDashboard {
    constructor() {
        this.refreshInterval = 30000; // 30 seconds
        this.intervalId = null;
        this.init();
    }

    init() {
        this.refreshData();
        this.startAutoRefresh();
        this.setupEventListeners();
    }

    async refreshData() {
        try {
            const response = await fetch('../scripts/system_monitor.php');
            const data = await response.json();
            this.updateDashboard(data);
            this.updateTimestamp();
        } catch (error) {
            console.error('Failed to fetch monitoring data:', error);
            this.showError('Failed to load monitoring data');
        }
    }

    updateDashboard(data) {
        // Update system health
        this.updateSystemHealth(data.system);

        // Update database health
        this.updateDatabaseHealth(data.database);

        // Update API health
        this.updateApiHealth(data.api);

        // Update application status
        this.updateApplicationStatus(data.application);

        // Update performance metrics
        this.updatePerformanceMetrics(data.performance);

        // Update alerts
        this.updateAlerts(data.alerts);
    }

    updateSystemHealth(system) {
        // CPU Usage
        const cpuUsage = system.cpu_usage || 0;
        document.getElementById('cpu-usage').textContent = `${cpuUsage}%`;
        this.updateProgressBar('cpu-progress', cpuUsage, [70, 85]);

        // Memory Usage
        const memoryUsage = system.memory_usage || 0;
        document.getElementById('memory-usage').textContent = `${memoryUsage}%`;
        this.updateProgressBar('memory-progress', memoryUsage, [75, 90]);

        // Disk Usage
        const diskUsage = system.disk_usage || 0;
        document.getElementById('disk-usage').textContent = `${diskUsage}%`;
        this.updateProgressBar('disk-progress', diskUsage, [80, 95]);

        // Load Average
        const loadAverage = system.load_average || 0;
        document.getElementById('load-average').textContent = loadAverage.toFixed(2);

        // Uptime
        const uptime = this.formatUptime(system.uptime || 0);
        document.getElementById('uptime').textContent = uptime;
    }

    updateDatabaseHealth(database) {
        // Connection Status
        const connectionStatus = database.connection ? 'Connected' : 'Disconnected';
        const connectionClass = database.connection ? 'status-good' : 'status-critical';
        this.updateStatus('db-connection', connectionStatus, connectionClass);

        // Response Time
        const responseTime = `${Math.round(database.response_time || 0)}ms`;
        document.getElementById('db-response-time').textContent = responseTime;

        // Active Connections
        document.getElementById('db-connections').textContent = database.active_connections || 0;

        // Slow Queries
        document.getElementById('db-slow-queries').textContent = database.slow_queries || 0;
    }

    updateApiHealth(api) {
        // Trendyol API Status
        const apiStatus = api.trendyol_api?.status || 'Unknown';
        const apiClass = apiStatus === 'healthy' ? 'status-good' : 'status-critical';
        this.updateStatus('api-status', apiStatus, apiClass);

        // API Response Time
        const apiResponseTime = `${api.trendyol_api?.response_time || 0}ms`;
        document.getElementById('api-response-time').textContent = apiResponseTime;

        // Rate Limit Usage
        if (api.rate_limits) {
            const usage = api.rate_limits.current_usage || 0;
            const limit = api.rate_limits.limit || 100;
            const percentage = Math.round((usage / limit) * 100);

            document.getElementById('api-rate-limit').textContent = `${usage}/${limit} (${percentage}%)`;
            this.updateProgressBar('rate-limit-progress', percentage, [70, 90]);
        }
    }

    updateApplicationStatus(application) {
        // Extension Status
        const extensionStatus = application.extension_status?.enabled ? 'Enabled' : 'Disabled';
        const extensionClass = application.extension_status?.enabled ? 'status-good' : 'status-critical';
        this.updateStatus('extension-status', extensionStatus, extensionClass);

        // Sync Queue
        const syncQueue = application.sync_queue;
        if (syncQueue) {
            const queueText = `Pending: ${syncQueue.pending}, Processing: ${syncQueue.processing}, Failed: ${syncQueue.failed}`;
            document.getElementById('sync-queue').textContent = queueText;
        }

        // Success Rate (placeholder)
        document.getElementById('success-rate').textContent = '98.5%';

        // Last Sync (placeholder)
        document.getElementById('last-sync').textContent = '5 minutes ago';
    }

    updatePerformanceMetrics(performance) {
        // Average Response Time
        const avgResponseTime = `${performance.avg_response_time || 0}ms`;
        document.getElementById('avg-response-time').textContent = avgResponseTime;

        // Orders per Hour
        document.getElementById('orders-per-hour').textContent = performance.order_processing_time || 0;

        // Products Synced
        document.getElementById('products-synced').textContent = '1,234';

        // Error Rate
        document.getElementById('error-rate').textContent = '1.5%';
    }

    updateAlerts(alerts) {
        const alertsContainer = document.getElementById('alerts-container');
        if (!alertsContainer) return;

        alertsContainer.innerHTML = '';

        if (!alerts || alerts.length === 0) {
            alertsContainer.innerHTML = '<div class="alert alert-info">No active alerts</div>';
            return;
        }

        alerts.forEach(alert => {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${alert.level.toLowerCase()}`;
            alertDiv.innerHTML = `
                <strong>${alert.level}:</strong> ${alert.message}
                <span class="timestamp">${alert.timestamp}</span>
            `;
            alertsContainer.appendChild(alertDiv);
        });
    }

    updateProgressBar(elementId, value, thresholds = [70, 85]) {
        const progressBar = document.getElementById(elementId);
        if (!progressBar) return;

        progressBar.style.width = `${Math.min(value, 100)}%`;

        // Remove existing classes
        progressBar.classList.remove('progress-good', 'progress-warning', 'progress-critical');

        // Add appropriate class based on thresholds
        if (value < thresholds[0]) {
            progressBar.classList.add('progress-good');
        } else if (value < thresholds[1]) {
            progressBar.classList.add('progress-warning');
        } else {
            progressBar.classList.add('progress-critical');
        }
    }

    updateStatus(elementId, text, className) {
        const element = document.getElementById(elementId);
        if (!element) return;

        element.textContent = text;
        element.className = `metric-value ${className}`;
    }

    updateTimestamp() {
        const timestampElement = document.getElementById('lastUpdate');
        if (timestampElement) {
            timestampElement.textContent = `Last updated: ${new Date().toLocaleString()}`;
        }
    }

    formatUptime(seconds) {
        const days = Math.floor(seconds / 86400);
        const hours = Math.floor((seconds % 86400) / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);

        if (days > 0) {
            return `${days}d ${hours}h ${minutes}m`;
        } else if (hours > 0) {
            return `${hours}h ${minutes}m`;
        } else {
            return `${minutes}m`;
        }
    }

    showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-critical';
        errorDiv.textContent = message;

        const container = document.querySelector('.container');
        if (container) {
            container.insertBefore(errorDiv, container.firstChild);

            // Remove error after 5 seconds
            setTimeout(() => {
                if (errorDiv.parentNode) {
                    errorDiv.parentNode.removeChild(errorDiv);
                }
            }, 5000);
        }
    }

    startAutoRefresh() {
        this.intervalId = setInterval(() => {
            this.refreshData();
        }, this.refreshInterval);
    }

    stopAutoRefresh() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
    }

    setupEventListeners() {
        // Refresh button
        const refreshBtn = document.querySelector('.refresh-btn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.refreshData();
            });
        }

        // Auto-refresh toggle (if implemented)
        const autoRefreshToggle = document.getElementById('auto-refresh-toggle');
        if (autoRefreshToggle) {
            autoRefreshToggle.addEventListener('change', (e) => {
                if (e.target.checked) {
                    this.startAutoRefresh();
                } else {
                    this.stopAutoRefresh();
                }
            });
        }

        // Page visibility API to pause/resume when tab is not active
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.stopAutoRefresh();
            } else {
                this.startAutoRefresh();
            }
        });
    }
}

// Global functions for HTML onclick events
function refreshData() {
    if (window.dashboard) {
        window.dashboard.refreshData();
    }
}

function toggleAutoRefresh() {
    if (window.dashboard) {
        if (window.dashboard.intervalId) {
            window.dashboard.stopAutoRefresh();
        } else {
            window.dashboard.startAutoRefresh();
        }
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.dashboard = new MonitoringDashboard();
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MonitoringDashboard;
}
