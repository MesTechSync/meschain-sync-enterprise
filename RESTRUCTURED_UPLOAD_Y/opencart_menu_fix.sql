-- OpenCart 4.0.2.3 Cron Table Setup
-- This script ensures the cron table exists with proper structure

CREATE TABLE IF NOT EXISTS `oc_cron` (
  `cron_id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(64) NOT NULL,
  `description` text NOT NULL,
  `cycle` varchar(12) NOT NULL,
  `action` text NOT NULL,
  `status` tinyint(1) NOT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime NOT NULL,
  PRIMARY KEY (`cron_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Insert default cron jobs if they don't exist
INSERT IGNORE INTO `oc_cron` (`code`, `description`, `cycle`, `action`, `status`, `date_added`, `date_modified`) VALUES
('currency', 'Update exchange rates', 'day', 'cron/currency', 1, NOW(), NOW()),
('gdpr', 'GDPR compliance cleanup', 'day', 'cron/gdpr', 1, NOW(), NOW()),
('subscription', 'Process subscriptions', 'day', 'cron/subscription', 1, NOW(), NOW());

-- Grant permissions for admin users
INSERT IGNORE INTO `oc_user_group` (`name`, `permission`) VALUES 
('Administrator', '{"access":["common/column_left","common/dashboard"],"modify":["common/column_left","common/dashboard"]}');

-- Update admin permissions to include cron access
UPDATE `oc_user_group` SET 
`permission` = JSON_SET(IFNULL(`permission`, "{}"), "$.access", JSON_ARRAY_APPEND(IFNULL(JSON_EXTRACT(`permission`, "$.access"), JSON_ARRAY()), "$", "marketplace/cron"))
WHERE `name` = 'Administrator';

UPDATE `oc_user_group` SET 
`permission` = JSON_SET(IFNULL(`permission`, "{}"), "$.modify", JSON_ARRAY_APPEND(IFNULL(JSON_EXTRACT(`permission`, "$.modify"), JSON_ARRAY()), "$", "marketplace/cron"))
WHERE `name` = 'Administrator';
