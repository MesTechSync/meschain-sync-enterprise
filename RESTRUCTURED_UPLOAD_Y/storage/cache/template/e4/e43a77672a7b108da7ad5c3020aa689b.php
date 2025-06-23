<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* admin/view/template/extension/module/trendyol.twig */
class __TwigTemplate_4529d1a5bfb050c48d61b23eb6ec0c3e extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        echo ($context["header"] ?? null);
        echo ($context["column_left"] ?? null);
        echo "
<div class=\"container-fluid\">
  <div class=\"page-header\">
    <div class=\"container-fluid\">
      <div class=\"pull-right\">
        <button type=\"submit\" form=\"form-module\" data-toggle=\"tooltip\" title=\"Save\" class=\"btn btn-primary\"><i class=\"fa fa-save\"></i></button>
        <a href=\"";
        // line 7
        echo ($context["cancel"] ?? null);
        echo "\" data-toggle=\"tooltip\" title=\"Cancel\" class=\"btn btn-default\"><i class=\"fa fa-reply\"></i></a>
      </div>
      <h1>Trendyol Integration</h1>
      <ul class=\"breadcrumb\">
        ";
        // line 11
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["breadcrumbs"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["breadcrumb"]) {
            // line 12
            echo "        <li><a href=\"";
            echo twig_get_attribute($this->env, $this->source, $context["breadcrumb"], "href", [], "any", false, false, false, 12);
            echo "\">";
            echo twig_get_attribute($this->env, $this->source, $context["breadcrumb"], "text", [], "any", false, false, false, 12);
            echo "</a></li>
        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['breadcrumb'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 14
        echo "      </ul>
    </div>
  </div>
  <div class=\"container-fluid\">
    ";
        // line 18
        if (($context["error_warning"] ?? null)) {
            // line 19
            echo "    <div class=\"alert alert-danger alert-dismissible\"><i class=\"fa fa-exclamation-circle\"></i> ";
            echo ($context["error_warning"] ?? null);
            echo "
      <button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
    </div>
    ";
        }
        // line 23
        echo "    ";
        if (($context["success"] ?? null)) {
            // line 24
            echo "    <div class=\"alert alert-success alert-dismissible\"><i class=\"fa fa-check-circle\"></i> ";
            echo ($context["success"] ?? null);
            echo "
      <button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
    </div>
    ";
        }
        // line 28
        echo "    <div class=\"panel panel-default\">
      <div class=\"panel-heading\">
        <h3 class=\"panel-title\"><i class=\"fa fa-shopping-cart\"></i> Trendyol Settings</h3>
      </div>
      <div class=\"panel-body\">
        <form action=\"";
        // line 33
        echo ($context["action"] ?? null);
        echo "\" method=\"post\" enctype=\"multipart/form-data\" id=\"form-module\" class=\"form-horizontal\">
          <div class=\"form-group required\">
            <label class=\"col-sm-2 control-label\" for=\"input-status\">Status</label>
            <div class=\"col-sm-10\">
              <select name=\"module_trendyol_status\" id=\"input-status\" class=\"form-control\">
                ";
        // line 38
        if (($context["module_trendyol_status"] ?? null)) {
            // line 39
            echo "                <option value=\"1\" selected=\"selected\">Enabled</option>
                <option value=\"0\">Disabled</option>
                ";
        } else {
            // line 42
            echo "                <option value=\"1\">Enabled</option>
                <option value=\"0\" selected=\"selected\">Disabled</option>
                ";
        }
        // line 45
        echo "              </select>
            </div>
          </div>
          <div class=\"form-group required\">
            <label class=\"col-sm-2 control-label\" for=\"input-api-key\"><span data-toggle=\"tooltip\" title=\"Get this from Trendyol Partner Panel\">API Key</span></label>
            <div class=\"col-sm-10\">
              <input type=\"text\" name=\"module_trendyol_api_key\" value=\"";
        // line 51
        echo ($context["module_trendyol_api_key"] ?? null);
        echo "\" placeholder=\"Enter your Trendyol API Key\" id=\"input-api-key\" class=\"form-control\" />
              ";
        // line 52
        if (($context["error_api_key"] ?? null)) {
            // line 53
            echo "              <div class=\"text-danger\">";
            echo ($context["error_api_key"] ?? null);
            echo "</div>
              ";
        }
        // line 55
        echo "            </div>
          </div>
          <div class=\"form-group required\">
            <label class=\"col-sm-2 control-label\" for=\"input-api-secret\"><span data-toggle=\"tooltip\" title=\"Get this from Trendyol Partner Panel\">API Secret</span></label>
            <div class=\"col-sm-10\">
              <input type=\"text\" name=\"module_trendyol_api_secret\" value=\"";
        // line 60
        echo ($context["module_trendyol_api_secret"] ?? null);
        echo "\" placeholder=\"Enter your Trendyol API Secret\" id=\"input-api-secret\" class=\"form-control\" />
              ";
        // line 61
        if (($context["error_api_secret"] ?? null)) {
            // line 62
            echo "              <div class=\"text-danger\">";
            echo ($context["error_api_secret"] ?? null);
            echo "</div>
              ";
        }
        // line 64
        echo "            </div>
          </div>
          <div class=\"form-group required\">
            <label class=\"col-sm-2 control-label\" for=\"input-supplier-id\"><span data-toggle=\"tooltip\" title=\"Your Trendyol Supplier ID\">Supplier ID</span></label>
            <div class=\"col-sm-10\">
              <input type=\"text\" name=\"module_trendyol_supplier_id\" value=\"";
        // line 69
        echo ($context["module_trendyol_supplier_id"] ?? null);
        echo "\" placeholder=\"Enter your Trendyol Supplier ID\" id=\"input-supplier-id\" class=\"form-control\" />
              ";
        // line 70
        if (($context["error_supplier_id"] ?? null)) {
            // line 71
            echo "              <div class=\"text-danger\">";
            echo ($context["error_supplier_id"] ?? null);
            echo "</div>
              ";
        }
        // line 73
        echo "            </div>
          </div>
          <div class=\"form-group\">
            <label class=\"col-sm-2 control-label\" for=\"input-test-mode\">Test Mode</label>
            <div class=\"col-sm-10\">
              <select name=\"module_trendyol_test_mode\" id=\"input-test-mode\" class=\"form-control\">
                ";
        // line 79
        if (($context["module_trendyol_test_mode"] ?? null)) {
            // line 80
            echo "                <option value=\"1\" selected=\"selected\">Enabled</option>
                <option value=\"0\">Disabled</option>
                ";
        } else {
            // line 83
            echo "                <option value=\"1\">Enabled</option>
                <option value=\"0\" selected=\"selected\">Disabled</option>
                ";
        }
        // line 86
        echo "              </select>
              <div class=\"help-block\">Enable test mode for development and testing</div>
            </div>
          </div>
          <div class=\"form-group\">
            <label class=\"col-sm-2 control-label\" for=\"input-auto-sync\">Auto Sync</label>
            <div class=\"col-sm-10\">
              <select name=\"module_trendyol_auto_sync\" id=\"input-auto-sync\" class=\"form-control\">
                ";
        // line 94
        if (($context["module_trendyol_auto_sync"] ?? null)) {
            // line 95
            echo "                <option value=\"1\" selected=\"selected\">Enabled</option>
                <option value=\"0\">Disabled</option>
                ";
        } else {
            // line 98
            echo "                <option value=\"1\">Enabled</option>
                <option value=\"0\" selected=\"selected\">Disabled</option>
                ";
        }
        // line 101
        echo "              </select>
              <div class=\"help-block\">Automatically sync orders and inventory</div>
            </div>
          </div>
        </form>
        <div class=\"alert alert-info\">
          <h4><i class=\"fa fa-info-circle\"></i> Trendyol Integration Guide</h4>
          <p>1. Get your API credentials from <strong>Trendyol Partner Panel</strong></p>
          <p>2. Enter your <strong>API Key</strong>, <strong>API Secret</strong> and <strong>Supplier ID</strong></p>
          <p>3. Enable <strong>Test Mode</strong> for development</p>
          <p>4. Enable <strong>Auto Sync</strong> to automatically sync orders and inventory</p>
          <p>5. Save settings and test the connection</p>
        </div>
      </div>
    </div>
  </div>
</div>
";
        // line 118
        echo ($context["footer"] ?? null);
    }

    public function getTemplateName()
    {
        return "admin/view/template/extension/module/trendyol.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  242 => 118,  223 => 101,  218 => 98,  213 => 95,  211 => 94,  201 => 86,  196 => 83,  191 => 80,  189 => 79,  181 => 73,  175 => 71,  173 => 70,  169 => 69,  162 => 64,  156 => 62,  154 => 61,  150 => 60,  143 => 55,  137 => 53,  135 => 52,  131 => 51,  123 => 45,  118 => 42,  113 => 39,  111 => 38,  103 => 33,  96 => 28,  88 => 24,  85 => 23,  77 => 19,  75 => 18,  69 => 14,  58 => 12,  54 => 11,  47 => 7,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "admin/view/template/extension/module/trendyol.twig", "/Users/mezbjen/Desktop/meschain-sync-enterprise-1/RESTRUCTURED_UPLOAD/opencart4/admin/view/template/extension/module/trendyol.twig");
    }
}
