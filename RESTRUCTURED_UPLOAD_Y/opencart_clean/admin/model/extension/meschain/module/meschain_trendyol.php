<?php
namespace Opencart\Admin\Model\Extension\Meschain\Module;

/**
 * MesChain Trendyol Main Module Model
 * 
 * @author MesChain Development Team
 * @version 1.0.0
 */
class MeschainTrendyol extends \Opencart\System\Engine\Model {
    
    public function install() {
        // Installation logic here
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_trendyol_settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `key` varchar(255) NOT NULL,
            `value` text,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `key` (`key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }
    
    public function uninstall() {
        // Uninstallation logic here
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_trendyol_settings`");
    }
    
    public function editSetting($code, $data, $store_id = 0) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '" . (int)$store_id . "' AND `code` = '" . $this->db->escape($code) . "'");
        
        foreach ($data as $key => $value) {
            if (substr($key, 0, strlen($code)) == $code) {
                if (!is_array($value)) {
                    $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET store_id = '" . (int)$store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
                } else {
                    $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET store_id = '" . (int)$store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(json_encode($value, true)) . "', serialized = '1'");
                }
            }
        }
    }
}
?>