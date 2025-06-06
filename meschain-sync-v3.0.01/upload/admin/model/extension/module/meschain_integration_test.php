<?php
/**
 * MesChain Integration Test Model
 * MesChain-Sync entegrasyon test sistemleri için model dosyası
 */
class ModelExtensionModuleMeschainIntegrationTest extends Model {
    
    /**
     * Tüm pazaryeri bağlantılarını test et
     * @return array Test sonuçları
     */
    public function testAllMarketplaces() {
        $results = [
            'overall_status' => 'success',
            'test_count' => 0,
            'success_count' => 0,
            'error_count' => 0,
            'marketplaces' => []
        ];
        
        $marketplaces = [
            'trendyol' => 'Trendyol',
            'n11' => 'N11',
            'amazon' => 'Amazon',
            'hepsiburada' => 'Hepsiburada',
            'ebay' => 'eBay',
            'ozon' => 'Ozon'
        ];
        
        foreach ($marketplaces as $key => $name) {
            $marketplace_result = $this->testMarketplace($key);
            $marketplace_result['name'] = $name;
            $results['marketplaces'][$key] = $marketplace_result;
            
            $results['test_count'] += $marketplace_result['test_count'];
            $results['success_count'] += $marketplace_result['success_count'];
            $results['error_count'] += $marketplace_result['error_count'];
            
            if ($marketplace_result['status'] !== 'success') {
                $results['overall_status'] = 'error';
            }
        }
        
        // Test sonucunu veritabanına kaydet
        $this->saveTestResult($results);
        
        return $results;
    }
    
    /**
     * Belirli bir pazaryerini test et
     * @param string $marketplace Pazaryeri adı
     * @return array Test sonucu
     */
    public function testMarketplace($marketplace) {
        $result = [
            'status' => 'success',
            'test_count' => 0,
            'success_count' => 0,
            'error_count' => 0,
            'tests' => []
        ];
        
        try {
            // API bağlantı testi
            $api_test = $this->testAPIConnection($marketplace);
            $result['tests']['api_connection'] = $api_test;
            $result['test_count']++;
            
            if ($api_test['status'] === 'success') {
                $result['success_count']++;
                
                // API bağlantı başarılıysa diğer testleri çalıştır
                $auth_test = $this->testAuthentication($marketplace);
                $result['tests']['authentication'] = $auth_test;
                $result['test_count']++;
                
                if ($auth_test['status'] === 'success') {
                    $result['success_count']++;
                    
                    // Kategori testi
                    $category_test = $this->testCategoryFetch($marketplace);
                    $result['tests']['category_fetch'] = $category_test;
                    $result['test_count']++;
                    
                    if ($category_test['status'] === 'success') {
                        $result['success_count']++;
                    } else {
                        $result['error_count']++;
                    }
                    
                    // Ürün listesi testi
                    $product_test = $this->testProductList($marketplace);
                    $result['tests']['product_list'] = $product_test;
                    $result['test_count']++;
                    
                    if ($product_test['status'] === 'success') {
                        $result['success_count']++;
                    } else {
                        $result['error_count']++;
                    }
                    
                } else {
                    $result['error_count']++;
                }
            } else {
                $result['error_count']++;
                $result['status'] = 'error';
            }
            
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['error_count']++;
            $result['error_message'] = $e->getMessage();
        }
        
        if ($result['error_count'] > 0) {
            $result['status'] = 'error';
        }
        
        return $result;
    }
    
    /**
     * API bağlantı testi
     * @param string $marketplace Pazaryeri
     * @return array Test sonucu
     */
    private function testAPIConnection($marketplace) {
        $test = [
            'name' => 'API Connection',
            'status' => 'error',
            'message' => '',
            'response_time' => 0,
            'details' => []
        ];
        
        $start_time = microtime(true);
        
        try {
            $this->load->library('meschain/helper/' . $marketplace);
            $helper_class = 'Meschain' . ucfirst($marketplace) . 'Helper';
            
            if (class_exists($helper_class)) {
                $helper = new $helper_class($this->registry);
                
                // Basit bir API çağrısı yap (genellikle ping veya status endpoint)
                $response = $helper->testConnection();
                
                $test['response_time'] = round((microtime(true) - $start_time) * 1000, 2);
                
                if ($response && isset($response['success']) && $response['success']) {
                    $test['status'] = 'success';
                    $test['message'] = 'API connection successful';
                    $test['details'] = $response;
                } else {
                    $test['message'] = 'API connection failed: ' . ($response['error'] ?? 'Unknown error');
                    $test['details'] = $response;
                }
            } else {
                $test['message'] = 'Helper class not found: ' . $helper_class;
            }
            
        } catch (Exception $e) {
            $test['response_time'] = round((microtime(true) - $start_time) * 1000, 2);
            $test['message'] = 'Exception: ' . $e->getMessage();
        }
        
        return $test;
    }
    
