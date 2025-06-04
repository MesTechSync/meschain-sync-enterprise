import React, { useState, useEffect } from 'react';

interface AutomationRule {
  id: string;
  name: string;
  type: 'pricing' | 'inventory' | 'listing' | 'order';
  status: 'active' | 'inactive' | 'paused';
  marketplace: string[];
  conditions: AutomationCondition[];
  actions: AutomationAction[];
  lastRun: string;
  nextRun: string;
  executionCount: number;
  successRate: number;
  createdAt: string;
}

interface AutomationCondition {
  field: string;
  operator: 'equals' | 'greater_than' | 'less_than' | 'contains' | 'between';
  value: string | number;
  secondValue?: string | number;
}

interface AutomationAction {
  type: 'update_price' | 'update_stock' | 'pause_listing' | 'send_notification' | 'create_order';
  parameters: Record<string, any>;
}

interface PricingRule {
  id: string;
  name: string;
  marketplace: string;
  category: string;
  minMargin: number;
  maxMargin: number;
  competitorTracking: boolean;
  dynamicPricing: boolean;
  priceFloor: number;
  priceCeiling: number;
  isActive: boolean;
}

interface InventoryRule {
  id: string;
  name: string;
  marketplace: string;
  lowStockThreshold: number;
  autoReorder: boolean;
  reorderQuantity: number;
  supplierNotification: boolean;
  pauseListingOnZero: boolean;
  isActive: boolean;
}

interface AutomationStats {
  totalRules: number;
  activeRules: number;
  executionsToday: number;
  successRate: number;
  timeSaved: number;
  revenueImpact: number;
}

