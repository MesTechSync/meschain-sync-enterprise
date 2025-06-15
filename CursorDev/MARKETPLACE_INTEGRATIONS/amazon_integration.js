/**
 * Amazon SP-API Integration
 * MesChain-Sync Frontend Module v3.0 - OpenCart Integration
 * 
 * Features:
 * - OpenCart Admin API integration
 * - Amazon SP-API management
 * - Multi-marketplace synchronization
 * - Real-time inventory tracking
 * - Order management
 * - Performance analytics
 */
class AmazonIntegration {
    constructor() {
        // OpenCart API Configuration
        this.apiEndpoint = '/admin/index.php?route=extension/module/amazon';
        this.userToken = this.extractUserToken();
        this.connectionStatus = 'testing';
        this.lastDataUpdate = null;
        this.products = [];
        this.orders = [];
        this.metrics = {
            totalSales: 0,
            activeProducts: 0,
            newOrders: 0,
            inventoryCount: 0,
            apiRequests: 0,
            successRate: 0
        };
        
        // Amazon specific configurations
        this.amazonConfig = {
            apiVersion: 'SP-API-v1',
            marketplace: 'amazon',
            currency: 'USD', // or EUR, GBP based on marketplace
            locale: 'en-US',
            timezone: 'UTC',
            brandColors: {
                primary: '#FF9900',
                secondary: '#FFB84D',
                accent: '#FF6B00'
            },
            fulfillmentTypes: ['FBA', 'FBM', 'SFP'],
            categories: [
                'Electronics', 'Books', 'Clothing', 'Home & Garden',
                'Sports & Outdoors', 'Health & Beauty', 'Toys & Games'
            ]
        };

        // Chart instances
        this.charts = {
            sales: null,
            category: null
        };

        // Polling intervals
        this.pollingIntervals = {
            apiStatus: null,
            salesData: null,
            orders: null,
            inventory: null
        };

        console.log('🛒 Amazon SP-API Integration v3.0 başlatılıyor...');
        this.init();
    }

