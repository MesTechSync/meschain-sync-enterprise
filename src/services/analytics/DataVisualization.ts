import { EventEmitter } from 'events';
import { Logger } from '../core/Logger';

/**
 * Data Visualization Service
 * Advanced visualization system providing chart generation,
 * interactive dashboards, custom visualizations, and export capabilities
 */

export interface VisualizationConfig {
  id: string;
  name: string;
  type: 'chart' | 'table' | 'heatmap' | 'treemap' | 'gauge' | 'map' | 'timeline' | 'network' | 'funnel';
  chartType?: 'line' | 'bar' | 'area' | 'pie' | 'donut' | 'scatter' | 'bubble' | 'radar' | 'waterfall' | 'candlestick';
  dataSource: {
    type: 'analytics' | 'predictive' | 'business_intelligence' | 'performance' | 'custom';
    query: string;
    parameters?: Record<string, any>;
    refreshInterval?: number;
  };
  styling: {
    theme: 'light' | 'dark' | 'auto';
    colors: string[];
    width: number;
    height: number;
    responsive: boolean;
  };
  options: {
    title?: string;
    subtitle?: string;
    legend: boolean;
    grid: boolean;
    zoom: boolean;
    export: boolean;
    interactive: boolean;
    animations: boolean;
  };
  axes?: {
    x: AxisConfig;
    y: AxisConfig;
    y2?: AxisConfig;
  };
  series: SeriesConfig[];
  filters?: FilterConfig[];
  drillDown?: DrillDownConfig;
  createdAt: Date;
  updatedAt: Date;
}

export interface AxisConfig {
  label: string;
  type: 'linear' | 'logarithmic' | 'datetime' | 'category';
  min?: number;
  max?: number;
  format?: string;
  unit?: string;
  grid: boolean;
}

export interface SeriesConfig {
  name: string;
  field: string;
  type?: 'line' | 'bar' | 'area';
  color?: string;
  yAxis?: 'y' | 'y2';
  stack?: string;
  aggregation?: 'sum' | 'avg' | 'max' | 'min' | 'count';
  format?: string;
}

export interface FilterConfig {
  field: string;
  type: 'date' | 'select' | 'multiselect' | 'range' | 'text';
  label: string;
  options?: Array<{ value: any; label: string }>;
  defaultValue?: any;
}

export interface DrillDownConfig {
  enabled: boolean;
  levels: Array<{
    field: string;
    label: string;
    chartType?: string;
  }>;
}

export interface VisualizationData {
  id: string;
  configId: string;
  data: any[];
  metadata: {
    totalRows: number;
    lastUpdated: Date;
    executionTime: number;
    dataRange: {
      start: Date;
      end: Date;
    };
  };
  chartData: {
    series: any[];
    categories: string[];
    annotations?: any[];
  };
}

export interface Dashboard {
  id: string;
  name: string;
  description?: string;
  layout: {
    type: 'grid' | 'flex' | 'tabs';
    columns: number;
    rows: number;
  };
  widgets: DashboardWidget[];
  filters: GlobalFilter[];
  settings: {
    autoRefresh: boolean;
    refreshInterval: number;
    theme: 'light' | 'dark' | 'auto';
    fullscreen: boolean;
  };
  permissions: {
    viewers: string[];
    editors: string[];
    owners: string[];
  };
  createdBy: string;
  createdAt: Date;
  updatedAt: Date;
}

export interface DashboardWidget {
  id: string;
  visualizationId: string;
  position: {
    x: number;
    y: number;
    width: number;
    height: number;
  };
  title?: string;
  showTitle: boolean;
  borders: boolean;
  background?: string;
  filters?: Record<string, any>;
}

export interface GlobalFilter {
  id: string;
  field: string;
  type: 'date' | 'select' | 'multiselect';
  label: string;
  value: any;
  affectedWidgets: string[];
}

export interface ExportConfig {
  format: 'png' | 'jpeg' | 'svg' | 'pdf' | 'excel' | 'csv' | 'json';
  quality?: number;
  width?: number;
  height?: number;
  includeData?: boolean;
  filename?: string;
}

