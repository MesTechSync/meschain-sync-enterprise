<?php
/**
 * n11.php
 *
 * Amaç: n11 modülünün OpenCart yönetici paneli (admin) tarafındaki controller dosyasıdır.
 *
 * Loglama: Tüm önemli işlemler ve hatalar n11_controller.log dosyasına kaydedilmelidir.
 * Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]
 *
 * Hata yönetimi: Hatalar loglanmalı ve kullanıcıya açıklayıcı mesaj gösterilmelidir.
 */
// n11 modülünün OpenCart admin tarafındaki controller dosyası

class ControllerExtensionModuleN11 extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/module/n11');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_n11', $this->request->post);
            $this->writeLog('admin', 'AYAR_GUNCELLEME', 'n11 ayarları güncellendi.');
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/module/n11', 'user_token=' . $this->session->data['user_token'], true));
        }

        $data['heading_title'] = $this->language->get('heading_title');
        $data['action'] = $this->url->link('extension/module/n11', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);
        $data['module_n11_api_key'] = isset($this->request->post['module_n11_api_key']) ? $this->request->post['module_n11_api_key'] : $this->config->get('module_n11_api_key');
        $data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
        $data['success'] = isset($this->session->data['success']) ? $this->session->data['success'] : '';
        unset($this->session->data['success']);
        $this->response->setOutput($this->load->view('extension/module/n11', $data));
    }

    public function install() {
        $this->writeLog('admin', 'KURULUM', 'n11 modülü kuruldu.');
    }

    public function uninstall() {
        $this->writeLog('admin', 'KALDIRMA', 'n11 modülü kaldırıldı.');
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/n11')) {
            $this->error['warning'] = $this->language->get('error_permission');
            $this->writeLog('admin', 'HATA', 'İzin yok.');
            return false;
        }
        return true;
    }

    private function writeLog($user, $action, $message) {
        $log_file = DIR_LOGS . 'n11_controller.log';
        $date = date('Y-m-d H:i:s');
        $log = "[$date] [$user] [$action] $message\n";
        file_put_contents($log_file, $log, FILE_APPEND);
    }
}

// ... OpenCart controller fonksiyonları buraya eklenecek ... 