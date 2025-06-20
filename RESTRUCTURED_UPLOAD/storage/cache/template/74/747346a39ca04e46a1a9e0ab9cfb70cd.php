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

/* admin/view/template/common/header.twig */
class __TwigTemplate_b79479562f562b21d281cfb3684641b5 extends Template
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
        echo "<!DOCTYPE html>
<html dir=\"";
        // line 2
        echo ($context["direction"] ?? null);
        echo "\" lang=\"";
        echo ($context["lang"] ?? null);
        echo "\">
<head>
  <meta charset=\"UTF-8\"/>
  <title>";
        // line 5
        echo ($context["title"] ?? null);
        echo "</title>
  <base href=\"";
        // line 6
        echo ($context["base"] ?? null);
        echo "\"/>
  ";
        // line 7
        if (($context["description"] ?? null)) {
            // line 8
            echo "    <meta name=\"description\" content=\"";
            echo ($context["description"] ?? null);
            echo "\"/>
  ";
        }
        // line 10
        echo "  ";
        if (($context["keywords"] ?? null)) {
            // line 11
            echo "    <meta name=\"keywords\" content=\"";
            echo ($context["keywords"] ?? null);
            echo "\"/>
  ";
        }
        // line 13
        echo "  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1, shrink-to-fit=no\"/>
  <meta http-equiv=\"cache-control\" content=\"no-cache\">
  <meta http-equiv=\"expires\" content=\"0\">
  <link href=\"";
        // line 16
        echo ($context["bootstrap"] ?? null);
        echo "\" rel=\"stylesheet\" media=\"screen\"/>
  <link href=\"";
        // line 17
        echo ($context["icons"] ?? null);
        echo "\" rel=\"stylesheet\" type=\"text/css\"/>
  <link href=\"";
        // line 18
        echo ($context["stylesheet"] ?? null);
        echo "\" rel=\"stylesheet\" type=\"text/css\"/>
  <script src=\"";
        // line 19
        echo ($context["jquery"] ?? null);
        echo "\" type=\"text/javascript\"></script>
  <script type=\"text/javascript\" src=\"view/javascript/jquery/datetimepicker/moment.min.js\"></script>
  <script type=\"text/javascript\" src=\"view/javascript/jquery/datetimepicker/moment-with-locales.min.js\"></script>
  <script type=\"text/javascript\" src=\"view/javascript/jquery/datetimepicker/daterangepicker.js\"></script>
  <link href=\"view/javascript/jquery/datetimepicker/daterangepicker.css\" rel=\"stylesheet\" type=\"text/css\"/>
  <script type=\"text/javascript\" src=\"view/javascript/common.js\"></script>
  <script type=\"text/javascript\" src=\"view/javascript/menu_fix.js\"></script>
  ";
        // line 26
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["styles"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["style"]) {
            // line 27
            echo "    <link type=\"text/css\" href=\"";
            echo twig_get_attribute($this->env, $this->source, $context["style"], "href", [], "any", false, false, false, 27);
            echo "\" rel=\"";
            echo twig_get_attribute($this->env, $this->source, $context["style"], "rel", [], "any", false, false, false, 27);
            echo "\" media=\"";
            echo twig_get_attribute($this->env, $this->source, $context["style"], "media", [], "any", false, false, false, 27);
            echo "\"/>
  ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['style'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 29
        echo "  ";
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["links"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["link"]) {
            // line 30
            echo "    <link href=\"";
            echo twig_get_attribute($this->env, $this->source, $context["link"], "href", [], "any", false, false, false, 30);
            echo "\" rel=\"";
            echo twig_get_attribute($this->env, $this->source, $context["link"], "rel", [], "any", false, false, false, 30);
            echo "\"/>
  ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['link'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 32
        echo "  ";
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["scripts"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["script"]) {
            // line 33
            echo "    <script type=\"text/javascript\" src=\"";
            echo twig_get_attribute($this->env, $this->source, $context["script"], "href", [], "any", false, false, false, 33);
            echo "\"></script>
  ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['script'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 35
        echo "</head>
<body>
<div id=\"container\">
  <div id=\"alert\" class=\"toast-container position-fixed top-0 end-0 p-3\"></div>
  <header id=\"header\" class=\"navbar navbar-expand navbar-light bg-light\">
    <div class=\"container-fluid\">
      <a href=\"";
        // line 41
        echo ($context["home"] ?? null);
        echo "\" class=\"navbar-brand d-none d-lg-block\"><img src=\"view/image/logo.png\" alt=\"";
        echo ($context["heading_title"] ?? null);
        echo "\" title=\"";
        echo ($context["heading_title"] ?? null);
        echo "\"/></a>
      ";
        // line 42
        if (($context["logged"] ?? null)) {
            // line 43
            echo "        <button type=\"button\" id=\"button-menu\" class=\"btn btn-link d-inline-block d-lg-none\"><i class=\"fa-solid fa-bars\"></i></button>
        <ul class=\"nav navbar-nav\">
          <li id=\"nav-notification\" class=\"nav-item dropdown\">
            <a href=\"#\" data-bs-toggle=\"dropdown\" class=\"nav-link dropdown-toggle\"><i class=\"fa-regular fa-bell\"></i>";
            // line 46
            if (($context["notification_total"] ?? null)) {
                echo " <span class=\"badge bg-danger\">";
                echo ($context["notification_total"] ?? null);
                echo "</span>";
            }
            echo "</a>
            <div class=\"dropdown-menu dropdown-menu-end\">
              ";
            // line 48
            if (($context["notifications"] ?? null)) {
                // line 49
                echo "                ";
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable(($context["notifications"] ?? null));
                foreach ($context['_seq'] as $context["_key"] => $context["notification"]) {
                    // line 50
                    echo "                  <a href=\"";
                    echo twig_get_attribute($this->env, $this->source, $context["notification"], "href", [], "any", false, false, false, 50);
                    echo "\" data-bs-toggle=\"modal\" class=\"dropdown-item\">";
                    echo twig_get_attribute($this->env, $this->source, $context["notification"], "title", [], "any", false, false, false, 50);
                    echo "</a>
                ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['notification'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 52
                echo "                <a href=\"";
                echo ($context["notification_all"] ?? null);
                echo "\" class=\"dropdown-item text-center text-primary\">";
                echo ($context["text_notification_all"] ?? null);
                echo "</a>
              ";
            } else {
                // line 54
                echo "                <span class=\"dropdown-item text-center\">";
                echo ($context["text_no_results"] ?? null);
                echo "</span>
              ";
            }
            // line 56
            echo "            </div>
          </li>
          <li id=\"nav-language\" class=\"nav-item dropdown\">";
            // line 58
            echo ($context["language"] ?? null);
            echo "</li>
          <li id=\"nav-profile\" class=\"nav-item dropdown\">
            <a href=\"#\" data-bs-toggle=\"dropdown\" class=\"nav-link dropdown-toggle\"><img src=\"";
            // line 60
            echo ($context["image"] ?? null);
            echo "\" alt=\"";
            echo ($context["firstname"] ?? null);
            echo " ";
            echo ($context["lastname"] ?? null);
            echo "\" title=\"";
            echo ($context["username"] ?? null);
            echo "\" class=\"rounded-circle\"/><span class=\"d-none d-md-inline d-lg-inline\">&nbsp;&nbsp;&nbsp;";
            echo ($context["firstname"] ?? null);
            echo " ";
            echo ($context["lastname"] ?? null);
            echo " <i class=\"fa-solid fa-caret-down fa-fw\"></i></span></a>
            <ul class=\"dropdown-menu dropdown-menu-end\">
              <li><a href=\"";
            // line 62
            echo ($context["profile"] ?? null);
            echo "\" class=\"dropdown-item\"><i class=\"fa-solid fa-user-circle fa-fw\"></i> ";
            echo ($context["text_profile"] ?? null);
            echo "</a></li>
              <li><hr class=\"dropdown-divider\"></li>
              <li><h6 class=\"dropdown-header\">";
            // line 64
            echo ($context["text_store"] ?? null);
            echo "</h6></li>
              ";
            // line 65
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["stores"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["store"]) {
                // line 66
                echo "                <a href=\"";
                echo twig_get_attribute($this->env, $this->source, $context["store"], "href", [], "any", false, false, false, 66);
                echo "\" target=\"_blank\" class=\"dropdown-item\">";
                echo twig_get_attribute($this->env, $this->source, $context["store"], "name", [], "any", false, false, false, 66);
                echo "</a>
              ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['store'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 68
            echo "              <li><hr class=\"dropdown-divider\"></li>
              <li><h6 class=\"dropdown-header\">";
            // line 69
            echo ($context["text_help"] ?? null);
            echo "</h6></li>
              <li><a href=\"https://www.opencart.com\" target=\"_blank\" class=\"dropdown-item\"><i class=\"fa-brands fa-opencart fa-fw\"></i> ";
            // line 70
            echo ($context["text_homepage"] ?? null);
            echo "</a></li>
              <li><a href=\"http://docs.opencart.com\" target=\"_blank\" class=\"dropdown-item\"><i class=\"fa-solid fa-file fa-fw\"></i> ";
            // line 71
            echo ($context["text_documentation"] ?? null);
            echo "</a></li>
              <li><a href=\"https://forum.opencart.com\" target=\"_blank\" class=\"dropdown-item\"><i class=\"fa-solid fa-comments fa-fw\"></i> ";
            // line 72
            echo ($context["text_support"] ?? null);
            echo "</a></li>
            </ul>
          </li>
          <li id=\"nav-logout\" class=\"nav-item\"><a href=\"";
            // line 75
            echo ($context["logout"] ?? null);
            echo "\" class=\"nav-link\"><i class=\"fa-solid fa-sign-out\"></i> <span class=\"d-none d-md-inline\">";
            echo ($context["text_logout"] ?? null);
            echo "</span></a></li>
        </ul>
      ";
        }
        // line 78
        echo "    </div>
  </header>
";
    }

    public function getTemplateName()
    {
        return "admin/view/template/common/header.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  290 => 78,  282 => 75,  276 => 72,  272 => 71,  268 => 70,  264 => 69,  261 => 68,  250 => 66,  246 => 65,  242 => 64,  235 => 62,  220 => 60,  215 => 58,  211 => 56,  205 => 54,  197 => 52,  186 => 50,  181 => 49,  179 => 48,  170 => 46,  165 => 43,  163 => 42,  155 => 41,  147 => 35,  138 => 33,  133 => 32,  122 => 30,  117 => 29,  104 => 27,  100 => 26,  90 => 19,  86 => 18,  82 => 17,  78 => 16,  73 => 13,  67 => 11,  64 => 10,  58 => 8,  56 => 7,  52 => 6,  48 => 5,  40 => 2,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "admin/view/template/common/header.twig", "/Users/mezbjen/Desktop/meschain-sync-enterprise-1/RESTRUCTURED_UPLOAD/opencart4/admin/view/template/common/header.twig");
    }
}
