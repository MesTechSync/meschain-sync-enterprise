<?php
namespace Opencart\Admin\Controller\Extension\Opencart\Dashboard;
class Sale extends \Opencart\System\Engine\Controller {
	public function dashboard(): string {
		$this->load->language('extension/opencart/dashboard/sale');

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_view'] = $this->language->get('text_view');

		$data['token'] = $this->session->data['user_token'];

		$today = $this->getTodaySales();

		$data['total'] = $this->currency->format($today['total'], $this->config->get('config_currency'));
		$data['sales'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token']);

		$yesterday = $this->getYesterdaySales();

		$difference = $today['total'] - $yesterday['total'];

		if ($difference > 0) {
			$data['percentage'] = '+' . round((($difference) * 100) / $today['total'], 2);
		} else {
			$data['percentage'] = round((($difference) * 100) / $today['total'], 2);
		}

		return $this->load->view('extension/opencart/dashboard/sale', $data);
	}

	private function getTodaySales(): array {
		$this->load->model('sale/order');

		$today = date('Y-m-d');

		$filter_data = array(
			'filter_date_added' => $today
		);

		$orders = $this->model_sale_order->getOrders($filter_data);

		$total = 0;

		foreach ($orders as $order) {
			$total += $order['total'];
		}

		return array(
			'total' => $total,
			'orders' => count($orders)
		);
	}

	private function getYesterdaySales(): array {
		$this->load->model('sale/order');

		$yesterday = date('Y-m-d', strtotime('-1 day'));

		$filter_data = array(
			'filter_date_added' => $yesterday
		);

		$orders = $this->model_sale_order->getOrders($filter_data);

		$total = 0;

		foreach ($orders as $order) {
			$total += $order['total'];
		}

		return array(
			'total' => $total,
			'orders' => count($orders)
		);
	}
}
?> 