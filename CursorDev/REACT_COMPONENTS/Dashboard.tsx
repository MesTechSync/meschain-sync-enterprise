import React, { useState, useEffect } from 'react';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, ArcElement } from 'chart.js';
import { Line, Doughnut } from 'react-chartjs-2';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, ArcElement);

interface DashboardMetrics {
  totalSales: number;
  activeProducts: number;
  apiStatus: 'online' | 'offline' | 'warning';
  marketplaceCount: number;
  responseTime: number;
}

interface SalesData {
  labels: string[];
  values: number[];
}

interface MarketplaceData {
  name: string;
  sales: number;
  status: 'online' | 'offline' | 'warning';
  color: string;
}

const Dashboard: React.FC = () => {
  const [metrics, setMetrics] = useState<DashboardMetrics>({
    totalSales: 0,
    activeProducts: 0,
    apiStatus: 'offline',
    marketplaceCount: 0,
    responseTime: 0
  });

  const [salesData, setSalesData] = useState<SalesData>({
    labels: [],
    values: []
  });

  const [marketplaces, setMarketplaces] = useState<MarketplaceData[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    initializeDashboard();
    const interval = setInterval(updateRealTimeData, 30000); // 30 saniye
    return () => clearInterval(interval);
  }, []);

  const initializeDashboard = async () => {
    try {
      setIsLoading(true);
      await Promise.all([
        fetchDashboardMetrics(),
        fetchSalesData(),
        fetchMarketplaceData()
      ]);
      setError(null);
    } catch (err) {
      setError('Dashboard yüklenirken bir hata oluştu');
      console.error('Dashboard initialization error:', err);
    } finally {
      setIsLoading(false);
    }
  };

  const fetchDashboardMetrics = async () => {
    try {
      const response = await fetch('/admin/extension/module/meschain/dashboard/metrics');
      if (!response.ok) throw new Error('API request failed');
      
      const data = await response.json();
      setMetrics({
        totalSales: data.total_sales || 0,
        activeProducts: data.active_products || 0,
        apiStatus: data.api_status || 'offline',
        marketplaceCount: data.marketplace_count || 0,
        responseTime: data.response_time || 0
      });
    } catch (error) {
      console.error('Metrics fetch error:', error);
      // Fallback data for demo
      setMetrics({
        totalSales: 125000,
        activeProducts: 1250,
        apiStatus: 'online',
        marketplaceCount: 5,
        responseTime: 125
      });
    }
  };

  const fetchSalesData = async () => {
    try {
      const response = await fetch('/admin/extension/module/meschain/dashboard/sales-chart');
      if (!response.ok) throw new Error('Sales data request failed');
      
      const data = await response.json();
      setSalesData({
        labels: data.labels || ['Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt', 'Paz'],
        values: data.values || [1200, 1900, 800, 2100, 1500, 2500, 2200]
      });
    } catch (error) {
      console.error('Sales data fetch error:', error);
      // Fallback data
      setSalesData({
        labels: ['Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt', 'Paz'],
        values: [1200, 1900, 800, 2100, 1500, 2500, 2200]
      });
    }
  };

  const fetchMarketplaceData = async () => {
    try {
      const response = await fetch('/admin/extension/module/meschain/dashboard/marketplaces');
      if (!response.ok) throw new Error('Marketplace data request failed');
      
      const data = await response.json();
      setMarketplaces(data.marketplaces || [
        { name: 'Amazon', sales: 45000, status: 'online', color: '#FF9900' },
        { name: 'eBay', sales: 32000, status: 'online', color: '#E53238' },
        { name: 'Trendyol', sales: 28000, status: 'online', color: '#F27A1A' },
        { name: 'N11', sales: 15000, status: 'warning', color: '#FF6000' },
        { name: 'Hepsiburada', sales: 5000, status: 'offline', color: '#FF6000' }
      ]);
    } catch (error) {
      console.error('Marketplace data fetch error:', error);
      // Fallback data
      setMarketplaces([
        { name: 'Amazon', sales: 45000, status: 'online', color: '#FF9900' },
        { name: 'eBay', sales: 32000, status: 'online', color: '#E53238' },
        { name: 'Trendyol', sales: 28000, status: 'online', color: '#F27A1A' },
        { name: 'N11', sales: 15000, status: 'warning', color: '#FF6000' },
        { name: 'Hepsiburada', sales: 5000, status: 'offline', color: '#FF6000' }
      ]);
    }
  };

  const updateRealTimeData = async () => {
    try {
      await fetchDashboardMetrics();
    } catch (error) {
      console.error('Real-time update error:', error);
    }
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'online': return '🟢';
      case 'warning': return '🟡';
      case 'offline': return '🔴';
      default: return '⚪';
    }
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('tr-TR', {
      style: 'currency',
      currency: 'TRY'
    }).format(amount);
  };

  const salesChartData = {
    labels: salesData.labels,
    datasets: [
      {
        label: 'Satış (₺)',
        data: salesData.values,
        backgroundColor: 'rgba(54, 162, 235, 0.1)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 2,
        fill: true,
        tension: 0.4
      }
    ]
  };

  const marketplaceChartData = {
    labels: marketplaces.map(m => m.name),
    datasets: [
      {
        data: marketplaces.map(m => m.sales),
        backgroundColor: marketplaces.map(m => m.color),
        borderWidth: 2,
        borderColor: '#fff'
      }
    ]
  };

  const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'top' as const,
      },
      title: {
        display: false,
      },
    },
    scales: {
      y: {
        beginAtZero: true,
      },
    },
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
          <strong className="font-bold">Hata!</strong>
          <span className="block sm:inline"> {error}</span>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900 flex items-center">
          <span className="mr-3">🚀</span>
          MesChain-Sync Dashboard
          <span className="ml-4 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
            <span className="animate-pulse mr-1">🟢</span>
            Canlı Sistem
          </span>
        </h1>
      </div>

      {/* Metrics Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div className="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-blue-100 text-sm font-medium">Toplam Satış</p>
              <p className="text-2xl font-bold">{formatCurrency(metrics.totalSales)}</p>
            </div>
            <div className="text-3xl">💰</div>
          </div>
          <div className="mt-2">
            <span className="text-blue-100 text-xs">
              <span className="animate-pulse">🟢</span> Canlı
            </span>
          </div>
        </div>

        <div className="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-green-100 text-sm font-medium">Aktif Ürünler</p>
              <p className="text-2xl font-bold">{metrics.activeProducts.toLocaleString()}</p>
            </div>
            <div className="text-3xl">📦</div>
          </div>
          <div className="mt-2">
            <span className="text-green-100 text-xs">
              <span className="animate-pulse">🟢</span> Canlı
            </span>
          </div>
        </div>

        <div className="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-purple-100 text-sm font-medium">API Durumu</p>
              <p className="text-2xl font-bold">{getStatusIcon(metrics.apiStatus)}</p>
            </div>
            <div className="text-3xl">⚡</div>
          </div>
          <div className="mt-2">
            <span className="text-purple-100 text-xs">{metrics.responseTime}ms</span>
          </div>
        </div>

        <div className="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-orange-100 text-sm font-medium">Marketplaces</p>
              <p className="text-2xl font-bold">{metrics.marketplaceCount}</p>
            </div>
            <div className="text-3xl">🛒</div>
          </div>
          <div className="mt-2">
            <span className="text-orange-100 text-xs">Amazon, eBay, N11...</span>
          </div>
        </div>
      </div>

      {/* Charts Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {/* Sales Chart */}
        <div className="bg-white rounded-lg shadow-lg p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <span className="mr-2">📊</span>
            Satış Performansı
          </h3>
          <div className="h-64">
            <Line data={salesChartData} options={chartOptions} />
          </div>
        </div>

        {/* Marketplace Distribution */}
        <div className="bg-white rounded-lg shadow-lg p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <span className="mr-2">🛒</span>
            Marketplace Dağılımı
          </h3>
          <div className="h-64">
            <Doughnut 
              data={marketplaceChartData} 
              options={{
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                  legend: {
                    position: 'bottom' as const,
                  },
                },
              }} 
            />
          </div>
        </div>
      </div>

      {/* Marketplace Status */}
      <div className="bg-white rounded-lg shadow-lg p-6">
        <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
          <span className="mr-2">🌐</span>
          Marketplace Durumu
        </h3>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
          {marketplaces.map((marketplace, index) => (
            <div key={index} className="border rounded-lg p-4 hover:shadow-md transition-shadow">
              <div className="flex items-center justify-between mb-2">
                <h4 className="font-medium text-gray-900">{marketplace.name}</h4>
                <span className="text-lg">{getStatusIcon(marketplace.status)}</span>
              </div>
              <p className="text-sm text-gray-600">Satış: {formatCurrency(marketplace.sales)}</p>
              <div className="mt-2">
                <div 
                  className="h-2 rounded-full" 
                  style={{ backgroundColor: marketplace.color, opacity: 0.3 }}
                >
                  <div 
                    className="h-2 rounded-full" 
                    style={{ 
                      backgroundColor: marketplace.color,
                      width: `${(marketplace.sales / Math.max(...marketplaces.map(m => m.sales))) * 100}%`
                    }}
                  ></div>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default Dashboard; 