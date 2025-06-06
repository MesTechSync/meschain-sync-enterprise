// 🏗️ MesChain-Sync Enterprise - Data Warehouse Integration Service
// Advanced ETL and Data Analytics Pipeline

import { EventEmitter } from 'events';

// ====================================
// 🎯 TYPES & INTERFACES
// ====================================

export interface DataWarehouseConfig {
  provider: 'postgresql' | 'bigquery' | 'snowflake' | 'redshift' | 'clickhouse';
  connection: {
    host: string;
    port: number;
    database: string;
    username: string;
    password: string;
    ssl?: boolean;
    schema?: string;
  };
  etl: {
    batchSize: number;
    refreshInterval: number; // minutes
    retentionDays: number;
    compressionLevel: number;
  };
  aggregations: {
    realTime: boolean;
    precomputed: string[];
    materialized: string[];
  };
}

export interface DataSchema {
  table: string;
  columns: {
    name: string;
    type: 'string' | 'number' | 'date' | 'boolean' | 'json';
    nullable: boolean;
    primaryKey?: boolean;
    foreignKey?: string;
    indexed?: boolean;
  }[];
  partitioning?: {
    column: string;
    type: 'range' | 'hash' | 'list';
    strategy: string;
  };
  clustering?: string[];
}

export interface ETLJob {
  id: string;
  name: string;
  sourceTable: string;
  targetTable: string;
  transformations: ETLTransformation[];
  schedule: {
    frequency: 'realtime' | 'hourly' | 'daily' | 'weekly';
    time?: string;
    timezone?: string;
  };
  status: 'running' | 'stopped' | 'error' | 'pending';
  lastRun?: Date;
  nextRun?: Date;
  metrics: {
    rowsProcessed: number;
    executionTime: number;
    errorCount: number;
    successRate: number;
  };
}

export interface ETLTransformation {
  type: 'filter' | 'aggregate' | 'join' | 'pivot' | 'custom';
  config: Record<string, any>;
  validation?: {
    rules: string[];
    onError: 'skip' | 'fail' | 'log';
  };
}

export interface AnalyticsQuery {
  id: string;
  name: string;
  description: string;
  sql: string;
  parameters: {
    name: string;
    type: string;
    defaultValue?: any;
    required: boolean;
  }[];
  cacheTTL: number; // seconds
  tags: string[];
  category: string;
  complexity: 'low' | 'medium' | 'high';
  estimatedCost?: number;
}

export interface DataCube {
  name: string;
  dimensions: {
    name: string;
    hierarchy?: string[];
    granularity: string[];
  }[];
  measures: {
    name: string;
    aggregationType: 'sum' | 'avg' | 'count' | 'min' | 'max' | 'distinct';
    format: string;
  }[];
  filters: {
    dimension: string;
    operator: 'eq' | 'ne' | 'gt' | 'lt' | 'in' | 'between';
    value: any;
  }[];
}

// ====================================
// 📊 DATA WAREHOUSE SCHEMAS
// ====================================

