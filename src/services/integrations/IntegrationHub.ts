/**
 * Advanced Integration Hub - Central marketplace integration management
 * Manages all marketplace connections, synchronization, and data flow
 * 
 * @author MesChain Team
 * @version 3.0.0
 * @since 2025-01-15
 */

import { EventEmitter } from 'events';
import axios, { AxiosInstance, AxiosRequestConfig } from 'axios';
import { performance } from 'perf_hooks';

// Types and Interfaces
export interface MarketplaceConfig {
  id: string;
  name: string;
  apiUrl: string;
  apiKey: string;
  apiSecret?: string;
  authType: 'bearer' | 'basic' | 'oauth' | 'custom';
  rateLimit: {
    requests: number;
    window: number; // milliseconds
  };
  endpoints: {
    products: string;
    orders: string;
    inventory: string;
    webhooks: string;
  };
  features: {
    realTimeSync: boolean;
    bulkOperations: boolean;
    webhookSupport: boolean;
    fileUpload: boolean;
  };
}

export interface SyncOperation {
  id: string;
  marketplaceId: string;
  type: 'product' | 'order' | 'inventory' | 'category';
  action: 'create' | 'update' | 'delete' | 'sync';
  data: any;
  status: 'pending' | 'processing' | 'completed' | 'failed' | 'retrying';
  priority: 'low' | 'medium' | 'high' | 'critical';
  attempts: number;
  maxRetries: number;
  createdAt: Date;
  updatedAt: Date;
  completedAt?: Date;
  error?: string;
  metadata: Record<string, any>;
}

export interface IntegrationMetrics {
  marketplaceId: string;
  totalOperations: number;
  successfulOperations: number;
  failedOperations: number;
  averageResponseTime: number;
  lastSyncTime: Date;
  apiQuotaUsed: number;
  apiQuotaLimit: number;
  errors: Array<{
    timestamp: Date;
    operation: string;
    error: string;
    httpStatus?: number;
  }>;
}

export interface WebhookEvent {
  id: string;
  marketplaceId: string;
  type: string;
  payload: any;
  timestamp: Date;
  processed: boolean;
  retryCount: number;
}

export class IntegrationHub extends EventEmitter {
  private marketplaces: Map<string, MarketplaceConfig> = new Map();
  private apiClients: Map<string, AxiosInstance> = new Map();
  private syncQueue: SyncOperation[] = [];
  private activeOperations: Map<string, SyncOperation> = new Map();
  private metrics: Map<string, IntegrationMetrics> = new Map();
  private rateLimiters: Map<string, { requests: number; resetTime: number }> = new Map();
  private webhookQueue: WebhookEvent[] = [];
  private isProcessing = false;

  constructor() {
    super();
    this.startQueueProcessor();
    this.startMetricsCollector();
    this.startHealthMonitor();
  }

  /**
   * Register a new marketplace integration
   */
  public async registerMarketplace(config: MarketplaceConfig): Promise<void> {
    try {
      // Validate configuration
      this.validateMarketplaceConfig(config);

      // Create API client
      const apiClient = this.createApiClient(config);
      
      // Test connection
      await this.testConnection(config, apiClient);

      // Store configuration and client
      this.marketplaces.set(config.id, config);
      this.apiClients.set(config.id, apiClient);

      // Initialize metrics
      this.metrics.set(config.id, {
        marketplaceId: config.id,
        totalOperations: 0,
        successfulOperations: 0,
        failedOperations: 0,
        averageResponseTime: 0,
        lastSyncTime: new Date(),
        apiQuotaUsed: 0,
        apiQuotaLimit: config.rateLimit.requests,
        errors: []
      });

      // Initialize rate limiter
      this.rateLimiters.set(config.id, {
        requests: 0,
        resetTime: Date.now() + config.rateLimit.window
      });

      this.emit('marketplace:registered', { marketplaceId: config.id, config });
      
      console.log(`✅ Marketplace ${config.name} registered successfully`);
    } catch (error) {
      console.error(`❌ Failed to register marketplace ${config.name}:`, error);
      throw error;
    }
  }

