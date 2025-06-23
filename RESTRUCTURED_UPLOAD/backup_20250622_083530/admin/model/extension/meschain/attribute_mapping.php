<?php
namespace Opencart\Admin\Model\Extension\Meschain;

/**
 * Trendyol Attribute Mapping Model
 * V2 Design Implementation
 * 
 * @author Meschain Development Team
 * @version 2.0.0
 */
class AttributeMapping extends \Opencart\System\Engine\Model {
    
    /**
     * Özellik eşleştirmeleri tablosunu oluştur
     */
    public function createTable(): void {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_attribute_mapping` (
                `mapping_id` int(11) NOT NULL AUTO_INCREMENT,
                `trendyol_category_id` int(11) NOT NULL,
                `opencart_attribute_id` int(11) NOT NULL,
                `trendyol_attribute_id` int(11) NOT NULL,
                `trendyol_attribute_name` varchar(255) NOT NULL,
                `opencart_attribute_name` varchar(255) NOT NULL,
                `mapping_type` enum('manual','auto','ai_suggested') DEFAULT 'manual',
                `confidence_score` decimal(3,2) DEFAULT NULL,
                `is_active` tinyint(1) DEFAULT 1,
                `value_mapping` text DEFAULT NULL COMMENT 'JSON of mapped values',
                `created_by` int(11) DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`mapping_id`),
                UNIQUE KEY `category_attribute_unique` (`trendyol_category_id`,`opencart_attribute_id`),
                KEY `trendyol_attribute_id` (`trendyol_attribute_id`),
                KEY `opencart_attribute_id` (`opencart_attribute_id`),
                KEY `mapping_type` (`mapping_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "trendyol_category_attribute` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `trendyol_category_id` int(11) NOT NULL,
                `trendyol_attribute_id` int(11) NOT NULL,
                `name` varchar(255) NOT NULL,
                `required` tinyint(1) DEFAULT 0,
                `varianter` tinyint(1) DEFAULT 0,
                `allowCustom` tinyint(1) DEFAULT 0,
                `attribute_values` text DEFAULT NULL COMMENT 'JSON array of allowed values',
                `attribute_type` varchar(50) DEFAULT NULL,
                `last_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `category_attribute_unique` (`trendyol_category_id`,`trendyol_attribute_id`),
                KEY `trendyol_category_id` (`trendyol_category_id`),
                KEY `required` (`required`),
                KEY `varianter` (`varianter`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    /**
     * Trendyol kategori özelliklerini kaydet
     */
    public function saveTrendyolCategoryAttributes($category_id, $attributes_data): void {
        if (!isset($attributes_data['categoryAttributes']) || !is_array($attributes_data['categoryAttributes'])) {
            throw new \Exception('Invalid category attributes data format');
        }

        // Önce bu kategori için olan özellikleri temizle
        $this->db->query("DELETE FROM `" . DB_PREFIX . "trendyol_category_attribute` WHERE trendyol_category_id = '" . (int)$category_id . "'");
        
        // Ardından yeni özellikleri ekle
        foreach ($attributes_data['categoryAttributes'] as $attribute) {
            $values_json = isset($attribute['attributeValues']) ? json_encode($attribute['attributeValues']) : null;
            
            $this->db->query("INSERT INTO `" . DB_PREFIX . "trendyol_category_attribute` SET 
                `trendyol_category_id` = '" . (int)$category_id . "', 
                `trendyol_attribute_id` = '" . (int)$attribute['id'] . "', 
                `name` = '" . $this->db->escape($attribute['name']) . "',
                `required` = '" . (isset($attribute['required']) && $attribute['required'] ? 1 : 0) . "',
                `varianter` = '" . (isset($attribute['varianter']) && $attribute['varianter'] ? 1 : 0) . "',
                `allowCustom` = '" . (isset($attribute['allowCustom']) && $attribute['allowCustom'] ? 1 : 0) . "',
                `attribute_values` = " . ($values_json ? "'" . $this->db->escape($values_json) . "'" : "NULL") . ",
                `attribute_type` = " . (isset($attribute['attributeType']) ? "'" . $this->db->escape($attribute['attributeType']) . "'" : "NULL") . "
            ");
        }
    }

    /**
     * Tüm özellik eşleştirmelerini getir
     */
    public function getAttributeMappings($category_id): array {
        $query = $this->db->query("
            SELECT m.*, oca.name as opencart_name, ag.name as attribute_group_name, 
                   tca.name as trendyol_name, tca.required, tca.varianter, 
                   tca.attribute_values, tca.attribute_type 
            FROM `" . DB_PREFIX . "trendyol_attribute_mapping` m 
            LEFT JOIN `" . DB_PREFIX . "attribute` oca ON (m.opencart_attribute_id = oca.attribute_id) 
            LEFT JOIN `" . DB_PREFIX . "attribute_description` ocad ON (oca.attribute_id = ocad.attribute_id) 
            LEFT JOIN `" . DB_PREFIX . "attribute_group` ag ON (oca.attribute_group_id = ag.attribute_group_id) 
            LEFT JOIN `" . DB_PREFIX . "trendyol_category_attribute` tca ON (m.trendyol_attribute_id = tca.trendyol_attribute_id AND m.trendyol_category_id = tca.trendyol_category_id)
            WHERE m.trendyol_category_id = '" . (int)$category_id . "'
            AND ocad.language_id = '" . (int)$this->config->get('config_language_id') . "'
            ORDER BY ag.name ASC, ocad.name ASC
        ");
        
        return $query->rows;
    }

    /**
     * Özellik eşleştirme kaydını getir
     */
    public function getMappingByAttributeId($category_id, $attribute_id): array {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "trendyol_attribute_mapping` 
            WHERE trendyol_category_id = '" . (int)$category_id . "'
            AND opencart_attribute_id = '" . (int)$attribute_id . "'
        ");
        
        return $query->row ?? [];
    }

