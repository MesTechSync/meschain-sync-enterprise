/**
 * MesChain-Sync Prometheus & Grafana Monitoring Integration
 * v1.0 - Advanced Performance Monitoring and Analytics
 * 
 * Features:
 * - Real-time metrics collection
 * - Prometheus metrics export
 * - Grafana dashboard integration
 * - Custom alerting rules
 * - Performance analytics
 * 
 * @author Gemini Innovation Team
 * @version 1.0.0
 * @date June 9, 2025
 */

class MesChainPrometheusMonitor {
    constructor(config = {}) {
        this.config = {
            metricsEndpoint: config.metricsEndpoint || '/metrics',
            pushGatewayUrl: config.pushGatewayUrl || 'http://localhost:9091',
            jobName: config.jobName || 'meschain-sync',
            instance: config.instance || 'web-client',
            collectInterval: config.collectInterval || 15000, // 15 seconds
            enablePush: config.enablePush || false,
            ...config
        };

        // Metrics storage
        this.metrics = new Map();
        this.counters = new Map();
        this.gauges = new Map();
        this.histograms = new Map();
        this.summaries = new Map();

        // Collection state
        this.isCollecting = false;
        this.collectionInterval = null;

        // Initialize default metrics
        this.initializeDefaultMetrics();
        
        console.log('📊 MesChain Prometheus Monitor initialized');
        this.startCollection();
    }

    /**
     * Initialize default metrics
     */
    initializeDefaultMetrics() {
        // System metrics
        this.createCounter('meschain_requests_total', 'Total number of requests', ['method', 'endpoint', 'status']);
        this.createGauge('meschain_active_users', 'Number of active users', ['role']);
        this.createGauge('meschain_websocket_connections', 'Active WebSocket connections');
        this.createHistogram('meschain_request_duration_seconds', 'Request duration in seconds', ['method', 'endpoint']);
        
        // Business metrics
        this.createCounter('meschain_orders_total', 'Total orders processed', ['marketplace', 'status']);
        this.createGauge('meschain_inventory_items', 'Current inventory count', ['marketplace']);
        this.createCounter('meschain_sync_operations_total', 'Total sync operations', ['marketplace', 'operation', 'status']);
        this.createGauge('meschain_sync_lag_seconds', 'Sync lag in seconds', ['marketplace']);
        
        // Performance metrics
        this.createHistogram('meschain_api_response_time', 'API response time', ['api', 'method']);
        this.createGauge('meschain_memory_usage_bytes', 'Memory usage in bytes');
        this.createGauge('meschain_cpu_usage_percent', 'CPU usage percentage');
        this.createCounter('meschain_errors_total', 'Total errors', ['type', 'component']);
        
        // GraphQL metrics
        this.createCounter('meschain_graphql_operations_total', 'GraphQL operations', ['operation_type', 'operation_name']);
        this.createHistogram('meschain_graphql_execution_time', 'GraphQL execution time', ['operation_type']);
        this.createGauge('meschain_graphql_subscriptions_active', 'Active GraphQL subscriptions');
        
        console.log('✅ Default metrics initialized');
    }

    /**
     * Create a counter metric
     */
    createCounter(name, help, labels = []) {
        this.counters.set(name, {
            type: 'counter',
            help,
            labels,
            values: new Map()
        });
    }

    /**
     * Create a gauge metric
     */
    createGauge(name, help, labels = []) {
        this.gauges.set(name, {
            type: 'gauge',
            help,
            labels,
            values: new Map()
        });
    }

    /**
     * Create a histogram metric
     */
    createHistogram(name, help, labels = [], buckets = [0.1, 0.5, 1, 2.5, 5, 10]) {
        this.histograms.set(name, {
            type: 'histogram',
            help,
            labels,
            buckets,
            values: new Map()
        });
    }

    /**
     * Create a summary metric
     */
    createSummary(name, help, labels = [], quantiles = [0.5, 0.9, 0.95, 0.99]) {
        this.summaries.set(name, {
            type: 'summary',
            help,
            labels,
            quantiles,
            values: new Map()
        });
    }

    /**
     * Increment a counter
     */
    incrementCounter(name, labels = {}, value = 1) {
        const counter = this.counters.get(name);
        if (!counter) {
            console.warn(`Counter ${name} not found`);
            return;
        }

        const labelKey = this.getLabelKey(labels);
        const currentValue = counter.values.get(labelKey) || 0;
        counter.values.set(labelKey, currentValue + value);

        this.emit('metric_updated', { name, type: 'counter', labels, value: currentValue + value });
    }

    /**
     * Set a gauge value
     */
    setGauge(name, value, labels = {}) {
        const gauge = this.gauges.get(name);
        if (!gauge) {
            console.warn(`Gauge ${name} not found`);
            return;
        }

        const labelKey = this.getLabelKey(labels);
        gauge.values.set(labelKey, value);

        this.emit('metric_updated', { name, type: 'gauge', labels, value });
    }

