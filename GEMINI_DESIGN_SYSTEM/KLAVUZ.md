# GEMINI DESIGN SYSTEM - Geliştirici Kılavuzu (v1.0)

**ID:** GDS-DOCS-V1.0  
**Tarih:** 8 Haziran 2025

---

## 1. Felsefe ve Amaç

GEMINI DESIGN SYSTEM (GDS), MesChain-Sync projesinin kullanıcı arayüzlerini (UI) ve kullanıcı deneyimini (UX) standartlaştırmak, modernleştirmek ve hızlandırmak için tasarlanmış merkezi bir tasarım ve bileşen kütüphanesidir. Bu sistem, "atomik tasarım" prensiplerini benimser; yani en küçük yapı taşlarından (atomlar: renkler, butonlar) başlayarak daha karmaşık organizmalara (moleküller: arama çubukları, kartlar) ve templatelere (sayfa yerleşimleri) kadar tutarlı bir bütün oluşturur.

**Temel Hedefler:**
-   **Hız:** Tekrar kullanılabilir bileşenler sayesinde geliştirme sürecini %70'e kadar hızlandırmak.
-   **Tutarlılık:** Projenin tüm panellerinde (Süper Admin, Admin, Dropshipping, Teknik Kullanıcı) aynı görsel dili ve kullanıcı deneyimini sağlamak.
-   **Modernizasyon:** React, TailwindCSS ve Framer Motion gibi modern teknolojileri kullanarak eskiyen Twig tabanlı arayüzden kademeli geçişi sağlamak.
-   **Bakım Kolaylığı:** Merkezi bir sistem sayesinde, bir bileşende yapılan değişikliklerin anında tüm projeye yansımasını sağlamak.

---

## 2. Dizin Yapısı ve Sorumluluklar

GDS, aşağıdaki modüler yapı üzerine kurulmuştur:

```
/GEMINI_DESIGN_SYSTEM
├── 📁 assets/
│   ├── 📁 icons/          # SVG ikon setleri
│   ├── 📄 animations.scss # Global animasyon stilleri (Framer Motion üzerine)
│   ├── 📄 colors.scss     # Ana renk paleti (CSS Değişkenleri olarak)
│   ├── 📄 shadows.scss     # Gölgelendirme stilleri
│   └── 📄 typography.scss  # Tipografi (font aileleri, boyutlar, ağırlıklar)
├── 📁 components/
│   ├── 📁 buttons/         # Buton bileşenleri (Primary, Secondary, Destructive vb.)
│   ├── 📁 cards/           # Bilgi kartları, widget'lar
│   ├── 📁 charts/          # Grafik bileşenleri (Recharts/ApexCharts)
│   └── 📁 forms/           # Input, Select, Checkbox gibi form elemanları
├── 📁 layouts/
│   └── 📄 DashboardLayout.tsx # Kenar çubuğu, başlık ve ana içerik alanını içeren ana sayfa yerleşimi
├── 📁 themes/
│   ├── 📄 dark.theme.ts    # Karanlık mod için tema değişkenleri
│   ├── 📄 light.theme.ts   # Aydınlık mod için tema değişkenleri
│   └── 📄 dynamic.theme.ts # Kullanıcı tercihlerine göre tema üreten script
├── 📁 utils/
│   ├── 📄 motionUtils.ts   # Kompleks animasyonlar için yardımcı fonksiyonlar (GSAP entegrasyonu)
│   └── 📄 themeSwitcher.ts # Temalar arası geçişi yöneten mantık
├── 📄 KLAVUZ.md            # Bu kılavuz
└── 📄 SUPER_ADMIN_PANEL.html # Bu sistemle oluşturulmuş örnek panel
```

---

## 3. Teknolojik Altyapı

-   **Framework:** React (JSX/TSX)
-   **Stil:** TailwindCSS (Yardımcı sınıflar tabanlı)
-   **Animasyon:** Framer Motion (Mikro etkileşimler ve geçişler için)
-   **Grafikler:** Recharts veya ApexCharts (Veri görselleştirme)
-   **İkonlar:** Phosphor Icons / Lucide (veya özel SVG setleri)
-   **Kod Kalitesi:** ESLint + Prettier

---

## 4. Nasıl Kullanılır? Mevcut Sisteme Entegrasyon

GDS'nin amacı, mevcut OpenCart (Twig) yapısını bir anda değiştirmek değil, onu adım adım modernleştirmektir. Bu "Büyük Birleşme" stratejisi şu adımlarla gerçekleştirilir:

