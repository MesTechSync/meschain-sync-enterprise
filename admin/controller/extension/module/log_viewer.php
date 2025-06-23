<?php
/**
 * log_viewer.php
 *
 * Amaç: MesChain Sync modülü için log görüntüleme ve analiz controller dosyası.
 *
 * Loglama: Log görüntüleme işlemleri log_viewer.log dosyasına kaydedilir.
 * Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]
 */

class ControllerExtensionModuleLogViewer extends Controller {
    /**
     * Ana sayfa
     */
    public function index() {
        $this->load->language('extension/module/log_viewer');
        $this->document->setTitle($this->language->get('heading_title'));
        
        // CSS dosyasını yükle
        $this->document->addStyle('view/template/extension/module/meschain_theme.css');
        
        // JavaScript dosyasını yükle
        $this->document->addScript('view/javascript/extension/module/log_viewer.js');
        
        // Dil değişkenlerini yükle
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_list'] = $this->language->get('text_list');
        $data['text_no_results'] = $this->language->get('text_no_results');
        $data['text_confirm'] = $this->language->get('text_confirm');
        $data['text_filter'] = $this->language->get('text_filter');
        $data['text_date'] = $this->language->get('text_date');
        $data['text_user'] = $this->language->get('text_user');
        $data['text_action'] = $this->language->get('text_action');
        $data['text_message'] = $this->language->get('text_message');
        $data['text_log_files'] = $this->language->get('text_log_files');
        $data['text_download'] = $this->language->get('text_download');
        $data['text_delete'] = $this->language->get('text_delete');
        $data['text_refresh'] = $this->language->get('text_refresh');
        $data['text_clear'] = $this->language->get('text_clear');
        $data['text_log_info'] = $this->language->get('text_log_info');
        $data['text_log_size'] = $this->language->get('text_log_size');
        $data['text_log_entries'] = $this->language->get('text_log_entries');
        $data['text_log_date_range'] = $this->language->get('text_log_date_range');
        $data['text_error_stats'] = $this->language->get('text_error_stats');
        $data['text_most_common_errors'] = $this->language->get('text_most_common_errors');
        $data['text_most_active_users'] = $this->language->get('text_most_active_users');
        
        $data['column_date'] = $this->language->get('column_date');
        $data['column_user'] = $this->language->get('column_user');
        $data['column_action'] = $this->language->get('column_action');
        $data['column_message'] = $this->language->get('column_message');
        $data['column_count'] = $this->language->get('column_count');
        
        $data['entry_date_start'] = $this->language->get('entry_date_start');
        $data['entry_date_end'] = $this->language->get('entry_date_end');
        $data['entry_user'] = $this->language->get('entry_user');
        $data['entry_action'] = $this->language->get('entry_action');
        $data['entry_message'] = $this->language->get('entry_message');
        $data['entry_log_file'] = $this->language->get('entry_log_file');
        
        $data['button_filter'] = $this->language->get('button_filter');
        $data['button_clear'] = $this->language->get('button_clear');
        $data['button_download'] = $this->language->get('button_download');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_refresh'] = $this->language->get('button_refresh');
        
        // Breadcrumb
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/log_viewer', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Linkler
        $data['action'] = $this->url->link('extension/module/log_viewer', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true);
        $data['download'] = $this->url->link('extension/module/log_viewer/download', 'user_token=' . $this->session->data['user_token'], true);
        $data['delete'] = $this->url->link('extension/module/log_viewer/delete', 'user_token=' . $this->session->data['user_token'], true);
        $data['clear'] = $this->url->link('extension/module/log_viewer/clear', 'user_token=' . $this->session->data['user_token'], true);
        
        // Filtreler
        $data['filter_date_start'] = isset($this->request->get['filter_date_start']) ? $this->request->get['filter_date_start'] : '';
        $data['filter_date_end'] = isset($this->request->get['filter_date_end']) ? $this->request->get['filter_date_end'] : '';
        $data['filter_user'] = isset($this->request->get['filter_user']) ? $this->request->get['filter_user'] : '';
        $data['filter_action'] = isset($this->request->get['filter_action']) ? $this->request->get['filter_action'] : '';
        $data['filter_message'] = isset($this->request->get['filter_message']) ? $this->request->get['filter_message'] : '';
        
        // Log dosyaları
        $log_files = $this->getLogFiles();
        $data['log_files'] = array();
        
        foreach ($log_files as $file) {
            $data['log_files'][] = array(
                'name' => basename($file),
                'size' => $this->formatSize(filesize($file)),
                'modified' => date('Y-m-d H:i:s', filemtime($file)),
                'download' => $this->url->link('extension/module/log_viewer/download', 'user_token=' . $this->session->data['user_token'] . '&file=' . urlencode(basename($file)), true),
                'delete' => $this->url->link('extension/module/log_viewer/delete', 'user_token=' . $this->session->data['user_token'] . '&file=' . urlencode(basename($file)), true),
                'view' => $this->url->link('extension/module/log_viewer', 'user_token=' . $this->session->data['user_token'] . '&file=' . urlencode(basename($file)), true)
            );
        }
        
        // Seçilen log dosyası
        $selected_file = isset($this->request->get['file']) ? $this->request->get['file'] : 'meschain.log';
        $data['selected_file'] = $selected_file;
        
        // Log kayıtları
        $log_entries = $this->getLogEntries($selected_file, $data['filter_date_start'], $data['filter_date_end'], $data['filter_user'], $data['filter_action'], $data['filter_message']);
        $data['log_entries'] = $log_entries;
        
        // Log istatistikleri
        $stats = $this->getLogStats($log_entries);
        $data['stats'] = $stats;
        
        // Log dosya bilgileri
        $file_path = DIR_LOGS . $selected_file;
        if (file_exists($file_path)) {
            $data['log_info'] = array(
                'name' => $selected_file,
                'size' => $this->formatSize(filesize($file_path)),
                'modified' => date('Y-m-d H:i:s', filemtime($file_path)),
                'entries' => count($log_entries),
                'date_range' => $stats['date_range']
            );
        } else {
            $data['log_info'] = array(
                'name' => $selected_file,
                'size' => '0 B',
                'modified' => '',
                'entries' => 0,
                'date_range' => ''
            );
        }
        
        // Başarı mesajı
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        // Hata mesajı
        if (isset($this->session->data['error'])) {
            $data['error_warning'] = $this->session->data['error'];
            unset($this->session->data['error']);
        } else {
            $data['error_warning'] = '';
        }
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        // Log
        $this->writeLog($this->user->getUserName(), 'VIEW', 'Log görüntüleyici açıldı: ' . $selected_file);
        
        $this->response->setOutput($this->load->view('extension/module/log_viewer', $data));
    }
    
