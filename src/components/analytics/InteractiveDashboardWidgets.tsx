/**
 * Interactive Dashboard Widget System
 * Priority 4: Advanced Dashboard Analytics & Real-time Updates
 * 
 * @version 4.0.0
 * @author MesChain Sync Team - Cursor Team Priority 4
 */

import React, { useState, useEffect, useMemo, useCallback, useRef } from 'react';
import { MS365Colors, MS365Typography, MS365Spacing, AdvancedMS365Theme } from '../../theme/microsoft365-advanced';
import { MS365Card } from '../Microsoft365/MS365Card';
import { MS365Button } from '../Microsoft365/MS365Button';
import { MS365Charts } from '../Microsoft365/MS365Charts';

// TypeScript Interfaces
export interface WidgetPosition {
  x: number;
  y: number;
  w: number;
  h: number;
}

export interface DashboardWidget {
  id: string;
  title: string;
  type: 'chart' | 'metric' | 'table' | 'status' | 'heatmap' | 'gauge' | 'timeline' | 'custom';
  size: 'small' | 'medium' | 'large' | 'xlarge';
  position: WidgetPosition;
  config: {
    refreshInterval: number;
    dataSource: string;
    chartType?: 'line' | 'bar' | 'pie' | 'area' | 'scatter' | 'donut';
    colors?: string[];
    showLegend?: boolean;
    showGrid?: boolean;
    thresholds?: { warning: number; critical: number };
    [key: string]: any;
  };
  data: any;
  isVisible: boolean;
  isLoading: boolean;
  isDragging: boolean;
  isResizing: boolean;
  error?: string;
  lastUpdate?: Date;
  permissions: {
    canMove: boolean;
    canResize: boolean;
    canDelete: boolean;
    canConfigure: boolean;
  };
}

export interface WidgetTemplate {
  id: string;
  name: string;
  description: string;
  type: DashboardWidget['type'];
  icon: string;
  defaultSize: DashboardWidget['size'];
  defaultConfig: DashboardWidget['config'];
  category: 'business' | 'technical' | 'marketplace' | 'analytics';
}

export interface DashboardLayout {
  id: string;
  name: string;
  description: string;
  widgets: DashboardWidget[];
  gridSize: { cols: number; rows: number };
  isDefault: boolean;
  createdBy: string;
  createdAt: Date;
  lastModified: Date;
}

// Widget Templates Factory
const createWidgetTemplates = (): WidgetTemplate[] => [
  {
    id: 'revenue_chart',
    name: 'Revenue Chart',
    description: 'Real-time revenue tracking with trend analysis',
    type: 'chart',
    icon: '💰',
    defaultSize: 'large',
    defaultConfig: {
      refreshInterval: 30000,
      dataSource: 'revenue_api',
      chartType: 'line',
      colors: [MS365Colors.primary.green[500], MS365Colors.primary.blue[500]],
      showLegend: true,
      showGrid: true
    },
    category: 'business'
  },
  {
    id: 'system_metrics',
    name: 'System Metrics',
    description: 'CPU, Memory, and Disk usage monitoring',
    type: 'gauge',
    icon: '🖥️',
    defaultSize: 'medium',
    defaultConfig: {
      refreshInterval: 5000,
      dataSource: 'system_api',
      thresholds: { warning: 70, critical: 85 }
    },
    category: 'technical'
  },
  {
    id: 'order_status',
    name: 'Order Status',
    description: 'Real-time order processing status',
    type: 'status',
    icon: '📦',
    defaultSize: 'small',
    defaultConfig: {
      refreshInterval: 10000,
      dataSource: 'orders_api'
    },
    category: 'business'
  },
  {
    id: 'marketplace_health',
    name: 'Marketplace Health',
    description: 'Health status of all integrated marketplaces',
    type: 'heatmap',
    icon: '🏪',
    defaultSize: 'large',
    defaultConfig: {
      refreshInterval: 60000,
      dataSource: 'marketplace_api'
    },
    category: 'marketplace'
  },
  {
    id: 'api_performance',
    name: 'API Performance',
    description: 'Response times and error rates',
    type: 'chart',
    icon: '🌐',
    defaultSize: 'medium',
    defaultConfig: {
      refreshInterval: 15000,
      dataSource: 'api_metrics',
      chartType: 'area',
      colors: [MS365Colors.primary.blue[500]]
    },
    category: 'technical'
  },
  {
    id: 'sales_funnel',
    name: 'Sales Funnel',
    description: 'Conversion funnel analysis',
    type: 'chart',
    icon: '📊',
    defaultSize: 'large',
    defaultConfig: {
      refreshInterval: 300000,
      dataSource: 'analytics_api',
      chartType: 'bar'
    },
    category: 'analytics'
  }
];

