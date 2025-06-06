/**
 * Cache Manager Service
 * Advanced multi-tier caching system with intelligent cache strategies
 */

import { EventEmitter } from 'events';

// Types
export interface CacheConfig {
  defaultTTL: number; // seconds
  maxSize: number; // bytes
  compressionEnabled: boolean;
  persistenceEnabled: boolean;
  distributedEnabled: boolean;
  strategies: CacheStrategy[];
}

export interface CacheStrategy {
  name: string;
  algorithm: 'LRU' | 'LFU' | 'FIFO' | 'LIFO' | 'ARC' | 'TTL' | 'ADAPTIVE';
  priority: number;
  conditions: CacheCondition[];
  ttl?: number;
  maxItems?: number;
}

export interface CacheCondition {
  type: 'PATTERN' | 'SIZE' | 'FREQUENCY' | 'TIME' | 'USER_TYPE' | 'CUSTOM';
  operator: 'EQUALS' | 'CONTAINS' | 'REGEX' | 'GT' | 'LT' | 'GTE' | 'LTE';
  value: any;
  weight: number;
}

export interface CacheEntry<T = any> {
  key: string;
  value: T;
  metadata: CacheMetadata;
  compressed: boolean;
  tags: string[];
}

export interface CacheMetadata {
  createdAt: Date;
  updatedAt: Date;
  expiresAt?: Date;
  accessCount: number;
  lastAccessed: Date;
  size: number;
  hits: number;
  misses: number;
  strategy: string;
  priority: number;
  source: string;
}

export interface CacheStats {
  totalEntries: number;
  totalSize: number;
  hitRate: number;
  missRate: number;
  evictionRate: number;
  averageResponseTime: number;
  memoryUsage: number;
  compressionRatio: number;
  strategyStats: Map<string, StrategyStats>;
  topKeys: TopKeyStats[];
}

export interface StrategyStats {
  name: string;
  entries: number;
  hits: number;
  misses: number;
  evictions: number;
  hitRate: number;
  averageSize: number;
}

export interface TopKeyStats {
  key: string;
  hits: number;
  size: number;
  strategy: string;
  lastAccessed: Date;
}

export interface CacheOperation {
  type: 'GET' | 'SET' | 'DELETE' | 'CLEAR' | 'EVICT';
  key: string;
  success: boolean;
  duration: number;
  size?: number;
  strategy?: string;
  timestamp: Date;
}

export interface CacheWarming {
  id: string;
  name: string;
  schedule: string; // cron expression
  keys: string[];
  dataSource: () => Promise<Map<string, any>>;
  enabled: boolean;
  lastRun?: Date;
  nextRun?: Date;
  success?: boolean;
}

export interface CacheInvalidation {
  strategy: 'TAG' | 'PATTERN' | 'TIME' | 'DEPENDENCY' | 'MANUAL';
  target: string;
  cascade: boolean;
  delay?: number;
}

export interface CacheTier {
  name: string;
  type: 'MEMORY' | 'DISK' | 'DISTRIBUTED' | 'CDN';
  priority: number;
  config: any;
  enabled: boolean;
  fallback?: string;
}

export interface CacheCluster {
  nodes: CacheNode[];
  loadBalancer: LoadBalancer;
  replication: ReplicationConfig;
  consistency: 'EVENTUAL' | 'STRONG' | 'WEAK';
}

export interface CacheNode {
  id: string;
  host: string;
  port: number;
  status: 'ACTIVE' | 'INACTIVE' | 'FAILED';
  load: number;
  capacity: number;
  latency: number;
}

export interface LoadBalancer {
  algorithm: 'ROUND_ROBIN' | 'LEAST_CONNECTIONS' | 'WEIGHTED' | 'HASH' | 'ADAPTIVE';
  healthCheck: boolean;
  failover: boolean;
}

export interface ReplicationConfig {
  factor: number;
  synchronous: boolean;
  backupNodes: number;
}

const defaultConfig: CacheConfig = {
  defaultTTL: 3600, // 1 hour
  maxSize: 1024 * 1024 * 1024, // 1GB
  compressionEnabled: true,
  persistenceEnabled: false,
  distributedEnabled: false,
  strategies: [
    {
      name: 'default',
      algorithm: 'LRU',
      priority: 1,
      conditions: [],
      ttl: 3600,
      maxItems: 10000
    }
  ]
};

