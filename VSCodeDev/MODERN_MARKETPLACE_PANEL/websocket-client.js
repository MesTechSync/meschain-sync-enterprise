/**
 * MesChain WebSocket Client
 * Real-time dashboard updates and marketplace synchronization
 * 
 * @version 1.0.0
 * @date June 1, 2025
 * @author VSCode Frontend Team
 */

class MeschainWebSocketClient {
    constructor() {
        this.wsUrl = 'ws://localhost:8080';
        this.socket = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 5000; // 5 seconds
        this.isConnected = false;
        this.subscriptions = new Set();
        
        this.init();
    }
    
    init() {
        this.connect();
        this.setupEventHandlers();
    }
    
    connect() {
        try {
            console.log('Connecting to WebSocket server...');
            this.socket = new WebSocket(this.wsUrl);
            
            this.socket.onopen = this.onOpen.bind(this);
            this.socket.onmessage = this.onMessage.bind(this);
            this.socket.onclose = this.onClose.bind(this);
            this.socket.onerror = this.onError.bind(this);
            
        } catch (error) {
            console.error('WebSocket connection failed:', error);
            this.scheduleReconnect();
        }
    }
    
    onOpen(event) {
        console.log('✅ WebSocket connected successfully');
        this.isConnected = true;
        this.reconnectAttempts = 0;
        
        // Update connection status indicator
        this.updateConnectionStatus('connected');
        
        // Subscribe to dashboard updates
        this.subscribeToDashboard();
        
        // Show success notification
        this.showNotification('Connected to real-time updates', 'success');
    }
    
    onMessage(event) {
        try {
            const data = JSON.parse(event.data);
            this.handleMessage(data);
        } catch (error) {
            console.error('Error parsing WebSocket message:', error);
        }
    }
    
    onClose(event) {
        console.log('WebSocket connection closed');
        this.isConnected = false;
        this.updateConnectionStatus('disconnected');
        
        // Attempt to reconnect if not a clean close
        if (event.code !== 1000) {
            this.scheduleReconnect();
        }
    }
    
    onError(error) {
        console.error('WebSocket error:', error);
        this.updateConnectionStatus('error');
        this.showNotification('Connection error - retrying...', 'warning');
    }
    
    handleMessage(data) {
        console.log('Received message:', data.type);
        
        switch (data.type) {
            case 'dashboard_update':
                this.handleDashboardUpdate(data.data);
                break;
                
            case 'sync_status_update':
                this.handleSyncStatusUpdate(data);
                break;
                
            case 'performance_data':
                this.handlePerformanceData(data.data);
                break;
                
            case 'marketplace_status':
                this.handleMarketplaceStatus(data.data);
                break;
                
            case 'subscription_confirmed':
                console.log('Dashboard subscription confirmed');
                break;
                
            case 'sync_started':
                this.handleSyncStarted(data);
                break;
                
            case 'error':
                console.error('Server error:', data.message);
                this.showNotification(data.message, 'error');
                break;
                
            default:
                console.log('Unknown message type:', data.type);
        }
    }
    
    handleDashboardUpdate(data) {
        if (!data) return;
        
        // Update overview metrics
        if (data.overview) {
            this.updateOverviewMetrics(data.overview);
        }
        
        // Update performance metrics
        if (data.performance) {
            this.updatePerformanceMetrics(data.performance);
        }
        
        // Update marketplace status
        if (data.marketplace_status) {
            this.updateMarketplaceStatus(data.marketplace_status);
        }
        
        // Update recent orders
        if (data.recent_orders) {
            this.updateRecentOrders(data.recent_orders);
        }
        
        // Show update indicator
        this.showUpdateIndicator();
    }
    
    handleSyncStatusUpdate(data) {
        const statusElement = document.querySelector(`#${data.marketplace}Status`);
        if (statusElement) {
            const badgeClass = data.status === 'in_progress' ? 'warning' : 
                             data.status === 'completed' ? 'success' : 'info';
            
            statusElement.innerHTML = `
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-${badgeClass}">${data.status}</span>
                    <small class="text-muted">Syncing...</small>
                </div>
            `;
        }
        
        this.showNotification(`${data.marketplace} sync ${data.status}`, 'info');
    }
    
    handlePerformanceData(data) {
        this.updatePerformanceMetrics(data);
    }
    
    handleMarketplaceStatus(data) {
        this.updateMarketplaceStatus(data);
    }
    