// Drag and Drop Hook
const useDragAndDrop = (
  widgets: DashboardWidget[],
  onUpdateWidget: (id: string, updates: Partial<DashboardWidget>) => void
) => {
  const [dragState, setDragState] = useState<{
    isDragging: boolean;
    draggedWidget: string | null;
    dragOffset: { x: number; y: number };
    originalPosition: WidgetPosition | null;
  }>({
    isDragging: false,
    draggedWidget: null,
    dragOffset: { x: 0, y: 0 },
    originalPosition: null
  });

  const handleMouseDown = useCallback((
    e: React.MouseEvent,
    widget: DashboardWidget
  ) => {
    if (!widget.permissions.canMove) return;

    const rect = (e.target as HTMLElement).closest('.widget-container')?.getBoundingClientRect();
    if (!rect) return;

    setDragState({
      isDragging: true,
      draggedWidget: widget.id,
      dragOffset: {
        x: e.clientX - rect.left,
        y: e.clientY - rect.top
      },
      originalPosition: { ...widget.position }
    });

    onUpdateWidget(widget.id, { isDragging: true });
  }, [onUpdateWidget]);

  const handleMouseMove = useCallback((e: MouseEvent) => {
    if (!dragState.isDragging || !dragState.draggedWidget) return;

    const container = document.querySelector('.dashboard-grid');
    if (!container) return;

    const containerRect = container.getBoundingClientRect();
    const gridSize = 50; // 50px grid

    const newX = Math.round((e.clientX - containerRect.left - dragState.dragOffset.x) / gridSize) * gridSize;
    const newY = Math.round((e.clientY - containerRect.top - dragState.dragOffset.y) / gridSize) * gridSize;

    onUpdateWidget(dragState.draggedWidget, {
      position: {
        ...dragState.originalPosition!,
        x: Math.max(0, newX),
        y: Math.max(0, newY)
      }
    });
  }, [dragState, onUpdateWidget]);

  const handleMouseUp = useCallback(() => {
    if (dragState.draggedWidget) {
      onUpdateWidget(dragState.draggedWidget, { isDragging: false });
    }

    setDragState({
      isDragging: false,
      draggedWidget: null,
      dragOffset: { x: 0, y: 0 },
      originalPosition: null
    });
  }, [dragState.draggedWidget, onUpdateWidget]);

  useEffect(() => {
    if (dragState.isDragging) {
      document.addEventListener('mousemove', handleMouseMove);
      document.addEventListener('mouseup', handleMouseUp);
      
      return () => {
        document.removeEventListener('mousemove', handleMouseMove);
        document.removeEventListener('mouseup', handleMouseUp);
      };
    }
  }, [dragState.isDragging, handleMouseMove, handleMouseUp]);

  return { dragState, handleMouseDown };
};

// Widget Data Service
class WidgetDataService {
  private dataCache: Map<string, { data: any; timestamp: number }> = new Map();
  private refreshIntervals: Map<string, NodeJS.Timeout> = new Map();

  public async fetchWidgetData(widget: DashboardWidget): Promise<any> {
    const cacheKey = `${widget.id}_${widget.config.dataSource}`;
    const cached = this.dataCache.get(cacheKey);
    const now = Date.now();

    // Return cached data if still fresh
    if (cached && (now - cached.timestamp) < widget.config.refreshInterval) {
      return cached.data;
    }

    // Simulate API calls with realistic data
    const data = this.generateMockData(widget);
    this.dataCache.set(cacheKey, { data, timestamp: now });
    
    return data;
  }

