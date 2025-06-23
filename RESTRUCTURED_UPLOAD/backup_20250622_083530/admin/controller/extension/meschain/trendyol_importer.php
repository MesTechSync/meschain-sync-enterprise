<?php
namespace Opencart\Admin\Controller\Extension\Meschain;
/**
 * Trendyol Product Importer Admin Controller
 * MesChain-Sync Enterprise v4.5.0
 *
 * Handles admin interface for Trendyol product import system
 *
 * @author MesChain Development Team
 * @version 4.5.0 Enterprise
 * @copyright 2024 MesChain Technologies
 */

class TrendyolImporter extends \Opencart\System\Engine\Controller {

    private $error = array();

    public function index() {
        $this->load->language('extension/meschain/trendyol_importer');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/meschain/trendyol_importer');

        $this->getList();
    }

    public function dashboard() {
        $this->load->language('extension/meschain/trendyol_importer');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/meschain/trendyol_importer');

        // Get dashboard data
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_dashboard'] = $this->language->get('text_dashboard');

        // Breadcrumbs
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/meschain/trendyol_importer', 'user_token=' . $this->session->data['user_token'], true)
        );

        // Get statistics
        $data['total_sessions'] = $this->model_extension_meschain_trendyol_importer->getTotalSessions();
        $data['active_sessions'] = $this->model_extension_meschain_trendyol_importer->getActiveSessions();
        $data['total_products_imported'] = $this->model_extension_meschain_trendyol_importer->getTotalProductsImported();
        $data['recent_sessions'] = $this->model_extension_meschain_trendyol_importer->getRecentSessions(5);

        // Test API connection
        $data['api_status'] = $this->testApiConnection();

