/**
 * N11 Activity Feed Module
 * MesChain-Sync Frontend Module for Real-time Activity Tracking
 * 
 * Features:
 * - Real-time activity monitoring
 * - Event categorization
 * - Time-based filtering
 * - User action tracking
 * - System event logging
 */
class N11ActivityFeed {
    constructor() {
        // Configuration
        this.config = {
            apiEndpoint: '/admin/index.php?route=extension/module/n11',
            userToken: this.extractUserToken(),
            activityTypes: {
                'new_order': { icon: 'shopping-cart', color: '#FF6000' },
                'product_update': { icon: 'edit', color: '#28A745' },
                'stock_low': { icon: 'exclamation-triangle', color: '#FFC107' },
                'price_change': { icon: 'tag', color: '#17A2B8' },
                'customer_message': { icon: 'comment', color: '#6C757D' },
                'return_request': { icon: 'undo', color: '#DC3545' },
                'system_sync': { icon: 'sync', color: '#0D6EFD' }
            },
            maxActivities: 50,
            refreshInterval: 60000, // 1 minute
        };

        // State variables
        this.activities = [];
        this.isLoading = false;
        this.currentFilter = 'all';
        this.lastRefreshTime = null;
        
        // DOM Elements
        this.feedContainer = document.getElementById('n11ActivityFeed');
        this.filterButtons = document.querySelectorAll('.n11-activity-filter');
        this.loadMoreButton = document.getElementById('n11LoadMoreActivities');
        
        console.log('🔔 N11 Activity Feed Module başlatılıyor...');
        this.init();
    }
    
