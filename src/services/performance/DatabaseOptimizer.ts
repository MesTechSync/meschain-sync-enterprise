/**
 * Database Optimizer Service
 * Intelligent database performance monitoring and optimization
 */

import { EventEmitter } from 'events';

// Types
export interface DatabaseConfig {
  type: 'MYSQL' | 'POSTGRESQL' | 'MONGODB' | 'REDIS' | 'ELASTICSEARCH';
  host: string;
  port: number;
  database: string;
  username: string;
  password: string;
  poolSize: number;
  connectionTimeout: number;
  queryTimeout: number;
  ssl?: boolean;
}

export interface QueryAnalysis {
  id: string;
  query: string;
  database: string;
  table: string;
  type: 'SELECT' | 'INSERT' | 'UPDATE' | 'DELETE' | 'CREATE' | 'ALTER' | 'DROP';
  executionTime: number;
  rowsAffected: number;
  indexesUsed: string[];
  fullTableScan: boolean;
  optimizationScore: number;
  recommendations: QueryRecommendation[];
  timestamp: Date;
  frequency: number;
  cacheHit?: boolean;
}

export interface QueryRecommendation {
  type: 'INDEX' | 'QUERY_REWRITE' | 'SCHEMA_CHANGE' | 'PARTITIONING' | 'CACHING';
  priority: 'LOW' | 'MEDIUM' | 'HIGH' | 'CRITICAL';
  title: string;
  description: string;
  impact: string;
  implementation: string[];
  estimatedImprovement: number; // percentage
  cost: 'LOW' | 'MEDIUM' | 'HIGH';
  risk: 'LOW' | 'MEDIUM' | 'HIGH';
}

export interface IndexSuggestion {
  id: string;
  table: string;
  columns: string[];
  type: 'BTREE' | 'HASH' | 'FULLTEXT' | 'SPATIAL' | 'COMPOSITE';
  reason: string;
  estimatedImprovement: number;
  estimatedSize: number;
  priority: 'LOW' | 'MEDIUM' | 'HIGH' | 'CRITICAL';
  queries: string[];
  creationSQL: string;
  impactedOperations: string[];
}

export interface DatabaseMetrics {
  timestamp: Date;
  connections: ConnectionMetrics;
  queries: QueryMetrics;
  storage: StorageMetrics;
  cache: CacheMetrics;
  locks: LockMetrics;
  replication?: ReplicationMetrics;
}

export interface ConnectionMetrics {
  active: number;
  idle: number;
  total: number;
  maxConnections: number;
  connectionUtilization: number;
  averageConnectionTime: number;
  connectionErrors: number;
  timeouts: number;
}

export interface QueryMetrics {
  totalQueries: number;
  queriesPerSecond: number;
  averageQueryTime: number;
  slowQueries: number;
  slowQueryThreshold: number;
  selectQueries: number;
  insertQueries: number;
  updateQueries: number;
  deleteQueries: number;
  cacheHitRatio: number;
}

export interface StorageMetrics {
  totalSize: number;
  dataSize: number;
  indexSize: number;
  freeSpace: number;
  fragmentedSpace: number;
  growthRate: number;
  ioReads: number;
  ioWrites: number;
  ioLatency: number;
}

export interface CacheMetrics {
  bufferPoolSize: number;
  bufferPoolUtilization: number;
  cacheHitRatio: number;
  cacheMissRatio: number;
  evictions: number;
  dirtyPages: number;
}

export interface LockMetrics {
  totalLocks: number;
  lockWaits: number;
  deadlocks: number;
  averageLockTime: number;
  lockTimeouts: number;
  blockingQueries: number;
}

export interface ReplicationMetrics {
  replicationLag: number;
  replicationErrors: number;
  binlogSize: number;
  slaveStatus: 'RUNNING' | 'STOPPED' | 'ERROR';
}

export interface OptimizationPlan {
  id: string;
  name: string;
  description: string;
  targetDatabase: string;
  recommendations: QueryRecommendation[];
  indexSuggestions: IndexSuggestion[];
  estimatedImpact: OptimizationImpact;
  implementationSteps: ImplementationStep[];
  rollbackPlan: RollbackStep[];
  riskAssessment: RiskAssessment;
  schedule: OptimizationSchedule;
  status: 'DRAFT' | 'APPROVED' | 'IN_PROGRESS' | 'COMPLETED' | 'ROLLED_BACK';
}

