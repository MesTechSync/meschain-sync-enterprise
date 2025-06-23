const express = require('express');
const path = require('path');
const cors = require('cors');
const app = express();
const PORT = 3017;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static(path.join(__dirname, 'CursorDev/FRONTEND_COMPONENTS')));

// Serve super admin dashboard
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'CursorDev/FRONTEND_COMPONENTS/super_admin_dashboard.html'));
});

// Handle the specific dist/html path
app.get('/CursorDev/dist/html/super_admin_dashboard.html', (req, res) => {
    res.sendFile(path.join(__dirname, 'CursorDev/FRONTEND_COMPONENTS/super_admin_dashboard.html'));
});

// Serve static files from CursorDev directory
app.use('/CursorDev', express.static(path.join(__dirname, 'CursorDev')));

// API routes - proxy to main backend server on port 8080
app.use('/api', (req, res) => {
    const axios = require('axios');
    const backendUrl = `http://localhost:8080${req.originalUrl}`;
    
    axios({
        method: req.method,
        url: backendUrl,
        data: req.body,
        headers: req.headers
    }).then(response => {
        res.json(response.data);
    }).catch(error => {
        console.error('API proxy error:', error.message);
        res.status(500).json({ error: 'Backend API connection failed' });
    });
});

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({ 
        status: 'OK', 
        service: 'Super Admin Panel - Advanced Cross-Marketplace',
        port: PORT,
        timestamp: new Date().toISOString()
    });
});

app.listen(PORT, () => {
    console.log(`🚀 Super Admin Panel Server running on http://localhost:${PORT}`);
    console.log(`📊 Dashboard available at: http://localhost:${PORT}`);
    console.log(`🔗 Backend API proxy: http://localhost:${PORT}/api/*`);
    console.log(`🎯 Service: Advanced Cross-Marketplace Management`);
});
