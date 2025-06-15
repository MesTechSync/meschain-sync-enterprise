<?php
// Text
$_['heading_title']    = 'MesChain RBAC & Multi-Tenant Yönetimi';

// Tab
$_['tab_overview']     = 'Genel Bakış';
$_['tab_tenants']      = 'Tenant Yönetimi';
$_['tab_users']        = 'Kullanıcı Rolleri';
$_['tab_permissions']  = 'İzin Şablonları';
$_['tab_sessions']     = 'Aktif Oturumlar';

// Button
$_['button_save']         = 'Kaydet';
$_['button_cancel']       = 'İptal';
$_['button_add_tenant']   = 'Yeni Tenant Ekle';
$_['button_assign_role']  = 'Rol Ata';
$_['button_refresh']      = 'Yenile';
$_['button_edit']         = 'Düzenle';
$_['button_delete']       = 'Sil';
$_['button_view_users']   = 'Kullanıcıları Görüntüle';

// Entry
$_['entry_tenant_name']        = 'Tenant Adı';
$_['entry_tenant_type']        = 'Tenant Tipi';
$_['entry_domain']             = 'Domain';
$_['entry_max_users']          = 'Maksimum Kullanıcı';
$_['entry_max_orders']         = 'Aylık Sipariş Limiti';
$_['entry_features_enabled']   = 'Aktif Özellikler';
$_['entry_user_select']        = 'Kullanıcı Seç';
$_['entry_role_template']      = 'Rol Şablonu';
$_['entry_custom_permissions'] = 'Özel İzinler';

// Column
$_['column_tenant_id']       = 'ID';
$_['column_tenant_name']     = 'Tenant Adı';
$_['column_tenant_type']     = 'Tip';
$_['column_domain']          = 'Domain';
$_['column_user_count']      = 'Kullanıcı Sayısı';
$_['column_status']          = 'Durum';
$_['column_date_created']    = 'Oluşturma Tarihi';
$_['column_actions']         = 'İşlemler';
$_['column_user']            = 'Kullanıcı';
$_['column_email']           = 'E-posta';
$_['column_role']            = 'Rol';
$_['column_marketplace']     = 'Marketplace Erişimi';
$_['column_limits']          = 'Limitler';
$_['column_last_update']     = 'Son Güncelleme';
$_['column_ip_address']      = 'IP Adresi';
$_['column_browser']         = 'Tarayıcı';
$_['column_last_activity']   = 'Son Aktivite';
$_['column_tenant']          = 'Tenant';

// Help
$_['help_tenant_name']     = 'Tenant için benzersiz bir isim girin';
$_['help_tenant_type']     = 'Individual: Bireysel kullanım, Business: İşletme, Enterprise: Kurumsal';
$_['help_domain']          = 'Bu tenant\'a özel domain (opsiyonel)';
$_['help_max_users']       = 'Bu tenant\'ta maksimum kaç kullanıcı olabilir';
$_['help_max_orders']      = 'Aylık maksimum sipariş işleme limiti';
$_['help_features_enabled'] = 'Bu tenant için aktif olan marketplace\'ler';

// Text - Overview
$_['text_current_tenant']    = 'Mevcut Tenant';
$_['text_user_role']         = 'Sizin Rolünüz';
$_['text_system_stats']      = 'Sistem İstatistikleri';
$_['text_loading_stats']     = 'İstatistikler yükleniyor...';
$_['text_no_tenant']         = 'Tenant bilgisi yüklenemedi';
$_['text_no_role']           = 'Rol bilgisi bulunamadı';

// Text - Tenants
$_['text_no_tenants']        = 'Tenant bulunamadı';
$_['text_create_tenant']     = 'Yeni Tenant Oluştur';
$_['text_tenant_users']      = 'Tenant Kullanıcıları';

// Text - Users
$_['text_select_tenant']     = 'Önce bir tenant seçin';
$_['text_no_users']          = 'Bu tenant\'ta kullanıcı bulunmuyor';
$_['text_assign_role']       = 'Kullanıcıya Rol Ata';

// Text - Permissions
$_['text_permission_templates'] = 'İzin Şablonları';
$_['text_role_level']          = 'Rol Seviyesi';
$_['text_permissions']         = 'İzinler';
$_['text_marketplace_access']  = 'Marketplace Erişimi';
$_['text_all_marketplaces']    = 'Tüm Marketplace\'ler';

// Text - Sessions
$_['text_active_sessions']   = 'Aktif Oturumlar';
$_['text_no_sessions']       = 'Aktif oturum bulunamadı';
$_['text_loading_sessions']  = 'Oturumlar yükleniyor...';

// Text - Tenant Types
$_['text_individual']  = 'Bireysel';
$_['text_business']    = 'İşletme';
$_['text_enterprise']  = 'Kurumsal';

// Text - Status
$_['text_active']    = 'Aktif';
$_['text_inactive']  = 'Pasif';
$_['text_suspended'] = 'Askıya Alınmış';

// Text - Roles
$_['text_super_admin'] = 'Süper Admin';
$_['text_admin']       = 'Admin';
$_['text_technical']   = 'Teknik Personel';
$_['text_user']        = 'Kullanıcı';
$_['text_viewer']      = 'Görüntüleyici';

