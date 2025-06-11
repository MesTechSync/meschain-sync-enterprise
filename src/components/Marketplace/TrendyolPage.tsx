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
  Paper,
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
  Divider,
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
} from '@mui/icons-material';

interface TrendyolStats {
  totalProducts: number;
  activeProducts: number;
  totalOrders: number;
  pendingOrders: number;
  totalRevenue: number;
  monthlyRevenue: number;
  syncStatus: 'connected' | 'syncing' | 'error';
  lastSync: Date;
}

interface TrendyolProduct {
  id: string;
  title: string;
  barcode: string;
  price: number;
  stock: number;
  status: 'active' | 'passive' | 'rejected';
  category: string;
  lastUpdate: Date;
}

const TrendyolPage: React.FC = () => {
  const [stats, setStats] = useState<TrendyolStats>({
    totalProducts: 0,
    activeProducts: 0,
    totalOrders: 0,
    pendingOrders: 0,
    totalRevenue: 0,
    monthlyRevenue: 0,
    syncStatus: 'connected',
    lastSync: new Date(),
  });

  const [products, setProducts] = useState<TrendyolProduct[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isSyncing, setIsSyncing] = useState(false);
  const [autoSync, setAutoSync] = useState(true);

  useEffect(() => {
    const fetchTrendyolData = async () => {
      setIsLoading(true);
      
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 2000));
      
      setStats({
        totalProducts: 1234,
        activeProducts: 1156,
        totalOrders: 456,
        pendingOrders: 23,
        totalRevenue: 125600,
        monthlyRevenue: 34500,
        syncStatus: 'connected',
        lastSync: new Date(),
      });

      setProducts([
        {
          id: 'TR001',
          title: 'Samsung Galaxy S24 Ultra 256GB',
          barcode: '8806095048371',
          price: 45999,
          stock: 15,
          status: 'active',
          category: 'Elektronik > Telefon',
          lastUpdate: new Date(),
        },
        {
          id: 'TR002',
          title: 'Apple iPhone 15 Pro 128GB',
          barcode: '194253433729',
          price: 52999,
          stock: 8,
          status: 'active',
          category: 'Elektronik > Telefon',
          lastUpdate: new Date(),
        },
        {
          id: 'TR003',
          title: 'Nike Air Max 270 Spor Ayakkabı',
          barcode: '195866062345',
          price: 3299,
          stock: 0,
          status: 'passive',
          category: 'Spor > Ayakkabı',
          lastUpdate: new Date(),
        },
        {
          id: 'TR004',
          title: 'Dyson V15 Detect Kablosuz Süpürge',
          barcode: '885609015934',
          price: 8999,
          stock: 5,
          status: 'active',
          category: 'Ev & Yaşam > Temizlik',
          lastUpdate: new Date(),
        },
        {
          id: 'TR005',
          title: 'Sony WH-1000XM5 Kulaklık',
          barcode: '4548736142824',
          price: 7999,
          stock: 12,
          status: 'rejected',
          category: 'Elektronik > Ses',
          lastUpdate: new Date(),
        },
      ]);

      setIsLoading(false);
    };

    fetchTrendyolData();
  }, []);

  const handleSync = async () => {
    setIsSyncing(true);
    await new Promise(resolve => setTimeout(resolve, 3000));
    setStats(prev => ({ ...prev, lastSync: new Date() }));
    setIsSyncing(false);
  };

  const getStatusColor = (status: TrendyolProduct['status']) => {
    switch (status) {
      case 'active': return 'success';
      case 'passive': return 'warning';
      case 'rejected': return 'error';
      default: return 'default';
    }
  };

  const getStatusText = (status: TrendyolProduct['status']) => {
    switch (status) {
      case 'active': return 'Aktif';
      case 'passive': return 'Pasif';
      case 'rejected': return 'Reddedildi';
      default: return 'Bilinmiyor';
    }
  };

  if (isLoading) {
    return (
      <Box display="flex" justifyContent="center" alignItems="center" minHeight="60vh">
        <Box textAlign="center">
          <CircularProgress size={60} />
          <Typography variant="h6" sx={{ mt: 2 }}>
            Trendyol Verileri Yükleniyor...
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
          <Avatar sx={{ bgcolor: '#FF6000', mr: 2, width: 56, height: 56 }}>
            <Store sx={{ fontSize: 32 }} />
          </Avatar>
          <Box>
            <Typography variant="h4" component="h1" gutterBottom>
              Trendyol Entegrasyonu
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
        {stats.syncStatus === 'connected' && 'Trendyol ile bağlantı başarılı. Tüm sistemler çalışıyor.'}
        {stats.syncStatus === 'syncing' && 'Trendyol ile senkronizasyon devam ediyor...'}
        {stats.syncStatus === 'error' && 'Trendyol bağlantısında sorun var. Lütfen ayarları kontrol edin.'}
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
                    ₺{stats.totalRevenue.toLocaleString()}
                  </Typography>
                  <Typography variant="body2" color="info.main">
                    Bu ay: ₺{stats.monthlyRevenue.toLocaleString()}
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
                    Performans
                  </Typography>
                  <Typography variant="h4">
                    94%
                  </Typography>
                  <LinearProgress 
                    variant="determinate" 
                    value={94} 
                    sx={{ mt: 1 }}
                    color="success"
                  />
                </Box>
                <Avatar sx={{ bgcolor: 'info.main' }}>
                  <TrendingUp />
                </Avatar>
              </Box>
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      {/* Quick Actions */}
      <Card sx={{ mb: 4 }}>
        <CardContent>
          <Typography variant="h6" gutterBottom>
            Hızlı İşlemler
          </Typography>
          <Box display="flex" gap={2} flexWrap="wrap">
            <Button variant="outlined" startIcon={<Upload />}>
              Ürün Yükle
            </Button>
            <Button variant="outlined" startIcon={<Download />}>
              Ürün İndir
            </Button>
            <Button variant="outlined" startIcon={<Sync />}>
              Stok Güncelle
            </Button>
            <Button variant="outlined" startIcon={<Settings />}>
              API Ayarları
            </Button>
            <Button variant="outlined" startIcon={<TrendingUp />}>
              Raporlar
            </Button>
          </Box>
        </CardContent>
      </Card>

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
                  <TableCell>Ürün ID</TableCell>
                  <TableCell>Ürün Adı</TableCell>
                  <TableCell>Barkod</TableCell>
                  <TableCell align="right">Fiyat</TableCell>
                  <TableCell align="right">Stok</TableCell>
                  <TableCell>Durum</TableCell>
                  <TableCell>Kategori</TableCell>
                  <TableCell>Son Güncelleme</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {products.map((product) => (
                  <TableRow key={product.id} hover>
                    <TableCell>
                      <Typography variant="body2" fontWeight="bold">
                        {product.id}
                      </Typography>
                    </TableCell>
                    <TableCell>
                      <Typography variant="body2">
                        {product.title}
                      </Typography>
                    </TableCell>
                    <TableCell>
                      <Typography variant="body2" fontFamily="monospace">
                        {product.barcode}
                      </Typography>
                    </TableCell>
                    <TableCell align="right">
                      <Typography variant="body2" fontWeight="bold">
                        ₺{product.price.toLocaleString()}
                      </Typography>
                    </TableCell>
                    <TableCell align="right">
                      <Typography 
                        variant="body2" 
                        color={product.stock === 0 ? 'error.main' : product.stock < 5 ? 'warning.main' : 'text.primary'}
                        fontWeight={product.stock < 5 ? 'bold' : 'normal'}
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
                      <Typography variant="body2" color="text.secondary">
                        {product.category}
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

export default TrendyolPage; 