/**
 * N11 Turkish Marketplace Integration
 * MesChain-Sync Frontend Module v3.0 - OpenCart Integration
 * 
 * Features:
 * - OpenCart Admin API integration
 * - N11 Seller API management
 * - Turkish Lira currency support
 * - Commission tracking
 * - Category mapping
 * - Real-time order processing
 * - Turkish marketplace compliance
 */
class N11Integration {
    constructor() {
        // OpenCart API Configuration
        this.apiEndpoint = '/admin/index.php?route=extension/module/n11';
        this.userToken = this.extractUserToken();
        this.connectionStatus = 'testing';
        this.lastDataUpdate = null;
        this.products = [];
        this.orders = [];
        this.commissionRates = {};
        this.metrics = {
            monthlySales: 0,
            activeProducts: 0,
            pendingOrders: 0,
            avgCommissionRate: 0,
            categoryCount: 0,
            monthlyCommission: 0
        };
        
        // N11 specific configurations
        this.n11Config = {
            apiVersion: 'v2.0',
            marketplace: 'n11',
            currency: 'TRY',
            locale: 'tr-TR',
            timezone: 'Europe/Istanbul',
            brandColors: {
                primary: '#6B2C91',
                secondary: '#8A4FB1',
                accent: '#B775D8'
            },
            categories: [
                'Bilgisayar', 'Cep Telefonu', 'Elektronik', 'Ev & Yaşam',
                'Moda', 'Spor & Outdoor', 'Otomobil', 'Kozmetik',
                'Kitap & Müzik', 'Anne & Bebek', 'Pet Shop', 'Süpermarket'
            ],
            commissionRanges: {
                'technology': { min: 6, max: 12 },
                'fashion': { min: 12, max: 18 },
                'home': { min: 8, max: 15 },
                'automotive': { min: 5, max: 10 }
            },
            shippingOptions: ['Ücretsiz Kargo', 'Aynı Gün Teslimat', 'Standart Kargo']
        };

        // Chart instances
        this.charts = {
            sales: null,
            commission: null
        };

        // Polling intervals
        this.pollingIntervals = {
            apiStatus: null,
            salesData: null,
            orders: null,
            commission: null
        };

        console.log('🛍️ N11 Turkish Marketplace Integration v3.0 başlatılıyor...');
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
     * Initialize the N11 dashboard
     */
    async init() {
        try {
            console.log('🚀 N11 dashboard başlatılıyor...');
            
            // Test OpenCart API connection
            await this.testOpenCartAPI();
            
            // Initialize UI components
            this.setupEventListeners();
            await this.loadInitialData();
            
            // Start real-time updates
            this.startRealTimeUpdates();
            
            console.log('✅ N11 dashboard başarıyla yüklendi!');
            
        } catch (error) {
            console.error('❌ N11 dashboard hatası:', error);
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
                this.updateConnectionStatus('success', 'N11 API bağlantısı başarılı!');
                console.log('✅ OpenCart N11 API bağlantısı başarılı');
                return true;
            } else {
                throw new Error(data.error || 'N11 API bağlantı hatası');
            }
            
        } catch (error) {
            console.error('❌ OpenCart N11 API test hatası:', error);
            this.connectionStatus = 'disconnected';
            this.updateConnectionStatus('error', error.message);
            throw error;
        }
    }

    /**
     * Setup event listeners for UI interactions
     */
    setupEventListeners() {
        // Global functions for HTML onclick events
        window.refreshProducts = () => this.refreshProducts();
        window.syncInventory = () => this.syncInventory();
        window.updatePrices = () => this.updatePrices();
        window.processOrders = () => this.processOrders();
        window.manageCategories = () => this.manageCategories();
        window.openN11Settings = () => this.openSettings();
        window.testN11API = () => this.testAPI();
        window.viewCommissionDetails = () => this.viewCommissionDetails();
    }

