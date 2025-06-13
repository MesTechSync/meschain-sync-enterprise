import React, { useState, useEffect, useCallback } from 'react';
import { 
  ShoppingCart, 
  Gavel, 
  DollarSign, 
  TrendingUp, 
  Package, 
  Clock, 
  Globe, 
  Settings,
  RefreshCcw,
  AlertCircle,
  CheckCircle,
  Eye,
  Plus,
  Star,
  MessageCircle,
  BarChart3
} from 'lucide-react';

// Types and Interfaces
interface EbayListingData {
  id: string;
  title: string;
  price: number;
  currency: string;
  bids: number;
  watchers: number;
  timeLeft: string;
  status: 'active' | 'ended' | 'sold' | 'unsold';
  listingType: 'auction' | 'buyitnow' | 'classified';
  category: string;
  imageUrl: string;
  viewCount: number;
  lastUpdated: string;
}

interface EbayMetrics {
  totalListings: number;
  activeAuctions: number;
  soldItems: number;
  totalRevenue: number;
  averagePrice: number;
  successRate: number;
  feedbackScore: number;
  watchers: number;
}

interface EbayConfiguration {
  appId: string;
  certId: string;
  devId: string;
  userToken: string;
  sandbox: boolean;
  siteId: string;
  autoList: boolean;
  globalShipping: boolean;
}

