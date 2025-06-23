const express = require('express');
const cors = require('cors');
const WebSocket = require('ws');
const http = require('http');
const crypto = require('crypto');

const app = express();
const PORT = 3030;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static('.'));

// Create HTTP server and WebSocket server
const server = http.createServer(app);
const wss = new WebSocket.Server({ server });

// Security metrics storage
let securityMetrics = {
    score: 94,
    threats: {
        high: 1,
        medium: 2,
        low: 3
    },
    compliance: {
        gdpr: 98,
        pci_dss: 95,
        iso27001: 87,
        soc2: 92
    },
    access: {
        activeSessions: 127,
        failedLogins: 23,
        adminUsers: 8,
        twoFactorEnabled: 89
    },
    network: {
        firewallStatus: 'active',
        ddosProtection: true,
        sslGrade: 'A+',
        vpnConnections: 45
    }
};

// Security events storage
let securityEvents = [];
let threatIntelligence = new Map();

// WebSocket connection handling
wss.on('connection', (ws) => {
    console.log('🔒 Security Center client connected');
    
    // Send initial security status
    ws.send(JSON.stringify({
        type: 'security_status',
        data: securityMetrics
    }));
    
    ws.on('message', (message) => {
        try {
            const data = JSON.parse(message);
            handleSecurityMessage(ws, data);
        } catch (error) {
            console.error('Invalid message format:', error);
        }
    });
    
    ws.on('close', () => {
        console.log('🔒 Security Center client disconnected');
    });
});

function handleSecurityMessage(ws, data) {
    switch (data.action) {
        case 'start_scan':
            startSecurityScan(ws);
            break;
        case 'generate_report':
            generateSecurityReport(ws);
            break;
        case 'audit_compliance':
            auditCompliance(ws);
            break;
        case 'manage_threats':
            manageThreat(ws, data.threatId);
            break;
    }
}

// Security scan simulation
function startSecurityScan(ws) {
    const scanId = crypto.randomUUID();
    let progress = 0;
    
    const scanComponents = [
        'Network ports',
        'File system integrity',
        'User permissions',
        'SSL certificates',
        'Database security',
        'API endpoints',
        'Application dependencies',
        'Backup systems'
    ];
    
    ws.send(JSON.stringify({
        type: 'scan_started',
        scanId: scanId,
        message: 'Comprehensive security scan initiated'
    }));
    
    const scanInterval = setInterval(() => {
        const component = scanComponents[Math.floor(progress / 12.5)];
        progress += Math.random() * 15 + 5;
        
        if (progress >= 100) {
            clearInterval(scanInterval);
            completeScan(ws, scanId);
        } else {
            ws.send(JSON.stringify({
                type: 'scan_progress',
                scanId: scanId,
                progress: Math.floor(progress),
                component: component
            }));
        }
    }, 800);
}

function completeScan(ws, scanId) {
    const vulnerabilities = Math.floor(Math.random() * 3);
    const findings = generateScanFindings(vulnerabilities);
    
    securityMetrics.score = Math.max(85, 100 - vulnerabilities * 5);
    
    ws.send(JSON.stringify({
        type: 'scan_complete',
        scanId: scanId,
        score: securityMetrics.score,
        vulnerabilities: vulnerabilities,
        findings: findings
    }));
    
    // Broadcast updated metrics to all clients
    broadcastToAll({
        type: 'metrics_update',
        data: securityMetrics
    });
}

function generateScanFindings(vulnerabilityCount) {
    const possibleFindings = [
        { severity: 'low', description: 'Outdated JavaScript library detected', recommendation: 'Update to latest version' },
        { severity: 'medium', description: 'Weak password policy detected', recommendation: 'Enforce stronger password requirements' },
        { severity: 'high', description: 'Unencrypted data transmission', recommendation: 'Enable TLS encryption' },
        { severity: 'low', description: 'Missing security headers', recommendation: 'Add HSTS and CSP headers' },
        { severity: 'medium', description: 'Excessive user privileges', recommendation: 'Implement principle of least privilege' }
    ];
    
    return possibleFindings.slice(0, vulnerabilityCount);
}

// Compliance audit simulation
function auditCompliance(ws) {
    const auditId = crypto.randomUUID();
    const standards = ['GDPR', 'PCI DSS', 'ISO 27001', 'SOC 2'];
    let currentStandard = 0;
    
    ws.send(JSON.stringify({
        type: 'audit_started',
        auditId: auditId,
        message: 'Compliance audit initiated'
    }));
    
    const auditInterval = setInterval(() => {
        if (currentStandard < standards.length) {
            const standard = standards[currentStandard];
            const score = 85 + Math.random() * 15;
            
            ws.send(JSON.stringify({
                type: 'audit_progress',
                auditId: auditId,
                standard: standard,
                score: Math.floor(score),
                progress: ((currentStandard + 1) / standards.length) * 100
            }));
            
            currentStandard++;
        } else {
            clearInterval(auditInterval);
            completeAudit(ws, auditId);
        }
    }, 1500);
}