export interface VisualizationTemplate {
  id: string;
  name: string;
  category: 'sales' | 'marketing' | 'operations' | 'finance' | 'custom';
  description: string;
  preview: string;
  config: Partial<VisualizationConfig>;
  tags: string[];
  popularity: number;
}

export interface InteractiveEvent {
  type: 'click' | 'hover' | 'zoom' | 'pan' | 'select' | 'drill_down';
  visualization: string;
  data: any;
  timestamp: Date;
  position?: { x: number; y: number };
}

export class DataVisualization extends EventEmitter {
  private logger: Logger;
  private isRunning: boolean = false;

  // Data stores
  private visualizations: Map<string, VisualizationConfig> = new Map();
  private visualizationData: Map<string, VisualizationData> = new Map();
  private dashboards: Map<string, Dashboard> = new Map();
  private templates: Map<string, VisualizationTemplate> = new Map();

  // Caching
  private dataCache: Map<string, { data: any; expiry: Date }> = new Map();
  private cacheExpiryTime = 5 * 60 * 1000; // 5 minutes

  // Performance tracking
  private performanceMetrics = {
    totalVisualizations: 0,
    totalDashboards: 0,
    dataQueriesExecuted: 0,
    avgRenderTime: 0,
    cacheHitRate: 0,
    exportRequests: 0
  };

  // Default color palettes
  private colorPalettes = {
    default: ['#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316'],
    dark: ['#60A5FA', '#F87171', '#34D399', '#FBBF24', '#A78BFA', '#F472B6', '#2DD4BF', '#FB923C'],
    business: ['#1E40AF', '#DC2626', '#059669', '#D97706', '#7C3AED', '#C2185B', '#0D9488', '#EA580C'],
    pastel: ['#DBEAFE', '#FEE2E2', '#D1FAE5', '#FEF3C7', '#EDE9FE', '#FCE7F3', '#CCFBF1', '#FED7AA']
  };

  constructor() {
    super();
    this.logger = new Logger('DataVisualization');
    this.initializeSystem();
  }

  /**
   * Initialize the data visualization system
   */
  private async initializeSystem(): Promise<void> {
    try {
      await this.loadTemplates();
      await this.loadDashboards();
      await this.setupChartEngine();
      
      this.startDataRefresh();
      this.isRunning = true;
      
      this.logger.info('Data Visualization System initialized successfully');
      this.emit('system:initialized');
    } catch (error) {
      this.logger.error('Failed to initialize data visualization system', error);
      throw error;
    }
  }

  /**
   * Create a new visualization
   */
  public async createVisualization(
    config: Omit<VisualizationConfig, 'id' | 'createdAt' | 'updatedAt'>
  ): Promise<VisualizationConfig> {
    try {
      const visualization: VisualizationConfig = {
        ...config,
        id: this.generateId(),
        createdAt: new Date(),
        updatedAt: new Date()
      };

      // Apply default styling if not provided
      if (!visualization.styling.colors || visualization.styling.colors.length === 0) {
        visualization.styling.colors = this.colorPalettes.default;
      }

      this.visualizations.set(visualization.id, visualization);
      this.performanceMetrics.totalVisualizations++;

      // Generate initial data
      await this.refreshVisualizationData(visualization.id);

      this.logger.info(`Visualization created: ${visualization.name}`, { 
        visualizationId: visualization.id 
      });
      
      this.emit('visualization:created', visualization);

      return visualization;
    } catch (error) {
      this.logger.error('Failed to create visualization', error);
      throw error;
    }
  }

  /**
   * Create a new dashboard
   */
  public async createDashboard(
    config: Omit<Dashboard, 'id' | 'createdAt' | 'updatedAt'>
  ): Promise<Dashboard> {
    try {
      const dashboard: Dashboard = {
        ...config,
        id: this.generateId(),
        createdAt: new Date(),
        updatedAt: new Date()
      };

      this.dashboards.set(dashboard.id, dashboard);
      this.performanceMetrics.totalDashboards++;

      this.logger.info(`Dashboard created: ${dashboard.name}`, { 
        dashboardId: dashboard.id 
      });
      
      this.emit('dashboard:created', dashboard);

      return dashboard;
    } catch (error) {
      this.logger.error('Failed to create dashboard', error);
      throw error;
    }
  }

