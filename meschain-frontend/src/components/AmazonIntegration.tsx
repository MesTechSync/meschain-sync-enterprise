import React, { useState, useEffect, useCallback } from 'react';
import { 
  ShoppingCart, 
  Package, 
  DollarSign, 
  TrendingUp, 
  Truck, 
  Clock, 
  Globe, 
  Settings,
  RefreshCcw,
  AlertCircle,
  CheckCircle,
  Eye,
  Plus,
  Star,
  BarChart3,
  Users,
  Box,
  Zap
} from 'lucide-react';

// Types and Interfaces
interface AmazonProductData {
  asin: string;
  title: string;
  price: number;
  currency: string;
  rank: number;
  reviews: number;
  rating: number;
  availability: 'in_stock' | 'out_of_stock' | 'limited';
  fulfillment: 'FBA' | 'FBM' | 'Amazon';
  category: string;
  imageUrl: string;
  salesRank: number;
  lastUpdated: string;
}

interface AmazonMetrics {
  totalProducts: number;
  fbaProducts: number;
  totalSales: number;
  monthlyRevenue: number;
  averageRating: number;
  totalReviews: number;
  returnRate: number;
  inventoryHealth: number;
}

interface AmazonConfiguration {
  marketplaceId: string;
  sellerId: string;
  accessKey: string;
  secretKey: string;
  roleArn: string;
  refreshToken: string;
  region: string;
  sandbox: boolean;
  autoSync: boolean;
  fbaEnabled: boolean;
}

