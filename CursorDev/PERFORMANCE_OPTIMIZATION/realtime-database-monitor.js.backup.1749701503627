/**
 * 🎯 ATOM-MZ006: Real-time Database Performance Monitor
 * VSCode Team Backend Development - Phase 2
 * 
 * Advanced real-time monitoring for database performance
 * Target: <30ms query times with proactive optimization
 * 
 * @author MezBjen (VSCode Team)
 * @version 2.0.0
 * @date June 10, 2025
 * @integration Cursor Team Support Ready
 */

const EventEmitter = require('events');
const mysql = require('mysql2/promise');
const redis = require('redis');

class RealtimeDatabaseMonitor extends EventEmitter {
    constructor(config) {
        super();
        this.config = {
            mysql: config.mysql || {},
            redis: config.redis || {},
            thresholds: {
                query_time_warning: 30,     // milliseconds
                query_time_critical: 50,    // milliseconds
                connection_warning: 80,     // percentage
                connection_critical: 95,    // percentage
                memory_warning: 75,         // percentage
                memory_critical: 90         // percentage
            },
            monitoring: {
                interval: 1000,             // 1 second
                retention_hours: 24,        // 24 hours
                batch_size: 100,
                alert_cooldown: 300000      // 5 minutes
            }
        };
        
        this.metrics = {
            query_times: [],
            active_connections: 0,
            slow_queries: [],
            optimization_suggestions: [],
            performance_score: 100
        };
        
        this.alerts = new Map();
        this.monitoring_active = false;
        
        console.log('🔍 ATOM-MZ006: Real-time Database Monitor - Initialized');
        this.initializeMonitoring();
    }
    
    /**
     * Initialize real-time monitoring system
     */
    async initializeMonitoring() {
        try {
            // Initialize database connections
            await this.initializeDatabaseConnections();
            
            // Setup Redis for caching metrics
            await this.initializeRedisCache();
            
            // Start monitoring loops
            this.startPerformanceMonitoring();
            this.startQueryAnalysis();
            this.startConnectionMonitoring();
            
            this.monitoring_active = true;
            this.emit('monitor_started', { timestamp: new Date() });
            
            console.log('✅ ATOM-MZ006: All monitoring systems active');
            
        } catch (error) {
            console.error('🚨 ATOM-MZ006 Initialization Error:', error);
            this.emit('monitor_error', error);
        }
    }
    
    /**
     * Initialize database connections with optimization
     */
    async initializeDatabaseConnections() {
        this.db_pool = mysql.createPool({
            ...this.config.mysql,
            connectionLimit: 20,
            acquireTimeout: 60000,
            timeout: 60000,
            reconnect: true,
            // Performance optimizations
            supportBigNumbers: true,
            bigNumberStrings: true,
            dateStrings: false,
            debug: false,
            // Connection health monitoring
            enableKeepAlive: true,
            keepAliveInitialDelay: 0
        });
        
        // Test connection and verify performance
        const connection = await this.db_pool.getConnection();
        await connection.ping();
        connection.release();
        
        console.log('🔗 Database connection pool initialized with performance optimization');
    }
    
    /**
     * Initialize Redis cache for metrics storage
     */
    async initializeRedisCache() {
        this.redis_client = redis.createClient(this.config.redis);
        
        this.redis_client.on('error', (err) => {
            console.error('🚨 Redis connection error:', err);
            this.emit('redis_error', err);
        });
        
        await this.redis_client.connect();
        console.log('📊 Redis cache initialized for metrics storage');
    }
    
    /**
     * Start real-time performance monitoring
     */
    startPerformanceMonitoring() {
        this.performance_interval = setInterval(async () => {
            try {
                const metrics = await this.collectPerformanceMetrics();
                await this.analyzePerformance(metrics);
                await this.cacheMetrics(metrics);
                
                // Emit real-time metrics for Cursor team integration
                this.emit('performance_metrics', metrics);
                
            } catch (error) {
                console.error('🚨 Performance monitoring error:', error);
            }
        }, this.config.monitoring.interval);
        
        console.log('📈 Real-time performance monitoring started');
    }
    
