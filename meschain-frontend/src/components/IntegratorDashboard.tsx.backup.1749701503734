import React, { useState, useEffect } from 'react';
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
  Cell
} from 'recharts';

interface MarketplaceIntegration {
  id: string;
  name: string;
  status: 'connected' | 'disconnected' | 'error' | 'configuring';
  apiHealth: number;
  lastSync: string;
  totalProducts: number;
  syncedProducts: number;
  pendingProducts: number;
  errorCount: number;
  color: string;
}

interface ApiConfiguration {
  marketplace: string;
  endpoint: string;
  method: string;
  status: 'active' | 'inactive' | 'testing';
  responseTime: number;
  successRate: number;
  lastTest: string;
}

interface ProductMapping {
  id: number;
  sourceCategory: string;
  targetCategory: string;
  marketplace: string;
  mappedFields: number;
  totalFields: number;
  status: 'complete' | 'partial' | 'pending';
}

const IntegratorDashboard: React.FC = () => {
  const [marketplaces, setMarketplaces] = useState<MarketplaceIntegration[]>([]);
  const [apiConfigs, setApiConfigs] = useState<ApiConfiguration[]>([]);
  const [productMappings, setProductMappings] = useState<ProductMapping[]>([]);
  const [selectedMarketplace, setSelectedMarketplace] = useState<string>('all');

  useEffect(() => {
    // Mock data - gerçek API'larla değiştirilecek
    setMarketplaces([
      {
        id: 'trendyol',
        name: 'Trendyol',
        status: 'connected',
        apiHealth: 98.5,
        lastSync: '2025-06-02 15:30',
        totalProducts: 1250,
        syncedProducts: 1180,
        pendingProducts: 45,
        errorCount: 25,
        color: '#FF6B35'
      },
      {
        id: 'amazon',
        name: 'Amazon',
        status: 'connected',
        apiHealth: 96.2,
        lastSync: '2025-06-02 15:25',
        totalProducts: 890,
        syncedProducts: 845,
        pendingProducts: 30,
        errorCount: 15,
        color: '#FF9900'
      },
      {
        id: 'n11',
        name: 'N11',
        status: 'configuring',
        apiHealth: 85.0,
        lastSync: '2025-06-02 14:45',
        totalProducts: 650,
        syncedProducts: 520,
        pendingProducts: 80,
        errorCount: 50,
        color: '#FF6000'
      },
      {
        id: 'hepsiburada',
        name: 'Hepsiburada',
        status: 'error',
        apiHealth: 45.0,
        lastSync: '2025-06-02 12:15',
        totalProducts: 420,
        syncedProducts: 280,
        pendingProducts: 90,
        errorCount: 50,
        color: '#FF6000'
      },
      {
        id: 'ebay',
        name: 'eBay',
        status: 'disconnected',
        apiHealth: 0,
        lastSync: '2025-06-01 18:30',
        totalProducts: 0,
        syncedProducts: 0,
        pendingProducts: 0,
        errorCount: 0,
        color: '#E53238'
      },
      {
        id: 'ozon',
        name: 'Ozon',
        status: 'configuring',
        apiHealth: 72.0,
        lastSync: '2025-06-02 13:20',
        totalProducts: 320,
        syncedProducts: 180,
        pendingProducts: 100,
        errorCount: 40,
        color: '#005BFF'
      }
    ]);

    setApiConfigs([
      {
        marketplace: 'Trendyol',
        endpoint: '/api/v1/products',
        method: 'GET',
        status: 'active',
        responseTime: 245,
        successRate: 98.5,
        lastTest: '2025-06-02 15:30'
      },
      {
        marketplace: 'Amazon',
        endpoint: '/sp-api/catalog/v0/items',
        method: 'GET',
        status: 'active',
        responseTime: 180,
        successRate: 96.2,
        lastTest: '2025-06-02 15:25'
      },
      {
        marketplace: 'N11',
        endpoint: '/api/product.json',
        method: 'POST',
        status: 'testing',
        responseTime: 420,
        successRate: 85.0,
        lastTest: '2025-06-02 14:45'
      },
      {
        marketplace: 'Hepsiburada',
        endpoint: '/api/products',
        method: 'GET',
        status: 'inactive',
        responseTime: 0,
        successRate: 0,
        lastTest: '2025-06-02 12:15'
      }
    ]);

    setProductMappings([
      {
        id: 1,
        sourceCategory: 'Elektronik > Telefon',
        targetCategory: 'Electronics > Mobile Phones',
        marketplace: 'Amazon',
        mappedFields: 15,
        totalFields: 18,
        status: 'complete'
      },
      {
        id: 2,
        sourceCategory: 'Giyim > Kadın > Elbise',
        targetCategory: 'Kadın > Giyim > Elbise',
        marketplace: 'Trendyol',
        mappedFields: 12,
        totalFields: 15,
        status: 'partial'
      },
      {
        id: 3,
        sourceCategory: 'Ev & Yaşam > Mutfak',
        targetCategory: 'Home & Kitchen',
        marketplace: 'N11',
        mappedFields: 8,
        totalFields: 20,
        status: 'pending'
      }
    ]);
  }, []);

  const getStatusColor = (status: string) => {
    const colors = {
      connected: 'bg-green-100 text-green-800',
      disconnected: 'bg-gray-100 text-gray-800',
      error: 'bg-red-100 text-red-800',
      configuring: 'bg-yellow-100 text-yellow-800'
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
  };

  const getApiStatusColor = (status: string) => {
    const colors = {
      active: 'bg-green-100 text-green-800',
      inactive: 'bg-red-100 text-red-800',
      testing: 'bg-yellow-100 text-yellow-800'
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
  };

  const getMappingStatusColor = (status: string) => {
    const colors = {
      complete: 'bg-green-100 text-green-800',
      partial: 'bg-yellow-100 text-yellow-800',
      pending: 'bg-red-100 text-red-800'
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
  };

  // Grafik verileri
  const syncPerformanceData = marketplaces.map(mp => ({
    name: mp.name,
    synced: mp.syncedProducts,
    pending: mp.pendingProducts,
    errors: mp.errorCount,
    health: mp.apiHealth
  }));

  const apiResponseTimeData = [
    { time: '00:00', trendyol: 245, amazon: 180, n11: 420 },
    { time: '04:00', trendyol: 230, amazon: 165, n11: 380 },
    { time: '08:00', trendyol: 280, amazon: 220, n11: 450 },
    { time: '12:00', trendyol: 320, amazon: 250, n11: 520 },
    { time: '16:00', trendyol: 290, amazon: 200, n11: 480 },
    { time: '20:00', trendyol: 260, amazon: 185, n11: 410 }
  ];

  const integrationStatusData = [
    { name: 'Bağlı', value: 2, color: '#10B981' },
    { name: 'Yapılandırılıyor', value: 2, color: '#F59E0B' },
    { name: 'Hata', value: 1, color: '#EF4444' },
    { name: 'Bağlantısız', value: 1, color: '#6B7280' }
  ];

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Entegratör Paneli</h1>
          <p className="text-sm text-gray-500 mt-1">Pazaryeri entegrasyonları ve API yönetimi</p>
        </div>
        <div className="flex space-x-2">
          <select
            value={selectedMarketplace}
            onChange={(e) => setSelectedMarketplace(e.target.value)}
            className="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm"
          >
            <option value="all">Tüm Pazaryerleri</option>
            {marketplaces.map(mp => (
              <option key={mp.id} value={mp.id}>{mp.name}</option>
            ))}
          </select>
          <button className="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
            <span>🔄</span>
            <span>Senkronize Et</span>
          </button>
          <button className="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
            <span>⚙️</span>
            <span>Yeni Entegrasyon</span>
          </button>
        </div>
      </div>

      {/* Marketplace Status Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {marketplaces.map((marketplace) => (
          <div key={marketplace.id} className="bg-white rounded-lg shadow-md p-6 border-l-4" style={{ borderLeftColor: marketplace.color }}>
            <div className="flex items-center justify-between mb-4">
              <div className="flex items-center space-x-2">
                <h3 className="font-semibold text-gray-900">{marketplace.name}</h3>
                <span className={`px-2 py-1 rounded-full text-xs ${getStatusColor(marketplace.status)}`}>
                  {marketplace.status}
                </span>
              </div>
              <div className="text-right">
                <div className="text-sm text-gray-500">API Sağlığı</div>
                <div className="text-lg font-bold" style={{ color: marketplace.color }}>
                  {marketplace.apiHealth}%
                </div>
              </div>
            </div>
            
            <div className="space-y-2">
              <div className="flex justify-between text-sm">
                <span className="text-gray-600">Toplam Ürün:</span>
                <span className="font-medium">{marketplace.totalProducts}</span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-gray-600">Senkronize:</span>
                <span className="font-medium text-green-600">{marketplace.syncedProducts}</span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-gray-600">Beklemede:</span>
                <span className="font-medium text-yellow-600">{marketplace.pendingProducts}</span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-gray-600">Hata:</span>
                <span className="font-medium text-red-600">{marketplace.errorCount}</span>
              </div>
            </div>
            
            <div className="mt-4 pt-4 border-t border-gray-200">
              <div className="flex justify-between items-center">
                <span className="text-xs text-gray-500">Son sync: {marketplace.lastSync}</span>
                <div className="flex space-x-1">
                  <button className="text-blue-600 hover:text-blue-800 text-xs">Yapılandır</button>
                  <button className="text-green-600 hover:text-green-800 text-xs">Test Et</button>
                </div>
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* Charts Row */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Sync Performance Chart */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Senkronizasyon Performansı</h2>
          <ResponsiveContainer width="100%" height={300}>
            <BarChart data={syncPerformanceData}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="name" />
              <YAxis />
              <Tooltip />
              <Bar dataKey="synced" fill="#10B981" name="Senkronize" />
              <Bar dataKey="pending" fill="#F59E0B" name="Beklemede" />
              <Bar dataKey="errors" fill="#EF4444" name="Hata" />
            </BarChart>
          </ResponsiveContainer>
        </div>

        {/* API Response Time Chart */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">API Yanıt Süreleri (ms)</h2>
          <ResponsiveContainer width="100%" height={300}>
            <LineChart data={apiResponseTimeData}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="time" />
              <YAxis />
              <Tooltip />
              <Line type="monotone" dataKey="trendyol" stroke="#FF6B35" strokeWidth={2} name="Trendyol" />
              <Line type="monotone" dataKey="amazon" stroke="#FF9900" strokeWidth={2} name="Amazon" />
              <Line type="monotone" dataKey="n11" stroke="#FF6000" strokeWidth={2} name="N11" />
            </LineChart>
          </ResponsiveContainer>
        </div>
      </div>

      {/* API Configurations & Product Mappings */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* API Configurations */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex justify-between items-center mb-4">
            <h2 className="text-lg font-semibold text-gray-900">API Konfigürasyonları</h2>
            <button className="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
              + Yeni API
            </button>
          </div>
          <div className="space-y-3">
            {apiConfigs.map((config, index) => (
              <div key={index} className="p-4 bg-gray-50 rounded-lg">
                <div className="flex items-center justify-between mb-2">
                  <div className="flex items-center space-x-2">
                    <span className="font-medium text-gray-900">{config.marketplace}</span>
                    <span className={`px-2 py-1 rounded-full text-xs ${getApiStatusColor(config.status)}`}>
                      {config.status}
                    </span>
                  </div>
                  <div className="flex space-x-1">
                    <button className="text-blue-600 hover:text-blue-800 text-sm">Test</button>
                    <button className="text-green-600 hover:text-green-800 text-sm">Düzenle</button>
                  </div>
                </div>
                <div className="text-sm text-gray-600 mb-2">
                  <span className="font-mono bg-gray-200 px-2 py-1 rounded">{config.method}</span>
                  <span className="ml-2">{config.endpoint}</span>
                </div>
                <div className="flex justify-between text-xs text-gray-500">
                  <span>Yanıt: {config.responseTime}ms</span>
                  <span>Başarı: {config.successRate}%</span>
                  <span>Son test: {config.lastTest}</span>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Product Mappings */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex justify-between items-center mb-4">
            <h2 className="text-lg font-semibold text-gray-900">Ürün Kategori Haritalama</h2>
            <button className="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
              + Yeni Haritalama
            </button>
          </div>
          <div className="space-y-3">
            {productMappings.map((mapping) => (
              <div key={mapping.id} className="p-4 bg-gray-50 rounded-lg">
                <div className="flex items-center justify-between mb-2">
                  <span className="font-medium text-gray-900">{mapping.marketplace}</span>
                  <span className={`px-2 py-1 rounded-full text-xs ${getMappingStatusColor(mapping.status)}`}>
                    {mapping.status}
                  </span>
                </div>
                <div className="text-sm text-gray-600 mb-2">
                  <div className="mb-1">
                    <span className="text-gray-500">Kaynak:</span> {mapping.sourceCategory}
                  </div>
                  <div>
                    <span className="text-gray-500">Hedef:</span> {mapping.targetCategory}
                  </div>
                </div>
                <div className="flex justify-between items-center">
                  <div className="text-xs text-gray-500">
                    Alan eşleme: {mapping.mappedFields}/{mapping.totalFields}
                  </div>
                  <div className="flex space-x-1">
                    <button className="text-blue-600 hover:text-blue-800 text-sm">Düzenle</button>
                    <button className="text-green-600 hover:text-green-800 text-sm">Test</button>
                  </div>
                </div>
                <div className="mt-2">
                  <div className="w-full bg-gray-200 rounded-full h-2">
                    <div 
                      className="bg-blue-600 h-2 rounded-full" 
                      style={{ width: `${(mapping.mappedFields / mapping.totalFields) * 100}%` }}
                    ></div>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Integration Status Overview */}
      <div className="bg-white rounded-lg shadow-md p-6">
        <h2 className="text-lg font-semibold text-gray-900 mb-4">Entegrasyon Durumu Özeti</h2>
        <div className="flex items-center">
          <div className="w-1/3">
            <ResponsiveContainer width="100%" height={200}>
              <PieChart>
                <Pie
                  data={integrationStatusData}
                  cx="50%"
                  cy="50%"
                  labelLine={false}
                  label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                  outerRadius={60}
                  fill="#8884d8"
                  dataKey="value"
                >
                  {integrationStatusData.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={entry.color} />
                  ))}
                </Pie>
                <Tooltip />
              </PieChart>
            </ResponsiveContainer>
          </div>
          <div className="w-2/3 pl-6">
            <div className="grid grid-cols-2 gap-4">
              <div className="bg-green-50 rounded-lg p-4">
                <div className="text-sm text-green-600 font-medium">Aktif Entegrasyonlar</div>
                <div className="text-2xl font-bold text-green-800">2</div>
                <div className="text-xs text-green-500">Trendyol, Amazon</div>
              </div>
              <div className="bg-yellow-50 rounded-lg p-4">
                <div className="text-sm text-yellow-600 font-medium">Yapılandırılıyor</div>
                <div className="text-2xl font-bold text-yellow-800">2</div>
                <div className="text-xs text-yellow-500">N11, Ozon</div>
              </div>
              <div className="bg-red-50 rounded-lg p-4">
                <div className="text-sm text-red-600 font-medium">Hata Durumunda</div>
                <div className="text-2xl font-bold text-red-800">1</div>
                <div className="text-xs text-red-500">Hepsiburada</div>
              </div>
              <div className="bg-gray-50 rounded-lg p-4">
                <div className="text-sm text-gray-600 font-medium">Bağlantısız</div>
                <div className="text-2xl font-bold text-gray-800">1</div>
                <div className="text-xs text-gray-500">eBay</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default IntegratorDashboard; 