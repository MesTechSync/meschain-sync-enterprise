<?php

/**
 * MesChain Trendyol Cron Job Management Controller
 * Day 5-6: Admin panel for cron job management and monitoring
 *
 * @author MesChain Development Team
 * @version 1.0.0
 * @since OpenCart 4.0.2.3
 */

namespace Opencart\Admin\Controller\Extension\Meschain\Cron;

class Trendyol extends \Opencart\System\Engine\Controller
{
    private $error = [];

    public function index()
    {
        $this->load->language('extension/meschain/cron/trendyol');

        $this->document->setTitle($this->language->get('heading_title'));

        // Handle form submission
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('meschain_trendyol_cron', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/meschain/cron/trendyol', 'user_token=' . $this->session->data['user_token']));
        }

        // Load models
        $this->load->model('setting/setting');
        $this->load->model('extension/meschain/trendyol');

        // Get current settings
        $settings = $this->model_setting_setting->getSetting('meschain_trendyol_cron');

        // Prepare data for view
        $data = [];

        // Breadcrumbs
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/meschain/cron/trendyol', 'user_token=' . $this->session->data['user_token'])
        ];

        // Form action
        $data['action'] = $this->url->link('extension/meschain/cron/trendyol', 'user_token=' . $this->session->data['user_token']);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

        // Settings
        $data['meschain_trendyol_cron_status'] = $settings['meschain_trendyol_cron_status'] ?? 0;
        $data['meschain_trendyol_cron_product_sync_enabled'] = $settings['meschain_trendyol_cron_product_sync_enabled'] ?? 1;
        $data['meschain_trendyol_cron_order_sync_enabled'] = $settings['meschain_trendyol_cron_order_sync_enabled'] ?? 1;
        $data['meschain_trendyol_cron_stock_sync_enabled'] = $settings['meschain_trendyol_cron_stock_sync_enabled'] ?? 1;
        $data['meschain_trendyol_cron_webhook_processing_enabled'] = $settings['meschain_trendyol_cron_webhook_processing_enabled'] ?? 1;

        // Intervals
        $data['meschain_trendyol_cron_product_sync_interval'] = $settings['meschain_trendyol_cron_product_sync_interval'] ?? 60;
        $data['meschain_trendyol_cron_order_sync_interval'] = $settings['meschain_trendyol_cron_order_sync_interval'] ?? 15;
        $data['meschain_trendyol_cron_stock_sync_interval'] = $settings['meschain_trendyol_cron_stock_sync_interval'] ?? 30;
        $data['meschain_trendyol_cron_webhook_processing_interval'] = $settings['meschain_trendyol_cron_webhook_processing_interval'] ?? 5;

        // Alert settings
        $data['meschain_trendyol_cron_alert_email'] = $settings['meschain_trendyol_cron_alert_email'] ?? '';
        $data['meschain_trendyol_cron_alert_on_error'] = $settings['meschain_trendyol_cron_alert_on_error'] ?? 1;
        $data['meschain_trendyol_cron_alert_on_low_stock'] = $settings['meschain_trendyol_cron_alert_on_low_stock'] ?? 1;

        // Performance settings
        $data['meschain_trendyol_cron_batch_size'] = $settings['meschain_trendyol_cron_batch_size'] ?? 50;
        $data['meschain_trendyol_cron_max_execution_time'] = $settings['meschain_trendyol_cron_max_execution_time'] ?? 300;
        $data['meschain_trendyol_cron_memory_limit'] = $settings['meschain_trendyol_cron_memory_limit'] ?? '256M';

        // Get cron job statistics
        $data['statistics'] = $this->getCronStatistics();

        // Get recent logs
        $data['recent_logs'] = $this->getRecentLogs();

        // Get cron job status
        $data['cron_status'] = $this->getCronJobStatus();

