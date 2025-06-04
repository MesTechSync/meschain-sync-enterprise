-- MesChain-Sync User Permissions Fix
-- Bu script, tüm MesChain-Sync modülleri için kullanıcı izinlerini düzeltir

-- Ana MesChain-Sync modülü izinleri
INSERT IGNORE INTO `oc_user_group` (`user_group_id`, `name`, `permission`) VALUES
(1, 'Administrator', CONCAT(IFNULL((SELECT `permission` FROM (SELECT * FROM `oc_user_group` WHERE `user_group_id` = 1) AS ug), ''), ',"access":["extension/module/meschain_sync"],"modify":["extension/module/meschain_sync"]'));

-- Marketplace modülleri izinleri
INSERT IGNORE INTO `oc_user_group` (`user_group_id`, `name`, `permission`) VALUES
(1, 'Administrator', CONCAT(IFNULL((SELECT `permission` FROM (SELECT * FROM `oc_user_group` WHERE `user_group_id` = 1) AS ug), ''), ',"access":["extension/module/amazon"],"modify":["extension/module/amazon"]'));

INSERT IGNORE INTO `oc_user_group` (`user_group_id`, `name`, `permission`) VALUES
(1, 'Administrator', CONCAT(IFNULL((SELECT `permission` FROM (SELECT * FROM `oc_user_group` WHERE `user_group_id` = 1) AS ug), ''), ',"access":["extension/module/ebay"],"modify":["extension/module/ebay"]'));

INSERT IGNORE INTO `oc_user_group` (`user_group_id`, `name`, `permission`) VALUES
(1, 'Administrator', CONCAT(IFNULL((SELECT `permission` FROM (SELECT * FROM `oc_user_group` WHERE `user_group_id` = 1) AS ug), ''), ',"access":["extension/module/hepsiburada"],"modify":["extension/module/hepsiburada"]'));

INSERT IGNORE INTO `oc_user_group` (`user_group_id`, `name`, `permission`) VALUES
(1, 'Administrator', CONCAT(IFNULL((SELECT `permission` FROM (SELECT * FROM `oc_user_group` WHERE `user_group_id` = 1) AS ug), ''), ',"access":["extension/module/n11"],"modify":["extension/module/n11"]'));

INSERT IGNORE INTO `oc_user_group` (`user_group_id`, `name`, `permission`) VALUES
(1, 'Administrator', CONCAT(IFNULL((SELECT `permission` FROM (SELECT * FROM `oc_user_group` WHERE `user_group_id` = 1) AS ug), ''), ',"access":["extension/module/trendyol"],"modify":["extension/module/trendyol"]'));

INSERT IGNORE INTO `oc_user_group` (`user_group_id`, `name`, `permission`) VALUES
(1, 'Administrator', CONCAT(IFNULL((SELECT `permission` FROM (SELECT * FROM `oc_user_group` WHERE `user_group_id` = 1) AS ug), ''), ',"access":["extension/module/ozon"],"modify":["extension/module/ozon"]'));

-- Yardımcı modüller izinleri
INSERT IGNORE INTO `oc_user_group` (`user_group_id`, `name`, `permission`) VALUES
(1, 'Administrator', CONCAT(IFNULL((SELECT `permission` FROM (SELECT * FROM `oc_user_group` WHERE `user_group_id` = 1) AS ug), ''), ',"access":["extension/module/n11_category"],"modify":["extension/module/n11_category"]'));

INSERT IGNORE INTO `oc_user_group` (`user_group_id`, `name`, `permission`) VALUES
(1, 'Administrator', CONCAT(IFNULL((SELECT `permission` FROM (SELECT * FROM `oc_user_group` WHERE `user_group_id` = 1) AS ug), ''), ',"access":["extension/module/cache_monitor"],"modify":["extension/module/cache_monitor"]'));

