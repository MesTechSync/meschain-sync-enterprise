/**
 * MesChain-Sync - Modern Dashboard JavaScript
 * VS Code Team Development - UI/UX Modernization
 * Version: 2.0.0
 * Date: June 2, 2025
 */

// ===== GLOBAL UTILITIES =====
const MesChainDashboard = {
    // Configuration
    config: {
        themes: ['light', 'dark', 'auto'],
        currentTheme: localStorage.getItem('meschain-theme') || 'light',
        refreshInterval: 30000, // 30 seconds
        animationSpeed: 250,
        apiEndpoints: {
            metrics: '/admin/index.php?route=extension/mestech/mestech_sync/getMetrics',
            orders: '/admin/index.php?route=extension/mestech/mestech_sync/getOrders',
            products: '/admin/index.php?route=extension/mestech/mestech_sync/getProducts',
            analytics: '/admin/index.php?route=extension/mestech/mestech_sync/getAnalytics'
        }
    },

    // Initialize dashboard
    init() {
        console.log('🚀 MesChain Dashboard Initializing...');
        this.initTheme();
        this.initWidgets();
        this.initEventListeners();
        this.startAutoRefresh();
        this.initCharts();
        this.initNotifications();
        console.log('✅ MesChain Dashboard Ready!');
    },

    // ===== THEME MANAGEMENT =====
    initTheme() {
        this.applyTheme(this.config.currentTheme);
        this.createThemeToggle();
    },

    applyTheme(theme) {
        const body = document.body;
        const root = document.documentElement;
        
        // Remove existing theme classes
        body.classList.remove('theme-light', 'theme-dark');
        root.removeAttribute('data-theme');
        
        if (theme === 'auto') {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            theme = prefersDark ? 'dark' : 'light';
        }
        
        body.classList.add(`theme-${theme}`);
        root.setAttribute('data-theme', theme);
        this.config.currentTheme = theme;
        localStorage.setItem('meschain-theme', theme);
        
        // Update theme toggle icon
        this.updateThemeToggleIcon(theme);
        
        console.log(`🎨 Theme applied: ${theme}`);
    },

    createThemeToggle() {
        const toggle = document.createElement('button');
        toggle.className = 'theme-toggle';
        toggle.setAttribute('aria-label', 'Toggle theme');
        toggle.innerHTML = '<i class="fa fa-sun-o"></i>';
        
        toggle.addEventListener('click', () => {
            const themes = this.config.themes;
            const currentIndex = themes.indexOf(this.config.currentTheme);
            const nextTheme = themes[(currentIndex + 1) % themes.length];
            this.applyTheme(nextTheme);
        });
        
        document.body.appendChild(toggle);
    },

    updateThemeToggleIcon(theme) {
        const toggle = document.querySelector('.theme-toggle i');
        if (toggle) {
            const icons = {
                light: 'fa-sun-o',
                dark: 'fa-moon-o',
                auto: 'fa-adjust'
            };
            toggle.className = `fa ${icons[theme] || 'fa-sun-o'}`;
        }
    },

    // ===== WIDGET MANAGEMENT =====
    initWidgets() {
        this.widgets = {
            metrics: new MetricsWidget(),
            orders: new OrdersWidget(),
            products: new ProductsWidget(),
            analytics: new AnalyticsWidget()
        };

        // Load initial data for all widgets
        Object.values(this.widgets).forEach(widget => {
            widget.loadData();
        });
    },

    // ===== EVENT LISTENERS =====
    initEventListeners() {
        // Responsive navigation toggle
        const navToggle = document.querySelector('.navbar-toggler');
        if (navToggle) {
            navToggle.addEventListener('click', this.toggleMobileNav);
        }

        // Form enhancements
        this.initFormEnhancements();

        // Table enhancements
        this.initTableEnhancements();

        // Window resize handler
        window.addEventListener('resize', this.handleResize.bind(this));

        // Keyboard shortcuts
        document.addEventListener('keydown', this.handleKeyboard.bind(this));
    },

    toggleMobileNav() {
        const nav = document.querySelector('.navbar-collapse');
        if (nav) {
            nav.classList.toggle('show');
        }
    },

    initFormEnhancements() {
        // Add floating labels
        document.querySelectorAll('.form-control-modern').forEach(input => {
            this.addFloatingLabel(input);
        });

        // Add form validation
        document.querySelectorAll('form').forEach(form => {
            this.addFormValidation(form);
        });
    },

    addFloatingLabel(input) {
        const wrapper = document.createElement('div');
        wrapper.className = 'form-floating';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        if (input.placeholder) {
            const label = document.createElement('label');
            label.textContent = input.placeholder;
            label.setAttribute('for', input.id);
            wrapper.appendChild(label);
        }
    },

    addFormValidation(form) {
        form.addEventListener('submit', (e) => {
            if (!this.validateForm(form)) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    },

    validateForm(form) {
        return form.checkValidity();
    },

    initTableEnhancements() {
        document.querySelectorAll('.table-modern').forEach(table => {
            this.addTableSorting(table);
            this.addTableSearch(table);
        });
    },

    addTableSorting(table) {
        const headers = table.querySelectorAll('th[data-sortable]');
        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                this.sortTable(table, header);
            });
        });
    },

    addTableSearch(table) {
        const searchInput = document.querySelector(`[data-table-search="${table.id}"]`);
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.filterTable(table, e.target.value);
            });
        }
    },

    sortTable(table, header) {
        // Table sorting implementation
        console.log('Sorting table by:', header.textContent);
    },

    filterTable(table, searchTerm) {
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const matches = text.includes(searchTerm.toLowerCase());
            row.style.display = matches ? '' : 'none';
        });
    },

    handleResize() {
        // Handle responsive breakpoints
        const width = window.innerWidth;
        if (width < 768) {
            document.body.classList.add('mobile');
        } else {
            document.body.classList.remove('mobile');
        }
    },

    handleKeyboard(e) {
        // Keyboard shortcuts
        if (e.altKey && e.key === 't') {
            const themeToggle = document.querySelector('.theme-toggle');
            if (themeToggle) themeToggle.click();
        }
    },

    // ===== AUTO REFRESH =====
    startAutoRefresh() {
        setInterval(() => {
            Object.values(this.widgets).forEach(widget => {
                if (widget.autoRefresh) {
                    widget.loadData();
                }
            });
        }, this.config.refreshInterval);
    },

    // ===== CHART INITIALIZATION =====
    initCharts() {
        if (typeof Chart !== 'undefined') {
            Chart.defaults.font.family = getComputedStyle(document.documentElement).getPropertyValue('--font-family-primary');
            Chart.defaults.color = getComputedStyle(document.documentElement).getPropertyValue('--gray-600');
        }
    },

    // ===== NOTIFICATIONS =====
    initNotifications() {
        this.notifications = new NotificationManager();
    },

    showNotification(message, type = 'info', duration = 5000) {
        if (this.notifications) {
            this.notifications.show(message, type, duration);
        }
    },

    // ===== UTILITIES =====
    formatNumber(num) {
        return new Intl.NumberFormat('tr-TR').format(num);
    },

    formatCurrency(amount, currency = 'TRY') {
        return new Intl.NumberFormat('tr-TR', {
            style: 'currency',
            currency: currency
        }).format(amount);
    },

    formatDate(date) {
        return new Intl.DateTimeFormat('tr-TR', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }).format(new Date(date));
    },

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
};

