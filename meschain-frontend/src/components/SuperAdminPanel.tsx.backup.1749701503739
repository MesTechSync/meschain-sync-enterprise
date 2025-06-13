import React, { useState, useEffect, useCallback } from 'react';
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
  Area,
  RadialBarChart,
  RadialBar,
  ComposedChart,
  LineChart,
  Line
} from 'recharts';
import {
  ChartBarIcon,
  UserGroupIcon,
  ServerIcon,
  CogIcon,
  BellIcon,
  ShieldCheckIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon,
  XMarkIcon,
  MagnifyingGlassIcon,
  PlusIcon,
  DocumentTextIcon,
  ArrowDownTrayIcon,
  ClockIcon,
  EyeIcon,
  TrashIcon,
  PencilIcon,
  CloudIcon,
  CpuChipIcon,
  StopIcon,
  CurrencyDollarIcon,
  GlobeAltIcon,
  CheckIcon,
  ArrowPathIcon,
  FunnelIcon,
  LockClosedIcon,
  KeyIcon,
  PauseIcon,
  PlayIcon
} from '@heroicons/react/24/outline';

// Enhanced interfaces
interface SuperAdminStats {
  totalUsers: number;
  activeMarketplaces: number;
  totalApiCalls: number;
  systemHealth: number;
  securityScore: number;
  dailyRevenue: number;
  monthlyGrowth: number;
  activeConnections: number;
  cpuUsage: number;
  memoryUsage: number;
  diskUsage: number;
  networkLatency: number;
  uptime: number;
  errorRate: number;
  throughput: number;
  cacheHitRatio: number;
}

interface EnhancedUserManagement {
  id: number;
  username: string;
  email: string;
  firstName: string;
  lastName: string;
  role: 'super_admin' | 'admin' | 'integrator' | 'tech_support' | 'dropshipper';
  status: 'active' | 'inactive' | 'suspended' | 'pending';
  lastLogin: string;
  dateAdded: string;
  marketplaceAccess: string[];
  permissions: string[];
  apiCallsToday: number;
  lastActivity: string;
  ipAddress: string;
  deviceInfo: string;
  loginCount: number;
  failedLoginAttempts: number;
  lastPasswordChange: string;
  twoFactorEnabled: boolean;
  sessionActive: boolean;
  dataUsage: number;
  avatar?: string;
}

interface EnhancedSystemLog {
  id: number;
  timestamp: string;
  level: 'info' | 'warning' | 'error' | 'critical' | 'debug';
  message: string;
  source: string;
  userId?: number;
  action?: string;
  ipAddress?: string;
  userAgent?: string;
  duration?: number;
  statusCode?: number;
  category: 'auth' | 'api' | 'system' | 'security' | 'marketplace' | 'user';
  metadata?: any;
}

interface AdvancedMarketplaceConfig {
  id: string;
  name: string;
  status: 'connected' | 'disconnected' | 'error' | 'testing' | 'maintenance';
  apiKey: string;
  secretKey?: string;
  lastSync: string;
  totalCalls: number;
  successRate: number;
  rateLimit: number;
  icon: string;
  color: string;
  region: string;
  environment: 'production' | 'staging' | 'development';
  version: string;
  healthCheck: 'healthy' | 'degraded' | 'unhealthy';
  responseTime: number;
  errorCount: number;
  retryCount: number;
  timeout: number;
  features: string[];
  webhookUrl?: string;
  syncFrequency: string;
  dataFormat: 'json' | 'xml' | 'csv';
}

interface CriticalSecurityAlert {
  id: number;
  type: 'login_attempt' | 'api_abuse' | 'permission_violation' | 'system_breach' | 'data_leak' | 'malware' | 'ddos';
  severity: 'low' | 'medium' | 'high' | 'critical';
  message: string;
  timestamp: string;
  source: string;
  resolved: boolean;
  assignedTo?: string;
  actionsTaken: string[];
  affectedUsers?: number[];
  riskScore: number;
  automated: boolean;
  falsePositive: boolean;
}

interface SystemMaintenance {
  id: string;
  type: 'backup' | 'update' | 'restart' | 'cleanup' | 'optimization';
  status: 'scheduled' | 'running' | 'completed' | 'failed' | 'cancelled';
  progress: number;
  scheduledFor: string;
  estimatedDuration: number;
  actualDuration?: number;
  description: string;
  affectedServices: string[];
  lastRun?: string;
  recurring: boolean;
  cronSchedule?: string;
}

interface RealTimeMetrics {
  timestamp: string;
  activeUsers: number;
  apiCalls: number;
  errorRate: number;
  responseTime: number;
  cpuUsage: number;
  memoryUsage: number;
  networkTraffic: number;
}

// Enhanced chart data
const enhancedSystemPerformanceData = [
  { time: '00:00', cpu: 65, memory: 78, network: 45, disk: 32, api_calls: 1200 },
  { time: '02:00', cpu: 68, memory: 76, network: 48, disk: 33, api_calls: 890 },
  { time: '04:00', cpu: 72, memory: 82, network: 52, disk: 35, api_calls: 650 },
  { time: '06:00', cpu: 75, memory: 85, network: 58, disk: 38, api_calls: 1100 },
  { time: '08:00', cpu: 88, memory: 91, network: 76, disk: 42, api_calls: 2400 },
  { time: '10:00', cpu: 92, memory: 94, network: 82, disk: 45, api_calls: 3200 },
  { time: '12:00', cpu: 94, memory: 89, network: 83, disk: 48, api_calls: 3800 },
  { time: '14:00', cpu: 89, memory: 87, network: 78, disk: 46, api_calls: 3500 },
  { time: '16:00', cpu: 91, memory: 85, network: 79, disk: 44, api_calls: 3100 },
  { time: '18:00', cpu: 85, memory: 80, network: 72, disk: 40, api_calls: 2800 },
  { time: '20:00', cpu: 79, memory: 76, network: 68, disk: 37, api_calls: 2200 },
  { time: '22:00', cpu: 74, memory: 73, network: 62, disk: 34, api_calls: 1800 }
];

const enhancedUserRoleDistribution = [
  { name: 'Super Admin', value: 5, color: '#DC2626', permissions: 100 },
  { name: 'Admin', value: 18, color: '#2563EB', permissions: 85 },
  { name: 'Marketplace Manager', value: 42, color: '#059669', permissions: 65 },
  { name: 'Technical Support', value: 28, color: '#D97706', permissions: 45 },
  { name: 'Integrator', value: 15, color: '#7C3AED', permissions: 70 },
  { name: 'Dropshipper', value: 1247, color: '#EC4899', permissions: 25 },
  { name: 'Viewer', value: 89, color: '#6B7280', permissions: 10 }
];

const securityMetricsData = [
  { name: 'Başarılı Giriş', value: 2847, color: '#059669' },
  { name: 'Başarısız Giriş', value: 156, color: '#DC2626' },
  { name: 'API Kötüye Kullanım', value: 23, color: '#D97706' },
  { name: 'Yetki İhlali', value: 8, color: '#7C3AED' }
];

// Security metrics data for pie chart
const securityMetricsDataPie = [
  { name: 'Güvenli Giriş', value: 85, color: '#10B981' },
  { name: 'Başarısız Giriş', value: 10, color: '#EF4444' },
  { name: 'Şüpheli Aktivite', value: 3, color: '#F59E0B' },
  { name: 'Engellenen IP', value: 2, color: '#6B7280' }
];

