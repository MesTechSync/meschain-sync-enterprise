/**
 * SignalR Proxy Server for MesChain SYNC Super Admin Panel
 * Temporary solution to test admin panel while Azure Functions are being deployed
 */

const express = require('express');
const cors = require('cors');
const jwt = require('jsonwebtoken');
const app = express();
const PORT = 3099;

// Middleware
app.use(cors());
app.use(express.json());

// Environment variables
const SIGNALR_CONNECTION_STRING = "Endpoint=https://signalr-meschain-prod.service.signalr.net;AccessKey=ETOfjQRIdSWOL9k84xUXkJQll7FF9h78FJdoIwJ4BGV8dK0nXYpLJQQJ99BFAC5RqLJXJ3w3AAAAASRSbrnu;Version=1.0;";
const JWT_SECRET = "MesChain-Enterprise-JWT-Secret-2025-Secure-Key";
const SIGNALR_SECRET = "MesChain-SignalR-Secret-2025-Enterprise";

/**
 * SignalR Negotiate Endpoint
 */
app.post('/api/negotiate', (req, res) => {
    try {
        console.log('🔌 SignalR negotiate request received');
        
        // Extract user info from headers
        const userId = req.headers['x-user-id'] || 'super-admin';
        const userRole = req.headers['x-user-role'] || 'super_admin';
        
        // Hub assignment based on user role
        let hubName = 'MesChainSyncSuperAdminHub';
        let groups = ['SuperAdmins', 'AllUsers'];
        
        if (userRole === 'super_admin') {
            hubName = 'MesChainSyncSuperAdminHub';
            groups = ['SuperAdmins', 'AllUsers'];
        }
        
        // Add user-specific group
        groups.push(`User_${userId}`);
        
        // Generate SignalR access token
        const accessToken = generateSignalRAccessToken(userId, hubName, groups);
        
        // Connection info
        const connectionInfo = {
            url: 'https://signalr-meschain-prod.service.signalr.net/client',
            accessToken: accessToken,
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
        
        console.log(`✅ SignalR negotiation successful for user ${userId} with role ${userRole}`);
        
        res.json(connectionInfo);
        
    } catch (error) {
        console.error('❌ SignalR negotiation failed:', error);
        res.status(500).json({ error: 'SignalR negotiation failed' });
    }
});

/**
 * Health Check Endpoint
 */
app.get('/api/health', (req, res) => {
    res.json({
        success: true,
        service: 'MesChain SYNC SignalR Proxy',
        timestamp: new Date().toISOString(),
        signalr_status: 'connected',
        environment: 'development'
    });
});

/**
 * Admin Dashboard Update Endpoint
 */
app.get('/api/adminDashboardUpdater', (req, res) => {
    const dashboardData = {
        timestamp: new Date().toISOString(),
        system: generateSystemMetrics(),
        marketplaces: generateMarketplaceMetrics(),
        analytics: generateAnalyticsMetrics(),
        atom: generateAtomMetrics(),
        realtime: generateRealtimeMetrics()
    };
    
    res.json({
        success: true,
        timestamp: dashboardData.timestamp,
        data: dashboardData,
        version: "1.0.0",
        environment: "development"
    });
});

/**
 * Generate SignalR access token with user claims
 */
function generateSignalRAccessToken(userId, hubName, groups) {
    const claims = {
        userId,
        hubName,
        groups,
        iat: Math.floor(Date.now() / 1000),
        exp: Math.floor(Date.now() / 1000) + (60 * 60) // 1 hour expiry
    };
    
    return jwt.sign(claims, SIGNALR_SECRET);
}

/**
 * Generate mock system metrics
 */
function generateSystemMetrics() {
    return {
        timestamp: new Date().toISOString(),
        performance: {
            cpu_usage: Math.round(Math.random() * 100 * 100) / 100,
            memory_usage: Math.round(Math.random() * 100 * 100) / 100,
            disk_usage: Math.round(Math.random() * 80 * 100) / 100,
            network_io: {
                bytes_sent: Math.floor(Math.random() * 1000000),
                bytes_received: Math.floor(Math.random() * 1000000)
            }
        },
        connections: {
            active_sessions: Math.floor(Math.random() * 1000) + 100,
            total_connections: Math.floor(Math.random() * 2000) + 500,
            peak_connections: Math.floor(Math.random() * 3000) + 1000
        },
        health: {
            status: 'healthy',
            uptime: Math.floor(Math.random() * 86400000),
            response_time: Math.round(Math.random() * 500 * 100) / 100
        },
        azure: {
            signalr_status: 'connected',
            function_app_status: 'running',
            storage_status: 'available'
        }
    };
}

/**
 * Generate mock marketplace metrics
 */
function generateMarketplaceMetrics() {
    const marketplaces = ['amazon_turkey', 'trendyol', 'hepsiburada', 'gittigidiyor', 'n11', 'pazarama'];
    const metrics = {};
    
    for (const marketplace of marketplaces) {
        metrics[marketplace] = {
            active_listings: Math.floor(Math.random() * 15000) + 5000,
            pending_orders: Math.floor(Math.random() * 200) + 50,
            processed_orders: Math.floor(Math.random() * 1000) + 200,
            sync_status: Math.random() > 0.1 ? 'active' : 'pending',
            last_sync: new Date(Date.now() - Math.random() * 3600000).toISOString(),
            sync_errors: Math.floor(Math.random() * 5),
            api_rate_limit: Math.round((1 - Math.random() * 0.3) * 100),
            revenue_today: Math.round(Math.random() * 50000 * 100) / 100,
            conversion_rate: Math.round(Math.random() * 8 * 100) / 100
        };
    }
    
    return {
        summary: {
            total_listings: Object.values(metrics).reduce((sum, m) => sum + m.active_listings, 0),
            total_orders: Object.values(metrics).reduce((sum, m) => sum + m.pending_orders, 0),
            total_revenue: Object.values(metrics).reduce((sum, m) => sum + m.revenue_today, 0),
            active_marketplaces: marketplaces.length,
            sync_health: Math.random() > 0.2 ? 'excellent' : 'good'
        },
        marketplaces: metrics
    };
}

/**
 * Generate mock analytics metrics
 */
function generateAnalyticsMetrics() {
    return {
        revenue: {
            today: Math.round(Math.random() * 150000 * 100) / 100,
            this_week: Math.round(Math.random() * 800000 * 100) / 100,
            this_month: Math.round(Math.random() * 3000000 * 100) / 100,
            growth_rate: Math.round((Math.random() * 20 + 5) * 100) / 100
        },
        orders: {
            today: Math.floor(Math.random() * 2000) + 500,
            pending: Math.floor(Math.random() * 300) + 100,
            processing: Math.floor(Math.random() * 200) + 50,
            completed: Math.floor(Math.random() * 1500) + 300,
            cancelled: Math.floor(Math.random() * 50) + 10
        },
        products: {
            total_active: Math.floor(Math.random() * 50000) + 25000,
            low_stock: Math.floor(Math.random() * 500) + 100,
            out_of_stock: Math.floor(Math.random() * 100) + 20,
            top_performers: [
                { name: 'Premium Ürün A', sales: Math.floor(Math.random() * 200) + 50 },
                { name: 'Popüler Ürün B', sales: Math.floor(Math.random() * 180) + 40 },
                { name: 'Trend Ürün C', sales: Math.floor(Math.random() * 160) + 30 }
            ]
        },
        customers: {
            total_active: Math.floor(Math.random() * 10000) + 5000,
            new_today: Math.floor(Math.random() * 100) + 20,
            returning_rate: Math.round(Math.random() * 40 + 60),
            satisfaction_score: Math.round(Math.random() * 2 + 8)
        },
        alerts: {
            critical: Math.floor(Math.random() * 5),
            warning: Math.floor(Math.random() * 15) + 5,
            info: Math.floor(Math.random() * 30) + 10,
            total: 0
        }
    };
}

/**
 * Generate mock ATOM system metrics
 */
function generateAtomMetrics() {
    return {
        modules: {
            M001_foundation: { status: 'active', performance: 98.5 },
            M002_marketplace: { status: 'active', performance: 97.2 },
            M003_inventory: { status: 'active', performance: 99.1 },
            M004_analytics: { status: 'active', performance: 96.8 },
            M005_automation: { status: 'active', performance: 98.9 },
            M006_integration: { status: 'active', performance: 97.7 },
            M007_advanced: { status: 'active', performance: 99.3 }
        },
        sync_engine: {
            status: 'running',
            threads: Math.floor(Math.random() * 20) + 10,
            queue_size: Math.floor(Math.random() * 1000) + 200,
            throughput: Math.floor(Math.random() * 500) + 100,
            error_rate: Math.round(Math.random() * 2 * 100) / 100
        },
        ai_engine: {
            status: 'active',
            ml_models: 15,
            predictions_today: Math.floor(Math.random() * 10000) + 5000,
            accuracy_rate: Math.round(Math.random() * 5 + 95),
            processing_time: Math.round(Math.random() * 200 + 50)
        }
    };
}

/**
 * Generate mock realtime metrics
 */
function generateRealtimeMetrics() {
    return {
        signalr: {
            connected_clients: Math.floor(Math.random() * 500) + 100,
            messages_sent: Math.floor(Math.random() * 10000) + 2000,
            messages_received: Math.floor(Math.random() * 8000) + 1500,
            connection_quality: Math.round(Math.random() * 10 + 90)
        },
        websocket: {
            active_connections: Math.floor(Math.random() * 300) + 50,
            data_transfer: Math.floor(Math.random() * 1000000) + 500000,
            latency: Math.round(Math.random() * 50 + 10)
        },
        api: {
            requests_per_minute: Math.floor(Math.random() * 1000) + 200,
            average_response_time: Math.round(Math.random() * 300 + 100),
            success_rate: Math.round(Math.random() * 5 + 95),
            rate_limit_usage: Math.round(Math.random() * 30 + 20)
        }
    };
}

// Start server
app.listen(PORT, () => {
    console.log(`🚀 MesChain SYNC SignalR Proxy Server running on port ${PORT}`);
    console.log(`🔌 SignalR Negotiate: http://localhost:${PORT}/api/negotiate`);
    console.log(`💓 Health Check: http://localhost:${PORT}/api/health`);
    console.log(`📊 Dashboard Data: http://localhost:${PORT}/api/adminDashboardUpdater`);
    console.log(`📡 SignalR Endpoint: https://signalr-meschain-prod.service.signalr.net`);
    console.log(`✅ Server is ready to accept connections`);
}).on('error', (err) => {
    console.error('❌ Server error:', err);
});

// Keep the process alive
process.on('uncaughtException', (err) => {
    console.error('❌ Uncaught exception:', err);
});

process.on('unhandledRejection', (reason, promise) => {
    console.error('❌ Unhandled rejection at:', promise, 'reason:', reason);
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('🛑 SignalR Proxy Server shutting down gracefully...');
    process.exit(0);
});

process.on('SIGINT', () => {
    console.log('🛑 SignalR Proxy Server interrupted, shutting down...');
    process.exit(0);
});