        // URLs
        $data['import_wizard_url'] = $this->url->link('extension/meschain/trendyol_importer/wizard', 'user_token=' . $this->session->data['user_token'], true);
        $data['sessions_url'] = $this->url->link('extension/meschain/trendyol_importer', 'user_token=' . $this->session->data['user_token'], true);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/meschain/trendyol_importer_dashboard', $data));
    }

    public function wizard() {
        $this->load->language('extension/meschain/trendyol_importer');

        $this->document->setTitle($this->language->get('heading_title') . ' - ' . $this->language->get('text_import_wizard'));

        $this->load->model('extension/meschain/trendyol_importer');

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_import_wizard'] = $this->language->get('text_import_wizard');

        // Breadcrumbs
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/meschain/trendyol_importer', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_import_wizard'),
            'href' => $this->url->link('extension/meschain/trendyol_importer/wizard', 'user_token=' . $this->session->data['user_token'], true)
        );

        // Get categories for mapping
        $this->load->model('catalog/category');
        $data['categories'] = $this->model_catalog_category->getCategories();

        // URLs
        $data['action'] = $this->url->link('extension/meschain/trendyol_importer/startImport', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('extension/meschain/trendyol_importer', 'user_token=' . $this->session->data['user_token'], true);
        $data['test_connection_url'] = $this->url->link('extension/meschain/trendyol_importer/testConnection', 'user_token=' . $this->session->data['user_token'], true);

        $data['user_token'] = $this->session->data['user_token'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/meschain/trendyol_import_wizard', $data));
    }

    public function startImport() {
        $this->load->language('extension/meschain/trendyol_importer');

        $json = array();

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->load->model('extension/meschain/trendyol_importer');

            // Validate input
            if (empty($this->request->post['session_name'])) {
                $json['error'] = $this->language->get('error_session_name');
            }

            if (!$json) {
                try {
                    // Start import process
                    $settings = array(
                        'default_category' => $this->request->post['default_category'] ?? 1,
                        'update_existing' => $this->request->post['update_existing'] ?? false,
                        'import_images' => $this->request->post['import_images'] ?? true,
                        'batch_size' => $this->request->post['batch_size'] ?? 50
                    );

                    $result = $this->model_extension_meschain_trendyol_importer->startImport(
                        $this->request->post['session_name'],
                        $settings
                    );

                    if ($result['success']) {
                        $json['success'] = $this->language->get('text_import_started');
                        $json['session_id'] = $result['session_id'];
                        $json['redirect'] = $this->url->link('extension/meschain/trendyol_importer/progress', 'session_id=' . $result['session_id'] . '&user_token=' . $this->session->data['user_token'], true);
                    } else {
                        $json['error'] = $result['message'];
                    }

                } catch (\Exception $e) {
                    $json['error'] = $e->getMessage();
                }
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function progress() {
        $this->load->language('extension/meschain/trendyol_importer');

        $this->document->setTitle($this->language->get('heading_title') . ' - ' . $this->language->get('text_import_progress'));

        $this->load->model('extension/meschain/trendyol_importer');

        $session_id = $this->request->get['session_id'] ?? 0;

        if (!$session_id) {
            $this->response->redirect($this->url->link('extension/meschain/trendyol_importer', 'user_token=' . $this->session->data['user_token'], true));
        }

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_import_progress'] = $this->language->get('text_import_progress');

        // Breadcrumbs
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/meschain/trendyol_importer', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_import_progress'),
            'href' => $this->url->link('extension/meschain/trendyol_importer/progress', 'session_id=' . $session_id . '&user_token=' . $this->session->data['user_token'], true)
        );

        $data['session_id'] = $session_id;
        $data['progress_url'] = $this->url->link('extension/meschain/trendyol_importer/getProgress', 'session_id=' . $session_id . '&user_token=' . $this->session->data['user_token'], true);
        $data['cancel_url'] = $this->url->link('extension/meschain/trendyol_importer/cancelImport', 'session_id=' . $session_id . '&user_token=' . $this->session->data['user_token'], true);
        $data['back_url'] = $this->url->link('extension/meschain/trendyol_importer', 'user_token=' . $this->session->data['user_token'], true);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/meschain/trendyol_import_progress', $data));
    }

    public function getProgress() {
        $this->load->model('extension/meschain/trendyol_importer');

        $session_id = $this->request->get['session_id'] ?? 0;
        $json = array();

        if ($session_id) {
            $progress = $this->model_extension_meschain_trendyol_importer->getImportProgress($session_id);
            
            if ($progress) {
                $json = $progress;
                
                // Calculate percentage
                if ($progress['total_products'] > 0) {
                    $json['percentage'] = round(($progress['processed_products'] / $progress['total_products']) * 100, 2);
                } else {
                    $json['percentage'] = 0;
                }
            } else {
                $json['error'] = 'Session not found';
            }
        } else {
            $json['error'] = 'Invalid session ID';
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function testConnection() {
        $json = array();

        try {
            $api_status = $this->testApiConnection();
            $json = $api_status;
        } catch (\Exception $e) {
            $json = array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function testApiConnection() {
        try {
            // Load the Trendyol API Client
            require_once(DIR_SYSTEM . 'library/meschain/api/TrendyolApiClient.php');
            
            $api_config = array(
                'api_key' => $this->config->get('meschain_trendyol_api_key'),
                'api_secret' => $this->config->get('meschain_trendyol_api_secret'),
                'supplier_id' => $this->config->get('meschain_trendyol_supplier_id'),
                'test_mode' => $this->config->get('meschain_trendyol_test_mode')
            );

            $api_client = new \MesChain\Api\TrendyolApiClient($api_config);
            return $api_client->testConnection();

        } catch (\Exception $e) {
            return array(
                'success' => false,
                'message' => 'API connection failed: ' . $e->getMessage()
            );
        }
    }

    protected function getList() {
        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $limit = 20;
        $offset = ($page - 1) * $limit;

        $this->load->model('extension/meschain/trendyol_importer');

        $data['sessions'] = $this->model_extension_meschain_trendyol_importer->getImportSessions($limit, $offset);
        $total_sessions = $this->model_extension_meschain_trendyol_importer->getTotalSessions();

        // Breadcrumbs
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/meschain/trendyol_importer', 'user_token=' . $this->session->data['user_token'], true)
        );

        // Pagination
        $data['pagination'] = $this->load->controller('common/pagination', [
            'total' => $total_sessions,
            'page'  => $page,
            'limit' => $limit,
            'url'   => $this->url->link('extension/meschain/trendyol_importer', 'user_token=' . $this->session->data['user_token'] . '&page={page}', true)
        ]);

        $data['results'] = sprintf($this->language->get('text_pagination'), ($total_sessions) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($total_sessions - $limit)) ? $total_sessions : ((($page - 1) * $limit) + $limit), $total_sessions, ceil($total_sessions / $limit));

        $data['heading_title'] = $this->language->get('heading_title');

        // URLs
        $data['add'] = $this->url->link('extension/meschain/trendyol_importer/wizard', 'user_token=' . $this->session->data['user_token'], true);
        $data['dashboard'] = $this->url->link('extension/meschain/trendyol_importer/dashboard', 'user_token=' . $this->session->data['user_token'], true);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/meschain/trendyol_importer', $data));
    }

    public function install() {
        $this->load->model('extension/meschain/trendyol_importer');
        $this->model_extension_meschain_trendyol_importer->install();
    }

    public function uninstall() {
        $this->load->model('extension/meschain/trendyol_importer');
        $this->model_extension_meschain_trendyol_importer->uninstall();
    }
}