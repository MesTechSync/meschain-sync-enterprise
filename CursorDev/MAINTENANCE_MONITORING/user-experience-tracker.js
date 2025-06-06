/**
 * User Experience Tracker
 * Advanced UX monitoring and optimization system
 * Selinay Team - Task 7 Implementation
 * June 5, 2025
 */

class UserExperienceTracker {
    constructor() {
        this.config = {
            trackingInterval: 5000, // 5 seconds
            sessionTimeout: 30 * 60 * 1000, // 30 minutes
            interactionThreshold: 100, // ms for meaningful interactions
            scrollThreshold: 25, // % of page height
            engagementMetrics: {
                timeOnPage: true,
                scrollDepth: true,
                clickTracking: true,
                formInteractions: true,
                errorTracking: true,
                performanceImpact: true
            },
            heatmapSampling: 0.1, // 10% of users
            accessibilityMonitoring: true,
            turkishLocalization: true
        };
        
        this.sessionData = {
            sessionId: this.generateSessionId(),
            startTime: Date.now(),
            userId: this.getUserId(),
            userAgent: navigator.userAgent,
            viewport: this.getViewportSize(),
            language: navigator.language
        };
        
        this.interactions = [];
        this.scrollData = [];
        this.performanceMetrics = new Map();
        this.errors = [];
        this.heatmapData = [];
        this.accessibilityIssues = [];
        this.satisfactionScore = 0;
        
        this.isTracking = false;
        
        this.initializeUXTracking();
    }

    /**
     * Initialize User Experience Tracking
     */
    async initializeUXTracking() {
        try {
            console.log('👥 Initializing User Experience Tracker...');
            
            await this.setupInteractionTracking();
            await this.setupScrollTracking();
            await this.setupFormTracking();
            await this.setupErrorTracking();
            await this.setupPerformanceTracking();
            await this.setupAccessibilityTracking();
            await this.setupHeatmapTracking();
            
            this.startUXMonitoring();
            
            console.log('✅ User Experience Tracker initialized successfully');
            this.logUXEvent('TRACKER_INITIALIZED', 'UX tracking system started');
            
        } catch (error) {
            console.error('❌ Failed to initialize UX tracker:', error);
            this.handleUXError('INITIALIZATION_FAILED', error);
        }
    }

    /**
     * Setup Interaction Tracking
     */
    async setupInteractionTracking() {
        try {
            // Add environment check for document
            if (typeof document === 'undefined') {
                console.warn('⚠️ document not available. Skipping interaction tracking.');
                return;
            }

            // Click tracking
            document.addEventListener('click', (event) => {
                this.trackInteraction('click', {
                    element: this.getElementSelector(event.target),
                    tagName: event.target.tagName,
                    className: event.target.className,
                    coordinates: {
                        x: event.clientX,
                        y: event.clientY
                    },
                    timestamp: Date.now(),
                    url: window.location.href
                });
            }, { passive: true });

            // Keyboard interactions
            document.addEventListener('keydown', (event) => {
                if (this.isImportantKey(event.key)) {
                    this.trackInteraction('keydown', {
                        key: event.key,
                        element: this.getElementSelector(event.target),
                        timestamp: Date.now()
                    });
                }
            }, { passive: true });

            // Mouse movements (sampled)
            let mouseMoveThrottle = null;
            document.addEventListener('mousemove', (event) => {
                if (mouseMoveThrottle) return;
                
                mouseMoveThrottle = setTimeout(() => {
                    if (this.shouldSampleHeatmap()) {
                        this.trackHeatmapData('mousemove', {
                            x: event.clientX,
                            y: event.clientY,
                            timestamp: Date.now()
                        });
                    }
                    mouseMoveThrottle = null;
                }, 200);
            }, { passive: true });

            // Touch interactions for mobile
            document.addEventListener('touchstart', (event) => {
                this.trackInteraction('touch', {
                    element: this.getElementSelector(event.target),
                    touches: event.touches.length,
                    coordinates: event.touches[0] ? {
                        x: event.touches[0].clientX,
                        y: event.touches[0].clientY
                    } : null,
                    timestamp: Date.now()
                });
            }, { passive: true });

            console.log('✅ Interaction tracking setup complete');
            
        } catch (error) {
            console.error('❌ Interaction tracking setup failed:', error);
            throw error;
        }
    }

