import { EventEmitter } from 'events';
import { Logger } from '../core/Logger';

/**
 * Performance Monitoring System
 * Comprehensive system performance tracking with real-time metrics,
 * health monitoring, alerting, and optimization recommendations
 */

export interface PerformanceMetric {
  id: string;
  name: string;
  category: 'system' | 'application' | 'database' | 'network' | 'business' | 'user_experience';
  value: number;
  unit: string;
  timestamp: Date;
  source: string;
  tags: Record<string, string>;
  aggregationType?: 'avg' | 'sum' | 'max' | 'min' | 'count';
}

export interface SystemHealth {
  overall: 'healthy' | 'warning' | 'critical' | 'unknown';
  score: number; // 0-100
  components: {
    api: ComponentHealth;
    database: ComponentHealth;
    cache: ComponentHealth;
    messageQueue: ComponentHealth;
    storage: ComponentHealth;
    network: ComponentHealth;
  };
  lastUpdated: Date;
}

export interface ComponentHealth {
  status: 'healthy' | 'warning' | 'critical' | 'down';
  responseTime: number;
  uptime: number;
  errorRate: number;
  throughput: number;
  details: {
    memory: number;
    cpu: number;
    connections: number;
    errors: string[];
  };
}

export interface PerformanceAlert {
  id: string;
  type: 'threshold' | 'anomaly' | 'trend' | 'health';
  severity: 'info' | 'warning' | 'error' | 'critical';
  metric: string;
  component: string;
  message: string;
  currentValue: number;
  threshold: number;
  impact: 'low' | 'medium' | 'high' | 'critical';
  recommendations: string[];
  timestamp: Date;
  acknowledged: boolean;
  autoResolve: boolean;
  escalationLevel: number;
}

export interface PerformanceBaseline {
  metric: string;
  component: string;
  timeframe: '1h' | '24h' | '7d' | '30d';
  baseline: {
    average: number;
    p50: number;
    p95: number;
    p99: number;
    min: number;
    max: number;
  };
  trend: 'improving' | 'stable' | 'degrading';
  lastCalculated: Date;
}

export interface PerformanceReport {
  id: string;
  period: { start: Date; end: Date };
  summary: {
    overallHealth: number;
    uptime: number;
    avgResponseTime: number;
    errorRate: number;
    throughput: number;
  };
  trends: {
    metric: string;
    change: number;
    direction: 'up' | 'down' | 'stable';
  }[];
  incidents: PerformanceIncident[];
  recommendations: OptimizationRecommendation[];
  generatedAt: Date;
}

export interface PerformanceIncident {
  id: string;
  title: string;
  severity: 'minor' | 'major' | 'critical';
  startTime: Date;
  endTime?: Date;
  duration?: number;
  affectedComponents: string[];
  rootCause?: string;
  impact: {
    usersAffected: number;
    revenueImpact: number;
    serviceAvailability: number;
  };
  timeline: Array<{
    timestamp: Date;
    event: string;
    description: string;
  }>;
  resolution?: {
    action: string;
    implementedBy: string;
    timestamp: Date;
  };
}

export interface OptimizationRecommendation {
  id: string;
  category: 'performance' | 'scalability' | 'reliability' | 'cost';
  priority: 'low' | 'medium' | 'high' | 'urgent';
  title: string;
  description: string;
  impact: {
    performance: string;
    cost: number;
    effort: 'low' | 'medium' | 'high';
  };
  implementation: {
    steps: string[];
    timeline: string;
    resources: string[];
  };
  metrics: string[];
  expectedImprovement: {
    responseTime?: number;
    throughput?: number;
    errorReduction?: number;
    costSavings?: number;
  };
}

export interface ResourceUtilization {
  component: string;
  resources: {
    cpu: {
      usage: number;
      cores: number;
      loadAverage: number[];
    };
    memory: {
      used: number;
      total: number;
      cached: number;
      swapUsed: number;
    };
    disk: {
      used: number;
      total: number;
      iops: number;
      readThroughput: number;
      writeThroughput: number;
    };
    network: {
      inbound: number;
      outbound: number;
      connections: number;
      errors: number;
    };
  };
  timestamp: Date;
}

export class PerformanceMonitoring extends EventEmitter {
  private logger: Logger;
  private isRunning: boolean = false;

