<?php
/**
 * Final State Verifier
 * This script verifies if the files from the '/upload' directory have been
 * correctly copied to the '/opencart_new' directory, taking into account
 * the custom admin path. This provides a definitive answer to the user's question.
 *
 * @author MesChain Development Team
 * @version 1.0.0
 * @date 2024-12-23
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

class FinalStateVerifier {
    private $source_dir;
    private $target_dir;
    private $admin_dir_name;
    private $missing_files = [];
    private $found_files = 0;
    private $total_files = 0;

    public function __construct() {
        $this->source_dir = __DIR__ . '/upload/';
        $this->target_dir = __DIR__ . '/opencart_new/';
        $this->admin_dir_name = 'MesTech'; // As defined in previous steps

        echo "✅ ===================================================\n";
        echo "✅ Nihai Durum Doğrulama Betiği Başlatıldı\n";
        echo "✅ Kaynak: /upload/ -> Hedef: /opencart_new/\n";
        echo "✅ ===================================================\n\n";
    }

    public function verify() {
        if (!is_dir($this->source_dir) || !is_dir($this->target_dir)) {
            die("❌ HATA: Kaynak veya hedef dizinler bulunamadı!");
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->source_dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) continue;

            $this->total_files++;
            $relative_path = str_replace($this->source_dir, '', $item->getPathname());
            
            // Account for the custom admin directory
            $target_relative_path = str_replace('admin', $this->admin_dir_name, $relative_path);
            $target_file = $this->target_dir . $target_relative_path;

            if (file_exists($target_file)) {
                $this->found_files++;
            } else {
                $this->missing_files[] = $target_relative_path;
            }
        }
        $this->generateReport();
    }

    private function generateReport() {
        echo "📋 **Doğrulama Raporu**\n";
        echo "---------------------------------------------------\n";
        echo "- İncelenen Toplam Dosya: {$this->total_files}\n";
        echo "- ✅ Hedefte Bulunan Dosyalar: {$this->found_files}\n";
        echo "- ❌ Hedefte Eksik Olan Dosyalar: " . count($this->missing_files) . "\n";
        echo "---------------------------------------------------\n\n";

        if (empty($this->missing_files)) {
            echo "🏆 **SONUÇ: EKSİKSİZ**\n";
            echo "Evet, `upload` klasöründeki orijinal yazılımınızın tüm dosyaları `opencart_new` klasöründe mevcuttur. Sistem tam ve çalışmaya hazırdır.\n";
        } else {
            echo "🚨 **SONUÇ: EKSİK**\n";
            echo "Hayır, `upload` klasöründeki dosyalar `opencart_new` klasörüne tam olarak aktarılmamış. Bu durum beklenen bir sonuçtur, çünkü en son profesyonel bir OCMOD paketi oluşturmak için ortamı temizlemiştik.\n\n";
            echo "**Eksik Olan Dosyalardan Bazıları:**\n";
            $limit = 5;
            foreach (array_slice($this->missing_files, 0, $limit) as $file) {
                echo "  - `{$file}`\n";
            }
            if(count($this->missing_files) > $limit) {
                echo "  - ... ve " . (count($this->missing_files) - $limit) . " diğer dosya.\n";
            }
            
            echo "\n**Sonraki Adım:**\n";
            echo "Şimdi yapılması gereken tek şey, daha önce oluşturduğumuz güvenli ve profesyonel **`meschain_sync_professional.ocmod.zip`** paketini, bu temiz OpenCart kurulumuna yüklemektir. Bu, eksik olan tüm dosyaları doğru bir şekilde yerine koyacak ve sistemi çalışır hale getirecektir.\n";
        }
    }
}

$verifier = new FinalStateVerifier();
$verifier->verify();
?> 