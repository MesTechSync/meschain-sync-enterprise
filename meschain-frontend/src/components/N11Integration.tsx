import React, { useState, useEffect, useCallback } from 'react';
import { 
  ShoppingCart, 
  Package, 
  DollarSign, 
  TrendingUp, 
  Truck, 
  Star, 
  Target,
  Settings,
  RefreshCcw,
  AlertCircle,
  CheckCircle,
  Eye,
  Plus,
  BarChart3,
  Users,
  Zap,
  Award,
  Megaphone,
  TrendingDown
} from 'lucide-react';

// Types and Interfaces
interface N11ProductData {
  id: string;
  title: string;
  price: number;
  currency: string;
  stock: number;
  sales: number;
  views: number;
  category: string;
  imageUrl: string;
  status: 'active' | 'passive' | 'rejected' | 'waiting';
  campaignStatus: 'none' | 'active' | 'pending' | 'completed';
  commissionRate: number;
  lastUpdated: string;
}

interface N11Metrics {
  totalProducts: number;
  activeProducts: number;
  totalSales: number;
  monthlyRevenue: number;
  averageRating: number;
  campaignCount: number;
  commissionEarned: number;
  proSellerBonus: number;
}

interface N11Configuration {
  apiKey: string;
  secretKey: string;
  storeKey: string;
  proSeller: boolean;
  autoCampaign: boolean;
  commissionRate: number;
  priceMarkup: number;
  autoDiscount: boolean;
  cargoCompany: string;
}

interface N11Campaign {
  id: string;
  name: string;
  type: 'discount' | 'shipping' | 'category' | 'special';
  discount: number;
  startDate: string;
  endDate: string;
  status: 'active' | 'pending' | 'completed' | 'cancelled';
  products: number;
  revenue: number;
}

