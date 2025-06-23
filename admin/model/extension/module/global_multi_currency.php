<?php
/**
 * ATOM-M022: Global Multi-Currency Model
 * Global commerce data management with quantum-enhanced processing
 * MesChain-Sync Enterprise v2.2.0 - Musti Team Implementation
 * 
 * @package    MesChain Global Multi-Currency Model
 * @version    2.2.0
 * @author     MUSTI TAKIMI - ATOM Development Team
 * @date       June 7, 2025
 * @copyright  MesTechSync Solutions
 */

class ModelExtensionModuleGlobalMultiCurrency extends Model {
    
    private $currencies_table = 'meschain_global_currencies';
    private $exchange_rates_table = 'meschain_exchange_rates';
    private $conversions_table = 'meschain_currency_conversions';
    private $localization_table = 'meschain_localization_settings';
    private $tax_rates_table = 'meschain_international_tax_rates';
    private $arbitrage_table = 'meschain_arbitrage_opportunities';
    
    public function __construct($registry) {
        parent::__construct($registry);
        $this->createTables();
    }
    
    /**
     * Create necessary database tables
     */
    private function createTables() {
        // Global Currencies table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->currencies_table . "` (
                `currency_id` int(11) NOT NULL AUTO_INCREMENT,
                `currency_code` varchar(3) NOT NULL,
                `currency_name` varchar(255) NOT NULL,
                `currency_symbol` varchar(10) NOT NULL,
                `decimal_places` tinyint(2) DEFAULT 2,
                `priority` int(11) DEFAULT 100,
                `is_active` tinyint(1) DEFAULT 1,
                `is_crypto` tinyint(1) DEFAULT 0,
                `quantum_enhanced` tinyint(1) DEFAULT 1,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`currency_id`),
                UNIQUE KEY `currency_code` (`currency_code`),
                KEY `is_active` (`is_active`),
                KEY `priority` (`priority`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Exchange Rates table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->exchange_rates_table . "` (
                `rate_id` int(11) NOT NULL AUTO_INCREMENT,
                `from_currency` varchar(3) NOT NULL,
                `to_currency` varchar(3) NOT NULL,
                `exchange_rate` decimal(20,8) NOT NULL,
                `provider` varchar(100) NOT NULL,
                `spread` decimal(8,4) DEFAULT 0.0000,
                `volatility` decimal(8,4) DEFAULT 0.0000,
                `quantum_enhanced` tinyint(1) DEFAULT 1,
                `last_updated` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`rate_id`),
                UNIQUE KEY `currency_pair_provider` (`from_currency`, `to_currency`, `provider`),
                KEY `from_currency` (`from_currency`),
                KEY `to_currency` (`to_currency`),
                KEY `last_updated` (`last_updated`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Currency Conversions table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->conversions_table . "` (
                `conversion_id` int(11) NOT NULL AUTO_INCREMENT,
                `conversion_uuid` varchar(255) NOT NULL,
                `original_amount` decimal(20,8) NOT NULL,
                `converted_amount` decimal(20,8) NOT NULL,
                `from_currency` varchar(3) NOT NULL,
                `to_currency` varchar(3) NOT NULL,
                `exchange_rate` decimal(20,8) NOT NULL,
                `fees` decimal(20,8) DEFAULT 0.00000000,
                `provider` varchar(100) NOT NULL,
                `processing_time` decimal(8,3) DEFAULT 0.000,
                `quantum_acceleration` decimal(10,2) DEFAULT 0.00,
                `user_id` int(11) DEFAULT NULL,
                `ip_address` varchar(45) DEFAULT NULL,
                `user_agent` text,
                `quantum_enhanced` tinyint(1) DEFAULT 1,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`conversion_id`),
                UNIQUE KEY `conversion_uuid` (`conversion_uuid`),
                KEY `from_currency` (`from_currency`),
                KEY `to_currency` (`to_currency`),
                KEY `user_id` (`user_id`),
                KEY `created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Localization Settings table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->localization_table . "` (
                `localization_id` int(11) NOT NULL AUTO_INCREMENT,
                `locale_code` varchar(10) NOT NULL,
                `locale_name` varchar(255) NOT NULL,
                `language_code` varchar(2) NOT NULL,
                `country_code` varchar(2) NOT NULL,
                `direction` enum('ltr','rtl') DEFAULT 'ltr',
                `date_format` varchar(50) DEFAULT 'Y-m-d',
                `time_format` varchar(50) DEFAULT 'H:i:s',
                `number_format` varchar(50) DEFAULT '1,234.56',
                `currency_position` enum('before','after') DEFAULT 'before',
                `thousand_separator` varchar(1) DEFAULT ',',
                `decimal_separator` varchar(1) DEFAULT '.',
                `priority` int(11) DEFAULT 100,
                `is_active` tinyint(1) DEFAULT 1,
                `quantum_enhanced` tinyint(1) DEFAULT 1,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`localization_id`),
                UNIQUE KEY `locale_code` (`locale_code`),
                KEY `language_code` (`language_code`),
                KEY `country_code` (`country_code`),
                KEY `is_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // International Tax Rates table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->tax_rates_table . "` (
                `tax_id` int(11) NOT NULL AUTO_INCREMENT,
                `country_code` varchar(2) NOT NULL,
                `country_name` varchar(255) NOT NULL,
                `tax_type` varchar(50) NOT NULL,
                `vat_rate` decimal(5,2) DEFAULT 0.00,
                `sales_tax_rate` decimal(5,2) DEFAULT 0.00,
                `additional_tax_rate` decimal(5,2) DEFAULT 0.00,
                `tax_number_required` tinyint(1) DEFAULT 0,
                `reverse_charge_applicable` tinyint(1) DEFAULT 0,
                `digital_services_tax` decimal(5,2) DEFAULT 0.00,
                `quantum_enhanced` tinyint(1) DEFAULT 1,
                `effective_from` date NOT NULL,
                `effective_to` date DEFAULT NULL,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`tax_id`),
                KEY `country_code` (`country_code`),
                KEY `tax_type` (`tax_type`),
                KEY `effective_from` (`effective_from`),
                KEY `effective_to` (`effective_to`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        // Arbitrage Opportunities table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . $this->arbitrage_table . "` (
                `arbitrage_id` int(11) NOT NULL AUTO_INCREMENT,
                `analysis_uuid` varchar(255) NOT NULL,
                `currency_pair` varchar(7) NOT NULL,
                `from_currency` varchar(3) NOT NULL,
                `to_currency` varchar(3) NOT NULL,
                `rate_1` decimal(20,8) NOT NULL,
                `rate_2` decimal(20,8) NOT NULL,
                `cross_rate` decimal(20,8) NOT NULL,
                `profit_potential` decimal(8,4) NOT NULL,
                `risk_level` enum('low','medium','high') DEFAULT 'medium',
                `provider_1` varchar(100) NOT NULL,
                `provider_2` varchar(100) NOT NULL,
                `quantum_enhanced` tinyint(1) DEFAULT 1,
                `expires_at` timestamp NULL,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`arbitrage_id`),
                UNIQUE KEY `analysis_uuid` (`analysis_uuid`),
                KEY `currency_pair` (`currency_pair`),
                KEY `profit_potential` (`profit_potential`),
                KEY `risk_level` (`risk_level`),
                KEY `created_at` (`created_at`),
                KEY `expires_at` (`expires_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }
    
    /**
     * Save currency configuration
     */
    public function saveCurrency($currency_data) {
        $sql = "INSERT INTO `" . DB_PREFIX . $this->currencies_table . "` SET 
                `currency_code` = '" . $this->db->escape($currency_data['code']) . "',
                `currency_name` = '" . $this->db->escape($currency_data['name']) . "',
                `currency_symbol` = '" . $this->db->escape($currency_data['symbol']) . "',
                `decimal_places` = '" . (int)$currency_data['decimal_places'] . "',
                `priority` = '" . (int)$currency_data['priority'] . "',
                `is_active` = '" . (int)$currency_data['is_active'] . "',
                `is_crypto` = '" . (int)$currency_data['is_crypto'] . "',
                `quantum_enhanced` = '" . (int)$currency_data['quantum_enhanced'] . "'
                ON DUPLICATE KEY UPDATE
                `currency_name` = '" . $this->db->escape($currency_data['name']) . "',
                `currency_symbol` = '" . $this->db->escape($currency_data['symbol']) . "',
                `decimal_places` = '" . (int)$currency_data['decimal_places'] . "',
                `priority` = '" . (int)$currency_data['priority'] . "',
                `is_active` = '" . (int)$currency_data['is_active'] . "',
                `is_crypto` = '" . (int)$currency_data['is_crypto'] . "',
                `quantum_enhanced` = '" . (int)$currency_data['quantum_enhanced'] . "'";
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }
    
    /**
     * Save exchange rate
     */
    public function saveExchangeRate($rate_data) {
        $sql = "INSERT INTO `" . DB_PREFIX . $this->exchange_rates_table . "` SET 
                `from_currency` = '" . $this->db->escape($rate_data['from_currency']) . "',
                `to_currency` = '" . $this->db->escape($rate_data['to_currency']) . "',
                `exchange_rate` = '" . (float)$rate_data['rate'] . "',
                `provider` = '" . $this->db->escape($rate_data['provider']) . "',
                `spread` = '" . (float)$rate_data['spread'] . "',
                `volatility` = '" . (float)$rate_data['volatility'] . "',
                `quantum_enhanced` = '" . (int)$rate_data['quantum_enhanced'] . "'
                ON DUPLICATE KEY UPDATE
                `exchange_rate` = '" . (float)$rate_data['rate'] . "',
                `spread` = '" . (float)$rate_data['spread'] . "',
                `volatility` = '" . (float)$rate_data['volatility'] . "',
                `quantum_enhanced` = '" . (int)$rate_data['quantum_enhanced'] . "'";
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }
    
    /**
     * Save currency conversion
     */
    public function saveConversion($conversion_data) {
        $sql = "INSERT INTO `" . DB_PREFIX . $this->conversions_table . "` SET 
                `conversion_uuid` = '" . $this->db->escape($conversion_data['uuid']) . "',
                `original_amount` = '" . (float)$conversion_data['original_amount'] . "',
                `converted_amount` = '" . (float)$conversion_data['converted_amount'] . "',
                `from_currency` = '" . $this->db->escape($conversion_data['from_currency']) . "',
                `to_currency` = '" . $this->db->escape($conversion_data['to_currency']) . "',
                `exchange_rate` = '" . (float)$conversion_data['exchange_rate'] . "',
                `fees` = '" . (float)$conversion_data['fees'] . "',
                `provider` = '" . $this->db->escape($conversion_data['provider']) . "',
                `processing_time` = '" . (float)$conversion_data['processing_time'] . "',
                `quantum_acceleration` = '" . (float)$conversion_data['quantum_acceleration'] . "',
                `user_id` = '" . (int)$conversion_data['user_id'] . "',
                `ip_address` = '" . $this->db->escape($conversion_data['ip_address']) . "',
                `user_agent` = '" . $this->db->escape($conversion_data['user_agent']) . "',
                `quantum_enhanced` = '" . (int)$conversion_data['quantum_enhanced'] . "'";
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }
    
    /**
     * Save localization settings
     */
    public function saveLocalization($localization_data) {
        $sql = "INSERT INTO `" . DB_PREFIX . $this->localization_table . "` SET 
                `locale_code` = '" . $this->db->escape($localization_data['locale_code']) . "',
                `locale_name` = '" . $this->db->escape($localization_data['locale_name']) . "',
                `language_code` = '" . $this->db->escape($localization_data['language_code']) . "',
                `country_code` = '" . $this->db->escape($localization_data['country_code']) . "',
                `direction` = '" . $this->db->escape($localization_data['direction']) . "',
                `date_format` = '" . $this->db->escape($localization_data['date_format']) . "',
                `time_format` = '" . $this->db->escape($localization_data['time_format']) . "',
                `number_format` = '" . $this->db->escape($localization_data['number_format']) . "',
                `currency_position` = '" . $this->db->escape($localization_data['currency_position']) . "',
                `thousand_separator` = '" . $this->db->escape($localization_data['thousand_separator']) . "',
                `decimal_separator` = '" . $this->db->escape($localization_data['decimal_separator']) . "',
                `priority` = '" . (int)$localization_data['priority'] . "',
                `is_active` = '" . (int)$localization_data['is_active'] . "',
                `quantum_enhanced` = '" . (int)$localization_data['quantum_enhanced'] . "'
                ON DUPLICATE KEY UPDATE
                `locale_name` = '" . $this->db->escape($localization_data['locale_name']) . "',
                `language_code` = '" . $this->db->escape($localization_data['language_code']) . "',
                `country_code` = '" . $this->db->escape($localization_data['country_code']) . "',
                `direction` = '" . $this->db->escape($localization_data['direction']) . "',
                `date_format` = '" . $this->db->escape($localization_data['date_format']) . "',
                `time_format` = '" . $this->db->escape($localization_data['time_format']) . "',
                `number_format` = '" . $this->db->escape($localization_data['number_format']) . "',
                `currency_position` = '" . $this->db->escape($localization_data['currency_position']) . "',
                `thousand_separator` = '" . $this->db->escape($localization_data['thousand_separator']) . "',
                `decimal_separator` = '" . $this->db->escape($localization_data['decimal_separator']) . "',
                `priority` = '" . (int)$localization_data['priority'] . "',
                `is_active` = '" . (int)$localization_data['is_active'] . "',
                `quantum_enhanced` = '" . (int)$localization_data['quantum_enhanced'] . "'";
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }
    
    /**
     * Save tax rate
     */
    public function saveTaxRate($tax_data) {
        $sql = "INSERT INTO `" . DB_PREFIX . $this->tax_rates_table . "` SET 
                `country_code` = '" . $this->db->escape($tax_data['country_code']) . "',
                `country_name` = '" . $this->db->escape($tax_data['country_name']) . "',
                `tax_type` = '" . $this->db->escape($tax_data['tax_type']) . "',
                `vat_rate` = '" . (float)$tax_data['vat_rate'] . "',
                `sales_tax_rate` = '" . (float)$tax_data['sales_tax_rate'] . "',
                `additional_tax_rate` = '" . (float)$tax_data['additional_tax_rate'] . "',
                `tax_number_required` = '" . (int)$tax_data['tax_number_required'] . "',
                `reverse_charge_applicable` = '" . (int)$tax_data['reverse_charge_applicable'] . "',
                `digital_services_tax` = '" . (float)$tax_data['digital_services_tax'] . "',
                `quantum_enhanced` = '" . (int)$tax_data['quantum_enhanced'] . "',
                `effective_from` = '" . $this->db->escape($tax_data['effective_from']) . "',
                `effective_to` = '" . $this->db->escape($tax_data['effective_to']) . "'";
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }
    
    /**
     * Save arbitrage opportunity
     */
    public function saveArbitrageOpportunity($arbitrage_data) {
        $sql = "INSERT INTO `" . DB_PREFIX . $this->arbitrage_table . "` SET 
                `analysis_uuid` = '" . $this->db->escape($arbitrage_data['uuid']) . "',
                `currency_pair` = '" . $this->db->escape($arbitrage_data['currency_pair']) . "',
                `from_currency` = '" . $this->db->escape($arbitrage_data['from_currency']) . "',
                `to_currency` = '" . $this->db->escape($arbitrage_data['to_currency']) . "',
                `rate_1` = '" . (float)$arbitrage_data['rate_1'] . "',
                `rate_2` = '" . (float)$arbitrage_data['rate_2'] . "',
                `cross_rate` = '" . (float)$arbitrage_data['cross_rate'] . "',
                `profit_potential` = '" . (float)$arbitrage_data['profit_potential'] . "',
                `risk_level` = '" . $this->db->escape($arbitrage_data['risk_level']) . "',
                `provider_1` = '" . $this->db->escape($arbitrage_data['provider_1']) . "',
                `provider_2` = '" . $this->db->escape($arbitrage_data['provider_2']) . "',
                `quantum_enhanced` = '" . (int)$arbitrage_data['quantum_enhanced'] . "',
                `expires_at` = DATE_ADD(NOW(), INTERVAL 1 HOUR)";
        
        $this->db->query($sql);
        
        return $this->db->getLastId();
    }
    
    /**
     * Get currencies
     */
    public function getCurrencies($filters = []) {
        $sql = "SELECT * FROM `" . DB_PREFIX . $this->currencies_table . "` WHERE 1=1";
        
        if (!empty($filters['is_active'])) {
            $sql .= " AND `is_active` = '" . (int)$filters['is_active'] . "'";
        }
        
        if (!empty($filters['is_crypto'])) {
            $sql .= " AND `is_crypto` = '" . (int)$filters['is_crypto'] . "'";
        }
        
        $sql .= " ORDER BY `priority` ASC, `currency_code` ASC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get exchange rates
     */
    public function getExchangeRates($filters = []) {
        $sql = "SELECT * FROM `" . DB_PREFIX . $this->exchange_rates_table . "` WHERE 1=1";
        
        if (!empty($filters['from_currency'])) {
            $sql .= " AND `from_currency` = '" . $this->db->escape($filters['from_currency']) . "'";
        }
        
        if (!empty($filters['to_currency'])) {
            $sql .= " AND `to_currency` = '" . $this->db->escape($filters['to_currency']) . "'";
        }
        
        if (!empty($filters['provider'])) {
            $sql .= " AND `provider` = '" . $this->db->escape($filters['provider']) . "'";
        }
        
        $sql .= " ORDER BY `last_updated` DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get conversions
     */
    public function getConversions($filters = []) {
        $sql = "SELECT * FROM `" . DB_PREFIX . $this->conversions_table . "` WHERE 1=1";
        
        if (!empty($filters['from_currency'])) {
            $sql .= " AND `from_currency` = '" . $this->db->escape($filters['from_currency']) . "'";
        }
        
        if (!empty($filters['to_currency'])) {
            $sql .= " AND `to_currency` = '" . $this->db->escape($filters['to_currency']) . "'";
        }
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND `user_id` = '" . (int)$filters['user_id'] . "'";
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND `created_at` >= '" . $this->db->escape($filters['date_from']) . "'";
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND `created_at` <= '" . $this->db->escape($filters['date_to']) . "'";
        }
        
        $sql .= " ORDER BY `created_at` DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get localization settings
     */
    public function getLocalizationSettings($filters = []) {
        $sql = "SELECT * FROM `" . DB_PREFIX . $this->localization_table . "` WHERE 1=1";
        
        if (!empty($filters['is_active'])) {
            $sql .= " AND `is_active` = '" . (int)$filters['is_active'] . "'";
        }
        
        if (!empty($filters['language_code'])) {
            $sql .= " AND `language_code` = '" . $this->db->escape($filters['language_code']) . "'";
        }
        
        if (!empty($filters['country_code'])) {
            $sql .= " AND `country_code` = '" . $this->db->escape($filters['country_code']) . "'";
        }
        
        $sql .= " ORDER BY `priority` ASC, `locale_code` ASC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get tax rates
     */
    public function getTaxRates($filters = []) {
        $sql = "SELECT * FROM `" . DB_PREFIX . $this->tax_rates_table . "` WHERE 1=1";
        
        if (!empty($filters['country_code'])) {
            $sql .= " AND `country_code` = '" . $this->db->escape($filters['country_code']) . "'";
        }
        
        if (!empty($filters['tax_type'])) {
            $sql .= " AND `tax_type` = '" . $this->db->escape($filters['tax_type']) . "'";
        }
        
        // Only get current tax rates
        $sql .= " AND `effective_from` <= CURDATE()";
        $sql .= " AND (`effective_to` IS NULL OR `effective_to` >= CURDATE())";
        
        $sql .= " ORDER BY `country_code` ASC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get arbitrage opportunities
     */
    public function getArbitrageOpportunities($filters = []) {
        $sql = "SELECT * FROM `" . DB_PREFIX . $this->arbitrage_table . "` WHERE 1=1";
        
        if (!empty($filters['currency_pair'])) {
            $sql .= " AND `currency_pair` = '" . $this->db->escape($filters['currency_pair']) . "'";
        }
        
        if (!empty($filters['risk_level'])) {
            $sql .= " AND `risk_level` = '" . $this->db->escape($filters['risk_level']) . "'";
        }
        
        if (!empty($filters['min_profit'])) {
            $sql .= " AND `profit_potential` >= '" . (float)$filters['min_profit'] . "'";
        }
        
        // Only get non-expired opportunities
        $sql .= " AND (`expires_at` IS NULL OR `expires_at` > NOW())";
        
        $sql .= " ORDER BY `profit_potential` DESC, `created_at` DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $query = $this->db->query($sql);
        
        return $query->rows;
    }
    
    /**
     * Get global statistics
     */
    public function getGlobalStatistics($period = '24h') {
        $date_condition = "";
        
        switch ($period) {
            case '1h':
                $date_condition = "WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
                break;
            case '24h':
                $date_condition = "WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
                break;
            case '7d':
                $date_condition = "WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case '30d':
                $date_condition = "WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
        }
        
        $statistics = [];
        
        // Total conversions
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . $this->conversions_table . "` " . $date_condition);
        $statistics['total_conversions'] = $query->row['total'];
        
        // Total volume
        $query = $this->db->query("SELECT SUM(original_amount) as total_volume FROM `" . DB_PREFIX . $this->conversions_table . "` " . $date_condition);
        $statistics['total_volume'] = round($query->row['total_volume'], 2);
        
        // Average processing time
        $query = $this->db->query("SELECT AVG(processing_time) as avg_time FROM `" . DB_PREFIX . $this->conversions_table . "` " . $date_condition);
        $statistics['average_processing_time'] = round($query->row['avg_time'], 3);
        
        // Most traded currencies
        $query = $this->db->query("
            SELECT from_currency as currency, COUNT(*) as count 
            FROM `" . DB_PREFIX . $this->conversions_table . "` " . $date_condition . "
            GROUP BY from_currency 
            ORDER BY count DESC 
            LIMIT 5
        ");
        $statistics['most_traded_currencies'] = $query->rows;
        
        // Active currencies
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . $this->currencies_table . "` WHERE is_active = 1");
        $statistics['active_currencies'] = $query->row['total'];
        
        // Active locales
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . $this->localization_table . "` WHERE is_active = 1");
        $statistics['active_locales'] = $query->row['total'];
        
