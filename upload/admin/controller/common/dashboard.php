<?php
class ControllerCommonDashboard extends Controller {
	public function index() {
		$this->load->language('common/dashboard');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		// Dashboard Extensions
		$dashboards = array();

		$this->load->model('setting/extension');

		// Get a list of installed modules
		$extensions = $this->model_setting_extension->getInstalled('dashboard');
		
		// Add MesChain-Sync main dashboard widget
		if ($this->config->get('module_meschain_sync_status')) {
			$dashboards[] = array(
				'code'       => 'meschain_sync',
				'width'      => 12,
				'sort_order' => 1
			);
		}
		
		// Add Amazon dashboard widget if module is installed
		if ($this->config->get('module_amazon_status')) {
			$dashboards[] = array(
				'code'       => 'amazon',
				'width'      => 6,
				'sort_order' => 2
			);
		}
		
		// Add eBay dashboard widget if module is installed
		if ($this->config->get('module_ebay_status')) {
			$dashboards[] = array(
				'code'       => 'ebay',
				'width'      => 6,
				'sort_order' => 3
			);
		}
		
		// Add Hepsiburada dashboard widget if module is installed
		if ($this->config->get('module_hepsiburada_status')) {
			$dashboards[] = array(
				'code'       => 'hepsiburada',
				'width'      => 6,
				'sort_order' => 4
			);
		}
		
		// Add N11 dashboard widget if module is installed
		if ($this->config->get('module_n11_status')) {
			$dashboards[] = array(
				'code'       => 'n11',
				'width'      => 6,
				'sort_order' => 5
			);
		}
		
		// Add Trendyol dashboard widget if module is installed
		if ($this->config->get('module_trendyol_status')) {
			$dashboards[] = array(
				'code'       => 'trendyol',
				'width'      => 6,
				'sort_order' => 6
			);
		}
		
		// Add Ozon dashboard widget if module is installed
		if ($this->config->get('module_ozon_status')) {
			$dashboards[] = array(
				'code'       => 'ozon',
				'width'      => 6,
				'sort_order' => 7
			);
		}
		
		// Add Cache Monitor dashboard widget if module is installed
		if ($this->config->get('module_cache_monitor_status')) {
			$dashboards[] = array(
				'code'       => 'cache_monitor',
				'width'      => 4,
				'sort_order' => 8
			);
		}
		
		// Add Dropshipping dashboard widget if module is installed
		if ($this->config->get('module_dropshipping_status')) {
			$dashboards[] = array(
				'code'       => 'dropshipping',
				'width'      => 4,
				'sort_order' => 9
			);
		}
		
		// Add User Management dashboard widget if module is installed
		if ($this->config->get('module_user_management_status')) {
			$dashboards[] = array(
				'code'       => 'user_management',
				'width'      => 4,
				'sort_order' => 10
			);
		}
		
		// Add files for each dashboard
		foreach ($extensions as $code) {
			if ($this->config->get('dashboard_' . $code . '_status') && $this->user->hasPermission('access', 'extension/dashboard/' . $code)) {
				$dashboards[] = array(
					'code'       => $code,
					'width'      => $this->config->get('dashboard_' . $code . '_width'),
					'sort_order' => $this->config->get('dashboard_' . $code . '_sort_order')
				);
			}
		}

		// Sort dashboards by sort order
		$sort_order = array();
		foreach ($dashboards as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}
		array_multisort($sort_order, SORT_ASC, $dashboards);

		// Load each dashboard widget
		$data['dashboards'] = array();
		
		foreach ($dashboards as $dashboard) {
			$widget_data = array();
			
			// Check if it's a MesChain-Sync module
			$meschain_modules = array('meschain_sync', 'amazon', 'ebay', 'hepsiburada', 'n11', 'trendyol', 'ozon', 'cache_monitor', 'dropshipping', 'user_management');
			
			if (in_array($dashboard['code'], $meschain_modules)) {
				// Load MesChain module dashboard widget
				$widget_data = $this->loadMeschainWidget($dashboard['code']);
			} else {
				// Load standard OpenCart dashboard widget
				if (file_exists(DIR_APPLICATION . 'controller/extension/dashboard/' . $dashboard['code'] . '.php') && $this->user->hasPermission('access', 'extension/dashboard/' . $dashboard['code'])) {
					$widget_data = $this->load->controller('extension/dashboard/' . $dashboard['code']);
				}
			}
			
			if ($widget_data) {
				$data['dashboards'][] = array(
					'code'       => $dashboard['code'],
					'width'      => $dashboard['width'],
					'sort_order' => $dashboard['sort_order'],
					'content'    => $widget_data
				);
			}
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('common/dashboard', $data));
	}
	
	/**
	 * Load MesChain-Sync module dashboard widget
	 */
	private function loadMeschainWidget($module_code) {
		try {
			// Modeli yükle - güvenli kontrol ile
			$model_file = DIR_APPLICATION . 'model/extension/module/' . $module_code . '.php';
			if (!file_exists($model_file)) {
				return '<div class="panel panel-default"><div class="panel-body"><p>Model dosyası bulunamadı: ' . $module_code . '</p></div></div>';
			}
			
			$this->load->model('extension/module/' . $module_code);
			
			// Model nesnesini güvenli şekilde al
			$model_name = 'model_extension_module_' . $module_code;
			$model = $this->{$model_name};
			
			// getDashboardStats metodu var mı kontrol et
			if ($model && method_exists($model, 'getDashboardStats')) {
				$stats = $model->getDashboardStats();
			} else {
				// Varsayılan istatistikler
				$stats = array(
					'total_products' => 0,
					'total_orders' => 0,
					'total_sync' => 0,
					'last_sync' => 'Hiçbir zaman',
					'status' => 'error',
					'recent_activity' => 'Modül yapılandırılmamış'
				);
			}
			
			// Widget template dosyası var mı kontrol et
			$template_file = DIR_TEMPLATE . 'extension/dashboard/meschain_widget.twig';
			if (!file_exists($template_file)) {
				return '<div class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title">' . ucfirst(str_replace('_', ' ', $module_code)) . '</h3>
					</div>
					<div class="panel-body">
						<p>Template dosyası bulunamadı</p>
						<p>Stats: ' . json_encode($stats) . '</p>
					</div>
				</div>';
			}
			
			// Widget verilerini hazırla
			$data = array();
			$data['module_name'] = ucfirst(str_replace('_', ' ', $module_code));
			$data['stats'] = $stats;
			$data['module_code'] = $module_code;
			$data['user_token'] = $this->session->data['user_token'];
			
			// Widget HTML'ini döndür
			return $this->load->view('extension/dashboard/meschain_widget', $data);
			
		} catch (Exception $e) {
			// Hata durumunda basit widget döndür
			return '<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title">
						<i class="fa fa-exclamation-triangle"></i> ' . ucfirst(str_replace('_', ' ', $module_code)) . '
					</h3>
				</div>
				<div class="panel-body">
					<p class="text-danger">Widget yüklenemedi: ' . $e->getMessage() . '</p>
					<a href="index.php?route=extension/module/' . $module_code . '&user_token=' . $this->session->data['user_token'] . '" class="btn btn-primary btn-sm">
						<i class="fa fa-cog"></i> Modüle Git
					</a>
				</div>
			</div>';
		}
	}
} 