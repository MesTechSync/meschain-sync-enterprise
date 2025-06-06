/**
 * Performance Monitoring System
 * Priority 4: Advanced Dashboard Analytics & Real-time Updates
 * 
 * @version 4.0.0
 * @author MesChain Sync Team - Cursor Team Priority 4
 */

import React, { useState, useEffect, useCallback } from 'react';
import { MS365Colors, MS365Typography, MS365Spacing, AdvancedMS365Theme } from '../../theme/microsoft365-advanced';
import { MS365Card } from '../Microsoft365/MS365Card';
import { MS365Button } from '../Microsoft365/MS365Button';
import { MS365Charts } from '../Microsoft365/MS365Charts';

// TypeScript Interfaces
export interface SystemHealthMetric {
  name: string;
  value: number;
  unit: string;
  status: 'excellent' | 'good' | 'warning' | 'critical';
  trend: 'up' | 'down' | 'stable';
  threshold: { warning: number; critical: number };
  history: { timestamp: number; value: number }[];
}

export interface APIEndpointMetric {
  endpoint: string;
  method: string;
  avgResponseTime: number;
  requestCount: number;
  errorRate: number;
  uptime: number;
  lastError?: Date;
  responseTimes: number[];
}

export interface AlertRule {
  id: string;
  name: string;
  metric: string;
  condition: 'greater_than' | 'less_than' | 'equals';
  threshold: number;
  severity: 'info' | 'warning' | 'critical';
  enabled: boolean;
  channels: ('email' | 'slack' | 'sms' | 'webhook')[];
}

export interface SystemAlert {
  id: string;
  type: 'performance' | 'error' | 'security' | 'business';
  severity: 'info' | 'warning' | 'critical';
  title: string;
  message: string;
  timestamp: Date;
  acknowledged: boolean;
  resolvedAt?: Date;
  metadata: Record<string, any>;
}

// Performance Monitoring Service
class PerformanceMonitoringService {
  private metrics: Map<string, SystemHealthMetric> = new Map();
  private alerts: SystemAlert[] = [];
  private alertRules: AlertRule[] = [];

  constructor() {
    this.initializeMetrics();
    this.initializeAlertRules();
  }

  private initializeMetrics(): void {
    const baseMetrics: Omit<SystemHealthMetric, 'value' | 'status' | 'history'>[] = [
      {
        name: 'CPU Usage',
        unit: '%',
        trend: 'stable',
        threshold: { warning: 70, critical: 85 }
      },
      {
        name: 'Memory Usage',
        unit: '%',
        trend: 'stable',
        threshold: { warning: 75, critical: 90 }
      },
      {
        name: 'Disk Usage',
        unit: '%',
        trend: 'up',
        threshold: { warning: 80, critical: 95 }
      },
      {
        name: 'Network I/O',
        unit: 'MB/s',
        trend: 'stable',
        threshold: { warning: 100, critical: 150 }
      },
      {
        name: 'Database Connections',
        unit: 'count',
        trend: 'stable',
        threshold: { warning: 80, critical: 95 }
      },
      {
        name: 'Queue Length',
        unit: 'items',
        trend: 'down',
        threshold: { warning: 100, critical: 200 }
      }
    ];

    baseMetrics.forEach(metric => {
      const currentValue = this.generateRealisticValue(metric.name);
      const fullMetric: SystemHealthMetric = {
        ...metric,
        value: currentValue,
        status: this.getMetricStatus(currentValue, metric.threshold),
        history: this.generateHistory(metric.name)
      };
      this.metrics.set(metric.name, fullMetric);
    });
  }

  private initializeAlertRules(): void {
    this.alertRules = [
      {
        id: 'cpu_high',
        name: 'High CPU Usage',
        metric: 'CPU Usage',
        condition: 'greater_than',
        threshold: 85,
        severity: 'critical',
        enabled: true,
        channels: ['email', 'slack']
      },
      {
        id: 'memory_high',
        name: 'High Memory Usage',
        metric: 'Memory Usage',
        condition: 'greater_than',
        threshold: 90,
        severity: 'critical',
        enabled: true,
        channels: ['email', 'slack']
      },
      {
        id: 'disk_full',
        name: 'Disk Space Critical',
        metric: 'Disk Usage',
        condition: 'greater_than',
        threshold: 95,
        severity: 'critical',
        enabled: true,
        channels: ['email', 'slack', 'sms']
      }
    ];
  }

