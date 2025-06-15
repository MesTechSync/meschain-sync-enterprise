<?php
/**
 * n11_category_mapping.php
 *
 * Amaç: OpenCart kategorileri ile N11 kategorileri arasında eşleştirme sağlamak için gerekli model dosyası.
 * Bu model, kategori eşleştirmelerini yönetir ve veritabanında saklar.
 *
 * Loglama: Önemli işlemler n11_category.log dosyasına kaydedilir.
 * Log formatı: [YYYY-MM-DD HH:MM:SS] [İŞLEM] [AÇIKLAMA]
 */

class ModelExtensionModuleN11CategoryMapping extends Model {
    /**
     * Gerekli veritabanı tablosunu oluşturur
     */
    public function install() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "n11_category_mapping` (
                `mapping_id` INT(11) NOT NULL AUTO_INCREMENT,
                `opencart_category_id` INT(11) NOT NULL,
                `n11_category_id` VARCHAR(50) NOT NULL,
                `n11_category_name` VARCHAR(255) NOT NULL,
                `n11_category_path` VARCHAR(255) NOT NULL,
                `attributes_required` TEXT NULL,
                `date_added` DATETIME NOT NULL,
                `date_modified` DATETIME NOT NULL,
                PRIMARY KEY (`mapping_id`),
                UNIQUE KEY `opencart_category_id` (`opencart_category_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
        
        $this->log('install', 'N11 kategori eşleştirme tablosu oluşturuldu');
        
        return true;
    }

    /**
     * Tabloyu siler
     */
    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "n11_category_mapping`");
        $this->log('uninstall', 'N11 kategori eşleştirme tablosu silindi');
        
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
            INSERT INTO `" . DB_PREFIX . "n11_category_mapping` SET
            `opencart_category_id` = '" . (int)$data['opencart_category_id'] . "',
            `n11_category_id` = '" . $this->db->escape($data['n11_category_id']) . "',
            `n11_category_name` = '" . $this->db->escape($data['n11_category_name']) . "',
            `n11_category_path` = '" . $this->db->escape($data['n11_category_path']) . "',
            `attributes_required` = '" . (isset($data['attributes_required']) ? $this->db->escape(json_encode($data['attributes_required'])) : '') . "',
            `date_added` = NOW(),
            `date_modified` = NOW()
        ");
        
        $mapping_id = $this->db->getLastId();
        
        $this->log('add', 'Yeni kategori eşleştirme eklendi: OpenCart Kategori ID=' . $data['opencart_category_id'] . ', N11 Kategori ID=' . $data['n11_category_id']);
        
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
            UPDATE `" . DB_PREFIX . "n11_category_mapping` SET
            `opencart_category_id` = '" . (int)$data['opencart_category_id'] . "',
            `n11_category_id` = '" . $this->db->escape($data['n11_category_id']) . "',
            `n11_category_name` = '" . $this->db->escape($data['n11_category_name']) . "',
            `n11_category_path` = '" . $this->db->escape($data['n11_category_path']) . "',
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
        $this->db->query("DELETE FROM `" . DB_PREFIX . "n11_category_mapping` WHERE `mapping_id` = '" . (int)$mapping_id . "'");
        
        $this->log('delete', 'Kategori eşleştirme silindi: ID=' . $mapping_id);
        
        return true;
    }

    /**
     * OpenCart kategori ID'sine göre N11 kategori eşleştirmesini getirir
     * 
     * @param int $opencart_category_id OpenCart kategori ID
     * @return array|bool Eşleştirme bilgileri veya false
     */
    public function getCategoryMappingByOpenCartId($opencart_category_id) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "n11_category_mapping`
            WHERE `opencart_category_id` = '" . (int)$opencart_category_id . "'
        ");
        
