import React, { useState, useEffect } from 'react';
import apiService from '../services/api';

interface Product {
  id: string;
  name: string;
  supplier: string;
  category: string;
  basePrice: number;
  sellingPrice: number;
  margin: number;
  stock: number;
  sales: number;
  image: string;
  marketplaces: string[];
  status: 'active' | 'inactive' | 'out_of_stock';
  lastUpdated: string;
}

interface Supplier {
  id: string;
  name: string;
  country: string;
  rating: number;
  products: number;
  status: 'active' | 'inactive';
}

const DropshippingPage: React.FC = () => {
  const [products, setProducts] = useState<Product[]>([]);
  const [suppliers, setSuppliers] = useState<Supplier[]>([]);
  const [selectedProducts, setSelectedProducts] = useState<string[]>([]);
  const [currentView, setCurrentView] = useState<'products' | 'suppliers' | 'add-product'>('products');
  const [isLoading, setIsLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [filterCategory, setFilterCategory] = useState('all');
  const [filterMarketplace, setFilterMarketplace] = useState('all');
  const [showProfitCalculator, setShowProfitCalculator] = useState(false);

  useEffect(() => {
    initializeDropshipping();
  }, []);

  const initializeDropshipping = async () => {
    try {
      setIsLoading(true);
      await Promise.all([
        fetchProducts(),
        fetchSuppliers()
      ]);
    } catch (error) {
      console.error('Dropshipping initialization error:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const fetchProducts = async () => {
    try {
      // Mock data - replace with actual API call
      const mockProducts: Product[] = [
        {
          id: '1',
          name: 'Wireless Bluetooth Kulaklık Pro',
          supplier: 'TechSupplier Co.',
          category: 'Elektronik',
          basePrice: 150,
          sellingPrice: 250,
          margin: 40,
          stock: 50,
          sales: 125,
          image: '/images/headphones.jpg',
          marketplaces: ['Amazon', 'Trendyol', 'N11'],
          status: 'active',
          lastUpdated: '2025-06-02 14:30:00'
        },
        {
          id: '2',
          name: 'Akıllı Saat Sport Edition',
          supplier: 'SmartTech Ltd.',
          category: 'Elektronik',
          basePrice: 300,
          sellingPrice: 450,
          margin: 33.3,
          stock: 30,
          sales: 85,
          image: '/images/smartwatch.jpg',
          marketplaces: ['eBay', 'Hepsiburada'],
          status: 'active',
          lastUpdated: '2025-06-02 13:15:00'
        },
        {
          id: '3',
          name: 'Gaming Mouse RGB',
          supplier: 'GameGear Inc.',
          category: 'Bilgisayar',
          basePrice: 80,
          sellingPrice: 140,
          margin: 42.8,
          stock: 0,
          sales: 45,
          image: '/images/gaming-mouse.jpg',
          marketplaces: ['Amazon', 'N11'],
          status: 'out_of_stock',
          lastUpdated: '2025-06-01 16:20:00'
        },
        {
          id: '4',
          name: 'Yoga Mat Premium',
          supplier: 'FitLife Co.',
          category: 'Spor',
          basePrice: 45,
          sellingPrice: 89,
          margin: 49.4,
          stock: 75,
          sales: 220,
          image: '/images/yoga-mat.jpg',
          marketplaces: ['Trendyol', 'Hepsiburada'],
          status: 'active',
          lastUpdated: '2025-06-02 10:45:00'
        }
      ];
      
      setProducts(mockProducts);
    } catch (error) {
      console.error('Products fetch error:', error);
    }
  };

  const fetchSuppliers = async () => {
    try {
      // Mock data
      const mockSuppliers: Supplier[] = [
        {
          id: '1',
          name: 'TechSupplier Co.',
          country: 'Çin',
          rating: 4.8,
          products: 1250,
          status: 'active'
        },
        {
          id: '2',
          name: 'SmartTech Ltd.',
          country: 'Almanya',
          rating: 4.6,
          products: 890,
          status: 'active'
        },
        {
          id: '3',
          name: 'GameGear Inc.',
          country: 'ABD',
          rating: 4.9,
          products: 650,
          status: 'active'
        },
        {
          id: '4',
          name: 'FitLife Co.',
          country: 'Türkiye',
          rating: 4.7,
          products: 420,
          status: 'active'
        }
      ];
      
      setSuppliers(mockSuppliers);
    } catch (error) {
      console.error('Suppliers fetch error:', error);
    }
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
      const response = await apiService.bulkActionProducts(action, selectedProducts);
      
      if (response.success) {
        alert(`${action} işlemi başarıyla tamamlandı`);
        setSelectedProducts([]);
        await fetchProducts();
      } else {
        alert('İşlem hatası: ' + response.error);
      }
    } catch (error) {
      console.error('Bulk action error:', error);
      alert('İşlem sırasında bir hata oluştu');
    }
  };

  const filteredProducts = products.filter(product => {
    const matchesSearch = product.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                         product.supplier.toLowerCase().includes(searchTerm.toLowerCase());
    const matchesCategory = filterCategory === 'all' || product.category === filterCategory;
    const matchesMarketplace = filterMarketplace === 'all' || 
                              product.marketplaces.includes(filterMarketplace);
    
    return matchesSearch && matchesCategory && matchesMarketplace;
  });

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'active': return 'bg-green-100 text-green-800';
      case 'inactive': return 'bg-gray-100 text-gray-800';
      case 'out_of_stock': return 'bg-red-100 text-red-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'active': return '🟢';
      case 'inactive': return '⚪';
      case 'out_of_stock': return '🔴';
      default: return '⚪';
    }
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
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-green-600"></div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-gray-900 flex items-center">
          <span className="mr-3">📦</span>
          Dropshipping Yönetimi
        </h1>
        <p className="mt-2 text-gray-600">
          Ürün kataloğunuzu yönetin ve tedarikçilerinizi takip edin
        </p>
      </div>

      {/* Navigation Tabs */}
      <div className="mb-6">
        <nav className="flex space-x-8">
          <button
            onClick={() => setCurrentView('products')}
            className={`py-2 px-1 border-b-2 font-medium text-sm ${
              currentView === 'products'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            }`}
          >
            📦 Ürünler ({products.length})
          </button>
          <button
            onClick={() => setCurrentView('suppliers')}
            className={`py-2 px-1 border-b-2 font-medium text-sm ${
              currentView === 'suppliers'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            }`}
          >
            🏭 Tedarikçiler ({suppliers.length})
          </button>
          <button
            onClick={() => setCurrentView('add-product')}
            className={`py-2 px-1 border-b-2 font-medium text-sm ${
              currentView === 'add-product'
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            }`}
          >
            ➕ Ürün Ekle
          </button>
        </nav>
      </div>

      {/* Products View */}
      {currentView === 'products' && (
        <>
          {/* Filters and Actions */}
          <div className="bg-white rounded-lg shadow p-6 mb-6">
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Arama</label>
                <input
                  type="text"
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Ürün veya tedarikçi ara..."
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <select
                  value={filterCategory}
                  onChange={(e) => setFilterCategory(e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="all">Tüm Kategoriler</option>
                  <option value="Elektronik">Elektronik</option>
                  <option value="Bilgisayar">Bilgisayar</option>
                  <option value="Spor">Spor</option>
                </select>
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Marketplace</label>
                <select
                  value={filterMarketplace}
                  onChange={(e) => setFilterMarketplace(e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="all">Tüm Marketplaces</option>
                  <option value="Amazon">Amazon</option>
                  <option value="Trendyol">Trendyol</option>
                  <option value="N11">N11</option>
                  <option value="eBay">eBay</option>
                  <option value="Hepsiburada">Hepsiburada</option>
                </select>
              </div>
              <div className="flex items-end">
                <button
                  onClick={() => setShowProfitCalculator(!showProfitCalculator)}
                  className="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
                >
                  💰 Kar Hesaplayıcı
                </button>
              </div>
            </div>

            {/* Bulk Actions */}
            <div className="flex space-x-2">
              <button
                onClick={() => handleBulkAction('sync')}
                disabled={selectedProducts.length === 0}
                className="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
              >
                🔄 Senkronize Et ({selectedProducts.length})
              </button>
              <button
                onClick={() => handleBulkAction('list')}
                disabled={selectedProducts.length === 0}
                className="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
              >
                📋 Listele ({selectedProducts.length})
              </button>
              <button
                onClick={() => handleBulkAction('remove')}
                disabled={selectedProducts.length === 0}
                className="bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
              >
                🗑️ Kaldır ({selectedProducts.length})
              </button>
            </div>
          </div>

          {/* Profit Calculator */}
          {showProfitCalculator && (
            <div className="bg-white rounded-lg shadow p-6 mb-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">💰 Kar Hesaplayıcı</h3>
              <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
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
                <div className="flex items-end">
                  <button className="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    Hesapla
                  </button>
                </div>
              </div>
            </div>
          )}

          {/* Products Table */}
          <div className="bg-white rounded-lg shadow overflow-hidden">
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      <input
                        type="checkbox"
                        onChange={(e) => {
                          if (e.target.checked) {
                            setSelectedProducts(filteredProducts.map(p => p.id));
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
                      Fiyat/Kar
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Stok/Satış
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Marketplaces
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
                  {filteredProducts.map((product) => (
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
                          <div className="flex-shrink-0 h-12 w-12">
                            <div className="h-12 w-12 rounded-lg bg-gray-200 flex items-center justify-center">
                              📦
                            </div>
                          </div>
                          <div className="ml-4">
                            <div className="text-sm font-medium text-gray-900">{product.name}</div>
                            <div className="text-sm text-gray-500">{product.category}</div>
                          </div>
                        </div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {product.supplier}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <div>{formatCurrency(product.basePrice)}</div>
                        <div className="text-green-600 font-medium">{formatCurrency(product.sellingPrice)}</div>
                        <div className="text-xs text-gray-500">%{product.margin.toFixed(1)} kar</div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <div className={`${product.stock < 10 ? 'text-red-600' : 'text-green-600'}`}>
                          {product.stock} stok
                        </div>
                        <div className="text-blue-600">{product.sales} satış</div>
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
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(product.status)}`}>
                          {getStatusIcon(product.status)} {product.status}
                        </span>
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
        </>
      )}

      {/* Suppliers View */}
      {currentView === 'suppliers' && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {suppliers.map((supplier) => (
            <div key={supplier.id} className="bg-white rounded-lg shadow-lg p-6">
              <div className="flex items-center justify-between mb-4">
                <h3 className="text-lg font-semibold text-gray-900">{supplier.name}</h3>
                <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(supplier.status)}`}>
                  {getStatusIcon(supplier.status)} {supplier.status}
                </span>
              </div>
              
              <div className="space-y-2 mb-4">
                <div className="flex justify-between">
                  <span className="text-sm text-gray-500">Ülke:</span>
                  <span className="text-sm font-medium">{supplier.country}</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-500">Değerlendirme:</span>
                  <span className="text-sm font-medium">⭐ {supplier.rating}/5</span>
                </div>
                <div className="flex justify-between">
                  <span className="text-sm text-gray-500">Ürün Sayısı:</span>
                  <span className="text-sm font-medium">{supplier.products.toLocaleString()}</span>
                </div>
              </div>
              
              <div className="flex space-x-2">
                <button className="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-sm font-medium transition-colors">
                  Ürünleri Gör
                </button>
                <button className="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded-md text-sm font-medium transition-colors">
                  İletişim
                </button>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Add Product View */}
      {currentView === 'add-product' && (
        <div className="bg-white rounded-lg shadow p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-6">Yeni Ürün Ekle</h3>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Ürün Adı</label>
              <input
                type="text"
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Ürün adını girin"
              />
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
              <select className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Kategori seçin</option>
                <option value="Elektronik">Elektronik</option>
                <option value="Bilgisayar">Bilgisayar</option>
                <option value="Spor">Spor</option>
              </select>
            </div>
            
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Tedarikçi</label>
              <select className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tedarikçi seçin</option>
                {suppliers.map(supplier => (
                  <option key={supplier.id} value={supplier.id}>{supplier.name}</option>
                ))}
              </select>
            </div>
            
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
              <label className="block text-sm font-medium text-gray-700 mb-1">Stok Miktarı</label>
              <input
                type="number"
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="0"
              />
            </div>
          </div>
          
          <div className="mt-6">
            <label className="block text-sm font-medium text-gray-700 mb-1">Ürün Açıklaması</label>
            <textarea
              rows={4}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Ürün açıklamasını girin"
            ></textarea>
          </div>
          
          <div className="mt-6 flex justify-end space-x-2">
            <button
              onClick={() => setCurrentView('products')}
              className="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors"
            >
              İptal
            </button>
            <button className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
              Ürün Ekle
            </button>
          </div>
        </div>
      )}
    </div>
  );
};

export default DropshippingPage; 