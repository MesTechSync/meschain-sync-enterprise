import React, { useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import { 
  ChartBarIcon, 
  ShoppingCartIcon, 
  CubeIcon, 
  CurrencyDollarIcon,
  ArrowUpIcon,
  ArrowDownIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon,
  XCircleIcon,
  ClockIcon
} from '@heroicons/react/24/outline';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, BarChart, Bar } from 'recharts';

// Mock data for charts
const salesData = [
  { name: 'Ocak', sales: 4000, orders: 240 },
  { name: 'Şubat', sales: 3000, orders: 139 },
  { name: 'Mart', sales: 2000, orders: 980 },
  { name: 'Nisan', sales: 2780, orders: 390 },
  { name: 'Mayıs', sales: 1890, orders: 480 },
  { name: 'Haziran', sales: 2390, orders: 380 },
];

const marketplaceData = [
  { name: 'Trendyol', value: 45, color: '#f97316' },
  { name: 'N11', value: 25, color: '#3b82f6' },
  { name: 'Amazon', value: 20, color: '#eab308' },
  { name: 'Hepsiburada', value: 10, color: '#ef4444' },
];

interface TrendyolApiStatus {
  connected: boolean;
  testing: boolean;
  lastTest: string | null;
  error: string | null;
  responseTime: number | null;
}

