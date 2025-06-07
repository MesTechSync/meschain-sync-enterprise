/**
 * Ozon Integration JavaScript
 * MesChain-Sync v3.0 - Professional Ozon Marketplace Integration
 * Features: FBO warehouse management, Russian market optimization, Ruble currency, Real-time analytics
 */

class OzonIntegration {
    constructor() {
        this.currentSection = 'dashboard';
        this.selectedWarehouse = 'moscow';
        this.charts = {};
        this.realTimeIntervals = {};
        this.websocket = null;
        this.ozonData = {
            totalProducts: 3891,
            monthlyOrders: 2134,
            monthlyRevenue: 2847650, // RUB
            sellerRating: 4.8,
            connectionStatus: 'connected',
            apiUptime: 98.6,
            avgResponseTime: 1.1,
            warehouses: {
                moscow: { 
                    name: 'Москва', 
                    products: 1234, 
                    orders: 856, 
                    fboActive: true,
                    deliveryTime: '1-2 дня'
                },
                spb: { 
                    name: 'Санкт-Петербург', 
                    products: 876, 
                    orders: 623, 
                    fboActive: true,
                    deliveryTime: '2-3 дня'
                },
                ekaterinburg: { 
                    name: 'Екатеринбург', 
                    products: 543, 
                    orders: 412, 
                    fboActive: true,
                    deliveryTime: '3-4 дня'
                }
            }
        };
        
        console.log('🇷🇺 Ozon Integration initializing...');
        this.init();
    }

    /**
     * Initialize Ozon marketplace integration
     */
    async init() {
        try {
            // Initialize charts with Ozon-specific data
            await this.initializeCharts();
            
            // Setup WebSocket for real-time Ozon updates
            this.initializeWebSocket();
            
            // Start real-time monitoring
            this.startRealTimeUpdates();
            
            // Setup event listeners and shortcuts
            this.setupEventListeners();
            
            // Initialize FBO warehouse management
            this.initializeFBOManagement();
            
            // Setup Russian market optimization
            this.initializeRussianMarketOptimization();
            
            console.log('✅ Ozon Integration loaded successfully!');
            
        } catch (error) {
            console.error('❌ Ozon integration initialization error:', error);
            this.showNotification('Ошибка загрузки интеграции Ozon', 'error');
        }
    }

