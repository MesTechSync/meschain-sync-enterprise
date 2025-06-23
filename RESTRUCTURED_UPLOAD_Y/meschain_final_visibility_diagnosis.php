<?php
/**
 * MesChain Sync - Final Visibility & Menu Diagnosis
 * This script checks the final and most common reason for an extension not appearing:
 * the admin menu link integration in older OpenCart versions.
 *
 * @author MesChain Development Team
 * @version 1.0.0
 * @date 2024-12-22
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- Configuration ---
define('OPENCART_ROOT', __DIR__ . '/opencart_new/');
$extension_route = 'extension/meschain/module/meschain_trendyol';
$column_left_path = OPENCART_ROOT . 'admin/controller/common/column_left.php';
// --- End Configuration ---

class MenuDiagnoser {
    private $db;
    private $report = [];

    public function __construct() {
        echo "🔍 ============================================\n";
        echo "🔍 MesChain Sync - Nihai Görünürlük Testi\n";
        echo "🔍 ============================================\n\n";
        $this->connectDatabase();
    }
    
    private function connectDatabase() {
        // Suppress warnings for re-definitions
        @require_once(OPENCART_ROOT . 'config.php');
        @require_once(OPENCART_ROOT . 'admin/config.php');
        
        try {
            $this->db = new PDO(
                "mysql:host=" . DB_HOSTNAME . ";dbname=" . DB_DATABASE . ";port=" . DB_PORT . ";charset=utf8mb4",
                DB_USERNAME,
                DB_PASSWORD,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            $this->db = null; // Ensure db is null on failure
        }
    }

    public function runDiagnosis() {
        if (!$this->db) {
            $this->addResult('Veritabanı Bağlantısı', false, 'Başarısız. config.php dosyaları kontrol edilmeli.');
            $this->generateReport();
            return;
        }
        $this->addResult('Veritabanı Bağlantısı', true, 'Başarılı.');
        
        $this->checkExtensionRecord();
        $this->checkMenuFile();
        $this->generateReport();
    }

    private function checkExtensionRecord() {
        global $extension_route;
        $code = 'meschain_trendyol';
        $stmt = $this->db->prepare("SELECT * FROM " . DB_PREFIX . "extension WHERE `type` = 'module' AND `code` = :code");
        $stmt->execute(['code' => $code]);
        $is_registered = $stmt->fetch() !== false;
        $this->addResult('Veritabanı Kaydı (`oc_extension`)', $is_registered, $is_registered ? 'Eklenti doğru şekilde kayıtlı.' : 'Eklenti veritabanında kayıtlı değil!');
    }
    
    private function checkMenuFile() {
        global $column_left_path, $extension_route;
        
        if (!file_exists($column_left_path)) {
            $this->addResult('Menü Dosyası (`column_left.php`)', false, 'Dosya bulunamadı! OpenCart kurulumu bozuk olabilir.');
            return;
        }
        
        $content = file_get_contents($column_left_path);
        
        if (strpos($content, $extension_route) !== false) {
            $this->addResult('Menü Dosyası (`column_left.php`)', true, 'Menü linki dosyada MEVCUT.');
        } else {
            $this->addResult('Menü Dosyası (`column_left.php`)', false, 'Menü linki dosyada EKSİK. Sorunun ana kaynağı bu.');
        }
    }

    private function addResult($test, $success, $message) {
        $this->report[] = ['test' => $test, 'success' => $success, 'message' => $message];
    }
    
    public function generateReport() {
        echo "📋 **Teşhis Raporu**\n\n";
        foreach ($this->report as $result) {
            $icon = $result['success'] ? '✅' : '❌';
            echo sprintf("%s **%s:** %s\n", $icon, $result['test'], $result['message']);
        }
        
        echo "\n\n**Sonuç:**\n";
        
        $db_ok = $this->isTestSuccessful('Veritabanı Kaydı (`oc_extension`)');
        $menu_ok = $this->isTestSuccessful('Menü Dosyası (`column_left.php`)');
        
        if ($db_ok && !$menu_ok) {
            echo "Sorun netleşti: Eklentiniz veritabanına doğru şekilde kaydedilmiş ancak eski tip OpenCart kurulumunuzda menü linki `column_left.php` dosyasına **eklenmemiş**. Bu yüzden panelde göremiyorsunuz.\n\n";
            echo "**Çözüm:** Bir sonraki adımda bu dosyayı otomatik olarak düzenleyerek menü linkini ekleyecek bir düzeltme betiği çalıştıracağım.\n";
        } elseif (!$db_ok) {
            echo "Hala temel bir veritabanı kayıt sorunu var. Önceki düzeltme betiği bunu çözememiş. Bu sorunu öncelikli olarak ele almalıyız.\n";
        } elseif ($db_ok && $menu_ok) {
            echo "İlginç bir durum. Hem veritabanı kaydı hem de menü dosyası doğru görünüyor. Sorun, yönetici yetkilerinde veya OpenCart önbelleğinde olabilir. Yine de menü dosyasını yeniden oluşturmayı deneyebiliriz.\n";
        } else {
            echo "Birden fazla kritik hata tespit edildi. Adım adım ilerleyerek düzelteceğiz.\n";
        }
    }

    private function isTestSuccessful($testName) {
        foreach ($this->report as $result) {
            if ($result['test'] === $testName) {
                return $result['success'];
            }
        }
        return false;
    }
}

$diag = new MenuDiagnoser();
$diag->runDiagnosis();
?> 