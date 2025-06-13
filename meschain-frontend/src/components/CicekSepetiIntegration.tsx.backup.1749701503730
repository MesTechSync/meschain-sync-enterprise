import React, { useState, useEffect } from 'react';
import {
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  LineChart,
  Line,
  PieChart,
  Pie,
  Cell
} from 'recharts';

interface CicekSepetiCredentials {
  merchantId: string;
  apiKey: string;
  apiSecret: string;
  testMode: boolean;
}

interface CicekSepetiProduct {
  id: string;
  name: string;
  category: 'flowers' | 'plants' | 'gifts' | 'arrangements';
  occasion: string[];
  price: number;
  deliveryType: 'same_day' | 'next_day' | 'scheduled';
  availableRegions: string[];
  seasonality: string;
  careInstructions?: string;
  images: string[];
  status: 'active' | 'seasonal' | 'out_of_stock';
}

interface DeliverySchedule {
  date: string;
  timeSlots: TimeSlot[];
}

interface TimeSlot {
  id: string;
  startTime: string;
  endTime: string;
  available: boolean;
  price: number;
}

interface PersonalizedMessage {
  cardType: 'standard' | 'premium' | 'custom';
  message: string;
  senderName: string;
  recipientName: string;
  occasion: string;
}

interface CicekSepetiOrder {
  orderNumber: string;
  orderDate: string;
  deliveryDate: string;
  deliveryTime: string;
  recipientInfo: {
    name: string;
    phone: string;
    address: string;
    city: string;
    district: string;
  };
  products: CicekSepetiProduct[];
  personalizedMessage?: PersonalizedMessage;
  totalAmount: number;
  status: 'pending' | 'confirmed' | 'preparing' | 'delivered' | 'cancelled';
}

interface CicekSepetiStats {
  totalOrders: number;
  deliveredOrders: number;
  pendingOrders: number;
  monthlyRevenue: number;
  popularOccasions: { name: string; count: number }[];
  deliverySuccessRate: number;
}

