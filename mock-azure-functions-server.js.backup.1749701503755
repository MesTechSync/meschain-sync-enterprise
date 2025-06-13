const express = require('express');
const cors = require('cors');

const app = express();
const PORT = 7071; // Azure Functions default port

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Mock SignalR connection info for development
const mockSignalRConnection = {
    url: 'wss://meschain-signalr.service.signalr.net',
    accessToken: 'mock-access-token-' + Date.now(),
    expires: new Date(Date.now() + 3600000).toISOString() // 1 hour from now
};

// Health Check Endpoint
app.get('/api/health', (req, res) => {
    console.log('📊 Health check called');
    res.json({
        status: 'healthy',
        timestamp: new Date().toISOString(),
        service: 'MesChain-Sync Enterprise SignalR Functions (Mock)',
        version: '1.0.0',
        nodeVersion: process.version
    });
});

// Test Endpoint
app.get('/api/test', (req, res) => {
    console.log('🧪 Test endpoint called');
    res.json({
        message: 'Test successful',
        timestamp: new Date().toISOString(),
        service: 'Mock Azure Functions'
    });
});

// SignalR Negotiate Endpoint
app.post('/api/negotiate', (req, res) => {
    console.log('🔗 SignalR negotiate called (POST)');
    const userId = req.query.userId || (req.body && req.body.userId) || 'anonymous';
    const userRole = req.query.userRole || (req.body && req.body.userRole) || 'user';
    
    console.log(`User: ${userId}, Role: ${userRole}`);
    
    res.json({
        url: mockSignalRConnection.url,
        accessToken: mockSignalRConnection.accessToken,
        userId: userId,
        userRole: userRole,
        expires: mockSignalRConnection.expires,
        hubName: 'MesChainHub'
    });
});

// SignalR Negotiate Endpoint (GET version)
app.get('/api/negotiate', (req, res) => {
    console.log('🔗 SignalR negotiate called (GET)');
    const userId = req.query.userId || 'anonymous';
    const userRole = req.query.userRole || 'user';
    
    console.log(`User: ${userId}, Role: ${userRole}`);
    
    res.json({
        url: mockSignalRConnection.url,
        accessToken: mockSignalRConnection.accessToken,
        userId: userId,
        userRole: userRole,
        expires: mockSignalRConnection.expires,
        hubName: 'MesChainHub'
    });
});

// Admin Dashboard Update Endpoint (POST)
app.post('/api/adminDashboardUpdater', (req, res) => {
    console.log('📊 Admin dashboard update called (POST)');
    
    const mockData = {
        totalOrders: Math.floor(Math.random() * 1000) + 500,
        totalRevenue: Math.floor(Math.random() * 50000) + 10000,
        activeUsers: Math.floor(Math.random() * 200) + 50,
        systemStatus: 'operational',
        lastUpdate: new Date().toISOString(),
        marketplaces: {
            amazon: { orders: Math.floor(Math.random() * 100), status: 'connected' },
            ebay: { orders: Math.floor(Math.random() * 100), status: 'connected' },
            trendyol: { orders: Math.floor(Math.random() * 100), status: 'connected' },
            n11: { orders: Math.floor(Math.random() * 100), status: 'connected' }
        }
    };
    
    res.json({
        success: true,
        data: mockData,
        timestamp: new Date().toISOString(),
        signalRNotification: 'Dashboard updated via SignalR'
    });
});

