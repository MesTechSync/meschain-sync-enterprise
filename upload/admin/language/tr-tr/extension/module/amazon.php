<?php
/**
 * Amazon modülü Türkçe dil dosyası
 * 
 * @author MesChain-Sync
 * @version 1.0.1
 */

// Heading
$_['heading_title']                    = 'Amazon SP-API Entegrasyonu';

// Text
$_['text_extension']                   = 'Eklentiler';
$_['text_success']                     = 'Başarılı: Amazon modülü ayarları güncellendi!';
$_['text_edit']                        = 'Amazon Modülünü Düzenle';
$_['text_enabled']                     = 'Etkin';
$_['text_disabled']                    = 'Devre Dışı';
$_['text_api_settings']                = 'API Ayarları';
$_['text_api_info']                    = 'Amazon SP-API Bilgileri';
$_['text_api_info_desc']               = 'Amazon SP-API entegrasyonu için aşağıdaki bilgilere ihtiyacınız var:';
$_['text_api_step1']                   = '1. Amazon Seller Central hesabınıza giriş yapın';
$_['text_api_step2']                   = '2. "Partner Network" > "Develop Apps" bölümüne gidin';
$_['text_api_step3']                   = '3. Yeni bir uygulama oluşturun ve LWA (Login with Amazon) bilgilerini alın';
$_['text_api_step4']                   = '4. Refresh Token için yetkilendirme işlemini tamamlayın';
$_['text_api_step5']                   = '5. Marketplace ID\'nizi ve Seller ID\'nizi kontrol edin';
$_['text_api_docs']                    = 'Detaylı bilgi için Amazon SP-API dokümantasyonunu inceleyin.';
$_['text_connection_help']             = 'API bilgilerinizi girdikten sonra "Bağlantıyı Test Et" butonuna tıklayın.';
$_['text_status']                      = 'Durum';
$_['text_test_connection']             = 'Bağlantıyı Test Et';
$_['text_dashboard']                   = 'Dashboard';
$_['text_orders_imported']             = '%d yeni, %d güncellenen sipariş başarıyla içe aktarıldı!';
$_['text_products_synced']             = '%d ürün başarıyla senkronize edildi!';
$_['text_sync_errors']                 = '%d ürün senkronize edilemedi';
$_['text_stock_updated']               = '%d ürün stoku başarıyla güncellendi!';
$_['text_order_converted']             = 'Sipariş başarıyla OpenCart\'a dönüştürüldü!';

// Entry
$_['entry_lwa_client_id']              = 'LWA Client ID';
$_['entry_lwa_client_secret']          = 'LWA Client Secret';
$_['entry_lwa_refresh_token']          = 'LWA Refresh Token';
$_['entry_seller_id']                  = 'Seller ID';
$_['entry_marketplace_id']             = 'Marketplace ID';
$_['entry_region']                     = 'Bölge';
$_['entry_status']                     = 'Durum';
$_['entry_debug']                      = 'Debug Modu';
$_['entry_marketplace']                = 'Pazaryeri';

// Button
$_['button_save']                      = 'Kaydet';
$_['button_cancel']                    = 'İptal';
$_['button_test']                      = 'Test Et';
$_['button_sync_orders']               = 'Siparişleri Çek';
$_['button_sync_products']             = 'Ürünleri Senkronize Et';
$_['button_update_stock']              = 'Stokları Güncelle';
$_['button_update_prices']             = 'Fiyatları Güncelle';
$_['button_convert_order']             = 'Siparişi Dönüştür';

// Error
$_['error_permission']                 = 'Uyarı: Amazon modülünü değiştirme yetkiniz yok!';
$_['error_lwa_client_id']              = 'LWA Client ID gereklidir!';
$_['error_lwa_client_secret']          = 'LWA Client Secret gereklidir!';
$_['error_lwa_refresh_token']          = 'LWA Refresh Token gereklidir!';
$_['error_seller_id']                  = 'Seller ID gereklidir!';
$_['error_marketplace_id']             = 'Marketplace ID gereklidir!';
$_['error_connection']                 = 'Amazon SP-API bağlantısı başarısız. Lütfen ayarlarınızı kontrol edin.';
$_['error_api_response']               = 'Amazon API\'den geçersiz yanıt alındı.';
$_['error_rate_limit']                 = 'Amazon API rate limit aşıldı. Lütfen daha sonra tekrar deneyin.';
$_['error_order_not_found']            = 'Amazon siparişi bulunamadı.';
$_['error_product_not_found']          = 'Ürün bulunamadı.';

