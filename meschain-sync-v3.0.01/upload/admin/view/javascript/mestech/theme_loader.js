/**
 * MesTech Sync - Tema Yükleyici
 * 
 * Seçilen temayı yükler ve uygular
 */

$(document).ready(function() {
    // Tema seçimi değiştiğinde
    $('#input-theme').on('change', function() {
        var theme = $(this).val();
        loadTheme(theme);
        saveTheme(theme);
    });
    
    // Tema önizleme butonları
    $('.theme-preview-btn').on('click', function(e) {
        e.preventDefault();
        var theme = $(this).data('theme');
        loadTheme(theme);
        
        // Önizleme bildirimi
        showNotification('Tema önizleniyor: ' + theme, 'info');
    });
    
    // Tema uygulama butonları
    $('.theme-apply-btn').on('click', function(e) {
        e.preventDefault();
        var theme = $(this).data('theme');
        loadTheme(theme);
        saveTheme(theme);
        
        // Tema seçimini güncelle
        $('#input-theme').val(theme);
    });
    
    // Sayfa yüklendiğinde mevcut temayı yükle
    var currentTheme = $('#input-theme').val();
    if (currentTheme) {
        loadTheme(currentTheme);
    }
    
    /**
     * Temayı yükler ve uygular
     * 
     * @param {string} theme Tema adı
     */
    function loadTheme(theme) {
        // Mevcut tema stil dosyasını kaldır
        $('link[data-theme]').remove();
        
        // Varsayılan tema ise işlem yapma
        if (theme === 'default') {
            return;
        }
        
        // Yeni tema stil dosyasını ekle
        var cssLink = $('<link>', {
            rel: 'stylesheet',
            type: 'text/css',
            href: 'view/stylesheet/mestech/' + theme + '.css',
            'data-theme': theme
        });
        
        $('head').append(cssLink);
        
        // Tema yüklendiğinde bildirim göster
        cssLink.on('load', function() {
            // Tema başarıyla yüklendi
            console.log('Tema yüklendi: ' + theme);
        });
        
        // Tema yüklenemezse hata göster
        cssLink.on('error', function() {
            console.error('Tema yüklenemedi: ' + theme);
            showNotification('Tema yüklenemedi: ' + theme, 'danger');
        });
    }
    
    /**
     * Temayı kaydeder
     * 
     * @param {string} theme Tema adı
     */
    function saveTheme(theme) {
        $.ajax({
            url: 'index.php?route=extension/mestech/mestech_sync/change_theme&user_token=' + getUrlParam('user_token'),
            type: 'post',
            data: {
                theme: theme
            },
            dataType: 'json',
            success: function(json) {
                if (json.success) {
                    showNotification(json.success, 'success');
                } else if (json.error) {
                    showNotification(json.error, 'danger');
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                showNotification('Tema kaydedilirken hata oluştu: ' + thrownError, 'danger');
            }
        });
    }
    
    /**
     * Bildirim gösterir
     * 
     * @param {string} message Mesaj
     * @param {string} type Bildirim tipi (success, info, warning, danger)
     */
    function showNotification(message, type) {
        // Mevcut bildirimleri kaldır
        $('.theme-notification').remove();
        
        // Yeni bildirim oluştur
        var notification = $('<div>', {
            class: 'alert alert-' + type + ' theme-notification',
            html: '<i class="fa fa-info-circle"></i> ' + message + '<button type="button" class="close" data-dismiss="alert">&times;</button>'
        });
        
        // Bildirimi göster
        $('#content > .container-fluid').prepend(notification);
        
        // 3 saniye sonra otomatik kapat
        setTimeout(function() {
            notification.fadeOut('slow', function() {
                $(this).remove();
            });
        }, 3000);
    }
    
    /**
     * URL parametresini alır
     * 
     * @param {string} name Parametre adı
     * @return {string|null} Parametre değeri
     */
    function getUrlParam(name) {
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results == null) {
            return null;
        }
        return decodeURI(results[1]) || null;
    }
}); 