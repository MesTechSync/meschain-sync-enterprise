/**
 * MesChain-Sync Critical Services Starter
 * June 8, 2025
 * Purpose: Start all critical database-dependent services
 */

const { spawn } = require('child_process');
const http = require('http');

class CriticalServicesStarter {
    constructor() {
        this.criticalServices = [
            { port: 3005, file: 'port_3005_product_management_server.js', name: 'Product Management Suite' },
            { port: 3006, file: 'port_3006_order_management_server.js', name: 'Order Management System' },
            { port: 3007, file: 'port_3007_inventory_management_server.js', name: 'Inventory Management Hub' },
            { port: 3012, file: 'port_3012_trendyol_seller_server.js', name: 'Trendyol Seller Hub' },
            { port: 3014, file: 'port_3014_n11_management_server.js', name: 'N11 Management Console' }
        ];
        
        this.runningServices = [];
    }

    /**
     * Start all critical services
     */
    async startAllServices() {
        console.log('🚀 STARTING CRITICAL DATABASE-DEPENDENT SERVICES');
        console.log('═══════════════════════════════════════════════════════════════');
        
        for (const service of this.criticalServices) {
            await this.startService(service);
            await this.sleep(2000); // Wait 2 seconds between starts
        }
        
        // Verify services after startup
        setTimeout(() => {
            this.verifyServices();
        }, 5000);
    }

    /**
     * Start individual service
     */
    async startService(service) {
        console.log(`\n🔄 Starting ${service.name} (Port ${service.port})...`);
        
        try {
            const child = spawn('node', [service.file], {
                stdio: ['ignore', 'pipe', 'pipe'],
                detached: false,
                cwd: process.cwd()
            });
            
            child.stdout.on('data', (data) => {
                console.log(`[${service.port}] ${data.toString().trim()}`);
            });
            
            child.stderr.on('data', (data) => {
                console.error(`[${service.port}] ERROR: ${data.toString().trim()}`);
            });
            
            child.on('close', (code) => {
                console.log(`[${service.port}] Process exited with code ${code}`);
            });
            
            this.runningServices.push({
                ...service,
                process: child,
                pid: child.pid,
                startTime: new Date()
            });
            
            console.log(`✅ ${service.name} started with PID ${child.pid}`);
            
        } catch (error) {
            console.error(`❌ Failed to start ${service.name}: ${error.message}`);
        }
    }

    /**
     * Verify services are responding
     */
    async verifyServices() {
        console.log('\n🔍 VERIFYING SERVICE CONNECTIVITY');
        console.log('═══════════════════════════════════════════════════════════════');
        
        for (const service of this.criticalServices) {
            await this.checkService(service);
        }
        
        this.displaySummary();
    }

    /**
     * Check individual service
     */
    async checkService(service) {
        try {
            const response = await this.makeHealthCheck(service.port);
            console.log(`✅ Port ${service.port} (${service.name}): RESPONDING`);
            return true;
        } catch (error) {
            console.log(`❌ Port ${service.port} (${service.name}): NOT RESPONDING`);
            return false;
        }
    }

    /**
     * Make health check request
     */
    makeHealthCheck(port) {
        return new Promise((resolve, reject) => {
            const req = http.get(`http://localhost:${port}/health`, (res) => {
                if (res.statusCode === 200) {
                    resolve(true);
                } else {
                    reject(new Error(`HTTP ${res.statusCode}`));
                }
            });
            
            req.on('error', (error) => {
                reject(error);
            });
            
            req.setTimeout(3000, () => {
                req.abort();
                reject(new Error('Timeout'));
            });
        });
    }

    /**
     * Display summary
     */
    displaySummary() {
        console.log('\n📊 CRITICAL SERVICES STARTUP SUMMARY');
        console.log('═══════════════════════════════════════════════════════════════');
        console.log(`🎯 Total Critical Services: ${this.criticalServices.length}`);
        console.log(`🚀 Started Processes: ${this.runningServices.length}`);
        console.log('\n📋 Service Details:');
        
        this.runningServices.forEach(service => {
            console.log(`  • Port ${service.port}: ${service.name} (PID: ${service.pid})`);
        });
        
        console.log('\n🔗 Quick Access Links:');
        this.criticalServices.forEach(service => {
            console.log(`  • http://localhost:${service.port} - ${service.name}`);
        });
        
        console.log('\n📝 Next Steps:');
        console.log('  1. Verify services are accessible in browser');
        console.log('  2. Test database connections');
        console.log('  3. Run authentication tests');
        console.log('  4. Monitor service logs');
    }

    /**
     * Sleep utility
     */
    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    /**
     * Graceful shutdown
     */
    shutdown() {
        console.log('\n🛑 Shutting down all critical services...');
        
        this.runningServices.forEach(service => {
            if (service.process && !service.process.killed) {
                service.process.kill();
                console.log(`  • Stopped ${service.name} (PID: ${service.pid})`);
            }
        });
        
        console.log('✅ All services shut down');
    }
}

// Handle graceful shutdown
const serviceStarter = new CriticalServicesStarter();

process.on('SIGINT', () => {
    serviceStarter.shutdown();
    process.exit(0);
});

process.on('SIGTERM', () => {
    serviceStarter.shutdown();
    process.exit(0);
});

// Start services
async function main() {
    try {
        await serviceStarter.startAllServices();
        
        console.log('\n🎉 Critical services startup process completed!');
        console.log('🔄 Services will continue running in the background...');
        console.log('🛑 Press Ctrl+C to shutdown all services');
        
        // Keep the process alive
        setInterval(() => {
            // Do nothing, just keep alive
        }, 30000);
        
    } catch (error) {
        console.error('❌ Failed to start critical services:', error.message);
        process.exit(1);
    }
}

// Run if executed directly
if (require.main === module) {
    main();
}

module.exports = CriticalServicesStarter;