        // Error handling
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        // Header and footer
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/meschain/cron/trendyol', $data));
    }

    /**
     * Manual sync trigger
     */
    public function manualSync()
    {
        $this->load->language('extension/meschain/cron/trendyol');

        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/meschain/cron/trendyol')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $sync_type = $this->request->post['sync_type'] ?? '';

            switch ($sync_type) {
                case 'product':
                    $result = $this->runProductSync();
                    break;
                case 'order':
                    $result = $this->runOrderSync();
                    break;
                case 'stock':
                    $result = $this->runStockSync();
                    break;
                case 'webhook':
                    $result = $this->runWebhookProcessor();
                    break;
                case 'all':
                    $result = $this->runFullSync();
                    break;
                default:
                    $json['error'] = 'Invalid sync type';
                    break;
            }

            if (isset($result)) {
                if ($result['success']) {
                    $json['success'] = $result['message'];
                } else {
                    $json['error'] = $result['error'];
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Get cron job logs via AJAX
     */
    public function getLogs()
    {
        $this->load->language('extension/meschain/cron/trendyol');

        $json = [];

        if (!$this->user->hasPermission('access', 'extension/meschain/cron/trendyol')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $page = (int)($this->request->get['page'] ?? 1);
            $limit = 20;
            $offset = ($page - 1) * $limit;

            $logs = $this->db->query(
                "
                SELECT * FROM " . DB_PREFIX . "trendyol_sync_logs
                ORDER BY created_at DESC
                LIMIT " . $offset . ", " . $limit
            );

            $total = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "trendyol_sync_logs");

            $json['logs'] = [];
            if ($logs->num_rows) {
                foreach ($logs->rows as $log) {
                    $json['logs'][] = [
                        'id' => $log['log_id'],
                        'sync_type' => $log['sync_type'],
                        'status' => $log['status'],
                        'message' => $log['message'],
                        'execution_time' => $log['execution_time'],
                        'created_at' => date('d/m/Y H:i:s', strtotime($log['created_at']))
                    ];
                }
            }

            $json['total'] = $total->row['total'];
            $json['page'] = $page;
            $json['pages'] = ceil($total->row['total'] / $limit);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Get real-time statistics
     */
    public function getStats()
    {
        $json = [];

        if (!$this->user->hasPermission('access', 'extension/meschain/cron/trendyol')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $json = $this->getCronStatistics();
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Clear logs
     */
    public function clearLogs()
    {
        $this->load->language('extension/meschain/cron/trendyol');

        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/meschain/cron/trendyol')) {
            $json['error'] = $this->language->get('error_permission');
        } else {
            $days = (int)($this->request->post['days'] ?? 30);

            $this->db->query("
                DELETE FROM " . DB_PREFIX . "trendyol_sync_logs
                WHERE created_at < DATE_SUB(NOW(), INTERVAL " . $days . " DAY)
            ");

            $json['success'] = sprintf($this->language->get('text_logs_cleared'), $days);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Generate cron job setup script
     */
    public function generateCronScript()
    {
        $this->load->language('extension/meschain/cron/trendyol');

        if (!$this->user->hasPermission('access', 'extension/meschain/cron/trendyol')) {
            $this->response->redirect($this->url->link('error/permission', 'user_token=' . $this->session->data['user_token']));
        }

        $settings = $this->model_setting_setting->getSetting('meschain_trendyol_cron');

        $php_path = '/usr/bin/php'; // Default PHP path
        $opencart_path = DIR_APPLICATION . '../';

        $cron_jobs = [];

        // Main sync job
        if ($settings['meschain_trendyol_cron_status'] ?? 0) {
            $cron_jobs[] = sprintf(
                "*/%d * * * * %s %ssystem/library/meschain/cron/trendyol_sync.php >/dev/null 2>&1",
                $settings['meschain_trendyol_cron_order_sync_interval'] ?? 15,
                $php_path,
                $opencart_path
            );
        }

        // Product sync
        if ($settings['meschain_trendyol_cron_product_sync_enabled'] ?? 1) {
            $cron_jobs[] = sprintf(
                "*/%d * * * * %s %ssystem/library/meschain/cron/product_sync.php >/dev/null 2>&1",
                $settings['meschain_trendyol_cron_product_sync_interval'] ?? 60,
                $php_path,
                $opencart_path
            );
        }

        // Stock sync
        if ($settings['meschain_trendyol_cron_stock_sync_enabled'] ?? 1) {
            $cron_jobs[] = sprintf(
                "*/%d * * * * %s %ssystem/library/meschain/cron/stock_sync.php >/dev/null 2>&1",
                $settings['meschain_trendyol_cron_stock_sync_interval'] ?? 30,
                $php_path,
                $opencart_path
            );
        }

        // Webhook processor
        if ($settings['meschain_trendyol_cron_webhook_processing_enabled'] ?? 1) {
            $cron_jobs[] = sprintf(
                "*/%d * * * * %s %ssystem/library/meschain/cron/webhook_processor.php >/dev/null 2>&1",
                $settings['meschain_trendyol_cron_webhook_processing_interval'] ?? 5,
                $php_path,
                $opencart_path
            );
        }

        $script_content = "#!/bin/bash\n\n";
        $script_content .= "# MesChain Trendyol Cron Jobs Setup Script\n";
        $script_content .= "# Generated on: " . date('Y-m-d H:i:s') . "\n\n";
        $script_content .= "echo 'Setting up Trendyol cron jobs...'\n\n";

        foreach ($cron_jobs as $job) {
            $script_content .= "# Add cron job: " . $job . "\n";
            $script_content .= "(crontab -l 2>/dev/null; echo \"" . $job . "\") | crontab -\n\n";
        }

        $script_content .= "echo 'Cron jobs setup completed!'\n";
        $script_content .= "echo 'You can verify with: crontab -l'\n";

        // Set headers for file download
        $this->response->addHeader('Content-Type: application/x-sh');
        $this->response->addHeader('Content-Disposition: attachment; filename="trendyol_cron_setup.sh"');
        $this->response->addHeader('Content-Length: ' . strlen($script_content));

        $this->response->setOutput($script_content);
    }

    /**
     * Private helper methods
     */
    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/meschain/cron/trendyol')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    private function getCronStatistics()
    {
        $stats = [];

        // Today's statistics
        $today = $this->db->query("
            SELECT
                sync_type,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success,
                SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as errors,
                AVG(execution_time) as avg_time
            FROM " . DB_PREFIX . "trendyol_sync_logs
            WHERE DATE(created_at) = CURDATE()
            GROUP BY sync_type
        ");

        $stats['today'] = [];
        if ($today->num_rows) {
            foreach ($today->rows as $row) {
                $stats['today'][$row['sync_type']] = [
                    'total' => $row['total'],
                    'success' => $row['success'],
                    'errors' => $row['errors'],
                    'avg_time' => round($row['avg_time'], 2)
                ];
            }
        }

        // Last 7 days
        $week = $this->db->query("
            SELECT
                DATE(created_at) as date,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success,
                SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as errors
            FROM " . DB_PREFIX . "trendyol_sync_logs
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date DESC
        ");

        $stats['week'] = [];
        if ($week->num_rows) {
            foreach ($week->rows as $row) {
                $stats['week'][] = [
                    'date' => $row['date'],
                    'total' => $row['total'],
                    'success' => $row['success'],
                    'errors' => $row['errors']
                ];
            }
        }

        // Current queue sizes
        $queues = $this->db->query("
            SELECT
                'products' as queue_type,
                COUNT(*) as pending
            FROM " . DB_PREFIX . "trendyol_products
            WHERE sync_status = 'pending'

            UNION ALL

            SELECT
                'orders' as queue_type,
                COUNT(*) as pending
            FROM " . DB_PREFIX . "trendyol_orders
            WHERE sync_status = 'pending'

            UNION ALL

            SELECT
                'webhooks' as queue_type,
                COUNT(*) as pending
            FROM " . DB_PREFIX . "trendyol_webhook_logs
            WHERE processed = 0
        ");

        $stats['queues'] = [];
        if ($queues->num_rows) {
            foreach ($queues->rows as $row) {
                $stats['queues'][$row['queue_type']] = $row['pending'];
            }
        }

        return $stats;
    }

    private function getRecentLogs()
    {
        $logs = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "trendyol_sync_logs
            ORDER BY created_at DESC
            LIMIT 10
        ");

        $recent_logs = [];
        if ($logs->num_rows) {
            foreach ($logs->rows as $log) {
                $recent_logs[] = [
                    'sync_type' => $log['sync_type'],
                    'status' => $log['status'],
                    'message' => $log['message'],
                    'execution_time' => $log['execution_time'],
                    'created_at' => date('d/m/Y H:i:s', strtotime($log['created_at']))
                ];
            }
        }

        return $recent_logs;
    }

    private function getCronJobStatus()
    {
        $status = [];

        // Check if lock files exist (indicating running processes)
        $lock_files = [
            'trendyol_sync' => sys_get_temp_dir() . '/trendyol_sync.lock',
            'product_sync' => sys_get_temp_dir() . '/trendyol_product_sync.lock',
            'order_sync' => sys_get_temp_dir() . '/trendyol_order_sync.lock',
            'stock_sync' => sys_get_temp_dir() . '/trendyol_stock_sync.lock',
            'webhook_processor' => sys_get_temp_dir() . '/trendyol_webhook_processor.lock'
        ];

        foreach ($lock_files as $job => $lock_file) {
            $status[$job] = [
                'running' => file_exists($lock_file),
                'last_run' => $this->getLastRunTime($job)
            ];
        }

        return $status;
    }

    private function getLastRunTime($sync_type)
    {
        $query = $this->db->query("
            SELECT created_at FROM " . DB_PREFIX . "trendyol_sync_logs
            WHERE sync_type = '" . $this->db->escape($sync_type) . "'
            ORDER BY created_at DESC
            LIMIT 1
        ");

        if ($query->num_rows) {
            return date('d/m/Y H:i:s', strtotime($query->row['created_at']));
        }

        return 'Never';
    }

    private function runProductSync()
    {
        $command = 'php ' . DIR_APPLICATION . '../system/library/meschain/cron/product_sync.php';
        $output = shell_exec($command . ' 2>&1');

        return [
            'success' => strpos($output, 'error') === false,
            'message' => $output ?: 'Product sync completed',
            'error' => strpos($output, 'error') !== false ? $output : null
        ];
    }

    private function runOrderSync()
    {
        $command = 'php ' . DIR_APPLICATION . '../system/library/meschain/cron/order_sync.php';
        $output = shell_exec($command . ' 2>&1');

        return [
            'success' => strpos($output, 'error') === false,
            'message' => $output ?: 'Order sync completed',
            'error' => strpos($output, 'error') !== false ? $output : null
        ];
    }

    private function runStockSync()
    {
        $command = 'php ' . DIR_APPLICATION . '../system/library/meschain/cron/stock_sync.php';
        $output = shell_exec($command . ' 2>&1');

        return [
            'success' => strpos($output, 'error') === false,
            'message' => $output ?: 'Stock sync completed',
            'error' => strpos($output, 'error') !== false ? $output : null
        ];
    }

    private function runWebhookProcessor()
    {
        $command = 'php ' . DIR_APPLICATION . '../system/library/meschain/cron/webhook_processor.php';
        $output = shell_exec($command . ' 2>&1');

        return [
            'success' => strpos($output, 'error') === false,
            'message' => $output ?: 'Webhook processing completed',
            'error' => strpos($output, 'error') !== false ? $output : null
        ];
    }

    private function runFullSync()
    {
        $command = 'php ' . DIR_APPLICATION . '../system/library/meschain/cron/trendyol_sync.php';
        $output = shell_exec($command . ' 2>&1');

        return [
            'success' => strpos($output, 'error') === false,
            'message' => $output ?: 'Full sync completed',
            'error' => strpos($output, 'error') !== false ? $output : null
        ];
    }
}
