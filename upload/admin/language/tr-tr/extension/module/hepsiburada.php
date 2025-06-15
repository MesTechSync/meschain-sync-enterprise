<?php
/**
 * Hepsiburada Language File - Turkish
 * MesChain-Sync v3.0 - OpenCart 3.0.4.0 Integration
 * 
 * @author MesChain Development Team
 * @version 3.0.0
 * @copyright 2024 MesChain Technologies
 */

// Heading
$_['heading_title']                = 'Hepsiburada Marketplace';

// Text
$_['text_extension']              = 'Entegrasyonlar';
$_['text_success']                = 'Başarılı: Hepsiburada modülü güncellendi!';
$_['text_edit']                   = 'Hepsiburada Modülünü Düzenle';
$_['text_enabled']                = 'Etkin';
$_['text_disabled']               = 'Pasif';
$_['text_yes']                    = 'Evet';
$_['text_no']                     = 'Hayır';
$_['text_loading']                = 'Yükleniyor...';
$_['text_select']                 = 'Seçiniz';

// Tab
$_['tab_general']                 = 'Genel Ayarlar';
$_['tab_api']                     = 'API Ayarları';
$_['tab_cargo']                   = 'Kargo Ayarları';
$_['tab_promotions']              = 'Promosyonlar';
$_['tab_categories']              = 'Kategoriler';
$_['tab_sync']                    = 'Senkronizasyon';
$_['tab_orders']                  = 'Siparişler';
$_['tab_logs']                    = 'Loglar';

// Entry
$_['entry_status']                = 'Durum';
$_['entry_username']              = 'Kullanıcı Adı';
$_['entry_password']              = 'Şifre';
$_['entry_merchant_id']           = 'Merchant ID';
$_['entry_cargo_company']         = 'Varsayılan Kargo Şirketi';
$_['entry_auto_approve']          = 'Otomatik Onay';
$_['entry_debug']                 = 'Debug Modu';
$_['entry_auto_sync']             = 'Otomatik Senkronizasyon';
$_['entry_sync_interval']         = 'Senkronizasyon Aralığı (dakika)';
$_['entry_price_markup']          = 'Fiyat Markup (%)';
$_['entry_stock_buffer']          = 'Stok Tamponu';
$_['entry_category_mapping']      = 'Kategori Eşleştirme';
$_['entry_vat_rate']              = 'KDV Oranı (%)';
$_['entry_preparation_time']      = 'Hazırlık Süresi (gün)';

// Help
$_['help_username']               = 'Hepsiburada Marketplace API kullanıcı adınızı girin.';
$_['help_password']               = 'Hepsiburada Marketplace API şifrenizi girin.';
$_['help_merchant_id']            = 'Hepsiburada Merchant ID\'nizi girin.';
$_['help_cargo_company']          = 'Varsayılan kargo şirketini seçin.';
$_['help_auto_approve']           = 'Siparişleri otomatik olarak onaylamak için etkinleştirin.';
$_['help_debug']                  = 'Debug modunda tüm API istekleri kaydedilir.';
$_['help_auto_sync']              = 'Otomatik ürün senkronizasyonunu etkinleştirin.';
$_['help_sync_interval']          = 'Otomatik senkronizasyon aralığı (15-1440 dakika arası).';
$_['help_price_markup']           = 'Hepsiburada\'ya gönderilecek fiyatlara eklenecek markup yüzdesi.';
$_['help_stock_buffer']           = 'Güvenlik stoku (negatif stok durumunu önlemek için).';
$_['help_vat_rate']               = 'Türkiye KDV oranı (varsayılan %18).';
$_['help_preparation_time']       = 'Ürün hazırlık süresi (1-5 gün arası).';

// Button
$_['button_save']                 = 'Kaydet';
$_['button_cancel']               = 'İptal';
$_['button_test_connection']      = 'Bağlantıyı Test Et';
$_['button_sync_products']        = 'Ürünleri Senkronize Et';
$_['button_sync_prices']          = 'Fiyatları Güncelle';
$_['button_sync_orders']          = 'Siparişleri Al';
$_['button_update_cargo']         = 'Kargo Bilgilerini Güncelle';
$_['button_manage_promotions']    = 'Promosyonları Yönet';
$_['button_clear_logs']           = 'Logları Temizle';
$_['button_export_logs']          = 'Logları Dışa Aktar';

// Success Messages
$_['text_api_connected']          = 'Hepsiburada API bağlantısı başarılı!';
$_['text_sync_success']           = '%d ürün başarıyla senkronize edildi!';
$_['text_price_update_success']   = '%d ürün fiyatı başarıyla güncellendi!';
$_['text_cargo_update_success']   = '%d sipariş kargo bilgisi güncellendi!';
$_['text_orders_synced']          = '%d sipariş başarıyla senkronize edildi!';
$_['text_promotions_applied']     = '%d promosyon uygulandı!';

