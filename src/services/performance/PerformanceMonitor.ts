/**
 * Performance Monitor Service
 * Real-time performance monitoring, profiling, and optimization recommendations
 */

import { EventEmitter } from 'events';

// Types
export interface PerformanceMetrics {
  timestamp: Date;
  cpu: CPUMetrics;
  memory: MemoryMetrics;
  network: NetworkMetrics;
  database: DatabaseMetrics;
  application: ApplicationMetrics;
  user: UserExperienceMetrics;
}

export interface CPUMetrics {
  usage: number; // percentage
  loadAverage: number[];
  processes: number;
  threads: number;
  temperature?: number;
}

export interface MemoryMetrics {
  used: number; // bytes
  available: number;
  total: number;
  usage: number; // percentage
  heap: HeapMetrics;
  gc: GCMetrics;
}

export interface HeapMetrics {
  used: number;
  size: number;
  limit: number;
  external: number;
}

export interface GCMetrics {
  collections: number;
  duration: number;
  freed: number;
  type: string;
}

export interface NetworkMetrics {
  bytesIn: number;
  bytesOut: number;
  packetsIn: number;
  packetsOut: number;
  latency: number;
  bandwidth: BandwidthMetrics;
  connections: ConnectionMetrics;
}

export interface BandwidthMetrics {
  upload: number; // Mbps
  download: number;
  utilization: number; // percentage
}

export interface ConnectionMetrics {
  active: number;
  established: number;
  timeWait: number;
  closeWait: number;
}

export interface DatabaseMetrics {
  connections: number;
  activeQueries: number;
  slowQueries: number;
  averageQueryTime: number;
  cacheHitRatio: number;
  deadlocks: number;
  tableSize: number;
  indexSize: number;
}

export interface ApplicationMetrics {
  requestsPerSecond: number;
  averageResponseTime: number;
  errorRate: number;
  throughput: number;
  activeUsers: number;
  endpoints: EndpointMetrics[];
}

export interface EndpointMetrics {
  path: string;
  method: string;
  calls: number;
  averageTime: number;
  p50: number;
  p95: number;
  p99: number;
  errors: number;
  errorRate: number;
}

export interface UserExperienceMetrics {
  pageLoadTime: number;
  timeToInteractive: number;
  firstContentfulPaint: number;
  largestContentfulPaint: number;
  cumulativeLayoutShift: number;
  firstInputDelay: number;
  coreWebVitals: CoreWebVitals;
}

export interface CoreWebVitals {
  lcp: number; // Largest Contentful Paint
  fid: number; // First Input Delay
  cls: number; // Cumulative Layout Shift
  score: 'GOOD' | 'NEEDS_IMPROVEMENT' | 'POOR';
}

export interface PerformanceAlert {
  id: string;
  timestamp: Date;
  severity: 'LOW' | 'MEDIUM' | 'HIGH' | 'CRITICAL';
  category: 'CPU' | 'MEMORY' | 'NETWORK' | 'DATABASE' | 'APPLICATION' | 'USER_EXPERIENCE';
  metric: string;
  currentValue: number;
  threshold: number;
  message: string;
  recommendation: string;
  acknowledged: boolean;
}

export interface PerformanceThreshold {
  metric: string;
  warningLevel: number;
  criticalLevel: number;
  enabled: boolean;
}

export interface PerformanceReport {
  id: string;
  startTime: Date;
  endTime: Date;
  summary: PerformanceSummary;
  trends: PerformanceTrend[];
  alerts: PerformanceAlert[];
  recommendations: PerformanceRecommendation[];
  bottlenecks: PerformanceBottleneck[];
}

export interface PerformanceSummary {
  averageCPU: number;
  averageMemory: number;
  averageResponseTime: number;
  totalRequests: number;
  errorRate: number;
  uptime: number;
  peakConcurrentUsers: number;
}

export interface PerformanceTrend {
  metric: string;
  trend: 'IMPROVING' | 'STABLE' | 'DEGRADING';
  changePercentage: number;
  significance: 'LOW' | 'MEDIUM' | 'HIGH';
}

export interface PerformanceRecommendation {
  id: string;
  category: 'INFRASTRUCTURE' | 'APPLICATION' | 'DATABASE' | 'FRONTEND' | 'NETWORK';
  priority: 'LOW' | 'MEDIUM' | 'HIGH' | 'CRITICAL';
  title: string;
  description: string;
  implementation: string[];
  expectedImprovement: string;
  effort: 'LOW' | 'MEDIUM' | 'HIGH';
  cost: 'FREE' | 'LOW' | 'MEDIUM' | 'HIGH';
}

