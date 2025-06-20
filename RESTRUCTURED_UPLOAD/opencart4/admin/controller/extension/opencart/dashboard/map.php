<?php
namespace Opencart\Admin\Controller\Extension\Opencart\Dashboard;
class Map extends \Opencart\System\Engine\Controller {
	public function dashboard(): string {
		$this->load->language('extension/opencart/dashboard/map');

		$data['heading_title'] = $this->language->get('heading_title');

		$data['token'] = $this->session->data['user_token'];

		return $this->load->view('extension/opencart/dashboard/map', $data);
	}

	public function map(): void {
		$json = array();

		$this->load->model('sale/order');

		$results = $this->model_sale_order->getTotalOrdersByCountry();

		foreach ($results as $result) {
			$json[strtolower($result['iso_code_2'])] = array(
				'total'  => $result['total'],
				'amount' => $this->currency->format($result['amount'], $this->config->get('config_currency'))
			);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
?> 