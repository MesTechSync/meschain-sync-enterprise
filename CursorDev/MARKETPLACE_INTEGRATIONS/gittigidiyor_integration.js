/**
 * GittiGidiyor Turkish Marketplace Integration
 * MesChain-Sync Frontend Module v2.0 (eBay Turkey Branch)
 * 
 * Features:
 * - Turkish auction system
 * - eBay synchronization
 * - Turkish Lira formatting
 * - Real-time bidding tracking
 * - Local Turkish shipping
 * - Cross-platform listing
 */
class GittiGidiyorIntegration {
    constructor() {
        this.apiEndpoint = '/api/gittigidiyor';
        this.ebayApiEndpoint = '/api/ebay-turkey';
        this.connectionStatus = 'testing';
        this.lastDataUpdate = null;
        this.auctions = [];
        this.activeAuctions = [];
        this.metrics = {
            auctionRevenue: 0,
            activeAuctionsCount: 0,
            turkeyShippingRate: 0,
            sellerRating: 0,
            dailyTarget: 5000 // TL
        };
        
        // GittiGidiyor specific configurations
        this.gittigidiyorConfig = {
            apiVersion: '2.0',
            marketplace: 'gittigidiyor',
            currency: 'TRY',
            locale: 'tr-TR',
            timezone: 'Europe/Istanbul',
            brandColors: {
                primary: '#FF7A00',
                secondary: '#E85D00',
                accent: '#CC5200'
            },
            auctionTypes: ['classic', 'dutch', 'reserve', 'buy_now'],
            shippingMethods: ['ptt', 'yurtici', 'aras', 'mng', 'ups_turkey'],
            categoryMappings: {
                'electronics': 'Elektronik',
                'collectibles': 'Koleksiyon',
                'automotive': 'Otomotiv',
                'fashion': 'Moda',
                'home': 'Ev & Bahçe',
                'sports': 'Spor & Outdoor'
            }
        };

        // Chart instances
        this.charts = {
            auctions: null,
            category: null,
            realtime: null
        };

        // Polling intervals
        this.pollingIntervals = {
            apiStatus: null,
            auctionData: null,
            ebaySync: null,
            bidTracking: null
        };

        // Auction timers
        this.auctionTimers = new Map();

        console.log('🔨 GittiGidiyor Integration başlatılıyor...');
        this.init();
    }

    async init() {
        try {
            // API bağlantısını test et
            await this.testApiConnection();
            
            // UI event listeners'ları ekle
            this.setupEventListeners();
            
            // Charts'ları başlat
            this.initializeCharts();
            
            // Real-time polling'i başlat
            this.startPolling();
            
            // Demo data yükle (development için)
            await this.loadDemoData();
            
            // Auction timers'ları başlat
            this.startAuctionTimers();
            
            console.log('✅ GittiGidiyor Dashboard hazır!');
        } catch (error) {
            console.error('❌ GittiGidiyor init hatası:', error);
            this.showError('Dashboard başlatma hatası: ' + error.message);
        }
    }

    async testApiConnection() {
        this.updateConnectionStatus('testing', 'GittiGidiyor & eBay Turkey API\'ya bağlanılıyor...');
        
        try {
            // Simulated API test
            await this.delay(2500);
            
            // Mock successful connection
            const testResult = {
                status: 'success',
                apiVersion: '2.0',
                merchantId: 'GG-TR-12345',
                ebayPartner: true,
                permissions: ['read_auctions', 'manage_listings', 'cross_list_ebay', 'shipping_turkey'],
                lastSync: new Date().toISOString()
            };

            this.updateConnectionStatus('success', 'GittiGidiyor & eBay Turkey API bağlantısı başarılı');
            
            // Update nav indicator
            document.getElementById('api-health-indicator').textContent = '🟢';
            document.getElementById('api-status-text').textContent = 'Bağlı & Senkronize';
            
            return testResult;
        } catch (error) {
            this.updateConnectionStatus('error', 'API bağlantı hatası: ' + error.message);
            throw error;
        }
    }

