/**
 * Feature Flag System
 * Advanced feature management and controlled rollouts
 * Selinay Team - Task 7.4.1 Implementation
 * June 5, 2025
 */

class FeatureFlagSystem {
    constructor() {
        this.config = {
            rolloutStrategies: {
                percentage: true,
                userGroups: true,
                geographic: true,
                timeBasedRollout: true,
                a_b_testing: true
            },
            targetingRules: {
                userAttributes: ['id', 'email', 'group', 'country', 'subscription'],
                deviceAttributes: ['platform', 'browser', 'version'],
                environmentAttributes: ['environment', 'region', 'cluster']
            },
            safetyMechanisms: {
                killSwitch: true,
                automaticRollback: true,
                performanceMonitoring: true,
                errorRateMonitoring: true
            },
            integrations: {
                analytics: true,
                monitoring: true,
                logging: true,
                notifications: true
            }
        };
        
        this.flags = new Map();
        this.evaluationHistory = [];
        this.rolloutPlans = new Map();
        this.activeLaunches = new Map();
        
        this.initializeFeatureFlags();
    }

    /**
     * Initialize Feature Flag System
     */
    async initializeFeatureFlags() {
        try {
            console.log('🚩 Initializing Feature Flag System...');
            
            await this.loadFeatureFlags();
            await this.setupEvaluationEngine();
            await this.setupRolloutManagement();
            await this.setupMonitoring();
            
            console.log('✅ Feature Flag System initialized successfully');
        } catch (error) {
            console.error('❌ Feature Flag System initialization failed:', error);
            throw error;
        }
    }

    /**
     * Load Feature Flags Configuration
     */
    async loadFeatureFlags() {
        const featureFlags = [
            {
                key: 'new-dashboard-layout',
                name: 'New Dashboard Layout',
                description: 'Updated dashboard with improved UX and performance',
                type: 'release',
                status: 'active',
                rolloutStrategy: {
                    type: 'percentage',
                    percentage: 25,
                    targetGroups: ['beta-users', 'internal-team']
                },
                targeting: {
                    rules: [
                        { attribute: 'userGroup', operator: 'in', values: ['beta-users'] },
                        { attribute: 'environment', operator: 'equals', value: 'production' }
                    ]
                },
                variations: {
                    control: { enabled: false, config: {} },
                    treatment: { enabled: true, config: { layout: 'v2', animations: true } }
                },
                createdAt: '2025-06-01T00:00:00Z',
                createdBy: 'selinay-team'
            },
            {
                key: 'advanced-analytics',
                name: 'Advanced Analytics Module',
                description: 'Enhanced analytics with real-time data processing',
                type: 'feature',
                status: 'development',
                rolloutStrategy: {
                    type: 'userGroups',
                    groups: ['power-users', 'enterprise-clients']
                },
                targeting: {
                    rules: [
                        { attribute: 'subscription', operator: 'in', values: ['enterprise', 'professional'] }
                    ]
                },
                variations: {
                    disabled: { enabled: false },
                    enabled: { 
                        enabled: true, 
                        config: { 
                            realTimeUpdates: true, 
                            advancedCharts: true,
                            dataExport: true 
                        } 
                    }
                },
                createdAt: '2025-06-03T00:00:00Z',
                createdBy: 'selinay-team'
            },
            {
                key: 'mobile-app-redesign',
                name: 'Mobile App Redesign',
                description: 'Complete mobile interface overhaul',
                type: 'experiment',
                status: 'planning',
                rolloutStrategy: {
                    type: 'a_b_testing',
                    variants: ['control', 'redesign-a', 'redesign-b'],
                    trafficSplit: [50, 25, 25]
                },
                targeting: {
                    rules: [
                        { attribute: 'platform', operator: 'in', values: ['iOS', 'Android'] }
                    ]
                },
                variations: {
                    control: { enabled: false, config: { design: 'current' } },
                    'redesign-a': { enabled: true, config: { design: 'modern', colorScheme: 'light' } },
                    'redesign-b': { enabled: true, config: { design: 'modern', colorScheme: 'dark' } }
                },
                createdAt: '2025-06-05T00:00:00Z',
                createdBy: 'selinay-team'
            }
        ];

        for (const flag of featureFlags) {
            this.flags.set(flag.key, flag);
        }

        console.log(`📋 Loaded ${featureFlags.length} feature flags`);
    }

