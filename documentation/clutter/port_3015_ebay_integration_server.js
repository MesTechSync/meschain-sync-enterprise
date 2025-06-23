const express = require('express');
const cors = require('cors');
const path = require('path');

const app = express();
const PORT = 6015;

// Enable CORS for all requests
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Serve static files
app.use(express.static(path.join(__dirname, 'public')));
app.use('/CursorDev', express.static(path.join(__dirname, 'CursorDev')));

// Load Priority 3 Authentication Middleware
const Priority3AuthMiddleware = require('./priority3_auth_middleware');

// Initialize authentication
const auth = new Priority3AuthMiddleware({
    serviceName: 'eBay Integration Hub Server',
    serviceType: 'ebay_integration',
    port: PORT,
    requiredRoles: ['super_admin', 'admin', 'marketplace_manager'],
    permissions: {'super_admin': ['*'], 'admin': ['ebay', 'global', 'gsp'], 'marketplace_manager': ['ebay', 'global']}
});

// Authentication middleware
const authenticateUser = auth.requireAuth();

// Login and logout routes
app.get('/login', auth.getLoginPage());
app.post('/login', auth.handleLogin());
app.post('/logout', auth.handleLogout());

// Protected routes - require authentication
app.get('/', authenticateUser, (req, res) => {
    res.send(`DASHBOARD_HTML_PLACEHOLDER`);
});

// API Routes with authentication
app.get('/api/status', authenticateUser, (req, res) => {
    res.json({
        success: true,
        service: 'eBay Integration Hub Server',
        port: PORT,
        status: 'active',
        timestamp: new Date().toISOString(),
        user: req.user
    });
});

// Health check endpoint (no auth required)
app.get('/health', (req, res) => {
    res.json({
        success: true,
        service: 'eBay Integration Hub Server',
        port: PORT,
        status: 'healthy',
        timestamp: new Date().toISOString(),
        uptime: process.uptime()
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`🚀 eBay Integration Hub Server running on port ${PORT}`);
    console.log(`🔐 Authentication: Priority 3 - Global eBay Multi-market Support`);
    console.log(`📊 Dashboard: http://localhost:${PORT}`);
    console.log(`🔑 Login: http://localhost:${PORT}/login`);
    console.log(`🌐 API: http://localhost:${PORT}/api/*`);
    console.log(`💡 Health: http://localhost:${PORT}/health`);
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('🛑 eBay Integration Hub Server shutting down gracefully...');
    process.exit(0);
});