  private generateMockData(widget: DashboardWidget): any {
    switch (widget.type) {
      case 'chart':
        return this.generateChartData(widget);
      case 'metric':
        return this.generateMetricData(widget);
      case 'gauge':
        return this.generateGaugeData(widget);
      case 'status':
        return this.generateStatusData(widget);
      case 'heatmap':
        return this.generateHeatmapData(widget);
      default:
        return { value: Math.random() * 100 };
    }
  }

  private generateChartData(widget: DashboardWidget) {
    const points = 20;
    const baseValue = 1000;
    const data = [];

    for (let i = 0; i < points; i++) {
      const timestamp = Date.now() - (points - i) * 300000; // 5-minute intervals
      const trend = widget.config.dataSource.includes('revenue') ? 1.02 : 0.98;
      const value = baseValue * Math.pow(trend, i) + (Math.random() - 0.5) * 200;
      
      data.push({
        timestamp,
        value: Math.max(0, value),
        label: new Date(timestamp).toLocaleTimeString()
      });
    }

    return {
      series: [{
        name: widget.title,
        data: data.map(d => d.value),
        labels: data.map(d => d.label)
      }],
      categories: data.map(d => d.label),
      total: data.reduce((sum, d) => sum + d.value, 0)
    };
  }

  private generateMetricData(widget: DashboardWidget) {
    const baseValue = widget.config.dataSource.includes('revenue') ? 45678 : 342;
    const change = (Math.random() - 0.5) * 20; // -10% to +10%
    
    return {
      current: baseValue + (baseValue * change / 100),
      previous: baseValue,
      change: change,
      trend: change > 0 ? 'up' : change < 0 ? 'down' : 'stable',
      format: widget.config.dataSource.includes('revenue') ? 'currency' : 'number'
    };
  }

  private generateGaugeData(widget: DashboardWidget) {
    const value = 30 + Math.random() * 50; // 30-80%
    const thresholds = widget.config.thresholds || { warning: 70, critical: 85 };
    
    return {
      value,
      max: 100,
      thresholds,
      status: value > thresholds.critical ? 'critical' : 
              value > thresholds.warning ? 'warning' : 'good',
      unit: '%'
    };
  }

  private generateStatusData(widget: DashboardWidget) {
    const statuses = ['online', 'offline', 'maintenance', 'error'];
    const items = ['Trendyol', 'Amazon', 'N11', 'Hepsiburada', 'Ozon', 'eBay'];
    
    return items.map(item => ({
      name: item,
      status: statuses[Math.floor(Math.random() * statuses.length)],
      lastUpdate: new Date(Date.now() - Math.random() * 3600000),
      metrics: {
        orders: Math.floor(Math.random() * 500),
        products: Math.floor(Math.random() * 2000)
      }
    }));
  }

  private generateHeatmapData(widget: DashboardWidget) {
    const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    const hours = Array.from({ length: 24 }, (_, i) => `${i}:00`);
    
    return days.map(day => 
      hours.map(hour => ({
        day,
        hour,
        value: Math.random() * 100
      }))
    ).flat();
  }

  public startAutoRefresh(widget: DashboardWidget, onUpdate: (data: any) => void) {
    this.stopAutoRefresh(widget.id);
    
    const interval = setInterval(async () => {
      try {
        const data = await this.fetchWidgetData(widget);
        onUpdate(data);
      } catch (error) {
        console.error(`Error refreshing widget ${widget.id}:`, error);
      }
    }, widget.config.refreshInterval);

    this.refreshIntervals.set(widget.id, interval);
  }

  public stopAutoRefresh(widgetId: string) {
    const interval = this.refreshIntervals.get(widgetId);
    if (interval) {
      clearInterval(interval);
      this.refreshIntervals.delete(widgetId);
    }
  }

  public cleanup() {
    this.refreshIntervals.forEach(interval => clearInterval(interval));
    this.refreshIntervals.clear();
    this.dataCache.clear();
  }
}

