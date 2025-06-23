const express = require('express');
const cors = require('cors');
const path = require('path');

const app = express();
const PORT = 6012;

// Enable CORS for all requests
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Serve static files
app.use(express.static(path.join(__dirname, 'public')));
app.use('/CursorDev', express.static(path.join(__dirname, 'CursorDev')));

// Load Advanced Rate Limiting System
const AdvancedRateLimitingSystem = require('./api_rate_limiting_system');
const rateLimiting = new AdvancedRateLimitingSystem();

// Apply rate limiting middleware (before authentication)
rateLimiting.setupMiddleware(app);

// Load Priority 3 Authentication Middleware
const Priority3AuthMiddleware = require('./priority3_auth_middleware');

// Initialize authentication
const auth = new Priority3AuthMiddleware({
    serviceName: 'Trendyol Seller Hub Server',
    serviceType: 'trendyol_seller',
    port: PORT,
    requiredRoles: ['super_admin', 'admin', 'marketplace_manager'],
    permissions: {'super_admin': ['*'], 'admin': ['trendyol', 'commissions', 'analytics'], 'marketplace_manager': ['trendyol', 'analytics']}
});

// Authentication middleware
const authenticateUser = auth.requireAuth();

// Login and logout routes
app.get('/login', auth.getLoginPage());
app.post('/login', auth.handleLogin());
app.post('/logout', auth.handleLogout());

// Protected routes - require authentication
app.get('/', authenticateUser, (req, res) => {
    const supportDashboardHTML = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