export interface OptimizationImpact {
  performanceImprovement: number; // percentage
  diskSpaceSaving: number; // bytes
  memoryOptimization: number; // percentage
  costReduction: number; // monthly cost reduction
  riskLevel: 'LOW' | 'MEDIUM' | 'HIGH';
}

export interface ImplementationStep {
  id: string;
  name: string;
  description: string;
  type: 'INDEX_CREATION' | 'QUERY_MODIFICATION' | 'CONFIG_CHANGE' | 'SCHEMA_CHANGE';
  sql?: string;
  estimatedDuration: number;
  dependencies: string[];
  rollbackSQL?: string;
  validation: string[];
}

export interface RollbackStep {
  stepId: string;
  rollbackSQL: string;
  validation: string[];
  description: string;
}

export interface RiskAssessment {
  overallRisk: 'LOW' | 'MEDIUM' | 'HIGH' | 'CRITICAL';
  riskFactors: RiskFactor[];
  mitigationStrategies: string[];
  backupRequirements: string[];
  testingPlan: string[];
}

export interface RiskFactor {
  category: 'PERFORMANCE' | 'DATA_INTEGRITY' | 'AVAILABILITY' | 'SECURITY';
  description: string;
  likelihood: 'LOW' | 'MEDIUM' | 'HIGH';
  impact: 'LOW' | 'MEDIUM' | 'HIGH';
  mitigation: string;
}

export interface OptimizationSchedule {
  plannedStart: Date;
  estimatedDuration: number;
  maintenanceWindow: boolean;
  backupBeforeExecution: boolean;
  rollbackTimeLimit: number;
}

export interface PerformanceBaseline {
  id: string;
  timestamp: Date;
  duration: number; // measurement period in minutes
  metrics: DatabaseMetrics[];
  queries: QueryAnalysis[];
  summary: BaselineSummary;
}

export interface BaselineSummary {
  averageQueryTime: number;
  queriesPerSecond: number;
  slowQueryPercentage: number;
  connectionUtilization: number;
  cacheHitRatio: number;
  ioLatency: number;
  topSlowQueries: QueryAnalysis[];
}

export class DatabaseOptimizer extends EventEmitter {
  private configs: Map<string, DatabaseConfig> = new Map();
  private metrics: Map<string, DatabaseMetrics[]> = new Map();
  private queryAnalyses: Map<string, QueryAnalysis[]> = new Map();
  private optimizationPlans: Map<string, OptimizationPlan> = new Map();
  private baselines: Map<string, PerformanceBaseline> = new Map();
  private isMonitoring = false;
  private monitoringInterval?: NodeJS.Timeout;

  constructor() {
    super();
    this.initialize();
  }

  private initialize(): void {
    console.log('📊 Database Optimizer initialized');
  }

  public addDatabase(name: string, config: DatabaseConfig): void {
    this.configs.set(name, config);
    this.metrics.set(name, []);
    this.queryAnalyses.set(name, []);
    console.log(`🗄️ Added database: ${name} (${config.type})`);
  }

  public startMonitoring(interval: number = 30000): void {
    if (this.isMonitoring) return;

    this.isMonitoring = true;
    this.monitoringInterval = setInterval(() => {
      this.collectMetrics();
    }, interval);

    console.log(`📈 Database monitoring started (${interval}ms interval)`);
  }

  public stopMonitoring(): void {
    if (this.monitoringInterval) {
      clearInterval(this.monitoringInterval);
      this.monitoringInterval = undefined;
    }
    this.isMonitoring = false;
    console.log('⏹️ Database monitoring stopped');
  }

  private async collectMetrics(): Promise<void> {
    for (const [dbName, config] of this.configs) {
      try {
        const metrics = await this.gatherDatabaseMetrics(dbName, config);
        
        const dbMetrics = this.metrics.get(dbName) || [];
        dbMetrics.push(metrics);
        
        // Keep only last 1000 metrics
        if (dbMetrics.length > 1000) {
          dbMetrics.splice(0, dbMetrics.length - 1000);
        }
        
        this.metrics.set(dbName, dbMetrics);
        
        // Analyze for optimization opportunities
        await this.analyzePerformance(dbName, metrics);
        
        this.emit('metrics:collected', { database: dbName, metrics });
        
      } catch (error) {
        console.error(`Error collecting metrics for ${dbName}:`, error);
        this.emit('metrics:error', { database: dbName, error });
      }
    }
  }