    /**
     * Extract user_token from URL for OpenCart API calls
     */
    extractUserToken() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('user_token') || '';
    }
    
    /**
     * Initialize the activity feed
     */
    async init() {
        try {
            // Set up event listeners
            this.setupEventListeners();
            
            // Initial data load
            await this.loadActivities();
            
            // Start refresh interval
            this.startAutoRefresh();
            
            console.log('✅ N11 Activity Feed başarıyla yüklendi!');
        } catch (error) {
            console.error('❌ N11 Activity Feed hatası:', error);
            this.showError('Activity Feed yüklenirken bir hata oluştu');
        }
    }
    
    /**
     * Setup event listeners for activity feed interactions
     */
    setupEventListeners() {
        // Filter buttons
        this.filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                const filterType = button.getAttribute('data-filter');
                this.filterActivities(filterType);
                
                // Update active button
                this.filterButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
            });
        });
        
        // Load more button
        if (this.loadMoreButton) {
            this.loadMoreButton.addEventListener('click', () => {
                this.loadMoreActivities();
            });
        }
        
        // Global function for dismissing activities
        window.dismissActivity = (activityId) => this.dismissActivity(activityId);
    }
    
    /**
     * Load activities from the API
     */
    async loadActivities(offset = 0, limit = 10) {
        if (this.isLoading) return;
        
        try {
            this.isLoading = true;
            this.showLoading(true);
            
            const response = await fetch(`${this.config.apiEndpoint}&action=getActivities&user_token=${this.config.userToken}&offset=${offset}&limit=${limit}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`API error: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                if (offset === 0) {
                    // First page, replace all activities
                    this.activities = data.activities || [];
                } else {
                    // Append activities
                    this.activities = [...this.activities, ...(data.activities || [])];
                }
                
                this.renderActivities();
                this.lastRefreshTime = new Date();
                
                // Update last refresh time display
                const refreshTimeElement = document.getElementById('n11-last-refresh-time');
                if (refreshTimeElement) {
                    refreshTimeElement.textContent = this.lastRefreshTime.toLocaleTimeString('tr-TR');
                }
                
                return data.activities || [];
            } else {
                throw new Error(data.error || 'API yanıt hatası');
            }
            
        } catch (error) {
            console.error('❌ Aktivite yükleme hatası:', error);
            this.showError(error.message);
            return [];
        } finally {
            this.isLoading = false;
            this.showLoading(false);
        }
    }
    
    /**
     * Render activities to the feed container
     */
    renderActivities() {
        if (!this.feedContainer) return;
        
        // Filter activities if needed
        const activitiesToShow = this.currentFilter === 'all' 
            ? this.activities 
            : this.activities.filter(activity => activity.type === this.currentFilter);
        
        if (activitiesToShow.length === 0) {
            this.feedContainer.innerHTML = `
                <div class="n11-activity-empty">
                    <i class="fas fa-info-circle"></i>
                    <p>Aktivite bulunamadı</p>
                </div>
            `;
            return;
        }
        
        let html = '';
        
        activitiesToShow.forEach(activity => {
            const activityType = this.config.activityTypes[activity.type] || { 
                icon: 'circle', 
                color: '#6C757D' 
            };
            
            const timeAgo = this.formatTimeAgo(new Date(activity.timestamp));
            
            html += `
                <div class="n11-activity-item" data-id="${activity.id}">
                    <div class="n11-activity-icon" style="background-color: ${activityType.color}">
                        <i class="fas fa-${activityType.icon}"></i>
                    </div>
                    <div class="n11-activity-content">
                        <div class="n11-activity-header">
                            <h5>${activity.title}</h5>
                            <span class="n11-activity-time">${timeAgo}</span>
                        </div>
                        <p>${activity.message}</p>
                        ${activity.actionUrl ? `
                        <a href="${activity.actionUrl}" class="n11-activity-action">
                            ${activity.actionText || 'Görüntüle'} <i class="fas fa-chevron-right"></i>
                        </a>
                        ` : ''}
                    </div>
                    <button class="n11-activity-dismiss" onclick="dismissActivity('${activity.id}')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
        });
        
        this.feedContainer.innerHTML = html;
    }
    
    /**
     * Format timestamp to relative time (time ago)
     */
    formatTimeAgo(timestamp) {
        const now = new Date();
        const diff = now - timestamp;
        
        // Less than a minute
        if (diff < 60000) {
            return 'Az önce';
        }
        
        // Less than an hour
        if (diff < 3600000) {
            const minutes = Math.floor(diff / 60000);
            return `${minutes} dakika önce`;
        }
        
        // Less than a day
        if (diff < 86400000) {
            const hours = Math.floor(diff / 3600000);
            return `${hours} saat önce`;
        }
        
        // Less than a week
        if (diff < 604800000) {
            const days = Math.floor(diff / 86400000);
            return `${days} gün önce`;
        }
        
        // Otherwise, return formatted date
        return timestamp.toLocaleDateString('tr-TR');
    }
    
    /**
     * Filter activities by type
     */
    filterActivities(type) {
        this.currentFilter = type;
        this.renderActivities();
    }
    
    /**
     * Load more activities
     */
    async loadMoreActivities() {
        await this.loadActivities(this.activities.length);
    }
    
    /**
     * Dismiss an activity
     */
    async dismissActivity(activityId) {
        try {
            const response = await fetch(`${this.config.apiEndpoint}&action=dismissActivity&user_token=${this.config.userToken}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ activity_id: activityId })
            });
            
            if (!response.ok) {
                throw new Error(`API error: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                // Remove from local array
                this.activities = this.activities.filter(activity => activity.id !== activityId);
                
                // Remove from DOM
                const activityElement = document.querySelector(`.n11-activity-item[data-id="${activityId}"]`);
                if (activityElement) {
                    activityElement.classList.add('n11-activity-dismissed');
                    setTimeout(() => {
                        activityElement.remove();
                    }, 300);
                }
                
                return true;
            } else {
                throw new Error(data.error || 'API yanıt hatası');
            }
            
        } catch (error) {
            console.error('❌ Aktivite silme hatası:', error);
            this.showError(error.message);
            return false;
        }
    }
    
    /**
     * Start auto-refresh timer
     */
    startAutoRefresh() {
        setInterval(() => {
            this.loadActivities(0, 10);
        }, this.config.refreshInterval);
    }
    
    /**
     * Show loading state
     */
    showLoading(isLoading) {
        if (isLoading) {
            const loadingIndicator = document.getElementById('n11-activity-loading');
            if (loadingIndicator) {
                loadingIndicator.style.display = 'block';
            }
        } else {
            const loadingIndicator = document.getElementById('n11-activity-loading');
            if (loadingIndicator) {
                loadingIndicator.style.display = 'none';
            }
        }
    }
    
    /**
     * Show error message
     */
    showError(message) {
        const errorElement = document.getElementById('n11-activity-error');
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
            
            setTimeout(() => {
                errorElement.style.display = 'none';
            }, 5000);
        }
    }
    
    /**
     * Add new activity manually (for testing)
     */
    addTestActivity() {
        const types = Object.keys(this.config.activityTypes);
        const randomType = types[Math.floor(Math.random() * types.length)];
        
        const newActivity = {
            id: `test_${Date.now()}`,
            type: randomType,
            title: `Test ${randomType} Activity`,
            message: 'Bu bir test aktivitesidir. Gerçek veriler API\'den gelecektir.',
            timestamp: new Date(),
            actionUrl: '#',
            actionText: 'Test'
        };
        
        this.activities.unshift(newActivity);
        this.renderActivities();
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Create global instance
    window.n11ActivityFeed = new N11ActivityFeed();
    
    // For testing: Add button to menu
    const testButton = document.getElementById('n11-add-test-activity');
    if (testButton) {
        testButton.addEventListener('click', () => {
            window.n11ActivityFeed.addTestActivity();
        });
    }
});
