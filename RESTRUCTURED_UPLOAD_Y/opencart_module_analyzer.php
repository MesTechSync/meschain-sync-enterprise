<?php
/**
 * OpenCart 4.x Modül Mimarisi Derinlemesine Analiz ve Karşılaştırma
 * 
 * @author GitHub Copilot Professional
 * @version 3.0.0
 * @date 22 Haziran 2025
 * @compatibility OpenCart 4.0.2.3+
 */

class OpenCartModuleArchitectureAnalyzer {
    
    private $db;
    private $report = [];
    private $opencart_path;
    
    public function __construct() {
        $this->loadConfig();
        $this->connectDatabase();
        $this->opencart_path = DIR_OPENCART;
    }
    
    /**
     * Ana analiz işlemini başlat
     */
    public function runCompleteAnalysis() {
        echo "🔍 OpenCart Modül Mimarisi Analizi Başlatılıyor...\n";
        
        // 1. Mevcut modülleri analiz et
        $this->analyzeExistingModules();
        
        // 2. Dosya yapısını analiz et
        $this->analyzeFileStructure();
        
        // 3. Veritabanı yapısını analiz et
        $this->analyzeDatabaseStructure();
        
        // 4. Menu sistemini analiz et
        $this->analyzeMenuSystem();
        
        // 5. MesChain modüllerini analiz et
        $this->analyzeMesChainModules();
        
        // 6. Raporu oluştur
        $this->generateReport();
        
        echo "✅ Analiz tamamlandı. Rapor: opencart_module_architecture_report.md\n";
    }
    
    /**
     * Mevcut modülleri analiz et
     */
    private function analyzeExistingModules() {
        echo "📊 Mevcut modüller analiz ediliyor...\n";
        
        $query = "SELECT * FROM oc_extension WHERE type = 'module' ORDER BY extension, code";
        $result = $this->db->query($query);
        
        $modules = [];
        while ($row = $result->fetch_assoc()) {
            $modules[] = $row;
        }
        
        $this->report['existing_modules'] = $modules;
        
        // Modül sayıları
        $this->report['module_stats'] = [
            'total_modules' => count($modules),
            'active_modules' => count(array_filter($modules, function($m) { return $m['status'] == 1; })),
            'inactive_modules' => count(array_filter($modules, function($m) { return $m['status'] == 0; }))
        ];
        
        echo "   Toplam modül: " . $this->report['module_stats']['total_modules'] . "\n";
        echo "   Aktif modül: " . $this->report['module_stats']['active_modules'] . "\n";
    }
    
    /**
     * Dosya yapısını analiz et
     */
    private function analyzeFileStructure() {
        echo "📁 Dosya yapısı analiz ediliyor...\n";
        
        $this->report['file_structure'] = [];
        
        // Extension dizinlerini tara
        $extension_dir = $this->opencart_path . 'extension/';
        if (is_dir($extension_dir)) {
            $extensions = scandir($extension_dir);
            foreach ($extensions as $ext) {
                if ($ext != '.' && $ext != '..' && is_dir($extension_dir . $ext)) {
                    $this->report['file_structure'][$ext] = $this->scanExtensionDirectory($extension_dir . $ext);
                }
            }
        }
        
        // Upload dizinini de kontrol et
        $upload_dir = $this->opencart_path . 'upload/';
        if (is_dir($upload_dir)) {
            $this->report['upload_structure'] = $this->scanUploadDirectory($upload_dir);
        }
        
        echo "   " . count($this->report['file_structure']) . " extension dizini bulundu\n";
    }
    