  // Data stores
  private metrics: Map<string, PerformanceMetric[]> = new Map();
  private systemHealth: SystemHealth;
  private alerts: Map<string, PerformanceAlert> = new Map();
  private baselines: Map<string, PerformanceBaseline> = new Map();
  private incidents: Map<string, PerformanceIncident> = new Map();
  private resourceUtilization: Map<string, ResourceUtilization[]> = new Map();

  // Configuration
  private monitoringConfig = {
    metricsRetentionDays: 30,
    alertThresholds: {
      responseTime: { warning: 1000, critical: 2000 },
      errorRate: { warning: 1, critical: 5 },
      cpuUsage: { warning: 70, critical: 90 },
      memoryUsage: { warning: 80, critical: 95 },
      diskUsage: { warning: 85, critical: 95 }
    },
    collectionIntervals: {
      system: 15000,   // 15 seconds
      application: 30000, // 30 seconds
      business: 60000   // 1 minute
    }
  };

  // Performance tracking
  private performanceStats = {
    totalMetrics: 0,
    alertsTriggered: 0,
    incidentsResolved: 0,
    avgCollectionTime: 0,
    systemUptime: process.uptime()
  };

  constructor() {
    super();
    this.logger = new Logger('PerformanceMonitoring');
    this.systemHealth = this.initializeSystemHealth();
    this.initializeSystem();
  }

  /**
   * Initialize the performance monitoring system
   */
  private async initializeSystem(): Promise<void> {
    try {
      await this.loadBaselines();
      await this.setupMonitoring();
      
      this.startMetricsCollection();
      this.startHealthChecks();
      this.startAlertProcessing();
      this.isRunning = true;
      
      this.logger.info('Performance Monitoring System initialized successfully');
      this.emit('system:initialized');
    } catch (error) {
      this.logger.error('Failed to initialize performance monitoring system', error);
      throw error;
    }
  }

  /**
   * Record a performance metric
   */
  public recordMetric(metric: Omit<PerformanceMetric, 'id' | 'timestamp'>): void {
    try {
      const performanceMetric: PerformanceMetric = {
        ...metric,
        id: this.generateId(),
        timestamp: new Date()
      };

      const key = `${metric.category}_${metric.name}`;
      if (!this.metrics.has(key)) {
        this.metrics.set(key, []);
      }
      
      this.metrics.get(key)!.push(performanceMetric);
      this.performanceStats.totalMetrics++;

      // Check for alerts
      this.checkMetricAlerts(performanceMetric);

      this.emit('metric:recorded', performanceMetric);
    } catch (error) {
      this.logger.error('Failed to record metric', error);
    }
  }

  /**
   * Get current system health
   */
  public getSystemHealth(): SystemHealth {
    return { ...this.systemHealth };
  }

  /**
   * Get performance metrics for a specific period
   */
  public getMetrics(
    category?: string,
    timeRange?: { start: Date; end: Date },
    limit?: number
  ): PerformanceMetric[] {
    try {
      let allMetrics: PerformanceMetric[] = [];

      for (const [key, metrics] of this.metrics.entries()) {
        if (category && !key.startsWith(category)) continue;
        
        let filteredMetrics = metrics;
        
        if (timeRange) {
          filteredMetrics = metrics.filter(m => 
            m.timestamp >= timeRange.start && m.timestamp <= timeRange.end
          );
        }
        
        allMetrics.push(...filteredMetrics);
      }

      // Sort by timestamp (newest first)
      allMetrics.sort((a, b) => b.timestamp.getTime() - a.timestamp.getTime());

      // Apply limit
      if (limit) {
        allMetrics = allMetrics.slice(0, limit);
      }

      return allMetrics;
    } catch (error) {
      this.logger.error('Failed to get metrics', error);
      return [];
    }
  }

  /**
   * Get active performance alerts
   */
  public getActiveAlerts(): PerformanceAlert[] {
    return Array.from(this.alerts.values())
      .filter(alert => !alert.acknowledged)
      .sort((a, b) => b.timestamp.getTime() - a.timestamp.getTime());
  }