// Admin Dashboard Get Endpoint (GET)
app.get('/api/adminDashboardUpdater', (req, res) => {
    console.log('📊 Admin dashboard data requested (GET)');
    
    const mockData = {
        system: {
            performance: {
                cpu_usage: (85 + Math.random() * 10).toFixed(1),
                memory_usage: (70 + Math.random() * 20).toFixed(1),
                disk_usage: (45 + Math.random() * 15).toFixed(1)
            },
            status: 'operational',
            uptime: Math.floor(Math.random() * 86400) + 3600
        },
        marketplace: {
            summary: {
                total_orders: Math.floor(Math.random() * 5000) + 15000,
                total_revenue: Math.floor(Math.random() * 100000) + 50000,
                active_listings: Math.floor(Math.random() * 1000) + 2000
            },
            platforms: {
                amazon: { orders: Math.floor(Math.random() * 100) + 50, status: 'connected', revenue: Math.floor(Math.random() * 20000) + 10000 },
                trendyol: { orders: Math.floor(Math.random() * 150) + 75, status: 'connected', revenue: Math.floor(Math.random() * 15000) + 8000 },
                n11: { orders: Math.floor(Math.random() * 80) + 40, status: 'connected', revenue: Math.floor(Math.random() * 12000) + 6000 },
                hepsiburada: { orders: Math.floor(Math.random() * 120) + 60, status: 'connected', revenue: Math.floor(Math.random() * 18000) + 9000 }
            }
        },
        analytics: {
            revenue: {
                today: Math.floor(Math.random() * 50000) + 100000,
                yesterday: Math.floor(Math.random() * 45000) + 95000,
                week: Math.floor(Math.random() * 300000) + 600000,
                month: Math.floor(Math.random() * 1200000) + 2400000
            },
            users: {
                active_today: Math.floor(Math.random() * 1000) + 2500,
                new_today: Math.floor(Math.random() * 100) + 50,
                total_registered: Math.floor(Math.random() * 50000) + 100000
            }
        },
        realtime: {
            signalr: {
                connected_clients: Math.floor(Math.random() * 1000) + 2500,
                active_connections: Math.floor(Math.random() * 100) + 200,
                messages_per_minute: Math.floor(Math.random() * 500) + 100
            },
            api: {
                requests_per_minute: Math.floor(Math.random() * 500000) + 1000000,
                response_time_avg: (50 + Math.random() * 100).toFixed(1),
                error_rate: (Math.random() * 2).toFixed(2)
            }
        }
    };
    
    res.json({
        success: true,
        data: mockData,
        timestamp: new Date().toISOString(),
        source: 'mock_api',
        version: '1.0.0'
    });
});

// Marketplace Sync Handler
app.post('/api/marketplaceSyncHandler', (req, res) => {
    console.log('🛒 Marketplace sync handler called');
    const { marketplace, action, data } = req.body;
    
    console.log(`Marketplace: ${marketplace}, Action: ${action}`);
    
    const mockResponse = {
        success: true,
        marketplace: marketplace || 'unknown',
        action: action || 'sync',
        processed: Math.floor(Math.random() * 50) + 1,
        timestamp: new Date().toISOString(),
        signalRNotification: `${marketplace} sync completed`
    };
    
    res.json(mockResponse);
});

// SignalR Messages Handler
app.post('/api/signalRMessages', (req, res) => {
    console.log('💬 SignalR messages handler called');
    const { target, message, groupName } = req.body;
    
    console.log(`Target: ${target}, Group: ${groupName}, Message: ${JSON.stringify(message)}`);
    
    res.json({
        success: true,
        messagesSent: 1,
        target: target || 'broadcast',
        groupName: groupName || 'default',
        timestamp: new Date().toISOString()
    });
});

// Error handling
app.use((err, req, res, next) => {
    console.error('❌ Error:', err);
    res.status(500).json({
        error: 'Internal Server Error',
        message: err.message,
        timestamp: new Date().toISOString()
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`🚀 MesChain-Sync Mock Azure Functions running on port ${PORT}`);
    console.log(`📡 Health check: http://localhost:${PORT}/api/health`);
    console.log(`🧪 Test endpoint: http://localhost:${PORT}/api/test`);
    console.log(`🔗 SignalR negotiate: http://localhost:${PORT}/api/negotiate`);
    console.log(`⚡ Node.js version: ${process.version}`);
    console.log(`🕐 Started at: ${new Date().toISOString()}`);
});

// Graceful shutdown
process.on('SIGINT', () => {
    console.log('\n🛑 Shutting down Mock Azure Functions...');
    process.exit(0);
});

process.on('SIGTERM', () => {
    console.log('\n🛑 Shutting down Mock Azure Functions...');
    process.exit(0);
});
