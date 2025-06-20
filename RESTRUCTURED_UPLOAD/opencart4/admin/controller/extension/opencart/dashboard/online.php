<?php
namespace Opencart\Admin\Controller\Extension\Opencart\Dashboard;
class Online extends \Opencart\System\Engine\Controller {
	public function dashboard(): string {
		$this->load->language('extension/opencart/dashboard/online');

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_view'] = $this->language->get('text_view');

		$data['token'] = $this->session->data['user_token'];

		$data['total'] = $this->getTotalOnline();

		$data['online'] = $this->url->link('report/online', 'user_token=' . $this->session->data['user_token']);

		return $this->load->view('extension/opencart/dashboard/online', $data);
	}

	private function getTotalOnline(): int {
		$this->load->model('tool/online');

		return $this->model_tool_online->getTotalOnline();
	}
}
?> 