  private async gatherDatabaseMetrics(dbName: string, config: DatabaseConfig): Promise<DatabaseMetrics> {
    // Mock database metrics collection
    // In real implementation, would connect to actual database and gather real metrics
    
    const metrics: DatabaseMetrics = {
      timestamp: new Date(),
      connections: {
        active: Math.floor(Math.random() * 50) + 10,
        idle: Math.floor(Math.random() * 20) + 5,
        total: 0,
        maxConnections: config.poolSize,
        connectionUtilization: 0,
        averageConnectionTime: Math.random() * 100 + 50,
        connectionErrors: Math.floor(Math.random() * 3),
        timeouts: Math.floor(Math.random() * 2)
      },
      queries: {
        totalQueries: Math.floor(Math.random() * 10000) + 5000,
        queriesPerSecond: Math.random() * 100 + 50,
        averageQueryTime: Math.random() * 200 + 100,
        slowQueries: Math.floor(Math.random() * 10) + 2,
        slowQueryThreshold: 1000,
        selectQueries: Math.floor(Math.random() * 8000) + 4000,
        insertQueries: Math.floor(Math.random() * 1000) + 500,
        updateQueries: Math.floor(Math.random() * 800) + 400,
        deleteQueries: Math.floor(Math.random() * 200) + 100,
        cacheHitRatio: Math.random() * 20 + 75
      },
      storage: {
        totalSize: 10 * 1024 * 1024 * 1024, // 10GB
        dataSize: 7 * 1024 * 1024 * 1024,
        indexSize: 2 * 1024 * 1024 * 1024,
        freeSpace: 1 * 1024 * 1024 * 1024,
        fragmentedSpace: 100 * 1024 * 1024,
        growthRate: 2.5, // 2.5% monthly
        ioReads: Math.floor(Math.random() * 1000) + 500,
        ioWrites: Math.floor(Math.random() * 300) + 100,
        ioLatency: Math.random() * 20 + 5
      },
      cache: {
        bufferPoolSize: 2 * 1024 * 1024 * 1024, // 2GB
        bufferPoolUtilization: Math.random() * 20 + 75,
        cacheHitRatio: Math.random() * 10 + 85,
        cacheMissRatio: Math.random() * 10 + 5,
        evictions: Math.floor(Math.random() * 50),
        dirtyPages: Math.floor(Math.random() * 100) + 50
      },
      locks: {
        totalLocks: Math.floor(Math.random() * 100) + 50,
        lockWaits: Math.floor(Math.random() * 5),
        deadlocks: Math.floor(Math.random() * 2),
        averageLockTime: Math.random() * 50 + 10,
        lockTimeouts: Math.floor(Math.random() * 2),
        blockingQueries: Math.floor(Math.random() * 3)
      }
    };

    // Calculate derived values
    metrics.connections.total = metrics.connections.active + metrics.connections.idle;
    metrics.connections.connectionUtilization = 
      (metrics.connections.total / metrics.connections.maxConnections) * 100;

    return metrics;
  }

  private async analyzePerformance(dbName: string, metrics: DatabaseMetrics): Promise<void> {
    // Analyze slow queries
    if (metrics.queries.slowQueries > 5) {
      await this.analyzePotentialIndexes(dbName);
    }

    // Check connection pool utilization
    if (metrics.connections.connectionUtilization > 80) {
      this.emit('optimization:opportunity', {
        database: dbName,
        type: 'CONNECTION_POOL',
        severity: 'HIGH',
        message: 'Connection pool utilization is high',
        recommendation: 'Consider increasing pool size or optimizing connection usage'
      });
    }

    // Check cache hit ratio
    if (metrics.queries.cacheHitRatio < 80) {
      this.emit('optimization:opportunity', {
        database: dbName,
        type: 'CACHE_OPTIMIZATION',
        severity: 'MEDIUM',
        message: 'Cache hit ratio is below optimal',
        recommendation: 'Consider increasing buffer pool size or optimizing queries'
      });
    }

    // Check I/O latency
    if (metrics.storage.ioLatency > 15) {
      this.emit('optimization:opportunity', {
        database: dbName,
        type: 'IO_OPTIMIZATION',
        severity: 'HIGH',
        message: 'I/O latency is high',
        recommendation: 'Consider faster storage or query optimization'
      });
    }
  }

