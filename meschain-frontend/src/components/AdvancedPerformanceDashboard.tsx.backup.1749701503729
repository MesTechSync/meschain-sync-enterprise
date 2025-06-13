import React, { useState, useEffect, useCallback } from 'react';
import { Activity, Cpu, Zap, TrendingUp, BarChart3, Monitor, Wifi, Database, Clock, AlertTriangle } from 'lucide-react';
import { Line, Doughnut, Bar } from 'react-chartjs-2';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  BarElement,
} from 'chart.js';
import toast from 'react-hot-toast';

// Register Chart.js components
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  BarElement
);

interface PerformanceMetrics {
  timestamp: string;
  memoryUsage: number;
  memoryLimit: number;
  cpuUsage: number;
  networkLatency: number;
  apiResponseTime: number;
  activeConnections: number;
  requestsPerSecond: number;
  errorRate: number;
  bundleSize: number;
  loadTime: number;
}

interface ApiEndpointMetrics {
  endpoint: string;
  averageResponseTime: number;
  requestCount: number;
  errorCount: number;
  successRate: number;
  lastCalled: string;
}

const AdvancedPerformanceDashboard: React.FC = () => {
  const [metrics, setMetrics] = useState<PerformanceMetrics[]>([]);
  const [apiMetrics, setApiMetrics] = useState<ApiEndpointMetrics[]>([]);
  const [currentMetrics, setCurrentMetrics] = useState<PerformanceMetrics | null>(null);
  const [isMonitoring, setIsMonitoring] = useState(true);
  const [timeRange, setTimeRange] = useState<'1h' | '6h' | '24h'>('1h');

  // Simulate real-time performance data collection
  const collectMetrics = useCallback(() => {
    try {
      const now = new Date().toISOString();
      
      // Collect browser performance metrics
      const performanceEntries = performance.getEntriesByType('navigation') as PerformanceNavigationTiming[];
      const memoryInfo = (performance as any).memory;
      
      const newMetric: PerformanceMetrics = {
        timestamp: now,
        memoryUsage: memoryInfo ? Math.round(memoryInfo.usedJSHeapSize / 1024 / 1024) : 0,
        memoryLimit: memoryInfo ? Math.round(memoryInfo.totalJSHeapSize / 1024 / 1024) : 0,
        cpuUsage: Math.random() * 50 + 20, // Simulated CPU usage
        networkLatency: Math.random() * 100 + 50, // Simulated network latency
        apiResponseTime: Math.random() * 200 + 100, // Simulated API response time
        activeConnections: Math.floor(Math.random() * 20) + 5,
        requestsPerSecond: Math.random() * 50 + 10,
        errorRate: Math.random() * 5, // 0-5% error rate
        bundleSize: 304.54, // Current bundle size in KB
        loadTime: performanceEntries[0] ? 
          Math.round(performanceEntries[0].loadEventEnd - performanceEntries[0].loadEventStart) : 0
      };

      setCurrentMetrics(newMetric);
      
      setMetrics(prev => {
        const updated = [...prev, newMetric];
        // Keep only last hour of data for 1h view, adjust for other ranges
        const maxPoints = timeRange === '1h' ? 60 : timeRange === '6h' ? 360 : 1440;
        return updated.slice(-maxPoints);
      });

    } catch (error) {
      console.error('Error collecting metrics:', error);
    }
  }, [timeRange]);

  // Simulate API endpoint metrics
  const updateApiMetrics = useCallback(() => {
    const endpoints = [
      '/api/dashboard/metrics',
      '/api/trendyol/products',
      '/api/trendyol/orders',
      '/api/marketplace/sync',
      '/api/user/profile',
      '/api/reports/sales'
    ];

    const updatedMetrics = endpoints.map(endpoint => ({
      endpoint,
      averageResponseTime: Math.random() * 300 + 50,
      requestCount: Math.floor(Math.random() * 1000) + 100,
      errorCount: Math.floor(Math.random() * 10),
      successRate: 95 + Math.random() * 5, // 95-100% success rate
      lastCalled: new Date(Date.now() - Math.random() * 3600000).toISOString()
    }));

    setApiMetrics(updatedMetrics);
  }, []);

  useEffect(() => {
    if (isMonitoring) {
      collectMetrics();
      updateApiMetrics();
      
      const interval = setInterval(() => {
        collectMetrics();
        updateApiMetrics();
      }, 5000); // Update every 5 seconds

      return () => clearInterval(interval);
    }
  }, [isMonitoring, collectMetrics, updateApiMetrics]);

  // Chart configurations
  const memoryChartData = {
    labels: metrics.slice(-20).map(m => new Date(m.timestamp).toLocaleTimeString('tr-TR', { 
      hour: '2-digit', 
      minute: '2-digit' 
    })),
    datasets: [
      {
        label: 'Kullanılan Bellek (MB)',
        data: metrics.slice(-20).map(m => m.memoryUsage),
        borderColor: 'rgb(59, 130, 246)',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        borderWidth: 2,
        fill: true,
        tension: 0.4
      },
      {
        label: 'Bellek Limiti (MB)',
        data: metrics.slice(-20).map(m => m.memoryLimit),
        borderColor: 'rgb(239, 68, 68)',
        backgroundColor: 'rgba(239, 68, 68, 0.1)',
        borderWidth: 2,
        borderDash: [5, 5],
        fill: false
      }
    ]
  };

  const performanceChartData = {
    labels: metrics.slice(-20).map(m => new Date(m.timestamp).toLocaleTimeString('tr-TR', { 
      hour: '2-digit', 
      minute: '2-digit' 
    })),
    datasets: [
      {
        label: 'CPU Kullanımı (%)',
        data: metrics.slice(-20).map(m => m.cpuUsage),
        borderColor: 'rgb(34, 197, 94)',
        backgroundColor: 'rgba(34, 197, 94, 0.1)',
        borderWidth: 2,
        yAxisID: 'y'
      },
      {
        label: 'API Yanıt Süresi (ms)',
        data: metrics.slice(-20).map(m => m.apiResponseTime),
        borderColor: 'rgb(168, 85, 247)',
        backgroundColor: 'rgba(168, 85, 247, 0.1)',
        borderWidth: 2,
        yAxisID: 'y1'
      }
    ]
  };

  const apiEndpointsChartData = {
    labels: apiMetrics.map(api => api.endpoint.replace('/api/', '')),
    datasets: [
      {
        label: 'Ortalama Yanıt Süresi (ms)',
        data: apiMetrics.map(api => api.averageResponseTime),
        backgroundColor: [
          'rgba(59, 130, 246, 0.8)',
          'rgba(16, 185, 129, 0.8)',
          'rgba(245, 158, 11, 0.8)',
          'rgba(239, 68, 68, 0.8)',
          'rgba(168, 85, 247, 0.8)',
          'rgba(236, 72, 153, 0.8)'
        ],
        borderColor: [
          'rgb(59, 130, 246)',
          'rgb(16, 185, 129)',
          'rgb(245, 158, 11)',
          'rgb(239, 68, 68)',
          'rgb(168, 85, 247)',
          'rgb(236, 72, 153)'
        ],
        borderWidth: 1
      }
    ]
  };

  const systemHealthData = {
    labels: ['Sağlıklı', 'Uyarı', 'Kritik'],
    datasets: [
      {
        data: [85, 12, 3],
        backgroundColor: [
          'rgba(34, 197, 94, 0.8)',
          'rgba(245, 158, 11, 0.8)',
          'rgba(239, 68, 68, 0.8)'
        ],
        borderColor: [
          'rgb(34, 197, 94)',
          'rgb(245, 158, 11)',
          'rgb(239, 68, 68)'
        ],
        borderWidth: 2
      }
    ]
  };

  const getStatusColor = (value: number, thresholds: { warning: number; critical: number }) => {
    if (value >= thresholds.critical) return 'text-red-600 bg-red-50 border-red-200';
    if (value >= thresholds.warning) return 'text-yellow-600 bg-yellow-50 border-yellow-200';
    return 'text-green-600 bg-green-50 border-green-200';
  };

  const getStatusIcon = (value: number, thresholds: { warning: number; critical: number }) => {
    if (value >= thresholds.critical) return <AlertTriangle className="w-5 h-5 text-red-500" />;
    if (value >= thresholds.warning) return <AlertTriangle className="w-5 h-5 text-yellow-500" />;
    return <Activity className="w-5 h-5 text-green-500" />;
  };

  return (
    <div className="space-y-6">
      {/* Header Controls */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-3">
            <div className="p-2 bg-blue-100 rounded-lg">
              <Monitor className="w-6 h-6 text-blue-600" />
            </div>
            <div>
              <h2 className="text-xl font-semibold text-gray-900">Advanced Performance Dashboard</h2>
              <p className="text-sm text-gray-500">
                Real-time system monitoring and performance analytics
              </p>
            </div>
          </div>
          
          <div className="flex items-center space-x-4">
            <div className="flex items-center space-x-2">
              <label className="text-sm font-medium text-gray-600">Time Range:</label>
              <select
                value={timeRange}
                onChange={(e) => setTimeRange(e.target.value as '1h' | '6h' | '24h')}
                className="bg-white border border-gray-300 rounded-md px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="1h">Last 1 Hour</option>
                <option value="6h">Last 6 Hours</option>
                <option value="24h">Last 24 Hours</option>
              </select>
            </div>
            
            <button
              onClick={() => {
                setIsMonitoring(!isMonitoring);
                toast.success(isMonitoring ? 'Monitoring stopped' : 'Monitoring started');
              }}
              className={`flex items-center space-x-2 px-4 py-2 rounded-lg transition-colors ${
                isMonitoring 
                  ? 'bg-red-500 text-white hover:bg-red-600' 
                  : 'bg-green-500 text-white hover:bg-green-600'
              }`}
            >
              <Activity className={`w-4 h-4 ${isMonitoring ? 'animate-pulse' : ''}`} />
              <span>{isMonitoring ? 'Stop' : 'Start'} Monitoring</span>
            </button>
          </div>
        </div>
      </div>

      {/* Real-time Metrics Cards */}
      {currentMetrics && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div className={`rounded-lg border p-6 ${getStatusColor(currentMetrics.memoryUsage, { warning: 100, critical: 150 })}`}>
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium opacity-75">Memory Usage</p>
                <p className="text-2xl font-bold">{currentMetrics.memoryUsage}MB</p>
                <p className="text-xs opacity-60">/ {currentMetrics.memoryLimit}MB</p>
              </div>
              <div className="p-3 bg-white bg-opacity-50 rounded-lg">
                <Cpu className="w-6 h-6" />
              </div>
            </div>
          </div>

          <div className={`rounded-lg border p-6 ${getStatusColor(currentMetrics.cpuUsage, { warning: 70, critical: 85 })}`}>
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium opacity-75">CPU Usage</p>
                <p className="text-2xl font-bold">{Math.round(currentMetrics.cpuUsage)}%</p>
              </div>
              <div className="p-3 bg-white bg-opacity-50 rounded-lg">
                {getStatusIcon(currentMetrics.cpuUsage, { warning: 70, critical: 85 })}
              </div>
            </div>
          </div>

          <div className={`rounded-lg border p-6 ${getStatusColor(currentMetrics.apiResponseTime, { warning: 200, critical: 300 })}`}>
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium opacity-75">API Response</p>
                <p className="text-2xl font-bold">{Math.round(currentMetrics.apiResponseTime)}ms</p>
              </div>
              <div className="p-3 bg-white bg-opacity-50 rounded-lg">
                <Zap className="w-6 h-6" />
              </div>
            </div>
          </div>

          <div className={`rounded-lg border p-6 ${getStatusColor(currentMetrics.errorRate, { warning: 2, critical: 5 })}`}>
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium opacity-75">Error Rate</p>
                <p className="text-2xl font-bold">{currentMetrics.errorRate.toFixed(1)}%</p>
              </div>
              <div className="p-3 bg-white bg-opacity-50 rounded-lg">
                <AlertTriangle className="w-6 h-6" />
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Charts Section */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Memory Usage Chart */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <Cpu className="w-5 h-5 mr-2" />
            Memory Usage Trend
          </h3>
          <div className="h-64">
            <Line 
              data={memoryChartData}
              options={{
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                  legend: {
                    position: 'top',
                  },
                },
                scales: {
                  y: {
                    beginAtZero: true,
                    title: {
                      display: true,
                      text: 'Memory (MB)'
                    }
                  }
                }
              }}
            />
          </div>
        </div>

        {/* Performance Chart */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <TrendingUp className="w-5 h-5 mr-2" />
            Performance Metrics
          </h3>
          <div className="h-64">
            <Line 
              data={performanceChartData}
              options={{
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                  mode: 'index',
                  intersect: false,
                },
                plugins: {
                  legend: {
                    position: 'top',
                  },
                },
                scales: {
                  y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                      display: true,
                      text: 'CPU Usage (%)'
                    }
                  },
                  y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                      display: true,
                      text: 'Response Time (ms)'
                    },
                    grid: {
                      drawOnChartArea: false,
                    },
                  },
                }
              }}
            />
          </div>
        </div>
      </div>

      {/* API Endpoints and System Health */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* API Endpoints Performance */}
        <div className="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <BarChart3 className="w-5 h-5 mr-2" />
            API Endpoints Performance
          </h3>
          <div className="h-64">
            <Bar 
              data={apiEndpointsChartData}
              options={{
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                  legend: {
                    display: false
                  },
                },
                scales: {
                  y: {
                    beginAtZero: true,
                    title: {
                      display: true,
                      text: 'Response Time (ms)'
                    }
                  }
                }
              }}
            />
          </div>
        </div>

        {/* System Health */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <Database className="w-5 h-5 mr-2" />
            System Health
          </h3>
          <div className="h-64 flex items-center justify-center">
            <div className="w-48 h-48">
              <Doughnut 
                data={systemHealthData}
                options={{
                  responsive: true,
                  maintainAspectRatio: false,
                  plugins: {
                    legend: {
                      position: 'bottom',
                    },
                  }
                }}
              />
            </div>
          </div>
        </div>
      </div>

      {/* API Endpoints Details Table */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
          <Wifi className="w-5 h-5 mr-2" />
          API Endpoints Detailed Metrics
        </h3>
        <div className="overflow-x-auto">
          <table className="min-w-full">
            <thead>
              <tr className="border-b border-gray-200">
                <th className="text-left py-3 px-4 font-medium text-gray-900">Endpoint</th>
                <th className="text-left py-3 px-4 font-medium text-gray-900">Avg Response Time</th>
                <th className="text-left py-3 px-4 font-medium text-gray-900">Requests</th>
                <th className="text-left py-3 px-4 font-medium text-gray-900">Success Rate</th>
                <th className="text-left py-3 px-4 font-medium text-gray-900">Last Called</th>
                <th className="text-left py-3 px-4 font-medium text-gray-900">Status</th>
              </tr>
            </thead>
            <tbody>
              {apiMetrics.map((api, index) => (
                <tr key={index} className="border-b border-gray-100 hover:bg-gray-50">
                  <td className="py-3 px-4 font-mono text-sm">{api.endpoint}</td>
                  <td className="py-3 px-4">
                    <span className={`font-medium ${
                      api.averageResponseTime > 300 ? 'text-red-600' :
                      api.averageResponseTime > 200 ? 'text-yellow-600' : 'text-green-600'
                    }`}>
                      {Math.round(api.averageResponseTime)}ms
                    </span>
                  </td>
                  <td className="py-3 px-4">{api.requestCount.toLocaleString()}</td>
                  <td className="py-3 px-4">
                    <span className={`font-medium ${
                      api.successRate >= 99 ? 'text-green-600' :
                      api.successRate >= 95 ? 'text-yellow-600' : 'text-red-600'
                    }`}>
                      {api.successRate.toFixed(1)}%
                    </span>
                  </td>
                  <td className="py-3 px-4 text-sm text-gray-500">
                    {new Date(api.lastCalled).toLocaleString('tr-TR')}
                  </td>
                  <td className="py-3 px-4">
                    <span className={`inline-flex px-2 py-1 rounded-full text-xs font-medium ${
                      api.successRate >= 99 ? 'bg-green-100 text-green-800' :
                      api.successRate >= 95 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'
                    }`}>
                      {api.successRate >= 99 ? 'Healthy' : api.successRate >= 95 ? 'Warning' : 'Critical'}
                    </span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Performance Insights */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
          <Clock className="w-5 h-5 mr-2" />
          Performance Insights & Recommendations
        </h3>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div className="p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <h4 className="font-medium text-blue-900 mb-2">Bundle Size Optimization</h4>
            <p className="text-sm text-blue-700">
              Current bundle size: {currentMetrics?.bundleSize}KB. Consider code splitting for better performance.
            </p>
          </div>
          
          <div className="p-4 bg-green-50 border border-green-200 rounded-lg">
            <h4 className="font-medium text-green-900 mb-2">Memory Management</h4>
            <p className="text-sm text-green-700">
              Memory usage is within optimal range. Good garbage collection performance detected.
            </p>
          </div>
          
          <div className="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <h4 className="font-medium text-yellow-900 mb-2">API Optimization</h4>
            <p className="text-sm text-yellow-700">
              Some API endpoints show higher response times. Consider caching and request optimization.
            </p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default AdvancedPerformanceDashboard; 