  /**
   * Generate performance report
   */
  public async generateReport(period: { start: Date; end: Date }): Promise<PerformanceReport> {
    try {
      const metrics = this.getMetrics(undefined, period);
      
      // Calculate summary statistics
      const responseTimeMetrics = metrics.filter(m => m.name === 'response_time');
      const errorMetrics = metrics.filter(m => m.name === 'error_rate');
      const throughputMetrics = metrics.filter(m => m.name === 'throughput');

      const summary = {
        overallHealth: this.systemHealth.score,
        uptime: this.calculateUptime(period),
        avgResponseTime: this.calculateAverage(responseTimeMetrics),
        errorRate: this.calculateAverage(errorMetrics),
        throughput: this.calculateAverage(throughputMetrics)
      };

      // Analyze trends
      const trends = this.analyzeTrends(metrics);

      // Get incidents for the period
      const incidents = Array.from(this.incidents.values())
        .filter(incident => 
          incident.startTime >= period.start && incident.startTime <= period.end
        );

      // Generate recommendations
      const recommendations = await this.generateOptimizationRecommendations();

      const report: PerformanceReport = {
        id: this.generateId(),
        period,
        summary,
        trends,
        incidents,
        recommendations,
        generatedAt: new Date()
      };

      this.logger.info('Performance report generated', { reportId: report.id });
      this.emit('report:generated', report);

      return report;
    } catch (error) {
      this.logger.error('Failed to generate performance report', error);
      throw error;
    }
  }

  /**
   * Create performance incident
   */
  public createIncident(
    title: string,
    severity: PerformanceIncident['severity'],
    affectedComponents: string[],
    description: string
  ): PerformanceIncident {
    try {
      const incident: PerformanceIncident = {
        id: this.generateId(),
        title,
        severity,
        startTime: new Date(),
        affectedComponents,
        impact: {
          usersAffected: 0,
          revenueImpact: 0,
          serviceAvailability: 100
        },
        timeline: [
          {
            timestamp: new Date(),
            event: 'incident_created',
            description
          }
        ]
      };

      this.incidents.set(incident.id, incident);

      this.logger.warn(`Performance incident created: ${title}`, { 
        incidentId: incident.id,
        severity,
        components: affectedComponents 
      });
      
      this.emit('incident:created', incident);

      return incident;
    } catch (error) {
      this.logger.error('Failed to create incident', error);
      throw error;
    }
  }

  /**
   * Resolve performance incident
   */
  public resolveIncident(
    incidentId: string,
    resolution: string,
    implementedBy: string
  ): void {
    try {
      const incident = this.incidents.get(incidentId);
      if (!incident) {
        throw new Error(`Incident not found: ${incidentId}`);
      }

      incident.endTime = new Date();
      incident.duration = incident.endTime.getTime() - incident.startTime.getTime();
      incident.resolution = {
        action: resolution,
        implementedBy,
        timestamp: new Date()
      };

      incident.timeline.push({
        timestamp: new Date(),
        event: 'incident_resolved',
        description: resolution
      });

      this.performanceStats.incidentsResolved++;

      this.logger.info(`Performance incident resolved: ${incident.title}`, { 
        incidentId,
        duration: incident.duration 
      });
      
      this.emit('incident:resolved', incident);
    } catch (error) {
      this.logger.error('Failed to resolve incident', error);
    }
  }

  /**
   * Get resource utilization for components
   */
  public getResourceUtilization(
    component?: string,
    timeRange?: { start: Date; end: Date }
  ): ResourceUtilization[] {
    try {
      let utilization: ResourceUtilization[] = [];

      for (const [comp, data] of this.resourceUtilization.entries()) {
        if (component && comp !== component) continue;
        
        let filteredData = data;
        if (timeRange) {
          filteredData = data.filter(u => 
            u.timestamp >= timeRange.start && u.timestamp <= timeRange.end
          );
        }
        
        utilization.push(...filteredData);
      }

      return utilization.sort((a, b) => b.timestamp.getTime() - a.timestamp.getTime());
    } catch (error) {
      this.logger.error('Failed to get resource utilization', error);
      return [];
    }
  }

  /**
   * Get performance statistics
   */
  public getPerformanceStats(): any {
    return {
      ...this.performanceStats,
      uptime: this.isRunning ? process.uptime() : 0,
      memoryUsage: process.memoryUsage().heapUsed / 1024 / 1024,
      activeAlerts: this.getActiveAlerts().length,
      totalIncidents: this.incidents.size,
      metricsCollected: this.performanceStats.totalMetrics
    };
  }

  // Helper methods
  private generateId(): string {
    return Math.random().toString(36).substr(2, 9);
  }