  public async analyzeQuery(dbName: string, query: string): Promise<QueryAnalysis> {
    const analysis: QueryAnalysis = {
      id: this.generateAnalysisId(),
      query: query.trim(),
      database: dbName,
      table: this.extractTableName(query),
      type: this.determineQueryType(query),
      executionTime: Math.random() * 1000 + 100,
      rowsAffected: Math.floor(Math.random() * 10000) + 100,
      indexesUsed: this.extractIndexesUsed(query),
      fullTableScan: this.detectFullTableScan(query),
      optimizationScore: 0,
      recommendations: [],
      timestamp: new Date(),
      frequency: 1,
      cacheHit: Math.random() > 0.3
    };

    // Calculate optimization score
    analysis.optimizationScore = this.calculateOptimizationScore(analysis);

    // Generate recommendations
    analysis.recommendations = this.generateQueryRecommendations(analysis);

    // Store analysis
    const analyses = this.queryAnalyses.get(dbName) || [];
    analyses.push(analysis);
    this.queryAnalyses.set(dbName, analyses);

    this.emit('query:analyzed', { database: dbName, analysis });

    return analysis;
  }

  private extractTableName(query: string): string {
    const fromMatch = query.match(/FROM\s+`?(\w+)`?/i);
    const intoMatch = query.match(/INTO\s+`?(\w+)`?/i);
    const updateMatch = query.match(/UPDATE\s+`?(\w+)`?/i);
    
    return fromMatch?.[1] || intoMatch?.[1] || updateMatch?.[1] || 'unknown';
  }

  private determineQueryType(query: string): QueryAnalysis['type'] {
    const upperQuery = query.trim().toUpperCase();
    
    if (upperQuery.startsWith('SELECT')) return 'SELECT';
    if (upperQuery.startsWith('INSERT')) return 'INSERT';
    if (upperQuery.startsWith('UPDATE')) return 'UPDATE';
    if (upperQuery.startsWith('DELETE')) return 'DELETE';
    if (upperQuery.startsWith('CREATE')) return 'CREATE';
    if (upperQuery.startsWith('ALTER')) return 'ALTER';
    if (upperQuery.startsWith('DROP')) return 'DROP';
    
    return 'SELECT';
  }

  private extractIndexesUsed(query: string): string[] {
    // Mock index detection - in real implementation would use EXPLAIN
    const mockIndexes = ['idx_primary', 'idx_created_at', 'idx_user_id'];
    return mockIndexes.slice(0, Math.floor(Math.random() * 3) + 1);
  }

  private detectFullTableScan(query: string): boolean {
    // Simple heuristic - in real implementation would use EXPLAIN
    return !query.includes('WHERE') || query.includes('SELECT *');
  }

  private calculateOptimizationScore(analysis: QueryAnalysis): number {
    let score = 100;

    // Penalties
    if (analysis.fullTableScan) score -= 30;
    if (analysis.executionTime > 1000) score -= 20;
    if (analysis.indexesUsed.length === 0) score -= 25;
    if (analysis.type === 'SELECT' && analysis.query.includes('SELECT *')) score -= 15;
    if (analysis.query.includes('ORDER BY') && !analysis.query.includes('LIMIT')) score -= 10;

    return Math.max(0, score);
  }

  private generateQueryRecommendations(analysis: QueryAnalysis): QueryRecommendation[] {
    const recommendations: QueryRecommendation[] = [];

    if (analysis.fullTableScan) {
      recommendations.push({
        type: 'INDEX',
        priority: 'HIGH',
        title: 'Add Index to Prevent Full Table Scan',
        description: 'Query is performing a full table scan which is inefficient',
        impact: 'Significant performance improvement for query execution',
        implementation: [
          'Analyze WHERE clause conditions',
          'Create appropriate indexes on filtering columns',
          'Test query performance after index creation'
        ],
        estimatedImprovement: 70,
        cost: 'LOW',
        risk: 'LOW'
      });
    }

    if (analysis.executionTime > 1000) {
      recommendations.push({
        type: 'QUERY_REWRITE',
        priority: 'HIGH',
        title: 'Optimize Slow Query',
        description: 'Query execution time exceeds acceptable threshold',
        impact: 'Reduced response time and improved user experience',
        implementation: [
          'Review query execution plan',
          'Optimize JOIN operations',
          'Consider query restructuring',
          'Add appropriate indexes'
        ],
        estimatedImprovement: 60,
        cost: 'MEDIUM',
        risk: 'LOW'
      });
    }

    if (analysis.query.includes('SELECT *')) {
      recommendations.push({
        type: 'QUERY_REWRITE',
        priority: 'MEDIUM',
        title: 'Specify Required Columns',
        description: 'SELECT * can be inefficient and may return unnecessary data',
        impact: 'Reduced network traffic and improved query performance',
        implementation: [
          'Replace SELECT * with specific column names',
          'Remove unused columns from result set',
          'Update application code accordingly'
        ],
        estimatedImprovement: 25,
        cost: 'LOW',
        risk: 'LOW'
      });
    }

    return recommendations;
  }

