import React, { useState, useEffect, useCallback } from 'react';

// Operations Command Center interfaces
interface SystemComponent {
  id: string;
  name: string;
  category: 'core' | 'database' | 'cache' | 'api' | 'ml' | 'security';
  status: 'operational' | 'warning' | 'critical' | 'maintenance' | 'offline';
  uptime: number;
  responseTime: number;
  throughput: number;
  errorRate: number;
  resourceUsage: {
    cpu: number;
    memory: number;
    disk: number;
    network: number;
  };
  lastHealthCheck: string;
  dependencies: string[];
  version: string;
  replicas: number;
  location: string;
}

interface OperationalAlert {
  id: string;
  timestamp: string;
  severity: 'info' | 'warning' | 'critical' | 'emergency';
  component: string;
  title: string;
  description: string;
  status: 'active' | 'investigating' | 'mitigating' | 'resolved';
  assignee?: string;
  estimatedImpact: string;
  affectedUsers: number;
  mttr?: number; // Mean Time To Recovery
  actions: ActionItem[];
}

interface ActionItem {
  id: string;
  description: string;
  status: 'pending' | 'in_progress' | 'completed';
  assignee: string;
  estimatedTime: number;
  actualTime?: number;
}

interface PerformanceMetric {
  timestamp: string;
  component: string;
  metric: string;
  value: number;
  threshold: number;
  status: 'normal' | 'warning' | 'critical';
}

interface IncidentLog {
  id: string;
  timestamp: string;
  severity: 'info' | 'warning' | 'error' | 'critical';
  component: string;
  message: string;
  details?: string;
  correlationId?: string;
  resolved: boolean;
}

interface CapacityForecast {
  component: string;
  currentUtilization: number;
  projectedUtilization: number;
  timeToCapacity: number; // days
  recommendedAction: string;
  confidence: number;
}

interface AutomationRule {
  id: string;
  name: string;
  trigger: string;
  condition: string;
  action: string;
  enabled: boolean;
  lastTriggered?: string;
  executionCount: number;
  successRate: number;
}