// Text - Permissions List
$_['text_system_admin']           = 'Sistem Yönetimi';
$_['text_tenant_management']      = 'Tenant Yönetimi';
$_['text_user_management']        = 'Kullanıcı Yönetimi';
$_['text_marketplace_management'] = 'Marketplace Yönetimi';
$_['text_webhook_management']     = 'Webhook Yönetimi';
$_['text_api_management']         = 'API Yönetimi';
$_['text_report_access']          = 'Rapor Erişimi';
$_['text_log_access']             = 'Log Erişimi';

// Text - Marketplaces
$_['text_trendyol']     = 'Trendyol';
$_['text_n11']          = 'N11';
$_['text_amazon']       = 'Amazon';
$_['text_hepsiburada']  = 'Hepsiburada';
$_['text_ozon']         = 'Ozon';
$_['text_ebay']         = 'eBay';

// Success
$_['text_success_tenant_created']  = 'Tenant başarıyla oluşturuldu!';
$_['text_success_role_assigned']   = 'Rol başarıyla atandı!';
$_['text_success_role_updated']    = 'Rol başarıyla güncellendi!';
$_['text_success_tenant_updated']  = 'Tenant başarıyla güncellendi!';
$_['text_success_settings_saved']  = 'RBAC ayarları başarıyla kaydedildi!';

// Warning
$_['text_warning_select_tenant']   = 'Önce bir tenant seçin';
$_['text_warning_select_user']     = 'Lütfen bir kullanıcı seçin';
$_['text_warning_select_role']     = 'Lütfen bir rol şablonu seçin';
$_['text_warning_confirm_delete']  = 'Bu işlem geri alınamaz. Emin misiniz?';

// Error
$_['error_permission']           = 'Bu sayfaya erişim yetkiniz yok!';
$_['error_tenant_name']          = 'Tenant adı 3-100 karakter arasında olmalıdır!';
$_['error_tenant_exists']        = 'Bu tenant adı zaten kullanımda!';
$_['error_domain_exists']        = 'Bu domain zaten kayıtlı!';
$_['error_max_users']            = 'Maksimum kullanıcı sayısı geçerli bir sayı olmalıdır!';
$_['error_max_orders']           = 'Maksimum sipariş limiti geçerli bir sayı olmalıdır!';
$_['error_invalid_tenant_type']  = 'Geçersiz tenant tipi!';
$_['error_user_not_found']       = 'Kullanıcı bulunamadı!';
$_['error_role_template']        = 'Geçersiz rol şablonu!';
$_['error_database']             = 'Veritabanı hatası oluştu!';
$_['error_server']               = 'Sunucu hatası oluştu!';
$_['error_tenant_limit']         = 'Tenant kullanıcı limiti aşıldı!';
$_['error_feature_limit']        = 'Günlük özellik limitiniz aşıldı!';
$_['error_invalid_session']      = 'Geçersiz oturum!';
$_['error_session_expired']      = 'Oturumunuz sona erdi!';
$_['error_access_denied']        = 'Erişim reddedildi!';
$_['error_insufficient_privileges'] = 'Yetersiz yetki!';

// Modal
$_['modal_create_tenant_title']  = 'Yeni Tenant Oluştur';
$_['modal_assign_role_title']    = 'Kullanıcıya Rol Ata';
$_['modal_edit_tenant_title']    = 'Tenant Düzenle';
$_['modal_view_permissions']     = 'İzinleri Görüntüle';

// Placeholder
$_['placeholder_tenant_name']    = 'Örnek: ABC Şirketi';
$_['placeholder_domain']         = 'Örnek: abc-sirketi.com';
$_['placeholder_select_user']    = 'Kullanıcı seçin...';
$_['placeholder_select_role']    = 'Rol seçin...';
$_['placeholder_select_tenant']  = 'Tenant seçin...';

// Tooltip
$_['tooltip_super_admin']        = 'Tüm sistem yetkilerine sahip en yüksek seviye rol';
$_['tooltip_admin']              = 'Tenant yönetimi ve marketplace işlemleri yapabilir';
$_['tooltip_technical']          = 'API entegrasyonları ve teknik işlemler yapabilir';
$_['tooltip_user']               = 'Temel marketplace işlemleri yapabilir';
$_['tooltip_viewer']             = 'Sadece görüntüleme yetkisi vardır';
$_['tooltip_tenant_type']        = 'Tenant tipi limitler ve özellikler belirler';
$_['tooltip_marketplace_access'] = 'Bu rolle erişilebilen marketplace\'ler';
$_['tooltip_feature_limits']     = 'Günlük API çağrısı ve işlem limitleri';

// Stats
$_['stats_total_tenants']        = 'Toplam Tenant';
$_['stats_total_users']          = 'Toplam Kullanıcı';
$_['stats_active_sessions']      = 'Aktif Oturum';
$_['stats_daily_activities']     = 'Günlük Aktivite';
$_['stats_monthly_api_calls']    = 'Aylık API Çağrısı';
$_['stats_marketplace_count']    = 'Aktif Marketplace';
?> 