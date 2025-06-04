<?php
/**
 * n11_api.php
 *
 * Amaç: n11 modülünün OpenCart mağaza (catalog) tarafındaki controller dosyasıdır. API ile sipariş, ürün, stok işlemleri burada yönetilir.
 *
 * Loglama: Tüm önemli işlemler ve hatalar n11_api.log dosyasına kaydedilmelidir.
 * Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]
 *
 * Hata yönetimi: Hatalar loglanmalı ve kullanıcıya açıklayıcı mesaj gösterilmelidir.
 */
// n11 modülünün OpenCart mağaza tarafındaki controller dosyası

// ... OpenCart catalog controller fonksiyonları buraya eklenecek ... 

class ControllerExtensionModuleN11Api extends Controller {
    public function getStatus() {
        $this->load->language('extension/module/n11');
        $json = array();
        try {
            $json['status'] = 'OK';
            $json['message'] = 'n11 API çalışıyor.';
            $this->writeLog('api', 'DURUM', 'API status OK');
        } catch (Exception $e) {
            $json['status'] = 'ERROR';
            $json['message'] = $e->getMessage();
            $this->writeLog('api', 'HATA', $e->getMessage());
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function writeLog($user, $action, $message) {
        $log_file = DIR_LOGS . 'n11_api.log';
        $date = date('Y-m-d H:i:s');
        $log = "[$date] [$user] [$action] $message\n";
        file_put_contents($log_file, $log, FILE_APPEND);
    }
} 