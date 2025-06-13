import React, { useState, useEffect, useCallback } from 'react';
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
  Cell,
  Area,
  AreaChart
} from 'recharts';
import {
  UserGroupIcon,
  ServerIcon,
  AdjustmentsVerticalIcon,
  LockClosedIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon,
  PlusIcon,
  CheckCircleIcon,
  XCircleIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  Cog6ToothIcon,
  ShieldExclamationIcon,
  UsersIcon,
  ArrowPathIcon
} from '@heroicons/react/24/outline';

interface Role {
  id: string;
  name: string;
  displayName: string;
  description: string;
  permissions: string[];
  userCount: number;
  isSystem: boolean;
  createdAt: string;
  lastModified: string;
  color: string;
  priority: number;
}

interface Permission {
  id: string;
  name: string;
  category: string;
  description: string;
  isRequired: boolean;
  dependencies: string[];
  risk: 'low' | 'medium' | 'high' | 'critical';
}

interface APIConfiguration {
  id: string;
  name: string;
  endpoint: string;
  status: 'active' | 'inactive' | 'error' | 'maintenance';
  rateLimit: number;
  timeout: number;
  retryCount: number;
  lastCheck: string;
  responseTime: number;
  errorRate: number;
  region: string;
  environment: 'production' | 'staging' | 'development';
}

interface SystemPerformance {
  cpuUsage: number;
  memoryUsage: number;
  diskUsage: number;
  networkLatency: number;
  activeConnections: number;
  cacheHitRatio: number;
  databaseConnections: number;
  queueSize: number;
  uptime: number;
  errorRate: number;
  throughput: number;
  averageResponseTime: number;
}

interface AutomationRule {
  id: string;
  name: string;
  type: 'scheduled' | 'trigger' | 'condition' | 'webhook';
  status: 'active' | 'inactive' | 'error' | 'pending';
  schedule?: string;
  trigger?: string;
  action: string;
  lastRun?: string;
  nextRun?: string;
  successCount: number;
  errorCount: number;
  description: string;
  priority: 'low' | 'medium' | 'high' | 'critical';
}

interface PerformanceMetric {
  timestamp: string;
  cpu: number;
  memory: number;
  network: number;
  disk: number;
}

interface UserActivity {
  userId: string;
  username: string;
  action: string;
  timestamp: string;
  ip: string;
  success: boolean;
}

