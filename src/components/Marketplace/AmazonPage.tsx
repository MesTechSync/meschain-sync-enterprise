import React, { useState, useEffect } from 'react';
import {
  Box,
  Grid,
  Card,
  CardContent,
  Typography,
  Button,
  Chip,
  Avatar,
  IconButton,
  Tooltip,
  Alert,
  CircularProgress,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Switch,
  FormControlLabel,
  LinearProgress,
} from '@mui/material';
import {
  Store,
  Sync,
  CheckCircle,
  Error,
  Warning,
  Settings,
  Refresh,
  Upload,
  Download,
  TrendingUp,
  ShoppingCart,
  Inventory,
  AttachMoney,
  Star,
  LocalShipping,
  Security,
  Language,
} from '@mui/icons-material';

interface AmazonStats {
  totalProducts: number;
  activeProducts: number;
  totalOrders: number;
  pendingOrders: number;
  totalRevenue: number;
  monthlyRevenue: number;
  syncStatus: 'connected' | 'syncing' | 'error' | 'disconnected';
  lastSync: Date;
  fbaProducts: number;
  fbmProducts: number;
  averageRating: number;
  totalReviews: number;
}

interface AmazonProduct {
  id: string;
  asin: string;
  title: string;
  sku: string;
  price: number;
  stock: number;
  status: 'active' | 'inactive' | 'suppressed' | 'incomplete';
  category: string;
  fulfillment: 'FBA' | 'FBM';
  rating: number;
  reviews: number;
  lastUpdate: Date;
  marketplace: 'US' | 'UK' | 'DE' | 'FR' | 'IT' | 'ES' | 'TR';
}

