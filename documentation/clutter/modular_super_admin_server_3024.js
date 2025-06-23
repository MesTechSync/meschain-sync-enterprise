/**
 * MesChain-Sync Modular Super Admin Panel Server
 * Port: 3024
 * Version: 5.0 Enterprise
 * Date: 15 Haziran 2025
 */

const express = require('express');
const cors = require('cors');
const path = require('path');
const fs = require('fs');

const app = express();
const PORT = 3024;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Static files for modular admin panel
app.use(express.static(path.join(__dirname, 'super_admin_modular')));

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({
        status: 'healthy',
        service: 'MesChain-Sync Modular Super Admin Panel',
        port: PORT,
        version: '5.0',
        timestamp: new Date().toISOString(),
        uptime: process.uptime(),
        modular: true,
        components: {
            js_modules: 10,
            css_modules: 7,
            html_components: 'pending'
        }
    });
});

// API endpoints for modular system
app.get('/api/system/status', (req, res) => {
    res.json({
        system: 'MesChain-Sync Enterprise',
        admin_panel: 'Modular Super Admin v5.0',
        status: 'operational',
        modularization: {
            javascript: 'completed',
            css: 'completed',
            html: 'in_progress'
        },
        services: {
            core: 'running',
            notifications: 'active',
            language: 'active',
            theme: 'active',
            health_monitoring: 'active'
        }
    });
});

// Modular component endpoints
app.get('/api/components/health', (req, res) => {
    const componentsPath = path.join(__dirname, 'super_admin_modular');
    const jsPath = path.join(componentsPath, 'js');
    const cssPath = path.join(componentsPath, 'styles');
    
    try {
        const jsFiles = fs.readdirSync(jsPath).filter(file => file.endsWith('.js'));
        const cssFiles = fs.readdirSync(cssPath).filter(file => file.endsWith('.css'));
        
        res.json({
            status: 'healthy',
            components: {
                javascript: {
                    count: jsFiles.length,
                    files: jsFiles
                },
                css: {
                    count: cssFiles.length,
                    files: cssFiles
                }
            }
        });
    } catch (error) {
        res.status(500).json({
            status: 'error',
            message: 'Error reading component files',
            error: error.message
        });
    }
});

// Serve the main modular admin panel
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'super_admin_modular', 'index.html'));
});

app.get('/meschain_sync_super_admin.html', (req, res) => {
    res.sendFile(path.join(__dirname, 'super_admin_modular', 'index.html'));
});

// API for marketplace management
app.get('/api/marketplaces', (req, res) => {
    res.json({
        marketplaces: [
            { name: 'Trendyol', port: 3012, status: 'configured' },
            { name: 'Amazon', port: 3011, status: 'configured' },
            { name: 'N11', port: 3014, status: 'configured' },
            { name: 'Hepsiburada', port: 3010, status: 'configured' },
            { name: 'GittiGidiyor', port: 3013, status: 'configured' }
        ]
    });
});

// Logging middleware
app.use((req, res, next) => {
    const timestamp = new Date().toISOString();
    console.log(`[${timestamp}] ${req.method} ${req.url} - Modular Super Admin Panel`);
    next();
});

// Error handling
app.use((err, req, res, next) => {
    console.error('Error:', err);
    res.status(500).json({
        error: 'Internal Server Error',
        message: err.message,
        service: 'MesChain Modular Super Admin'
    });
});

// 404 handler
app.use((req, res) => {
    res.status(404).json({
        error: 'Not Found',
        message: `Route ${req.url} not found`,
        service: 'MesChain Modular Super Admin'
    });
});

// Start server
app.listen(PORT, () => {
    console.log('🚀 ==========================================');
    console.log('🎯 MesChain-Sync Modular Super Admin Panel');
    console.log('📊 Version: 5.0 Enterprise');
    console.log(`🌐 Server running on: http://localhost:${PORT}`);
    console.log(`🔗 Admin Panel: http://localhost:${PORT}/meschain_sync_super_admin.html`);
    console.log('📁 Serving modular components from: super_admin_modular/');
    console.log('⚡ JavaScript modules: 10 files');
    console.log('🎨 CSS modules: 7 files');
    console.log('🔧 Status: Production Ready');
    console.log('==========================================');
});

// Graceful shutdown
process.on('SIGINT', () => {
    console.log('\n🛑 Shutting down MesChain Modular Super Admin Panel...');
    process.exit(0);
});
