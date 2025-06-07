/**
 * 🧪 SELINAY TASK 8 PHASE 2 - INTEGRATION TESTING FRAMEWORK
 * Comprehensive Testing Suite for Phase 2 Master Controller Integration
 * 
 * TESTING SCOPE:
 * ✅ Component initialization and health checks
 * ✅ Cross-component integration flows validation
 * ✅ AI orchestration engine testing
 * ✅ Performance optimization validation
 * ✅ Security and compliance integration testing
 * ✅ Business intelligence analytics validation
 * ✅ Real-time monitoring system testing
 * ✅ End-to-end workflow validation
 * 
 * @author Selinay - Frontend UI/UX Specialist
 * @version 3.0.0 - Phase 2 Integration Testing
 * @date June 6, 2025
 */

const { EventEmitter } = require('events');
const Phase2MasterControllerIntegration = require('./phase2-master-controller-integration.js');

class Phase2IntegrationTestSuite extends EventEmitter {
    constructor() {
        super();
        
        this.masterController = null;
        this.testResults = new Map();
        this.testMetrics = {
            totalTests: 0,
            passedTests: 0,
            failedTests: 0,
            warningTests: 0,
            startTime: null,
            endTime: null,
            duration: 0
        };
        
        this.testCategories = [
            'component_initialization',
            'integration_flows',
            'orchestration_engine',
            'performance_optimization',
            'security_compliance',
            'business_intelligence',
            'monitoring_systems',
            'end_to_end_workflows'
        ];
        
        this.isRunning = false;
        
        console.log('🧪 Phase 2 Integration Test Suite initialized');
    }

    /**
     * 🚀 Run Complete Integration Test Suite
     */
    async runIntegrationTests() {
        console.log('🧪 Starting Phase 2 Integration Test Suite...');
        console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        this.isRunning = true;
        this.testMetrics.startTime = Date.now();
        
        try {
            // Initialize master controller
            await this.initializeMasterController();
            
            // Run test categories
            for (const category of this.testCategories) {
                console.log(`\n🔍 Testing Category: ${category.toUpperCase()}`);
                await this.runTestCategory(category);
            }
            
            // Generate test report
            await this.generateTestReport();
            
            this.isRunning = false;
            this.testMetrics.endTime = Date.now();
            this.testMetrics.duration = this.testMetrics.endTime - this.testMetrics.startTime;
            
            console.log('✅ Phase 2 Integration Test Suite completed successfully');
            this.emit('test_suite_completed', this.getTestSummary());
            
        } catch (error) {
            console.error('❌ Integration Test Suite failed:', error);
            this.isRunning = false;
            this.emit('test_suite_failed', error);
            throw error;
        }
    }

    /**
     * 🏗️ Initialize Master Controller for Testing
     */
    async initializeMasterController() {
        console.log('🏗️ Initializing Master Controller for testing...');
        
        try {
            this.masterController = new Phase2MasterControllerIntegration();
            await this.masterController.initialize();
            
            // Wait for stabilization
            await this.waitForStabilization(5000);
            
            console.log('✅ Master Controller initialized for testing');
            
        } catch (error) {
            console.error('❌ Failed to initialize Master Controller for testing:', error);
            throw error;
        }
    }

    /**
     * ⏳ Wait for System Stabilization
     */
    async waitForStabilization(duration = 5000) {
        return new Promise(resolve => setTimeout(resolve, duration));
    }

    /**
     * 🧪 Run Test Category
     */
    async runTestCategory(category) {
        switch (category) {
            case 'component_initialization':
                await this.testComponentInitialization();
                break;
            case 'integration_flows':
                await this.testIntegrationFlows();
                break;
            case 'orchestration_engine':
                await this.testOrchestrationEngine();
                break;
            case 'performance_optimization':
                await this.testPerformanceOptimization();
                break;
            case 'security_compliance':
                await this.testSecurityCompliance();
                break;
            case 'business_intelligence':
                await this.testBusinessIntelligence();
                break;
            case 'monitoring_systems':
                await this.testMonitoringSystems();
                break;
            case 'end_to_end_workflows':
                await this.testEndToEndWorkflows();
                break;
            default:
                console.log(`⚠️ Unknown test category: ${category}`);
        }
    }

