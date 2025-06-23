// MesChain Real-time Features Server - Port 3039
// HIGH PRIORITY SERVICE - Created: June 11, 2025

const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const cors = require('cors');

const app = express();
const server = http.createServer(app);
const io = socketIo(server, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"]
    }
});

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
        requestsPerMinute: 0
    },
    marketplaceUpdates: []
};

// Health Check
app.get('/health', (req, res) => {
    res.status(200).json({
        status: 'healthy',
        service: 'Real-time Features',
        port: PORT,
        timestamp: new Date().toISOString(),
        uptime: process.uptime(),
        connectedClients: realtimeData.connectedUsers,
        version: '1.0.0'
    });
});

// REST API Endpoints
app.get('/api/realtime/stats', (req, res) => {
    res.json({
        success: true,
        data: realtimeData.systemStats,
        connectedUsers: realtimeData.connectedUsers,
        timestamp: new Date().toISOString()
    });
});

app.get('/api/realtime/notifications', (req, res) => {
    res.json({
        success: true,
        data: realtimeData.notifications.slice(-50), // Son 50 bildirim
        total: realtimeData.notifications.length
    });
});

app.post('/api/realtime/broadcast', (req, res) => {
    const { type, message, data } = req.body;
    
    const notification = {
        id: `NOTIF-${Date.now()}`,
        type: type || 'info',
        message,
        data: data || {},
        timestamp: new Date().toISOString()
    };
    
    realtimeData.notifications.push(notification);
    
    // Broadcast to all connected clients
    io.emit('notification', notification);
    
    res.json({
        success: true,
        data: notification,
        message: 'Notification broadcasted successfully'
    });
});

app.get('/api/realtime/orders/active', (req, res) => {
    res.json({
        success: true,
        data: realtimeData.activeOrders,
        total: realtimeData.activeOrders.length
    });
});

// Socket.IO Real-time Events
io.on('connection', (socket) => {
    console.log(`✅ New client connected: ${socket.id}`);
    realtimeData.connectedUsers++;
    realtimeData.systemStats.activeConnections = realtimeData.connectedUsers;
    
    // Send welcome message
    socket.emit('welcome', {
        message: 'Connected to MesChain Real-time Server',
        clientId: socket.id,
        timestamp: new Date().toISOString()
    });
    
    // Broadcast user count update
    io.emit('userCount', realtimeData.connectedUsers);
    
    // Handle marketplace updates
    socket.on('marketplace:update', (data) => {
        const update = {
            id: `UPD-${Date.now()}`,
            type: 'marketplace',
            data,
            timestamp: new Date().toISOString(),
            source: socket.id
        };
        
        realtimeData.marketplaceUpdates.push(update);
        
        // Broadcast to all other clients
        socket.broadcast.emit('marketplace:updated', update);
        
        console.log(`📦 Marketplace update from ${socket.id}:`, data);
    });
    
    // Handle order updates
    socket.on('order:update', (orderData) => {
        const existingOrderIndex = realtimeData.activeOrders.findIndex(
            order => order.id === orderData.id
        );
        
        if (existingOrderIndex !== -1) {
            realtimeData.activeOrders[existingOrderIndex] = {
                ...realtimeData.activeOrders[existingOrderIndex],
                ...orderData,
                lastUpdated: new Date().toISOString()
            };
        } else {
            realtimeData.activeOrders.push({
                ...orderData,
                created: new Date().toISOString(),
                lastUpdated: new Date().toISOString()
            });
        }
        
        // Broadcast order update
        io.emit('order:updated', orderData);
        
        console.log(`📋 Order update:`, orderData);
    });
    
    // Handle system monitoring
    socket.on('system:monitor', (data) => {
        realtimeData.systemStats = {
            ...realtimeData.systemStats,
            ...data,
            timestamp: new Date().toISOString()
        };
        
        // Broadcast system stats
        io.emit('system:stats', realtimeData.systemStats);
    });
    
    // Handle chat messages
    socket.on('chat:message', (messageData) => {
        const chatMessage = {
            id: `MSG-${Date.now()}`,
            ...messageData,
            timestamp: new Date().toISOString(),
            socketId: socket.id
        };
        
        // Broadcast chat message
        io.emit('chat:newMessage', chatMessage);
        
        console.log(`💬 Chat message:`, chatMessage);
    });
    
    // Handle client disconnect
    socket.on('disconnect', () => {
        console.log(`❌ Client disconnected: ${socket.id}`);
        realtimeData.connectedUsers--;
        realtimeData.systemStats.activeConnections = realtimeData.connectedUsers;
        
        // Broadcast updated user count
        io.emit('userCount', realtimeData.connectedUsers);
    });
    
    // Handle ping for connection testing
    socket.on('ping', (callback) => {
        if (callback) {
            callback({
                timestamp: new Date().toISOString(),
                server: 'MesChain Real-time Server',
                port: PORT
            });
        }
    });
});

// Periodic system updates
setInterval(() => {
    // Simulate system stats updates
    realtimeData.systemStats = {
        ...realtimeData.systemStats,
        cpuUsage: Math.random() * 100,
        memoryUsage: Math.random() * 100,
        requestsPerMinute: Math.floor(Math.random() * 1000),
        timestamp: new Date().toISOString()
    };
    
    // Broadcast system stats every 30 seconds
    io.emit('system:stats', realtimeData.systemStats);
}, 30000);

// Cleanup old notifications (keep last 1000)
setInterval(() => {
    if (realtimeData.notifications.length > 1000) {
        realtimeData.notifications = realtimeData.notifications.slice(-1000);
    }
}, 300000); // Every 5 minutes

// Error handling
app.use((err, req, res, next) => {
    console.error('Real-time Server Error:', err);
    res.status(500).json({
        success: false,
        error: 'Internal Server Error',
        message: 'An error occurred in real-time server'
    });
});

// 404 handler
app.use((req, res) => {
    res.status(404).json({
        success: false,
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
    console.log(`🌐 Socket.IO endpoint: ws://localhost:${PORT}`);
    console.log(`⏰ Started at: ${new Date().toISOString()}`);
});

module.exports = { app, server, io };
