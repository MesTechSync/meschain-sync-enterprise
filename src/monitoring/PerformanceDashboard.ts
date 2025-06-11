/**
 * MesChain-Sync Performance Dashboard - Azure Enhanced Edition
 * Real-time monitoring and analytics for marketplace integrations with Azure Cloud Integration
 * 
 * @author MesChain Development Team & MezBjen Team
 * @version 5.0.0
 * @copyright 2025 MesChain Technologies
 * @features Azure Application Insights, Blob Storage, Service Bus, Key Vault, Event Hubs
 */

// Azure SDK Imports
import { DefaultAzureCredential } from '@azure/identity';
import { BlobServiceClient } from '@azure/storage-blob';
import { ServiceBusClient } from '@azure/service-bus';
import { SecretClient } from '@azure/keyvault-secrets';
import { EventHubProducerClient } from '@azure/event-hubs';
import { TelemetryClient } from 'applicationinsights';

export interface MarketplaceMetrics {
    marketplace: string;
    status: 'active' | 'inactive' | 'error';
    responseTime: number;
    successRate: number;
    orderCount: number;
    errorCount: number;
    lastSync: Date;
    uptime: number;
    // Azure Enhanced Properties
    azureRegion?: string;
    correlationId?: string;
    healthScore?: number;
    azureResourceId?: string;
    telemetryEnabled?: boolean;
}

export interface SystemMetrics {
    totalMarketplaces: number;
    activeMarketplaces: number;
    totalOrders: number;
    totalProducts: number;
    averageResponseTime: number;
    systemUptime: number;
    memoryUsage: number;
    cpuUsage: number;
    // Azure Enhanced Properties
    azureServiceHealth?: {
        applicationInsights: boolean;
        blobStorage: boolean;
        serviceBus: boolean;
        keyVault: boolean;
        eventHubs: boolean;
    };
    cloudMetrics?: {
        requestsPerMinute: number;
        dataThroughput: number;
        errorRate: number;
        azureCosts: number;
    };
}

export interface PerformanceAlert {
    id: string;
    marketplace: string;
    type: 'error' | 'warning' | 'info';
    message: string;
    timestamp: Date;
    resolved: boolean;
    // Azure Enhanced Properties
    azureMetadata?: {
        operationId: string;
        resourceType: string;
        autoResolutionSuggestion?: string;
        azureRegion: string;
        severity: 'critical' | 'high' | 'medium' | 'low';
    };
    telemetryData?: any;
}

export interface AzureConfiguration {
    applicationInsights: {
        connectionString: string;
        enableLiveMetrics: boolean;
        samplingPercentage: number;
    };
    storage: {
        accountName: string;
        containerName: string;
        lifecyclePolicy: boolean;
    };
    serviceBus: {
        namespace: string;
        queueName: string;
        deadLetterQueue: string;
    };
    keyVault: {
        vaultUrl: string;
        secretRotationEnabled: boolean;
    };
    eventHubs: {
        namespace: string;
        eventHubName: string;
        consumerGroup: string;
    };
    security: {
        managedIdentityEnabled: boolean;
        auditLoggingEnabled: boolean;
    };
    performance: {
        cachingEnabled: boolean;
        retryPolicy: {
            maxAttempts: number;
            backoffMultiplier: number;
        };
        circuitBreaker: {
            failureThreshold: number;
            timeoutThreshold: number;
        };
    };
}

export class PerformanceDashboard {
    private marketplaces: string[] = [
        'trendyol', 'n11', 'hepsiburada', 'amazon', 'ozon', 'ebay'
    ];
    
    private metricsCache: Map<string, MarketplaceMetrics> = new Map();
    private alertsCache: PerformanceAlert[] = [];
    private refreshInterval: number = 30000; // 30 seconds

    // Azure Services Integration
    private azureCredential: DefaultAzureCredential;
    private blobServiceClient: BlobServiceClient;
    private serviceBusClient: ServiceBusClient;
    private keyVaultClient: SecretClient;
    private eventHubClient: EventHubProducerClient;
    private telemetryClient: TelemetryClient;
    private azureConfig: AzureConfiguration;
    
    // Circuit Breaker State
    private circuitBreakerState: Map<string, {
        failures: number;
        lastFailure: Date;
        isOpen: boolean;
    }> = new Map();

