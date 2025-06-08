/**
 * GEMINI Super Admin SignalR Integration
 * Enhanced real-time dashboard with Azure SignalR
 */

import MesChainSignalRClient from './meschain-signalr-client';

interface DashboardMetrics {
  activeUsers: number;
  dailyTransactions: number;
  revenue: number;
  conversionRate: number;
  systemUptime: number;
  responseTime: number;
  errorRate: number;
  throughput: number;
}

interface SystemStatus {
  activeSessions: number;
  apiHealth: string;
  databaseStatus: string;
  cacheStatus: string;
  uptime: number;
  diskUsage: number;
  memoryUsage: number;
}

interface MarketplaceStatus {
  [marketplace: string]: {
    status: string;
    lastSync: string;
    syncProgress: number;
    errorCount: number;
    productCount: number;
  };
}

class GeminiSuperAdminSignalR {
  private signalRClient: MesChainSignalRClient;
  private dashboardUpdateInterval: NodeJS.Timeout | null = null;
  private performanceCharts: Map<string, any> = new Map();
  private isInitialized: boolean = false;

  constructor(userId: string) {
    console.log('🚀 Initializing GEMINI Super Admin SignalR Client');
    
    this.signalRClient = new MesChainSignalRClient({
      userId,
      userRole: 'super_admin',
      authToken: this.getAuthToken(),
      serverUrl: '/api'
    });

    this.setupEventListeners();
    this.initializeRealTimeUpdates();
  }

  /**
   * Setup event listeners for SignalR events
   */
  private setupEventListeners(): void {
    // Listen for SignalR connection events
    this.signalRClient.addEventListener('connected', () => {
      this.onSignalRConnected();
    });

    this.signalRClient.addEventListener('disconnected', () => {
      this.onSignalRDisconnected();
    });

    this.signalRClient.addEventListener('reconnecting', () => {
      this.onSignalRReconnecting();
    });

    this.signalRClient.addEventListener('reconnected', () => {
      this.onSignalRReconnected();
    });

    // Listen for admin dashboard updates
    this.signalRClient.addEventListener('adminDashboardUpdate', (data) => {
      this.handleAdminDashboardUpdate(data);
    });

    // Listen for system alerts
    this.signalRClient.addEventListener('systemAlert', (alert) => {
      this.handleSystemAlert(alert);
    });

    this.signalRClient.addEventListener('criticalAlert', (alert) => {
      this.handleCriticalAlert(alert);
    });

    // Listen for system health updates
    this.signalRClient.addEventListener('systemHealthUpdate', (healthData) => {
      this.handleSystemHealthUpdate(healthData);
    });

    // Listen for marketplace sync updates
    this.signalRClient.addEventListener('marketplaceSyncUpdate', (data) => {
      this.handleMarketplaceSyncUpdate(data);
    });

    console.log('✅ GEMINI SignalR event listeners configured');
  }

  /**
   * Initialize real-time updates
   */
  private async initializeRealTimeUpdates(): Promise<void> {
    try {
      // Wait for connection to be established
      await this.waitForConnection();
      
      // Subscribe to admin updates
      await this.signalRClient.subscribeToAdminUpdates();
      
      // Initialize performance charts
      this.initializeCharts();
      
      // Start periodic health checks
      this.startHealthChecks();
      
      // Mark as initialized
      this.isInitialized = true;
      
      console.log('✅ GEMINI real-time updates initialized');

    } catch (error) {
      console.error('❌ Failed to initialize real-time updates:', error);
      this.showNotification('Failed to initialize real-time updates', 'error');
    }
  }

  /**
   * Wait for SignalR connection to be established
   */
  private async waitForConnection(): Promise<void> {
    return new Promise((resolve, reject) => {
      const maxWaitTime = 30000; // 30 seconds
      const checkInterval = 100; // 100ms
      let elapsed = 0;

      const checkConnection = () => {
        if (this.signalRClient.isConnected()) {
          resolve();
        } else if (elapsed >= maxWaitTime) {
          reject(new Error('Connection timeout'));
        } else {
          elapsed += checkInterval;
          setTimeout(checkConnection, checkInterval);
        }
      };

      checkConnection();
    });
  }

