/**
 * Real-Time Sync Engine - Live data synchronization across marketplaces
 * Provides real-time data sync, conflict resolution, and consistency management
 * 
 * @author MesChain Team
 * @version 3.0.0
 * @since 2025-01-15
 */

import { EventEmitter } from 'events';
import WebSocket from 'ws';
import { performance } from 'perf_hooks';
import crypto from 'crypto';

// Types and Interfaces
export interface SyncEntity {
  id: string;
  type: 'product' | 'order' | 'inventory' | 'category' | 'customer';
  marketplaceId: string;
  data: any;
  version: number;
  lastModified: Date;
  checksum: string;
  metadata: Record<string, any>;
}

export interface SyncEvent {
  id: string;
  entityId: string;
  entityType: string;
  action: 'create' | 'update' | 'delete' | 'sync';
  marketplaceId: string;
  sourceMarketplace: string;
  data: any;
  timestamp: Date;
  version: number;
  priority: 'low' | 'medium' | 'high' | 'critical';
  conflictResolution?: 'merge' | 'override' | 'manual' | 'ignore';
}

export interface SyncConflict {
  id: string;
  entityId: string;
  entityType: string;
  conflictType: 'version' | 'data' | 'timestamp' | 'marketplace';
  sourceData: any;
  targetData: any;
  sourceMarketplace: string;
  targetMarketplace: string;
  detectedAt: Date;
  resolved: boolean;
  resolution?: 'merge' | 'override' | 'manual';
  resolvedAt?: Date;
  resolvedBy?: string;
}

export interface SyncRule {
  id: string;
  entityType: string;
  sourceMarketplace: string;
  targetMarketplaces: string[];
  direction: 'bidirectional' | 'source_to_target' | 'target_to_source';
  priority: number;
  conditions: {
    field: string;
    operator: 'equals' | 'not_equals' | 'contains' | 'greater_than' | 'less_than';
    value: any;
  }[];
  transformations: {
    field: string;
    transformation: 'map' | 'format' | 'calculate' | 'default';
    config: any;
  }[];
  conflictResolution: 'merge' | 'override' | 'manual' | 'source_wins' | 'target_wins' | 'latest_wins';
  enabled: boolean;
}

export interface SyncMetrics {
  totalEvents: number;
  processedEvents: number;
  failedEvents: number;
  conflictsDetected: number;
  conflictsResolved: number;
  averageProcessingTime: number;
  throughputPerSecond: number;
  lastSyncTime: Date;
  activeConnections: number;
  queueSize: number;
}

export class RealTimeSyncEngine extends EventEmitter {
  private entities: Map<string, SyncEntity> = new Map();
  private syncRules: Map<string, SyncRule> = new Map();
  private conflicts: Map<string, SyncConflict> = new Map();
  private eventQueue: SyncEvent[] = [];
  private wsConnections: Map<string, WebSocket> = new Map();
  private processingQueue = false;
  private metrics: SyncMetrics;
  private wsServer: WebSocket.Server;

  constructor(private config: {
    port?: number;
    maxQueueSize?: number;
    processingInterval?: number;
    conflictTimeoutMs?: number;
  } = {}) {
    super();
    
    this.metrics = {
      totalEvents: 0,
      processedEvents: 0,
      failedEvents: 0,
      conflictsDetected: 0,
      conflictsResolved: 0,
      averageProcessingTime: 0,
      throughputPerSecond: 0,
      lastSyncTime: new Date(),
      activeConnections: 0,
      queueSize: 0
    };

    this.startWebSocketServer();
    this.startEventProcessor();
    this.startMetricsCollector();
    this.startConflictResolver();
  }