function completeAudit(ws, auditId) {
    const issues = [
        'Minor: Log retention policy needs update',
        'Advisory: Consider implementing additional access controls',
        'Info: Documentation update recommended'
    ];
    
    ws.send(JSON.stringify({
        type: 'audit_complete',
        auditId: auditId,
        overallScore: 93,
        issues: issues
    }));
}

// Threat intelligence and management
function generateThreatIntelligence() {
    const threatTypes = [
        'Brute force attack',
        'SQL injection attempt',
        'Cross-site scripting',
        'Suspicious file upload',
        'Unauthorized API access',
        'Port scanning activity',
        'Malware signature detected',
        'Phishing attempt'
    ];
    
    return {
        id: crypto.randomUUID(),
        type: threatTypes[Math.floor(Math.random() * threatTypes.length)],
        severity: ['low', 'medium', 'high'][Math.floor(Math.random() * 3)],
        source: generateRandomIP(),
        timestamp: new Date().toISOString(),
        status: 'active'
    };
}

function generateRandomIP() {
    return Array.from({length: 4}, () => Math.floor(Math.random() * 256)).join('.');
}

// API Routes
app.get('/health', (req, res) => {
    res.json({
        status: 'healthy',
        service: 'Security & Compliance Center',
        port: PORT,
        timestamp: new Date().toISOString(),
        uptime: process.uptime(),
        securityScore: securityMetrics.score
    });
});

app.get('/api/security/metrics', (req, res) => {
    res.json({
        success: true,
        data: securityMetrics
    });
});

app.get('/api/security/threats', (req, res) => {
    const threats = Array.from(threatIntelligence.values())
        .sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp))
        .slice(0, 20);
    
    res.json({
        success: true,
        data: threats
    });
});

app.post('/api/security/scan', (req, res) => {
    const scanId = crypto.randomUUID();
    
    // Simulate scan initiation
    setTimeout(() => {
        broadcastToAll({
            type: 'scan_started',
            scanId: scanId
        });
    }, 100);
    
    res.json({
        success: true,
        scanId: scanId,
        message: 'Security scan initiated'
    });
});

app.post('/api/security/report', (req, res) => {
    const reportId = crypto.randomUUID();
    const report = {
        id: reportId,
        timestamp: new Date().toISOString(),
        securityScore: securityMetrics.score,
        threats: securityMetrics.threats,
        compliance: securityMetrics.compliance,
        recommendations: [
            'Update password policy to require 12+ characters',
            'Enable multi-factor authentication for all admin accounts',
            'Implement automated security patch management',
            'Schedule regular penetration testing'
        ]
    };
    
    res.json({
        success: true,
        report: report
    });
});

app.get('/api/security/logs', (req, res) => {
    const { limit = 50, level } = req.query;
    
    let logs = securityEvents;
    if (level) {
        logs = logs.filter(log => log.level === level);
    }
    
    res.json({
        success: true,
        data: logs.slice(0, parseInt(limit))
    });
});

app.post('/api/security/logs', (req, res) => {
    const { level, message, source } = req.body;
    
    const logEntry = {
        id: crypto.randomUUID(),
        timestamp: new Date().toISOString(),
        level: level || 'info',
        message: message,
        source: source || 'system'
    };
    
    securityEvents.unshift(logEntry);
    
    // Keep only last 1000 entries
    if (securityEvents.length > 1000) {
        securityEvents = securityEvents.slice(0, 1000);
    }
    
    // Broadcast to WebSocket clients
    broadcastToAll({
        type: 'log_entry',
        data: logEntry
    });
    
    res.json({
        success: true,
        logId: logEntry.id
    });
});

app.get('/api/compliance/audit', (req, res) => {
    const auditResults = {
        overall: 93,
        standards: {
            gdpr: { score: 98, status: 'compliant', lastAudit: '2024-12-01' },
            pci_dss: { score: 95, status: 'compliant', lastAudit: '2024-11-15' },
            iso27001: { score: 87, status: 'minor_issues', lastAudit: '2024-10-30' },
            soc2: { score: 92, status: 'compliant', lastAudit: '2024-11-20' }
        },
        issues: [
            { severity: 'minor', description: 'Log retention policy documentation' },
            { severity: 'advisory', description: 'Additional access control recommendations' }
        ]
    };
    
    res.json({
        success: true,
        data: auditResults
    });
});

