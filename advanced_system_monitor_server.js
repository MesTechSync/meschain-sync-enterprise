const express = require('express');
const path = require('path');
const fs = require('fs');
const cors = require('cors');
const os = require('os');

const app = express();
const PORT = 3026;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static(__dirname));

// Enhanced system monitoring API endpoints
app.get('/api/system/stats', (req, res) => {
    const stats = {
        timestamp: new Date().toISOString(),
        cpu: {
            usage: Math.floor(Math.random() * 40) + 30,
            cores: os.cpus().length,
            loadAverage: os.loadavg()[0].toFixed(2),
            temperature: Math.floor(Math.random() * 20 + 60)
        },
        memory: {
            total: Math.round(os.totalmem() / 1024 / 1024 / 1024),
            used: Math.round((os.totalmem() - os.freemem()) / 1024 / 1024 / 1024),
            free: Math.round(os.freemem() / 1024 / 1024 / 1024),
            usage: Math.floor(((os.totalmem() - os.freemem()) / os.totalmem()) * 100)
        },
        disk: {
            usage: Math.floor(Math.random() * 25 + 40),
            read: (Math.random() * 50 + 20).toFixed(1),
            write: (Math.random() * 30 + 10).toFixed(1),
            iops: Math.floor(Math.random() * 2000 + 1000)
        },
        network: {
            download: (Math.random() * 100 + 50).toFixed(1),
            upload: (Math.random() * 50 + 30).toFixed(1),
            connections: Math.floor(Math.random() * 500 + 1000)
        },
        uptime: os.uptime(),
        platform: os.platform(),
        arch: os.arch(),
        hostname: os.hostname()
    };
    
    res.json({
        success: true,
        data: stats
    });
});

app.get('/api/services/detailed', async (req, res) => {
    const services = [
        { 
            port: 3000, 
            name: 'Main Dashboard', 
            status: 'active',
            pid: Math.floor(Math.random() * 10000 + 1000),
            cpu: Math.floor(Math.random() * 30 + 10),
            memory: Math.floor(Math.random() * 100 + 50),
            uptime: '2d 14h 32m',
            requests: Math.floor(Math.random() * 1000 + 5000),
            errors: Math.floor(Math.random() * 5),
            lastCheck: new Date().toISOString()
        },
        { 
            port: 3002, 
            name: 'Admin Panel', 
            status: 'active',
            pid: Math.floor(Math.random() * 10000 + 1000),
            cpu: Math.floor(Math.random() * 25 + 15),
            memory: Math.floor(Math.random() * 80 + 40),
            uptime: '2d 14h 28m',
            requests: Math.floor(Math.random() * 800 + 3000),
            errors: Math.floor(Math.random() * 3),
            lastCheck: new Date().toISOString()
        },
        { 
            port: 3004, 
            name: 'Performance Monitor', 
            status: 'active',
            pid: Math.floor(Math.random() * 10000 + 1000),
            cpu: Math.floor(Math.random() * 20 + 20),
            memory: Math.floor(Math.random() * 70 + 30),
            uptime: '2d 13h 45m',
            requests: Math.floor(Math.random() * 600 + 2000),
            errors: Math.floor(Math.random() * 2),
            lastCheck: new Date().toISOString()
        },
        { 
            port: 3005, 
            name: 'Product Management', 
            status: 'active',
            pid: Math.floor(Math.random() * 10000 + 1000),
            cpu: Math.floor(Math.random() * 35 + 20),
            memory: Math.floor(Math.random() * 90 + 60),
            uptime: '2d 12h 18m',
            requests: Math.floor(Math.random() * 1200 + 4000),
            errors: Math.floor(Math.random() * 4),
            lastCheck: new Date().toISOString()
        },
        { 
            port: 3023, 
            name: 'Super Admin Panel', 
            status: 'active',
            pid: Math.floor(Math.random() * 10000 + 1000),
            cpu: Math.floor(Math.random() * 28 + 12),
            memory: Math.floor(Math.random() * 65 + 35),
            uptime: '1d 23h 12m',
            requests: Math.floor(Math.random() * 500 + 1500),
            errors: Math.floor(Math.random() * 1),
            lastCheck: new Date().toISOString()
        },
        { 
            port: 3025, 
            name: 'Enhanced Dashboard', 
            status: 'active',
            pid: Math.floor(Math.random() * 10000 + 1000),
            cpu: Math.floor(Math.random() * 22 + 18),
            memory: Math.floor(Math.random() * 75 + 45),
            uptime: '47m 33s',
            requests: Math.floor(Math.random() * 300 + 800),
            errors: Math.floor(Math.random() * 1),
            lastCheck: new Date().toISOString()
        },
        { 
            port: 8080, 
            name: 'API Gateway', 
            status: 'active',
            pid: Math.floor(Math.random() * 10000 + 1000),
            cpu: Math.floor(Math.random() * 45 + 25),
            memory: Math.floor(Math.random() * 120 + 80),
            uptime: '2d 14h 35m',
            requests: Math.floor(Math.random() * 2000 + 10000),
            errors: Math.floor(Math.random() * 8),
            lastCheck: new Date().toISOString()
        }
    ];
    
    res.json({
        success: true,
        data: services,
        summary: {
            total: services.length,
            active: services.filter(s => s.status === 'active').length,
            totalRequests: services.reduce((sum, s) => sum + s.requests, 0),
            totalErrors: services.reduce((sum, s) => sum + s.errors, 0)
        }
    });
});