  /**
   * Start WebSocket server for real-time connections
   */
  private startWebSocketServer(): void {
    const port = this.config.port || 8080;
    
    this.wsServer = new WebSocket.Server({ port });

    this.wsServer.on('connection', (ws: WebSocket, req) => {
      const connectionId = `conn_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
      
      console.log(`🔌 New WebSocket connection: ${connectionId}`);
      this.wsConnections.set(connectionId, ws);
      this.metrics.activeConnections++;

      // Send welcome message
      this.sendMessage(ws, {
        type: 'connection_established',
        connectionId,
        timestamp: new Date().toISOString()
      });

      // Handle messages
      ws.on('message', (data: string) => {
        try {
          const message = JSON.parse(data);
          this.handleWebSocketMessage(connectionId, message);
        } catch (error) {
          console.error('❌ Invalid WebSocket message:', error);
          this.sendMessage(ws, {
            type: 'error',
            message: 'Invalid message format'
          });
        }
      });

      // Handle disconnection
      ws.on('close', () => {
        console.log(`🔌 WebSocket disconnected: ${connectionId}`);
        this.wsConnections.delete(connectionId);
        this.metrics.activeConnections--;
      });

      // Handle errors
      ws.on('error', (error) => {
        console.error(`❌ WebSocket error for ${connectionId}:`, error);
        this.wsConnections.delete(connectionId);
        this.metrics.activeConnections--;
      });
    });

    console.log(`🚀 Real-time Sync WebSocket server started on port ${port}`);
  }

  /**
   * Handle WebSocket messages
   */
  private handleWebSocketMessage(connectionId: string, message: any): void {
    switch (message.type) {
      case 'subscribe':
        this.handleSubscribe(connectionId, message);
        break;
      case 'unsubscribe':
        this.handleUnsubscribe(connectionId, message);
        break;
      case 'sync_event':
        this.handleSyncEvent(message.event);
        break;
      case 'resolve_conflict':
        this.handleConflictResolution(message.conflictId, message.resolution);
        break;
      case 'ping':
        const ws = this.wsConnections.get(connectionId);
        if (ws) {
          this.sendMessage(ws, { type: 'pong', timestamp: new Date().toISOString() });
        }
        break;
      default:
        console.log(`ℹ️ Unknown message type: ${message.type}`);
    }
  }

  /**
   * Handle subscription to entity updates
   */
  private handleSubscribe(connectionId: string, message: any): void {
    const { entityType, entityId, marketplaceId } = message;
    
    // Store subscription info (you might want to use a proper subscription manager)
    console.log(`📫 Subscription: ${connectionId} -> ${entityType}:${entityId}@${marketplaceId}`);
    
    const ws = this.wsConnections.get(connectionId);
    if (ws) {
      this.sendMessage(ws, {
        type: 'subscribed',
        entityType,
        entityId,
        marketplaceId,
        timestamp: new Date().toISOString()
      });
    }
  }

  /**
   * Handle unsubscription
   */
  private handleUnsubscribe(connectionId: string, message: any): void {
    const { entityType, entityId, marketplaceId } = message;
    
    console.log(`📫 Unsubscription: ${connectionId} -> ${entityType}:${entityId}@${marketplaceId}`);
    
    const ws = this.wsConnections.get(connectionId);
    if (ws) {
      this.sendMessage(ws, {
        type: 'unsubscribed',
        entityType,
        entityId,
        marketplaceId,
        timestamp: new Date().toISOString()
      });
    }
  }

  /**
   * Send message to WebSocket client
   */
  private sendMessage(ws: WebSocket, message: any): void {
    if (ws.readyState === WebSocket.OPEN) {
      ws.send(JSON.stringify(message));
    }
  }

  /**
   * Broadcast message to all connected clients
   */
  private broadcast(message: any): void {
    for (const ws of this.wsConnections.values()) {
      this.sendMessage(ws, message);
    }
  }

  /**
   * Add sync event to queue
   */
  public async addSyncEvent(event: Omit<SyncEvent, 'id' | 'timestamp'>): Promise<string> {
    const syncEvent: SyncEvent = {
      ...event,
      id: `evt_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
      timestamp: new Date()
    };

    // Check queue size limit
    const maxQueueSize = this.config.maxQueueSize || 10000;
    if (this.eventQueue.length >= maxQueueSize) {
      throw new Error('Sync queue is full');
    }

    // Add to queue based on priority
    if (event.priority === 'critical') {
      this.eventQueue.unshift(syncEvent);
    } else {
      this.eventQueue.push(syncEvent);
    }

    this.metrics.totalEvents++;
    this.metrics.queueSize = this.eventQueue.length;

    // Broadcast to subscribers
    this.broadcast({
      type: 'sync_event_queued',
      event: syncEvent,
      queueSize: this.eventQueue.length
    });

    this.emit('event:queued', syncEvent);
    return syncEvent.id;
  }

  /**
   * Handle sync event
   */
  private async handleSyncEvent(event: SyncEvent): Promise<void> {
    await this.addSyncEvent(event);
  }

  /**
   * Process sync events
   */
  private async processSyncEvent(event: SyncEvent): Promise<void> {
    const startTime = performance.now();
    
    try {
      console.log(`🔄 Processing sync event: ${event.action} ${event.entityType}:${event.entityId}`);

      // Get or create entity
      const entityKey = `${event.entityType}:${event.entityId}:${event.marketplaceId}`;
      let entity = this.entities.get(entityKey);

      // Check for conflicts
      const conflict = await this.detectConflict(event, entity);
      if (conflict) {
        await this.handleConflict(conflict);
        return;
      }

      // Apply sync rules
      const appliedRules = await this.applySyncRules(event);
      if (appliedRules.length === 0) {
        console.log(`ℹ️ No applicable sync rules for event ${event.id}`);
        return;
      }

      // Process the event
      switch (event.action) {
        case 'create':
        case 'update':
          entity = await this.createOrUpdateEntity(event, entity);
          break;
        case 'delete':
          await this.deleteEntity(event);
          break;
        case 'sync':
          await this.syncEntity(event);
          break;
      }

      // Store updated entity
      if (entity) {
        this.entities.set(entityKey, entity);
      }

      // Propagate to other marketplaces
      await this.propagateChanges(event, appliedRules);

      // Update metrics
      this.metrics.processedEvents++;
      const processingTime = performance.now() - startTime;
      this.updateProcessingTimeMetrics(processingTime);

      // Broadcast success
      this.broadcast({
        type: 'sync_event_processed',
        eventId: event.id,
        entityType: event.entityType,
        entityId: event.entityId,
        processingTime
      });

      this.emit('event:processed', { event, entity, processingTime });

    } catch (error) {
      console.error(`❌ Failed to process sync event ${event.id}:`, error);
      
      this.metrics.failedEvents++;
      
      // Broadcast error
      this.broadcast({
        type: 'sync_event_failed',
        eventId: event.id,
        error: error.message
      });

      this.emit('event:failed', { event, error });
    }
  }

  /**
   * Detect sync conflicts
   */
  private async detectConflict(event: SyncEvent, existingEntity?: SyncEntity): Promise<SyncConflict | null> {
    if (!existingEntity) {
      return null; // No conflict for new entities
    }

    const conflict: SyncConflict = {
      id: `conflict_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
      entityId: event.entityId,
      entityType: event.entityType,
      conflictType: 'version',
      sourceData: event.data,
      targetData: existingEntity.data,
      sourceMarketplace: event.sourceMarketplace,
      targetMarketplace: existingEntity.marketplaceId,
      detectedAt: new Date(),
      resolved: false
    };

    // Check version conflict
    if (event.version <= existingEntity.version) {
      conflict.conflictType = 'version';
      this.conflicts.set(conflict.id, conflict);
      this.metrics.conflictsDetected++;
      
      console.log(`⚠️ Version conflict detected for ${event.entityType}:${event.entityId}`);
      
      // Broadcast conflict
      this.broadcast({
        type: 'sync_conflict_detected',
        conflict
      });

      this.emit('conflict:detected', conflict);
      return conflict;
    }

    // Check data conflicts (if timestamps are very close)
    const timeDiff = Math.abs(event.timestamp.getTime() - existingEntity.lastModified.getTime());
    if (timeDiff < 1000 && this.hasDataConflict(event.data, existingEntity.data)) {
      conflict.conflictType = 'data';
      this.conflicts.set(conflict.id, conflict);
      this.metrics.conflictsDetected++;
      
      console.log(`⚠️ Data conflict detected for ${event.entityType}:${event.entityId}`);
      
      // Broadcast conflict
      this.broadcast({
        type: 'sync_conflict_detected',
        conflict
      });

      this.emit('conflict:detected', conflict);
      return conflict;
    }

    return null;
  }

  /**
   * Check if there's a data conflict
   */
  private hasDataConflict(sourceData: any, targetData: any): boolean {
    // Simple comparison - in production, you'd want more sophisticated logic
    const sourceChecksum = this.calculateChecksum(sourceData);
    const targetChecksum = this.calculateChecksum(targetData);
    
    return sourceChecksum !== targetChecksum;
  }

  /**
   * Handle conflict
   */
  private async handleConflict(conflict: SyncConflict): Promise<void> {
    // Find applicable sync rule for conflict resolution
    const rules = Array.from(this.syncRules.values()).filter(rule => 
      rule.entityType === conflict.entityType &&
      (rule.sourceMarketplace === conflict.sourceMarketplace || 
       rule.targetMarketplaces.includes(conflict.targetMarketplace))
    );

    if (rules.length === 0) {
      console.log(`ℹ️ No conflict resolution rules found for ${conflict.id}`);
      return;
    }

    const rule = rules[0]; // Use first matching rule
    
    switch (rule.conflictResolution) {
      case 'source_wins':
        await this.resolveConflict(conflict.id, 'override', 'source');
        break;
      case 'target_wins':
        await this.resolveConflict(conflict.id, 'ignore', 'target');
        break;
      case 'latest_wins':
        await this.resolveConflict(conflict.id, 'override', 'latest');
        break;
      case 'merge':
        await this.resolveConflict(conflict.id, 'merge', 'auto');
        break;
      case 'manual':
        // Wait for manual resolution
        console.log(`⏳ Manual conflict resolution required for ${conflict.id}`);
        break;
    }
  }

  /**
   * Resolve conflict
   */
  public async resolveConflict(conflictId: string, resolution: string, resolvedBy: string = 'system'): Promise<void> {
    const conflict = this.conflicts.get(conflictId);
    if (!conflict) {
      throw new Error(`Conflict not found: ${conflictId}`);
    }

    try {
      let resolvedData: any;

      switch (resolution) {
        case 'override':
          resolvedData = conflict.sourceData;
          break;
        case 'ignore':
          resolvedData = conflict.targetData;
          break;
        case 'merge':
          resolvedData = this.mergeData(conflict.sourceData, conflict.targetData);
          break;
        default:
          throw new Error(`Unknown resolution type: ${resolution}`);
      }

      // Update entity
      const entityKey = `${conflict.entityType}:${conflict.entityId}:${conflict.targetMarketplace}`;
      const entity = this.entities.get(entityKey);
      
      if (entity) {
        entity.data = resolvedData;
        entity.version++;
        entity.lastModified = new Date();
        entity.checksum = this.calculateChecksum(resolvedData);
        
        this.entities.set(entityKey, entity);
      }

      // Mark conflict as resolved
      conflict.resolved = true;
      conflict.resolution = resolution as any;
      conflict.resolvedAt = new Date();
      conflict.resolvedBy = resolvedBy;

      this.metrics.conflictsResolved++;

      // Broadcast resolution
      this.broadcast({
        type: 'sync_conflict_resolved',
        conflictId,
        resolution,
        resolvedBy,
        resolvedData
      });

      console.log(`✅ Conflict ${conflictId} resolved using ${resolution}`);
      this.emit('conflict:resolved', { conflict, resolution, resolvedData });

    } catch (error) {
      console.error(`❌ Failed to resolve conflict ${conflictId}:`, error);
      throw error;
    }
  }

  /**
   * Handle conflict resolution message
   */
  private async handleConflictResolution(conflictId: string, resolution: any): Promise<void> {
    await this.resolveConflict(conflictId, resolution.type, resolution.resolvedBy || 'user');
  }

  /**
   * Merge conflicting data
   */
  private mergeData(sourceData: any, targetData: any): any {
    // Simple merge strategy - in production, you'd want more sophisticated logic
    return {
      ...targetData,
      ...sourceData,
      _mergedAt: new Date().toISOString(),
      _mergeSource: 'automatic'
    };
  }

  /**
   * Apply sync rules
   */
  private async applySyncRules(event: SyncEvent): Promise<SyncRule[]> {
    const applicableRules = Array.from(this.syncRules.values()).filter(rule => {
      if (!rule.enabled) return false;
      if (rule.entityType !== event.entityType) return false;
      
      // Check source marketplace
      if (rule.sourceMarketplace !== 'any' && rule.sourceMarketplace !== event.sourceMarketplace) {
        return false;
      }

      // Check conditions
      if (rule.conditions.length > 0) {
        return rule.conditions.every(condition => {
          return this.evaluateCondition(event.data, condition);
        });
      }

      return true;
    });

    return applicableRules.sort((a, b) => b.priority - a.priority);
  }

  /**
   * Evaluate sync rule condition
   */
  private evaluateCondition(data: any, condition: any): boolean {
    const fieldValue = this.getNestedValue(data, condition.field);
    
    switch (condition.operator) {
      case 'equals':
        return fieldValue === condition.value;
      case 'not_equals':
        return fieldValue !== condition.value;
      case 'contains':
        return typeof fieldValue === 'string' && fieldValue.includes(condition.value);
      case 'greater_than':
        return fieldValue > condition.value;
      case 'less_than':
        return fieldValue < condition.value;
      default:
        return false;
    }
  }

  /**
   * Get nested value from object
   */
  private getNestedValue(obj: any, path: string): any {
    return path.split('.').reduce((current, key) => current?.[key], obj);
  }

  /**
   * Create or update entity
   */
  private async createOrUpdateEntity(event: SyncEvent, existingEntity?: SyncEntity): Promise<SyncEntity> {
    const entity: SyncEntity = {
      id: event.entityId,
      type: event.entityType as any,
      marketplaceId: event.marketplaceId,
      data: event.data,
      version: existingEntity ? existingEntity.version + 1 : 1,
      lastModified: new Date(),
      checksum: this.calculateChecksum(event.data),
      metadata: {
        ...existingEntity?.metadata,
        lastSyncEvent: event.id,
        sourceMarketplace: event.sourceMarketplace
      }
    };

    console.log(`💾 ${existingEntity ? 'Updated' : 'Created'} entity: ${entity.type}:${entity.id}`);
    
    return entity;
  }

  /**
   * Delete entity
   */
  private async deleteEntity(event: SyncEvent): Promise<void> {
    const entityKey = `${event.entityType}:${event.entityId}:${event.marketplaceId}`;
    this.entities.delete(entityKey);
    
    console.log(`🗑️ Deleted entity: ${event.entityType}:${event.entityId}`);
  }

  /**
   * Sync entity
   */
  private async syncEntity(event: SyncEvent): Promise<void> {
    // This would trigger a full sync operation
    console.log(`🔄 Syncing entity: ${event.entityType}:${event.entityId}`);
  }

  /**
   * Propagate changes to other marketplaces
   */
  private async propagateChanges(event: SyncEvent, rules: SyncRule[]): Promise<void> {
    for (const rule of rules) {
      if (rule.direction === 'source_to_target' || rule.direction === 'bidirectional') {
        for (const targetMarketplace of rule.targetMarketplaces) {
          if (targetMarketplace !== event.sourceMarketplace) {
            await this.propagateToMarketplace(event, rule, targetMarketplace);
          }
        }
      }
    }
  }

  /**
   * Propagate to specific marketplace
   */
  private async propagateToMarketplace(event: SyncEvent, rule: SyncRule, targetMarketplace: string): Promise<void> {
    try {
      // Transform data if needed
      let transformedData = event.data;
      if (rule.transformations.length > 0) {
        transformedData = this.applyTransformations(event.data, rule.transformations);
      }

      // Create propagation event
      const propagationEvent: SyncEvent = {
        ...event,
        id: `prop_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
        marketplaceId: targetMarketplace,
        data: transformedData,
        timestamp: new Date()
      };

      // Add to queue for processing
      await this.addSyncEvent(propagationEvent);

      console.log(`🔄 Propagated to ${targetMarketplace}: ${event.entityType}:${event.entityId}`);

    } catch (error) {
      console.error(`❌ Failed to propagate to ${targetMarketplace}:`, error);
    }
  }