export interface PerformanceBottleneck {
  id: string;
  type: 'CPU_BOUND' | 'MEMORY_BOUND' | 'IO_BOUND' | 'NETWORK_BOUND' | 'DATABASE_BOUND';
  component: string;
  impact: 'LOW' | 'MEDIUM' | 'HIGH' | 'CRITICAL';
  description: string;
  affectedOperations: string[];
  resolution: string;
}

export interface PerformanceProfile {
  id: string;
  name: string;
  startTime: Date;
  endTime: Date;
  duration: number;
  functions: FunctionProfile[];
  hotSpots: HotSpot[];
  callGraph: CallGraphNode[];
}

export interface FunctionProfile {
  name: string;
  calls: number;
  totalTime: number;
  ownTime: number;
  averageTime: number;
  percentage: number;
  file: string;
  line?: number;
}

export interface HotSpot {
  function: string;
  file: string;
  line: number;
  time: number;
  percentage: number;
  optimization: string;
}

export interface CallGraphNode {
  function: string;
  children: CallGraphNode[];
  time: number;
  calls: number;
}

const defaultThresholds: PerformanceThreshold[] = [
  { metric: 'cpu.usage', warningLevel: 70, criticalLevel: 90, enabled: true },
  { metric: 'memory.usage', warningLevel: 80, criticalLevel: 95, enabled: true },
  { metric: 'application.responseTime', warningLevel: 1000, criticalLevel: 2000, enabled: true },
  { metric: 'application.errorRate', warningLevel: 1, criticalLevel: 5, enabled: true },
  { metric: 'database.slowQueries', warningLevel: 10, criticalLevel: 50, enabled: true },
  { metric: 'user.pageLoadTime', warningLevel: 3000, criticalLevel: 5000, enabled: true }
];

export class PerformanceMonitor extends EventEmitter {
  private metrics: PerformanceMetrics[] = [];
  private alerts: PerformanceAlert[] = [];
  private thresholds: Map<string, PerformanceThreshold> = new Map();
  private isMonitoring = false;
  private monitoringInterval?: NodeJS.Timeout;
  private profiles: Map<string, PerformanceProfile> = new Map();

  constructor() {
    super();
    this.initialize();
  }

  private initialize(): void {
    // Load default thresholds
    defaultThresholds.forEach(threshold => {
      this.thresholds.set(threshold.metric, threshold);
    });

    // Start monitoring
    this.startMonitoring();
    
    console.log('⚡ Performance Monitor initialized');
  }

  public startMonitoring(interval: number = 5000): void {
    if (this.isMonitoring) return;

    this.isMonitoring = true;
    this.monitoringInterval = setInterval(() => {
      this.collectMetrics();
    }, interval);

    console.log(`📊 Performance monitoring started (${interval}ms interval)`);
  }

  public stopMonitoring(): void {
    if (this.monitoringInterval) {
      clearInterval(this.monitoringInterval);
      this.monitoringInterval = undefined;
    }
    this.isMonitoring = false;
    console.log('⏹️ Performance monitoring stopped');
  }

  private async collectMetrics(): Promise<void> {
    try {
      const metrics: PerformanceMetrics = {
        timestamp: new Date(),
        cpu: await this.getCPUMetrics(),
        memory: await this.getMemoryMetrics(),
        network: await this.getNetworkMetrics(),
        database: await this.getDatabaseMetrics(),
        application: await this.getApplicationMetrics(),
        user: await this.getUserExperienceMetrics()
      };

      this.metrics.push(metrics);
      
      // Keep only last 1000 metrics
      if (this.metrics.length > 1000) {
        this.metrics = this.metrics.slice(-1000);
      }

      // Check thresholds
      this.checkThresholds(metrics);

      this.emit('metrics:collected', metrics);
    } catch (error) {
      console.error('Error collecting metrics:', error);
    }
  }

  private async getCPUMetrics(): Promise<CPUMetrics> {
    // Mock CPU metrics - in real implementation would use system APIs
    return {
      usage: Math.random() * 30 + 20, // 20-50%
      loadAverage: [0.5, 0.7, 0.8],
      processes: 150 + Math.floor(Math.random() * 50),
      threads: 800 + Math.floor(Math.random() * 200),
      temperature: 45 + Math.random() * 20
    };
  }

