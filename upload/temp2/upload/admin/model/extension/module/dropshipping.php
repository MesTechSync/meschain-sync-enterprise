<?php
/**
 * dropshipping.php
 *
 * Amaç: Dropshipping özelliği için OpenCart yönetici paneli model dosyası.
 *
 * Loglama: Tüm veritabanı işlemleri ve hatalar dropshipping_model.log dosyasına kaydedilir.
 * Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]
 */

class ModelExtensionModuleDropshipping extends Model {
    /**
     * Modül kurulumu
     */
    public function install() {
        $this->writeLog('SYSTEM', 'INSTALL_START', 'Dropshipping modülü kurulumu başladı');
        
        // Dropshipping ürünleri tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "dropshipping_product` (
            `product_id` INT(11) NOT NULL AUTO_INCREMENT,
            `opencart_product_id` INT(11) NOT NULL,
            `supplier` VARCHAR(32) NOT NULL COMMENT 'trendyol, n11, amazon, ebay, hepsiburada, ozon',
            `supplier_product_id` VARCHAR(64) NOT NULL,
            `supplier_price` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
            `supplier_sku` VARCHAR(64) NOT NULL,
            `supplier_barcode` VARCHAR(64) NOT NULL,
            `status` TINYINT(1) NOT NULL DEFAULT 1,
            `date_added` DATETIME NOT NULL,
            `date_modified` DATETIME NOT NULL,
            PRIMARY KEY (`product_id`),
            UNIQUE KEY `opencart_product` (`opencart_product_id`),
            KEY `supplier` (`supplier`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Dropshipping siparişleri tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "dropshipping_order` (
            `order_id` INT(11) NOT NULL AUTO_INCREMENT,
            `opencart_order_id` INT(11) NOT NULL,
            `supplier` VARCHAR(32) NOT NULL,
            `supplier_order_id` VARCHAR(64) NULL,
            `status` VARCHAR(32) NOT NULL DEFAULT 'pending',
            `total_amount` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
            `shipping_cost` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
            `tracking_number` VARCHAR(64) NULL,
            `tracking_url` VARCHAR(255) NULL,
            `date_added` DATETIME NOT NULL,
            `date_modified` DATETIME NOT NULL,
            PRIMARY KEY (`order_id`),
            KEY `opencart_order_id` (`opencart_order_id`),
            KEY `supplier` (`supplier`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Dropshipping sipariş ürünleri tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "dropshipping_order_product` (
            `order_product_id` INT(11) NOT NULL AUTO_INCREMENT,
            `order_id` INT(11) NOT NULL,
            `product_id` INT(11) NOT NULL,
            `quantity` INT(4) NOT NULL DEFAULT 1,
            `price` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
            `total` DECIMAL(15,4) NOT NULL DEFAULT 0.0000,
            PRIMARY KEY (`order_product_id`),
            KEY `order_id` (`order_id`),
            KEY `product_id` (`product_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Dropshipping ayarları tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "dropshipping_setting` (
            `setting_id` INT(11) NOT NULL AUTO_INCREMENT,
            `key` VARCHAR(64) NOT NULL,
            `value` TEXT NOT NULL,
            `date_added` DATETIME NOT NULL,
            `date_modified` DATETIME NOT NULL,
            PRIMARY KEY (`setting_id`),
            UNIQUE KEY `key` (`key`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;");
        
        // Varsayılan ayarları ekle
        $this->db->query("INSERT INTO `" . DB_PREFIX . "dropshipping_setting` SET 
            `key` = 'status', 
            `value` = '0',
            `date_added` = NOW(),
            `date_modified` = NOW()");
            
        $this->db->query("INSERT INTO `" . DB_PREFIX . "dropshipping_setting` SET 
            `key` = 'auto_order', 
            `value` = '0',
            `date_added` = NOW(),
            `date_modified` = NOW()");
            
        $this->db->query("INSERT INTO `" . DB_PREFIX . "dropshipping_setting` SET 
            `key` = 'price_markup', 
            `value` = '20',
            `date_added` = NOW(),
            `date_modified` = NOW()");
            
        $this->db->query("INSERT INTO `" . DB_PREFIX . "dropshipping_setting` SET 
            `key` = 'default_supplier', 
            `value` = 'trendyol',
            `date_added` = NOW(),
            `date_modified` = NOW()");
        
        $this->writeLog('SYSTEM', 'INSTALL_COMPLETE', 'Dropshipping modülü kurulumu tamamlandı');
    }
    
    /**
     * Modülü kaldır
     */
    public function uninstall() {
        $this->writeLog('SYSTEM', 'UNINSTALL_START', 'Dropshipping modülü kaldırılıyor');
        
        // Tabloları silme (isteğe bağlı)
        // $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "dropshipping_product`");
        // $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "dropshipping_order`");
        // $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "dropshipping_order_product`");
        // $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "dropshipping_setting`");
        
        $this->writeLog('SYSTEM', 'UNINSTALL_COMPLETE', 'Dropshipping modülü kaldırıldı');
    }
    
    /**
     * Ayarları kaydet
     */
    public function saveSettings($data) {
        $this->writeLog('USER', 'SAVE_SETTINGS', 'Dropshipping ayarları kaydediliyor');
        
        if (isset($data['dropshipping_status'])) {
            $this->updateSetting('status', $data['dropshipping_status']);
        }
        
        if (isset($data['dropshipping_auto_order'])) {
            $this->updateSetting('auto_order', $data['dropshipping_auto_order']);
        }
        
        if (isset($data['dropshipping_price_markup'])) {
            $this->updateSetting('price_markup', $data['dropshipping_price_markup']);
        }
        
        if (isset($data['dropshipping_default_supplier'])) {
            $this->updateSetting('default_supplier', $data['dropshipping_default_supplier']);
        }
        
        $this->writeLog('USER', 'SAVE_SETTINGS_COMPLETE', 'Dropshipping ayarları kaydedildi');
    }
    
    /**
     * Ayar güncelle
     */
    private function updateSetting($key, $value) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "dropshipping_setting` WHERE `key` = '" . $this->db->escape($key) . "'");
        
        if ($query->num_rows) {
            $this->db->query("UPDATE `" . DB_PREFIX . "dropshipping_setting` SET 
                `value` = '" . $this->db->escape($value) . "',
                `date_modified` = NOW()
                WHERE `key` = '" . $this->db->escape($key) . "'");
        } else {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "dropshipping_setting` SET 
                `key` = '" . $this->db->escape($key) . "',
                `value` = '" . $this->db->escape($value) . "',
                `date_added` = NOW(),
                `date_modified` = NOW()");
        }
    }
    
    /**
     * Ayarları getir
     */
    public function getSettings() {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "dropshipping_setting`");
        
        $settings = [];
        foreach ($query->rows as $row) {
            $settings[$row['key']] = $row['value'];
        }
        
        return $settings;
    }
    
    /**
     * Dropshipping ürünlerini getir
     */
    public function getProducts() {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "dropshipping_product` ORDER BY `date_added` DESC");
        return $query->rows;
    }
    
    /**
     * Tedarikçiden ürün ara
     */
    public function searchProducts($supplier, $keyword) {
        $this->writeLog('USER', 'SEARCH_PRODUCTS', "Ürün arama: Tedarikçi=$supplier, Anahtar=$keyword");
        
        // Tedarikçi API sınıfını yükle
        $className = ucfirst($supplier) . 'Helper';
        $helperFile = DIR_SYSTEM . "library/entegrator/api/{$supplier}_api.php";
        
        if (!file_exists($helperFile)) {
            $this->writeLog('USER', 'SEARCH_ERROR', "API dosyası bulunamadı: $helperFile");
            return false;
        }
        
        require_once($helperFile);
        
        if (!class_exists($className)) {
            $this->writeLog('USER', 'SEARCH_ERROR', "API sınıfı bulunamadı: $className");
            return false;
        }
        
        // API ayarlarını yükle
        $settings = $this->loadApiSettings($supplier);
        if (!$settings) {
            $this->writeLog('USER', 'SEARCH_ERROR', "API ayarları bulunamadı");
            return false;
        }
        
        // Ürün ara
        $results = $className::searchProducts($settings, $keyword);
        
        if ($results && isset($results['products'])) {
            $this->writeLog('USER', 'SEARCH_COMPLETE', count($results['products']) . " adet ürün bulundu");
            return $results['products'];
        }
        
        $this->writeLog('USER', 'SEARCH_ERROR', "Ürün bulunamadı");
        return false;
    }
    
    /**
     * Tedarikçiden ürün içe aktar
     */
    public function importProducts($supplier, $products) {
        $this->writeLog('USER', 'IMPORT_START', "Ürün içe aktarma: Tedarikçi=$supplier, Ürün sayısı=" . count($products));
        
        $importCount = 0;
        
        foreach ($products as $product) {
            // OpenCart'a yeni ürün ekle
            $opencartProductId = $this->addProductToOpencart($supplier, $product);
            
            if ($opencartProductId) {
                // Dropshipping tablosuna ekle
                $this->db->query("INSERT INTO `" . DB_PREFIX . "dropshipping_product` SET 
                    `opencart_product_id` = '" . (int)$opencartProductId . "',
                    `supplier` = '" . $this->db->escape($supplier) . "',
                    `supplier_product_id` = '" . $this->db->escape($product['id']) . "',
                    `supplier_price` = '" . (float)$product['price'] . "',
                    `supplier_sku` = '" . $this->db->escape($product['sku'] ?? '') . "',
                    `supplier_barcode` = '" . $this->db->escape($product['barcode'] ?? '') . "',
                    `status` = '1',
                    `date_added` = NOW(),
                    `date_modified` = NOW()");
                
                $importCount++;
            }
        }
        
        $this->writeLog('USER', 'IMPORT_COMPLETE', "$importCount adet ürün içe aktarıldı");
        return $importCount;
    }
    
    /**
     * OpenCart'a ürün ekle
     */
    private function addProductToOpencart($supplier, $product) {
        $this->load->model('catalog/product');
        
        // Fiyat hesapla (markup ekle)
        $settings = $this->getSettings();
        $markup = isset($settings['price_markup']) ? (float)$settings['price_markup'] : 20;
        $price = $product['price'] * (1 + $markup / 100);
        
        // Ürün verisi oluştur
        $productData = [
            'model' => $product['sku'] ?? $product['id'],
            'sku' => $product['sku'] ?? '',
            'upc' => '',
            'ean' => $product['barcode'] ?? '',
            'jan' => '',
            'isbn' => '',
            'mpn' => '',
            'location' => '',
            'quantity' => $product['quantity'] ?? 0,
            'minimum' => 1,
            'subtract' => 1,
            'stock_status_id' => 5, // Stokta Var
            'date_available' => date('Y-m-d'),
            'manufacturer_id' => 0,
            'shipping' => 1,
            'price' => $price,
            'points' => 0,
            'weight' => 0,
            'weight_class_id' => 1, // Kilogram
            'length' => 0,
            'width' => 0,
            'height' => 0,
            'length_class_id' => 1, // Santimetre
            'status' => 1,
            'tax_class_id' => 0,
            'sort_order' => 0,
            'product_description' => [
                1 => [
                    'name' => $product['name'],
                    'description' => $product['description'] ?? '',
                    'tag' => '',
                    'meta_title' => $product['name'],
                    'meta_description' => '',
                    'meta_keyword' => ''
                ]
            ],
            'product_store' => [0],
            'product_category' => [],
            'product_image' => [],
            'points' => 0
        ];
        
        // Resimler
        if (isset($product['images']) && is_array($product['images'])) {
            foreach ($product['images'] as $index => $imageUrl) {
                if ($index === 0) {
                    $productData['image'] = $this->downloadImage($imageUrl);
                } else {
                    $productData['product_image'][] = [
                        'image' => $this->downloadImage($imageUrl),
                        'sort_order' => $index
                    ];
                }
            }
        }
        
        // OpenCart'a ekle
        return $this->model_catalog_product->addProduct($productData);
    }
    
    /**
     * Resmi indir
     */
    private function downloadImage($url) {
        $imageDir = DIR_IMAGE . 'catalog/dropshipping/';
        
        if (!is_dir($imageDir)) {
            mkdir($imageDir, 0777, true);
        }
        
        $filename = md5($url) . '.jpg';
        $path = 'catalog/dropshipping/' . $filename;
        $fullPath = $imageDir . $filename;
        
        if (!file_exists($fullPath)) {
            $content = file_get_contents($url);
            if ($content) {
                file_put_contents($fullPath, $content);
            } else {
                return '';
            }
        }
        
        return $path;
    }
    
    /**
     * Tedarikçide sipariş oluştur
     */
    public function createSupplierOrder($opencartOrderId) {
        $this->writeLog('USER', 'CREATE_ORDER_START', "Tedarikçi siparişi oluşturuluyor: OpenCart Order ID=$opencartOrderId");
        
        // OpenCart siparişini yükle
        $this->load->model('sale/order');
        $order = $this->model_sale_order->getOrder($opencartOrderId);
        
        if (!$order) {
            $this->writeLog('USER', 'CREATE_ORDER_ERROR', "OpenCart siparişi bulunamadı: $opencartOrderId");
            return false;
        }
        
        // Sipariş ürünlerini yükle
        $orderProducts = $this->model_sale_order->getOrderProducts($opencartOrderId);
        
        // Dropshipping ürünlerini kontrol et
        $supplierOrders = [];
        
        foreach ($orderProducts as $orderProduct) {
            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "dropshipping_product` WHERE `opencart_product_id` = '" . (int)$orderProduct['product_id'] . "'");
            
            if ($query->num_rows) {
                $dropshippingProduct = $query->row;
                $supplier = $dropshippingProduct['supplier'];
                
                if (!isset($supplierOrders[$supplier])) {
                    $supplierOrders[$supplier] = [
                        'products' => [],
                        'total' => 0
                    ];
                }
                
                $supplierOrders[$supplier]['products'][] = [
                    'product_id' => $dropshippingProduct['supplier_product_id'],
                    'quantity' => $orderProduct['quantity'],
                    'price' => $dropshippingProduct['supplier_price'],
                    'total' => $dropshippingProduct['supplier_price'] * $orderProduct['quantity'],
                    'dropshipping_product_id' => $dropshippingProduct['product_id']
                ];
                
                $supplierOrders[$supplier]['total'] += $dropshippingProduct['supplier_price'] * $orderProduct['quantity'];
            }
        }
        
        // Her tedarikçi için sipariş oluştur
        foreach ($supplierOrders as $supplier => $supplierOrder) {
            // Tedarikçi API sınıfını yükle
            $className = ucfirst($supplier) . 'Helper';
            $helperFile = DIR_SYSTEM . "library/entegrator/api/{$supplier}_api.php";
            
            if (!file_exists($helperFile)) {
                $this->writeLog('USER', 'CREATE_ORDER_ERROR', "API dosyası bulunamadı: $helperFile");
                continue;
            }
            
            require_once($helperFile);
            
            if (!class_exists($className)) {
                $this->writeLog('USER', 'CREATE_ORDER_ERROR', "API sınıfı bulunamadı: $className");
                continue;
            }
            
            // API ayarlarını yükle
            $settings = $this->loadApiSettings($supplier);
            if (!$settings) {
                $this->writeLog('USER', 'CREATE_ORDER_ERROR', "API ayarları bulunamadı");
                continue;
            }
            
            // Sipariş verisi oluştur
            $orderData = [
                'customerFirstName' => $order['firstname'],
                'customerLastName' => $order['lastname'],
                'customerEmail' => $order['email'],
                'customerPhone' => $order['telephone'],
                'address' => $order['shipping_address_1'] . ' ' . $order['shipping_address_2'],
                'city' => $order['shipping_city'],
                'district' => '',
                'postalCode' => $order['shipping_postcode'],
                'items' => []
            ];
            
            foreach ($supplierOrder['products'] as $product) {
                $orderData['items'][] = [
                    'productId' => $product['product_id'],
                    'quantity' => $product['quantity']
                ];
            }
            
            // Tedarikçide sipariş oluştur
            $response = $className::createOrder($settings, $orderData);
            
            if ($response && isset($response['id'])) {
                $supplierOrderId = $response['id'];
                
                // Dropshipping sipariş tablosuna ekle
                $this->db->query("INSERT INTO `" . DB_PREFIX . "dropshipping_order` SET 
                    `opencart_order_id` = '" . (int)$opencartOrderId . "',
                    `supplier` = '" . $this->db->escape($supplier) . "',
                    `supplier_order_id` = '" . $this->db->escape($supplierOrderId) . "',
                    `status` = 'pending',
                    `total_amount` = '" . (float)$supplierOrder['total'] . "',
                    `date_added` = NOW(),
                    `date_modified` = NOW()");
                
                $dropshippingOrderId = $this->db->getLastId();
                
                // Sipariş ürünlerini ekle
                foreach ($supplierOrder['products'] as $product) {
                    $this->db->query("INSERT INTO `" . DB_PREFIX . "dropshipping_order_product` SET 
                        `order_id` = '" . (int)$dropshippingOrderId . "',
                        `product_id` = '" . (int)$product['dropshipping_product_id'] . "',
                        `quantity` = '" . (int)$product['quantity'] . "',
                        `price` = '" . (float)$product['price'] . "',
                        `total` = '" . (float)$product['total'] . "'");
                }
                
                $this->writeLog('USER', 'CREATE_ORDER_SUCCESS', "Tedarikçi siparişi oluşturuldu: Supplier=$supplier, Order ID=$supplierOrderId");
            } else {
                $this->writeLog('USER', 'CREATE_ORDER_ERROR', "Tedarikçi siparişi oluşturulamadı: $supplier");
            }
        }
        
        return !empty($supplierOrders);
    }
    
    /**
     * API ayarlarını yükle
     */
    private function loadApiSettings($supplier) {
        // Veritabanında ayar yoksa config dosyasından oku
        $configFile = DIR_SYSTEM . "library/entegrator/config_{$supplier}.php";
        if (file_exists($configFile)) {
            $config = require($configFile);
            $config['endpoint'] = $this->getEndpoint($supplier);
            return $config;
        }
        
        return false;
    }
    
    /**
     * API endpoint'ini döndür
     */
    private function getEndpoint($supplier) {
        $endpoints = [
            'trendyol' => 'https://api.trendyol.com/sapigw',
            'n11' => 'https://api.n11.com/ws',
            'amazon' => 'https://mws-eu.amazonservices.com',
            'ebay' => 'https://api.ebay.com/ws/api.dll',
            'hepsiburada' => 'https://marketplace-api.hepsiburada.com',
            'ozon' => 'https://api-seller.ozon.ru'
        ];
        
        return $endpoints[$supplier] ?? '';
    }
    
    /**
     * Log kaydı
     */
    private function writeLog($user, $action, $message) {
        $log_file = DIR_LOGS . 'dropshipping_model.log';
        $date = date('Y-m-d H:i:s');
        $log = "[$date] [$user] [$action] $message\n";
        file_put_contents($log_file, $log, FILE_APPEND);
    }
} 