// Widget Components
const WidgetContainer: React.FC<{
  widget: DashboardWidget;
  onUpdate: (id: string, updates: Partial<DashboardWidget>) => void;
  onDelete: (id: string) => void;
  onMouseDown: (e: React.MouseEvent, widget: DashboardWidget) => void;
}> = ({ widget, onUpdate, onDelete, onMouseDown }) => {
  const [isHovered, setIsHovered] = useState(false);

  const getSizePixels = (size: DashboardWidget['size']) => {
    switch (size) {
      case 'small': return { width: 250, height: 200 };
      case 'medium': return { width: 350, height: 300 };
      case 'large': return { width: 500, height: 400 };
      case 'xlarge': return { width: 700, height: 500 };
      default: return { width: 350, height: 300 };
    }
  };

  const sizePixels = getSizePixels(widget.size);

  return (
    <div
      className="widget-container"
      style={{
        position: 'absolute',
        left: widget.position.x,
        top: widget.position.y,
        width: sizePixels.width,
        height: sizePixels.height,
        cursor: widget.isDragging ? 'grabbing' : widget.permissions.canMove ? 'grab' : 'default',
        transform: widget.isDragging ? 'scale(1.02)' : 'scale(1)',
        transition: widget.isDragging ? 'none' : 'transform 0.2s ease',
        zIndex: widget.isDragging ? 1000 : 1,
        opacity: widget.isVisible ? 1 : 0.5
      }}
      onMouseEnter={() => setIsHovered(true)}
      onMouseLeave={() => setIsHovered(false)}
    >
      {/* Widget Header */}
      <div
        style={{
          position: 'absolute',
          top: -10,
          left: 0,
          right: 0,
          height: '30px',
          background: isHovered ? 'rgba(0,0,0,0.1)' : 'transparent',
          borderRadius: '8px 8px 0 0',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
          padding: `0 ${MS365Spacing[2]}`,
          opacity: isHovered ? 1 : 0,
          transition: 'opacity 0.2s ease'
        }}
        onMouseDown={(e) => onMouseDown(e, widget)}
      >
        <div style={{
          fontSize: MS365Typography.sizes.xs,
          fontWeight: MS365Typography.weights.semibold,
          color: MS365Colors.neutral[700]
        }}>
          {widget.title}
        </div>
        
        <div style={{ display: 'flex', gap: MS365Spacing[1] }}>
          {widget.permissions.canConfigure && (
            <button
              style={{
                background: 'none',
                border: 'none',
                cursor: 'pointer',
                fontSize: '12px',
                padding: '2px 4px',
                borderRadius: '4px',
                color: MS365Colors.neutral[600]
              }}
              onClick={() => {/* TODO: Open config */}}
            >
              ⚙️
            </button>
          )}
          
          {widget.permissions.canDelete && (
            <button
              style={{
                background: 'none',
                border: 'none',
                cursor: 'pointer',
                fontSize: '12px',
                padding: '2px 4px',
                borderRadius: '4px',
                color: MS365Colors.primary.red[600]
              }}
              onClick={() => onDelete(widget.id)}
            >
              ✕
            </button>
          )}
        </div>
      </div>

      {/* Widget Content */}
      <MS365Card
        title=""
        variant="elevated"
        style={{
          width: '100%',
          height: '100%',
          border: widget.isDragging ? `2px solid ${MS365Colors.primary.blue[500]}` : undefined
        }}
        content={<WidgetContent widget={widget} />}
      />
    </div>
  );
};

const WidgetContent: React.FC<{ widget: DashboardWidget }> = ({ widget }) => {
  if (widget.isLoading) {
    return (
      <div style={{
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center',
        height: '100%',
        color: MS365Colors.neutral[600]
      }}>
        <div>Loading...</div>
      </div>
    );
  }

  if (widget.error) {
    return (
      <div style={{
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center',
        height: '100%',
        color: MS365Colors.primary.red[600],
        textAlign: 'center'
      }}>
        <div>
          <div style={{ fontSize: '2rem', marginBottom: MS365Spacing[2] }}>⚠️</div>
          <div>Error loading widget</div>
          <div style={{ fontSize: MS365Typography.sizes.xs }}>{widget.error}</div>
        </div>
      </div>
    );
  }

  switch (widget.type) {
    case 'metric':
      return <MetricWidget widget={widget} />;
    case 'chart':
      return <ChartWidget widget={widget} />;
    case 'status':
      return <StatusWidget widget={widget} />;
    case 'gauge':
      return <GaugeWidget widget={widget} />;
    default:
      return (
        <div style={{
          display: 'flex',
          justifyContent: 'center',
          alignItems: 'center',
          height: '100%',
          color: MS365Colors.neutral[600]
        }}>
          <div>{widget.title}</div>
        </div>
      );
  }
};

