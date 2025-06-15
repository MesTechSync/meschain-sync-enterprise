<?php
/**
 * eBay Modülü - Türkçe Dil Dosyası
 * 
 * @author MesChain Development Team
 * @version 2.0.0
 * @since 2024-01-21
 */

// Heading
$_['heading_title']         = 'eBay Entegrasyonu';

// Text
$_['text_extension']        = 'Uzantılar';
$_['text_success']          = 'Başarılı: eBay modülü güncellendi!';
$_['text_edit']            = 'eBay Modülünü Düzenle';
$_['text_enabled']         = 'Etkin';
$_['text_disabled']        = 'Devre Dışı';
$_['text_loading']         = 'Yükleniyor...';
$_['text_syncing']         = 'Senkronize ediliyor...';
$_['text_all']             = 'Tümü';
$_['text_none']            = 'Hiçbiri';
$_['text_select']          = 'Seçin';
$_['text_yes']             = 'Evet';
$_['text_no']              = 'Hayır';

// Tab
$_['tab_general']          = 'Genel';
$_['tab_api']              = 'API Ayarları';
$_['tab_products']         = 'Ürün Yönetimi';
$_['tab_orders']           = 'Sipariş Yönetimi';
$_['tab_logs']             = 'Günlük Kayıtları';
$_['tab_help']             = 'Yardım';

// Entry
$_['entry_status']         = 'Durum';
$_['entry_client_id']      = 'Client ID';
$_['entry_client_secret']  = 'Client Secret';
$_['entry_refresh_token']  = 'Refresh Token';
$_['entry_redirect_uri']   = 'Redirect URI';
$_['entry_marketplace']    = 'Pazaryeri';
$_['entry_sandbox_mode']   = 'Test Modu';
$_['entry_auto_sync']      = 'Otomatik Senkronizasyon';
$_['entry_sync_interval']  = 'Senkronizasyon Aralığı';
$_['entry_default_condition'] = 'Varsayılan Durum';
$_['entry_default_format'] = 'Varsayılan Format';
$_['entry_default_duration'] = 'Varsayılan Süre';

// Button
$_['button_save']          = 'Kaydet';
$_['button_cancel']        = 'İptal';
$_['button_test_connection'] = 'Bağlantıyı Test Et';
$_['button_sync_products'] = 'Ürünleri Senkronize Et';
$_['button_sync_orders']   = 'Siparişleri Senkronize Et';
$_['button_sync_categories'] = 'Kategorileri Senkronize Et';
$_['button_clear_logs']    = 'Günlükleri Temizle';
$_['button_export_products'] = 'Ürünleri Dışa Aktar';
$_['button_import_orders'] = 'Siparişleri İçe Aktar';

// Help
$_['help_client_id']       = 'eBay Developer hesabınızdan aldığınız Client ID\'yi girin.';
$_['help_client_secret']   = 'eBay Developer hesabınızdan aldığınız Client Secret\'ı girin.';
$_['help_refresh_token']   = 'OAuth sürecinden aldığınız Refresh Token\'ı girin.';
$_['help_redirect_uri']    = 'OAuth için belirlediğiniz Redirect URI\'yi girin.';
$_['help_marketplace']     = 'Satış yapacağınız eBay pazaryerini seçin.';
$_['help_sandbox_mode']    = 'Test için sandbox modunu etkinleştirin.';
$_['help_auto_sync']       = 'Otomatik senkronizasyonu etkinleştirin.';
$_['help_sync_interval']   = 'Dakika cinsinden senkronizasyon aralığını girin.';

// Column
$_['column_product_name']  = 'Ürün Adı';
$_['column_sku']           = 'SKU';
$_['column_ebay_item_id']  = 'eBay Item ID';
$_['column_status']        = 'Durum';
$_['column_last_sync']     = 'Son Senkronizasyon';
$_['column_price']         = 'Fiyat';
$_['column_quantity']      = 'Stok';
$_['column_action']        = 'İşlem';

$_['column_order_id']      = 'Sipariş ID';
$_['column_ebay_order_id'] = 'eBay Sipariş ID';
$_['column_buyer']         = 'Alıcı';
$_['column_total']         = 'Toplam';
$_['column_date_added']    = 'Tarih';
$_['column_order_status']  = 'Sipariş Durumu';

// Status
$_['status_active']        = 'Aktif';
$_['status_ended']         = 'Sona Erdi';
$_['status_out_of_stock']  = 'Stokta Yok';
$_['status_hidden']        = 'Gizli';
$_['status_draft']         = 'Taslak';

// Marketplace
$_['marketplace_us']       = 'Amerika Birleşik Devletleri';
$_['marketplace_uk']       = 'Birleşik Krallık';
$_['marketplace_de']       = 'Almanya';
$_['marketplace_fr']       = 'Fransa';
$_['marketplace_it']       = 'İtalya';
$_['marketplace_es']       = 'İspanya';
$_['marketplace_au']       = 'Avustralya';
$_['marketplace_ca']       = 'Kanada';

// Condition
$_['condition_new']        = 'Yeni';
$_['condition_new_other']  = 'Yeni (Diğer)';
$_['condition_manufacturer_refurbished'] = 'Üretici Yenilenmiş';
$_['condition_seller_refurbished'] = 'Satıcı Yenilenmiş';
$_['condition_used']       = 'Kullanılmış';
$_['condition_for_parts']  = 'Parça İçin';

