/**
 * MesChain SignalR Client - Enterprise Edition
 * Enhanced WebSocket client using Azure SignalR Service
 * Replaces legacy PHP WebSocket implementation
 */

import * as signalR from "@microsoft/signalr";

interface ConnectionConfig {
  userId: string;
  userRole: string;
  authToken?: string;
  hubName?: string;
  serverUrl?: string;
}

interface SignalRMessage {
  target: string;
  arguments: any[];
  connectionId?: string;
  groupName?: string;
  userId?: string;
}

class MesChainSignalRClient {
  private connection: signalR.HubConnection | null = null;
  private config: ConnectionConfig;
  private connectionState: 'disconnected' | 'connecting' | 'connected' | 'reconnecting' = 'disconnected';
  private reconnectAttempts: number = 0;
  private maxReconnectAttempts: number = 10;
  private eventListeners: Map<string, Function[]> = new Map();
  private connectionPromise: Promise<void> | null = null;
  private connectionTimeout: NodeJS.Timeout | null = null;

  constructor(config: ConnectionConfig) {
    this.config = {
      serverUrl: 'https://signalr-meschain-prod.service.signalr.net',
      hubName: 'MesChainSyncHub',
      ...config
    };
    
    this.initializeConnection();
  }

  /**
   * Initialize SignalR connection with retry logic
   */
  private async initializeConnection(): Promise<void> {
    try {
      // Get connection info from negotiation endpoint
      const connectionInfo = await this.negotiateConnection();
      
      if (!connectionInfo) {
        throw new Error('Failed to negotiate SignalR connection');
      }

      // Create SignalR connection
      this.connection = new signalR.HubConnectionBuilder()
        .withUrl(connectionInfo.url, {
          accessTokenFactory: () => connectionInfo.accessToken || this.config.authToken || ''
        })
        .withAutomaticReconnect({
          nextRetryDelayInMilliseconds: (retryContext) => {
            // Exponential backoff with jitter
            const delay = Math.min(1000 * Math.pow(2, retryContext.previousRetryCount), 30000);
            return delay + Math.random() * 1000;
          }
        })
        .configureLogging(signalR.LogLevel.Information)
        .build();

      this.setupEventHandlers();
      await this.startConnection();

    } catch (error) {
      console.error('❌ SignalR connection initialization failed:', error);
      this.scheduleReconnect();
    }
  }

