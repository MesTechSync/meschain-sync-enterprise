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
import {
  Box,
  Card,
  CardContent,
  Typography,
  Grid,
  Tabs,
  Tab,
  Select,
  MenuItem,
  FormControl,
  InputLabel,
  Button,
  Chip,
  IconButton,
  Tooltip as MuiTooltip,
  Switch,
  FormControlLabel,
  LinearProgress,
  Alert,
  Divider,
} from '@mui/material';
import {
  Timeline,
  TrendingUp,
  TrendingDown,
  Assessment,
  PieChart as MuiPieChart,
  BarChart as MuiBarChart,
  ShowChart,
  TableChart,
  GetApp,
  Refresh,
  FilterList,
  CompareArrows,
  Insights,
  AutoGraph,
  Analytics as MuiAnalyticsIcon,
} from '@mui/icons-material';

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

interface AdvancedAnalyticsProps {
  data?: AnalyticsData;
  isLoading?: boolean;
  onExport?: (format: string, data: any) => void;
  onRefresh?: () => void;
  onFilterChange?: (filters: any) => void;
}

const AdvancedAnalytics: React.FC<AdvancedAnalyticsProps> = ({
  data,
  isLoading = false,
  onExport,
  onRefresh,
  onFilterChange,
}) => {
  const { t, i18n } = useTranslation();
  const { formatCurrency, formatNumber, getCurrentLanguageData } = useLanguage();
  
  const [activeTab, setActiveTab] = useState(0);
  const [timeRange, setTimeRange] = useState('30d');
  const [selectedMetrics, setSelectedMetrics] = useState(['revenue', 'orders', 'customers']);
  const [comparisonMode, setComparisonMode] = useState(false);
  const [forecastEnabled, setForecastEnabled] = useState(true);
  const [realTimeEnabled, setRealTimeEnabled] = useState(false);

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
      value: _.sumBy(items, selectedMetrics[0]),
      count: items.length
    }));
  }, [filteredData, selectedMetrics]);

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
        return <TrendingUp color="success" />;
      case 'down':
        return <TrendingDown color="error" />;
      default:
        return null;
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
    <Box p={3}>
      {/* Header */}
      <Box display="flex" justifyContent="space-between" alignItems="center" mb={3}>
        <Box>
          <Typography variant="h4" gutterBottom>
            Gelişmiş Analiz & İş Zekası
          </Typography>
          <Typography variant="body1" color="text.secondary">
            Kapsamlı performans analizi ve akıllı öneriler
          </Typography>
        </Box>
        <Box display="flex" gap={1}>
          <FormControlLabel
            control={
              <Switch
                checked={realTimeEnabled}
                onChange={(e) => setRealTimeEnabled(e.target.checked)}
              />
            }
            label="Canlı Veri"
          />
          <FormControl size="small" sx={{ minWidth: 120 }}>
            <InputLabel>Zaman Aralığı</InputLabel>
            <Select
              value={timeRange}
              onChange={(e) => setTimeRange(e.target.value)}
              label="Zaman Aralığı"
            >
              <MenuItem value="7d">Son 7 Gün</MenuItem>
              <MenuItem value="30d">Son 30 Gün</MenuItem>
              <MenuItem value="90d">Son 3 Ay</MenuItem>
              <MenuItem value="1y">Son 1 Yıl</MenuItem>
            </Select>
          </FormControl>
          <Tooltip title="Verileri Yenile">
            <IconButton onClick={onRefresh}>
              <Refresh />
            </IconButton>
          </Tooltip>
          <Button
            variant="contained"
            startIcon={<GetApp />}
            onClick={() => onExport?.('excel', currentData)}
          >
            Export
          </Button>
        </Box>
      </Box>

      {/* Loading */}
      {isLoading && <LinearProgress sx={{ mb: 2 }} />}

      {/* Tabs */}
      <Box sx={{ borderBottom: 1, borderColor: 'divider', mb: 3 }}>
        <Tabs value={activeTab} onChange={(e, newValue) => setActiveTab(newValue)}>
          <Tab icon={<MuiAnalyticsIcon />} label="Genel Bakış" />
          <Tab icon={<ShowChart />} label="Detaylı Analiz" />
          <Tab icon={<Insights />} label="İş Zekası" />
        </Tabs>
      </Box>

      {/* Tab Content */}
      {activeTab === 0 && <OverviewTab />}
      {activeTab === 1 && <DetailedAnalyticsTab />}
      {activeTab === 2 && <InsightsTab />}
    </Box>
  );
};

export default AdvancedAnalytics; 