const express = require('express');
const path = require('path');
const app = express();
const PORT = 3008;

// CORS middleware
app.use((req, res, next) => {
  res.header('Access-Control-Allow-Origin', '*');
  res.header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
  res.header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
  
  if (req.method === 'OPTIONS') {
    res.sendStatus(200);
  } else {
    next();
  }
});

// Static file serving
app.use(express.static(path.join(__dirname)));

// Serve CursorDev files explicitly
app.use('/CursorDev', express.static(path.join(__dirname, 'CursorDev')));

// JSON parsing middleware
app.use(express.json());

// Main dashboard route
app.get('/', (req, res) => {
  try {
    res.sendFile(path.join(__dirname, 'advanced_dashboard_panel.html'));
  } catch (error) {
    console.error('Dashboard dosyası servis hatası:', error);
    res.status(500).json({ 
      error: 'Dashboard yüklenemedi',
      message: error.message,
      timestamp: new Date().toISOString()
    });
  }
});

// Handle CursorDev/dist/html path routing for HTML files
app.get('/CursorDev/dist/html/super_admin_dashboard.html', (req, res) => {
  try {
    res.sendFile(path.join(__dirname, 'CursorDev/FRONTEND_COMPONENTS/super_admin_dashboard.html'));
  } catch (error) {
    console.error('Super Admin dashboard file serving error:', error);
    res.status(500).json({ 
      error: 'Super Admin dashboard file not found',
      message: error.message,
      actualPath: '/CursorDev/FRONTEND_COMPONENTS/super_admin_dashboard.html',
      timestamp: new Date().toISOString()
    });
  }
});

// Handle other dist/html files
app.get('/CursorDev/dist/html/:filename', (req, res) => {
  try {
    const filename = req.params.filename;
    // Map dist/html files to their actual locations
    let actualPath;
    
    if (filename === 'super_admin_dashboard.html') {
      actualPath = path.join(__dirname, 'CursorDev/FRONTEND_COMPONENTS/super_admin_dashboard.html');
    } else if (filename === 'dashboard.html') {
      actualPath = path.join(__dirname, 'CursorDev/FRONTEND_COMPONENTS/dashboard.html');
    } else if (filename === 'admin_dashboard.html') {
      actualPath = path.join(__dirname, 'CursorDev/FRONTEND_COMPONENTS/admin_dashboard.html');
    } else {
      // Try to find in FRONTEND_COMPONENTS first
      actualPath = path.join(__dirname, 'CursorDev/FRONTEND_COMPONENTS/', filename);
    }
    
    res.sendFile(actualPath);
  } catch (error) {
    console.error('HTML file serving error:', error);
    res.status(404).json({ 
      error: 'HTML file not found',
      message: `File ${req.params.filename} not found in expected locations`,
      availableFiles: [
        'super_admin_dashboard.html',
        'dashboard.html', 
        'admin_dashboard.html'
      ],
      timestamp: new Date().toISOString()
    });
  }
});

// Mock API endpoints for dashboard data
app.get('/api/dashboard', (req, res) => {
  try {
    const dashboardData = {
      status: 'success',
      timestamp: new Date().toISOString(),
      data: {
        widgets: {
          total_sales: '₺' + (Math.floor(Math.random() * 50000) + 100000).toLocaleString('tr-TR'),
          active_products: Math.floor(Math.random() * 1000) + 2000,
          pending_orders: Math.floor(Math.random() * 50) + 10,
          api_response_time: Math.floor(Math.random() * 300) + 200 + 'ms'
        },
        charts: {
          sales_trend: {
            labels: generateDateLabels(30),
            datasets: [{
              data: generateRandomData(30, 2000, 8000)
            }]
          },
          marketplace_distribution: {
            labels: ['Trendyol', 'Hepsiburada', 'N11', 'Amazon', 'eBay', 'Ozon'],
            datasets: [{
              data: [35, 25, 15, 12, 8, 5]
            }]
          },
          real_time_orders: {
            labels: generateTimeLabels(24),
            datasets: [{
              data: generateRandomData(24, 1, 15)
            }]
          }
        },
        real_time: {
          system_health: 'excellent',
          uptime: '15 gün 8 saat',
          memory_usage: Math.floor(Math.random() * 30) + 30 + 'MB',
          cpu_usage: Math.floor(Math.random() * 20) + 5 + '%'
        },
        marketplaces: {
          trendyol: { status: 'connected', response_time: '250ms', last_sync: '2 dakika önce' },
          hepsiburada: { status: 'connected', response_time: '180ms', last_sync: '1 dakika önce' },
          n11: { status: 'testing', response_time: '420ms', last_sync: '5 dakika önce' },
          amazon: { status: 'testing', response_time: '380ms', last_sync: '3 dakika önce' },
          ebay: { status: 'disconnected', response_time: 'N/A', last_sync: '15 dakika önce' },
          ozon: { status: 'testing', response_time: '650ms', last_sync: '8 dakika önce' }
        },
        recent_activities: [
          { type: 'order', message: 'Yeni sipariş alındı: #TR-2024-001234', time: '2 dakika önce' },
          { type: 'sync', message: 'Trendyol ürün senkronizasyonu tamamlandı', time: '5 dakika önce' },
          { type: 'product', message: '15 yeni ürün sisteme eklendi', time: '8 dakika önce' },
          { type: 'system', message: 'Sistem performansı optimizasyonu tamamlandı', time: '12 dakika önce' },
          { type: 'order', message: 'Hepsiburada sipariş #HB-987654 işlendi', time: '15 dakika önce' }
        ]
      }
    };
    
    res.json(dashboardData);
  } catch (error) {
    console.error('API veri hatası:', error);
    res.status(500).json({ 
      error: 'API veri sağlanamadı',
      message: error.message 
    });
  }
});