  /**
   * Negotiate connection with server
   */
  private async negotiateConnection(): Promise<any> {
    try {
      const response = await fetch(`${this.config.serverUrl}/negotiate`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'x-user-id': this.config.userId,
          'x-user-role': this.config.userRole,
          'Authorization': `Bearer ${this.getAuthToken()}`
        }
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(`Negotiation failed: ${errorData.message || response.statusText}`);
      }

      const connectionInfo = await response.json();
      console.log('✅ SignalR connection negotiated successfully');
      return connectionInfo;

    } catch (error) {
      console.error('❌ SignalR negotiation failed:', error);
      throw error;
    }
  }

  /**
   * Setup SignalR event handlers
   */
  private setupEventHandlers(): void {
    if (!this.connection) return;

    // Connection state handlers
    this.connection.onclose(this.handleDisconnection.bind(this));
    this.connection.onreconnecting(this.handleReconnecting.bind(this));
    this.connection.onreconnected(this.handleReconnected.bind(this));

    // Role-based message handlers
    this.setupRoleBasedHandlers();
    
    // Common handlers for all roles
    this.setupCommonHandlers();
  }

  /**
   * Setup role-specific message handlers
   */
  private setupRoleBasedHandlers(): void {
    if (!this.connection) return;

    switch (this.config.userRole.toLowerCase()) {
      case 'super_admin':
      case 'admin':
        this.setupAdminHandlers();
        break;
      case 'marketplace_manager':
      case 'seller':
        this.setupMarketplaceHandlers();
        break;
      case 'analyst':
      case 'viewer':
        this.setupDashboardHandlers();
        break;
      default:
        this.setupDashboardHandlers();
    }
  }

  /**
   * Setup admin-specific handlers
   */
  private setupAdminHandlers(): void {
    if (!this.connection) return;

    this.connection.on("updateAdminDashboard", (data) => {
      console.log('📊 Admin dashboard update received:', data);
      this.dispatchEvent('adminDashboardUpdate', data);
      this.updateAdminDashboard(data);
    });

    this.connection.on("systemAlert", (alert) => {
      console.log('🚨 System alert received:', alert);
      this.dispatchEvent('systemAlert', alert);
      this.handleSystemAlert(alert);
    });

    this.connection.on("criticalAlert", (alert) => {
      console.log('🔴 Critical alert received:', alert);
      this.dispatchEvent('criticalAlert', alert);
      this.handleCriticalAlert(alert);
    });

    this.connection.on("systemHealthUpdate", (healthData) => {
      console.log('💚 System health update:', healthData);
      this.dispatchEvent('systemHealthUpdate', healthData);
      this.updateSystemHealth(healthData);
    });

    this.connection.on("adminGroupJoined", (data) => {
      console.log('✅ Admin group joined:', data);
      this.dispatchEvent('adminGroupJoined', data);
    });
  }

  /**
   * Setup marketplace-specific handlers
   */
  private setupMarketplaceHandlers(): void {
    if (!this.connection) return;

    this.connection.on("marketplaceSyncUpdate", (data) => {
      console.log('🔄 Marketplace sync update:', data);
      this.dispatchEvent('marketplaceSyncUpdate', data);
      this.updateSyncProgress(data.marketplace, data.progress);
    });

    this.connection.on("marketplaceSyncStarted", (data) => {
      console.log('🟡 Marketplace sync started:', data);
      this.dispatchEvent('marketplaceSyncStarted', data);
      this.showSyncStartedNotification(data);
    });

    this.connection.on("marketplaceSyncCompleted", (data) => {
      console.log('🟢 Marketplace sync completed:', data);
      this.dispatchEvent('marketplaceSyncCompleted', data);
      this.showSyncCompletedNotification(data);
    });

    this.connection.on("inventoryUpdate", (update) => {
      console.log('📦 Inventory update:', update);
      this.dispatchEvent('inventoryUpdate', update);
      this.updateInventoryStatus(update);
    });

    this.connection.on("orderNotification", (order) => {
      console.log('🛒 Order notification:', order);
      this.dispatchEvent('orderNotification', order);
      this.showOrderNotification(order);
    });

    this.connection.on("marketplaceGroupJoined", (data) => {
      console.log('✅ Marketplace group joined:', data);
      this.dispatchEvent('marketplaceGroupJoined', data);
    });
  }

  /**
   * Setup dashboard-specific handlers
   */
  private setupDashboardHandlers(): void {
    if (!this.connection) return;

    this.connection.on("dashboardUpdate", (data) => {
      console.log('📈 Dashboard update received:', data);
      this.dispatchEvent('dashboardUpdate', data);
      this.updateDashboardMetrics(data);
    });

    this.connection.on("metricsUpdate", (metrics) => {
      console.log('📊 Metrics update:', metrics);
      this.dispatchEvent('metricsUpdate', metrics);
      this.updateMetricsDisplay(metrics);
    });

    this.connection.on("performanceData", (performance) => {
      console.log('⚡ Performance data:', performance);
      this.dispatchEvent('performanceData', performance);
      this.updatePerformanceCharts(performance);
    });

    this.connection.on("businessMetrics", (business) => {
      console.log('💼 Business metrics:', business);
      this.dispatchEvent('businessMetrics', business);
      this.updateBusinessDisplay(business);
    });

    this.connection.on("dashboardSubscribed", (data) => {
      console.log('✅ Dashboard subscribed:', data);
      this.dispatchEvent('dashboardSubscribed', data);
    });
  }

  /**
   * Setup common handlers for all roles
   */
  private setupCommonHandlers(): void {
    if (!this.connection) return;

    this.connection.on("connectionEstablished", (data) => {
      console.log('🔌 Connection established:', data);
      this.dispatchEvent('connectionEstablished', data);
      this.showConnectionEstablishedNotification(data);
    });

    this.connection.on("receiveNotification", (notification) => {
      console.log('🔔 Notification received:', notification);
      this.dispatchEvent('receiveNotification', notification);
      this.showNotification(notification);
    });

    this.connection.on("receiveUserMessage", (message) => {
      console.log('💬 User message received:', message);
      this.dispatchEvent('receiveUserMessage', message);
      this.showUserMessage(message);
    });

    this.connection.on("maintenanceNotice", (notice) => {
      console.log('🔧 Maintenance notice:', notice);
      this.dispatchEvent('maintenanceNotice', notice);
      this.showMaintenanceNotice(notice);
    });

    this.connection.on("error", (error) => {
      console.error('❌ SignalR error:', error);
      this.dispatchEvent('error', error);
      this.handleSignalRError(error);
    });
  }

  /**
   * Start SignalR connection
   */
  private async startConnection(): Promise<void> {
    if (!this.connection) return;

    try {
      this.connectionState = 'connecting';
      this.dispatchEvent('connecting');

      await this.connection.start();
      
      this.connectionState = 'connected';
      this.reconnectAttempts = 0;
      
      console.log('✅ SignalR connection established');
      this.dispatchEvent('connected');
      
      // Join appropriate groups based on role
      await this.joinRoleGroups();
      
    } catch (error) {
      console.error('❌ SignalR connection failed:', error);
      this.connectionState = 'disconnected';
      this.dispatchEvent('connectionFailed', error);
      this.scheduleReconnect();
    }
  }

  /**
   * Join appropriate groups based on user role
   */
  private async joinRoleGroups(): Promise<void> {
    if (!this.connection || this.connection.state !== signalR.HubConnectionState.Connected) {
      return;
    }

    try {
      switch (this.config.userRole.toLowerCase()) {
        case 'super_admin':
        case 'admin':
          await this.connection.invoke("JoinAdminGroup", this.config.userId, this.config.userRole);
          break;
        case 'marketplace_manager':
          await this.connection.invoke("JoinMarketplaceGroup", "all", this.config.userId);
          break;
        case 'seller':
          // Join specific marketplace groups based on user's marketplaces
          const userMarketplaces = await this.getUserMarketplaces();
          for (const marketplace of userMarketplaces) {
            await this.connection.invoke("JoinMarketplaceGroup", marketplace, this.config.userId);
          }
          break;
        default:
          await this.connection.invoke("SubscribeToDashboard", this.config.userId, ["basic"]);
      }
    } catch (error) {
      console.error('Failed to join role groups:', error);
    }
  }

  /**
   * Handle connection disconnection
   */
  private handleDisconnection(error?: Error): void {
    console.log('🔌 SignalR connection closed:', error);
    this.connectionState = 'disconnected';
    this.dispatchEvent('disconnected', error);
    this.showDisconnectedNotification();
  }

  /**
   * Handle reconnection attempt
   */
  private handleReconnecting(error?: Error): void {
    console.log('🔄 SignalR reconnecting:', error);
    this.connectionState = 'reconnecting';
    this.dispatchEvent('reconnecting', error);
    this.showReconnectingNotification();
  }

  /**
   * Handle successful reconnection
   */
  private handleReconnected(connectionId?: string): void {
    console.log('✅ SignalR reconnected:', connectionId);
    this.connectionState = 'connected';
    this.reconnectAttempts = 0;
    this.dispatchEvent('reconnected', connectionId);
    this.showReconnectedNotification();
  }

  /**
   * Schedule reconnection attempt
   */
  private scheduleReconnect(): void {
    if (this.reconnectAttempts >= this.maxReconnectAttempts) {
      console.error('❌ Max reconnection attempts reached');
      this.dispatchEvent('maxReconnectAttemptsReached');
      this.showMaxReconnectNotification();
      return;
    }

    const delay = Math.min(1000 * Math.pow(2, this.reconnectAttempts), 30000);
    this.reconnectAttempts++;
    
    console.log(`⏳ Scheduling reconnect attempt ${this.reconnectAttempts} in ${delay}ms`);
    
    this.connectionTimeout = setTimeout(() => {
      this.initializeConnection();
    }, delay);
  }

  // Public API Methods

  /**
   * Subscribe to admin updates (admin/super_admin only)
   */
  public async subscribeToAdminUpdates(): Promise<void> {
    if (!this.isConnected()) {
      throw new Error('Not connected to SignalR');
    }

    await this.connection!.invoke("JoinAdminGroup", this.config.userId, this.config.userRole);
  }

  /**
   * Subscribe to dashboard updates
   */
  public async subscribeToDashboard(metrics: string[]): Promise<void> {
    if (!this.isConnected()) {
      throw new Error('Not connected to SignalR');
    }

    await this.connection!.invoke("SubscribeToDashboard", this.config.userId, metrics);
  }

  /**
   * Send admin command (admin/super_admin only)
   */
  public async sendAdminCommand(command: string, parameters: any): Promise<void> {
    if (!this.isConnected()) {
      throw new Error('Not connected to SignalR');
    }

    if (!['super_admin', 'admin'].includes(this.config.userRole)) {
      throw new Error('Insufficient permissions for admin commands');
    }

    await this.connection!.invoke("HandleAdminCommand", command, parameters);
  }

  /**
   * Trigger marketplace sync
   */
  public async triggerMarketplaceSync(marketplace: string, options: any = {}): Promise<void> {
    if (!this.isConnected()) {
      throw new Error('Not connected to SignalR');
    }

    await this.connection!.invoke("TriggerMarketplaceSync", marketplace, options);
  }

  /**
   * Send message to another user
   */
  public async sendUserMessage(targetUserId: string, message: string, type: string = 'info'): Promise<void> {
    if (!this.isConnected()) {
      throw new Error('Not connected to SignalR');
    }

    await this.connection!.invoke("SendUserMessage", targetUserId, message, type);
  }

  /**
   * Add event listener
   */
  public addEventListener(event: string, callback: Function): void {
    if (!this.eventListeners.has(event)) {
      this.eventListeners.set(event, []);
    }
    this.eventListeners.get(event)!.push(callback);
  }

  /**
   * Remove event listener
   */
  public removeEventListener(event: string, callback: Function): void {
    const listeners = this.eventListeners.get(event);
    if (listeners) {
      const index = listeners.indexOf(callback);
      if (index > -1) {
        listeners.splice(index, 1);
      }
    }
  }

  /**
   * Check if connected
   */
  public isConnected(): boolean {
    return this.connectionState === 'connected' && 
           this.connection?.state === signalR.HubConnectionState.Connected;
  }

  /**
   * Get connection state
   */
  public getConnectionState(): string {
    return this.connectionState;
  }

  /**
   * Disconnect
   */
  public async disconnect(): Promise<void> {
    if (this.connectionTimeout) {
      clearTimeout(this.connectionTimeout);
      this.connectionTimeout = null;
    }

    if (this.connection) {
      await this.connection.stop();
      this.connection = null;
    }
    
    this.connectionState = 'disconnected';
    this.dispatchEvent('disconnected');
  }

  // Private Helper Methods

  private dispatchEvent(eventType: string, data?: any): void {
    // Dispatch to internal listeners
    const listeners = this.eventListeners.get(eventType);
    if (listeners) {
      listeners.forEach(callback => {
        try {
          callback(data);
        } catch (error) {
          console.error(`Error in event listener for ${eventType}:`, error);
        }
      });
    }

    // Dispatch custom DOM events for integration with existing code
    const customEvent = new CustomEvent(`signalr:${eventType}`, { detail: data });
    document.dispatchEvent(customEvent);
  }

  private getAuthToken(): string {
    return this.config.authToken || 
           localStorage.getItem('auth_token') || 
           document.querySelector('meta[name="auth-token"]')?.getAttribute('content') || 
           '';
  }

  private async getUserMarketplaces(): Promise<string[]> {
    // In production, fetch from user profile or API
    return ['amazon', 'ebay']; // Default marketplaces
  }

  // UI Update Methods

  private updateAdminDashboard(data: any): void {
    if (typeof window.updateGeminiDashboard === 'function') {
      window.updateGeminiDashboard(data);
    }
  }

  private handleSystemAlert(alert: any): void {
    this.showNotification({
      message: alert.message,
      type: alert.level,
      id: alert.id
    });
  }

  private handleCriticalAlert(alert: any): void {
    this.showNotification({
      message: alert.message,
      type: 'critical',
      id: alert.id,
      persistent: true
    });
  }

  private updateSystemHealth(healthData: any): void {
    // Update system health indicators in UI
    const healthIndicator = document.getElementById('system-health-indicator');
    if (healthIndicator) {
      healthIndicator.className = `health-indicator ${healthData.status}`;
      healthIndicator.textContent = healthData.status.toUpperCase();
    }
  }

  private updateSyncProgress(marketplace: string, progress: number): void {
    const progressBar = document.querySelector(`#${marketplace}-progress-bar`);
    if (progressBar) {
      (progressBar as HTMLElement).style.width = `${progress}%`;
    }

    const progressText = document.querySelector(`#${marketplace}-progress-text`);
    if (progressText) {
      progressText.textContent = `${progress.toFixed(1)}%`;
    }
  }

  private showSyncStartedNotification(data: any): void {
    this.showNotification({
      message: `${data.marketplace.toUpperCase()} sync started`,
      type: 'info',
      duration: 3000
    });
  }

  private showSyncCompletedNotification(data: any): void {
    this.showNotification({
      message: `${data.marketplace.toUpperCase()} sync completed successfully`,
      type: 'success',
      duration: 5000
    });
  }

  private updateInventoryStatus(update: any): void {
    // Update inventory status in UI
    console.log('Updating inventory status:', update);
  }

  private showOrderNotification(order: any): void {
    this.showNotification({
      message: `New order received: ${order.orderNumber}`,
      type: 'info',
      duration: 5000
    });
  }

  private updateDashboardMetrics(data: any): void {
    // Update dashboard metrics
    if (typeof window.updateDashboardMetrics === 'function') {
      window.updateDashboardMetrics(data);
    }
  }

  private updateMetricsDisplay(metrics: any): void {
    Object.keys(metrics).forEach(key => {
      const element = document.getElementById(`metric-${key}`);
      if (element) {
        element.textContent = metrics[key];
      }
    });
  }

  private updatePerformanceCharts(performance: any): void {
    // Update performance charts
    if (typeof window.updatePerformanceCharts === 'function') {
      window.updatePerformanceCharts(performance);
    }
  }

  private updateBusinessDisplay(business: any): void {
    // Update business metrics display
    if (typeof window.updateBusinessMetrics === 'function') {
      window.updateBusinessMetrics(business);
    }
  }

  private showConnectionEstablishedNotification(data: any): void {
    this.showNotification({
      message: `Connected to ${data.welcomeMessage}`,
      type: 'success',
      duration: 3000
    });
  }

  private showUserMessage(message: any): void {
    this.showNotification({
      message: message.message,
      type: message.type,
      duration: 5000
    });
  }

  private showMaintenanceNotice(notice: any): void {
    this.showNotification({
      message: notice.message,
      type: 'warning',
      persistent: true
    });
  }

  private handleSignalRError(error: any): void {
    console.error('SignalR error:', error);
    this.showNotification({
      message: `Connection error: ${error.message || 'Unknown error'}`,
      type: 'error',
      duration: 5000
    });
  }

  private showDisconnectedNotification(): void {
    this.showNotification({
      message: 'Connection lost. Attempting to reconnect...',
      type: 'warning',
      id: 'connection-status'
    });
  }

  private showReconnectingNotification(): void {
    this.showNotification({
      message: 'Reconnecting to server...',
      type: 'info',
      id: 'connection-status'
    });
  }

  private showReconnectedNotification(): void {
    this.showNotification({
      message: 'Connection restored successfully',
      type: 'success',
      duration: 3000,
      id: 'connection-status'
    });
  }

  private showMaxReconnectNotification(): void {
    this.showNotification({
      message: 'Unable to reconnect. Please refresh the page.',
      type: 'error',
      persistent: true
    });
  }

  private showNotification(notification: any): void {
    // Use existing notification system or create a simple one
    if (typeof window.showGeminiNotification === 'function') {
      window.showGeminiNotification(notification.message, notification.type);
    } else if (typeof window.showNotification === 'function') {
      window.showNotification(notification);
    } else {
      // Fallback to console and simple alert for critical messages
      console.log(`${notification.type?.toUpperCase()}: ${notification.message}`);
      if (notification.type === 'critical' || notification.persistent) {
        alert(notification.message);
      }
    }
  }
}

// Export for module usage
export default MesChainSignalRClient;

// Global window interface for TypeScript
declare global {
  interface Window {
    MesChainSignalR: typeof MesChainSignalRClient;
    updateGeminiDashboard?: (data: any) => void;
    updateDashboardMetrics?: (data: any) => void;
    updatePerformanceCharts?: (performance: any) => void;
    updateBusinessMetrics?: (business: any) => void;
    showGeminiNotification?: (message: string, type: string) => void;
    showNotification?: (notification: any) => void;
  }
}

// Make available globally for backward compatibility
if (typeof window !== 'undefined') {
  window.MesChainSignalR = MesChainSignalRClient;
}
