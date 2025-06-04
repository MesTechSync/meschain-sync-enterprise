import React, { useState, useEffect } from 'react';

interface TestResult {
  success: boolean;
  message: string;
  responseTime?: number;
  data?: any;
  error?: string;
}

interface TestConfig {
  apiKey: string;
  secretKey: string;
  supplierId: string;
  sandboxMode: boolean;
}

interface TestStatus {
  overall: 'idle' | 'testing' | 'success' | 'error';
  connection: 'idle' | 'testing' | 'success' | 'error';
  health: 'idle' | 'testing' | 'success' | 'error';
  metrics: 'idle' | 'testing' | 'success' | 'error';
  orders: 'idle' | 'testing' | 'success' | 'error';
  webhook: 'idle' | 'testing' | 'success' | 'error';
}

const TrendyolTestPage: React.FC = () => {
  const [config, setConfig] = useState<TestConfig>({
    apiKey: '',
    secretKey: '',
    supplierId: '',
    sandboxMode: true
  });

  const [testStatus, setTestStatus] = useState<TestStatus>({
    overall: 'idle',
    connection: 'idle',
    health: 'idle',
    metrics: 'idle',
    orders: 'idle',
    webhook: 'idle'
  });

  const [testResults, setTestResults] = useState<{
    connection?: TestResult;
    health?: TestResult;
    metrics?: TestResult;
    orders?: TestResult;
    webhook?: TestResult;
  }>({});

  const [isConfigValid, setIsConfigValid] = useState(false);

  useEffect(() => {
    const valid = config.apiKey.length > 0 && config.secretKey.length > 0 && config.supplierId.length > 0;
    setIsConfigValid(valid);
  }, [config]);

  const simulateApiCall = async (testType: string, duration: number = 1000): Promise<TestResult> => {
    try {
      // Gerçek API çağrısı yap
      const response = await fetch(`http://localhost:8080/test_api.php?action=${testType}`, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      const startTime = Date.now();
      const data = await response.json();
      const responseTime = Date.now() - startTime;

      if (response.ok && data.success) {
        return {
          success: true,
          message: data.message || `${testType} testi başarılı`,
          responseTime,
          data: data.data
        };
      } else {
        return {
          success: false,
          message: data.message || `${testType} testi başarısız`,
          responseTime,
          error: data.error || 'API hatası'
        };
      }
    } catch (error) {
      return {
        success: false,
        message: `${testType} testi başarısız`,
        responseTime: 0,
        error: error instanceof Error ? error.message : 'Bağlantı hatası'
      };
    }
  };

  const runSingleTest = async (testType: keyof TestStatus) => {
    if (!isConfigValid) {
      alert('Lütfen önce API yapılandırmasını tamamlayın');
      return;
    }

    setTestStatus(prev => ({ ...prev, [testType]: 'testing' }));
    
    try {
      // Test type'ı API action'a çevir
      const apiActionMap = {
        connection: 'test-connection',
        health: 'test-connection', // Health check için de connection test kullan
        metrics: 'sales-data',
        orders: 'orders-count',
        webhook: 'webhook-status'
      };
      
      const apiAction = apiActionMap[testType as keyof typeof apiActionMap] || testType;
      const result = await simulateApiCall(apiAction);
      
      setTestResults(prev => ({ ...prev, [testType]: result }));
      setTestStatus(prev => ({ 
        ...prev, 
        [testType]: result.success ? 'success' : 'error' 
      }));
    } catch (error) {
      setTestResults(prev => ({ 
        ...prev, 
        [testType]: { 
          success: false, 
          message: 'Test sırasında hata oluştu',
          error: error instanceof Error ? error.message : 'Bilinmeyen hata'
        } 
      }));
      setTestStatus(prev => ({ ...prev, [testType]: 'error' }));
    }
  };

  const runAllTests = async () => {
    if (!isConfigValid) {
      alert('Lütfen önce API yapılandırmasını tamamlayın');
      return;
    }

    setTestStatus({
      overall: 'testing',
      connection: 'idle',
      health: 'idle',
      metrics: 'idle',
      orders: 'idle',
      webhook: 'idle'
    });

    setTestResults({});

    const tests: (keyof TestStatus)[] = ['connection', 'health', 'metrics', 'orders', 'webhook'];
    
    for (const test of tests) {
      if (test === 'overall') continue;
      await runSingleTest(test);
      await new Promise(resolve => setTimeout(resolve, 500)); // Small delay between tests
    }

    // Determine overall status
    const results = Object.values(testResults);
    const allSuccess = results.every(result => result?.success);
    const anySuccess = results.some(result => result?.success);
    
    setTestStatus(prev => ({
      ...prev,
      overall: allSuccess ? 'success' : anySuccess ? 'error' : 'error'
    }));
  };

  const resetTests = () => {
    setTestStatus({
      overall: 'idle',
      connection: 'idle',
      health: 'idle',
      metrics: 'idle',
      orders: 'idle',
      webhook: 'idle'
    });
    setTestResults({});
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'testing':
        return <span className="animate-spin text-blue-500">⏳</span>;
      case 'success':
        return <span className="text-green-500">✅</span>;
      case 'error':
        return <span className="text-red-500">❌</span>;
      default:
        return <span className="text-gray-400">⏸️</span>;
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'testing':
        return 'border-blue-200 bg-blue-50';
      case 'success':
        return 'border-green-200 bg-green-50';
      case 'error':
        return 'border-red-200 bg-red-50';
      default:
        return 'border-gray-200 bg-white';
    }
  };

  const TestCard: React.FC<{
    title: string;
    description: string;
    status: string;
    result?: TestResult;
    onTest: () => void;
    icon: string;
  }> = ({ title, description, status, result, onTest, icon }) => (
    <div className={`border rounded-lg p-6 ${getStatusColor(status)}`}>
      <div className="flex items-center justify-between mb-4">
        <div className="flex items-center space-x-3">
          <span className="text-2xl">{icon}</span>
          <div>
            <h3 className="text-lg font-semibold text-gray-900">{title}</h3>
            <p className="text-sm text-gray-600">{description}</p>
          </div>
        </div>
        <div className="flex items-center space-x-2">
          {getStatusIcon(status)}
          <button
            onClick={onTest}
            disabled={status === 'testing' || !isConfigValid}
            className="bg-blue-500 hover:bg-blue-600 disabled:bg-gray-300 text-white px-3 py-1 rounded text-sm"
          >
            Test
          </button>
        </div>
      </div>
      
      {result && (
        <div className="mt-4 p-3 bg-white rounded border">
          <div className="flex items-center justify-between mb-2">
            <span className={`font-medium ${result.success ? 'text-green-600' : 'text-red-600'}`}>
              {result.message}
            </span>
            {result.responseTime && (
              <span className="text-sm text-gray-500">{result.responseTime}ms</span>
            )}
          </div>
          
          {result.error && (
            <div className="text-sm text-red-600 mb-2">
              <span className="mr-1">⚠️</span>
              {result.error}
            </div>
          )}
          
          {result.data && (
            <details className="mt-2">
              <summary className="text-sm text-gray-600 cursor-pointer">Detayları göster</summary>
              <pre className="mt-2 text-xs bg-gray-100 p-2 rounded overflow-auto">
                {JSON.stringify(result.data, null, 2)}
              </pre>
            </details>
          )}
        </div>
      )}
    </div>
  );

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Trendyol API Test</h1>
          <p className="text-gray-600 mt-1">API bağlantısını ve fonksiyonlarını test edin</p>
        </div>
        <div className="flex space-x-2">
          <button
            onClick={resetTests}
            className="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2"
          >
            <span>🔄</span>
            <span>Sıfırla</span>
          </button>
          <button
            onClick={runAllTests}
            disabled={testStatus.overall === 'testing' || !isConfigValid}
            className="bg-green-500 hover:bg-green-600 disabled:bg-gray-300 text-white px-4 py-2 rounded-lg flex items-center space-x-2"
          >
            <span>▶️</span>
            <span>Tüm Testleri Çalıştır</span>
          </button>
        </div>
      </div>

      {/* Configuration Panel */}
      <div className="bg-white rounded-lg shadow-md p-6">
        <h3 className="text-lg font-semibold mb-4 flex items-center">
          ⚙️ API Yapılandırması
        </h3>
        
        {/* Secret Key Uyarısı */}
        {config.secretKey === '••••••••••••••••••••' && (
          <div className="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <div className="flex items-center">
              <span className="text-yellow-600 mr-2">⚠️</span>
              <div>
                <div className="font-medium text-yellow-800">Secret Key Gerekli</div>
                <div className="text-sm text-yellow-700 mt-1">
                  Gerçek API testleri için Trendyol Partner Panel'den aldığınız Secret Key'i girmeniz gerekiyor.
                  Şu anda demo verilerle çalışıyorsunuz.
                </div>
              </div>
            </div>
          </div>
        )}
        
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              API Key
            </label>
            <input
              type="text"
              value={config.apiKey}
              onChange={(e) => setConfig(prev => ({ ...prev, apiKey: e.target.value }))}
              placeholder="Trendyol API anahtarınızı girin"
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Secret Key
            </label>
            <input
              type="password"
              value={config.secretKey}
              onChange={(e) => setConfig(prev => ({ ...prev, secretKey: e.target.value }))}
              placeholder="Gizli anahtarınızı girin"
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Supplier ID
            </label>
            <input
              type="text"
              value={config.supplierId}
              onChange={(e) => setConfig(prev => ({ ...prev, supplierId: e.target.value }))}
              placeholder="Tedarikçi ID'nizi girin"
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          
          <div className="flex items-center">
            <input
              type="checkbox"
              id="sandboxMode"
              checked={config.sandboxMode}
              onChange={(e) => setConfig(prev => ({ ...prev, sandboxMode: e.target.checked }))}
              className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label htmlFor="sandboxMode" className="ml-2 block text-sm text-gray-700">
              Sandbox Modu (Test Ortamı)
            </label>
          </div>
        </div>
        
        <div className="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
          <div className="flex items-center">
            <span className="mr-2">ℹ️</span>
            <span className="text-sm text-blue-700">
              {isConfigValid 
                ? 'Yapılandırma tamamlandı. Testleri çalıştırabilirsiniz.' 
                : 'Lütfen tüm alanları doldurun.'}
            </span>
          </div>
        </div>
      </div>

      {/* Overall Status */}
      <div className={`bg-white rounded-lg shadow-md p-6 border-l-4 ${
        testStatus.overall === 'success' ? 'border-green-500' :
        testStatus.overall === 'error' ? 'border-red-500' :
        testStatus.overall === 'testing' ? 'border-blue-500' : 'border-gray-300'
      }`}>
        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-3">
            {getStatusIcon(testStatus.overall)}
            <div>
              <h3 className="text-lg font-semibold text-gray-900">Genel Test Durumu</h3>
              <p className="text-sm text-gray-600">
                {testStatus.overall === 'idle' && 'Testler henüz çalıştırılmadı'}
                {testStatus.overall === 'testing' && 'Testler çalışıyor...'}
                {testStatus.overall === 'success' && 'Tüm testler başarılı'}
                {testStatus.overall === 'error' && 'Bazı testler başarısız'}
              </p>
            </div>
          </div>
        </div>
      </div>

      {/* Test Cards */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <TestCard
          title="Bağlantı Testi"
          description="Temel API bağlantısını kontrol eder"
          status={testStatus.connection}
          result={testResults.connection}
          onTest={() => runSingleTest('connection')}
          icon="🔗"
        />
        
        <TestCard
          title="Sağlık Kontrolü"
          description="API sağlık durumunu kontrol eder"
          status={testStatus.health}
          result={testResults.health}
          onTest={() => runSingleTest('health')}
          icon="💚"
        />
        
        <TestCard
          title="Metrik Verileri"
          description="Dashboard verilerini çeker"
          status={testStatus.metrics}
          result={testResults.metrics}
          onTest={() => runSingleTest('metrics')}
          icon="📊"
        />
        
        <TestCard
          title="Son Siparişler"
          description="Sipariş listesini çeker"
          status={testStatus.orders}
          result={testResults.orders}
          onTest={() => runSingleTest('orders')}
          icon="🛒"
        />
        
        <TestCard
          title="Webhook Durumu"
          description="Webhook sistemini kontrol eder"
          status={testStatus.webhook}
          result={testResults.webhook}
          onTest={() => runSingleTest('webhook')}
          icon="🔗"
        />
      </div>

      {/* Help Section */}
      <div className="bg-white rounded-lg shadow-md p-6">
        <h2 className="text-lg font-semibold text-gray-900 mb-4">Yardım ve Bilgiler</h2>
        <div className="space-y-3 text-sm text-gray-600">
          <p>
            <strong>API Key:</strong> Trendyol Partner Panel'den alacağınız API anahtarı
          </p>
          <p>
            <strong>Secret Key:</strong> API anahtarınızla birlikte verilen gizli anahtar
          </p>
          <p>
            <strong>Supplier ID:</strong> Trendyol'daki mağaza/tedarikçi kimlik numaranız
          </p>
          <p>
            <strong>Sandbox Modu:</strong> Test ortamında çalışmak için aktif edin
          </p>
        </div>
      </div>
    </div>
  );
};

export default TrendyolTestPage; 