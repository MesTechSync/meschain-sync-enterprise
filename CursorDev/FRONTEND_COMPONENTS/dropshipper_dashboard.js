/**
 * Dropshipper Dashboard JavaScript
 * MesChain-Sync v3.0 - B2B Product Catalog & Profit Management
 * Features: Profit calculation, Product catalog, Marketplace selection
 */

class DropshipperDashboard {
    constructor() {
        this.currentSection = 'dashboard';
        this.charts = {};
        this.realTimeIntervals = {};
        this.profitData = {
            totalProfit: 12847,
            avgMargin: 28.5,
            activeProducts: 1247,
            monthlyOrders: 156
        };
        this.marketplaces = ['Trendyol', 'N11', 'Amazon', 'eBay', 'Hepsiburada', 'Ozon'];
        this.selectedMarketplaces = new Set(['Trendyol', 'Amazon']);
        this.catalogProducts = [
            {
                id: 'product1',
                name: 'Smartphone Wireless Charger',
                supplierPrice: 45.00,
                suggestedPrice: 89.99,
                supplier: 'Tedarikçi A',
                category: 'Elektronik'
            },
            {
                id: 'product2',
                name: 'Bluetooth Kulaklık',
                supplierPrice: 120.00,
                suggestedPrice: 199.99,
                supplier: 'Tedarikçi B',
                category: 'Elektronik'
            },
            {
                id: 'product3',
                name: 'Laptop Stand',
                supplierPrice: 85.00,
                suggestedPrice: 149.99,
                supplier: 'Tedarikçi C',
                category: 'Ofis'
            }
        ];
        
        console.log('💰 Dropshipper Dashboard initializing...');
        this.init();
    }

    /**
     * Initialize dropshipper dashboard
     */
    async init() {
        try {
            // Initialize charts
            await this.initializeCharts();
            
            // Start real-time updates
            this.startRealTimeUpdates();
            
            // Setup event listeners
            this.setupEventListeners();
            
            // Initialize profit calculator
            this.initializeProfitCalculator();
            
            // Setup marketplace chips
            this.initializeMarketplaceChips();
            
            console.log('✅ Dropshipper Dashboard loaded successfully!');
            
        } catch (error) {
            console.error('❌ Dropshipper Dashboard initialization error:', error);
            this.showNotification('Dashboard yüklenirken hata oluştu', 'error');
        }
    }

