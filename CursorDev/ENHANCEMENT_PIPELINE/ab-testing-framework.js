/**
 * MesChain-Sync Enterprise - A/B Testing Framework
 * Selinay Team - Task 7: Maintenance & Optimization Protocol
 * 
 * Enterprise-grade A/B testing framework for feature rollouts,
 * user experience optimization, and data-driven decision making
 */

const EventEmitter = require('events');
const crypto = require('crypto');

class ABTestingFramework extends EventEmitter {
    constructor(config = {}) {
        super();
        
        this.config = {
            defaultSampleSize: config.defaultSampleSize || 1000,
            confidenceLevel: config.confidenceLevel || 0.95,
            minimumDetectableEffect: config.minimumDetectableEffect || 0.05,
            maxTestDuration: config.maxTestDuration || 30 * 24 * 60 * 60 * 1000, // 30 days
            autoStopOnSignificance: config.autoStopOnSignificance !== false,
            dataRetentionDays: config.dataRetentionDays || 90,
            ...config
        };

        this.tests = new Map();
        this.userSegments = new Map();
        this.conversionEvents = new Map();
        this.analytics = new Map();
        this.isInitialized = false;
        this.statisticsCache = new Map();

        this.initialize();
    }

    /**
     * Initialize the A/B testing framework
     */
    async initialize() {
        try {
            await this.loadExistingTests();
            await this.setupEventTracking();
            await this.initializeStatisticsEngine();
            
            this.isInitialized = true;
            this.emit('framework-initialized');
            
            console.log('A/B Testing Framework initialized successfully');
        } catch (error) {
            this.emit('initialization-error', error);
            throw error;
        }
    }

    /**
     * Create a new A/B test
     */
    async createTest(testConfig) {
        try {
            const testId = this.generateTestId();
            const test = {
                id: testId,
                name: testConfig.name,
                description: testConfig.description,
                hypothesis: testConfig.hypothesis,
                variants: this.validateVariants(testConfig.variants),
                targetMetric: testConfig.targetMetric,
                segmentCriteria: testConfig.segmentCriteria || {},
                trafficAllocation: testConfig.trafficAllocation || 100,
                startDate: testConfig.startDate || new Date(),
                endDate: testConfig.endDate,
                status: 'draft',
                createdAt: new Date(),
                createdBy: testConfig.createdBy,
                sampleSize: testConfig.sampleSize || this.config.defaultSampleSize,
                significance: {
                    required: testConfig.significanceLevel || this.config.confidenceLevel,
                    current: 0,
                    achieved: false
                },
                participants: new Map(),
                conversions: new Map(),
                metrics: new Map(),
                metadata: testConfig.metadata || {}
            };

            // Initialize variant tracking
            test.variants.forEach(variant => {
                test.participants.set(variant.id, []);
                test.conversions.set(variant.id, []);
                test.metrics.set(variant.id, {
                    views: 0,
                    conversions: 0,
                    conversionRate: 0,
                    revenue: 0,
                    bounceRate: 0,
                    timeOnPage: 0,
                    customMetrics: new Map()
                });
            });

            this.tests.set(testId, test);
            
            this.emit('test-created', {
                testId,
                test: { ...test, participants: undefined, conversions: undefined }
            });

            return testId;
        } catch (error) {
            this.emit('test-creation-error', { error, config: testConfig });
            throw error;
        }
    }

    /**
     * Start an A/B test
     */
    async startTest(testId) {
        try {
            const test = this.tests.get(testId);
            if (!test) {
                throw new Error(`Test ${testId} not found`);
            }

            if (test.status !== 'draft') {
                throw new Error(`Test ${testId} cannot be started from status: ${test.status}`);
            }

            // Validate test configuration
            await this.validateTestConfiguration(test);

            // Calculate sample size if needed
            test.calculatedSampleSize = this.calculateSampleSize(test);

            // Start the test
            test.status = 'running';
            test.actualStartDate = new Date();
            
            // Setup automated monitoring
            this.setupTestMonitoring(testId);

            this.emit('test-started', { testId, test });
            
            console.log(`A/B Test ${testId} started successfully`);
            return true;
        } catch (error) {
            this.emit('test-start-error', { testId, error });
            throw error;
        }
    }

