// 📊 MesChain-Sync Enterprise - Advanced Reporting Dashboard
// Comprehensive Business Intelligence Reporting System

import React, { useState, useEffect, useMemo, useCallback } from 'react';
import {
  Box,
  Card,
  CardContent,
  Typography,
  Grid,
  Button,
  Fab,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  TextField,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Chip,
  IconButton,
  Tooltip,
  Divider,
  List,
  ListItem,
  ListItemText,
  ListItemSecondaryAction,
  CircularProgress,
  Alert,
  Paper,
  Tab,
  Tabs,
  Switch,
  FormControlLabel,
  Accordion,
  AccordionSummary,
  AccordionDetails,
  Slider,
  Rating,
} from '@mui/material';
import {
  Add,
  Edit,
  Delete,
  Share,
  Schedule,
  FilterList,
  GetApp,
  Visibility,
  ExpandMore,
  Save,
  Refresh,
  PlayArrow,
  Pause,
  Settings,
  Dashboard,
  Assessment,
  TrendingUp,
  PieChart,
  BarChart,
  ShowChart,
  TableView,
  Email,
  Notifications,
  CalendarToday,
  Group,
  Business,
  Analytics,
} from '@mui/icons-material';
import { DatePicker } from '@mui/x-date-pickers/DatePicker';
import { AdapterDateFns } from '@mui/x-date-pickers/AdapterDateFns';
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider';
import { 
  LineChart, 
  Line, 
  AreaChart, 
  Area, 
  BarChart as RechartsBarChart, 
  Bar, 
  PieChart as RechartsPieChart, 
  Pie, 
  Cell,
  XAxis, 
  YAxis, 
  CartesianGrid, 
  Tooltip as RechartsTooltip, 
  ResponsiveContainer,
  Legend,
  ComposedChart,
  Scatter,
  ScatterChart,
} from 'recharts';
import { exportAnalyticsData, ExportData, ExportOptions } from '../../utils/dataExport';

// ====================================
// 🎯 TYPES & INTERFACES
// ====================================

interface ReportConfig {
  id: string;
  name: string;
  description: string;
  type: 'standard' | 'custom' | 'scheduled';
  category: 'sales' | 'marketing' | 'operations' | 'finance' | 'customer';
  template?: string;
  filters: {
    dateRange: { start: Date; end: Date };
    marketplaces: string[];
    categories: string[];
    metrics: string[];
    groupBy: string;
  };
  visualization: {
    chartType: 'line' | 'bar' | 'pie' | 'area' | 'scatter' | 'table';
    showTrends: boolean;
    showComparison: boolean;
    showForecast: boolean;
  };
  scheduling?: {
    enabled: boolean;
    frequency: 'daily' | 'weekly' | 'monthly';
    recipients: string[];
    format: 'pdf' | 'excel' | 'email';
    nextRun?: Date;
  };
  createdAt: Date;
  updatedAt: Date;
  createdBy: string;
  tags: string[];
  isPublic: boolean;
}

interface ReportTemplate {
  id: string;
  name: string;
  description: string;
  category: string;
  thumbnail: string;
  config: Partial<ReportConfig>;
  popularity: number;
  isBuiltIn: boolean;
}

interface AdvancedReportingDashboardProps {
  onReportCreate?: (config: ReportConfig) => void;
  onReportUpdate?: (id: string, config: ReportConfig) => void;
  onReportDelete?: (id: string) => void;
  onReportExport?: (id: string, format: string) => void;
}

// ====================================
// 📊 REPORT TEMPLATES
// ====================================

