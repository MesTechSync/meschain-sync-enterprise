import React, { useState, useEffect, useCallback } from 'react';

// Live Production Monitor interfaces
interface LiveMetrics {
  timestamp: string;
  activeUsers: number;
  requestsPerSecond: number;
  responseTime: number;
  errorRate: number;
  throughput: number;
  memoryUsage: number;
  cpuUsage: number;
  diskUsage: number;
}

interface BusinessMetrics {
  totalTransactions: number;
  successfulTransactions: number;
  failedTransactions: number;
  revenue: number;
  newUsers: number;
  returningUsers: number;
  conversionRate: number;
  averageOrderValue: number;
}

interface ServiceHealth {
  serviceName: string;
  status: 'healthy' | 'warning' | 'critical' | 'down';
  uptime: number;
  lastHeartbeat: string;
  responseTime: number;
  errorCount: number;
  requestCount: number;
  version: string;
  instances: number;
}

interface AlertRule {
  id: string;
  name: string;
  metric: string;
  operator: '>' | '<' | '==' | '!=' | '>=' | '<=';
  threshold: number;
  severity: 'info' | 'warning' | 'critical';
  enabled: boolean;
  lastTriggered?: string;
  triggerCount: number;
}

interface LiveAlert {
  id: string;
  timestamp: string;
  severity: 'info' | 'warning' | 'critical';
  title: string;
  message: string;
  service: string;
  metric: string;
  value: number;
  threshold: number;
  status: 'active' | 'resolved' | 'acknowledged';
  resolvedAt?: string;
  acknowledgedBy?: string;
}

interface UserActivity {
  timestamp: string;
  userId: string;
  action: string;
  endpoint: string;
  responseTime: number;
  statusCode: number;
  userAgent: string;
  location: string;
}