    /**
     * Assign user to test variant
     */
    assignUserToVariant(testId, userId, userAttributes = {}) {
        try {
            const test = this.tests.get(testId);
            if (!test || test.status !== 'running') {
                return null;
            }

            // Check if user already assigned
            const existingAssignment = this.getUserAssignment(testId, userId);
            if (existingAssignment) {
                return existingAssignment;
            }

            // Check if user matches segment criteria
            if (!this.matchesSegmentCriteria(userAttributes, test.segmentCriteria)) {
                return null;
            }

            // Check traffic allocation
            if (!this.shouldIncludeInTest(userId, test.trafficAllocation)) {
                return null;
            }

            // Assign to variant using consistent hashing
            const variantId = this.assignVariant(testId, userId, test.variants);
            const assignment = {
                testId,
                userId,
                variantId,
                assignedAt: new Date(),
                userAttributes,
                events: []
            };

            // Add to test participants
            test.participants.get(variantId).push(assignment);
            
            // Update metrics
            const metrics = test.metrics.get(variantId);
            metrics.views++;

            this.emit('user-assigned', assignment);
            
            return assignment;
        } catch (error) {
            this.emit('assignment-error', { testId, userId, error });
            return null;
        }
    }

    /**
     * Track conversion event
     */
    async trackConversion(testId, userId, conversionData = {}) {
        try {
            const test = this.tests.get(testId);
            if (!test || test.status !== 'running') {
                return false;
            }

            const assignment = this.getUserAssignment(testId, userId);
            if (!assignment) {
                return false;
            }

            const conversion = {
                testId,
                userId,
                variantId: assignment.variantId,
                conversionType: conversionData.type || 'default',
                value: conversionData.value || 1,
                revenue: conversionData.revenue || 0,
                timestamp: new Date(),
                metadata: conversionData.metadata || {}
            };

            // Add to conversions
            test.conversions.get(assignment.variantId).push(conversion);
            
            // Update metrics
            const metrics = test.metrics.get(assignment.variantId);
            metrics.conversions++;
            metrics.revenue += conversion.revenue;
            metrics.conversionRate = metrics.conversions / metrics.views;

            // Update custom metrics
            if (conversionData.customMetrics) {
                Object.entries(conversionData.customMetrics).forEach(([key, value]) => {
                    if (!metrics.customMetrics.has(key)) {
                        metrics.customMetrics.set(key, []);
                    }
                    metrics.customMetrics.get(key).push(value);
                });
            }

            // Check for statistical significance
            await this.checkStatisticalSignificance(testId);

            this.emit('conversion-tracked', conversion);
            
            return true;
        } catch (error) {
            this.emit('conversion-tracking-error', { testId, userId, error });
            return false;
        }
    }

    /**
     * Calculate statistical significance
     */
    async checkStatisticalSignificance(testId) {
        try {
            const test = this.tests.get(testId);
            if (!test || test.variants.length !== 2) {
                return false;
            }

            const [controlId, treatmentId] = test.variants.map(v => v.id);
            const controlMetrics = test.metrics.get(controlId);
            const treatmentMetrics = test.metrics.get(treatmentId);

            // Check minimum sample size
            const totalParticipants = controlMetrics.views + treatmentMetrics.views;
            if (totalParticipants < test.calculatedSampleSize) {
                return false;
            }

            // Perform z-test for proportions
            const significance = this.calculateZTest(
                controlMetrics.conversions,
                controlMetrics.views,
                treatmentMetrics.conversions,
                treatmentMetrics.views
            );

            test.significance.current = significance.pValue;
            test.significance.achieved = significance.pValue <= (1 - test.significance.required);

            if (test.significance.achieved && this.config.autoStopOnSignificance) {
                await this.stopTest(testId, 'significance-achieved');
            }

            this.emit('significance-calculated', {
                testId,
                significance: test.significance,
                statistics: significance
            });

            return test.significance.achieved;
        } catch (error) {
            this.emit('significance-calculation-error', { testId, error });
            return false;
        }
    }