const N11Integration: React.FC = () => {
  const [isLoading, setIsLoading] = useState(false);
  const [lastUpdate, setLastUpdate] = useState<Date>(new Date());
  const [autoRefresh, setAutoRefresh] = useState(true);
  const [activeTab, setActiveTab] = useState('dashboard');
  const [apiStatus, setApiStatus] = useState<'online' | 'offline' | 'testing'>('online');
  
  // State for N11 data
  const [products, setProducts] = useState<N11ProductData[]>([]);
  const [campaigns, setCampaigns] = useState<N11Campaign[]>([]);
  const [metrics, setMetrics] = useState<N11Metrics>({
    totalProducts: 0,
    activeProducts: 0,
    totalSales: 0,
    monthlyRevenue: 0,
    averageRating: 0,
    campaignCount: 0,
    commissionEarned: 0,
    proSellerBonus: 0
  });
  const [config, setConfig] = useState<N11Configuration>({
    apiKey: '',
    secretKey: '',
    storeKey: '',
    proSeller: true,
    autoCampaign: false,
    commissionRate: 8.5,
    priceMarkup: 15,
    autoDiscount: true,
    cargoCompany: 'MNG'
  });

  // Sample data generation for Turkish market
  const generateSampleProducts = useCallback((): N11ProductData[] => [
    {
      id: 'n11_001',
      title: 'Samsung Galaxy S24 Ultra 256GB Titanium',
      price: 45999,
      currency: 'TRY',
      stock: 25,
      sales: 156,
      views: 2847,
      category: 'Cep Telefonu > Android Telefonlar',
      imageUrl: '/api/placeholder/200/200',
      status: 'active',
      campaignStatus: 'active',
      commissionRate: 8.5,
      lastUpdated: new Date().toISOString()
    },
    {
      id: 'n11_002',
      title: 'Apple iPhone 15 Pro 128GB Doğal Titanyum',
      price: 52999,
      currency: 'TRY',
      stock: 18,
      sales: 89,
      views: 1923,
      category: 'Cep Telefonu > iPhone',
      imageUrl: '/api/placeholder/200/200',
      status: 'active',
      campaignStatus: 'none',
      commissionRate: 7.8,
      lastUpdated: new Date().toISOString()
    },
    {
      id: 'n11_003',
      title: 'Dyson V15 Detect Absolute Kablosuz Süpürge',
      price: 8999,
      currency: 'TRY',
      stock: 8,
      sales: 34,
      views: 856,
      category: 'Ev & Yaşam > Süpürge',
      imageUrl: '/api/placeholder/200/200',
      status: 'active',
      campaignStatus: 'pending',
      commissionRate: 12.5,
      lastUpdated: new Date().toISOString()
    }
  ], []);

  const generateSampleCampaigns = useCallback((): N11Campaign[] => [
    {
      id: 'camp_001',
      name: 'Teknoloji Ürünleri %20 İndirim',
      type: 'category',
      discount: 20,
      startDate: new Date(Date.now() - 86400000 * 3).toISOString(),
      endDate: new Date(Date.now() + 86400000 * 4).toISOString(),
      status: 'active',
      products: 145,
      revenue: 1247890
    },
    {
      id: 'camp_002',
      name: 'Ücretsiz Kargo Kampanyası',
      type: 'shipping',
      discount: 0,
      startDate: new Date(Date.now() - 86400000 * 10).toISOString(),
      endDate: new Date(Date.now() + 86400000 * 20).toISOString(),
      status: 'active',
      products: 89,
      revenue: 567432
    },
    {
      id: 'camp_003',
      name: 'Flash Sale - Sınırlı Süre',
      type: 'special',
      discount: 35,
      startDate: new Date(Date.now() - 86400000).toISOString(),
      endDate: new Date(Date.now() + 86400000).toISOString(),
      status: 'active',
      products: 23,
      revenue: 189567
    }
  ], []);

  const generateSampleMetrics = useCallback((): N11Metrics => ({
    totalProducts: 324,
    activeProducts: 287,
    totalSales: 1847,
    monthlyRevenue: 2156789,
    averageRating: 4.6,
    campaignCount: 8,
    commissionEarned: 183227,
    proSellerBonus: 45780
  }), []);

  // Fetch data from API
  const fetchN11Data = useCallback(async () => {
    setIsLoading(true);
    try {
      if (apiStatus === 'online') {
        // Real N11 API calls would go here
        const response = await fetch('/admin/extension/module/meschain/api/n11/dashboard');
        if (!response.ok) throw new Error('N11 API Error');
        const data = await response.json();
        setProducts(data.products || []);
        setCampaigns(data.campaigns || []);
        setMetrics(data.metrics || generateSampleMetrics());
      } else {
        // Demo data
        await new Promise(resolve => setTimeout(resolve, 900));
        setProducts(generateSampleProducts());
        setCampaigns(generateSampleCampaigns());
        setMetrics(generateSampleMetrics());
      }
      setLastUpdate(new Date());
    } catch (error) {
      console.error('Error fetching N11 data:', error);
      setProducts(generateSampleProducts());
      setCampaigns(generateSampleCampaigns());
      setMetrics(generateSampleMetrics());
      setApiStatus('offline');
    } finally {
      setIsLoading(false);
    }
  }, [apiStatus, generateSampleProducts, generateSampleCampaigns, generateSampleMetrics]);

  // Auto-refresh
  useEffect(() => {
    if (autoRefresh) {
      const interval = setInterval(fetchN11Data, 30000);
      return () => clearInterval(interval);
    }
  }, [autoRefresh, fetchN11Data]);

  // Initial load
  useEffect(() => {
    fetchN11Data();
  }, [fetchN11Data]);

  // Utility functions
  const formatCurrency = (amount: number, currency: string = 'TRY') => {
    return new Intl.NumberFormat('tr-TR', {
      style: 'currency',
      currency: currency
    }).format(amount);
  };

  const getStatusColor = (status: string) => {
    const colors = {
      active: 'text-green-600 bg-green-100',
      passive: 'text-gray-600 bg-gray-100',
      rejected: 'text-red-600 bg-red-100',
      waiting: 'text-yellow-600 bg-yellow-100',
      pending: 'text-blue-600 bg-blue-100',
      completed: 'text-purple-600 bg-purple-100',
      cancelled: 'text-red-600 bg-red-100'
    };
    return colors[status as keyof typeof colors] || 'text-gray-600 bg-gray-100';
  };

  const getCampaignIcon = (type: string) => {
    switch (type) {
      case 'discount': return <TrendingDown className="w-4 h-4" />;
      case 'shipping': return <Truck className="w-4 h-4" />;
      case 'category': return <Package className="w-4 h-4" />;
      case 'special': return <Zap className="w-4 h-4" />;
      default: return <Target className="w-4 h-4" />;
    }
  };

  // Component render functions
  const renderHeader = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
      <div className="px-6 py-4 border-b border-gray-200">
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-3">
            <div className="p-2 bg-purple-100 rounded-lg">
              <Package className="w-6 h-6 text-purple-600" />
            </div>
            <div>
              <h1 className="text-2xl font-bold text-gray-900">N11 Integration</h1>
              <p className="text-sm text-gray-600">Türkiye'nin en büyük marketplace platformu</p>
            </div>
          </div>
          
          <div className="flex items-center space-x-4">
            {config.proSeller && (
              <div className="flex items-center space-x-2 px-3 py-1 rounded-full text-sm font-medium bg-gold-100 text-gold-800">
                <Award className="w-4 h-4" />
                <span>Pro Seller</span>
              </div>
            )}
            
            <div className={`flex items-center space-x-2 px-3 py-1 rounded-full text-sm font-medium ${
              apiStatus === 'online' ? 'bg-green-100 text-green-800' :
              apiStatus === 'testing' ? 'bg-yellow-100 text-yellow-800' :
              'bg-red-100 text-red-800'
            }`}>
              <div className={`w-2 h-2 rounded-full ${
                apiStatus === 'online' ? 'bg-green-500' :
                apiStatus === 'testing' ? 'bg-yellow-500' :
                'bg-red-500'
              }`}></div>
              <span>{apiStatus === 'online' ? 'N11 Live' : apiStatus === 'testing' ? 'Testing' : 'Demo Mode'}</span>
            </div>
            
            <button
              onClick={fetchN11Data}
              disabled={isLoading}
              className="flex items-center space-x-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 disabled:opacity-50"
            >
              <RefreshCcw className={`w-4 h-4 ${isLoading ? 'animate-spin' : ''}`} />
              <span>Sync</span>
            </button>
          </div>
        </div>
        
        <div className="mt-4 text-sm text-gray-500">
          Last sync: {lastUpdate.toLocaleString('tr-TR')} | Commission Rate: %{config.commissionRate}
        </div>
      </div>
      
      <div className="px-6 py-3">
        <nav className="flex space-x-6">
          {[
            { id: 'dashboard', label: 'Dashboard', icon: BarChart3 },
            { id: 'products', label: 'Ürünler', icon: Package },
            { id: 'campaigns', label: 'Kampanyalar', icon: Megaphone },
            { id: 'analytics', label: 'Analytics', icon: TrendingUp },
            { id: 'settings', label: 'API Ayarları', icon: Settings }
          ].map(({ id, label, icon: Icon }) => (
            <button
              key={id}
              onClick={() => setActiveTab(id)}
              className={`flex items-center space-x-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors ${
                activeTab === id
                  ? 'bg-purple-100 text-purple-700'
                  : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
              }`}
            >
              <Icon className="w-4 h-4" />
              <span>{label}</span>
            </button>
          ))}
        </nav>
      </div>
    </div>
  );

  const renderMetricsCards = () => (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      {[
        {
          title: 'Aylık Ciro',
          value: formatCurrency(metrics.monthlyRevenue),
          change: '+24.8%',
          positive: true,
          icon: DollarSign,
          color: 'green'
        },
        {
          title: 'Toplam Ürün',
          value: metrics.totalProducts.toString(),
          change: `${metrics.activeProducts} aktif`,
          positive: true,
          icon: Package,
          color: 'blue'
        },
        {
          title: 'Ortalama Puan',
          value: metrics.averageRating.toString(),
          change: `${metrics.totalSales} satış`,
          positive: true,
          icon: Star,
          color: 'yellow'
        },
        {
          title: 'Pro Seller Bonus',
          value: formatCurrency(metrics.proSellerBonus),
          change: `${metrics.campaignCount} kampanya`,
          positive: true,
          icon: Award,
          color: 'purple'
        }
      ].map((metric, index) => (
        <div key={index} className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between">
            <div className={`p-2 rounded-lg bg-${metric.color}-100`}>
              <metric.icon className={`w-6 h-6 text-${metric.color}-600`} />
            </div>
            <span className={`text-sm font-medium ${
              metric.positive ? 'text-green-600' : 'text-red-600'
            }`}>
              {metric.change}
            </span>
          </div>
          <div className="mt-4">
            <div className="text-2xl font-bold text-gray-900">{metric.value}</div>
            <div className="text-sm text-gray-600">{metric.title}</div>
          </div>
        </div>
      ))}
    </div>
  );

  const renderProductsTable = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200">
      <div className="px-6 py-4 border-b border-gray-200">
        <div className="flex items-center justify-between">
          <h2 className="text-lg font-semibold text-gray-900">Ürün Kataloğu</h2>
          <button className="flex items-center space-x-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
            <Plus className="w-4 h-4" />
            <span>Ürün Ekle</span>
          </button>
        </div>
      </div>
      
      <div className="overflow-x-auto">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Ürün
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Fiyat
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Stok
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Satış/Görüntülenme
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Kampanya
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
                <td className="px-6 py-4">
                  <div className="flex items-center space-x-3">
                    <img 
                      src={product.imageUrl} 
                      alt={product.title}
                      className="w-12 h-12 rounded-lg object-cover"
                    />
                    <div>
                      <div className="text-sm font-medium text-gray-900">{product.title}</div>
                      <div className="text-sm text-gray-500">{product.category}</div>
                    </div>
                  </div>
                </td>
                <td className="px-6 py-4">
                  <div className="text-sm font-medium text-gray-900">
                    {formatCurrency(product.price, product.currency)}
                  </div>
                  <div className="text-sm text-gray-500">
                    Komisyon: %{product.commissionRate}
                  </div>
                </td>
                <td className="px-6 py-4">
                  <div className={`text-sm font-medium ${product.stock > 10 ? 'text-green-600' : product.stock > 0 ? 'text-yellow-600' : 'text-red-600'}`}>
                    {product.stock} adet
                  </div>
                </td>
                <td className="px-6 py-4">
                  <div className="text-sm text-gray-900">
                    {product.sales} satış / {product.views} görüntülenme
                  </div>
                  <div className="text-sm text-gray-500">
                    CVR: %{((product.sales / product.views) * 100).toFixed(1)}
                  </div>
                </td>
                <td className="px-6 py-4">
                  <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(product.campaignStatus)}`}>
                    {product.campaignStatus === 'none' ? 'Yok' : 
                     product.campaignStatus === 'active' ? 'Aktif' :
                     product.campaignStatus === 'pending' ? 'Beklemede' : 'Tamamlandı'}
                  </span>
                </td>
                <td className="px-6 py-4">
                  <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(product.status)}`}>
                    {product.status === 'active' ? 'Aktif' : 
                     product.status === 'passive' ? 'Pasif' :
                     product.status === 'rejected' ? 'Reddedildi' : 'Beklemede'}
                  </span>
                </td>
                <td className="px-6 py-4">
                  <div className="flex items-center space-x-2">
                    <button className="p-1 text-gray-400 hover:text-gray-600">
                      <Eye className="w-4 h-4" />
                    </button>
                    <button className="p-1 text-gray-400 hover:text-gray-600">
                      <Settings className="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );

  const renderCampaignsTable = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200">
      <div className="px-6 py-4 border-b border-gray-200">
        <div className="flex items-center justify-between">
          <h2 className="text-lg font-semibold text-gray-900">Aktif Kampanyalar</h2>
          <button className="flex items-center space-x-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
            <Plus className="w-4 h-4" />
            <span>Kampanya Oluştur</span>
          </button>
        </div>
      </div>
      
      <div className="overflow-x-auto">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Kampanya
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Tip
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                İndirim
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Süre
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Ürün Sayısı
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Gelir
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Durum
              </th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {campaigns.map((campaign) => (
              <tr key={campaign.id} className="hover:bg-gray-50">
                <td className="px-6 py-4">
                  <div className="flex items-center space-x-3">
                    <div className="p-2 bg-purple-100 rounded-lg">
                      {getCampaignIcon(campaign.type)}
                    </div>
                    <div>
                      <div className="text-sm font-medium text-gray-900">{campaign.name}</div>
                    </div>
                  </div>
                </td>
                <td className="px-6 py-4">
                  <span className="text-sm text-gray-900 capitalize">
                    {campaign.type === 'discount' ? 'İndirim' :
                     campaign.type === 'shipping' ? 'Kargo' :
                     campaign.type === 'category' ? 'Kategori' : 'Özel'}
                  </span>
                </td>
                <td className="px-6 py-4">
                  <div className="text-sm font-medium text-gray-900">
                    {campaign.discount > 0 ? `%${campaign.discount}` : 'Ücretsiz Kargo'}
                  </div>
                </td>
                <td className="px-6 py-4">
                  <div className="text-sm text-gray-900">
                    {new Date(campaign.startDate).toLocaleDateString('tr-TR')} - {new Date(campaign.endDate).toLocaleDateString('tr-TR')}
                  </div>
                </td>
                <td className="px-6 py-4">
                  <div className="text-sm text-gray-900">{campaign.products} ürün</div>
                </td>
                <td className="px-6 py-4">
                  <div className="text-sm font-medium text-gray-900">
                    {formatCurrency(campaign.revenue)}
                  </div>
                </td>
                <td className="px-6 py-4">
                  <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(campaign.status)}`}>
                    {campaign.status === 'active' ? 'Aktif' :
                     campaign.status === 'pending' ? 'Beklemede' :
                     campaign.status === 'completed' ? 'Tamamlandı' : 'İptal'}
                  </span>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );

  const renderSettings = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <h2 className="text-lg font-semibold text-gray-900 mb-6">N11 API Ayarları</h2>
      
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            API Key
          </label>
          <input
            type="text"
            value={config.apiKey}
            onChange={(e) => setConfig({...config, apiKey: e.target.value})}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
            placeholder="N11 API anahtarınızı girin"
          />
        </div>
        
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Secret Key
          </label>
          <input
            type="password"
            value={config.secretKey}
            onChange={(e) => setConfig({...config, secretKey: e.target.value})}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
            placeholder="N11 gizli anahtarınızı girin"
          />
        </div>
        
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Store Key
          </label>
          <input
            type="text"
            value={config.storeKey}
            onChange={(e) => setConfig({...config, storeKey: e.target.value})}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
            placeholder="N11 mağaza anahtarınızı girin"
          />
        </div>
        
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Kargo Firması
          </label>
          <select
            value={config.cargoCompany}
            onChange={(e) => setConfig({...config, cargoCompany: e.target.value})}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
          >
            <option value="MNG">MNG Kargo</option>
            <option value="Yurtici">Yurtiçi Kargo</option>
            <option value="Aras">Aras Kargo</option>
            <option value="PTT">PTT Kargo</option>
            <option value="UPS">UPS Kargo</option>
          </select>
        </div>
        
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Komisyon Oranı (%)
          </label>
          <input
            type="number"
            value={config.commissionRate}
            onChange={(e) => setConfig({...config, commissionRate: parseFloat(e.target.value)})}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
            min="0"
            max="50"
            step="0.1"
          />
        </div>
        
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Fiyat Artış Oranı (%)
          </label>
          <input
            type="number"
            value={config.priceMarkup}
            onChange={(e) => setConfig({...config, priceMarkup: parseFloat(e.target.value)})}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
            min="0"
            max="100"
            step="0.1"
          />
        </div>
      </div>
      
      <div className="mt-6 space-y-4">
        <div className="flex items-center justify-between">
          <div>
            <label className="text-sm font-medium text-gray-700">Pro Seller</label>
            <p className="text-sm text-gray-500">N11 Pro Seller avantajlarından yararlanın</p>
          </div>
          <button
            onClick={() => setConfig({...config, proSeller: !config.proSeller})}
            className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors ${
              config.proSeller ? 'bg-purple-600' : 'bg-gray-200'
            }`}
          >
            <span className={`inline-block h-4 w-4 transform rounded-full bg-white transition-transform ${
              config.proSeller ? 'translate-x-6' : 'translate-x-1'
            }`} />
          </button>
        </div>
        
        <div className="flex items-center justify-between">
          <div>
            <label className="text-sm font-medium text-gray-700">Otomatik Kampanya</label>
            <p className="text-sm text-gray-500">Otomatik kampanya oluşturma</p>
          </div>
          <button
            onClick={() => setConfig({...config, autoCampaign: !config.autoCampaign})}
            className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors ${
              config.autoCampaign ? 'bg-purple-600' : 'bg-gray-200'
            }`}
          >
            <span className={`inline-block h-4 w-4 transform rounded-full bg-white transition-transform ${
              config.autoCampaign ? 'translate-x-6' : 'translate-x-1'
            }`} />
          </button>
        </div>
        
        <div className="flex items-center justify-between">
          <div>
            <label className="text-sm font-medium text-gray-700">Otomatik İndirim</label>
            <p className="text-sm text-gray-500">Rekabetçi fiyatlandırma için otomatik indirim</p>
          </div>
          <button
            onClick={() => setConfig({...config, autoDiscount: !config.autoDiscount})}
            className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors ${
              config.autoDiscount ? 'bg-purple-600' : 'bg-gray-200'
            }`}
          >
            <span className={`inline-block h-4 w-4 transform rounded-full bg-white transition-transform ${
              config.autoDiscount ? 'translate-x-6' : 'translate-x-1'
            }`} />
          </button>
        </div>
      </div>
      
      <div className="mt-6 flex justify-end space-x-3">
        <button className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
          Bağlantıyı Test Et
        </button>
        <button className="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
          Ayarları Kaydet
        </button>
      </div>
    </div>
  );

  const renderDashboard = () => (
    <div className="space-y-6">
      {renderMetricsCards()}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div>{renderProductsTable()}</div>
        <div>{renderCampaignsTable()}</div>
      </div>
    </div>
  );

  // Main render
  return (
    <div className="min-h-screen bg-gray-50 p-6">
      {renderHeader()}
      
      {activeTab === 'dashboard' && renderDashboard()}
      {activeTab === 'products' && renderProductsTable()}
      {activeTab === 'campaigns' && renderCampaignsTable()}
      {activeTab === 'analytics' && renderDashboard()}
      {activeTab === 'settings' && renderSettings()}
    </div>
  );
};

export default N11Integration; 