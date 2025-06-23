<?php
/**
 * MesChain Sync Enterprise Dashboard
 * Native OpenCart 4.x Controller
 * Path: admin/controller/extension/meschain/dashboard.php
 */

namespace Opencart\Admin\Controller\Extension\Meschain;

class Dashboard extends \Opencart\System\Engine\Controller {
    
    /**
     * Main dashboard view
     */
    public function index() {
        $this->load->language('extension/meschain/dashboard');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/meschain/dashboard', 'user_token=' . $this->session->data['user_token'])
        );
        
        // Load dashboard data
        $this->load->model('extension/meschain/dashboard');
        
        $data['stats'] = $this->model_extension_meschain_dashboard->getStats();
        $data['recent_syncs'] = $this->model_extension_meschain_dashboard->getRecentSyncs();
        $data['marketplace_status'] = $this->model_extension_meschain_dashboard->getMarketplaceStatus();
        
        // Dashboard navigation
        $data['nav_links'] = [
            [
                'text' => $this->language->get('text_dashboard'),
                'href' => $this->url->link('extension/meschain/dashboard', 'user_token=' . $this->session->data['user_token']),
                'active' => true
            ],
            [
                'text' => $this->language->get('text_trendyol'),
                'href' => $this->url->link('extension/meschain/trendyol', 'user_token=' . $this->session->data['user_token']),
                'active' => false
            ],
            [
                'text' => $this->language->get('text_products'),
                'href' => $this->url->link('extension/meschain/products', 'user_token=' . $this->session->data['user_token']),
                'active' => false
            ],
            [
                'text' => $this->language->get('text_orders'),
                'href' => $this->url->link('extension/meschain/orders', 'user_token=' . $this->session->data['user_token']),
                'active' => false
            ],
            [
                'text' => $this->language->get('text_settings'),
                'href' => $this->url->link('extension/meschain/settings', 'user_token=' . $this->session->data['user_token']),
                'active' => false
            ]
        ];
        
        // Common template data
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $data['user_token'] = $this->session->data['user_token'];
        
        $this->response->setOutput($this->load->view('extension/meschain/dashboard', $data));
    }
    
    /**
     * Quick sync action
     */
    public function quickSync() {
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->language('extension/meschain/dashboard');
            
            try {
                $this->load->model('extension/meschain/dashboard');
                
                $sync_type = $this->request->post['sync_type'] ?? 'all';
                $marketplace = $this->request->post['marketplace'] ?? 'all';
                
                $result = $this->model_extension_meschain_dashboard->quickSync($sync_type, $marketplace);
                
                if ($result['success']) {
                    $json['success'] = $this->language->get('text_sync_success');
                    $json['data'] = $result['data'];
                } else {
                    $json['error'] = $result['message'];
                }
                
            } catch (Exception $e) {
                $json['error'] = $this->language->get('error_sync_failed') . ': ' . $e->getMessage();
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Get live statistics via AJAX
     */
    public function getStats() {
        $json = array();
        
        if ($this->request->server['REQUEST_METHOD'] == 'GET') {
            $this->load->model('extension/meschain/dashboard');
            
            try {
                $json['stats'] = $this->model_extension_meschain_dashboard->getLiveStats();
                $json['success'] = true;
                
            } catch (Exception $e) {
                $json['error'] = 'Failed to load statistics';
                $json['success'] = false;
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Permission check
     */
    public function user() {
        $this->load->language('extension/meschain/dashboard');
        
        if (!$this->user->hasPermission('access', 'extension/meschain/dashboard')) {
            $this->session->data['warning'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token']));
        }
    }
    
    /**
     * System health check
     */
    public function healthCheck() {
        $json = array();
        
        $this->load->model('extension/meschain/dashboard');
        
        try {
            $health = $this->model_extension_meschain_dashboard->systemHealthCheck();
            
            $json['success'] = true;
            $json['health'] = $health;
            $json['overall_status'] = $health['overall'] >= 80 ? 'healthy' : ($health['overall'] >= 60 ? 'warning' : 'error');
            
        } catch (Exception $e) {
            $json['success'] = false;
            $json['error'] = $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Export dashboard data
     */
    public function exportData() {
        $this->load->language('extension/meschain/dashboard');
        
        if (!$this->user->hasPermission('modify', 'extension/meschain/dashboard')) {
            $this->session->data['error'] = $this->language->get('error_permission');
            $this->response->redirect($this->url->link('extension/meschain/dashboard', 'user_token=' . $this->session->data['user_token']));
        }
        
        $this->load->model('extension/meschain/dashboard');
        
        $format = $this->request->get['format'] ?? 'csv';
        $date_from = $this->request->get['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
        $date_to = $this->request->get['date_to'] ?? date('Y-m-d');
        
        $data = $this->model_extension_meschain_dashboard->exportData($date_from, $date_to);
        
        if ($format === 'csv') {
            $this->exportToCsv($data);
        } elseif ($format === 'json') {
            $this->exportToJson($data);
        }
    }
    
    /**
     * Export to CSV
     */
    private function exportToCsv($data) {
        $filename = 'meschain_dashboard_' . date('Y-m-d_H-i-s') . '.csv';
        
        $this->response->addheader('Content-Type: text/csv');
        $this->response->addheader('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        if (!empty($data)) {
            // Write header
            fputcsv($output, array_keys($data[0]));
            
            // Write data
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
    }
    
    /**
     * Export to JSON
     */
    private function exportToJson($data) {
        $filename = 'meschain_dashboard_' . date('Y-m-d_H-i-s') . '.json';
        
        $this->response->addheader('Content-Type: application/json');
        $this->response->addheader('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $export_data = [
            'export_date' => date('Y-m-d H:i:s'),
            'version' => '2.0.0',
            'data' => $data
        ];
        
        $this->response->setOutput(json_encode($export_data, JSON_PRETTY_PRINT));
    }
}
?>
