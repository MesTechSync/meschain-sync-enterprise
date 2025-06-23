<?php
/**
 * hepsiburada_category_mapping.php
 *
 * Amaç: OpenCart kategorileri ile Hepsiburada kategorileri arasında eşleştirme sağlamak için gerekli model dosyası.
 * Bu model, kategori eşleştirmelerini yönetir ve veritabanında saklar.
 *
 * @author MesChain Development Team (MUSTI)
 * @version 4.0.0
 * @copyright 2025 MesChain Technologies
 */

class ModelExtensionModuleHepsiburadaCategoryMapping extends Model {
    /**
     * Gerekli veritabanı tablosunu oluşturur
     */
    public function install() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "hepsiburada_category_mapping` (
                `mapping_id` INT(11) NOT NULL AUTO_INCREMENT,
                `opencart_category_id` INT(11) NOT NULL,
                `hepsiburada_category_id` VARCHAR(50) NOT NULL,
                `hepsiburada_category_name` VARCHAR(255) NOT NULL,
                `hepsiburada_category_path` VARCHAR(255) NOT NULL,
                `attributes_required` TEXT NULL,
                `date_added` DATETIME NOT NULL,
                `date_modified` DATETIME NOT NULL,
                PRIMARY KEY (`mapping_id`),
                UNIQUE KEY `opencart_category_id` (`opencart_category_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
        
        $this->log('install', 'Hepsiburada kategori eşleştirme tablosu oluşturuldu');
        
        return true;
    }

    /**
     * Tabloyu siler
     */
    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "hepsiburada_category_mapping`");
        $this->log('uninstall', 'Hepsiburada kategori eşleştirme tablosu silindi');
        
        return true;
    }

    /**
     * Yeni bir kategori eşleştirme ekler
     * 
     * @param array $data Eşleştirme verileri
     * @return int Eşleştirme ID
     */
    public function addCategoryMapping($data) {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "hepsiburada_category_mapping` SET
            `opencart_category_id` = '" . (int)$data['opencart_category_id'] . "',
            `hepsiburada_category_id` = '" . $this->db->escape($data['hepsiburada_category_id']) . "',
            `hepsiburada_category_name` = '" . $this->db->escape($data['hepsiburada_category_name']) . "',
            `hepsiburada_category_path` = '" . $this->db->escape($data['hepsiburada_category_path']) . "',
            `attributes_required` = '" . (isset($data['attributes_required']) ? $this->db->escape(json_encode($data['attributes_required'])) : '') . "',
            `date_added` = NOW(),
            `date_modified` = NOW()
        ");
        
        $mapping_id = $this->db->getLastId();
        
        $this->log('add', 'Yeni kategori eşleştirme eklendi: OpenCart Kategori ID=' . $data['opencart_category_id'] . ', Hepsiburada Kategori ID=' . $data['hepsiburada_category_id']);
        
        return $mapping_id;
    }

    /**
     * Kategori eşleştirmeyi günceller
     * 
     * @param int $mapping_id Eşleştirme ID
     * @param array $data Eşleştirme verileri
     * @return bool Başarılı/Başarısız
     */
    public function editCategoryMapping($mapping_id, $data) {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "hepsiburada_category_mapping` SET
            `opencart_category_id` = '" . (int)$data['opencart_category_id'] . "',
            `hepsiburada_category_id` = '" . $this->db->escape($data['hepsiburada_category_id']) . "',
            `hepsiburada_category_name` = '" . $this->db->escape($data['hepsiburada_category_name']) . "',
            `hepsiburada_category_path` = '" . $this->db->escape($data['hepsiburada_category_path']) . "',
            `attributes_required` = '" . (isset($data['attributes_required']) ? $this->db->escape(json_encode($data['attributes_required'])) : '') . "',
            `date_modified` = NOW()
            WHERE `mapping_id` = '" . (int)$mapping_id . "'
        ");
        
        $this->log('edit', 'Kategori eşleştirme güncellendi: ID=' . $mapping_id);
        
        return true;
    }

