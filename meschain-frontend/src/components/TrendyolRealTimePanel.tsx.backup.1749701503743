import React, { useState, useEffect, useCallback } from 'react';
import { ShoppingBag, TrendingUp, AlertCircle, RefreshCw, CheckCircle, XCircle } from 'lucide-react';
import { Line } from 'react-chartjs-2';
import trendyolAPI, { TrendyolProduct, TrendyolOrder } from '../services/trendyolAPI';
import toast from 'react-hot-toast';

interface TrendyolMetrics {
  totalProducts: number;
  activeProducts: number;
  totalOrders: number;
  todaySales: number;
  apiStatus: 'connected' | 'failed' | 'testing';
  lastSync: string;
}

const TrendyolRealTimePanel: React.FC = () => {
  const [metrics, setMetrics] = useState<TrendyolMetrics>({
    totalProducts: 0,
    activeProducts: 0,
    totalOrders: 0,
    todaySales: 0,
    apiStatus: 'testing',
    lastSync: new Date().toISOString()
  });

  const [products, setProducts] = useState<TrendyolProduct[]>([]);
  const [orders, setOrders] = useState<TrendyolOrder[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [salesChartData, setSalesChartData] = useState<any>(null);

  // Real-time data fetching
  const fetchTrendyolData = useCallback(async (showToast = false) => {
    try {
      setRefreshing(true);

      // Test API connection first
      const connectionTest = await trendyolAPI.testConnection();
      setMetrics(prev => ({
        ...prev,
        apiStatus: connectionTest.success ? 'connected' : 'failed'
      }));

      // Fetch products
      const productsResponse = await trendyolAPI.getProducts({ page: 0, size: 20 });
      if (productsResponse.success && productsResponse.data) {
        setProducts(productsResponse.data.products || []);
        
        const activeCount = productsResponse.data.products?.filter(p => p.status === 'active').length || 0;
        setMetrics(prev => ({
          ...prev,
          totalProducts: productsResponse.data?.products?.length || 0,
          activeProducts: activeCount
        }));
      }

      // Fetch recent orders
      const ordersResponse = await trendyolAPI.getOrders({ 
        startDate: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
        endDate: new Date().toISOString().split('T')[0],
        size: 10 
      });
      
      if (ordersResponse.success && ordersResponse.data) {
        setOrders(ordersResponse.data.orders || []);
        
        const todayOrders = ordersResponse.data.orders?.filter(order => 
          new Date(order.orderDate).toDateString() === new Date().toDateString()
        ) || [];
        
        const todaySalesAmount = todayOrders.reduce((sum, order) => sum + order.totalPrice, 0);
        
        setMetrics(prev => ({
          ...prev,
          totalOrders: ordersResponse.data?.orders?.length || 0,
          todaySales: todaySalesAmount,
          lastSync: new Date().toISOString()
        }));
      }

      // Generate sales chart data
      generateSalesChart(ordersResponse.data?.orders || []);

      if (showToast) {
        toast.success('Trendyol verileri güncellendi!');
      }

    } catch (error) {
      console.error('Trendyol data fetch error:', error);
      setMetrics(prev => ({ ...prev, apiStatus: 'failed' }));
      
      if (showToast) {
        toast.error('Trendyol API bağlantı hatası!');
      }
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  const generateSalesChart = (orderData: TrendyolOrder[]) => {
    const last7Days = Array.from({ length: 7 }, (_, i) => {
      const date = new Date();
      date.setDate(date.getDate() - i);
      return date.toISOString().split('T')[0];
    }).reverse();

    const salesByDay = last7Days.map(date => {
      const dayOrders = orderData.filter(order => 
        order.orderDate.split('T')[0] === date
      );
      return dayOrders.reduce((sum, order) => sum + order.totalPrice, 0);
    });

    setSalesChartData({
      labels: last7Days.map(date => new Date(date).toLocaleDateString('tr-TR', { 
        month: 'short', 
        day: 'numeric' 
      })),
      datasets: [{
        label: 'Günlük Satış (₺)',
        data: salesByDay,
        borderColor: '#f97316',
        backgroundColor: 'rgba(249, 115, 22, 0.1)',
        borderWidth: 2,
        fill: true,
        tension: 0.4
      }]
    });
  };

  // Auto-refresh every 30 seconds
  useEffect(() => {
    fetchTrendyolData();
    
    const interval = setInterval(() => {
      fetchTrendyolData();
    }, 30000);

    return () => clearInterval(interval);
  }, [fetchTrendyolData]);

  const handleManualRefresh = () => {
    fetchTrendyolData(true);
  };

  const getStatusIcon = () => {
    switch (metrics.apiStatus) {
      case 'connected':
        return <CheckCircle className="w-5 h-5 text-green-500" />;
      case 'failed':
        return <XCircle className="w-5 h-5 text-red-500" />;
      default:
        return <AlertCircle className="w-5 h-5 text-yellow-500" />;
    }
  };

  const getStatusText = () => {
    switch (metrics.apiStatus) {
      case 'connected':
        return 'Bağlı';
      case 'failed':
        return 'Bağlantı Hatası';
      default:
        return 'Test Ediliyor';
    }
  };

  if (loading) {
    return (
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div className="flex items-center justify-center h-64">
          <div className="flex items-center space-x-2">
            <RefreshCw className="w-6 h-6 animate-spin text-orange-500" />
            <span className="text-gray-600">Trendyol verileri yükleniyor...</span>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header with Status */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-3">
            <div className="p-2 bg-orange-100 rounded-lg">
              <ShoppingBag className="w-6 h-6 text-orange-600" />
            </div>
            <div>
              <h2 className="text-xl font-semibold text-gray-900">Trendyol Canlı Panel</h2>
              <p className="text-sm text-gray-500">
                Son güncelleme: {new Date(metrics.lastSync).toLocaleTimeString('tr-TR')}
              </p>
            </div>
          </div>
          
          <div className="flex items-center space-x-4">
            <div className="flex items-center space-x-2">
              {getStatusIcon()}
              <span className={`text-sm font-medium ${
                metrics.apiStatus === 'connected' ? 'text-green-600' : 
                metrics.apiStatus === 'failed' ? 'text-red-600' : 'text-yellow-600'
              }`}>
                {getStatusText()}
              </span>
            </div>
            
            <button
              onClick={handleManualRefresh}
              disabled={refreshing}
              className="flex items-center space-x-2 px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              <RefreshCw className={`w-4 h-4 ${refreshing ? 'animate-spin' : ''}`} />
              <span>Yenile</span>
            </button>
          </div>
        </div>
      </div>

      {/* Metrics Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Toplam Ürün</p>
              <p className="text-2xl font-bold text-gray-900">{metrics.totalProducts.toLocaleString()}</p>
            </div>
            <div className="p-3 bg-blue-100 rounded-lg">
              <ShoppingBag className="w-6 h-6 text-blue-600" />
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Aktif Ürün</p>
              <p className="text-2xl font-bold text-green-600">{metrics.activeProducts.toLocaleString()}</p>
            </div>
            <div className="p-3 bg-green-100 rounded-lg">
              <CheckCircle className="w-6 h-6 text-green-600" />
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Toplam Sipariş</p>
              <p className="text-2xl font-bold text-purple-600">{metrics.totalOrders.toLocaleString()}</p>
            </div>
            <div className="p-3 bg-purple-100 rounded-lg">
              <TrendingUp className="w-6 h-6 text-purple-600" />
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-600">Bugünkü Satış</p>
              <p className="text-2xl font-bold text-orange-600">₺{metrics.todaySales.toLocaleString()}</p>
            </div>
            <div className="p-3 bg-orange-100 rounded-lg">
              <TrendingUp className="w-6 h-6 text-orange-600" />
            </div>
          </div>
        </div>
      </div>

      {/* Sales Chart */}
      {salesChartData && (
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Son 7 Gün Satış Trendi</h3>
          <div className="h-64">
            <Line 
              data={salesChartData}
              options={{
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                  legend: {
                    display: false
                  }
                },
                scales: {
                  y: {
                    beginAtZero: true,
                    ticks: {
                      callback: function(value) {
                        return '₺' + value.toLocaleString();
                      }
                    }
                  }
                }
              }}
            />
          </div>
        </div>
      )}

      {/* Recent Products & Orders */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Recent Products */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Son Ürünler</h3>
          <div className="space-y-3">
            {products.slice(0, 5).map((product) => (
              <div key={product.id} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div className="flex-1">
                  <p className="font-medium text-gray-900 truncate">{product.title}</p>
                  <p className="text-sm text-gray-500">{product.brand}</p>
                </div>
                <div className="text-right">
                  <p className="font-semibold text-gray-900">₺{product.price.toLocaleString()}</p>
                  <p className={`text-xs ${
                    product.status === 'active' ? 'text-green-600' : 
                    product.status === 'passive' ? 'text-yellow-600' : 'text-red-600'
                  }`}>
                    {product.status === 'active' ? 'Aktif' : 
                     product.status === 'passive' ? 'Pasif' : 'Reddedildi'}
                  </p>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Recent Orders */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Son Siparişler</h3>
          <div className="space-y-3">
            {orders.slice(0, 5).map((order) => (
              <div key={order.orderNumber} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div className="flex-1">
                  <p className="font-medium text-gray-900">#{order.orderNumber}</p>
                  <p className="text-sm text-gray-500">{order.customerName}</p>
                </div>
                <div className="text-right">
                  <p className="font-semibold text-gray-900">₺{order.totalPrice.toLocaleString()}</p>
                  <p className={`text-xs ${
                    order.status === 'Delivered' ? 'text-green-600' : 
                    order.status === 'Cancelled' ? 'text-red-600' : 'text-blue-600'
                  }`}>
                    {order.status}
                  </p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* API Status Warning */}
      {metrics.apiStatus === 'failed' && (
        <div className="bg-red-50 border border-red-200 rounded-lg p-4">
          <div className="flex items-center space-x-2">
            <XCircle className="w-5 h-5 text-red-500" />
            <div>
              <p className="text-red-800 font-medium">Trendyol API Bağlantı Hatası</p>
              <p className="text-red-600 text-sm">
                API anahtarlarınızı kontrol edin veya demo verilerle devam edin.
                Gerçek veriler için .env.local dosyasında Trendyol API bilgilerinizi güncelleyin.
              </p>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default TrendyolRealTimePanel; 