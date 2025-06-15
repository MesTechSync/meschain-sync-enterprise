const express = require('express');
const path = require('path');
const cors = require('cors');

const app = express();
const PORT = 3023;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static(__dirname));

// Serve Super Admin Login
app.get('/', (req, res) => {
    const filePath = path.join(__dirname, 'port_3002_super_admin_with_login.html');
    res.sendFile(filePath);
});

// Login endpoint
app.get('/login', (req, res) => {
    const filePath = path.join(__dirname, 'port_3002_super_admin_with_login.html');
    res.sendFile(filePath);
});

// API endpoints
app.get('/api/status', (req, res) => {
    res.json({
        success: true,
        service: 'Super Admin Login Panel',
        port: PORT,
        status: 'active',
        timestamp: new Date().toISOString(),
        description: 'Super Admin Authentication System'
    });
});

// Authentication API
app.post('/api/auth/login', (req, res) => {
    const { username, password } = req.body;
    
    // Demo authentication
    if (username === 'superadmin' && password === 'admin123') {
        res.json({
            success: true,
            message: 'Login successful',
            token: 'demo_token_' + Date.now(),
            user: {
                username: 'superadmin',
                role: 'super_admin',
                permissions: ['all']
            }
        });
    } else {
        res.status(401).json({
            success: false,
            message: 'Invalid credentials'
        });
    }
});

// Start server
app.listen(PORT, () => {
    console.log(`🔐 ═══════════════════════════════════════════════════════════════`);
    console.log(`👑 SÜPER ADMİN LOGİN PANELİ BAŞLADI!`);
    console.log(`🔐 ═══════════════════════════════════════════════════════════════`);
    console.log(`🌐 Panel URL: http://localhost:${PORT}`);
    console.log(`🔑 Login URL: http://localhost:${PORT}/login`);
    console.log(`🔗 API Status: http://localhost:${PORT}/api/status`);
    console.log(`📊 Auth API: http://localhost:${PORT}/api/auth/login`);
    console.log(`👤 Demo Kullanıcı: superadmin / admin123`);
    console.log(`🔐 ═══════════════════════════════════════════════════════════════`);
});

// Graceful shutdown
process.on('SIGINT', () => {
    console.log('\n🛑 Süper Admin Login Panel durduruluyor...');
    process.exit(0);
});

process.on('SIGTERM', () => {
    console.log('\n🛑 Süper Admin Login Panel stopping...');
    process.exit(0);
});
