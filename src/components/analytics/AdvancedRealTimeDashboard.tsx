/**
 * Advanced Real-time Dashboard Component
 * Priority 4: Advanced Dashboard Analytics & Real-time Updates
 * 
 * @version 4.0.0
 * @author MesChain Sync Team - Cursor Team Priority 4
 */

import React, { useState, useEffect, useCallback, useRef, useMemo } from 'react';
import { MS365Colors, MS365Typography, MS365Spacing, AdvancedMS365Theme } from '../../theme/microsoft365-advanced';
import { MS365Card } from '../Microsoft365/MS365Card';
import { MS365Button } from '../Microsoft365/MS365Button';
import { MS365Charts } from '../Microsoft365/MS365Charts';
import { MS365DataGrid } from '../Microsoft365/MS365DataGrid';

// TypeScript Interfaces
export interface RealTimeMetrics {
  timestamp: number;
  activeUsers: number;
  systemPerformance: {
    cpu: number;
    memory: number;
    disk: number;
    network: number;
  };
  apiMetrics: {
    responseTime: number;
    requestsPerSecond: number;
    errorRate: number;
    uptime: number;
  };
  marketplaceStatus: {
    [key: string]: {
      status: 'online' | 'offline' | 'maintenance' | 'error';
      orders: number;
      products: number;
      lastSync: number;
    };
  };
  businessMetrics: {
    totalRevenue: number;
    ordersToday: number;
    conversionRate: number;
    averageOrderValue: number;
  };
}

export interface WebSocketConnection {
  socket: WebSocket | null;
  connected: boolean;
  reconnectAttempts: number;
  maxReconnectAttempts: number;
  reconnectInterval: number;
  heartbeatInterval: number;
  lastHeartbeat: number;
  messageQueue: any[];
}

export interface DashboardWidget {
  id: string;
  title: string;
  type: 'chart' | 'metric' | 'table' | 'status' | 'custom';
  size: 'small' | 'medium' | 'large' | 'xlarge';
  position: { x: number; y: number; w: number; h: number };
  config: any;
  data: any;
  refreshInterval: number;
  isVisible: boolean;
  isLoading: boolean;
  error?: string;
}

export interface PredictiveAnalytics {
  nextHour: {
    expectedOrders: number;
    expectedRevenue: number;
    systemLoad: number;
    confidence: number;
  };
  trends: {
    sales: 'up' | 'down' | 'stable';
    performance: 'improving' | 'degrading' | 'stable';
    errors: 'increasing' | 'decreasing' | 'stable';
  };
  recommendations: {
    id: string;
    type: 'performance' | 'business' | 'security';
    priority: 'high' | 'medium' | 'low';
    message: string;
    action?: string;
  }[];
}

