/**
 * SELINAY TEAM - Task 7: Dependency Monitor
 * Comprehensive dependency monitoring and management
 * Date: June 5, 2025
 * @author Selinay Team
 */

const EventEmitter = require('events');
const fs = require('fs').promises;
const path = require('path');
const crypto = require('crypto');

class DependencyMonitor extends EventEmitter {
    constructor() {
        super();
        this.dependencies = new Map();
        this.vulnerabilities = [];
        this.updateQueue = [];
        this.monitoringActive = false;
        this.checkInterval = null;
        this.config = null;
    }

    /**
     * Initialize dependency monitor
     */
    async initialize() {
        try {
            console.log('🚀 Initializing Dependency Monitor...');
            
            // Load monitoring configuration
            await this.loadConfiguration();
            
            // Scan current dependencies
            await this.scanDependencies();
            
            // Setup monitoring schedules
            await this.setupMonitoring();
            
            // Initialize security scanning
            await this.initializeSecurityScanning();
            
            console.log('✅ Dependency Monitor initialized successfully');
            this.emit('deps:initialized');
            
        } catch (error) {
            console.error('❌ Failed to initialize dependency monitor:', error);
            this.emit('deps:error', error);
        }
    }

    /**
     * Load monitoring configuration
     */
    async loadConfiguration() {
        this.config = {
            scan_intervals: {
                security_scan: 86400000, // Daily
                update_check: 43200000, // Twice daily
                health_check: 3600000, // Hourly
                license_check: 604800000 // Weekly
            },
            security: {
                severity_threshold: 'medium',
                auto_fix_low: true,
                alert_critical: true,
                block_high_risk: true
            },
            updates: {
                auto_update_patch: true,
                auto_update_minor: false,
                auto_update_major: false,
                test_before_update: true
            },
            monitoring: {
                performance_impact: true,
                bundle_size_impact: true,
                compatibility_check: true,
                license_compliance: true
            },
            package_managers: [
                'npm',
                'yarn',
                'composer',
                'pip'
            ]
        };

        console.log('📋 Dependency monitoring configuration loaded');
    }

    /**
     * Scan current dependencies
     */
    async scanDependencies() {
        try {
            console.log('🔍 Scanning project dependencies...');
            
            // Scan different package managers
            await this.scanNpmDependencies();
            await this.scanComposerDependencies();
            await this.scanPythonDependencies();
            
            console.log(`📦 Found ${this.dependencies.size} dependencies`);
            this.emit('deps:scan_complete', Array.from(this.dependencies.values()));
            
        } catch (error) {
            console.error('❌ Dependency scan failed:', error);
            this.emit('deps:scan_error', error);
        }
    }

    /**
     * Scan NPM dependencies
     */
    async scanNpmDependencies() {
        // Simulate scanning package.json and package-lock.json
        const npmDependencies = [
            {
                name: 'react',
                version: '18.2.0',
                type: 'production',
                manager: 'npm',
                size: 42000,
                license: 'MIT',
                vulnerabilities: 0,
                last_updated: '2023-06-15',
                latest_version: '18.2.0'
            },
            {
                name: 'lodash',
                version: '4.17.20',
                type: 'production',
                manager: 'npm',
                size: 70000,
                license: 'MIT',
                vulnerabilities: 1,
                last_updated: '2021-02-20',
                latest_version: '4.17.21'
            },
            {
                name: 'moment',
                version: '2.29.1',
                type: 'production',
                manager: 'npm',
                size: 67000,
                license: 'MIT',
                vulnerabilities: 0,
                last_updated: '2021-02-22',
                latest_version: '2.29.4',
                deprecated: true
            },
            {
                name: 'axios',
                version: '0.27.2',
                type: 'production',
                manager: 'npm',
                size: 15000,
                license: 'MIT',
                vulnerabilities: 0,
                last_updated: '2022-04-22',
                latest_version: '1.6.2'
            },
            {
                name: 'express',
                version: '4.18.1',
                type: 'production',
                manager: 'npm',
                size: 25000,
                license: 'MIT',
                vulnerabilities: 0,
                last_updated: '2022-04-25',
                latest_version: '4.18.2'
            }
        ];

        for (const dep of npmDependencies) {
            this.dependencies.set(`npm:${dep.name}`, {
                ...dep,
                id: `npm:${dep.name}`,
                health_score: this.calculateHealthScore(dep),
                risk_level: this.calculateRiskLevel(dep),
                update_recommendation: this.getUpdateRecommendation(dep)
            });
        }

        console.log('📦 NPM dependencies scanned');
    }

