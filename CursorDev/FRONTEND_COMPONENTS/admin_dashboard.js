/**
 * Admin Dashboard JavaScript
 * MesChain-Sync v3.0 - Store Management System
 * Features: Product management, Order tracking, Marketplace sync
 */

class AdminDashboard {
    constructor() {
        this.currentSection = 'dashboard';
        this.charts = {};
        this.realTimeIntervals = {};
        this.storeData = {
            totalProducts: 1247,
            pendingOrders: 89,
            syncStatus: '6/7',
            lowStock: 12
        };
        this.marketplaces = {
            amazon: { status: 'connected', lastSync: new Date() },
            trendyol: { status: 'connected', lastSync: new Date() },
            n11: { status: 'syncing', lastSync: new Date(Date.now() - 300000) },
            ebay: { status: 'connected', lastSync: new Date() },
            hepsiburada: { status: 'disconnected', lastSync: new Date(Date.now() - 86400000) }
        };
        
        console.log('🏪 Admin Dashboard initializing...');
        this.init();
    }

    /**
     * Initialize admin dashboard
     */
    async init() {
        try {
            // Initialize charts
            await this.initializeCharts();
            
            // Start real-time updates
            this.startRealTimeUpdates();
            
            // Setup event listeners
            this.setupEventListeners();
            
            // Update marketplace statuses
            this.updateMarketplaceConnections();
            
            console.log('✅ Admin Dashboard loaded successfully!');
            
        } catch (error) {
            console.error('❌ Admin Dashboard initialization error:', error);
            this.showNotification('Dashboard yüklenirken hata oluştu', 'error');
        }
    }

