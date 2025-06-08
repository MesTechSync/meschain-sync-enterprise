/**
 * MesChain-Sync Enterprise - Database Connection Validation System
 * June 8, 2025
 * Priority: CRITICAL INFRASTRUCTURE TASK
 * 
 * This system validates database connections for all critical API endpoints
 * that may not function properly without database connectivity.
 */

const http = require('http');
const https = require('https');
const fs = require('fs');
const path = require('path');

class DatabaseConnectionValidator {
    constructor() {
        this.criticalPorts = [3005, 3006, 3007, 3009, 3012, 3014];
        this.testResults = {};
        this.connectionStatus = {};
        
        console.log('🔍 MesChain-Sync Database Connection Validator initialized');
        console.log('🎯 Testing critical ports:', this.criticalPorts.join(', '));
    }

    /**
     * Validate all critical database connections
     */
    async validateAllConnections() {
        console.log('\n🚀 STARTING DATABASE CONNECTION VALIDATION');
        console.log('═══════════════════════════════════════════════════════════════');
        
        for (const port of this.criticalPorts) {
            await this.validatePortConnection(port);
        }
        
        await this.generateValidationReport();
        return this.testResults;
    }

    /**
     * Validate database connection for specific port
     */
    async validatePortConnection(port) {
        console.log(`\n🔍 Testing Port ${port}...`);
        
        const service = this.getServiceName(port);
        console.log(`📝 Service: ${service}`);
        
        try {
            // Test 1: Health Check Endpoint
            const healthCheck = await this.testHealthEndpoint(port);
            
            // Test 2: API Status Endpoint
            const apiStatus = await this.testAPIEndpoint(port);
            
            // Test 3: Database-specific Endpoint Test
            const dbTest = await this.testDatabaseEndpoint(port);
            
            this.testResults[port] = {
                service: service,
                health_check: healthCheck,
                api_status: apiStatus,
                database_test: dbTest,
                overall_status: this.calculateOverallStatus(healthCheck, apiStatus, dbTest),
                timestamp: new Date().toISOString()
            };
            
            this.logPortResults(port);
            
        } catch (error) {
            console.log(`❌ Port ${port} - Connection Error: ${error.message}`);
            this.testResults[port] = {
                service: service,
                error: error.message,
                overall_status: 'failed',
                timestamp: new Date().toISOString()
            };
        }
    }

    /**
     * Test health endpoint
     */
    async testHealthEndpoint(port) {
        const url = `http://localhost:${port}/health`;
        
        try {
            const response = await this.makeHttpRequest(url);
            
            if (response.statusCode === 200) {
                console.log(`  ✅ Health endpoint responding`);
                return { status: 'active', response_time: response.responseTime };
            } else {
                console.log(`  ⚠️ Health endpoint returned ${response.statusCode}`);
                return { status: 'warning', response_time: response.responseTime };
            }
        } catch (error) {
            console.log(`  ❌ Health endpoint failed: ${error.message}`);
            return { status: 'failed', error: error.message };
        }
    }

    /**
     * Test API status endpoint
     */
    async testAPIEndpoint(port) {
        const url = `http://localhost:${port}/api/status`;
        
        try {
            const response = await this.makeHttpRequest(url);
            
            if (response.statusCode === 200) {
                console.log(`  ✅ API endpoint responding`);
                return { status: 'active', response_time: response.responseTime };
            } else if (response.statusCode === 401 || response.statusCode === 403) {
                console.log(`  🔐 API endpoint requires authentication (normal)`);
                return { status: 'authenticated', response_time: response.responseTime };
            } else {
                console.log(`  ⚠️ API endpoint returned ${response.statusCode}`);
                return { status: 'warning', response_time: response.responseTime };
            }
        } catch (error) {
            console.log(`  ❌ API endpoint failed: ${error.message}`);
            return { status: 'failed', error: error.message };
        }
    }