    /**
     * Scan Composer dependencies
     */
    async scanComposerDependencies() {
        // Simulate scanning composer.json and composer.lock
        const composerDependencies = [
            {
                name: 'symfony/console',
                version: '6.2.0',
                type: 'production',
                manager: 'composer',
                size: 85000,
                license: 'MIT',
                vulnerabilities: 0,
                last_updated: '2022-11-25',
                latest_version: '6.4.0'
            },
            {
                name: 'doctrine/orm',
                version: '2.14.0',
                type: 'production',
                manager: 'composer',
                size: 120000,
                license: 'MIT',
                vulnerabilities: 0,
                last_updated: '2022-12-15',
                latest_version: '2.17.0'
            }
        ];

        for (const dep of composerDependencies) {
            this.dependencies.set(`composer:${dep.name}`, {
                ...dep,
                id: `composer:${dep.name}`,
                health_score: this.calculateHealthScore(dep),
                risk_level: this.calculateRiskLevel(dep),
                update_recommendation: this.getUpdateRecommendation(dep)
            });
        }

        console.log('🐘 Composer dependencies scanned');
    }

    /**
     * Scan Python dependencies
     */
    async scanPythonDependencies() {
        // Simulate scanning requirements.txt or pipfile
        const pythonDependencies = [
            {
                name: 'django',
                version: '4.1.5',
                type: 'production',
                manager: 'pip',
                size: 95000,
                license: 'BSD-3-Clause',
                vulnerabilities: 0,
                last_updated: '2023-01-02',
                latest_version: '4.2.7'
            },
            {
                name: 'requests',
                version: '2.28.1',
                type: 'production',
                manager: 'pip',
                size: 35000,
                license: 'Apache-2.0',
                vulnerabilities: 0,
                last_updated: '2022-06-29',
                latest_version: '2.31.0'
            }
        ];

        for (const dep of pythonDependencies) {
            this.dependencies.set(`pip:${dep.name}`, {
                ...dep,
                id: `pip:${dep.name}`,
                health_score: this.calculateHealthScore(dep),
                risk_level: this.calculateRiskLevel(dep),
                update_recommendation: this.getUpdateRecommendation(dep)
            });
        }

        console.log('🐍 Python dependencies scanned');
    }

    /**
     * Setup monitoring schedules
     */
    async setupMonitoring() {
        this.monitoringActive = true;

        // Security vulnerability scan
        setInterval(() => {
            this.performSecurityScan();
        }, this.config.scan_intervals.security_scan);

        // Update availability check
        setInterval(() => {
            this.checkForUpdates();
        }, this.config.scan_intervals.update_check);

        // Health monitoring
        setInterval(() => {
            this.performHealthCheck();
        }, this.config.scan_intervals.health_check);

        // License compliance check
        setInterval(() => {
            this.checkLicenseCompliance();
        }, this.config.scan_intervals.license_check);

        console.log('⏰ Dependency monitoring schedules configured');
    }

    /**
     * Initialize security scanning
     */
    async initializeSecurityScanning() {
        // Setup security scanning tools
        const scanningTools = {
            npm_audit: { enabled: true, severity: 'low' },
            snyk: { enabled: true, severity: 'medium' },
            github_security: { enabled: true, severity: 'high' },
            custom_rules: { enabled: true }
        };

        console.log('🔒 Security scanning tools initialized:', scanningTools);
    }

