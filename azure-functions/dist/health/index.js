"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const functions_1 = require("@azure/functions");
/**
 * Health Check Function for MesChain-Sync Azure Functions
 * Provides health status for the entire Azure Functions application
 */
async function healthCheck(request, context) {
    try {
        context.log('Health check requested');
        // Perform basic health checks
        const healthStatus = {
            status: 'healthy',
            timestamp: new Date().toISOString(),
            service: 'MesChain-Sync Azure Functions',
            version: '1.0.0',
            environment: process.env.AZURE_FUNCTIONS_ENVIRONMENT || 'production',
            checks: {
                memory: {
                    status: 'healthy',
                    usage: process.memoryUsage()
                },
                uptime: {
                    status: 'healthy',
                    seconds: process.uptime()
                },
                signalr: {
                    status: process.env.SignalRConnectionString ? 'configured' : 'not-configured'
                },
                functions: {
                    status: 'healthy',
                    available: ['health', 'negotiate', 'adminDashboardUpdater', 'signalRMessages']
                }
            },
            metadata: {
                nodeVersion: process.version,
                platform: process.platform,
                architecture: process.arch
            }
        };
        return {
            status: 200,
            jsonBody: healthStatus,
            headers: {
                "Content-Type": "application/json",
                "X-Health-Check": "OK",
                "X-Timestamp": new Date().toISOString()
            }
        };
    }
    catch (error) {
        context.error('Health check failed:', error);
        return {
            status: 500,
            jsonBody: {
                status: 'unhealthy',
                timestamp: new Date().toISOString(),
                service: 'MesChain-Sync Azure Functions',
                error: error.message,
                checks: {
                    application: 'failed'
                }
            }
        };
    }
}
// Register the function
functions_1.app.http('health', {
    methods: ['GET'],
    authLevel: 'anonymous',
    handler: healthCheck
});
//# sourceMappingURL=index.js.map