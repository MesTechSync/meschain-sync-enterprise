import React, { Suspense, useEffect, useState } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { I18nextProvider } from 'react-i18next';
import i18n from './i18n';
import Layout from './components/Layout';
import ErrorBoundary from './components/ErrorBoundary';
import PWAPrompt from './components/PWAPrompt';
import { lazyWithRetry, preloadComponent } from './utils/performanceOptimization';
import './i18n'; // Import i18n configuration
import './App.css';

// Lazy load components with retry mechanism
const Dashboard = lazyWithRetry(() => import('./components/Dashboard'));
const AdminDashboard = lazyWithRetry(() => import('./components/AdminDashboard'));
const SuperAdminPanel = lazyWithRetry(() => import('./components/SuperAdminPanel'));
const IntegratorDashboard = lazyWithRetry(() => import('./components/IntegratorDashboard'));
const TechSupportDashboard = lazyWithRetry(() => import('./components/TechSupportDashboard'));
const DropshipperDashboard = lazyWithRetry(() => import('./components/DropshipperDashboard'));
const AnalyticsDashboard = lazyWithRetry(() => import('./components/AnalyticsDashboard'));
const AdvancedAnalytics = lazyWithRetry(() => import('./components/AdvancedAnalytics'));
const AdvancedAIAnalyticsDashboard = lazyWithRetry(() => import('./components/AdvancedAIAnalyticsDashboard'));
const CustomerBehaviorAI = lazyWithRetry(() => import('./components/CustomerBehaviorAI'));
const AdvancedAutomationCenter = lazyWithRetry(() => import('./components/AdvancedAutomationCenter'));
const EnterpriseIntelligenceDashboard = lazyWithRetry(() => import('./components/EnterpriseIntelligenceDashboard'));
const GlobalIntelligenceDashboard = lazyWithRetry(() => import('./components/GlobalIntelligenceDashboard'));
const QuantumIntelligenceDashboard = lazyWithRetry(() => import('./components/QuantumIntelligenceDashboard'));
const AdvancedPerformanceDashboard = lazyWithRetry(() => import('./components/AdvancedPerformanceDashboard'));
const SmartNotificationCenter = lazyWithRetry(() => import('./components/SmartNotificationCenter'));
const AdvancedReportsPage = lazyWithRetry(() => import('./pages/AdvancedReportsPage'));
const AutomationCenter = lazyWithRetry(() => import('./components/AutomationCenter'));
const CommunicationCenter = lazyWithRetry(() => import('./components/CommunicationCenter'));
const AdvancedConfigurationInterface = lazyWithRetry(() => import('./components/AdvancedConfigurationInterface'));
const CrossMarketplaceDashboard = lazyWithRetry(() => import('./components/CrossMarketplaceDashboard'));
const MarketplacesPage = lazyWithRetry(() => import('./pages/MarketplacesPage'));
const DropshippingPage = lazyWithRetry(() => import('./pages/DropshippingPage'));
const ReportsPage = lazyWithRetry(() => import('./pages/ReportsPage'));
const SettingsPage = lazyWithRetry(() => import('./pages/SettingsPage'));
const UsersPage = lazyWithRetry(() => import('./pages/UsersPage'));
const TrendyolTestPage = lazyWithRetry(() => import('./pages/TrendyolTestPage'));
const TrendyolApiInfo = lazyWithRetry(() => import('./pages/TrendyolApiInfo'));
const TrendyolDashboard = lazyWithRetry(() => import('./pages/TrendyolDashboard'));
const TrendyolIntegration = lazyWithRetry(() => import('./components/TrendyolIntegration'));
const TrendyolRealTimePanel = lazyWithRetry(() => import('./components/TrendyolRealTimePanel'));
const HepsiburadaIntegration = lazyWithRetry(() => import('./components/HepsiburadaIntegration'));
const CicekSepetiIntegration = lazyWithRetry(() => import('./components/CicekSepetiIntegration'));
const N11Integration = lazyWithRetry(() => import('./components/N11Integration'));
const EbayIntegration = lazyWithRetry(() => import('./components/EbayIntegration'));
const AmazonIntegration = lazyWithRetry(() => import('./components/AmazonIntegration'));
const RealTimeMonitoringDashboard = lazyWithRetry(() => import('./components/RealTimeMonitoringDashboard'));
const ProductionReadinessDashboard = lazyWithRetry(() => import('./components/ProductionReadinessDashboard'));
const AdvancedDataVisualization = lazyWithRetry(() => import('./components/AdvancedDataVisualization'));
const UniversalIntelligenceDashboard = lazyWithRetry(() => import('./components/UniversalIntelligenceDashboard'));
const TranscendentalIntelligenceDashboard = lazyWithRetry(() => import('./components/TranscendentalIntelligenceDashboard'));
const AbsoluteSourceIntelligenceDashboard = lazyWithRetry(() => import('./components/AbsoluteSourceIntelligenceDashboard'));

