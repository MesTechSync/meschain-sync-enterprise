<?php
class ControllerCommonColumnLeft extends Controller {
	public function index() {
		if (isset($this->request->get['user_token']) && isset($this->session->data['user_token']) && ($this->request->get['user_token'] == $this->session->data['user_token'])) {
			$this->load->language('common/column_left');

			// Create a 3 level menu array
			// Level 2 can not have children
			
			// Menu
			$data['menus'][] = array(
				'id'       => 'menu-dashboard',
				'icon'	   => 'fa-dashboard',
				'name'	   => $this->language->get('text_dashboard'),
				'href'     => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
				'children' => array()
			);
			
			// Catalog
			$catalog = array();
			
			if ($this->user->hasPermission('access', 'catalog/category')) {
				$catalog[] = array(
					'name'	   => $this->language->get('text_category'),
					'href'     => $this->url->link('catalog/category', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'catalog/product')) {
				$catalog[] = array(
					'name'	   => $this->language->get('text_product'),
					'href'     => $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($catalog) {
				$data['menus'][] = array(
					'id'       => 'menu-catalog',
					'icon'	   => 'fa-tags', 
					'name'	   => $this->language->get('text_catalog'),
					'href'     => '',
					'children' => $catalog
				);		
			}
			
			// Extension
			$marketplace = array();
			
			if ($this->user->hasPermission('access', 'marketplace/marketplace')) {
				$marketplace[] = array(
					'name'	   => $this->language->get('text_marketplace'),
					'href'     => $this->url->link('marketplace/marketplace', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'marketplace/installer')) {
				$marketplace[] = array(
					'name'	   => $this->language->get('text_installer'),
					'href'     => $this->url->link('marketplace/installer', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}	
			
			if ($this->user->hasPermission('access', 'marketplace/extension')) {
				$marketplace[] = array(
					'name'	   => $this->language->get('text_extension'),
					'href'     => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()
				);
			}
								
			if ($this->user->hasPermission('access', 'marketplace/modification')) {
				$marketplace[] = array(
					'name'	   => $this->language->get('text_modification'),
					'href'     => $this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'marketplace/event')) {
				$marketplace[] = array(
					'name'	   => $this->language->get('text_event'),
					'href'     => $this->url->link('marketplace/event', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
					
			if ($marketplace) {
				$data['menus'][] = array(
					'id'       => 'menu-extension',
					'icon'	   => 'fa-puzzle-piece', 
					'name'	   => $this->language->get('text_extension'),
					'href'     => '',
					'children' => $marketplace
				);		
			}
			
			// MesChain-Sync Marketplace Integrations
			$marketplace = array();

			// Main Dashboard
			if ($this->user->hasPermission('access', 'extension/module/meschain_sync')) {
				$marketplace[] = array(
					'name'     => 'Dashboard',
					'href'     => $this->url->link('extension/module/meschain_sync', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()
				);
			}

			// Amazon SP-API Integration
			if ($this->user->hasPermission('access', 'extension/module/amazon')) {
				$marketplace[] = array(
					'name'     => 'Amazon SP-API',
					'href'     => $this->url->link('extension/module/amazon', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()
				);
			}

			// eBay REST API Integration
			if ($this->user->hasPermission('access', 'extension/module/ebay')) {
				$marketplace[] = array(
					'name'     => 'eBay REST API',
					'href'     => $this->url->link('extension/module/ebay', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()
				);
			}

			// Hepsiburada Integration
			if ($this->user->hasPermission('access', 'extension/module/hepsiburada')) {
				$marketplace[] = array(
					'name'     => 'Hepsiburada',
					'href'     => $this->url->link('extension/module/hepsiburada', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()
				);
			}

			// N11 SOAP API Integration
			if ($this->user->hasPermission('access', 'extension/module/n11')) {
				$marketplace[] = array(
					'name'     => 'N11 SOAP API',
					'href'     => $this->url->link('extension/module/n11', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()
				);
			}

			// Trendyol API Integration
			if ($this->user->hasPermission('access', 'extension/module/trendyol')) {
				$marketplace[] = array(
					'name'     => 'Trendyol API',
					'href'     => $this->url->link('extension/module/trendyol', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()
				);
			}

			// Ozon REST API Integration
			if ($this->user->hasPermission('access', 'extension/module/ozon')) {
				$marketplace[] = array(
					'name'     => 'Ozon REST API',
					'href'     => $this->url->link('extension/module/ozon', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()
				);
			}

			// Divider
			$marketplace[] = array(
				'name'     => '---',
				'href'     => '',
				'children' => array()
			);

			// N11 Category Mapping
			if ($this->user->hasPermission('access', 'extension/module/n11_category')) {
				$marketplace[] = array(
					'name'     => 'N11 Kategori Eşleştirme',
					'href'     => $this->url->link('extension/module/n11_category', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()
				);
			}

			// Cache Monitor
			if ($this->user->hasPermission('access', 'extension/module/cache_monitor')) {
				$marketplace[] = array(
					'name'     => 'Cache Monitor',
					'href'     => $this->url->link('extension/module/cache_monitor', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()
				);
			}

			// Dropshipping Management
			if ($this->user->hasPermission('access', 'extension/module/dropshipping')) {
				$marketplace[] = array(
					'name'     => 'Dropshipping Yönetimi',
					'href'     => $this->url->link('extension/module/dropshipping', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()
				);
			}

			// User Management
			if ($this->user->hasPermission('access', 'extension/module/user_management')) {
				$marketplace[] = array(
					'name'     => 'Kullanıcı Yönetimi',
					'href'     => $this->url->link('extension/module/user_management', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()
				);
			}

			// Announcement Management
			if ($this->user->hasPermission('access', 'extension/module/announcement')) {
				$marketplace[] = array(
					'name'     => 'Duyuru Yönetimi',
					'href'     => $this->url->link('extension/module/announcement', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()
				);
			}

			// RBAC Multi-Tenant Management
			if ($this->user->hasPermission('access', 'extension/module/rbac_management')) {
				$marketplace[] = array(
					'name'     => 'RBAC & Multi-Tenant',
					'href'     => $this->url->link('extension/module/rbac_management', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()
				);
			}

			// User Settings
			if ($this->user->hasPermission('access', 'extension/module/user_settings')) {
				$marketplace[] = array(
					'name'     => 'Kullanıcı Ayarları',
					'href'     => $this->url->link('extension/module/user_settings', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()
				);
			}

			// Help & Documentation
			if ($this->user->hasPermission('access', 'extension/module/help')) {
				$marketplace[] = array(
					'name'     => 'Yardım ve Dokümantasyon',
					'href'     => $this->url->link('extension/module/help', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()
				);
			}

			if ($marketplace) {
				$data['menus'][] = array(
					'id'       => 'menu-meschain',
					'icon'     => 'fa-exchange',
					'name'     => 'MesChain-Sync',
					'href'     => '',
					'children' => $marketplace
				);
			}
			
			// Sales
			$sale = array();
			
			if ($this->user->hasPermission('access', 'sale/order')) {
				$sale[] = array(
					'name'	   => $this->language->get('text_order'),
					'href'     => $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'sale/recurring')) {
				$sale[] = array(
					'name'	   => $this->language->get('text_recurring'),
					'href'     => $this->url->link('sale/recurring', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'sale/return')) {
				$sale[] = array(
					'name'	   => $this->language->get('text_return'),
					'href'     => $this->url->link('sale/return', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			// Voucher
			$voucher = array();
			
			if ($this->user->hasPermission('access', 'sale/voucher')) {
				$voucher[] = array(
					'name'	   => $this->language->get('text_voucher'),
					'href'     => $this->url->link('sale/voucher', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'sale/voucher_theme')) {
				$voucher[] = array(
					'name'	   => $this->language->get('text_voucher_theme'),
					'href'     => $this->url->link('sale/voucher_theme', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($voucher) {
				$sale[] = array(
					'name'	   => $this->language->get('text_voucher'),
					'href'     => '',
					'children' => $voucher		
				);
			}
			
			if ($sale) {
				$data['menus'][] = array(
					'id'       => 'menu-sale',
					'icon'	   => 'fa-shopping-cart', 
					'name'	   => $this->language->get('text_sale'),
					'href'     => '',
					'children' => $sale
				);
			}
			
			// Design
			$design = array();
			
			if ($this->user->hasPermission('access', 'design/layout')) {
				$design[] = array(
					'name'	   => $this->language->get('text_layout'),
					'href'     => $this->url->link('design/layout', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'design/theme')) {
				$design[] = array(
					'name'	   => $this->language->get('text_theme'),
					'href'     => $this->url->link('design/theme', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'design/translation')) {
				$design[] = array(
					'name'	   => $this->language->get('text_language_editor'),
					'href'     => $this->url->link('design/translation', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'design/banner')) {
				$design[] = array(
					'name'	   => $this->language->get('text_banner'),
					'href'     => $this->url->link('design/banner', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'design/seo_url')) {
				$design[] = array(
					'name'	   => $this->language->get('text_seo_url'),
					'href'     => $this->url->link('design/seo_url', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($design) {
				$data['menus'][] = array(
					'id'       => 'menu-design',
					'icon'	   => 'fa-television', 
					'name'	   => $this->language->get('text_design'),
					'href'     => '',
					'children' => $design
				);
			}
			
			// System
			$system = array();
			
			if ($this->user->hasPermission('access', 'setting/setting')) {
				$system[] = array(
					'name'	   => $this->language->get('text_setting'),
					'href'     => $this->url->link('setting/store', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			// Users
			$user = array();
			
			if ($this->user->hasPermission('access', 'user/user')) {
				$user[] = array(
					'name'	   => $this->language->get('text_users'),
					'href'     => $this->url->link('user/user', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'user/user_permission')) {
				$user[] = array(
					'name'	   => $this->language->get('text_user_group'),
					'href'     => $this->url->link('user/user_permission', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'user/api')) {
				$user[] = array(
					'name'	   => $this->language->get('text_api'),
					'href'     => $this->url->link('user/api', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($user) {
				$system[] = array(
					'name'	   => $this->language->get('text_users'),
					'href'     => '',
					'children' => $user		
				);
			}
			
			// Localisation
			$localisation = array();
			
			if ($this->user->hasPermission('access', 'localisation/language')) {
				$localisation[] = array(
					'name'	   => $this->language->get('text_language'),
					'href'     => $this->url->link('localisation/language', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'localisation/currency')) {
				$localisation[] = array(
					'name'	   => $this->language->get('text_currency'),
					'href'     => $this->url->link('localisation/currency', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'localisation/stock_status')) {
				$localisation[] = array(
					'name'	   => $this->language->get('text_stock_status'),
					'href'     => $this->url->link('localisation/stock_status', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'localisation/order_status')) {
				$localisation[] = array(
					'name'	   => $this->language->get('text_order_status'),
					'href'     => $this->url->link('localisation/order_status', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($localisation) {
				$system[] = array(
					'name'	   => $this->language->get('text_localisation'),
					'href'     => '',
					'children' => $localisation		
				);
			}
			
			// Tools
			$maintenance = array();
				
			if ($this->user->hasPermission('access', 'tool/backup')) {
				$maintenance[] = array(
					'name'	   => $this->language->get('text_backup'),
					'href'     => $this->url->link('tool/backup', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'tool/upload')) {
				$maintenance[] = array(
					'name'	   => $this->language->get('text_upload'),
					'href'     => $this->url->link('tool/upload', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
						
			if ($this->user->hasPermission('access', 'tool/log')) {
				$maintenance[] = array(
					'name'	   => $this->language->get('text_log'),
					'href'     => $this->url->link('tool/log', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
		
			if ($maintenance) {
				$system[] = array(
					'name'	   => $this->language->get('text_maintenance'),
					'href'     => '',
					'children' => $maintenance		
				);
			}		
			
			
			if ($system) {
				$data['menus'][] = array(
					'id'       => 'menu-system',
					'icon'	   => 'fa-cog', 
					'name'	   => $this->language->get('text_system'),
					'href'     => '',
					'children' => $system
				);
			}
			
			// Reports
			$report = array();
			
			if ($this->user->hasPermission('access', 'report/report')) {
				$report[] = array(
					'name'	   => $this->language->get('text_reports'),
					'href'     => $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'report/online')) {
				$report[] = array(
					'name'	   => $this->language->get('text_online'),
					'href'     => $this->url->link('report/online', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($this->user->hasPermission('access', 'report/statistics')) {
				$report[] = array(
					'name'	   => $this->language->get('text_statistics'),
					'href'     => $this->url->link('report/statistics', 'user_token=' . $this->session->data['user_token'], true),
					'children' => array()		
				);
			}
			
			if ($report) {
				$data['menus'][] = array(
					'id'       => 'menu-report',
					'icon'	   => 'fa-bar-chart', 
					'name'	   => $this->language->get('text_reports'),
					'href'     => '',
					'children' => $report
				);
			}
			
			return $this->load->view('common/column_left', $data);
		}
	}
} 