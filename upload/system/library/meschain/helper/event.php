<?php
/**
 * MeschainEventHelper - Event-Driven Architecture
 * 
 * Sistem genelinde event yönetimi, listener'lar ve async processing
 * 
 * @author MesChain Development Team
 * @version 1.0.0
 * @since 2024-01-21
 */

class MeschainEventHelper {
    
    private $registry;
    private $db;
    private $log;
    private $listeners = [];
    private $queuedEvents = [];
    private $processingEvents = false;
    
    // Event tipleri
    const TYPE_SYNC = 'sync';           // Senkron işlem
    const TYPE_ASYNC = 'async';         // Asenkron işlem  
    const TYPE_WEBHOOK = 'webhook';     // Webhook tetikleme
    const TYPE_EMAIL = 'email';         // Email gönderimi
    const TYPE_LOG = 'log';             // Loglama
    
    // Event öncelikleri
    const PRIORITY_LOW = 1;
    const PRIORITY_NORMAL = 2;
    const PRIORITY_HIGH = 3;
    const PRIORITY_CRITICAL = 4;
    
    public function __construct($registry) {
        $this->registry = $registry;
        $this->db = $registry->get('db');
        $this->log = new Log('meschain_events.log');
        
        $this->createEventTables();
        $this->loadSystemListeners();
    }
    