    /**
     * Log dosyasını indir
     */
    public function download() {
        $this->load->language('extension/module/log_viewer');
        
        if (!$this->user->hasPermission('modify', 'extension/module/log_viewer')) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/module/log_viewer', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        $file = isset($this->request->get['file']) ? basename($this->request->get['file']) : 'meschain.log';
        $file_path = DIR_LOGS . $file;
        
        if (file_exists($file_path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $file . '"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_path));
            
            readfile($file_path);
            
            $this->writeLog($this->user->getUserName(), 'DOWNLOAD', 'Log dosyası indirildi: ' . $file);
            exit;
        } else {
            $this->session->data['error'] = $this->language->get('error_file_not_found');
            $this->response->redirect($this->url->link('extension/module/log_viewer', 'user_token=' . $this->session->data['user_token'], true));
        }
    }
    
    /**
     * Log dosyasını sil
     */
    public function delete() {
        $this->load->language('extension/module/log_viewer');
        
        if (!$this->user->hasPermission('modify', 'extension/module/log_viewer')) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/module/log_viewer', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        $file = isset($this->request->get['file']) ? basename($this->request->get['file']) : '';
        $file_path = DIR_LOGS . $file;
        
        if ($file && file_exists($file_path)) {
            unlink($file_path);
            $this->session->data['success'] = $this->language->get('text_success_delete');
            $this->writeLog($this->user->getUserName(), 'DELETE', 'Log dosyası silindi: ' . $file);
        } else {
            $this->session->data['error'] = $this->language->get('error_file_not_found');
        }
        
        $this->response->redirect($this->url->link('extension/module/log_viewer', 'user_token=' . $this->session->data['user_token'], true));
    }
    
    /**
     * Log dosyasını temizle
     */
    public function clear() {
        $this->load->language('extension/module/log_viewer');
        
        if (!$this->user->hasPermission('modify', 'extension/module/log_viewer')) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/module/log_viewer', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        $file = isset($this->request->get['file']) ? basename($this->request->get['file']) : 'meschain.log';
        $file_path = DIR_LOGS . $file;
        
        if (file_exists($file_path)) {
            file_put_contents($file_path, '');
            $this->session->data['success'] = $this->language->get('text_success_clear');
            $this->writeLog($this->user->getUserName(), 'CLEAR', 'Log dosyası temizlendi: ' . $file);
        } else {
            $this->session->data['error'] = $this->language->get('error_file_not_found');
        }
        
        $this->response->redirect($this->url->link('extension/module/log_viewer', 'user_token=' . $this->session->data['user_token'] . '&file=' . urlencode($file), true));
    }
    
    /**
     * Log dosyalarını getir
     */
    private function getLogFiles() {
        $files = glob(DIR_LOGS . '*.log');
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        return $files;
    }
    
    /**
     * Log kayıtlarını getir
     */
    private function getLogEntries($file, $date_start = '', $date_end = '', $user = '', $action = '', $message = '') {
        $file_path = DIR_LOGS . $file;
        $entries = array();
        
        if (!file_exists($file_path)) {
            return $entries;
        }
        
        $lines = file($file_path);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]
            if (preg_match('/\[(.*?)\] \[(.*?)\] \[(.*?)\] (.*)/i', $line, $matches)) {
                $date = $matches[1];
                $user_name = $matches[2];
                $action_name = $matches[3];
                $message_text = $matches[4];
                
                // Filtrele
                if ($date_start && strtotime($date) < strtotime($date_start)) continue;
                if ($date_end && strtotime($date) > strtotime($date_end . ' 23:59:59')) continue;
                if ($user && stripos($user_name, $user) === false) continue;
                if ($action && stripos($action_name, $action) === false) continue;
                if ($message && stripos($message_text, $message) === false) continue;
                
                $entries[] = array(
                    'date' => $date,
                    'user' => $user_name,
                    'action' => $action_name,
                    'message' => $message_text
                );
            }
        }
        
        // En yeni kayıtlar üstte
        usort($entries, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return $entries;
    }
    
    /**
     * Log istatistiklerini getir
     */
    private function getLogStats($entries) {
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
    
    /**
     * Boyut formatla
     */
    private function formatSize($bytes) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Kurulum
     */
    public function install() {
        // Yetki ekle
        $this->load->model('user/user_group');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/log_viewer');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/log_viewer');
        
        $this->writeLog('SYSTEM', 'INSTALL', 'Log görüntüleyici modülü kuruldu');
    }
    
    /**
     * Kaldırma
     */
    public function uninstall() {
        $this->writeLog('SYSTEM', 'UNINSTALL', 'Log görüntüleyici modülü kaldırıldı');
    }
    
    /**
     * Log kaydı
     */
    private function writeLog($user, $action, $message) {
        $log_file = DIR_LOGS . 'log_viewer.log';
        $date = date('Y-m-d H:i:s');
        $log = "[$date] [$user] [$action] $message\n";
        file_put_contents($log_file, $log, FILE_APPEND);
    }
} 