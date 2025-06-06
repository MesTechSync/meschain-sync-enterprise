/**
 * Performance Dashboard Component
 * Real-time performance monitoring and optimization insights
 */

import React, { useState, useEffect, useCallback, useMemo } from 'react';
import {
  Card,
  CardHeader,
  CardTitle,
  CardContent,
  Badge,
  Button,
  Progress,
  Alert,
  AlertDescription,
  Table,
  TableHeader,
  TableRow,
  TableHead,
  TableBody,
  TableCell,
  Tabs,
  TabsList,
  TabsTrigger,
  TabsContent,
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
  Switch,
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger
} from '@/components/ui';
import {
  Activity,
  Zap,
  TrendingUp,
  TrendingDown,
  Clock,
  Database,
  HardDrive,
  Cpu,
  Memory,
  Network,
  AlertTriangle,
  CheckCircle,
  Play,
  Pause,
  BarChart3,
  LineChart,
  PieChart,
  Settings,
  Download,
  RefreshCw,
  Gauge,
  Target,
  Layers,
  Package,
  Globe,
  Users,
  Server
} from 'lucide-react';
import { 
  LineChart as RechartsLineChart, 
  Line, 
  AreaChart, 
  Area, 
  XAxis, 
  YAxis, 
  CartesianGrid, 
  Tooltip as RechartsTooltip, 
  Legend, 
  ResponsiveContainer,
  PieChart as RechartsPieChart,
  Pie,
  Cell,
  BarChart,
  Bar,
  ComposedChart
} from 'recharts';
import PerformanceMonitor, { PerformanceMetrics, PerformanceAlert } from '../../services/performance/PerformanceMonitor';
import CacheManager from '../../services/performance/CacheManager';
import DatabaseOptimizer from '../../services/performance/DatabaseOptimizer';
import BundleOptimizer from '../../services/performance/BundleOptimizer';

// Types
interface PerformanceOverview {
  performanceScore: number;
  responseTime: number;
  throughput: number;
  errorRate: number;
  uptime: number;
  activeUsers: number;
  cacheHitRatio: number;
  databaseHealth: number;
}

interface OptimizationSuggestion {
  id: string;
  category: 'FRONTEND' | 'BACKEND' | 'DATABASE' | 'CACHE' | 'INFRASTRUCTURE';
  priority: 'LOW' | 'MEDIUM' | 'HIGH' | 'CRITICAL';
  title: string;
  description: string;
  impact: number;
  effort: 'LOW' | 'MEDIUM' | 'HIGH';
  estimatedSavings: string;
}