const CicekSepetiIntegration: React.FC = () => {
  const [credentials, setCredentials] = useState<CicekSepetiCredentials>({
    merchantId: '',
    apiKey: '',
    apiSecret: '',
    testMode: true
  });
  const [isConnected, setIsConnected] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [activeTab, setActiveTab] = useState<'setup' | 'products' | 'orders' | 'delivery' | 'analytics'>('setup');
  const [products, setProducts] = useState<CicekSepetiProduct[]>([]);
  const [orders, setOrders] = useState<CicekSepetiOrder[]>([]);
  const [stats, setStats] = useState<CicekSepetiStats>({
    totalOrders: 0,
    deliveredOrders: 0,
    pendingOrders: 0,
    monthlyRevenue: 0,
    popularOccasions: [],
    deliverySuccessRate: 0
  });
  const [selectedOccasion, setSelectedOccasion] = useState<string>('');
  const [deliverySchedule, setDeliverySchedule] = useState<DeliverySchedule[]>([]);

  // Özel günler listesi
  const occasions = [
    'Sevgililer Günü', 'Anneler Günü', 'Babalar Günü', 'Doğum Günü',
    'Yıldönümü', 'Mezuniyet', 'Yeni İş', 'Geçmiş Olsun', 'Tebrikler',
    'Özür Dilerim', 'Teşekkürler', 'Düğün', 'Nişan', 'Kına Gecesi'
  ];

  // Test bağlantısı
  const testConnection = async () => {
    setIsLoading(true);
    try {
      const response = await fetch('/api/marketplace/ciceksepeti/test-connection', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(credentials)
      });

      if (response.ok) {
        setIsConnected(true);
        alert('ÇiçekSepeti bağlantısı başarılı!');
        await loadDashboardData();
      } else {
        throw new Error('Bağlantı başarısız');
      }
    } catch (error) {
      console.error('Bağlantı hatası:', error);
      alert('Bağlantı başarısız. Lütfen bilgilerinizi kontrol edin.');
    } finally {
      setIsLoading(false);
    }
  };

  // Dashboard verilerini yükle
  const loadDashboardData = async () => {
    try {
      const [productsRes, ordersRes, statsRes] = await Promise.all([
        fetch('/api/marketplace/ciceksepeti/products'),
        fetch('/api/marketplace/ciceksepeti/orders'),
        fetch('/api/marketplace/ciceksepeti/stats')
      ]);

      if (productsRes.ok) {
        const productsData = await productsRes.json();
        setProducts(productsData);
      }

      if (ordersRes.ok) {
        const ordersData = await ordersRes.json();
        setOrders(ordersData);
      }

      if (statsRes.ok) {
        const statsData = await statsRes.json();
        setStats(statsData);
      }
    } catch (error) {
      console.error('Veri yükleme hatası:', error);
    }
  };

  // Teslimat takvimi yükle
  const loadDeliverySchedule = async () => {
    try {
      const response = await fetch('/api/marketplace/ciceksepeti/delivery-schedule');
      if (response.ok) {
        const scheduleData = await response.json();
        setDeliverySchedule(scheduleData);
      }
    } catch (error) {
      console.error('Teslimat takvimi yükleme hatası:', error);
    }
  };

  // Chart verileri
  const revenueData = [
    { month: 'Ocak', revenue: 25000 },
    { month: 'Şubat', revenue: 45000 }, // Sevgililer günü
    { month: 'Mart', revenue: 32000 },
    { month: 'Nisan', revenue: 28000 },
    { month: 'Mayıs', revenue: 55000 }, // Anneler günü
    { month: 'Haziran', revenue: 38000 }
  ];

  const occasionData = stats.popularOccasions.map(occasion => ({
    name: occasion.name,
    value: occasion.count,
    color: getOccasionColor(occasion.name)
  }));

  function getOccasionColor(occasion: string): string {
    const colors: { [key: string]: string } = {
      'Sevgililer Günü': '#EF4444',
      'Anneler Günü': '#EC4899',
      'Doğum Günü': '#F59E0B',
      'Yıldönümü': '#8B5CF6',
      'Mezuniyet': '#10B981'
    };
    return colors[occasion] || '#6B7280';
  }

  useEffect(() => {
    const savedCredentials = localStorage.getItem('ciceksepeti_credentials');
    if (savedCredentials) {
      const parsed = JSON.parse(savedCredentials);
      setCredentials(parsed);
      setIsConnected(true);
      loadDashboardData();
      loadDeliverySchedule();
    }
  }, []);

  return (
    <div className="max-w-7xl mx-auto p-6">
      {/* Header */}
      <div className="mb-8">
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-4">
            <div className="w-12 h-12 bg-pink-500 rounded-lg flex items-center justify-center">
              <span className="text-white font-bold text-xl">🌸</span>
            </div>
            <div>
              <h1 className="text-3xl font-bold text-gray-900">ÇiçekSepeti Entegrasyonu</h1>
              <p className="text-gray-600">Çiçek ve hediye teslimat platformu</p>
            </div>
          </div>
          <div className="flex items-center space-x-2">
            <div className={`w-3 h-3 rounded-full ${isConnected ? 'bg-green-500' : 'bg-red-500'}`}></div>
            <span className={`text-sm font-medium ${isConnected ? 'text-green-600' : 'text-red-600'}`}>
              {isConnected ? 'Bağlı' : 'Bağlı Değil'}
            </span>
          </div>
        </div>
      </div>

      {/* Navigation Tabs */}
      <div className="border-b border-gray-200 mb-6">
        <nav className="-mb-px flex space-x-8">
          {[
            { id: 'setup', name: 'Kurulum', icon: '⚙️' },
            { id: 'products', name: 'Ürünler', icon: '🌹' },
            { id: 'orders', name: 'Siparişler', icon: '📦' },
            { id: 'delivery', name: 'Teslimat', icon: '🚚' },
            { id: 'analytics', name: 'Analitik', icon: '📊' }
          ].map((tab) => (
            <button
              key={tab.id}
              onClick={() => setActiveTab(tab.id as any)}
              className={`py-2 px-1 border-b-2 font-medium text-sm ${
                activeTab === tab.id
                  ? 'border-pink-500 text-pink-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              }`}
            >
              <span className="mr-2">{tab.icon}</span>
              {tab.name}
            </button>
          ))}
        </nav>
      </div>

      {/* Setup Tab */}
      {activeTab === 'setup' && (
        <div className="space-y-6">
          {/* API Setup */}
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 className="text-xl font-semibold text-gray-900 mb-6">ÇiçekSepeti API Ayarları</h2>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Merchant ID
                </label>
                <input
                  type="text"
                  value={credentials.merchantId}
                  onChange={(e) => setCredentials({...credentials, merchantId: e.target.value})}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500"
                  placeholder="ÇiçekSepeti Merchant ID'nizi girin"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  API Key
                </label>
                <input
                  type="text"
                  value={credentials.apiKey}
                  onChange={(e) => setCredentials({...credentials, apiKey: e.target.value})}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500"
                  placeholder="API Key'inizi girin"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  API Secret
                </label>
                <input
                  type="password"
                  value={credentials.apiSecret}
                  onChange={(e) => setCredentials({...credentials, apiSecret: e.target.value})}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500"
                  placeholder="API Secret'ınızı girin"
                />
              </div>

              <div>
                <label className="flex items-center">
                  <input
                    type="checkbox"
                    checked={credentials.testMode}
                    onChange={(e) => setCredentials({...credentials, testMode: e.target.checked})}
                    className="rounded border-gray-300 text-pink-600 focus:ring-pink-500"
                  />
                  <span className="ml-2 text-sm text-gray-700">Test Modu</span>
                </label>
                <p className="text-xs text-gray-500 mt-1">
                  Test modunda gerçek teslimatlar yapılmaz
                </p>
              </div>
            </div>

            <div className="mt-6 flex space-x-4">
              <button
                onClick={testConnection}
                disabled={isLoading || !credentials.merchantId || !credentials.apiKey}
                className="bg-pink-600 text-white px-6 py-2 rounded-md hover:bg-pink-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {isLoading ? 'Test Ediliyor...' : 'Bağlantıyı Test Et'}
              </button>
              
              {isConnected && (
                <button
                  onClick={() => {
                    localStorage.setItem('ciceksepeti_credentials', JSON.stringify(credentials));
                    alert('Ayarlar kaydedildi');
                  }}
                  className="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700"
                >
                  Ayarları Kaydet
                </button>
              )}
            </div>

            {/* API Bilgilendirme */}
            <div className="mt-8 bg-pink-50 border border-pink-200 rounded-md p-4">
              <h3 className="text-sm font-medium text-pink-800 mb-2">🌸 ÇiçekSepeti Özel Özellikleri</h3>
              <ul className="text-sm text-pink-700 space-y-1">
                <li>• Özel gün bazlı ürün yönetimi</li>
                <li>• Teslimat tarihi ve saat seçimi</li>
                <li>• Kişiselleştirilmiş mesaj kartları</li>
                <li>• Mevsimsel ürün takibi</li>
                <li>• Bölgesel teslimat yönetimi</li>
              </ul>
            </div>
          </div>

          {/* Delivery Settings */}
          {isConnected && (
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h2 className="text-xl font-semibold text-gray-900 mb-6">Teslimat Ayarları</h2>
              
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Teslimat Bölgeleri
                  </label>
                  <select className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500">
                    <option value="">Bölge seçin</option>
                    <option value="istanbul">İstanbul</option>
                    <option value="ankara">Ankara</option>
                    <option value="izmir">İzmir</option>
                    <option value="bursa">Bursa</option>
                    <option value="antalya">Antalya</option>
                  </select>
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Aynı Gün Teslimat
                  </label>
                  <select className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500">
                    <option value="enabled">Aktif</option>
                    <option value="disabled">Pasif</option>
                  </select>
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Teslimat Saatleri
                  </label>
                  <input
                    type="text"
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500"
                    placeholder="09:00-18:00"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Özel Gün Fiyatlandırması
                  </label>
                  <input
                    type="number"
                    step="0.01"
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500"
                    placeholder="Ek ücret (%)"
                  />
                </div>
              </div>

              <div className="mt-6">
                <button className="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                  Teslimat Ayarlarını Kaydet
                </button>
              </div>
            </div>
          )}
        </div>
      )}

      {/* Products Tab */}
      {activeTab === 'products' && isConnected && (
        <div className="space-y-6">
          {/* Occasion Filter */}
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div className="flex items-center justify-between">
              <h3 className="text-lg font-medium text-gray-900">Özel Gün Filtresi</h3>
              <select
                value={selectedOccasion}
                onChange={(e) => setSelectedOccasion(e.target.value)}
                className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500"
              >
                <option value="">Tüm Özel Günler</option>
                {occasions.map(occasion => (
                  <option key={occasion} value={occasion}>{occasion}</option>
                ))}
              </select>
            </div>
          </div>

          {/* Product Grid */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {products.filter(product => 
              !selectedOccasion || product.occasion.includes(selectedOccasion)
            ).map((product) => (
              <div key={product.id} className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div className="aspect-w-16 aspect-h-9">
                  <img
                    src={product.images[0] || '/placeholder-flower.png'}
                    alt={product.name}
                    className="w-full h-48 object-cover"
                  />
                </div>
                <div className="p-4">
                  <h4 className="text-lg font-medium text-gray-900 mb-2">{product.name}</h4>
                  <div className="flex items-center justify-between mb-2">
                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                      product.category === 'flowers' ? 'bg-pink-100 text-pink-800' :
                      product.category === 'plants' ? 'bg-green-100 text-green-800' :
                      product.category === 'gifts' ? 'bg-purple-100 text-purple-800' :
                      'bg-blue-100 text-blue-800'
                    }`}>
                      {product.category === 'flowers' ? '🌹 Çiçek' :
                       product.category === 'plants' ? '🌱 Bitki' :
                       product.category === 'gifts' ? '🎁 Hediye' : '💐 Aranjman'}
                    </span>
                    <span className="text-lg font-bold text-gray-900">{product.price}₺</span>
                  </div>
                  <div className="mb-3">
                    <p className="text-sm text-gray-600 mb-1">Özel Günler:</p>
                    <div className="flex flex-wrap gap-1">
                      {product.occasion.slice(0, 3).map(occasion => (
                        <span key={occasion} className="inline-flex px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">
                          {occasion}
                        </span>
                      ))}
                      {product.occasion.length > 3 && (
                        <span className="inline-flex px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">
                          +{product.occasion.length - 3}
                        </span>
                      )}
                    </div>
                  </div>
                  <div className="flex items-center justify-between">
                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                      product.deliveryType === 'same_day' ? 'bg-red-100 text-red-800' :
                      product.deliveryType === 'next_day' ? 'bg-yellow-100 text-yellow-800' :
                      'bg-blue-100 text-blue-800'
                    }`}>
                      {product.deliveryType === 'same_day' ? '⚡ Aynı Gün' :
                       product.deliveryType === 'next_day' ? '📅 Ertesi Gün' : '🗓️ Planlı'}
                    </span>
                    <button className="text-pink-600 hover:text-pink-900 text-sm font-medium">
                      Düzenle
                    </button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Orders Tab */}
      {activeTab === 'orders' && isConnected && (
        <div className="space-y-6">
          {/* Order Stats */}
          <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <div className="flex items-center">
                <div className="p-2 bg-pink-100 rounded-lg">
                  <span className="text-2xl">📦</span>
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-600">Toplam Sipariş</p>
                  <p className="text-2xl font-bold text-gray-900">{stats.totalOrders}</p>
                </div>
              </div>
            </div>

            <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <div className="flex items-center">
                <div className="p-2 bg-green-100 rounded-lg">
                  <span className="text-2xl">✅</span>
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-600">Teslim Edilen</p>
                  <p className="text-2xl font-bold text-green-600">{stats.deliveredOrders}</p>
                </div>
              </div>
            </div>

            <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <div className="flex items-center">
                <div className="p-2 bg-yellow-100 rounded-lg">
                  <span className="text-2xl">⏳</span>
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-600">Bekleyen</p>
                  <p className="text-2xl font-bold text-yellow-600">{stats.pendingOrders}</p>
                </div>
              </div>
            </div>

            <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <div className="flex items-center">
                <div className="p-2 bg-blue-100 rounded-lg">
                  <span className="text-2xl">📈</span>
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-600">Başarı Oranı</p>
                  <p className="text-2xl font-bold text-blue-600">{stats.deliverySuccessRate}%</p>
                </div>
              </div>
            </div>
          </div>

          {/* Orders List */}
          <div className="bg-white rounded-lg shadow-sm border border-gray-200">
            <div className="px-6 py-4 border-b border-gray-200">
              <h3 className="text-lg font-medium text-gray-900">Son Siparişler</h3>
            </div>
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Sipariş
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Alıcı
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Teslimat
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Tutar
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Durum
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      İşlemler
                    </th>
                  </tr>
                </thead>
                <tbody className="bg-white divide-y divide-gray-200">
                  {orders.map((order) => (
                    <tr key={order.orderNumber} className="hover:bg-gray-50">
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div>
                          <div className="text-sm font-medium text-gray-900">{order.orderNumber}</div>
                          <div className="text-sm text-gray-500">{new Date(order.orderDate).toLocaleDateString('tr-TR')}</div>
                        </div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div>
                          <div className="text-sm font-medium text-gray-900">{order.recipientInfo.name}</div>
                          <div className="text-sm text-gray-500">{order.recipientInfo.city}</div>
                        </div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div>
                          <div className="text-sm font-medium text-gray-900">{new Date(order.deliveryDate).toLocaleDateString('tr-TR')}</div>
                          <div className="text-sm text-gray-500">{order.deliveryTime}</div>
                        </div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {order.totalAmount.toLocaleString('tr-TR')}₺
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                          order.status === 'delivered' ? 'bg-green-100 text-green-800' :
                          order.status === 'preparing' ? 'bg-blue-100 text-blue-800' :
                          order.status === 'confirmed' ? 'bg-yellow-100 text-yellow-800' :
                          order.status === 'cancelled' ? 'bg-red-100 text-red-800' :
                          'bg-gray-100 text-gray-800'
                        }`}>
                          {order.status === 'delivered' ? 'Teslim Edildi' :
                           order.status === 'preparing' ? 'Hazırlanıyor' :
                           order.status === 'confirmed' ? 'Onaylandı' :
                           order.status === 'cancelled' ? 'İptal' : 'Bekliyor'}
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div className="flex space-x-2">
                          <button className="text-pink-600 hover:text-pink-900 text-xs">
                            Detay
                          </button>
                          {order.personalizedMessage && (
                            <button className="text-blue-600 hover:text-blue-900 text-xs">
                              Mesaj
                            </button>
                          )}
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}

      {/* Delivery Tab */}
      {activeTab === 'delivery' && isConnected && (
        <div className="space-y-6">
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 className="text-lg font-medium text-gray-900 mb-4">Teslimat Takvimi</h3>
            
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {deliverySchedule.map((schedule) => (
                <div key={schedule.date} className="border border-gray-200 rounded-lg p-4">
                  <h4 className="font-medium text-gray-900 mb-3">
                    {new Date(schedule.date).toLocaleDateString('tr-TR', { 
                      weekday: 'long', 
                      year: 'numeric', 
                      month: 'long', 
                      day: 'numeric' 
                    })}
                  </h4>
                  <div className="space-y-2">
                    {schedule.timeSlots.map((slot) => (
                      <div key={slot.id} className={`p-2 rounded text-sm ${
                        slot.available 
                          ? 'bg-green-50 text-green-800 border border-green-200' 
                          : 'bg-red-50 text-red-800 border border-red-200'
                      }`}>
                        <div className="flex justify-between items-center">
                          <span>{slot.startTime} - {slot.endTime}</span>
                          <span className="font-medium">{slot.price}₺</span>
                        </div>
                        <div className="text-xs mt-1">
                          {slot.available ? '✅ Müsait' : '❌ Dolu'}
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      )}

      {/* Analytics Tab */}
      {activeTab === 'analytics' && isConnected && (
        <div className="space-y-6">
          {/* Revenue Chart */}
          <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 className="text-lg font-medium text-gray-900 mb-4">Aylık Gelir Trendi</h3>
            <ResponsiveContainer width="100%" height={300}>
              <LineChart data={revenueData}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="month" />
                <YAxis />
                <Tooltip formatter={(value) => [`${value.toLocaleString('tr-TR')}₺`, 'Gelir']} />
                <Line type="monotone" dataKey="revenue" stroke="#EC4899" strokeWidth={2} />
              </LineChart>
            </ResponsiveContainer>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {/* Popular Occasions */}
            <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <h3 className="text-lg font-medium text-gray-900 mb-4">Popüler Özel Günler</h3>
              <ResponsiveContainer width="100%" height={250}>
                <PieChart>
                  <Pie
                    data={occasionData}
                    cx="50%"
                    cy="50%"
                    outerRadius={80}
                    fill="#8884d8"
                    dataKey="value"
                    label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                  >
                    {occasionData.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={entry.color} />
                    ))}
                  </Pie>
                  <Tooltip />
                </PieChart>
              </ResponsiveContainer>
            </div>

            {/* Delivery Performance */}
            <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <h3 className="text-lg font-medium text-gray-900 mb-4">Teslimat Performansı</h3>
              <div className="space-y-4">
                <div className="flex justify-between items-center">
                  <span className="text-sm text-gray-600">Zamanında Teslimat</span>
                  <span className="text-sm font-medium text-gray-900">{stats.deliverySuccessRate}%</span>
                </div>
                <div className="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    className="bg-green-500 h-2 rounded-full" 
                    style={{ width: `${stats.deliverySuccessRate}%` }}
                  ></div>
                </div>

                <div className="flex justify-between items-center">
                  <span className="text-sm text-gray-600">Aynı Gün Teslimat</span>
                  <span className="text-sm font-medium text-gray-900">85%</span>
                </div>
                <div className="w-full bg-gray-200 rounded-full h-2">
                  <div className="bg-pink-500 h-2 rounded-full" style={{ width: '85%' }}></div>
                </div>

                <div className="flex justify-between items-center">
                  <span className="text-sm text-gray-600">Müşteri Memnuniyeti</span>
                  <span className="text-sm font-medium text-gray-900">4.8/5</span>
                </div>
                <div className="w-full bg-gray-200 rounded-full h-2">
                  <div className="bg-blue-500 h-2 rounded-full" style={{ width: '96%' }}></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Connection Warning */}
      {!isConnected && activeTab !== 'setup' && (
        <div className="bg-yellow-50 border border-yellow-200 rounded-md p-4">
          <div className="flex">
            <div className="flex-shrink-0">
              <span className="text-yellow-400 text-xl">⚠️</span>
            </div>
            <div className="ml-3">
              <h3 className="text-sm font-medium text-yellow-800">
                ÇiçekSepeti Bağlantısı Gerekli
              </h3>
              <div className="mt-2 text-sm text-yellow-700">
                <p>
                  Bu sekmeyi görüntülemek için önce ÇiçekSepeti API bağlantısını kurmanız gerekiyor.
                  <button 
                    onClick={() => setActiveTab('setup')}
                    className="ml-1 font-medium underline hover:text-yellow-900"
                  >
                    Kurulum sekmesine git
                  </button>
                </p>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default CicekSepetiIntegration; 