    /**
     * Calculate dependency health score
     */
    calculateHealthScore(dependency) {
        let score = 100;

        // Age penalty
        const lastUpdate = new Date(dependency.last_updated);
        const ageInMonths = (Date.now() - lastUpdate.getTime()) / (1000 * 60 * 60 * 24 * 30);
        if (ageInMonths > 24) score -= 30;
        else if (ageInMonths > 12) score -= 15;
        else if (ageInMonths > 6) score -= 5;

        // Vulnerability penalty
        score -= dependency.vulnerabilities * 20;

        // Deprecation penalty
        if (dependency.deprecated) score -= 40;

        // Version behind penalty
        const currentVersion = dependency.version.split('.').map(Number);
        const latestVersion = dependency.latest_version.split('.').map(Number);
        
        if (latestVersion[0] > currentVersion[0]) score -= 25; // Major version behind
        else if (latestVersion[1] > currentVersion[1]) score -= 10; // Minor version behind
        else if (latestVersion[2] > currentVersion[2]) score -= 5; // Patch version behind

        return Math.max(score, 0);
    }

    /**
     * Calculate risk level
     */
    calculateRiskLevel(dependency) {
        if (dependency.vulnerabilities > 0) return 'high';
        if (dependency.deprecated) return 'high';
        
        const healthScore = this.calculateHealthScore(dependency);
        if (healthScore < 50) return 'high';
        if (healthScore < 70) return 'medium';
        return 'low';
    }

    /**
     * Get update recommendation
     */
    getUpdateRecommendation(dependency) {
        const currentVersion = dependency.version.split('.').map(Number);
        const latestVersion = dependency.latest_version.split('.').map(Number);
        
        if (dependency.vulnerabilities > 0) return 'urgent';
        if (dependency.deprecated) return 'urgent';
        if (latestVersion[0] > currentVersion[0]) return 'major_update_available';
        if (latestVersion[1] > currentVersion[1]) return 'minor_update_available';
        if (latestVersion[2] > currentVersion[2]) return 'patch_update_available';
        return 'up_to_date';
    }

    /**
     * Perform security scan
     */
    async performSecurityScan() {
        try {
            console.log('🔒 Performing security vulnerability scan...');
            
            const vulnerabilities = await this.scanForVulnerabilities();
            
            // Update vulnerability information
            this.vulnerabilities = vulnerabilities;
            
            // Process critical vulnerabilities
            const criticalVulns = vulnerabilities.filter(v => v.severity === 'critical');
            if (criticalVulns.length > 0) {
                await this.handleCriticalVulnerabilities(criticalVulns);
            }
            
            // Auto-fix low severity issues if enabled
            if (this.config.security.auto_fix_low) {
                const lowVulns = vulnerabilities.filter(v => v.severity === 'low');
                await this.autoFixVulnerabilities(lowVulns);
            }
            
            console.log(`🔍 Security scan complete: ${vulnerabilities.length} vulnerabilities found`);
            this.emit('deps:security_scan_complete', vulnerabilities);
            
        } catch (error) {
            console.error('❌ Security scan failed:', error);
            this.emit('deps:security_scan_error', error);
        }
    }

    /**
     * Scan for vulnerabilities
     */
    async scanForVulnerabilities() {
        const vulnerabilities = [];
        
        // Simulate vulnerability scanning
        for (const [id, dependency] of this.dependencies) {
            if (dependency.vulnerabilities > 0) {
                // Generate mock vulnerabilities
                for (let i = 0; i < dependency.vulnerabilities; i++) {
                    vulnerabilities.push({
                        id: crypto.randomBytes(8).toString('hex'),
                        dependency_id: id,
                        dependency_name: dependency.name,
                        title: `Security vulnerability in ${dependency.name}`,
                        severity: this.getRandomSeverity(),
                        cve_id: `CVE-2023-${Math.floor(Math.random() * 10000)}`,
                        description: `Security issue found in ${dependency.name} version ${dependency.version}`,
                        affected_versions: `<= ${dependency.version}`,
                        fixed_version: dependency.latest_version,
                        published_date: new Date().toISOString(),
                        source: 'npm_audit'
                    });
                }
            }
        }
        
        // Add some random vulnerabilities for demonstration
        if (Math.random() > 0.7) {
            vulnerabilities.push({
                id: crypto.randomBytes(8).toString('hex'),
                dependency_id: 'npm:lodash',
                dependency_name: 'lodash',
                title: 'Prototype pollution vulnerability',
                severity: 'medium',
                cve_id: 'CVE-2023-1234',
                description: 'Prototype pollution in lodash',
                affected_versions: '<= 4.17.20',
                fixed_version: '4.17.21',
                published_date: new Date().toISOString(),
                source: 'github_security'
            });
        }
        
        return vulnerabilities;
    }

