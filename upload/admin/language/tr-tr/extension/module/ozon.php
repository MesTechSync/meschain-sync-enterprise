<?php
/**
 * Ozon Language File - Turkish
 * MesChain-Sync v3.0 - OpenCart 3.0.4.0 Integration
 * 
 * @author MesChain Development Team
 * @version 3.0.0
 * @copyright 2024 MesChain Technologies
 */

// Heading
$_['heading_title']                = 'Ozon Marketplace';

// Text
$_['text_extension']              = 'Entegrasyonlar';
$_['text_success']                = 'Başarılı: Ozon modülü güncellendi!';
$_['text_edit']                   = 'Ozon Modülünü Düzenle';
$_['text_enabled']                = 'Etkin';
$_['text_disabled']               = 'Pasif';
$_['text_yes']                    = 'Evet';
$_['text_no']                     = 'Hayır';
$_['text_loading']                = 'Yükleniyor...';

// Tab
$_['tab_general']                 = 'Genel Ayarlar';
$_['tab_api']                     = 'API Ayarları';
$_['tab_fbo']                     = 'FBO Ayarları';
$_['tab_sync']                    = 'Senkronizasyon';
$_['tab_orders']                  = 'Siparişler';
$_['tab_logs']                    = 'Loglar';

// Entry
$_['entry_status']                = 'Durum';
$_['entry_client_id']             = 'Client ID';
$_['entry_api_key']               = 'API Key';
$_['entry_fbo_enabled']           = 'FBO Etkin';
$_['entry_warehouse_id']          = 'Varsayılan Depo ID';
$_['entry_debug']                 = 'Debug Modu';
$_['entry_auto_sync']             = 'Otomatik Senkronizasyon';
$_['entry_sync_interval']         = 'Senkronizasyon Aralığı (dakika)';
$_['entry_price_markup']          = 'Fiyat Markup (%)';
$_['entry_stock_buffer']          = 'Stok Tamponu';
$_['entry_category_mapping']      = 'Kategori Eşleştirme';

// Help
$_['help_client_id']              = 'Ozon Seller API Client ID\'nizi girin.';
$_['help_api_key']                = 'Ozon Seller API Key\'inizi girin.';
$_['help_fbo_enabled']            = 'FBO (Fulfillment by Ozon) hizmetini kullanmak için etkinleştirin.';
$_['help_warehouse_id']           = 'Varsayılan FBO depo ID\'nizi girin.';
$_['help_debug']                  = 'Debug modunda tüm API istekleri kaydedilir.';
$_['help_auto_sync']              = 'Otomatik ürün senkronizasyonunu etkinleştirin.';
$_['help_sync_interval']          = 'Otomatik senkronizasyon aralığı (15-1440 dakika arası).';
$_['help_price_markup']           = 'Ozon\'a gönderilecek fiyatlara eklenecek markup yüzdesi.';
$_['help_stock_buffer']           = 'Güvenlik stoku (negatif stok durumunu önlemek için).';

// Button
$_['button_save']                 = 'Kaydet';
$_['button_cancel']               = 'İptal';
$_['button_test_connection']      = 'Bağlantıyı Test Et';
$_['button_sync_products']        = 'Ürünleri Senkronize Et';
$_['button_sync_prices']          = 'Fiyatları Güncelle';
$_['button_sync_orders']          = 'Siparişleri Al';
$_['button_bulk_upload']          = 'Toplu FBO Yükleme';
$_['button_clear_logs']           = 'Logları Temizle';
$_['button_export_logs']          = 'Logları Dışa Aktar';

// Success Messages
$_['text_api_connected']          = 'Ozon API bağlantısı başarılı!';
$_['text_sync_success']           = '%d ürün başarıyla senkronize edildi!';
$_['text_price_update_success']   = '%d ürün fiyatı başarıyla güncellendi!';
$_['text_fbo_upload_success']     = '%d ürün FBO deposuna başarıyla yüklendi!';
$_['text_orders_synced']          = '%d sipariş başarıyla senkronize edildi!';

// Error Messages
$_['error_permission']            = 'Uyarı: Bu modülü değiştirme izniniz yok!';
$_['error_client_id']             = 'Client ID gerekli!';
$_['error_api_key']               = 'API Key gerekli!';
$_['error_api_credentials']       = 'API kimlik bilgileri ayarlanmamış!';
$_['error_api_connection']        = 'Ozon API bağlantısı başarısız!';
$_['error_api_timeout']           = 'API isteği zaman aşımına uğradı!';
$_['error_api_rate_limit']        = 'API rate limit aşıldı. Lütfen bekleyin.';
$_['error_product_not_found']     = 'Ürün bulunamadı!';
$_['error_invalid_warehouse']     = 'Geçersiz depo ID!';
$_['error_fbo_disabled']          = 'FBO hizmeti etkin değil!';
$_['error_sync_failed']           = 'Senkronizasyon başarısız!';
$_['error_no_products']           = 'Senkronize edilecek ürün bulunamadı!';
$_['error_invalid_price']         = 'Geçersiz fiyat formatı!';
$_['error_insufficient_stock']    = 'Yetersiz stok!';

