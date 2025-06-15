{{ header }}{{ column_left }}
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
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
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-question-circle"></i> {{ text_help }}</h3>
      </div>
      <div class="panel-body">
        <ul class="nav nav-tabs">
          <li class="active"><a href="#tab-general" data-toggle="tab">{{ text_general }}</a></li>
          <li><a href="#tab-marketplace" data-toggle="tab">{{ text_marketplace }}</a></li>
          <li><a href="#tab-announcement" data-toggle="tab">{{ text_announcement }}</a></li>
          <li><a href="#tab-user" data-toggle="tab">{{ text_user }}</a></li>
          <li><a href="#tab-theme" data-toggle="tab">{{ text_theme }}</a></li>
          <li><a href="#tab-logs" data-toggle="tab">{{ text_logs }}</a></li>
          <li><a href="#tab-faq" data-toggle="tab">{{ text_faq }}</a></li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane active" id="tab-general">
            <div class="meschain-card">
              <div class="meschain-card-header">
                <h3>{{ text_about }}</h3>
              </div>
              <div class="meschain-card-body">
                <p>{{ text_version }}: {{ version }}</p>
                <p>{{ text_description }}</p>
                <p>{{ text_support }}</p>
              </div>
            </div>
            <div class="meschain-card">
              <div class="meschain-card-header">
                <h3>{{ text_getting_started }}</h3>
              </div>
              <div class="meschain-card-body">
                <ol>
                  <li>{{ text_step_1 }}</li>
                  <li>{{ text_step_2 }}</li>
                  <li>{{ text_step_3 }}</li>
                  <li>{{ text_step_4 }}</li>
                </ol>
              </div>
            </div>
          </div>
          <div class="tab-pane" id="tab-marketplace">
            <div class="meschain-card">
              <div class="meschain-card-header">
                <h3>{{ text_marketplace_integration }}</h3>
              </div>
              <div class="meschain-card-body">
                <p>{{ text_marketplace_desc }}</p>
                <h4>{{ text_supported_marketplaces }}</h4>
                <ul>
                  <li><strong>Trendyol</strong> - {{ text_trendyol_desc }}</li>
                  <li><strong>n11</strong> - {{ text_n11_desc }}</li>
                  <li><strong>Amazon</strong> - {{ text_amazon_desc }}</li>
                  <li><strong>eBay</strong> - {{ text_ebay_desc }}</li>
                  <li><strong>Hepsiburada</strong> - {{ text_hepsiburada_desc }}</li>
                  <li><strong>Ozon</strong> - {{ text_ozon_desc }}</li>
                </ul>
              </div>
            </div>
            <div class="meschain-card">
              <div class="meschain-card-header">
                <h3>{{ text_api_setup }}</h3>
              </div>
              <div class="meschain-card-body">
                <p>{{ text_api_setup_desc }}</p>
                <h4>{{ text_api_security }}</h4>
                <p>{{ text_api_security_desc }}</p>
              </div>
            </div>
          </div>
          <div class="tab-pane" id="tab-announcement">
            <div class="meschain-card">
              <div class="meschain-card-header">
                <h3>{{ text_announcement_system }}</h3>
              </div>
              <div class="meschain-card-body">
                <p>{{ text_announcement_desc }}</p>
                <h4>{{ text_creating_announcements }}</h4>
                <ol>
                  <li>{{ text_announcement_step_1 }}</li>
                  <li>{{ text_announcement_step_2 }}</li>
                  <li>{{ text_announcement_step_3 }}</li>
                  <li>{{ text_announcement_step_4 }}</li>
                </ol>
                <h4>{{ text_announcement_templates }}</h4>
                <ul>
                  <li><strong>{{ text_template_classic }}</strong> - {{ text_template_classic_desc }}</li>
                  <li><strong>{{ text_template_warning }}</strong> - {{ text_template_warning_desc }}</li>
                  <li><strong>{{ text_template_success }}</strong> - {{ text_template_success_desc }}</li>
                  <li><strong>{{ text_template_info }}</strong> - {{ text_template_info_desc }}</li>
                  <li><strong>{{ text_template_special }}</strong> - {{ text_template_special_desc }}</li>
                </ul>
              </div>
            </div>
          </div>
          <div class="tab-pane" id="tab-user">
            <div class="meschain-card">
              <div class="meschain-card-header">
                <h3>{{ text_user_settings }}</h3>
              </div>
              <div class="meschain-card-body">
                <p>{{ text_user_settings_desc }}</p>
                <h4>{{ text_profile_management }}</h4>
                <p>{{ text_profile_management_desc }}</p>
                <h4>{{ text_permissions }}</h4>
                <p>{{ text_permissions_desc }}</p>
              </div>
            </div>
          </div>
          <div class="tab-pane" id="tab-theme">
            <div class="meschain-card">
              <div class="meschain-card-header">
                <h3>{{ text_theme_system }}</h3>
              </div>
              <div class="meschain-card-body">
                <p>{{ text_theme_desc }}</p>
                <h4>{{ text_selecting_theme }}</h4>
                <ol>
                  <li>{{ text_theme_step_1 }}</li>
                  <li>{{ text_theme_step_2 }}</li>
                  <li>{{ text_theme_step_3 }}</li>
                </ol>
                <h4>{{ text_customizing_theme }}</h4>
                <p>{{ text_customizing_theme_desc }}</p>
              </div>
            </div>
          </div>
          <div class="tab-pane" id="tab-logs">
            <div class="meschain-card">
              <div class="meschain-card-header">
                <h3>{{ text_log_system }}</h3>
              </div>
              <div class="meschain-card-body">
                <p>{{ text_log_desc }}</p>
                <h4>{{ text_log_locations }}</h4>
                <ul>
                  <li><code>system/storage/logs/meschain.log</code> - {{ text_main_log_desc }}</li>
                  <li><code>system/storage/logs/announcement.log</code> - {{ text_announcement_log_desc }}</li>
                  <li><code>system/storage/logs/user_settings.log</code> - {{ text_user_settings_log_desc }}</li>
                  <li><code>system/storage/logs/trendyol_controller.log</code> - {{ text_trendyol_log_desc }}</li>
                  <li><code>system/storage/logs/error.log</code> - {{ text_error_log_desc }}</li>
                </ul>
                <h4>{{ text_log_format }}</h4>
                <pre>[YYYY-MM-DD HH:MM:SS] [USERNAME/ROLE] [ACTION] [MESSAGE]</pre>
                <h4>{{ text_common_errors }}</h4>
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th>{{ text_error_code }}</th>
                      <th>{{ text_description }}</th>
                      <th>{{ text_solution }}</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>E001</td>
                      <td>{{ text_error_e001 }}</td>
                      <td>{{ text_solution_e001 }}</td>
                    </tr>
                    <tr>
                      <td>A001</td>
                      <td>{{ text_error_a001 }}</td>
                      <td>{{ text_solution_a001 }}</td>
                    </tr>
                    <tr>
                      <td>U001</td>
                      <td>{{ text_error_u001 }}</td>
                      <td>{{ text_solution_u001 }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="tab-pane" id="tab-faq">
            <div class="meschain-card">
              <div class="meschain-card-header">
                <h3>{{ text_faq }}</h3>
              </div>
              <div class="meschain-card-body">
                <div class="panel-group" id="accordion">
                  <div class="panel panel-default">
                    <div class="panel-heading">
                      <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordion" href="#collapse1">{{ text_faq_1 }}</a>
                      </h4>
                    </div>
                    <div id="collapse1" class="panel-collapse collapse">
                      <div class="panel-body">{{ text_faq_answer_1 }}</div>
                    </div>
                  </div>
                  <div class="panel panel-default">
                    <div class="panel-heading">
                      <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordion" href="#collapse2">{{ text_faq_2 }}</a>
                      </h4>
                    </div>
                    <div id="collapse2" class="panel-collapse collapse">
                      <div class="panel-body">{{ text_faq_answer_2 }}</div>
                    </div>
                  </div>
                  <div class="panel panel-default">
                    <div class="panel-heading">
                      <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordion" href="#collapse3">{{ text_faq_3 }}</a>
                      </h4>
                    </div>
                    <div id="collapse3" class="panel-collapse collapse">
                      <div class="panel-body">{{ text_faq_answer_3 }}</div>
                    </div>
                  </div>
                  <div class="panel panel-default">
                    <div class="panel-heading">
                      <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordion" href="#collapse4">{{ text_faq_4 }}</a>
                      </h4>
                    </div>
                    <div id="collapse4" class="panel-collapse collapse">
                      <div class="panel-body">{{ text_faq_answer_4 }}</div>
                    </div>
                  </div>
                  <div class="panel panel-default">
                    <div class="panel-heading">
                      <h4 class="panel-title">
                        <a data-toggle="collapse" data-parent="#accordion" href="#collapse5">{{ text_faq_5 }}</a>
                      </h4>
                    </div>
                    <div id="collapse5" class="panel-collapse collapse">
                      <div class="panel-body">{{ text_faq_answer_5 }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="meschain-card">
              <div class="meschain-card-header">
                <h3>{{ text_support }}</h3>
              </div>
              <div class="meschain-card-body">
                <p>{{ text_support_desc }}</p>
                <p><strong>{{ text_email }}:</strong> support@meschain-sync.com</p>
                <p><strong>{{ text_website }}:</strong> <a href="https://meschain-sync.com/support" target="_blank">https://meschain-sync.com/support</a></p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
{{ footer }} 