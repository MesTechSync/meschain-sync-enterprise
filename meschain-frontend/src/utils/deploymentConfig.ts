interface DeploymentConfig {
  environment: 'development' | 'staging' | 'production';
  apiBaseUrl: string;
  websocketUrl: string;
  enableAnalytics: boolean;
  enableDebugMode: boolean;
  cdnUrl?: string;
  sslEnabled: boolean;
  cachingEnabled: boolean;
  compressionEnabled: boolean;
  errorReporting: {
    enabled: boolean;
    endpoint?: string;
    apiKey?: string;
  };
  performance: {
    monitoring: boolean;
    bundleAnalysis: boolean;
    memoryOptimization: boolean;
  };
}

class DeploymentManager {
  private config: DeploymentConfig;

  constructor() {
    this.config = this.loadConfig();
  }

  private loadConfig(): DeploymentConfig {
    const env = process.env.NODE_ENV || 'development';
    
    const baseConfig: DeploymentConfig = {
      environment: env as 'development' | 'staging' | 'production',
      apiBaseUrl: process.env.REACT_APP_API_BASE_URL || 'http://localhost:8080',
      websocketUrl: process.env.REACT_APP_WEBSOCKET_URL || 'ws://localhost:8080/ws',
      enableAnalytics: process.env.REACT_APP_ANALYTICS_ENABLED === 'true',
      enableDebugMode: process.env.REACT_APP_DEBUG_MODE === 'true',
      cdnUrl: process.env.REACT_APP_CDN_URL,
      sslEnabled: env === 'production',
      cachingEnabled: process.env.REACT_APP_CACHE_ENABLED !== 'false',
      compressionEnabled: env === 'production',
      errorReporting: {
        enabled: env === 'production',
        endpoint: process.env.REACT_APP_ERROR_REPORTING_ENDPOINT,
        apiKey: process.env.REACT_APP_ERROR_REPORTING_API_KEY
      },
      performance: {
        monitoring: env === 'production',
        bundleAnalysis: process.env.REACT_APP_BUNDLE_ANALYSIS === 'true',
        memoryOptimization: env === 'production'
      }
    };

    // Environment-specific overrides
    switch (env) {
      case 'production':
        return {
          ...baseConfig,
          apiBaseUrl: process.env.REACT_APP_PROD_API_URL || baseConfig.apiBaseUrl,
          websocketUrl: process.env.REACT_APP_PROD_WS_URL || baseConfig.websocketUrl.replace('ws:', 'wss:'),
          enableDebugMode: false,
          sslEnabled: true,
          compressionEnabled: true
        };
      
      case 'staging':
        return {
          ...baseConfig,
          apiBaseUrl: process.env.REACT_APP_STAGING_API_URL || baseConfig.apiBaseUrl,
          websocketUrl: process.env.REACT_APP_STAGING_WS_URL || baseConfig.websocketUrl,
          enableAnalytics: false,
          enableDebugMode: true
        };
      
      default:
        return baseConfig;
    }
  }

  getConfig(): DeploymentConfig {
    return { ...this.config };
  }

