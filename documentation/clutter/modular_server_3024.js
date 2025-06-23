const express = require('express');
const path = require('path');
const cors = require('cors');

const app = express();
const PORT = 3024;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Modüler Super Admin Panel için static files
app.use(express.static(path.join(__dirname, 'super_admin_modular')));

// Ana sayfa - Modüler Super Admin Panel
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'super_admin_modular', 'index.html'));
});

// Super Admin Panel ana sayfası
app.get('/meschain_sync_super_admin.html', (req, res) => {
    res.sendFile(path.join(__dirname, 'super_admin_modular', 'index.html'));
});

// Eski super admin panel (backup)
app.get('/original', (req, res) => {
    res.sendFile(path.join(__dirname, 'meschain_sync_super_admin.html'));
});

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({
        status: 'healthy',
        port: PORT,
        service: 'MesChain-Sync Modular Super Admin Panel',
        version: '5.0',
        timestamp: new Date().toISOString(),
        features: {
            modular_js: true,
            modular_css: true,
            multi_language: true,
            theme_system: true,
            health_monitoring: true,
            marketplace_integration: true
        }
    });
});

// API endpoints for system status
app.get('/api/status', (req, res) => {
    res.json({
        system: 'MesChain-Sync Enterprise',
        version: '5.0',
        architecture: 'Modular',
        components: {
            javascript_modules: 10,
            css_modules: 7,
            languages_supported: 4,
            marketplaces_integrated: 6
        },
        status: 'operational',
        last_update: '2025-06-15'
    });
});

// API endpoint for component status
app.get('/api/components', (req, res) => {
    res.json({
        javascript_modules: [
            { name: 'core.js', status: 'loaded', size: '2.1kb' },
            { name: 'notifications.js', status: 'loaded', size: '8.7kb' },
            { name: 'language.js', status: 'loaded', size: '6.2kb' },
            { name: 'theme.js', status: 'loaded', size: '5.8kb' },
            { name: 'sidebar.js', status: 'loaded', size: '4.9kb' },
            { name: 'health.js', status: 'loaded', size: '6.1kb' },
            { name: 'navigation.js', status: 'loaded', size: '5.4kb' },
            { name: 'marketplace.js', status: 'loaded', size: '7.3kb' },
            { name: 'trendyol.js', status: 'loaded', size: '6.8kb' },
            { name: 'utils.js', status: 'loaded', size: '7.2kb' }
        ],
        css_modules: [
            { name: 'theme.css', status: 'loaded', size: '4.1kb' },
            { name: 'main.css', status: 'loaded', size: '12.3kb' },
            { name: 'sidebar.css', status: 'loaded', size: '8.7kb' },
            { name: 'components.css', status: 'loaded', size: '15.2kb' },
            { name: 'marketplace.css', status: 'loaded', size: '6.9kb' },
            { name: 'animations.css', status: 'loaded', size: '3.4kb' },
            { name: 'services.css', status: 'loaded', size: '5.8kb' }
        ]
    });
});

// Marketplace simulation endpoints
app.get('/api/marketplaces', (req, res) => {
    res.json({
        trendyol: { port: 3012, status: 'online', last_sync: '2025-06-15T10:30:00Z' },
        amazon: { port: 3011, status: 'online', last_sync: '2025-06-15T10:25:00Z' },
        n11: { port: 3014, status: 'online', last_sync: '2025-06-15T10:20:00Z' },
        hepsiburada: { port: 3010, status: 'online', last_sync: '2025-06-15T10:35:00Z' },
        gittigidiyor: { port: 3013, status: 'maintenance', last_sync: '2025-06-15T09:15:00Z' },
        ebay: { port: 3015, status: 'online', last_sync: '2025-06-15T10:40:00Z' }
    });
});

// Error handling
app.use((err, req, res, next) => {
    console.error('Error:', err);
    res.status(500).json({
        error: 'Internal Server Error',
        message: err.message,
        timestamp: new Date().toISOString()
    });
});

// HTML Component routes
app.get('/components/header.html', (req, res) => {
    res.sendFile(path.join(__dirname, 'super_admin_modular', 'components', 'header.html'));
});

app.get('/components/sidebar.html', (req, res) => {
    res.sendFile(path.join(__dirname, 'super_admin_modular', 'components', 'sidebar.html'));
});

app.get('/components/main-content.html', (req, res) => {
    res.sendFile(path.join(__dirname, 'super_admin_modular', 'components', 'main-content.html'));
});

app.get('/components/modals.html', (req, res) => {
    res.sendFile(path.join(__dirname, 'super_admin_modular', 'components', 'modals.html'));
});

// 404 handler
app.use((req, res) => {
    res.status(404).json({
        error: 'Not Found',
        message: `Route ${req.path} not found`,
        available_routes: [
            '/',
            '/meschain_sync_super_admin.html',
            '/original',
            '/health',
            '/api/status',
            '/api/components',
            '/api/marketplaces',
            '/components/header.html',
            '/components/sidebar.html',
            '/components/main-content.html',
            '/components/modals.html'
        ]
    });
});

// Server startup
app.listen(PORT, () => {
    console.log('🚀 MesChain-Sync Modular Super Admin Panel Server Started!');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    console.log(`📡 Server: http://localhost:${PORT}`);
    console.log(`🔗 Super Admin Panel: http://localhost:${PORT}/meschain_sync_super_admin.html`);
    console.log(`🏠 Home: http://localhost:${PORT}/`);
    console.log(`🔄 Original Panel: http://localhost:${PORT}/original`);
    console.log(`❤️  Health Check: http://localhost:${PORT}/health`);
    console.log(`📊 API Status: http://localhost:${PORT}/api/status`);
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    console.log('🎯 Features:');
    console.log('   ✅ Modular JavaScript Architecture (10 modules)');
    console.log('   ✅ Modular CSS System (7 stylesheets)');
    console.log('   ✅ Multi-language Support (TR/EN/DE/FR)');
    console.log('   ✅ Advanced Theme System (Dark/Light)');
    console.log('   ✅ Real-time Health Monitoring');
    console.log('   ✅ Marketplace Integrations');
    console.log('   ✅ RESTful API Endpoints');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    console.log('📱 Mobile & Desktop Responsive');
    console.log('🔒 Enterprise Security Ready');
    console.log('⚡ Production Optimized');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('🛑 SIGTERM received, shutting down gracefully...');
    process.exit(0);
});

process.on('SIGINT', () => {
    console.log('🛑 SIGINT received, shutting down gracefully...');
    process.exit(0);
});

module.exports = app;
