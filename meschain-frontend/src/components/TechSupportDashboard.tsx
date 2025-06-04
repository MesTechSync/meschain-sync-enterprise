import React, { useState, useEffect } from 'react';
// import { useTranslation } from 'react-i18next'; // Removed unused translation
import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  PieChart,
  Pie,
  Cell,
  AreaChart,
  Area
} from 'recharts';

interface SystemHealth {
  cpu: number;
  memory: number;
  disk: number;
  network: number;
  database: number;
  apiHealth: number;
}

interface SupportTicket {
  id: number;
  title: string;
  user: string;
  priority: 'low' | 'medium' | 'high' | 'critical';
  status: 'open' | 'in_progress' | 'resolved' | 'closed';
  category: 'api' | 'integration' | 'performance' | 'bug' | 'feature';
  createdAt: string;
  assignedTo?: string;
  marketplace?: string;
}

interface ErrorLog {
  id: number;
  timestamp: string;
  level: 'error' | 'warning' | 'critical';
  message: string;
  source: string;
  marketplace?: string;
  userId?: number;
  stackTrace?: string;
  resolved: boolean;
}

interface PerformanceMetric {
  timestamp: string;
  responseTime: number;
  throughput: number;
  errorRate: number;
  activeUsers: number;
}

const TechSupportDashboard: React.FC = () => {
  const [systemHealth, setSystemHealth] = useState<SystemHealth>({
    cpu: 0,
    memory: 0,
    disk: 0,
    network: 0,
    database: 0,
    apiHealth: 0
  });
  
  const [supportTickets, setSupportTickets] = useState<SupportTicket[]>([]);
  const [errorLogs, setErrorLogs] = useState<ErrorLog[]>([]);
  const [performanceMetrics, setPerformanceMetrics] = useState<PerformanceMetric[]>([]);
  const [selectedTimeRange, setSelectedTimeRange] = useState<'1h' | '24h' | '7d' | '30d'>('24h');

  useEffect(() => {
    // Mock data - gerçek API'larla değiştirilecek
    setSystemHealth({
      cpu: 68.5,
      memory: 74.2,
      disk: 45.8,
      network: 89.3,
      database: 92.1,
      apiHealth: 96.7
    });

    setSupportTickets([
      {
        id: 1,
        title: 'Trendyol API bağlantı hatası',
        user: 'Ahmet Yılmaz',
        priority: 'high',
        status: 'in_progress',
        category: 'api',
        createdAt: '2025-06-02 14:30',
        assignedTo: 'Mehmet Kaya',
        marketplace: 'Trendyol'
      },
      {
        id: 2,
        title: 'Amazon ürün senkronizasyonu yavaş',
        user: 'Fatma Demir',
        priority: 'medium',
        status: 'open',
        category: 'performance',
        createdAt: '2025-06-02 13:15',
        marketplace: 'Amazon'
      },
      {
        id: 3,
        title: 'N11 sipariş durumu güncellenmiyor',
        user: 'Ali Özkan',
        priority: 'critical',
        status: 'open',
        category: 'integration',
        createdAt: '2025-06-02 12:45',
        marketplace: 'N11'
      },
      {
        id: 4,
        title: 'Dashboard yükleme sorunu',
        user: 'Zeynep Kara',
        priority: 'low',
        status: 'resolved',
        category: 'bug',
        createdAt: '2025-06-02 11:20',
        assignedTo: 'Ayşe Yıldız'
      },
      {
        id: 5,
        title: 'Toplu ürün yükleme özelliği talebi',
        user: 'Mustafa Çelik',
        priority: 'medium',
        status: 'open',
        category: 'feature',
        createdAt: '2025-06-02 10:30'
      }
    ]);

    setErrorLogs([
      {
        id: 1,
        timestamp: '2025-06-02 15:45:23',
        level: 'critical',
        message: 'Database connection pool exhausted',
        source: 'DatabaseManager',
        resolved: false
      },
      {
        id: 2,
        timestamp: '2025-06-02 15:42:15',
        level: 'error',
        message: 'Trendyol API rate limit exceeded',
        source: 'TrendyolAPI',
        marketplace: 'Trendyol',
        userId: 123,
        resolved: true
      },
      {
        id: 3,
        timestamp: '2025-06-02 15:38:45',
        level: 'warning',
        message: 'High memory usage detected (85%)',
        source: 'SystemMonitor',
        resolved: false
      },
      {
        id: 4,
        timestamp: '2025-06-02 15:35:12',
        level: 'error',
        message: 'Amazon SP-API authentication failed',
        source: 'AmazonAPI',
        marketplace: 'Amazon',
        userId: 456,
        resolved: true
      },
      {
        id: 5,
        timestamp: '2025-06-02 15:30:08',
        level: 'critical',
        message: 'N11 webhook endpoint unreachable',
        source: 'N11Webhook',
        marketplace: 'N11',
        resolved: false
      }
    ]);

    setPerformanceMetrics([
      { timestamp: '14:00', responseTime: 245, throughput: 1250, errorRate: 2.1, activeUsers: 89 },
      { timestamp: '14:15', responseTime: 230, throughput: 1340, errorRate: 1.8, activeUsers: 92 },
      { timestamp: '14:30', responseTime: 280, throughput: 1180, errorRate: 3.2, activeUsers: 87 },
      { timestamp: '14:45', responseTime: 320, throughput: 1050, errorRate: 4.5, activeUsers: 85 },
      { timestamp: '15:00', responseTime: 290, throughput: 1220, errorRate: 2.8, activeUsers: 91 },
      { timestamp: '15:15', responseTime: 260, throughput: 1380, errorRate: 1.9, activeUsers: 94 },
      { timestamp: '15:30', responseTime: 275, throughput: 1290, errorRate: 2.3, activeUsers: 88 },
      { timestamp: '15:45', responseTime: 240, throughput: 1420, errorRate: 1.5, activeUsers: 96 }
    ]);
  }, []);

  const getPriorityColor = (priority: string) => {
    const colors = {
      low: 'bg-blue-100 text-blue-800',
      medium: 'bg-yellow-100 text-yellow-800',
      high: 'bg-orange-100 text-orange-800',
      critical: 'bg-red-100 text-red-800'
    };
    return colors[priority as keyof typeof colors] || 'bg-gray-100 text-gray-800';
  };

  const getStatusColor = (status: string) => {
    const colors = {
      open: 'bg-red-100 text-red-800',
      in_progress: 'bg-yellow-100 text-yellow-800',
      resolved: 'bg-green-100 text-green-800',
      closed: 'bg-gray-100 text-gray-800'
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
  };

  const getLogLevelColor = (level: string) => {
    const colors = {
      warning: 'bg-yellow-100 text-yellow-800',
      error: 'bg-red-100 text-red-800',
      critical: 'bg-red-200 text-red-900 font-bold'
    };
    return colors[level as keyof typeof colors] || 'bg-gray-100 text-gray-800';
  };

  const getHealthColor = (value: number) => {
    if (value >= 90) return '#10B981'; // Green
    if (value >= 70) return '#F59E0B'; // Yellow
    return '#EF4444'; // Red
  };

  // Grafik verileri
  const ticketsByCategory = [
    { name: 'API', count: 8, color: '#3B82F6' },
    { name: 'Entegrasyon', count: 5, color: '#8B5CF6' },
    { name: 'Performans', count: 3, color: '#F59E0B' },
    { name: 'Bug', count: 7, color: '#EF4444' },
    { name: 'Özellik', count: 2, color: '#10B981' }
  ];

  const ticketsByPriority = [
    { name: 'Düşük', value: 6, color: '#3B82F6' },
    { name: 'Orta', value: 12, color: '#F59E0B' },
    { name: 'Yüksek', value: 5, color: '#EF4444' },
    { name: 'Kritik', value: 2, color: '#7C2D12' }
  ];

  const systemHealthData = [
    { name: 'CPU', value: systemHealth.cpu, color: getHealthColor(systemHealth.cpu) },
    { name: 'Bellek', value: systemHealth.memory, color: getHealthColor(systemHealth.memory) },
    { name: 'Disk', value: systemHealth.disk, color: getHealthColor(systemHealth.disk) },
    { name: 'Ağ', value: systemHealth.network, color: getHealthColor(systemHealth.network) },
    { name: 'Veritabanı', value: systemHealth.database, color: getHealthColor(systemHealth.database) },
    { name: 'API', value: systemHealth.apiHealth, color: getHealthColor(systemHealth.apiHealth) }
  ];

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Teknik Servis Paneli</h1>
          <p className="text-sm text-gray-500 mt-1">Sistem izleme, hata takibi ve kullanıcı desteği</p>
        </div>
        <div className="flex space-x-2">
          <select
            value={selectedTimeRange}
            onChange={(e) => setSelectedTimeRange(e.target.value as '1h' | '24h' | '7d' | '30d')}
            className="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm"
          >
            <option value="1h">Son 1 Saat</option>
            <option value="24h">Son 24 Saat</option>
            <option value="7d">Son 7 Gün</option>
            <option value="30d">Son 30 Gün</option>
          </select>
          <button className="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
            <span>🚨</span>
            <span>Acil Durum</span>
          </button>
          <button className="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
            <span>🔄</span>
            <span>Yenile</span>
          </button>
        </div>
      </div>

      {/* System Health Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
        {systemHealthData.map((metric, index) => (
          <div key={index} className="bg-white rounded-lg shadow-md p-4 border-l-4" style={{ borderLeftColor: metric.color }}>
            <div className="text-center">
              <div className="text-sm font-medium text-gray-600 mb-2">{metric.name}</div>
              <div className="text-2xl font-bold" style={{ color: metric.color }}>
                {metric.value}%
              </div>
              <div className="mt-2">
                <div className="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    className="h-2 rounded-full transition-all duration-300" 
                    style={{ 
                      width: `${metric.value}%`,
                      backgroundColor: metric.color
                    }}
                  ></div>
                </div>
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* Performance Metrics Chart */}
      <div className="bg-white rounded-lg shadow-md p-6">
        <h2 className="text-lg font-semibold text-gray-900 mb-4">Sistem Performans Metrikleri</h2>
        <ResponsiveContainer width="100%" height={300}>
          <AreaChart data={performanceMetrics}>
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis dataKey="timestamp" />
            <YAxis />
            <Tooltip />
            <Area type="monotone" dataKey="responseTime" stackId="1" stroke="#3B82F6" fill="#3B82F6" fillOpacity={0.6} name="Yanıt Süresi (ms)" />
            <Area type="monotone" dataKey="throughput" stackId="2" stroke="#10B981" fill="#10B981" fillOpacity={0.6} name="İşlem Hacmi" />
            <Area type="monotone" dataKey="errorRate" stackId="3" stroke="#EF4444" fill="#EF4444" fillOpacity={0.6} name="Hata Oranı (%)" />
          </AreaChart>
        </ResponsiveContainer>
      </div>

      {/* Support Tickets & Error Logs */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Support Tickets */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex justify-between items-center mb-4">
            <h2 className="text-lg font-semibold text-gray-900">Destek Talepleri</h2>
            <button className="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
              + Yeni Talep
            </button>
          </div>
          <div className="space-y-3 max-h-96 overflow-y-auto">
            {supportTickets.map((ticket) => (
              <div key={ticket.id} className="p-4 bg-gray-50 rounded-lg">
                <div className="flex items-center justify-between mb-2">
                  <div className="flex items-center space-x-2">
                    <span className={`px-2 py-1 rounded-full text-xs ${getPriorityColor(ticket.priority)}`}>
                      {ticket.priority}
                    </span>
                    <span className={`px-2 py-1 rounded-full text-xs ${getStatusColor(ticket.status)}`}>
                      {ticket.status}
                    </span>
                  </div>
                  <span className="text-xs text-gray-500">#{ticket.id}</span>
                </div>
                <h3 className="font-medium text-gray-900 mb-1">{ticket.title}</h3>
                <div className="text-sm text-gray-600 mb-2">
                  <span>Kullanıcı: {ticket.user}</span>
                  {ticket.marketplace && <span className="ml-2">• {ticket.marketplace}</span>}
                </div>
                <div className="flex justify-between items-center text-xs text-gray-500">
                  <span>{ticket.createdAt}</span>
                  {ticket.assignedTo && <span>Atanan: {ticket.assignedTo}</span>}
                </div>
                <div className="mt-2 flex space-x-1">
                  <button className="text-blue-600 hover:text-blue-800 text-xs">Görüntüle</button>
                  <button className="text-green-600 hover:text-green-800 text-xs">Atama</button>
                  <button className="text-orange-600 hover:text-orange-800 text-xs">Güncelle</button>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Error Logs */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex justify-between items-center mb-4">
            <h2 className="text-lg font-semibold text-gray-900">Sistem Hataları</h2>
            <button className="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm">
              Tümünü Gör
            </button>
          </div>
          <div className="space-y-3 max-h-96 overflow-y-auto">
            {errorLogs.map((log) => (
              <div key={log.id} className="p-3 bg-gray-50 rounded-lg">
                <div className="flex items-center justify-between mb-2">
                  <span className={`px-2 py-1 rounded-full text-xs ${getLogLevelColor(log.level)}`}>
                    {log.level.toUpperCase()}
                  </span>
                  <div className="flex items-center space-x-2">
                    {log.resolved ? (
                      <span className="text-green-600 text-xs">✅ Çözüldü</span>
                    ) : (
                      <span className="text-red-600 text-xs">❌ Açık</span>
                    )}
                    <span className="text-xs text-gray-500">{log.timestamp}</span>
                  </div>
                </div>
                <p className="text-sm text-gray-800 mb-1">{log.message}</p>
                <div className="flex justify-between items-center text-xs text-gray-500">
                  <span>Kaynak: {log.source}</span>
                  {log.marketplace && <span>Pazaryeri: {log.marketplace}</span>}
                </div>
                {!log.resolved && (
                  <div className="mt-2 flex space-x-1">
                    <button className="text-blue-600 hover:text-blue-800 text-xs">Detay</button>
                    <button className="text-green-600 hover:text-green-800 text-xs">Çöz</button>
                    <button className="text-orange-600 hover:text-orange-800 text-xs">Atama</button>
                  </div>
                )}
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Analytics Charts */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Tickets by Category */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Kategoriye Göre Talepler</h2>
          <ResponsiveContainer width="100%" height={250}>
            <BarChart data={ticketsByCategory}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="name" />
              <YAxis />
              <Tooltip />
              <Bar dataKey="count" fill="#3B82F6" />
            </BarChart>
          </ResponsiveContainer>
        </div>

        {/* Tickets by Priority */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Önceliğe Göre Talepler</h2>
          <ResponsiveContainer width="100%" height={250}>
            <PieChart>
              <Pie
                data={ticketsByPriority}
                cx="50%"
                cy="50%"
                labelLine={false}
                label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                outerRadius={80}
                fill="#8884d8"
                dataKey="value"
              >
                {ticketsByPriority.map((entry, index) => (
                  <Cell key={`cell-${index}`} fill={entry.color} />
                ))}
              </Pie>
              <Tooltip />
            </PieChart>
          </ResponsiveContainer>
        </div>
      </div>

      {/* Quick Actions */}
      <div className="bg-white rounded-lg shadow-md p-6">
        <h2 className="text-lg font-semibold text-gray-900 mb-4">Hızlı İşlemler</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <button className="bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg p-4 text-left transition-colors">
            <div className="text-blue-600 text-2xl mb-2">🔧</div>
            <div className="font-medium text-blue-900">Sistem Bakımı</div>
            <div className="text-sm text-blue-600">Rutin bakım işlemleri</div>
          </button>
          
          <button className="bg-green-50 hover:bg-green-100 border border-green-200 rounded-lg p-4 text-left transition-colors">
            <div className="text-green-600 text-2xl mb-2">📊</div>
            <div className="font-medium text-green-900">Performans Raporu</div>
            <div className="text-sm text-green-600">Detaylı analiz oluştur</div>
          </button>
          
          <button className="bg-yellow-50 hover:bg-yellow-100 border border-yellow-200 rounded-lg p-4 text-left transition-colors">
            <div className="text-yellow-600 text-2xl mb-2">⚠️</div>
            <div className="font-medium text-yellow-900">Uyarı Kuralları</div>
            <div className="text-sm text-yellow-600">Alarm ayarlarını yönet</div>
          </button>
          
          <button className="bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg p-4 text-left transition-colors">
            <div className="text-red-600 text-2xl mb-2">🚨</div>
            <div className="font-medium text-red-900">Acil Müdahale</div>
            <div className="text-sm text-red-600">Kritik sorun çözümü</div>
          </button>
        </div>
      </div>
    </div>
  );
};

export default TechSupportDashboard; 