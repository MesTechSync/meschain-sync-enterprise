<?php
/**
 * MesChain-Sync Trendyol Webhooks Language File (Turkish)
 * 
 * @category   Language
 * @package    MesChain-Sync
 * @version    3.0.1
 * @author     MesTech Team
 * @license    Commercial License
 * @link       https://meschain.com
 */

// Heading
$_['heading_title']              = 'Trendyol Webhook Yönetimi';

// Text
$_['text_extension']             = 'Eklentiler';
$_['text_success']               = 'Başarılı: Trendyol Webhook Sistemi başarıyla güncellendi!';
$_['text_edit']                  = 'Trendyol Webhook Sistemi Düzenle';

// Webhook Statistics
$_['text_total_webhooks']        = 'Toplam Webhook';
$_['text_successful_webhooks']   = 'Başarılı Webhook';
$_['text_failed_webhooks']       = 'Başarısız Webhook';
$_['text_avg_response_time']     = 'Ortalama Yanıt Süresi';
$_['text_all_time']              = 'Tüm Zamanlar';
$_['text_success_rate']          = 'Başarı Oranı';
$_['text_errors']                = 'Hatalar';
$_['text_performance']           = 'Performans';

// Webhook Configuration
$_['text_webhook_configuration'] = 'Webhook Yapılandırması';
$_['entry_webhook_url']          = 'Webhook URL';
$_['entry_webhook_secret']       = 'Webhook Gizli Anahtarı';
$_['entry_webhook_events']       = 'Webhook Olayları';
$_['entry_webhook_timeout']      = 'Timeout (Saniye)';
$_['entry_webhook_retry']        = 'Otomatik Yeniden Deneme';
$_['entry_webhook_max_retries']  = 'Maksimum Deneme Sayısı';
$_['entry_webhook_status']       = 'Webhook Durumu';
$_['text_enabled']               = 'Etkin';
$_['text_disabled']              = 'Devre Dışı';

// Help Texts
$_['help_webhook_url']           = 'Trendyol webhook\'larını alacak URL adresi';
$_['help_webhook_secret']        = 'Webhook güvenliği için kullanılan gizli anahtar';
$_['help_webhook_timeout']       = 'Webhook isteği için maksimum bekleme süresi (5-60 saniye)';
$_['help_webhook_retry']         = 'Başarısız webhook\'lar için otomatik yeniden deneme';

// Webhook Events
$_['text_order_created']         = 'Sipariş Oluşturuldu';
$_['text_order_updated']         = 'Sipariş Güncellendi';
$_['text_order_cancelled']       = 'Sipariş İptal Edildi';
$_['text_product_updated']       = 'Ürün Güncellendi';
$_['text_stock_updated']         = 'Stok Güncellendi';
$_['text_price_updated']         = 'Fiyat Güncellendi';

// Charts
$_['text_webhook_activity']      = 'Webhook Aktivitesi';
$_['text_event_distribution']    = 'Olay Dağılımı';

// Recent Webhooks
$_['text_recent_webhooks']       = 'Son Webhook\'lar';
$_['text_filter_by_status']      = 'Duruma Göre Filtrele';
$_['text_all_statuses']          = 'Tüm Durumlar';
$_['text_successful']            = 'Başarılı';
$_['text_failed']                = 'Başarısız';
$_['text_pending']               = 'Beklemede';

// Table Columns
$_['column_id']                  = 'ID';
$_['column_event_type']          = 'Olay Türü';
$_['column_status']              = 'Durum';
$_['column_response_time']       = 'Yanıt Süresi';
$_['column_received_at']         = 'Alındığı Zaman';
$_['column_processed_at']        = 'İşlendiği Zaman';
$_['column_retry_count']         = 'Deneme Sayısı';
$_['column_action']              = 'İşlem';

// Status Messages
$_['text_success']               = 'Başarılı';
$_['text_error']                 = 'Hata';
$_['text_pending']               = 'Beklemede';

// Pagination
$_['text_showing']               = 'Gösteriliyor';
$_['text_to']                    = '-';
$_['text_of']                    = '/';
$_['text_entries']               = 'kayıt';

// Webhook Details Modal
$_['text_webhook_details']       = 'Webhook Detayları';
$_['text_basic_information']     = 'Temel Bilgiler';
$_['text_headers']               = 'Başlıklar';
$_['text_payload']               = 'Veri';
$_['text_response']              = 'Yanıt';
$_['text_error_message']         = 'Hata Mesajı';

// Buttons
$_['button_test_webhook']        = 'Webhook Testi';
$_['button_clear_logs']          = 'Logları Temizle';
$_['button_save']                = 'Kaydet';
$_['button_close']               = 'Kapat';

// Test Messages
$_['text_confirm_test_webhook']  = 'Test webhook\'u göndermek istediğinizden emin misiniz?';
$_['text_webhook_test_success']  = 'Webhook testi başarılı';
$_['text_webhook_test_failed']   = 'Webhook testi başarısız';
$_['text_confirm_clear_logs']    = 'Tüm webhook loglarını temizlemek istediğinizden emin misiniz?';
$_['text_logs_cleared']          = 'Webhook logları başarıyla temizlendi.';
$_['text_clear_logs_failed']     = 'Webhook logları temizlenemedi';

// Details and Actions
$_['text_details_error']         = 'Webhook detayları yüklenirken hata oluştu';
$_['text_confirm_retry_webhook'] = 'Bu webhook\'u yeniden göndermek istediğinizden emin misiniz?';
$_['text_webhook_retry_success'] = 'Webhook başarıyla yeniden gönderildi.';
$_['text_webhook_retry_failed']  = 'Webhook yeniden gönderilemedi';
$_['text_confirm_delete_webhook'] = 'Bu webhook kaydını silmek istediğinizden emin misiniz?';
$_['text_delete_failed']         = 'Webhook kaydı silinemedi';

// AJAX Messages
$_['text_ajax_error']            = 'AJAX hatası oluştu. Lütfen tekrar deneyin.';

// Help
$_['help_trendyol_webhooks']     = 'Trendyol Webhook Yönetimi ile Trendyol\'dan gelen webhook\'ları yönetebilir ve izleyebilirsiniz.';

// Error
$_['error_permission']           = 'Uyarı: Trendyol Webhook Sistemi modülünü değiştirme izniniz yok!';
$_['error_webhook_url_required'] = 'Webhook URL alanı zorunludur!';
$_['error_invalid_webhook_url']  = 'Geçersiz webhook URL!';
$_['error_webhook_secret_required'] = 'Webhook gizli anahtarı zorunludur!';
$_['error_invalid_timeout']      = 'Timeout değeri 5-60 saniye arasında olmalıdır!';
$_['error_invalid_max_retries']  = 'Maksimum deneme sayısı 1-5 arasında olmalıdır!';
?> 