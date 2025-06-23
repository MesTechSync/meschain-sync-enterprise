# 🤖 AI İçin OpenCart OCMod Paketi Oluşturma Kılavuzu

## 📋 GENEL KURAL: OCMod Nedir?

**OCMod** = OpenCart Modification sistemi. Dosya uzantısı `.ocmod.zip` olmalıdır.

### ❌ YANLIŞ Yaklaşımlar:
- `.zip` uzantısı kullanmak
- Yanlış dosya yapısı
- install.xml eksikliği
- Gereksiz dosyalar eklemek

### ✅ DOĞRU Yaklaşım:
- `.ocmod.zip` uzantısı
- Standart OCMod yapısı
- install.xml zorunlu
- Sadece gerekli dosyalar

---

## 🏗️ STANDART OCMOD YAPISI

```
ModulAdi.ocmod.zip
├── install.xml (ZORUNLU - OCMod tanımı)
└── upload/ (ZORUNLU - OpenCart dosyaları)
    ├── admin/
    │   ├── controller/extension/module/
    │   ├── model/extension/module/
    │   ├── view/template/extension/module/
    │   └── language/
    │       ├── tr-tr/extension/module/
    │       └── en-gb/extension/module/
    ├── catalog/ (Opsiyonel - Frontend dosyaları)
    └── system/ (Opsiyonel - Kütüphaneler)
```

---

## 📄 install.xml ŞABLONU

```xml
<?xml version="1.0" encoding="utf-8"?>
<modification>
    <name>Modül Adı</name>
    <code>modul_kodu</code>
    <version>1.0.0</version>
    <author>Yazar Adı</author>
    <link>https://website.com</link>
    <description>Modül açıklaması</description>
    
    <!-- Admin menüye link ekleme -->
    <file path="admin/view/template/common/column_left.twig">
        <operation>
            <search><![CDATA[{% if marketplace %}]]></search>
            <add position="before"><![CDATA[
            <li><a href="{{ modul_linki }}"><i class="fa fa-icon"></i> {{ modul_adi }}</a></li>
            ]]></add>
        </operation>
    </file>
    
    <!-- Controller'a link ekleme -->
    <file path="admin/controller/common/column_left.php">
        <operation>
            <search><![CDATA[$data['marketplace'] = $this->url->link('marketplace/marketplace', 'user_token=' . $this->session->data['user_token'], true);]]></search>
            <add position="after"><![CDATA[
        
        // Modül Adı
        $data['modul_linki'] = $this->url->link('extension/module/modul_kodu', 'user_token=' . $this->session->data['user_token'], true);
        $this->load->language('extension/module/modul_kodu');
        $data['modul_adi'] = $this->language->get('heading_title');
            ]]></add>
        </operation>
    </file>
</modification>
```

---

## 🎯 CONTROLLER ŞABLONU

```php
<?php
class ControllerExtensionModuleModulKodu extends Controller {
    
    private $error = array();
    
    public function index() {
        $this->load->language('extension/module/modul_kodu');
        $this->document->setTitle($this->language->get('heading_title'));
        
        // Model yükle
        $this->load->model('extension/module/modul_kodu');
        
        // POST işlemi
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_modul_kodu', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }
        
        // Breadcrumbs
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Template
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/module/modul_kodu', $data));
    }
    
    public function install() {
        $this->load->model('extension/module/modul_kodu');
        $this->model_extension_module_modul_kodu->install();
    }
    
    public function uninstall() {
        $this->load->model('extension/module/modul_kodu');
        $this->model_extension_module_modul_kodu->uninstall();
    }
    
    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/modul_kodu')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }
}
```

---

## 🗄️ MODEL ŞABLONU