  private generateRealisticValue(metricName: string): number {
    const baseValues: Record<string, number> = {
      'CPU Usage': 45,
      'Memory Usage': 62,
      'Disk Usage': 78,
      'Network I/O': 35,
      'Database Connections': 42,
      'Queue Length': 15
    };

    const base = baseValues[metricName] || 50;
    const variation = (Math.random() - 0.5) * 20; // ±10
    return Math.max(0, Math.min(100, base + variation));
  }

  private generateHistory(metricName: string): { timestamp: number; value: number }[] {
    const history = [];
    const now = Date.now();
    
    for (let i = 59; i >= 0; i--) {
      const timestamp = now - (i * 60000); // 1-minute intervals
      const value = this.generateRealisticValue(metricName);
      history.push({ timestamp, value });
    }
    
    return history;
  }

  private getMetricStatus(value: number, threshold: { warning: number; critical: number }): SystemHealthMetric['status'] {
    if (value >= threshold.critical) return 'critical';
    if (value >= threshold.warning) return 'warning';
    if (value <= 25) return 'excellent';
    return 'good';
  }

  public getMetrics(): Map<string, SystemHealthMetric> {
    return this.metrics;
  }

  public updateMetrics(): void {
    this.metrics.forEach((metric, name) => {
      const newValue = this.generateRealisticValue(name);
      const newStatus = this.getMetricStatus(newValue, metric.threshold);
      
      // Add to history
      const newHistoryPoint = { timestamp: Date.now(), value: newValue };
      const updatedHistory = [...metric.history.slice(-59), newHistoryPoint];
      
      this.metrics.set(name, {
        ...metric,
        value: newValue,
        status: newStatus,
        history: updatedHistory
      });

      // Check alert rules
      this.checkAlertRules(name, newValue);
    });
  }