    /**
     * Collect comprehensive performance metrics
     */
    async collectPerformanceMetrics() {
        const start_time = process.hrtime.bigint();
        
        try {
            // Database performance metrics
            const [db_metrics] = await this.db_pool.execute(`
                SELECT 
                    COUNT(*) as active_connections,
                    AVG(TIME) as avg_query_time,
                    MAX(TIME) as max_query_time,
                    COUNT(CASE WHEN TIME > ? THEN 1 END) as slow_queries
                FROM INFORMATION_SCHEMA.PROCESSLIST 
                WHERE COMMAND != 'Sleep'
            `, [this.config.thresholds.query_time_warning]);
            
            // System metrics
            const [system_metrics] = await this.db_pool.execute(`
                SHOW STATUS WHERE Variable_name IN (
                    'Connections', 'Max_used_connections', 'Threads_connected',
                    'Queries', 'Slow_queries', 'Innodb_buffer_pool_pages_data',
                    'Innodb_buffer_pool_pages_total'
                )
            `);
            
            const metrics = {
                timestamp: new Date(),
                collection_time: Number(process.hrtime.bigint() - start_time) / 1000000, // ms
                database: this.processDbMetrics(db_metrics[0]),
                system: this.processSystemMetrics(system_metrics),
                performance_score: this.calculatePerformanceScore(db_metrics[0], system_metrics)
            };
            
            return metrics;
            
        } catch (error) {
            console.error('🚨 Metrics collection error:', error);
            return null;
        }
    }
    
    /**
     * Process database metrics
     */
    processDbMetrics(raw_metrics) {
        return {
            active_connections: raw_metrics.active_connections || 0,
            avg_query_time: parseFloat(raw_metrics.avg_query_time) || 0,
            max_query_time: parseFloat(raw_metrics.max_query_time) || 0,
            slow_queries: raw_metrics.slow_queries || 0,
            connection_usage: (raw_metrics.active_connections / 20) * 100 // Based on pool limit
        };
    }
    
    /**
     * Process system metrics
     */
    processSystemMetrics(system_metrics) {
        const metrics_map = {};
        system_metrics.forEach(metric => {
            metrics_map[metric.Variable_name] = parseInt(metric.Value);
        });
        
        return {
            total_connections: metrics_map.Connections || 0,
            max_used_connections: metrics_map.Max_used_connections || 0,
            threads_connected: metrics_map.Threads_connected || 0,
            total_queries: metrics_map.Queries || 0,
            slow_queries: metrics_map.Slow_queries || 0,
            buffer_pool_usage: (metrics_map.Innodb_buffer_pool_pages_data / metrics_map.Innodb_buffer_pool_pages_total) * 100 || 0
        };
    }
    
    /**
     * Calculate overall performance score
     */
    calculatePerformanceScore(db_metrics, system_metrics) {
        let score = 100;
        
        // Query time penalty
        if (db_metrics.avg_query_time > this.config.thresholds.query_time_critical) {
            score -= 30;
        } else if (db_metrics.avg_query_time > this.config.thresholds.query_time_warning) {
            score -= 15;
        }
        
        // Connection usage penalty
        const connection_usage = (db_metrics.active_connections / 20) * 100;
        if (connection_usage > this.config.thresholds.connection_critical) {
            score -= 25;
        } else if (connection_usage > this.config.thresholds.connection_warning) {
            score -= 10;
        }
        
        // Slow queries penalty
        if (db_metrics.slow_queries > 10) {
            score -= 20;
        } else if (db_metrics.slow_queries > 5) {
            score -= 10;
        }
        
        return Math.max(score, 0);
    }
    
    /**
     * Analyze performance and generate alerts
     */
    async analyzePerformance(metrics) {
        if (!metrics) return;
        
        const alerts = [];
        
        // Query time analysis
        if (metrics.database.avg_query_time > this.config.thresholds.query_time_critical) {
            alerts.push({
                type: 'CRITICAL',
                category: 'QUERY_PERFORMANCE',
                message: `Average query time ${metrics.database.avg_query_time}ms exceeds critical threshold`,
                suggestions: [
                    'Check for missing indexes',
                    'Analyze slow query log',
                    'Consider query optimization',
                    'Review table structure'
                ]
            });
        }
        
        // Connection usage analysis
        if (metrics.database.connection_usage > this.config.thresholds.connection_critical) {
            alerts.push({
                type: 'CRITICAL',
                category: 'CONNECTION_POOL',
                message: `Connection usage ${metrics.database.connection_usage.toFixed(1)}% exceeds critical threshold`,
                suggestions: [
                    'Increase connection pool size',
                    'Check for connection leaks',
                    'Optimize connection usage',
                    'Implement connection queuing'
                ]
            });
        }
        
        // Process alerts
        for (const alert of alerts) {
            await this.processAlert(alert);
        }
        
        // Store performance trend
        this.metrics.query_times.push({
            timestamp: metrics.timestamp,
            avg_time: metrics.database.avg_query_time,
            max_time: metrics.database.max_query_time
        });
        
        // Keep only last hour of data
        const one_hour_ago = new Date(Date.now() - 3600000);
        this.metrics.query_times = this.metrics.query_times.filter(
            m => m.timestamp > one_hour_ago
        );
    }
    