// Preload critical components
if (typeof window !== 'undefined') {
  // Preload dashboard components based on user role
  const userData = (window as any).MESCHAIN_CONFIG?.userData;
  if (userData) {
    switch (userData.role) {
      case 'super_admin':
        preloadComponent(() => import('./components/SuperAdminPanel'));
        break;
      case 'admin':
        preloadComponent(() => import('./components/AdminDashboard'));
        break;
      default:
        preloadComponent(() => import('./components/Dashboard'));
    }
  }
}

// Performance monitoring
const reportWebVitals = (metric: any) => {
  if (process.env.NODE_ENV === 'development') {
    console.log('Web Vitals:', metric);
  }
};

// User interface
interface User {
  id: string;
  username: string;
  email: string;
  role: 'super_admin' | 'admin' | 'dropshipper' | 'integrator' | 'tech_support';
  permissions: string[];
  token: string;
}

// Get user data from global config
const getUserData = (): User | null => {
  try {
    const config = (window as any).MESCHAIN_CONFIG;
    if (config && config.userData) {
      return config.userData;
    }
    
    // Fallback mock data for development
    return {
      id: '1',
      username: 'admin',
      email: 'admin@meschain.com',
      role: 'super_admin',
      permissions: ['marketplace_manage', 'product_manage', 'order_manage', 'report_view'],
      token: 'mock-token'
    };
  } catch (error) {
    console.error('Error getting user data:', error);
    return null;
  }
};

// Protected Route Component
interface ProtectedRouteProps {
  children: React.ReactNode;
  allowedRoles?: string[];
  requiredPermission?: string;
}

const ProtectedRoute: React.FC<ProtectedRouteProps> = ({ 
  children, 
  allowedRoles = [], 
  requiredPermission 
}) => {
  const [user, setUser] = useState<User | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    // Initialize user data
    const userData = getUserData();
    if (userData) {
      setUser(userData);
    } else {
      setError('Kullanıcı verisi alınamadı');
    }
    setIsLoading(false);
  }, []);

  if (isLoading) {
    return (
      <div className="flex items-center justify-center min-h-screen bg-gray-50">
        <div className="text-center">
          <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-indigo-600 mx-auto"></div>
          <p className="mt-4 text-lg text-gray-600">MesChain-Sync Yükleniyor...</p>
        </div>
      </div>
    );
  }

  if (error || !user) {
    return (
      <div className="flex items-center justify-center min-h-screen bg-gray-50">
          <div className="text-center">
          <div className="text-red-500 text-6xl mb-4">⚠️</div>
          <h2 className="text-2xl font-bold text-gray-900 mb-4">Hata Oluştu</h2>
          <p className="text-gray-600 mb-4">{error || 'Kullanıcı verisi alınamadı'}</p>
            <button
            onClick={() => window.location.reload()}
            className="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md transition-colors"
            >
            Sayfayı Yenile
            </button>
        </div>
      </div>
    );
  }

  if (allowedRoles.length > 0 && !allowedRoles.includes(user.role)) {
    return <Navigate to="/dashboard" replace />;
  }
  
  if (requiredPermission && !user.permissions.includes('all') && !user.permissions.includes(requiredPermission)) {
    return <Navigate to="/dashboard" replace />;
  }

  return <>{children}</>;
};

// Enhanced Loading Component with PWA features
const EnhancedLoadingSpinner: React.FC = () => (
  <div className="flex items-center justify-center min-h-screen bg-gray-50">
    <div className="text-center">
      <div className="meschain-spinner w-12 h-12 mx-auto mb-4"></div>
      <p className="text-gray-600 font-medium">MesChain yükleniyor...</p>
      <p className="text-sm text-gray-400 mt-2">PWA optimizasyonları aktif</p>
    </div>
  </div>
);

// Get appropriate dashboard based on user role
const getDashboardComponent = () => {
  const user = getUserData();
  
  if (!user) return <Dashboard />;
  
  switch (user.role) {
    case 'super_admin':
      return <SuperAdminPanel />;
    case 'admin':
      return <AdminDashboard />;
    case 'integrator':
      return <IntegratorDashboard />;
    case 'tech_support':
      return <TechSupportDashboard />;
    case 'dropshipper':
      return <DropshipperDashboard />;
    default:
      return <Dashboard />;
  }
};

