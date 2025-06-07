const express = require('express');
const path = require('path');
const fs = require('fs');

const app = express();
const PORT = 3007;

// Static files için middleware
app.use(express.static(path.join(__dirname, 'CursorDev/FRONTEND_COMPONENTS')));
app.use(express.static(path.join(__dirname, 'upload')));

// Ana route - orijinal technical panel
app.get('/', (req, res) => {
    const htmlPath = path.join(__dirname, 'original_light_technical.html');
    if (fs.existsSync(htmlPath)) {
        res.sendFile(htmlPath);
    } else {
        res.status(404).send('Original Light Technical Panel not found');
    }
});

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({ 
        status: 'OK', 
        message: 'Original Technical Panel Server Running',
        port: PORT,
        timestamp: new Date().toISOString()
    });
});

// CORS için middleware (diğer portlardan erişim için)
app.use((req, res, next) => {
    res.header('Access-Control-Allow-Origin', '*');
    res.header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    res.header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
    next();
});

// Error handling
app.use((err, req, res, next) => {
    console.error('Server Error:', err);
    res.status(500).json({ error: 'Internal Server Error' });
});

// 404 handler
app.use((req, res) => {
    res.status(404).json({ error: 'Not Found' });
});

// Server başlatma
app.listen(PORT, () => {
    console.log('🔧 Original Technical Panel Server Started');
    console.log(`📍 URL: http://localhost:${PORT}`);
    console.log(`🎨 Mode: Light Theme (Original) - Purple Scheme`);
    console.log(`⚡ Status: Running without affecting current setup`);
    console.log('━'.repeat(60));
});

// Graceful shutdown
process.on('SIGINT', () => {
    console.log('\n🔴 Original Technical Panel Server Shutting Down...');
    process.exit(0);
});

module.exports = app;
