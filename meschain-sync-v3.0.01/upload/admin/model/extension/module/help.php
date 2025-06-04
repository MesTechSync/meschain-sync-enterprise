<?php
/**
 * MesChain Help & Documentation Model
 * 
 * OpenCart Extension - Help System
 * 
 * @category   Model
 * @package    MesChain-Sync
 * @version    2.5.0
 * @author     MesTech Team
 * @license    Commercial License
 * @link       https://meschain.com
 */

class ModelExtensionModuleHelp extends Model {
    
    /**
     * Model installation
     */
    public function install() {
        // Create help articles table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_help_articles` (
                `article_id` int(11) NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL,
                `content` text NOT NULL,
                `category` varchar(100) NOT NULL,
                `tags` varchar(255) DEFAULT NULL,
                `sort_order` int(3) NOT NULL DEFAULT '0',
                `status` tinyint(1) NOT NULL DEFAULT '1',
                `views` int(11) NOT NULL DEFAULT '0',
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`article_id`),
                KEY `category` (`category`),
                KEY `status` (`status`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
        
        // Create help categories table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_help_categories` (
                `category_id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(100) NOT NULL,
                `description` text,
                `icon` varchar(50) DEFAULT NULL,
                `sort_order` int(3) NOT NULL DEFAULT '0',
                `status` tinyint(1) NOT NULL DEFAULT '1',
                PRIMARY KEY (`category_id`),
                KEY `status` (`status`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
        
        // Insert default categories
        $this->insertDefaultCategories();
        
        // Insert default articles
        $this->insertDefaultArticles();
    }
    
    /**
     * Model uninstallation
     */
    public function uninstall() {
        // Note: We don't drop tables on uninstall to preserve data
        // Only remove settings
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE code LIKE 'module_help%'");
    }
    
    /**
     * Insert default categories
     */
    private function insertDefaultCategories() {
        $categories = array(
            array('name' => 'Genel Kullanım', 'description' => 'Temel kullanım rehberi', 'icon' => 'fa-info-circle', 'sort_order' => 1),
            array('name' => 'Marketplace Entegrasyonları', 'description' => 'Pazaryeri entegrasyon rehberleri', 'icon' => 'fa-shopping-cart', 'sort_order' => 2),
            array('name' => 'API Ayarları', 'description' => 'API yapılandırma rehberleri', 'icon' => 'fa-cogs', 'sort_order' => 3),
            array('name' => 'Sorun Giderme', 'description' => 'Yaygın sorunlar ve çözümleri', 'icon' => 'fa-wrench', 'sort_order' => 4),
            array('name' => 'Sık Sorulan Sorular', 'description' => 'Frequently Asked Questions', 'icon' => 'fa-question-circle', 'sort_order' => 5)
        );
        
        foreach ($categories as $category) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "meschain_help_categories SET 
                name = '" . $this->db->escape($category['name']) . "',
                description = '" . $this->db->escape($category['description']) . "',
                icon = '" . $this->db->escape($category['icon']) . "',
                sort_order = '" . (int)$category['sort_order'] . "',
                status = '1'
            ");
        }
    }
    
    /**
     * Insert default articles
     */
    private function insertDefaultArticles() {
        $articles = array(
            array(
                'title' => 'MesChain-Sync Kurulum Rehberi',
                'content' => 'MesChain-Sync modülünün kurulum ve ilk ayarlarının yapılması için detaylı rehber...',
                'category' => 'Genel Kullanım',
                'tags' => 'kurulum,başlangıç,ayar',
                'sort_order' => 1
            ),
            array(
                'title' => 'Amazon SP-API Entegrasyonu',
                'content' => 'Amazon SP-API entegrasyonu için gerekli adımlar ve API ayarları...',
                'category' => 'Marketplace Entegrasyonları',
                'tags' => 'amazon,sp-api,entegrasyon',
                'sort_order' => 1
            ),
            array(
                'title' => 'Trendyol API Ayarları',
                'content' => 'Trendyol API entegrasyonu için gerekli ayarlar ve konfigürasyon...',
                'category' => 'Marketplace Entegrasyonları',
                'tags' => 'trendyol,api,ayar',
                'sort_order' => 2
            ),
            array(
                'title' => 'N11 SOAP API Kullanımı',
                'content' => 'N11 SOAP API entegrasyonu ve kullanım rehberi...',
                'category' => 'Marketplace Entegrasyonları',
                'tags' => 'n11,soap,api',
                'sort_order' => 3
            ),
            array(
                'title' => 'Hata Mesajları ve Çözümleri',
                'content' => 'Yaygın hata mesajları ve bunların çözüm yöntemleri...',
                'category' => 'Sorun Giderme',
                'tags' => 'hata,çözüm,debugging',
                'sort_order' => 1
            )
        );
        
        foreach ($articles as $article) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "meschain_help_articles SET 
                title = '" . $this->db->escape($article['title']) . "',
                content = '" . $this->db->escape($article['content']) . "',
                category = '" . $this->db->escape($article['category']) . "',
                tags = '" . $this->db->escape($article['tags']) . "',
                sort_order = '" . (int)$article['sort_order'] . "',
                status = '1',
                views = '0',
                created_at = NOW(),
                updated_at = NOW()
            ");
        }
    }
    
    /**
     * Get help categories
     */
    public function getCategories() {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "meschain_help_categories WHERE status = '1' ORDER BY sort_order, name");
        return $query->rows;
    }
    
    /**
     * Get help articles by category
     */
    public function getArticlesByCategory($category, $start = 0, $limit = 20) {
        $sql = "SELECT * FROM " . DB_PREFIX . "meschain_help_articles WHERE status = '1'";
        
        if ($category && $category != 'all') {
            $sql .= " AND category = '" . $this->db->escape($category) . "'";
        }
        
        $sql .= " ORDER BY sort_order, title";
        
        if ($limit) {
            $sql .= " LIMIT " . (int)$start . "," . (int)$limit;
        }
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * Get article by ID
     */
    public function getArticle($article_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "meschain_help_articles WHERE article_id = '" . (int)$article_id . "' AND status = '1'");
        
        if ($query->num_rows) {
            // Increment view count
            $this->db->query("UPDATE " . DB_PREFIX . "meschain_help_articles SET views = views + 1 WHERE article_id = '" . (int)$article_id . "'");
            return $query->row;
        }
        
        return false;
    }
    
    /**
     * Search articles
     */
    public function searchArticles($keyword, $start = 0, $limit = 20) {
        $sql = "SELECT * FROM " . DB_PREFIX . "meschain_help_articles WHERE status = '1'";
        $sql .= " AND (title LIKE '%" . $this->db->escape($keyword) . "%' OR content LIKE '%" . $this->db->escape($keyword) . "%' OR tags LIKE '%" . $this->db->escape($keyword) . "%')";
        $sql .= " ORDER BY title";
        
        if ($limit) {
            $sql .= " LIMIT " . (int)$start . "," . (int)$limit;
        }
        
        $query = $this->db->query($sql);
        return $query->rows;
    }
    
    /**
     * Get popular articles
     */
    public function getPopularArticles($limit = 5) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "meschain_help_articles WHERE status = '1' ORDER BY views DESC LIMIT " . (int)$limit);
        return $query->rows;
    }
    
    /**
     * Get total articles count
     */
    public function getTotalArticles($category = null) {
        $sql = "SELECT COUNT(*) as total FROM " . DB_PREFIX . "meschain_help_articles WHERE status = '1'";
        
        if ($category && $category != 'all') {
            $sql .= " AND category = '" . $this->db->escape($category) . "'";
        }
        
        $query = $this->db->query($sql);
        return $query->row['total'];
    }
} 