    /**
     * Kategori eşleştirmeyi siler
     * 
     * @param int $mapping_id Eşleştirme ID
     * @return bool Başarılı/Başarısız
     */
    public function deleteCategoryMapping($mapping_id) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "hepsiburada_category_mapping` WHERE `mapping_id` = '" . (int)$mapping_id . "'");
        
        $this->log('delete', 'Kategori eşleştirme silindi: ID=' . $mapping_id);
        
        return true;
    }

    /**
     * Belirli bir eşleştirmeyi getirir
     * 
     * @param int $mapping_id Eşleştirme ID
     * @return array Eşleştirme verileri
     */
    public function getCategoryMapping($mapping_id) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "hepsiburada_category_mapping` 
            WHERE `mapping_id` = '" . (int)$mapping_id . "'
        ");
        
        $mapping = $query->row;
        
        if (isset($mapping['attributes_required']) && !empty($mapping['attributes_required'])) {
            $mapping['attributes_required'] = json_decode($mapping['attributes_required'], true);
        } else {
            $mapping['attributes_required'] = array();
        }
        
        return $mapping;
    }

    /**
     * OpenCart kategori ID'sine göre eşleştirmeyi getirir
     * 
     * @param int $opencart_category_id OpenCart Kategori ID
     * @return array Eşleştirme verileri
     */
    public function getCategoryMappingByOpenCartCategoryId($opencart_category_id) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "hepsiburada_category_mapping` 
            WHERE `opencart_category_id` = '" . (int)$opencart_category_id . "'
        ");
        
        $mapping = $query->row;
        
        if (isset($mapping['attributes_required']) && !empty($mapping['attributes_required'])) {
            $mapping['attributes_required'] = json_decode($mapping['attributes_required'], true);
        } else {
            $mapping['attributes_required'] = array();
        }
        
        return $mapping;
    }

    /**
     * Hepsiburada kategori ID'sine göre eşleştirmeyi getirir
     * 
     * @param string $hepsiburada_category_id Hepsiburada Kategori ID
     * @return array Eşleştirme verileri
     */
    public function getCategoryMappingByHepsiburadaCategoryId($hepsiburada_category_id) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "hepsiburada_category_mapping` 
            WHERE `hepsiburada_category_id` = '" . $this->db->escape($hepsiburada_category_id) . "'
        ");
        
        $mapping = $query->row;
        
        if (isset($mapping['attributes_required']) && !empty($mapping['attributes_required'])) {
            $mapping['attributes_required'] = json_decode($mapping['attributes_required'], true);
        } else {
            $mapping['attributes_required'] = array();
        }
        
        return $mapping;
    }

    /**
     * Tüm kategori eşleştirmelerini getirir
     * 
     * @param array $data Filtreleme ve sıralama parametreleri
     * @return array Eşleştirme listesi
     */
    public function getCategoryMappings($data = array()) {
        $sql = "
            SELECT hcm.*, c.name as category_name, c.path as category_path
            FROM `" . DB_PREFIX . "hepsiburada_category_mapping` hcm
            LEFT JOIN `" . DB_PREFIX . "category_description` c ON (hcm.opencart_category_id = c.category_id)
            WHERE c.language_id = '" . (int)$this->config->get('config_language_id') . "'
        ";
        
        if (!empty($data['filter_opencart_category'])) {
            $sql .= " AND c.name LIKE '%" . $this->db->escape($data['filter_opencart_category']) . "%'";
        }
        
        if (!empty($data['filter_hepsiburada_category'])) {
            $sql .= " AND hcm.hepsiburada_category_name LIKE '%" . $this->db->escape($data['filter_hepsiburada_category']) . "%'";
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
     * Eşleştirme sayısını getirir
     * 
     * @param array $data Filtreleme parametreleri
     * @return int Eşleştirme sayısı
     */
    public function getTotalCategoryMappings($data = array()) {
        $sql = "
            SELECT COUNT(*) AS total
            FROM `" . DB_PREFIX . "hepsiburada_category_mapping` hcm
            LEFT JOIN `" . DB_PREFIX . "category_description` c ON (hcm.opencart_category_id = c.category_id)
            WHERE c.language_id = '" . (int)$this->config->get('config_language_id') . "'
        ";
        
        if (!empty($data['filter_opencart_category'])) {
            $sql .= " AND c.name LIKE '%" . $this->db->escape($data['filter_opencart_category']) . "%'";
        }
        
        if (!empty($data['filter_hepsiburada_category'])) {
            $sql .= " AND hcm.hepsiburada_category_name LIKE '%" . $this->db->escape($data['filter_hepsiburada_category']) . "%'";
        }
        
        $query = $this->db->query($sql);
        
        return $query->row['total'];
    }

    /**
     * Aktivite kaydı tutar
     * 
     * @param string $action İşlem
     * @param string $message Mesaj
     * @return void
     */
    private function log($action, $message) {
        if (defined('DIR_LOGS')) {
            $log = new Log('hepsiburada_category.log');
            $log->write('[' . date('Y-m-d H:i:s') . '] [' . $action . '] ' . $message);
        }
    }
}