// WebSocket Manager Hook
const useWebSocket = (url: string, onMessage: (data: any) => void) => {
  const [connection, setConnection] = useState<WebSocketConnection>({
    socket: null,
    connected: false,
    reconnectAttempts: 0,
    maxReconnectAttempts: 10,
    reconnectInterval: 5000,
    heartbeatInterval: 30000,
    lastHeartbeat: Date.now(),
    messageQueue: []
  });

  const reconnectTimeoutRef = useRef<NodeJS.Timeout>();
  const heartbeatTimeoutRef = useRef<NodeJS.Timeout>();

  const connect = useCallback(() => {
    try {
      const socket = new WebSocket(url);
      
      socket.onopen = () => {
        console.log('🔗 WebSocket connected');
        setConnection(prev => ({
          ...prev,
          socket,
          connected: true,
          reconnectAttempts: 0
        }));
        
        // Send queued messages
        connection.messageQueue.forEach(message => {
          socket.send(JSON.stringify(message));
        });
        setConnection(prev => ({ ...prev, messageQueue: [] }));
        
        // Start heartbeat
        startHeartbeat(socket);
      };

      socket.onmessage = (event) => {
        try {
          const data = JSON.parse(event.data);
          if (data.type === 'heartbeat') {
            setConnection(prev => ({ ...prev, lastHeartbeat: Date.now() }));
            return;
          }
          onMessage(data);
        } catch (error) {
          console.error('WebSocket message parsing error:', error);
        }
      };

      socket.onclose = () => {
        console.log('📡 WebSocket disconnected');
        setConnection(prev => ({
          ...prev,
          socket: null,
          connected: false
        }));
        scheduleReconnect();
      };

      socket.onerror = (error) => {
        console.error('❌ WebSocket error:', error);
      };

    } catch (error) {
      console.error('WebSocket connection failed:', error);
      scheduleReconnect();
    }
  }, [url, onMessage]);

  const startHeartbeat = (socket: WebSocket) => {
    heartbeatTimeoutRef.current = setInterval(() => {
      if (socket.readyState === WebSocket.OPEN) {
        socket.send(JSON.stringify({ type: 'heartbeat' }));
      }
    }, connection.heartbeatInterval);
  };

  const scheduleReconnect = useCallback(() => {
    if (connection.reconnectAttempts < connection.maxReconnectAttempts) {
      reconnectTimeoutRef.current = setTimeout(() => {
        setConnection(prev => ({
          ...prev,
          reconnectAttempts: prev.reconnectAttempts + 1
        }));
        connect();
      }, connection.reconnectInterval);
    }
  }, [connection.reconnectAttempts, connection.maxReconnectAttempts, connection.reconnectInterval, connect]);

  const sendMessage = useCallback((message: any) => {
    if (connection.socket && connection.connected) {
      connection.socket.send(JSON.stringify(message));
    } else {
      setConnection(prev => ({
        ...prev,
        messageQueue: [...prev.messageQueue, message]
      }));
    }
  }, [connection.socket, connection.connected]);

  const disconnect = useCallback(() => {
    if (reconnectTimeoutRef.current) {
      clearTimeout(reconnectTimeoutRef.current);
    }
    if (heartbeatTimeoutRef.current) {
      clearInterval(heartbeatTimeoutRef.current);
    }
    if (connection.socket) {
      connection.socket.close();
    }
  }, [connection.socket]);

  useEffect(() => {
    connect();
    return disconnect;
  }, [connect, disconnect]);

  return { connection, sendMessage, disconnect };
};

