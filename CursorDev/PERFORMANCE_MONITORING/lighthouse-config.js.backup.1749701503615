/**
 * 🚀 SELINAY LIGHTHOUSE CONFIG - Automated Performance Auditing
 * MesChain-Sync Enterprise Lighthouse Configuration
 * Task 6.3 - Performance Monitoring Setup
 * Created: June 5, 2025
 */

const lighthouse = require('lighthouse');
const chromeLauncher = require('chrome-launcher');
const fs = require('fs').promises;
const path = require('path');

class SelinayLighthouseConfig {
    constructor() {
        this.config = {
            extends: 'lighthouse:default',
            settings: {
                maxWaitForFcp: 15 * 1000,
                maxWaitForLoad: 35 * 1000,
                formFactor: 'desktop',
                throttling: {
                    rttMs: 40,
                    throughputKbps: 10240,
                    cpuSlowdownMultiplier: 1,
                    requestLatencyMs: 0,
                    downloadThroughputKbps: 0,
                    uploadThroughputKbps: 0
                },
                screenEmulation: {
                    mobile: false,
                    width: 1350,
                    height: 940,
                    deviceScaleFactor: 1,
                    disabled: false
                },
                emulatedUserAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            },
            audits: [
                // Performance audits
                'first-contentful-paint',
                'largest-contentful-paint',
                'first-meaningful-paint',
                'speed-index',
                'interactive',
                'max-potential-fid',
                'cumulative-layout-shift',
                'total-blocking-time',
                
                // Resource audits
                'unused-javascript',
                'unused-css-rules',
                'unminified-css',
                'unminified-javascript',
                'render-blocking-resources',
                'efficient-animated-content',
                'offscreen-images',
                'webp-images',
                'uses-optimized-images',
                'uses-text-compression',
                'uses-responsive-images',
                
                // Best practices
                'uses-https',
                'uses-http2',
                'no-document-write',
                'external-anchors-use-rel-noopener',
                'geolocation-on-start',
                'notification-on-start',
                
                // Accessibility
                'color-contrast',
                'image-alt',
                'label',
                'tabindex',
                'heading-order',
                
                // SEO
                'document-title',
                'meta-description',
                'viewport',
                'canonical',
                
                // Turkish/International
                'hreflang',
                'charset'
            ],
            categories: {
                performance: {
                    title: 'Performance',
                    auditRefs: [
                        { id: 'first-contentful-paint', weight: 10 },
                        { id: 'largest-contentful-paint', weight: 25 },
                        { id: 'first-meaningful-paint', weight: 10 },
                        { id: 'speed-index', weight: 10 },
                        { id: 'interactive', weight: 10 },
                        { id: 'max-potential-fid', weight: 10 },
                        { id: 'cumulative-layout-shift', weight: 15 },
                        { id: 'total-blocking-time', weight: 10 }
                    ]
                },
                accessibility: {
                    title: 'Accessibility',
                    auditRefs: [
                        { id: 'color-contrast', weight: 3 },
                        { id: 'image-alt', weight: 3 },
                        { id: 'label', weight: 3 },
                        { id: 'tabindex', weight: 3 },
                        { id: 'heading-order', weight: 2 }
                    ]
                },
                'best-practices': {
                    title: 'Best Practices',
                    auditRefs: [
                        { id: 'uses-https', weight: 1 },
                        { id: 'uses-http2', weight: 1 },
                        { id: 'no-document-write', weight: 1 },
                        { id: 'external-anchors-use-rel-noopener', weight: 1 },
                        { id: 'geolocation-on-start', weight: 1 },
                        { id: 'notification-on-start', weight: 1 }
                    ]
                },
                seo: {
                    title: 'SEO',
                    auditRefs: [
                        { id: 'document-title', weight: 1 },
                        { id: 'meta-description', weight: 1 },
                        { id: 'viewport', weight: 1 },
                        { id: 'canonical', weight: 1 },
                        { id: 'hreflang', weight: 1 },
                        { id: 'charset', weight: 1 }
                    ]
                }
            }
        };
        
        this.mobileConfig = {
            ...this.config,
            settings: {
                ...this.config.settings,
                formFactor: 'mobile',
                throttling: {
                    rttMs: 150,
                    throughputKbps: 1638.4,
                    cpuSlowdownMultiplier: 4,
                    requestLatencyMs: 150 * 3.75,
                    downloadThroughputKbps: 1638.4 * 0.9,
                    uploadThroughputKbps: 1638.4 * 0.9
                },
                screenEmulation: {
                    mobile: true,
                    width: 360,
                    height: 640,
                    deviceScaleFactor: 2.625,
                    disabled: false
                }
            }
        };
        
        this.testUrls = [
            'http://localhost:3000',  // Dashboard
            'http://localhost:3001',  // Frontend
            'http://localhost:3002',  // Super Admin
            'http://localhost:3003'   // Marketplace
        ];
        
        this.chrome = null;
        this.results = [];
    }