    /**
     * Get random severity for demo
     */
    getRandomSeverity() {
        const severities = ['low', 'medium', 'high', 'critical'];
        const weights = [0.4, 0.3, 0.2, 0.1]; // Weighted random
        const random = Math.random();
        let sum = 0;
        
        for (let i = 0; i < severities.length; i++) {
            sum += weights[i];
            if (random <= sum) return severities[i];
        }
        
        return 'low';
    }

    /**
     * Handle critical vulnerabilities
     */
    async handleCriticalVulnerabilities(criticalVulns) {
        console.log(`🚨 Handling ${criticalVulns.length} critical vulnerabilities`);
        
        for (const vuln of criticalVulns) {
            // Alert administrators
            this.emit('deps:critical_vulnerability', vuln);
            
            // If auto-blocking is enabled, take action
            if (this.config.security.block_high_risk) {
                await this.blockVulnerableDependency(vuln);
            }
            
            // Queue for immediate update
            this.queueUrgentUpdate(vuln);
        }
    }

    /**
     * Auto-fix vulnerabilities
     */
    async autoFixVulnerabilities(vulnerabilities) {
        console.log(`🔧 Auto-fixing ${vulnerabilities.length} low severity vulnerabilities`);
        
        for (const vuln of vulnerabilities) {
            if (vuln.fixed_version) {
                await this.scheduleUpdate(vuln.dependency_id, vuln.fixed_version);
            }
        }
    }

    /**
     * Block vulnerable dependency
     */
    async blockVulnerableDependency(vulnerability) {
        console.log(`🚫 Blocking vulnerable dependency: ${vulnerability.dependency_name}`);
        
        // Add to blocked list (in real implementation, this would update security policies)
        const blockAction = {
            dependency: vulnerability.dependency_name,
            reason: `Critical vulnerability: ${vulnerability.cve_id}`,
            blocked_at: new Date().toISOString(),
            auto_unblock: false
        };
        
        this.emit('deps:dependency_blocked', blockAction);
    }

    /**
     * Queue urgent update
     */
    queueUrgentUpdate(vulnerability) {
        const update = {
            dependency_id: vulnerability.dependency_id,
            current_version: this.dependencies.get(vulnerability.dependency_id)?.version,
            target_version: vulnerability.fixed_version,
            priority: 'urgent',
            reason: `Fix for ${vulnerability.cve_id}`,
            created_at: new Date().toISOString()
        };
        
        this.updateQueue.unshift(update); // Add to front of queue
        console.log(`⚡ Urgent update queued for ${vulnerability.dependency_name}`);
    }

    /**
     * Check for updates
     */
    async checkForUpdates() {
        try {
            console.log('🔄 Checking for dependency updates...');
            
            const updatesAvailable = [];
            
            for (const [id, dependency] of this.dependencies) {
                const updateInfo = await this.checkDependencyUpdate(dependency);
                if (updateInfo.hasUpdate) {
                    updatesAvailable.push(updateInfo);
                }
            }
            
            // Process automatic updates
            const autoUpdates = updatesAvailable.filter(update => 
                this.shouldAutoUpdate(update)
            );
            
            for (const update of autoUpdates) {
                await this.scheduleUpdate(update.dependency_id, update.latest_version);
            }
            
            console.log(`📋 Update check complete: ${updatesAvailable.length} updates available`);
            this.emit('deps:updates_checked', updatesAvailable);
            
        } catch (error) {
            console.error('❌ Update check failed:', error);
            this.emit('deps:update_check_error', error);
        }
    }

