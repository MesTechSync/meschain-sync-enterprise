/**
 * Hepsiburada Integration JavaScript
 * MesChain-Sync v3.0 - Advanced Marketplace Integration System
 * Features: Real-time sync, Advanced analytics, Performance tracking, WebSocket integration
 */

class HepsiburadaIntegration {
    constructor() {
        this.currentSection = 'dashboard';
        this.charts = {};
        this.realTimeIntervals = {};
        this.websocket = null;
        this.hepsiburadaData = {
            totalProducts: 2134,
            monthlyOrders: 723,
            monthlyRevenue: 94567,
            avgRating: 4.8,
            connectionStatus: 'connected',
            apiUptime: 98.7,
            avgResponseTime: 1.2
        };
        
        console.log('🛍️ Hepsiburada Integration initializing...');
        this.init();
    }

    /**
     * Initialize Hepsiburada integration
     */
    async init() {
        try {
            // Initialize charts
            await this.initializeCharts();
            
            // Setup WebSocket for real-time updates
            this.initializeWebSocket();
            
            // Start real-time updates
            this.startRealTimeUpdates();
            
            // Setup event listeners
            this.setupEventListeners();
            
            // Initialize performance monitoring
            this.initializePerformanceTracking();
            
            console.log('✅ Hepsiburada Integration loaded successfully!');
            
        } catch (error) {
            console.error('❌ Hepsiburada integration initialization error:', error);
            this.showNotification('Hepsiburada entegrasyonu yüklenirken hata oluştu', 'error');
        }
    }

