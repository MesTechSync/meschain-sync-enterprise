/**
 * Content Management System - Advanced Content Creation & Publishing
 * MesChain-Sync Content Dashboard v8.0
 * 
 * Features:
 * - 📝 Content Creation Studio & Rich Text Editor
 * - 🎨 Digital Asset Management & Media Library
 * - 📅 Content Calendar & Publishing Workflows
 * - 🔄 Multi-Channel Publishing (Website, Social, Email)
 * - 📊 Content Performance Analytics & Tracking
 * - 🔍 SEO Optimization & Keyword Management
 * - 🌍 Multi-Language Support & Localization
 * - 📈 Content ROI & Engagement Metrics
 */
class ContentManagement {
    constructor() {
        this.contentEndpoint = '/api/content';
        this.assetUrl = 'wss://assets.meschain-sync.com';
        this.isContentActive = true;
        this.contentScore = 85;
        this.content = [];
        this.assets = [];
        this.filters = {
            status: 'all',
            type: 'all',
            channel: 'all'
        };
        
        // Content Status Types
        this.statusTypes = {
            'draft': { name: 'Draft', color: '#6B7280', icon: 'fas fa-edit' },
            'review': { name: 'In Review', color: '#F59E0B', icon: 'fas fa-eye' },
            'published': { name: 'Published', color: '#10B981', icon: 'fas fa-check-circle' },
            'scheduled': { name: 'Scheduled', color: '#3B82F6', icon: 'fas fa-clock' },
            'archived': { name: 'Archived', color: '#EF4444', icon: 'fas fa-archive' }
        };
        
        // Content Types
        this.contentTypes = {
            'article': { name: 'Article', color: '#3B82F6', icon: 'fas fa-newspaper' },
            'video': { name: 'Video', color: '#EF4444', icon: 'fas fa-video' },
            'image': { name: 'Image', color: '#10B981', icon: 'fas fa-image' },
            'document': { name: 'Document', color: '#F59E0B', icon: 'fas fa-file-pdf' },
            'social': { name: 'Social Post', color: '#8B5CF6', icon: 'fas fa-share-alt' }
        };
        
        // Content Statistics
        this.contentStats = {
            totalContent: 1247,
            publishedContent: 896,
            digitalAssets: 3456,
            storageUsed: 2.4,
            monthlyViews: 847000,
            engagementRate: 7.8,
            seoScore: 92,
            keywordRanking: 234
        };
        
        // Content Categories
        this.contentCategories = {
            blogArticles: 487,
            productDescriptions: 234,
            marketingContent: 156,
            socialPosts: 189,
            contentPerformance: 85
        };
        
        // Asset Library
        this.assetLibrary = {
            imageAssets: 2143,
            videoAssets: 234,
            documentAssets: 567,
            storageUsage: 67.8,
            totalStorage: 5000 // GB
        };
        
        // SEO Configuration
        this.seoConfig = {
            autoSeo: true,
            keywordSuggestions: true,
            schemaMarkup: true,
            avgScore: 92,
            totalKeywords: 234
        };
        
        // Publishing Channels
        this.publishingChannels = {
            website: { status: 'active', published: 234 },
            social: { status: 'connected', published: 156 },
            marketplace: { status: 'syncing', published: 89 },
            email: { status: 'ready', published: 67 }
        };
        
        this.init();
    }
    
