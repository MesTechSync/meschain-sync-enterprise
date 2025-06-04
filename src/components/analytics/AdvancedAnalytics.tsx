import React, { useState, useEffect, useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import { useLanguage } from '../../hooks/useLanguage';
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
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
  ComposedChart,
  RadialBarChart,
  RadialBar,
  ScatterChart,
  Scatter
} from 'recharts';
import {
  CalendarIcon,
  ChartBarIcon,
  CurrencyDollarIcon,
  ShoppingCartIcon,
  TrendingUpIcon,
  TrendingDownIcon,
  ArrowPathIcon,
  FunnelIcon,
  EyeIcon,
  DocumentArrowDownIcon,
  Cog6ToothIcon
} from '@heroicons/react/24/outline';
import { format, subDays, startOfDay, endOfDay, isWithinInterval } from 'date-fns';
import { tr, enUS } from 'date-fns/locale';
import _ from 'lodash';
import toast from 'react-hot-toast';

interface AnalyticsData {
  date: string;
  sales: number;
  orders: number;
  revenue: number;
  profit: number;
  visitors: number;
  conversionRate: number;
  marketplace: string;
  category: string;
  region: string;
}

interface MetricCard {
  title: string;
  value: number;
  change: number;
  trend: 'up' | 'down' | 'stable';
  format: 'currency' | 'number' | 'percentage';
  icon: React.ComponentType<any>;
  color: string;
}

interface FilterOptions {
  dateRange: {
    start: Date;
    end: Date;
  };
  marketplaces: string[];
  categories: string[];
  regions: string[];
  metrics: string[];
}