export class CacheManager extends EventEmitter {
  private config: CacheConfig;
  private stores: Map<string, Map<string, CacheEntry>> = new Map();
  private strategies: Map<string, CacheStrategy> = new Map();
  private stats: CacheStats;
  private operations: CacheOperation[] = [];
  private warmingJobs: Map<string, CacheWarming> = new Map();
  private tiers: Map<string, CacheTier> = new Map();
  private cluster?: CacheCluster;

  constructor(config?: Partial<CacheConfig>) {
    super();
    this.config = { ...defaultConfig, ...config };
    this.initialize();
  }

  private initialize(): void {
    // Initialize strategies
    this.config.strategies.forEach(strategy => {
      this.strategies.set(strategy.name, strategy);
      this.stores.set(strategy.name, new Map());
    });

    // Initialize stats
    this.stats = {
      totalEntries: 0,
      totalSize: 0,
      hitRate: 0,
      missRate: 0,
      evictionRate: 0,
      averageResponseTime: 0,
      memoryUsage: 0,
      compressionRatio: 0,
      strategyStats: new Map(),
      topKeys: []
    };

    // Initialize cache tiers
    this.initializeTiers();

    // Start background tasks
    this.startBackgroundTasks();

    console.log('🚀 Cache Manager initialized with', this.strategies.size, 'strategies');
  }

  private initializeTiers(): void {
    // Memory tier (L1)
    this.tiers.set('memory', {
      name: 'memory',
      type: 'MEMORY',
      priority: 1,
      config: { maxSize: 512 * 1024 * 1024 }, // 512MB
      enabled: true
    });

    // Disk tier (L2)
    this.tiers.set('disk', {
      name: 'disk',
      type: 'DISK',
      priority: 2,
      config: { path: './cache', maxSize: 2 * 1024 * 1024 * 1024 }, // 2GB
      enabled: this.config.persistenceEnabled,
      fallback: 'memory'
    });

    // Distributed tier (L3)
    this.tiers.set('distributed', {
      name: 'distributed',
      type: 'DISTRIBUTED',
      priority: 3,
      config: { nodes: ['cache-1:6379', 'cache-2:6379'] },
      enabled: this.config.distributedEnabled,
      fallback: 'disk'
    });
  }

  private startBackgroundTasks(): void {
    // Cleanup expired entries every minute
    setInterval(() => this.cleanupExpired(), 60000);
    
    // Update stats every 5 seconds
    setInterval(() => this.updateStats(), 5000);
    
    // Process cache warming jobs
    setInterval(() => this.processCacheWarming(), 300000); // 5 minutes
  }

  // Core Cache Operations
  public async get<T>(key: string, strategy?: string): Promise<T | null> {
    const startTime = performance.now();
    
    try {
      const selectedStrategy = strategy || this.selectOptimalStrategy(key);
      const store = this.stores.get(selectedStrategy);
      
      if (!store) {
        this.recordOperation('GET', key, false, performance.now() - startTime);
        return null;
      }

      const entry = store.get(key);
      
      if (!entry) {
        // Try fallback tiers
        const fallbackResult = await this.getFallback(key);
        this.recordOperation('GET', key, fallbackResult !== null, performance.now() - startTime);
        return fallbackResult;
      }

      // Check expiration
      if (entry.metadata.expiresAt && entry.metadata.expiresAt < new Date()) {
        store.delete(key);
        this.recordOperation('GET', key, false, performance.now() - startTime);
        return null;
      }

      // Update access metadata
      entry.metadata.lastAccessed = new Date();
      entry.metadata.accessCount++;
      entry.metadata.hits++;

      const value = this.deserializeValue(entry);
      this.recordOperation('GET', key, true, performance.now() - startTime, entry.metadata.size, selectedStrategy);
      
      this.emit('cache:hit', { key, strategy: selectedStrategy, value });
      return value;

    } catch (error) {
      this.recordOperation('GET', key, false, performance.now() - startTime);
      this.emit('cache:error', { operation: 'GET', key, error });
      return null;
    }
  }

