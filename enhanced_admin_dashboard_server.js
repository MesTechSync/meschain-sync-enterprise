const express = require('express');
const path = require('path');
const fs = require('fs');
const cors = require('cors');
const WebSocket = require('ws');

const app = express();
const PORT = 3025;

// Middleware
app.use(cors({
    origin: ['http://localhost:3025', 'http://localhost:3000', 'http://localhost:3023'],
    credentials: true
}));
app.use(express.json());
app.use(express.static(__dirname));

// Enhanced API endpoints for the dashboard
app.get('/api/system/health', (req, res) => {
    const systemHealth = {
        status: 'healthy',
        uptime: process.uptime(),
        memory: process.memoryUsage(),
        cpu: process.cpuUsage(),
        timestamp: new Date().toISOString(),
        services: {
            active: 23,
            total: 26,
            health_score: 98.7
        }
    };
    
    res.json(systemHealth);
});

app.get('/api/services/status', async (req, res) => {
    const services = [
        { port: 3000, name: 'Main Dashboard', status: 'active', cpu: Math.floor(Math.random() * 30) + 30, memory: Math.floor(Math.random() * 40) + 40 },
        { port: 3002, name: 'Admin Panel', status: 'active', cpu: Math.floor(Math.random() * 25) + 25, memory: Math.floor(Math.random() * 35) + 45 },
        { port: 3004, name: 'Performance Monitor', status: 'active', cpu: Math.floor(Math.random() * 20) + 25, memory: Math.floor(Math.random() * 30) + 40 },
        { port: 3005, name: 'Product Management', status: 'active', cpu: Math.floor(Math.random() * 35) + 30, memory: Math.floor(Math.random() * 40) + 45 },
        { port: 3006, name: 'Order Management', status: 'active', cpu: Math.floor(Math.random() * 30) + 25, memory: Math.floor(Math.random() * 45) + 50 },
        { port: 3007, name: 'Inventory System', status: 'active', cpu: Math.floor(Math.random() * 32) + 28, memory: Math.floor(Math.random() * 38) + 42 },
        { port: 3023, name: 'Super Admin Panel', status: 'active', cpu: Math.floor(Math.random() * 28) + 22, memory: Math.floor(Math.random() * 33) + 37 },
        { port: 8080, name: 'API Gateway', status: 'active', cpu: Math.floor(Math.random() * 45) + 35, memory: Math.floor(Math.random() * 50) + 55 }
    ];
    
    res.json({
        success: true,
        data: services,
        total: services.length,
        active: services.filter(s => s.status === 'active').length
    });
});

app.get('/api/metrics/realtime', (req, res) => {
    const metrics = {
        timestamp: new Date().toISOString(),
        system: {
            cpu_usage: Math.floor(Math.random() * 30) + 40,
            memory_usage: Math.floor(Math.random() * 25) + 60,
            disk_usage: Math.floor(Math.random() * 20) + 45,
            network_io: Math.floor(Math.random() * 100) + 200
        },
        business: {
            active_users: Math.floor(Math.random() * 200) + 1100,
            revenue_today: Math.floor(Math.random() * 10000) + 40000,
            orders_count: Math.floor(Math.random() * 50) + 120,
            conversion_rate: (Math.random() * 2 + 8).toFixed(2)
        },
        marketplace: {
            trendyol: { status: 'active', orders: Math.floor(Math.random() * 20) + 45 },
            amazon: { status: 'active', orders: Math.floor(Math.random() * 15) + 30 },
            n11: { status: 'active', orders: Math.floor(Math.random() * 10) + 20 },
            hepsiburada: { status: 'active', orders: Math.floor(Math.random() * 12) + 25 }
        }
    };
    
    res.json({
        success: true,
        data: metrics
    });
});

app.get('/api/analytics/performance', (req, res) => {
    const hours = Array.from({length: 24}, (_, i) => `${i.toString().padStart(2, '0')}:00`);
    const cpuData = hours.map(() => Math.floor(Math.random() * 30) + 40);
    const memoryData = hours.map(() => Math.floor(Math.random() * 25) + 60);
    const networkData = hours.map(() => Math.floor(Math.random() * 50) + 100);
    
    res.json({
        success: true,
        data: {
            labels: hours,
            datasets: [
                {
                    label: 'CPU Usage',
                    data: cpuData,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)'
                },
                {
                    label: 'Memory Usage',
                    data: memoryData,
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)'
                },
                {
                    label: 'Network I/O',
                    data: networkData,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)'
                }
            ]
        }
    });
});

app.get('/api/analytics/revenue', (req, res) => {
    const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    const revenueData = days.map(() => Math.floor(Math.random() * 20000) + 15000);
    
    res.json({
        success: true,
        data: {
            labels: days,
            datasets: [{
                label: 'Daily Revenue',
                data: revenueData,
                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                borderColor: '#3b82f6'
            }]
        }
    });
});

app.post('/api/services/:port/restart', (req, res) => {
    const port = req.params.port;
    
    // Simulate service restart
    setTimeout(() => {
        res.json({
            success: true,
            message: `Service on port ${port} restarted successfully`,
            timestamp: new Date().toISOString()
        });
    }, 1000);
});