    /**
     * Event tablolarını oluştur
     */
    private function createEventTables() {
        // Event queue tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_event_queue` (
            `queue_id` int(11) NOT NULL AUTO_INCREMENT,
            `event_name` varchar(255) NOT NULL,
            `event_data` json,
            `event_type` enum('sync','async','webhook','email','log') DEFAULT 'async',
            `priority` tinyint(1) DEFAULT 2,
            `tenant_id` int(11) DEFAULT NULL,
            `user_id` int(11) DEFAULT NULL,
            `status` enum('pending','processing','completed','failed','retry') DEFAULT 'pending',
            `attempts` int(11) DEFAULT 0,
            `max_attempts` int(11) DEFAULT 3,
            `scheduled_at` datetime DEFAULT NULL,
            `started_at` datetime DEFAULT NULL,
            `completed_at` datetime DEFAULT NULL,
            `error_message` text,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`queue_id`),
            KEY `event_name` (`event_name`),
            KEY `status` (`status`),
            KEY `priority` (`priority`),
            KEY `tenant_id` (`tenant_id`),
            KEY `scheduled_at` (`scheduled_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // Event listeners tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_event_listeners` (
            `listener_id` int(11) NOT NULL AUTO_INCREMENT,
            `event_name` varchar(255) NOT NULL,
            `listener_class` varchar(255) NOT NULL,
            `listener_method` varchar(100) NOT NULL,
            `priority` tinyint(1) DEFAULT 2,
            `is_active` tinyint(1) DEFAULT 1,
            `conditions` json,
            `tenant_id` int(11) DEFAULT NULL,
            `created_by` int(11) DEFAULT NULL,
            `date_created` datetime NOT NULL,
            PRIMARY KEY (`listener_id`),
            KEY `event_name` (`event_name`),
            KEY `priority` (`priority`),
            KEY `is_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        // Event history tablosu
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "meschain_event_history` (
            `history_id` int(11) NOT NULL AUTO_INCREMENT,
            `event_name` varchar(255) NOT NULL,
            `event_data` json,
            `triggered_by` varchar(255),
            `listeners_called` json,
            `execution_time` decimal(10,4) DEFAULT NULL,
            `tenant_id` int(11) DEFAULT NULL,
            `user_id` int(11) DEFAULT NULL,
            `date_triggered` datetime NOT NULL,
            PRIMARY KEY (`history_id`),
            KEY `event_name` (`event_name`),
            KEY `date_triggered` (`date_triggered`),
            KEY `tenant_id` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        
        $this->log->write('Event tabloları oluşturuldu/kontrol edildi');
    }
    
    /**
     * Sistem listener'larını yükle
     */
    private function loadSystemListeners() {
        $this->addListener('product.updated', 'MeschainSyncHelper', 'onProductUpdated');
        $this->addListener('order.created', 'MeschainOrderHelper', 'onOrderCreated');
        $this->addListener('user.login', 'MeschainRbacHelper', 'onUserLogin');
        $this->addListener('api.rate_limit_exceeded', 'MeschainApiHelper', 'onRateLimitExceeded');
        $this->addListener('webhook.received', 'MeschainWebhookHelper', 'onWebhookReceived');
        $this->addListener('error.critical', 'MeschainNotificationHelper', 'onCriticalError');
        $this->addListener('tenant.created', 'MeschainTenantHelper', 'onTenantCreated');
        $this->addListener('config.changed', 'MeschainConfigHelper', 'onConfigChanged');
    }
    
    /**
     * Event tetikle
     */
    public function trigger($eventName, $data = [], $options = []) {
        $startTime = microtime(true);
        
        $type = $options['type'] ?? self::TYPE_SYNC;
        $priority = $options['priority'] ?? self::PRIORITY_NORMAL;
        $tenantId = $options['tenant_id'] ?? $this->getCurrentTenantId();
        $userId = $options['user_id'] ?? $this->getCurrentUserId();
        $scheduledAt = $options['scheduled_at'] ?? null;
        
        // Event data'ya metadata ekle
        $eventData = array_merge($data, [
            '_meta' => [
                'triggered_at' => date('Y-m-d H:i:s'),
                'triggered_by' => $this->getCallerInfo(),
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'priority' => $priority,
                'type' => $type
            ]
        ]);
        
        $this->log->write("Event tetiklendi: {$eventName}");
        
        if ($type === self::TYPE_SYNC) {
            // Senkron işlem - hemen çalıştır
            return $this->processEventSync($eventName, $eventData, $startTime);
        } else {
            // Asenkron işlem - queue'ya ekle
            return $this->queueEvent($eventName, $eventData, $type, $priority, $scheduledAt, $tenantId, $userId);
        }
    }
    
    /**
     * Senkron event işleme
     */
    private function processEventSync($eventName, $eventData, $startTime) {
        $listeners = $this->getEventListeners($eventName);
        $calledListeners = [];
        $results = [];
        
        foreach ($listeners as $listener) {
            try {
                if ($this->checkListenerConditions($listener, $eventData)) {
                    $result = $this->callListener($listener, $eventData);
                    $results[] = $result;
                    $calledListeners[] = [
                        'class' => $listener['listener_class'],
                        'method' => $listener['listener_method'],
                        'result' => $result
                    ];
                }
            } catch (Exception $e) {
                $this->log->write("Listener hatası: {$listener['listener_class']}::{$listener['listener_method']} - " . $e->getMessage());
                $calledListeners[] = [
                    'class' => $listener['listener_class'],
                    'method' => $listener['listener_method'],
                    'error' => $e->getMessage()
                ];
            }
        }
        
        $executionTime = microtime(true) - $startTime;
        
        // History'ye kaydet
        $this->saveEventHistory($eventName, $eventData, $calledListeners, $executionTime);
        
        return [
            'success' => true,
            'listeners_called' => count($calledListeners),
            'execution_time' => $executionTime,
            'results' => $results
        ];
    }
    
    /**
     * Event'i queue'ya ekle
     */
    private function queueEvent($eventName, $eventData, $type, $priority, $scheduledAt, $tenantId, $userId) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_event_queue` SET
            event_name = '" . $this->db->escape($eventName) . "',
            event_data = '" . $this->db->escape(json_encode($eventData)) . "',
            event_type = '" . $this->db->escape($type) . "',
            priority = " . (int)$priority . ",
            tenant_id = " . ($tenantId ? (int)$tenantId : "NULL") . ",
            user_id = " . ($userId ? (int)$userId : "NULL") . ",
            scheduled_at = " . ($scheduledAt ? "'" . $this->db->escape($scheduledAt) . "'" : "NULL") . ",
            created_at = NOW()
        ");
        
        $queueId = $this->db->getLastId();
        
        $this->log->write("Event queue'ya eklendi: {$eventName} (ID: {$queueId})");
        
        return [
            'success' => true,
            'queue_id' => $queueId,
            'message' => 'Event queue\'ya eklendi'
        ];
    }
    
    /**
     * Queue'daki event'leri işle
     */
    public function processQueue($limit = 10) {
        if ($this->processingEvents) {
            return false; // Çifte işleme önlemi
        }
        
        $this->processingEvents = true;
        $processed = 0;
        
        try {
            // Pending event'leri priority sırasına göre al
            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_event_queue` 
                WHERE status = 'pending' 
                AND (scheduled_at IS NULL OR scheduled_at <= NOW())
                ORDER BY priority DESC, created_at ASC 
                LIMIT " . (int)$limit);
            
            foreach ($query->rows as $event) {
                try {
                    $this->processQueuedEvent($event);
                    $processed++;
                } catch (Exception $e) {
                    $this->log->write("Queue event işleme hatası: {$event['event_name']} - " . $e->getMessage());
                }
            }
            
        } finally {
            $this->processingEvents = false;
        }
        
        return $processed;
    }
    