// ===== WIDGET BASE CLASS =====
class BaseWidget {
    constructor(selector, options = {}) {
        this.selector = selector;
        this.element = document.querySelector(selector);
        this.options = { autoRefresh: true, ...options };
        this.loading = false;
        this.data = null;
        this.error = null;
    }

    async loadData() {
        if (this.loading) return;
        
        this.loading = true;
        this.showLoading();
        
        try {
            const response = await fetch(this.getApiUrl());
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            this.data = await response.json();
            this.render();
            this.error = null;
        } catch (error) {
            this.error = error.message;
            this.showError();
            console.error(`Widget ${this.constructor.name} error:`, error);
        } finally {
            this.loading = false;
            this.hideLoading();
        }
    }

    showLoading() {
        if (this.element) {
            this.element.classList.add('loading');
            const loader = this.element.querySelector('.loading-spinner');
            if (loader) loader.style.display = 'block';
        }
    }

    hideLoading() {
        if (this.element) {
            this.element.classList.remove('loading');
            const loader = this.element.querySelector('.loading-spinner');
            if (loader) loader.style.display = 'none';
        }
    }

    showError() {
        if (this.element) {
            const errorEl = this.element.querySelector('.widget-error');
            if (errorEl) {
                errorEl.textContent = this.error;
                errorEl.style.display = 'block';
            }
        }
    }