  private initializeSystemHealth(): SystemHealth {
    return {
      overall: 'healthy',
      score: 100,
      components: {
        api: {
          status: 'healthy',
          responseTime: 150,
          uptime: 99.9,
          errorRate: 0.1,
          throughput: 1000,
          details: { memory: 512, cpu: 25, connections: 100, errors: [] }
        },
        database: {
          status: 'healthy',
          responseTime: 50,
          uptime: 99.95,
          errorRate: 0.05,
          throughput: 2000,
          details: { memory: 2048, cpu: 40, connections: 50, errors: [] }
        },
        cache: {
          status: 'healthy',
          responseTime: 5,
          uptime: 99.8,
          errorRate: 0.01,
          throughput: 5000,
          details: { memory: 1024, cpu: 15, connections: 200, errors: [] }
        },
        messageQueue: {
          status: 'healthy',
          responseTime: 20,
          uptime: 99.7,
          errorRate: 0.1,
          throughput: 1500,
          details: { memory: 256, cpu: 10, connections: 30, errors: [] }
        },
        storage: {
          status: 'healthy',
          responseTime: 100,
          uptime: 99.9,
          errorRate: 0.01,
          throughput: 500,
          details: { memory: 128, cpu: 5, connections: 10, errors: [] }
        },
        network: {
          status: 'healthy',
          responseTime: 10,
          uptime: 99.95,
          errorRate: 0.01,
          throughput: 10000,
          details: { memory: 64, cpu: 5, connections: 1000, errors: [] }
        }
      },
      lastUpdated: new Date()
    };
  }

  private async loadBaselines(): Promise<void> {
    // Load or calculate performance baselines
    this.logger.info('Loading performance baselines...');
  }

  private async setupMonitoring(): Promise<void> {
    // Setup monitoring configuration
    this.logger.info('Setting up performance monitoring...');
  }

  private startMetricsCollection(): void {
    // System metrics collection
    setInterval(() => {
      this.collectSystemMetrics();
    }, this.monitoringConfig.collectionIntervals.system);

    // Application metrics collection
    setInterval(() => {
      this.collectApplicationMetrics();
    }, this.monitoringConfig.collectionIntervals.application);

    // Business metrics collection
    setInterval(() => {
      this.collectBusinessMetrics();
    }, this.monitoringConfig.collectionIntervals.business);
  }

  private startHealthChecks(): void {
    setInterval(() => {
      this.updateSystemHealth();
    }, 30000); // Update health every 30 seconds
  }

  private startAlertProcessing(): void {
    setInterval(() => {
      this.processAlerts();
    }, 10000); // Process alerts every 10 seconds
  }

  private collectSystemMetrics(): void {
    try {
      const memUsage = process.memoryUsage();
      const cpuUsage = process.cpuUsage();

      // Memory metrics
      this.recordMetric({
        name: 'memory_usage',
        category: 'system',
        value: memUsage.heapUsed / 1024 / 1024,
        unit: 'MB',
        source: 'node_process',
        tags: { type: 'heap' }
      });

      // CPU metrics (simplified)
      this.recordMetric({
        name: 'cpu_usage',
        category: 'system',
        value: Math.random() * 100, // Mock CPU usage
        unit: 'percent',
        source: 'node_process',
        tags: { type: 'total' }
      });

      // Uptime
      this.recordMetric({
        name: 'uptime',
        category: 'system',
        value: process.uptime(),
        unit: 'seconds',
        source: 'node_process',
        tags: { type: 'application' }
      });

    } catch (error) {
      this.logger.error('Failed to collect system metrics', error);
    }
  }

  private collectApplicationMetrics(): void {
    try {
      // Response time (mock)
      this.recordMetric({
        name: 'response_time',
        category: 'application',
        value: Math.random() * 500 + 50,
        unit: 'ms',
        source: 'express_server',
        tags: { endpoint: 'api' }
      });

      // Throughput (mock)
      this.recordMetric({
        name: 'throughput',
        category: 'application',
        value: Math.random() * 1000 + 500,
        unit: 'req/min',
        source: 'express_server',
        tags: { type: 'requests' }
      });

      // Error rate (mock)
      this.recordMetric({
        name: 'error_rate',
        category: 'application',
        value: Math.random() * 2,
        unit: 'percent',
        source: 'express_server',
        tags: { type: '5xx' }
      });

    } catch (error) {
      this.logger.error('Failed to collect application metrics', error);
    }
  }

  private collectBusinessMetrics(): void {
    try {
      // Order processing time
      this.recordMetric({
        name: 'order_processing_time',
        category: 'business',
        value: Math.random() * 10000 + 1000,
        unit: 'ms',
        source: 'order_service',
        tags: { marketplace: 'trendyol' }
      });

      // Sync success rate
      this.recordMetric({
        name: 'sync_success_rate',
        category: 'business',
        value: 95 + Math.random() * 5,
        unit: 'percent',
        source: 'sync_engine',
        tags: { type: 'inventory' }
      });

    } catch (error) {
      this.logger.error('Failed to collect business metrics', error);
    }
  }

