<?php
namespace Opencart\Admin\Controller\Extension\Opencart\Dashboard;
class Order extends \Opencart\System\Engine\Controller {
	public function dashboard(): string {
		$this->load->language('extension/opencart/dashboard/order');

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_view'] = $this->language->get('text_view');

		$data['token'] = $this->session->data['user_token'];

		$data['total'] = $this->getTotalOrders();

		$data['order'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token']);

		return $this->load->view('extension/opencart/dashboard/order', $data);
	}

	private function getTotalOrders(): int {
		$this->load->model('sale/order');

		$today = date('Y-m-d');

		$filter_data = array(
			'filter_date_added' => $today
		);

		$orders = $this->model_sale_order->getOrders($filter_data);

		return count($orders);
	}
}
?> 