    /**
     * Check individual dependency update
     */
    async checkDependencyUpdate(dependency) {
        const currentVersion = dependency.version.split('.').map(Number);
        const latestVersion = dependency.latest_version.split('.').map(Number);
        
        const hasUpdate = (
            latestVersion[0] > currentVersion[0] ||
            latestVersion[1] > currentVersion[1] ||
            latestVersion[2] > currentVersion[2]
        );
        
        let updateType = 'none';
        if (hasUpdate) {
            if (latestVersion[0] > currentVersion[0]) updateType = 'major';
            else if (latestVersion[1] > currentVersion[1]) updateType = 'minor';
            else updateType = 'patch';
        }
        
        return {
            dependency_id: dependency.id,
            dependency_name: dependency.name,
            current_version: dependency.version,
            latest_version: dependency.latest_version,
            hasUpdate,
            updateType,
            breaking_changes: updateType === 'major',
            release_notes: `https://github.com/package/${dependency.name}/releases`,
            compatibility_risk: this.assessCompatibilityRisk(dependency, updateType)
        };
    }

    /**
     * Assess compatibility risk
     */
    assessCompatibilityRisk(dependency, updateType) {
        if (updateType === 'major') return 'high';
        if (updateType === 'minor' && dependency.type === 'production') return 'medium';
        return 'low';
    }

    /**
     * Should auto-update dependency
     */
    shouldAutoUpdate(updateInfo) {
        switch (updateInfo.updateType) {
            case 'patch':
                return this.config.updates.auto_update_patch;
            case 'minor':
                return this.config.updates.auto_update_minor;
            case 'major':
                return this.config.updates.auto_update_major;
            default:
                return false;
        }
    }

    /**
     * Schedule update
     */
    async scheduleUpdate(dependencyId, targetVersion) {
        const update = {
            dependency_id: dependencyId,
            target_version: targetVersion,
            priority: 'normal',
            scheduled_at: new Date().toISOString(),
            test_required: this.config.updates.test_before_update
        };
        
        this.updateQueue.push(update);
        console.log(`📅 Update scheduled: ${dependencyId} -> ${targetVersion}`);
        
        this.emit('deps:update_scheduled', update);
    }

    /**
     * Perform health check
     */
    async performHealthCheck() {
        try {
            console.log('💊 Performing dependency health check...');
            
            const healthReport = {
                timestamp: new Date().toISOString(),
                total_dependencies: this.dependencies.size,
                health_summary: await this.generateHealthSummary(),
                risk_analysis: await this.analyzeRisks(),
                recommendations: await this.generateRecommendations()
            };
            
            this.emit('deps:health_check_complete', healthReport);
            
            return healthReport;
            
        } catch (error) {
            console.error('❌ Health check failed:', error);
            this.emit('deps:health_check_error', error);
        }
    }

    /**
     * Generate health summary
     */
    async generateHealthSummary() {
        const dependencies = Array.from(this.dependencies.values());
        
        const healthCategories = {
            excellent: dependencies.filter(d => d.health_score >= 90).length,
            good: dependencies.filter(d => d.health_score >= 70 && d.health_score < 90).length,
            fair: dependencies.filter(d => d.health_score >= 50 && d.health_score < 70).length,
            poor: dependencies.filter(d => d.health_score < 50).length
        };
        
        const riskCategories = {
            low: dependencies.filter(d => d.risk_level === 'low').length,
            medium: dependencies.filter(d => d.risk_level === 'medium').length,
            high: dependencies.filter(d => d.risk_level === 'high').length
        };
        
        return {
            health_distribution: healthCategories,
            risk_distribution: riskCategories,
            average_health_score: dependencies.reduce((sum, d) => sum + d.health_score, 0) / dependencies.length,
            deprecated_count: dependencies.filter(d => d.deprecated).length,
            outdated_count: dependencies.filter(d => d.update_recommendation !== 'up_to_date').length
        };
    }