    /**
     * Setup Scroll Tracking
     */
    async setupScrollTracking() {
        try {
            // Add environment check for window
            if (typeof window === 'undefined') {
                console.warn('⚠️ window not available. Skipping scroll tracking.');
                return;
            }

            let scrollThrottle = null;
            let maxScrollDepth = 0;
            
            window.addEventListener('scroll', () => {
                if (scrollThrottle) return;
                
                scrollThrottle = setTimeout(() => {
                    const scrollDepth = this.calculateScrollDepth();
                    
                    if (scrollDepth > maxScrollDepth) {
                        maxScrollDepth = scrollDepth;
                        
                        this.trackScrollData({
                            depth: scrollDepth,
                            maxDepth: maxScrollDepth,
                            timestamp: Date.now(),
                            url: window.location.href,
                            viewportHeight: window.innerHeight,
                            documentHeight: document.documentElement.scrollHeight
                        });
                        
                        // Track significant scroll milestones
                        if (scrollDepth >= 25 && !this.hasScrollMilestone('25%')) {
                            this.trackInteraction('scroll_milestone', { milestone: '25%', timestamp: Date.now() });
                        }
                        if (scrollDepth >= 50 && !this.hasScrollMilestone('50%')) {
                            this.trackInteraction('scroll_milestone', { milestone: '50%', timestamp: Date.now() });
                        }
                        if (scrollDepth >= 75 && !this.hasScrollMilestone('75%')) {
                            this.trackInteraction('scroll_milestone', { milestone: '75%', timestamp: Date.now() });
                        }
                        if (scrollDepth >= 100 && !this.hasScrollMilestone('100%')) {
                            this.trackInteraction('scroll_milestone', { milestone: '100%', timestamp: Date.now() });
                        }
                    }
                    
                    scrollThrottle = null;
                }, 100);
            }, { passive: true });

            console.log('✅ Scroll tracking setup complete');
            
        } catch (error) {
            console.error('❌ Scroll tracking setup failed:', error);
            throw error;
        }
    }

    /**
     * Setup Form Tracking
     */
    async setupFormTracking() {
        try {
            // Add environment check for document
            if (typeof document === 'undefined') {
                console.warn('⚠️ document not available. Skipping form tracking.');
                return;
            }

            // Form field interactions
            document.addEventListener('focus', (event) => {
                if (this.isFormElement(event.target)) {
                    this.trackFormInteraction('focus', {
                        element: this.getElementSelector(event.target),
                        formId: this.getFormId(event.target),
                        fieldType: event.target.type || event.target.tagName,
                        timestamp: Date.now()
                    });
                }
            }, { capture: true, passive: true });

            document.addEventListener('blur', (event) => {
                if (this.isFormElement(event.target)) {
                    this.trackFormInteraction('blur', {
                        element: this.getElementSelector(event.target),
                        formId: this.getFormId(event.target),
                        fieldType: event.target.type || event.target.tagName,
                        hasValue: !!event.target.value,
                        timestamp: Date.now()
                    });
                }
            }, { capture: true, passive: true });

            // Form submission tracking
            document.addEventListener('submit', (event) => {
                this.trackFormInteraction('submit', {
                    formId: event.target.id || 'unnamed',
                    action: event.target.action,
                    method: event.target.method,
                    fieldCount: event.target.elements.length,
                    timestamp: Date.now()
                });
            }, { passive: true });

            // Form validation errors
            document.addEventListener('invalid', (event) => {
                this.trackFormError({
                    element: this.getElementSelector(event.target),
                    validationMessage: event.target.validationMessage,
                    validity: event.target.validity,
                    timestamp: Date.now()
                });
            }, { capture: true, passive: true });

            console.log('✅ Form tracking setup complete');
            
        } catch (error) {
            console.error('❌ Form tracking setup failed:', error);
            throw error;
        }
    }