const PerformanceDashboard: React.FC = () => {
  // State Management
  const [performanceMonitor] = useState(() => new PerformanceMonitor());
  const [cacheManager] = useState(() => new CacheManager());
  const [databaseOptimizer] = useState(() => new DatabaseOptimizer());
  const [bundleOptimizer] = useState(() => new BundleOptimizer());
  
  const [overview, setOverview] = useState<PerformanceOverview>({
    performanceScore: 85,
    responseTime: 245,
    throughput: 1250,
    errorRate: 0.8,
    uptime: 99.9,
    activeUsers: 847,
    cacheHitRatio: 89,
    databaseHealth: 92
  });
  
  const [metrics, setMetrics] = useState<PerformanceMetrics[]>([]);
  const [alerts, setAlerts] = useState<PerformanceAlert[]>([]);
  const [suggestions, setSuggestions] = useState<OptimizationSuggestion[]>([]);
  const [isMonitoring, setIsMonitoring] = useState(true);
  const [selectedTimeRange, setSelectedTimeRange] = useState('1h');
  const [autoRefresh, setAutoRefresh] = useState(true);

  // Colors for charts
  const CHART_COLORS = {
    primary: '#3b82f6',
    success: '#10b981',
    warning: '#f59e0b',
    danger: '#ef4444',
    info: '#6366f1',
    secondary: '#8b5cf6'
  };

  // Data Loading
  useEffect(() => {
    loadPerformanceData();
    
    if (autoRefresh) {
      const interval = setInterval(loadPerformanceData, 30000); // Every 30 seconds
      return () => clearInterval(interval);
    }
  }, [autoRefresh]);

  useEffect(() => {
    // Load optimization suggestions
    loadOptimizationSuggestions();
  }, []);

  const loadPerformanceData = useCallback(async () => {
    try {
      // Get latest metrics
      const latestMetrics = performanceMonitor.getMetrics(50);
      setMetrics(latestMetrics);

      // Get alerts
      const currentAlerts = performanceMonitor.getAlerts(false);
      setAlerts(currentAlerts);

      // Update overview
      const latest = latestMetrics[latestMetrics.length - 1];
      if (latest) {
        setOverview({
          performanceScore: performanceMonitor.getPerformanceScore(),
          responseTime: latest.application.averageResponseTime,
          throughput: latest.application.requestsPerSecond,
          errorRate: latest.application.errorRate,
          uptime: 99.9,
          activeUsers: latest.application.activeUsers,
          cacheHitRatio: latest.database.cacheHitRatio,
          databaseHealth: 100 - (latest.database.slowQueries * 5)
        });
      }

    } catch (error) {
      console.error('Error loading performance data:', error);
    }
  }, [performanceMonitor]);

  const loadOptimizationSuggestions = useCallback(async () => {
    // Mock optimization suggestions
    const mockSuggestions: OptimizationSuggestion[] = [
      {
        id: '1',
        category: 'DATABASE',
        priority: 'HIGH',
        title: 'Add Database Index',
        description: 'Products table query optimization with composite index',
        impact: 65,
        effort: 'LOW',
        estimatedSavings: '40% faster queries'
      },
      {
        id: '2',
        category: 'FRONTEND',
        priority: 'MEDIUM',
        title: 'Bundle Code Splitting',
        description: 'Split vendor bundle to improve initial load time',
        impact: 35,
        effort: 'MEDIUM',
        estimatedSavings: '2.3s faster load'
      },
      {
        id: '3',
        category: 'CACHE',
        priority: 'MEDIUM',
        title: 'Redis Cache Layer',
        description: 'Implement Redis for frequently accessed data',
        impact: 50,
        effort: 'HIGH',
        estimatedSavings: '60% cache hit rate'
      },
      {
        id: '4',
        category: 'INFRASTRUCTURE',
        priority: 'LOW',
        title: 'CDN Implementation',
        description: 'Use CDN for static assets delivery',
        impact: 25,
        effort: 'MEDIUM',
        estimatedSavings: '1.5s faster assets'
      }
    ];

    setSuggestions(mockSuggestions);
  }, []);

  // Chart Data
  const performanceTrendData = useMemo(() => {
    return metrics.slice(-24).map((metric, index) => ({
      time: new Date(metric.timestamp).toLocaleTimeString('tr-TR', { 
        hour: '2-digit', 
        minute: '2-digit' 
      }),
      responseTime: metric.application.averageResponseTime,
      throughput: metric.application.requestsPerSecond,
      cpuUsage: metric.cpu.usage,
      memoryUsage: metric.memory.usage,
      errorRate: metric.application.errorRate
    }));
  }, [metrics]);

  const resourceUtilizationData = useMemo(() => {
    const latest = metrics[metrics.length - 1];
    if (!latest) return [];

    return [
      { name: 'CPU', value: latest.cpu.usage, color: CHART_COLORS.danger },
      { name: 'Memory', value: latest.memory.usage, color: CHART_COLORS.warning },
      { name: 'Storage', value: 65, color: CHART_COLORS.info },
      { name: 'Network', value: 45, color: CHART_COLORS.success }
    ];
  }, [metrics]);

  const databaseMetricsData = useMemo(() => {
    return metrics.slice(-12).map(metric => ({
      time: new Date(metric.timestamp).toLocaleTimeString('tr-TR', { 
        hour: '2-digit', 
        minute: '2-digit' 
      }),
      queryTime: metric.database.averageQueryTime,
      connections: metric.database.connections,
      slowQueries: metric.database.slowQueries,
      cacheHit: metric.database.cacheHitRatio
    }));
  }, [metrics]);

  // Event Handlers
  const handleStartProfiling = async () => {
    const profileId = performanceMonitor.startProfiling('Dashboard Profile');
    console.log('Started profiling:', profileId);
    
    setTimeout(() => {
      const profile = performanceMonitor.stopProfiling(profileId);
      console.log('Profiling completed:', profile);
    }, 10000);
  };

  const handleGenerateReport = () => {
    const endTime = new Date();
    const startTime = new Date(endTime.getTime() - 24 * 60 * 60 * 1000); // 24 hours ago
    
    const report = performanceMonitor.generateReport(startTime, endTime);
    
    // Download report
    const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `performance-report-${new Date().toISOString().split('T')[0]}.json`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  };

  const handleAcknowledgeAlert = (alertId: string) => {
    performanceMonitor.acknowledgeAlert(alertId);
    setAlerts(prev => prev.filter(alert => alert.id !== alertId));
  };

  const getPriorityColor = (priority: string) => {
    switch (priority) {
      case 'CRITICAL': return 'destructive';
      case 'HIGH': return 'destructive';
      case 'MEDIUM': return 'default';
      case 'LOW': return 'secondary';
      default: return 'secondary';
    }
  };

  const getScoreColor = (score: number) => {
    if (score >= 90) return 'text-green-600';
    if (score >= 75) return 'text-yellow-600';
    if (score >= 60) return 'text-orange-600';
    return 'text-red-600';
  };

  const getHealthIcon = (score: number) => {
    if (score >= 90) return CheckCircle;
    if (score >= 75) return AlertTriangle;
    return AlertTriangle;
  };

  return (
    <div className="p-6 space-y-6 bg-gray-50 min-h-screen">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Performans Dashboard</h1>
          <p className="text-gray-600 mt-1">Gerçek zamanlı performans izleme ve optimizasyon</p>
        </div>
        
        <div className="flex items-center gap-3">
          <div className="flex items-center gap-2">
            <span className="text-sm text-gray-600">Otomatik Yenileme</span>
            <Switch
              checked={autoRefresh}
              onCheckedChange={setAutoRefresh}
            />
          </div>
          
          <Select value={selectedTimeRange} onValueChange={setSelectedTimeRange}>
            <SelectTrigger className="w-24">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="1h">1s</SelectItem>
              <SelectItem value="6h">6s</SelectItem>
              <SelectItem value="24h">24s</SelectItem>
              <SelectItem value="7d">7g</SelectItem>
            </SelectContent>
          </Select>
          
          <Button onClick={handleGenerateReport} variant="outline">
            <Download className="w-4 h-4 mr-2" />
            Rapor İndir
          </Button>
          
          <Button onClick={handleStartProfiling}>
            <Activity className="w-4 h-4 mr-2" />
            Profil Başlat
          </Button>
        </div>
      </div>

      {/* Performance Score Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card className="border-l-4 border-l-blue-500">
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Performans Skoru</p>
                <p className={`text-3xl font-bold ${getScoreColor(overview.performanceScore)}`}>
                  {overview.performanceScore}
                </p>
              </div>
              <Gauge className="w-8 h-8 text-blue-500" />
            </div>
            <Progress value={overview.performanceScore} className="mt-3" />
          </CardContent>
        </Card>

        <Card className="border-l-4 border-l-green-500">
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Yanıt Süresi</p>
                <p className="text-3xl font-bold text-green-600">{overview.responseTime}ms</p>
              </div>
              <Clock className="w-8 h-8 text-green-500" />
            </div>
            <p className="text-sm text-gray-500 mt-2">
              Hedef: &lt;300ms
            </p>
          </CardContent>
        </Card>

        <Card className="border-l-4 border-l-purple-500">
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Throughput</p>
                <p className="text-3xl font-bold text-purple-600">{overview.throughput}</p>
              </div>
              <TrendingUp className="w-8 h-8 text-purple-500" />
            </div>
            <p className="text-sm text-gray-500 mt-2">
              İstek/saniye
            </p>
          </CardContent>
        </Card>

        <Card className="border-l-4 border-l-yellow-500">
          <CardContent className="p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Hata Oranı</p>
                <p className="text-3xl font-bold text-yellow-600">{overview.errorRate}%</p>
              </div>
              <AlertTriangle className="w-8 h-8 text-yellow-500" />
            </div>
            <p className="text-sm text-gray-500 mt-2">
              Hedef: &lt;1%
            </p>
          </CardContent>
        </Card>
      </div>

      {/* Active Alerts */}
      {alerts.length > 0 && (
        <Alert>
          <AlertTriangle className="h-4 w-4" />
          <AlertDescription>
            <div className="flex items-center justify-between">
              <span>{alerts.length} aktif performans uyarısı bulunuyor.</span>
              <Button 
                variant="outline" 
                size="sm" 
                onClick={() => setAlerts([])}
              >
                Tümünü Onayla
              </Button>
            </div>
          </AlertDescription>
        </Alert>
      )}

      {/* Main Content Tabs */}
      <Tabs defaultValue="overview" className="space-y-6">
        <TabsList className="grid w-full grid-cols-6">
          <TabsTrigger value="overview">Genel Bakış</TabsTrigger>
          <TabsTrigger value="realtime">Gerçek Zamanlı</TabsTrigger>
          <TabsTrigger value="database">Veritabanı</TabsTrigger>
          <TabsTrigger value="frontend">Frontend</TabsTrigger>
          <TabsTrigger value="cache">Cache</TabsTrigger>
          <TabsTrigger value="optimization">Optimizasyon</TabsTrigger>
        </TabsList>

        {/* Overview Tab */}
        <TabsContent value="overview" className="space-y-6">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {/* Performance Trend */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <LineChart className="w-5 h-5" />
                  Performans Trendi (Son 24 Saat)
                </CardTitle>
              </CardHeader>
              <CardContent>
                <ResponsiveContainer width="100%" height={300}>
                  <RechartsLineChart data={performanceTrendData}>
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="time" />
                    <YAxis yAxisId="left" />
                    <YAxis yAxisId="right" orientation="right" />
                    <RechartsTooltip />
                    <Legend />
                    <Line 
                      yAxisId="left"
                      type="monotone" 
                      dataKey="responseTime" 
                      stroke={CHART_COLORS.primary}
                      name="Yanıt Süresi (ms)"
                    />
                    <Line 
                      yAxisId="right"
                      type="monotone" 
                      dataKey="throughput" 
                      stroke={CHART_COLORS.success}
                      name="Throughput"
                    />
                  </RechartsLineChart>
                </ResponsiveContainer>
              </CardContent>
            </Card>

            {/* Resource Utilization */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <PieChart className="w-5 h-5" />
                  Kaynak Kullanımı
                </CardTitle>
              </CardHeader>
              <CardContent>
                <ResponsiveContainer width="100%" height={300}>
                  <RechartsPieChart>
                    <Pie
                      data={resourceUtilizationData}
                      cx="50%"
                      cy="50%"
                      outerRadius={80}
                      dataKey="value"
                      label={({ name, value }) => `${name}: ${value}%`}
                    >
                      {resourceUtilizationData.map((entry, index) => (
                        <Cell key={`cell-${index}`} fill={entry.color} />
                      ))}
                    </Pie>
                    <RechartsTooltip />
                  </RechartsPieChart>
                </ResponsiveContainer>
              </CardContent>
            </Card>
          </div>

          {/* System Health Overview */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <Card>
              <CardContent className="p-6">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm font-medium text-gray-600">Uptime</p>
                    <p className="text-2xl font-bold text-green-600">{overview.uptime}%</p>
                  </div>
                  <Server className="w-6 h-6 text-green-500" />
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardContent className="p-6">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm font-medium text-gray-600">Aktif Kullanıcı</p>
                    <p className="text-2xl font-bold text-blue-600">{overview.activeUsers.toLocaleString('tr-TR')}</p>
                  </div>
                  <Users className="w-6 h-6 text-blue-500" />
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardContent className="p-6">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm font-medium text-gray-600">Cache Hit Oranı</p>
                    <p className="text-2xl font-bold text-purple-600">{overview.cacheHitRatio}%</p>
                  </div>
                  <Database className="w-6 h-6 text-purple-500" />
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardContent className="p-6">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm font-medium text-gray-600">DB Sağlığı</p>
                    <p className="text-2xl font-bold text-green-600">{overview.databaseHealth}%</p>
                  </div>
                  <HardDrive className="w-6 h-6 text-green-500" />
                </div>
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        {/* Real-time Tab */}
        <TabsContent value="realtime" className="space-y-6">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {/* CPU & Memory Usage */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Cpu className="w-5 h-5" />
                  CPU ve Bellek Kullanımı
                </CardTitle>
              </CardHeader>
              <CardContent>
                <ResponsiveContainer width="100%" height={300}>
                  <AreaChart data={performanceTrendData}>
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="time" />
                    <YAxis />
                    <RechartsTooltip />
                    <Legend />
                    <Area 
                      type="monotone" 
                      dataKey="cpuUsage" 
                      stackId="1"
                      stroke={CHART_COLORS.danger}
                      fill={CHART_COLORS.danger}
                      name="CPU (%)"
                    />
                    <Area 
                      type="monotone" 
                      dataKey="memoryUsage" 
                      stackId="2"
                      stroke={CHART_COLORS.warning}
                      fill={CHART_COLORS.warning}
                      name="Bellek (%)"
                    />
                  </AreaChart>
                </ResponsiveContainer>
              </CardContent>
            </Card>

            {/* Error Rate Monitoring */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <AlertTriangle className="w-5 h-5" />
                  Hata Oranı İzleme
                </CardTitle>
              </CardHeader>
              <CardContent>
                <ResponsiveContainer width="100%" height={300}>
                  <BarChart data={performanceTrendData}>
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="time" />
                    <YAxis />
                    <RechartsTooltip />
                    <Bar 
                      dataKey="errorRate" 
                      fill={CHART_COLORS.danger}
                      name="Hata Oranı (%)"
                    />
                  </BarChart>
                </ResponsiveContainer>
              </CardContent>
            </Card>
          </div>

          {/* Active Alerts Table */}
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <AlertTriangle className="w-5 h-5" />
                Aktif Uyarılar
              </CardTitle>
            </CardHeader>
            <CardContent>
              {alerts.length > 0 ? (
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>Zaman</TableHead>
                      <TableHead>Ciddiyet</TableHead>
                      <TableHead>Kategori</TableHead>
                      <TableHead>Mesaj</TableHead>
                      <TableHead>Öneri</TableHead>
                      <TableHead>İşlem</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {alerts.slice(0, 10).map((alert) => (
                      <TableRow key={alert.id}>
                        <TableCell>{alert.timestamp.toLocaleString('tr-TR')}</TableCell>
                        <TableCell>
                          <Badge variant={getPriorityColor(alert.severity)}>
                            {alert.severity}
                          </Badge>
                        </TableCell>
                        <TableCell>{alert.category}</TableCell>
                        <TableCell className="max-w-md truncate">{alert.message}</TableCell>
                        <TableCell className="max-w-md truncate">{alert.recommendation}</TableCell>
                        <TableCell>
                          <Button 
                            variant="outline" 
                            size="sm"
                            onClick={() => handleAcknowledgeAlert(alert.id)}
                          >
                            Onayla
                          </Button>
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              ) : (
                <p className="text-center text-gray-500 py-8">Aktif uyarı bulunmuyor</p>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        {/* Database Tab */}
        <TabsContent value="database" className="space-y-6">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {/* Database Performance */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Database className="w-5 h-5" />
                  Veritabanı Performansı
                </CardTitle>
              </CardHeader>
              <CardContent>
                <ResponsiveContainer width="100%" height={300}>
                  <ComposedChart data={databaseMetricsData}>
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="time" />
                    <YAxis yAxisId="left" />
                    <YAxis yAxisId="right" orientation="right" />
                    <RechartsTooltip />
                    <Legend />
                    <Bar 
                      yAxisId="left"
                      dataKey="queryTime" 
                      fill={CHART_COLORS.primary}
                      name="Sorgu Süresi (ms)"
                    />
                    <Line 
                      yAxisId="right"
                      type="monotone" 
                      dataKey="cacheHit" 
                      stroke={CHART_COLORS.success}
                      name="Cache Hit (%)"
                    />
                  </ComposedChart>
                </ResponsiveContainer>
              </CardContent>
            </Card>

            {/* Connection Pool Status */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Network className="w-5 h-5" />
                  Bağlantı Havuzu Durumu
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-4">
                  <div className="flex justify-between items-center">
                    <span className="text-sm font-medium">Aktif Bağlantılar</span>
                    <span className="text-lg font-bold text-blue-600">
                      {metrics[metrics.length - 1]?.database.connections || 0}
                    </span>
                  </div>
                  <Progress 
                    value={((metrics[metrics.length - 1]?.database.connections || 0) / 100) * 100} 
                    className="h-2" 
                  />
                  
                  <div className="grid grid-cols-2 gap-4 mt-6">
                    <div className="text-center">
                      <p className="text-2xl font-bold text-green-600">
                        {metrics[metrics.length - 1]?.database.cacheHitRatio.toFixed(1) || 0}%
                      </p>
                      <p className="text-sm text-gray-600">Cache Hit Oranı</p>
                    </div>
                    <div className="text-center">
                      <p className="text-2xl font-bold text-red-600">
                        {metrics[metrics.length - 1]?.database.slowQueries || 0}
                      </p>
                      <p className="text-sm text-gray-600">Yavaş Sorgular</p>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        {/* Frontend Tab */}
        <TabsContent value="frontend" className="space-y-6">
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Package className="w-5 h-5" />
                  Bundle Boyutu
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="text-center">
                  <p className="text-3xl font-bold text-blue-600">2.4 MB</p>
                  <p className="text-sm text-gray-600 mt-1">Sıkıştırılmış</p>
                  <Progress value={65} className="mt-4" />
                  <p className="text-xs text-gray-500 mt-2">Hedef: &lt;2 MB</p>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Clock className="w-5 h-5" />
                  Sayfa Yükleme
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="text-center">
                  <p className="text-3xl font-bold text-green-600">1.8s</p>
                  <p className="text-sm text-gray-600 mt-1">Ortalama</p>
                  <Progress value={75} className="mt-4" />
                  <p className="text-xs text-gray-500 mt-2">Hedef: &lt;2s</p>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Zap className="w-5 h-5" />
                  Core Web Vitals
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-2">
                  <div className="flex justify-between">
                    <span className="text-sm">LCP</span>
                    <Badge variant="default">İyi</Badge>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm">FID</span>
                    <Badge variant="default">İyi</Badge>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm">CLS</span>
                    <Badge variant="secondary">Orta</Badge>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        {/* Cache Tab */}
        <TabsContent value="cache" className="space-y-6">
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Memory className="w-5 h-5" />
                  Cache Performansı
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="grid grid-cols-2 gap-4">
                  <div className="text-center">
                    <p className="text-3xl font-bold text-green-600">89%</p>
                    <p className="text-sm text-gray-600">Hit Oranı</p>
                  </div>
                  <div className="text-center">
                    <p className="text-3xl font-bold text-blue-600">45ms</p>
                    <p className="text-sm text-gray-600">Ortalama Süre</p>
                  </div>
                  <div className="text-center">
                    <p className="text-3xl font-bold text-purple-600">2.1GB</p>
                    <p className="text-sm text-gray-600">Kullanılan</p>
                  </div>
                  <div className="text-center">
                    <p className="text-3xl font-bold text-yellow-600">15,847</p>
                    <p className="text-sm text-gray-600">Toplam Key</p>
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Layers className="w-5 h-5" />
                  Cache Stratejileri
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-3">
                  <div className="flex justify-between items-center">
                    <span className="text-sm font-medium">LRU Cache</span>
                    <Badge variant="default">Aktif</Badge>
                  </div>
                  <div className="flex justify-between items-center">
                    <span className="text-sm font-medium">Redis Cache</span>
                    <Badge variant="default">Aktif</Badge>
                  </div>
                  <div className="flex justify-between items-center">
                    <span className="text-sm font-medium">Browser Cache</span>
                    <Badge variant="secondary">Optimizing</Badge>
                  </div>
                  <div className="flex justify-between items-center">
                    <span className="text-sm font-medium">CDN Cache</span>
                    <Badge variant="default">Aktif</Badge>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        {/* Optimization Tab */}
        <TabsContent value="optimization" className="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Target className="w-5 h-5" />
                Optimizasyon Önerileri
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {suggestions.map((suggestion) => (
                  <div key={suggestion.id} className="border rounded-lg p-4">
                    <div className="flex items-center justify-between mb-3">
                      <div className="flex items-center gap-3">
                        <Badge variant={getPriorityColor(suggestion.priority)}>
                          {suggestion.priority}
                        </Badge>
                        <Badge variant="outline">{suggestion.category}</Badge>
                        <h3 className="font-semibold">{suggestion.title}</h3>
                      </div>
                      <div className="text-right">
                        <p className="text-sm font-medium text-green-600">
                          {suggestion.estimatedSavings}
                        </p>
                        <p className="text-xs text-gray-500">
                          %{suggestion.impact} iyileştirme
                        </p>
                      </div>
                    </div>
                    
                    <p className="text-gray-700 mb-3">{suggestion.description}</p>
                    
                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-4 text-sm text-gray-600">
                        <span>Efor: {suggestion.effort}</span>
                        <span>Etki: %{suggestion.impact}</span>
                      </div>
                      <Button variant="outline" size="sm">
                        Uygula
                      </Button>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
};

export default PerformanceDashboard; 