  private async getMemoryMetrics(): Promise<MemoryMetrics> {
    const total = 16 * 1024 * 1024 * 1024; // 16GB
    const used = total * (0.4 + Math.random() * 0.3); // 40-70%
    
    return {
      total,
      used,
      available: total - used,
      usage: (used / total) * 100,
      heap: {
        used: 150 * 1024 * 1024 + Math.random() * 50 * 1024 * 1024,
        size: 200 * 1024 * 1024,
        limit: 1.4 * 1024 * 1024 * 1024,
        external: 10 * 1024 * 1024
      },
      gc: {
        collections: Math.floor(Math.random() * 5),
        duration: Math.random() * 10,
        freed: Math.random() * 50 * 1024 * 1024,
        type: Math.random() > 0.5 ? 'major' : 'minor'
      }
    };
  }

  private async getNetworkMetrics(): Promise<NetworkMetrics> {
    return {
      bytesIn: Math.random() * 1000000,
      bytesOut: Math.random() * 800000,
      packetsIn: Math.random() * 1000,
      packetsOut: Math.random() * 800,
      latency: Math.random() * 50 + 10,
      bandwidth: {
        upload: 100 + Math.random() * 50,
        download: 500 + Math.random() * 200,
        utilization: Math.random() * 40 + 20
      },
      connections: {
        active: 50 + Math.floor(Math.random() * 100),
        established: 40 + Math.floor(Math.random() * 80),
        timeWait: Math.floor(Math.random() * 10),
        closeWait: Math.floor(Math.random() * 5)
      }
    };
  }

  private async getDatabaseMetrics(): Promise<DatabaseMetrics> {
    return {
      connections: 15 + Math.floor(Math.random() * 10),
      activeQueries: Math.floor(Math.random() * 20),
      slowQueries: Math.floor(Math.random() * 3),
      averageQueryTime: Math.random() * 100 + 50,
      cacheHitRatio: 85 + Math.random() * 10,
      deadlocks: Math.floor(Math.random() * 2),
      tableSize: 2.5 * 1024 * 1024 * 1024, // 2.5GB
      indexSize: 500 * 1024 * 1024 // 500MB
    };
  }

  private async getApplicationMetrics(): Promise<ApplicationMetrics> {
    const endpoints: EndpointMetrics[] = [
      {
        path: '/api/products',
        method: 'GET',
        calls: 1000 + Math.floor(Math.random() * 500),
        averageTime: 150 + Math.random() * 100,
        p50: 120,
        p95: 300,
        p99: 500,
        errors: Math.floor(Math.random() * 10),
        errorRate: Math.random() * 2
      },
      {
        path: '/api/orders',
        method: 'POST',
        calls: 200 + Math.floor(Math.random() * 100),
        averageTime: 250 + Math.random() * 150,
        p50: 200,
        p95: 450,
        p99: 800,
        errors: Math.floor(Math.random() * 5),
        errorRate: Math.random() * 1.5
      }
    ];

    return {
      requestsPerSecond: 50 + Math.random() * 100,
      averageResponseTime: 200 + Math.random() * 150,
      errorRate: Math.random() * 2,
      throughput: 1000 + Math.random() * 500,
      activeUsers: 150 + Math.floor(Math.random() * 100),
      endpoints
    };
  }

  private async getUserExperienceMetrics(): Promise<UserExperienceMetrics> {
    const lcp = 1500 + Math.random() * 1000;
    const fid = 80 + Math.random() * 40;
    const cls = Math.random() * 0.2;

    return {
      pageLoadTime: 2000 + Math.random() * 1000,
      timeToInteractive: 2500 + Math.random() * 1500,
      firstContentfulPaint: 1200 + Math.random() * 800,
      largestContentfulPaint: lcp,
      cumulativeLayoutShift: cls,
      firstInputDelay: fid,
      coreWebVitals: {
        lcp,
        fid,
        cls,
        score: this.calculateWebVitalScore(lcp, fid, cls)
      }
    };
  }

