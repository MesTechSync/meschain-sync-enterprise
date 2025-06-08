const { app } = require('@azure/functions');

// Health Check Function
app.http('health', {
    methods: ['GET'],
    authLevel: 'anonymous',
    handler: async (request, context) => {
        context.log('Health check endpoint called');
        
        return {
            status: 200,
            jsonBody: {
                status: 'healthy',
                timestamp: new Date().toISOString(),
                service: 'MesChain-Sync Enterprise SignalR Functions',
                version: '1.0.0'
            }
        };
    }
});

// Test function  
app.http('test', {
    methods: ['GET'],
    authLevel: 'anonymous',
    handler: async (request, context) => {
        return {
            status: 200,
            jsonBody: { 
                message: 'Test successful',
                timestamp: new Date().toISOString()
            }
        };
    }
});

// SignalR Negotiate Function
app.http('negotiate', {
    methods: ['POST', 'GET'],
    authLevel: 'anonymous',
    handler: async (request, context) => {
        context.log('SignalR negotiate endpoint called');
        
        const userId = request.query.get('userId') || 'anonymous';
        const userRole = request.query.get('userRole') || 'user';
        
        return {
            status: 200,
            jsonBody: {
                url: 'https://your-signalr-service.service.signalr.net',
                accessToken: 'mock-token-' + Date.now(),
                userId: userId,
                userRole: userRole
            }
        };
    }
});

// Admin Dashboard Updater Function
app.http('adminDashboardUpdater', {
    methods: ['POST', 'GET'],
    authLevel: 'anonymous',
    handler: async (request, context) => {
        context.log('Admin dashboard updater called');
        
        const dashboardData = {
            totalUsers: 1247,
            activeConnections: 89,
            systemHealth: 'healthy',
            lastUpdate: new Date().toISOString(),
            marketplaces: {
                trendyol: { status: 'active', orders: 45, sync: 'ok' },
                amazon: { status: 'active', orders: 23, sync: 'ok' },
                hepsiburada: { status: 'active', orders: 67, sync: 'ok' },
                n11: { status: 'active', orders: 12, sync: 'ok' }
            },
            metrics: {
                totalOrders: 147,
                successRate: 98.5,
                avgResponseTime: 245,
                errorCount: 2
            }
        };
        
        return {
            status: 200,
            jsonBody: {
                success: true,
                data: dashboardData,
                timestamp: new Date().toISOString()
            }
        };
    }
});

// SignalR Messages Handler Function
app.http('signalRMessages', {
    methods: ['POST'],
    authLevel: 'anonymous',
    handler: async (request, context) => {
        context.log('SignalR messages handler called');
        
        let requestData = {};
        try {
            const text = await request.text();
            requestData = text ? JSON.parse(text) : {};
        } catch (error) {
            context.log('Failed to parse request body:', error);
        }
        
        const messageType = requestData.type || 'general';
        const messageData = requestData.data || {};
        
        const processedMessage = {
            type: messageType,
            data: messageData,
            timestamp: new Date().toISOString(),
            processed: true
        };
        
        return {
            status: 200,
            jsonBody: {
                success: true,
                messageId: 'msg_' + Date.now(),
                processedMessage: processedMessage,
                timestamp: new Date().toISOString()
            }
        };
    }
});

module.exports = app;