// Marketplace status API
app.get('/api/marketplace-status', (req, res) => {
  try {
    const marketplaceStatus = {
      status: 'success',
      data: {
        trendyol: { 
          status: 'connected', 
          response_time: Math.floor(Math.random() * 200) + 150 + 'ms',
          last_sync: Math.floor(Math.random() * 5) + 1 + ' dakika önce',
          sync_success_rate: Math.floor(Math.random() * 10) + 90 + '%'
        },
        hepsiburada: { 
          status: 'connected', 
          response_time: Math.floor(Math.random() * 200) + 150 + 'ms',
          last_sync: Math.floor(Math.random() * 5) + 1 + ' dakika önce',
          sync_success_rate: Math.floor(Math.random() * 10) + 85 + '%'
        },
        n11: { 
          status: 'testing', 
          response_time: Math.floor(Math.random() * 300) + 300 + 'ms',
          last_sync: Math.floor(Math.random() * 10) + 5 + ' dakika önce',
          sync_success_rate: Math.floor(Math.random() * 15) + 75 + '%'
        },
        amazon: { 
          status: 'testing', 
          response_time: Math.floor(Math.random() * 250) + 250 + 'ms',
          last_sync: Math.floor(Math.random() * 8) + 3 + ' dakika önce',
          sync_success_rate: Math.floor(Math.random() * 12) + 80 + '%'
        },
        ebay: { 
          status: Math.random() > 0.3 ? 'testing' : 'disconnected',
          response_time: Math.random() > 0.3 ? Math.floor(Math.random() * 400) + 400 + 'ms' : 'N/A',
          last_sync: Math.floor(Math.random() * 20) + 10 + ' dakika önce',
          sync_success_rate: Math.random() > 0.3 ? Math.floor(Math.random() * 20) + 70 + '%' : 'N/A'
        },
        ozon: { 
          status: 'testing', 
          response_time: Math.floor(Math.random() * 500) + 500 + 'ms',
          last_sync: Math.floor(Math.random() * 15) + 5 + ' dakika önce',
          sync_success_rate: Math.floor(Math.random() * 20) + 65 + '%'
        }
      }
    };
    
    res.json(marketplaceStatus);
  } catch (error) {
    console.error('Marketplace durum API hatası:', error);
    res.status(500).json({ 
      error: 'Marketplace durumu alınamadı',
      message: error.message 
    });
  }
});

// Performance metrics API
app.get('/api/performance', (req, res) => {
  try {
    const performanceData = {
      status: 'success',
      data: {
        memory_usage: Math.floor(Math.random() * 30) + 30,
        cpu_usage: Math.floor(Math.random() * 20) + 5,
        db_queries: Math.floor(Math.random() * 20) + 15,
        cache_hit_rate: Math.floor(Math.random() * 10) + 85,
        uptime: calculateUptime(),
        response_time: Math.floor(Math.random() * 100) + 50,
        active_connections: Math.floor(Math.random() * 50) + 100,
        system_load: (Math.random() * 2 + 0.5).toFixed(2)
      }
    };
    
    res.json(performanceData);
  } catch (error) {
    console.error('Performans API hatası:', error);
    res.status(500).json({ 
      error: 'Performans verileri alınamadı',
      message: error.message 
    });
  }
});

