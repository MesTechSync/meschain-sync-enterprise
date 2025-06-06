import { EventEmitter } from 'events';

/**
 * Scalability Manager
 * Sistem ölçeklenebilirlik yöneticisi
 * G2: Enterprise Integration & Scalability - Component 2/6
 */

export interface ScalingRule {
  id: string;
  name: string;
  metric: 'CPU' | 'MEMORY' | 'DISK' | 'NETWORK' | 'REQUEST_COUNT' | 'RESPONSE_TIME' | 'QUEUE_SIZE' | 'ERROR_RATE';
  threshold: number;
  comparison: 'GREATER_THAN' | 'LESS_THAN' | 'EQUALS';
  duration: number; // seconds
  cooldown: number; // seconds
  action: ScalingAction;
  isActive: boolean;
  priority: number;
  conditions: ScalingCondition[];
}

export interface ScalingAction {
  type: 'SCALE_UP' | 'SCALE_DOWN' | 'SCALE_OUT' | 'SCALE_IN' | 'RESTART' | 'ALERT' | 'CUSTOM';
  parameters: {
    replicas?: number;
    cpuLimit?: string;
    memoryLimit?: string;
    targetValue?: number;
    percentage?: number;
    customScript?: string;
  };
  rollbackOnFailure: boolean;
}

export interface ScalingCondition {
  metric: string;
  operator: 'AND' | 'OR';
  threshold: number;
  comparison: 'GREATER_THAN' | 'LESS_THAN' | 'EQUALS';
}

export interface ResourcePool {
  id: string;
  name: string;
  type: 'COMPUTE' | 'STORAGE' | 'NETWORK' | 'DATABASE' | 'CACHE' | 'QUEUE';
  resources: Resource[];
  scaling: {
    minInstances: number;
    maxInstances: number;
    currentInstances: number;
    targetInstances: number;
    autoScaling: boolean;
    scalingPolicy: 'AGGRESSIVE' | 'MODERATE' | 'CONSERVATIVE';
  };
  healthCheck: HealthCheck;
  metrics: PoolMetrics;
}

export interface Resource {
  id: string;
  name: string;
  status: 'RUNNING' | 'STOPPED' | 'STARTING' | 'STOPPING' | 'ERROR' | 'MAINTENANCE';
  capacity: ResourceCapacity;
  utilization: ResourceUtilization;
  lastHealthCheck: Date;
  location: string;
  tags: Record<string, string>;
}

export interface ResourceCapacity {
  cpu: number; // cores
  memory: number; // GB
  storage: number; // GB
  network: number; // Mbps
  concurrent: number; // connections
}

export interface ResourceUtilization {
  cpu: number; // percentage
  memory: number; // percentage
  storage: number; // percentage
  network: number; // percentage
  concurrent: number; // current connections
  requestsPerSecond: number;
}

export interface HealthCheck {
  enabled: boolean;
  interval: number; // seconds
  timeout: number; // seconds
  retries: number;
  endpoint?: string;
  expectedStatus?: number;
  customCheck?: string;
}

export interface PoolMetrics {
  totalCapacity: ResourceCapacity;
  totalUtilization: ResourceUtilization;
  averageResponseTime: number;
  throughput: number;
  errorRate: number;
  availability: number;
  costPerHour: number;
  efficiency: number;
}

export interface ScalingEvent {
  id: string;
  timestamp: Date;
  ruleId: string;
  poolId: string;
  trigger: string;
  action: ScalingAction;
  beforeState: ResourceSnapshot;
  afterState: ResourceSnapshot;
  success: boolean;
  duration: number;
  error?: string;
  metrics: EventMetrics;
}

export interface ResourceSnapshot {
  instances: number;
  totalCapacity: ResourceCapacity;
  totalUtilization: ResourceUtilization;
  healthScore: number;
}

export interface EventMetrics {
  impactOnPerformance: number;
  costImpact: number;
  userImpact: number;
  stabilizationTime: number;
}

export interface ScalabilityMetrics {
  totalPools: number;
  totalResources: number;
  averageUtilization: number;
  scalingEventsToday: number;
  costOptimizationScore: number;
  systemEfficiency: number;
  predictedGrowth: number;
  recommendedActions: RecommendedAction[];
}

