import React, { useState, useEffect, useCallback } from 'react';
import {
  LineChart,
  Line,
  AreaChart,
  Area,
  BarChart,
  Bar,
  PieChart,
  Pie,
  Cell,
  ComposedChart,
  RadialBarChart,
  RadialBar,
  ScatterChart,
  Scatter,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer
} from 'recharts';
import { useTranslation } from 'react-i18next';

// Types
interface AdvancedMetrics {
  revenue: number;
  orders: number;
  visitors: number;
  conversionRate: number;
  averageOrderValue: number;
  customerLifetimeValue: number;
  returnOnAdSpend: number;
  profitMargin: number;
}

interface ChartData {
  timestamp: string;
  revenue: number;
  orders: number;
  visitors: number;
  conversionRate: number;
  profit: number;
  expenses: number;
}

interface MarketplaceData {
  name: string;
  revenue: number;
  orders: number;
  growth: number;
  marketShare: number;
  color: string;
}

interface CategoryPerformance {
  category: string;
  revenue: number;
  orders: number;
  profit: number;
  margin: number;
  trend: 'up' | 'down' | 'stable';
}

interface AIInsight {
  type: 'opportunity' | 'warning' | 'trend' | 'recommendation';
  title: string;
  description: string;
  impact: 'high' | 'medium' | 'low';
  action?: string;
}

type ChartType = 'line' | 'area' | 'bar' | 'pie' | 'composed' | 'radial' | 'scatter';
type MetricType = 'revenue' | 'orders' | 'visitors' | 'conversionRate';
type TimeRange = '1h' | '24h' | '7d' | '30d' | '90d' | '1y';

