/**
 * MesChain-Sync Enterprise - User Experience Analytics
 * Selinay Team - Frontend UI/UX Specialist Task 6 Implementation
 * Advanced UX Metrics Collection & Analysis System
 * Date: June 6, 2025
 */

class SelinayUXAnalytics {
    constructor(config = {}) {
        this.config = {
            apiEndpoint: config.apiEndpoint || '/api/ux-analytics',
            enableHeatmaps: config.enableHeatmaps !== false,
            enableScrollTracking: config.enableScrollTracking !== false,
            enableClickTracking: config.enableClickTracking !== false,
            enableFormAnalytics: config.enableFormAnalytics !== false,
            enablePageTimings: config.enablePageTimings !== false,
            enableUserJourney: config.enableUserJourney !== false,
            enableAccessibilityTracking: config.enableAccessibilityTracking !== false,
            sessionTimeout: config.sessionTimeout || 30 * 60 * 1000, // 30 minutes
            batchSize: config.batchSize || 20,
            flushInterval: config.flushInterval || 10000, // 10 seconds
            sampling: config.sampling || 1.0, // 100% by default
            ...config
        };

        this.sessionId = this.generateSessionId();
        this.userId = this.getUserId();
        this.pageLoadTime = Date.now();
        this.analyticsQueue = [];
        this.userJourney = [];
        this.scrollData = [];
        this.clickHeatmapData = [];
        this.formInteractions = new Map();
        this.pageTimings = {};
        this.accessibilityMetrics = {};
        this.isInitialized = false;

        this.init();
    }

    /**
     * Initialize UX Analytics system
     */
    init() {
        if (this.isInitialized) return;

        try {
            this.setupPageTimings();
            this.setupScrollTracking();
            this.setupClickTracking();
            this.setupFormAnalytics();
            this.setupUserJourneyTracking();
            this.setupAccessibilityTracking();
            this.setupVisibilityTracking();
            this.setupEngagementMetrics();
            this.startPeriodicCollection();
            this.setupBeforeUnloadHandler();

            this.isInitialized = true;
            this.trackEvent('ux_analytics_initialized', { timestamp: Date.now() });
            console.log('✅ Selinay UX Analytics initialized');
        } catch (error) {
            console.error('❌ Failed to initialize UX Analytics:', error);
        }
    }

    /**
     * Setup page timing measurements
     */
    setupPageTimings() {
        if (!this.config.enablePageTimings) return;

        // Track initial page load
        window.addEventListener('load', () => {
            const navigation = performance.getEntriesByType('navigation')[0];
            if (navigation) {
                this.pageTimings = {
                    pageLoadTime: navigation.loadEventEnd - navigation.loadEventStart,
                    domContentLoaded: navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart,
                    timeToInteractive: this.calculateTimeToInteractive(),
                    firstPaint: this.getFirstPaint(),
                    firstContentfulPaint: this.getFirstContentfulPaint(),
                    largestContentfulPaint: this.getLargestContentfulPaint()
                };

                this.trackEvent('page_timings', this.pageTimings);
            }
        });

        // Track route changes (SPA navigation)
        this.originalPushState = history.pushState;
        this.originalReplaceState = history.replaceState;
        
        history.pushState = (...args) => {
            this.trackRouteChange('pushState', args[2]);
            return this.originalPushState.apply(history, args);
        };

        history.replaceState = (...args) => {
            this.trackRouteChange('replaceState', args[2]);
            return this.originalReplaceState.apply(history, args);
        };

        window.addEventListener('popstate', (event) => {
            this.trackRouteChange('popstate', window.location.pathname);
        });
    }