    /**
     * Setup Evaluation Engine
     */
    async setupEvaluationEngine() {
        this.evaluationEngine = {
            evaluate: this.evaluateFlag.bind(this),
            cache: new Map(),
            cacheTTL: 300000, // 5 minutes
            metrics: {
                totalEvaluations: 0,
                cacheHits: 0,
                cacheMisses: 0
            }
        };

        console.log('⚙️ Feature flag evaluation engine configured');
    }

    /**
     * Setup Rollout Management
     */
    async setupRolloutManagement() {
        // Placeholder for rollout management setup
        console.log('🛠️ Rollout management configured');
    }

    /**
     * Setup Monitoring
     */
    async setupMonitoring() {
        // Placeholder for monitoring setup
        console.log('📊 Monitoring configured');
    }

    /**
     * Evaluate Feature Flag
     */
    async evaluateFlag(flagKey, context) {
        this.evaluationEngine.metrics.totalEvaluations++;
        
        const evaluation = {
            flagKey,
            timestamp: new Date().toISOString(),
            context,
            result: null,
            reason: null,
            cached: false
        };

        try {
            // Check cache first
            const cacheKey = this.generateCacheKey(flagKey, context);
            const cached = this.evaluationEngine.cache.get(cacheKey);
            
            if (cached && (Date.now() - cached.timestamp) < this.evaluationEngine.cacheTTL) {
                evaluation.result = cached.result;
                evaluation.reason = 'cached';
                evaluation.cached = true;
                this.evaluationEngine.metrics.cacheHits++;
                return evaluation;
            }

            this.evaluationEngine.metrics.cacheMisses++;

            const flag = this.flags.get(flagKey);
            if (!flag) {
                evaluation.result = this.getDefaultVariation(flagKey);
                evaluation.reason = 'flag_not_found';
                return evaluation;
            }

            if (flag.status === 'disabled') {
                evaluation.result = this.getDefaultVariation(flagKey);
                evaluation.reason = 'flag_disabled';
                return evaluation;
            }

            // Evaluate targeting rules
            const targetingResult = await this.evaluateTargeting(flag, context);
            if (!targetingResult.matches) {
                evaluation.result = this.getDefaultVariation(flagKey);
                evaluation.reason = 'targeting_failed';
                return evaluation;
            }

            // Apply rollout strategy
            const rolloutResult = await this.evaluateRollout(flag, context);
            evaluation.result = rolloutResult.variation;
            evaluation.reason = rolloutResult.reason;

            // Cache the result
            this.evaluationEngine.cache.set(cacheKey, {
                result: evaluation.result,
                timestamp: Date.now()
            });

            return evaluation;

        } catch (error) {
            evaluation.result = this.getDefaultVariation(flagKey);
            evaluation.reason = 'evaluation_error';
            evaluation.error = error.message;
            return evaluation;
            
        } finally {
            this.evaluationHistory.push(evaluation);
            
            // Keep only last 10000 evaluations
            if (this.evaluationHistory.length > 10000) {
                this.evaluationHistory = this.evaluationHistory.slice(-10000);
            }
        }
    }

    /**
     * Evaluate Targeting Rules
     */
    async evaluateTargeting(flag, context) {
        if (!flag.targeting || !flag.targeting.rules) {
            return { matches: true, reason: 'no_targeting_rules' };
        }

        for (const rule of flag.targeting.rules) {
            const matches = this.evaluateRule(rule, context);
            if (!matches) {
                return { matches: false, reason: `rule_failed: ${rule.attribute}` };
            }
        }

        return { matches: true, reason: 'all_rules_passed' };
    }

    /**
     * Evaluate Individual Rule
     */
    evaluateRule(rule, context) {
        const value = this.getContextValue(context, rule.attribute);
        
        switch (rule.operator) {
            case 'equals':
                return value === rule.value;
            case 'not_equals':
                return value !== rule.value;
            case 'in':
                return rule.values.includes(value);
            case 'not_in':
                return !rule.values.includes(value);
            case 'contains':
                return typeof value === 'string' && value.includes(rule.value);
            case 'starts_with':
                return typeof value === 'string' && value.startsWith(rule.value);
            case 'ends_with':
                return typeof value === 'string' && value.endsWith(rule.value);
            case 'greater_than':
                return Number(value) > Number(rule.value);
            case 'less_than':
                return Number(value) < Number(rule.value);
            default:
                return false;
        }
    }

