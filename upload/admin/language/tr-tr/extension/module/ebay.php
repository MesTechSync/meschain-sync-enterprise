<?php
/**
 * eBay Language File - Turkish
 * MesChain-Sync v3.0 - OpenCart 3.0.4.0 Integration
 * 
 * @author MesChain Development Team
 * @version 3.0.0
 * @copyright 2024 MesChain Technologies
 */

// Heading
$_['heading_title']                = 'eBay Marketplace';

// Text
$_['text_extension']              = 'Entegrasyonlar';
$_['text_success']                = 'Başarılı: eBay modülü güncellendi!';
$_['text_edit']                   = 'eBay Modülünü Düzenle';
$_['text_enabled']                = 'Etkin';
$_['text_disabled']               = 'Pasif';
$_['text_yes']                    = 'Evet';
$_['text_no']                     = 'Hayır';
$_['text_loading']                = 'Yükleniyor...';
$_['text_select']                 = 'Seçiniz';

// Tab
$_['tab_general']                 = 'Genel Ayarlar';
$_['tab_api']                     = 'API Ayarları';
$_['tab_listing']                 = 'Listeleme Ayarları';
$_['tab_shipping']                = 'Kargo & Uluslararası';
$_['tab_payment']                 = 'Ödeme Yöntemleri';
$_['tab_feedback']                = 'Değerlendirme Yönetimi';
$_['tab_promotion']               = 'Promoted Listings';
$_['tab_categories']              = 'Kategoriler';
$_['tab_orders']                  = 'Siparişler';
$_['tab_logs']                    = 'Loglar';

// Entry
$_['entry_status']                = 'Durum';
$_['entry_app_id']                = 'App ID (Client ID)';
$_['entry_cert_id']               = 'Cert ID (Client Secret)';
$_['entry_dev_id']                = 'Dev ID';
$_['entry_user_token']            = 'User Token';
$_['entry_sandbox']               = 'Sandbox Modu';
$_['entry_site']                  = 'eBay Sitesi';
$_['entry_listing_type']          = 'Listeleme Türü';
$_['entry_auto_paypal']           = 'Otomatik PayPal';
$_['entry_global_shipping']       = 'Global Shipping Program';
$_['entry_best_offer']            = 'Best Offer Etkin';
$_['entry_auto_feedback']         = 'Otomatik Feedback';
$_['entry_promotion_budget']      = 'Promoted Listings Bütçesi';
$_['entry_price_markup']          = 'Fiyat Markup (%)';
$_['entry_condition']             = 'Ürün Durumu';
$_['entry_return_policy']         = 'İade Politikası';
$_['entry_dispatch_time']         = 'Gönderim Süresi (gün)';

// Help
$_['help_app_id']                 = 'eBay Developer hesabınızdan App ID (Client ID) girin.';
$_['help_cert_id']                = 'eBay Developer hesabınızdan Cert ID (Client Secret) girin.';
$_['help_dev_id']                 = 'eBay Developer hesabınızdan Dev ID girin.';
$_['help_user_token']             = 'eBay kullanıcı token\'ınızı girin.';
$_['help_sandbox']                = 'Geliştirme ve test için sandbox modunu kullanın.';
$_['help_site']                   = 'Ürünlerinizi listelemek istediğiniz eBay sitesini seçin.';
$_['help_listing_type']           = 'Buy It Now (Hemen Al) veya Auction (Açık Artırma) seçin.';
$_['help_global_shipping']        = 'eBay Global Shipping Program ile uluslararası gönderimleri etkinleştirin.';
$_['help_best_offer']             = 'Alıcıların teklif vermesine izin verin.';
$_['help_auto_feedback']          = 'Başarılı işlemler için otomatik pozitif feedback bırakın.';
$_['help_promotion_budget']       = 'Promoted Listings için günlük bütçe (USD).';
$_['help_price_markup']           = 'eBay\'e gönderilecek fiyatlara eklenecek markup yüzdesi.';
$_['help_dispatch_time']          = 'Ürünleri kargoya verme süreniz (1-5 gün arası).';

