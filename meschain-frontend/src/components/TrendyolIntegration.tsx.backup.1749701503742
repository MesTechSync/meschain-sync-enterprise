import React, { useState, useEffect, useCallback } from 'react';
import {
  LineChart,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  PieChart,
  Pie,
  Cell
} from 'recharts';
import { ArrowPathIcon } from '@heroicons/react/24/outline';

interface TrendyolCredentials {
  supplierId: string;
  apiKey: string;
  apiSecret: string;
  testMode: boolean;
}

interface TrendyolProduct {
  id: string;
  barcode: string;
  title: string;
  description: string;
  brand: string;
  categoryId: number;
  categoryName: string;
  listPrice: number;
  salePrice: number;
  vatRate: number;
  stockQuantity: number;
  images: string[];
  attributes: TrendyolAttribute[];
  status: 'active' | 'passive' | 'rejected';
  approvalStatus: 'approved' | 'waiting' | 'rejected';
  lastUpdated: string;
  salesCount: number;
  viewCount: number;
}

interface TrendyolAttribute {
  attributeId: number;
  attributeName: string;
  attributeValue: string;
  attributeValueId: number;
}

interface TrendyolOrder {
  orderNumber: string;
  orderDate: string;
  customerFirstName: string;
  customerLastName: string;
  orderStatus: 'Created' | 'Picking' | 'Invoiced' | 'Shipped' | 'Delivered' | 'Cancelled';
  totalPrice: number;
  cargoTrackingNumber?: string;
  items: TrendyolOrderItem[];
  shippingAddress: string;
  estimatedDeliveryDate?: string;
}

interface TrendyolOrderItem {
  productId: string;
  productName: string;
  quantity: number;
  price: number;
  barcode: string;
  commission: number;
}

interface TrendyolStats {
  totalProducts: number;
  activeProducts: number;
  pendingApproval: number;
  rejectedProducts: number;
  totalOrders: number;
  monthlyRevenue: number;
  averageRating: number;
  returnRate: number;
  conversionRate: number;
  totalViews: number;
  apiCallsToday: number;
  lastSyncTime: string;
}

interface ApiStatus {
  isOnline: boolean;
  lastCheck: string;
  responseTime: number;
  errorCount: number;
  connectionQuality: 'excellent' | 'good' | 'poor' | 'offline' | 'checking';
  lastSuccessfulSync: string;
}