// Real-time Metrics Display Component
const RealTimeMetricsPanel: React.FC<{ metrics: RealTimeMetrics }> = ({ metrics }) => {
  const getStatusColor = (value: number, thresholds: { good: number; warning: number }) => {
    if (value <= thresholds.good) return MS365Colors.primary.green[500];
    if (value <= thresholds.warning) return '#f59e0b';
    return MS365Colors.primary.red[500];
  };

  return (
    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(250px, 1fr))', gap: MS365Spacing[4] }}>
      {/* System Performance */}
      <MS365Card
        title="🖥️ System Performance"
        variant="elevated"
        content={
          <div style={{ display: 'flex', flexDirection: 'column', gap: MS365Spacing[3] }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <span style={{ fontSize: MS365Typography.sizes.sm }}>CPU Usage</span>
              <span style={{ 
                color: getStatusColor(metrics.systemPerformance.cpu, { good: 70, warning: 85 }),
                fontWeight: MS365Typography.weights.semibold 
              }}>
                {metrics.systemPerformance.cpu.toFixed(1)}%
              </span>
            </div>
            <div style={{ 
              width: '100%', 
              height: '8px', 
              backgroundColor: MS365Colors.neutral[200], 
              borderRadius: '4px',
              overflow: 'hidden'
            }}>
              <div style={{
                width: `${metrics.systemPerformance.cpu}%`,
                height: '100%',
                backgroundColor: getStatusColor(metrics.systemPerformance.cpu, { good: 70, warning: 85 }),
                transition: 'width 0.3s ease'
              }} />
            </div>
            
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <span style={{ fontSize: MS365Typography.sizes.sm }}>Memory Usage</span>
              <span style={{ 
                color: getStatusColor(metrics.systemPerformance.memory, { good: 70, warning: 85 }),
                fontWeight: MS365Typography.weights.semibold 
              }}>
                {metrics.systemPerformance.memory.toFixed(1)}%
              </span>
            </div>
            <div style={{ 
              width: '100%', 
              height: '8px', 
              backgroundColor: MS365Colors.neutral[200], 
              borderRadius: '4px',
              overflow: 'hidden'
            }}>
              <div style={{
                width: `${metrics.systemPerformance.memory}%`,
                height: '100%',
                backgroundColor: getStatusColor(metrics.systemPerformance.memory, { good: 70, warning: 85 }),
                transition: 'width 0.3s ease'
              }} />
            </div>
          </div>
        }
      />

      {/* API Metrics */}
      <MS365Card
        title="🌐 API Performance"
        variant="elevated"
        content={
          <div style={{ display: 'flex', flexDirection: 'column', gap: MS365Spacing[3] }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <span style={{ fontSize: MS365Typography.sizes.sm }}>Response Time</span>
              <span style={{ 
                color: getStatusColor(metrics.apiMetrics.responseTime, { good: 200, warning: 500 }),
                fontWeight: MS365Typography.weights.semibold 
              }}>
                {metrics.apiMetrics.responseTime}ms
              </span>
            </div>
            
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <span style={{ fontSize: MS365Typography.sizes.sm }}>Requests/sec</span>
              <span style={{ 
                color: MS365Colors.primary.blue[600],
                fontWeight: MS365Typography.weights.semibold 
              }}>
                {metrics.apiMetrics.requestsPerSecond.toFixed(1)}
              </span>
            </div>
            
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <span style={{ fontSize: MS365Typography.sizes.sm }}>Error Rate</span>
              <span style={{ 
                color: getStatusColor(metrics.apiMetrics.errorRate * 100, { good: 1, warning: 5 }),
                fontWeight: MS365Typography.weights.semibold 
              }}>
                {(metrics.apiMetrics.errorRate * 100).toFixed(2)}%
              </span>
            </div>
            
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <span style={{ fontSize: MS365Typography.sizes.sm }}>Uptime</span>
              <span style={{ 
                color: metrics.apiMetrics.uptime > 99.9 ? MS365Colors.primary.green[600] : '#f59e0b',
                fontWeight: MS365Typography.weights.semibold 
              }}>
                {metrics.apiMetrics.uptime.toFixed(2)}%
              </span>
            </div>
          </div>
        }
      />

      {/* Business Metrics */}
      <MS365Card
        title="💰 Business Metrics"
        variant="elevated"
        content={
          <div style={{ display: 'flex', flexDirection: 'column', gap: MS365Spacing[3] }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <span style={{ fontSize: MS365Typography.sizes.sm }}>Revenue Today</span>
              <span style={{ 
                color: MS365Colors.primary.green[600],
                fontWeight: MS365Typography.weights.semibold,
                fontSize: MS365Typography.sizes.lg
              }}>
                ${metrics.businessMetrics.totalRevenue.toLocaleString()}
              </span>
            </div>
            
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <span style={{ fontSize: MS365Typography.sizes.sm }}>Orders Today</span>
              <span style={{ 
                color: MS365Colors.primary.blue[600],
                fontWeight: MS365Typography.weights.semibold 
              }}>
                {metrics.businessMetrics.ordersToday.toLocaleString()}
              </span>
            </div>
            
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <span style={{ fontSize: MS365Typography.sizes.sm }}>Conversion Rate</span>
              <span style={{ 
                color: MS365Colors.primary.purple[600],
                fontWeight: MS365Typography.weights.semibold 
              }}>
                {(metrics.businessMetrics.conversionRate * 100).toFixed(1)}%
              </span>
            </div>
            
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <span style={{ fontSize: MS365Typography.sizes.sm }}>Avg Order Value</span>
              <span style={{ 
                color: MS365Colors.primary.orange[600],
                fontWeight: MS365Typography.weights.semibold 
              }}>
                ${metrics.businessMetrics.averageOrderValue.toFixed(2)}
              </span>
            </div>
          </div>
        }
      />

      {/* Active Users */}
      <MS365Card
        title="👥 Live Activity"
        variant="elevated"
        content={
          <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: MS365Spacing[2] }}>
            <div style={{
              fontSize: '3rem',
              fontWeight: MS365Typography.weights.bold,
              color: MS365Colors.primary.blue[600]
            }}>
              {metrics.activeUsers}
            </div>
            <div style={{
              fontSize: MS365Typography.sizes.sm,
              color: MS365Colors.neutral[600]
            }}>
              Active Users
            </div>
            <div style={{
              display: 'flex',
              alignItems: 'center',
              gap: MS365Spacing[1],
              fontSize: MS365Typography.sizes.xs,
              color: MS365Colors.neutral[500]
            }}>
              <div style={{
                width: '8px',
                height: '8px',
                borderRadius: '50%',
                backgroundColor: MS365Colors.primary.green[500]
              }} />
              Real-time
            </div>
          </div>
        }
      />
    </div>
  );
};