// Format
$_['format_fixed_price']   = 'Sabit Fiyat';
$_['format_auction']       = 'Açık Artırma';

// Duration
$_['duration_1_day']       = '1 Gün';
$_['duration_3_days']      = '3 Gün';
$_['duration_5_days']      = '5 Gün';
$_['duration_7_days']      = '7 Gün';
$_['duration_10_days']     = '10 Gün';
$_['duration_30_days']     = '30 Gün';
$_['duration_gtc']         = 'Bitene Kadar (GTC)';

// Success Messages
$_['success_connection']   = 'eBay API bağlantısı başarılı!';
$_['success_products_sync'] = 'Ürünler başarıyla senkronize edildi!';
$_['success_orders_sync']  = 'Siparişler başarıyla senkronize edildi!';
$_['success_categories_sync'] = 'Kategoriler başarıyla senkronize edildi!';
$_['success_logs_cleared'] = 'Günlük kayıtları temizlendi!';

// Error Messages
$_['error_permission']     = 'Uyarı: eBay modülünü değiştirme izniniz yok!';
$_['error_client_id']      = 'Client ID gerekli!';
$_['error_client_secret']  = 'Client Secret gerekli!';
$_['error_refresh_token']  = 'Refresh Token gerekli!';
$_['error_connection']     = 'eBay API bağlantısı başarısız!';
$_['error_sync_products']  = 'Ürün senkronizasyonu başarısız!';
$_['error_sync_orders']    = 'Sipariş senkronizasyonu başarısız!';
$_['error_api_limit']      = 'API istek limiti aşıldı. Lütfen daha sonra tekrar deneyin.';
$_['error_invalid_token']  = 'Geçersiz veya süresi dolmuş token. Lütfen yeniden yetkilendirin.';

// Info Messages
$_['info_no_products']     = 'Senkronize edilecek ürün bulunamadı.';
$_['info_no_orders']       = 'Senkronize edilecek sipariş bulunamadı.';
$_['info_sync_in_progress'] = 'Senkronizasyon devam ediyor...';
$_['info_rate_limit']      = 'Rate limit aşıldı. Senkronizasyon otomatik olarak yavaşlatıldı.';

// Statistics
$_['stat_total_products']  = 'Toplam Ürün';
$_['stat_synced_products'] = 'Senkronize Ürün';
$_['stat_total_orders']    = 'Toplam Sipariş';
$_['stat_new_orders']      = 'Yeni Sipariş';
$_['stat_total_revenue']   = 'Toplam Gelir';
$_['stat_commission']      = 'Komisyon';

// Dashboard
$_['dashboard_title']      = 'eBay Dashboard';
$_['dashboard_api_status'] = 'API Durumu';
$_['dashboard_last_sync']  = 'Son Senkronizasyon';
$_['dashboard_products']   = 'Ürünler';
$_['dashboard_orders']     = 'Siparişler';
$_['dashboard_revenue']    = 'Gelir';

// Settings
$_['settings_title']       = 'eBay Ayarları';
$_['settings_api']         = 'API Ayarları';
$_['settings_sync']        = 'Senkronizasyon Ayarları';
$_['settings_listing']     = 'Listeleme Ayarları';
$_['settings_order']       = 'Sipariş Ayarları';

// Log Types
$_['log_info']             = 'Bilgi';
$_['log_warning']          = 'Uyarı';
$_['log_error']            = 'Hata';
$_['log_success']          = 'Başarılı';

// Sync Status
$_['sync_pending']         = 'Bekliyor';
$_['sync_synced']          = 'Senkronize';
$_['sync_error']           = 'Hata';
$_['sync_processing']      = 'İşleniyor';

// Navigation
$_['nav_dashboard']        = 'Dashboard';
$_['nav_products']         = 'Ürünler';
$_['nav_orders']           = 'Siparişler';
$_['nav_settings']         = 'Ayarlar';
$_['nav_logs']             = 'Günlükler';

// Filters
$_['filter_status']        = 'Durum Filtresi';
$_['filter_marketplace']   = 'Pazaryeri Filtresi';
$_['filter_date_from']     = 'Başlangıç Tarihi';
$_['filter_date_to']       = 'Bitiş Tarihi';
$_['filter_search']        = 'Arama';

// Actions
$_['action_sync']          = 'Senkronize Et';
$_['action_edit']          = 'Düzenle';
$_['action_delete']        = 'Sil';
$_['action_view']          = 'Görüntüle';
$_['action_enable']        = 'Etkinleştir';
$_['action_disable']       = 'Devre Dışı Bırak';

// Tooltips
$_['tooltip_client_id']    = 'eBay geliştirici konsolundan alınan Client ID';
$_['tooltip_marketplace']  = 'Ürünlerinizin satılacağı eBay pazaryeri';
$_['tooltip_condition']    = 'Ürünün durumu (Yeni, Kullanılmış vb.)';
$_['tooltip_format']       = 'Listeleme formatı (Sabit Fiyat veya Açık Artırma)';
$_['tooltip_duration']     = 'Listelemenin aktif kalacağı süre';
?> 