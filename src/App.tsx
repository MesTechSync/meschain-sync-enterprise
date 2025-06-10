import React from 'react';
import { Routes, Route, Navigate } from 'react-router-dom';
import { Box, Container, Typography, Paper, CircularProgress } from '@mui/material';
import { Helmet } from 'react-helmet-async';
import { Dashboard } from '@mui/icons-material';
import MainDashboard from './components/Dashboard/MainDashboard';
import AppLayout from './components/Layout/AppLayout';

// Simple Loading component
const LoadingSpinner: React.FC = () => (
  <Box 
    display="flex" 
    justifyContent="center" 
    alignItems="center" 
    minHeight="100vh"
    flexDirection="column"
    gap={2}
  >
    <CircularProgress size={50} />
    <Typography variant="h6" color="text.secondary">
      MesChain-Sync Enterprise Yükleniyor...
    </Typography>
  </Box>
);

// Simple Dashboard component
const DashboardPage: React.FC = () => {
  return (
    <Container maxWidth="xl" sx={{ py: 4 }}>
      <Helmet>
        <title>Dashboard - MesChain-Sync Enterprise</title>
      </Helmet>
      
      <Paper 
        elevation={3} 
        sx={{ 
          p: 4, 
          mb: 4, 
          background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
          color: 'white',
          textAlign: 'center'
        }}
      >
        <Dashboard sx={{ fontSize: 60, mb: 2 }} />
        <Typography variant="h3" component="h1" gutterBottom>
          🎉 MesChain-Sync Enterprise v4.5
        </Typography>
        <Typography variant="h5" sx={{ opacity: 0.9 }}>
          Frontend Geliştirme Başladı!
        </Typography>
      </Paper>

      <Box sx={{ display: 'grid', gridTemplateColumns: { xs: '1fr', md: '1fr 1fr', lg: '1fr 1fr 1fr' }, gap: 3 }}>
        <Paper sx={{ p: 3, textAlign: 'center' }}>
          <Typography variant="h6" gutterBottom color="primary">
            🚀 Backend Status
          </Typography>
          <Typography variant="body2" color="text.secondary">
            Port 3023 - Aktif
          </Typography>
          <Typography variant="body2" color="success.main">
            ✅ API Gateway Hazır
          </Typography>
        </Paper>

        <Paper sx={{ p: 3, textAlign: 'center' }}>
          <Typography variant="h6" gutterBottom color="primary">
            🎨 Frontend Status
          </Typography>
          <Typography variant="body2" color="text.secondary">
            React 18 + TypeScript
          </Typography>
          <Typography variant="body2" color="success.main">
            ✅ Development Mode
          </Typography>
        </Paper>

        <Paper sx={{ p: 3, textAlign: 'center' }}>
          <Typography variant="h6" gutterBottom color="primary">
            📊 Features
          </Typography>
          <Typography variant="body2" color="text.secondary">
            Material-UI + Charts
          </Typography>
          <Typography variant="body2" color="success.main">
            ✅ Modern UI Ready
          </Typography>
        </Paper>
      </Box>

      <Paper sx={{ p: 3, mt: 4 }}>
        <Typography variant="h6" gutterBottom>
          🛠️ Geliştirme Notları
        </Typography>
        <Typography variant="body2" color="text.secondary" component="div">
          <ul style={{ paddingLeft: '20px' }}>
            <li>Backend tamamen hazır (Port 3023 aktif)</li>
            <li>React 18 + TypeScript frontend kuruldu</li>
            <li>Material-UI theme sistemi aktif</li>
            <li>Error boundary ve global error handling</li>
            <li>Responsive design desteği</li>
            <li>Performance monitoring</li>
          </ul>
        </Typography>
      </Paper>
    </Container>
  );
};

const App: React.FC = () => {
  const [isLoading, setIsLoading] = React.useState(true);

  React.useEffect(() => {
    // Simulate app initialization
    const timer = setTimeout(() => {
      setIsLoading(false);
      console.log('🎯 MesChain-Sync Enterprise Frontend Ready!');
    }, 1500);

    return () => clearTimeout(timer);
  }, []);

  if (isLoading) {
    return <LoadingSpinner />;
  }

  return (
    <Box>
      <Helmet>
        <title>MesChain-Sync Enterprise v4.5</title>
        <meta name="description" content="Advanced Multi-Marketplace Integration Platform" />
      </Helmet>
      
      <AppLayout>
        <Routes>
          <Route path="/" element={<Navigate to="/dashboard" replace />} />
          <Route path="/dashboard" element={<MainDashboard />} />
          <Route path="/welcome" element={<DashboardPage />} />
          <Route path="*" element={<Navigate to="/dashboard" replace />} />
        </Routes>
      </AppLayout>
    </Box>
  );
};

export default App; 