{{ header }}{{ column_left }}
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-announcement" data-toggle="tooltip" title="{{ button_save }}" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="{{ cancel }}" data-toggle="tooltip" title="{{ button_cancel }}" class="btn btn-default"><i class="fa fa-reply"></i></a>
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
    {% if error_warning %}
    <div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i> {{ error_warning }}
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    {% endif %}
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> {{ text_form }}</h3>
      </div>
      <div class="panel-body">
        <form action="{{ action }}" method="post" enctype="multipart/form-data" id="form-announcement" class="form-horizontal">
          {% if announcement.announcement_id %}
          <input type="hidden" name="announcement_id" value="{{ announcement.announcement_id }}" />
          {% endif %}
          
          <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-title">{{ entry_title }}</label>
            <div class="col-sm-10">
              <input type="text" name="title" value="{{ announcement.title }}" placeholder="{{ entry_title }}" id="input-title" class="form-control" />
              {% if error_title %}
              <div class="text-danger">{{ error_title }}</div>
              {% endif %}
            </div>
          </div>
          
          <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-content">{{ entry_content }}</label>
            <div class="col-sm-10">
              <textarea name="content" placeholder="{{ entry_content }}" id="input-content" data-toggle="summernote" class="form-control">{{ announcement.content }}</textarea>
              {% if error_content %}
              <div class="text-danger">{{ error_content }}</div>
              {% endif %}
            </div>
          </div>
          
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-roles">{{ entry_roles }}</label>
            <div class="col-sm-10">
              <div class="well well-sm" style="height: 150px; overflow: auto;">
                {% for user_role in user_roles %}
                <div class="checkbox">
                  <label>
                    {% if user_role.user_group_id in announcement.roles %}
                    <input type="checkbox" name="roles[]" value="{{ user_role.user_group_id }}" checked="checked" />
                    {{ user_role.name }}
                    {% else %}
                    <input type="checkbox" name="roles[]" value="{{ user_role.user_group_id }}" />
                    {{ user_role.name }}
                    {% endif %}
                  </label>
                </div>
                {% endfor %}
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-date">{{ entry_date }}</label>
            <div class="col-sm-10">
              <div class="input-group date">
                <input type="text" name="date" value="{{ announcement.date }}" placeholder="{{ entry_date }}" data-date-format="YYYY-MM-DD" id="input-date" class="form-control" />
                <span class="input-group-btn">
                  <button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>
                </span>
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-expire">{{ entry_expire }}</label>
            <div class="col-sm-10">
              <div class="input-group date">
                <input type="text" name="expire" value="{{ announcement.expire }}" placeholder="{{ entry_expire }}" data-date-format="YYYY-MM-DD" id="input-expire" class="form-control" />
                <span class="input-group-btn">
                  <button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>
                </span>
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-active">{{ entry_active }}</label>
            <div class="col-sm-10">
              <select name="active" id="input-active" class="form-control">
                {% if announcement.active %}
                <option value="1" selected="selected">{{ text_yes }}</option>
                <option value="0">{{ text_no }}</option>
                {% else %}
                <option value="1">{{ text_yes }}</option>
                <option value="0" selected="selected">{{ text_no }}</option>
                {% endif %}
              </select>
            </div>
          </div>
          
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-template">{{ entry_template }}</label>
            <div class="col-sm-10">
              <select name="template" id="input-template" class="form-control">
                {% for template_key, template_name in templates %}
                {% if template_key == announcement.template %}
                <option value="{{ template_key }}" selected="selected">{{ template_name }}</option>
                {% else %}
                <option value="{{ template_key }}">{{ template_name }}</option>
                {% endif %}
                {% endfor %}
              </select>
            </div>
          </div>
          
          <div class="form-group">
            <label class="col-sm-2 control-label">Ekler</label>
            <div class="col-sm-10">
              <button type="button" id="button-upload" class="btn btn-primary"><i class="fa fa-upload"></i> Dosya Yükle</button>
              <div id="attachments" class="well well-sm" style="margin-top: 15px;">
                {% if announcement.attachments %}
                {% for attachment in announcement.attachments %}
                <div id="attachment-{{ loop.index }}">
                  <button type="button" class="btn btn-danger btn-xs pull-right" onclick="$(this).parent().remove();"><i class="fa fa-trash-o"></i></button>
                  <i class="fa fa-file-o"></i> {{ attachment }}
                  <input type="hidden" name="attachments[]" value="{{ attachment }}" />
                </div>
                {% endfor %}
                {% endif %}
              </div>
            </div>
          </div>
          
        </form>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
// Summernote için
$(document).ready(function() {
  $('[data-toggle=\'summernote\']').summernote({
    height: 300,
    toolbar: [
      ['style', ['style']],
      ['font', ['bold', 'underline', 'clear']],
      ['fontname', ['fontname']],
      ['color', ['color']],
      ['para', ['ul', 'ol', 'paragraph']],
      ['table', ['table']],
      ['insert', ['link', 'image', 'video']],
      ['view', ['fullscreen', 'codeview', 'help']]
    ]
  });
});

// Tarih seçici
$('.date').datetimepicker({
  pickTime: false
});

// Dosya yükleme
$('#button-upload').on('click', function() {
  $('#form-upload').remove();
  
  $('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file" /></form>');
  
  $('#form-upload input[name=\'file\']').trigger('click');
  
  if (typeof timer != 'undefined') {
    clearInterval(timer);
  }
  
  timer = setInterval(function() {
    if ($('#form-upload input[name=\'file\']').val() != '') {
      clearInterval(timer);
      
      $.ajax({
        url: 'index.php?route=extension/module/announcement/upload&user_token={{ user_token }}',
        type: 'post',
        dataType: 'json',
        data: new FormData($('#form-upload')[0]),
        cache: false,
        contentType: false,
        processData: false,
        beforeSend: function() {
          $('#button-upload').button('loading');
        },
        complete: function() {
          $('#button-upload').button('reset');
        },
        success: function(json) {
          if (json['error']) {
            alert(json['error']);
          }
          
          if (json['success']) {
            alert(json['success']);
            
            var attachment_id = $('#attachments div').length + 1;
            
            $('#attachments').append('<div id="attachment-' + attachment_id + '"><button type="button" class="btn btn-danger btn-xs pull-right" onclick="$(this).parent().remove();"><i class="fa fa-trash-o"></i></button><i class="fa fa-file-o"></i> ' + json['filename'] + '<input type="hidden" name="attachments[]" value="' + json['filename'] + '" /></div>');
          }
        },
        error: function(xhr, ajaxOptions, thrownError) {
          alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
      });
    }
  }, 500);
});
//--></script>
{{ footer }} 