const AdvancedAnalytics: React.FC = () => {
  const { t, i18n } = useTranslation();
  const { formatCurrency, formatNumber, getCurrentLanguageData } = useLanguage();
  
  const [data, setData] = useState<AnalyticsData[]>([]);
  const [filteredData, setFilteredData] = useState<AnalyticsData[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [selectedChart, setSelectedChart] = useState<'line' | 'area' | 'bar' | 'pie' | 'composed' | 'radial' | 'scatter'>('line');
  const [selectedMetric, setSelectedMetric] = useState<'sales' | 'orders' | 'revenue' | 'profit' | 'visitors' | 'conversionRate'>('revenue');
  const [filters, setFilters] = useState<FilterOptions>({
    dateRange: {
      start: subDays(new Date(), 30),
      end: new Date()
    },
    marketplaces: [],
    categories: [],
    regions: [],
    metrics: ['revenue', 'orders', 'sales']
  });
  const [timeRange, setTimeRange] = useState<'7d' | '30d' | '90d' | '1y'>('30d');
  const [isRealTime, setIsRealTime] = useState(false);

  const locale = i18n.language === 'tr' ? tr : enUS;

  // Generate mock data
  const generateMockData = (): AnalyticsData[] => {
    const marketplaces = ['Amazon', 'eBay', 'Trendyol', 'N11', 'Hepsiburada', 'Ozon'];
    const categories = ['Electronics', 'Fashion', 'Home', 'Sports', 'Books', 'Beauty'];
    const regions = ['Europe', 'North America', 'Asia', 'Middle East', 'South America'];
    const data: AnalyticsData[] = [];

    for (let i = 0; i < 90; i++) {
      const date = format(subDays(new Date(), i), 'yyyy-MM-dd');
      const baseRevenue = 10000 + Math.random() * 15000;
      const seasonalMultiplier = 1 + 0.3 * Math.sin((i / 30) * Math.PI);
      
      data.push({
        date,
        sales: Math.floor(baseRevenue * 0.8 * seasonalMultiplier),
        orders: Math.floor(50 + Math.random() * 100),
        revenue: Math.floor(baseRevenue * seasonalMultiplier),
        profit: Math.floor(baseRevenue * 0.25 * seasonalMultiplier),
        visitors: Math.floor(1000 + Math.random() * 2000),
        conversionRate: 2 + Math.random() * 8,
        marketplace: marketplaces[Math.floor(Math.random() * marketplaces.length)],
        category: categories[Math.floor(Math.random() * categories.length)],
        region: regions[Math.floor(Math.random() * regions.length)]
      });
    }

    return data.reverse();
  };

  useEffect(() => {
    const loadData = async () => {
      setIsLoading(true);
      try {
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 1000));
        const mockData = generateMockData();
        setData(mockData);
        setFilteredData(mockData);
      } catch (error) {
        toast.error(t('errors.general'));
      } finally {
        setIsLoading(false);
      }
    };

    loadData();
  }, [t]);

  // Apply filters
  useEffect(() => {
    let filtered = data.filter(item => {
      const itemDate = new Date(item.date);
      const isInDateRange = isWithinInterval(itemDate, {
        start: startOfDay(filters.dateRange.start),
        end: endOfDay(filters.dateRange.end)
      });

      const isInMarketplaces = filters.marketplaces.length === 0 || 
        filters.marketplaces.includes(item.marketplace);
      
      const isInCategories = filters.categories.length === 0 || 
        filters.categories.includes(item.category);
      
      const isInRegions = filters.regions.length === 0 || 
        filters.regions.includes(item.region);

      return isInDateRange && isInMarketplaces && isInCategories && isInRegions;
    });

    setFilteredData(filtered);
  }, [data, filters]);

  // Calculate metrics
  const metrics = useMemo((): MetricCard[] => {
    if (filteredData.length === 0) return [];

    const currentPeriod = filteredData.slice(-7);
    const previousPeriod = filteredData.slice(-14, -7);

    const calculateChange = (current: number, previous: number) => {
      if (previous === 0) return 0;
      return ((current - previous) / previous) * 100;
    };

    const currentRevenue = _.sumBy(currentPeriod, 'revenue');
    const previousRevenue = _.sumBy(previousPeriod, 'revenue');
    const revenueChange = calculateChange(currentRevenue, previousRevenue);

    const currentOrders = _.sumBy(currentPeriod, 'orders');
    const previousOrders = _.sumBy(previousPeriod, 'orders');
    const ordersChange = calculateChange(currentOrders, previousOrders);

    const currentSales = _.sumBy(currentPeriod, 'sales');
    const previousSales = _.sumBy(previousPeriod, 'sales');
    const salesChange = calculateChange(currentSales, previousSales);

    const currentProfit = _.sumBy(currentPeriod, 'profit');
    const previousProfit = _.sumBy(previousPeriod, 'profit');
    const profitChange = calculateChange(currentProfit, previousProfit);

    const currentVisitors = _.sumBy(currentPeriod, 'visitors');
    const previousVisitors = _.sumBy(previousPeriod, 'visitors');
    const visitorsChange = calculateChange(currentVisitors, previousVisitors);

    const currentConversion = _.meanBy(currentPeriod, 'conversionRate');
    const previousConversion = _.meanBy(previousPeriod, 'conversionRate');
    const conversionChange = calculateChange(currentConversion, previousConversion);

    return [
      {
        title: t('dashboard.totalRevenue'),
        value: currentRevenue,
        change: revenueChange,
        trend: revenueChange > 0 ? 'up' : revenueChange < 0 ? 'down' : 'stable',
        format: 'currency',
        icon: CurrencyDollarIcon,
        color: 'blue'
      },
      {
        title: t('dashboard.totalOrders'),
        value: currentOrders,
        change: ordersChange,
        trend: ordersChange > 0 ? 'up' : ordersChange < 0 ? 'down' : 'stable',
        format: 'number',
        icon: ShoppingCartIcon,
        color: 'green'
      },
      {
        title: t('dashboard.totalSales'),
        value: currentSales,
        change: salesChange,
        trend: salesChange > 0 ? 'up' : salesChange < 0 ? 'down' : 'stable',
        format: 'currency',
        icon: ChartBarIcon,
        color: 'purple'
      },
      {
        title: t('reports.profitReport'),
        value: currentProfit,
        change: profitChange,
        trend: profitChange > 0 ? 'up' : profitChange < 0 ? 'down' : 'stable',
        format: 'currency',
        icon: TrendingUpIcon,
        color: 'yellow'
      },
      {
        title: t('analytics.visitors'),
        value: currentVisitors,
        change: visitorsChange,
        trend: visitorsChange > 0 ? 'up' : visitorsChange < 0 ? 'down' : 'stable',
        format: 'number',
        icon: EyeIcon,
        color: 'indigo'
      },
      {
        title: t('analytics.conversionRate'),
        value: currentConversion,
        change: conversionChange,
        trend: conversionChange > 0 ? 'up' : conversionChange < 0 ? 'down' : 'stable',
        format: 'percentage',
        icon: FunnelIcon,
        color: 'pink'
      }
    ];
  }, [filteredData, t]);

  // Chart data processing
  const chartData = useMemo(() => {
    return filteredData.map(item => ({
      ...item,
      formattedDate: format(new Date(item.date), 'MMM dd', { locale })
    }));
  }, [filteredData, locale]);

  // Marketplace distribution data
  const marketplaceData = useMemo(() => {
    const grouped = _.groupBy(filteredData, 'marketplace');
    return Object.entries(grouped).map(([marketplace, items]) => ({
      name: marketplace,
      value: _.sumBy(items, selectedMetric),
      count: items.length
    }));
  }, [filteredData, selectedMetric]);

  // Category performance data
  const categoryData = useMemo(() => {
    const grouped = _.groupBy(filteredData, 'category');
    return Object.entries(grouped).map(([category, items]) => ({
      category,
      revenue: _.sumBy(items, 'revenue'),
      orders: _.sumBy(items, 'orders'),
      profit: _.sumBy(items, 'profit')
    }));
  }, [filteredData]);

  const formatValue = (value: number, format: 'currency' | 'number' | 'percentage') => {
    switch (format) {
      case 'currency':
        return formatCurrency(value);
      case 'percentage':
        return `${value.toFixed(1)}%`;
      default:
        return formatNumber(value);
    }
  };

  const getTrendIcon = (trend: 'up' | 'down' | 'stable') => {
    switch (trend) {
      case 'up':
        return <TrendingUpIcon className="w-4 h-4 text-green-500" />;
      case 'down':
        return <TrendingDownIcon className="w-4 h-4 text-red-500" />;
      default:
        return <div className="w-4 h-4 bg-gray-400 rounded-full" />;
    }
  };

  const getColorClass = (color: string) => {
    const colors = {
      blue: 'bg-blue-500',
      green: 'bg-green-500',
      purple: 'bg-purple-500',
      yellow: 'bg-yellow-500',
      indigo: 'bg-indigo-500',
      pink: 'bg-pink-500'
    };
    return colors[color as keyof typeof colors] || 'bg-gray-500';
  };

  const COLORS = ['#3B82F6', '#10B981', '#8B5CF6', '#F59E0B', '#6366F1', '#EC4899'];

  const exportData = () => {
    const csvContent = [
      ['Date', 'Revenue', 'Orders', 'Sales', 'Profit', 'Visitors', 'Conversion Rate', 'Marketplace', 'Category', 'Region'],
      ...filteredData.map(item => [
        item.date,
        item.revenue,
        item.orders,
        item.sales,
        item.profit,
        item.visitors,
        item.conversionRate,
        item.marketplace,
        item.category,
        item.region
      ])
    ].map(row => row.join(',')).join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `analytics-${format(new Date(), 'yyyy-MM-dd')}.csv`;
    a.click();
    URL.revokeObjectURL(url);
    
    toast.success(t('success.exported'));
  };

  const getDaysFromRange = (range: string) => {
    switch (range) {
      case '7d': return 7;
      case '30d': return 30;
      case '90d': return 90;
      case '1y': return 365;
      default: return 30;
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
            <XAxis dataKey="formattedDate" />
            <YAxis />
            <Tooltip 
              formatter={(value: number) => [formatValue(value, selectedMetric === 'conversionRate' ? 'percentage' : selectedMetric === 'visitors' || selectedMetric === 'orders' ? 'number' : 'currency'), t(`dashboard.${selectedMetric}`)]}
            />
            <Legend />
            <Line 
              type="monotone" 
              dataKey={selectedMetric} 
              stroke="#3B82F6" 
              strokeWidth={2}
              dot={{ fill: '#3B82F6' }}
            />
          </LineChart>
        );

      case 'area':
        return (
          <AreaChart {...commonProps}>
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis dataKey="formattedDate" />
            <YAxis />
            <Tooltip 
              formatter={(value: number) => [formatValue(value, selectedMetric === 'conversionRate' ? 'percentage' : selectedMetric === 'visitors' || selectedMetric === 'orders' ? 'number' : 'currency'), t(`dashboard.${selectedMetric}`)]}
            />
            <Area 
              type="monotone" 
              dataKey={selectedMetric} 
              stroke="#3B82F6" 
              fill="#3B82F6" 
              fillOpacity={0.3}
            />
          </AreaChart>
        );

      case 'bar':
        return (
          <BarChart {...commonProps}>
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis dataKey="formattedDate" />
            <YAxis />
            <Tooltip 
              formatter={(value: number) => [formatValue(value, selectedMetric === 'conversionRate' ? 'percentage' : selectedMetric === 'visitors' || selectedMetric === 'orders' ? 'number' : 'currency'), t(`dashboard.${selectedMetric}`)]}
            />
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
              labelLine={false}
              label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
              outerRadius={120}
              fill="#8884d8"
              dataKey="value"
            >
              {marketplaceData.map((entry, index) => (
                <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
              ))}
            </Pie>
            <Tooltip 
              formatter={(value: number) => [formatValue(value, selectedMetric === 'conversionRate' ? 'percentage' : selectedMetric === 'visitors' || selectedMetric === 'orders' ? 'number' : 'currency'), t(`dashboard.${selectedMetric}`)]}
            />
          </PieChart>
        );

      case 'composed':
        return (
          <ComposedChart {...commonProps}>
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis dataKey="formattedDate" />
            <YAxis />
            <Tooltip />
            <Legend />
            <Bar dataKey="orders" fill="#10B981" />
            <Line type="monotone" dataKey="revenue" stroke="#3B82F6" strokeWidth={2} />
            <Area type="monotone" dataKey="profit" fill="#8B5CF6" fillOpacity={0.3} />
          </ComposedChart>
        );

      case 'radial':
        return (
          <RadialBarChart width={400} height={300} cx={200} cy={150} innerRadius="10%" outerRadius="80%" data={marketplaceData}>
            <RadialBar minAngle={15} label={{ position: 'insideStart', fill: '#fff' }} background clockWise dataKey="value" />
            <Legend iconSize={18} layout="vertical" verticalAlign="middle" wrapperStyle={{ paddingLeft: '20px' }} />
            <Tooltip />
          </RadialBarChart>
        );

      case 'scatter':
        return (
          <ScatterChart {...commonProps}>
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis dataKey="visitors" name="Visitors" />
            <YAxis dataKey="revenue" name="Revenue" />
            <Tooltip cursor={{ strokeDasharray: '3 3' }} />
            <Scatter name="Revenue vs Visitors" data={chartData} fill="#3B82F6" />
          </ScatterChart>
        );

      default:
        return null;
    }
  };

  useEffect(() => {
    if (isRealTime) {
      const interval = setInterval(() => {
        // Simulate real-time data updates
        console.log('Updating real-time data...');
      }, 5000);

      return () => clearInterval(interval);
    }
  }, [isRealTime]);

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
        <span className="ml-4 text-lg text-gray-600">{t('common.loading')}</span>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="bg-white shadow rounded-lg p-6">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">
              {t('analytics.advancedAnalytics')}
            </h1>
            <p className="mt-1 text-sm text-gray-600">
              {t('analytics.comprehensiveInsights')}
            </p>
          </div>
          <div className="flex items-center space-x-4">
            <button
              onClick={exportData}
              className="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              <DocumentArrowDownIcon className="w-4 h-4" />
              <span>{t('common.export')}</span>
            </button>
            <button className="flex items-center space-x-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
              <Cog6ToothIcon className="w-4 h-4" />
              <span>{t('common.settings')}</span>
            </button>
          </div>
        </div>
      </div>

      {/* Metrics Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6">
        {metrics.map((metric, index) => (
          <div key={index} className="bg-white overflow-hidden shadow rounded-lg">
            <div className="p-5">
              <div className="flex items-center">
                <div className="flex-shrink-0">
                  <div className={`w-8 h-8 ${getColorClass(metric.color)} rounded-lg flex items-center justify-center`}>
                    <metric.icon className="w-5 h-5 text-white" />
                  </div>
                </div>
                <div className="ml-5 w-0 flex-1">
                  <dl>
                    <dt className="text-sm font-medium text-gray-500 truncate">
                      {metric.title}
                    </dt>
                    <dd className="flex items-baseline">
                      <div className="text-lg font-semibold text-gray-900">
                        {formatValue(metric.value, metric.format)}
                      </div>
                      <div className="ml-2 flex items-baseline text-sm">
                        {getTrendIcon(metric.trend)}
                        <span className={`ml-1 ${
                          metric.trend === 'up' ? 'text-green-600' : 
                          metric.trend === 'down' ? 'text-red-600' : 'text-gray-500'
                        }`}>
                          {Math.abs(metric.change).toFixed(1)}%
                        </span>
                      </div>
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* Chart Controls */}
      <div className="bg-white shadow rounded-lg p-6">
        <div className="flex flex-wrap items-center justify-between gap-4">
          <div className="flex items-center space-x-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                {t('analytics.chartType')}
              </label>
              <select
                value={selectedChart}
                onChange={(e) => setSelectedChart(e.target.value as any)}
                className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="line">{t('analytics.lineChart')}</option>
                <option value="area">{t('analytics.areaChart')}</option>
                <option value="bar">{t('analytics.barChart')}</option>
                <option value="pie">{t('analytics.pieChart')}</option>
                <option value="composed">{t('analytics.composedChart')}</option>
                <option value="radial">{t('analytics.radialChart')}</option>
                <option value="scatter">{t('analytics.scatterChart')}</option>
              </select>
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                {t('analytics.metric')}
              </label>
              <select
                value={selectedMetric}
                onChange={(e) => setSelectedMetric(e.target.value as any)}
                className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="revenue">{t('dashboard.totalRevenue')}</option>
                <option value="orders">{t('dashboard.totalOrders')}</option>
                <option value="sales">{t('dashboard.totalSales')}</option>
                <option value="profit">{t('reports.profitReport')}</option>
                <option value="visitors">{t('analytics.visitors')}</option>
                <option value="conversionRate">{t('analytics.conversionRate')}</option>
              </select>
            </div>
          </div>

          <div className="flex items-center space-x-2">
            <CalendarIcon className="w-5 h-5 text-gray-400" />
            <span className="text-sm text-gray-600">
              {format(filters.dateRange.start, 'MMM dd, yyyy', { locale })} - {format(filters.dateRange.end, 'MMM dd, yyyy', { locale })}
            </span>
          </div>
        </div>
      </div>

      {/* Main Chart */}
      <div className="bg-white shadow rounded-lg p-6">
        <h3 className="text-lg font-medium text-gray-900 mb-4">
          {t('analytics.performanceTrends')}
        </h3>
        <div className="flex flex-wrap items-center justify-between gap-4">
          <div className="flex items-center space-x-4">
            <button
              onClick={() => setIsRealTime(!isRealTime)}
              className={`flex items-center space-x-2 px-3 py-1 rounded-md text-sm font-medium transition-colors ${
                isRealTime
                  ? 'bg-green-100 text-green-800'
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              }`}
            >
              <ArrowPathIcon className={`w-4 h-4 ${isRealTime ? 'animate-spin' : ''}`} />
              <span>{t('analytics.realTimeData')}</span>
            </button>
          </div>

          <div className="flex items-center space-x-2">
            <Cog6ToothIcon className="w-5 h-5 text-gray-400" />
            <span className="text-sm text-gray-600">{t('common.settings')}</span>
          </div>
        </div>
        <div className="h-96">
          <ResponsiveContainer width="100%" height="100%">
            {renderChart()}
          </ResponsiveContainer>
        </div>
      </div>

      {/* Secondary Charts */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Marketplace Distribution */}
        <div className="bg-white shadow rounded-lg p-6">
          <h3 className="text-lg font-medium text-gray-900 mb-4">
            {t('analytics.marketplaceDistribution')}
          </h3>
          <div className="h-64">
            <ResponsiveContainer width="100%" height="100%">
              <PieChart>
                <Pie
                  data={marketplaceData}
                  cx="50%"
                  cy="50%"
                  innerRadius={40}
                  outerRadius={80}
                  paddingAngle={5}
                  dataKey="value"
                >
                  {marketplaceData.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                  ))}
                </Pie>
                <Tooltip 
                  formatter={(value: number) => [formatValue(value, selectedMetric === 'conversionRate' ? 'percentage' : selectedMetric === 'visitors' || selectedMetric === 'orders' ? 'number' : 'currency'), t(`dashboard.${selectedMetric}`)]}
                />
                <Legend />
              </PieChart>
            </ResponsiveContainer>
          </div>
        </div>

        {/* Category Performance */}
        <div className="bg-white shadow rounded-lg p-6">
          <h3 className="text-lg font-medium text-gray-900 mb-4">
            {t('analytics.categoryPerformance')}
          </h3>
          <div className="h-64">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={categoryData} layout="horizontal">
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis type="number" />
                <YAxis dataKey="category" type="category" width={80} />
                <Tooltip 
                  formatter={(value: number) => [formatCurrency(value), t('dashboard.totalRevenue')]}
                />
                <Bar dataKey="revenue" fill="#3B82F6" />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>
      </div>
    </div>
  );
};

export default AdvancedAnalytics; 