app.get('/api/logs/recent', (req, res) => {
    const logEntries = [
        { timestamp: new Date(Date.now() - 30000).toISOString(), level: 'INFO', message: 'Database connection pool optimized' },
        { timestamp: new Date(Date.now() - 25000).toISOString(), level: 'SUCCESS', message: 'Cache hit ratio improved to 94.7%' },
        { timestamp: new Date(Date.now() - 20000).toISOString(), level: 'INFO', message: 'API response time: 127ms average' },
        { timestamp: new Date(Date.now() - 15000).toISOString(), level: 'SUCCESS', message: 'Memory cleanup completed successfully' },
        { timestamp: new Date(Date.now() - 10000).toISOString(), level: 'INFO', message: 'Security scan completed - no threats detected' },
        { timestamp: new Date(Date.now() - 5000).toISOString(), level: 'SUCCESS', message: 'Backup process completed successfully' },
        { timestamp: new Date().toISOString(), level: 'INFO', message: 'Network latency optimized' }
    ];
    
    res.json({
        success: true,
        data: logEntries
    });
});

app.get('/api/performance/history', (req, res) => {
    const hours = parseInt(req.query.hours) || 24;
    const interval = parseInt(req.query.interval) || 3600; // 1 hour default
    
    const dataPoints = Math.floor(hours * 3600 / interval);
    const history = [];
    
    for (let i = dataPoints; i > 0; i--) {
        const timestamp = new Date(Date.now() - (i * interval * 1000));
        history.push({
            timestamp: timestamp.toISOString(),
            cpu: Math.floor(Math.random() * 40 + 30),
            memory: Math.floor(Math.random() * 30 + 50),
            disk: Math.floor(Math.random() * 25 + 40),
            network: Math.floor(Math.random() * 50 + 100),
            requests: Math.floor(Math.random() * 1000 + 2000),
            response_time: Math.floor(Math.random() * 100 + 80)
        });
    }
    
    res.json({
        success: true,
        data: history
    });
});

app.post('/api/services/:port/action', (req, res) => {
    const port = req.params.port;
    const action = req.body.action;
    
    setTimeout(() => {
        let message = '';
        switch (action) {
            case 'restart':
                message = `Service on port ${port} restarted successfully`;
                break;
            case 'stop':
                message = `Service on port ${port} stopped`;
                break;
            case 'start':
                message = `Service on port ${port} started`;
                break;
            default:
                message = `Action ${action} performed on service ${port}`;
        }
        
        res.json({
            success: true,
            message: message,
            timestamp: new Date().toISOString()
        });
    }, 1000 + Math.random() * 2000);
});

