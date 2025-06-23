# Trendyol Modülü - Modüler Yapılandırma Kılavuzu

**Versiyon:** 4.5  
**Tarih:** 20 Haziran 2025  
**Uyumluluk:** OpenCart 4.0.2.3+, MesChain-Sync Enterprise v3.0+

---

## 📋 Modüler Mimari Genel Bakış

Trendyol modülü, OpenCart sistemine iki farklı şekilde entegre edilebilecek şekilde tasarlanmıştır:

1. **Bağımsız Modül:** Sadece Trendyol entegrasyonu için tek başına kurulum
2. **MesChain-Sync Eklentisi:** Merkezi çoklu pazaryeri yönetimi sistemi içinde

---

## 🔧 Bağımsız Modül Yapılandırması

### Dosya Yapısı
```
upload/
├── admin/
│   ├── controller/extension/module/
│   │   ├── trendyol.php                    # Ana modül kontrolörü
│   │   └── trendyol_login.php              # Güvenli giriş kontrolörü
│   ├── model/extension/module/
│   │   └── trendyol.php                    # Veritabanı işlemleri
│   ├── view/template/extension/module/
│   │   ├── trendyol.twig                   # Ana panel arayüzü
│   │   ├── trendyol_login.twig             # Giriş ekranı
│   │   ├── trendyol_settings.twig          # Ayarlar paneli
│   │   ├── trendyol_products.twig          # Ürün yönetimi
│   │   ├── trendyol_orders.twig            # Sipariş yönetimi
│   │   └── trendyol_categories.twig        # Kategori eşleştirme
│   └── language/
│       ├── tr-tr/extension/module/trendyol.php
│       └── en-gb/extension/module/trendyol.php
├── catalog/
│   ├── controller/extension/module/
│   │   └── trendyol_api.php                # Dış API endpoint'leri
│   └── model/extension/module/
│       └── trendyol_webhook.php            # Webhook işleyicisi
└── system/
    └── library/meschain/
        ├── api/TrendyolApiClient.php       # API istemcisi
        ├── webhook/TrendyolWebhookHandler.php  # Webhook işleyicisi
        └── helper/
            ├── trendyol.php                # Yardımcı fonksiyonlar
            └── trendyol_helper.php         # Ek yardımcılar
```

### Veritabanı Tabloları
```sql
-- Trendyol modül ayarları
CREATE TABLE `oc_trendyol_settings` (
    `setting_id` int(11) NOT NULL AUTO_INCREMENT,
    `store_id` int(11) NOT NULL DEFAULT '0',
    `key` varchar(64) NOT NULL,
    `value` text NOT NULL,
    `serialized` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`setting_id`),
    KEY `store_id` (`store_id`,`key`)
);

-- Kategori eşleştirmeleri
CREATE TABLE `oc_trendyol_category_mapping` (
    `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
    `opencart_category_id` int(11) NOT NULL,
    `trendyol_category_id` int(11) NOT NULL,
    `trendyol_category_name` varchar(255) NOT NULL,
    `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
    `updated_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`mapping_id`),
    UNIQUE KEY `opencart_category_id` (`opencart_category_id`)
);

-- Ürün senkronizasyon durumu
CREATE TABLE `oc_trendyol_product_sync` (
    `sync_id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `trendyol_product_id` varchar(50) DEFAULT NULL,
    `barcode` varchar(100) DEFAULT NULL,
    `sync_status` enum('pending','synced','failed','updated') DEFAULT 'pending',
    `last_sync_date` datetime DEFAULT NULL,
    `error_message` text,
    `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`sync_id`),
    KEY `product_id` (`product_id`),
    KEY `trendyol_product_id` (`trendyol_product_id`)
);

-- Sipariş senkronizasyonu
CREATE TABLE `oc_trendyol_order_sync` (
    `sync_id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `trendyol_order_number` varchar(100) NOT NULL,
    `sync_status` enum('pending','synced','failed') DEFAULT 'pending',
    `sync_date` datetime DEFAULT NULL,
    `error_message` text,
    `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`sync_id`),
    UNIQUE KEY `trendyol_order_number` (`trendyol_order_number`),
    KEY `order_id` (`order_id`)
);