    /**
     * Test database-specific endpoints
     */
    async testDatabaseEndpoint(port) {
        // Define port-specific database endpoints
        const dbEndpoints = {
            3005: '/api/products', // Product Management
            3006: '/api/orders',   // Order Management  
            3007: '/api/inventory', // Inventory Management
            3009: '/api/marketplace', // Cross-Marketplace
            3012: '/api/trendyol',  // Trendyol Seller
            3014: '/api/n11-status' // N11 Management
        };
        
        const endpoint = dbEndpoints[port];
        if (!endpoint) {
            return { status: 'no_db_endpoint', message: 'No specific database endpoint defined' };
        }
        
        const url = `http://localhost:${port}${endpoint}`;
        
        try {
            const response = await this.makeHttpRequest(url);
            
            if (response.statusCode === 200) {
                console.log(`  ✅ Database endpoint responding`);
                return { status: 'active', endpoint: endpoint, response_time: response.responseTime };
            } else if (response.statusCode === 401 || response.statusCode === 403) {
                console.log(`  🔐 Database endpoint requires authentication`);
                return { status: 'authenticated', endpoint: endpoint, response_time: response.responseTime };
            } else {
                console.log(`  ⚠️ Database endpoint returned ${response.statusCode}`);
                return { status: 'warning', endpoint: endpoint, response_time: response.responseTime };
            }
        } catch (error) {
            console.log(`  ❌ Database endpoint failed: ${error.message}`);
            return { status: 'failed', endpoint: endpoint, error: error.message };
        }
    }

    /**
     * Make HTTP request with timeout
     */
    makeHttpRequest(url, timeout = 5000) {
        return new Promise((resolve, reject) => {
            const startTime = Date.now();
            
            const req = http.get(url, (res) => {
                let data = '';
                
                res.on('data', (chunk) => {
                    data += chunk;
                });
                
                res.on('end', () => {
                    const responseTime = Date.now() - startTime;
                    resolve({
                        statusCode: res.statusCode,
                        data: data,
                        responseTime: responseTime
                    });
                });
            });
            
            req.on('error', (error) => {
                reject(error);
            });
            
            req.setTimeout(timeout, () => {
                req.abort();
                reject(new Error('Request timeout'));
            });
        });
    }

    /**
     * Get service name for port
     */
    getServiceName(port) {
        const services = {
            3005: 'Product Management Suite',
            3006: 'Order Management System', 
            3007: 'Inventory Management Hub',
            3009: 'Cross-Marketplace Admin',
            3012: 'Trendyol Seller Hub',
            3014: 'N11 Management Console'
        };
        
        return services[port] || `Service on Port ${port}`;
    }

    /**
     * Calculate overall status
     */
    calculateOverallStatus(healthCheck, apiStatus, dbTest) {
        const statuses = [healthCheck.status, apiStatus.status, dbTest.status];
        
        if (statuses.includes('failed')) {
            return 'failed';
        } else if (statuses.includes('warning')) {
            return 'warning';
        } else if (statuses.includes('authenticated')) {
            return 'active_auth_required';
        } else if (statuses.includes('active')) {
            return 'active';
        } else {
            return 'unknown';
        }
    }

    /**
     * Log results for specific port
     */
    logPortResults(port) {
        const result = this.testResults[port];
        const status = result.overall_status;
        
        let emoji, message;
        switch (status) {
            case 'active':
                emoji = '✅';
                message = 'All endpoints operational';
                break;
            case 'active_auth_required':
                emoji = '🔐';
                message = 'Active with authentication required';
                break;
            case 'warning':
                emoji = '⚠️';
                message = 'Some issues detected';
                break;
            case 'failed':
                emoji = '❌';
                message = 'Connection failed';
                break;
            default:
                emoji = '❓';
                message = 'Status unknown';
        }
        
        console.log(`${emoji} Port ${port} - ${message}`);
    }

    /**
     * Generate validation report
     */
    async generateValidationReport() {
        console.log('\n📊 GENERATING DATABASE CONNECTION VALIDATION REPORT');
        console.log('═══════════════════════════════════════════════════════════════');
        
        const summary = this.generateSummary();
        const reportPath = path.join(__dirname, 'DATABASE_CONNECTION_VALIDATION_REPORT_JUNE8_2025.md');
        
        const reportContent = this.generateReportContent(summary);
        
        fs.writeFileSync(reportPath, reportContent);
        console.log(`📄 Report saved: ${reportPath}`);
        
        this.displaySummary(summary);
    }

    /**
     * Generate summary statistics
     */
    generateSummary() {
        const total = Object.keys(this.testResults).length;
        let active = 0, authenticated = 0, warning = 0, failed = 0;
        
        Object.values(this.testResults).forEach(result => {
            switch (result.overall_status) {
                case 'active':
                    active++;
                    break;
                case 'active_auth_required':
                    authenticated++;
                    break;
                case 'warning':
                    warning++;
                    break;
                case 'failed':
                    failed++;
                    break;
            }
        });
        
        return {
            total,
            active,
            authenticated,
            warning,
            failed,
            success_rate: ((active + authenticated) / total * 100).toFixed(1)
        };
    }

