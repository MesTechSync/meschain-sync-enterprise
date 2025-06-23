<?php
/**
 * MesChain Sync - N11 Modülü
 * OpenCart 3.0.4.0 Uyumlu
 *
 * @author MesChain Development Team
 * @version 1.0.0
 */

class ControllerExtensionModuleMeschainN11 extends Controller {

    private $error = array();

    /**
     * Ana sayfa
     */
    public function index() {
        $this->load->language('extension/module/meschain_n11');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_meschain_n11', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

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
            'href' => $this->url->link('extension/module/meschain_n11', 'user_token=' . $this->session->data['user_token'], true)
        );

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['action'] = $this->url->link('extension/module/meschain_n11', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        // Ayarları getir
        if (isset($this->request->post['module_meschain_n11_status'])) {
            $data['module_meschain_n11_status'] = $this->request->post['module_meschain_n11_status'];
        } else {
            $data['module_meschain_n11_status'] = $this->config->get('module_meschain_n11_status');
        }

        if (isset($this->request->post['module_meschain_n11_api_key'])) {
            $data['module_meschain_n11_api_key'] = $this->request->post['module_meschain_n11_api_key'];
        } else {
            $data['module_meschain_n11_api_key'] = $this->config->get('module_meschain_n11_api_key');
        }

        if (isset($this->request->post['module_meschain_n11_api_secret'])) {
            $data['module_meschain_n11_api_secret'] = $this->request->post['module_meschain_n11_api_secret'];
        } else {
            $data['module_meschain_n11_api_secret'] = $this->config->get('module_meschain_n11_api_secret');
        }

        if (isset($this->request->post['module_meschain_n11_test_mode'])) {
            $data['module_meschain_n11_test_mode'] = $this->request->post['module_meschain_n11_test_mode'];
        } else {
            $data['module_meschain_n11_test_mode'] = $this->config->get('module_meschain_n11_test_mode');
        }

        $data['user_token'] = $this->session->data['user_token'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/meschain_n11', $data));
    }

    /**
     * Doğrulama
     */
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/meschain_n11')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    /**
     * Modül kurulumu
     */
    public function install() {
        $this->load->model('user/user_group');

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/meschain_n11');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/meschain_n11');
    }

    /**
     * Modül kaldırma
     */
    public function uninstall() {
        // Cleanup işlemleri
    }
}