<?php
/**
 * Cache Helper Class
 * 
 * Cache sistemi için merkezi yönetim sınıfı. Tag'ler ile ilişkili cache'leri
 * yönetmek için kullanılır.
 */
class CacheHelper {
    private static $instance;
    private $cache; // OpenCart cache instance
    private $db;
    private $config;
    private $ttl = 3600; // Default 1 saat
    private $tag_prefix = 'tag_';
    private $data_prefix = 'data_';
    
    private function __construct() {
        $registry = Registry::getInstance();
        $this->cache = $registry->get('cache');
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
        
        // Cache TTL ayarını config'den al
        if ($this->config->get('meschain_cache_ttl')) {
            $this->ttl = (int)$this->config->get('meschain_cache_ttl');
        }
    }
    
    /**
     * Singleton pattern
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * API yanıtını cache'e kaydet
     * 
     * @param string $marketplace Pazaryeri adı
     * @param string $endpoint API endpoint
     * @param array $params İstek parametreleri 
     * @param mixed $data Cache'lenecek veri
     * @param array $tags Cache tag'leri
     * @return bool Başarılı mı
     */
    public function setApiResponse($marketplace, $endpoint, $params, $data, $tags = array()) {
        $key = $this->generateCacheKey($marketplace, $endpoint, $params);
        $expiry = time() + $this->ttl;
        
        // Cache veriyi kaydet
        $success = $this->cache->set($this->data_prefix . $key, array(
            'data' => $data,
            'expiry' => $expiry,
            'tags' => $tags
        ));
        
        if ($success) {
            // Tag'leri kaydet
            foreach ($tags as $tag) {
                $tag_keys = $this->cache->get($this->tag_prefix . $tag) ?: array();
                if (!in_array($key, $tag_keys)) {
                    $tag_keys[] = $key;
                    $this->cache->set($this->tag_prefix . $tag, $tag_keys);
                }
            }
            
            // İstatistikleri güncelle
            $this->updateStats('set', $marketplace);
        }
        
        return $success;
    }
    
    /**
     * Cache'den API yanıtı getir
     * 
     * @param string $marketplace Pazaryeri adı
     * @param string $endpoint API endpoint
     * @param array $params İstek parametreleri
     * @return mixed|bool Cache verisi veya false
     */
    public function getApiResponse($marketplace, $endpoint, $params) {
        $key = $this->generateCacheKey($marketplace, $endpoint, $params);
        $cached = $this->cache->get($this->data_prefix . $key);
        
        if ($cached && is_array($cached)) {
            if ($cached['expiry'] > time()) {
                $this->updateStats('hit', $marketplace);
                return $cached['data'];
            } else {
                // Süresi dolmuş, sil
                $this->deleteCache($key);
                $this->updateStats('expired', $marketplace);
            }
        } else {
            $this->updateStats('miss', $marketplace);
        }
        
        return false;
    }
    
    /**
     * Tag ile ilişkili tüm cache'leri sil
     * 
     * @param string $tag Cache tag
     * @return bool Başarılı mı
     */
    public function deleteByTag($tag) {
        $tag_keys = $this->cache->get($this->tag_prefix . $tag);
        
        if (!$tag_keys) {
            return false;
        }
        
        foreach ($tag_keys as $key) {
            $this->deleteCache($key);
        }
        
        // Tag'i sil
        $this->cache->delete($this->tag_prefix . $tag);
        
        return true;
    }
    
    /**
     * Tüm cache'i temizle
     * 
     * @return bool Başarılı mı
     */
    public function clearAll() {
        return $this->cache->flush();
    }
    
    /**
     * Cache istatistiklerini getir
     * 
     * @return array İstatistik verisi
     */
    public function getStats() {
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "meschain_cache_stats 
            WHERE date = CURDATE()
        ");
        
        if ($query->num_rows) {
            return $query->row;
        }
        
        return array(
            'hits' => 0,
            'misses' => 0,
            'expired' => 0
        );
    }
    
    /**
     * Cache istatistiklerini güncelle
     * 
     * @param string $type İşlem tipi (hit, miss, expired, set)
     * @param string $marketplace Pazaryeri adı
     */
    private function updateStats($type, $marketplace) {
        $date = date('Y-m-d');
        
        // Günün istatistik kaydını al/oluştur
        $query = $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_cache_stats 
                (date, marketplace, hits, misses, expired, sets)
            VALUES 
                ('" . $date . "', '" . $this->db->escape($marketplace) . "', 0, 0, 0, 0)
            ON DUPLICATE KEY UPDATE
                date = VALUES(date)
        ");
        
        // İstatistiği güncelle
        $this->db->query("
            UPDATE " . DB_PREFIX . "meschain_cache_stats 
            SET " . $type . "s = " . $type . "s + 1
            WHERE date = '" . $date . "'
            AND marketplace = '" . $this->db->escape($marketplace) . "'
        ");
    }
    
    /**
     * Cache key oluştur
     */
    private function generateCacheKey($marketplace, $endpoint, $params) {
        return md5($marketplace . '_' . $endpoint . '_' . serialize($params));
    }
    
    /**
     * Tek bir cache anahtarını sil
     */
    private function deleteCache($key) {
        $cached = $this->cache->get($this->data_prefix . $key);
        
        if ($cached && isset($cached['tags'])) {
            // Tag listelerinden kaldır
            foreach ($cached['tags'] as $tag) {
                $tag_keys = $this->cache->get($this->tag_prefix . $tag) ?: array();
                $tag_keys = array_diff($tag_keys, array($key));
                if (!empty($tag_keys)) {
                    $this->cache->set($this->tag_prefix . $tag, $tag_keys);
                } else {
                    $this->cache->delete($this->tag_prefix . $tag);
                }
            }
        }
        
        return $this->cache->delete($this->data_prefix . $key);
    }
}
