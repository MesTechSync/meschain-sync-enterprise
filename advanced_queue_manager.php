<?php
/**
 * MesChain Sync Enterprise - Advanced Queue Management System
 * Team: Musti (Backend Infrastructure)
 * Priority: 1 - Critical
 * Date: June 11, 2025
 * 
 * Features:
 * - Priority-based queue routing
 * - Dead letter queue management
 * - Auto-scaling queue workers
 * - Real-time queue monitoring
 * - Advanced error handling and recovery
 */

class AdvancedQueueManager {
    private $redis;
    private $db;
    private $logger;
    private $worker_pools = [];
    private $queue_config;
    
    // Queue priorities
    const PRIORITY_CRITICAL = 1;
    const PRIORITY_HIGH = 2;
    const PRIORITY_NORMAL = 3;
    const PRIORITY_LOW = 4;
    const PRIORITY_BACKGROUND = 5;
    
    public function __construct($redis_connection, $database) {
        $this->redis = $redis_connection;
        $this->db = $database;
        $this->logger = new Logger('advanced_queue_manager');
        $this->queue_config = $this->loadQueueConfiguration();
        $this->initializeWorkerPools();
    }
    
    /**
     * Enhanced job queuing with priority routing
     */
    public function enqueueJobWithPriority($job_data, $priority = self::PRIORITY_NORMAL, $options = []) {
        try {
            // Validate job data
            $validated_job = $this->validateJobData($job_data);
            
            // Determine optimal queue based on priority and load
            $queue_name = $this->determineOptimalQueue($priority, $options);
            
            // Create enhanced job payload
            $job_payload = [
                'id' => uniqid('job_', true),
                'data' => $validated_job,
                'priority' => $priority,
                'created_at' => microtime(true),
                'attempts' => 0,
                'max_attempts' => $options['max_attempts'] ?? 3,
                'timeout' => $options['timeout'] ?? 300,
                'delay' => $options['delay'] ?? 0,
                'marketplace' => $options['marketplace'] ?? 'all',
                'user_id' => $options['user_id'] ?? null,
                'tags' => $options['tags'] ?? [],
                'dependencies' => $options['dependencies'] ?? []
            ];
            
            // Handle delayed jobs
            if ($job_payload['delay'] > 0) {
                $this->scheduleDelayedJob($job_payload, $queue_name);
            } else {
                $this->addToQueue($job_payload, $queue_name);
            }
            
            // Log job creation
            $this->logJobEvent('job_queued', $job_payload);
            
            // Update queue metrics
            $this->updateQueueMetrics($queue_name, 'job_added');
            
            return [
                'success' => true,
                'job_id' => $job_payload['id'],
                'queue' => $queue_name,
                'estimated_execution' => $this->estimateExecutionTime($queue_name, $priority)
            ];
            
        } catch (Exception $e) {
            $this->logger->error("Failed to enqueue job", [
                'error' => $e->getMessage(),
                'job_data' => $job_data
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Priority-based queue routing logic
     */
    private function determineOptimalQueue($priority, $options) {
        // Get current queue loads
        $queue_loads = $this->getCurrentQueueLoads();
        
        // Priority queue mapping
        $priority_queues = [
            self::PRIORITY_CRITICAL => 'critical_queue',
            self::PRIORITY_HIGH => 'high_priority_queue',
            self::PRIORITY_NORMAL => 'normal_queue',
            self::PRIORITY_LOW => 'low_priority_queue',
            self::PRIORITY_BACKGROUND => 'background_queue'
        ];
        
        $base_queue = $priority_queues[$priority];
        
        // Check for marketplace-specific routing
        if (isset($options['marketplace']) && $options['marketplace'] !== 'all') {
            $marketplace_queue = $base_queue . '_' . $options['marketplace'];
            if ($this->queueExists($marketplace_queue)) {
                $base_queue = $marketplace_queue;
            }
        }
        
        // Load balancing: if queue is overloaded, try alternative
        if ($queue_loads[$base_queue] > $this->queue_config['max_load_threshold']) {
            $alternative_queue = $this->findAlternativeQueue($base_queue, $priority);
            if ($alternative_queue) {
                $base_queue = $alternative_queue;
            }
        }
        
        return $base_queue;
    }
    
    /**
     * Dead Letter Queue Management
     */
    public function handleFailedJob($job_payload, $error_details) {
        try {
            $job_payload['attempts']++;
            $job_payload['last_error'] = $error_details;
            $job_payload['failed_at'] = microtime(true);
            
            // Check if job should be moved to DLQ
            if ($job_payload['attempts'] >= $job_payload['max_attempts']) {
                $this->moveToDeadLetterQueue($job_payload);
                $this->notifyJobFailure($job_payload);
            } else {
                // Retry with exponential backoff
                $retry_delay = $this->calculateRetryDelay($job_payload['attempts']);
                $job_payload['delay'] = $retry_delay;
                $this->scheduleDelayedJob($job_payload, $job_payload['original_queue']);
            }
            
            // Log failure
            $this->logJobEvent('job_failed', $job_payload);
            
            // Update failure metrics
            $this->updateFailureMetrics($job_payload);
            
        } catch (Exception $e) {
            $this->logger->critical("Failed to handle failed job", [
                'job_id' => $job_payload['id'],
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Dead Letter Queue processing
     */
    private function moveToDeadLetterQueue($job_payload) {
        $dlq_data = [
            'original_job' => $job_payload,
            'moved_to_dlq_at' => microtime(true),
            'failure_reason' => $job_payload['last_error'],
            'total_attempts' => $job_payload['attempts'],
            'recovery_attempts' => 0,
            'status' => 'failed'
        ];
        
        // Store in Redis DLQ
        $this->redis->lpush('dead_letter_queue', json_encode($dlq_data));
        
        // Store in database for analysis
        $this->db->insert('dead_letter_queue_jobs', [
            'job_id' => $job_payload['id'],
            'original_queue' => $job_payload['original_queue'],
            'job_data' => json_encode($job_payload),
            'failure_reason' => $job_payload['last_error'],
            'attempts' => $job_payload['attempts'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->logger->warning("Job moved to Dead Letter Queue", [
            'job_id' => $job_payload['id'],
            'attempts' => $job_payload['attempts']
        ]);
    }
    
    /**
     * Auto-scaling worker management
     */
    public function autoScaleWorkers() {
        foreach ($this->worker_pools as $pool_name => $pool_config) {
            $current_load = $this->getPoolLoad($pool_name);
            $current_workers = $this->getActiveWorkerCount($pool_name);
            
            // Scale up conditions
            if ($current_load > 80 && $current_workers < $pool_config['max_workers']) {
                $new_workers = min(
                    $pool_config['max_workers'],
                    $current_workers + $pool_config['scale_up_count']
                );
                $this->scaleUpWorkers($pool_name, $new_workers - $current_workers);
            }
            
            // Scale down conditions
            elseif ($current_load < 30 && $current_workers > $pool_config['min_workers']) {
                $workers_to_remove = min(
                    $current_workers - $pool_config['min_workers'],
                    $pool_config['scale_down_count']
                );
                $this->scaleDownWorkers($pool_name, $workers_to_remove);
            }
            
            // Update scaling metrics
            $this->updateScalingMetrics($pool_name, $current_load, $current_workers);
        }
    }
    
    /**
     * Real-time queue monitoring dashboard data
     */
    public function getQueueMonitoringData() {
        $monitoring_data = [
            'timestamp' => microtime(true),
            'queues' => [],
            'workers' => [],
            'system_metrics' => [],
            'dead_letter_queue' => [],
            'performance_metrics' => []
        ];
        
        // Get queue statistics
        foreach ($this->getAllQueueNames() as $queue_name) {
            $monitoring_data['queues'][$queue_name] = [
                'size' => $this->redis->llen($queue_name),
                'processing_rate' => $this->getProcessingRate($queue_name),
                'average_wait_time' => $this->getAverageWaitTime($queue_name),
                'error_rate' => $this->getErrorRate($queue_name),
                'priority_distribution' => $this->getPriorityDistribution($queue_name)
            ];
        }
        
        // Get worker pool status
        foreach ($this->worker_pools as $pool_name => $pool_config) {
            $monitoring_data['workers'][$pool_name] = [
                'active_workers' => $this->getActiveWorkerCount($pool_name),
                'busy_workers' => $this->getBusyWorkerCount($pool_name),
                'worker_efficiency' => $this->getWorkerEfficiency($pool_name),
                'auto_scaling_status' => $this->getAutoScalingStatus($pool_name)
            ];
        }
        
        // System metrics
        $monitoring_data['system_metrics'] = [
            'total_jobs_processed' => $this->getTotalJobsProcessed(),
            'jobs_per_second' => $this->getJobsPerSecond(),
            'memory_usage' => memory_get_usage(true),
            'cpu_load' => sys_getloadavg()[0],
            'redis_memory' => $this->getRedisMemoryUsage()
        ];
        
        // Dead Letter Queue analysis
        $monitoring_data['dead_letter_queue'] = [
            'total_failed_jobs' => $this->redis->llen('dead_letter_queue'),
            'recent_failures' => $this->getRecentFailures(),
            'top_failure_reasons' => $this->getTopFailureReasons(),
            'recovery_candidates' => $this->getRecoveryCandidates()
        ];
        
        return $monitoring_data;
    }
    
    /**
     * Advanced job recovery from DLQ
     */
    public function recoverJobsFromDLQ($criteria = []) {
        try {
            $dlq_jobs = $this->getRecoverableJobs($criteria);
            $recovered_count = 0;
            
            foreach ($dlq_jobs as $dlq_job) {
                if ($this->attemptJobRecovery($dlq_job)) {
                    $recovered_count++;
                }
            }
            
            $this->logger->info("Jobs recovered from DLQ", [
                'recovered_count' => $recovered_count,
                'total_candidates' => count($dlq_jobs)
            ]);
            
            return [
                'success' => true,
                'recovered_jobs' => $recovered_count,
                'total_candidates' => count($dlq_jobs)
            ];
            
        } catch (Exception $e) {
            $this->logger->error("DLQ recovery failed", ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Worker pool initialization
     */
    private function initializeWorkerPools() {
        $this->worker_pools = [
            'critical_pool' => [
                'min_workers' => 2,
                'max_workers' => 10,
                'scale_up_count' => 2,
                'scale_down_count' => 1,
                'queues' => ['critical_queue']
            ],
            'high_priority_pool' => [
                'min_workers' => 3,
                'max_workers' => 15,
                'scale_up_count' => 3,
                'scale_down_count' => 2,
                'queues' => ['high_priority_queue']
            ],
            'normal_pool' => [
                'min_workers' => 5,
                'max_workers' => 20,
                'scale_up_count' => 5,
                'scale_down_count' => 3,
                'queues' => ['normal_queue']
            ],
            'background_pool' => [
                'min_workers' => 2,
                'max_workers' => 8,
                'scale_up_count' => 2,
                'scale_down_count' => 1,
                'queues' => ['low_priority_queue', 'background_queue']
            ]
        ];
    }
    
    /**
     * Calculate retry delay with exponential backoff
     */
    private function calculateRetryDelay($attempt) {
        $base_delay = 5; // 5 seconds
        $max_delay = 300; // 5 minutes
        
        $delay = $base_delay * pow(2, $attempt - 1);
        return min($delay, $max_delay);
    }
}

/**
 * Queue Worker Process Manager
 */
class QueueWorkerManager {
    private $queue_manager;
    private $worker_processes = [];
    
    public function __construct($queue_manager) {
        $this->queue_manager = $queue_manager;
    }
    
    /**
     * Start queue worker processes
     */
    public function startWorkers($pool_name, $worker_count) {
        for ($i = 0; $i < $worker_count; $i++) {
            $pid = $this->forkWorkerProcess($pool_name);
            $this->worker_processes[$pool_name][] = $pid;
        }
    }
    
    /**
     * Monitor and restart failed workers
     */
    public function monitorWorkers() {
        foreach ($this->worker_processes as $pool_name => $pids) {
            foreach ($pids as $index => $pid) {
                if (!$this->isProcessAlive($pid)) {
                    $this->logger->warning("Worker process died, restarting", [
                        'pool' => $pool_name,
                        'pid' => $pid
                    ]);
                    
                    // Restart worker
                    $new_pid = $this->forkWorkerProcess($pool_name);
                    $this->worker_processes[$pool_name][$index] = $new_pid;
                }
            }
        }
    }
}

/**
 * Initialize Advanced Queue Manager
 */
function initializeAdvancedQueueManager() {
    try {
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        
        $db = new PDO("mysql:host=localhost;dbname=meschain_sync", $username, $password);
        
        $queueManager = new AdvancedQueueManager($redis, $db);
        
        // Start auto-scaling monitor
        $queueManager->autoScaleWorkers();
        
        echo "✅ Advanced Queue Manager initialized successfully\n";
        echo "📊 Auto-scaling and priority routing active\n";
        echo "🔄 Dead Letter Queue management enabled\n";
        
        return $queueManager;
        
    } catch (Exception $e) {
        echo "❌ Failed to initialize Advanced Queue Manager: " . $e->getMessage() . "\n";
        return false;
    }
}

// Initialize if run directly
if (php_sapi_name() === 'cli') {
    initializeAdvancedQueueManager();
}
?>