INSERT IGNORE INTO `oc_user_group` (`user_group_id`, `name`, `permission`) VALUES
(1, 'Administrator', CONCAT(IFNULL((SELECT `permission` FROM (SELECT * FROM `oc_user_group` WHERE `user_group_id` = 1) AS ug), ''), ',"access":["extension/module/dropshipping"],"modify":["extension/module/dropshipping"]'));

INSERT IGNORE INTO `oc_user_group` (`user_group_id`, `name`, `permission`) VALUES
(1, 'Administrator', CONCAT(IFNULL((SELECT `permission` FROM (SELECT * FROM `oc_user_group` WHERE `user_group_id` = 1) AS ug), ''), ',"access":["extension/module/user_management"],"modify":["extension/module/user_management"]'));

INSERT IGNORE INTO `oc_user_group` (`user_group_id`, `name`, `permission`) VALUES
(1, 'Administrator', CONCAT(IFNULL((SELECT `permission` FROM (SELECT * FROM `oc_user_group` WHERE `user_group_id` = 1) AS ug), ''), ',"access":["extension/module/announcement"],"modify":["extension/module/announcement"]'));

INSERT IGNORE INTO `oc_user_group` (`user_group_id`, `name`, `permission`) VALUES
(1, 'Administrator', CONCAT(IFNULL((SELECT `permission` FROM (SELECT * FROM `oc_user_group` WHERE `user_group_id` = 1) AS ug), ''), ',"access":["extension/module/rbac_management"],"modify":["extension/module/rbac_management"]'));

INSERT IGNORE INTO `oc_user_group` (`user_group_id`, `name`, `permission`) VALUES
(1, 'Administrator', CONCAT(IFNULL((SELECT `permission` FROM (SELECT * FROM `oc_user_group` WHERE `user_group_id` = 1) AS ug), ''), ',"access":["extension/module/user_settings"],"modify":["extension/module/user_settings"]'));

INSERT IGNORE INTO `oc_user_group` (`user_group_id`, `name`, `permission`) VALUES
(1, 'Administrator', CONCAT(IFNULL((SELECT `permission` FROM (SELECT * FROM `oc_user_group` WHERE `user_group_id` = 1) AS ug), ''), ',"access":["extension/module/help"],"modify":["extension/module/help"]'));

-- Basit yöntem (eğer yukarıdaki çalışmazsa)
-- Direkt JSON güncelleme
UPDATE `oc_user_group` SET `permission` = JSON_SET(
    IFNULL(`permission`, '{}'),
    '$.access[999]', 'extension/module/meschain_sync',
    '$.modify[999]', 'extension/module/meschain_sync',
    '$.access[1000]', 'extension/module/amazon',
    '$.modify[1000]', 'extension/module/amazon',
    '$.access[1001]', 'extension/module/ebay',
    '$.modify[1001]', 'extension/module/ebay',
    '$.access[1002]', 'extension/module/hepsiburada',
    '$.modify[1002]', 'extension/module/hepsiburada',
    '$.access[1003]', 'extension/module/n11',
    '$.modify[1003]', 'extension/module/n11',
    '$.access[1004]', 'extension/module/trendyol',
    '$.modify[1004]', 'extension/module/trendyol',
    '$.access[1005]', 'extension/module/ozon',
    '$.modify[1005]', 'extension/module/ozon',
    '$.access[1006]', 'extension/module/n11_category',
    '$.modify[1006]', 'extension/module/n11_category',
    '$.access[1007]', 'extension/module/cache_monitor',
    '$.modify[1007]', 'extension/module/cache_monitor',
    '$.access[1008]', 'extension/module/dropshipping',
    '$.modify[1008]', 'extension/module/dropshipping',
    '$.access[1009]', 'extension/module/user_management',
    '$.modify[1009]', 'extension/module/user_management',
    '$.access[1010]', 'extension/module/announcement',
    '$.modify[1010]', 'extension/module/announcement',
    '$.access[1011]', 'extension/module/rbac_management',
    '$.modify[1011]', 'extension/module/rbac_management',
    '$.access[1012]', 'extension/module/user_settings',
    '$.modify[1012]', 'extension/module/user_settings',
    '$.access[1013]', 'extension/module/help',
    '$.modify[1013]', 'extension/module/help'
) WHERE `user_group_id` = 1;