    /**
     * Setup Error Tracking
     */
    async setupErrorTracking() {
        try {
            // Add environment check for window
            if (typeof window === 'undefined') {
                console.warn('⚠️ window not available. Skipping error tracking.');
                return;
            }

            // JavaScript errors
            window.addEventListener('error', (event) => {
                this.trackError('javascript', {
                    message: event.message,
                    filename: event.filename,
                    lineno: event.lineno,
                    colno: event.colno,
                    stack: event.error?.stack,
                    timestamp: Date.now(),
                    url: window.location.href,
                    userAgent: navigator.userAgent
                });
            });

            // Unhandled promise rejections
            window.addEventListener('unhandledrejection', (event) => {
                this.trackError('promise_rejection', {
                    reason: event.reason,
                    promise: event.promise,
                    timestamp: Date.now(),
                    url: window.location.href
                });
            });

            // Resource loading errors
            document.addEventListener('error', (event) => {
                if (event.target !== window) {
                    this.trackError('resource', {
                        elementType: event.target.tagName,
                        source: event.target.src || event.target.href,
                        timestamp: Date.now(),
                        url: window.location.href
                    });
                }
            }, { capture: true });

            // Network errors
            if ('navigator' in window && 'onLine' in navigator) {
                window.addEventListener('offline', () => {
                    this.trackError('network', {
                        type: 'offline',
                        timestamp: Date.now()
                    });
                });

                window.addEventListener('online', () => {
                    this.trackError('network', {
                        type: 'back_online',
                        timestamp: Date.now()
                    });
                });
            }

            console.log('✅ Error tracking setup complete');
            
        } catch (error) {
            console.error('❌ Error tracking setup failed:', error);
            throw error;
        }
    }

    /**
     * Setup Performance Tracking
     */
    async setupPerformanceTracking() {
        try {
            // Add environment check for window
            if (typeof window === 'undefined') {
                console.warn('⚠️ window not available. Skipping performance tracking.');
                return;
            }

            // Page load performance
            window.addEventListener('load', () => {
                setTimeout(() => {
                    const perfData = this.collectPerformanceData();
                    this.trackPerformanceMetric('page_load', perfData);
                }, 100);
            });

            // User timing API
            if ('performance' in window && 'mark' in performance) {
                // Monitor custom performance marks
                const observer = new PerformanceObserver((list) => {
                    list.getEntries().forEach(entry => {
                        if (entry.entryType === 'mark' || entry.entryType === 'measure') {
                            this.trackPerformanceMetric('user_timing', {
                                name: entry.name,
                                type: entry.entryType,
                                startTime: entry.startTime,
                                duration: entry.duration,
                                timestamp: Date.now()
                            });
                        }
                    });
                });

                try {
                    observer.observe({ entryTypes: ['mark', 'measure'] });
                } catch (e) {
                    console.warn('Performance observer not fully supported');
                }
            }

            // Frame rate monitoring
            this.startFrameRateMonitoring();

            console.log('✅ Performance tracking setup complete');
            
        } catch (error) {
            console.error('❌ Performance tracking setup failed:', error);
            throw error;
        }
    }