export const WAREHOUSE_SCHEMAS: DataSchema[] = [
  {
    table: 'fact_sales',
    columns: [
      { name: 'id', type: 'string', nullable: false, primaryKey: true },
      { name: 'order_id', type: 'string', nullable: false, indexed: true },
      { name: 'product_id', type: 'string', nullable: false, foreignKey: 'dim_products.id' },
      { name: 'customer_id', type: 'string', nullable: false, foreignKey: 'dim_customers.id' },
      { name: 'marketplace_id', type: 'string', nullable: false, foreignKey: 'dim_marketplaces.id' },
      { name: 'date_id', type: 'string', nullable: false, foreignKey: 'dim_dates.id' },
      { name: 'quantity', type: 'number', nullable: false },
      { name: 'unit_price', type: 'number', nullable: false },
      { name: 'total_amount', type: 'number', nullable: false },
      { name: 'cost', type: 'number', nullable: true },
      { name: 'profit', type: 'number', nullable: true },
      { name: 'currency', type: 'string', nullable: false },
      { name: 'status', type: 'string', nullable: false },
      { name: 'created_at', type: 'date', nullable: false },
      { name: 'updated_at', type: 'date', nullable: false },
    ],
    partitioning: {
      column: 'created_at',
      type: 'range',
      strategy: 'monthly',
    },
    clustering: ['marketplace_id', 'date_id'],
  },
  {
    table: 'dim_products',
    columns: [
      { name: 'id', type: 'string', nullable: false, primaryKey: true },
      { name: 'sku', type: 'string', nullable: false, indexed: true },
      { name: 'name', type: 'string', nullable: false },
      { name: 'category_id', type: 'string', nullable: false, foreignKey: 'dim_categories.id' },
      { name: 'brand', type: 'string', nullable: true },
      { name: 'price', type: 'number', nullable: false },
      { name: 'cost', type: 'number', nullable: true },
      { name: 'weight', type: 'number', nullable: true },
      { name: 'dimensions', type: 'json', nullable: true },
      { name: 'attributes', type: 'json', nullable: true },
      { name: 'active', type: 'boolean', nullable: false },
      { name: 'created_at', type: 'date', nullable: false },
      { name: 'updated_at', type: 'date', nullable: false },
    ],
  },
  {
    table: 'dim_customers',
    columns: [
      { name: 'id', type: 'string', nullable: false, primaryKey: true },
      { name: 'email', type: 'string', nullable: false, indexed: true },
      { name: 'first_name', type: 'string', nullable: true },
      { name: 'last_name', type: 'string', nullable: true },
      { name: 'phone', type: 'string', nullable: true },
      { name: 'gender', type: 'string', nullable: true },
      { name: 'birth_date', type: 'date', nullable: true },
      { name: 'registration_date', type: 'date', nullable: false },
      { name: 'segment', type: 'string', nullable: false },
      { name: 'lifetime_value', type: 'number', nullable: false },
      { name: 'total_orders', type: 'number', nullable: false },
      { name: 'location', type: 'json', nullable: true },
      { name: 'preferences', type: 'json', nullable: true },
      { name: 'active', type: 'boolean', nullable: false },
    ],
  },
  {
    table: 'dim_marketplaces',
    columns: [
      { name: 'id', type: 'string', nullable: false, primaryKey: true },
      { name: 'name', type: 'string', nullable: false },
      { name: 'code', type: 'string', nullable: false, indexed: true },
      { name: 'country', type: 'string', nullable: false },
      { name: 'currency', type: 'string', nullable: false },
      { name: 'commission_rate', type: 'number', nullable: false },
      { name: 'api_config', type: 'json', nullable: true },
      { name: 'active', type: 'boolean', nullable: false },
    ],
  },
  {
    table: 'dim_dates',
    columns: [
      { name: 'id', type: 'string', nullable: false, primaryKey: true },
      { name: 'date', type: 'date', nullable: false, indexed: true },
      { name: 'year', type: 'number', nullable: false },
      { name: 'quarter', type: 'number', nullable: false },
      { name: 'month', type: 'number', nullable: false },
      { name: 'week', type: 'number', nullable: false },
      { name: 'day', type: 'number', nullable: false },
      { name: 'day_of_week', type: 'number', nullable: false },
      { name: 'is_weekend', type: 'boolean', nullable: false },
      { name: 'is_holiday', type: 'boolean', nullable: false },
      { name: 'fiscal_year', type: 'number', nullable: false },
      { name: 'fiscal_quarter', type: 'number', nullable: false },
    ],
  },
];

// ====================================
// 📊 PREDEFINED ANALYTICS QUERIES
// ====================================