export interface RecommendedAction {
  type: 'OPTIMIZATION' | 'SCALING' | 'COST_REDUCTION' | 'PERFORMANCE' | 'MAINTENANCE';
  priority: 'HIGH' | 'MEDIUM' | 'LOW';
  title: string;
  description: string;
  expectedBenefit: string;
  estimatedCost: number;
  implementationTime: number; // hours
  resources: string[];
}

export interface LoadBalancer {
  id: string;
  name: string;
  algorithm: 'ROUND_ROBIN' | 'LEAST_CONNECTIONS' | 'WEIGHTED' | 'IP_HASH' | 'HEALTH_BASED';
  targets: LoadBalancerTarget[];
  healthCheck: HealthCheck;
  metrics: {
    totalRequests: number;
    successfulRequests: number;
    failedRequests: number;
    averageResponseTime: number;
    throughput: number;
  };
  isActive: boolean;
}

export interface LoadBalancerTarget {
  resourceId: string;
  weight: number;
  isHealthy: boolean;
  connections: number;
  responseTime: number;
}

export class ScalabilityManager extends EventEmitter {
  private pools: Map<string, ResourcePool> = new Map();
  private rules: Map<string, ScalingRule> = new Map();
  private events: ScalingEvent[] = [];
  private loadBalancers: Map<string, LoadBalancer> = new Map();
  private metrics: ScalabilityMetrics;
  private monitoringInterval: NodeJS.Timeout | null = null;
  private scalingInProgress: Set<string> = new Set();

  constructor() {
    super();
    this.metrics = this.initializeMetrics();
    this.startMonitoring();
    this.setupPredictiveAnalysis();
  }

  /**
   * Kaynak havuzu oluştur
   */
  async createResourcePool(pool: Omit<ResourcePool, 'id' | 'metrics'>): Promise<string> {
    try {
      const id = this.generateId();
      const newPool: ResourcePool = {
        ...pool,
        id,
        metrics: this.calculatePoolMetrics(pool.resources)
      };

      this.pools.set(id, newPool);
      this.updateGlobalMetrics();
      this.emit('poolCreated', newPool);

      console.log(`✅ Resource pool created: ${pool.name} (${pool.type})`);
      return id;
    } catch (error) {
      console.error('❌ Error creating resource pool:', error);
      throw error;
    }
  }

  /**
   * Kaynak ekle
   */
  async addResource(poolId: string, resource: Omit<Resource, 'id' | 'lastHealthCheck'>): Promise<string> {
    try {
      const pool = this.pools.get(poolId);
      if (!pool) {
        throw new Error('Resource pool not found');
      }

      const id = this.generateId();
      const newResource: Resource = {
        ...resource,
        id,
        lastHealthCheck: new Date()
      };

      pool.resources.push(newResource);
      pool.scaling.currentInstances = pool.resources.length;
      pool.metrics = this.calculatePoolMetrics(pool.resources);

      this.updateGlobalMetrics();
      this.emit('resourceAdded', { poolId, resource: newResource });

      console.log(`✅ Resource added to pool: ${resource.name}`);
      return id;
    } catch (error) {
      console.error('❌ Error adding resource:', error);
      throw error;
    }
  }

  /**
   * Ölçeklendirme kuralı ekle
   */
  async addScalingRule(rule: Omit<ScalingRule, 'id'>): Promise<string> {
    try {
      const id = this.generateId();
      const newRule: ScalingRule = {
        ...rule,
        id
      };

      this.rules.set(id, newRule);
      this.emit('scalingRuleAdded', newRule);

      console.log(`✅ Scaling rule added: ${rule.name}`);
      return id;
    } catch (error) {
      console.error('❌ Error adding scaling rule:', error);
      throw error;
    }
  }