  /**
   * Get visualization data with formatting for charts
   */
  public async getVisualizationData(visualizationId: string): Promise<VisualizationData> {
    try {
      const visualization = this.visualizations.get(visualizationId);
      if (!visualization) {
        throw new Error(`Visualization not found: ${visualizationId}`);
      }

      // Check cache first
      const cacheKey = `viz_${visualizationId}`;
      const cached = this.dataCache.get(cacheKey);
      
      if (cached && cached.expiry > new Date()) {
        this.performanceMetrics.cacheHitRate++;
        return this.visualizationData.get(visualizationId)!;
      }

      // Refresh data
      await this.refreshVisualizationData(visualizationId);
      
      return this.visualizationData.get(visualizationId)!;
    } catch (error) {
      this.logger.error('Failed to get visualization data', error);
      throw error;
    }
  }

  /**
   * Get dashboard with all widget data
   */
  public async getDashboardData(dashboardId: string): Promise<any> {
    try {
      const dashboard = this.dashboards.get(dashboardId);
      if (!dashboard) {
        throw new Error(`Dashboard not found: ${dashboardId}`);
      }

      // Get data for all widgets
      const widgetData = await Promise.all(
        dashboard.widgets.map(async (widget) => {
          const vizData = await this.getVisualizationData(widget.visualizationId);
          return {
            ...widget,
            data: vizData
          };
        })
      );

      return {
        ...dashboard,
        widgets: widgetData,
        lastUpdated: new Date()
      };
    } catch (error) {
      this.logger.error('Failed to get dashboard data', error);
      throw error;
    }
  }

  /**
   * Export visualization or dashboard
   */
  public async exportVisualization(
    visualizationId: string,
    config: ExportConfig
  ): Promise<Buffer | string> {
    try {
      const visualization = this.visualizations.get(visualizationId);
      if (!visualization) {
        throw new Error(`Visualization not found: ${visualizationId}`);
      }

      this.performanceMetrics.exportRequests++;

      switch (config.format) {
        case 'json':
          return this.exportAsJSON(visualizationId);
        case 'csv':
          return this.exportAsCSV(visualizationId);
        case 'excel':
          return this.exportAsExcel(visualizationId);
        case 'png':
        case 'jpeg':
        case 'svg':
        case 'pdf':
          return this.exportAsImage(visualizationId, config);
        default:
          throw new Error(`Unsupported export format: ${config.format}`);
      }
    } catch (error) {
      this.logger.error('Failed to export visualization', error);
      throw error;
    }
  }

  /**
   * Get available visualization templates
   */
  public getTemplates(category?: string): VisualizationTemplate[] {
    const templates = Array.from(this.templates.values());
    
    if (category) {
      return templates.filter(t => t.category === category);
    }
    
    return templates.sort((a, b) => b.popularity - a.popularity);
  }

  /**
   * Create visualization from template
   */
  public async createFromTemplate(
    templateId: string,
    customConfig?: Partial<VisualizationConfig>
  ): Promise<VisualizationConfig> {
    try {
      const template = this.templates.get(templateId);
      if (!template) {
        throw new Error(`Template not found: ${templateId}`);
      }

      const config: Omit<VisualizationConfig, 'id' | 'createdAt' | 'updatedAt'> = {
        ...template.config as any,
        ...customConfig,
        name: customConfig?.name || `${template.name} - ${new Date().toLocaleDateString()}`
      };

      return await this.createVisualization(config);
    } catch (error) {
      this.logger.error('Failed to create visualization from template', error);
      throw error;
    }
  }

  /**
   * Process interactive events
   */
  public processInteractiveEvent(event: InteractiveEvent): void {
    try {
      this.logger.debug('Processing interactive event', { event });

      switch (event.type) {
        case 'drill_down':
          this.handleDrillDown(event);
          break;
        case 'zoom':
          this.handleZoom(event);
          break;
        case 'select':
          this.handleSelection(event);
          break;
        default:
          // Handle other event types
          break;
      }

      this.emit('interaction:event', event);
    } catch (error) {
      this.logger.error('Failed to process interactive event', error);
    }
  }