  public async set<T>(
    key: string, 
    value: T, 
    options?: {
      ttl?: number;
      strategy?: string;
      tags?: string[];
      priority?: number;
    }
  ): Promise<boolean> {
    const startTime = performance.now();
    
    try {
      const ttl = options?.ttl || this.config.defaultTTL;
      const strategy = options?.strategy || this.selectOptimalStrategy(key, value);
      const tags = options?.tags || [];
      const priority = options?.priority || 1;

      const store = this.stores.get(strategy);
      if (!store) {
        this.recordOperation('SET', key, false, performance.now() - startTime);
        return false;
      }

      const serializedValue = await this.serializeValue(value);
      const size = this.calculateSize(serializedValue);

      // Check if we need to evict entries
      await this.ensureSpace(strategy, size);

      const entry: CacheEntry<T> = {
        key,
        value,
        metadata: {
          createdAt: new Date(),
          updatedAt: new Date(),
          expiresAt: ttl > 0 ? new Date(Date.now() + ttl * 1000) : undefined,
          accessCount: 0,
          lastAccessed: new Date(),
          size,
          hits: 0,
          misses: 0,
          strategy,
          priority,
          source: 'application'
        },
        compressed: this.config.compressionEnabled && size > 1024,
        tags
      };

      store.set(key, entry);
      
      // Replicate to other tiers if needed
      await this.replicateToTiers(key, entry);

      this.recordOperation('SET', key, true, performance.now() - startTime, size, strategy);
      this.emit('cache:set', { key, strategy, size, ttl });
      
      return true;

    } catch (error) {
      this.recordOperation('SET', key, false, performance.now() - startTime);
      this.emit('cache:error', { operation: 'SET', key, error });
      return false;
    }
  }

  public async delete(key: string, strategy?: string): Promise<boolean> {
    const startTime = performance.now();
    
    try {
      let deleted = false;

      if (strategy) {
        const store = this.stores.get(strategy);
        if (store && store.has(key)) {
          store.delete(key);
          deleted = true;
        }
      } else {
        // Delete from all strategies
        for (const [strategyName, store] of this.stores) {
          if (store.has(key)) {
            store.delete(key);
            deleted = true;
          }
        }
      }

      // Delete from other tiers
      await this.deleteFromTiers(key);

      this.recordOperation('DELETE', key, deleted, performance.now() - startTime);
      this.emit('cache:delete', { key, strategy });
      
      return deleted;

    } catch (error) {
      this.recordOperation('DELETE', key, false, performance.now() - startTime);
      this.emit('cache:error', { operation: 'DELETE', key, error });
      return false;
    }
  }

  public async clear(strategy?: string): Promise<boolean> {
    try {
      if (strategy) {
        const store = this.stores.get(strategy);
        if (store) {
          store.clear();
        }
      } else {
        this.stores.forEach(store => store.clear());
      }

      this.emit('cache:clear', { strategy });
      return true;

    } catch (error) {
      this.emit('cache:error', { operation: 'CLEAR', error });
      return false;
    }
  }

  // Advanced Operations
  public async mget<T>(keys: string[], strategy?: string): Promise<Map<string, T | null>> {
    const results = new Map<string, T | null>();
    
    // Use Promise.all for parallel execution
    const promises = keys.map(async key => {
      const value = await this.get<T>(key, strategy);
      return { key, value };
    });

    const resolved = await Promise.all(promises);
    resolved.forEach(({ key, value }) => {
      results.set(key, value);
    });

    return results;
  }

  public async mset<T>(entries: Map<string, T>, options?: {
    ttl?: number;
    strategy?: string;
    tags?: string[];
  }): Promise<boolean> {
    try {
      const promises = Array.from(entries.entries()).map(([key, value]) =>
        this.set(key, value, options)
      );

      const results = await Promise.all(promises);
      return results.every(result => result);

    } catch (error) {
      this.emit('cache:error', { operation: 'MSET', error });
      return false;
    }
  }

  public async invalidateByTag(tag: string): Promise<number> {
    let invalidated = 0;

    for (const [strategyName, store] of this.stores) {
      const keysToDelete: string[] = [];
      
      for (const [key, entry] of store) {
        if (entry.tags.includes(tag)) {
          keysToDelete.push(key);
        }
      }

      keysToDelete.forEach(key => {
        store.delete(key);
        invalidated++;
      });
    }

    this.emit('cache:invalidate', { tag, count: invalidated });
    return invalidated;
  }

