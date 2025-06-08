import { AzureFunction, Context, HttpRequest } from "@azure/functions";
import * as jwt from 'jsonwebtoken';

/**
 * SignalR Negotiation Function for MesChain-Sync Enterprise
 * Handles client connection negotiation with role-based hub assignment
 * Integrates with MesChain SYNC Super Admin panel for real-time updates
 */
const negotiate: AzureFunction = async function (context: Context, req: HttpRequest): Promise<void> {
  try {
    // Extract authorization token
    const authHeader = req.headers.authorization;
    const token = authHeader?.split(' ')[1];
    
    if (!token) {
      context.res = {
        status: 401,
        body: { error: 'Authorization token required' }
      };
      return;
    }

    // Verify JWT token
    const jwtSecret = process.env.JWT_SECRET || 'MesChain-Enterprise-JWT-Secret-2025-Secure-Key';
    let payload: any;
    
    try {
      payload = jwt.verify(token, jwtSecret);
    } catch (err) {
      context.res = {
        status: 401,
        body: { error: 'Invalid authorization token' }
      };
      return;
    }

    // Determine user role and hub assignment
    const userRole = payload.role || 'user';
    const userId = payload.sub || payload.userId || 'anonymous';
    
    // Hub assignment based on user role
    let hubName = 'MesChainSyncHub';
    let groups: string[] = [];
    
    switch (userRole) {
      case 'super_admin':
        hubName = 'MesChainSyncSuperAdminHub';
        groups = ['SuperAdmins', 'AllUsers'];
        break;
      case 'admin':
        hubName = 'MesChainSyncAdminHub';
        groups = ['Admins', 'AllUsers'];
        break;
      case 'marketplace_manager':
        hubName = 'MesChainSyncMarketplaceHub';
        groups = ['MarketplaceManagers', 'AllUsers'];
        break;
      case 'analytics_viewer':
        hubName = 'MesChainSyncAnalyticsHub';
        groups = ['AnalyticsViewers', 'AllUsers'];
        break;
      default:
        groups = ['AllUsers'];
    }

    // Add user-specific group
    groups.push(`User_${userId}`);

    // SignalR connection info
    const signalREndpoint = process.env.AZURE_SIGNALR_CONNECTION_STRING?.split(';')[0].replace('Endpoint=', '');
    const connectionInfo = {
      url: signalREndpoint,
      accessToken: generateSignalRAccessToken(userId, hubName, groups),
      availableTransports: [
        {
          transport: 'WebSockets',
          transferFormats: ['Text', 'Binary']
        },
        {
          transport: 'ServerSentEvents',
          transferFormats: ['Text']
        }
      ]
    };

    context.log(`SignalR negotiation successful for user ${userId} with role ${userRole}`);
    
    context.res = {
      status: 200,
      headers: {
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
        'Access-Control-Allow-Headers': 'Authorization, Content-Type'
      },
      body: connectionInfo
    };

  } catch (error: any) {
    context.log.error('SignalR negotiation failed:', error);
    
    context.res = {
      status: 500,
      body: { error: 'SignalR negotiation failed' }
    };
  }
};

/**
 * Generate SignalR access token with user claims
 */
function generateSignalRAccessToken(userId: string, hubName: string, groups: string[]): string {
  const signalRSecret = process.env.SIGNALR_SECRET || 'MesChain-SignalR-Secret-2025-Enterprise';
  
  const claims = {
    userId,
    hubName,
    groups,
    iat: Math.floor(Date.now() / 1000),
    exp: Math.floor(Date.now() / 1000) + (60 * 60) // 1 hour expiry
  };

  return jwt.sign(claims, signalRSecret);
}

export default negotiate;

// Also export for Azure Functions runtime compatibility
module.exports = negotiate;