// MesChain Advanced Marketplace Engine - Port 3041 (Temporary)
// ISOLATED TEST VERSION - Created: June 12, 2025

const express = require('express');
const cors = require('cors');

const app = express();
const PORT = 3041;

// Basic middleware
app.use(cors());
app.use(express.json());

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({
        success: true,
        service: 'Advanced Marketplace Engine (Test)',
        port: PORT,
        status: 'healthy',
        timestamp: new Date().toISOString(),
        uptime: process.uptime()
    });
});

// Simple API endpoint
app.get('/api/status', (req, res) => {
    res.json({
        success: true,
        message: 'Marketplace engine is running',
        timestamp: new Date().toISOString()
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`🏪 Test Marketplace Engine running on port ${PORT}`);
    console.log(`📊 Health check: http://localhost:${PORT}/health`);
});

module.exports = app;