// Button
$_['button_save']                 = 'Kaydet';
$_['button_cancel']               = 'İptal';
$_['button_test_connection']      = 'Bağlantıyı Test Et';
$_['button_list_products']        = 'Ürünleri Listele';
$_['button_update_listings']      = 'Listeleri Güncelle';
$_['button_sync_orders']          = 'Siparişleri Senkronize Et';
$_['button_manage_promotions']    = 'Promoted Listings Yönet';
$_['button_manage_feedback']      = 'Feedback Yönet';
$_['button_end_listings']         = 'Listeleri Sonlandır';
$_['button_get_categories']       = 'Kategorileri Çek';

// Success Messages
$_['text_api_connected']          = 'eBay API bağlantısı başarılı!';
$_['text_listing_success']        = '%d ürün başarıyla eBay\'e listelendi!';
$_['text_update_success']         = '%d liste başarıyla güncellendi!';
$_['text_order_sync_success']     = '%d sipariş başarıyla senkronize edildi!';
$_['text_promotion_success']      = '%d liste promoted olarak işaretlendi!';
$_['text_feedback_success']       = '%d feedback başarıyla bırakıldı!';
$_['text_end_success']            = '%d liste başarıyla sonlandırıldı!';

// Error Messages
$_['error_permission']            = 'Uyarı: Bu modülü değiştirme izniniz yok!';
$_['error_app_id']                = 'App ID gerekli!';
$_['error_cert_id']               = 'Cert ID gerekli!';
$_['error_dev_id']                = 'Dev ID gerekli!';
$_['error_user_token']            = 'User Token gerekli!';
$_['error_api_credentials']       = 'eBay API kimlik bilgileri ayarlanmamış!';
$_['error_api_connection']        = 'eBay API bağlantısı başarısız!';
$_['error_api_timeout']           = 'API isteği zaman aşımına uğradı!';
$_['error_api_rate_limit']        = 'API rate limit aşıldı. Lütfen bekleyin.';
$_['error_listing_failed']        = 'Ürün listeleme başarısız!';
$_['error_no_products']           = 'Listelenecek ürün bulunamadı!';
$_['error_invalid_category']      = 'Geçersiz kategori!';
$_['error_invalid_price']         = 'Geçersiz fiyat formatı!';
$_['error_insufficient_stock']    = 'Yetersiz stok!';
$_['error_promotion_failed']      = 'Promoted listing oluşturulamadı!';
$_['error_feedback_failed']       = 'Feedback bırakılamadı!';
$_['error_unauthorized']          = 'eBay API yetkilendirmesi başarısız!';

// Column Headers
$_['column_item_id']              = 'eBay Item ID';
$_['column_title']                = 'Başlık';
$_['column_sku']                  = 'SKU';
$_['column_listing_type']         = 'Listeleme Türü';
$_['column_price']                = 'Fiyat';
$_['column_quantity']             = 'Miktar';
$_['column_watch_count']          = 'İzleyen Sayısı';
$_['column_hit_count']            = 'Görüntülenme';
$_['column_status']               = 'Durum';
$_['column_start_time']           = 'Başlangıç Zamanı';
$_['column_end_time']             = 'Bitiş Zamanı';
$_['column_site']                 = 'eBay Sitesi';
$_['column_currency']             = 'Para Birimi';
$_['column_promoted']             = 'Promoted';
$_['column_international']        = 'Uluslararası';
$_['column_action']               = 'İşlem';
$_['column_order_id']             = 'Sipariş ID';
$_['column_buyer']                = 'Alıcı';
$_['column_payment_status']       = 'Ödeme Durumu';
$_['column_shipping_status']      = 'Kargo Durumu';
$_['column_feedback_score']       = 'Feedback Puanı';
$_['column_feedback_percentage']  = 'Pozitif %';

// Listing Types
$_['text_listing_fixed_price']    = 'Buy It Now (Hemen Al)';
$_['text_listing_auction']        = 'Auction (Açık Artırma)';
$_['text_listing_store']          = 'Store Inventory';
$_['text_listing_classified']     = 'Classified Ad';