// Column Headers
$_['column_product']              = 'Ürün';
$_['column_sku']                  = 'SKU';
$_['column_status']               = 'Durum';
$_['column_ozon_id']              = 'Ozon ID';
$_['column_price']                = 'Fiyat';
$_['column_stock']                = 'Stok';
$_['column_last_sync']            = 'Son Senkronizasyon';
$_['column_action']               = 'İşlem';
$_['column_order_id']             = 'Sipariş ID';
$_['column_customer']             = 'Müşteri';
$_['column_total']                = 'Toplam';
$_['column_date']                 = 'Tarih';

// Status Options
$_['text_status_pending']         = 'Beklemede';
$_['text_status_synced']          = 'Senkronize';
$_['text_status_error']           = 'Hata';
$_['text_status_blocked']         = 'Engellendi';

$_['text_fbo_none']               = 'FBO Yok';
$_['text_fbo_pending']            = 'FBO Beklemede';
$_['text_fbo_uploaded']           = 'FBO Yüklendi';
$_['text_fbo_active']             = 'FBO Aktif';
$_['text_fbo_inactive']           = 'FBO Pasif';

// Warehouse Names
$_['text_warehouse_moscow']       = 'Moskova FBO';
$_['text_warehouse_spb']          = 'St. Petersburg FBO';
$_['text_warehouse_ekb']          = 'Ekaterinburg FBO';
$_['text_warehouse_kazan']        = 'Kazan FBO';
$_['text_warehouse_novosibirsk']  = 'Novosibirsk FBO';

// Dashboard
$_['text_dashboard']              = 'Ozon Dashboard';
$_['text_total_products']         = 'Toplam Ürün';
$_['text_synced_products']        = 'Senkronize Ürün';
$_['text_pending_products']       = 'Bekleyen Ürün';
$_['text_monthly_orders']         = 'Aylık Sipariş';
$_['text_monthly_revenue']        = 'Aylık Gelir';
$_['text_api_status']             = 'API Durumu';
$_['text_last_sync']              = 'Son Senkronizasyon';
$_['text_fbo_warehouses']         = 'FBO Depoları';

// Notifications
$_['text_sync_started']           = 'Senkronizasyon başlatıldı...';
$_['text_sync_completed']         = 'Senkronizasyon tamamlandı!';
$_['text_price_update_started']   = 'Fiyat güncelleme başlatıldı...';
$_['text_price_update_completed'] = 'Fiyat güncelleme tamamlandı!';
$_['text_fbo_upload_started']     = 'FBO yükleme başlatıldı...';
$_['text_fbo_upload_completed']   = 'FBO yükleme tamamlandı!';

// Russian Market Specific
$_['text_rub_currency']           = '₽ (Ruble)';
$_['text_vat_included']           = 'KDV Dahil';
$_['text_delivery_russia']        = 'Rusya\'ya Teslimat';
$_['text_moscow_time']            = 'Moskova Saati';
$_['text_business_hours']         = 'İş Saatleri (09:00-18:00 MSK)';
$_['text_premium_price']          = 'Ozon Premium Fiyat';
$_['text_marketing_price']        = 'Pazarlama Fiyatı';

// Log Messages
$_['text_log_sync_product']       = 'Ürün senkronizasyonu';
$_['text_log_price_update']       = 'Fiyat güncelleme';
$_['text_log_stock_update']       = 'Stok güncelleme';
$_['text_log_order_sync']         = 'Sipariş senkronizasyonu';
$_['text_log_fbo_upload']         = 'FBO yükleme';
$_['text_log_api_call']           = 'API çağrısı';
$_['text_log_error']              = 'Hata';
$_['text_log_success']            = 'Başarılı';

// Tooltips
$_['tooltip_sync_all']            = 'Tüm ürünleri Ozon\'a senkronize et';
$_['tooltip_update_prices']       = 'Değişen fiyatları Ozon\'da güncelle';
$_['tooltip_fbo_upload']          = 'Seçili ürünleri FBO deposuna yükle';
$_['tooltip_test_api']            = 'API bağlantısını ve kimlik bilgilerini test et';
$_['tooltip_clear_logs']          = 'Tüm senkronizasyon loglarını temizle';

// Form Validation
$_['validation_client_id_format'] = 'Client ID sayısal olmalıdır!';
$_['validation_api_key_length']   = 'API Key en az 32 karakter olmalıdır!';
$_['validation_warehouse_id']     = 'Depo ID geçerli bir format olmalıdır!';
$_['validation_sync_interval']    = 'Senkronizasyon aralığı 15-1440 dakika arasında olmalıdır!';
$_['validation_price_markup']     = 'Fiyat markup 0-100 arasında olmalıdır!';

// Integration Info
$_['text_module_version']         = 'MesChain-Sync v3.0';
$_['text_ozon_api_version']       = 'Ozon Seller API v3';
$_['text_supported_features']     = 'Desteklenen Özellikler';
$_['text_fbo_support']            = '✓ FBO Depo Yönetimi';
$_['text_realtime_sync']          = '✓ Gerçek Zamanlı Senkronizasyon';
$_['text_price_optimization']     = '✓ Rus Pazarı Fiyat Optimizasyonu';
$_['text_auto_categories']        = '✓ Otomatik Kategori Eşleştirme';
$_['text_order_management']       = '✓ Sipariş Yönetimi';
$_['text_performance_monitoring'] = '✓ Performans İzleme';
?> 