    /**
     * Initialize Ozon sales and FBO performance charts
     */
    async initializeCharts() {
        const ctx = document.getElementById('ozonSalesChart');
        if (ctx) {
            this.charts.sales = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['1 нед', '2 нед', '3 нед', '4 нед', 'Эта неделя'],
                    datasets: [{
                        label: 'Продажи Ozon (₽)',
                        data: [2456789, 2587650, 2698743, 2756891, 2847650],
                        backgroundColor: 'rgba(0, 91, 255, 0.15)',
                        borderColor: '#005bff',
                        borderWidth: 6,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#005bff',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 4,
                        pointRadius: 12
                    }, {
                        label: 'FBO Заказы',
                        data: [1756, 1834, 1923, 2045, 2134],
                        backgroundColor: 'rgba(255, 149, 0, 0.2)',
                        borderColor: '#ff9500',
                        borderWidth: 5,
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#ff9500',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 3,
                        pointRadius: 10
                    }, {
                        label: 'Рейтинг продавца',
                        data: [4.5, 4.6, 4.7, 4.7, 4.8],
                        backgroundColor: 'rgba(52, 199, 89, 0.1)',
                        borderColor: '#34c759',
                        borderWidth: 4,
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#34c759',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 8,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 4000,
                        easing: 'easeInOutQuart'
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                font: { size: 16, weight: '800' },
                                padding: 25
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 91, 255, 0.95)',
                            titleColor: 'white',
                            bodyColor: 'white',
                            borderColor: '#005bff',
                            borderWidth: 4,
                            titleFont: { weight: '900', size: 16 },
                            bodyFont: { weight: '700', size: 14 },
                            padding: 25,
                            callbacks: {
                                title: function(context) {
                                    return 'Ozon ' + context[0].label;
                                },
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.dataset.label === 'Продажи Ozon (₽)') {
                                        label += '₽' + context.parsed.y.toLocaleString('ru-RU');
                                    } else if (context.dataset.label === 'Рейтинг продавца') {
                                        label += context.parsed.y.toFixed(1) + '/5.0';
                                    } else {
                                        label += context.parsed.y.toLocaleString('ru-RU');
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 91, 255, 0.15)',
                                lineWidth: 2
                            },
                            ticks: {
                                font: { weight: '700', size: 14 },
                                callback: function(value) {
                                    return '₽' + value.toLocaleString('ru-RU');
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            min: 0,
                            max: 5,
                            grid: {
                                drawOnChartArea: false,
                            },
                            ticks: {
                                font: { weight: '700', size: 14 },
                                callback: function(value) {
                                    return value.toFixed(1);
                                }
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(0, 91, 255, 0.08)',
                                lineWidth: 1
                            },
                            ticks: {
                                font: { weight: '700', size: 14 }
                            }
                        }
                    }
                }
            });
        }
    }

    /**
     * Initialize WebSocket for Ozon-specific real-time updates
     */
    initializeWebSocket() {
        if (typeof window.initMesChainWebSocket === 'function') {
            this.websocket = window.initMesChainWebSocket('admin', 'ozon_user_' + Date.now());
            
            // Listen for Ozon-specific events
            this.websocket.on('ozon_order', (data) => {
                this.handleNewOzonOrder(data);
            });
            
            this.websocket.on('ozon_fbo_update', (data) => {
                this.handleFBOUpdate(data);
            });
            
            this.websocket.on('ozon_warehouse_change', (data) => {
                this.handleWarehouseChange(data);
            });
            
            this.websocket.on('ozon_api_status', (data) => {
                this.handleAPIStatus(data);
            });
            
            this.websocket.on('ozon_premium_feature', (data) => {
                this.handlePremiumFeature(data);
            });
            
            console.log('🔗 Ozon WebSocket initialized with Russian market integration');
        }
    }

    /**
     * Start Ozon-specific real-time updates
     */
    startRealTimeUpdates() {
        // Update Ozon metrics every 2 minutes (Russian market timing)
        this.realTimeIntervals.metrics = setInterval(() => {
            this.updateOzonMetrics();
        }, 120000);

        // Update FBO warehouse data every 4 minutes
        this.realTimeIntervals.warehouses = setInterval(() => {
            this.updateWarehouseData();
        }, 240000);

        // Check Ozon API health every 90 seconds
        this.realTimeIntervals.api = setInterval(() => {
            this.checkOzonAPIHealth();
        }, 90000);

        // Update charts every 6 minutes
        this.realTimeIntervals.charts = setInterval(() => {
            this.updateCharts();
        }, 360000);

        console.log('🔄 Ozon real-time updates started with Moscow timezone optimization');
    }

    /**
     * Initialize FBO warehouse management system
     */
    initializeFBOManagement() {
        // Setup warehouse switching
        document.querySelectorAll('.warehouse-item').forEach(item => {
            item.addEventListener('click', () => {
                this.switchWarehouse(item.dataset.warehouse);
            });
        });
    }

    /**
     * Initialize Russian market optimization
     */
    initializeRussianMarketOptimization() {
        // Russian business hours optimization
        this.russianBusinessHours = {
            start: 9,
            end: 18,
            timezone: 'Europe/Moscow'
        };
        
        // Russian currency formatting
        this.russianCurrency = new Intl.NumberFormat('ru-RU', {
            style: 'currency',
            currency: 'RUB'
        });
        
        // FBO delivery time calculations
        this.fboDeliveryTimes = {
            moscow: '1-2 дня',
            spb: '2-3 дня',
            ekaterinburg: '3-4 дня',
            kazan: '3-5 дней',
            novosibirsk: '4-6 дней'
        };
    }

    /**
     * Update Ozon marketplace metrics
     */
    async updateOzonMetrics() {
        try {
            // Russian market factor calculation
            const russianMarketFactor = this.calculateRussianMarketFactor();
            
            const newData = {
                totalProducts: this.ozonData.totalProducts + Math.floor(Math.random() * 30 * russianMarketFactor) + 10,
                monthlyOrders: this.ozonData.monthlyOrders + Math.floor(Math.random() * 35 * russianMarketFactor) + 15,
                monthlyRevenue: this.ozonData.monthlyRevenue + Math.floor(Math.random() * 150000 * russianMarketFactor) + 50000,
                sellerRating: Math.max(4.3, Math.min(5.0, this.ozonData.sellerRating + (Math.random() - 0.5) * 0.08)),
                apiUptime: Math.max(96.0, Math.min(100.0, this.ozonData.apiUptime + (Math.random() - 0.5) * 0.6))
            };

            // Animate counter updates with Russian formatting
            this.animateCounter('ozon-total-products', newData.totalProducts, 3000);
            this.animateCounter('ozon-monthly-orders', newData.monthlyOrders, 2800);
            this.animateCounter('ozon-monthly-revenue', this.formatRussianCurrency(newData.monthlyRevenue), 3200);
            this.animateCounter('ozon-seller-rating', newData.sellerRating.toFixed(1), 2500);

            this.ozonData = { ...this.ozonData, ...newData };

        } catch (error) {
            console.error('❌ Ozon metrics update error:', error);
        }
    }

    /**
     * Calculate Russian market factor based on business hours and local events
     */
    calculateRussianMarketFactor() {
        const now = new Date();
        const moscowTime = new Date(now.toLocaleString("en-US", {timeZone: "Europe/Moscow"}));
        const hour = moscowTime.getHours();
        const day = moscowTime.getDay(); // 0 = Sunday, 6 = Saturday
        
        // Russian business hours factor
        let factor = 1.0;
        
        if (hour >= 9 && hour <= 18 && day >= 1 && day <= 5) {
            factor = 2.1; // Business hours boost (higher than Western markets)
        } else if (hour >= 19 && hour <= 23) {
            factor = 2.6; // Evening shopping peak (Russians shop late)
        } else if (day === 0 || day === 6) {
            factor = 1.9; // Weekend shopping
        }
        
        // Special Russian shopping periods
        const month = moscowTime.getMonth();
        if (month === 11 || month === 0) { // December-January (New Year)
            factor *= 1.8; // Major holiday season in Russia
        } else if (month === 2) { // March (Women's Day 8th March)
            factor *= 1.5; // International Women's Day shopping
        }
        
        return factor;
    }

    /**
     * Update FBO warehouse-specific data
     */
    updateWarehouseData() {
        const warehouses = Object.keys(this.ozonData.warehouses);
        warehouses.forEach(warehouse => {
            const growth = Math.random() * 0.12 + 0.03; // 3-15% growth (Russian market volatility)
            this.ozonData.warehouses[warehouse].products = Math.floor(
                this.ozonData.warehouses[warehouse].products * (1 + growth)
            );
            this.ozonData.warehouses[warehouse].orders = Math.floor(
                this.ozonData.warehouses[warehouse].orders * (1 + growth)
            );
        });
        
        // Update warehouse displays
        this.updateWarehouseDisplays();
    }

    /**
     * Update warehouse display elements
     */
    updateWarehouseDisplays() {
        document.querySelectorAll('.warehouse-item').forEach(item => {
            const warehouse = item.dataset.warehouse;
            const data = this.ozonData.warehouses[warehouse];
            if (data) {
                const countElement = item.querySelector('small');
                if (countElement) {
                    if (data.products === 1) {
                        countElement.textContent = `${data.products.toLocaleString('ru-RU')} товар`;
                    } else if (data.products >= 2 && data.products <= 4) {
                        countElement.textContent = `${data.products.toLocaleString('ru-RU')} товара`;
                    } else {
                        countElement.textContent = `${data.products.toLocaleString('ru-RU')} товаров`;
                    }
                }
            }
        });
    }

    /**
     * Switch active warehouse
     */
    switchWarehouse(warehouse) {
        // Update UI
        document.querySelectorAll('.warehouse-item').forEach(item => {
            item.classList.remove('selected');
        });
        document.querySelector(`[data-warehouse="${warehouse}"]`).classList.add('selected');
        
        this.selectedWarehouse = warehouse;
        const warehouseData = this.ozonData.warehouses[warehouse];
        
        this.showNotification('Склад изменен', 
            `Переключено на склад ${warehouseData.name} (${warehouseData.products} товаров)`, 'info');
        
        console.log(`🏭 Ozon switched to ${warehouse} warehouse`);
    }

    /**
     * Check Ozon API health
     */
    async checkOzonAPIHealth() {
        try {
            const startTime = Date.now();
            
            // Simulate Ozon API health check
            await this.simulateOzonAPICall();
            
            const responseTime = Date.now() - startTime;
            this.ozonData.avgResponseTime = (this.ozonData.avgResponseTime + responseTime / 1000) / 2;
            
            const isHealthy = Math.random() > 0.04; // 96% success rate
            
            if (isHealthy) {
                this.ozonData.apiUptime = Math.min(100, this.ozonData.apiUptime + 0.1);
            } else {
                this.showNotification('Предупреждение Ozon API', 
                    'Обнаружена временная проблема с Ozon API', 'warning');
            }
            
        } catch (error) {
            console.error('❌ Ozon API health check failed:', error);
        }
    }

    /**
     * Update charts with new Ozon data
     */
    updateCharts() {
        if (this.charts.sales) {
            const chart = this.charts.sales;
            
            const russianFactor = this.calculateRussianMarketFactor();
            const newRevenue = Math.max(2500000, Math.min(3500000, 
                this.ozonData.monthlyRevenue + (Math.random() - 0.5) * 400000 * russianFactor));
            const newOrders = Math.max(1800, Math.min(2800, 
                this.ozonData.monthlyOrders + (Math.random() - 0.5) * 300 * russianFactor));
            const newRating = Math.max(4.3, Math.min(5.0, 
                this.ozonData.sellerRating + (Math.random() - 0.5) * 0.15));

            // Update chart data
            chart.data.datasets[0].data.push(Math.round(newRevenue));
            chart.data.datasets[1].data.push(Math.round(newOrders));
            chart.data.datasets[2].data.push(parseFloat(newRating.toFixed(1)));

            // Keep only last 5 data points
            if (chart.data.datasets[0].data.length > 5) {
                chart.data.datasets[0].data.shift();
                chart.data.datasets[1].data.shift();
                chart.data.datasets[2].data.shift();
                
                chart.data.labels.shift();
                chart.data.labels.push('Сейчас');
            }

            chart.update('active');
        }
    }

    /**
     * Handle WebSocket events for Ozon
     */
    handleNewOzonOrder(data) {
        const orderValue = data.amount || Math.floor(Math.random() * 50000) + 5000;
        this.showNotification('Новый заказ Ozon!', 
            `${data.orderId || '#OZON-' + Math.floor(Math.random() * 1000000)} - ${this.formatRussianCurrency(orderValue)}`, 'success');
        
        this.ozonData.monthlyOrders++;
        this.ozonData.monthlyRevenue += orderValue;
        
        this.animateCounter('ozon-monthly-orders', this.ozonData.monthlyOrders);
    }

    handleFBOUpdate(data) {
        this.showNotification('Обновление FBO', 
            `${data.productCount || Math.floor(Math.random() * 100) + 20} товаров обновлено в FBO`, 'info');
        this.updateWarehouseData();
    }

    handleWarehouseChange(data) {
        if (data.warehouse && this.ozonData.warehouses[data.warehouse]) {
            this.showNotification('Активность склада', 
                `Новая активность на складе ${this.ozonData.warehouses[data.warehouse].name}`, 'info');
        }
    }

    handleAPIStatus(data) {
        if (data.status === 'healthy') {
            this.ozonData.apiUptime = Math.min(100, this.ozonData.apiUptime + 0.1);
        } else {
            this.showNotification('Статус Ozon API', 
                'Снижение производительности Ozon API', 'warning');
        }
    }

    handlePremiumFeature(data) {
        this.showNotification('Ozon Premium!', 
            `${data.featureName || 'Новая функция'} активирована - улучшена видимость товаров`, 'success');
    }

    /**
     * Ozon-specific action functions
     */
    async syncAllOzonProducts() {
        this.showNotification('Синхронизация товаров Ozon', 
            'Все товары Ozon синхронизируются через Seller API...', 'info');
        
        try {
            await this.simulateAsyncOperation(9000);
            const syncedCount = Math.floor(Math.random() * 500) + 250;
            this.ozonData.totalProducts += Math.floor(syncedCount * 0.12);
            this.animateCounter('ozon-total-products', this.ozonData.totalProducts);
            
            this.showNotification('Синхронизация завершена!', 
                `${syncedCount} товаров Ozon успешно синхронизировано`, 'success');
            
        } catch (error) {
            this.showNotification('Ошибка синхронизации', 'Ошибка подключения к Ozon API', 'error');
        }
    }

    async updateOzonPrices() {
        this.showNotification('Обновление цен Ozon', 
            'Цены товаров обновляются в соответствии с курсом рубля...', 'info');
        
        try {
            await this.simulateAsyncOperation(7000);
            const updatedCount = Math.floor(Math.random() * 300) + 150;
            this.showNotification('Цены обновлены!', 
                `${updatedCount} цен товаров успешно обновлено в Ozon`, 'success');
            
        } catch (error) {
            this.showNotification('Ошибка обновления цен', 'Ошибка Ozon API', 'error');
        }
    }

    async exportOzonOrders() {
        this.showNotification('Экспорт данных Ozon', 
            'Данные заказов и FBO экспортируются в CSV формат...', 'info');
        
        try {
            await this.simulateAsyncOperation(5000);
            this.showNotification('Экспорт завершен!', 
                'Данные Ozon успешно экспортированы в CSV файл', 'success');
            
        } catch (error) {
            this.showNotification('Ошибка экспорта', 'Операция экспорта данных не удалась', 'error');
        }
    }

    async bulkOzonUpload() {
        this.showNotification('FBO Массовая загрузка', 
            'Товары загружаются на склады FBO Ozon...', 'info');
        
        try {
            await this.simulateAsyncOperation(8000);
            const uploadedCount = Math.floor(Math.random() * 200) + 100;
            this.ozonData.totalProducts += uploadedCount;
            this.animateCounter('ozon-total-products', this.ozonData.totalProducts);
            
            this.showNotification('FBO загрузка завершена!', 
                `${uploadedCount} товаров успешно загружено в FBO Ozon`, 'success');
            
        } catch (error) {
            this.showNotification('Ошибка загрузки', 'FBO массовая загрузка не удалась', 'error');
        }
    }

    /**
     * Section navigation for Ozon UI
     */
    showOzonSection(sectionName) {
        // Hide all sections
        document.querySelectorAll('.ozon-section').forEach(section => {
            section.style.display = 'none';
        });

        // Remove active class from nav links
        document.querySelectorAll('.ozon-nav-link').forEach(link => {
            link.classList.remove('active');
        });

        // Show selected section
        const targetSection = document.getElementById(`ozon-${sectionName}-section`);
        if (targetSection) {
            targetSection.style.display = 'block';
        }

        // Add active class to clicked nav link
        const activeLink = document.querySelector(`[onclick="showOzonSection('${sectionName}')"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }

        this.currentSection = sectionName;
        console.log(`🇷🇺 Ozon switched to ${sectionName} section`);
    }

    /**
     * Setup event listeners and keyboard shortcuts
     */
    setupEventListeners() {
        // Global function assignments for HTML onclick events
        window.showOzonSection = (section) => this.showOzonSection(section);
        window.syncAllOzonProducts = () => this.syncAllOzonProducts();
        window.updateOzonPrices = () => this.updateOzonPrices();
        window.exportOzonOrders = () => this.exportOzonOrders();
        window.bulkOzonUpload = () => this.bulkOzonUpload();

        // Ozon-specific keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.shiftKey) {
                switch(e.key) {
                    case 'O':
                        e.preventDefault();
                        this.syncAllOzonProducts();
                        break;
                    case 'F':
                        e.preventDefault();
                        this.switchWarehouse('moscow');
                        break;
                    case 'R':
                        e.preventDefault();
                        this.updateOzonPrices();
                        break;
                }
            }
        });
    }

    /**
     * Utility functions
     */
    formatRussianCurrency(amount) {
        return this.russianCurrency.format(amount);
    }

    animateCounter(elementId, targetValue, duration = 3000) {
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
            
            const easeOutExpo = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
            const currentValue = startValue + (targetValue - startValue) * easeOutExpo;
            
            if (elementId === 'ozon-monthly-revenue') {
                element.textContent = this.formatRussianCurrency(Math.floor(currentValue));
            } else if (elementId === 'ozon-seller-rating') {
                element.textContent = currentValue.toFixed(1);
            } else {
                element.textContent = Math.floor(currentValue).toLocaleString('ru-RU');
            }

            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };

        animate();
    }

    showNotification(title, message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'info'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 11000;
            max-width: 450px;
            box-shadow: 0 20px 60px rgba(0, 91, 255, 0.4);
            border-radius: 25px;
            border: 4px solid var(--ozon-light-blue);
            animation: slideInFromRightRU 0.6s ease-out;
        `;
        
        const iconMap = {
            error: 'exclamation-triangle',
            success: 'check-circle',
            warning: 'exclamation-circle',
            info: 'info-circle'
        };
        
        notification.innerHTML = `
            <div class="d-flex align-items-start">
                <i class="fas fa-${iconMap[type]} me-3 mt-1" style="font-size: 1.4rem;"></i>
                <div class="flex-grow-1">
                    <div class="fw-bold fs-5">${title}</div>
                    <div class="mt-2">${message}</div>
                    <div class="text-muted small mt-2">
                        <span class="russian-flag" style="display: inline-block; width: 16px; height: 12px; margin-right: 5px;"></span>
                        ${new Date().toLocaleTimeString('ru-RU')}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 8000);
    }

    simulateAsyncOperation(duration) {
        return new Promise((resolve) => {
            setTimeout(resolve, duration);
        });
    }

    simulateOzonAPICall() {
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                if (Math.random() > 0.04) { // 96% success rate
                    resolve();
                } else {
                    reject(new Error('Ozon API timeout'));
                }
            }, Math.random() * 2200 + 700);
        });
    }

    /**
     * Cleanup and destroy
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

        console.log('🧹 Ozon Integration cleaned up');
    }
}

// CSS animations for Ozon
const ozonStyles = document.createElement('style');
ozonStyles.textContent = `
    @keyframes slideInFromRightRU {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    .timeline-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--ozon-gradient);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
`;
document.head.appendChild(ozonStyles);

// Initialize Ozon integration when DOM is ready
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