    handleSyncStarted(data) {
        this.showNotification(`${data.marketplace} synchronization started`, 'info');
        
        // Update sync button state
        const syncButton = document.querySelector(`#sync-${data.marketplace}`);
        if (syncButton) {
            syncButton.disabled = true;
            syncButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Syncing...';
        }
    }
    
    updateOverviewMetrics(overview) {
        const elements = {
            'totalRevenue': `$${this.formatNumber(overview.total_revenue)}`,
            'totalOrders': this.formatNumber(overview.total_orders),
            'activeProducts': this.formatNumber(overview.active_products),
            'conversionRate': `${overview.conversion_rate}%`
        };
        
        Object.keys(elements).forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                this.animateValueUpdate(element, elements[id]);
            }
        });
    }
    
    updatePerformanceMetrics(metrics) {
        // Update response time
        const responseTimeElement = document.getElementById('responseTime');
        if (responseTimeElement) {
            responseTimeElement.textContent = `${metrics.response_time}ms`;
        }
        
        // Update uptime
        const uptimeElement = document.getElementById('uptime');
        if (uptimeElement) {
            uptimeElement.textContent = `${metrics.uptime}%`;
        }
        
        // Update memory usage
        const memoryBar = document.getElementById('memoryUsageBar');
        if (memoryBar) {
            memoryBar.style.width = `${metrics.memory_usage}%`;
            memoryBar.textContent = `${metrics.memory_usage}%`;
        }
        
        // Update CPU usage
        const cpuBar = document.getElementById('cpuUsageBar');
        if (cpuBar) {
            cpuBar.style.width = `${metrics.cpu_usage}%`;
            cpuBar.textContent = `${metrics.cpu_usage}%`;
        }
    }
    
    updateMarketplaceStatus(statusData) {
        Object.keys(statusData).forEach(marketplace => {
            const status = statusData[marketplace];
            const statusElement = document.getElementById(`${marketplace}Status`);
            
            if (statusElement) {
                const statusClass = status.status === 'connected' ? 'success' : 
                                  status.status === 'warning' ? 'warning' : 
                                  status.status === 'syncing' ? 'info' : 'danger';
                
                const timeAgo = this.getTimeAgo(status.last_sync);
                
                statusElement.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-${statusClass}">${status.status}</span>
                        <small class="text-muted">${timeAgo}</small>
                    </div>
                    <div class="mt-1">
                        <small>Products: ${status.products_synced || 0} | Errors: ${status.errors || 0}</small>
                    </div>
                `;
            }
        });
    }
    
    updateRecentOrders(orders) {
        const tableBody = document.querySelector('#recentOrdersTable tbody');
        if (!tableBody || !orders.length) return;
        
        // Add new orders to the top
        orders.forEach(order => {
            const row = document.createElement('tr');
            row.className = 'table-row-new'; // For animation
            row.innerHTML = `
                <td><span class="fw-bold">${order.id}</span></td>
                <td>${order.customer}</td>
                <td><span class="badge bg-${this.getMarketplaceBadgeColor(order.marketplace)}">${order.marketplace}</span></td>
                <td class="fw-bold text-success">$${order.total}</td>
                <td class="text-muted">${this.getTimeAgo(order.timestamp)}</td>
            `;
            
            tableBody.insertBefore(row, tableBody.firstChild);
            
            // Remove animation class after animation
            setTimeout(() => row.classList.remove('table-row-new'), 500);
        });
        
        // Keep only the latest 10 orders
        while (tableBody.children.length > 10) {
            tableBody.removeChild(tableBody.lastChild);
        }
    }
    
    subscribeToDashboard() {
        this.sendMessage({
            action: 'subscribe_dashboard',
            interval: 30 // 30 seconds
        });
    }
    
    requestManualSync(marketplace = 'all') {
        if (!this.isConnected) {
            this.showNotification('Not connected to server', 'error');
            return;
        }
        
        this.sendMessage({
            action: 'manual_sync',
            marketplace: marketplace
        });
    }
    
    requestPerformanceData() {
        this.sendMessage({
            action: 'get_performance'
        });
    }
    
    requestMarketplaceStatus() {
        this.sendMessage({
            action: 'get_marketplace_status'
        });
    }
    
    sendMessage(data) {
        if (this.isConnected && this.socket.readyState === WebSocket.OPEN) {
            this.socket.send(JSON.stringify(data));
        } else {
            console.warn('WebSocket not connected, message queued');
            // Could implement message queue here
        }
    }
    
    scheduleReconnect() {
        if (this.reconnectAttempts >= this.maxReconnectAttempts) {
            console.error('Max reconnection attempts reached');
            this.showNotification('Unable to connect to server', 'error');
            return;
        }
        
        this.reconnectAttempts++;
        console.log(`Reconnecting in ${this.reconnectDelay}ms (attempt ${this.reconnectAttempts})`);
        
        setTimeout(() => {
            this.connect();
        }, this.reconnectDelay);
    }
    
    updateConnectionStatus(status) {
        const indicator = document.getElementById('connectionIndicator');
        if (!indicator) return;
        
        const statusConfig = {
            connected: { class: 'bg-success', text: 'Connected', icon: 'fas fa-circle' },
            disconnected: { class: 'bg-secondary', text: 'Disconnected', icon: 'fas fa-circle' },
            error: { class: 'bg-danger', text: 'Error', icon: 'fas fa-exclamation-circle' }
        };
        
        const config = statusConfig[status] || statusConfig.disconnected;
        indicator.className = `badge ${config.class}`;
        indicator.innerHTML = `<i class="${config.icon} me-1"></i>${config.text}`;
    }
    
    showUpdateIndicator() {
        const indicator = document.getElementById('updateIndicator');
        if (indicator) {
            indicator.style.display = 'inline-block';
            setTimeout(() => {
                indicator.style.display = 'none';
            }, 2000);
        }
    }
    
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 1060; max-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    }
    
    animateValueUpdate(element, newValue) {
        element.style.transition = 'all 0.3s ease';
        element.style.transform = 'scale(1.1)';
        element.textContent = newValue;
        
        setTimeout(() => {
            element.style.transform = 'scale(1)';
        }, 300);
    }
    
    formatNumber(num) {
        return new Intl.NumberFormat().format(num);
    }
    
    getMarketplaceBadgeColor(marketplace) {
        const colors = {
            'Amazon': 'warning',
            'Trendyol': 'danger',
            'eBay': 'primary',
            'N11': 'info',
            'Ozon': 'secondary'
        };
        return colors[marketplace] || 'secondary';
    }
    
    getTimeAgo(timestamp) {
        const now = Date.now() / 1000;
        const diff = now - timestamp;
        
        if (diff < 60) return 'Just now';
        if (diff < 3600) return `${Math.floor(diff / 60)} minutes ago`;
        if (diff < 86400) return `${Math.floor(diff / 3600)} hours ago`;
        return `${Math.floor(diff / 86400)} days ago`;
    }
    
    setupEventHandlers() {
        // Manual sync buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-sync-marketplace]')) {
                const marketplace = e.target.getAttribute('data-sync-marketplace');
                this.requestManualSync(marketplace);
            }
        });
        
        // Refresh buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('#refreshPerformance')) {
                this.requestPerformanceData();
            }
            if (e.target.matches('#refreshMarketplaces')) {
                this.requestMarketplaceStatus();
            }
        });
    }
    
    disconnect() {
        if (this.socket) {
            this.socket.close(1000, 'Client disconnect');
        }
    }
}

// Global WebSocket client instance
let webSocketClient = null;

// Initialize WebSocket when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Add connection indicator to the navbar
    const navbar = document.querySelector('.navbar .d-flex');
    if (navbar) {
        const indicator = document.createElement('span');
        indicator.id = 'connectionIndicator';
        indicator.className = 'badge bg-secondary me-2';
        indicator.innerHTML = '<i class="fas fa-circle me-1"></i>Connecting...';
        navbar.insertBefore(indicator, navbar.firstChild);
        
        // Add update indicator
        const updateIndicator = document.createElement('span');
        updateIndicator.id = 'updateIndicator';
        updateIndicator.className = 'badge bg-info me-2';
        updateIndicator.style.display = 'none';
        updateIndicator.innerHTML = '<i class="fas fa-sync fa-spin me-1"></i>Updating...';
        navbar.insertBefore(updateIndicator, navbar.firstChild);
    }
    
    // Initialize WebSocket client
    try {
        webSocketClient = new MeschainWebSocketClient();
        console.log('🚀 WebSocket client initialized');
    } catch (error) {
        console.error('Failed to initialize WebSocket client:', error);
    }
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (webSocketClient) {
        webSocketClient.disconnect();
    }
});

// Export for manual testing
window.MeschainWebSocketClient = MeschainWebSocketClient;