    /**
     * Stop an A/B test
     */
    async stopTest(testId, reason = 'manual') {
        try {
            const test = this.tests.get(testId);
            if (!test) {
                throw new Error(`Test ${testId} not found`);
            }

            if (test.status !== 'running') {
                throw new Error(`Test ${testId} is not running`);
            }

            test.status = 'completed';
            test.endedAt = new Date();
            test.endReason = reason;

            // Generate final report
            const report = await this.generateTestReport(testId);
            test.finalReport = report;

            // Cleanup monitoring
            this.cleanupTestMonitoring(testId);

            this.emit('test-stopped', { testId, reason, report });
            
            console.log(`A/B Test ${testId} stopped: ${reason}`);
            return report;
        } catch (error) {
            this.emit('test-stop-error', { testId, error });
            throw error;
        }
    }

    /**
     * Generate comprehensive test report
     */
    async generateTestReport(testId) {
        try {
            const test = this.tests.get(testId);
            if (!test) {
                throw new Error(`Test ${testId} not found`);
            }

            const report = {
                testId,
                testName: test.name,
                hypothesis: test.hypothesis,
                status: test.status,
                duration: test.endedAt ? test.endedAt - test.actualStartDate : Date.now() - test.actualStartDate,
                totalParticipants: 0,
                variants: [],
                winner: null,
                confidence: test.significance.current,
                statisticalSignificance: test.significance.achieved,
                recommendations: [],
                insights: [],
                generatedAt: new Date()
            };

            // Calculate variant performance
            test.variants.forEach(variant => {
                const metrics = test.metrics.get(variant.id);
                const participants = test.participants.get(variant.id).length;
                
                report.totalParticipants += participants;
                
                const variantReport = {
                    id: variant.id,
                    name: variant.name,
                    participants,
                    conversions: metrics.conversions,
                    conversionRate: metrics.conversionRate,
                    revenue: metrics.revenue,
                    revenuePerUser: participants > 0 ? metrics.revenue / participants : 0,
                    improvement: 0,
                    customMetrics: {}
                };

                // Calculate custom metrics averages
                metrics.customMetrics.forEach((values, key) => {
                    variantReport.customMetrics[key] = {
                        average: values.reduce((a, b) => a + b, 0) / values.length,
                        count: values.length,
                        total: values.reduce((a, b) => a + b, 0)
                    };
                });

                report.variants.push(variantReport);
            });

            // Determine winner and improvements
            if (report.variants.length === 2) {
                const [control, treatment] = report.variants;
                const improvement = ((treatment.conversionRate - control.conversionRate) / control.conversionRate) * 100;
                
                treatment.improvement = improvement;
                
                if (test.significance.achieved) {
                    report.winner = improvement > 0 ? treatment : control;
                    report.recommendations.push(
                        `Deploy ${report.winner.name} variant based on statistical significance`
                    );
                } else {
                    report.recommendations.push('Continue testing - no significant difference detected');
                }
            }

            // Generate insights
            report.insights = this.generateInsights(test, report);

            return report;
        } catch (error) {
            this.emit('report-generation-error', { testId, error });
            throw error;
        }
    }

    /**
     * Get current test status and metrics
     */
    getTestStatus(testId) {
        const test = this.tests.get(testId);
        if (!test) {
            return null;
        }

        const status = {
            id: testId,
            name: test.name,
            status: test.status,
            startDate: test.actualStartDate,
            endDate: test.endedAt,
            participants: 0,
            variants: []
        };

        test.variants.forEach(variant => {
            const metrics = test.metrics.get(variant.id);
            const participants = test.participants.get(variant.id).length;
            
            status.participants += participants;
            status.variants.push({
                id: variant.id,
                name: variant.name,
                participants,
                conversions: metrics.conversions,
                conversionRate: metrics.conversionRate
            });
        });

        return status;
    }

