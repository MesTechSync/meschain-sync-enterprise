<?php
namespace Opencart\Admin\Model\Extension\Meschain;

/**
 * Trendyol Brand Mapping Model
 * V2 Design Implementation
 * 
 * @author Meschain Development Team
 * @version 2.0.0
 */
class BrandMapping extends \Opencart\System\Engine\Model {
    
    /**
     * Marka eşleştirmeleri tablosunu oluştur
     */
    public function createTable(): void {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_brand_mapping` (
                `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
                `opencart_manufacturer_id` int(11) NOT NULL,
                `trendyol_brand_id` int(11) NOT NULL,
                `trendyol_brand_name` varchar(255) NOT NULL,
                `opencart_brand_name` varchar(255) NOT NULL,
                `mapping_type` enum('manual','auto','ai_suggested') DEFAULT 'manual',
                `confidence_score` decimal(3,2) DEFAULT NULL,
                `is_active` tinyint(1) DEFAULT 1,
                `created_by` int(11) DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`mapping_id`),
                UNIQUE KEY `opencart_manufacturer_id` (`opencart_manufacturer_id`),
                KEY `trendyol_brand_id` (`trendyol_brand_id`),
                KEY `mapping_type` (`mapping_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_brand` (
                `trendyol_brand_id` int(11) NOT NULL,
                `name` varchar(255) NOT NULL,
                `is_approved` tinyint(1) DEFAULT 0,
                `last_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`trendyol_brand_id`),
                KEY `name` (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    /**
     * Trendyol markalarını kaydet
     */
    public function saveTrendyolBrands($brands_data): void {
        if (!isset($brands_data['brands']) || !is_array($brands_data['brands'])) {
            throw new \Exception('Invalid brands data format');
        }

        // Önce eski marka tablosunu temizle
        $this->db->query("TRUNCATE TABLE `" . DB_PREFIX . "trendyol_brand`");
        
        // Ardından yeni markaları ekle
        foreach ($brands_data['brands'] as $brand) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "trendyol_brand` SET 
                `trendyol_brand_id` = '" . (int)$brand['id'] . "', 
                `name` = '" . $this->db->escape($brand['name']) . "',
                `is_approved` = '" . (isset($brand['isApproved']) ? (int)$brand['isApproved'] : 0) . "'
            ");
        }
    }