  private async analyzePotentialIndexes(dbName: string): Promise<IndexSuggestion[]> {
    const analyses = this.queryAnalyses.get(dbName) || [];
    const slowQueries = analyses.filter(a => a.executionTime > 1000 || a.fullTableScan);
    
    const suggestions: IndexSuggestion[] = [];

    // Mock index suggestions based on slow queries
    const tables = [...new Set(slowQueries.map(q => q.table))];
    
    for (const table of tables) {
      const tableQueries = slowQueries.filter(q => q.table === table);
      
      if (tableQueries.length > 2) {
        suggestions.push({
          id: this.generateSuggestionId(),
          table,
          columns: ['user_id', 'created_at'],
          type: 'COMPOSITE',
          reason: 'Multiple slow queries on this table with WHERE conditions',
          estimatedImprovement: 65,
          estimatedSize: 50 * 1024 * 1024, // 50MB
          priority: 'HIGH',
          queries: tableQueries.map(q => q.query),
          creationSQL: `CREATE INDEX idx_${table}_user_created ON ${table} (user_id, created_at);`,
          impactedOperations: ['SELECT', 'UPDATE', 'DELETE']
        });
      }
    }

    return suggestions;
  }

  public async createOptimizationPlan(
    name: string,
    dbName: string,
    options?: {
      includeIndexes?: boolean;
      includeQueryOptimizations?: boolean;
      maxRisk?: 'LOW' | 'MEDIUM' | 'HIGH';
    }
  ): Promise<OptimizationPlan> {
    const recommendations: QueryRecommendation[] = [];
    const indexSuggestions: IndexSuggestion[] = [];

    // Gather recommendations from recent analyses
    const analyses = this.queryAnalyses.get(dbName) || [];
    const recentAnalyses = analyses.slice(-100); // Last 100 queries

    for (const analysis of recentAnalyses) {
      if (analysis.optimizationScore < 70) {
        recommendations.push(...analysis.recommendations);
      }
    }

    // Get index suggestions
    if (options?.includeIndexes !== false) {
      indexSuggestions.push(...await this.analyzePotentialIndexes(dbName));
    }

    // Filter by risk level
    const maxRisk = options?.maxRisk || 'HIGH';
    const filteredRecommendations = recommendations.filter(r => 
      this.getRiskLevel(r.risk) <= this.getRiskLevel(maxRisk)
    );

    // Create implementation steps
    const implementationSteps = this.createImplementationSteps(filteredRecommendations, indexSuggestions);

    const plan: OptimizationPlan = {
      id: this.generatePlanId(),
      name,
      description: `Optimization plan for ${dbName} database`,
      targetDatabase: dbName,
      recommendations: filteredRecommendations,
      indexSuggestions,
      estimatedImpact: this.calculateEstimatedImpact(filteredRecommendations, indexSuggestions),
      implementationSteps,
      rollbackPlan: this.createRollbackPlan(implementationSteps),
      riskAssessment: this.assessRisk(filteredRecommendations, indexSuggestions),
      schedule: {
        plannedStart: new Date(Date.now() + 24 * 60 * 60 * 1000), // Tomorrow
        estimatedDuration: implementationSteps.reduce((sum, step) => sum + step.estimatedDuration, 0),
        maintenanceWindow: true,
        backupBeforeExecution: true,
        rollbackTimeLimit: 60 // minutes
      },
      status: 'DRAFT'
    };

    this.optimizationPlans.set(plan.id, plan);
    this.emit('plan:created', { database: dbName, plan });

    return plan;
  }

  private getRiskLevel(risk: string): number {
    const levels = { 'LOW': 1, 'MEDIUM': 2, 'HIGH': 3 };
    return levels[risk] || 1;
  }