  private updateSystemHealth(): void {
    try {
      const components = this.systemHealth.components;
      
      // Update each component with current metrics
      for (const [componentName, component] of Object.entries(components)) {
        // Simulate health changes
        component.responseTime = Math.random() * 200 + 50;
        component.errorRate = Math.random() * 1;
        component.throughput = Math.random() * 2000 + 500;
        
        // Update status based on thresholds
        if (component.responseTime > 1000 || component.errorRate > 5) {
          component.status = 'critical';
        } else if (component.responseTime > 500 || component.errorRate > 2) {
          component.status = 'warning';
        } else {
          component.status = 'healthy';
        }
      }

      // Calculate overall health score
      const healthScores = Object.values(components).map(comp => {
        switch (comp.status) {
          case 'healthy': return 100;
          case 'warning': return 70;
          case 'critical': return 30;
          case 'down': return 0;
          default: return 50;
        }
      });

      this.systemHealth.score = Math.round(
        healthScores.reduce((sum, score) => sum + score, 0) / healthScores.length
      );

      // Update overall status
      if (this.systemHealth.score >= 90) {
        this.systemHealth.overall = 'healthy';
      } else if (this.systemHealth.score >= 70) {
        this.systemHealth.overall = 'warning';
      } else {
        this.systemHealth.overall = 'critical';
      }

      this.systemHealth.lastUpdated = new Date();

      this.emit('health:updated', this.systemHealth);

    } catch (error) {
      this.logger.error('Failed to update system health', error);
    }
  }

  private checkMetricAlerts(metric: PerformanceMetric): void {
    try {
      const thresholds = this.monitoringConfig.alertThresholds;
      let threshold: any;
      let alertType: string;

      // Determine threshold based on metric
      switch (metric.name) {
        case 'response_time':
          threshold = thresholds.responseTime;
          alertType = 'response_time_threshold';
          break;
        case 'error_rate':
          threshold = thresholds.errorRate;
          alertType = 'error_rate_threshold';
          break;
        case 'cpu_usage':
          threshold = thresholds.cpuUsage;
          alertType = 'cpu_usage_threshold';
          break;
        case 'memory_usage':
          threshold = thresholds.memoryUsage;
          alertType = 'memory_usage_threshold';
          break;
        default:
          return; // No threshold defined for this metric
      }

      let severity: PerformanceAlert['severity'];
      let thresholdValue: number;

      if (metric.value >= threshold.critical) {
        severity = 'critical';
        thresholdValue = threshold.critical;
      } else if (metric.value >= threshold.warning) {
        severity = 'warning';
        thresholdValue = threshold.warning;
      } else {
        return; // No alert needed
      }

      const alert: PerformanceAlert = {
        id: this.generateId(),
        type: 'threshold',
        severity,
        metric: metric.name,
        component: metric.source,
        message: `${metric.name} exceeded ${severity} threshold: ${metric.value.toFixed(2)} ${metric.unit}`,
        currentValue: metric.value,
        threshold: thresholdValue,
        impact: severity === 'critical' ? 'high' : 'medium',
        recommendations: this.generateAlertRecommendations(metric.name, severity),
        timestamp: new Date(),
        acknowledged: false,
        autoResolve: false,
        escalationLevel: 0
      };

      this.alerts.set(alert.id, alert);
      this.performanceStats.alertsTriggered++;

      this.logger.warn(`Performance alert triggered: ${alert.message}`, { alertId: alert.id });
      this.emit('alert:triggered', alert);

    } catch (error) {
      this.logger.error('Failed to check metric alerts', error);
    }
  }

  private generateAlertRecommendations(metricName: string, severity: string): string[] {
    const recommendations = [];

    switch (metricName) {
      case 'response_time':
        recommendations.push(
          'Check for slow database queries',
          'Review application performance',
          'Consider scaling resources'
        );
        break;
      case 'error_rate':
        recommendations.push(
          'Check application logs for errors',
          'Review recent deployments',
          'Monitor external dependencies'
        );
        break;
      case 'cpu_usage':
        recommendations.push(
          'Identify CPU-intensive processes',
          'Consider horizontal scaling',
          'Optimize application code'
        );
        break;
      case 'memory_usage':
        recommendations.push(
          'Check for memory leaks',
          'Increase available memory',
          'Optimize memory usage patterns'
        );
        break;
      default:
        recommendations.push('Investigate the root cause', 'Review system metrics');
    }

    return recommendations;
  }

