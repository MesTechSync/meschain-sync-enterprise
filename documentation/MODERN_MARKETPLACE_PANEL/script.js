// Modern Marketplace Control Panel JavaScript

// Theme Management
const themeToggle = document.getElementById('themeToggle');
const currentTheme = localStorage.getItem('theme') || 'light';

// Set initial theme
document.documentElement.setAttribute('data-theme', currentTheme);
updateThemeIcon(currentTheme);

themeToggle.addEventListener('click', () => {
    const theme = document.documentElement.getAttribute('data-theme');
    const newTheme = theme === 'dark' ? 'light' : 'dark';
    
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateThemeIcon(newTheme);
});

function updateThemeIcon(theme) {
    const icon = themeToggle.querySelector('i');
    if (theme === 'dark') {
        icon.className = 'fas fa-sun';
    } else {
        icon.className = 'fas fa-moon';
    }
}

// Chart.js Configuration
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Amazon',
                data: [12000, 19000, 15000, 25000, 22000, 30000, 28000, 35000, 32000, 40000, 38000, 45000],
                borderColor: '#ff9500',
                backgroundColor: 'rgba(255, 149, 0, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Trendyol',
                data: [8000, 12000, 10000, 18000, 16000, 22000, 20000, 28000, 25000, 32000, 30000, 35000],
                borderColor: '#f27a1a',
                backgroundColor: 'rgba(242, 122, 26, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'eBay',
                data: [5000, 8000, 6000, 12000, 10000, 15000, 13000, 18000, 16000, 22000, 20000, 25000],
                borderColor: '#0064d2',
                backgroundColor: 'rgba(0, 100, 210, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'N11',
                data: [3000, 5000, 4000, 8000, 7000, 10000, 9000, 12000, 11000, 15000, 14000, 18000],
                borderColor: '#ff6600',
                backgroundColor: 'rgba(255, 102, 0, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Monthly Revenue by Marketplace'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });

    // Marketplace Distribution Chart
    const marketplaceCtx = document.getElementById('marketplaceChart').getContext('2d');
    new Chart(marketplaceCtx, {
        type: 'doughnut',
        data: {
            labels: ['Amazon', 'Trendyol', 'eBay', 'N11'],
            datasets: [{
                data: [45, 30, 15, 10],
                backgroundColor: [
                    '#ff9500',
                    '#f27a1a', 
                    '#0064d2',
                    '#ff6600'
                ],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                title: {
                    display: true,
                    text: 'Revenue Distribution'
                }
            }
        }
    });
});

// Real-time Data Updates with Backend Integration
class MarketplaceMonitor {
    constructor() {
        this.updateInterval = 30000; // 30 seconds
        this.apiUrl = './api/dashboard_data.php';
        this.charts = {};
        this.init();
    }

    init() {
        this.loadDashboardData();
        this.setupRealTimeUpdates();
    }

