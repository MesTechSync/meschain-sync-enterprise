<?php
/**
 * Cache İstatistik Tablosu Migration
 */
$sql = "
CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_cache_stats` (
    `date` date NOT NULL,
    `marketplace` varchar(32) NOT NULL,
    `hits` int(11) NOT NULL DEFAULT 0,
    `misses` int(11) NOT NULL DEFAULT 0,
    `expired` int(11) NOT NULL DEFAULT 0, 
    `sets` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`date`,`marketplace`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
";

$this->db->query($sql);