        // Current arbitrage opportunities
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . $this->arbitrage_table . "` WHERE expires_at > NOW() OR expires_at IS NULL");
        $statistics['arbitrage_opportunities'] = $query->row['total'];
        
        return $statistics;
    }
    
    /**
     * Get currency performance
     */
    public function getCurrencyPerformance($period = '24h') {
        $date_condition = "";
        
        switch ($period) {
            case '1h':
                $date_condition = "WHERE c.created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
                break;
            case '24h':
                $date_condition = "WHERE c.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)";
                break;
            case '7d':
                $date_condition = "WHERE c.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case '30d':
                $date_condition = "WHERE c.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
        }
        
        $performance = [];
        
        // Currency conversion volume
        $query = $this->db->query("
            SELECT 
                c.from_currency,
                COUNT(*) as conversion_count,
                SUM(c.original_amount) as total_volume,
                AVG(c.processing_time) as avg_processing_time,
                AVG(c.quantum_acceleration) as avg_quantum_acceleration
            FROM `" . DB_PREFIX . $this->conversions_table . "` c
            " . $date_condition . "
            GROUP BY c.from_currency
            ORDER BY total_volume DESC
        ");
        
        $performance['currency_volumes'] = $query->rows;
        
        // Exchange rate volatility
        $query = $this->db->query("
            SELECT 
                from_currency,
                to_currency,
                AVG(volatility) as avg_volatility,
                MAX(volatility) as max_volatility,
                MIN(volatility) as min_volatility
            FROM `" . DB_PREFIX . $this->exchange_rates_table . "`
            GROUP BY from_currency, to_currency
            ORDER BY avg_volatility DESC
            LIMIT 10
        ");
        
        $performance['volatility_data'] = $query->rows;
        
        return $performance;
    }
    
    /**
     * Get dashboard metrics
     */
    public function getDashboardMetrics() {
        $metrics = [];
        
        // Active currencies
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . $this->currencies_table . "` WHERE is_active = 1");
        $metrics['active_currencies'] = $query->row['total'];
        
        // Today's conversions
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . $this->conversions_table . "` WHERE DATE(created_at) = CURDATE()");
        $metrics['conversions_today'] = $query->row['total'];
        
        // Today's volume
        $query = $this->db->query("SELECT SUM(original_amount) as total FROM `" . DB_PREFIX . $this->conversions_table . "` WHERE DATE(created_at) = CURDATE()");
        $metrics['volume_today'] = round($query->row['total'], 2);
        
        // Active locales
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . $this->localization_table . "` WHERE is_active = 1");
        $metrics['active_locales'] = $query->row['total'];
        
        // Exchange rate updates today
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . $this->exchange_rates_table . "` WHERE DATE(last_updated) = CURDATE()");
        $metrics['rate_updates_today'] = $query->row['total'];
        
        // Current arbitrage opportunities
        $query = $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . $this->arbitrage_table . "` WHERE expires_at > NOW() OR expires_at IS NULL");
        $metrics['arbitrage_opportunities'] = $query->row['total'];
        
        return $metrics;
    }
} 