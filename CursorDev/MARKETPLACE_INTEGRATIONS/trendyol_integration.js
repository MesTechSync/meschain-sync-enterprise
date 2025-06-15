/**
 * Trendyol Turkish Marketplace Integration
 * MesChain-Sync Frontend Module v3.0 - OpenCart Integration
 * 
 * Features:
 * - OpenCart Admin API integration
 * - Turkish Lira formatting
 * - Campaign management
 * - Real-time order tracking
 * - Webhook management
 * - Turkish marketplace specifics
 */
class TrendyolIntegration {
    constructor() {        // OpenCart API Configuration
        this.apiEndpoint = '/admin/index.php?route=extension/module/trendyol/api';
        this.userToken = this.extractUserToken();
        this.connectionStatus = 'testing';        this.lastDataUpdate = null;
        this.campaigns = [];
        this.products = [];
        this.webhookStatus = {
            enabled: false,
            events_count: 0,
            last_event: null,
            configuration: {}
        };
        this.metrics = {
            monthlyRevenue: 0,
            activeProducts: 0,
            pendingOrders: 0,
            sellerRating: 0,
            campaignSales: 0,
            dailyTarget: 15000 // TL
        };
        
        // Trendyol specific configurations
        this.trendyolConfig = {
            apiVersion: '2.0',
            marketplace: 'trendyol',
            currency: 'TRY',
            locale: 'tr-TR',
            timezone: 'Europe/Istanbul',
            brandColors: {
                primary: '#FF6000',
                secondary: '#FF8533',
                accent: '#FFB366'
            },
            deliveryTypes: ['fast', 'standard', 'cargo'],
            categoryMappings: {
                'electronics': 'Elektronik',
                'fashion': 'Giyim & Aksesuar',
                'home': 'Ev & Yaşam',
                'cosmetics': 'Kozmetik',
                'sports': 'Spor & Outdoor',
                'books': 'Kitap & Müzik'
            }
        };

        // Chart instances
        this.charts = {
            sales: null
        };        // Polling intervals
        this.pollingIntervals = {
            apiStatus: null,
            salesData: null,
            campaigns: null,
            orders: null,
            webhooks: null
        };

        console.log('🛍️ Trendyol Integration v3.0 başlatılıyor...');
        this.init();
    }