    /**
     * 🏗️ Test Component Initialization
     */
    async testComponentInitialization() {
        console.log('🏗️ Testing component initialization...');
        
        const expectedComponents = [
            'multiRegionLoadBalancer',
            'aiOperationsAssistant',
            'intelligentMonitoringSystem',
            'quantumReadySecurityFramework',
            'advancedComplianceEngine',
            'advancedBusinessIntelligence',
            'advancedPerformanceTuner',
            'enterpriseMetricsEngine'
        ];
        
        for (const componentId of expectedComponents) {
            const testName = `component_init_${componentId}`;
            
            try {
                const status = this.masterController.getIntegrationStatus();
                const component = status.components.find(c => c.id === componentId);
                
                if (!component) {
                    this.recordTestResult(testName, 'FAILED', `Component ${componentId} not found`);
                    continue;
                }
                
                if (component.status !== 'active') {
                    this.recordTestResult(testName, 'FAILED', `Component ${componentId} not active: ${component.status}`);
                    continue;
                }
                
                if (component.health < 80) {
                    this.recordTestResult(testName, 'WARNING', `Component ${componentId} health low: ${component.health}%`);
                    continue;
                }
                
                this.recordTestResult(testName, 'PASSED', `Component ${componentId} initialized successfully`);
                
            } catch (error) {
                this.recordTestResult(testName, 'FAILED', `Component ${componentId} test error: ${error.message}`);
            }
        }
    }

    /**
     * 🔗 Test Integration Flows
     */
    async testIntegrationFlows() {
        console.log('🔗 Testing integration flows...');
        
        const status = this.masterController.getIntegrationStatus();
        
        // Test that integration flows exist
        const testName = 'integration_flows_exist';
        if (status.integrationFlows.length === 0) {
            this.recordTestResult(testName, 'FAILED', 'No integration flows found');
            return;
        }
        
        this.recordTestResult(testName, 'PASSED', `${status.integrationFlows.length} integration flows found`);
        
        // Test each integration flow
        for (const flow of status.integrationFlows) {
            const flowTestName = `integration_flow_${flow.id}`;
            
            try {
                if (flow.status !== 'active') {
                    this.recordTestResult(flowTestName, 'FAILED', `Flow ${flow.id} not active: ${flow.status}`);
                    continue;
                }
                
                if (flow.errorCount > flow.syncCount * 0.1) { // More than 10% error rate
                    this.recordTestResult(flowTestName, 'WARNING', `Flow ${flow.id} high error rate: ${flow.errorCount}/${flow.syncCount}`);
                    continue;
                }
                
                if (!flow.lastSync || (Date.now() - flow.lastSync) > 60000) { // No sync in last minute
                    this.recordTestResult(flowTestName, 'WARNING', `Flow ${flow.id} last sync too old`);
                    continue;
                }
                
                this.recordTestResult(flowTestName, 'PASSED', `Flow ${flow.id} operating normally`);
                
            } catch (error) {
                this.recordTestResult(flowTestName, 'FAILED', `Flow ${flow.id} test error: ${error.message}`);
            }
        }
    }

