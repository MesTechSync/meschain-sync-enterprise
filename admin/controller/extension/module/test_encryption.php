<?php
/**
 * Test script for MeschainEncryption functionality
 * This file can be accessed via: admin/index.php?route=extension/module/test_encryption&user_token=YOUR_TOKEN
 */
class ControllerExtensionModuleTestEncryption extends Controller {
    /**
     * Test encryption functionality
     */
    public function index() {
        // Load language file
        $this->load->language('extension/module/base_marketplace');
        
        // Security check
        if (!$this->user->hasPermission('modify', 'extension/module/test_encryption')) {
            $this->response->redirect($this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true));
            return;
        }
        
        // Load encryption class
        $encryption_file = DIR_SYSTEM . 'library/meschain/encryption.php';
        if (file_exists($encryption_file)) {
            require_once($encryption_file);
            $this->encryption = new MeschainEncryption();
        } else {
            die('Encryption file not found at: ' . $encryption_file);
        }
        
        // Set up page
        $this->document->setTitle('MeschainEncryption Test');
        
        $data = array();
        $data['heading_title'] = 'Encryption Test';
        $data['user_token'] = $this->session->data['user_token'];
        
        // Run tests
        $test_data = array(
            'api_key' => 'test_api_key_12345',
            'api_secret' => 'very_secret_value_67890',
            'client_id' => 'client_123456789',
            'access_token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.test_token',
            'non_sensitive' => 'This should not be encrypted'
        );
        
        // Test encryption
        $encrypted_data = $this->encryption->encryptApiCredentials($test_data);
        
        // Test decryption
        $decrypted_data = $this->encryption->decryptApiCredentials($encrypted_data);
        
        // Prepare results
        $data['test_results'] = array(
            'original_data' => $test_data,
            'encrypted_data' => $encrypted_data,
            'decrypted_data' => $decrypted_data,
            'verification' => array(
                'api_key' => ($test_data['api_key'] === $decrypted_data['api_key']),
                'api_secret' => ($test_data['api_secret'] === $decrypted_data['api_secret']),
                'client_id' => ($test_data['client_id'] === $decrypted_data['client_id']),
                'access_token' => ($test_data['access_token'] === $decrypted_data['access_token']),
                'non_sensitive' => ($test_data['non_sensitive'] === $decrypted_data['non_sensitive'])
            )
        );
        
        // All test passed?
        $data['all_passed'] = !in_array(false, $data['test_results']['verification']);
        
        // Test individual methods
        $single_string = "Test sensitive string";
        $encrypted_string = $this->encryption->encrypt($single_string);
        $decrypted_string = $this->encryption->decrypt($encrypted_string);
        
        $data['single_test'] = array(
            'original' => $single_string,
            'encrypted' => $encrypted_string,
            'decrypted' => $decrypted_string,
            'passed' => ($single_string === $decrypted_string)
        );
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => 'Home',
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => 'Encryption Test',
            'href' => $this->url->link('extension/module/test_encryption', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Common page elements
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        // Render output
        $this->response->setOutput($this->load->view('extension/module/test_encryption', $data));
    }
} 