import React, { useState, useEffect, useCallback } from 'react';

// Production Monitoring interfaces
interface SystemHealth {
  id: string;
  name: string;
  status: 'healthy' | 'warning' | 'critical' | 'down';
  uptime: number;
  lastCheck: string;
  responseTime: number;
  errorRate: number;
  throughput: number;
  dependencies: string[];
}

interface Alert {
  id: string;
  timestamp: string;
  severity: 'info' | 'warning' | 'error' | 'critical';
  system: string;
  metric: string;
  message: string;
  value: number;
  threshold: number;
  status: 'open' | 'acknowledged' | 'resolved';
  assignee?: string;
  resolvedAt?: string;
  escalationLevel: number;
}

interface PerformanceMetric {
  id: string;
  name: string;
  value: number;
  unit: string;
  trend: 'up' | 'down' | 'stable';
  target: number;
  status: 'normal' | 'warning' | 'critical';
  history: number[];
  lastUpdated: string;
}

interface ServiceLevelObjective {
  id: string;
  name: string;
  description: string;
  target: number;
  current: number;
  unit: string;
  period: string;
  status: 'met' | 'at_risk' | 'breached';
  errorBudget: number;
  burnRate: number;
}

interface IncidentTimeline {
  id: string;
  timestamp: string;
  type: 'alert' | 'escalation' | 'mitigation' | 'resolution';
  description: string;
  user: string;
  impact: string;
  duration?: number;
}

interface DashboardWidget {
  id: string;
  title: string;
  type: 'metric' | 'chart' | 'status' | 'alert_list' | 'slo';
  position: { x: number; y: number; width: number; height: number };
  config: any;
  isVisible: boolean;
}

