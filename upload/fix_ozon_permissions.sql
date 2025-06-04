-- Ozon Modülü Permission Düzeltme Script
-- Bu script Ozon modülü için gerekli izinleri ekler

-- Admin kullanıcı grubunu bul (genellikle group_id = 1)
SET @admin_group_id = (SELECT user_group_id FROM `oc_user_group` WHERE name = 'Administrator' LIMIT 1);

-- Eğer admin grubu bulunamazsa, varsayılan olarak 1'i kullan
SET @admin_group_id = IFNULL(@admin_group_id, 1);

-- Ozon modülü için access permission'ı ekle
INSERT IGNORE INTO `oc_user_group_permission` (`user_group_id`, `permission`, `type`) 
VALUES (@admin_group_id, 'extension/module/ozon', 'access');

-- Ozon modülü için modify permission'ı ekle  
INSERT IGNORE INTO `oc_user_group_permission` (`user_group_id`, `permission`, `type`) 
VALUES (@admin_group_id, 'extension/module/ozon', 'modify');

-- Ozon dashboard için access permission'ı ekle
INSERT IGNORE INTO `oc_user_group_permission` (`user_group_id`, `permission`, `type`) 
VALUES (@admin_group_id, 'extension/module/ozon/dashboard', 'access');

-- Ozon dashboard için modify permission'ı ekle
INSERT IGNORE INTO `oc_user_group_permission` (`user_group_id`, `permission`, `type`) 
VALUES (@admin_group_id, 'extension/module/ozon/dashboard', 'modify');

-- Tüm diğer Ozon alt fonksiyonları için permission'lar
INSERT IGNORE INTO `oc_user_group_permission` (`user_group_id`, `permission`, `type`) 
VALUES 
(@admin_group_id, 'extension/module/ozon/test_connection', 'access'),
(@admin_group_id, 'extension/module/ozon/test_connection', 'modify'),
(@admin_group_id, 'extension/module/ozon/get_orders', 'access'),
(@admin_group_id, 'extension/module/ozon/get_orders', 'modify'),
(@admin_group_id, 'extension/module/ozon/sync_products', 'access'),
(@admin_group_id, 'extension/module/ozon/sync_products', 'modify'),
(@admin_group_id, 'extension/module/ozon/update_stock', 'access'),
(@admin_group_id, 'extension/module/ozon/update_stock', 'modify');

-- Extension tablosuna Ozon modülünü ekle (eğer yoksa)
INSERT IGNORE INTO `oc_extension` (`type`, `code`) VALUES ('module', 'ozon');

-- Başarı mesajı için session güncelle (opsiyonel)
SELECT 'Ozon modülü permission\'ları başarıyla eklendi!' as result; 