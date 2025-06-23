{{ header }}{{ column_left }}
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <a href="{{ add }}" data-toggle="tooltip" title="{{ button_add }}" class="btn btn-primary"><i class="fa fa-plus"></i></a>
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
        <h3 class="panel-title"><i class="fa fa-list"></i> {{ text_list }}</h3>
      </div>
      <div class="panel-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover meschain-table">
            <thead>
              <tr>
                <th>{{ column_title }}</th>
                <th>{{ column_content }}</th>
                <th>{{ column_roles }}</th>
                <th>{{ column_date }}</th>
                <th>{{ column_expire }}</th>
                <th>{{ column_active }}</th>
                <th class="text-right">{{ column_action }}</th>
              </tr>
            </thead>
            <tbody>
              {% if announcements %}
              {% for announcement in announcements %}
              <tr>
                <td>{{ announcement.title }}</td>
                <td>{{ announcement.content }}</td>
                <td>{{ announcement.roles }}</td>
                <td>{{ announcement.date }}</td>
                <td>{{ announcement.expire }}</td>
                <td>{{ announcement.active }}</td>
                <td class="text-right">
                  <a href="{{ announcement.edit }}" data-toggle="tooltip" title="{{ button_edit }}" class="btn btn-primary"><i class="fa fa-pencil"></i></a>
                  <a href="{{ announcement.delete }}" data-toggle="tooltip" title="{{ button_delete }}" class="btn btn-danger" onclick="return confirm('{{ text_confirm }}');"><i class="fa fa-trash-o"></i></a>
                </td>
              </tr>
              {% endfor %}
              {% else %}
              <tr>
                <td class="text-center" colspan="7">{{ text_no_results }}</td>
              </tr>
              {% endif %}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
{{ footer }} 