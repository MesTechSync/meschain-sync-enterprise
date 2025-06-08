# OPUS Design System - OpenCart Entegrasyon Rehberi

## 🎨 Genel Bakış

OPUS Design System, OpenCart 3.0+ için özel olarak tasarlanmış, modern ve kapsamlı bir UI/UX çözümüdür. Bu sistem, SELINAY ve GEMINI design system'lerinin en iyi özelliklerini alarak, çok daha gelişmiş bir deneyim sunar.

## 🚀 Özellikler

- **🌓 Gelişmiş Tema Sistemi**: Light, Dark, Midnight, Sunset, Ocean, Forest temaları
- **📱 Tam Responsive**: Tüm cihazlarda mükemmel görünüm
- **⚡ Performans Odaklı**: GPU hızlandırmalı animasyonlar
- **♿ Erişilebilirlik**: WCAG 2.1 AA uyumlu
- **🎯 OpenCart Uyumlu**: MVC yapısına tam uyum
- **🔧 Kolay Özelleştirme**: CSS değişkenleri ile hızlı tema değişimi

## 📦 Kurulum

### 1. Dosyaları Kopyalama

```bash
# OPUS Design System dosyalarını OpenCart'a kopyalayın
cp -r OPUS_DESIGN_SYSTEM/core/* upload/catalog/view/theme/default/stylesheet/opus/core/
cp -r OPUS_DESIGN_SYSTEM/components/* upload/catalog/view/theme/default/stylesheet/opus/components/
cp -r OPUS_DESIGN_SYSTEM/themes/* upload/catalog/view/javascript/opus/themes/
```

### 2. Header.twig Düzenleme

`upload/catalog/view/theme/default/template/common/header.twig` dosyasına ekleyin:

```html
<!-- OPUS Design System CSS -->
<link rel="stylesheet" href="catalog/view/theme/default/stylesheet/opus/core/opus-variables.css">
<link rel="stylesheet" href="catalog/view/theme/default/stylesheet/opus/core/opus-base.css">
<link rel="stylesheet" href="catalog/view/theme/default/stylesheet/opus/core/opus-animations.css">
<link rel="stylesheet" href="catalog/view/theme/default/stylesheet/opus/components/opus-components.css">

<!-- OPUS Theme Manager -->
<script src="catalog/view/javascript/opus/themes/opus-theme-manager.js"></script>
```

### 3. Admin Panel Entegrasyonu

Admin panel için `upload/admin/view/template/common/header.twig` dosyasına aynı linkleri ekleyin.

## 🎨 Kullanım Örnekleri

### Butonlar

```html
<!-- Primary Button -->
<button class="opus-btn opus-btn-primary">
    <i class="fa fa-shopping-cart"></i>
    Sepete Ekle
</button>

<!-- Loading Button -->
<button class="opus-btn opus-btn-primary opus-btn-loading">
    İşleniyor...
</button>

<!-- Hover Efektli Button -->
<button class="opus-btn opus-btn-accent opus-hover-lift">
    Hemen Al
</button>
```

### Kartlar

```html
<!-- Ürün Kartı -->
<div class="opus-card opus-hover-lift">
    <img src="product.jpg" alt="Ürün">
    <div class="opus-card-header">
        <h3 class="opus-card-title">Ürün Adı</h3>
        <p class="opus-card-subtitle">Kategori</p>
    </div>
    <div class="opus-card-body">
        <p class="opus-text-2xl opus-font-bold opus-text-primary">₺299.99</p>
    </div>
    <div class="opus-card-footer">
        <button class="opus-btn opus-btn-primary opus-btn-sm">Sepete Ekle</button>
    </div>
</div>
```

### Form Elemanları

```html
<!-- Arama Formu -->
<form class="opus-form">
    <div class="opus-form-group">
        <label class="opus-label">Ürün Ara</label>
        <input type="text" class="opus-input" placeholder="Ne aramıştınız?">
    </div>
    <button type="submit" class="opus-btn opus-btn-primary">
        <i class="fa fa-search"></i> Ara
    </button>
</form>
```

### Bildirimler

```javascript
// Başarı bildirimi
function showSuccessNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'opus-notification opus-notification-success';
    notification.innerHTML = `
        <div class="opus-notification-title">Başarılı!</div>
        <div>${message}</div>
        <div class="opus-notification-close" onclick="this.parentElement.remove()">✕</div>
    `;
    document.body.appendChild(notification);
}
```

## 🔧 OpenCart Controller Entegrasyonu

### Controller Örneği