// Marketplace Status Grid Component
const MarketplaceStatusGrid: React.FC<{ marketplaces: RealTimeMetrics['marketplaceStatus'] }> = ({ marketplaces }) => {
  const getStatusColor = (status: string) => {
    switch (status) {
      case 'online': return MS365Colors.primary.green[500];
      case 'offline': return MS365Colors.neutral[400];
      case 'maintenance': return '#f59e0b';
      case 'error': return MS365Colors.primary.red[500];
      default: return MS365Colors.neutral[400];
    }
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'online': return '✅';
      case 'offline': return '⚫';
      case 'maintenance': return '🔧';
      case 'error': return '❌';
      default: return '❓';
    }
  };

  return (
    <MS365Card
      title="🏪 Marketplace Status"
      variant="elevated"
      headerActions={
        <div style={{ 
          fontSize: MS365Typography.sizes.xs, 
          color: MS365Colors.neutral[600],
          display: 'flex',
          alignItems: 'center',
          gap: MS365Spacing[1]
        }}>
          <div style={{
            width: '8px',
            height: '8px',
            borderRadius: '50%',
            backgroundColor: MS365Colors.primary.green[500]
          }} />
          Live Status
        </div>
      }
      content={
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: MS365Spacing[3] }}>
          {Object.entries(marketplaces).map(([marketplace, data]) => (
            <div 
              key={marketplace}
              style={{
                padding: MS365Spacing[3],
                border: `2px solid ${getStatusColor(data.status)}`,
                borderRadius: AdvancedMS365Theme.components.cards.radiuses.md,
                backgroundColor: data.status === 'online' ? `${getStatusColor(data.status)}10` : MS365Colors.neutral[50]
              }}
            >
              <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: MS365Spacing[2] }}>
                <span style={{ 
                  fontSize: MS365Typography.sizes.base,
                  fontWeight: MS365Typography.weights.semibold,
                  textTransform: 'capitalize'
                }}>
                  {marketplace}
                </span>
                <span style={{ fontSize: '1.2rem' }}>
                  {getStatusIcon(data.status)}
                </span>
              </div>
              
              <div style={{ fontSize: MS365Typography.sizes.xs, color: MS365Colors.neutral[600], marginBottom: MS365Spacing[2] }}>
                Status: <span style={{ color: getStatusColor(data.status), fontWeight: MS365Typography.weights.medium }}>
                  {data.status.toUpperCase()}
                </span>
              </div>
              
              <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: MS365Typography.sizes.xs, color: MS365Colors.neutral[600] }}>
                <span>Orders: {data.orders}</span>
                <span>Products: {data.products}</span>
              </div>
              
              {data.lastSync && (
                <div style={{ 
                  fontSize: MS365Typography.sizes.xs, 
                  color: MS365Colors.neutral[500], 
                  marginTop: MS365Spacing[1] 
                }}>
                  Last sync: {new Date(data.lastSync).toLocaleTimeString()}
                </div>
              )}
            </div>
          ))}
        </div>
      }
    />
  );
};