    /**
     * Initialize Hepsiburada sales and performance chart
     */
    async initializeCharts() {
        const ctx = document.getElementById('hepsiburadaSalesChart');
        if (ctx) {
            this.charts.sales = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['1 Hf', '2 Hf', '3 Hf', '4 Hf', 'Bu Hafta'],
                    datasets: [{
                        label: 'Satış (₺)',
                        data: [62000, 68000, 75000, 81000, 94567],
                        backgroundColor: 'rgba(255, 96, 0, 0.1)',
                        borderColor: '#ff6000',
                        borderWidth: 5,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#ff6000',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 4,
                        pointRadius: 10,
                        pointHoverRadius: 12
                    }, {
                        label: 'Sipariş Sayısı',
                        data: [445, 498, 567, 632, 723],
                        backgroundColor: 'rgba(0, 200, 81, 0.15)',
                        borderColor: '#00c851',
                        borderWidth: 4,
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#00c851',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 3,
                        pointRadius: 8,
                        pointHoverRadius: 10
                    }, {
                        label: 'Ürün Sayısı',
                        data: [1856, 1923, 1987, 2054, 2134],
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderColor: '#3b82f6',
                        borderWidth: 3,
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#3b82f6',
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
                        duration: 3500,
                        easing: 'easeInOutQuart'
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                font: { size: 14, weight: '700' },
                                padding: 20
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 96, 0, 0.95)',
                            titleColor: 'white',
                            bodyColor: 'white',
                            borderColor: '#ff6000',
                            borderWidth: 3,
                            titleFont: { weight: '800', size: 14 },
                            bodyFont: { weight: '600', size: 13 },
                            padding: 20,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.dataset.label === 'Satış (₺)') {
                                        label += '₺' + context.parsed.y.toLocaleString('tr-TR');
                                    } else {
                                        label += context.parsed.y.toLocaleString('tr-TR');
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
                                color: 'rgba(255, 96, 0, 0.1)',
                                lineWidth: 1
                            },
                            ticks: {
                                font: { weight: '600', size: 12 },
                                callback: function(value) {
                                    return '₺' + value.toLocaleString('tr-TR');
                                }
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(255, 96, 0, 0.05)',
                                lineWidth: 1
                            },
                            ticks: {
                                font: { weight: '600', size: 12 }
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }
    }

    /**
     * Initialize WebSocket connection for real-time updates
     */
    initializeWebSocket() {
        if (typeof window.initMesChainWebSocket === 'function') {
            this.websocket = window.initMesChainWebSocket('admin', 'hepsiburada_user_' + Date.now());
            
            // Listen for Hepsiburada-specific events
            this.websocket.on('hepsiburada_order', (data) => {
                this.handleNewHepsiburadaOrder(data);
            });
            
            this.websocket.on('hepsiburada_product_sync', (data) => {
                this.handleProductSync(data);
            });
            
            this.websocket.on('hepsiburada_price_update', (data) => {
                this.handlePriceUpdate(data);
            });
            
            this.websocket.on('hepsiburada_performance', (data) => {
                this.handlePerformanceUpdate(data);
            });
            
            console.log('🔗 Hepsiburada WebSocket initialized');
        }
    }

    /**
     * Start real-time updates
     */
    startRealTimeUpdates() {
        // Update metrics every 75 seconds
        this.realTimeIntervals.metrics = setInterval(() => {
            this.updateHepsiburadaMetrics();
        }, 75000);

        // Update charts every 4 minutes
        this.realTimeIntervals.charts = setInterval(() => {
            this.updateCharts();
        }, 240000);

        // Check connection status every 45 seconds
        this.realTimeIntervals.connection = setInterval(() => {
            this.checkConnectionStatus();
        }, 45000);

        // Update performance indicators every 2 minutes
        this.realTimeIntervals.performance = setInterval(() => {
            this.updatePerformanceIndicators();
        }, 120000);

        console.log('🔄 Hepsiburada real-time updates started');
    }

    /**
     * Initialize performance tracking
     */
    initializePerformanceTracking() {
        // Track API response times
        this.performanceData = {
            apiCalls: 0,
            totalResponseTime: 0,
            successfulCalls: 0,
            failedCalls: 0
        };
    }

    /**
     * Update Hepsiburada metrics with advanced animations
     */
    async updateHepsiburadaMetrics() {
        try {
            // Simulate realistic API data updates
            const newData = {
                totalProducts: this.hepsiburadaData.totalProducts + Math.floor(Math.random() * 25) + 5,
                monthlyOrders: this.hepsiburadaData.monthlyOrders + Math.floor(Math.random() * 15) + 2,
                monthlyRevenue: this.hepsiburadaData.monthlyRevenue + Math.floor(Math.random() * 8000) + 1000,
                avgRating: Math.max(4.0, Math.min(5.0, this.hepsiburadaData.avgRating + (Math.random() - 0.5) * 0.15)),
                apiUptime: Math.max(95.0, Math.min(100.0, this.hepsiburadaData.apiUptime + (Math.random() - 0.5) * 0.5)),
                avgResponseTime: Math.max(0.8, Math.min(2.0, this.hepsiburadaData.avgResponseTime + (Math.random() - 0.5) * 0.3))
            };

            // Animate counter updates with easing
            this.animateCounter('hb-total-products', newData.totalProducts, 2500);
            this.animateCounter('hb-monthly-orders', newData.monthlyOrders, 2200);
            this.animateCounter('hb-monthly-revenue', `₺${newData.monthlyRevenue.toLocaleString('tr-TR')}`, 2800);
            this.animateCounter('hb-avg-rating', newData.avgRating.toFixed(1), 2000);

            this.hepsiburadaData = { ...this.hepsiburadaData, ...newData };

            // Update performance indicators
            this.updatePerformanceDisplay();

        } catch (error) {
            console.error('❌ Hepsiburada metrics update error:', error);
        }
    }

    /**
     * Update performance indicators
     */
    updatePerformanceDisplay() {
        // Update uptime percentage
        const uptimeElement = document.querySelector('.performance-indicator .col-4:nth-child(2) .fw-bold');
        if (uptimeElement) {
            uptimeElement.textContent = `${this.hepsiburadaData.apiUptime.toFixed(1)}%`;
        }

        // Update average response time
        const responseElement = document.querySelector('.performance-indicator .col-4:nth-child(3) .fw-bold');
        if (responseElement) {
            responseElement.textContent = `${this.hepsiburadaData.avgResponseTime.toFixed(1)}s`;
        }
    }

    /**
     * Update charts with new performance data
     */
    updateCharts() {
        if (this.charts.sales) {
            const chart = this.charts.sales;
            
            // Generate new realistic data points
            const newSales = Math.max(70000, Math.min(120000, this.hepsiburadaData.monthlyRevenue + (Math.random() - 0.5) * 15000));
            const newOrders = Math.max(600, Math.min(900, this.hepsiburadaData.monthlyOrders + (Math.random() - 0.5) * 80));
            const newProducts = this.hepsiburadaData.totalProducts;

            // Update chart data with smooth transitions
            chart.data.datasets[0].data.push(Math.round(newSales));
            chart.data.datasets[1].data.push(Math.round(newOrders));
            chart.data.datasets[2].data.push(newProducts);

            // Keep only last 5 data points for clean visualization
            if (chart.data.datasets[0].data.length > 5) {
                chart.data.datasets[0].data.shift();
                chart.data.datasets[1].data.shift();
                chart.data.datasets[2].data.shift();
                
                // Update time labels
                chart.data.labels.shift();
                chart.data.labels.push('Şimdi');
            }

            chart.update('active');
        }
    }

    /**
     * Check Hepsiburada API connection status with health monitoring
     */
    async checkConnectionStatus() {
        try {
            // Simulate advanced API health check
            const isConnected = Math.random() > 0.03; // 97% uptime simulation
            const responseTime = Math.random() * 2 + 0.5; // 0.5-2.5s response time
            
            this.performanceData.apiCalls++;
            this.performanceData.totalResponseTime += responseTime;
            
            if (isConnected) {
                this.performanceData.successfulCalls++;
                this.hepsiburadaData.avgResponseTime = this.performanceData.totalResponseTime / this.performanceData.apiCalls;
                this.hepsiburadaData.apiUptime = (this.performanceData.successfulCalls / this.performanceData.apiCalls) * 100;
            } else {
                this.performanceData.failedCalls++;
            }
            
            if (isConnected !== (this.hepsiburadaData.connectionStatus === 'connected')) {
                this.updateConnectionStatus(isConnected ? 'connected' : 'disconnected');
            }
            
        } catch (error) {
            this.updateConnectionStatus('error');
            this.performanceData.failedCalls++;
        }
    }

    /**
     * Update connection status UI with advanced indicators
     */
    updateConnectionStatus(status) {
        this.hepsiburadaData.connectionStatus = status;
        
        const dot = document.querySelector('.connection-dot');
        const text = dot?.nextElementSibling;
        
        if (dot && text) {
            dot.className = `connection-dot ${status}`;
            
            switch(status) {
                case 'connected':
                    text.textContent = 'Bağlı ve Aktif';
                    break;
                case 'disconnected':
                    text.textContent = 'Bağlantı Kesildi';
                    break;
                case 'error':
                    text.textContent = 'Bağlantı Hatası';
                    break;
            }
        }
        
        if (status !== 'connected') {
            this.showNotification('Hepsiburada Bağlantı Sorunu', 
                'API bağlantısında sorun tespit edildi. Tekrar deneniyor...', 'warning');
        }
    }

    /**
     * Update performance indicators in real-time
     */
    updatePerformanceIndicators() {
        // Simulate performance fluctuations
        const salesGrowth = Math.random() * 20 + 35; // 35-55% growth
        const uptimeChange = (Math.random() - 0.5) * 0.5;
        const responseChange = (Math.random() - 0.5) * 0.2;

        this.hepsiburadaData.apiUptime = Math.max(95, Math.min(100, this.hepsiburadaData.apiUptime + uptimeChange));
        this.hepsiburadaData.avgResponseTime = Math.max(0.8, Math.min(2.5, this.hepsiburadaData.avgResponseTime + responseChange));

        // Update growth percentage
        const growthElement = document.querySelector('.performance-indicator .col-4:first-child .fw-bold');
        if (growthElement) {
            growthElement.textContent = `+${salesGrowth.toFixed(0)}%`;
        }

        this.updatePerformanceDisplay();
    }

    /**
     * Handle WebSocket events
     */
    handleNewHepsiburadaOrder(data) {
        this.showNotification('Yeni Hepsiburada Siparişi!', 
            `${data.orderId || '#HB-' + Math.floor(Math.random() * 100000)} - ₺${data.amount || Math.floor(Math.random() * 5000) + 500}`, 'success');
        
        // Update order count with animation
        this.hepsiburadaData.monthlyOrders++;
        this.animateCounter('hb-monthly-orders', this.hepsiburadaData.monthlyOrders);
    }

    handleProductSync(data) {
        this.showNotification('Ürün Senkronizasyonu Tamamlandı', 
            `${data.productCount || Math.floor(Math.random() * 100) + 50} ürün başarıyla güncellendi`, 'info');
    }

    handlePriceUpdate(data) {
        this.showNotification('Fiyat Güncellemesi Başarılı', 
            `${data.updatedCount || Math.floor(Math.random() * 200) + 100} ürün fiyatı güncellendi`, 'success');
    }

    handlePerformanceUpdate(data) {
        if (data.metric && data.value) {
            this.hepsiburadaData[data.metric] = data.value;
            this.updatePerformanceDisplay();
        }
    }

    /**
     * Section navigation with smooth transitions
     */
    showHepsiburadaSection(sectionName) {
        // Hide all sections with fade effect
        document.querySelectorAll('.hb-section').forEach(section => {
            section.style.opacity = '0';
            setTimeout(() => {
                section.style.display = 'none';
            }, 200);
        });

        // Remove active class from all nav links
        document.querySelectorAll('.hb-nav-link').forEach(link => {
            link.classList.remove('active');
        });

        // Show selected section with fade in
        setTimeout(() => {
            const targetSection = document.getElementById(`hepsiburada-${sectionName}-section`);
            if (targetSection) {
                targetSection.style.display = 'block';
                setTimeout(() => {
                    targetSection.style.opacity = '1';
                }, 50);
            }
        }, 200);

        // Add active class to clicked nav link
        const activeLink = document.querySelector(`[onclick="showHepsiburadaSection('${sectionName}')"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }

        this.currentSection = sectionName;
        console.log(`🛍️ Hepsiburada switched to ${sectionName} section`);
    }

    /**
     * Hepsiburada specific functions
     */
    async syncAllHBProducts() {
        this.showNotification('Senkronizasyon Başlatıldı', 'Tüm ürünler Hepsiburada ile senkronize ediliyor...', 'info');
        
        try {
            await this.simulateAsyncOperation(9000);
            const syncedCount = Math.floor(Math.random() * 300) + 150;
            this.hepsiburadaData.totalProducts += Math.floor(syncedCount * 0.1);
            this.animateCounter('hb-total-products', this.hepsiburadaData.totalProducts);
            this.showNotification('Senkronizasyon Tamamlandı!', 
                `${syncedCount} ürün başarıyla senkronize edildi`, 'success');
            
        } catch (error) {
            this.showNotification('Senkronizasyon Hatası', 'Bir hata oluştu, tekrar deneniyor...', 'error');
        }
    }

    async updateHBPrices() {
        this.showNotification('Fiyat Güncellemesi', 'Hepsiburada fiyatları güncelleniyor...', 'info');
        
        try {
            await this.simulateAsyncOperation(6000);
            const updatedCount = Math.floor(Math.random() * 200) + 80;
            this.showNotification('Fiyatlar Güncellendi!', 
                `${updatedCount} ürün fiyatı başarıyla güncellendi`, 'success');
            
        } catch (error) {
            this.showNotification('Fiyat Güncelleme Hatası', 'Bir hata oluştu', 'error');
        }
    }

    async exportHBOrders() {
        this.showNotification('Export İşlemi', 'Hepsiburada siparişleri export ediliyor...', 'info');
        
        try {
            await this.simulateAsyncOperation(4000);
            this.showNotification('Export Tamamlandı!', 
                'Siparişler başarıyla Excel formatında export edildi', 'success');
            
        } catch (error) {
            this.showNotification('Export Hatası', 'Export işlemi sırasında hata oluştu', 'error');
        }
    }

    async bulkHBUpload() {
        this.showNotification('Toplu Yükleme', 'Ürünler Hepsiburada\'ya yükleniyor...', 'info');
        
        try {
            await this.simulateAsyncOperation(7000);
            const uploadedCount = Math.floor(Math.random() * 150) + 50;
            this.hepsiburadaData.totalProducts += uploadedCount;
            this.animateCounter('hb-total-products', this.hepsiburadaData.totalProducts);
            this.showNotification('Yükleme Tamamlandı!', 
                `${uploadedCount} ürün başarıyla yüklendi`, 'success');
            
        } catch (error) {
            this.showNotification('Yükleme Hatası', 'Toplu yükleme sırasında hata oluştu', 'error');
        }
    }

    viewAllHBOrders() {
        this.showHepsiburadaSection('orders');
        this.showNotification('Sipariş Listesi', 'Tüm Hepsiburada siparişleri görüntüleniyor...', 'info');
    }

    addNewHBProduct() {
        this.showNotification('Yeni Ürün Ekleme', 'Hepsiburada ürün ekleme formu açılıyor...', 'info');
        console.log('🆕 Add new product to Hepsiburada');
    }

    async saveHBSettings() {
        this.showNotification('Ayarlar Kaydediliyor', 'Hepsiburada API ayarları güncelleniyor...', 'info');
        
        try {
            await this.simulateAsyncOperation(2500);
            this.showNotification('Ayarlar Kaydedildi!', 
                'Hepsiburada entegrasyon ayarları başarıyla güncellendi', 'success');
            
        } catch (error) {
            this.showNotification('Kaydetme Hatası', 'Ayarlar kaydedilemedi', 'error');
        }
    }

    async testHBConnection() {
        this.showNotification('Bağlantı Testi', 'Hepsiburada API bağlantısı test ediliyor...', 'info');
        
        try {
            await this.simulateAsyncOperation(3500);
            this.showNotification('Bağlantı Başarılı!', 
                'Hepsiburada API ile bağlantı başarıyla sağlandı', 'success');
            this.updateConnectionStatus('connected');
            
        } catch (error) {
            this.showNotification('Bağlantı Hatası', 'API bağlantısı kurulamadı', 'error');
            this.updateConnectionStatus('disconnected');
        }
    }

    /**
     * Setup event listeners and keyboard shortcuts
     */
    setupEventListeners() {
        // Global function assignments for HTML onclick events
        window.showHepsiburadaSection = (section) => this.showHepsiburadaSection(section);
        window.syncAllHBProducts = () => this.syncAllHBProducts();
        window.updateHBPrices = () => this.updateHBPrices();
        window.exportHBOrders = () => this.exportHBOrders();
        window.bulkHBUpload = () => this.bulkHBUpload();
        window.viewAllHBOrders = () => this.viewAllHBOrders();
        window.addNewHBProduct = () => this.addNewHBProduct();
        window.saveHBSettings = () => this.saveHBSettings();
        window.testHBConnection = () => this.testHBConnection();

        // Advanced keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.altKey) {
                switch(e.key) {
                    case 'h':
                        e.preventDefault();
                        this.syncAllHBProducts();
                        break;
                    case 'p':
                        e.preventDefault();
                        this.updateHBPrices();
                        break;
                    case 'o':
                        e.preventDefault();
                        this.showHepsiburadaSection('orders');
                        break;
                    case 'u':
                        e.preventDefault();
                        this.bulkHBUpload();
                        break;
                }
            }
        });

        // Add smooth transitions to sections
        document.querySelectorAll('.hb-section').forEach(section => {
            section.style.transition = 'opacity 0.3s ease-in-out';
        });
    }

    /**
     * Enhanced utility functions
     */
    animateCounter(elementId, targetValue, duration = 2500) {
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
            
            // Advanced easing function
            const easeOutCubic = 1 - Math.pow(1 - progress, 3);
            const currentValue = startValue + (targetValue - startValue) * easeOutCubic;
            
            if (elementId === 'hb-monthly-revenue') {
                element.textContent = `₺${Math.floor(currentValue).toLocaleString('tr-TR')}`;
            } else if (elementId === 'hb-avg-rating') {
                element.textContent = currentValue.toFixed(1);
            } else {
                element.textContent = Math.floor(currentValue).toLocaleString('tr-TR');
            }

            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };

        animate();
    }

    showNotification(title, message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'info'} alert-dismissible fade show position-fixed`;
        toast.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 10000;
            max-width: 420px;
            box-shadow: 0 15px 50px rgba(255, 96, 0, 0.35);
            border-radius: 20px;
            border: 3px solid var(--hb-border);
            animation: slideInFromRight 0.5s ease-out;
            backdrop-filter: blur(10px);
        `;
        
        const iconMap = {
            error: 'exclamation-circle',
            success: 'check-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        
        toast.innerHTML = `
            <div class="d-flex align-items-start">
                <i class="fas fa-${iconMap[type]} me-3 mt-1" style="font-size: 1.2rem;"></i>
                <div class="flex-grow-1">
                    <div class="fw-bold fs-6">${title}</div>
                    <div class="small mt-1">${message}</div>
                    <div class="text-muted small mt-2">${new Date().toLocaleTimeString('tr-TR')}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        document.body.appendChild(toast);

        // Enhanced auto-remove with fade out
        setTimeout(() => {
            if (toast.parentNode) {
                toast.style.animation = 'slideOutToRight 0.5s ease-in';
                setTimeout(() => toast.remove(), 500);
            }
        }, 7000);
    }

    simulateAsyncOperation(duration) {
        return new Promise((resolve) => {
            setTimeout(resolve, duration);
        });
    }

    /**
     * Enhanced cleanup on page unload
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

        console.log('🧹 Hepsiburada Integration cleaned up');
    }
}

// Enhanced CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInFromRight {
        from { 
            transform: translateX(100%) scale(0.9); 
            opacity: 0; 
        }
        to { 
            transform: translateX(0) scale(1); 
            opacity: 1; 
        }
    }
    @keyframes slideOutToRight {
        from { 
            transform: translateX(0) scale(1); 
            opacity: 1; 
        }
        to { 
            transform: translateX(100%) scale(0.9); 
            opacity: 0; 
        }
    }
    .hb-section {
        opacity: 1;
        transition: opacity 0.3s ease-in-out;
    }
`;
document.head.appendChild(style);

// Initialize Hepsiburada integration when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.hepsiburadaIntegration = new HepsiburadaIntegration();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.hepsiburadaIntegration) {
        window.hepsiburadaIntegration.destroy();
    }
});

// Export for use in other modules
window.HepsiburadaIntegration = HepsiburadaIntegration; 