```php
class ControllerExtensionModuleOpusTheme extends Controller {
    public function index() {
        // CSS dosyalarını yükle
        $this->document->addStyle('catalog/view/theme/default/stylesheet/opus/core/opus-variables.css');
        $this->document->addStyle('catalog/view/theme/default/stylesheet/opus/core/opus-base.css');
        $this->document->addStyle('catalog/view/theme/default/stylesheet/opus/core/opus-animations.css');
        $this->document->addStyle('catalog/view/theme/default/stylesheet/opus/components/opus-components.css');
        
        // JavaScript dosyalarını yükle
        $this->document->addScript('catalog/view/javascript/opus/themes/opus-theme-manager.js');
        
        // Tema tercihini al
        $data['theme'] = $this->config->get('opus_theme') ?: 'light';
        
        return $this->load->view('extension/module/opus_theme', $data);
    }
}
```

### Model Örneği

```php
class ModelExtensionModuleOpusTheme extends Model {
    public function getThemeSettings() {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "opus_theme_settings");
        return $query->row;
    }
    
    public function saveThemeSettings($data) {
        $this->db->query("UPDATE " . DB_PREFIX . "opus_theme_settings SET 
            theme = '" . $this->db->escape($data['theme']) . "',
            custom_colors = '" . $this->db->escape(json_encode($data['custom_colors'])) . "'
            WHERE setting_id = '1'");
    }
}
```

## 🎯 MesChain-Sync Entegrasyonu

### Dashboard Örneği

```html
<!-- MesChain Dashboard -->
<div class="opus-container">
    <h1 class="opus-text-4xl opus-font-bold opus-animate-fade-in">
        MesChain Sync Dashboard
    </h1>
    
    <div class="opus-grid opus-grid-cols-4" style="margin-top: var(--opus-space-8);">
        <!-- Trendyol Card -->
        <div class="opus-card opus-card-elevated opus-animate-scale-in">
            <div class="opus-card-header">
                <h3 class="opus-card-title">Trendyol</h3>
                <span class="opus-badge opus-badge-success">Aktif</span>
            </div>
            <div class="opus-card-body">
                <p class="opus-text-3xl opus-font-bold">1,234</p>
                <p class="opus-text-sm opus-text-tertiary">Toplam Ürün</p>
            </div>
        </div>
        
        <!-- Diğer marketplace kartları... -->
    </div>
</div>
```

### API Durum Göstergesi

```html
<!-- API Status -->
<div class="opus-flex opus-items-center opus-space-x-2">
    <div class="opus-spinner opus-spinner-sm"></div>
    <span class="opus-text-sm">API'ye bağlanılıyor...</span>
</div>

<!-- Success State -->
<div class="opus-flex opus-items-center opus-space-x-2">
    <span class="opus-badge opus-badge-success">✓ Bağlı</span>
    <span class="opus-text-sm">Son güncelleme: 2 dakika önce</span>
</div>
```

## 🌙 Tema Değiştirici Entegrasyonu

```javascript
// Tema değiştiriciyi navbar'a ekle
document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.querySelector('.navbar');
    const themeSwitcher = opusTheme.createThemeSwitcher();
    navbar.appendChild(themeSwitcher);
    
    // Tema değişikliklerini dinle
    window.addEventListener('opus-theme-changed', function(e) {
        console.log('Tema değişti:', e.detail.theme);
        // AJAX ile sunucuya kaydet
        $.ajax({
            url: 'index.php?route=extension/module/opus_theme/saveTheme',
            method: 'POST',
            data: { theme: e.detail.theme },
            success: function() {
                console.log('Tema tercihi kaydedildi');
            }
        });
    });
});
```

## 📱 Responsive Tasarım

OPUS Design System, mobil öncelikli yaklaşımla tasarlanmıştır:

```css
/* Mobil görünüm */
@media (max-width: 768px) {
    .opus-grid-cols-4 {
        grid-template-columns: repeat(1, 1fr);
    }
    
    .opus-container {
        padding-left: var(--opus-space-4);
        padding-right: var(--opus-space-4);
    }
}

/* Tablet görünüm */
@media (min-width: 768px) and (max-width: 1024px) {
    .opus-grid-cols-4 {
        grid-template-columns: repeat(2, 1fr);
    }
}
```

## 🚨 Önemli Notlar

1. **Cache Temizleme**: CSS değişikliklerinden sonra OpenCart cache'ini temizleyin
2. **Tema Uyumu**: Default tema ile çakışmaları önlemek için OPUS class'larını kullanın
3. **JavaScript Sırası**: jQuery'den sonra OPUS scriptlerini yükleyin
4. **Performans**: Kullanılmayan bileşenleri yüklemeyin

## 🤝 Destek

- **Dokümantasyon**: `/OPUS_DESIGN_SYSTEM/docs/`
- **Demo**: `/OPUS_DESIGN_SYSTEM/demos/index.html`
- **GitHub**: [MesChain-Sync Repository](https://github.com/mezbjen/meschain-sync)

## 📄 Lisans

OPUS Design System, MesChain-Sync projesi kapsamında MIT lisansı ile sunulmaktadır. 