  /**
   * Load balancer oluştur
   */
  async createLoadBalancer(loadBalancer: Omit<LoadBalancer, 'id' | 'metrics'>): Promise<string> {
    try {
      const id = this.generateId();
      const newLoadBalancer: LoadBalancer = {
        ...loadBalancer,
        id,
        metrics: {
          totalRequests: 0,
          successfulRequests: 0,
          failedRequests: 0,
          averageResponseTime: 0,
          throughput: 0
        }
      };

      this.loadBalancers.set(id, newLoadBalancer);
      this.emit('loadBalancerCreated', newLoadBalancer);

      console.log(`✅ Load balancer created: ${loadBalancer.name}`);
      return id;
    } catch (error) {
      console.error('❌ Error creating load balancer:', error);
      throw error;
    }
  }

  /**
   * Manuel ölçeklendirme
   */
  async scalePool(poolId: string, targetInstances: number, reason: string = 'Manual scaling'): Promise<void> {
    try {
      const pool = this.pools.get(poolId);
      if (!pool) {
        throw new Error('Resource pool not found');
      }

      if (this.scalingInProgress.has(poolId)) {
        throw new Error('Scaling operation already in progress for this pool');
      }

      this.scalingInProgress.add(poolId);
      
      const beforeState = this.captureResourceSnapshot(pool);
      const currentInstances = pool.scaling.currentInstances;

      console.log(`🔄 Scaling pool ${pool.name} from ${currentInstances} to ${targetInstances} instances`);

      if (targetInstances > currentInstances) {
        await this.scaleOut(pool, targetInstances - currentInstances);
      } else if (targetInstances < currentInstances) {
        await this.scaleIn(pool, currentInstances - targetInstances);
      }

      const afterState = this.captureResourceSnapshot(pool);
      
      const event: ScalingEvent = {
        id: this.generateId(),
        timestamp: new Date(),
        ruleId: 'manual',
        poolId,
        trigger: reason,
        action: {
          type: targetInstances > currentInstances ? 'SCALE_OUT' : 'SCALE_IN',
          parameters: { replicas: targetInstances },
          rollbackOnFailure: false
        },
        beforeState,
        afterState,
        success: true,
        duration: 0,
        metrics: this.calculateEventMetrics(beforeState, afterState)
      };

      this.events.push(event);
      this.emit('scalingCompleted', event);

      console.log(`✅ Pool scaling completed: ${pool.name}`);
    } catch (error) {
      console.error('❌ Error scaling pool:', error);
      throw error;
    } finally {
      this.scalingInProgress.delete(poolId);
    }
  }

  /**
   * Otomatik ölçeklendirme kontrolü
   */
  private async checkAutoScaling(): Promise<void> {
    for (const rule of this.rules.values()) {
      if (!rule.isActive) continue;

      try {
        await this.evaluateScalingRule(rule);
      } catch (error) {
        console.error(`Error evaluating scaling rule ${rule.name}:`, error);
      }
    }
  }

  /**
   * Ölçeklendirme kuralını değerlendir
   */
  private async evaluateScalingRule(rule: ScalingRule): Promise<void> {
    const pools = Array.from(this.pools.values());
    
    for (const pool of pools) {
      if (!pool.scaling.autoScaling) continue;
      if (this.scalingInProgress.has(pool.id)) continue;

      const metricValue = this.getMetricValue(pool, rule.metric);
      const conditionMet = this.evaluateCondition(metricValue, rule.threshold, rule.comparison);

      if (conditionMet && this.checkAdditionalConditions(pool, rule.conditions)) {
        // Cooldown kontrolü
        const lastEvent = this.getLastScalingEvent(pool.id);
        if (lastEvent && this.isInCooldown(lastEvent, rule.cooldown)) {
          continue;
        }

        await this.executeScalingAction(pool, rule);
      }
    }
  }