    async loadDashboardData() {
        try {
            const response = await fetch(`${this.apiUrl}?action=dashboard`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            this.updateDashboard(data);
            
        } catch (error) {
            console.error('Error loading dashboard data:', error);
            this.loadMockData(); // Fallback to mock data
        }
    }

    updateDashboard(data) {
        // Update overview metrics
        this.updateOverviewMetrics(data.overview);
        
        // Update charts with real data
        this.updateRevenueChart(data.revenue);
        this.updateMarketplaceChart(data.marketplace_distribution);
        
        // Update recent orders
        this.updateRecentOrders(data.recent_orders);
        
        // Update performance metrics
        this.updatePerformanceMetrics(data.performance_metrics);
        
        // Update sync status
        this.updateSyncStatus(data.sync_status);
        
        // Update alerts
        this.updateAlerts(data.alerts);
    }

    updateOverviewMetrics(overview) {
        if (!overview) return;
        
        // Update total revenue
        const revenueElement = document.querySelector('#totalRevenue');
        if (revenueElement) {
            revenueElement.textContent = '$' + this.formatNumber(overview.total_revenue);
        }
        
        // Update total orders
        const ordersElement = document.querySelector('#totalOrders');
        if (ordersElement) {
            ordersElement.textContent = this.formatNumber(overview.total_orders);
        }
        
        // Update active products
        const productsElement = document.querySelector('#activeProducts');
        if (productsElement) {
            productsElement.textContent = this.formatNumber(overview.active_products);
        }
        
        // Update conversion rate
        const conversionElement = document.querySelector('#conversionRate');
        if (conversionElement) {
            conversionElement.textContent = overview.conversion_rate + '%';
        }
    }

    updateRecentOrders(orders) {
        if (!orders) return;
        
        const ordersTable = document.querySelector('#recentOrdersTable tbody');
        if (!ordersTable) return;
        
        ordersTable.innerHTML = '';
        
        orders.forEach(order => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><span class="fw-bold">${order.id}</span></td>
                <td>${order.customer}</td>
                <td><span class="badge bg-${this.getMarketplaceBadgeColor(order.marketplace)}">${order.marketplace}</span></td>
                <td class="fw-bold text-success">$${order.total}</td>
                <td class="text-muted">${order.date}</td>
            `;
            ordersTable.appendChild(row);
        });
    }

    updatePerformanceMetrics(metrics) {
        if (!metrics) return;
        
        // Update response time
        const responseTimeElement = document.querySelector('#responseTime');
        if (responseTimeElement) {
            responseTimeElement.textContent = metrics.response_time + 'ms';
        }
        
        // Update uptime
        const uptimeElement = document.querySelector('#uptime');
        if (uptimeElement) {
            uptimeElement.textContent = metrics.uptime + '%';
        }
        
        // Update memory usage progress bar
        const memoryBar = document.querySelector('#memoryUsageBar');
        if (memoryBar) {
            memoryBar.style.width = metrics.memory_usage + '%';
            memoryBar.textContent = metrics.memory_usage + '%';
        }
        
        // Update CPU usage progress bar
        const cpuBar = document.querySelector('#cpuUsageBar');
        if (cpuBar) {
            cpuBar.style.width = metrics.cpu_usage + '%';
            cpuBar.textContent = metrics.cpu_usage + '%';
        }
    }

    updateSyncStatus(syncStatus) {
        if (!syncStatus) return;
        
        Object.keys(syncStatus).forEach(marketplace => {
            const status = syncStatus[marketplace];
            const statusElement = document.querySelector(`#${marketplace}Status`);
            
            if (statusElement) {
                const statusClass = status.status === 'connected' ? 'success' : 
                                  status.status === 'warning' ? 'warning' : 'danger';
                
                statusElement.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-${statusClass}">${status.status}</span>
                        <small class="text-muted">${status.last_sync}</small>
                    </div>
                    <div class="mt-1">
                        <small>Products: ${status.products_synced} | Errors: ${status.errors}</small>
                    </div>
                `;
            }
        });
    }

    updateAlerts(alerts) {
        if (!alerts) return;
        
        const alertsContainer = document.querySelector('#alertsContainer');
        if (!alertsContainer) return;
        
        alertsContainer.innerHTML = '';
        
        alerts.forEach(alert => {
            const alertElement = document.createElement('div');
            alertElement.className = `alert alert-${alert.type} alert-dismissible fade show`;
            alertElement.innerHTML = `
                <i class="${alert.icon} me-2"></i>
                ${alert.message}
                <small class="d-block mt-1 text-muted">${alert.time}</small>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertsContainer.appendChild(alertElement);
        });
    }

    loadMockData() {
        // Fallback mock data when API is not available
        const mockData = {
            overview: {
                total_revenue: 1247580,
                total_orders: 8432,
                active_products: 1543,
                conversion_rate: 3.42
            },
            recent_orders: [
                {id: 'ORD-001', customer: 'John Smith', total: '299.99', marketplace: 'Amazon', date: 'Jun 1, 2025'},
                {id: 'ORD-002', customer: 'Sarah Johnson', total: '159.50', marketplace: 'Trendyol', date: 'Jun 1, 2025'},
                {id: 'ORD-003', customer: 'Mike Davis', total: '89.99', marketplace: 'eBay', date: 'May 31, 2025'}
            ],
            performance_metrics: {
                response_time: 145,
                uptime: 99.97,
                memory_usage: 68,
                cpu_usage: 34
            }
        };
        
        this.updateDashboard(mockData);
    }

    formatNumber(num) {
        return new Intl.NumberFormat().format(num);
    }

    getMarketplaceBadgeColor(marketplace) {
        const colors = {
            'Amazon': 'warning',
            'Trendyol': 'danger',
            'eBay': 'primary',
            'N11': 'info',
            'Direct': 'secondary'
        };
        return colors[marketplace] || 'secondary';
    }

    setupRealTimeUpdates() {
        setInterval(() => {
            this.loadDashboardData();
        }, this.updateInterval);
    }

    updateStats() {
        // Legacy method - now handled by loadDashboardData()
        this.loadDashboardData();
            { id: 'pendingOrders', value: this.generateRandomValue(150, 200) },
            { id: 'apiHealth', value: this.generateRandomValue(95, 99) }
        ];

        stats.forEach(stat => {
            const element = document.querySelector(`[data-stat="${stat.id}"]`);
            if (element) {
                this.animateValue(element, stat.value);
            }
        });
    }