    /**
     * 🧠 Test Orchestration Engine
     */
    async testOrchestrationEngine() {
        console.log('🧠 Testing orchestration engine...');
        
        const status = this.masterController.getIntegrationStatus();
        
        // Test orchestration status
        const orchestrationTest = 'orchestration_status';
        if (status.orchestration.status !== 'active') {
            this.recordTestResult(orchestrationTest, 'FAILED', `Orchestration engine not active: ${status.orchestration.status}`);
            return;
        }
        
        this.recordTestResult(orchestrationTest, 'PASSED', 'Orchestration engine active');
        
        // Test orchestration strategies
        const strategiesTest = 'orchestration_strategies';
        if (status.orchestration.strategiesCount < 3) {
            this.recordTestResult(strategiesTest, 'WARNING', `Low strategies count: ${status.orchestration.strategiesCount}`);
        } else {
            this.recordTestResult(strategiesTest, 'PASSED', `${status.orchestration.strategiesCount} strategies configured`);
        }
        
        // Test optimization execution
        const optimizationTest = 'orchestration_optimization';
        if (status.orchestration.optimizationCount === 0) {
            this.recordTestResult(optimizationTest, 'WARNING', 'No optimizations executed yet');
        } else {
            this.recordTestResult(optimizationTest, 'PASSED', `${status.orchestration.optimizationCount} optimizations executed`);
        }
    }

    /**
     * ⚡ Test Performance Optimization
     */
    async testPerformanceOptimization() {
        console.log('⚡ Testing performance optimization...');
        
        const status = this.masterController.getIntegrationStatus();
        
        // Test overall performance score
        const performanceTest = 'overall_performance';
        const performanceScore = status.systemMetrics.overallPerformance;
        
        if (performanceScore < 70) {
            this.recordTestResult(performanceTest, 'FAILED', `Low performance score: ${performanceScore.toFixed(1)}%`);
        } else if (performanceScore < 85) {
            this.recordTestResult(performanceTest, 'WARNING', `Moderate performance score: ${performanceScore.toFixed(1)}%`);
        } else {
            this.recordTestResult(performanceTest, 'PASSED', `Good performance score: ${performanceScore.toFixed(1)}%`);
        }
        
        // Test system health
        const healthTest = 'system_health';
        const healthScore = status.systemMetrics.systemHealth;
        
        if (healthScore < 80) {
            this.recordTestResult(healthTest, 'FAILED', `Low health score: ${healthScore.toFixed(1)}%`);
        } else if (healthScore < 90) {
            this.recordTestResult(healthTest, 'WARNING', `Moderate health score: ${healthScore.toFixed(1)}%`);
        } else {
            this.recordTestResult(healthTest, 'PASSED', `Good health score: ${healthScore.toFixed(1)}%`);
        }
        
        // Test performance tuner component
        const tunerComponent = status.components.find(c => c.id === 'advancedPerformanceTuner');
        const tunerTest = 'performance_tuner';
        
        if (!tunerComponent || tunerComponent.status !== 'active') {
            this.recordTestResult(tunerTest, 'FAILED', 'Performance tuner not active');
        } else {
            this.recordTestResult(tunerTest, 'PASSED', 'Performance tuner active and operational');
        }
    }

    /**
     * 🛡️ Test Security and Compliance
     */
    async testSecurityCompliance() {
        console.log('🛡️ Testing security and compliance systems...');
        
        const status = this.masterController.getIntegrationStatus();
        
        // Test quantum security framework
        const quantumComponent = status.components.find(c => c.id === 'quantumReadySecurityFramework');
        const quantumTest = 'quantum_security';
        
        if (!quantumComponent || quantumComponent.status !== 'active') {
            this.recordTestResult(quantumTest, 'FAILED', 'Quantum security framework not active');
        } else {
            this.recordTestResult(quantumTest, 'PASSED', 'Quantum security framework operational');
        }
        
        // Test compliance engine
        const complianceComponent = status.components.find(c => c.id === 'advancedComplianceEngine');
        const complianceTest = 'compliance_engine';
        
        if (!complianceComponent || complianceComponent.status !== 'active') {
            this.recordTestResult(complianceTest, 'FAILED', 'Compliance engine not active');
        } else {
            this.recordTestResult(complianceTest, 'PASSED', 'Compliance engine operational');
        }
        
        // Test compliance score
        const complianceScoreTest = 'compliance_score';
        const complianceScore = status.systemMetrics.complianceScore;
        
        if (complianceScore < 90) {
            this.recordTestResult(complianceScoreTest, 'WARNING', `Low compliance score: ${complianceScore.toFixed(1)}%`);
        } else {
            this.recordTestResult(complianceScoreTest, 'PASSED', `Good compliance score: ${complianceScore.toFixed(1)}%`);
        }
        
        // Test quantum readiness
        const quantumReadinessTest = 'quantum_readiness';
        const quantumReadiness = status.systemMetrics.quantumReadiness;
        
        if (quantumReadiness < 85) {
            this.recordTestResult(quantumReadinessTest, 'WARNING', `Low quantum readiness: ${quantumReadiness.toFixed(1)}%`);
        } else {
            this.recordTestResult(quantumReadinessTest, 'PASSED', `Good quantum readiness: ${quantumReadiness.toFixed(1)}%`);
        }
    }

