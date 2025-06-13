/**
 * MS365Charts - Chart Components with Chart.js Integration
 * Microsoft 365 design system compliant chart components
 * 
 * @version 2.0.0
 * @author MesChain Sync Team
 */

import React, { useRef, useEffect, useState } from 'react';
import { MS365Colors, MS365Typography, MS365Spacing, AdvancedMS365Theme } from '../../theme/microsoft365-advanced';

// Chart.js types (simplified for demo)
interface ChartData {
  labels: string[];
  datasets: Array<{
    label: string;
    data: number[];
    backgroundColor?: string | string[];
    borderColor?: string | string[];
    borderWidth?: number;
    fill?: boolean;
    tension?: number;
  }>;
}

interface ChartOptions {
  responsive?: boolean;
  maintainAspectRatio?: boolean;
  plugins?: {
    legend?: {
      display?: boolean;
      position?: 'top' | 'bottom' | 'left' | 'right';
    };
    title?: {
      display?: boolean;
      text?: string;
    };
  };
  scales?: {
    x?: {
      display?: boolean;
      grid?: {
        display?: boolean;
      };
    };
    y?: {
      display?: boolean;
      grid?: {
        display?: boolean;
      };
    };
  };
}

// Base Chart Props
export interface BaseChartProps {
  data: ChartData;
  options?: ChartOptions;
  width?: number;
  height?: number;
  title?: string;
  subtitle?: string;
  loading?: boolean;
  error?: string;
  className?: string;
  style?: React.CSSProperties;
  theme?: 'light' | 'dark';
}

// Chart Color Palettes
const MS365ChartColors = {
  primary: [
    MS365Colors.primary.blue[500],
    MS365Colors.primary.blue[400],
    MS365Colors.primary.blue[600],
    MS365Colors.primary.blue[300],
    MS365Colors.primary.blue[700]
  ],
  success: [
    MS365Colors.primary.green[500],
    MS365Colors.primary.green[400],
    MS365Colors.primary.green[600],
    MS365Colors.primary.green[300],
    MS365Colors.primary.green[700]
  ],
  warning: [
    '#f59e0b',
    '#fbbf24',
    '#d97706',
    '#fcd34d',
    '#b45309'
  ],
  error: [
    MS365Colors.primary.red[500],
    MS365Colors.primary.red[400],
    MS365Colors.primary.red[600],
    MS365Colors.primary.red[300],
    MS365Colors.primary.red[700]
  ],
  neutral: [
    MS365Colors.neutral[600],
    MS365Colors.neutral[500],
    MS365Colors.neutral[700],
    MS365Colors.neutral[400],
    MS365Colors.neutral[800]
  ],
  mixed: [
    MS365Colors.primary.blue[500],
    MS365Colors.primary.green[500],
    '#f59e0b',
    MS365Colors.primary.red[500],
    '#8b5cf6',
    '#06b6d4',
    '#84cc16',
    '#f97316'
  ]
};

// Default Chart Options
const getDefaultOptions = (theme: 'light' | 'dark' = 'light'): ChartOptions => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
      position: 'top'
    }
  },
  scales: {
    x: {
      display: true,
      grid: {
        display: true
      }
    },
    y: {
      display: true,
      grid: {
        display: true
      }
    }
  }
});