  private calculateWebVitalScore(lcp: number, fid: number, cls: number): CoreWebVitals['score'] {
    const lcpGood = lcp <= 2500;
    const fidGood = fid <= 100;
    const clsGood = cls <= 0.1;

    const goodCount = [lcpGood, fidGood, clsGood].filter(Boolean).length;
    
    if (goodCount === 3) return 'GOOD';
    if (goodCount >= 2) return 'NEEDS_IMPROVEMENT';
    return 'POOR';
  }

  private checkThresholds(metrics: PerformanceMetrics): void {
    const checks = [
      { metric: 'cpu.usage', value: metrics.cpu.usage },
      { metric: 'memory.usage', value: metrics.memory.usage },
      { metric: 'application.responseTime', value: metrics.application.averageResponseTime },
      { metric: 'application.errorRate', value: metrics.application.errorRate },
      { metric: 'database.slowQueries', value: metrics.database.slowQueries },
      { metric: 'user.pageLoadTime', value: metrics.user.pageLoadTime }
    ];

    checks.forEach(check => {
      const threshold = this.thresholds.get(check.metric);
      if (!threshold || !threshold.enabled) return;

      let severity: PerformanceAlert['severity'] | null = null;
      
      if (check.value >= threshold.criticalLevel) {
        severity = 'CRITICAL';
      } else if (check.value >= threshold.warningLevel) {
        severity = 'HIGH';
      }

      if (severity) {
        const alert: PerformanceAlert = {
          id: this.generateAlertId(),
          timestamp: new Date(),
          severity,
          category: this.getCategoryFromMetric(check.metric),
          metric: check.metric,
          currentValue: check.value,
          threshold: severity === 'CRITICAL' ? threshold.criticalLevel : threshold.warningLevel,
          message: `${check.metric} exceeded threshold: ${check.value.toFixed(2)} >= ${severity === 'CRITICAL' ? threshold.criticalLevel : threshold.warningLevel}`,
          recommendation: this.getRecommendationForMetric(check.metric, check.value),
          acknowledged: false
        };

        this.alerts.push(alert);
        this.emit('alert:triggered', alert);
      }
    });
  }

  private getCategoryFromMetric(metric: string): PerformanceAlert['category'] {
    if (metric.startsWith('cpu.')) return 'CPU';
    if (metric.startsWith('memory.')) return 'MEMORY';
    if (metric.startsWith('network.')) return 'NETWORK';
    if (metric.startsWith('database.')) return 'DATABASE';
    if (metric.startsWith('application.')) return 'APPLICATION';
    if (metric.startsWith('user.')) return 'USER_EXPERIENCE';
    return 'APPLICATION';
  }

  private getRecommendationForMetric(metric: string, value: number): string {
    const recommendations: Record<string, string> = {
      'cpu.usage': 'Consider scaling horizontally or optimizing CPU-intensive operations',
      'memory.usage': 'Check for memory leaks or increase available memory',
      'application.responseTime': 'Optimize database queries and implement caching',
      'application.errorRate': 'Investigate error logs and fix underlying issues',
      'database.slowQueries': 'Add database indexes or optimize query performance',
      'user.pageLoadTime': 'Optimize frontend assets and implement lazy loading'
    };
    
    return recommendations[metric] || 'Monitor and investigate further';
  }

  // Profiling Methods
  public startProfiling(name: string): string {
    const profileId = this.generateProfileId();
    const profile: PerformanceProfile = {
      id: profileId,
      name,
      startTime: new Date(),
      endTime: new Date(),
      duration: 0,
      functions: [],
      hotSpots: [],
      callGraph: []
    };

    this.profiles.set(profileId, profile);
    console.log(`🔍 Started profiling: ${name} (${profileId})`);
    
    return profileId;
  }

  public stopProfiling(profileId: string): PerformanceProfile | null {
    const profile = this.profiles.get(profileId);
    if (!profile) return null;

    profile.endTime = new Date();
    profile.duration = profile.endTime.getTime() - profile.startTime.getTime();
    
    // Mock profiling data
    profile.functions = this.generateMockFunctionProfiles();
    profile.hotSpots = this.generateMockHotSpots();
    profile.callGraph = this.generateMockCallGraph();

    console.log(`✅ Profiling completed: ${profile.name} (${profile.duration}ms)`);
    this.emit('profiling:completed', profile);
    
    return profile;
  }