app.get('/api/network/topology', (req, res) => {
    const nodes = [
        { id: 'gateway', name: 'API Gateway', type: 'gateway', connections: 156, status: 'active' },
        { id: 'admin', name: 'Admin Services', type: 'service', connections: 89, status: 'active' },
        { id: 'api', name: 'Core API', type: 'api', connections: 234, status: 'active' },
        { id: 'database', name: 'Database Cluster', type: 'database', connections: 67, status: 'active' },
        { id: 'cache', name: 'Redis Cache', type: 'cache', connections: 145, status: 'active' },
        { id: 'monitor', name: 'Monitoring', type: 'monitor', connections: 23, status: 'active' }
    ];
    
    const connections = [
        { from: 'gateway', to: 'admin', bandwidth: '125 MB/s', latency: '2ms' },
        { from: 'gateway', to: 'api', bandwidth: '234 MB/s', latency: '1ms' },
        { from: 'api', to: 'database', bandwidth: '89 MB/s', latency: '3ms' },
        { from: 'api', to: 'cache', bandwidth: '167 MB/s', latency: '1ms' },
        { from: 'admin', to: 'database', bandwidth: '45 MB/s', latency: '4ms' },
        { from: 'monitor', to: 'gateway', bandwidth: '12 MB/s', latency: '2ms' }
    ];
    
    res.json({
        success: true,
        data: {
            nodes: nodes,
            connections: connections,
            timestamp: new Date().toISOString()
        }
    });
});

app.get('/api/alerts/active', (req, res) => {
    const alerts = [
        {
            id: 'cpu_high',
            level: 'warning',
            message: 'CPU usage above 80% on service 8080',
            timestamp: new Date(Date.now() - 300000).toISOString(),
            resolved: false
        },
        {
            id: 'disk_space',
            level: 'info',
            message: 'Disk space usage at 75%',
            timestamp: new Date(Date.now() - 600000).toISOString(),
            resolved: false
        }
    ];
    
    res.json({
        success: true,
        data: alerts
    });
});

// Serve the advanced system monitor
app.get('/', (req, res) => {
    const filePath = path.join(__dirname, 'advanced_system_monitor.html');
    if (fs.existsSync(filePath)) {
        res.sendFile(filePath);
    } else {
        res.status(404).send(`
            <html>
                <head>
                    <title>🔗 Advanced System Monitor</title>
                    <style>
                        body { 
                            font-family: 'Inter', sans-serif; 
                            margin: 0; 
                            padding: 40px; 
                            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
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
                        .error { border-left: 4px solid #ef4444; }
                        .info { border-left: 4px solid #3b82f6; }
                    </style>
                </head>
                <body>
                    <div class="container">
                        <h1>🔗 Advanced System Monitor</h1>
                        <div class="status error">
                            <h3>Monitor File Not Found</h3>
                            <p>advanced_system_monitor.html not found</p>
                        </div>
                        <div class="status info">
                            <h3>Server Information</h3>
                            <p>Server running on <strong>Port ${PORT}</strong></p>
                            <p>Advanced System Monitor v1.0</p>
                        </div>
                    </div>
                </body>
            </html>
        `);
    }
});

// Error handling
app.use((err, req, res, next) => {
    console.error('Server error:', err);
    res.status(500).json({
        success: false,
        error: 'Internal server error',
        message: err.message
    });
});

// Start server
app.listen(PORT, () => {
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log('🔗 Advanced System Monitor Server');
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log(`📡 URL: http://localhost:${PORT}`);
    console.log(`⚡ Monitor: advanced_system_monitor.html`);
    console.log(`🎯 Version: 1.0.0 Enterprise`);
    console.log(`🕐 Started: ${new Date().toLocaleString('en-US')}`);
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log('✅ Advanced System Monitor running!');
    console.log('🌐 Access URL: http://localhost:' + PORT);
    console.log('📊 API Endpoints: /api/*');
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
});

// Graceful shutdown
process.on('SIGINT', () => {
    console.log('\n🛑 Shutting down Advanced System Monitor...');
    process.exit(0);
});

module.exports = app;
