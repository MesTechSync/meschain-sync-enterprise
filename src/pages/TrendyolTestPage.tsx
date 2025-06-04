import React, { useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import {
  CheckCircleIcon,
  XCircleIcon,
  ClockIcon,
  ExclamationTriangleIcon,
  ArrowPathIcon,
  CogIcon,
  ChartBarIcon,
  ShoppingCartIcon,
  DocumentTextIcon
} from '@heroicons/react/24/outline';

interface ApiTestResult {
  name: string;
  endpoint: string;
  success: boolean;
  responseTime: number;
  message: string;
  data?: any;
}

interface TrendyolConfig {
  apiKey: string;
  secretKey: string;
  supplierId: string;
  sandboxMode: boolean;
}

const TrendyolTestPage: React.FC = () => {
  const { t } = useTranslation();
  const [testResults, setTestResults] = useState<ApiTestResult[]>([]);
  const [isRunningTests, setIsRunningTests] = useState(false);
  const [config, setConfig] = useState<TrendyolConfig>({
    apiKey: '',
    secretKey: '',
    supplierId: '',
    sandboxMode: true
  });
  const [overallStatus, setOverallStatus] = useState<'idle' | 'testing' | 'success' | 'error'>('idle');

  const apiTests = [
    {
      name: 'Bağlantı Testi',
      endpoint: 'test-connection',
      description: 'Temel API bağlantısını test eder'
    },
    {
      name: 'Sağlık Kontrolü',
      endpoint: 'health-check',
      description: 'API sağlık durumunu kontrol eder'
    },
    {
      name: 'Metrikler',
      endpoint: 'metrics',
      description: 'Dashboard metriklerini alır'
    },
    {
      name: 'Son Siparişler',
      endpoint: 'recent-orders',
      description: 'Son siparişleri listeler'
    },
    {
      name: 'Webhook Durumu',
      endpoint: 'webhook-status',
      description: 'Webhook sisteminin durumunu kontrol eder'
    }
  ];

  const runSingleTest = async (test: any): Promise<ApiTestResult> => {
    const startTime = Date.now();
    
    try {
      const response = await fetch(
        `/admin/index.php?route=extension/module/trendyol/api&action=${test.endpoint}&user_token=${(window as any).user_token || 'test'}`,
        {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
          }
        }
      );

      const responseTime = Date.now() - startTime;
      const data = await response.json();

      return {
        name: test.name,
        endpoint: test.endpoint,
        success: data.success || false,
        responseTime,
        message: data.message || (data.success ? 'Başarılı' : 'Başarısız'),
        data: data.data
      };
    } catch (error) {
      return {
        name: test.name,
        endpoint: test.endpoint,
        success: false,
        responseTime: Date.now() - startTime,
        message: 'Ağ hatası: ' + (error as Error).message
      };
    }
  };

  const runAllTests = async () => {
    setIsRunningTests(true);
    setOverallStatus('testing');
    setTestResults([]);

    const results: ApiTestResult[] = [];

    for (const test of apiTests) {
      const result = await runSingleTest(test);
      results.push(result);
      setTestResults([...results]);
      
      // Small delay between tests
      await new Promise(resolve => setTimeout(resolve, 500));
    }

    const successCount = results.filter(r => r.success).length;
    setOverallStatus(successCount === results.length ? 'success' : 'error');
    setIsRunningTests(false);
  };

  const getStatusIcon = (success: boolean) => {
    return success ? (
      <CheckCircleIcon className="w-5 h-5 text-green-500" />
    ) : (
      <XCircleIcon className="w-5 h-5 text-red-500" />
    );
  };

  const getOverallStatusColor = () => {
    switch (overallStatus) {
      case 'success': return 'text-green-600 bg-green-100';
      case 'error': return 'text-red-600 bg-red-100';
      case 'testing': return 'text-blue-600 bg-blue-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  const getOverallStatusText = () => {
    switch (overallStatus) {
      case 'success': return 'Tüm testler başarılı';
      case 'error': return 'Bazı testler başarısız';
      case 'testing': return 'Testler çalışıyor...';
      default: return 'Test başlatılmadı';
    }
  };

  return (
    <div className="px-6 py-8">
      <div className="mb-8">
        <h1 className="text-2xl font-bold text-gray-900 mb-2">Trendyol API Test Merkezi</h1>
        <p className="text-gray-600">
          Trendyol API entegrasyonunuzu test edin ve bağlantı durumunu kontrol edin.
        </p>
      </div>

      {/* Overall Status */}
      <div className="bg-white rounded-lg shadow p-6 mb-8">
        <div className="flex items-center justify-between mb-4">
          <h2 className="text-lg font-semibold text-gray-900">Test Durumu</h2>
          <div className={`px-3 py-1 rounded-full text-sm font-medium ${getOverallStatusColor()}`}>
            {getOverallStatusText()}
          </div>
        </div>

        <div className="flex items-center justify-between">
          <div className="flex items-center space-x-4">
            <div className="text-sm text-gray-600">
              Toplam Test: <span className="font-medium">{apiTests.length}</span>
            </div>
            <div className="text-sm text-gray-600">
              Başarılı: <span className="font-medium text-green-600">
                {testResults.filter(r => r.success).length}
              </span>
            </div>
            <div className="text-sm text-gray-600">
              Başarısız: <span className="font-medium text-red-600">
                {testResults.filter(r => !r.success).length}
              </span>
            </div>
          </div>

          <button
            onClick={runAllTests}
            disabled={isRunningTests}
            className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {isRunningTests ? (
              <>
                <ArrowPathIcon className="w-4 h-4 mr-2 animate-spin" />
                Testler Çalışıyor...
              </>
            ) : (
              <>
                <ArrowPathIcon className="w-4 h-4 mr-2" />
                Tüm Testleri Çalıştır
              </>
            )}
          </button>
        </div>
      </div>

      {/* Configuration Panel */}
      <div className="bg-white rounded-lg shadow p-6 mb-8">
        <div className="flex items-center mb-4">
          <CogIcon className="w-5 h-5 text-gray-500 mr-2" />
          <h2 className="text-lg font-semibold text-gray-900">API Konfigürasyonu</h2>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              API Anahtarı
            </label>
            <input
              type="password"
              value={config.apiKey}
              onChange={(e) => setConfig({...config, apiKey: e.target.value})}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Trendyol API anahtarınızı girin"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Gizli Anahtar
            </label>
            <input
              type="password"
              value={config.secretKey}
              onChange={(e) => setConfig({...config, secretKey: e.target.value})}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Gizli anahtarınızı girin"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Tedarikçi ID
            </label>
            <input
              type="text"
              value={config.supplierId}
              onChange={(e) => setConfig({...config, supplierId: e.target.value})}
              className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Tedarikçi ID'nizi girin"
            />
          </div>

          <div className="flex items-center">
            <input
              type="checkbox"
              id="sandboxMode"
              checked={config.sandboxMode}
              onChange={(e) => setConfig({...config, sandboxMode: e.target.checked})}
              className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            />
            <label htmlFor="sandboxMode" className="ml-2 block text-sm text-gray-700">
              Sandbox Modu (Test Ortamı)
            </label>
          </div>
        </div>
      </div>

      {/* Test Results */}
      <div className="bg-white rounded-lg shadow p-6">
        <div className="flex items-center mb-4">
          <DocumentTextIcon className="w-5 h-5 text-gray-500 mr-2" />
          <h2 className="text-lg font-semibold text-gray-900">Test Sonuçları</h2>
        </div>

        {testResults.length === 0 && !isRunningTests && (
          <div className="text-center py-8 text-gray-500">
            Henüz test çalıştırılmadı. Yukarıdaki butona tıklayarak testleri başlatın.
          </div>
        )}

        <div className="space-y-4">
          {apiTests.map((test, index) => {
            const result = testResults.find(r => r.endpoint === test.endpoint);
            const isCurrentTest = isRunningTests && index === testResults.length;
            
            return (
              <div key={test.endpoint} className="border border-gray-200 rounded-lg p-4">
                <div className="flex items-center justify-between">
                  <div className="flex items-center">
                    {result ? (
                      getStatusIcon(result.success)
                    ) : isCurrentTest ? (
                      <ClockIcon className="w-5 h-5 text-blue-500 animate-spin" />
                    ) : (
                      <div className="w-5 h-5 bg-gray-200 rounded-full"></div>
                    )}
                    <div className="ml-3">
                      <h3 className="text-sm font-medium text-gray-900">{test.name}</h3>
                      <p className="text-xs text-gray-500">{test.description}</p>
                    </div>
                  </div>

                  <div className="text-right">
                    {result && (
                      <>
                        <div className="text-sm text-gray-600">
                          {result.responseTime}ms
                        </div>
                        <div className={`text-xs ${result.success ? 'text-green-600' : 'text-red-600'}`}>
                          {result.message}
                        </div>
                      </>
                    )}
                    {isCurrentTest && (
                      <div className="text-sm text-blue-600">Test ediliyor...</div>
                    )}
                  </div>
                </div>

                {result && result.data && (
                  <div className="mt-3 p-3 bg-gray-50 rounded text-xs">
                    <pre className="whitespace-pre-wrap">
                      {JSON.stringify(result.data, null, 2)}
                    </pre>
                  </div>
                )}
              </div>
            );
          })}
        </div>
      </div>
    </div>
  );
};

export default TrendyolTestPage; 