    /**
     * Extract user_token from URL for OpenCart API calls
     */
    extractUserToken() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('user_token') || '';
    }    /**
     * Initialize the Trendyol dashboard
     */
    async init() {
        try {
            console.log('🚀 Trendyol dashboard başlatılıyor...');
            
            // Test OpenCart API connection
            await this.testOpenCartAPI();
            
            // Initialize UI components
            this.setupEventListeners();
            await this.loadInitialData();
            
            // Initialize webhook management
            await this.initializeWebhooks();
            
            // Start real-time updates
            this.startRealTimeUpdates();
            
            console.log('✅ Trendyol dashboard başarıyla yüklendi!');
            
        } catch (error) {
            console.error('❌ Trendyol dashboard hatası:', error);
            this.showError('Dashboard yüklenirken bir hata oluştu');
        }
    }

    /**
     * Test OpenCart API connection
     */
    async testOpenCartAPI() {
        try {
            const response = await fetch(`${this.apiEndpoint}&action=test&user_token=${this.userToken}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`OpenCart API error: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success) {
                this.connectionStatus = 'connected';
                console.log('✅ OpenCart Trendyol API bağlantısı başarılı');
                return true;
            } else {
                throw new Error(data.error || 'API bağlantı hatası');
            }
            
        } catch (error) {
            console.error('❌ OpenCart API test hatası:', error);
            this.connectionStatus = 'disconnected';
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
        window.manageComplaints = () => this.manageComplaints();
        window.manageCampaigns = () => this.manageCampaigns();
        window.openTrendyolSettings = () => this.openSettings();
        window.testTrendyolAPI = () => this.testAPI();
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
            
            // Update last update time
            document.getElementById('last-update').textContent = new Date().toLocaleString('tr-TR');
            
        } catch (error) {
            console.error('❌ Veri yükleme hatası:', error);
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
                this.animateCounter('trendyol-sales', metrics.monthly_sales || 0, '₺');
                this.animateCounter('active-products', metrics.active_products || 0);
                this.animateCounter('pending-orders', metrics.pending_orders || 0);
                this.animateCounter('seller-rating', metrics.seller_rating || 0, '', 1);
                
                this.metrics = metrics;
            } else {
                console.warn('Metrics data hatası:', data.error);
            }
            
        } catch (error) {
            console.error('❌ Metrics güncelleme hatası:', error);
        }
    }

    /**
     * Initialize sales chart
     */
    async initializeSalesChart() {
        try {
            const response = await fetch(`${this.apiEndpoint}&action=getSalesData&user_token=${this.userToken}`);
            const data = await response.json();

            const ctx = document.getElementById('trendyolSalesChart').getContext('2d');
            
            this.charts.sales = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.chart_data?.labels || ['Son 7 Gün', 'Son 6 Gün', 'Son 5 Gün', 'Son 4 Gün', 'Son 3 Gün', 'Dün', 'Bugün'],
                    datasets: [{
                        label: 'Trendyol Satışları (₺)',
                        data: data.chart_data?.values || [2500, 3200, 2800, 4100, 3600, 5200, 4800],
                        backgroundColor: 'rgba(255, 96, 0, 0.1)',
                        borderColor: '#FF6000',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#FF6000',
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
                            backgroundColor: 'rgba(255, 96, 0, 0.9)',
                            titleColor: 'white',
                            bodyColor: 'white',
                            borderColor: '#FF6000',
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
                                color: 'rgba(255, 96, 0, 0.1)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return '₺' + value.toLocaleString('tr-TR');
                                }
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(255, 96, 0, 0.05)'
                            }
                        }
                    }
                }
            });

        } catch (error) {
            console.error('❌ Sales chart hatası:', error);
            this.showChartError('trendyolSalesChart', 'Chart yüklenemedi');
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
                                    <small class="text-muted">SKU: ${product.sku || 'N/A'}</small>
                                </div>
                                <div class="col-md-2">
                                    <span class="badge ${product.status === 'active' ? 'bg-success' : 'bg-secondary'}">
                                        ${product.status === 'active' ? 'Aktif' : 'Pasif'}
                                    </span>
                                </div>
                                <div class="col-md-2">
                                    <strong>₺${product.price?.toLocaleString('tr-TR') || '0'}</strong>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-sm trendyol-btn" onclick="editProduct(${product.id})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="text-center p-4"><p class="text-muted">Ürün bulunamadı</p></div>';
            }

        } catch (error) {
            console.error('❌ Products güncelleme hatası:', error);
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
                    const statusClass = this.getStatusClass(order.status);
                    html += `
                        <tr>
                            <td><strong>${order.order_number}</strong></td>
                            <td>${order.customer_name}</td>
                            <td>${order.product_name}</td>
                            <td><strong>₺${order.total?.toLocaleString('tr-TR')}</strong></td>
                            <td><span class="badge ${statusClass}">${order.status_text}</span></td>
                            <td>${new Date(order.date_added).toLocaleDateString('tr-TR')}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewOrder('${order.order_id}')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
                
                tbody.innerHTML = html;
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Sipariş bulunamadı</td></tr>';
            }

        } catch (error) {
            console.error('❌ Orders güncelleme hatası:', error);
            document.getElementById('recent-orders').innerHTML = 
                '<tr><td colspan="7" class="text-center text-danger">Siparişler yüklenemedi</td></tr>';
        }
    }

    /**
     * Start real-time updates
     */
    startRealTimeUpdates() {
        // Update metrics every 60 seconds
        this.pollingIntervals.apiStatus = setInterval(() => {
            this.updateMetrics();
        }, 60000);

        // Update sales data every 5 minutes
        this.pollingIntervals.salesData = setInterval(() => {
            this.updateSalesChart();
        }, 300000);

        console.log('🔄 Real-time güncellemeler başlatıldı');
    }

    /**
     * Update sales chart data
     */
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
                console.error('❌ Sales chart güncelleme hatası:', error);
            }
        }
    }

    /**
     * Animate counter with Turkish formatting
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

    /**
     * Get status class for order status
     */
    getStatusClass(status) {
        const statusMap = {
            'created': 'bg-primary',
            'approved': 'bg-info',
            'picking': 'bg-warning',
            'invoiced': 'bg-success',
            'shipped': 'bg-success',
            'delivered': 'bg-success',
            'cancelled': 'bg-danger',
            'returned': 'bg-secondary'
        };
        return statusMap[status] || 'bg-secondary';
    }

    /**
     * Marketplace specific functions
     */
    async refreshProducts() {
        console.log('🔄 Ürünler yenileniyor...');
        document.getElementById('products-container').innerHTML = 
            '<div class="text-center p-4"><i class="fas fa-spinner fa-spin"></i> Yenileniyor...</div>';
        await this.updateProducts();
        this.showSuccess('Ürünler başarıyla yenilendi!');
    }

    async syncInventory() {
        console.log('🔄 Stok senkronizasyonu başlatılıyor...');
        try {
            const response = await fetch(`${this.apiEndpoint}&action=syncInventory&user_token=${this.userToken}`, {
                method: 'POST'
            });
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('Stok senkronizasyonu tamamlandı!');
                await this.updateMetrics();
            } else {
                this.showError(data.error || 'Senkronizasyon hatası');
            }
        } catch (error) {
            console.error('❌ Sync hatası:', error);
            this.showError('Senkronizasyon sırasında hata oluştu');
        }
    }

    async updatePrices() {
        console.log('💰 Fiyatlar güncelleniyor...');
        this.showInfo('Fiyat güncellemesi geliştiriliyor...');
    }

    async processOrders() {
        console.log('📦 Siparişler işleniyor...');
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
            console.error('❌ Order processing hatası:', error);
            this.showError('Sipariş işleme sırasında hata oluştu');
        }
    }

    async manageComplaints() {
        console.log('⚠️ Şikayet yönetimi açılıyor...');
        window.open(`${this.apiEndpoint}&action=complaints&user_token=${this.userToken}`, '_blank');
    }

    async manageCampaigns() {
        console.log('📢 Kampanya yönetimi açılıyor...');
        window.open(`${this.apiEndpoint}&action=campaigns&user_token=${this.userToken}`, '_blank');
    }

    async openSettings() {
        console.log('⚙️ Trendyol ayarları açılıyor...');
        window.open(`/admin/index.php?route=extension/module/trendyol&user_token=${this.userToken}`, '_blank');
    }

    async testAPI() {
        console.log('🔍 API testi başlatılıyor...');
        try {
            await this.testOpenCartAPI();
            this.showSuccess('Trendyol API bağlantısı başarılı!');
        } catch (error) {
            this.showError('API bağlantı hatası: ' + error.message);
        }
    }

    /**
     * Webhook Management System
     * Handles webhook configuration, testing, and monitoring
     */
    
    /**
     * Initialize webhook management
     */
    async initializeWebhooks() {
        try {
            console.log('🔗 Webhook sistemi başlatılıyor...');
            
            // Load webhook status
            await this.loadWebhookStatus();
            
            // Setup webhook event listeners
            this.setupWebhookEventListeners();
            
            // Start webhook monitoring
            this.startWebhookMonitoring();
            
            console.log('✅ Webhook sistemi başarıyla başlatıldı');
            
        } catch (error) {
            console.error('❌ Webhook sistemi hatası:', error);
            this.showError('Webhook sistemi başlatılamadı');
        }
    }

    /**
     * Load current webhook configuration and status
     */
    async loadWebhookStatus() {
        try {
            const response = await fetch(`${this.apiEndpoint}&action=getWebhookStatus&user_token=${this.userToken}`);
            const data = await response.json();

            if (data.success) {
                this.webhookStatus = data.status;
                this.updateWebhookStatusUI(data.status);
                console.log('📊 Webhook durumu yüklendi:', data.status);
            } else {
                console.warn('Webhook durumu alınamadı:', data.error);
            }
            
        } catch (error) {
            console.error('❌ Webhook durumu yüklenirken hata:', error);
        }
    }

    /**
     * Update webhook status in the UI
     */
    updateWebhookStatusUI(status) {
        // Update webhook indicators
        const indicators = {
            'webhook-status': status.enabled ? 'connected' : 'disconnected',
            'webhook-events': status.events_count || 0,
            'webhook-last-event': status.last_event || 'Henüz event yok'
        };

        Object.entries(indicators).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) {
                if (id === 'webhook-status') {
                    element.className = `badge ${value === 'connected' ? 'bg-success' : 'bg-secondary'}`;
                    element.textContent = value === 'connected' ? 'Aktif' : 'Pasif';
                } else {
                    element.textContent = value;
                }
            }
        });

        // Update webhook configuration toggles
        if (status.configuration) {
            Object.entries(status.configuration).forEach(([eventType, enabled]) => {
                const toggle = document.getElementById(`webhook-${eventType}`);
                if (toggle) {
                    toggle.checked = enabled;
                }
            });
        }
    }

    /**
     * Setup webhook event listeners
     */
    setupWebhookEventListeners() {
        // Webhook toggle switches
        const webhookTypes = ['orders', 'products', 'inventory', 'payments'];
        
        webhookTypes.forEach(type => {
            const toggle = document.getElementById(`webhook-${type}`);
            if (toggle) {
                toggle.addEventListener('change', (e) => {
                    this.toggleWebhook(type, e.target.checked);
                });
            }
        });

        // Webhook test button
        const testButton = document.getElementById('test-webhook-btn');
        if (testButton) {
            testButton.addEventListener('click', () => this.testWebhook());
        }

        // Webhook configuration button
        const configButton = document.getElementById('configure-webhooks-btn');
        if (configButton) {
            configButton.addEventListener('click', () => this.openWebhookConfiguration());
        }

        // Global webhook functions for HTML onclick events
        window.testTrendyolWebhook = () => this.testWebhook();
        window.configureWebhooks = () => this.openWebhookConfiguration();
        window.viewWebhookLogs = () => this.viewWebhookLogs();
        window.clearWebhookLogs = () => this.clearWebhookLogs();
    }

    /**
     * Toggle webhook for specific event type
     */
    async toggleWebhook(eventType, enabled) {
        try {
            console.log(`🔄 ${eventType} webhook ${enabled ? 'etkinleştiriliyor' : 'devre dışı bırakılıyor'}...`);
            
            const response = await fetch(`${this.apiEndpoint}&action=toggleWebhook&user_token=${this.userToken}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    event_type: eventType,
                    enabled: enabled
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(`${eventType} webhook ${enabled ? 'etkinleştirildi' : 'devre dışı bırakıldı'}`);
                await this.loadWebhookStatus(); // Refresh status
            } else {
                throw new Error(data.error || 'Webhook ayarı güncellenemedi');
            }
            
        } catch (error) {
            console.error('❌ Webhook toggle hatası:', error);
            this.showError(`Webhook ayarı güncellenirken hata: ${error.message}`);
            
            // Revert toggle state
            const toggle = document.getElementById(`webhook-${eventType}`);
            if (toggle) {
                toggle.checked = !enabled;
            }
        }
    }

    /**
     * Test webhook connectivity
     */
    async testWebhook() {
        try {
            console.log('🧪 Webhook testi başlatılıyor...');
            this.showInfo('Webhook bağlantısı test ediliyor...');
            
            const response = await fetch(`${this.apiEndpoint}&action=testWebhook&user_token=${this.userToken}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(`Webhook testi başarılı! Yanıt süresi: ${data.response_time || 'N/A'}ms`);
                
                // Update test results in UI
                this.updateWebhookTestResults(data.test_results);
            } else {
                throw new Error(data.error || 'Webhook testi başarısız');
            }
            
        } catch (error) {
            console.error('❌ Webhook test hatası:', error);
            this.showError(`Webhook testi başarısız: ${error.message}`);
        }
    }

    /**
     * Update webhook test results in UI
     */
    updateWebhookTestResults(results) {
        const container = document.getElementById('webhook-test-results');
        if (!container) return;

        let html = '<div class="webhook-test-results mt-3">';
        html += '<h6>Test Sonuçları:</h6>';
        html += '<div class="list-group list-group-flush">';
        
        if (results && results.length > 0) {
            results.forEach(result => {
                const statusClass = result.success ? 'text-success' : 'text-danger';
                const icon = result.success ? 'fa-check-circle' : 'fa-times-circle';
                
                html += `
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas ${icon} ${statusClass}"></i>
                            <span class="ms-2">${result.test_name}</span>
                        </div>
                        <div class="text-end">
                            <small class="${statusClass}">${result.message}</small>
                            ${result.response_time ? `<br><small class="text-muted">${result.response_time}ms</small>` : ''}
                        </div>
                    </div>
                `;
            });
        } else {
            html += '<div class="list-group-item text-center text-muted">Test sonucu bulunamadı</div>';
        }
        
        html += '</div></div>';
        container.innerHTML = html;
    }

    /**
     * Open webhook configuration modal
     */
    async openWebhookConfiguration() {
        try {
            console.log('⚙️ Webhook yapılandırması açılıyor...');
            
            // Load current configuration
            const response = await fetch(`${this.apiEndpoint}&action=getWebhookConfiguration&user_token=${this.userToken}`);
            const data = await response.json();
            
            if (data.success) {
                this.showWebhookConfigurationModal(data.configuration);
            } else {
                throw new Error(data.error || 'Webhook yapılandırması alınamadı');
            }
            
        } catch (error) {
            console.error('❌ Webhook configuration hatası:', error);
            this.showError('Webhook yapılandırması açılamadı');
        }
    }

    /**
     * Show webhook configuration modal
     */
    showWebhookConfigurationModal(config) {
        // Create modal HTML
        const modalHtml = `
            <div class="modal fade" id="webhookConfigModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-cog text-primary"></i>
                                Webhook Yapılandırması
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Webhook URL</h6>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="webhook-url" 
                                               value="${config.webhook_url || ''}" readonly>
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyWebhookUrl()">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6>Secret Key</h6>
                                    <div class="input-group mb-3">
                                        <input type="password" class="form-control" id="webhook-secret" 
                                               value="${config.secret_key || ''}" readonly>
                                        <button class="btn btn-outline-secondary" type="button" onclick="toggleSecretVisibility()">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <h6>Event Subscriptions</h6>
                            <div class="row">
                                ${this.generateEventSubscriptionHTML(config.events || {})}
                            </div>
                            
                            <h6 class="mt-3">Webhook Logs</h6>
                            <div id="webhook-logs-container">
                                <div class="text-center p-3">
                                    <button class="btn trendyol-btn" onclick="loadWebhookLogs()">
                                        <i class="fas fa-sync-alt"></i> Logları Yükle
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                            <button type="button" class="btn trendyol-btn" onclick="saveWebhookConfiguration()">
                                <i class="fas fa-save"></i> Kaydet
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if any
        const existingModal = document.getElementById('webhookConfigModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Add modal to page
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('webhookConfigModal'));
        modal.show();

        // Setup modal-specific functions
        this.setupWebhookModalFunctions();
    }

    /**
     * Generate event subscription HTML for configuration modal
     */
    generateEventSubscriptionHTML(events) {
        const eventTypes = [
            { key: 'orders', label: 'Sipariş Events', description: 'Yeni sipariş, sipariş güncellemeleri' },
            { key: 'products', label: 'Ürün Events', description: 'Ürün onayı, red, güncelleme' },
            { key: 'inventory', label: 'Stok Events', description: 'Stok değişiklikleri' },
            { key: 'payments', label: 'Ödeme Events', description: 'Ödeme durumu değişiklikleri' }
        ];

        let html = '';
        eventTypes.forEach(eventType => {
            const isEnabled = events[eventType.key] || false;
            html += `
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="event-${eventType.key}" 
                                       ${isEnabled ? 'checked' : ''}>
                                <label class="form-check-label" for="event-${eventType.key}">
                                    <strong>${eventType.label}</strong>
                                </label>
                            </div>
                            <small class="text-muted">${eventType.description}</small>
                        </div>
                    </div>
                </div>
            `;
        });

        return html;
    }

    /**
     * Setup webhook modal specific functions
     */
    setupWebhookModalFunctions() {
        // Copy webhook URL function
        window.copyWebhookUrl = () => {
            const urlInput = document.getElementById('webhook-url');
            if (urlInput) {
                urlInput.select();
                document.execCommand('copy');
                this.showSuccess('Webhook URL kopyalandı!');
            }
        };

        // Toggle secret visibility
        window.toggleSecretVisibility = () => {
            const secretInput = document.getElementById('webhook-secret');
            const button = secretInput.nextElementSibling.querySelector('i');
            
            if (secretInput.type === 'password') {
                secretInput.type = 'text';
                button.className = 'fas fa-eye-slash';
            } else {
                secretInput.type = 'password';
                button.className = 'fas fa-eye';
            }
        };

        // Load webhook logs
        window.loadWebhookLogs = () => this.loadWebhookLogs();

        // Save webhook configuration
        window.saveWebhookConfiguration = () => this.saveWebhookConfiguration();
    }

    /**
     * Start webhook monitoring for real-time status updates
     */
    startWebhookMonitoring() {
        // Update webhook status every 30 seconds
        this.pollingIntervals.webhooks = setInterval(() => {
            this.loadWebhookStatus();
        }, 30000);

        console.log('🔄 Webhook monitoring başlatıldı');
    }

    /**
     * Load and display webhook logs
     */
    async loadWebhookLogs() {
        try {
            const response = await fetch(`${this.apiEndpoint}&action=getWebhookLogs&user_token=${this.userToken}`);
            const data = await response.json();

            const container = document.getElementById('webhook-logs-container');
            if (!container) return;

            if (data.success && data.logs) {
                this.displayWebhookLogs(data.logs, container);
            } else {
                container.innerHTML = '<div class="text-center p-3 text-muted">Log bulunamadı</div>';
            }
            
        } catch (error) {
            console.error('❌ Webhook logs yüklenirken hata:', error);
            const container = document.getElementById('webhook-logs-container');
            if (container) {
                container.innerHTML = '<div class="text-center p-3 text-danger">Loglar yüklenemedi</div>';
            }
        }
    }

    /**
     * Display webhook logs in the container
     */
    displayWebhookLogs(logs, container) {
        let html = '<div class="webhook-logs-list" style="max-height: 300px; overflow-y: auto;">';
        
        if (logs.length > 0) {
            logs.forEach(log => {
                const statusClass = log.status === 'success' ? 'text-success' : 'text-danger';
                const icon = log.status === 'success' ? 'fa-check-circle' : 'fa-times-circle';
                
                html += `
                    <div class="d-flex justify-content-between align-items-start p-2 border-bottom">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center">
                                <i class="fas ${icon} ${statusClass} me-2"></i>
                                <strong>${log.event_type}</strong>
                                <span class="badge bg-secondary ms-2">${log.status}</span>
                            </div>
                            <small class="text-muted">${log.message}</small>
                        </div>
                        <small class="text-muted">${new Date(log.timestamp).toLocaleString('tr-TR')}</small>
                    </div>
                `;
            });
        } else {
            html += '<div class="text-center p-3 text-muted">Henüz webhook log kaydı yok</div>';
        }
        
        html += '</div>';
        
        // Add clear logs button
        html += `
            <div class="text-center mt-3">
                <button class="btn btn-sm btn-outline-danger" onclick="clearWebhookLogs()">
                    <i class="fas fa-trash"></i> Logları Temizle
                </button>
            </div>
        `;
        
        container.innerHTML = html;
    }

    /**
     * Save webhook configuration
     */
    async saveWebhookConfiguration() {
        try {
            const configuration = this.collectWebhookConfiguration();
            
            const response = await fetch(`${this.apiEndpoint}&action=saveWebhookConfiguration&user_token=${this.userToken}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(configuration)
            });

            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('Webhook yapılandırması kaydedildi!');
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('webhookConfigModal'));
                if (modal) {
                    modal.hide();
                }
                
                // Refresh webhook status
                await this.loadWebhookStatus();
            } else {
                throw new Error(data.error || 'Yapılandırma kaydedilemedi');
            }
            
        } catch (error) {
            console.error('❌ Webhook configuration save hatası:', error);
            this.showError(`Yapılandırma kaydedilirken hata: ${error.message}`);
        }
    }

    /**
     * Collect webhook configuration from modal form
     */
    collectWebhookConfiguration() {
        const configuration = {
            events: {}
        };

        // Collect event subscriptions
        const eventTypes = ['orders', 'products', 'inventory', 'payments'];
        eventTypes.forEach(eventType => {
            const checkbox = document.getElementById(`event-${eventType}`);
            if (checkbox) {
                configuration.events[eventType] = checkbox.checked;
            }
        });

        return configuration;
    }

    /**
     * Clear webhook logs
     */
    async clearWebhookLogs() {
        if (!confirm('Tüm webhook logları silinecek. Emin misiniz?')) {
            return;
        }

        try {
            const response = await fetch(`${this.apiEndpoint}&action=clearWebhookLogs&user_token=${this.userToken}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('Webhook logları temizlendi!');
                
                // Reload logs display
                await this.loadWebhookLogs();
            } else {
                throw new Error(data.error || 'Loglar temizlenemedi');
            }
            
        } catch (error) {
            console.error('❌ Webhook logs clear hatası:', error);
            this.showError(`Loglar temizlenirken hata: ${error.message}`);
        }
    }

    /**
     * View detailed webhook logs in separate window
     */
    async viewWebhookLogs() {
        console.log('📋 Webhook logları görüntüleniyor...');
        window.open(`${this.apiEndpoint}&action=viewWebhookLogs&user_token=${this.userToken}`, '_blank');
    }

    /**
     * Utility functions
     */
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
        // Create toast notification
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        toast.style.top = '20px';
        toast.style.right = '20px';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(toast);
        
        // Auto remove after 5 seconds
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
        
        console.log('🧹 Trendyol Integration temizlendi');
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.trendyolIntegration = new TrendyolIntegration();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.trendyolIntegration) {
        window.trendyolIntegration.destroy();
    }
});

// Export for use in other modules
window.TrendyolIntegration = TrendyolIntegration;