    /**
     * Initialize profit analysis chart
     */
    async initializeCharts() {
        const ctx = document.getElementById('profitAnalysisChart');
        if (ctx) {
            this.charts.profitAnalysis = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['30 Gün', '25 Gün', '20 Gün', '15 Gün', '10 Gün', '5 Gün', 'Bugün'],
                    datasets: [{
                        label: 'Günlük Kar (₺)',
                        data: [320, 450, 380, 520, 610, 480, 720],
                        backgroundColor: 'rgba(124, 58, 237, 0.1)',
                        borderColor: '#7c3aed',
                        borderWidth: 4,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#7c3aed',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 3,
                        pointRadius: 8
                    }, {
                        label: 'Kar Marjı (%)',
                        data: [25.2, 28.1, 24.8, 31.5, 33.2, 29.7, 31.2],
                        backgroundColor: 'rgba(5, 150, 105, 0.1)',
                        borderColor: '#059669',
                        borderWidth: 3,
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#059669',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6
                    }, {
                        label: 'Sipariş Sayısı',
                        data: [12, 16, 14, 18, 22, 19, 25],
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        borderColor: '#f59e0b',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#f59e0b',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 3000,
                        easing: 'easeInOutQuart'
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                font: { size: 13, weight: '700' }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(31, 41, 55, 0.95)',
                            titleColor: 'white',
                            bodyColor: 'white',
                            borderColor: '#7c3aed',
                            borderWidth: 2,
                            titleFont: { weight: '700' },
                            bodyFont: { weight: '600' },
                            padding: 12
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(124, 58, 237, 0.1)'
                            },
                            ticks: {
                                font: { weight: '600' }
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(124, 58, 237, 0.05)'
                            },
                            ticks: {
                                font: { weight: '600' }
                            }
                        }
                    }
                }
            });
        }
    }

    /**
     * Start real-time updates for dropshipper dashboard
     */
    startRealTimeUpdates() {
        // Update profit metrics every 60 seconds
        this.realTimeIntervals.metrics = setInterval(() => {
            this.updateProfitMetrics();
        }, 60000);

        // Update charts every 4 minutes
        this.realTimeIntervals.charts = setInterval(() => {
            this.updateCharts();
        }, 240000);

        // Update product catalog every 2 minutes
        this.realTimeIntervals.catalog = setInterval(() => {
            this.updateProductCatalog();
        }, 120000);

        console.log('🔄 Dropshipper real-time updates started');
    }

    /**
     * Update profit metrics with animations
     */
    async updateProfitMetrics() {
        try {
            // Simulate real API data
            const newData = {
                totalProfit: this.profitData.totalProfit + Math.floor(Math.random() * 500) + 50,
                avgMargin: Math.max(20, Math.min(40, this.profitData.avgMargin + (Math.random() - 0.5) * 3)),
                activeProducts: this.profitData.activeProducts + Math.floor(Math.random() * 10),
                monthlyOrders: this.profitData.monthlyOrders + Math.floor(Math.random() * 5)
            };

            // Animate counter updates
            this.animateCounter('total-profit', `₺${newData.totalProfit.toLocaleString('tr-TR')}`);
            this.animateCounter('avg-margin', `${newData.avgMargin.toFixed(1)}%`);
            this.animateCounter('active-products', newData.activeProducts);
            this.animateCounter('monthly-orders', newData.monthlyOrders);

            this.profitData = newData;

        } catch (error) {
            console.error('❌ Profit metrics update error:', error);
        }
    }

    /**
     * Update charts with new profit data
     */
    updateCharts() {
        if (this.charts.profitAnalysis) {
            const chart = this.charts.profitAnalysis;
            
            // Generate new realistic data points
            const newProfit = Math.max(200, Math.min(1000, 720 + (Math.random() - 0.5) * 200));
            const newMargin = Math.max(20, Math.min(40, 31.2 + (Math.random() - 0.5) * 5));
            const newOrders = Math.max(10, Math.min(30, 25 + (Math.random() - 0.5) * 8));

            // Add new data and remove oldest if more than 7 points
            chart.data.datasets[0].data.push(Math.round(newProfit));
            chart.data.datasets[1].data.push(Math.round(newMargin * 10) / 10);
            chart.data.datasets[2].data.push(Math.round(newOrders));

            if (chart.data.datasets[0].data.length > 7) {
                chart.data.datasets[0].data.shift();
                chart.data.datasets[1].data.shift();
                chart.data.datasets[2].data.shift();
            }

            chart.update('active');
        }
    }

    /**
     * Update product catalog (simulate new products)
     */
    updateProductCatalog() {
        // Simulate new product additions
        if (Math.random() < 0.3) {
            console.log('📦 Yeni ürünler kataloğa eklendi');
            this.profitData.activeProducts += Math.floor(Math.random() * 3) + 1;
        }
    }

    /**
     * Initialize profit calculator
     */
    initializeProfitCalculator() {
        const supplierInput = document.getElementById('supplier-price');
        const saleInput = document.getElementById('sale-price');
        
        if (supplierInput && saleInput) {
            // Set initial example values
            supplierInput.value = '45.00';
            saleInput.value = '89.99';
            this.calculateProfit();
        }
    }

    /**
     * Calculate profit margin and net profit
     */
    calculateProfit() {
        const supplierPrice = parseFloat(document.getElementById('supplier-price')?.value) || 0;
        const salePrice = parseFloat(document.getElementById('sale-price')?.value) || 0;
        
        if (supplierPrice > 0 && salePrice > 0) {
            const netProfit = salePrice - supplierPrice;
            const profitMargin = ((netProfit / salePrice) * 100);
            
            // Update UI elements
            const marginInput = document.getElementById('profit-margin-input');
            const netProfitDisplay = document.getElementById('net-profit-display');
            
            if (marginInput) {
                marginInput.value = profitMargin.toFixed(1);
            }
            
            if (netProfitDisplay) {
                netProfitDisplay.textContent = `₺${netProfit.toFixed(2)}`;
                netProfitDisplay.className = netProfit > 0 ? 'alert alert-success mb-0' : 'alert alert-danger mb-0';
            }
        }
    }

    /**
     * Initialize marketplace selection chips
     */
    initializeMarketplaceChips() {
        document.querySelectorAll('.marketplace-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                const marketplace = chip.textContent.trim();
                
                if (chip.classList.contains('selected')) {
                    chip.classList.remove('selected');
                    this.selectedMarketplaces.delete(marketplace);
                } else {
                    chip.classList.add('selected');
                    this.selectedMarketplaces.add(marketplace);
                }
                
                console.log('📱 Selected marketplaces:', Array.from(this.selectedMarketplaces));
            });
        });
    }

    /**
     * Section navigation
     */
    showDropshipperSection(sectionName) {
        // Hide all sections
        document.querySelectorAll('.ds-content-section').forEach(section => {
            section.style.display = 'none';
        });

        // Remove active class from all nav links
        document.querySelectorAll('.ds-nav-link').forEach(link => {
            link.classList.remove('active');
        });

        // Show selected section
        const targetSection = document.getElementById(`ds-${sectionName}-section`);
        if (targetSection) {
            targetSection.style.display = 'block';
        }

        // Add active class to clicked nav link
        const activeLink = document.querySelector(`[onclick="showDropshipperSection('${sectionName}')"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }

        this.currentSection = sectionName;
        console.log(`💼 Dropshipper switched to ${sectionName} section`);
    }

    /**
     * Dropshipper specific functions
     */
    applyProfitSettings() {
        const supplierPrice = parseFloat(document.getElementById('supplier-price')?.value) || 0;
        const salePrice = parseFloat(document.getElementById('sale-price')?.value) || 0;
        
        if (supplierPrice > 0 && salePrice > 0) {
            const netProfit = salePrice - supplierPrice;
            this.showNotification(`Kar marjı ayarları uygulandı: ₺${netProfit.toFixed(2)} kar`, 'success');
        } else {
            this.showNotification('Lütfen geçerli fiyatlar girin', 'warning');
        }
    }

    showFullCatalog() {
        this.showDropshipperSection('catalog');
        this.showNotification('Tam katalog görünümüne geçiliyor...', 'info');
    }

    addToMyStore(productId) {
        const product = this.catalogProducts.find(p => p.id === productId);
        if (product) {
            const selectedMPs = Array.from(this.selectedMarketplaces);
            if (selectedMPs.length === 0) {
                this.showNotification('Lütfen en az bir pazaryeri seçin', 'warning');
                return;
            }
            
            this.showNotification(`${product.name} mağazanıza ekleniyor...`, 'info');
            
            setTimeout(() => {
                this.showNotification(`✅ ${product.name} başarıyla ${selectedMPs.join(', ')} pazaryerlerine eklendi!`, 'success');
                this.profitData.activeProducts++;
                this.animateCounter('active-products', this.profitData.activeProducts);
            }, 2000);
        }
    }

    quickAddProduct() {
        this.showNotification('Hızlı ürün ekleme paneli açılıyor...', 'info');
        // In real implementation, this would open a modal or redirect to add product page
        console.log('🚀 Quick add product triggered');
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Global function assignments for HTML onclick events
        window.showDropshipperSection = (section) => this.showDropshipperSection(section);
        window.calculateProfit = () => this.calculateProfit();
        window.applyProfitSettings = () => this.applyProfitSettings();
        window.showFullCatalog = () => this.showFullCatalog();
        window.addToMyStore = (productId) => this.addToMyStore(productId);
        window.quickAddProduct = () => this.quickAddProduct();

        // Real-time profit calculation
        document.addEventListener('input', (e) => {
            if (e.target.id === 'supplier-price' || e.target.id === 'sale-price') {
                this.calculateProfit();
            }
        });

        // Keyboard shortcuts for dropshipper
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.shiftKey) {
                switch(e.key) {
                    case 'C':
                        e.preventDefault();
                        this.showDropshipperSection('catalog');
                        break;
                    case 'P':
                        e.preventDefault();
                        this.showDropshipperSection('profit');
                        break;
                    case 'A':
                        e.preventDefault();
                        this.quickAddProduct();
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

        // Handle string values with currency symbols
        let startValue = 0;
        let isString = typeof targetValue === 'string';
        
        if (isString) {
            startValue = parseFloat(element.textContent.replace(/[^\d.-]/g, '')) || 0;
            targetValue = parseFloat(targetValue.replace(/[^\d.-]/g, '')) || 0;
        } else {
            startValue = parseInt(element.textContent.replace(/[^\d.-]/g, '')) || 0;
        }

        const duration = 2000;
        const startTime = Date.now();

        const animate = () => {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const easeOutCubic = 1 - Math.pow(1 - progress, 3);
            const currentValue = startValue + (targetValue - startValue) * easeOutCubic;
            
            if (isString) {
                if (elementId === 'total-profit') {
                    element.textContent = `₺${Math.floor(currentValue).toLocaleString('tr-TR')}`;
                } else if (elementId === 'avg-margin') {
                    element.textContent = `${currentValue.toFixed(1)}%`;
                }
            } else {
                element.textContent = Math.floor(currentValue).toLocaleString('tr-TR');
            }

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
            box-shadow: 0 8px 30px rgba(124, 58, 237, 0.25);
            border-radius: 16px;
            border: 2px solid var(--ds-border);
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

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 5000);
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

        console.log('🧹 Dropshipper Dashboard cleaned up');
    }
}

// Initialize dropshipper dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.dropshipperDashboard = new DropshipperDashboard();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.dropshipperDashboard) {
        window.dropshipperDashboard.destroy();
    }
});

// Export for use in other modules
window.DropshipperDashboard = DropshipperDashboard; 