    /**
     * Initialize store performance chart
     */
    async initializeCharts() {
        const ctx = document.getElementById('storePerformanceChart');
        if (ctx) {
            this.charts.storePerformance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['30 Gün', '25 Gün', '20 Gün', '15 Gün', '10 Gün', '5 Gün', 'Bugün'],
                    datasets: [{
                        label: 'Toplam Satış (₺)',
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
                    }, {
                        label: 'Sipariş Sayısı',
                        data: [89, 97, 85, 112, 105, 118, 127],
                        backgroundColor: 'rgba(8, 145, 178, 0.1)',
                        borderColor: '#0891b2',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#0891b2',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4
                    }, {
                        label: 'Aktif Ürün',
                        data: [1180, 1190, 1205, 1215, 1230, 1240, 1247],
                        backgroundColor: 'rgba(124, 58, 237, 0.1)',
                        borderColor: '#7c3aed',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#7c3aed',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 2500,
                        easing: 'easeInOutQuart'
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                font: { size: 12, weight: '600' }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(31, 41, 55, 0.95)',
                            titleColor: 'white',
                            bodyColor: 'white',
                            borderColor: '#059669',
                            borderWidth: 1,
                            titleFont: { weight: '600' },
                            bodyFont: { weight: '500' }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(5, 150, 105, 0.1)'
                            },
                            ticks: {
                                font: { weight: '500' }
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(5, 150, 105, 0.05)'
                            },
                            ticks: {
                                font: { weight: '500' }
                            }
                        }
                    }
                }
            });
        }
    }

    /**
     * Start real-time updates for admin dashboard
     */
    startRealTimeUpdates() {
        // Update store metrics every 45 seconds
        this.realTimeIntervals.metrics = setInterval(() => {
            this.updateStoreMetrics();
        }, 45000);

        // Update charts every 3 minutes
        this.realTimeIntervals.charts = setInterval(() => {
            this.updateCharts();
        }, 180000);

        // Update marketplace status every 60 seconds
        this.realTimeIntervals.marketplace = setInterval(() => {
            this.updateMarketplaceConnections();
        }, 60000);

        console.log('🔄 Admin real-time updates started');
    }

    /**
     * Update store metrics with smooth animations
     */
    async updateStoreMetrics() {
        try {
            // Simulate real API data - replace with actual backend calls
            const newData = {
                totalProducts: this.storeData.totalProducts + Math.floor(Math.random() * 5),
                pendingOrders: Math.max(0, this.storeData.pendingOrders + Math.floor((Math.random() - 0.6) * 10)),
                syncStatus: '6/7', // Keep stable for demo
                lowStock: Math.max(0, this.storeData.lowStock + Math.floor((Math.random() - 0.5) * 3))
            };

            // Animate counter updates
            this.animateCounter('total-products', newData.totalProducts);
            this.animateCounter('pending-orders', newData.pendingOrders);
            this.animateCounter('low-stock', newData.lowStock);

            this.storeData = newData;

        } catch (error) {
            console.error('❌ Store metrics update error:', error);
        }
    }

    /**
     * Update charts with new store data
     */
    updateCharts() {
        if (this.charts.storePerformance) {
            const chart = this.charts.storePerformance;
            
            // Generate new realistic data points
            const newSales = Math.max(40000, Math.min(80000, 72000 + (Math.random() - 0.5) * 15000));
            const newOrders = Math.max(80, Math.min(150, 127 + (Math.random() - 0.5) * 20));
            const newProducts = this.storeData.totalProducts;

            // Add new data and remove oldest if more than 7 points
            chart.data.datasets[0].data.push(Math.round(newSales));
            chart.data.datasets[1].data.push(Math.round(newOrders));
            chart.data.datasets[2].data.push(newProducts);

            if (chart.data.datasets[0].data.length > 7) {
                chart.data.datasets[0].data.shift();
                chart.data.datasets[1].data.shift();
                chart.data.datasets[2].data.shift();
            }

            chart.update('active');
        }
    }

    /**
     * Update marketplace connection statuses
     */
    updateMarketplaceConnections() {
        Object.keys(this.marketplaces).forEach(marketplace => {
            const connection = this.marketplaces[marketplace];
            
            // Simulate status changes (in real app, this comes from API)
            if (connection.status === 'syncing') {
                const timeSinceSync = Date.now() - connection.lastSync.getTime();
                if (timeSinceSync > 120000) { // 2 minutes
                    connection.status = 'connected';
                    connection.lastSync = new Date();
                    this.showNotification(`${marketplace} senkronizasyonu tamamlandı`, 'success');
                }
            } else if (connection.status === 'connected' && Math.random() < 0.02) {
                // Occasional sync operations
                connection.status = 'syncing';
                console.log(`🔄 ${marketplace} senkronizasyon başlatıldı`);
            }
        });
    }

    /**
     * Section navigation
     */
    showAdminSection(sectionName) {
        // Hide all sections
        document.querySelectorAll('.admin-content-section').forEach(section => {
            section.style.display = 'none';
        });

        // Remove active class from all nav links
        document.querySelectorAll('.admin-nav-link').forEach(link => {
            link.classList.remove('active');
        });

        // Show selected section
        const targetSection = document.getElementById(`admin-${sectionName}-section`);
        if (targetSection) {
            targetSection.style.display = 'block';
        }

        // Add active class to clicked nav link
        const activeLink = document.querySelector(`[onclick="showAdminSection('${sectionName}')"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }

        this.currentSection = sectionName;
        console.log(`📋 Admin switched to ${sectionName} section`);
    }

    /**
     * Store management functions
     */
    async bulkProductUpload() {
        this.showNotification('Toplu ürün yükleme başlatılıyor...', 'info');
        
        try {
            await this.simulateAsyncOperation(4000);
            const newProducts = Math.floor(Math.random() * 50) + 20;
            this.storeData.totalProducts += newProducts;
            this.animateCounter('total-products', this.storeData.totalProducts);
            this.showNotification(`${newProducts} ürün başarıyla yüklendi!`, 'success');
            
        } catch (error) {
            this.showNotification('Ürün yükleme sırasında hata oluştu', 'error');
        }
    }

    async syncAllMarketplaces() {
        this.showNotification('Tüm pazaryerleri senkronize ediliyor...', 'info');
        
        try {
            // Set all to syncing status
            Object.keys(this.marketplaces).forEach(marketplace => {
                if (this.marketplaces[marketplace].status === 'connected') {
                    this.marketplaces[marketplace].status = 'syncing';
                }
            });

            await this.simulateAsyncOperation(6000);
            
            // Complete sync for all marketplaces
            Object.keys(this.marketplaces).forEach(marketplace => {
                this.marketplaces[marketplace].status = 'connected';
                this.marketplaces[marketplace].lastSync = new Date();
            });

            this.showNotification('Tüm pazaryerleri başarıyla senkronize edildi!', 'success');
            
        } catch (error) {
            this.showNotification('Senkronizasyon sırasında hata oluştu', 'error');
        }
    }

    async updatePricesGlobal() {
        const confirmed = confirm('Tüm ürünlerin fiyatları güncellenecek. Devam etmek istediğinizden emin misiniz?');
        if (!confirmed) return;

        this.showNotification('Global fiyat güncellemesi başlatılıyor...', 'warning');
        
        try {
            await this.simulateAsyncOperation(5000);
            this.showNotification('Fiyatlar başarıyla güncellendi!', 'success');
            
        } catch (error) {
            this.showNotification('Fiyat güncellemesi sırasında hata oluştu', 'error');
        }
    }

    stockAlert() {
        this.showNotification(`${this.storeData.lowStock} ürün için stok uyarısı aktif`, 'warning');
        console.log('📦 Stok uyarı sistemi kontrol edildi');
    }

    manageMarketplaces() {
        this.showAdminSection('marketplaces');
        this.showNotification('Pazaryeri yönetim sayfasına yönlendiriliyorsunuz...', 'info');
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Global function assignments for HTML onclick events
        window.showAdminSection = (section) => this.showAdminSection(section);
        window.bulkProductUpload = () => this.bulkProductUpload();
        window.syncAllMarketplaces = () => this.syncAllMarketplaces();
        window.updatePricesGlobal = () => this.updatePricesGlobal();
        window.stockAlert = () => this.stockAlert();
        window.manageMarketplaces = () => this.manageMarketplaces();

        // Keyboard shortcuts for admin
        document.addEventListener('keydown', (e) => {
            if (e.altKey) {
                switch(e.key) {
                    case '1':
                        e.preventDefault();
                        this.showAdminSection('dashboard');
                        break;
                    case '2':
                        e.preventDefault();
                        this.showAdminSection('products');
                        break;
                    case '3':
                        e.preventDefault();
                        this.showAdminSection('orders');
                        break;
                    case 's':
                        e.preventDefault();
                        this.syncAllMarketplaces();
                        break;
                }
            }
        });
    }

    /**
     * Utility functions
     */
    animateCounter(elementId, targetValue, decimals = 0) {
        const element = document.getElementById(elementId);
        if (!element) return;

        const startValue = parseInt(element.textContent.replace(/[^\d.-]/g, '')) || 0;
        const duration = 1800;
        const startTime = Date.now();

        const animate = () => {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const easeOutCubic = 1 - Math.pow(1 - progress, 3);
            const currentValue = startValue + (targetValue - startValue) * easeOutCubic;
            
            element.textContent = decimals > 0 ? 
                currentValue.toFixed(decimals) : 
                Math.floor(currentValue).toLocaleString('tr-TR');

            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };

        animate();
    }

    showNotification(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'info'} alert-dismissible fade show position-fixed`;
        toast.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
            box-shadow: 0 6px 20px rgba(5, 150, 105, 0.2);
            border-radius: 12px;
        `;
        
        const iconMap = {
            error: 'exclamation-circle',
            success: 'check-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        
        toast.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${iconMap[type]} me-2"></i>
                <div class="flex-grow-1 fw-bold">${message}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        document.body.appendChild(toast);

        // Auto remove after 4 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 4000);
    }

    simulateAsyncOperation(duration) {
        return new Promise((resolve) => {
            setTimeout(resolve, duration);
        });
    }

    /**
     * Cleanup on page unload
     */
    destroy() {
        Object.values(this.realTimeIntervals).forEach(interval => {
            if (interval) clearInterval(interval);
        });

        Object.values(this.charts).forEach(chart => {
            if (chart) chart.destroy();
        });

        console.log('🧹 Admin Dashboard cleaned up');
    }
}

// Initialize admin dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.adminDashboard = new AdminDashboard();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.adminDashboard) {
        window.adminDashboard.destroy();
    }
});

// Export for use in other modules
window.AdminDashboard = AdminDashboard; 