/**
 * SELINAY TASK 7: MAINTENANCE & OPTIMIZATION PROTOCOL
 * Performance Regression Detector - Advanced Monitoring System
 * 
 * Implements automated regression detection for ongoing maintenance
 * Features real-time performance comparison and predictive analysis
 * 
 * @version 2.0.0
 * @date June 5, 2025
 * @author Selinay Team - Frontend UI/UX Specialist
 * @priority CRITICAL - Task 7 Implementation
 */

class PerformanceRegressionDetector {
    constructor(options = {}) {
        this.config = {
            sampleWindow: options.sampleWindow || 50, // Number of samples to analyze
            regressionThreshold: options.regressionThreshold || 0.15, // 15% degradation
            sensitivityLevel: options.sensitivityLevel || 0.8, // 80% confidence
            detectionInterval: options.detectionInterval || 30000, // 30 seconds
            baselineWindow: options.baselineWindow || 7, // Days for baseline
            ...options
        };

        this.performanceBaselines = new Map();
        this.performanceHistory = new Map();
        this.regressionAlerts = [];
        this.isMonitoring = false;
        this.monitoringInterval = null;
        
        this.metrics = {
            detected: 0,
            prevented: 0,
            accuracy: 0,
            lastDetection: null,
            activeSessions: 0
        };

        this.alertChannels = {
            realtime: [],
            email: [],
            slack: [],
            dashboard: []
        };

        console.log('🔍 Selinay Performance Regression Detector v2.0 initialized');
        this.initializeRegressionDetection();
    }

    /**
     * Initialize regression detection system
     */
    initializeRegressionDetection() {
        this.setupPerformanceBaselines();
        this.initializeStatisticalAnalysis();
        this.setupRegressionPatterns();
        this.initializeAlertSystem();
        
        console.log('✅ Regression detection system initialized');
    }

    /**
     * Setup performance baselines for comparison
     */
    setupPerformanceBaselines() {
        const baselineMetrics = [
            'loadTime',
            'firstContentfulPaint',
            'largestContentfulPaint',
            'cumulativeLayoutShift',
            'firstInputDelay',
            'timeToFirstByte',
            'memoryUsage',
            'cpuUtilization',
            'networkLatency',
            'renderTime',
            'apiResponseTime'
        ];

        baselineMetrics.forEach(metric => {
            this.performanceBaselines.set(metric, {
                values: [],
                mean: 0,
                standardDeviation: 0,
                percentile95: 0,
                percentile99: 0,
                trend: 'stable',
                lastUpdated: Date.now(),
                confidence: 1.0
            });

            this.performanceHistory.set(metric, []);
        });

        // Load historical data if available
        this.loadHistoricalBaselines();
    }

    /**
     * Initialize statistical analysis engine
     */
    initializeStatisticalAnalysis() {
        this.statisticalEngine = {
            // Z-score based detection
            zScoreDetection: (value, baseline) => {
                if (baseline.standardDeviation === 0) return 0;
                return (value - baseline.mean) / baseline.standardDeviation;
            },

            // Exponential Weighted Moving Average
            ewmaDetection: (values, alpha = 0.2) => {
                if (values.length < 2) return 0;
                
                let ewma = values[0];
                for (let i = 1; i < values.length; i++) {
                    ewma = alpha * values[i] + (1 - alpha) * ewma;
                }
                return ewma;
            },

            // Change Point Detection
            changePointDetection: (values, threshold = 0.1) => {
                if (values.length < 10) return -1;
                
                const segmentSize = Math.floor(values.length / 2);
                const firstSegment = values.slice(0, segmentSize);
                const secondSegment = values.slice(segmentSize);
                
                const firstMean = firstSegment.reduce((a, b) => a + b) / firstSegment.length;
                const secondMean = secondSegment.reduce((a, b) => a + b) / secondSegment.length;
                
                const change = Math.abs(secondMean - firstMean) / firstMean;
                return change > threshold ? segmentSize : -1;
            },

            // Seasonal Decomposition
            seasonalAnalysis: (values, period = 24) => {
                if (values.length < period * 2) return { trend: 0, seasonal: 0, residual: 0 };
                
                // Simple trend calculation
                const firstHalf = values.slice(0, Math.floor(values.length / 2));
                const secondHalf = values.slice(Math.floor(values.length / 2));
                
                const firstMean = firstHalf.reduce((a, b) => a + b) / firstHalf.length;
                const secondMean = secondHalf.reduce((a, b) => a + b) / secondHalf.length;
                
                return {
                    trend: (secondMean - firstMean) / firstMean,
                    seasonal: 0, // Simplified for this implementation
                    residual: 0
                };
            }
        };

        console.log('📊 Statistical analysis engine initialized');
    }

