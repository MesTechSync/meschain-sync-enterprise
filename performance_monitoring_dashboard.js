#!/usr/bin/env node

/**
 * Advanced Performance Monitoring Dashboard
 * MesChain-Sync Enterprise - Real-time System Monitoring
 * Version: 1.0.0
 * Date: June 7, 2025
 */

const express = require('express');
const http = require('http');
const socketIO = require('socket.io');
const os = require('os');
const fs = require('fs');
const path = require('path');

const app = express();
const server = http.createServer(app);
const io = socketIO(server);

const PORT = 3030;

// Performance metrics storage
let performanceMetrics = {
    systemHealth: {
        cpu: 0,
        memory: 0,
        uptime: 0,
        loadAverage: []
    },
    serverMetrics: {},
    requestCounts: {},
    errorCounts: {},
    lastUpdated: new Date()
};

// Middleware
app.use(express.static(path.join(__dirname, 'public')));
app.use(express.json());

// Collect system metrics
function collectSystemMetrics() {
    const cpus = os.cpus();
    const totalMemory = os.totalmem();
    const freeMemory = os.freemem();
    const usedMemory = totalMemory - freeMemory;
    
    performanceMetrics.systemHealth = {
        cpu: Math.round((cpus.length - os.loadavg()[0]) / cpus.length * 100),
        memory: Math.round((usedMemory / totalMemory) * 100),
        uptime: os.uptime(),
        loadAverage: os.loadavg(),
        platform: os.platform(),
        architecture: os.arch(),
        nodeVersion: process.version
    };
}

// Test server endpoints
async function testServerEndpoints() {
    const servers = [
        { name: 'Super Admin Panel', port: 3002 },
        { name: 'Product Management', port: 3005 },
        { name: 'Cross-Marketplace Admin', port: 3009 },
        { name: 'Trendyol Seller Hub', port: 3012 },
        { name: 'PHP Analytics Engine', port: 8080, path: '/health.php' }
    ];
    
    for (const server of servers) {
        try {
            const startTime = Date.now();
            const response = await fetch(`http://localhost:${server.port}${server.path || '/health'}`);
            const endTime = Date.now();
            const responseTime = endTime - startTime;
            
            performanceMetrics.serverMetrics[server.name] = {
                status: response.ok ? 'healthy' : 'error',
                responseTime: responseTime,
                port: server.port,
                lastChecked: new Date()
            };
        } catch (error) {
            performanceMetrics.serverMetrics[server.name] = {
                status: 'offline',
                responseTime: null,
                port: server.port,
                error: error.message,
                lastChecked: new Date()
            };
        }
    }
}