-- Webhook logları
CREATE TABLE `oc_trendyol_webhook_logs` (
    `log_id` int(11) NOT NULL AUTO_INCREMENT,
    `event_type` varchar(50) NOT NULL,
    `payload` text NOT NULL,
    `processed` tinyint(1) DEFAULT '0',
    `processing_result` text,
    `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
    `processed_date` datetime DEFAULT NULL,
    PRIMARY KEY (`log_id`),
    KEY `event_type` (`event_type`),
    KEY `processed` (`processed`)
);
```

---

## 🔗 MesChain-Sync Entegre Yapılandırması

### Ortak Kütüphaneler
```
system/library/meschain/
├── core/
│   ├── BaseApiClient.php              # Tüm API'ler için temel sınıf
│   ├── WebhookManager.php             # Merkezi webhook yönetimi
│   └── SyncManager.php                # Senkronizasyon koordinatörü
├── marketplace/
│   ├── TrendyolMarketplace.php        # Trendyol özel implementasyon
│   ├── AmazonMarketplace.php          # Amazon implementasyonu
│   └── MarketplaceInterface.php       # Ortak interface
├── security/
│   ├── JwtManager.php                 # JWT token yönetimi
│   ├── AzureAdAuth.php               # Azure AD entegrasyonu
│   └── SecurityHelper.php            # Güvenlik yardımcıları
└── azure/
    ├── AzureStorageManager.php        # Azure Storage entegrasyonu
    ├── AzureKeyVaultClient.php        # Key Vault entegrasyonu
    └── AzureMonitorClient.php         # Monitoring entegrasyonu
```

### MesChain-Sync Panel Entegrasyonu
```php
// admin/controller/extension/module/meschain_sync.php
class ControllerExtensionModuleMeschainSync extends Controller {
    
    private $marketplaces = [
        'trendyol' => 'TrendyolMarketplace',
        'amazon' => 'AmazonMarketplace',
        'n11' => 'N11Marketplace',
        'hepsiburada' => 'HepsiburadaMarketplace'
    ];
    
    public function index() {
        // Merkezi dashboard
        $data['active_marketplaces'] = $this->getActiveMarketplaces();
        $data['sync_status'] = $this->getSyncStatus();
        $data['recent_orders'] = $this->getRecentOrders();
        
        $this->response->setOutput($this->load->view('extension/module/meschain_sync', $data));
    }
    
    public function trendyol() {
        // Trendyol özel paneli
        $this->load->controller('extension/module/trendyol');
    }
}
```

---

## ⚙️ Yapılandırma Dosyaları

### config/trendyol.php
```php
<?php
return [
    'api' => [
        'base_url' => 'https://api.trendyol.com/sapigw',
        'timeout' => 30,
        'retry_attempts' => 3,
        'retry_delay' => 2, // saniye
    ],
    
    'webhook' => [
        'enabled' => true,
        'events' => [
            'orderCreated',
            'orderStatusChanged',
            'orderCancelled',
            'stockUpdated',
            'priceUpdated'
        ],
        'signature_verification' => true,
    ],
    
    'sync' => [
        'batch_size' => 50,
        'sync_interval' => 300, // 5 dakika
        'auto_sync_enabled' => true,
        'sync_images' => true,
        'sync_stock' => true,
        'sync_prices' => true,
    ],
    
    'logging' => [
        'enabled' => true,
        'level' => 'info', // debug, info, warning, error
        'max_file_size' => '10MB',
        'max_files' => 5,
    ],
    
    'cache' => [
        'enabled' => true,
        'ttl' => [
            'products' => 3600,      // 1 saat
            'categories' => 86400,   // 24 saat
            'api_responses' => 300,  // 5 dakika
        ]
    ],
    
    'security' => [
        'jwt_enabled' => true,
        'azure_ad_enabled' => true,
        'rate_limiting' => [
            'enabled' => true,
            'requests_per_minute' => 60,
            'burst_limit' => 10,
        ]
    ]
];
```

### config/azure.php
```php
<?php
return [
    'storage' => [
        'account_name' => env('AZURE_STORAGE_ACCOUNT'),
        'account_key' => env('AZURE_STORAGE_KEY'),
        'container_name' => 'trendyol-data',
        'cdn_url' => env('AZURE_CDN_URL'),
    ],
    
    'key_vault' => [
        'vault_url' => env('AZURE_KEY_VAULT_URL'),
        'client_id' => env('AZURE_CLIENT_ID'),
        'client_secret' => env('AZURE_CLIENT_SECRET'),
        'tenant_id' => env('AZURE_TENANT_ID'),
    ],
    
    'monitor' => [
        'workspace_id' => env('AZURE_MONITOR_WORKSPACE_ID'),
        'workspace_key' => env('AZURE_MONITOR_WORKSPACE_KEY'),
        'custom_metrics_enabled' => true,
    ],
    
    'service_bus' => [
        'connection_string' => env('AZURE_SERVICE_BUS_CONNECTION'),
        'queue_name' => 'trendyol-sync-queue',
        'topic_name' => 'marketplace-events',
    ]
];
```

---

## 🔌 Eklenti Sistemi

### Trendyol Eklenti Kaydı
```php
// system/library/meschain/plugins/TrendyolPlugin.php
class TrendyolPlugin implements PluginInterface {
    
    public function getName(): string {
        return 'Trendyol Marketplace Integration';
    }
    
    public function getVersion(): string {
        return '4.5.0';
    }
    
    public function getDependencies(): array {
        return ['meschain-core', 'azure-integration'];
    }
    
    public function install(): bool {
        // Kurulum işlemleri
        $this->createTables();
        $this->seedDefaultData();
        $this->registerRoutes();
        return true;
    }
    
    public function uninstall(): bool {
        // Kaldırma işlemleri (isteğe bağlı)
        // $this->dropTables(); // Veri kaybı riski
        $this->unregisterRoutes();
        return true;
    }
    
    public function activate(): bool {
        $this->enableWebhooks();
        $this->startSyncScheduler();
        return true;
    }
    
    public function deactivate(): bool {
        $this->disableWebhooks();
        $this->stopSyncScheduler();
        return true;
    }
}
```

### Plugin Manager
```php
// admin/controller/extension/module/plugin_manager.php
class ControllerExtensionModulePluginManager extends Controller {
    
    public function index() {
        $plugins = $this->model_extension_module_plugin_manager->getInstalledPlugins();
        
        $data['plugins'] = [];
        foreach ($plugins as $plugin) {
            $data['plugins'][] = [
                'name' => $plugin->getName(),
                'version' => $plugin->getVersion(),
                'status' => $this->getPluginStatus($plugin),
                'actions' => $this->getPluginActions($plugin)
            ];
        }
        
        $this->response->setOutput($this->load->view('extension/module/plugin_manager', $data));
    }
    
    public function activate() {
        $plugin_code = $this->request->get['plugin'];
        $plugin = $this->model_extension_module_plugin_manager->getPlugin($plugin_code);
        
        if ($plugin && $plugin->activate()) {
            $this->session->data['success'] = 'Plugin başarıyla etkinleştirildi!';
        } else {
            $this->session->data['error'] = 'Plugin etkinleştirilemedi!';
        }
        
        $this->response->redirect($this->url->link('extension/module/plugin_manager'));
    }
}
```

---

## 🔄 Event System Entegrasyonu

### Event Hooks
```php
// system/library/meschain/events/TrendyolEvents.php
class TrendyolEvents {
    
    public function __construct($registry) {
        $this->registry = $registry;
    }
    
    // Ürün eklendiğinde Trendyol'a senkronize et
    public function onProductAdded($route, $data) {
        if ($this->config->get('trendyol_auto_sync_enabled')) {
            $this->load->model('extension/module/trendyol');
            $this->model_extension_module_trendyol->syncProduct($data[0]);
        }
    }
    
    // Ürün güncellendiğinde Trendyol'da güncelle
    public function onProductUpdated($route, $data) {
        if ($this->config->get('trendyol_auto_sync_enabled')) {
            $this->load->model('extension/module/trendyol');
            $this->model_extension_module_trendyol->updateProduct($data[0]);
        }
    }
    
    // Stok değiştiğinde Trendyol'da güncelle
    public function onStockUpdated($route, $data) {
        if ($this->config->get('trendyol_sync_stock')) {
            $this->load->model('extension/module/trendyol');
            $this->model_extension_module_trendyol->updateStock($data[0], $data[1]);
        }
    }
}
```

### Event Registration
```php
// admin/model/extension/module/trendyol.php - install() method
public function install() {
    // Veritabanı tablolarını oluştur
    $this->createTables();
    
    // Event hooks kaydet
    $this->load->model('setting/event');
    
    $events = [
        ['trigger' => 'catalog/model/catalog/product/addProduct/after', 'action' => 'extension/module/trendyol/productAdded'],
        ['trigger' => 'catalog/model/catalog/product/editProduct/after', 'action' => 'extension/module/trendyol/productUpdated'],
        ['trigger' => 'catalog/model/catalog/product/editStock/after', 'action' => 'extension/module/trendyol/stockUpdated'],
        ['trigger' => 'catalog/model/checkout/order/addOrder/after', 'action' => 'extension/module/trendyol/orderAdded'],
    ];
    
    foreach ($events as $event) {
        $this->model_setting_event->addEvent('trendyol', $event['trigger'], $event['action']);
    }
}
```

---

## 🚀 Deployment Stratejileri

### Docker Konteyner Yapılandırması
```dockerfile
# Dockerfile.trendyol
FROM php:8.1-apache

# OpenCart ve Trendyol modülü için gerekli PHP uzantıları
RUN docker-php-ext-install mysqli pdo pdo_mysql curl json gd zip

# Azure uzantıları
RUN pecl install sqlsrv pdo_sqlsrv
RUN docker-php-ext-enable sqlsrv pdo_sqlsrv

# OpenCart dosyalarını kopyala
COPY upload/ /var/www/html/

# Trendyol modül yapılandırması
COPY config/trendyol.php /var/www/html/config/
COPY config/azure.php /var/www/html/config/

# Apache yapılandırması
COPY apache/opencart.conf /etc/apache2/sites-available/
RUN a2ensite opencart.conf
RUN a2enmod rewrite ssl

EXPOSE 80 443

CMD ["apache2-foreground"]
```

### Kubernetes Deployment
```yaml
# k8s/trendyol-deployment.yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: opencart-trendyol
spec:
  replicas: 3
  selector:
    matchLabels:
      app: opencart-trendyol
  template:
    metadata:
      labels:
        app: opencart-trendyol
    spec:
      containers:
      - name: opencart
        image: meschain/opencart-trendyol:4.5
        ports:
        - containerPort: 80
        env:
        - name: DB_HOSTNAME
          value: "mysql-service"
        - name: DB_USERNAME
          valueFrom:
            secretKeyRef:
              name: mysql-secret
              key: username
        - name: DB_PASSWORD
          valueFrom:
            secretKeyRef:
              name: mysql-secret
              key: password
        - name: AZURE_KEY_VAULT_URL
          valueFrom:
            configMapKeyRef:
              name: azure-config
              key: key-vault-url
        resources:
          requests:
            memory: "256Mi"
            cpu: "250m"
          limits:
            memory: "512Mi"
            cpu: "500m"
---
apiVersion: v1
kind: Service
metadata:
  name: opencart-trendyol-service
spec:
  selector:
    app: opencart-trendyol
  ports:
  - protocol: TCP
    port: 80
    targetPort: 80
  type: LoadBalancer
```

---

## 📊 Monitoring ve Analytics

### Azure Monitor Integration
```php
// system/library/meschain/monitoring/TrendyolMonitor.php
class TrendyolMonitor {
    
    public function logApiRequest($endpoint, $method, $response_time, $status_code) {
        $this->azureMonitor->trackRequest([
            'name' => "trendyol_api_{$endpoint}",
            'method' => $method,
            'duration' => $response_time,
            'responseCode' => $status_code,
            'success' => $status_code < 400
        ]);
    }
    
    public function logSyncOperation($operation, $items_count, $success_count, $error_count) {
        $this->azureMonitor->trackCustomEvent('trendyol_sync', [
            'operation' => $operation,
            'items_processed' => $items_count,
            'success_count' => $success_count,
            'error_count' => $error_count,
            'success_rate' => ($success_count / $items_count) * 100
        ]);
    }
    
    public function alertOnError($error_message, $context = []) {
        $this->azureMonitor->trackException(new Exception($error_message), [
            'module' => 'trendyol',
            'context' => json_encode($context)
        ]);
    }
}
```

### Performance Metrics
```php
// Performance tracking örneği
class TrendyolPerformanceTracker {
    
    public function trackProductSync($product_id, $start_time, $end_time, $result) {
        $duration = $end_time - $start_time;
        
        $this->azureMonitor->trackMetric('trendyol_product_sync_duration', $duration, [
            'product_id' => $product_id,
            'result' => $result ? 'success' : 'failure'
        ]);
        
        if ($duration > 5000) { // 5 saniyeden uzun sürerse
            $this->azureMonitor->trackEvent('slow_product_sync', [
                'product_id' => $product_id,
                'duration' => $duration
            ]);
        }
    }
}
```

---

## 🔧 Sorun Giderme ve Bakım

### Diagnostic Tool
```php
// admin/controller/extension/module/trendyol_diagnostic.php
class ControllerExtensionModuleTrendyolDiagnostic extends Controller {
    
    public function index() {
        $data['checks'] = [
            'database' => $this->checkDatabase(),
            'api_connection' => $this->checkApiConnection(),
            'webhook_endpoint' => $this->checkWebhookEndpoint(),
            'file_permissions' => $this->checkFilePermissions(),
            'ssl_certificate' => $this->checkSslCertificate(),
            'azure_services' => $this->checkAzureServices()
        ];
        
        $this->response->setOutput($this->load->view('extension/module/trendyol_diagnostic', $data));
    }
    
    private function checkApiConnection() {
        try {
            $this->load->library('meschain/api/TrendyolApiClient');
            $client = new TrendyolApiClient();
            $response = $client->testConnection();
            
            return [
                'status' => 'success',
                'message' => 'API bağlantısı başarılı',
                'details' => $response
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'API bağlantısı başarısız: ' . $e->getMessage()
            ];
        }
    }
}
```

Bu modüler yapılandırma kılavuzu, Trendyol modülünün hem bağımsız hem de MesChain-Sync entegre kurulumu için gerekli tüm teknik detayları içermektedir.