// Individual Widget Types
const MetricWidget: React.FC<{ widget: DashboardWidget }> = ({ widget }) => {
  const data = widget.data || { current: 0, change: 0, trend: 'stable' };
  
  return (
    <div style={{
      display: 'flex',
      flexDirection: 'column',
      justifyContent: 'center',
      alignItems: 'center',
      height: '100%',
      padding: MS365Spacing[4]
    }}>
      <div style={{
        fontSize: '2.5rem',
        fontWeight: MS365Typography.weights.bold,
        color: MS365Colors.neutral[900],
        marginBottom: MS365Spacing[2]
      }}>
        {data.format === 'currency' ? `$${data.current.toLocaleString()}` : data.current.toLocaleString()}
      </div>
      
      <div style={{
        fontSize: MS365Typography.sizes.sm,
        color: MS365Colors.neutral[600],
        marginBottom: MS365Spacing[2]
      }}>
        {widget.title}
      </div>
      
      <div style={{
        display: 'flex',
        alignItems: 'center',
        gap: MS365Spacing[1],
        fontSize: MS365Typography.sizes.sm,
        color: data.trend === 'up' ? MS365Colors.primary.green[600] : 
              data.trend === 'down' ? MS365Colors.primary.red[600] : 
              MS365Colors.neutral[600]
      }}>
        <span>{data.trend === 'up' ? '↗️' : data.trend === 'down' ? '↘️' : '➡️'}</span>
        <span>{Math.abs(data.change).toFixed(1)}%</span>
      </div>
    </div>
  );
};

const ChartWidget: React.FC<{ widget: DashboardWidget }> = ({ widget }) => {
  const data = widget.data || { series: [], categories: [] };
  
  return (
    <div style={{
      height: '100%',
      padding: MS365Spacing[3]
    }}>
      <div style={{
        fontSize: MS365Typography.sizes.base,
        fontWeight: MS365Typography.weights.semibold,
        marginBottom: MS365Spacing[3],
        color: MS365Colors.neutral[900]
      }}>
        {widget.title}
      </div>
      
      {data.series.length > 0 ? (
        <MS365Charts
          type={widget.config.chartType || 'line'}
          data={data.series}
          categories={data.categories}
          height={200}
          colors={widget.config.colors}
        />
      ) : (
        <div style={{
          display: 'flex',
          justifyContent: 'center',
          alignItems: 'center',
          height: '150px',
          color: MS365Colors.neutral[500]
        }}>
          No data available
        </div>
      )}
    </div>
  );
};

const StatusWidget: React.FC<{ widget: DashboardWidget }> = ({ widget }) => {
  const data = widget.data || [];
  
  const getStatusColor = (status: string) => {
    switch (status) {
      case 'online': return MS365Colors.primary.green[500];
      case 'offline': return MS365Colors.neutral[400];
      case 'maintenance': return '#f59e0b';
      case 'error': return MS365Colors.primary.red[500];
      default: return MS365Colors.neutral[400];
    }
  };

  return (
    <div style={{
      height: '100%',
      padding: MS365Spacing[3],
      overflow: 'auto'
    }}>
      <div style={{
        fontSize: MS365Typography.sizes.base,
        fontWeight: MS365Typography.weights.semibold,
        marginBottom: MS365Spacing[3],
        color: MS365Colors.neutral[900]
      }}>
        {widget.title}
      </div>
      
      <div style={{ display: 'flex', flexDirection: 'column', gap: MS365Spacing[2] }}>
        {data.map((item: any, index: number) => (
          <div key={index} style={{
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'center',
            padding: MS365Spacing[2],
            backgroundColor: MS365Colors.neutral[50],
            borderRadius: AdvancedMS365Theme.components.cards.radiuses.sm,
            border: `1px solid ${getStatusColor(item.status)}`
          }}>
            <div>
              <div style={{
                fontSize: MS365Typography.sizes.sm,
                fontWeight: MS365Typography.weights.medium
              }}>
                {item.name}
              </div>
              <div style={{
                fontSize: MS365Typography.sizes.xs,
                color: MS365Colors.neutral[600]
              }}>
                Orders: {item.metrics?.orders || 0}
              </div>
            </div>
            <div style={{
              width: '12px',
              height: '12px',
              borderRadius: '50%',
              backgroundColor: getStatusColor(item.status)
            }} />
          </div>
        ))}
      </div>
    </div>
  );
};