    /**
     * Kimlik doğrulama testi
     * @param string $marketplace Pazaryeri
     * @return array Test sonucu
     */
    private function testAuthentication($marketplace) {
        $test = [
            'name' => 'Authentication',
            'status' => 'error',
            'message' => '',
            'response_time' => 0,
            'details' => []
        ];
        
        $start_time = microtime(true);
        
        try {
            $this->load->library('meschain/helper/' . $marketplace);
            $helper_class = 'Meschain' . ucfirst($marketplace) . 'Helper';
            
            if (class_exists($helper_class)) {
                $helper = new $helper_class($this->registry);
                
                // Kimlik doğrulama testi
                $response = $helper->testAuthentication();
                
                $test['response_time'] = round((microtime(true) - $start_time) * 1000, 2);
                
                if ($response && isset($response['authenticated']) && $response['authenticated']) {
                    $test['status'] = 'success';
                    $test['message'] = 'Authentication successful';
                    $test['details'] = $response;
                } else {
                    $test['message'] = 'Authentication failed: ' . ($response['error'] ?? 'Invalid credentials');
                    $test['details'] = $response;
                }
            } else {
                $test['message'] = 'Helper class not found: ' . $helper_class;
            }
            
        } catch (Exception $e) {
            $test['response_time'] = round((microtime(true) - $start_time) * 1000, 2);
            $test['message'] = 'Exception: ' . $e->getMessage();
        }
        
        return $test;
    }
    
    /**
     * Kategori çekme testi
     * @param string $marketplace Pazaryeri
     * @return array Test sonucu
     */
    private function testCategoryFetch($marketplace) {
        $test = [
            'name' => 'Category Fetch',
            'status' => 'error',
            'message' => '',
            'response_time' => 0,
            'details' => []
        ];
        
        $start_time = microtime(true);
        
        try {
            $this->load->library('meschain/helper/' . $marketplace);
            $helper_class = 'Meschain' . ucfirst($marketplace) . 'Helper';
            
            if (class_exists($helper_class)) {
                $helper = new $helper_class($this->registry);
                
                // Kategori listesi çek
                $categories = $helper->getCategories();
                
                $test['response_time'] = round((microtime(true) - $start_time) * 1000, 2);
                
                if (is_array($categories) && count($categories) > 0) {
                    $test['status'] = 'success';
                    $test['message'] = 'Categories fetched successfully (' . count($categories) . ' categories)';
                    $test['details'] = ['category_count' => count($categories)];
                } else {
                    $test['message'] = 'No categories found or invalid response';
                }
            } else {
                $test['message'] = 'Helper class not found: ' . $helper_class;
            }
            
        } catch (Exception $e) {
            $test['response_time'] = round((microtime(true) - $start_time) * 1000, 2);
            $test['message'] = 'Exception: ' . $e->getMessage();
        }
        
        return $test;
    }
    
