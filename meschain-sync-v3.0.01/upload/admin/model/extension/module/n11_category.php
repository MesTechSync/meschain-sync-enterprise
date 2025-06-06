<?php
/**
 * N11 Category Model
 * MesChain-Sync N11 kategori yönetimi için model dosyası
 */
class ModelExtensionModuleN11Category extends Model {
    
    /**
     * N11 kategori ağacını getir
     * @param int $parent_id Üst kategori ID
     * @return array Kategori listesi
     */
    public function getN11Categories($parent_id = 0) {
        $cache_key = 'n11.categories.' . $parent_id;
        $categories = $this->cache->get($cache_key);
        
        if (!$categories) {
            $categories = $this->fetchCategoriesFromAPI($parent_id);
            $this->cache->set($cache_key, $categories, 3600); // 1 saat cache
        }
        
        return $categories;
    }
    
    /**
     * N11 API'dan kategorileri getir
     * @param int $parent_id Üst kategori ID
     * @return array Kategori listesi
     */
    private function fetchCategoriesFromAPI($parent_id = 0) {
        try {
            $this->load->library('meschain/helper/n11');
            $n11_helper = new MeschainN11Helper($this->registry);
            
            $categories = $n11_helper->getCategories($parent_id);
            
            // Veritabanına kaydet
            $this->saveCategoriesFromAPI($categories);
            
            return $categories;
            
        } catch (Exception $e) {
            $this->log->write('N11 Category API Error: ' . $e->getMessage());
            
            // API hata durumunda veritabanından getir
            return $this->getCategoriesFromDB($parent_id);
        }
    }
    