    /**
     * Setup scroll tracking
     */
    setupScrollTracking() {
        if (!this.config.enableScrollTracking) return;

        let scrollTimeout;
        let maxScrollDepth = 0;
        let scrollStartTime = Date.now();

        window.addEventListener('scroll', () => {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const documentHeight = document.documentElement.scrollHeight - window.innerHeight;
            const scrollPercentage = (scrollTop / documentHeight) * 100;

            maxScrollDepth = Math.max(maxScrollDepth, scrollPercentage);

            // Debounce scroll events
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                this.scrollData.push({
                    timestamp: Date.now(),
                    scrollTop: scrollTop,
                    scrollPercentage: scrollPercentage,
                    viewportHeight: window.innerHeight,
                    documentHeight: document.documentElement.scrollHeight
                });

                // Track scroll milestones
                if (scrollPercentage >= 25 && !this.scrollMilestones?.quarter) {
                    this.trackEvent('scroll_milestone', { milestone: '25%', time: Date.now() - scrollStartTime });
                    this.scrollMilestones = { ...this.scrollMilestones, quarter: true };
                }
                if (scrollPercentage >= 50 && !this.scrollMilestones?.half) {
                    this.trackEvent('scroll_milestone', { milestone: '50%', time: Date.now() - scrollStartTime });
                    this.scrollMilestones = { ...this.scrollMilestones, half: true };
                }
                if (scrollPercentage >= 75 && !this.scrollMilestones?.threeQuarter) {
                    this.trackEvent('scroll_milestone', { milestone: '75%', time: Date.now() - scrollStartTime });
                    this.scrollMilestones = { ...this.scrollMilestones, threeQuarter: true };
                }
                if (scrollPercentage >= 100 && !this.scrollMilestones?.complete) {
                    this.trackEvent('scroll_milestone', { milestone: '100%', time: Date.now() - scrollStartTime });
                    this.scrollMilestones = { ...this.scrollMilestones, complete: true };
                }
            }, 100);
        });

        // Track when user stops scrolling
        window.addEventListener('beforeunload', () => {
            this.trackEvent('scroll_summary', {
                maxScrollDepth: maxScrollDepth,
                totalScrollTime: Date.now() - scrollStartTime,
                scrollEvents: this.scrollData.length
            });
        });
    }

    /**
     * Setup click tracking and heatmap data
     */
    setupClickTracking() {
        if (!this.config.enableClickTracking) return;

        document.addEventListener('click', (event) => {
            const rect = event.target.getBoundingClientRect();
            const clickData = {
                timestamp: Date.now(),
                x: event.clientX,
                y: event.clientY,
                relativeX: (event.clientX / window.innerWidth) * 100,
                relativeY: (event.clientY / window.innerHeight) * 100,
                element: event.target.tagName.toLowerCase(),
                elementId: event.target.id,
                elementClass: event.target.className,
                elementText: event.target.textContent?.slice(0, 100),
                url: window.location.href,
                viewport: {
                    width: window.innerWidth,
                    height: window.innerHeight
                }
            };

            this.clickHeatmapData.push(clickData);

            // Track specific UI elements
            if (event.target.matches('button, a, input[type="submit"], .clickable')) {
                this.trackEvent('ui_interaction', {
                    type: 'click',
                    element: event.target.tagName.toLowerCase(),
                    id: event.target.id,
                    text: event.target.textContent?.slice(0, 50),
                    timestamp: Date.now()
                });
            }

            // Track CTA clicks
            if (event.target.matches('.cta, .btn-primary, .call-to-action')) {
                this.trackEvent('cta_click', {
                    element: event.target.textContent?.slice(0, 50),
                    position: { x: clickData.relativeX, y: clickData.relativeY },
                    timestamp: Date.now()
                });
            }
        });

        // Track double clicks
        document.addEventListener('dblclick', (event) => {
            this.trackEvent('double_click', {
                element: event.target.tagName.toLowerCase(),
                id: event.target.id,
                timestamp: Date.now()
            });
        });

        // Track right clicks
        document.addEventListener('contextmenu', (event) => {
            this.trackEvent('right_click', {
                element: event.target.tagName.toLowerCase(),
                id: event.target.id,
                timestamp: Date.now()
            });
        });
    }

    /**
     * Setup form analytics
     */
    setupFormAnalytics() {
        if (!this.config.enableFormAnalytics) return;

        // Track form interactions
        document.addEventListener('focusin', (event) => {
            if (event.target.matches('input, textarea, select')) {
                const formId = event.target.closest('form')?.id || 'unknown';
                const fieldName = event.target.name || event.target.id || 'unnamed';
                
                if (!this.formInteractions.has(formId)) {
                    this.formInteractions.set(formId, {
                        startTime: Date.now(),
                        fields: new Map(),
                        abandonments: 0,
                        submissions: 0
                    });
                }

                this.formInteractions.get(formId).fields.set(fieldName, {
                    focusTime: Date.now(),
                    focusCount: (this.formInteractions.get(formId).fields.get(fieldName)?.focusCount || 0) + 1,
                    value: ''
                });
            }
        });

        // Track field blur events
        document.addEventListener('focusout', (event) => {
            if (event.target.matches('input, textarea, select')) {
                const formId = event.target.closest('form')?.id || 'unknown';
                const fieldName = event.target.name || event.target.id || 'unnamed';
                
                const formData = this.formInteractions.get(formId);
                if (formData && formData.fields.has(fieldName)) {
                    const fieldData = formData.fields.get(fieldName);
                    fieldData.blurTime = Date.now();
                    fieldData.focusDuration = fieldData.blurTime - fieldData.focusTime;
                    fieldData.value = event.target.value;
                    fieldData.filled = Boolean(event.target.value);
                }
            }
        });

        // Track form submissions
        document.addEventListener('submit', (event) => {
            const formId = event.target.id || 'unknown';
            const formData = this.formInteractions.get(formId);
            
            if (formData) {
                formData.submissions++;
                formData.completionTime = Date.now() - formData.startTime;
            }

            this.trackEvent('form_submission', {
                formId: formId,
                completionTime: formData?.completionTime,
                fieldCount: formData?.fields.size,
                timestamp: Date.now()
            });
        });

        // Track form abandonments
        window.addEventListener('beforeunload', () => {
            this.formInteractions.forEach((formData, formId) => {
                if (formData.submissions === 0 && formData.fields.size > 0) {
                    this.trackEvent('form_abandonment', {
                        formId: formId,
                        timeSpent: Date.now() - formData.startTime,
                        fieldsInteracted: formData.fields.size,
                        fieldsCompleted: Array.from(formData.fields.values()).filter(f => f.filled).length,
                        timestamp: Date.now()
                    });
                }
            });
        });
    }

    /**
     * Setup user journey tracking
     */
    setupUserJourneyTracking() {
        if (!this.config.enableUserJourney) return;

        // Track page views
        this.addJourneyStep({
            type: 'page_view',
            url: window.location.href,
            title: document.title,
            timestamp: Date.now()
        });

        // Track button clicks
        document.addEventListener('click', (event) => {
            if (event.target.matches('button, a, input[type="submit"]')) {
                this.addJourneyStep({
                    type: 'interaction',
                    action: 'click',
                    element: event.target.tagName.toLowerCase(),
                    text: event.target.textContent?.slice(0, 50),
                    url: window.location.href,
                    timestamp: Date.now()
                });
            }
        });

        // Track search actions
        document.addEventListener('submit', (event) => {
            if (event.target.matches('form[role="search"], .search-form')) {
                const searchInput = event.target.querySelector('input[type="search"], input[name*="search"]');
                this.addJourneyStep({
                    type: 'search',
                    query: searchInput?.value,
                    url: window.location.href,
                    timestamp: Date.now()
                });
            }
        });
    }

    /**
     * Setup accessibility tracking
     */
    setupAccessibilityTracking() {
        if (!this.config.enableAccessibilityTracking) return;

        // Track keyboard navigation
        let keyboardNavigation = false;
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Tab') {
                keyboardNavigation = true;
                this.accessibilityMetrics.keyboardNavigation = true;
            }
        });

        // Track focus indicators
        document.addEventListener('focusin', (event) => {
            if (keyboardNavigation) {
                const focusStyles = window.getComputedStyle(event.target);
                this.accessibilityMetrics.focusIndicatorUsage = {
                    outline: focusStyles.outline !== 'none',
                    boxShadow: focusStyles.boxShadow !== 'none',
                    element: event.target.tagName.toLowerCase()
                };
            }
        });

        // Track screen reader compatibility markers
        this.accessibilityMetrics.ariaLabels = document.querySelectorAll('[aria-label]').length;
        this.accessibilityMetrics.ariaDescribedBy = document.querySelectorAll('[aria-describedby]').length;
        this.accessibilityMetrics.altTexts = document.querySelectorAll('img[alt]').length;
        this.accessibilityMetrics.headingStructure = this.analyzeHeadingStructure();

        // Track color contrast issues (basic detection)
        this.checkColorContrast();
    }

    /**
     * Setup page visibility tracking
     */
    setupVisibilityTracking() {
        let visibilityStartTime = Date.now();
        let totalVisibleTime = 0;

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                totalVisibleTime += Date.now() - visibilityStartTime;
                this.trackEvent('page_hidden', { 
                    visibleTime: Date.now() - visibilityStartTime,
                    timestamp: Date.now()
                });
            } else {
                visibilityStartTime = Date.now();
                this.trackEvent('page_visible', { timestamp: Date.now() });
            }
        });

        // Track total visible time on page unload
        window.addEventListener('beforeunload', () => {
            if (!document.hidden) {
                totalVisibleTime += Date.now() - visibilityStartTime;
            }
            this.trackEvent('total_visible_time', { 
                totalVisibleTime: totalVisibleTime,
                timestamp: Date.now()
            });
        });
    }

    /**
     * Setup engagement metrics
     */
    setupEngagementMetrics() {
        let mouseMovements = 0;
        let keystrokes = 0;
        let idleTime = 0;
        let lastActivity = Date.now();

        // Track mouse movements
        document.addEventListener('mousemove', () => {
            mouseMovements++;
            lastActivity = Date.now();
        });

        // Track keystrokes
        document.addEventListener('keydown', () => {
            keystrokes++;
            lastActivity = Date.now();
        });

        // Track idle time
        setInterval(() => {
            const now = Date.now();
            if (now - lastActivity > 30000) { // 30 seconds of inactivity
                idleTime += 1000;
            }
        }, 1000);

        // Send engagement metrics periodically
        setInterval(() => {
            this.trackEvent('engagement_metrics', {
                mouseMovements: mouseMovements,
                keystrokes: keystrokes,
                idleTime: idleTime,
                timestamp: Date.now()
            });
            
            // Reset counters
            mouseMovements = 0;
            keystrokes = 0;
            idleTime = 0;
        }, 60000); // Every minute
    }

    /**
     * Track route changes
     */
    trackRouteChange(method, url) {
        this.trackEvent('route_change', {
            method: method,
            from: window.location.href,
            to: url,
            timestamp: Date.now()
        });

        this.addJourneyStep({
            type: 'navigation',
            from: window.location.href,
            to: url,
            method: method,
            timestamp: Date.now()
        });
    }

    /**
     * Add step to user journey
     */
    addJourneyStep(step) {
        this.userJourney.push(step);
        
        // Keep only recent 100 steps
        if (this.userJourney.length > 100) {
            this.userJourney.shift();
        }
    }

    /**
     * Track custom event
     */
    trackEvent(eventName, data = {}) {
        // Apply sampling
        if (Math.random() > this.config.sampling) {
            return;
        }

        const event = {
            id: this.generateEventId(),
            name: eventName,
            data: data,
            sessionId: this.sessionId,
            userId: this.userId,
            url: window.location.href,
            referrer: document.referrer,
            timestamp: Date.now(),
            userAgent: navigator.userAgent,
            viewport: {
                width: window.innerWidth,
                height: window.innerHeight
            },
            device: this.getDeviceInfo()
        };

        this.analyticsQueue.push(event);

        // Flush if batch size reached
        if (this.analyticsQueue.length >= this.config.batchSize) {
            this.flushAnalytics();
        }
    }

    /**
     * Helper methods
     */
    calculateTimeToInteractive() {
        // Simplified TTI calculation
        const navigation = performance.getEntriesByType('navigation')[0];
        return navigation ? navigation.domContentLoadedEventEnd - navigation.navigationStart : 0;
    }

    getFirstPaint() {
        const paintEntries = performance.getEntriesByType('paint');
        const firstPaint = paintEntries.find(entry => entry.name === 'first-paint');
        return firstPaint ? firstPaint.startTime : 0;
    }

    getFirstContentfulPaint() {
        const paintEntries = performance.getEntriesByType('paint');
        const fcp = paintEntries.find(entry => entry.name === 'first-contentful-paint');
        return fcp ? fcp.startTime : 0;
    }

    getLargestContentfulPaint() {
        return new Promise((resolve) => {
            if ('PerformanceObserver' in window) {
                const observer = new PerformanceObserver((list) => {
                    const entries = list.getEntries();
                    const lastEntry = entries[entries.length - 1];
                    resolve(lastEntry.startTime);
                });
                observer.observe({ entryTypes: ['largest-contentful-paint'] });
            } else {
                resolve(0);
            }
        });
    }

    analyzeHeadingStructure() {
        const headings = document.querySelectorAll('h1, h2, h3, h4, h5, h6');
        const structure = {};
        headings.forEach(h => {
            const level = h.tagName.toLowerCase();
            structure[level] = (structure[level] || 0) + 1;
        });
        return structure;
    }

    checkColorContrast() {
        // Basic color contrast check
        const elements = document.querySelectorAll('*');
        let contrastIssues = 0;
        
        elements.forEach(el => {
            const styles = window.getComputedStyle(el);
            const bgColor = styles.backgroundColor;
            const textColor = styles.color;
            
            // Simple check - would need more sophisticated contrast calculation
            if (bgColor !== 'rgba(0, 0, 0, 0)' && textColor !== 'rgba(0, 0, 0, 0)') {
                // Placeholder for actual contrast calculation
                // This would typically use color contrast calculation libraries
            }
        });
        
        this.accessibilityMetrics.contrastIssues = contrastIssues;
    }

    getDeviceInfo() {
        return {
            type: /Mobi|Android/i.test(navigator.userAgent) ? 'mobile' : 'desktop',
            platform: navigator.platform,
            language: navigator.language,
            cookieEnabled: navigator.cookieEnabled,
            onLine: navigator.onLine
        };
    }

    generateEventId() {
        return 'evt_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    generateSessionId() {
        return 'sess_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    getUserId() {
        return localStorage.getItem('userId') || 'anonymous_' + Math.random().toString(36).substr(2, 9);
    }

    /**
     * Start periodic data collection
     */
    startPeriodicCollection() {
        setInterval(() => {
            this.flushAnalytics();
        }, this.config.flushInterval);
    }

    /**
     * Setup beforeunload handler
     */
    setupBeforeUnloadHandler() {
        window.addEventListener('beforeunload', () => {
            this.flushAnalytics(true);
            this.trackEvent('session_end', {
                sessionDuration: Date.now() - this.pageLoadTime,
                pageViews: this.userJourney.filter(step => step.type === 'page_view').length,
                interactions: this.userJourney.filter(step => step.type === 'interaction').length,
                scrollData: this.scrollData.length,
                clickData: this.clickHeatmapData.length
            });
        });
    }

    /**
     * Flush analytics data to server
     */
    async flushAnalytics(force = false) {
        if (this.analyticsQueue.length === 0) return;

        const dataToSend = [...this.analyticsQueue];
        this.analyticsQueue = [];

        try {
            const response = await fetch(this.config.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    events: dataToSend,
                    scrollData: this.scrollData,
                    clickHeatmapData: this.clickHeatmapData,
                    userJourney: this.userJourney,
                    formInteractions: Object.fromEntries(this.formInteractions),
                    accessibilityMetrics: this.accessibilityMetrics,
                    timestamp: Date.now(),
                    source: 'selinay-ux-analytics'
                })
            });

            if (response.ok) {
                console.log(`📊 Sent ${dataToSend.length} UX analytics events`);
                // Clear sent data
                this.scrollData = [];
                this.clickHeatmapData = [];
            } else {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
        } catch (error) {
            console.error('❌ Failed to send UX analytics:', error);
            // Re-add to queue if not forcing
            if (!force) {
                this.analyticsQueue.unshift(...dataToSend);
            }
        }
    }

    /**
     * Get analytics summary
     */
    getAnalyticsSummary() {
        return {
            sessionId: this.sessionId,
            userId: this.userId,
            sessionDuration: Date.now() - this.pageLoadTime,
            eventsTracked: this.analyticsQueue.length,
            userJourneySteps: this.userJourney.length,
            scrollEvents: this.scrollData.length,
            clickEvents: this.clickHeatmapData.length,
            formInteractions: this.formInteractions.size,
            accessibilityMetrics: this.accessibilityMetrics
        };
    }

    /**
     * Destroy analytics tracker
     */
    destroy() {
        this.flushAnalytics(true);
        console.log('🧹 Selinay UX Analytics destroyed');
    }
}

// Initialize UX Analytics
const selinayUXAnalytics = new SelinayUXAnalytics({
    apiEndpoint: '/api/selinay/ux-analytics',
    enableHeatmaps: true,
    enableScrollTracking: true,
    enableClickTracking: true,
    enableFormAnalytics: true,
    enablePageTimings: true,
    enableUserJourney: true,
    enableAccessibilityTracking: true,
    sampling: 1.0 // 100% sampling for comprehensive UX insights
});

// Export for global access
window.SelinayUXAnalytics = selinayUXAnalytics;

// Expose helper functions
window.trackUXEvent = selinayUXAnalytics.trackEvent.bind(selinayUXAnalytics);

console.log('📈 Selinay UX Analytics System loaded - Task 6 Implementation');