  private createImplementationSteps(
    recommendations: QueryRecommendation[],
    indexSuggestions: IndexSuggestion[]
  ): ImplementationStep[] {
    const steps: ImplementationStep[] = [];

    // Index creation steps
    indexSuggestions.forEach((suggestion, index) => {
      steps.push({
        id: `index_${index + 1}`,
        name: `Create Index on ${suggestion.table}`,
        description: suggestion.reason,
        type: 'INDEX_CREATION',
        sql: suggestion.creationSQL,
        estimatedDuration: 30, // minutes
        dependencies: [],
        rollbackSQL: `DROP INDEX idx_${suggestion.table}_${suggestion.columns.join('_')};`,
        validation: [
          `SHOW INDEXES FROM ${suggestion.table};`,
          `EXPLAIN SELECT * FROM ${suggestion.table} WHERE ${suggestion.columns[0]} = ?;`
        ]
      });
    });

    // Configuration changes
    const configRecommendations = recommendations.filter(r => r.type === 'QUERY_REWRITE');
    configRecommendations.forEach((rec, index) => {
      steps.push({
        id: `config_${index + 1}`,
        name: rec.title,
        description: rec.description,
        type: 'CONFIG_CHANGE',
        estimatedDuration: 15,
        dependencies: [],
        validation: ['Test query performance', 'Verify application functionality']
      });
    });

    return steps;
  }

  private createRollbackPlan(steps: ImplementationStep[]): RollbackStep[] {
    return steps
      .filter(step => step.rollbackSQL)
      .map(step => ({
        stepId: step.id,
        rollbackSQL: step.rollbackSQL!,
        validation: step.validation,
        description: `Rollback ${step.name}`
      }));
  }

  private calculateEstimatedImpact(
    recommendations: QueryRecommendation[],
    indexSuggestions: IndexSuggestion[]
  ): OptimizationImpact {
    const avgImprovement = [
      ...recommendations.map(r => r.estimatedImprovement),
      ...indexSuggestions.map(s => s.estimatedImprovement)
    ].reduce((sum, imp) => sum + imp, 0) / (recommendations.length + indexSuggestions.length);

    const diskSpaceCost = indexSuggestions.reduce((sum, s) => sum + s.estimatedSize, 0);

    return {
      performanceImprovement: Math.round(avgImprovement),
      diskSpaceSaving: -diskSpaceCost, // Negative because indexes use space
      memoryOptimization: Math.round(avgImprovement * 0.6),
      costReduction: avgImprovement * 10, // $10 per percentage point improvement
      riskLevel: this.calculateOverallRisk(recommendations, indexSuggestions)
    };
  }

  private assessRisk(
    recommendations: QueryRecommendation[],
    indexSuggestions: IndexSuggestion[]
  ): RiskAssessment {
    const riskFactors: RiskFactor[] = [
      {
        category: 'PERFORMANCE',
        description: 'Index creation may temporarily impact performance',
        likelihood: 'MEDIUM',
        impact: 'MEDIUM',
        mitigation: 'Create indexes during maintenance window'
      },
      {
        category: 'AVAILABILITY',
        description: 'Large table modifications may cause locks',
        likelihood: 'LOW',
        impact: 'HIGH',
        mitigation: 'Use online DDL operations where possible'
      }
    ];

    return {
      overallRisk: this.calculateOverallRisk(recommendations, indexSuggestions),
      riskFactors,
      mitigationStrategies: [
        'Perform changes during maintenance window',
        'Create full database backup before execution',
        'Test all changes in staging environment',
        'Monitor performance during implementation'
      ],
      backupRequirements: [
        'Full database backup',
        'Transaction log backup',
        'Schema backup'
      ],
      testingPlan: [
        'Execute plan in staging environment',
        'Run performance tests',
        'Validate application functionality',
        'Test rollback procedures'
      ]
    };
  }

  private calculateOverallRisk(
    recommendations: QueryRecommendation[],
    indexSuggestions: IndexSuggestion[]
  ): 'LOW' | 'MEDIUM' | 'HIGH' | 'CRITICAL' {
    const highRiskCount = recommendations.filter(r => r.risk === 'HIGH').length +
                         indexSuggestions.filter(s => s.priority === 'CRITICAL').length;

    if (highRiskCount > 3) return 'CRITICAL';
    if (highRiskCount > 1) return 'HIGH';
    if (highRiskCount > 0) return 'MEDIUM';
    return 'LOW';
  }

