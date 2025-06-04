<?php
/**
 * trendyol_login.php
 *
 * Amaç: Trendyol modülü için özel login ekranı ve giriş işlemleri.
 *
 * Not: 2FA şimdilik pasif, ileride aktif edilebilir.
 */
class ControllerExtensionModuleTrendyolLogin extends Controller {
    public function index() {
        $this->load->language('extension/module/trendyol');
        $this->document->setTitle('Trendyol Modül Girişi');
        $data = [
            'action' => $this->url->link('extension/module/trendyol_login', '', true),
            'error_warning' => '',
            'success' => '',
        ];
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $username = $this->request->post['username'] ?? '';
            $password = $this->request->post['password'] ?? '';
            // 2FA kodu şimdilik pasif
            if ($this->user->login($username, $password)) {
                $this->writeLog($username, 'LOGIN', 'Başarılı giriş.');
                // OpenCart'ın kendi user_token'ını kullan veya üret
                if (!isset($this->session->data['user_token']) || !$this->session->data['user_token']) {
                    $this->session->data['user_token'] = token(32);
                }
                $user_token = $this->session->data['user_token'];
                // Doğrudan Trendyol paneline yönlendir
                $this->response->redirect($this->url->link('extension/module/trendyol/dashboard', 'user_token=' . $user_token, true));
                return;
            } else {
                $data['error_warning'] = 'Kullanıcı adı veya şifre hatalı!';
                $this->writeLog($username, 'LOGIN_FAIL', 'Hatalı giriş denemesi.');
            }
        }
        $this->response->setOutput($this->load->view('extension/module/trendyol_login', $data));
    }
    private function writeLog($user, $action, $message) {
        $log_file = DIR_LOGS . 'trendyol_controller.log';
        $date = date('Y-m-d H:i:s');
        $log = "[$date] [$user] [$action] $message\n";
        file_put_contents($log_file, $log, FILE_APPEND);
    }
} 