    /**
     * Evaluate Rollout Strategy
     */
    async evaluateRollout(flag, context) {
        const strategy = flag.rolloutStrategy;
        
        switch (strategy.type) {
            case 'percentage':
                return this.evaluatePercentageRollout(flag, context, strategy);
            case 'userGroups':
                return this.evaluateUserGroupRollout(flag, context, strategy);
            case 'geographic':
                return this.evaluateGeographicRollout(flag, context, strategy);
            case 'timeBasedRollout':
                return this.evaluateTimeBasedRollout(flag, context, strategy);
            case 'a_b_testing':
                return this.evaluateABTestRollout(flag, context, strategy);
            default:
                return { variation: this.getDefaultVariation(flag.key), reason: 'unknown_strategy' };
        }
    }

    /**
     * Evaluate Percentage Rollout
     */
    evaluatePercentageRollout(flag, context, strategy) {
        const hash = this.hashContext(context, flag.key);
        const percentage = (hash % 100) + 1;
        
        if (percentage <= strategy.percentage) {
            return { 
                variation: flag.variations.treatment || flag.variations.enabled,
                reason: `percentage_rollout: ${percentage}% <= ${strategy.percentage}%`
            };
        } else {
            return { 
                variation: flag.variations.control || flag.variations.disabled,
                reason: `percentage_rollout: ${percentage}% > ${strategy.percentage}%`
            };
        }
    }

    /**
     * Evaluate A/B Test Rollout
     */
    evaluateABTestRollout(flag, context, strategy) {
        const hash = this.hashContext(context, flag.key);
        const bucket = hash % 100;
        
        let cumulativePercentage = 0;
        for (let i = 0; i < strategy.variants.length; i++) {
            cumulativePercentage += strategy.trafficSplit[i];
            if (bucket < cumulativePercentage) {
                const variantKey = strategy.variants[i];
                return {
                    variation: flag.variations[variantKey],
                    reason: `ab_test: assigned to variant ${variantKey}`
                };
            }
        }
        
        // Fallback to control
        return {
            variation: flag.variations.control,
            reason: 'ab_test: fallback to control'
        };
    }

    /**
     * Create Rollout Plan
     */
    async createRolloutPlan(flagKey, plan) {
        const rolloutPlan = {
            id: this.generateRolloutId(),
            flagKey,
            name: plan.name,
            description: plan.description,
            phases: plan.phases || [],
            currentPhase: 0,
            status: 'draft',
            startDate: plan.startDate,
            endDate: plan.endDate,
            successCriteria: plan.successCriteria || {},
            rollbackTriggers: plan.rollbackTriggers || {},
            createdAt: new Date().toISOString(),
            createdBy: plan.createdBy
        };

        this.rolloutPlans.set(rolloutPlan.id, rolloutPlan);
        console.log(`📅 Rollout plan created: ${rolloutPlan.name} (${rolloutPlan.id})`);
        
        return rolloutPlan;
    }

    /**
     * Start Rollout
     */
    async startRollout(rolloutPlanId) {
        const plan = this.rolloutPlans.get(rolloutPlanId);
        if (!plan) {
            throw new Error(`Rollout plan not found: ${rolloutPlanId}`);
        }

        plan.status = 'active';
        plan.startedAt = new Date().toISOString();
        
        const launch = {
            id: this.generateLaunchId(),
            rolloutPlanId,
            flagKey: plan.flagKey,
            currentPhase: 0,
            phaseHistory: [],
            metrics: {
                totalUsers: 0,
                exposedUsers: 0,
                conversionRate: 0,
                errorRate: 0
            },
            status: 'running'
        };

        this.activeLaunches.set(launch.id, launch);
        
        // Start first phase
        await this.executeRolloutPhase(launch, plan.phases[0]);
        
        console.log(`🚀 Rollout started: ${plan.name}`);
        return launch;
    }