  /**
   * Get system performance metrics
   */
  public getPerformanceMetrics(): any {
    return {
      ...this.performanceMetrics,
      uptime: this.isRunning ? process.uptime() : 0,
      memoryUsage: process.memoryUsage().heapUsed / 1024 / 1024,
      cacheSize: this.dataCache.size,
      activeVisualizations: this.visualizations.size,
      activeDashboards: this.dashboards.size
    };
  }

  // Helper methods
  private generateId(): string {
    return Math.random().toString(36).substr(2, 9);
  }

  private async refreshVisualizationData(visualizationId: string): Promise<void> {
    try {
      const startTime = Date.now();
      const visualization = this.visualizations.get(visualizationId);
      if (!visualization) return;

      // Mock data generation based on visualization type
      const rawData = await this.generateMockData(visualization);
      
      // Transform data for chart rendering
      const chartData = this.transformDataForChart(rawData, visualization);

      const visualizationData: VisualizationData = {
        id: this.generateId(),
        configId: visualizationId,
        data: rawData,
        metadata: {
          totalRows: rawData.length,
          lastUpdated: new Date(),
          executionTime: Date.now() - startTime,
          dataRange: {
            start: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000),
            end: new Date()
          }
        },
        chartData
      };

      this.visualizationData.set(visualizationId, visualizationData);
      this.performanceMetrics.dataQueriesExecuted++;

      // Cache the data
      this.dataCache.set(`viz_${visualizationId}`, {
        data: visualizationData,
        expiry: new Date(Date.now() + this.cacheExpiryTime)
      });

      this.emit('data:refreshed', { visualizationId, data: visualizationData });

    } catch (error) {
      this.logger.error(`Failed to refresh visualization data: ${visualizationId}`, error);
    }
  }

  private async generateMockData(visualization: VisualizationConfig): Promise<any[]> {
    const data = [];
    const days = 30;
    
    switch (visualization.type) {
      case 'chart':
        // Generate time series data
        for (let i = 0; i < days; i++) {
          const date = new Date(Date.now() - (days - i) * 24 * 60 * 60 * 1000);
          data.push({
            date: date.toISOString().split('T')[0],
            revenue: Math.random() * 10000 + 5000,
            orders: Math.random() * 100 + 50,
            conversion_rate: Math.random() * 5 + 2,
            marketplace: ['trendyol', 'n11', 'amazon'][Math.floor(Math.random() * 3)]
          });
        }
        break;
        
      case 'table':
        // Generate tabular data
        const marketplaces = ['Trendyol', 'N11', 'Amazon', 'Ozon', 'Hepsiburada'];
        for (const marketplace of marketplaces) {
          data.push({
            marketplace,
            revenue: Math.random() * 50000 + 20000,
            orders: Math.random() * 500 + 200,
            products: Math.random() * 1000 + 500,
            rating: Math.random() * 2 + 3
          });
        }
        break;
        
      case 'heatmap':
        // Generate heatmap data
        const hours = Array.from({ length: 24 }, (_, i) => i);
        const weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        
        for (const day of weekdays) {
          for (const hour of hours) {
            data.push({
              day,
              hour,
              value: Math.random() * 100
            });
          }
        }
        break;
        
      default:
        // Default data structure
        for (let i = 0; i < 20; i++) {
          data.push({
            category: `Category ${i + 1}`,
            value: Math.random() * 1000 + 100,
            percentage: Math.random() * 100
          });
        }
    }
    
    return data;
  }

  private transformDataForChart(data: any[], visualization: VisualizationConfig): any {
    const chartData: any = {
      series: [],
      categories: [],
      annotations: []
    };

    switch (visualization.chartType) {
      case 'line':
      case 'area':
        // Transform for time series charts
        if (data.length > 0 && data[0].date) {
          chartData.categories = data.map(d => d.date);
          
          for (const series of visualization.series) {
            chartData.series.push({
              name: series.name,
              data: data.map(d => d[series.field] || 0),
              type: series.type || visualization.chartType,
              color: series.color
            });
          }
        }
        break;
        
      case 'bar':
        // Transform for bar charts
        chartData.categories = data.map(d => d.category || d.marketplace || d.name);
        
        for (const series of visualization.series) {
          chartData.series.push({
            name: series.name,
            data: data.map(d => d[series.field] || 0),
            color: series.color
          });
        }
        break;
        
      case 'pie':
      case 'donut':
        // Transform for pie charts
        chartData.series = [{
          name: visualization.series[0]?.name || 'Data',
          data: data.map(d => ({
            name: d.category || d.marketplace || d.name,
            y: d[visualization.series[0]?.field] || d.value || 0,
            color: this.getNextColor(visualization.styling.colors, data.indexOf(d))
          }))
        }];
        break;
        
      default:
        // Default transformation
        chartData.series = visualization.series.map(series => ({
          name: series.name,
          data: data.map(d => d[series.field] || 0)
        }));
        chartData.categories = data.map((d, i) => d.category || `Item ${i + 1}`);
    }

    return chartData;
  }

  private getNextColor(colors: string[], index: number): string {
    return colors[index % colors.length];
  }

  private async loadTemplates(): Promise<void> {
    const defaultTemplates: VisualizationTemplate[] = [
      {
        id: 'revenue_trend',
        name: 'Revenue Trend',
        category: 'sales',
        description: 'Track revenue trends over time across all marketplaces',
        preview: 'chart_line_preview.png',
        config: {
          type: 'chart',
          chartType: 'line',
          dataSource: {
            type: 'analytics',
            query: 'revenue_by_date'
          },
          styling: {
            theme: 'light',
            colors: this.colorPalettes.business,
            width: 800,
            height: 400,
            responsive: true
          },
          options: {
            title: 'Revenue Trend',
            legend: true,
            grid: true,
            zoom: true,
            export: true,
            interactive: true,
            animations: true
          },
          series: [
            { name: 'Revenue', field: 'revenue', aggregation: 'sum' }
          ]
        },
        tags: ['revenue', 'trend', 'sales'],
        popularity: 95
      },
      {
        id: 'marketplace_performance',
        name: 'Marketplace Performance',
        category: 'operations',
        description: 'Compare performance metrics across different marketplaces',
        preview: 'chart_bar_preview.png',
        config: {
          type: 'chart',
          chartType: 'bar',
          dataSource: {
            type: 'business_intelligence',
            query: 'marketplace_kpis'
          },
          styling: {
            theme: 'light',
            colors: this.colorPalettes.default,
            width: 600,
            height: 400,
            responsive: true
          },
          options: {
            title: 'Marketplace Performance',
            legend: true,
            grid: true,
            export: true,
            interactive: true,
            animations: true
          },
          series: [
            { name: 'Revenue', field: 'revenue' },
            { name: 'Orders', field: 'orders', yAxis: 'y2' }
          ]
        },
        tags: ['marketplace', 'performance', 'comparison'],
        popularity: 88
      },
      {
        id: 'order_heatmap',
        name: 'Order Activity Heatmap',
        category: 'operations',
        description: 'Visualize order activity patterns by time and day',
        preview: 'heatmap_preview.png',
        config: {
          type: 'heatmap',
          dataSource: {
            type: 'analytics',
            query: 'orders_by_time'
          },
          styling: {
            theme: 'light',
            colors: ['#FEF3C7', '#FCD34D', '#F59E0B', '#D97706', '#92400E'],
            width: 800,
            height: 300,
            responsive: true
          },
          options: {
            title: 'Order Activity Heatmap',
            interactive: true,
            export: true
          },
          series: [
            { name: 'Orders', field: 'value' }
          ]
        },
        tags: ['orders', 'heatmap', 'activity'],
        popularity: 76
      }
    ];

    for (const template of defaultTemplates) {
      this.templates.set(template.id, template);
    }

    this.logger.info(`Loaded ${defaultTemplates.length} visualization templates`);
  }

  private async loadDashboards(): Promise<void> {
    this.logger.info('Loading dashboards...');
  }

  private async setupChartEngine(): Promise<void> {
    this.logger.info('Setting up chart rendering engine...');
  }

  private startDataRefresh(): void {
    setInterval(() => {
      this.refreshAllVisualizations();
    }, 60000); // Refresh every minute
  }

  private async refreshAllVisualizations(): Promise<void> {
    for (const visualization of this.visualizations.values()) {
      if (visualization.dataSource.refreshInterval) {
        const lastUpdate = this.visualizationData.get(visualization.id)?.metadata.lastUpdated;
        if (!lastUpdate || 
            Date.now() - lastUpdate.getTime() > visualization.dataSource.refreshInterval) {
          await this.refreshVisualizationData(visualization.id);
        }
      }
    }
  }

  private async exportAsJSON(visualizationId: string): Promise<string> {
    const data = this.visualizationData.get(visualizationId);
    return JSON.stringify(data, null, 2);
  }

  private async exportAsCSV(visualizationId: string): Promise<string> {
    const data = this.visualizationData.get(visualizationId);
    if (!data || data.data.length === 0) return '';

    const headers = Object.keys(data.data[0]);
    const csvRows = [
      headers.join(','),
      ...data.data.map(row => 
        headers.map(header => 
          typeof row[header] === 'string' ? `"${row[header]}"` : row[header]
        ).join(',')
      )
    ];

    return csvRows.join('\n');
  }

  private async exportAsExcel(visualizationId: string): Promise<Buffer> {
    // Mock Excel export - would use a library like exceljs
    const data = await this.exportAsCSV(visualizationId);
    return Buffer.from(data, 'utf-8');
  }

  private async exportAsImage(
    visualizationId: string, 
    config: ExportConfig
  ): Promise<Buffer> {
    // Mock image export - would use a library like puppeteer or canvas
    const visualization = this.visualizations.get(visualizationId);
    const mockImageData = `Mock ${config.format.toUpperCase()} image for ${visualization?.name}`;
    return Buffer.from(mockImageData, 'utf-8');
  }

  private handleDrillDown(event: InteractiveEvent): void {
    // Implement drill-down functionality
    this.logger.info('Processing drill-down event', { visualization: event.visualization });
  }

  private handleZoom(event: InteractiveEvent): void {
    // Implement zoom functionality
    this.logger.info('Processing zoom event', { visualization: event.visualization });
  }

  private handleSelection(event: InteractiveEvent): void {
    // Implement selection functionality
    this.logger.info('Processing selection event', { visualization: event.visualization });
  }

  /**
   * Update visualization configuration
   */
  public updateVisualization(
    visualizationId: string,
    updates: Partial<VisualizationConfig>
  ): VisualizationConfig {
    try {
      const visualization = this.visualizations.get(visualizationId);
      if (!visualization) {
        throw new Error(`Visualization not found: ${visualizationId}`);
      }

      const updated = {
        ...visualization,
        ...updates,
        updatedAt: new Date()
      };

      this.visualizations.set(visualizationId, updated);

      this.logger.info(`Visualization updated: ${updated.name}`, { visualizationId });
      this.emit('visualization:updated', updated);

      return updated;
    } catch (error) {
      this.logger.error('Failed to update visualization', error);
      throw error;
    }
  }

  /**
   * Delete visualization
   */
  public deleteVisualization(visualizationId: string): void {
    try {
      const visualization = this.visualizations.get(visualizationId);
      if (!visualization) {
        throw new Error(`Visualization not found: ${visualizationId}`);
      }

      this.visualizations.delete(visualizationId);
      this.visualizationData.delete(visualizationId);
      this.dataCache.delete(`viz_${visualizationId}`);

      this.logger.info(`Visualization deleted: ${visualization.name}`, { visualizationId });
      this.emit('visualization:deleted', { visualizationId, visualization });
    } catch (error) {
      this.logger.error('Failed to delete visualization', error);
      throw error;
    }
  }

  /**
   * Shutdown the data visualization system
   */
  public async shutdown(): Promise<void> {
    try {
      this.isRunning = false;
      this.dataCache.clear();
      this.removeAllListeners();
      
      this.logger.info('Data Visualization System shut down successfully');
    } catch (error) {
      this.logger.error('Error during data visualization shutdown', error);
      throw error;
    }
  }
}