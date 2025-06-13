import React, { useState, useEffect } from 'react';
import {
  BarChart,
  Bar,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  ResponsiveContainer,
  LineChart,
  Line,
  PieChart,
  Pie,
  Cell
} from 'recharts';

interface AdminStats {
  totalUsers: number;
  activeMarketplaces: number;
  totalApiCalls: number;
  systemHealth: number;
  dailyRevenue: number;
  monthlyGrowth: number;
}

interface UserManagement {
  id: number;
  name: string;
  email: string;
  role: 'super_admin' | 'admin' | 'integrator' | 'tech_support' | 'dropshipper';
  status: 'active' | 'inactive' | 'suspended';
  lastLogin: string;
  marketplaceAccess: string[];
}

interface SystemLog {
  id: number;
  timestamp: string;
  level: 'info' | 'warning' | 'error' | 'critical';
  message: string;
  source: string;
  userId?: number;
}

const AdminDashboard: React.FC = () => {
  const [stats, setStats] = useState<AdminStats>({
    totalUsers: 0,
    activeMarketplaces: 0,
    totalApiCalls: 0,
    systemHealth: 0,
    dailyRevenue: 0,
    monthlyGrowth: 0
  });

  const [users, setUsers] = useState<UserManagement[]>([]);
  const [systemLogs, setSystemLogs] = useState<SystemLog[]>([]);
  const [selectedTimeRange, setSelectedTimeRange] = useState<'24h' | '7d' | '30d'>('24h');

  // API çağrıları için mock data - gerçek API'larla değiştirilecek
  useEffect(() => {
    // Sistem istatistiklerini yükle
    setStats({
      totalUsers: 156,
      activeMarketplaces: 6,
      totalApiCalls: 45230,
      systemHealth: 98.5,
      dailyRevenue: 125000,
      monthlyGrowth: 23.5
    });

    // Kullanıcı listesini yükle
    setUsers([
      {
        id: 1,
        name: 'Ahmet Yılmaz',
        email: 'ahmet@example.com',
        role: 'admin',
        status: 'active',
        lastLogin: '2025-06-02 14:30',
        marketplaceAccess: ['Trendyol', 'N11', 'Amazon']
      },
      {
        id: 2,
        name: 'Fatma Demir',
        email: 'fatma@example.com',
        role: 'dropshipper',
        status: 'active',
        lastLogin: '2025-06-02 13:15',
        marketplaceAccess: ['Trendyol', 'Hepsiburada']
      },
      {
        id: 3,
        name: 'Mehmet Kaya',
        email: 'mehmet@example.com',
        role: 'integrator',
        status: 'inactive',
        lastLogin: '2025-06-01 09:45',
        marketplaceAccess: ['Amazon', 'eBay']
      }
    ]);

    // Sistem loglarını yükle
    setSystemLogs([
      {
        id: 1,
        timestamp: '2025-06-02 15:30:25',
        level: 'info',
        message: 'Trendyol API senkronizasyonu başarıyla tamamlandı',
        source: 'TrendyolAPI',
        userId: 1
      },
      {
        id: 2,
        timestamp: '2025-06-02 15:28:10',
        level: 'warning',
        message: 'Amazon API rate limit yaklaşıldı (%85)',
        source: 'AmazonAPI'
      },
      {
        id: 3,
        timestamp: '2025-06-02 15:25:45',
        level: 'error',
        message: 'N11 API bağlantı hatası - 3 kez yeniden denendi',
        source: 'N11API'
      },
      {
        id: 4,
        timestamp: '2025-06-02 15:20:12',
        level: 'critical',
        message: 'Sistem bellek kullanımı %90 seviyesinde',
        source: 'SystemMonitor'
      }
    ]);
  }, []);

  // Grafik verileri
  const apiUsageData = [
    { name: 'Trendyol', calls: 12500, success: 98.5 },
    { name: 'Amazon', calls: 8900, success: 97.2 },
    { name: 'N11', calls: 7800, success: 95.8 },
    { name: 'Hepsiburada', calls: 6200, success: 96.1 },
    { name: 'eBay', calls: 5400, success: 94.3 },
    { name: 'Ozon', calls: 4430, success: 92.7 }
  ];

  const systemPerformanceData = [
    { time: '00:00', cpu: 45, memory: 62, network: 78 },
    { time: '04:00', cpu: 38, memory: 58, network: 65 },
    { time: '08:00', cpu: 72, memory: 75, network: 89 },
    { time: '12:00', cpu: 85, memory: 82, network: 95 },
    { time: '16:00', cpu: 78, memory: 79, network: 88 },
    { time: '20:00', cpu: 65, memory: 71, network: 82 }
  ];

  const userRoleDistribution = [
    { name: 'Dropshipper', value: 89, color: '#10B981' },
    { name: 'Admin', value: 34, color: '#3B82F6' },
    { name: 'Integrator', value: 23, color: '#8B5CF6' },
    { name: 'Tech Support', value: 8, color: '#F59E0B' },
    { name: 'Super Admin', value: 2, color: '#EF4444' }
  ];

  const getRoleColor = (role: string) => {
    const colors = {
      super_admin: 'bg-red-100 text-red-800',
      admin: 'bg-blue-100 text-blue-800',
      integrator: 'bg-purple-100 text-purple-800',
      tech_support: 'bg-yellow-100 text-yellow-800',
      dropshipper: 'bg-green-100 text-green-800'
    };
    return colors[role as keyof typeof colors] || 'bg-gray-100 text-gray-800';
  };

  const getStatusColor = (status: string) => {
    const colors = {
      active: 'bg-green-100 text-green-800',
      inactive: 'bg-gray-100 text-gray-800',
      suspended: 'bg-red-100 text-red-800'
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
  };

  const getLogLevelColor = (level: string) => {
    const colors = {
      info: 'bg-blue-100 text-blue-800',
      warning: 'bg-yellow-100 text-yellow-800',
      error: 'bg-red-100 text-red-800',
      critical: 'bg-red-200 text-red-900 font-bold'
    };
    return colors[level as keyof typeof colors] || 'bg-gray-100 text-gray-800';
  };

  const StatCard: React.FC<{
    title: string;
    value: string | number;
    change?: number;
    icon: string;
    color: string;
  }> = ({ title, value, change, icon, color }) => (
    <div className="bg-white rounded-lg shadow-md p-6 border-l-4" style={{ borderLeftColor: color }}>
      <div className="flex items-center justify-between">
        <div>
          <p className="text-sm font-medium text-gray-600">{title}</p>
          <p className="text-2xl font-bold text-gray-900">{value}</p>
          {change !== undefined && (
            <div className="flex items-center mt-2">
              <span className={`text-sm font-medium ${change >= 0 ? 'text-green-600' : 'text-red-600'}`}>
                {change >= 0 ? '↗' : '↘'} {change >= 0 ? '+' : ''}{change}%
              </span>
            </div>
          )}
        </div>
        <div className="p-3 rounded-full text-2xl" style={{ backgroundColor: color + '20' }}>
          {icon}
        </div>
      </div>
    </div>
  );

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Süper Admin Paneli</h1>
          <p className="text-sm text-gray-500 mt-1">Sistem geneli yönetim ve izleme</p>
        </div>
        <div className="flex space-x-2">
          <select
            value={selectedTimeRange}
            onChange={(e) => setSelectedTimeRange(e.target.value as '24h' | '7d' | '30d')}
            className="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm"
          >
            <option value="24h">Son 24 Saat</option>
            <option value="7d">Son 7 Gün</option>
            <option value="30d">Son 30 Gün</option>
          </select>
          <button className="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
            <span>📊</span>
            <span>Rapor Oluştur</span>
          </button>
        </div>
      </div>

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard
          title="Toplam Kullanıcı"
          value={stats.totalUsers}
          change={12}
          icon="👥"
          color="#3B82F6"
        />
        <StatCard
          title="Aktif Pazaryeri"
          value={stats.activeMarketplaces}
          icon="🏪"
          color="#10B981"
        />
        <StatCard
          title="Günlük API Çağrısı"
          value={stats.totalApiCalls.toLocaleString()}
          change={8}
          icon="🔗"
          color="#8B5CF6"
        />
        <StatCard
          title="Sistem Sağlığı"
          value={`${stats.systemHealth}%`}
          change={2.1}
          icon="💚"
          color="#10B981"
        />
      </div>

      {/* Charts Row */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* API Usage Chart */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Pazaryeri API Kullanımı</h2>
          <ResponsiveContainer width="100%" height={300}>
            <BarChart data={apiUsageData}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="name" />
              <YAxis />
              <Tooltip />
              <Bar dataKey="calls" fill="#3B82F6" />
            </BarChart>
          </ResponsiveContainer>
        </div>

        {/* System Performance Chart */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <h2 className="text-lg font-semibold text-gray-900 mb-4">Sistem Performansı</h2>
          <ResponsiveContainer width="100%" height={300}>
            <LineChart data={systemPerformanceData}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="time" />
              <YAxis />
              <Tooltip />
              <Line type="monotone" dataKey="cpu" stroke="#EF4444" strokeWidth={2} />
              <Line type="monotone" dataKey="memory" stroke="#F59E0B" strokeWidth={2} />
              <Line type="monotone" dataKey="network" stroke="#10B981" strokeWidth={2} />
            </LineChart>
          </ResponsiveContainer>
        </div>
      </div>

      {/* User Management & System Logs */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* User Management */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex justify-between items-center mb-4">
            <h2 className="text-lg font-semibold text-gray-900">Kullanıcı Yönetimi</h2>
            <button className="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
              + Yeni Kullanıcı
            </button>
          </div>
          <div className="space-y-3">
            {users.map((user) => (
              <div key={user.id} className="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div className="flex-1">
                  <div className="flex items-center space-x-2">
                    <span className="font-medium text-gray-900">{user.name}</span>
                    <span className={`px-2 py-1 rounded-full text-xs ${getRoleColor(user.role)}`}>
                      {user.role}
                    </span>
                    <span className={`px-2 py-1 rounded-full text-xs ${getStatusColor(user.status)}`}>
                      {user.status}
                    </span>
                  </div>
                  <p className="text-sm text-gray-500">{user.email}</p>
                  <p className="text-xs text-gray-400">Son giriş: {user.lastLogin}</p>
                </div>
                <div className="flex space-x-1">
                  <button className="text-blue-600 hover:text-blue-800 text-sm">Düzenle</button>
                  <button className="text-red-600 hover:text-red-800 text-sm">Sil</button>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* System Logs */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <div className="flex justify-between items-center mb-4">
            <h2 className="text-lg font-semibold text-gray-900">Sistem Logları</h2>
            <button className="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm">
              Tümünü Gör
            </button>
          </div>
          <div className="space-y-2 max-h-80 overflow-y-auto">
            {systemLogs.map((log) => (
              <div key={log.id} className="p-3 bg-gray-50 rounded-lg">
                <div className="flex items-center justify-between mb-1">
                  <span className={`px-2 py-1 rounded-full text-xs ${getLogLevelColor(log.level)}`}>
                    {log.level.toUpperCase()}
                  </span>
                  <span className="text-xs text-gray-500">{log.timestamp}</span>
                </div>
                <p className="text-sm text-gray-800">{log.message}</p>
                <p className="text-xs text-gray-500">Kaynak: {log.source}</p>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* User Role Distribution */}
      <div className="bg-white rounded-lg shadow-md p-6">
        <h2 className="text-lg font-semibold text-gray-900 mb-4">Kullanıcı Rol Dağılımı</h2>
        <div className="flex items-center">
          <div className="w-1/2">
            <ResponsiveContainer width="100%" height={250}>
              <PieChart>
                <Pie
                  data={userRoleDistribution}
                  cx="50%"
                  cy="50%"
                  labelLine={false}
                  label={({ name, percent }) => `${name} ${(percent * 100).toFixed(0)}%`}
                  outerRadius={80}
                  fill="#8884d8"
                  dataKey="value"
                >
                  {userRoleDistribution.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={entry.color} />
                  ))}
                </Pie>
                <Tooltip />
              </PieChart>
            </ResponsiveContainer>
          </div>
          <div className="w-1/2 pl-6">
            <div className="space-y-3">
              {userRoleDistribution.map((role, index) => (
                <div key={index} className="flex items-center justify-between">
                  <div className="flex items-center space-x-2">
                    <div 
                      className="w-4 h-4 rounded-full" 
                      style={{ backgroundColor: role.color }}
                    ></div>
                    <span className="text-sm font-medium">{role.name}</span>
                  </div>
                  <span className="text-sm text-gray-600">{role.value} kullanıcı</span>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default AdminDashboard; 