  getApiUrl(endpoint: string = ''): string {
    const baseUrl = this.config.apiBaseUrl.replace(/\/$/, '');
    const cleanEndpoint = endpoint.replace(/^\//, '');
    return cleanEndpoint ? `${baseUrl}/${cleanEndpoint}` : baseUrl;
  }

  getWebSocketUrl(): string {
    return this.config.websocketUrl;
  }

  getCDNUrl(asset: string): string {
    if (!this.config.cdnUrl) return asset;
    const cdnBase = this.config.cdnUrl.replace(/\/$/, '');
    const cleanAsset = asset.replace(/^\//, '');
    return `${cdnBase}/${cleanAsset}`;
  }

  isProduction(): boolean {
    return this.config.environment === 'production';
  }

  isStaging(): boolean {
    return this.config.environment === 'staging';
  }

  isDevelopment(): boolean {
    return this.config.environment === 'development';
  }

  shouldEnableSSL(): boolean {
    return this.config.sslEnabled;
  }

  shouldEnableAnalytics(): boolean {
    return this.config.enableAnalytics;
  }

  shouldEnableDebugMode(): boolean {
    return this.config.enableDebugMode;
  }

  shouldEnableCaching(): boolean {
    return this.config.cachingEnabled;
  }

  shouldEnableCompression(): boolean {
    return this.config.compressionEnabled;
  }

  getErrorReportingConfig() {
    return this.config.errorReporting;
  }

  getPerformanceConfig() {
    return this.config.performance;
  }

  // Health check for deployment readiness
  async validateDeployment(): Promise<{
    ready: boolean;
    checks: Array<{ name: string; status: 'pass' | 'fail' | 'warning'; message: string }>;
  }> {
    const checks: Array<{ name: string; status: 'pass' | 'fail' | 'warning'; message: string }> = [];

    // API connectivity check
    try {
      const response = await fetch(this.getApiUrl('/health'), { 
        method: 'GET',
        timeout: 5000 
      } as any);
      
      checks.push({
        name: 'API Connectivity',
        status: response.ok ? 'pass' : 'fail',
        message: response.ok ? 'API is reachable' : `API returned ${response.status}`
      });
    } catch (error) {
      checks.push({
        name: 'API Connectivity',
        status: 'fail',
        message: 'API is not reachable'
      });
    }

    // SSL check for production
    if (this.isProduction()) {
      const hasSSL = window.location.protocol === 'https:';
      checks.push({
        name: 'SSL Certificate',
        status: hasSSL ? 'pass' : 'fail',
        message: hasSSL ? 'HTTPS is enabled' : 'HTTPS is required for production'
      });
    }

    // Environment variables check
    const requiredEnvVars = [
      'REACT_APP_API_BASE_URL',
      'REACT_APP_WEBSOCKET_URL'
    ];

    if (this.isProduction()) {
      requiredEnvVars.push(
        'REACT_APP_PROD_API_URL',
        'REACT_APP_PROD_WS_URL'
      );
    }

    const missingVars = requiredEnvVars.filter(envVar => !process.env[envVar]);
    checks.push({
      name: 'Environment Variables',
      status: missingVars.length === 0 ? 'pass' : 'warning',
      message: missingVars.length === 0 
        ? 'All required environment variables are set'
        : `Missing: ${missingVars.join(', ')}`
    });

    // Performance check
    const performanceEntries = performance.getEntriesByType('navigation') as PerformanceNavigationTiming[];
    const loadTime = performanceEntries[0]?.loadEventEnd - performanceEntries[0]?.loadEventStart;
    
    checks.push({
      name: 'Load Performance',
      status: loadTime < 3000 ? 'pass' : loadTime < 5000 ? 'warning' : 'fail',
      message: `Page load time: ${Math.round(loadTime)}ms`
    });

    // Memory usage check
    const memoryInfo = (performance as any).memory;
    if (memoryInfo) {
      const memoryUsage = (memoryInfo.usedJSHeapSize / memoryInfo.totalJSHeapSize) * 100;
      checks.push({
        name: 'Memory Usage',
        status: memoryUsage < 70 ? 'pass' : memoryUsage < 85 ? 'warning' : 'fail',
        message: `Memory usage: ${Math.round(memoryUsage)}%`
      });
    }

    const failedChecks = checks.filter(check => check.status === 'fail').length;
    
    return {
      ready: failedChecks === 0,
      checks
    };
  }

  // Generate deployment report
  generateDeploymentReport(): string {
    const config = this.getConfig();
    
    return `
# MesChain-Sync Deployment Report
Generated: ${new Date().toISOString()}

## Environment Configuration
- Environment: ${config.environment}
- API Base URL: ${config.apiBaseUrl}
- WebSocket URL: ${config.websocketUrl}
- SSL Enabled: ${config.sslEnabled}
- Debug Mode: ${config.enableDebugMode}
- Analytics: ${config.enableAnalytics}
- Caching: ${config.cachingEnabled}
- Compression: ${config.compressionEnabled}

## Feature Flags
- Dropshipping: ${process.env.REACT_APP_FEATURE_DROPSHIPPING}
- Multi-Marketplace: ${process.env.REACT_APP_FEATURE_MULTI_MARKETPLACE}
- Advanced Analytics: ${process.env.REACT_APP_FEATURE_ADVANCED_ANALYTICS}
- Real-time Sync: ${process.env.REACT_APP_FEATURE_REAL_TIME_SYNC}

## Error Reporting
- Enabled: ${config.errorReporting.enabled}
- Endpoint: ${config.errorReporting.endpoint || 'Not configured'}

## Performance Monitoring
- Monitoring: ${config.performance.monitoring}
- Bundle Analysis: ${config.performance.bundleAnalysis}
- Memory Optimization: ${config.performance.memoryOptimization}

## Deployment Checklist
- [ ] Environment variables configured
- [ ] SSL certificate installed (production)
- [ ] CDN configured (if applicable)
- [ ] Error reporting setup
- [ ] Performance monitoring enabled
- [ ] Database connections tested
- [ ] API endpoints validated
- [ ] WebSocket connections tested
- [ ] Security headers configured
- [ ] Cache policies set
- [ ] Backup procedures verified
    `;
  }
}

// Singleton instance
export const deploymentManager = new DeploymentManager();
export default deploymentManager;
export type { DeploymentConfig }; 