app.get('/api/access/stats', (req, res) => {
    res.json({
        success: true,
        data: {
            activeSessions: securityMetrics.access.activeSessions,
            failedLogins: securityMetrics.access.failedLogins,
            adminUsers: securityMetrics.access.adminUsers,
            twoFactorEnabled: securityMetrics.access.twoFactorEnabled,
            recentActivity: [
                { user: 'admin@meschain.com', action: 'login', timestamp: new Date().toISOString() },
                { user: 'user1@meschain.com', action: 'password_change', timestamp: new Date(Date.now() - 300000).toISOString() },
                { user: 'security@meschain.com', action: 'scan_initiated', timestamp: new Date(Date.now() - 600000).toISOString() }
            ]
        }
    });
});

app.get('/api/network/status', (req, res) => {
    res.json({
        success: true,
        data: {
            firewall: {
                status: 'active',
                rules: 127,
                blocked: 1523,
                allowed: 45231
            },
            ssl: {
                grade: 'A+',
                validUntil: '2025-06-15',
                protocols: ['TLS 1.2', 'TLS 1.3']
            },
            ddos: {
                enabled: true,
                attacks_blocked: 5,
                threshold: '1000 req/min'
            },
            vpn: {
                active_connections: securityMetrics.network.vpnConnections,
                total_capacity: 100,
                bandwidth_usage: '67%'
            }
        }
    });
});

// Real-time threat generation
function generateRealTimeThreats() {
    setInterval(() => {
        if (Math.random() < 0.3) { // 30% chance every interval
            const threat = generateThreatIntelligence();
            threatIntelligence.set(threat.id, threat);
            
            // Remove old threats (keep last 100)
            if (threatIntelligence.size > 100) {
                const firstKey = threatIntelligence.keys().next().value;
                threatIntelligence.delete(firstKey);
            }
            
            // Broadcast to all clients
            broadcastToAll({
                type: 'threat_detected',
                data: threat
            });
            
            // Log the threat
            securityEvents.unshift({
                id: crypto.randomUUID(),
                timestamp: new Date().toISOString(),
                level: threat.severity === 'high' ? 'error' : 'warning',
                message: `${threat.type} detected from ${threat.source}`,
                source: 'threat_detection'
            });
        }
    }, 10000); // Check every 10 seconds
}

// Broadcast message to all WebSocket clients
function broadcastToAll(message) {
    wss.clients.forEach(client => {
        if (client.readyState === WebSocket.OPEN) {
            client.send(JSON.stringify(message));
        }
    });
}

// Simulate security metrics updates
function updateSecurityMetrics() {
    setInterval(() => {
        // Slightly vary metrics to simulate real-time updates
        securityMetrics.access.activeSessions += Math.floor(Math.random() * 6) - 3;
        securityMetrics.access.failedLogins += Math.floor(Math.random() * 3);
        securityMetrics.network.vpnConnections += Math.floor(Math.random() * 4) - 2;
        
        // Keep values in realistic ranges
        securityMetrics.access.activeSessions = Math.max(100, Math.min(200, securityMetrics.access.activeSessions));
        securityMetrics.access.failedLogins = Math.max(0, securityMetrics.access.failedLogins);
        securityMetrics.network.vpnConnections = Math.max(30, Math.min(70, securityMetrics.network.vpnConnections));
        
        // Broadcast updates every 30 seconds
        broadcastToAll({
            type: 'metrics_update',
            data: securityMetrics
        });
    }, 30000);
}

// Initialize security systems
function initializeSecuritySystems() {
    console.log('🔒 Initializing Security & Compliance Center...');
    
    // Start threat intelligence generation
    generateRealTimeThreats();
    
    // Start metrics updates
    updateSecurityMetrics();
    
    // Add initial security events
    securityEvents.push(
        {
            id: crypto.randomUUID(),
            timestamp: new Date().toISOString(),
            level: 'info',
            message: 'Security & Compliance Center initialized',
            source: 'system'
        },
        {
            id: crypto.randomUUID(),
            timestamp: new Date(Date.now() - 60000).toISOString(),
            level: 'success',
            message: 'All security systems operational',
            source: 'system'
        }
    );
    
    console.log('🛡️  Security systems online');
    console.log('📊 Threat intelligence active');
    console.log('🔍 Real-time monitoring enabled');
}

// Start server
server.listen(PORT, () => {
    console.log(`🔒 Security & Compliance Center Server running on port ${PORT}`);
    console.log(`📊 Dashboard: http://localhost:${PORT}/security_compliance_center.html`);
    console.log(`🌐 Health check: http://localhost:${PORT}/health`);
    console.log(`🔌 WebSocket endpoint: ws://localhost:${PORT}`);
    
    initializeSecuritySystems();
});

// Graceful shutdown
process.on('SIGTERM', () => {
    console.log('🔒 Security Center shutting down gracefully...');
    server.close(() => {
        console.log('🔒 Security Center stopped');
        process.exit(0);
    });
});

module.exports = { app, server, wss };
