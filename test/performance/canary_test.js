/**
 * ATOM-C017 Canary Test Suite
 * 
 * This script performs canary testing on newly deployed environments before
 * they receive production traffic. Tests critical paths and functionalities
 * to ensure the deployment is ready for production traffic.
 */

const axios = require('axios');
const yargs = require('yargs/yargs');
const { hideBin } = require('yargs/helpers');

// Parse command-line arguments
const argv = yargs(hideBin(process.argv))
  .option('url', {
    alias: 'u',
    type: 'string',
    description: 'The base URL of the environment to test',
    demandOption: true
  })
  .option('timeout', {
    alias: 't',
    type: 'number',
    description: 'Request timeout in milliseconds',
    default: 10000
  })
  .option('verbose', {
    alias: 'v',
    type: 'boolean',
    description: 'Run with verbose logging',
    default: false
  })
  .help()
  .parse();

// Setup test configuration
const config = {
  baseUrl: argv.url,
  timeout: argv.timeout,
  verbose: argv.verbose,
  authToken: process.env.CANARY_TEST_TOKEN || 'canary-test-token',
};

// Set up axios instance
const api = axios.create({
  baseURL: config.baseUrl,
  timeout: config.timeout,
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${config.authToken}`,
    'X-Canary-Test': 'true',
  }
});

// Test result tracking
let passed = 0;
let failed = 0;
let skipped = 0;

// Core endpoints to test
const criticalEndpoints = [
  { name: 'Health Check', path: '/health', method: 'GET', 
    validate: (res) => res.status === 200 && res.data.status === 'UP' },
  { name: 'API Version', path: '/api/version', method: 'GET', 
    validate: (res) => res.status === 200 && res.data.version },
  { name: 'Marketplaces Status', path: '/api/marketplaces/status', method: 'GET', 
    validate: (res) => res.status === 200 && Array.isArray(res.data.marketplaces) },
  { name: 'Products API', path: '/api/products?limit=1', method: 'GET', 
    validate: (res) => res.status === 200 && res.data.items && res.data.pagination },
  { name: 'Orders API', path: '/api/orders?limit=1', method: 'GET', 
    validate: (res) => res.status === 200 && res.data.items && res.data.pagination },
  { name: 'Analytics Dashboard', path: '/api/analytics/summary', method: 'GET', 
    validate: (res) => res.status === 200 && res.data.salesMetrics },
  { name: 'User Authentication', path: '/api/auth/verify', method: 'POST', 
    data: { token: config.authToken }, 
    validate: (res) => res.status === 200 && res.data.valid === true },
];

// Non-critical but important endpoints (failures here won't fail the canary test)
const secondaryEndpoints = [
  { name: 'Notifications', path: '/api/notifications', method: 'GET', 
    validate: (res) => res.status === 200 },
  { name: 'User Settings', path: '/api/settings', method: 'GET', 
    validate: (res) => res.status === 200 },
  { name: 'Documentation', path: '/api/docs', method: 'GET', 
    validate: (res) => res.status === 200 },
];

// Performance thresholds (in milliseconds)
const performanceThresholds = {
  criticalEndpoint: 400,  // Critical endpoints should respond in under 400ms
  secondaryEndpoint: 800, // Secondary endpoints should respond in under 800ms
};

/**
 * Run a test against a specific endpoint
 */
async function testEndpoint(endpoint, isCritical = true) {
  const startTime = Date.now();
  const testName = `${endpoint.name} (${endpoint.path})`;
  
  try {
    // Log test start
    if (config.verbose) {
      console.log(`Testing ${testName}...`);
    }
    
    // Make request
    const response = await api({
      method: endpoint.method,
      url: endpoint.path,
      data: endpoint.data,
    });
    
    // Calculate response time
    const responseTime = Date.now() - startTime;
    
    // Validate response
    const isValid = endpoint.validate(response);
    
    // Check performance
    const performanceThreshold = isCritical ? 
      performanceThresholds.criticalEndpoint : 
      performanceThresholds.secondaryEndpoint;
    
    const performancePassed = responseTime <= performanceThreshold;
    
    // Determine if test passed
    if (isValid && performancePassed) {
      passed++;
      console.log(`✅ PASS: ${testName} - ${responseTime}ms`);
    } else {
      if (!isValid) {
        console.error(`❌ FAIL: ${testName} - Invalid response`);
        if (config.verbose) {
          console.error('Response:', JSON.stringify(response.data, null, 2));
        }
      } else {
        console.error(`⚠️ SLOW: ${testName} - ${responseTime}ms (threshold: ${performanceThreshold}ms)`);
      }
      
      // For critical endpoints, failures count towards the overall test result
      if (isCritical) {
        failed++;
      } else {
        // For secondary endpoints, just log the issue but don't fail the test
        console.log(`Note: ${testName} issue would not block deployment`);
      }
    }
    
  } catch (error) {
    failed += isCritical ? 1 : 0;
    console.error(`❌ ERROR: ${testName} - ${error.message}`);
    
    if (config.verbose && error.response) {
      console.error('Status:', error.response.status);
      console.error('Response:', JSON.stringify(error.response.data, null, 2));
    }
  }
}

/**
 * Run all canary tests
 */
async function runTests() {
  console.log(`🚀 Starting canary tests against ${config.baseUrl}`);
  console.log('===============================================');
  
  // Run critical endpoint tests
  console.log('\n🔴 Testing Critical Endpoints:');
  for (const endpoint of criticalEndpoints) {
    await testEndpoint(endpoint, true);
  }
  
  // Run secondary endpoint tests
  console.log('\n🔵 Testing Secondary Endpoints:');
  for (const endpoint of secondaryEndpoints) {
    await testEndpoint(endpoint, false);
  }
  
  // Print summary
  console.log('\n===============================================');
  console.log('📊 SUMMARY:');
  console.log(`✅ Passed: ${passed}`);
  console.log(`❌ Failed: ${failed}`);
  console.log(`⏩ Skipped: ${skipped}`);
  
  // Determine overall test status - only critical endpoints affect the result
  if (failed > 0) {
    console.log('\n❌ CANARY TEST FAILED');
    process.exit(1);
  } else {
    console.log('\n✅ CANARY TEST PASSED');
    process.exit(0);
  }
}

// Run the test suite
runTests().catch(error => {
  console.error('Canary test suite failed:', error);
  process.exit(1);
});