    /**
     * Helper Methods
     */
    
    generateTestId() {
        return `test_${Date.now()}_${crypto.randomBytes(4).toString('hex')}`;
    }

    validateVariants(variants) {
        if (!Array.isArray(variants) || variants.length < 2) {
            throw new Error('At least 2 variants required');
        }

        const totalWeight = variants.reduce((sum, v) => sum + (v.weight || 50), 0);
        if (Math.abs(totalWeight - 100) > 0.1) {
            throw new Error('Variant weights must sum to 100');
        }

        return variants.map((variant, index) => ({
            id: variant.id || `variant_${index}`,
            name: variant.name || `Variant ${index + 1}`,
            description: variant.description || '',
            weight: variant.weight || (100 / variants.length),
            config: variant.config || {}
        }));
    }

    matchesSegmentCriteria(userAttributes, criteria) {
        if (!criteria || Object.keys(criteria).length === 0) {
            return true;
        }

        return Object.entries(criteria).every(([key, expectedValue]) => {
            const userValue = userAttributes[key];
            
            if (Array.isArray(expectedValue)) {
                return expectedValue.includes(userValue);
            }
            
            if (typeof expectedValue === 'object' && expectedValue.operator) {
                return this.evaluateCondition(userValue, expectedValue);
            }
            
            return userValue === expectedValue;
        });
    }

    shouldIncludeInTest(userId, trafficAllocation) {
        if (trafficAllocation >= 100) return true;
        
        const hash = crypto.createHash('md5').update(userId).digest('hex');
        const hashValue = parseInt(hash.substring(0, 8), 16);
        const percentage = (hashValue % 100) + 1;
        
        return percentage <= trafficAllocation;
    }

    assignVariant(testId, userId, variants) {
        const hash = crypto.createHash('md5').update(`${testId}_${userId}`).digest('hex');
        const hashValue = parseInt(hash.substring(0, 8), 16);
        const percentage = hashValue % 100;
        
        let cumulative = 0;
        for (const variant of variants) {
            cumulative += variant.weight;
            if (percentage < cumulative) {
                return variant.id;
            }
        }
        
        return variants[variants.length - 1].id;
    }

    getUserAssignment(testId, userId) {
        const test = this.tests.get(testId);
        if (!test) return null;

        for (const [variantId, participants] of test.participants) {
            const assignment = participants.find(p => p.userId === userId);
            if (assignment) return assignment;
        }

        return null;
    }

    calculateSampleSize(test) {
        // Simplified sample size calculation for conversion rate optimization
        const alpha = 1 - test.significance.required; // Type I error
        const beta = 0.2; // Type II error (80% power)
        const mde = this.config.minimumDetectableEffect;
        const baselineRate = 0.05; // Assumed baseline conversion rate

        // Z-scores for alpha/2 and beta
        const zAlpha = 1.96; // For 95% confidence
        const zBeta = 0.84; // For 80% power

        const p1 = baselineRate;
        const p2 = baselineRate * (1 + mde);
        const pPooled = (p1 + p2) / 2;

        const numerator = Math.pow(zAlpha + zBeta, 2) * 2 * pPooled * (1 - pPooled);
        const denominator = Math.pow(p2 - p1, 2);

        return Math.ceil(numerator / denominator);
    }

    calculateZTest(conversions1, total1, conversions2, total2) {
        const p1 = conversions1 / total1;
        const p2 = conversions2 / total2;
        const pPooled = (conversions1 + conversions2) / (total1 + total2);
        
        const standardError = Math.sqrt(pPooled * (1 - pPooled) * (1/total1 + 1/total2));
        const zScore = (p2 - p1) / standardError;
        const pValue = 2 * (1 - this.normalCDF(Math.abs(zScore)));

        return {
            zScore,
            pValue,
            significant: pValue <= (1 - this.config.confidenceLevel)
        };
    }

    normalCDF(x) {
        // Approximation of normal cumulative distribution function
        return 0.5 * (1 + this.erf(x / Math.sqrt(2)));
    }