    /**
     * Veritabanı yapısını analiz et
     */
    private function analyzeDatabaseStructure() {
        echo "🗄️ Veritabanı yapısı analiz ediliyor...\n";
        
        $this->report['database'] = [];
        
        // Extension tablosu analizi
        $this->report['database']['extension_table'] = $this->analyzeTable('oc_extension');
        
        // Extension install tablosu
        $this->report['database']['extension_install_table'] = $this->analyzeTable('oc_extension_install');
        
        // Extension path tablosu
        $this->report['database']['extension_path_table'] = $this->analyzeTable('oc_extension_path');
        
        // Setting tablosu (modül ayarları için)
        $query = "SELECT * FROM oc_setting WHERE `key` LIKE '%module_%' LIMIT 10";
        $result = $this->db->query($query);
        $settings = [];
        while ($row = $result->fetch_assoc()) {
            $settings[] = $row;
        }
        $this->report['database']['module_settings'] = $settings;
        
        // User group permissions
        $query = "SELECT * FROM oc_user_group WHERE name = 'Administrator'";
        $result = $this->db->query($query);
        $admin_group = $result->fetch_assoc();
        if ($admin_group) {
            $this->report['database']['admin_permissions'] = json_decode($admin_group['permission'], true);
        }
        
        echo "   Veritabanı tabloları analiz edildi\n";
    }
    
    /**
     * Menu sistemini analiz et
     */
    private function analyzeMenuSystem() {
        echo "📋 Menu sistemi analiz ediliyor...\n";
        
        // Event tablosunu kontrol et
        $query = "SELECT * FROM oc_event WHERE code LIKE '%menu%' OR action LIKE '%menu%'";
        $result = $this->db->query($query);
        $menu_events = [];
        while ($row = $result->fetch_assoc()) {
            $menu_events[] = $row;
        }
        $this->report['menu_events'] = $menu_events;
        
        echo "   " . count($menu_events) . " menu event'i bulundu\n";
    }
    
    /**
     * MesChain modüllerini analiz et
     */
    private function analyzeMesChainModules() {
        echo "🔧 MesChain modülleri analiz ediliyor...\n";
        
        // MesChain extension kayıtları
        $query = "SELECT * FROM oc_extension WHERE extension = 'meschain' OR code LIKE '%meschain%'";
        $result = $this->db->query($query);
        $meschain_extensions = [];
        while ($row = $result->fetch_assoc()) {
            $meschain_extensions[] = $row;
        }
        $this->report['meschain_modules'] = $meschain_extensions;
        
        // MesChain dosya yapısı
        $meschain_files = [];
        $upload_admin = $this->opencart_path . 'upload/admin/';
        if (is_dir($upload_admin)) {
            $meschain_files['admin'] = $this->findMesChainFiles($upload_admin);
        }
        
        $upload_system = $this->opencart_path . 'upload/system/';
        if (is_dir($upload_system)) {
            $meschain_files['system'] = $this->findMesChainFiles($upload_system);
        }
        
        $this->report['meschain_files'] = $meschain_files;
        
        // MesChain tabloları
        $query = "SHOW TABLES LIKE '%meschain%'";
        $result = $this->db->query($query);
        $meschain_tables = [];
        while ($row = $result->fetch_assoc()) {
            $meschain_tables[] = array_values($row)[0];
        }
        $this->report['meschain_tables'] = $meschain_tables;
        
        echo "   " . count($meschain_extensions) . " MesChain extension kaydı bulundu\n";
        echo "   " . count($meschain_tables) . " MesChain tablosu bulundu\n";
    }
    
    /**
     * Extension dizinini tara
     */
    private function scanExtensionDirectory($path) {
        $structure = [];
        $items = scandir($path);
        
        foreach ($items as $item) {
            if ($item != '.' && $item != '..') {
                $full_path = $path . '/' . $item;
                if (is_dir($full_path)) {
                    $structure['directories'][] = $item;
                    if ($item == 'admin') {
                        $structure['admin_structure'] = $this->scanDirectory($full_path);
                    }
                } else {
                    $structure['files'][] = $item;
                }
            }
        }
        
        return $structure;
    }
    
    /**
     * Upload dizinini tara
     */
    private function scanUploadDirectory($path) {
        $structure = [];
        $main_dirs = ['admin', 'catalog', 'system'];
        
        foreach ($main_dirs as $dir) {
            $dir_path = $path . $dir;
            if (is_dir($dir_path)) {
                $structure[$dir] = $this->scanDirectory($dir_path, 2); // 2 level deep
            }
        }
        
        return $structure;
    }
    
