import React, { useState, useEffect } from 'react';
import { Link, useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import LanguageSwitcher from './LanguageSwitcher';
import { HomeIcon, ChartBarIcon, BuildingStorefrontIcon, TruckIcon, ShoppingCartIcon, ArchiveBoxIcon, DocumentChartBarIcon, Cog6ToothIcon } from '@heroicons/react/24/outline';
import { NavLink } from 'react-router-dom';

interface User {
  id: string;
  username: string;
  email: string;
  role: 'super_admin' | 'admin' | 'dropshipper' | 'integrator' | 'support';
  firstName: string;
  lastName: string;
  avatar?: string;
}

interface LayoutProps {
  user: User | null;
  children: React.ReactNode;
}

const Layout: React.FC<LayoutProps> = ({ user, children }) => {
  const { t } = useTranslation();
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [notifications, setNotifications] = useState<any[]>([]);
  const [showNotifications, setShowNotifications] = useState(false);
  const location = useLocation();

  useEffect(() => {
    // WebSocket connection for real-time notifications
    try {
      const ws = new WebSocket('ws://localhost:8080/notifications');
      
      ws.onmessage = (event) => {
        const notification = JSON.parse(event.data);
        setNotifications(prev => [notification, ...prev.slice(0, 9)]); // Keep last 10
      };

      return () => {
        ws.close();
      };
    } catch (error) {
      console.log('WebSocket connection failed:', error);
    }
  }, []);

  const getRoleDisplayName = (role: string) => {
    return t(`users.${role}`);
  };

  const getRoleColor = (role: string) => {
    const roleColors = {
      super_admin: 'bg-red-100 text-red-800',
      admin: 'bg-blue-100 text-blue-800',
      dropshipper: 'bg-green-100 text-green-800',
      integrator: 'bg-purple-100 text-purple-800',
      support: 'bg-yellow-100 text-yellow-800'
    };
    return roleColors[role as keyof typeof roleColors] || 'bg-gray-100 text-gray-800';
  };

  const getMenuIcon = (path: string) => {
    const icons = {
      '/dashboard': '📊',
      '/': '📊',
      '/marketplaces': '🛒',
      '/dropshipping': '📦',
      '/reports': '📈',
      '/users': '👥',
      '/settings': '⚙️'
    };
    return icons[path as keyof typeof icons] || '📄';
  };

  const isActiveRoute = (path: string) => {
    return location.pathname === path || (path === '/' && location.pathname === '/dashboard');
  };

  const getAvailableRoutes = () => {
    if (!user) return [];

    interface Route {
      path: string;
      titleKey: string;
      roles: string[];
    }

    const allRoutes: Route[] = [
      { path: '/', titleKey: 'navigation.dashboard', roles: ['super_admin', 'admin', 'dropshipper', 'integrator', 'support'] },
      { path: '/marketplaces', titleKey: 'navigation.marketplaces', roles: ['super_admin', 'admin', 'integrator'] },
      { path: '/dropshipping', titleKey: 'navigation.dropshipping', roles: ['super_admin', 'admin', 'dropshipper'] },
      { path: '/reports', titleKey: 'navigation.reports', roles: ['super_admin', 'admin', 'dropshipper'] },
      { path: '/users', titleKey: 'navigation.users', roles: ['super_admin'] },
      { path: '/settings', titleKey: 'navigation.settings', roles: ['super_admin', 'admin', 'dropshipper', 'integrator', 'support'] }
    ];

    return allRoutes.filter(route => route.roles.includes(user.role));
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
          <h2 className="text-2xl font-bold text-gray-900 mb-2">{t('auth.accessDenied')}</h2>
          <p className="text-gray-600 mb-4">{t('auth.loginRequired')}</p>
          <button 
            onClick={() => window.location.href = '/admin/index.php?route=common/login'}
            className="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors"
          >
            {t('auth.login')}
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Mobile sidebar overlay */}
      {sidebarOpen && (
        <div 
          className="fixed inset-0 z-40 lg:hidden"
          onClick={() => setSidebarOpen(false)}
        >
          <div className="fixed inset-0 bg-gray-600 bg-opacity-75"></div>
        </div>
      )}

      {/* Sidebar */}
      <div className={`fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 ${
        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
      }`}>
        <div className="flex flex-col h-full">
          {/* Logo */}
          <div className="flex items-center justify-center h-16 bg-blue-600 text-white">
            <div className="flex items-center">
              <span className="text-2xl mr-2">🚀</span>
              <span className="text-xl font-bold">MesChain-Sync</span>
            </div>
          </div>

          {/* User Info */}
          <div className="p-4 border-b border-gray-200">
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
          <nav className="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
            <NavLink
              to="/dashboard"
              className={({ isActive }) =>
                `flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors ${
                  isActive
                    ? 'bg-blue-100 text-blue-700'
                    : 'text-gray-700 hover:bg-gray-100'
                }`
              }
            >
              <HomeIcon className="w-5 h-5 mr-3" />
              {t('navigation.dashboard')}
            </NavLink>

            <NavLink
              to="/analytics"
              className={({ isActive }) =>
                `flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors ${
                  isActive
                    ? 'bg-blue-100 text-blue-700'
                    : 'text-gray-700 hover:bg-gray-100'
                }`
              }
            >
              <ChartBarIcon className="w-5 h-5 mr-3" />
              {t('analytics.advancedAnalytics')}
            </NavLink>

            <NavLink
              to="/marketplaces"
              className={({ isActive }) =>
                `flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors ${
                  isActive
                    ? 'bg-blue-100 text-blue-700'
                    : 'text-gray-700 hover:bg-gray-100'
                }`
              }
            >
              <BuildingStorefrontIcon className="w-5 h-5 mr-3" />
              {t('marketplaces.title')}
            </NavLink>

            <NavLink
              to="/dropshipping"
              className={({ isActive }) =>
                `flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors ${
                  isActive
                    ? 'bg-blue-100 text-blue-700'
                    : 'text-gray-700 hover:bg-gray-100'
                }`
              }
            >
              <TruckIcon className="w-5 h-5 mr-3" />
              {t('dropshipping.title')}
            </NavLink>

            <NavLink
              to="/orders"
              className={({ isActive }) =>
                `flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors ${
                  isActive
                    ? 'bg-blue-100 text-blue-700'
                    : 'text-gray-700 hover:bg-gray-100'
                }`
              }
            >
              <ShoppingCartIcon className="w-5 h-5 mr-3" />
              {t('orders.title')}
            </NavLink>

            <NavLink
              to="/inventory"
              className={({ isActive }) =>
                `flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors ${
                  isActive
                    ? 'bg-blue-100 text-blue-700'
                    : 'text-gray-700 hover:bg-gray-100'
                }`
              }
            >
              <ArchiveBoxIcon className="w-5 h-5 mr-3" />
              {t('inventory.title')}
            </NavLink>

            <NavLink
              to="/reports"
              className={({ isActive }) =>
                `flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors ${
                  isActive
                    ? 'bg-blue-100 text-blue-700'
                    : 'text-gray-700 hover:bg-gray-100'
                }`
              }
            >
              <DocumentChartBarIcon className="w-5 h-5 mr-3" />
              {t('reports.title')}
            </NavLink>

            <NavLink
              to="/trendyol-test"
              className={({ isActive }) =>
                `group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors ${
                  isActive
                    ? 'bg-blue-100 text-blue-700'
                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                }`
              }
            >
              <Cog6ToothIcon className="mr-3 h-5 w-5 flex-shrink-0" />
              Trendyol Test
            </NavLink>

            <NavLink
              to="/settings"
              className={({ isActive }) =>
                `group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors ${
                  isActive
                    ? 'bg-blue-100 text-blue-700'
                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                }`
              }
            >
              <Cog6ToothIcon className="mr-3 h-5 w-5 flex-shrink-0" />
              {t('settings.title')}
              <span className="ml-auto text-xs text-gray-400">{t('settings.subtitle')}</span>
            </NavLink>
          </nav>

          {/* System Status - Fixed at bottom */}
          <div className="p-4 border-t border-gray-200 bg-gray-50">
            <div className="flex items-center justify-between text-sm">
              <span className="text-gray-500">{t('dashboard.systemStatus')}</span>
              <div className="flex items-center">
                <span className="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                <span className="text-green-600 font-medium">{t('common.online')}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Main content */}
      <div className="lg:pl-64">
        {/* Top navigation */}
        <div className="sticky top-0 z-40 bg-white shadow-sm border-b border-gray-200">
          <div className="flex items-center justify-between h-16 px-4">
            <div className="flex items-center">
              <button
                onClick={() => setSidebarOpen(!sidebarOpen)}
                className="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500"
              >
                <span className="sr-only">{t('common.menu')}</span>
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
                        {availableRoutes.find(route => route.path === location.pathname)?.titleKey 
                          ? t(availableRoutes.find(route => route.path === location.pathname)!.titleKey)
                          : t('navigation.dashboard')}
                      </span>
                    </div>
                  </li>
                </ol>
              </nav>
            </div>

            <div className="flex items-center space-x-4">
              {/* Language Switcher */}
              <LanguageSwitcher />

              {/* Notifications */}
              <div className="relative">
                <button
                  onClick={() => setShowNotifications(!showNotifications)}
                  className="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500"
                >
                  <span className="sr-only">{t('notifications.title')}</span>
                  <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 17h5l-5 5v-5zM4 19h6v-6H4v6zM16 3h5v5h-5V3zM4 3h6v6H4V3z" />
                  </svg>
                  {notifications.length > 0 && (
                    <span className="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-400 ring-2 ring-white"></span>
                  )}
                </button>

                {/* Notifications dropdown */}
                {showNotifications && (
                  <div className="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                    <div className="py-1">
                      <div className="px-4 py-2 text-sm font-medium text-gray-900 border-b border-gray-200">
                        {t('notifications.title')} ({notifications.length})
                      </div>
                      {notifications.length === 0 ? (
                        <div className="px-4 py-3 text-sm text-gray-500">
                          {t('notifications.title')} 0
                        </div>
                      ) : (
                        notifications.slice(0, 5).map((notification, index) => (
                          <div key={index} className="px-4 py-3 hover:bg-gray-50 border-b border-gray-100">
                            <p className="text-sm text-gray-900">{notification.message}</p>
                            <p className="text-xs text-gray-500 mt-1">{notification.time}</p>
                          </div>
                        ))
                      )}
                    </div>
                  </div>
                )}
              </div>

              {/* User menu */}
              <div className="relative">
                <button
                  onClick={handleLogout}
                  className="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                  title={t('auth.logout')}
                >
                  <span className="sr-only">{t('auth.logout')}</span>
                  <div className="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold">
                    {user.username.charAt(0).toUpperCase()}
                  </div>
                </button>
              </div>
            </div>
          </div>
        </div>

        {/* Page content */}
        <main className="flex-1 min-h-screen">
          <div className="py-6">
            {children}
          </div>
        </main>
      </div>
    </div>
  );
};

export default Layout; 