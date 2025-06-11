import React, { useState } from 'react';
import {
  Box,
  Grid,
  Card,
  CardContent,
  Typography,
  Button,
  Switch,
  TextField,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Tabs,
  Tab,
  Divider,
  Alert,
  Avatar,
  List,
  ListItem,
  ListItemText,
  ListItemSecondaryAction,
  IconButton,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  FormControlLabel,
} from '@mui/material';
import {
  Settings,
  Security,
  Notifications,
  Store,
  Api,
  Save,
  Refresh,
  Edit,
  Delete,
  Add,
  Visibility,
  VisibilityOff,
  Warning,
  CheckCircle,
} from '@mui/icons-material';

interface MarketplaceConfig {
  id: string;
  name: string;
  enabled: boolean;
  apiKey: string;
  secretKey: string;
  storeId?: string;
  environment: 'sandbox' | 'production';
  lastSync?: Date;
  status: 'connected' | 'disconnected' | 'error';
}

const SettingsPage: React.FC = () => {
  const [activeTab, setActiveTab] = useState(0);
  const [showApiKeys, setShowApiKeys] = useState(false);
  const [openDialog, setOpenDialog] = useState(false);
  const [selectedMarketplace, setSelectedMarketplace] = useState<MarketplaceConfig | null>(null);

  const [marketplaces, setMarketplaces] = useState<MarketplaceConfig[]>([
    {
      id: 'trendyol',
      name: 'Trendyol',
      enabled: true,
      apiKey: 'TY_API_KEY_***',
      secretKey: 'TY_SECRET_***',
      storeId: '12345',
      environment: 'production',
      lastSync: new Date(),
      status: 'connected',
    },
    {
      id: 'n11',
      name: 'N11',
      enabled: true,
      apiKey: 'N11_API_KEY_***',
      secretKey: 'N11_SECRET_***',
      environment: 'production',
      lastSync: new Date(),
      status: 'connected',
    },
    {
      id: 'amazon',
      name: 'Amazon',
      enabled: false,
      apiKey: '',
      secretKey: '',
      environment: 'sandbox',
      status: 'disconnected',
    },
    {
      id: 'hepsiburada',
      name: 'Hepsiburada',
      enabled: true,
      apiKey: 'HB_API_KEY_***',
      secretKey: 'HB_SECRET_***',
      environment: 'production',
      lastSync: new Date(),
      status: 'error',
    },
    {
      id: 'ebay',
      name: 'eBay',
      enabled: false,
      apiKey: '',
      secretKey: '',
      environment: 'sandbox',
      status: 'disconnected',
    },
    {
      id: 'ozon',
      name: 'Ozon',
      enabled: false,
      apiKey: '',
      secretKey: '',
      environment: 'sandbox',
      status: 'disconnected',
    },
  ]);

  const [generalSettings, setGeneralSettings] = useState({
    autoSync: true,
    syncInterval: 30,
    notifications: true,
    emailNotifications: true,
    stockAlerts: true,
    orderAlerts: true,
    language: 'tr',
    timezone: 'Europe/Istanbul',
  });

  const handleTabChange = (event: React.SyntheticEvent, newValue: number) => {
    setActiveTab(newValue);
  };

  const handleMarketplaceEdit = (marketplace: MarketplaceConfig) => {
    setSelectedMarketplace(marketplace);
    setOpenDialog(true);
  };

  const handleDialogClose = () => {
    setOpenDialog(false);
    setSelectedMarketplace(null);
  };

  const getStatusColor = (status: MarketplaceConfig['status']) => {
    switch (status) {
      case 'connected': return 'success';
      case 'disconnected': return 'warning';
      case 'error': return 'error';
      default: return 'default';
    }
  };

  const getStatusText = (status: MarketplaceConfig['status']) => {
    switch (status) {
      case 'connected': return 'Bağlı';
      case 'disconnected': return 'Bağlı Değil';
      case 'error': return 'Hata';
      default: return 'Bilinmiyor';
    }
  };

  const getMarketplaceColor = (id: string) => {
    const colors: { [key: string]: string } = {
      trendyol: '#FF6000',
      n11: '#7B68EE',
      amazon: '#FF9900',
      hepsiburada: '#FF6000',
      ebay: '#0064D2',
      ozon: '#005BFF',
    };
    return colors[id] || '#666';
  };

  return (
    <Box sx={{ p: 3 }}>
      {/* Header */}
      <Box display="flex" justifyContent="space-between" alignItems="center" mb={3}>
        <Box>
          <Typography variant="h4" component="h1" gutterBottom>
            Sistem Ayarları
          </Typography>
          <Typography variant="body2" color="text.secondary">
            MesChain-Sync Enterprise ayarlarını yönetin
          </Typography>
        </Box>
        <Box display="flex" gap={2}>
          <Button variant="outlined" startIcon={<Refresh />}>
            Yenile
          </Button>
          <Button variant="contained" startIcon={<Save />}>
            Kaydet
          </Button>
        </Box>
      </Box>

      {/* Settings Tabs */}
      <Card>
        <Box sx={{ borderBottom: 1, borderColor: 'divider' }}>
          <Tabs value={activeTab} onChange={handleTabChange}>
            <Tab 
              label="Genel Ayarlar" 
              icon={<Settings />} 
              iconPosition="start"
            />
            <Tab 
              label="Pazaryerleri" 
              icon={<Store />} 
              iconPosition="start"
            />
            <Tab 
              label="API Ayarları" 
              icon={<Api />} 
              iconPosition="start"
            />
            <Tab 
              label="Bildirimler" 
              icon={<Notifications />} 
              iconPosition="start"
            />
            <Tab 
              label="Güvenlik" 
              icon={<Security />} 
              iconPosition="start"
            />
          </Tabs>
        </Box>

        <CardContent>
          {/* General Settings Tab */}
          {activeTab === 0 && (
            <Box>
              <Typography variant="h6" gutterBottom>
                Genel Sistem Ayarları
              </Typography>
              
              <Grid container spacing={3}>
                <Grid item xs={12} md={6}>
                  <Card variant="outlined">
                    <CardContent>
                      <Typography variant="h6" gutterBottom>
                        Senkronizasyon
                      </Typography>
                      
                      <FormControlLabel
                        control={
                          <Switch
                            checked={generalSettings.autoSync}
                            onChange={(e) => setGeneralSettings(prev => ({ ...prev, autoSync: e.target.checked }))}
                          />
                        }
                        label="Otomatik Senkronizasyon"
                      />
                      
                      <Box mt={2}>
                        <FormControl fullWidth>
                          <InputLabel>Senkronizasyon Aralığı</InputLabel>
                          <Select
                            value={generalSettings.syncInterval}
                            onChange={(e) => setGeneralSettings(prev => ({ ...prev, syncInterval: e.target.value as number }))}
                            label="Senkronizasyon Aralığı"
                          >
                            <MenuItem value={15}>15 Dakika</MenuItem>
                            <MenuItem value={30}>30 Dakika</MenuItem>
                            <MenuItem value={60}>1 Saat</MenuItem>
                            <MenuItem value={180}>3 Saat</MenuItem>
                            <MenuItem value={360}>6 Saat</MenuItem>
                          </Select>
                        </FormControl>
                      </Box>
                    </CardContent>
                  </Card>
                </Grid>

                <Grid item xs={12} md={6}>
                  <Card variant="outlined">
                    <CardContent>
                      <Typography variant="h6" gutterBottom>
                        Bölgesel Ayarlar
                      </Typography>
                      
                      <Box mb={2}>
                        <FormControl fullWidth>
                          <InputLabel>Dil</InputLabel>
                          <Select
                            value={generalSettings.language}
                            onChange={(e) => setGeneralSettings(prev => ({ ...prev, language: e.target.value }))}
                            label="Dil"
                          >
                            <MenuItem value="tr">Türkçe</MenuItem>
                            <MenuItem value="en">English</MenuItem>
                            <MenuItem value="ru">Русский</MenuItem>
                          </Select>
                        </FormControl>
                      </Box>
                      
                      <FormControl fullWidth>
                        <InputLabel>Saat Dilimi</InputLabel>
                        <Select
                          value={generalSettings.timezone}
                          onChange={(e) => setGeneralSettings(prev => ({ ...prev, timezone: e.target.value }))}
                          label="Saat Dilimi"
                        >
                          <MenuItem value="Europe/Istanbul">İstanbul (UTC+3)</MenuItem>
                          <MenuItem value="Europe/London">Londra (UTC+0)</MenuItem>
                          <MenuItem value="Europe/Moscow">Moskova (UTC+3)</MenuItem>
                          <MenuItem value="America/New_York">New York (UTC-5)</MenuItem>
                        </Select>
                      </FormControl>
                    </CardContent>
                  </Card>
                </Grid>
              </Grid>
            </Box>
          )}

          {/* Marketplaces Tab */}
          {activeTab === 1 && (
            <Box>
              <Typography variant="h6" gutterBottom>
                Pazaryeri Entegrasyonları
              </Typography>
              
              <List>
                {marketplaces.map((marketplace) => (
                  <React.Fragment key={marketplace.id}>
                    <ListItem>
                      <Avatar
                        sx={{
                          bgcolor: getMarketplaceColor(marketplace.id),
                          mr: 2,
                        }}
                      >
                        <Store />
                      </Avatar>
                      <ListItemText
                        primary={marketplace.name}
                        secondary={
                          <Box>
                            <Typography variant="body2" color="text.secondary">
                              Durum: <span style={{ color: getStatusColor(marketplace.status) === 'success' ? '#4caf50' : getStatusColor(marketplace.status) === 'error' ? '#f44336' : '#ff9800' }}>
                                {getStatusText(marketplace.status)}
                              </span>
                            </Typography>
                            {marketplace.lastSync && (
                              <Typography variant="caption" color="text.secondary">
                                Son senkronizasyon: {marketplace.lastSync.toLocaleString('tr-TR')}
                              </Typography>
                            )}
                          </Box>
                        }
                      />
                      <ListItemSecondaryAction>
                        <Box display="flex" alignItems="center" gap={1}>
                          <Switch
                            checked={marketplace.enabled}
                            onChange={(e) => {
                              setMarketplaces(prev => 
                                prev.map(m => 
                                  m.id === marketplace.id 
                                    ? { ...m, enabled: e.target.checked }
                                    : m
                                )
                              );
                            }}
                          />
                          <IconButton onClick={() => handleMarketplaceEdit(marketplace)}>
                            <Edit />
                          </IconButton>
                        </Box>
                      </ListItemSecondaryAction>
                    </ListItem>
                    <Divider />
                  </React.Fragment>
                ))}
              </List>
            </Box>
          )}

          {/* API Settings Tab */}
          {activeTab === 2 && (
            <Box>
              <Typography variant="h6" gutterBottom>
                API Konfigürasyonu
              </Typography>
              
              <Alert severity="warning" sx={{ mb: 3 }}>
                <Typography variant="body2">
                  API anahtarlarınızı güvenli tutun. Bu bilgileri asla üçüncü şahıslarla paylaşmayın.
                </Typography>
              </Alert>

              <Box display="flex" alignItems="center" gap={2} mb={3}>
                <Typography variant="body1">API Anahtarlarını Göster:</Typography>
                <IconButton onClick={() => setShowApiKeys(!showApiKeys)}>
                  {showApiKeys ? <VisibilityOff /> : <Visibility />}
                </IconButton>
              </Box>

              <Grid container spacing={3}>
                {marketplaces.filter(m => m.enabled).map((marketplace) => (
                  <Grid item xs={12} md={6} key={marketplace.id}>
                    <Card variant="outlined">
                      <CardContent>
                        <Box display="flex" alignItems="center" mb={2}>
                          <Avatar
                            sx={{
                              bgcolor: getMarketplaceColor(marketplace.id),
                              mr: 2,
                              width: 32,
                              height: 32,
                            }}
                          >
                            <Store fontSize="small" />
                          </Avatar>
                          <Typography variant="h6">{marketplace.name}</Typography>
                        </Box>
                        
                        <TextField
                          fullWidth
                          label="API Key"
                          type={showApiKeys ? 'text' : 'password'}
                          value={marketplace.apiKey}
                          margin="normal"
                          size="small"
                        />
                        
                        <TextField
                          fullWidth
                          label="Secret Key"
                          type={showApiKeys ? 'text' : 'password'}
                          value={marketplace.secretKey}
                          margin="normal"
                          size="small"
                        />
                        
                        {marketplace.storeId && (
                          <TextField
                            fullWidth
                            label="Store ID"
                            value={marketplace.storeId}
                            margin="normal"
                            size="small"
                          />
                        )}
                        
                        <FormControl fullWidth margin="normal" size="small">
                          <InputLabel>Ortam</InputLabel>
                          <Select
                            value={marketplace.environment}
                            label="Ortam"
                          >
                            <MenuItem value="sandbox">Test (Sandbox)</MenuItem>
                            <MenuItem value="production">Canlı (Production)</MenuItem>
                          </Select>
                        </FormControl>
                      </CardContent>
                    </Card>
                  </Grid>
                ))}
              </Grid>
            </Box>
          )}

          {/* Notifications Tab */}
          {activeTab === 3 && (
            <Box>
              <Typography variant="h6" gutterBottom>
                Bildirim Ayarları
              </Typography>
              
              <Grid container spacing={3}>
                <Grid item xs={12} md={6}>
                  <Card variant="outlined">
                    <CardContent>
                      <Typography variant="h6" gutterBottom>
                        Genel Bildirimler
                      </Typography>
                      
                      <FormControlLabel
                        control={
                          <Switch
                            checked={generalSettings.notifications}
                            onChange={(e) => setGeneralSettings(prev => ({ ...prev, notifications: e.target.checked }))}
                          />
                        }
                        label="Sistem Bildirimleri"
                      />
                      
                      <FormControlLabel
                        control={
                          <Switch
                            checked={generalSettings.emailNotifications}
                            onChange={(e) => setGeneralSettings(prev => ({ ...prev, emailNotifications: e.target.checked }))}
                          />
                        }
                        label="E-posta Bildirimleri"
                      />
                    </CardContent>
                  </Card>
                </Grid>

                <Grid item xs={12} md={6}>
                  <Card variant="outlined">
                    <CardContent>
                      <Typography variant="h6" gutterBottom>
                        Uyarı Bildirimleri
                      </Typography>
                      
                      <FormControlLabel
                        control={
                          <Switch
                            checked={generalSettings.stockAlerts}
                            onChange={(e) => setGeneralSettings(prev => ({ ...prev, stockAlerts: e.target.checked }))}
                          />
                        }
                        label="Stok Uyarıları"
                      />
                      
                      <FormControlLabel
                        control={
                          <Switch
                            checked={generalSettings.orderAlerts}
                            onChange={(e) => setGeneralSettings(prev => ({ ...prev, orderAlerts: e.target.checked }))}
                          />
                        }
                        label="Sipariş Uyarıları"
                      />
                    </CardContent>
                  </Card>
                </Grid>
              </Grid>
            </Box>
          )}

          {/* Security Tab */}
          {activeTab === 4 && (
            <Box>
              <Typography variant="h6" gutterBottom>
                Güvenlik Ayarları
              </Typography>
              
              <Alert severity="info" sx={{ mb: 3 }}>
                <Typography variant="body2">
                  Güvenlik ayarlarınız otomatik olarak yönetilmektedir. Herhangi bir değişiklik için sistem yöneticisi ile iletişime geçin.
                </Typography>
              </Alert>

              <Grid container spacing={3}>
                <Grid item xs={12} md={4}>
                  <Card variant="outlined">
                    <CardContent sx={{ textAlign: 'center' }}>
                      <CheckCircle sx={{ fontSize: 48, color: 'success.main', mb: 2 }} />
                      <Typography variant="h6">SSL Sertifikası</Typography>
                      <Typography variant="body2" color="text.secondary">
                        Aktif ve Güncel
                      </Typography>
                    </CardContent>
                  </Card>
                </Grid>

                <Grid item xs={12} md={4}>
                  <Card variant="outlined">
                    <CardContent sx={{ textAlign: 'center' }}>
                      <CheckCircle sx={{ fontSize: 48, color: 'success.main', mb: 2 }} />
                      <Typography variant="h6">API Güvenliği</Typography>
                      <Typography variant="body2" color="text.secondary">
                        256-bit Şifreleme
                      </Typography>
                    </CardContent>
                  </Card>
                </Grid>

                <Grid item xs={12} md={4}>
                  <Card variant="outlined">
                    <CardContent sx={{ textAlign: 'center' }}>
                      <Warning sx={{ fontSize: 48, color: 'warning.main', mb: 2 }} />
                      <Typography variant="h6">Yedekleme</Typography>
                      <Typography variant="body2" color="text.secondary">
                        Günlük Otomatik
                      </Typography>
                    </CardContent>
                  </Card>
                </Grid>
              </Grid>
            </Box>
          )}
        </CardContent>
      </Card>

      {/* Edit Marketplace Dialog */}
      <Dialog open={openDialog} onClose={handleDialogClose} maxWidth="sm" fullWidth>
        <DialogTitle>
          {selectedMarketplace?.name} Ayarları
        </DialogTitle>
        <DialogContent>
          {selectedMarketplace && (
            <Box sx={{ pt: 2 }}>
              <TextField
                fullWidth
                label="API Key"
                defaultValue={selectedMarketplace.apiKey}
                margin="normal"
              />
              <TextField
                fullWidth
                label="Secret Key"
                defaultValue={selectedMarketplace.secretKey}
                margin="normal"
              />
              {selectedMarketplace.storeId && (
                <TextField
                  fullWidth
                  label="Store ID"
                  defaultValue={selectedMarketplace.storeId}
                  margin="normal"
                />
              )}
              <FormControl fullWidth margin="normal">
                <InputLabel>Ortam</InputLabel>
                <Select
                  defaultValue={selectedMarketplace.environment}
                  label="Ortam"
                >
                  <MenuItem value="sandbox">Test (Sandbox)</MenuItem>
                  <MenuItem value="production">Canlı (Production)</MenuItem>
                </Select>
              </FormControl>
            </Box>
          )}
        </DialogContent>
        <DialogActions>
          <Button onClick={handleDialogClose}>İptal</Button>
          <Button variant="contained" onClick={handleDialogClose}>
            Kaydet
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
};

export default SettingsPage;