    /**
     * Load initial dashboard data
     */
    async loadInitialData() {
        try {
            // Load metrics
            await this.updateMetrics();
            
            // Load sales chart
            await this.initializeSalesChart();
            
            // Load products
            await this.updateProducts();
            
            // Load recent orders
            await this.updateRecentOrders();
            
            // Load commission data
            await this.updateCommissionData();
            
            // Update last update time
            document.getElementById('last-update').textContent = new Date().toLocaleString('tr-TR');
            
        } catch (error) {
            console.error('❌ N11 veri yükleme hatası:', error);
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
                
                // Update metric cards with Turkish Lira formatting
                this.animateCounter('n11-sales', metrics.monthly_sales || 0, '₺');
                this.animateCounter('active-products', metrics.active_products || 0);
                this.animateCounter('pending-orders', metrics.pending_orders || 0);
                this.animateCounter('commission-rate', metrics.avg_commission_rate || 0, '%');
                
                this.metrics = metrics;
            } else {
                console.warn('N11 metrics data hatası:', data.error);
            }
            
        } catch (error) {
            console.error('❌ N11 metrics güncelleme hatası:', error);
        }
    }

    /**
     * Initialize sales chart
     */
    async initializeSalesChart() {
        try {
            const response = await fetch(`${this.apiEndpoint}&action=getSalesData&user_token=${this.userToken}`);
            const data = await response.json();

            const ctx = document.getElementById('n11SalesChart').getContext('2d');
            
            this.charts.sales = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.chart_data?.labels || ['Son 7 Gün', 'Son 6 Gün', 'Son 5 Gün', 'Son 4 Gün', 'Son 3 Gün', 'Dün', 'Bugün'],
                    datasets: [{
                        label: 'N11 Satışları (₺)',
                        data: data.chart_data?.values || [1800, 2200, 1600, 2800, 2400, 3200, 2900],
                        backgroundColor: 'rgba(107, 44, 145, 0.1)',
                        borderColor: '#6B2C91',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#6B2C91',
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
                            backgroundColor: 'rgba(107, 44, 145, 0.9)',
                            titleColor: 'white',
                            bodyColor: 'white',
                            borderColor: '#6B2C91',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    return `Satış: ₺${context.parsed.y.toLocaleString('tr-TR')}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(107, 44, 145, 0.1)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return '₺' + value.toLocaleString('tr-TR');
                                }
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(107, 44, 145, 0.05)'
                            }
                        }
                    }
                }
            });

        } catch (error) {
            console.error('❌ N11 sales chart hatası:', error);
            this.showChartError('n11SalesChart', 'Chart yüklenemedi');
        }
    }

    /**
     * Update products list
     */
    async updateProducts() {
        try {
            const response = await fetch(`${this.apiEndpoint}&action=getProducts&user_token=${this.userToken}`);
            const data = await response.json();

            const container = document.getElementById('products-container');
            
            if (data.success && data.products) {
                let html = '';
                
                data.products.forEach(product => {
                    html += `
                        <div class="product-item">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6 class="mb-1">${product.name}</h6>
                                    <small class="text-muted">N11 ID: ${product.n11_id || 'N/A'}</small>
                                </div>
                                <div class="col-md-2">
                                    <span class="badge ${product.status === 'active' ? 'bg-success' : 'bg-secondary'}">
                                        ${product.status === 'active' ? 'Aktif' : 'Pasif'}
                                    </span>
                                </div>
                                <div class="col-md-2">
                                    <strong>₺${product.price?.toLocaleString('tr-TR') || '0'}</strong>
                                    <br><small class="text-muted">Komisyon: %${product.commission_rate || '0'}</small>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-sm n11-btn" onclick="editN11Product('${product.n11_id}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="text-center p-4"><p class="text-muted">N11 ürünü bulunamadı</p></div>';
            }

        } catch (error) {
            console.error('❌ N11 products güncelleme hatası:', error);
            document.getElementById('products-container').innerHTML = 
                '<div class="text-center p-4"><p class="text-danger">Ürünler yüklenemedi</p></div>';
        }
    }

    /**
     * Update recent orders table
     */
    async updateRecentOrders() {
        try {
            const response = await fetch(`${this.apiEndpoint}&action=getRecentOrders&user_token=${this.userToken}`);
            const data = await response.json();

            const tbody = document.getElementById('recent-orders');
            
            if (data.success && data.orders) {
                let html = '';
                
                data.orders.forEach(order => {
                    const statusClass = this.getOrderStatusClass(order.status);
                    const commissionAmount = (order.total * order.commission_rate / 100).toFixed(2);
                    
                    html += `
                        <tr>
                            <td><strong>${order.order_number}</strong></td>
                            <td>${order.customer_name}</td>
                            <td>${order.product_name}</td>
                            <td><strong>₺${order.total?.toLocaleString('tr-TR')}</strong></td>
                            <td>₺${commissionAmount} (%${order.commission_rate})</td>
                            <td><span class="badge ${statusClass}">${order.status_text}</span></td>
                            <td>${new Date(order.date_added).toLocaleDateString('tr-TR')}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewN11Order('${order.order_id}')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
                
                tbody.innerHTML = html;
            } else {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Sipariş bulunamadı</td></tr>';
            }

        } catch (error) {
            console.error('❌ N11 orders güncelleme hatası:', error);
            document.getElementById('recent-orders').innerHTML = 
                '<tr><td colspan="8" class="text-center text-danger">Siparişler yüklenemedi</td></tr>';
        }
    }

    /**
     * Update commission data
     */
    async updateCommissionData() {
        try {
            const response = await fetch(`${this.apiEndpoint}&action=getCommissionData&user_token=${this.userToken}`);
            const data = await response.json();

            if (data.success && data.commission_data) {
                const container = document.getElementById('commission-container');
                let html = '';
                
                data.commission_data.forEach(category => {
                    html += `
                        <div class="commission-card">
                            <h6 class="mb-1">${category.name}</h6>
                            <small>%${category.rate} komisyon - ₺${category.monthly_amount} bu ay</small>
                        </div>
                    `;
                });
                
                container.innerHTML = html;
                this.commissionRates = data.commission_data;
            }

        } catch (error) {
            console.error('❌ N11 commission data hatası:', error);
        }
    }

    /**
     * Start real-time updates
     */
    startRealTimeUpdates() {
        // Update metrics every 90 seconds
        this.pollingIntervals.apiStatus = setInterval(() => {
            this.updateMetrics();
        }, 90000);

        // Update sales data every 5 minutes
        this.pollingIntervals.salesData = setInterval(() => {
            this.updateSalesChart();
        }, 300000);

        // Update commission data every 10 minutes
        this.pollingIntervals.commission = setInterval(() => {
            this.updateCommissionData();
        }, 600000);

        console.log('🔄 N11 real-time güncellemeler başlatıldı');
    }

    /**
     * N11-specific business logic functions
     */
    async refreshProducts() {
        console.log('🔄 N11 ürünleri yenileniyor...');
        document.getElementById('products-container').innerHTML = 
            '<div class="text-center p-4"><div class="loading-animation"></div> Yenileniyor...</div>';
        await this.updateProducts();
        this.showSuccess('N11 ürünleri başarıyla yenilendi!');
    }

    async syncInventory() {
        console.log('🔄 N11 stok senkronizasyonu başlatılıyor...');
        try {
            const response = await fetch(`${this.apiEndpoint}&action=syncInventory&user_token=${this.userToken}`, {
                method: 'POST'
            });
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('N11 stok senkronizasyonu tamamlandı!');
                await this.updateMetrics();
                await this.updateProducts();
            } else {
                this.showError(data.error || 'Senkronizasyon hatası');
            }
        } catch (error) {
            console.error('❌ N11 sync hatası:', error);
            this.showError('Senkronizasyon sırasında hata oluştu');
        }
    }

    async updatePrices() {
        console.log('💰 N11 fiyatlar güncelleniyor...');
        try {
            const response = await fetch(`${this.apiEndpoint}&action=updatePrices&user_token=${this.userToken}`, {
                method: 'POST'
            });
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(`${data.updated_count || 0} ürün fiyatı güncellendi!`);
                await this.updateProducts();
                await this.updateMetrics();
            } else {
                this.showError(data.error || 'Fiyat güncelleme hatası');
            }
        } catch (error) {
            console.error('❌ N11 price update hatası:', error);
            this.showError('Fiyat güncelleme sırasında hata oluştu');
        }
    }

    async processOrders() {
        console.log('📦 N11 siparişleri işleniyor...');
        try {
            const response = await fetch(`${this.apiEndpoint}&action=processOrders&user_token=${this.userToken}`, {
                method: 'POST'
            });
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(`${data.processed_count || 0} sipariş işlendi!`);
                await this.updateRecentOrders();
                await this.updateMetrics();
            } else {
                this.showError(data.error || 'Sipariş işleme hatası');
            }
        } catch (error) {
            console.error('❌ N11 order processing hatası:', error);
            this.showError('Sipariş işleme sırasında hata oluştu');
        }
    }

    async manageCategories() {
        console.log('📂 N11 kategori yönetimi açılıyor...');
        window.open(`${this.apiEndpoint}&action=categories&user_token=${this.userToken}`, '_blank');
    }

    async openSettings() {
        console.log('⚙️ N11 ayarları açılıyor...');
        window.open(`/admin/index.php?route=extension/module/n11&user_token=${this.userToken}`, '_blank');
    }

    async testAPI() {
        console.log('🔍 N11 API testi başlatılıyor...');
        try {
            await this.testOpenCartAPI();
            this.showSuccess('N11 API bağlantısı başarılı!');
        } catch (error) {
            this.showError('N11 API bağlantı hatası: ' + error.message);
        }
    }

    async viewCommissionDetails() {
        console.log('📊 N11 komisyon detayları açılıyor...');
        window.open(`${this.apiEndpoint}&action=commissionReport&user_token=${this.userToken}`, '_blank');
    }

    /**
     * Update UI helper functions
     */
    updateConnectionStatus(type, message) {
        // UI güncellemesi - N11 specific
        console.log(`N11 Connection Status: ${type} - ${message}`);
    }

    /**
     * Utility functions
     */
    animateCounter(elementId, targetValue, prefix = '', decimals = 0) {
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
            
            const formattedValue = decimals > 0 ? 
                currentValue.toFixed(decimals) : 
                Math.floor(currentValue).toLocaleString('tr-TR');
            
            element.textContent = prefix + formattedValue;

            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };

        animate();
    }

    getOrderStatusClass(status) {
        const statusMap = {
            'new': 'bg-primary',
            'approved': 'bg-info',
            'preparing': 'bg-warning',
            'shipped': 'bg-success',
            'delivered': 'bg-success',
            'cancelled': 'bg-danger',
            'returned': 'bg-secondary'
        };
        return statusMap[status] || 'bg-secondary';
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
                console.error('❌ N11 sales chart güncelleme hatası:', error);
            }
        }
    }

    showSuccess(message) {
        this.showToast(message, 'success');
    }

    showError(message) {
        this.showToast(message, 'error');
    }

    showInfo(message) {
        this.showToast(message, 'info');
    }

    showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'error' ? 'danger' : type === 'info' ? 'info' : 'success'} alert-dismissible fade show position-fixed`;
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
        
        console.log('🧹 N11 Integration temizlendi');
    }
}

// Global functions for HTML onclick events
window.editN11Product = function(n11Id) {
    console.log('✏️ N11 ürün düzenleme:', n11Id);
    window.open(`/admin/index.php?route=extension/module/n11/product&n11_id=${n11Id}`, '_blank');
};

window.viewN11Order = function(orderId) {
    console.log('👁️ N11 sipariş görüntüleme:', orderId);
    window.open(`/admin/index.php?route=extension/module/n11/order&order_id=${orderId}`, '_blank');
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.n11Integration = new N11Integration();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.n11Integration) {
        window.n11Integration.destroy();
    }
});

// Export for use in other modules
window.N11Integration = N11Integration; 