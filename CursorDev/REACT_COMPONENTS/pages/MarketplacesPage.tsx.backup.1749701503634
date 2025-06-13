import React, { useState, useEffect } from 'react';
import apiService from '../services/api';

interface Marketplace {
  id: string;
  name: string;
  status: 'connected' | 'disconnected' | 'error';
  lastSync: string;
  products: number;
  orders: number;
  revenue: number;
  apiKey: string;
  settings: any;
}

const MarketplacesPage: React.FC = () => {
  const [marketplaces, setMarketplaces] = useState<Marketplace[]>([]);
  const [selectedMarketplace, setSelectedMarketplace] = useState<string | null>(null);
  const [showSettings, setShowSettings] = useState(false);
  const [isLoading, setIsLoading] = useState(true);
  const [syncingMarketplace, setSyncingMarketplace] = useState<string | null>(null);

  useEffect(() => {
    fetchMarketplaces();
  }, []);

  const fetchMarketplaces = async () => {
    try {
      setIsLoading(true);
      // Mock data - replace with actual API call
      const mockMarketplaces: Marketplace[] = [
        {
          id: 'amazon',
          name: 'Amazon',
          status: 'connected',
          lastSync: '2025-06-02 14:30:00',
          products: 1250,
          orders: 45,
          revenue: 125000,
          apiKey: 'AKIA***************',
          settings: { region: 'EU', currency: 'EUR' }
        },
        {
          id: 'trendyol',
          name: 'Trendyol',
          status: 'connected',
          lastSync: '2025-06-02 14:25:00',
          products: 890,
          orders: 32,
          revenue: 85000,
          apiKey: 'TY***************',
          settings: { supplierId: '12345', commission: 15 }
        },
        {
          id: 'n11',
          name: 'N11',
          status: 'error',
          lastSync: '2025-06-02 12:15:00',
          products: 650,
          orders: 18,
          revenue: 45000,
          apiKey: 'N11***************',
          settings: { storeId: '67890' }
        },
        {
          id: 'ebay',
          name: 'eBay',
          status: 'disconnected',
          lastSync: '2025-06-01 18:00:00',
          products: 0,
          orders: 0,
          revenue: 0,
          apiKey: '',
          settings: {}
        },
        {
          id: 'hepsiburada',
          name: 'Hepsiburada',
          status: 'connected',
          lastSync: '2025-06-02 14:20:00',
          products: 420,
          orders: 12,
          revenue: 28000,
          apiKey: 'HB***************',
          settings: { merchantId: '54321' }
        }
      ];
      
      setMarketplaces(mockMarketplaces);
    } catch (error) {
      console.error('Marketplaces fetch error:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleSync = async (marketplaceId: string) => {
    try {
      setSyncingMarketplace(marketplaceId);
      const response = await apiService.syncMarketplace(marketplaceId);
      
      if (response.success) {
        // Update marketplace status
        setMarketplaces(prev => 
          prev.map(mp => 
            mp.id === marketplaceId 
              ? { ...mp, lastSync: new Date().toISOString(), status: 'connected' }
              : mp
          )
        );
        alert('Senkronizasyon başarıyla tamamlandı!');
      } else {
        alert('Senkronizasyon hatası: ' + response.error);
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
      const response = await apiService.testAPIConnection(marketplaceId);
      
      if (response.success) {
        alert('Bağlantı testi başarılı!');
        setMarketplaces(prev => 
          prev.map(mp => 
            mp.id === marketplaceId 
              ? { ...mp, status: 'connected' }
              : mp
          )
        );
      } else {
        alert('Bağlantı testi başarısız: ' + response.error);
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
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'connected': return '🟢';
      case 'disconnected': return '⚪';
      case 'error': return '🔴';
      default: return '⚪';
    }
  };

  const getMarketplaceIcon = (name: string) => {
    const icons = {
      'Amazon': '📦',
      'Trendyol': '🛍️',
      'N11': '🏪',
      'eBay': '🔨',
      'Hepsiburada': '🛒'
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
    return new Date(dateString).toLocaleString('tr-TR');
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
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
                  Toplam Gelir
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
                      {getStatusIcon(marketplace.status)} {marketplace.status}
                    </span>
                  </div>
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
                  disabled={syncingMarketplace === marketplace.id}
                  className="flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-3 py-2 rounded-md text-sm font-medium transition-colors"
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
                  className="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-md text-sm font-medium transition-colors"
                >
                  🔗 Test Et
                </button>
                
                <button
                  onClick={() => {
                    setSelectedMarketplace(marketplace.id);
                    setShowSettings(true);
                  }}
                  className="bg-purple-600 hover:bg-purple-700 text-white px-3 py-2 rounded-md text-sm font-medium transition-colors"
                >
                  ⚙️
                </button>
              </div>
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
              
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    API Anahtarı
                  </label>
                  <input
                    type="password"
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="API anahtarınızı girin"
                    defaultValue={marketplaces.find(mp => mp.id === selectedMarketplace)?.apiKey}
                  />
                </div>
                
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Senkronizasyon Sıklığı
                  </label>
                  <select className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="5">5 dakika</option>
                    <option value="15">15 dakika</option>
                    <option value="30">30 dakika</option>
                    <option value="60">1 saat</option>
                  </select>
                </div>
              </div>
              
              <div className="flex justify-end space-x-2 mt-6">
                <button
                  onClick={() => setShowSettings(false)}
                  className="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors"
                >
                  İptal
                </button>
                <button
                  onClick={() => {
                    // Save settings logic here
                    setShowSettings(false);
                    alert('Ayarlar kaydedildi!');
                  }}
                  className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                >
                  Kaydet
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