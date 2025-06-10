<?php
/**
 * MesChain Security Management Controller
 * 
 * @category   MesChain
 * @package    Security Management
 * @author     MesChain Development Team
 * @copyright  2025 MesChain
 * @license    https://meschain.com/license
 * @version    1.0.0
 */

class ControllerExtensionModuleMeschainSecurity extends Controller {
    
    private $error = [];
    
    public function index() {
        $this->load->language('extension/module/meschain_security');
        
        $this->document->setTitle($this->language->get('heading_title'));
        
        // Load security libraries
        $this->load->library('meschain/security/oauth_server');
        $this->load->library('meschain/security/rbac_manager');
        
        $oauth_server = new MesChainOAuthServer($this->registry);
        $rbac_manager = new MesChainRBACManager($this->registry);
        
        $data['breadcrumbs'] = [];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/meschain_security', 'user_token=' . $this->session->data['user_token'], true)
        ];
        
        // Get OAuth statistics
        $data['oauth_stats'] = $oauth_server->getStatistics();
        
        // Get RBAC statistics
        $data['rbac_stats'] = $rbac_manager->getStatistics();
        
        // Get OAuth clients
        $data['oauth_clients'] = $this->getOAuthClients();
        
        // Get roles and permissions
        $data['roles'] = $rbac_manager->getAllRoles();
        $data['permissions'] = $rbac_manager->getAllPermissions();
        
        // Get recent security events
        $data['security_events'] = $this->getSecurityEvents();
        
