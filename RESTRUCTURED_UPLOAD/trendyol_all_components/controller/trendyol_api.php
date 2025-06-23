<?php
/**
 * trendyol_api.php
 *
 * Amaç: Trendyol modülünün OpenCart mağaza (catalog) tarafındaki controller dosyasıdır. API ile sipariş, ürün, stok işlemleri burada yönetilir.
 *
 * Loglama: Tüm önemli işlemler ve hatalar trendyol_api.log dosyasına kaydedilmelidir.
 * Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]
 *
 * Hata yönetimi: Hatalar loglanmalı ve kullanıcıya açıklayıcı mesaj gösterilmelidir.
 */
// Trendyol modülünün OpenCart mağaza tarafındaki controller dosyası

// ... OpenCart catalog controller fonksiyonları buraya eklenecek ... 

class ControllerExtensionModuleTrendyolApi extends Controller {
    public function getStatus() {
        $this->load->language('extension/module/trendyol');
        $json = array();
        try {
            $json['status'] = 'OK';
            $json['message'] = 'Trendyol API çalışıyor.';
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
        $log_file = DIR_LOGS . 'trendyol_api.log';
        $date = date('Y-m-d H:i:s');
        $log = "[$date] [$user] [$action] $message\n";
        file_put_contents($log_file, $log, FILE_APPEND);
    }
} 
