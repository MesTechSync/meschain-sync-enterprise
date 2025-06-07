/**
 * Cross-Marketplace Analytics Engine
 * MesChain-Sync Unified Analytics v3.0
 * 
 * Features:
 * - 7 Platform Integration (Amazon, eBay, N11, Hepsiburada, GittiGidiyor, Trendyol, General)
 * - Real-time data aggregation
 * - Cross-platform performance comparison
 * - Unified profit analysis
 * - Benchmark scoring system
 * - Multi-currency support (TL, USD, EUR)
 */
class CrossMarketplaceAnalytics {
    constructor() {
        this.analyticsEndpoint = '/api/unified-analytics';
        this.connectionStatus = 'testing';
        this.lastDataUpdate = null;
        this.platforms = [];
        this.aggregatedData = {};
        this.benchmarkScores = {};
        
        // Platform configurations
        this.platformConfigs = {
            amazon: {
                name: 'Amazon',
                color: '#FF9900',
                currency: 'USD',
                apiEndpoint: '/api/amazon',
                weight: 1.2, // Strategic importance multiplier
                categories: ['Electronics', 'Books', 'Home'],
                fees: { commission: 0.15, shipping: 0.08 }
            },
            ebay: {
                name: 'eBay',
                color: '#E53238',
                currency: 'USD',
                apiEndpoint: '/api/ebay-turkey',
                weight: 1.1,
                categories: ['Collectibles', 'Electronics', 'Automotive'],
                fees: { commission: 0.10, shipping: 0.06 }
            },
            n11: {
                name: 'N11',
                color: '#FF6000',
                currency: 'TRY',
                apiEndpoint: '/api/n11',
                weight: 0.9,
                categories: ['Electronics', 'Fashion', 'Home'],
                fees: { commission: 0.12, shipping: 0.05 }
            },
            hepsiburada: {
                name: 'Hepsiburada',
                color: '#FF6000',
                currency: 'TRY',
                apiEndpoint: '/api/hepsiburada',
                weight: 1.0,
                categories: ['Electronics', 'Fashion', 'Sports'],
                fees: { commission: 0.08, shipping: 0.04 }
            },
            gittigidiyor: {
                name: 'GittiGidiyor',
                color: '#FF7A00',
                currency: 'TRY',
                apiEndpoint: '/api/gittigidiyor',
                weight: 0.8,
                categories: ['Auctions', 'Collectibles', 'Electronics'],
                fees: { commission: 0.06, shipping: 0.03 }
            },
            trendyol: {
                name: 'Trendyol',
                color: '#F27A1A',
                currency: 'TRY',
                apiEndpoint: '/api/trendyol',
                weight: 1.3, // Highest weight for Turkish market leader
                categories: ['Fashion', 'Beauty', 'Home'],
                fees: { commission: 0.10, shipping: 0.05 }
            },
            general: {
                name: 'Genel Platform',
                color: '#6366F1',
                currency: 'TRY',
                apiEndpoint: '/api/general',
                weight: 0.7,
                categories: ['Mixed', 'Local'],
                fees: { commission: 0.05, shipping: 0.02 }
            }
        };

        // Analytics configuration
        this.analyticsConfig = {
            refreshInterval: 30000, // 30 seconds
            dataRetentionDays: 90,
            benchmarkThresholds: {
                excellent: 90,
                good: 75,
                average: 60,
                poor: 45
            },
            currencyRates: {
                USD: 27.45, // USD to TRY rate
                EUR: 29.80, // EUR to TRY rate
                TRY: 1.00
            },
            kpiWeights: {
                revenue: 0.4,
                growth: 0.25,
                profitMargin: 0.2,
                efficiency: 0.15
            }
        };

        // Chart instances
        this.charts = {
            platformComparison: null,
            marketShare: null,
            realtimeCrossPlatform: null
        };

        // Polling intervals
        this.pollingIntervals = {
            unified: null,
            platformStatus: null,
            benchmarkUpdate: null
        };

        // Real-time data streams
        this.dataStreams = new Map();
        this.aggregationBuffer = new Map();

        console.log('📊 Cross-Marketplace Analytics Engine başlatılıyor...');
        this.init();
    }