    /**
     * Dizini belirli derinliğe kadar tara
     */
    private function scanDirectory($path, $depth = 1) {
        if ($depth <= 0) return [];
        
        $structure = [];
        $items = scandir($path);
        
        foreach ($items as $item) {
            if ($item != '.' && $item != '..') {
                $full_path = $path . '/' . $item;
                if (is_dir($full_path)) {
                    $structure['directories'][$item] = $this->scanDirectory($full_path, $depth - 1);
                } else {
                    $structure['files'][] = $item;
                }
            }
        }
        
        return $structure;
    }
    
    /**
     * MesChain dosyalarını bul
     */
    private function findMesChainFiles($path) {
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        
        foreach ($iterator as $file) {
            if ($file->isFile() && strpos($file->getPathname(), 'meschain') !== false) {
                $files[] = str_replace($this->opencart_path, '', $file->getPathname());
            }
        }
        
        return $files;
    }
    
    /**
     * Tablo yapısını analiz et
     */
    private function analyzeTable($table_name) {
        $table_info = [];
        
        // Tablo var mı kontrol et
        $query = "SHOW TABLES LIKE '$table_name'";
        $result = $this->db->query($query);
        if ($result->num_rows == 0) {
            return ['exists' => false];
        }
        
        $table_info['exists'] = true;
        
        // Tablo yapısı
        $query = "DESCRIBE $table_name";
        $result = $this->db->query($query);
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row;
        }
        $table_info['columns'] = $columns;
        
        // Kayıt sayısı
        $query = "SELECT COUNT(*) as count FROM $table_name";
        $result = $this->db->query($query);
        $count = $result->fetch_assoc();
        $table_info['row_count'] = $count['count'];
        
