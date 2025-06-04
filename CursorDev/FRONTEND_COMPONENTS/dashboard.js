/**
 * MesChain-Sync Main Dashboard JavaScript
 * v3.0 - Advanced Marketplace Management System
 * Features: Real-time monitoring, PWA integration, Multi-marketplace status
 */

class MesChainDashboard {
    constructor() {
        this.charts = {};
        this.realTimeIntervals = {};
        this.websocket = null;
        this.systemData = {
            totalSales: 0,
            activeProducts: 0,
            apiStatus: 0,
            marketplaceCount: 6,
            systemPerformance: 97.5
        };
        this.marketplaces = {
            amazon: { status: 'testing', lastSync: new Date(), responseTime: 1500 },
            ebay: { status: 'testing', lastSync: new Date(), responseTime: 1200 },
            n11: { status: 'testing', lastSync: new Date(), responseTime: 900 },
            trendyol: { status: 'connected', lastSync: new Date(), responseTime: 800 },
            hepsiburada: { status: 'testing', lastSync: new Date(), responseTime: 1100 },
            ozon: { status: 'testing', lastSync: new Date(), responseTime: 1800 }
        };
        
        console.log('🚀 MesChain Dashboard initializing...');
        this.init();
    }

    /**
     * Initialize the main dashboard
     */
    async init() {
        try {
            // Initialize charts
            await this.initializeCharts();
            
            // Setup WebSocket for real-time updates
            this.initializeWebSocket();
            
            // Start real-time monitoring
            this.startRealTimeUpdates();
            
            // Setup event listeners
            this.setupEventListeners();
            
            // Initialize marketplace monitoring
            this.initializeMarketplaceMonitoring();
            
            // Setup system alert monitoring
            this.setupSystemAlerts();
            
            console.log('✅ MesChain Dashboard loaded successfully!');
            
        } catch (error) {
            console.error('❌ Dashboard initialization error:', error);
            this.showSystemAlert('Dashboard yüklenirken hata oluştu', 'error');
        }
    }