// Error Messages
$_['error_permission']            = 'Uyarı: Bu modülü değiştirme izniniz yok!';
$_['error_username']              = 'Kullanıcı adı gerekli!';
$_['error_password']              = 'Şifre gerekli!';
$_['error_merchant_id']           = 'Merchant ID gerekli!';
$_['error_api_credentials']       = 'API kimlik bilgileri ayarlanmamış!';
$_['error_api_connection']        = 'Hepsiburada API bağlantısı başarısız!';
$_['error_api_timeout']           = 'API isteği zaman aşımına uğradı!';
$_['error_api_rate_limit']        = 'API rate limit aşıldı. Lütfen bekleyin.';
$_['error_product_not_found']     = 'Ürün bulunamadı!';
$_['error_invalid_cargo']         = 'Geçersiz kargo şirketi!';
$_['error_sync_failed']           = 'Senkronizasyon başarısız!';
$_['error_no_products']           = 'Senkronize edilecek ürün bulunamadı!';
$_['error_invalid_price']         = 'Geçersiz fiyat formatı!';
$_['error_insufficient_stock']    = 'Yetersiz stok!';
$_['error_category_mapping']      = 'Kategori eşleştirmesi bulunamadı!';
$_['error_promotion_failed']      = 'Promosyon uygulanırken hata oluştu!';

// Column Headers
$_['column_product']              = 'Ürün';
$_['column_sku']                  = 'SKU';
$_['column_status']               = 'Durum';
$_['column_hb_id']                = 'Hepsiburada ID';
$_['column_price']                = 'Fiyat';
$_['column_stock']                = 'Stok';
$_['column_cargo_company']        = 'Kargo Şirketi';
$_['column_last_sync']            = 'Son Senkronizasyon';
$_['column_action']               = 'İşlem';
$_['column_order_id']             = 'Sipariş ID';
$_['column_customer']             = 'Müşteri';
$_['column_total']                = 'Toplam';
$_['column_date']                 = 'Tarih';
$_['column_tracking_number']      = 'Kargo Takip No';
$_['column_category_id']          = 'Kategori ID';
$_['column_category_name']        = 'Kategori Adı';
$_['column_commission_rate']      = 'Komisyon Oranı';

// Status Options
$_['text_status_pending']         = 'Beklemede';
$_['text_status_synced']          = 'Senkronize';
$_['text_status_error']           = 'Hata';
$_['text_status_rejected']        = 'Reddedildi';
$_['text_status_approved']        = 'Onaylandı';

$_['text_approval_waiting']       = 'Onay Bekliyor';
$_['text_approval_approved']      = 'Onaylandı';
$_['text_approval_rejected']      = 'Reddedildi';

// Cargo Companies
$_['text_cargo_yurtici']          = 'Yurtiçi Kargo';
$_['text_cargo_mng']              = 'MNG Kargo';
$_['text_cargo_aras']             = 'Aras Kargo';
$_['text_cargo_ptt']              = 'PTT Kargo';
$_['text_cargo_ups']              = 'UPS Kargo';
$_['text_cargo_sendeo']           = 'Sendeo';
$_['text_cargo_horoz']            = 'Horoz Lojistik';
$_['text_cargo_susaydin']         = 'Süsaydın Nakliyat';

// Order Status
$_['text_order_new']              = 'Yeni';
$_['text_order_preparing']        = 'Hazırlanıyor';
$_['text_order_shipped']          = 'Kargoya Verildi';
$_['text_order_delivered']        = 'Teslim Edildi';
$_['text_order_cancelled']        = 'İptal Edildi';
$_['text_order_returned']         = 'İade Edildi';

// Dashboard
$_['text_dashboard']              = 'Hepsiburada Dashboard';
$_['text_total_products']         = 'Toplam Ürün';
$_['text_synced_products']        = 'Senkronize Ürün';
$_['text_pending_products']       = 'Bekleyen Ürün';
$_['text_monthly_orders']         = 'Aylık Sipariş';
$_['text_monthly_revenue']        = 'Aylık Gelir';
$_['text_api_status']             = 'API Durumu';
$_['text_last_sync']              = 'Son Senkronizasyon';
$_['text_active_promotions']      = 'Aktif Promosyonlar';
$_['text_pending_shipments']      = 'Bekleyen Kargolar';
$_['text_quick_actions']          = 'Hızlı İşlemler';

// Notifications
$_['text_sync_started']           = 'Senkronizasyon başlatıldı...';
$_['text_sync_completed']         = 'Senkronizasyon tamamlandı!';
$_['text_price_update_started']   = 'Fiyat güncelleme başlatıldı...';
$_['text_price_update_completed'] = 'Fiyat güncelleme tamamlandı!';
$_['text_cargo_update_started']   = 'Kargo güncelleme başlatıldı...';
$_['text_cargo_update_completed'] = 'Kargo güncelleme tamamlandı!';