    /**
     * Observe a histogram value
     */
    observeHistogram(name, value, labels = {}) {
        const histogram = this.histograms.get(name);
        if (!histogram) {
            console.warn(`Histogram ${name} not found`);
            return;
        }

        const labelKey = this.getLabelKey(labels);
        if (!histogram.values.has(labelKey)) {
            histogram.values.set(labelKey, {
                count: 0,
                sum: 0,
                buckets: new Map()
            });
        }

        const histogramData = histogram.values.get(labelKey);
        histogramData.count++;
        histogramData.sum += value;

        // Update buckets
        histogram.buckets.forEach(bucket => {
            if (value <= bucket) {
                const currentCount = histogramData.buckets.get(bucket) || 0;
                histogramData.buckets.set(bucket, currentCount + 1);
            }
        });

        this.emit('metric_updated', { name, type: 'histogram', labels, value });
    }

    /**
     * Observe a summary value
     */
    observeSummary(name, value, labels = {}) {
        const summary = this.summaries.get(name);
        if (!summary) {
            console.warn(`Summary ${name} not found`);
            return;
        }

        const labelKey = this.getLabelKey(labels);
        if (!summary.values.has(labelKey)) {
            summary.values.set(labelKey, {
                count: 0,
                sum: 0,
                values: []
            });
        }

        const summaryData = summary.values.get(labelKey);
        summaryData.count++;
        summaryData.sum += value;
        summaryData.values.push(value);

        // Keep only last 1000 values for quantile calculation
        if (summaryData.values.length > 1000) {
            summaryData.values = summaryData.values.slice(-1000);
        }

        this.emit('metric_updated', { name, type: 'summary', labels, value });
    }

    /**
     * Start automatic metrics collection
     */
    startCollection() {
        if (this.isCollecting) return;

        this.isCollecting = true;
        this.collectionInterval = setInterval(() => {
            this.collectSystemMetrics();
            this.collectBusinessMetrics();
            
            if (this.config.enablePush) {
                this.pushMetrics();
            }
        }, this.config.collectInterval);

        console.log('📊 Metrics collection started');
    }

    /**
     * Stop metrics collection
     */
    stopCollection() {
        if (this.collectionInterval) {
            clearInterval(this.collectionInterval);
            this.collectionInterval = null;
        }
        this.isCollecting = false;

        console.log('📊 Metrics collection stopped');
    }

    /**
     * Collect system metrics
     */
    collectSystemMetrics() {
        try {
            // Memory usage
            if (performance.memory) {
                this.setGauge('meschain_memory_usage_bytes', performance.memory.usedJSHeapSize);
            }

            // Active WebSocket connections
            if (window.mesChainWS) {
                const status = window.mesChainWS.getStatus();
                this.setGauge('meschain_websocket_connections', status.connected ? 1 : 0);
            }

            // Active users by role
            const activeUsers = this.getActiveUsersByRole();
            Object.entries(activeUsers).forEach(([role, count]) => {
                this.setGauge('meschain_active_users', count, { role });
            });

            // GraphQL subscriptions
            if (window.mesChainGraphQL) {
                const status = window.mesChainGraphQL.getStatus();
                this.setGauge('meschain_graphql_subscriptions_active', status.subscriptions);
            }

        } catch (error) {
            console.error('❌ Error collecting system metrics:', error);
            this.incrementCounter('meschain_errors_total', { type: 'metric_collection', component: 'system' });
        }
    }

    /**
     * Collect business metrics
     */
    collectBusinessMetrics() {
        try {
            // Simulate business metrics collection
            // In real implementation, these would come from actual data sources

            // Inventory metrics
            const marketplaces = ['trendyol', 'amazon', 'n11', 'hepsiburada'];
            marketplaces.forEach(marketplace => {
                const inventoryCount = Math.floor(Math.random() * 1000) + 500;
                this.setGauge('meschain_inventory_items', inventoryCount, { marketplace });
                
                const syncLag = Math.random() * 30; // 0-30 seconds
                this.setGauge('meschain_sync_lag_seconds', syncLag, { marketplace });
            });

        } catch (error) {
            console.error('❌ Error collecting business metrics:', error);
            this.incrementCounter('meschain_errors_total', { type: 'metric_collection', component: 'business' });
        }
    }

    /**
     * Get active users by role
     */
    getActiveUsersByRole() {
        // In real implementation, this would query actual user data
        return {
            'super_admin': 1,
            'admin': Math.floor(Math.random() * 5) + 1,
            'dropshipper': Math.floor(Math.random() * 20) + 5
        };
    }

