import http from 'k6/http';
import { sleep, check } from 'k6';
import { Counter, Rate, Trend } from 'k6/metrics';
import { randomIntBetween } from 'https://jslib.k6.io/k6-utils/1.2.0/index.js';

// Custom metrics
const marketplaceRequests = new Counter('marketplace_requests');
const failedRequests = new Rate('failed_requests');
const marketplaceSyncTime = new Trend('marketplace_sync_time');
const apiResponseTime = new Trend('api_response_time');

// Test configuration
export const options = {
  scenarios: {
    // Common traffic simulation
    average_load: {
      executor: 'ramping-vus',
      startVUs: 5,
      stages: [
        { duration: '1m', target: 20 },  // Ramp up to 20 virtual users over 1 minute
        { duration: '3m', target: 20 },  // Stay at 20 VUs for 3 minutes
        { duration: '1m', target: 0 },   // Ramp down to 0 VUs over 1 minute
      ],
      gracefulRampDown: '30s',
    },
    // Peak traffic simulation
    peak_hour: {
      executor: 'constant-arrival-rate',
      rate: 100,                         // 100 iterations per minute
      timeUnit: '1m',                    // 1 minute
      duration: '2m',                    // for 2 minutes total
      preAllocatedVUs: 50,               // Pre-allocate 50 VUs
      maxVUs: 100,
    },
    // Stress test
    stress_test: {
      executor: 'ramping-arrival-rate',
      startRate: 50,
      timeUnit: '1m',
      stages: [
        { duration: '2m', target: 200 }, // Ramp up to 200 requests per minute over 2 minutes
        { duration: '1m', target: 200 }, // Stay at 200 requests per minute for 1 minute
        { duration: '30s', target: 0 },  // Ramp down to 0 requests over 30 seconds
      ],
      preAllocatedVUs: 100,
      maxVUs: 200,
    },
  },
  thresholds: {
    http_req_duration: ['p(95)<500'], // 95% of requests must finish within 500ms
    failed_requests: ['rate<0.05'],   // Error rate must be less than 5%
    marketplace_sync_time: ['p(95)<2000'], // 95% of marketplace syncs must complete within 2s
    api_response_time: ['avg<300'],   // Average API response time under 300ms
  },
};

// API endpoints to test
const API_BASE_URL = __ENV.API_URL || 'https://api.atom-c017.com';
const ENDPOINTS = {
  marketplaceStatus: '/api/marketplaces/status',
  marketplaceSync: '/api/marketplaces/sync',
  health: '/health',
  analytics: '/api/analytics/summary',
  products: '/api/products',
  orders: '/api/orders',
};

// Authentication header
const AUTH_HEADER = {
  headers: {
    'Authorization': `Bearer ${__ENV.API_TOKEN || 'k6-load-test-token'}`,
    'Content-Type': 'application/json',
  },
};

// Helper function to simulate realistic marketplace ID selection
function getRandomMarketplaceId() {
  const marketplaces = ['trendyol', 'amazon', 'n11', 'hepsiburada', 'ciceksepeti'];
  return marketplaces[Math.floor(Math.random() * marketplaces.length)];
}

// Main test function
export default function() {
  // Test 1: Health Check
  const healthCheck = http.get(`${API_BASE_URL}${ENDPOINTS.health}`);
  
  check(healthCheck, {
    'health endpoint status is 200': (r) => r.status === 200,
    'health endpoint contains status:up': (r) => r.json('status') === 'UP',
  });
  
  // Record metrics
  apiResponseTime.add(healthCheck.timings.duration);
  failedRequests.add(healthCheck.status !== 200);
  
  // Test 2: Get Marketplace Status (Read-only)
  const marketplaceStatusUrl = `${API_BASE_URL}${ENDPOINTS.marketplaceStatus}`;
  const marketplaceStatus = http.get(marketplaceStatusUrl, AUTH_HEADER);
  
  check(marketplaceStatus, {
    'marketplace status status is 200': (r) => r.status === 200,
    'marketplace status returns data': (r) => r.json('marketplaces') !== undefined,
  });
  
  // Record metrics
  apiResponseTime.add(marketplaceStatus.timings.duration);
  failedRequests.add(marketplaceStatus.status !== 200);
  marketplaceRequests.add(1);
  
  // Test 3: Get Products List with Pagination and Filtering
  const productsUrl = `${API_BASE_URL}${ENDPOINTS.products}?page=1&limit=25&sort=price&order=desc`;
  const products = http.get(productsUrl, AUTH_HEADER);
  
  check(products, {
    'products endpoint status is 200': (r) => r.status === 200,
    'products contain pagination info': (r) => r.json('pagination') !== undefined,
    'products contain items': (r) => r.json('items').length > 0,
  });
  
  // Record metrics
  apiResponseTime.add(products.timings.duration);
  failedRequests.add(products.status !== 200);
  
  // Test 4: Analytics Summary (Authenticated)
  const analyticsUrl = `${API_BASE_URL}${ENDPOINTS.analytics}`;
  const analytics = http.get(analyticsUrl, AUTH_HEADER);
  
  check(analytics, {
    'analytics endpoint status is 200': (r) => r.status === 200,
    'analytics contains data': (r) => Object.keys(r.json()).length > 0,
  });
  
  // Record metrics
  apiResponseTime.add(analytics.timings.duration);
  failedRequests.add(analytics.status !== 200);
  
  // Test 5: Marketplace Sync (Write operation, more expensive)
  // Only run this test for 10% of iterations to avoid overloading
  if (Math.random() < 0.1) {
    const marketplaceId = getRandomMarketplaceId();
    const syncUrl = `${API_BASE_URL}${ENDPOINTS.marketplaceSync}/${marketplaceId}`;
    const syncPayload = JSON.stringify({
      full: false,
      entities: ['products', 'orders'],
    });
    
    const startTime = new Date();
    const syncResult = http.post(syncUrl, syncPayload, AUTH_HEADER);
    const syncDuration = new Date() - startTime;
    
    check(syncResult, {
      'sync endpoint status is 202': (r) => r.status === 202,
      'sync response contains job_id': (r) => r.json('job_id') !== undefined,
    });
    
    // Record metrics
    marketplaceSyncTime.add(syncDuration);
    apiResponseTime.add(syncResult.timings.duration);
    failedRequests.add(syncResult.status !== 202);
    marketplaceRequests.add(1);
  }
  
  // Add random sleep time between 1-5 seconds to simulate real user behavior
  sleep(randomIntBetween(1, 5));
}

// Lifecycle hooks
export function setup() {
  // Perform setup tasks before the main test
  console.log('Starting load test with API URL:', API_BASE_URL);

  // Verify API is available before starting the test
  const healthCheck = http.get(`${API_BASE_URL}${ENDPOINTS.health}`);
  
  if (healthCheck.status !== 200) {
    throw new Error(`API health check failed with status ${healthCheck.status}`);
  }
  
  return { startTime: new Date() };
}

export function teardown(data) {
  // Log test duration
  const testDuration = (new Date() - data.startTime) / 1000;
  console.log(`Load test completed in ${testDuration.toFixed(2)} seconds`);
  
  // Export test results summary to stdout (will be captured by CI)
  console.log('==== TEST SUMMARY ====');
  console.log(`- Total duration: ${testDuration.toFixed(2)}s`);
  console.log(`- Test completed at: ${new Date().toISOString()}`);
}
