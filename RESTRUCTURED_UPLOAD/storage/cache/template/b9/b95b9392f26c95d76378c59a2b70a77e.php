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

/* admin/view/template/extension/meschain_sync.twig */
class __TwigTemplate_e75a30ee3d6351de50adebc521e64a22 extends Template
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
<div id=\"content\">
  <div class=\"page-header\">
    <div class=\"container-fluid\">
      <h1>";
        // line 5
        echo ($context["heading_title"] ?? null);
        echo "</h1>
      <ul class=\"breadcrumb\">
        ";
        // line 7
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["breadcrumbs"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["breadcrumb"]) {
            // line 8
            echo "          <li><a href=\"";
            echo twig_get_attribute($this->env, $this->source, $context["breadcrumb"], "href", [], "any", false, false, false, 8);
            echo "\">";
            echo twig_get_attribute($this->env, $this->source, $context["breadcrumb"], "text", [], "any", false, false, false, 8);
            echo "</a></li>
        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['breadcrumb'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 10
        echo "      </ul>
    </div>
  </div>
  <div class=\"container-fluid\">
    <div class=\"panel panel-default\">
      <div class=\"panel-heading\">
        <h3 class=\"panel-title\"><i class=\"fa fa-puzzle-piece\"></i> ";
        // line 16
        echo ($context["text_list"] ?? null);
        echo "</h3>
      </div>
      <div class=\"panel-body\">
        <div class=\"alert alert-info\">
          <i class=\"fa fa-info-circle\"></i> 
          <strong>MesChain SYNC Category</strong><br>
          This section contains all MesChain marketplace synchronization extensions.
        </div>
        
        <div class=\"table-responsive\">
          <table class=\"table table-bordered table-hover\">
            <thead>
              <tr>
                <td class=\"text-left\">";
        // line 29
        echo ($context["column_name"] ?? null);
        echo "</td>
                <td class=\"text-left\">";
        // line 30
        echo ($context["column_status"] ?? null);
        echo "</td>
                <td class=\"text-right\">";
        // line 31
        echo ($context["column_action"] ?? null);
        echo "</td>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class=\"text-left\">
                  <b>MesChain-Sync Enterprise</b><br>
                  <small>Multi-marketplace synchronization platform for Trendyol, Hepsiburada, Amazon, N11, eBay and more</small>
                </td>
                <td class=\"text-left\">
                  ";
        // line 41
        if ((($context["meschain_sync_status"] ?? null) == ($context["text_enabled"] ?? null))) {
            // line 42
            echo "                    <span class=\"label label-success\">";
            echo ($context["meschain_sync_status"] ?? null);
            echo "</span>
                  ";
        } else {
            // line 44
            echo "                    <span class=\"label label-danger\">";
            echo ($context["meschain_sync_status"] ?? null);
            echo "</span>
                  ";
        }
        // line 46
        echo "                </td>
                <td class=\"text-right\">
                  <a href=\"";
        // line 48
        echo ($context["meschain_sync_edit"] ?? null);
        echo "\" data-toggle=\"tooltip\" title=\"Edit\" class=\"btn btn-primary\">
                    <i class=\"fa fa-pencil\"></i> Edit
                  </a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <div class=\"alert alert-success\">
          <i class=\"fa fa-check-circle\"></i>
          <strong>Ready to use!</strong> Click the Edit button above to configure your marketplace synchronization settings.
        </div>
      </div>
    </div>
  </div>
</div>
";
        // line 65
        echo ($context["footer"] ?? null);
    }

    public function getTemplateName()
    {
        return "admin/view/template/extension/meschain_sync.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  148 => 65,  128 => 48,  124 => 46,  118 => 44,  112 => 42,  110 => 41,  97 => 31,  93 => 30,  89 => 29,  73 => 16,  65 => 10,  54 => 8,  50 => 7,  45 => 5,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "admin/view/template/extension/meschain_sync.twig", "/Users/mezbjen/Desktop/meschain-sync-enterprise-1/RESTRUCTURED_UPLOAD/opencart4/admin/view/template/extension/meschain_sync.twig");
    }
}
