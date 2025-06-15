/**
 * Performance Monitoring System - Advanced System & Application Monitoring
 * MesChain-Sync Performance Dashboard v8.0
 * 
 * Features:
 * - 📈 Real-time System Performance Monitoring
 * - 🖥️ Server Resource Tracking (CPU, RAM, Disk, Network)
 * - 🗄️ Database Performance Analytics
 * - ⚡ Application Performance Metrics
 * - 🔍 AI-powered Bottleneck Detection
 * - 📊 Advanced Performance Charts & Analytics
 * - ⚠️ Smart Alert & Threshold Management
 * - 🧪 Load Testing & Stress Analysis
 * - 📱 Mobile Application Performance Tracking
 * - 🎯 Performance Optimization Recommendations
 */
class PerformanceMonitoring {
    constructor() {
        this.performanceEndpoint = '/api/performance';
        this.monitoringUrl = 'wss://performance.meschain-sync.com';
        this.isMonitoringActive = true;
        this.healthScore = 97.8;
        this.metrics = [];
        this.filters = {
            metric: 'all',
            status: 'all',
            timerange: '1h'
        };
        
        // Performance Status Types
        this.statusTypes = {
            'excellent': { name: 'Excellent', color: '#10B981', icon: 'fas fa-check-circle', threshold: 90 },
            'good': { name: 'Good', color: '#3B82F6', icon: 'fas fa-thumbs-up', threshold: 70 },
            'warning': { name: 'Warning', color: '#F59E0B', icon: 'fas fa-exclamation-triangle', threshold: 50 },
            'critical': { name: 'Critical', color: '#EF4444', icon: 'fas fa-times-circle', threshold: 0 }
        };
        
        // Metric Categories
        this.metricCategories = {
            'system': { name: 'System', icon: 'fas fa-server', color: '#059669' },
            'application': { name: 'Application', icon: 'fas fa-code', color: '#3B82F6' },
            'database': { name: 'Database', icon: 'fas fa-database', color: '#F59E0B' },
            'network': { name: 'Network', icon: 'fas fa-network-wired', color: '#8B5CF6' }
        };
        
        // System Performance Metrics
        this.systemMetrics = {
            cpuUsage: 23.4,
            cpuCores: 8,
            cpuTemp: 42,
            ramUsage: 64.8,
            ramFree: 11.3,
            ramTotal: 32,
            diskUsage: 45.2,
            diskIO: 1.2,
            diskSpace: { used: 226, total: 500 },
            networkTraffic: 847,
            networkLatency: 12,
            networkLoad: 847
        };
        
        // Performance Analytics
        this.analytics = {
            uptimePercentage: 99.97,
            avgResponseTime: 124,
            requestsPerSec: '2.4K/s',
            errorRate: 0.03,
            loadBalancerStatus: 'Running',
            cacheHitRate: 94.7,
            activeSessions: 1247,
            lastHealthCheck: '30s ago'
        };
        
        // Database Performance
        this.databaseMetrics = {
            activeConnections: 127,
            queryResponseTime: 12.4,
            slowQueries: 3,
            connectionPool: 200,
            cacheHitRatio: 89.2,
            indexEfficiency: 94.6
        };
        
        // Mobile Performance
        this.mobileMetrics = {
            iosMonitoring: true,
            androidMonitoring: true,
            webMonitoring: true,
            appLaunchTime: 1.2,
            crashRate: 0.01,
            memoryUsage: 45.8
        };
        
        // Bottleneck Detection
        this.bottleneckAnalysis = {
            detectedIssues: '2 Minor',
            performanceScore: 'A+',
            optimizationTips: 3,
            lastAnalysis: new Date(),
            severity: 'low'
        };
        
        // Load Testing Configuration
        this.loadTestingConfig = {
            concurrent: 1000,
            duration: 60,
            rampUp: 30,
            environment: 'staging'
        };
        
        this.init();
    }
    