  /**
   * Ölçeklendirme işlemini gerçekleştir
   */
  private async executeScalingAction(pool: ResourcePool, rule: ScalingRule): Promise<void> {
    try {
      this.scalingInProgress.add(pool.id);
      
      const beforeState = this.captureResourceSnapshot(pool);
      
      console.log(`🔄 Auto-scaling triggered for pool: ${pool.name} (Rule: ${rule.name})`);

      switch (rule.action.type) {
        case 'SCALE_OUT':
          await this.scaleOut(pool, rule.action.parameters.replicas || 1);
          break;
        case 'SCALE_IN':
          await this.scaleIn(pool, rule.action.parameters.replicas || 1);
          break;
        case 'SCALE_UP':
          await this.scaleUp(pool, rule.action.parameters);
          break;
        case 'SCALE_DOWN':
          await this.scaleDown(pool, rule.action.parameters);
          break;
        case 'RESTART':
          await this.restartPool(pool);
          break;
        case 'ALERT':
          this.sendScalingAlert(pool, rule);
          break;
      }

      const afterState = this.captureResourceSnapshot(pool);
      
      const event: ScalingEvent = {
        id: this.generateId(),
        timestamp: new Date(),
        ruleId: rule.id,
        poolId: pool.id,
        trigger: `Auto-scaling rule: ${rule.name}`,
        action: rule.action,
        beforeState,
        afterState,
        success: true,
        duration: 0,
        metrics: this.calculateEventMetrics(beforeState, afterState)
      };

      this.events.push(event);
      this.emit('autoScalingCompleted', event);

      console.log(`✅ Auto-scaling completed for pool: ${pool.name}`);
    } catch (error) {
      console.error(`❌ Auto-scaling failed for pool: ${pool.name}`, error);
      
      const event: ScalingEvent = {
        id: this.generateId(),
        timestamp: new Date(),
        ruleId: rule.id,
        poolId: pool.id,
        trigger: `Auto-scaling rule: ${rule.name}`,
        action: rule.action,
        beforeState: this.captureResourceSnapshot(pool),
        afterState: this.captureResourceSnapshot(pool),
        success: false,
        duration: 0,
        error: error.message,
        metrics: {
          impactOnPerformance: 0,
          costImpact: 0,
          userImpact: 0,
          stabilizationTime: 0
        }
      };

      this.events.push(event);
      this.emit('autoScalingFailed', event);
    } finally {
      this.scalingInProgress.delete(pool.id);
    }
  }

  /**
   * Scale Out - yeni instance'lar ekle
   */
  private async scaleOut(pool: ResourcePool, count: number): Promise<void> {
    for (let i = 0; i < count; i++) {
      if (pool.resources.length >= pool.scaling.maxInstances) {
        console.warn(`Maximum instances reached for pool: ${pool.name}`);
        break;
      }

      const newResource: Resource = {
        id: this.generateId(),
        name: `${pool.name}-instance-${pool.resources.length + 1}`,
        status: 'STARTING',
        capacity: this.getDefaultCapacity(pool.type),
        utilization: {
          cpu: 0,
          memory: 0,
          storage: 0,
          network: 0,
          concurrent: 0,
          requestsPerSecond: 0
        },
        lastHealthCheck: new Date(),
        location: 'default',
        tags: {}
      };

      pool.resources.push(newResource);
      
      // Simulate startup time
      setTimeout(() => {
        newResource.status = 'RUNNING';
        this.emit('resourceStarted', { poolId: pool.id, resourceId: newResource.id });
      }, 2000);
    }

    pool.scaling.currentInstances = pool.resources.length;
    pool.metrics = this.calculatePoolMetrics(pool.resources);
  }

  /**
   * Scale In - instance'ları kaldır
   */
  private async scaleIn(pool: ResourcePool, count: number): Promise<void> {
    const toRemove = Math.min(count, pool.resources.length - pool.scaling.minInstances);
    
    for (let i = 0; i < toRemove; i++) {
      const resource = pool.resources.pop();
      if (resource) {
        resource.status = 'STOPPING';
        
        // Simulate shutdown time
        setTimeout(() => {
          this.emit('resourceStopped', { poolId: pool.id, resourceId: resource.id });
        }, 1000);
      }
    }

    pool.scaling.currentInstances = pool.resources.length;
    pool.metrics = this.calculatePoolMetrics(pool.resources);
  }

  /**
   * Scale Up - mevcut instance'ların kapasitesini artır
   */
  private async scaleUp(pool: ResourcePool, parameters: any): Promise<void> {
    const percentage = parameters.percentage || 20;
    
    for (const resource of pool.resources) {
      resource.capacity.cpu *= (1 + percentage / 100);
      resource.capacity.memory *= (1 + percentage / 100);
    }

    pool.metrics = this.calculatePoolMetrics(pool.resources);
  }