    /**
     * Trendyol kategori özelliklerini getir
     */
    public function getTrendyolAttributesByCategory($category_id): array {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "trendyol_category_attribute` 
            WHERE trendyol_category_id = '" . (int)$category_id . "'
            ORDER BY name ASC
        ");
        
        $attributes = $query->rows;
        
        // Attribute values JSON'ları çöz
        foreach ($attributes as &$attribute) {
            if (!empty($attribute['attribute_values'])) {
                $attribute['attribute_values_array'] = json_decode($attribute['attribute_values'], true);
            } else {
                $attribute['attribute_values_array'] = [];
            }
        }
        
        return $attributes;
    }

    /**
     * Özellik eşleştirmelerini kaydet
     */
    public function saveAttributeMapping(array $data): void {
        if (!isset($data['attribute_mapping']) || !is_array($data['attribute_mapping']) || !isset($data['category_id'])) {
            return;
        }
        
        $category_id = (int)$data['category_id'];

        foreach ($data['attribute_mapping'] as $opencart_attribute_id => $trendyol_attribute_id) {
            if (empty($trendyol_attribute_id)) {
                continue;
            }
            
            // OpenCart özellik adını al
            $attribute_query = $this->db->query("
                SELECT ad.name, a.attribute_group_id 
                FROM `" . DB_PREFIX . "attribute` a 
                LEFT JOIN `" . DB_PREFIX . "attribute_description` ad ON (a.attribute_id = ad.attribute_id)
                WHERE a.attribute_id = '" . (int)$opencart_attribute_id . "'
                AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "'
            ");
            
            $opencart_name = $attribute_query->row['name'] ?? '';
            
            // Trendyol özellik adını al
            $trendyol_query = $this->db->query("
                SELECT name FROM `" . DB_PREFIX . "trendyol_category_attribute` 
                WHERE trendyol_category_id = '" . $category_id . "'
                AND trendyol_attribute_id = '" . (int)$trendyol_attribute_id . "'
            ");
            
            $trendyol_name = $trendyol_query->row['name'] ?? '';
            
            // Var olan eşleştirmeyi kontrol et
            $existing_query = $this->db->query("
                SELECT mapping_id FROM `" . DB_PREFIX . "trendyol_attribute_mapping` 
                WHERE trendyol_category_id = '" . $category_id . "'
                AND opencart_attribute_id = '" . (int)$opencart_attribute_id . "'
            ");
            
            $now = date('Y-m-d H:i:s');
            
            if ($existing_query->num_rows) {
                // Güncelle
                $this->db->query("
                    UPDATE `" . DB_PREFIX . "trendyol_attribute_mapping` SET 
                    `trendyol_attribute_id` = '" . (int)$trendyol_attribute_id . "', 
                    `trendyol_attribute_name` = '" . $this->db->escape($trendyol_name) . "', 
                    `opencart_attribute_name` = '" . $this->db->escape($opencart_name) . "', 
                    `mapping_type` = 'manual',
                    `updated_at` = '" . $this->db->escape($now) . "' 
                    WHERE trendyol_category_id = '" . $category_id . "'
                    AND opencart_attribute_id = '" . (int)$opencart_attribute_id . "'
                ");
            } else {
                // Yeni ekle
                $this->db->query("
                    INSERT INTO `" . DB_PREFIX . "trendyol_attribute_mapping` SET 
                    `trendyol_category_id` = '" . $category_id . "',
                    `opencart_attribute_id` = '" . (int)$opencart_attribute_id . "', 
                    `trendyol_attribute_id` = '" . (int)$trendyol_attribute_id . "', 
                    `trendyol_attribute_name` = '" . $this->db->escape($trendyol_name) . "', 
                    `opencart_attribute_name` = '" . $this->db->escape($opencart_name) . "', 
                    `mapping_type` = 'manual',
                    `created_by` = '" . (int)$this->user->getId() . "',
                    `created_at` = '" . $this->db->escape($now) . "',
                    `updated_at` = '" . $this->db->escape($now) . "'
                ");
            }
        }
    }

    /**
     * Değer eşleştirmelerini güncelle
     */
    public function updateValueMapping($mapping_id, $value_mapping): void {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "trendyol_attribute_mapping` SET 
            `value_mapping` = '" . $this->db->escape(json_encode($value_mapping)) . "',
            `updated_at` = NOW()
            WHERE mapping_id = '" . (int)$mapping_id . "'
        ");
    }

    /**
     * Otomatik özellik eşleştirme
     */
    public function autoMapAttributes($category_id): int {
        // OpenCart özelliklerini al
        $opencart_attributes_query = $this->db->query("
            SELECT a.attribute_id, ad.name, ag.name as group_name
            FROM `" . DB_PREFIX . "attribute` a 
            LEFT JOIN `" . DB_PREFIX . "attribute_description` ad ON (a.attribute_id = ad.attribute_id)
            LEFT JOIN `" . DB_PREFIX . "attribute_group` ag ON (a.attribute_group_id = ag.attribute_group_id)
            WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "'
            ORDER BY ad.name
        ");
        
        $opencart_attributes = $opencart_attributes_query->rows;
        
        // Trendyol kategori özelliklerini al
        $trendyol_attributes_query = $this->db->query("
            SELECT trendyol_attribute_id, name, required, varianter  
            FROM `" . DB_PREFIX . "trendyol_category_attribute` 
            WHERE trendyol_category_id = '" . (int)$category_id . "'
            ORDER BY name
        ");
        
        $trendyol_attributes = $trendyol_attributes_query->rows;
        
        $mapped_count = 0;
        $now = date('Y-m-d H:i:s');
        
        foreach ($opencart_attributes as $oc_attribute) {
            // Bu özellik zaten eşleştirilmiş mi kontrol et
            $existing_query = $this->db->query("
                SELECT mapping_id FROM `" . DB_PREFIX . "trendyol_attribute_mapping` 
                WHERE trendyol_category_id = '" . (int)$category_id . "'
                AND opencart_attribute_id = '" . (int)$oc_attribute['attribute_id'] . "'
            ");
            
            if ($existing_query->num_rows) {
                continue; // Zaten eşleştirilmiş, atlayalım
            }
            
            // İsim benzerliği için en iyi eşleşmeyi bul
            $best_match = null;
            $highest_score = 0;
            
            $oc_full_name = $oc_attribute['group_name'] . ' > ' . $oc_attribute['name'];
            
            foreach ($trendyol_attributes as $tr_attribute) {
                // İsim benzerliği skoru hesapla
                $score = $this->calculateSimilarityScore($oc_attribute['name'], $tr_attribute['name']);
                // Grup ismi de içeriyorsa bonus puan
                $group_score = $this->calculateSimilarityScore($oc_full_name, $tr_attribute['name']);
                
                $combined_score = max($score, $group_score);
                
                if ($combined_score > $highest_score && $combined_score > 0.65) { // 0.65 eşik değeri
                    $highest_score = $combined_score;
                    $best_match = $tr_attribute;
                }
            }
            
            // Yeterli benzerlik skoru ile eşleşme bulunduysa kaydet
            if ($best_match) {
                $this->db->query("
                    INSERT INTO `" . DB_PREFIX . "trendyol_attribute_mapping` SET 
                    `trendyol_category_id` = '" . (int)$category_id . "',
                    `opencart_attribute_id` = '" . (int)$oc_attribute['attribute_id'] . "', 
                    `trendyol_attribute_id` = '" . (int)$best_match['trendyol_attribute_id'] . "', 
                    `trendyol_attribute_name` = '" . $this->db->escape($best_match['name']) . "', 
                    `opencart_attribute_name` = '" . $this->db->escape($oc_attribute['name']) . "', 
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