        if ($query->num_rows) {
            $result = $query->row;
            
            if (isset($result['attributes_required']) && !empty($result['attributes_required'])) {
                $result['attributes_required'] = json_decode($result['attributes_required'], true);
            } else {
                $result['attributes_required'] = array();
            }
            
            return $result;
        } else {
            return false;
        }
    }

    /**
     * N11 kategori ID'sine göre eşleştirmeyi getirir
     * 
     * @param string $n11_category_id N11 kategori ID
     * @return array|bool Eşleştirme bilgileri veya false
     */
    public function getCategoryMappingByN11Id($n11_category_id) {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "n11_category_mapping`
            WHERE `n11_category_id` = '" . $this->db->escape($n11_category_id) . "'
        ");
        
        if ($query->num_rows) {
            $result = $query->row;
            
            if (isset($result['attributes_required']) && !empty($result['attributes_required'])) {
                $result['attributes_required'] = json_decode($result['attributes_required'], true);
            } else {
                $result['attributes_required'] = array();
            }
            
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Tüm kategori eşleştirmelerini getirir
     * 
     * @param array $data Filtre ve sıralama seçenekleri
     * @return array Eşleştirme listesi
     */
    public function getCategoryMappings($data = array()) {
        $sql = "SELECT m.*, c.name AS opencart_category_name
                FROM `" . DB_PREFIX . "n11_category_mapping` m
                LEFT JOIN `" . DB_PREFIX . "category_description` c 
                ON (m.opencart_category_id = c.category_id AND c.language_id = '" . (int)$this->config->get('config_language_id') . "')";
        
        if (!empty($data['filter_opencart_name'])) {
            $sql .= " AND c.name LIKE '%" . $this->db->escape($data['filter_opencart_name']) . "%'";
        }
        
        if (!empty($data['filter_n11_name'])) {
            $sql .= " AND m.n11_category_name LIKE '%" . $this->db->escape($data['filter_n11_name']) . "%'";
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
        
        $mappings = array();
        
        foreach ($query->rows as $row) {
            if (isset($row['attributes_required']) && !empty($row['attributes_required'])) {
                $row['attributes_required'] = json_decode($row['attributes_required'], true);
            } else {
                $row['attributes_required'] = array();
            }
            
            $mappings[] = $row;
        }
        
        return $mappings;
    }

    /**
     * Toplam kategori eşleştirme sayısını getirir
     * 
     * @param array $data Filtre seçenekleri
     * @return int Toplam sayı
     */
    public function getTotalCategoryMappings($data = array()) {
        $sql = "SELECT COUNT(*) AS total 
                FROM `" . DB_PREFIX . "n11_category_mapping` m
                LEFT JOIN `" . DB_PREFIX . "category_description` c 
                ON (m.opencart_category_id = c.category_id AND c.language_id = '" . (int)$this->config->get('config_language_id') . "')";
        
        if (!empty($data['filter_opencart_name'])) {
            $sql .= " AND c.name LIKE '%" . $this->db->escape($data['filter_opencart_name']) . "%'";
        }
        
        if (!empty($data['filter_n11_name'])) {
            $sql .= " AND m.n11_category_name LIKE '%" . $this->db->escape($data['filter_n11_name']) . "%'";
        }
        
        $query = $this->db->query($sql);
        
        return $query->row['total'];
    }

    /**
     * Kategori eşleştirme için gerekli nitelikleri kaydeder
     * 
     * @param int $mapping_id Eşleştirme ID
     * @param array $attributes Nitelikler
     * @return bool Başarılı/Başarısız
     */
    public function saveAttributeRequirements($mapping_id, $attributes) {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "n11_category_mapping` SET
            `attributes_required` = '" . $this->db->escape(json_encode($attributes)) . "',
            `date_modified` = NOW()
            WHERE `mapping_id` = '" . (int)$mapping_id . "'
        ");
        
        $this->log('attributes', 'Kategori nitelik gereksinimleri güncellendi: ID=' . $mapping_id);
        
        return true;
    }

    /**
     * Log kaydı oluşturur
     * 
     * @param string $action İşlem türü
     * @param string $message Mesaj
     */
    private function log($action, $message) {
        $log = new Log('n11_category.log');
        $log->write('[' . $action . '] ' . $message);
    }
} 