export const OperationsCommandCenter: React.FC = () => {
  const [systemComponents, setSystemComponents] = useState<SystemComponent[]>([]);
  const [operationalAlerts, setOperationalAlerts] = useState<OperationalAlert[]>([]);
  const [performanceMetrics, setPerformanceMetrics] = useState<PerformanceMetric[]>([]);
  const [incidentLogs, setIncidentLogs] = useState<IncidentLog[]>([]);
  const [capacityForecasts, setCapacityForecasts] = useState<CapacityForecast[]>([]);
  const [automationRules, setAutomationRules] = useState<AutomationRule[]>([]);
  const [selectedTab, setSelectedTab] = useState('overview');
  const [autoRefresh, setAutoRefresh] = useState(true);
  const [refreshInterval, setRefreshInterval] = useState(5000);

  // Initialize Operations Command Center
  useEffect(() => {
    // Initialize system components
    setSystemComponents([
      {
        id: 'api_gateway',
        name: 'API Gateway',
        category: 'api',
        status: 'operational',
        uptime: 99.98,
        responseTime: 42,
        throughput: 2847,
        errorRate: 0.01,
        resourceUsage: { cpu: 35, memory: 68, disk: 45, network: 52 },
        lastHealthCheck: new Date().toISOString(),
        dependencies: ['load_balancer', 'auth_service'],
        version: 'v2.2.0',
        replicas: 3,
        location: 'EU-West-1'
      },
      {
        id: 'database_cluster',
        name: 'Database Cluster',
        category: 'database',
        status: 'operational',
        uptime: 99.99,
        responseTime: 8,
        throughput: 1523,
        errorRate: 0.00,
        resourceUsage: { cpu: 42, memory: 78, disk: 65, network: 34 },
        lastHealthCheck: new Date().toISOString(),
        dependencies: [],
        version: 'PostgreSQL 14.2',
        replicas: 3,
        location: 'EU-West-1'
      },
      {
        id: 'cache_layer',
        name: 'Cache Layer (Redis)',
        category: 'cache',
        status: 'operational',
        uptime: 100.00,
        responseTime: 2,
        throughput: 4156,
        errorRate: 0.00,
        resourceUsage: { cpu: 28, memory: 85, disk: 25, network: 67 },
        lastHealthCheck: new Date().toISOString(),
        dependencies: [],
        version: 'Redis 6.2',
        replicas: 3,
        location: 'EU-West-1'
      },
      {
        id: 'ml_pipeline',
        name: 'ML Pipeline',
        category: 'ml',
        status: 'warning',
        uptime: 99.85,
        responseTime: 234,
        throughput: 856,
        errorRate: 0.05,
        resourceUsage: { cpu: 89, memory: 92, disk: 55, network: 43 },
        lastHealthCheck: new Date().toISOString(),
        dependencies: ['database_cluster'],
        version: 'v2.2.0',
        replicas: 2,
        location: 'EU-West-1'
      },
      {
        id: 'security_layer',
        name: 'Security Layer',
        category: 'security',
        status: 'operational',
        uptime: 99.99,
        responseTime: 15,
        throughput: 3247,
        errorRate: 0.00,
        resourceUsage: { cpu: 31, memory: 55, disk: 32, network: 78 },
        lastHealthCheck: new Date().toISOString(),
        dependencies: ['api_gateway'],
        version: 'v2.2.0',
        replicas: 2,
        location: 'EU-West-1'
      },
      {
        id: 'user_service',
        name: 'User Service',
        category: 'core',
        status: 'operational',
        uptime: 99.95,
        responseTime: 38,
        throughput: 1847,
        errorRate: 0.02,
        resourceUsage: { cpu: 45, memory: 62, disk: 38, network: 41 },
        lastHealthCheck: new Date().toISOString(),
        dependencies: ['database_cluster', 'cache_layer'],
        version: 'v2.2.0',
        replicas: 2,
        location: 'EU-West-1'
      }
    ]);

    // Initialize operational alerts
    setOperationalAlerts([
      {
        id: 'alert_001',
        timestamp: new Date().toISOString(),
        severity: 'warning',
        component: 'ml_pipeline',
        title: 'High CPU Usage in ML Pipeline',
        description: 'ML Pipeline CPU usage has exceeded 85% for more than 10 minutes',
        status: 'investigating',
        assignee: 'ML Team',
        estimatedImpact: 'Potential slowdown in AI features',
        affectedUsers: 0,
        actions: [
          {
            id: 'action_001',
            description: 'Scale ML pipeline instances',
            status: 'in_progress',
            assignee: 'DevOps Team',
            estimatedTime: 15
          },
          {
            id: 'action_002',
            description: 'Optimize ML model inference',
            status: 'pending',
            assignee: 'ML Team',
            estimatedTime: 30
          }
        ]
      },
      {
        id: 'alert_002',
        timestamp: new Date(Date.now() - 300000).toISOString(),
        severity: 'info',
        component: 'cache_layer',
        title: 'Cache Memory Usage Above 80%',
        description: 'Redis cache memory usage is at 85%, consider expanding capacity',
        status: 'active',
        estimatedImpact: 'Potential cache evictions may affect performance',
        affectedUsers: 0,
        actions: [
          {
            id: 'action_003',
            description: 'Monitor cache eviction rates',
            status: 'completed',
            assignee: 'Platform Team',
            estimatedTime: 5,
            actualTime: 3
          }
        ]
      }
    ]);

    // Initialize capacity forecasts
    setCapacityForecasts([
      {
        component: 'database_cluster',
        currentUtilization: 65,
        projectedUtilization: 85,
        timeToCapacity: 45,
        recommendedAction: 'Add read replica or increase storage',
        confidence: 87
      },
      {
        component: 'cache_layer',
        currentUtilization: 85,
        projectedUtilization: 95,
        timeToCapacity: 12,
        recommendedAction: 'Scale cache cluster immediately',
        confidence: 92
      },
      {
        component: 'ml_pipeline',
        currentUtilization: 89,
        projectedUtilization: 98,
        timeToCapacity: 7,
        recommendedAction: 'Urgent: Scale ML compute resources',
        confidence: 95
      }
    ]);

    // Initialize automation rules
    setAutomationRules([
      {
        id: 'rule_001',
        name: 'Auto-scale API Gateway',
        trigger: 'CPU > 80% for 5 minutes',
        condition: 'During business hours',
        action: 'Add 1 replica, max 5 replicas',
        enabled: true,
        lastTriggered: '2025-01-17T14:30:00Z',
        executionCount: 23,
        successRate: 95.7
      },
      {
        id: 'rule_002',
        name: 'Database Connection Pool Alert',
        trigger: 'Active connections > 90%',
        condition: 'Any time',
        action: 'Send alert to DBA team',
        enabled: true,
        executionCount: 8,
        successRate: 100
      },
      {
        id: 'rule_003',
        name: 'Memory Pressure Auto-restart',
        trigger: 'Memory usage > 95% for 10 minutes',
        condition: 'Service has multiple replicas',
        action: 'Rolling restart of affected service',
        enabled: true,
        lastTriggered: '2025-01-15T09:22:00Z',
        executionCount: 3,
        successRate: 100
      }
    ]);

    // Start real-time monitoring
    if (autoRefresh) {
      const interval = setInterval(() => {
        updateMetrics();
        generateIncidentLogs();
      }, refreshInterval);

      return () => clearInterval(interval);
    }
  }, [autoRefresh, refreshInterval]);

  // Update real-time metrics
  const updateMetrics = () => {
    setSystemComponents(prev => prev.map(component => {
      // Simulate real-time updates
      const cpuDelta = Math.floor(Math.random() * 10 - 5);
      const memoryDelta = Math.floor(Math.random() * 6 - 3);
      const responseDelta = Math.floor(Math.random() * 10 - 5);

      return {
        ...component,
        resourceUsage: {
          ...component.resourceUsage,
          cpu: Math.max(0, Math.min(100, component.resourceUsage.cpu + cpuDelta)),
          memory: Math.max(0, Math.min(100, component.resourceUsage.memory + memoryDelta))
        },
        responseTime: Math.max(1, component.responseTime + responseDelta),
        lastHealthCheck: new Date().toISOString()
      };
    }));
  };

  // Generate incident logs
  const generateIncidentLogs = () => {
    if (Math.random() < 0.3) { // 30% chance to generate a log entry
      const components = ['api_gateway', 'database_cluster', 'cache_layer', 'ml_pipeline'];
      const severities: ('info' | 'warning' | 'error' | 'critical')[] = ['info', 'warning', 'error'];
      const messages = [
        'Health check completed successfully',
        'Memory usage threshold exceeded',
        'Response time spike detected',
        'Auto-scaling event triggered',
        'Cache eviction event occurred',
        'Database connection pool warning'
      ];

      const newLog: IncidentLog = {
        id: `log_${Date.now()}`,
        timestamp: new Date().toISOString(),
        severity: severities[Math.floor(Math.random() * severities.length)],
        component: components[Math.floor(Math.random() * components.length)],
        message: messages[Math.floor(Math.random() * messages.length)],
        resolved: Math.random() < 0.8
      };

      setIncidentLogs(prev => [newLog, ...prev.slice(0, 99)]);
    }
  };

  // Acknowledge alert
  const acknowledgeAlert = useCallback((alertId: string, assignee: string) => {
    setOperationalAlerts(prev => prev.map(alert => 
      alert.id === alertId 
        ? { ...alert, status: 'investigating', assignee }
        : alert
    ));
  }, []);

  // Resolve alert
  const resolveAlert = useCallback((alertId: string) => {
    setOperationalAlerts(prev => prev.map(alert => 
      alert.id === alertId 
        ? { ...alert, status: 'resolved', mttr: Math.floor(Math.random() * 30 + 5) }
        : alert
    ));
  }, []);

  // Toggle automation rule
  const toggleAutomationRule = useCallback((ruleId: string) => {
    setAutomationRules(prev => prev.map(rule => 
      rule.id === ruleId 
        ? { ...rule, enabled: !rule.enabled }
        : rule
    ));
  }, []);

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'operational': case 'normal': case 'resolved': case 'completed': return 'text-green-600 bg-green-100';
      case 'warning': case 'investigating': case 'in_progress': return 'text-yellow-600 bg-yellow-100';
      case 'critical': case 'error': case 'active': return 'text-red-600 bg-red-100';
      case 'emergency': return 'text-purple-600 bg-purple-100';
      case 'maintenance': case 'pending': return 'text-blue-600 bg-blue-100';
      case 'offline': return 'text-gray-600 bg-gray-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const getSeverityColor = (severity: string) => {
    switch (severity) {
      case 'info': return 'text-blue-600 bg-blue-100';
      case 'warning': return 'text-yellow-600 bg-yellow-100';
      case 'error': case 'critical': return 'text-red-600 bg-red-100';
      case 'emergency': return 'text-purple-600 bg-purple-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const getCategoryIcon = (category: string) => {
    switch (category) {
      case 'core': return '🏗️';
      case 'database': return '🗄️';
      case 'cache': return '⚡';
      case 'api': return '🔗';
      case 'ml': return '🤖';
      case 'security': return '🛡️';
      default: return '⚙️';
    }
  };

  const getResourceUsageColor = (usage: number) => {
    if (usage < 70) return 'bg-green-500';
    if (usage < 85) return 'bg-yellow-500';
    return 'bg-red-500';
  };

  const tabs = [
    { id: 'overview', label: 'System Overview', count: systemComponents.length },
    { id: 'alerts', label: 'Active Alerts', count: operationalAlerts.filter(a => a.status !== 'resolved').length },
    { id: 'performance', label: 'Performance', count: systemComponents.length },
    { id: 'capacity', label: 'Capacity Planning', count: capacityForecasts.length },
    { id: 'automation', label: 'Automation', count: automationRules.filter(r => r.enabled).length },
    { id: 'logs', label: 'Incident Logs', count: incidentLogs.length }
  ];

  return (
    <div className="operations-command-center p-6">
      <div className="mb-6">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold text-gray-900 mb-2">🎛️ Operations Command Center</h1>
            <p className="text-gray-600">Real-time system monitoring and operational control</p>
          </div>
          <div className="flex space-x-3">
            <button
              onClick={() => setAutoRefresh(!autoRefresh)}
              className={`px-4 py-2 rounded-lg transition-colors ${
                autoRefresh 
                  ? 'bg-green-600 text-white hover:bg-green-700' 
                  : 'bg-gray-600 text-white hover:bg-gray-700'
              }`}
            >
              {autoRefresh ? '⏸️ Pause' : '▶️ Resume'} Auto-refresh
            </button>
            <select
              value={refreshInterval}
              onChange={(e) => setRefreshInterval(Number(e.target.value))}
              className="px-3 py-2 border border-gray-300 rounded-lg"
            >
              <option value={1000}>1 second</option>
              <option value={5000}>5 seconds</option>
              <option value={10000}>10 seconds</option>
              <option value={30000}>30 seconds</option>
            </select>
          </div>
        </div>
      </div>

      {/* System Status Overview */}
      <div className="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Total Components</h3>
          <p className="text-2xl font-bold text-blue-600">{systemComponents.length}</p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Operational</h3>
          <p className="text-2xl font-bold text-green-600">
            {systemComponents.filter(c => c.status === 'operational').length}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Warnings</h3>
          <p className="text-2xl font-bold text-yellow-600">
            {systemComponents.filter(c => c.status === 'warning').length}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Critical</h3>
          <p className="text-2xl font-bold text-red-600">
            {systemComponents.filter(c => c.status === 'critical').length}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Active Alerts</h3>
          <p className="text-2xl font-bold text-orange-600">
            {operationalAlerts.filter(a => a.status !== 'resolved').length}
          </p>
        </div>
        <div className="bg-white rounded-lg shadow p-4 text-center">
          <h3 className="text-sm font-medium text-gray-500">Avg Uptime</h3>
          <p className="text-2xl font-bold text-purple-600">
            {(systemComponents.reduce((sum, c) => sum + c.uptime, 0) / systemComponents.length).toFixed(2)}%
          </p>
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
                  ? 'border-blue-500 text-blue-600'
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
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {systemComponents.map((component, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <h3 className="font-semibold text-gray-900 flex items-center">
                    <span className="mr-2">{getCategoryIcon(component.category)}</span>
                    {component.name}
                  </h3>
                  <p className="text-sm text-gray-600">{component.version} • {component.replicas} replicas</p>
                  <p className="text-xs text-gray-500">{component.location}</p>
                </div>
                <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(component.status)}`}>
                  {component.status}
                </span>
              </div>
              
              <div className="space-y-3">
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Uptime:</span>
                  <span className="font-medium text-green-600">{component.uptime.toFixed(2)}%</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Response:</span>
                  <span className="font-medium">{component.responseTime}ms</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Throughput:</span>
                  <span className="font-medium">{component.throughput}/min</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Error Rate:</span>
                  <span className={`font-medium ${component.errorRate > 0.1 ? 'text-red-600' : 'text-green-600'}`}>
                    {component.errorRate.toFixed(2)}%
                  </span>
                </div>
              </div>
              
              {/* Resource Usage */}
              <div className="mt-4">
                <h4 className="text-sm font-medium text-gray-700 mb-2">Resource Usage</h4>
                <div className="space-y-2">
                  {Object.entries(component.resourceUsage).map(([resource, usage]) => (
                    <div key={resource}>
                      <div className="flex justify-between text-xs">
                        <span className="capitalize">{resource}:</span>
                        <span>{usage}%</span>
                      </div>
                      <div className="w-full bg-gray-200 rounded-full h-1.5">
                        <div 
                          className={`h-1.5 rounded-full ${getResourceUsageColor(usage)}`}
                          style={{ width: `${usage}%` }}
                        ></div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
              
              <p className="text-xs text-gray-500 mt-3">
                Last check: {new Date(component.lastHealthCheck).toLocaleTimeString()}
              </p>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'alerts' && (
        <div className="space-y-4">
          {operationalAlerts.filter(a => a.status !== 'resolved').length > 0 ? (
            operationalAlerts.filter(a => a.status !== 'resolved').map((alert, index) => (
              <div key={index} className="bg-white rounded-lg shadow p-6">
                <div className="flex justify-between items-start mb-4">
                  <div>
                    <div className="flex items-center space-x-2 mb-2">
                      <span className={`px-2 py-1 text-xs rounded-full ${getSeverityColor(alert.severity)}`}>
                        {alert.severity}
                      </span>
                      <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(alert.status)}`}>
                        {alert.status}
                      </span>
                      <span className="text-xs text-gray-500">{alert.component}</span>
                    </div>
                    <h3 className="font-semibold text-gray-900">{alert.title}</h3>
                    <p className="text-sm text-gray-600">{alert.description}</p>
                    <p className="text-sm text-orange-600 mt-1">Impact: {alert.estimatedImpact}</p>
                  </div>
                  <div className="flex space-x-2">
                    {alert.status === 'active' && (
                      <button
                        onClick={() => acknowledgeAlert(alert.id, 'Operations Team')}
                        className="px-3 py-1 bg-yellow-600 text-white text-sm rounded hover:bg-yellow-700"
                      >
                        Acknowledge
                      </button>
                    )}
                    <button
                      onClick={() => resolveAlert(alert.id)}
                      className="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700"
                    >
                      Resolve
                    </button>
                  </div>
                </div>
                
                {alert.actions.length > 0 && (
                  <div>
                    <h4 className="font-medium text-gray-900 mb-2">Action Items</h4>
                    <div className="space-y-2">
                      {alert.actions.map((action, i) => (
                        <div key={i} className="flex items-center justify-between bg-gray-50 rounded p-2">
                          <div>
                            <span className="text-sm font-medium">{action.description}</span>
                            <span className="text-xs text-gray-500 ml-2">({action.assignee})</span>
                          </div>
                          <div className="flex items-center space-x-2">
                            <span className="text-xs text-gray-500">{action.estimatedTime}min</span>
                            <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(action.status)}`}>
                              {action.status.replace('_', ' ')}
                            </span>
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                )}
                
                <div className="text-xs text-gray-500 mt-4">
                  <span>Created: {new Date(alert.timestamp).toLocaleString()}</span>
                  {alert.assignee && <span className="ml-4">Assigned to: {alert.assignee}</span>}
                  {alert.affectedUsers > 0 && <span className="ml-4">Affected users: {alert.affectedUsers}</span>}
                </div>
              </div>
            ))
          ) : (
            <div className="bg-white rounded-lg shadow p-6 text-center">
              <p className="text-gray-500">No active alerts</p>
              <p className="text-green-600 font-medium mt-2">🎉 All systems operational!</p>
            </div>
          )}
        </div>
      )}

      {selectedTab === 'capacity' && (
        <div className="space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {capacityForecasts.map((forecast, index) => (
              <div key={index} className="bg-white rounded-lg shadow p-6">
                <h3 className="font-semibold text-gray-900 mb-4">{forecast.component}</h3>
                
                <div className="space-y-3">
                  <div>
                    <div className="flex justify-between text-sm mb-1">
                      <span>Current Utilization</span>
                      <span>{forecast.currentUtilization}%</span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-2">
                      <div 
                        className={`h-2 rounded-full ${getResourceUsageColor(forecast.currentUtilization)}`}
                        style={{ width: `${forecast.currentUtilization}%` }}
                      ></div>
                    </div>
                  </div>
                  
                  <div>
                    <div className="flex justify-between text-sm mb-1">
                      <span>Projected Utilization</span>
                      <span>{forecast.projectedUtilization}%</span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-2">
                      <div 
                        className={`h-2 rounded-full ${getResourceUsageColor(forecast.projectedUtilization)}`}
                        style={{ width: `${forecast.projectedUtilization}%` }}
                      ></div>
                    </div>
                  </div>
                  
                  <div className="pt-2 border-t">
                    <div className="flex justify-between text-sm">
                      <span className="text-gray-600">Time to Capacity:</span>
                      <span className={`font-medium ${
                        forecast.timeToCapacity < 14 ? 'text-red-600' :
                        forecast.timeToCapacity < 30 ? 'text-yellow-600' :
                        'text-green-600'
                      }`}>
                        {forecast.timeToCapacity} days
                      </span>
                    </div>
                    <div className="flex justify-between text-sm mt-1">
                      <span className="text-gray-600">Confidence:</span>
                      <span className="font-medium">{forecast.confidence}%</span>
                    </div>
                  </div>
                </div>
                
                <div className="mt-4 p-3 bg-blue-50 rounded">
                  <h4 className="text-sm font-medium text-blue-900 mb-1">Recommended Action</h4>
                  <p className="text-sm text-blue-700">{forecast.recommendedAction}</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {selectedTab === 'automation' && (
        <div className="space-y-4">
          {automationRules.map((rule, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-6">
              <div className="flex justify-between items-start mb-4">
                <div>
                  <h3 className="font-semibold text-gray-900">{rule.name}</h3>
                  <div className="mt-2 space-y-1">
                    <p className="text-sm text-gray-600"><strong>Trigger:</strong> {rule.trigger}</p>
                    <p className="text-sm text-gray-600"><strong>Condition:</strong> {rule.condition}</p>
                    <p className="text-sm text-gray-600"><strong>Action:</strong> {rule.action}</p>
                  </div>
                </div>
                <div className="flex space-x-2">
                  <span className={`px-2 py-1 text-xs rounded-full ${
                    rule.enabled ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                  }`}>
                    {rule.enabled ? 'Enabled' : 'Disabled'}
                  </span>
                  <button
                    onClick={() => toggleAutomationRule(rule.id)}
                    className={`px-3 py-1 text-sm rounded transition-colors ${
                      rule.enabled 
                        ? 'bg-red-600 text-white hover:bg-red-700' 
                        : 'bg-green-600 text-white hover:bg-green-700'
                    }`}
                  >
                    {rule.enabled ? 'Disable' : 'Enable'}
                  </button>
                </div>
              </div>
              
              <div className="grid grid-cols-3 gap-4 text-sm">
                <div>
                  <span className="text-gray-600">Executions:</span>
                  <p className="font-medium">{rule.executionCount}</p>
                </div>
                <div>
                  <span className="text-gray-600">Success Rate:</span>
                  <p className="font-medium text-green-600">{rule.successRate.toFixed(1)}%</p>
                </div>
                <div>
                  <span className="text-gray-600">Last Triggered:</span>
                  <p className="font-medium">
                    {rule.lastTriggered ? new Date(rule.lastTriggered).toLocaleDateString() : 'Never'}
                  </p>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'logs' && (
        <div className="bg-white rounded-lg shadow">
          <div className="p-4 border-b">
            <h3 className="text-lg font-semibold text-gray-900">Recent Incident Logs</h3>
          </div>
          <div className="divide-y max-h-96 overflow-y-auto">
            {incidentLogs.slice(0, 50).map((log, index) => (
              <div key={index} className="p-4 flex justify-between items-center">
                <div className="flex items-center space-x-3">
                  <span className={`px-2 py-1 text-xs rounded-full ${getSeverityColor(log.severity)}`}>
                    {log.severity}
                  </span>
                  <span className="text-sm font-medium text-gray-900">{log.component}</span>
                  <span className="text-sm text-gray-600">{log.message}</span>
                </div>
                <div className="flex items-center space-x-4 text-sm text-gray-500">
                  <span className={`px-2 py-1 rounded-full text-xs ${
                    log.resolved ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                  }`}>
                    {log.resolved ? 'Resolved' : 'Open'}
                  </span>
                  <span>{new Date(log.timestamp).toLocaleTimeString()}</span>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
};

export default OperationsCommandCenter; 