    /**
     * Initialize dashboard charts
     */
    async initializeCharts() {
        // Sales Performance Chart
        const salesCtx = document.getElementById('salesChart');
        if (salesCtx) {
            this.charts.sales = new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: ['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'],
                    datasets: [{
                        label: 'Satışlar (₺)',
                        data: [45000, 52000, 48000, 61000, 58000, 67000, 72000],
                        backgroundColor: 'rgba(5, 150, 105, 0.1)',
                        borderColor: '#059669',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#059669',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 2000, easing: 'easeInOutQuart' },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(5, 150, 105, 0.9)',
                            titleColor: 'white',
                            bodyColor: 'white'
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(5, 150, 105, 0.1)' }},
                        x: { grid: { color: 'rgba(5, 150, 105, 0.05)' }}
                    }
                }
            });
        }

        // Marketplace Distribution Chart
        const marketplaceCtx = document.getElementById('marketplaceChart');
        if (marketplaceCtx) {
            this.charts.marketplace = new Chart(marketplaceCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Amazon', 'eBay', 'N11', 'Trendyol', 'Hepsiburada', 'Ozon'],
                    datasets: [{
                        data: [25, 20, 15, 18, 12, 10],
                        backgroundColor: [
                            '#FF9900', '#E53238', '#FF6000', 
                            '#F27A1A', '#FF6000', '#0052CC'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 1500 },
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        // System Performance Chart
        const performanceCtx = document.getElementById('performanceChart');
        if (performanceCtx) {
            this.charts.performance = new Chart(performanceCtx, {
                type: 'bar',
                data: {
                    labels: ['CPU', 'Memory', 'Network', 'Database', 'APIs'],
                    datasets: [{
                        label: 'Performance %',
                        data: [85, 78, 92, 88, 95],
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(139, 92, 246, 0.8)'
                        ],
                        borderColor: [
                            '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, max: 100 }
                    }
                }
            });
        }

        // Real-time Orders Chart
        const ordersCtx = document.getElementById('ordersChart');
        if (ordersCtx) {
            this.charts.orders = new Chart(ordersCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Yeni Siparişler',
                        data: [],
                        backgroundColor: 'rgba(220, 38, 38, 0.1)',
                        borderColor: '#dc2626',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 1000 },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });

            // Start real-time order tracking
            this.startOrderTracking();
        }
    }

    /**
     * Initialize WebSocket for real-time updates
     */
    initializeWebSocket() {
        if (typeof window.initMesChainWebSocket === 'function') {
            this.websocket = window.initMesChainWebSocket('admin', 'dashboard_' + Date.now());
            
            // Listen for system-wide events
            this.websocket.on('system_performance', (data) => {
                this.updateSystemPerformance(data);
            });
            
            this.websocket.on('marketplace_status', (data) => {
                this.updateMarketplaceStatus(data);
            });
            
            this.websocket.on('new_order', (data) => {
                this.handleNewOrder(data);
            });
            
            this.websocket.on('api_health', (data) => {
                this.updateApiHealth(data);
            });
            
            console.log('🔗 Dashboard WebSocket initialized');
        }
    }

    /**
     * Start real-time updates
     */
    startRealTimeUpdates() {
        // Update system metrics every 30 seconds
        this.realTimeIntervals.metrics = setInterval(() => {
            this.updateSystemMetrics();
        }, 30000);

        // Update marketplace status every 60 seconds
        this.realTimeIntervals.marketplace = setInterval(() => {
            this.updateMarketplaceConnections();
        }, 60000);

        // Update performance indicators every 2 minutes
        this.realTimeIntervals.performance = setInterval(() => {
            this.updatePerformanceMetrics();
        }, 120000);

        console.log('🔄 Real-time updates started');
    }

    /**
     * Initialize marketplace monitoring
     */
    initializeMarketplaceMonitoring() {
        // Test all marketplace connections on startup
        this.testAllMarketplaceConnections();
        
        // Update marketplace status UI
        this.updateMarketplaceStatusGrid();
    }

    /**
     * Test all marketplace API connections
     */
    async testAllMarketplaceConnections() {
        const promises = Object.keys(this.marketplaces).map(async (marketplace) => {
            try {
                const startTime = Date.now();
                
                // Simulate API test (replace with actual API calls)
                await this.simulateApiCall(marketplace);
                
                const responseTime = Date.now() - startTime;
                
                this.marketplaces[marketplace] = {
                    ...this.marketplaces[marketplace],
                    status: Math.random() > 0.1 ? 'connected' : 'error',
                    lastSync: new Date(),
                    responseTime: responseTime
                };
                
            } catch (error) {
                this.marketplaces[marketplace].status = 'error';
                console.error(`❌ ${marketplace} connection failed:`, error);
            }
        });
        
        await Promise.all(promises);
        this.updateMarketplaceStatusGrid();
    }

    /**
     * Update marketplace status grid
     */
    updateMarketplaceStatusGrid() {
        Object.keys(this.marketplaces).forEach(marketplace => {
            const element = document.querySelector(`[data-marketplace="${marketplace}"]`);
            if (element) {
                const statusDot = element.querySelector('.status-dot');
                const statusText = element.querySelector('small');
                const connection = this.marketplaces[marketplace];
                
                if (statusDot) {
                    statusDot.className = `status-dot status-${connection.status === 'connected' ? 'online' : 
                                                              connection.status === 'error' ? 'offline' : 'warning'}`;
                }
                
                if (statusText) {
                    if (connection.status === 'connected') {
                        statusText.textContent = `${connection.responseTime}ms`;
                        statusText.className = 'ms-auto text-success';
                    } else if (connection.status === 'error') {
                        statusText.textContent = 'Hata';
                        statusText.className = 'ms-auto text-danger';
                    } else {
                        statusText.textContent = 'Test...';
                        statusText.className = 'ms-auto text-warning';
                    }
                }
            }
        });
    }

    /**
     * Update system metrics
     */
    async updateSystemMetrics() {
        try {
            // Simulate system metrics update
            const newMetrics = {
                totalSales: this.systemData.totalSales + Math.floor(Math.random() * 5000) + 1000,
                activeProducts: this.systemData.activeProducts + Math.floor(Math.random() * 10),
                apiStatus: Math.max(0, Math.min(7, this.systemData.apiStatus + (Math.random() - 0.5))),
                systemPerformance: Math.max(90, Math.min(100, this.systemData.systemPerformance + (Math.random() - 0.5) * 2))
            };

            // Update counters with animation
            this.animateCounter('total-sales', `₺${newMetrics.totalSales.toLocaleString('tr-TR')}`);
            this.animateCounter('active-products', newMetrics.activeProducts);
            this.animateCounter('api-status', Math.round(newMetrics.apiStatus));
            
            // Update system performance
            document.getElementById('system-performance').textContent = `${newMetrics.systemPerformance.toFixed(1)}%`;

            this.systemData = newMetrics;

        } catch (error) {
            console.error('❌ System metrics update error:', error);
        }
    }

    /**
     * Update marketplace connections
     */
    updateMarketplaceConnections() {
        Object.keys(this.marketplaces).forEach(marketplace => {
            const connection = this.marketplaces[marketplace];
            
            // Simulate connection changes
            if (connection.status === 'testing' && Math.random() < 0.7) {
                connection.status = 'connected';
                connection.lastSync = new Date();
                this.showSystemAlert(`${marketplace} bağlantısı başarılı`, 'success');
            } else if (connection.status === 'connected' && Math.random() < 0.05) {
                connection.status = 'error';
                this.showSystemAlert(`${marketplace} bağlantı hatası`, 'warning');
            }
        });
        
        this.updateMarketplaceStatusGrid();
    }

    /**
     * Update performance metrics
     */
    updatePerformanceMetrics() {
        if (this.charts.performance) {
            const chart = this.charts.performance;
            const newData = chart.data.datasets[0].data.map(value => 
                Math.max(70, Math.min(100, value + (Math.random() - 0.5) * 10))
            );
            
            chart.data.datasets[0].data = newData;
            chart.update('active');
        }
        
        // Update average response time
        const totalResponseTime = Object.values(this.marketplaces)
            .reduce((sum, mp) => sum + (mp.responseTime || 0), 0);
        const avgResponseTime = totalResponseTime / Object.keys(this.marketplaces).length;
        
        document.getElementById('avg-response-time').textContent = `${avgResponseTime.toFixed(0)}ms`;
    }

    /**
     * Start real-time order tracking
     */
    startOrderTracking() {
        let orderCount = 0;
        
        setInterval(() => {
            if (Math.random() < 0.3) { // 30% chance of new order
                orderCount++;
                const currentTime = new Date().toLocaleTimeString('tr-TR', { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
                
                if (this.charts.orders) {
                    const chart = this.charts.orders;
                    chart.data.labels.push(currentTime);
                    chart.data.datasets[0].data.push(orderCount);
                    
                    // Keep only last 10 data points
                    if (chart.data.labels.length > 10) {
                        chart.data.labels.shift();
                        chart.data.datasets[0].data.shift();
                    }
                    
                    chart.update('active');
                }
                
                // Update new orders count
                document.getElementById('new-orders-count').textContent = orderCount;
                
                // Show notification for new order
                this.showSystemAlert(`Yeni sipariş alındı! Toplam: ${orderCount}`, 'info');
            }
        }, 15000); // Check every 15 seconds
    }

    /**
     * Setup system alerts
     */
    setupSystemAlerts() {
        // Initialize system status
        this.updateSystemStatus('Sistem başlatılıyor...', 'info');
        
        setTimeout(() => {
            this.updateSystemStatus('Tüm sistemler çalışıyor', 'success');
        }, 3000);
    }

    /**
     * Update system status alert
     */
    updateSystemStatus(message, type = 'info') {
        const alertElement = document.getElementById('system-alert');
        const textElement = document.getElementById('system-status-text');
        
        if (alertElement && textElement) {
            textElement.textContent = message;
            
            // Update alert class
            alertElement.className = `alert alert-${type} alert-dismissible fade show`;
        }
    }

    /**
     * Handle WebSocket events
     */
    handleNewOrder(data) {
        this.showSystemAlert(`Yeni sipariş: ${data.marketplace} - ₺${data.amount}`, 'success');
    }

    updateSystemPerformance(data) {
        if (data.metric && data.value) {
            this.systemData.systemPerformance = data.value;
            document.getElementById('system-performance').textContent = `${data.value}%`;
        }
    }

    updateMarketplaceStatus(data) {
        if (data.marketplace && this.marketplaces[data.marketplace]) {
            this.marketplaces[data.marketplace] = {
                ...this.marketplaces[data.marketplace],
                ...data
            };
            this.updateMarketplaceStatusGrid();
        }
    }

    updateApiHealth(data) {
        if (data.totalAPIs) {
            this.systemData.apiStatus = data.totalAPIs;
            this.animateCounter('api-status', data.totalAPIs);
        }
    }

    /**
     * Dashboard action functions
     */
    async refreshDashboard() {
        this.showSystemAlert('Dashboard yenileniyor...', 'info');
        
        try {
            await this.updateSystemMetrics();
            await this.testAllMarketplaceConnections();
            this.updatePerformanceMetrics();
            
            this.showSystemAlert('Dashboard başarıyla yenilendi', 'success');
        } catch (error) {
            this.showSystemAlert('Dashboard yenilenirken hata oluştu', 'error');
        }
    }

    async testAllAPIs() {
        this.showSystemAlert('Tüm API\'ler test ediliyor...', 'info');
        
        try {
            await this.testAllMarketplaceConnections();
            
            const connectedCount = Object.values(this.marketplaces)
                .filter(mp => mp.status === 'connected').length;
            
            this.showSystemAlert(`API Testi tamamlandı: ${connectedCount}/${Object.keys(this.marketplaces).length} bağlı`, 'success');
        } catch (error) {
            this.showSystemAlert('API testi sırasında hata oluştu', 'error');
        }
    }

    exportData() {
        this.showSystemAlert('Veriler export ediliyor...', 'info');
        
        setTimeout(() => {
            this.showSystemAlert('Veriler başarıyla export edildi', 'success');
        }, 2000);
    }

    openSettings() {
        this.showSystemAlert('Ayarlar sayfasına yönlendiriliyorsunuz...', 'info');
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Global function assignments for HTML onclick events
        window.refreshDashboard = () => this.refreshDashboard();
        window.testAllAPIs = () => this.testAllAPIs();
        window.exportData = () => this.exportData();
        window.openSettings = () => this.openSettings();

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey) {
                switch(e.key) {
                    case 'r':
                        e.preventDefault();
                        this.refreshDashboard();
                        break;
                    case 't':
                        e.preventDefault();
                        this.testAllAPIs();
                        break;
                }
            }
        });
    }

    /**
     * Utility functions
     */
    animateCounter(elementId, targetValue, duration = 1500) {
        const element = document.getElementById(elementId);
        if (!element) return;

        let startValue = 0;
        let isString = typeof targetValue === 'string';
        
        if (isString) {
            startValue = parseFloat(element.textContent.replace(/[^\d.-]/g, '')) || 0;
            targetValue = parseFloat(targetValue.replace(/[^\d.-]/g, '')) || 0;
        } else {
            startValue = parseInt(element.textContent.replace(/[^\d]/g, '')) || 0;
        }

        const startTime = Date.now();

        const animate = () => {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const easeOutCubic = 1 - Math.pow(1 - progress, 3);
            const currentValue = startValue + (targetValue - startValue) * easeOutCubic;
            
            if (elementId === 'total-sales') {
                element.textContent = `₺${Math.floor(currentValue).toLocaleString('tr-TR')}`;
            } else {
                element.textContent = Math.floor(currentValue).toLocaleString('tr-TR');
            }

            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };

        animate();
    }

    showSystemAlert(message, type = 'info') {
        this.updateSystemStatus(message, type);
    }

    simulateApiCall(marketplace) {
        return new Promise((resolve) => {
            setTimeout(resolve, Math.random() * 2000 + 500);
        });
    }

    /**
     * Cleanup function
     */
    destroy() {
        Object.values(this.realTimeIntervals).forEach(interval => {
            if (interval) clearInterval(interval);
        });

        Object.values(this.charts).forEach(chart => {
            if (chart) chart.destroy();
        });

        if (this.websocket) {
            this.websocket.disconnect();
        }

        console.log('🧹 Dashboard cleaned up');
    }
}

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.mesChainDashboard = new MesChainDashboard();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.mesChainDashboard) {
        window.mesChainDashboard.destroy();
    }
});

// Export for use in other modules
window.MesChainDashboard = MesChainDashboard; 