    /**
     * Setup regression pattern recognition
     */
    setupRegressionPatterns() {
        this.regressionPatterns = {
            // Gradual degradation over time
            gradualDegradation: {
                detector: (history) => {
                    if (history.length < 10) return false;
                    
                    const recent = history.slice(-10);
                    const older = history.slice(-20, -10);
                    
                    if (older.length === 0) return false;
                    
                    const recentAvg = recent.reduce((a, b) => a + b.value) / recent.length;
                    const olderAvg = older.reduce((a, b) => a + b.value) / older.length;
                    
                    return (recentAvg - olderAvg) / olderAvg > this.config.regressionThreshold;
                },
                severity: 'medium',
                action: 'investigate_trend'
            },

            // Sudden performance drops
            suddenDrop: {
                detector: (history) => {
                    if (history.length < 5) return false;
                    
                    const latest = history[history.length - 1];
                    const baseline = history.slice(-6, -1);
                    
                    if (baseline.length === 0) return false;
                    
                    const baselineAvg = baseline.reduce((a, b) => a + b.value) / baseline.length;
                    return (latest.value - baselineAvg) / baselineAvg > this.config.regressionThreshold * 2;
                },
                severity: 'high',
                action: 'immediate_investigation'
            },

            // Memory leak detection
            memoryLeak: {
                detector: (history) => {
                    if (history.length < 20) return false;
                    
                    const values = history.map(h => h.value);
                    const trend = this.statisticalEngine.seasonalAnalysis(values).trend;
                    
                    return trend > 0.1 && values[values.length - 1] > values[0] * 1.5;
                },
                severity: 'high',
                action: 'memory_analysis'
            },

            // Periodic performance issues
            periodicIssues: {
                detector: (history) => {
                    if (history.length < 50) return false;
                    
                    // Check for recurring spikes
                    const values = history.map(h => h.value);
                    const spikes = values.filter(v => v > values.reduce((a, b) => a + b) / values.length * 1.5);
                    
                    return spikes.length > values.length * 0.1;
                },
                severity: 'medium',
                action: 'pattern_analysis'
            }
        };

        console.log('🔄 Regression patterns configured');
    }

