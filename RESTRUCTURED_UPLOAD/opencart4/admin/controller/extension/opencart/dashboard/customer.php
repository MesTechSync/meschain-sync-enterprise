<?php
namespace Opencart\Admin\Controller\Extension\Opencart\Dashboard;
class Customer extends \Opencart\System\Engine\Controller {
	public function dashboard(): string {
		$this->load->language('extension/opencart/dashboard/customer');

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_view'] = $this->language->get('text_view');

		$data['token'] = $this->session->data['user_token'];

		$data['total'] = $this->getTotalCustomers();

		$data['customer'] = $this->url->link('customer/customer', 'user_token=' . $this->session->data['user_token']);

		return $this->load->view('extension/opencart/dashboard/customer', $data);
	}

	private function getTotalCustomers(): int {
		$this->load->model('customer/customer');

		$today = date('Y-m-d');

		$filter_data = array(
			'filter_date_added' => $today
		);

		return $this->model_customer_customer->getTotalCustomers($filter_data);
	}
}
?> 