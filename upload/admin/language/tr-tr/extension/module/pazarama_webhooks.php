<?php
/**
 * Pazarama Webhook Language - Turkish
 * MesChain-Sync v3.0 - Pazarama Marketplace Integration
 */

// Heading
$_['heading_title']          = 'Pazarama Webhook Yönetimi';

// Text
$_['text_extension']         = 'Eklentiler';
$_['text_success']           = 'Başarılı: Pazarama webhook ayarları güncellendi!';
$_['text_edit']              = 'Pazarama Webhook Düzenle';
$_['text_webhook_list']      = 'Webhook Listesi';
$_['text_add_webhook']       = 'Yeni Webhook Ekle';
$_['text_recent_events']     = 'Son Olaylar';
$_['text_notifications']     = 'Bildirimler';
$_['text_test_results']      = 'Test Sonuçları';
$_['text_log_details']       = 'Log Detayları';
$_['text_enabled']           = 'Etkin';
$_['text_disabled']          = 'Devre Dışı';
$_['text_never']             = 'Hiçbir Zaman';
$_['text_optional']          = 'Opsiyonel';
$_['text_select']            = 'Seçiniz';
$_['text_system_event']      = 'Sistem Olayı';
$_['text_no_webhooks']       = 'Henüz webhook eklenmemiş.';
$_['text_no_events']         = 'Henüz olay kaydı bulunmamaktadır.';
$_['text_no_notifications']  = 'Henüz bildirim bulunmamaktadır.';

// Statistics
$_['text_total_webhooks']    = 'Toplam Webhook';
$_['text_active_webhooks']   = 'Aktif Webhook';
$_['text_events_today']      = 'Bugünkü Olaylar';
$_['text_success_rate']      = 'Başarı Oranı';

// Tabs
$_['tab_webhooks']           = 'Webhook\'lar';
$_['tab_events']             = 'Olaylar';
$_['tab_notifications']      = 'Bildirimler';

// Entry
$_['entry_event_type']       = 'Olay Türü';
$_['entry_webhook_url']      = 'Webhook URL';
$_['entry_webhook_secret']   = 'Gizli Anahtar';
$_['entry_status']           = 'Durum';

// Column
$_['column_event_type']      = 'Olay Türü';
$_['column_url']             = 'URL';
$_['column_status']          = 'Durum';
$_['column_success_count']   = 'Başarılı';
$_['column_error_count']     = 'Hatalı';
$_['column_last_triggered']  = 'Son Tetiklenme';
$_['column_webhook']         = 'Webhook';
$_['column_response_code']   = 'Yanıt Kodu';
$_['column_execution_time']  = 'Süre';
$_['column_date']            = 'Tarih';
$_['column_action']          = 'İşlem';

// Button
$_['button_add']             = 'Ekle';
$_['button_edit']            = 'Düzenle';
$_['button_delete']          = 'Sil';
$_['button_test']            = 'Test Et';
$_['button_test_webhook']    = 'Webhook Test Et';
$_['button_configure']       = 'Yapılandır';
$_['button_enable']          = 'Etkinleştir';
$_['button_disable']         = 'Devre Dışı Bırak';
$_['button_refresh']         = 'Yenile';
$_['button_clear_logs']      = 'Logları Temizle';
$_['button_view_details']    = 'Detayları Görüntüle';
$_['button_mark_read']       = 'Okundu Olarak İşaretle';
$_['button_mark_all_read']   = 'Tümünü Okundu İşaretle';
$_['button_close']           = 'Kapat';
$_['button_cancel']          = 'İptal';

// Event Types
$_['text_order_created']     = 'Sipariş Oluşturuldu';
$_['text_order_updated']     = 'Sipariş Güncellendi';
$_['text_order_cancelled']   = 'Sipariş İptal Edildi';
$_['text_product_approved']  = 'Ürün Onaylandı';
$_['text_product_rejected']  = 'Ürün Reddedildi';
$_['text_inventory_updated'] = 'Stok Güncellendi';
$_['text_payment_completed'] = 'Ödeme Tamamlandı';

// Confirmation Messages
$_['text_confirm_toggle']    = 'Webhook durumunu değiştirmek istediğinizden emin misiniz?';
$_['text_confirm_delete']    = 'Bu webhook\'u silmek istediğinizden emin misiniz?';
$_['text_confirm_delete_notification'] = 'Bu bildirimi silmek istediğinizden emin misiniz?';