    /**
     * Extract user_token from URL for OpenCart API calls
     */
    extractUserToken() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('user_token') || '';
    }

    /**
     * Initialize the Amazon dashboard
     */
    async init() {
        try {
            console.log('🚀 Amazon dashboard başlatılıyor...');
            
            // Test OpenCart API connection
            await this.testOpenCartAPI();
            
            // Initialize UI components
            this.setupEventListeners();
            await this.loadInitialData();
            
            // Start real-time updates
            this.startRealTimeUpdates();
            
            console.log('✅ Amazon dashboard başarıyla yüklendi!');
            
        } catch (error) {
            console.error('❌ Amazon dashboard hatası:', error);
            this.showError('Dashboard yüklenirken bir hata oluştu');
        }
    }

    /**
     * Test OpenCart API connection
     */
    async testOpenCartAPI() {
        try {
            const startTime = Date.now();
            
            const response = await fetch(`${this.apiEndpoint}&action=test&user_token=${this.userToken}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const responseTime = Date.now() - startTime;

            if (!response.ok) {
                throw new Error(`OpenCart API error: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success) {
                this.connectionStatus = 'connected';
                this.updateConnectionStatus('success', 'Amazon SP-API bağlantısı başarılı!');
                this.updateAPIMetrics(responseTime, true);
                console.log('✅ OpenCart Amazon API bağlantısı başarılı');
                return true;
            } else {
                throw new Error(data.error || 'API bağlantı hatası');
            }
            
        } catch (error) {
            console.error('❌ OpenCart Amazon API test hatası:', error);
            this.connectionStatus = 'disconnected';
            this.updateConnectionStatus('error', error.message);
            this.updateAPIMetrics(0, false);
            throw error;
        }
    }

    /**
     * Setup event listeners for UI interactions
     */
    setupEventListeners() {
        // Global functions for HTML onclick events
        window.syncProducts = () => this.syncProducts();
        window.updateInventory = () => this.updateInventory();
        window.fetchOrders = () => this.fetchOrders();
        window.generateReport = () => this.generateReport();
        window.openSettings = () => this.openSettings();
    }

    /**
     * Load initial dashboard data
     */
    async loadInitialData() {
        try {
            // Load metrics
            await this.updateMetrics();
            
            // Load charts
            await this.initializeCharts();
            
            // Load products
            await this.updateProducts();
            
            // Load recent activities
            await this.updateRecentActivities();
            
        } catch (error) {
            console.error('❌ Amazon veri yükleme hatası:', error);
            this.showError('Veriler yüklenirken hata oluştu');
        }
    }

    /**
     * Update dashboard metrics
     */
    async updateMetrics() {
        try {
            const response = await fetch(`${this.apiEndpoint}&action=getMetrics&user_token=${this.userToken}`);
            const data = await response.json();

            if (data.success) {
                const metrics = data.metrics;
                
                // Update metric cards with animation
                this.animateCounter('product-count', metrics.active_products || 0);
                this.animateCounter('order-count', metrics.new_orders || 0);
                this.updateAPIStatus(metrics.api_status || 'Aktif');
                this.updateInventoryStatus(metrics.inventory_status || 'Normal');
                
                // Update API metrics
                document.getElementById('requests-today').textContent = metrics.api_requests || 0;
                document.getElementById('success-rate').textContent = (metrics.success_rate || 95) + '%';
                
                this.metrics = metrics;
            } else {
                console.warn('Amazon metrics data hatası:', data.error);
            }
            
        } catch (error) {
            console.error('❌ Amazon metrics güncelleme hatası:', error);
        }
    }

    /**
     * Initialize charts
     */
    async initializeCharts() {
        try {
            await Promise.all([
                this.createSalesChart(),
                this.createCategoryChart()
            ]);
        } catch (error) {
            console.error('❌ Amazon charts hatası:', error);
        }
    }

    /**
     * Create sales chart
     */
    async createSalesChart() {
        try {
            const response = await fetch(`${this.apiEndpoint}&action=getSalesData&user_token=${this.userToken}`);
            const data = await response.json();

            const ctx = document.getElementById('amazonSalesChart').getContext('2d');
            
            this.charts.sales = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.chart_data?.labels || ['6 Gün Önce', '5 Gün Önce', '4 Gün Önce', '3 Gün Önce', '2 Gün Önce', 'Dün', 'Bugün'],
                    datasets: [{
                        label: 'Amazon Satışları ($)',
                        data: data.chart_data?.values || [1200, 1500, 800, 2100, 1800, 2500, 2200],
                        backgroundColor: 'rgba(255, 153, 0, 0.1)',
                        borderColor: '#FF9900',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#FF9900',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 2000,
                        easing: 'easeInOutQuart'
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                font: { size: 12 }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 153, 0, 0.9)',
                            titleColor: 'white',
                            bodyColor: 'white',
                            borderColor: '#FF9900',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    return `Satış: $${context.parsed.y.toLocaleString()}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(255, 153, 0, 0.1)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(255, 153, 0, 0.05)'
                            }
                        }
                    }
                }
            });

            // Update revenue display
            if (data.chart_data && data.chart_data.values) {
                const totalRevenue = data.chart_data.values.reduce((a, b) => a + b, 0);
                const avgOrder = totalRevenue / data.chart_data.values.length;
                
                document.getElementById('total-revenue').textContent = '$' + totalRevenue.toLocaleString();
                document.getElementById('avg-order').textContent = '$' + avgOrder.toFixed(2);
            }

        } catch (error) {
            console.error('❌ Amazon sales chart hatası:', error);
            this.showChartError('amazonSalesChart', 'Sales Chart yüklenemedi');
        }
    }

    /**
     * Create category chart
     */
    async createCategoryChart() {
        try {
            const response = await fetch(`${this.apiEndpoint}&action=getCategoryData&user_token=${this.userToken}`);
            const data = await response.json();

            const ctx = document.getElementById('amazonCategoryChart').getContext('2d');
            
            this.charts.category = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.chart_data?.labels || ['Electronics', 'Books', 'Clothing', 'Home & Garden', 'Sports'],
                    datasets: [{
                        data: data.chart_data?.values || [35, 25, 20, 15, 5],
                        backgroundColor: [
                            '#FF9900',
                            '#FFB84D', 
                            '#FF6B00',
                            '#E6870A',
                            '#CC7700'
                        ],
                        borderWidth: 3,
                        borderColor: '#fff',
                        hoverBorderWidth: 5,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 2000,
                        easing: 'easeInOutElastic'
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                                font: { size: 11 }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 153, 0, 0.9)',
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((context.parsed * 100) / total).toFixed(1);
                                    return `${context.label}: ${percentage}% (${context.parsed} ürün)`;
                                }
                            }
                        }
                    }
                }
            });

        } catch (error) {
            console.error('❌ Amazon category chart hatası:', error);
            this.showChartError('amazonCategoryChart', 'Category Chart yüklenemedi');
        }
    }

    /**
     * Update products list
     */
    async updateProducts() {
        try {
            const response = await fetch(`${this.apiEndpoint}&action=getProducts&user_token=${this.userToken}`);
            const data = await response.json();

            const container = document.getElementById('product-list');
            
            if (data.success && data.products) {
                let html = '';
                
                data.products.forEach(product => {
                    html += `
                        <div class="product-item">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6 class="mb-1">${product.title}</h6>
                                    <small class="text-muted">ASIN: ${product.asin || 'N/A'}</small>
                                </div>
                                <div class="col-md-2">
                                    <span class="badge ${product.status === 'active' ? 'bg-success' : 'bg-secondary'}">
                                        ${product.status === 'active' ? 'Aktif' : 'Pasif'}
                                    </span>
                                </div>
                                <div class="col-md-2">
                                    <strong>$${product.price?.toLocaleString() || '0'}</strong>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-sm btn-amazon" onclick="editAmazonProduct('${product.asin}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="text-center p-4"><p class="text-muted">Amazon ürünü bulunamadı</p></div>';
            }

        } catch (error) {
            console.error('❌ Amazon products güncelleme hatası:', error);
            document.getElementById('product-list').innerHTML = 
                '<div class="text-center p-4"><p class="text-danger">Ürünler yüklenemedi</p></div>';
        }
    }

    /**
     * Update recent activities
     */
    async updateRecentActivities() {
        try {
            const response = await fetch(`${this.apiEndpoint}&action=getRecentActivities&user_token=${this.userToken}`);
            const data = await response.json();

            const container = document.getElementById('recent-activities');
            
            if (data.success && data.activities) {
                let html = '';
                
                data.activities.forEach(activity => {
                    html += `
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small><strong>${activity.type}:</strong> ${activity.description}</small>
                            <small class="text-muted">${activity.time}</small>
                        </div>
                    `;
                });
                
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="text-center text-muted"><small>Aktivite bulunamadı</small></div>';
            }

        } catch (error) {
            console.error('❌ Amazon activities güncelleme hatası:', error);
        }
    }

    /**
     * Start real-time updates
     */
    startRealTimeUpdates() {
        // Update metrics every 2 minutes
        this.pollingIntervals.apiStatus = setInterval(() => {
            this.updateMetrics();
        }, 120000);

        // Update sales data every 5 minutes
        this.pollingIntervals.salesData = setInterval(() => {
            this.updateSalesChart();
        }, 300000);

        console.log('🔄 Amazon real-time güncellemeler başlatıldı');
    }

    /**
     * Amazon-specific business logic functions
     */
    async syncProducts() {
        console.log('🔄 Amazon ürün senkronizasyonu başlatılıyor...');
        try {
            const response = await fetch(`${this.apiEndpoint}&action=syncProducts&user_token=${this.userToken}`, {
                method: 'POST'
            });
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('Amazon ürün senkronizasyonu tamamlandı!');
                await this.updateProducts();
                await this.updateMetrics();
            } else {
                this.showError(data.error || 'Senkronizasyon hatası');
            }
        } catch (error) {
            console.error('❌ Amazon sync hatası:', error);
            this.showError('Senkronizasyon sırasında hata oluştu');
        }
    }

    async updateInventory() {
        console.log('📦 Amazon envanter güncelleniyor...');
        try {
            const response = await fetch(`${this.apiEndpoint}&action=updateInventory&user_token=${this.userToken}`, {
                method: 'POST'
            });
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('Amazon envanter güncellendi!');
                await this.updateMetrics();
            } else {
                this.showError(data.error || 'Envanter güncelleme hatası');
            }
        } catch (error) {
            console.error('❌ Amazon inventory hatası:', error);
            this.showError('Envanter güncelleme sırasında hata oluştu');
        }
    }

    async fetchOrders() {
        console.log('🛒 Amazon siparişleri getiriliyor...');
        try {
            const response = await fetch(`${this.apiEndpoint}&action=fetchOrders&user_token=${this.userToken}`, {
                method: 'POST'
            });
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(`${data.order_count || 0} yeni sipariş alındı!`);
                await this.updateMetrics();
                await this.updateRecentActivities();
            } else {
                this.showError(data.error || 'Sipariş alma hatası');
            }
        } catch (error) {
            console.error('❌ Amazon orders hatası:', error);
            this.showError('Sipariş alma sırasında hata oluştu');
        }
    }

    async generateReport() {
        console.log('📊 Amazon raporu oluşturuluyor...');
        try {
            const response = await fetch(`${this.apiEndpoint}&action=generateReport&user_token=${this.userToken}`, {
                method: 'POST'
            });
            const data = await response.json();
            
            if (data.success && data.report_url) {
                this.showSuccess('Rapor oluşturuldu!');
                window.open(data.report_url, '_blank');
            } else {
                this.showError(data.error || 'Rapor oluşturma hatası');
            }
        } catch (error) {
            console.error('❌ Amazon report hatası:', error);
            this.showError('Rapor oluşturma sırasında hata oluştu');
        }
    }

    async openSettings() {
        console.log('⚙️ Amazon ayarları açılıyor...');
        window.open(`/admin/index.php?route=extension/module/amazon&user_token=${this.userToken}`, '_blank');
    }

    /**
     * Update UI helper functions
     */
    updateConnectionStatus(type, message) {
        const alertElement = document.getElementById('connection-alert');
        const statusElement = document.getElementById('connection-status-text');
        const indicatorElement = document.getElementById('api-health-indicator');
        const apiStatusElement = document.getElementById('api-status-text');
        
        if (type === 'success') {
            alertElement.className = 'connection-success';
            indicatorElement.textContent = '🟢';
            apiStatusElement.textContent = 'Bağlı';
        } else if (type === 'error') {
            alertElement.className = 'connection-error';
            indicatorElement.textContent = '🔴';
            apiStatusElement.textContent = 'Bağlantı Hatası';
        } else {
            alertElement.className = 'connection-testing';
            indicatorElement.textContent = '🟡';
            apiStatusElement.textContent = 'Test Ediliyor';
        }
        
        statusElement.textContent = message;
    }

    updateAPIMetrics(responseTime, success) {
        document.getElementById('api-response-time').textContent = responseTime > 0 ? `${responseTime}ms` : 'Hata';
        
        if (success) {
            this.metrics.apiRequests++;
            this.metrics.successRate = Math.min(100, this.metrics.successRate + 1);
        } else {
            this.metrics.successRate = Math.max(0, this.metrics.successRate - 5);
        }
    }

    updateAPIStatus(status) {
        const element = document.getElementById('api-status-display');
        if (element) {
            element.textContent = status;
        }
    }

    updateInventoryStatus(status) {
        const element = document.getElementById('inventory-status');
        if (element) {
            element.textContent = status;
        }
    }

    /**
     * Utility functions
     */
    animateCounter(elementId, targetValue) {
        const element = document.getElementById(elementId);
        if (!element) return;

        const startValue = 0;
        const duration = 2000;
        const startTime = Date.now();

        const animate = () => {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const easeOutCubic = 1 - Math.pow(1 - progress, 3);
            const currentValue = startValue + (targetValue - startValue) * easeOutCubic;
            
            element.textContent = Math.floor(currentValue).toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };

        animate();
    }

    async updateSalesChart() {
        if (this.charts.sales) {
            try {
                const response = await fetch(`${this.apiEndpoint}&action=getSalesData&user_token=${this.userToken}`);
                const data = await response.json();

                if (data.success && data.chart_data) {
                    this.charts.sales.data.datasets[0].data = data.chart_data.values;
                    this.charts.sales.update('active');
                }
            } catch (error) {
                console.error('❌ Amazon sales chart güncelleme hatası:', error);
            }
        }
    }

    showSuccess(message) {
        this.showToast(message, 'success');
    }

    showError(message) {
        this.showToast(message, 'error');
    }

    showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show position-fixed`;
        toast.style.top = '20px';
        toast.style.right = '20px';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 5000);
    }

    showChartError(chartId, message) {
        const canvas = document.getElementById(chartId);
        if (canvas && canvas.parentNode) {
            canvas.parentNode.innerHTML = `
                <div class="text-center p-4">
                    <i class="fas fa-exclamation-triangle text-warning fa-2x"></i>
                    <p class="mt-2 text-muted">${message}</p>
                </div>
            `;
        }
    }

    /**
     * Cleanup on page unload
     */
    destroy() {
        Object.values(this.pollingIntervals).forEach(interval => {
            if (interval) clearInterval(interval);
        });
        
        Object.values(this.charts).forEach(chart => {
            if (chart) chart.destroy();
        });
        
        console.log('🧹 Amazon Integration temizlendi');
    }
}

// Global functions for HTML onclick events
window.editAmazonProduct = function(asin) {
    console.log('✏️ Amazon ürün düzenleme:', asin);
    window.open(`/admin/index.php?route=extension/module/amazon/product&asin=${asin}`, '_blank');
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.amazonIntegration = new AmazonIntegration();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.amazonIntegration) {
        window.amazonIntegration.destroy();
    }
});

// Export for use in other modules
window.AmazonIntegration = AmazonIntegration; 