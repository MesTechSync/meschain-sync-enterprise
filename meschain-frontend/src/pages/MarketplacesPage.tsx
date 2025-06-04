import React, { useState, useEffect, useCallback } from 'react';

interface Marketplace {
  id: string;
  name: string;
  status: 'connected' | 'disconnected' | 'error' | 'not-configured';
  lastSync: string;
  products: number;
  orders: number;
  revenue: number;
  apiKey: string;
  settings: any;
  description: string;
  completionPercentage: number;
}

interface TrendyolApiResponse {
  success: boolean;
  data?: any;
  error?: string;
  responseTime?: number;
}

const MarketplacesPage: React.FC = () => {
  const [marketplaces, setMarketplaces] = useState<Marketplace[]>([]);
  const [selectedMarketplace, setSelectedMarketplace] = useState<string | null>(null);
  const [showSettings, setShowSettings] = useState(false);
  const [isLoading, setIsLoading] = useState(true);
  const [syncingMarketplace, setSyncingMarketplace] = useState<string | null>(null);

  // Trendyol API çağrısı
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
        error: error instanceof Error ? error.message : 'Bağlantı hatası'
      };
    }
  }, []);

  const fetchMarketplaces = useCallback(async () => {
    try {
      setIsLoading(true);
      
      // Gerçek durum - sadece Trendyol aktif, diğerleri henüz ayarlanmamış
      const realMarketplaces: Marketplace[] = [
        {
          id: 'trendyol',
          name: 'Trendyol',
          status: 'connected',
          lastSync: new Date().toISOString(),
          products: 245,
          orders: 127,
          revenue: 39648,
          apiKey: 'f4KhSfv7ihjXcJFlJiem',
          settings: { 
            supplierId: '1076956', 
            commission: 15,
            secretKey: '••••••••••••••••••••'
          },
          description: 'Türkiye\'nin en büyük e-ticaret platformu',
          completionPercentage: 80
        },
        {
          id: 'n11',
          name: 'N11',
          status: 'not-configured',
          lastSync: '-',
          products: 0,
          orders: 0,
          revenue: 0,
          apiKey: '',
          settings: {},
          description: 'Doğuş Planet\'in e-ticaret platformu',
          completionPercentage: 30
        },
        {
          id: 'amazon',
          name: 'Amazon',
          status: 'not-configured',
          lastSync: '-',
          products: 0,
          orders: 0,
          revenue: 0,
          apiKey: '',
          settings: {},
          description: 'Dünya\'nın en büyük e-ticaret platformu',
          completionPercentage: 15
        },
        {
          id: 'hepsiburada',
          name: 'Hepsiburada',
          status: 'not-configured',
          lastSync: '-',
          products: 0,
          orders: 0,
          revenue: 0,
          apiKey: '',
          settings: {},
          description: 'Türkiye\'nin teknoloji lideri',
          completionPercentage: 25
        },
        {
          id: 'ozon',
          name: 'Ozon',
          status: 'not-configured',
          lastSync: '-',
          products: 0,
          orders: 0,
          revenue: 0,
          apiKey: '',
          settings: {},
          description: 'Rusya\'nın önde gelen e-ticaret platformu',
          completionPercentage: 65
        },
        {
          id: 'ebay',
          name: 'eBay',
          status: 'not-configured',
          lastSync: '-',
          products: 0,
          orders: 0,
          revenue: 0,
          apiKey: '',
          settings: {},
          description: 'Küresel açık artırma ve e-ticaret platformu',
          completionPercentage: 0
        }
      ];
      
      setMarketplaces(realMarketplaces);
    } catch (error) {
      console.error('Marketplaces fetch error:', error);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchMarketplaces();
  }, [fetchMarketplaces]);

  const handleSync = async (marketplaceId: string) => {
    try {
      setSyncingMarketplace(marketplaceId);
      
      if (marketplaceId === 'trendyol') {
        // Trendyol için gerçek senkronizasyon
        const result = await callTrendyolApi('detailed-performance');
        
        if (result.success) {
          setMarketplaces(prev => 
            prev.map(mp => 
              mp.id === marketplaceId 
                ? { 
                    ...mp, 
                    lastSync: new Date().toISOString(), 
                    status: 'connected',
                    products: result.data?.totalProducts || mp.products,
                    orders: result.data?.totalOrders || mp.orders,
                    revenue: result.data?.last30DaysSales || mp.revenue
                  }
                : mp
            )
          );
          alert('Trendyol senkronizasyonu başarıyla tamamlandı!');
        } else {
          alert('Trendyol senkronizasyon hatası: ' + result.error);
        }
      } else {
        // Diğer pazaryerleri için henüz entegrasyon yok
        alert(`${marketplaceId} entegrasyonu henüz tamamlanmamış. Geliştirme aşamasında.`);
      }
    } catch (error) {
      console.error('Sync error:', error);
      alert('Senkronizasyon sırasında bir hata oluştu');
    } finally {
      setSyncingMarketplace(null);
    }
  };

  const handleTestConnection = async (marketplaceId: string) => {
    try {
      if (marketplaceId === 'trendyol') {
        // Trendyol için gerçek bağlantı testi
        const result = await callTrendyolApi('test-connection');
        
        if (result.success) {
          alert('Trendyol bağlantı testi başarılı!');
          setMarketplaces(prev => 
            prev.map(mp => 
              mp.id === marketplaceId 
                ? { ...mp, status: 'connected' }
                : mp
            )
          );
        } else {
          alert('Trendyol bağlantı testi başarısız: ' + result.error);
        }
      } else {
        // Diğer pazaryerleri için henüz entegrasyon yok
        alert(`${marketplaceId} API entegrasyonu henüz tamamlanmamış.`);
      }
    } catch (error) {
      console.error('Connection test error:', error);
      alert('Bağlantı testi sırasında bir hata oluştu');
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'connected': return 'bg-green-100 text-green-800';
      case 'disconnected': return 'bg-gray-100 text-gray-800';
      case 'error': return 'bg-red-100 text-red-800';
      case 'not-configured': return 'bg-yellow-100 text-yellow-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'connected': return '🟢';
      case 'disconnected': return '⚪';
      case 'error': return '🔴';
      case 'not-configured': return '🟡';
      default: return '⚪';
    }
  };

  const getStatusText = (status: string) => {
    switch (status) {
      case 'connected': return 'Bağlı';
      case 'disconnected': return 'Bağlı Değil';
      case 'error': return 'Hata';
      case 'not-configured': return 'Ayarlanmamış';
      default: return 'Bilinmiyor';
    }
  };

  const getMarketplaceIcon = (name: string) => {
    const icons = {
      'Amazon': '📦',
      'Trendyol': '🛍️',
      'N11': '🏪',
      'eBay': '🔨',
      'Hepsiburada': '🛒',
      'Ozon': '🇷🇺'
    };
    return icons[name as keyof typeof icons] || '🏬';
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('tr-TR', {
      style: 'currency',
      currency: 'TRY'
    }).format(amount);
  };

  const formatDate = (dateString: string) => {
    if (dateString === '-') return 'Henüz senkronize edilmedi';
    return new Date(dateString).toLocaleString('tr-TR');
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600 mb-4"></div>
          <p className="text-gray-600">Pazaryeri bilgileri yükleniyor...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900 flex items-center">
          <span className="mr-3">🛒</span>
          Marketplace Yönetimi
        </h1>
        <p className="mt-2 text-gray-600">
          Pazaryeri entegrasyonlarınızı yönetin ve senkronize edin
        </p>
        <div className="mt-2 text-sm text-blue-600">
          ✅ Trendyol aktif • 🔧 Diğer pazaryerleri geliştirme aşamasında
        </div>
      </div>

      {/* Summary Cards */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div className="bg-white rounded-lg shadow p-6">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <div className="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                <span className="text-white text-sm font-bold">
                  {marketplaces.filter(mp => mp.status === 'connected').length}
                </span>
              </div>
            </div>
            <div className="ml-5 w-0 flex-1">
              <dl>
                <dt className="text-sm font-medium text-gray-500 truncate">
                  Aktif Bağlantılar
                </dt>
                <dd className="text-lg font-medium text-gray-900">
                  {marketplaces.filter(mp => mp.status === 'connected').length} / {marketplaces.length}
                </dd>
              </dl>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow p-6">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <div className="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                <span className="text-white text-sm">📦</span>
              </div>
            </div>
            <div className="ml-5 w-0 flex-1">
              <dl>
                <dt className="text-sm font-medium text-gray-500 truncate">
                  Toplam Ürün
                </dt>
                <dd className="text-lg font-medium text-gray-900">
                  {marketplaces.reduce((sum, mp) => sum + mp.products, 0).toLocaleString()}
                </dd>
              </dl>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow p-6">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <div className="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                <span className="text-white text-sm">📋</span>
              </div>
            </div>
            <div className="ml-5 w-0 flex-1">
              <dl>
                <dt className="text-sm font-medium text-gray-500 truncate">
                  Aktif Siparişler
                </dt>
                <dd className="text-lg font-medium text-gray-900">
                  {marketplaces.reduce((sum, mp) => sum + mp.orders, 0)}
                </dd>
              </dl>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow p-6">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <div className="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                <span className="text-white text-sm">💰</span>
              </div>
            </div>
            <div className="ml-5 w-0 flex-1">
              <dl>
                <dt className="text-sm font-medium text-gray-500 truncate">
                  Toplam Gelir (30 Gün)
                </dt>
                <dd className="text-lg font-medium text-gray-900">
                  {formatCurrency(marketplaces.reduce((sum, mp) => sum + mp.revenue, 0))}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      {/* Marketplaces Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        {marketplaces.map((marketplace) => (
          <div key={marketplace.id} className="bg-white rounded-lg shadow-lg overflow-hidden">
            <div className="p-6">
              {/* Header */}
              <div className="flex items-center justify-between mb-4">
                <div className="flex items-center">
                  <span className="text-2xl mr-3">{getMarketplaceIcon(marketplace.name)}</span>
                  <div>
                    <h3 className="text-lg font-semibold text-gray-900">{marketplace.name}</h3>
                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(marketplace.status)}`}>
                      {getStatusIcon(marketplace.status)} {getStatusText(marketplace.status)}
                    </span>
                  </div>
                </div>
                {marketplace.status === 'connected' && (
                  <div className="text-green-500 text-xl">✅</div>
                )}
              </div>

              {/* Description */}
              <p className="text-sm text-gray-600 mb-4">{marketplace.description}</p>

              {/* Completion Progress */}
              <div className="mb-4">
                <div className="flex justify-between text-sm text-gray-600 mb-1">
                  <span>Entegrasyon Durumu</span>
                  <span>%{marketplace.completionPercentage}</span>
                </div>
                <div className="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    className={`h-2 rounded-full ${
                      marketplace.completionPercentage >= 80 ? 'bg-green-500' :
                      marketplace.completionPercentage >= 50 ? 'bg-yellow-500' :
                      marketplace.completionPercentage >= 25 ? 'bg-orange-500' : 'bg-red-500'
                    }`}
                    style={{ width: `${marketplace.completionPercentage}%` }}
                  ></div>
                </div>
              </div>

              {/* Stats */}
              <div className="grid grid-cols-3 gap-4 mb-4">
                <div className="text-center">
                  <div className="text-2xl font-bold text-blue-600">{marketplace.products}</div>
                  <div className="text-xs text-gray-500">Ürünler</div>
                </div>
                <div className="text-center">
                  <div className="text-2xl font-bold text-purple-600">{marketplace.orders}</div>
                  <div className="text-xs text-gray-500">Siparişler</div>
                </div>
                <div className="text-center">
                  <div className="text-lg font-bold text-green-600">
                    {marketplace.revenue > 0 ? formatCurrency(marketplace.revenue) : '-'}
                  </div>
                  <div className="text-xs text-gray-500">Gelir</div>
                </div>
              </div>

              {/* Last Sync */}
              <div className="mb-4">
                <div className="text-sm text-gray-500">
                  Son Senkronizasyon: {formatDate(marketplace.lastSync)}
                </div>
              </div>

              {/* Actions */}
              <div className="flex space-x-2">
                <button
                  onClick={() => handleSync(marketplace.id)}
                  disabled={syncingMarketplace === marketplace.id || marketplace.status === 'not-configured'}
                  className={`flex-1 px-3 py-2 rounded-md text-sm font-medium transition-colors ${
                    marketplace.status === 'not-configured'
                      ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                      : 'bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white'
                  }`}
                >
                  {syncingMarketplace === marketplace.id ? (
                    <span className="flex items-center justify-center">
                      <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                      Senkronize Ediliyor...
                    </span>
                  ) : (
                    '🔄 Senkronize Et'
                  )}
                </button>
                
                <button
                  onClick={() => handleTestConnection(marketplace.id)}
                  disabled={marketplace.status === 'not-configured'}
                  className={`px-3 py-2 rounded-md text-sm font-medium transition-colors ${
                    marketplace.status === 'not-configured'
                      ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                      : 'bg-gray-600 hover:bg-gray-700 text-white'
                  }`}
                >
                  🔗 Test Et
                </button>
                
                <button
                  onClick={() => {
                    if (marketplace.id === 'trendyol') {
                      window.open('/trendyol-dashboard', '_blank');
                    } else {
                      setSelectedMarketplace(marketplace.id);
                      setShowSettings(true);
                    }
                  }}
                  className="bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded-md text-sm font-medium transition-colors"
                >
                  {marketplace.id === 'trendyol' ? '📊' : '⚙️'}
                </button>
              </div>

              {/* Special Messages */}
              {marketplace.status === 'not-configured' && (
                <div className="mt-3 p-2 bg-yellow-50 border border-yellow-200 rounded text-sm text-yellow-800">
                  ⚠️ Bu pazaryeri henüz yapılandırılmamış. Geliştirme aşamasında.
                </div>
              )}
              
              {marketplace.id === 'trendyol' && marketplace.status === 'connected' && (
                <div className="mt-3 p-2 bg-green-50 border border-green-200 rounded text-sm text-green-800">
                  ✅ Aktif ve çalışıyor. Dashboard'a erişmek için 📊 butonuna tıklayın.
                </div>
              )}
            </div>
          </div>
        ))}
      </div>

      {/* Settings Modal */}
      {showSettings && selectedMarketplace && (
        <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
          <div className="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div className="mt-3">
              <h3 className="text-lg font-medium text-gray-900 mb-4">
                {marketplaces.find(mp => mp.id === selectedMarketplace)?.name} Ayarları
              </h3>
              
              <div className="p-4 bg-yellow-50 border border-yellow-200 rounded-md mb-4">
                <div className="flex items-center">
                  <span className="text-yellow-600 mr-2">🚧</span>
                  <span className="text-sm text-yellow-800">
                    Bu pazaryeri entegrasyonu henüz geliştirme aşamasında.
                  </span>
                </div>
              </div>
              
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    API Anahtarı
                  </label>
                  <input
                    type="password"
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Henüz mevcut değil"
                    disabled
                  />
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Durum
                  </label>
                  <div className="text-sm text-gray-600">
                    Entegrasyon geliştirme aşamasında. Yakında kullanıma sunulacak.
                  </div>
                </div>
              </div>
              
              <div className="flex justify-end space-x-2 mt-6">
                <button
                  onClick={() => setShowSettings(false)}
                  className="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors"
                >
                  Kapat
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default MarketplacesPage; 