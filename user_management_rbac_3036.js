// MesChain User Management & RBAC Server - Port 3036
// CRITICAL SERVICE - Created: June 11, 2025

const express = require('express');
const cors = require('cors');
const jwt = require('jsonwebtoken');
const bcrypt = require('bcryptjs');

const app = express();
const PORT = 3036;
const JWT_SECRET = process.env.JWT_SECRET || 'meschain-ultra-secret-key-2025';

// Middleware
app.use(cors({
    origin: '*',
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization', 'X-Requested-With']
}));
app.use(express.json({ limit: '50mb' }));
app.use(express.urlencoded({ extended: true }));

// Mock Users Database
const users = [
    {
        id: 'USR-001',
        username: 'superadmin',
        email: 'admin@meschain.com',
        password: '$2a$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewdBPj56h6/LDBC6', // password123
        role: 'super_admin',
        permissions: ['*'],
        status: 'active',
        created: '2025-01-01T00:00:00Z'
    },
    {
        id: 'USR-002',
        username: 'admin',
        email: 'admin@company.com',
        password: '$2a$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewdBPj56h6/LDBC6',
        role: 'admin',
        permissions: ['users:read', 'users:write', 'products:*', 'orders:*'],
        status: 'active',
        created: '2025-01-02T00:00:00Z'
    }
];

// JWT Middleware
const authenticateToken = (req, res, next) => {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1];

    if (!token) {
        return res.status(401).json({ success: false, error: 'Access token required' });
    }

    jwt.verify(token, JWT_SECRET, (err, user) => {
        if (err) {
            return res.status(403).json({ success: false, error: 'Invalid or expired token' });
        }
        req.user = user;
        next();
    });
};

// Health Check
app.get('/health', (req, res) => {
    res.status(200).json({
        status: 'healthy',
        service: 'User Management & RBAC',
        port: PORT,
        timestamp: new Date().toISOString(),
        uptime: process.uptime(),
        version: '1.0.0'
    });
});

// Authentication Endpoints
app.post('/auth/login', async (req, res) => {
    const { username, password } = req.body;

    try {
        const user = users.find(u => u.username === username && u.status === 'active');
        
        if (!user) {
            return res.status(401).json({
                success: false,
                error: 'Invalid credentials'
            });
        }

        const isValidPassword = await bcrypt.compare(password, user.password);
        
        if (!isValidPassword) {
            return res.status(401).json({
                success: false,
                error: 'Invalid credentials'
            });
        }

        const token = jwt.sign(
            { 
                id: user.id, 
                username: user.username, 
                role: user.role,
                permissions: user.permissions 
            },
            JWT_SECRET,
            { expiresIn: '24h' }
        );

        res.json({
            success: true,
            data: {
                token,
                user: {
                    id: user.id,
                    username: user.username,
                    email: user.email,
                    role: user.role,
                    permissions: user.permissions
                }
            },
            message: 'Login successful'
        });

    } catch (error) {
        res.status(500).json({
            success: false,
            error: 'Login failed',
            message: error.message
        });
    }
});

app.post('/auth/logout', authenticateToken, (req, res) => {
    res.json({
        success: true,
        message: 'Logout successful'
    });
});

app.get('/auth/me', authenticateToken, (req, res) => {
    const user = users.find(u => u.id === req.user.id);
    
    if (!user) {
        return res.status(404).json({
            success: false,
            error: 'User not found'
        });
    }

    res.json({
        success: true,
        data: {
            id: user.id,
            username: user.username,
            email: user.email,
            role: user.role,
            permissions: user.permissions,
            status: user.status
        }
    });
});

// User Management Endpoints
app.get('/api/users', authenticateToken, (req, res) => {
    // Check permission
    if (!req.user.permissions.includes('*') && !req.user.permissions.includes('users:read')) {
        return res.status(403).json({
            success: false,
            error: 'Insufficient permissions'
        });
    }

    const safeUsers = users.map(user => ({
        id: user.id,
        username: user.username,
        email: user.email,
        role: user.role,
        status: user.status,
        created: user.created
    }));

    res.json({
        success: true,
        data: safeUsers,
        total: safeUsers.length
    });
});

app.post('/api/users', authenticateToken, (req, res) => {
    // Check permission
    if (!req.user.permissions.includes('*') && !req.user.permissions.includes('users:write')) {
        return res.status(403).json({
            success: false,
            error: 'Insufficient permissions'
        });
    }

    const { username, email, password, role } = req.body;

    // Basic validation
    if (!username || !email || !password || !role) {
        return res.status(400).json({
            success: false,
            error: 'Missing required fields'
        });
    }

    // Check if user already exists
    if (users.find(u => u.username === username || u.email === email)) {
        return res.status(409).json({
            success: false,
            error: 'User already exists'
        });
    }

    const newUser = {
        id: `USR-${String(users.length + 1).padStart(3, '0')}`,
        username,
        email,
        password: bcrypt.hashSync(password, 12),
        role,
        permissions: role === 'admin' ? ['users:read', 'products:*'] : ['products:read'],
        status: 'active',
        created: new Date().toISOString()
    };

    users.push(newUser);

    res.status(201).json({
        success: true,
        data: {
            id: newUser.id,
            username: newUser.username,
            email: newUser.email,
            role: newUser.role,
            status: newUser.status
        },
        message: 'User created successfully'
    });
});

// Role-Based Access Control
app.get('/api/rbac/roles', authenticateToken, (req, res) => {
    const roles = [
        {
            name: 'super_admin',
            label: 'Super Administrator',
            permissions: ['*'],
            description: 'Full system access'
        },
        {
            name: 'admin',
            label: 'Administrator',
            permissions: ['users:read', 'users:write', 'products:*', 'orders:*'],
            description: 'Administrative access'
        },
        {
            name: 'user',
            label: 'Regular User',
            permissions: ['products:read', 'orders:read'],
            description: 'Basic user access'
        }
    ];

    res.json({
        success: true,
        data: roles
    });
});

app.get('/api/rbac/permissions', authenticateToken, (req, res) => {
    const permissions = [
        { name: '*', description: 'All permissions' },
        { name: 'users:read', description: 'Read users' },
        { name: 'users:write', description: 'Create/update users' },
        { name: 'products:read', description: 'Read products' },
        { name: 'products:write', description: 'Create/update products' },
        { name: 'products:*', description: 'All product permissions' },
        { name: 'orders:read', description: 'Read orders' },
        { name: 'orders:write', description: 'Create/update orders' },
        { name: 'orders:*', description: 'All order permissions' }
    ];

    res.json({
        success: true,
        data: permissions
    });
});

// Error handling
app.use((err, req, res, next) => {
    console.error('RBAC Error:', err);
    res.status(500).json({
        success: false,
        error: 'Internal Server Error',
        message: 'An error occurred in user management system'
    });
});

// 404 handler
app.use('*', (req, res) => {
    res.status(404).json({
        success: false,
        error: 'Not Found',
        message: `Endpoint ${req.method} ${req.originalUrl} not found`
    });
});

// Start server
app.listen(PORT, () => {
    console.log(`🔐 MesChain User Management & RBAC running on port ${PORT}`);
    console.log(`📊 Health check: http://localhost:${PORT}/health`);
    console.log(`🔑 Auth endpoint: http://localhost:${PORT}/auth/login`);
    console.log(`⏰ Started at: ${new Date().toISOString()}`);
});

module.exports = app;
