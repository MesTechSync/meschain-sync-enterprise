import React, { useState } from 'react';

interface APIEndpoint {
  method: 'GET' | 'POST' | 'PUT' | 'DELETE';
  endpoint: string;
  description: string;
  parameters?: string[];
  example?: string;
}

const TrendyolAPIInfo: React.FC = () => {
  const [activeTab, setActiveTab] = useState<'overview' | 'endpoints' | 'auth' | 'examples'>('overview');

  const apiEndpoints: APIEndpoint[] = [
    {
      method: 'GET',
      endpoint: '/api/v1/products',
      description: 'Tüm ürünleri listeler',
      parameters: ['page', 'size', 'approved'],
      example: 'GET /api/v1/products?page=0&size=50&approved=true'
    },
    {
      method: 'POST',
      endpoint: '/api/v1/products',
      description: 'Yeni ürün ekler',
      parameters: ['productMainId', 'attributes', 'images'],
      example: 'POST /api/v1/products'
    },
    {
      method: 'PUT',
      endpoint: '/api/v1/products/price-and-inventory',
      description: 'Ürün fiyat ve stok günceller',
      parameters: ['items'],
      example: 'PUT /api/v1/products/price-and-inventory'
    },
    {
      method: 'GET',
      endpoint: '/api/v1/orders',
      description: 'Sipariş listesini getirir',
      parameters: ['startDate', 'endDate', 'page', 'size'],
      example: 'GET /api/v1/orders?startDate=2024-01-01&endDate=2024-12-31'
    },
    {
      method: 'POST',
      endpoint: '/api/v1/orders/{id}/status',
      description: 'Sipariş durumunu günceller',
      parameters: ['status', 'trackingNumber'],
      example: 'POST /api/v1/orders/12345/status'
    }
  ];

  const getMethodColor = (method: string) => {
    switch (method) {
      case 'GET':
        return 'bg-green-100 text-green-800';
      case 'POST':
        return 'bg-blue-100 text-blue-800';
      case 'PUT':
        return 'bg-yellow-100 text-yellow-800';
      case 'DELETE':
        return 'bg-red-100 text-red-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const renderOverview = () => (
    <div className="space-y-6">
      <div className="bg-white rounded-lg shadow p-6">
        <h3 className="text-lg font-medium text-gray-900 mb-4">
          🚀 Trendyol API'ye Başlangıç
        </h3>
        <div className="prose text-gray-600">
          <p className="mb-4">
            Trendyol Satıcı API'si, mağazanızı programmatik olarak yönetmenizi sağlar. 
            Ürün listeleme, sipariş yönetimi, stok güncelleme ve daha fazlası için API kullanabilirsiniz.
          </p>
          
          <h4 className="text-md font-semibold text-gray-900 mb-2">Ana Özellikler:</h4>
          <ul className="list-disc list-inside space-y-1 mb-4">
            <li>Ürün listeleme ve güncelleme</li>
            <li>Sipariş yönetimi</li>
            <li>Stok ve fiyat senkronizasyonu</li>
            <li>Kargo takip entegrasyonu</li>
            <li>Raporlama ve analitik</li>
          </ul>
          
          <h4 className="text-md font-semibold text-gray-900 mb-2">Gereksinimler:</h4>
          <ul className="list-disc list-inside space-y-1">
            <li>Aktif Trendyol Satıcı hesabı</li>
            <li>API erişim izni</li>
            <li>Supplier ID, API Key ve Secret</li>
            <li>HTTPS destekli uygulama</li>
          </ul>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div className="bg-blue-50 border border-blue-200 rounded-lg p-6">
          <div className="flex items-center mb-4">
            <div className="text-2xl mr-3">📚</div>
            <h4 className="text-lg font-semibold text-blue-900">Dokümantasyon</h4>
          </div>
          <p className="text-blue-700 mb-4">
            Resmi Trendyol API dokümantasyonu ve örnekleri
          </p>
          <a 
            href="https://developers.trendyol.com" 
            target="_blank" 
            rel="noopener noreferrer"
            className="text-blue-600 hover:text-blue-800 underline"
          >
            developers.trendyol.com →
          </a>
        </div>

        <div className="bg-green-50 border border-green-200 rounded-lg p-6">
          <div className="flex items-center mb-4">
            <div className="text-2xl mr-3">🛠️</div>
            <h4 className="text-lg font-semibold text-green-900">Test Ortamı</h4>
          </div>
          <p className="text-green-700 mb-4">
            API entegrasyonunuzu test edin ve geliştirin
          </p>
          <button className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md transition-colors">
            Test Sayfasına Git
          </button>
        </div>
      </div>
    </div>
  );

  const renderEndpoints = () => (
    <div className="space-y-4">
      {apiEndpoints.map((endpoint, index) => (
        <div key={index} className="bg-white rounded-lg shadow p-6">
          <div className="flex items-start justify-between mb-3">
            <div className="flex items-center space-x-3">
              <span className={`px-2 py-1 rounded text-xs font-medium ${getMethodColor(endpoint.method)}`}>
                {endpoint.method}
              </span>
              <code className="text-sm font-mono bg-gray-100 px-2 py-1 rounded">
                {endpoint.endpoint}
              </code>
            </div>
          </div>
          
          <p className="text-gray-600 mb-4">{endpoint.description}</p>
          
          {endpoint.parameters && (
            <div className="mb-4">
              <h5 className="text-sm font-semibold text-gray-900 mb-2">Parametreler:</h5>
              <div className="flex flex-wrap gap-2">
                {endpoint.parameters.map((param, paramIndex) => (
                  <span key={paramIndex} className="bg-gray-100 text-gray-700 px-2 py-1 rounded text-xs">
                    {param}
                  </span>
                ))}
              </div>
            </div>
          )}
          
          {endpoint.example && (
            <div>
              <h5 className="text-sm font-semibold text-gray-900 mb-2">Örnek:</h5>
              <code className="block bg-gray-50 p-3 rounded text-sm font-mono text-gray-800">
                {endpoint.example}
              </code>
            </div>
          )}
        </div>
      ))}
    </div>
  );

  const renderAuth = () => (
    <div className="space-y-6">
      <div className="bg-white rounded-lg shadow p-6">
        <h3 className="text-lg font-medium text-gray-900 mb-4">
          🔐 Kimlik Doğrulama
        </h3>
        
        <div className="space-y-4">
          <div>
            <h4 className="font-semibold text-gray-900 mb-2">1. API Bilgilerinizi Alın</h4>
            <p className="text-gray-600 mb-3">
              Trendyol Satıcı Paneli {'>'} Entegrasyonlar {'>'} API Bilgileri bölümünden:
            </p>
            <ul className="list-disc list-inside text-gray-600 space-y-1">
              <li><strong>Supplier ID:</strong> Satıcı kimlik numaranız</li>
              <li><strong>API Key:</strong> Genel erişim anahtarı</li>
              <li><strong>API Secret:</strong> Gizli anahtar</li>
            </ul>
          </div>

          <div>
            <h4 className="font-semibold text-gray-900 mb-2">2. Base64 Kodlama</h4>
            <p className="text-gray-600 mb-3">
              API Key ve Secret'ı şu formatta Base64 ile kodlayın:
            </p>
            <code className="block bg-gray-50 p-3 rounded text-sm font-mono">
              base64(apiKey:apiSecret)
            </code>
          </div>

          <div>
            <h4 className="font-semibold text-gray-900 mb-2">3. Request Header</h4>
            <p className="text-gray-600 mb-3">
              Her API isteğinde şu header'ı ekleyin:
            </p>
            <code className="block bg-gray-50 p-3 rounded text-sm font-mono">
              Authorization: Basic {'{base64EncodedCredentials}'}
            </code>
          </div>
        </div>
      </div>

      <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
        <div className="flex items-start">
          <div className="text-yellow-600 text-xl mr-3">⚠️</div>
          <div>
            <h4 className="text-yellow-800 font-semibold mb-2">Güvenlik Uyarısı</h4>
            <ul className="text-yellow-700 space-y-1 text-sm">
              <li>• API bilgilerinizi asla paylaşmayın</li>
              <li>• HTTPS kullanmadan API'ye istek yapmayın</li>
              <li>• Client-side JavaScript'te API Secret saklamayın</li>
              <li>• Rate limiting kurallarına uyun (dakikada 100 istek)</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  );

  const renderExamples = () => (
    <div className="space-y-6">
      <div className="bg-white rounded-lg shadow p-6">
        <h3 className="text-lg font-medium text-gray-900 mb-4">
          💡 Kod Örnekleri
        </h3>
        
        <div className="space-y-6">
          <div>
            <h4 className="font-semibold text-gray-900 mb-2">JavaScript (Fetch API)</h4>
            <code className="block bg-gray-900 text-gray-100 p-4 rounded text-sm font-mono overflow-x-auto">
{`const apiKey = 'your-api-key';
const apiSecret = 'your-api-secret';
const credentials = btoa(\`\${apiKey}:\${apiSecret}\`);

fetch('https://api.trendyol.com/sapigw/suppliers/12345/products', {
  method: 'GET',
  headers: {
    'Authorization': \`Basic \${credentials}\`,
    'Content-Type': 'application/json'
  }
})
.then(response => response.json())
.then(data => console.log(data));`}
            </code>
          </div>

          <div>
            <h4 className="font-semibold text-gray-900 mb-2">PHP (cURL)</h4>
            <code className="block bg-gray-900 text-gray-100 p-4 rounded text-sm font-mono overflow-x-auto">
{`<?php
$apiKey = 'your-api-key';
$apiSecret = 'your-api-secret';
$credentials = base64_encode("$apiKey:$apiSecret");

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.trendyol.com/sapigw/suppliers/12345/products');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Basic ' . $credentials,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
print_r($data);
?>`}
            </code>
          </div>

          <div>
            <h4 className="font-semibold text-gray-900 mb-2">Python (Requests)</h4>
            <code className="block bg-gray-900 text-gray-100 p-4 rounded text-sm font-mono overflow-x-auto">
{`import requests
import base64

api_key = 'your-api-key'
api_secret = 'your-api-secret'
credentials = base64.b64encode(f"{api_key}:{api_secret}".encode()).decode()

headers = {
    'Authorization': f'Basic {credentials}',
    'Content-Type': 'application/json'
}

response = requests.get(
    'https://api.trendyol.com/sapigw/suppliers/12345/products',
    headers=headers
)

data = response.json()
print(data)`}
            </code>
          </div>
        </div>
      </div>
    </div>
  );

  const renderContent = () => {
    switch (activeTab) {
      case 'overview':
        return renderOverview();
      case 'endpoints':
        return renderEndpoints();
      case 'auth':
        return renderAuth();
      case 'examples':
        return renderExamples();
      default:
        return renderOverview();
    }
  };

  return (
    <div className="space-y-6">
      <div className="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
        <h1 className="text-3xl font-bold mb-2">📖 Trendyol API Rehberi</h1>
        <p className="text-orange-100">Trendyol API entegrasyonu için kapsamlı rehber</p>
      </div>

      {/* Tabs */}
      <div className="bg-white rounded-lg shadow">
        <div className="border-b border-gray-200">
          <nav className="-mb-px flex space-x-8 px-6">
            {[
              { id: 'overview', label: '🏠 Genel Bakış' },
              { id: 'endpoints', label: '🔗 API Endpoints' },
              { id: 'auth', label: '🔐 Kimlik Doğrulama' },
              { id: 'examples', label: '💡 Kod Örnekleri' }
            ].map((tab) => (
              <button
                key={tab.id}
                onClick={() => setActiveTab(tab.id as any)}
                className={`py-4 px-1 border-b-2 font-medium text-sm transition-colors ${
                  activeTab === tab.id
                    ? 'border-orange-500 text-orange-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                }`}
              >
                {tab.label}
              </button>
            ))}
          </nav>
        </div>
        
        <div className="p-6">
          {renderContent()}
        </div>
      </div>
    </div>
  );
};

export default TrendyolAPIInfo; 