// Main Dashboard Component
export const AdvancedRealTimeDashboard: React.FC = () => {
  const [metrics, setMetrics] = useState<RealTimeMetrics | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [connectionStatus, setConnectionStatus] = useState<'connecting' | 'connected' | 'disconnected'>('connecting');
  const [lastUpdate, setLastUpdate] = useState<Date | null>(null);

  // WebSocket connection
  const { connection, sendMessage } = useWebSocket(
    `ws://localhost:8080/dashboard-realtime`,
    useCallback((data: any) => {
      if (data.type === 'metrics_update') {
        setMetrics(data.data);
        setLastUpdate(new Date());
        setIsLoading(false);
      }
    }, [])
  );

  // Update connection status
  useEffect(() => {
    setConnectionStatus(connection.connected ? 'connected' : 'disconnected');
  }, [connection.connected]);

  // Request initial data
  useEffect(() => {
    if (connection.connected) {
      sendMessage({
        type: 'subscribe',
        channels: ['system_metrics', 'api_metrics', 'business_metrics', 'marketplace_status']
      });
    }
  }, [connection.connected, sendMessage]);

  // Fallback data for demo
  const fallbackMetrics: RealTimeMetrics = {
    timestamp: Date.now(),
    activeUsers: 127,
    systemPerformance: {
      cpu: 45.6,
      memory: 62.3,
      disk: 78.1,
      network: 23.4
    },
    apiMetrics: {
      responseTime: 156,
      requestsPerSecond: 24.7,
      errorRate: 0.012,
      uptime: 99.97
    },
    marketplaceStatus: {
      trendyol: { status: 'online', orders: 156, products: 1247, lastSync: Date.now() - 300000 },
      amazon: { status: 'online', orders: 89, products: 856, lastSync: Date.now() - 180000 },
      n11: { status: 'maintenance', orders: 34, products: 423, lastSync: Date.now() - 600000 },
      hepsiburada: { status: 'online', orders: 67, products: 789, lastSync: Date.now() - 120000 },
      ozon: { status: 'error', orders: 12, products: 156, lastSync: Date.now() - 900000 },
      ebay: { status: 'offline', orders: 0, products: 234, lastSync: Date.now() - 1800000 }
    },
    businessMetrics: {
      totalRevenue: 45678.90,
      ordersToday: 342,
      conversionRate: 0.0347,
      averageOrderValue: 133.45
    }
  };

  const displayMetrics = metrics || fallbackMetrics;

  if (isLoading) {
    return (
      <div style={{ 
        display: 'flex', 
        justifyContent: 'center', 
        alignItems: 'center', 
        height: '400px',
        flexDirection: 'column',
        gap: MS365Spacing[3]
      }}>
        <div style={{ fontSize: '2rem' }}>⏳</div>
        <div style={{ color: MS365Colors.neutral[600] }}>Loading real-time dashboard...</div>
      </div>
    );
  }

  return (
    <div style={{ padding: MS365Spacing[4] }}>
      {/* Header */}
      <div style={{ 
        display: 'flex', 
        justifyContent: 'space-between', 
        alignItems: 'center', 
        marginBottom: MS365Spacing[4] 
      }}>
        <div>
          <h1 style={{ 
            margin: 0, 
            fontSize: MS365Typography.sizes['2xl'], 
            fontWeight: MS365Typography.weights.bold,
            color: MS365Colors.neutral[900]
          }}>
            📊 Real-time Analytics Dashboard
          </h1>
          <p style={{ 
            margin: 0, 
            marginTop: MS365Spacing[1],
            color: MS365Colors.neutral[600],
            fontSize: MS365Typography.sizes.sm
          }}>
            Live system monitoring and business intelligence
          </p>
        </div>
        
        <div style={{ display: 'flex', alignItems: 'center', gap: MS365Spacing[3] }}>
          <div style={{ 
            display: 'flex', 
            alignItems: 'center', 
            gap: MS365Spacing[1],
            fontSize: MS365Typography.sizes.sm,
            color: connectionStatus === 'connected' ? MS365Colors.primary.green[600] : MS365Colors.primary.red[600]
          }}>
            <div style={{
              width: '8px',
              height: '8px',
              borderRadius: '50%',
              backgroundColor: connectionStatus === 'connected' ? MS365Colors.primary.green[500] : MS365Colors.primary.red[500]
            }} />
            {connectionStatus === 'connected' ? 'Connected' : 'Disconnected'}
          </div>
          
          {lastUpdate && (
            <div style={{ 
              fontSize: MS365Typography.sizes.xs,
              color: MS365Colors.neutral[500]
            }}>
              Last update: {lastUpdate.toLocaleTimeString()}
            </div>
          )}
        </div>
      </div>

      {/* Real-time Metrics */}
      <div style={{ marginBottom: MS365Spacing[6] }}>
        <RealTimeMetricsPanel metrics={displayMetrics} />
      </div>

      {/* Marketplace Status */}
      <div style={{ marginBottom: MS365Spacing[6] }}>
        <MarketplaceStatusGrid marketplaces={displayMetrics.marketplaceStatus} />
      </div>
    </div>
  );
};

export default AdvancedRealTimeDashboard; 