  private processAlerts(): void {
    // Process alert escalations, auto-resolution, etc.
    for (const alert of this.alerts.values()) {
      if (!alert.acknowledged && alert.autoResolve) {
        // Check if conditions have returned to normal
        // This would involve checking current metric values
      }
    }
  }

  private calculateUptime(period: { start: Date; end: Date }): number {
    // Mock uptime calculation
    return 99.5 + Math.random() * 0.5;
  }

  private calculateAverage(metrics: PerformanceMetric[]): number {
    if (metrics.length === 0) return 0;
    return metrics.reduce((sum, m) => sum + m.value, 0) / metrics.length;
  }

  private analyzeTrends(metrics: PerformanceMetric[]): Array<{
    metric: string;
    change: number;
    direction: 'up' | 'down' | 'stable';
  }> {
    const trends = [];
    const metricGroups = new Map<string, PerformanceMetric[]>();
    
    // Group metrics by name
    for (const metric of metrics) {
      if (!metricGroups.has(metric.name)) {
        metricGroups.set(metric.name, []);
      }
      metricGroups.get(metric.name)!.push(metric);
    }

    // Analyze trend for each metric
    for (const [metricName, metricData] of metricGroups.entries()) {
      if (metricData.length < 2) continue;
      
      const sorted = metricData.sort((a, b) => a.timestamp.getTime() - b.timestamp.getTime());
      const first = sorted[0].value;
      const last = sorted[sorted.length - 1].value;
      const change = ((last - first) / first) * 100;
      
      let direction: 'up' | 'down' | 'stable' = 'stable';
      if (Math.abs(change) > 5) {
        direction = change > 0 ? 'up' : 'down';
      }

      trends.push({
        metric: metricName,
        change: Math.round(change * 100) / 100,
        direction
      });
    }

    return trends;
  }

  private async generateOptimizationRecommendations(): Promise<OptimizationRecommendation[]> {
    return [
      {
        id: this.generateId(),
        category: 'performance',
        priority: 'medium',
        title: 'Database Query Optimization',
        description: 'Optimize slow database queries to improve response times',
        impact: {
          performance: '20-30% response time improvement',
          cost: 0,
          effort: 'medium'
        },
        implementation: {
          steps: [
            'Identify slow queries using query logs',
            'Add appropriate database indexes',
            'Optimize query structures',
            'Monitor performance improvements'
          ],
          timeline: '2-3 weeks',
          resources: ['Database administrator', 'Backend developers']
        },
        metrics: ['response_time', 'database_query_time'],
        expectedImprovement: {
          responseTime: 25,
          throughput: 15
        }
      },
      {
        id: this.generateId(),
        category: 'scalability',
        priority: 'high',
        title: 'Implement Auto-scaling',
        description: 'Set up automatic scaling based on resource utilization',
        impact: {
          performance: 'Better handling of traffic spikes',
          cost: 5000,
          effort: 'high'
        },
        implementation: {
          steps: [
            'Configure auto-scaling policies',
            'Set up monitoring triggers',
            'Test scaling scenarios',
            'Implement cost controls'
          ],
          timeline: '4-6 weeks',
          resources: ['DevOps team', 'Infrastructure team']
        },
        metrics: ['cpu_usage', 'memory_usage', 'response_time'],
        expectedImprovement: {
          responseTime: 40,
          costSavings: 15
        }
      }
    ];
  }

  /**
   * Acknowledge a performance alert
   */
  public acknowledgeAlert(alertId: string, acknowledgedBy: string): void {
    try {
      const alert = this.alerts.get(alertId);
      if (!alert) {
        throw new Error(`Alert not found: ${alertId}`);
      }

      alert.acknowledged = true;
      
      this.logger.info(`Performance alert acknowledged: ${alert.message}`, { 
        alertId,
        acknowledgedBy 
      });
      
      this.emit('alert:acknowledged', { alert, acknowledgedBy });
    } catch (error) {
      this.logger.error('Failed to acknowledge alert', error);
    }
  }

  /**
   * Shutdown the performance monitoring system
   */
  public async shutdown(): Promise<void> {
    try {
      this.isRunning = false;
      this.removeAllListeners();
      
      this.logger.info('Performance Monitoring System shut down successfully');
    } catch (error) {
      this.logger.error('Error during performance monitoring shutdown', error);
      throw error;
    }
  }
}