        // Action URLs
        $data['action_create_oauth_client'] = $this->url->link('extension/module/meschain_security/createOAuthClient', 'user_token=' . $this->session->data['user_token'], true);
        $data['action_create_role'] = $this->url->link('extension/module/meschain_security/createRole', 'user_token=' . $this->session->data['user_token'], true);
        $data['action_assign_role'] = $this->url->link('extension/module/meschain_security/assignRole', 'user_token=' . $this->session->data['user_token'], true);
        $data['action_revoke_token'] = $this->url->link('extension/module/meschain_security/revokeToken', 'user_token=' . $this->session->data['user_token'], true);
        $data['action_test_permission'] = $this->url->link('extension/module/meschain_security/testPermission', 'user_token=' . $this->session->data['user_token'], true);
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/meschain_security', $data));
    }
    
    /**
     * Create OAuth client
     */
    public function createOAuthClient() {
        $this->load->language('extension/module/meschain_security');
        
        $json = [];
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $client_name = $this->request->post['client_name'] ?? '';
            $redirect_uri = $this->request->post['redirect_uri'] ?? '';
            $scopes = $this->request->post['scopes'] ?? [];
            
            if (empty($client_name)) {
                $json['error'] = 'Client name is required';
            } elseif (empty($redirect_uri)) {
                $json['error'] = 'Redirect URI is required';
            } else {
                $client_data = $this->createOAuthClientData($client_name, $redirect_uri, $scopes);
                
                if ($client_data) {
                    $json['success'] = 'OAuth client created successfully';
                    $json['client_data'] = $client_data;
                } else {
                    $json['error'] = 'Failed to create OAuth client';
                }
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Create role
     */
    public function createRole() {
        $this->load->language('extension/module/meschain_security');
        
        $json = [];
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $role_name = $this->request->post['role_name'] ?? '';
            $description = $this->request->post['description'] ?? '';
            $permissions = $this->request->post['permissions'] ?? [];
            
            if (empty($role_name)) {
                $json['error'] = 'Role name is required';
            } else {
                $this->load->library('meschain/security/rbac_manager');
                $rbac_manager = new MesChainRBACManager($this->registry);
                
                $role_id = $rbac_manager->createRole($role_name, $description, $permissions, $this->user->getId());
                
                if ($role_id) {
                    $json['success'] = 'Role created successfully';
                    $json['role_id'] = $role_id;
                } else {
                    $json['error'] = 'Failed to create role';
                }
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Assign role to user
     */
    public function assignRole() {
        $this->load->language('extension/module/meschain_security');
        
        $json = [];
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $user_id = $this->request->post['user_id'] ?? '';
            $role_id = $this->request->post['role_id'] ?? '';
            
            if (empty($user_id) || empty($role_id)) {
                $json['error'] = 'User ID and Role ID are required';
            } else {
                $this->load->library('meschain/security/rbac_manager');
                $rbac_manager = new MesChainRBACManager($this->registry);
                
                if ($rbac_manager->assignRole($user_id, $role_id, $this->user->getId())) {
                    $json['success'] = 'Role assigned successfully';
                } else {
                    $json['error'] = 'Failed to assign role';
                }
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Revoke token
     */
    public function revokeToken() {
        $this->load->language('extension/module/meschain_security');
        
        $json = [];
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $token = $this->request->post['token'] ?? '';
            $token_type = $this->request->post['token_type'] ?? 'access_token';
            
            if (empty($token)) {
                $json['error'] = 'Token is required';
            } else {
                $this->load->library('meschain/security/oauth_server');
                $oauth_server = new MesChainOAuthServer($this->registry);
                
                $result = $oauth_server->revokeToken($token, $token_type);
                
                if ($result['success']) {
                    $json['success'] = 'Token revoked successfully';
                } else {
                    $json['error'] = $result['error'];
                }
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Test permission
     */
    public function testPermission() {
        $this->load->language('extension/module/meschain_security');
        
        $json = [];
        
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $user_id = $this->request->post['user_id'] ?? '';
            $permission = $this->request->post['permission'] ?? '';
            
            if (empty($user_id) || empty($permission)) {
                $json['error'] = 'User ID and permission are required';
            } else {
                $this->load->library('meschain/security/rbac_manager');
                $rbac_manager = new MesChainRBACManager($this->registry);
                
                $has_permission = $rbac_manager->hasPermission($user_id, $permission);
                
                $json['success'] = true;
                $json['has_permission'] = $has_permission;
                $json['message'] = $has_permission ? 'User has permission' : 'User does not have permission';
            }
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Get OAuth clients
     */
    private function getOAuthClients() {
        $query = $this->db->query("
            SELECT * FROM " . DB_PREFIX . "meschain_oauth_clients 
            ORDER BY created_at DESC
        ");
        
        return $query->rows;
    }
    
    /**
     * Create OAuth client data
     */
    private function createOAuthClientData($client_name, $redirect_uri, $scopes = []) {
        // Generate client credentials
        $client_id = 'mc_' . bin2hex(random_bytes(8));
        $client_secret = bin2hex(random_bytes(32));
        
        // Insert client
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "meschain_oauth_clients 
            (client_id, client_secret, client_name, redirect_uri, scopes, status, created_at) 
            VALUES (
                '" . $this->db->escape($client_id) . "',
                '" . $this->db->escape($client_secret) . "',
                '" . $this->db->escape($client_name) . "',
                '" . $this->db->escape($redirect_uri) . "',
                '" . $this->db->escape(json_encode($scopes)) . "',
                1,
                NOW()
            )
        ");
        
        return [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'client_name' => $client_name,
            'redirect_uri' => $redirect_uri,
            'scopes' => $scopes
        ];
    }
    
    /**
     * Get recent security events
     */
    private function getSecurityEvents() {
        $events = [];
        
        // OAuth events
        $oauth_query = $this->db->query("
            SELECT 'oauth' as type, event, client_id as identifier, created_at 
            FROM " . DB_PREFIX . "meschain_auth_logs 
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        
        $events = array_merge($events, $oauth_query->rows);
        
        // RBAC events
        $rbac_query = $this->db->query("
            SELECT 'rbac' as type, event, user_id as identifier, created_at 
            FROM " . DB_PREFIX . "meschain_rbac_logs 
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        
        $events = array_merge($events, $rbac_query->rows);
        
        // Sort by date
        usort($events, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($events, 0, 20);
    }
    
    /**
     * Initialize security system
     */
    public function initializeSecurity() {
        $json = [];
        
        try {
            // Initialize RBAC defaults
            $this->load->library('meschain/security/rbac_manager');
            $rbac_manager = new MesChainRBACManager($this->registry);
            
            if ($rbac_manager->initializeDefaults()) {
                $json['success'] = 'Security system initialized successfully';
            } else {
                $json['error'] = 'Failed to initialize security system';
            }
            
        } catch (Exception $e) {
            $json['error'] = 'Initialization error: ' . $e->getMessage();
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Get security dashboard data
     */
    public function getDashboardData() {
        $this->load->library('meschain/security/oauth_server');
        $this->load->library('meschain/security/rbac_manager');
        
        $oauth_server = new MesChainOAuthServer($this->registry);
        $rbac_manager = new MesChainRBACManager($this->registry);
        
        $json = [
            'oauth_stats' => $oauth_server->getStatistics(),
            'rbac_stats' => $rbac_manager->getStatistics(),
            'security_events' => $this->getSecurityEvents(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    /**
     * Security audit report
     */
    public function auditReport() {
        $this->load->language('extension/module/meschain_security');
        
        $data['audit_data'] = $this->generateAuditReport();
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }
    
    /**
     * Generate security audit report
     */
    private function generateAuditReport() {
        $report = [];
        
        // Active tokens audit
        $report['active_tokens'] = $this->db->query("
            SELECT COUNT(*) as count 
            FROM " . DB_PREFIX . "meschain_refresh_tokens 
            WHERE expires_at > NOW()
        ")->row['count'];
        
        // Failed authentications (last 24h)
        $report['failed_auths'] = $this->db->query("
            SELECT COUNT(*) as count 
            FROM " . DB_PREFIX . "meschain_auth_logs 
            WHERE event = 'authentication_failed' 
            AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ")->row['count'];
        
        // Users without roles
        $report['users_without_roles'] = $this->db->query("
            SELECT COUNT(*) as count 
            FROM " . DB_PREFIX . "user u
            LEFT JOIN " . DB_PREFIX . "meschain_user_roles ur ON u.user_id = ur.user_id
            WHERE ur.user_id IS NULL
        ")->row['count'];
        
        // Expired tokens (should be cleaned)
        $report['expired_tokens'] = $this->db->query("
            SELECT COUNT(*) as count 
            FROM " . DB_PREFIX . "meschain_refresh_tokens 
            WHERE expires_at <= NOW()
        ")->row['count'];
        
        return $report;
    }
} 