    /**
     * Initialize Performance Monitoring System
     */
    init() {
        console.log('📈 Performance Monitoring System başlatılıyor...');
        
        this.setupEventListeners();
        this.loadMetrics();
        this.initializeCharts();
        this.startRealTimeMonitoring();
        this.loadDemoMetrics();
        this.updateSystemHealth();
        this.updateResourceUsage();
        
        console.log('✅ Performance Monitoring System hazır!');
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // System toggle
        document.getElementById('system-toggle')?.addEventListener('click', () => {
            this.toggleSystemMonitoring();
        });
        
        // Filter changes
        document.getElementById('metric-filter')?.addEventListener('change', (e) => {
            this.filters.metric = e.target.value;
            this.renderMetrics();
        });
        
        document.getElementById('status-filter')?.addEventListener('change', (e) => {
            this.filters.status = e.target.value;
            this.renderMetrics();
        });
        
        document.getElementById('timerange-filter')?.addEventListener('change', (e) => {
            this.filters.timerange = e.target.value;
            this.renderMetrics();
        });
        
        // Performance level tabs
        document.querySelectorAll('.level-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                this.switchPerformanceLevel(tab.dataset.level);
            });
        });
        
        // Mobile monitoring switches
        document.getElementById('ios-monitoring')?.addEventListener('change', (e) => {
            this.toggleMobileMonitoring('ios', e.target.checked);
        });
        
        document.getElementById('android-monitoring')?.addEventListener('change', (e) => {
            this.toggleMobileMonitoring('android', e.target.checked);
        });
        
        document.getElementById('web-monitoring')?.addEventListener('change', (e) => {
            this.toggleMobileMonitoring('web', e.target.checked);
        });
    }
    
    /**
     * Load metrics from performance API
     */
    async loadMetrics() {
        try {
            console.log('🔍 Performance metrics yükleniyor...');
            
            // Simulate API call
            setTimeout(() => {
                console.log('✅ Performance data yüklendi');
                this.renderMetrics();
            }, 1000);
        } catch (error) {
            console.error('❌ Metrics loading hatası:', error);
        }
    }
    
    /**
     * Load demo metrics
     */
    loadDemoMetrics() {
        const demoMetrics = [
            {
                id: 1,
                name: 'CPU Usage',
                category: 'system',
                value: 23.4,
                unit: '%',
                status: 'excellent',
                threshold: 80,
                description: 'Processor utilization across all cores',
                lastUpdate: new Date(Date.now() - 30000),
                trend: 'stable',
                details: 'Average CPU usage: 23.4%, Peak: 45.2%'
            },
            {
                id: 2,
                name: 'Memory Usage',
                category: 'system',
                value: 64.8,
                unit: '%',
                status: 'good',
                threshold: 85,
                description: 'RAM utilization and memory allocation',
                lastUpdate: new Date(Date.now() - 45000),
                trend: 'increasing',
                details: 'Used: 20.7GB / Total: 32GB, Swap: 2.1GB'
            },
            {
                id: 3,
                name: 'Database Response Time',
                category: 'database',
                value: 12.4,
                unit: 'ms',
                status: 'excellent',
                threshold: 50,
                description: 'Average query execution time',
                lastUpdate: new Date(Date.now() - 60000),
                trend: 'improving',
                details: 'Avg: 12.4ms, P95: 45ms, Slow queries: 3'
            },
            {
                id: 4,
                name: 'Application Throughput',
                category: 'application',
                value: 2400,
                unit: 'req/s',
                status: 'excellent',
                threshold: 1000,
                description: 'Requests processed per second',
                lastUpdate: new Date(Date.now() - 15000),
                trend: 'stable',
                details: 'Current: 2.4K req/s, Peak: 3.8K req/s'
            },
            {
                id: 5,
                name: 'Network Latency',
                category: 'network',
                value: 12,
                unit: 'ms',
                status: 'excellent',
                threshold: 100,
                description: 'Network response time and connectivity',
                lastUpdate: new Date(Date.now() - 20000),
                trend: 'stable',
                details: 'Latency: 12ms, Packet loss: 0.01%'
            },
            {
                id: 6,
                name: 'Disk I/O Performance',
                category: 'system',
                value: 1200,
                unit: 'MB/s',
                status: 'good',
                threshold: 500,
                description: 'Storage read/write performance',
                lastUpdate: new Date(Date.now() - 90000),
                trend: 'fluctuating',
                details: 'Read: 800MB/s, Write: 400MB/s, IOPS: 15K'
            },
            {
                id: 7,
                name: 'Error Rate',
                category: 'application',
                value: 0.03,
                unit: '%',
                status: 'excellent',
                threshold: 1,
                description: 'Application error percentage',
                lastUpdate: new Date(Date.now() - 35000),
                trend: 'stable',
                details: 'Errors: 47 / Total requests: 156,847'
            },
            {
                id: 8,
                name: 'Cache Hit Rate',
                category: 'application',
                value: 94.7,
                unit: '%',
                status: 'excellent',
                threshold: 80,
                description: 'Cache efficiency and performance',
                lastUpdate: new Date(Date.now() - 25000),
                trend: 'improving',
                details: 'Cache hits: 94.7%, Misses: 5.3%, Size: 2.1GB'
            }
        ];
        
        this.metrics = demoMetrics;
        this.renderMetrics();
    }
    
    /**
     * Render metrics list
     */
    renderMetrics() {
        const container = document.getElementById('metrics-list');
        if (!container) return;
        
        const filteredMetrics = this.filterMetrics();
        
        if (filteredMetrics.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-chart-line text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-3 text-success">Sistem Performansı Mükemmel</h5>
                    <p class="text-muted">Seçili filtrelere uygun metrik bulunamadı</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = filteredMetrics.map(metric => `
            <div class="metric-item ${metric.status}" data-id="${metric.id}" onclick="inspectMetric(${metric.id})">
                <div class="status-badge status-${metric.status}">
                    ${this.statusTypes[metric.status]?.name || metric.status.toUpperCase()}
                </div>
                <div class="metric-time">
                    ${this.formatTime(metric.lastUpdate)}
                </div>
                
                <div class="d-flex align-items-start">
                    <div class="me-3">
                        <i class="${this.metricCategories[metric.category]?.icon || 'fas fa-chart-line'} text-${this.getStatusColor(metric.status)}" 
                           style="font-size: 1.5rem;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">
                            ${metric.name}
                            <span class="badge bg-secondary ms-2">${metric.category}</span>
                        </h6>
                        <p class="mb-2 text-muted">${metric.description}</p>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="h5 mb-0 text-${this.getStatusColor(metric.status)}">
                                ${metric.value}${metric.unit}
                            </span>
                            <span class="badge ${this.getTrendBadgeClass(metric.trend)}">${this.getTrendLabel(metric.trend)}</span>
                            <small class="text-muted">Threshold: ${metric.threshold}${metric.unit}</small>
                        </div>
                        <div class="small text-muted">
                            ${metric.details}
                        </div>
                    </div>
                </div>
                
                <div class="metric-actions">
                    <button class="btn btn-sm btn-outline-primary" onclick="optimizeMetric(${metric.id})">
                        <i class="fas fa-cog me-1"></i>Optimize
                    </button>
                    <button class="btn btn-sm btn-outline-info" onclick="analyzeMetric(${metric.id})">
                        <i class="fas fa-chart-bar me-1"></i>Analyze
                    </button>
                    <button class="btn btn-sm btn-outline-warning" onclick="setAlert(${metric.id})">
                        <i class="fas fa-bell me-1"></i>Alert
                    </button>
                </div>
            </div>
        `).join('');
    }
    
    /**
     * Filter metrics based on current filters
     */
    filterMetrics() {
        return this.metrics.filter(metric => {
            if (this.filters.metric !== 'all' && metric.category !== this.filters.metric) {
                return false;
            }
            if (this.filters.status !== 'all' && metric.status !== this.filters.status) {
                return false;
            }
            // Time range filtering could be implemented here
            return true;
        });
    }
    
    /**
     * Get status color class
     */
    getStatusColor(status) {
        const colors = {
            'excellent': 'success',
            'good': 'info',
            'warning': 'warning',
            'critical': 'danger'
        };
        return colors[status] || 'primary';
    }
    
    /**
     * Get trend badge class
     */
    getTrendBadgeClass(trend) {
        const classes = {
            'improving': 'bg-success',
            'stable': 'bg-info',
            'increasing': 'bg-warning',
            'decreasing': 'bg-warning',
            'fluctuating': 'bg-secondary'
        };
        return classes[trend] || 'bg-secondary';
    }
    
    /**
     * Get trend label
     */
    getTrendLabel(trend) {
        const labels = {
            'improving': '📈 Improving',
            'stable': '➡️ Stable',
            'increasing': '⬆️ Increasing',
            'decreasing': '⬇️ Decreasing',
            'fluctuating': '🔄 Fluctuating'
        };
        return labels[trend] || trend;
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
        this.initPerformanceChart();
    }
    
    /**
     * Initialize performance chart
     */
    initPerformanceChart() {
        const ctx = document.getElementById('performanceChart');
        if (!ctx) return;
        
        this.performanceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['50min', '40min', '30min', '20min', '10min', '5min', 'Now'],
                datasets: [
                    {
                        label: 'CPU Usage (%)',
                        data: [18.5, 22.1, 25.8, 23.4, 21.9, 24.2, 23.4],
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Memory Usage (%)',
                        data: [58.2, 61.4, 63.8, 64.8, 62.5, 65.1, 64.8],
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: false,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Response Time (ms)',
                        data: [98, 112, 134, 124, 118, 129, 124],
                        borderColor: '#F59E0B',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        tension: 0.4,
                        fill: false,
                        yAxisID: 'y1'
                    },
                    {
                        label: 'Network Latency (ms)',
                        data: [8, 15, 12, 12, 10, 14, 12],
                        borderColor: '#8B5CF6',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y1'
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
                        max: 100,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        },
                        title: {
                            display: true,
                            text: 'Percentage (%)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        min: 0,
                        max: 200,
                        title: {
                            display: true,
                            text: 'Time (ms)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
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
        // Simulate new performance data every 5-10 seconds
        setInterval(() => {
            this.simulatePerformanceUpdate();
        }, Math.random() * 5000 + 5000);
        
        // Update system metrics every 3 seconds
        setInterval(() => {
            this.updateSystemMetrics();
        }, 3000);
        
        // Update health score every 30 seconds
        setInterval(() => {
            this.updateSystemHealth();
        }, 30000);
        
        // Update resource usage every 10 seconds
        setInterval(() => {
            this.updateResourceUsage();
        }, 10000);
    }
    
    /**
     * Simulate performance data updates
     */
    simulatePerformanceUpdate() {
        if (this.metrics.length === 0) return;
        
        const randomMetric = this.metrics[Math.floor(Math.random() * this.metrics.length)];
        
        // Update metric value with realistic variations
        const variation = (Math.random() - 0.5) * 0.2; // ±10% variation
        randomMetric.value = Math.max(0, randomMetric.value * (1 + variation));
        randomMetric.lastUpdate = new Date();
        
        // Update status based on threshold
        if (randomMetric.value >= randomMetric.threshold * 0.9) {
            randomMetric.status = 'critical';
        } else if (randomMetric.value >= randomMetric.threshold * 0.7) {
            randomMetric.status = 'warning';
        } else if (randomMetric.value >= randomMetric.threshold * 0.4) {
            randomMetric.status = 'good';
        } else {
            randomMetric.status = 'excellent';
        }
        
        // Update trend
        const trends = ['improving', 'stable', 'increasing', 'fluctuating'];
        randomMetric.trend = trends[Math.floor(Math.random() * trends.length)];
        
        this.renderMetrics();
        this.updateAnalytics();
    }
    
    /**
     * Update system metrics
     */
    updateSystemMetrics() {
        // Simulate CPU usage changes
        this.systemMetrics.cpuUsage += (Math.random() - 0.5) * 5;
        this.systemMetrics.cpuUsage = Math.max(0, Math.min(100, this.systemMetrics.cpuUsage));
        
        // Simulate RAM usage changes
        this.systemMetrics.ramUsage += (Math.random() - 0.5) * 3;
        this.systemMetrics.ramUsage = Math.max(0, Math.min(100, this.systemMetrics.ramUsage));
        
        // Simulate temperature changes
        this.systemMetrics.cpuTemp += (Math.random() - 0.5) * 2;
        this.systemMetrics.cpuTemp = Math.max(20, Math.min(80, this.systemMetrics.cpuTemp));
        
        // Update UI
        document.getElementById('cpu-usage').textContent = this.systemMetrics.cpuUsage.toFixed(1) + '%';
        document.getElementById('cpu-load').textContent = this.systemMetrics.cpuUsage.toFixed(1) + '%';
        document.getElementById('cpu-temp').textContent = Math.floor(this.systemMetrics.cpuTemp) + '°C';
        document.getElementById('ram-usage').textContent = this.systemMetrics.ramUsage.toFixed(1) + '%';
        
        // Update RAM free calculation
        const ramUsed = (this.systemMetrics.ramUsage / 100) * this.systemMetrics.ramTotal;
        const ramFree = this.systemMetrics.ramTotal - ramUsed;
        document.getElementById('ram-free').textContent = ramFree.toFixed(1) + 'GB';
        document.getElementById('memory-used').textContent = `${ramUsed.toFixed(1)}GB / ${this.systemMetrics.ramTotal}GB`;
        
        // Update progress bars
        document.getElementById('cpu-progress-bar').style.width = this.systemMetrics.cpuUsage + '%';
        
        // Update progress bar colors based on usage
        const cpuProgressBar = document.getElementById('cpu-progress-bar');
        if (this.systemMetrics.cpuUsage >= 80) {
            cpuProgressBar.style.background = 'linear-gradient(45deg, #EF4444, #DC2626)';
        } else if (this.systemMetrics.cpuUsage >= 60) {
            cpuProgressBar.style.background = 'linear-gradient(45deg, #F59E0B, #D97706)';
        } else {
            cpuProgressBar.style.background = 'linear-gradient(45deg, #059669, #047857)';
        }
    }
    
    /**
     * Update analytics
     */
    updateAnalytics() {
        // Simulate analytics changes
        this.analytics.avgResponseTime += Math.floor((Math.random() - 0.5) * 20);
        this.analytics.avgResponseTime = Math.max(50, Math.min(500, this.analytics.avgResponseTime));
        
        this.analytics.errorRate += (Math.random() - 0.5) * 0.01;
        this.analytics.errorRate = Math.max(0, Math.min(5, this.analytics.errorRate));
        
        // Update UI
        document.getElementById('avg-response-time').textContent = this.analytics.avgResponseTime + 'ms';
        document.getElementById('error-rate').textContent = this.analytics.errorRate.toFixed(2) + '%';
    }
    
    /**
     * Update system health score
     */
    updateSystemHealth() {
        // Calculate health score based on metrics
        let score = 100;
        
        // CPU impact
        if (this.systemMetrics.cpuUsage > 80) score -= 20;
        else if (this.systemMetrics.cpuUsage > 60) score -= 10;
        
        // RAM impact
        if (this.systemMetrics.ramUsage > 90) score -= 20;
        else if (this.systemMetrics.ramUsage > 75) score -= 10;
        
        // Temperature impact
        if (this.systemMetrics.cpuTemp > 70) score -= 15;
        else if (this.systemMetrics.cpuTemp > 60) score -= 5;
        
        // Error rate impact
        if (this.analytics.errorRate > 1) score -= 15;
        else if (this.analytics.errorRate > 0.1) score -= 5;
        
        this.healthScore = Math.max(60, score);
        
        // Update UI
        const healthDisplay = document.getElementById('system-health-display');
        const healthIndicator = healthDisplay.querySelector('.health-indicator');
        const statusElement = document.getElementById('performance-status');
        const statusText = document.getElementById('system-status-text');
        
        healthIndicator.textContent = this.healthScore.toFixed(1) + '%';
        
        // Update colors and status based on health score
        if (this.healthScore >= 90) {
            healthDisplay.className = 'system-health';
            statusElement.className = 'performance-status performance-excellent';
            statusText.textContent = 'Excellent';
        } else if (this.healthScore >= 80) {
            healthDisplay.className = 'system-health health-good';
            statusElement.className = 'performance-status performance-good';
            statusText.textContent = 'Good';
        } else if (this.healthScore >= 60) {
            healthDisplay.className = 'system-health health-warning';
            statusElement.className = 'performance-status performance-warning';
            statusText.textContent = 'Warning';
        } else {
            healthDisplay.className = 'system-health health-critical';
            statusElement.className = 'performance-status performance-critical';
            statusText.textContent = 'Critical';
        }
        
        document.getElementById('uptime-percentage').textContent = this.healthScore.toFixed(2) + '%';
    }
    
    /**
     * Update resource usage
     */
    updateResourceUsage() {
        // Simulate network changes
        this.systemMetrics.networkTraffic += Math.floor((Math.random() - 0.5) * 100);
        this.systemMetrics.networkTraffic = Math.max(100, Math.min(2000, this.systemMetrics.networkTraffic));
        
        this.systemMetrics.networkLatency += Math.floor((Math.random() - 0.5) * 5);
        this.systemMetrics.networkLatency = Math.max(5, Math.min(100, this.systemMetrics.networkLatency));
        
        // Update UI
        document.getElementById('network-traffic').textContent = this.systemMetrics.networkTraffic + 'MB/s';
        document.getElementById('network-latency').textContent = this.systemMetrics.networkLatency + 'ms';
        document.getElementById('network-load').textContent = this.systemMetrics.networkTraffic + 'MB/s';
        
        // Update database metrics
        this.databaseMetrics.queryResponseTime += (Math.random() - 0.5) * 2;
        this.databaseMetrics.queryResponseTime = Math.max(5, Math.min(100, this.databaseMetrics.queryResponseTime));
        
        document.getElementById('db-response-time').textContent = this.databaseMetrics.queryResponseTime.toFixed(1) + 'ms';
    }
    
    /**
     * Toggle system monitoring
     */
    toggleSystemMonitoring() {
        this.isMonitoringActive = !this.isMonitoringActive;
        
        const button = document.getElementById('system-toggle');
        const statusText = document.getElementById('system-status-text');
        
        if (this.isMonitoringActive) {
            button.classList.remove('alerting');
            this.showSuccessMessage('System monitoring aktif edildi!');
        } else {
            button.classList.add('alerting');
            statusText.textContent = 'Stopped';
            this.showWarningMessage('System monitoring durduruldu!');
        }
    }
    
    /**
     * Switch performance level
     */
    switchPerformanceLevel(level) {
        document.querySelectorAll('.level-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        
        document.querySelector(`[data-level="${level}"]`).classList.add('active');
        
        const levelNames = {
            'real-time': 'Real-time Monitoring',
            'historical': 'Historical Analysis'
        };
        
        this.showInfoMessage(`${levelNames[level]} moduna geçildi`);
    }
    
    /**
     * Toggle mobile monitoring
     */
    toggleMobileMonitoring(platform, enabled) {
        const platformNames = {
            'ios': 'iOS App Monitoring',
            'android': 'Android App Monitoring',
            'web': 'Web App Monitoring'
        };
        
        const message = enabled ? 'aktif edildi' : 'devre dışı bırakıldı';
        this.showInfoMessage(`${platformNames[platform]} ${message}`);
        
        // Update mobile metrics
        this.mobileMetrics[`${platform}Monitoring`] = enabled;
    }
    
    /**
     * Inspect metric details
     */
    inspectMetric(metricId) {
        const metric = this.metrics.find(m => m.id === metricId);
        if (!metric) return;
        
        this.showInfoMessage(`${metric.name} metriği detayları inceleniyor...`);
    }
    
    /**
     * Optimize metric
     */
    optimizeMetric(metricId) {
        const metric = this.metrics.find(m => m.id === metricId);
        if (!metric) return;
        
        this.showInfoMessage(`${metric.name} için optimizasyon başlatılıyor...`);
        
        setTimeout(() => {
            this.showSuccessMessage(`${metric.name} optimizasyonu tamamlandı!`);
        }, 3000);
    }
    
    /**
     * Analyze metric
     */
    analyzeMetric(metricId) {
        const metric = this.metrics.find(m => m.id === metricId);
        if (!metric) return;
        
        this.showInfoMessage(`${metric.name} detaylı analizi başlatılıyor...`);
        
        setTimeout(() => {
            this.showSuccessMessage(`${metric.name} analizi tamamlandı! Rapor hazırlandı.`);
        }, 2500);
    }
    
    /**
     * Set alert for metric
     */
    setAlert(metricId) {
        const metric = this.metrics.find(m => m.id === metricId);
        if (!metric) return;
        
        this.showSuccessMessage(`${metric.name} için alert konfigürasyonu açılıyor...`);
    }
    
    /**
     * Refresh all metrics
     */
    refreshAllMetrics() {
        this.showInfoMessage('Tüm metrikler yenileniyor...');
        
        this.metrics.forEach(metric => {
            metric.lastUpdate = new Date();
            metric.value = metric.value * (0.95 + Math.random() * 0.1); // Small random variation
        });
        
        this.renderMetrics();
        
        setTimeout(() => {
            this.showSuccessMessage('Tüm metrikler başarıyla yenilendi!');
        }, 1500);
    }
    
    /**
     * Analyze database performance
     */
    analyzeDatabasePerformance() {
        this.showInfoMessage('Database performans analizi başlatılıyor...');
        
        setTimeout(() => {
            this.showSuccessMessage('Database analizi tamamlandı! Slow query optimizasyonları tespit edildi.');
        }, 4000);
    }
    
    /**
     * Configure mobile monitoring
     */
    configureMobileMonitoring() {
        this.showInfoMessage('Mobile monitoring konfigürasyon paneli açılıyor...');
    }
    
    /**
     * Run bottleneck analysis
     */
    runBottleneckAnalysis() {
        this.showInfoMessage('AI-powered bottleneck analizi başlatılıyor...');
        
        setTimeout(() => {
            // Simulate analysis results
            const issues = Math.floor(Math.random() * 5);
            const score = ['A+', 'A', 'B+', 'B', 'C+'][Math.floor(Math.random() * 5)];
            const tips = Math.floor(Math.random() * 8) + 1;
            
            document.getElementById('detected-issues').textContent = `${issues} Minor`;
            document.getElementById('performance-score').textContent = score;
            document.getElementById('optimization-tips').textContent = `${tips} Available`;
            
            this.showSuccessMessage(`Bottleneck analizi tamamlandı! ${issues} sorun tespit edildi, ${tips} optimizasyon önerisi hazırlandı.`);
        }, 5000);
    }
    
    /**
     * Run performance test
     */
    runPerformanceTest() {
        this.showInfoMessage('Kapsamlı performans testi başlatılıyor...');
        
        setTimeout(() => {
            this.showSuccessMessage('Performance test tamamlandı! Sistem performansı: A+ (97.8/100)');
        }, 6000);
    }
    
    /**
     * Export performance report
     */
    exportPerformanceReport() {
        const report = {
            timestamp: new Date().toISOString(),
            healthScore: this.healthScore,
            systemMetrics: this.systemMetrics,
            analytics: this.analytics,
            databaseMetrics: this.databaseMetrics,
            mobileMetrics: this.mobileMetrics,
            bottleneckAnalysis: this.bottleneckAnalysis,
            metrics: this.metrics
        };
        
        const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `performance-report-${new Date().toISOString().split('T')[0]}.json`;
        a.click();
        URL.revokeObjectURL(url);
        
        this.showSuccessMessage('Performance raporu indirildi!');
    }
    
    /**
     * Configure alerts
     */
    configureAlerts(type) {
        const alertTypes = {
            'thresholds': 'Performance Threshold Configuration',
            'notifications': 'Alert Notification Settings'
        };
        
        this.showInfoMessage(`${alertTypes[type]} açılıyor...`);
    }
    
    /**
     * Run load test
     */
    runLoadTest(testType) {
        const testNames = {
            'cpu': 'CPU Stress Test',
            'memory': 'Memory Load Test',
            'disk': 'Disk I/O Test',
            'network': 'Network Stress Test',
            'database': 'Database Load Test',
            'api': 'API Load Test',
            'concurrent': 'Concurrent Users Test',
            'endurance': 'Endurance Test',
            'spike': 'Spike Testing',
            'scalability': 'Scalability Test',
            'failover': 'Failover Test',
            'recovery': 'Recovery Test'
        };
        
        const testName = testNames[testType] || testType;
        this.showInfoMessage(`${testName} başlatılıyor...`);
        
        setTimeout(() => {
            const score = (Math.random() * 30 + 70).toFixed(1); // 70-100 range
            this.showSuccessMessage(`${testName} tamamlandı! Performance Score: ${score}/100`);
        }, Math.random() * 5000 + 3000);
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
window.inspectMetric = function(metricId) {
    window.performanceMonitoring?.inspectMetric(metricId);
};

window.optimizeMetric = function(metricId) {
    window.performanceMonitoring?.optimizeMetric(metricId);
};

window.analyzeMetric = function(metricId) {
    window.performanceMonitoring?.analyzeMetric(metricId);
};

window.setAlert = function(metricId) {
    window.performanceMonitoring?.setAlert(metricId);
};

window.refreshAllMetrics = function() {
    window.performanceMonitoring?.refreshAllMetrics();
};

window.analyzeDatabasePerformance = function() {
    window.performanceMonitoring?.analyzeDatabasePerformance();
};

window.configureMobileMonitoring = function() {
    window.performanceMonitoring?.configureMobileMonitoring();
};

window.runBottleneckAnalysis = function() {
    window.performanceMonitoring?.runBottleneckAnalysis();
};

window.runPerformanceTest = function() {
    window.performanceMonitoring?.runPerformanceTest();
};

window.exportPerformanceReport = function() {
    window.performanceMonitoring?.exportPerformanceReport();
};

window.configureAlerts = function(type) {
    window.performanceMonitoring?.configureAlerts(type);
};

window.runLoadTest = function(testType) {
    window.performanceMonitoring?.runLoadTest(testType);
}; 