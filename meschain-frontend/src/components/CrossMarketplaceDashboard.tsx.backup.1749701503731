import React, { useState, useEffect, useCallback } from 'react';
import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  LineChart,
  Line,
  PieChart,
  Pie,
  Cell,
  ComposedChart,
  Area,
  AreaChart
} from 'recharts';
import {
  ShoppingCartIcon,
  CurrencyDollarIcon,
  ChartBarIcon,
  GlobeAltIcon,
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon,
  ClockIcon,
  Cog6ToothIcon,
  ArrowPathIcon,
  EyeIcon,
  PencilIcon,
  PlayIcon,
  PauseIcon,
  StopIcon
} from '@heroicons/react/24/outline';

interface MarketplaceMetrics {
  id: string;
  name: string;
  logo: string;
  status: 'active' | 'inactive' | 'error' | 'maintenance';
  totalOrders: number;
  totalRevenue: number;
  activeProducts: number;
  conversionRate: number;
  averageOrderValue: number;
  growthRate: number;
  lastSync: string;
  apiCalls: number;
  errorRate: number;
  region: string;
}

interface OrderData {
  date: string;
  trendyol: number;
  amazon: number;
  n11: number;
  hepsiburada: number;
  ozon: number;
  total: number;
}

interface ProductPerformance {
  marketplace: string;
  productId: string;
  title: string;
  sales: number;
  revenue: number;
  stock: number;
  price: number;
  rating: number;
  reviews: number;
  category: string;
}

interface SyncStatus {
  marketplace: string;
  isRunning: boolean;
  lastSync: string;
  nextSync: string;
  progress: number;
  errors: number;
}