  private generateMockFunctionProfiles(): FunctionProfile[] {
    return [
      {
        name: 'productController.getProducts',
        calls: 1000,
        totalTime: 5000,
        ownTime: 1500,
        averageTime: 5,
        percentage: 25,
        file: 'src/controllers/ProductController.ts',
        line: 45
      },
      {
        name: 'databaseService.query',
        calls: 2500,
        totalTime: 7500,
        ownTime: 7500,
        averageTime: 3,
        percentage: 37.5,
        file: 'src/services/DatabaseService.ts',
        line: 123
      },
      {
        name: 'cacheService.get',
        calls: 5000,
        totalTime: 2000,
        ownTime: 2000,
        averageTime: 0.4,
        percentage: 10,
        file: 'src/services/CacheService.ts',
        line: 67
      }
    ];
  }

  private generateMockHotSpots(): HotSpot[] {
    return [
      {
        function: 'JSON.stringify',
        file: 'src/utils/serializer.ts',
        line: 23,
        time: 3000,
        percentage: 15,
        optimization: 'Use faster JSON serialization library'
      },
      {
        function: 'bcrypt.compare',
        file: 'src/services/AuthService.ts',
        line: 89,
        time: 2500,
        percentage: 12.5,
        optimization: 'Implement bcrypt caching or use async version'
      }
    ];
  }

  private generateMockCallGraph(): CallGraphNode[] {
    return [
      {
        function: 'main',
        time: 10000,
        calls: 1,
        children: [
          {
            function: 'productController.getProducts',
            time: 5000,
            calls: 1000,
            children: [
              {
                function: 'databaseService.query',
                time: 3500,
                calls: 2500,
                children: []
              }
            ]
          }
        ]
      }
    ];
  }

  // Public API Methods
  public getMetrics(limit: number = 100): PerformanceMetrics[] {
    return this.metrics.slice(-limit);
  }

  public getLatestMetrics(): PerformanceMetrics | null {
    return this.metrics[this.metrics.length - 1] || null;
  }

  public getAlerts(acknowledged: boolean = false): PerformanceAlert[] {
    return this.alerts.filter(alert => alert.acknowledged === acknowledged);
  }

  public acknowledgeAlert(alertId: string): boolean {
    const alert = this.alerts.find(a => a.id === alertId);
    if (alert) {
      alert.acknowledged = true;
      this.emit('alert:acknowledged', alert);
      return true;
    }
    return false;
  }

  public setThreshold(metric: string, threshold: Partial<PerformanceThreshold>): void {
    const existing = this.thresholds.get(metric) || { metric, warningLevel: 0, criticalLevel: 0, enabled: true };
    const updated = { ...existing, ...threshold, metric };
    this.thresholds.set(metric, updated);
    this.emit('threshold:updated', updated);
  }

  public getThresholds(): PerformanceThreshold[] {
    return Array.from(this.thresholds.values());
  }

  public generateReport(startTime: Date, endTime: Date): PerformanceReport {
    const filteredMetrics = this.metrics.filter(m => 
      m.timestamp >= startTime && m.timestamp <= endTime
    );

    const summary = this.calculateSummary(filteredMetrics);
    const trends = this.calculateTrends(filteredMetrics);
    const reportAlerts = this.alerts.filter(a => 
      a.timestamp >= startTime && a.timestamp <= endTime
    );
    const recommendations = this.generateRecommendations(filteredMetrics, reportAlerts);
    const bottlenecks = this.identifyBottlenecks(filteredMetrics);

    return {
      id: this.generateReportId(),
      startTime,
      endTime,
      summary,
      trends,
      alerts: reportAlerts,
      recommendations,
      bottlenecks
    };
  }

  private calculateSummary(metrics: PerformanceMetrics[]): PerformanceSummary {
    if (metrics.length === 0) {
      return {
        averageCPU: 0,
        averageMemory: 0,
        averageResponseTime: 0,
        totalRequests: 0,
        errorRate: 0,
        uptime: 0,
        peakConcurrentUsers: 0
      };
    }

    const totalRequests = metrics.reduce((sum, m) => sum + m.application.requestsPerSecond, 0);
    
    return {
      averageCPU: metrics.reduce((sum, m) => sum + m.cpu.usage, 0) / metrics.length,
      averageMemory: metrics.reduce((sum, m) => sum + m.memory.usage, 0) / metrics.length,
      averageResponseTime: metrics.reduce((sum, m) => sum + m.application.averageResponseTime, 0) / metrics.length,
      totalRequests,
      errorRate: metrics.reduce((sum, m) => sum + m.application.errorRate, 0) / metrics.length,
      uptime: 99.9, // Mock uptime
      peakConcurrentUsers: Math.max(...metrics.map(m => m.application.activeUsers))
    };
  }

