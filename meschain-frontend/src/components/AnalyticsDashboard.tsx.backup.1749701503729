import React, { useState, useEffect } from 'react';
import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  PieChart,
  Pie,
  Cell,
  Area,
  Line,
  ComposedChart
} from 'recharts';

interface AnalyticsData {
  totalRevenue: number;
  totalOrders: number;
  averageOrderValue: number;
  conversionRate: number;
  topSellingProducts: ProductAnalytics[];
  marketplacePerformance: MarketplaceAnalytics[];
  revenueByMonth: RevenueData[];
  customerSegments: CustomerSegment[];
  profitMargins: ProfitData[];
}

interface ProductAnalytics {
  id: string;
  name: string;
  category: string;
  totalSales: number;
  revenue: number;
  profit: number;
  marketplace: string;
  trend: 'up' | 'down' | 'stable';
}

interface MarketplaceAnalytics {
  marketplace: string;
  revenue: number;
  orders: number;
  averageOrderValue: number;
  conversionRate: number;
  commission: number;
  profit: number;
  growth: number;
  color: string;
}

interface RevenueData {
  month: string;
  revenue: number;
  orders: number;
  profit: number;
  expenses: number;
}

interface CustomerSegment {
  segment: string;
  count: number;
  revenue: number;
  averageOrderValue: number;
  color: string;
}

interface ProfitData {
  category: string;
  margin: number;
  revenue: number;
  cost: number;
  profit: number;
}