const CrossMarketplaceDashboard: React.FC = () => {
  const [marketplaces, setMarketplaces] = useState<MarketplaceMetrics[]>([]);
  const [orderHistory, setOrderHistory] = useState<OrderData[]>([]);
  const [topProducts, setTopProducts] = useState<ProductPerformance[]>([]);
  const [syncStatuses, setSyncStatuses] = useState<SyncStatus[]>([]);
  const [selectedTimeframe, setSelectedTimeframe] = useState<'7d' | '30d' | '90d'>('30d');
  const [selectedMarketplace, setSelectedMarketplace] = useState<string>('all');
  const [isLoading, setIsLoading] = useState(false);
  const [lastUpdate, setLastUpdate] = useState<string>('');
  const [realTimeMode, setRealTimeMode] = useState(true);

  const loadDashboardData = useCallback(async () => {
    setIsLoading(true);
    try {
      const response = await fetch('/admin/extension/module/meschain/api/cross-marketplace/dashboard');
      if (response.ok) {
        const data = await response.json();
        setMarketplaces(data.marketplaces);
        setOrderHistory(data.orderHistory);
        setTopProducts(data.topProducts);
        setSyncStatuses(data.syncStatuses);
      } else {
        throw new Error('Cross-marketplace API failed');
      }
    } catch (error) {
      console.warn('Cross-marketplace API offline, using demo data:', error);
      loadDemoData();
    } finally {
      setIsLoading(false);
      setLastUpdate(new Date().toLocaleString('tr-TR'));
    }
  }, []);

  const loadDemoData = useCallback(() => {
    console.log('🌐 Loading Cross-Marketplace Dashboard demo data...');
    
    // Enhanced marketplace metrics
    setMarketplaces([
      {
        id: 'trendyol',
        name: 'Trendyol',
        logo: '🟠',
        status: 'active',
        totalOrders: 2456,
        totalRevenue: 1234567.89,
        activeProducts: 856,
        conversionRate: 3.4,
        averageOrderValue: 502.45,
        growthRate: 15.6,
        lastSync: '2025-06-03 02:45:00',
        apiCalls: 12456,
        errorRate: 1.2,
        region: 'Turkey'
      },
      {
        id: 'amazon',
        name: 'Amazon',
        logo: '🟡',
        status: 'active',
        totalOrders: 1834,
        totalRevenue: 2345678.12,
        activeProducts: 1245,
        conversionRate: 4.2,
        averageOrderValue: 1278.56,
        growthRate: 22.3,
        lastSync: '2025-06-03 02:40:00',
        apiCalls: 8934,
        errorRate: 0.8,
        region: 'Europe'
      },
      {
        id: 'n11',
        name: 'N11',
        logo: '🟣',
        status: 'error',
        totalOrders: 1267,
        totalRevenue: 567890.34,
        activeProducts: 654,
        conversionRate: 2.8,
        averageOrderValue: 448.23,
        growthRate: -5.2,
        lastSync: '2025-06-03 01:15:00',
        apiCalls: 5678,
        errorRate: 12.5,
        region: 'Turkey'
      },
      {
        id: 'hepsiburada',
        name: 'Hepsiburada',
        logo: '🔴',
        status: 'active',
        totalOrders: 1589,
        totalRevenue: 789012.45,
        activeProducts: 743,
        conversionRate: 3.1,
        averageOrderValue: 496.78,
        growthRate: 8.9,
        lastSync: '2025-06-03 02:35:00',
        apiCalls: 6789,
        errorRate: 2.1,
        region: 'Turkey'
      },
      {
        id: 'ozon',
        name: 'Ozon',
        logo: '🔵',
        status: 'maintenance',
        totalOrders: 945,
        totalRevenue: 456789.67,
        activeProducts: 432,
        conversionRate: 2.6,
        averageOrderValue: 483.45,
        growthRate: 12.1,
        lastSync: '2025-06-03 00:20:00',
        apiCalls: 3456,
        errorRate: 5.3,
        region: 'Russia'
      }
    ]);

    // Order history for charts
    const generateOrderHistory = () => {
      const history: OrderData[] = [];
      const now = new Date();
      for (let i = 29; i >= 0; i--) {
        const date = new Date(now.getTime() - i * 24 * 60 * 60 * 1000);
        const trendyol = Math.floor(Math.random() * 50) + 30;
        const amazon = Math.floor(Math.random() * 40) + 25;
        const n11 = Math.floor(Math.random() * 30) + 15;
        const hepsiburada = Math.floor(Math.random() * 35) + 20;
        const ozon = Math.floor(Math.random() * 25) + 10;
        
        history.push({
          date: date.toISOString().split('T')[0],
          trendyol,
          amazon,
          n11,
          hepsiburada,
          ozon,
          total: trendyol + amazon + n11 + hepsiburada + ozon
        });
      }
      return history;
    };
    setOrderHistory(generateOrderHistory());

    // Top performing products
    setTopProducts([
      {
        marketplace: 'trendyol',
        productId: 'TR-001',
        title: 'iPhone 15 Pro Max 256GB',
        sales: 145,
        revenue: 435000,
        stock: 23,
        price: 45999,
        rating: 4.8,
        reviews: 89,
        category: 'Elektronik'
      },
      {
        marketplace: 'amazon',
        productId: 'AM-002',
        title: 'Samsung Galaxy S24 Ultra',
        sales: 134,
        revenue: 401400,
        stock: 31,
        price: 42999,
        rating: 4.7,
        reviews: 156,
        category: 'Elektronik'
      },
      {
        marketplace: 'hepsiburada',
        productId: 'HB-003',
        title: 'MacBook Pro M3 14"',
        sales: 67,
        revenue: 268000,
        stock: 12,
        price: 54999,
        rating: 4.9,
        reviews: 43,
        category: 'Bilgisayar'
      },
      {
        marketplace: 'trendyol',
        productId: 'TR-004',
        title: 'Nike Air Max 270',
        sales: 234,
        revenue: 70200,
        stock: 89,
        price: 899,
        rating: 4.6,
        reviews: 267,
        category: 'Spor'
      },
      {
        marketplace: 'amazon',
        productId: 'AM-005',
        title: 'Sony WH-1000XM5',
        sales: 98,
        revenue: 78400,
        stock: 45,
        price: 3999,
        rating: 4.8,
        reviews: 123,
        category: 'Elektronik'
      }
    ]);

    // Sync statuses
    setSyncStatuses([
      {
        marketplace: 'trendyol',
        isRunning: false,
        lastSync: '2025-06-03 02:45:00',
        nextSync: '2025-06-03 03:15:00',
        progress: 100,
        errors: 0
      },
      {
        marketplace: 'amazon',
        isRunning: true,
        lastSync: '2025-06-03 02:40:00',
        nextSync: '2025-06-03 03:40:00',
        progress: 65,
        errors: 0
      },
      {
        marketplace: 'n11',
        isRunning: false,
        lastSync: '2025-06-03 01:15:00',
        nextSync: '2025-06-03 04:15:00',
        progress: 0,
        errors: 3
      },
      {
        marketplace: 'hepsiburada',
        isRunning: false,
        lastSync: '2025-06-03 02:35:00',
        nextSync: '2025-06-03 03:35:00',
        progress: 100,
        errors: 0
      },
      {
        marketplace: 'ozon',
        isRunning: false,
        lastSync: '2025-06-03 00:20:00',
        nextSync: '2025-06-03 06:20:00',
        progress: 0,
        errors: 1
      }
    ]);

    console.log('✅ Cross-Marketplace Dashboard demo data loaded');
  }, []);

  // Real-time updates
  useEffect(() => {
    loadDashboardData();
    
    if (realTimeMode) {
      const interval = setInterval(() => {
        loadDashboardData();
      }, 30000);
      
      return () => clearInterval(interval);
    }
  }, [loadDashboardData, realTimeMode]);

  // Trigger marketplace sync
  const triggerSync = async (marketplaceId: string) => {
    setIsLoading(true);
    try {
      const response = await fetch(`/admin/extension/module/meschain/api/sync/${marketplaceId}`, {
        method: 'POST'
      });
      
      if (response.ok) {
        // Update sync status
        setSyncStatuses(prev => prev.map(status => 
          status.marketplace === marketplaceId 
            ? { ...status, isRunning: true, progress: 0 }
            : status
        ));
      } else {
        throw new Error('Sync trigger failed');
      }
    } catch (error) {
      console.warn('Sync API offline, simulating sync:', error);
      setSyncStatuses(prev => prev.map(status => 
        status.marketplace === marketplaceId 
          ? { ...status, isRunning: true, progress: 0 }
          : status
      ));
    } finally {
      setIsLoading(false);
    }
  };

  // Calculate totals
  const totalOrders = marketplaces.reduce((sum, mp) => sum + mp.totalOrders, 0);
  const totalRevenue = marketplaces.reduce((sum, mp) => sum + mp.totalRevenue, 0);
  const totalProducts = marketplaces.reduce((sum, mp) => sum + mp.activeProducts, 0);
  const averageConversion = marketplaces.reduce((sum, mp) => sum + mp.conversionRate, 0) / marketplaces.length;

  // Format currency
  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('tr-TR', {
      style: 'currency',
      currency: 'TRY',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(amount);
  };

  // Get status color
  const getStatusColor = (status: string) => {
    switch (status) {
      case 'active': return 'text-green-600 bg-green-100';
      case 'inactive': return 'text-gray-600 bg-gray-100';
      case 'error': return 'text-red-600 bg-red-100';
      case 'maintenance': return 'text-yellow-600 bg-yellow-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  // Chart colors
  const CHART_COLORS = ['#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6'];

  if (isLoading && !marketplaces.length) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-4 text-lg text-gray-600">Cross-Marketplace Dashboard Yükleniyor...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold mb-2">🌐 Cross-Marketplace Dashboard</h1>
            <p className="text-blue-100">Tüm pazaryerlerini tek noktadan yönetin</p>
          </div>
          <div className="text-right">
            <div className="flex items-center space-x-4 mb-2">
              <button
                onClick={() => setRealTimeMode(!realTimeMode)}
                className={`flex items-center space-x-2 px-3 py-1 rounded-full text-sm font-medium transition-colors ${
                  realTimeMode
                    ? 'bg-green-100 text-green-800'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                }`}
              >
                <ArrowPathIcon className={`w-4 h-4 ${realTimeMode ? 'animate-spin' : ''}`} />
                <span>Canlı Takip</span>
              </button>
              <select
                value={selectedTimeframe}
                onChange={(e) => setSelectedTimeframe(e.target.value as any)}
                className="rounded border-gray-300 text-gray-900 text-sm"
              >
                <option value="7d">Son 7 Gün</option>
                <option value="30d">Son 30 Gün</option>
                <option value="90d">Son 90 Gün</option>
              </select>
            </div>
            <p className="text-blue-200 text-sm">Son güncelleme: {lastUpdate}</p>
          </div>
        </div>
      </div>

      {/* Overview Stats */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <div className="flex items-center">
            <div className="p-2 bg-blue-100 rounded-lg">
              <ShoppingCartIcon className="h-6 w-6 text-blue-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-600">Toplam Sipariş</p>
              <p className="text-2xl font-bold text-gray-900">{totalOrders.toLocaleString()}</p>
            </div>
          </div>
          <div className="mt-4 flex items-center text-sm">
            <ArrowTrendingUpIcon className="h-4 w-4 text-green-500 mr-1" />
            <span className="text-green-600 font-medium">+12.5%</span>
            <span className="text-gray-500 ml-1">bu ay</span>
          </div>
        </div>

        <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <div className="flex items-center">
            <div className="p-2 bg-green-100 rounded-lg">
              <CurrencyDollarIcon className="h-6 w-6 text-green-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-600">Toplam Gelir</p>
              <p className="text-2xl font-bold text-gray-900">{formatCurrency(totalRevenue)}</p>
            </div>
          </div>
          <div className="mt-4 flex items-center text-sm">
            <ArrowTrendingUpIcon className="h-4 w-4 text-green-500 mr-1" />
            <span className="text-green-600 font-medium">+18.2%</span>
            <span className="text-gray-500 ml-1">bu ay</span>
          </div>
        </div>

        <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <div className="flex items-center">
            <div className="p-2 bg-purple-100 rounded-lg">
              <ChartBarIcon className="h-6 w-6 text-purple-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-600">Aktif Ürün</p>
              <p className="text-2xl font-bold text-gray-900">{totalProducts.toLocaleString()}</p>
            </div>
          </div>
          <div className="mt-4 flex items-center text-sm">
            <ArrowTrendingUpIcon className="h-4 w-4 text-green-500 mr-1" />
            <span className="text-green-600 font-medium">+7.3%</span>
            <span className="text-gray-500 ml-1">bu ay</span>
          </div>
        </div>

        <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <div className="flex items-center">
            <div className="p-2 bg-orange-100 rounded-lg">
              <GlobeAltIcon className="h-6 w-6 text-orange-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-600">Ortalama Dönüşüm</p>
              <p className="text-2xl font-bold text-gray-900">{averageConversion.toFixed(1)}%</p>
            </div>
          </div>
          <div className="mt-4 flex items-center text-sm">
            <ArrowTrendingUpIcon className="h-4 w-4 text-green-500 mr-1" />
            <span className="text-green-600 font-medium">+2.1%</span>
            <span className="text-gray-500 ml-1">bu ay</span>
          </div>
        </div>
      </div>

      {/* Marketplace Status Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        {marketplaces.map((marketplace) => (
          <div key={marketplace.id} className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div className="flex items-center justify-between mb-4">
              <div className="flex items-center">
                <span className="text-2xl mr-3">{marketplace.logo}</span>
                <div>
                  <h3 className="font-medium text-gray-900">{marketplace.name}</h3>
                  <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${getStatusColor(marketplace.status)}`}>
                    {marketplace.status === 'active' ? 'Aktif' : 
                     marketplace.status === 'error' ? 'Hata' : 
                     marketplace.status === 'maintenance' ? 'Bakım' : 'İnaktif'}
                  </span>
                </div>
              </div>
            </div>
            
            <div className="space-y-3">
              <div className="flex justify-between">
                <span className="text-sm text-gray-500">Sipariş</span>
                <span className="text-sm font-medium text-gray-900">{marketplace.totalOrders}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-sm text-gray-500">Gelir</span>
                <span className="text-sm font-medium text-gray-900">{formatCurrency(marketplace.totalRevenue)}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-sm text-gray-500">Ürün</span>
                <span className="text-sm font-medium text-gray-900">{marketplace.activeProducts}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-sm text-gray-500">Büyüme</span>
                <span className={`text-sm font-medium ${marketplace.growthRate >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                  {marketplace.growthRate >= 0 ? '+' : ''}{marketplace.growthRate}%
                </span>
              </div>
            </div>

            <div className="mt-4 pt-4 border-t border-gray-200">
              <button
                onClick={() => triggerSync(marketplace.id)}
                disabled={syncStatuses.find(s => s.marketplace === marketplace.id)?.isRunning}
                className="w-full text-center text-sm text-blue-600 hover:text-blue-800 font-medium disabled:text-gray-400"
              >
                {syncStatuses.find(s => s.marketplace === marketplace.id)?.isRunning ? 'Senkronizasyon...' : 'Senkronize Et'}
              </button>
            </div>
          </div>
        ))}
      </div>

      {/* Charts and Analysis */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Order Trends */}
        <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <h3 className="text-lg font-medium text-gray-900 mb-4">Sipariş Trendleri (Son 30 Gün)</h3>
          <ResponsiveContainer width="100%" height={300}>
            <LineChart data={orderHistory}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="date" tickFormatter={(value) => new Date(value).toLocaleDateString('tr-TR', { month: 'short', day: 'numeric' })} />
              <YAxis />
              <Tooltip labelFormatter={(value) => new Date(value).toLocaleDateString('tr-TR')} />
              <Line type="monotone" dataKey="trendyol" stroke="#F59E0B" strokeWidth={2} name="Trendyol" />
              <Line type="monotone" dataKey="amazon" stroke="#3B82F6" strokeWidth={2} name="Amazon" />
              <Line type="monotone" dataKey="n11" stroke="#8B5CF6" strokeWidth={2} name="N11" />
              <Line type="monotone" dataKey="hepsiburada" stroke="#EF4444" strokeWidth={2} name="Hepsiburada" />
              <Line type="monotone" dataKey="ozon" stroke="#10B981" strokeWidth={2} name="Ozon" />
            </LineChart>
          </ResponsiveContainer>
        </div>

        {/* Revenue Distribution */}
        <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <h3 className="text-lg font-medium text-gray-900 mb-4">Gelir Dağılımı</h3>
          <ResponsiveContainer width="100%" height={300}>
            <PieChart>
              <Pie
                data={marketplaces.map((mp, index) => ({
                  name: mp.name,
                  value: mp.totalRevenue,
                  fill: CHART_COLORS[index % CHART_COLORS.length]
                }))}
                cx="50%"
                cy="50%"
                innerRadius={60}
                outerRadius={120}
                paddingAngle={5}
                dataKey="value"
              >
                {marketplaces.map((entry, index) => (
                  <Cell key={`cell-${index}`} fill={CHART_COLORS[index % CHART_COLORS.length]} />
                ))}
              </Pie>
              <Tooltip formatter={(value) => formatCurrency(Number(value))} />
            </PieChart>
          </ResponsiveContainer>
        </div>
      </div>

      {/* Top Products and Sync Status */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Top Products */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200">
          <div className="px-6 py-4 border-b border-gray-200">
            <h3 className="text-lg font-medium text-gray-900">En Çok Satan Ürünler</h3>
          </div>
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ürün</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pazaryeri</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satış</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gelir</th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {topProducts.map((product, index) => (
                  <tr key={index} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div>
                        <div className="text-sm font-medium text-gray-900">{product.title}</div>
                        <div className="text-sm text-gray-500">{product.category}</div>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 capitalize">
                        {product.marketplace}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {product.sales}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {formatCurrency(product.revenue)}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                        product.stock > 50 ? 'bg-green-100 text-green-800' :
                        product.stock > 20 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'
                      }`}>
                        {product.stock}
                      </span>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>

        {/* Sync Status */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200">
          <div className="px-6 py-4 border-b border-gray-200">
            <h3 className="text-lg font-medium text-gray-900">Senkronizasyon Durumu</h3>
          </div>
          <div className="p-6">
            <div className="space-y-4">
              {syncStatuses.map((sync) => {
                const marketplace = marketplaces.find(mp => mp.id === sync.marketplace);
                return (
                  <div key={sync.marketplace} className="border rounded-lg p-4">
                    <div className="flex items-center justify-between mb-2">
                      <div className="flex items-center">
                        <span className="text-lg mr-2">{marketplace?.logo}</span>
                        <span className="font-medium text-gray-900 capitalize">{sync.marketplace}</span>
                      </div>
                      <div className="flex items-center space-x-2">
                        {sync.isRunning ? (
                          <ArrowPathIcon className="h-4 w-4 text-blue-600 animate-spin" />
                        ) : sync.errors > 0 ? (
                          <ExclamationTriangleIcon className="h-4 w-4 text-red-600" />
                        ) : (
                          <CheckCircleIcon className="h-4 w-4 text-green-600" />
                        )}
                        <button
                          onClick={() => triggerSync(sync.marketplace)}
                          disabled={sync.isRunning}
                          className="text-blue-600 hover:text-blue-800 disabled:text-gray-400"
                        >
                          <PlayIcon className="h-4 w-4" />
                        </button>
                      </div>
                    </div>
                    
                    {sync.isRunning && (
                      <div className="mb-2">
                        <div className="flex justify-between text-sm text-gray-600 mb-1">
                          <span>İlerleme</span>
                          <span>{sync.progress}%</span>
                        </div>
                        <div className="w-full bg-gray-200 rounded-full h-2">
                          <div 
                            className="bg-blue-600 h-2 rounded-full transition-all duration-300"
                            style={{ width: `${sync.progress}%` }}
                          ></div>
                        </div>
                      </div>
                    )}
                    
                    <div className="text-sm text-gray-500">
                      <div>Son: {new Date(sync.lastSync).toLocaleString('tr-TR')}</div>
                      <div>Sonraki: {new Date(sync.nextSync).toLocaleString('tr-TR')}</div>
                      {sync.errors > 0 && (
                        <div className="text-red-600">{sync.errors} hata</div>
                      )}
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default CrossMarketplaceDashboard; 