const GaugeWidget: React.FC<{ widget: DashboardWidget }> = ({ widget }) => {
  const data = widget.data || { value: 0, max: 100, status: 'good' };
  const percentage = (data.value / data.max) * 100;
  
  const getStatusColor = (status: string) => {
    switch (status) {
      case 'good': return MS365Colors.primary.green[500];
      case 'warning': return '#f59e0b';
      case 'critical': return MS365Colors.primary.red[500];
      default: return MS365Colors.neutral[400];
    }
  };

  return (
    <div style={{
      display: 'flex',
      flexDirection: 'column',
      justifyContent: 'center',
      alignItems: 'center',
      height: '100%',
      padding: MS365Spacing[4]
    }}>
      <div style={{
        fontSize: MS365Typography.sizes.base,
        fontWeight: MS365Typography.weights.semibold,
        marginBottom: MS365Spacing[4],
        color: MS365Colors.neutral[900]
      }}>
        {widget.title}
      </div>
      
      <div style={{
        position: 'relative',
        width: '120px',
        height: '120px',
        marginBottom: MS365Spacing[3]
      }}>
        <svg width="120" height="120" style={{ transform: 'rotate(-90deg)' }}>
          <circle
            cx="60"
            cy="60"
            r="50"
            fill="none"
            stroke={MS365Colors.neutral[200]}
            strokeWidth="10"
          />
          <circle
            cx="60"
            cy="60"
            r="50"
            fill="none"
            stroke={getStatusColor(data.status)}
            strokeWidth="10"
            strokeDasharray={`${percentage * 3.14159} 314.159`}
            strokeLinecap="round"
            style={{ transition: 'stroke-dasharray 0.5s ease' }}
          />
        </svg>
        
        <div style={{
          position: 'absolute',
          top: '50%',
          left: '50%',
          transform: 'translate(-50%, -50%)',
          textAlign: 'center'
        }}>
          <div style={{
            fontSize: '1.5rem',
            fontWeight: MS365Typography.weights.bold,
            color: getStatusColor(data.status)
          }}>
            {data.value.toFixed(1)}
          </div>
          <div style={{
            fontSize: MS365Typography.sizes.xs,
            color: MS365Colors.neutral[600]
          }}>
            {data.unit || '%'}
          </div>
        </div>
      </div>
      
      <div style={{
        fontSize: MS365Typography.sizes.sm,
        color: getStatusColor(data.status),
        textTransform: 'uppercase',
        fontWeight: MS365Typography.weights.semibold
      }}>
        {data.status}
      </div>
    </div>
  );
};