const TrendyolIntegration: React.FC = () => {
  const [credentials, setCredentials] = useState<TrendyolCredentials>({
    supplierId: '',
    apiKey: '',
    apiSecret: '',
    testMode: false
  });
  const [isConnected, setIsConnected] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [activeTab, setActiveTab] = useState<'setup' | 'products' | 'orders' | 'analytics'>('setup');
  const [products, setProducts] = useState<TrendyolProduct[]>([]);
  const [orders, setOrders] = useState<TrendyolOrder[]>([]);
  const [stats, setStats] = useState<TrendyolStats>({
    totalProducts: 0,
    activeProducts: 0,
    pendingApproval: 0,
    rejectedProducts: 0,
    totalOrders: 0,
    monthlyRevenue: 0,
    averageRating: 0,
    returnRate: 0,
    conversionRate: 0,
    totalViews: 0,
    apiCallsToday: 0,
    lastSyncTime: ''
  });
  const [selectedProducts, setSelectedProducts] = useState<string[]>([]);
  const [bulkAction, setBulkAction] = useState<string>('');
  const [apiStatus, setApiStatus] = useState<ApiStatus>({
    isOnline: false,
    lastCheck: '',
    responseTime: 0,
    errorCount: 0,
    connectionQuality: 'offline',
    lastSuccessfulSync: ''
  });
  const [realTimeUpdates, setRealTimeUpdates] = useState(true);
  const [lastUpdate, setLastUpdate] = useState<string>('');
  const [errorMessage, setErrorMessage] = useState<string>('');
  const [isDataFromAPI, setIsDataFromAPI] = useState(false);

  // Load saved credentials from localStorage
  const loadSavedCredentials = useCallback(() => {
    try {
      const saved = localStorage.getItem('trendyol_credentials');
      if (saved) {
        const parsedCredentials = JSON.parse(saved);
        setCredentials(parsedCredentials);
        setIsConnected(true);
        return parsedCredentials;
      }
    } catch (error) {
      console.error('Kaydedilmiş kimlik bilgileri yüklenemedi:', error);
    }
    return null;
  }, []);

  // Check API status
  const checkApiStatus = useCallback(async () => {
    const startTime = performance.now();
    
    try {
      // Enhanced API health check with timeout
      const response = await Promise.race([
        fetch('/admin/extension/module/meschain/api/trendyol/health-check', {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${credentials.apiKey}`,
            'User-Store-Id': credentials.supplierId,
          }
        }),
        new Promise<Response>((_, reject) => 
          setTimeout(() => reject(new Error('Request timeout')), 10000)
        )
      ]);

      const responseTime = performance.now() - startTime;
      
      if (response.ok) {
        const data = await response.json();
        
        if (data.success) {
          const quality = responseTime < 500 ? 'excellent' : 
                         responseTime < 1000 ? 'good' : 'poor';
          
          setApiStatus({
            isOnline: true,
            lastCheck: new Date().toISOString(),
            responseTime: Math.round(responseTime),
            errorCount: 0,
            connectionQuality: quality,
            lastSuccessfulSync: new Date().toISOString()
          });
          
          console.log(`✅ Trendyol API: ONLINE (${Math.round(responseTime)}ms - ${quality})`);
          setIsConnected(true);
          return true;
        }
      }
      
      throw new Error('API health check failed');
      
    } catch (error) {
      console.warn('⚠️ Trendyol API: OFFLINE', error);
      
      setApiStatus(prev => ({
        isOnline: false,
        lastCheck: new Date().toISOString(),
        responseTime: 0,
        errorCount: prev.errorCount + 1,
        connectionQuality: 'offline',
        lastSuccessfulSync: prev.lastSuccessfulSync
      }));
      
      setIsConnected(false);
      return false;
    }
  }, [credentials.apiKey, credentials.supplierId]);

  // Enhanced demo data with offline indicator
  const loadDemoData = useCallback(() => {
    console.log('📊 Loading enhanced demo data...');
    
    // Demo stats with offline indicator
    setStats({
      totalProducts: 234,
      activeProducts: 189,
      pendingApproval: 23,
      rejectedProducts: 22,
      totalOrders: 1247,
      monthlyRevenue: 45678.90,
      averageRating: 4.2,
      returnRate: 3.8,
      conversionRate: 2.4,
      totalViews: 15234,
      apiCallsToday: 1456,
      lastSyncTime: 'OFFLINE - Demo Data'
    });

    // Demo products
    setProducts([
      {
        id: 'demo-001',
        barcode: '8680000000001',
        title: 'Premium Bluetooth Kulaklık',
        description: 'Yüksek kalite, gürültü engelleme özellikli',
        brand: 'TechBrand',
        categoryId: 1001,
        categoryName: 'Elektronik > Ses Sistemleri',
        listPrice: 299.99,
        salePrice: 249.99,
        vatRate: 18,
        stockQuantity: 45,
        images: [],
        attributes: [],
        status: 'active',
        approvalStatus: 'approved',
        lastUpdated: '2025-06-02T10:30:00Z',
        salesCount: 156,
        viewCount: 2340
      },
      {
        id: 'demo-002',
        barcode: '8680000000002',
        title: 'Ergonomik Ofis Sandalyesi',
        description: 'Yüksek sırt desteği, ayarlanabilir',
        brand: 'OfficePlus',
        categoryId: 2001,
        categoryName: 'Mobilya > Ofis Mobilyaları',
        listPrice: 899.99,
        salePrice: 699.99,
        vatRate: 18,
        stockQuantity: 12,
        images: [],
        attributes: [],
        status: 'active',
        approvalStatus: 'approved',
        lastUpdated: '2025-06-02T09:15:00Z',
        salesCount: 89,
        viewCount: 1567
      }
    ]);

    // Demo orders
    setOrders([
      {
        orderNumber: 'TY-20250602-001',
        orderDate: '2025-06-02T08:30:00Z',
        customerFirstName: 'Ahmet',
        customerLastName: 'Yılmaz',
        orderStatus: 'Shipped',
        totalPrice: 249.99,
        cargoTrackingNumber: 'TRK123456789',
        items: [{
          productId: 'demo-001',
          productName: 'Premium Bluetooth Kulaklık',
          quantity: 1,
          price: 249.99,
          barcode: '8680000000001',
          commission: 24.99
        }],
        shippingAddress: 'İstanbul, Türkiye',
        estimatedDeliveryDate: '2025-06-04T18:00:00Z'
      }
    ]);

    setIsDataFromAPI(false);
    setLastUpdate(`Demo Data - ${new Date().toLocaleString('tr-TR')}`);
  }, []);

  // Load real data from Trendyol API
  const loadTrendyolData = useCallback(async () => {
    if (!isConnected || !credentials.apiKey) {
      console.log('🔄 Loading demo data (API offline)');
      loadDemoData();
      return;
    }

    setIsLoading(true);
    console.log('🔄 Loading REAL Trendyol data...');

    try {
      // Update API status to checking
      setApiStatus(prev => ({
        ...prev,
        isOnline: true,
        lastCheck: new Date().toISOString(),
        connectionQuality: 'checking'
      }));

      // Parallel API calls for better performance
      const [statsResponse, productsResponse, ordersResponse] = await Promise.allSettled([
        // Real Trendyol Stats API
        fetch('/admin/extension/module/meschain/api/trendyol/stats', {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${credentials.apiKey}`,
            'User-Store-Id': credentials.supplierId,
            'X-Requested-With': 'XMLHttpRequest'
          }
        }),
        
        // Real Trendyol Products API
        fetch('/admin/extension/module/meschain/api/trendyol/products', {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${credentials.apiKey}`,
            'User-Store-Id': credentials.supplierId,
            'X-Requested-With': 'XMLHttpRequest'
          }
        }),
        
        // Real Trendyol Orders API
        fetch('/admin/extension/module/meschain/api/trendyol/orders', {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${credentials.apiKey}`,
            'User-Store-Id': credentials.supplierId,
            'X-Requested-With': 'XMLHttpRequest'
          }
        })
      ]);

      let realDataLoaded = false;

      // Process Stats
      if (statsResponse.status === 'fulfilled' && statsResponse.value.ok) {
        const statsData = await statsResponse.value.json();
        if (statsData.success) {
          setStats({
            totalProducts: statsData.data.totalProducts || statsData.data.total_products || 0,
            activeProducts: statsData.data.activeProducts || statsData.data.active_products || 0,
            pendingApproval: statsData.data.pendingApproval || statsData.data.pending_approval || 0,
            rejectedProducts: statsData.data.rejectedProducts || statsData.data.rejected_products || 0,
            totalOrders: statsData.data.totalOrders || statsData.data.total_orders || 0,
            monthlyRevenue: statsData.data.monthlyRevenue || statsData.data.monthly_revenue || 0,
            averageRating: statsData.data.averageRating || statsData.data.avg_rating || 0,
            returnRate: statsData.data.returnRate || statsData.data.return_rate || 0,
            conversionRate: statsData.data.conversionRate || statsData.data.conversion_rate || 0,
            totalViews: statsData.data.totalViews || statsData.data.total_views || 0,
            apiCallsToday: statsData.data.apiCallsToday || statsData.data.api_calls_today || 0,
            lastSyncTime: new Date().toLocaleString('tr-TR')
          });
          console.log('✅ Real Trendyol stats loaded');
          realDataLoaded = true;
        }
      } else {
        console.warn('⚠️ Stats API failed, using demo data');
      }

      // Process Products
      if (productsResponse.status === 'fulfilled' && productsResponse.value.ok) {
        const productsData = await productsResponse.value.json();
        if (productsData.success && (productsData.data.content || productsData.data.products)) {
          const productList = productsData.data.content || productsData.data.products || [];
          const realProducts: TrendyolProduct[] = productList.map((item: any) => ({
            id: item.id || item.productId || String(Math.random()),
            barcode: item.barcode || item.productCode || '',
            title: item.title || item.productName || 'Ürün Adı',
            description: item.description || item.productDescription || '',
            brand: item.brand || item.brandName || 'Marka',
            categoryId: item.categoryId || item.category?.id || 0,
            categoryName: item.categoryName || item.category?.name || 'Kategori',
            listPrice: item.listPrice || item.price || 0,
            salePrice: item.salePrice || item.discountedPrice || item.listPrice || item.price || 0,
            vatRate: item.vatRate || item.taxRate || 18,
            stockQuantity: item.stockQuantity || item.quantity || 0,
            images: item.images || item.productImages || [],
            attributes: item.attributes || item.productAttributes || [],
            status: item.approved || item.isActive ? 'active' : 'passive',
            approvalStatus: item.onSale || item.approved ? 'approved' : 'waiting',
            lastUpdated: item.lastPriceChangeDate || item.lastModifiedDate || new Date().toISOString(),
            salesCount: item.salesCount || item.soldCount || 0,
            viewCount: item.viewCount || item.clickCount || 0
          }));
          
          setProducts(realProducts);
          console.log(`✅ ${realProducts.length} real Trendyol products loaded`);
          realDataLoaded = true;
        }
      } else {
        console.warn('⚠️ Products API failed, using demo data');
      }

      // Process Orders
      if (ordersResponse.status === 'fulfilled' && ordersResponse.value.ok) {
        const ordersData = await ordersResponse.value.json();
        if (ordersData.success && (ordersData.data.content || ordersData.data.orders)) {
          const orderList = ordersData.data.content || ordersData.data.orders || [];
          const realOrders: TrendyolOrder[] = orderList.map((item: any) => ({
            orderNumber: item.orderNumber || item.id || '',
            orderDate: item.orderDate || item.createdDate || new Date().toISOString(),
            customerFirstName: item.customerFirstName || item.customer?.firstName || 'Müşteri',
            customerLastName: item.customerLastName || item.customer?.lastName || 'Adı',
            orderStatus: item.status || item.orderStatus || 'Created',
            totalPrice: item.totalPrice || item.amount || 0,
            cargoTrackingNumber: item.cargoTrackingNumber || item.trackingNumber || undefined,
            items: (item.orderLines || item.items || []).map((line: any) => ({
              productId: line.productId || line.id || '',
              productName: line.productName || line.name || '',
              quantity: line.quantity || 1,
              price: line.price || line.unitPrice || 0,
              barcode: line.barcode || line.productCode || '',
              commission: line.commission || line.commissionAmount || 0
            })),
            shippingAddress: item.shipmentAddress || item.address || '',
            estimatedDeliveryDate: item.estimatedDeliveryDate || item.deliveryDate || undefined
          }));
          
          setOrders(realOrders);
          console.log(`✅ ${realOrders.length} real Trendyol orders loaded`);
          realDataLoaded = true;
        }
      } else {
        console.warn('⚠️ Orders API failed, using demo data');
      }

      if (realDataLoaded) {
        setIsDataFromAPI(true);
        setLastUpdate(new Date().toLocaleString('tr-TR'));
        setErrorMessage('');
        
        // Update API status after successful data load
        setApiStatus(prev => ({
          ...prev,
          isOnline: true,
          lastSuccessfulSync: new Date().toISOString(),
          errorCount: 0,
          connectionQuality: 'excellent',
          responseTime: Date.now() - performance.now()
        }));

        console.log('🎉 Real Trendyol data integration completed successfully!');
      } else {
        throw new Error('No real data could be loaded from any endpoint');
      }

    } catch (error: any) {
      console.error('❌ Trendyol API Error:', error);
      setIsDataFromAPI(false);
      setErrorMessage(`API Error: ${error.message}`);
      
      // Fallback to demo data
      console.log('🔄 Falling back to demo data...');
      loadDemoData();
      
      // Update API status
      setApiStatus(prev => ({
        ...prev,
        isOnline: false,
        errorCount: prev.errorCount + 1,
        connectionQuality: 'offline',
        lastCheck: new Date().toISOString()
      }));
    } finally {
      setIsLoading(false);
    }
  }, [credentials, isConnected, loadDemoData]);

  // Load saved credentials and check API status on component mount
  useEffect(() => {
    loadSavedCredentials();
    checkApiStatus();
  }, [loadSavedCredentials, checkApiStatus]);

  // Real-time updates effect
  useEffect(() => {
    if (realTimeUpdates && isConnected) {
      console.log('🔄 Starting real-time updates (30s interval)');
      
      // Initial load
      loadTrendyolData();
      
      // Set up 30-second interval for real-time updates
      const interval = setInterval(() => {
        console.log('⏰ Real-time update triggered');
        loadTrendyolData();
      }, 30000); // 30 seconds
      
      return () => {
        console.log('🛑 Stopping real-time updates');
        clearInterval(interval);
      };
    }
  }, [realTimeUpdates, isConnected, loadTrendyolData]);

  // API status monitoring
  useEffect(() => {
    if (isConnected) {
      const statusInterval = setInterval(async () => {
        try {
          const startTime = performance.now();
          const response = await fetch('/admin/extension/module/meschain/api/trendyol/health', {
            method: 'GET',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${credentials.apiKey}`,
              'User-Store-Id': credentials.supplierId,
            }
          });
          
          const responseTime = performance.now() - startTime;
          
          if (response.ok) {
            setApiStatus(prev => ({
              ...prev,
              isOnline: true,
              lastCheck: new Date().toISOString(),
              responseTime: Math.round(responseTime),
              connectionQuality: responseTime < 500 ? 'excellent' : 
                               responseTime < 1000 ? 'good' : 
                               responseTime < 2000 ? 'poor' : 'offline'
            }));
          } else {
            throw new Error(`HTTP ${response.status}`);
          }
        } catch (error) {
          setApiStatus(prev => ({
            ...prev,
            isOnline: false,
            lastCheck: new Date().toISOString(),
            connectionQuality: 'offline',
            errorCount: prev.errorCount + 1
          }));
        }
      }, 60000); // Check every minute
      
      return () => clearInterval(statusInterval);
    }
  }, [isConnected, credentials]);

  const saveCredentials = (creds: TrendyolCredentials) => {
    localStorage.setItem('trendyol_credentials', JSON.stringify(creds));
  };

  // Test API connection
  const testConnection = async () => {
    setIsLoading(true);
    console.log('🧪 Testing Trendyol API connection...');
    
    try {
      const startTime = performance.now();
      
      const response = await fetch('/admin/extension/module/meschain/api/trendyol/test-connection', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          supplierId: credentials.supplierId,
          apiKey: credentials.apiKey,
          apiSecret: credentials.apiSecret
        })
      });

      const responseTime = performance.now() - startTime;

      if (response.ok) {
        const data = await response.json();
        if (data.success) {
          const quality = responseTime < 500 ? 'excellent' : 
                         responseTime < 1000 ? 'good' : 'poor';
          
          setApiStatus({
            isOnline: true,
            lastCheck: new Date().toISOString(),
            responseTime: Math.round(responseTime),
            errorCount: 0,
            connectionQuality: quality,
            lastSuccessfulSync: new Date().toISOString()
          });
          
          alert(`✅ API bağlantısı başarılı! (${Math.round(responseTime)}ms - ${quality})`);
          await loadTrendyolData();
        } else {
          throw new Error(data.message || 'API test başarısız');
        }
      } else {
        throw new Error(`HTTP ${response.status}: API test isteği başarısız`);
      }
    } catch (error) {
      console.error('❌ API test hatası:', error);
      setApiStatus(prev => ({
        isOnline: false,
        lastCheck: new Date().toISOString(),
        responseTime: 0,
        errorCount: prev.errorCount + 1,
        connectionQuality: 'offline',
        lastSuccessfulSync: prev.lastSuccessfulSync
      }));
      alert('❌ API bağlantısı başarısız: ' + (error as Error).message);
    } finally {
      setIsLoading(false);
    }
  };

  // Update product status with real API
  const updateProductStatus = async (productId: string, status: 'active' | 'passive') => {
    setIsLoading(true);
    try {
      const response = await fetch(`/admin/extension/module/meschain/api/trendyol/products/${productId}/status`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${credentials.apiKey}`,
          'Supplier-Id': credentials.supplierId
        },
        body: JSON.stringify({ status })
      });

      if (response.ok) {
        await loadTrendyolData();
        alert('✅ Ürün durumu güncellendi');
      } else {
        throw new Error('Status update failed');
      }
    } catch (error) {
      console.error('Güncelleme hatası:', error);
      alert('❌ Güncelleme başarısız. Lütfen tekrar deneyin.');
    } finally {
      setIsLoading(false);
    }
  };

  // Bulk actions with real API
  const handleBulkAction = async () => {
    if (!bulkAction || selectedProducts.length === 0) return;

    setIsLoading(true);
    try {
      const response = await fetch('/admin/extension/module/meschain/api/trendyol/products/bulk-action', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${credentials.apiKey}`,
          'Supplier-Id': credentials.supplierId
        },
        body: JSON.stringify({
          action: bulkAction,
          productIds: selectedProducts
        })
      });

      if (response.ok) {
        await loadTrendyolData();
        setSelectedProducts([]);
        setBulkAction('');
        alert('✅ Toplu işlem tamamlandı');
      } else {
        throw new Error('Bulk action failed');
      }
    } catch (error) {
      console.error('Toplu işlem hatası:', error);
      alert('❌ Toplu işlem başarısız. Lütfen tekrar deneyin.');
    } finally {
      setIsLoading(false);
    }
  };

  // Update order status with real API
  const updateOrderStatus = async (orderNumber: string, status: string) => {
    setIsLoading(true);
    try {
      const response = await fetch(`/admin/extension/module/meschain/api/trendyol/orders/${orderNumber}/status`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${credentials.apiKey}`,
          'Supplier-Id': credentials.supplierId
        },
        body: JSON.stringify({ status })
      });

      if (response.ok) {
        await loadTrendyolData();
        alert('✅ Sipariş durumu güncellendi');
      } else {
        throw new Error('Order status update failed');
      }
    } catch (error) {
      console.error('Sipariş güncelleme hatası:', error);
      alert('❌ Sipariş güncellemesi başarısız. Lütfen tekrar deneyin.');
    } finally {
      setIsLoading(false);
    }
  };

  // Format currency
  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('tr-TR', {
      style: 'currency',
      currency: 'TRY',
      minimumFractionDigits: 2
    }).format(amount);
  };

  // Get status color
  const getStatusColor = (status: string) => {
    const colors = {
      connected: 'bg-green-100 text-green-800',
      disconnected: 'bg-red-100 text-red-800',
      error: 'bg-red-100 text-red-800',
      configuring: 'bg-yellow-100 text-yellow-800'
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
  };

  // Chart data
  const revenueData = [
    { month: 'Ocak', revenue: 85000, orders: 234 },
    { month: 'Şubat', revenue: 92000, orders: 267 },
    { month: 'Mart', revenue: 108000, orders: 298 },
    { month: 'Nisan', revenue: 125000, orders: 334 },
    { month: 'Mayıs', revenue: 118000, orders: 312 },
    { month: 'Haziran', revenue: 135000, orders: 356 }
  ];

  const productStatusData = [
    { name: 'Aktif', value: stats.activeProducts, color: '#10B981' },
    { name: 'Beklemede', value: stats.pendingApproval, color: '#F59E0B' },
    { name: 'Reddedilen', value: stats.rejectedProducts, color: '#EF4444' }
  ];

  // Enhanced connection status display
  const getConnectionStatus = () => {
    if (!isConnected || !credentials.apiKey) {
      return {
        status: 'disconnected',
        color: 'bg-red-100 text-red-800',
        icon: '❌',
        text: 'BAĞLANTI YOK'
      };
    }

    if (isLoading) {
      return {
        status: 'connecting',
        color: 'bg-yellow-100 text-yellow-800',
        icon: '🔄',
        text: 'BAĞLANIYOR...'
      };
    }

    switch (apiStatus.connectionQuality) {
      case 'excellent':
        return {
          status: 'excellent',
          color: 'bg-green-100 text-green-800',
          icon: '🟢',
          text: `ONLINE (${apiStatus.responseTime}ms)`
        };
      case 'good':
        return {
          status: 'good',
          color: 'bg-blue-100 text-blue-800',
          icon: '🔵',
          text: `ONLINE (${apiStatus.responseTime}ms)`
        };
      case 'poor':
        return {
          status: 'poor',
          color: 'bg-orange-100 text-orange-800',
          icon: '🟡',
          text: `YAVAS (${apiStatus.responseTime}ms)`
        };
      case 'offline':
      default:
        return {
          status: 'offline',
          color: 'bg-red-100 text-red-800',
          icon: '🔴',
          text: isDataFromAPI ? 'API HATASI' : 'OFF - DEMO'
        };
    }
  };

  const connectionStatus = getConnectionStatus();

  return (
    <div className="space-y-6">
      {/* Enhanced Header with Advanced Status */}
      <div className="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">🛍️ Trendyol Entegrasyonu</h1>
          <p className="text-sm text-gray-500 mt-1">Gerçek zamanlı Trendyol marketplace yönetimi</p>
        </div>
        
        {/* Enhanced Control Panel */}
        <div className="flex flex-wrap items-center gap-3">
          {/* Enhanced API Status Indicator */}
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div className="flex items-center justify-between mb-4">
              <h3 className="text-lg font-semibold text-gray-900">🔌 API Bağlantı Durumu</h3>
              <div className="flex items-center space-x-2">
                <div className={`w-3 h-3 rounded-full ${
                  apiStatus.connectionQuality === 'excellent' ? 'bg-green-500 animate-pulse' :
                  apiStatus.connectionQuality === 'good' ? 'bg-yellow-500' :
                  apiStatus.connectionQuality === 'poor' ? 'bg-orange-500' :
                  apiStatus.connectionQuality === 'checking' ? 'bg-blue-500 animate-pulse' :
                  'bg-red-500'
                }`}></div>
                <span className={`text-sm font-medium ${
                  apiStatus.isOnline ? 'text-green-600' : 'text-red-600'
                }`}>
                  {apiStatus.isOnline ? 'ONLINE' : 'OFFLINE'}
                </span>
              </div>
            </div>
            
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div className="text-center">
                <p className="text-sm text-gray-600">Bağlantı Kalitesi</p>
                <p className={`text-lg font-bold ${
                  apiStatus.connectionQuality === 'excellent' ? 'text-green-600' :
                  apiStatus.connectionQuality === 'good' ? 'text-yellow-600' :
                  apiStatus.connectionQuality === 'poor' ? 'text-orange-600' :
                  apiStatus.connectionQuality === 'checking' ? 'text-blue-600' :
                  'text-red-600'
                }`}>
                  {apiStatus.connectionQuality === 'excellent' ? 'Mükemmel' :
                   apiStatus.connectionQuality === 'good' ? 'İyi' :
                   apiStatus.connectionQuality === 'poor' ? 'Zayıf' :
                   apiStatus.connectionQuality === 'checking' ? 'Kontrol Ediliyor' :
                   'Çevrimdışı'}
                </p>
              </div>
              
              <div className="text-center">
                <p className="text-sm text-gray-600">Yanıt Süresi</p>
                <p className="text-lg font-bold text-gray-900">
                  {apiStatus.responseTime > 0 ? `${apiStatus.responseTime}ms` : '-'}
                </p>
              </div>
              
              <div className="text-center">
                <p className="text-sm text-gray-600">Hata Sayısı</p>
                <p className={`text-lg font-bold ${apiStatus.errorCount > 0 ? 'text-red-600' : 'text-green-600'}`}>
                  {apiStatus.errorCount}
                </p>
              </div>
              
              <div className="text-center">
                <p className="text-sm text-gray-600">Son Kontrol</p>
                <p className="text-sm text-gray-900">
                  {apiStatus.lastCheck ? 
                    new Date(apiStatus.lastCheck).toLocaleTimeString('tr-TR', { 
                      hour: '2-digit', 
                      minute: '2-digit' 
                    }) : '-'
                  }
                </p>
              </div>
            </div>
            
            {/* Real-time Data Indicator */}
            <div className="mt-4 pt-4 border-t border-gray-200">
              <div className="flex items-center justify-between">
                <div className="flex items-center space-x-2">
                  <div className={`w-2 h-2 rounded-full ${isDataFromAPI ? 'bg-green-500' : 'bg-orange-500'}`}></div>
                  <span className="text-sm text-gray-600">
                    {isDataFromAPI ? 'Gerçek API Verisi' : 'Demo Verisi'}
                  </span>
                </div>
                
                <div className="flex items-center space-x-2">
                  <button
                    onClick={() => setRealTimeUpdates(!realTimeUpdates)}
                    className={`flex items-center space-x-1 px-3 py-1 rounded-md text-sm font-medium transition-colors ${
                      realTimeUpdates
                        ? 'bg-green-100 text-green-800'
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                    }`}
                  >
                    <div className={`w-2 h-2 rounded-full ${realTimeUpdates ? 'bg-green-500 animate-pulse' : 'bg-gray-400'}`}></div>
                    <span>{realTimeUpdates ? 'Canlı Takip ON' : 'Canlı Takip OFF'}</span>
                  </button>
                  
                  <button
                    onClick={() => {
                      console.log('🔄 Manual refresh triggered');
                      loadTrendyolData();
                    }}
                    disabled={isLoading}
                    className="flex items-center space-x-1 px-3 py-1 bg-blue-100 text-blue-800 rounded-md text-sm font-medium hover:bg-blue-200 disabled:opacity-50"
                  >
                    <ArrowPathIcon className={`w-4 h-4 ${isLoading ? 'animate-spin' : ''}`} />
                    <span>Yenile</span>
                  </button>
                </div>
              </div>
              
              {errorMessage && (
                <div className="mt-2 p-2 bg-red-50 border border-red-200 rounded-md">
                  <p className="text-sm text-red-600">{errorMessage}</p>
                </div>
              )}
              
              <p className="text-xs text-gray-500 mt-2">
                Son Güncelleme: {lastUpdate || 'Henüz güncellenmedi'}
              </p>
            </div>
          </div>

          {/* Data Source Indicator */}
          <div className={`flex items-center px-3 py-2 rounded-lg text-sm font-medium ${
            isDataFromAPI 
              ? 'bg-blue-100 text-blue-800 border border-blue-200' 
              : 'bg-orange-100 text-orange-800 border border-orange-200'
          }`}>
            <span className="mr-2">{isDataFromAPI ? '🔗' : '📊'}</span>
            <span className="font-medium">
              {isDataFromAPI ? 'REAL API' : 'DEMO DATA'}
            </span>
          </div>

          {/* Real-time Updates Toggle */}
          <div className="flex items-center space-x-2">
            <span className="text-sm text-gray-600 hidden md:block">Otomatik</span>
            <button
              onClick={() => setRealTimeUpdates(!realTimeUpdates)}
              className={`relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 ${
                realTimeUpdates ? 'bg-blue-600' : 'bg-gray-200'
              }`}
              title="30 saniye otomatik güncelleme"
            >
              <span className={`inline-block h-4 w-4 transform rounded-full bg-white transition-transform ${
                realTimeUpdates ? 'translate-x-6' : 'translate-x-1'
              }`} />
            </button>
          </div>

          {/* Enhanced Manual Refresh Button */}
          <button
            onClick={loadTrendyolData}
            disabled={isLoading}
            className="bg-blue-500 hover:bg-blue-600 disabled:bg-gray-400 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            title="Verileri manuel olarak yenile"
          >
            <span className={isLoading ? 'animate-spin' : ''}>🔄</span>
            <span className="hidden md:block">Yenile</span>
          </button>

          {/* Test Connection Button */}
          <button
            onClick={testConnection}
            disabled={isLoading}
            className="bg-green-500 hover:bg-green-600 disabled:bg-gray-400 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
            title="API bağlantısını test et"
          >
            <span>🧪</span>
            <span className="hidden md:block">Test</span>
          </button>
        </div>
      </div>

      {/* Enhanced Status Information Panel */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          {/* API Status Details */}
          <div className="flex items-center space-x-3">
            <div className="flex-shrink-0">
              <div className={`w-3 h-3 rounded-full ${connectionStatus.status === 'excellent' ? 'bg-green-500' : connectionStatus.status === 'good' ? 'bg-blue-500' : connectionStatus.status === 'poor' ? 'bg-orange-500' : 'bg-red-500'} animate-pulse`}></div>
            </div>
            <div>
              <p className="text-sm font-medium text-gray-900">API Durumu</p>
              <p className="text-xs text-gray-500">
                {connectionStatus.status === 'disconnected' ? 'Bağlantı kurulamadı' : connectionStatus.status === 'connecting' ? 'Bağlanıyor...' : connectionStatus.status === 'offline' ? 'OFFLINE' : connectionStatus.status.toUpperCase()}
              </p>
            </div>
          </div>

          {/* Data Source */}
          <div className="flex items-center space-x-3">
            <div className="flex-shrink-0">
              <span className="text-lg">{isDataFromAPI ? '🔗' : '📊'}</span>
            </div>
            <div>
              <p className="text-sm font-medium text-gray-900">Veri Kaynağı</p>
              <p className="text-xs text-gray-500">
                {isDataFromAPI ? 'Gerçek Trendyol API' : 'Demo verileri gösteriliyor'}
              </p>
            </div>
          </div>

          {/* Last Update */}
          <div className="flex items-center space-x-3">
            <div className="flex-shrink-0">
              <span className="text-lg">⏰</span>
            </div>
            <div>
              <p className="text-sm font-medium text-gray-900">Son Güncelleme</p>
              <p className="text-xs text-gray-500">
                {lastUpdate || 'Henüz güncellenmedi'}
              </p>
            </div>
          </div>
        </div>

        {/* Advanced Stats Row */}
        {connectionStatus.status !== 'disconnected' && (
          <div className="mt-4 pt-4 border-t border-gray-200">
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
              <div>
                <span className="text-gray-500">Yanıt Süresi:</span>
                <span className="ml-2 font-medium text-gray-900">{apiStatus.responseTime}ms</span>
              </div>
              <div>
                <span className="text-gray-500">Kalite:</span>
                <span className="ml-2 font-medium text-gray-900">{apiStatus.connectionQuality.toUpperCase()}</span>
              </div>
              <div>
                <span className="text-gray-500">Hata Sayısı:</span>
                <span className="ml-2 font-medium text-gray-900">{apiStatus.errorCount}</span>
              </div>
              <div>
                <span className="text-gray-500">Son Başarılı:</span>
                <span className="ml-2 font-medium text-gray-900">
                  {apiStatus.lastSuccessfulSync ? new Date(apiStatus.lastSuccessfulSync).toLocaleTimeString('tr-TR') : 'Yok'}
                </span>
              </div>
            </div>
          </div>
        )}
      </div>

      {/* API Offline Warning */}
      {connectionStatus.status === 'disconnected' && (
        <div className="bg-red-50 border border-red-200 rounded-lg p-4">
          <div className="flex items-start">
            <div className="flex-shrink-0">
              <span className="text-red-400 text-xl">🔴</span>
            </div>
            <div className="ml-3">
              <h3 className="text-sm font-medium text-red-800">Trendyol API Bağlantısı Kesildi</h3>
              <div className="mt-2 text-sm text-red-700">
                <p>• API sunucusuna ulaşılamıyor</p>
                <p>• Demo veriler gösteriliyor</p>
                <p>• Gerçek zamanlı güncellemeler durdu</p>
                <p className="mt-2">
                  <strong>Çözüm:</strong> API bilgilerinizi kontrol edin ve "Test" butonuna tıklayın.
                </p>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Real-time Update Status */}
      {realTimeUpdates && connectionStatus.status !== 'disconnected' && (
        <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <span className="text-blue-500 text-xl animate-pulse">🔄</span>
            </div>
            <div className="ml-3">
              <p className="text-sm font-medium text-blue-900">Gerçek Zamanlı Güncelleme Aktif</p>
              <p className="text-sm text-blue-700">
                Veriler her 30 saniyede bir otomatik olarak Trendyol API'sinden güncelleniyor.
              </p>
            </div>
          </div>
        </div>
      )}

      {/* Error Message */}
      {errorMessage && (
        <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
          <div className="flex">
            <div className="flex-shrink-0">
              <span className="text-yellow-400">⚠️</span>
            </div>
            <div className="ml-3">
              <p className="text-sm text-yellow-800">{errorMessage}</p>
            </div>
          </div>
        </div>
      )}

      {/* Navigation Tabs */}
      <div className="border-b border-gray-200 mb-6">
        <nav className="-mb-px flex space-x-8">
          {[
            { id: 'setup', name: 'Kurulum', icon: '⚙️' },
            { id: 'products', name: 'Ürünler', icon: '📦' },
            { id: 'orders', name: 'Siparişler', icon: '🛒' },
            { id: 'analytics', name: 'Analitik', icon: '📊' }
          ].map((tab) => (
            <button
              key={tab.id}
              onClick={() => setActiveTab(tab.id as any)}
              className={`py-2 px-1 border-b-2 font-medium text-sm ${
                activeTab === tab.id
                  ? 'border-orange-500 text-orange-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              }`}
            >
              <span className="mr-2">{tab.icon}</span>
              {tab.name}
            </button>
          ))}
        </nav>
      </div>

      {/* Setup Tab */}
      {activeTab === 'setup' && (
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 className="text-xl font-semibold text-gray-900 mb-6">Trendyol API Ayarları</h2>
          
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Supplier ID
              </label>
              <input
                type="text"
                value={credentials.supplierId}
                onChange={(e) => setCredentials({...credentials, supplierId: e.target.value})}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
                placeholder="Trendyol Supplier ID'nizi girin"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                API Key
              </label>
              <input
                type="text"
                value={credentials.apiKey}
                onChange={(e) => setCredentials({...credentials, apiKey: e.target.value})}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
                placeholder="API Key'inizi girin"
              />
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                API Secret
              </label>
              <input
                type="password"
                value={credentials.apiSecret}
                onChange={(e) => setCredentials({...credentials, apiSecret: e.target.value})}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
                placeholder="API Secret'ınızı girin"
              />
            </div>

            <div>
              <label className="flex items-center">
                <input
                  type="checkbox"
                  checked={credentials.testMode}
                  onChange={(e) => setCredentials({...credentials, testMode: e.target.checked})}
                  className="rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                />
                <span className="ml-2 text-sm text-gray-700">Test Modu</span>
              </label>
              <p className="text-xs text-gray-500 mt-1">
                Test modunda gerçek işlemler yapılmaz
              </p>
            </div>
          </div>

          <div className="mt-6 flex space-x-4">
            <button
              onClick={testConnection}
              disabled={isLoading || !credentials.supplierId || !credentials.apiKey}
              className="bg-orange-600 text-white px-6 py-2 rounded-md hover:bg-orange-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {isLoading ? 'Test Ediliyor...' : 'Bağlantıyı Test Et'}
            </button>
            
            {isConnected && (
              <button
                onClick={() => {
                  localStorage.setItem('trendyol_credentials', JSON.stringify(credentials));
                  alert('Ayarlar kaydedildi');
                }}
                className="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700"
              >
                Ayarları Kaydet
              </button>
            )}
          </div>

          {/* API Bilgilendirme */}
          <div className="mt-8 bg-orange-50 border border-orange-200 rounded-md p-4">
            <h3 className="text-sm font-medium text-orange-800 mb-2">📋 API Bilgileri Nasıl Alınır?</h3>
            <ol className="text-sm text-orange-700 space-y-1">
              <li>1. Trendyol Pazaryeri Paneli'ne giriş yapın</li>
              <li>2. Entegrasyon → API Yönetimi bölümüne gidin</li>
              <li>3. Yeni API anahtarı oluşturun</li>
              <li>4. Supplier ID, API Key ve Secret bilgilerini kopyalayın</li>
            </ol>
          </div>
        </div>
      )}

      {/* Products Tab */}
      {activeTab === 'products' && connectionStatus.status !== 'disconnected' && (
        <div className="space-y-6">
          {/* Ana İstatistikler */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div className="bg-white overflow-hidden shadow rounded-lg">
              <div className="p-5">
                <div className="flex items-center">
                  <div className="flex-shrink-0">
                    <div className="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                      <span className="text-white text-sm">📦</span>
                    </div>
                  </div>
                  <div className="ml-5 w-0 flex-1">
                    <dl>
                      <dt className="text-sm font-medium text-gray-500 truncate">Toplam Ürün</dt>
                      <dd className="flex items-baseline">
                        <div className="text-2xl font-semibold text-gray-900">
                          {connectionStatus.status === 'disconnected' ? 'OFF' : stats.totalProducts.toLocaleString('tr-TR')}
                        </div>
                        {connectionStatus.status !== 'disconnected' && (
                          <div className="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                            <span>+{stats.activeProducts}</span>
                            <span className="ml-1 text-gray-500">aktif</span>
                          </div>
                        )}
                      </dd>
                      {!isDataFromAPI && (
                        <dd className="text-xs text-red-500 mt-1">Demo Veri</dd>
                      )}
                    </dl>
                  </div>
                </div>
              </div>
            </div>

            <div className="bg-white overflow-hidden shadow rounded-lg">
              <div className="p-5">
                <div className="flex items-center">
                  <div className="flex-shrink-0">
                    <div className="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                      <span className="text-white text-sm">📋</span>
                    </div>
                  </div>
                  <div className="ml-5 w-0 flex-1">
                    <dl>
                      <dt className="text-sm font-medium text-gray-500 truncate">Toplam Sipariş</dt>
                      <dd className="flex items-baseline">
                        <div className="text-2xl font-semibold text-gray-900">
                          {connectionStatus.status === 'disconnected' ? 'OFF' : stats.totalOrders.toLocaleString('tr-TR')}
                        </div>
                        {connectionStatus.status !== 'disconnected' && (
                          <div className="ml-2 flex items-baseline text-sm font-semibold text-blue-600">
                            <span>Bu ay</span>
                          </div>
                        )}
                      </dd>
                      {!isDataFromAPI && (
                        <dd className="text-xs text-red-500 mt-1">Demo Veri</dd>
                      )}
                    </dl>
                  </div>
                </div>
              </div>
            </div>

            <div className="bg-white overflow-hidden shadow rounded-lg">
              <div className="p-5">
                <div className="flex items-center">
                  <div className="flex-shrink-0">
                    <div className="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                      <span className="text-white text-sm">💰</span>
                    </div>
                  </div>
                  <div className="ml-5 w-0 flex-1">
                    <dl>
                      <dt className="text-sm font-medium text-gray-500 truncate">Aylık Gelir</dt>
                      <dd className="flex items-baseline">
                        <div className="text-2xl font-semibold text-gray-900">
                          {connectionStatus.status === 'disconnected' ? 'OFF' : formatCurrency(stats.monthlyRevenue)}
                        </div>
                        {connectionStatus.status !== 'disconnected' && (
                          <div className="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                            <span>+{stats.conversionRate}%</span>
                          </div>
                        )}
                      </dd>
                      {!isDataFromAPI && (
                        <dd className="text-xs text-red-500 mt-1">Demo Veri</dd>
                      )}
                    </dl>
                  </div>
                </div>
              </div>
            </div>

            <div className="bg-white overflow-hidden shadow rounded-lg">
              <div className="p-5">
                <div className="flex items-center">
                  <div className="flex-shrink-0">
                    <div className="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                      <span className="text-white text-sm">⭐</span>
                    </div>
                  </div>
                  <div className="ml-5 w-0 flex-1">
                    <dl>
                      <dt className="text-sm font-medium text-gray-500 truncate">Değerlendirme</dt>
                      <dd className="flex items-baseline">
                        <div className="text-2xl font-semibold text-gray-900">
                          {connectionStatus.status === 'disconnected' ? 'OFF' : stats.averageRating.toFixed(1)}
                        </div>
                        {connectionStatus.status !== 'disconnected' && (
                          <div className="ml-2 flex items-baseline text-sm font-semibold text-yellow-600">
                            <span>/5.0</span>
                          </div>
                        )}
                      </dd>
                      {!isDataFromAPI && (
                        <dd className="text-xs text-red-500 mt-1">Demo Veri</dd>
                      )}
                    </dl>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* Ürün İstatistikleri */}
          <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <div className="flex items-center">
                <div className="p-2 bg-blue-100 rounded-lg">
                  <span className="text-2xl">📦</span>
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-600">Toplam Ürün</p>
                  <p className="text-2xl font-bold text-gray-900">{stats.totalProducts}</p>
                </div>
              </div>
            </div>

            <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <div className="flex items-center">
                <div className="p-2 bg-green-100 rounded-lg">
                  <span className="text-2xl">✅</span>
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-600">Aktif Ürün</p>
                  <p className="text-2xl font-bold text-green-600">{stats.activeProducts}</p>
                </div>
              </div>
            </div>

            <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <div className="flex items-center">
                <div className="p-2 bg-yellow-100 rounded-lg">
                  <span className="text-2xl">⏳</span>
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-600">Onay Bekliyor</p>
                  <p className="text-2xl font-bold text-yellow-600">{stats.pendingApproval}</p>
                </div>
              </div>
            </div>

            <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <div className="flex items-center">
                <div className="p-2 bg-red-100 rounded-lg">
                  <span className="text-2xl">❌</span>
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-600">Reddedilen</p>
                  <p className="text-2xl font-bold text-red-600">{stats.rejectedProducts}</p>
                </div>
              </div>
            </div>
          </div>

          {/* Toplu İşlemler */}
          {selectedProducts.length > 0 && (
            <div className="bg-blue-50 border border-blue-200 rounded-md p-4">
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium text-blue-800">
                  {selectedProducts.length} ürün seçildi
                </span>
                <div className="flex items-center space-x-2">
                  <select
                    value={bulkAction}
                    onChange={(e) => setBulkAction(e.target.value)}
                    className="text-sm border border-blue-300 rounded px-2 py-1"
                  >
                    <option value="">İşlem Seçin</option>
                    <option value="activate">Aktif Yap</option>
                    <option value="deactivate">Pasif Yap</option>
                    <option value="update_stock">Stok Güncelle</option>
                    <option value="update_price">Fiyat Güncelle</option>
                  </select>
                  <button
                    onClick={handleBulkAction}
                    disabled={!bulkAction}
                    className="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 disabled:opacity-50"
                  >
                    Uygula
                  </button>
                </div>
              </div>
            </div>
          )}

          {/* Ürün Listesi */}
          <div className="bg-white rounded-lg shadow-sm border border-gray-200">
            <div className="px-6 py-4 border-b border-gray-200">
              <h3 className="text-lg font-medium text-gray-900">Ürün Listesi</h3>
            </div>
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
                        className="rounded border-gray-300"
                      />
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Ürün
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Kategori
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Fiyat
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Stok
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
                      <td className="px-6 py-4 whitespace-nowrap">
                        <input
                          type="checkbox"
                          checked={selectedProducts.includes(product.id)}
                          onChange={(e) => {
                            if (e.target.checked) {
                              setSelectedProducts([...selectedProducts, product.id]);
                            } else {
                              setSelectedProducts(selectedProducts.filter(id => id !== product.id));
                            }
                          }}
                          className="rounded border-gray-300"
                        />
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="flex items-center">
                          <div className="flex-shrink-0 h-10 w-10">
                            <img
                              className="h-10 w-10 rounded-md object-cover"
                              src={product.images[0] || '/placeholder-product.png'}
                              alt={product.title}
                            />
                          </div>
                          <div className="ml-4">
                            <div className="text-sm font-medium text-gray-900 truncate max-w-xs">
                              {product.title}
                            </div>
                            <div className="text-sm text-gray-500">{product.barcode}</div>
                          </div>
                        </div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {product.categoryName}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <div>
                          <span className="font-medium">{product.salePrice.toLocaleString('tr-TR')}₺</span>
                          {product.listPrice !== product.salePrice && (
                            <div className="text-xs text-gray-500 line-through">
                              {product.listPrice.toLocaleString('tr-TR')}₺
                            </div>
                          )}
                        </div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                          product.stockQuantity > 10 ? 'bg-green-100 text-green-800' :
                          product.stockQuantity > 0 ? 'bg-yellow-100 text-yellow-800' :
                          'bg-red-100 text-red-800'
                        }`}>
                          {product.stockQuantity} adet
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                          product.status === 'active' ? 'bg-green-100 text-green-800' :
                          product.status === 'passive' ? 'bg-gray-100 text-gray-800' :
                          'bg-red-100 text-red-800'
                        }`}>
                          {product.status === 'active' ? 'Aktif' :
                           product.status === 'passive' ? 'Pasif' : 'Reddedildi'}
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div className="flex space-x-2">
                          <button
                            onClick={() => updateProductStatus(product.id, 
                              product.status === 'active' ? 'passive' : 'active'
                            )}
                            className={`px-3 py-1 rounded text-xs ${
                              product.status === 'active' 
                                ? 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                : 'bg-green-100 text-green-700 hover:bg-green-200'
                            }`}
                          >
                            {product.status === 'active' ? 'Pasif Yap' : 'Aktif Yap'}
                          </button>
                          <button className="text-blue-600 hover:text-blue-900 text-xs">
                            Düzenle
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}

      {/* Orders Tab */}
      {activeTab === 'orders' && connectionStatus.status !== 'disconnected' && (
        <div className="space-y-6">
          {/* Sipariş İstatistikleri */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <div className="flex items-center">
                <div className="p-2 bg-blue-100 rounded-lg">
                  <span className="text-2xl">🛒</span>
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-600">Toplam Sipariş</p>
                  <p className="text-2xl font-bold text-gray-900">{stats.totalOrders}</p>
                </div>
              </div>
            </div>

            <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <div className="flex items-center">
                <div className="p-2 bg-green-100 rounded-lg">
                  <span className="text-2xl">💰</span>
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-600">Aylık Ciro</p>
                  <p className="text-2xl font-bold text-green-600">
                    {stats.monthlyRevenue.toLocaleString('tr-TR')}₺
                  </p>
                </div>
              </div>
            </div>

            <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <div className="flex items-center">
                <div className="p-2 bg-yellow-100 rounded-lg">
                  <span className="text-2xl">⭐</span>
                </div>
                <div className="ml-4">
                  <p className="text-sm font-medium text-gray-600">Ortalama Puan</p>
                  <p className="text-2xl font-bold text-yellow-600">{stats.averageRating}/5</p>
                </div>
              </div>
            </div>
          </div>

          {/* Sipariş Listesi */}
          <div className="bg-white rounded-lg shadow-sm border border-gray-200">
            <div className="px-6 py-4 border-b border-gray-200">
              <h3 className="text-lg font-medium text-gray-900">Son Siparişler</h3>
            </div>
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Sipariş No
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Müşteri
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Tarih
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Tutar
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
                  {orders.map((order) => (
                    <tr key={order.orderNumber} className="hover:bg-gray-50">
                      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {order.orderNumber}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {order.customerFirstName} {order.customerLastName}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {new Date(order.orderDate).toLocaleDateString('tr-TR')}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {order.totalPrice.toLocaleString('tr-TR')}₺
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                          order.orderStatus === 'Delivered' ? 'bg-green-100 text-green-800' :
                          order.orderStatus === 'Shipped' ? 'bg-blue-100 text-blue-800' :
                          order.orderStatus === 'Picking' ? 'bg-yellow-100 text-yellow-800' :
                          'bg-gray-100 text-gray-800'
                        }`}>
                          {order.orderStatus}
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div className="flex space-x-2">
                          <button className="text-blue-600 hover:text-blue-900 text-xs">
                            Detay
                          </button>
                          <button 
                            onClick={() => updateOrderStatus(order.orderNumber, 'Shipped')}
                            className="text-green-600 hover:text-green-900 text-xs"
                          >
                            Kargola
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}

      {/* Analytics Tab */}
      {activeTab === 'analytics' && connectionStatus.status !== 'disconnected' && (
        <div className="space-y-6">
          {/* Gelir Grafiği */}
          <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <h3 className="text-lg font-medium text-gray-900 mb-4">Aylık Gelir Trendi</h3>
            <ResponsiveContainer width="100%" height={300}>
              <LineChart data={revenueData}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="month" />
                <YAxis />
                <Tooltip formatter={(value) => [`${value.toLocaleString('tr-TR')}₺`, 'Gelir']} />
                <Line type="monotone" dataKey="revenue" stroke="#F97316" strokeWidth={2} />
              </LineChart>
            </ResponsiveContainer>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {/* Ürün Durum Dağılımı */}
            <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <h3 className="text-lg font-medium text-gray-900 mb-4">Ürün Durum Dağılımı</h3>
              <ResponsiveContainer width="100%" height={250}>
                <PieChart>
                  <Pie
                    data={productStatusData}
                    cx="50%"
                    cy="50%"
                    outerRadius={80}
                    fill="#8884d8"
                    dataKey="value"
                    label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                  >
                    {productStatusData.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={entry.color} />
                    ))}
                  </Pie>
                  <Tooltip />
                </PieChart>
              </ResponsiveContainer>
            </div>

            {/* Performans Metrikleri */}
            <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
              <h3 className="text-lg font-medium text-gray-900 mb-4">Performans Metrikleri</h3>
              <div className="space-y-4">
                <div className="flex justify-between items-center">
                  <span className="text-sm text-gray-600">İade Oranı</span>
                  <span className="text-sm font-medium text-gray-900">{stats.returnRate}%</span>
                </div>
                <div className="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    className="bg-red-500 h-2 rounded-full" 
                    style={{ width: `${stats.returnRate}%` }}
                  ></div>
                </div>

                <div className="flex justify-between items-center">
                  <span className="text-sm text-gray-600">Müşteri Memnuniyeti</span>
                  <span className="text-sm font-medium text-gray-900">{stats.averageRating}/5</span>
                </div>
                <div className="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    className="bg-green-500 h-2 rounded-full" 
                    style={{ width: `${(stats.averageRating / 5) * 100}%` }}
                  ></div>
                </div>

                <div className="flex justify-between items-center">
                  <span className="text-sm text-gray-600">Aktif Ürün Oranı</span>
                  <span className="text-sm font-medium text-gray-900">
                    {stats.totalProducts > 0 ? Math.round((stats.activeProducts / stats.totalProducts) * 100) : 0}%
                  </span>
                </div>
                <div className="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    className="bg-blue-500 h-2 rounded-full" 
                    style={{ 
                      width: `${stats.totalProducts > 0 ? (stats.activeProducts / stats.totalProducts) * 100 : 0}%` 
                    }}
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Bağlantı Yoksa Uyarı */}
      {connectionStatus.status === 'disconnected' && activeTab !== 'setup' && (
        <div className="bg-yellow-50 border border-yellow-200 rounded-md p-4">
          <div className="flex">
            <div className="flex-shrink-0">
              <span className="text-yellow-400 text-xl">⚠️</span>
            </div>
            <div className="ml-3">
              <h3 className="text-sm font-medium text-yellow-800">
                Trendyol Bağlantısı Gerekli
              </h3>
              <div className="mt-2 text-sm text-yellow-700">
                <p>
                  Bu sekmeyi görüntülemek için önce Trendyol API bağlantısını kurmanız gerekiyor.
                  <button 
                    onClick={() => setActiveTab('setup')}
                    className="ml-1 font-medium underline hover:text-yellow-900"
                  >
                    Kurulum sekmesine git
                  </button>
                </p>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default TrendyolIntegration; 