/**
 * @file index.js
 * @description Main entry point for Secure API Gateway
 * @version 1.0.0
 * @author Cursor AI Team
 * @date June 13, 2025
 */

require('dotenv').config();
const GatewayCore = require('./gateway_core');
const configureGatewayRoutes = require('./gateway_routes');

/**
 * Main function to start API Gateway
 */
async function startGateway() {
  try {
    console.log('🚀 Starting Secure API Gateway...');
    
    // Load environment variables
    const port = process.env.API_GATEWAY_PORT || 3000;
    const environment = process.env.NODE_ENV || 'production';
    
    // Configure Redis
    const redisOptions = {
      url: process.env.REDIS_URL || 'redis://localhost:6379',
      username: process.env.REDIS_USERNAME || '',
      password: process.env.REDIS_PASSWORD || ''
    };
    
    // Initialize Gateway Core
    const gateway = new GatewayCore({
      port,
      environment,
      redisOptions,
      tokenSecret: process.env.TOKEN_SECRET,
      keyPath: process.env.JWT_KEY_PATH || './keys',
      issuer: process.env.JWT_ISSUER || 'meschain-api-gateway',
      audience: process.env.JWT_AUDIENCE || 'meschain-clients',
      defaultRateLimit: parseInt(process.env.DEFAULT_RATE_LIMIT || '100'),
      rateWindow: parseInt(process.env.RATE_WINDOW || '60'),
      meshType: process.env.SERVICE_MESH_TYPE || 'istio',
      serviceName: process.env.SERVICE_NAME || 'api-gateway',
      namespace: process.env.NAMESPACE || 'default'
    });
    
    // Initialize core components
    await gateway.initialize();
    
    // Configure routes
    configureGatewayRoutes(gateway, {
      serviceProxies: {
        'user-service': '/api/users',
        'product-service': '/api/products',
        'order-service': '/api/orders',
        'payment-service': '/api/payments',
        'inventory-service': '/api/inventory',
        'notification-service': '/api/notifications'
      }
    });
    
    // Start the gateway server
    await gateway.start();
    
    console.log(`
    =======================================================
    🔐 SECURE API GATEWAY RUNNING
    =======================================================
    Environment: ${environment}
    Port: ${port}
    Routes: 
     - Health: http://localhost:${port}/health
     - Metrics: http://localhost:${port}/metrics
     - OAuth: http://localhost:${port}/api/oauth/*
     - Services: http://localhost:${port}/api/services/status
    =======================================================
    `);
  } catch (error) {
    console.error('❌ Failed to start API Gateway:', error);
    process.exit(1);
  }
}

// Handle process termination
process.on('SIGINT', async () => {
  console.log('Shutting down API Gateway...');
  process.exit(0);
});

// Handle uncaught exceptions
process.on('uncaughtException', (error) => {
  console.error('Uncaught exception:', error);
  process.exit(1);
});

// Handle unhandled promise rejections
process.on('unhandledRejection', (reason, promise) => {
  console.error('Unhandled Rejection at:', promise, 'reason:', reason);
  process.exit(1);
});

// Start the gateway
startGateway();
