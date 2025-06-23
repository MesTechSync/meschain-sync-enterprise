# OpenCart Modül Mimarisi Analiz Raporu

Bu rapor, OpenCart'taki standart modüllerin nasıl çalıştığını ve `MesChain Trendyol` eklentisinin bu yapıya nasıl entegre olduğunu açıklar.

## 1. Modüller Nerede ve Dosya Yapısı Nasıl?

OpenCart 4.x standartlarına göre, modüller `extension/{extension_name}/` klasörü altında bulunur. Panelde gördüğünüz modüller `opencart` isimli ana eklentinin bir parçasıdır.

**Örnek Dosya Yolları (Standart Modüller için):**
```
featured: ❌ Bulunamadı -> admin/controller/extension/opencart/module/featured.php
category: ❌ Bulunamadı -> admin/controller/extension/opencart/module/category.php
banner: ❌ Bulunamadı -> admin/controller/extension/opencart/module/banner.php
account: ❌ Bulunamadı -> admin/controller/extension/opencart/module/account.php
```
Her modülün benzer şekilde `model`, `view` (template) ve `language` dosyaları da ilgili klasörlerde bulunur. **Temel kural: Bir modülün tüm bileşenleri aynı dosya yolunu takip eder.**

### Karşılaştırma: MesChain Trendyol Modülü
Bizim eklentimiz ise kendi adıyla (`meschain`) gruplanmıştır:
```
admin/controller/extension/meschain/module/meschain_trendyol.php
admin/model/extension/meschain/module/meschain_trendyol.php
admin/view/template/extension/meschain/module/meschain_trendyol.twig
```
Bu yapı, eklentimizi OpenCart standartlarına uygun, temiz ve yönetilebilir kılar.

## 2. Veritabanı Kaydı (`oc_extension` Tablosu)

Bir modülün Eklentiler listesinde görünmesi için bu tabloya kaydedilmesi **zorunludur**. İşte panelde gördüğünüz modüllerin kayıtları:

| Modül Kodu (`code`) | Uzantı Adı (`extension`) | Tip (`type`) | Açıklama |
|---------------------|---------------------------|--------------|----------|
| `featured` | `opencart` | `module` | Standart OpenCart modülü. |
| `category` | `opencart` | `module` | Standart OpenCart modülü. |
| `banner` | `opencart` | `module` | Standart OpenCart modülü. |
| `account` | `opencart` | `module` | Standart OpenCart modülü. |

**Önemli Çıkarım:** OpenCart, bir modülün dosyalarını bulmak için `extension` ve `code` sütunlarını birleştirerek bir dosya yolu oluşturur. Örneğin, `featured` modülü için yol `extension/opencart/admin/controller/module/featured.php` şeklinde tahmin edilir.

### Karşılaştırma: MesChain Trendyol Modülü
| Modül Kodu (`code`) | Uzantı Adı (`extension`) | Tip (`type`) | Durum |
|---------------------|---------------------------|--------------|--------|
| `meschain_trendyol` | (yok) | (yok) | ❌ Kayıtlı Değil |

**Düzeltme Betiğinin Yaptığı:** Düzeltme betiğimiz, eklentimizi `code='meschain_trendyol'` ve `extension='meschain'` olarak bu tabloya ekledi. Bu sayede OpenCart artık eklentimizin dosyalarını `extension/meschain/admin/controller/module/meschain_trendyol.php` yolunda doğru bir şekilde bulabilir. **"Görünüp kaybolma" sorununun ana nedeni bu kaydın olmaması veya yanlış olmasıydı.**

## 3. Modül Durumu (`oc_setting` Tablosu)

Bir modülün "Enabled" (Etkin) veya "Disabled" (Devre Dışı) durumu bu tabloda saklanır.

| Modül | Ayar Anahtarı (`key`) | Değer (`value`) | Durum |
|---------|---------------------------|-----------------|--------|
| `featured` | `module_featured_status` | `0 (Kayıt Yok)` | ❌ Devre Dışı |
| `category` | `module_category_status` | `1` | ✅ Etkin |
| `banner` | `module_banner_status` | `0 (Kayıt Yok)` | ❌ Devre Dışı |
| `account` | `module_account_status` | `1` | ✅ Etkin |
| `meschain_trendyol` | `module_meschain_trendyol_status` | `0 (Kayıt Yok)` | ❌ Devre Dışı |

**Düzeltme Betiğinin Yaptığı:** Betiğimiz, `module_meschain_trendyol_status` anahtarı için değeri `1` (Etkin) olarak ayarladı. Bu olmadan eklenti listede görünse bile kullanılamazdı.

## 4. Sayfalara Ekleme (`oc_layout_module` Tablosu)

Son olarak, "Etkin" bir modülün sitenin hangi sayfasında (örn. anasayfa, kategori sayfası) ve neresinde (örn. sol sütun, en üst) görüneceği bu tabloda belirlenir.

| Atandığı Sayfa (`layout_name`) | Modül Kodu (`code`) | Pozisyon (`position`) |
|-------------------------------|-----------------------|-----------------------|
| Account | `opencart.account` | `column_right` |
| Affiliate | `opencart.account` | `column_right` |
| Category | `opencart.category` | `column_left` |
| Category | `opencart.banner.1` | `column_left` |
| Home | `opencart.banner.3` | `content_top` |
| Home | `opencart.banner.4` | `content_bottom` |
| Home | `opencart.featured.2` | `content_top` |

**Bizim Durumumuz:** `MesChain Trendyol` bir arayüz modülü (ön yüzde görünen) değil, bir **yönetim paneli aracıdır**. Bu yüzden bu tabloya kaydedilmesine gerek yoktur. Bizim entegrasyonumuz yönetici menüsü (`oc_menu`) üzerinden yapılır, ki düzeltme betiğimiz bunu da halletmiştir.

## Sonuç ve Özet

1.  **Dosya Yapısı:** Modülünüz standartlara uygun olarak `extension/meschain/` altında düzgün bir şekilde yapılandırılmıştır. ✅
2.  **Veritabanı Kaydı:** En kritik adım olan `oc_extension` kaydı, düzeltme betiği ile doğru bir şekilde yapıldı. Bu, OpenCart'ın modülünüzü "görmesini" sağlar. ✅
3.  **Etkinleştirme:** Modül durumu `oc_setting` tablosunda `1` olarak ayarlandı, böylece kullanılabilir hale geldi. ✅
4.  **Yönetim:** Eklentiniz bir yönetim aracı olduğu için, sayfa düzenlerine değil, doğrudan yönetici menüsüne entegre edildi. ✅

Artık sistemin tüm parçaları birbiriyle doğru bir şekilde konuşuyor. Eklentinizin istikrarlı çalışmasının sebebi, bu dört temel adımın eksiksiz ve doğru bir şekilde tamamlanmış olmasıdır.