    /**
     * Execute Rollout Phase
     */
    async executeRolloutPhase(launch, phase) {
        console.log(`📊 Executing rollout phase: ${phase.name}`);
        
        const flag = this.flags.get(launch.flagKey);
        if (!flag) {
            throw new Error(`Flag not found: ${launch.flagKey}`);
        }

        // Update flag configuration for this phase
        flag.rolloutStrategy.percentage = phase.percentage;
        if (phase.targetGroups) {
            flag.rolloutStrategy.targetGroups = phase.targetGroups;
        }

        const phaseExecution = {
            phase: phase.name,
            percentage: phase.percentage,
            startTime: new Date().toISOString(),
            duration: phase.duration,
            status: 'active'
        };

        launch.phaseHistory.push(phaseExecution);
        
        // Monitor phase execution
        if (phase.duration) {
            setTimeout(async () => {
                await this.completePhase(launch, phaseExecution);
            }, phase.duration * 60000); // Convert minutes to ms
        }
    }

    /**
     * Complete Rollout Phase
     */
    async completePhase(launch, phaseExecution) {
        phaseExecution.status = 'completed';
        phaseExecution.endTime = new Date().toISOString();
        
        const plan = this.rolloutPlans.get(launch.rolloutPlanId);
        const metrics = await this.gatherPhaseMetrics(launch, phaseExecution);
        
        phaseExecution.metrics = metrics;
        
        // Check success criteria
        const success = this.checkSuccessCriteria(metrics, plan.successCriteria);
        if (!success) {
            await this.initiateRollback(launch, 'success_criteria_not_met');
            return;
        }

        // Check for rollback triggers
        const rollbackTriggered = this.checkRollbackTriggers(metrics, plan.rollbackTriggers);
        if (rollbackTriggered) {
            await this.initiateRollback(launch, rollbackTriggered.reason);
            return;
        }

        // Move to next phase
        launch.currentPhase++;
        if (launch.currentPhase < plan.phases.length) {
            await this.executeRolloutPhase(launch, plan.phases[launch.currentPhase]);
        } else {
            await this.completeRollout(launch);
        }
    }

    /**
     * Initiate Rollback
     */
    async initiateRollback(launch, reason) {
        console.log(`🔄 Initiating rollback for launch ${launch.id}: ${reason}`);
        
        const flag = this.flags.get(launch.flagKey);
        if (flag) {
            // Reset to safe configuration
            flag.rolloutStrategy.percentage = 0;
            flag.status = 'disabled';
        }

        launch.status = 'rolled_back';
        launch.rollbackReason = reason;
        launch.rollbackTime = new Date().toISOString();
        
        // Send notifications
        await this.sendRollbackNotification(launch, reason);
    }

    /**
     * Get Feature Flag Status
     */
    isFeatureEnabled(flagKey, context) {
        return this.evaluateFlag(flagKey, context).then(evaluation => {
            return evaluation.result && evaluation.result.enabled === true;
        });
    }

    /**
     * Get Feature Flag Configuration
     */
    getFeatureConfig(flagKey, context) {
        return this.evaluateFlag(flagKey, context).then(evaluation => {
            return evaluation.result ? evaluation.result.config : {};
        });
    }

    /**
     * Generate Cache Key
     */
    generateCacheKey(flagKey, context) {
        const contextStr = JSON.stringify(context, Object.keys(context).sort());
        return `${flagKey}:${this.hashString(contextStr)}`;
    }

    /**
     * Hash Context for Consistent Assignment
     */
    hashContext(context, flagKey) {
        const key = context.userId || context.sessionId || context.deviceId || 'anonymous';
        return this.hashString(`${key}:${flagKey}`);
    }

    /**
     * Hash String
     */
    hashString(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            const char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convert to 32-bit integer
        }
        return Math.abs(hash);
    }

    /**
     * Get Context Value
     */
    getContextValue(context, attribute) {
        const parts = attribute.split('.');
        let value = context;
        
        for (const part of parts) {
            value = value?.[part];
        }
        
        return value;
    }

    /**
     * Get Default Variation
     */
    getDefaultVariation(flagKey) {
        return { enabled: false, config: {} };
    }

    /**
     * Generate Rollout ID
     */
    generateRolloutId() {
        return `rollout_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }

    /**
     * Generate Launch ID
     */
    generateLaunchId() {
        return `launch_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }

    /**
     * Get System Status
     */
    getStatus() {
        return {
            totalFlags: this.flags.size,
            activeRollouts: this.activeLaunches.size,
            evaluationMetrics: this.evaluationEngine.metrics,
            recentEvaluations: this.evaluationHistory.slice(-10),
            config: this.config
        };
    }
}

module.exports = FeatureFlagSystem;