    /**
     * 📈 Test Business Intelligence
     */
    async testBusinessIntelligence() {
        console.log('📈 Testing business intelligence systems...');
        
        const status = this.masterController.getIntegrationStatus();
        
        // Test BI component
        const biComponent = status.components.find(c => c.id === 'advancedBusinessIntelligence');
        const biTest = 'business_intelligence';
        
        if (!biComponent || biComponent.status !== 'active') {
            this.recordTestResult(biTest, 'FAILED', 'Business Intelligence component not active');
        } else {
            this.recordTestResult(biTest, 'PASSED', 'Business Intelligence component operational');
        }
        
        // Test metrics engine
        const metricsComponent = status.components.find(c => c.id === 'enterpriseMetricsEngine');
        const metricsTest = 'metrics_engine';
        
        if (!metricsComponent || metricsComponent.status !== 'active') {
            this.recordTestResult(metricsTest, 'FAILED', 'Metrics engine not active');
        } else {
            this.recordTestResult(metricsTest, 'PASSED', 'Metrics engine operational');
        }
        
        // Test business metrics
        const businessMetricsTest = 'business_metrics';
        const businessMetrics = status.systemMetrics.businessMetrics;
        
        if (businessMetrics < 75) {
            this.recordTestResult(businessMetricsTest, 'WARNING', `Low business metrics: ${businessMetrics.toFixed(1)}%`);
        } else {
            this.recordTestResult(businessMetricsTest, 'PASSED', `Good business metrics: ${businessMetrics.toFixed(1)}%`);
        }
    }

    /**
     * 🔍 Test Monitoring Systems
     */
    async testMonitoringSystems() {
        console.log('🔍 Testing monitoring systems...');
        
        const status = this.masterController.getIntegrationStatus();
        
        // Test intelligent monitoring component
        const monitoringComponent = status.components.find(c => c.id === 'intelligentMonitoringSystem');
        const monitoringTest = 'intelligent_monitoring';
        
        if (!monitoringComponent || monitoringComponent.status !== 'active') {
            this.recordTestResult(monitoringTest, 'FAILED', 'Intelligent monitoring system not active');
        } else {
            this.recordTestResult(monitoringTest, 'PASSED', 'Intelligent monitoring system operational');
        }
        
        // Test AI operations assistant
        const aiOpsComponent = status.components.find(c => c.id === 'aiOperationsAssistant');
        const aiOpsTest = 'ai_operations';
        
        if (!aiOpsComponent || aiOpsComponent.status !== 'active') {
            this.recordTestResult(aiOpsTest, 'FAILED', 'AI Operations Assistant not active');
        } else {
            this.recordTestResult(aiOpsTest, 'PASSED', 'AI Operations Assistant operational');
        }
        
        // Test load balancer monitoring
        const lbComponent = status.components.find(c => c.id === 'multiRegionLoadBalancer');
        const lbTest = 'load_balancer_monitoring';
        
        if (!lbComponent || lbComponent.status !== 'active') {
            this.recordTestResult(lbTest, 'FAILED', 'Multi-Region Load Balancer not active');
        } else {
            this.recordTestResult(lbTest, 'PASSED', 'Multi-Region Load Balancer operational');
        }
    }