    /**
     * Generate label key for metric storage
     */
    getLabelKey(labels) {
        return Object.entries(labels)
            .sort(([a], [b]) => a.localeCompare(b))
            .map(([key, value]) => `${key}="${value}"`)
            .join(',');
    }

    /**
     * Export metrics in Prometheus format
     */
    exportPrometheusMetrics() {
        let output = '';

        // Export counters
        this.counters.forEach((metric, name) => {
            output += `# HELP ${name} ${metric.help}\n`;
            output += `# TYPE ${name} counter\n`;
            
            metric.values.forEach((value, labelKey) => {
                const labels = labelKey ? `{${labelKey}}` : '';
                output += `${name}${labels} ${value}\n`;
            });
            output += '\n';
        });

        // Export gauges
        this.gauges.forEach((metric, name) => {
            output += `# HELP ${name} ${metric.help}\n`;
            output += `# TYPE ${name} gauge\n`;
            
            metric.values.forEach((value, labelKey) => {
                const labels = labelKey ? `{${labelKey}}` : '';
                output += `${name}${labels} ${value}\n`;
            });
            output += '\n';
        });

        // Export histograms
        this.histograms.forEach((metric, name) => {
            output += `# HELP ${name} ${metric.help}\n`;
            output += `# TYPE ${name} histogram\n`;
            
            metric.values.forEach((histogramData, labelKey) => {
                const baseLabels = labelKey ? `{${labelKey}}` : '';
                
                // Bucket counts
                metric.buckets.forEach(bucket => {
                    const bucketLabels = labelKey ? 
                        `{${labelKey},le="${bucket}"}` : 
                        `{le="${bucket}"}`;
                    const count = histogramData.buckets.get(bucket) || 0;
                    output += `${name}_bucket${bucketLabels} ${count}\n`;
                });
                
                // +Inf bucket
                const infLabels = labelKey ? 
                    `{${labelKey},le="+Inf"}` : 
                    `{le="+Inf"}`;
                output += `${name}_bucket${infLabels} ${histogramData.count}\n`;
                
                // Count and sum
                output += `${name}_count${baseLabels} ${histogramData.count}\n`;
                output += `${name}_sum${baseLabels} ${histogramData.sum}\n`;
            });
            output += '\n';
        });

        return output;
    }