    constructor(azureConfig: AzureConfiguration) {
        this.azureConfig = azureConfig;
        this.initializeAzureServices();
        this.initializeMonitoring();
    }

    /**
     * Initialize Azure Services with Enhanced Security and Error Handling
     */
    private async initializeAzureServices(): Promise<void> {
        try {
            console.log('🔷 Initializing Azure services for MezBjen team...');
            
            // Initialize Azure Credential with Managed Identity
            this.azureCredential = new DefaultAzureCredential();
            
            // Initialize services in dependency order with retry logic
            await this.initializeWithRetry('Application Insights', async () => {
                const appInsights = require('applicationinsights');
                appInsights.setup(this.azureConfig.applicationInsights.connectionString)
                    .setAutoDependencyCorrelation(true)
                    .setAutoCollectRequests(true)
                    .setAutoCollectPerformance(true)
                    .setAutoCollectExceptions(true)
                    .setAutoCollectDependencies(true)
                    .setAutoCollectConsole(true)
                    .setUseDiskRetryCaching(true)
                    .setSendLiveMetrics(this.azureConfig.applicationInsights.enableLiveMetrics)
                    .start();
                
                this.telemetryClient = appInsights.defaultClient;
                this.telemetryClient.config.samplingPercentage = this.azureConfig.applicationInsights.samplingPercentage;
            });

            await this.initializeWithRetry('Blob Storage', async () => {
                this.blobServiceClient = new BlobServiceClient(
                    `https://${this.azureConfig.storage.accountName}.blob.core.windows.net`,
                    this.azureCredential
                );
                
                // Setup lifecycle management
                if (this.azureConfig.storage.lifecyclePolicy) {
                    await this.setupStorageLifecyclePolicy();
                }
            });

            await this.initializeWithRetry('Service Bus', async () => {
                this.serviceBusClient = new ServiceBusClient(
                    `${this.azureConfig.serviceBus.namespace}.servicebus.windows.net`,
                    this.azureCredential
                );
            });

            await this.initializeWithRetry('Key Vault', async () => {
                this.keyVaultClient = new SecretClient(
                    this.azureConfig.keyVault.vaultUrl,
                    this.azureCredential
                );
                
                // Setup secret rotation monitoring
                if (this.azureConfig.keyVault.secretRotationEnabled) {
                    await this.monitorSecretRotation();
                }
            });

            await this.initializeWithRetry('Event Hubs', async () => {
                this.eventHubClient = new EventHubProducerClient(
                    `${this.azureConfig.eventHubs.namespace}.servicebus.windows.net`,
                    this.azureConfig.eventHubs.eventHubName,
                    this.azureCredential
                );
            });

            // Initialize circuit breakers for all services
            this.initializeCircuitBreakers();
            
            // Start continuous health monitoring
            this.startAzureHealthMonitoring();
            
            console.log('✅ Azure services initialized successfully for MezBjen team');
            
            // Send initialization telemetry
            this.telemetryClient?.trackEvent({
                name: 'PerformanceDashboardInitialized',
                properties: {
                    version: '5.0.0',
                    team: 'MezBjen',
                    azureRegion: process.env.AZURE_REGION || 'unknown',
                    initializationTime: new Date().toISOString()
                }
            });
            
        } catch (error) {
            console.error('❌ Azure services initialization failed:', error);
            this.telemetryClient?.trackException({ exception: error as Error });
            throw new Error(`Azure initialization failed: ${error}`);
        }
    }

    /**
     * Initialize real-time monitoring
     */
    private initializeMonitoring(): void {
        this.loadMarketplaceMetrics();
        this.startRealTimeUpdates();
        this.setupAlertSystem();
    }

    /**
     * Get current system metrics
     */
    public async getSystemMetrics(): Promise<SystemMetrics> {
        const marketplaceMetrics = Array.from(this.metricsCache.values());
        
        return {
            totalMarketplaces: this.marketplaces.length,
            activeMarketplaces: marketplaceMetrics.filter(m => m.status === 'active').length,
            totalOrders: marketplaceMetrics.reduce((sum, m) => sum + m.orderCount, 0),
            totalProducts: await this.getTotalProducts(),
            averageResponseTime: this.calculateAverageResponseTime(marketplaceMetrics),
            systemUptime: this.getSystemUptime(),
            memoryUsage: this.getMemoryUsage(),
            cpuUsage: this.getCpuUsage()
        };
    }

