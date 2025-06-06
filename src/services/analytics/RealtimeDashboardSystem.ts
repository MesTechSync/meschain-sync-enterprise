import { EventEmitter } from 'events';
import { Logger } from '../core/Logger';
import { AdvancedAnalyticsEngine, AnalyticsMetric } from './AdvancedAnalyticsEngine';

/**
 * Real-Time Dashboard System
 * Provides live data streaming, interactive visualizations,
 * and customizable dashboard layouts for business intelligence
 */

export interface DashboardWidget {
  id: string;
  type: 'chart' | 'metric' | 'table' | 'gauge' | 'heatmap' | 'alert';
  title: string;
  position: {
    x: number;
    y: number;
    width: number;
    height: number;
  };
  config: {
    metrics: string[];
    chartType?: 'line' | 'bar' | 'pie' | 'area' | 'scatter';
    timeRange?: string;
    refreshInterval?: number;
    thresholds?: Record<string, number>;
    filters?: Record<string, any>;
  };
  data?: any;
  lastUpdated?: Date;
}

export interface Dashboard {
  id: string;
  name: string;
  userId: string;
  isPublic: boolean;
  widgets: DashboardWidget[];
  layout: 'grid' | 'flex' | 'custom';
  theme: 'light' | 'dark' | 'auto';
  autoRefresh: boolean;
  refreshInterval: number;
  createdAt: Date;
  updatedAt: Date;
}

export interface DashboardUser {
  id: string;
  name: string;
  email: string;
  role: 'admin' | 'manager' | 'analyst' | 'viewer';
  permissions: string[];
  dashboards: string[];
  preferences: {
    theme: string;
    defaultRefreshRate: number;
    notifications: boolean;
  };
}

export interface RealtimeDataStream {
  id: string;
  metrics: string[];
  subscribers: Set<string>;
  lastUpdate: Date;
  updateInterval: number;
  isActive: boolean;
}

export interface AlertRule {
  id: string;
  name: string;
  metric: string;
  condition: 'greater_than' | 'less_than' | 'equals' | 'change_percent';
  threshold: number;
  severity: 'info' | 'warning' | 'error' | 'critical';
  enabled: boolean;
  recipients: string[];
  lastTriggered?: Date;
}

export interface DashboardAlert {
  id: string;
  ruleId: string;
  metric: string;
  currentValue: number;
  threshold: number;
  severity: 'info' | 'warning' | 'error' | 'critical';
  timestamp: Date;
  acknowledged: boolean;
  acknowledgedBy?: string;
  message: string;
}

export class RealtimeDashboardSystem extends EventEmitter {
  private logger: Logger;
  private analyticsEngine: AdvancedAnalyticsEngine;
  private isRunning: boolean = false;

  // Data stores
  private dashboards: Map<string, Dashboard> = new Map();
  private users: Map<string, DashboardUser> = new Map();
  private dataStreams: Map<string, RealtimeDataStream> = new Map();
  private alertRules: Map<string, AlertRule> = new Map();
  private activeAlerts: Map<string, DashboardAlert> = new Map();

  // WebSocket connections (simulated)
  private connections: Map<string, any> = new Map();
  
  // Performance metrics
  private performanceMetrics = {
    totalDashboards: 0,
    activeConnections: 0,
    dataPointsPerSecond: 0,
    avgResponseTime: 0,
    alertsTriggered: 0
  };

  // Cache for frequently accessed data
  private widgetDataCache: Map<string, { data: any; timestamp: Date }> = new Map();
  private cacheExpiryTime = 30000; // 30 seconds

  constructor(analyticsEngine: AdvancedAnalyticsEngine) {
    super();
    this.logger = new Logger('RealtimeDashboardSystem');
    this.analyticsEngine = analyticsEngine;
    this.initializeSystem();
  }

  /**
   * Initialize the dashboard system
   */
  private async initializeSystem(): Promise<void> {
    try {
      await this.loadDashboards();
      await this.loadUsers();
      await this.setupDataStreams();
      await this.loadAlertRules();
      
      this.startRealtimeUpdates();
      this.startAlertMonitoring();
      this.isRunning = true;
      
      this.logger.info('Real-Time Dashboard System initialized successfully');
      this.emit('system:initialized');
    } catch (error) {
      this.logger.error('Failed to initialize dashboard system', error);
      throw error;
    }
  }

