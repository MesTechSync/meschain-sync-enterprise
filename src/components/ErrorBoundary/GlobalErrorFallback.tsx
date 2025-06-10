import React from 'react';
import { Box, Button, Container, Typography, Paper } from '@mui/material';
import { ErrorOutline, Refresh } from '@mui/icons-material';

interface Props {
  error: Error;
  resetErrorBoundary: () => void;
}

export const GlobalErrorFallback: React.FC<Props> = ({ error, resetErrorBoundary }) => {
  React.useEffect(() => {
    console.error('🚨 Global Error Boundary triggered:', error);
  }, [error]);

  return (
    <Container maxWidth="md" sx={{ py: 8 }}>
      <Paper 
        elevation={3} 
        sx={{ 
          p: 4, 
          textAlign: 'center',
          background: 'linear-gradient(135deg, #fee2e2 0%, #fecaca 100%)',
          border: '1px solid #fca5a5'
        }}
      >
        <Box sx={{ mb: 3 }}>
          <ErrorOutline sx={{ fontSize: 64, color: 'error.main', mb: 2 }} />
          <Typography variant="h4" component="h1" gutterBottom color="error">
            Beklenmeyen Bir Hata Oluştu
          </Typography>
          <Typography variant="body1" color="text.secondary" sx={{ mb: 3 }}>
            MesChain-Sync Enterprise uygulamasında bir sorun yaşandı. Lütfen sayfayı yenileyin veya destek ile iletişime geçin.
          </Typography>
        </Box>

        {/* Error Details */}
        <Paper 
          sx={{ 
            p: 2, 
            mb: 3, 
            backgroundColor: '#fef2f2',
            border: '1px solid #fecaca',
            textAlign: 'left',
            fontFamily: 'monospace',
            fontSize: '0.875rem',
            color: '#991b1b',
            maxHeight: '200px',
            overflow: 'auto'
          }}
        >
          <Typography variant="body2" component="pre" sx={{ whiteSpace: 'pre-wrap' }}>
            {error.message}
          </Typography>
          {error.stack && (
            <Typography variant="caption" component="pre" sx={{ mt: 1, opacity: 0.7 }}>
              {error.stack}
            </Typography>
          )}
        </Paper>

        {/* Action Buttons */}
        <Box sx={{ display: 'flex', gap: 2, justifyContent: 'center', flexWrap: 'wrap' }}>
          <Button
            variant="contained"
            color="primary"
            startIcon={<Refresh />}
            onClick={resetErrorBoundary}
            size="large"
          >
            Uygulamayı Yenile
          </Button>
          
          <Button
            variant="outlined"
            color="secondary"
            onClick={() => window.location.href = '/'}
            size="large"
          >
            Ana Sayfaya Dön
          </Button>
        </Box>

        {/* Additional Info */}
        <Box sx={{ mt: 4, p: 2, backgroundColor: '#f8fafc', borderRadius: 1 }}>
          <Typography variant="body2" color="text.secondary">
            <strong>Hata Zamanı:</strong> {new Date().toLocaleString('tr-TR')}
          </Typography>
          <Typography variant="body2" color="text.secondary">
            <strong>Kullanıcı Aracısı:</strong> {navigator.userAgent}
          </Typography>
          <Typography variant="body2" color="text.secondary">
            <strong>URL:</strong> {window.location.href}
          </Typography>
        </Box>

        {/* Support Info */}
        <Typography variant="caption" color="text.secondary" sx={{ mt: 2, display: 'block' }}>
          Sorun devam ederse, lütfen bu hata bilgilerini ekleyerek destek ekibi ile iletişime geçin.
        </Typography>
      </Paper>
    </Container>
  );
}; 