    /**
     * Launch Chrome for Lighthouse testing
     */
    async launchChrome() {
        console.log('🚀 Launching Chrome for Lighthouse monitoring...');
        
        this.chrome = await chromeLauncher.launch({
            chromeFlags: [
                '--headless',
                '--disable-gpu',
                '--no-sandbox',
                '--disable-dev-shm-usage',
                '--disable-extensions',
                '--disable-default-apps'
            ]
        });
        
        console.log(`✅ Chrome launched on port ${this.chrome.port}`);
        return this.chrome.port;
    }

    /**
     * Run Lighthouse audit for a specific URL
     */
    async runAudit(url, config = this.config, label = '') {
        console.log(`🔍 Running Lighthouse audit for ${url} ${label}...`);
        
        const options = {
            logLevel: 'info',
            output: 'json',
            port: this.chrome.port,
            onlyCategories: ['performance', 'accessibility', 'best-practices', 'seo']
        };
        
        try {
            const runnerResult = await lighthouse(url, options, config);
            const report = runnerResult.lhr;
            
            const result = {
                url,
                label,
                timestamp: new Date().toISOString(),
                scores: {
                    performance: Math.round(report.categories.performance.score * 100),
                    accessibility: Math.round(report.categories.accessibility.score * 100),
                    bestPractices: Math.round(report.categories['best-practices'].score * 100),
                    seo: Math.round(report.categories.seo.score * 100)
                },
                metrics: {
                    firstContentfulPaint: report.audits['first-contentful-paint'].numericValue,
                    largestContentfulPaint: report.audits['largest-contentful-paint'].numericValue,
                    speedIndex: report.audits['speed-index'].numericValue,
                    interactive: report.audits['interactive'].numericValue,
                    totalBlockingTime: report.audits['total-blocking-time'].numericValue,
                    cumulativeLayoutShift: report.audits['cumulative-layout-shift'].numericValue
                },
                opportunities: this.extractOpportunities(report),
                diagnostics: this.extractDiagnostics(report),
                rawReport: report
            };
            
            this.results.push(result);
            
            const score = result.scores.performance;
            const icon = score >= 90 ? '✅' : score >= 70 ? '⚠️' : '❌';
            console.log(`${icon} ${label || url}: Performance Score ${score}/100`);
            
            return result;
            
        } catch (error) {
            console.error(`❌ Lighthouse audit failed for ${url}: ${error.message}`);
            throw error;
        }
    }

    /**
     * Extract optimization opportunities from Lighthouse report
     */
    extractOpportunities(report) {
        const opportunities = [];
        const opportunityAudits = [
            'unused-javascript',
            'unused-css-rules',
            'render-blocking-resources',
            'offscreen-images',
            'unminified-css',
            'unminified-javascript',
            'uses-optimized-images',
            'uses-webp-images',
            'efficient-animated-content'
        ];
        
        opportunityAudits.forEach(auditId => {
            if (report.audits[auditId] && report.audits[auditId].details) {
                const audit = report.audits[auditId];
                if (audit.score < 1 && audit.details.overallSavingsMs > 0) {
                    opportunities.push({
                        id: auditId,
                        title: audit.title,
                        description: audit.description,
                        savingsMs: audit.details.overallSavingsMs,
                        savingsBytes: audit.details.overallSavingsBytes || 0,
                        score: audit.score
                    });
                }
            }
        });
        
        return opportunities.sort((a, b) => b.savingsMs - a.savingsMs);
    }