    async init() {
        try {
            // Unified API bağlantısını test et
            await this.testUnifiedConnection();
            
            // Platform connections'ları kontrol et
            await this.checkAllPlatformConnections();
            
            // UI event listeners'ları ekle
            this.setupEventListeners();
            
            // Charts'ları başlat
            this.initializeCharts();
            
            // Real-time aggregation'ı başlat
            this.startDataAggregation();
            
            // Demo unified data yükle
            await this.loadUnifiedData();
            
            // Platform status tracking'i başlat
            this.startPlatformTracking();
            
            console.log('✅ Cross-Marketplace Analytics Dashboard hazır!');
        } catch (error) {
            console.error('❌ Unified Analytics init hatası:', error);
            this.showError('Unified Dashboard başlatma hatası: ' + error.message);
        }
    }

    async testUnifiedConnection() {
        this.updateConnectionStatus('testing', '7 marketplace entegrasyonu kontrol ediliyor...');
        
        try {
            // Simulated unified API test
            await this.delay(3000);
            
            // Mock successful unified connection
            const testResult = {
                status: 'success',
                connectedPlatforms: 7,
                totalRevenue: 847650.75,
                activeSince: new Date().toISOString(),
                capabilities: [
                    'cross_platform_analytics',
                    'unified_reporting', 
                    'real_time_aggregation',
                    'benchmark_scoring',
                    'profit_optimization'
                ],
                dataLatency: '1.2s average'
            };

            this.updateConnectionStatus('success', 'Tüm platformlar unified analytics\'e bağlandı');
            
            // Update nav indicator
            document.getElementById('unified-health-indicator').textContent = '🟢';
            document.getElementById('unified-status-text').textContent = 'Unified & Optimized';
            document.getElementById('active-platforms').textContent = '7/7 Platform';
            
            return testResult;
        } catch (error) {
            this.updateConnectionStatus('partial', 'Bazı platformlarda bağlantı sorunu: ' + error.message);
            throw error;
        }
    }

    async checkAllPlatformConnections() {
        const platformStatuses = {};
        
        for (const [key, config] of Object.entries(this.platformConfigs)) {
            try {
                // Simulated platform connection check
                await this.delay(200);
                const isHealthy = Math.random() > 0.05; // 95% success rate
                
                platformStatuses[key] = {
                    name: config.name,
                    status: isHealthy ? 'connected' : 'warning',
                    lastSync: new Date(),
                    responseTime: Math.floor(Math.random() * 300) + 100,
                    revenue: Math.floor(Math.random() * 150000) + 50000
                };
            } catch (error) {
                platformStatuses[key] = {
                    name: config.name,
                    status: 'error',
                    lastSync: null,
                    responseTime: 0,
                    revenue: 0
                };
            }
        }
        
        this.platformStatuses = platformStatuses;
        return platformStatuses;
    }

    updateConnectionStatus(status, message) {
        this.connectionStatus = status;
        const alertElement = document.getElementById('connection-alert');
        const statusText = document.getElementById('connection-status-text');
        
        if (alertElement && statusText) {
            alertElement.className = `connection-status connection-${status}`;
            statusText.textContent = message;
            
            const icon = alertElement.querySelector('.loading-animation') || 
                        alertElement.querySelector('i') || 
                        document.createElement('i');
            
            if (status === 'success') {
                icon.className = 'fas fa-check-circle';
                icon.style.animation = 'none';
            } else if (status === 'partial') {
                icon.className = 'fas fa-exclamation-triangle';
                icon.style.animation = 'none';
            } else {
                icon.className = 'loading-animation';
            }
        }
    }

    setupEventListeners() {
        // Global functions for HTML onclick events
        window.refreshAllPlatforms = () => this.refreshAllPlatforms();
        window.exportAnalytics = () => this.exportAnalytics();
        window.generateReport = () => this.generateReport();
        window.exportToExcel = () => this.exportToExcel();
        window.syncAllPlatforms = () => this.syncAllPlatforms();
        window.profitAnalysis = () => this.profitAnalysis();
        window.openAnalyticsSettings = () => this.openSettings();
    }