  public async invalidateByPattern(pattern: RegExp): Promise<number> {
    let invalidated = 0;

    for (const [strategyName, store] of this.stores) {
      const keysToDelete: string[] = [];
      
      for (const key of store.keys()) {
        if (pattern.test(key)) {
          keysToDelete.push(key);
        }
      }

      keysToDelete.forEach(key => {
        store.delete(key);
        invalidated++;
      });
    }

    this.emit('cache:invalidate', { pattern: pattern.source, count: invalidated });
    return invalidated;
  }

  // Strategy Selection
  private selectOptimalStrategy(key: string, value?: any): string {
    // Score each strategy based on conditions
    const scores = new Map<string, number>();

    for (const [name, strategy] of this.strategies) {
      let score = strategy.priority;

      for (const condition of strategy.conditions) {
        const conditionMet = this.evaluateCondition(condition, key, value);
        if (conditionMet) {
          score += condition.weight;
        }
      }

      scores.set(name, score);
    }

    // Return strategy with highest score
    const best = Array.from(scores.entries()).reduce((a, b) => a[1] > b[1] ? a : b);
    return best[0];
  }

  private evaluateCondition(condition: CacheCondition, key: string, value?: any): boolean {
    switch (condition.type) {
      case 'PATTERN':
        return new RegExp(condition.value).test(key);
      case 'SIZE':
        if (!value) return false;
        const size = this.calculateSize(value);
        return this.compareValues(size, condition.operator, condition.value);
      case 'FREQUENCY':
        // Mock frequency check - in real implementation would track access patterns
        return Math.random() > 0.5;
      case 'TIME':
        const hour = new Date().getHours();
        return this.compareValues(hour, condition.operator, condition.value);
      default:
        return false;
    }
  }

  private compareValues(actual: any, operator: string, expected: any): boolean {
    switch (operator) {
      case 'EQUALS': return actual === expected;
      case 'CONTAINS': return String(actual).includes(String(expected));
      case 'REGEX': return new RegExp(expected).test(String(actual));
      case 'GT': return actual > expected;
      case 'LT': return actual < expected;
      case 'GTE': return actual >= expected;
      case 'LTE': return actual <= expected;
      default: return false;
    }
  }

  // Eviction and Cleanup
  private async ensureSpace(strategy: string, requiredSize: number): Promise<void> {
    const store = this.stores.get(strategy);
    const strategyConfig = this.strategies.get(strategy);
    
    if (!store || !strategyConfig) return;

    const currentSize = this.calculateStoreSize(store);
    const maxSize = this.config.maxSize / this.strategies.size; // Distribute among strategies

    if (currentSize + requiredSize <= maxSize) return;

    // Evict entries based on strategy algorithm
    await this.evictEntries(strategy, currentSize + requiredSize - maxSize);
  }

  private async evictEntries(strategy: string, bytesToFree: number): Promise<void> {
    const store = this.stores.get(strategy);
    const strategyConfig = this.strategies.get(strategy);
    
    if (!store || !strategyConfig) return;

    const entries = Array.from(store.entries());
    let freedBytes = 0;

    // Sort entries based on eviction algorithm
    const sortedEntries = this.sortForEviction(entries, strategyConfig.algorithm);

    for (const [key, entry] of sortedEntries) {
      if (freedBytes >= bytesToFree) break;

      store.delete(key);
      freedBytes += entry.metadata.size;
      
      this.emit('cache:evict', { key, strategy, size: entry.metadata.size });
    }
  }

  private sortForEviction(
    entries: [string, CacheEntry][], 
    algorithm: CacheStrategy['algorithm']
  ): [string, CacheEntry][] {
    switch (algorithm) {
      case 'LRU':
        return entries.sort((a, b) => 
          a[1].metadata.lastAccessed.getTime() - b[1].metadata.lastAccessed.getTime()
        );
      case 'LFU':
        return entries.sort((a, b) => a[1].metadata.accessCount - b[1].metadata.accessCount);
      case 'FIFO':
        return entries.sort((a, b) => 
          a[1].metadata.createdAt.getTime() - b[1].metadata.createdAt.getTime()
        );
      case 'TTL':
        return entries.sort((a, b) => {
          const aExpiry = a[1].metadata.expiresAt?.getTime() || Infinity;
          const bExpiry = b[1].metadata.expiresAt?.getTime() || Infinity;
          return aExpiry - bExpiry;
        });
      default:
        return entries;
    }
  }