  /**
   * Scale Down - mevcut instance'ların kapasitesini azalt
   */
  private async scaleDown(pool: ResourcePool, parameters: any): Promise<void> {
    const percentage = parameters.percentage || 20;
    
    for (const resource of pool.resources) {
      resource.capacity.cpu *= (1 - percentage / 100);
      resource.capacity.memory *= (1 - percentage / 100);
    }

    pool.metrics = this.calculatePoolMetrics(pool.resources);
  }

  /**
   * Pool'u yeniden başlat
   */
  private async restartPool(pool: ResourcePool): Promise<void> {
    console.log(`🔄 Restarting pool: ${pool.name}`);
    
    for (const resource of pool.resources) {
      resource.status = 'STOPPING';
      
      // Simulate restart
      setTimeout(() => {
        resource.status = 'RUNNING';
        resource.lastHealthCheck = new Date();
      }, 3000);
    }
  }

  /**
   * Metrik değeri al
   */
  private getMetricValue(pool: ResourcePool, metric: string): number {
    switch (metric) {
      case 'CPU':
        return pool.metrics.totalUtilization.cpu;
      case 'MEMORY':
        return pool.metrics.totalUtilization.memory;
      case 'NETWORK':
        return pool.metrics.totalUtilization.network;
      case 'RESPONSE_TIME':
        return pool.metrics.averageResponseTime;
      case 'ERROR_RATE':
        return pool.metrics.errorRate;
      default:
        return 0;
    }
  }

  /**
   * Koşulu değerlendir
   */
  private evaluateCondition(value: number, threshold: number, comparison: string): boolean {
    switch (comparison) {
      case 'GREATER_THAN':
        return value > threshold;
      case 'LESS_THAN':
        return value < threshold;
      case 'EQUALS':
        return value === threshold;
      default:
        return false;
    }
  }

  /**
   * Ek koşulları kontrol et
   */
  private checkAdditionalConditions(pool: ResourcePool, conditions: ScalingCondition[]): boolean {
    if (conditions.length === 0) return true;

    return conditions.every(condition => {
      const value = this.getMetricValue(pool, condition.metric);
      return this.evaluateCondition(value, condition.threshold, condition.comparison);
    });
  }

  /**
   * Son ölçeklendirme olayını al
   */
  private getLastScalingEvent(poolId: string): ScalingEvent | undefined {
    return this.events
      .filter(event => event.poolId === poolId)
      .sort((a, b) => b.timestamp.getTime() - a.timestamp.getTime())[0];
  }

  /**
   * Cooldown durumunu kontrol et
   */
  private isInCooldown(lastEvent: ScalingEvent, cooldownSeconds: number): boolean {
    const now = new Date();
    const timeDiff = (now.getTime() - lastEvent.timestamp.getTime()) / 1000;
    return timeDiff < cooldownSeconds;
  }

  /**
   * Kaynak snapshot'ı al
   */
  private captureResourceSnapshot(pool: ResourcePool): ResourceSnapshot {
    return {
      instances: pool.scaling.currentInstances,
      totalCapacity: { ...pool.metrics.totalCapacity },
      totalUtilization: { ...pool.metrics.totalUtilization },
      healthScore: pool.metrics.availability
    };
  }

  /**
   * Olay metriklerini hesapla
   */
  private calculateEventMetrics(before: ResourceSnapshot, after: ResourceSnapshot): EventMetrics {
    return {
      impactOnPerformance: Math.abs(after.totalUtilization.cpu - before.totalUtilization.cpu),
      costImpact: (after.instances - before.instances) * 10, // Basit maliyet hesabı
      userImpact: Math.abs(after.healthScore - before.healthScore),
      stabilizationTime: 30 // seconds
    };
  }

