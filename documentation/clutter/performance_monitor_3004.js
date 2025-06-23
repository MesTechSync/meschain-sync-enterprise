// MesChain Performance Monitor Service - Port 3004
// HIGH PRIORITY SERVICE - Created: June 12, 2025

const express = require('express');
const cors = require('cors');
const os = require('os');
const fs = require('fs');
const path = require('path');

const app = express();
const PORT = 3004;

// Middleware
app.use(cors({
    origin: '*',
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With']
}));
app.use(express.json({ limit: '50mb' }));
app.use(express.urlencoded({ extended: true }));

// Performance metrics storage
let performanceData = {
    cpu: [],
    memory: [],
    disk: [],
    network: [],
    errors: [],
    responses: []
};

// System performance tracking
function collectSystemMetrics() {
    const cpuUsage = os.loadavg()[0] / os.cpus().length * 100;
    const memoryUsage = (1 - (os.freemem() / os.totalmem())) * 100;
    
    const timestamp = new Date().toISOString();
    
    performanceData.cpu.push({
        timestamp,
        usage: cpuUsage.toFixed(2),
        cores: os.cpus().length
    });
    
    performanceData.memory.push({
        timestamp,
        usage: memoryUsage.toFixed(2),
        total: (os.totalmem() / 1024 / 1024 / 1024).toFixed(2) + 'GB',
        free: (os.freemem() / 1024 / 1024 / 1024).toFixed(2) + 'GB'
    });
    
    // Keep only last 100 entries
    if (performanceData.cpu.length > 100) performanceData.cpu.shift();
    if (performanceData.memory.length > 100) performanceData.memory.shift();
}

// Start collecting metrics every 5 seconds
setInterval(collectSystemMetrics, 5000);
collectSystemMetrics(); // Initial collection

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({
        success: true,
        service: 'Performance Monitor',
        port: PORT,
        status: 'healthy',
        timestamp: new Date().toISOString(),
        uptime: process.uptime(),
        metrics: {
            cpu: performanceData.cpu.length,
            memory: performanceData.memory.length,
            monitoring: 'active'
        }
    });
});

// Get current system performance
app.get('/api/performance/current', (req, res) => {
    const latest = {
        cpu: performanceData.cpu[performanceData.cpu.length - 1] || null,
        memory: performanceData.memory[performanceData.memory.length - 1] || null,
        system: {
            platform: os.platform(),
            arch: os.arch(),
            hostname: os.hostname(),
            uptime: os.uptime()
        }
    };
    
    res.json({
        success: true,
        data: latest,
        timestamp: new Date().toISOString()
    });
});

// Get performance history
app.get('/api/performance/history', (req, res) => {
    const limit = parseInt(req.query.limit) || 50;
    
    res.json({
        success: true,
        data: {
            cpu: performanceData.cpu.slice(-limit),
            memory: performanceData.memory.slice(-limit)
        },
        timestamp: new Date().toISOString()
    });
});

// Get performance alerts
app.get('/api/performance/alerts', (req, res) => {
    const alerts = [];
    const latest = performanceData.cpu[performanceData.cpu.length - 1];
    const latestMem = performanceData.memory[performanceData.memory.length - 1];
    
    if (latest && parseFloat(latest.usage) > 80) {
        alerts.push({
            type: 'cpu',
            level: 'warning',
            message: `High CPU usage: ${latest.usage}%`,
            timestamp: latest.timestamp
        });
    }
    
    if (latestMem && parseFloat(latestMem.usage) > 85) {
        alerts.push({
            type: 'memory',
            level: 'warning', 
            message: `High memory usage: ${latestMem.usage}%`,
            timestamp: latestMem.timestamp
        });
    }
    
    res.json({
        success: true,
        alerts,
        count: alerts.length,
        timestamp: new Date().toISOString()
    });
});