// Turkish Market Specific
$_['text_try_currency']           = '₺ (Türk Lirası)';
$_['text_vat_included']           = 'KDV Dahil';
$_['text_delivery_turkey']        = 'Türkiye\'ye Teslimat';
$_['text_istanbul_time']          = 'İstanbul Saati';
$_['text_business_hours']         = 'İş Saatleri (09:00-18:00)';
$_['text_list_price']             = 'Liste Fiyatı';
$_['text_sale_price']             = 'İndirimli Fiyat';
$_['text_free_shipping']          = 'Ücretsiz Kargo';
$_['text_fast_delivery']          = 'Hızlı Teslimat';

// Categories
$_['text_category_elektronik']    = 'Elektronik';
$_['text_category_moda']          = 'Moda';
$_['text_category_ev_yasam']      = 'Ev & Yaşam';
$_['text_category_spor']          = 'Spor & Outdoor';
$_['text_category_otomotiv']      = 'Otomotiv';
$_['text_category_anne_bebek']    = 'Anne & Bebek';
$_['text_category_kitap']         = 'Kitap, Müzik, Film';
$_['text_category_oyuncak']       = 'Oyuncak';
$_['text_category_kozmetik']      = 'Kozmetik';
$_['text_category_supermarket']   = 'Süpermarket';

// Log Messages
$_['text_log_product_sync']       = 'Ürün senkronizasyonu';
$_['text_log_price_update']       = 'Fiyat güncelleme';
$_['text_log_stock_update']       = 'Stok güncelleme';
$_['text_log_order_sync']         = 'Sipariş senkronizasyonu';
$_['text_log_cargo_update']       = 'Kargo güncelleme';
$_['text_log_promotion_sync']     = 'Promosyon senkronizasyonu';
$_['text_log_api_call']           = 'API çağrısı';
$_['text_log_error']              = 'Hata';
$_['text_log_success']            = 'Başarılı';

// Tooltips
$_['tooltip_sync_all']            = 'Tüm ürünleri Hepsiburada\'ya senkronize et';
$_['tooltip_update_prices']       = 'Değişen fiyatları Hepsiburada\'da güncelle';
$_['tooltip_update_cargo']        = 'Sipariş kargo bilgilerini güncelle';
$_['tooltip_test_api']            = 'API bağlantısını ve kimlik bilgilerini test et';
$_['tooltip_clear_logs']          = 'Tüm senkronizasyon loglarını temizle';
$_['tooltip_manage_promotions']   = 'Aktif promosyonları yönet ve uygula';

// Form Validation
$_['validation_username_format']  = 'Kullanıcı adı geçerli format olmalıdır!';
$_['validation_password_length']  = 'Şifre en az 8 karakter olmalıdır!';
$_['validation_merchant_id']      = 'Merchant ID sayısal olmalıdır!';
$_['validation_sync_interval']    = 'Senkronizasyon aralığı 15-1440 dakika arasında olmalıdır!';
$_['validation_price_markup']     = 'Fiyat markup 0-100 arasında olmalıdır!';
$_['validation_vat_rate']         = 'KDV oranı 1-50 arasında olmalıdır!';
$_['validation_preparation_time'] = 'Hazırlık süresi 1-5 gün arasında olmalıdır!';

// Integration Info
$_['text_module_version']         = 'MesChain-Sync v3.0';
$_['text_hb_api_version']         = 'Hepsiburada Marketplace API v1';
$_['text_supported_features']     = 'Desteklenen Özellikler';
$_['text_cargo_integration']      = '✓ Kargo Entegrasyonu';
$_['text_realtime_sync']          = '✓ Gerçek Zamanlı Senkronizasyon';
$_['text_promotion_management']   = '✓ Promosyon Yönetimi';
$_['text_auto_categories']        = '✓ Otomatik Kategori Eşleştirme';
$_['text_order_management']       = '✓ Sipariş Yönetimi';
$_['text_turkish_market']         = '✓ Türk Pazarı Optimizasyonu';
$_['text_performance_monitoring'] = '✓ Performans İzleme';
$_['text_vat_support']            = '✓ KDV Desteği';

// Additional Features
$_['text_barcode_generation']     = 'Otomatik Barkod Oluşturma';
$_['text_image_optimization']     = 'Görsel Optimizasyonu';
$_['text_seo_friendly']           = 'SEO Dostu Ürün Başlıkları';
$_['text_multi_cargo']            = 'Çoklu Kargo Desteği';
$_['text_commission_tracking']    = 'Komisyon Takibi';
$_['text_return_management']      = 'İade Yönetimi';

// Time and Date
$_['text_never']                  = 'Hiçbir zaman';
$_['text_days']                   = 'gün';
$_['text_hours']                  = 'saat';
$_['text_minutes']                = 'dakika';
$_['text_active']                 = 'Aktif';
$_['text_inactive']               = 'Pasif';

// Promotion Types
$_['text_promotion_discount']     = 'İndirim';
$_['text_promotion_campaign']     = 'Kampanya';
$_['text_promotion_flash_sale']   = 'Flaş İndirim';
$_['text_promotion_bundle']       = 'Paket';
$_['text_discount_percentage']    = 'Yüzde';
$_['text_discount_amount']        = 'Tutar';
?> 