const AdvancedAnalytics: React.FC = () => {
  const { t } = useTranslation();
  
  // State
  const [metrics, setMetrics] = useState<AdvancedMetrics>({
    revenue: 0,
    orders: 0,
    visitors: 0,
    conversionRate: 0,
    averageOrderValue: 0,
    customerLifetimeValue: 0,
    returnOnAdSpend: 0,
    profitMargin: 0
  });

  const [chartData, setChartData] = useState<ChartData[]>([]);
  const [marketplaceData, setMarketplaceData] = useState<MarketplaceData[]>([]);
  const [categoryData, setCategoryData] = useState<CategoryPerformance[]>([]);
  const [aiInsights, setAiInsights] = useState<AIInsight[]>([]);
  
  const [selectedChart, setSelectedChart] = useState<ChartType>('line');
  const [selectedMetric, setSelectedMetric] = useState<MetricType>('revenue');
  const [timeRange, setTimeRange] = useState<TimeRange>('7d');
  const [isRealTime, setIsRealTime] = useState(false);
  const [isLoading, setIsLoading] = useState(true);

  // Real-time data update
  const updateRealTimeData = useCallback(async () => {
    if (!isRealTime) return;

    try {
      // Simulate real-time API call
      const newDataPoint: ChartData = {
        timestamp: new Date().toISOString(),
        revenue: Math.random() * 10000 + 50000,
        orders: Math.floor(Math.random() * 50) + 100,
        visitors: Math.floor(Math.random() * 500) + 1000,
        conversionRate: Math.random() * 2 + 2,
        profit: Math.random() * 5000 + 15000,
        expenses: Math.random() * 3000 + 10000
      };

      setChartData(prev => [...prev.slice(-23), newDataPoint]);
      
      // Update metrics
      setMetrics(prev => ({
        ...prev,
        revenue: prev.revenue + newDataPoint.revenue,
        orders: prev.orders + newDataPoint.orders,
        visitors: prev.visitors + newDataPoint.visitors,
        conversionRate: (prev.conversionRate + newDataPoint.conversionRate) / 2
      }));

    } catch (error) {
      console.error('Real-time data update error:', error);
    }
  }, [isRealTime]);

  // Load initial data
  useEffect(() => {
    loadAnalyticsData();
  }, [timeRange]);

  // Real-time updates
  useEffect(() => {
    let interval: NodeJS.Timeout;
    
    if (isRealTime) {
      interval = setInterval(updateRealTimeData, 30000); // 30 seconds
    }

    return () => {
      if (interval) clearInterval(interval);
    };
  }, [isRealTime, updateRealTimeData]);

  const loadAnalyticsData = async () => {
    setIsLoading(true);
    try {
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 1000));

      // Generate mock data based on time range
      const dataPoints = getDataPointsForTimeRange(timeRange);
      const mockChartData = generateMockChartData(dataPoints);
      
      setChartData(mockChartData);
      setMetrics({
        revenue: 2450000,
        orders: 1847,
        visitors: 45230,
        conversionRate: 4.08,
        averageOrderValue: 1326,
        customerLifetimeValue: 3250,
        returnOnAdSpend: 4.2,
        profitMargin: 28.5
      });

      setMarketplaceData([
        { name: 'Trendyol', revenue: 980000, orders: 742, growth: 15.2, marketShare: 40, color: '#F27A1A' },
        { name: 'Hepsiburada', revenue: 756000, orders: 567, growth: 12.8, marketShare: 31, color: '#FF6000' },
        { name: 'Amazon', revenue: 420000, orders: 315, growth: 8.5, marketShare: 17, color: '#FF9900' },
        { name: 'N11', revenue: 294000, orders: 223, growth: 5.2, marketShare: 12, color: '#7B2CBF' }
      ]);

      setCategoryData([
        { category: 'Elektronik', revenue: 980000, orders: 456, profit: 245000, margin: 25, trend: 'up' },
        { category: 'Moda', revenue: 756000, orders: 623, profit: 189000, margin: 25, trend: 'up' },
        { category: 'Ev & Yaşam', revenue: 420000, orders: 234, profit: 126000, margin: 30, trend: 'stable' },
        { category: 'Spor', revenue: 294000, orders: 167, profit: 88200, margin: 30, trend: 'down' }
      ]);

      setAiInsights([
        {
          type: 'opportunity',
          title: 'Elektronik Kategorisinde Büyüme Fırsatı',
          description: 'Son 7 günde elektronik kategorisinde %23 artış gözlemlendi. Stok artırımı önerilir.',
          impact: 'high',
          action: 'Stok seviyelerini %30 artır'
        },
        {
          type: 'warning',
          title: 'Spor Kategorisinde Düşüş',
          description: 'Spor ürünlerinde satışlar %12 azaldı. Pazarlama stratejisi gözden geçirilmeli.',
          impact: 'medium',
          action: 'Pazarlama kampanyası başlat'
        },
        {
          type: 'trend',
          title: 'Mobil Alışveriş Artışı',
          description: 'Mobil cihazlardan yapılan alışverişler %45 arttı.',
          impact: 'high'
        },
        {
          type: 'recommendation',
          title: 'Trendyol Optimizasyonu',
          description: 'Trendyol\'da dönüşüm oranınız sektör ortalamasının üzerinde. Reklam bütçesi artırılabilir.',
          impact: 'medium',
          action: 'Reklam bütçesini %20 artır'
        }
      ]);

    } catch (error) {
      console.error('Analytics data loading error:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const getDataPointsForTimeRange = (range: TimeRange): number => {
    switch (range) {
      case '1h': return 60;
      case '24h': return 24;
      case '7d': return 7;
      case '30d': return 30;
      case '90d': return 90;
      case '1y': return 12;
      default: return 7;
    }
  };

  const generateMockChartData = (points: number): ChartData[] => {
    return Array.from({ length: points }, (_, i) => ({
      timestamp: new Date(Date.now() - (points - i) * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
      revenue: Math.random() * 20000 + 30000,
      orders: Math.floor(Math.random() * 100) + 50,
      visitors: Math.floor(Math.random() * 1000) + 500,
      conversionRate: Math.random() * 3 + 2,
      profit: Math.random() * 8000 + 10000,
      expenses: Math.random() * 5000 + 8000
    }));
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('tr-TR', {
      style: 'currency',
      currency: 'TRY',
      minimumFractionDigits: 0
    }).format(amount);
  };

  const formatNumber = (num: number) => {
    return new Intl.NumberFormat('tr-TR').format(num);
  };

  const formatPercentage = (value: number) => {
    return `${value.toFixed(1)}%`;
  };

  const getInsightIcon = (type: AIInsight['type']) => {
    switch (type) {
      case 'opportunity': return '🚀';
      case 'warning': return '⚠️';
      case 'trend': return '📈';
      case 'recommendation': return '💡';
      default: return '📊';
    }
  };

  const getInsightColor = (type: AIInsight['type']) => {
    switch (type) {
      case 'opportunity': return 'bg-green-50 border-green-200 text-green-800';
      case 'warning': return 'bg-red-50 border-red-200 text-red-800';
      case 'trend': return 'bg-blue-50 border-blue-200 text-blue-800';
      case 'recommendation': return 'bg-purple-50 border-purple-200 text-purple-800';
      default: return 'bg-gray-50 border-gray-200 text-gray-800';
    }
  };

  const renderChart = () => {
    const commonProps = {
      data: chartData,
      margin: { top: 5, right: 30, left: 20, bottom: 5 }
    };

    switch (selectedChart) {
      case 'line':
        return (
          <LineChart {...commonProps}>
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis dataKey="timestamp" />
            <YAxis />
            <Tooltip formatter={(value) => formatCurrency(value as number)} />
            <Legend />
            <Line type="monotone" dataKey={selectedMetric} stroke="#3B82F6" strokeWidth={3} />
          </LineChart>
        );

      case 'area':
        return (
          <AreaChart {...commonProps}>
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis dataKey="timestamp" />
            <YAxis />
            <Tooltip formatter={(value) => formatCurrency(value as number)} />
            <Area type="monotone" dataKey={selectedMetric} stroke="#3B82F6" fill="#3B82F6" fillOpacity={0.3} />
          </AreaChart>
        );

      case 'bar':
        return (
          <BarChart {...commonProps}>
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis dataKey="timestamp" />
            <YAxis />
            <Tooltip formatter={(value) => formatCurrency(value as number)} />
            <Bar dataKey={selectedMetric} fill="#3B82F6" />
          </BarChart>
        );

      case 'pie':
        return (
          <PieChart>
            <Pie
              data={marketplaceData}
              cx="50%"
              cy="50%"
              outerRadius={80}
              fill="#8884d8"
              dataKey="revenue"
              label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
            >
              {marketplaceData.map((entry, index) => (
                <Cell key={`cell-${index}`} fill={entry.color} />
              ))}
            </Pie>
            <Tooltip formatter={(value) => formatCurrency(value as number)} />
          </PieChart>
        );

      case 'composed':
        return (
          <ComposedChart {...commonProps}>
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis dataKey="timestamp" />
            <YAxis />
            <Tooltip />
            <Legend />
            <Area type="monotone" dataKey="revenue" fill="#3B82F6" fillOpacity={0.3} />
            <Bar dataKey="orders" fill="#10B981" />
            <Line type="monotone" dataKey="profit" stroke="#F59E0B" strokeWidth={3} />
          </ComposedChart>
        );

      case 'radial':
        return (
          <RadialBarChart cx="50%" cy="50%" innerRadius="10%" outerRadius="80%" data={marketplaceData}>
            <RadialBar dataKey="marketShare" cornerRadius={10} fill="#3B82F6" />
            <Tooltip />
          </RadialBarChart>
        );

      case 'scatter':
        return (
          <ScatterChart {...commonProps}>
            <CartesianGrid />
            <XAxis dataKey="visitors" name="Ziyaretçi" />
            <YAxis dataKey="revenue" name="Gelir" />
            <Tooltip cursor={{ strokeDasharray: '3 3' }} />
            <Scatter name="Performans" data={chartData} fill="#3B82F6" />
          </ScatterChart>
        );

      default:
        // Fallback chart to prevent null return
        return (
          <BarChart {...commonProps}>
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis dataKey="timestamp" />
            <YAxis />
            <Tooltip formatter={(value) => formatCurrency(value as number)} />
            <Bar dataKey={selectedMetric} fill="#3B82F6" />
          </BarChart>
        );
    }
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-4 text-lg text-gray-600">Gelişmiş analitik veriler yükleniyor...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">📊 Gelişmiş Analitik</h1>
          <p className="text-sm text-gray-500 mt-1">AI destekli iş zekası ve gerçek zamanlı analiz</p>
        </div>
        <div className="flex space-x-4">
          <button
            onClick={() => setIsRealTime(!isRealTime)}
            className={`px-4 py-2 rounded-lg flex items-center space-x-2 ${
              isRealTime 
                ? 'bg-green-500 text-white' 
                : 'bg-gray-200 text-gray-700'
            }`}
          >
            <span className={isRealTime ? 'animate-pulse' : ''}>🔴</span>
            <span>{isRealTime ? 'Canlı' : 'Durgun'}</span>
          </button>
          <select
            value={timeRange}
            onChange={(e) => setTimeRange(e.target.value as TimeRange)}
            className="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm"
          >
            <option value="1h">Son 1 Saat</option>
            <option value="24h">Son 24 Saat</option>
            <option value="7d">Son 7 Gün</option>
            <option value="30d">Son 30 Gün</option>
            <option value="90d">Son 90 Gün</option>
            <option value="1y">Son 1 Yıl</option>
          </select>
        </div>
      </div>

      {/* Performance Metrics */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div className="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-blue-100">Toplam Gelir</p>
              <p className="text-2xl font-bold">{formatCurrency(metrics.revenue)}</p>
              <p className="text-sm text-blue-100 mt-1">↗ +15.2% bu ay</p>
            </div>
            <div className="text-3xl">💰</div>
          </div>
        </div>

        <div className="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-green-100">Toplam Sipariş</p>
              <p className="text-2xl font-bold">{formatNumber(metrics.orders)}</p>
              <p className="text-sm text-green-100 mt-1">↗ +12.8% bu ay</p>
            </div>
            <div className="text-3xl">📦</div>
          </div>
        </div>

        <div className="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-purple-100">Ziyaretçi</p>
              <p className="text-2xl font-bold">{formatNumber(metrics.visitors)}</p>
              <p className="text-sm text-purple-100 mt-1">↗ +8.5% bu ay</p>
            </div>
            <div className="text-3xl">👥</div>
          </div>
        </div>

        <div className="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-orange-100">Dönüşüm Oranı</p>
              <p className="text-2xl font-bold">{formatPercentage(metrics.conversionRate)}</p>
              <p className="text-sm text-orange-100 mt-1">↗ +2.1% bu ay</p>
            </div>
            <div className="text-3xl">📈</div>
          </div>
        </div>
      </div>

      {/* Chart Controls & Visualization */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div className="flex justify-between items-center mb-6">
          <h2 className="text-lg font-semibold text-gray-900">Dinamik Görselleştirme</h2>
          <div className="flex space-x-4">
            <select
              value={selectedChart}
              onChange={(e) => setSelectedChart(e.target.value as ChartType)}
              className="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm"
            >
              <option value="line">Çizgi Grafik</option>
              <option value="area">Alan Grafik</option>
              <option value="bar">Çubuk Grafik</option>
              <option value="pie">Pasta Grafik</option>
              <option value="composed">Karma Grafik</option>
              <option value="radial">Radyal Grafik</option>
              <option value="scatter">Dağılım Grafik</option>
            </select>
            <select
              value={selectedMetric}
              onChange={(e) => setSelectedMetric(e.target.value as MetricType)}
              className="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm"
            >
              <option value="revenue">Gelir</option>
              <option value="orders">Sipariş</option>
              <option value="visitors">Ziyaretçi</option>
              <option value="conversionRate">Dönüşüm Oranı</option>
            </select>
          </div>
        </div>
        <ResponsiveContainer width="100%" height={400}>
          {renderChart()}
        </ResponsiveContainer>
      </div>

      {/* AI Insights */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 className="text-lg font-semibold text-gray-900 mb-4">🤖 AI Öngörüleri</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {aiInsights.map((insight, index) => (
            <div key={index} className={`p-4 rounded-lg border ${getInsightColor(insight.type)}`}>
              <div className="flex items-start space-x-3">
                <span className="text-2xl">{getInsightIcon(insight.type)}</span>
                <div className="flex-1">
                  <h3 className="font-semibold">{insight.title}</h3>
                  <p className="text-sm mt-1">{insight.description}</p>
                  {insight.action && (
                    <button className="mt-2 text-xs bg-white bg-opacity-50 px-2 py-1 rounded">
                      {insight.action}
                    </button>
                  )}
                </div>
                <span className={`text-xs px-2 py-1 rounded ${
                  insight.impact === 'high' ? 'bg-red-100 text-red-800' :
                  insight.impact === 'medium' ? 'bg-yellow-100 text-yellow-800' :
                  'bg-green-100 text-green-800'
                }`}>
                  {insight.impact === 'high' ? 'Yüksek' : insight.impact === 'medium' ? 'Orta' : 'Düşük'}
                </span>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Marketplace & Category Performance */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Marketplace Performance */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Pazaryeri Performansı</h2>
          <div className="space-y-4">
            {marketplaceData.map((marketplace, index) => (
              <div key={index} className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div className="flex items-center space-x-3">
                  <div 
                    className="w-4 h-4 rounded-full" 
                    style={{ backgroundColor: marketplace.color }}
                  ></div>
                  <div>
                    <p className="font-medium text-gray-900">{marketplace.name}</p>
                    <p className="text-sm text-gray-500">{marketplace.orders} sipariş • %{marketplace.marketShare} pazar payı</p>
                  </div>
                </div>
                <div className="text-right">
                  <p className="font-semibold text-gray-900">{formatCurrency(marketplace.revenue)}</p>
                  <p className={`text-sm ${marketplace.growth >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                    {marketplace.growth >= 0 ? '+' : ''}{marketplace.growth}%
                  </p>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Category Performance */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Kategori Performansı</h2>
          <div className="space-y-4">
            {categoryData.map((category, index) => (
              <div key={index} className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div className="flex items-center space-x-3">
                  <div className="text-2xl">
                    {category.trend === 'up' ? '📈' : category.trend === 'down' ? '📉' : '➡️'}
                  </div>
                  <div>
                    <p className="font-medium text-gray-900">{category.category}</p>
                    <p className="text-sm text-gray-500">{category.orders} sipariş • %{category.margin} kar marjı</p>
                  </div>
                </div>
                <div className="text-right">
                  <p className="font-semibold text-gray-900">{formatCurrency(category.revenue)}</p>
                  <p className="text-sm text-green-600">{formatCurrency(category.profit)} kar</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Quick Actions */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 className="text-lg font-semibold text-gray-900 mb-4">Hızlı İşlemler</h2>
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <button className="flex items-center justify-center space-x-2 bg-blue-50 border border-blue-200 rounded-lg p-4 hover:bg-blue-100 transition-colors">
            <span className="text-2xl">📊</span>
            <span className="font-medium text-blue-800">Detaylı Rapor</span>
          </button>
          <button className="flex items-center justify-center space-x-2 bg-green-50 border border-green-200 rounded-lg p-4 hover:bg-green-100 transition-colors">
            <span className="text-2xl">📈</span>
            <span className="font-medium text-green-800">Trend Analizi</span>
          </button>
          <button className="flex items-center justify-center space-x-2 bg-purple-50 border border-purple-200 rounded-lg p-4 hover:bg-purple-100 transition-colors">
            <span className="text-2xl">🤖</span>
            <span className="font-medium text-purple-800">AI Önerileri</span>
          </button>
          <button className="flex items-center justify-center space-x-2 bg-orange-50 border border-orange-200 rounded-lg p-4 hover:bg-orange-100 transition-colors">
            <span className="text-2xl">📤</span>
            <span className="font-medium text-orange-800">Dışa Aktar</span>
          </button>
        </div>
      </div>
    </div>
  );
};

export default AdvancedAnalytics; 