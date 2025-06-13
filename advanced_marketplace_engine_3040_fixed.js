// MesChain Advanced Marketplace Engine - Port 3040
// FIXED VERSION - Created: June 12, 2025

const express = require('express');
const cors = require('cors');
const fs = require('fs');
const path = require('path');

const app = express();
const PORT = 3040;

// Middleware
app.use(cors({
    origin: '*',
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With']
}));
app.use(express.json({ limit: '50mb' }));
app.use(express.urlencoded({ extended: true }));

// Marketplace integrations
const marketplaces = {
    trendyol: {
        name: 'Trendyol',
        status: 'active',
        apiVersion: 'v2',
        lastSync: new Date().toISOString(),
        totalProducts: 1247,
        totalOrders: 89
    },
    amazon: {
        name: 'Amazon TR',
        status: 'active',
        apiVersion: 'v3',
        lastSync: new Date().toISOString(),
        totalProducts: 856,
        totalOrders: 156
    },
    n11: {
        name: 'N11',
        status: 'paused',
        apiVersion: 'v1',
        lastSync: new Date(Date.now() - 3600000).toISOString(),
        totalProducts: 445,
        totalOrders: 23
    },
    ebay: {
        name: 'eBay',
        status: 'active',
        apiVersion: 'v4',
        lastSync: new Date().toISOString(),
        totalProducts: 332,
        totalOrders: 41
    }
};

// Health Check
app.get('/health', (req, res) => {
    res.status(200).json({
        success: true,
        service: 'Advanced Marketplace Engine',
        port: PORT,
        status: 'healthy',
        timestamp: new Date().toISOString(),
        uptime: process.uptime(),
        marketplaces: Object.keys(marketplaces).length,
        activeIntegrations: Object.values(marketplaces).filter(m => m.status === 'active').length
    });
});

// Marketplace Management
app.get('/api/marketplaces', (req, res) => {
    res.json({
        success: true,
        data: marketplaces,
        summary: {
            total: Object.keys(marketplaces).length,
            active: Object.keys(marketplaces).filter(m => marketplaces[m].status === 'active').length,
            totalProducts: Object.values(marketplaces).reduce((sum, m) => sum + m.totalProducts, 0),
            totalOrders: Object.values(marketplaces).reduce((sum, m) => sum + m.totalOrders, 0)
        }
    });
});

app.get('/api/marketplaces/:marketplace', (req, res) => {
    const { marketplace } = req.params;
    
    if (!marketplaces[marketplace]) {
        return res.status(404).json({
            success: false,
            error: 'Marketplace not found'
        });
    }
    
    res.json({
        success: true,
        data: marketplaces[marketplace]
    });
});

// Product Sync
app.get('/api/products/sync/:marketplace', (req, res) => {
    const { marketplace } = req.params;
    
    if (!marketplaces[marketplace]) {
        return res.status(404).json({
            success: false,
            error: 'Marketplace not found'
        });
    }
    
    // Simulate sync process
    setTimeout(() => {
        marketplaces[marketplace].lastSync = new Date().toISOString();
        marketplaces[marketplace].totalProducts += Math.floor(Math.random() * 10);
    }, 100);
    
    res.json({
        success: true,
        message: `Product sync initiated for ${marketplace}`,
        marketplace: marketplaces[marketplace]
    });
});

// Error handling
app.use((err, req, res, next) => {
    console.error('Marketplace Engine Error:', err);
    res.status(500).json({
        success: false,
        error: 'Internal Server Error',
        message: 'An error occurred in marketplace engine'
    });
});

// 404 handler
app.use((req, res) => {
    res.status(404).json({
        success: false,
        error: 'Not Found',
        message: `Endpoint ${req.method} ${req.originalUrl} not found`
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`🏪 MesChain Advanced Marketplace Engine running on port ${PORT}`);
    console.log(`📊 Health check: http://localhost:${PORT}/health`);
    console.log(`🔗 API Base: http://localhost:${PORT}/api`);
    console.log(`⏰ Started at: ${new Date().toISOString()}`);
});

module.exports = app;