  /**
   * Apply data transformations
   */
  private applyTransformations(data: any, transformations: any[]): any {
    let result = { ...data };

    for (const transformation of transformations) {
      try {
        switch (transformation.transformation) {
          case 'map':
            result[transformation.field] = transformation.config.mapping[result[transformation.field]];
            break;
          case 'format':
            result[transformation.field] = this.formatValue(result[transformation.field], transformation.config);
            break;
          case 'calculate':
            result[transformation.field] = this.calculateValue(result, transformation.config);
            break;
          case 'default':
            if (!result[transformation.field]) {
              result[transformation.field] = transformation.config.value;
            }
            break;
        }
      } catch (error) {
        console.error(`❌ Transformation failed for field ${transformation.field}:`, error);
      }
    }

    return result;
  }

  /**
   * Format value
   */
  private formatValue(value: any, config: any): any {
    switch (config.type) {
      case 'date':
        return new Date(value).toISOString();
      case 'currency':
        return parseFloat(value).toFixed(2);
      case 'string':
        return String(value);
      case 'number':
        return Number(value);
      default:
        return value;
    }
  }

  /**
   * Calculate value
   */
  private calculateValue(data: any, config: any): any {
    // Simple expression evaluator - in production, use a proper library
    const expression = config.expression;
    
    try {
      // Replace field references with actual values
      let evalExpression = expression;
      const fieldMatches = expression.match(/\{([^}]+)\}/g);
      
      if (fieldMatches) {
        for (const match of fieldMatches) {
          const fieldName = match.slice(1, -1);
          const fieldValue = this.getNestedValue(data, fieldName);
          evalExpression = evalExpression.replace(match, fieldValue);
        }
      }

      return eval(evalExpression);
    } catch (error) {
      console.error('❌ Expression evaluation failed:', error);
      return null;
    }
  }

  /**
   * Calculate checksum
   */
  private calculateChecksum(data: any): string {
    const serialized = JSON.stringify(data, Object.keys(data).sort());
    return crypto.createHash('md5').update(serialized).digest('hex');
  }

  /**
   * Update processing time metrics
   */
  private updateProcessingTimeMetrics(processingTime: number): void {
    const total = this.metrics.processedEvents;
    this.metrics.averageProcessingTime = 
      ((this.metrics.averageProcessingTime * (total - 1)) + processingTime) / total;
  }

  /**
   * Start event processor
   */
  private startEventProcessor(): void {
    const interval = this.config.processingInterval || 1000;
    
    setInterval(async () => {
      if (this.processingQueue || this.eventQueue.length === 0) return;

      this.processingQueue = true;

      try {
        const batchSize = 10;
        const events = this.eventQueue.splice(0, batchSize);
        
        const promises = events.map(event => this.processSyncEvent(event));
        await Promise.allSettled(promises);
        
        this.metrics.queueSize = this.eventQueue.length;
        this.metrics.lastSyncTime = new Date();

      } catch (error) {
        console.error('❌ Event processor error:', error);
      } finally {
        this.processingQueue = false;
      }
    }, interval);
  }

  /**
   * Start metrics collector
   */
  private startMetricsCollector(): void {
    setInterval(() => {
      // Calculate throughput
      const now = Date.now();
      const windowSize = 60000; // 1 minute
      const recentEvents = this.metrics.processedEvents; // Simplified calculation
      
      this.metrics.throughputPerSecond = recentEvents / 60;

      // Broadcast metrics
      this.broadcast({
        type: 'sync_metrics',
        metrics: this.metrics,
        timestamp: new Date().toISOString()
      });

      this.emit('metrics:collected', this.metrics);
    }, 60000); // Every minute
  }

  /**
   * Start conflict resolver
   */
  private startConflictResolver(): void {
    const timeoutMs = this.config.conflictTimeoutMs || 300000; // 5 minutes
    
    setInterval(() => {
      const now = Date.now();
      
      for (const [conflictId, conflict] of this.conflicts.entries()) {
        if (!conflict.resolved && (now - conflict.detectedAt.getTime()) > timeoutMs) {
          // Auto-resolve using latest_wins strategy
          this.resolveConflict(conflictId, 'override', 'timeout');
        }
      }
    }, 60000); // Check every minute
  }

  /**
   * Add sync rule
   */
  public addSyncRule(rule: SyncRule): void {
    this.syncRules.set(rule.id, rule);
    console.log(`📋 Sync rule added: ${rule.id} (${rule.entityType})`);
    this.emit('rule:added', rule);
  }

  /**
   * Remove sync rule
   */
  public removeSyncRule(ruleId: string): void {
    this.syncRules.delete(ruleId);
    console.log(`📋 Sync rule removed: ${ruleId}`);
    this.emit('rule:removed', ruleId);
  }

  /**
   * Get sync metrics
   */
  public getMetrics(): SyncMetrics {
    return { ...this.metrics };
  }

  /**
   * Get entity
   */
  public getEntity(entityType: string, entityId: string, marketplaceId: string): SyncEntity | null {
    const key = `${entityType}:${entityId}:${marketplaceId}`;
    return this.entities.get(key) || null;
  }

  /**
   * Get conflicts
   */
  public getConflicts(resolved?: boolean): SyncConflict[] {
    const conflicts = Array.from(this.conflicts.values());
    
    if (resolved !== undefined) {
      return conflicts.filter(conflict => conflict.resolved === resolved);
    }
    
    return conflicts;
  }

  /**
   * Get sync rules
   */
  public getSyncRules(): SyncRule[] {
    return Array.from(this.syncRules.values());
  }

  /**
   * Get queue status
   */
  public getQueueStatus(): {
    size: number;
    processing: boolean;
    oldestEvent?: Date;
  } {
    return {
      size: this.eventQueue.length,
      processing: this.processingQueue,
      oldestEvent: this.eventQueue.length > 0 ? this.eventQueue[0].timestamp : undefined
    };
  }

  /**
   * Clear processed entities (cleanup)
   */
  public cleanup(olderThanHours: number = 24): void {
    const cutoffTime = Date.now() - (olderThanHours * 60 * 60 * 1000);
    let removedCount = 0;

    for (const [key, entity] of this.entities.entries()) {
      if (entity.lastModified.getTime() < cutoffTime) {
        this.entities.delete(key);
        removedCount++;
      }
    }

    // Clean up resolved conflicts
    for (const [conflictId, conflict] of this.conflicts.entries()) {
      if (conflict.resolved && conflict.resolvedAt && conflict.resolvedAt.getTime() < cutoffTime) {
        this.conflicts.delete(conflictId);
      }
    }

    console.log(`🧹 Cleaned up ${removedCount} old entities`);
  }

  /**
   * Shutdown sync engine
   */
  public async shutdown(): Promise<void> {
    console.log('🛑 Shutting down Real-time Sync Engine...');
    
    // Close WebSocket server
    this.wsServer.close();
    
    // Close all connections
    for (const ws of this.wsConnections.values()) {
      ws.close();
    }

    // Wait for queue to empty
    while (this.eventQueue.length > 0 && this.processingQueue) {
      await new Promise(resolve => setTimeout(resolve, 1000));
    }

    this.removeAllListeners();
    console.log('✅ Real-time Sync Engine shutdown complete');
  }
}

export default RealTimeSyncEngine;