  private cleanupExpired(): void {
    for (const [strategyName, store] of this.stores) {
      const expiredKeys: string[] = [];
      const now = new Date();

      for (const [key, entry] of store) {
        if (entry.metadata.expiresAt && entry.metadata.expiresAt < now) {
          expiredKeys.push(key);
        }
      }

      expiredKeys.forEach(key => {
        store.delete(key);
        this.emit('cache:expire', { key, strategy: strategyName });
      });
    }
  }

  // Serialization and Compression
  private async serializeValue(value: any): Promise<any> {
    if (this.config.compressionEnabled && this.shouldCompress(value)) {
      // Mock compression - in real implementation would use actual compression
      return { __compressed: true, data: JSON.stringify(value) };
    }
    return value;
  }

  private deserializeValue(entry: CacheEntry): any {
    if (entry.compressed && entry.value.__compressed) {
      return JSON.parse(entry.value.data);
    }
    return entry.value;
  }

  private shouldCompress(value: any): boolean {
    const size = this.calculateSize(value);
    return size > 1024; // Compress if larger than 1KB
  }

  private calculateSize(value: any): number {
    // Rough size calculation
    return JSON.stringify(value).length * 2; // Assuming 2 bytes per character
  }

  private calculateStoreSize(store: Map<string, CacheEntry>): number {
    let total = 0;
    for (const entry of store.values()) {
      total += entry.metadata.size;
    }
    return total;
  }

  // Tier Management
  private async getFallback(key: string): Promise<any> {
    // Try disk tier, then distributed tier
    // Mock implementation - in real app would implement actual tier fallback
    return null;
  }

  private async replicateToTiers(key: string, entry: CacheEntry): Promise<void> {
    // Mock replication to other tiers
    // In real implementation would replicate to disk/distributed tiers
  }

  private async deleteFromTiers(key: string): Promise<void> {
    // Mock deletion from other tiers
  }

  // Cache Warming
  public addWarmingJob(warming: Omit<CacheWarming, 'id'>): string {
    const id = this.generateWarmingId();
    this.warmingJobs.set(id, { ...warming, id });
    return id;
  }

  private async processCacheWarming(): Promise<void> {
    for (const warming of this.warmingJobs.values()) {
      if (!warming.enabled) continue;
      
      const now = new Date();
      if (warming.nextRun && warming.nextRun > now) continue;

      try {
        const data = await warming.dataSource();
        const promises: Promise<boolean>[] = [];

        for (const [key, value] of data) {
          promises.push(this.set(key, value));
        }

        await Promise.all(promises);
        
        warming.lastRun = now;
        warming.nextRun = this.calculateNextRun(warming.schedule);
        warming.success = true;

        this.emit('cache:warming:completed', { id: warming.id, keys: data.size });

      } catch (error) {
        warming.success = false;
        this.emit('cache:warming:failed', { id: warming.id, error });
      }
    }
  }

  private calculateNextRun(schedule: string): Date {
    // Mock cron calculation - in real implementation would use cron parser
    return new Date(Date.now() + 60 * 60 * 1000); // 1 hour from now
  }