export const ANALYTICS_QUERIES: AnalyticsQuery[] = [
  {
    id: 'revenue_by_marketplace',
    name: 'Marketplace Revenue Analysis',
    description: 'Revenue breakdown by marketplace with growth rates',
    sql: `
      SELECT 
        m.name as marketplace,
        SUM(s.total_amount) as revenue,
        COUNT(DISTINCT s.order_id) as orders,
        AVG(s.total_amount) as avg_order_value,
        LAG(SUM(s.total_amount)) OVER (PARTITION BY m.id ORDER BY d.year, d.month) as prev_revenue,
        ((SUM(s.total_amount) - LAG(SUM(s.total_amount)) OVER (PARTITION BY m.id ORDER BY d.year, d.month)) / 
         LAG(SUM(s.total_amount)) OVER (PARTITION BY m.id ORDER BY d.year, d.month)) * 100 as growth_rate
      FROM fact_sales s
      JOIN dim_marketplaces m ON s.marketplace_id = m.id
      JOIN dim_dates d ON s.date_id = d.id
      WHERE d.date BETWEEN :start_date AND :end_date
      GROUP BY m.id, m.name, d.year, d.month
      ORDER BY revenue DESC
    `,
    parameters: [
      { name: 'start_date', type: 'date', required: true },
      { name: 'end_date', type: 'date', required: true },
    ],
    cacheTTL: 3600,
    tags: ['revenue', 'marketplace', 'growth'],
    category: 'sales',
    complexity: 'medium',
    estimatedCost: 50,
  },
  {
    id: 'customer_lifetime_value',
    name: 'Customer Lifetime Value Analysis',
    description: 'CLV calculation with segmentation',
    sql: `
      WITH customer_metrics AS (
        SELECT 
          c.id,
          c.segment,
          c.registration_date,
          COUNT(DISTINCT s.order_id) as total_orders,
          SUM(s.total_amount) as total_revenue,
          AVG(s.total_amount) as avg_order_value,
          MAX(d.date) as last_order_date,
          MIN(d.date) as first_order_date,
          EXTRACT(days FROM MAX(d.date) - MIN(d.date)) as customer_lifespan
        FROM dim_customers c
        LEFT JOIN fact_sales s ON c.id = s.customer_id
        LEFT JOIN dim_dates d ON s.date_id = d.id
        WHERE c.active = true
        GROUP BY c.id, c.segment, c.registration_date
      )
      SELECT 
        segment,
        COUNT(*) as customer_count,
        AVG(total_revenue) as avg_clv,
        AVG(total_orders) as avg_orders,
        AVG(avg_order_value) as avg_order_value,
        AVG(customer_lifespan) as avg_lifespan_days,
        PERCENTILE_CONT(0.5) WITHIN GROUP (ORDER BY total_revenue) as median_clv,
        PERCENTILE_CONT(0.9) WITHIN GROUP (ORDER BY total_revenue) as clv_90th_percentile
      FROM customer_metrics
      GROUP BY segment
      ORDER BY avg_clv DESC
    `,
    parameters: [],
    cacheTTL: 7200,
    tags: ['clv', 'segmentation', 'customers'],
    category: 'customer',
    complexity: 'high',
    estimatedCost: 120,
  },
  {
    id: 'product_performance',
    name: 'Product Performance Dashboard',
    description: 'Top performing products with profitability metrics',
    sql: `
      SELECT 
        p.name as product_name,
        p.sku,
        p.brand,
        cat.name as category,
        SUM(s.quantity) as total_sold,
        SUM(s.total_amount) as revenue,
        SUM(s.profit) as profit,
        (SUM(s.profit) / SUM(s.total_amount)) * 100 as profit_margin,
        AVG(s.unit_price) as avg_price,
        COUNT(DISTINCT s.customer_id) as unique_customers,
        COUNT(DISTINCT s.marketplace_id) as marketplace_count
      FROM fact_sales s
      JOIN dim_products p ON s.product_id = p.id
      JOIN dim_categories cat ON p.category_id = cat.id
      JOIN dim_dates d ON s.date_id = d.id
      WHERE d.date BETWEEN :start_date AND :end_date
        AND (:category IS NULL OR cat.name = :category)
        AND (:min_revenue IS NULL OR SUM(s.total_amount) >= :min_revenue)
      GROUP BY p.id, p.name, p.sku, p.brand, cat.name
      HAVING SUM(s.quantity) > 0
      ORDER BY revenue DESC
      LIMIT :limit
    `,
    parameters: [
      { name: 'start_date', type: 'date', required: true },
      { name: 'end_date', type: 'date', required: true },
      { name: 'category', type: 'string', required: false },
      { name: 'min_revenue', type: 'number', required: false },
      { name: 'limit', type: 'number', defaultValue: 100, required: false },
    ],
    cacheTTL: 1800,
    tags: ['products', 'performance', 'profitability'],
    category: 'products',
    complexity: 'medium',
    estimatedCost: 75,
  },
  {
    id: 'seasonal_trends',
    name: 'Seasonal Sales Trends',
    description: 'Year-over-year seasonal pattern analysis',
    sql: `
      WITH seasonal_data AS (
        SELECT 
          d.year,
          d.quarter,
          d.month,
          CASE 
            WHEN d.month IN (12, 1, 2) THEN 'Winter'
            WHEN d.month IN (3, 4, 5) THEN 'Spring'
            WHEN d.month IN (6, 7, 8) THEN 'Summer'
            WHEN d.month IN (9, 10, 11) THEN 'Fall'
          END as season,
          SUM(s.total_amount) as revenue,
          COUNT(DISTINCT s.order_id) as orders,
          AVG(s.total_amount) as avg_order_value
        FROM fact_sales s
        JOIN dim_dates d ON s.date_id = d.id
        WHERE d.year >= EXTRACT(year FROM CURRENT_DATE) - 3
        GROUP BY d.year, d.quarter, d.month
      )
      SELECT 
        season,
        month,
        AVG(revenue) as avg_revenue,
        STDDEV(revenue) as revenue_stddev,
        MIN(revenue) as min_revenue,
        MAX(revenue) as max_revenue,
        AVG(orders) as avg_orders,
        COUNT(*) as data_points,
        (MAX(revenue) - MIN(revenue)) / AVG(revenue) * 100 as volatility_percent
      FROM seasonal_data
      GROUP BY season, month
      ORDER BY month
    `,
    parameters: [],
    cacheTTL: 14400,
    tags: ['seasonal', 'trends', 'patterns'],
    category: 'analytics',
    complexity: 'high',
    estimatedCost: 90,
  },
];

