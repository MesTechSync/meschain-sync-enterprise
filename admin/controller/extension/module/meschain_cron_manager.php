<?php
/**
 * MesChain Cron Job Management Controller
 * Cron job'ları ve queue yönetimi için admin paneli
 * @author MesChain-Sync Team
 * @version 1.0.0
 */

class ControllerExtensionModuleMeschainCronManager extends Controller {
    
    public function index() {
        $this->load->language('extension/module/meschain_cron_manager');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/meschain_cron_manager', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Cron job'ları getir
        $data['cron_jobs'] = $this->getCronJobs();
        
        // Queue istatistiği getir
        $data['queue_stats'] = $this->getQueueStats();
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/meschain_cron_manager', $data));
    }
    
    /**
     * Manuel cron çalıştırma
     */
    public function runCron() {
        $json = array();
        
        try {
            // Logger'ı yükle
            require_once(DIR_SYSTEM . 'library/meschain/logger/logger.php');
            $logger = new MesChainLogger('cron_manager');
            
            // Cron manager'ı yükle
            require_once(DIR_SYSTEM . 'library/meschain/scheduler/cron_manager.php');
            $cron_manager = new MesChainCronManager($this->db, $logger, $this->config);
            
            // Scheduler'ı çalıştır
            $cron_manager->runScheduler();
            
            $json['success'] = 'Cron jobları başarıyla çalıştırıldı';
            
        } catch (Exception $e) {
            $json['error'] = 'Cron çalıştırma hatası: ' . $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Queue işleme
     */
    public function processQueue() {
        $json = array();
        
        try {
            // Logger'ı yükle
            require_once(DIR_SYSTEM . 'library/meschain/logger/logger.php');
            $logger = new MesChainLogger('queue_manager');
            
            // Queue manager'ı yükle
            require_once(DIR_SYSTEM . 'library/meschain/queue/queue_manager.php');
            $queue_manager = new MesChainQueueManager($this->db, $logger, $this->config);
            
            // Queue'yu işle
            $queue_manager->processQueue(20); // 20 job işle
            
            $json['success'] = 'Queue başarıyla işlendi';
            
        } catch (Exception $e) {
            $json['error'] = 'Queue işleme hatası: ' . $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Yeni cron job ekleme
     */
    public function addCronJob() {
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $name = $this->request->post['name'] ?? '';
            $job_type = $this->request->post['job_type'] ?? '';
            $marketplace = $this->request->post['marketplace'] ?? '';
            $frequency = $this->request->post['frequency'] ?? 'hourly';
            $priority = (int)($this->request->post['priority'] ?? 1);
            $parameters = $this->request->post['parameters'] ?? '{}';
            
            if (empty($name) || empty($job_type)) {
                $json['error'] = 'Ad ve job tipi gerekli';
            } else {
                try {
                    $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_cron_jobs` SET
                        name = '" . $this->db->escape($name) . "',
                        job_type = '" . $this->db->escape($job_type) . "',
                        marketplace = '" . $this->db->escape($marketplace) . "',
                        frequency = '" . $this->db->escape($frequency) . "',
                        priority = " . (int)$priority . ",
                        parameters = '" . $this->db->escape($parameters) . "',
                        is_active = 1,
                        status = 'pending',
                        date_added = NOW()");
                    
                    $json['success'] = 'Cron job başarıyla eklendi';
                    
                } catch (Exception $e) {
                    $json['error'] = 'Cron job ekleme hatası: ' . $e->getMessage();
                }
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Cron job durumunu değiştir
     */
    public function toggleCronJob() {
        $json = array();
        
        $job_id = (int)($this->request->post['job_id'] ?? 0);
        $is_active = (int)($this->request->post['is_active'] ?? 0);
        
        if ($job_id > 0) {
            try {
                $this->db->query("UPDATE `" . DB_PREFIX . "meschain_cron_jobs` SET
                    is_active = " . (int)$is_active . "
                    WHERE id = " . (int)$job_id);
                
                $json['success'] = 'Cron job durumu güncellendi';
                
            } catch (Exception $e) {
                $json['error'] = 'Güncelleme hatası: ' . $e->getMessage();
            }
        } else {
            $json['error'] = 'Geçersiz job ID';
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Cron job'ları getir
     */
    private function getCronJobs() {
        $query = $this->db->query("
            SELECT * FROM `" . DB_PREFIX . "meschain_cron_jobs` 
            ORDER BY priority DESC, date_added DESC
        ");
        
        return $query->rows;
    }
    
    /**
     * Queue istatistikleri getir
     */
    private function getQueueStats() {
        try {
            // Logger'ı yükle
            require_once(DIR_SYSTEM . 'library/meschain/logger/logger.php');
            $logger = new MesChainLogger('queue_stats');
            
            // Queue manager'ı yükle
            require_once(DIR_SYSTEM . 'library/meschain/queue/queue_manager.php');
            $queue_manager = new MesChainQueueManager($this->db, $logger, $this->config);
            
            return $queue_manager->getQueueStats();
            
        } catch (Exception $e) {
            return array();
        }
    }
    
    /**
     * Queue temizleme
     */
    public function cleanupQueue() {
        $json = array();
        
        try {
            // Logger'ı yükle
            require_once(DIR_SYSTEM . 'library/meschain/logger/logger.php');
            $logger = new MesChainLogger('queue_cleanup');
            
            // Queue manager'ı yükle
            require_once(DIR_SYSTEM . 'library/meschain/queue/queue_manager.php');
            $queue_manager = new MesChainQueueManager($this->db, $logger, $this->config);
            
            // Tamamlanan job'ları temizle
            $deleted = $queue_manager->cleanupCompletedJobs();
            
            $json['success'] = "{$deleted} tamamlanan job temizlendi";
            
        } catch (Exception $e) {
            $json['error'] = 'Temizleme hatası: ' . $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Sistem durumu kontrolü
     */
    public function systemStatus() {
        $json = array();
        
        try {
            // Cron job durumları
            $cron_status = $this->db->query("
                SELECT status, COUNT(*) as count 
                FROM `" . DB_PREFIX . "meschain_cron_jobs` 
                WHERE is_active = 1 
                GROUP BY status
            ");
            
            $json['cron_jobs'] = array();
            foreach ($cron_status->rows as $row) {
                $json['cron_jobs'][$row['status']] = $row['count'];
            }
            
            // Queue durumları
            $queue_status = $this->db->query("
                SELECT status, COUNT(*) as count 
                FROM `" . DB_PREFIX . "meschain_queue` 
                GROUP BY status
            ");
            
            $json['queue'] = array();
            foreach ($queue_status->rows as $row) {
                $json['queue'][$row['status']] = $row['count'];
            }
            
            // Son çalışan job'lar
            $recent_jobs = $this->db->query("
                SELECT name, status, last_run 
                FROM `" . DB_PREFIX . "meschain_cron_jobs` 
                WHERE last_run IS NOT NULL 
                ORDER BY last_run DESC 
                LIMIT 5
            ");
            
            $json['recent_jobs'] = $recent_jobs->rows;
            
            $json['timestamp'] = date('Y-m-d H:i:s');
            $json['success'] = true;
            
        } catch (Exception $e) {
            $json['error'] = 'Durum kontrolü hatası: ' . $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}

?> 