// Success Messages
$_['text_webhook_added']     = 'Webhook başarıyla eklendi!';
$_['text_webhook_updated']   = 'Webhook başarıyla güncellendi!';
$_['text_webhook_deleted']   = 'Webhook başarıyla silindi!';
$_['text_logs_cleared']      = 'Loglar başarıyla temizlendi!';
$_['text_notification_marked_read'] = 'Bildirim okundu olarak işaretlendi!';
$_['text_all_notifications_marked_read'] = 'Tüm bildirimler okundu olarak işaretlendi!';
$_['text_configuration_saved'] = 'Webhook yapılandırması kaydedildi!';

// Error Messages
$_['error_permission']       = 'Uyarı: Bu modülü değiştirme izniniz yok!';
$_['error_event_type']       = 'Olay türü gereklidir!';
$_['error_webhook_url']      = 'Geçerli bir webhook URL\'si gereklidir!';
$_['error_webhook_not_found'] = 'Webhook bulunamadı!';
$_['error_webhook_id_required'] = 'Webhook ID gereklidir!';
$_['error_method_not_allowed'] = 'Bu metoda izin verilmiyor!';
$_['error_logs_clear_failed'] = 'Loglar temizlenirken hata oluştu!';
$_['error_configuration_save_failed'] = 'Yapılandırma kaydedilemedi!';

// Help Text
$_['help_webhook_url']       = 'Pazarama\'dan gelen bildirimleri alacak URL adresinizi girin.';
$_['help_webhook_secret']    = 'Webhook güvenliği için kullanılacak gizli anahtarı girin.';
$_['help_event_type']        = 'Bu webhook\'un hangi olay türleri için tetikleneceğini seçin.';

// Info Messages
$_['text_webhook_info']      = 'Webhook\'lar Pazarama\'dan gelen gerçek zamanlı bildirimleri almanızı sağlar.';
$_['text_security_info']     = 'Gizli anahtar kullanarak webhook güvenliğini artırabilirsiniz.';
$_['text_test_info']         = 'Webhook\'larınızı test ederek doğru çalıştığından emin olun.';

// Status Messages
$_['text_webhook_test_success'] = 'Webhook testi başarılı!';
$_['text_webhook_test_failed'] = 'Webhook testi başarısız!';
$_['text_webhook_enabled']   = 'Webhook etkinleştirildi!';
$_['text_webhook_disabled']  = 'Webhook devre dışı bırakıldı!';

// Notification Types
$_['text_order_created_notification'] = 'Yeni sipariş bildirimi';
$_['text_product_approved_notification'] = 'Ürün onay bildirimi';
$_['text_inventory_updated_notification'] = 'Stok güncelleme bildirimi';
$_['text_payment_completed_notification'] = 'Ödeme tamamlanma bildirimi';
$_['text_webhook_test_notification'] = 'Webhook test bildirimi';
$_['text_logs_cleared_notification'] = 'Log temizleme bildirimi';

// Webhook Configuration
$_['text_configure_webhooks'] = 'Webhook Yapılandırması';
$_['text_event_subscriptions'] = 'Olay Abonelikleri';
$_['text_enable_event']      = 'Bu olay türünü etkinleştir';
$_['text_webhook_url_placeholder'] = 'https://yoursite.com/webhook/pazarama';
$_['text_webhook_secret_placeholder'] = 'Güvenlik için rastgele bir anahtar girin';

// Advanced Options
$_['text_advanced_options']  = 'Gelişmiş Seçenekler';
$_['text_retry_attempts']    = 'Yeniden Deneme Sayısı';
$_['text_timeout_seconds']   = 'Zaman Aşımı (Saniye)';
$_['text_log_retention_days'] = 'Log Saklama Süresi (Gün)';

// Monitoring
$_['text_webhook_monitoring'] = 'Webhook İzleme';
$_['text_last_24_hours']     = 'Son 24 Saat';
$_['text_last_7_days']       = 'Son 7 Gün';
$_['text_last_30_days']      = 'Son 30 Gün';
$_['text_response_time']     = 'Yanıt Süresi';
$_['text_average_response_time'] = 'Ortalama Yanıt Süresi';
$_['text_error_rate']        = 'Hata Oranı';
$_['text_uptime']            = 'Çalışma Süresi';

// Webhook Events Detail
$_['text_payload']           = 'Veri Yükü';
$_['text_response']          = 'Yanıt';
$_['text_headers']           = 'Başlıklar';
$_['text_request_details']   = 'İstek Detayları';
$_['text_response_details']  = 'Yanıt Detayları';
?>
