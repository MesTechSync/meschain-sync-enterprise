<?php
/**
 * MesChain-Sync Turkish Language File
 * 
 * @category   Language
 * @package    MesChain-Sync
 * @version    2.5.0
 * @author     MesTech Team
 * @license    Commercial License
 */

// Heading
$_['heading_title']    = 'MesChain-Sync';

// Text
$_['text_extension']   = 'Eklentiler';
$_['text_success']     = 'Başarılı: MesChain-Sync modülü güncellendi!';
$_['text_edit']        = 'MesChain-Sync Modülünü Düzenle';
$_['text_enabled']     = 'Etkin';
$_['text_disabled']    = 'Devre Dışı';
$_['text_amazon']       = 'Amazon';
$_['text_ebay']         = 'eBay';
$_['text_hepsiburada']  = 'Hepsiburada';
$_['text_n11']          = 'N11';
$_['text_ozon']         = 'Ozon';
$_['text_trendyol']     = 'Trendyol';

// Tab
$_['tab_general']      = 'Genel Ayarlar';
$_['tab_amazon']         = 'Amazon SP-API';
$_['tab_ebay']           = 'eBay REST API';
$_['tab_hepsiburada']    = 'Hepsiburada API';
$_['tab_n11']            = 'N11 SOAP API';
$_['tab_ozon']           = 'Ozon REST API';
$_['tab_trendyol']       = 'Trendyol API';
$_['tab_logs']           = 'Loglar ve Raporlar';

// Entry
$_['entry_status']      = 'Durum';
$_['entry_debug']        = 'Debug Modu';
$_['entry_interval']     = 'Senkronizasyon Aralığı';
$_['entry_api_key']      = 'API Anahtarı';
$_['entry_api_secret']   = 'API Gizli Anahtarı';
$_['entry_client_id']    = 'İstemci ID';
$_['entry_client_secret']= 'İstemci Gizli Anahtarı';
$_['entry_access_token'] = 'Erişim Token';
$_['entry_refresh_token']= 'Yenileme Token';
$_['entry_seller_id']    = 'Satıcı ID';
$_['entry_marketplace']  = 'Pazaryeri';
$_['entry_sandbox']      = 'Test Modu (Sandbox)';
$_['entry_default_category'] = 'Varsayılan Kategori';
$_['entry_shipping_time']= 'Kargo Süresi (gün)';
$_['entry_price_increase'] = 'Fiyat Artış Oranı (%)';

// Button
$_['button_save']        = 'Kaydet';
$_['button_cancel']      = 'İptal';
$_['button_test_connection'] = 'Bağlantıyı Test Et';
$_['button_sync_products'] = 'Ürünleri Senkronize Et';
$_['button_sync_orders']  = 'Siparişleri Senkronize Et';
$_['button_sync_inventory'] = 'Stok Senkronize Et';
$_['button_clear_logs']   = 'Logları Temizle';
$_['button_export_logs']  = 'Logları Dışa Aktar';

// Help
$_['help_status']        = 'Modülü etkinleştir/devre dışı bırak';
$_['help_debug']          = 'Debug modunda ayrıntılı log tutulur';
$_['help_interval']       = 'Otomatik senkronizasyon için bekleme süresi';
$_['help_sandbox']         = 'Test ortamını kullan (gerçek işlemler yapılmaz)';
$_['help_price_increase']  = 'Pazaryerine gönderilen fiyatlara uygulanacak artış oranı';

// Success
$_['success_connection'] = 'API bağlantısı başarılı';
$_['success_sync_products'] = 'Ürün senkronizasyonu başarıyla tamamlandı!';
$_['success_sync_orders']   = 'Sipariş senkronizasyonu başarıyla tamamlandı!';
$_['success_sync_inventory']= 'Stok senkronizasyonu başarıyla tamamlandı!';
$_['success_clear_logs']    = 'Loglar başarıyla temizlendi!';

// Error
$_['error_permission']    = 'Uyarı: MesChain-Sync modülünü değiştirme yetkiniz yok!';
$_['error_warning']        = 'Hatalar için formu dikkatli kontrol edin!';
$_['error_api_key']         = 'API Anahtarı gerekli!';
$_['error_api_secret']      = 'API Gizli Anahtarı gerekli!';
$_['error_client_id']       = 'İstemci ID gerekli!';
$_['error_client_secret']   = 'İstemci Gizli Anahtarı gerekli!';
$_['error_connection']      = 'API bağlantısı başarısız: ';
$_['error_sync']            = 'Senkronizasyon hatası: ';

// Info
$_['info_total_products']   = 'Toplam Ürün';
$_['info_synced_products']  = 'Senkronize Edilen Ürün';
$_['info_total_orders']     = 'Toplam Sipariş';
$_['info_recent_syncs']     = 'Son 24 Saat Senkronizasyon';
$_['info_success_rate']     = 'Başarı Oranı';
$_['info_last_sync']        = 'Son Senkronizasyon';

// Column
$_['column_marketplace']    = 'Pazaryeri';
$_['column_operation']        = 'İşlem';
$_['column_product']          = 'Ürün';
$_['column_status']           = 'Durum';
$_['column_message']          = 'Mesaj';
$_['column_date']             = 'Tarih';
$_['column_action']           = 'Eylem';

// Status
$_['status_connected']        = 'Bağlandı';
$_['status_disconnected']      = 'Bağlantı Kesildi';
$_['status_error']             = 'Hata';
$_['status_success']           = 'Başarılı';
$_['status_pending']           = 'Beklemede';
$_['status_syncing']           = 'Senkronize Ediliyor';

// Marketplace specific
$_['amazon_marketplace_us']    = 'Amerika Birleşik Devletleri';
$_['amazon_marketplace_ca']      = 'Kanada';
$_['amazon_marketplace_mx']      = 'Meksika';
$_['amazon_marketplace_br']      = 'Brezilya';
$_['amazon_marketplace_uk']      = 'Birleşik Krallık';
$_['amazon_marketplace_de']      = 'Almanya';
$_['amazon_marketplace_fr']      = 'Fransa';
$_['amazon_marketplace_it']      = 'İtalya';
$_['amazon_marketplace_es']      = 'İspanya';
$_['amazon_marketplace_jp']      = 'Japonya';
$_['amazon_marketplace_au']      = 'Avustralya';

$_['ebay_marketplace_us']        = 'eBay Amerika';
$_['ebay_marketplace_uk']        = 'eBay İngiltere';
$_['ebay_marketplace_de']        = 'eBay Almanya';
$_['ebay_marketplace_fr']        = 'eBay Fransa';
$_['ebay_marketplace_it']        = 'eBay İtalya';
$_['ebay_marketplace_es']        = 'eBay İspanya';
$_['ebay_marketplace_ca']        = 'eBay Kanada';
$_['ebay_marketplace_au']        = 'eBay Avustralya'; 