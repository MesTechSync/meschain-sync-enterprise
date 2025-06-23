<?php
/**
 * MesChain Sync - Final Installation Fixer (Clean & Rebuild)
 * This script completely cleans up any previous failed installation attempts and
 * correctly installs the extension by registering it, setting permissions, and
 * forcefully adding the admin menu link.
 *
 * @author MesChain Development Team
 * @version 2.0.0
 * @date 2024-12-22
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- Configuration ---
define('OPENCART_ROOT', __DIR__ . '/opencart_new/');
$extension_code = 'meschain_trendyol';
$extension_type = 'module';
$extension_path_in_db = 'meschain'; // The 'extension' column value
$extension_route = 'extension/meschain/module/meschain_trendyol';
$extension_name = 'MesChain Trendyol';
$column_left_path = OPENCART_ROOT . 'admin/controller/common/column_left.php';
// --- End Configuration ---

class FinalFixer {
    private $db;

    public function __construct() {
        echo "🔧 ===================================================\n";
        echo "🔧 MesChain Sync - Nihai Kurulum Düzeltici (Temizle & Kur)\n";
        echo "🔧 ===================================================\n\n";

        if (!$this->connectDatabase()) {
            die("❌ FATAL: Veritabanı bağlantısı kurulamadı. İşlem durduruldu.\n");
        }
    }

    private function connectDatabase() {
        @require_once(OPENCART_ROOT . 'config.php');
        @require_once(OPENCART_ROOT . 'admin/config.php');
        try {
            $this->db = new PDO(
                "mysql:host=" . DB_HOSTNAME . ";dbname=" . DB_DATABASE . ";port=" . DB_PORT . ";charset=utf8mb4",
                DB_USERNAME,
                DB_PASSWORD,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function run() {
        $this->cleanup();
        $this->rebuild();
        echo "\n🎉 İŞLEM TAMAMLANDI! Eklenti başarıyla temizlendi ve yeniden kuruldu.\n";
        echo "Lütfen tarayıcı önbelleğinizi temizleyip yönetici panelini yenileyin. 'MesChain Trendyol' menüde görünmelidir.\n";
    }

    private function cleanup() {
        global $extension_code, $extension_route;
        echo "🧹 Adım 1: Eski veya bozuk kayıtlar temizleniyor...\n";

        // Remove from oc_extension
        $stmt = $this->db->prepare("DELETE FROM " . DB_PREFIX . "extension WHERE `code` = :code");
        $stmt->execute(['code' => $extension_code]);
        echo "   - `oc_extension` temizlendi. ({$stmt->rowCount()} satır silindi)\n";

        // Remove from oc_setting
        $stmt = $this->db->prepare("DELETE FROM " . DB_PREFIX . "setting WHERE `code` = :code");
        $stmt->execute(['code' => 'module_' . $extension_code]);
        echo "   - `oc_setting` temizlendi. ({$stmt->rowCount()} satır silindi)\n";

        // Remove from user group permissions
        $stmt = $this->db->prepare("SELECT user_group_id, permission FROM " . DB_PREFIX . "user_group");
        $stmt->execute();
        $groups = $stmt->fetchAll();
        $cleaned_groups = 0;
        foreach ($groups as $group) {
            $permissions = json_decode($group['permission'], true);
            $changed = false;
            if (isset($permissions['access']) && ($key = array_search($extension_route, $permissions['access'])) !== false) {
                unset($permissions['access'][$key]);
                $changed = true;
            }
            if (isset($permissions['modify']) && ($key = array_search($extension_route, $permissions['modify'])) !== false) {
                unset($permissions['modify'][$key]);
                $changed = true;
            }
            if ($changed) {
                $update_stmt = $this->db->prepare("UPDATE " . DB_PREFIX . "user_group SET permission = :perm WHERE user_group_id = :id");
                $update_stmt->execute(['perm' => json_encode($permissions), 'id' => $group['user_group_id']]);
                $cleaned_groups++;
            }
        }
        echo "   - `oc_user_group` yetkileri temizlendi. ({$cleaned_groups} grup güncellendi)\n";

        // Remove from menu file
        global $column_left_path;
        if (file_exists($column_left_path)) {
            $content = file_get_contents($column_left_path);
            $new_content = preg_replace("/\/\/ --- MESCHAIN START ---(.|\n)*?\/\/ --- MESCHAIN END ---/", "", $content);
            if ($content !== $new_content) {
                file_put_contents($column_left_path, $new_content);
                echo "   - `column_left.php` dosyasındaki eski menü linki temizlendi.\n";
            }
        }
        echo "   ✅ Temizlik tamamlandı.\n\n";
    }

    private function rebuild() {
        echo "🏗️ Adım 2: Eklenti sıfırdan, doğru şekilde kuruluyor...\n";
        $this->installExtension();
        $this->activateModule();
        $this->grantPermissions();
        $this->addMenuLink();
    }

    private function installExtension() {
        global $extension_type, $extension_code, $extension_path_in_db;
        try {
            $stmt = $this->db->prepare("INSERT INTO " . DB_PREFIX . "extension (`type`, `code`, `extension`) VALUES (:type, :code, :extension)");
            $stmt->execute(['type' => $extension_type, 'code' => $extension_code, 'extension' => $extension_path_in_db]);
            echo "   - `oc_extension` kaydı oluşturuldu: ✅\n";
        } catch (PDOException $e) {
            echo "   - `oc_extension` kaydı oluşturulamadı: ❌ HATA: {$e->getMessage()}\n";
        }
    }

    private function activateModule() {
        global $extension_code;
        try {
            $stmt = $this->db->prepare("INSERT INTO " . DB_PREFIX . "setting (`store_id`, `code`, `key`, `value`, `serialized`) VALUES (0, :code, :key, '1', 0)");
            $stmt->execute(['code' => 'module_' . $extension_code, 'key' => 'module_' . $extension_code . '_status']);
            echo "   - `oc_setting` ile modül etkinleştirildi: ✅\n";
        } catch (PDOException $e) {
            echo "   - Modül etkinleştirilemedi: ❌ HATA: {$e->getMessage()}\n";
        }
    }
    
    private function grantPermissions() {
        global $extension_route;
        try {
            $stmt = $this->db->prepare("SELECT * FROM " . DB_PREFIX . "user_group WHERE user_group_id = 1");
            $stmt->execute();
            $admin_group = $stmt->fetch();
            if ($admin_group) {
                $permissions = json_decode($admin_group['permission'], true);
                $permissions['access'][] = $extension_route;
                $permissions['modify'][] = $extension_route;
                $update_stmt = $this->db->prepare("UPDATE " . DB_PREFIX . "user_group SET permission = :perm WHERE user_group_id = 1");
                $update_stmt->execute(['perm' => json_encode($permissions)]);
                echo "   - Yönetici grubuna tam yetki verildi: ✅\n";
            }
        } catch (PDOException $e) {
            echo "   - Yetkiler verilemedi: ❌ HATA: {$e->getMessage()}\n";
        }
    }

    private function addMenuLink() {
        global $column_left_path, $extension_name, $extension_route;
        echo "   - Menü linki `column_left.php` dosyasına ekleniyor...\n";
        
        if (!file_exists($column_left_path) || !is_writable($column_left_path)) {
            echo "     - ❌ HATA: `{$column_left_path}` dosyası bulunamadı veya yazılabilir değil.\n";
            return;
        }

        $content = file_get_contents($column_left_path);

        // Find the 'Extensions' menu item to insert after it.
        $anchor = "'marketplace/extension'";
        $pos = strpos($content, $anchor);
        if ($pos === false) {
             echo "     - ⚠️ UYARI: Menüde 'Eklentiler' linki bulunamadı. Link en sona eklenecek.\n";
             $anchor = "class ControllerCommonColumnLeft extends Controller {";
             $pos = strpos($content, $anchor);
        }

        $insert_point = strpos($content, ']', $pos) + 1; // Find the closing bracket of the marketplace/extension array
        $insert_point = strpos($content, ');', $insert_point) + 2; // Find the closing of the statement.

        $menu_code = "\n\n\t\t// --- MESCHAIN START ---\n";
        $menu_code .= "\t\t\$meschain = array();\n";
        $menu_code .= "\t\tif (\$this->user->hasPermission('access', '{$extension_route}')) {\n";
        $menu_code .= "\t\t\t\$meschain[] = array(\n";
        $menu_code .= "\t\t\t\t'name'\t   => '{$extension_name}',\n";
        $menu_code .= "\t\t\t\t'href'     => \$this->url->link('{$extension_route}', 'user_token=' . \$this->session->data['user_token'], true),\n";
        $menu_code .= "\t\t\t\t'children' => array()\n";
        $menu_code .= "\t\t\t);\n";
        $menu_code .= "\t\t}\n\n";
        $menu_code .= "\t\tif (\$meschain) {\n";
        $menu_code .= "\t\t\t\$data['menus'][] = array(\n";
        $menu_code .= "\t\t\t\t'id'       => 'menu-meschain',\n";
        $menu_code .= "\t\t\t\t'icon'\t   => 'fa-cogs',\n";
        $menu_code .= "\t\t\t\t'name'\t   => 'MesChain Sync',\n";
        $menu_code .= "\t\t\t\t'href'     => '',\n";
        $menu_code .= "\t\t\t\t'children' => \$meschain\n";
        $menu_code .= "\t\t\t);\n";
        $menu_code .= "\t\t}\n\t\t// --- MESCHAIN END ---\n";


        $new_content = substr_replace($content, $menu_code, $insert_point, 0);

        if(file_put_contents($column_left_path, $new_content)) {
            echo "   - Menü linki başarıyla eklendi: ✅\n";
        } else {
            echo "   - Menü linki eklenemedi: ❌\n";
        }
    }
}

$fixer = new FinalFixer();
$fixer->run();
?> 