export const ProductionMonitoringDashboard: React.FC = () => {
  const [systemHealth, setSystemHealth] = useState<SystemHealth[]>([]);
  const [alerts, setAlerts] = useState<Alert[]>([]);
  const [performanceMetrics, setPerformanceMetrics] = useState<PerformanceMetric[]>([]);
  const [slos, setSlos] = useState<ServiceLevelObjective[]>([]);
  const [incidentTimeline, setIncidentTimeline] = useState<IncidentTimeline[]>([]);
  const [widgets, setWidgets] = useState<DashboardWidget[]>([]);
  const [selectedTab, setSelectedTab] = useState('overview');
  const [isAlertingEnabled, setIsAlertingEnabled] = useState(true);

  // Initialize production monitoring
  useEffect(() => {
    setSystemHealth([
      {
        id: 'api_gateway',
        name: 'API Gateway',
        status: 'healthy',
        uptime: 99.97,
        lastCheck: new Date().toISOString(),
        responseTime: 45,
        errorRate: 0.02,
        throughput: 1247,
        dependencies: ['database', 'cache_cluster', 'auth_service']
      },
      {
        id: 'database',
        name: 'Primary Database',
        status: 'warning',
        uptime: 99.95,
        lastCheck: new Date().toISOString(),
        responseTime: 78,
        errorRate: 0.05,
        throughput: 892,
        dependencies: ['storage', 'backup_service']
      },
      {
        id: 'cache_cluster',
        name: 'Redis Cache Cluster',
        status: 'healthy',
        uptime: 99.99,
        lastCheck: new Date().toISOString(),
        responseTime: 12,
        errorRate: 0.01,
        throughput: 3456,
        dependencies: ['memory_nodes']
      },
      {
        id: 'ml_pipeline',
        name: 'ML Processing Pipeline',
        status: 'healthy',
        uptime: 99.92,
        lastCheck: new Date().toISOString(),
        responseTime: 156,
        errorRate: 0.03,
        throughput: 234,
        dependencies: ['ai_models', 'data_storage']
      },
      {
        id: 'auth_service',
        name: 'Authentication Service',
        status: 'critical',
        uptime: 99.87,
        lastCheck: new Date().toISOString(),
        responseTime: 234,
        errorRate: 0.12,
        throughput: 567,
        dependencies: ['user_database', 'session_store']
      }
    ]);

    setAlerts([
      {
        id: 'alert_001',
        timestamp: '2025-01-17T22:30:00Z',
        severity: 'critical',
        system: 'auth_service',
        metric: 'error_rate',
        message: 'Authentication service error rate exceeded 10%',
        value: 12.3,
        threshold: 10,
        status: 'open',
        escalationLevel: 2
      },
      {
        id: 'alert_002',
        timestamp: '2025-01-17T22:25:00Z',
        severity: 'warning',
        system: 'database',
        metric: 'response_time',
        message: 'Database response time above normal threshold',
        value: 78,
        threshold: 70,
        status: 'acknowledged',
        assignee: 'DevOps Team',
        escalationLevel: 1
      },
      {
        id: 'alert_003',
        timestamp: '2025-01-17T22:15:00Z',
        severity: 'info',
        system: 'cache_cluster',
        metric: 'memory_usage',
        message: 'Cache memory usage increased',
        value: 67,
        threshold: 65,
        status: 'resolved',
        resolvedAt: '2025-01-17T22:20:00Z',
        escalationLevel: 0
      }
    ]);

    setPerformanceMetrics([
      {
        id: 'requests_per_second',
        name: 'Requests per Second',
        value: 1247,
        unit: 'req/s',
        trend: 'up',
        target: 1500,
        status: 'normal',
        history: [987, 1123, 1089, 1156, 1203, 1247],
        lastUpdated: new Date().toISOString()
      },
      {
        id: 'average_response_time',
        name: 'Average Response Time',
        value: 47,
        unit: 'ms',
        trend: 'stable',
        target: 50,
        status: 'normal',
        history: [45, 48, 46, 49, 46, 47],
        lastUpdated: new Date().toISOString()
      },
      {
        id: 'error_rate',
        name: 'Overall Error Rate',
        value: 0.08,
        unit: '%',
        trend: 'down',
        target: 0.1,
        status: 'normal',
        history: [0.12, 0.11, 0.09, 0.08, 0.08, 0.08],
        lastUpdated: new Date().toISOString()
      },
      {
        id: 'cpu_utilization',
        name: 'CPU Utilization',
        value: 67.8,
        unit: '%',
        trend: 'up',
        target: 70,
        status: 'warning',
        history: [58.2, 61.4, 63.7, 65.9, 66.8, 67.8],
        lastUpdated: new Date().toISOString()
      },
      {
        id: 'memory_usage',
        name: 'Memory Usage',
        value: 78.5,
        unit: '%',
        trend: 'stable',
        target: 80,
        status: 'warning',
        history: [76.2, 77.1, 78.3, 78.9, 78.7, 78.5],
        lastUpdated: new Date().toISOString()
      }
    ]);

    setSlos([
      {
        id: 'slo_001',
        name: 'API Availability',
        description: '99.9% uptime over 30 days',
        target: 99.9,
        current: 99.97,
        unit: '%',
        period: '30 days',
        status: 'met',
        errorBudget: 85.3,
        burnRate: 0.12
      },
      {
        id: 'slo_002',
        name: 'Response Time P95',
        description: '95th percentile response time under 200ms',
        target: 200,
        current: 156,
        unit: 'ms',
        period: '24 hours',
        status: 'met',
        errorBudget: 78.9,
        burnRate: 0.08
      },
      {
        id: 'slo_003',
        name: 'Error Rate',
        description: 'Error rate below 0.1% over 7 days',
        target: 0.1,
        current: 0.08,
        unit: '%',
        period: '7 days',
        status: 'met',
        errorBudget: 92.1,
        burnRate: 0.03
      },
      {
        id: 'slo_004',
        name: 'Data Processing SLA',
        description: 'ML pipeline processing under 5 minutes',
        target: 300,
        current: 234,
        unit: 'seconds',
        period: '24 hours',
        status: 'met',
        errorBudget: 67.4,
        burnRate: 0.15
      }
    ]);

    setIncidentTimeline([
      {
        id: 'timeline_001',
        timestamp: '2025-01-17T22:30:00Z',
        type: 'alert',
        description: 'Critical alert triggered: Authentication service error rate exceeded 10%',
        user: 'System',
        impact: 'User login failures affecting 15% of users'
      },
      {
        id: 'timeline_002',
        timestamp: '2025-01-17T22:32:00Z',
        type: 'escalation',
        description: 'Alert escalated to Level 2 - DevOps team notified',
        user: 'AlertManager',
        impact: 'On-call engineer paged'
      },
      {
        id: 'timeline_003',
        timestamp: '2025-01-17T22:35:00Z',
        type: 'mitigation',
        description: 'Temporary mitigation: Redirecting traffic to backup auth service',
        user: 'DevOps Engineer',
        impact: 'Service partially restored'
      }
    ]);

    // Start real-time updates
    const interval = setInterval(() => {
      updateRealTimeMetrics();
    }, 5000);

    return () => clearInterval(interval);
  }, []);

  // Update real-time metrics
  const updateRealTimeMetrics = () => {
    setPerformanceMetrics(prev => prev.map(metric => {
      const variation = (Math.random() - 0.5) * 0.1;
      let newValue = metric.value * (1 + variation);
      
      // Keep values within realistic bounds
      if (metric.unit === '%') {
        newValue = Math.max(0, Math.min(100, newValue));
      } else if (metric.id === 'requests_per_second') {
        newValue = Math.max(800, Math.min(2000, newValue));
      }
      
      const newHistory = [...metric.history.slice(1), newValue];
      
      // Determine status
      let status: 'normal' | 'warning' | 'critical' = 'normal';
      if (metric.id === 'error_rate') {
        status = newValue > metric.target ? 'critical' : 'normal';
      } else {
        const threshold = metric.target * 0.9;
        status = newValue > threshold ? 'warning' : 'normal';
      }
      
      return {
        ...metric,
        value: newValue,
        history: newHistory,
        status,
        lastUpdated: new Date().toISOString()
      };
    }));

    // Update system health
    setSystemHealth(prev => prev.map(system => {
      const responseTimeVariation = (Math.random() - 0.5) * 10;
      const errorRateVariation = (Math.random() - 0.5) * 0.02;
      
      return {
        ...system,
        responseTime: Math.max(10, system.responseTime + responseTimeVariation),
        errorRate: Math.max(0, system.errorRate + errorRateVariation),
        lastCheck: new Date().toISOString()
      };
    }));
  };

  // Acknowledge alert
  const acknowledgeAlert = useCallback((alertId: string) => {
    setAlerts(prev => prev.map(alert => 
      alert.id === alertId 
        ? { ...alert, status: 'acknowledged', assignee: 'Current User' }
        : alert
    ));
  }, []);

  // Resolve alert
  const resolveAlert = useCallback((alertId: string) => {
    setAlerts(prev => prev.map(alert => 
      alert.id === alertId 
        ? { ...alert, status: 'resolved', resolvedAt: new Date().toISOString() }
        : alert
    ));
  }, []);

  // Create manual alert
  const createManualAlert = useCallback(() => {
    const newAlert: Alert = {
      id: `alert_${Date.now()}`,
      timestamp: new Date().toISOString(),
      severity: 'warning',
      system: 'manual',
      metric: 'manual_check',
      message: 'Manual alert created for investigation',
      value: 0,
      threshold: 0,
      status: 'open',
      escalationLevel: 1
    };
    
    setAlerts(prev => [newAlert, ...prev]);
  }, []);

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'healthy': case 'met': case 'normal': return 'text-green-600 bg-green-100';
      case 'warning': case 'at_risk': return 'text-yellow-600 bg-yellow-100';
      case 'critical': case 'breached': return 'text-red-600 bg-red-100';
      case 'down': return 'text-gray-600 bg-gray-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const getSeverityColor = (severity: string) => {
    switch (severity) {
      case 'critical': return 'text-red-600 bg-red-100';
      case 'error': return 'text-red-600 bg-red-100';
      case 'warning': return 'text-yellow-600 bg-yellow-100';
      case 'info': return 'text-blue-600 bg-blue-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const tabs = [
    { id: 'overview', label: 'Overview', count: systemHealth.length },
    { id: 'alerts', label: 'Alerts', count: alerts.filter(a => a.status === 'open').length },
    { id: 'metrics', label: 'Metrics', count: performanceMetrics.length },
    { id: 'slo', label: 'SLOs', count: slos.length },
    { id: 'incidents', label: 'Incidents', count: incidentTimeline.length }
  ];

  return (
    <div className="production-monitoring-dashboard p-6">
      <div className="mb-6">
        <h2 className="text-2xl font-bold text-gray-900 mb-2">Production Monitoring Dashboard</h2>
        <p className="text-gray-600">Real-time system health, performance metrics, and incident management</p>
      </div>

      {/* Overall System Status */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div className="bg-white rounded-lg shadow p-4">
          <h3 className="text-sm font-medium text-gray-500">Systems Status</h3>
          <p className="text-2xl font-bold text-green-600">
            {systemHealth.filter(s => s.status === 'healthy').length}/{systemHealth.length}
          </p>
          <p className="text-xs text-gray-600">Healthy</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4">
          <h3 className="text-sm font-medium text-gray-500">Open Alerts</h3>
          <p className="text-2xl font-bold text-red-600">
            {alerts.filter(a => a.status === 'open').length}
          </p>
          <p className="text-xs text-gray-600">Requiring attention</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4">
          <h3 className="text-sm font-medium text-gray-500">Average Response</h3>
          <p className="text-2xl font-bold text-blue-600">
            {performanceMetrics.find(m => m.id === 'average_response_time')?.value.toFixed(0)}ms
          </p>
          <p className="text-xs text-gray-600">API response time</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4">
          <h3 className="text-sm font-medium text-gray-500">SLO Compliance</h3>
          <p className="text-2xl font-bold text-green-600">
            {slos.filter(s => s.status === 'met').length}/{slos.length}
          </p>
          <p className="text-xs text-gray-600">Meeting targets</p>
        </div>
      </div>

      {/* Quick Actions */}
      <div className="bg-white rounded-lg shadow p-4 mb-6">
        <div className="flex justify-between items-center">
          <h3 className="text-lg font-semibold text-gray-900">Production Control Center</h3>
          <div className="flex space-x-2">
            <button
              onClick={createManualAlert}
              className="px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700 transition-colors"
            >
              Create Alert
            </button>
            <button
              onClick={() => setIsAlertingEnabled(!isAlertingEnabled)}
              className={`px-4 py-2 rounded transition-colors ${
                isAlertingEnabled 
                  ? 'bg-green-600 text-white hover:bg-green-700' 
                  : 'bg-gray-600 text-white hover:bg-gray-700'
              }`}
            >
              {isAlertingEnabled ? 'Alerting ON' : 'Alerting OFF'}
            </button>
          </div>
        </div>
      </div>

      {/* Tab Navigation */}
      <div className="border-b border-gray-200 mb-6">
        <nav className="-mb-px flex space-x-8">
          {tabs.map((tab) => (
            <button
              key={tab.id}
              onClick={() => setSelectedTab(tab.id)}
              className={`whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm ${
                selectedTab === tab.id
                  ? 'border-green-500 text-green-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              }`}
            >
              {tab.label}
              <span className="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">
                {tab.count}
              </span>
            </button>
          ))}
        </nav>
      </div>

      {/* Tab Content */}
      {selectedTab === 'overview' && (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {systemHealth.map((system, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <h3 className="text-lg font-semibold text-gray-900">{system.name}</h3>
                <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(system.status)}`}>
                  {system.status}
                </span>
              </div>
              
              <div className="grid grid-cols-2 gap-4 mb-4">
                <div>
                  <p className="text-sm text-gray-600">Uptime</p>
                  <p className="text-lg font-semibold text-green-600">{system.uptime.toFixed(2)}%</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Response Time</p>
                  <p className="text-lg font-semibold">{system.responseTime.toFixed(0)}ms</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Error Rate</p>
                  <p className="text-lg font-semibold text-red-600">{system.errorRate.toFixed(2)}%</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Throughput</p>
                  <p className="text-lg font-semibold">{system.throughput} req/min</p>
                </div>
              </div>
              
              {system.dependencies.length > 0 && (
                <div>
                  <h4 className="font-medium text-gray-900 mb-2">Dependencies</h4>
                  <div className="flex flex-wrap gap-2">
                    {system.dependencies.map((dep, i) => (
                      <span key={i} className="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">
                        {dep}
                      </span>
                    ))}
                  </div>
                </div>
              )}
              
              <p className="text-xs text-gray-500 mt-3">
                Last checked: {new Date(system.lastCheck).toLocaleString()}
              </p>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'alerts' && (
        <div className="space-y-4">
          {alerts.map((alert, index) => (
            <div key={index} className={`bg-white rounded-lg shadow p-6 border-l-4 ${
              alert.severity === 'critical' ? 'border-red-500' :
              alert.severity === 'error' ? 'border-red-400' :
              alert.severity === 'warning' ? 'border-yellow-400' :
              'border-blue-400'
            }`}>
              <div className="flex justify-between items-start mb-4">
                <div>
                  <h3 className="text-lg font-semibold text-gray-900">{alert.message}</h3>
                  <p className="text-sm text-gray-600">{alert.system} • {alert.metric}</p>
                </div>
                <div className="flex space-x-2">
                  <span className={`px-2 py-1 text-xs rounded-full ${getSeverityColor(alert.severity)}`}>
                    {alert.severity}
                  </span>
                  <span className={`px-2 py-1 text-xs rounded-full ${
                    alert.status === 'open' ? 'bg-red-100 text-red-800' :
                    alert.status === 'acknowledged' ? 'bg-yellow-100 text-yellow-800' :
                    'bg-green-100 text-green-800'
                  }`}>
                    {alert.status}
                  </span>
                </div>
              </div>
              
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div>
                  <p className="text-sm text-gray-600">Current Value</p>
                  <p className="font-semibold">{alert.value}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Threshold</p>
                  <p className="font-semibold">{alert.threshold}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Escalation Level</p>
                  <p className="font-semibold">Level {alert.escalationLevel}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Triggered</p>
                  <p className="font-semibold">{new Date(alert.timestamp).toLocaleString()}</p>
                </div>
              </div>
              
              {alert.assignee && (
                <p className="text-sm text-gray-600 mb-3">Assigned to: {alert.assignee}</p>
              )}
              
              <div className="flex space-x-2">
                {alert.status === 'open' && (
                  <button
                    onClick={() => acknowledgeAlert(alert.id)}
                    className="px-3 py-1 bg-yellow-600 text-white text-sm rounded hover:bg-yellow-700 transition-colors"
                  >
                    Acknowledge
                  </button>
                )}
                {alert.status !== 'resolved' && (
                  <button
                    onClick={() => resolveAlert(alert.id)}
                    className="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition-colors"
                  >
                    Resolve
                  </button>
                )}
                <button className="px-3 py-1 bg-gray-600 text-white text-sm rounded hover:bg-gray-700 transition-colors">
                  View Details
                </button>
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'metrics' && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {performanceMetrics.map((metric, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <h3 className="text-lg font-semibold text-gray-900">{metric.name}</h3>
                <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(metric.status)}`}>
                  {metric.status}
                </span>
              </div>
              
              <div className="mb-4">
                <div className="flex items-baseline">
                  <span className="text-3xl font-bold text-gray-900">
                    {metric.value.toFixed(metric.unit === '%' ? 1 : 0)}
                  </span>
                  <span className="ml-1 text-lg text-gray-600">{metric.unit}</span>
                  <span className={`ml-2 text-sm ${
                    metric.trend === 'up' ? 'text-green-500' : 
                    metric.trend === 'down' ? 'text-red-500' : 'text-gray-500'
                  }`}>
                    {metric.trend === 'up' ? '↗' : metric.trend === 'down' ? '↘' : '→'}
                  </span>
                </div>
                <p className="text-sm text-gray-600">Target: {metric.target} {metric.unit}</p>
              </div>
              
              {/* Mini chart */}
              <div className="mb-4">
                <div className="flex items-end space-x-1 h-16">
                  {metric.history.map((value, i) => (
                    <div
                      key={i}
                      className="bg-blue-500 rounded-t flex-1"
                      style={{ 
                        height: `${(value / Math.max(...metric.history)) * 100}%`,
                        minHeight: '4px'
                      }}
                    ></div>
                  ))}
                </div>
              </div>
              
              <p className="text-xs text-gray-500">
                Last updated: {new Date(metric.lastUpdated).toLocaleString()}
              </p>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'slo' && (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {slos.map((slo, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <h3 className="text-lg font-semibold text-gray-900">{slo.name}</h3>
                <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(slo.status)}`}>
                  {slo.status}
                </span>
              </div>
              
              <p className="text-gray-700 mb-4">{slo.description}</p>
              
              <div className="grid grid-cols-2 gap-4 mb-4">
                <div>
                  <p className="text-sm text-gray-600">Target</p>
                  <p className="text-lg font-semibold">{slo.target} {slo.unit}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Current</p>
                  <p className={`text-lg font-semibold ${
                    slo.status === 'met' ? 'text-green-600' :
                    slo.status === 'at_risk' ? 'text-yellow-600' : 'text-red-600'
                  }`}>
                    {slo.current} {slo.unit}
                  </p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Error Budget</p>
                  <p className="text-lg font-semibold">{slo.errorBudget.toFixed(1)}%</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Burn Rate</p>
                  <p className="text-lg font-semibold">{slo.burnRate.toFixed(2)}</p>
                </div>
              </div>
              
              <div className="bg-gray-100 rounded-full h-2 mb-2">
                <div 
                  className={`h-2 rounded-full ${
                    slo.status === 'met' ? 'bg-green-500' :
                    slo.status === 'at_risk' ? 'bg-yellow-500' : 'bg-red-500'
                  }`}
                  style={{ width: `${(slo.current / slo.target) * 100}%` }}
                ></div>
              </div>
              
              <p className="text-xs text-gray-500">Period: {slo.period}</p>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'incidents' && (
        <div className="bg-white rounded-lg shadow p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-6">Incident Timeline</h3>
          
          <div className="space-y-4">
            {incidentTimeline.map((event, index) => (
              <div key={index} className="flex items-start space-x-4">
                <div className={`flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center ${
                  event.type === 'alert' ? 'bg-red-100 text-red-600' :
                  event.type === 'escalation' ? 'bg-orange-100 text-orange-600' :
                  event.type === 'mitigation' ? 'bg-blue-100 text-blue-600' :
                  'bg-green-100 text-green-600'
                }`}>
                  {event.type === 'alert' ? '!' :
                   event.type === 'escalation' ? '↑' :
                   event.type === 'mitigation' ? '⚡' : '✓'}
                </div>
                
                <div className="flex-1">
                  <div className="flex justify-between items-start">
                    <div>
                      <h4 className="font-medium text-gray-900 capitalize">{event.type.replace('_', ' ')}</h4>
                      <p className="text-gray-700">{event.description}</p>
                      <p className="text-sm text-gray-600">Impact: {event.impact}</p>
                    </div>
                    <div className="text-right text-sm text-gray-500">
                      <p>{new Date(event.timestamp).toLocaleString()}</p>
                      <p>{event.user}</p>
                    </div>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
};

export default ProductionMonitoringDashboard; 