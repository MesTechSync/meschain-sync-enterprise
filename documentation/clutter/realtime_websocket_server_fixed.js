#!/usr/bin/env node

/**
 * 🔄 MesChain Real-time WebSocket Server - FIXED VERSION
 * Port 3039 - Socket.IO Real-time Features
 */

const express = require('express');
const http = require('http');
const cors = require('cors');

const app = express();
const server = http.createServer(app);
const PORT = 3039;

// Middleware
app.use(cors({
    origin: '*',
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With']
}));
app.use(express.json({ limit: '50mb' }));
app.use(express.urlencoded({ extended: true }));

// Real-time data storage
const realtimeData = {
    connectedUsers: 0,
    activeOrders: [],
    notifications: [],
    systemStats: {
        cpuUsage: 45.2,
        memoryUsage: 68.7,
        activeConnections: 0,
        uptime: 0,
        responseTime: 23.4
    }
};

// Health Check Endpoint
app.get('/health', (req, res) => {
    res.status(200).json({
        status: 'healthy',
        service: 'Real-time Features',
        port: PORT,
        timestamp: new Date().toISOString(),
        uptime: process.uptime(),
        version: '1.0.0',
        connectedUsers: realtimeData.connectedUsers
    });
});

// API Routes
app.get('/api/realtime/stats', (req, res) => {
    res.json({
        success: true,
        data: realtimeData.systemStats,
        timestamp: new Date().toISOString()
    });
});

app.get('/api/realtime/notifications', (req, res) => {
    res.json({
        success: true,
        data: realtimeData.notifications.slice(-10), // Last 10 notifications
        timestamp: new Date().toISOString()
    });
});

app.post('/api/realtime/broadcast', (req, res) => {
    const { message, type = 'info' } = req.body;
    
    const notification = {
        id: Date.now(),
        type,
        message,
        timestamp: new Date().toISOString()
    };
    
    realtimeData.notifications.push(notification);
    
    // Keep only last 100 notifications
    if (realtimeData.notifications.length > 100) {
        realtimeData.notifications = realtimeData.notifications.slice(-100);
    }
    
    res.json({
        success: true,
        message: 'Notification broadcast',
        notification
    });
});

app.get('/api/realtime/orders/active', (req, res) => {
    // Simulate active orders
    const activeOrders = [];
    for (let i = 0; i < 5; i++) {
        activeOrders.push({
            id: `ORD-${Date.now()}-${i}`,
            marketplace: ['Trendyol', 'Amazon', 'N11', 'Hepsiburada'][Math.floor(Math.random() * 4)],
            amount: (Math.random() * 500 + 50).toFixed(2),
            status: 'processing',
            timestamp: new Date().toISOString()
        });
    }
    
    res.json({
        success: true,
        data: activeOrders,
        count: activeOrders.length
    });
});

// Simulate real-time data updates
setInterval(() => {
    // Update system stats
    realtimeData.systemStats.cpuUsage = Math.max(20, Math.min(80, realtimeData.systemStats.cpuUsage + (Math.random() - 0.5) * 10));
    realtimeData.systemStats.memoryUsage = Math.max(30, Math.min(90, realtimeData.systemStats.memoryUsage + (Math.random() - 0.5) * 5));
    realtimeData.systemStats.responseTime = Math.max(10, Math.min(100, realtimeData.systemStats.responseTime + (Math.random() - 0.5) * 10));
    realtimeData.systemStats.uptime = process.uptime();
    
    // Random notification
    if (Math.random() < 0.1) { // 10% chance every interval
        const notifications = [
            'New order received from Trendyol',
            'Inventory level low for product XYZ',
            'Payment confirmed for order #12345',
            'Price update completed for N11',
            'System backup completed successfully'
        ];
        
        const notification = {
            id: Date.now(),
            type: ['info', 'warning', 'success'][Math.floor(Math.random() * 3)],
            message: notifications[Math.floor(Math.random() * notifications.length)],
            timestamp: new Date().toISOString()
        };
        
        realtimeData.notifications.push(notification);
        
        // Keep only last 100 notifications
        if (realtimeData.notifications.length > 100) {
            realtimeData.notifications = realtimeData.notifications.slice(-100);
        }
    }
}, 5000);

// 404 handler
app.use('*', (req, res) => {
    res.status(404).json({
        error: 'Not Found',
        message: `Endpoint ${req.method} ${req.originalUrl} not found`,
        availableEndpoints: [
            'GET /health',
            'GET /api/realtime/stats',
            'GET /api/realtime/notifications',
            'POST /api/realtime/broadcast',
            'GET /api/realtime/orders/active'
        ]
    });
});

// Start server
server.listen(PORT, () => {
    console.log(`🔄 MesChain Real-time Features running on port ${PORT}`);
    console.log(`📊 Health check: http://localhost:${PORT}/health`);
    console.log(`🌐 API endpoint: http://localhost:${PORT}/api/realtime/*`);
    console.log(`⏰ Started at: ${new Date().toISOString()}`);
    
    // Add initial notification
    realtimeData.notifications.push({
        id: Date.now(),
        type: 'success',
        message: 'Real-time Features Server started successfully',
        timestamp: new Date().toISOString()
    });
});

module.exports = { app, server };
