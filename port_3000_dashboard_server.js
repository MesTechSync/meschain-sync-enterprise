const express = require('express');
const path = require('path');
const cors = require('cors');

const app = express();
const PORT = 3000;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static(__dirname));

// Serve the main dashboard
app.get('/', (req, res) => {
    const filePath = path.join(__dirname, 'port_3000_dashboard_with_login.html');
    res.sendFile(filePath);
});

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({
        status: 'OK',
        service: 'Main Enterprise Dashboard',
        port: PORT,
        timestamp: new Date().toISOString(),
        uptime: process.uptime()
    });
});

// API status endpoint
app.get('/api/status', (req, res) => {
    res.json({
        success: true,
        service: 'Main Enterprise Dashboard',
        port: PORT,
        status: 'active',
        timestamp: new Date().toISOString(),
        description: 'Primary Enterprise Dashboard Hub'
    });
});

// Start server
app.listen(PORT, () => {
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log('🏠  MAIN ENTERPRISE DASHBOARD SERVER STARTED');
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
    console.log(`📊 Dashboard URL: http://localhost:${PORT}`);
    console.log(`🔗 Health Check: http://localhost:${PORT}/health`);
    console.log(`🌐 API Status: http://localhost:${PORT}/api/status`);
    console.log('✨ Features: Service Hub, Authentication, Marketplace Navigation');
    console.log('🚀 ═══════════════════════════════════════════════════════════════');
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('🛑 Main Enterprise Dashboard Server shutting down gracefully...');
    process.exit(0);
});

process.on('SIGINT', () => {
    console.log('\n🛑 Main Enterprise Dashboard Server stopping...');
    process.exit(0);
});
