import React, { Suspense } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { ReactQueryDevtools } from '@tanstack/react-query-devtools';
import { Toaster } from 'react-hot-toast';
import { useTranslation } from 'react-i18next';
import './i18n'; // Import i18n configuration

// Components
import Layout from './components/Layout';
import Dashboard from './components/Dashboard';
import DropshipperDashboard from './components/DropshipperDashboard';
import PWAPrompt from './components/PWAPrompt';
import { usePWA } from './hooks/usePWA';
import AdvancedReportsPage from './pages/AdvancedReportsPage';
import TrendyolTestPage from './pages/TrendyolTestPage';

// Pages
import MarketplacesPage from './pages/MarketplacesPage';
import DropshippingPage from './pages/DropshippingPage';
import OrdersPage from './pages/OrdersPage';
import InventoryPage from './pages/InventoryPage';
import SettingsPage from './pages/SettingsPage';

// Types
export type UserRole = 'super_admin' | 'admin' | 'dropshipper' | 'integrator' | 'support';

interface User {
  id: string;
  username: string;
  email: string;
  role: UserRole;
  firstName: string;
  lastName: string;
  avatar?: string;
}

// Create a client
const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
      refetchOnWindowFocus: false,
    },
  },
});

// Mock user data - In real app, this would come from authentication
const mockUser: User = {
  id: '1',
  username: 'admin',
  email: 'admin@meschain.com',
  role: 'super_admin',
  firstName: 'Admin',
  lastName: 'User',
  avatar: 'https://ui-avatars.com/api/?name=Admin+User&background=3b82f6&color=fff'
};

// Route protection component
interface ProtectedRouteProps {
  children: React.ReactNode;
  allowedRoles: UserRole[];
  userRole: UserRole;
}

const ProtectedRoute: React.FC<ProtectedRouteProps> = ({ children, allowedRoles, userRole }) => {
  if (!allowedRoles.includes(userRole)) {
    return <Navigate to="/dashboard" replace />;
  }
  return <>{children}</>;
};

// Loading component
const LoadingSpinner: React.FC = () => {
  const { t } = useTranslation();
  
  return (
    <div className="min-h-screen flex items-center justify-center">
      <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      <span className="ml-4 text-lg text-gray-600">{t('common.loading')}</span>
    </div>
  );
};

const App: React.FC = () => {
  const { t } = useTranslation();
  const { 
    isInstallable, 
    isOffline, 
    updateAvailable, 
    installApp, 
    updateApp,
    dismissInstall,
    dismissUpdate,
    promptInstall
  } = usePWA();

  const getDashboardComponent = (role: UserRole) => {
    switch (role) {
      case 'dropshipper':
        return <DropshipperDashboard />;
      default:
        return <Dashboard />;
    }
  };

  return (
    <QueryClientProvider client={queryClient}>
      <Router>
        <div className="App">
          <Suspense fallback={<LoadingSpinner />}>
            <Layout user={mockUser}>
              <Routes>
                <Route path="/" element={<Navigate to="/dashboard" replace />} />
                <Route path="/dashboard" element={<Dashboard />} />
                <Route path="/dropshipper" element={<DropshipperDashboard />} />
                <Route path="/marketplaces" element={<MarketplacesPage />} />
                <Route path="/dropshipping" element={<DropshippingPage />} />
                <Route path="/orders" element={<OrdersPage />} />
                <Route path="/inventory" element={<InventoryPage />} />
                <Route path="/analytics" element={<AdvancedReportsPage />} />
                <Route path="/reports" element={<AdvancedReportsPage />} />
                <Route path="/trendyol-test" element={<TrendyolTestPage />} />
                <Route path="/settings" element={<SettingsPage />} />
                <Route path="*" element={<Navigate to="/dashboard" replace />} />
              </Routes>
            </Layout>
          </Suspense>

          {/* PWA Components */}
          {isInstallable && <PWAPrompt onInstall={promptInstall} />}

          {/* Toast notifications */}
          <Toaster
            position="top-right"
            toastOptions={{
              duration: 4000,
              style: {
                background: '#363636',
                color: '#fff',
              },
              success: {
                duration: 3000,
                iconTheme: {
                  primary: '#10b981',
                  secondary: '#fff',
                },
              },
              error: {
                duration: 5000,
                iconTheme: {
                  primary: '#ef4444',
                  secondary: '#fff',
                },
              },
            }}
          />

          {/* React Query Devtools */}
          {process.env.NODE_ENV === 'development' && (
            <ReactQueryDevtools initialIsOpen={false} />
          )}
        </div>
      </Router>
    </QueryClientProvider>
  );
};

export default App; 