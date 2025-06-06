<?php
/**
 * Log Viewer Model
 * MesChain-Sync log görüntüleme ve yönetimi için model dosyası
 */
class ModelExtensionModuleLogViewer extends Model {
    
    /**
     * Mevcut log dosyalarını listele
     * @return array Log dosyaları
     */
    public function getLogFiles() {
        $log_files = [];
        $log_directory = DIR_LOGS;
        
        if (is_dir($log_directory)) {
            $files = scandir($log_directory);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'log') {
                    $filepath = $log_directory . $file;
                    $log_files[] = [
                        'name' => $file,
                        'size' => filesize($filepath),
                        'modified' => filemtime($filepath),
                        'readable' => is_readable($filepath)
                    ];
                }
            }
            
            // Değiştirilme tarihine göre sırala (en yeni önce)
            usort($log_files, function($a, $b) {
                return $b['modified'] - $a['modified'];
            });
        }
        
        return $log_files;
    }
    
    /**
     * Log dosyası içeriğini oku
     * @param string $filename Log dosyası adı
     * @param int $lines Okunacak satır sayısı (son N satır)
     * @param int $offset Başlangıç pozisyonu
     * @return array Log verileri
     */
    public function readLogFile($filename, $lines = 100, $offset = 0) {
        $log_data = [
            'content' => '',
            'lines' => [],
            'total_lines' => 0,
            'file_size' => 0,
            'last_modified' => null,
            'error' => null
        ];
        
        $filepath = DIR_LOGS . $filename;
        
        // Güvenlik kontrolü - sadece log dizinindeki dosyalar
        if (!$this->isValidLogFile($filepath)) {
            $log_data['error'] = 'Invalid log file or access denied';
            return $log_data;
        }
        
        if (!file_exists($filepath)) {
            $log_data['error'] = 'Log file not found';
            return $log_data;
        }
        
        if (!is_readable($filepath)) {
            $log_data['error'] = 'Log file is not readable';
            return $log_data;
        }
        
        try {
            $log_data['file_size'] = filesize($filepath);
            $log_data['last_modified'] = filemtime($filepath);
            
            // Büyük dosyalar için optimize edilmiş okuma
            if ($log_data['file_size'] > 10 * 1024 * 1024) { // 10MB'dan büyükse
                $log_data = $this->readLargeLogFile($filepath, $lines, $offset);
            } else {
                $content = file_get_contents($filepath);
                $all_lines = explode("\n", $content);
                $log_data['total_lines'] = count($all_lines);
                
                // Son N satırı al
                if ($lines > 0) {
                    $start = max(0, $log_data['total_lines'] - $lines - $offset);
                    $selected_lines = array_slice($all_lines, $start, $lines);
                } else {
                    $selected_lines = $all_lines;
                }
                
                $log_data['lines'] = $this->parseLogLines($selected_lines);
                $log_data['content'] = implode("\n", $selected_lines);
            }
            
        } catch (Exception $e) {
            $log_data['error'] = 'Error reading log file: ' . $e->getMessage();
        }
        
        return $log_data;
    }
    
    /**
     * Büyük log dosyalarını okuma
     * @param string $filepath Dosya yolu
     * @param int $lines İstenilen satır sayısı  
     * @param int $offset Başlangıç pozisyonu
     * @return array Log verileri
     */
    private function readLargeLogFile($filepath, $lines, $offset) {
        $log_data = [
            'content' => '',
            'lines' => [],
            'total_lines' => 0,
            'file_size' => filesize($filepath),
            'last_modified' => filemtime($filepath)
        ];
        
        $file = new SplFileObject($filepath);
        $file->seek(PHP_INT_MAX);
        $log_data['total_lines'] = $file->key() + 1;
        
        // Son satırlardan başlayarak oku
        $start_line = max(0, $log_data['total_lines'] - $lines - $offset);
        $file->seek($start_line);
        
        $selected_lines = [];
        $count = 0;
        
        while (!$file->eof() && $count < $lines) {
            $line = rtrim($file->current());
            if ($line !== '') {
                $selected_lines[] = $line;
                $count++;
            }
            $file->next();
        }
        
        $log_data['lines'] = $this->parseLogLines($selected_lines);
        $log_data['content'] = implode("\n", $selected_lines);
        
        return $log_data;
    }
    
    /**
     * Log satırlarını parse et
     * @param array $lines Log satırları
     * @return array Parse edilmiş log verileri
     */
    private function parseLogLines($lines) {
        $parsed_lines = [];
        
        foreach ($lines as $index => $line) {
            if (empty(trim($line))) continue;
            
            $parsed_line = [
                'line_number' => $index + 1,
                'raw_content' => $line,
                'timestamp' => null,
                'level' => 'INFO',
                'message' => $line,
                'context' => []
            ];
            
            // Timestamp'i çıkart
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
                $parsed_line['timestamp'] = $matches[1];
                $line = preg_replace('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]\s*/', '', $line);
            }
            
            // Log level'ı çıkart
            if (preg_match('/^(DEBUG|INFO|NOTICE|WARNING|ERROR|CRITICAL|ALERT|EMERGENCY):\s*/', $line, $matches)) {
                $parsed_line['level'] = $matches[1];
                $line = preg_replace('/^' . $matches[1] . ':\s*/', '', $line);
            }
            
            // Mesajı temizle
            $parsed_line['message'] = trim($line);
            
            // Seviye rengini belirle
            $parsed_line['level_class'] = $this->getLevelClass($parsed_line['level']);
            
            $parsed_lines[] = $parsed_line;
        }
        
        return $parsed_lines;
    }
    
    /**
     * Log seviye CSS sınıfını getir
     * @param string $level Log seviyesi
     * @return string CSS sınıfı
     */
    private function getLevelClass($level) {
        $classes = [
            'DEBUG' => 'text-muted',
            'INFO' => 'text-info',
            'NOTICE' => 'text-primary',
            'WARNING' => 'text-warning',
            'ERROR' => 'text-danger',
            'CRITICAL' => 'text-danger bg-warning',
            'ALERT' => 'text-white bg-danger',
            'EMERGENCY' => 'text-white bg-dark'
        ];
        
        return $classes[$level] ?? 'text-secondary';
    }
    
    /**
     * Log dosyası güvenlik kontrolü
     * @param string $filepath Dosya yolu
     * @return bool Geçerli mi?
     */
    private function isValidLogFile($filepath) {
        // Sadece log dizinindeki dosyalar
        $log_directory = realpath(DIR_LOGS);
        $real_filepath = realpath($filepath);
        
        if (!$real_filepath) {
            return false;
        }
        
        // Path traversal saldırılarına karşı kontrol
        if (strpos($real_filepath, $log_directory) !== 0) {
            return false;
        }
        
        // Sadece .log uzantılı dosyalar
        if (pathinfo($real_filepath, PATHINFO_EXTENSION) !== 'log') {
            return false;
        }
        
        return true;
    }
    
    /**
     * Log dosyasını sil
     * @param string $filename Log dosyası adı
     * @return bool Başarılı mı?
     */
    public function deleteLogFile($filename) {
        $filepath = DIR_LOGS . $filename;
        
        if (!$this->isValidLogFile($filepath)) {
            return false;
        }
        
        if (!file_exists($filepath)) {
            return false;
        }
        
        return unlink($filepath);
    }
    
    /**
     * Log dosyasını temizle (içeriği sil)
     * @param string $filename Log dosyası adı
     * @return bool Başarılı mı?
     */
    public function clearLogFile($filename) {
        $filepath = DIR_LOGS . $filename;
        
        if (!$this->isValidLogFile($filepath)) {
            return false;
        }
        
        if (!file_exists($filepath)) {
            return false;
        }
        
        return file_put_contents($filepath, '') !== false;
    }
    
    /**
     * Log dosyalarını arşivle
     * @param array $filenames Arşivlenecek dosya adları
     * @return string|false Arşiv dosya yolu veya false
     */
    public function archiveLogFiles($filenames) {
        if (empty($filenames)) {
            return false;
        }
        
        $archive_name = 'meschain_logs_' . date('Y_m_d_H_i_s') . '.zip';
        $archive_path = DIR_LOGS . $archive_name;
        
        if (!class_exists('ZipArchive')) {
            return false;
        }
        
        $zip = new ZipArchive();
        if ($zip->open($archive_path, ZipArchive::CREATE) !== TRUE) {
            return false;
        }
        
        foreach ($filenames as $filename) {
            $filepath = DIR_LOGS . $filename;
            if ($this->isValidLogFile($filepath) && file_exists($filepath)) {
                $zip->addFile($filepath, $filename);
            }
        }
        
        $zip->close();
        
        return file_exists($archive_path) ? $archive_path : false;
    }
    
    /**
     * Log istatistiklerini getir
     * @return array İstatistikler
     */
    public function getLogStats() {
        $stats = [
            'total_files' => 0,
            'total_size' => 0,
            'largest_file' => null,
            'newest_file' => null,
            'oldest_file' => null,
            'by_type' => []
        ];
        
        $log_files = $this->getLogFiles();
        $stats['total_files'] = count($log_files);
        
        if (empty($log_files)) {
            return $stats;
        }
        
        $largest_size = 0;
        $newest_time = 0;
        $oldest_time = PHP_INT_MAX;
        
        foreach ($log_files as $file) {
            $stats['total_size'] += $file['size'];
            
            // En büyük dosya
            if ($file['size'] > $largest_size) {
                $largest_size = $file['size'];
                $stats['largest_file'] = $file['name'];
            }
            
            // En yeni dosya
            if ($file['modified'] > $newest_time) {
                $newest_time = $file['modified'];
                $stats['newest_file'] = $file['name'];
            }
            
            // En eski dosya
            if ($file['modified'] < $oldest_time) {
                $oldest_time = $file['modified'];
                $stats['oldest_file'] = $file['name'];
            }
            
            // Tipe göre grupla
            $type = $this->getLogFileType($file['name']);
            if (!isset($stats['by_type'][$type])) {
                $stats['by_type'][$type] = ['count' => 0, 'size' => 0];
            }
            $stats['by_type'][$type]['count']++;
            $stats['by_type'][$type]['size'] += $file['size'];
        }
        
        return $stats;
    }
    
    /**
     * Log dosya tipini belirle
     * @param string $filename Dosya adı
     * @return string Dosya tipi
     */
    private function getLogFileType($filename) {
        if (strpos($filename, 'error') !== false) return 'error';
        if (strpos($filename, 'trendyol') !== false) return 'trendyol';
        if (strpos($filename, 'n11') !== false) return 'n11';
        if (strpos($filename, 'amazon') !== false) return 'amazon';
        if (strpos($filename, 'hepsiburada') !== false) return 'hepsiburada';
        if (strpos($filename, 'ebay') !== false) return 'ebay';
        if (strpos($filename, 'ozon') !== false) return 'ozon';
        if (strpos($filename, 'webhook') !== false) return 'webhook';
        if (strpos($filename, 'api') !== false) return 'api';
        
        return 'system';
    }
} 