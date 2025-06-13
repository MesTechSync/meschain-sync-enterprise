import React, { useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';

interface Product {
  id: string;
  name: string;
  description: string;
  category: string;
  subcategory: string;
  brand: string;
  sku: string;
  supplierPrice: number;
  suggestedPrice: number;
  minMargin: number;
  maxMargin: number;
  stock: number;
  images: string[];
  variants: ProductVariant[];
  specifications: { [key: string]: string };
  supplier: Supplier;
  marketplaces: string[];
  tags: string[];
  isActive: boolean;
  createdAt: string;
  updatedAt: string;
}

interface ProductVariant {
  id: string;
  name: string;
  type: 'color' | 'size' | 'material' | 'style';
  value: string;
  priceModifier: number;
  stockModifier: number;
  sku: string;
}

interface Supplier {
  id: string;
  name: string;
  email: string;
  phone: string;
  rating: number;
  totalProducts: number;
  isVerified: boolean;
}

interface DropshipperSettings {
  defaultMargin: number;
  preferredMarketplaces: string[];
  autoSync: boolean;
  priceRules: PriceRule[];
}

interface PriceRule {
  id: string;
  name: string;
  condition: 'category' | 'brand' | 'price_range' | 'supplier';
  value: string;
  marginPercentage: number;
  isActive: boolean;
}

interface CartItem {
  productId: string;
  variantId?: string;
  quantity: number;
  customPrice: number;
  targetMarketplaces: string[];
}

const DropshippingCatalog: React.FC = () => {
  const { t } = useTranslation();
  const [products, setProducts] = useState<Product[]>([]);
  const [filteredProducts, setFilteredProducts] = useState<Product[]>([]);
  const [cart, setCart] = useState<CartItem[]>([]);
  const [dropshipperSettings, setDropshipperSettings] = useState<DropshipperSettings>({
    defaultMargin: 25,
    preferredMarketplaces: ['Trendyol', 'N11'],
    autoSync: true,
    priceRules: []
  });

  // Filters
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('all');
  const [selectedBrand, setSelectedBrand] = useState('all');
  const [priceRange, setPriceRange] = useState({ min: 0, max: 10000 });
  const [selectedMarketplaces, setSelectedMarketplaces] = useState<string[]>([]);
  const [sortBy, setSortBy] = useState<'name' | 'price' | 'margin' | 'stock'>('name');
  const [sortOrder, setSortOrder] = useState<'asc' | 'desc'>('asc');

  // View modes
  const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid');
  const [showCart, setShowCart] = useState(false);
  const [showPriceCalculator, setShowPriceCalculator] = useState(false);

  useEffect(() => {
    // Mock data - gerçek API'larla değiştirilecek
    const mockProducts: Product[] = [
      {
        id: '1',
        name: 'iPhone 14 Pro Max 256GB',
        description: 'Apple iPhone 14 Pro Max 256GB Depolama Alanı',
        category: 'Elektronik',
        subcategory: 'Telefon',
        brand: 'Apple',
        sku: 'APL-IP14PM-256',
        supplierPrice: 25000,
        suggestedPrice: 32000,
        minMargin: 15,
        maxMargin: 35,
        stock: 45,
        images: ['/images/iphone14.jpg'],
        variants: [
          { id: '1', name: 'Renk', type: 'color', value: 'Uzay Siyahı', priceModifier: 0, stockModifier: 0, sku: 'APL-IP14PM-256-SB' },
          { id: '2', name: 'Renk', type: 'color', value: 'Altın', priceModifier: 500, stockModifier: -10, sku: 'APL-IP14PM-256-GD' }
        ],
        specifications: {
          'Ekran Boyutu': '6.7 inç',
          'Depolama': '256GB',
          'RAM': '6GB',
          'Kamera': '48MP'
        },
        supplier: {
          id: 'sup1',
          name: 'TechnoPlus Tedarik',
          email: 'info@technoplus.com',
          phone: '+90 212 555 0123',
          rating: 4.8,
          totalProducts: 1250,
          isVerified: true
        },
        marketplaces: ['Trendyol', 'N11', 'Amazon', 'Hepsiburada'],
        tags: ['premium', 'apple', 'smartphone'],
        isActive: true,
        createdAt: '2025-06-01',
        updatedAt: '2025-06-02'
      },
      {
        id: '2',
        name: 'Samsung Galaxy S24 Ultra',
        description: 'Samsung Galaxy S24 Ultra 512GB',
        category: 'Elektronik',
        subcategory: 'Telefon',
        brand: 'Samsung',
        sku: 'SAM-GS24U-512',
        supplierPrice: 22000,
        suggestedPrice: 28000,
        minMargin: 20,
        maxMargin: 40,
        stock: 32,
        images: ['/images/galaxy-s24.jpg'],
        variants: [
          { id: '3', name: 'Renk', type: 'color', value: 'Titanium Gray', priceModifier: 0, stockModifier: 0, sku: 'SAM-GS24U-512-TG' },
          { id: '4', name: 'Renk', type: 'color', value: 'Titanium Black', priceModifier: 0, stockModifier: -5, sku: 'SAM-GS24U-512-TB' }
        ],
        specifications: {
          'Ekran Boyutu': '6.8 inç',
          'Depolama': '512GB',
          'RAM': '12GB',
          'Kamera': '200MP'
        },
        supplier: {
          id: 'sup2',
          name: 'MobilTech Distribütör',
          email: 'sales@mobiltech.com',
          phone: '+90 216 444 5678',
          rating: 4.6,
          totalProducts: 890,
          isVerified: true
        },
        marketplaces: ['Trendyol', 'N11', 'Hepsiburada'],
        tags: ['premium', 'samsung', 'android'],
        isActive: true,
        createdAt: '2025-06-01',
        updatedAt: '2025-06-02'
      },
      {
        id: '3',
        name: 'Nike Air Max 270 Erkek Spor Ayakkabı',
        description: 'Nike Air Max 270 Erkek Spor Ayakkabı - Siyah/Beyaz',
        category: 'Giyim & Aksesuar',
        subcategory: 'Ayakkabı',
        brand: 'Nike',
        sku: 'NIKE-AM270-BW',
        supplierPrice: 450,
        suggestedPrice: 650,
        minMargin: 25,
        maxMargin: 50,
        stock: 120,
        images: ['/images/nike-airmax.jpg'],
        variants: [
          { id: '5', name: 'Beden', type: 'size', value: '42', priceModifier: 0, stockModifier: 0, sku: 'NIKE-AM270-BW-42' },
          { id: '6', name: 'Beden', type: 'size', value: '43', priceModifier: 0, stockModifier: -10, sku: 'NIKE-AM270-BW-43' },
          { id: '7', name: 'Beden', type: 'size', value: '44', priceModifier: 0, stockModifier: -15, sku: 'NIKE-AM270-BW-44' }
        ],
        specifications: {
          'Malzeme': 'Sentetik Deri',
          'Taban': 'Kauçuk',
          'Cinsiyet': 'Erkek',
          'Renk': 'Siyah/Beyaz'
        },
        supplier: {
          id: 'sup3',
          name: 'SportMax Wholesale',
          email: 'orders@sportmax.com',
          phone: '+90 312 333 4567',
          rating: 4.7,
          totalProducts: 2100,
          isVerified: true
        },
        marketplaces: ['Trendyol', 'N11', 'Hepsiburada'],
        tags: ['spor', 'nike', 'ayakkabı', 'erkek'],
        isActive: true,
        createdAt: '2025-06-01',
        updatedAt: '2025-06-02'
      }
    ];

    setProducts(mockProducts);
    setFilteredProducts(mockProducts);
  }, []);

  // Filter and search logic
  useEffect(() => {
    let filtered = products.filter(product => {
      const matchesSearch = product.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                           product.description.toLowerCase().includes(searchTerm.toLowerCase()) ||
                           product.brand.toLowerCase().includes(searchTerm.toLowerCase());
      
      const matchesCategory = selectedCategory === 'all' || product.category === selectedCategory;
      const matchesBrand = selectedBrand === 'all' || product.brand === selectedBrand;
      const matchesPrice = product.supplierPrice >= priceRange.min && product.supplierPrice <= priceRange.max;
      const matchesMarketplace = selectedMarketplaces.length === 0 || 
                                selectedMarketplaces.some(mp => product.marketplaces.includes(mp));

      return matchesSearch && matchesCategory && matchesBrand && matchesPrice && matchesMarketplace;
    });

    // Sort products
    filtered.sort((a, b) => {
      let aValue, bValue;
      switch (sortBy) {
        case 'price':
          aValue = a.supplierPrice;
          bValue = b.supplierPrice;
          break;
        case 'margin':
          aValue = ((a.suggestedPrice - a.supplierPrice) / a.supplierPrice) * 100;
          bValue = ((b.suggestedPrice - b.supplierPrice) / b.supplierPrice) * 100;
          break;
        case 'stock':
          aValue = a.stock;
          bValue = b.stock;
          break;
        default:
          aValue = a.name;
          bValue = b.name;
      }

      if (sortOrder === 'asc') {
        return aValue > bValue ? 1 : -1;
      } else {
        return aValue < bValue ? 1 : -1;
      }
    });

    setFilteredProducts(filtered);
  }, [products, searchTerm, selectedCategory, selectedBrand, priceRange, selectedMarketplaces, sortBy, sortOrder]);

  // Calculate profit margin
  const calculateMargin = (supplierPrice: number, sellingPrice: number): number => {
    return ((sellingPrice - supplierPrice) / supplierPrice) * 100;
  };

  // Calculate suggested selling price with margin
  const calculateSellingPrice = (supplierPrice: number, marginPercentage: number): number => {
    return supplierPrice * (1 + marginPercentage / 100);
  };

  // Add to cart
  const addToCart = (product: Product, variantId?: string, customPrice?: number) => {
    const price = customPrice || calculateSellingPrice(product.supplierPrice, dropshipperSettings.defaultMargin);
    const newItem: CartItem = {
      productId: product.id,
      variantId,
      quantity: 1,
      customPrice: price,
      targetMarketplaces: dropshipperSettings.preferredMarketplaces
    };

    setCart(prev => {
      const existingIndex = prev.findIndex(item => 
        item.productId === product.id && item.variantId === variantId
      );
      
      if (existingIndex >= 0) {
        const updated = [...prev];
        updated[existingIndex].quantity += 1;
        return updated;
      } else {
        return [...prev, newItem];
      }
    });
  };

  // Remove from cart
  const removeFromCart = (productId: string, variantId?: string) => {
    setCart(prev => prev.filter(item => 
      !(item.productId === productId && item.variantId === variantId)
    ));
  };

  // Get unique categories and brands for filters
  const categories = Array.from(new Set(products.map(p => p.category)));
  const brands = Array.from(new Set(products.map(p => p.brand)));
  const marketplaces = ['Trendyol', 'N11', 'Amazon', 'Hepsiburada', 'eBay', 'Ozon'];

  const ProductCard: React.FC<{ product: Product }> = ({ product }) => {
    const [selectedVariant, setSelectedVariant] = useState<string | undefined>();
    const [customPrice, setCustomPrice] = useState(product.suggestedPrice);
    const margin = calculateMargin(product.supplierPrice, customPrice);

    return (
      <div className="bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:shadow-lg transition-shadow">
        <div className="flex justify-between items-start mb-4">
          <div className="flex-1">
            <h3 className="font-semibold text-gray-900 mb-2">{product.name}</h3>
            <p className="text-sm text-gray-600 mb-2">{product.description}</p>
            <div className="flex items-center space-x-2 mb-2">
              <span className="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">{product.category}</span>
              <span className="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">{product.brand}</span>
              {product.supplier.isVerified && (
                <span className="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded">✓ Doğrulanmış</span>
              )}
            </div>
          </div>
          <div className="text-right">
            <div className="text-sm text-gray-500">Stok</div>
            <div className="font-bold text-gray-900">{product.stock}</div>
          </div>
        </div>

        {/* Variants */}
        {product.variants.length > 0 && (
          <div className="mb-4">
            <label className="block text-sm font-medium text-gray-700 mb-2">Varyant Seçin:</label>
            <select
              value={selectedVariant || ''}
              onChange={(e) => setSelectedVariant(e.target.value || undefined)}
              className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
            >
              <option value="">Varsayılan</option>
              {product.variants.map(variant => (
                <option key={variant.id} value={variant.id}>
                  {variant.name}: {variant.value}
                  {variant.priceModifier !== 0 && ` (+${variant.priceModifier}₺)`}
                </option>
              ))}
            </select>
          </div>
        )}

        {/* Pricing */}
        <div className="mb-4 p-3 bg-gray-50 rounded-lg">
          <div className="grid grid-cols-2 gap-4 text-sm">
            <div>
              <span className="text-gray-600">Tedarikçi Fiyatı:</span>
              <div className="font-bold text-gray-900">{product.supplierPrice.toLocaleString()}₺</div>
            </div>
            <div>
              <span className="text-gray-600">Önerilen Fiyat:</span>
              <div className="font-bold text-green-600">{product.suggestedPrice.toLocaleString()}₺</div>
            </div>
          </div>
          
          <div className="mt-3">
            <label className="block text-sm font-medium text-gray-700 mb-1">Satış Fiyatınız:</label>
            <input
              type="number"
              value={customPrice}
              onChange={(e) => setCustomPrice(Number(e.target.value))}
              className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
              min={product.supplierPrice * (1 + product.minMargin / 100)}
              max={product.supplierPrice * (1 + product.maxMargin / 100)}
            />
            <div className="flex justify-between text-xs mt-1">
              <span className={`${margin >= product.minMargin ? 'text-green-600' : 'text-red-600'}`}>
                Kar Marjı: %{margin.toFixed(1)}
              </span>
              <span className="text-gray-500">
                Kar: {(customPrice - product.supplierPrice).toLocaleString()}₺
              </span>
            </div>
          </div>
        </div>

        {/* Supplier Info */}
        <div className="mb-4 text-sm">
          <div className="flex items-center justify-between">
            <span className="text-gray-600">Tedarikçi:</span>
            <span className="font-medium">{product.supplier.name}</span>
          </div>
          <div className="flex items-center justify-between">
            <span className="text-gray-600">Değerlendirme:</span>
            <span className="text-yellow-600">★ {product.supplier.rating}</span>
          </div>
        </div>

        {/* Marketplaces */}
        <div className="mb-4">
          <div className="text-sm text-gray-600 mb-2">Uyumlu Pazaryerleri:</div>
          <div className="flex flex-wrap gap-1">
            {product.marketplaces.map(mp => (
              <span key={mp} className="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded">
                {mp}
              </span>
            ))}
          </div>
        </div>

        {/* Actions */}
        <div className="flex space-x-2">
          <button
            onClick={() => addToCart(product, selectedVariant, customPrice)}
            disabled={margin < product.minMargin}
            className={`flex-1 px-4 py-2 rounded-md text-sm font-medium transition-colors ${
              margin >= product.minMargin
                ? 'bg-blue-600 hover:bg-blue-700 text-white'
                : 'bg-gray-300 text-gray-500 cursor-not-allowed'
            }`}
          >
            Sepete Ekle
          </button>
          <button className="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
            Detay
          </button>
        </div>
      </div>
    );
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Dropshipping Kataloğu</h1>
          <p className="text-sm text-gray-500 mt-1">B2B ürün kataloğu ve kar marjı hesaplama</p>
        </div>
        <div className="flex space-x-2">
          <button
            onClick={() => setShowCart(!showCart)}
            className="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2"
          >
            <span>🛒</span>
            <span>Sepet ({cart.length})</span>
          </button>
          <button
            onClick={() => setShowPriceCalculator(!showPriceCalculator)}
            className="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2"
          >
            <span>💰</span>
            <span>Fiyat Hesaplayıcı</span>
          </button>
        </div>
      </div>

      {/* Filters */}
      <div className="bg-white rounded-lg shadow-md p-6">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Arama</label>
            <input
              type="text"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              placeholder="Ürün, marka veya açıklama ara..."
              className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
            />
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
            <select
              value={selectedCategory}
              onChange={(e) => setSelectedCategory(e.target.value)}
              className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
            >
              <option value="all">Tüm Kategoriler</option>
              {categories.map(cat => (
                <option key={cat} value={cat}>{cat}</option>
              ))}
            </select>
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Marka</label>
            <select
              value={selectedBrand}
              onChange={(e) => setSelectedBrand(e.target.value)}
              className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
            >
              <option value="all">Tüm Markalar</option>
              {brands.map(brand => (
                <option key={brand} value={brand}>{brand}</option>
              ))}
            </select>
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Sıralama</label>
            <select
              value={`${sortBy}-${sortOrder}`}
              onChange={(e) => {
                const [sort, order] = e.target.value.split('-');
                setSortBy(sort as any);
                setSortOrder(order as any);
              }}
              className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
            >
              <option value="name-asc">İsim (A-Z)</option>
              <option value="name-desc">İsim (Z-A)</option>
              <option value="price-asc">Fiyat (Düşük-Yüksek)</option>
              <option value="price-desc">Fiyat (Yüksek-Düşük)</option>
              <option value="margin-desc">Kar Marjı (Yüksek-Düşük)</option>
              <option value="stock-desc">Stok (Çok-Az)</option>
            </select>
          </div>
        </div>

        <div className="flex justify-between items-center">
          <div className="text-sm text-gray-600">
            {filteredProducts.length} ürün gösteriliyor
          </div>
          <div className="flex space-x-2">
            <button
              onClick={() => setViewMode('grid')}
              className={`px-3 py-1 rounded text-sm ${viewMode === 'grid' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'}`}
            >
              Grid
            </button>
            <button
              onClick={() => setViewMode('list')}
              className={`px-3 py-1 rounded text-sm ${viewMode === 'list' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'}`}
            >
              Liste
            </button>
          </div>
        </div>
      </div>

      {/* Products Grid */}
      <div className={`grid gap-6 ${viewMode === 'grid' ? 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3' : 'grid-cols-1'}`}>
        {filteredProducts.map(product => (
          <ProductCard key={product.id} product={product} />
        ))}
      </div>

      {/* Cart Sidebar */}
      {showCart && (
        <div className="fixed inset-y-0 right-0 w-96 bg-white shadow-xl z-50 overflow-y-auto">
          <div className="p-6">
            <div className="flex justify-between items-center mb-4">
              <h2 className="text-lg font-semibold">Sepetim</h2>
              <button
                onClick={() => setShowCart(false)}
                className="text-gray-500 hover:text-gray-700"
              >
                ✕
              </button>
            </div>
            
            {cart.length === 0 ? (
              <p className="text-gray-500 text-center py-8">Sepetiniz boş</p>
            ) : (
              <div className="space-y-4">
                {cart.map((item, index) => {
                  const product = products.find(p => p.id === item.productId);
                  if (!product) return null;
                  
                  return (
                    <div key={index} className="border border-gray-200 rounded-lg p-4">
                      <div className="flex justify-between items-start mb-2">
                        <h3 className="font-medium text-sm">{product.name}</h3>
                        <button
                          onClick={() => removeFromCart(item.productId, item.variantId)}
                          className="text-red-500 hover:text-red-700 text-sm"
                        >
                          Sil
                        </button>
                      </div>
                      <div className="text-sm text-gray-600 space-y-1">
                        <div>Adet: {item.quantity}</div>
                        <div>Fiyat: {item.customPrice.toLocaleString()}₺</div>
                        <div>Toplam: {(item.customPrice * item.quantity).toLocaleString()}₺</div>
                        <div>Pazaryerleri: {item.targetMarketplaces.join(', ')}</div>
                      </div>
                    </div>
                  );
                })}
                
                <div className="border-t pt-4">
                  <div className="text-lg font-semibold mb-4">
                    Toplam: {cart.reduce((sum, item) => sum + (item.customPrice * item.quantity), 0).toLocaleString()}₺
                  </div>
                  <button className="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-md font-medium">
                    Pazaryerlerine Aktar
                  </button>
                </div>
              </div>
            )}
          </div>
        </div>
      )}

      {/* Price Calculator Modal */}
      {showPriceCalculator && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg p-6 w-96">
            <div className="flex justify-between items-center mb-4">
              <h2 className="text-lg font-semibold">Fiyat Hesaplayıcı</h2>
              <button
                onClick={() => setShowPriceCalculator(false)}
                className="text-gray-500 hover:text-gray-700"
              >
                ✕
              </button>
            </div>
            
            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Tedarikçi Fiyatı (₺)</label>
                <input type="number" className="w-full border border-gray-300 rounded-md px-3 py-2" />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Hedef Kar Marjı (%)</label>
                <input type="number" className="w-full border border-gray-300 rounded-md px-3 py-2" />
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Pazaryeri Komisyonu (%)</label>
                <input type="number" className="w-full border border-gray-300 rounded-md px-3 py-2" />
              </div>
              
              <div className="bg-gray-50 p-4 rounded-lg">
                <div className="text-sm text-gray-600">Önerilen Satış Fiyatı:</div>
                <div className="text-2xl font-bold text-green-600">0₺</div>
              </div>
              
              <button className="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-md font-medium">
                Hesapla
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default DropshippingCatalog; 