const REPORT_TEMPLATES: ReportTemplate[] = [
  {
    id: 'sales-overview',
    name: 'Satış Genel Görünüm',
    description: 'Genel satış performansı ve trendleri',
    category: 'sales',
    thumbnail: '📈',
    popularity: 95,
    isBuiltIn: true,
    config: {
      filters: {
        metrics: ['revenue', 'orders', 'averageOrderValue'],
        groupBy: 'date',
      },
      visualization: {
        chartType: 'line',
        showTrends: true,
        showComparison: true,
        showForecast: false,
      },
    },
  },
  {
    id: 'marketplace-comparison',
    name: 'Marketplace Karşılaştırması',
    description: 'Pazaryeri performans karşılaştırması',
    category: 'marketing',
    thumbnail: '🏪',
    popularity: 87,
    isBuiltIn: true,
    config: {
      filters: {
        metrics: ['revenue', 'orders', 'growth'],
        groupBy: 'marketplace',
      },
      visualization: {
        chartType: 'bar',
        showTrends: false,
        showComparison: true,
        showForecast: false,
      },
    },
  },
  {
    id: 'customer-segmentation',
    name: 'Müşteri Segmentasyonu',
    description: 'Müşteri grubu analizi ve davranış raporları',
    category: 'customer',
    thumbnail: '👥',
    popularity: 78,
    isBuiltIn: true,
    config: {
      filters: {
        metrics: ['customerValue', 'retention', 'acquisition'],
        groupBy: 'segment',
      },
      visualization: {
        chartType: 'pie',
        showTrends: false,
        showComparison: false,
        showForecast: false,
      },
    },
  },
  {
    id: 'financial-summary',
    name: 'Finansal Özet',
    description: 'Gelir, gider ve karlılık analizi',
    category: 'finance',
    thumbnail: '💰',
    popularity: 92,
    isBuiltIn: true,
    config: {
      filters: {
        metrics: ['revenue', 'profit', 'margin', 'costs'],
        groupBy: 'month',
      },
      visualization: {
        chartType: 'area',
        showTrends: true,
        showComparison: true,
        showForecast: true,
      },
    },
  },
  {
    id: 'operational-efficiency',
    name: 'Operasyonel Verimlilik',
    description: 'Stok, sipariş işlem ve teslimat metrikleri',
    category: 'operations',
    thumbnail: '⚙️',
    popularity: 73,
    isBuiltIn: true,
    config: {
      filters: {
        metrics: ['orderProcessingTime', 'stockTurnover', 'deliveryTime'],
        groupBy: 'week',
      },
      visualization: {
        chartType: 'scatter',
        showTrends: true,
        showComparison: false,
        showForecast: false,
      },
    },
  },
];

// ====================================
// 📊 MAIN COMPONENT
// ====================================