app.post('/api/system/sync', (req, res) => {
    // Simulate system sync
    setTimeout(() => {
        res.json({
            success: true,
            message: 'System sync completed successfully',
            synced_records: Math.floor(Math.random() * 1000) + 5000,
            duration: '2.3s',
            timestamp: new Date().toISOString()
        });
    }, 2000);
});

app.get('/api/reports/generate', (req, res) => {
    const reportData = {
        id: `report_${Date.now()}`,
        type: 'system_status',
        generated_at: new Date().toISOString(),
        data: {
            services_count: 23,
            uptime: '99.98%',
            performance_score: 98.7,
            security_score: 94.2,
            last_24h: {
                requests: Math.floor(Math.random() * 50000) + 100000,
                errors: Math.floor(Math.random() * 10) + 2,
                avg_response_time: '127ms'
            }
        }
    };
    
    res.json({
        success: true,
        message: 'Report generated successfully',
        data: reportData
    });
});

// Serve the enhanced dashboard
app.get('/', (req, res) => {
    const filePath = path.join(__dirname, 'enhanced_admin_dashboard_continuation.html');
    if (fs.existsSync(filePath)) {
        res.sendFile(filePath);
    } else {
        res.status(404).send(`
            <html>
                <head>
                    <title>🔗 Enhanced MesChain Admin Dashboard</title>
                    <style>
                        body { 
                            font-family: 'Inter', sans-serif; 
                            margin: 0; 
                            padding: 40px; 
                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                            color: #fff; 
                            min-height: 100vh;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }
                        .container { 
                            max-width: 600px; 
                            text-align: center; 
                            background: rgba(255, 255, 255, 0.1);
                            padding: 40px;
                            border-radius: 20px;
                            backdrop-filter: blur(10px);
                            border: 1px solid rgba(255, 255, 255, 0.2);
                        }
                        h1 { font-size: 2rem; margin-bottom: 20px; }
                        .status { 
                            background: rgba(255, 255, 255, 0.1); 
                            padding: 20px; 
                            border-radius: 10px; 
                            margin: 20px 0; 
                        }
                        .error { border-left: 4px solid #ff4757; }
                        .info { border-left: 4px solid #3742fa; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h1>🔗 Enhanced MesChain Admin Dashboard</h1>
                        <div class="status error">
                            <h3>Dashboard File Not Found</h3>
                            <p>enhanced_admin_dashboard_continuation.html not found</p>
                        </div>
                        <div class="status info">
                            <h3>Server Information</h3>
                            <p>This server is running on <strong>Port ${PORT}</strong></p>
                            <p>Enhanced MesChain Admin Dashboard v4.2</p>
                        </div>
                    </div>
                </body>
            </html>
        `);
    }
});

// WebSocket server for real-time updates
const server = require('http').createServer(app);
const wss = new WebSocket.Server({ server });

wss.on('connection', (ws) => {
    console.log('🔌 New WebSocket connection established');
    
    // Send initial data
    ws.send(JSON.stringify({
        type: 'welcome',
        message: 'Connected to Enhanced MesChain Admin Dashboard',
        timestamp: new Date().toISOString()
    }));
    
    // Send real-time updates every 30 seconds
    const interval = setInterval(() => {
        if (ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify({
                type: 'metrics_update',
                data: {
                    cpu: Math.floor(Math.random() * 30) + 40,
                    memory: Math.floor(Math.random() * 25) + 60,
                    active_users: Math.floor(Math.random() * 200) + 1100,
                    revenue: Math.floor(Math.random() * 1000) + 5000,
                    timestamp: new Date().toISOString()
                }
            }));
        }
    }, 30000);
    
    ws.on('close', () => {
        console.log('🔌 WebSocket connection closed');
        clearInterval(interval);
    });
    
    ws.on('error', (error) => {
        console.error('WebSocket error:', error);
        clearInterval(interval);
    });
});

// Error handling middleware
app.use((err, req, res, next) => {
    console.error('Server error:', err);
    res.status(500).json({
        success: false,
        error: 'Internal server error',
        message: err.message
    });
});

// Start the server
server.listen(PORT, () => {
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log('🔗 Enhanced MesChain Admin Dashboard Server');
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log(`📡 URL: http://localhost:${PORT}`);
    console.log(`⚡ Dashboard: enhanced_admin_dashboard_continuation.html`);
    console.log(`🎯 Version: 4.2.0 Enterprise Enhanced`);
    console.log(`🕐 Started: ${new Date().toLocaleString('en-US')}`);
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log('✅ Enhanced Admin Dashboard successfully running!');
    console.log('🌐 Access URL: http://localhost:' + PORT);
    console.log('🔄 WebSocket: ws://localhost:' + PORT);
    console.log('📊 API Endpoints: /api/*');
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
});

// Graceful shutdown
process.on('SIGINT', () => {
    console.log('\n🛑 Shutting down Enhanced MesChain Admin Dashboard...');
    server.close(() => {
        console.log('👋 Enhanced Dashboard server closed gracefully');
        process.exit(0);
    });
});

process.on('SIGTERM', () => {
    console.log('\n🛑 Received SIGTERM, shutting down gracefully...');
    server.close(() => {
        process.exit(0);
    });
});

module.exports = app;