    /**
     * Tek bir queued event'i işle
     */
    private function processQueuedEvent($event) {
        $queueId = $event['queue_id'];
        $eventName = $event['event_name'];
        $eventData = json_decode($event['event_data'], true);
        
        // İşleme başladığını belirt
        $this->db->query("UPDATE `" . DB_PREFIX . "meschain_event_queue` SET
            status = 'processing',
            started_at = NOW(),
            attempts = attempts + 1
            WHERE queue_id = " . (int)$queueId);
        
        $startTime = microtime(true);
        
        try {
            $listeners = $this->getEventListeners($eventName);
            $calledListeners = [];
            
            foreach ($listeners as $listener) {
                if ($this->checkListenerConditions($listener, $eventData)) {
                    $this->callListener($listener, $eventData);
                    $calledListeners[] = [
                        'class' => $listener['listener_class'],
                        'method' => $listener['listener_method']
                    ];
                }
            }
            
            $executionTime = microtime(true) - $startTime;
            
            // Başarılı tamamlandı
            $this->db->query("UPDATE `" . DB_PREFIX . "meschain_event_queue` SET
                status = 'completed',
                completed_at = NOW()
                WHERE queue_id = " . (int)$queueId);
            
            // History'ye kaydet
            $this->saveEventHistory($eventName, $eventData, $calledListeners, $executionTime);
            
            $this->log->write("Queue event tamamlandı: {$eventName} (ID: {$queueId})");
            
        } catch (Exception $e) {
            $attempts = $event['attempts'] + 1;
            $maxAttempts = $event['max_attempts'];
            
            if ($attempts >= $maxAttempts) {
                // Maksimum deneme aşıldı - failed olarak işaretle
                $this->db->query("UPDATE `" . DB_PREFIX . "meschain_event_queue` SET
                    status = 'failed',
                    error_message = '" . $this->db->escape($e->getMessage()) . "',
                    completed_at = NOW()
                    WHERE queue_id = " . (int)$queueId);
                
                $this->log->write("Queue event başarısız (max retry): {$eventName} - " . $e->getMessage());
            } else {
                // Retry için beklemede bırak
                $retryAt = date('Y-m-d H:i:s', strtotime('+' . (5 * $attempts) . ' minutes'));
                
                $this->db->query("UPDATE `" . DB_PREFIX . "meschain_event_queue` SET
                    status = 'retry',
                    error_message = '" . $this->db->escape($e->getMessage()) . "',
                    scheduled_at = '" . $retryAt . "'
                    WHERE queue_id = " . (int)$queueId);
                
                $this->log->write("Queue event retry planlandı: {$eventName} - " . $retryAt);
            }
            
            throw $e;
        }
    }
    
    /**
     * Event listener ekle
     */
    public function addListener($eventName, $listenerClass, $listenerMethod, $options = []) {
        $priority = $options['priority'] ?? self::PRIORITY_NORMAL;
        $conditions = $options['conditions'] ?? [];
        $tenantId = $options['tenant_id'] ?? null;
        
        // Mevcut listener kontrolü
        $existing = $this->db->query("SELECT listener_id FROM `" . DB_PREFIX . "meschain_event_listeners` 
            WHERE event_name = '" . $this->db->escape($eventName) . "'
            AND listener_class = '" . $this->db->escape($listenerClass) . "'
            AND listener_method = '" . $this->db->escape($listenerMethod) . "'
            AND tenant_id " . ($tenantId ? "= " . (int)$tenantId : "IS NULL"));
        
        if (!$existing->num_rows) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_event_listeners` SET
                event_name = '" . $this->db->escape($eventName) . "',
                listener_class = '" . $this->db->escape($listenerClass) . "',
                listener_method = '" . $this->db->escape($listenerMethod) . "',
                priority = " . (int)$priority . ",
                conditions = '" . $this->db->escape(json_encode($conditions)) . "',
                tenant_id = " . ($tenantId ? (int)$tenantId : "NULL") . ",
                created_by = " . (int)$this->getCurrentUserId() . ",
                date_created = NOW()
            ");
            
            $this->log->write("Event listener eklendi: {$eventName} -> {$listenerClass}::{$listenerMethod}");
        }
    }
    
    /**
     * Event listener kaldır
     */
    public function removeListener($eventName, $listenerClass, $listenerMethod, $tenantId = null) {
        $sql = "DELETE FROM `" . DB_PREFIX . "meschain_event_listeners` 
                WHERE event_name = '" . $this->db->escape($eventName) . "'
                AND listener_class = '" . $this->db->escape($listenerClass) . "'
                AND listener_method = '" . $this->db->escape($listenerMethod) . "'";
        
        if ($tenantId) {
            $sql .= " AND tenant_id = " . (int)$tenantId;
        } else {
            $sql .= " AND tenant_id IS NULL";
        }
        
        $this->db->query($sql);
        
        $this->log->write("Event listener kaldırıldı: {$eventName} -> {$listenerClass}::{$listenerMethod}");
    }
    
    /**
     * Event için listener'ları al
     */
    private function getEventListeners($eventName) {
        // Cache kontrolü
        $cacheKey = "event_listeners_{$eventName}";
        if (isset($this->listeners[$cacheKey])) {
            return $this->listeners[$cacheKey];
        }
        
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "meschain_event_listeners` 
            WHERE event_name = '" . $this->db->escape($eventName) . "'
            AND is_active = 1
            ORDER BY priority DESC, listener_id ASC");
        
        $listeners = [];
        foreach ($query->rows as $row) {
            $row['conditions'] = json_decode($row['conditions'], true) ?? [];
            $listeners[] = $row;
        }
        
        // Cache'le
        $this->listeners[$cacheKey] = $listeners;
        
        return $listeners;
    }
    
    /**
     * Listener koşullarını kontrol et
     */
    private function checkListenerConditions($listener, $eventData) {
        $conditions = $listener['conditions'];
        
        if (empty($conditions)) {
            return true;
        }
        
        foreach ($conditions as $field => $expectedValue) {
            $actualValue = $this->getDataValue($eventData, $field);
            
            if ($actualValue !== $expectedValue) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Nested array'den değer al
     */
    private function getDataValue($data, $path) {
        $keys = explode('.', $path);
        $current = $data;
        
        foreach ($keys as $key) {
            if (!isset($current[$key])) {
                return null;
            }
            $current = $current[$key];
        }
        
        return $current;
    }
    
    /**
     * Listener'ı çağır
     */
    private function callListener($listener, $eventData) {
        $className = $listener['listener_class'];
        $methodName = $listener['listener_method'];
        
        // Helper sınıfı yükle
        if (strpos($className, 'Meschain') === 0) {
            $helperFile = DIR_SYSTEM . 'library/meschain/helper/' . strtolower(str_replace('MeschainHelper', '', $className)) . '.php';
            if (file_exists($helperFile)) {
                require_once($helperFile);
            }
        }
        
        if (class_exists($className)) {
            $instance = new $className($this->registry);
            
            if (method_exists($instance, $methodName)) {
                return call_user_func([$instance, $methodName], $eventData);
            } else {
                throw new Exception("Method not found: {$className}::{$methodName}");
            }
        } else {
            throw new Exception("Class not found: {$className}");
        }
    }
    
    /**
     * Event history kaydet
     */
    private function saveEventHistory($eventName, $eventData, $calledListeners, $executionTime) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "meschain_event_history` SET
            event_name = '" . $this->db->escape($eventName) . "',
            event_data = '" . $this->db->escape(json_encode($eventData)) . "',
            triggered_by = '" . $this->db->escape($this->getCallerInfo()) . "',
            listeners_called = '" . $this->db->escape(json_encode($calledListeners)) . "',
            execution_time = " . (float)$executionTime . ",
            tenant_id = " . (int)$this->getCurrentTenantId() . ",
            user_id = " . (int)$this->getCurrentUserId() . ",
            date_triggered = NOW()
        ");
    }
    
    /**
     * Çağrı bilgisini al
     */
    private function getCallerInfo() {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);
        
        for ($i = 1; $i < count($trace); $i++) {
            if (isset($trace[$i]['class']) && $trace[$i]['class'] !== __CLASS__) {
                return $trace[$i]['class'] . '::' . $trace[$i]['function'];
            }
        }
        
        return 'Unknown';
    }
    
    /**
     * Mevcut tenant ID'sini al
     */
    private function getCurrentTenantId() {
        if ($this->registry->has('session')) {
            $session = $this->registry->get('session');
            return $session->data['tenant_id'] ?? 1;
        }
        return 1;
    }
    
    /**
     * Mevcut kullanıcı ID'sini al
     */
    private function getCurrentUserId() {
        if ($this->registry->has('user')) {
            $user = $this->registry->get('user');
            return $user->getId();
        }
        return 0;
    }
    
    /**
     * Event istatistiklerini al
     */
    public function getEventStats($timeframe = '24 hours') {
        $query = $this->db->query("SELECT 
            event_name,
            COUNT(*) as total_events,
            AVG(execution_time) as avg_execution_time,
            MAX(execution_time) as max_execution_time,
            COUNT(DISTINCT tenant_id) as tenant_count
            FROM `" . DB_PREFIX . "meschain_event_history` 
            WHERE date_triggered >= DATE_SUB(NOW(), INTERVAL {$timeframe})
            GROUP BY event_name
            ORDER BY total_events DESC");
        
        return $query->rows;
    }
    
    /**
     * Queue temizle (eski kayıtları sil)
     */
    public function cleanupQueue($days = 7) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_event_queue` 
            WHERE status IN ('completed', 'failed') 
            AND completed_at < DATE_SUB(NOW(), INTERVAL {$days} DAY)");
        
        $this->db->query("DELETE FROM `" . DB_PREFIX . "meschain_event_history` 
            WHERE date_triggered < DATE_SUB(NOW(), INTERVAL {$days} DAY)");
        
        $this->log->write("Event queue temizlendi ({$days} gün)");
    }
}
?> 