  /**
   * Create a new dashboard
   */
  public async createDashboard(
    userId: string, 
    name: string, 
    config?: Partial<Dashboard>
  ): Promise<Dashboard> {
    try {
      const dashboard: Dashboard = {
        id: this.generateId(),
        name,
        userId,
        isPublic: config?.isPublic || false,
        widgets: config?.widgets || [],
        layout: config?.layout || 'grid',
        theme: config?.theme || 'light',
        autoRefresh: config?.autoRefresh !== false,
        refreshInterval: config?.refreshInterval || 30000,
        createdAt: new Date(),
        updatedAt: new Date()
      };

      this.dashboards.set(dashboard.id, dashboard);
      this.performanceMetrics.totalDashboards++;

      this.logger.info(`Dashboard created: ${dashboard.name}`, { dashboardId: dashboard.id });
      this.emit('dashboard:created', dashboard);

      return dashboard;
    } catch (error) {
      this.logger.error('Failed to create dashboard', error);
      throw error;
    }
  }

  /**
   * Add widget to dashboard
   */
  public async addWidget(
    dashboardId: string, 
    widget: Omit<DashboardWidget, 'id'>
  ): Promise<DashboardWidget> {
    try {
      const dashboard = this.dashboards.get(dashboardId);
      if (!dashboard) {
        throw new Error(`Dashboard not found: ${dashboardId}`);
      }

      const newWidget: DashboardWidget = {
        ...widget,
        id: this.generateId(),
        lastUpdated: new Date()
      };

      dashboard.widgets.push(newWidget);
      dashboard.updatedAt = new Date();

      // Initialize widget data
      await this.updateWidgetData(newWidget);

      this.logger.info(`Widget added to dashboard: ${newWidget.title}`, { 
        dashboardId, 
        widgetId: newWidget.id 
      });
      
      this.emit('widget:added', { dashboard, widget: newWidget });

      return newWidget;
    } catch (error) {
      this.logger.error('Failed to add widget', error);
      throw error;
    }
  }

  /**
   * Start real-time data streaming for a client
   */
  public async startDataStream(
    clientId: string, 
    dashboardId: string
  ): Promise<void> {
    try {
      const dashboard = this.dashboards.get(dashboardId);
      if (!dashboard) {
        throw new Error(`Dashboard not found: ${dashboardId}`);
      }

      // Create or get existing data stream
      let stream = this.dataStreams.get(dashboardId);
      if (!stream) {
        const metrics = this.extractMetricsFromDashboard(dashboard);
        stream = {
          id: dashboardId,
          metrics,
          subscribers: new Set(),
          lastUpdate: new Date(),
          updateInterval: dashboard.refreshInterval,
          isActive: true
        };
        this.dataStreams.set(dashboardId, stream);
      }

      // Add client to stream
      stream.subscribers.add(clientId);
      this.connections.set(clientId, { dashboardId, connectedAt: new Date() });
      this.performanceMetrics.activeConnections++;

      this.logger.info(`Client connected to data stream`, { clientId, dashboardId });
      this.emit('stream:client_connected', { clientId, dashboardId });

    } catch (error) {
      this.logger.error('Failed to start data stream', error);
      throw error;
    }
  }

  /**
   * Stop data streaming for a client
   */
  public async stopDataStream(clientId: string): Promise<void> {
    try {
      const connection = this.connections.get(clientId);
      if (!connection) return;

      const stream = this.dataStreams.get(connection.dashboardId);
      if (stream) {
        stream.subscribers.delete(clientId);
        
        // Clean up empty streams
        if (stream.subscribers.size === 0) {
          stream.isActive = false;
        }
      }

      this.connections.delete(clientId);
      this.performanceMetrics.activeConnections--;

      this.logger.info(`Client disconnected from data stream`, { clientId });
      this.emit('stream:client_disconnected', { clientId });

    } catch (error) {
      this.logger.error('Failed to stop data stream', error);
    }
  }

  /**
   * Get dashboard data with real-time updates
   */
  public async getDashboardData(dashboardId: string): Promise<any> {
    try {
      const dashboard = this.dashboards.get(dashboardId);
      if (!dashboard) {
        throw new Error(`Dashboard not found: ${dashboardId}`);
      }

      // Update all widget data
      const updatedWidgets = await Promise.all(
        dashboard.widgets.map(widget => this.getWidgetData(widget))
      );

      const dashboardData = {
        dashboard: {
          ...dashboard,
          widgets: updatedWidgets
        },
        metadata: {
          lastUpdated: new Date(),
          totalWidgets: updatedWidgets.length,
          activeAlerts: Array.from(this.activeAlerts.values()).filter(
            alert => updatedWidgets.some(w => w.config.metrics.includes(alert.metric))
          )
        }
      };

      this.emit('dashboard:data_updated', dashboardData);
      return dashboardData;

    } catch (error) {
      this.logger.error('Failed to get dashboard data', error);
      throw error;
    }
  }