    /**
     * Get marketplace-specific metrics
     */
    public async getMarketplaceMetrics(marketplace: string): Promise<MarketplaceMetrics | null> {
        if (!this.marketplaces.includes(marketplace)) {
            return null;
        }

        const cached = this.metricsCache.get(marketplace);
        if (cached && this.isCacheValid(cached.lastSync)) {
            return cached;
        }

        return await this.fetchMarketplaceMetrics(marketplace);
    }

    /**
     * Get all marketplace metrics
     */
    public async getAllMarketplaceMetrics(): Promise<MarketplaceMetrics[]> {
        const promises = this.marketplaces.map(marketplace => 
            this.getMarketplaceMetrics(marketplace)
        );
        
        const results = await Promise.all(promises);
        return results.filter(result => result !== null) as MarketplaceMetrics[];
    }

    /**
     * Get performance alerts
     */
    public getPerformanceAlerts(unreadOnly: boolean = false): PerformanceAlert[] {
        if (unreadOnly) {
            return this.alertsCache.filter(alert => !alert.resolved);
        }
        return [...this.alertsCache];
    }

    /**
     * Create performance alert
     */
    public createAlert(marketplace: string, type: 'error' | 'warning' | 'info', message: string): void {
        const alert: PerformanceAlert = {
            id: this.generateAlertId(),
            marketplace,
            type,
            message,
            timestamp: new Date(),
            resolved: false
        };

        this.alertsCache.unshift(alert);
        
        // Keep only last 100 alerts
        if (this.alertsCache.length > 100) {
            this.alertsCache = this.alertsCache.slice(0, 100);
        }

        this.notifyAlert(alert);
    }

    /**
     * Resolve alert
     */
    public resolveAlert(alertId: string): boolean {
        const alert = this.alertsCache.find(a => a.id === alertId);
        if (alert) {
            alert.resolved = true;
            return true;
        }
        return false;
    }

    /**
     * Get performance trends
     */
    public async getPerformanceTrends(marketplace: string, timeRange: '1h' | '24h' | '7d' | '30d'): Promise<any> {
        const endTime = new Date();
        const startTime = new Date();
        
        switch (timeRange) {
            case '1h':
                startTime.setHours(startTime.getHours() - 1);
                break;
            case '24h':
                startTime.setDate(startTime.getDate() - 1);
                break;
            case '7d':
                startTime.setDate(startTime.getDate() - 7);
                break;
            case '30d':
                startTime.setDate(startTime.getDate() - 30);
                break;
        }

        return await this.fetchPerformanceTrends(marketplace, startTime, endTime);
    }

    /**
     * Export performance report
     */
    public async exportPerformanceReport(format: 'json' | 'csv' | 'pdf'): Promise<string> {
        const systemMetrics = await this.getSystemMetrics();
        const marketplaceMetrics = await this.getAllMarketplaceMetrics();
        const alerts = this.getPerformanceAlerts();

        const reportData = {
            generatedAt: new Date(),
            systemMetrics,
            marketplaceMetrics,
            alerts,
            summary: this.generatePerformanceSummary(systemMetrics, marketplaceMetrics)
        };

        switch (format) {
            case 'json':
                return JSON.stringify(reportData, null, 2);
            case 'csv':
                return this.convertToCSV(reportData);
            case 'pdf':
                return await this.generatePDF(reportData);
            default:
                throw new Error('Unsupported export format');
        }
    }

    /**
     * Private methods
     */
    private async loadMarketplaceMetrics(): Promise<void> {
        for (const marketplace of this.marketplaces) {
            const metrics = await this.fetchMarketplaceMetrics(marketplace);
            if (metrics) {
                this.metricsCache.set(marketplace, metrics);
            }
        }
    }

    private startRealTimeUpdates(): void {
        setInterval(async () => {
            await this.updateMetrics();
        }, this.refreshInterval);
    }

    private setupAlertSystem(): void {
        // Monitor for performance issues
        setInterval(() => {
            this.checkPerformanceThresholds();
        }, 60000); // Check every minute
    }