const Dashboard: React.FC = () => {
  const { t } = useTranslation();
  const [trendyolStatus, setTrendyolStatus] = useState<TrendyolApiStatus>({
    connected: false,
    testing: false,
    lastTest: null,
    error: null,
    responseTime: null
  });

  // Test Trendyol API connection
  const testTrendyolConnection = async () => {
    setTrendyolStatus(prev => ({ ...prev, testing: true, error: null }));
    
    try {
      const startTime = Date.now();
      const response = await fetch('/admin/index.php?route=extension/module/trendyol/api&action=test-connection&user_token=' + (window as any).user_token, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
        }
      });
      
      const responseTime = Date.now() - startTime;
      const data = await response.json();
      
      setTrendyolStatus({
        connected: data.success || false,
        testing: false,
        lastTest: new Date().toLocaleString('tr-TR'),
        error: data.success ? null : (data.message || 'Bağlantı başarısız'),
        responseTime: responseTime
      });
    } catch (error) {
      setTrendyolStatus({
        connected: false,
        testing: false,
        lastTest: new Date().toLocaleString('tr-TR'),
        error: 'Ağ hatası: ' + (error as Error).message,
        responseTime: null
      });
    }
  };

  // Auto-test on component mount
  useEffect(() => {
    testTrendyolConnection();
  }, []);

  const stats = [
    {
      title: t('dashboard.totalSales'),
      value: '₺124,500',
      change: '+12.5%',
      changeType: 'increase' as const,
      icon: CurrencyDollarIcon,
      color: 'text-green-600',
      bgColor: 'bg-green-100'
    },
    {
      title: t('dashboard.totalOrders'),
      value: '1,234',
      change: '+8.2%',
      changeType: 'increase' as const,
      icon: ShoppingCartIcon,
      color: 'text-blue-600',
      bgColor: 'bg-blue-100'
    },
    {
      title: t('dashboard.totalProducts'),
      value: '5,678',
      change: '+2.1%',
      changeType: 'increase' as const,
      icon: CubeIcon,
      color: 'text-purple-600',
      bgColor: 'bg-purple-100'
    },
    {
      title: t('dashboard.activeMarketplaces'),
      value: '4',
      change: '0%',
      changeType: 'neutral' as const,
      icon: ChartBarIcon,
      color: 'text-orange-600',
      bgColor: 'bg-orange-100'
    }
  ];

  return (
    <div className="px-6">
      <div className="mb-8">
        <h1 className="text-2xl font-bold text-gray-900 mb-2">{t('dashboard.title')}</h1>
        <p className="text-gray-600">{t('dashboard.welcome')}</p>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        {stats.map((stat, index) => (
          <div key={index} className="bg-white rounded-lg shadow p-6">
            <div className="flex items-center">
              <div className={`p-3 rounded-lg ${stat.bgColor}`}>
                <stat.icon className={`w-6 h-6 ${stat.color}`} />
              </div>
              <div className="ml-4">
                <p className="text-sm font-medium text-gray-600">{stat.title}</p>
                <div className="flex items-center">
                  <p className="text-2xl font-semibold text-gray-900">{stat.value}</p>
                  <div className={`ml-2 flex items-center text-sm ${
                    stat.changeType === 'increase' ? 'text-green-600' : 
                    stat.changeType === 'decrease' ? 'text-red-600' : 'text-gray-600'
                  }`}>
                    {stat.changeType === 'increase' && <ArrowUpIcon className="w-4 h-4 mr-1" />}
                    {stat.changeType === 'decrease' && <ArrowDownIcon className="w-4 h-4 mr-1" />}
                    <span>{stat.change}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* Trendyol API Test Section */}
      <div className="bg-white rounded-lg shadow p-6 mb-8">
        <div className="flex items-center justify-between mb-4">
          <h2 className="text-lg font-semibold text-gray-900">Trendyol API Durumu</h2>
          <button
            onClick={testTrendyolConnection}
            disabled={trendyolStatus.testing}
            className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {trendyolStatus.testing ? (
              <>
                <ClockIcon className="w-4 h-4 mr-2 animate-spin" />
                Test Ediliyor...
              </>
            ) : (
              'Bağlantıyı Test Et'
            )}
          </button>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div className="flex items-center">
            {trendyolStatus.connected ? (
              <CheckCircleIcon className="w-5 h-5 text-green-500 mr-2" />
            ) : (
              <XCircleIcon className="w-5 h-5 text-red-500 mr-2" />
            )}
            <span className={`text-sm font-medium ${trendyolStatus.connected ? 'text-green-700' : 'text-red-700'}`}>
              {trendyolStatus.connected ? 'Bağlı' : 'Bağlantı Yok'}
            </span>
          </div>
          
          {trendyolStatus.responseTime && (
            <div className="flex items-center">
              <ClockIcon className="w-5 h-5 text-blue-500 mr-2" />
              <span className="text-sm text-gray-600">
                Yanıt Süresi: {trendyolStatus.responseTime}ms
              </span>
            </div>
          )}
          
          {trendyolStatus.lastTest && (
            <div className="flex items-center">
              <span className="text-sm text-gray-500">
                Son Test: {trendyolStatus.lastTest}
              </span>
            </div>
          )}
        </div>
        
        {trendyolStatus.error && (
          <div className="mt-4 p-3 bg-red-50 border border-red-200 rounded-md">
            <div className="flex">
              <ExclamationTriangleIcon className="w-5 h-5 text-red-400 mr-2" />
              <span className="text-sm text-red-700">{trendyolStatus.error}</span>
            </div>
          </div>
        )}
      </div>

      {/* Charts Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        {/* Sales Chart */}
        <div className="bg-white rounded-lg shadow p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">{t('dashboard.salesChart')}</h2>
          <ResponsiveContainer width="100%" height={300}>
            <LineChart data={salesData}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="name" />
              <YAxis />
              <Tooltip />
              <Line type="monotone" dataKey="sales" stroke="#3b82f6" strokeWidth={2} />
            </LineChart>
          </ResponsiveContainer>
        </div>

        {/* Marketplace Distribution */}
        <div className="bg-white rounded-lg shadow p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">{t('dashboard.marketplaceChart')}</h2>
          <ResponsiveContainer width="100%" height={300}>
            <BarChart data={marketplaceData}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="name" />
              <YAxis />
              <Tooltip />
              <Bar dataKey="value" fill="#3b82f6" />
            </BarChart>
          </ResponsiveContainer>
        </div>
      </div>

      {/* Recent Activity */}
      <div className="bg-white rounded-lg shadow p-6">
        <h2 className="text-lg font-semibold text-gray-900 mb-4">{t('dashboard.recentActivity')}</h2>
        <div className="space-y-4">
          <div className="flex items-center justify-between py-3 border-b border-gray-200">
            <div className="flex items-center">
              <div className="w-2 h-2 bg-green-400 rounded-full mr-3"></div>
              <span className="text-sm text-gray-900">Yeni sipariş alındı - #TY123456</span>
            </div>
            <span className="text-sm text-gray-500">2 dakika önce</span>
          </div>
          <div className="flex items-center justify-between py-3 border-b border-gray-200">
            <div className="flex items-center">
              <div className="w-2 h-2 bg-blue-400 rounded-full mr-3"></div>
              <span className="text-sm text-gray-900">Ürün stoku güncellendi - SKU: ABC123</span>
            </div>
            <span className="text-sm text-gray-500">5 dakika önce</span>
          </div>
          <div className="flex items-center justify-between py-3">
            <div className="flex items-center">
              <div className="w-2 h-2 bg-orange-400 rounded-full mr-3"></div>
              <span className="text-sm text-gray-900">Trendyol senkronizasyonu tamamlandı</span>
            </div>
            <span className="text-sm text-gray-500">10 dakika önce</span>
          </div>
        </div>
      </div>
    </div>
  );
};

export default Dashboard; 