  /**
   * Pool metriklerini hesapla
   */
  private calculatePoolMetrics(resources: Resource[]): PoolMetrics {
    if (resources.length === 0) {
      return {
        totalCapacity: { cpu: 0, memory: 0, storage: 0, network: 0, concurrent: 0 },
        totalUtilization: { cpu: 0, memory: 0, storage: 0, network: 0, concurrent: 0, requestsPerSecond: 0 },
        averageResponseTime: 0,
        throughput: 0,
        errorRate: 0,
        availability: 100,
        costPerHour: 0,
        efficiency: 100
      };
    }

    const totalCapacity = resources.reduce((sum, resource) => ({
      cpu: sum.cpu + resource.capacity.cpu,
      memory: sum.memory + resource.capacity.memory,
      storage: sum.storage + resource.capacity.storage,
      network: sum.network + resource.capacity.network,
      concurrent: sum.concurrent + resource.capacity.concurrent
    }), { cpu: 0, memory: 0, storage: 0, network: 0, concurrent: 0 });

    const avgUtilization = resources.reduce((sum, resource) => ({
      cpu: sum.cpu + resource.utilization.cpu,
      memory: sum.memory + resource.utilization.memory,
      storage: sum.storage + resource.utilization.storage,
      network: sum.network + resource.utilization.network,
      concurrent: sum.concurrent + resource.utilization.concurrent,
      requestsPerSecond: sum.requestsPerSecond + resource.utilization.requestsPerSecond
    }), { cpu: 0, memory: 0, storage: 0, network: 0, concurrent: 0, requestsPerSecond: 0 });

    const resourceCount = resources.length;
    const totalUtilization = {
      cpu: avgUtilization.cpu / resourceCount,
      memory: avgUtilization.memory / resourceCount,
      storage: avgUtilization.storage / resourceCount,
      network: avgUtilization.network / resourceCount,
      concurrent: avgUtilization.concurrent / resourceCount,
      requestsPerSecond: avgUtilization.requestsPerSecond
    };

    const healthyResources = resources.filter(r => r.status === 'RUNNING').length;
    const availability = (healthyResources / resourceCount) * 100;

    return {
      totalCapacity,
      totalUtilization,
      averageResponseTime: Math.random() * 100 + 50, // Mock
      throughput: totalUtilization.requestsPerSecond,
      errorRate: Math.random() * 5, // Mock
      availability,
      costPerHour: resourceCount * 0.5, // Mock
      efficiency: Math.max(0, 100 - totalUtilization.cpu) // Efficiency based on CPU usage
    };
  }

  /**
   * Varsayılan kapasite al
   */
  private getDefaultCapacity(type: string): ResourceCapacity {
    const capacities = {
      COMPUTE: { cpu: 2, memory: 4, storage: 20, network: 100, concurrent: 100 },
      STORAGE: { cpu: 1, memory: 2, storage: 100, network: 50, concurrent: 50 },
      NETWORK: { cpu: 1, memory: 1, storage: 10, network: 1000, concurrent: 1000 },
      DATABASE: { cpu: 4, memory: 8, storage: 50, network: 200, concurrent: 200 },
      CACHE: { cpu: 2, memory: 8, storage: 5, network: 500, concurrent: 500 },
      QUEUE: { cpu: 1, memory: 2, storage: 10, network: 100, concurrent: 1000 }
    };

    return capacities[type] || capacities.COMPUTE;
  }

  /**
   * Ölçeklendirme uyarısı gönder
   */
  private sendScalingAlert(pool: ResourcePool, rule: ScalingRule): void {
    const alert = {
      title: `Scaling Alert: ${pool.name}`,
      message: `Pool ${pool.name} requires attention. Rule: ${rule.name}`,
      severity: 'WARNING',
      timestamp: new Date(),
      poolId: pool.id,
      ruleId: rule.id
    };

    this.emit('scalingAlert', alert);
    console.log(`🚨 Scaling alert sent for pool: ${pool.name}`);
  }

  /**
   * İzlemeyi başlat
   */
  private startMonitoring(): void {
    this.monitoringInterval = setInterval(() => {
      this.updateResourceUtilization();
      this.checkAutoScaling();
      this.performHealthChecks();
      this.updateGlobalMetrics();
    }, 30000); // Her 30 saniye
  }

