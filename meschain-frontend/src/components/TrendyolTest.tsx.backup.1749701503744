import React, { useState } from 'react';

interface TestResult {
  test: string;
  status: 'success' | 'error' | 'pending';
  message: string;
  timestamp?: string;
}

const TrendyolTest: React.FC = () => {
  const [isRunning, setIsRunning] = useState(false);
  const [testResults, setTestResults] = useState<TestResult[]>([]);
  const [apiCredentials, setApiCredentials] = useState({
    supplierId: '',
    apiKey: '',
    apiSecret: ''
  });

  const runTests = async () => {
    setIsRunning(true);
    setTestResults([]);

    const tests = [
      'API Bağlantı Testi',
      'Kimlik Doğrulama',
      'Ürün Listesi Getirme',
      'Sipariş Listesi Getirme',
      'Stok Senkronizasyonu',
      'Fiyat Güncelleme'
    ];

    for (let i = 0; i < tests.length; i++) {
      const test = tests[i];
      
      // Add pending test
      setTestResults(prev => [...prev, {
        test,
        status: 'pending',
        message: 'Test çalışıyor...'
      }]);

      // Simulate test execution
      await new Promise(resolve => setTimeout(resolve, 1000 + Math.random() * 2000));

      // Random success/failure for demo
      const success = Math.random() > 0.2; // 80% success rate

      setTestResults(prev => 
        prev.map((result, index) => 
          index === i ? {
            test,
            status: success ? 'success' : 'error',
            message: success ? 'Test başarılı' : 'Test başarısız - Lütfen ayarları kontrol edin',
            timestamp: new Date().toLocaleTimeString('tr-TR')
          } : result
        )
      );
    }

    setIsRunning(false);
  };

  const getStatusIcon = (status: TestResult['status']) => {
    switch (status) {
      case 'success':
        return '✅';
      case 'error':
        return '❌';
      case 'pending':
        return '⏳';
      default:
        return '⚪';
    }
  };

  const getStatusColor = (status: TestResult['status']) => {
    switch (status) {
      case 'success':
        return 'text-green-600 bg-green-50 border-green-200';
      case 'error':
        return 'text-red-600 bg-red-50 border-red-200';
      case 'pending':
        return 'text-yellow-600 bg-yellow-50 border-yellow-200';
      default:
        return 'text-gray-600 bg-gray-50 border-gray-200';
    }
  };

  return (
    <div className="space-y-6">
      <div className="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
        <h1 className="text-3xl font-bold mb-2">🧪 Trendyol API Test</h1>
        <p className="text-orange-100">API bağlantınızı ve entegrasyonunuzu test edin</p>
      </div>

      {/* API Credentials Form */}
      <div className="bg-white rounded-lg shadow p-6">
        <h3 className="text-lg font-medium text-gray-900 mb-4">
          API Kimlik Bilgileri
        </h3>
        
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              Supplier ID
            </label>
            <input
              type="text"
              value={apiCredentials.supplierId}
              onChange={(e) => setApiCredentials({
                ...apiCredentials,
                supplierId: e.target.value
              })}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
              placeholder="12345"
            />
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              API Key
            </label>
            <input
              type="text"
              value={apiCredentials.apiKey}
              onChange={(e) => setApiCredentials({
                ...apiCredentials,
                apiKey: e.target.value
              })}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
              placeholder="your-api-key"
            />
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              API Secret
            </label>
            <input
              type="password"
              value={apiCredentials.apiSecret}
              onChange={(e) => setApiCredentials({
                ...apiCredentials,
                apiSecret: e.target.value
              })}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
              placeholder="your-api-secret"
            />
          </div>
        </div>

        <div className="mt-6">
          <button
            onClick={runTests}
            disabled={isRunning || !apiCredentials.supplierId || !apiCredentials.apiKey || !apiCredentials.apiSecret}
            className={`w-full md:w-auto px-6 py-3 rounded-md font-medium transition-colors ${
              isRunning || !apiCredentials.supplierId || !apiCredentials.apiKey || !apiCredentials.apiSecret
                ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                : 'bg-orange-500 hover:bg-orange-600 text-white'
            }`}
          >
            {isRunning ? '🔄 Testler Çalışıyor...' : '🚀 Testleri Başlat'}
          </button>
        </div>
      </div>

      {/* Test Results */}
      {testResults.length > 0 && (
        <div className="bg-white rounded-lg shadow p-6">
          <h3 className="text-lg font-medium text-gray-900 mb-4">
            Test Sonuçları
          </h3>
          
          <div className="space-y-3">
            {testResults.map((result, index) => (
              <div
                key={index}
                className={`p-4 rounded-lg border ${getStatusColor(result.status)}`}
              >
                <div className="flex items-center justify-between">
                  <div className="flex items-center space-x-3">
                    <span className="text-2xl">{getStatusIcon(result.status)}</span>
                    <div>
                      <h4 className="font-medium">{result.test}</h4>
                      <p className="text-sm opacity-75">{result.message}</p>
                    </div>
                  </div>
                  {result.timestamp && (
                    <span className="text-xs opacity-50">
                      {result.timestamp}
                    </span>
                  )}
                </div>
              </div>
            ))}
          </div>

          {/* Summary */}
          {!isRunning && testResults.length === 6 && (
            <div className="mt-6 p-4 bg-gray-50 rounded-lg">
              <div className="flex items-center justify-between">
                <div>
                  <h4 className="font-medium text-gray-900">Test Özeti</h4>
                  <p className="text-sm text-gray-600">
                    {testResults.filter(r => r.status === 'success').length} başarılı, {' '}
                    {testResults.filter(r => r.status === 'error').length} başarısız
                  </p>
                </div>
                <div className={`px-3 py-1 rounded-full text-sm font-medium ${
                  testResults.every(r => r.status === 'success')
                    ? 'bg-green-100 text-green-800'
                    : testResults.some(r => r.status === 'success')
                    ? 'bg-yellow-100 text-yellow-800'
                    : 'bg-red-100 text-red-800'
                }`}>
                  {testResults.every(r => r.status === 'success')
                    ? 'Tüm Testler Başarılı'
                    : testResults.some(r => r.status === 'success')
                    ? 'Kısmi Başarı'
                    : 'Testler Başarısız'
                  }
                </div>
              </div>
            </div>
          )}
        </div>
      )}

      {/* Quick Actions */}
      <div className="bg-white rounded-lg shadow p-6">
        <h3 className="text-lg font-medium text-gray-900 mb-4">
          Hızlı İşlemler
        </h3>
        
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <button className="p-4 text-left border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <div className="text-2xl mb-2">📋</div>
            <h4 className="font-medium text-gray-900">API Dokümantasyonu</h4>
            <p className="text-sm text-gray-600">Trendyol API rehberini inceleyin</p>
          </button>
          
          <button className="p-4 text-left border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <div className="text-2xl mb-2">🔧</div>
            <h4 className="font-medium text-gray-900">Ayarlar</h4>
            <p className="text-sm text-gray-600">API ayarlarını düzenleyin</p>
          </button>
          
          <button className="p-4 text-left border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <div className="text-2xl mb-2">📊</div>
            <h4 className="font-medium text-gray-900">Dashboard</h4>
            <p className="text-sm text-gray-600">Ana panele git</p>
          </button>
          
          <button className="p-4 text-left border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
            <div className="text-2xl mb-2">🆘</div>
            <h4 className="font-medium text-gray-900">Destek</h4>
            <p className="text-sm text-gray-600">Teknik destek alın</p>
          </button>
        </div>
      </div>
    </div>
  );
};

export default TrendyolTest; 