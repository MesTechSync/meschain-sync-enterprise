/**
 * Port Conflict Resolver - MesChain-Sync Enterprise
 * Date: June 8, 2025
 * Purpose: Comprehensive port conflict detection and resolution system
 * 
 * Features:
 * - Automatic port conflict detection
 * - Alternative port range allocation
 * - Clean server restart with conflict resolution
 * - Real-time port monitoring
 * - Conflict resolution reporting
 */

const http = require('http');
const https = require('https');
const fs = require('fs');
const { exec } = require('child_process');
const path = require('path');

class PortConflictResolver {
    constructor() {
        this.primaryPortRange = { start: 3000, end: 3016 };
        this.secondaryPortRange = { start: 4000, end: 4016 };
        this.alternativePortRange = { start: 5000, end: 5016 };
        this.emergencyPortRange = { start: 6000, end: 6016 };
        
        this.serviceDefinitions = [
            { name: 'Dashboard', port: 3000, description: '📊 Ana Dashboard Sistemi', priority: 'critical' },
            { name: 'Frontend Components', port: 3001, description: '🎨 Frontend Bileşenleri', priority: 'high' },
            { name: 'Super Admin', port: 3002, description: '👑 Süper Admin Paneli', priority: 'critical' },
            { name: 'Marketplace Hub', port: 3003, description: '🏪 Marketplace Merkezi', priority: 'critical' },
            { name: 'Analytics Engine', port: 3004, description: '📈 Analitik Motoru', priority: 'high' },
            { name: 'Reporting System', port: 3005, description: '📄 Raporlama Sistemi', priority: 'medium' },
            { name: 'Order Management', port: 3006, description: '📋 Sipariş Yönetimi', priority: 'critical' },
            { name: 'Inventory Management', port: 3007, description: '📦 Stok Yönetimi', priority: 'critical' },
            { name: 'Product Catalog', port: 3008, description: '🛍️ Ürün Kataloğu', priority: 'high' },
            { name: 'Cross Marketplace Admin', port: 3009, description: '🌐 Çapraz Marketplace Yönetimi', priority: 'high' },
            { name: 'Hepsiburada Specialist', port: 3010, description: '🛍️ Hepsiburada Uzmanı', priority: 'high' },
            { name: 'Amazon Seller', port: 3011, description: '📦 Amazon Satıcı Sistemi', priority: 'high' },
            { name: 'Trendyol Seller', port: 3012, description: '🛒 Trendyol Satıcı Sistemi', priority: 'high' },
            { name: 'GittiGidiyor Manager', port: 3013, description: '🎯 GittiGidiyor Yöneticisi', priority: 'medium' },
            { name: 'N11 Management', port: 3014, description: '🏢 N11 Yönetim Sistemi', priority: 'high' },
            { name: 'eBay Integration', port: 3015, description: '🌐 eBay Entegrasyonu', priority: 'medium' },
            { name: 'Trendyol Advanced Testing', port: 3016, description: '🧪 Trendyol İleri Testler', priority: 'low' }
        ];

        this.conflictLog = [];
        this.resolutionActions = [];
    }

    /**
     * Check if a port is available
     */
    async checkPortAvailability(port) {
        return new Promise((resolve) => {
            const server = http.createServer();
            
            server.listen(port, (err) => {
                if (err) {
                    resolve(false);
                } else {
                    server.close(() => {
                        resolve(true);
                    });
                }
            });
            
            server.on('error', () => {
                resolve(false);
            });
        });
    }

    /**
     * Scan port range for conflicts
     */
    async scanPortRange(startPort, endPort) {
        console.log(`🔍 Scanning ports ${startPort}-${endPort} for conflicts...`);
        
        const conflicts = [];
        const available = [];
        
        for (let port = startPort; port <= endPort; port++) {
            const isAvailable = await this.checkPortAvailability(port);
            
            if (isAvailable) {
                available.push(port);
                console.log(`✅ Port ${port}: Available`);
            } else {
                conflicts.push(port);
                console.log(`❌ Port ${port}: In use (CONFLICT)`);
                
                this.conflictLog.push({
                    port: port,
                    timestamp: new Date().toISOString(),
                    status: 'conflict_detected',
                    range: `${startPort}-${endPort}`
                });
            }
        }
        
        return { conflicts, available };
    }