  private checkAlertRules(metricName: string, value: number): void {
    const relevantRules = this.alertRules.filter(rule => 
      rule.enabled && rule.metric === metricName
    );

    relevantRules.forEach(rule => {
      const triggered = this.evaluateCondition(value, rule.condition, rule.threshold);
      
      if (triggered) {
        this.triggerAlert({
          id: `alert_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
          type: 'performance',
          severity: rule.severity,
          title: rule.name,
          message: `${metricName} is ${value.toFixed(1)}${this.metrics.get(metricName)?.unit || ''}, exceeding threshold of ${rule.threshold}`,
          timestamp: new Date(),
          acknowledged: false,
          metadata: {
            metricName,
            value,
            threshold: rule.threshold,
            rule: rule.id
          }
        });
      }
    });
  }

  private evaluateCondition(value: number, condition: AlertRule['condition'], threshold: number): boolean {
    switch (condition) {
      case 'greater_than': return value > threshold;
      case 'less_than': return value < threshold;
      case 'equals': return Math.abs(value - threshold) < 0.01;
      default: return false;
    }
  }

  private triggerAlert(alert: SystemAlert): void {
    // Prevent duplicate alerts within 5 minutes
    const recentSimilar = this.alerts.find(a => 
      a.title === alert.title && 
      !a.acknowledged && 
      (Date.now() - a.timestamp.getTime()) < 300000
    );

    if (!recentSimilar) {
      this.alerts.unshift(alert);
      // Keep only last 100 alerts
      if (this.alerts.length > 100) {
        this.alerts = this.alerts.slice(0, 100);
      }
    }
  }

  public getAlerts(): SystemAlert[] {
    return this.alerts;
  }

  public acknowledgeAlert(alertId: string): void {
    const alert = this.alerts.find(a => a.id === alertId);
    if (alert) {
      alert.acknowledged = true;
    }
  }

  public generateAPIMetrics(): APIEndpointMetric[] {
    const endpoints = [
      { endpoint: '/api/orders', method: 'GET' },
      { endpoint: '/api/products', method: 'GET' },
      { endpoint: '/api/sync', method: 'POST' },
      { endpoint: '/api/marketplace/trendyol', method: 'GET' },
      { endpoint: '/api/marketplace/amazon', method: 'GET' },
      { endpoint: '/api/analytics', method: 'GET' }
    ];

    return endpoints.map(ep => ({
      ...ep,
      avgResponseTime: 50 + Math.random() * 200,
      requestCount: Math.floor(Math.random() * 1000) + 100,
      errorRate: Math.random() * 0.05, // 0-5%
      uptime: 95 + Math.random() * 5, // 95-100%
      responseTimes: Array.from({ length: 20 }, () => 30 + Math.random() * 300)
    }));
  }
}

// System Health Overview Component
const SystemHealthOverview: React.FC<{ metrics: Map<string, SystemHealthMetric> }> = ({ metrics }) => {
  const getStatusColor = (status: SystemHealthMetric['status']) => {
    switch (status) {
      case 'excellent': return MS365Colors.primary.green[600];
      case 'good': return MS365Colors.primary.blue[600];
      case 'warning': return '#f59e0b';
      case 'critical': return MS365Colors.primary.red[600];
      default: return MS365Colors.neutral[600];
    }
  };

  const getStatusIcon = (status: SystemHealthMetric['status']) => {
    switch (status) {
      case 'excellent': return '🟢';
      case 'good': return '🔵';
      case 'warning': return '🟡';
      case 'critical': return '🔴';
      default: return '⚪';
    }
  };

  const getTrendIcon = (trend: SystemHealthMetric['trend']) => {
    switch (trend) {
      case 'up': return '📈';
      case 'down': return '📉';
      case 'stable': return '➡️';
      default: return '❓';
    }
  };

  return (
    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))', gap: MS365Spacing[4] }}>
      {Array.from(metrics.values()).map(metric => (
        <MS365Card
          key={metric.name}
          title={metric.name}
          variant="elevated"
          content={
            <div style={{ display: 'flex', flexDirection: 'column', gap: MS365Spacing[3] }}>
              {/* Current Value */}
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <div>
                  <div style={{
                    fontSize: '2rem',
                    fontWeight: MS365Typography.weights.bold,
                    color: getStatusColor(metric.status)
                  }}>
                    {metric.value.toFixed(1)}{metric.unit}
                  </div>
                  <div style={{ 
                    fontSize: MS365Typography.sizes.sm,
                    color: getStatusColor(metric.status),
                    display: 'flex',
                    alignItems: 'center',
                    gap: MS365Spacing[1]
                  }}>
                    <span>{getStatusIcon(metric.status)}</span>
                    {metric.status.toUpperCase()}
                  </div>
                </div>
                <div style={{ textAlign: 'center' }}>
                  <div style={{ fontSize: '1.5rem' }}>{getTrendIcon(metric.trend)}</div>
                  <div style={{ 
                    fontSize: MS365Typography.sizes.xs,
                    color: MS365Colors.neutral[600],
                    textTransform: 'capitalize'
                  }}>
                    {metric.trend}
                  </div>
                </div>
              </div>

              {/* Threshold Indicators */}
              <div style={{ position: 'relative', height: '8px', backgroundColor: MS365Colors.neutral[200], borderRadius: '4px' }}>
                <div 
                  style={{
                    position: 'absolute',
                    left: `${metric.threshold.warning}%`,
                    top: '-2px',
                    width: '2px',
                    height: '12px',
                    backgroundColor: '#f59e0b'
                  }}
                />
                <div 
                  style={{
                    position: 'absolute',
                    left: `${metric.threshold.critical}%`,
                    top: '-2px',
                    width: '2px',
                    height: '12px',
                    backgroundColor: MS365Colors.primary.red[600]
                  }}
                />
                <div style={{
                  width: `${Math.min(100, metric.value)}%`,
                  height: '100%',
                  backgroundColor: getStatusColor(metric.status),
                  borderRadius: '4px',
                  transition: 'width 0.3s ease'
                }} />
              </div>

              {/* Mini Chart */}
              <div style={{ height: '60px' }}>
                <MS365Charts
                  type="line"
                  data={[{
                    name: metric.name,
                    data: metric.history.map(h => h.value)
                  }]}
                  categories={metric.history.map(h => new Date(h.timestamp).toLocaleTimeString())}
                  height={60}
                  colors={[getStatusColor(metric.status)]}
                  showGrid={false}
                  showLegend={false}
                />
              </div>
            </div>
          }
        />
      ))}
    </div>
  );
};

// API Performance Panel Component
const APIPerformancePanel: React.FC<{ apiMetrics: APIEndpointMetric[] }> = ({ apiMetrics }) => {
  const getStatusColor = (errorRate: number, uptime: number) => {
    if (errorRate > 0.05 || uptime < 95) return MS365Colors.primary.red[600];
    if (errorRate > 0.02 || uptime < 98) return '#f59e0b';
    return MS365Colors.primary.green[600];
  };

  return (
    <MS365Card
      title="🌐 API Performance Monitor"
      variant="elevated"
      content={
        <div style={{ display: 'flex', flexDirection: 'column', gap: MS365Spacing[3] }}>
          {apiMetrics.map((api, index) => (
            <div key={index} style={{
              padding: MS365Spacing[3],
              border: `1px solid ${MS365Colors.neutral[200]}`,
              borderRadius: AdvancedMS365Theme.components.cards.radiuses.md,
              backgroundColor: MS365Colors.neutral[50]
            }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: MS365Spacing[2] }}>
                <div>
                  <div style={{
                    fontSize: MS365Typography.sizes.base,
                    fontWeight: MS365Typography.weights.semibold,
                    color: MS365Colors.neutral[900]
                  }}>
                    {api.method} {api.endpoint}
                  </div>
                  <div style={{
                    fontSize: MS365Typography.sizes.xs,
                    color: MS365Colors.neutral[600]
                  }}>
                    {api.requestCount.toLocaleString()} requests today
                  </div>
                </div>
                <div style={{
                  width: '12px',
                  height: '12px',
                  borderRadius: '50%',
                  backgroundColor: getStatusColor(api.errorRate, api.uptime)
                }} />
              </div>

              <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(120px, 1fr))', gap: MS365Spacing[2] }}>
                <div>
                  <div style={{ fontSize: MS365Typography.sizes.xs, color: MS365Colors.neutral[600] }}>Avg Response</div>
                  <div style={{ 
                    fontSize: MS365Typography.sizes.sm,
                    fontWeight: MS365Typography.weights.semibold,
                    color: api.avgResponseTime > 500 ? MS365Colors.primary.red[600] : 
                          api.avgResponseTime > 200 ? '#f59e0b' : 
                          MS365Colors.primary.green[600]
                  }}>
                    {api.avgResponseTime.toFixed(0)}ms
                  </div>
                </div>
                
                <div>
                  <div style={{ fontSize: MS365Typography.sizes.xs, color: MS365Colors.neutral[600] }}>Error Rate</div>
                  <div style={{ 
                    fontSize: MS365Typography.sizes.sm,
                    fontWeight: MS365Typography.weights.semibold,
                    color: getStatusColor(api.errorRate, 100)
                  }}>
                    {(api.errorRate * 100).toFixed(2)}%
                  </div>
                </div>
                
                <div>
                  <div style={{ fontSize: MS365Typography.sizes.xs, color: MS365Colors.neutral[600] }}>Uptime</div>
                  <div style={{ 
                    fontSize: MS365Typography.sizes.sm,
                    fontWeight: MS365Typography.weights.semibold,
                    color: getStatusColor(0, api.uptime)
                  }}>
                    {api.uptime.toFixed(1)}%
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      }
    />
  );
};

// Alerts Panel Component
const AlertsPanel: React.FC<{ 
  alerts: SystemAlert[];
  onAcknowledge: (alertId: string) => void;
}> = ({ alerts, onAcknowledge }) => {
  const getSeverityColor = (severity: SystemAlert['severity']) => {
    switch (severity) {
      case 'critical': return MS365Colors.primary.red[600];
      case 'warning': return '#f59e0b';
      case 'info': return MS365Colors.primary.blue[600];
      default: return MS365Colors.neutral[600];
    }
  };

  const getSeverityIcon = (severity: SystemAlert['severity']) => {
    switch (severity) {
      case 'critical': return '🚨';
      case 'warning': return '⚠️';
      case 'info': return 'ℹ️';
      default: return '📢';
    }
  };

  const unacknowledgedAlerts = alerts.filter(alert => !alert.acknowledged);

  return (
    <MS365Card
      title={`🔔 System Alerts (${unacknowledgedAlerts.length} unread)`}
      variant="elevated"
      content={
        <div style={{ display: 'flex', flexDirection: 'column', gap: MS365Spacing[2], maxHeight: '400px', overflow: 'auto' }}>
          {alerts.length === 0 ? (
            <div style={{
              textAlign: 'center',
              padding: MS365Spacing[4],
              color: MS365Colors.neutral[600]
            }}>
              ✅ No alerts. System is running smoothly!
            </div>
          ) : (
            alerts.map(alert => (
              <div
                key={alert.id}
                style={{
                  padding: MS365Spacing[3],
                  border: `2px solid ${getSeverityColor(alert.severity)}`,
                  borderRadius: AdvancedMS365Theme.components.cards.radiuses.md,
                  backgroundColor: alert.acknowledged ? MS365Colors.neutral[50] : `${getSeverityColor(alert.severity)}10`,
                  opacity: alert.acknowledged ? 0.7 : 1
                }}
              >
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: MS365Spacing[2] }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: MS365Spacing[2] }}>
                    <span style={{ fontSize: '1.2rem' }}>{getSeverityIcon(alert.severity)}</span>
                    <div>
                      <div style={{
                        fontSize: MS365Typography.sizes.sm,
                        fontWeight: MS365Typography.weights.semibold,
                        color: MS365Colors.neutral[900]
                      }}>
                        {alert.title}
                      </div>
                      <div style={{
                        fontSize: MS365Typography.sizes.xs,
                        color: getSeverityColor(alert.severity),
                        textTransform: 'uppercase'
                      }}>
                        {alert.severity} • {alert.type}
                      </div>
                    </div>
                  </div>
                  
                  {!alert.acknowledged && (
                    <MS365Button
                      variant="ghost"
                      size="sm"
                      onClick={() => onAcknowledge(alert.id)}
                    >
                      ✓ Acknowledge
                    </MS365Button>
                  )}
                </div>

                <div style={{
                  fontSize: MS365Typography.sizes.sm,
                  color: MS365Colors.neutral[700],
                  marginBottom: MS365Spacing[2]
                }}>
                  {alert.message}
                </div>

                <div style={{
                  fontSize: MS365Typography.sizes.xs,
                  color: MS365Colors.neutral[500]
                }}>
                  {alert.timestamp.toLocaleString()}
                  {alert.acknowledged && ' • Acknowledged'}
                </div>
              </div>
            ))
          )}
        </div>
      }
    />
  );
};

// Main Performance Monitoring Component
export const PerformanceMonitoringSystem: React.FC = () => {
  const [monitoringService] = useState(() => new PerformanceMonitoringService());
  const [metrics, setMetrics] = useState<Map<string, SystemHealthMetric>>(new Map());
  const [apiMetrics, setApiMetrics] = useState<APIEndpointMetric[]>([]);
  const [alerts, setAlerts] = useState<SystemAlert[]>([]);
  const [isMonitoring, setIsMonitoring] = useState(true);

  const updateData = useCallback(() => {
    monitoringService.updateMetrics();
    setMetrics(new Map(monitoringService.getMetrics()));
    setApiMetrics(monitoringService.generateAPIMetrics());
    setAlerts([...monitoringService.getAlerts()]);
  }, [monitoringService]);

  const handleAcknowledgeAlert = useCallback((alertId: string) => {
    monitoringService.acknowledgeAlert(alertId);
    setAlerts([...monitoringService.getAlerts()]);
  }, [monitoringService]);

  useEffect(() => {
    updateData();
    
    let interval: NodeJS.Timeout;
    if (isMonitoring) {
      interval = setInterval(updateData, 5000); // Update every 5 seconds
    }

    return () => {
      if (interval) clearInterval(interval);
    };
  }, [isMonitoring, updateData]);

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: MS365Spacing[6], padding: MS365Spacing[4] }}>
      {/* Header */}
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <div>
          <h1 style={{
            margin: 0,
            fontSize: MS365Typography.sizes['2xl'],
            fontWeight: MS365Typography.weights.bold,
            color: MS365Colors.neutral[900]
          }}>
            📊 Performance Monitoring
          </h1>
          <p style={{
            margin: 0,
            marginTop: MS365Spacing[1],
            color: MS365Colors.neutral[600],
            fontSize: MS365Typography.sizes.sm
          }}>
            Real-time system health and performance analytics
          </p>
        </div>

        <div style={{ display: 'flex', alignItems: 'center', gap: MS365Spacing[3] }}>
          <div style={{
            display: 'flex',
            alignItems: 'center',
            gap: MS365Spacing[1],
            fontSize: MS365Typography.sizes.sm,
            color: isMonitoring ? MS365Colors.primary.green[600] : MS365Colors.neutral[600]
          }}>
            <div style={{
              width: '8px',
              height: '8px',
              borderRadius: '50%',
              backgroundColor: isMonitoring ? MS365Colors.primary.green[500] : MS365Colors.neutral[400]
            }} />
            {isMonitoring ? 'Live Monitoring' : 'Monitoring Paused'}
          </div>

          <MS365Button
            variant={isMonitoring ? "secondary" : "primary"}
            onClick={() => setIsMonitoring(!isMonitoring)}
          >
            {isMonitoring ? '⏸️ Pause' : '▶️ Resume'}
          </MS365Button>
        </div>
      </div>

      {/* System Health Overview */}
      <div>
        <h2 style={{
          margin: 0,
          marginBottom: MS365Spacing[4],
          fontSize: MS365Typography.sizes.lg,
          fontWeight: MS365Typography.weights.semibold,
          color: MS365Colors.neutral[800]
        }}>
          🖥️ System Health Overview
        </h2>
        <SystemHealthOverview metrics={metrics} />
      </div>

      {/* API Performance and Alerts */}
      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: MS365Spacing[4] }}>
        <APIPerformancePanel apiMetrics={apiMetrics} />
        <AlertsPanel alerts={alerts} onAcknowledge={handleAcknowledgeAlert} />
      </div>
    </div>
  );
};

export default PerformanceMonitoringSystem; 