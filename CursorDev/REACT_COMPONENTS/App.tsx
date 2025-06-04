import React, { useState, useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import Dashboard from './Dashboard';
import DropshipperDashboard from './DropshipperDashboard';
import Layout from './components/Layout';
import MarketplacesPage from './pages/MarketplacesPage';
import DropshippingPage from './pages/DropshippingPage';
import ReportsPage from './pages/ReportsPage';
import SettingsPage from './pages/SettingsPage';
import UsersPage from './pages/UsersPage';
import apiService from './services/api';
import './App.css';

interface User {
  id: string;
  name: string;
  email: string;
  role: 'super_admin' | 'admin' | 'dropshipper' | 'integrator' | 'support';
  permissions: string[];
}

interface AppState {
  user: User | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  error: string | null;
}

const App: React.FC = () => {
  const [appState, setAppState] = useState<AppState>({
    user: null,
    isAuthenticated: false,
    isLoading: true,
    error: null
  });

  useEffect(() => {
    initializeApp();
  }, []);

  const initializeApp = async () => {
    try {
      setAppState(prev => ({ ...prev, isLoading: true }));

      // Check if user is authenticated via OpenCart session
      const userRole = (window as any).MESCHAIN_CONFIG?.userRole;
      const userName = (window as any).MESCHAIN_CONFIG?.userName;
      const userEmail = (window as any).MESCHAIN_CONFIG?.userEmail;
      const userPermissions = (window as any).MESCHAIN_CONFIG?.permissions || [];

      if (userRole && userName) {
        const user: User = {
          id: '1',
          name: userName,
          email: userEmail || '',
          role: userRole,
          permissions: userPermissions
        };

        setAppState({
          user,
          isAuthenticated: true,
          isLoading: false,
          error: null
        });

        // Test API connection
        const healthCheck = await apiService.healthCheck();
        if (!healthCheck.success) {
          console.warn('API health check failed:', healthCheck.error);
        }
      } else {
        setAppState({
          user: null,
          isAuthenticated: false,
          isLoading: false,
          error: 'Kullanıcı oturumu bulunamadı'
        });
      }
    } catch (error) {
      console.error('App initialization error:', error);
      setAppState({
        user: null,
        isAuthenticated: false,
        isLoading: false,
        error: 'Uygulama başlatılırken bir hata oluştu'
      });
    }
  };

  const handleLogout = () => {
    setAppState({
      user: null,
      isAuthenticated: false,
      isLoading: false,
      error: null
    });
    
    // Redirect to OpenCart admin login
    window.location.href = '/admin/index.php?route=common/login';
  };

  const getDashboardComponent = (userRole: string) => {
    switch (userRole) {
      case 'dropshipper':
        return <DropshipperDashboard />;
      case 'super_admin':
      case 'admin':
      case 'integrator':
      case 'support':
      default:
        return <Dashboard />;
    }
  };

  const getAvailableRoutes = (userRole: string) => {
    const baseRoutes = [
      { path: '/dashboard', component: getDashboardComponent(userRole), title: 'Dashboard' }
    ];

    switch (userRole) {
      case 'super_admin':
        return [
          ...baseRoutes,
          { path: '/marketplaces', component: <MarketplacesPage />, title: 'Marketplaces' },
          { path: '/dropshipping', component: <DropshippingPage />, title: 'Dropshipping' },
          { path: '/reports', component: <ReportsPage />, title: 'Reports' },
          { path: '/users', component: <UsersPage />, title: 'Users' },
          { path: '/settings', component: <SettingsPage />, title: 'Settings' }
        ];
      
      case 'admin':
        return [
          ...baseRoutes,
          { path: '/marketplaces', component: <MarketplacesPage />, title: 'Marketplaces' },
          { path: '/dropshipping', component: <DropshippingPage />, title: 'Dropshipping' },
          { path: '/reports', component: <ReportsPage />, title: 'Reports' },
          { path: '/settings', component: <SettingsPage />, title: 'Settings' }
        ];
      
      case 'dropshipper':
        return [
          ...baseRoutes,
          { path: '/dropshipping', component: <DropshippingPage />, title: 'My Products' },
          { path: '/reports', component: <ReportsPage />, title: 'My Reports' }
        ];
      
      case 'integrator':
        return [
          ...baseRoutes,
          { path: '/marketplaces', component: <MarketplacesPage />, title: 'Marketplaces' },
          { path: '/reports', component: <ReportsPage />, title: 'Reports' }
        ];
      
      case 'support':
        return [
          ...baseRoutes,
          { path: '/reports', component: <ReportsPage />, title: 'Reports' }
        ];
      
      default:
        return baseRoutes;
    }
  };

  if (appState.isLoading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600 mx-auto"></div>
          <h2 className="mt-4 text-xl font-semibold text-gray-900">MesChain-Sync Yükleniyor...</h2>
          <p className="mt-2 text-gray-600">Lütfen bekleyin</p>
        </div>
      </div>
    );
  }

  if (appState.error || !appState.isAuthenticated) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="max-w-md w-full bg-white shadow-lg rounded-lg p-6">
          <div className="text-center">
            <div className="text-6xl mb-4">🔒</div>
            <h2 className="text-2xl font-bold text-gray-900 mb-2">Erişim Engellendi</h2>
            <p className="text-gray-600 mb-4">
              {appState.error || 'Bu sayfaya erişim için giriş yapmanız gerekiyor.'}
            </p>
            <button
              onClick={() => window.location.href = '/admin/index.php?route=common/login'}
              className="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors"
            >
              Giriş Yap
            </button>
          </div>
        </div>
      </div>
    );
  }

  const availableRoutes = getAvailableRoutes(appState.user!.role);

  return (
    <Router basename="/admin/extension/module/meschain">
      <div className="App">
        <Layout 
          user={appState.user!} 
          onLogout={handleLogout}
          availableRoutes={availableRoutes}
        >
          <Routes>
            <Route path="/" element={<Navigate to="/dashboard" replace />} />
            
            {availableRoutes.map((route, index) => (
              <Route 
                key={index}
                path={route.path} 
                element={route.component} 
              />
            ))}
            
            {/* Catch-all route for unauthorized pages */}
            <Route 
              path="*" 
              element={
                <div className="min-h-screen bg-gray-50 flex items-center justify-center">
                  <div className="text-center">
                    <div className="text-6xl mb-4">🚫</div>
                    <h2 className="text-2xl font-bold text-gray-900 mb-2">Sayfa Bulunamadı</h2>
                    <p className="text-gray-600 mb-4">
                      Aradığınız sayfa mevcut değil veya erişim yetkiniz bulunmuyor.
                    </p>
                    <button
                      onClick={() => window.location.href = '/admin/extension/module/meschain/dashboard'}
                      className="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors"
                    >
                      Dashboard'a Dön
                    </button>
                  </div>
                </div>
              } 
            />
          </Routes>
        </Layout>
      </div>
    </Router>
  );
};

export default App; 