export const AdvancedReportingDashboard: React.FC<AdvancedReportingDashboardProps> = ({
  onReportCreate,
  onReportUpdate,
  onReportDelete,
  onReportExport,
}) => {
  const [activeTab, setActiveTab] = useState(0);
  const [reports, setReports] = useState<ReportConfig[]>([]);
  const [selectedReport, setSelectedReport] = useState<ReportConfig | null>(null);
  const [isCreateDialogOpen, setIsCreateDialogOpen] = useState(false);
  const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);
  const [isTemplateDialogOpen, setIsTemplateDialogOpen] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const [filterCategory, setFilterCategory] = useState('all');
  const [sortBy, setSortBy] = useState('name');

  // ====================================
  // 🎨 SAMPLE DATA
  // ====================================

  const sampleReports: ReportConfig[] = [
    {
      id: '1',
      name: 'Aylık Satış Raporu',
      description: 'Detaylı aylık satış performans analizi',
      type: 'standard',
      category: 'sales',
      filters: {
        dateRange: { start: new Date(2024, 0, 1), end: new Date() },
        marketplaces: ['trendyol', 'n11'],
        categories: ['electronics', 'fashion'],
        metrics: ['revenue', 'orders'],
        groupBy: 'month',
      },
      visualization: {
        chartType: 'line',
        showTrends: true,
        showComparison: true,
        showForecast: false,
      },
      scheduling: {
        enabled: true,
        frequency: 'monthly',
        recipients: ['admin@meschain.com'],
        format: 'pdf',
        nextRun: new Date(2025, 2, 1),
      },
      createdAt: new Date(2024, 11, 1),
      updatedAt: new Date(2024, 11, 15),
      createdBy: 'Admin User',
      tags: ['monthly', 'sales', 'performance'],
      isPublic: true,
    },
    {
      id: '2',
      name: 'Pazaryeri Karşılaştırma',
      description: 'Tüm pazaryerlerinin detaylı performans analizi',
      type: 'custom',
      category: 'marketing',
      filters: {
        dateRange: { start: new Date(2024, 10, 1), end: new Date() },
        marketplaces: ['trendyol', 'n11', 'amazon', 'hepsiburada'],
        categories: [],
        metrics: ['revenue', 'orders', 'growth'],
        groupBy: 'marketplace',
      },
      visualization: {
        chartType: 'bar',
        showTrends: false,
        showComparison: true,
        showForecast: false,
      },
      createdAt: new Date(2024, 10, 15),
      updatedAt: new Date(2024, 11, 20),
      createdBy: 'Marketing Team',
      tags: ['marketplace', 'comparison', 'growth'],
      isPublic: false,
    },
  ];

  useEffect(() => {
    setReports(sampleReports);
  }, []);

  // ====================================
  // 🔍 FILTERING & SEARCHING
  // ====================================

  const filteredReports = useMemo(() => {
    return reports
      .filter(report => {
        const matchesSearch = report.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                             report.description.toLowerCase().includes(searchTerm.toLowerCase());
        const matchesCategory = filterCategory === 'all' || report.category === filterCategory;
        return matchesSearch && matchesCategory;
      })
      .sort((a, b) => {
        switch (sortBy) {
          case 'name':
            return a.name.localeCompare(b.name);
          case 'created':
            return b.createdAt.getTime() - a.createdAt.getTime();
          case 'updated':
            return b.updatedAt.getTime() - a.updatedAt.getTime();
          default:
            return 0;
        }
      });
  }, [reports, searchTerm, filterCategory, sortBy]);

  // ====================================
  // 📊 CHART RENDERING
  // ====================================

  const renderChart = useCallback((config: ReportConfig, data: any[]) => {
    const { chartType } = config.visualization;
    const chartProps = {
      width: '100%',
      height: 300,
      data,
    };

    switch (chartType) {
      case 'line':
        return (
          <ResponsiveContainer {...chartProps}>
            <LineChart data={data}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="name" />
              <YAxis />
              <RechartsTooltip />
              <Legend />
              <Line type="monotone" dataKey="value" stroke="#0066CC" strokeWidth={2} />
              {config.visualization.showComparison && (
                <Line type="monotone" dataKey="previous" stroke="#FF6B35" strokeDasharray="5 5" />
              )}
            </LineChart>
          </ResponsiveContainer>
        );
      
      case 'bar':
        return (
          <ResponsiveContainer {...chartProps}>
            <RechartsBarChart data={data}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="name" />
              <YAxis />
              <RechartsTooltip />
              <Legend />
              <Bar dataKey="value" fill="#0066CC" />
            </RechartsBarChart>
          </ResponsiveContainer>
        );
      
      case 'pie':
        return (
          <ResponsiveContainer {...chartProps}>
            <RechartsPieChart>
              <Pie
                data={data}
                cx="50%"
                cy="50%"
                outerRadius={100}
                fill="#8884d8"
                dataKey="value"
                label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
              >
                {data.map((entry, index) => (
                  <Cell key={`cell-${index}`} fill={`hsl(${index * 45}, 70%, 50%)`} />
                ))}
              </Pie>
              <RechartsTooltip />
            </RechartsPieChart>
          </ResponsiveContainer>
        );
      
      case 'area':
        return (
          <ResponsiveContainer {...chartProps}>
            <AreaChart data={data}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="name" />
              <YAxis />
              <RechartsTooltip />
              <Legend />
              <Area type="monotone" dataKey="value" stroke="#0066CC" fill="#0066CC" fillOpacity={0.3} />
            </AreaChart>
          </ResponsiveContainer>
        );
      
      default:
        return <Typography>Chart type not supported</Typography>;
    }
  }, []);

  // ====================================
  // 📝 REPORT CREATION DIALOG
  // ====================================

  const ReportCreationDialog = () => {
    const [newReport, setNewReport] = useState<Partial<ReportConfig>>({
      name: '',
      description: '',
      type: 'custom',
      category: 'sales',
      filters: {
        dateRange: { start: new Date(), end: new Date() },
        marketplaces: [],
        categories: [],
        metrics: [],
        groupBy: 'date',
      },
      visualization: {
        chartType: 'line',
        showTrends: true,
        showComparison: false,
        showForecast: false,
      },
      tags: [],
      isPublic: false,
    });

    const handleCreate = () => {
      const report: ReportConfig = {
        ...newReport,
        id: Date.now().toString(),
        createdAt: new Date(),
        updatedAt: new Date(),
        createdBy: 'Current User',
      } as ReportConfig;

      setReports(prev => [...prev, report]);
      onReportCreate?.(report);
      setIsCreateDialogOpen(false);
    };

    return (
      <Dialog open={isCreateDialogOpen} onClose={() => setIsCreateDialogOpen(false)} maxWidth="md" fullWidth>
        <DialogTitle>Yeni Rapor Oluştur</DialogTitle>
        <DialogContent>
          <Grid container spacing={3}>
            <Grid item xs={12} md={6}>
              <TextField
                fullWidth
                label="Rapor Adı"
                value={newReport.name}
                onChange={(e) => setNewReport(prev => ({ ...prev, name: e.target.value }))}
                margin="normal"
              />
            </Grid>
            <Grid item xs={12} md={6}>
              <FormControl fullWidth margin="normal">
                <InputLabel>Kategori</InputLabel>
                <Select
                  value={newReport.category}
                  onChange={(e) => setNewReport(prev => ({ ...prev, category: e.target.value as any }))}
                  label="Kategori"
                >
                  <MenuItem value="sales">Satış</MenuItem>
                  <MenuItem value="marketing">Pazarlama</MenuItem>
                  <MenuItem value="operations">Operasyon</MenuItem>
                  <MenuItem value="finance">Finans</MenuItem>
                  <MenuItem value="customer">Müşteri</MenuItem>
                </Select>
              </FormControl>
            </Grid>
            <Grid item xs={12}>
              <TextField
                fullWidth
                label="Açıklama"
                multiline
                rows={3}
                value={newReport.description}
                onChange={(e) => setNewReport(prev => ({ ...prev, description: e.target.value }))}
                margin="normal"
              />
            </Grid>
            <Grid item xs={12} md={6}>
              <FormControl fullWidth margin="normal">
                <InputLabel>Grafik Türü</InputLabel>
                <Select
                  value={newReport.visualization?.chartType}
                  onChange={(e) => setNewReport(prev => ({
                    ...prev,
                    visualization: { ...prev.visualization!, chartType: e.target.value as any }
                  }))}
                  label="Grafik Türü"
                >
                  <MenuItem value="line">Çizgi Grafik</MenuItem>
                  <MenuItem value="bar">Bar Grafik</MenuItem>
                  <MenuItem value="pie">Pasta Grafik</MenuItem>
                  <MenuItem value="area">Alan Grafik</MenuItem>
                  <MenuItem value="scatter">Dağılım Grafik</MenuItem>
                </Select>
              </FormControl>
            </Grid>
            <Grid item xs={12} md={6}>
              <FormControlLabel
                control={
                  <Switch
                    checked={newReport.visualization?.showTrends}
                    onChange={(e) => setNewReport(prev => ({
                      ...prev,
                      visualization: { ...prev.visualization!, showTrends: e.target.checked }
                    }))}
                  />
                }
                label="Trend Analizi"
              />
            </Grid>
          </Grid>
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setIsCreateDialogOpen(false)}>İptal</Button>
          <Button onClick={handleCreate} variant="contained">Oluştur</Button>
        </DialogActions>
      </Dialog>
    );
  };

  // ====================================
  // 📋 TEMPLATE GALLERY
  // ====================================

  const TemplateGallery = () => (
    <Dialog open={isTemplateDialogOpen} onClose={() => setIsTemplateDialogOpen(false)} maxWidth="lg" fullWidth>
      <DialogTitle>Rapor Şablonları</DialogTitle>
      <DialogContent>
        <Grid container spacing={3}>
          {REPORT_TEMPLATES.map((template) => (
            <Grid item xs={12} sm={6} md={4} key={template.id}>
              <Card 
                sx={{ 
                  height: '100%', 
                  cursor: 'pointer',
                  '&:hover': { elevation: 4 }
                }}
                onClick={() => {
                  // Create report from template
                  const newReport: ReportConfig = {
                    ...template.config,
                    id: Date.now().toString(),
                    name: template.name,
                    description: template.description,
                    type: 'standard',
                    category: template.category as any,
                    createdAt: new Date(),
                    updatedAt: new Date(),
                    createdBy: 'Current User',
                    tags: [],
                    isPublic: false,
                  } as ReportConfig;
                  
                  setReports(prev => [...prev, newReport]);
                  onReportCreate?.(newReport);
                  setIsTemplateDialogOpen(false);
                }}
              >
                <CardContent>
                  <Box textAlign="center" mb={2}>
                    <Typography variant="h2">{template.thumbnail}</Typography>
                  </Box>
                  <Typography variant="h6" gutterBottom>
                    {template.name}
                  </Typography>
                  <Typography variant="body2" color="text.secondary" paragraph>
                    {template.description}
                  </Typography>
                  <Box display="flex" justifyContent="space-between" alignItems="center">
                    <Chip label={template.category} size="small" />
                    <Box display="flex" alignItems="center">
                      <Rating value={template.popularity / 20} readOnly size="small" />
                      <Typography variant="caption" ml={1}>
                        {template.popularity}%
                      </Typography>
                    </Box>
                  </Box>
                </CardContent>
              </Card>
            </Grid>
          ))}
        </Grid>
      </DialogContent>
      <DialogActions>
        <Button onClick={() => setIsTemplateDialogOpen(false)}>Kapat</Button>
      </DialogActions>
    </Dialog>
  );

  // ====================================
  // 📊 REPORTS LIST TAB
  // ====================================

  const ReportsListTab = () => (
    <Box>
      {/* Filters */}
      <Paper sx={{ p: 2, mb: 3 }}>
        <Grid container spacing={2} alignItems="center">
          <Grid item xs={12} md={4}>
            <TextField
              fullWidth
              placeholder="Rapor ara..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              size="small"
            />
          </Grid>
          <Grid item xs={12} md={3}>
            <FormControl fullWidth size="small">
              <InputLabel>Kategori</InputLabel>
              <Select
                value={filterCategory}
                onChange={(e) => setFilterCategory(e.target.value)}
                label="Kategori"
              >
                <MenuItem value="all">Tümü</MenuItem>
                <MenuItem value="sales">Satış</MenuItem>
                <MenuItem value="marketing">Pazarlama</MenuItem>
                <MenuItem value="operations">Operasyon</MenuItem>
                <MenuItem value="finance">Finans</MenuItem>
                <MenuItem value="customer">Müşteri</MenuItem>
              </Select>
            </FormControl>
          </Grid>
          <Grid item xs={12} md={3}>
            <FormControl fullWidth size="small">
              <InputLabel>Sırala</InputLabel>
              <Select
                value={sortBy}
                onChange={(e) => setSortBy(e.target.value)}
                label="Sırala"
              >
                <MenuItem value="name">İsim</MenuItem>
                <MenuItem value="created">Oluşturma Tarihi</MenuItem>
                <MenuItem value="updated">Güncelleme Tarihi</MenuItem>
              </Select>
            </FormControl>
          </Grid>
          <Grid item xs={12} md={2}>
            <Button
              fullWidth
              variant="contained"
              startIcon={<Add />}
              onClick={() => setIsCreateDialogOpen(true)}
            >
              Yeni Rapor
            </Button>
          </Grid>
        </Grid>
      </Paper>

      {/* Reports Grid */}
      <Grid container spacing={3}>
        {filteredReports.map((report) => (
          <Grid item xs={12} md={6} lg={4} key={report.id}>
            <Card>
              <CardContent>
                <Box display="flex" justifyContent="space-between" alignItems="flex-start" mb={2}>
                  <Typography variant="h6" component="div">
                    {report.name}
                  </Typography>
                  <IconButton size="small">
                    <Edit />
                  </IconButton>
                </Box>
                
                <Typography variant="body2" color="text.secondary" paragraph>
                  {report.description}
                </Typography>

                <Box mb={2}>
                  {report.tags.map((tag) => (
                    <Chip key={tag} label={tag} size="small" sx={{ mr: 0.5, mb: 0.5 }} />
                  ))}
                </Box>

                {/* Sample Chart */}
                <Box height={200} mb={2}>
                  {renderChart(report, [
                    { name: 'Jan', value: 4000, previous: 3500 },
                    { name: 'Feb', value: 3000, previous: 2800 },
                    { name: 'Mar', value: 2000, previous: 2200 },
                    { name: 'Apr', value: 2780, previous: 2600 },
                    { name: 'May', value: 1890, previous: 1700 },
                    { name: 'Jun', value: 2390, previous: 2100 },
                  ])}
                </Box>

                <Box display="flex" justifyContent="space-between" alignItems="center">
                  <Typography variant="caption" color="text.secondary">
                    {report.updatedAt.toLocaleDateString()}
                  </Typography>
                  <Box>
                    <Tooltip title="Görüntüle">
                      <IconButton size="small">
                        <Visibility />
                      </IconButton>
                    </Tooltip>
                    <Tooltip title="Export">
                      <IconButton size="small">
                        <GetApp />
                      </IconButton>
                    </Tooltip>
                    <Tooltip title="Paylaş">
                      <IconButton size="small">
                        <Share />
                      </IconButton>
                    </Tooltip>
                  </Box>
                </Box>
              </CardContent>
            </Card>
          </Grid>
        ))}
      </Grid>
    </Box>
  );

  // ====================================
  // 📅 SCHEDULED REPORTS TAB
  // ====================================

  const ScheduledReportsTab = () => {
    const scheduledReports = reports.filter(report => report.scheduling?.enabled);

    return (
      <Box>
        <Box display="flex" justifyContent="space-between" alignItems="center" mb={3}>
          <Typography variant="h6">Zamanlanmış Raporlar</Typography>
          <Button
            variant="contained"
            startIcon={<Schedule />}
            onClick={() => setIsCreateDialogOpen(true)}
          >
            Yeni Zamanlama
          </Button>
        </Box>

        <List>
          {scheduledReports.map((report) => (
            <Card key={report.id} sx={{ mb: 2 }}>
              <ListItem>
                <ListItemText
                  primary={report.name}
                  secondary={
                    <Box>
                      <Typography variant="body2" component="div">
                        Sıklık: {report.scheduling?.frequency}
                      </Typography>
                      <Typography variant="body2" component="div">
                        Sonraki çalışma: {report.scheduling?.nextRun?.toLocaleDateString()}
                      </Typography>
                      <Typography variant="body2" component="div">
                        Alıcılar: {report.scheduling?.recipients.join(', ')}
                      </Typography>
                    </Box>
                  }
                />
                <ListItemSecondaryAction>
                  <IconButton edge="end">
                    <PlayArrow />
                  </IconButton>
                  <IconButton edge="end">
                    <Edit />
                  </IconButton>
                  <IconButton edge="end">
                    <Delete />
                  </IconButton>
                </ListItemSecondaryAction>
              </ListItem>
            </Card>
          ))}
        </List>
      </Box>
    );
  };

  // ====================================
  // 🎯 MAIN RENDER
  // ====================================

  return (
    <LocalizationProvider dateAdapter={AdapterDateFns}>
      <Box p={3}>
        {/* Header */}
        <Box display="flex" justifyContent="space-between" alignItems="center" mb={3}>
          <Box>
            <Typography variant="h4" gutterBottom>
              Gelişmiş Raporlama
            </Typography>
            <Typography variant="body1" color="text.secondary">
              Kapsamlı iş zekası raporları ve analiz
            </Typography>
          </Box>
          <Box display="flex" gap={1}>
            <Button
              variant="outlined"
              startIcon={<Dashboard />}
              onClick={() => setIsTemplateDialogOpen(true)}
            >
              Şablonlar
            </Button>
            <Button
              variant="contained"
              startIcon={<Add />}
              onClick={() => setIsCreateDialogOpen(true)}
            >
              Yeni Rapor
            </Button>
          </Box>
        </Box>

        {/* Loading */}
        {isLoading && <CircularProgress sx={{ mb: 2 }} />}

        {/* Tabs */}
        <Paper sx={{ mb: 3 }}>
          <Tabs value={activeTab} onChange={(e, newValue) => setActiveTab(newValue)}>
            <Tab icon={<Assessment />} label="Raporlar" />
            <Tab icon={<Schedule />} label="Zamanlanmış" />
            <Tab icon={<Analytics />} label="İstatistikler" />
          </Tabs>
        </Paper>

        {/* Tab Content */}
        {activeTab === 0 && <ReportsListTab />}
        {activeTab === 1 && <ScheduledReportsTab />}
        {activeTab === 2 && (
          <Alert severity="info">
            İstatistik paneli yakında eklenecek...
          </Alert>
        )}

        {/* Dialogs */}
        <ReportCreationDialog />
        <TemplateGallery />

        {/* Floating Action Button */}
        <Fab
          color="primary"
          sx={{ position: 'fixed', bottom: 16, right: 16 }}
          onClick={() => setIsCreateDialogOpen(true)}
        >
          <Add />
        </Fab>
      </Box>
    </LocalizationProvider>
  );
};

export default AdvancedReportingDashboard; 