    /**
     * Analyze risks
     */
    async analyzeRisks() {
        const dependencies = Array.from(this.dependencies.values());
        const highRiskDeps = dependencies.filter(d => d.risk_level === 'high');
        
        return {
            total_vulnerabilities: this.vulnerabilities.length,
            critical_vulnerabilities: this.vulnerabilities.filter(v => v.severity === 'critical').length,
            high_risk_dependencies: highRiskDeps.length,
            deprecated_dependencies: dependencies.filter(d => d.deprecated).length,
            license_risks: await this.assessLicenseRisks(),
            supply_chain_risk: this.assessSupplyChainRisk()
        };
    }

    /**
     * Assess license risks
     */
    async assessLicenseRisks() {
        const dependencies = Array.from(this.dependencies.values());
        const licenses = dependencies.map(d => d.license);
        
        const restrictiveLicenses = ['GPL-3.0', 'AGPL-3.0', 'LGPL-3.0'];
        const riskyLicenses = licenses.filter(license => 
            restrictiveLicenses.includes(license)
        );
        
        return {
            total_licenses: new Set(licenses).size,
            risky_licenses: riskyLicenses.length,
            unknown_licenses: licenses.filter(l => !l || l === 'UNKNOWN').length
        };
    }

    /**
     * Assess supply chain risk
     */
    assessSupplyChainRisk() {
        const dependencies = Array.from(this.dependencies.values());
        
        // Calculate based on various factors
        let riskScore = 0;
        
        // High number of dependencies increases risk
        if (dependencies.length > 500) riskScore += 30;
        else if (dependencies.length > 200) riskScore += 15;
        
        // Outdated dependencies increase risk
        const outdatedCount = dependencies.filter(d => d.update_recommendation !== 'up_to_date').length;
        riskScore += (outdatedCount / dependencies.length) * 40;
        
        // Vulnerabilities increase risk
        riskScore += this.vulnerabilities.length * 5;
        
        // Deprecated dependencies increase risk
        const deprecatedCount = dependencies.filter(d => d.deprecated).length;
        riskScore += deprecatedCount * 10;
        
        if (riskScore > 70) return 'high';
        if (riskScore > 40) return 'medium';
        return 'low';
    }

    /**
     * Generate recommendations
     */
    async generateRecommendations() {
        const recommendations = [];
        const dependencies = Array.from(this.dependencies.values());
        
        // Vulnerability recommendations
        if (this.vulnerabilities.length > 0) {
            recommendations.push({
                priority: 'high',
                category: 'security',
                title: 'Address Security Vulnerabilities',
                description: `Fix ${this.vulnerabilities.length} security vulnerabilities`,
                action: 'update_vulnerable_dependencies'
            });
        }
        
        // Deprecated dependency recommendations
        const deprecated = dependencies.filter(d => d.deprecated);
        if (deprecated.length > 0) {
            recommendations.push({
                priority: 'medium',
                category: 'maintenance',
                title: 'Replace Deprecated Dependencies',
                description: `Replace ${deprecated.length} deprecated dependencies`,
                action: 'migrate_deprecated_dependencies'
            });
        }
        
        // Update recommendations
        const outdated = dependencies.filter(d => d.update_recommendation !== 'up_to_date');
        if (outdated.length > 10) {
            recommendations.push({
                priority: 'low',
                category: 'maintenance',
                title: 'Update Dependencies',
                description: `Update ${outdated.length} outdated dependencies`,
                action: 'batch_update_dependencies'
            });
        }
        
        return recommendations;
    }

    /**
     * Check license compliance
     */
    async checkLicenseCompliance() {
        try {
            console.log('⚖️ Checking license compliance...');
            
            const complianceReport = await this.generateComplianceReport();
            
            this.emit('deps:license_compliance_checked', complianceReport);
            
            return complianceReport;
            
        } catch (error) {
            console.error('❌ License compliance check failed:', error);
            this.emit('deps:license_compliance_error', error);
        }
    }

