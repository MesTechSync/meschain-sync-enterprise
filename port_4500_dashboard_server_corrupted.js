const express = require('express');
const path = require('path');
const cors = require('cors');
const WebSocket = require('ws');
const http = require('http');
const axios = require('axios');

const app = express();
const server = http.createServer(app);
const PORT = 4500;

// Create WebSo    console.log(`👑 Super Admin: http://localhost:${PORT}/dashboard.html`);   console.log(`👑 Super Admin: http://localhost:${PORT}/dashboard.html`);   console.log(`👑 Super Admin: http://localhost:${PORT}/dashboard.html`);ket server
const wss = new WebSocket.Server({ 
    server,
    path: '/dashboard-ws'
});

// Middleware
app.use(cors({
    origin: '*',
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization']
}));
app.use(express.json());
app.use(express.static(__dirname));

// System Monitoring Data
const systemServices = {
    criticalServices: [
        { name: 'VSCode Atomic Task Coordination Center', port: 3050, status: 'unknown', endpoint: '/health' },
        { name: 'VSCode Advanced Security Framework', port: 3042, status: 'unknown', endpoint: '/health' },
        { name: 'VSCode Microservices Architecture', port: 3043, status: 'unknown', endpoint: '/health' },
        { name: 'VSCode Quantum Performance Engine', port: 3041, status: 'unknown', endpoint: '/health' },
        { name: 'Real-time Features Server', port: 3039, status: 'unknown', endpoint: '/health' },
        { name: 'User Management & RBAC', port: 3036, status: 'unknown', endpoint: '/health' }
    ],
    frontendServices: [
        { name: 'Super Admin Panel', port: 3023, status: 'unknown', endpoint: '/' },
        { name: 'Enhanced Quantum Panel', port: 3030, status: 'unknown', endpoint: '/' },
        { name: 'Main Enterprise Dashboard', port: 3000, status: 'unknown', endpoint: '/' }
    ]
};

// Check service health
async function checkServiceHealth(service) {
    try {
        const response = await axios.get(`http://localhost:${service.port}${service.endpoint}`, { timeout: 3000 });
        service.status = 'healthy';
        service.responseTime = response.headers['x-response-time'] || 'N/A';
        service.lastCheck = new Date().toISOString();
        return true;
    } catch (error) {
        service.status = 'unhealthy';
        service.error = error.message;
        service.lastCheck = new Date().toISOString();
        return false;
    }
}

// Monitor all services periodically
setInterval(async () => {
    const allServices = [...systemServices.criticalServices, ...systemServices.frontendServices];
    for (const service of allServices) {
        await checkServiceHealth(service);
    }
    
    // Broadcast to all connected WebSocket clients
    const systemStatus = {
        type: 'system_status_update',
        data: {
            criticalServices: systemServices.criticalServices,
            frontendServices: systemServices.frontendServices,
            timestamp: new Date().toISOString()
        }
    };
    
    wss.clients.forEach(client => {
        if (client.readyState === WebSocket.OPEN) {
            client.send(JSON.stringify(systemStatus));
        }
    });
}, 10000); // Check every 10 seconds

// WebSocket connection handling
wss.on('connection', (ws, req) => {
    console.log('🔌 Dashboard WebSocket connected from:', req.socket.remoteAddress);
    
    // Send welcome message
    ws.send(JSON.stringify({
        type: 'connection',
        status: 'connected',
        message: 'Connected to Port 4500 Dashboard',
        timestamp: new Date().toISOString()
    }));
    
    // Send periodic system updates
    const updateInterval = setInterval(() => {
        if (ws.readyState === WebSocket.OPEN) {
            ws.send(JSON.stringify({
                type: 'system_update',
                data: {
                    activeServices: Math.floor(Math.random() * 5) + 25,
                    totalPorts: 34,
                    systemLoad: (Math.random() * 30 + 20).toFixed(1),
                    uptime: Math.floor(process.uptime()),
                    timestamp: new Date().toISOString()
                }
            }));
        } else {
            clearInterval(updateInterval);
        }
    }, 3000);
    
    ws.on('close', () => {
        clearInterval(updateInterval);
        console.log('🔌 Dashboard WebSocket disconnected');
    });
});

// Serve the main dashboard
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'port_4500_dashboard.html'));
});

// API endpoints
app.get('/api/status', (req, res) => {
    res.json({
        success: true,
        service: 'Port 4500 Dashboard',
        port: PORT,
        status: 'active',
        timestamp: new Date().toISOString(),
        description: 'Advanced System Monitoring Dashboard',
        features: [
            'Real-time monitoring',
            'WebSocket integration',
            'System performance tracking',
            'Service health monitoring'
        ]
    });
});

app.get('/api/system-stats', (req, res) => {
    res.json({
        success: true,
        stats: {
            totalServices: 28,
            activeServices: 26,
            systemLoad: (Math.random() * 30 + 15).toFixed(1) + '%',
            memoryUsage: (Math.random() * 40 + 45).toFixed(1) + '%',
            diskUsage: (Math.random() * 20 + 60).toFixed(1) + '%',
            networkTraffic: Math.floor(Math.random() * 1000) + 500 + ' KB/s',
            uptime: Math.floor(process.uptime()) + 's',
            lastUpdate: new Date().toISOString()
        }
    });
});