    updateConnectionStatus(status, message) {
        this.connectionStatus = status;
        const alertElement = document.getElementById('connection-alert');
        const statusText = document.getElementById('connection-status-text');
        
        if (alertElement && statusText) {
            alertElement.className = `connection-status connection-${status}`;
            statusText.textContent = message;
            
            // Update icon based on status
            const icon = alertElement.querySelector('.loading-animation') || 
                        alertElement.querySelector('i') || 
                        document.createElement('i');
            
            if (status === 'success') {
                icon.className = 'fas fa-check-circle';
                icon.style.animation = 'none';
            } else if (status === 'error') {
                icon.className = 'fas fa-exclamation-triangle';
                icon.style.animation = 'none';
            } else {
                icon.className = 'loading-animation';
            }
        }
    }

    setupEventListeners() {
        // Global functions for HTML onclick events
        window.refreshAuctions = () => this.refreshAuctions();
        window.createNewAuction = () => this.createNewAuction();
        window.manageAuctions = () => this.manageAuctions();
        window.promoteToFeatured = () => this.promoteToFeatured();
        window.updateAuctionPrices = () => this.updateAuctionPrices();
        window.manageTurkishShipping = () => this.manageTurkishShipping();
        window.openGittiGidiyorSettings = () => this.openSettings();
    }

    async loadDemoData() {
        // GittiGidiyor specific demo auction data
        this.auctions = [
            {
                id: 'GG001',
                title: 'iPhone 14 Pro Max 128GB - Garantili',
                category: 'Elektronik',
                currentBid: 38999.99,
                startingBid: 35000.00,
                reservePrice: 40000.00,
                buyNowPrice: 42999.99,
                bidCount: 23,
                endTime: new Date(Date.now() + 2 * 60 * 60 * 1000), // 2 hours
                isReservePrice: true,
                isFeatured: true,
                shippingMethod: 'yurtici',
                seller: 'TechStore_TR',
                sellerRating: 4.8,
                watchers: 156,
                ebayListed: true,
                revenue: 38999.99
            },
            {
                id: 'GG002',
                title: 'Antika Osmanlı Saati - Koleksiyon',
                category: 'Koleksiyon',
                currentBid: 2850.00,
                startingBid: 1500.00,
                reservePrice: null,
                buyNowPrice: null,
                bidCount: 67,
                endTime: new Date(Date.now() + 45 * 60 * 1000), // 45 minutes
                isReservePrice: false,
                isFeatured: false,
                shippingMethod: 'ptt',
                seller: 'AntikaEvi',
                sellerRating: 4.9,
                watchers: 234,
                ebayListed: false,
                revenue: 2850.00
            },
            {
                id: 'GG003',
                title: 'BMW E46 M3 Yedek Parça Seti',
                category: 'Otomotiv',
                currentBid: 8750.00,
                startingBid: 7000.00,
                reservePrice: 9000.00,
                buyNowPrice: 12000.00,
                bidCount: 34,
                endTime: new Date(Date.now() + 6 * 60 * 60 * 1000), // 6 hours
                isReservePrice: true,
                isFeatured: true,
                shippingMethod: 'aras',
                seller: 'BMWParts_TR',
                sellerRating: 4.6,
                watchers: 89,
                ebayListed: true,
                revenue: 8750.00
            },
            {
                id: 'GG004',
                title: 'Vintage Levis 501 Kot Pantolon',
                category: 'Moda',
                currentBid: 450.00,
                startingBid: 200.00,
                reservePrice: null,
                buyNowPrice: 799.99,
                bidCount: 12,
                endTime: new Date(Date.now() + 3 * 60 * 60 * 1000), // 3 hours
                isReservePrice: false,
                isFeatured: false,
                shippingMethod: 'mng',
                seller: 'VintageStyle',
                sellerRating: 4.4,
                watchers: 45,
                ebayListed: false,
                revenue: 450.00
            },
            {
                id: 'GG005',
                title: 'Handmade Turkish Carpet 150x200',
                category: 'Ev & Bahçe',
                currentBid: 3200.00,
                startingBid: 2500.00,
                reservePrice: 3500.00,
                buyNowPrice: 4500.00,
                bidCount: 18,
                endTime: new Date(Date.now() + 12 * 60 * 60 * 1000), // 12 hours
                isReservePrice: true,
                isFeatured: true,
                shippingMethod: 'ups_turkey',
                seller: 'TurkishCrafts',
                sellerRating: 4.7,
                watchers: 123,
                ebayListed: true,
                revenue: 3200.00
            }
        ];

        this.activeAuctions = this.auctions.filter(auction => auction.endTime > new Date());

        // Update metrics
        await this.updateMetrics();
        await this.updateAuctions();
        await this.updateRecentActivities();
    }