    /**
     * Extract diagnostics from Lighthouse report
     */
    extractDiagnostics(report) {
        const diagnostics = [];
        const diagnosticAudits = [
            'mainthread-work-breakdown',
            'bootup-time',
            'uses-rel-preload',
            'font-display',
            'third-party-summary'
        ];
        
        diagnosticAudits.forEach(auditId => {
            if (report.audits[auditId]) {
                const audit = report.audits[auditId];
                diagnostics.push({
                    id: auditId,
                    title: audit.title,
                    description: audit.description,
                    score: audit.score,
                    displayValue: audit.displayValue
                });
            }
        });
        
        return diagnostics;
    }

    /**
     * Run comprehensive monitoring across all URLs and form factors
     */
    async runComprehensiveMonitoring() {
        console.log('📊 Starting Selinay Comprehensive Lighthouse Monitoring...');
        
        await this.launchChrome();
        
        try {
            for (const url of this.testUrls) {
                // Desktop audit
                await this.runAudit(url, this.config, '(Desktop)');
                
                // Mobile audit
                await this.runAudit(url, this.mobileConfig, '(Mobile)');
                
                // Wait between audits to avoid overwhelming the system
                await new Promise(resolve => setTimeout(resolve, 2000));
            }
            
            return this.generateReport();
            
        } catch (error) {
            console.error('❌ Comprehensive monitoring failed:', error);
            throw error;
        } finally {
            await this.cleanup();
        }
    }

    /**
     * Generate performance monitoring report
     */
    async generateReport() {
        console.log('📋 Generating Lighthouse monitoring report...');
        
        const summary = {
            totalAudits: this.results.length,
            averageScores: {
                performance: Math.round(this.results.reduce((sum, r) => sum + r.scores.performance, 0) / this.results.length),
                accessibility: Math.round(this.results.reduce((sum, r) => sum + r.scores.accessibility, 0) / this.results.length),
                bestPractices: Math.round(this.results.reduce((sum, r) => sum + r.scores.bestPractices, 0) / this.results.length),
                seo: Math.round(this.results.reduce((sum, r) => sum + r.scores.seo, 0) / this.results.length)
            },
            criticalIssues: this.results.filter(r => r.scores.performance < 50).length,
            needsImprovement: this.results.filter(r => r.scores.performance >= 50 && r.scores.performance < 80).length,
            goodPerformance: this.results.filter(r => r.scores.performance >= 80).length
        };
        
        // Aggregate opportunities
        const allOpportunities = this.results.flatMap(r => r.opportunities);
        const uniqueOpportunities = this.aggregateOpportunities(allOpportunities);
        
        const report = {
            summary,
            results: this.results,
            topOpportunities: uniqueOpportunities.slice(0, 10),
            timestamp: new Date().toISOString(),
            config: {
                desktop: this.config,
                mobile: this.mobileConfig
            }
        };
        
        // Save report
        const reportPath = path.join(process.cwd(), 'lighthouse-monitoring-report.json');
        await fs.writeFile(reportPath, JSON.stringify(report, null, 2));
        
        console.log(`📄 Lighthouse report saved to: ${reportPath}`);
        
        return report;
    }

    /**
     * Aggregate similar opportunities across audits
     */
    aggregateOpportunities(opportunities) {
        const grouped = opportunities.reduce((acc, opp) => {
            if (!acc[opp.id]) {
                acc[opp.id] = {
                    ...opp,
                    totalSavingsMs: 0,
                    totalSavingsBytes: 0,
                    occurrences: 0
                };
            }
            acc[opp.id].totalSavingsMs += opp.savingsMs;
            acc[opp.id].totalSavingsBytes += opp.savingsBytes;
            acc[opp.id].occurrences++;
            return acc;
        }, {});
        
        return Object.values(grouped).sort((a, b) => b.totalSavingsMs - a.totalSavingsMs);
    }

    /**
     * Get configuration for specific use case
     */
    getConfig(type = 'desktop') {
        switch (type) {
            case 'mobile':
                return this.mobileConfig;
            case 'desktop':
            default:
                return this.config;
        }
    }

