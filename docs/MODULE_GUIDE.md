# 📘 MesChain-Sync: Modül Geliştirme Rehberi

Bu doküman, MesChain-Sync projesine yeni bir pazaryeri modülü ekleme veya mevcut modülleri geliştirme sürecini detaylandırmaktadır.

## 📋 İçindekiler

- [Başlamadan Önce](#başlamadan-önce)
- [Yeni Pazaryeri Modülü Ekleme](#yeni-pazaryeri-modülü-ekleme)
- [Modül Yapısı](#modül-yapısı)
- [API Entegrasyonu](#api-entegrasyonu)
- [Controller Yapısı](#controller-yapısı)
- [Model Yapısı](#model-yapısı)
- [View (Twig) Şablonları](#view-twig-şablonları)
- [Dil Dosyaları](#dil-dosyaları)
- [Loglama ve Hata Yönetimi](#loglama-ve-hata-yönetimi)
- [Test Etme](#test-etme)
- [Dokümantasyon](#dokümantasyon)

## 🚀 Başlamadan Önce

MesChain-Sync için modül geliştirmeye başlamadan önce, aşağıdaki gereksinimleri karşıladığınızdan emin olun:

1. **OpenCart MVC Mimarisi** hakkında temel bilgi
2. **PHP programlama** dilinde yeterlilik
3. **Twig şablon motoru** hakkında bilgi
4. **Entegre edilecek pazaryeri API** dokümantasyonuna erişim

## 🔧 Yeni Pazaryeri Modülü Ekleme

Yeni bir pazaryeri modülü eklemek için izlenmesi gereken adımlar:

### 1. Klasör Yapısı Oluşturma

```
upload/admin/controller/extension/mestech/[pazaryeri]/
├── README.md               # Modül açıklaması
├── TODO.md                 # Yapılacaklar listesi
└── controller.php          # Ana kontrolcü

upload/admin/language/[dil]/extension/mestech/[pazaryeri]/
└── [pazaryeri].php         # Dil dosyası

upload/admin/view/template/extension/mestech/[pazaryeri]/
├── dashboard.twig          # Kontrol paneli şablonu
├── settings.twig           # Ayarlar şablonu
├── products.twig           # Ürün yönetimi şablonu
└── orders.twig             # Sipariş yönetimi şablonu

upload/system/library/entegrator/
└── [pazaryeri].php         # API entegrasyon sınıfı
```

### 2. Ana Controller Dosyasına Kayıt

`upload/admin/controller/extension/mestech/mestech_sync.php` dosyasında:

```php
// Yeni pazaryeri menü girişi ekleme
$data['menus'][] = [
    'name' => $this->language->get('text_[pazaryeri]'),
    'href' => $this->url->link('extension/mestech/[pazaryeri]', 'user_token=' . $this->session->data['user_token'], true),
    'icon' => 'fa-shopping-cart'
];
```

## 📂 Modül Yapısı

Her pazaryeri modülü aşağıdaki bileşenleri içermelidir:

### 1. Controller Dosyası

Ana işlevleri yöneten controller dosyası şu metodları içermelidir:

- `index()` - Ana sayfa (dashboard)
- `settings()` - API ve genel ayarlar
- `products()` - Ürün yönetimi
- `orders()` - Sipariş yönetimi
- `sync()` - Senkronizasyon işlemleri
- `logs()` - Loglama ve hata kayıtları

### 2. API Entegrasyon Sınıfı

`upload/system/library/entegrator/[pazaryeri].php` dosyası, API ile iletişimi sağlayan metodları içermelidir:

- `connect()` - API bağlantısı kurma
- `getProducts()` - Ürünleri çekme
- `updateProduct()` - Ürün güncelleme
- `getOrders()` - Siparişleri çekme
- `updateOrderStatus()` - Sipariş durumu güncelleme
- `getCategories()` - Kategorileri çekme

### 3. View Şablonları

Her modül için en az aşağıdaki şablonlar oluşturulmalıdır:

- `dashboard.twig` - Genel bakış ve istatistikler
- `settings.twig` - API ve genel ayarlar
- `products.twig` - Ürün yönetimi
- `orders.twig` - Sipariş yönetimi

### 4. Dil Dosyaları

Dil dosyaları (`[pazaryeri].php`), aşağıdaki anahtar gruplarını içermelidir:

- Başlıklar (`heading_title`, `text_dashboard`, vb.)
- Butonlar (`button_save`, `button_sync`, vb.)
- Hata mesajları (`error_api_connection`, vb.)
- Form etiketleri (`entry_api_key`, vb.)
- Başarı mesajları (`text_success`, vb.)

## 🔌 API Entegrasyonu

Yeni bir pazaryeri API'sini entegre ederken dikkat edilmesi gereken noktalar:

### 1. API İstemcisi Oluşturma

```php
class Entegrator[Pazaryeri] {
    private $apiKey;
    private $apiSecret;
    private $apiUrl;
    private $logger;
    
    public function __construct($registry) {
        $this->config = $registry->get('config');
        $this->log = $registry->get('log');
        
        $this->apiKey = $this->config->get('module_mestech_[pazaryeri]_api_key');
        $this->apiSecret = $this->config->get('module_mestech_[pazaryeri]_api_secret');
        $this->apiUrl = $this->config->get('module_mestech_[pazaryeri]_api_url');
        
        // Loglama için hazırlık
        $this->logger = new \Library\meschain\Logger('[pazaryeri]');
    }
    
    public function connect() {
        // API bağlantı kodları
    }
    
    // Diğer API metodları...
}
```

### 2. API İstekleri ve Yanıt İşleme

```php
public function getProducts($parameters = []) {
    try {
        // API isteği gönderme
        $response = $this->makeRequest('GET', '/products', $parameters);
        
        // Yanıtı işleme
        return $this->formatProductData($response);
    } catch (\Exception $e) {
        $this->logger->error('API Ürün Çekme Hatası: ' . $e->getMessage());
        return false;
    }
}

private function makeRequest($method, $endpoint, $data = []) {
    // HTTP isteği yapma
    // Guzzle veya CURL kullanılabilir
}

private function formatProductData($apiResponse) {
    // API yanıtını OpenCart uyumlu formata dönüştürme
}
```

## 🎮 Controller Yapısı

Controller dosyaları OpenCart standartlarına uygun olmalıdır:

```php
<?php
class ControllerExtensionMestech[Pazaryeri] extends Controller {
    private $error = array();
    
    public function index() {
        $this->load->language('extension/mestech/[pazaryeri]/[pazaryeri]');
        $this->document->setTitle($this->language->get('heading_title'));
        
        // Dashboard verilerini yükleme
        $data['breadcrumbs'] = $this->getBreadcrumbs();
        
        // Şablon verilerini hazırlama
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        // Şablonu yükleme
        $this->response->setOutput($this->load->view('extension/mestech/[pazaryeri]/dashboard', $data));
    }
    
    public function settings() {
        // Ayarlar sayfası için kod
    }
    
    // Diğer metodlar...
    
    private function getBreadcrumbs() {
        // Breadcrumb verilerini hazırlama
    }
}
```

## 📊 Model Yapısı

Modeller, veritabanı işlemlerini yönetir:

```php
<?php
class ModelExtensionMestech[Pazaryeri] extends Model {
    public function saveSettings($data) {
        // Ayarları kaydetme
    }
    
    public function getSettings() {
        // Ayarları getirme
    }
    
    public function saveProductMapping($opencart_product_id, $marketplace_product_id) {
        // Ürün eşleştirmelerini kaydetme
    }
    
    // Diğer metodlar...
}
```

## 🎨 View (Twig) Şablonları

Şablonlar tutarlı bir yapıda olmalıdır:

```twig
{{ header }}{{ column_left }}

<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-module" class="btn btn-primary">{{ button_save }}</button>
      </div>
      <h1>{{ heading_title }}</h1>
      <ul class="breadcrumb">
        {% for breadcrumb in breadcrumbs %}
        <li><a href="{{ breadcrumb.href }}">{{ breadcrumb.text }}</a></li>
        {% endfor %}
      </ul>
    </div>
  </div>
  
  <div class="container-fluid">
    {% if error_warning %}
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> {{ error_warning }}
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    {% endif %}
    
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> {{ text_edit }}</h3>
      </div>
      <div class="panel-body">
        <!-- İçerik buraya gelecek -->
      </div>
    </div>
  </div>
</div>

{{ footer }}
```

## 🌐 Dil Dosyaları

```php
<?php
// Dil sabitlerini tanımlama
$_['heading_title']            = '[Pazaryeri] Entegrasyonu';

// Dashboard
$_['text_dashboard']           = 'Dashboard';
$_['text_settings']            = 'Ayarlar';
$_['text_products']            = 'Ürünler';
$_['text_orders']              = 'Siparişler';

// Butonlar
$_['button_save']              = 'Kaydet';
$_['button_sync']              = 'Senkronize Et';
$_['button_cancel']            = 'İptal';

// Form etiketleri
$_['entry_api_key']            = 'API Anahtarı';
$_['entry_api_secret']         = 'API Secret';
$_['entry_status']             = 'Durum';

// Mesajlar
$_['text_success']             = 'Başarılı: [Pazaryeri] ayarları güncellendi!';
$_['error_api_connection']     = 'API bağlantı hatası: %s';
$_['error_permission']         = 'Uyarı: [Pazaryeri] modülünü değiştirme izniniz yok!';
```

## 📝 Loglama ve Hata Yönetimi

Tüm API istekleri ve önemli işlemler loglanmalıdır:

```php
// system/library/meschain/logger.php sınıfını kullanma
$this->logger->info('[Pazaryeri] API isteği gönderildi: ' . json_encode($parameters));
$this->logger->error('[Pazaryeri] API hatası: ' . $e->getMessage());
```

## 🧪 Test Etme

Her modül için manuel test senaryoları oluşturun:

1. API bağlantı testi
2. Ayarların kaydedilmesi ve yüklenmesi
3. Ürün senkronizasyonu
4. Sipariş senkronizasyonu
5. Hata durumları yönetimi

## 📚 Dokümantasyon

Her modül için aşağıdaki dokümanları oluşturun:

1. **README.md** - Modül açıklaması
2. **TODO.md** - Yapılacaklar listesi
3. **test_cases.md** - Test senaryoları
4. **API_GUIDE.md** - API kullanım rehberi

Bu rehber, MesChain-Sync projesine yeni pazaryeri modülleri eklerken tutarlı ve kaliteli kod üretmeyi sağlamak için tasarlanmıştır. 