    async updateMetrics() {
        // Calculate metrics from demo auction data
        const totalRevenue = this.auctions.reduce((sum, auction) => sum + auction.revenue, 0);
        const activeCount = this.activeAuctions.length;
        const avgRating = this.auctions.reduce((sum, a) => sum + a.sellerRating, 0) / this.auctions.length;
        const totalBids = this.auctions.reduce((sum, a) => sum + a.bidCount, 0);

        // Animate counter updates
        this.animateCounter('auction-revenue', this.formatTurkishLira(totalRevenue));
        this.animateCounter('current-month-auction', this.formatTurkishLira(totalRevenue * 0.75));
        
        this.animateCounter('active-auctions-count', activeCount);
        document.getElementById('open-auctions').textContent = activeCount;
        document.getElementById('closed-auctions').textContent = this.auctions.length - activeCount;
        
        this.animateCounter('turkey-shipping-rate', '94.2%');
        document.getElementById('shipments-today').textContent = '28';
        document.getElementById('shipments-yesterday').textContent = '31';
        
        this.animateCounter('seller-rating-gg', avgRating.toFixed(1));
        document.getElementById('feedback-count').textContent = '1,247';
        document.getElementById('positive-rate').textContent = '98.4';

        // Update active auctions count in nav
        document.getElementById('active-auctions').textContent = `${activeCount} Aktif Açık Artırma`;

        // Update chart summary data
        document.getElementById('total-auction-sales').textContent = this.formatTurkishLira(totalRevenue);
        document.getElementById('avg-auction-price').textContent = this.formatTurkishLira(totalRevenue / this.auctions.length);
        document.getElementById('auction-success-rate').textContent = '87.3%';
        document.getElementById('total-bids').textContent = totalBids;

        // Update sidebar metrics
        document.getElementById('auction-sales-total').textContent = this.formatTurkishLira(totalRevenue);
        document.getElementById('auction-profit-margin').textContent = '34.2%';
        
        // eBay integration metrics
        const ebayListedCount = this.auctions.filter(a => a.ebayListed).length;
        document.getElementById('ebay-sync-status').textContent = 'Aktif';
        document.getElementById('cross-listing-count').textContent = ebayListedCount;
        document.getElementById('last-ebay-sync').textContent = '5 dk önce';

        // Turkish market metrics
        document.getElementById('turkey-sales').textContent = this.formatTurkishLira(totalRevenue * 0.85);
        document.getElementById('local-shipping-rate').textContent = '94.2%';
        document.getElementById('turkish-buyer-satisfaction').textContent = '⭐4.7';

        // Real-time metrics
        document.getElementById('last-bid-time').textContent = '3 dk önce';
        document.getElementById('daily-auction-target').textContent = this.formatTurkishLira(5000);
        document.getElementById('featured-auctions').textContent = this.auctions.filter(a => a.isFeatured).length;
        document.getElementById('completed-auctions').textContent = this.auctions.length - activeCount;

        // Category analysis
        const categories = [...new Set(this.auctions.map(a => a.category))];
        const topCategory = categories.reduce((top, cat) => {
            const catBids = this.auctions.filter(a => a.category === cat).reduce((sum, a) => sum + a.bidCount, 0);
            const topBids = this.auctions.filter(a => a.category === top).reduce((sum, a) => sum + a.bidCount, 0);
            return catBids > topBids ? cat : top;
        });
        
        document.getElementById('top-auction-category').textContent = topCategory;
        document.getElementById('most-profitable-category').textContent = this.auctions.sort((a, b) => b.revenue - a.revenue)[0].category;

        // Update next auction end time
        const nextEndingAuction = this.activeAuctions.sort((a, b) => a.endTime - b.endTime)[0];
        if (nextEndingAuction) {
            const timeLeft = this.formatTimeLeft(nextEndingAuction.endTime);
            document.getElementById('next-auction-end').textContent = timeLeft;
        }
    }