    updateApiHealth() {
        const apis = ['amazon', 'trendyol', 'ebay', 'n11'];
        
        apis.forEach(api => {
            const responseTime = this.generateRandomValue(80, 300);
            const health = responseTime < 200 ? 'healthy' : 'slow';
            const percentage = Math.max(100 - (responseTime / 10), 60);

            // Update response time display
            const timeElement = document.querySelector(`[data-api="${api}"] .response-time`);
            if (timeElement) {
                timeElement.textContent = `${responseTime}ms`;
            }

            // Update health badge
            const badgeElement = document.querySelector(`[data-api="${api}"] .badge`);
            if (badgeElement) {
                badgeElement.className = `badge bg-${health === 'healthy' ? 'success' : 'warning'}`;
                badgeElement.textContent = health === 'healthy' ? 'Healthy' : 'Slow';
            }

            // Update progress bar
            const progressElement = document.querySelector(`[data-api="${api}"] .progress-bar`);
            if (progressElement) {
                progressElement.style.width = `${percentage}%`;
                progressElement.className = `progress-bar bg-${health === 'healthy' ? 'success' : 'warning'}`;
            }
        });
    }

    generateRandomValue(min, max) {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }

    animateValue(element, targetValue) {
        const startValue = parseInt(element.textContent.replace(/[^0-9]/g, '')) || 0;
        const duration = 1000;
        const startTime = performance.now();

        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const currentValue = Math.floor(startValue + (targetValue - startValue) * progress);
            
            if (element.textContent.includes('$')) {
                element.textContent = `$${currentValue.toLocaleString()}`;
            } else if (element.textContent.includes('%')) {
                element.textContent = `${currentValue}%`;
            } else {
                element.textContent = currentValue.toLocaleString();
            }

            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };

        requestAnimationFrame(animate);
    }

    setupRealTimeUpdates() {
        setInterval(() => {
            this.updateStats();
            this.updateApiHealth();
        }, this.updateInterval);
    }
}

// Notification System
class NotificationManager {
    constructor() {
        this.notifications = [];
        this.container = this.createContainer();
    }

