import React, { useState, useEffect, useCallback } from 'react';
import { 
  Activity, 
  Zap, 
  Server, 
  Globe, 
  AlertTriangle, 
  CheckCircle,
  Clock, 
  TrendingUp, 
  TrendingDown,
  BarChart3, 
  Cpu,
  Database,
  Wifi,
  Shield,
  Users,
  Package,
  DollarSign,
  RefreshCcw,
  Settings
} from 'lucide-react';

// Types and Interfaces
interface SystemMetrics {
  cpu: number;
  memory: number;
  disk: number;
  network: number;
  uptime: string;
  activeUsers: number;
  totalRequests: number;
  errorRate: number;
}

interface APIStatus {
  endpoint: string;
  status: 'online' | 'offline' | 'degraded';
  responseTime: number;
  lastChecked: string;
  requests24h: number;
  errors24h: number;
}

interface MarketplaceHealth {
  marketplace: string;
  status: 'healthy' | 'warning' | 'critical';
  orders: number;
  revenue: number;
  apiCalls: number;
  errors: number;
  lastSync: string;
}

interface Alert {
  id: string;
  type: 'critical' | 'warning' | 'info';
  message: string;
  timestamp: string;
  source: string;
  resolved: boolean;
}

const RealTimeMonitoringDashboard: React.FC = () => {
  const [isLoading, setIsLoading] = useState(false);
  const [lastUpdate, setLastUpdate] = useState<Date>(new Date());
  const [autoRefresh, setAutoRefresh] = useState(true);
  const [refreshInterval, setRefreshInterval] = useState(5); // seconds
  
  // State for monitoring data
  const [systemMetrics, setSystemMetrics] = useState<SystemMetrics>({
    cpu: 0,
    memory: 0,
    disk: 0,
    network: 0,
    uptime: '0h 0m',
    activeUsers: 0,
    totalRequests: 0,
    errorRate: 0
  });
  
  const [apiStatuses, setApiStatuses] = useState<APIStatus[]>([]);
  const [marketplaceHealth, setMarketplaceHealth] = useState<MarketplaceHealth[]>([]);
  const [alerts, setAlerts] = useState<Alert[]>([]);

  // Sample data generation
  const generateSystemMetrics = useCallback((): SystemMetrics => ({
    cpu: Math.random() * 100,
    memory: Math.random() * 100,
    disk: Math.random() * 100,
    network: Math.random() * 100,
    uptime: '15d 7h 23m',
    activeUsers: Math.floor(Math.random() * 50) + 10,
    totalRequests: Math.floor(Math.random() * 10000) + 50000,
    errorRate: Math.random() * 5
  }), []);

  const generateApiStatuses = useCallback((): APIStatus[] => [
    {
      endpoint: 'Trendyol API',
      status: Math.random() > 0.8 ? 'offline' : Math.random() > 0.9 ? 'degraded' : 'online',
      responseTime: Math.random() * 1000 + 100,
      lastChecked: new Date().toISOString(),
      requests24h: Math.floor(Math.random() * 5000) + 1000,
      errors24h: Math.floor(Math.random() * 50)
    },
    {
      endpoint: 'Hepsiburada API',
      status: Math.random() > 0.85 ? 'degraded' : 'online',
      responseTime: Math.random() * 800 + 150,
      lastChecked: new Date().toISOString(),
      requests24h: Math.floor(Math.random() * 3000) + 800,
      errors24h: Math.floor(Math.random() * 30)
    },
    {
      endpoint: 'Amazon SP-API',
      status: Math.random() > 0.9 ? 'offline' : 'online',
      responseTime: Math.random() * 1200 + 200,
      lastChecked: new Date().toISOString(),
      requests24h: Math.floor(Math.random() * 2000) + 500,
      errors24h: Math.floor(Math.random() * 20)
    },
    {
      endpoint: 'eBay Trading API',
      status: 'online',
      responseTime: Math.random() * 600 + 120,
      lastChecked: new Date().toISOString(),
      requests24h: Math.floor(Math.random() * 1500) + 300,
      errors24h: Math.floor(Math.random() * 10)
    }
  ], []);

  const generateMarketplaceHealth = useCallback((): MarketplaceHealth[] => [
    {
      marketplace: 'Trendyol',
      status: Math.random() > 0.8 ? 'warning' : 'healthy',
      orders: Math.floor(Math.random() * 100) + 50,
      revenue: Math.random() * 10000 + 5000,
      apiCalls: Math.floor(Math.random() * 1000) + 500,
      errors: Math.floor(Math.random() * 10),
      lastSync: new Date(Date.now() - Math.random() * 60000).toISOString()
    },
    {
      marketplace: 'Hepsiburada',
      status: 'healthy',
      orders: Math.floor(Math.random() * 80) + 30,
      revenue: Math.random() * 8000 + 3000,
      apiCalls: Math.floor(Math.random() * 800) + 300,
      errors: Math.floor(Math.random() * 5),
      lastSync: new Date(Date.now() - Math.random() * 30000).toISOString()
    },
    {
      marketplace: 'Amazon',
      status: Math.random() > 0.9 ? 'critical' : 'healthy',
      orders: Math.floor(Math.random() * 120) + 70,
      revenue: Math.random() * 15000 + 8000,
      apiCalls: Math.floor(Math.random() * 1200) + 600,
      errors: Math.floor(Math.random() * 15),
      lastSync: new Date(Date.now() - Math.random() * 120000).toISOString()
    }
  ], []);

  const generateAlerts = useCallback((): Alert[] => [
    {
      id: '1',
      type: 'critical',
      message: 'High error rate detected on Trendyol API (5.2%)',
      timestamp: new Date(Date.now() - 300000).toISOString(),
      source: 'API Monitor',
      resolved: false
    },
    {
      id: '2',
      type: 'warning',
      message: 'Memory usage above 80% threshold',
      timestamp: new Date(Date.now() - 600000).toISOString(),
      source: 'System Monitor',
      resolved: false
    },
    {
      id: '3',
      type: 'info',
      message: 'Amazon sync completed successfully',
      timestamp: new Date(Date.now() - 900000).toISOString(),
      source: 'Marketplace Sync',
      resolved: true
    }
  ], []);

  // Fetch monitoring data
  const fetchMonitoringData = useCallback(async () => {
    setIsLoading(true);
    try {
      // In production, these would be real API calls
      await new Promise(resolve => setTimeout(resolve, 500));
      
      setSystemMetrics(generateSystemMetrics());
      setApiStatuses(generateApiStatuses());
      setMarketplaceHealth(generateMarketplaceHealth());
      setAlerts(generateAlerts());
      setLastUpdate(new Date());
    } catch (error) {
      console.error('Error fetching monitoring data:', error);
    } finally {
      setIsLoading(false);
    }
  }, [generateSystemMetrics, generateApiStatuses, generateMarketplaceHealth, generateAlerts]);

  // Auto-refresh
  useEffect(() => {
    if (autoRefresh) {
      const interval = setInterval(fetchMonitoringData, refreshInterval * 1000);
      return () => clearInterval(interval);
    }
  }, [autoRefresh, refreshInterval, fetchMonitoringData]);

  // Initial load
  useEffect(() => {
    fetchMonitoringData();
  }, [fetchMonitoringData]);

  // Utility functions
  const getStatusColor = (status: string) => {
    const colors = {
      online: 'text-green-600 bg-green-100',
      healthy: 'text-green-600 bg-green-100',
      offline: 'text-red-600 bg-red-100',
      critical: 'text-red-600 bg-red-100',
      degraded: 'text-yellow-600 bg-yellow-100',
      warning: 'text-yellow-600 bg-yellow-100'
    };
    return colors[status as keyof typeof colors] || 'text-gray-600 bg-gray-100';
  };

  const getAlertIcon = (type: string) => {
    switch (type) {
      case 'critical': return <AlertTriangle className="w-4 h-4 text-red-500" />;
      case 'warning': return <AlertTriangle className="w-4 h-4 text-yellow-500" />;
      case 'info': return <CheckCircle className="w-4 h-4 text-blue-500" />;
      default: return <Activity className="w-4 h-4 text-gray-500" />;
    }
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('tr-TR', {
      style: 'currency',
      currency: 'TRY'
    }).format(amount);
  };

  const formatResponseTime = (ms: number) => {
    if (ms < 1000) return `${Math.round(ms)}ms`;
    return `${(ms / 1000).toFixed(1)}s`;
  };

  // Component render functions
  const renderHeader = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
      <div className="px-6 py-4 border-b border-gray-200">
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-3">
            <div className="p-2 bg-blue-100 rounded-lg">
              <Activity className="w-6 h-6 text-blue-600" />
            </div>
            <div>
              <h1 className="text-2xl font-bold text-gray-900">Real-Time System Monitoring</h1>
              <p className="text-sm text-gray-600">Live system health & performance dashboard</p>
            </div>
          </div>
          
          <div className="flex items-center space-x-4">
            <div className="flex items-center space-x-2">
              <label className="text-sm text-gray-600">Refresh:</label>
              <select
                value={refreshInterval}
                onChange={(e) => setRefreshInterval(Number(e.target.value))}
                className="px-2 py-1 border border-gray-300 rounded text-sm"
              >
                <option value={5}>5s</option>
                <option value={10}>10s</option>
                <option value={30}>30s</option>
                <option value={60}>1m</option>
              </select>
            </div>
            
            <div className="flex items-center space-x-2">
              <button
                onClick={() => setAutoRefresh(!autoRefresh)}
                className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors ${
                  autoRefresh ? 'bg-blue-600' : 'bg-gray-200'
                }`}
              >
                <span className={`inline-block h-4 w-4 transform rounded-full bg-white transition-transform ${
                  autoRefresh ? 'translate-x-6' : 'translate-x-1'
                }`} />
              </button>
              <span className="text-sm text-gray-600">Auto</span>
            </div>
            
            <button
              onClick={fetchMonitoringData}
              disabled={isLoading}
              className="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
            >
              <RefreshCcw className={`w-4 h-4 ${isLoading ? 'animate-spin' : ''}`} />
              <span>Refresh</span>
            </button>
          </div>
        </div>
        
        <div className="mt-4 text-sm text-gray-500">
          Last updated: {lastUpdate.toLocaleString('tr-TR')} | Next refresh: {refreshInterval}s
        </div>
      </div>
    </div>
  );

  const renderSystemMetrics = () => (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      {[
        {
          title: 'CPU Usage',
          value: `${systemMetrics.cpu.toFixed(1)}%`,
          icon: Cpu,
          color: systemMetrics.cpu > 80 ? 'red' : systemMetrics.cpu > 60 ? 'yellow' : 'green'
        },
        {
          title: 'Memory Usage',
          value: `${systemMetrics.memory.toFixed(1)}%`,
          icon: Database,
          color: systemMetrics.memory > 80 ? 'red' : systemMetrics.memory > 60 ? 'yellow' : 'green'
        },
        {
          title: 'Active Users',
          value: systemMetrics.activeUsers.toString(),
          icon: Users,
          color: 'blue'
        },
        {
          title: 'Error Rate',
          value: `${systemMetrics.errorRate.toFixed(2)}%`,
          icon: AlertTriangle,
          color: systemMetrics.errorRate > 3 ? 'red' : systemMetrics.errorRate > 1 ? 'yellow' : 'green'
        }
      ].map((metric, index) => (
        <div key={index} className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between">
            <div className={`p-2 rounded-lg bg-${metric.color}-100`}>
              <metric.icon className={`w-6 h-6 text-${metric.color}-600`} />
            </div>
          </div>
          <div className="mt-4">
            <div className="text-2xl font-bold text-gray-900">{metric.value}</div>
            <div className="text-sm text-gray-600">{metric.title}</div>
          </div>
        </div>
      ))}
    </div>
  );

  const renderApiStatuses = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
      <div className="px-6 py-4 border-b border-gray-200">
        <h2 className="text-lg font-semibold text-gray-900">API Health Status</h2>
      </div>
      
      <div className="p-6">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {apiStatuses.map((api, index) => (
            <div key={index} className="border border-gray-200 rounded-lg p-4">
              <div className="flex items-center justify-between mb-3">
                <h3 className="font-medium text-gray-900">{api.endpoint}</h3>
                <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(api.status)}`}>
                  {api.status}
                </span>
              </div>
              
              <div className="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <div className="text-gray-500">Response Time</div>
                  <div className="font-medium">{formatResponseTime(api.responseTime)}</div>
                </div>
                <div>
                  <div className="text-gray-500">24h Requests</div>
                  <div className="font-medium">{api.requests24h.toLocaleString()}</div>
                </div>
                <div>
                  <div className="text-gray-500">24h Errors</div>
                  <div className="font-medium text-red-600">{api.errors24h}</div>
                </div>
                <div>
                  <div className="text-gray-500">Last Check</div>
                  <div className="font-medium">{new Date(api.lastChecked).toLocaleTimeString('tr-TR')}</div>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );

  const renderMarketplaceHealth = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
      <div className="px-6 py-4 border-b border-gray-200">
        <h2 className="text-lg font-semibold text-gray-900">Marketplace Health</h2>
      </div>
      
      <div className="overflow-x-auto">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Marketplace
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Orders
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Revenue
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                API Calls
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Last Sync
              </th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {marketplaceHealth.map((marketplace, index) => (
              <tr key={index} className="hover:bg-gray-50">
                <td className="px-6 py-4 whitespace-nowrap">
                  <div className="text-sm font-medium text-gray-900">{marketplace.marketplace}</div>
                </td>
                <td className="px-6 py-4 whitespace-nowrap">
                  <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(marketplace.status)}`}>
                    {marketplace.status}
                  </span>
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {marketplace.orders}
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {formatCurrency(marketplace.revenue)}
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {marketplace.apiCalls} / {marketplace.errors} errors
                </td>
                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {new Date(marketplace.lastSync).toLocaleTimeString('tr-TR')}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );

  const renderAlerts = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200">
      <div className="px-6 py-4 border-b border-gray-200">
        <h2 className="text-lg font-semibold text-gray-900">Recent Alerts</h2>
      </div>
      
      <div className="p-6">
        <div className="space-y-4">
          {alerts.map((alert) => (
            <div key={alert.id} className={`border-l-4 p-4 ${
              alert.type === 'critical' ? 'border-red-500 bg-red-50' :
              alert.type === 'warning' ? 'border-yellow-500 bg-yellow-50' :
              'border-blue-500 bg-blue-50'
            } ${alert.resolved ? 'opacity-60' : ''}`}>
              <div className="flex items-start space-x-3">
                {getAlertIcon(alert.type)}
                <div className="flex-1">
                  <div className="flex items-center justify-between">
                    <p className={`text-sm font-medium ${
                      alert.type === 'critical' ? 'text-red-800' :
                      alert.type === 'warning' ? 'text-yellow-800' :
                      'text-blue-800'
                    }`}>
                      {alert.message}
                    </p>
                    {alert.resolved && (
                      <span className="text-xs text-gray-500 bg-gray-200 px-2 py-1 rounded">
                        Resolved
                      </span>
                    )}
                  </div>
                  <div className="text-xs text-gray-600 mt-1">
                    {alert.source} • {new Date(alert.timestamp).toLocaleString('tr-TR')}
                  </div>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      {renderHeader()}
      {renderSystemMetrics()}
      {renderApiStatuses()}
      {renderMarketplaceHealth()}
      {renderAlerts()}
    </div>
  );
};

export default RealTimeMonitoringDashboard; 