app.get('/api/services', (req, res) => {
    const services = [
        { port: 3000, name: 'Main Enterprise Dashboard', status: 'active' },
        { port: 3001, name: 'Frontend Components Hub', status: 'active' },
        { port: 3002, name: 'Super Admin Panel', status: 'active' },
        { port: 3003, name: 'Marketplace Hub', status: 'active' },
        { port: 3004, name: 'Performance Dashboard', status: 'active' },
        { port: 3005, name: 'Product Management', status: 'active' },
        { port: 3006, name: 'Order Management', status: 'active' },
        { port: 3007, name: 'Inventory Management', status: 'active' },
        { port: 3008, name: 'Advanced Dashboard', status: 'active' },
        { port: 3023, name: 'Super Admin Panel (HTML)', status: 'warning' },
        { port: 3028, name: 'AI Analytics Dashboard', status: 'active' },
        { port: 3029, name: 'Mobile App Manager', status: 'active' },
        { port: 3030, name: 'Security & Compliance Center', status: 'active' }
    ];
    
    res.json({
        success: true,
        services: services,
        total: services.length,
        active: services.filter(s => s.status === 'active').length,
        warning: services.filter(s => s.status === 'warning').length
    });
});

// API Endpoints for System Management
app.get('/api/system/status', async (req, res) => {
    const allServices = [...systemServices.criticalServices, ...systemServices.frontendServices];
    const healthyServices = allServices.filter(s => s.status === 'healthy').length;
    
    res.json({
        status: 'operational',
        totalServices: allServices.length,
        healthyServices,
        healthPercentage: Math.round((healthyServices / allServices.length) * 100),
        criticalServices: systemServices.criticalServices,
        frontendServices: systemServices.frontendServices,
        lastUpdate: new Date().toISOString()
    });
});

app.get('/api/services/critical', (req, res) => {
    res.json({
        services: systemServices.criticalServices,
        count: systemServices.criticalServices.length,
        healthy: systemServices.criticalServices.filter(s => s.status === 'healthy').length
    });
});

app.get('/api/services/frontend', (req, res) => {
    res.json({
        services: systemServices.frontendServices,
        count: systemServices.frontendServices.length,
        healthy: systemServices.frontendServices.filter(s => s.status === 'healthy').length
    });
});

app.post('/api/services/restart/:port', async (req, res) => {
    const port = req.params.port;
    const service = [...systemServices.criticalServices, ...systemServices.frontendServices]
        .find(s => s.port.toString() === port);
    
    if (!service) {
        return res.status(404).json({ error: 'Service not found' });
    }
    
    try {
        // Attempt to restart by calling service restart endpoint
        await axios.post(`http://localhost:${port}/restart`, {}, { timeout: 5000 });
        res.json({ success: true, message: `Service on port ${port} restart initiated` });
    } catch (error) {
        res.status(500).json({ error: 'Failed to restart service', details: error.message });
    }
});

app.get('/api/coordination/tasks', async (req, res) => {
    try {
        const response = await axios.get('http://localhost:3050/status', { timeout: 5000 });
        res.json(response.data);
    } catch (error) {
        res.status(503).json({ error: 'Coordination center unavailable', details: error.message });
    }
});

app.get('/api/security/status', async (req, res) => {
    try {
        const response = await axios.get('http://localhost:3042/security-status', { timeout: 5000 });
        res.json(response.data);
    } catch (error) {
        res.status(503).json({ error: 'Security framework unavailable', details: error.message });
    }
});

app.get('/api/performance/metrics', async (req, res) => {
    try {
        const response = await axios.get('http://localhost:3041/performance-status', { timeout: 5000 });
        res.json(response.data);
    } catch (error) {
        res.status(503).json({ error: 'Performance engine unavailable', details: error.message });
    }
});

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({
        status: 'healthy',
        service: 'MesChain Enterprise Dashboard',
        port: PORT,
        timestamp: new Date().toISOString(),
        uptime: process.uptime(),
        version: '4.0.0-ENTERPRISE'
    });
});

// Main dashboard route
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'meschain_enterprise_dashboard_4500.html'));
});

// Super admin integration route
app.get('/dashboard.html', (req, res) => {
    res.sendFile(path.join(__dirname, 'meschain_sync_super_admin.html'));
});

// Start server with WebSocket support
server.listen(PORT, () => {
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log('📊    MESCHAIN ENTERPRISE DASHBOARD SERVER STARTED SUCCESSFULLY  📊');
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log(`📊 Dashboard URL: http://localhost:${PORT}`);
    console.log(`� Super Admin: http://localhost:${PORT}/super-admin`);
    console.log(`�🔌 WebSocket: ws://localhost:${PORT}/dashboard-ws`);
    console.log(`🔗 Health Check: http://localhost:${PORT}/health`);
    console.log(`🌐 System Status: http://localhost:${PORT}/api/system/status`);
    console.log(`🎯 Critical Services: http://localhost:${PORT}/api/services/critical`);
    console.log(`�️ Frontend Services: http://localhost:${PORT}/api/services/frontend`);
    console.log('✨ Features: Real-time monitoring, Service coordination, WebSocket support');
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    
    // Start initial health check
    setTimeout(async () => {
        console.log('🔍 Performing initial system health check...');
        const allServices = [...systemServices.criticalServices, ...systemServices.frontendServices];
        for (const service of allServices) {
            await checkServiceHealth(service);
        }
        console.log('✅ Initial health check completed');
    }, 2000);
});

// Graceful shutdown

process.on('SIGTERM', () => {
    console.log('🛑 MesChain Enterprise Dashboard Server shutting down gracefully...');
    wss.close();
    server.close(() => {
        console.log('✅ Server closed');
        process.exit(0);
    });
});

process.on('SIGINT', () => {
    console.log('\n🛑 MesChain Enterprise Dashboard Server stopping...');
    wss.close();
    server.close(() => {
        console.log('✅ Server closed');
        process.exit(0);
    });
});