    /**
     * Cleanup Chrome instance
     */
    async cleanup() {
        if (this.chrome) {
            console.log('🧹 Closing Chrome instance...');
            await this.chrome.kill();
        }
    }
}

/**
 * Selinay Lighthouse Scheduler - Run automated performance monitoring
 */
class SelinayLighthouseScheduler {
    constructor(interval = 3600000) { // Default: 1 hour
        this.interval = interval;
        this.lighthouse = new SelinayLighthouseConfig();
        this.isRunning = false;
        this.intervalId = null;
    }

    /**
     * Start scheduled monitoring
     */
    start() {
        if (this.isRunning) {
            console.log('⚠️ Lighthouse monitoring already running');
            return;
        }
        
        console.log(`🚀 Starting Selinay Lighthouse monitoring (interval: ${this.interval / 1000 / 60} minutes)`);
        
        this.isRunning = true;
        
        // Run initial monitoring
        this.runMonitoring();
        
        // Schedule recurring monitoring
        this.intervalId = setInterval(() => {
            this.runMonitoring();
        }, this.interval);
    }

    /**
     * Stop scheduled monitoring
     */
    stop() {
        if (!this.isRunning) {
            console.log('⚠️ Lighthouse monitoring not running');
            return;
        }
        
        console.log('🛑 Stopping Selinay Lighthouse monitoring');
        
        this.isRunning = false;
        
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
    }

    /**
     * Run monitoring cycle
     */
    async runMonitoring() {
        try {
            console.log('🔄 Running scheduled Lighthouse monitoring...');
            const report = await this.lighthouse.runComprehensiveMonitoring();
            
            // Check for performance regressions
            this.checkPerformanceRegressions(report);
            
            console.log('✅ Scheduled monitoring completed successfully');
            
        } catch (error) {
            console.error('❌ Scheduled monitoring failed:', error);
        }
    }

    /**
     * Check for performance regressions
     */
    checkPerformanceRegressions(report) {
        const criticalThreshold = 70;
        const regressions = report.results.filter(r => r.scores.performance < criticalThreshold);
        
        if (regressions.length > 0) {
            console.warn(`🚨 PERFORMANCE ALERT: ${regressions.length} pages below critical threshold (${criticalThreshold})`);
            regressions.forEach(regression => {
                console.warn(`  - ${regression.url} ${regression.label}: ${regression.scores.performance}/100`);
            });
        }
    }
}

// Export classes and configurations
module.exports = {
    SelinayLighthouseConfig,
    SelinayLighthouseScheduler
};

// CLI execution
if (require.main === module) {
    const args = process.argv.slice(2);
    const command = args[0] || 'run';
    
    if (command === 'schedule') {
        const interval = parseInt(args[1]) || 60; // minutes
        const scheduler = new SelinayLighthouseScheduler(interval * 60 * 1000);
        scheduler.start();
        
        // Graceful shutdown
        process.on('SIGINT', () => {
            scheduler.stop();
            process.exit(0);
        });
        
    } else {
        // Run one-time monitoring
        const lighthouse = new SelinayLighthouseConfig();
        lighthouse.runComprehensiveMonitoring()
            .then(report => {
                console.log('✅ Lighthouse monitoring completed');
                const avgScore = report.summary.averageScores.performance;
                process.exit(avgScore < 70 ? 1 : 0);
            })
            .catch(error => {
                console.error('❌ Lighthouse monitoring failed:', error);
                process.exit(1);
            });
    }
}

/**
 * 🌟 SELINAY LIGHTHOUSE CONFIG - FEATURE HIGHLIGHTS
 * 
 * ✅ Comprehensive performance monitoring (Core Web Vitals)
 * ✅ Desktop and mobile form factor testing
 * ✅ Automated opportunity detection and prioritization
 * ✅ Accessibility and SEO auditing
 * ✅ Turkish language and internationalization support
 * ✅ Scheduled monitoring with regression detection
 * ✅ Performance alert system
 * ✅ Detailed reporting and analytics
 * ✅ CI/CD integration ready
 * ✅ Production monitoring capabilities
 * 
 * Production-ready for MesChain-Sync Enterprise
 * Created by Selinay Frontend UI/UX Team - June 5, 2025
 */
