import React, { useState, useEffect, useCallback } from 'react';
import {
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  PieChart,
  Pie,
  Cell,
  AreaChart,
  Area
} from 'recharts';

interface TrendyolStats {
  todaySales: number;
  last30DaysSales: number;
  last7DaysSales: number;
  pendingAmount: number;
  totalOrders: number;
  totalProducts: number;
  totalCustomers: number;
  avgOrderValue: number;
  conversionRate: number;
}

interface ActionItem {
  category: string;
  title: string;
  count: number;
  priority: 'high' | 'medium' | 'low';
  color: string;
  icon: string;
}

interface TrendyolApiResponse {
  success: boolean;
  data?: any;
  error?: string;
  responseTime?: number;
}

const TrendyolDashboard: React.FC = () => {
  const [stats, setStats] = useState<TrendyolStats>({
    todaySales: 0,
    last30DaysSales: 0,
    last7DaysSales: 0,
    pendingAmount: 0,
    totalOrders: 0,
    totalProducts: 0,
    totalCustomers: 0,
    avgOrderValue: 0,
    conversionRate: 0
  });
  const [isLoading, setIsLoading] = useState(true);
  const [lastUpdate, setLastUpdate] = useState<string>('');
  const [isConnected, setIsConnected] = useState(false);
  const [notifications] = useState([
    { id: 1, type: 'warning', message: '197 ürününüzde zayıf içerik tespit edildi', time: '5 dk önce' },
    { id: 2, type: 'info', message: '3 yeni sipariş bekliyor', time: '15 dk önce' },
    { id: 3, type: 'success', message: 'Reklam bakiyeniz güncellendi: 131 ₺', time: '1 saat önce' },
    { id: 4, type: 'warning', message: '19 belge onay bekliyor', time: '2 saat önce' }
  ]);

  // Aksiyon öğeleri - Trendyol paneline benzer
  const [actionItems] = useState<ActionItem[]>([
    // Sipariş & Operasyon
    { category: 'Sipariş & Operasyon', title: 'Bekleyen Siparişler', count: 3, priority: 'high', color: '#FF6B35', icon: '📦' },
    { category: 'Sipariş & Operasyon', title: 'Geciken Siparişler', count: 0, priority: 'medium', color: '#FF9500', icon: '⏰' },
    { category: 'Sipariş & Operasyon', title: 'Bekleyen İadeler', count: 0, priority: 'medium', color: '#34C759', icon: '↩️' },
    { category: 'Sipariş & Operasyon', title: 'Sipariş Soruları', count: 0, priority: 'low', color: '#007AFF', icon: '❓' },
    
    // Ürün
    { category: 'Ürün', title: 'Ürün Soruları', count: 0, priority: 'low', color: '#5856D6', icon: '💬' },
    { category: 'Ürün', title: 'Zayıf İçerikli Ürünler', count: 197, priority: 'high', color: '#FF3B30', icon: '⚠️' },
    { category: 'Ürün', title: 'Revize Bekleyen Ürünler', count: 0, priority: 'medium', color: '#FF9500', icon: '🔄' },
    { category: 'Ürün', title: 'Bekleyen Belgeler', count: 19, priority: 'high', color: '#FF6B35', icon: '📄' },
    
    // Satış Artırıcı
    { category: 'Satış Artırıcı', title: 'Reklam Bakiyesi', count: 131, priority: 'medium', color: '#30D158', icon: '💰' },
    { category: 'Satış Artırıcı', title: 'Kredi Pazaryeri', count: 3.59, priority: 'low', color: '#64D2FF', icon: '💳' },
    { category: 'Satış Artırıcı', title: 'Avantajlı Ürün Etiketleri', count: 5, priority: 'medium', color: '#BF5AF2', icon: '🏷️' },
    { category: 'Satış Artırıcı', title: 'Müşteri Duyuruları', count: 6, priority: 'low', color: '#FF9F0A', icon: '📢' }
  ]);

  // Hızlı Eylemler
  const quickActions = [
    { title: 'Yeni Ürün Ekle', icon: '➕', color: '#34C759', action: () => console.log('Yeni ürün ekle') },
    { title: 'Toplu Fiyat Güncelle', icon: '💰', color: '#FF9500', action: () => console.log('Fiyat güncelle') },
    { title: 'Stok Senkronize Et', icon: '🔄', color: '#007AFF', action: () => console.log('Stok sync') },
    { title: 'Raporları İndir', icon: '📊', color: '#5856D6', action: () => console.log('Rapor indir') },
    { title: 'Kampanya Oluştur', icon: '🎯', color: '#FF6B35', action: () => console.log('Kampanya') },
    { title: 'Müşteri Mesajları', icon: '💬', color: '#64D2FF', action: () => console.log('Mesajlar') }
  ];

  // Son Siparişler (Demo)
  const recentOrders = [
    { id: 'TY-2024-001', customer: 'Ahmet Y.', amount: 245.50, status: 'Hazırlanıyor', time: '2 saat önce' },
    { id: 'TY-2024-002', customer: 'Fatma K.', amount: 189.90, status: 'Kargoda', time: '4 saat önce' },
    { id: 'TY-2024-003', customer: 'Mehmet S.', amount: 567.25, status: 'Teslim Edildi', time: '6 saat önce' },
    { id: 'TY-2024-004', customer: 'Ayşe D.', amount: 123.75, status: 'Beklemede', time: '8 saat önce' },
    { id: 'TY-2024-005', customer: 'Ali R.', amount: 345.00, status: 'İptal Edildi', time: '1 gün önce' }
  ];

  // Popüler Ürünler (Demo)
  const popularProducts = [
    { name: 'iPhone 15 Pro Max Kılıf', sales: 45, stock: 120, price: 89.90 },
    { name: 'Samsung Galaxy S24 Ekran Koruyucu', sales: 38, stock: 85, price: 45.50 },
    { name: 'Wireless Şarj Aleti', sales: 32, stock: 67, price: 129.90 },
    { name: 'Bluetooth Kulaklık', sales: 28, stock: 43, price: 199.90 },
    { name: 'Laptop Standı', sales: 24, stock: 156, price: 79.90 }
  ];

  // API çağrısı fonksiyonu
  const callTrendyolApi = useCallback(async (endpoint: string): Promise<TrendyolApiResponse> => {
    try {
      const response = await fetch(`http://localhost:8080/test_api.php?action=${endpoint}`, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      const data = await response.json();
      return {
        success: data.success || false,
        data: data.data,
        responseTime: data.responseTime || 0
      };
    } catch (error) {
      return {
        success: false,
        error: error instanceof Error ? error.message : 'Bilinmeyen hata'
      };
    }
  }, []);

  // Trendyol verilerini çek
  const fetchTrendyolData = useCallback(async () => {
    setIsLoading(true);
    
    try {
      const result = await callTrendyolApi('detailed-performance');
      
      if (result.success && result.data) {
        setStats({
          todaySales: result.data.todaySales || 0,
          last30DaysSales: result.data.last30DaysSales || 0,
          last7DaysSales: result.data.last7DaysSales || 0,
          pendingAmount: result.data.pendingAmount || 0,
          totalOrders: result.data.totalOrders || 0,
          totalProducts: result.data.totalProducts || 0,
          totalCustomers: result.data.totalCustomers || 0,
          avgOrderValue: result.data.avgOrderValue || 0,
          conversionRate: result.data.conversionRate || 0
        });
        
        setIsConnected(true);
        setLastUpdate(new Date().toLocaleString('tr-TR'));
        
        console.log('✅ Trendyol verileri güncellendi:', result.data);
      } else {
        setIsConnected(false);
        console.error('❌ Trendyol API hatası:', result.error);
      }
    } catch (error) {
      setIsConnected(false);
      console.error('❌ Veri çekme hatası:', error);
    } finally {
      setIsLoading(false);
    }
  }, [callTrendyolApi]);

  // Sayfa yüklendiğinde verileri çek
  useEffect(() => {
    fetchTrendyolData();
    
    // Her 5 dakikada bir güncelle
    const interval = setInterval(fetchTrendyolData, 5 * 60 * 1000);
    
    return () => clearInterval(interval);
  }, [fetchTrendyolData]);

  // Grafik verileri
  const salesChartData = [
    { name: 'Pzt', sales: stats.last7DaysSales * 0.12, orders: Math.floor(stats.totalOrders * 0.12) },
    { name: 'Sal', sales: stats.last7DaysSales * 0.15, orders: Math.floor(stats.totalOrders * 0.15) },
    { name: 'Çar', sales: stats.last7DaysSales * 0.18, orders: Math.floor(stats.totalOrders * 0.18) },
    { name: 'Per', sales: stats.last7DaysSales * 0.16, orders: Math.floor(stats.totalOrders * 0.16) },
    { name: 'Cum', sales: stats.last7DaysSales * 0.20, orders: Math.floor(stats.totalOrders * 0.20) },
    { name: 'Cmt', sales: stats.last7DaysSales * 0.10, orders: Math.floor(stats.totalOrders * 0.10) },
    { name: 'Paz', sales: stats.last7DaysSales * 0.09, orders: Math.floor(stats.totalOrders * 0.09) }
  ];

  const categoryData = [
    { name: 'Elektronik', value: 35, color: '#FF6B35' },
    { name: 'Moda', value: 28, color: '#007AFF' },
    { name: 'Ev & Yaşam', value: 22, color: '#34C759' },
    { name: 'Spor', value: 15, color: '#FF9500' }
  ];

  // Kategori grupları
  const actionCategories = actionItems.reduce((acc, item) => {
    if (!acc[item.category]) {
      acc[item.category] = [];
    }
    acc[item.category].push(item);
    return acc;
  }, {} as Record<string, ActionItem[]>);

  const ActionCard: React.FC<{ item: ActionItem }> = ({ item }) => (
    <div className={`bg-white rounded-lg shadow-sm border-l-4 p-4 hover:shadow-md transition-shadow cursor-pointer`} 
         style={{ borderLeftColor: item.color }}>
      <div className="flex items-center justify-between">
        <div className="flex items-center space-x-3">
          <span className="text-2xl">{item.icon}</span>
          <div>
            <h4 className="font-medium text-gray-900">{item.title}</h4>
            <p className="text-sm text-gray-500">
              {item.category === 'Satış Artırıcı' && item.title === 'Kredi Pazaryeri' ? `%${item.count}` :
               item.category === 'Satış Artırıcı' && item.title === 'Reklam Bakiyesi' ? `${item.count} ₺` :
               item.category === 'Satış Artırıcı' && item.title === 'Avantajlı Ürün Etiketleri' ? `${item.count} / 576` :
               item.count}
            </p>
          </div>
        </div>
        <div className={`px-2 py-1 rounded-full text-xs font-medium ${
          item.priority === 'high' ? 'bg-red-100 text-red-800' :
          item.priority === 'medium' ? 'bg-yellow-100 text-yellow-800' :
          'bg-green-100 text-green-800'
        }`}>
          {item.priority === 'high' ? 'Yüksek' : item.priority === 'medium' ? 'Orta' : 'Düşük'}
        </div>
      </div>
    </div>
  );

  if (isLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="text-center">
          <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-orange-500 mb-4"></div>
          <p className="text-gray-600">Trendyol verileri yükleniyor...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div className="flex items-center space-x-4">
          <div className="w-12 h-12 bg-gradient-to-r from-orange-500 to-red-500 rounded-lg flex items-center justify-center">
            <span className="text-white text-2xl font-bold">T</span>
          </div>
          <div>
            <h1 className="text-3xl font-bold text-gray-900">Trendyol Mağaza Yönetimi</h1>
            <p className="text-gray-600">Mağaza performansınızı takip edin ve yönetin</p>
            {lastUpdate && (
              <p className="text-sm text-gray-500">Son güncelleme: {lastUpdate}</p>
            )}
          </div>
        </div>
        <div className="flex items-center space-x-3">
          <div className={`flex items-center space-x-2 px-3 py-2 rounded-lg ${
            isConnected ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
          }`}>
            <div className={`w-2 h-2 rounded-full ${isConnected ? 'bg-green-500' : 'bg-red-500'}`}></div>
            <span className="text-sm font-medium">
              {isConnected ? 'Bağlı' : 'Bağlantı Yok'}
            </span>
          </div>
          <button
            onClick={fetchTrendyolData}
            disabled={isLoading}
            className="bg-orange-500 hover:bg-orange-600 disabled:bg-gray-300 text-white px-4 py-2 rounded-lg flex items-center space-x-2"
          >
            <span>🔄</span>
            <span>Yenile</span>
          </button>
        </div>
      </div>

      {/* Ana Performans Kartları */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div className="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-blue-100">Bugünkü Satış</p>
              <p className="text-3xl font-bold">{stats.todaySales.toLocaleString('tr-TR')} ₺</p>
              <p className="text-blue-200 text-sm">%0 (bugün)</p>
            </div>
            <div className="text-4xl opacity-80">💰</div>
          </div>
        </div>

        <div className="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-green-100">30 Günlük Satış</p>
              <p className="text-3xl font-bold">{stats.last30DaysSales.toLocaleString('tr-TR')} ₺</p>
              <p className="text-green-200 text-sm">%-62.95</p>
            </div>
            <div className="text-4xl opacity-80">📈</div>
          </div>
        </div>

        <div className="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-purple-100">7 Günlük Satış</p>
              <p className="text-3xl font-bold">{stats.last7DaysSales.toLocaleString('tr-TR')} ₺</p>
              <p className="text-purple-200 text-sm">%-32.51</p>
            </div>
            <div className="text-4xl opacity-80">📊</div>
          </div>
        </div>

        <div className="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-orange-100">Ödenecek Tutar</p>
              <p className="text-3xl font-bold">{stats.pendingAmount.toLocaleString('tr-TR')} ₺</p>
              <p className="text-orange-200 text-sm">Beklemede</p>
            </div>
            <div className="text-4xl opacity-80">💳</div>
          </div>
        </div>
      </div>

      {/* Erken Ödeme Bilgisi */}
      <div className="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-lg shadow-md p-6 border border-yellow-200">
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-4">
            <div className="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center">
              <span className="text-white text-xl">⚡</span>
            </div>
            <div>
              <h3 className="text-lg font-semibold text-gray-800">Erken Ödeme Fırsatı</h3>
              <p className="text-gray-600">Vadenizi beklemeden paranızı alabilirsiniz!</p>
            </div>
          </div>
          <div className="text-right">
            <p className="text-2xl font-bold text-green-600">
              {(stats.pendingAmount * 0.98).toLocaleString('tr-TR')} ₺
            </p>
            <p className="text-sm text-gray-500">%2 komisyon ile</p>
          </div>
        </div>
      </div>

      {/* Bildirimler */}
      <div className="bg-white rounded-lg shadow-md p-6">
        <div className="flex items-center justify-between mb-4">
          <h2 className="text-xl font-bold text-gray-900 flex items-center">
            <span className="mr-2">🔔</span>
            Bildirimler
          </h2>
          <button className="text-sm text-orange-600 hover:text-orange-700 font-medium">
            Tümünü Görüntüle
          </button>
        </div>
        <div className="space-y-3">
          {notifications.map((notification) => (
            <div key={notification.id} className="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
              <div className={`w-2 h-2 rounded-full mt-2 ${
                notification.type === 'warning' ? 'bg-yellow-500' :
                notification.type === 'success' ? 'bg-green-500' :
                notification.type === 'error' ? 'bg-red-500' : 'bg-blue-500'
              }`}></div>
              <div className="flex-1">
                <p className="text-sm text-gray-900">{notification.message}</p>
                <p className="text-xs text-gray-500 mt-1">{notification.time}</p>
              </div>
              <button className="text-gray-400 hover:text-gray-600">
                <span className="text-sm">✕</span>
              </button>
            </div>
          ))}
        </div>
      </div>

      {/* Hızlı Eylemler */}
      <div className="bg-white rounded-lg shadow-md p-6">
        <h2 className="text-xl font-bold text-gray-900 mb-6 flex items-center">
          <span className="mr-2">⚡</span>
          Hızlı Eylemler
        </h2>
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
          {quickActions.map((action, index) => (
            <button
              key={index}
              onClick={action.action}
              className="flex flex-col items-center p-4 rounded-lg border-2 border-gray-200 hover:border-gray-300 transition-colors group"
            >
              <div 
                className="w-12 h-12 rounded-full flex items-center justify-center text-white text-xl mb-2 group-hover:scale-110 transition-transform"
                style={{ backgroundColor: action.color }}
              >
                {action.icon}
              </div>
              <span className="text-sm font-medium text-gray-700 text-center">{action.title}</span>
            </button>
          ))}
        </div>
      </div>

      {/* Aksiyonlar Bölümü */}
      <div className="bg-white rounded-lg shadow-md p-6">
        <h2 className="text-xl font-bold text-gray-900 mb-6 flex items-center">
          <span className="mr-2">⚡</span>
          Aksiyonlar
        </h2>
        
        {Object.entries(actionCategories).map(([category, items]) => (
          <div key={category} className="mb-8">
            <h3 className="text-lg font-semibold text-gray-800 mb-4 flex items-center">
              <span className="mr-2">
                {category === 'Sipariş & Operasyon' ? '📦' :
                 category === 'Ürün' ? '🛍️' : '🚀'}
              </span>
              {category}
            </h3>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              {items.map((item, index) => (
                <ActionCard key={index} item={item} />
              ))}
            </div>
          </div>
        ))}
      </div>

      {/* Alt Bölüm - Son Siparişler ve Popüler Ürünler */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Son Siparişler */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <span className="mr-2">🛒</span>
            Son Siparişler
          </h3>
          <div className="space-y-3">
            {recentOrders.map((order, index) => (
              <div key={index} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div className="flex-1">
                  <div className="flex items-center justify-between">
                    <span className="font-medium text-gray-900">{order.id}</span>
                    <span className="text-lg font-bold text-green-600">{order.amount.toFixed(2)} ₺</span>
                  </div>
                  <div className="flex items-center justify-between mt-1">
                    <span className="text-sm text-gray-600">{order.customer}</span>
                    <span className="text-xs text-gray-500">{order.time}</span>
                  </div>
                </div>
                <div className="ml-4">
                  <span className={`px-2 py-1 text-xs font-medium rounded-full ${
                    order.status === 'Teslim Edildi' ? 'bg-green-100 text-green-800' :
                    order.status === 'Kargoda' ? 'bg-blue-100 text-blue-800' :
                    order.status === 'Hazırlanıyor' ? 'bg-yellow-100 text-yellow-800' :
                    order.status === 'İptal Edildi' ? 'bg-red-100 text-red-800' :
                    'bg-gray-100 text-gray-800'
                  }`}>
                    {order.status}
                  </span>
                </div>
              </div>
            ))}
          </div>
          <div className="mt-4 text-center">
            <button className="text-orange-600 hover:text-orange-700 font-medium text-sm">
              Tüm Siparişleri Görüntüle →
            </button>
          </div>
        </div>

        {/* Popüler Ürünler */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <span className="mr-2">🔥</span>
            Popüler Ürünler
          </h3>
          <div className="space-y-3">
            {popularProducts.map((product, index) => (
              <div key={index} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div className="flex-1">
                  <div className="font-medium text-gray-900 text-sm">{product.name}</div>
                  <div className="flex items-center justify-between mt-1">
                    <span className="text-sm text-gray-600">{product.sales} satış</span>
                    <span className="text-sm font-bold text-green-600">{product.price.toFixed(2)} ₺</span>
                  </div>
                </div>
                <div className="ml-4 text-right">
                  <div className={`text-xs font-medium ${
                    product.stock > 50 ? 'text-green-600' :
                    product.stock > 20 ? 'text-yellow-600' : 'text-red-600'
                  }`}>
                    Stok: {product.stock}
                  </div>
                </div>
              </div>
            ))}
          </div>
          <div className="mt-4 text-center">
            <button className="text-orange-600 hover:text-orange-700 font-medium text-sm">
              Tüm Ürünleri Görüntüle →
            </button>
          </div>
        </div>
      </div>

      {/* Grafikler */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Haftalık Satış Trendi */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">📈 Haftalık Satış Trendi</h3>
          <ResponsiveContainer width="100%" height={300}>
            <AreaChart data={salesChartData}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="name" />
              <YAxis />
              <Tooltip formatter={(value, name) => [
                name === 'sales' ? `${value} ₺` : `${value} adet`,
                name === 'sales' ? 'Satış' : 'Sipariş'
              ]} />
              <Area type="monotone" dataKey="sales" stackId="1" stroke="#FF6B35" fill="#FF6B35" fillOpacity={0.6} />
            </AreaChart>
          </ResponsiveContainer>
        </div>

        {/* Kategori Dağılımı */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">🏷️ Kategori Dağılımı</h3>
          <ResponsiveContainer width="100%" height={300}>
            <PieChart>
              <Pie
                data={categoryData}
                cx="50%"
                cy="50%"
                labelLine={false}
                label={({ name, percent }) => `${name} %${(percent * 100).toFixed(0)}`}
                outerRadius={80}
                fill="#8884d8"
                dataKey="value"
              >
                {categoryData.map((entry, index) => (
                  <Cell key={`cell-${index}`} fill={entry.color} />
                ))}
              </Pie>
              <Tooltip />
            </PieChart>
          </ResponsiveContainer>
        </div>
      </div>

      {/* Detaylı İstatistikler */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-white rounded-lg shadow-md p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">📊 Sipariş İstatistikleri</h3>
          <div className="space-y-4">
            <div className="flex justify-between">
              <span className="text-gray-600">Toplam Sipariş</span>
              <span className="font-semibold">{stats.totalOrders}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-600">Ortalama Sipariş Değeri</span>
              <span className="font-semibold">{stats.avgOrderValue.toFixed(2)} ₺</span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-600">Dönüşüm Oranı</span>
              <span className="font-semibold">%{stats.conversionRate.toFixed(2)}</span>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-md p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">🛍️ Ürün Bilgileri</h3>
          <div className="space-y-4">
            <div className="flex justify-between">
              <span className="text-gray-600">Aktif Ürün</span>
              <span className="font-semibold">{stats.totalProducts}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-600">Zayıf İçerikli</span>
              <span className="font-semibold text-red-600">197</span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-600">Bekleyen Belge</span>
              <span className="font-semibold text-orange-600">19</span>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-md p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">👥 Müşteri Bilgileri</h3>
          <div className="space-y-4">
            <div className="flex justify-between">
              <span className="text-gray-600">Toplam Müşteri</span>
              <span className="font-semibold">{stats.totalCustomers}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-600">Müşteri Başına Ortalama</span>
              <span className="font-semibold">
                {stats.totalCustomers > 0 ? (stats.last30DaysSales / stats.totalCustomers).toFixed(2) : 0} ₺
              </span>
            </div>
            <div className="flex justify-between">
              <span className="text-gray-600">Tekrar Alışveriş Oranı</span>
              <span className="font-semibold">%23.4</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default TrendyolDashboard; 