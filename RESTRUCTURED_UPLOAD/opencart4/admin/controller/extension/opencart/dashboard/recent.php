<?php
namespace Opencart\Admin\Controller\Extension\Opencart\Dashboard;
class Recent extends \Opencart\System\Engine\Controller {
	public function dashboard(): string {
		$this->load->language('extension/opencart/dashboard/recent');

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_view'] = $this->language->get('text_view');

		$data['token'] = $this->session->data['user_token'];

		$data['orders'] = array();

		$this->load->model('sale/order');

		$results = $this->model_sale_order->getOrders(array('sort' => 'o.date_added', 'order' => 'DESC', 'start' => 0, 'limit' => 5));

		foreach ($results as $result) {
			$data['orders'][] = array(
				'order_id'   => $result['order_id'],
				'customer'   => $result['customer'],
				'status'     => $result['status'],
				'total'      => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'view'       => $this->url->link('sale/order.info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'])
			);
		}

		$data['order'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token']);

		return $this->load->view('extension/opencart/dashboard/recent', $data);
	}
}
?> 