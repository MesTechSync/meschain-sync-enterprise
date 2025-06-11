import React, { useState, useEffect } from 'react';
import {
  Box,
  Grid,
  Card,
  CardContent,
  Typography,
  Button,
  Chip,
  LinearProgress,
  Avatar,
  IconButton,
  Tooltip,
  Paper,
  Divider,
  Alert,
  CircularProgress,
} from '@mui/material';
import {
  Dashboard as DashboardIcon,
  TrendingUp,
  ShoppingCart,
  Inventory,
  Analytics,
  Notifications,
  Refresh,
  Settings,
  Store,
  LocalShipping,
  AttachMoney,
  People,
} from '@mui/icons-material';
import { Line, Bar, Doughnut } from 'react-chartjs-2';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip as ChartTooltip,
  Legend,
  ArcElement,
} from 'chart.js';

// Register Chart.js components
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  ChartTooltip,
  Legend,
  ArcElement
);

interface DashboardStats {
  totalOrders: number;
  totalRevenue: number;
  activeProducts: number;
  connectedMarketplaces: number;
  pendingOrders: number;
  lowStockItems: number;
}

interface MarketplaceStatus {
  name: string;
  status: 'connected' | 'disconnected' | 'syncing';
  orders: number;
  revenue: number;
  icon: string;
}