    /**
     * Initialize alert system
     */
    initializeAlertSystem() {
        this.alertSystem = {
            send: (alert) => {
                // Real-time alerts
                this.alertChannels.realtime.forEach(callback => {
                    callback(alert);
                });

                // Dashboard notifications
                this.sendDashboardAlert(alert);

                // Log to console for development
                console.warn(`🚨 REGRESSION ALERT: ${alert.title}`, alert);
            },

            generateAlert: (metric, regression) => {
                return {
                    id: `reg_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
                    timestamp: new Date().toISOString(),
                    type: 'performance_regression',
                    metric: metric,
                    severity: regression.severity,
                    title: `Performance Regression Detected: ${metric}`,
                    description: this.getAlertDescription(metric, regression),
                    currentValue: regression.currentValue,
                    baselineValue: regression.baselineValue,
                    degradationPercent: regression.degradationPercent,
                    action: regression.action,
                    recommendations: this.getRecommendations(metric, regression),
                    affectedComponents: this.getAffectedComponents(metric),
                    estimatedImpact: this.estimateUserImpact(metric, regression)
                };
            }
        };

        console.log('🔔 Alert system initialized');
    }

    /**
     * Start regression monitoring
     */
    startMonitoring() {
        if (this.isMonitoring) {
            console.warn('⚠️ Regression monitoring already active');
            return;
        }

        console.log('🚀 Starting performance regression monitoring...');
        this.isMonitoring = true;
        this.metrics.activeSessions++;

        // Start continuous monitoring
        this.monitoringInterval = setInterval(() => {
            this.performRegressionAnalysis();
        }, this.config.detectionInterval);

        // Update baselines periodically
        setInterval(() => {
            this.updateBaselines();
        }, 300000); // Every 5 minutes

        // Performance pattern analysis
        setInterval(() => {
            this.analyzePerformancePatterns();
        }, 600000); // Every 10 minutes

        console.log('✅ Regression monitoring started');
        this.emitStatusUpdate('monitoring_started');
    }

    /**
     * Stop regression monitoring
     */
    stopMonitoring() {
        if (!this.isMonitoring) {
            console.warn('⚠️ Regression monitoring not active');
            return;
        }

        console.log('⏹️ Stopping performance regression monitoring...');
        this.isMonitoring = false;

        if (this.monitoringInterval) {
            clearInterval(this.monitoringInterval);
            this.monitoringInterval = null;
        }

        console.log('✅ Regression monitoring stopped');
        this.emitStatusUpdate('monitoring_stopped');
    }

    /**
     * Record performance metric
     */
    recordMetric(metricName, value, timestamp = Date.now()) {
        if (!this.performanceHistory.has(metricName)) {
            console.warn(`⚠️ Unknown metric: ${metricName}`);
            return;
        }

        const history = this.performanceHistory.get(metricName);
        const entry = {
            value: value,
            timestamp: timestamp,
            sessionId: this.getCurrentSessionId()
        };

        history.push(entry);

        // Maintain window size
        if (history.length > this.config.sampleWindow * 2) {
            history.splice(0, history.length - this.config.sampleWindow * 2);
        }

        this.performanceHistory.set(metricName, history);

        // Check for immediate regressions
        if (this.isMonitoring) {
            this.checkImmediateRegression(metricName, value);
        }
    }

    /**
     * Perform comprehensive regression analysis
     */
    performRegressionAnalysis() {
        const regressions = [];

        this.performanceHistory.forEach((history, metricName) => {
            if (history.length < 10) return;

            const baseline = this.performanceBaselines.get(metricName);
            const recent = history.slice(-10);
            const recentValues = recent.map(r => r.value);
            const recentMean = recentValues.reduce((a, b) => a + b) / recentValues.length;

            // Z-score analysis
            const zScore = this.statisticalEngine.zScoreDetection(recentMean, baseline);
            
            // Check each pattern
            Object.entries(this.regressionPatterns).forEach(([patternName, pattern]) => {
                if (pattern.detector(history)) {
                    const regression = {
                        metric: metricName,
                        pattern: patternName,
                        severity: pattern.severity,
                        action: pattern.action,
                        currentValue: recentMean,
                        baselineValue: baseline.mean,
                        degradationPercent: ((recentMean - baseline.mean) / baseline.mean * 100).toFixed(2),
                        confidence: this.calculateConfidence(history, baseline),
                        zScore: zScore,
                        detectedAt: Date.now()
                    };

                    regressions.push(regression);
                    this.handleRegression(metricName, regression);
                }
            });
        });

        if (regressions.length > 0) {
            this.metrics.detected += regressions.length;
            this.metrics.lastDetection = Date.now();
            console.log(`🔍 Detected ${regressions.length} performance regressions`);
        }

        return regressions;
    }

    /**
     * Check for immediate regression
     */
    checkImmediateRegression(metricName, value) {
        const baseline = this.performanceBaselines.get(metricName);
        const history = this.performanceHistory.get(metricName);

        if (baseline.mean === 0 || history.length < 5) return;

        const zScore = this.statisticalEngine.zScoreDetection(value, baseline);
        
        // Immediate alert for severe degradation
        if (Math.abs(zScore) > 3) {
            const regression = {
                metric: metricName,
                pattern: 'immediate_anomaly',
                severity: 'critical',
                action: 'immediate_investigation',
                currentValue: value,
                baselineValue: baseline.mean,
                degradationPercent: ((value - baseline.mean) / baseline.mean * 100).toFixed(2),
                confidence: 0.95,
                zScore: zScore,
                detectedAt: Date.now()
            };

            this.handleRegression(metricName, regression);
        }
    }

    /**
     * Handle detected regression
     */
    handleRegression(metricName, regression) {
        const alert = this.alertSystem.generateAlert(metricName, regression);
        this.regressionAlerts.push(alert);

        // Send alert
        this.alertSystem.send(alert);

        // Update metrics
        this.updateRegressionMetrics(regression);

        // Trigger automatic mitigation if configured
        if (this.config.autoMitigation) {
            this.triggerAutoMitigation(metricName, regression);
        }

        // Store for analysis
        this.storeRegressionData(alert);
    }

    /**
     * Update performance baselines
     */
    updateBaselines() {
        this.performanceHistory.forEach((history, metricName) => {
            if (history.length < 20) return;

            const values = history.slice(-30).map(h => h.value);
            const baseline = this.performanceBaselines.get(metricName);

            // Calculate new statistics
            const mean = values.reduce((a, b) => a + b) / values.length;
            const variance = values.reduce((a, b) => a + Math.pow(b - mean, 2), 0) / values.length;
            const standardDeviation = Math.sqrt(variance);

            // Calculate percentiles
            const sorted = [...values].sort((a, b) => a - b);
            const percentile95 = sorted[Math.floor(sorted.length * 0.95)];
            const percentile99 = sorted[Math.floor(sorted.length * 0.99)];

            // Update baseline
            baseline.mean = mean;
            baseline.standardDeviation = standardDeviation;
            baseline.percentile95 = percentile95;
            baseline.percentile99 = percentile99;
            baseline.lastUpdated = Date.now();

            this.performanceBaselines.set(metricName, baseline);
        });

        console.log('📊 Performance baselines updated');
    }

    /**
     * Analyze performance patterns
     */
    analyzePerformancePatterns() {
        const patterns = {
            timeOfDay: this.analyzeTimePatterns(),
            dayOfWeek: this.analyzeDayPatterns(),
            loadBased: this.analyzeLoadPatterns(),
            seasonal: this.analyzeSeasonalPatterns()
        };

        this.performancePatterns = patterns;
        this.emitStatusUpdate('patterns_analyzed', patterns);
        
        console.log('🔄 Performance patterns analyzed:', patterns);
    }

    /**
     * Get alert description
     */
    getAlertDescription(metric, regression) {
        const descriptions = {
            gradualDegradation: `Gradual performance degradation detected in ${metric}. Performance has declined by ${regression.degradationPercent}% over time.`,
            suddenDrop: `Sudden performance drop detected in ${metric}. Immediate ${regression.degradationPercent}% degradation observed.`,
            memoryLeak: `Potential memory leak detected. ${metric} shows sustained growth pattern indicating resource leakage.`,
            periodicIssues: `Periodic performance issues detected in ${metric}. Recurring performance spikes identified.`,
            immediate_anomaly: `Critical performance anomaly detected in ${metric}. Immediate attention required.`
        };

        return descriptions[regression.pattern] || `Performance regression detected in ${metric}`;
    }

    /**
     * Get recommendations for regression
     */
    getRecommendations(metric, regression) {
        const recommendations = {
            loadTime: [
                'Check server response times',
                'Optimize critical rendering path',
                'Review resource loading order',
                'Implement caching strategies'
            ],
            memoryUsage: [
                'Investigate memory leaks',
                'Review garbage collection patterns',
                'Optimize data structures',
                'Implement memory pooling'
            ],
            apiResponseTime: [
                'Check database query performance',
                'Review API endpoint efficiency',
                'Implement response caching',
                'Scale backend resources'
            ],
            networkLatency: [
                'Check CDN performance',
                'Review network configuration',
                'Implement connection pooling',
                'Optimize payload sizes'
            ]
        };

        return recommendations[metric] || [
            'Investigate root cause',
            'Review recent code changes',
            'Check system resources',
            'Monitor user impact'
        ];
    }

    /**
     * Get affected components
     */
    getAffectedComponents(metric) {
        const components = {
            loadTime: ['Page rendering', 'User experience', 'SEO scores'],
            memoryUsage: ['Browser performance', 'System stability', 'User sessions'],
            apiResponseTime: ['Data loading', 'User interactions', 'System reliability'],
            networkLatency: ['Real-time features', 'Data synchronization', 'User experience']
        };

        return components[metric] || ['System performance', 'User experience'];
    }

    /**
     * Estimate user impact
     */
    estimateUserImpact(metric, regression) {
        const severity = regression.severity;
        const degradation = parseFloat(regression.degradationPercent);

        if (severity === 'critical' || degradation > 50) {
            return {
                level: 'high',
                description: 'Significant user experience degradation expected',
                affectedUsers: '> 80%',
                businessImpact: 'High risk of user abandonment'
            };
        } else if (severity === 'high' || degradation > 25) {
            return {
                level: 'medium',
                description: 'Noticeable performance impact for users',
                affectedUsers: '40-80%',
                businessImpact: 'Potential impact on conversion rates'
            };
        } else {
            return {
                level: 'low',
                description: 'Minor performance impact',
                affectedUsers: '< 40%',
                businessImpact: 'Limited business impact expected'
            };
        }
    }

    /**
     * Calculate confidence level
     */
    calculateConfidence(history, baseline) {
        const sampleSize = history.length;
        const variability = baseline.standardDeviation / baseline.mean;
        
        // Simple confidence calculation based on sample size and variability
        let confidence = Math.min(1.0, sampleSize / 30);
        confidence *= Math.max(0.5, 1 - variability);
        
        return Math.round(confidence * 100) / 100;
    }

    /**
     * Load historical baselines
     */
    loadHistoricalBaselines() {
        try {
            // Add environment check for localStorage
            if (typeof localStorage !== 'undefined') {
                const stored = localStorage.getItem('selinay_performance_baselines');
                if (stored) {
                    const data = JSON.parse(stored);
                    Object.entries(data).forEach(([metric, baseline]) => {
                        this.performanceBaselines.set(metric, baseline);
                    });
                    console.log('📚 Historical baselines loaded');
                } else {
                    console.warn('⚠️ localStorage not available. Skipping historical baselines loading.');
                }
            } else {
                console.warn('⚠️ localStorage not available. Skipping historical baselines loading.');
            }
        } catch (error) {
            console.warn('⚠️ Could not load historical baselines:', error);
        }
    }

    /**
     * Save baselines to storage
     */
    saveBaselines() {
        try {
            // Add environment check for localStorage
            if (typeof localStorage !== 'undefined') {
                const data = Object.fromEntries(this.performanceBaselines);
                localStorage.setItem('selinay_performance_baselines', JSON.stringify(data));
            } else {
                console.warn('⚠️ localStorage not available. Skipping baselines saving.');
            }
        } catch (error) {
            console.warn('⚠️ Could not save baselines:', error);
        }
    }

    /**
     * Send dashboard alert
     */
    sendDashboardAlert(alert) {
        // Add environment check for window and CustomEvent
        if (typeof window !== 'undefined' && typeof CustomEvent !== 'undefined') {
            // Emit custom event for dashboard
            const event = new CustomEvent('selinayRegressionAlert', {
                detail: alert
            });
            window.dispatchEvent(event);
        } else {
            console.warn('⚠️ window or CustomEvent not available. Skipping dashboard alert.');
        }
    }

    /**
     * Trigger automatic mitigation actions
     */
    triggerAutoMitigation(metricName, regression) {
        console.log(`🔧 Triggering auto-mitigation for ${metricName}`);
        
        const mitigations = {
            memoryUsage: () => {
                // Add environment check for window
                if (typeof window !== 'undefined' && typeof window.gc === 'function') {
                    window.gc();
                }
                // Clear caches
                this.clearPerformanceCaches();
            },
            apiResponseTime: () => {
                // Scale backend resources (placeholder for actual implementation)
                console.log('🔧 Scaling backend resources for API response time');
            },
            networkLatency: () => {
                // Optimize network settings (placeholder for actual implementation)
                console.log('🔧 Optimizing network settings for latency');
            },
            loadTime: () => {
                // Preload critical resources (placeholder for actual implementation)
                console.log('🔧 Preloading critical resources to improve load time');
            }
        };

        if (mitigations[metricName]) {
            mitigations[metricName]();
            console.log(`✅ Auto-mitigation applied for ${metricName}`);
        }
    }

    /**
     * Store regression data for analysis
     */
    storeRegressionData(alert) {
        try {
            // Add environment check for localStorage
            if (typeof localStorage !== 'undefined') {
                const stored = JSON.parse(localStorage.getItem('selinay_regression_history') || '[]');
                stored.push(alert);
                
                // Limit storage to last 100 entries
                if (stored.length > 100) {
                    stored.splice(0, stored.length - 100);
                }
                
                localStorage.setItem('selinay_regression_history', JSON.stringify(stored));
            } else {
                console.warn('⚠️ localStorage not available. Skipping regression data storage.');
            }
        } catch (error) {
            console.warn('⚠️ Could not store regression data:', error);
        }
    }

    /**
     * Get current session ID
     */
    getCurrentSessionId() {
        // Add environment check for sessionStorage
        if (typeof sessionStorage !== 'undefined') {
            return sessionStorage.getItem('selinay_session_id') || 'default_node_session';
        }
        return 'default_node_session'; // Fallback for Node.js
    }

    /**
     * Clear performance-related caches
     */
    clearPerformanceCaches() {
        // Add environment check for navigator and caches
        if (typeof navigator !== 'undefined' && 'serviceWorker' in navigator && typeof caches !== 'undefined') {
            caches.keys().then(names => {
                names.forEach(name => caches.delete(name));
            });
        } else {
            console.warn('⚠️ navigator.serviceWorker or caches API not available. Skipping cache clearing.');
        }
    }

    /**
     * Emit status updates to the dashboard
     */
    emitStatusUpdate(event, data = {}) {
        // Add environment check for window and CustomEvent
        if (typeof window !== 'undefined' && typeof CustomEvent !== 'undefined') {
            const statusEvent = new CustomEvent('selinayRegressionStatus', {
                detail: {
                    event: event,
                    timestamp: Date.now(),
                    metrics: this.metrics,
                    data: data
                }
            });
            window.dispatchEvent(statusEvent);
        } else {
            console.warn('⚠️ window or CustomEvent not available. Skipping status update.');
        }
    }

    /**
     * Get regression history from storage
     */
    getRegressionHistory() {
        try {
            // Add environment check for localStorage
            if (typeof localStorage !== 'undefined') {
                return JSON.parse(localStorage.getItem('selinay_regression_history') || '[]');
            }
            console.warn('⚠️ localStorage not available. Returning empty regression history.');
            return [];
        } catch (error) {
            console.warn('⚠️ Could not load regression history:', error);
            return [];
        }
    }

    /**
     * Export regression data as JSON file
     */
    exportData() {
        // Add environment check for Blob, URL, document
        if (typeof Blob !== 'undefined' && typeof URL !== 'undefined' && typeof document !== 'undefined' && typeof document.createElement === 'function') {
            const exportData = {
                baselines: Object.fromEntries(this.performanceBaselines),
                history: this.getRegressionHistory(),
                config: this.config
            };

            // Create downloadable file
            const blob = new Blob([JSON.stringify(exportData, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            
            const a = document.createElement('a');
            a.href = url;
            a.download = `selinay_regression_data_${Date.now()}.json`;
            a.click();
            
            URL.revokeObjectURL(url);
            console.log('📁 Regression data exported');
        } else {
            console.warn('⚠️ Browser-specific APIs (Blob, URL, document) not available. Skipping data export.');
        }
    }
}

// Export for global use
// Add environment check for window
if (typeof window !== 'undefined') {
    window.PerformanceRegressionDetector = PerformanceRegressionDetector;

    // Initialize for automatic use
    window.addEventListener('DOMContentLoaded', () => {
        if (!window.selinayRegressionDetector) {
            window.selinayRegressionDetector = new PerformanceRegressionDetector({
                autoStart: true,
                sensitivityLevel: 0.8
            });
            
            // Auto-start monitoring
            window.selinayRegressionDetector.startMonitoring();
            
            console.log('🔍 Selinay Performance Regression Detector auto-initialized');
        }
    });
} else {
    // Export for Node.js environments if needed
    module.exports = PerformanceRegressionDetector;
}

console.log('🔍 Performance Regression Detector v2.0 loaded successfully!');