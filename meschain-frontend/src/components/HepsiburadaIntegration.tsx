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

interface HepsiburadaCredentials {
  merchantId: string;
  username: string;
  password: string;
  testMode: boolean;
}

interface HepsiburadaProduct {
  id: string;
  sku: string;
  productName: string;
  categoryId: string;
  categoryName: string;
  price: number;
  listPrice: number;
  stockQuantity: number;
  images: string[];
  status: 'active' | 'passive' | 'waiting_approval';
  merchantSku: string;
}

interface HepsiburadaOrder {
  orderNumber: string;
  orderDate: string;
  customerId: string;
  customerName: string;
  status: string;
  totalAmount: number;
  items: HepsiburadaOrderItem[];
}

interface HepsiburadaOrderItem {
  lineItemId: string;
  merchantSku: string;
  productName: string;
  quantity: number;
  price: number;
}

interface HepsiburadaStats {
  totalProducts: number;
  activeProducts: number;
  waitingApproval: number;
  totalOrders: number;
  monthlyRevenue: number;
  averageRating: number;
  commissionRate: number;
}

const HepsiburadaIntegration: React.FC = () => {
  const [credentials, setCredentials] = useState<HepsiburadaCredentials>({
    merchantId: '',
    username: '',
    password: '',
    testMode: true
  });
  const [isConnected, setIsConnected] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [activeTab, setActiveTab] = useState<'setup' | 'products' | 'orders' | 'analytics'>('setup');
  const [products, setProducts] = useState<HepsiburadaProduct[]>([]);
  const [orders, setOrders] = useState<HepsiburadaOrder[]>([]);
  const [stats, setStats] = useState<HepsiburadaStats>({
    totalProducts: 0,
    activeProducts: 0,
    waitingApproval: 0,
    totalOrders: 0,
    monthlyRevenue: 0,
    averageRating: 0,
    commissionRate: 0
  });

  // Test bağlantısı
  const testConnection = async () => {
    setIsLoading(true);
    try {
      const response = await fetch('/api/marketplace/hepsiburada/test-connection', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(credentials)
      });

      if (response.ok) {
        setIsConnected(true);
        alert('Hepsiburada bağlantısı başarılı!');
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
        fetch('/api/marketplace/hepsiburada/products'),
        fetch('/api/marketplace/hepsiburada/orders'),
        fetch('/api/marketplace/hepsiburada/stats')
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

  // Chart verileri
  const revenueData = [
    { month: 'Ocak', revenue: 38000 },
    { month: 'Şubat', revenue: 42000 },
    { month: 'Mart', revenue: 39000 },
    { month: 'Nisan', revenue: 51000 },
    { month: 'Mayıs', revenue: 47000 },
    { month: 'Haziran', revenue: 58000 }
  ];

  const productStatusData = [
    { name: 'Aktif', value: stats.activeProducts, color: '#10B981' },
    { name: 'Onay Bekliyor', value: stats.waitingApproval, color: '#F59E0B' },
    { name: 'Pasif', value: stats.totalProducts - stats.activeProducts - stats.waitingApproval, color: '#6B7280' }
  ];

  useEffect(() => {
    const savedCredentials = localStorage.getItem('hepsiburada_credentials');
    if (savedCredentials) {
      const parsed = JSON.parse(savedCredentials);
      setCredentials(parsed);
      setIsConnected(true);
      loadDashboardData();
    }
  }, []);

  return (
    <div className="max-w-7xl mx-auto p-6">
      {/* Header */}
      <div className="mb-8">
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-4">
            <div className="w-12 h-12 bg-orange-600 rounded-lg flex items-center justify-center">
              <span className="text-white font-bold text-xl">H</span>
            </div>
            <div>
              <h1 className="text-3xl font-bold text-gray-900">Hepsiburada Entegrasyonu</h1>
              <p className="text-gray-600">Türkiye'nin teknoloji lideri e-ticaret platformu</p>
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
            { id: 'products', name: 'Ürünler', icon: '📦' },
            { id: 'orders', name: 'Siparişler', icon: '🛒' },
            { id: 'analytics', name: 'Analitik', icon: '📊' }
          ].map((tab) => (
            <button
              key={tab.id}
              onClick={() => setActiveTab(tab.id as any)}
              className={`py-2 px-1 border-b-2 font-medium text-sm ${
                activeTab === tab.id
                  ? 'border-orange-600 text-orange-600'
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
          {/* Merchant Setup */}
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 className="text-xl font-semibold text-gray-900 mb-6">Hepsiburada Merchant Ayarları</h2>
            
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Merchant ID
                </label>
                <input
                  type="text"
                  value={credentials.merchantId}
                  onChange={(e) => setCredentials({...credentials, merchantId: e.target.value})}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600"
                  placeholder="Hepsiburada Merchant ID'nizi girin"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Kullanıcı Adı
                </label>
                <input
                  type="text"
                  value={credentials.username}
                  onChange={(e) => setCredentials({...credentials, username: e.target.value})}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600"
                  placeholder="API kullanıcı adınızı girin"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Şifre
                </label>
                <input
                  type="password"
                  value={credentials.password}
                  onChange={(e) => setCredentials({...credentials, password: e.target.value})}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600"
                  placeholder="API şifrenizi girin"
                />
              </div>

              <div>
                <label className="flex items-center">
                  <input
                    type="checkbox"
                    checked={credentials.testMode}
                    onChange={(e) => setCredentials({...credentials, testMode: e.target.checked})}
                    className="rounded border-gray-300 text-orange-600 focus:ring-orange-600"
                  />
                  <span className="ml-2 text-sm text-gray-700">Test Modu</span>
                </label>
                <p className="text-xs text-gray-500 mt-1">
                  Test modunda gerçek işlemler yapılmaz
                </p>
              </div>
            </div>

            <div className="mt-6 flex space-x-4">
              <button
                onClick={testConnection}
                disabled={isLoading || !credentials.merchantId || !credentials.username}
                className="bg-orange-600 text-white px-6 py-2 rounded-md hover:bg-orange-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {isLoading ? 'Test Ediliyor...' : 'Bağlantıyı Test Et'}
              </button>
              
              {isConnected && (
                <button
                  onClick={() => {
                    localStorage.setItem('hepsiburada_credentials', JSON.stringify(credentials));
                    alert('Ayarlar kaydedildi');
                  }}
                  className="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700"
                >
                  Ayarları Kaydet
                </button>
              )}
            </div>

            {/* API Bilgilendirme */}
            <div className="mt-8 bg-orange-50 border border-orange-200 rounded-md p-4">
              <h3 className="text-sm font-medium text-orange-800 mb-2">📋 API Bilgileri Nasıl Alınır?</h3>
              <ol className="text-sm text-orange-700 space-y-1">
                <li>1. Hepsiburada Merchant Paneli'ne giriş yapın</li>
                <li>2. Entegrasyon → API Yönetimi bölümüne gidin</li>
                <li>3. Yeni API kullanıcısı oluşturun</li>
                <li>4. Merchant ID, kullanıcı adı ve şifre bilgilerini alın</li>
              </ol>
            </div>
          </div>

          {/* Store Configuration */}
          {isConnected && (
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h2 className="text-xl font-semibold text-gray-900 mb-6">Mağaza Konfigürasyonu</h2>
              
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Mağaza Adı
                  </label>
                  <input
                    type="text"
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600"
                    placeholder="Mağaza adınızı girin"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Komisyon Oranı
                  </label>
                  <input
                    type="number"
                    step="0.01"
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600"
                    placeholder="Komisyon oranı (%)"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Kargo Şirketi
                  </label>
                  <select className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600">
                    <option value="">Kargo şirketi seçin</option>
                    <option value="yurtici">Yurtiçi Kargo</option>
                    <option value="aras">Aras Kargo</option>
                    <option value="mng">MNG Kargo</option>
                    <option value="ptt">PTT Kargo</option>
                  </select>
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Hazırlama Süresi (Gün)
                  </label>
                  <input
                    type="number"
                    min="1"
                    max="30"
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-600"
                    placeholder="Ürün hazırlama süresi"
                  />
                </div>
              </div>

              <div className="mt-6">
                <button className="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                  Mağaza Ayarlarını Kaydet
                </button>
              </div>
            </div>
          )}
        </div>
      )}

      {/* Products Tab */}
      {activeTab === 'products' && isConnected && (
        <div className="space-y-6">
          {/* Ürün İstatistikleri */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <div className="flex items-center">
                <div className="p-2 bg-blue-100 rounded-lg">
                  <span className="text-2xl">📦</span>
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-600">Toplam Ürün</p>
                  <p className="text-2xl font-bold text-gray-900">{stats.totalProducts}</p>
                </div>
              </div>
            </div>

            <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <div className="flex items-center">
                <div className="p-2 bg-green-100 rounded-lg">
                  <span className="text-2xl">✅</span>
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-600">Aktif Ürün</p>
                  <p className="text-2xl font-bold text-green-600">{stats.activeProducts}</p>
                </div>
              </div>
            </div>

            <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <div className="flex items-center">
                <div className="p-2 bg-yellow-100 rounded-lg">
                  <span className="text-2xl">⏳</span>
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-600">Onay Bekliyor</p>
                  <p className="text-2xl font-bold text-yellow-600">{stats.waitingApproval}</p>
                </div>
              </div>
            </div>
          </div>

          {/* Ürün Listesi */}
          <div className="bg-white rounded-lg shadow-sm border border-gray-200">
            <div className="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
              <h3 className="text-lg font-medium text-gray-900">Ürün Listesi</h3>
              <button className="bg-orange-600 text-white px-4 py-2 rounded-md hover:bg-orange-700 text-sm">
                Yeni Ürün Ekle
              </button>
            </div>
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Ürün
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      SKU
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Kategori
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Fiyat
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Stok
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
                  {products.map((product) => (
                    <tr key={product.id} className="hover:bg-gray-50">
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="flex items-center">
                          <div className="flex-shrink-0 h-10 w-10">
                            <img
                              className="h-10 w-10 rounded-md object-cover"
                              src={product.images[0] || '/placeholder-product.png'}
                              alt={product.productName}
                            />
                          </div>
                          <div className="ml-4">
                            <div className="text-sm font-medium text-gray-900 truncate max-w-xs">
                              {product.productName}
                            </div>
                          </div>
                        </div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {product.merchantSku}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {product.categoryName}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <div>
                          <span className="font-medium">{product.price.toLocaleString('tr-TR')}₺</span>
                          {product.listPrice !== product.price && (
                            <div className="text-xs text-gray-500 line-through">
                              {product.listPrice.toLocaleString('tr-TR')}₺
                            </div>
                          )}
                        </div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                          product.stockQuantity > 10 ? 'bg-green-100 text-green-800' :
                          product.stockQuantity > 0 ? 'bg-yellow-100 text-yellow-800' :
                          'bg-red-100 text-red-800'
                        }`}>
                          {product.stockQuantity} adet
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                          product.status === 'active' ? 'bg-green-100 text-green-800' :
                          product.status === 'waiting_approval' ? 'bg-yellow-100 text-yellow-800' :
                          'bg-gray-100 text-gray-800'
                        }`}>
                          {product.status === 'active' ? 'Aktif' :
                           product.status === 'waiting_approval' ? 'Onay Bekliyor' : 'Pasif'}
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div className="flex space-x-2">
                          <button className="text-blue-600 hover:text-blue-900 text-xs">
                            Düzenle
                          </button>
                          <button className="text-red-600 hover:text-red-900 text-xs">
                            Sil
                          </button>
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

      {/* Orders Tab */}
      {activeTab === 'orders' && isConnected && (
        <div className="space-y-6">
          {/* Sipariş İstatistikleri */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <div className="flex items-center">
                <div className="p-2 bg-blue-100 rounded-lg">
                  <span className="text-2xl">🛒</span>
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
                  <span className="text-2xl">💰</span>
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-600">Aylık Ciro</p>
                  <p className="text-2xl font-bold text-green-600">
                    {stats.monthlyRevenue.toLocaleString('tr-TR')}₺
                  </p>
                </div>
              </div>
            </div>

            <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <div className="flex items-center">
                <div className="p-2 bg-orange-100 rounded-lg">
                  <span className="text-2xl">📈</span>
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-600">Komisyon Oranı</p>
                  <p className="text-2xl font-bold text-orange-600">{stats.commissionRate}%</p>
                </div>
              </div>
            </div>
          </div>

          {/* Sipariş Listesi */}
          <div className="bg-white rounded-lg shadow-sm border border-gray-200">
            <div className="px-6 py-4 border-b border-gray-200">
              <h3 className="text-lg font-medium text-gray-900">Son Siparişler</h3>
            </div>
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Sipariş No
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Müşteri
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Tarih
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
                      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {order.orderNumber}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {order.customerName}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {new Date(order.orderDate).toLocaleDateString('tr-TR')}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {order.totalAmount.toLocaleString('tr-TR')}₺
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                          order.status === 'Delivered' ? 'bg-green-100 text-green-800' :
                          order.status === 'Shipped' ? 'bg-blue-100 text-blue-800' :
                          order.status === 'Processing' ? 'bg-yellow-100 text-yellow-800' :
                          'bg-gray-100 text-gray-800'
                        }`}>
                          {order.status}
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div className="flex space-x-2">
                          <button className="text-blue-600 hover:text-blue-900 text-xs">
                            Detay
                          </button>
                          <button className="text-green-600 hover:text-green-900 text-xs">
                            Kargola
                          </button>
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

      {/* Analytics Tab */}
      {activeTab === 'analytics' && isConnected && (
        <div className="space-y-6">
          {/* Gelir Grafiği */}
          <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 className="text-lg font-medium text-gray-900 mb-4">Aylık Gelir Trendi</h3>
            <ResponsiveContainer width="100%" height={300}>
              <LineChart data={revenueData}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="month" />
                <YAxis />
                <Tooltip formatter={(value) => [`${value.toLocaleString('tr-TR')}₺`, 'Gelir']} />
                <Line type="monotone" dataKey="revenue" stroke="#EA580C" strokeWidth={2} />
              </LineChart>
            </ResponsiveContainer>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {/* Ürün Durum Dağılımı */}
            <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <h3 className="text-lg font-medium text-gray-900 mb-4">Ürün Durum Dağılımı</h3>
              <ResponsiveContainer width="100%" height={250}>
                <PieChart>
                  <Pie
                    data={productStatusData}
                    cx="50%"
                    cy="50%"
                    outerRadius={80}
                    fill="#8884d8"
                    dataKey="value"
                    label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                  >
                    {productStatusData.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={entry.color} />
                    ))}
                  </Pie>
                  <Tooltip />
                </PieChart>
              </ResponsiveContainer>
            </div>

            {/* Performans Metrikleri */}
            <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <h3 className="text-lg font-medium text-gray-900 mb-4">Performans Metrikleri</h3>
              <div className="space-y-4">
                <div className="flex justify-between items-center">
                  <span className="text-sm text-gray-600">Müşteri Memnuniyeti</span>
                  <span className="text-sm font-medium text-gray-900">{stats.averageRating}/5</span>
                </div>
                <div className="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    className="bg-green-500 h-2 rounded-full" 
                    style={{ width: `${(stats.averageRating / 5) * 100}%` }}
                  ></div>
                </div>

                <div className="flex justify-between items-center">
                  <span className="text-sm text-gray-600">Komisyon Oranı</span>
                  <span className="text-sm font-medium text-gray-900">{stats.commissionRate}%</span>
                </div>
                <div className="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    className="bg-orange-500 h-2 rounded-full" 
                    style={{ width: `${stats.commissionRate}%` }}
                  ></div>
                </div>

                <div className="flex justify-between items-center">
                  <span className="text-sm text-gray-600">Aktif Ürün Oranı</span>
                  <span className="text-sm font-medium text-gray-900">
                    {stats.totalProducts > 0 ? Math.round((stats.activeProducts / stats.totalProducts) * 100) : 0}%
                  </span>
                </div>
                <div className="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    className="bg-blue-500 h-2 rounded-full" 
                    style={{ 
                      width: `${stats.totalProducts > 0 ? (stats.activeProducts / stats.totalProducts) * 100 : 0}%` 
                    }}
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Bağlantı Yoksa Uyarı */}
      {!isConnected && activeTab !== 'setup' && (
        <div className="bg-yellow-50 border border-yellow-200 rounded-md p-4">
          <div className="flex">
            <div className="flex-shrink-0">
              <span className="text-yellow-400 text-xl">⚠️</span>
            </div>
            <div className="ml-3">
              <h3 className="text-sm font-medium text-yellow-800">
                Hepsiburada Bağlantısı Gerekli
              </h3>
              <div className="mt-2 text-sm text-yellow-700">
                <p>
                  Bu sekmeyi görüntülemek için önce Hepsiburada API bağlantısını kurmanız gerekiyor.
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

export default HepsiburadaIntegration; 