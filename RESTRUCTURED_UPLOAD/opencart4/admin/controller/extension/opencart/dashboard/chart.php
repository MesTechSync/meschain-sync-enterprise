<?php
namespace Opencart\Admin\Controller\Extension\Opencart\Dashboard;
class Chart extends \Opencart\System\Engine\Controller {
	public function dashboard(): string {
		$this->load->language('extension/opencart/dashboard/chart');

		$data['heading_title'] = $this->language->get('heading_title');

		$data['token'] = $this->session->data['user_token'];

		$data['order'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token']);

		return $this->load->view('extension/opencart/dashboard/chart', $data);
	}

	public function chart(): void {
		$this->load->language('extension/opencart/dashboard/chart');

		$json = array();

		// Last 30 days
		$this->load->model('sale/order');

		for ($i = 0; $i < 30; $i++) {
			$date = date('Y-m-d', strtotime('-' . $i . ' day'));

			$filter_data = array(
				'filter_date_added' => $date
			);

			$orders = $this->model_sale_order->getOrders($filter_data);

			$total = 0;

			foreach ($orders as $order) {
				$total += $order['total'];
			}

			$json[date('j M', strtotime($date))] = $total;
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode(array_reverse($json, true)));
	}
}
?> 