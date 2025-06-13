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
  Add,
  Edit,
  Delete,
  Visibility,
} from '@mui/icons-material';

interface N11Stats {
  totalProducts: number;
  activeProducts: number;
  totalOrders: number;
  pendingOrders: number;
  totalRevenue: number;
  monthlyRevenue: number;
  syncStatus: 'connected' | 'syncing' | 'error' | 'disconnected';
  lastSync: Date;
  commissionRate: number;
}

interface N11Product {
  id: string;
  title: string;
  productCode: string;
  price: number;
  discountPrice?: number;
  stock: number;
  status: 'active' | 'passive' | 'waiting' | 'rejected';
  category: string;
  commission: number;
  lastUpdate: Date;
  images: number;
}

const N11Page: React.FC = () => {
  const [stats, setStats] = useState<N11Stats>({
    totalProducts: 0,
    activeProducts: 0,
    totalOrders: 0,
    pendingOrders: 0,
    totalRevenue: 0,
    monthlyRevenue: 0,
    syncStatus: 'connected',
    lastSync: new Date(),
    commissionRate: 8.5,
  });

  const [products, setProducts] = useState<N11Product[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isSyncing, setIsSyncing] = useState(false);
  const [autoSync, setAutoSync] = useState(true);

  useEffect(() => {
    const fetchN11Data = async () => {
      setIsLoading(true);
      
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 2500));
      
      setStats({
        totalProducts: 856,
        activeProducts: 743,
        totalOrders: 234,
        pendingOrders: 12,
        totalRevenue: 89400,
        monthlyRevenue: 23100,
        syncStatus: 'connected',
        lastSync: new Date(),
        commissionRate: 8.5,
      });

      setProducts([
        {
          id: 'N11001',
          title: 'Xiaomi Redmi Note 13 Pro 256GB',
          productCode: 'XMI-RN13P-256',
          price: 12999,
          discountPrice: 11499,
          stock: 25,
          status: 'active',
          category: 'Elektronik > Cep Telefonu',
          commission: 8.5,
          lastUpdate: new Date(),
          images: 8,
        },
        {
          id: 'N11002',
          title: 'Adidas Ultraboost 22 Koşu Ayakkabısı',
          productCode: 'ADI-UB22-BLK',
          price: 4299,
          stock: 12,
          status: 'active',
          category: 'Spor > Koşu Ayakkabısı',
          commission: 12.0,
          lastUpdate: new Date(),
          images: 6,
        },
        {
          id: 'N11003',
          title: 'Philips Airfryer XXL 7.3L',
          productCode: 'PHI-AF-XXL73',
          price: 5999,
          discountPrice: 4999,
          stock: 0,
          status: 'passive',
          category: 'Ev & Yaşam > Mutfak',
          commission: 15.0,
          lastUpdate: new Date(),
          images: 10,
        },
        {
          id: 'N11004',
          title: 'Canon EOS R50 Aynasız Fotoğraf Makinesi',
          productCode: 'CAN-EOSR50-KIT',
          price: 28999,
          stock: 3,
          status: 'waiting',
          category: 'Elektronik > Fotoğraf',
          commission: 6.5,
          lastUpdate: new Date(),
          images: 12,
        },
        {
          id: 'N11005',
          title: 'Lego Creator Expert Taj Mahal',
          productCode: 'LEG-CR-TAJM',
          price: 2499,
          stock: 8,
          status: 'rejected',
          category: 'Oyuncak > Lego',
          commission: 18.0,
          lastUpdate: new Date(),
          images: 5,
        },
      ]);

      setIsLoading(false);
    };

    fetchN11Data();
  }, []);

  const handleSync = async () => {
    setIsSyncing(true);
    await new Promise(resolve => setTimeout(resolve, 4000));
    setStats(prev => ({ ...prev, lastSync: new Date() }));
    setIsSyncing(false);
  };

  const getStatusColor = (status: N11Product['status']) => {
    switch (status) {
      case 'active': return 'success';
      case 'passive': return 'warning';
      case 'waiting': return 'info';
      case 'rejected': return 'error';
      default: return 'default';
    }
  };

  const getStatusText = (status: N11Product['status']) => {
    switch (status) {
      case 'active': return 'Aktif';
      case 'passive': return 'Pasif';
      case 'waiting': return 'Onay Bekliyor';
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
            N11 Verileri Yükleniyor...
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
          <Avatar sx={{ bgcolor: '#7B68EE', mr: 2, width: 56, height: 56 }}>
            <Store sx={{ fontSize: 32 }} />
          </Avatar>
          <Box>
            <Typography variant="h4" component="h1" gutterBottom>
              N11 Entegrasyonu
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
        {stats.syncStatus === 'connected' && `N11 ile bağlantı başarılı. Komisyon oranı: %${stats.commissionRate}`}
        {stats.syncStatus === 'syncing' && 'N11 ile senkronizasyon devam ediyor...'}
        {stats.syncStatus === 'error' && 'N11 bağlantısında sorun var. API anahtarlarını kontrol edin.'}
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
                    Komisyon Oranı
                  </Typography>
                  <Typography variant="h4">
                    %{stats.commissionRate}
                  </Typography>
                  <LinearProgress 
                    variant="determinate" 
                    value={stats.commissionRate * 10} 
                    sx={{ mt: 1 }}
                    color="info"
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
            <Button variant="outlined" startIcon={<Add />}>
              Yeni Ürün
            </Button>
            <Button variant="outlined" startIcon={<Upload />}>
              Toplu Yükleme
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
              Satış Raporları
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
                  <TableCell>Ürün Kodu</TableCell>
                  <TableCell align="right">Fiyat</TableCell>
                  <TableCell align="right">İndirimli</TableCell>
                  <TableCell align="right">Stok</TableCell>
                  <TableCell>Durum</TableCell>
                  <TableCell align="right">Komisyon</TableCell>
                  <TableCell align="center">Resim</TableCell>
                  <TableCell align="center">İşlemler</TableCell>
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
                        {product.productCode}
                      </Typography>
                    </TableCell>
                    <TableCell align="right">
                      <Typography variant="body2" fontWeight="bold">
                        ₺{product.price.toLocaleString()}
                      </Typography>
                    </TableCell>
                    <TableCell align="right">
                      {product.discountPrice ? (
                        <Typography variant="body2" fontWeight="bold" color="error.main">
                          ₺{product.discountPrice.toLocaleString()}
                        </Typography>
                      ) : (
                        <Typography variant="body2" color="text.secondary">
                          -
                        </Typography>
                      )}
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
                    <TableCell align="right">
                      <Typography variant="body2" color="info.main" fontWeight="bold">
                        %{product.commission}
                      </Typography>
                    </TableCell>
                    <TableCell align="center">
                      <Chip 
                        label={product.images}
                        size="small"
                        variant="outlined"
                      />
                    </TableCell>
                    <TableCell align="center">
                      <Box display="flex" gap={1}>
                        <Tooltip title="Görüntüle">
                          <IconButton size="small">
                            <Visibility fontSize="small" />
                          </IconButton>
                        </Tooltip>
                        <Tooltip title="Düzenle">
                          <IconButton size="small">
                            <Edit fontSize="small" />
                          </IconButton>
                        </Tooltip>
                        <Tooltip title="Sil">
                          <IconButton size="small" color="error">
                            <Delete fontSize="small" />
                          </IconButton>
                        </Tooltip>
                      </Box>
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

export default N11Page; 