// Real-time updates API (WebSocket simulation)
app.get('/api/realtime-updates', (req, res) => {
  try {
    const realtimeData = {
      status: 'success',
      timestamp: new Date().toISOString(),
      data: {
        new_orders: Math.floor(Math.random() * 5),
        sync_operations: Math.floor(Math.random() * 3),
        system_alerts: Math.floor(Math.random() * 2),
        active_users: Math.floor(Math.random() * 10) + 5,
        latest_activity: {
          type: ['order', 'sync', 'product', 'system'][Math.floor(Math.random() * 4)],
          message: generateRandomActivity(),
          time: 'Az önce'
        }
      }
    };
    
    res.json(realtimeData);
  } catch (error) {
    console.error('Gerçek zamanlı veri API hatası:', error);
    res.status(500).json({ 
      error: 'Gerçek zamanlı veriler alınamadı',
      message: error.message 
    });
  }
});

// Health check endpoint
app.get('/health', (req, res) => {
  res.json({
    status: 'healthy',
    service: 'MesChain-Sync Advanced Dashboard Server',
    port: PORT,
    version: '3.0.0',
    timestamp: new Date().toISOString(),
    uptime: process.uptime(),
    memory: process.memoryUsage(),
    environment: process.env.NODE_ENV || 'development'
  });
});

// Error handling middleware
app.use((error, req, res, next) => {
  console.error('Server Error:', error);
  res.status(500).json({
    error: 'Internal Server Error',
    message: error.message,
    timestamp: new Date().toISOString()
  });
});

// 404 handler
app.use((req, res) => {
  res.status(404).json({
    error: 'Not Found',
    message: `Route ${req.originalUrl} not found`,
    available_routes: [
      'GET /',
      'GET /api/dashboard',
      'GET /api/marketplace-status',
      'GET /api/performance',
      'GET /api/realtime-updates',
      'GET /health'
    ]
  });
});

// Helper functions
function generateDateLabels(days) {
  const labels = [];
  for (let i = days - 1; i >= 0; i--) {
    const date = new Date();
    date.setDate(date.getDate() - i);
    labels.push(date.toLocaleDateString('tr-TR', { month: 'short', day: 'numeric' }));
  }
  return labels;
}

function generateTimeLabels(hours) {
  const labels = [];
  for (let i = hours - 1; i >= 0; i--) {
    const date = new Date();
    date.setHours(date.getHours() - i);
    labels.push(date.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' }));
  }
  return labels;
}

function generateRandomData(count, min, max) {
  return Array.from({ length: count }, () => Math.floor(Math.random() * (max - min + 1)) + min);
}

function calculateUptime() {
  const uptime = process.uptime();
  const days = Math.floor(uptime / 86400);
  const hours = Math.floor((uptime % 86400) / 3600);
  const minutes = Math.floor((uptime % 3600) / 60);
  return `${days} gün ${hours} saat ${minutes} dakika`;
}

function generateRandomActivity() {
  const activities = [
    'Yeni sipariş alındı: #TR-2024-' + Math.floor(Math.random() * 999999),
    'Ürün senkronizasyonu tamamlandı',
    'Stok güncelleme işlemi başlatıldı',
    'Sistem performansı kontrol edildi',
    'API bağlantısı yenilendi',
    'Veritabanı yedekleme tamamlandı',
    'Kullanıcı girişi kaydedildi',
    'Marketplace durumu güncellendi'
  ];
  return activities[Math.floor(Math.random() * activities.length)];
}

// Start server
const server = app.listen(PORT, () => {
  console.log(`
🚀 MesChain-Sync Advanced Dashboard Server Başlatıldı!
📊 Panel Tipi: Original June 4th Advanced Dashboard
🌐 URL: http://localhost:${PORT}
🔧 Özellikler: 
   ✅ Chart.js Entegrasyonu
   ✅ Gerçek Zamanlı Güncellemeler
   ✅ WebSocket Simülasyonu
   ✅ Marketplace Monitoring
   ✅ Performance Metrics
   ✅ Interactive Charts
   ✅ PWA Ready
   ✅ Mobile Responsive
📡 API Endpoints:
   - GET /               → Ana Dashboard
   - GET /api/dashboard  → Dashboard Verileri
   - GET /api/marketplace-status → Marketplace Durumları
   - GET /api/performance → Performans Metrikleri
   - GET /api/realtime-updates → Gerçek Zamanlı Updates
   - GET /health         → Sunucu Durumu
🕐 Timestamp: ${new Date().toLocaleString('tr-TR')}
  `);
});

// Graceful shutdown
process.on('SIGTERM', () => {
  console.log('🛑 SIGTERM alındı, sunucu kapatılıyor...');
  server.close(() => {
    console.log('✅ Advanced Dashboard Server kapatıldı');
    process.exit(0);
  });
});

process.on('SIGINT', () => {
  console.log('🛑 SIGINT alındı, sunucu kapatılıyor...');
  server.close(() => {
    console.log('✅ Advanced Dashboard Server kapatıldı');
    process.exit(0);
  });
});

module.exports = app;
