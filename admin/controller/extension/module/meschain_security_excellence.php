<?php
/**
 * MesChain-Sync Security Excellence Controller
 * ATOM-C013: Security Excellence Integration
 * 
 * Admin controller for security dashboard management,
 * vulnerability monitoring, and compliance tracking
 * 
 * @package    MesChain-Sync
 * @subpackage Admin\Controller
 * @version    3.0.4.0
 * @author     MesChain Development Team
 * @copyright  2025 MesChain-Sync Enterprise
 * @license    Commercial License
 * @since      ATOM-C013
 */

class ControllerExtensionModuleMeschainSecurityExcellence extends Controller
{
    /**
     * @var object Security Excellence Framework instance
     */
    private $securityFramework;
    
    /**
     * @var array Error messages
     */
    private $error = [];
    
    /**
     * Constructor
     */
    public function __construct($registry)
    {
        parent::__construct($registry);
        
        // Load security framework
        require_once(DIR_SYSTEM . 'library/meschain/security/SecurityExcellenceFramework.php');
        $this->securityFramework = new \MesChain\Security\SecurityExcellenceFramework(
            $this->db,
            $this->log,
            $this->config->get('meschain_security_config')
        );
    }
    
    /**
     * Main security dashboard index
     * 
     * @return void
     */
    public function index()
    {
        try {
            // Load language file
            $this->load->language('extension/module/meschain_security_excellence');
            
            // Set page title
            $this->document->setTitle($this->language->get('heading_title'));
            
            // Load models
            $this->load->model('setting/setting');
            $this->load->model('extension/module/meschain_security_excellence');
            
            // Handle form submission
            if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
                $this->model_setting_setting->editSetting('module_meschain_security_excellence', $this->request->post);
                
                $this->session->data['success'] = $this->language->get('text_success');
                
                $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
            }
            
            // Get security analytics
            $securityAnalytics = $this->securityFramework->getSecurityAnalytics('24h');
            
            // Get compliance status
            $complianceStatus = $this->securityFramework->checkComplianceStatus();
            
            // Perform quick vulnerability scan
            $vulnerabilityScan = $this->securityFramework->performVulnerabilityScan(['quick' => true]);
            
            // Prepare template data
            $data = $this->prepareTemplateData();
            $data['security_analytics'] = $securityAnalytics;
            $data['compliance_status'] = $complianceStatus;
            $data['vulnerability_scan'] = $vulnerabilityScan;
            $data['framework_status'] = $this->securityFramework->getStatus();
            
            // Set breadcrumbs
            $data['breadcrumbs'] = $this->getBreadcrumbs();
            
            // Render template
            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');
            
            $this->response->setOutput($this->load->view('extension/module/meschain_security_excellence', $data));
            
        } catch (Exception $e) {
            $this->log->write('Security Excellence Controller Error: ' . $e->getMessage());
            $this->error['warning'] = 'Security dashboard initialization failed: ' . $e->getMessage();
            
            // Fallback error display
            $data = $this->prepareTemplateData();
            $data['error_warning'] = $this->error['warning'];
            
            $this->response->setOutput($this->load->view('extension/module/meschain_security_excellence', $data));
        }
    }
    
    /**
     * AJAX endpoint for vulnerability scan
     * 
     * @return void
     */
    public function scan()
    {
        try {
            // Verify AJAX request
            if (!$this->request->server['HTTP_X_REQUESTED_WITH'] || 
                strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
                throw new Exception('Invalid request method');
            }
            
            // Verify user permissions
            if (!$this->user->hasPermission('modify', 'extension/module/meschain_security_excellence')) {
                throw new Exception('Access denied');
            }
            
            // Get scan options
            $options = [
                'type' => $this->request->post['scan_type'] ?? 'comprehensive',
                'deep_scan' => isset($this->request->post['deep_scan']) ? (bool)$this->request->post['deep_scan'] : false
            ];
            
            // Perform vulnerability scan
            $results = $this->securityFramework->performVulnerabilityScan($options);
            
            // Return JSON response
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'data' => $results,
                'message' => 'Vulnerability scan completed successfully'
            ]));
            
        } catch (Exception $e) {
            $this->log->write('Security Scan Error: ' . $e->getMessage());
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }
    
    /**
     * AJAX endpoint for command validation
     * 
     * @return void
     */
    public function validateCommand()
    {
        try {
            // Verify AJAX request
            if (!$this->request->server['HTTP_X_REQUESTED_WITH'] || 
                strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
                throw new Exception('Invalid request method');
            }
            
            // Get command to validate
            $command = $this->request->post['command'] ?? '';
            $userId = $this->user->getId();
            
            if (empty($command)) {
                throw new Exception('Command is required');
            }
            
            // Validate command using security framework
            $result = $this->securityFramework->validateCommand($command, $userId);
            
            // Return JSON response
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'data' => $result
            ]));
            
        } catch (Exception $e) {
            $this->log->write('Command Validation Error: ' . $e->getMessage());
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }
    
    /**
     * AJAX endpoint for security analytics
     * 
     * @return void
     */
    public function analytics()
    {
        try {
            // Verify AJAX request
            if (!$this->request->server['HTTP_X_REQUESTED_WITH'] || 
                strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
                throw new Exception('Invalid request method');
            }
            
            // Get timeframe
            $timeframe = $this->request->get['timeframe'] ?? '24h';
            
            // Get security analytics
            $analytics = $this->securityFramework->getSecurityAnalytics($timeframe);
            
            // Return JSON response
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'data' => $analytics
            ]));
            
        } catch (Exception $e) {
            $this->log->write('Security Analytics Error: ' . $e->getMessage());
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }
    
    /**
     * AJAX endpoint for compliance status
     * 
     * @return void
     */
    public function compliance()
    {
        try {
            // Verify AJAX request
            if (!$this->request->server['HTTP_X_REQUESTED_WITH'] || 
                strtolower($this->request->server['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
                throw new Exception('Invalid request method');
            }
            
            // Get compliance standard
            $standard = $this->request->get['standard'] ?? null;
            
            // Get compliance status
            $compliance = $this->securityFramework->checkComplianceStatus($standard);
            
            // Return JSON response
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'data' => $compliance
            ]));
            
        } catch (Exception $e) {
            $this->log->write('Compliance Check Error: ' . $e->getMessage());
            
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]));
        }
    }
    
    /**
     * Security settings configuration
     * 
     * @return void
     */
    public function settings()
    {
        try {
            // Load language file
            $this->load->language('extension/module/meschain_security_excellence');
            
            // Set page title
            $this->document->setTitle($this->language->get('heading_title_settings'));
            
            // Handle form submission
            if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateSettings()) {
                $this->model_setting_setting->editSetting('meschain_security_config', $this->request->post);
                
                $this->session->data['success'] = $this->language->get('text_settings_success');
                
                $this->response->redirect($this->url->link('extension/module/meschain_security_excellence/settings', 'user_token=' . $this->session->data['user_token'], true));
            }
            
            // Prepare template data
            $data = $this->prepareTemplateData();
            $data['current_config'] = $this->config->get('meschain_security_config');
            
            // Set breadcrumbs
            $data['breadcrumbs'] = $this->getBreadcrumbs('settings');
            
            // Render template
            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');
            
            $this->response->setOutput($this->load->view('extension/module/meschain_security_excellence_settings', $data));
            
        } catch (Exception $e) {
            $this->log->write('Security Settings Error: ' . $e->getMessage());
            $this->error['warning'] = 'Security settings initialization failed: ' . $e->getMessage();
        }
    }
    
    /**
     * Validate form data
     * 
     * @return bool True if valid
     */
    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/meschain_security_excellence')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        return !$this->error;
    }
    
    /**
     * Validate settings form data
     * 
     * @return bool True if valid
     */
    protected function validateSettings()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/meschain_security_excellence')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        // Validate scan interval
        if (isset($this->request->post['scan_interval'])) {
            $interval = (int)$this->request->post['scan_interval'];
            if ($interval < 300 || $interval > 86400) {
                $this->error['scan_interval'] = 'Scan interval must be between 300 and 86400 seconds';
            }
        }
        
        // Validate alert threshold
        if (isset($this->request->post['alert_threshold'])) {
            $threshold = (int)$this->request->post['alert_threshold'];
            if ($threshold < 1 || $threshold > 4) {
                $this->error['alert_threshold'] = 'Alert threshold must be between 1 and 4';
            }
        }
        
        return !$this->error;
    }
    
    /**
     * Prepare common template data
     * 
     * @return array Template data
     */
    private function prepareTemplateData()
    {
        // Load language file
        $this->load->language('extension/module/meschain_security_excellence');
        
        $data = [];
        
        // Language strings
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_scan'] = $this->language->get('button_scan');
        
        // URLs
        $data['action'] = $this->url->link('extension/module/meschain_security_excellence', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        $data['scan_url'] = $this->url->link('extension/module/meschain_security_excellence/scan', 'user_token=' . $this->session->data['user_token'], true);
        $data['analytics_url'] = $this->url->link('extension/module/meschain_security_excellence/analytics', 'user_token=' . $this->session->data['user_token'], true);
        $data['compliance_url'] = $this->url->link('extension/module/meschain_security_excellence/compliance', 'user_token=' . $this->session->data['user_token'], true);
        $data['settings_url'] = $this->url->link('extension/module/meschain_security_excellence/settings', 'user_token=' . $this->session->data['user_token'], true);
        
        // User token
        $data['user_token'] = $this->session->data['user_token'];
        
        // Error messages
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        
        // Success message
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        return $data;
    }
    
    /**
     * Get breadcrumbs
     * 
     * @param string $page Current page
     * @return array Breadcrumbs
     */
    private function getBreadcrumbs($page = 'index')
    {
        $breadcrumbs = [];
        
        $breadcrumbs[] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        $breadcrumbs[] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        ];
        
        if ($page === 'settings') {
            $breadcrumbs[] = [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/meschain_security_excellence', 'user_token=' . $this->session->data['user_token'], true)
            ];
            
            $breadcrumbs[] = [
                'text' => $this->language->get('heading_title_settings'),
                'href' => $this->url->link('extension/module/meschain_security_excellence/settings', 'user_token=' . $this->session->data['user_token'], true)
            ];
        } else {
            $breadcrumbs[] = [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/meschain_security_excellence', 'user_token=' . $this->session->data['user_token'], true)
            ];
        }
        
        return $breadcrumbs;
    }
    
    /**
     * Install method
     * 
     * @return void
     */
    public function install()
    {
        try {
            // Create security tables
            $this->load->model('extension/module/meschain_security_excellence');
            $this->model_extension_module_meschain_security_excellence->install();
            
            // Set default configuration
            $defaultConfig = [
                'scan_interval' => 3600,
                'threat_intelligence_update' => 1800,
                'compliance_check_interval' => 86400,
                'alert_threshold' => 3,
                'ampersand_prevention' => true,
                'real_time_monitoring' => true
            ];
            
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('meschain_security_config', $defaultConfig);
            
            $this->log->write('Security Excellence Module installed successfully');
            
        } catch (Exception $e) {
            $this->log->write('Security Excellence Module installation failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Uninstall method
     * 
     * @return void
     */
    public function uninstall()
    {
        try {
            // Remove security tables
            $this->load->model('extension/module/meschain_security_excellence');
            $this->model_extension_module_meschain_security_excellence->uninstall();
            
            // Remove configuration
            $this->load->model('setting/setting');
            $this->model_setting_setting->deleteSetting('meschain_security_config');
            $this->model_setting_setting->deleteSetting('module_meschain_security_excellence');
            
            $this->log->write('Security Excellence Module uninstalled successfully');
            
        } catch (Exception $e) {
            $this->log->write('Security Excellence Module uninstallation failed: ' . $e->getMessage());
            throw $e;
        }
    }
} 