  /**
   * Initialize performance charts
   */
  private initializeCharts(): void {
    try {
      // Initialize response time chart
      this.initializeChart('response-time-chart', 'Response Time (ms)', '#3498db');
      
      // Initialize throughput chart
      this.initializeChart('throughput-chart', 'Throughput (req/s)', '#2ecc71');
      
      // Initialize error rate chart
      this.initializeChart('error-rate-chart', 'Error Rate (%)', '#e74c3c');
      
      // Initialize user activity chart
      this.initializeChart('user-activity-chart', 'Active Users', '#9b59b6');

      console.log('📊 Performance charts initialized');

    } catch (error) {
      console.error('❌ Failed to initialize charts:', error);
    }
  }

  /**
   * Initialize individual chart
   */
  private initializeChart(chartId: string, label: string, color: string): void {
    const chartElement = document.getElementById(chartId);
    if (!chartElement) {
      console.warn(`Chart element not found: ${chartId}`);
      return;
    }

    // Use Chart.js if available
    if (typeof Chart !== 'undefined') {
      const ctx = (chartElement as HTMLCanvasElement).getContext('2d');
      if (ctx) {
        const chart = new Chart(ctx, {
          type: 'line',
          data: {
            labels: [],
            datasets: [{
              label,
              data: [],
              borderColor: color,
              backgroundColor: color + '20',
              borderWidth: 2,
              fill: true,
              tension: 0.4
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              x: {
                display: false
              },
              y: {
                beginAtZero: true
              }
            },
            plugins: {
              legend: {
                display: false
              }
            },
            animation: {
              duration: 300
            }
          }
        });

        this.performanceCharts.set(chartId, chart);
      }
    }
  }

  /**
   * Handle SignalR connection established
   */
  private onSignalRConnected(): void {
    console.log('🟢 GEMINI Dashboard - SignalR Connected');
    
    // Update connection status indicator
    this.updateConnectionStatus('connected', 'CONNECTED');
    
    // Show success notification
    this.showNotification('Real-time connection established', 'success');
    
    // Request initial dashboard data
    this.requestDashboardUpdate();
  }

  /**
   * Handle SignalR disconnection
   */
  private onSignalRDisconnected(): void {
    console.log('🔴 GEMINI Dashboard - SignalR Disconnected');
    
    // Update connection status indicator
    this.updateConnectionStatus('disconnected', 'DISCONNECTED');
    
    // Show warning notification
    this.showNotification('Connection lost - data may be outdated', 'warning');
  }

  /**
   * Handle SignalR reconnecting
   */
  private onSignalRReconnecting(): void {
    console.log('🟡 GEMINI Dashboard - SignalR Reconnecting');
    
    // Update connection status indicator
    this.updateConnectionStatus('reconnecting', 'RECONNECTING...');
    
    // Show info notification
    this.showNotification('Attempting to reconnect...', 'info');
  }

  /**
   * Handle SignalR reconnected
   */
  private onSignalRReconnected(): void {
    console.log('🟢 GEMINI Dashboard - SignalR Reconnected');
    
    // Update connection status indicator
    this.updateConnectionStatus('connected', 'CONNECTED');
    
    // Show success notification
    this.showNotification('Connection restored successfully', 'success');
    
    // Re-subscribe to admin updates
    this.signalRClient.subscribeToAdminUpdates().catch(error => {
      console.error('Failed to re-subscribe to admin updates:', error);
    });
  }

  /**
   * Handle admin dashboard updates
   */
  private handleAdminDashboardUpdate(data: any): void {
    console.log('📊 GEMINI - Admin dashboard update received:', data);
    
    try {
      // Update system status
      if (data.systemStatus) {
        this.updateSystemStatus(data.systemStatus);
      }
      
      // Update performance metrics
      if (data.performanceMetrics) {
        this.updatePerformanceMetrics(data.performanceMetrics);
      }
      
      // Update business metrics
      if (data.businessMetrics) {
        this.updateBusinessMetrics(data.businessMetrics);
      }
      
      // Update security metrics
      if (data.securityMetrics) {
        this.updateSecurityMetrics(data.securityMetrics);
      }
      
      // Update marketplace status
      if (data.marketplaceStatus) {
        this.updateMarketplaceStatus(data.marketplaceStatus);
      }
      
      // Update last refresh timestamp
      this.updateLastRefreshTime(data.timestamp);

    } catch (error) {
      console.error('❌ Error updating dashboard:', error);
      this.showNotification('Error updating dashboard data', 'error');
    }
  }

  /**
   * Update system status indicators
   */
  private updateSystemStatus(status: SystemStatus): void {
    // Update active sessions
    this.updateElement('active-sessions', status.activeSessions?.toLocaleString());
    
    // Update API health
    this.updateStatusIndicator('api-health', status.apiHealth);
    
    // Update database status
    this.updateStatusIndicator('database-status', status.databaseStatus);
    
    // Update cache status
    this.updateStatusIndicator('cache-status', status.cacheStatus);
    
    // Update system uptime
    this.updateElement('system-uptime', this.formatUptime(status.uptime));
    
    // Update disk usage
    this.updateProgressBar('disk-usage-progress', status.diskUsage);
    this.updateElement('disk-usage-text', `${status.diskUsage?.toFixed(1)}%`);
    
    // Update memory usage
    this.updateProgressBar('memory-usage-progress', status.memoryUsage);
    this.updateElement('memory-usage-text', `${status.memoryUsage?.toFixed(1)}%`);
  }

  /**
   * Update performance metrics and charts
   */
  private updatePerformanceMetrics(metrics: DashboardMetrics): void {
    // Update response time chart and display
    this.updateChart('response-time-chart', metrics.responseTime);
    this.updateElement('response-time', `${metrics.responseTime?.toFixed(0)}ms`);
    
    // Update throughput chart and display
    this.updateChart('throughput-chart', metrics.throughput);
    this.updateElement('throughput', metrics.throughput?.toLocaleString());
    
    // Update error rate chart and display
    this.updateChart('error-rate-chart', metrics.errorRate);
    this.updateElement('error-rate', `${metrics.errorRate?.toFixed(2)}%`);
    
    // Update concurrent users
    this.updateElement('concurrent-users', metrics.activeUsers?.toLocaleString());
    this.updateChart('user-activity-chart', metrics.activeUsers);
  }

  /**
   * Update business metrics
   */
  private updateBusinessMetrics(metrics: DashboardMetrics): void {
    // Update active users
    this.updateElement('total-active-users', metrics.activeUsers?.toLocaleString());
    
    // Update daily transactions
    this.updateElement('daily-transactions', metrics.dailyTransactions?.toLocaleString());
    
    // Update revenue
    this.updateElement('daily-revenue', this.formatCurrency(metrics.revenue));
    
    // Update conversion rate
    this.updateElement('conversion-rate', `${metrics.conversionRate?.toFixed(1)}%`);
  }

  /**
   * Update security metrics
   */
  private updateSecurityMetrics(securityData: any): void {
    // Update security score
    this.updateElement('security-score', `${securityData.securityScore?.toFixed(1)}/100`);
    
    // Update failed logins
    this.updateElement('failed-logins', securityData.failedLogins?.toString());
    
    // Update active threats
    this.updateElement('active-threats', securityData.activeThreats?.toString());
    
    // Update vulnerability count
    this.updateElement('vulnerability-count', securityData.vulnerabilityCount?.toString());
    
    // Update certificate status
    this.updateStatusIndicator('certificate-status', securityData.certificateStatus);
  }

  /**
   * Update marketplace synchronization status
   */
  private updateMarketplaceStatus(marketplaceStatus: MarketplaceStatus): void {
    const marketplaces = ['amazon', 'ebay', 'trendyol', 'hepsiburada', 'etsy'];
    
    marketplaces.forEach(marketplace => {
      const status = marketplaceStatus[marketplace];
      if (status) {
        // Update sync status
        this.updateStatusIndicator(`${marketplace}-sync-status`, status.status);
        
        // Update last sync time
        this.updateElement(`${marketplace}-last-sync`, this.formatTimestamp(status.lastSync));
        
        // Update sync progress
        this.updateProgressBar(`${marketplace}-sync-progress`, status.syncProgress);
        
        // Update product count
        this.updateElement(`${marketplace}-product-count`, status.productCount?.toLocaleString());
        
        // Update error count
        this.updateElement(`${marketplace}-error-count`, status.errorCount?.toString());
      }
    });
  }

  /**
   * Handle system alerts
   */
  private handleSystemAlert(alert: any): void {
    console.log('🚨 GEMINI - System alert received:', alert);
    
    // Show alert notification
    this.showNotification(alert.message, alert.level);
    
    // Update alert indicator in dashboard
    this.addAlertToList(alert);
  }

  /**
   * Handle critical alerts
   */
  private handleCriticalAlert(alert: any): void {
    console.log('🔴 GEMINI - Critical alert received:', alert);
    
    // Show persistent critical notification
    this.showNotification(alert.message, 'critical', true);
    
    // Highlight critical status in dashboard
    this.highlightCriticalStatus(alert);
    
    // Play alert sound if available
    this.playAlertSound();
  }

  /**
   * Handle system health updates
   */
  private handleSystemHealthUpdate(healthData: any): void {
    console.log('💚 GEMINI - System health update:', healthData);
    
    // Update overall system health indicator
    this.updateSystemHealthIndicator(healthData);
  }

  /**
   * Handle marketplace sync updates
   */
  private handleMarketplaceSyncUpdate(data: any): void {
    console.log('🔄 GEMINI - Marketplace sync update:', data);
    
    // Update specific marketplace sync progress
    this.updateProgressBar(`${data.marketplace}-sync-progress`, data.progress);
    
    // Show sync progress notification
    if (data.progress === 100) {
      this.showNotification(`${data.marketplace.toUpperCase()} sync completed`, 'success');
    }
  }

  /**
   * Start periodic health checks
   */
  private startHealthChecks(): void {
    this.dashboardUpdateInterval = setInterval(async () => {
      if (this.signalRClient.isConnected()) {
        try {
          await this.requestDashboardUpdate();
        } catch (error) {
          console.error('Failed to request dashboard update:', error);
        }
      }
    }, 30000); // Every 30 seconds
  }

  /**
   * Request dashboard update from server
   */
  private async requestDashboardUpdate(): Promise<void> {
    try {
      await this.signalRClient.sendAdminCommand('requestDashboardUpdate', {
        timestamp: new Date().toISOString(),
        requestId: `update_${Date.now()}`
      });
    } catch (error) {
      console.error('Failed to request dashboard update:', error);
    }
  }

  // Admin Action Methods

  /**
   * Trigger marketplace sync
   */
  public async triggerMarketplaceSync(marketplace: string): Promise<void> {
    try {
      await this.signalRClient.sendAdminCommand('triggerSync', { marketplace });
      this.showNotification(`${marketplace.toUpperCase()} sync initiated`, 'info');
    } catch (error) {
      console.error('Failed to trigger sync:', error);
      this.showNotification('Failed to trigger sync', 'error');
    }
  }

  /**
   * Broadcast system alert
   */
  public async broadcastSystemAlert(message: string, level: string): Promise<void> {
    try {
      await this.signalRClient.sendAdminCommand('broadcastAlert', { message, level });
      this.showNotification('System alert broadcasted', 'success');
    } catch (error) {
      console.error('Failed to broadcast alert:', error);
      this.showNotification('Failed to broadcast alert', 'error');
    }
  }

  /**
   * Schedule system maintenance
   */
  public async scheduleSystemMaintenance(scheduledTime: string, duration: string): Promise<void> {
    try {
      await this.signalRClient.sendAdminCommand('systemMaintenance', {
        action: 'schedule',
        scheduledTime,
        duration
      });
      this.showNotification('Maintenance scheduled successfully', 'success');
    } catch (error) {
      console.error('Failed to schedule maintenance:', error);
      this.showNotification('Failed to schedule maintenance', 'error');
    }
  }

  // Utility Methods

  private updateElement(elementId: string, value: string): void {
    const element = document.getElementById(elementId);
    if (element) {
      element.textContent = value;
    }
  }

  private updateStatusIndicator(elementId: string, status: string): void {
    const element = document.getElementById(elementId);
    if (element) {
      element.className = `status-indicator ${status.toLowerCase()}`;
      element.textContent = status.toUpperCase();
    }
  }

  private updateProgressBar(elementId: string, value: number): void {
    const element = document.getElementById(elementId);
    if (element) {
      (element as HTMLElement).style.width = `${Math.min(100, Math.max(0, value))}%`;
    }
  }

  private updateConnectionStatus(status: string, text: string): void {
    const statusIndicator = document.getElementById('connection-status');
    if (statusIndicator) {
      statusIndicator.className = `connection-status ${status}`;
      statusIndicator.textContent = text;
    }
  }

  private updateChart(chartId: string, value: number): void {
    const chart = this.performanceCharts.get(chartId);
    if (chart && typeof Chart !== 'undefined') {
      const data = chart.data.datasets[0].data;
      const labels = chart.data.labels;
      
      // Add new data point
      data.push(value);
      labels.push(new Date().toLocaleTimeString());
      
      // Keep only last 20 points
      if (data.length > 20) {
        data.shift();
        labels.shift();
      }
      
      chart.update('none'); // Update without animation for better performance
    }
  }

  private updateLastRefreshTime(timestamp: string): void {
    const element = document.getElementById('last-refresh-time');
    if (element) {
      element.textContent = `Last updated: ${new Date(timestamp).toLocaleTimeString()}`;
    }
  }

  private addAlertToList(alert: any): void {
    const alertsList = document.getElementById('alerts-list');
    if (alertsList) {
      const alertElement = document.createElement('div');
      alertElement.className = `alert-item ${alert.level}`;
      alertElement.innerHTML = `
        <span class="alert-time">${new Date(alert.timestamp).toLocaleTimeString()}</span>
        <span class="alert-message">${alert.message}</span>
      `;
      alertsList.insertBefore(alertElement, alertsList.firstChild);
      
      // Keep only last 10 alerts
      while (alertsList.children.length > 10) {
        alertsList.removeChild(alertsList.lastChild!);
      }
    }
  }

  private highlightCriticalStatus(alert: any): void {
    // Add visual indicators for critical status
    const criticalIndicator = document.getElementById('critical-status-indicator');
    if (criticalIndicator) {
      criticalIndicator.className = 'critical-status active';
      criticalIndicator.textContent = 'CRITICAL ALERT ACTIVE';
    }
  }

  private updateSystemHealthIndicator(healthData: any): void {
    const healthIndicator = document.getElementById('system-health-indicator');
    if (healthIndicator) {
      const overallStatus = this.calculateOverallHealth(healthData);
      healthIndicator.className = `health-indicator ${overallStatus}`;
      healthIndicator.textContent = overallStatus.toUpperCase();
    }
  }

  private calculateOverallHealth(healthData: any): string {
    // Simple health calculation logic
    if (healthData.apiHealth === 'healthy' && 
        healthData.databaseStatus === 'healthy' && 
        healthData.cacheStatus === 'healthy') {
      return 'healthy';
    } else if (healthData.apiHealth === 'error' || 
               healthData.databaseStatus === 'error') {
      return 'error';
    } else {
      return 'warning';
    }
  }

  private playAlertSound(): void {
    // Play alert sound if available
    const audio = document.getElementById('alert-sound') as HTMLAudioElement;
    if (audio) {
      audio.play().catch(error => {
        console.log('Could not play alert sound:', error);
      });
    }
  }

  private formatUptime(seconds: number): string {
    const days = Math.floor(seconds / 86400);
    const hours = Math.floor((seconds % 86400) / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    return `${days}d ${hours}h ${minutes}m`;
  }

  private formatCurrency(amount: number): string {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD'
    }).format(amount);
  }