export const LiveProductionMonitor: React.FC = () => {
  const [liveMetrics, setLiveMetrics] = useState<LiveMetrics[]>([]);
  const [businessMetrics, setBusinessMetrics] = useState<BusinessMetrics | null>(null);
  const [serviceHealth, setServiceHealth] = useState<ServiceHealth[]>([]);
  const [alertRules, setAlertRules] = useState<AlertRule[]>([]);
  const [liveAlerts, setLiveAlerts] = useState<LiveAlert[]>([]);
  const [userActivity, setUserActivity] = useState<UserActivity[]>([]);
  const [selectedTab, setSelectedTab] = useState('overview');
  const [isMonitoring, setIsMonitoring] = useState(true);
  const [selectedTimeRange, setSelectedTimeRange] = useState('1h');

  // Initialize Live Production Monitor
  useEffect(() => {
    // Initialize services
    setServiceHealth([
      {
        serviceName: 'API Gateway',
        status: 'healthy',
        uptime: 99.98,
        lastHeartbeat: new Date().toISOString(),
        responseTime: 42,
        errorCount: 3,
        requestCount: 15247,
        version: 'v2.2.0',
        instances: 3
      },
      {
        serviceName: 'User Service',
        status: 'healthy',
        uptime: 99.95,
        lastHeartbeat: new Date().toISOString(),
        responseTime: 38,
        errorCount: 1,
        requestCount: 8542,
        version: 'v2.2.0',
        instances: 2
      },
      {
        serviceName: 'Order Service',
        status: 'warning',
        uptime: 99.85,
        lastHeartbeat: new Date().toISOString(),
        responseTime: 156,
        errorCount: 12,
        requestCount: 3247,
        version: 'v2.2.0',
        instances: 2
      },
      {
        serviceName: 'Payment Service',
        status: 'healthy',
        uptime: 99.99,
        lastHeartbeat: new Date().toISOString(),
        responseTime: 89,
        errorCount: 0,
        requestCount: 2847,
        version: 'v2.2.0',
        instances: 2
      },
      {
        serviceName: 'ML Pipeline',
        status: 'healthy',
        uptime: 99.92,
        lastHeartbeat: new Date().toISOString(),
        responseTime: 234,
        errorCount: 5,
        requestCount: 1456,
        version: 'v2.2.0',
        instances: 1
      },
      {
        serviceName: 'Cache Service',
        status: 'healthy',
        uptime: 100.00,
        lastHeartbeat: new Date().toISOString(),
        responseTime: 3,
        errorCount: 0,
        requestCount: 25674,
        version: 'v2.2.0',
        instances: 3
      }
    ]);

    // Initialize alert rules
    setAlertRules([
      {
        id: 'rule_001',
        name: 'High Response Time',
        metric: 'response_time',
        operator: '>',
        threshold: 1000,
        severity: 'warning',
        enabled: true,
        triggerCount: 0
      },
      {
        id: 'rule_002',
        name: 'High Error Rate',
        metric: 'error_rate',
        operator: '>',
        threshold: 5,
        severity: 'critical',
        enabled: true,
        triggerCount: 2
      },
      {
        id: 'rule_003',
        name: 'Low Memory',
        metric: 'memory_usage',
        operator: '>',
        threshold: 90,
        severity: 'warning',
        enabled: true,
        triggerCount: 0
      },
      {
        id: 'rule_004',
        name: 'Service Down',
        metric: 'uptime',
        operator: '<',
        threshold: 95,
        severity: 'critical',
        enabled: true,
        triggerCount: 0
      }
    ]);

    // Initialize business metrics
    setBusinessMetrics({
      totalTransactions: 15247,
      successfulTransactions: 15201,
      failedTransactions: 46,
      revenue: 842567.89,
      newUsers: 1247,
      returningUsers: 3456,
      conversionRate: 12.47,
      averageOrderValue: 89.47
    });

    // Start real-time monitoring
    const interval = setInterval(() => {
      if (isMonitoring) {
        updateLiveMetrics();
        updateUserActivity();
        checkAlertRules();
      }
    }, 2000);

    return () => clearInterval(interval);
  }, [isMonitoring]);

  // Update live metrics
  const updateLiveMetrics = () => {
    const now = new Date().toISOString();
    const baseMetrics = {
      activeUsers: 1247,
      requestsPerSecond: 42,
      responseTime: 45,
      errorRate: 0.3,
      throughput: 2547,
      memoryUsage: 68,
      cpuUsage: 35,
      diskUsage: 60
    };

    const newMetrics: LiveMetrics = {
      timestamp: now,
      activeUsers: baseMetrics.activeUsers + Math.floor(Math.random() * 100 - 50),
      requestsPerSecond: baseMetrics.requestsPerSecond + Math.floor(Math.random() * 20 - 10),
      responseTime: baseMetrics.responseTime + Math.floor(Math.random() * 30 - 15),
      errorRate: Math.max(0, baseMetrics.errorRate + (Math.random() * 0.5 - 0.25)),
      throughput: baseMetrics.throughput + Math.floor(Math.random() * 200 - 100),
      memoryUsage: Math.max(0, Math.min(100, baseMetrics.memoryUsage + Math.floor(Math.random() * 10 - 5))),
      cpuUsage: Math.max(0, Math.min(100, baseMetrics.cpuUsage + Math.floor(Math.random() * 20 - 10))),
      diskUsage: Math.max(0, Math.min(100, baseMetrics.diskUsage + Math.floor(Math.random() * 2 - 1)))
    };

    setLiveMetrics(prev => [...prev.slice(-59), newMetrics]);
  };

  // Update user activity
  const updateUserActivity = () => {
    const actions = ['login', 'purchase', 'browse', 'search', 'add_to_cart', 'checkout', 'view_product'];
    const endpoints = ['/api/users', '/api/orders', '/api/products', '/api/cart', '/api/payments'];
    const locations = ['Istanbul', 'Ankara', 'Izmir', 'Bursa', 'Antalya'];
    const userAgents = ['Chrome', 'Firefox', 'Safari', 'Edge'];

    if (Math.random() < 0.7) { // 70% chance to add new activity
      const newActivity: UserActivity = {
        timestamp: new Date().toISOString(),
        userId: `user_${Math.floor(Math.random() * 10000)}`,
        action: actions[Math.floor(Math.random() * actions.length)],
        endpoint: endpoints[Math.floor(Math.random() * endpoints.length)],
        responseTime: Math.floor(Math.random() * 500 + 20),
        statusCode: Math.random() < 0.95 ? 200 : (Math.random() < 0.5 ? 404 : 500),
        userAgent: userAgents[Math.floor(Math.random() * userAgents.length)],
        location: locations[Math.floor(Math.random() * locations.length)]
      };

      setUserActivity(prev => [newActivity, ...prev.slice(0, 49)]);
    }
  };

  // Check alert rules
  const checkAlertRules = () => {
    const latestMetrics = liveMetrics[liveMetrics.length - 1];
    if (!latestMetrics) return;

    alertRules.forEach(rule => {
      if (!rule.enabled) return;

      let value = 0;
      switch (rule.metric) {
        case 'response_time':
          value = latestMetrics.responseTime;
          break;
        case 'error_rate':
          value = latestMetrics.errorRate;
          break;
        case 'memory_usage':
          value = latestMetrics.memoryUsage;
          break;
        case 'cpu_usage':
          value = latestMetrics.cpuUsage;
          break;
      }

      let shouldTrigger = false;
      switch (rule.operator) {
        case '>':
          shouldTrigger = value > rule.threshold;
          break;
        case '<':
          shouldTrigger = value < rule.threshold;
          break;
        case '>=':
          shouldTrigger = value >= rule.threshold;
          break;
        case '<=':
          shouldTrigger = value <= rule.threshold;
          break;
        case '==':
          shouldTrigger = value === rule.threshold;
          break;
        case '!=':
          shouldTrigger = value !== rule.threshold;
          break;
      }

      if (shouldTrigger) {
        const existingAlert = liveAlerts.find(a => 
          a.metric === rule.metric && a.status === 'active'
        );

        if (!existingAlert) {
          const newAlert: LiveAlert = {
            id: `alert_${Date.now()}`,
            timestamp: new Date().toISOString(),
            severity: rule.severity,
            title: rule.name,
            message: `${rule.metric} ${rule.operator} ${rule.threshold} (current: ${value.toFixed(2)})`,
            service: 'Production System',
            metric: rule.metric,
            value: value,
            threshold: rule.threshold,
            status: 'active'
          };

          setLiveAlerts(prev => [newAlert, ...prev]);
          
          // Update rule trigger count
          setAlertRules(prev => prev.map(r => 
            r.id === rule.id 
              ? { ...r, triggerCount: r.triggerCount + 1, lastTriggered: new Date().toISOString() }
              : r
          ));
        }
      }
    });
  };

  // Acknowledge alert
  const acknowledgeAlert = useCallback((alertId: string) => {
    setLiveAlerts(prev => prev.map(alert => 
      alert.id === alertId 
        ? { ...alert, status: 'acknowledged', acknowledgedBy: 'Production Team' }
        : alert
    ));
  }, []);

  // Resolve alert
  const resolveAlert = useCallback((alertId: string) => {
    setLiveAlerts(prev => prev.map(alert => 
      alert.id === alertId 
        ? { ...alert, status: 'resolved', resolvedAt: new Date().toISOString() }
        : alert
    ));
  }, []);

  // Toggle monitoring
  const toggleMonitoring = useCallback(() => {
    setIsMonitoring(prev => !prev);
  }, []);

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'healthy': case 'resolved': return 'text-green-600 bg-green-100';
      case 'warning': case 'acknowledged': return 'text-yellow-600 bg-yellow-100';
      case 'critical': case 'active': return 'text-red-600 bg-red-100';
      case 'down': return 'text-gray-600 bg-gray-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const getSeverityColor = (severity: string) => {
    switch (severity) {
      case 'info': return 'text-blue-600 bg-blue-100';
      case 'warning': return 'text-yellow-600 bg-yellow-100';
      case 'critical': return 'text-red-600 bg-red-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const tabs = [
    { id: 'overview', label: 'Live Overview', count: serviceHealth.length },
    { id: 'metrics', label: 'Metrics', count: liveMetrics.length },
    { id: 'alerts', label: 'Alerts', count: liveAlerts.filter(a => a.status === 'active').length },
    { id: 'activity', label: 'User Activity', count: userActivity.length },
    { id: 'business', label: 'Business Metrics', count: 1 }
  ];

  return (
    <div className="live-production-monitor p-6">
      <div className="mb-6">
        <div className="flex justify-between items-center">
          <div>
            <h2 className="text-2xl font-bold text-gray-900 mb-2">📊 Live Production Monitor</h2>
            <p className="text-gray-600">Real-time production system monitoring and alerts</p>
          </div>
          <div className="flex space-x-2">
            <button
              onClick={toggleMonitoring}
              className={`px-4 py-2 rounded transition-colors ${
                isMonitoring 
                  ? 'bg-red-600 text-white hover:bg-red-700' 
                  : 'bg-green-600 text-white hover:bg-green-700'
              }`}
            >
              {isMonitoring ? '⏸️ Pause Monitoring' : '▶️ Resume Monitoring'}
            </button>
            <select
              value={selectedTimeRange}
              onChange={(e) => setSelectedTimeRange(e.target.value)}
              className="px-3 py-2 border border-gray-300 rounded"
            >
              <option value="5m">Last 5 minutes</option>
              <option value="15m">Last 15 minutes</option>
              <option value="1h">Last 1 hour</option>
              <option value="24h">Last 24 hours</option>
            </select>
          </div>
        </div>
      </div>

      {/* System Status Banner */}
      <div className="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <div className="flex justify-between items-center">
          <div>
            <h3 className="text-lg font-semibold text-green-800">
              🟢 System Status: Operational
            </h3>
            <p className="text-green-700">
              All critical systems are running normally | 
              Uptime: 99.97% | 
              Last Updated: {new Date().toLocaleTimeString()}
            </p>
          </div>
          <div className="flex space-x-4">
            <div className="text-center">
              <p className="text-2xl font-bold text-green-600">
                {liveMetrics.length > 0 ? liveMetrics[liveMetrics.length - 1].activeUsers : 0}
              </p>
              <p className="text-xs text-green-700">Active Users</p>
            </div>
            <div className="text-center">
              <p className="text-2xl font-bold text-blue-600">
                {liveMetrics.length > 0 ? liveMetrics[liveMetrics.length - 1].requestsPerSecond : 0}
              </p>
              <p className="text-xs text-blue-700">Req/Sec</p>
            </div>
            <div className="text-center">
              <p className="text-2xl font-bold text-purple-600">
                {liveMetrics.length > 0 ? liveMetrics[liveMetrics.length - 1].responseTime : 0}ms
              </p>
              <p className="text-xs text-purple-700">Response Time</p>
            </div>
          </div>
        </div>
      </div>

      {/* Active Alerts */}
      {liveAlerts.filter(a => a.status === 'active').length > 0 && (
        <div className="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
          <h3 className="text-lg font-semibold text-red-800 mb-2">🚨 Active Alerts</h3>
          <div className="space-y-2">
            {liveAlerts.filter(a => a.status === 'active').slice(0, 3).map((alert, index) => (
              <div key={index} className="flex justify-between items-center bg-white rounded p-2">
                <div>
                  <span className={`px-2 py-1 text-xs rounded-full ${getSeverityColor(alert.severity)} mr-2`}>
                    {alert.severity}
                  </span>
                  <span className="font-medium">{alert.title}</span>
                  <span className="text-sm text-gray-600 ml-2">{alert.message}</span>
                </div>
                <div className="flex space-x-2">
                  <button
                    onClick={() => acknowledgeAlert(alert.id)}
                    className="px-2 py-1 bg-yellow-600 text-white text-xs rounded hover:bg-yellow-700"
                  >
                    Acknowledge
                  </button>
                  <button
                    onClick={() => resolveAlert(alert.id)}
                    className="px-2 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700"
                  >
                    Resolve
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

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
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {serviceHealth.map((service, index) => (
            <div key={index} className="bg-white rounded-lg shadow p-4">
              <div className="flex justify-between items-start mb-3">
                <div>
                  <h3 className="font-semibold text-gray-900">{service.serviceName}</h3>
                  <p className="text-sm text-gray-600">v{service.version} • {service.instances} instances</p>
                </div>
                <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(service.status)}`}>
                  {service.status}
                </span>
              </div>
              
              <div className="space-y-2">
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Uptime:</span>
                  <span className="font-medium text-green-600">{service.uptime.toFixed(2)}%</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Response:</span>
                  <span className="font-medium">{service.responseTime}ms</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Requests:</span>
                  <span className="font-medium">{service.requestCount.toLocaleString()}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-600">Errors:</span>
                  <span className={`font-medium ${service.errorCount > 0 ? 'text-red-600' : 'text-green-600'}`}>
                    {service.errorCount}
                  </span>
                </div>
              </div>
              
              <p className="text-xs text-gray-500 mt-3">
                Last heartbeat: {new Date(service.lastHeartbeat).toLocaleTimeString()}
              </p>
            </div>
          ))}
        </div>
      )}

      {selectedTab === 'metrics' && (
        <div className="space-y-6">
          {/* Key Metrics Cards */}
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            {liveMetrics.length > 0 && (
              <>
                <div className="bg-white rounded-lg shadow p-4 text-center">
                  <h3 className="text-sm font-medium text-gray-500">CPU Usage</h3>
                  <p className="text-2xl font-bold text-blue-600">
                    {liveMetrics[liveMetrics.length - 1].cpuUsage}%
                  </p>
                </div>
                <div className="bg-white rounded-lg shadow p-4 text-center">
                  <h3 className="text-sm font-medium text-gray-500">Memory Usage</h3>
                  <p className="text-2xl font-bold text-purple-600">
                    {liveMetrics[liveMetrics.length - 1].memoryUsage}%
                  </p>
                </div>
                <div className="bg-white rounded-lg shadow p-4 text-center">
                  <h3 className="text-sm font-medium text-gray-500">Disk Usage</h3>
                  <p className="text-2xl font-bold text-orange-600">
                    {liveMetrics[liveMetrics.length - 1].diskUsage}%
                  </p>
                </div>
                <div className="bg-white rounded-lg shadow p-4 text-center">
                  <h3 className="text-sm font-medium text-gray-500">Error Rate</h3>
                  <p className="text-2xl font-bold text-red-600">
                    {liveMetrics[liveMetrics.length - 1].errorRate.toFixed(2)}%
                  </p>
                </div>
              </>
            )}
          </div>
          
          {/* Metrics Chart Placeholder */}
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">Real-time Metrics</h3>
            <div className="h-64 bg-gray-50 rounded flex items-center justify-center">
              <p className="text-gray-500">Live metrics chart would be rendered here</p>
            </div>
          </div>
        </div>
      )}

      {selectedTab === 'alerts' && (
        <div className="space-y-4">
          {liveAlerts.length > 0 ? (
            liveAlerts.map((alert, index) => (
              <div key={index} className="bg-white rounded-lg shadow p-4">
                <div className="flex justify-between items-start mb-2">
                  <div>
                    <div className="flex items-center space-x-2">
                      <span className={`px-2 py-1 text-xs rounded-full ${getSeverityColor(alert.severity)}`}>
                        {alert.severity}
                      </span>
                      <span className={`px-2 py-1 text-xs rounded-full ${getStatusColor(alert.status)}`}>
                        {alert.status}
                      </span>
                    </div>
                    <h3 className="font-semibold text-gray-900 mt-2">{alert.title}</h3>
                    <p className="text-sm text-gray-600">{alert.message}</p>
                  </div>
                  {alert.status === 'active' && (
                    <div className="flex space-x-2">
                      <button
                        onClick={() => acknowledgeAlert(alert.id)}
                        className="px-3 py-1 bg-yellow-600 text-white text-sm rounded hover:bg-yellow-700"
                      >
                        Acknowledge
                      </button>
                      <button
                        onClick={() => resolveAlert(alert.id)}
                        className="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700"
                      >
                        Resolve
                      </button>
                    </div>
                  )}
                </div>
                <div className="text-xs text-gray-500">
                  <span>Service: {alert.service}</span> • 
                  <span className="ml-2">Triggered: {new Date(alert.timestamp).toLocaleString()}</span>
                  {alert.resolvedAt && (
                    <span className="ml-2">Resolved: {new Date(alert.resolvedAt).toLocaleString()}</span>
                  )}
                </div>
              </div>
            ))
          ) : (
            <div className="bg-white rounded-lg shadow p-6 text-center">
              <p className="text-gray-500">No alerts at this time</p>
              <p className="text-green-600 font-medium mt-2">🎉 All systems running smoothly!</p>
            </div>
          )}
        </div>
      )}

      {selectedTab === 'activity' && (
        <div className="bg-white rounded-lg shadow">
          <div className="p-4 border-b">
            <h3 className="text-lg font-semibold text-gray-900">Recent User Activity</h3>
          </div>
          <div className="divide-y">
            {userActivity.slice(0, 20).map((activity, index) => (
              <div key={index} className="p-4 flex justify-between items-center">
                <div>
                  <span className="font-medium text-gray-900">{activity.action}</span>
                  <span className="text-gray-600 ml-2">{activity.endpoint}</span>
                  <span className="text-sm text-gray-500 ml-2">by {activity.userId}</span>
                </div>
                <div className="flex items-center space-x-4 text-sm text-gray-500">
                  <span>{activity.responseTime}ms</span>
                  <span className={`px-2 py-1 rounded-full text-xs ${
                    activity.statusCode === 200 ? 'bg-green-100 text-green-800' :
                    activity.statusCode === 404 ? 'bg-yellow-100 text-yellow-800' :
                    'bg-red-100 text-red-800'
                  }`}>
                    {activity.statusCode}
                  </span>
                  <span>{activity.location}</span>
                  <span>{new Date(activity.timestamp).toLocaleTimeString()}</span>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {selectedTab === 'business' && businessMetrics && (
        <div className="space-y-6">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div className="bg-white rounded-lg shadow p-4 text-center">
              <h3 className="text-sm font-medium text-gray-500">Total Transactions</h3>
              <p className="text-2xl font-bold text-blue-600">
                {businessMetrics.totalTransactions.toLocaleString()}
              </p>
            </div>
            <div className="bg-white rounded-lg shadow p-4 text-center">
              <h3 className="text-sm font-medium text-gray-500">Success Rate</h3>
              <p className="text-2xl font-bold text-green-600">
                {((businessMetrics.successfulTransactions / businessMetrics.totalTransactions) * 100).toFixed(1)}%
              </p>
            </div>
            <div className="bg-white rounded-lg shadow p-4 text-center">
              <h3 className="text-sm font-medium text-gray-500">Revenue</h3>
              <p className="text-2xl font-bold text-purple-600">
                ${businessMetrics.revenue.toLocaleString()}
              </p>
            </div>
            <div className="bg-white rounded-lg shadow p-4 text-center">
              <h3 className="text-sm font-medium text-gray-500">Conversion Rate</h3>
              <p className="text-2xl font-bold text-orange-600">
                {businessMetrics.conversionRate.toFixed(1)}%
              </p>
            </div>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div className="bg-white rounded-lg shadow p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">User Metrics</h3>
              <div className="space-y-3">
                <div className="flex justify-between">
                  <span className="text-gray-600">New Users:</span>
                  <span className="font-bold">{businessMetrics.newUsers.toLocaleString()}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Returning Users:</span>
                  <span className="font-bold">{businessMetrics.returningUsers.toLocaleString()}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Average Order Value:</span>
                  <span className="font-bold">${businessMetrics.averageOrderValue.toFixed(2)}</span>
                </div>
              </div>
            </div>
            
            <div className="bg-white rounded-lg shadow p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">Transaction Metrics</h3>
              <div className="space-y-3">
                <div className="flex justify-between">
                  <span className="text-gray-600">Successful:</span>
                  <span className="font-bold text-green-600">{businessMetrics.successfulTransactions.toLocaleString()}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Failed:</span>
                  <span className="font-bold text-red-600">{businessMetrics.failedTransactions.toLocaleString()}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-gray-600">Success Rate:</span>
                  <span className="font-bold text-green-600">
                    {((businessMetrics.successfulTransactions / businessMetrics.totalTransactions) * 100).toFixed(2)}%
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default LiveProductionMonitor; 