// Dashboard HTML
app.get('/', (req, res) => {
    res.send(`
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MesChain-Sync Performance Monitor</title>
    <script src="/socket.io/socket.io.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .metric-card {
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            padding: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        .metric-title {
            font-size: 1.2rem;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .metric-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .server-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .server-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
        }
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .status-healthy { background: #4ade80; }
        .status-error { background: #ef4444; }
        .status-offline { background: #6b7280; }
        .progress-bar {
            width: 100%;
            height: 20px;
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4ade80, #22c55e);
            transition: width 0.3s ease;
        }
        .real-time-badge {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(34, 197, 94, 0.2);
            padding: 10px 20px;
            border-radius: 25px;
            border: 1px solid #22c55e;
        }
        .pulse {
            width: 8px;
            height: 8px;
            background: #22c55e;
            border-radius: 50%;
            margin-right: 8px;
            animation: pulse 1s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
            100% { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="real-time-badge">
        <div class="pulse"></div>
        <span>Real-time Monitoring</span>
    </div>
    
    <div class="container">
        <div class="header">
            <h1>🚀 MesChain-Sync Performance Monitor</h1>
            <p>Real-time system monitoring and performance analytics</p>
            <p><strong>Last Updated:</strong> <span id="lastUpdated">--</span></p>
        </div>
        
        <div class="dashboard-grid">
            <div class="metric-card">
                <div class="metric-title">🖥️ System CPU</div>
                <div class="metric-value" id="cpuUsage">--</div>
                <div class="progress-bar">
                    <div class="progress-fill" id="cpuProgress" style="width: 0%"></div>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-title">💾 Memory Usage</div>
                <div class="metric-value" id="memoryUsage">--</div>
                <div class="progress-bar">
                    <div class="progress-fill" id="memoryProgress" style="width: 0%"></div>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="metric-title">⏱️ System Uptime</div>
                <div class="metric-value" id="systemUptime">--</div>
                <p>Platform: <span id="platform">--</span></p>
            </div>
            
            <div class="metric-card">
                <div class="metric-title">🚀 Server Status</div>
                <div class="server-list" id="serverList">
                    <!-- Servers will be populated here -->
                </div>
            </div>
        </div>
    </div>
    
    <script>
        const socket = io();
        
        socket.on('performance-update', (data) => {
            updateDashboard(data);
        });
        
        function updateDashboard(metrics) {
            // Update system metrics
            document.getElementById('cpuUsage').textContent = metrics.systemHealth.cpu + '%';
            document.getElementById('cpuProgress').style.width = metrics.systemHealth.cpu + '%';
            
            document.getElementById('memoryUsage').textContent = metrics.systemHealth.memory + '%';
            document.getElementById('memoryProgress').style.width = metrics.systemHealth.memory + '%';
            
            document.getElementById('systemUptime').textContent = formatUptime(metrics.systemHealth.uptime);
            document.getElementById('platform').textContent = metrics.systemHealth.platform + ' ' + metrics.systemHealth.architecture;
            
            // Update server list
            const serverList = document.getElementById('serverList');
            serverList.innerHTML = '';
            
            Object.entries(metrics.serverMetrics).forEach(([name, server]) => {
                const serverItem = document.createElement('div');
                serverItem.className = 'server-item';
                serverItem.innerHTML = \`
                    <div style="display: flex; align-items: center;">
                        <div class="status-indicator status-\${server.status}"></div>
                        <span>\${name} (:\${server.port})</span>
                    </div>
                    <span>\${server.responseTime ? server.responseTime + 'ms' : server.status}</span>
                \`;
                serverList.appendChild(serverItem);
            });
            
            document.getElementById('lastUpdated').textContent = new Date(metrics.lastUpdated).toLocaleTimeString();
        }
        
        function formatUptime(seconds) {
            const days = Math.floor(seconds / 86400);
            const hours = Math.floor((seconds % 86400) / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            return \`\${days}d \${hours}h \${minutes}m\`;
        }
    </script>
</body>
</html>
    `);
});

// API endpoint for metrics
app.get('/api/metrics', (req, res) => {
    res.json(performanceMetrics);
});

// Socket.IO connection handling
io.on('connection', (socket) => {
    console.log('📊 Performance monitoring client connected');
    
    // Send initial data
    socket.emit('performance-update', performanceMetrics);
    
    socket.on('disconnect', () => {
        console.log('📊 Performance monitoring client disconnected');
    });
});

// Start monitoring
function startMonitoring() {
    setInterval(async () => {
        collectSystemMetrics();
        await testServerEndpoints();
        performanceMetrics.lastUpdated = new Date();
        
        // Broadcast to all connected clients
        io.emit('performance-update', performanceMetrics);
    }, 5000); // Update every 5 seconds
}

// Start server
server.listen(PORT, () => {
    console.log('🚀 Performance Monitoring Dashboard started!');
    console.log(\`📊 Dashboard: http://localhost:\${PORT}\`);
    console.log(\`🔍 API: http://localhost:\${PORT}/api/metrics\`);
    console.log('⚡ Real-time monitoring active');
    
    // Start monitoring immediately
    collectSystemMetrics();
    testServerEndpoints().then(() => {
        performanceMetrics.lastUpdated = new Date();
        startMonitoring();
    });
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('🛑 Performance Monitor shutting down gracefully...');
    server.close(() => {
        process.exit(0);
    });
});