    /**
     * Tüm marka eşleştirmelerini getir
     */
    public function getBrandMappings(): array {
        $query = $this->db->query("
            SELECT m.*, oc.name as opencart_name, tb.name as trendyol_name
            FROM `" . DB_PREFIX . "trendyol_brand_mapping` m 
            LEFT JOIN `" . DB_PREFIX . "manufacturer` oc ON (m.opencart_manufacturer_id = oc.manufacturer_id) 
            LEFT JOIN `" . DB_PREFIX . "trendyol_brand` tb ON (m.trendyol_brand_id = tb.trendyol_brand_id) 
            ORDER BY oc.name ASC
        ");
        
        return $query->rows;
    }

    /**
     * Marka eşleştirme kaydını getir
     */
    public function getMappingByOpenCartManufacturerId($manufacturer_id): array {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "trendyol_brand_mapping` 
            WHERE opencart_manufacturer_id = '" . (int)$manufacturer_id . "'
        ");
        
        return $query->row ?? [];
    }

    /**
     * Trendyol Markalarını getir
     */
    public function getTrendyolBrands(): array {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "trendyol_brand` 
            ORDER BY name ASC
        ");
        
        return $query->rows;
    }

    /**
     * Marka eşleştirmelerini kaydet
     */
    public function saveBrandMapping(array $data): void {
        if (!isset($data['brand_mapping']) || !is_array($data['brand_mapping'])) {
            return;
        }

        foreach ($data['brand_mapping'] as $opencart_manufacturer_id => $trendyol_brand_id) {
            if (empty($trendyol_brand_id)) {
                continue;
            }
            
            // OpenCart marka adını al
            $brand_query = $this->db->query("
                SELECT name FROM `" . DB_PREFIX . "manufacturer` 
                WHERE manufacturer_id = '" . (int)$opencart_manufacturer_id . "'
            ");
            
            $opencart_name = $brand_query->row['name'] ?? '';
            
            // Trendyol marka adını al
            $trendyol_query = $this->db->query("
                SELECT name FROM `" . DB_PREFIX . "trendyol_brand` 
                WHERE trendyol_brand_id = '" . (int)$trendyol_brand_id . "'
            ");
            
            $trendyol_name = $trendyol_query->row['name'] ?? '';
            
            // Var olan eşleştirmeyi kontrol et
            $existing_query = $this->db->query("
                SELECT mapping_id FROM `" . DB_PREFIX . "trendyol_brand_mapping` 
                WHERE opencart_manufacturer_id = '" . (int)$opencart_manufacturer_id . "'
            ");
            
            $now = date('Y-m-d H:i:s');
            
            if ($existing_query->num_rows) {
                // Güncelle
                $this->db->query("
                    UPDATE `" . DB_PREFIX . "trendyol_brand_mapping` SET 
                    `trendyol_brand_id` = '" . (int)$trendyol_brand_id . "', 
                    `trendyol_brand_name` = '" . $this->db->escape($trendyol_name) . "', 
                    `opencart_brand_name` = '" . $this->db->escape($opencart_name) . "', 
                    `mapping_type` = 'manual',
                    `updated_at` = '" . $this->db->escape($now) . "' 
                    WHERE opencart_manufacturer_id = '" . (int)$opencart_manufacturer_id . "'
                ");
            } else {
                // Yeni ekle
                $this->db->query("
                    INSERT INTO `" . DB_PREFIX . "trendyol_brand_mapping` SET 
                    `opencart_manufacturer_id` = '" . (int)$opencart_manufacturer_id . "', 
                    `trendyol_brand_id` = '" . (int)$trendyol_brand_id . "', 
                    `trendyol_brand_name` = '" . $this->db->escape($trendyol_name) . "', 
                    `opencart_brand_name` = '" . $this->db->escape($opencart_name) . "', 
                    `mapping_type` = 'manual',
                    `created_by` = '" . (int)$this->user->getId() . "',
                    `created_at` = '" . $this->db->escape($now) . "',
                    `updated_at` = '" . $this->db->escape($now) . "'
                ");
            }
        }
    }

    /**
     * Otomatik marka eşleştirme
     */
    public function autoMapBrands(): int {
        // OpenCart markalarını al
        $opencart_brands_query = $this->db->query("
            SELECT manufacturer_id, name 
            FROM `" . DB_PREFIX . "manufacturer` 
            ORDER BY name
        ");
        
        $opencart_brands = $opencart_brands_query->rows;
        
        // Trendyol markalarını al
        $trendyol_brands_query = $this->db->query("
            SELECT trendyol_brand_id, name 
            FROM `" . DB_PREFIX . "trendyol_brand` 
            ORDER BY name
        ");
        
        $trendyol_brands = $trendyol_brands_query->rows;
        
        $mapped_count = 0;
        $now = date('Y-m-d H:i:s');
        
        foreach ($opencart_brands as $oc_brand) {
            // Bu marka zaten eşleştirilmiş mi kontrol et
            $existing_query = $this->db->query("
                SELECT mapping_id FROM `" . DB_PREFIX . "trendyol_brand_mapping` 
                WHERE opencart_manufacturer_id = '" . (int)$oc_brand['manufacturer_id'] . "'
            ");
            
            if ($existing_query->num_rows) {
                continue; // Zaten eşleştirilmiş, atlayalım
            }
            
            // İsim benzerliği için en iyi eşleşmeyi bul
            $best_match = null;
            $highest_score = 0;
            
            foreach ($trendyol_brands as $tr_brand) {
                // İsim benzerliği skoru hesapla
                $score = $this->calculateSimilarityScore($oc_brand['name'], $tr_brand['name']);
                
                if ($score > $highest_score && $score > 0.7) { // 0.7 eşik değeri (markalar için daha yüksek hassasiyet)
                    $highest_score = $score;
                    $best_match = $tr_brand;
                }
            }
            
            // Yeterli benzerlik skoru ile eşleşme bulunduysa kaydet
            if ($best_match) {
                $this->db->query("
                    INSERT INTO `" . DB_PREFIX . "trendyol_brand_mapping` SET 
                    `opencart_manufacturer_id` = '" . (int)$oc_brand['manufacturer_id'] . "', 
                    `trendyol_brand_id` = '" . (int)$best_match['trendyol_brand_id'] . "', 
                    `trendyol_brand_name` = '" . $this->db->escape($best_match['name']) . "', 
                    `opencart_brand_name` = '" . $this->db->escape($oc_brand['name']) . "', 
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
