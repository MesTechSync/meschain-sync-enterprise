import React, { useState, useEffect } from 'react';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, BarElement } from 'chart.js';
import { Line, Bar } from 'react-chartjs-2';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, BarElement);

interface Product {
  id: string;
  name: string;
  supplier: string;
  basePrice: number;
  sellingPrice: number;
  margin: number;
  stock: number;
  sales: number;
  image: string;
  marketplaces: string[];
}

interface ProfitData {
  totalProfit: number;
  monthlyProfit: number;
  profitMargin: number;
  topProducts: Product[];
}

interface OrderData {
  pending: number;
  processing: number;
  shipped: number;
  delivered: number;
  total: number;
}

const DropshipperDashboard: React.FC = () => {
  const [products, setProducts] = useState<Product[]>([]);
  const [profitData, setProfitData] = useState<ProfitData>({
    totalProfit: 0,
    monthlyProfit: 0,
    profitMargin: 0,
    topProducts: []
  });
  const [orderData, setOrderData] = useState<OrderData>({
    pending: 0,
    processing: 0,
    shipped: 0,
    delivered: 0,
    total: 0
  });
  const [selectedProducts, setSelectedProducts] = useState<string[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [showProfitCalculator, setShowProfitCalculator] = useState(false);

  useEffect(() => {
    initializeDropshipperDashboard();
  }, []);

  const initializeDropshipperDashboard = async () => {
    try {
      setIsLoading(true);
      await Promise.all([
        fetchProducts(),
        fetchProfitData(),
        fetchOrderData()
      ]);
    } catch (error) {
      console.error('Dropshipper dashboard initialization error:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const fetchProducts = async () => {
    try {
      const response = await fetch('/admin/extension/module/meschain/dropshipping/products');
      if (!response.ok) throw new Error('Products fetch failed');
      
      const data = await response.json();
      setProducts(data.products || [
        {
          id: '1',
          name: 'Wireless Bluetooth Kulaklık',
          supplier: 'TechSupplier Co.',
          basePrice: 150,
          sellingPrice: 250,
          margin: 40,
          stock: 50,
          sales: 25,
          image: '/images/headphones.jpg',
          marketplaces: ['Amazon', 'Trendyol', 'N11']
        },
        {
          id: '2',
          name: 'Akıllı Saat',
          supplier: 'SmartTech Ltd.',
          basePrice: 300,
          sellingPrice: 450,
          margin: 33.3,
          stock: 30,
          sales: 15,
          image: '/images/smartwatch.jpg',
          marketplaces: ['eBay', 'Hepsiburada']
        }
      ]);
    } catch (error) {
      console.error('Products fetch error:', error);
    }
  };

  const fetchProfitData = async () => {
    try {
      const response = await fetch('/admin/extension/module/meschain/dropshipping/profit');
      if (!response.ok) throw new Error('Profit data fetch failed');
      
      const data = await response.json();
      setProfitData(data || {
        totalProfit: 15000,
        monthlyProfit: 3500,
        profitMargin: 35.5,
        topProducts: []
      });
    } catch (error) {
      console.error('Profit data fetch error:', error);
    }
  };

  const fetchOrderData = async () => {
    try {
      const response = await fetch('/admin/extension/module/meschain/dropshipping/orders');
      if (!response.ok) throw new Error('Order data fetch failed');
      
      const data = await response.json();
      setOrderData(data || {
        pending: 12,
        processing: 8,
        shipped: 15,
        delivered: 45,
        total: 80
      });
    } catch (error) {
      console.error('Order data fetch error:', error);
    }
  };

  const calculateProfit = (basePrice: number, sellingPrice: number) => {
    const profit = sellingPrice - basePrice;
    const margin = (profit / sellingPrice) * 100;
    return { profit, margin };
  };

  const handleProductSelect = (productId: string) => {
    setSelectedProducts(prev => 
      prev.includes(productId) 
        ? prev.filter(id => id !== productId)
        : [...prev, productId]
    );
  };

  const handleBulkAction = async (action: string) => {
    if (selectedProducts.length === 0) {
      alert('Lütfen en az bir ürün seçin');
      return;
    }

    try {
      const response = await fetch('/admin/extension/module/meschain/dropshipping/bulk-action', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action,
          productIds: selectedProducts
        })
      });

      if (response.ok) {
        alert(`${action} işlemi başarıyla tamamlandı`);
        setSelectedProducts([]);
        await fetchProducts();
      }
    } catch (error) {
      console.error('Bulk action error:', error);
      alert('İşlem sırasında bir hata oluştu');
    }
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('tr-TR', {
      style: 'currency',
      currency: 'TRY'
    }).format(amount);
  };

  const orderChartData = {
    labels: ['Bekleyen', 'İşleniyor', 'Kargoda', 'Teslim Edildi'],
    datasets: [
      {
        label: 'Sipariş Sayısı',
        data: [orderData.pending, orderData.processing, orderData.shipped, orderData.delivered],
        backgroundColor: [
          'rgba(255, 206, 84, 0.8)',
          'rgba(54, 162, 235, 0.8)',
          'rgba(255, 159, 64, 0.8)',
          'rgba(75, 192, 192, 0.8)'
        ],
        borderColor: [
          'rgba(255, 206, 84, 1)',
          'rgba(54, 162, 235, 1)',
          'rgba(255, 159, 64, 1)',
          'rgba(75, 192, 192, 1)'
        ],
        borderWidth: 2
      }
    ]
  };

  const profitTrendData = {
    labels: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran'],
    datasets: [
      {
        label: 'Aylık Kar (₺)',
        data: [2500, 2800, 3200, 3000, 3500, 3800],
        backgroundColor: 'rgba(34, 197, 94, 0.1)',
        borderColor: 'rgba(34, 197, 94, 1)',
        borderWidth: 2,
        fill: true,
        tension: 0.4
      }
    ]
  };

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-green-600"></div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900 flex items-center">
          <span className="mr-3">🛒</span>
          Dropshipper Dashboard
          <span className="ml-4 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
            <span className="animate-pulse mr-1">🟢</span>
            Aktif
          </span>
        </h1>
      </div>

      {/* Profit Metrics */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div className="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-green-100 text-sm font-medium">Toplam Kar</p>
              <p className="text-2xl font-bold">{formatCurrency(profitData.totalProfit)}</p>
            </div>
            <div className="text-3xl">💰</div>
          </div>
          <div className="mt-2">
            <span className="text-green-100 text-xs">
              <span className="animate-pulse">📈</span> Artış trendi
            </span>
          </div>
        </div>

        <div className="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-blue-100 text-sm font-medium">Aylık Kar</p>
              <p className="text-2xl font-bold">{formatCurrency(profitData.monthlyProfit)}</p>
            </div>
            <div className="text-3xl">📊</div>
          </div>
          <div className="mt-2">
            <span className="text-blue-100 text-xs">Bu ay</span>
          </div>
        </div>

        <div className="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-purple-100 text-sm font-medium">Kar Marjı</p>
              <p className="text-2xl font-bold">%{profitData.profitMargin.toFixed(1)}</p>
            </div>
            <div className="text-3xl">📈</div>
          </div>
          <div className="mt-2">
            <span className="text-purple-100 text-xs">Ortalama</span>
          </div>
        </div>

        <div className="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-orange-100 text-sm font-medium">Toplam Sipariş</p>
              <p className="text-2xl font-bold">{orderData.total}</p>
            </div>
            <div className="text-3xl">📦</div>
          </div>
          <div className="mt-2">
            <span className="text-orange-100 text-xs">Bu ay</span>
          </div>
        </div>
      </div>

      {/* Charts */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div className="bg-white rounded-lg shadow-lg p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <span className="mr-2">📊</span>
            Sipariş Durumu
          </h3>
          <div className="h-64">
            <Bar 
              data={orderChartData} 
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

        <div className="bg-white rounded-lg shadow-lg p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <span className="mr-2">📈</span>
            Kar Trendi
          </h3>
          <div className="h-64">
            <Line 
              data={profitTrendData} 
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

      {/* Product Management */}
      <div className="bg-white rounded-lg shadow-lg p-6 mb-8">
        <div className="flex items-center justify-between mb-6">
          <h3 className="text-lg font-semibold text-gray-900 flex items-center">
            <span className="mr-2">🛍️</span>
            Ürün Yönetimi
          </h3>
          <div className="flex space-x-2">
            <button
              onClick={() => setShowProfitCalculator(!showProfitCalculator)}
              className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"
            >
              💰 Kar Hesaplayıcı
            </button>
            <button
              onClick={() => handleBulkAction('sync')}
              disabled={selectedProducts.length === 0}
              className="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"
            >
              🔄 Senkronize Et
            </button>
            <button
              onClick={() => handleBulkAction('list')}
              disabled={selectedProducts.length === 0}
              className="bg-purple-600 hover:bg-purple-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"
            >
              📋 Listele
            </button>
          </div>
        </div>

        {/* Profit Calculator */}
        {showProfitCalculator && (
          <div className="bg-gray-50 rounded-lg p-4 mb-6">
            <h4 className="font-medium text-gray-900 mb-3">💰 Kar Hesaplayıcı</h4>
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Alış Fiyatı</label>
                <input
                  type="number"
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="₺0"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Satış Fiyatı</label>
                <input
                  type="number"
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="₺0"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Kar</label>
                <input
                  type="text"
                  className="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100"
                  placeholder="₺0"
                  readOnly
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Kar Marjı</label>
                <input
                  type="text"
                  className="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100"
                  placeholder="%0"
                  readOnly
                />
              </div>
            </div>
          </div>
        )}

        {/* Products Table */}
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <input
                    type="checkbox"
                    onChange={(e) => {
                      if (e.target.checked) {
                        setSelectedProducts(products.map(p => p.id));
                      } else {
                        setSelectedProducts([]);
                      }
                    }}
                    className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                  />
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Ürün
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tedarikçi
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Alış/Satış
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Kar Marjı
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Stok
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Marketplaces
                </th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  İşlemler
                </th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {products.map((product) => (
                <tr key={product.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap">
                    <input
                      type="checkbox"
                      checked={selectedProducts.includes(product.id)}
                      onChange={() => handleProductSelect(product.id)}
                      className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="flex items-center">
                      <div className="flex-shrink-0 h-10 w-10">
                        <div className="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                          📦
                        </div>
                      </div>
                      <div className="ml-4">
                        <div className="text-sm font-medium text-gray-900">{product.name}</div>
                        <div className="text-sm text-gray-500">ID: {product.id}</div>
                      </div>
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {product.supplier}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <div>{formatCurrency(product.basePrice)}</div>
                    <div className="text-green-600 font-medium">{formatCurrency(product.sellingPrice)}</div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                      product.margin > 30 ? 'bg-green-100 text-green-800' :
                      product.margin > 20 ? 'bg-yellow-100 text-yellow-800' :
                      'bg-red-100 text-red-800'
                    }`}>
                      %{product.margin.toFixed(1)}
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <span className={`${product.stock < 10 ? 'text-red-600' : 'text-green-600'}`}>
                      {product.stock}
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="flex flex-wrap gap-1">
                      {product.marketplaces.map((marketplace, index) => (
                        <span
                          key={index}
                          className="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800"
                        >
                          {marketplace}
                        </span>
                      ))}
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button className="text-blue-600 hover:text-blue-900 mr-2">Düzenle</button>
                    <button className="text-green-600 hover:text-green-900 mr-2">Listele</button>
                    <button className="text-red-600 hover:text-red-900">Kaldır</button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
};

export default DropshipperDashboard; 