// eBay Sites
$_['text_site_us']                = 'eBay US (ebay.com)';
$_['text_site_uk']                = 'eBay UK (ebay.co.uk)';
$_['text_site_germany']           = 'eBay Germany (ebay.de)';
$_['text_site_france']            = 'eBay France (ebay.fr)';
$_['text_site_italy']             = 'eBay Italy (ebay.it)';
$_['text_site_spain']             = 'eBay Spain (ebay.es)';
$_['text_site_australia']         = 'eBay Australia (ebay.com.au)';
$_['text_site_canada']            = 'eBay Canada (ebay.ca)';
$_['text_site_india']             = 'eBay India (ebay.in)';
$_['text_site_turkey']            = 'GittiGidiyor Turkey';

// Status Options
$_['text_status_draft']           = 'Taslak';
$_['text_status_listed']          = 'Listelendi';
$_['text_status_sold']            = 'Satıldı';
$_['text_status_ended']           = 'Sona Erdi';
$_['text_status_error']           = 'Hata';

// Payment Methods
$_['text_payment_paypal']         = 'PayPal';
$_['text_payment_credit_card']    = 'Kredi/Banka Kartı';
$_['text_payment_bank_transfer']  = 'Banka Havalesi';
$_['text_payment_escrow']         = 'Escrow';
$_['text_payment_cash_pickup']    = 'Kapıda Ödeme';

// Shipping Services
$_['text_shipping_ups']           = 'UPS Ground';
$_['text_shipping_usps']          = 'USPS Priority Mail';
$_['text_shipping_fedex']         = 'FedEx Home Delivery';
$_['text_shipping_international'] = 'Uluslararası Kargo';
$_['text_shipping_global']        = 'eBay Global Shipping Program';
$_['text_shipping_standard']      = 'Standart Uluslararası Kargo';

// Conditions
$_['text_condition_new']          = 'Yeni';
$_['text_condition_used']         = 'Kullanılmış';
$_['text_condition_refurbished']  = 'Yenilenmiş';
$_['text_condition_parts']        = 'Parça/Çalışmıyor';

// Dashboard
$_['text_dashboard']              = 'eBay Dashboard';
$_['text_total_listings']         = 'Toplam Liste';
$_['text_active_listings']        = 'Aktif Listeler';
$_['text_watching_count']         = 'Toplam İzleyen';
$_['text_monthly_sales']          = 'Aylık Satış';
$_['text_monthly_fees']           = 'Aylık Ücretler';
$_['text_feedback_score']         = 'Feedback Puanı';
$_['text_last_sync']              = 'Son Senkronizasyon';
$_['text_promoted_listings']      = 'Promoted Listeler';
$_['text_international_sales']    = 'Uluslararası Satışlar';
$_['text_defect_rate']            = 'Hata Oranı';
$_['text_seller_level']           = 'Satıcı Seviyesi';
$_['text_quick_actions']          = 'Hızlı İşlemler';

// Notifications
$_['text_listing_started']        = 'Ürün listeleme başlatıldı...';
$_['text_listing_completed']      = 'Ürün listeleme tamamlandı!';
$_['text_update_started']         = 'Liste güncelleme başlatıldı...';
$_['text_update_completed']       = 'Liste güncelleme tamamlandı!';
$_['text_sync_started']           = 'Sipariş senkronizasyonu başlatıldı...';
$_['text_sync_completed']         = 'Sipariş senkronizasyonu tamamlandı!';
$_['text_promotion_started']      = 'Promoted listings yönetimi başlatıldı...';
$_['text_promotion_completed']    = 'Promoted listings yönetimi tamamlandı!';