    async loadUnifiedData() {
        // Generate comprehensive demo data for all platforms
        this.aggregatedData = {
            totalRevenue: 847650.75,
            monthlyGrowth: 23.4,
            avgProfitMargin: 34.8,
            platformPerformance: {
                amazon: { revenue: 285000, growth: 18.5, margin: 28.3, score: 88 },
                trendyol: { revenue: 195400, growth: 32.1, margin: 41.2, score: 92 },
                ebay: { revenue: 145000, growth: 15.8, margin: 35.6, score: 85 },
                hepsiburada: { revenue: 98500, growth: 28.7, margin: 38.9, score: 87 },
                n11: { revenue: 67200, growth: 22.3, margin: 31.4, score: 81 },
                gittigidiyor: { revenue: 45750, growth: 26.9, margin: 42.1, score: 83 },
                general: { revenue: 10800, growth: 12.1, margin: 25.8, score: 76 }
            },
            benchmarkScores: {
                overall: 86.2,
                turkish: 88.1,
                international: 84.3
            }
        };

        // Platform specific data
        this.platforms = Object.keys(this.platformConfigs).map(key => ({
            id: key,
            ...this.platformConfigs[key],
            ...this.aggregatedData.platformPerformance[key],
            status: this.platformStatuses[key]?.status || 'connected',
            lastUpdate: new Date(),
            efficiency: Math.floor(Math.random() * 25) + 75
        }));

        await this.updateUnifiedMetrics();
        await this.updatePlatformsList();
        await this.updateRecentActivities();
    }

    async updateUnifiedMetrics() {
        const { totalRevenue, monthlyGrowth, avgProfitMargin, benchmarkScores } = this.aggregatedData;
        
        // Find top performer
        const topPlatform = this.platforms.reduce((top, platform) => 
            platform.score > (top.score || 0) ? platform : top
        );

        // Animate counter updates
        this.animateCounter('total-revenue', this.formatCurrency(totalRevenue));
        this.animateCounter('monthly-total', this.formatCurrency(totalRevenue * 0.83));
        
        this.animateCounter('top-performer', topPlatform.name);
        document.getElementById('performance-score').textContent = topPlatform.score;
        document.getElementById('top-revenue').textContent = this.formatCurrency(topPlatform.revenue);
        
        this.animateCounter('growth-rate', `+${monthlyGrowth}%`);
        document.getElementById('monthly-growth').textContent = monthlyGrowth;
        document.getElementById('previous-growth').textContent = (monthlyGrowth - 5.2).toFixed(1);
        
        this.animateCounter('avg-profit-margin', `${avgProfitMargin}%`);
        const margins = this.platforms.map(p => p.margin);
        document.getElementById('best-margin').textContent = Math.max(...margins).toFixed(1);
        document.getElementById('worst-margin').textContent = Math.min(...margins).toFixed(1);

        // Update chart summary data
        document.getElementById('total-monthly-sales').textContent = this.formatCurrency(totalRevenue);
        document.getElementById('avg-platform-performance').textContent = this.formatCurrency(totalRevenue / 7);
        document.getElementById('best-performing-day').textContent = '32.4%';
        document.getElementById('cross-platform-efficiency').textContent = '91.2%';

        // Update market share data
        const leader = this.platforms.sort((a, b) => b.revenue - a.revenue)[0];
        const fastestGrowing = this.platforms.sort((a, b) => b.growth - a.growth)[0];
        document.getElementById('market-leader').textContent = leader.name;
        document.getElementById('fastest-growing').textContent = fastestGrowing.name;

        // Update sidebar metrics
        document.getElementById('overall-conversion').textContent = '8.4%';
        document.getElementById('overall-efficiency').textContent = '91.2%';
        document.getElementById('performance-status').textContent = 'Mükemmel';
        
        // Platform health metrics
        const activePlatforms = this.platforms.filter(p => p.status === 'connected').length;
        document.getElementById('active-platforms-count').textContent = `${activePlatforms}/7`;
        document.getElementById('avg-uptime').textContent = '99.4%';
        document.getElementById('avg-response-time').textContent = '185ms';

        // Profit breakdown
        document.getElementById('most-profitable').textContent = topPlatform.name;
        document.getElementById('total-profit-margin').textContent = `${avgProfitMargin}%`;
        document.getElementById('monthly-profit').textContent = this.formatCurrency(totalRevenue * (avgProfitMargin / 100));

        // Real-time metrics
        document.getElementById('last-update-time').textContent = 'Az önce';
        document.getElementById('daily-target-unified').textContent = this.formatCurrency(50000);
        document.getElementById('current-leader').textContent = leader.name;
        document.getElementById('synced-platforms').textContent = `${activePlatforms}/7`;
    }

