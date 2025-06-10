import React, { Suspense } from 'react';
import { Routes, Route } from 'react-router-dom';
import { ThemeProvider } from '@mui/material/styles';
import { CssBaseline, CircularProgress, Box } from '@mui/material';
import { ErrorBoundary } from 'react-error-boundary';
import { Toaster } from 'react-hot-toast';
import { I18nextProvider } from 'react-i18next';

import { theme } from './theme/theme';
import { GlobalErrorFallback } from './components/ErrorBoundary/GlobalErrorFallback';
import AppLayout from './components/Layout/AppLayout';
import MainDashboard from './components/Dashboard/MainDashboard';
import TrendyolModule from './modules/TrendyolModule';
import N11Module from './modules/N11Module';
import AmazonModule from './modules/AmazonModule';
import HepsiburadaModule from './modules/HepsiburadaModule';
import EbayModule from './modules/EbayModule';
import OzonModule from './modules/OzonModule';
import ProductModule from './modules/ProductModule';
import OrderModule from './modules/OrderModule';
import InventoryModule from './modules/InventoryModule';
import ReportModule from './modules/ReportModule';
import SettingsModule from './modules/SettingsModule';
import i18n from './i18n';
// AI/ML Integration - Priority 2
import AdvancedAIIntegration from './ai/AdvancedAIIntegration';
// Security Framework - Priority 3
import AdvancedSecurityFramework from './security/AdvancedSecurityFramework';

// Marketplace Pages
import TrendyolPage from './components/Marketplace/TrendyolPage';
import N11Page from './components/Marketplace/N11Page';
import AmazonPage from './components/Marketplace/AmazonPage';
import HepsiburadaPage from './components/Marketplace/HepsiburadaPage';
import EbayPage from './components/Marketplace/EbayPage';
import OzonPage from './components/Marketplace/OzonPage';

// Other Pages
import OrdersPage from './components/Orders/OrdersPage';
import ProductsPage from './components/Products/ProductsPage';
import ReportsPage from './components/Reports/ReportsPage';
import InventoryPage from './components/Inventory/InventoryPage';
import SettingsPage from './components/Settings/SettingsPage';

// Placeholder components for other pages
const SupportPage = () => <div>Destek Sayfası</div>;

const LoadingFallback = () => (
  <Box display="flex" justifyContent="center" alignItems="center" minHeight="60vh">
    <CircularProgress />
  </Box>
);

const App: React.FC = () => {
  return (
    <ErrorBoundary FallbackComponent={GlobalErrorFallback}>
      <ThemeProvider theme={theme}>
        <CssBaseline />
        <I18nextProvider i18n={i18n}>
          <div className="App">
            <Routes>
              <Route path="/" element={<AppLayout />}>
                <Route index element={<MainDashboard />} />
                <Route path="dashboard" element={<MainDashboard />} />
                
                {/* AI/ML Integration Routes - Priority 2 Enhancement */}
                <Route path="ai" element={<AdvancedAIIntegration />} />
                <Route path="ai/integration" element={<AdvancedAIIntegration />} />
                <Route path="ai/ml-pipeline" element={<AdvancedAIIntegration />} />
                
                {/* Security Framework Routes - Priority 3 Enhancement */}
                <Route path="security" element={<AdvancedSecurityFramework />} />
                <Route path="security/framework" element={<AdvancedSecurityFramework />} />
                <Route path="security/threats" element={<AdvancedSecurityFramework />} />
                
                {/* Marketplace Routes */}
                <Route path="marketplace/trendyol" element={<TrendyolModule />} />
                <Route path="marketplace/n11" element={<N11Module />} />
                <Route path="marketplace/amazon" element={<AmazonModule />} />
                <Route path="marketplace/hepsiburada" element={<HepsiburadaModule />} />
                <Route path="marketplace/ebay" element={<EbayModule />} />
                <Route path="marketplace/ozon" element={<OzonModule />} />
                
                {/* Module Routes */}
                <Route path="products" element={<ProductModule />} />
                <Route path="orders" element={<OrderModule />} />
                <Route path="inventory" element={<InventoryModule />} />
                <Route path="reports" element={<ReportModule />} />
                <Route path="settings" element={<SettingsModule />} />
              </Route>
            </Routes>
            <Toaster position="top-right" />
          </div>
        </I18nextProvider>
      </ThemeProvider>
    </ErrorBoundary>
  );
};

export default App; 