  private formatTimestamp(timestamp: string): string {
    return new Date(timestamp).toLocaleString();
  }

  private showNotification(message: string, type: string, persistent: boolean = false): void {
    // Use existing GEMINI notification system
    if (typeof window.showGeminiNotification === 'function') {
      window.showGeminiNotification(message, type);
    } else if (typeof window.showNotification === 'function') {
      window.showNotification({ message, type, persistent });
    } else {
      console.log(`${type.toUpperCase()}: ${message}`);
      if (type === 'critical' || persistent) {
        alert(message);
      }
    }
  }

  private getAuthToken(): string {
    return localStorage.getItem('auth_token') || 
           document.querySelector('meta[name="auth-token"]')?.getAttribute('content') || 
           '';
  }

  // Cleanup
  public destroy(): void {
    if (this.dashboardUpdateInterval) {
      clearInterval(this.dashboardUpdateInterval);
      this.dashboardUpdateInterval = null;
    }
    
    // Destroy charts
    this.performanceCharts.forEach(chart => {
      if (chart && typeof chart.destroy === 'function') {
        chart.destroy();
      }
    });
    this.performanceCharts.clear();
    
    // Disconnect SignalR
    this.signalRClient.disconnect();
    
    console.log('🗑️ GEMINI SignalR client destroyed');
  }
}

// Export for module usage
export default GeminiSuperAdminSignalR;

// Auto-initialize if user is admin and DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
  const userRole = document.querySelector('meta[name="user-role"]')?.getAttribute('content');
  
  if (userId && (userRole === 'super_admin' || userRole === 'admin')) {
    console.log('🚀 Auto-initializing GEMINI SignalR for admin user');
    window.geminiSignalR = new GeminiSuperAdminSignalR(userId);
  }
});

// Global window interface
declare global {
  interface Window {
    geminiSignalR: GeminiSuperAdminSignalR;
    showGeminiNotification?: (message: string, type: string) => void;
    showNotification?: (notification: any) => void;
    Chart?: any;
  }
}