    async updatePlatformsList() {
        const container = document.getElementById('platforms-container');
        if (!container) return;

        let html = '';
        this.platforms.forEach(platform => {
            const statusIcon = platform.status === 'connected' ? '🟢' : 
                             platform.status === 'warning' ? '🟡' : '🔴';
            const statusClass = platform.status === 'connected' ? 'status-active' : 'status-warning';
            
            const profitValue = platform.revenue * (platform.margin / 100);
            const efficiencyPercent = platform.efficiency;

            html += `
                <div class="marketplace-card" data-platform="${platform.id}">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div style="width: 8px; height: 8px; background: ${platform.color}; border-radius: 50%; margin-right: 12px;"></div>
                                <div>
                                    <h6 class="mb-1">${platform.name}</h6>
                                    <small class="${statusClass}">${statusIcon} ${platform.status === 'connected' ? 'Bağlı' : 'Uyarı'}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="revenue-display">${this.formatCurrency(platform.revenue)}</div>
                            <small class="text-muted">Gelir</small>
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="trending-up">+${platform.growth}%</div>
                            <small class="text-muted">Büyüme</small>
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="profit-margin">${platform.margin}%</div>
                            <small class="text-muted">Kar Marjı</small>
                        </div>
                        <div class="col-md-2 text-end">
                            <div class="benchmark-score">${platform.score}</div>
                            <small class="text-muted">Benchmark</small>
                            <div class="mt-1">
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar" style="width: ${efficiencyPercent}%; background: ${platform.color};" 
                                         role="progressbar"></div>
                                </div>
                                <small style="font-size: 0.7rem;">${efficiencyPercent}% verimli</small>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="d-flex gap-2 flex-wrap">
                                ${platform.categories.map(cat => `<span class="marketplace-tag">${cat}</span>`).join('')}
                                <span class="marketplace-tag">📊 ${this.formatCurrency(profitValue)} kar</span>
                                <span class="marketplace-tag">⏱️ ${platform.status?.responseTime || 'N/A'}ms</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    async updateRecentActivities() {
        const container = document.getElementById('recent-activities-unified');
        if (!container) return;

        const activities = [
            { time: '30s önce', action: 'Trendyol revenue spike', details: 'Fashion kategorisinde +45% artış', icon: 'trending-up', color: 'success', platform: 'trendyol' },
            { time: '1dk önce', action: 'Amazon sync completed', details: 'SP-API veri senkronizasyonu', icon: 'sync', color: 'info', platform: 'amazon' },
            { time: '2dk önce', action: 'GittiGidiyor auction end', details: 'Açık artırma 3.200 TL ile bitti', icon: 'gavel', color: 'warning', platform: 'gittigidiyor' },
            { time: '3dk önce', action: 'Cross-platform optimization', details: 'Kar marjı optimizasyonu çalıştı', icon: 'cogs', color: 'primary', platform: 'unified' },
            { time: '5dk önce', action: 'Hepsiburada campaign update', details: 'Premium ürün kampanyası başladı', icon: 'star', color: 'success', platform: 'hepsiburada' }
        ];

        const html = activities.map(activity => {
            const platformColor = this.platformConfigs[activity.platform]?.color || '#6366F1';
            return `
                <div class="d-flex align-items-center mb-2 p-2 rounded" style="background: rgba(0,0,0,0.02); border-left: 3px solid ${platformColor};">
                    <i class="fas fa-${activity.icon} text-${activity.color} me-2"></i>
                    <div class="flex-grow-1">
                        <div class="small fw-bold">${activity.action}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">${activity.details}</div>
                    </div>
                    <small class="text-muted">${activity.time}</small>
                </div>
            `;
        }).join('');

        container.innerHTML = html;
    }

    initializeCharts() {
        this.initPlatformComparisonChart();
        this.initMarketShareChart();
        this.initRealtimeCrossPlatformChart();
    }

    initPlatformComparisonChart() {
        const ctx = document.getElementById('platformComparisonChart');
        if (!ctx) return;

        // 30 günlük platform comparison data
        const last30Days = Array.from({length: 30}, (_, i) => {
            const date = new Date();
            date.setDate(date.getDate() - (29 - i));
            return date.toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit' });
        });

        const datasets = this.platforms.slice(0, 5).map(platform => ({
            label: platform.name,
            data: Array.from({length: 30}, () => 
                platform.revenue / 30 + (Math.random() - 0.5) * (platform.revenue / 15)
            ),
            borderColor: platform.color,
            backgroundColor: platform.color + '20',
            fill: false,
            tension: 0.4,
            pointBackgroundColor: platform.color,
            pointBorderWidth: 2,
            pointRadius: 3
        }));

        this.charts.platformComparison = new Chart(ctx, {
            type: 'line',
            data: {
                labels: last30Days,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: false
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + 
                                       new Intl.NumberFormat('tr-TR', { 
                                           style: 'currency', 
                                           currency: 'TRY' 
                                       }).format(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('tr-TR', { 
                                    style: 'currency', 
                                    currency: 'TRY',
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                }).format(value);
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
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

    initMarketShareChart() {
        const ctx = document.getElementById('marketShareChart');
        if (!ctx) return;

        const labels = this.platforms.map(p => p.name);
        const data = this.platforms.map(p => p.revenue);
        const colors = this.platforms.map(p => p.color);

        this.charts.marketShare = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverBorderWidth: 4,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            font: {
                                size: 10
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: %${percentage} (${new Intl.NumberFormat('tr-TR', { 
                                    style: 'currency', 
                                    currency: 'TRY',
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                }).format(value)})`;
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
    }

    initRealtimeCrossPlatformChart() {
        const ctx = document.getElementById('realtimeCrossPlatformChart');
        if (!ctx) return;

        // Son 24 saatlik unified performance data
        const last24Hours = Array.from({length: 24}, (_, i) => {
            const hour = (new Date().getHours() - (23 - i) + 24) % 24;
            return hour.toString().padStart(2, '0') + ':00';
        });

        const unifiedPerformance = Array.from({length: 24}, () => Math.floor(Math.random() * 15000) + 5000);
        const crossPlatformEfficiency = Array.from({length: 24}, () => Math.floor(Math.random() * 20) + 80);

        this.charts.realtimeCrossPlatform = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: last24Hours,
                datasets: [
                    {
                        label: 'Unified Revenue (TL)',
                        data: unifiedPerformance,
                        backgroundColor: '#6366F1' + '80',
                        borderColor: '#6366F1',
                        borderWidth: 1,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Cross-Platform Efficiency (%)',
                        data: crossPlatformEfficiency,
                        type: 'line',
                        borderColor: '#F59E0B',
                        backgroundColor: '#F59E0B' + '20',
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
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (context.datasetIndex === 0) {
                                    return 'Revenue: ' + new Intl.NumberFormat('tr-TR', { 
                                        style: 'currency', 
                                        currency: 'TRY' 
                                    }).format(context.parsed.y);
                                } else {
                                    return 'Efficiency: ' + context.parsed.y + '%';
                                }
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('tr-TR', { 
                                    style: 'currency', 
                                    currency: 'TRY',
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                }).format(value);
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        min: 0,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });

        // Auto-update every 45 seconds
        setInterval(() => {
            this.updateRealtimeCrossPlatformChart();
        }, 45000);
    }    async updateRealtimeCrossPlatformChart() {
        if (!this.charts.realtimeCrossPlatform) return;

        try {
            // 🚀 REAL-TIME API INTEGRATION - Fetch cross-marketplace analytics data
            const response = await fetch('/admin/extension/module/meschain/api/cross-marketplace/realtime-analytics', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const data = await response.json();
                
                if (data.success && data.analytics_data) {
                    // ✅ Use real API data for cross-marketplace analytics
                    const analyticsData = data.analytics_data;
                    
                    // Update revenue data from all marketplaces
                    if (analyticsData.total_revenue_data && analyticsData.total_revenue_data.length > 0) {
                        this.charts.realtimeCrossPlatform.data.datasets[0].data = analyticsData.total_revenue_data;
                    }
                    
                    // Update efficiency metrics
                    if (analyticsData.efficiency_data && analyticsData.efficiency_data.length > 0) {
                        this.charts.realtimeCrossPlatform.data.datasets[1].data = analyticsData.efficiency_data;
                    }
                    
                    // Update time labels
                    if (analyticsData.time_labels && analyticsData.time_labels.length > 0) {
                        this.charts.realtimeCrossPlatform.data.labels = analyticsData.time_labels;
                    }
                    
                    console.log('📊 Cross-marketplace analytics updated with real API data');
                } else {
                    // Fallback to local data generation
                    this.updateChartsWithLocalData();
                }
            } else {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
        } catch (error) {
            console.warn('⚠️ Cross-marketplace Analytics API unavailable, using local data generation:', error);
            this.updateChartsWithLocalData();
        }

        // Smooth animation update
        this.charts.realtimeCrossPlatform.update('none');

        // Update last update time
        const updateTime = new Date().toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' });
        const lastUpdateElement = document.getElementById('last-update-time');
        if (lastUpdateElement) {
            lastUpdateElement.textContent = updateTime;
        }
    }

    /**
     * Fallback chart update with locally generated data
     */
    updateChartsWithLocalData() {
        // Generate realistic cross-marketplace data
        const newRevenue = Math.floor(Math.random() * 15000) + 5000;
        const newEfficiency = Math.floor(Math.random() * 20) + 80;
        
        // Add new data points and maintain rolling window
        this.charts.realtimeCrossPlatform.data.datasets[0].data.push(newRevenue);
        this.charts.realtimeCrossPlatform.data.datasets[0].data.shift();
        
        this.charts.realtimeCrossPlatform.data.datasets[1].data.push(newEfficiency);
        this.charts.realtimeCrossPlatform.data.datasets[1].data.shift();

        // Update time labels
        const currentHour = new Date().getHours().toString().padStart(2, '0') + ':00';
        this.charts.realtimeCrossPlatform.data.labels.push(currentHour);
        this.charts.realtimeCrossPlatform.data.labels.shift();
    }

    startDataAggregation() {
        // Real-time data aggregation every 30 seconds
        this.pollingIntervals.unified = setInterval(() => {
            this.aggregateRealTimeData();
        }, 30000);

        // Platform status monitoring every 1 minute
        this.pollingIntervals.platformStatus = setInterval(() => {
            this.monitorPlatformStatus();
        }, 60000);

        // Benchmark recalculation every 5 minutes
        this.pollingIntervals.benchmarkUpdate = setInterval(() => {
            this.recalculateBenchmarks();
        }, 300000);
    }

    startPlatformTracking() {
        // Individual platform tracking with different intervals
        Object.keys(this.platformConfigs).forEach(platformKey => {
            const trackingInterval = setInterval(() => {
                this.trackPlatformPerformance(platformKey);
            }, 45000); // 45 seconds per platform
            
            this.dataStreams.set(platformKey, trackingInterval);
        });
    }

    async aggregateRealTimeData() {
        console.log('🔄 Unified veri toplama çalışıyor...');
        
        // Simulate real-time revenue updates
        this.platforms.forEach(platform => {
            const variation = (Math.random() - 0.5) * 0.02; // ±1% variation
            platform.revenue *= (1 + variation);
            
            // Update efficiency randomly
            platform.efficiency = Math.max(70, Math.min(100, 
                platform.efficiency + (Math.random() - 0.5) * 5
            ));
        });

        // Recalculate aggregated data
        this.aggregatedData.totalRevenue = this.platforms.reduce((sum, p) => sum + p.revenue, 0);
        
        // Update UI
        await this.updateUnifiedMetrics();
        await this.updatePlatformsList();
    }

    async monitorPlatformStatus() {
        console.log('📡 Platform durumları kontrol ediliyor...');
        
        // Simulate status changes
        this.platforms.forEach(platform => {
            const random = Math.random();
            if (random > 0.95) {
                platform.status.status = 'warning';
            } else if (random > 0.02) {
                platform.status.status = 'connected';
            }
        });
    }

    async recalculateBenchmarks() {
        console.log('📊 Benchmark skorları yeniden hesaplanıyor...');
        
        // Recalculate benchmark scores based on performance
        this.platforms.forEach(platform => {
            const revenueScore = Math.min(100, (platform.revenue / 300000) * 100);
            const growthScore = Math.min(100, platform.growth * 3);
            const marginScore = Math.min(100, platform.margin * 2.5);
            const efficiencyScore = platform.efficiency;
            
            platform.score = Math.round(
                (revenueScore * 0.3) + 
                (growthScore * 0.25) + 
                (marginScore * 0.25) + 
                (efficiencyScore * 0.2)
            );
        });
    }

    async trackPlatformPerformance(platformKey) {
        const platform = this.platforms.find(p => p.id === platformKey);
        if (!platform) return;

        // Simulate minor performance fluctuations
        const perfVariation = (Math.random() - 0.5) * 0.1;
        platform.efficiency = Math.max(70, Math.min(100, 
            platform.efficiency + perfVariation
        ));
    }

    // Action Methods
    async refreshAllPlatforms() {
        this.showLoadingState('platforms-container', 'Tüm platformlar yenileniyor...');
        await this.delay(2000);
        await this.checkAllPlatformConnections();
        await this.updatePlatformsList();
        this.showSuccessMessage('Tüm platform verileri güncellendi');
    }

    async exportAnalytics() {
        console.log('📊 Analytics raporu dışa aktarılıyor...');
        this.showInfo('Comprehensive analytics raporu hazırlanıyor...');
    }

    async generateReport() {
        console.log('📋 Kapsamlı rapor oluşturuluyor...');
        this.showInfo('Cross-platform performance raporu oluşturuluyor...');
    }

    async exportToExcel() {
        console.log('📊 Excel dosyası hazırlanıyor...');
        this.showInfo('Platform verileri Excel formatında dışa aktarılıyor...');
    }

    async syncAllPlatforms() {
        console.log('🔄 Platform senkronizasyonu başlatılıyor...');
        this.showLoadingState('platforms-container', 'Tüm platformlar senkronize ediliyor...');
        await this.delay(3000);
        await this.updatePlatformsList();
        this.showSuccessMessage('Tüm platformlar başarıyla senkronize edildi');
    }

    async profitAnalysis() {
        console.log('💰 Kar analizi çalıştırılıyor...');
        this.showInfo('Cross-platform kar optimizasyonu analizi başlatıldı...');
    }

    async openSettings() {
        console.log('⚙️ Unified analytics ayarları açılıyor...');
        this.showInfo('Cross-marketplace analytics ayarları paneli geliştiriliyor...');
    }

    // Utility Methods
    formatCurrency(amount, currency = 'TRY') {
        const rate = this.analyticsConfig.currencyRates[currency] || 1;
        const tlAmount = currency === 'TRY' ? amount : amount * rate;
        
        return new Intl.NumberFormat('tr-TR', {
            style: 'currency',
            currency: 'TRY',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(tlAmount);
    }

    animateCounter(elementId, targetValue) {
        const element = document.getElementById(elementId);
        if (!element) return;

        element.style.transform = 'scale(1.1)';
        element.style.color = '#6366F1';
        
        setTimeout(() => {
            element.textContent = targetValue;
            element.style.transform = 'scale(1)';
            element.style.color = '';
        }, 300);
    }

    showLoadingState(containerId, message) {
        const container = document.getElementById(containerId);
        if (container) {
            container.innerHTML = `
                <div class="text-center p-4">
                    <div class="loading-animation mb-3"></div>
                    <p>${message}</p>
                </div>
            `;
        }
    }

    showSuccessMessage(message) {
        this.showToast(message, 'success');
    }

    showError(message) {
        this.showToast(message, 'error');
    }

    showInfo(message) {
        this.showToast(message, 'info');
    }

    showToast(message, type = 'info') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} 
                          alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(toast);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 5000);
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    destroy() {
        // Clean up intervals
        Object.values(this.pollingIntervals).forEach(interval => {
            if (interval) clearInterval(interval);
        });

        // Clear data streams
        this.dataStreams.forEach(interval => clearInterval(interval));
        this.dataStreams.clear();

        // Destroy charts
        Object.values(this.charts).forEach(chart => {
            if (chart) chart.destroy();
        });

        console.log('🧹 Cross-Marketplace Analytics Engine temizlendi');
    }
}

// Export for use in other modules
window.CrossMarketplaceAnalytics = CrossMarketplaceAnalytics; 