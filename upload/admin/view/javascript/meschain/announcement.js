/**
 * MesChain Sync - Duyuru Sistemi
 * 
 * Bu dosya, MesChain Sync modülünün duyuru sistemini yönetir.
 * Duyurular, yönetici panelinde gösterilir ve kullanıcılar tarafından kapatılabilir.
 */

var MesChainAnnouncement = {
    /**
     * Duyuru sistemini başlat
     */
    init: function() {
        this.loadAnnouncements();
        this.bindEvents();
    },
    
    /**
     * Duyuruları yükle
     */
    loadAnnouncements: function() {
        var self = this;
        
        // API'den duyuruları al
        $.ajax({
            url: 'index.php?route=extension/module/meschain_sync/getAnnouncements&user_token=' + getURLVar('user_token'),
            dataType: 'json',
            success: function(json) {
                if (json.success && json.announcements.length > 0) {
                    self.renderAnnouncements(json.announcements);
                    $('#meschain-announcements').slideDown();
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                console.error('Duyurular yüklenirken hata oluştu: ' + thrownError);
            }
        });
    },
    
    /**
     * Duyuruları görüntüle
     */
    renderAnnouncements: function(announcements) {
        var container = $('#meschain-announcements');
        container.empty();
        
        $.each(announcements, function(i, announcement) {
            var html = '<div class="announcement" data-id="' + announcement.id + '">';
            html += '<div class="announcement-header">';
            html += '<h4>' + announcement.title + '</h4>';
            html += '<button type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
            html += '</div>';
            html += '<div class="announcement-body">' + announcement.content + '</div>';
            html += '</div>';
            
            container.append(html);
        });
    },
    
    /**
     * Olayları bağla
     */
    bindEvents: function() {
        var self = this;
        
        // Duyuru kapatma butonu
        $(document).on('click', '#meschain-announcements .close', function() {
            var announcement = $(this).closest('.announcement');
            var id = announcement.data('id');
            
            self.dismissAnnouncement(id);
            announcement.slideUp(function() {
                $(this).remove();
                
                if ($('#meschain-announcements .announcement').length === 0) {
                    $('#meschain-announcements').slideUp();
                }
            });
        });
    },
    
    /**
     * Duyuruyu kapat
     */
    dismissAnnouncement: function(id) {
        $.ajax({
            url: 'index.php?route=extension/module/meschain_sync/dismissAnnouncement&user_token=' + getURLVar('user_token'),
            type: 'POST',
            data: { announcement_id: id },
            dataType: 'json',
            error: function(xhr, ajaxOptions, thrownError) {
                console.error('Duyuru kapatılırken hata oluştu: ' + thrownError);
            }
        });
    }
};

// Sayfa yüklendiğinde duyuru sistemini başlat
$(document).ready(function() {
    MesChainAnnouncement.init();
}); 