        return $table_info;
    }
    
    /**
     * Raporu oluştur
     */
    private function generateReport() {
        $markdown = $this->generateMarkdownReport();
        file_put_contents('opencart_module_architecture_report.md', $markdown);
    }
    
    /**
     * Markdown raporu oluştur
     */
    private function generateMarkdownReport() {
        $date = date('d F Y, H:i:s');
        
        $md = "# OpenCart 4.x Modül Mimarisi Derinlemesine Analiz Raporu\n\n";
        $md .= "**Analiz Tarihi:** $date  \n";
        $md .= "**OpenCart Versiyonu:** 4.0.2.3  \n";
        $md .= "**Analiz Kapsamı:** Tam Sistem Mimarisi  \n\n";
        
        $md .= "---\n\n";
        
        // Özet
        $md .= "## 📊 Analiz Özeti\n\n";
        $md .= "| Metrik | Değer |\n";
        $md .= "|--------|-------|\n";
        $md .= "| **Toplam Modül** | " . $this->report['module_stats']['total_modules'] . " |\n";
        $md .= "| **Aktif Modül** | " . $this->report['module_stats']['active_modules'] . " |\n";
        $md .= "| **MesChain Modülleri** | " . count($this->report['meschain_modules']) . " |\n";
        $md .= "| **MesChain Tabloları** | " . count($this->report['meschain_tables']) . " |\n";
        $md .= "| **Extension Dizinleri** | " . count($this->report['file_structure']) . " |\n\n";
        
        // Mevcut modüller
        $md .= "## 🔧 Mevcut Modüller Listesi\n\n";
        $md .= "| Extension | Code | Type | Status |\n";
        $md .= "|-----------|------|---------|--------|\n";
        foreach ($this->report['existing_modules'] as $module) {
            $status = $module['status'] == 1 ? '✅ Aktif' : '❌ Pasif';
            $md .= "| {$module['extension']} | {$module['code']} | {$module['type']} | $status |\n";
        }
        $md .= "\n";
        
        // OpenCart standart modül mimarisi
        $md .= "## 🏗️ OpenCart Standart Modül Mimarisi\n\n";
        $md .= "### Dosya Yapısı Standardı\n\n";
        $md .= "OpenCart 4.x'te modüller şu yapıyı takip eder:\n\n";
        $md .= "```\n";
        $md .= "extension/{extension_name}/\n";
        $md .= "├── admin/\n";
        $md .= "│   ├── controller/\n";
        $md .= "│   │   └── module/{module_code}.php\n";
        $md .= "│   ├── model/\n";
        $md .= "│   │   └── module/{module_code}.php\n";
        $md .= "│   ├── language/\n";
        $md .= "│   │   └── en-gb/module/{module_code}.php\n";
        $md .= "│   └── view/template/\n";
        $md .= "│       └── module/{module_code}.twig\n";
        $md .= "└── catalog/\n";
        $md .= "    ├── controller/\n";
        $md .= "    │   └── module/{module_code}.php\n";
        $md .= "    ├── model/\n";
        $md .= "    │   └── module/{module_code}.php\n";
        $md .= "    └── view/template/\n";
        $md .= "        └── module/{module_code}.twig\n";
        $md .= "```\n\n";
        
        // Veritabanı yapısı
        $md .= "### Veritabanı Entegrasyonu\n\n";
        $md .= "Bir modülün OpenCart'ta tanınması için gerekli adımlar:\n\n";
        $md .= "1. **Extension Kaydı** (`oc_extension` tablosu):\n";
        $md .= "   - `extension`: Uzantı grubu adı (örn: 'opencart', 'meschain')\n";
        $md .= "   - `type`: Uzantı tipi ('module', 'theme', 'payment' vb.)\n";
        $md .= "   - `code`: Modülün benzersiz kodu\n";
        $md .= "   - `status`: Aktif/pasif durumu (1/0)\n\n";
        
        $md .= "2. **Ayarlar** (`oc_setting` tablosu):\n";
        $md .= "   - `module_{code}_status`: Modül durumu\n";
        $md .= "   - `module_{code}_name`: Modül adı\n";
        $md .= "   - Diğer modül-specific ayarlar\n\n";
        
        $md .= "3. **İzinler** (`oc_user_group` tablosu):\n";
        $md .= "   - `access` permissions: Modülü görüntüleme izni\n";
        $md .= "   - `modify` permissions: Modülü değiştirme izni\n\n";
        
        // MesChain analizi
        $md .= "## 🔧 MesChain Modül Analizi\n\n";
        $md .= "### Kayıtlı MesChain Modülleri\n\n";
        if (!empty($this->report['meschain_modules'])) {
            $md .= "| Extension | Code | Type | Status |\n";
            $md .= "|-----------|------|---------|--------|\n";
            foreach ($this->report['meschain_modules'] as $module) {
                $status = $module['status'] == 1 ? '✅ Aktif' : '❌ Pasif';
                $md .= "| {$module['extension']} | {$module['code']} | {$module['type']} | $status |\n";
            }
        } else {
            $md .= "❌ **Uyarı:** Hiç MesChain modülü kaydı bulunamadı!\n\n";
        }
        $md .= "\n";
        
        // MesChain dosyaları
        $md .= "### MesChain Dosya Yapısı\n\n";
        if (!empty($this->report['meschain_files']['admin'])) {
            $md .= "**Admin Dosyaları:**\n";
            foreach ($this->report['meschain_files']['admin'] as $file) {
                $md .= "- `$file`\n";
            }
            $md .= "\n";
        }
        
        if (!empty($this->report['meschain_files']['system'])) {
            $md .= "**System Dosyaları:**\n";
            foreach ($this->report['meschain_files']['system'] as $file) {
                $md .= "- `$file`\n";
            }
            $md .= "\n";
        }
        
        // MesChain tabloları
        $md .= "### MesChain Veritabanı Tabloları\n\n";
        if (!empty($this->report['meschain_tables'])) {
            foreach ($this->report['meschain_tables'] as $table) {
                $md .= "- `$table`\n";
            }
        } else {
            $md .= "❌ **Uyarı:** Hiç MesChain tablosu bulunamadı!\n";
        }
        $md .= "\n";
        
        // Sorun tespit ve çözümler
        $md .= "## 🚨 Tespit Edilen Sorunlar ve Çözümler\n\n";
        $md .= $this->generateProblemsAndSolutions();
        
        // OCMOD yapısı
        $md .= "## 📦 OCMOD Paket Yapısı Önerisi\n\n";
        $md .= $this->generateOCMODStructure();
        
        return $md;
    }
    
    /**
     * Sorunları ve çözümleri oluştur
     */
    private function generateProblemsAndSolutions() {
        $md = "";
        
        // MesChain modül kayıtları kontrolü
        if (empty($this->report['meschain_modules'])) {
            $md .= "### ❌ Problem 1: MesChain Modülleri Kayıtlı Değil\n\n";
            $md .= "**Sorun:** `oc_extension` tablosunda MesChain modülleri bulunamıyor.\n\n";
            $md .= "**Çözüm:**\n";
            $md .= "```sql\n";
            $md .= "INSERT INTO oc_extension (extension, type, code, status) VALUES\n";
            $md .= "('meschain', 'module', 'meschain_sync', 1),\n";
            $md .= "('meschain', 'module', 'meschain_trendyol', 1);\n";
            $md .= "```\n\n";
        }
        
        // Admin permissions kontrolü
        if (isset($this->report['database']['admin_permissions'])) {
            $has_meschain_access = false;
            if (isset($this->report['database']['admin_permissions']['access'])) {
                foreach ($this->report['database']['admin_permissions']['access'] as $perm) {
                    if (strpos($perm, 'meschain') !== false) {
                        $has_meschain_access = true;
                        break;
                    }
                }
            }
            
            if (!$has_meschain_access) {
                $md .= "### ❌ Problem 2: Admin İzinleri Eksik\n\n";
                $md .= "**Sorun:** Administrator grubu MesChain modüllerine erişim iznine sahip değil.\n\n";
                $md .= "**Çözüm:** Admin user group permissions'a MesChain yolları eklenmelidir.\n\n";
            }
        }
        
        return $md;
    }
    
    /**
     * OCMOD yapısı önerisi
     */
    private function generateOCMODStructure() {
        $md = "MesChain Trendyol modülü için önerilen OCMOD yapısı:\n\n";
        $md .= "```\n";
        $md .= "meschain-trendyol.ocmod.zip\n";
        $md .= "├── install.xml\n";
        $md .= "└── upload/\n";
        $md .= "    ├── admin/\n";
        $md .= "    │   ├── controller/extension/module/\n";
        $md .= "    │   │   └── meschain_trendyol.php\n";
        $md .= "    │   ├── model/extension/module/\n";
        $md .= "    │   │   └── meschain_trendyol.php\n";
        $md .= "    │   ├── view/template/extension/module/\n";
        $md .= "    │   │   └── meschain_trendyol.twig\n";
        $md .= "    │   └── language/\n";
        $md .= "    │       ├── en-gb/extension/module/\n";
        $md .= "    │       │   └── meschain_trendyol.php\n";
        $md .= "    │       └── tr-tr/extension/module/\n";
        $md .= "    │           └── meschain_trendyol.php\n";
        $md .= "    ├── catalog/\n";
        $md .= "    │   ├── controller/extension/module/\n";
        $md .= "    │   │   └── meschain_trendyol.php\n";
        $md .= "    │   └── model/extension/module/\n";
        $md .= "    │       └── meschain_trendyol.php\n";
        $md .= "    └── system/library/meschain/\n";
        $md .= "        ├── api/\n";
        $md .= "        │   └── TrendyolApiClient.php\n";
        $md .= "        └── bootstrap.php\n";
        $md .= "```\n\n";
        
        return $md;
    }
    
    /**
     * Config yükle
     */
    private function loadConfig() {
        if (file_exists('config.php')) {
            require_once 'config.php';
        } elseif (file_exists('opencart_new/config.php')) {
            require_once 'opencart_new/config.php';
        } else {
            throw new Exception("config.php dosyası bulunamadı!");
        }
    }
    
    /**
     * Veritabanı bağlantısı
     */
    private function connectDatabase() {
        $this->db = new mysqli(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
        
        if ($this->db->connect_error) {
            throw new Exception("Database connection failed: " . $this->db->connect_error);
        }
        
        $this->db->set_charset('utf8mb4');
    }
}

// Ana script çalıştırma
try {
    $analyzer = new OpenCartModuleArchitectureAnalyzer();
    $analyzer->runCompleteAnalysis();
    
    echo "\n🎉 Analiz tamamlandı!\n";
    echo "📄 Rapor dosyası: opencart_module_architecture_report.md\n";
    
} catch (Exception $e) {
    echo "❌ Hata: " . $e->getMessage() . "\n";
}
?>
