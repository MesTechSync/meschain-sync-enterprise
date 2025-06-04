import React, { useState, useEffect } from 'react';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, BarElement, ArcElement } from 'chart.js';
import { Line, Bar, Doughnut } from 'react-chartjs-2';
import apiService from '../services/api';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, BarElement, ArcElement);

interface ReportData {
  sales: {
    total: number;
    monthly: number[];
    byMarketplace: { name: string; value: number; color: string }[];
  };
  products: {
    total: number;
    topSelling: { name: string; sales: number; revenue: number }[];
    byCategory: { name: string; count: number }[];
  };
  orders: {
    total: number;
    pending: number;
    completed: number;
    cancelled: number;
    byStatus: number[];
  };
  profit: {
    total: number;
    margin: number;
    monthly: number[];
  };
}

const ReportsPage: React.FC = () => {
  const [reportData, setReportData] = useState<ReportData | null>(null);
  const [selectedPeriod, setSelectedPeriod] = useState('30days');
  const [selectedReport, setSelectedReport] = useState('overview');
  const [isLoading, setIsLoading] = useState(true);
  const [isExporting, setIsExporting] = useState(false);

  useEffect(() => {
    fetchReportData();
  }, [selectedPeriod]);

  const fetchReportData = async () => {
    try {
      setIsLoading(true);
      
      // Mock data - replace with actual API call
      const mockData: ReportData = {
        sales: {
          total: 285000,
          monthly: [45000, 52000, 48000, 61000, 58000, 65000, 72000, 68000, 75000, 82000, 78000, 85000],
          byMarketplace: [
            { name: 'Amazon', value: 125000, color: '#FF9900' },
            { name: 'Trendyol', value: 85000, color: '#F27A1A' },
            { name: 'N11', value: 45000, color: '#FF6000' },
            { name: 'eBay', value: 30000, color: '#E53238' }
          ]
        },
        products: {
          total: 2450,
          topSelling: [
            { name: 'Wireless Bluetooth Kulaklık', sales: 125, revenue: 31250 },
            { name: 'Akıllı Saat Sport Edition', sales: 85, revenue: 38250 },
            { name: 'Gaming Mouse RGB', sales: 65, revenue: 9100 },
            { name: 'Yoga Mat Premium', sales: 220, revenue: 19580 },
            { name: 'Laptop Stand Adjustable', sales: 45, revenue: 6750 }
          ],
          byCategory: [
            { name: 'Elektronik', count: 1250 },
            { name: 'Bilgisayar', count: 650 },
            { name: 'Spor', count: 420 },
            { name: 'Ev & Yaşam', count: 130 }
          ]
        },
        orders: {
          total: 540,
          pending: 45,
          completed: 425,
          cancelled: 70,
          byStatus: [45, 425, 70]
        },
        profit: {
          total: 95000,
          margin: 33.3,
          monthly: [15000, 17500, 16000, 20500, 19500, 21750, 24000, 22750, 25000, 27500, 26000, 28500]
        }
      };
      
      setReportData(mockData);
    } catch (error) {
      console.error('Report data fetch error:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleExport = async (format: 'excel' | 'csv' | 'pdf') => {
    try {
      setIsExporting(true);
      const response = await apiService.exportReport(selectedReport, format, {
        period: selectedPeriod
      });
      
      if (response.success) {
        // Create download link
        const blob = new Blob([response.data], { 
          type: format === 'excel' ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' :
                format === 'csv' ? 'text/csv' : 'application/pdf'
        });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `meschain-report-${selectedReport}-${selectedPeriod}.${format}`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        
        alert('Rapor başarıyla indirildi!');
      } else {
        alert('Export hatası: ' + response.error);
      }
    } catch (error) {
      console.error('Export error:', error);
      alert('Export sırasında bir hata oluştu');
    } finally {
      setIsExporting(false);
    }
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('tr-TR', {
      style: 'currency',
      currency: 'TRY'
    }).format(amount);
  };

  const salesChartData = {
    labels: ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'],
    datasets: [
      {
        label: 'Satış (₺)',
        data: reportData?.sales.monthly || [],
        backgroundColor: 'rgba(54, 162, 235, 0.1)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 2,
        fill: true,
        tension: 0.4
      }
    ]
  };

  const profitChartData = {
    labels: ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'],
    datasets: [
      {
        label: 'Kar (₺)',
        data: reportData?.profit.monthly || [],
        backgroundColor: 'rgba(34, 197, 94, 0.1)',
        borderColor: 'rgba(34, 197, 94, 1)',
        borderWidth: 2,
        fill: true,
        tension: 0.4
      }
    ]
  };

  const marketplaceChartData = {
    labels: reportData?.sales.byMarketplace.map(m => m.name) || [],
    datasets: [
      {
        data: reportData?.sales.byMarketplace.map(m => m.value) || [],
        backgroundColor: reportData?.sales.byMarketplace.map(m => m.color) || [],
        borderWidth: 2,
        borderColor: '#fff'
      }
    ]
  };

  const orderStatusChartData = {
    labels: ['Bekleyen', 'Tamamlanan', 'İptal Edilen'],
    datasets: [
      {
        label: 'Sipariş Sayısı',
        data: reportData?.orders.byStatus || [],
        backgroundColor: [
          'rgba(255, 206, 84, 0.8)',
          'rgba(75, 192, 192, 0.8)',
          'rgba(255, 99, 132, 0.8)'
        ],
        borderColor: [
          'rgba(255, 206, 84, 1)',
          'rgba(75, 192, 192, 1)',
          'rgba(255, 99, 132, 1)'
        ],
        borderWidth: 2
      }
    ]
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-purple-600"></div>
      </div>
    );
  }

  if (!reportData) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <div className="text-6xl mb-4">📊</div>
          <h2 className="text-2xl font-bold text-gray-900 mb-2">Rapor Verisi Bulunamadı</h2>
          <p className="text-gray-600">Lütfen daha sonra tekrar deneyin.</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900 flex items-center">
          <span className="mr-3">📈</span>
          Raporlar & Analitik
        </h1>
        <p className="mt-2 text-gray-600">
          Satış performansınızı analiz edin ve detaylı raporlar alın
        </p>
      </div>

      {/* Controls */}
      <div className="bg-white rounded-lg shadow p-6 mb-8">
        <div className="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
          <div className="flex space-x-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Rapor Türü</label>
              <select
                value={selectedReport}
                onChange={(e) => setSelectedReport(e.target.value)}
                className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
              >
                <option value="overview">Genel Bakış</option>
                <option value="sales">Satış Raporu</option>
                <option value="products">Ürün Raporu</option>
                <option value="orders">Sipariş Raporu</option>
                <option value="profit">Kar Raporu</option>
              </select>
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Zaman Aralığı</label>
              <select
                value={selectedPeriod}
                onChange={(e) => setSelectedPeriod(e.target.value)}
                className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
              >
                <option value="7days">Son 7 Gün</option>
                <option value="30days">Son 30 Gün</option>
                <option value="3months">Son 3 Ay</option>
                <option value="6months">Son 6 Ay</option>
                <option value="1year">Son 1 Yıl</option>
              </select>
            </div>
          </div>

          <div className="flex space-x-2">
            <button
              onClick={() => handleExport('excel')}
              disabled={isExporting}
              className="bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
            >
              📊 Excel
            </button>
            <button
              onClick={() => handleExport('csv')}
              disabled={isExporting}
              className="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
            >
              📄 CSV
            </button>
            <button
              onClick={() => handleExport('pdf')}
              disabled={isExporting}
              className="bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
            >
              📑 PDF
            </button>
          </div>
        </div>
      </div>

      {/* Summary Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div className="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-blue-100 text-sm font-medium">Toplam Satış</p>
              <p className="text-2xl font-bold">{formatCurrency(reportData.sales.total)}</p>
            </div>
            <div className="text-3xl">💰</div>
          </div>
          <div className="mt-2">
            <span className="text-blue-100 text-xs">
              <span className="animate-pulse">📈</span> +12.5% bu ay
            </span>
          </div>
        </div>

        <div className="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-green-100 text-sm font-medium">Toplam Kar</p>
              <p className="text-2xl font-bold">{formatCurrency(reportData.profit.total)}</p>
            </div>
            <div className="text-3xl">📊</div>
          </div>
          <div className="mt-2">
            <span className="text-green-100 text-xs">
              %{reportData.profit.margin} kar marjı
            </span>
          </div>
        </div>

        <div className="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-purple-100 text-sm font-medium">Toplam Sipariş</p>
              <p className="text-2xl font-bold">{reportData.orders.total}</p>
            </div>
            <div className="text-3xl">📦</div>
          </div>
          <div className="mt-2">
            <span className="text-purple-100 text-xs">
              {reportData.orders.pending} bekleyen
            </span>
          </div>
        </div>

        <div className="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-orange-100 text-sm font-medium">Aktif Ürünler</p>
              <p className="text-2xl font-bold">{reportData.products.total.toLocaleString()}</p>
            </div>
            <div className="text-3xl">🛍️</div>
          </div>
          <div className="mt-2">
            <span className="text-orange-100 text-xs">
              4 kategoride
            </span>
          </div>
        </div>
      </div>

      {/* Charts Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {/* Sales Trend */}
        <div className="bg-white rounded-lg shadow-lg p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <span className="mr-2">📈</span>
            Satış Trendi
          </h3>
          <div className="h-64">
            <Line 
              data={salesChartData} 
              options={{
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                  legend: {
                    display: false,
                  },
                },
                scales: {
                  y: {
                    beginAtZero: true,
                  },
                },
              }} 
            />
          </div>
        </div>

        {/* Profit Trend */}
        <div className="bg-white rounded-lg shadow-lg p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <span className="mr-2">💹</span>
            Kar Trendi
          </h3>
          <div className="h-64">
            <Line 
              data={profitChartData} 
              options={{
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                  legend: {
                    display: false,
                  },
                },
                scales: {
                  y: {
                    beginAtZero: true,
                  },
                },
              }} 
            />
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

        {/* Order Status */}
        <div className="bg-white rounded-lg shadow-lg p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <span className="mr-2">📋</span>
            Sipariş Durumu
          </h3>
          <div className="h-64">
            <Bar 
              data={orderStatusChartData} 
              options={{
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                  legend: {
                    display: false,
                  },
                },
                scales: {
                  y: {
                    beginAtZero: true,
                  },
                },
              }} 
            />
          </div>
        </div>
      </div>

      {/* Top Selling Products */}
      <div className="bg-white rounded-lg shadow-lg p-6 mb-8">
        <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
          <span className="mr-2">🏆</span>
          En Çok Satan Ürünler
        </h3>
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Sıra
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Ürün Adı
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Satış Adedi
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Toplam Gelir
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Performans
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {reportData.products.topSelling.map((product, index) => (
                <tr key={index} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="flex items-center">
                      <span className={`inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold text-white ${
                        index === 0 ? 'bg-yellow-500' :
                        index === 1 ? 'bg-gray-400' :
                        index === 2 ? 'bg-orange-600' : 'bg-blue-500'
                      }`}>
                        {index + 1}
                      </span>
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="text-sm font-medium text-gray-900">{product.name}</div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="text-sm text-gray-900">{product.sales} adet</div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="text-sm font-medium text-green-600">{formatCurrency(product.revenue)}</div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="flex items-center">
                      <div className="w-16 bg-gray-200 rounded-full h-2 mr-2">
                        <div 
                          className="bg-blue-600 h-2 rounded-full" 
                          style={{ width: `${(product.sales / Math.max(...reportData.products.topSelling.map(p => p.sales))) * 100}%` }}
                        ></div>
                      </div>
                      <span className="text-sm text-gray-500">
                        {((product.sales / Math.max(...reportData.products.topSelling.map(p => p.sales))) * 100).toFixed(0)}%
                      </span>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Category Distribution */}
      <div className="bg-white rounded-lg shadow-lg p-6">
        <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
          <span className="mr-2">📊</span>
          Kategori Dağılımı
        </h3>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          {reportData.products.byCategory.map((category, index) => (
            <div key={index} className="border rounded-lg p-4 text-center">
              <div className="text-2xl font-bold text-blue-600 mb-2">{category.count}</div>
              <div className="text-sm font-medium text-gray-900">{category.name}</div>
              <div className="mt-2">
                <div className="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    className="bg-blue-600 h-2 rounded-full" 
                    style={{ width: `${(category.count / reportData.products.total) * 100}%` }}
                  ></div>
                </div>
                <div className="text-xs text-gray-500 mt-1">
                  %{((category.count / reportData.products.total) * 100).toFixed(1)}
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};

export default ReportsPage; 