// Performance dashboard
app.get('/dashboard', (req, res) => {
    const html = `
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>MesChain Performance Monitor</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; }
            .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .header h1 { color: #2c3e50; margin-bottom: 10px; }
            .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
            .metric-card { background: white; border-radius: 8px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .metric-title { font-size: 18px; font-weight: bold; color: #34495e; margin-bottom: 15px; }
            .metric-value { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
            .cpu-value { color: #3498db; }
            .memory-value { color: #e74c3c; }
            .disk-value { color: #f39c12; }
            .progress-bar { width: 100%; height: 8px; background: #ecf0f1; border-radius: 4px; overflow: hidden; }
            .progress-fill { height: 100%; border-radius: 4px; transition: width 0.3s ease; }
            .alerts { margin-top: 30px; }
            .alert { padding: 15px; margin: 10px 0; border-radius: 5px; }
            .alert-warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
            .timestamp { font-size: 12px; color: #7f8c8d; margin-top: 10px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🚀 MesChain Performance Monitor</h1>
                <p>Real-time system performance monitoring and alerts</p>
            </div>
            
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-title">CPU Usage</div>
                    <div class="metric-value cpu-value" id="cpuValue">Loading...</div>
                    <div class="progress-bar">
                        <div class="progress-fill" id="cpuProgress" style="background: #3498db;"></div>
                    </div>
                    <div class="timestamp" id="cpuTime"></div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-title">Memory Usage</div>
                    <div class="metric-value memory-value" id="memoryValue">Loading...</div>
                    <div class="progress-bar">
                        <div class="progress-fill" id="memoryProgress" style="background: #e74c3c;"></div>
                    </div>
                    <div class="timestamp" id="memoryTime"></div>
                </div>
                
                <div class="metric-card">
                    <div class="metric-title">System Info</div>
                    <div id="systemInfo">Loading...</div>
                </div>
            </div>
            
            <div class="alerts">
                <h3>🚨 Performance Alerts</h3>
                <div id="alertsContainer">Loading alerts...</div>
            </div>
        </div>
        
        <script>
            function updateMetrics() {
                fetch('/api/performance/current')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data.cpu) {
                            document.getElementById('cpuValue').textContent = data.data.cpu.usage + '%';
                            document.getElementById('cpuProgress').style.width = data.data.cpu.usage + '%';
                            document.getElementById('cpuTime').textContent = new Date(data.data.cpu.timestamp).toLocaleString();
                        }
                        
                        if (data.success && data.data.memory) {
                            document.getElementById('memoryValue').textContent = data.data.memory.usage + '%';
                            document.getElementById('memoryProgress').style.width = data.data.memory.usage + '%';
                            document.getElementById('memoryTime').textContent = new Date(data.data.memory.timestamp).toLocaleString();
                        }
                        
                        if (data.success && data.data.system) {
                            const sys = data.data.system;
                            document.getElementById('systemInfo').innerHTML = 
                                '<p><strong>Platform:</strong> ' + sys.platform + '</p>' +
                                '<p><strong>Architecture:</strong> ' + sys.arch + '</p>' +
                                '<p><strong>Hostname:</strong> ' + sys.hostname + '</p>' +
                                '<p><strong>Uptime:</strong> ' + Math.floor(sys.uptime / 3600) + ' hours</p>';
                        }
                    })
                    .catch(error => console.error('Error fetching metrics:', error));
            }
            
            function updateAlerts() {
                fetch('/api/performance/alerts')
                    .then(response => response.json())
                    .then(data => {
                        const container = document.getElementById('alertsContainer');
                        if (data.success && data.alerts.length > 0) {
                            container.innerHTML = data.alerts.map(alert => 
                                '<div class="alert alert-warning">' +
                                '<strong>' + alert.type.toUpperCase() + ' Alert:</strong> ' + alert.message +
                                '<div class="timestamp">' + new Date(alert.timestamp).toLocaleString() + '</div>' +
                                '</div>'
                            ).join('');
                        } else {
                            container.innerHTML = '<p style="color: #27ae60;">✅ No performance alerts</p>';
                        }
                    })
                    .catch(error => console.error('Error fetching alerts:', error));
            }
            
            // Update every 5 seconds
            updateMetrics();
            updateAlerts();
            setInterval(updateMetrics, 5000);
            setInterval(updateAlerts, 10000);
        </script>
    </body>
    </html>`;
    
    res.send(html);
});

// Error handling
app.use((err, req, res, next) => {
    console.error('Performance Monitor Error:', err);
    res.status(500).json({
        success: false,
        error: 'Internal Server Error',
        message: 'An error occurred in performance monitor'
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`📊 MesChain Performance Monitor running on port ${PORT}`);
    console.log(`🏥 Health check: http://localhost:${PORT}/health`);
    console.log(`📈 Dashboard: http://localhost:${PORT}/dashboard`);
    console.log(`⏰ Started at: ${new Date().toISOString()}`);
    collectSystemMetrics();
});

module.exports = app;