### Adım 1: React Uygulamasını Yüklemek

OpenCart Controller'ından (örn: `admin/controller/extension/module/meschain_dashboard.php`) verileri çektikten sonra, bu verileri tek bir Twig şablonuna gönderin. Hedefimiz, zamanla tüm `.twig` dosyalarını tek bir ana şablon (`meschain_react_loader.twig`) altında birleştirmektir.

Bu ana Twig dosyası, derlenmiş React uygulamasını yüklemekle görevlidir:

```twig
{# meschain_react_loader.twig #}
{{ header }}{{ column_left }}
<div id="content">
  {# React uygulamasının bağlanacağı kök element #}
  <div id="react-root" data-user-role="{{ user_role }}" data-api-token="{{ api_token }}"></div>
</div>

{# Gerekli veri objesini window nesnesine ekle #}
<script type.="text/javascript">
  window.opencart = {
    user: {
      name: "{{ user_fullname }}",
      email: "{{ user_email }}"
    },
    api: {
      baseUrl: "{{ api_base_url }}",
      endpoints: {
        orders: "{{ api_orders_endpoint }}",
        products: "{{ api_products_endpoint }}"
      }
    }
  };
</script>

{# Derlenmiş React JS ve CSS dosyalarını yükle (Bu yollar derleme sonrası oluşur) #}
<link href="view/javascript/meschain-react/static/css/main.css" rel="stylesheet">
<script src="view/javascript/meschain-react/static/js/main.js"></script>

{{ footer }}
```

### Adım 2: React Tarafında Veriyi Almak ve Bileşenleri Kullanmak

`meschain-frontend` projesinin ana giriş noktasında (`index.tsx`), OpenCart'tan gelen verileri okuyup React uygulamasını başlatın.

```tsx
// meschain-frontend/src/index.tsx
import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './App';

const rootElement = document.getElementById('react-root');
if (rootElement) {
  const root = ReactDOM.createRoot(rootElement);

  // OpenCart'tan gelen global veriyi al
  const opencartData = window.opencart;

  root.render(
    <React.StrictMode>
      {/* Veriyi App bileşenine prop olarak geçir */}
      <App opencart={opencartData} />
    </React.StrictMode>
  );
}
```

### Adım 3: Yeni Bir Bileşen Geliştirmek

1.  **Klasör Oluşturun:** `GEMINI_DESIGN_SYSTEM/components` altında bileşeniniz için yeni bir klasör oluşturun (örn: `status_pills`).
2.  **Bileşeni Yazın:**
    ```tsx
    // GEMINI_DESIGN_SYSTEM/components/status_pills/StatusPill.tsx
    import React from 'react';

    interface StatusPillProps {
      status: 'active' | 'inactive' | 'pending' | 'error';
      text: string;
    }

    const statusClasses = {
      active: 'bg-green-100 text-green-800',
      inactive: 'bg-gray-100 text-gray-800',
      pending: 'bg-yellow-100 text-yellow-800',
      error: 'bg-red-100 text-red-800',
    };

    const StatusPill: React.FC<StatusPillProps> = ({ status, text }) => {
      return (
        <span className={`px-2 py-1 text-xs font-medium rounded-full ${statusClasses[status]}`}>
          {text}
        </span>
      );
    };

    export default StatusPill;
    ```
3.  **Kullanın:** Geliştirdiğiniz bileşeni, ihtiyacınız olan herhangi bir sayfada veya başka bir bileşende import ederek kullanın.

---

## 5. Vizyon: Süper Admin Paneli

`SUPER_ADMIN_PANEL.html`, bu sistemin yeteneklerini sergileyen canlı bir belgedir. İçerisinde GDS ile oluşturulmuş şu gibi modern elementler barındırır:

-   **Glassmorphism Kenar Çubuğu:** Yarı saydam ve bulanık arka plana sahip modern bir menü.
-   **Dinamik Widget'lar:** Sürüklenip bırakılabilen, yeniden boyutlandırılabilen ve içeriği canlı olarak güncellenen veri kartları.
-   **Etkileşimli Grafikler:** Üzerine gelindiğinde detay gösteren, animasyonlu geçişlere sahip grafikler.
-   **Gerçek Zamanlı Bildirimler:** Sağ üst köşeden kayarak gelen, animasyonlu "toast" bildirimleri.

Bu kılavuz, projenin arayüz geliştirme süreçlerini birleştirmek ve geleceğe taşımak için yaşayan bir belgedir. Yeni bileşenler ve standartlar eklendikçe güncellenmelidir. 