  /**
   * Kaynak kullanımını güncelle
   */
  private updateResourceUtilization(): void {
    for (const pool of this.pools.values()) {
      pool.resources.forEach(resource => {
        if (resource.status === 'RUNNING') {
          // Mock utilization updates
          resource.utilization.cpu = Math.max(0, Math.min(100, resource.utilization.cpu + (Math.random() - 0.5) * 10));
          resource.utilization.memory = Math.max(0, Math.min(100, resource.utilization.memory + (Math.random() - 0.5) * 8));
          resource.utilization.network = Math.max(0, Math.min(100, resource.utilization.network + (Math.random() - 0.5) * 15));
          resource.utilization.requestsPerSecond = Math.max(0, resource.utilization.requestsPerSecond + (Math.random() - 0.5) * 20);
        }
      });

      pool.metrics = this.calculatePoolMetrics(pool.resources);
    }
  }

  /**
   * Sağlık kontrollerini gerçekleştir
   */
  private performHealthChecks(): void {
    for (const pool of this.pools.values()) {
      if (!pool.healthCheck.enabled) continue;

      pool.resources.forEach(resource => {
        // Mock health check
        const isHealthy = Math.random() > 0.05; // %95 healthy probability
        
        if (!isHealthy && resource.status === 'RUNNING') {
          resource.status = 'ERROR';
          this.emit('resourceUnhealthy', { poolId: pool.id, resourceId: resource.id });
        } else if (isHealthy && resource.status === 'ERROR') {
          resource.status = 'RUNNING';
          this.emit('resourceRecovered', { poolId: pool.id, resourceId: resource.id });
        }

        resource.lastHealthCheck = new Date();
      });
    }
  }

  /**
   * Global metrikleri güncelle
   */
  private updateGlobalMetrics(): void {
    const pools = Array.from(this.pools.values());
    const totalResources = pools.reduce((sum, pool) => sum + pool.resources.length, 0);
    
    const avgUtilization = pools.length > 0 
      ? pools.reduce((sum, pool) => sum + pool.metrics.totalUtilization.cpu, 0) / pools.length
      : 0;

    const todayEvents = this.events.filter(event => {
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      return event.timestamp >= today;
    }).length;

    this.metrics = {
      totalPools: pools.length,
      totalResources,
      averageUtilization: avgUtilization,
      scalingEventsToday: todayEvents,
      costOptimizationScore: this.calculateCostOptimizationScore(),
      systemEfficiency: this.calculateSystemEfficiency(),
      predictedGrowth: this.calculatePredictedGrowth(),
      recommendedActions: this.generateRecommendedActions()
    };

    this.emit('metricsUpdated', this.metrics);
  }

  /**
   * Maliyet optimizasyon skorunu hesapla
   */
  private calculateCostOptimizationScore(): number {
    const pools = Array.from(this.pools.values());
    if (pools.length === 0) return 100;

    const totalEfficiency = pools.reduce((sum, pool) => sum + pool.metrics.efficiency, 0);
    return totalEfficiency / pools.length;
  }

  /**
   * Sistem verimliliğini hesapla
   */
  private calculateSystemEfficiency(): number {
    const pools = Array.from(this.pools.values());
    if (pools.length === 0) return 100;

    const totalAvailability = pools.reduce((sum, pool) => sum + pool.metrics.availability, 0);
    return totalAvailability / pools.length;
  }

  /**
   * Tahmin edilen büyümeyi hesapla
   */
  private calculatePredictedGrowth(): number {
    // Mock implementation - gerçekte makine öğrenmesi kullanılabilir
    const recentEvents = this.events.slice(-10);
    const scaleOutEvents = recentEvents.filter(e => e.action.type === 'SCALE_OUT').length;
    return scaleOutEvents * 10; // Simple growth prediction
  }

  /**
   * Önerilen işlemleri oluştur
   */
  private generateRecommendedActions(): RecommendedAction[] {
    const actions: RecommendedAction[] = [];
    
    for (const pool of this.pools.values()) {
      if (pool.metrics.totalUtilization.cpu < 30) {
        actions.push({
          type: 'COST_REDUCTION',
          priority: 'MEDIUM',
          title: `Scale down ${pool.name}`,
          description: `Pool ${pool.name} has low CPU utilization (${pool.metrics.totalUtilization.cpu.toFixed(1)}%)`,
          expectedBenefit: `Save ~$${(pool.resources.length * 0.1).toFixed(2)}/hour`,
          estimatedCost: 0,
          implementationTime: 0.5,
          resources: [pool.id]
        });
      }

      if (pool.metrics.averageResponseTime > 2000) {
        actions.push({
          type: 'PERFORMANCE',
          priority: 'HIGH',
          title: `Optimize ${pool.name} performance`,
          description: `Pool ${pool.name} has high response time (${pool.metrics.averageResponseTime.toFixed(0)}ms)`,
          expectedBenefit: 'Improved user experience',
          estimatedCost: 50,
          implementationTime: 2,
          resources: [pool.id]
        });
      }
    }

    return actions.sort((a, b) => {
      const priorityOrder = { HIGH: 3, MEDIUM: 2, LOW: 1 };
      return priorityOrder[b.priority] - priorityOrder[a.priority];
    });
  }