// Help
$_['help_lwa_client_id']               = 'Amazon Developer Portal\'dan alınan LWA (Login with Amazon) Client ID';
$_['help_lwa_client_secret']           = 'Amazon Developer Portal\'dan alınan LWA (Login with Amazon) Client Secret';
$_['help_lwa_refresh_token']           = 'SP-API yetkilendirme işlemi sonucu alınan Refresh Token';
$_['help_seller_id']                   = 'Amazon Seller Central\'dan alınan Seller ID (Merchant ID)';
$_['help_marketplace_id']              = 'Satış yapılan Amazon pazaryerinin Marketplace ID\'si';
$_['help_region']                      = 'Amazon SP-API bölgesi (EU, NA, FE)';
$_['help_debug']                       = 'Debug modu etkinleştirildiğinde API istekleri detaylı olarak loglanır';

// Info
$_['info_marketplace_ids']             = 'Yaygın Marketplace ID\'ler:<br/>
                                          • Türkiye: A33AVAJ2PDY3EV<br/>
                                          • Almanya: A1PA6795UKMFR9<br/>
                                          • İngiltere: A1F83G8C2ARO7P<br/>
                                          • Fransa: A13V1IB3VIYZZH<br/>
                                          • İtalya: APJ6JRA9NG5V4<br/>
                                          • İspanya: A1RKKUPIHCS9HS<br/>
                                          • Hollanda: A1805IZSGTT6HS';

// Tab
$_['tab_general']                      = 'Genel';
$_['tab_api']                          = 'API Ayarları';
$_['tab_sync']                         = 'Senkronizasyon';
$_['tab_orders']                       = 'Siparişler';
$_['tab_products']                     = 'Ürünler';
$_['tab_fulfillment']                  = 'Kargolama';
$_['tab_advertising']                  = 'Reklam';
$_['tab_logs']                         = 'Günlükler';
$_['tab_help']                         = 'Yardım';

// Column
$_['column_amazon_order_id']           = 'Amazon Sipariş ID';
$_['column_purchase_date']             = 'Satın Alma Tarihi';
$_['column_order_status']              = 'Sipariş Durumu';
$_['column_buyer_name']                = 'Alıcı Adı';
$_['column_order_total']               = 'Toplam';
$_['column_items']                     = 'Kalem Sayısı';
$_['column_opencart_order_id']         = 'OpenCart Sipariş ID';
$_['column_action']                    = 'İşlem';
$_['column_amazon_sku']                = 'Amazon SKU';
$_['column_product_name']              = 'Ürün Adı';
$_['column_sync_status']               = 'Senkronizasyon Durumu';
$_['column_last_sync']                 = 'Son Senkronizasyon';
$_['column_amazon_price']              = 'Amazon Fiyatı';
$_['column_amazon_quantity']           = 'Amazon Stok';

// Dashboard
$_['dashboard_title']                  = 'Amazon Dashboard';
$_['dashboard_orders_today']           = 'Bugünkü Siparişler';
$_['dashboard_orders_month']           = 'Bu Ayki Siparişler';
$_['dashboard_sales_today']            = 'Bugünkü Satışlar';
$_['dashboard_sales_month']            = 'Bu Ayki Satışlar';
$_['dashboard_products_active']        = 'Aktif Ürünler';
$_['dashboard_products_inactive']      = 'Pasif Ürünler';
$_['dashboard_stock_in']               = 'Stokta Var';
$_['dashboard_stock_out']              = 'Stokta Yok';
$_['dashboard_recent_orders']          = 'Son Siparişler';
$_['dashboard_sync_status']            = 'Senkronizasyon Durumu';
$_['dashboard_api_status']             = 'API Durumu';
$_['dashboard_last_sync']              = 'Son Senkronizasyon';

// Status
$_['status_pending']                   = 'Beklemede';
$_['status_synced']                    = 'Senkronize';
$_['status_error']                     = 'Hata';
$_['status_disabled']                  = 'Devre Dışı';
$_['status_unshipped']                 = 'Kargoya Verilmemiş';
$_['status_shipped']                   = 'Kargoya Verilmiş';
$_['status_delivered']                 = 'Teslim Edilmiş';
$_['status_canceled']                  = 'İptal Edilmiş';

// Region Options
$_['region_eu']                        = 'Avrupa (EU)';
$_['region_na']                        = 'Kuzey Amerika (NA)';
$_['region_fe']                        = 'Uzak Doğu (FE)';

// Marketplace
$_['marketplace_us']                   = 'Amerika Birleşik Devletleri';
$_['marketplace_ca']                   = 'Kanada';
$_['marketplace_mx']                   = 'Meksika';
$_['marketplace_br']                   = 'Brezilya';

// Success messages
$_['text_connection_success']          = 'Amazon SP-API bağlantısı başarılı!';
$_['text_orders_synced']               = '%d sipariş senkronize edildi';
$_['text_inventory_updated']           = 'Stok başarıyla güncellendi';
$_['text_prices_updated']              = 'Fiyatlar başarıyla güncellendi';

// Dashboard Stats
$_['stat_total_products']              = 'Toplam Ürünler';
$_['stat_total_orders']                = 'Toplam Siparişler';
$_['stat_total_revenue']               = 'Toplam Gelir'; 