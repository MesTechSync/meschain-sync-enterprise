<?php
namespace Opencart\Admin\Controller\Extension\Opencart\Dashboard;
class Activity extends \Opencart\System\Engine\Controller {
	public function dashboard(): string {
		$this->load->language('extension/opencart/dashboard/activity');

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_view'] = $this->language->get('text_view');

		$data['token'] = $this->session->data['user_token'];

		$data['activities'] = array();

		$data['activity'] = $this->url->link('user/user', 'user_token=' . $this->session->data['user_token']);

		$results = $this->getActivities();

		foreach ($results as $result) {
			$data['activities'][] = array(
				'comment'    => $result['comment'],
				'date_added' => date($this->language->get('datetime_format'), strtotime($result['date_added']))
			);
		}

		return $this->load->view('extension/opencart/dashboard/activity', $data);
	}

	private function getActivities(): array {
		$activities = array();

		// Get recent orders
		$this->load->model('sale/order');

		$orders = $this->model_sale_order->getOrders(array('sort' => 'o.date_added', 'order' => 'DESC', 'start' => 0, 'limit' => 5));

		foreach ($orders as $order) {
			$activities[] = array(
				'comment'    => sprintf('New order #%s by %s', $order['order_id'], $order['customer']),
				'date_added' => $order['date_added']
			);
		}

		// Get recent customers
		$this->load->model('customer/customer');

		$customers = $this->model_customer_customer->getCustomers(array('sort' => 'c.date_added', 'order' => 'DESC', 'start' => 0, 'limit' => 5));

		foreach ($customers as $customer) {
			$activities[] = array(
				'comment'    => sprintf('New customer registration: %s %s', $customer['firstname'], $customer['lastname']),
				'date_added' => $customer['date_added']
			);
		}

		// Sort by date
		usort($activities, function($a, $b) {
			return strtotime($b['date_added']) - strtotime($a['date_added']);
		});

		return array_slice($activities, 0, 10);
	}
}
?> 