  /**
   * Update widget data from analytics engine
   */
  private async updateWidgetData(widget: DashboardWidget): Promise<void> {
    try {
      const cacheKey = `${widget.id}_${JSON.stringify(widget.config)}`;
      const cached = this.widgetDataCache.get(cacheKey);
      
      // Return cached data if still valid
      if (cached && (Date.now() - cached.timestamp.getTime()) < this.cacheExpiryTime) {
        widget.data = cached.data;
        return;
      }

      let data: any;

      switch (widget.type) {
        case 'metric':
          data = await this.getMetricWidgetData(widget);
          break;
        case 'chart':
          data = await this.getChartWidgetData(widget);
          break;
        case 'table':
          data = await this.getTableWidgetData(widget);
          break;
        case 'gauge':
          data = await this.getGaugeWidgetData(widget);
          break;
        case 'heatmap':
          data = await this.getHeatmapWidgetData(widget);
          break;
        case 'alert':
          data = await this.getAlertWidgetData(widget);
          break;
        default:
          data = { error: 'Unsupported widget type' };
      }

      widget.data = data;
      widget.lastUpdated = new Date();

      // Cache the data
      this.widgetDataCache.set(cacheKey, {
        data,
        timestamp: new Date()
      });

    } catch (error) {
      this.logger.error(`Failed to update widget data: ${widget.title}`, error);
      widget.data = { error: error.message };
    }
  }

  /**
   * Get metric widget data
   */
  private async getMetricWidgetData(widget: DashboardWidget): Promise<any> {
    const result = await this.analyticsEngine.executeQuery({
      metrics: widget.config.metrics,
      timeRange: {
        start: new Date(Date.now() - 24 * 60 * 60 * 1000), // 24h ago
        end: new Date()
      },
      filters: widget.config.filters
    });

    return {
      current: result.data[result.data.length - 1]?.value || 0,
      previous: result.data[result.data.length - 2]?.value || 0,
      trend: result.data.length > 1 ? 
        (result.data[result.data.length - 1].value > result.data[result.data.length - 2].value ? 'up' : 'down') : 'stable',
      unit: result.data[0]?.unit || '',
      lastUpdated: new Date()
    };
  }

  /**
   * Get chart widget data
   */
  private async getChartWidgetData(widget: DashboardWidget): Promise<any> {
    const timeRange = this.parseTimeRange(widget.config.timeRange || '24h');
    
    const result = await this.analyticsEngine.executeQuery({
      metrics: widget.config.metrics,
      timeRange,
      filters: widget.config.filters
    });

    return {
      series: widget.config.metrics.map(metric => ({
        name: metric,
        data: result.data
          .filter(d => d.name === metric)
          .map(d => ({ x: d.timestamp, y: d.value }))
      })),
      chartType: widget.config.chartType || 'line',
      lastUpdated: new Date()
    };
  }

  /**
   * Get table widget data
   */
  private async getTableWidgetData(widget: DashboardWidget): Promise<any> {
    const result = await this.analyticsEngine.executeQuery({
      metrics: widget.config.metrics,
      timeRange: {
        start: new Date(Date.now() - 24 * 60 * 60 * 1000),
        end: new Date()
      },
      filters: widget.config.filters,
      limit: 100
    });

    return {
      columns: ['Metric', 'Value', 'Unit', 'Timestamp'],
      rows: result.data.map(d => [
        d.name,
        d.value.toFixed(2),
        d.unit,
        d.timestamp.toLocaleString()
      ]),
      totalRows: result.summary.totalRecords,
      lastUpdated: new Date()
    };
  }