// Widget Templates Panel
const WidgetTemplatesPanel: React.FC<{
  templates: WidgetTemplate[];
  onAddWidget: (template: WidgetTemplate) => void;
  isOpen: boolean;
  onClose: () => void;
}> = ({ templates, onAddWidget, isOpen, onClose }) => {
  const categories = ['business', 'technical', 'marketplace', 'analytics'] as const;

  if (!isOpen) return null;

  return (
    <div style={{
      position: 'fixed',
      top: 0,
      right: isOpen ? 0 : '-400px',
      width: '400px',
      height: '100vh',
      backgroundColor: 'white',
      boxShadow: '-4px 0 8px rgba(0,0,0,0.1)',
      transition: 'right 0.3s ease',
      zIndex: 1000,
      padding: MS365Spacing[4],
      overflow: 'auto'
    }}>
      <div style={{
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        marginBottom: MS365Spacing[4]
      }}>
        <h3 style={{
          margin: 0,
          fontSize: MS365Typography.sizes.lg,
          fontWeight: MS365Typography.weights.bold
        }}>
          Widget Templates
        </h3>
        <MS365Button
          variant="ghost"
          size="sm"
          onClick={onClose}
        >
          ✕
        </MS365Button>
      </div>

      {categories.map(category => (
        <div key={category} style={{ marginBottom: MS365Spacing[4] }}>
          <h4 style={{
            margin: 0,
            marginBottom: MS365Spacing[2],
            fontSize: MS365Typography.sizes.base,
            fontWeight: MS365Typography.weights.semibold,
            textTransform: 'capitalize',
            color: MS365Colors.neutral[700]
          }}>
            {category}
          </h4>
          
          <div style={{ display: 'flex', flexDirection: 'column', gap: MS365Spacing[2] }}>
            {templates
              .filter(template => template.category === category)
              .map(template => (
                <div
                  key={template.id}
                  style={{
                    padding: MS365Spacing[3],
                    border: `1px solid ${MS365Colors.neutral[200]}`,
                    borderRadius: AdvancedMS365Theme.components.cards.radiuses.md,
                    cursor: 'pointer',
                    transition: 'border-color 0.2s ease',
                    backgroundColor: 'white'
                  }}
                  onClick={() => onAddWidget(template)}
                  onMouseEnter={(e) => {
                    e.currentTarget.style.borderColor = MS365Colors.primary.blue[500];
                  }}
                  onMouseLeave={(e) => {
                    e.currentTarget.style.borderColor = MS365Colors.neutral[200];
                  }}
                >
                  <div style={{
                    display: 'flex',
                    alignItems: 'flex-start',
                    gap: MS365Spacing[2]
                  }}>
                    <span style={{ fontSize: '1.5rem' }}>{template.icon}</span>
                    <div style={{ flex: 1 }}>
                      <div style={{
                        fontSize: MS365Typography.sizes.sm,
                        fontWeight: MS365Typography.weights.semibold,
                        marginBottom: MS365Spacing[1]
                      }}>
                        {template.name}
                      </div>
                      <div style={{
                        fontSize: MS365Typography.sizes.xs,
                        color: MS365Colors.neutral[600]
                      }}>
                        {template.description}
                      </div>
                    </div>
                  </div>
                </div>
              ))}
          </div>
        </div>
      ))}
    </div>
  );
};