  /**
   * Tahmine dayalı analiz kurulumu
   */
  private setupPredictiveAnalysis(): void {
    setInterval(() => {
      this.runPredictiveAnalysis();
    }, 300000); // Her 5 dakika
  }

  /**
   * Tahmine dayalı analiz çalıştır
   */
  private runPredictiveAnalysis(): void {
    for (const pool of this.pools.values()) {
      if (!pool.scaling.autoScaling) continue;

      const prediction = this.predictResourceNeed(pool);
      
      if (prediction.confidence > 0.8) {
        console.log(`🔮 Predictive analysis for ${pool.name}: ${prediction.action} (${(prediction.confidence * 100).toFixed(1)}% confidence)`);
        
        if (prediction.action === 'SCALE_OUT' && pool.resources.length < pool.scaling.maxInstances) {
          this.emit('predictiveScalingRecommendation', {
            poolId: pool.id,
            action: prediction.action,
            confidence: prediction.confidence,
            reason: prediction.reason
          });
        }
      }
    }
  }

  /**
   * Kaynak ihtiyacını tahmin et
   */
  private predictResourceNeed(pool: ResourcePool): { action: string; confidence: number; reason: string } {
    // Mock predictive analysis
    const recentUtilization = pool.metrics.totalUtilization.cpu;
    const trend = Math.random() - 0.5; // Random trend for demo
    
    if (recentUtilization > 70 && trend > 0.2) {
      return {
        action: 'SCALE_OUT',
        confidence: 0.85,
        reason: 'High CPU utilization with upward trend'
      };
    } else if (recentUtilization < 30 && trend < -0.2) {
      return {
        action: 'SCALE_IN',
        confidence: 0.75,
        reason: 'Low CPU utilization with downward trend'
      };
    }

    return {
      action: 'MAINTAIN',
      confidence: 0.6,
      reason: 'Stable resource utilization'
    };
  }

  /**
   * Metrikleri başlat
   */
  private initializeMetrics(): ScalabilityMetrics {
    return {
      totalPools: 0,
      totalResources: 0,
      averageUtilization: 0,
      scalingEventsToday: 0,
      costOptimizationScore: 100,
      systemEfficiency: 100,
      predictedGrowth: 0,
      recommendedActions: []
    };
  }

  /**
   * ID oluştur
   */
  private generateId(): string {
    return 'scale_' + Math.random().toString(36).substr(2, 9);
  }

  // Public getter methods
  getPools(): ResourcePool[] {
    return Array.from(this.pools.values());
  }

  getPool(id: string): ResourcePool | undefined {
    return this.pools.get(id);
  }

  getRules(): ScalingRule[] {
    return Array.from(this.rules.values());
  }

  getRule(id: string): ScalingRule | undefined {
    return this.rules.get(id);
  }

  getEvents(): ScalingEvent[] {
    return [...this.events];
  }

  getLoadBalancers(): LoadBalancer[] {
    return Array.from(this.loadBalancers.values());
  }

  getMetrics(): ScalabilityMetrics {
    return { ...this.metrics };
  }

  /**
   * Kaynakları temizle
   */
  dispose(): void {
    if (this.monitoringInterval) {
      clearInterval(this.monitoringInterval);
      this.monitoringInterval = null;
    }

    this.pools.clear();
    this.rules.clear();
    this.events.length = 0;
    this.loadBalancers.clear();
    this.scalingInProgress.clear();
    this.removeAllListeners();

    console.log('🧹 ScalabilityManager disposed');
  }
}

export default ScalabilityManager;