    /**
     * Generate compliance report
     */
    async generateComplianceReport() {
        const dependencies = Array.from(this.dependencies.values());
        const licenses = {};
        
        // Group by license
        for (const dep of dependencies) {
            const license = dep.license || 'UNKNOWN';
            if (!licenses[license]) {
                licenses[license] = {
                    dependencies: [],
                    count: 0,
                    risk_level: this.assessLicenseRisk(license)
                };
            }
            licenses[license].dependencies.push(dep.name);
            licenses[license].count++;
        }
        
        return {
            timestamp: new Date().toISOString(),
            total_dependencies: dependencies.length,
            unique_licenses: Object.keys(licenses).length,
            license_breakdown: licenses,
            compliance_issues: this.findComplianceIssues(licenses),
            recommendations: this.generateLicenseRecommendations(licenses)
        };
    }

    /**
     * Assess license risk
     */
    assessLicenseRisk(license) {
        const highRisk = ['GPL-3.0', 'AGPL-3.0'];
        const mediumRisk = ['LGPL-3.0', 'GPL-2.0'];
        const lowRisk = ['MIT', 'Apache-2.0', 'BSD-3-Clause', 'ISC'];
        
        if (highRisk.includes(license)) return 'high';
        if (mediumRisk.includes(license)) return 'medium';
        if (lowRisk.includes(license)) return 'low';
        return 'unknown';
    }

    /**
     * Find compliance issues
     */
    findComplianceIssues(licenses) {
        const issues = [];
        
        for (const [license, info] of Object.entries(licenses)) {
            if (info.risk_level === 'high') {
                issues.push({
                    type: 'high_risk_license',
                    license,
                    count: info.count,
                    dependencies: info.dependencies
                });
            }
            
            if (license === 'UNKNOWN') {
                issues.push({
                    type: 'unknown_license',
                    count: info.count,
                    dependencies: info.dependencies
                });
            }
        }
        
        return issues;
    }

    /**
     * Generate license recommendations
     */
    generateLicenseRecommendations(licenses) {
        const recommendations = [];
        
        if (licenses['UNKNOWN']) {
            recommendations.push({
                priority: 'medium',
                title: 'Identify Unknown Licenses',
                description: `Identify licenses for ${licenses['UNKNOWN'].count} dependencies`
            });
        }
        
        const highRiskLicenses = Object.entries(licenses).filter(([_, info]) => info.risk_level === 'high');
        if (highRiskLicenses.length > 0) {
            recommendations.push({
                priority: 'high',
                title: 'Review High-Risk Licenses',
                description: 'Review and potentially replace dependencies with high-risk licenses'
            });
        }
        
        return recommendations;
    }

    /**
     * Get dependency report
     */
    async getDependencyReport() {
        const report = {
            timestamp: new Date().toISOString(),
            summary: await this.generateHealthSummary(),
            dependencies: Array.from(this.dependencies.values()),
            vulnerabilities: this.vulnerabilities,
            update_queue: this.updateQueue,
            recommendations: await this.generateRecommendations(),
            compliance: await this.generateComplianceReport()
        };

        return report;
    }

    /**
     * Stop dependency monitoring
     */
    async stopMonitoring() {
        this.monitoringActive = false;
        if (this.checkInterval) {
            clearInterval(this.checkInterval);
            this.checkInterval = null;
        }
        console.log('🛑 Dependency monitoring stopped');
        this.emit('deps:stopped');
    }
}

module.exports = DependencyMonitor;

// Example usage
if (require.main === module) {
    const depMonitor = new DependencyMonitor();
    
    // Set up event listeners
    depMonitor.on('deps:initialized', () => {
        console.log('✅ Dependency monitor is ready');
    });
    
    depMonitor.on('deps:scan_complete', (dependencies) => {
        console.log(`📦 Scanned ${dependencies.length} dependencies`);
    });
    
    depMonitor.on('deps:security_scan_complete', (vulnerabilities) => {
        console.log(`🔒 Security scan: ${vulnerabilities.length} vulnerabilities found`);
    });
    
    depMonitor.on('deps:critical_vulnerability', (vulnerability) => {
        console.log(`🚨 Critical vulnerability: ${vulnerability.title}`);
    });
    
    // Initialize dependency monitoring
    depMonitor.initialize();
}
