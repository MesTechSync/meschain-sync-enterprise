<?php
/**
 * MesChain Sync - Upload Package Completeness Verifier
 * This script audits the `upload` directory to ensure all essential files
 * for the extension are present and correctly located.
 *
 * @author MesChain Development Team
 * @version 1.0.0
 * @date 2024-12-22
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

class UploadVerifier {
    private $upload_dir;
    private $found_files = 0;
    private $missing_files = 0;
    private $report = [];

    // This list defines a "complete" extension package.
    private $required_files = [
        'Admin Panel' => [
            'admin/controller/extension/meschain/module/meschain_trendyol.php',
            'admin/model/extension/meschain/module/meschain_trendyol.php',
            'admin/view/template/extension/meschain/module/meschain_trendyol.twig',
        ],
        'Admin Language' => [
            'admin/language/en-gb/extension/meschain/module/meschain_trendyol.php',
            'admin/language/tr-tr/extension/meschain/module/meschain_trendyol.php',
            'admin/language/en-gb/extension/meschain/module/meschain_trendyol_cron.php',
            'admin/language/tr-tr/extension/meschain/module/meschain_trendyol_cron.php',
        ],
        'Cron Jobs' => [
            'admin/controller/extension/meschain/cron/trendyol.php',
            'system/library/meschain/cron/trendyol_sync.php',
            'system/library/meschain/cron/product_sync.php',
            'system/library/meschain/cron/order_sync.php',
            'system/library/meschain/cron/stock_sync.php',
        ],
        'Event & Webhook Listeners (Catalog)' => [
            'catalog/controller/extension/meschain/cron/trendyol.php',
            'catalog/controller/extension/meschain/event/product.php',
            'catalog/controller/extension/meschain/event/order.php',
            'catalog/controller/extension/meschain/event/stock.php',
        ],
        'Core System Libraries' => [
            'system/library/meschain/api/trendyol_client.php',
            'system/library/meschain/sync/product.php',
            'system/library/meschain/sync/order.php',
            'system/library/meschain/sync/stock.php',
        ],
        'Documentation' => [
            'docs/IMPLEMENTATION_SUMMARY.md',
            'docs/TRENDYOL_INTEGRATION_COMPREHENSIVE_TEST_REPORT.md',
        ]
    ];

    public function __construct() {
        $this->upload_dir = __DIR__ . '/upload/';
        echo "✅ ===================================================\n";
        echo "✅ `upload` Klasörü Bütünlük Doğrulaması\n";
        echo "✅ ===================================================\n\n";
    }

    public function verify() {
        if (!is_dir($this->upload_dir)) {
            die("❌ FATAL: `upload` klasörü bulunamadı!\n");
        }

        foreach ($this->required_files as $category => $files) {
            $this->report[$category] = [];
            echo "🔎 Kategori kontrol ediliyor: {$category}\n";
            foreach ($files as $file) {
                $path = $this->upload_dir . $file;
                $exists = file_exists($path);
                $this->report[$category][] = [
                    'file' => $file,
                    'exists' => $exists
                ];
                if ($exists) {
                    $this->found_files++;
                    echo "   - ✅ Bulundu: {$file}\n";
                } else {
                    $this->missing_files++;
                    echo "   - ❌ EKSİK: {$file}\n";
                }
            }
        }
        $this->generateSummary();
    }

    private function generateSummary() {
        $total_files = $this->found_files + $this->missing_files;
        echo "\n\n📋 **Doğrulama Özeti**\n";
        echo "---------------------------------------------------\n";
        echo "Kontrol Edilen Toplam Dosya Sayısı: {$total_files}\n";
        echo "✅ Bulunan Dosyalar: {$this->found_files}\n";
        echo "❌ Eksik Dosyalar: {$this->missing_files}\n";
        echo "---------------------------------------------------\n\n";

        if ($this->missing_files === 0) {
            echo "🏆 **SONUÇ: EKSİKSİZ** 🏆\n";
            echo "Yazılım paketiniz tam ve eksiksizdir. Gerekli tüm dosyalar `upload` klasöründe doğru yerlerde bulunmaktadır. Bu paket, herhangi bir uyumlu OpenCart sistemine kurulmaya hazırdır.\n";
        } else {
            echo "🚨 **SONUÇ: EKSİK** 🚨\n";
            echo "Yazılım paketinizde kritik dosyalar eksiktir. Bu eksik dosyalar olmadan eklenti düzgün çalışmayacak veya kurulumda hatalara neden olacaktır. Lütfen yukarıdaki listede 'EKSİK' olarak işaretlenen dosyaları tamamlayın.\n";
        }
    }
}

$verifier = new UploadVerifier();
$verifier->verify();
?> 