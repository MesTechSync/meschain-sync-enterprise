/**
 * Ozon Russian Marketplace Integration
 * MesChain-Sync Frontend Module v3.0 - OpenCart Integration
 * 
 * Features:
 * - OpenCart Admin API integration
 * - Ozon Seller API management
 * - Russian Ruble currency support
 * - Multi-region logistics (Moscow, SPB, etc.)
 * - Multi-language support (RU/EN/TR)
 * - Ozon Express delivery
 * - Real-time analytics
 * - Regional compliance
 */
class OzonIntegration {
    constructor() {
        // OpenCart API Configuration
        this.apiEndpoint = '/admin/index.php?route=extension/module/ozon';
        this.userToken = this.extractUserToken();
        this.connectionStatus = 'testing';
        this.lastDataUpdate = null;
        this.products = [];
        this.orders = [];
        this.regions = [];
        this.metrics = {
            totalSales: 0,
            activeProducts: 0,
            pendingOrders: 0,
            sellerRating: 0,
            moscowSales: 0,
            spbSales: 0,
            regionsales: 0,
            expressCoverage: 0
        };
        
        // Ozon specific configurations
        this.ozonConfig = {
            apiVersion: 'v4.0',
            marketplace: 'ozon',
            currency: 'RUB',
            defaultLocale: 'ru-RU',
            supportedLocales: ['ru-RU', 'en-US', 'tr-TR'],
            timezone: 'Europe/Moscow',
            brandColors: {
                primary: '#005BFF',
                secondary: '#3D7BFF',
                orange: '#FF6A00',
                green: '#00C851',
                gray: '#F5F5F5'
            },
            regions: [
                'Moscow', 'St. Petersburg', 'Novosibirsk', 'Yekaterinburg',
                'Kazan', 'Nizhny Novgorod', 'Chelyabinsk', 'Samara',
                'Omsk', 'Rostov-on-Don', 'Ufa', 'Krasnoyarsk'
            ],
            deliveryTypes: [
                'ozon_express', 'pickup_point', 'courier', 'post'
            ],
            deliveryTypeNames: {
                'ozon_express': 'Ozon Express',
                'pickup_point': 'Pickup Point',
                'courier': 'Courier Delivery',
                'post': 'Russian Post'
            },
            categories: [
                'Электроника', 'Одежда и обувь', 'Дом и сад', 'Красота и здоровье',
                'Детские товары', 'Спорт и отдых', 'Автотовары', 'Книги',
                'Зоотовары', 'Продукты питания', 'Бытовая техника', 'Мебель'
            ],
            commissionRanges: {
                'electronics': { min: 5, max: 12 },
                'fashion': { min: 8, max: 18 },
                'home': { min: 6, max: 15 },
                'books': { min: 15, max: 25 }
            }
        };

        // Chart instances
        this.charts = {
            sales: null,
            regional: null
        };

        // Polling intervals
        this.pollingIntervals = {
            apiStatus: null,
            salesData: null,
            orders: null,
            regional: null
        };

        console.log('🇷🇺 Ozon Russian Marketplace Integration v3.0 başlatılıyor...');
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
     * Initialize the Ozon dashboard
     */
    async init() {
        try {
            console.log('🚀 Ozon dashboard başlatılıyor...');
            
            // Test OpenCart API connection
            await this.testOpenCartAPI();
            
            // Initialize UI components
            this.setupEventListeners();
            await this.loadInitialData();
            
            // Start real-time updates
            this.startRealTimeUpdates();
            
            console.log('✅ Ozon dashboard başarıyla yüklendi!');
            
        } catch (error) {
            console.error('❌ Ozon dashboard hatası:', error);
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
                this.updateConnectionStatus('success', 'Ozon API bağlantısı başarılı!');
                console.log('✅ OpenCart Ozon API bağlantısı başarılı');
                return true;
            } else {
                throw new Error(data.error || 'Ozon API bağlantı hatası');
            }
            
        } catch (error) {
            console.error('❌ OpenCart Ozon API test hatası:', error);
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
        window.optimizeForOzonExpress = () => this.optimizeForOzonExpress();
        window.syncInventory = () => this.syncInventory();
        window.updatePrices = () => this.updatePrices();
        window.manageOzonExpress = () => this.manageOzonExpress();
        window.processOrders = () => this.processOrders();
        window.manageRegions = () => this.manageRegions();
        window.openOzonSettings = () => this.openSettings();
        window.testOzonAPI = () => this.testAPI();
        window.viewRegionalReport = () => this.viewRegionalReport();
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
            
            // Load regional data
            await this.updateRegionalData();
            
            // Update last update time
            document.getElementById('last-update').textContent = new Date().toLocaleString('ru-RU');
            
        } catch (error) {
            console.error('❌ Ozon veri yükleme hatası:', error);
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
                
                // Update metric cards with Russian Ruble formatting
                this.animateCounter('ozon-sales', metrics.total_sales || 0, '₽');
                this.animateCounter('active-products', metrics.active_products || 0);
                this.animateCounter('pending-orders', metrics.pending_orders || 0);
                this.animateCounter('seller-rating', metrics.seller_rating || 0, '', 1);
                
                this.metrics = metrics;
            } else {
                console.warn('Ozon metrics data hatası:', data.error);
            }
            
        } catch (error) {
            console.error('❌ Ozon metrics güncelleme hatası:', error);
        }
    }

    /**
     * Initialize sales chart
     */
    async initializeSalesChart() {
        try {
            const response = await fetch(`${this.apiEndpoint}&action=getSalesData&user_token=${this.userToken}`);
            const data = await response.json();

            const ctx = document.getElementById('ozonSalesChart').getContext('2d');
            
            this.charts.sales = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.chart_data?.labels || ['7 дней назад', '6 дней назад', '5 дней назад', '4 дня назад', '3 дня назад', 'Вчера', 'Сегодня'],
                    datasets: [{
                        label: 'Ozon Продажи (₽)',
                        data: data.chart_data?.values || [45000, 52000, 48000, 58000, 62000, 67000, 61000],
                        backgroundColor: 'rgba(0, 91, 255, 0.1)',
                        borderColor: '#005BFF',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#005BFF',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }, {
                        label: 'Ozon Express (₽)',
                        data: data.express_data?.values || [18000, 22000, 19000, 25000, 27000, 30000, 28000],
                        backgroundColor: 'rgba(255, 106, 0, 0.1)',
                        borderColor: '#FF6A00',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#FF6A00',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
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
                            backgroundColor: 'rgba(0, 91, 255, 0.9)',
                            titleColor: 'white',
                            bodyColor: 'white',
                            borderColor: '#005BFF',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    return `${context.dataset.label}: ₽${context.parsed.y.toLocaleString('ru-RU')}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 91, 255, 0.1)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return '₽' + value.toLocaleString('ru-RU');
                                }
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(0, 91, 255, 0.05)'
                            }
                        }
                    }
                }
            });

        } catch (error) {
            console.error('❌ Ozon sales chart hatası:', error);
            this.showChartError('ozonSalesChart', 'Chart yüklenemedi');
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
                    const expressEnabled = product.ozon_express_enabled;
                    const expressBadge = expressEnabled ? 
                        '<span class="ozon-express-badge">Express</span>' : 
                        '<span class="badge bg-secondary">Обычная доставка</span>';
                    
                    const translationStatus = product.translation_status || 'ru';
                    const langBadge = translationStatus === 'multilang' ? 
                        '<span class="multi-lang-badge">RU/EN</span>' : 
                        `<span class="russian-region">${translationStatus.toUpperCase()}</span>`;
                    
                    html += `
                        <div class="product-item">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <h6 class="mb-1">${product.name}</h6>
                                    <small class="text-muted">Ozon ID: ${product.ozon_id || 'N/A'}</small>
                                    ${expressBadge}
                                    ${langBadge}
                                </div>
                                <div class="col-md-2">
                                    <span class="badge ${product.status === 'active' ? 'bg-success' : 'bg-secondary'}">
                                        ${product.status === 'active' ? 'Активен' : 'Неактивен'}
                                    </span>
                                </div>
                                <div class="col-md-2">
                                    <strong class="ruble-price">₽${product.price?.toLocaleString('ru-RU') || '0'}</strong>
                                    <br><small class="text-muted">Склад: ${product.stock || 0}</small>
                                </div>
                                <div class="col-md-2">
                                    <span class="russian-region">${product.region || 'Москва'}</span>
                                </div>
                                <div class="col-md-1">
                                    <button class="btn btn-sm ozon-btn" onclick="editOzonProduct('${product.ozon_id}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="text-center p-4"><p class="text-muted">Ozon товаров не найдено</p></div>';
            }

        } catch (error) {
            console.error('❌ Ozon products güncelleme hatası:', error);
            document.getElementById('products-container').innerHTML = 
                '<div class="text-center p-4"><p class="text-danger">Товары не загружены</p></div>';
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
                    const deliveryType = this.ozonConfig.deliveryTypeNames[order.delivery_type] || 'Standard';
                    const deliveryBadge = order.delivery_type === 'ozon_express' ? 'ozon-express-badge' : 'badge bg-secondary';
                    
                    html += `
                        <tr>
                            <td><strong>${order.order_number}</strong></td>
                            <td>${order.customer_name}</td>
                            <td>${order.product_name}</td>
                            <td><strong class="ruble-price">₽${order.total?.toLocaleString('ru-RU')}</strong></td>
                            <td><span class="russian-region">${order.region || 'Москва'}</span></td>
                            <td><span class="${deliveryBadge}">${deliveryType}</span></td>
                            <td><span class="badge ${statusClass}">${order.status_text}</span></td>
                            <td>${new Date(order.date_added).toLocaleDateString('ru-RU')}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewOzonOrder('${order.order_id}')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
                
                tbody.innerHTML = html;
            } else {
                tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">Заказы не найдены</td></tr>';
            }

        } catch (error) {
            console.error('❌ Ozon orders güncelleme hatası:', error);
            document.getElementById('recent-orders').innerHTML = 
                '<tr><td colspan="9" class="text-center text-danger">Заказы не загружены</td></tr>';
        }
    }

    /**
     * Update regional data
     */
    async updateRegionalData() {
        try {
            const response = await fetch(`${this.apiEndpoint}&action=getRegionalData&user_token=${this.userToken}`);
            const data = await response.json();

            if (data.success && data.regional_data) {
                const regional = data.regional_data;
                
                document.getElementById('moscow-sales').textContent = 
                    '₽' + (regional.moscow_sales || 0).toLocaleString('ru-RU');
                document.getElementById('spb-sales').textContent = 
                    '₽' + (regional.spb_sales || 0).toLocaleString('ru-RU');
                document.getElementById('regions-sales').textContent = 
                    '₽' + (regional.other_regions_sales || 0).toLocaleString('ru-RU');
                document.getElementById('express-coverage').textContent = 
                    '%' + (regional.express_coverage || 0);
                
                // Update currency and commission data
                document.getElementById('rub-rate').textContent = '₽1.00';
                document.getElementById('commission-rate').textContent = 
                    '%' + (regional.commission_rate || 0);
                document.getElementById('monthly-commission').textContent = 
                    '₽' + (regional.monthly_commission || 0).toLocaleString('ru-RU');
                document.getElementById('express-fees').textContent = 
                    '₽' + (regional.express_fees || 0).toLocaleString('ru-RU');
                
                // Update language support
                document.getElementById('translated-products').textContent = 
                    regional.translated_products || 0;
                document.getElementById('auto-translate').textContent = 
                    '%' + (regional.auto_translate_percentage || 0);
                
                document.getElementById('currency-update').textContent = new Date().toLocaleString('ru-RU');
            }

        } catch (error) {
            console.error('❌ Ozon regional data hatası:', error);
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

        // Update regional data every 5 minutes
        this.pollingIntervals.regional = setInterval(() => {
            this.updateRegionalData();
        }, 300000);

        // Update orders every 3 minutes
        this.pollingIntervals.orders = setInterval(() => {
            this.updateRecentOrders();
        }, 180000);

        console.log('🔄 Ozon real-time güncellemeler başlatıldı');
    }

    /**
     * Ozon-specific business logic functions
     */
    async refreshProducts() {
        console.log('🔄 Ozon товары обновляются...');
        document.getElementById('products-container').innerHTML = 
            '<div class="text-center p-4"><div class="loading-animation"></div> Обновление...</div>';
        await this.updateProducts();
        this.showSuccess('Ozon товары успешно обновлены!');
    }

    async optimizeForOzonExpress() {
        console.log('🚀 Ozon Express optimizasyonu başlatılıyor...');
        try {
            const response = await fetch(`${this.apiEndpoint}&action=optimizeOzonExpress&user_token=${this.userToken}`, {
                method: 'POST'
            });
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(`${data.optimized_count || 0} товар оптимизирован для Ozon Express!`);
                await this.updateProducts();
                await this.updateRegionalData();
            } else {
                this.showError(data.error || 'Ошибка оптимизации');
            }
        } catch (error) {
            console.error('❌ Ozon Express optimization hatası:', error);
            this.showError('Ошибка при оптимизации');
        }
    }

    async syncInventory() {
        console.log('🔄 Ozon склад синхронизируется...');
        try {
            const response = await fetch(`${this.apiEndpoint}&action=syncInventory&user_token=${this.userToken}`, {
                method: 'POST'
            });
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('Синхронизация склада Ozon завершена!');
                await this.updateMetrics();
                await this.updateProducts();
            } else {
                this.showError(data.error || 'Ошибка синхронизации');
            }
        } catch (error) {
            console.error('❌ Ozon sync hatası:', error);
            this.showError('Ошибка при синхронизации');
        }
    }

    async updatePrices() {
        console.log('💰 Ozon цены обновляются...');
        try {
            const response = await fetch(`${this.apiEndpoint}&action=updatePrices&user_token=${this.userToken}`, {
                method: 'POST'
            });
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(`${data.updated_count || 0} цен товаров обновлено!`);
                await this.updateProducts();
                await this.updateMetrics();
            } else {
                this.showError(data.error || 'Ошибка обновления цен');
            }
        } catch (error) {
            console.error('❌ Ozon price update hatası:', error);
            this.showError('Ошибка при обновлении цен');
        }
    }

    async manageOzonExpress() {
        console.log('🚚 Ozon Express yönetimi açılıyor...');
        window.open(`${this.apiEndpoint}&action=manageOzonExpress&user_token=${this.userToken}`, '_blank');
    }

    async processOrders() {
        console.log('📦 Ozon заказы обрабатываются...');
        try {
            const response = await fetch(`${this.apiEndpoint}&action=processOrders&user_token=${this.userToken}`, {
                method: 'POST'
            });
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(`${data.processed_count || 0} заказов обработано!`);
                await this.updateRecentOrders();
                await this.updateMetrics();
            } else {
                this.showError(data.error || 'Ошибка обработки заказов');
            }
        } catch (error) {
            console.error('❌ Ozon order processing hatası:', error);
            this.showError('Ошибка при обработке заказов');
        }
    }

    async manageRegions() {
        console.log('🗺️ Ozon bölge yönetimi açılıyor...');
        window.open(`${this.apiEndpoint}&action=manageRegions&user_token=${this.userToken}`, '_blank');
    }

    async openSettings() {
        console.log('⚙️ Ozon ayarları açılıyor...');
        window.open(`/admin/index.php?route=extension/module/ozon&user_token=${this.userToken}`, '_blank');
    }

    async testAPI() {
        console.log('🔍 Ozon API testi başlatılıyor...');
        try {
            await this.testOpenCartAPI();
            this.showSuccess('Ozon API подключение успешно!');
        } catch (error) {
            this.showError('Ошибка подключения к Ozon API: ' + error.message);
        }
    }

    async viewRegionalReport() {
        console.log('📊 Ozon bölgesel rapor açılıyor...');
        window.open(`${this.apiEndpoint}&action=regionalReport&user_token=${this.userToken}`, '_blank');
    }

    /**
     * Update UI helper functions
     */
    updateConnectionStatus(type, message) {
        console.log(`Ozon Connection Status: ${type} - ${message}`);
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
                Math.floor(currentValue).toLocaleString('ru-RU');
            
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
            'awaiting_approve': 'bg-info',
            'awaiting_packaging': 'bg-warning',
            'awaiting_deliver': 'bg-success',
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
                    if (data.express_data) {
                        this.charts.sales.data.datasets[1].data = data.express_data.values;
                    }
                    this.charts.sales.update('active');
                }
            } catch (error) {
                console.error('❌ Ozon sales chart güncelleme hatası:', error);
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
        
        console.log('🧹 Ozon Integration temizlendi');
    }
}

// Global functions for HTML onclick events
window.editOzonProduct = function(ozonId) {
    console.log('✏️ Ozon товар редактирование:', ozonId);
    window.open(`/admin/index.php?route=extension/module/ozon/product&ozon_id=${ozonId}`, '_blank');
};

window.viewOzonOrder = function(orderId) {
    console.log('👁️ Ozon заказ просмотр:', orderId);
    window.open(`/admin/index.php?route=extension/module/ozon/order&order_id=${orderId}`, '_blank');
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.ozonIntegration = new OzonIntegration();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.ozonIntegration) {
        window.ozonIntegration.destroy();
    }
});

// Export for use in other modules
window.OzonIntegration = OzonIntegration; 