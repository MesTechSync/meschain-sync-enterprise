<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* admin/view/template/extension/meschain/trendyol.twig */
class __TwigTemplate_be862a4dfe67f2d69f3d1b10fa16f534 extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 1
        yield ($context["header"] ?? null);
        yield ($context["column_left"] ?? null);
        yield "
<div id=\"content\">
  <div class=\"page-header\">
    <div class=\"container-fluid\">
      <div class=\"float-end\">
        <button type=\"submit\" form=\"form-trendyol\" data-bs-toggle=\"tooltip\" title=\"";
        // line 6
        yield ($context["button_save"] ?? null);
        yield "\" class=\"btn btn-primary\">
          <i class=\"fa-solid fa-save\"></i>
        </button>
        <a href=\"";
        // line 9
        yield ($context["cancel"] ?? null);
        yield "\" data-bs-toggle=\"tooltip\" title=\"";
        yield ($context["button_cancel"] ?? null);
        yield "\" class=\"btn btn-secondary\">
          <i class=\"fa-solid fa-reply\"></i>
        </a>
      </div>
      <h1>";
        // line 13
        yield ($context["heading_title"] ?? null);
        yield "</h1>
      <ul class=\"breadcrumb\">
        ";
        // line 15
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["breadcrumbs"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["breadcrumb"]) {
            // line 16
            yield "        <li class=\"breadcrumb-item\"><a href=\"";
            yield CoreExtension::getAttribute($this->env, $this->source, $context["breadcrumb"], "href", [], "any", false, false, false, 16);
            yield "\">";
            yield CoreExtension::getAttribute($this->env, $this->source, $context["breadcrumb"], "text", [], "any", false, false, false, 16);
            yield "</a></li>
        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['breadcrumb'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 18
        yield "      </ul>
    </div>
  </div>
  <div class=\"container-fluid\">
    ";
        // line 22
        if ((($tmp = ($context["error_warning"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 23
            yield "    <div class=\"alert alert-danger alert-dismissible\"><i class=\"fa-solid fa-circle-exclamation\"></i> ";
            yield ($context["error_warning"] ?? null);
            yield "
      <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>
    </div>
    ";
        }
        // line 27
        yield "    ";
        if ((($tmp = ($context["success"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 28
            yield "    <div class=\"alert alert-success alert-dismissible\"><i class=\"fa-solid fa-circle-check\"></i> ";
            yield ($context["success"] ?? null);
            yield "
      <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>
    </div>
    ";
        }
        // line 32
        yield "    
    <div class=\"card\">
      <div class=\"card-header\">
        <i class=\"fa-solid fa-pencil\"></i> ";
        // line 35
        yield ($context["text_edit"] ?? null);
        yield "
      </div>
      <div class=\"card-body\">
        <form id=\"form-trendyol\" action=\"";
        // line 38
        yield ($context["action"] ?? null);
        yield "\" method=\"post\" data-oc-toggle=\"ajax\">
          <ul class=\"nav nav-tabs\">
            <li class=\"nav-item\"><a class=\"nav-link active\" href=\"#tab-general\" data-bs-toggle=\"tab\">";
        // line 40
        yield ($context["tab_general"] ?? null);
        yield "</a></li>
            <li class=\"nav-item\"><a class=\"nav-link\" href=\"#tab-api\" data-bs-toggle=\"tab\">";
        // line 41
        yield ($context["tab_api"] ?? null);
        yield "</a></li>
            <li class=\"nav-item\"><a class=\"nav-link\" href=\"#tab-products\" data-bs-toggle=\"tab\">";
        // line 42
        yield ($context["tab_products"] ?? null);
        yield "</a></li>
            <li class=\"nav-item\"><a class=\"nav-link\" href=\"#tab-orders\" data-bs-toggle=\"tab\">";
        // line 43
        yield ($context["tab_orders"] ?? null);
        yield "</a></li>
            <li class=\"nav-item\"><a class=\"nav-link\" href=\"#tab-logs\" data-bs-toggle=\"tab\">";
        // line 44
        yield ($context["tab_logs"] ?? null);
        yield "</a></li>
          </ul>
          
          <div class=\"tab-content\">
            <div class=\"tab-pane fade show active\" id=\"tab-general\">
              <div class=\"row mb-3\">
                <label for=\"input-status\" class=\"col-sm-2 col-form-label\">";
        // line 50
        yield ($context["entry_status"] ?? null);
        yield "</label>
                <div class=\"col-sm-10\">
                  <div class=\"form-check form-switch form-switch-lg\">
                    <input type=\"hidden\" name=\"meschain_trendyol_status\" value=\"0\"/>
                    <input type=\"checkbox\" name=\"meschain_trendyol_status\" value=\"1\" id=\"input-status\" class=\"form-check-input\"";
        // line 54
        if ((($tmp = ($context["meschain_trendyol_status"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield " checked";
        }
        yield "/>
                  </div>
                  <div class=\"form-text\">";
        // line 56
        yield ($context["help_status"] ?? null);
        yield "</div>
                </div>
              </div>
              
              <div class=\"row mb-3\">
                <label for=\"input-debug\" class=\"col-sm-2 col-form-label\">";
        // line 61
        yield ($context["entry_debug"] ?? null);
        yield "</label>
                <div class=\"col-sm-10\">
                  <div class=\"form-check form-switch form-switch-lg\">
                    <input type=\"hidden\" name=\"meschain_trendyol_debug\" value=\"0\"/>
                    <input type=\"checkbox\" name=\"meschain_trendyol_debug\" value=\"1\" id=\"input-debug\" class=\"form-check-input\"";
        // line 65
        if ((($tmp = ($context["meschain_trendyol_debug"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield " checked";
        }
        yield "/>
                  </div>
                  <div class=\"form-text\">";
        // line 67
        yield ($context["help_debug"] ?? null);
        yield "</div>
                </div>
              </div>
            </div>
            
            <div class=\"tab-pane fade\" id=\"tab-api\">
              <div class=\"row mb-3\">
                <label for=\"input-api-key\" class=\"col-sm-2 col-form-label\">";
        // line 74
        yield ($context["entry_api_key"] ?? null);
        yield "</label>
                <div class=\"col-sm-10\">
                  <input type=\"text\" name=\"meschain_trendyol_api_key\" value=\"";
        // line 76
        yield ($context["meschain_trendyol_api_key"] ?? null);
        yield "\" placeholder=\"";
        yield ($context["entry_api_key"] ?? null);
        yield "\" id=\"input-api-key\" class=\"form-control\"/>
                  <div class=\"form-text\">";
        // line 77
        yield ($context["help_api_key"] ?? null);
        yield "</div>
                </div>
              </div>
              
              <div class=\"row mb-3\">
                <label for=\"input-api-secret\" class=\"col-sm-2 col-form-label\">";
        // line 82
        yield ($context["entry_api_secret"] ?? null);
        yield "</label>
                <div class=\"col-sm-10\">
                  <input type=\"password\" name=\"meschain_trendyol_api_secret\" value=\"";
        // line 84
        yield ($context["meschain_trendyol_api_secret"] ?? null);
        yield "\" placeholder=\"";
        yield ($context["entry_api_secret"] ?? null);
        yield "\" id=\"input-api-secret\" class=\"form-control\"/>
                  <div class=\"form-text\">";
        // line 85
        yield ($context["help_api_secret"] ?? null);
        yield "</div>
                </div>
              </div>
              
              <div class=\"row mb-3\">
                <label for=\"input-supplier-id\" class=\"col-sm-2 col-form-label\">";
        // line 90
        yield ($context["entry_supplier_id"] ?? null);
        yield "</label>
                <div class=\"col-sm-10\">
                  <input type=\"text\" name=\"meschain_trendyol_supplier_id\" value=\"";
        // line 92
        yield ($context["meschain_trendyol_supplier_id"] ?? null);
        yield "\" placeholder=\"";
        yield ($context["entry_supplier_id"] ?? null);
        yield "\" id=\"input-supplier-id\" class=\"form-control\"/>
                  <div class=\"form-text\">";
        // line 93
        yield ($context["help_supplier_id"] ?? null);
        yield "</div>
                </div>
              </div>
              
              <div class=\"row mb-3\">
                <label class=\"col-sm-2 col-form-label\">";
        // line 98
        yield ($context["entry_test_connection"] ?? null);
        yield "</label>
                <div class=\"col-sm-10\">
                  <button type=\"button\" id=\"button-test-connection\" class=\"btn btn-info\">
                    <i class=\"fa-solid fa-plug\"></i> ";
        // line 101
        yield ($context["button_test_connection"] ?? null);
        yield "
                  </button>
                  <div id=\"test-result\" class=\"mt-2\"></div>
                </div>
              </div>
            </div>
            
            <div class=\"tab-pane fade\" id=\"tab-products\">
              <div class=\"row mb-3\">
                <label for=\"input-auto-sync\" class=\"col-sm-2 col-form-label\">";
        // line 110
        yield ($context["entry_auto_sync"] ?? null);
        yield "</label>
                <div class=\"col-sm-10\">
                  <div class=\"form-check form-switch form-switch-lg\">
                    <input type=\"hidden\" name=\"meschain_trendyol_auto_sync\" value=\"0\"/>
                    <input type=\"checkbox\" name=\"meschain_trendyol_auto_sync\" value=\"1\" id=\"input-auto-sync\" class=\"form-check-input\"";
        // line 114
        if ((($tmp = ($context["meschain_trendyol_auto_sync"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield " checked";
        }
        yield "/>
                  </div>
                  <div class=\"form-text\">";
        // line 116
        yield ($context["help_auto_sync"] ?? null);
        yield "</div>
                </div>
              </div>
              
              <div class=\"row mb-3\">
                <label for=\"input-sync-interval\" class=\"col-sm-2 col-form-label\">";
        // line 121
        yield ($context["entry_sync_interval"] ?? null);
        yield "</label>
                <div class=\"col-sm-10\">
                  <select name=\"meschain_trendyol_sync_interval\" id=\"input-sync-interval\" class=\"form-select\">
                    ";
        // line 124
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["sync_intervals"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["interval"]) {
            // line 125
            yield "                    <option value=\"";
            yield CoreExtension::getAttribute($this->env, $this->source, $context["interval"], "value", [], "any", false, false, false, 125);
            yield "\"";
            if ((CoreExtension::getAttribute($this->env, $this->source, $context["interval"], "value", [], "any", false, false, false, 125) == ($context["meschain_trendyol_sync_interval"] ?? null))) {
                yield " selected";
            }
            yield ">";
            yield CoreExtension::getAttribute($this->env, $this->source, $context["interval"], "text", [], "any", false, false, false, 125);
            yield "</option>
                    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['interval'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 127
        yield "                  </select>
                  <div class=\"form-text\">";
        // line 128
        yield ($context["help_sync_interval"] ?? null);
        yield "</div>
                </div>
              </div>
            </div>
            
            <div class=\"tab-pane fade\" id=\"tab-orders\">
              <div class=\"row mb-3\">
                <label for=\"input-order-status\" class=\"col-sm-2 col-form-label\">";
        // line 135
        yield ($context["entry_order_status"] ?? null);
        yield "</label>
                <div class=\"col-sm-10\">
                  <select name=\"meschain_trendyol_order_status\" id=\"input-order-status\" class=\"form-select\">
                    ";
        // line 138
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["order_statuses"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["order_status"]) {
            // line 139
            yield "                    <option value=\"";
            yield CoreExtension::getAttribute($this->env, $this->source, $context["order_status"], "order_status_id", [], "any", false, false, false, 139);
            yield "\"";
            if ((CoreExtension::getAttribute($this->env, $this->source, $context["order_status"], "order_status_id", [], "any", false, false, false, 139) == ($context["meschain_trendyol_order_status"] ?? null))) {
                yield " selected";
            }
            yield ">";
            yield CoreExtension::getAttribute($this->env, $this->source, $context["order_status"], "name", [], "any", false, false, false, 139);
            yield "</option>
                    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['order_status'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 141
        yield "                  </select>
                  <div class=\"form-text\">";
        // line 142
        yield ($context["help_order_status"] ?? null);
        yield "</div>
                </div>
              </div>
            </div>
            
            <div class=\"tab-pane fade\" id=\"tab-logs\">
              <div class=\"row mb-3\">
                <label for=\"input-log-level\" class=\"col-sm-2 col-form-label\">";
        // line 149
        yield ($context["entry_log_level"] ?? null);
        yield "</label>
                <div class=\"col-sm-10\">
                  <select name=\"meschain_trendyol_log_level\" id=\"input-log-level\" class=\"form-select\">
                    ";
        // line 152
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["log_levels"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["log_level"]) {
            // line 153
            yield "                    <option value=\"";
            yield CoreExtension::getAttribute($this->env, $this->source, $context["log_level"], "value", [], "any", false, false, false, 153);
            yield "\"";
            if ((CoreExtension::getAttribute($this->env, $this->source, $context["log_level"], "value", [], "any", false, false, false, 153) == ($context["meschain_trendyol_log_level"] ?? null))) {
                yield " selected";
            }
            yield ">";
            yield CoreExtension::getAttribute($this->env, $this->source, $context["log_level"], "text", [], "any", false, false, false, 153);
            yield "</option>
                    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['log_level'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 155
        yield "                  </select>
                  <div class=\"form-text\">";
        // line 156
        yield ($context["help_log_level"] ?? null);
        yield "</div>
                </div>
              </div>
              
              <div class=\"row mb-3\">
                <label class=\"col-sm-2 col-form-label\">";
        // line 161
        yield ($context["entry_clear_logs"] ?? null);
        yield "</label>
                <div class=\"col-sm-10\">
                  <button type=\"button\" id=\"button-clear-logs\" class=\"btn btn-warning\">
                    <i class=\"fa-solid fa-trash\"></i> ";
        // line 164
        yield ($context["button_clear_logs"] ?? null);
        yield "
                  </button>
                  <div class=\"form-text\">";
        // line 166
        yield ($context["help_clear_logs"] ?? null);
        yield "</div>
                </div>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script type=\"text/javascript\">
\$('#button-test-connection').on('click', function() {
    var button = \$(this);
    var original = button.html();
    
    button.html('<i class=\"fa-solid fa-spinner fa-spin\"></i> ";
        // line 182
        yield ($context["text_testing"] ?? null);
        yield "');
    button.prop('disabled', true);
    
    \$.ajax({
        url: 'index.php?route=extension/meschain/trendyol.testConnection&user_token=";
        // line 186
        yield ($context["user_token"] ?? null);
        yield "',
        type: 'post',
        data: {
            'api_key': \$('input[name=\"meschain_trendyol_api_key\"]').val(),
            'api_secret': \$('input[name=\"meschain_trendyol_api_secret\"]').val(),
            'supplier_id': \$('input[name=\"meschain_trendyol_supplier_id\"]').val()
        },
        dataType: 'json',
        success: function(json) {
            if (json['success']) {
                \$('#test-result').html('<div class=\"alert alert-success\"><i class=\"fa-solid fa-check-circle\"></i> ' + json['success'] + '</div>');
            } else if (json['error']) {
                \$('#test-result').html('<div class=\"alert alert-danger\"><i class=\"fa-solid fa-exclamation-triangle\"></i> ' + json['error'] + '</div>');
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            \$('#test-result').html('<div class=\"alert alert-danger\"><i class=\"fa-solid fa-exclamation-triangle\"></i> ";
        // line 202
        yield ($context["error_connection"] ?? null);
        yield "</div>');
        },
        complete: function() {
            button.html(original);
            button.prop('disabled', false);
        }
    });
});

\$('#button-clear-logs').on('click', function() {
    if (confirm('";
        // line 212
        yield ($context["text_confirm_clear_logs"] ?? null);
        yield "')) {
        \$.ajax({
            url: 'index.php?route=extension/meschain/trendyol.clearLogs&user_token=";
        // line 214
        yield ($context["user_token"] ?? null);
        yield "',
            type: 'post',
            dataType: 'json',
            success: function(json) {
                if (json['success']) {
                    \$('#content').prepend('<div class=\"alert alert-success alert-dismissible\"><i class=\"fa-solid fa-check-circle\"></i> ' + json['success'] + ' <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>');
                }
            }
        });
    }
});
</script>

";
        // line 227
        yield ($context["footer"] ?? null);
        yield "
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "admin/view/template/extension/meschain/trendyol.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  499 => 227,  483 => 214,  478 => 212,  465 => 202,  446 => 186,  439 => 182,  420 => 166,  415 => 164,  409 => 161,  401 => 156,  398 => 155,  383 => 153,  379 => 152,  373 => 149,  363 => 142,  360 => 141,  345 => 139,  341 => 138,  335 => 135,  325 => 128,  322 => 127,  307 => 125,  303 => 124,  297 => 121,  289 => 116,  282 => 114,  275 => 110,  263 => 101,  257 => 98,  249 => 93,  243 => 92,  238 => 90,  230 => 85,  224 => 84,  219 => 82,  211 => 77,  205 => 76,  200 => 74,  190 => 67,  183 => 65,  176 => 61,  168 => 56,  161 => 54,  154 => 50,  145 => 44,  141 => 43,  137 => 42,  133 => 41,  129 => 40,  124 => 38,  118 => 35,  113 => 32,  105 => 28,  102 => 27,  94 => 23,  92 => 22,  86 => 18,  75 => 16,  71 => 15,  66 => 13,  57 => 9,  51 => 6,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "admin/view/template/extension/meschain/trendyol.twig", "/Users/mezbjen/Desktop/meschain-sync-enterprise-1/RESTRUCTURED_UPLOAD/opencart4_clean/admin/view/template/extension/meschain/trendyol.twig");
    }
}