// ====================================
// 🏭 DATA WAREHOUSE SERVICE
// ====================================

export class DataWarehouseService extends EventEmitter {
  private config: DataWarehouseConfig;
  private connectionPool: any;
  private etlJobs: Map<string, ETLJob> = new Map();
  private queryCache: Map<string, { data: any; timestamp: number; ttl: number }> = new Map();

  constructor(config: DataWarehouseConfig) {
    super();
    this.config = config;
    this.initializeConnection();
    this.setupETLJobs();
    this.startQueryCacheCleaner();
  }

  // ====================================
  // 🔌 CONNECTION MANAGEMENT
  // ====================================

  private async initializeConnection(): Promise<void> {
    try {
      switch (this.config.provider) {
        case 'postgresql':
          await this.initializePostgreSQL();
          break;
        case 'bigquery':
          await this.initializeBigQuery();
          break;
        case 'snowflake':
          await this.initializeSnowflake();
          break;
        case 'redshift':
          await this.initializeRedshift();
          break;
        case 'clickhouse':
          await this.initializeClickHouse();
          break;
        default:
          throw new Error(`Unsupported provider: ${this.config.provider}`);
      }
      
      this.emit('connected', { provider: this.config.provider });
    } catch (error) {
      this.emit('error', { type: 'connection', error: error.message });
      throw error;
    }
  }