    createContainer() {
        const container = document.createElement('div');
        container.id = 'notification-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 350px;
        `;
        document.body.appendChild(container);
        return container;
    }

    show(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification alert alert-${type} alert-dismissible fade show`;
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${this.getIcon(type)} me-2"></i>
                <span>${message}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        `;

        this.container.appendChild(notification);

        // Auto-dismiss
        setTimeout(() => {
            if (notification.parentNode) {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 150);
            }
        }, duration);
    }

    getIcon(type) {
        const icons = {
            success: 'check-circle',
            warning: 'exclamation-triangle',
            danger: 'exclamation-circle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }
}

// Quick Actions
class QuickActions {
    constructor() {
        this.init();
    }

    init() {
        this.setupSyncButtons();
        this.setupBulkActions();
    }

    setupSyncButtons() {
        document.querySelectorAll('[data-action="sync"]').forEach(button => {
            button.addEventListener('click', (e) => {
                const marketplace = e.target.dataset.marketplace;
                this.syncMarketplace(marketplace);
            });
        });
    }

    setupBulkActions() {
        document.querySelectorAll('[data-action="bulk"]').forEach(button => {
            button.addEventListener('click', (e) => {
                const action = e.target.dataset.bulkAction;
                this.performBulkAction(action);
            });
        });
    }

    syncMarketplace(marketplace) {
        const button = document.querySelector(`[data-marketplace="${marketplace}"]`);
        const originalHtml = button.innerHTML;
        
        // Show loading state
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Syncing...';
        button.disabled = true;

        // Simulate API call
        setTimeout(() => {
            button.innerHTML = originalHtml;
            button.disabled = false;
            
            notificationManager.show(
                `${marketplace} synchronization completed successfully!`,
                'success'
            );
        }, 2000);
    }

    performBulkAction(action) {
        notificationManager.show(
            `${action} operation started in background`,
            'info'
        );
    }
}

// Search and Filter
class SearchFilter {
    constructor() {
        this.init();
    }

    init() {
        this.setupSearch();
        this.setupFilters();
    }

    setupSearch() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', this.debounce((e) => {
                this.performSearch(e.target.value);
            }, 300));
        }
    }

    setupFilters() {
        document.querySelectorAll('[data-filter]').forEach(filter => {
            filter.addEventListener('change', (e) => {
                this.applyFilter(e.target.dataset.filter, e.target.value);
            });
        });
    }

    performSearch(query) {
        // Implement search logic
        console.log('Searching for:', query);
    }

    applyFilter(filterType, value) {
        // Implement filter logic
        console.log('Applying filter:', filterType, value);
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Initialize components
const marketplaceMonitor = new MarketplaceMonitor();
const notificationManager = new NotificationManager();
const quickActions = new QuickActions();
const searchFilter = new SearchFilter();

// Welcome notification
setTimeout(() => {
    notificationManager.show(
        'Welcome to MesChain-Sync Pro! Your marketplace dashboard is ready.',
        'success'
    );
}, 1000);

// Simulated real-time events
setInterval(() => {
    const events = [
        { message: 'New order received from Amazon', type: 'info' },
        { message: 'Product stock updated on Trendyol', type: 'success' },
        { message: 'Price synchronization completed', type: 'success' },
        { message: 'API rate limit warning for eBay', type: 'warning' }
    ];
    
    const randomEvent = events[Math.floor(Math.random() * events.length)];
    if (Math.random() > 0.7) { // 30% chance
        notificationManager.show(randomEvent.message, randomEvent.type);
    }
}, 15000);

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
    // Ctrl/Cmd + K for search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.focus();
        }
    }
    
    // Ctrl/Cmd + Shift + S for sync all
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'S') {
        e.preventDefault();
        notificationManager.show('Syncing all marketplaces...', 'info');
    }
});

// Progressive Web App features
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then((registration) => {
                console.log('SW registered: ', registration);
            })
            .catch((registrationError) => {
                console.log('SW registration failed: ', registrationError);
            });
    });
}

// Performance monitoring
const performanceObserver = new PerformanceObserver((list) => {
    for (const entry of list.getEntries()) {
        if (entry.entryType === 'navigation') {
            console.log('Page load time:', entry.loadEventEnd - entry.loadEventStart);
        }
    }
});

performanceObserver.observe({ entryTypes: ['navigation'] });

// Error handling
window.addEventListener('error', (e) => {
    console.error('Global error:', e.error);
    notificationManager.show(
        'An error occurred. Please refresh the page if issues persist.',
        'danger'
    );
});

// Responsive sidebar toggle
const sidebarToggle = document.getElementById('sidebarToggle');
if (sidebarToggle) {
    sidebarToggle.addEventListener('click', () => {
        document.querySelector('.sidebar').classList.toggle('show');
    });
}

// Auto-refresh data every 5 minutes
setInterval(() => {
    console.log('Auto-refreshing dashboard data...');
    marketplaceMonitor.updateStats();
    marketplaceMonitor.updateApiHealth();
}, 300000);
