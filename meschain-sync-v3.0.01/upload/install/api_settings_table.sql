-- API Ayarları Tablosu
CREATE TABLE IF NOT EXISTS `oc_user_api_settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `marketplace` VARCHAR(64) NOT NULL,
  `settings` TEXT NOT NULL,
  `date_added` DATETIME NOT NULL,
  `date_modified` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_marketplace` (`user_id`, `marketplace`),
  KEY `marketplace` (`marketplace`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci; 