    getApiUrl() {
        throw new Error('getApiUrl must be implemented by subclass');
    }

    render() {
        throw new Error('render must be implemented by subclass');
    }
}

// ===== SPECIFIC WIDGETS =====
class MetricsWidget extends BaseWidget {
    constructor() {
        super('.metrics-widget');
    }

    getApiUrl() {
        return MesChainDashboard.config.apiEndpoints.metrics;
    }

    render() {
        if (!this.data || !this.element) return;

        const metricsHTML = `
            <div class="metrics-grid">
                <div class="metric-item">
                    <div class="metric-icon">
                        <i class="fa fa-shopping-cart"></i>
                    </div>
                    <div class="metric-content">
                        <h3>${MesChainDashboard.formatNumber(this.data.total_orders || 0)}</h3>
                        <p>Toplam Sipariş</p>
                        <span class="metric-change positive">
                            <i class="fa fa-arrow-up"></i>
                            ${this.data.orders_change || 0}%
                        </span>
                    </div>
                </div>
                <div class="metric-item">
                    <div class="metric-icon">
                        <i class="fa fa-box"></i>
                    </div>
                    <div class="metric-content">
                        <h3>${MesChainDashboard.formatNumber(this.data.total_products || 0)}</h3>
                        <p>Aktif Ürün</p>
                        <span class="metric-change positive">
                            <i class="fa fa-arrow-up"></i>
                            ${this.data.products_change || 0}%
                        </span>
                    </div>
                </div>
                <div class="metric-item">
                    <div class="metric-icon">
                        <i class="fa fa-lira-sign"></i>
                    </div>
                    <div class="metric-content">
                        <h3>${MesChainDashboard.formatCurrency(this.data.total_revenue || 0)}</h3>
                        <p>Toplam Ciro</p>
                        <span class="metric-change positive">
                            <i class="fa fa-arrow-up"></i>
                            ${this.data.revenue_change || 0}%
                        </span>
                    </div>
                </div>
            </div>
        `;

        this.element.innerHTML = metricsHTML;
    }
}

class OrdersWidget extends BaseWidget {
    constructor() {
        super('.orders-widget');
    }

    getApiUrl() {
        return MesChainDashboard.config.apiEndpoints.orders;
    }

    render() {
        if (!this.data || !this.element) return;

        const ordersHTML = `
            <div class="widget-header">
                <h3 class="widget-title">Son Siparişler</h3>
                <a href="#" class="btn btn-sm btn-primary">Tümünü Gör</a>
            </div>
            <div class="orders-list">
                ${this.data.orders ? this.data.orders.map(order => `
                    <div class="order-item">
                        <div class="order-info">
                            <strong>#${order.order_id}</strong>
                            <span class="order-marketplace">${order.marketplace}</span>
                        </div>
                        <div class="order-meta">
                            <span class="order-amount">${MesChainDashboard.formatCurrency(order.amount)}</span>
                            <span class="order-status status-${order.status}">${order.status_text}</span>
                        </div>
                    </div>
                `).join('') : '<p>Henüz sipariş bulunmuyor.</p>'}
            </div>
        `;

        this.element.innerHTML = ordersHTML;
    }
}

class ProductsWidget extends BaseWidget {
    constructor() {
        super('.products-widget');
    }

    getApiUrl() {
        return MesChainDashboard.config.apiEndpoints.products;
    }