// Mock Chart.js implementation (for demo purposes)
const MockChart = ({ 
  type, 
  data, 
  options, 
  width = 400, 
  height = 300 
}: { 
  type: string; 
  data: ChartData; 
  options: ChartOptions; 
  width?: number; 
  height?: number; 
}) => {
  const canvasRef = useRef<HTMLCanvasElement>(null);

  useEffect(() => {
    if (!canvasRef.current) return;

    const canvas = canvasRef.current;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    // Clear canvas
    ctx.clearRect(0, 0, width, height);

    // Simple mock visualization based on chart type
    if (type === 'bar') {
      drawBarChart(ctx, data, width, height);
    } else if (type === 'line') {
      drawLineChart(ctx, data, width, height);
    } else if (type === 'pie' || type === 'doughnut') {
      drawPieChart(ctx, data, width, height, type === 'doughnut');
    }
  }, [type, data, width, height]);

  // Simple bar chart drawing
  const drawBarChart = (ctx: CanvasRenderingContext2D, data: ChartData, w: number, h: number) => {
    const barWidth = (w - 80) / data.labels.length;
    const maxValue = Math.max(...data.datasets[0].data);
    
    data.datasets.forEach((dataset, datasetIndex) => {
      dataset.data.forEach((value, index) => {
        const barHeight = (value / maxValue) * (h - 80);
        const x = 40 + index * barWidth + datasetIndex * (barWidth / data.datasets.length);
        const y = h - 40 - barHeight;
        
        ctx.fillStyle = Array.isArray(dataset.backgroundColor) 
          ? dataset.backgroundColor[index] || MS365ChartColors.primary[index % MS365ChartColors.primary.length]
          : dataset.backgroundColor || MS365ChartColors.primary[datasetIndex % MS365ChartColors.primary.length];
        
        ctx.fillRect(x, y, barWidth / data.datasets.length - 2, barHeight);
      });
    });

    // Draw axes
    ctx.strokeStyle = MS365Colors.neutral[300];
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(40, h - 40);
    ctx.lineTo(w - 20, h - 40);
    ctx.moveTo(40, 20);
    ctx.lineTo(40, h - 40);
    ctx.stroke();

    // Draw labels
    ctx.fillStyle = MS365Colors.neutral[700];
    ctx.font = '12px ' + MS365Typography.fonts.system;
    ctx.textAlign = 'center';
    data.labels.forEach((label, index) => {
      ctx.fillText(label, 40 + index * barWidth + barWidth / 2, h - 20);
    });
  };

  // Simple line chart drawing
  const drawLineChart = (ctx: CanvasRenderingContext2D, data: ChartData, w: number, h: number) => {
    const stepX = (w - 80) / (data.labels.length - 1);
    const maxValue = Math.max(...data.datasets.flatMap(d => d.data));
    
    data.datasets.forEach((dataset, datasetIndex) => {
      ctx.strokeStyle = Array.isArray(dataset.borderColor) 
        ? dataset.borderColor[0] 
        : dataset.borderColor || MS365ChartColors.primary[datasetIndex % MS365ChartColors.primary.length];
      ctx.lineWidth = dataset.borderWidth || 2;
      ctx.beginPath();
      
      dataset.data.forEach((value, index) => {
        const x = 40 + index * stepX;
        const y = h - 40 - (value / maxValue) * (h - 80);
        
        if (index === 0) {
          ctx.moveTo(x, y);
        } else {
          ctx.lineTo(x, y);
        }
      });
      
      ctx.stroke();

      // Draw points
      dataset.data.forEach((value, index) => {
        const x = 40 + index * stepX;
        const y = h - 40 - (value / maxValue) * (h - 80);
        
        ctx.fillStyle = ctx.strokeStyle;
        ctx.beginPath();
        ctx.arc(x, y, 3, 0, Math.PI * 2);
        ctx.fill();
      });
    });

    // Draw grid
    ctx.strokeStyle = MS365Colors.neutral[200];
    ctx.lineWidth = 1;
    for (let i = 0; i <= 5; i++) {
      const y = 40 + (i * (h - 80)) / 5;
      ctx.beginPath();
      ctx.moveTo(40, y);
      ctx.lineTo(w - 20, y);
      ctx.stroke();
    }

    // Draw axes
    ctx.strokeStyle = MS365Colors.neutral[300];
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(40, h - 40);
    ctx.lineTo(w - 20, h - 40);
    ctx.moveTo(40, 20);
    ctx.lineTo(40, h - 40);
    ctx.stroke();
  };

  // Simple pie chart drawing
  const drawPieChart = (ctx: CanvasRenderingContext2D, data: ChartData, w: number, h: number, isDoughnut: boolean = false) => {
    const centerX = w / 2;
    const centerY = h / 2;
    const radius = Math.min(w, h) / 2 - 40;
    const innerRadius = isDoughnut ? radius * 0.5 : 0;
    
    const total = data.datasets[0].data.reduce((sum, value) => sum + value, 0);
    let currentAngle = -Math.PI / 2;

    data.datasets[0].data.forEach((value, index) => {
      const sliceAngle = (value / total) * Math.PI * 2;
      const color = Array.isArray(data.datasets[0].backgroundColor) 
        ? data.datasets[0].backgroundColor[index] 
        : MS365ChartColors.mixed[index % MS365ChartColors.mixed.length];

      // Draw slice
      ctx.fillStyle = color;
      ctx.beginPath();
      ctx.arc(centerX, centerY, radius, currentAngle, currentAngle + sliceAngle);
      if (isDoughnut) {
        ctx.arc(centerX, centerY, innerRadius, currentAngle + sliceAngle, currentAngle, true);
      } else {
        ctx.lineTo(centerX, centerY);
      }
      ctx.closePath();
      ctx.fill();

      // Draw border
      ctx.strokeStyle = MS365Colors.background.primary;
      ctx.lineWidth = 2;
      ctx.stroke();

      currentAngle += sliceAngle;
    });
  };

  return (
    <canvas
      ref={canvasRef}
      width={width}
      height={height}
      style={{
        width: '100%',
        height: '100%',
        maxWidth: `${width}px`,
        maxHeight: `${height}px`
      }}
    />
  );
};

