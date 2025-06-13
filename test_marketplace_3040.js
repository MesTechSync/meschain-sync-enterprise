// Minimal test for Advanced Marketplace Engine
const express = require('express');
const cors = require('cors');

const app = express();
const PORT = 3040;

app.use(cors());
app.use(express.json());

// Test endpoint
app.get('/test', (req, res) => {
    res.json({ success: true, message: 'Test endpoint working' });
});

// Health check endpoint
app.get('/health', (req, res) => {
    console.log('Health check requested');
    res.json({
        success: true,
        service: 'Advanced Marketplace Engine',
        port: PORT,
        status: 'healthy',
        timestamp: new Date().toISOString(),
        uptime: process.uptime()
    });
});

app.listen(PORT, () => {
    console.log(`🏪 Test Marketplace Engine running on port ${PORT}`);
    console.log(`📊 Health check: http://localhost:${PORT}/health`);
});

// Log any errors
process.on('uncaughtException', (error) => {
    console.error('Uncaught Exception:', error);
});

process.on('unhandledRejection', (reason, promise) => {
    console.error('Unhandled Rejection at:', promise, 'reason:', reason);
});
