<?php
/**
 * meschain_log.php
 *
 * Amaç: MesChain-Sync modülü için kapsamlı loglama sistemi
 * Bu dosya, modülün çeşitli bileşenleri tarafından kullanılan merkezi bir loglama sistemi sağlar.
 * 
 * Kullanım:
 * $log = new MesChainLog('trendyol');
 * $log->write('info', 'API isteği gönderildi', $data);
 */
class MesChainLog {
    private $logPath;
    private $module;
    private $maxLogSize = 5242880; // 5MB
    private $maxLogFiles = 5;
    
    /**
     * Constructor
     * 
     * @param string $module Log modülü adı (örn. 'trendyol', 'n11', 'system')
     */
    public function __construct($module = 'general') {
        $this->module = preg_replace('/[^a-z0-9_-]/', '', strtolower($module));
        $this->logPath = DIR_LOGS . 'meschain_' . $this->module . '.log';
        
        // Log dosyası boyut kontrolü
        $this->checkLogSize();
    }
    
    /**
     * Log mesajı yazar
     * 
     * @param string $level Log seviyesi ('debug', 'info', 'warning', 'error', 'critical')
     * @param string $message Log mesajı
     * @param array|object|string $data İsteğe bağlı veri
     * @return bool
     */
    public function write($level = 'info', $message, $data = null) {
        $levels = array('debug', 'info', 'warning', 'error', 'critical');
        
        if (!in_array($level, $levels)) {
            $level = 'info';
        }
        
        $log = array(
            'datetime' => date('Y-m-d H:i:s'),
            'level' => strtoupper($level),
            'module' => $this->module,
            'message' => $message
        );
        
        if ($data !== null) {
            if (is_array($data) || is_object($data)) {
                $log['data'] = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            } else {
                $log['data'] = (string)$data;
            }
        }
        
        // Log formatı
        $logEntry = '[' . $log['datetime'] . '] [' . $log['level'] . '] [' . $log['module'] . '] ' . $log['message'];
        
        if (isset($log['data'])) {
            $logEntry .= "\nData: " . $log['data'];
        }
        
        $logEntry .= "\n" . str_repeat('-', 80) . "\n";
        
        // Log dosyasına yazma
        return file_put_contents($this->logPath, $logEntry, FILE_APPEND);
    }
    
    /**
     * Debug log mesajı
     * 
     * @param string $message Log mesajı
     * @param array|object|string $data İsteğe bağlı veri
     * @return bool
     */
    public function debug($message, $data = null) {
        return $this->write('debug', $message, $data);
    }
    
    /**
     * Info log mesajı
     * 
     * @param string $message Log mesajı
     * @param array|object|string $data İsteğe bağlı veri
     * @return bool
     */
    public function info($message, $data = null) {
        return $this->write('info', $message, $data);
    }
    
    /**
     * Warning log mesajı
     * 
     * @param string $message Log mesajı
     * @param array|object|string $data İsteğe bağlı veri
     * @return bool
     */
    public function warning($message, $data = null) {
        return $this->write('warning', $message, $data);
    }
    
    /**
     * Error log mesajı
     * 
     * @param string $message Log mesajı
     * @param array|object|string $data İsteğe bağlı veri
     * @return bool
     */
    public function error($message, $data = null) {
        return $this->write('error', $message, $data);
    }
    
    /**
     * Critical log mesajı
     * 
     * @param string $message Log mesajı
     * @param array|object|string $data İsteğe bağlı veri
     * @return bool
     */
    public function critical($message, $data = null) {
        return $this->write('critical', $message, $data);
    }
    
    /**
     * Log boyutunu kontrol eder ve gerekirse rotasyon uygular
     */
    private function checkLogSize() {
        if (file_exists($this->logPath) && filesize($this->logPath) > $this->maxLogSize) {
            $this->rotateLog();
        }
    }
    
    /**
     * Log rotasyonu
     */
    private function rotateLog() {
        // Eski rotasyon dosyalarını kaydır
        for ($i = $this->maxLogFiles - 1; $i > 0; $i--) {
            $oldFile = $this->logPath . '.' . $i;
            $newFile = $this->logPath . '.' . ($i + 1);
            
            if (file_exists($oldFile)) {
                if ($i == $this->maxLogFiles - 1) {
                    @unlink($oldFile);
                } else {
                    @rename($oldFile, $newFile);
                }
            }
        }
        
        // Mevcut log dosyasını .1 olarak kaydet
        @rename($this->logPath, $this->logPath . '.1');
        
        // Yeni bir log dosyası oluştur
        $message = "Log rotasyonu gerçekleştirildi - " . date('Y-m-d H:i:s') . "\n";
        file_put_contents($this->logPath, $message);
    }
    
    /**
     * Log içeriğini okur
     * 
     * @param int $lines Okunacak satır sayısı (0: tüm dosya)
     * @return string
     */
    public function read($lines = 0) {
        if (!file_exists($this->logPath)) {
            return '';
        }
        
        if ($lines <= 0) {
            return file_get_contents($this->logPath);
        }
        
        $file = new SplFileObject($this->logPath, 'r');
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();
        
        $output = '';
        $startLine = max(0, $totalLines - $lines);
        
        $file->seek($startLine);
        
        while (!$file->eof()) {
            $output .= $file->current();
            $file->next();
        }
        
        return $output;
    }
    
    /**
     * Log dosyasını temizler
     * 
     * @return bool
     */
    public function clear() {
        return file_put_contents($this->logPath, '');
    }
} 