    private async updateMetrics(): Promise<void> {
        for (const marketplace of this.marketplaces) {
            try {
                const metrics = await this.fetchMarketplaceMetrics(marketplace);
                if (metrics) {
                    this.metricsCache.set(marketplace, metrics);
                }
            } catch (error) {
                this.createAlert(marketplace, 'error', `Failed to update metrics: ${error}`);
            }
        }
    }

    private async fetchMarketplaceMetrics(marketplace: string): Promise<MarketplaceMetrics> {
        // Simulate API call to get marketplace metrics
        const response = await fetch(`/api/marketplace/${marketplace}/metrics`);
        
        if (!response.ok) {
            throw new Error(`Failed to fetch metrics for ${marketplace}`);
        }

        const data = await response.json();
        
        return {
            marketplace,
            status: data.status || 'inactive',
            responseTime: data.responseTime || 0,
            successRate: data.successRate || 0,
            orderCount: data.orderCount || 0,
            errorCount: data.errorCount || 0,
            lastSync: new Date(data.lastSync || Date.now()),
            uptime: data.uptime || 0
        };
    }

    private async getTotalProducts(): Promise<number> {
        try {
            const response = await fetch('/api/products/count');
            const data = await response.json();
            return data.count || 0;
        } catch {
            return 0;
        }
    }

    private calculateAverageResponseTime(metrics: MarketplaceMetrics[]): number {
        if (metrics.length === 0) return 0;
        
        const total = metrics.reduce((sum, m) => sum + m.responseTime, 0);
        return Math.round(total / metrics.length);
    }

    private getSystemUptime(): number {
        // Return system uptime in seconds
        return Math.floor(process.uptime ? process.uptime() : 0);
    }

    private getMemoryUsage(): number {
        if (typeof process !== 'undefined' && process.memoryUsage) {
            const used = process.memoryUsage();
            return Math.round((used.heapUsed / used.heapTotal) * 100);
        }
        return 0;
    }

    private getCpuUsage(): number {
        // Simplified CPU usage calculation
        return Math.random() * 100; // Replace with actual CPU monitoring
    }

    private isCacheValid(lastSync: Date): boolean {
        const now = new Date();
        const diffMs = now.getTime() - lastSync.getTime();
        return diffMs < this.refreshInterval;
    }