const AdvancedConfigurationInterface: React.FC = () => {
  const [activeTab, setActiveTab] = useState<'overview' | 'roles' | 'api' | 'performance' | 'automation' | 'security'>('overview');
  const [roles, setRoles] = useState<Role[]>([]);
  const [permissions, setPermissions] = useState<Permission[]>([]);
  const [apiConfigs, setApiConfigs] = useState<APIConfiguration[]>([]);
  const [systemPerformance, setSystemPerformance] = useState<SystemPerformance>({
    cpuUsage: 0,
    memoryUsage: 0,
    diskUsage: 0,
    networkLatency: 0,
    activeConnections: 0,
    cacheHitRatio: 0,
    databaseConnections: 0,
    queueSize: 0,
    uptime: 0,
    errorRate: 0,
    throughput: 0,
    averageResponseTime: 0
  });
  const [performanceHistory, setPerformanceHistory] = useState<PerformanceMetric[]>([]);
  const [automationRules, setAutomationRules] = useState<AutomationRule[]>([]);
  const [userActivity, setUserActivity] = useState<UserActivity[]>([]);
  const [selectedRole, setSelectedRole] = useState<string>('');
  const [isLoading, setIsLoading] = useState(false);
  const [apiStatus, setApiStatus] = useState<'online' | 'offline' | 'maintenance'>('online');
  const [lastUpdate, setLastUpdate] = useState<string>('');
  const [realTimeMode, setRealTimeMode] = useState(true);
  const [selectedMetric, setSelectedMetric] = useState<'cpu' | 'memory' | 'network' | 'disk'>('cpu');
  const [securityLevel, setSecurityLevel] = useState<'standard' | 'enhanced' | 'maximum'>('enhanced');

  const loadDemoData = useCallback(() => {
    console.log('🔧 Loading Advanced Configuration demo data...');
    
    // Enhanced roles with detailed permissions
    setRoles([
      {
        id: 'super_admin',
        name: 'super_admin',
        displayName: 'Super Admin',
        description: 'Tam sistem yöneticisi - tüm yetkilere sahip',
        permissions: ['all_permissions', 'system_admin', 'user_management', 'security_admin', 'api_admin'],
        userCount: 2,
        isSystem: true,
        createdAt: '2025-01-01 00:00:00',
        lastModified: '2025-06-02 15:30:00',
        color: '#DC2626',
        priority: 10
      },
      {
        id: 'admin',
        name: 'admin',
        displayName: 'Admin',
        description: 'Pazaryeri ve ürün yöneticisi',
        permissions: ['marketplace_manage', 'product_manage', 'order_manage', 'report_view', 'user_view'],
        userCount: 8,
        isSystem: true,
        createdAt: '2025-01-01 00:00:00',
        lastModified: '2025-06-02 14:15:00',
        color: '#2563EB',
        priority: 8
      },
      {
        id: 'integrator',
        name: 'integrator',
        displayName: 'Integrator',
        description: 'Sistem entegrasyon uzmanı',
        permissions: ['integration_manage', 'api_access', 'system_monitor', 'webhook_manage'],
        userCount: 5,
        isSystem: true,
        createdAt: '2025-01-01 00:00:00',
        lastModified: '2025-06-02 13:45:00',
        color: '#7C3AED',
        priority: 7
      },
      {
        id: 'tech_support',
        name: 'tech_support',
        displayName: 'Tech Support',
        description: 'Teknik destek uzmanı',
        permissions: ['support_access', 'log_view', 'system_monitor', 'user_support'],
        userCount: 12,
        isSystem: true,
        createdAt: '2025-01-01 00:00:00',
        lastModified: '2025-06-02 12:30:00',
        color: '#059669',
        priority: 6
      },
      {
        id: 'dropshipper_premium',
        name: 'dropshipper_premium',
        displayName: 'Premium Dropshipper',
        description: 'Premium dropshipping kullanıcısı',
        permissions: ['dropshipping_premium', 'advanced_analytics', 'bulk_operations', 'priority_support'],
        userCount: 156,
        isSystem: false,
        createdAt: '2025-01-15 00:00:00',
        lastModified: '2025-06-02 11:15:00',
        color: '#F59E0B',
        priority: 5
      },
      {
        id: 'dropshipper_standard',
        name: 'dropshipper_standard',
        displayName: 'Standard Dropshipper',
        description: 'Standart dropshipping kullanıcısı',
        permissions: ['dropshipping_basic', 'basic_analytics', 'standard_operations'],
        userCount: 1261,
        isSystem: false,
        createdAt: '2025-01-15 00:00:00',
        lastModified: '2025-06-02 10:45:00',
        color: '#6B7280',
        priority: 3
      }
    ]);

    // Comprehensive permissions system
    setPermissions([
      // System Administration
      { id: 'all_permissions', name: 'Tüm Yetkiler', category: 'System', description: 'Sistemdeki tüm yetkilere erişim', isRequired: false, dependencies: [], risk: 'critical' },
      { id: 'system_admin', name: 'Sistem Yönetimi', category: 'System', description: 'Sistem ayarları ve konfigürasyon', isRequired: false, dependencies: [], risk: 'critical' },
      { id: 'user_management', name: 'Kullanıcı Yönetimi', category: 'System', description: 'Kullanıcı oluşturma, düzenleme, silme', isRequired: false, dependencies: [], risk: 'high' },
      { id: 'security_admin', name: 'Güvenlik Yönetimi', category: 'System', description: 'Güvenlik ayarları ve izinleri', isRequired: false, dependencies: [], risk: 'critical' },
      
      // API Management
      { id: 'api_admin', name: 'API Yönetimi', category: 'API', description: 'API konfigürasyonu ve yönetimi', isRequired: false, dependencies: [], risk: 'high' },
      { id: 'api_access', name: 'API Erişimi', category: 'API', description: 'API endpoint\'lerine erişim', isRequired: false, dependencies: [], risk: 'medium' },
      { id: 'webhook_manage', name: 'Webhook Yönetimi', category: 'API', description: 'Webhook oluşturma ve yönetimi', isRequired: false, dependencies: ['api_access'], risk: 'medium' },
      
      // Marketplace Operations
      { id: 'marketplace_manage', name: 'Pazaryeri Yönetimi', category: 'Marketplace', description: 'Pazaryeri entegrasyonları', isRequired: false, dependencies: [], risk: 'medium' },
      { id: 'product_manage', name: 'Ürün Yönetimi', category: 'Marketplace', description: 'Ürün ekleme, düzenleme, silme', isRequired: false, dependencies: [], risk: 'low' },
      { id: 'order_manage', name: 'Sipariş Yönetimi', category: 'Marketplace', description: 'Sipariş işlemleri', isRequired: false, dependencies: [], risk: 'medium' },
      
      // Dropshipping
      { id: 'dropshipping_premium', name: 'Premium Dropshipping', category: 'Dropshipping', description: 'Premium dropshipping özellikleri', isRequired: false, dependencies: [], risk: 'low' },
      { id: 'dropshipping_basic', name: 'Temel Dropshipping', category: 'Dropshipping', description: 'Temel dropshipping özellikleri', isRequired: false, dependencies: [], risk: 'low' },
      { id: 'bulk_operations', name: 'Toplu İşlemler', category: 'Dropshipping', description: 'Toplu ürün ve sipariş işlemleri', isRequired: false, dependencies: [], risk: 'medium' },
      
      // Analytics & Reports
      { id: 'advanced_analytics', name: 'Gelişmiş Analitik', category: 'Analytics', description: 'Detaylı analitik raporları', isRequired: false, dependencies: [], risk: 'low' },
      { id: 'basic_analytics', name: 'Temel Analitik', category: 'Analytics', description: 'Temel analitik raporları', isRequired: false, dependencies: [], risk: 'low' },
      { id: 'report_view', name: 'Rapor Görüntüleme', category: 'Analytics', description: 'Raporları görüntüleme', isRequired: false, dependencies: [], risk: 'low' },
      
      // Support & Monitoring
      { id: 'support_access', name: 'Destek Erişimi', category: 'Support', description: 'Destek paneline erişim', isRequired: false, dependencies: [], risk: 'low' },
      { id: 'log_view', name: 'Log Görüntüleme', category: 'Support', description: 'Sistem loglarını görüntüleme', isRequired: false, dependencies: [], risk: 'medium' },
      { id: 'system_monitor', name: 'Sistem İzleme', category: 'Support', description: 'Sistem performansını izleme', isRequired: false, dependencies: [], risk: 'medium' },
      { id: 'user_support', name: 'Kullanıcı Desteği', category: 'Support', description: 'Kullanıcılara destek sağlama', isRequired: false, dependencies: [], risk: 'low' },
      
      // Operations
      { id: 'standard_operations', name: 'Standart İşlemler', category: 'Operations', description: 'Standart operasyonel işlemler', isRequired: false, dependencies: [], risk: 'low' },
      { id: 'priority_support', name: 'Öncelikli Destek', category: 'Operations', description: 'Öncelikli destek hizmeti', isRequired: false, dependencies: [], risk: 'low' },
      { id: 'user_view', name: 'Kullanıcı Görüntüleme', category: 'Operations', description: 'Kullanıcı bilgilerini görüntüleme', isRequired: false, dependencies: [], risk: 'low' }
    ]);

    // Enhanced API configurations
    setApiConfigs([
      {
        id: 'trendyol_api',
        name: 'Trendyol Merchant API',
        endpoint: 'https://api.trendyol.com/sapigw/suppliers',
        status: 'active',
        rateLimit: 1000,
        timeout: 30000,
        retryCount: 3,
        lastCheck: '2025-06-02 15:45:00',
        responseTime: 245,
        errorRate: 1.2,
        region: 'Turkey',
        environment: 'production'
      },
      {
        id: 'n11_api',
        name: 'N11 API',
        endpoint: 'https://api.n11.com/ws',
        status: 'error',
        rateLimit: 500,
        timeout: 25000,
        retryCount: 5,
        lastCheck: '2025-06-02 15:44:00',
        responseTime: 1240,
        errorRate: 12.5,
        region: 'Turkey',
        environment: 'production'
      },
      {
        id: 'amazon_sp_api',
        name: 'Amazon SP-API',
        endpoint: 'https://sellingpartnerapi-eu.amazon.com',
        status: 'active',
        rateLimit: 200,
        timeout: 45000,
        retryCount: 3,
        lastCheck: '2025-06-02 15:43:00',
        responseTime: 456,
        errorRate: 2.8,
        region: 'Europe',
        environment: 'production'
      },
      {
        id: 'hepsiburada_api',
        name: 'Hepsiburada API',
        endpoint: 'https://mpop.hepsiburada.com/api',
        status: 'active',
        rateLimit: 300,
        timeout: 20000,
        retryCount: 3,
        lastCheck: '2025-06-02 15:42:00',
        responseTime: 312,
        errorRate: 3.2,
        region: 'Turkey',
        environment: 'production'
      },
      {
        id: 'ozon_api',
        name: 'Ozon API',
        endpoint: 'https://api-seller.ozon.ru',
        status: 'maintenance',
        rateLimit: 150,
        timeout: 35000,
        retryCount: 4,
        lastCheck: '2025-06-02 15:41:00',
        responseTime: 890,
        errorRate: 5.1,
        region: 'Russia',
        environment: 'production'
      },
      {
        id: 'ebay_api',
        name: 'eBay Trading API',
        endpoint: 'https://api.ebay.com/ws/api.dll',
        status: 'inactive',
        rateLimit: 100,
        timeout: 40000,
        retryCount: 2,
        lastCheck: '2025-06-02 15:40:00',
        responseTime: 0,
        errorRate: 0,
        region: 'Global',
        environment: 'staging'
      }
    ]);

    // Enhanced system performance metrics
    setSystemPerformance({
      cpuUsage: 68.5,
      memoryUsage: 74.2,
      diskUsage: 45.8,
      networkLatency: 23.4,
      activeConnections: 1247,
      cacheHitRatio: 94.6,
      databaseConnections: 45,
      queueSize: 12,
      uptime: 2847600, // 33 days
      errorRate: 2.1,
      throughput: 1250,
      averageResponseTime: 347
    });

    // Performance history for charts
    const now = new Date();
    const history: PerformanceMetric[] = [];
    for (let i = 23; i >= 0; i--) {
      const timestamp = new Date(now.getTime() - i * 60 * 60 * 1000);
      history.push({
        timestamp: timestamp.toISOString(),
        cpu: Math.random() * 30 + 50,
        memory: Math.random() * 20 + 60,
        network: Math.random() * 50 + 10,
        disk: Math.random() * 20 + 30
      });
    }
    setPerformanceHistory(history);

    // Enhanced automation rules
    setAutomationRules([
      {
        id: 'auto_sync_trendyol',
        name: 'Trendyol Otomatik Senkronizasyon',
        type: 'scheduled',
        status: 'active',
        schedule: '*/30 * * * *', // Every 30 minutes
        action: 'sync_products_and_orders',
        lastRun: '2025-06-02 15:30:00',
        nextRun: '2025-06-02 16:00:00',
        successCount: 1247,
        errorCount: 23,
        description: 'Trendyol ürün ve sipariş verilerini otomatik senkronize eder',
        priority: 'high'
      },
      {
        id: 'stock_alert_system',
        name: 'Stok Uyarı Sistemi',
        type: 'trigger',
        status: 'active',
        trigger: 'stock_level_low',
        action: 'send_notification',
        lastRun: '2025-06-02 14:45:00',
        successCount: 89,
        errorCount: 2,
        description: 'Stok seviyesi düştüğünde otomatik uyarı gönderir',
        priority: 'medium'
      },
      {
        id: 'daily_backup',
        name: 'Günlük Yedekleme',
        type: 'scheduled',
        status: 'active',
        schedule: '0 2 * * *', // Daily at 2 AM
        action: 'create_backup',
        lastRun: '2025-06-02 02:00:00',
        nextRun: '2025-06-03 02:00:00',
        successCount: 62,
        errorCount: 1,
        description: 'Günlük sistem ve veri yedeklemesi',
        priority: 'critical'
      },
      {
        id: 'performance_monitor',
        name: 'Performans İzleme',
        type: 'condition',
        status: 'active',
        trigger: 'cpu_usage > 90',
        action: 'alert_admin',
        lastRun: '2025-06-01 18:30:00',
        successCount: 15,
        errorCount: 0,
        description: 'Sistem performansı kritik seviyeye ulaştığında uyarı',
        priority: 'high'
      },
      {
        id: 'api_health_check',
        name: 'API Sağlık Kontrolü',
        type: 'scheduled',
        status: 'active',
        schedule: '*/5 * * * *', // Every 5 minutes
        action: 'check_api_endpoints',
        lastRun: '2025-06-02 15:45:00',
        nextRun: '2025-06-02 15:50:00',
        successCount: 2847,
        errorCount: 45,
        description: 'Tüm API endpoint\'lerinin sağlık durumunu kontrol eder',
        priority: 'medium'
      },
      {
        id: 'order_processing',
        name: 'Sipariş İşleme Otomasyonu',
        type: 'webhook',
        status: 'active',
        trigger: 'new_order_received',
        action: 'process_order_workflow',
        lastRun: '2025-06-02 15:42:00',
        successCount: 456,
        errorCount: 12,
        description: 'Yeni siparişleri otomatik olarak işleme alır',
        priority: 'high'
      }
    ]);

    // Recent user activity
    setUserActivity([
      {
        userId: 'admin_001',
        username: 'admin_user',
        action: 'Updated API configuration for Trendyol',
        timestamp: '2025-06-02 15:45:00',
        ip: '192.168.1.100',
        success: true
      },
      {
        userId: 'tech_002',
        username: 'tech_support_01',
        action: 'Viewed system performance metrics',
        timestamp: '2025-06-02 15:42:00',
        ip: '192.168.1.105',
        success: true
      },
      {
        userId: 'integrator_001',
        username: 'integrator_01',
        action: 'Created new automation rule',
        timestamp: '2025-06-02 15:38:00',
        ip: '192.168.1.110',
        success: true
      },
      {
        userId: 'admin_001',
        username: 'admin_user',
        action: 'Failed to update user permissions',
        timestamp: '2025-06-02 15:35:00',
        ip: '192.168.1.100',
        success: false
      },
      {
        userId: 'super_admin',
        username: 'super_admin',
        action: 'Modified security settings',
        timestamp: '2025-06-02 15:30:00',
        ip: '192.168.1.50',
        success: true
      }
    ]);

    setLastUpdate(new Date().toLocaleString('tr-TR'));
    console.log('✅ Advanced Configuration demo data loaded');
  }, []);

  const loadConfigurationData = useCallback(async () => {
    setIsLoading(true);
    try {
      const response = await fetch('/admin/extension/module/meschain/api/configuration');
      if (response.ok) {
        const responseData = await response.json();
        // Process real API data here
        console.log('Configuration data loaded:', responseData);
      } else {
        throw new Error('Configuration API failed');
      }
    } catch (error) {
      console.warn('Configuration API offline, using demo data:', error);
      loadDemoData();
    } finally {
      setIsLoading(false);
      setLastUpdate(new Date().toLocaleString('tr-TR'));
    }
  }, [loadDemoData]);

  // Real-time updates
  useEffect(() => {
    loadConfigurationData();
    
    if (realTimeMode) {
      const interval = setInterval(() => {
        loadConfigurationData();
        
        // Update performance metrics
        setSystemPerformance(prev => ({
          ...prev,
          cpuUsage: Math.max(10, Math.min(95, prev.cpuUsage + (Math.random() - 0.5) * 10)),
          memoryUsage: Math.max(20, Math.min(90, prev.memoryUsage + (Math.random() - 0.5) * 5)),
          networkLatency: Math.max(5, Math.min(100, prev.networkLatency + (Math.random() - 0.5) * 15)),
          activeConnections: Math.max(100, Math.min(2000, prev.activeConnections + Math.floor((Math.random() - 0.5) * 100)))
        }));
      }, 30000);
      
      return () => clearInterval(interval);
    }
  }, [loadConfigurationData, realTimeMode]);

  // Update role permissions
  const updateRolePermissions = async (roleId: string, permissions: string[]) => {
    setIsLoading(true);
    try {
      const response = await fetch(`/admin/extension/module/meschain/api/admin/roles/${roleId}/permissions`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ permissions })
      });

      if (response.ok) {
        setRoles(prev => prev.map(role => 
          role.id === roleId ? { ...role, permissions } : role
        ));
        alert('Rol yetkileri başarıyla güncellendi!');
      } else {
        throw new Error('Role update failed');
      }
    } catch (error) {
      console.warn('Role update API offline, simulating success:', error);
      setRoles(prev => prev.map(role => 
        role.id === roleId ? { ...role, permissions } : role
      ));
      alert('Rol yetkileri güncellendi! (Demo mode)');
    } finally {
      setIsLoading(false);
    }
  };

  // Test API configuration
  const testAPIConfiguration = async (configId: string) => {
    setIsLoading(true);
    try {
      const response = await fetch(`/admin/extension/module/meschain/api/admin/api-configs/${configId}/test`, {
        method: 'POST'
      });

      if (response.ok) {
        const result = await response.json();
        alert(`API Test: ${result.success ? 'Başarılı' : 'Başarısız'}`);
      } else {
        throw new Error('API test failed');
      }
    } catch (error) {
      console.warn('API test offline, simulating test:', error);
      const isSuccess = Math.random() > 0.3;
      alert(`API Test: ${isSuccess ? 'Başarılı' : 'Başarısız'} (Demo mode)`);
      
      // Update the config status
      setApiConfigs(prev => prev.map(config => 
        config.id === configId ? { 
          ...config, 
          status: isSuccess ? 'active' : 'error',
          lastCheck: new Date().toLocaleString('tr-TR')
        } : config
      ));
    } finally {
      setIsLoading(false);
    }
  };

  // Toggle automation rule
  const toggleAutomationRule = async (ruleId: string, status: 'active' | 'inactive') => {
    setIsLoading(true);
    try {
      const response = await fetch(`/admin/extension/module/meschain/api/admin/automation/${ruleId}/status`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ status })
      });

      if (response.ok) {
        setAutomationRules(prev => prev.map(rule => 
          rule.id === ruleId ? { ...rule, status } : rule
        ));
      } else {
        throw new Error('Automation toggle failed');
      }
    } catch (error) {
      console.warn('Automation toggle API offline, simulating toggle:', error);
      setAutomationRules(prev => prev.map(rule => 
        rule.id === ruleId ? { ...rule, status } : rule
      ));
    } finally {
      setIsLoading(false);
    }
  };

  // Utility functions
  const getStatusColor = (status: string) => {
    switch (status) {
      case 'active': return 'bg-green-100 text-green-800';
      case 'inactive': return 'bg-gray-100 text-gray-800';
      case 'error': return 'bg-red-100 text-red-800';
      case 'maintenance': return 'bg-yellow-100 text-yellow-800';
      case 'pending': return 'bg-blue-100 text-blue-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getRiskColor = (risk: string) => {
    switch (risk) {
      case 'low': return 'bg-green-100 text-green-800';
      case 'medium': return 'bg-yellow-100 text-yellow-800';
      case 'high': return 'bg-orange-100 text-orange-800';
      case 'critical': return 'bg-red-100 text-red-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getPriorityColor = (priority: string) => {
    switch (priority) {
      case 'low': return 'bg-gray-100 text-gray-800';
      case 'medium': return 'bg-blue-100 text-blue-800';
      case 'high': return 'bg-orange-100 text-orange-800';
      case 'critical': return 'bg-red-100 text-red-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getPermissionsByCategory = () => {
    return permissions.reduce((acc, permission) => {
      if (!acc[permission.category]) {
        acc[permission.category] = [];
      }
      acc[permission.category].push(permission);
      return acc;
    }, {} as Record<string, Permission[]>);
  };

  // Update system performance (simulated)
  const updateSystemPerformance = useCallback(async () => {
    try {
      const response = await fetch('/admin/extension/module/meschain/api/system/performance');
      if (response.ok) {
        const newData = await response.json();
        setSystemPerformance(newData);
      } else {
        throw new Error('Performance API failed');
      }
    } catch (error) {
      // Use demo data when API fails
      setSystemPerformance({
        cpuUsage: Math.random() * 40 + 30,
        memoryUsage: Math.random() * 30 + 40,
        diskUsage: Math.random() * 20 + 25,
        networkLatency: Math.random() * 60 + 20,
        activeConnections: Math.floor(Math.random() * 500) + 1000,
        cacheHitRatio: Math.random() * 10 + 85,
        databaseConnections: Math.floor(Math.random() * 20) + 30,
        queueSize: Math.floor(Math.random() * 50) + 10,
        uptime: Math.random() * 2 + 98,
        errorRate: Math.random() * 2,
        throughput: Math.random() * 200 + 800,
        averageResponseTime: Math.random() * 100 + 150
      });
    } finally {
      setLastUpdate(new Date().toLocaleString('tr-TR'));
    }
  }, []);

  // Format bytes utility function
  const formatBytes = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  };

  // Format uptime utility function
  const formatUptime = (seconds: number) => {
    const days = Math.floor(seconds / 86400);
    const hours = Math.floor((seconds % 86400) / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    return `${days}g ${hours}s ${minutes}d`;
  };

  // Performance data for charts
  const performanceData = [
    { name: 'CPU', value: systemPerformance.cpuUsage },
    { name: 'Bellek', value: systemPerformance.memoryUsage },
    { name: 'Disk', value: systemPerformance.diskUsage },
    { name: 'Cache Hit', value: systemPerformance.cacheHitRatio }
  ];

  // Render Overview Tab
  const renderOverview = () => (
    <div className="space-y-6">
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {/* System Status Cards */}
        <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <div className="flex items-center">
            <div className="p-2 bg-green-100 rounded-lg">
              <ServerIcon className="h-6 w-6 text-green-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-600">Sistem Durumu</p>
              <p className="text-2xl font-bold text-green-600">Aktif</p>
            </div>
          </div>
          <div className="mt-4">
            <p className="text-xs text-gray-500">Uptime: {formatUptime(systemPerformance.uptime)}</p>
          </div>
        </div>

        <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <div className="flex items-center">
            <div className="p-2 bg-blue-100 rounded-lg">
              <UserGroupIcon className="h-6 w-6 text-blue-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-600">Toplam Kullanıcı</p>
              <p className="text-2xl font-bold text-gray-900">
                {roles.reduce((sum, role) => sum + role.userCount, 0)}
              </p>
            </div>
          </div>
          <div className="mt-4">
            <p className="text-xs text-gray-500">{roles.length} farklı rol</p>
          </div>
        </div>

        <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <div className="flex items-center">
            <div className="p-2 bg-purple-100 rounded-lg">
              <ServerIcon className="h-6 w-6 text-purple-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-600">API Durumu</p>
              <p className="text-2xl font-bold text-gray-900">
                {apiConfigs.filter(api => api.status === 'active').length}/{apiConfigs.length}
              </p>
            </div>
          </div>
          <div className="mt-4">
            <p className="text-xs text-gray-500">Aktif entegrasyon</p>
          </div>
        </div>

        <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <div className="flex items-center">
            <div className="p-2 bg-orange-100 rounded-lg">
              <Cog6ToothIcon className="h-6 w-6 text-orange-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-600">Otomasyon</p>
              <p className="text-2xl font-bold text-gray-900">
                {automationRules.filter(rule => rule.status === 'active').length}
              </p>
            </div>
          </div>
          <div className="mt-4">
            <p className="text-xs text-gray-500">Aktif kural</p>
          </div>
        </div>
      </div>

      {/* Performance Overview */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* System Performance Chart */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 className="text-lg font-medium text-gray-900 mb-4">Sistem Performansı</h3>
          <ResponsiveContainer width="100%" height={300}>
            <BarChart data={[
              { name: 'CPU', value: systemPerformance.cpuUsage },
              { name: 'Memory', value: systemPerformance.memoryUsage },
              { name: 'Disk', value: systemPerformance.diskUsage },
              { name: 'Cache Hit', value: systemPerformance.cacheHitRatio }
            ]}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="name" />
              <YAxis />
              <Tooltip formatter={(value) => [`${value}%`, 'Kullanım']} />
              <Bar dataKey="value" fill="#3B82F6" />
            </BarChart>
          </ResponsiveContainer>
        </div>

        {/* Recent Activity */}
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h3 className="text-lg font-medium text-gray-900 mb-4">Son Aktiviteler</h3>
          <div className="space-y-4">
            {userActivity.slice(0, 5).map((activity, index) => (
              <div key={index} className="flex items-start space-x-3">
                <div className={`flex-shrink-0 w-2 h-2 rounded-full mt-2 ${
                  activity.success ? 'bg-green-500' : 'bg-red-500'
                }`} />
                <div className="flex-1 min-w-0">
                  <p className="text-sm text-gray-900">{activity.action}</p>
                  <p className="text-xs text-gray-500">
                    {activity.username} • {activity.timestamp} • {activity.ip}
                  </p>
                </div>
                <div className={`flex-shrink-0 ${
                  activity.success ? 'text-green-600' : 'text-red-600'
                }`}>
                  {activity.success ? 
                    <CheckCircleIcon className="h-4 w-4" /> : 
                    <XCircleIcon className="h-4 w-4" />
                  }
                </div>
              </div>
            ))}
          </div>
          <div className="mt-4 pt-4 border-t border-gray-200">
            <button className="text-sm text-blue-600 hover:text-blue-800 font-medium">
              Tüm aktiviteleri görüntüle →
            </button>
          </div>
        </div>
      </div>

      {/* Critical Alerts */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 className="text-lg font-medium text-gray-900 mb-4">Kritik Uyarılar</h3>
        <div className="space-y-3">
          {/* High CPU Alert */}
          {systemPerformance.cpuUsage > 80 && (
            <div className="flex items-center p-3 bg-red-50 border border-red-200 rounded-lg">
              <ExclamationTriangleIcon className="h-5 w-5 text-red-600 mr-3" />
              <div className="flex-1">
                <p className="text-sm font-medium text-red-800">
                  Yüksek CPU Kullanımı
                </p>
                <p className="text-xs text-red-600">
                  CPU kullanımı %{systemPerformance.cpuUsage.toFixed(1)} seviyesinde
                </p>
              </div>
              <button className="text-red-600 hover:text-red-800">
                <XCircleIcon className="h-4 w-4" />
              </button>
            </div>
          )}

          {/* API Error Alert */}
          {apiConfigs.some(api => api.status === 'error') && (
            <div className="flex items-center p-3 bg-orange-50 border border-orange-200 rounded-lg">
              <ExclamationTriangleIcon className="h-5 w-5 text-orange-600 mr-3" />
              <div className="flex-1">
                <p className="text-sm font-medium text-orange-800">
                  API Bağlantı Sorunu
                </p>
                <p className="text-xs text-orange-600">
                  {apiConfigs.filter(api => api.status === 'error').length} API hata durumunda
                </p>
              </div>
              <button 
                onClick={() => setActiveTab('api')}
                className="text-orange-600 hover:text-orange-800 text-xs font-medium"
              >
                Düzelt
              </button>
            </div>
          )}

          {/* No Critical Alerts */}
          {systemPerformance.cpuUsage <= 80 && !apiConfigs.some(api => api.status === 'error') && (
            <div className="flex items-center p-3 bg-green-50 border border-green-200 rounded-lg">
              <CheckCircleIcon className="h-5 w-5 text-green-600 mr-3" />
              <div className="flex-1">
                <p className="text-sm font-medium text-green-800">
                  Tüm Sistemler Normal
                </p>
                <p className="text-xs text-green-600">
                  Kritik uyarı bulunmuyor
                </p>
              </div>
            </div>
          )}
        </div>
      </div>
    </div>
  );

  const renderRolesTab = () => (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h3 className="text-lg font-semibold text-gray-900">👥 Rol ve Yetki Yönetimi</h3>
        <div className="flex items-center space-x-4">
          <button className="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 text-sm">
            <PlusIcon className="h-4 w-4" />
            <span>Yeni Rol</span>
          </button>
          <button className="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 text-sm">
            <ArrowPathIcon className="h-4 w-4" />
            <span>Yenile</span>
          </button>
        </div>
      </div>

      {/* Role Statistics */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div className="bg-white p-6 rounded-lg shadow">
          <div className="flex items-center">
            <div className="p-2 bg-blue-100 rounded-lg">
              <UserGroupIcon className="h-6 w-6 text-blue-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-500">Toplam Rol</p>
              <p className="text-2xl font-bold text-gray-900">{roles.length}</p>
            </div>
          </div>
        </div>
        <div className="bg-white p-6 rounded-lg shadow">
          <div className="flex items-center">
            <div className="p-2 bg-green-100 rounded-lg">
              <UsersIcon className="h-6 w-6 text-green-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-500">Toplam Kullanıcı</p>
              <p className="text-2xl font-bold text-gray-900">{roles.reduce((sum, role) => sum + role.userCount, 0)}</p>
            </div>
          </div>
        </div>
        <div className="bg-white p-6 rounded-lg shadow">
          <div className="flex items-center">
            <div className="p-2 bg-purple-100 rounded-lg">
              <LockClosedIcon className="h-6 w-6 text-purple-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-500">Toplam Yetki</p>
              <p className="text-2xl font-bold text-gray-900">{permissions.length}</p>
            </div>
          </div>
        </div>
        <div className="bg-white p-6 rounded-lg shadow">
          <div className="flex items-center">
            <div className="p-2 bg-red-100 rounded-lg">
              <ShieldExclamationIcon className="h-6 w-6 text-red-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-500">Kritik Yetki</p>
              <p className="text-2xl font-bold text-gray-900">{permissions.filter(p => p.risk === 'critical').length}</p>
            </div>
          </div>
        </div>
      </div>

      {/* Roles Table */}
      <div className="bg-white shadow rounded-lg overflow-hidden">
        <div className="px-6 py-4 border-b border-gray-200">
          <h4 className="text-lg font-medium text-gray-900">Sistem Rolleri</h4>
        </div>
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kullanıcı Sayısı</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Yetki Sayısı</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Öncelik</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Son Güncelleme</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {roles.map((role) => (
                <tr key={role.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div className="flex items-center">
                      <div className="w-3 h-3 rounded-full mr-3" style={{ backgroundColor: role.color }}></div>
                      <div>
                        <div className="text-sm font-medium text-gray-900">{role.displayName}</div>
                        <div className="text-sm text-gray-500">{role.description}</div>
                      </div>
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      {role.userCount} kullanıcı
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {role.permissions.length} yetki
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getPriorityColor(role.priority.toString())}`}>
                      Öncelik {role.priority}
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {new Date(role.lastModified).toLocaleDateString('tr-TR')}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div className="flex items-center space-x-2">
                      <button className="text-blue-600 hover:text-blue-900">
                        <EyeIcon className="h-4 w-4" />
                      </button>
                      <button className="text-green-600 hover:text-green-900">
                        <PencilIcon className="h-4 w-4" />
                      </button>
                      {!role.isSystem && (
                        <button className="text-red-600 hover:text-red-900">
                          <TrashIcon className="h-4 w-4" />
                        </button>
                      )}
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Permissions by Category */}
      <div className="bg-white shadow rounded-lg">
        <div className="px-6 py-4 border-b border-gray-200">
          <h4 className="text-lg font-medium text-gray-900">Yetki Kategorileri</h4>
        </div>
        <div className="p-6">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {Object.entries(getPermissionsByCategory()).map(([category, perms]) => (
              <div key={category} className="border rounded-lg p-4">
                <h5 className="font-medium text-gray-900 mb-3">{category}</h5>
                <div className="space-y-2">
                  {perms.map((permission) => (
                    <div key={permission.id} className="flex items-center justify-between">
                      <span className="text-sm text-gray-600">{permission.name}</span>
                      <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${getRiskColor(permission.risk)}`}>
                        {permission.risk}
                      </span>
                    </div>
                  ))}
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );

  const renderAPITab = () => (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h3 className="text-lg font-semibold text-gray-900">🔗 API Konfigürasyonu</h3>
        <div className="flex items-center space-x-4">
          <button className="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 text-sm">
            <PlusIcon className="h-4 w-4" />
            <span>Yeni API</span>
          </button>
          <button className="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 text-sm">
            <ArrowPathIcon className="h-4 w-4" />
            <span>Tümünü Test Et</span>
          </button>
        </div>
      </div>

      {/* API Statistics */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div className="bg-white p-6 rounded-lg shadow">
          <div className="flex items-center">
            <div className="p-2 bg-green-100 rounded-lg">
              <CheckCircleIcon className="h-6 w-6 text-green-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-500">Aktif API</p>
              <p className="text-2xl font-bold text-gray-900">{apiConfigs.filter(api => api.status === 'active').length}</p>
            </div>
          </div>
        </div>
        <div className="bg-white p-6 rounded-lg shadow">
          <div className="flex items-center">
            <div className="p-2 bg-red-100 rounded-lg">
              <XCircleIcon className="h-6 w-6 text-red-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-500">Hatalı API</p>
              <p className="text-2xl font-bold text-gray-900">{apiConfigs.filter(api => api.status === 'error').length}</p>
            </div>
          </div>
        </div>
        <div className="bg-white p-6 rounded-lg shadow">
          <div className="flex items-center">
            <div className="p-2 bg-yellow-100 rounded-lg">
              <ExclamationTriangleIcon className="h-6 w-6 text-yellow-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-500">Bakımda</p>
              <p className="text-2xl font-bold text-gray-900">{apiConfigs.filter(api => api.status === 'maintenance').length}</p>
            </div>
          </div>
        </div>
        <div className="bg-white p-6 rounded-lg shadow">
          <div className="flex items-center">
            <div className="p-2 bg-blue-100 rounded-lg">
              <ServerIcon className="h-6 w-6 text-blue-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-500">Ortalama Yanıt</p>
              <p className="text-2xl font-bold text-gray-900">{Math.round(apiConfigs.reduce((sum, api) => sum + api.responseTime, 0) / apiConfigs.length)}ms</p>
            </div>
          </div>
        </div>
      </div>

      {/* API Configurations Table */}
      <div className="bg-white shadow rounded-lg overflow-hidden">
        <div className="px-6 py-4 border-b border-gray-200">
          <h4 className="text-lg font-medium text-gray-900">API Endpoint'leri</h4>
        </div>
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">API</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Yanıt Süresi</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hata Oranı</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate Limit</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Son Kontrol</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {apiConfigs.map((api) => (
                <tr key={api.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div>
                      <div className="text-sm font-medium text-gray-900">{api.name}</div>
                      <div className="text-sm text-gray-500">{api.region} • {api.environment}</div>
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(api.status)}`}>
                      {api.status === 'active' ? 'Aktif' : 
                       api.status === 'error' ? 'Hata' : 
                       api.status === 'maintenance' ? 'Bakım' : 'İnaktif'}
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {api.responseTime > 0 ? `${api.responseTime}ms` : '-'}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <span className={api.errorRate > 10 ? 'text-red-600' : api.errorRate > 5 ? 'text-yellow-600' : 'text-green-600'}>
                      {api.errorRate}%
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {api.rateLimit}/saat
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {new Date(api.lastCheck).toLocaleString('tr-TR')}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div className="flex items-center space-x-2">
                      <button 
                        onClick={() => testAPIConfiguration(api.id)}
                        className="text-blue-600 hover:text-blue-900"
                        title="Test Et"
                      >
                        <ArrowPathIcon className="h-4 w-4" />
                      </button>
                      <button className="text-green-600 hover:text-green-900" title="Düzenle">
                        <PencilIcon className="h-4 w-4" />
                      </button>
                      <button className="text-gray-600 hover:text-gray-900" title="Görüntüle">
                        <EyeIcon className="h-4 w-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );

  const renderPerformanceTab = () => (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h3 className="text-lg font-semibold text-gray-900">⚡ Performans Ayarları</h3>
        <div className="flex items-center space-x-4">
          <select 
            value={selectedMetric} 
            onChange={(e) => setSelectedMetric(e.target.value as any)}
            className="border border-gray-300 rounded-md px-3 py-2 text-sm"
          >
            <option value="cpu">CPU Kullanımı</option>
            <option value="memory">Bellek Kullanımı</option>
            <option value="network">Ağ Trafiği</option>
            <option value="disk">Disk Kullanımı</option>
          </select>
          <button className="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 text-sm">
            <ArrowPathIcon className="h-4 w-4" />
            <span>Yenile</span>
          </button>
        </div>
      </div>

      {/* Performance Metrics */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div className="bg-white p-6 rounded-lg shadow">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-500">CPU Kullanımı</p>
              <p className="text-2xl font-bold text-gray-900">{systemPerformance.cpuUsage.toFixed(1)}%</p>
            </div>
            <div className={`w-12 h-12 rounded-full flex items-center justify-center ${systemPerformance.cpuUsage > 80 ? 'bg-red-100' : systemPerformance.cpuUsage > 60 ? 'bg-yellow-100' : 'bg-green-100'}`}>
              <span className={`text-sm font-bold ${systemPerformance.cpuUsage > 80 ? 'text-red-600' : systemPerformance.cpuUsage > 60 ? 'text-yellow-600' : 'text-green-600'}`}>
                {Math.round(systemPerformance.cpuUsage)}%
              </span>
            </div>
          </div>
        </div>
        <div className="bg-white p-6 rounded-lg shadow">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-500">Bellek Kullanımı</p>
              <p className="text-2xl font-bold text-gray-900">{systemPerformance.memoryUsage.toFixed(1)}%</p>
            </div>
            <div className={`w-12 h-12 rounded-full flex items-center justify-center ${systemPerformance.memoryUsage > 80 ? 'bg-red-100' : systemPerformance.memoryUsage > 60 ? 'bg-yellow-100' : 'bg-green-100'}`}>
              <span className={`text-sm font-bold ${systemPerformance.memoryUsage > 80 ? 'text-red-600' : systemPerformance.memoryUsage > 60 ? 'text-yellow-600' : 'text-green-600'}`}>
                {Math.round(systemPerformance.memoryUsage)}%
              </span>
            </div>
          </div>
        </div>
        <div className="bg-white p-6 rounded-lg shadow">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-500">Ağ Gecikmesi</p>
              <p className="text-2xl font-bold text-gray-900">{systemPerformance.networkLatency.toFixed(1)}ms</p>
            </div>
            <div className={`w-12 h-12 rounded-full flex items-center justify-center ${systemPerformance.networkLatency > 100 ? 'bg-red-100' : systemPerformance.networkLatency > 50 ? 'bg-yellow-100' : 'bg-green-100'}`}>
              <span className={`text-sm font-bold ${systemPerformance.networkLatency > 100 ? 'text-red-600' : systemPerformance.networkLatency > 50 ? 'text-yellow-600' : 'text-green-600'}`}>
                {Math.round(systemPerformance.networkLatency)}
              </span>
            </div>
          </div>
        </div>
        <div className="bg-white p-6 rounded-lg shadow">
          <div className="flex items-center justify-between">
            <div>
              <p className="text-sm font-medium text-gray-500">Cache Hit Oranı</p>
              <p className="text-2xl font-bold text-gray-900">{systemPerformance.cacheHitRatio.toFixed(1)}%</p>
            </div>
            <div className={`w-12 h-12 rounded-full flex items-center justify-center ${systemPerformance.cacheHitRatio < 80 ? 'bg-red-100' : systemPerformance.cacheHitRatio < 90 ? 'bg-yellow-100' : 'bg-green-100'}`}>
              <span className={`text-sm font-bold ${systemPerformance.cacheHitRatio < 80 ? 'text-red-600' : systemPerformance.cacheHitRatio < 90 ? 'text-yellow-600' : 'text-green-600'}`}>
                {Math.round(systemPerformance.cacheHitRatio)}%
              </span>
            </div>
          </div>
        </div>
      </div>

      {/* Performance Chart */}
      <div className="bg-white p-6 rounded-lg shadow">
        <h4 className="text-lg font-medium text-gray-900 mb-4">24 Saatlik Performans Geçmişi</h4>
        <div className="h-80">
          <ResponsiveContainer width="100%" height="100%">
            <LineChart data={performanceHistory}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis 
                dataKey="timestamp" 
                tickFormatter={(value) => new Date(value).toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })}
              />
              <YAxis />
              <Tooltip 
                labelFormatter={(value) => new Date(value).toLocaleString('tr-TR')}
                formatter={(value: any, name: string) => [`${Math.round(value)}${name === 'network' ? 'ms' : '%'}`, name.toUpperCase()]}
              />
              <Line 
                type="monotone" 
                dataKey={selectedMetric} 
                stroke="#3B82F6" 
                strokeWidth={2}
                dot={false}
              />
            </LineChart>
          </ResponsiveContainer>
        </div>
      </div>

      {/* System Information */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div className="bg-white p-6 rounded-lg shadow">
          <h4 className="text-lg font-medium text-gray-900 mb-4">Sistem Bilgileri</h4>
          <div className="space-y-3">
            <div className="flex justify-between">
              <span className="text-sm text-gray-500">Aktif Bağlantı</span>
              <span className="text-sm font-medium text-gray-900">{systemPerformance.activeConnections}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-sm text-gray-500">Veritabanı Bağlantısı</span>
              <span className="text-sm font-medium text-gray-900">{systemPerformance.databaseConnections}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-sm text-gray-500">Kuyruk Boyutu</span>
              <span className="text-sm font-medium text-gray-900">{systemPerformance.queueSize}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-sm text-gray-500">Çalışma Süresi</span>
              <span className="text-sm font-medium text-gray-900">{formatUptime(systemPerformance.uptime)}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-sm text-gray-500">Hata Oranı</span>
              <span className="text-sm font-medium text-gray-900">{systemPerformance.errorRate.toFixed(1)}%</span>
            </div>
            <div className="flex justify-between">
              <span className="text-sm text-gray-500">Throughput</span>
              <span className="text-sm font-medium text-gray-900">{systemPerformance.throughput} req/min</span>
            </div>
          </div>
        </div>

        <div className="bg-white p-6 rounded-lg shadow">
          <h4 className="text-lg font-medium text-gray-900 mb-4">Performans Ayarları</h4>
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Cache Stratejisi</label>
              <select className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                <option>Redis + Memory Cache</option>
                <option>Memory Cache Only</option>
                <option>Database Cache</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">API Timeout (ms)</label>
              <input type="number" defaultValue="30000" className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Max Concurrent Requests</label>
              <input type="number" defaultValue="1000" className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Database Pool Size</label>
              <input type="number" defaultValue="50" className="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" />
            </div>
            <button className="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md text-sm">
              Ayarları Kaydet
            </button>
          </div>
        </div>
      </div>
    </div>
  );

  const renderAutomationTab = () => (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h3 className="text-lg font-semibold text-gray-900">🤖 Otomasyon Kuralları</h3>
        <div className="flex items-center space-x-4">
          <button className="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 text-sm">
            <PlusIcon className="h-4 w-4" />
            <span>Yeni Kural</span>
          </button>
          <button className="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 text-sm">
            <ArrowPathIcon className="h-4 w-4" />
            <span>Tümünü Yenile</span>
          </button>
        </div>
      </div>

      {/* Automation Statistics */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div className="bg-white p-6 rounded-lg shadow">
          <div className="flex items-center">
            <div className="p-2 bg-green-100 rounded-lg">
              <CheckCircleIcon className="h-6 w-6 text-green-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-500">Aktif Kurallar</p>
              <p className="text-2xl font-bold text-gray-900">{automationRules.filter(rule => rule.status === 'active').length}</p>
            </div>
          </div>
        </div>
        <div className="bg-white p-6 rounded-lg shadow">
          <div className="flex items-center">
            <div className="p-2 bg-blue-100 rounded-lg">
              <Cog6ToothIcon className="h-6 w-6 text-blue-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-500">Toplam Çalıştırma</p>
              <p className="text-2xl font-bold text-gray-900">{automationRules.reduce((sum, rule) => sum + rule.successCount, 0)}</p>
            </div>
          </div>
        </div>
        <div className="bg-white p-6 rounded-lg shadow">
          <div className="flex items-center">
            <div className="p-2 bg-red-100 rounded-lg">
              <XCircleIcon className="h-6 w-6 text-red-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-500">Toplam Hata</p>
              <p className="text-2xl font-bold text-gray-900">{automationRules.reduce((sum, rule) => sum + rule.errorCount, 0)}</p>
            </div>
          </div>
        </div>
        <div className="bg-white p-6 rounded-lg shadow">
          <div className="flex items-center">
            <div className="p-2 bg-yellow-100 rounded-lg">
              <ExclamationTriangleIcon className="h-6 w-6 text-yellow-600" />
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-500">Başarı Oranı</p>
              <p className="text-2xl font-bold text-gray-900">
                {Math.round((automationRules.reduce((sum, rule) => sum + rule.successCount, 0) / 
                (automationRules.reduce((sum, rule) => sum + rule.successCount + rule.errorCount, 0))) * 100)}%
              </p>
            </div>
          </div>
        </div>
      </div>

      {/* Automation Rules Table */}
      <div className="bg-white shadow rounded-lg overflow-hidden">
        <div className="px-6 py-4 border-b border-gray-200">
          <h4 className="text-lg font-medium text-gray-900">Otomasyon Kuralları</h4>
        </div>
        <div className="overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kural</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tip</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durum</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Başarı/Hata</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Son Çalıştırma</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sonraki</th>
                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {automationRules.map((rule) => (
                <tr key={rule.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap">
                    <div>
                      <div className="text-sm font-medium text-gray-900">{rule.name}</div>
                      <div className="text-sm text-gray-500">{rule.description}</div>
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                      rule.type === 'scheduled' ? 'bg-blue-100 text-blue-800' :
                      rule.type === 'trigger' ? 'bg-green-100 text-green-800' :
                      rule.type === 'condition' ? 'bg-purple-100 text-purple-800' :
                      'bg-orange-100 text-orange-800'
                    }`}>
                      {rule.type === 'scheduled' ? 'Zamanlanmış' :
                       rule.type === 'trigger' ? 'Tetikleyici' :
                       rule.type === 'condition' ? 'Koşullu' : 'Webhook'}
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap">
                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getStatusColor(rule.status)}`}>
                      {rule.status === 'active' ? 'Aktif' : 
                       rule.status === 'inactive' ? 'İnaktif' : 
                       rule.status === 'error' ? 'Hata' : 'Beklemede'}
                    </span>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <div className="flex items-center space-x-2">
                      <span className="text-green-600">{rule.successCount}</span>
                      <span className="text-gray-400">/</span>
                      <span className="text-red-600">{rule.errorCount}</span>
                    </div>
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {rule.lastRun ? new Date(rule.lastRun).toLocaleString('tr-TR') : '-'}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {rule.nextRun ? new Date(rule.nextRun).toLocaleString('tr-TR') : '-'}
                  </td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div className="flex items-center space-x-2">
                      <button 
                        onClick={() => toggleAutomationRule(rule.id, rule.status === 'active' ? 'inactive' : 'active')}
                        className={`${rule.status === 'active' ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'}`}
                        title={rule.status === 'active' ? 'Durdur' : 'Başlat'}
                      >
                        {rule.status === 'active' ? 
                          <XCircleIcon className="h-4 w-4" /> : 
                          <CheckCircleIcon className="h-4 w-4" />
                        }
                      </button>
                      <button className="text-blue-600 hover:text-blue-900" title="Düzenle">
                        <PencilIcon className="h-4 w-4" />
                      </button>
                      <button className="text-gray-600 hover:text-gray-900" title="Görüntüle">
                        <EyeIcon className="h-4 w-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Rule Types */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div className="bg-white p-6 rounded-lg shadow">
          <h4 className="font-medium text-gray-900 mb-3">📅 Zamanlanmış Kurallar</h4>
          <div className="space-y-2">
            {automationRules.filter(rule => rule.type === 'scheduled').map(rule => (
              <div key={rule.id} className="text-sm">
                <div className="font-medium text-gray-900">{rule.name}</div>
                <div className="text-gray-500">{rule.schedule}</div>
              </div>
            ))}
          </div>
        </div>
        <div className="bg-white p-6 rounded-lg shadow">
          <h4 className="font-medium text-gray-900 mb-3">⚡ Tetikleyici Kurallar</h4>
          <div className="space-y-2">
            {automationRules.filter(rule => rule.type === 'trigger').map(rule => (
              <div key={rule.id} className="text-sm">
                <div className="font-medium text-gray-900">{rule.name}</div>
                <div className="text-gray-500">{rule.trigger}</div>
              </div>
            ))}
          </div>
        </div>
        <div className="bg-white p-6 rounded-lg shadow">
          <h4 className="font-medium text-gray-900 mb-3">🔍 Koşullu Kurallar</h4>
          <div className="space-y-2">
            {automationRules.filter(rule => rule.type === 'condition').map(rule => (
              <div key={rule.id} className="text-sm">
                <div className="font-medium text-gray-900">{rule.name}</div>
                <div className="text-gray-500">{rule.trigger}</div>
              </div>
            ))}
          </div>
        </div>
        <div className="bg-white p-6 rounded-lg shadow">
          <h4 className="font-medium text-gray-900 mb-3">🔗 Webhook Kuralları</h4>
          <div className="space-y-2">
            {automationRules.filter(rule => rule.type === 'webhook').map(rule => (
              <div key={rule.id} className="text-sm">
                <div className="font-medium text-gray-900">{rule.name}</div>
                <div className="text-gray-500">{rule.trigger}</div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );

  const renderSecurityTab = () => (
    <div className="space-y-6">
      <h2 className="text-xl font-semibold text-gray-900">Güvenlik Yönetimi</h2>
      
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {/* Security Metrics */}
        <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <div className="flex items-center">
            <div className="p-2 bg-blue-100 rounded-lg">
              <span className="text-2xl">🔒</span>
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-600">Güvenlik Düzeyi</p>
              <p className="text-2xl font-bold text-gray-900">{securityLevel}</p>
            </div>
          </div>
        </div>

        <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <div className="flex items-center">
            <div className="p-2 bg-green-100 rounded-lg">
              <span className="text-2xl">🔒</span>
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-600">Son Güvenlik Kontrolü</p>
              <p className="text-2xl font-bold text-gray-900">{new Date(lastUpdate).toLocaleString('tr-TR')}</p>
            </div>
          </div>
        </div>

        <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <div className="flex items-center">
            <div className="p-2 bg-purple-100 rounded-lg">
              <span className="text-2xl">🔒</span>
            </div>
            <div className="ml-4">
              <p className="text-sm font-medium text-gray-600">Son Kullanıcı Aktivitesi</p>
              <p className="text-2xl font-bold text-gray-900">{userActivity.length} aktivite</p>
            </div>
          </div>
        </div>
      </div>

      <div className="mt-6 pt-6 border-t border-gray-200">
        <h4 className="font-medium text-gray-900 mb-3">Güvenlik Ayarları</h4>
        <div className="space-y-3">
          <div className="flex items-center justify-between">
            <span className="text-sm text-gray-600">Güvenlik Düzeyini Değiştir</span>
            <select
              value={securityLevel}
              onChange={(e) => setSecurityLevel(e.target.value as any)}
              className="rounded border-gray-300 text-gray-900 focus:ring-blue-500"
            >
              <option value="standard">Standart</option>
              <option value="enhanced">Gelişmiş</option>
              <option value="maximum">Maksimum</option>
            </select>
          </div>
        </div>
      </div>
    </div>
  );

  if (isLoading && !roles.length) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="text-center">
          <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-4 text-lg text-gray-600">Configuration Interface Yükleniyor...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold mb-2">⚙️ Gelişmiş Konfigürasyon</h1>
            <p className="text-indigo-100">Sistem ayarlarını yönetin ve performansı izleyin</p>
          </div>
          <div className="text-right">
            {/* Real-time Status */}
            <div className="flex items-center space-x-4 mb-2">
              <div className={`flex items-center px-3 py-1 rounded-full text-sm font-medium ${
                apiStatus === 'online' ? 'bg-green-100 text-green-800' :
                apiStatus === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'
              }`}>
                <span className={`w-2 h-2 rounded-full mr-2 ${
                  apiStatus === 'online' ? 'bg-green-500' :
                  apiStatus === 'maintenance' ? 'bg-yellow-500' : 'bg-red-500'
                }`}></span>
                API {apiStatus === 'online' ? 'Online' : apiStatus === 'maintenance' ? 'Maintenance' : 'Offline'}
              </div>
            </div>
            <p className="text-indigo-200 text-sm">Son güncelleme: {lastUpdate}</p>
          </div>
        </div>
      </div>

      {/* Navigation Tabs */}
      <div className="bg-white shadow rounded-lg">
        <div className="border-b border-gray-200">
          <nav className="-mb-px flex space-x-8">
            {[
              { id: 'overview', name: 'Genel Bakış', icon: '🌐' },
              { id: 'roles', name: 'Rol Yönetimi', icon: '👥' },
              { id: 'api', name: 'API Yönetimi', icon: '🔗' },
              { id: 'performance', name: 'Performans', icon: '📊' },
              { id: 'automation', name: 'Otomasyon', icon: '🤖' },
              { id: 'security', name: 'Güvenlik', icon: '🔒' }
            ].map((tab) => (
              <button
                key={tab.id}
                onClick={() => setActiveTab(tab.id as any)}
                className={`py-4 px-6 border-b-2 font-medium text-sm transition-colors flex items-center space-x-2 ${
                  activeTab === tab.id
                    ? 'border-indigo-500 text-indigo-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                }`}
              >
                <span>{tab.icon}</span>
                <span>{tab.name}</span>
              </button>
            ))}
          </nav>
        </div>

        {/* Tab Content */}
        <div className="p-6">
          {/* Overview Tab */}
          {activeTab === 'overview' && renderOverview()}

          {/* Role Management Tab */}
          {activeTab === 'roles' && renderRolesTab()}

          {/* API Management Tab */}
          {activeTab === 'api' && renderAPITab()}

          {/* Performance Tab */}
          {activeTab === 'performance' && renderPerformanceTab()}

          {/* Automation Tab */}
          {activeTab === 'automation' && renderAutomationTab()}

          {/* Security Tab */}
          {activeTab === 'security' && renderSecurityTab()}
        </div>
      </div>
    </div>
  );
};

export default AdvancedConfigurationInterface; 