  public async createBaseline(dbName: string, duration: number = 60): Promise<PerformanceBaseline> {
    const startTime = new Date();
    const metrics: DatabaseMetrics[] = [];
    const queries: QueryAnalysis[] = [];

    // Collect metrics for the specified duration
    const interval = setInterval(async () => {
      const config = this.configs.get(dbName);
      if (config) {
        const metric = await this.gatherDatabaseMetrics(dbName, config);
        metrics.push(metric);
      }
    }, 30000); // Every 30 seconds

    // Wait for duration
    await new Promise(resolve => setTimeout(resolve, duration * 60 * 1000));
    clearInterval(interval);

    const endTime = new Date();

    // Get recent query analyses
    const recentQueries = this.queryAnalyses.get(dbName) || [];
    const baselineQueries = recentQueries.filter(q => 
      q.timestamp >= startTime && q.timestamp <= endTime
    );

    const baseline: PerformanceBaseline = {
      id: this.generateBaselineId(),
      timestamp: startTime,
      duration,
      metrics,
      queries: baselineQueries,
      summary: this.createBaselineSummary(metrics, baselineQueries)
    };

    this.baselines.set(baseline.id, baseline);
    this.emit('baseline:created', { database: dbName, baseline });

    return baseline;
  }

  private createBaselineSummary(
    metrics: DatabaseMetrics[],
    queries: QueryAnalysis[]
  ): BaselineSummary {
    const avgQueryTime = queries.length > 0 
      ? queries.reduce((sum, q) => sum + q.executionTime, 0) / queries.length
      : 0;

    const slowQueries = queries.filter(q => q.executionTime > 1000);
    const slowQueryPercentage = queries.length > 0 
      ? (slowQueries.length / queries.length) * 100
      : 0;

    const avgMetrics = this.aggregateMetrics(metrics);

    return {
      averageQueryTime: avgQueryTime,
      queriesPerSecond: avgMetrics.queries.queriesPerSecond,
      slowQueryPercentage,
      connectionUtilization: avgMetrics.connections.connectionUtilization,
      cacheHitRatio: avgMetrics.queries.cacheHitRatio,
      ioLatency: avgMetrics.storage.ioLatency,
      topSlowQueries: slowQueries
        .sort((a, b) => b.executionTime - a.executionTime)
        .slice(0, 10)
    };
  }

  private aggregateMetrics(metrics: DatabaseMetrics[]): DatabaseMetrics {
    if (metrics.length === 0) {
      throw new Error('No metrics to aggregate');
    }

    // Calculate averages for numeric values
    const avg = (values: number[]) => values.reduce((sum, val) => sum + val, 0) / values.length;

    return {
      timestamp: new Date(),
      connections: {
        active: avg(metrics.map(m => m.connections.active)),
        idle: avg(metrics.map(m => m.connections.idle)),
        total: avg(metrics.map(m => m.connections.total)),
        maxConnections: metrics[0].connections.maxConnections,
        connectionUtilization: avg(metrics.map(m => m.connections.connectionUtilization)),
        averageConnectionTime: avg(metrics.map(m => m.connections.averageConnectionTime)),
        connectionErrors: avg(metrics.map(m => m.connections.connectionErrors)),
        timeouts: avg(metrics.map(m => m.connections.timeouts))
      },
      queries: {
        totalQueries: avg(metrics.map(m => m.queries.totalQueries)),
        queriesPerSecond: avg(metrics.map(m => m.queries.queriesPerSecond)),
        averageQueryTime: avg(metrics.map(m => m.queries.averageQueryTime)),
        slowQueries: avg(metrics.map(m => m.queries.slowQueries)),
        slowQueryThreshold: metrics[0].queries.slowQueryThreshold,
        selectQueries: avg(metrics.map(m => m.queries.selectQueries)),
        insertQueries: avg(metrics.map(m => m.queries.insertQueries)),
        updateQueries: avg(metrics.map(m => m.queries.updateQueries)),
        deleteQueries: avg(metrics.map(m => m.queries.deleteQueries)),
        cacheHitRatio: avg(metrics.map(m => m.queries.cacheHitRatio))
      },
      storage: {
        totalSize: avg(metrics.map(m => m.storage.totalSize)),
        dataSize: avg(metrics.map(m => m.storage.dataSize)),
        indexSize: avg(metrics.map(m => m.storage.indexSize)),
        freeSpace: avg(metrics.map(m => m.storage.freeSpace)),
        fragmentedSpace: avg(metrics.map(m => m.storage.fragmentedSpace)),
        growthRate: avg(metrics.map(m => m.storage.growthRate)),
        ioReads: avg(metrics.map(m => m.storage.ioReads)),
        ioWrites: avg(metrics.map(m => m.storage.ioWrites)),
        ioLatency: avg(metrics.map(m => m.storage.ioLatency))
      },
      cache: {
        bufferPoolSize: avg(metrics.map(m => m.cache.bufferPoolSize)),
        bufferPoolUtilization: avg(metrics.map(m => m.cache.bufferPoolUtilization)),
        cacheHitRatio: avg(metrics.map(m => m.cache.cacheHitRatio)),
        cacheMissRatio: avg(metrics.map(m => m.cache.cacheMissRatio)),
        evictions: avg(metrics.map(m => m.cache.evictions)),
        dirtyPages: avg(metrics.map(m => m.cache.dirtyPages))
      },
      locks: {
        totalLocks: avg(metrics.map(m => m.locks.totalLocks)),
        lockWaits: avg(metrics.map(m => m.locks.lockWaits)),
        deadlocks: avg(metrics.map(m => m.locks.deadlocks)),
        averageLockTime: avg(metrics.map(m => m.locks.averageLockTime)),
        lockTimeouts: avg(metrics.map(m => m.locks.lockTimeouts)),
        blockingQueries: avg(metrics.map(m => m.locks.blockingQueries))
      }
    };
  }