    /**
     * Display summary
     */
    displaySummary(summary) {
        console.log('\n🎯 VALIDATION SUMMARY:');
        console.log(`📊 Total Services Tested: ${summary.total}`);
        console.log(`✅ Fully Active: ${summary.active}`);
        console.log(`🔐 Active (Auth Required): ${summary.authenticated}`);
        console.log(`⚠️ Warnings: ${summary.warning}`);
        console.log(`❌ Failed: ${summary.failed}`);
        console.log(`📈 Success Rate: ${summary.success_rate}%`);
        
        if (summary.success_rate >= 90) {
            console.log('🎉 EXCELLENT: Database connections are in great shape!');
        } else if (summary.success_rate >= 75) {
            console.log('👍 GOOD: Most database connections are working');
        } else if (summary.success_rate >= 50) {
            console.log('⚠️ NEEDS ATTENTION: Several connection issues detected');
        } else {
            console.log('🚨 CRITICAL: Major database connection problems!');
        }
    }

    /**
     * Generate report content
     */
    generateReportContent(summary) {
        const timestamp = new Date().toISOString();
        
        let content = `# 📊 DATABASE CONNECTION VALIDATION REPORT
**Generated:** ${timestamp}
**System:** MesChain-Sync Enterprise
**Validation Type:** Critical API Endpoints Database Connectivity

## 🎯 EXECUTIVE SUMMARY

- **Total Services Tested:** ${summary.total}
- **Success Rate:** ${summary.success_rate}%
- **Fully Active:** ${summary.active}
- **Active (Auth Required):** ${summary.authenticated}  
- **Warnings:** ${summary.warning}
- **Failed:** ${summary.failed}

## 📋 DETAILED RESULTS

`;

        Object.entries(this.testResults).forEach(([port, result]) => {
            const statusEmoji = this.getStatusEmoji(result.overall_status);
            content += `### ${statusEmoji} Port ${port} - ${result.service}

- **Overall Status:** ${result.overall_status}
- **Health Check:** ${result.health_check?.status || 'N/A'}
- **API Status:** ${result.api_status?.status || 'N/A'}
- **Database Test:** ${result.database_test?.status || 'N/A'}
- **Timestamp:** ${result.timestamp}

`;

            if (result.error) {
                content += `- **Error:** ${result.error}\n\n`;
            }
        });

        content += `## 🔍 RECOMMENDATIONS

`;

        if (summary.failed > 0) {
            content += `### ❌ Failed Connections
- Investigate failed database connections immediately
- Check database server status and configuration
- Verify network connectivity
- Review application logs for errors

`;
        }

        if (summary.warning > 0) {
            content += `### ⚠️ Warning Conditions  
- Review services with warnings
- Check for intermittent connectivity issues
- Monitor response times
- Consider optimization if needed

`;
        }

        content += `### 📈 Next Steps
1. Fix any failed connections (Priority: HIGH)
2. Address warning conditions (Priority: MEDIUM)
3. Set up automated monitoring for database connectivity
4. Implement connection pooling if not already in place
5. Create database failover procedures

## 📊 SYSTEM HEALTH STATUS

Current database connectivity health: **${this.getOverallHealth(summary)}**

---
*Report generated by MesChain-Sync Database Connection Validator*
*June 8, 2025*
`;

        return content;
    }

    /**
     * Get status emoji
     */
    getStatusEmoji(status) {
        const emojis = {
            'active': '✅',
            'active_auth_required': '🔐',
            'warning': '⚠️',
            'failed': '❌',
            'unknown': '❓'
        };
        return emojis[status] || '❓';
    }

    /**
     * Get overall health assessment
     */
    getOverallHealth(summary) {
        if (summary.success_rate >= 95) return 'EXCELLENT 🎉';
        if (summary.success_rate >= 85) return 'GOOD 👍';
        if (summary.success_rate >= 70) return 'FAIR ⚠️';
        return 'POOR 🚨';
    }
}

// Execute validation if run directly
async function main() {
    const validator = new DatabaseConnectionValidator();
    
    try {
        await validator.validateAllConnections();
        console.log('\n🎉 Database connection validation completed successfully!');
    } catch (error) {
        console.error('\n❌ Database connection validation failed:', error.message);
        process.exit(1);
    }
}

// Export for use in other modules
module.exports = DatabaseConnectionValidator;

// Run if executed directly
if (require.main === module) {
    main();
}
