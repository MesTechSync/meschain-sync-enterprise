<?php
/**
 * logger.php
 *
 * MesChain Sync modülü için log kütüphanesi
 */
class MeschainLogger {
    private $file;
    private $directory;
    
    /**
     * Yapılandırıcı
     *
     * @param string $filename Log dosyası adı
     */
    public function __construct($filename) {
        $this->directory = DIR_LOGS;
        $this->file = $filename;
        
        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0777, true);
        }
    }
    
    /**
     * Log kaydı ekle
     *
     * @param string $user Kullanıcı adı veya rolü
     * @param string $action İşlem adı
     * @param string $message Log mesajı
     * @return void
     */
    public function write($user, $action, $message) {
        $log_file = $this->directory . $this->file;
        $date = date('Y-m-d H:i:s');
        $log = "[$date] [$user] [$action] $message\n";
        file_put_contents($log_file, $log, FILE_APPEND);
    }
    
    /**
     * Log dosyasını temizle
     *
     * @return void
     */
    public function clear() {
        $log_file = $this->directory . $this->file;
        file_put_contents($log_file, '');
    }
    
    /**
     * Log dosyasını oku
     *
     * @param int $lines Okunacak satır sayısı (0 = tümü)
     * @return array Log kayıtları
     */
    public function read($lines = 0) {
        $log_file = $this->directory . $this->file;
        $entries = array();
        
        if (!file_exists($log_file)) {
            return $entries;
        }
        
        $file_content = file($log_file);
        
        if ($lines > 0) {
            $file_content = array_slice($file_content, -$lines);
        }
        
        foreach ($file_content as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]
            if (preg_match('/\[(.*?)\] \[(.*?)\] \[(.*?)\] (.*)/i', $line, $matches)) {
                $entries[] = array(
                    'date' => $matches[1],
                    'user' => $matches[2],
                    'action' => $matches[3],
                    'message' => $matches[4]
                );
            }
        }
        
        return $entries;
    }
    
    /**
     * Log dosyasını indir
     *
     * @return void
     */
    public function download() {
        $log_file = $this->directory . $this->file;
        
        if (file_exists($log_file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $this->file . '"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($log_file));
            
            readfile($log_file);
            exit;
        }
    }
    
    /**
     * Log istatistiklerini getir
     *
     * @return array İstatistikler
     */
    public function getStats() {
        $entries = $this->read();
        
        $stats = array(
            'total' => count($entries),
            'users' => array(),
            'actions' => array(),
            'errors' => array(),
            'date_range' => '',
            'most_active_users' => array(),
            'most_common_errors' => array()
        );
        
        if (empty($entries)) {
            return $stats;
        }
        
        $min_date = null;
        $max_date = null;
        
        foreach ($entries as $entry) {
            // Kullanıcı istatistikleri
            if (!isset($stats['users'][$entry['user']])) {
                $stats['users'][$entry['user']] = 0;
            }
            $stats['users'][$entry['user']]++;
            
            // İşlem istatistikleri
            if (!isset($stats['actions'][$entry['action']])) {
                $stats['actions'][$entry['action']] = 0;
            }
            $stats['actions'][$entry['action']]++;
            
            // Hata istatistikleri
            if (stripos($entry['action'], 'ERROR') !== false || stripos($entry['action'], 'HATA') !== false) {
                if (!isset($stats['errors'][$entry['message']])) {
                    $stats['errors'][$entry['message']] = 0;
                }
                $stats['errors'][$entry['message']]++;
            }
            
            // Tarih aralığı
            $date = strtotime($entry['date']);
            if ($min_date === null || $date < $min_date) {
                $min_date = $date;
            }
            if ($max_date === null || $date > $max_date) {
                $max_date = $date;
            }
        }
        
        // Tarih aralığı
        if ($min_date && $max_date) {
            $stats['date_range'] = date('Y-m-d H:i:s', $min_date) . ' - ' . date('Y-m-d H:i:s', $max_date);
        }
        
        // En aktif kullanıcılar
        arsort($stats['users']);
        $stats['most_active_users'] = array_slice($stats['users'], 0, 5, true);
        
        // En yaygın hatalar
        arsort($stats['errors']);
        $stats['most_common_errors'] = array_slice($stats['errors'], 0, 5, true);
        
        return $stats;
    }
} 