  // Public API Methods
  public getDatabaseMetrics(dbName: string, limit: number = 100): DatabaseMetrics[] {
    const metrics = this.metrics.get(dbName) || [];
    return metrics.slice(-limit);
  }

  public getQueryAnalyses(dbName: string, limit: number = 100): QueryAnalysis[] {
    const analyses = this.queryAnalyses.get(dbName) || [];
    return analyses.slice(-limit);
  }

  public getOptimizationPlans(): OptimizationPlan[] {
    return Array.from(this.optimizationPlans.values());
  }

  public getOptimizationPlan(planId: string): OptimizationPlan | null {
    return this.optimizationPlans.get(planId) || null;
  }

  public getBaselines(): PerformanceBaseline[] {
    return Array.from(this.baselines.values());
  }

  public getSlowQueries(dbName: string, threshold: number = 1000): QueryAnalysis[] {
    const analyses = this.queryAnalyses.get(dbName) || [];
    return analyses
      .filter(a => a.executionTime > threshold)
      .sort((a, b) => b.executionTime - a.executionTime);
  }

  public getDatabaseHealth(dbName: string): {
    score: number;
    status: 'EXCELLENT' | 'GOOD' | 'WARNING' | 'CRITICAL';
    issues: string[];
    recommendations: string[];
  } {
    const metrics = this.getDatabaseMetrics(dbName, 1)[0];
    if (!metrics) {
      return {
        score: 0,
        status: 'CRITICAL',
        issues: ['No metrics available'],
        recommendations: ['Start monitoring the database']
      };
    }

    let score = 100;
    const issues: string[] = [];
    const recommendations: string[] = [];

    // Check various health indicators
    if (metrics.connections.connectionUtilization > 80) {
      score -= 15;
      issues.push('High connection utilization');
      recommendations.push('Increase connection pool size or optimize connection usage');
    }

    if (metrics.queries.cacheHitRatio < 80) {
      score -= 10;
      issues.push('Low cache hit ratio');
      recommendations.push('Increase buffer pool size or optimize queries');
    }

    if (metrics.storage.ioLatency > 15) {
      score -= 20;
      issues.push('High I/O latency');
      recommendations.push('Consider faster storage or query optimization');
    }

    if (metrics.locks.deadlocks > 0) {
      score -= 10;
      issues.push('Deadlocks detected');
      recommendations.push('Review transaction logic and query ordering');
    }

    const status = score >= 90 ? 'EXCELLENT' :
                  score >= 75 ? 'GOOD' :
                  score >= 60 ? 'WARNING' : 'CRITICAL';

    return { score, status, issues, recommendations };
  }

  // ID Generators
  private generateAnalysisId(): string {
    return `analysis_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  }

  private generateSuggestionId(): string {
    return `suggestion_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  }

  private generatePlanId(): string {
    return `plan_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  }

  private generateBaselineId(): string {
    return `baseline_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
  }
}

export default DatabaseOptimizer; 