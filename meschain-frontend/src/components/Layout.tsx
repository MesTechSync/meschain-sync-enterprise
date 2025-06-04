import React, { useState, useEffect } from 'react';
import { useLocation, useNavigate, Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import NotificationSystem from './NotificationSystem';
import LanguageSelector from './LanguageSelector';

interface User {
  id: string;
  username: string;
  email: string;
  role: 'super_admin' | 'admin' | 'dropshipper' | 'integrator' | 'tech_support';
  permissions: string[];
  token: string;
}

interface LayoutProps {
  user: User | null;
  children: React.ReactNode;
}

const Layout: React.FC<LayoutProps> = ({ user, children }) => {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const location = useLocation();
  const navigate = useNavigate();
  const { t, i18n } = useTranslation();

  useEffect(() => {
    // WebSocket connection for real-time notifications
    try {
    const ws = new WebSocket('ws://localhost:8080/notifications');
    
    ws.onmessage = (event) => {
      const notification = JSON.parse(event.data);
        console.log('Received notification:', notification);
    };

    return () => {
      ws.close();
    };
    } catch (error) {
      console.log('WebSocket connection failed:', error);
    }
  }, []);

  const handleNavigation = (path: string) => {
    if (location.pathname === path) {
      return;
    }
    setSidebarOpen(false);
    navigate(path);
  };

  const getRoleDisplayName = (role: string) => {
    const roleNames = {
      super_admin: 'Süper Admin',
      admin: 'Admin',
      dropshipper: 'Dropshipper',
      integrator: 'Entegratör',
      tech_support: 'Teknik Servis'
    };
    return roleNames[role as keyof typeof roleNames] || role;
  };

  const getRoleColor = (role: string) => {
    const roleColors = {
      super_admin: 'bg-red-100 text-red-800',
      admin: 'bg-blue-100 text-blue-800',
      dropshipper: 'bg-green-100 text-green-800',
      integrator: 'bg-purple-100 text-purple-800',
      tech_support: 'bg-yellow-100 text-yellow-800'
    };
    return roleColors[role as keyof typeof roleColors] || 'bg-gray-100 text-gray-800';
  };

  const getMenuIcon = (path: string): string => {
    const icons: { [key: string]: string } = {
      '/': '🏠',
      '/dashboard': '📊',
      '/marketplaces': '🛒',
      '/dropshipping': '📦',
      '/reports': '📈',
      '/settings': '⚙️',
      '/users': '👥',
      '/trendyol-test': '🧪',
      '/trendyol-api-info': 'ℹ️',
      '/trendyol-dashboard': '🛍️',
      '/trendyol-real-time-panel': '📡',
      '/marketplace/trendyol': '🛍️',
      '/marketplace/hepsiburada': '🛒',
      '/marketplace/ciceksepeti': '🌸',
      '/marketplace/n11': '🟣',
      '/marketplace/amazon': '📦',
      '/marketplace/ebay': '🔵',
      '/marketplace/etsy': '🎨',
      '/analytics': '📊',
      '/ai-analytics': '🤖',
      '/customer-behavior-ai': '🧠',
      '/advanced-automation': '🔄',
      '/enterprise-intelligence': '🏢',
      '/global-intelligence': '🌍',
      '/quantum-intelligence': '⚛️',
      '/universal-intelligence': '🌌',
      '/transcendental-intelligence': '👑',
      '/super-admin': '👑',
      '/integrator': '🔧',
      '/tech-support': '🛠️',
      '/dropshipper': '📦',
      '/advanced-analytics': '📈',
      '/n11-integration': '🟣',
      '/production-readiness': '🚀',
      '/advanced-data-visualization': '📊'
    };
    return icons[path] || '📄';
  };

  const isActiveRoute = (path: string) => {
    return location.pathname === path || (path === '/' && location.pathname === '/dashboard');
  };

  const changeLanguage = (lng: string) => {
    i18n.changeLanguage(lng);
  };

  const getAvailableRoutes = () => {
    if (!user) return [];

    interface Route {
      path: string;
      title: string;
      permission: string | null;
    }

    const baseRoutes: Route[] = [
      { path: '/', title: t('navigation.dashboard'), permission: null }
    ];

    const allRoutes: Route[] = [
      // Super Admin özel rotası
      ...(user.role === 'super_admin' ? [
        { path: '/super-admin', title: '👑 Super Admin Panel', permission: null }
      ] : []),
      { path: '/marketplaces', title: t('navigation.marketplaces'), permission: 'marketplace_manage' },
      { path: '/cross-marketplace', title: 'Cross-Marketplace', permission: 'marketplace_manage' },
      { path: '/trendyol-dashboard', title: 'Trendyol Mağaza', permission: 'marketplace_manage' },
      { path: '/trendyol-real-time-panel', title: '📡 Trendyol Canlı Panel', permission: 'marketplace_manage' },
      { path: '/trendyol-test', title: 'Trendyol Test', permission: null },
      { path: '/trendyol-api-info', title: 'API Rehberi', permission: null },
      { path: '/dropshipping', title: 'Dropshipping', permission: 'dropshipping_manage' },
      { path: '/analytics', title: t('navigation.analytics'), permission: 'analytics_view' },
      { path: '/advanced-analytics', title: 'Gelişmiş Analitik', permission: 'analytics_view' },
      { path: '/ai-analytics', title: '🤖 AI Analytics', permission: 'analytics_view' },
      { path: '/customer-behavior-ai', title: '👥 Customer Behavior AI', permission: 'analytics_view' },
      { path: '/advanced-automation', title: '⚡ Advanced Automation', permission: 'automation_manage' },
      { path: '/enterprise-intelligence', title: '🧠 Enterprise Intelligence', permission: 'enterprise_manage' },
      { path: '/global-intelligence', title: '🌍 Global Intelligence', permission: 'global_manage' },
      { path: '/quantum-intelligence', title: '⚛️ Quantum Intelligence', permission: 'quantum_manage' },
      { path: '/advanced-performance', title: '⚡ Performans İzleme', permission: 'analytics_view' },
      { path: '/smart-notifications', title: '🔔 Akıllı Bildirimler', permission: null },
      { path: '/advanced-reports', title: 'Gelişmiş Raporlar', permission: 'report_view' },
      { path: '/automation', title: 'Otomasyon', permission: 'automation_manage' },
      { path: '/communication', title: 'İletişim', permission: 'communication_manage' },
      { path: '/reports', title: t('navigation.reports'), permission: 'report_view' },
      { path: '/settings', title: t('navigation.settings'), permission: 'system_config' },
      { path: '/production-readiness', title: '🚀 Production Readiness', permission: 'marketplace_manage' },
      { path: '/users', title: 'Kullanıcılar', permission: 'user_manage' }
    ];

    const filteredRoutes = allRoutes.filter(route => 
      !route.permission || 
      user.permissions.includes(route.permission) || 
      user.permissions.includes('all')
    );

    return [...baseRoutes, ...filteredRoutes];
  };

  const handleLogout = () => {
    // Redirect to OpenCart admin login
    window.location.href = '/admin/index.php?route=common/login';
  };

  const availableRoutes = getAvailableRoutes();

  if (!user) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="text-6xl mb-4">🔒</div>
          <h2 className="text-2xl font-bold text-gray-900 mb-2">Erişim Engellendi</h2>
          <p className="text-gray-600 mb-4">Bu sayfaya erişim için giriş yapmanız gerekiyor.</p>
          <button 
            onClick={() => window.location.href = '/admin/index.php?route=common/login'}
            className="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors"
          >
            Giriş Yap
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 flex">
      {/* Mobile sidebar overlay */}
      {sidebarOpen && (
        <div 
          className="fixed inset-0 z-40 lg:hidden"
          onClick={() => setSidebarOpen(false)}
        >
          <div className="fixed inset-0 bg-gray-600 bg-opacity-75"></div>
        </div>
      )}

      {/* Sidebar - Fixed position */}
      <div className={`fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out ${
        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
      } lg:translate-x-0 lg:static lg:inset-0`}>
        <div className="flex flex-col h-full">
          {/* Header */}
          <div className="flex items-center justify-center h-16 bg-blue-600 text-white flex-shrink-0">
          <div className="flex items-center">
            <span className="text-2xl mr-2">🚀</span>
            <span className="text-xl font-bold">MesChain-Sync</span>
          </div>
        </div>

        {/* User Info */}
          <div className="p-4 border-b border-gray-200 flex-shrink-0">
          <div className="flex items-center">
            <div className="flex-shrink-0">
              <div className="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold">
                  {user.username.charAt(0).toUpperCase()}
              </div>
            </div>
            <div className="ml-3">
                <p className="text-sm font-medium text-gray-900">{user.username}</p>
              <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getRoleColor(user.role)}`}>
                {getRoleDisplayName(user.role)}
              </span>
            </div>
          </div>
        </div>

        {/* Navigation */}
          <nav className="flex-1 overflow-y-auto py-4">
          <div className="px-2 space-y-1">
            {availableRoutes.map((route, index) => (
                <button
                key={index}
                  onClick={() => handleNavigation(route.path)}
                  className={`w-full group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors ${
                  isActiveRoute(route.path)
                    ? 'bg-blue-100 text-blue-900'
                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                }`}
              >
                  <span className="mr-3 text-lg flex-shrink-0">{getMenuIcon(route.path)}</span>
                  <span className="flex-1 text-left">{route.title}</span>
                {isActiveRoute(route.path) && (
                    <span className="ml-2 w-2 h-2 bg-blue-600 rounded-full flex-shrink-0"></span>
                  )}
                </button>
              ))}

              {/* Marketplace Integrations */}
              {(user.role === 'admin' || user.role === 'integrator') && (
                <div className="space-y-1">
                  <h3 className="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Pazaryeri Entegrasyonları
                  </h3>
                  <Link
                    to="/marketplace/trendyol"
                    className={`group flex items-center px-2 py-2 text-sm font-medium rounded-md ${
                      location.pathname === '/marketplace/trendyol'
                        ? 'bg-blue-100 text-blue-900'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                    }`}
                  >
                    <span className="mr-3 text-lg">🟠</span>
                    Trendyol
                  </Link>
                  <Link
                    to="/marketplace/hepsiburada"
                    className={`group flex items-center px-2 py-2 text-sm font-medium rounded-md ${
                      location.pathname === '/marketplace/hepsiburada'
                        ? 'bg-blue-100 text-blue-900'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                    }`}
                  >
                    <span className="mr-3 text-lg">🟠</span>
                    Hepsiburada
                  </Link>
                  <Link
                    to="/marketplace/ciceksepeti"
                    className={`group flex items-center px-2 py-2 text-sm font-medium rounded-md ${
                      location.pathname === '/marketplace/ciceksepeti'
                        ? 'bg-blue-100 text-blue-900'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                    }`}
                  >
                    <span className="mr-3 text-lg">🌸</span>
                    ÇiçekSepeti
                  </Link>
                  <Link
                    to="/marketplace/n11"
                    className={`group flex items-center px-2 py-2 text-sm font-medium rounded-md ${
                      location.pathname === '/marketplace/n11'
                        ? 'bg-blue-100 text-blue-900'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                    }`}
                  >
                    <span className="mr-3 text-lg">🟣</span>
                    N11
                  </Link>
                  <Link
                    to="/marketplace/amazon"
                    className={`group flex items-center px-2 py-2 text-sm font-medium rounded-md ${
                      location.pathname === '/marketplace/amazon'
                        ? 'bg-blue-100 text-blue-900'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                    }`}
                  >
                    <span className="mr-3 text-lg">🟡</span>
                    Amazon
                  </Link>
                  <Link
                    to="/marketplace/ebay"
                    className={`group flex items-center px-2 py-2 text-sm font-medium rounded-md ${
                      location.pathname === '/marketplace/ebay'
                        ? 'bg-blue-100 text-blue-900'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                    }`}
                  >
                    <span className="mr-3 text-lg">🔵</span>
                    eBay
                  </Link>
                  <Link
                    to="/marketplace/etsy"
                    className={`group flex items-center px-2 py-2 text-sm font-medium rounded-md ${
                      location.pathname === '/marketplace/etsy'
                        ? 'bg-blue-100 text-blue-900'
                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                    }`}
                  >
                    <span className="mr-3 text-lg">🟠</span>
                    Etsy
                  </Link>
                </div>
              )}

              {/* Intelligence & AI Section */}
              {(user.role === 'super_admin' || user.role === 'admin') && (
                <div className="mb-6">
                  <h3 className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                    Intelligence & AI
                  </h3>
                  <div className="space-y-1">
                    <Link
                      to="/ai-analytics"
                      className={`group flex items-center px-3 py-2 text-sm font-medium rounded-md ${
                        location.pathname === '/ai-analytics'
                          ? 'bg-indigo-100 text-indigo-700'
                          : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                      }`}
                    >
                      <span className="mr-3 text-lg">🤖</span>
                      AI Analytics
                    </Link>
                    <Link
                      to="/customer-behavior-ai"
                      className={`group flex items-center px-3 py-2 text-sm font-medium rounded-md ${
                        location.pathname === '/customer-behavior-ai'
                          ? 'bg-indigo-100 text-indigo-700'
                          : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                      }`}
                    >
                      <span className="mr-3 text-lg">🧠</span>
                      Customer Behavior AI
                    </Link>
                    <Link
                      to="/advanced-automation"
                      className={`group flex items-center px-3 py-2 text-sm font-medium rounded-md ${
                        location.pathname === '/advanced-automation'
                          ? 'bg-indigo-100 text-indigo-700'
                          : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                      }`}
                    >
                      <span className="mr-3 text-lg">🔄</span>
                      Advanced Automation
                    </Link>
                    <Link
                      to="/enterprise-intelligence"
                      className={`group flex items-center px-3 py-2 text-sm font-medium rounded-md ${
                        location.pathname === '/enterprise-intelligence'
                          ? 'bg-indigo-100 text-indigo-700'
                          : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                      }`}
                    >
                      <span className="mr-3 text-lg">🏢</span>
                      Enterprise Intelligence
                    </Link>
                    <Link
                      to="/global-intelligence"
                      className={`group flex items-center px-3 py-2 text-sm font-medium rounded-md ${
                        location.pathname === '/global-intelligence'
                          ? 'bg-indigo-100 text-indigo-700'
                          : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                      }`}
                    >
                      <span className="mr-3 text-lg">🌍</span>
                      Global Intelligence
                    </Link>
                    <Link
                      to="/quantum-intelligence"
                      className={`group flex items-center px-3 py-2 text-sm font-medium rounded-md ${
                        location.pathname === '/quantum-intelligence'
                          ? 'bg-indigo-100 text-indigo-700'
                          : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                      }`}
                    >
                      <span className="mr-3 text-lg">⚛️</span>
                      Quantum Intelligence
                    </Link>
                    <Link
                      to="/universal-intelligence"
                      className={`group flex items-center px-3 py-2 text-sm font-medium rounded-md ${
                        location.pathname === '/universal-intelligence'
                          ? 'bg-indigo-100 text-indigo-700'
                          : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                      }`}
                    >
                      <span className="mr-3 text-lg">🌌</span>
                      Universal Intelligence
                    </Link>
                    {user.role === 'super_admin' && (
                      <Link
                        to="/transcendental-intelligence"
                        className={`group flex items-center px-3 py-2 text-sm font-medium rounded-md ${
                          location.pathname === '/transcendental-intelligence'
                            ? 'bg-gradient-to-r from-yellow-100 to-pink-100 text-purple-700'
                            : 'text-gray-600 hover:bg-gradient-to-r hover:from-yellow-50 hover:to-pink-50 hover:text-purple-700'
                        }`}
                      >
                        <span className="mr-3 text-lg">👑</span>
                        Transcendental Intelligence
                      </Link>
                    )}
                    {user.role === 'super_admin' && (
                      <Link
                        to="/absolute-source-intelligence"
                        className={`group flex items-center px-3 py-2 text-sm font-medium rounded-md ${
                          location.pathname === '/absolute-source-intelligence'
                            ? 'bg-gradient-to-r from-white via-yellow-100 to-pink-100 text-purple-700 border border-gold-300 shadow-lg'
                            : 'text-gray-600 hover:bg-gradient-to-r hover:from-white hover:via-yellow-50 hover:to-pink-50 hover:text-purple-700'
                        }`}
                      >
                        <span className="mr-3 text-lg">☀️</span>
                        Absolute Source Intelligence
                      </Link>
                    )}
                  </div>
                </div>
              )}

              {/* Advanced Features Section */}
              {(user?.role === 'super_admin' || user?.role === 'admin' || user?.role === 'integrator') && (
                <div className="px-3 py-2">
                  <p className="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Gelişmiş Özellikler
                  </p>
                  <div className="mt-1 space-y-1">
                    <Link
                      to="/analytics"
                      className={`group flex items-center px-3 py-2 text-sm font-medium rounded-md ${
                        location.pathname === '/analytics'
                          ? 'bg-indigo-100 text-indigo-700'
                          : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                      }`}
                    >
                      <span className="mr-3 text-lg">{getMenuIcon('/analytics')}</span>
                      Analitik
                    </Link>
                    <Link
                      to="/advanced-performance"
                      className={`group flex items-center px-3 py-2 text-sm font-medium rounded-md ${
                        location.pathname === '/advanced-performance'
                          ? 'bg-indigo-100 text-indigo-700'
                          : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                      }`}
                    >
                      <span className="mr-3 text-lg">{getMenuIcon('/advanced-performance')}</span>
                      Performans İzleme
                    </Link>
                    <Link
                      to="/smart-notifications"
                      className={`group flex items-center px-3 py-2 text-sm font-medium rounded-md ${
                        location.pathname === '/smart-notifications'
                          ? 'bg-indigo-100 text-indigo-700'
                          : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                      }`}
                    >
                      <span className="mr-3 text-lg">{getMenuIcon('/smart-notifications')}</span>
                      Akıllı Bildirimler
                    </Link>
                    <Link
                      to="/automation"
                      className={`group flex items-center px-3 py-2 text-sm font-medium rounded-md ${
                        location.pathname === '/automation'
                          ? 'bg-indigo-100 text-indigo-700'
                          : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                      }`}
                    >
                      <span className="mr-3 text-lg">{getMenuIcon('/automation')}</span>
                      Otomasyon
                    </Link>
                    <Link
                      to="/communication"
                      className={`group flex items-center px-3 py-2 text-sm font-medium rounded-md ${
                        location.pathname === '/communication'
                          ? 'bg-indigo-100 text-indigo-700'
                          : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                      }`}
                    >
                      <span className="mr-3 text-lg">{getMenuIcon('/communication')}</span>
                      İletişim
                    </Link>
                    {(user?.role === 'super_admin' || user?.role === 'admin') && (
                      <Link
                        to="/advanced-config"
                        className={`group flex items-center px-3 py-2 text-sm font-medium rounded-md ${
                          location.pathname === '/advanced-config'
                            ? 'bg-indigo-100 text-indigo-700'
                            : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                        }`}
                      >
                        <span className="mr-3 text-lg">{getMenuIcon('/advanced-config')}</span>
                        Gelişmiş Ayarlar
              </Link>
                    )}
                  </div>
                </div>
              )}
          </div>
        </nav>

        {/* System Status */}
          <div className="p-4 border-t border-gray-200 flex-shrink-0">
          <div className="flex items-center justify-between text-sm">
            <span className="text-gray-500">Sistem Durumu</span>
            <div className="flex items-center">
              <span className="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
              <span className="text-green-600 font-medium">Çevrimiçi</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Main content area - Flex grow to fill remaining space */}
      <div className="flex-1 flex flex-col min-w-0">
        {/* Top navigation */}
        <div className="sticky top-0 z-30 bg-white shadow-sm border-b border-gray-200">
          <div className="flex items-center justify-between h-16 px-4 lg:px-6">
            <div className="flex items-center">
              <button
                onClick={() => setSidebarOpen(!sidebarOpen)}
                className="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500"
              >
                <span className="sr-only">Menüyü aç</span>
                <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                </svg>
              </button>

              {/* Breadcrumb */}
              <nav className="ml-4 flex" aria-label="Breadcrumb">
                <ol className="flex items-center space-x-4">
                  <li>
                    <div className="flex items-center">
                      <span className="text-sm font-medium text-gray-500">MesChain-Sync</span>
                    </div>
                  </li>
                  <li>
                    <div className="flex items-center">
                      <svg className="flex-shrink-0 h-5 w-5 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clipRule="evenodd" />
                      </svg>
                      <span className="ml-4 text-sm font-medium text-gray-900">
                        {availableRoutes.find(route => route.path === location.pathname)?.title || 'Dashboard'}
                      </span>
                    </div>
                  </li>
                </ol>
              </nav>
            </div>

            <div className="flex items-center space-x-4">
              {/* Language Switcher */}
              <LanguageSelector />

              {/* Notifications */}
              <NotificationSystem />

              {/* User menu */}
              <div className="relative">
                <button
                  onClick={handleLogout}
                  className="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <span className="sr-only">Kullanıcı menüsünü aç</span>
                  <div className="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold">
                    {user.username.charAt(0).toUpperCase()}
                  </div>
                </button>
              </div>
            </div>
          </div>
        </div>

        {/* Page content - Flex grow to fill remaining space */}
        <main className="flex-1 bg-gray-50">
          <div className="py-6">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
          {children}
            </div>
          </div>
        </main>
      </div>
    </div>
  );
};

export default Layout; 