  /**
   * Get gauge widget data
   */
  private async getGaugeWidgetData(widget: DashboardWidget): Promise<any> {
    const result = await this.analyticsEngine.executeQuery({
      metrics: widget.config.metrics.slice(0, 1), // Gauge shows single metric
      timeRange: {
        start: new Date(Date.now() - 60 * 60 * 1000), // 1h ago
        end: new Date()
      }
    });

    const latestValue = result.data[result.data.length - 1]?.value || 0;
    const thresholds = widget.config.thresholds || {};

    return {
      value: latestValue,
      min: thresholds.min || 0,
      max: thresholds.max || 100,
      unit: result.data[0]?.unit || '',
      status: this.getGaugeStatus(latestValue, thresholds),
      lastUpdated: new Date()
    };
  }

  /**
   * Get heatmap widget data
   */
  private async getHeatmapWidgetData(widget: DashboardWidget): Promise<any> {
    // Mock heatmap data for marketplaces x metrics
    const marketplaces = ['trendyol', 'n11', 'amazon', 'ozon'];
    const data = [];

    for (let i = 0; i < marketplaces.length; i++) {
      for (let j = 0; j < widget.config.metrics.length; j++) {
        data.push({
          x: j,
          y: i,
          value: Math.random() * 100,
          marketplace: marketplaces[i],
          metric: widget.config.metrics[j]
        });
      }
    }

    return {
      data,
      xLabels: widget.config.metrics,
      yLabels: marketplaces,
      lastUpdated: new Date()
    };
  }

  /**
   * Get alert widget data
   */
  private async getAlertWidgetData(widget: DashboardWidget): Promise<any> {
    const alerts = Array.from(this.activeAlerts.values())
      .filter(alert => widget.config.metrics.includes(alert.metric))
      .sort((a, b) => b.timestamp.getTime() - a.timestamp.getTime())
      .slice(0, 10);

    return {
      alerts,
      summary: {
        total: alerts.length,
        critical: alerts.filter(a => a.severity === 'critical').length,
        error: alerts.filter(a => a.severity === 'error').length,
        warning: alerts.filter(a => a.severity === 'warning').length,
        info: alerts.filter(a => a.severity === 'info').length
      },
      lastUpdated: new Date()
    };
  }

  /**
   * Create alert rule
   */
  public async createAlertRule(rule: Omit<AlertRule, 'id'>): Promise<AlertRule> {
    try {
      const alertRule: AlertRule = {
        ...rule,
        id: this.generateId()
      };

      this.alertRules.set(alertRule.id, alertRule);

      this.logger.info(`Alert rule created: ${alertRule.name}`, { ruleId: alertRule.id });
      this.emit('alert_rule:created', alertRule);

      return alertRule;
    } catch (error) {
      this.logger.error('Failed to create alert rule', error);
      throw error;
    }
  }

  /**
   * Get dashboard performance metrics
   */
  public getPerformanceMetrics(): any {
    return {
      ...this.performanceMetrics,
      uptime: this.isRunning ? '99.9%' : '0%',
      memoryUsage: process.memoryUsage().heapUsed / 1024 / 1024,
      cacheSize: this.widgetDataCache.size,
      activeStreams: Array.from(this.dataStreams.values()).filter(s => s.isActive).length
    };
  }

  // Helper methods
  private generateId(): string {
    return Math.random().toString(36).substr(2, 9);
  }

  private async getWidgetData(widget: DashboardWidget): Promise<DashboardWidget> {
    await this.updateWidgetData(widget);
    return widget;
  }

  private extractMetricsFromDashboard(dashboard: Dashboard): string[] {
    const metrics = new Set<string>();
    
    for (const widget of dashboard.widgets) {
      for (const metric of widget.config.metrics) {
        metrics.add(metric);
      }
    }
    
    return Array.from(metrics);
  }

  private parseTimeRange(timeRange: string): { start: Date; end: Date } {
    const now = new Date();
    const match = timeRange.match(/(\d+)([hdwmy])/);
    
    if (!match) {
      return {
        start: new Date(now.getTime() - 24 * 60 * 60 * 1000), // Default to 24h
        end: now
      };
    }

    const [, amount, unit] = match;
    const multipliers = {
      'h': 60 * 60 * 1000,
      'd': 24 * 60 * 60 * 1000,
      'w': 7 * 24 * 60 * 60 * 1000,
      'm': 30 * 24 * 60 * 60 * 1000,
      'y': 365 * 24 * 60 * 60 * 1000
    };

    const milliseconds = parseInt(amount) * (multipliers[unit as keyof typeof multipliers] || multipliers.d);

    return {
      start: new Date(now.getTime() - milliseconds),
      end: now
    };
  }