```php
<?php
class ModelExtensionModuleModulKodu extends Model {
    
    public function install() {
        // Veritabanı tabloları oluştur
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "modul_tablosu` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL,
                `status` tinyint(1) NOT NULL DEFAULT '1',
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ");
    }
    
    public function uninstall() {
        // Ayarları sil (tabloları koruyabilirsiniz)
        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE code LIKE 'module_modul_kodu%'");
    }
}
```

---

## 🎨 TEMPLATE ŞABLONU (.twig)

```twig
{{ header }}{{ column_left }}
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="form-modul" class="btn btn-primary">
                    <i class="fa fa-save"></i>
                </button>
                <a href="{{ cancel }}" class="btn btn-default">
                    <i class="fa fa-reply"></i>
                </a>
            </div>
            <h1>{{ heading_title }}</h1>
            <ul class="breadcrumb">
                {% for breadcrumb in breadcrumbs %}
                <li><a href="{{ breadcrumb.href }}">{{ breadcrumb.text }}</a></li>
                {% endfor %}
            </ul>
        </div>
    </div>
    
    <div class="container-fluid">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> {{ text_edit }}</h3>
            </div>
            <div class="panel-body">
                <form action="{{ action }}" method="post" id="form-modul" class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">{{ entry_status }}</label>
                        <div class="col-sm-10">
                            <select name="module_modul_kodu_status" class="form-control">
                                <option value="1">{{ text_enabled }}</option>
                                <option value="0">{{ text_disabled }}</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
{{ footer }}
```

---

## 🌍 DİL DOSYASI ŞABLONU

```php
<?php
// Heading
$_['heading_title']     = 'Modül Adı';

// Text
$_['text_extension']    = 'Modüller';
$_['text_success']      = 'Başarı: Modül güncellendi!';
$_['text_edit']         = 'Modülü Düzenle';
$_['text_enabled']      = 'Etkin';
$_['text_disabled']     = 'Devre Dışı';

// Entry
$_['entry_status']      = 'Durum';

// Error
$_['error_permission']  = 'Uyarı: Bu modülü değiştirme izniniz yok!';
```

---

## ⚙️ OCMOD OLUŞTURMA ADIMI

### 1. Dizin Yapısını Oluştur
```bash
mkdir ModulAdi-ocmod
cd ModulAdi-ocmod
mkdir -p upload/admin/controller/extension/module
mkdir -p upload/admin/model/extension/module
mkdir -p upload/admin/view/template/extension/module
mkdir -p upload/admin/language/tr-tr/extension/module
mkdir -p upload/admin/language/en-gb/extension/module
```

### 2. Dosyaları Oluştur
- install.xml
- Controller dosyası
- Model dosyası  
- Template dosyası
- Dil dosyaları

### 3. OCMod Paketi Oluştur
```bash
# ÖNEMLI: .ocmod.zip uzantısı kullan
zip -r ModulAdi.ocmod.zip install.xml upload/
```

### 4. PowerShell ile (Windows)
```powershell
Compress-Archive -Path "install.xml", "upload" -DestinationPath "ModulAdi.ocmod.zip"
```

---

## 🚨 YAYGIN HATALAR VE ÇÖZÜMLERİ

### ❌ "Geçersiz dosya türü" Hatası
**Sebep:** Dosya uzantısı `.zip` olarak kullanılmış
**Çözüm:** `.ocmod.zip` uzantısı kullan

### ❌ "install.xml bulunamadı" Hatası  
**Sebep:** install.xml dosyası eksik veya yanlış konumda
**Çözüm:** install.xml'i zip'in kök dizinine koy

### ❌ "Dosyalar çıkarılmıyor" Hatası
**Sebep:** upload/ klasörü eksik veya yanlış yapı
**Çözüm:** Standart upload/ yapısını kullan

### ❌ "Admin menüde link yok" Hatası
**Sebep:** install.xml'deki modification çalışmıyor
**Çözüm:** Extensions > Modifications > Refresh yap

---

## 🎯 BAŞARILI OCMOD KRİTERLERİ

### ✅ Dosya Kontrolü:
- [ ] `.ocmod.zip` uzantısı
- [ ] install.xml kök dizinde
- [ ] upload/ klasörü mevcut
- [ ] Standart OpenCart dizin yapısı

### ✅ Fonksiyon Kontrolü:
- [ ] Admin menüde link görünüyor
- [ ] Dashboard açılıyor
- [ ] Ayarlar kaydediliyor
- [ ] Install/Uninstall çalışıyor

### ✅ Kod Kalitesi:
- [ ] OpenCart 3.x uyumlu
- [ ] PHPDoc yorumları
- [ ] Hata işleme
- [ ] Güvenlik kontrolleri

---

## 📚 ÖRNEK PROJELER

### Basit Modül:
- Sadece ayarlar sayfası
- Tek controller
- Temel template

### Orta Seviye Modül:
- Dashboard + ayarlar
- AJAX işlemleri
- Veritabanı tabloları

### Gelişmiş Modül:
- Çoklu sayfalar
- API entegrasyonları
- Cron job desteği
- Webhook sistemi

---

**🎯 Bu kılavuzu takip ederek her zaman çalışan OCMod paketleri oluşturabilirsiniz!** 