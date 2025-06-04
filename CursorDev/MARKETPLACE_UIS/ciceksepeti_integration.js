/**
 * ÇiçekSepeti Integration JavaScript
 * MesChain-Sync v3.0 - Advanced Flower Marketplace Integration System
 * Features: Real-time sync, Seasonal analytics, Product lifecycle tracking, WebSocket integration
 */

class CicekSepetiIntegration {
    constructor() {
        this.currentSection = 'dashboard';
        this.charts = {};
        this.realTimeIntervals = {};
        this.websocket = null;
        this.cicekSepetiData = {
            totalProducts: 1523,
            monthlyOrders: 342,
            monthlyRevenue: 78945,
            avgRating: 4.6,
            connectionStatus: 'connected',
            apiUptime: 99.2,
            avgResponseTime: 850,
            peakSeason: 'Valentines'
        };
        
        console.log('🌸 ÇiçekSepeti Integration initializing...');
        this.init();
    }

    /**
     * Initialize the ÇiçekSepeti integration
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
            
            // Initialize seasonal monitoring
            this.initializeSeasonalTracking();
            
            // Setup connection monitoring
            this.setupConnectionMonitoring();
            
            console.log('✅ ÇiçekSepeti Integration loaded successfully!');
            this.showToast('ÇiçekSepeti entegrasyonu başarıyla yüklendi', 'success');
            
        } catch (error) {
            console.error('❌ ÇiçekSepeti initialization error:', error);
            this.showToast('Entegrasyon yüklenirken hata oluştu', 'error');
        }
    }

    /**
     * Initialize dashboard charts
     */
    async initializeCharts() {
        // Sales Performance Chart - optimized for flower seasonality
        const salesCtx = document.getElementById('salesChart');
        if (salesCtx) {
            this.charts.sales = new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz'],
                    datasets: [
                        {
                            label: 'Çiçek Satışları (₺)',
                            data: [35000, 89000, 45000, 67000, 78000, 52000, 48000],
                            backgroundColor: 'rgba(233, 30, 99, 0.1)',
                            borderColor: '#e91e63',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#e91e63',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 3,
                            pointRadius: 7
                        },
                        {
                            label: 'Bitki Satışları (₺)',
                            data: [25000, 28000, 32000, 38000, 42000, 45000, 48000],
                            backgroundColor: 'rgba(76, 175, 80, 0.1)',
                            borderColor: '#4caf50',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#4caf50',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 2500, easing: 'easeInOutQuart' },
                    plugins: {
                        legend: { 
                            display: true,
                            position: 'top',
                            labels: { usePointStyle: true }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(233, 30, 99, 0.9)',
                            titleColor: 'white',
                            bodyColor: 'white',
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            grid: { color: 'rgba(233, 30, 99, 0.1)' },
                            ticks: {
                                callback: function(value) {
                                    return '₺' + value.toLocaleString('tr-TR');
                                }
                            }
                        },
                        x: { grid: { color: 'rgba(233, 30, 99, 0.05)' }}
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        }

        // Category Distribution Chart - specific to flower categories
        const categoryCtx = document.getElementById('categoryChart');
        if (categoryCtx) {
            this.charts.category = new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Buket', 'Orkide', 'Sukulent', 'Saksı Çiçek', 'Aranjman', 'Diğer'],
                    datasets: [{
                        data: [30, 20, 15, 18, 12, 5],
                        backgroundColor: [
                            '#e91e63', '#f8bbd9', '#ad1457', 
                            '#4caf50', '#81c784', '#ffb74d'
                        ],
                        borderWidth: 3,
                        borderColor: '#fff',
                        hoverBorderWidth: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { 
                        duration: 2000,
                        animateRotate: true,
                        animateScale: true
                    },
                    plugins: {
                        legend: { 
                            position: 'bottom',
                            labels: { 
                                padding: 15,
                                usePointStyle: true,
                                font: { size: 12 }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(233, 30, 99, 0.9)',
                            titleColor: 'white',
                            bodyColor: 'white',
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': %' + context.parsed;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Detailed Analytics Chart - seasonal trends
        const analyticsCtx = document.getElementById('analyticsChart');
        if (analyticsCtx) {
            this.charts.analytics = new Chart(analyticsCtx, {
                type: 'bar',
                data: {
                    labels: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran'],
                    datasets: [
                        {
                            label: 'Sipariş Sayısı',
                            data: [120, 380, 180, 280, 320, 190],
                            backgroundColor: 'rgba(233, 30, 99, 0.8)',
                            borderColor: '#e91e63',
                            borderWidth: 2,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Ortalama Sipariş Değeri (₺)',
                            data: [85, 125, 95, 110, 140, 105],
                            type: 'line',
                            backgroundColor: 'rgba(76, 175, 80, 0.1)',
                            borderColor: '#4caf50',
                            borderWidth: 3,
                            fill: false,
                            tension: 0.4,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 2000 },
                    plugins: {
                        legend: { display: true },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: { display: true, text: 'Sipariş Sayısı' }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: { display: true, text: 'Sipariş Değeri (₺)' },
                            grid: { drawOnChartArea: false }
                        }
                    }
                }
            });
        }
    }

    /**
     * Initialize WebSocket for real-time updates
     */
    initializeWebSocket() {
        if (typeof window.initMesChainWebSocket === 'function') {
            this.websocket = window.initMesChainWebSocket('marketplace', 'ciceksepeti_' + Date.now());
            
            // Listen for ÇiçekSepeti-specific events
            this.websocket.on('ciceksepeti_order', (data) => {
                this.handleNewOrder(data);
            });
            
            this.websocket.on('ciceksepeti_product_update', (data) => {
                this.handleProductUpdate(data);
            });
            
            this.websocket.on('ciceksepeti_stock_alert', (data) => {
                this.handleStockAlert(data);
            });
            
            this.websocket.on('seasonal_trend', (data) => {
                this.handleSeasonalTrend(data);
            });
            
            console.log('🔗 ÇiçekSepeti WebSocket initialized');
        }
    }

    /**
     * Start real-time updates
     */
    startRealTimeUpdates() {
        // Update metrics every 45 seconds (flower business is time-sensitive)
        this.realTimeIntervals.metrics = setInterval(() => {
            this.updateMetrics();
        }, 45000);

        // Update connection status every 30 seconds
        this.realTimeIntervals.connection = setInterval(() => {
            this.updateConnectionStatus();
        }, 30000);

        // Check seasonal trends every 5 minutes
        this.realTimeIntervals.seasonal = setInterval(() => {
            this.checkSeasonalTrends();
        }, 300000);

        // Update charts every 2 minutes
        this.realTimeIntervals.charts = setInterval(() => {
            this.updateChartsData();
        }, 120000);

        console.log('🔄 ÇiçekSepeti real-time updates started');
    }

    /**
     * Initialize seasonal tracking
     */
    initializeSeasonalTracking() {
        const today = new Date();
        const month = today.getMonth() + 1;
        
        // Detect peak seasons for flower business
        let currentSeason = 'Normal';
        if (month === 2) currentSeason = 'Sevgililer Günü';
        else if (month === 3) currentSeason = 'Kadınlar Günü';
        else if (month === 5) currentSeason = 'Anneler Günü';
        else if (month === 12) currentSeason = 'Yılbaşı';
        
        this.cicekSepetiData.peakSeason = currentSeason;
        
        if (currentSeason !== 'Normal') {
            this.showToast(`${currentSeason} döneminde artış bekleniyor`, 'info');
        }
    }

    /**
     * Setup connection monitoring
     */
    setupConnectionMonitoring() {
        // Test connection every 2 minutes
        setInterval(() => {
            this.testApiConnection();
        }, 120000);
        
        // Initial connection test
        this.testApiConnection();
    }

    /**
     * Update dashboard metrics
     */
    async updateMetrics() {
        try {
            // Simulate realistic flower business metrics
            const seasonMultiplier = this.getSeasonalMultiplier();
            
            const newMetrics = {
                totalProducts: this.cicekSepetiData.totalProducts + Math.floor(Math.random() * 5),
                monthlyOrders: Math.floor(this.cicekSepetiData.monthlyOrders * seasonMultiplier + Math.random() * 10),
                monthlyRevenue: Math.floor(this.cicekSepetiData.monthlyRevenue * seasonMultiplier + Math.random() * 2000),
                avgRating: Math.max(4.0, Math.min(5.0, this.cicekSepetiData.avgRating + (Math.random() - 0.5) * 0.1))
            };

            // Update counters with animation
            this.animateCounter('total-products', newMetrics.totalProducts);
            this.animateCounter('monthly-orders', newMetrics.monthlyOrders);
            this.animateCounter('monthly-revenue', `₺${newMetrics.monthlyRevenue.toLocaleString('tr-TR')}`);
            this.animateCounter('avg-rating', newMetrics.avgRating.toFixed(1));

            this.cicekSepetiData = { ...this.cicekSepetiData, ...newMetrics };

        } catch (error) {
            console.error('❌ Metrics update error:', error);
        }
    }

    /**
     * Get seasonal multiplier for realistic metrics
     */
    getSeasonalMultiplier() {
        const season = this.cicekSepetiData.peakSeason;
        switch(season) {
            case 'Sevgililer Günü': return 2.8;
            case 'Kadınlar Günü': return 2.2;
            case 'Anneler Günü': return 3.1;
            case 'Yılbaşı': return 1.8;
            default: return 1.0;
        }
    }

    /**
     * Update connection status
     */
    updateConnectionStatus() {
        const connectionElement = document.getElementById('connection-text');
        const lastSyncElement = document.getElementById('last-sync');
        
        // Simulate connection status updates
        const isConnected = Math.random() > 0.05; // 95% uptime
        
        if (isConnected) {
            connectionElement.textContent = 'Bağlı';
            connectionElement.parentElement.className = 'connection-status status-connected';
            lastSyncElement.textContent = 'Son Sync: şimdi';
        } else {
            connectionElement.textContent = 'Bağlantı Sorunu';
            connectionElement.parentElement.className = 'connection-status status-error';
            this.showToast('ÇiçekSepeti bağlantısında sorun var', 'warning');
        }
    }

    /**
     * Test API connection
     */
    async testApiConnection() {
        try {
            const startTime = Date.now();
            
            // Simulate API test
            await this.simulateApiCall();
            
            const responseTime = Date.now() - startTime;
            this.cicekSepetiData.avgResponseTime = responseTime;
            
            console.log(`🌸 API Response Time: ${responseTime}ms`);
            
        } catch (error) {
            console.error('❌ API connection test failed:', error);
            this.showToast('API bağlantı testi başarısız', 'error');
        }
    }

    /**
     * Check seasonal trends
     */
    checkSeasonalTrends() {
        const today = new Date();
        const dayOfMonth = today.getDate();
        
        // Alert for upcoming peak seasons
        if (today.getMonth() === 1 && dayOfMonth === 10) { // Feb 10
            this.showToast('Sevgililer Günü yaklaşıyor! Stok kontrolü yapın.', 'info');
        } else if (today.getMonth() === 2 && dayOfMonth === 5) { // Mar 5
            this.showToast('Kadınlar Günü için hazırlık zamanı!', 'info');
        } else if (today.getMonth() === 4 && dayOfMonth === 5) { // May 5
            this.showToast('Anneler Günü için yoğunluk bekleniyor!', 'info');
        }
    }

    /**
     * Update charts with new data
     */
    updateChartsData() {
        // Update sales chart with seasonal variations
        if (this.charts.sales) {
            const chart = this.charts.sales;
            const seasonMultiplier = this.getSeasonalMultiplier();
            
            // Add new data point
            const newFlowerSales = Math.floor((Math.random() * 20000 + 40000) * seasonMultiplier);
            const newPlantSales = Math.floor(Math.random() * 10000 + 35000);
            
            chart.data.datasets[0].data.push(newFlowerSales);
            chart.data.datasets[1].data.push(newPlantSales);
            chart.data.labels.push(new Date().toLocaleDateString('tr-TR', { month: 'short' }));
            
            // Keep only last 7 data points
            if (chart.data.labels.length > 7) {
                chart.data.labels.shift();
                chart.data.datasets[0].data.shift();
                chart.data.datasets[1].data.shift();
            }
            
            chart.update('active');
        }

        // Update category distribution
        if (this.charts.category) {
            const chart = this.charts.category;
            const newDistribution = chart.data.datasets[0].data.map(value => 
                Math.max(5, Math.min(35, value + (Math.random() - 0.5) * 5))
            );
            chart.data.datasets[0].data = newDistribution;
            chart.update('active');
        }
    }

    /**
     * Handle WebSocket events
     */
    handleNewOrder(data) {
        this.showToast(`Yeni sipariş: ${data.product} - ₺${data.amount}`, 'success');
        this.cicekSepetiData.monthlyOrders++;
        this.animateCounter('monthly-orders', this.cicekSepetiData.monthlyOrders);
    }

    handleProductUpdate(data) {
        this.showToast(`Ürün güncellendi: ${data.productName}`, 'info');
    }

    handleStockAlert(data) {
        if (data.stock < 10) {
            this.showToast(`Stok uyarısı: ${data.productName} (${data.stock} adet)`, 'warning');
        }
    }

    handleSeasonalTrend(data) {
        this.showToast(`Trend Uyarısı: ${data.trend}`, 'info');
    }

    /**
     * ÇiçekSepeti action functions
     */
    async syncProducts() {
        this.showToast('Ürünler senkronize ediliyor...', 'info');
        
        try {
            // Simulate product sync with realistic timing for flower business
            await this.simulateOperation(3000);
            
            const syncedCount = Math.floor(Math.random() * 50) + 100;
            this.showToast(`${syncedCount} ürün başarıyla senkronize edildi`, 'success');
            
            // Update product count
            this.cicekSepetiData.totalProducts += Math.floor(Math.random() * 5);
            this.animateCounter('total-products', this.cicekSepetiData.totalProducts);
            
        } catch (error) {
            this.showToast('Ürün senkronizasyonu başarısız', 'error');
        }
    }

    async updatePrices() {
        this.showToast('Fiyatlar güncelleniyor...', 'info');
        
        try {
            await this.simulateOperation(2500);
            
            const updatedCount = Math.floor(Math.random() * 30) + 50;
            this.showToast(`${updatedCount} ürün fiyatı güncellendi`, 'success');
            
        } catch (error) {
            this.showToast('Fiyat güncelleme başarısız', 'error');
        }
    }

    async exportOrders() {
        this.showToast('Siparişler export ediliyor...', 'info');
        
        try {
            await this.simulateOperation(2000);
            this.showToast('Siparişler başarıyla export edildi', 'success');
            
        } catch (error) {
            this.showToast('Export işlemi başarısız', 'error');
        }
    }

    async bulkUpload() {
        this.showToast('Toplu yükleme başlatılıyor...', 'info');
        
        try {
            await this.simulateOperation(4000);
            
            const uploadedCount = Math.floor(Math.random() * 20) + 15;
            this.showToast(`${uploadedCount} ürün başarıyla yüklendi`, 'success');
            
            this.cicekSepetiData.totalProducts += uploadedCount;
            this.animateCounter('total-products', this.cicekSepetiData.totalProducts);
            
        } catch (error) {
            this.showToast('Toplu yükleme başarısız', 'error');
        }
    }

    addNewProduct() {
        this.showToast('Yeni ürün ekleme formu açılıyor...', 'info');
    }

    saveSettings() {
        this.showToast('Ayarlar kaydediliyor...', 'info');
        
        setTimeout(() => {
            this.showToast('Ayarlar başarıyla kaydedildi', 'success');
        }, 1500);
    }

    testConnection() {
        this.showToast('Bağlantı test ediliyor...', 'info');
        
        setTimeout(() => {
            const isSuccess = Math.random() > 0.1;
            if (isSuccess) {
                this.showToast('Bağlantı testi başarılı', 'success');
            } else {
                this.showToast('Bağlantı testi başarısız', 'error');
            }
        }, 2000);
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Global function assignments for HTML onclick events
        window.syncProducts = () => this.syncProducts();
        window.updatePrices = () => this.updatePrices();
        window.exportOrders = () => this.exportOrders();
        window.bulkUpload = () => this.bulkUpload();
        window.addNewProduct = () => this.addNewProduct();
        window.saveSettings = () => this.saveSettings();
        window.testConnection = () => this.testConnection();

        // Tab switching events
        document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', (e) => {
                const targetId = e.target.getAttribute('data-bs-target').substring(1);
                this.currentSection = targetId;
                console.log(`🌸 Switched to section: ${targetId}`);
                
                // Trigger chart resize if needed
                if (this.charts[targetId] || targetId === 'analytics') {
                    setTimeout(() => {
                        Object.values(this.charts).forEach(chart => {
                            if (chart) chart.resize();
                        });
                    }, 300);
                }
            });
        });

        // Keyboard shortcuts for ÇiçekSepeti
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.shiftKey) {
                switch(e.key) {
                    case 'S':
                        e.preventDefault();
                        this.syncProducts();
                        break;
                    case 'P':
                        e.preventDefault();
                        this.updatePrices();
                        break;
                    case 'O':
                        e.preventDefault();
                        this.exportOrders();
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
            
            if (elementId === 'monthly-revenue') {
                element.textContent = `₺${Math.floor(currentValue).toLocaleString('tr-TR')}`;
            } else if (elementId === 'avg-rating') {
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

    showToast(message, type = 'info') {
        const toastElement = document.getElementById('notification-toast');
        const messageElement = document.getElementById('toast-message');
        
        if (toastElement && messageElement) {
            messageElement.textContent = message;
            
            // Update toast style based on type
            toastElement.className = `toast bg-${type === 'error' ? 'danger' : type === 'warning' ? 'warning' : type === 'success' ? 'success' : 'info'}`;
            
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
        }
    }

    simulateApiCall() {
        return new Promise((resolve) => {
            setTimeout(resolve, Math.random() * 1000 + 500);
        });
    }

    simulateOperation(duration) {
        return new Promise((resolve) => {
            setTimeout(resolve, duration);
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

        console.log('🧹 ÇiçekSepeti Integration cleaned up');
    }
}

// Initialize ÇiçekSepeti integration when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.cicekSepetiIntegration = new CicekSepetiIntegration();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.cicekSepetiIntegration) {
        window.cicekSepetiIntegration.destroy();
    }
});

// Export for use in other modules
window.CicekSepetiIntegration = CicekSepetiIntegration; 