    erf(x) {
        // Approximation of error function
        const a1 =  0.254829592;
        const a2 = -0.284496736;
        const a3 =  1.421413741;
        const a4 = -1.453152027;
        const a5 =  1.061405429;
        const p  =  0.3275911;

        const sign = x >= 0 ? 1 : -1;
        x = Math.abs(x);

        const t = 1.0 / (1.0 + p * x);
        const y = 1.0 - (((((a5 * t + a4) * t) + a3) * t + a2) * t + a1) * t * Math.exp(-x * x);

        return sign * y;
    }

    generateInsights(test, report) {
        const insights = [];

        // Sample size insights
        if (report.totalParticipants < test.calculatedSampleSize) {
            insights.push(`Sample size (${report.totalParticipants}) is below recommended size (${test.calculatedSampleSize})`);
        }

        // Duration insights
        const durationDays = report.duration / (24 * 60 * 60 * 1000);
        if (durationDays < 7) {
            insights.push('Test duration is less than 7 days - consider running longer for seasonal effects');
        }

        // Performance insights
        if (report.variants.length === 2) {
            const [control, treatment] = report.variants;
            if (Math.abs(treatment.improvement) < 5) {
                insights.push('Small effect size detected - consider practical significance vs statistical significance');
            }
        }

        return insights;
    }

    async loadExistingTests() {
        // Implementation for loading persisted tests
        console.log('Loading existing A/B tests...');
    }

    async setupEventTracking() {
        // Implementation for event tracking setup
        console.log('Setting up event tracking...');
    }

    async initializeStatisticsEngine() {
        // Implementation for statistics engine initialization
        console.log('Initializing statistics engine...');
    }

    async validateTestConfiguration(test) {
        // Implementation for test configuration validation
        return true;
    }

    setupTestMonitoring(testId) {
        // Implementation for automated test monitoring
        const interval = setInterval(() => {
            this.checkTestHealth(testId);
        }, 60 * 60 * 1000); // Check hourly

        if (!this.monitoringIntervals) {
            this.monitoringIntervals = new Map();
        }
        this.monitoringIntervals.set(testId, interval);
    }

    cleanupTestMonitoring(testId) {
        if (this.monitoringIntervals && this.monitoringIntervals.has(testId)) {
            clearInterval(this.monitoringIntervals.get(testId));
            this.monitoringIntervals.delete(testId);
        }
    }

    checkTestHealth(testId) {
        const test = this.tests.get(testId);
        if (!test || test.status !== 'running') return;

        // Check for test duration limits
        const runningTime = Date.now() - test.actualStartDate;
        if (runningTime > this.config.maxTestDuration) {
            this.stopTest(testId, 'max-duration-reached');
            return;
        }

        // Check for statistical significance
        this.checkStatisticalSignificance(testId);
    }

    evaluateCondition(value, condition) {
        switch (condition.operator) {
            case 'gt': return value > condition.value;
            case 'gte': return value >= condition.value;
            case 'lt': return value < condition.value;
            case 'lte': return value <= condition.value;
            case 'eq': return value === condition.value;
            case 'ne': return value !== condition.value;
            case 'in': return condition.value.includes(value);
            case 'nin': return !condition.value.includes(value);
            default: return false;
        }
    }

    /**
     * Get framework statistics
     */
    getFrameworkStats() {
        const stats = {
            totalTests: this.tests.size,
            runningTests: 0,
            completedTests: 0,
            totalParticipants: 0,
            totalConversions: 0,
            significantTests: 0
        };

        for (const test of this.tests.values()) {
            if (test.status === 'running') stats.runningTests++;
            if (test.status === 'completed') stats.completedTests++;
            if (test.significance.achieved) stats.significantTests++;

            test.variants.forEach(variant => {
                const metrics = test.metrics.get(variant.id);
                stats.totalParticipants += test.participants.get(variant.id).length;
                stats.totalConversions += metrics.conversions;
            });
        }

        return stats;
    }
}

module.exports = ABTestingFramework;