const App: React.FC = () => {
  const [isOnline, setIsOnline] = useState(navigator.onLine);

  // Handle online/offline status
  useEffect(() => {
    const handleOnline = () => {
      setIsOnline(true);
      document.body.classList.remove('offline');
    };
    
    const handleOffline = () => {
      setIsOnline(false);
      document.body.classList.add('offline');
    };

    window.addEventListener('online', handleOnline);
    window.addEventListener('offline', handleOffline);

    return () => {
      window.removeEventListener('online', handleOnline);
      window.removeEventListener('offline', handleOffline);
    };
  }, []);

  // Handle PWA install prompt
  useEffect(() => {
    const handleBeforeInstallPrompt = (e: Event) => {
      e.preventDefault();
      // Store the event for later use
      (window as any).deferredPrompt = e;
    };

    window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);

    return () => {
      window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
    };
  }, []);

  // Report web vitals for performance monitoring
  useEffect(() => {
    import('web-vitals').then(({ getCLS, getFID, getFCP, getLCP, getTTFB }) => {
      getCLS(reportWebVitals);
      getFID(reportWebVitals);
      getFCP(reportWebVitals);
      getLCP(reportWebVitals);
      getTTFB(reportWebVitals);
    });
  }, []);

  return (
    <ErrorBoundary>
      <I18nextProvider i18n={i18n}>
        <Router>
          <div className="App min-h-screen bg-gray-50">
            {/* PWA Status Indicators */}
            {!isOnline && (
              <div className="fixed top-0 left-0 right-0 bg-yellow-500 text-white text-center py-2 z-50">
                📵 Çevrimdışı mod - Önbellek verilerini kullanıyoruz
              </div>
            )}

            <Layout user={getUserData()}>
              <Suspense fallback={<EnhancedLoadingSpinner />}>
                <Routes>
                  {/* Dashboard Routes */}
                  <Route 
                    path="/" 
                    element={
                      <ProtectedRoute>
                        <Navigate to="/dashboard" replace />
                      </ProtectedRoute>
                    } 
                  />
                  
                  <Route 
                    path="/dashboard" 
                    element={
                      <ProtectedRoute>
                        {getDashboardComponent()}
                      </ProtectedRoute>
                    } 
                  />

                  {/* Super Admin Panel Route */}
                  <Route 
                    path="/super-admin" 
                    element={
                      <ProtectedRoute allowedRoles={['super_admin']}>
                        <SuperAdminPanel />
                      </ProtectedRoute>
                    } 
                  />

                  {/* Marketplaces Route */}
                  <Route 
                    path="/marketplaces" 
                    element={
                      <ProtectedRoute requiredPermission="marketplace_manage">
                        <CrossMarketplaceDashboard />
                      </ProtectedRoute>
                    } 
                  />
                  
                  <Route 
                    path="/cross-marketplace" 
                    element={
                      <ProtectedRoute requiredPermission="marketplace_manage">
                        <CrossMarketplaceDashboard />
                      </ProtectedRoute>
                    } 
                  />

                  {/* Trendyol Routes */}
                  <Route 
                    path="/trendyol-dashboard" 
                    element={
                      <ProtectedRoute requiredPermission="marketplace_manage">
                        <TrendyolDashboard />
                      </ProtectedRoute>
                    } 
                  />
                  
                  <Route 
                    path="/trendyol-integration" 
                    element={
                      <ProtectedRoute requiredPermission="marketplace_manage">
                        <TrendyolIntegration />
                      </ProtectedRoute>
                    } 
                  />
                  
                  <Route 
                    path="/trendyol-test" 
                    element={
                      <ProtectedRoute>
                        <TrendyolTestPage />
                      </ProtectedRoute>
                    } 
                  />
                  
                  <Route 
                    path="/trendyol-api-info" 
                    element={
                      <ProtectedRoute>
                        <TrendyolApiInfo />
                      </ProtectedRoute>
                    } 
                  />

                  {/* Analytics Routes */}
                  <Route 
                    path="/analytics" 
                    element={
                      <ProtectedRoute requiredPermission="analytics_view">
                        <AdvancedAnalytics />
                      </ProtectedRoute>
                    } 
                  />
                  
                  <Route 
                    path="/advanced-analytics" 
                    element={
                      <ProtectedRoute requiredPermission="analytics_view">
                        <AdvancedAnalytics />
                      </ProtectedRoute>
                    } 
                  />

                  {/* AI Analytics Dashboard Route */}
                  <Route 
                    path="/ai-analytics" 
                    element={
                      <ProtectedRoute requiredPermission="analytics_view">
                        <AdvancedAIAnalyticsDashboard />
                      </ProtectedRoute>
                    } 
                  />

                  {/* Customer Behavior AI Route */}
                  <Route 
                    path="/customer-behavior-ai" 
                    element={
                      <ProtectedRoute requiredPermission="analytics_view">
                        <CustomerBehaviorAI />
                      </ProtectedRoute>
                    } 
                  />

                  {/* Advanced Automation Center Route */}
                  <Route 
                    path="/advanced-automation" 
                    element={
                      <ProtectedRoute requiredPermission="automation_manage">
                        <AdvancedAutomationCenter />
                      </ProtectedRoute>
                    } 
                  />

                  {/* Enterprise Intelligence Dashboard Route */}
                  <Route 
                    path="/enterprise-intelligence" 
                    element={
                      <ProtectedRoute allowedRoles={['super_admin', 'admin']} requiredPermission="enterprise_manage">
                        <EnterpriseIntelligenceDashboard />
                      </ProtectedRoute>
                    } 
                  />

                  {/* Global Intelligence Dashboard Route */}
                  <Route 
                    path="/global-intelligence" 
                    element={
                      <ProtectedRoute allowedRoles={['super_admin', 'admin']} requiredPermission="global_manage">
                        <GlobalIntelligenceDashboard />
                      </ProtectedRoute>
                    } 
                  />

                  {/* Quantum Intelligence Dashboard Route */}
                  <Route 
                    path="/quantum-intelligence" 
                    element={
                      <ProtectedRoute allowedRoles={['super_admin', 'admin']} requiredPermission="quantum_manage">
                        <QuantumIntelligenceDashboard />
                      </ProtectedRoute>
                    } 
                  />
                  
                  <Route 
                    path="/advanced-reports" 
                    element={
                      <ProtectedRoute requiredPermission="reports_view">
                        <AdvancedReportsPage />
                      </ProtectedRoute>
                    } 
                  />

                  {/* Advanced Features Routes */}
                  <Route 
                    path="/advanced-performance" 
                    element={
                      <ProtectedRoute requiredPermission="analytics_view">
                        <AdvancedPerformanceDashboard />
                      </ProtectedRoute>
                    } 
                  />

                  <Route 
                    path="/smart-notifications" 
                    element={
                      <ProtectedRoute>
                        <SmartNotificationCenter />
                      </ProtectedRoute>
                    } 
                  />

                  <Route 
                    path="/data-visualization" 
                    element={
                      <ProtectedRoute requiredPermission="analytics_view">
                        <AdvancedDataVisualization />
                      </ProtectedRoute>
                    } 
                  />

                  {/* Configuration Route */}
                  <Route 
                    path="/configuration" 
                    element={
                      <ProtectedRoute allowedRoles={['super_admin', 'admin']}>
                        <AdvancedConfigurationInterface />
                      </ProtectedRoute>
                    } 
                  />

                  {/* Users Route (Super Admin only) */}
                  <Route 
                    path="/users" 
                    element={
                      <ProtectedRoute 
                        requiredPermission="user_manage"
                        allowedRoles={['super_admin']}
                      >
                        <UsersPage />
                      </ProtectedRoute>
                    } 
                  />

                  {/* Hepsiburada Integration Route */}
                  <Route 
                    path="/marketplace/hepsiburada" 
                    element={
                      <ProtectedRoute requiredPermission="marketplace_manage">
                        <HepsiburadaIntegration />
                      </ProtectedRoute>
                    } 
                  />

                  {/* ÇiçekSepeti Integration Route */}
                  <Route 
                    path="/marketplace/ciceksepeti" 
                    element={
                      <ProtectedRoute requiredPermission="marketplace_manage">
                        <CicekSepetiIntegration />
                      </ProtectedRoute>
                    } 
                  />

                  {/* Dropshipping Route */}
                  <Route 
                    path="/dropshipping" 
                    element={
                      <ProtectedRoute requiredPermission="dropshipping_manage">
                        <DropshipperDashboard />
                      </ProtectedRoute>
                    } 
                  />

                  {/* N11 Integration Route */}
                  <Route 
                    path="/marketplace/n11" 
                    element={
                      <ProtectedRoute requiredPermission="marketplace_manage">
                        <N11Integration />
                      </ProtectedRoute>
                    } 
                  />

                  {/* Amazon Integration Route */}
                  <Route 
                    path="/marketplace/amazon" 
                    element={
                      <ProtectedRoute requiredPermission="marketplace_manage">
                        <AmazonIntegration />
                      </ProtectedRoute>
                    } 
                  />

                  {/* eBay Integration Route */}
                  <Route 
                    path="/marketplace/ebay" 
                    element={
                      <ProtectedRoute requiredPermission="marketplace_manage">
                        <EbayIntegration />
                      </ProtectedRoute>
                    } 
                  />

                  {/* Etsy Integration Route */}
                  <Route 
                    path="/marketplace/etsy" 
                    element={
                      <ProtectedRoute requiredPermission="marketplace_manage">
                        <div className="text-center py-12">
                          <h2 className="text-2xl font-bold text-gray-900 mb-4">Etsy Entegrasyonu</h2>
                          <p className="text-gray-600 mb-4">Etsy marketplace entegrasyonu yakında kullanıma sunulacak.</p>
                          <div className="text-6xl mb-4">🟠</div>
                          <p className="text-sm text-gray-500">Artisan API geliştirme devam ediyor...</p>
                        </div>
                      </ProtectedRoute>
                    } 
                  />

                  {/* Automation Center Route */}
                  <Route 
                    path="/automation" 
                    element={
                      <ProtectedRoute requiredPermission="automation_manage">
                        <AutomationCenter />
                      </ProtectedRoute>
                    } 
                  />

                  {/* Communication Center Route */}
                  <Route 
                    path="/communication" 
                    element={
                      <ProtectedRoute requiredPermission="communication_manage">
                        <CommunicationCenter />
                      </ProtectedRoute>
                    } 
                  />

                  {/* Trendyol Real Time Panel Route */}
                  <Route 
                    path="/trendyol-real-time-panel" 
                    element={
                      <ProtectedRoute requiredPermission="marketplace_manage">
                        <TrendyolRealTimePanel />
                      </ProtectedRoute>
                    } 
                  />

                  {/* Production Readiness Dashboard Route */}
                  <Route 
                    path="/production-readiness" 
                    element={
                      <ProtectedRoute requiredPermission="marketplace_manage">
                        <ProductionReadinessDashboard />
                      </ProtectedRoute>
                    } 
                  />

                  {/* Real Time Monitoring Dashboard Route */}
                  <Route 
                    path="/real-time-monitoring" 
                    element={
                      <ProtectedRoute requiredPermission="marketplace_manage">
                        <RealTimeMonitoringDashboard />
                      </ProtectedRoute>
                    } 
                  />

                  {/* Universal Intelligence Dashboard Route */}
                  <Route 
                    path="/universal-intelligence" 
                    element={
                      <ProtectedRoute allowedRoles={['super_admin', 'admin']} requiredPermission="global_manage">
                        <UniversalIntelligenceDashboard />
                      </ProtectedRoute>
                    } 
                  />

                  {/* Transcendental Intelligence Dashboard Route */}
                  <Route 
                    path="/transcendental-intelligence" 
                    element={
                      <ProtectedRoute allowedRoles={['super_admin']} requiredPermission="divine_manage">
                        <TranscendentalIntelligenceDashboard />
                      </ProtectedRoute>
                    } 
                  />

                  {/* Absolute Source Intelligence Dashboard Route */}
              <Route 
                    path="/absolute-source-intelligence" 
                    element={
                      <ProtectedRoute allowedRoles={['super_admin']} requiredPermission="source_manage">
                        <AbsoluteSourceIntelligenceDashboard />
                      </ProtectedRoute>
                    } 
                  />

                  {/* Fallback Route */}
            <Route 
              path="*" 
              element={
                      <div className="flex items-center justify-center min-h-screen">
                  <div className="text-center">
                          <h1 className="text-4xl font-bold text-gray-800 mb-4">404</h1>
                          <p className="text-gray-600 mb-4">Sayfa bulunamadı</p>
                    <button
                            onClick={() => window.history.back()}
                            className="meschain-button meschain-button-primary"
                    >
                            Geri Dön
                    </button>
                  </div>
                </div>
              } 
            />
          </Routes>
              </Suspense>
        </Layout>

            {/* PWA Prompt Component */}
            <PWAPrompt />
      </div>
    </Router>
      </I18nextProvider>
    </ErrorBoundary>
  );
};

export default App; 