// Main Interactive Dashboard Component
export const InteractiveDashboardWidgets: React.FC = () => {
  const [widgets, setWidgets] = useState<DashboardWidget[]>([]);
  const [widgetTemplates] = useState(() => createWidgetTemplates());
  const [dataService] = useState(() => new WidgetDataService());
  const [showTemplates, setShowTemplates] = useState(false);
  const [selectedLayout, setSelectedLayout] = useState<DashboardLayout | null>(null);

  // Drag and drop functionality
  const { dragState, handleMouseDown } = useDragAndDrop(widgets, (id, updates) => {
    setWidgets(prev => prev.map(w => w.id === id ? { ...w, ...updates } : w));
  });

  const addWidget = useCallback((template: WidgetTemplate) => {
    const newWidget: DashboardWidget = {
      id: `widget_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
      title: template.name,
      type: template.type,
      size: template.defaultSize,
      position: {
        x: Math.floor(Math.random() * 300),
        y: Math.floor(Math.random() * 200),
        w: 1,
        h: 1
      },
      config: { ...template.defaultConfig },
      data: null,
      isVisible: true,
      isLoading: true,
      isDragging: false,
      isResizing: false,
      permissions: {
        canMove: true,
        canResize: true,
        canDelete: true,
        canConfigure: true
      }
    };

    setWidgets(prev => [...prev, newWidget]);
    
    // Start loading data
    dataService.fetchWidgetData(newWidget).then(data => {
      setWidgets(prev => prev.map(w => 
        w.id === newWidget.id 
          ? { ...w, data, isLoading: false, lastUpdate: new Date() }
          : w
      ));
    });

    // Start auto-refresh
    dataService.startAutoRefresh(newWidget, (data) => {
      setWidgets(prev => prev.map(w => 
        w.id === newWidget.id 
          ? { ...w, data, lastUpdate: new Date() }
          : w
      ));
    });

    setShowTemplates(false);
  }, [dataService]);

  const deleteWidget = useCallback((widgetId: string) => {
    dataService.stopAutoRefresh(widgetId);
    setWidgets(prev => prev.filter(w => w.id !== widgetId));
  }, [dataService]);

  const clearAllWidgets = useCallback(() => {
    widgets.forEach(widget => dataService.stopAutoRefresh(widget.id));
    setWidgets([]);
  }, [widgets, dataService]);

  // Cleanup on unmount
  useEffect(() => {
    return () => {
      dataService.cleanup();
    };
  }, [dataService]);

  return (
    <div style={{ position: 'relative', height: '100vh', overflow: 'hidden' }}>
      {/* Toolbar */}
      <div style={{
        position: 'fixed',
        top: 0,
        left: 0,
        right: 0,
        height: '60px',
        backgroundColor: 'white',
        borderBottom: `1px solid ${MS365Colors.neutral[200]}`,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'space-between',
        padding: `0 ${MS365Spacing[4]}`,
        zIndex: 100
      }}>
        <div>
          <h2 style={{
            margin: 0,
            fontSize: MS365Typography.sizes.lg,
            fontWeight: MS365Typography.weights.bold
          }}>
            📊 Interactive Dashboard
          </h2>
        </div>

        <div style={{ display: 'flex', gap: MS365Spacing[2] }}>
          <MS365Button
            variant="primary"
            size="sm"
            onClick={() => setShowTemplates(true)}
          >
            + Add Widget
          </MS365Button>
          
          {widgets.length > 0 && (
            <MS365Button
              variant="secondary"
              size="sm"
              onClick={clearAllWidgets}
            >
              Clear All
            </MS365Button>
          )}
        </div>
      </div>

      {/* Dashboard Grid */}
      <div
        className="dashboard-grid"
        style={{
          position: 'relative',
          marginTop: '60px',
          width: '100%',
          height: 'calc(100vh - 60px)',
          backgroundColor: MS365Colors.neutral[50],
          backgroundImage: `
            linear-gradient(${MS365Colors.neutral[200]} 1px, transparent 1px),
            linear-gradient(90deg, ${MS365Colors.neutral[200]} 1px, transparent 1px)
          `,
          backgroundSize: '50px 50px',
          overflow: 'auto'
        }}
      >
        {widgets.length === 0 ? (
          <div style={{
            display: 'flex',
            flexDirection: 'column',
            justifyContent: 'center',
            alignItems: 'center',
            height: '100%',
            color: MS365Colors.neutral[600]
          }}>
            <div style={{ fontSize: '3rem', marginBottom: MS365Spacing[3] }}>📊</div>
            <div style={{ fontSize: MS365Typography.sizes.lg, marginBottom: MS365Spacing[2] }}>
              Create Your Custom Dashboard
            </div>
            <div style={{ fontSize: MS365Typography.sizes.sm, marginBottom: MS365Spacing[4] }}>
              Add widgets to start monitoring your business metrics
            </div>
            <MS365Button
              variant="primary"
              onClick={() => setShowTemplates(true)}
            >
              Get Started
            </MS365Button>
          </div>
        ) : (
          widgets.map(widget => (
            <WidgetContainer
              key={widget.id}
              widget={widget}
              onUpdate={(id, updates) => {
                setWidgets(prev => prev.map(w => w.id === id ? { ...w, ...updates } : w));
              }}
              onDelete={deleteWidget}
              onMouseDown={handleMouseDown}
            />
          ))
        )}
      </div>

      {/* Widget Templates Panel */}
      <WidgetTemplatesPanel
        templates={widgetTemplates}
        onAddWidget={addWidget}
        isOpen={showTemplates}
        onClose={() => setShowTemplates(false)}
      />

      {/* Overlay for drag state */}
      {dragState.isDragging && (
        <div style={{
          position: 'fixed',
          top: 0,
          left: 0,
          right: 0,
          bottom: 0,
          backgroundColor: 'rgba(0,0,0,0.1)',
          zIndex: 999,
          pointerEvents: 'none'
        }} />
      )}
    </div>
  );
};

export default InteractiveDashboardWidgets; 