const AnalyticsDashboard: React.FC = () => {
  const [analyticsData, setAnalyticsData] = useState<AnalyticsData>({
    totalRevenue: 0,
    totalOrders: 0,
    averageOrderValue: 0,
    conversionRate: 0,
    topSellingProducts: [],
    marketplacePerformance: [],
    revenueByMonth: [],
    customerSegments: [],
    profitMargins: []
  });

  const [selectedTimeRange, setSelectedTimeRange] = useState<'7d' | '30d' | '90d' | '1y'>('30d');
  const [selectedMarketplace, setSelectedMarketplace] = useState<string>('all');
  const [isLoading, setIsLoading] = useState(true);

  // Load analytics data
  useEffect(() => {
    loadAnalyticsData();
  }, [selectedTimeRange, selectedMarketplace]);

  const loadAnalyticsData = async () => {
    setIsLoading(true);
    try {
      // Simulate API call - replace with real API
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      const mockData: AnalyticsData = {
        totalRevenue: 2450000,
        totalOrders: 1847,
        averageOrderValue: 1326,
        conversionRate: 3.2,
        topSellingProducts: [
          {
            id: '1',
            name: 'Wireless Bluetooth Kulaklık',
            category: 'Elektronik',
            totalSales: 245,
            revenue: 122500,
            profit: 36750,
            marketplace: 'Trendyol',
            trend: 'up'
          },
          {
            id: '2',
            name: 'Organik Çay Seti',
            category: 'Gıda',
            totalSales: 189,
            revenue: 94500,
            profit: 28350,
            marketplace: 'Hepsiburada',
            trend: 'up'
          },
          {
            id: '3',
            name: 'Doğum Günü Çiçek Buketi',
            category: 'Hediye',
            totalSales: 156,
            revenue: 78000,
            profit: 23400,
            marketplace: 'ÇiçekSepeti',
            trend: 'stable'
          }
        ],
        marketplacePerformance: [
          {
            marketplace: 'Trendyol',
            revenue: 980000,
            orders: 742,
            averageOrderValue: 1321,
            conversionRate: 3.8,
            commission: 49000,
            profit: 294000,
            growth: 15.2,
            color: '#F27A1A'
          },
          {
            marketplace: 'Hepsiburada',
            revenue: 756000,
            orders: 567,
            averageOrderValue: 1333,
            conversionRate: 3.1,
            commission: 37800,
            profit: 226800,
            growth: 12.8,
            color: '#FF6000'
          },
          {
            marketplace: 'ÇiçekSepeti',
            revenue: 420000,
            orders: 315,
            averageOrderValue: 1333,
            conversionRate: 2.9,
            commission: 21000,
            profit: 126000,
            growth: 8.5,
            color: '#EC4899'
          },
          {
            marketplace: 'N11',
            revenue: 294000,
            orders: 223,
            averageOrderValue: 1318,
            conversionRate: 2.7,
            commission: 14700,
            profit: 88200,
            growth: 5.2,
            color: '#7B2CBF'
          }
        ],
        revenueByMonth: [
          { month: 'Ocak', revenue: 180000, orders: 135, profit: 54000, expenses: 126000 },
          { month: 'Şubat', revenue: 210000, orders: 158, profit: 63000, expenses: 147000 },
          { month: 'Mart', revenue: 245000, orders: 184, profit: 73500, expenses: 171500 },
          { month: 'Nisan', revenue: 280000, orders: 210, profit: 84000, expenses: 196000 },
          { month: 'Mayıs', revenue: 320000, orders: 240, profit: 96000, expenses: 224000 },
          { month: 'Haziran', revenue: 365000, orders: 274, profit: 109500, expenses: 255500 }
        ],
        customerSegments: [
          { segment: 'Premium Müşteriler', count: 156, revenue: 780000, averageOrderValue: 5000, color: '#10B981' },
          { segment: 'Düzenli Müşteriler', count: 423, revenue: 1058000, averageOrderValue: 2500, color: '#3B82F6' },
          { segment: 'Yeni Müşteriler', count: 789, revenue: 612000, averageOrderValue: 775, color: '#F59E0B' }
        ],
        profitMargins: [
          { category: 'Elektronik', margin: 25, revenue: 980000, cost: 735000, profit: 245000 },
          { category: 'Gıda', margin: 35, revenue: 560000, cost: 364000, profit: 196000 },
          { category: 'Hediye', margin: 40, revenue: 420000, cost: 252000, profit: 168000 },
          { category: 'Ev & Yaşam', margin: 30, revenue: 490000, cost: 343000, profit: 147000 }
        ]
      };

      setAnalyticsData(mockData);
    } catch (error) {
      console.error('Analytics data loading error:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('tr-TR', {
      style: 'currency',
      currency: 'TRY',
      minimumFractionDigits: 0
    }).format(amount);
  };

  const formatPercentage = (value: number) => {
    return `${value.toFixed(1)}%`;
  };

  const getTrendIcon = (trend: string) => {
    switch (trend) {
      case 'up': return '📈';
      case 'down': return '📉';
      default: return '➡️';
    }
  };

  const StatCard: React.FC<{
    title: string;
    value: string;
    change?: number;
    icon: string;
    color: string;
  }> = ({ title, value, change, icon, color }) => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div className="flex items-center justify-between">
        <div>
          <p className="text-sm font-medium text-gray-600">{title}</p>
          <p className="text-2xl font-bold text-gray-900">{value}</p>
          {change !== undefined && (
            <div className="flex items-center mt-2">
              <span className={`text-sm font-medium ${change >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                {change >= 0 ? '↗' : '↘'} {change >= 0 ? '+' : ''}{change}%
              </span>
              <span className="text-xs text-gray-500 ml-2">son 30 gün</span>
            </div>
          )}
        </div>
        <div className="p-3 rounded-full text-2xl" style={{ backgroundColor: color + '20' }}>
          {icon}
        </div>
      </div>
    </div>
  );

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-4 text-lg text-gray-600">Analytics yükleniyor...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Analytics Dashboard</h1>
          <p className="text-sm text-gray-500 mt-1">İş zekası ve performans analizi</p>
        </div>
        <div className="flex space-x-4">
          <select
            value={selectedTimeRange}
            onChange={(e) => setSelectedTimeRange(e.target.value as any)}
            className="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm"
          >
            <option value="7d">Son 7 Gün</option>
            <option value="30d">Son 30 Gün</option>
            <option value="90d">Son 90 Gün</option>
            <option value="1y">Son 1 Yıl</option>
          </select>
          <select
            value={selectedMarketplace}
            onChange={(e) => setSelectedMarketplace(e.target.value)}
            className="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm"
          >
            <option value="all">Tüm Pazaryerleri</option>
            <option value="trendyol">Trendyol</option>
            <option value="hepsiburada">Hepsiburada</option>
            <option value="ciceksepeti">ÇiçekSepeti</option>
            <option value="n11">N11</option>
          </select>
          <button className="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
            <span>📊</span>
            <span>Rapor İndir</span>
          </button>
        </div>
      </div>

      {/* Key Metrics */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard
          title="Toplam Gelir"
          value={formatCurrency(analyticsData.totalRevenue)}
          change={15.2}
          icon="💰"
          color="#10B981"
        />
        <StatCard
          title="Toplam Sipariş"
          value={analyticsData.totalOrders.toLocaleString()}
          change={12.8}
          icon="📦"
          color="#3B82F6"
        />
        <StatCard
          title="Ortalama Sipariş Değeri"
          value={formatCurrency(analyticsData.averageOrderValue)}
          change={8.5}
          icon="🛒"
          color="#8B5CF6"
        />
        <StatCard
          title="Dönüşüm Oranı"
          value={formatPercentage(analyticsData.conversionRate)}
          change={2.1}
          icon="📈"
          color="#F59E0B"
        />
      </div>

      {/* Revenue Trend */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 className="text-lg font-semibold text-gray-900 mb-4">Gelir Trendi</h2>
        <ResponsiveContainer width="100%" height={350}>
          <ComposedChart data={analyticsData.revenueByMonth}>
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis dataKey="month" />
            <YAxis />
            <Tooltip formatter={(value, name) => [
              name === 'revenue' ? formatCurrency(value as number) : value,
              name === 'revenue' ? 'Gelir' : name === 'orders' ? 'Sipariş' : 'Kar'
            ]} />
            <Area type="monotone" dataKey="revenue" fill="#3B82F6" fillOpacity={0.3} />
            <Bar dataKey="orders" fill="#10B981" />
            <Line type="monotone" dataKey="profit" stroke="#F59E0B" strokeWidth={3} />
          </ComposedChart>
        </ResponsiveContainer>
      </div>

      {/* Marketplace Performance & Customer Segments */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Marketplace Performance */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Pazaryeri Performansı</h2>
          <div className="space-y-4">
            {analyticsData.marketplacePerformance.map((marketplace, index) => (
              <div key={index} className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div className="flex items-center space-x-3">
                  <div 
                    className="w-4 h-4 rounded-full" 
                    style={{ backgroundColor: marketplace.color }}
                  ></div>
                  <div>
                    <p className="font-medium text-gray-900">{marketplace.marketplace}</p>
                    <p className="text-sm text-gray-500">{marketplace.orders} sipariş</p>
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

        {/* Customer Segments */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Müşteri Segmentleri</h2>
          <ResponsiveContainer width="100%" height={250}>
            <PieChart>
              <Pie
                data={analyticsData.customerSegments}
                cx="50%"
                cy="50%"
                outerRadius={80}
                fill="#8884d8"
                dataKey="revenue"
                label={({ segment, percent }) => `${segment} ${(percent * 100).toFixed(0)}%`}
              >
                {analyticsData.customerSegments.map((entry, index) => (
                  <Cell key={`cell-${index}`} fill={entry.color} />
                ))}
              </Pie>
              <Tooltip formatter={(value) => formatCurrency(value as number)} />
            </PieChart>
          </ResponsiveContainer>
        </div>
      </div>

      {/* Top Products & Profit Margins */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Top Selling Products */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">En Çok Satan Ürünler</h2>
          <div className="space-y-4">
            {analyticsData.topSellingProducts.map((product, index) => (
              <div key={product.id} className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div className="flex items-center space-x-3">
                  <div className="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                    {index + 1}
                  </div>
                  <div>
                    <p className="font-medium text-gray-900">{product.name}</p>
                    <p className="text-sm text-gray-500">{product.category} • {product.marketplace}</p>
                  </div>
                </div>
                <div className="text-right">
                  <div className="flex items-center space-x-2">
                    <span className="text-lg">{getTrendIcon(product.trend)}</span>
                    <div>
                      <p className="font-semibold text-gray-900">{formatCurrency(product.revenue)}</p>
                      <p className="text-sm text-gray-500">{product.totalSales} satış</p>
                    </div>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Profit Margins */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Kategori Kar Marjları</h2>
          <ResponsiveContainer width="100%" height={250}>
            <BarChart data={analyticsData.profitMargins}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="category" />
              <YAxis />
              <Tooltip formatter={(value, name) => [
                name === 'margin' ? formatPercentage(value as number) : formatCurrency(value as number),
                name === 'margin' ? 'Kar Marjı' : name === 'revenue' ? 'Gelir' : 'Kar'
              ]} />
              <Bar dataKey="margin" fill="#10B981" />
            </BarChart>
          </ResponsiveContainer>
        </div>
      </div>

      {/* Export Options */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 className="text-lg font-semibold text-gray-900 mb-4">Rapor Dışa Aktarma</h2>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <button className="flex items-center justify-center space-x-2 bg-green-50 border border-green-200 rounded-lg p-4 hover:bg-green-100 transition-colors">
            <span className="text-2xl">📊</span>
            <span className="font-medium text-green-800">Excel Raporu</span>
          </button>
          <button className="flex items-center justify-center space-x-2 bg-red-50 border border-red-200 rounded-lg p-4 hover:bg-red-100 transition-colors">
            <span className="text-2xl">📄</span>
            <span className="font-medium text-red-800">PDF Raporu</span>
          </button>
          <button className="flex items-center justify-center space-x-2 bg-blue-50 border border-blue-200 rounded-lg p-4 hover:bg-blue-100 transition-colors">
            <span className="text-2xl">📈</span>
            <span className="font-medium text-blue-800">Detaylı Analiz</span>
          </button>
        </div>
      </div>
    </div>
  );
};

export default AnalyticsDashboard; 