  private calculateTrends(metrics: PerformanceMetrics[]): PerformanceTrend[] {
    // Simple trend calculation - in real implementation would use more sophisticated analysis
    return [
      {
        metric: 'CPU Usage',
        trend: Math.random() > 0.5 ? 'STABLE' : 'IMPROVING',
        changePercentage: (Math.random() - 0.5) * 10,
        significance: 'MEDIUM'
      },
      {
        metric: 'Response Time',
        trend: 'IMPROVING',
        changePercentage: -5.2,
        significance: 'HIGH'
      }
    ];
  }

  private generateRecommendations(metrics: PerformanceMetrics[], alerts: PerformanceAlert[]): PerformanceRecommendation[] {
    const recommendations: PerformanceRecommendation[] = [];

    // High CPU recommendation
    const avgCPU = metrics.reduce((sum, m) => sum + m.cpu.usage, 0) / metrics.length;
    if (avgCPU > 70) {
      recommendations.push({
        id: this.generateRecommendationId(),
        category: 'INFRASTRUCTURE',
        priority: 'HIGH',
        title: 'Optimize CPU Usage',
        description: 'CPU usage is consistently high, affecting performance',
        implementation: [
          'Profile CPU-intensive operations',
          'Implement horizontal scaling',
          'Optimize algorithms and data structures',
          'Consider upgrading hardware'
        ],
        expectedImprovement: '30-50% reduction in CPU usage',
        effort: 'MEDIUM',
        cost: 'MEDIUM'
      });
    }

    // Database performance recommendation
    const avgSlowQueries = metrics.reduce((sum, m) => sum + m.database.slowQueries, 0) / metrics.length;
    if (avgSlowQueries > 5) {
      recommendations.push({
        id: this.generateRecommendationId(),
        category: 'DATABASE',
        priority: 'HIGH',
        title: 'Optimize Database Performance',
        description: 'High number of slow queries detected',
        implementation: [
          'Add database indexes',
          'Optimize query execution plans',
          'Implement query caching',
          'Consider database partitioning'
        ],
        expectedImprovement: '40-60% faster query execution',
        effort: 'MEDIUM',
        cost: 'LOW'
      });
    }

    return recommendations;
  }

  private identifyBottlenecks(metrics: PerformanceMetrics[]): PerformanceBottleneck[] {
    const bottlenecks: PerformanceBottleneck[] = [];

    // Database bottleneck
    const avgQueryTime = metrics.reduce((sum, m) => sum + m.database.averageQueryTime, 0) / metrics.length;
    if (avgQueryTime > 200) {
      bottlenecks.push({
        id: this.generateBottleneckId(),
        type: 'DATABASE_BOUND',
        component: 'Database Query Layer',
        impact: 'HIGH',
        description: 'Database queries are taking longer than expected',
        affectedOperations: ['Product search', 'Order processing', 'User authentication'],
        resolution: 'Optimize database indexes and implement query caching'
      });
    }

    return bottlenecks;
  }

  // ID Generators
  private generateAlertId(): string {
    return `alert_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  }

  private generateProfileId(): string {
    return `profile_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  }

  private generateReportId(): string {
    return `report_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  }

  private generateRecommendationId(): string {
    return `rec_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  }

  private generateBottleneckId(): string {
    return `bottleneck_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  }

  public getPerformanceScore(): number {
    const latest = this.getLatestMetrics();
    if (!latest) return 0;

    // Calculate performance score based on multiple factors
    const cpuScore = Math.max(0, 100 - latest.cpu.usage);
    const memoryScore = Math.max(0, 100 - latest.memory.usage);
    const responseScore = Math.max(0, 100 - (latest.application.averageResponseTime / 20));
    const errorScore = Math.max(0, 100 - (latest.application.errorRate * 20));
    const webVitalScore = latest.user.coreWebVitals.score === 'GOOD' ? 100 : 
                         latest.user.coreWebVitals.score === 'NEEDS_IMPROVEMENT' ? 70 : 40;

    return Math.round((cpuScore + memoryScore + responseScore + errorScore + webVitalScore) / 5);
  }
}

export default PerformanceMonitor; 