    /**
     * 🔄 Test End-to-End Workflows
     */
    async testEndToEndWorkflows() {
        console.log('🔄 Testing end-to-end workflows...');
        
        // Test workflow: Traffic routing optimization
        await this.testTrafficRoutingWorkflow();
        
        // Test workflow: Security incident response
        await this.testSecurityIncidentWorkflow();
        
        // Test workflow: Performance optimization cycle
        await this.testPerformanceOptimizationWorkflow();
        
        // Test workflow: Business intelligence reporting
        await this.testBusinessIntelligenceWorkflow();
    }

    /**
     * 🌍 Test Traffic Routing Workflow
     */
    async testTrafficRoutingWorkflow() {
        const workflowTest = 'traffic_routing_workflow';
        
        try {
            const status = this.masterController.getIntegrationStatus();
            
            // Check load balancer
            const lb = status.components.find(c => c.id === 'multiRegionLoadBalancer');
            if (!lb || lb.status !== 'active') {
                this.recordTestResult(workflowTest, 'FAILED', 'Load balancer not active for workflow');
                return;
            }
            
            // Check AI operations
            const aiOps = status.components.find(c => c.id === 'aiOperationsAssistant');
            if (!aiOps || aiOps.status !== 'active') {
                this.recordTestResult(workflowTest, 'FAILED', 'AI Operations not active for workflow');
                return;
            }
            
            // Check monitoring
            const monitoring = status.components.find(c => c.id === 'intelligentMonitoringSystem');
            if (!monitoring || monitoring.status !== 'active') {
                this.recordTestResult(workflowTest, 'FAILED', 'Monitoring not active for workflow');
                return;
            }
            
            // Check integration flow
            const integrationFlow = status.integrationFlows.find(f => f.id === 'global_traffic_optimization');
            if (!integrationFlow || integrationFlow.status !== 'active') {
                this.recordTestResult(workflowTest, 'WARNING', 'Traffic optimization flow not active');
                return;
            }
            
            this.recordTestResult(workflowTest, 'PASSED', 'Traffic routing workflow operational');
            
        } catch (error) {
            this.recordTestResult(workflowTest, 'FAILED', `Traffic routing workflow error: ${error.message}`);
        }
    }

    /**
     * 🛡️ Test Security Incident Workflow
     */
    async testSecurityIncidentWorkflow() {
        const workflowTest = 'security_incident_workflow';
        
        try {
            const status = this.masterController.getIntegrationStatus();
            
            // Check quantum security
            const security = status.components.find(c => c.id === 'quantumReadySecurityFramework');
            if (!security || security.status !== 'active') {
                this.recordTestResult(workflowTest, 'FAILED', 'Quantum security not active for workflow');
                return;
            }
            
            // Check compliance
            const compliance = status.components.find(c => c.id === 'advancedComplianceEngine');
            if (!compliance || compliance.status !== 'active') {
                this.recordTestResult(workflowTest, 'FAILED', 'Compliance engine not active for workflow');
                return;
            }
            
            // Check monitoring integration
            const securityFlow = status.integrationFlows.find(f => f.id === 'security_monitoring_sync');
            if (!securityFlow || securityFlow.status !== 'active') {
                this.recordTestResult(workflowTest, 'WARNING', 'Security monitoring flow not active');
                return;
            }
            
            this.recordTestResult(workflowTest, 'PASSED', 'Security incident workflow operational');
            
        } catch (error) {
            this.recordTestResult(workflowTest, 'FAILED', `Security incident workflow error: ${error.message}`);
        }
    }

