<?php
/**
 * MesChain-Sync Trendyol Integration V2
 * Category Mapping Model
 */
namespace Opencart\Admin\Model\Extension\MeschainSync\Extension\Meschain;

class CategoryMapping extends \Opencart\System\Engine\Model {
    
    /**
     * Kategori eşleştirmeleri tablosunu oluştur
     */
    public function createTable(): void {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_category_mapping` (
                `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
                `opencart_category_id` int(11) NOT NULL,
                `trendyol_category_id` int(11) NOT NULL,
                `trendyol_category_name` varchar(255) NOT NULL,
                `opencart_category_name` varchar(255) NOT NULL,
                `mapping_type` enum('manual','auto','ai_suggested') DEFAULT 'manual',
                `confidence_score` decimal(3,2) DEFAULT NULL,
                `is_active` tinyint(1) DEFAULT 1,
                `created_by` int(11) DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`mapping_id`),
                UNIQUE KEY `opencart_category_id` (`opencart_category_id`),
                KEY `trendyol_category_id` (`trendyol_category_id`),
                KEY `mapping_type` (`mapping_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_category` (
                `trendyol_category_id` int(11) NOT NULL,
                `name` varchar(255) NOT NULL,
                `parent_id` int(11) DEFAULT NULL,
                `path` varchar(500) DEFAULT NULL,
                `leaf` tinyint(1) DEFAULT 0,
                `display_order` int(11) DEFAULT 0,
                `last_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`trendyol_category_id`),
                KEY `parent_id` (`parent_id`),
                KEY `leaf` (`leaf`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    /**
     * Trendyol kategorilerini kaydet
     */
    public function saveTrendyolCategories($categories_data): void {
        if (!isset($categories_data['categories']) || !is_array($categories_data['categories'])) {
            throw new \Exception('Invalid categories data format');
        }

        // Önce eski kategori tablosunu temizle (veya kategorileri inaktif yap)
        $this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "trendyol_category`");
        
        // Ardından yeni kategorileri ekle
        foreach ($categories_data['categories'] as $category) {
            $this->saveTrendyolCategoryRecursive($category);
        }
    }

    /**
     * Trendyol kategorisini ve alt kategorilerini recursive olarak kaydet
     */
    private function saveTrendyolCategoryRecursive($category, $parent_id = 0, $path = ''): void {
        $category_name = $category['name'];
        $category_id = $category['id'];
        $is_leaf = isset($category['subCategories']) && empty($category['subCategories']);
        
        // Path oluştur
        $current_path = $path ? $path . ' > ' . $category_name : $category_name;
        
        // Ana kategoriyi kaydet
        $this->db->query("INSERT INTO `" . DB_PREFIX . "trendyol_category` SET 
            `trendyol_category_id` = '" . (int)$category_id . "', 
            `name` = '" . $this->db->escape($category_name) . "', 
            `parent_id` = '" . (int)$parent_id . "',
            `path` = '" . $this->db->escape($current_path) . "',
            `leaf` = '" . ($is_leaf ? 1 : 0) . "',
            `display_order` = '" . (isset($category['displayOrder']) ? (int)$category['displayOrder'] : 0) . "'
        ");
        
        // Alt kategorileri kaydet
        if (isset($category['subCategories']) && is_array($category['subCategories'])) {
            foreach ($category['subCategories'] as $subcategory) {
                $this->saveTrendyolCategoryRecursive($subcategory, $category_id, $current_path);
            }
        }
    }

    /**
     * Tüm kategori eşleştirmelerini getir
     */
    public function getCategoryMappings(): array {
        $query = $this->db->query("
            SELECT m.*, oc.name as opencart_name, tc.name as trendyol_name, tc.path as trendyol_path 
            FROM `" . DB_PREFIX . "trendyol_category_mapping` m 
            LEFT JOIN `" . DB_PREFIX . "category_description` oc ON (m.opencart_category_id = oc.category_id) 
            LEFT JOIN `" . DB_PREFIX . "trendyol_category` tc ON (m.trendyol_category_id = tc.trendyol_category_id) 
            WHERE oc.language_id = '" . (int)$this->config->get('config_language_id') . "'
            ORDER BY tc.path ASC
        ");
        
        return $query->rows;
    }

    /**
     * Kategori eşleştirme kaydını getir
     */
    public function getMappingByOpenCartCategoryId($category_id): array {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "trendyol_category_mapping` 
            WHERE opencart_category_id = '" . (int)$category_id . "'
        ");
        
        return $query->row ?? [];
    }

    /**
     * Trendyol Kategorilerini getir
     */
    public function getTrendyolCategories(): array {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "trendyol_category` 
            ORDER BY path ASC
        ");
        
        return $query->rows;
    }

    /**
     * Kategori eşleştirmelerini kaydet
     */
    public function saveCategoryMapping(array $data): void {
        if (!isset($data['category_mapping']) || !is_array($data['category_mapping'])) {
            return;
        }

        foreach ($data['category_mapping'] as $opencart_category_id => $trendyol_category_id) {
            if (empty($trendyol_category_id)) {
                continue;
            }
            
            // OpenCart kategori adını al
            $category_query = $this->db->query("
                SELECT name FROM `" . DB_PREFIX . "category_description` 
                WHERE category_id = '" . (int)$opencart_category_id . "' 
                AND language_id = '" . (int)$this->config->get('config_language_id') . "'
            ");
            
            $opencart_name = $category_query->row['name'] ?? '';
            
            // Trendyol kategori adını al
            $trendyol_query = $this->db->query("
                SELECT name FROM `" . DB_PREFIX . "trendyol_category` 
                WHERE trendyol_category_id = '" . (int)$trendyol_category_id . "'
            ");
            
            $trendyol_name = $trendyol_query->row['name'] ?? '';
            
            // Var olan eşleştirmeyi kontrol et
            $existing_query = $this->db->query("
                SELECT mapping_id FROM `" . DB_PREFIX . "trendyol_category_mapping` 
                WHERE opencart_category_id = '" . (int)$opencart_category_id . "'
            ");
            
            $now = date('Y-m-d H:i:s');
            
            if ($existing_query->num_rows) {
                // Güncelle
                $this->db->query("
                    UPDATE `" . DB_PREFIX . "trendyol_category_mapping` SET 
                    `trendyol_category_id` = '" . (int)$trendyol_category_id . "', 
                    `trendyol_category_name` = '" . $this->db->escape($trendyol_name) . "', 
                    `opencart_category_name` = '" . $this->db->escape($opencart_name) . "', 
                    `mapping_type` = 'manual',
                    `updated_at` = '" . $this->db->escape($now) . "' 
                    WHERE opencart_category_id = '" . (int)$opencart_category_id . "'
                ");
            } else {
                // Yeni ekle
                $this->db->query("
                    INSERT INTO `" . DB_PREFIX . "trendyol_category_mapping` SET 
                    `opencart_category_id` = '" . (int)$opencart_category_id . "', 
                    `trendyol_category_id` = '" . (int)$trendyol_category_id . "', 
                    `trendyol_category_name` = '" . $this->db->escape($trendyol_name) . "', 
                    `opencart_category_name` = '" . $this->db->escape($opencart_name) . "', 
                    `mapping_type` = 'manual',
                    `created_by` = '" . (int)$this->user->getId() . "',
                    `created_at` = '" . $this->db->escape($now) . "',
                    `updated_at` = '" . $this->db->escape($now) . "'
                ");
            }
        }
    }

    /**
     * Otomatik kategori eşleştirme
     */
    public function autoMapCategories(): int {
        // OpenCart kategorilerini al
        $opencart_categories_query = $this->db->query("
            SELECT c.category_id, cd.name 
            FROM `" . DB_PREFIX . "category` c 
            LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (c.category_id = cd.category_id) 
            WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "'
            ORDER BY cd.name
        ");
        
        $opencart_categories = $opencart_categories_query->rows;
        
        // Trendyol kategorilerini al
        $trendyol_categories_query = $this->db->query("
            SELECT trendyol_category_id, name, path 
            FROM `" . DB_PREFIX . "trendyol_category` 
            WHERE leaf = 1
            ORDER BY name
        ");
        
        $trendyol_categories = $trendyol_categories_query->rows;
        
        $mapped_count = 0;
        $now = date('Y-m-d H:i:s');
        
        foreach ($opencart_categories as $oc_category) {
            // Bu kategori zaten eşleştirilmiş mi kontrol et
            $existing_query = $this->db->query("
                SELECT mapping_id FROM `" . DB_PREFIX . "trendyol_category_mapping` 
                WHERE opencart_category_id = '" . (int)$oc_category['category_id'] . "'
            ");
            
            if ($existing_query->num_rows) {
                continue; // Zaten eşleştirilmiş, atlayalım
            }
            
            // İsim benzerliği için en iyi eşleşmeyi bul
            $best_match = null;
            $highest_score = 0;
            
            foreach ($trendyol_categories as $tr_category) {
                // İsim benzerliği skoru hesapla (basit)
                $score = $this->calculateSimilarityScore($oc_category['name'], $tr_category['name']);
                
                if ($score > $highest_score && $score > 0.6) { // 0.6 eşik değeri
                    $highest_score = $score;
                    $best_match = $tr_category;
                }
            }
            
            // Yeterli benzerlik skoru ile eşleşme bulunduysa kaydet
            if ($best_match) {
                $this->db->query("
                    INSERT INTO `" . DB_PREFIX . "trendyol_category_mapping` SET 
                    `opencart_category_id` = '" . (int)$oc_category['category_id'] . "', 
                    `trendyol_category_id` = '" . (int)$best_match['trendyol_category_id'] . "', 
                    `trendyol_category_name` = '" . $this->db->escape($best_match['name']) . "', 
                    `opencart_category_name` = '" . $this->db->escape($oc_category['name']) . "', 
                    `mapping_type` = 'auto',
                    `confidence_score` = '" . (float)$highest_score . "',
                    `created_by` = '" . (int)$this->user->getId() . "',
                    `created_at` = '" . $this->db->escape($now) . "',
                    `updated_at` = '" . $this->db->escape($now) . "'
                ");
                
                $mapped_count++;
            }
        }
        
        return $mapped_count;
    }

    /**
     * İki metin arasındaki benzerlik skorunu hesapla
     */
    private function calculateSimilarityScore($text1, $text2): float {
        $text1 = mb_strtolower(trim($text1));
        $text2 = mb_strtolower(trim($text2));
        
        // Tam eşleşme
        if ($text1 === $text2) {
            return 1.0;
        }
        
        // Levenshtein mesafesi (normalize edilmiş)
        $lev = levenshtein($text1, $text2);
        $max_len = max(mb_strlen($text1), mb_strlen($text2));
        
        if ($max_len === 0) {
            return 0;
        }
        
        return 1 - ($lev / $max_len);
    }
}
