import React, { useState, useEffect } from 'react';
import {
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  LineChart,
  Line
} from 'recharts';

interface TrendyolMetrics {
  totalRevenue: number;
  totalOrders: number;
  averageOrderValue: number;
  conversionRate: number;
  returnRate: number;
  rating: number;
  activeProducts: number;
  pendingProducts: number;
}

const TrendyolDashboard: React.FC = () => {
  const [metrics, setMetrics] = useState<TrendyolMetrics>({
    totalRevenue: 0,
    totalOrders: 0,
    averageOrderValue: 0,
    conversionRate: 0,
    returnRate: 0,
    rating: 0,
    activeProducts: 0,
    pendingProducts: 0
  });

  const [salesData, setSalesData] = useState<any[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    loadTrendyolData();
  }, []);

  const loadTrendyolData = async () => {
    setIsLoading(true);
    try {
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 1000));

      setMetrics({
        totalRevenue: 1250000,
        totalOrders: 847,
        averageOrderValue: 1475,
        conversionRate: 4.2,
        returnRate: 2.8,
        rating: 4.7,
        activeProducts: 156,
        pendingProducts: 12
      });

      setSalesData([
        { date: '1 Haz', sales: 45000, orders: 32 },
        { date: '2 Haz', sales: 52000, orders: 38 },
        { date: '3 Haz', sales: 48000, orders: 35 },
        { date: '4 Haz', sales: 61000, orders: 42 },
        { date: '5 Haz', sales: 55000, orders: 39 },
        { date: '6 Haz', sales: 58000, orders: 41 },
        { date: '7 Haz', sales: 63000, orders: 45 }
      ]);

    } catch (error) {
      console.error('Error loading Trendyol data:', error);
    } finally {
      setIsLoading(false);
    }
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-orange-600 mx-auto"></div>
          <p className="mt-4 text-lg text-gray-600">Trendyol verileri yükleniyor...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
        <h1 className="text-3xl font-bold mb-2">🛍️ Trendyol Dashboard</h1>
        <p className="text-orange-100">Trendyol mağaza performansınızı izleyin</p>
      </div>

      {/* Metrics Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div className="bg-white rounded-lg shadow p-6">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <div className="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                <span className="text-white font-bold">₺</span>
              </div>
            </div>
            <div className="ml-5 w-0 flex-1">
              <dl>
                <dt className="text-sm font-medium text-gray-500 truncate">
                  Toplam Gelir
                </dt>
                <dd className="text-lg font-semibold text-gray-900">
                  ₺{metrics.totalRevenue.toLocaleString('tr-TR')}
                </dd>
              </dl>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow p-6">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <div className="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                <span className="text-white text-sm">📦</span>
              </div>
            </div>
            <div className="ml-5 w-0 flex-1">
              <dl>
                <dt className="text-sm font-medium text-gray-500 truncate">
                  Toplam Sipariş
                </dt>
                <dd className="text-lg font-semibold text-gray-900">
                  {metrics.totalOrders.toLocaleString('tr-TR')}
                </dd>
              </dl>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow p-6">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <div className="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                <span className="text-white text-sm">⭐</span>
              </div>
            </div>
            <div className="ml-5 w-0 flex-1">
              <dl>
                <dt className="text-sm font-medium text-gray-500 truncate">
                  Mağaza Puanı
                </dt>
                <dd className="text-lg font-semibold text-gray-900">
                  {metrics.rating.toFixed(1)} / 5.0
                </dd>
              </dl>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow p-6">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <div className="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center">
                <span className="text-white text-sm">📈</span>
              </div>
            </div>
            <div className="ml-5 w-0 flex-1">
              <dl>
                <dt className="text-sm font-medium text-gray-500 truncate">
                  Dönüşüm Oranı
                </dt>
                <dd className="text-lg font-semibold text-gray-900">
                  %{metrics.conversionRate.toFixed(1)}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      {/* Sales Chart */}
      <div className="bg-white rounded-lg shadow p-6">
        <h3 className="text-lg font-medium text-gray-900 mb-4">
          Günlük Satış Performansı
        </h3>
        <ResponsiveContainer width="100%" height={300}>
          <LineChart data={salesData}>
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis dataKey="date" />
            <YAxis />
            <Tooltip 
              formatter={(value, name) => [
                name === 'sales' ? `₺${value.toLocaleString('tr-TR')}` : value,
                name === 'sales' ? 'Satış' : 'Sipariş'
              ]}
            />
            <Line type="monotone" dataKey="sales" stroke="#F27A1A" strokeWidth={3} />
            <Line type="monotone" dataKey="orders" stroke="#3B82F6" strokeWidth={2} />
          </LineChart>
        </ResponsiveContainer>
      </div>

      {/* Additional Widgets */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div className="bg-white rounded-lg shadow p-6">
          <h3 className="text-lg font-medium text-gray-900 mb-4">
            Ürün Durumu
          </h3>
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <span className="text-sm text-gray-600">Aktif Ürünler</span>
              <span className="text-lg font-semibold text-green-600">
                {metrics.activeProducts}
              </span>
            </div>
            <div className="flex items-center justify-between">
              <span className="text-sm text-gray-600">Bekleyen Ürünler</span>
              <span className="text-lg font-semibold text-yellow-600">
                {metrics.pendingProducts}
              </span>
            </div>
            <div className="flex items-center justify-between">
              <span className="text-sm text-gray-600">İade Oranı</span>
              <span className="text-lg font-semibold text-red-600">
                %{metrics.returnRate.toFixed(1)}
              </span>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow p-6">
          <h3 className="text-lg font-medium text-gray-900 mb-4">
            Hızlı İşlemler
          </h3>
          <div className="space-y-3">
            <button className="w-full bg-orange-500 hover:bg-orange-600 text-white font-medium py-2 px-4 rounded-md transition-colors">
              Ürün Listele
            </button>
            <button className="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-md transition-colors">
              Sipariş Yönetimi
            </button>
            <button className="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-2 px-4 rounded-md transition-colors">
              Raporları Görüntüle
            </button>
            <button className="w-full bg-purple-500 hover:bg-purple-600 text-white font-medium py-2 px-4 rounded-md transition-colors">
              Ayarlar
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};

export default TrendyolDashboard; 