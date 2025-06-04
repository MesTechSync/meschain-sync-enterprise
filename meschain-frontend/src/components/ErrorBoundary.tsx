import React, { Component, ErrorInfo, ReactNode } from 'react';

interface Props {
  children: ReactNode;
}

interface State {
  hasError: boolean;
  error?: Error;
  errorInfo?: ErrorInfo;
}

class ErrorBoundary extends Component<Props, State> {
  constructor(props: Props) {
    super(props);
    this.state = { hasError: false };
  }

  static getDerivedStateFromError(error: Error): State {
    return { hasError: true, error };
  }

  componentDidCatch(error: Error, errorInfo: ErrorInfo) {
    console.error('ErrorBoundary caught an error:', error, errorInfo);
    
    this.setState({
      error,
      errorInfo
    });

    // Report error to monitoring service in production
    if (process.env.NODE_ENV === 'production') {
      // Example: reportError(error, errorInfo);
    }
  }

  render() {
    if (this.state.hasError) {
      return (
        <div className="min-h-screen flex items-center justify-center bg-gray-50">
          <div className="max-w-md w-full bg-white rounded-lg shadow-lg p-6 text-center">
            <div className="text-6xl mb-4">🚨</div>
            <h1 className="text-2xl font-bold text-gray-900 mb-4">
              Bir Hata Oluştu
            </h1>
            <p className="text-gray-600 mb-6">
              Üzgünüz, beklenmeyen bir hata oluştu. Lütfen sayfayı yenileyin veya daha sonra tekrar deneyin.
            </p>
            
            {process.env.NODE_ENV === 'development' && this.state.error && (
              <details className="text-left mb-4 p-4 bg-gray-100 rounded">
                <summary className="cursor-pointer font-medium text-red-600 mb-2">
                  Hata Detayları (Geliştirici Modu)
                </summary>
                <pre className="text-xs text-red-800 overflow-auto">
                  {this.state.error.toString()}
                  {this.state.errorInfo?.componentStack}
                </pre>
              </details>
            )}
            
            <div className="space-y-3">
              <button
                onClick={() => window.location.reload()}
                className="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors"
              >
                🔄 Sayfayı Yenile
              </button>
              
              <button
                onClick={() => window.history.back()}
                className="w-full bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-md transition-colors"
              >
                ← Geri Dön
              </button>
              
              <button
                onClick={() => window.location.href = '/dashboard'}
                className="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md transition-colors"
              >
                🏠 Ana Sayfaya Git
              </button>
            </div>
            
            <div className="mt-6 text-sm text-gray-500">
              <p>Sorun devam ederse lütfen teknik destek ile iletişime geçin.</p>
              <p className="mt-1">
                📧 support@meschain.com | 📞 +90 (212) 123-4567
              </p>
            </div>
          </div>
        </div>
      );
    }

    return this.props.children;
  }
}

export default ErrorBoundary; 