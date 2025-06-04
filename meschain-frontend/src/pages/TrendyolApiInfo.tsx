import React from 'react';

const TrendyolApiInfo: React.FC = () => {
  const apiEndpoints = [
    {
      category: "📦 Ürün Yönetimi",
      endpoints: [
        { name: "Ürün Listesi", endpoint: "/suppliers/{supplierId}/products", description: "Tüm ürünlerinizi listeler" },
        { name: "Ürün Detayı", endpoint: "/suppliers/{supplierId}/products/{productId}", description: "Belirli bir ürünün detaylarını getirir" },
        { name: "Ürün Ekleme", endpoint: "POST /suppliers/{supplierId}/products", description: "Yeni ürün ekler" },
        { name: "Ürün Güncelleme", endpoint: "PUT /suppliers/{supplierId}/products", description: "Mevcut ürünü günceller" },
        { name: "Stok Güncelleme", endpoint: "POST /suppliers/{supplierId}/products/price-and-inventory", description: "Fiyat ve stok günceller" },
        { name: "Ürün Görselleri", endpoint: "/suppliers/{supplierId}/products/{productId}/images", description: "Ürün görsellerini yönetir" }
      ]
    },
    {
      category: "🛒 Sipariş Yönetimi", 
      endpoints: [
        { name: "Sipariş Listesi", endpoint: "/suppliers/{supplierId}/orders", description: "Tüm siparişlerinizi listeler" },
        { name: "Sipariş Detayı", endpoint: "/suppliers/{supplierId}/orders/{orderNumber}", description: "Belirli siparişin detaylarını getirir" },
        { name: "Kargo Bilgisi", endpoint: "POST /suppliers/{supplierId}/orders/shipment-packages", description: "Kargo takip bilgisi gönderir" },
        { name: "Sipariş Durumu", endpoint: "PUT /suppliers/{supplierId}/orders/{orderNumber}", description: "Sipariş durumunu günceller" },
        { name: "İade İşlemleri", endpoint: "/suppliers/{supplierId}/claims", description: "İade ve şikayet işlemleri" }
      ]
    },
    {
      category: "📊 Raporlama",
      endpoints: [
        { name: "Satış Raporu", endpoint: "/suppliers/{supplierId}/finance/settlements", description: "Ödeme ve komisyon raporları" },
        { name: "Performans", endpoint: "/suppliers/{supplierId}/products/batch-requests", description: "Ürün performans metrikleri" },
        { name: "Komisyon", endpoint: "/suppliers/{supplierId}/commissions", description: "Komisyon oranları ve hesaplamalar" },
        { name: "Fatura Bilgileri", endpoint: "/suppliers/{supplierId}/invoices", description: "Fatura ve ödeme bilgileri" }
      ]
    },
    {
      category: "🔗 Webhook & Entegrasyon",
      endpoints: [
        { name: "Webhook Kayıt", endpoint: "POST /webhooks", description: "Webhook URL'lerini kaydeder" },
        { name: "Webhook Listesi", endpoint: "/webhooks", description: "Aktif webhook'ları listeler" },
        { name: "API Durumu", endpoint: "/suppliers/{supplierId}/addresses", description: "API bağlantı durumunu kontrol eder" },
        { name: "Kategori Listesi", endpoint: "/product-categories", description: "Trendyol kategori ağacını getirir" }
      ]
    }
  ];

  const integrationSteps = [
    {
      step: 1,
      title: "Trendyol Partner Paneli",
      description: "partner.trendyol.com'dan API bilgilerinizi alın",
      details: ["API Key", "Secret Key", "Supplier ID"]
    },
    {
      step: 2, 
      title: "OpenCart Admin Paneli",
      description: "API bilgilerini OpenCart'a girin",
      details: ["Extensions > Modules > Trendyol", "API ayarlarını yapılandırın", "Bağlantıyı test edin"]
    },
    {
      step: 3,
      title: "Senkronizasyon",
      description: "Ürün ve sipariş senkronizasyonunu başlatın",
      details: ["Ürünleri Trendyol'a gönder", "Siparişleri otomatik al", "Stok senkronizasyonu"]
    }
  ];

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg p-6">
        <h1 className="text-3xl font-bold mb-2">🚀 Trendyol API Entegrasyonu</h1>
        <p className="text-orange-100">OpenCart ile Trendyol arasında tam entegrasyon rehberi</p>
      </div>

      {/* Secret Key Alma Rehberi */}
      <div className="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-lg shadow-md p-6 mb-6 border border-yellow-200">
        <h3 className="text-lg font-semibold text-yellow-800 mb-4 flex items-center">
          🔑 Secret Key Nasıl Alınır?
        </h3>
        
        <div className="space-y-4">
          <div className="bg-white rounded-lg p-4 border border-yellow-100">
            <div className="flex items-start">
              <span className="bg-yellow-100 text-yellow-800 rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3 mt-0.5">1</span>
              <div>
                <div className="font-medium text-gray-800">Trendyol Partner Panel'e Giriş</div>
                <div className="text-sm text-gray-600 mt-1">
                  <a href="https://partner.trendyol.com" target="_blank" rel="noopener noreferrer" 
                     className="text-blue-600 hover:underline">partner.trendyol.com</a> adresinden giriş yapın
                </div>
              </div>
            </div>
          </div>
          
          <div className="bg-white rounded-lg p-4 border border-yellow-100">
            <div className="flex items-start">
              <span className="bg-yellow-100 text-yellow-800 rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3 mt-0.5">2</span>
              <div>
                <div className="font-medium text-gray-800">Entegrasyon Menüsü</div>
                <div className="text-sm text-gray-600 mt-1">
                  Sol menüden "Entegrasyon" → "API Entegrasyonu" bölümüne gidin
                </div>
              </div>
            </div>
          </div>
          
          <div className="bg-white rounded-lg p-4 border border-yellow-100">
            <div className="flex items-start">
              <span className="bg-yellow-100 text-yellow-800 rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3 mt-0.5">3</span>
              <div>
                <div className="font-medium text-gray-800">API Bilgilerini Kopyalayın</div>
                <div className="text-sm text-gray-600 mt-1">
                  API Key, Secret Key ve Supplier ID bilgilerini kopyalayın
                </div>
              </div>
            </div>
          </div>
          
          <div className="bg-white rounded-lg p-4 border border-yellow-100">
            <div className="flex items-start">
              <span className="bg-yellow-100 text-yellow-800 rounded-full w-6 h-6 flex items-center justify-center text-sm font-bold mr-3 mt-0.5">4</span>
              <div>
                <div className="font-medium text-gray-800">MesChain-Sync'e Ekleyin</div>
                <div className="text-sm text-gray-600 mt-1">
                  <a href="/trendyol-test" className="text-blue-600 hover:underline">Test sayfasında</a> 
                  API bilgilerinizi girin ve testleri çalıştırın
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div className="mt-4 p-3 bg-yellow-100 rounded-lg">
          <div className="flex items-center">
            <span className="text-yellow-700 mr-2">⚠️</span>
            <span className="text-sm text-yellow-800">
              <strong>Güvenlik:</strong> API bilgilerinizi kimseyle paylaşmayın ve güvenli bir yerde saklayın.
            </span>
          </div>
        </div>
      </div>

      {/* Integration Steps */}
      <div className="bg-white rounded-lg shadow-md p-6">
        <h2 className="text-2xl font-bold text-gray-900 mb-4">📋 Entegrasyon Adımları</h2>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {integrationSteps.map((step) => (
            <div key={step.step} className="border rounded-lg p-4 hover:shadow-lg transition-shadow">
              <div className="flex items-center mb-3">
                <div className="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold mr-3">
                  {step.step}
                </div>
                <h3 className="font-semibold text-gray-900">{step.title}</h3>
              </div>
              <p className="text-gray-600 mb-3">{step.description}</p>
              <ul className="space-y-1">
                {step.details.map((detail, index) => (
                  <li key={index} className="text-sm text-gray-500 flex items-center">
                    <span className="mr-2">•</span>
                    {detail}
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </div>
      </div>

      {/* API Endpoints */}
      <div className="space-y-6">
        <h2 className="text-2xl font-bold text-gray-900">🔗 Trendyol API Özellikleri</h2>
        
        {apiEndpoints.map((category, categoryIndex) => (
          <div key={categoryIndex} className="bg-white rounded-lg shadow-md p-6">
            <h3 className="text-xl font-semibold text-gray-900 mb-4">{category.category}</h3>
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
              {category.endpoints.map((endpoint, endpointIndex) => (
                <div key={endpointIndex} className="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
                  <div className="flex items-start justify-between mb-2">
                    <h4 className="font-medium text-gray-900">{endpoint.name}</h4>
                    <span className="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">API</span>
                  </div>
                  <code className="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded block mb-2">
                    {endpoint.endpoint}
                  </code>
                  <p className="text-sm text-gray-600">{endpoint.description}</p>
                </div>
              ))}
            </div>
          </div>
        ))}
      </div>

      {/* Configuration Guide */}
      <div className="bg-white rounded-lg shadow-md p-6">
        <h2 className="text-2xl font-bold text-gray-900 mb-4">⚙️ Yapılandırma Rehberi</h2>
        
        <div className="space-y-6">
          <div className="border-l-4 border-blue-500 pl-4">
            <h3 className="font-semibold text-gray-900 mb-2">🔑 API Bilgileri Nerede?</h3>
            <div className="space-y-2 text-sm text-gray-600">
              <p><strong>OpenCart Admin:</strong> <code>http://yourdomain.com/admin/index.php?route=extension/module/trendyol</code></p>
              <p><strong>React Panel:</strong> <code>http://localhost:3000/trendyol-test</code> (Test için)</p>
            </div>
          </div>

          <div className="border-l-4 border-green-500 pl-4">
            <h3 className="font-semibold text-gray-900 mb-2">📊 Çekebileceğimiz Veriler</h3>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
              <div>
                <strong>Ürünler:</strong>
                <ul className="text-gray-600 mt-1">
                  <li>• Ürün listesi</li>
                  <li>• Stok durumu</li>
                  <li>• Fiyat bilgileri</li>
                  <li>• Kategoriler</li>
                </ul>
              </div>
              <div>
                <strong>Siparişler:</strong>
                <ul className="text-gray-600 mt-1">
                  <li>• Yeni siparişler</li>
                  <li>• Sipariş durumu</li>
                  <li>• Kargo bilgileri</li>
                  <li>• Müşteri bilgileri</li>
                </ul>
              </div>
              <div>
                <strong>Raporlar:</strong>
                <ul className="text-gray-600 mt-1">
                  <li>• Satış raporları</li>
                  <li>• Komisyon bilgileri</li>
                  <li>• Ödeme raporları</li>
                  <li>• Performans</li>
                </ul>
              </div>
              <div>
                <strong>Sistem:</strong>
                <ul className="text-gray-600 mt-1">
                  <li>• API durumu</li>
                  <li>• Webhook'lar</li>
                  <li>• Rate limit</li>
                  <li>• Hata logları</li>
                </ul>
              </div>
            </div>
          </div>

          <div className="border-l-4 border-orange-500 pl-4">
            <h3 className="font-semibold text-gray-900 mb-2">🔄 Otomatik Senkronizasyon</h3>
            <div className="text-sm text-gray-600 space-y-1">
              <p>• <strong>Ürün Senkronizasyonu:</strong> OpenCart ürünlerinizi Trendyol'a otomatik gönderir</p>
              <p>• <strong>Stok Güncelleme:</strong> Stok değişikliklerini gerçek zamanlı senkronize eder</p>
              <p>• <strong>Sipariş İmport:</strong> Trendyol siparişlerini OpenCart'a otomatik aktarır</p>
              <p>• <strong>Fiyat Güncelleme:</strong> Fiyat değişikliklerini otomatik günceller</p>
            </div>
          </div>
        </div>
      </div>

      {/* Quick Start */}
      <div className="bg-gradient-to-r from-green-500 to-blue-500 text-white rounded-lg p-6">
        <h2 className="text-2xl font-bold mb-4">🚀 Hızlı Başlangıç</h2>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h3 className="font-semibold mb-2">1. API Bilgilerini Girin</h3>
            <p className="text-green-100 text-sm">Trendyol Partner Panel'den aldığınız API Key, Secret Key ve Supplier ID'yi OpenCart admin paneline girin.</p>
          </div>
          <div>
            <h3 className="font-semibold mb-2">2. Bağlantıyı Test Edin</h3>
            <p className="text-green-100 text-sm">"Test Connection" butonuna tıklayarak API bağlantısının çalıştığını doğrulayın.</p>
          </div>
          <div>
            <h3 className="font-semibold mb-2">3. Ürünleri Senkronize Edin</h3>
            <p className="text-green-100 text-sm">OpenCart ürünlerinizi Trendyol'a göndermek için senkronizasyon işlemini başlatın.</p>
          </div>
          <div>
            <h3 className="font-semibold mb-2">4. Siparişleri İzleyin</h3>
            <p className="text-green-100 text-sm">Trendyol'dan gelen siparişler otomatik olarak OpenCart'a aktarılacak.</p>
          </div>
        </div>
      </div>
    </div>
  );
};

export default TrendyolApiInfo; 