    /**
     * Ürün listesi testi
     * @param string $marketplace Pazaryeri
     * @return array Test sonucu
     */
    private function testProductList($marketplace) {
        $test = [
            'name' => 'Product List',
            'status' => 'error',
            'message' => '',
            'response_time' => 0,
            'details' => []
        ];
        
        $start_time = microtime(true);
        
        try {
            $this->load->library('meschain/helper/' . $marketplace);
            $helper_class = 'Meschain' . ucfirst($marketplace) . 'Helper';
            
            if (class_exists($helper_class)) {
                $helper = new $helper_class($this->registry);
                
                // Ürün listesi çek (ilk 10 ürün)
                $products = $helper->getProducts(['limit' => 10]);
                
                $test['response_time'] = round((microtime(true) - $start_time) * 1000, 2);
                
                if (is_array($products) && count($products) >= 0) {
                    $test['status'] = 'success';
                    $test['message'] = 'Products fetched successfully (' . count($products) . ' products)';
                    $test['details'] = ['product_count' => count($products)];
                } else {
                    $test['message'] = 'Invalid product response';
                }
            } else {
                $test['message'] = 'Helper class not found: ' . $helper_class;
            }
            
        } catch (Exception $e) {
            $test['response_time'] = round((microtime(true) - $start_time) * 1000, 2);
            $test['message'] = 'Exception: ' . $e->getMessage();
        }
        
        return $test;
    }
    
    /**
     * Test sonucunu veritabanına kaydet
     * @param array $result Test sonucu
     */
    private function saveTestResult($result) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_integration_test` SET 
            test_type = 'full_marketplace_test',
            status = '" . $this->db->escape($result['overall_status']) . "',
            total_tests = '" . (int)$result['test_count'] . "',
            success_tests = '" . (int)$result['success_count'] . "',
            error_tests = '" . (int)$result['error_count'] . "',
            result_data = '" . $this->db->escape(json_encode($result)) . "',
            date_created = NOW()");
    }
    
    /**
     * Test geçmişini getir
     * @param array $data Filtre parametreleri
     * @return array Test geçmişi
     */
    public function getTestHistory($data = []) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "meschain_integration_test` WHERE 1=1";
        
        if (!empty($data['filter_status'])) {
            $sql .= " AND status = '" . $this->db->escape($data['filter_status']) . "'";
        }
        
        if (!empty($data['filter_test_type'])) {
            $sql .= " AND test_type = '" . $this->db->escape($data['filter_test_type']) . "'";
        }
        
        if (!empty($data['filter_date_start'])) {
            $sql .= " AND DATE(date_created) >= '" . $this->db->escape($data['filter_date_start']) . "'";
        }
        
        if (!empty($data['filter_date_end'])) {
            $sql .= " AND DATE(date_created) <= '" . $this->db->escape($data['filter_date_end']) . "'";
        }
        
        $sql .= " ORDER BY date_created DESC";
        
        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }
            
            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }
            
            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Benchmark testi çalıştır
     * @param string $marketplace Pazaryeri
     * @return array Benchmark sonucu
     */
    public function runBenchmark($marketplace) {
        $benchmark = [
            'marketplace' => $marketplace,
            'tests' => [],
            'summary' => []
        ];
        
        // API çağrı hızı testi (10 kez API çağrısı)
        $api_times = [];
        for ($i = 0; $i < 10; $i++) {
            $start_time = microtime(true);
            $this->testAPIConnection($marketplace);
            $api_times[] = round((microtime(true) - $start_time) * 1000, 2);
        }
        
        $benchmark['tests']['api_speed'] = [
            'times' => $api_times,
            'average' => round(array_sum($api_times) / count($api_times), 2),
            'min' => min($api_times),
            'max' => max($api_times)
        ];
        
        // Bellek kullanımı testi
        $memory_start = memory_get_usage();
        $this->testMarketplace($marketplace);
        $memory_end = memory_get_usage();
        
        $benchmark['tests']['memory_usage'] = [
            'start' => $memory_start,
            'end' => $memory_end,
            'used' => $memory_end - $memory_start,
            'peak' => memory_get_peak_usage()
        ];
        
        return $benchmark;
    }
    
    /**
     * Test tabloları oluştur
     */
    public function createTestTables() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_integration_test` (
            `test_id` int(11) NOT NULL AUTO_INCREMENT,
            `test_type` varchar(100) NOT NULL,
            `status` enum('success','error','warning') DEFAULT 'success',
            `total_tests` int(11) DEFAULT 0,
            `success_tests` int(11) DEFAULT 0,
            `error_tests` int(11) DEFAULT 0,
            `result_data` longtext,
            `date_created` datetime NOT NULL,
            PRIMARY KEY (`test_id`),
            KEY `idx_status` (`status`),
            KEY `idx_test_type` (`test_type`),
            KEY `idx_date_created` (`date_created`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }
} 