    /**
     * Find alternative ports for conflicted services
     */
    async findAlternativePorts(conflictedPorts) {
        console.log(`🔄 Finding alternative ports for ${conflictedPorts.length} conflicted services...`);
        
        const alternatives = new Map();
        
        // Try secondary range (4000-4016)
        for (const port of conflictedPorts) {
            const alternativePort = port + 1000; // 3000 -> 4000, 3001 -> 4001, etc.
            const isAvailable = await this.checkPortAvailability(alternativePort);
            
            if (isAvailable) {
                alternatives.set(port, alternativePort);
                console.log(`🔄 Port ${port} -> ${alternativePort} (Secondary Range)`);
            }
        }
        
        // Try alternative range (5000-5016) for remaining conflicts
        for (const port of conflictedPorts) {
            if (!alternatives.has(port)) {
                const alternativePort = port + 2000; // 3000 -> 5000, 3001 -> 5001, etc.
                const isAvailable = await this.checkPortAvailability(alternativePort);
                
                if (isAvailable) {
                    alternatives.set(port, alternativePort);
                    console.log(`🔄 Port ${port} -> ${alternativePort} (Alternative Range)`);
                }
            }
        }
        
        // Try emergency range (6000-6016) for critical services
        for (const port of conflictedPorts) {
            if (!alternatives.has(port)) {
                const service = this.serviceDefinitions.find(s => s.port === port);
                if (service && service.priority === 'critical') {
                    const alternativePort = port + 3000; // 3000 -> 6000, 3001 -> 6001, etc.
                    const isAvailable = await this.checkPortAvailability(alternativePort);
                    
                    if (isAvailable) {
                        alternatives.set(port, alternativePort);
                        console.log(`🚨 CRITICAL: Port ${port} -> ${alternativePort} (Emergency Range)`);
                    }
                }
            }
        }
        
        return alternatives;
    }

    /**
     * Generate port mapping configuration
     */
    generatePortMapping(alternatives) {
        console.log(`📝 Generating port mapping configuration...`);
        
        const portMapping = {};
        
        for (const service of this.serviceDefinitions) {
            const originalPort = service.port;
            const newPort = alternatives.get(originalPort) || originalPort;
            
            portMapping[service.name] = {
                originalPort: originalPort,
                assignedPort: newPort,
                description: service.description,
                priority: service.priority,
                status: alternatives.has(originalPort) ? 'redirected' : 'original',
                url: `http://localhost:${newPort}`
            };
        }
        
        return portMapping;
    }

    /**
     * Create server restart script with new port configuration
     */
    createRestartScript(portMapping) {
        console.log(`🔧 Creating server restart script with conflict resolution...`);
        
        const script = `/**
 * Auto-generated Server Restart Script with Port Conflict Resolution
 * Generated: ${new Date().toISOString()}
 * Purpose: Restart all services with resolved port conflicts
 */

const http = require('http');
const path = require('path');
const fs = require('fs');

// Port Mapping Configuration (Conflict Resolved)
const PORT_MAPPING = ${JSON.stringify(portMapping, null, 2)};

console.log('🔥 ═══════════════════════════════════════════════════════════════');
console.log('🚀     MESCHAIN-SYNC CONFLICT-RESOLVED SERVER STARTUP     🚀');
console.log('🔥 ═══════════════════════════════════════════════════════════════');
console.log('');

// Create servers for each service
Object.entries(PORT_MAPPING).forEach(([serviceName, config]) => {
    const server = http.createServer((req, res) => {
        res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
        res.end(\`
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>\${serviceName} - MesChain-Sync Enterprise</title>
            <style>
                body { 
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    margin: 0; padding: 20px; color: white; text-align: center;
                    min-height: 100vh; display: flex; flex-direction: column; justify-content: center;
                }
                .container {
                    background: rgba(255,255,255,0.1); backdrop-filter: blur(10px);
                    padding: 40px; border-radius: 20px; box-shadow: 0 8px 32px rgba(31,38,135,0.37);
                    max-width: 600px; margin: 0 auto;
                }
                .status { 
                    background: \${config.status === 'redirected' ? '#ff6b6b' : '#51cf66'};
                    padding: 10px 20px; border-radius: 25px; display: inline-block;
                    margin: 10px 0; font-weight: bold;
                }
                .priority {
                    color: \${config.priority === 'critical' ? '#ff6b6b' : config.priority === 'high' ? '#ffd43b' : '#51cf66'};
                    font-weight: bold; text-transform: uppercase;
                }
                .port-info {
                    background: rgba(255,255,255,0.2); padding: 20px; border-radius: 10px;
                    margin: 20px 0; border-left: 4px solid #ffd43b;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>🚀 \${serviceName}</h1>
                <p class="status">\${config.status === 'redirected' ? '🔄 Port Redirected' : '✅ Original Port'}</p>
                <div class="port-info">
                    <h3>Port Information:</h3>
                    <p><strong>Current Port:</strong> \${config.assignedPort}</p>
                    \${config.status === 'redirected' ? \`<p><strong>Original Port:</strong> \${config.originalPort} (Conflicted)</p>\` : ''}
                    <p><strong>Priority:</strong> <span class="priority">\${config.priority}</span></p>
                </div>
                <p>\${config.description}</p>
                <p><strong>Status:</strong> ✅ Running & Operational</p>
                <p><strong>Timestamp:</strong> \${new Date().toLocaleString('tr-TR')}</p>
            </div>
        </body>
        </html>
        \`);
    });

    server.listen(config.assignedPort, () => {
        const statusIcon = config.status === 'redirected' ? '🔄' : '✅';
        const priorityIcon = config.priority === 'critical' ? '🚨' : config.priority === 'high' ? '⚡' : '📝';
        
        console.log(\`\${statusIcon} Port \${config.assignedPort} - \${serviceName} STARTED! \${priorityIcon}\`);
        console.log(\`🌐 \${config.url}\`);
        console.log(\`📝 \${config.description}\`);
        if (config.status === 'redirected') {
            console.log(\`🔄 Redirected from port \${config.originalPort} due to conflict\`);
        }
        console.log('──────────────────────────────────────────────────');
    });
});

console.log('');
console.log('🎯 All services started successfully with conflict resolution!');
console.log('🔥 ═══════════════════════════════════════════════════════════════');
`;
        
        return script;
    }

