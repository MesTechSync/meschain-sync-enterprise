<?php
namespace Opencart\Admin\Model\Extension\Module;

class MeschainSync extends \Opencart\System\Engine\Model {
    public function install(): void {
        // Ana tablolar
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_marketplace` (
            `marketplace_id` INT(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(64) NOT NULL,
            `code` VARCHAR(32) NOT NULL,
            `settings` TEXT NOT NULL,
            `status` TINYINT(1) NOT NULL DEFAULT '0',
            `sort_order` INT(3) NOT NULL DEFAULT '0',
            `date_added` DATETIME NOT NULL,
            `date_modified` DATETIME NOT NULL,
            PRIMARY KEY (`marketplace_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_product` (
            `meschain_product_id` INT(11) NOT NULL AUTO_INCREMENT,
            `product_id` INT(11) NOT NULL,
            `marketplace_id` INT(11) NOT NULL,
            `marketplace_product_id` VARCHAR(128) NOT NULL,
            `status` TINYINT(1) NOT NULL DEFAULT '0',
            `price` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
            `quantity` INT(4) NOT NULL DEFAULT '0',
            `profit_margin` DECIMAL(5,2) NOT NULL DEFAULT '10.00',
            `sync_status` VARCHAR(32) NOT NULL,
            `last_sync` DATETIME NOT NULL,
            `date_added` DATETIME NOT NULL,
            `date_modified` DATETIME NOT NULL,
            PRIMARY KEY (`meschain_product_id`),
            KEY `product_id` (`product_id`),
            KEY `marketplace_id` (`marketplace_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

        // Varsayılan pazaryerleri
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_marketplace` 
            (`name`, `code`, `settings`, `status`, `sort_order`, `date_added`, `date_modified`) VALUES
            ('Amazon', 'amazon', '{\"api_key\":\"\",\"api_secret\":\"\",\"region\":\"tr\"}', 0, 1, NOW(), NOW()),
            ('Trendyol', 'trendyol', '{\"api_key\":\"\",\"api_secret\":\"\"}', 0, 2, NOW(), NOW()),
            ('N11', 'n11', '{\"api_key\":\"\",\"api_secret\":\"\"}', 0, 3, NOW(), NOW()),
            ('Hepsiburada', 'hepsiburada', '{\"api_key\":\"\",\"api_secret\":\"\"}', 0, 4, NOW(), NOW())");
        // Add missing tables for complete functionality
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_order` (
            `meschain_order_id` INT(11) NOT NULL AUTO_INCREMENT,
            `order_id` INT(11) NOT NULL,
            `marketplace_id` INT(11) NOT NULL,
            `marketplace_order_id` VARCHAR(128) NOT NULL,
            `status` VARCHAR(32) NOT NULL,
            `total` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
            `commission` DECIMAL(15,4) NOT NULL DEFAULT '0.0000',
            `currency_code` VARCHAR(3) NOT NULL,
            `date_added` DATETIME NOT NULL,
            `date_modified` DATETIME NOT NULL,
            PRIMARY KEY (`meschain_order_id`),
            KEY `order_id` (`order_id`),
            KEY `marketplace_id` (`marketplace_id`),
            KEY `marketplace_order_id` (`marketplace_order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_log` (
            `log_id` INT(11) NOT NULL AUTO_INCREMENT,
            `type` VARCHAR(32) NOT NULL,
            `reference_id` INT(11) NOT NULL,
            `message` TEXT NOT NULL,
            `date_added` DATETIME NOT NULL,
            PRIMARY KEY (`log_id`),
            KEY `type` (`type`),
            KEY `reference_id` (`reference_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
    }

    public function uninstall(): void {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_marketplace`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_product`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_order`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "meschain_log`");
    }

    public function getMarketplaces(): array {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_marketplace` ORDER BY sort_order ASC");
        return $query->rows;
    }

    public function getProductStatus(int $product_id): int {
        $query = $this->db->query("SELECT status FROM `" . DB_PREFIX . "meschain_product` WHERE product_id = '" . (int)$product_id . "' LIMIT 1");
        return $query->num_rows ? (int)$query->row['status'] : 0;
    }

    public function getProductProfitMargin(int $product_id): float {
        $query = $this->db->query("SELECT profit_margin FROM `" . DB_PREFIX . "meschain_product` WHERE product_id = '" . (int)$product_id . "' LIMIT 1");
        return $query->num_rows ? (float)$query->row['profit_margin'] : 10.00;
    }

    public function getMarketplaceOrdersByOrderId(int $order_id): array {
        $query = $this->db->query("SELECT mo.*, mm.name as marketplace_name
                                   FROM `" . DB_PREFIX . "meschain_order` mo
                                   LEFT JOIN `" . DB_PREFIX . "meschain_marketplace` mm ON mo.marketplace_id = mm.marketplace_id
                                   WHERE mo.order_id = '" . (int)$order_id . "'");
        return $query->rows;
    }

    public function addLog(string $type, int $reference_id, string $message): void {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_log`
                         SET type = '" . $this->db->escape($type) . "',
                             reference_id = '" . (int)$reference_id . "',
                             message = '" . $this->db->escape($message) . "',
                             date_added = NOW()");
    }

    public function getProductsByMarketplace(int $marketplace_id): array {
        $query = $this->db->query("SELECT mp.*, p.name, p.price as original_price
                                   FROM `" . DB_PREFIX . "meschain_product` mp
                                   LEFT JOIN `" . DB_PREFIX . "product_description` p ON mp.product_id = p.product_id
                                   WHERE mp.marketplace_id = '" . (int)$marketplace_id . "'
                                   AND p.language_id = '" . (int)$this->config->get('config_language_id') . "'");
        return $query->rows;
    }

    public function updateProductSync(int $meschain_product_id, string $sync_status): void {
        $this->db->query("UPDATE `" . DB_PREFIX . "meschain_product`
                         SET sync_status = '" . $this->db->escape($sync_status) . "',
                             last_sync = NOW(),
                             date_modified = NOW()
                         WHERE meschain_product_id = '" . (int)$meschain_product_id . "'");
    }
}