const EbayIntegration: React.FC = () => {
  const [isLoading, setIsLoading] = useState(false);
  const [lastUpdate, setLastUpdate] = useState<Date>(new Date());
  const [autoRefresh, setAutoRefresh] = useState(true);
  const [activeTab, setActiveTab] = useState('dashboard');
  const [apiStatus, setApiStatus] = useState<'online' | 'offline' | 'testing'>('online');
  
  // State for eBay data
  const [listings, setListings] = useState<EbayListingData[]>([]);
  const [metrics, setMetrics] = useState<EbayMetrics>({
    totalListings: 0,
    activeAuctions: 0,
    soldItems: 0,
    totalRevenue: 0,
    averagePrice: 0,
    successRate: 0,
    feedbackScore: 0,
    watchers: 0
  });
  const [config, setConfig] = useState<EbayConfiguration>({
    appId: '',
    certId: '',
    devId: '',
    userToken: '',
    sandbox: true,
    siteId: '0',
    autoList: false,
    globalShipping: true
  });

  // Sample data for demo purposes
  const generateSampleListings = useCallback((): EbayListingData[] => [
    {
      id: 'eb_001',
      title: 'Apple iPhone 15 Pro Max 256GB Titanium',
      price: 1199.99,
      currency: 'USD',
      bids: 15,
      watchers: 47,
      timeLeft: '2d 5h 23m',
      status: 'active',
      listingType: 'auction',
      category: 'Electronics > Cell Phones',
      imageUrl: '/api/placeholder/200/200',
      viewCount: 234,
      lastUpdated: new Date().toISOString()
    },
    {
      id: 'eb_002',
      title: 'Samsung Galaxy Watch Ultra 47mm',
      price: 649.99,
      currency: 'USD',
      bids: 0,
      watchers: 12,
      timeLeft: 'Buy It Now',
      status: 'active',
      listingType: 'buyitnow',
      category: 'Electronics > Smartwatches',
      imageUrl: '/api/placeholder/200/200',
      viewCount: 89,
      lastUpdated: new Date().toISOString()
    }
  ], []);

  const generateSampleMetrics = useCallback((): EbayMetrics => ({
    totalListings: 156,
    activeAuctions: 23,
    soldItems: 89,
    totalRevenue: 25847.50,
    averagePrice: 290.42,
    successRate: 87.3,
    feedbackScore: 99.1,
    watchers: 342
  }), []);

  // Fetch data from API or demo
  const fetchEbayData = useCallback(async () => {
    setIsLoading(true);
    try {
      if (apiStatus === 'online') {
        // Real API calls would go here
        const response = await fetch('/admin/extension/module/meschain/api/ebay/dashboard');
        if (!response.ok) throw new Error('API Error');
        const data = await response.json();
        setListings(data.listings || []);
        setMetrics(data.metrics || generateSampleMetrics());
      } else {
        // Demo data
        await new Promise(resolve => setTimeout(resolve, 800));
        setListings(generateSampleListings());
        setMetrics(generateSampleMetrics());
      }
      setLastUpdate(new Date());
    } catch (error) {
      console.error('Error fetching eBay data:', error);
      setListings(generateSampleListings());
      setMetrics(generateSampleMetrics());
      setApiStatus('offline');
    } finally {
      setIsLoading(false);
    }
  }, [apiStatus, generateSampleListings, generateSampleMetrics]);

  // Auto-refresh functionality
  useEffect(() => {
    if (autoRefresh) {
      const interval = setInterval(fetchEbayData, 30000);
      return () => clearInterval(interval);
    }
  }, [autoRefresh, fetchEbayData]);

  // Initial data load
  useEffect(() => {
    fetchEbayData();
  }, [fetchEbayData]);

  // Utility functions
  const formatCurrency = (amount: number, currency: string = 'USD') => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: currency
    }).format(amount);
  };

  const getStatusColor = (status: string) => {
    const colors = {
      active: 'text-green-600 bg-green-100',
      ended: 'text-gray-600 bg-gray-100',
      sold: 'text-blue-600 bg-blue-100',
      unsold: 'text-red-600 bg-red-100'
    };
    return colors[status as keyof typeof colors] || 'text-gray-600 bg-gray-100';
  };

  const getListingTypeIcon = (type: string) => {
    switch (type) {
      case 'auction': return <Gavel className="w-4 h-4" />;
      case 'buyitnow': return <ShoppingCart className="w-4 h-4" />;
      default: return <Package className="w-4 h-4" />;
    }
  };

  // Component render functions
  const renderHeader = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
      <div className="px-6 py-4 border-b border-gray-200">
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-3">
            <div className="p-2 bg-blue-100 rounded-lg">
              <Gavel className="w-6 h-6 text-blue-600" />
            </div>
            <div>
              <h1 className="text-2xl font-bold text-gray-900">eBay Integration</h1>
              <p className="text-sm text-gray-600">Global auction marketplace management</p>
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
              <span>{apiStatus === 'online' ? 'Live Data' : apiStatus === 'testing' ? 'Testing' : 'Demo Mode'}</span>
            </div>
            
            <button
              onClick={fetchEbayData}
              disabled={isLoading}
              className="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
            >
              <RefreshCcw className={`w-4 h-4 ${isLoading ? 'animate-spin' : ''}`} />
              <span>Refresh</span>
            </button>
          </div>
        </div>
        
        <div className="mt-4 text-sm text-gray-500">
          Last updated: {lastUpdate.toLocaleString('tr-TR')}
        </div>
      </div>
      
      <div className="px-6 py-3">
        <nav className="flex space-x-6">
          {[
            { id: 'dashboard', label: 'Dashboard', icon: BarChart3 },
            { id: 'listings', label: 'Listings', icon: Package },
            { id: 'auctions', label: 'Auctions', icon: Gavel },
            { id: 'analytics', label: 'Analytics', icon: TrendingUp },
            { id: 'settings', label: 'Settings', icon: Settings }
          ].map(({ id, label, icon: Icon }) => (
            <button
              key={id}
              onClick={() => setActiveTab(id)}
              className={`flex items-center space-x-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors ${
                activeTab === id
                  ? 'bg-blue-100 text-blue-700'
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
          title: 'Total Revenue',
          value: formatCurrency(metrics.totalRevenue),
          change: '+12.5%',
          positive: true,
          icon: DollarSign,
          color: 'green'
        },
        {
          title: 'Active Listings',
          value: metrics.totalListings.toString(),
          change: `${metrics.activeAuctions} auctions`,
          positive: true,
          icon: Package,
          color: 'blue'
        },
        {
          title: 'Success Rate',
          value: `${metrics.successRate}%`,
          change: '+2.1%',
          positive: true,
          icon: TrendingUp,
          color: 'purple'
        },
        {
          title: 'Feedback Score',
          value: `${metrics.feedbackScore}%`,
          change: `${metrics.watchers} watchers`,
          positive: true,
          icon: Star,
          color: 'yellow'
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

  const renderListingsTable = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200">
      <div className="px-6 py-4 border-b border-gray-200">
        <div className="flex items-center justify-between">
          <h2 className="text-lg font-semibold text-gray-900">Recent Listings</h2>
          <button className="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <Plus className="w-4 h-4" />
            <span>New Listing</span>
          </button>
        </div>
      </div>
      
      <div className="overflow-x-auto">
        <table className="min-w-full divide-y divide-gray-200">
          <thead className="bg-gray-50">
            <tr>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Item
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Type
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Price
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Bids/Watchers
              </th>
              <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Time Left
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
            {listings.map((listing) => (
              <tr key={listing.id} className="hover:bg-gray-50">
                <td className="px-6 py-4">
                  <div className="flex items-center space-x-3">
                    <img 
                      src={listing.imageUrl} 
                      alt={listing.title}
                      className="w-12 h-12 rounded-lg object-cover"
                    />
                    <div>
                      <div className="text-sm font-medium text-gray-900">{listing.title}</div>
                      <div className="text-sm text-gray-500">{listing.category}</div>
                    </div>
                  </div>
                </td>
                <td className="px-6 py-4">
                  <div className="flex items-center space-x-2">
                    {getListingTypeIcon(listing.listingType)}
                    <span className="text-sm text-gray-900 capitalize">{listing.listingType}</span>
                  </div>
                </td>
                <td className="px-6 py-4">
                  <div className="text-sm font-medium text-gray-900">
                    {formatCurrency(listing.price, listing.currency)}
                  </div>
                </td>
                <td className="px-6 py-4">
                  <div className="text-sm text-gray-900">
                    {listing.listingType === 'auction' ? `${listing.bids} bids` : `${listing.watchers} watching`}
                  </div>
                </td>
                <td className="px-6 py-4">
                  <div className="text-sm text-gray-900">{listing.timeLeft}</div>
                </td>
                <td className="px-6 py-4">
                  <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(listing.status)}`}>
                    {listing.status}
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

  const renderDashboard = () => (
    <div className="space-y-6">
      {renderMetricsCards()}
      {renderListingsTable()}
    </div>
  );

  const renderSettings = () => (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <h2 className="text-lg font-semibold text-gray-900 mb-6">eBay API Configuration</h2>
      
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            App ID (Client ID)
          </label>
          <input
            type="text"
            value={config.appId}
            onChange={(e) => setConfig({...config, appId: e.target.value})}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Enter your eBay App ID"
          />
        </div>
        
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Cert ID (Client Secret)
          </label>
          <input
            type="password"
            value={config.certId}
            onChange={(e) => setConfig({...config, certId: e.target.value})}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Enter your eBay Cert ID"
          />
        </div>
        
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Dev ID
          </label>
          <input
            type="text"
            value={config.devId}
            onChange={(e) => setConfig({...config, devId: e.target.value})}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Enter your eBay Dev ID"
          />
        </div>
        
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            User Token
          </label>
          <input
            type="password"
            value={config.userToken}
            onChange={(e) => setConfig({...config, userToken: e.target.value})}
            className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Enter your eBay User Token"
          />
        </div>
      </div>
      
      <div className="mt-6 space-y-4">
        <div className="flex items-center justify-between">
          <div>
            <label className="text-sm font-medium text-gray-700">Sandbox Mode</label>
            <p className="text-sm text-gray-500">Use eBay sandbox for testing</p>
          </div>
          <button
            onClick={() => setConfig({...config, sandbox: !config.sandbox})}
            className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors ${
              config.sandbox ? 'bg-blue-600' : 'bg-gray-200'
            }`}
          >
            <span className={`inline-block h-4 w-4 transform rounded-full bg-white transition-transform ${
              config.sandbox ? 'translate-x-6' : 'translate-x-1'
            }`} />
          </button>
        </div>
        
        <div className="flex items-center justify-between">
          <div>
            <label className="text-sm font-medium text-gray-700">Auto-refresh Data</label>
            <p className="text-sm text-gray-500">Automatically refresh every 30 seconds</p>
          </div>
          <button
            onClick={() => setAutoRefresh(!autoRefresh)}
            className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors ${
              autoRefresh ? 'bg-blue-600' : 'bg-gray-200'
            }`}
          >
            <span className={`inline-block h-4 w-4 transform rounded-full bg-white transition-transform ${
              autoRefresh ? 'translate-x-6' : 'translate-x-1'
            }`} />
          </button>
        </div>
      </div>
      
      <div className="mt-6 flex justify-end space-x-3">
        <button className="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
          Test Connection
        </button>
        <button className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
          Save Settings
        </button>
      </div>
    </div>
  );

  // Main render
  return (
    <div className="min-h-screen bg-gray-50 p-6">
      {renderHeader()}
      
      {activeTab === 'dashboard' && renderDashboard()}
      {activeTab === 'listings' && renderListingsTable()}
      {activeTab === 'auctions' && renderListingsTable()}
      {activeTab === 'analytics' && renderDashboard()}
      {activeTab === 'settings' && renderSettings()}
    </div>
  );
};

export default EbayIntegration; 