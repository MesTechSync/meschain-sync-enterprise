// Simple import test
console.log('🔍 Testing imports...');

try {
  console.log('📦 Importing DropshippingPerformanceOptimizer...');
  const { DropshippingPerformanceOptimizer } = require('./src/optimization/DropshippingPerformanceOptimizer');
  console.log('✅ DropshippingPerformanceOptimizer imported successfully');

  console.log('📦 Importing MarketplaceIntegrationService...');
  const { MarketplaceIntegrationService } = require('./src/integration/MarketplaceIntegrationService');
  console.log('✅ MarketplaceIntegrationService imported successfully');

  console.log('📦 Importing PerformanceDashboard...');
  const PerformanceDashboard = require('./src/monitoring/PerformanceDashboard').default;
  console.log('✅ PerformanceDashboard imported successfully');

  console.log('📦 Importing DropshippingPerformanceTestSuite...');
  const DropshippingPerformanceTestSuite = require('./src/testing/DropshippingPerformanceTestSuite').default;
  console.log('✅ DropshippingPerformanceTestSuite imported successfully');

  console.log('📦 Importing DropshippingOptimizationDeployer...');
  const { DropshippingOptimizationDeployer, quickDeploy } = require('./src/deployment/DropshippingOptimizationDeployer');
  console.log('✅ DropshippingOptimizationDeployer imported successfully');

  console.log('\n🎉 All imports successful! Starting deployment...\n');
  
  // Now run the actual deployment
  quickDeploy().then(result => {
    console.log('🚀 Deployment completed with result:', result);
  }).catch(error => {
    console.error('❌ Deployment failed:', error);
  });

} catch (error) {
  console.error('💥 Import failed:', error);
}
