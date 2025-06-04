{{ header }}{{ column_left }}
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <a href="{{ cancel }}" data-toggle="tooltip" title="{{ text_cancel }}" class="btn btn-default"><i class="fa fa-reply"></i></a>
      </div>
      <h1>{{ heading_title }}</h1>
      <ul class="breadcrumb">
        {% for breadcrumb in breadcrumbs %}
        <li><a href="{{ breadcrumb.href }}">{{ breadcrumb.text }}</a></li>
        {% endfor %}
      </ul>
    </div>
  </div>
  <div class="container-fluid meschain-panel">
    {% if success %}
    <div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> {{ success }}
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    {% endif %}
    {% if error_warning %}
    <div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i> {{ error_warning }}
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    {% endif %}
    
    <!-- Kullanıcı Profil Bilgisi -->
    <div class="meschain-user-profile">
      <div class="meschain-user-avatar">{{ username|first|upper }}</div>
      <div class="meschain-user-info">
        <div class="meschain-user-name">{{ username }}</div>
        <div class="meschain-user-role">{{ user_id }}</div>
      </div>
      <div class="meschain-user-actions">
        <a href="{{ action }}profile&user_token={{ user_token }}" class="btn btn-primary btn-sm"><i class="fa fa-user"></i> Profil</a>
      </div>
    </div>
    
    <!-- Tema Seçimi -->
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-paint-brush"></i> {{ text_theme }}</h3>
      </div>
      <div class="panel-body">
        <form action="{{ action }}" method="post" enctype="multipart/form-data" id="form-theme" class="form-horizontal">
          <div class="row">
            {% for theme in themes %}
            <div class="col-md-3 col-sm-6">
              <div class="thumbnail text-center">
                <div style="height: 100px; background: linear-gradient(120deg, #e6d3c5 60%, #eaf6fb 100%); margin-bottom: 10px; border-radius: 5px;"></div>
                <div class="caption">
                  <h4>{{ theme.name }}</h4>
                  <p>{{ theme.description }}</p>
                  <div class="radio">
                    <label>
                      {% if theme.directory == current_theme %}
                      <input type="radio" name="theme" value="{{ theme.directory }}" checked="checked" onchange="$('#form-theme').submit();" />
                      <span class="text-success"><i class="fa fa-check"></i> Aktif</span>
                      {% else %}
                      <input type="radio" name="theme" value="{{ theme.directory }}" onchange="$('#form-theme').submit();" />
                      <span>Seç</span>
                      {% endif %}
                    </label>
                  </div>
                </div>
              </div>
            </div>
            {% endfor %}
          </div>
        </form>
      </div>
    </div>
    
    <!-- Ayarlar -->
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-cogs"></i> Ayarlar</h3>
      </div>
      <div class="panel-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover meschain-table">
            <thead>
              <tr>
                <th>Ayar</th>
                <th>Değer</th>
                <th class="text-right">İşlem</th>
              </tr>
            </thead>
            <tbody>
              {% for key, value in settings %}
              <tr>
                <td>{{ key }}</td>
                <td>{{ value }}</td>
                <td class="text-right">
                  <button type="button" onclick="editSetting('{{ key }}', '{{ value|escape('js') }}')" data-toggle="tooltip" title="Düzenle" class="btn btn-primary"><i class="fa fa-pencil"></i></button>
                </td>
              </tr>
              {% endfor %}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Ayar Düzenleme Modal -->
<div class="modal fade" id="setting-modal" tabindex="-1" role="dialog" aria-labelledby="setting-modal-label">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Kapat"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="setting-modal-label">Ayar Düzenle</h4>
      </div>
      <div class="modal-body">
        <form id="form-setting">
          <input type="hidden" name="key" id="setting-key" value="">
          <div class="form-group">
            <label for="setting-value" class="control-label">Değer:</label>
            <input type="text" class="form-control" id="setting-value" name="value">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">İptal</button>
        <button type="button" class="btn btn-primary" onclick="saveSetting()">Kaydet</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
function editSetting(key, value) {
  $('#setting-key').val(key);
  $('#setting-value').val(value);
  $('#setting-modal').modal('show');
}

function saveSetting() {
  $.ajax({
    url: 'index.php?route=extension/module/user_settings/saveSetting&user_token={{ user_token }}',
    type: 'post',
    dataType: 'json',
    data: $('#form-setting').serialize(),
    beforeSend: function() {
      $('#setting-modal .btn-primary').button('loading');
    },
    complete: function() {
      $('#setting-modal .btn-primary').button('reset');
    },
    success: function(json) {
      $('.alert-dismissible').remove();
      
      if (json['error']) {
        $('#content > .container-fluid').prepend('<div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i> ' + json['error'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
      }
      
      if (json['success']) {
        $('#content > .container-fluid').prepend('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
        
        $('#setting-modal').modal('hide');
        
        // Sayfayı yenile
        setTimeout(function() {
          location.reload();
        }, 1000);
      }
    },
    error: function(xhr, ajaxOptions, thrownError) {
      alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
    }
  });
}
//--></script>
{{ footer }} 