import { AzureFunction, Context, HttpRequest } from "@azure/functions";

/**
 * Health Check Function for MesChain-Sync Azure Functions
 * Provides health status for the entire Azure Functions application
 */
const healthCheck: AzureFunction = async function(context: Context, req: HttpRequest): Promise<void> {
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

    context.res = {
      status: 200,
      body: healthStatus,
      headers: {
        "Content-Type": "application/json",
        "X-Health-Check": "OK",
        "X-Timestamp": new Date().toISOString()
      }
    };

  } catch (error: any) {
    context.log.error('Health check failed:', error);
    
    context.res = {
      status: 500,
      body: {
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
};

export default healthCheck;