const AutomationCenter: React.FC = () => {
  const [automationRules, setAutomationRules] = useState<AutomationRule[]>([]);
  const [pricingRules, setPricingRules] = useState<PricingRule[]>([]);
  const [inventoryRules, setInventoryRules] = useState<InventoryRule[]>([]);
  const [automationStats, setAutomationStats] = useState<AutomationStats>({
    totalRules: 0,
    activeRules: 0,
    executionsToday: 0,
    successRate: 0,
    timeSaved: 0,
    revenueImpact: 0
  });

  const [selectedTab, setSelectedTab] = useState<'overview' | 'pricing' | 'inventory' | 'rules' | 'logs'>('overview');
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    loadAutomationData();
  }, []);

  const loadAutomationData = async () => {
    setIsLoading(true);
    try {
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 1000));

      const mockAutomationRules: AutomationRule[] = [
        {
          id: '1',
          name: 'Dinamik Fiyat Optimizasyonu',
          type: 'pricing',
          status: 'active',
          marketplace: ['Trendyol', 'Hepsiburada'],
          conditions: [
            { field: 'competitor_price', operator: 'less_than', value: 100 },
            { field: 'stock_level', operator: 'greater_than', value: 10 }
          ],
          actions: [
            { type: 'update_price', parameters: { adjustment: -5, unit: 'percentage' } }
          ],
          lastRun: '2025-06-02T10:30:00Z',
          nextRun: '2025-06-02T11:00:00Z',
          executionCount: 1247,
          successRate: 98.5,
          createdAt: '2025-05-01T00:00:00Z'
        },
        {
          id: '2',
          name: 'Otomatik Stok Yönetimi',
          type: 'inventory',
          status: 'active',
          marketplace: ['Trendyol', 'Hepsiburada', 'ÇiçekSepeti'],
          conditions: [
            { field: 'stock_level', operator: 'less_than', value: 5 }
          ],
          actions: [
            { type: 'send_notification', parameters: { type: 'low_stock', recipients: ['admin'] } },
            { type: 'pause_listing', parameters: { when_zero: true } }
          ],
          lastRun: '2025-06-02T09:45:00Z',
          nextRun: '2025-06-02T10:45:00Z',
          executionCount: 892,
          successRate: 99.2,
          createdAt: '2025-05-15T00:00:00Z'
        },
        {
          id: '3',
          name: 'Kar Marjı Koruma',
          type: 'pricing',
          status: 'active',
          marketplace: ['Tüm Pazaryerleri'],
          conditions: [
            { field: 'profit_margin', operator: 'less_than', value: 15 }
          ],
          actions: [
            { type: 'update_price', parameters: { min_margin: 15, unit: 'percentage' } }
          ],
          lastRun: '2025-06-02T08:15:00Z',
          nextRun: '2025-06-02T12:15:00Z',
          executionCount: 567,
          successRate: 96.8,
          createdAt: '2025-04-20T00:00:00Z'
        }
      ];

      const mockPricingRules: PricingRule[] = [
        {
          id: '1',
          name: 'Elektronik Kategori Fiyatlama',
          marketplace: 'Trendyol',
          category: 'Elektronik',
          minMargin: 15,
          maxMargin: 35,
          competitorTracking: true,
          dynamicPricing: true,
          priceFloor: 50,
          priceCeiling: 5000,
          isActive: true
        },
        {
          id: '2',
          name: 'Gıda Ürünleri Fiyatlama',
          marketplace: 'Hepsiburada',
          category: 'Gıda',
          minMargin: 25,
          maxMargin: 45,
          competitorTracking: false,
          dynamicPricing: false,
          priceFloor: 10,
          priceCeiling: 500,
          isActive: true
        }
      ];

      const mockInventoryRules: InventoryRule[] = [
        {
          id: '1',
          name: 'Genel Stok Yönetimi',
          marketplace: 'Tüm Pazaryerleri',
          lowStockThreshold: 5,
          autoReorder: true,
          reorderQuantity: 50,
          supplierNotification: true,
          pauseListingOnZero: true,
          isActive: true
        },
        {
          id: '2',
          name: 'Çiçek Ürünleri Stok',
          marketplace: 'ÇiçekSepeti',
          lowStockThreshold: 3,
          autoReorder: false,
          reorderQuantity: 20,
          supplierNotification: true,
          pauseListingOnZero: true,
          isActive: true
        }
      ];

      const mockStats: AutomationStats = {
        totalRules: mockAutomationRules.length,
        activeRules: mockAutomationRules.filter(r => r.status === 'active').length,
        executionsToday: 2847,
        successRate: 98.1,
        timeSaved: 24.5,
        revenueImpact: 125000
      };

      setAutomationRules(mockAutomationRules);
      setPricingRules(mockPricingRules);
      setInventoryRules(mockInventoryRules);
      setAutomationStats(mockStats);
    } catch (error) {
      console.error('Automation data loading error:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'active': return 'bg-green-100 text-green-800';
      case 'inactive': return 'bg-gray-100 text-gray-800';
      case 'paused': return 'bg-yellow-100 text-yellow-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getTypeIcon = (type: string) => {
    switch (type) {
      case 'pricing': return '💰';
      case 'inventory': return '📦';
      case 'listing': return '📝';
      case 'order': return '🛒';
      default: return '⚙️';
    }
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('tr-TR', {
      style: 'currency',
      currency: 'TRY',
      minimumFractionDigits: 0
    }).format(amount);
  };

  const StatCard: React.FC<{
    title: string;
    value: string;
    icon: string;
    color: string;
    description?: string;
  }> = ({ title, value, icon, color, description }) => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div className="flex items-center justify-between">
        <div>
          <p className="text-sm font-medium text-gray-600">{title}</p>
          <p className="text-2xl font-bold text-gray-900">{value}</p>
          {description && (
            <p className="text-xs text-gray-500 mt-1">{description}</p>
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
          <p className="mt-4 text-lg text-gray-600">Otomasyon merkezi yükleniyor...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Otomasyon Merkezi</h1>
          <p className="text-sm text-gray-500 mt-1">Akıllı fiyatlama ve stok yönetimi</p>
        </div>
        <div className="flex space-x-4">
          <button
            className="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2"
          >
            <span>➕</span>
            <span>Yeni Kural</span>
          </button>
          <button className="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
            <span>▶️</span>
            <span>Tümünü Çalıştır</span>
          </button>
        </div>
      </div>

      {/* Stats Overview */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard
          title="Toplam Kural"
          value={automationStats.totalRules.toString()}
          icon="⚙️"
          color="#3B82F6"
          description="Oluşturulan otomasyon kuralları"
        />
        <StatCard
          title="Aktif Kural"
          value={automationStats.activeRules.toString()}
          icon="✅"
          color="#10B981"
          description="Şu anda çalışan kurallar"
        />
        <StatCard
          title="Bugünkü Çalıştırma"
          value={automationStats.executionsToday.toLocaleString()}
          icon="🔄"
          color="#8B5CF6"
          description="Otomatik işlem sayısı"
        />
        <StatCard
          title="Başarı Oranı"
          value={`${automationStats.successRate}%`}
          icon="🎯"
          color="#F59E0B"
          description="Hatasız çalışma oranı"
        />
      </div>

      {/* Tab Navigation */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200">
        <div className="border-b border-gray-200">
          <nav className="flex space-x-8 px-6">
            {[
              { id: 'overview', label: 'Genel Bakış', icon: '📊' },
              { id: 'pricing', label: 'Fiyatlama Kuralları', icon: '💰' },
              { id: 'inventory', label: 'Stok Kuralları', icon: '📦' },
              { id: 'rules', label: 'Tüm Kurallar', icon: '⚙️' },
              { id: 'logs', label: 'Çalışma Logları', icon: '📋' }
            ].map((tab) => (
              <button
                key={tab.id}
                onClick={() => setSelectedTab(tab.id as any)}
                className={`py-4 px-1 border-b-2 font-medium text-sm flex items-center space-x-2 ${
                  selectedTab === tab.id
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                }`}
              >
                <span>{tab.icon}</span>
                <span>{tab.label}</span>
              </button>
            ))}
          </nav>
        </div>

        <div className="p-6">
          {/* Overview Tab */}
          {selectedTab === 'overview' && (
            <div className="space-y-6">
              {/* Performance Metrics */}
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div className="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-6">
                  <h3 className="text-lg font-semibold text-blue-900 mb-4">⏱️ Zaman Tasarrufu</h3>
                  <div className="text-3xl font-bold text-blue-700 mb-2">
                    {automationStats.timeSaved} saat/gün
                  </div>
                  <p className="text-blue-600 text-sm">
                    Manuel işlemlerden tasarruf edilen süre
                  </p>
                </div>
                <div className="bg-gradient-to-r from-green-50 to-green-100 rounded-lg p-6">
                  <h3 className="text-lg font-semibold text-green-900 mb-4">💵 Gelir Etkisi</h3>
                  <div className="text-3xl font-bold text-green-700 mb-2">
                    {formatCurrency(automationStats.revenueImpact)}
                  </div>
                  <p className="text-green-600 text-sm">
                    Otomasyon sayesinde ek gelir
                  </p>
                </div>
              </div>

              {/* Recent Executions */}
              <div>
                <h3 className="text-lg font-semibold text-gray-900 mb-4">Son Çalıştırılan Kurallar</h3>
                <div className="space-y-3">
                  {automationRules.slice(0, 5).map((rule) => (
                    <div key={rule.id} className="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                      <div className="flex items-center space-x-3">
                        <span className="text-2xl">{getTypeIcon(rule.type)}</span>
                        <div>
                          <p className="font-medium text-gray-900">{rule.name}</p>
                          <p className="text-sm text-gray-500">
                            {rule.marketplace.join(', ')} • {rule.executionCount} çalıştırma
                          </p>
                        </div>
                      </div>
                      <div className="text-right">
                        <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(rule.status)}`}>
                          {rule.status === 'active' ? 'Aktif' : rule.status === 'paused' ? 'Duraklatıldı' : 'Pasif'}
                        </span>
                        <p className="text-sm text-gray-500 mt-1">%{rule.successRate} başarı</p>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          )}

          {/* Pricing Rules Tab */}
          {selectedTab === 'pricing' && (
            <div className="space-y-6">
              <div className="flex justify-between items-center">
                <h3 className="text-lg font-semibold text-gray-900">Fiyatlama Kuralları</h3>
                <button className="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm">
                  Yeni Fiyatlama Kuralı
                </button>
              </div>
              
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {pricingRules.map((rule) => (
                  <div key={rule.id} className="bg-white border border-gray-200 rounded-lg p-6">
                    <div className="flex justify-between items-start mb-4">
                      <div>
                        <h4 className="font-semibold text-gray-900">{rule.name}</h4>
                        <p className="text-sm text-gray-500">{rule.marketplace} • {rule.category}</p>
                      </div>
                      <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                        rule.isActive ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                      }`}>
                        {rule.isActive ? 'Aktif' : 'Pasif'}
                      </span>
                    </div>
                    
                    <div className="space-y-3">
                      <div className="flex justify-between">
                        <span className="text-sm text-gray-600">Kar Marjı:</span>
                        <span className="text-sm font-medium">%{rule.minMargin} - %{rule.maxMargin}</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-sm text-gray-600">Fiyat Aralığı:</span>
                        <span className="text-sm font-medium">
                          {formatCurrency(rule.priceFloor)} - {formatCurrency(rule.priceCeiling)}
                        </span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-sm text-gray-600">Rakip Takibi:</span>
                        <span className="text-sm font-medium">
                          {rule.competitorTracking ? '✅ Aktif' : '❌ Pasif'}
                        </span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-sm text-gray-600">Dinamik Fiyatlama:</span>
                        <span className="text-sm font-medium">
                          {rule.dynamicPricing ? '✅ Aktif' : '❌ Pasif'}
                        </span>
                      </div>
                    </div>
                    
                    <div className="mt-4 flex space-x-2">
                      <button className="flex-1 bg-blue-50 text-blue-600 py-2 px-3 rounded text-sm hover:bg-blue-100">
                        Düzenle
                      </button>
                      <button className="flex-1 bg-gray-50 text-gray-600 py-2 px-3 rounded text-sm hover:bg-gray-100">
                        Test Et
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Inventory Rules Tab */}
          {selectedTab === 'inventory' && (
            <div className="space-y-6">
              <div className="flex justify-between items-center">
                <h3 className="text-lg font-semibold text-gray-900">Stok Yönetimi Kuralları</h3>
                <button className="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm">
                  Yeni Stok Kuralı
                </button>
              </div>
              
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {inventoryRules.map((rule) => (
                  <div key={rule.id} className="bg-white border border-gray-200 rounded-lg p-6">
                    <div className="flex justify-between items-start mb-4">
                      <div>
                        <h4 className="font-semibold text-gray-900">{rule.name}</h4>
                        <p className="text-sm text-gray-500">{rule.marketplace}</p>
                      </div>
                      <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                        rule.isActive ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                      }`}>
                        {rule.isActive ? 'Aktif' : 'Pasif'}
                      </span>
                    </div>
                    
                    <div className="space-y-3">
                      <div className="flex justify-between">
                        <span className="text-sm text-gray-600">Düşük Stok Eşiği:</span>
                        <span className="text-sm font-medium">{rule.lowStockThreshold} adet</span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-sm text-gray-600">Otomatik Sipariş:</span>
                        <span className="text-sm font-medium">
                          {rule.autoReorder ? `✅ ${rule.reorderQuantity} adet` : '❌ Pasif'}
                        </span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-sm text-gray-600">Tedarikçi Bildirimi:</span>
                        <span className="text-sm font-medium">
                          {rule.supplierNotification ? '✅ Aktif' : '❌ Pasif'}
                        </span>
                      </div>
                      <div className="flex justify-between">
                        <span className="text-sm text-gray-600">Sıfır Stokta Durdur:</span>
                        <span className="text-sm font-medium">
                          {rule.pauseListingOnZero ? '✅ Aktif' : '❌ Pasif'}
                        </span>
                      </div>
                    </div>
                    
                    <div className="mt-4 flex space-x-2">
                      <button className="flex-1 bg-green-50 text-green-600 py-2 px-3 rounded text-sm hover:bg-green-100">
                        Düzenle
                      </button>
                      <button className="flex-1 bg-gray-50 text-gray-600 py-2 px-3 rounded text-sm hover:bg-gray-100">
                        Test Et
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* All Rules Tab */}
          {selectedTab === 'rules' && (
            <div className="space-y-4">
              <div className="flex justify-between items-center">
                <h3 className="text-lg font-semibold text-gray-900">Tüm Otomasyon Kuralları</h3>
                <div className="flex space-x-2">
                  <select className="bg-white border border-gray-300 rounded px-3 py-2 text-sm">
                    <option>Tüm Türler</option>
                    <option>Fiyatlama</option>
                    <option>Stok</option>
                    <option>Listeleme</option>
                  </select>
                  <select className="bg-white border border-gray-300 rounded px-3 py-2 text-sm">
                    <option>Tüm Durumlar</option>
                    <option>Aktif</option>
                    <option>Pasif</option>
                    <option>Duraklatıldı</option>
                  </select>
                </div>
              </div>
              
              <div className="bg-white border border-gray-200 rounded-lg overflow-hidden">
                <table className="min-w-full divide-y divide-gray-200">
                  <thead className="bg-gray-50">
                    <tr>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Kural
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tür
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Pazaryeri
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Durum
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Son Çalışma
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Başarı
                      </th>
                      <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        İşlemler
                      </th>
                    </tr>
                  </thead>
                  <tbody className="bg-white divide-y divide-gray-200">
                    {automationRules.map((rule) => (
                      <tr key={rule.id} className="hover:bg-gray-50">
                        <td className="px-6 py-4 whitespace-nowrap">
                          <div className="flex items-center">
                            <span className="text-2xl mr-3">{getTypeIcon(rule.type)}</span>
                            <div>
                              <div className="text-sm font-medium text-gray-900">{rule.name}</div>
                              <div className="text-sm text-gray-500">{rule.executionCount} çalıştırma</div>
                            </div>
                          </div>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <span className="text-sm text-gray-900 capitalize">{rule.type}</span>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <span className="text-sm text-gray-900">{rule.marketplace.join(', ')}</span>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(rule.status)}`}>
                            {rule.status === 'active' ? 'Aktif' : rule.status === 'paused' ? 'Duraklatıldı' : 'Pasif'}
                          </span>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                          {new Date(rule.lastRun).toLocaleString('tr-TR')}
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <div className="flex items-center">
                            <div className="text-sm font-medium text-gray-900">%{rule.successRate}</div>
                            <div className="ml-2 w-16 bg-gray-200 rounded-full h-2">
                              <div 
                                className="bg-green-500 h-2 rounded-full" 
                                style={{ width: `${rule.successRate}%` }}
                              ></div>
                            </div>
                          </div>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                          <div className="flex justify-end space-x-2">
                            <button className="text-blue-600 hover:text-blue-900">Düzenle</button>
                            <button className="text-green-600 hover:text-green-900">Çalıştır</button>
                            <button className="text-red-600 hover:text-red-900">Sil</button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}

          {/* Logs Tab */}
          {selectedTab === 'logs' && (
            <div className="space-y-4">
              <h3 className="text-lg font-semibold text-gray-900">Çalışma Logları</h3>
              <div className="bg-gray-900 rounded-lg p-4 text-green-400 font-mono text-sm max-h-96 overflow-y-auto">
                <div>[2025-06-02 10:30:15] ✅ Dinamik Fiyat Optimizasyonu - 45 ürün güncellendi</div>
                <div>[2025-06-02 10:25:32] ✅ Otomatik Stok Yönetimi - 12 düşük stok bildirimi gönderildi</div>
                <div>[2025-06-02 10:20:18] ✅ Kar Marjı Koruma - 8 ürün fiyatı artırıldı</div>
                <div>[2025-06-02 10:15:45] ⚠️ Trendyol API - Rate limit yaklaşıldı, 30 saniye bekleniyor</div>
                <div>[2025-06-02 10:10:22] ✅ Dinamik Fiyat Optimizasyonu - 67 ürün güncellendi</div>
                <div>[2025-06-02 10:05:11] ✅ Otomatik Stok Yönetimi - 3 ürün listesi duraklatıldı</div>
                <div>[2025-06-02 10:00:00] 🔄 Tüm kurallar başlatıldı - 3 aktif kural çalışıyor</div>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default AutomationCenter; 