    async updateAuctions() {
        const container = document.getElementById('auctions-container');
        if (!container) return;

        let html = '';
        this.activeAuctions.forEach(auction => {
            const timeLeft = this.formatTimeLeft(auction.endTime);
            const isEndingSoon = auction.endTime - new Date() < 60 * 60 * 1000; // 1 hour

            html += `
                <div class="auction-item" data-auction-id="${auction.id}">
                    ${isEndingSoon ? '<div class="auction-ending">BİTİYOR!</div>' : ''}
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h6 class="mb-2">${auction.title}</h6>
                            <div class="mb-2">
                                <span class="category-tag">${auction.category}</span>
                                ${auction.isFeatured ? '<span class="featured-seller">ÖNE ÇIKAN</span>' : ''}
                                ${auction.ebayListed ? '<span class="ebay-integration ms-1">eBay Sync</span>' : ''}
                            </div>
                            <div class="d-flex align-items-center gap-2 text-muted small">
                                <span>🔨 ${auction.bidCount} teklif</span>
                                <span>•</span>
                                <span>👁️ ${auction.watchers} takip</span>
                                <span>•</span>
                                <span>⭐ ${auction.sellerRating}</span>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="bid-display">${this.formatTurkishLira(auction.currentBid)}</div>
                            <small class="text-muted">
                                ${auction.isReservePrice ? `Reserve: ${this.formatTurkishLira(auction.reservePrice)}` : 'Reserve yok'}
                            </small><br>
                            ${auction.buyNowPrice ? `<small class="text-success">Hemen Al: ${this.formatTurkishLira(auction.buyNowPrice)}</small>` : ''}
                        </div>
                        <div class="col-md-3 text-end">
                            <div class="auction-timer mb-2">
                                <i class="fas fa-clock"></i>
                                <span id="timer-${auction.id}">${timeLeft}</span>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    İşlemler
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="editAuction('${auction.id}')">Düzenle</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="viewBidHistory('${auction.id}')">Teklif Geçmişi</a></li>
                                    ${!auction.isFeatured ? '<li><a class="dropdown-item" href="#" onclick="promoteToFeatured(\'' + auction.id + '\')">Öne Çıkar</a></li>' : ''}
                                    ${!auction.ebayListed ? '<li><a class="dropdown-item" href="#" onclick="crossListToEbay(\'' + auction.id + '\')">eBay\'e Ekle</a></li>' : ''}
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="endAuction('${auction.id}')">Erken Bitir</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        if (this.activeAuctions.length === 0) {
            html = `
                <div class="text-center p-4">
                    <i class="fas fa-gavel" style="font-size: 3rem; color: #FF7A00; margin-bottom: 15px;"></i>
                    <h5>Aktif açık artırma bulunmuyor</h5>
                    <p class="text-muted">Yeni açık artırma başlatın ve satışlarınızı artırın!</p>
                    <button class="btn btn-gittigidiyor" onclick="createNewAuction()">
                        <i class="fas fa-plus me-2"></i>Yeni Açık Artırma
                    </button>
                </div>
            `;
        }

        container.innerHTML = html;

        // Setup auction action functions
        window.editAuction = (id) => this.editAuction(id);
        window.viewBidHistory = (id) => this.viewBidHistory(id);
        window.crossListToEbay = (id) => this.crossListToEbay(id);
        window.endAuction = (id) => this.endAuction(id);
    }

    async updateRecentActivities() {
        const container = document.getElementById('recent-activities-gg');
        if (!container) return;

        const activities = [
            { time: '1 dk önce', action: 'Yeni teklif alındı', details: 'iPhone 14 Pro Max - 39.200 TL', icon: 'gavel', color: 'success' },
            { time: '5 dk önce', action: 'eBay senkronizasyonu', details: 'BMW parça seti cross-list edildi', icon: 'sync', color: 'info' },
            { time: '12 dk önce', action: 'Açık artırma başlatıldı', details: 'Vintage Levis kot pantolon', icon: 'play-circle', color: 'warning' },
            { time: '18 dk önce', action: 'Türkiye kargo güncellemesi', details: 'Yurtiçi Kargo entegrasyonu', icon: 'truck', color: 'secondary' },
            { time: '25 dk önce', action: 'Açık artırma bitti', details: 'Antika saat - 2.850 TL ile satıldı', icon: 'flag-checkered', color: 'success' }
        ];

        const html = activities.map(activity => `
            <div class="d-flex align-items-center mb-2 p-2 rounded" style="background: rgba(0,0,0,0.02);">
                <i class="fas fa-${activity.icon} text-${activity.color} me-2"></i>
                <div class="flex-grow-1">
                    <div class="small fw-bold">${activity.action}</div>
                    <div class="text-muted" style="font-size: 0.75rem;">${activity.details}</div>
                </div>
                <small class="text-muted">${activity.time}</small>
            </div>
        `).join('');

        container.innerHTML = html;
    }

    startAuctionTimers() {
        // Update auction timers every second
        setInterval(() => {
            this.activeAuctions.forEach(auction => {
                const timerElement = document.getElementById(`timer-${auction.id}`);
                if (timerElement) {
                    const timeLeft = this.formatTimeLeft(auction.endTime);
                    timerElement.textContent = timeLeft;
                    
                    // Check if auction ended
                    if (auction.endTime <= new Date()) {
                        this.handleAuctionEnd(auction);
                    }
                }
            });
        }, 1000);
    }

    handleAuctionEnd(auction) {
        // Remove from active auctions
        this.activeAuctions = this.activeAuctions.filter(a => a.id !== auction.id);
        
        // Show notification
        this.showSuccessMessage(`Açık artırma bitti: ${auction.title} - ${this.formatTurkishLira(auction.currentBid)}`);
        
        // Refresh UI
        this.updateAuctions();
        this.updateMetrics();
    }

    formatTimeLeft(endTime) {
        const now = new Date();
        const diff = endTime - now;
        
        if (diff <= 0) return 'Bitti';
        
        const hours = Math.floor(diff / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        
        if (hours > 0) {
            return `${hours}s ${minutes}d`;
        } else if (minutes > 0) {
            return `${minutes}d ${seconds}s`;
        } else {
            return `${seconds}s`;
        }
    }

    initializeCharts() {
        this.initAuctionsChart();
        this.initCategoryChart();
        this.initRealtimeChart();
    }

    initAuctionsChart() {
        const ctx = document.getElementById('gittigidiyorSalesChart');
        if (!ctx) return;

        // 30 günlük auction data
        const last30Days = Array.from({length: 30}, (_, i) => {
            const date = new Date();
            date.setDate(date.getDate() - (29 - i));
            return date.toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit' });
        });

        const auctionSales = Array.from({length: 30}, () => Math.floor(Math.random() * 25000) + 5000);
        const ebaySync = Array.from({length: 30}, () => Math.floor(Math.random() * 15000) + 2000);

        this.charts.auctions = new Chart(ctx, {
            type: 'line',
            data: {
                labels: last30Days,
                datasets: [
                    {
                        label: 'GittiGidiyor Açık Artırma (TL)',
                        data: auctionSales,
                        borderColor: this.gittigidiyorConfig.brandColors.primary,
                        backgroundColor: this.gittigidiyorConfig.brandColors.primary + '20',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: this.gittigidiyorConfig.brandColors.primary,
                        pointBorderWidth: 2,
                        pointRadius: 4
                    },
                    {
                        label: 'eBay Cross-list Satışları (TL)',
                        data: ebaySync,
                        borderColor: '#E53238',
                        backgroundColor: '#E53238' + '20',
                        fill: false,
                        tension: 0.4,
                        borderDash: [5, 5],
                        pointBackgroundColor: '#E53238',
                        pointBorderWidth: 2,
                        pointRadius: 3
                    }
                ]
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

    initCategoryChart() {
        const ctx = document.getElementById('gittigidiyorCategoryChart');
        if (!ctx) return;

        const categories = ['Elektronik', 'Koleksiyon', 'Otomotiv', 'Moda', 'Ev & Bahçe'];
        const values = [35, 25, 20, 12, 8];
        const colors = [
            this.gittigidiyorConfig.brandColors.primary,
            this.gittigidiyorConfig.brandColors.secondary,
            this.gittigidiyorConfig.brandColors.accent,
            '#28a745',
            '#6c757d'
        ];

        this.charts.category = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: categories,
                datasets: [{
                    data: values,
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
                                size: 12
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
                                return `${label}: %${percentage}`;
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
    }

    initRealtimeChart() {
        const ctx = document.getElementById('realtimeAuctionChart');
        if (!ctx) return;

        // Son 24 saatlik bid data (her saat için)
        const last24Hours = Array.from({length: 24}, (_, i) => {
            const hour = (new Date().getHours() - (23 - i) + 24) % 24;
            return hour.toString().padStart(2, '0') + ':00';
        });

        const realtimeBids = Array.from({length: 24}, () => Math.floor(Math.random() * 15) + 5);
        const featuredBids = Array.from({length: 24}, () => Math.floor(Math.random() * 8) + 2);

        this.charts.realtime = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: last24Hours,
                datasets: [
                    {
                        label: 'Toplam Teklif Sayısı',
                        data: realtimeBids,
                        backgroundColor: this.gittigidiyorConfig.brandColors.primary + '80',
                        borderColor: this.gittigidiyorConfig.brandColors.primary,
                        borderWidth: 1
                    },
                    {
                        label: 'Öne Çıkan Teklifler',
                        data: featuredBids,
                        backgroundColor: '#FFD700' + '80',
                        borderColor: '#FFD700',
                        borderWidth: 1
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
                                return context.dataset.label + ': ' + context.parsed.y + ' teklif';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Auto-update every 30 seconds
        setInterval(() => {
            this.updateRealtimeChart();
        }, 30000);
    }

    updateRealtimeChart() {
        if (!this.charts.realtime) return;

        // Add new data point and remove oldest
        const newBids = Math.floor(Math.random() * 15) + 5;
        const newFeatured = Math.floor(Math.random() * 8) + 2;
        
        this.charts.realtime.data.datasets[0].data.push(newBids);
        this.charts.realtime.data.datasets[0].data.shift();
        
        this.charts.realtime.data.datasets[1].data.push(newFeatured);
        this.charts.realtime.data.datasets[1].data.shift();

        // Update time labels
        const currentHour = new Date().getHours().toString().padStart(2, '0') + ':00';
        this.charts.realtime.data.labels.push(currentHour);
        this.charts.realtime.data.labels.shift();

        this.charts.realtime.update('none');

        // Update last bid time
        document.getElementById('last-bid-time').textContent = 'Az önce';
    }

    startPolling() {
        // API status check every 30 seconds
        this.pollingIntervals.apiStatus = setInterval(() => {
            this.checkApiStatus();
        }, 30000);

        // Auction data refresh every 1 minute
        this.pollingIntervals.auctionData = setInterval(() => {
            this.refreshAuctionData();
        }, 60000);

        // eBay sync check every 5 minutes
        this.pollingIntervals.ebaySync = setInterval(() => {
            this.checkEbaySync();
        }, 300000);

        // Bid tracking every 10 seconds
        this.pollingIntervals.bidTracking = setInterval(() => {
            this.trackNewBids();
        }, 10000);
    }

    async checkApiStatus() {
        try {
            // Simulated API health check
            const isHealthy = Math.random() > 0.03; // 97% uptime simulation
            
            if (isHealthy) {
                document.getElementById('api-health-indicator').textContent = '🟢';
                document.getElementById('api-status-text').textContent = 'Bağlı & Senkronize';
            } else {
                document.getElementById('api-health-indicator').textContent = '🟡';
                document.getElementById('api-status-text').textContent = 'Senkronizasyon Gecikmesi';
            }
        } catch (error) {
            document.getElementById('api-health-indicator').textContent = '🔴';
            document.getElementById('api-status-text').textContent = 'Bağlantı Hatası';
        }
    }

    async refreshAuctionData() {
        console.log('🔄 Açık artırma verileri güncelleniyor...');
        // Update auction data from API
        // This would typically fetch from API
    }

    async checkEbaySync() {
        console.log('🔗 eBay senkronizasyonu kontrol ediliyor...');
        // Check eBay synchronization status
    }

    async trackNewBids() {
        // Simulate new bids on random auctions
        if (this.activeAuctions.length > 0 && Math.random() > 0.7) {
            const randomAuction = this.activeAuctions[Math.floor(Math.random() * this.activeAuctions.length)];
            const bidIncrement = Math.floor(Math.random() * 500) + 100;
            
            randomAuction.currentBid += bidIncrement;
            randomAuction.bidCount++;
            
            // Show new bid notification
            this.showInfo(`Yeni teklif: ${randomAuction.title} - ${this.formatTurkishLira(randomAuction.currentBid)}`);
            
            // Update UI
            await this.updateAuctions();
            await this.updateMetrics();
        }
    }

    // Action Methods
    async refreshAuctions() {
        this.showLoadingState('auctions-container', 'Açık artırmalar yenileniyor...');
        await this.delay(1500);
        await this.updateAuctions();
        this.showSuccessMessage('Açık artırma listesi güncellendi');
    }

    async createNewAuction() {
        const auctionTitle = prompt('Yeni açık artırma başlığı:');
        if (auctionTitle) {
            console.log('🔨 Yeni açık artırma başlatılıyor:', auctionTitle);
            this.showSuccessMessage(`"${auctionTitle}" açık artırması başlatıldı`);
        }
    }

    async manageAuctions() {
        console.log('🔨 Açık artırma yönetimi açılıyor...');
        this.showInfo('Açık artırma yönetim paneli geliştiriliyor...');
    }

    async promoteToFeatured(auctionId = null) {
        if (auctionId) {
            const auction = this.auctions.find(a => a.id === auctionId);
            if (auction && !auction.isFeatured) {
                auction.isFeatured = true;
                await this.updateAuctions();
                this.showSuccessMessage(`${auction.title} öne çıkarıldı`);
            }
        } else {
            console.log('⭐ Öne çıkarma işlemi başlatıldı');
            this.showInfo('Öne çıkarma seçenekleri gösteriliyor...');
        }
    }

    async updateAuctionPrices() {
        console.log('💰 Açık artırma fiyatları güncelleniyor...');
        this.showLoadingState('auctions-container', 'Fiyatlar güncelleniyor...');
        await this.delay(2000);
        await this.updateAuctions();
        this.showSuccessMessage('Tüm açık artırma fiyatları güncellendi');
    }

    async manageTurkishShipping() {
        console.log('🚚 Türkiye kargo ayarları açılıyor...');
        this.showInfo('Yurtiçi Kargo, PTT, Aras Kargo entegrasyonları yönetiliyor...');
    }

    async openSettings() {
        console.log('⚙️ GittiGidiyor ayarları açılıyor...');
        this.showInfo('Marketplace ve eBay senkronizasyon ayarları paneli geliştiriliyor...');
    }

    async editAuction(id) {
        const auction = this.auctions.find(a => a.id === id);
        if (auction) {
            console.log('✏️ Açık artırma düzenleniyor:', auction.title);
            this.showInfo(`${auction.title} düzenleme paneli açılıyor...`);
        }
    }

    async viewBidHistory(id) {
        const auction = this.auctions.find(a => a.id === id);
        if (auction) {
            console.log('📊 Teklif geçmişi görüntüleniyor:', auction.title);
            this.showInfo(`${auction.title} teklif geçmişi yükleniyor...`);
        }
    }

    async crossListToEbay(id) {
        const auction = this.auctions.find(a => a.id === id);
        if (auction) {
            auction.ebayListed = true;
            await this.updateAuctions();
            this.showSuccessMessage(`${auction.title} eBay'e cross-list edildi`);
        }
    }

    async endAuction(id) {
        const auction = this.activeAuctions.find(a => a.id === id);
        if (auction && confirm(`${auction.title} açık artırmasını erken bitirmek istediğinizden emin misiniz?`)) {
            auction.endTime = new Date();
            this.handleAuctionEnd(auction);
        }
    }

    // Utility Methods
    formatTurkishLira(amount) {
        return new Intl.NumberFormat('tr-TR', {
            style: 'currency',
            currency: 'TRY',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }

    animateCounter(elementId, targetValue) {
        const element = document.getElementById(elementId);
        if (!element) return;

        element.style.transform = 'scale(1.1)';
        element.style.color = this.gittigidiyorConfig.brandColors.primary;
        
        setTimeout(() => {
            element.textContent = targetValue;
            element.style.transform = 'scale(1)';
            element.style.color = '';
        }, 200);
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

        // Auto remove after 4 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 4000);
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    destroy() {
        // Clean up intervals
        Object.values(this.pollingIntervals).forEach(interval => {
            if (interval) clearInterval(interval);
        });

        // Clear auction timers
        this.auctionTimers.clear();

        // Destroy charts
        Object.values(this.charts).forEach(chart => {
            if (chart) chart.destroy();
        });

        console.log('🧹 GittiGidiyor Integration temizlendi');
    }
}

// Export for use in other modules
window.GittiGidiyorIntegration = GittiGidiyorIntegration; 