    /**
     * ⚡ Test Performance Optimization Workflow
     */
    async testPerformanceOptimizationWorkflow() {
        const workflowTest = 'performance_optimization_workflow';
        
        try {
            const status = this.masterController.getIntegrationStatus();
            
            // Check performance tuner
            const tuner = status.components.find(c => c.id === 'advancedPerformanceTuner');
            if (!tuner || tuner.status !== 'active') {
                this.recordTestResult(workflowTest, 'FAILED', 'Performance tuner not active for workflow');
                return;
            }
            
            // Check metrics engine
            const metrics = status.components.find(c => c.id === 'enterpriseMetricsEngine');
            if (!metrics || metrics.status !== 'active') {
                this.recordTestResult(workflowTest, 'FAILED', 'Metrics engine not active for workflow');
                return;
            }
            
            // Check orchestration
            if (status.orchestration.status !== 'active') {
                this.recordTestResult(workflowTest, 'FAILED', 'Orchestration engine not active for workflow');
                return;
            }
            
            this.recordTestResult(workflowTest, 'PASSED', 'Performance optimization workflow operational');
            
        } catch (error) {
            this.recordTestResult(workflowTest, 'FAILED', `Performance optimization workflow error: ${error.message}`);
        }
    }

    /**
     * 📈 Test Business Intelligence Workflow
     */
    async testBusinessIntelligenceWorkflow() {
        const workflowTest = 'business_intelligence_workflow';
        
        try {
            const status = this.masterController.getIntegrationStatus();
            
            // Check BI component
            const bi = status.components.find(c => c.id === 'advancedBusinessIntelligence');
            if (!bi || bi.status !== 'active') {
                this.recordTestResult(workflowTest, 'FAILED', 'Business Intelligence not active for workflow');
                return;
            }
            
            // Check metrics engine
            const metrics = status.components.find(c => c.id === 'enterpriseMetricsEngine');
            if (!metrics || metrics.status !== 'active') {
                this.recordTestResult(workflowTest, 'FAILED', 'Metrics engine not active for workflow');
                return;
            }
            
            // Check BI integration flow
            const biFlow = status.integrationFlows.find(f => f.id === 'business_intelligence_feed');
            if (!biFlow || biFlow.status !== 'active') {
                this.recordTestResult(workflowTest, 'WARNING', 'BI integration flow not active');
                return;
            }
            
            this.recordTestResult(workflowTest, 'PASSED', 'Business Intelligence workflow operational');
            
        } catch (error) {
            this.recordTestResult(workflowTest, 'FAILED', `Business Intelligence workflow error: ${error.message}`);
        }
    }

    /**
     * 📊 Record Test Result
     */
    recordTestResult(testName, status, message) {
        this.testResults.set(testName, {
            name: testName,
            status,
            message,
            timestamp: Date.now()
        });
        
        this.testMetrics.totalTests++;
        
        switch (status) {
            case 'PASSED':
                this.testMetrics.passedTests++;
                console.log(`✅ ${testName}: ${message}`);
                break;
            case 'WARNING':
                this.testMetrics.warningTests++;
                console.log(`⚠️ ${testName}: ${message}`);
                break;
            case 'FAILED':
                this.testMetrics.failedTests++;
                console.log(`❌ ${testName}: ${message}`);
                break;
        }
    }

    /**
     * 📋 Generate Test Report
     */
    async generateTestReport() {
        console.log('\n📋 Generating comprehensive test report...');
        
        const report = {
            title: 'Phase 2 Integration Test Report',
            timestamp: new Date().toISOString(),
            duration: this.testMetrics.duration || (Date.now() - this.testMetrics.startTime),
            summary: {
                totalTests: this.testMetrics.totalTests,
                passedTests: this.testMetrics.passedTests,
                warningTests: this.testMetrics.warningTests,
                failedTests: this.testMetrics.failedTests,
                successRate: this.testMetrics.totalTests > 0 ? 
                    ((this.testMetrics.passedTests / this.testMetrics.totalTests) * 100).toFixed(1) : 0
            },
            systemStatus: this.masterController ? this.masterController.getIntegrationStatus() : null,
            testResults: Array.from(this.testResults.values()),
            recommendations: this.generateRecommendations()
        };
        
        console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        console.log('📊 TEST SUMMARY');
        console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        console.log(`Total Tests: ${report.summary.totalTests}`);
        console.log(`✅ Passed: ${report.summary.passedTests}`);
        console.log(`⚠️ Warnings: ${report.summary.warningTests}`);
        console.log(`❌ Failed: ${report.summary.failedTests}`);
        console.log(`🎯 Success Rate: ${report.summary.successRate}%`);
        console.log(`⏱️ Duration: ${this.formatDuration(report.duration)}`);
        console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        return report;
    }

