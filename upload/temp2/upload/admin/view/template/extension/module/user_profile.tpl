{{ header }}{{ column_left }}
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-profile" data-toggle="tooltip" title="{{ text_save }}" class="btn btn-primary"><i class="fa fa-save"></i></button>
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
    
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-user"></i> {{ text_profile }}</h3>
      </div>
      <div class="panel-body">
        <form action="{{ action }}" method="post" enctype="multipart/form-data" id="form-profile" class="form-horizontal">
          <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-username">{{ text_username }}</label>
            <div class="col-sm-10">
              <input type="text" name="username" value="{{ username }}" placeholder="{{ text_username }}" id="input-username" class="form-control" />
              {% if error_username %}
              <div class="text-danger">{{ error_username }}</div>
              {% endif %}
            </div>
          </div>
          <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-firstname">{{ text_firstname }}</label>
            <div class="col-sm-10">
              <input type="text" name="firstname" value="{{ firstname }}" placeholder="{{ text_firstname }}" id="input-firstname" class="form-control" />
              {% if error_firstname %}
              <div class="text-danger">{{ error_firstname }}</div>
              {% endif %}
            </div>
          </div>
          <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-lastname">{{ text_lastname }}</label>
            <div class="col-sm-10">
              <input type="text" name="lastname" value="{{ lastname }}" placeholder="{{ text_lastname }}" id="input-lastname" class="form-control" />
              {% if error_lastname %}
              <div class="text-danger">{{ error_lastname }}</div>
              {% endif %}
            </div>
          </div>
          <div class="form-group required">
            <label class="col-sm-2 control-label" for="input-email">{{ text_email }}</label>
            <div class="col-sm-10">
              <input type="text" name="email" value="{{ email }}" placeholder="{{ text_email }}" id="input-email" class="form-control" />
              {% if error_email %}
              <div class="text-danger">{{ error_email }}</div>
              {% endif %}
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-password">{{ text_password }}</label>
            <div class="col-sm-10">
              <input type="password" name="password" value="{{ password }}" placeholder="{{ text_password }}" id="input-password" class="form-control" autocomplete="off" />
              {% if error_password %}
              <div class="text-danger">{{ error_password }}</div>
              {% endif %}
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-confirm">{{ text_confirm }}</label>
            <div class="col-sm-10">
              <input type="password" name="confirm" value="{{ confirm }}" placeholder="{{ text_confirm }}" id="input-confirm" class="form-control" />
              {% if error_confirm %}
              <div class="text-danger">{{ error_confirm }}</div>
              {% endif %}
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
{{ footer }} 