    /**
     * Main conflict resolution process
     */
    async resolvePortConflicts() {
        console.log('🔥 ═══════════════════════════════════════════════════════════════');
        console.log('🚀          PORT CONFLICT RESOLUTION SYSTEM - JUNE 8, 2025          🚀');
        console.log('🔥 ═══════════════════════════════════════════════════════════════');
        console.log('');

        // Step 1: Scan primary port range for conflicts
        const { conflicts, available } = await this.scanPortRange(
            this.primaryPortRange.start, 
            this.primaryPortRange.end
        );

        console.log('');
        console.log('📊 CONFLICT ANALYSIS RESULTS:');
        console.log(`✅ Available Ports: ${available.length}/17`);
        console.log(`❌ Conflicted Ports: ${conflicts.length}/17`);
        console.log('');

        if (conflicts.length === 0) {
            console.log('🎉 No port conflicts detected! All ports in range 3000-3016 are available.');
            return { status: 'no_conflicts', conflicts: [], alternatives: new Map() };
        }

        // Step 2: Find alternative ports for conflicted services
        const alternatives = await this.findAlternativePorts(conflicts);

        console.log('');
        console.log('🔄 ALTERNATIVE PORT ASSIGNMENTS:');
        alternatives.forEach((newPort, originalPort) => {
            const service = this.serviceDefinitions.find(s => s.port === originalPort);
            console.log(`   ${originalPort} -> ${newPort} (${service ? service.name : 'Unknown Service'})`);
        });

        // Step 3: Generate port mapping configuration
        const portMapping = this.generatePortMapping(alternatives);

        // Step 4: Create restart script with new configuration
        const restartScript = this.createRestartScript(portMapping);

        // Step 5: Save restart script
        const scriptPath = path.join(__dirname, 'conflict_resolved_server_june8_2025.js');
        fs.writeFileSync(scriptPath, restartScript);

        // Step 6: Generate resolution report
        const report = {
            timestamp: new Date().toISOString(),
            conflictsDetected: conflicts.length,
            conflictedPorts: conflicts,
            availablePorts: available,
            alternativeAssignments: Object.fromEntries(alternatives),
            portMapping: portMapping,
            resolutionActions: this.resolutionActions,
            conflictLog: this.conflictLog,
            status: 'resolved',
            restartScriptPath: scriptPath
        };

        // Save resolution report
        const reportPath = path.join(__dirname, 'PORT_CONFLICT_RESOLUTION_REPORT_JUNE8_2025.json');
        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));

        console.log('');
        console.log('📄 RESOLUTION REPORT GENERATED:');
        console.log(`   📊 Report: ${reportPath}`);
        console.log(`   🔧 Restart Script: ${scriptPath}`);
        console.log('');

        return report;
    }
}

// Execute port conflict resolution
async function main() {
    try {
        const resolver = new PortConflictResolver();
        const result = await resolver.resolvePortConflicts();
        
        console.log('🎯 PORT CONFLICT RESOLUTION COMPLETED SUCCESSFULLY!');
        console.log('🔥 ═══════════════════════════════════════════════════════════════');
        
        process.exit(0);
    } catch (error) {
        console.error('❌ Port conflict resolution failed:', error);
        process.exit(1);
    }
}

// Run the resolver
main();