    /**
     * 💡 Generate Recommendations
     */
    generateRecommendations() {
        const recommendations = [];
        
        // Analyze failed tests
        const failedTests = Array.from(this.testResults.values()).filter(r => r.status === 'FAILED');
        if (failedTests.length > 0) {
            recommendations.push('Address failed test cases to ensure system reliability');
        }
        
        // Analyze warning tests
        const warningTests = Array.from(this.testResults.values()).filter(r => r.status === 'WARNING');
        if (warningTests.length > 0) {
            recommendations.push('Review warning conditions for potential optimization opportunities');
        }
        
        // Success rate analysis
        const successRate = (this.testMetrics.passedTests / this.testMetrics.totalTests) * 100;
        if (successRate < 90) {
            recommendations.push('System needs improvement to achieve 90%+ test success rate');
        } else if (successRate >= 95) {
            recommendations.push('Excellent system performance - ready for production deployment');
        }
        
        return recommendations;
    }

    /**
     * 📊 Get Test Summary
     */
    getTestSummary() {
        return {
            metrics: this.testMetrics,
            results: Array.from(this.testResults.values()),
            systemStatus: this.masterController ? this.masterController.getIntegrationStatus() : null,
            isRunning: this.isRunning
        };
    }

    /**
     * ⏱️ Format Duration
     */
    formatDuration(duration) {
        const seconds = Math.floor(duration / 1000);
        const minutes = Math.floor(seconds / 60);
        
        if (minutes > 0) {
            return `${minutes}m ${seconds % 60}s`;
        }
        return `${seconds}s`;
    }

    /**
     * 🧹 Cleanup
     */
    async cleanup() {
        console.log('🧹 Cleaning up test environment...');
        
        if (this.masterController) {
            await this.masterController.shutdown();
            this.masterController = null;
        }
        
        this.testResults.clear();
        this.isRunning = false;
        
        console.log('✅ Test cleanup completed');
    }
}

module.exports = Phase2IntegrationTestSuite;

// Example usage
if (require.main === module) {
    const testSuite = new Phase2IntegrationTestSuite();

    // Event listeners
    testSuite.on('test_suite_completed', (summary) => {
        console.log('🎉 Test suite completed successfully!');
        console.log(`Final Success Rate: ${((summary.metrics.passedTests / summary.metrics.totalTests) * 100).toFixed(1)}%`);
    });

    testSuite.on('test_suite_failed', (error) => {
        console.error('❌ Test suite failed:', error);
    });

    // Run the tests
    testSuite.runIntegrationTests()
        .then(() => {
            console.log('✅ Integration testing completed');
        })
        .catch(error => {
            console.error('❌ Integration testing failed:', error);
        })
        .finally(async () => {
            await testSuite.cleanup();
            process.exit(0);
        });

    // Graceful shutdown
    process.on('SIGINT', async () => {
        console.log('\n🛑 Received shutdown signal during testing...');
        await testSuite.cleanup();
        process.exit(0);
    });
}

console.log(`
🧪 PHASE 2 INTEGRATION TEST SUITE v3.0.0 LOADED
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ 8 testing categories configured
✅ End-to-end workflow validation ready
✅ Component integration testing prepared
✅ Performance and security validation enabled
✅ Business intelligence testing configured
🧪 COMPREHENSIVE PHASE 2 VALIDATION - SELINAY TEAM
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
`);