    /**
     * Initialize Content Management System
     */
    init() {
        console.log('📝 Content Management System başlatılıyor...');
        
        this.setupEventListeners();
        this.loadContent();
        this.initializeCharts();
        this.startRealTimeMonitoring();
        this.loadDemoContent();
        this.updateContentStats();
        this.updateAssetLibrary();
        
        console.log('✅ Content Management System hazır!');
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Content type selector
        document.querySelectorAll('.type-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                this.switchContentType(btn.dataset.type);
            });
        });
        
        // SEO switches
        document.getElementById('auto-seo')?.addEventListener('change', (e) => {
            this.toggleSEOFeature('autoSeo', e.target.checked);
        });
        
        document.getElementById('keyword-suggestions')?.addEventListener('change', (e) => {
            this.toggleSEOFeature('keywordSuggestions', e.target.checked);
        });
        
        document.getElementById('schema-markup')?.addEventListener('change', (e) => {
            this.toggleSEOFeature('schemaMarkup', e.target.checked);
        });
    }
    
    /**
     * Load content from API
     */
    async loadContent() {
        try {
            console.log('🔍 Content data yükleniyor...');
            
            // Simulate API call
            setTimeout(() => {
                console.log('✅ Content data yüklendi');
                this.renderContent();
            }, 1000);
        } catch (error) {
            console.error('❌ Content loading hatası:', error);
        }
    }
    
    /**
     * Load demo content
     */
    loadDemoContent() {
        const demoContent = [
            {
                id: 'CNT-2024-001',
                title: 'Complete Guide to E-commerce Success in 2024',
                type: 'article',
                status: 'published',
                author: 'Sarah Wilson',
                category: 'Blog',
                createdAt: new Date(Date.now() - 7200000), // 2 hours ago
                updatedAt: new Date(Date.now() - 3600000), // 1 hour ago
                publishedAt: new Date(Date.now() - 1800000), // 30 minutes ago
                content: 'Comprehensive guide covering the latest e-commerce trends, strategies, and best practices for marketplace success.',
                channels: ['website', 'social'],
                tags: ['ecommerce', 'marketplace', 'success', 'guide'],
                seoScore: 95,
                views: 2847,
                engagement: 8.2,
                wordCount: 2350,
                readingTime: 12
            },
            {
                id: 'CNT-2024-002',
                title: 'Product Photography Best Practices',
                type: 'video',
                status: 'review',
                author: 'Mike Johnson',
                category: 'Tutorial',
                createdAt: new Date(Date.now() - 10800000), // 3 hours ago
                updatedAt: new Date(Date.now() - 7200000),  // 2 hours ago
                publishedAt: null,
                content: 'Video tutorial showing professional product photography techniques for marketplace listings.',
                channels: ['website', 'social', 'marketplace'],
                tags: ['photography', 'product', 'tutorial', 'video'],
                seoScore: 88,
                views: 0,
                engagement: 0,
                duration: 847, // seconds
                videoSize: 245 // MB
            },
            {
                id: 'CNT-2024-003',
                title: 'Summer Collection 2024 - Social Media Campaign',
                type: 'social',
                status: 'scheduled',
                author: 'Emily Davis',
                category: 'Marketing',
                createdAt: new Date(Date.now() - 5400000), // 1.5 hours ago
                updatedAt: new Date(Date.now() - 3600000), // 1 hour ago
                publishedAt: new Date(Date.now() + 3600000), // 1 hour from now
                content: 'Social media content for promoting the new summer collection across all social platforms.',
                channels: ['social', 'email'],
                tags: ['summer', 'collection', 'campaign', 'social'],
                seoScore: 91,
                views: 0,
                engagement: 0,
                platforms: ['Instagram', 'Facebook', 'Twitter', 'LinkedIn'],
                scheduledPosts: 12
            },
            {
                id: 'CNT-2024-004',
                title: 'Customer Success Stories - Case Study',
                type: 'article',
                status: 'draft',
                author: 'Alex Rodriguez',
                category: 'Case Study',
                createdAt: new Date(Date.now() - 14400000), // 4 hours ago
                updatedAt: new Date(Date.now() - 1800000),  // 30 minutes ago
                publishedAt: null,
                content: 'Compilation of customer success stories and testimonials showcasing the impact of our marketplace solutions.',
                channels: ['website'],
                tags: ['customer', 'success', 'testimonial', 'case-study'],
                seoScore: 0,
                views: 0,
                engagement: 0,
                wordCount: 1850,
                readingTime: 9
            },
            {
                id: 'CNT-2024-005',
                title: 'API Integration Documentation',
                type: 'document',
                status: 'published',
                author: 'Jennifer White',
                category: 'Documentation',
                createdAt: new Date(Date.now() - 18000000), // 5 hours ago
                updatedAt: new Date(Date.now() - 10800000), // 3 hours ago
                publishedAt: new Date(Date.now() - 7200000), // 2 hours ago
                content: 'Technical documentation for developers implementing marketplace API integrations.',
                channels: ['website', 'marketplace'],
                tags: ['api', 'documentation', 'integration', 'technical'],
                seoScore: 87,
                views: 1234,
                engagement: 6.5,
                pages: 47,
                downloads: 189
            },
            {
                id: 'CNT-2024-006',
                title: 'Holiday Season Marketing Strategy',
                type: 'article',
                status: 'archived',
                author: 'Tom Anderson',
                category: 'Strategy',
                createdAt: new Date(Date.now() - 86400000), // 1 day ago
                updatedAt: new Date(Date.now() - 43200000), // 12 hours ago
                publishedAt: new Date(Date.now() - 21600000), // 6 hours ago
                content: 'Comprehensive marketing strategy for the upcoming holiday season across all channels.',
                channels: ['website', 'social', 'email'],
                tags: ['holiday', 'marketing', 'strategy', 'seasonal'],
                seoScore: 93,
                views: 5678,
                engagement: 9.1,
                wordCount: 3200,
                readingTime: 16
            }
        ];
        
        this.content = demoContent;
        this.renderContent();
    }
    
    /**
     * Render content list
     */
    renderContent() {
        const container = document.getElementById('content-list');
        if (!container) return;
        
        const filteredContent = this.filterContent();
        
        if (filteredContent.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-file-alt text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-success">Content Library Organized</h5>
                    <p class="text-muted">No content matches the selected filters</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = filteredContent.map(content => `
            <div class="content-item ${content.status}" data-id="${content.id}" onclick="editContent('${content.id}')">
                <div class="status-badge status-${content.status}">
                    ${this.statusTypes[content.status]?.name || content.status.toUpperCase()}
                </div>
                <div class="metric-time">
                    ${this.formatTime(content.createdAt)}
                </div>
                
                <div class="d-flex align-items-start">
                    <div class="content-thumbnail">
                        <i class="${this.contentTypes[content.type]?.icon || 'fas fa-file'}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">
                            ${content.title}
                            <span class="badge bg-secondary ms-2">${content.id}</span>
                        </h6>
                        <p class="mb-2 text-muted">
                            <i class="fas fa-user me-1"></i>
                            ${content.author} • ${content.category}
                        </p>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-info">${this.contentTypes[content.type]?.name}</span>
                            ${content.channels.map(channel => `
                                <span class="content-channels channel-${channel}">
                                    <i class="fas fa-${this.getChannelIcon(channel)} me-1"></i>
                                    ${channel}
                                </span>
                            `).join('')}
                        </div>
                        <div class="d-flex align-items-center gap-1 mb-2">
                            ${content.tags.map(tag => `
                                <span class="badge bg-light text-dark" style="font-size: 0.7rem;">
                                    #${tag}
                                </span>
                            `).join('')}
                        </div>
                        <div class="small text-muted">
                            <i class="fas fa-align-left me-1"></i>
                            ${content.content.substring(0, 120)}...
                        </div>
                        <div class="small text-muted mt-1">
                            <i class="fas fa-chart-line me-1"></i>
                            Views: ${this.formatNumber(content.views)} | 
                            Engagement: ${content.engagement}% | 
                            SEO: ${content.seoScore}/100
                            ${content.wordCount ? ` | ${content.wordCount} words` : ''}
                            ${content.readingTime ? ` | ${content.readingTime}min read` : ''}
                        </div>
                    </div>
                </div>
                
                <div class="content-actions" style="display: flex; gap: 10px; margin-top: 10px; opacity: 0; transition: opacity 0.3s ease;">
                    <button class="btn btn-sm btn-outline-primary" onclick="editContent('${content.id}')">
                        <i class="fas fa-edit me-1"></i>Edit
                    </button>
                    <button class="btn btn-sm btn-outline-success" onclick="publishContent('${content.id}')">
                        <i class="fas fa-share me-1"></i>Publish
                    </button>
                    <button class="btn btn-sm btn-outline-info" onclick="duplicateContent('${content.id}')">
                        <i class="fas fa-copy me-1"></i>Duplicate
                    </button>
                    <button class="btn btn-sm btn-outline-warning" onclick="analyzeContent('${content.id}')">
                        <i class="fas fa-analytics me-1"></i>Analyze
                    </button>
                </div>
            </div>
        `).join('');
        
        // Add hover effect for actions
        container.querySelectorAll('.content-item').forEach(item => {
            const actions = item.querySelector('.content-actions');
            item.addEventListener('mouseenter', () => {
                actions.style.opacity = '1';
            });
            item.addEventListener('mouseleave', () => {
                actions.style.opacity = '0';
            });
        });
    }
    
    /**
     * Filter content based on current filters
     */
    filterContent() {
        return this.content.filter(content => {
            if (this.filters.status !== 'all' && content.status !== this.filters.status) {
                return false;
            }
            if (this.filters.type !== 'all' && content.type !== this.filters.type) {
                return false;
            }
            if (this.filters.channel !== 'all' && !content.channels.includes(this.filters.channel)) {
                return false;
            }
            return true;
        });
    }
    
    /**
     * Get channel icon
     */
    getChannelIcon(channel) {
        const icons = {
            'website': 'globe',
            'social': 'share-alt',
            'marketplace': 'store',
            'email': 'envelope'
        };
        return icons[channel] || 'circle';
    }
    
    /**
     * Format number with K/M suffix
     */
    formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        }
        if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        return num.toString();
    }
    
    /**
     * Format timestamp
     */
    formatTime(timestamp) {
        const now = new Date();
        const diff = now - timestamp;
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);
        
        if (minutes < 1) return 'Şimdi';
        if (minutes < 60) return `${minutes}dk önce`;
        if (hours < 24) return `${hours}sa önce`;
        return `${days}g önce`;
    }
    
    /**
     * Initialize charts
     */
    initializeCharts() {
        this.initContentChart();
    }
    
    /**
     * Initialize content performance chart
     */
    initContentChart() {
        const ctx = document.getElementById('contentChart');
        if (!ctx) return;
        
        this.contentChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: Array.from({length: 30}, (_, i) => {
                    const date = new Date();
                    date.setDate(date.getDate() - (29 - i));
                    return date.toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit' });
                }),
                datasets: [
                    {
                        label: 'Content Views',
                        data: Array.from({length: 30}, () => Math.floor(Math.random() * 5000) + 1000),
                        borderColor: '#F59E0B',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Content Created',
                        data: Array.from({length: 30}, () => Math.floor(Math.random() * 10) + 1),
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y1'
                    },
                    {
                        label: 'Engagement Rate (%)',
                        data: Array.from({length: 30}, () => (Math.random() * 5) + 5),
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: false,
                        yAxisID: 'y2'
                    },
                    {
                        label: 'SEO Score',
                        data: Array.from({length: 30}, () => Math.floor(Math.random() * 20) + 80),
                        borderColor: '#8B5CF6',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        tension: 0.4,
                        fill: false,
                        yAxisID: 'y3'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        min: 0,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        },
                        title: {
                            display: true,
                            text: 'Views'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        min: 0,
                        max: 20,
                        title: {
                            display: true,
                            text: 'Content Count'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                    y2: {
                        type: 'linear',
                        display: false,
                        min: 0,
                        max: 15
                    },
                    y3: {
                        type: 'linear',
                        display: false,
                        min: 0,
                        max: 100
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }
    
    /**
     * Start real-time monitoring
     */
    startRealTimeMonitoring() {
        // Simulate content activity every 15-25 seconds
        setInterval(() => {
            this.simulateContentActivity();
        }, Math.random() * 10000 + 15000);
        
        // Update statistics every 8 seconds
        setInterval(() => {
            this.updateContentStats();
        }, 8000);
        
        // Update asset metrics every 12 seconds
        setInterval(() => {
            this.updateAssetLibrary();
        }, 12000);
        
        // Update content categories every 20 seconds
        setInterval(() => {
            this.updateContentCategories();
        }, 20000);
    }
    
    /**
     * Simulate content activity
     */
    simulateContentActivity() {
        // Occasionally create new content
        if (Math.random() < 0.2) { // 20% chance
            this.createAutoContent();
        }
        
        // Update existing content metrics
        this.updateContentMetrics();
        
        // Update stats
        this.updateContentStats();
    }
    
    /**
     * Create automatic content simulation
     */
    createAutoContent() {
        const titles = [
            'New Product Launch Guide',
            'Customer Retention Strategies',
            'Marketplace Optimization Tips',
            'Social Media Best Practices',
            'SEO Content Guidelines',
            'Email Marketing Templates'
        ];
        
        const authors = ['Sarah Wilson', 'Mike Johnson', 'Emily Davis', 'Alex Rodriguez', 'Jennifer White'];
        const categories = ['Blog', 'Tutorial', 'Guide', 'Case Study', 'Documentation'];
        const types = ['article', 'video', 'social', 'document'];
        const channels = [['website'], ['social'], ['marketplace'], ['website', 'social'], ['email']];
        
        const newContent = {
            id: `CNT-2024-${String(this.content.length + 1).padStart(3, '0')}`,
            title: titles[Math.floor(Math.random() * titles.length)],
            type: types[Math.floor(Math.random() * types.length)],
            status: 'draft',
            author: authors[Math.floor(Math.random() * authors.length)],
            category: categories[Math.floor(Math.random() * categories.length)],
            createdAt: new Date(),
            updatedAt: new Date(),
            publishedAt: null,
            content: 'Auto-generated content for demonstration purposes.',
            channels: channels[Math.floor(Math.random() * channels.length)],
            tags: ['auto-generated', 'content'],
            seoScore: 0,
            views: 0,
            engagement: 0,
            wordCount: Math.floor(Math.random() * 2000) + 500,
            readingTime: Math.floor(Math.random() * 15) + 3
        };
        
        this.content.unshift(newContent);
        this.renderContent();
        
        this.showInfoMessage(`New content created: ${newContent.title}`);
    }
    
    /**
     * Update content metrics
     */
    updateContentMetrics() {
        let hasUpdates = false;
        
        this.content.forEach(content => {
            if (content.status === 'published') {
                // Simulate view growth
                const viewIncrease = Math.floor(Math.random() * 50) + 1;
                content.views += viewIncrease;
                
                // Simulate engagement changes
                content.engagement += (Math.random() - 0.5) * 0.5;
                content.engagement = Math.max(0, Math.min(15, content.engagement));
                
                hasUpdates = true;
            }
        });
        
        if (hasUpdates) {
            this.renderContent();
        }
    }
    
    /**
     * Update content statistics
     */
    updateContentStats() {
        // Calculate current stats
        const publishedCount = this.content.filter(c => c.status === 'published').length;
        const totalViews = this.content.reduce((sum, c) => sum + c.views, 0);
        
        // Update stats with realistic variations
        this.contentStats.publishedContent = publishedCount;
        this.contentStats.totalContent = this.content.length;
        
        this.contentStats.monthlyViews += Math.floor((Math.random() - 0.5) * 1000);
        this.contentStats.monthlyViews = Math.max(800000, Math.min(900000, this.contentStats.monthlyViews));
        
        this.contentStats.engagementRate += (Math.random() - 0.5) * 0.2;
        this.contentStats.engagementRate = Math.max(6.0, Math.min(10.0, this.contentStats.engagementRate));
        
        // Update UI
        document.getElementById('total-content').textContent = this.contentStats.totalContent;
        document.getElementById('published-content').textContent = this.contentStats.publishedContent;
        document.getElementById('monthly-views').textContent = this.formatNumber(this.contentStats.monthlyViews);
        document.getElementById('engagement-rate').textContent = this.contentStats.engagementRate.toFixed(1) + '%';
    }
    
    /**
     * Update asset library metrics
     */
    updateAssetLibrary() {
        // Simulate asset activity
        this.assetLibrary.imageAssets += Math.floor((Math.random() - 0.3) * 5);
        this.assetLibrary.imageAssets = Math.max(2000, Math.min(2500, this.assetLibrary.imageAssets));
        
        this.assetLibrary.storageUsage += (Math.random() - 0.5) * 1;
        this.assetLibrary.storageUsage = Math.max(60, Math.min(80, this.assetLibrary.storageUsage));
        
        // Update UI
        document.getElementById('image-assets').textContent = this.assetLibrary.imageAssets;
        document.getElementById('storage-usage').textContent = this.assetLibrary.storageUsage.toFixed(1) + '%';
    }
    
    /**
     * Update content categories
     */
    updateContentCategories() {
        // Simulate category changes
        this.contentCategories.blogArticles += Math.floor((Math.random() - 0.4) * 3);
        this.contentCategories.blogArticles = Math.max(450, Math.min(520, this.contentCategories.blogArticles));
        
        // Update UI
        document.getElementById('blog-articles').textContent = this.contentCategories.blogArticles;
    }
    
    /**
     * Switch content type filter
     */
    switchContentType(type) {
        // Update UI
        document.querySelectorAll('.type-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-type="${type}"]`).classList.add('active');
        
        // Update filter
        this.filters.type = type;
        this.renderContent();
        
        this.showInfoMessage(`Content filtered by: ${type === 'all' ? 'All Types' : this.contentTypes[type]?.name || type}`);
    }
    
    /**
     * Toggle SEO feature
     */
    toggleSEOFeature(feature, enabled) {
        const featureNames = {
            'autoSeo': 'Auto SEO Optimization',
            'keywordSuggestions': 'Keyword Suggestions',
            'schemaMarkup': 'Schema Markup'
        };
        
        const message = enabled ? 'aktif edildi' : 'devre dışı bırakıldı';
        this.showInfoMessage(`${featureNames[feature]} ${message}`);
        
        // Update SEO config
        this.seoConfig[feature] = enabled;
    }
    
    /**
     * Create new content
     */
    createNewContent() {
        this.showInfoMessage('Content creation studio açılıyor...');
    }
    
    /**
     * Export content report
     */
    exportContentReport() {
        const report = {
            timestamp: new Date().toISOString(),
            contentStats: this.contentStats,
            contentCategories: this.contentCategories,
            assetLibrary: this.assetLibrary,
            seoConfig: this.seoConfig,
            publishingChannels: this.publishingChannels,
            content: this.content.slice(0, 100) // Last 100 content items
        };
        
        const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `content-report-${new Date().toISOString().split('T')[0]}.json`;
        a.click();
        URL.revokeObjectURL(url);
        
        this.showSuccessMessage('Content raporu indirildi!');
    }
    
    /**
     * Open asset library
     */
    openAssetLibrary() {
        this.showInfoMessage('Digital Asset Library açılıyor...');
    }
    
    /**
     * Analyze SEO performance
     */
    analyzeSEO() {
        this.showInfoMessage('SEO performance analizi başlatılıyor...');
    }
    
    /**
     * Edit content
     */
    editContent(contentId) {
        const content = this.content.find(c => c.id === contentId);
        if (!content) return;
        
        this.showInfoMessage(`${content.title} düzenleniyor...`);
    }
    
    /**
     * Publish content
     */
    publishContent(contentId) {
        const content = this.content.find(c => c.id === contentId);
        if (!content) return;
        
        if (typeof contentId === 'string') {
            // Single content publish
            content.status = 'published';
            content.publishedAt = new Date();
            this.renderContent();
            this.showSuccessMessage(`${content.title} başarıyla yayınlandı!`);
        } else {
            // Bulk publish operation
            this.showInfoMessage('Multi-channel publishing başlatılıyor...');
        }
    }
    
    /**
     * Schedule content
     */
    scheduleContent() {
        this.showInfoMessage('Content scheduling paneli açılıyor...');
    }
    
    /**
     * Duplicate content
     */
    duplicateContent(contentId) {
        const content = this.content.find(c => c.id === contentId);
        if (!content) return;
        
        const duplicatedContent = {
            ...content,
            id: `CNT-2024-${String(this.content.length + 1).padStart(3, '0')}`,
            title: `${content.title} (Copy)`,
            status: 'draft',
            createdAt: new Date(),
            updatedAt: new Date(),
            publishedAt: null,
            views: 0,
            engagement: 0,
            seoScore: 0
        };
        
        this.content.unshift(duplicatedContent);
        this.renderContent();
        this.showSuccessMessage(`${content.title} kopyalandı!`);
    }
    
    /**
     * Analyze content
     */
    analyzeContent(contentId) {
        const content = this.content.find(c => c.id === contentId);
        if (!content) return;
        
        this.showInfoMessage(`${content.title} analiz ediliyor...`);
    }
    
    /**
     * Show success message
     */
    showSuccessMessage(message) {
        this.showToast(message, 'success');
    }
    
    /**
     * Show warning message
     */
    showWarningMessage(message) {
        this.showToast(message, 'warning');
    }
    
    /**
     * Show info message
     */
    showInfoMessage(message) {
        this.showToast(message, 'info');
    }
    
    /**
     * Show error message
     */
    showErrorMessage(message) {
        this.showToast(message, 'danger');
    }
    
    /**
     * Show toast notification
     */
    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        
        const icons = {
            'success': 'check-circle',
            'warning': 'exclamation-triangle',
            'info': 'info-circle',
            'danger': 'exclamation-triangle'
        };
        
        toast.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${icons[type]} me-2"></i>
                ${message}
                <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }
}

// Global functions for HTML onclick events
window.editContent = function(contentId) {
    window.contentManagement?.editContent(contentId);
};

window.publishContent = function(contentId) {
    window.contentManagement?.publishContent(contentId);
};

window.duplicateContent = function(contentId) {
    window.contentManagement?.duplicateContent(contentId);
};

window.analyzeContent = function(contentId) {
    window.contentManagement?.analyzeContent(contentId);
};

window.createNewContent = function() {
    window.contentManagement?.createNewContent();
};

window.exportContentReport = function() {
    window.contentManagement?.exportContentReport();
};

window.openAssetLibrary = function() {
    window.contentManagement?.openAssetLibrary();
};

window.analyzeSEO = function() {
    window.contentManagement?.analyzeSEO();
};

window.scheduleContent = function() {
    window.contentManagement?.scheduleContent();
}; 