    render() {
        if (!this.data || !this.element) return;

        const productsHTML = `
            <div class="widget-header">
                <h3 class="widget-title">Ürün Durumu</h3>
                <button class="btn btn-sm btn-secondary" onclick="MesChainDashboard.widgets.products.loadData()">
                    <i class="fa fa-refresh"></i>
                </button>
            </div>
            <div class="products-stats">
                <div class="stat-item">
                    <span class="stat-label">Aktif</span>
                    <span class="stat-value">${this.data.active || 0}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Pasif</span>
                    <span class="stat-value">${this.data.inactive || 0}</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Stok Yok</span>
                    <span class="stat-value">${this.data.out_of_stock || 0}</span>
                </div>
            </div>
            <canvas id="products-chart" width="400" height="200"></canvas>
        `;

        this.element.innerHTML = productsHTML;
        this.renderChart();
    }

    renderChart() {
        const canvas = document.getElementById('products-chart');
        if (!canvas || !this.data) return;

        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Aktif', 'Pasif', 'Stok Yok'],
                datasets: [{
                    data: [this.data.active || 0, this.data.inactive || 0, this.data.out_of_stock || 0],
                    backgroundColor: [
                        'var(--success-green)',
                        'var(--warning-orange)',
                        'var(--error-red)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
}

class AnalyticsWidget extends BaseWidget {
    constructor() {
        super('.analytics-widget');
    }

    getApiUrl() {
        return MesChainDashboard.config.apiEndpoints.analytics;
    }

    render() {
        if (!this.data || !this.element) return;

        const analyticsHTML = `
            <div class="widget-header">
                <h3 class="widget-title">Satış Analizi</h3>
                <select class="form-control-modern" style="width: auto;" onchange="this.widget.changePeriod(this.value)">
                    <option value="7">Son 7 Gün</option>
                    <option value="30">Son 30 Gün</option>
                    <option value="90">Son 3 Ay</option>
                </select>
            </div>
            <canvas id="analytics-chart" width="400" height="300"></canvas>
        `;

        this.element.innerHTML = analyticsHTML;
        this.renderChart();
    }

    renderChart() {
        const canvas = document.getElementById('analytics-chart');
        if (!canvas || !this.data) return;

        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: this.data.labels || [],
                datasets: [{
                    label: 'Satış',
                    data: this.data.sales || [],
                    borderColor: 'var(--primary-blue)',
                    backgroundColor: 'rgba(44, 82, 130, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return MesChainDashboard.formatCurrency(value);
                            }
                        }
                    }
                }
            }
        });
    }

    changePeriod(period) {
        // Implementation for changing analytics period
        console.log('Changing analytics period to:', period);
    }
}

// ===== NOTIFICATION MANAGER =====
class NotificationManager {
    constructor() {
        this.container = this.createContainer();
    }

    createContainer() {
        const container = document.createElement('div');
        container.className = 'notifications-container';
        container.style.cssText = `
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 9999;
            max-width: 400px;
        `;
        document.body.appendChild(container);
        return container;
    }

    show(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type} fade-in`;
        notification.style.cssText = `
            background: var(--white);
            border: 1px solid var(--gray-200);
            border-left: 4px solid var(--${type === 'success' ? 'success-green' : type === 'error' ? 'error-red' : type === 'warning' ? 'warning-orange' : 'info-blue'});
            border-radius: var(--radius-md);
            padding: var(--spacing-md);
            margin-bottom: var(--spacing-sm);
            box-shadow: var(--shadow-lg);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        `;

        const icon = this.getIcon(type);
        notification.innerHTML = `
            <i class="fa ${icon}"></i>
            <span>${message}</span>
            <button class="notification-close" style="margin-left: auto; background: none; border: none; font-size: 1.2rem; cursor: pointer;">×</button>
        `;

        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => this.remove(notification));

        this.container.appendChild(notification);

        if (duration > 0) {
            setTimeout(() => this.remove(notification), duration);
        }
    }

    getIcon(type) {
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        return icons[type] || icons.info;
    }

    remove(notification) {
        if (notification && notification.parentNode) {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }
    }
}

// ===== INITIALIZATION =====
document.addEventListener('DOMContentLoaded', () => {
    MesChainDashboard.init();
});

// ===== GLOBAL EXPORTS =====
window.MesChainDashboard = MesChainDashboard;