-- Alternatif çözüm: Serialized PHP array formatında
-- OpenCart 3.x kullanıcı izinleri serialized format kullanır
UPDATE `oc_user_group` SET `permission` = 'a:2:{s:6:"access";a:100:{i:0;s:16:"common/dashboard";i:1;s:13:"common/startup";i:2;s:18:"common/login";i:3;s:20:"common/logout";i:4;s:19:"common/forgotten";i:5;s:15:"common/authorize";i:6;s:14:"common/column_left";i:7;s:13:"common/footer";i:8;s:13:"common/header";i:9;s:11:"common/menu";i:10;s:30:"extension/module/meschain_sync";i:11;s:23:"extension/module/amazon";i:12;s:21:"extension/module/ebay";i:13;s:28:"extension/module/hepsiburada";i:14;s:20:"extension/module/n11";i:15;s:25:"extension/module/trendyol";i:16;s:21:"extension/module/ozon";i:17;s:28:"extension/module/n11_category";i:18;s:29:"extension/module/cache_monitor";i:19;s:27:"extension/module/dropshipping";i:20;s:30:"extension/module/user_management";i:21;s:28:"extension/module/announcement";i:22;s:31:"extension/module/rbac_management";i:23;s:29:"extension/module/user_settings";i:24;s:21:"extension/module/help";}s:6:"modify";a:100:{i:0;s:16:"common/dashboard";i:1;s:13:"common/startup";i:2;s:18:"common/login";i:3;s:20:"common/logout";i:4;s:19:"common/forgotten";i:5;s:15:"common/authorize";i:6;s:14:"common/column_left";i:7;s:13:"common/footer";i:8;s:13:"common/header";i:9;s:11:"common/menu";i:10;s:30:"extension/module/meschain_sync";i:11;s:23:"extension/module/amazon";i:12;s:21:"extension/module/ebay";i:13;s:28:"extension/module/hepsiburada";i:14;s:20:"extension/module/n11";i:15;s:25:"extension/module/trendyol";i:16;s:21:"extension/module/ozon";i:17;s:28:"extension/module/n11_category";i:18;s:29:"extension/module/cache_monitor";i:19;s:27:"extension/module/dropshipping";i:20;s:30:"extension/module/user_management";i:21;s:28:"extension/module/announcement";i:22;s:31:"extension/module/rbac_management";i:23;s:29:"extension/module/user_settings";i:24;s:21:"extension/module/help";}}' 
WHERE `user_group_id` = 1 AND `name` = 'Administrator';

-- Modül durumlarını etkinleştir
INSERT IGNORE INTO `oc_setting` (`store_id`, `code`, `key`, `value`, `serialized`) VALUES
(0, 'module_meschain_sync', 'module_meschain_sync_status', '1', 0),
(0, 'module_amazon', 'module_amazon_status', '1', 0),
(0, 'module_ebay', 'module_ebay_status', '1', 0),
(0, 'module_hepsiburada', 'module_hepsiburada_status', '1', 0),
(0, 'module_n11', 'module_n11_status', '1', 0),
(0, 'module_trendyol', 'module_trendyol_status', '1', 0),
(0, 'module_ozon', 'module_ozon_status', '1', 0),
(0, 'module_cache_monitor', 'module_cache_monitor_status', '1', 0),
(0, 'module_dropshipping', 'module_dropshipping_status', '1', 0),
(0, 'module_user_management', 'module_user_management_status', '1', 0),
(0, 'module_announcement', 'module_announcement_status', '1', 0),
(0, 'module_rbac_management', 'module_rbac_management_status', '1', 0),
(0, 'module_user_settings', 'module_user_settings_status', '1', 0),
(0, 'module_help', 'module_help_status', '1', 0); 