    /**
     * Setup Accessibility Tracking
     */
    async setupAccessibilityTracking() {
        try {
            if (!this.config.accessibilityMonitoring) return;

            // Add environment check for document
            if (typeof document === 'undefined') {
                console.warn('⚠️ document not available. Skipping accessibility tracking.');
                return;
            }

            // Keyboard navigation monitoring
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Tab') {
                    this.trackAccessibilityEvent('keyboard_navigation', {
                        direction: event.shiftKey ? 'backward' : 'forward',
                        element: this.getElementSelector(event.target),
                        timestamp: Date.now()
                    });
                }
            }, { passive: true });

            // Focus tracking for accessibility
            document.addEventListener('focusin', (event) => {
                this.trackAccessibilityEvent('focus_change', {
                    element: this.getElementSelector(event.target),
                    hasVisibleFocus: this.hasVisibleFocus(event.target),
                    timestamp: Date.now()
                });
            }, { passive: true });

            // Screen reader detection (basic)
            this.detectScreenReader();

            // Color contrast monitoring
            this.monitorColorContrast();

            console.log('✅ Accessibility tracking setup complete');
            
        } catch (error) {
            console.error('❌ Accessibility tracking setup failed:', error);
            throw error;
        }
    }

    /**
     * Setup Heatmap Tracking
     */
    async setupHeatmapTracking() {
        try {
            if (Math.random() > this.config.heatmapSampling) {
                console.log('📊 User not selected for heatmap sampling');
                return;
            }

            // Add environment check for document
            if (typeof document === 'undefined') {
                console.warn('⚠️ document not available. Skipping heatmap tracking.');
                return;
            }

            // Click heatmap
            document.addEventListener('click', (event) => {
                this.trackHeatmapData('click', {
                    x: event.clientX,
                    y: event.clientY,
                    element: this.getElementSelector(event.target),
                    timestamp: Date.now(),
                    viewport: this.getViewportSize()
                });
            }, { passive: true });

            console.log('✅ Heatmap tracking setup complete');
            
        } catch (error) {
            console.error('❌ Heatmap tracking setup failed:', error);
            throw error;
        }
    }

    /**
     * Start UX Monitoring
     */
    startUXMonitoring() {
        if (this.isTracking) return;
        
        this.isTracking = true;
        
        // Main UX monitoring loop
        this.monitoringInterval = setInterval(() => {
            this.collectUXMetrics();
            this.calculateSatisfactionScore();
            this.analyzeUserBehavior();
            this.updateUXDashboard();
        }, this.config.trackingInterval);

        // Session timeout check
        this.timeoutInterval = setInterval(() => {
            this.checkSessionTimeout();
        }, 60000); // Check every minute

        // Data cleanup
        setInterval(() => {
            this.cleanupTrackingData();
        }, 10 * 60 * 1000); // Cleanup every 10 minutes

        console.log('🔄 UX monitoring started');
    }

    /**
     * Track User Interaction
     */
    trackInteraction(type, data) {
        const interaction = {
            id: this.generateInteractionId(),
            type: type,
            data: data,
            sessionId: this.sessionData.sessionId,
            timestamp: Date.now(),
            url: typeof window !== 'undefined' ? window.location.href : 'unknown'
        };

        this.interactions.push(interaction);

        // Store in localStorage for persistence
        this.storeInteractionData(interaction);

        // Dispatch event for real-time processing
        if (typeof window !== 'undefined' && typeof CustomEvent !== 'undefined') {
            window.dispatchEvent(new CustomEvent('userInteraction', {
                detail: interaction
            }));
        }
    }

    /**
     * Track Scroll Data
     */
    trackScrollData(data) {
        this.scrollData.push({
            ...data,
            sessionId: this.sessionData.sessionId
        });

        // Update session scroll metrics
        this.updateScrollMetrics(data);
    }

    /**
     * Track Form Interaction
     */
    trackFormInteraction(type, data) {
        this.trackInteraction(`form_${type}`, data);
        
        // Additional form-specific analysis
        this.analyzeFormBehavior(type, data);
    }

    /**
     * Track Form Error
     */
    trackFormError(data) {
        this.trackError('form_validation', data);
        
        // Impact user satisfaction score
        this.satisfactionScore -= 5;
    }

    /**
     * Track Error
     */
    trackError(type, data) {
        const error = {
            id: this.generateErrorId(),
            type: type,
            data: data,
            sessionId: this.sessionData.sessionId,
            timestamp: Date.now(),
            impact: this.calculateErrorImpact(type)
        };

        this.errors.push(error);

        // Store error data
        this.storeErrorData(error);

        // Update satisfaction score based on error severity
        this.satisfactionScore -= error.impact;

        console.warn('🚨 UX Error tracked:', error);
    }

    /**
     * Track Performance Metric
     */
    trackPerformanceMetric(type, data) {
        const metric = {
            type: type,
            data: data,
            timestamp: Date.now(),
            sessionId: this.sessionData.sessionId
        };

        this.performanceMetrics.set(`${type}_${Date.now()}`, metric);

        // Analyze performance impact on UX
        this.analyzePerformanceImpact(metric);
    }

    /**
     * Track Accessibility Event
     */
    trackAccessibilityEvent(type, data) {
        this.trackInteraction(`a11y_${type}`, data);
        
        // Analyze accessibility compliance
        this.analyzeAccessibilityCompliance(type, data);
    }

    /**
     * Track Heatmap Data
     */
    trackHeatmapData(type, data) {
        if (!this.shouldSampleHeatmap()) return;

        this.heatmapData.push({
            type: type,
            data: data,
            sessionId: this.sessionData.sessionId,
            timestamp: Date.now()
        });
    }

    /**
     * Calculate User Satisfaction Score
     */
    calculateSatisfactionScore() {
        let score = 100; // Start with perfect score

        // Time on page factor
        const sessionDuration = Date.now() - this.sessionData.startTime;
        if (sessionDuration > 5 * 60 * 1000) { // More than 5 minutes
            score += 10; // Bonus for engagement
        }

        // Interaction quality
        const recentInteractions = this.getRecentInteractions(60000); // Last minute
        if (recentInteractions.length > 5) {
            score += 5; // Active user bonus
        }

        // Error penalty
        const recentErrors = this.getRecentErrors(300000); // Last 5 minutes
        score -= recentErrors.length * 10;

        // Performance impact
        const performanceScore = this.getPerformanceScore();
        score += (performanceScore - 50) / 10; // Adjust based on performance

        // Accessibility compliance
        if (this.accessibilityIssues.length === 0) {
            score += 5;
        }

        // Scroll engagement
        const maxScrollDepth = Math.max(...this.scrollData.map(s => s.depth), 0);
        if (maxScrollDepth > 50) {
            score += 5;
        }

        // Form completion success
        const formSuccessRate = this.calculateFormSuccessRate();
        score += formSuccessRate * 0.2;

        // Clamp score between 0 and 100
        this.satisfactionScore = Math.max(0, Math.min(100, score));
        
        return this.satisfactionScore;
    }

    /**
     * Analyze User Behavior Patterns
     */
    analyzeUserBehavior() {
        const analysis = {
            engagementLevel: this.calculateEngagementLevel(),
            navigationPattern: this.analyzeNavigationPattern(),
            interactionQuality: this.analyzeInteractionQuality(),
            contentConsumption: this.analyzeContentConsumption(),
            potentialIssues: this.identifyPotentialIssues()
        };

        // Store analysis results
        this.sessionData.behaviorAnalysis = analysis;

        return analysis;
    }

    /**
     * Generate UX Insights
     */
    generateUXInsights() {
        const insights = [];

        // Engagement insights
        const engagementLevel = this.calculateEngagementLevel();
        if (engagementLevel > 0.8) {
            insights.push({
                type: 'positive',
                title: 'High User Engagement',
                description: `User showing ${(engagementLevel * 100).toFixed(1)}% engagement level`,
                impact: 'positive',
                recommendation: 'Continue providing engaging content'
            });
        } else if (engagementLevel < 0.3) {
            insights.push({
                type: 'warning',
                title: 'Low User Engagement',
                description: `User showing ${(engagementLevel * 100).toFixed(1)}% engagement level`,
                impact: 'negative',
                recommendation: 'Review content relevance and page layout'
            });
        }

        // Error insights
        if (this.errors.length > 0) {
            const errorTypes = this.getErrorTypeSummary();
            insights.push({
                type: 'warning',
                title: 'User Experience Errors',
                description: `${this.errors.length} errors detected: ${Object.keys(errorTypes).join(', ')}`,
                impact: 'negative',
                recommendation: 'Address technical issues affecting user experience'
            });
        }

        // Performance insights
        const performanceScore = this.getPerformanceScore();
        if (performanceScore < 60) {
            insights.push({
                type: 'warning',
                title: 'Performance Impact on UX',
                description: `Performance score: ${performanceScore}/100`,
                impact: 'negative',
                recommendation: 'Optimize page performance to improve user experience'
            });
        }

        // Accessibility insights
        if (this.accessibilityIssues.length > 0) {
            insights.push({
                type: 'warning',
                title: 'Accessibility Issues Detected',
                description: `${this.accessibilityIssues.length} accessibility issues found`,
                impact: 'negative',
                recommendation: 'Address accessibility issues for inclusive design'
            });
        }

        return insights;
    }

    /**
     * Export UX Report
     */
    exportUXReport() {
        const report = {
            generatedAt: new Date().toISOString(),
            sessionData: this.sessionData,
            summary: {
                sessionDuration: Date.now() - this.sessionData.startTime,
                totalInteractions: this.interactions.length,
                totalErrors: this.errors.length,
                satisfactionScore: this.satisfactionScore,
                maxScrollDepth: Math.max(...this.scrollData.map(s => s.depth), 0),
                engagementLevel: this.calculateEngagementLevel()
            },
            interactions: this.interactions.slice(-100), // Last 100 interactions
            errors: this.errors,
            performanceMetrics: Array.from(this.performanceMetrics.values()),
            scrollData: this.scrollData.slice(-50), // Last 50 scroll events
            heatmapData: this.heatmapData.slice(-200), // Last 200 heatmap points
            behaviorAnalysis: this.analyzeUserBehavior(),
            insights: this.generateUXInsights(),
            recommendations: this.generateUXRecommendations()
        };

        return report;
    }

    // Utility Methods
    generateSessionId() {
        return `ux_session_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }

    generateInteractionId() {
        return `interaction_${Date.now()}_${Math.random().toString(36).substr(2, 6)}`;
    }

    generateErrorId() {
        return `error_${Date.now()}_${Math.random().toString(36).substr(2, 6)}`;
    }

    getUserId() {
        // Add environment check for localStorage
        if (typeof localStorage !== 'undefined') {
            return localStorage.getItem('userId') || 'anonymous';
        }
        return 'anonymous_node_user';
    }

    getViewportSize() {
        // Add environment check for window
        if (typeof window !== 'undefined') {
            return {
                width: window.innerWidth,
                height: window.innerHeight
            };
        }
        return { width: 1920, height: 1080 }; // Default for Node.js
    }

    getElementSelector(element) {
        if (!element) return 'unknown';
        
        let selector = element.tagName.toLowerCase();
        
        if (element.id) {
            selector += `#${element.id}`;
        }
        
        if (element.className) {
            const classes = element.className.split(' ').filter(c => c.length > 0);
            if (classes.length > 0) {
                selector += `.${classes[0]}`;
            }
        }
        
        return selector;
    }

    calculateScrollDepth() {
        // Add environment check for window and document
        if (typeof window === 'undefined' || typeof document === 'undefined') {
            return 0;
        }

        const windowHeight = window.innerHeight;
        const documentHeight = document.documentElement.scrollHeight;
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        return Math.round(((scrollTop + windowHeight) / documentHeight) * 100);
    }

    isFormElement(element) {
        const formTags = ['INPUT', 'TEXTAREA', 'SELECT', 'BUTTON'];
        return formTags.includes(element.tagName);
    }

    getFormId(element) {
        const form = element.closest('form');
        return form ? (form.id || 'unnamed') : 'no-form';
    }

    isImportantKey(key) {
        const importantKeys = ['Enter', 'Tab', 'Escape', 'Space', 'ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'];
        return importantKeys.includes(key);
    }

    shouldSampleHeatmap() {
        return Math.random() <= this.config.heatmapSampling;
    }

    hasScrollMilestone(milestone) {
        return this.interactions.some(i => 
            i.type === 'scroll_milestone' && 
            i.data.milestone === milestone
        );
    }

    hasVisibleFocus(element) {
        // Add environment check for window
        if (typeof window === 'undefined' || !element) {
            return false;
        }

        const computedStyle = window.getComputedStyle(element);
        return computedStyle.outline !== 'none' || 
               computedStyle.outlineStyle !== 'none' ||
               computedStyle.boxShadow.includes('inset') ||
               element.matches(':focus-visible');
    }

    getRecentInteractions(timeWindow) {
        const cutoff = Date.now() - timeWindow;
        return this.interactions.filter(i => i.timestamp > cutoff);
    }

    getRecentErrors(timeWindow) {
        const cutoff = Date.now() - timeWindow;
        return this.errors.filter(e => e.timestamp > cutoff);
    }

    calculateEngagementLevel() {
        const sessionDuration = Date.now() - this.sessionData.startTime;
        const interactionRate = this.interactions.length / (sessionDuration / 1000); // interactions per second
        const scrollEngagement = Math.max(...this.scrollData.map(s => s.depth), 0) / 100;
        
        // Normalize and combine metrics
        const normalizedInteractionRate = Math.min(interactionRate * 10, 1); // Cap at 1
        const engagement = (normalizedInteractionRate + scrollEngagement) / 2;
        
        return Math.min(engagement, 1);
    }

    calculateErrorImpact(errorType) {
        const impacts = {
            'javascript': 15,
            'promise_rejection': 10,
            'resource': 5,
            'network': 20,
            'form_validation': 8
        };
        return impacts[errorType] || 5;
    }

    getPerformanceScore() {
        // Simplified performance score calculation
        const metrics = Array.from(this.performanceMetrics.values());
        if (metrics.length === 0) return 85; // Default good score
        
        // Calculate average performance based on available metrics
        return 85; // Placeholder
    }

    calculateFormSuccessRate() {
        const formSubmissions = this.interactions.filter(i => i.type === 'form_submit');
        const formErrors = this.errors.filter(e => e.type === 'form_validation');
        
        if (formSubmissions.length === 0) return 100;
        
        const successRate = ((formSubmissions.length - formErrors.length) / formSubmissions.length) * 100;
        return Math.max(0, successRate);
    }

    getErrorTypeSummary() {
        const summary = {};
        this.errors.forEach(error => {
            summary[error.type] = (summary[error.type] || 0) + 1;
        });
        return summary;
    }

    generateUXRecommendations() {
        return [
            'Continue monitoring user interaction patterns',
            'Address any identified performance issues',
            'Maintain accessibility compliance',
            'Optimize form user experience based on error patterns'
        ];
    }

    // Placeholder methods for complex implementations
    collectPerformanceData() { return {}; }
    startFrameRateMonitoring() {}
    detectScreenReader() {}
    monitorColorContrast() {}
    collectUXMetrics() {}
    updateUXDashboard() {}
    checkSessionTimeout() {}
    cleanupTrackingData() {}
    storeInteractionData() {}
    storeErrorData() {}
    updateScrollMetrics() {}
    analyzeFormBehavior() {}
    analyzePerformanceImpact() {}
    analyzeAccessibilityCompliance() {}
    analyzeNavigationPattern() { return 'linear'; }
    analyzeInteractionQuality() { return 'good'; }
    analyzeContentConsumption() { return 'engaged'; }
    identifyPotentialIssues() { return []; }

    updateUXDashboard(data) {
        // Add environment check for window and CustomEvent
        if (typeof window !== 'undefined' && typeof CustomEvent !== 'undefined') {
            window.dispatchEvent(new CustomEvent('uxTrackingUpdate', { 
                detail: data || this.exportUXReport() 
            }));
        } else {
            console.warn('⚠️ window or CustomEvent not available. Skipping UX dashboard update.');
        }
    }

    logUXEvent(eventType, message) {
        console.log(`👥 [${eventType}] ${message}`);
    }

    handleUXError(errorType, error) {
        console.error(`🚨 UX Tracking Error [${errorType}]:`, error);
    }
}

// Initialize when DOM is ready, only in browser environment
if (typeof document !== 'undefined' && typeof window !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        console.log('👥 User Experience Tracker initializing...');
        
        // Create global instance
        window.userExperienceTracker = new UserExperienceTracker();
        
        // Add global convenience methods
        window.getUXReport = () => window.userExperienceTracker.exportUXReport();
        window.getUXInsights = () => window.userExperienceTracker.generateUXInsights();
        window.getSatisfactionScore = () => window.userExperienceTracker.satisfactionScore;
        
        console.log('✅ User Experience Tracker available globally');
    });
} else if (typeof module !== 'undefined' && module.exports) {
    // For Node.js environments
    module.exports = UserExperienceTracker;
    console.log('👥 User Experience Tracker ready for Node.js environment');
}

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = UserExperienceTracker;
}