    /**
     * Process and emit alerts
     */
    async processAlert(alert) {
        const alert_key = `${alert.category}_${alert.type}`;
        const now = Date.now();
        
        // Check alert cooldown
        if (this.alerts.has(alert_key)) {
            const last_alert = this.alerts.get(alert_key);
            if (now - last_alert < this.config.monitoring.alert_cooldown) {
                return; // Still in cooldown
            }
        }
        
        // Update alert timestamp
        this.alerts.set(alert_key, now);
        
        // Log alert
        console.log(`🚨 ${alert.type} ALERT [${alert.category}]: ${alert.message}`);
        alert.suggestions.forEach(suggestion => {
            console.log(`   💡 ${suggestion}`);
        });
        
        // Emit alert for Cursor team integration
        this.emit('performance_alert', {
            ...alert,
            timestamp: new Date()
        });
        
        // Cache alert in Redis
        await this.redis_client.lpush('performance_alerts', JSON.stringify({
            ...alert,
            timestamp: new Date()
        }));
        
        // Keep only last 100 alerts
        await this.redis_client.ltrim('performance_alerts', 0, 99);
    }
    
    /**
     * Cache metrics in Redis for Cursor team access
     */
    async cacheMetrics(metrics) {
        try {
            // Cache latest metrics
            await this.redis_client.setex(
                'latest_performance_metrics',
                60, // 1 minute expiry
                JSON.stringify(metrics)
            );
            
            // Cache metrics history
            await this.redis_client.lpush(
                'performance_metrics_history',
                JSON.stringify(metrics)
            );
            
            // Keep only last 3600 entries (1 hour at 1-second intervals)
            await this.redis_client.ltrim('performance_metrics_history', 0, 3599);
            
        } catch (error) {
            console.error('🚨 Redis cache error:', error);
        }
    }
    
    /**
     * Start query analysis for optimization suggestions
     */
    startQueryAnalysis() {
        this.query_analysis_interval = setInterval(async () => {
            try {
                await this.analyzeSlowQueries();
                await this.generateOptimizationSuggestions();
                
            } catch (error) {
                console.error('🚨 Query analysis error:', error);
            }
        }, 30000); // Every 30 seconds
        
        console.log('🔍 Query analysis monitoring started');
    }
    
    /**
     * Analyze slow queries and generate suggestions
     */
    async analyzeSlowQueries() {
        try {
            // Get slow queries from performance schema
            const [slow_queries] = await this.db_pool.execute(`
                SELECT 
                    sql_text,
                    avg_timer_wait / 1000000 as avg_time_ms,
                    count_star as execution_count,
                    sum_timer_wait / 1000000 as total_time_ms
                FROM performance_schema.events_statements_summary_by_digest
                WHERE avg_timer_wait / 1000000 > ?
                ORDER BY avg_timer_wait DESC
                LIMIT 10
            `, [this.config.thresholds.query_time_warning]);
            
            if (slow_queries.length > 0) {
                this.emit('slow_queries_detected', {
                    count: slow_queries.length,
                    queries: slow_queries,
                    timestamp: new Date()
                });
                
                console.log(`🐌 Detected ${slow_queries.length} slow queries`);
            }
            
        } catch (error) {
            console.error('🚨 Slow query analysis error:', error);
        }
    }
    
