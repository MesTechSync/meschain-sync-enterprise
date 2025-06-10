import React, { Suspense } from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import { ThemeProvider } from '@mui/material/styles';
import { CssBaseline, CircularProgress, Box } from '@mui/material';
import { ErrorBoundary } from 'react-error-boundary';

import { theme } from './theme/theme';
import { GlobalErrorFallback } from './components/ErrorBoundary/GlobalErrorFallback';
import AppLayout from './components/Layout/AppLayout';
import MainDashboard from './components/Dashboard/MainDashboard';

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

// Placeholder components for other pages
const InventoryPage = () => <div>Stok Yönetimi Sayfası</div>;
const ReportsPage = () => <div>Raporlar Sayfası</div>;
const SettingsPage = () => <div>Ayarlar Sayfası</div>;
const SupportPage = () => <div>Destek Sayfası</div>;

const LoadingFallback = () => (
  <Box display="flex" justifyContent="center" alignItems="center" minHeight="60vh">
    <CircularProgress />
  </Box>
);

function App() {
  return (
    <ErrorBoundary FallbackComponent={GlobalErrorFallback}>
      <ThemeProvider theme={theme}>
        <CssBaseline />
        <Router>
          <AppLayout>
            <Suspense fallback={<LoadingFallback />}>
              <Routes>
                <Route path="/" element={<MainDashboard />} />
                <Route path="/dashboard" element={<MainDashboard />} />
                
                {/* Marketplace Routes */}
                <Route path="/marketplace/trendyol" element={<TrendyolPage />} />
                <Route path="/marketplace/n11" element={<N11Page />} />
                <Route path="/marketplace/amazon" element={<AmazonPage />} />
                <Route path="/marketplace/hepsiburada" element={<HepsiburadaPage />} />
                <Route path="/marketplace/ebay" element={<EbayPage />} />
                <Route path="/marketplace/ozon" element={<OzonPage />} />
                
                {/* Other Routes */}
                <Route path="/products" element={<ProductsPage />} />
                <Route path="/orders" element={<OrdersPage />} />
                <Route path="/inventory" element={<InventoryPage />} />
                <Route path="/reports" element={<ReportsPage />} />
                <Route path="/settings" element={<SettingsPage />} />
                <Route path="/support" element={<SupportPage />} />
              </Routes>
            </Suspense>
          </AppLayout>
        </Router>
      </ThemeProvider>
    </ErrorBoundary>
  );
}

export default App; 