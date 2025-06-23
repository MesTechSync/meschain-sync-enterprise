<?php
// admin/controller/extension/meschain.php
namespace Opencart\Admin\Controller\Extension;
class Meschain extends \Opencart\System\Engine\Controller {
	public function index(): void {
		$this->load->language('extension/meschain');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/meschain', 'user_token=' . $this->session->data['user_token'])
		];

		$data['text_list'] = $this->language->get('text_list');

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

		// MesChain Extensions List
		$extensions = [];

		// Add MesChain Sync module to this list
		$extensions[] = [
			'name'      => 'MesChain Sync',
			'status'    => $this->config->get('module_meschain_sync_status') ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
			'install'   => $this->url->link('extension/module/meschain_sync|install', 'user_token=' . $this->session->data['user_token']),
			'uninstall' => $this->url->link('extension/module/meschain_sync|uninstall', 'user_token=' . $this->session->data['user_token']),
			'installed' => $this->config->get('module_meschain_sync_status') ? true : false,
			'edit'      => $this->url->link('extension/module/meschain_sync', 'user_token=' . $this->session->data['user_token'])
		];

		$data['extensions'] = $extensions;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/meschain', $data));
	}

	public function menu(string &$route, array &$data): void {
		$this->load->language('extension/meschain');

		if ($this->user->hasPermission('access', 'extension/meschain')) {
			$meschain = [];
			
			$meschain[] = [
				'name'     => $this->language->get('heading_title'),
				'href'     => $this->url->link('extension/meschain', 'user_token=' . $this->session->data['user_token']),
				'children' => []
			];
			
			// Add MesChain Sync as a submenu item
			$meschain[0]['children'][] = [
				'name'     => 'MesChain Sync',
				'href'     => $this->url->link('extension/module/meschain_sync', 'user_token=' . $this->session->data['user_token']),
				'children' => []
			];

			// Insert MesChain menu after Extensions menu
			$extension_index = array_search('extension', array_column($data['menus'], 'id'));
			
			if ($extension_index !== false) {
				// Create a new array item for MesChain
				$meschain_menu = [
					'id'       => 'menu-meschain',
					'icon'     => 'fas fa-sync',  // Choose an appropriate icon
					'name'     => $this->language->get('heading_title'),
					'href'     => '',
					'children' => $meschain
				];
				
				// Insert MesChain menu after Extensions
				array_splice($data['menus'], $extension_index + 1, 0, [$meschain_menu]);
			}
		}
	}
}