    /**
     * Generate optimization suggestions
     */
    async generateOptimizationSuggestions() {
        const suggestions = [];
        
        // Analyze table statistics
        try {
            const [table_stats] = await this.db_pool.execute(`
                SELECT 
                    table_name,
                    table_rows,
                    avg_row_length,
                    data_length,
                    index_length,
                    (data_length + index_length) as total_size
                FROM information_schema.tables
                WHERE table_schema = DATABASE()
                ORDER BY total_size DESC
                LIMIT 10
            `);
            
            table_stats.forEach(table => {
                const index_ratio = table.index_length / (table.data_length + table.index_length);
                
                if (index_ratio < 0.1 && table.table_rows > 1000) {
                    suggestions.push({
                        type: 'INDEX_OPTIMIZATION',
                        table: table.table_name,
                        message: `Table ${table.table_name} may need additional indexes`,
                        priority: 'HIGH',
                        details: {
                            rows: table.table_rows,
                            index_ratio: (index_ratio * 100).toFixed(2) + '%'
                        }
                    });
                }
            });
            
        } catch (error) {
            console.error('🚨 Table analysis error:', error);
        }
        
        if (suggestions.length > 0) {
            this.emit('optimization_suggestions', {
                suggestions,
                timestamp: new Date()
            });
        }
    }
    
    /**
     * Start connection monitoring
     */
    startConnectionMonitoring() {
        this.connection_interval = setInterval(async () => {
            try {
                const pool_status = {
                    active_connections: this.db_pool._allConnections.length,
                    free_connections: this.db_pool._freeConnections.length,
                    queue_length: this.db_pool._connectionQueue.length
                };
                
                this.emit('connection_status', {
                    ...pool_status,
                    timestamp: new Date()
                });
                
            } catch (error) {
                console.error('🚨 Connection monitoring error:', error);
            }
        }, 5000); // Every 5 seconds
        
        console.log('🔗 Connection monitoring started');
    }
    
    /**
     * Get current performance summary for Cursor team
     */
    async getPerformanceSummary() {
        try {
            const latest_metrics = await this.redis_client.get('latest_performance_metrics');
            const recent_alerts = await this.redis_client.lrange('performance_alerts', 0, 9);
            
            return {
                current_metrics: latest_metrics ? JSON.parse(latest_metrics) : null,
                recent_alerts: recent_alerts.map(alert => JSON.parse(alert)),
                monitoring_status: {
                    active: this.monitoring_active,
                    uptime: process.uptime(),
                    memory_usage: process.memoryUsage()
                },
                performance_trends: {
                    avg_query_time_1h: this.calculateAverageQueryTime(),
                    performance_score: this.metrics.performance_score
                }
            };
            
        } catch (error) {
            console.error('🚨 Performance summary error:', error);
            return null;
        }
    }
    
    /**
     * Calculate average query time over last hour
     */
    calculateAverageQueryTime() {
        if (this.metrics.query_times.length === 0) return 0;
        
        const total_time = this.metrics.query_times.reduce((sum, metric) => sum + metric.avg_time, 0);
        return (total_time / this.metrics.query_times.length).toFixed(2);
    }
    
    /**
     * Stop all monitoring
     */
    stopMonitoring() {
        if (this.performance_interval) clearInterval(this.performance_interval);
        if (this.query_analysis_interval) clearInterval(this.query_analysis_interval);
        if (this.connection_interval) clearInterval(this.connection_interval);
        
        this.monitoring_active = false;
        this.emit('monitor_stopped', { timestamp: new Date() });
        
        console.log('🛑 Real-time database monitoring stopped');
    }
    
    /**
     * Cleanup resources
     */
    async cleanup() {
        this.stopMonitoring();
        
        if (this.db_pool) {
            await this.db_pool.end();
        }
        
        if (this.redis_client) {
            await this.redis_client.quit();
        }
        
        console.log('🧹 Database monitor cleanup completed');
    }
}

module.exports = RealtimeDatabaseMonitor;

/**
 * 🚀 CURSOR TEAM INTEGRATION READY
 * 
 * Usage Example:
 * 
 * const monitor = new RealtimeDatabaseMonitor({
 *     mysql: {
 *         host: 'localhost',
 *         user: 'your_user',
 *         password: 'your_password',
 *         database: 'your_database'
 *     },
 *     redis: {
 *         host: 'localhost',
 *         port: 6379
 *     }
 * });
 * 
 * // Listen for real-time events
 * monitor.on('performance_metrics', (metrics) => {
 *     console.log('Performance Update:', metrics);
 * });
 * 
 * monitor.on('performance_alert', (alert) => {
 *     console.log('Performance Alert:', alert);
 * });
 * 
 * // Get performance summary for dashboard
 * const summary = await monitor.getPerformanceSummary();
 */
