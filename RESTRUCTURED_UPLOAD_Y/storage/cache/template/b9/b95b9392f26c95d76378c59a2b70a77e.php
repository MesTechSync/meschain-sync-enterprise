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
<div class=\"container-fluid\">
  <div class=\"page-header\">
    <div class=\"container-fluid\">
      <div class=\"pull-right\">
        <a href=\"";
        // line 6
        echo ($context["back"] ?? null);
        echo "\" data-toggle=\"tooltip\" title=\"Back\" class=\"btn btn-default\"><i class=\"fa fa-reply\"></i></a>
      </div>
      <h1>MesChain SYNC</h1>
      <ul class=\"breadcrumb\">
        ";
        // line 10
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["breadcrumbs"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["breadcrumb"]) {
            // line 11
            echo "        <li><a href=\"";
            echo twig_get_attribute($this->env, $this->source, $context["breadcrumb"], "href", [], "any", false, false, false, 11);
            echo "\">";
            echo twig_get_attribute($this->env, $this->source, $context["breadcrumb"], "text", [], "any", false, false, false, 11);
            echo "</a></li>
        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['breadcrumb'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 13
        echo "      </ul>
    </div>
  </div>
  <div class=\"container-fluid\">
    <div class=\"panel panel-default\">
      <div class=\"panel-heading\">
        <h3 class=\"panel-title\"><i class=\"fa fa-puzzle-piece\"></i> MesChain SYNC Category</h3>
      </div>
      <div class=\"panel-body\">
        <div class=\"well\">
          <p>This section contains all MesChain marketplace synchronization extensions.</p>
        </div>
        <div class=\"table-responsive\">
          <table class=\"table table-bordered table-hover\">
            <thead>
              <tr>
                <td class=\"text-left\">Extension Name</td>
                <td class=\"text-left\">Status</td>
                <td class=\"text-right\">Action</td>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class=\"text-left\">
                  <strong>MesChain-Sync Enterprise</strong><br/>
                  <small>Multi-marketplace synchronization platform for Trendyol, Hepsiburada, Amazon, N11, eBay and more</small>
                </td>
                <td class=\"text-left\">
                  <span class=\"label label-success\">Enabled</span>
                </td>
                <td class=\"text-right\">
                  <a href=\"index.php?route=extension/module/meschain_sync&user_token=";
        // line 44
        echo ($context["user_token"] ?? null);
        echo "\" 
                     data-toggle=\"tooltip\" title=\"Edit\" class=\"btn btn-primary\">
                    <i class=\"fa fa-pencil\"></i> Edit
                  </a>
                </td>
              </tr>
              <tr>
                <td class=\"text-left\">
                  <strong>Trendyol Integration</strong><br/>
                  <small>Direct integration with Trendyol marketplace API</small>
                </td>
                <td class=\"text-left\">
                  <span class=\"label label-success\">Enabled</span>
                </td>
                <td class=\"text-right\">
                  <a href=\"index.php?route=extension/module/trendyol&user_token=";
        // line 59
        echo ($context["user_token"] ?? null);
        echo "\" 
                     data-toggle=\"tooltip\" title=\"Edit\" class=\"btn btn-primary\">
                    <i class=\"fa fa-pencil\"></i> Edit
                  </a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class=\"alert alert-info\">
          <i class=\"fa fa-info-circle\"></i> Ready to use! Click the Edit button above to configure your marketplace synchronization settings.
        </div>
      </div>
    </div>
  </div>
</div>
";
        // line 75
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
        return array (  138 => 75,  119 => 59,  101 => 44,  68 => 13,  57 => 11,  53 => 10,  46 => 6,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "admin/view/template/extension/meschain_sync.twig", "/Users/mezbjen/Desktop/meschain-sync-enterprise-1/RESTRUCTURED_UPLOAD/opencart4/admin/view/template/extension/meschain_sync.twig");
    }
}