  private async initializePostgreSQL(): Promise<void> {
    // PostgreSQL connection setup
    const { Pool } = require('pg');
    this.connectionPool = new Pool({
      host: this.config.connection.host,
      port: this.config.connection.port,
      database: this.config.connection.database,
      user: this.config.connection.username,
      password: this.config.connection.password,
      ssl: this.config.connection.ssl,
      max: 20,
      idleTimeoutMillis: 30000,
    });
  }

  private async initializeBigQuery(): Promise<void> {
    // BigQuery connection setup
    const { BigQuery } = require('@google-cloud/bigquery');
    this.connectionPool = new BigQuery({
      projectId: this.config.connection.database,
      keyFilename: process.env.GOOGLE_APPLICATION_CREDENTIALS,
    });
  }

  private async initializeSnowflake(): Promise<void> {
    // Snowflake connection setup
    const snowflake = require('snowflake-sdk');
    this.connectionPool = snowflake.createConnection({
      account: this.config.connection.host,
      username: this.config.connection.username,
      password: this.config.connection.password,
      database: this.config.connection.database,
      schema: this.config.connection.schema || 'PUBLIC',
    });
  }

  private async initializeRedshift(): Promise<void> {
    // Redshift connection setup (similar to PostgreSQL)
    const { Pool } = require('pg');
    this.connectionPool = new Pool({
      host: this.config.connection.host,
      port: this.config.connection.port || 5439,
      database: this.config.connection.database,
      user: this.config.connection.username,
      password: this.config.connection.password,
      ssl: true,
    });
  }

  private async initializeClickHouse(): Promise<void> {
    // ClickHouse connection setup
    const { ClickHouse } = require('clickhouse');
    this.connectionPool = new ClickHouse({
      host: this.config.connection.host,
      port: this.config.connection.port || 8123,
      database: this.config.connection.database,
      username: this.config.connection.username,
      password: this.config.connection.password,
    });
  }

  // ====================================
  // 📊 QUERY EXECUTION
  // ====================================

  async executeQuery(
    queryId: string, 
    parameters: Record<string, any> = {},
    useCache: boolean = true
  ): Promise<any> {
    const query = ANALYTICS_QUERIES.find(q => q.id === queryId);
    if (!query) {
      throw new Error(`Query not found: ${queryId}`);
    }

    // Check cache
    const cacheKey = `${queryId}:${JSON.stringify(parameters)}`;
    if (useCache && this.queryCache.has(cacheKey)) {
      const cached = this.queryCache.get(cacheKey)!;
      if (Date.now() - cached.timestamp < cached.ttl * 1000) {
        this.emit('query:cache_hit', { queryId, parameters });
        return cached.data;
      }
    }

    try {
      const startTime = Date.now();
      
      // Replace parameters in SQL
      let sql = query.sql;
      for (const [key, value] of Object.entries(parameters)) {
        sql = sql.replace(new RegExp(`:${key}`, 'g'), this.formatParameter(value));
      }

      // Execute query
      const result = await this.executeSQL(sql);
      const executionTime = Date.now() - startTime;

      // Cache result
      if (useCache && query.cacheTTL > 0) {
        this.queryCache.set(cacheKey, {
          data: result,
          timestamp: Date.now(),
          ttl: query.cacheTTL,
        });
      }

      this.emit('query:executed', {
        queryId,
        parameters,
        executionTime,
        rowCount: result.length,
        cost: query.estimatedCost,
      });

      return result;
    } catch (error) {
      this.emit('query:error', { queryId, parameters, error: error.message });
      throw error;
    }
  }