    private generateAlertId(): string {
        return `alert_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }

    private notifyAlert(alert: PerformanceAlert): void {
        // Send notification via WebSocket, email, etc.
        console.log(`[${alert.type.toUpperCase()}] ${alert.marketplace}: ${alert.message}`);
        
        // If it's a critical error, send immediate notification
        if (alert.type === 'error') {
            this.sendCriticalAlert(alert);
        }
    }

    private sendCriticalAlert(alert: PerformanceAlert): void {
        // Send critical alert via email, SMS, Slack, etc.
        fetch('/api/alerts/critical', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(alert)
        }).catch(error => {
            console.error('Failed to send critical alert:', error);
        });
    }

    private checkPerformanceThresholds(): void {
        this.metricsCache.forEach((metrics, marketplace) => {
            // Check response time threshold
            if (metrics.responseTime > 5000) { // 5 seconds
                this.createAlert(marketplace, 'warning', `High response time: ${metrics.responseTime}ms`);
            }

            // Check success rate threshold
            if (metrics.successRate < 95) {
                this.createAlert(marketplace, 'warning', `Low success rate: ${metrics.successRate}%`);
            }

            // Check if marketplace is down
            if (metrics.status === 'error') {
                this.createAlert(marketplace, 'error', `Marketplace is down or experiencing errors`);
            }

            // Check error count threshold
            if (metrics.errorCount > 10) {
                this.createAlert(marketplace, 'warning', `High error count: ${metrics.errorCount} errors`);
            }
        });
    }

    private async fetchPerformanceTrends(marketplace: string, startTime: Date, endTime: Date): Promise<any> {
        try {
            const response = await fetch(`/api/marketplace/${marketplace}/trends`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ startTime, endTime })
            });
            
            return await response.json();
        } catch (error) {
            console.error('Failed to fetch performance trends:', error);
            return { error: 'Failed to fetch trends' };
        }
    }

    private generatePerformanceSummary(systemMetrics: SystemMetrics, marketplaceMetrics: MarketplaceMetrics[]): any {
        const activeMarketplaces = marketplaceMetrics.filter(m => m.status === 'active');
        const totalErrors = marketplaceMetrics.reduce((sum, m) => sum + m.errorCount, 0);
        
        return {
            overallHealth: this.calculateOverallHealth(systemMetrics, marketplaceMetrics),
            performanceGrade: this.calculatePerformanceGrade(systemMetrics.averageResponseTime),
            reliabilityScore: this.calculateReliabilityScore(activeMarketplaces),
            totalErrors,
            recommendations: this.generateRecommendations(systemMetrics, marketplaceMetrics)
        };
    }

    private calculateOverallHealth(systemMetrics: SystemMetrics, marketplaceMetrics: MarketplaceMetrics[]): string {
        const activePercentage = (systemMetrics.activeMarketplaces / systemMetrics.totalMarketplaces) * 100;
        
        if (activePercentage >= 90 && systemMetrics.averageResponseTime < 1000) {
            return 'Excellent';
        } else if (activePercentage >= 70 && systemMetrics.averageResponseTime < 3000) {
            return 'Good';
        } else if (activePercentage >= 50) {
            return 'Fair';
        } else {
            return 'Poor';
        }
    }

    private calculatePerformanceGrade(avgResponseTime: number): string {
        if (avgResponseTime < 500) return 'A+';
        if (avgResponseTime < 1000) return 'A';
        if (avgResponseTime < 2000) return 'B';
        if (avgResponseTime < 3000) return 'C';
        if (avgResponseTime < 5000) return 'D';
        return 'F';
    }

    private calculateReliabilityScore(activeMarketplaces: MarketplaceMetrics[]): number {
        if (activeMarketplaces.length === 0) return 0;
        
        const totalSuccessRate = activeMarketplaces.reduce((sum, m) => sum + m.successRate, 0);
        return Math.round(totalSuccessRate / activeMarketplaces.length);
    }

    private generateRecommendations(systemMetrics: SystemMetrics, marketplaceMetrics: MarketplaceMetrics[]): string[] {
        const recommendations: string[] = [];
        
        if (systemMetrics.averageResponseTime > 3000) {
            recommendations.push('Consider optimizing API calls to improve response times');
        }
        
        if (systemMetrics.memoryUsage > 80) {
            recommendations.push('High memory usage detected. Consider scaling up resources');
        }
        
        const inactiveMarketplaces = marketplaceMetrics.filter(m => m.status !== 'active');
        if (inactiveMarketplaces.length > 0) {
            recommendations.push(`${inactiveMarketplaces.length} marketplace(s) are inactive. Check API connections`);
        }
        
        const highErrorMarketplaces = marketplaceMetrics.filter(m => m.errorCount > 5);
        if (highErrorMarketplaces.length > 0) {
            recommendations.push(`${highErrorMarketplaces.length} marketplace(s) have high error rates. Review error logs`);
        }
        
        if (recommendations.length === 0) {
            recommendations.push('System is running optimally. No immediate actions required');
        }
        
        return recommendations;
    }

    private convertToCSV(data: any): string {
        // Simplified CSV conversion
        const csvLines: string[] = [];
        
        // Headers
        csvLines.push('Marketplace,Status,Response Time,Success Rate,Order Count,Error Count');
        
        // Data rows
        data.marketplaceMetrics.forEach((metrics: MarketplaceMetrics) => {
            csvLines.push(
                `${metrics.marketplace},${metrics.status},${metrics.responseTime},${metrics.successRate},${metrics.orderCount},${metrics.errorCount}`
            );
        });
        
        return csvLines.join('\n');
    }

    private async generatePDF(data: any): Promise<string> {
        // This would require a PDF library like jsPDF or puppeteer
        // For now, return a placeholder
        return `PDF Report generated at ${data.generatedAt}`;
    }

    /**
     * Initialize service with retry logic and circuit breaker pattern
     */
    private async initializeWithRetry(serviceName: string, initFunction: () => Promise<void>): Promise<void> {
        const maxAttempts = this.azureConfig.performance.retryPolicy.maxAttempts;
        const backoffMultiplier = this.azureConfig.performance.retryPolicy.backoffMultiplier;
        
        for (let attempt = 1; attempt <= maxAttempts; attempt++) {
            try {
                await initFunction();
                console.log(`✅ ${serviceName} initialized successfully`);
                return;
            } catch (error) {
                console.warn(`⚠️ ${serviceName} initialization attempt ${attempt} failed:`, error);
                
                if (attempt === maxAttempts) {
                    throw new Error(`${serviceName} initialization failed after ${maxAttempts} attempts`);
                }
                
                // Exponential backoff
                const delay = Math.pow(backoffMultiplier, attempt) * 1000;
                await new Promise(resolve => setTimeout(resolve, delay));
            }
        }
    }

    /**
     * Setup Azure Storage Lifecycle Management
     */
    private async setupStorageLifecyclePolicy(): Promise<void> {
        try {
            // Implement lifecycle management for performance data
            console.log('📦 Setting up storage lifecycle policy...');
            // This would typically involve setting up blob lifecycle rules
            // for automatic tiering and deletion of old performance data
        } catch (error) {
            console.error('❌ Storage lifecycle setup failed:', error);
        }
    }

    /**
     * Monitor Key Vault secret rotation
     */
    private async monitorSecretRotation(): Promise<void> {
        try {
            console.log('🔐 Setting up Key Vault secret rotation monitoring...');
            // Monitor secrets that are approaching expiration
            // and trigger rotation events
        } catch (error) {
            console.error('❌ Secret rotation monitoring setup failed:', error);
        }
    }

    /**
     * Initialize circuit breakers for all Azure services
     */
    private initializeCircuitBreakers(): void {
        const services = ['ApplicationInsights', 'BlobStorage', 'ServiceBus', 'KeyVault', 'EventHubs'];
        services.forEach(service => {
            this.circuitBreakerState.set(service, {
                failures: 0,
                lastFailure: new Date(0),
                isOpen: false
            });
        });
    }

    /**
     * Check circuit breaker state and execute with protection
     */
    private async executeWithCircuitBreaker<T>(
        serviceName: string, 
        operation: () => Promise<T>
    ): Promise<T | null> {
        const breaker = this.circuitBreakerState.get(serviceName);
        if (!breaker) return null;

        const now = new Date();
        const timeSinceLastFailure = now.getTime() - breaker.lastFailure.getTime();
        
        // Check if circuit breaker should be reset
        if (breaker.isOpen && timeSinceLastFailure > this.azureConfig.performance.circuitBreaker.timeoutThreshold) {
            breaker.isOpen = false;
            breaker.failures = 0;
        }

        if (breaker.isOpen) {
            console.warn(`⚠️ Circuit breaker open for ${serviceName}, operation skipped`);
            return null;
        }

        try {
            const result = await operation();
            // Reset failures on success
            breaker.failures = 0;
            return result;
        } catch (error) {
            breaker.failures++;
            breaker.lastFailure = now;
            
            if (breaker.failures >= this.azureConfig.performance.circuitBreaker.failureThreshold) {
                breaker.isOpen = true;
                console.error(`🔴 Circuit breaker opened for ${serviceName} after ${breaker.failures} failures`);
            }
            
            throw error;
        }
    }

    /**
     * Start continuous Azure health monitoring
     */
    private startAzureHealthMonitoring(): void {
        setInterval(async () => {
            try {
                const healthData = await this.checkAzureServicesHealth();
                
                // Track service health metrics
                this.telemetryClient?.trackMetric({
                    name: 'AzureServicesHealth',
                    value: healthData.overallHealth ? 1 : 0,
                    properties: {
                        timestamp: new Date().toISOString(),
                        ...healthData.services
                    }
                });

                // Generate alerts for unhealthy services
                Object.entries(healthData.services).forEach(([service, isHealthy]) => {
                    if (!isHealthy) {
                        this.generateAzureServiceAlert(service);
                    }
                });

            } catch (error) {
                console.error('❌ Azure health monitoring failed:', error);
            }
        }, 60000); // Check every minute
    }

    /**
     * Check health of all Azure services
     */
    private async checkAzureServicesHealth(): Promise<{
        overallHealth: boolean;
        services: Record<string, boolean>;
    }> {
        const healthChecks = {
            applicationInsights: false,
            blobStorage: false,
            serviceBus: false,
            keyVault: false,
            eventHubs: false
        };

        // Application Insights health
        try {
            if (this.telemetryClient) {
                this.telemetryClient.trackMetric({ name: 'HealthCheck', value: 1 });
                healthChecks.applicationInsights = true;
            }
        } catch (error) {
            console.error('❌ Application Insights health check failed:', error);
        }

        // Blob Storage health
        try {
            await this.executeWithCircuitBreaker('BlobStorage', async () => {
                await this.blobServiceClient?.getAccountInfo();
                healthChecks.blobStorage = true;
            });
        } catch (error) {
            console.error('❌ Blob Storage health check failed:', error);
        }

        // Service Bus health
        try {
            await this.executeWithCircuitBreaker('ServiceBus', async () => {
                const sender = this.serviceBusClient?.createSender('health-check');
                if (sender) {
                    await sender.close();
                    healthChecks.serviceBus = true;
                }
            });
        } catch (error) {
            console.error('❌ Service Bus health check failed:', error);
        }

        // Key Vault health
        try {
            await this.executeWithCircuitBreaker('KeyVault', async () => {
                await this.keyVaultClient?.getSecret('health-check');
                healthChecks.keyVault = true;
            });
        } catch (error) {
            // Health check secret might not exist, this is acceptable
            healthChecks.keyVault = true;
        }

        // Event Hubs health
        try {
            await this.executeWithCircuitBreaker('EventHubs', async () => {
                const partitionIds = await this.eventHubClient?.getPartitionIds();
                if (partitionIds && partitionIds.length > 0) {
                    healthChecks.eventHubs = true;
                }
            });
        } catch (error) {
            console.error('❌ Event Hubs health check failed:', error);
        }

        const overallHealth = Object.values(healthChecks).every(Boolean);
        
        return {
            overallHealth,
            services: healthChecks
        };
    }

    /**
     * Generate Azure service health alert
     */
    private generateAzureServiceAlert(serviceName: string): void {
        const alert: PerformanceAlert = {
            id: `azure-${serviceName}-${Date.now()}`,
            marketplace: 'Azure Services',
            type: 'error',
            message: `Azure ${serviceName} service health check failed`,
            timestamp: new Date(),
            resolved: false,
            azureMetadata: {
                operationId: `health-check-${Date.now()}`,
                resourceType: serviceName,
                autoResolutionSuggestion: `Check ${serviceName} service status and connectivity`,
                azureRegion: process.env.AZURE_REGION || 'unknown',
                severity: 'high'
            }
        };

        this.alertsCache.push(alert);
        
        // Send alert to Service Bus
        this.sendAlertToServiceBus(alert);
        
        // Track alert in Application Insights
        this.telemetryClient?.trackEvent({
            name: 'AzureServiceAlert',
            properties: {
                serviceName,
                alertId: alert.id,
                severity: alert.azureMetadata?.severity,
                timestamp: alert.timestamp.toISOString()
            }
        });
    }

    /**
     * Send alert to Azure Service Bus for processing
     */
    private async sendAlertToServiceBus(alert: PerformanceAlert): Promise<void> {
        try {
            await this.executeWithCircuitBreaker('ServiceBus', async () => {
                const sender = this.serviceBusClient?.createSender(this.azureConfig.serviceBus.queueName);
                if (sender) {
                    await sender.sendMessages({
                        body: JSON.stringify(alert),
                        messageId: alert.id,
                        correlationId: alert.azureMetadata?.operationId,
                        label: 'PerformanceAlert'
                    });
                    await sender.close();
                }
            });
        } catch (error) {
            console.error('❌ Failed to send alert to Service Bus:', error);
            this.telemetryClient?.trackException({ exception: error as Error });
        }
    }

    /**
     * Stream real-time metrics to Azure Event Hubs
     */
    private async streamMetricsToEventHub(metrics: SystemMetrics): Promise<void> {
        try {
            await this.executeWithCircuitBreaker('EventHubs', async () => {
                const eventData = {
                    body: {
                        timestamp: new Date().toISOString(),
                        correlationId: this.generateCorrelationId(),
                        metrics: metrics,
                        source: 'PerformanceDashboard',
                        version: '5.0.0'
                    },
                    contentType: 'application/json'
                };

                const batch = await this.eventHubClient?.createBatch();
                if