    /**
     * Kategorileri veritabanına kaydet
     * @param array $categories Kategori listesi
     */
    private function saveCategoriesFromAPI($categories) {
        foreach ($categories as $category) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "n11_category` 
                (category_id, parent_id, name, level, leaf, date_updated) 
                VALUES (
                    '" . (int)$category['id'] . "',
                    '" . (int)$category['parentId'] . "',
                    '" . $this->db->escape($category['name']) . "',
                    '" . (int)$category['level'] . "',
                    '" . (int)$category['isLeaf'] . "',
                    NOW()
                ) ON DUPLICATE KEY UPDATE
                    name = VALUES(name),
                    level = VALUES(level),
                    leaf = VALUES(leaf),
                    date_updated = NOW()");
        }
    }
    
    /**
     * Kategorileri veritabanından getir
     * @param int $parent_id Üst kategori ID
     * @return array Kategori listesi
     */
    private function getCategoriesFromDB($parent_id = 0) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "n11_category` 
                                   WHERE parent_id = '" . (int)$parent_id . "' 
                                   ORDER BY name ASC");
        
        return $query->rows;
    }
    
    /**
     * OpenCart kategorilerini getir
     * @param int $parent_id Üst kategori ID
     * @return array Kategori listesi
     */
    public function getOpenCartCategories($parent_id = 0) {
        $query = $this->db->query("SELECT c.category_id, cd.name, c.parent_id 
                                   FROM `" . DB_PREFIX . "category` c 
                                   LEFT JOIN `" . DB_PREFIX . "category_description` cd 
                                   ON (c.category_id = cd.category_id) 
                                   WHERE c.parent_id = '" . (int)$parent_id . "' 
                                   AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' 
                                   AND c.status = '1'
                                   ORDER BY cd.name ASC");
        
        return $query->rows;
    }
    
    /**
     * Kategori eşleştirmelerini getir
     * @param array $data Filtreleme parametreleri
     * @return array Eşleştirme listesi
     */
    public function getCategoryMappings($data = []) {
        $sql = "SELECT cm.*, 
                       ocd.name as opencart_category_name,
                       nc.name as n11_category_name
                FROM `" . DB_PREFIX . "n11_category_mapping` cm
                LEFT JOIN `" . DB_PREFIX . "category_description` ocd 
                ON (cm.opencart_category_id = ocd.category_id AND ocd.language_id = '" . (int)$this->config->get('config_language_id') . "')
                LEFT JOIN `" . DB_PREFIX . "n11_category` nc 
                ON (cm.n11_category_id = nc.category_id)
                WHERE 1=1";
        
        if (!empty($data['filter_opencart_category'])) {
            $sql .= " AND cm.opencart_category_id = '" . (int)$data['filter_opencart_category'] . "'";
        }
        
        if (!empty($data['filter_n11_category'])) {
            $sql .= " AND cm.n11_category_id = '" . (int)$data['filter_n11_category'] . "'";
        }
        
        if (!empty($data['filter_status'])) {
            $sql .= " AND cm.status = '" . (int)$data['filter_status'] . "'";
        }
        
        $sort_data = [
            'ocd.name',
            'nc.name',
            'cm.status',
            'cm.date_added'
        ];
        
        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY cm.date_added";
        }
        
        if (isset($data['order']) && ($data['order'] == 'ASC')) {
            $sql .= " ASC";
        } else {
            $sql .= " DESC";
        }
        
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
     * Kategori eşleştirme ekle
     * @param array $data Eşleştirme verileri
     * @return int Eşleştirme ID
     */
    public function addCategoryMapping($data) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "n11_category_mapping` SET 
            opencart_category_id = '" . (int)$data['opencart_category_id'] . "',
            n11_category_id = '" . (int)$data['n11_category_id'] . "',
            commission_rate = '" . (float)($data['commission_rate'] ?? 0) . "',
            status = '" . (int)($data['status'] ?? 1) . "',
            date_added = NOW(),
            date_modified = NOW()");
        
        $mapping_id = $this->db->getLastId();
        
        // Cache temizle
        $this->cache->delete('n11.category.mapping.*');
        
        return $mapping_id;
    }
    
    /**
     * Kategori eşleştirme güncelle
     * @param int $mapping_id Eşleştirme ID
     * @param array $data Güncellenecek veriler
     */
    public function editCategoryMapping($mapping_id, $data) {
        $this->db->query("UPDATE `" . DB_PREFIX . "n11_category_mapping` SET 
            opencart_category_id = '" . (int)$data['opencart_category_id'] . "',
            n11_category_id = '" . (int)$data['n11_category_id'] . "',
            commission_rate = '" . (float)($data['commission_rate'] ?? 0) . "',
            status = '" . (int)($data['status'] ?? 1) . "',
            date_modified = NOW()
            WHERE mapping_id = '" . (int)$mapping_id . "'");
        
        // Cache temizle
        $this->cache->delete('n11.category.mapping.*');
    }
    
    /**
     * Kategori eşleştirme sil
     * @param int $mapping_id Eşleştirme ID
     */
    public function deleteCategoryMapping($mapping_id) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "n11_category_mapping` 
                          WHERE mapping_id = '" . (int)$mapping_id . "'");
        
        // Cache temizle
        $this->cache->delete('n11.category.mapping.*');
    }
    
    /**
     * OpenCart kategorisi için N11 kategori eşleştirmesi getir
     * @param int $opencart_category_id OpenCart kategori ID
     * @return array|false Eşleştirme verisi
     */
    public function getMappingByOpenCartCategory($opencart_category_id) {
        $cache_key = 'n11.category.mapping.' . $opencart_category_id;
        $mapping = $this->cache->get($cache_key);
        
        if ($mapping === false) {
            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "n11_category_mapping` 
                                       WHERE opencart_category_id = '" . (int)$opencart_category_id . "' 
                                       AND status = '1'");
            
            $mapping = $query->num_rows ? $query->row : null;
            $this->cache->set($cache_key, $mapping, 3600);
        }
        
        return $mapping;
    }
    
    /**
     * N11 kategorisi için komisyon oranını getir
     * @param int $n11_category_id N11 kategori ID
     * @return float Komisyon oranı
     */
    public function getCommissionRate($n11_category_id) {
        $query = $this->db->query("SELECT commission_rate FROM `" . DB_PREFIX . "n11_category` 
                                   WHERE category_id = '" . (int)$n11_category_id . "'");
        
        return $query->num_rows ? (float)$query->row['commission_rate'] : 0.0;
    }
    
    /**
     * Kategori istatistiklerini getir
     * @return array İstatistikler
     */
    public function getCategoryStats() {
        $stats = [];
        
        // Toplam N11 kategori sayısı
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "n11_category`");
        $stats['total_n11_categories'] = $query->row['total'];
        
        // Yaprak kategori sayısı (ürün eklenebilir)
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "n11_category` WHERE leaf = '1'");
        $stats['leaf_categories'] = $query->row['total'];
        
        // Toplam eşleştirme sayısı
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "n11_category_mapping`");
        $stats['total_mappings'] = $query->row['total'];
        
        // Aktif eşleştirme sayısı
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "n11_category_mapping` WHERE status = '1'");
        $stats['active_mappings'] = $query->row['total'];
        
        // Eşleştirilmemiş OpenCart kategoriler
        $query = $this->db->query("SELECT COUNT(*) as total 
                                   FROM `" . DB_PREFIX . "category` c 
                                   LEFT JOIN `" . DB_PREFIX . "n11_category_mapping` cm ON (c.category_id = cm.opencart_category_id)
                                   WHERE c.status = '1' AND cm.mapping_id IS NULL");
        $stats['unmapped_opencart_categories'] = $query->row['total'];
        
        return $stats;
    }
    
    /**
     * Kategori senkronizasyonunu başlat
     * @return array Sonuç
     */
    public function syncCategories() {
        $result = [
            'success' => false,
            'message' => '',
            'stats' => [
                'fetched' => 0,
                'updated' => 0,
                'errors' => 0
            ]
        ];
        
        try {
            // Kök kategorilerden başla
            $this->syncCategoryTree();
            
            $result['success'] = true;
            $result['message'] = 'Categories synchronized successfully';
            
        } catch (Exception $e) {
            $result['message'] = 'Sync failed: ' . $e->getMessage();
            $this->log->write('N11 Category Sync Error: ' . $e->getMessage());
        }
        
        return $result;
    }
    
    /**
     * Kategori ağacını senkronize et (recursive)
     * @param int $parent_id Üst kategori ID
     */
    private function syncCategoryTree($parent_id = 0) {
        $categories = $this->fetchCategoriesFromAPI($parent_id);
        
        foreach ($categories as $category) {
            // Alt kategoriler varsa recursive olarak sync et
            if (!$category['isLeaf']) {
                $this->syncCategoryTree($category['id']);
            }
        }
    }
    
    /**
     * Kategori tabloları oluştur
     */
    public function createCategoryTables() {
        // N11 kategori tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_category` (
            `category_id` int(11) NOT NULL,
            `parent_id` int(11) DEFAULT 0,
            `name` varchar(255) NOT NULL,
            `level` int(11) DEFAULT 0,
            `leaf` tinyint(1) DEFAULT 0,
            `commission_rate` decimal(15,4) DEFAULT 0.0000,
            `date_added` datetime DEFAULT CURRENT_TIMESTAMP,
            `date_updated` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`category_id`),
            KEY `idx_parent_id` (`parent_id`),
            KEY `idx_leaf` (`leaf`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        
        // N11 kategori eşleştirme tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_category_mapping` (
            `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
            `opencart_category_id` int(11) NOT NULL,
            `n11_category_id` int(11) NOT NULL,
            `commission_rate` decimal(15,4) DEFAULT 0.0000,
            `status` tinyint(1) DEFAULT 1,
            `date_added` datetime NOT NULL,
            `date_modified` datetime NOT NULL,
            PRIMARY KEY (`mapping_id`),
            UNIQUE KEY `idx_mapping` (`opencart_category_id`, `n11_category_id`),
            KEY `idx_opencart_category` (`opencart_category_id`),
            KEY `idx_n11_category` (`n11_category_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }
    
    /**
     * Kategori tabloları sil
     */
    public function dropCategoryTables() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "n11_category_mapping`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "n11_category`");
    }
} 