  private async executeSQL(sql: string): Promise<any[]> {
    switch (this.config.provider) {
      case 'postgresql':
      case 'redshift':
        const pgResult = await this.connectionPool.query(sql);
        return pgResult.rows;
        
      case 'bigquery':
        const [bqRows] = await this.connectionPool.query(sql);
        return Array.from(bqRows);
        
      case 'snowflake':
        return new Promise((resolve, reject) => {
          this.connectionPool.execute({
            sqlText: sql,
            complete: (err: any, stmt: any, rows: any) => {
              if (err) reject(err);
              else resolve(rows);
            },
          });
        });
        
      case 'clickhouse':
        const chResult = await this.connectionPool.query(sql).toPromise();
        return chResult;
        
      default:
        throw new Error(`Unsupported provider: ${this.config.provider}`);
    }
  }

  private formatParameter(value: any): string {
    if (typeof value === 'string') {
      return `'${value.replace(/'/g, "''")}'`;
    } else if (value instanceof Date) {
      return `'${value.toISOString()}'`;
    } else if (value === null || value === undefined) {
      return 'NULL';
    } else {
      return String(value);
    }
  }

  // ====================================
  // 🔄 ETL JOB MANAGEMENT
  // ====================================

  private setupETLJobs(): void {
    // Define standard ETL jobs
    const salesETL: ETLJob = {
      id: 'sales_etl',
      name: 'Sales Data ETL',
      sourceTable: 'raw_orders',
      targetTable: 'fact_sales',
      transformations: [
        {
          type: 'filter',
          config: { condition: 'status != "cancelled"' },
        },
        {
          type: 'aggregate',
          config: {
            groupBy: ['order_id', 'product_id'],
            measures: { quantity: 'sum', amount: 'sum' },
          },
        },
      ],
      schedule: { frequency: 'hourly', time: '00' },
      status: 'running',
      metrics: {
        rowsProcessed: 0,
        executionTime: 0,
        errorCount: 0,
        successRate: 100,
      },
    };

    const customerETL: ETLJob = {
      id: 'customer_etl',
      name: 'Customer Dimension ETL',
      sourceTable: 'raw_customers',
      targetTable: 'dim_customers',
      transformations: [
        {
          type: 'custom',
          config: {
            script: 'calculate_customer_segment_and_clv',
          },
        },
      ],
      schedule: { frequency: 'daily', time: '02:00' },
      status: 'running',
      metrics: {
        rowsProcessed: 0,
        executionTime: 0,
        errorCount: 0,
        successRate: 100,
      },
    };

    this.etlJobs.set(salesETL.id, salesETL);
    this.etlJobs.set(customerETL.id, customerETL);
  }

  async runETLJob(jobId: string): Promise<void> {
    const job = this.etlJobs.get(jobId);
    if (!job) {
      throw new Error(`ETL job not found: ${jobId}`);
    }

    try {
      job.status = 'running';
      job.lastRun = new Date();
      
      const startTime = Date.now();
      
      // Execute transformations
      for (const transformation of job.transformations) {
        await this.executeTransformation(job, transformation);
      }
      
      const executionTime = Date.now() - startTime;
      
      job.metrics.executionTime = executionTime;
      job.status = 'stopped';
      
      this.emit('etl:completed', { jobId, executionTime });
    } catch (error) {
      job.status = 'error';
      job.metrics.errorCount++;
      this.emit('etl:error', { jobId, error: error.message });
      throw error;
    }
  }

  private async executeTransformation(job: ETLJob, transformation: ETLTransformation): Promise<void> {
    // Implementation would depend on transformation type
    switch (transformation.type) {
      case 'filter':
        await this.executeFilterTransformation(job, transformation);
        break;
      case 'aggregate':
        await this.executeAggregateTransformation(job, transformation);
        break;
      case 'join':
        await this.executeJoinTransformation(job, transformation);
        break;
      case 'custom':
        await this.executeCustomTransformation(job, transformation);
        break;
    }
  }

  private async executeFilterTransformation(job: ETLJob, transformation: ETLTransformation): Promise<void> {
    // Filter transformation logic
  }