  /**
   * Add operation to sync queue
   */
  public async queueOperation(operation: Omit<SyncOperation, 'id' | 'createdAt' | 'updatedAt'>): Promise<string> {
    const syncOperation: SyncOperation = {
      ...operation,
      id: `op_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
      createdAt: new Date(),
      updatedAt: new Date(),
      attempts: 0
    };

    // Add to queue based on priority
    if (operation.priority === 'critical') {
      this.syncQueue.unshift(syncOperation);
    } else {
      this.syncQueue.push(syncOperation);
    }

    this.emit('operation:queued', syncOperation);
    
    return syncOperation.id;
  }

  /**
   * Process sync operation
   */
  private async processOperation(operation: SyncOperation): Promise<void> {
    const startTime = performance.now();
    
    try {
      operation.status = 'processing';
      operation.updatedAt = new Date();
      operation.attempts++;

      this.activeOperations.set(operation.id, operation);
      this.emit('operation:started', operation);

      // Check rate limits
      await this.checkRateLimit(operation.marketplaceId);

      // Get API client
      const apiClient = this.apiClients.get(operation.marketplaceId);
      if (!apiClient) {
        throw new Error(`No API client found for marketplace ${operation.marketplaceId}`);
      }

      // Execute operation based on type and action
      const result = await this.executeOperation(operation, apiClient);

      // Update operation status
      operation.status = 'completed';
      operation.completedAt = new Date();
      operation.metadata.result = result;

      // Update metrics
      this.updateMetrics(operation.marketplaceId, 'success', performance.now() - startTime);

      this.emit('operation:completed', operation);

    } catch (error) {
      console.error(`❌ Operation ${operation.id} failed:`, error);
      
      operation.error = error.message;
      operation.updatedAt = new Date();

      // Retry logic
      if (operation.attempts < operation.maxRetries) {
        operation.status = 'retrying';
        
        // Add back to queue with exponential backoff
        const delay = Math.pow(2, operation.attempts) * 1000;
        setTimeout(() => {
          this.syncQueue.unshift(operation);
        }, delay);

        this.emit('operation:retrying', operation);
      } else {
        operation.status = 'failed';
        this.updateMetrics(operation.marketplaceId, 'failure', performance.now() - startTime);
        this.emit('operation:failed', operation);
      }
    } finally {
      this.activeOperations.delete(operation.id);
    }
  }

  /**
   * Execute specific operation
   */
  private async executeOperation(operation: SyncOperation, apiClient: AxiosInstance): Promise<any> {
    const config = this.marketplaces.get(operation.marketplaceId);
    if (!config) {
      throw new Error(`Marketplace config not found: ${operation.marketplaceId}`);
    }

    switch (operation.type) {
      case 'product':
        return await this.executeProductOperation(operation, apiClient, config);
      case 'order':
        return await this.executeOrderOperation(operation, apiClient, config);
      case 'inventory':
        return await this.executeInventoryOperation(operation, apiClient, config);
      case 'category':
        return await this.executeCategoryOperation(operation, apiClient, config);
      default:
        throw new Error(`Unknown operation type: ${operation.type}`);
    }
  }

  /**
   * Execute product operations
   */
  private async executeProductOperation(
    operation: SyncOperation, 
    apiClient: AxiosInstance, 
    config: MarketplaceConfig
  ): Promise<any> {
    const endpoint = config.endpoints.products;
    
    switch (operation.action) {
      case 'create':
        return await apiClient.post(endpoint, operation.data);
      case 'update':
        return await apiClient.put(`${endpoint}/${operation.data.id}`, operation.data);
      case 'delete':
        return await apiClient.delete(`${endpoint}/${operation.data.id}`);
      case 'sync':
        return await apiClient.get(endpoint, { params: operation.data });
      default:
        throw new Error(`Unknown product action: ${operation.action}`);
    }
  }

  /**
   * Execute order operations
   */
  private async executeOrderOperation(
    operation: SyncOperation, 
    apiClient: AxiosInstance, 
    config: MarketplaceConfig
  ): Promise<any> {
    const endpoint = config.endpoints.orders;
    
    switch (operation.action) {
      case 'update':
        return await apiClient.put(`${endpoint}/${operation.data.id}`, operation.data);
      case 'sync':
        return await apiClient.get(endpoint, { params: operation.data });
      default:
        throw new Error(`Unknown order action: ${operation.action}`);
    }
  }

  /**
   * Execute inventory operations
   */
  private async executeInventoryOperation(
    operation: SyncOperation, 
    apiClient: AxiosInstance, 
    config: MarketplaceConfig
  ): Promise<any> {
    const endpoint = config.endpoints.inventory;
    
    switch (operation.action) {
      case 'update':
        return await apiClient.put(endpoint, operation.data);
      case 'sync':
        return await apiClient.get(endpoint, { params: operation.data });
      default:
        throw new Error(`Unknown inventory action: ${operation.action}`);
    }
  }

  /**
   * Execute category operations
   */
  private async executeCategoryOperation(
    operation: SyncOperation, 
    apiClient: AxiosInstance, 
    config: MarketplaceConfig
  ): Promise<any> {
    // Category operations are usually read-only
    switch (operation.action) {
      case 'sync':
        return await apiClient.get('/categories', { params: operation.data });
      default:
        throw new Error(`Unknown category action: ${operation.action}`);
    }
  }

  /**
   * Check rate limits
   */
  private async checkRateLimit(marketplaceId: string): Promise<void> {
    const config = this.marketplaces.get(marketplaceId);
    const limiter = this.rateLimiters.get(marketplaceId);
    
    if (!config || !limiter) return;

    const now = Date.now();
    
    // Reset window if expired
    if (now >= limiter.resetTime) {
      limiter.requests = 0;
      limiter.resetTime = now + config.rateLimit.window;
    }

    // Check if we've hit the limit
    if (limiter.requests >= config.rateLimit.requests) {
      const waitTime = limiter.resetTime - now;
      await new Promise(resolve => setTimeout(resolve, waitTime));
      
      // Reset after waiting
      limiter.requests = 0;
      limiter.resetTime = Date.now() + config.rateLimit.window;
    }

    limiter.requests++;
  }

  /**
   * Create API client for marketplace
   */
  private createApiClient(config: MarketplaceConfig): AxiosInstance {
    const clientConfig: AxiosRequestConfig = {
      baseURL: config.apiUrl,
      timeout: 30000,
      headers: {
        'Content-Type': 'application/json',
        'User-Agent': 'MesChain-Sync/3.0.0'
      }
    };

    // Configure authentication
    switch (config.authType) {
      case 'bearer':
        clientConfig.headers['Authorization'] = `Bearer ${config.apiKey}`;
        break;
      case 'basic':
        clientConfig.auth = {
          username: config.apiKey,
          password: config.apiSecret || ''
        };
        break;
      case 'custom':
        clientConfig.headers['X-API-Key'] = config.apiKey;
        break;
    }

    const client = axios.create(clientConfig);

    // Add request interceptor for logging
    client.interceptors.request.use(
      (config) => {
        console.log(`🔄 API Request: ${config.method?.toUpperCase()} ${config.url}`);
        return config;
      },
      (error) => {
        console.error('❌ Request error:', error);
        return Promise.reject(error);
      }
    );

    // Add response interceptor for error handling
    client.interceptors.response.use(
      (response) => {
        console.log(`✅ API Response: ${response.status} ${response.config.url}`);
        return response;
      },
      (error) => {
        console.error(`❌ API Error: ${error.response?.status} ${error.config?.url}`, error.message);
        return Promise.reject(error);
      }
    );

    return client;
  }

  /**
   * Test connection to marketplace
   */
  private async testConnection(config: MarketplaceConfig, apiClient: AxiosInstance): Promise<void> {
    try {
      // Try to fetch a simple endpoint or health check
      await apiClient.get('/health', { timeout: 5000 });
    } catch (error) {
      // If health endpoint doesn't exist, try products endpoint
      try {
        await apiClient.get(config.endpoints.products, { 
          timeout: 5000,
          params: { limit: 1 }
        });
      } catch (innerError) {
        throw new Error(`Failed to connect to ${config.name}: ${innerError.message}`);
      }
    }
  }

  /**
   * Validate marketplace configuration
   */
  private validateMarketplaceConfig(config: MarketplaceConfig): void {
    const required = ['id', 'name', 'apiUrl', 'apiKey', 'authType', 'endpoints'];
    const missing = required.filter(field => !config[field]);
    
    if (missing.length > 0) {
      throw new Error(`Missing required configuration fields: ${missing.join(', ')}`);
    }

    if (!config.endpoints.products || !config.endpoints.orders) {
      throw new Error('Product and order endpoints are required');
    }
  }

  /**
   * Update operation metrics
   */
  private updateMetrics(marketplaceId: string, result: 'success' | 'failure', responseTime: number): void {
    const metrics = this.metrics.get(marketplaceId);
    if (!metrics) return;

    metrics.totalOperations++;
    metrics.lastSyncTime = new Date();

    if (result === 'success') {
      metrics.successfulOperations++;
    } else {
      metrics.failedOperations++;
    }

    // Update average response time
    const totalOps = metrics.totalOperations;
    metrics.averageResponseTime = ((metrics.averageResponseTime * (totalOps - 1)) + responseTime) / totalOps;
  }

  /**
   * Start queue processor
   */
  private startQueueProcessor(): void {
    setInterval(async () => {
      if (this.isProcessing || this.syncQueue.length === 0) return;

      this.isProcessing = true;

      try {
        // Process up to 5 operations concurrently
        const operations = this.syncQueue.splice(0, 5);
        const promises = operations.map(op => this.processOperation(op));
        
        await Promise.allSettled(promises);
      } catch (error) {
        console.error('❌ Queue processor error:', error);
      } finally {
        this.isProcessing = false;
      }
    }, 1000);
  }

  /**
   * Start metrics collector
   */
  private startMetricsCollector(): void {
    setInterval(() => {
      this.emit('metrics:collected', {
        timestamp: new Date(),
        queueSize: this.syncQueue.length,
        activeOperations: this.activeOperations.size,
        marketplaces: Array.from(this.metrics.values())
      });
    }, 60000); // Every minute
  }

  /**
   * Start health monitor
   */
  private startHealthMonitor(): void {
    setInterval(async () => {
      const healthStatus = {
        timestamp: new Date(),
        healthy: true,
        issues: [] as string[]
      };

      // Check queue size
      if (this.syncQueue.length > 1000) {
        healthStatus.healthy = false;
        healthStatus.issues.push('Queue size too large');
      }

      // Check failed operations ratio
      for (const [marketplaceId, metrics] of this.metrics.entries()) {
        if (metrics.totalOperations > 0) {
          const failureRate = metrics.failedOperations / metrics.totalOperations;
          if (failureRate > 0.1) { // 10% failure rate threshold
            healthStatus.healthy = false;
            healthStatus.issues.push(`High failure rate for ${marketplaceId}: ${(failureRate * 100).toFixed(1)}%`);
          }
        }
      }

      this.emit('health:status', healthStatus);
    }, 300000); // Every 5 minutes
  }

  /**
   * Process webhook event
   */
  public async processWebhook(event: WebhookEvent): Promise<void> {
    try {
      console.log(`🔔 Processing webhook: ${event.type} from ${event.marketplaceId}`);

      // Add to webhook queue
      this.webhookQueue.push(event);

      // Process webhook based on type
      switch (event.type) {
        case 'order.created':
        case 'order.updated':
          await this.handleOrderWebhook(event);
          break;
        case 'product.updated':
          await this.handleProductWebhook(event);
          break;
        case 'inventory.changed':
          await this.handleInventoryWebhook(event);
          break;
        default:
          console.log(`ℹ️ Unhandled webhook type: ${event.type}`);
      }

      event.processed = true;
      this.emit('webhook:processed', event);

    } catch (error) {
      console.error(`❌ Webhook processing failed:`, error);
      event.retryCount++;
      
      if (event.retryCount < 3) {
        // Retry after delay
        setTimeout(() => {
          this.processWebhook(event);
        }, 5000 * event.retryCount);
      }
    }
  }

  /**
   * Handle order webhook
   */
  private async handleOrderWebhook(event: WebhookEvent): Promise<void> {
    const orderData = event.payload;
    
    // Queue order sync operation
    await this.queueOperation({
      marketplaceId: event.marketplaceId,
      type: 'order',
      action: 'sync',
      data: { orderId: orderData.id },
      status: 'pending',
      priority: 'high',
      maxRetries: 3,
      metadata: { webhookId: event.id }
    });
  }

  /**
   * Handle product webhook
   */
  private async handleProductWebhook(event: WebhookEvent): Promise<void> {
    const productData = event.payload;
    
    // Queue product sync operation
    await this.queueOperation({
      marketplaceId: event.marketplaceId,
      type: 'product',
      action: 'sync',
      data: { productId: productData.id },
      status: 'pending',
      priority: 'medium',
      maxRetries: 3,
      metadata: { webhookId: event.id }
    });
  }

  /**
   * Handle inventory webhook
   */
  private async handleInventoryWebhook(event: WebhookEvent): Promise<void> {
    const inventoryData = event.payload;
    
    // Queue inventory sync operation
    await this.queueOperation({
      marketplaceId: event.marketplaceId,
      type: 'inventory',
      action: 'update',
      data: inventoryData,
      status: 'pending',
      priority: 'high',
      maxRetries: 3,
      metadata: { webhookId: event.id }
    });
  }

  /**
   * Get integration metrics
   */
  public getMetrics(marketplaceId?: string): IntegrationMetrics | IntegrationMetrics[] {
    if (marketplaceId) {
      return this.metrics.get(marketplaceId) || null;
    }
    return Array.from(this.metrics.values());
  }

  /**
   * Get queue status
   */
  public getQueueStatus(): {
    pending: number;
    processing: number;
    webhooks: number;
  } {
    return {
      pending: this.syncQueue.length,
      processing: this.activeOperations.size,
      webhooks: this.webhookQueue.filter(w => !w.processed).length
    };
  }

  /**
   * Get marketplace status
   */
  public getMarketplaceStatus(): Array<{
    id: string;
    name: string;
    connected: boolean;
    lastActivity: Date;
    operationsCount: number;
    failureRate: number;
  }> {
    return Array.from(this.marketplaces.entries()).map(([id, config]) => {
      const metrics = this.metrics.get(id);
      return {
        id,
        name: config.name,
        connected: this.apiClients.has(id),
        lastActivity: metrics?.lastSyncTime || new Date(),
        operationsCount: metrics?.totalOperations || 0,
        failureRate: metrics ? (metrics.failedOperations / Math.max(metrics.totalOperations, 1)) : 0
      };
    });
  }

  /**
   * Bulk sync operation
   */
  public async bulkSync(
    marketplaceId: string, 
    type: 'product' | 'order' | 'inventory',
    filters?: Record<string, any>
  ): Promise<string[]> {
    const config = this.marketplaces.get(marketplaceId);
    if (!config || !config.features.bulkOperations) {
      throw new Error(`Bulk operations not supported for marketplace: ${marketplaceId}`);
    }

    // Create bulk sync operations
    const operationIds: string[] = [];
    const batchSize = 100;

    for (let i = 0; i < 10; i++) { // Process up to 1000 items in batches
      const operationId = await this.queueOperation({
        marketplaceId,
        type,
        action: 'sync',
        data: { 
          ...filters,
          limit: batchSize,
          offset: i * batchSize
        },
        status: 'pending',
        priority: 'medium',
        maxRetries: 3,
        metadata: { bulkSync: true, batch: i }
      });

      operationIds.push(operationId);
    }

    return operationIds;
  }

  /**
   * Cleanup old operations and metrics
   */
  public cleanup(olderThanDays: number = 7): void {
    const cutoffDate = new Date();
    cutoffDate.setDate(cutoffDate.getDate() - olderThanDays);

    // Clean up metrics errors
    for (const metrics of this.metrics.values()) {
      metrics.errors = metrics.errors.filter(error => error.timestamp > cutoffDate);
    }

    // Clean up processed webhooks
    this.webhookQueue = this.webhookQueue.filter(webhook => 
      !webhook.processed || new Date(webhook.timestamp) > cutoffDate
    );

    console.log(`🧹 Cleanup completed: Removed data older than ${olderThanDays} days`);
  }

  /**
   * Shutdown integration hub
   */
  public async shutdown(): Promise<void> {
    console.log('🛑 Shutting down Integration Hub...');
    
    // Wait for active operations to complete
    while (this.activeOperations.size > 0) {
      await new Promise(resolve => setTimeout(resolve, 1000));
    }

    // Clear all intervals and timers
    this.removeAllListeners();
    
    console.log('✅ Integration Hub shutdown complete');
  }
}

export default IntegrationHub;