const AmazonIntegration: React.FC = () => {
  const [isLoading, setIsLoading] = useState(false);
  const [lastUpdate, setLastUpdate] = useState<Date>(new Date());
  const [autoRefresh, setAutoRefresh] = useState(true);
  const [activeTab, setActiveTab] = useState('dashboard');
  const [apiStatus, setApiStatus] = useState<'online' | 'offline' | 'testing'>('online');
  
  // State for Amazon data
  const [products, setProducts] = useState<AmazonProductData[]>([]);
  const [metrics, setMetrics] = useState<AmazonMetrics>({
    totalProducts: 0,
    fbaProducts: 0,
    totalSales: 0,
    monthlyRevenue: 0,
    averageRating: 0,
    totalReviews: 0,
    returnRate: 0,
    inventoryHealth: 0
  });
  const [config, setConfig] = useState<AmazonConfiguration>({
    marketplaceId: 'ATVPDKIKX0DER',
    sellerId: '',
    accessKey: '',
    secretKey: '',
    roleArn: '',
    refreshToken: '',
    region: 'us-east-1',
    sandbox: true,
    autoSync: false,
    fbaEnabled: true
  });

  // Sample data generation
  const generateSampleProducts = useCallback((): AmazonProductData[] => [
    {
      asin: 'B08N5WRWNW',
      title: 'Echo Dot (4th Gen) Smart Speaker with Alexa',
      price: 49.99,
      currency: 'USD',
      rank: 1,
      reviews: 45623,
      rating: 4.7,
      availability: 'in_stock',
      fulfillment: 'FBA',
      category: 'Electronics > Smart Home',
      imageUrl: '/api/placeholder/200/200',
      salesRank: 1,
      lastUpdated: new Date().toISOString()
    },
    {
      asin: 'B07XJ8C8F7',
      title: 'Fire TV Stick 4K Max streaming device',
      price: 54.99,
      currency: 'USD',
      rank: 3,
      reviews: 28934,
      rating: 4.5,
      availability: 'in_stock',
      fulfillment: 'Amazon',
      category: 'Electronics > Streaming Media',
      imageUrl: '/api/placeholder/200/200',
      salesRank: 3,
      lastUpdated: new Date().toISOString()
    }
  ], []);

  const generateSampleMetrics = useCallback((): AmazonMetrics => ({
    totalProducts: 324,
    fbaProducts: 256,
    totalSales: 1547,
    monthlyRevenue: 48756.89,
    averageRating: 4.3,
    totalReviews: 156789,
    returnRate: 3.2,
    inventoryHealth: 92.7
  }), []);

  // Fetch data from API
  const fetchAmazonData = useCallback(async () => {
    setIsLoading(true);
    try {
      if (apiStatus === 'online') {
        // Real SP-API calls would go here
        const response = await fetch('/admin/extension/module/meschain/api/amazon/dashboard');
        if (!response.ok) throw new Error('SP-API Error');
        const data = await response.json();
        setProducts(data.products || []);
        setMetrics(data.metrics || generateSampleMetrics());
      } else {
        // Demo data
        await new Promise(resolve => setTimeout(resolve, 1000));
        setProducts(generateSampleProducts());
        setMetrics(generateSampleMetrics());
      }
      setLastUpdate(new Date());
    } catch (error) {
      console.error('Error fetching Amazon data:', error);
      setProducts(generateSampleProducts());
      setMetrics(generateSampleMetrics());
      setApiStatus('offline');
    } finally {
      setIsLoading(false);
    }
  }, [apiStatus, generateSampleProducts, generateSampleMetrics]);

  // Auto-refresh
  useEffect(() => {
    if (autoRefresh) {
      const interval = setInterval(fetchAmazonData, 30000);
      return () => clearInterval(interval);
    }
  }, [autoRefresh, fetchAmazonData]);

  // Initial load
  useEffect(() => {
    fetchAmazonData();
  }, [fetchAmazonData]);

  // Utility functions
  const formatCurrency = (amount: number, currency: string = 'USD') => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: currency
    }).format(amount);
  };

  const getAvailabilityColor = (availability: string) => {
    const colors = {
      in_stock: 'text-green-600 bg-green-100',
      out_of_stock: 'text-red-600 bg-red-100',
      limited: 'text-yellow-600 bg-yellow-100'
    };
    return colors[availability as keyof typeof colors] || 'text-gray-600 bg-gray-100';
  };

  const getFulfillmentBadge = (fulfillment: string) => {
    const badges = {
      FBA: 'bg-orange-100 text-orange-800',
      FBM: 'bg-blue-100 text-blue-800',
      Amazon: 'bg-purple-100 text-purple-800'
    };
    return badges[fulfillment as keyof typeof badges] || 'bg-gray-100 text-gray-800';
  };

  // Component render functions
  const renderHeader = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
      <div className="px-6 py-4 border-b border-gray-200">
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-3">
            <div className="p-2 bg-orange-100 rounded-lg">
              <Box className="w-6 h-6 text-orange-600" />
            </div>
            <div>
              <h1 className="text-2xl font-bold text-gray-900">Amazon SP-API Integration</h1>
              <p className="text-sm text-gray-600">Seller Central & FBA management</p>
            </div>
          </div>
          
          <div className="flex items-center space-x-4">
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
              <span>{apiStatus === 'online' ? 'SP-API Live' : apiStatus === 'testing' ? 'Testing' : 'Demo Mode'}</span>
            </div>
            
            <button
              onClick={fetchAmazonData}
              disabled={isLoading}
              className="flex items-center space-x-2 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 disabled:opacity-50"
            >
              <RefreshCcw className={`w-4 h-4 ${isLoading ? 'animate-spin' : ''}`} />
              <span>Sync</span>
            </button>
          </div>
        </div>
        
        <div className="mt-4 text-sm text-gray-500">
          Last sync: {lastUpdate.toLocaleString('tr-TR')} | Region: {config.region}
        </div>
      </div>
      
      <div className="px-6 py-3">
        <nav className="flex space-x-6">
          {[
            { id: 'dashboard', label: 'Dashboard', icon: BarChart3 },
            { id: 'products', label: 'Products', icon: Package },
            { id: 'fba', label: 'FBA Inventory', icon: Truck },
            { id: 'analytics', label: 'Analytics', icon: TrendingUp },
            { id: 'settings', label: 'SP-API Config', icon: Settings }
          ].map(({ id, label, icon: Icon }) => (
            <button
              key={id}
              onClick={() => setActiveTab(id)}
              className={`flex items-center space-x-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors ${
                activeTab === id
                  ? 'bg-orange-100 text-orange-700'
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
          title: 'Monthly Revenue',
          value: formatCurrency(metrics.monthlyRevenue),
          change: '+18.2%',
          positive: true,
          icon: DollarSign,
          color: 'green'
        },
        {
          title: 'Total Products',
          value: metrics.totalProducts.toString(),
          change: `${metrics.fbaProducts} FBA`,
          positive: true,
          icon: Package,
          color: 'blue'
        },
        {
          title: 'Average Rating',
          value: metrics.averageRating.toString(),
          change: `${metrics.totalReviews.toLocaleString()} reviews`,
          positive: true,
          icon: Star,
          color: 'yellow'
        },
        {
          title: 'Inventory Health',
          value: `${metrics.inventoryHealth}%`,
          change: `${metrics.returnRate}% return rate`,
          positive: metrics.returnRate < 5,
          icon: Zap,
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
          <h2 className="text-lg font-semibold text-gray-900">Product Catalog</h2>
          <button className="flex items-center space-x-2 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
            <Plus className="w-4 h-4" />
            <span>Add Product</span>
          </button>
        </div>
      </div>
      
      <div className="overflow-x-auto">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Product
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                ASIN
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Price
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Rating
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Fulfillment
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody className="bg-white divide-y divide-gray-200">
            {products.map((product) => (
              <tr key={product.asin} className="hover:bg-gray-50">
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
                  <code className="text-sm bg-gray-100 px-2 py-1 rounded">{product.asin}</code>
                </td>
                <td className="px-6 py-4">
                  <div className="text-sm font-medium text-gray-900">
                    {formatCurrency(product.price, product.currency)}
                  </div>
                </td>
                <td className="px-6 py-4">
                  <div className="flex items-center space-x-1">
                    <Star className="w-4 h-4 text-yellow-400 fill-current" />
                    <span className="text-sm text-gray-900">{product.rating}</span>
                    <span className="text-sm text-gray-500">({product.reviews.toLocaleString()})</span>
                  </div>
                </td>
                <td className="px-6 py-4">
                  <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getFulfillmentBadge(product.fulfillment)}`}>
                    {product.fulfillment}
                  </span>
                </td>
                <td className="px-6 py-4">
                  <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getAvailabilityColor(product.availability)}`}>
                    {product.availability.replace('_', ' ')}
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

  const renderSettings = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <h2 className="text-lg font-semibold text-gray-900 mb-6">Amazon SP-API Configuration</h2>
      
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Marketplace ID
          </label>
          <select
            value={config.marketplaceId}
            onChange={(e) => setConfig({...config, marketplaceId: e.target.value})}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
          >
            <option value="ATVPDKIKX0DER">Amazon.com (US)</option>
            <option value="A2EUQ1WTGCTBG2">Amazon.ca (Canada)</option>
            <option value="A1AM78C64UM0Y8">Amazon.com.mx (Mexico)</option>
            <option value="A1PA6795UKMFR9">Amazon.de (Germany)</option>
            <option value="A1RKKUPIHCS9HS">Amazon.es (Spain)</option>
            <option value="A13V1IB3VIYZZH">Amazon.fr (France)</option>
          </select>
        </div>
        
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Seller ID
          </label>
          <input
            type="text"
            value={config.sellerId}
            onChange={(e) => setConfig({...config, sellerId: e.target.value})}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
            placeholder="Enter your Seller ID"
          />
        </div>
        
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Access Key
          </label>
          <input
            type="text"
            value={config.accessKey}
            onChange={(e) => setConfig({...config, accessKey: e.target.value})}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
            placeholder="AWS Access Key ID"
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
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
            placeholder="AWS Secret Access Key"
          />
        </div>
        
        <div className="md:col-span-2">
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Role ARN
          </label>
          <input
            type="text"
            value={config.roleArn}
            onChange={(e) => setConfig({...config, roleArn: e.target.value})}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
            placeholder="arn:aws:iam::123456789012:role/SellingPartnerAPI"
          />
        </div>
      </div>
      
      <div className="mt-6 space-y-4">
        <div className="flex items-center justify-between">
          <div>
            <label className="text-sm font-medium text-gray-700">Sandbox Mode</label>
            <p className="text-sm text-gray-500">Use Amazon sandbox for testing</p>
          </div>
          <button
            onClick={() => setConfig({...config, sandbox: !config.sandbox})}
            className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors ${
              config.sandbox ? 'bg-orange-600' : 'bg-gray-200'
            }`}
          >
            <span className={`inline-block h-4 w-4 transform rounded-full bg-white transition-transform ${
              config.sandbox ? 'translate-x-6' : 'translate-x-1'
            }`} />
          </button>
        </div>
        
        <div className="flex items-center justify-between">
          <div>
            <label className="text-sm font-medium text-gray-700">FBA Integration</label>
            <p className="text-sm text-gray-500">Enable Fulfillment by Amazon features</p>
          </div>
          <button
            onClick={() => setConfig({...config, fbaEnabled: !config.fbaEnabled})}
            className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors ${
              config.fbaEnabled ? 'bg-orange-600' : 'bg-gray-200'
            }`}
          >
            <span className={`inline-block h-4 w-4 transform rounded-full bg-white transition-transform ${
              config.fbaEnabled ? 'translate-x-6' : 'translate-x-1'
            }`} />
          </button>
        </div>
      </div>
      
      <div className="mt-6 flex justify-end space-x-3">
        <button className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
          Test SP-API Connection
        </button>
        <button className="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
          Save Configuration
        </button>
      </div>
    </div>
  );

  const renderDashboard = () => (
    <div className="space-y-6">
      {renderMetricsCards()}
      {renderProductsTable()}
    </div>
  );

  // Main render
  return (
    <div className="min-h-screen bg-gray-50 p-6">
      {renderHeader()}
      
      {activeTab === 'dashboard' && renderDashboard()}
      {activeTab === 'products' && renderProductsTable()}
      {activeTab === 'fba' && renderProductsTable()}
      {activeTab === 'analytics' && renderDashboard()}
      {activeTab === 'settings' && renderSettings()}
    </div>
  );
};

export default AmazonIntegration; 