    /**
     * Push metrics to Prometheus Push Gateway
     */
    async pushMetrics() {
        try {
            const metrics = this.exportPrometheusMetrics();
            const url = `${this.config.pushGatewayUrl}/metrics/job/${this.config.jobName}/instance/${this.config.instance}`;
            
            await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'text/plain'
                },
                body: metrics
            });

            console.log('📊 Metrics pushed to Prometheus Push Gateway');

        } catch (error) {
            console.error('❌ Error pushing metrics:', error);
            this.incrementCounter('meschain_errors_total', { type: 'push_gateway', component: 'prometheus' });
        }
    }

    /**
     * Create Grafana dashboard configuration
     */
    generateGrafanaDashboard() {
        return {
            dashboard: {
                id: null,
                title: "MesChain-Sync Monitoring Dashboard",
                tags: ["meschain", "ecommerce", "monitoring"],
                timezone: "browser",
                panels: [
                    {
                        id: 1,
                        title: "Request Rate",
                        type: "graph",
                        targets: [
                            {
                                expr: "rate(meschain_requests_total[5m])",
                                legendFormat: "{{method}} {{endpoint}}"
                            }
                        ],
                        yAxes: [
                            {
                                label: "Requests/sec"
                            }
                        ]
                    },
                    {
                        id: 2,
                        title: "Response Time",
                        type: "graph",
                        targets: [
                            {
                                expr: "histogram_quantile(0.95, rate(meschain_request_duration_seconds_bucket[5m]))",
                                legendFormat: "95th percentile"
                            },
                            {
                                expr: "histogram_quantile(0.50, rate(meschain_request_duration_seconds_bucket[5m]))",
                                legendFormat: "50th percentile"
                            }
                        ],
                        yAxes: [
                            {
                                label: "Seconds"
                            }
                        ]
                    },
                    {
                        id: 3,
                        title: "Active Users",
                        type: "singlestat",
                        targets: [
                            {
                                expr: "sum(meschain_active_users)",
                                legendFormat: "Total Active Users"
                            }
                        ]
                    },
                    {
                        id: 4,
                        title: "Orders by Marketplace",
                        type: "graph",
                        targets: [
                            {
                                expr: "rate(meschain_orders_total[5m])",
                                legendFormat: "{{marketplace}}"
                            }
                        ]
                    },
                    {
                        id: 5,
                        title: "Sync Lag",
                        type: "graph",
                        targets: [
                            {
                                expr: "meschain_sync_lag_seconds",
                                legendFormat: "{{marketplace}}"
                            }
                        ],
                        yAxes: [
                            {
                                label: "Seconds"
                            }
                        ]
                    },
                    {
                        id: 6,
                        title: "Error Rate",
                        type: "graph",
                        targets: [
                            {
                                expr: "rate(meschain_errors_total[5m])",
                                legendFormat: "{{type}} - {{component}}"
                            }
                        ]
                    }
                ],
                time: {
                    from: "now-1h",
                    to: "now"
                },
                refresh: "30s"
            }
        };
    }

    /**
     * Setup alerting rules
     */
    generateAlertingRules() {
        return {
            groups: [
                {
                    name: "meschain.rules",
                    rules: [
                        {
                            alert: "HighErrorRate",
                            expr: "rate(meschain_errors_total[5m]) > 0.1",
                            for: "2m",
                            labels: {
                                severity: "warning"
                            },
                            annotations: {
                                summary: "High error rate detected",
                                description: "Error rate is {{ $value }} errors per second"
                            }
                        },
                        {
                            alert: "HighResponseTime",
                            expr: "histogram_quantile(0.95, rate(meschain_request_duration_seconds_bucket[5m])) > 2",
                            for: "5m",
                            labels: {
                                severity: "warning"
                            },
                            annotations: {
                                summary: "High response time detected",
                                description: "95th percentile response time is {{ $value }} seconds"
                            }
                        },
                        {
                            alert: "SyncLagHigh",
                            expr: "meschain_sync_lag_seconds > 60",
                            for: "5m",
                            labels: {
                                severity: "critical"
                            },
                            annotations: {
                                summary: "Sync lag is too high",
                                description: "Sync lag for {{ $labels.marketplace }} is {{ $value }} seconds"
                            }
                        },
                        {
                            alert: "WebSocketDisconnected",
                            expr: "meschain_websocket_connections == 0",
                            for: "1m",
                            labels: {
                                severity: "critical"
                            },
                            annotations: {
                                summary: "WebSocket connection lost",
                                description: "Real-time communication is down"
                            }
                        }
                    ]
                }
            ]
        };
    }

    /**
     * Event system
     */
    emit(event, data) {
        // Simple event emission for demo
        if (window.mesChainEventBus) {
            window.mesChainEventBus.emit(event, data);
        }
    }

    /**
     * Get current metrics summary
     */
    getMetricsSummary() {
        const summary = {
            counters: {},
            gauges: {},
            histograms: {},
            summaries: {},
            timestamp: new Date().toISOString()
        };

        // Summarize counters
        this.counters.forEach((metric, name) => {
            summary.counters[name] = {
                total: Array.from(metric.values.values()).reduce((sum, val) => sum + val, 0),
                count: metric.values.size
            };
        });

        // Summarize gauges
        this.gauges.forEach((metric, name) => {
            const values = Array.from(metric.values.values());
            summary.gauges[name] = {
                current: values[values.length - 1] || 0,
                count: values.length
            };
        });

        return summary;
    }

    /**
     * Reset all metrics
     */
    resetMetrics() {
        this.counters.forEach(metric => metric.values.clear());
        this.gauges.forEach(metric => metric.values.clear());
        this.histograms.forEach(metric => metric.values.clear());
        this.summaries.forEach(metric => metric.values.clear());

        console.log('📊 All metrics reset');
    }

    /**
     * Cleanup and stop monitoring
     */
    destroy() {
        this.stopCollection();
        this.resetMetrics();
        
        console.log('📊 Prometheus monitor destroyed');
    }
}

// Export class
window.MesChainPrometheusMonitor = MesChainPrometheusMonitor;

// Auto-initialization helper
window.initMesChainMonitoring = function(config = {}) {
    if (window.mesChainMonitor) {
        window.mesChainMonitor.destroy();
    }
    
    window.mesChainMonitor = new MesChainPrometheusMonitor(config);
    
    // Integrate with existing systems
    if (window.mesChainGraphQL) {
        // Monitor GraphQL operations
        window.mesChainGraphQL.on('query', (data) => {
            window.mesChainMonitor.incrementCounter('meschain_graphql_operations_total', {
                operation_type: 'query',
                operation_name: data.operationName || 'unknown'
            });
        });
        
        window.mesChainGraphQL.on('mutation', (data) => {
            window.mesChainMonitor.incrementCounter('meschain_graphql_operations_total', {
                operation_type: 'mutation',
                operation_name: data.operationName || 'unknown'
            });
        });
    }
    
    // Monitor WebSocket events
    if (window.mesChainWS) {
        window.mesChainWS.on('connected', () => {
            window.mesChainMonitor.setGauge('meschain_websocket_connections', 1);
        });
        
        window.mesChainWS.on('disconnected', () => {
            window.mesChainMonitor.setGauge('meschain_websocket_connections', 0);
        });
    }
    
    return window.mesChainMonitor;
};

console.log('📊 MesChain Prometheus & Grafana Monitoring loaded'); 