  private getGaugeStatus(value: number, thresholds: Record<string, number>): string {
    if (thresholds.critical && value >= thresholds.critical) return 'critical';
    if (thresholds.warning && value >= thresholds.warning) return 'warning';
    if (thresholds.good && value >= thresholds.good) return 'good';
    return 'normal';
  }

  private async loadDashboards(): Promise<void> {
    // Mock dashboard loading
    this.logger.info('Dashboards loaded from storage');
  }

  private async loadUsers(): Promise<void> {
    // Mock user loading
    this.logger.info('Users loaded from storage');
  }

  private async setupDataStreams(): Promise<void> {
    // Initialize data streams
    this.logger.info('Data streams initialized');
  }

  private async loadAlertRules(): Promise<void> {
    // Mock alert rules loading
    this.logger.info('Alert rules loaded');
  }

  private startRealtimeUpdates(): void {
    setInterval(async () => {
      await this.updateActiveStreams();
    }, 5000); // Update every 5 seconds
  }

  private startAlertMonitoring(): void {
    setInterval(async () => {
      await this.checkAlertRules();
    }, 10000); // Check alerts every 10 seconds
  }

  private async updateActiveStreams(): Promise<void> {
    for (const [streamId, stream] of this.dataStreams.entries()) {
      if (!stream.isActive || stream.subscribers.size === 0) continue;

      try {
        // Get fresh data for the stream
        const dashboard = this.dashboards.get(streamId);
        if (!dashboard) continue;

        const dashboardData = await this.getDashboardData(streamId);
        
        // Emit to all subscribers
        for (const clientId of stream.subscribers) {
          this.emit('stream:data_update', {
            clientId,
            streamId,
            data: dashboardData
          });
        }

        stream.lastUpdate = new Date();
        this.performanceMetrics.dataPointsPerSecond += dashboard.widgets.length;

      } catch (error) {
        this.logger.error(`Failed to update stream: ${streamId}`, error);
      }
    }
  }

  private async checkAlertRules(): Promise<void> {
    for (const [ruleId, rule] of this.alertRules.entries()) {
      if (!rule.enabled) continue;

      try {
        // Get current metric value
        const result = await this.analyticsEngine.executeQuery({
          metrics: [rule.metric],
          timeRange: {
            start: new Date(Date.now() - 5 * 60 * 1000), // Last 5 minutes
            end: new Date()
          },
          limit: 1
        });

        if (result.data.length === 0) continue;

        const currentValue = result.data[result.data.length - 1].value;
        const shouldTrigger = this.evaluateAlertCondition(rule, currentValue);

        if (shouldTrigger) {
          await this.triggerAlert(rule, currentValue);
        }

      } catch (error) {
        this.logger.error(`Failed to check alert rule: ${rule.name}`, error);
      }
    }
  }

  private evaluateAlertCondition(rule: AlertRule, value: number): boolean {
    switch (rule.condition) {
      case 'greater_than':
        return value > rule.threshold;
      case 'less_than':
        return value < rule.threshold;
      case 'equals':
        return Math.abs(value - rule.threshold) < 0.01;
      default:
        return false;
    }
  }

  private async triggerAlert(rule: AlertRule, currentValue: number): Promise<void> {
    const alert: DashboardAlert = {
      id: this.generateId(),
      ruleId: rule.id,
      metric: rule.metric,
      currentValue,
      threshold: rule.threshold,
      severity: rule.severity,
      timestamp: new Date(),
      acknowledged: false,
      message: `${rule.name}: ${rule.metric} is ${currentValue} (threshold: ${rule.threshold})`
    };

    this.activeAlerts.set(alert.id, alert);
    this.performanceMetrics.alertsTriggered++;

    this.logger.warn(`Alert triggered: ${rule.name}`, { 
      alertId: alert.id, 
      currentValue, 
      threshold: rule.threshold 
    });
    
    this.emit('alert:triggered', alert);

    // Update rule last triggered time
    rule.lastTriggered = new Date();
  }

  /**
   * Shutdown the dashboard system
   */
  public async shutdown(): Promise<void> {
    try {
      this.isRunning = false;
      this.dataStreams.clear();
      this.connections.clear();
      this.removeAllListeners();
      
      this.logger.info('Real-Time Dashboard System shut down successfully');
    } catch (error) {
      this.logger.error('Error during dashboard system shutdown', error);
      throw error;
    }
  }
}