// Global Marketplace Features
$_['text_currency_usd']           = '$ (US Dollar)';
$_['text_currency_gbp']           = '£ (British Pound)';
$_['text_currency_eur']           = '€ (Euro)';
$_['text_currency_aud']           = 'A$ (Australian Dollar)';
$_['text_currency_cad']           = 'C$ (Canadian Dollar)';
$_['text_currency_try']           = '₺ (Turkish Lira)';
$_['text_worldwide_shipping']     = 'Dünya Çapında Kargo';
$_['text_fast_n_free']            = 'Hızlı ve Ücretsiz Kargo';
$_['text_top_rated_seller']       = 'En İyi Satıcı';
$_['text_ebay_plus']              = 'eBay Plus';
$_['text_best_match']             = 'Best Match Optimizasyonu';
$_['text_cross_border']           = 'Sınır Ötesi Ticaret';

// Auction Features
$_['text_starting_bid']           = 'Başlangıç Teklifi';
$_['text_reserve_price']          = 'Rezerv Fiyatı';
$_['text_buy_it_now_price']       = 'Hemen Al Fiyatı';
$_['text_auction_duration']       = 'Açık Artırma Süresi';
$_['text_current_bid']            = 'Güncel Teklif';
$_['text_bid_count']              = 'Teklif Sayısı';
$_['text_time_left']              = 'Kalan Süre';
$_['text_auto_extend']            = 'Otomatik Uzatma';

// Promoted Listings
$_['text_promotion_rate']         = 'Promosyon Oranı (%)';
$_['text_campaign_budget']        = 'Kampanya Bütçesi';
$_['text_suggested_bid']          = 'Önerilen Teklif';
$_['text_impression_share']       = 'Gösterim Payı';
$_['text_click_through_rate']     = 'Tıklama Oranı';
$_['text_conversion_rate']        = 'Dönüşüm Oranı';
$_['text_ad_spend']               = 'Reklam Harcaması';
$_['text_total_sales']            = 'Toplam Satış';

// Feedback Management
$_['text_feedback_positive']      = 'Pozitif';
$_['text_feedback_neutral']       = 'Nötr';
$_['text_feedback_negative']      = 'Negatif';
$_['text_feedback_comment']       = 'Feedback Yorumu';
$_['text_feedback_response']      = 'Feedback Yanıtı';
$_['text_feedback_follow_up']     = 'Takip Mesajı';
$_['text_feedback_private']       = 'Özel Feedback';
$_['text_detailed_seller_rating'] = 'Detaylı Satıcı Değerlendirmesi';
$_['text_communication']          = 'İletişim';
$_['text_shipping_time']          = 'Kargo Süresi';
$_['text_shipping_charges']       = 'Kargo Ücretleri';
$_['text_item_description']       = 'Ürün Açıklaması';

// Return Policy
$_['text_returns_accepted']       = 'İadeler Kabul Edilir';
$_['text_returns_not_accepted']   = 'İade Kabul Edilmez';
$_['text_return_period_30']       = '30 Gün';
$_['text_return_period_60']       = '60 Gün';
$_['text_money_back']             = 'Para İadesi';
$_['text_exchange']               = 'Değişim';
$_['text_buyer_pays_shipping']    = 'Alıcı Kargo Öder';
$_['text_seller_pays_shipping']   = 'Satıcı Kargo Öder';

// Log Messages
$_['text_log_listing_create']     = 'Liste oluşturma';
$_['text_log_listing_update']     = 'Liste güncelleme';
$_['text_log_listing_end']        = 'Liste sonlandırma';
$_['text_log_order_sync']         = 'Sipariş senkronizasyonu';
$_['text_log_feedback_sync']      = 'Feedback senkronizasyonu';
$_['text_log_promotion_update']   = 'Promosyon güncelleme';
$_['text_log_category_sync']      = 'Kategori senkronizasyonu';
$_['text_log_api_call']           = 'API çağrısı';
$_['text_log_error']              = 'Hata';
$_['text_log_success']            = 'Başarılı';
$_['text_log_warning']            = 'Uyarı';

