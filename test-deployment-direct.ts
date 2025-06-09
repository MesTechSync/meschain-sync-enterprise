// Direct test of quickDeploy function
import { quickDeploy } from './src/deployment/DropshippingOptimizationDeployer';

console.log('🚀 Starting MezBjen Dropshipping Optimization Test...');

async function testDeployment() {
  try {
    console.log('📋 Calling quickDeploy function...');
    const result = await quickDeploy();
    
    console.log('\n📊 MezBjen Deployment Results:');
    console.log('='.repeat(50));
    console.log(`✅ Success: ${result.success}`);
    console.log(`⚡ Performance Improvement: ${result.performanceImprovement.toFixed(2)}%`);
    console.log(`🎯 Target Achieved (40%+): ${result.targetAchieved ? 'YES' : 'NO'}`);
    console.log(`⏱️ Deployment Time: ${result.deploymentTime}ms`);
    console.log(`🔄 Rollback Executed: ${result.rollbackExecuted}`);
    
    if (result.errors.length > 0) {
      console.log('\n❌ Errors:');
      result.errors.forEach(error => console.log(`   - ${error}`));
    }
    
    if (result.recommendations.length > 0) {
      console.log('\n💡 Recommendations:');
      result.recommendations.forEach(rec => console.log(`   - ${rec}`));
    }
    
    if (result.dashboardUrl) {
      console.log(`\n📊 Dashboard: ${result.dashboardUrl}`);
    }
    
    console.log('\n' + '='.repeat(50));
    console.log(result.targetAchieved ? '🎉 SUCCESS: 40%+ Performance Target ACHIEVED!' : '❌ FAILED: Performance target not reached');
    
    process.exit(result.targetAchieved ? 0 : 1);
    
  } catch (error) {
    console.error('💥 Deployment test failed:', error);
    process.exit(1);
  }
}

testDeployment();