// Chart Container Component
const ChartContainer: React.FC<BaseChartProps & { children: React.ReactNode }> = ({
  title,
  subtitle,
  loading,
  error,
  className,
  style,
  children
}) => {
  const containerStyles: React.CSSProperties = {
    backgroundColor: MS365Colors.background.primary,
    border: `1px solid ${MS365Colors.neutral[200]}`,
    borderRadius: AdvancedMS365Theme.components.cards.radiuses.md,
    padding: MS365Spacing[6],
    fontFamily: MS365Typography.fonts.system,
    position: 'relative',
    overflow: 'hidden',
    ...style
  };

  const titleStyles: React.CSSProperties = {
    fontSize: MS365Typography.sizes.lg,
    fontWeight: MS365Typography.weights.semibold,
    color: MS365Colors.neutral[900],
    margin: 0,
    marginBottom: subtitle ? MS365Spacing[1] : MS365Spacing[4]
  };

  const subtitleStyles: React.CSSProperties = {
    fontSize: MS365Typography.sizes.sm,
    color: MS365Colors.neutral[600],
    margin: 0,
    marginBottom: MS365Spacing[4]
  };

  if (loading) {
    return (
      <div className={`ms365-chart-container ${className || ''}`} style={containerStyles}>
        {title && <h3 style={titleStyles}>{title}</h3>}
        {subtitle && <p style={subtitleStyles}>{subtitle}</p>}
        <div
          style={{
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            height: '300px',
            color: MS365Colors.neutral[500]
          }}
        >
          <div
            style={{
              width: '32px',
              height: '32px',
              border: `3px solid ${MS365Colors.neutral[300]}`,
              borderTop: `3px solid ${MS365Colors.primary.blue[500]}`,
              borderRadius: '50%',
              animation: 'spin 1s linear infinite',
              marginRight: MS365Spacing[3]
            }}
          />
          Loading chart...
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className={`ms365-chart-container ${className || ''}`} style={containerStyles}>
        {title && <h3 style={titleStyles}>{title}</h3>}
        {subtitle && <p style={subtitleStyles}>{subtitle}</p>}
        <div
          style={{
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            height: '300px',
            color: MS365Colors.primary.red[600],
            textAlign: 'center'
          }}
        >
          <div>
            <div style={{ fontSize: '24px', marginBottom: MS365Spacing[2] }}>⚠️</div>
            <div>Error loading chart: {error}</div>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className={`ms365-chart-container ${className || ''}`} style={containerStyles}>
      {title && <h3 style={titleStyles}>{title}</h3>}
      {subtitle && <p style={subtitleStyles}>{subtitle}</p>}
      <div style={{ position: 'relative', height: '300px' }}>
        {children}
      </div>
    </div>
  );
};

// Bar Chart Component
export const MS365BarChart: React.FC<BaseChartProps> = (props) => {
  const { data, options = {}, width = 400, height = 300, theme = 'light' } = props;
  const chartOptions = { ...getDefaultOptions(theme), ...options };

  return (
    <ChartContainer {...props}>
      <MockChart
        type="bar"
        data={data}
        options={chartOptions}
        width={width}
        height={height}
      />
    </ChartContainer>
  );
};

// Line Chart Component
export const MS365LineChart: React.FC<BaseChartProps> = (props) => {
  const { data, options = {}, width = 400, height = 300, theme = 'light' } = props;
  const chartOptions = { ...getDefaultOptions(theme), ...options };

  return (
    <ChartContainer {...props}>
      <MockChart
        type="line"
        data={data}
        options={chartOptions}
        width={width}
        height={height}
      />
    </ChartContainer>
  );
};

// Pie Chart Component
export const MS365PieChart: React.FC<BaseChartProps> = (props) => {
  const { data, options = {}, width = 400, height = 300, theme = 'light' } = props;
  const chartOptions = { ...getDefaultOptions(theme), ...options };

  return (
    <ChartContainer {...props}>
      <MockChart
        type="pie"
        data={data}
        options={chartOptions}
        width={width}
        height={height}
      />
    </ChartContainer>
  );
};

// Doughnut Chart Component
export const MS365DoughnutChart: React.FC<BaseChartProps> = (props) => {
  const { data, options = {}, width = 400, height = 300, theme = 'light' } = props;
  const chartOptions = { ...getDefaultOptions(theme), ...options };

  return (
    <ChartContainer {...props}>
      <MockChart
        type="doughnut"
        data={data}
        options={chartOptions}
        width={width}
        height={height}
      />
    </ChartContainer>
  );
};

// Dashboard Stats Component
export interface StatsCardProps {
  title: string;
  value: string | number;
  change?: {
    value: number;
    type: 'increase' | 'decrease';
    period?: string;
  };
  icon?: React.ReactNode;
  color?: 'primary' | 'success' | 'warning' | 'error';
  loading?: boolean;
  className?: string;
  style?: React.CSSProperties;
}

export const MS365StatsCard: React.FC<StatsCardProps> = ({
  title,
  value,
  change,
  icon,
  color = 'primary',
  loading = false,
  className,
  style
}) => {
  const getColorScheme = () => {
    switch (color) {
      case 'success':
        return {
          background: MS365Colors.primary.green[50],
          border: MS365Colors.primary.green[200],
          iconColor: MS365Colors.primary.green[600],
          valueColor: MS365Colors.primary.green[700]
        };
      case 'warning':
        return {
          background: '#fef3c7',
          border: '#fbbf24',
          iconColor: '#d97706',
          valueColor: '#92400e'
        };
      case 'error':
        return {
          background: MS365Colors.primary.red[50],
          border: MS365Colors.primary.red[200],
          iconColor: MS365Colors.primary.red[600],
          valueColor: MS365Colors.primary.red[700]
        };
      default:
        return {
          background: MS365Colors.primary.blue[50],
          border: MS365Colors.primary.blue[200],
          iconColor: MS365Colors.primary.blue[600],
          valueColor: MS365Colors.primary.blue[700]
        };
    }
  };

  const colorScheme = getColorScheme();

  const cardStyles: React.CSSProperties = {
    backgroundColor: colorScheme.background,
    border: `1px solid ${colorScheme.border}`,
    borderRadius: AdvancedMS365Theme.components.cards.radiuses.md,
    padding: MS365Spacing[6],
    fontFamily: MS365Typography.fonts.system,
    position: 'relative',
    overflow: 'hidden',
    ...style
  };

  const headerStyles: React.CSSProperties = {
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: MS365Spacing[4]
  };

  const titleStyles: React.CSSProperties = {
    fontSize: MS365Typography.sizes.sm,
    color: MS365Colors.neutral[600],
    margin: 0,
    fontWeight: MS365Typography.weights.medium
  };

  const valueStyles: React.CSSProperties = {
    fontSize: MS365Typography.sizes['3xl'],
    fontWeight: MS365Typography.weights.bold,
    color: colorScheme.valueColor,
    margin: 0,
    lineHeight: 1.2
  };

  const changeStyles: React.CSSProperties = {
    fontSize: MS365Typography.sizes.sm,
    color: change?.type === 'increase' ? MS365Colors.primary.green[600] : MS365Colors.primary.red[600],
    marginTop: MS365Spacing[2],
    display: 'flex',
    alignItems: 'center',
    gap: MS365Spacing[1]
  };

  if (loading) {
    return (
      <div className={`ms365-stats-card ${className || ''}`} style={cardStyles}>
        <div style={headerStyles}>
          <div
            style={{
              width: '60%',
              height: '16px',
              backgroundColor: MS365Colors.neutral[200],
              borderRadius: '4px',
              animation: 'pulse 1.5s ease-in-out infinite'
            }}
          />
          {icon && (
            <div style={{ color: colorScheme.iconColor, fontSize: '24px' }}>
              {icon}
            </div>
          )}
        </div>
        <div
          style={{
            width: '80%',
            height: '32px',
            backgroundColor: MS365Colors.neutral[200],
            borderRadius: '4px',
            marginBottom: MS365Spacing[2],
            animation: 'pulse 1.5s ease-in-out infinite'
          }}
        />
        <div
          style={{
            width: '40%',
            height: '14px',
            backgroundColor: MS365Colors.neutral[200],
            borderRadius: '4px',
            animation: 'pulse 1.5s ease-in-out infinite'
          }}
        />
      </div>
    );
  }

  return (
    <div className={`ms365-stats-card ${className || ''}`} style={cardStyles}>
      <div style={headerStyles}>
        <h4 style={titleStyles}>{title}</h4>
        {icon && (
          <div style={{ color: colorScheme.iconColor, fontSize: '24px' }}>
            {icon}
          </div>
        )}
      </div>
      
      <div style={valueStyles}>
        {typeof value === 'number' ? value.toLocaleString() : value}
      </div>

      {change && (
        <div style={changeStyles}>
          <span>{change.type === 'increase' ? '↗' : '↘'}</span>
          <span>
            {change.value > 0 ? '+' : ''}{change.value}%
            {change.period && ` ${change.period}`}
          </span>
        </div>
      )}
    </div>
  );
};

// Export all chart colors for external use
export { MS365ChartColors };

// Export all chart components individually
export const MS365Charts = {
  BarChart: MS365BarChart,
  LineChart: MS365LineChart,
  PieChart: MS365PieChart,
  DoughnutChart: MS365DoughnutChart,
  StatsCard: MS365StatsCard
};

// Default export
export default MS365Charts; 