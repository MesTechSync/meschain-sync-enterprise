<?php
/**
 * MesChain Sync - Ana Modül Controller
 * OpenCart 3.0.4.0 Uyumlu
 *
 * @author MesChain Development Team
 * @version 1.0.0
 */

class ControllerExtensionModuleMeschainSync extends Controller {

    private $error = array();

    /**
     * Ana sayfa - Marketplace listesi
     */
    public function index() {
        $this->load->language('extension/module/meschain_sync');

        $this->document->setTitle($this->language->get('heading_title'));

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
            'href' => $this->url->link('extension/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true)
        );

        // Marketplace modülleri
        $data['modules'] = array(
            'trendyol' => array(
                'name' => $this->language->get('text_trendyol'),
                'status' => $this->config->get('module_meschain_trendyol_status'),
                'edit' => $this->url->link('extension/module/meschain_trendyol', 'user_token=' . $this->session->data['user_token'], true),
                'icon' => 'fa-shopping-cart',
                'description' => $this->language->get('text_trendyol_desc')
            ),
            'n11' => array(
                'name' => $this->language->get('text_n11'),
                'status' => $this->config->get('module_meschain_n11_status'),
                'edit' => $this->url->link('extension/module/meschain_n11', 'user_token=' . $this->session->data['user_token'], true),
                'icon' => 'fa-shopping-bag',
                'description' => $this->language->get('text_n11_desc')
            ),
            'hepsiburada' => array(
                'name' => $this->language->get('text_hepsiburada'),
                'status' => $this->config->get('module_meschain_hepsiburada_status'),
                'edit' => $this->url->link('extension/module/meschain_hepsiburada', 'user_token=' . $this->session->data['user_token'], true),
                'icon' => 'fa-shopping-basket',
                'description' => $this->language->get('text_hepsiburada_desc')
            ),
            'amazon' => array(
                'name' => $this->language->get('text_amazon'),
                'status' => $this->config->get('module_meschain_amazon_status'),
                'edit' => $this->url->link('extension/module/meschain_amazon', 'user_token=' . $this->session->data['user_token'], true),
                'icon' => 'fa-amazon',
                'description' => $this->language->get('text_amazon_desc')
            ),
            'ozon' => array(
                'name' => $this->language->get('text_ozon'),
                'status' => $this->config->get('module_meschain_ozon_status'),
                'edit' => $this->url->link('extension/module/meschain_ozon', 'user_token=' . $this->session->data['user_token'], true),
                'icon' => 'fa-globe',
                'description' => $this->language->get('text_ozon_desc')
            ),
            'ebay' => array(
                'name' => $this->language->get('text_ebay'),
                'status' => $this->config->get('module_meschain_ebay_status'),
                'edit' => $this->url->link('extension/module/meschain_ebay', 'user_token=' . $this->session->data['user_token'], true),
                'icon' => 'fa-gavel',
                'description' => $this->language->get('text_ebay_desc')
            )
        );

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/meschain_sync', $data));
    }

    /**
     * Modül durumunu değiştir
     */
    public function toggle() {
        $this->load->language('extension/module/meschain_sync');

        $json = array();

        if (!$this->user->hasPermission('modify', 'extension/module/meschain_sync')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (!$json) {
            $module = $this->request->get['module'];
            $status = $this->request->get['status'];

            $this->load->model('setting/setting');

            $setting_data = array(
                'module_meschain_' . $module . '_status' => $status
            );

            $this->model_setting_setting->editSetting('module_meschain_' . $module, $setting_data);

            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Modül kurulumu
     */
    public function install() {
        $this->load->model('setting/event');
        $this->load->model('user/user_group');

        // Event'leri ekle
        $this->model_setting_event->addEvent('meschain_sync', 'admin/view/common/column_left/before', 'extension/module/meschain_sync/addMenuItem');

        // Yetkileri ekle
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/meschain_sync');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/meschain_sync');

        // Alt modüller için yetkiler
        $modules = array('trendyol', 'n11', 'hepsiburada', 'amazon', 'ozon', 'ebay');
        foreach ($modules as $module) {
            $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/meschain_' . $module);
            $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/meschain_' . $module);
        }
    }

    /**
     * Modül kaldırma
     */
    public function uninstall() {
        $this->load->model('setting/event');

        $this->model_setting_event->deleteEventByCode('meschain_sync');
    }

    /**
     * Menü öğesi ekle
     */
    public function addMenuItem($route, $data, $output) {
        $this->load->language('extension/module/meschain_sync');

        $meschain_menu = '';

        $meschain_menu .= '<li id="menu-meschain">';
        $meschain_menu .= '<a href="#" class="parent"><i class="fa fa-shopping-cart fa-fw"></i> <span>' . $this->language->get('text_meschain_sync') . '</span> <span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span></a>';
        $meschain_menu .= '<ul class="treeview-menu">';

        if ($this->user->hasPermission('access', 'extension/module/meschain_sync')) {
            $meschain_menu .= '<li><a href="' . $this->url->link('extension/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true) . '"><i class="fa fa-circle-o"></i> ' . $this->language->get('text_dashboard') . '</a></li>';
        }

        if ($this->user->hasPermission('access', 'extension/module/meschain_trendyol')) {
            $meschain_menu .= '<li><a href="' . $this->url->link('extension/module/meschain_trendyol', 'user_token=' . $this->session->data['user_token'], true) . '"><i class="fa fa-circle-o"></i> ' . $this->language->get('text_trendyol') . '</a></li>';
        }

        if ($this->user->hasPermission('access', 'extension/module/meschain_n11')) {
            $meschain_menu .= '<li><a href="' . $this->url->link('extension/module/meschain_n11', 'user_token=' . $this->session->data['user_token'], true) . '"><i class="fa fa-circle-o"></i> ' . $this->language->get('text_n11') . '</a></li>';
        }

        if ($this->user->hasPermission('access', 'extension/module/meschain_hepsiburada')) {
            $meschain_menu .= '<li><a href="' . $this->url->link('extension/module/meschain_hepsiburada', 'user_token=' . $this->session->data['user_token'], true) . '"><i class="fa fa-circle-o"></i> ' . $this->language->get('text_hepsiburada') . '</a></li>';
        }

        if ($this->user->hasPermission('access', 'extension/module/meschain_amazon')) {
            $meschain_menu .= '<li><a href="' . $this->url->link('extension/module/meschain_amazon', 'user_token=' . $this->session->data['user_token'], true) . '"><i class="fa fa-circle-o"></i> ' . $this->language->get('text_amazon') . '</a></li>';
        }

        if ($this->user->hasPermission('access', 'extension/module/meschain_ozon')) {
            $meschain_menu .= '<li><a href="' . $this->url->link('extension/module/meschain_ozon', 'user_token=' . $this->session->data['user_token'], true) . '"><i class="fa fa-circle-o"></i> ' . $this->language->get('text_ozon') . '</a></li>';
        }

        if ($this->user->hasPermission('access', 'extension/module/meschain_ebay')) {
            $meschain_menu .= '<li><a href="' . $this->url->link('extension/module/meschain_ebay', 'user_token=' . $this->session->data['user_token'], true) . '"><i class="fa fa-circle-o"></i> ' . $this->language->get('text_ebay') . '</a></li>';
        }

        $meschain_menu .= '</ul>';
        $meschain_menu .= '</li>';

        $output = str_replace('<li id="menu-system">', $meschain_menu . '<li id="menu-system">', $output);

        return $output;
    }
}