const AmazonPage: React.FC = () => {
  const [stats, setStats] = useState<AmazonStats>({
    totalProducts: 0,
    activeProducts: 0,
    totalOrders: 0,
    pendingOrders: 0,
    totalRevenue: 0,
    monthlyRevenue: 0,
    syncStatus: 'connected',
    lastSync: new Date(),
    fbaProducts: 0,
    fbmProducts: 0,
    averageRating: 0,
    totalReviews: 0,
  });

  const [products, setProducts] = useState<AmazonProduct[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isSyncing, setIsSyncing] = useState(false);
  const [autoSync, setAutoSync] = useState(true);

  useEffect(() => {
    const fetchAmazonData = async () => {
      setIsLoading(true);
      
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 3000));
      
      setStats({
        totalProducts: 2156,
        activeProducts: 1987,
        totalOrders: 1234,
        pendingOrders: 45,
        totalRevenue: 456700,
        monthlyRevenue: 89200,
        syncStatus: 'connected',
        lastSync: new Date(),
        fbaProducts: 1456,
        fbmProducts: 531,
        averageRating: 4.3,
        totalReviews: 8945,
      });

      setProducts([
        {
          id: 'AMZ001',
          asin: 'B08N5WRWNW',
          title: 'Echo Dot (4th Gen) Smart Speaker with Alexa',
          sku: 'ECHO-DOT-4G-BLK',
          price: 49.99,
          stock: 156,
          status: 'active',
          category: 'Electronics > Smart Home',
          fulfillment: 'FBA',
          rating: 4.5,
          reviews: 2341,
          lastUpdate: new Date(),
          marketplace: 'US',
        },
        {
          id: 'AMZ002',
          asin: 'B07XJ8C8F5',
          title: 'Fire TV Stick 4K Max Streaming Device',
          sku: 'FIRE-TV-4K-MAX',
          price: 54.99,
          stock: 89,
          status: 'active',
          category: 'Electronics > Streaming Media',
          fulfillment: 'FBA',
          rating: 4.4,
          reviews: 1876,
          lastUpdate: new Date(),
          marketplace: 'US',
        },
        {
          id: 'AMZ003',
          asin: 'B08GYKNCCP',
          title: 'Nintendo Switch OLED Model Console',
          sku: 'NSW-OLED-WHT',
          price: 349.99,
          stock: 23,
          status: 'active',
          category: 'Video Games > Consoles',
          fulfillment: 'FBM',
          rating: 4.7,
          reviews: 3456,
          lastUpdate: new Date(),
          marketplace: 'US',
        },
        {
          id: 'AMZ004',
          asin: 'B09JQMJSXY',
          title: 'Apple AirPods Pro (2nd Generation)',
          sku: 'AIRPODS-PRO-2G',
          price: 249.99,
          stock: 0,
          status: 'suppressed',
          category: 'Electronics > Headphones',
          fulfillment: 'FBA',
          rating: 4.6,
          reviews: 4567,
          lastUpdate: new Date(),
          marketplace: 'US',
        },
        {
          id: 'AMZ005',
          asin: 'B0B7BP6CJN',
          title: 'Samsung 65" Neo QLED 4K Smart TV',
          sku: 'SAM-65-QLED-4K',
          price: 1299.99,
          stock: 12,
          status: 'incomplete',
          category: 'Electronics > Television',
          fulfillment: 'FBM',
          rating: 4.2,
          reviews: 892,
          lastUpdate: new Date(),
          marketplace: 'US',
        },
      ]);

      setIsLoading(false);
    };

    fetchAmazonData();
  }, []);

  const handleSync = async () => {
    setIsSyncing(true);
    await new Promise(resolve => setTimeout(resolve, 5000));
    setStats(prev => ({ ...prev, lastSync: new Date() }));
    setIsSyncing(false);
  };

  const getStatusColor = (status: AmazonProduct['status']) => {
    switch (status) {
      case 'active': return 'success';
      case 'inactive': return 'warning';
      case 'suppressed': return 'error';
      case 'incomplete': return 'info';
      default: return 'default';
    }
  };

  const getStatusText = (status: AmazonProduct['status']) => {
    switch (status) {
      case 'active': return 'Aktif';
      case 'inactive': return 'Pasif';
      case 'suppressed': return 'Bastırıldı';
      case 'incomplete': return 'Eksik';
      default: return 'Bilinmiyor';
    }
  };

  const getFulfillmentColor = (fulfillment: AmazonProduct['fulfillment']) => {
    return fulfillment === 'FBA' ? 'primary' : 'secondary';
  };

  const getMarketplaceFlag = (marketplace: AmazonProduct['marketplace']) => {
    const flags: Record<string, string> = {
      US: '🇺🇸',
      UK: '🇬🇧',
      DE: '🇩🇪',
      FR: '🇫🇷',
      IT: '🇮🇹',
      ES: '🇪🇸',
      TR: '🇹🇷',
    };
    return flags[marketplace] || '🌍';
  };

  if (isLoading) {
    return (
      <Box display="flex" justifyContent="center" alignItems="center" minHeight="60vh">
        <Box textAlign="center">
          <CircularProgress size={60} />
          <Typography variant="h6" sx={{ mt: 2 }}>
            Amazon Verileri Yükleniyor...
          </Typography>
        </Box>
      </Box>
    );
  }

  return (
    <Box sx={{ p: 3 }}>
      {/* Header */}
      <Box display="flex" justifyContent="space-between" alignItems="center" mb={3}>
        <Box display="flex" alignItems="center">
          <Avatar sx={{ bgcolor: '#FF9900', mr: 2, width: 56, height: 56 }}>
            <Store sx={{ fontSize: 32 }} />
          </Avatar>
          <Box>
            <Typography variant="h4" component="h1" gutterBottom>
              Amazon Entegrasyonu
            </Typography>
            <Typography variant="body2" color="text.secondary">
              Son senkronizasyon: {stats.lastSync.toLocaleString('tr-TR')}
            </Typography>
          </Box>
        </Box>
        <Box display="flex" gap={2}>
          <FormControlLabel
            control={
              <Switch
                checked={autoSync}
                onChange={(e) => setAutoSync(e.target.checked)}
                color="primary"
              />
            }
            label="Otomatik Senkronizasyon"
          />
          <Button
            variant="contained"
            startIcon={isSyncing ? <CircularProgress size={20} /> : <Sync />}
            onClick={handleSync}
            disabled={isSyncing}
          >
            {isSyncing ? 'Senkronize Ediliyor...' : 'Senkronize Et'}
          </Button>
        </Box>
      </Box>

      {/* Connection Status */}
      <Alert 
        severity={stats.syncStatus === 'connected' ? 'success' : stats.syncStatus === 'syncing' ? 'warning' : 'error'}
        sx={{ mb: 3 }}
        icon={
          stats.syncStatus === 'connected' ? <CheckCircle /> : 
          stats.syncStatus === 'syncing' ? <Warning /> : <Error />
        }
      >
        {stats.syncStatus === 'connected' && `Amazon MWS/SP-API bağlantısı başarılı. ${stats.fbaProducts} FBA, ${stats.fbmProducts} FBM ürün.`}
        {stats.syncStatus === 'syncing' && 'Amazon ile senkronizasyon devam ediyor...'}
        {stats.syncStatus === 'error' && 'Amazon API bağlantısında sorun var. Kimlik bilgilerini kontrol edin.'}
      </Alert>

      {/* Stats Cards */}
      <Grid container spacing={3} mb={4}>
        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Box display="flex" alignItems="center" justifyContent="space-between">
                <Box>
                  <Typography color="text.secondary" gutterBottom>
                    Toplam Ürün
                  </Typography>
                  <Typography variant="h4">
                    {stats.totalProducts.toLocaleString()}
                  </Typography>
                  <Typography variant="body2" color="success.main">
                    {stats.activeProducts} aktif
                  </Typography>
                </Box>
                <Avatar sx={{ bgcolor: 'primary.main' }}>
                  <Inventory />
                </Avatar>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Box display="flex" alignItems="center" justifyContent="space-between">
                <Box>
                  <Typography color="text.secondary" gutterBottom>
                    Toplam Sipariş
                  </Typography>
                  <Typography variant="h4">
                    {stats.totalOrders.toLocaleString()}
                  </Typography>
                  <Typography variant="body2" color="warning.main">
                    {stats.pendingOrders} bekliyor
                  </Typography>
                </Box>
                <Avatar sx={{ bgcolor: 'success.main' }}>
                  <ShoppingCart />
                </Avatar>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Box display="flex" alignItems="center" justifyContent="space-between">
                <Box>
                  <Typography color="text.secondary" gutterBottom>
                    Toplam Gelir
                  </Typography>
                  <Typography variant="h4">
                    ${stats.totalRevenue.toLocaleString()}
                  </Typography>
                  <Typography variant="body2" color="info.main">
                    Bu ay: ${stats.monthlyRevenue.toLocaleString()}
                  </Typography>
                </Box>
                <Avatar sx={{ bgcolor: 'warning.main' }}>
                  <AttachMoney />
                </Avatar>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} sm={6} md={3}>
          <Card>
            <CardContent>
              <Box display="flex" alignItems="center" justifyContent="space-between">
                <Box>
                  <Typography color="text.secondary" gutterBottom>
                    Ortalama Puan
                  </Typography>
                  <Typography variant="h4">
                    {stats.averageRating.toFixed(1)}
                  </Typography>
                  <Typography variant="body2" color="info.main">
                    {stats.totalReviews.toLocaleString()} değerlendirme
                  </Typography>
                </Box>
                <Avatar sx={{ bgcolor: 'info.main' }}>
                  <Star />
                </Avatar>
              </Box>
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      {/* FBA/FBM Stats */}
      <Grid container spacing={3} mb={4}>
        <Grid item xs={12} md={6}>
          <Card>
            <CardContent>
              <Typography variant="h6" gutterBottom>
                Fulfillment Dağılımı
              </Typography>
              <Box display="flex" alignItems="center" gap={2} mb={2}>
                <LocalShipping color="primary" />
                <Box flex={1}>
                  <Typography variant="body2">FBA (Fulfillment by Amazon)</Typography>
                  <LinearProgress 
                    variant="determinate" 
                    value={(stats.fbaProducts / stats.totalProducts) * 100} 
                    sx={{ mt: 1 }}
                    color="primary"
                  />
                </Box>
                <Typography variant="h6" color="primary.main">
                  {stats.fbaProducts}
                </Typography>
              </Box>
              <Box display="flex" alignItems="center" gap={2}>
                <Security color="secondary" />
                <Box flex={1}>
                  <Typography variant="body2">FBM (Fulfillment by Merchant)</Typography>
                  <LinearProgress 
                    variant="determinate" 
                    value={(stats.fbmProducts / stats.totalProducts) * 100} 
                    sx={{ mt: 1 }}
                    color="secondary"
                  />
                </Box>
                <Typography variant="h6" color="secondary.main">
                  {stats.fbmProducts}
                </Typography>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} md={6}>
          <Card>
            <CardContent>
              <Typography variant="h6" gutterBottom>
                Hızlı İşlemler
              </Typography>
              <Box display="flex" gap={2} flexWrap="wrap">
                <Button variant="outlined" startIcon={<Upload />} size="small">
                  Ürün Yükle
                </Button>
                <Button variant="outlined" startIcon={<Download />} size="small">
                  Rapor İndir
                </Button>
                <Button variant="outlined" startIcon={<Sync />} size="small">
                  Stok Güncelle
                </Button>
                <Button variant="outlined" startIcon={<Settings />} size="small">
                  API Ayarları
                </Button>
                <Button variant="outlined" startIcon={<TrendingUp />} size="small">
                  Performans
                </Button>
                <Button variant="outlined" startIcon={<Language />} size="small">
                  Pazaryerleri
                </Button>
              </Box>
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      {/* Products Table */}
      <Card>
        <CardContent>
          <Box display="flex" justifyContent="space-between" alignItems="center" mb={2}>
            <Typography variant="h6">
              Ürün Listesi
            </Typography>
            <Tooltip title="Listeyi Yenile">
              <IconButton onClick={handleSync}>
                <Refresh />
              </IconButton>
            </Tooltip>
          </Box>
          
          <TableContainer>
            <Table>
              <TableHead>
                <TableRow>
                  <TableCell>ASIN</TableCell>
                  <TableCell>Ürün Adı</TableCell>
                  <TableCell>SKU</TableCell>
                  <TableCell align="right">Fiyat</TableCell>
                  <TableCell align="right">Stok</TableCell>
                  <TableCell>Durum</TableCell>
                  <TableCell>Fulfillment</TableCell>
                  <TableCell align="center">Puan</TableCell>
                  <TableCell align="center">Pazaryeri</TableCell>
                  <TableCell>Son Güncelleme</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {products.map((product) => (
                  <TableRow key={product.id} hover>
                    <TableCell>
                      <Typography variant="body2" fontFamily="monospace" fontWeight="bold">
                        {product.asin}
                      </Typography>
                    </TableCell>
                    <TableCell>
                      <Typography variant="body2">
                        {product.title}
                      </Typography>
                    </TableCell>
                    <TableCell>
                      <Typography variant="body2" fontFamily="monospace">
                        {product.sku}
                      </Typography>
                    </TableCell>
                    <TableCell align="right">
                      <Typography variant="body2" fontWeight="bold">
                        ${product.price}
                      </Typography>
                    </TableCell>
                    <TableCell align="right">
                      <Typography 
                        variant="body2" 
                        color={product.stock === 0 ? 'error.main' : product.stock < 10 ? 'warning.main' : 'text.primary'}
                        fontWeight={product.stock < 10 ? 'bold' : 'normal'}
                      >
                        {product.stock}
                      </Typography>
                    </TableCell>
                    <TableCell>
                      <Chip 
                        label={getStatusText(product.status)}
                        color={getStatusColor(product.status)}
                        size="small"
                      />
                    </TableCell>
                    <TableCell>
                      <Chip 
                        label={product.fulfillment}
                        color={getFulfillmentColor(product.fulfillment)}
                        size="small"
                        variant="outlined"
                      />
                    </TableCell>
                    <TableCell align="center">
                      <Box display="flex" alignItems="center" justifyContent="center" gap={0.5}>
                        <Star fontSize="small" color="warning" />
                        <Typography variant="body2" fontWeight="bold">
                          {product.rating}
                        </Typography>
                        <Typography variant="caption" color="text.secondary">
                          ({product.reviews})
                        </Typography>
                      </Box>
                    </TableCell>
                    <TableCell align="center">
                      <Typography variant="h6">
                        {getMarketplaceFlag(product.marketplace)}
                      </Typography>
                    </TableCell>
                    <TableCell>
                      <Typography variant="body2" color="text.secondary">
                        {product.lastUpdate.toLocaleDateString('tr-TR')}
                      </Typography>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </TableContainer>
        </CardContent>
      </Card>
    </Box>
  );
};

export default AmazonPage; 