// Tooltips
$_['tooltip_list_products']       = 'Seçili ürünleri eBay\'e listele';
$_['tooltip_update_listings']     = 'Mevcut listelerin fiyat ve stok bilgilerini güncelle';
$_['tooltip_sync_orders']         = 'eBay\'den yeni siparişleri OpenCart\'a aktar';
$_['tooltip_test_api']            = 'eBay API bağlantısını ve kimlik bilgilerini test et';
$_['tooltip_manage_promotions']   = 'Promoted listings kampanyalarını yönet';
$_['tooltip_manage_feedback']     = 'Feedback puanınızı ve yorumlarınızı yönet';
$_['tooltip_global_shipping']     = 'Uluslararası alıcılara otomatik kargo hesaplama';
$_['tooltip_best_offer']          = 'Alıcıların sizinle pazarlık yapmasına izin verin';

// Form Validation
$_['validation_app_id_format']    = 'App ID geçerli format olmalıdır!';
$_['validation_token_length']     = 'User Token geçerli uzunlukta olmalıdır!';
$_['validation_price_markup']     = 'Fiyat markup 0-100 arasında olmalıdır!';
$_['validation_promotion_rate']   = 'Promosyon oranı 2-20 arasında olmalıdır!';
$_['validation_dispatch_time']    = 'Gönderim süresi 1-30 gün arasında olmalıdır!';

// Integration Info
$_['text_module_version']         = 'MesChain-Sync v3.0';
$_['text_ebay_api_version']       = 'eBay Trading API v967';
$_['text_supported_features']     = 'Desteklenen Özellikler';
$_['text_auction_bin_support']    = '✓ Açık Artırma & Buy It Now';
$_['text_international_shipping'] = '✓ Uluslararası Kargo';
$_['text_promoted_listings']      = '✓ Promoted Listings';
$_['text_feedback_automation']    = '✓ Otomatik Feedback';
$_['text_multi_site_support']     = '✓ Çoklu eBay Sitesi';
$_['text_best_match_optimization'] = '✓ Best Match Optimizasyonu';
$_['text_paypal_integration']     = '✓ PayPal Entegrasyonu';
$_['text_global_shipping_program'] = '✓ Global Shipping Program';

// Performance Metrics
$_['text_impression_data']        = 'Gösterim Verileri';
$_['text_conversion_data']        = 'Dönüşüm Verileri';
$_['text_seller_performance']     = 'Satıcı Performansı';
$_['text_defect_tracking']        = 'Hata Takibi';
$_['text_late_shipment_rate']     = 'Geç Kargo Oranı';
$_['text_cases_rate']             = 'Şikayet Oranı';
$_['text_above_standard']         = 'Standart Üstü';
$_['text_below_standard']         = 'Standart Altı';
$_['text_top_rated']              = 'En İyi Puan';

// Additional Features
$_['text_bulk_operations']        = 'Toplu İşlemler';
$_['text_schedule_listings']      = 'Zamanlanmış Listeleme';
$_['text_auto_relist']            = 'Otomatik Yeniden Listeleme';
$_['text_inventory_management']   = 'Stok Yönetimi';
$_['text_price_monitoring']       = 'Fiyat İzleme';
$_['text_competitor_analysis']    = 'Rakip Analizi';
$_['text_sales_analytics']        = 'Satış Analitiği';
$_['text_profit_calculator']      = 'Kar Hesaplayıcısı';

// Time and Date
$_['text_never']                  = 'Hiçbir zaman';
$_['text_days']                   = 'gün';
$_['text_hours']                  = 'saat';
$_['text_minutes']                = 'dakika';
$_['text_seconds']                = 'saniye';
$_['text_active']                 = 'Aktif';
$_['text_inactive']               = 'Pasif';
$_['text_expired']                = 'Süresi Dolmuş';

// API Rate Limiting
$_['text_api_calls_remaining']    = 'Kalan API Çağrısı';
$_['text_daily_limit']            = 'Günlük Limit';
$_['text_hourly_limit']           = 'Saatlik Limit';
$_['text_rate_limit_reset']       = 'Limit Sıfırlama';
$_['text_api_usage']              = 'API Kullanımı';
?> 