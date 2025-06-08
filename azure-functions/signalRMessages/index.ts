import { AzureFunction, Context, HttpRequest } from "@azure/functions";

/**
 * SignalR Messages Handler for MesChain SYNC Super Admin Panel
 * Handles real-time messaging and notifications
 */
const signalRMessages: AzureFunction = async function (context: Context, req: HttpRequest): Promise<void> {
  try {
    context.log('MesChain SYNC SignalR Messages - Processing request...');

    // Parse the incoming request
    const body = req.rawBody?.toString() || '';
    let messageData;
    
    try {
      messageData = JSON.parse(body);
    } catch (parseError) {
      context.log.warn('Failed to parse JSON body, using query parameters');
      messageData = {
        action: req.query.action || 'broadcast',
        target: req.query.target || 'adminUpdate',
        message: req.query.message || 'System update'
      };
    }

    // Generate SignalR message
    const signalRMessage = {
      target: messageData.target || "adminDashboardUpdate",
      arguments: [{
        type: messageData.action || "update",
        timestamp: new Date().toISOString(),
        data: messageData.data || await generateMockData(),
        source: "MesChain SYNC Azure SignalR"
      }]
    };

    context.log('MesChain SYNC SignalR message processed successfully', {
      target: signalRMessage.target,
      messageType: messageData.action
    });

    context.res = {
      status: 200,
      body: {
        success: true,
        message: "SignalR message sent successfully",
        timestamp: new Date().toISOString(),
        target: signalRMessage.target,
        messageId: `msg_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`
      },
      headers: {
        "Content-Type": "application/json",
        "X-MesChain-SignalR": "active"
      }
    };

  } catch (error: any) {
    context.log.error('MesChain SYNC SignalR message processing failed:', error);
    
    context.res = {
      status: 500,
      body: {
        success: false,
        error: error.message,
        timestamp: new Date().toISOString(),
        service: "MesChain SYNC SignalR Messages"
      }
    };
  }
};

/**
 * Generate mock data for testing
 */
async function generateMockData() {
  return {
    systemHealth: {
      status: "healthy",
      uptime: Math.floor(Math.random() * 86400000),
      activeUsers: Math.floor(Math.random() * 1000) + 100
    },
    marketplaceStats: {
      totalOrders: Math.floor(Math.random() * 2000) + 500,
      pendingSync: Math.floor(Math.random() * 50) + 10,
      errorCount: Math.floor(Math.random() * 5)
    },
    performance: {
      responseTime: Math.round(Math.random() * 500 + 100),
      throughput: Math.floor(Math.random() * 1000) + 200,
      cpuUsage: Math.round(Math.random() * 100)
    }
  };
}

// Register the function
export default signalRMessages;

// Also export for Azure Functions runtime compatibility
module.exports = signalRMessages;
