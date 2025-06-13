// MesChain Enterprise System Health Check
// Real-time backend services monitoring and validation
// Created: June 13, 2025

const axios = require('axios');
const colors = require('colors');

class SystemHealthMonitor {
    constructor() {
        this.criticalServices = [
            { name: 'VSCode Atomic Task Coordination Center', port: 3050, endpoint: '/health' },
            { name: 'VSCode Advanced Security Framework', port: 3042, endpoint: '/health' },
            { name: 'VSCode Microservices Architecture', port: 3043, endpoint: '/health' },
            { name: 'VSCode Quantum Performance Engine', port: 3041, endpoint: '/health' },
            { name: 'Real-time Features Server', port: 3039, endpoint: '/health' },
            { name: 'User Management & RBAC', port: 3036, endpoint: '/health' }
        ];
        
        this.additionalServices = [
            { name: 'Super Admin Panel', port: 3023, endpoint: '/' },
            { name: 'Enhanced Quantum Panel', port: 3030, endpoint: '/' },
            { name: 'Main Enterprise Dashboard', port: 3000, endpoint: '/' },
            { name: 'Performance Dashboard', port: 3004, endpoint: '/' }
        ];
        
        this.systemMetrics = {
            totalServices: this.criticalServices.length + this.additionalServices.length,
            healthyServices: 0,
            criticalHealthy: 0,
            errors: []
        };
    }
    
    async checkServiceHealth(service) {
        try {
            const url = `http://localhost:${service.port}${service.endpoint}`;
            const response = await axios.get(url, { timeout: 5000 });
            
            return {
                name: service.name,
                port: service.port,
                status: 'HEALTHY',
                responseTime: response.headers['x-response-time'] || 'N/A',
                statusCode: response.status,
                data: response.data
            };
        } catch (error) {
            return {
                name: service.name,
                port: service.port,
                status: 'UNHEALTHY',
                error: error.message,
                statusCode: error.response?.status || 'CONNECTION_FAILED'
            };
        }
    }
    
    async performSystemHealthCheck() {
        console.log('🔍 MesChain Enterprise System Health Check Starting...'.cyan.bold);
        console.log('━'.repeat(80).gray);
        
        // Check critical services
        console.log('\n🎯 CRITICAL BACKEND SERVICES STATUS:'.yellow.bold);
        for (const service of this.criticalServices) {
            const result = await this.checkServiceHealth(service);
            
            if (result.status === 'HEALTHY') {
                this.systemMetrics.criticalHealthy++;
                this.systemMetrics.healthyServices++;
                console.log(`✅ ${result.name.padEnd(40)} Port ${result.port} - ${result.status.green}`);
            } else {
                this.systemMetrics.errors.push(result);
                console.log(`❌ ${result.name.padEnd(40)} Port ${result.port} - ${result.status.red} (${result.error})`);
            }
        }
        
        // Check additional services
        console.log('\n🖥️ FRONTEND & DASHBOARD SERVICES STATUS:'.blue.bold);
        for (const service of this.additionalServices) {
            const result = await this.checkServiceHealth(service);
            
            if (result.status === 'HEALTHY') {
                this.systemMetrics.healthyServices++;
                console.log(`✅ ${result.name.padEnd(40)} Port ${result.port} - ${result.status.green}`);
            } else {
                this.systemMetrics.errors.push(result);
                console.log(`⚠️  ${result.name.padEnd(40)} Port ${result.port} - ${result.status.yellow} (${result.error})`);
            }
        }
        
        // Display system summary
        this.displaySystemSummary();
        
        // Display detailed service information for healthy critical services
        await this.displayDetailedServiceInfo();
        
        return this.systemMetrics;
    }
    
    displaySystemSummary() {
        console.log('\n📊 SYSTEM HEALTH SUMMARY:'.magenta.bold);
        console.log('━'.repeat(80).gray);
        
        const healthPercentage = Math.round((this.systemMetrics.healthyServices / this.systemMetrics.totalServices) * 100);
        const criticalHealthPercentage = Math.round((this.systemMetrics.criticalHealthy / this.criticalServices.length) * 100);
        
        console.log(`📈 Overall System Health: ${healthPercentage}% (${this.systemMetrics.healthyServices}/${this.systemMetrics.totalServices})`.cyan);
        console.log(`🎯 Critical Services Health: ${criticalHealthPercentage}% (${this.systemMetrics.criticalHealthy}/${this.criticalServices.length})`.yellow);
        
        if (this.systemMetrics.errors.length > 0) {
            console.log(`⚠️  Issues Found: ${this.systemMetrics.errors.length}`.red);
        } else {
            console.log('✅ All Services Operational'.green.bold);
        }
        
        console.log(`🕐 Health Check Completed: ${new Date().toISOString()}`.gray);
    }
    
    async displayDetailedServiceInfo() {
        console.log('\n📋 DETAILED SERVICE INFORMATION:'.green.bold);
        console.log('━'.repeat(80).gray);
        
        // VSCode Atomic Task Coordination Center details
        try {
            const coordinationStatus = await axios.get('http://localhost:3050/status');
            console.log('\n🎯 ATOMIC TASK COORDINATION CENTER:'.cyan.bold);
            console.log(`   Active Tasks: ${Object.keys(coordinationStatus.data.atomicTasks || {}).length}`);
            console.log(`   System Status: ${coordinationStatus.data.systemStatus || 'Unknown'}`);
        } catch (error) {
            console.log('\n🎯 ATOMIC TASK COORDINATION CENTER: Data not available'.red);
        }
        
        // Security Framework details
        try {
            const securityStatus = await axios.get('http://localhost:3042/security-status');
            console.log('\n🔒 SECURITY FRAMEWORK:'.cyan.bold);
            console.log(`   Security Level: ${securityStatus.data.securityConfig?.encryptionLevel || 'Unknown'}`);
            console.log(`   Threats Blocked: ${securityStatus.data.securityMetrics?.threatsBlocked || 0}`);
        } catch (error) {
            console.log('\n🔒 SECURITY FRAMEWORK: Data not available'.red);
        }
        
        // Performance Engine details
        try {
            const performanceStatus = await axios.get('http://localhost:3041/performance-status');
            console.log('\n⚡ QUANTUM PERFORMANCE ENGINE:'.cyan.bold);
            console.log(`   API Response Target: ${performanceStatus.data.quantumMetrics?.apiResponseTime?.target || 50}ms`);
            console.log(`   Throughput Target: ${performanceStatus.data.quantumMetrics?.throughputRPS?.target || 10000} RPS`);
        } catch (error) {
            console.log('\n⚡ QUANTUM PERFORMANCE ENGINE: Data not available'.red);
        }
    }
}

// Auto-run if called directly
if (require.main === module) {
    const monitor = new SystemHealthMonitor();
    monitor.performSystemHealthCheck().then(() => {
        console.log('\n🎉 Health check completed!'.green.bold);
        process.exit(0);
    }).catch(error => {
        console.error('\n❌ Health check failed:'.red.bold, error.message);
        process.exit(1);
    });
}

module.exports = SystemHealthMonitor;