  // Statistics and Monitoring
  private updateStats(): void {
    let totalEntries = 0;
    let totalSize = 0;
    let totalHits = 0;
    let totalMisses = 0;

    const strategyStats = new Map<string, StrategyStats>();

    for (const [strategyName, store] of this.stores) {
      let strategyHits = 0;
      let strategyMisses = 0;
      let strategySize = 0;

      for (const entry of store.values()) {
        totalEntries++;
        totalSize += entry.metadata.size;
        strategySize += entry.metadata.size;
        strategyHits += entry.metadata.hits;
        strategyMisses += entry.metadata.misses;
      }

      totalHits += strategyHits;
      totalMisses += strategyMisses;

      strategyStats.set(strategyName, {
        name: strategyName,
        entries: store.size,
        hits: strategyHits,
        misses: strategyMisses,
        evictions: 0, // Would track in real implementation
        hitRate: strategyHits > 0 ? (strategyHits / (strategyHits + strategyMisses)) * 100 : 0,
        averageSize: store.size > 0 ? strategySize / store.size : 0
      });
    }

    this.stats = {
      totalEntries,
      totalSize,
      hitRate: totalHits > 0 ? (totalHits / (totalHits + totalMisses)) * 100 : 0,
      missRate: totalMisses > 0 ? (totalMisses / (totalHits + totalMisses)) * 100 : 0,
      evictionRate: 0, // Would calculate from operations
      averageResponseTime: this.calculateAverageResponseTime(),
      memoryUsage: (totalSize / this.config.maxSize) * 100,
      compressionRatio: this.calculateCompressionRatio(),
      strategyStats,
      topKeys: this.getTopKeys(10)
    };
  }

  private calculateAverageResponseTime(): number {
    if (this.operations.length === 0) return 0;
    
    const recentOps = this.operations.slice(-100); // Last 100 operations
    const totalTime = recentOps.reduce((sum, op) => sum + op.duration, 0);
    return totalTime / recentOps.length;
  }

  private calculateCompressionRatio(): number {
    // Mock compression ratio calculation
    return 0.7; // 70% compression
  }

  private getTopKeys(limit: number): TopKeyStats[] {
    const allEntries: Array<[string, CacheEntry]> = [];
    
    for (const store of this.stores.values()) {
      for (const [key, entry] of store) {
        allEntries.push([key, entry]);
      }
    }

    return allEntries
      .sort((a, b) => b[1].metadata.hits - a[1].metadata.hits)
      .slice(0, limit)
      .map(([key, entry]) => ({
        key,
        hits: entry.metadata.hits,
        size: entry.metadata.size,
        strategy: entry.metadata.strategy,
        lastAccessed: entry.metadata.lastAccessed
      }));
  }

  private recordOperation(
    type: CacheOperation['type'],
    key: string,
    success: boolean,
    duration: number,
    size?: number,
    strategy?: string
  ): void {
    const operation: CacheOperation = {
      type,
      key,
      success,
      duration,
      size,
      strategy,
      timestamp: new Date()
    };

    this.operations.push(operation);
    
    // Keep only last 1000 operations
    if (this.operations.length > 1000) {
      this.operations = this.operations.slice(-1000);
    }
  }

  // Public API
  public getStats(): CacheStats {
    return { ...this.stats };
  }

  public getOperations(limit: number = 100): CacheOperation[] {
    return this.operations.slice(-limit);
  }

  public addStrategy(strategy: CacheStrategy): void {
    this.strategies.set(strategy.name, strategy);
    this.stores.set(strategy.name, new Map());
    this.emit('strategy:added', strategy);
  }

  public removeStrategy(name: string): boolean {
    if (this.strategies.delete(name)) {
      this.stores.delete(name);
      this.emit('strategy:removed', { name });
      return true;
    }
    return false;
  }

  public getStrategies(): CacheStrategy[] {
    return Array.from(this.strategies.values());
  }

  private generateWarmingId(): string {
    return `warming_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  }

  public async benchmark(operations: number = 1000): Promise<{
    totalTime: number;
    operationsPerSecond: number;
    averageLatency: number;
    hitRate: number;
  }> {
    const startTime = performance.now();
    const keys = Array.from({ length: operations }, (_, i) => `benchmark_key_${i}`);
    
    // Set operations
    for (const key of keys) {
      await this.set(key, { data: `value_${key}`, timestamp: Date.now() });
    }
    
    // Get operations (mix of hits and misses)
    let hits = 0;
    for (let i = 0; i < operations; i++) {
      const key = keys[Math.floor(Math.random() * keys.length)];
      const result = await this.get(key);
      if (result !== null) hits++;
    }
    
    const endTime = performance.now();
    const totalTime = endTime - startTime;
    
    return {
      totalTime,
      operationsPerSecond: (operations * 2) / (totalTime / 1000), // Set + Get operations
      averageLatency: totalTime / (operations * 2),
      hitRate: (hits / operations) * 100
    };
  }
}

export default CacheManager; 