#!/usr/bin/env node

/**
 * MesChain-Sync Health Check Script
 * Checks the status of all services and connectivity
 */

const ConnectivityChecker = require('../src/utils/connectivity-check');

async function runHealthCheck() {
  console.log('🏥 MesChain-Sync Health Check Started\n');
  console.log('=' .repeat(50));
  
  const checker = new ConnectivityChecker();
  
  try {
    const results = await checker.healthCheck();
    
    console.log('\n' + '=' .repeat(50));
    console.log('📋 Summary:');
    
    let allServicesOnline = true;
    for (const [service, result] of Object.entries(results.services)) {
      if (result.status !== 'connected') {
        allServicesOnline = false;
        console.log(`⚠️  ${service} is offline - consider starting the service`);
      }
    }
    
    if (allServicesOnline) {
      console.log('✅ All services are running correctly!');
    }
    
    if (results.internet.status !== 'connected') {
      console.log('⚠️  Internet connection unavailable - working in offline mode');
    }
    
    console.log('\n🔧 Quick Commands:');
    console.log('   Start all services: npm run start:all');
    console.log('   Start main app: npm run dev');
    console.log('   Start config panel: npm run dev:config');
    console.log('   Start panel manager: npm run dev:panels');
    
    process.exit(allServicesOnline ? 0 : 1);
    
  } catch (error) {
    console.error('❌ Health check failed:', error.message);
    process.exit(1);
  }
}

// Run health check if called directly
if (require.main === module) {
  runHealthCheck();
}

module.exports = runHealthCheck; 