  private async executeAggregateTransformation(job: ETLJob, transformation: ETLTransformation): Promise<void> {
    // Aggregate transformation logic
  }

  private async executeJoinTransformation(job: ETLJob, transformation: ETLTransformation): Promise<void> {
    // Join transformation logic
  }

  private async executeCustomTransformation(job: ETLJob, transformation: ETLTransformation): Promise<void> {
    // Custom transformation logic
  }

  // ====================================
  // 🗂️ DATA CUBE OPERATIONS
  // ====================================

  async queryDataCube(cube: DataCube): Promise<any[]> {
    // Build OLAP query from cube definition
    const sql = this.buildCubeQuery(cube);
    return this.executeSQL(sql);
  }

  private buildCubeQuery(cube: DataCube): string {
    const dimensions = cube.dimensions.map(d => d.name).join(', ');
    const measures = cube.measures.map(m => 
      `${m.aggregationType.toUpperCase()}(${m.name}) as ${m.name}`
    ).join(', ');
    
    let sql = `SELECT ${dimensions}, ${measures} FROM ${cube.name}`;
    
    if (cube.filters.length > 0) {
      const conditions = cube.filters.map(f => 
        `${f.dimension} ${this.getOperatorSQL(f.operator)} ${this.formatParameter(f.value)}`
      ).join(' AND ');
      sql += ` WHERE ${conditions}`;
    }
    
    sql += ` GROUP BY ${dimensions}`;
    
    return sql;
  }

  private getOperatorSQL(operator: string): string {
    const operatorMap: Record<string, string> = {
      'eq': '=',
      'ne': '!=',
      'gt': '>',
      'lt': '<',
      'in': 'IN',
      'between': 'BETWEEN',
    };
    return operatorMap[operator] || '=';
  }

  // ====================================
  // 🧹 CACHE MANAGEMENT
  // ====================================

  private startQueryCacheCleaner(): void {
    setInterval(() => {
      const now = Date.now();
      for (const [key, cached] of this.queryCache.entries()) {
        if (now - cached.timestamp > cached.ttl * 1000) {
          this.queryCache.delete(key);
        }
      }
    }, 60000); // Clean every minute
  }

  clearCache(pattern?: string): void {
    if (pattern) {
      for (const key of this.queryCache.keys()) {
        if (key.includes(pattern)) {
          this.queryCache.delete(key);
        }
      }
    } else {
      this.queryCache.clear();
    }
  }

  // ====================================
  // 📈 METRICS & MONITORING
  // ====================================

  getMetrics(): {
    queries: { total: number; cached: number; errors: number };
    etl: { jobs: number; running: number; errors: number };
    cache: { size: number; hitRate: number };
  } {
    const queryMetrics = {
      total: this.listenerCount('query:executed'),
      cached: this.listenerCount('query:cache_hit'),
      errors: this.listenerCount('query:error'),
    };

    const etlMetrics = {
      jobs: this.etlJobs.size,
      running: Array.from(this.etlJobs.values()).filter(j => j.status === 'running').length,
      errors: Array.from(this.etlJobs.values()).filter(j => j.status === 'error').length,
    };

    const cacheMetrics = {
      size: this.queryCache.size,
      hitRate: queryMetrics.cached / Math.max(queryMetrics.total, 1) * 100,
    };

    return { queries: queryMetrics, etl: etlMetrics, cache: cacheMetrics };
  }

  // ====================================
  // 🔌 LIFECYCLE MANAGEMENT
  // ====================================

  async disconnect(): Promise<void> {
    try {
      if (this.connectionPool) {
        switch (this.config.provider) {
          case 'postgresql':
          case 'redshift':
            await this.connectionPool.end();
            break;
          case 'snowflake':
            await this.connectionPool.destroy();
            break;
        }
      }
      this.emit('disconnected');
    } catch (error) {
      this.emit('error', { type: 'disconnect', error: error.message });
      throw error;
    }
  }
}

export default DataWarehouseService; 