const SuperAdminPanel: React.FC = () => {
  const [stats, setStats] = useState<SuperAdminStats>({
    totalUsers: 0,
    activeMarketplaces: 0,
    totalApiCalls: 0,
    systemHealth: 0,
    securityScore: 0,
    dailyRevenue: 0,
    monthlyGrowth: 0,
    activeConnections: 0,
    cpuUsage: 0,
    memoryUsage: 0,
    diskUsage: 0,
    networkLatency: 0,
    uptime: 0,
    errorRate: 0,
    throughput: 0,
    cacheHitRatio: 0
  });

  const [users, setUsers] = useState<EnhancedUserManagement[]>([]);
  const [systemLogs, setSystemLogs] = useState<EnhancedSystemLog[]>([]);
  const [marketplaceConfigs, setMarketplaceConfigs] = useState<AdvancedMarketplaceConfig[]>([]);
  const [securityAlerts, setSecurityAlerts] = useState<CriticalSecurityAlert[]>([]);
  const [maintenanceTasks, setMaintenanceTasks] = useState<SystemMaintenance[]>([]);
  const [realTimeMetrics, setRealTimeMetrics] = useState<RealTimeMetrics[]>([]);
  
  const [selectedTab, setSelectedTab] = useState<string>('overview');
  const [selectedTimeRange, setSelectedTimeRange] = useState<'1h' | '6h' | '24h' | '7d' | '30d'>('24h');
  const [isLoading, setIsLoading] = useState<boolean>(true);
  const [apiStatus, setApiStatus] = useState<'online' | 'offline' | 'degraded'>('online');
  const [realTimeMode, setRealTimeMode] = useState<boolean>(false);
  const [lastUpdate, setLastUpdate] = useState<string>('');
  
  // Advanced filters and search
  const [userFilter, setUserFilter] = useState<string>('');
  const [logFilter, setLogFilter] = useState<'all' | 'error' | 'warning' | 'info' | 'critical'>('all');
  const [selectedUser, setSelectedUser] = useState<number | null>(null);
  const [showCreateUserModal, setShowCreateUserModal] = useState<boolean>(false);
  const [showMaintenanceModal, setShowMaintenanceModal] = useState<boolean>(false);

  // Enhanced demo data loading
  const loadEnhancedDemoData = useCallback(() => {
    // Enhanced system stats
    setStats({
      totalUsers: 1444,
      activeMarketplaces: 8,
      totalApiCalls: 2847650,
      systemHealth: 97.8,
      securityScore: 94.2,
      dailyRevenue: 1245000,
      monthlyGrowth: 23.5,
      activeConnections: 892,
      cpuUsage: 68.5,
      memoryUsage: 74.2,
      diskUsage: 45.8,
      networkLatency: 23.4,
      uptime: 99.98,
      errorRate: 0.02,
      throughput: 2847,
      cacheHitRatio: 94.6
    });

    // Enhanced users data
    setUsers([
      {
        id: 1,
        username: 'super_admin',
        email: 'superadmin@meschain.com',
        firstName: 'Super',
        lastName: 'Admin',
        role: 'super_admin',
        status: 'active',
        lastLogin: '2025-06-02 20:15:30',
        dateAdded: '2025-01-01 00:00:00',
        marketplaceAccess: ['all'],
        permissions: ['all'],
        apiCallsToday: 1247,
        lastActivity: '2025-06-02 20:14:45',
        ipAddress: '192.168.1.100',
        deviceInfo: 'Chrome 120.0, macOS',
        loginCount: 2847,
        failedLoginAttempts: 0,
        lastPasswordChange: '2025-05-15 14:30:00',
        twoFactorEnabled: true,
        sessionActive: true,
        dataUsage: 2.4
      },
      {
        id: 2,
        username: 'admin_user',
        email: 'admin@meschain.com',
        firstName: 'Admin',
        lastName: 'User',
        role: 'admin',
        status: 'active',
        lastLogin: '2025-06-02 19:45:12',
        dateAdded: '2025-01-15 10:30:00',
        marketplaceAccess: ['trendyol', 'amazon', 'n11'],
        permissions: ['marketplace_manage', 'product_manage', 'order_manage'],
        apiCallsToday: 567,
        lastActivity: '2025-06-02 19:58:23',
        ipAddress: '192.168.1.101',
        deviceInfo: 'Firefox 118.0, Windows 11',
        loginCount: 1456,
        failedLoginAttempts: 2,
        lastPasswordChange: '2025-05-01 09:15:00',
        twoFactorEnabled: true,
        sessionActive: true,
        dataUsage: 1.8
      },
      {
        id: 3,
        username: 'integrator_01',
        email: 'integrator@meschain.com',
        firstName: 'System',
        lastName: 'Integrator',
        role: 'integrator',
        status: 'active',
        lastLogin: '2025-06-02 18:20:45',
        dateAdded: '2025-02-01 14:20:00',
        marketplaceAccess: ['trendyol', 'hepsiburada'],
        permissions: ['integration_manage', 'api_access', 'system_monitor'],
        apiCallsToday: 2847,
        lastActivity: '2025-06-02 18:35:12',
        ipAddress: '192.168.1.102',
        deviceInfo: 'Chrome 120.0, Ubuntu 22.04',
        loginCount: 892,
        failedLoginAttempts: 0,
        lastPasswordChange: '2025-04-20 16:45:00',
        twoFactorEnabled: true,
        sessionActive: false,
        dataUsage: 5.2
      },
      {
        id: 4,
        username: 'tech_support',
        email: 'support@meschain.com',
        firstName: 'Tech',
        lastName: 'Support',
        role: 'tech_support',
        status: 'active',
        lastLogin: '2025-06-02 17:30:22',
        dateAdded: '2025-02-15 11:10:00',
        marketplaceAccess: ['n11', 'ciceksepeti'],
        permissions: ['support_manage', 'log_view', 'user_assist'],
        apiCallsToday: 234,
        lastActivity: '2025-06-02 17:45:33',
        ipAddress: '192.168.1.103',
        deviceInfo: 'Safari 17.0, macOS',
        loginCount: 445,
        failedLoginAttempts: 1,
        lastPasswordChange: '2025-04-01 13:20:00',
        twoFactorEnabled: false,
        sessionActive: false,
        dataUsage: 0.8
      },
      {
        id: 5,
        username: 'dropshipper_premium',
        email: 'premium@dropship.com',
        firstName: 'Premium',
        lastName: 'Dropshipper',
        role: 'dropshipper',
        status: 'active',
        lastLogin: '2025-06-02 16:45:18',
        dateAdded: '2025-03-01 09:30:00',
        marketplaceAccess: ['trendyol', 'amazon'],
        permissions: ['dropshipping_view', 'product_view', 'order_view', 'profit_view'],
        apiCallsToday: 1567,
        lastActivity: '2025-06-02 16:55:44',
        ipAddress: '85.124.67.89',
        deviceInfo: 'Chrome 119.0, Android 14',
        loginCount: 2156,
        failedLoginAttempts: 0,
        lastPasswordChange: '2025-03-15 10:45:00',
        twoFactorEnabled: true,
        sessionActive: true,
        dataUsage: 3.7
      }
    ]);

    // Enhanced marketplace configs
    setMarketplaceConfigs([
      {
        id: 'trendyol',
        name: 'Trendyol',
        status: 'connected',
        apiKey: 'TY_****_****_8945',
        secretKey: 'SH_****_****_2341',
        lastSync: '2025-06-02 20:15:30',
        totalCalls: 145230,
        successRate: 98.7,
        rateLimit: 1000,
        icon: '🛍️',
        color: '#F27A1A',
        region: 'TR',
        environment: 'production',
        version: 'v1.0',
        healthCheck: 'healthy',
        responseTime: 245,
        errorCount: 23,
        retryCount: 3,
        timeout: 30000,
        features: ['products', 'orders', 'inventory', 'shipping'],
        webhookUrl: 'https://api.meschain.com/webhooks/trendyol',
        syncFrequency: '15 minutes',
        dataFormat: 'json'
      },
      {
        id: 'amazon',
        name: 'Amazon SP-API',
        status: 'connected',
        apiKey: 'AMZ_****_****_7821',
        secretKey: 'AMZ_****_****_9456',
        lastSync: '2025-06-02 20:10:15',
        totalCalls: 89456,
        successRate: 97.2,
        rateLimit: 1200,
        icon: '📦',
        color: '#FF9900',
        region: 'EU',
        environment: 'production',
        version: 'SP-API v0.1',
        healthCheck: 'healthy',
        responseTime: 456,
        errorCount: 67,
        retryCount: 5,
        timeout: 25000,
        features: ['listings', 'orders', 'reports', 'notifications'],
        webhookUrl: 'https://api.meschain.com/webhooks/amazon',
        syncFrequency: '30 minutes',
        dataFormat: 'json'
      },
      {
        id: 'hepsiburada',
        name: 'Hepsiburada',
        status: 'connected',
        apiKey: 'HB_****_****_3421',
        lastSync: '2025-06-02 20:12:45',
        totalCalls: 67234,
        successRate: 96.8,
        rateLimit: 600,
        icon: '🏪',
        color: '#FF6000',
        region: 'TR',
        environment: 'production',
        version: 'v2.1',
        healthCheck: 'healthy',
        responseTime: 312,
        errorCount: 45,
        retryCount: 2,
        timeout: 25000,
        features: ['products', 'orders', 'inventory'],
        webhookUrl: 'https://api.meschain.com/webhooks/hepsiburada',
        syncFrequency: '15 minutes',
        dataFormat: 'json'
      },
      {
        id: 'n11',
        name: 'N11',
        status: 'error',
        apiKey: 'N11_****_****_9876',
        lastSync: '2025-06-02 19:45:20',
        totalCalls: 23456,
        successRate: 89.4,
        rateLimit: 400,
        icon: '🟣',
        color: '#7B2CBF',
        region: 'TR',
        environment: 'production',
        version: 'v1.5',
        healthCheck: 'unhealthy',
        responseTime: 1240,
        errorCount: 234,
        retryCount: 3,
        timeout: 35000,
        features: ['products', 'orders'],
        syncFrequency: 'Manual',
        dataFormat: 'xml'
      }
    ]);

    // Enhanced security alerts
    setSecurityAlerts([
      {
        id: 1,
        type: 'login_attempt',
        severity: 'medium',
        message: 'Birden fazla başarısız giriş denemesi tespit edildi',
        timestamp: '2025-06-02 20:10:15',
        source: 'Authentication Service',
        resolved: false,
        actionsTaken: ['IP monitoring activated'],
        riskScore: 65,
        automated: true,
        falsePositive: false
      },
      {
        id: 2,
        type: 'api_abuse',
        severity: 'high',
        message: 'Rate limit aşımı tespit edildi - N11 API',
        timestamp: '2025-06-02 19:45:33',
        source: 'API Gateway',
        resolved: true,
        assignedTo: 'tech_support',
        actionsTaken: ['Rate limiting enforced', 'User notified'],
        affectedUsers: [3],
        riskScore: 78,
        automated: true,
        falsePositive: false
      },
      {
        id: 3,
        type: 'system_breach',
        severity: 'critical',
        message: 'Yetkisiz admin panel erişim denemesi',
        timestamp: '2025-06-02 18:22:44',
        source: 'Security Monitor',
        resolved: true,
        assignedTo: 'super_admin',
        actionsTaken: ['IP blocked', 'Security team notified', 'Logs archived'],
        riskScore: 92,
        automated: false,
        falsePositive: false
      }
    ]);

    // Enhanced system logs
    setSystemLogs([
      {
        id: 1,
        timestamp: '2025-06-02 20:15:30',
        level: 'info',
        message: 'Trendyol API sync completed successfully',
        source: 'Trendyol Service',
        userId: 3,
        action: 'sync_inventory',
        ipAddress: '192.168.1.102',
        duration: 2340,
        statusCode: 200,
        category: 'marketplace',
        metadata: { records_processed: 1247, errors: 0 }
      },
      {
        id: 2,
        timestamp: '2025-06-02 20:12:15',
        level: 'warning',
        message: 'High memory usage detected on server-01',
        source: 'System Monitor',
        action: 'memory_check',
        category: 'system',
        metadata: { memory_usage: 89.5, threshold: 85 }
      },
      {
        id: 3,
        timestamp: '2025-06-02 20:08:44',
        level: 'error',
        message: 'N11 API connection failed',
        source: 'N11 Service',
        userId: 2,
        action: 'api_call',
        ipAddress: '192.168.1.101',
        duration: 30000,
        statusCode: 503,
        category: 'api',
        metadata: { error_code: 'CONNECTION_TIMEOUT', retry_count: 3 }
      },
      {
        id: 4,
        timestamp: '2025-06-02 19:55:12',
        level: 'info',
        message: 'User login successful',
        source: 'Auth Service',
        userId: 1,
        action: 'user_login',
        ipAddress: '192.168.1.100',
        duration: 145,
        statusCode: 200,
        category: 'auth',
        metadata: { two_factor: true, device: 'desktop' }
      },
      {
        id: 5,
        timestamp: '2025-06-02 19:45:28',
        level: 'critical',
        message: 'Database connection pool exhausted',
        source: 'Database Monitor',
        action: 'db_health_check',
        category: 'system',
        metadata: { active_connections: 100, max_connections: 100 }
      }
    ]);

    // Maintenance tasks
    setMaintenanceTasks([
      {
        id: 'backup_001',
        type: 'backup',
        status: 'completed',
        progress: 100,
        scheduledFor: '2025-06-02 02:00:00',
        estimatedDuration: 3600,
        actualDuration: 3245,
        description: 'Daily database backup',
        affectedServices: ['Database', 'File Storage'],
        lastRun: '2025-06-02 02:54:05',
        recurring: true,
        cronSchedule: '0 2 * * *'
      },
      {
        id: 'update_001',
        type: 'update',
        status: 'scheduled',
        progress: 0,
        scheduledFor: '2025-06-03 03:00:00',
        estimatedDuration: 1800,
        description: 'Security patch update',
        affectedServices: ['API Gateway', 'Auth Service'],
        recurring: false
      },
      {
        id: 'cleanup_001',
        type: 'cleanup',
        status: 'running',
        progress: 67,
        scheduledFor: '2025-06-02 20:00:00',
        estimatedDuration: 1200,
        description: 'Log files cleanup',
        affectedServices: ['Log System'],
        recurring: true,
        cronSchedule: '0 20 * * 0'
      }
    ]);

    // Real-time metrics (last 24 data points)
    const now = new Date();
    const metricsData = Array.from({ length: 24 }, (_, i) => {
      const timestamp = new Date(now.getTime() - (23 - i) * 60 * 60 * 1000);
      return {
        timestamp: timestamp.toISOString(),
        activeUsers: Math.floor(Math.random() * 200) + 800,
        apiCalls: Math.floor(Math.random() * 1000) + 2000,
        errorRate: Math.random() * 5,
        responseTime: Math.random() * 200 + 150,
        cpuUsage: Math.random() * 40 + 50,
        memoryUsage: Math.random() * 30 + 60,
        networkTraffic: Math.random() * 500 + 1000
      };
    });
    setRealTimeMetrics(metricsData);

  }, []);

  // Real-time data loading with enhanced features
  useEffect(() => {
    loadSuperAdminData();
    
    if (realTimeMode) {
      const interval = setInterval(() => {
        loadSuperAdminData();
        
        // Update real-time metrics
        setRealTimeMetrics(prev => {
          const newMetric: RealTimeMetrics = {
            timestamp: new Date().toISOString(),
            activeUsers: Math.floor(Math.random() * 200) + 800,
            apiCalls: Math.floor(Math.random() * 1000) + 2000,
            errorRate: Math.random() * 5,
            responseTime: Math.random() * 200 + 150,
            cpuUsage: Math.random() * 40 + 50,
            memoryUsage: Math.random() * 30 + 60,
            networkTraffic: Math.random() * 500 + 1000
          };
          return [...prev.slice(1), newMetric];
        });
        
        // Update system stats
        setStats(prev => ({
          ...prev,
          cpuUsage: Math.max(10, Math.min(95, prev.cpuUsage + (Math.random() - 0.5) * 10)),
          memoryUsage: Math.max(20, Math.min(90, prev.memoryUsage + (Math.random() - 0.5) * 5)),
          activeConnections: Math.max(100, Math.min(2000, prev.activeConnections + Math.floor((Math.random() - 0.5) * 100)))
        }));
      }, 30000);
      
      return () => clearInterval(interval);
    }
  }, [realTimeMode, selectedTimeRange]);

  const loadSuperAdminData = useCallback(async () => {
    setIsLoading(true);
    try {
      // Try to load real data from API
      await Promise.all([
        loadSystemStats(),
        loadUserManagement(),
        loadSystemLogs(),
        loadMarketplaceConfigs(),
        loadSecurityAlerts()
      ]);
      setApiStatus('online');
    } catch (error) {
      console.warn('API offline, using enhanced demo data:', error);
      loadEnhancedDemoData();
      setApiStatus('offline');
    } finally {
      setIsLoading(false);
      setLastUpdate(new Date().toLocaleString('tr-TR'));
    }
  }, [selectedTimeRange, loadEnhancedDemoData]);

  const loadSystemStats = async () => {
    try {
      // Load real Trendyol API data
      const [performanceResponse, connectionResponse] = await Promise.all([
        fetch('/test_api.php?action=detailed-performance'),
        fetch('/test_api.php?action=test-connection')
      ]);

      if (performanceResponse.ok && connectionResponse.ok) {
        const performanceData = await performanceResponse.json();
        const connectionData = await connectionResponse.json();
        
        if (performanceData.success && connectionData.success) {
          // Update stats with real API data
          setStats({
            totalUsers: 1444, // Keep user count as is (not from Trendyol)
            activeMarketplaces: connectionData.success ? 8 : 7, // Adjust based on connection
            totalApiCalls: performanceData.data.totalOrders * 100, // Estimated from order data
            systemHealth: connectionData.success ? 97.8 : 85.0,
            securityScore: 94.2, // Keep security score
            dailyRevenue: performanceData.data.todaySales || 0,
            monthlyGrowth: 23.5, // Calculate from data if available
            activeConnections: 892,
            cpuUsage: 68.5,
            memoryUsage: 74.2,
            diskUsage: 45.8,
            networkLatency: connectionData.data?.responseTime || 250,
            uptime: connectionData.success ? 99.98 : 95.0,
            errorRate: 0.02,
            throughput: performanceData.data.totalOrders || 2847,
            cacheHitRatio: 94.6
          });

          console.log('✅ Real Trendyol API data loaded successfully');
          return;
        }
      }
      
      throw new Error('API request failed');
    } catch (error) {
      console.warn('🚨 Trendyol API unavailable, using fallback data:', error);
      // Fallback to demo data
      setStats({
        totalUsers: 1444,
        activeMarketplaces: 8,
        totalApiCalls: 2847650,
        systemHealth: 97.8,
        securityScore: 94.2,
        dailyRevenue: 1245000,
        monthlyGrowth: 23.5,
        activeConnections: 892,
        cpuUsage: 68.5,
        memoryUsage: 74.2,
        diskUsage: 45.8,
        networkLatency: 23.4,
        uptime: 99.98,
        errorRate: 0.02,
        throughput: 2847,
        cacheHitRatio: 94.6
      });
      throw error;
    }
  };

  const loadUserManagement = async () => {
    try {
      const response = await fetch('/admin/extension/module/meschain/api/admin/users');
      if (response.ok) {
        const data = await response.json();
        setUsers(data.users);
      } else {
        throw new Error('API request failed');
      }
    } catch (error) {
      throw error;
    }
  };

  const loadSystemLogs = async () => {
    try {
      const response = await fetch(`/admin/extension/module/meschain/api/admin/logs?filter=${logFilter}`);
      if (response.ok) {
        const data = await response.json();
        setSystemLogs(data.logs);
      } else {
        throw new Error('API request failed');
      }
    } catch (error) {
      throw error;
    }
  };

  const loadMarketplaceConfigs = async () => {
    try {
      // Load real marketplace status data
      const [trendyolStatus, marketplaceStatus] = await Promise.all([
        fetch('/test_api.php?action=test-connection'),
        fetch('/test_api.php?action=marketplace-status')
      ]);

      if (trendyolStatus.ok && marketplaceStatus.ok) {
        const trendyolData = await trendyolStatus.json();
        const marketplaceData = await marketplaceStatus.json();
        
        if (trendyolData.success && marketplaceData.success) {
          // Real marketplace configurations with live data
          const realConfigs: AdvancedMarketplaceConfig[] = [
            {
              id: 'trendyol',
              name: 'Trendyol',
              status: trendyolData.success ? 'connected' : 'error',
              apiKey: 'TY_****_****_8945',
              secretKey: 'SH_****_****_2341',
              lastSync: new Date().toISOString(),
              totalCalls: 145230,
              successRate: trendyolData.success ? 98.7 : 0,
              rateLimit: 1000,
              icon: '🛍️',
              color: '#F27A1A',
              region: 'TR',
              environment: 'production',
              version: 'v1.0',
              healthCheck: trendyolData.success ? 'healthy' : 'unhealthy',
              responseTime: trendyolData.data?.responseTime || 999,
              errorCount: trendyolData.success ? 23 : 150,
              retryCount: 3,
              timeout: 30000,
              features: ['products', 'orders', 'inventory', 'shipping'],
              webhookUrl: 'https://api.meschain.com/webhooks/trendyol',
              syncFrequency: '15 minutes',
              dataFormat: 'json'
            },
            {
              id: 'amazon',
              name: 'Amazon SP-API',
              status: marketplaceData.data?.amazon?.status || 'not-configured',
              apiKey: 'AMZ_****_****_7821',
              secretKey: 'AMZ_****_****_9456',
              lastSync: marketplaceData.data?.amazon?.lastSync || 'Henüz bağlanmadı',
              totalCalls: 89456,
              successRate: 97.2,
              rateLimit: 1200,
              icon: '📦',
              color: '#FF9900',
              region: 'EU',
              environment: 'production',
              version: 'SP-API v0.1',
              healthCheck: 'degraded',
              responseTime: 456,
              errorCount: 67,
              retryCount: 5,
              timeout: 25000,
              features: ['listings', 'orders', 'reports', 'notifications'],
              webhookUrl: 'https://api.meschain.com/webhooks/amazon',
              syncFrequency: '30 minutes',
              dataFormat: 'json'
            },
            {
              id: 'n11',
              name: 'N11',
              status: marketplaceData.data?.n11?.status || 'not-configured',
              apiKey: 'N11_****_****_9876',
              lastSync: marketplaceData.data?.n11?.lastSync || 'Henüz bağlanmadı',
              totalCalls: 23456,
              successRate: 89.4,
              rateLimit: 400,
              icon: '🟣',
              color: '#7B2CBF',
              region: 'TR',
              environment: 'production',
              version: 'v1.5',
              healthCheck: 'unhealthy',
              responseTime: 1240,
              errorCount: 234,
              retryCount: 3,
              timeout: 35000,
              features: ['products', 'orders'],
              webhookUrl: 'https://api.meschain.com/webhooks/n11',
              syncFrequency: '20 minutes',
              dataFormat: 'json'
            },
            {
              id: 'hepsiburada',
              name: 'Hepsiburada',
              status: marketplaceData.data?.hepsiburada?.status || 'not-configured',
              apiKey: 'HB_****_****_3421',
              lastSync: marketplaceData.data?.hepsiburada?.lastSync || 'Henüz bağlanmadı',
              totalCalls: 67234,
              successRate: 96.8,
              rateLimit: 600,
              icon: '🏪',
              color: '#FF6000',
              region: 'TR',
              environment: 'production',
              version: 'v2.1',
              healthCheck: 'healthy',
              responseTime: 312,
              errorCount: 45,
              retryCount: 2,
              timeout: 25000,
              features: ['products', 'orders', 'inventory'],
              webhookUrl: 'https://api.meschain.com/webhooks/hepsiburada',
              syncFrequency: '15 minutes',
              dataFormat: 'json'
            },
            {
              id: 'ozon',
              name: 'Ozon',
              status: marketplaceData.data?.ozon?.status || 'not-configured',
              apiKey: 'OZ_****_****_5432',
              lastSync: marketplaceData.data?.ozon?.lastSync || 'Henüz bağlanmadı',
              totalCalls: 15678,
              successRate: 92.1,
              rateLimit: 500,
              icon: '🇷🇺',
              color: '#005BBB',
              region: 'RU',
              environment: 'production',
              version: 'v3.0',
              healthCheck: 'degraded',
              responseTime: 890,
              errorCount: 89,
              retryCount: 4,
              timeout: 40000,
              features: ['products', 'orders', 'logistics'],
              webhookUrl: 'https://api.meschain.com/webhooks/ozon',
              syncFrequency: '25 minutes',
              dataFormat: 'json'
            },
            {
              id: 'ebay',
              name: 'eBay',
              status: marketplaceData.data?.ebay?.status || 'not-configured',
              apiKey: 'EB_****_****_7890',
              lastSync: marketplaceData.data?.ebay?.lastSync || 'Henüz bağlanmadı',
              totalCalls: 0,
              successRate: 0,
              rateLimit: 800,
              icon: '💼',
              color: '#E53238',
              region: 'GLOBAL',
              environment: 'development',
              version: 'Trading API v1.0',
              healthCheck: 'unhealthy',
              responseTime: 0,
              errorCount: 0,
              retryCount: 0,
              timeout: 35000,
              features: [],
              syncFrequency: 'Henüz aktif değil',
              dataFormat: 'xml'
            }
          ];

          setMarketplaceConfigs(realConfigs);
          console.log('✅ Real marketplace configurations loaded successfully');
          return;
        }
      }
      
      throw new Error('API request failed');
    } catch (error) {
      console.warn('🚨 Marketplace API unavailable, using fallback data:', error);
      // Fallback to demo data
      setMarketplaceConfigs([
        {
          id: 'trendyol',
          name: 'Trendyol',
          status: 'connected',
          apiKey: 'TY_****_****_8945',
          secretKey: 'SH_****_****_2341',
          lastSync: '2025-06-02 20:15:30',
          totalCalls: 145230,
          successRate: 98.7,
          rateLimit: 1000,
          icon: '🛍️',
          color: '#F27A1A',
          region: 'TR',
          environment: 'production',
          version: 'v1.0',
          healthCheck: 'healthy',
          responseTime: 245,
          errorCount: 23,
          retryCount: 3,
          timeout: 30000,
          features: ['products', 'orders', 'inventory', 'shipping'],
          webhookUrl: 'https://api.meschain.com/webhooks/trendyol',
          syncFrequency: '15 minutes',
          dataFormat: 'json'
        }
      ]);
      throw error;
    }
  };

  const loadSecurityAlerts = async () => {
    try {
      const response = await fetch('/admin/extension/module/meschain/api/admin/security/alerts');
      if (response.ok) {
        const data = await response.json();
        setSecurityAlerts(data.alerts);
      } else {
        throw new Error('API request failed');
      }
    } catch (error) {
      throw error;
    }
  };

  // Utility functions
  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('tr-TR', {
      style: 'currency',
      currency: 'TRY',
      minimumFractionDigits: 0
    }).format(amount);
  };

  const formatBytes = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  };

  const formatDuration = (seconds: number) => {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const remainingSeconds = seconds % 60;
    return `${hours}:${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
  };

  const getRoleColor = (role: string) => {
    switch (role) {
      case 'super_admin': return 'bg-red-100 text-red-800';
      case 'admin': return 'bg-blue-100 text-blue-800';
      case 'integrator': return 'bg-purple-100 text-purple-800';
      case 'tech_support': return 'bg-orange-100 text-orange-800';
      case 'dropshipper': return 'bg-pink-100 text-pink-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'active':
      case 'connected':
      case 'healthy':
      case 'completed': return 'bg-green-100 text-green-800';
      case 'inactive':
      case 'disconnected':
      case 'pending': return 'bg-yellow-100 text-yellow-800';
      case 'suspended':
      case 'error':
      case 'unhealthy':
      case 'failed': return 'bg-red-100 text-red-800';
      case 'testing':
      case 'running':
      case 'degraded': return 'bg-blue-100 text-blue-800';
      case 'maintenance': return 'bg-purple-100 text-purple-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getLogLevelColor = (level: string) => {
    switch (level) {
      case 'info': return 'bg-blue-100 text-blue-800';
      case 'warning': return 'bg-yellow-100 text-yellow-800';
      case 'error': return 'bg-red-100 text-red-800';
      case 'critical': return 'bg-red-600 text-white';
      case 'debug': return 'bg-gray-100 text-gray-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getSeverityColor = (severity: string) => {
    switch (severity) {
      case 'low': return 'bg-green-100 text-green-800';
      case 'medium': return 'bg-yellow-100 text-yellow-800';
      case 'high': return 'bg-orange-100 text-orange-800';
      case 'critical': return 'bg-red-100 text-red-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  // Enhanced stat card component
  const EnhancedStatCard: React.FC<{
    title: string;
    value: string | number;
    change?: number;
    icon: React.ReactNode;
    color: string;
    description?: string;
    trend?: 'up' | 'down' | 'stable';
    onClick?: () => void;
  }> = ({ title, value, change, icon, color, description, trend, onClick }) => (
    <div 
      className={`bg-white rounded-lg shadow-sm border border-gray-200 p-6 transition-all duration-200 ${
        onClick ? 'cursor-pointer hover:shadow-md hover:border-gray-300' : ''
      }`}
      onClick={onClick}
    >
      <div className="flex items-center justify-between">
        <div className="flex-1">
          <div className="flex items-center">
            <div className={`p-2 rounded-lg ${color} bg-opacity-20`}>
              {icon}
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-600">{title}</p>
              <p className="text-2xl font-bold text-gray-900">{value}</p>
              {change !== undefined && (
                <div className="flex items-center mt-1">
                  {trend === 'up' && <span className="text-green-600 text-sm">↗ </span>}
                  {trend === 'down' && <span className="text-red-600 text-sm">↘ </span>}
                  {trend === 'stable' && <span className="text-gray-600 text-sm">→ </span>}
                  <span className={`text-sm font-medium ${
                    change > 0 ? 'text-green-600' : change < 0 ? 'text-red-600' : 'text-gray-600'
                  }`}>
                    {change > 0 ? '+' : ''}{change.toFixed(1)}%
                  </span>
                </div>
              )}
              {description && (
                <p className="text-xs text-gray-500 mt-1">{description}</p>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );

  // Enhanced loading state
  if (isLoading && !stats.totalUsers) {
    return (
      <div className="flex items-center justify-center min-h-screen bg-gray-50">
        <div className="text-center">
          <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-indigo-600 mx-auto"></div>
          <p className="mt-4 text-lg text-gray-600">Super Admin Panel Yükleniyor...</p>
          <p className="text-sm text-gray-500">Gelişmiş özellikler hazırlanıyor...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">👑 Super Admin Panel</h1>
          <p className="text-sm text-gray-500 mt-1">Sistem yönetimi ve kontrol merkezi</p>
        </div>
        <div className="flex items-center space-x-4">
          {/* API Status Indicator */}
          <div className={`flex items-center px-3 py-1 rounded-full text-sm font-medium ${
            apiStatus === 'online' ? 'bg-green-100 text-green-800' : apiStatus === 'degraded' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'
          }`}>
            <span className={`w-2 h-2 rounded-full mr-2 ${
              apiStatus === 'online' ? 'bg-green-500' : apiStatus === 'degraded' ? 'bg-yellow-500' : 'bg-red-500'
            }`}></span>
            API {apiStatus === 'online' ? 'Online' : apiStatus === 'degraded' ? 'Değişken' : 'Offline'}
          </div>
          
          {/* Time Range Selector */}
          <select
            value={selectedTimeRange}
            onChange={(e) => setSelectedTimeRange(e.target.value as any)}
            className="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm"
          >
            <option value="1h">Son 1 Saat</option>
            <option value="6h">Son 6 Saat</option>
            <option value="24h">Son 24 Saat</option>
            <option value="7d">Son 7 Gün</option>
            <option value="30d">Son 30 Gün</option>
          </select>
          
          <button
            onClick={loadSuperAdminData}
            className="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2"
          >
            <span>🔄</span>
            <span>Yenile</span>
          </button>
        </div>
      </div>

      {/* Tab Navigation */}
      <div className="border-b border-gray-200">
        <nav className="flex space-x-8" aria-label="Tabs">
          {[
            { key: 'overview', label: 'Genel Bakış', icon: '📊' },
            { key: 'analytics', label: 'Analizler', icon: '📈' },
            { key: 'users', label: 'Kullanıcılar', icon: '👥' },
            { key: 'security', label: 'Güvenlik', icon: '🔒' },
            { key: 'marketplaces', label: 'Pazaryerleri', icon: '🛒' },
            { key: 'logs', label: 'Loglar', icon: '📋' },
            { key: 'maintenance', label: 'Bakım', icon: '🔧' },
            { key: 'config', label: 'Ayarlar', icon: '⚙️' }
          ].map((tab) => (
            <button
              key={tab.key}
              onClick={() => setSelectedTab(tab.key)}
              className={`${
                selectedTab === tab.key
                  ? 'border-blue-500 text-blue-600 bg-blue-50'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              } whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm flex items-center space-x-2 transition-colors`}
            >
              <span>{tab.icon}</span>
              <span>{tab.label}</span>
            </button>
          ))}
        </nav>
      </div>

      <div className="p-6">
        {/* Tab Content */}
        {selectedTab === 'overview' && (
          <div className="space-y-6">
            {/* Key Metrics Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div className="flex items-center">
                  <UserGroupIcon className="h-8 w-8 text-blue-600" />
                  <div className="ml-3">
                    <p className="text-sm font-medium text-gray-600">Toplam Kullanıcı</p>
                    <p className="text-2xl font-bold text-gray-900">1,444</p>
                    <p className="text-xs text-green-600">↗ +12% bu ay</p>
                  </div>
                </div>
              </div>
              
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div className="flex items-center">
                  <ServerIcon className="h-8 w-8 text-green-600" />
                  <div className="ml-3">
                    <p className="text-sm font-medium text-gray-600">Aktif Pazaryeri</p>
                    <p className="text-2xl font-bold text-gray-900">8</p>
                    <p className="text-xs text-green-600">↗ Tümü bağlı</p>
                  </div>
                </div>
              </div>
              
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div className="flex items-center">
                  <ChartBarIcon className="h-8 w-8 text-purple-600" />
                  <div className="ml-3">
                    <p className="text-sm font-medium text-gray-600">Günlük API</p>
                    <p className="text-2xl font-bold text-gray-900">2.8M</p>
                    <p className="text-xs text-blue-600">↗ +5% dün</p>
                  </div>
                </div>
              </div>
              
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div className="flex items-center">
                  <ShieldCheckIcon className="h-8 w-8 text-orange-600" />
                  <div className="ml-3">
                    <p className="text-sm font-medium text-gray-600">Sistem Sağlığı</p>
                    <p className="text-2xl font-bold text-gray-900">97.8%</p>
                    <p className="text-xs text-green-600">↗ Mükemmel</p>
                  </div>
                </div>
              </div>
            </div>

            {/* Charts Section */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
              {/* System Performance Charts */}
              <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {/* System Performance Chart */}
                <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                  <h4 className="text-md font-semibold text-gray-900 mb-4">📊 Sistem Performansı</h4>
                  <ResponsiveContainer width="100%" height={250}>
                    <AreaChart data={enhancedSystemPerformanceData}>
                      <CartesianGrid strokeDasharray="3 3" />
                      <XAxis dataKey="time" />
                      <YAxis />
                      <Tooltip />
                      <Area type="monotone" dataKey="cpu" stackId="1" stroke="#8884d8" fill="#8884d8" />
                      <Area type="monotone" dataKey="memory" stackId="1" stroke="#82ca9d" fill="#82ca9d" />
                      <Area type="monotone" dataKey="network" stackId="1" stroke="#ffc658" fill="#ffc658" />
                    </AreaChart>
                  </ResponsiveContainer>
                </div>

                {/* User Role Distribution */}
                <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                  <h3 className="text-lg font-semibold text-gray-900 mb-4">👥 Kullanıcı Dağılımı</h3>
                  <ResponsiveContainer width="100%" height={250}>
                    <PieChart>
                      <Pie
                        data={enhancedUserRoleDistribution}
                        cx="50%"
                        cy="50%"
                        labelLine={false}
                        label={({ name, value }) => `${name}: ${value}`}
                        outerRadius={80}
                        fill="#8884d8"
                        dataKey="value"
                      >
                        {enhancedUserRoleDistribution.map((entry, index) => (
                          <Cell key={`cell-${index}`} fill={entry.color} />
                        ))}
                      </Pie>
                      <Tooltip />
                    </PieChart>
                  </ResponsiveContainer>
                </div>
              </div>

              {/* Quick Actions */}
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 className="text-lg font-semibold text-gray-900 mb-4">⚡ Hızlı İşlemler</h3>
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                  <button 
                    onClick={() => setShowCreateUserModal(true)}
                    className="p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"
                  >
                    <PlusIcon className="h-6 w-6 text-blue-600 mx-auto mb-2" />
                    <p className="text-sm font-medium text-blue-900">Yeni Kullanıcı</p>
                  </button>
                  
                  <button 
                    onClick={() => setShowMaintenanceModal(true)}
                    className="p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors"
                  >
                    <CogIcon className="h-6 w-6 text-green-600 mx-auto mb-2" />
                    <p className="text-sm font-medium text-green-900">Bakım Planla</p>
                  </button>
                  
                  <button 
                    onClick={() => {
                      setSelectedTab('logs');
                      console.log('Switching to logs tab');
                    }}
                    className="p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors"
                  >
                    <DocumentTextIcon className="h-6 w-6 text-purple-600 mx-auto mb-2" />
                    <p className="text-sm font-medium text-purple-900">Sistem Logları</p>
                  </button>
                  
                  <button 
                    onClick={() => {
                      setSelectedTab('security');
                      console.log('Switching to security tab');
                    }}
                    className="p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors"
                  >
                    <BellIcon className="h-6 w-6 text-orange-600 mx-auto mb-2" />
                    <p className="text-sm font-medium text-orange-900">Güvenlik Uyarıları</p>
                  </button>
                </div>
              </div>
            </div>
          </div>
        )}

        {/* Users Tab */}
        {selectedTab === 'users' && (
          <div className="space-y-6">
            {/* Users Management Header */}
            <div className="flex justify-between items-center">
              <h2 className="text-2xl font-bold text-gray-900">👥 Kullanıcı Yönetimi</h2>
              <button
                onClick={() => setShowCreateUserModal(true)}
                className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2"
              >
                <PlusIcon className="h-5 w-5" />
                <span>Yeni Kullanıcı</span>
              </button>
            </div>

            {/* User Filters */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
              <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Arama</label>
                  <div className="relative">
                    <MagnifyingGlassIcon className="h-5 w-5 text-gray-400 absolute left-3 top-2.5" />
                    <input
                      type="text"
                      value={userFilter}
                      onChange={(e) => setUserFilter(e.target.value)}
                      placeholder="Kullanıcı ara..."
                      className="pl-10 w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                  <select className="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Tüm Roller</option>
                    <option value="super_admin">Super Admin</option>
                    <option value="admin">Admin</option>
                    <option value="integrator">Integrator</option>
                    <option value="tech_support">Tech Support</option>
                    <option value="dropshipper">Dropshipper</option>
                  </select>
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Durum</label>
                  <select className="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Tüm Durumlar</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Pasif</option>
                    <option value="suspended">Askıya Alınmış</option>
                    <option value="pending">Beklemede</option>
                  </select>
                </div>
                <div className="flex items-end">
                  <button className="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md flex items-center justify-center space-x-2">
                    <FunnelIcon className="h-5 w-5" />
                    <span>Filtrele</span>
                  </button>
                </div>
              </div>
            </div>

            {/* Users Table */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
              <div className="px-6 py-4 border-b border-gray-200">
                <h3 className="text-lg font-medium text-gray-900">Kullanıcı Listesi ({users.length})</h3>
              </div>
              <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                  <thead className="bg-gray-50">
                    <tr>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kullanıcı</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Son Giriş</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">API Çağrı</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                    </tr>
                  </thead>
                  <tbody className="bg-white divide-y divide-gray-200">
                    {users.map((user) => (
                      <tr key={user.id} className="hover:bg-gray-50">
                        <td className="px-6 py-4 whitespace-nowrap">
                          <div className="flex items-center">
                            <div className="h-10 w-10 flex-shrink-0">
                              <div className="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <span className="text-sm font-medium text-blue-600">
                                  {user.firstName.charAt(0)}{user.lastName.charAt(0)}
                                </span>
                              </div>
                            </div>
                            <div className="ml-4">
                              <div className="text-sm font-medium text-gray-900">{user.firstName} {user.lastName}</div>
                              <div className="text-sm text-gray-500">{user.email}</div>
                            </div>
                          </div>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getRoleColor(user.role)}`}>
                            {user.role.replace('_', ' ').toUpperCase()}
                          </span>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getStatusColor(user.status)}`}>
                            {user.status.toUpperCase()}
                          </span>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                          {user.lastLogin}
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                          {user.apiCallsToday.toLocaleString()}
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                          <button className="text-blue-600 hover:text-blue-900">
                            <EyeIcon className="h-4 w-4" />
                          </button>
                          <button className="text-indigo-600 hover:text-indigo-900">
                            <PencilIcon className="h-4 w-4" />
                          </button>
                          <button className="text-red-600 hover:text-red-900">
                            <TrashIcon className="h-4 w-4" />
                          </button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        )}

        {/* Security Tab */}
        {selectedTab === 'security' && (
          <div className="space-y-6">
            {/* Security Overview */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div className="flex items-center">
                  <ShieldCheckIcon className="h-8 w-8 text-green-600" />
                  <div className="ml-3">
                    <p className="text-sm font-medium text-gray-600">Güvenlik Skoru</p>
                    <p className="text-2xl font-bold text-gray-900">{stats.securityScore.toFixed(1)}%</p>
                    <p className="text-xs text-green-600">↗ Mükemmel</p>
                  </div>
                </div>
              </div>
              
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div className="flex items-center">
                  <ExclamationTriangleIcon className="h-8 w-8 text-orange-600" />
                  <div className="ml-3">
                    <p className="text-sm font-medium text-gray-600">Aktif Uyarılar</p>
                    <p className="text-2xl font-bold text-gray-900">{securityAlerts.filter(a => !a.resolved).length}</p>
                    <p className="text-xs text-orange-600">İnceleme gerekli</p>
                  </div>
                </div>
              </div>
              
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div className="flex items-center">
                  <LockClosedIcon className="h-8 w-8 text-blue-600" />
                  <div className="ml-3">
                    <p className="text-sm font-medium text-gray-600">2FA Aktif</p>
                    <p className="text-2xl font-bold text-gray-900">{users.filter(u => u.twoFactorEnabled).length}</p>
                    <p className="text-xs text-blue-600">/ {users.length} kullanıcı</p>
                  </div>
                </div>
              </div>
            </div>

            {/* Security Metrics Chart */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 className="text-lg font-semibold text-gray-900 mb-4">🔒 Güvenlik Metrikleri</h3>
                <ResponsiveContainer width="100%" height={300}>
                  <PieChart>
                    <Pie
                      data={securityMetricsDataPie}
                      cx="50%"
                      cy="50%"
                      labelLine={false}
                      label={({ name, value }) => `${name}: ${value}%`}
                      outerRadius={80}
                      fill="#8884d8"
                      dataKey="value"
                    >
                      {securityMetricsDataPie.map((entry, index) => (
                        <Cell key={`cell-${index}`} fill={entry.color} />
                      ))}
                    </Pie>
                    <Tooltip />
                  </PieChart>
                </ResponsiveContainer>
              </div>

              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 className="text-lg font-semibold text-gray-900 mb-4">🚨 Son Güvenlik Uyarıları</h3>
                <div className="space-y-3">
                  {securityAlerts.slice(0, 5).map((alert) => (
                    <div key={alert.id} className="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                      <div className={`flex-shrink-0 w-2 h-2 rounded-full mt-2 ${
                        alert.severity === 'critical' ? 'bg-red-500' :
                        alert.severity === 'high' ? 'bg-orange-500' :
                        alert.severity === 'medium' ? 'bg-yellow-500' : 'bg-green-500'
                      }`}></div>
                      <div className="flex-1 min-w-0">
                        <p className="text-sm font-medium text-gray-900">{alert.message}</p>
                        <p className="text-xs text-gray-500">{alert.timestamp}</p>
                        <div className="flex items-center mt-1 space-x-2">
                          <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getSeverityColor(alert.severity)}`}>
                            {alert.severity.toUpperCase()}
                          </span>
                          {alert.resolved && (
                            <span className="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                              Çözüldü
                            </span>
                          )}
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>

            {/* Security Actions */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">🛡️ Güvenlik İşlemleri</h3>
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                <button className="p-4 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                  <ShieldCheckIcon className="h-6 w-6 text-red-600 mx-auto mb-2" />
                  <p className="text-sm font-medium text-red-900">Güvenlik Taraması</p>
                </button>
                
                <button className="p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
                  <LockClosedIcon className="h-6 w-6 text-orange-600 mx-auto mb-2" />
                  <p className="text-sm font-medium text-orange-900">IP Engelleme</p>
                </button>
                
                <button className="p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors">
                  <KeyIcon className="h-6 w-6 text-yellow-600 mx-auto mb-2" />
                  <p className="text-sm font-medium text-yellow-900">API Key Yenile</p>
                </button>
                
                <button className="p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                  <DocumentTextIcon className="h-6 w-6 text-green-600 mx-auto mb-2" />
                  <p className="text-sm font-medium text-green-900">Güvenlik Raporu</p>
                </button>
              </div>
            </div>
          </div>
        )}

        {/* Logs Tab */}
        {selectedTab === 'logs' && (
          <div className="space-y-6">
            {/* Logs Header */}
            <div className="flex justify-between items-center">
              <h2 className="text-2xl font-bold text-gray-900">📋 Sistem Logları</h2>
              <div className="flex items-center space-x-4">
                <select
                  value={logFilter}
                  onChange={(e) => setLogFilter(e.target.value as any)}
                  className="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm"
                >
                  <option value="all">Tüm Loglar</option>
                  <option value="error">Hatalar</option>
                  <option value="warning">Uyarılar</option>
                  <option value="info">Bilgiler</option>
                  <option value="critical">Kritik</option>
                </select>
                <button className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                  <ArrowDownTrayIcon className="h-5 w-5" />
                  <span>Export</span>
                </button>
              </div>
            </div>

            {/* Log Stats */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div className="flex items-center">
                  <div className="p-2 rounded-lg bg-blue-100">
                    <DocumentTextIcon className="h-6 w-6 text-blue-600" />
                  </div>
                  <div className="ml-3">
                    <p className="text-sm font-medium text-gray-600">Toplam Log</p>
                    <p className="text-xl font-bold text-gray-900">{systemLogs.length.toLocaleString()}</p>
                  </div>
                </div>
              </div>
              
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div className="flex items-center">
                  <div className="p-2 rounded-lg bg-red-100">
                    <ExclamationTriangleIcon className="h-6 w-6 text-red-600" />
                  </div>
                  <div className="ml-3">
                    <p className="text-sm font-medium text-gray-600">Hatalar</p>
                    <p className="text-xl font-bold text-gray-900">{systemLogs.filter(log => log.level === 'error').length}</p>
                  </div>
                </div>
              </div>
              
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div className="flex items-center">
                  <div className="p-2 rounded-lg bg-yellow-100">
                    <ClockIcon className="h-6 w-6 text-yellow-600" />
                  </div>
                  <div className="ml-3">
                    <p className="text-sm font-medium text-gray-600">Uyarılar</p>
                    <p className="text-xl font-bold text-gray-900">{systemLogs.filter(log => log.level === 'warning').length}</p>
                  </div>
                </div>
              </div>
              
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div className="flex items-center">
                  <div className="p-2 rounded-lg bg-green-100">
                    <CheckCircleIcon className="h-6 w-6 text-green-600" />
                  </div>
                  <div className="ml-3">
                    <p className="text-sm font-medium text-gray-600">Başarılı</p>
                    <p className="text-xl font-bold text-gray-900">{systemLogs.filter(log => log.level === 'info').length}</p>
                  </div>
                </div>
              </div>
            </div>

            {/* Logs Table */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
              <div className="px-6 py-4 border-b border-gray-200">
                <h3 className="text-lg font-medium text-gray-900">Son Sistem Logları</h3>
              </div>
              <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                  <thead className="bg-gray-50">
                    <tr>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Zaman</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seviye</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mesaj</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kaynak</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
                    </tr>
                  </thead>
                  <tbody className="bg-white divide-y divide-gray-200">
                    {systemLogs.slice(0, 20).map((log) => (
                      <tr key={log.id} className="hover:bg-gray-50">
                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                          {new Date(log.timestamp).toLocaleString('tr-TR')}
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getLogLevelColor(log.level)}`}>
                            {log.level.toUpperCase()}
                          </span>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                          <span className="capitalize">{log.category}</span>
                        </td>
                        <td className="px-6 py-4 text-sm text-gray-900 max-w-xs truncate">
                          {log.message}
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                          {log.source}
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                          <button className="text-blue-600 hover:text-blue-900">
                            <EyeIcon className="h-4 w-4" />
                          </button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>

            {/* Real-time Log Stream */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <div className="flex justify-between items-center mb-4">
                <h3 className="text-lg font-semibold text-gray-900">🔴 Canlı Log Akışı</h3>
                <button 
                  onClick={() => setRealTimeMode(!realTimeMode)}
                  className={`px-4 py-2 rounded-lg flex items-center space-x-2 ${
                    realTimeMode ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'
                  }`}
                >
                  {realTimeMode ? <PauseIcon className="h-4 w-4" /> : <PlayIcon className="h-4 w-4" />}
                  <span>{realTimeMode ? 'Durdur' : 'Başlat'}</span>
                </button>
              </div>
              <div className="bg-black text-green-400 font-mono text-sm p-4 rounded-lg h-64 overflow-y-auto">
                {realTimeMode ? (
                  <div className="space-y-1">
                    <div>[{new Date().toISOString()}] INFO: Real-time monitoring aktif</div>
                    <div>[{new Date().toISOString()}] INFO: API çağrıları izleniyor...</div>
                    <div>[{new Date().toISOString()}] DEBUG: Trendyol sync completed</div>
                    <div>[{new Date().toISOString()}] WARNING: High CPU usage detected</div>
                    <div className="animate-pulse">[{new Date().toISOString()}] INFO: System monitoring...</div>
                  </div>
                ) : (
                  <div className="text-gray-500">Real-time log izleme durdu. Başlatmak için butona tıklayın.</div>
                )}
              </div>
            </div>
          </div>
        )}

        {/* Maintenance Modal */}
        {showMaintenanceModal && (
          <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div className="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
              <div className="mt-3">
                <div className="flex items-center justify-between mb-4">
                  <h3 className="text-lg font-medium text-gray-900">🔧 Bakım Görevi Planla</h3>
                  <button
                    onClick={() => setShowMaintenanceModal(false)}
                    className="text-gray-400 hover:text-gray-600"
                  >
                    <XMarkIcon className="h-6 w-6" />
                  </button>
                </div>
                
                <form className="space-y-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700">Görev Türü</label>
                    <select className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                      <option value="backup">Yedekleme</option>
                      <option value="update">Güncelleme</option>
                      <option value="restart">Yeniden Başlatma</option>
                      <option value="cleanup">Temizlik</option>
                      <option value="optimization">Optimizasyon</option>
                    </select>
                  </div>
                  
                  <div>
                    <label className="block text-sm font-medium text-gray-700">Açıklama</label>
                    <textarea
                      rows={3}
                      className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Bakım görevi açıklaması..."
                    />
                  </div>
                  
                  <div>
                    <label className="block text-sm font-medium text-gray-700">Planlanan Tarih</label>
                    <input
                      type="datetime-local"
                      className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    />
                  </div>
                  
                  <div>
                    <label className="block text-sm font-medium text-gray-700">Tahmini Süre (dakika)</label>
                    <input
                      type="number"
                      className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                      placeholder="60"
                    />
                  </div>
                  
                  <div>
                    <label className="block text-sm font-medium text-gray-700">Etkilenen Servisler</label>
                    <div className="mt-2 space-y-2">
                      {['Database', 'API Gateway', 'Auth Service', 'File Storage', 'Cache', 'Log System'].map((service) => (
                        <label key={service} className="flex items-center">
                          <input
                            type="checkbox"
                            className="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                          />
                          <span className="ml-2 text-sm text-gray-700">{service}</span>
                        </label>
                      ))}
                    </div>
                  </div>
                  
                  <div className="flex items-center">
                    <input
                      type="checkbox"
                      className="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    />
                    <span className="ml-2 text-sm text-gray-700">Tekrarlayan Görev</span>
                  </div>
                  
                  <div className="flex justify-end space-x-3 pt-4">
                    <button
                      type="button"
                      onClick={() => setShowMaintenanceModal(false)}
                      className="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                      İptal
                    </button>
                    <button
                      type="submit"
                      className="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
                    >
                      Görevi Planla
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        )}

        {/* Create User Modal */}
        {showCreateUserModal && (
          <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div className="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
              <div className="mt-3">
                <div className="flex items-center justify-between mb-4">
                  <h3 className="text-lg font-medium text-gray-900">👤 Yeni Kullanıcı Oluştur</h3>
                  <button
                    onClick={() => setShowCreateUserModal(false)}
                    className="text-gray-400 hover:text-gray-600"
                  >
                    <XMarkIcon className="h-6 w-6" />
                  </button>
                </div>
                
                <form className="space-y-4">
                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <label className="block text-sm font-medium text-gray-700">Ad</label>
                      <input
                        type="text"
                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Ad"
                      />
                    </div>
                    <div>
                      <label className="block text-sm font-medium text-gray-700">Soyad</label>
                      <input
                        type="text"
                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Soyad"
                      />
                    </div>
                  </div>
                  
                  <div>
                    <label className="block text-sm font-medium text-gray-700">E-posta</label>
                    <input
                      type="email"
                      className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                      placeholder="user@example.com"
                    />
                  </div>
                  
                  <div>
                    <label className="block text-sm font-medium text-gray-700">Kullanıcı Adı</label>
                    <input
                      type="text"
                      className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                      placeholder="username"
                    />
                  </div>
                  
                  <div>
                    <label className="block text-sm font-medium text-gray-700">Rol</label>
                    <select className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                      <option value="dropshipper">Dropshipper</option>
                      <option value="integrator">Integrator</option>
                      <option value="tech_support">Tech Support</option>
                      <option value="admin">Admin</option>
                      <option value="super_admin">Super Admin</option>
                    </select>
                  </div>
                  
                  <div>
                    <label className="block text-sm font-medium text-gray-700">Pazaryeri Erişimi</label>
                    <div className="mt-2 space-y-2">
                      {['Trendyol', 'Amazon', 'N11', 'Hepsiburada', 'eBay', 'Ozon'].map((marketplace) => (
                        <label key={marketplace} className="flex items-center">
                          <input
                            type="checkbox"
                            className="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                          />
                          <span className="ml-2 text-sm text-gray-700">{marketplace}</span>
                        </label>
                      ))}
                    </div>
                  </div>
                  
                  <div className="flex items-center">
                    <input
                      type="checkbox"
                      className="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    />
                    <span className="ml-2 text-sm text-gray-700">2FA Zorunlu</span>
                  </div>
                  
                  <div className="flex justify-end space-x-3 pt-4">
                    <button 
                      type="button"
                      onClick={() => setShowCreateUserModal(false)}
                      className="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                      İptal
                    </button>
                    <button
                      type="submit"
                      className="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
                    >
                      Kullanıcı Oluştur
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        )}

        {/* Marketplaces Tab */}
        {selectedTab === 'marketplaces' && (
          <div className="space-y-6">
            {/* Marketplaces Header */}
            <div className="flex justify-between items-center">
              <h2 className="text-2xl font-bold text-gray-900">🛒 Pazaryeri Yönetimi</h2>
              <button className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                <PlusIcon className="h-5 w-5" />
                <span>Yeni Entegrasyon</span>
              </button>
            </div>

            {/* Marketplace Status Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {marketplaceConfigs.map((marketplace) => (
                <div key={marketplace.id} className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                  <div className="flex items-center justify-between mb-4">
                    <div className="flex items-center space-x-3">
                      <div className="text-2xl">{marketplace.icon}</div>
                      <div>
                        <h3 className="text-lg font-semibold text-gray-900">{marketplace.name}</h3>
                        <p className="text-sm text-gray-500">{marketplace.region} - {marketplace.environment}</p>
                      </div>
                    </div>
                    <div className={`px-2 py-1 rounded-full text-xs font-semibold ${
                      marketplace.status === 'connected' ? 'bg-green-100 text-green-800' :
                      marketplace.status === 'error' ? 'bg-red-100 text-red-800' :
                      marketplace.status === 'testing' ? 'bg-yellow-100 text-yellow-800' :
                      'bg-gray-100 text-gray-800'
                    }`}>
                      {marketplace.status.toUpperCase()}
                    </div>
                  </div>

                  <div className="space-y-3">
                    <div className="flex justify-between">
                      <span className="text-sm text-gray-600">Başarı Oranı</span>
                      <span className="text-sm font-medium">{marketplace.successRate}%</span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-2">
                      <div 
                        className={`h-2 rounded-full ${
                          marketplace.successRate >= 90 ? 'bg-green-500' :
                          marketplace.successRate >= 70 ? 'bg-yellow-500' : 'bg-red-500'
                        }`}
                        style={{ width: `${marketplace.successRate}%` }}
                      ></div>
                    </div>

                    <div className="flex justify-between">
                      <span className="text-sm text-gray-600">Toplam Çağrı</span>
                      <span className="text-sm font-medium">{marketplace.totalCalls.toLocaleString()}</span>
                    </div>

                    <div className="flex justify-between">
                      <span className="text-sm text-gray-600">Yanıt Süresi</span>
                      <span className="text-sm font-medium">{marketplace.responseTime}ms</span>
                    </div>

                    <div className="flex justify-between">
                      <span className="text-sm text-gray-600">Son Sync</span>
                      <span className="text-sm font-medium">{new Date(marketplace.lastSync).toLocaleString('tr-TR')}</span>
                    </div>

                    <div className="flex justify-between">
                      <span className="text-sm text-gray-600">Rate Limit</span>
                      <span className="text-sm font-medium">{marketplace.rateLimit}/saat</span>
                    </div>
                  </div>

                  <div className="mt-4 flex space-x-2">
                    <button className="flex-1 bg-blue-50 hover:bg-blue-100 text-blue-700 px-3 py-2 rounded-md text-sm font-medium">
                      Yapılandır
                    </button>
                    <button className="flex-1 bg-green-50 hover:bg-green-100 text-green-700 px-3 py-2 rounded-md text-sm font-medium">
                      Test Et
                    </button>
                    <button className="bg-gray-50 hover:bg-gray-100 text-gray-700 px-3 py-2 rounded-md text-sm font-medium">
                      <CogIcon className="h-4 w-4" />
                    </button>
                  </div>
                </div>
              ))}
            </div>

            {/* API Performance Chart */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">📊 API Performans Karşılaştırması</h3>
              <ResponsiveContainer width="100%" height={300}>
                <BarChart data={marketplaceConfigs}>
                  <CartesianGrid strokeDasharray="3 3" />
                  <XAxis dataKey="name" />
                  <YAxis />
                  <Tooltip />
                  <Bar dataKey="successRate" fill="#10B981" name="Başarı Oranı %" />
                  <Bar dataKey="responseTime" fill="#3B82F6" name="Yanıt Süresi (ms)" />
                </BarChart>
              </ResponsiveContainer>
            </div>

            {/* Marketplace Features Matrix */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">🔧 Özellik Matrisi</h3>
              <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                  <thead className="bg-gray-50">
                    <tr>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pazaryeri</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ürün Yönetimi</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sipariş Yönetimi</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Yönetimi</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kargo</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Webhook</th>
                    </tr>
                  </thead>
                  <tbody className="bg-white divide-y divide-gray-200">
                    {marketplaceConfigs.map((marketplace) => (
                      <tr key={marketplace.id}>
                        <td className="px-6 py-4 whitespace-nowrap">
                          <div className="flex items-center">
                            <span className="text-lg mr-2">{marketplace.icon}</span>
                            <span className="text-sm font-medium text-gray-900">{marketplace.name}</span>
                          </div>
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          {marketplace.features.includes('products') ? 
                            <CheckCircleIcon className="h-5 w-5 text-green-500" /> : 
                            <XMarkIcon className="h-5 w-5 text-red-500" />
                          }
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          {marketplace.features.includes('orders') ? 
                            <CheckCircleIcon className="h-5 w-5 text-green-500" /> : 
                            <XMarkIcon className="h-5 w-5 text-red-500" />
                          }
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          {marketplace.features.includes('inventory') ? 
                            <CheckCircleIcon className="h-5 w-5 text-green-500" /> : 
                            <XMarkIcon className="h-5 w-5 text-red-500" />
                          }
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          {marketplace.features.includes('shipping') ? 
                            <CheckCircleIcon className="h-5 w-5 text-green-500" /> : 
                            <XMarkIcon className="h-5 w-5 text-red-500" />
                          }
                        </td>
                        <td className="px-6 py-4 whitespace-nowrap">
                          {marketplace.webhookUrl ? 
                            <CheckCircleIcon className="h-5 w-5 text-green-500" /> : 
                            <XMarkIcon className="h-5 w-5 text-red-500" />
                          }
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
        {selectedTab === 'analytics' && (
          <div className="space-y-6">
            <div className="flex justify-between items-center">
              <h2 className="text-2xl font-bold text-gray-900">📈 Gelişmiş Analizler</h2>
              <button className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                <ArrowDownTrayIcon className="h-5 w-5" />
                <span>Rapor İndir</span>
              </button>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div className="flex items-center">
                  <CpuChipIcon className="h-8 w-8 text-blue-600" />
                  <div className="ml-3">
                    <p className="text-sm font-medium text-gray-600">CPU Kullanımı</p>
                    <p className="text-2xl font-bold text-gray-900">{stats.cpuUsage.toFixed(1)}%</p>
                  </div>
                </div>
              </div>
              
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div className="flex items-center">
                  <CloudIcon className="h-8 w-8 text-green-600" />
                  <div className="ml-3">
                    <p className="text-sm font-medium text-gray-600">Bellek</p>
                    <p className="text-2xl font-bold text-gray-900">{stats.memoryUsage.toFixed(1)}%</p>
                  </div>
                </div>
              </div>
              
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div className="flex items-center">
                  <GlobeAltIcon className="h-8 w-8 text-purple-600" />
                  <div className="ml-3">
                    <p className="text-sm font-medium text-gray-600">Ağ Gecikmesi</p>
                    <p className="text-2xl font-bold text-gray-900">{stats.networkLatency.toFixed(1)}ms</p>
                  </div>
                </div>
              </div>
              
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div className="flex items-center">
                  <CurrencyDollarIcon className="h-8 w-8 text-orange-600" />
                  <div className="ml-3">
                    <p className="text-sm font-medium text-gray-600">Günlük Gelir</p>
                    <p className="text-2xl font-bold text-gray-900">{formatCurrency(stats.dailyRevenue)}</p>
                  </div>
                </div>
              </div>
            </div>

            {/* Performance Trends */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">📊 Performans Trendleri</h3>
              <ResponsiveContainer width="100%" height={300}>
                <LineChart data={realTimeMetrics}>
                  <CartesianGrid strokeDasharray="3 3" />
                  <XAxis dataKey="timestamp" />
                  <YAxis />
                  <Tooltip />
                  <Line type="monotone" dataKey="cpuUsage" stroke="#3B82F6" name="CPU %" />
                  <Line type="monotone" dataKey="memoryUsage" stroke="#10B981" name="Bellek %" />
                  <Line type="monotone" dataKey="responseTime" stroke="#F59E0B" name="Yanıt Süresi" />
                </LineChart>
              </ResponsiveContainer>
            </div>

            {/* API Usage Analytics */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">🔗 API Kullanım Analizi</h3>
              <ResponsiveContainer width="100%" height={300}>
                <BarChart data={realTimeMetrics}>
                  <CartesianGrid strokeDasharray="3 3" />
                  <XAxis dataKey="timestamp" />
                  <YAxis />
                  <Tooltip />
                  <Bar dataKey="apiCalls" fill="#8B5CF6" name="API Çağrıları" />
                  <Bar dataKey="errorRate" fill="#EF4444" name="Hata Oranı %" />
                </BarChart>
              </ResponsiveContainer>
            </div>

            {/* Cache Performance */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">⚡ Cache Performansı</h3>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div className="text-center">
                  <div className="text-3xl font-bold text-green-600">{stats.cacheHitRatio.toFixed(1)}%</div>
                  <div className="text-sm text-gray-600">Cache Hit Oranı</div>
                </div>
                <div className="text-center">
                  <div className="text-3xl font-bold text-blue-600">{stats.throughput.toLocaleString()}</div>
                  <div className="text-sm text-gray-600">İstek/Saniye</div>
                </div>
                <div className="text-center">
                  <div className="text-3xl font-bold text-purple-600">{formatDuration(stats.uptime)}</div>
                  <div className="text-sm text-gray-600">Uptime</div>
                </div>
              </div>
            </div>
          </div>
        )}

        {/* Maintenance Tab */}
        {selectedTab === 'maintenance' && (
          <div className="space-y-6">
            <div className="flex justify-between items-center">
              <h2 className="text-2xl font-bold text-gray-900">🔧 Sistem Bakımı</h2>
              <button 
                onClick={() => setShowMaintenanceModal(true)}
                className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2"
              >
                <PlusIcon className="h-5 w-5" />
                <span>Bakım Planla</span>
              </button>
            </div>

            {/* Maintenance Statistics */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div className="flex items-center">
                  <CogIcon className="h-8 w-8 text-blue-600" />
                  <div className="ml-3">
                    <p className="text-sm font-medium text-gray-600">Toplam Görev</p>
                    <p className="text-2xl font-bold text-gray-900">12</p>
                    <p className="text-xs text-blue-600">8 aktif</p>
                  </div>
                </div>
              </div>
              
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div className="flex items-center">
                  <CheckCircleIcon className="h-8 w-8 text-green-600" />
                  <div className="ml-3">
                    <p className="text-sm font-medium text-gray-600">Tamamlanan</p>
                    <p className="text-2xl font-bold text-gray-900">28</p>
                    <p className="text-xs text-green-600">Bu ay</p>
                  </div>
                </div>
              </div>
              
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div className="flex items-center">
                  <ExclamationTriangleIcon className="h-8 w-8 text-orange-600" />
                  <div className="ml-3">
                    <p className="text-sm font-medium text-gray-600">Bekleyen</p>
                    <p className="text-2xl font-bold text-gray-900">3</p>
                    <p className="text-xs text-orange-600">Öncelikli</p>
                  </div>
                </div>
              </div>
              
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div className="flex items-center">
                  <ClockIcon className="h-8 w-8 text-purple-600" />
                  <div className="ml-3">
                    <p className="text-sm font-medium text-gray-600">Sonraki</p>
                    <p className="text-2xl font-bold text-gray-900">2s</p>
                    <p className="text-xs text-purple-600">Log temizlik</p>
                  </div>
                </div>
              </div>
            </div>

            {/* Active Maintenance Tasks */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">🔄 Aktif Bakım Görevleri</h3>
              <div className="space-y-4">
                <div className="border border-gray-200 rounded-lg p-4">
                  <div className="flex items-center justify-between mb-3">
                    <div className="flex items-center space-x-3">
                      <div className="w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>
                      <div>
                        <h4 className="font-medium text-gray-900">Log Dosyaları Temizliği</h4>
                        <p className="text-sm text-gray-600">Eski log dosyalarını arşivliyor...</p>
                      </div>
                    </div>
                    <span className="text-sm text-blue-600 font-medium">Çalışıyor</span>
                  </div>
                  <div className="w-full bg-gray-200 rounded-full h-2">
                    <div className="h-2 rounded-full bg-blue-500" style={{ width: '67%' }}></div>
                  </div>
                  <div className="flex justify-between text-xs text-gray-500 mt-1">
                    <span>67% tamamlandı</span>
                    <span>Tahmini 2 dakika kaldı</span>
                  </div>
                </div>

                <div className="border border-gray-200 rounded-lg p-4">
                  <div className="flex items-center justify-between mb-3">
                    <div className="flex items-center space-x-3">
                      <div className="w-3 h-3 bg-green-500 rounded-full"></div>
                      <div>
                        <h4 className="font-medium text-gray-900">Veritabanı Optimizasyonu</h4>
                        <p className="text-sm text-gray-600">Dizinler optimize ediliyor...</p>
                      </div>
                    </div>
                    <span className="text-sm text-green-600 font-medium">Planlandı</span>
                  </div>
                  <div className="text-sm text-gray-600">
                    <p>⏰ Başlangıç: Bugün 02:00</p>
                    <p>⏱️ Süre: ~45 dakika</p>
                  </div>
                </div>

                <div className="border border-gray-200 rounded-lg p-4">
                  <div className="flex items-center justify-between mb-3">
                    <div className="flex items-center space-x-3">
                      <div className="w-3 h-3 bg-orange-500 rounded-full"></div>
                      <div>
                        <h4 className="font-medium text-gray-900">Sistem Güncellemesi</h4>
                        <p className="text-sm text-gray-600">Güvenlik yamaları uygulanacak</p>
                      </div>
                    </div>
                    <span className="text-sm text-orange-600 font-medium">Bekliyor</span>
                  </div>
                  <div className="text-sm text-gray-600">
                    <p>⏰ Başlangıç: Yarın 03:00</p>
                    <p>⏱️ Süre: ~2 saat</p>
                    <p>⚠️ Kısa süreli kesinti olabilir</p>
                  </div>
                </div>
              </div>
            </div>

            {/* Maintenance History */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">📊 Bakım Geçmişi</h3>
              <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                  <thead className="bg-gray-50">
                    <tr>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Görev</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tür</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Başlangıç</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Süre</th>
                      <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sonuç</th>
                    </tr>
                  </thead>
                  <tbody className="bg-white divide-y divide-gray-200">
                    <tr>
                      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Haftalık Yedekleme</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">backup</td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span className="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                          Tamamlandı
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">28 Mayıs 03:00</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">1s 23dk</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-green-600">✅ Başarılı</td>
                    </tr>
                    <tr>
                      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Cache Temizliği</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">cleanup</td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span className="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                          Tamamlandı
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">27 Mayıs 12:00</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">5dk</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-green-600">✅ Başarılı</td>
                    </tr>
                    <tr>
                      <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Sistem Yeniden Başlatma</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">restart</td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <span className="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                          Başarısız
                        </span>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">26 Mayıs 04:00</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">-</td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-red-600">❌ Timeout</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            {/* Quick Maintenance Actions */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">⚡ Hızlı Bakım İşlemleri</h3>
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                <button className="p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors text-center">
                  <CloudIcon className="h-8 w-8 text-blue-600 mx-auto mb-2" />
                  <p className="text-sm font-medium text-blue-900">Cache Temizle</p>
                  <p className="text-xs text-blue-600">~2 dakika</p>
                </button>
                
                <button className="p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors text-center">
                  <ServerIcon className="h-8 w-8 text-green-600 mx-auto mb-2" />
                  <p className="text-sm font-medium text-green-900">DB Optimize</p>
                  <p className="text-xs text-green-600">~15 dakika</p>
                </button>
                
                <button className="p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors text-center">
                  <DocumentTextIcon className="h-8 w-8 text-purple-600 mx-auto mb-2" />
                  <p className="text-sm font-medium text-purple-900">Log Arşivle</p>
                  <p className="text-xs text-purple-600">~5 dakika</p>
                </button>
                
                <button className="p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors text-center">
                  <ArrowPathIcon className="h-8 w-8 text-orange-600 mx-auto mb-2" />
                  <p className="text-sm font-medium text-orange-900">Sistem Yenile</p>
                  <p className="text-xs text-orange-600">~30 saniye</p>
                </button>
              </div>
            </div>
          </div>
        )}

        {/* Config Tab */}
        {selectedTab === 'config' && (
          <div className="space-y-6">
            <div className="flex justify-between items-center">
              <h2 className="text-2xl font-bold text-gray-900">⚙️ Sistem Ayarları</h2>
              <div className="flex space-x-2">
                <button className="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                  <ArrowDownTrayIcon className="h-5 w-5" />
                  <span>Ayarları Dışa Aktar</span>
                </button>
                <button className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                  <CheckIcon className="h-5 w-5" />
                  <span>Kaydet</span>
                </button>
              </div>
            </div>

            {/* System Configuration */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
              {/* General Settings */}
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 className="text-lg font-semibold text-gray-900 mb-4">🔧 Genel Ayarlar</h3>
                <div className="space-y-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Sistem Adı</label>
                    <input 
                      type="text" 
                      defaultValue="MesChain-Sync Super Admin"
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Zaman Dilimi</label>
                    <select className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                      <option value="Europe/Istanbul">Türkiye (UTC+3)</option>
                      <option value="UTC">UTC</option>
                      <option value="Europe/London">Londra (UTC+0)</option>
                    </select>
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Varsayılan Dil</label>
                    <select className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                      <option value="tr-TR">Türkçe</option>
                      <option value="en-GB">English</option>
                    </select>
                  </div>

                  <div className="flex items-center">
                    <input 
                      type="checkbox" 
                      id="maintenance-mode"
                      defaultChecked={false}
                      className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <label htmlFor="maintenance-mode" className="ml-2 block text-sm text-gray-700">
                      Bakım Modu
                    </label>
                  </div>

                  <div className="flex items-center">
                    <input 
                      type="checkbox" 
                      id="debug-mode"
                      defaultChecked={false}
                      className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <label htmlFor="debug-mode" className="ml-2 block text-sm text-gray-700">
                      Debug Modu
                    </label>
                  </div>
                </div>
              </div>

              {/* Security Settings */}
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 className="text-lg font-semibold text-gray-900 mb-4">🔒 Güvenlik Ayarları</h3>
                <div className="space-y-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Session Timeout (dakika)</label>
                    <input 
                      type="number" 
                      defaultValue="30"
                      min="5"
                      max="1440"
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Maksimum Login Denemesi</label>
                    <input 
                      type="number" 
                      defaultValue="5"
                      min="3"
                      max="10"
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">IP Engelleme Süresi (saat)</label>
                    <input 
                      type="number" 
                      defaultValue="24"
                      min="1"
                      max="168"
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>

                  <div className="flex items-center">
                    <input 
                      type="checkbox" 
                      id="two-factor-required"
                      defaultChecked={true}
                      className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <label htmlFor="two-factor-required" className="ml-2 block text-sm text-gray-700">
                      2FA Zorunlu
                    </label>
                  </div>

                  <div className="flex items-center">
                    <input 
                      type="checkbox" 
                      id="ip-whitelist"
                      defaultChecked={false}
                      className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <label htmlFor="ip-whitelist" className="ml-2 block text-sm text-gray-700">
                      IP Whitelist Aktif
                    </label>
                  </div>
                </div>
              </div>

              {/* API Settings */}
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 className="text-lg font-semibold text-gray-900 mb-4">🔗 API Ayarları</h3>
                <div className="space-y-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">API Rate Limit (istek/dakika)</label>
                    <input 
                      type="number" 
                      defaultValue="1000"
                      min="100"
                      max="10000"
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">API Timeout (saniye)</label>
                    <input 
                      type="number" 
                      defaultValue="30"
                      min="5"
                      max="300"
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Retry Sayısı</label>
                    <input 
                      type="number" 
                      defaultValue="3"
                      min="1"
                      max="10"
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>

                  <div className="flex items-center">
                    <input 
                      type="checkbox" 
                      id="api-logging"
                      defaultChecked={true}
                      className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <label htmlFor="api-logging" className="ml-2 block text-sm text-gray-700">
                      API Loglama Aktif
                    </label>
                  </div>

                  <div className="flex items-center">
                    <input 
                      type="checkbox" 
                      id="api-cache"
                      defaultChecked={true}
                      className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <label htmlFor="api-cache" className="ml-2 block text-sm text-gray-700">
                      API Cache Aktif
                    </label>
                  </div>
                </div>
              </div>

              {/* Database Settings */}
              <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 className="text-lg font-semibold text-gray-900 mb-4">🗄️ Veritabanı Ayarları</h3>
                <div className="space-y-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Connection Pool Size</label>
                    <input 
                      type="number" 
                      defaultValue="20"
                      min="5"
                      max="100"
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Query Timeout (saniye)</label>
                    <input 
                      type="number" 
                      defaultValue="30"
                      min="5"
                      max="300"
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Backup Frequency</label>
                    <select className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                      <option value="daily">Günlük</option>
                      <option value="weekly">Haftalık</option>
                      <option value="monthly">Aylık</option>
                    </select>
                  </div>

                  <div className="flex items-center">
                    <input 
                      type="checkbox" 
                      id="auto-backup"
                      defaultChecked={true}
                      className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <label htmlFor="auto-backup" className="ml-2 block text-sm text-gray-700">
                      Otomatik Yedekleme
                    </label>
                  </div>

                  <div className="flex items-center">
                    <input 
                      type="checkbox" 
                      id="query-logging"
                      defaultChecked={false}
                      className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <label htmlFor="query-logging" className="ml-2 block text-sm text-gray-700">
                      Query Loglama
                    </label>
                  </div>
                </div>
              </div>
            </div>

            {/* Notification Settings */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">🔔 Bildirim Ayarları</h3>
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                  <h4 className="font-medium text-gray-900 mb-3">Email Bildirimleri</h4>
                  <div className="space-y-2">
                    <div className="flex items-center">
                      <input type="checkbox" id="email-system-errors" defaultChecked={true} className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                      <label htmlFor="email-system-errors" className="ml-2 text-sm text-gray-700">Sistem Hataları</label>
                    </div>
                    <div className="flex items-center">
                      <input type="checkbox" id="email-security-alerts" defaultChecked={true} className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                      <label htmlFor="email-security-alerts" className="ml-2 text-sm text-gray-700">Güvenlik Uyarıları</label>
                    </div>
                    <div className="flex items-center">
                      <input type="checkbox" id="email-maintenance" defaultChecked={false} className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                      <label htmlFor="email-maintenance" className="ml-2 text-sm text-gray-700">Bakım Bildirimleri</label>
                    </div>
                  </div>
                </div>

                <div>
                  <h4 className="font-medium text-gray-900 mb-3">SMS Bildirimleri</h4>
                  <div className="space-y-2">
                    <div className="flex items-center">
                      <input type="checkbox" id="sms-critical-errors" defaultChecked={true} className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                      <label htmlFor="sms-critical-errors" className="ml-2 text-sm text-gray-700">Kritik Hatalar</label>
                    </div>
                    <div className="flex items-center">
                      <input type="checkbox" id="sms-security-breach" defaultChecked={true} className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                      <label htmlFor="sms-security-breach" className="ml-2 text-sm text-gray-700">Güvenlik İhlalleri</label>
                    </div>
                    <div className="flex items-center">
                      <input type="checkbox" id="sms-system-down" defaultChecked={true} className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                      <label htmlFor="sms-system-down" className="ml-2 text-sm text-gray-700">Sistem Çökmesi</label>
                    </div>
                  </div>
                </div>

                <div>
                  <h4 className="font-medium text-gray-900 mb-3">Slack Bildirimleri</h4>
                  <div className="space-y-2">
                    <div className="flex items-center">
                      <input type="checkbox" id="slack-deployments" defaultChecked={false} className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                      <label htmlFor="slack-deployments" className="ml-2 text-sm text-gray-700">Deployments</label>
                    </div>
                    <div className="flex items-center">
                      <input type="checkbox" id="slack-api-limits" defaultChecked={true} className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                      <label htmlFor="slack-api-limits" className="ml-2 text-sm text-gray-700">API Limit Uyarıları</label>
                    </div>
                    <div className="flex items-center">
                      <input type="checkbox" id="slack-performance" defaultChecked={false} className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                      <label htmlFor="slack-performance" className="ml-2 text-sm text-gray-700">Performans Raporları</label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {/* Environment Variables */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">🌐 Ortam Değişkenleri</h3>
              <div className="space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">REDIS_URL</label>
                    <input 
                      type="text" 
                      defaultValue="redis://localhost:6379"
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">ELASTICSEARCH_URL</label>
                    <input 
                      type="text" 
                      defaultValue="http://localhost:9200"
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">WEBHOOK_SECRET</label>
                    <input 
                      type="password" 
                      defaultValue="************************"
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">JWT_SECRET</label>
                    <input 
                      type="password" 
                      defaultValue="************************"
                      className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                </div>
                
                <div className="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                  <div className="flex">
                    <ExclamationTriangleIcon className="h-5 w-5 text-yellow-400" />
                    <div className="ml-3">
                      <p className="text-sm text-yellow-800">
                        <strong>Uyarı:</strong> Ortam değişkenlerini değiştirdikten sonra sistemi yeniden başlatmanız gerekebilir.
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default SuperAdminPanel; 