const MainDashboard: React.FC = () => {
  const [stats, setStats] = useState<DashboardStats>({
    totalOrders: 0,
    totalRevenue: 0,
    activeProducts: 0,
    connectedMarketplaces: 0,
    pendingOrders: 0,
    lowStockItems: 0,
  });

  const [marketplaces, setMarketplaces] = useState<MarketplaceStatus[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [lastUpdate, setLastUpdate] = useState<Date>(new Date());

  // Simulate data fetching
  useEffect(() => {
    const fetchDashboardData = async () => {
      setIsLoading(true);
      
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 1500));
      
      setStats({
        totalOrders: 1247,
        totalRevenue: 89650,
        activeProducts: 3456,
        connectedMarketplaces: 5,
        pendingOrders: 23,
        lowStockItems: 12,
      });

      setMarketplaces([
        { name: 'Trendyol', status: 'connected', orders: 456, revenue: 34500, icon: '🛒' },
        { name: 'N11', status: 'connected', orders: 234, revenue: 18900, icon: '🏪' },
        { name: 'Amazon', status: 'syncing', orders: 189, revenue: 15600, icon: '📦' },
        { name: 'eBay', status: 'connected', orders: 167, revenue: 12300, icon: '🌐' },
        { name: 'Hepsiburada', status: 'disconnected', orders: 201, revenue: 8350, icon: '🛍️' },
      ]);

      setIsLoading(false);
      setLastUpdate(new Date());
    };

    fetchDashboardData();
  }, []);

  const handleRefresh = () => {
    setLastUpdate(new Date());
    // Trigger data refresh
  };

  // Chart data
  const salesChartData = {
    labels: ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran'],
    datasets: [
      {
        label: 'Satışlar',
        data: [12000, 19000, 15000, 25000, 22000, 30000],
        borderColor: '#2563eb',
        backgroundColor: 'rgba(37, 99, 235, 0.1)',
        tension: 0.4,
      },
    ],
  };

  const marketplaceChartData = {
    labels: marketplaces.map(m => m.name),
    datasets: [
      {
        data: marketplaces.map(m => m.revenue),
        backgroundColor: [
          '#FF6384',
          '#36A2EB',
          '#FFCE56',
          '#4BC0C0',
          '#9966FF',
        ],
      },
    ],
  };

  const getStatusColor = (status: MarketplaceStatus['status']) => {
    switch (status) {
      case 'connected': return 'success';
      case 'syncing': return 'warning';
      case 'disconnected': return 'error';
      default: return 'default';
    }
  };

  const getStatusText = (status: MarketplaceStatus['status']) => {
    switch (status) {
      case 'connected': return 'Bağlı';
      case 'syncing': return 'Senkronizasyon';
      case 'disconnected': return 'Bağlantı Kesildi';
      default: return 'Bilinmiyor';
    }
  };

  if (isLoading) {
    return (
      <Box display="flex" justifyContent="center" alignItems="center" minHeight="60vh">
        <Box textAlign="center">
          <CircularProgress size={60} />
          <Typography variant="h6" sx={{ mt: 2 }}>
            Dashboard Yükleniyor...
          </Typography>
        </Box>
      </Box>
    );
  }

  return (
    <Box sx={{ p: 3 }}>
      {/* Header */}
      <Box display="flex" justifyContent="space-between" alignItems="center" mb={3}>
        <Box>
          <Typography variant="h4" component="h1" gutterBottom>
            <DashboardIcon sx={{ mr: 1, verticalAlign: 'middle' }} />
            MesChain-Sync Dashboard
          </Typography>
          <Typography variant="body2" color="text.secondary">
            Son güncelleme: {lastUpdate.toLocaleString('tr-TR')}
          </Typography>
        </Box>
        <Box>
          <Tooltip title="Verileri Yenile">
            <IconButton onClick={handleRefresh} color="primary">
              <Refresh />
            </IconButton>
          </Tooltip>
          <Tooltip title="Ayarlar">
            <IconButton color="primary">
              <Settings />
            </IconButton>
          </Tooltip>
        </Box>
      </Box>

      {/* Stats Cards */}
      <Grid container spacing={3} mb={4}>
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
                </Box>
                <Avatar sx={{ bgcolor: 'primary.main' }}>
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
                </Box>
                <Avatar sx={{ bgcolor: 'success.main' }}>
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
                    Aktif Ürün
                  </Typography>
                  <Typography variant="h4">
                    {stats.activeProducts.toLocaleString()}
                  </Typography>
                </Box>
                <Avatar sx={{ bgcolor: 'info.main' }}>
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
                    Bağlı Pazaryeri
                  </Typography>
                  <Typography variant="h4">
                    {stats.connectedMarketplaces}
                  </Typography>
                </Box>
                <Avatar sx={{ bgcolor: 'warning.main' }}>
                  <Store />
                </Avatar>
              </Box>
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      {/* Alerts */}
      {(stats.pendingOrders > 0 || stats.lowStockItems > 0) && (
        <Box mb={3}>
          {stats.pendingOrders > 0 && (
            <Alert severity="warning" sx={{ mb: 1 }}>
              {stats.pendingOrders} bekleyen siparişiniz var. Hemen işlem yapın!
            </Alert>
          )}
          {stats.lowStockItems > 0 && (
            <Alert severity="error">
              {stats.lowStockItems} ürününüzde stok azalması var. Stok güncellemesi yapın!
            </Alert>
          )}
        </Box>
      )}

      {/* Charts and Marketplace Status */}
      <Grid container spacing={3}>
        {/* Sales Chart */}
        <Grid item xs={12} md={8}>
          <Card>
            <CardContent>
              <Typography variant="h6" gutterBottom>
                <TrendingUp sx={{ mr: 1, verticalAlign: 'middle' }} />
                Satış Trendi
              </Typography>
              <Box height={300}>
                <Line 
                  data={salesChartData} 
                  options={{
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                      legend: {
                        position: 'top' as const,
                      },
                    },
                  }} 
                />
              </Box>
            </CardContent>
          </Card>
        </Grid>

        {/* Marketplace Revenue */}
        <Grid item xs={12} md={4}>
          <Card>
            <CardContent>
              <Typography variant="h6" gutterBottom>
                <Analytics sx={{ mr: 1, verticalAlign: 'middle' }} />
                Pazaryeri Gelirleri
              </Typography>
              <Box height={300}>
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
              </Box>
            </CardContent>
          </Card>
        </Grid>

        {/* Marketplace Status */}
        <Grid item xs={12}>
          <Card>
            <CardContent>
              <Typography variant="h6" gutterBottom>
                <Store sx={{ mr: 1, verticalAlign: 'middle' }} />
                Pazaryeri Durumu
              </Typography>
              <Grid container spacing={2}>
                {marketplaces.map((marketplace, index) => (
                  <Grid item xs={12} sm={6} md={4} lg={2.4} key={index}>
                    <Paper sx={{ p: 2, textAlign: 'center' }}>
                      <Typography variant="h4" sx={{ mb: 1 }}>
                        {marketplace.icon}
                      </Typography>
                      <Typography variant="h6" gutterBottom>
                        {marketplace.name}
                      </Typography>
                      <Chip 
                        label={getStatusText(marketplace.status)}
                        color={getStatusColor(marketplace.status)}
                        size="small"
                        sx={{ mb: 1 }}
                      />
                      <Typography variant="body2" color="text.secondary">
                        {marketplace.orders} sipariş
                      </Typography>
                      <Typography variant="body2" color="text.secondary">
                        ₺{marketplace.revenue.toLocaleString()}
                      </Typography>
                    </Paper>
                  </Grid>
                ))}
              </Grid>
            </CardContent>
          </Card>
        </Grid>
      </Grid>
    </Box>
  );
};

export default MainDashboard; 