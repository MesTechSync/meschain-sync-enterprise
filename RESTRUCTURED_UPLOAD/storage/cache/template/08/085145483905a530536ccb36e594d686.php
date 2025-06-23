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

/* admin/view/template/common/header.twig */
class __TwigTemplate_67c4264c0a360587e0a2a9f939f2db5b extends Template
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
        yield "<!DOCTYPE html>
<html dir=\"";
        // line 2
        yield ($context["direction"] ?? null);
        yield "\" lang=\"";
        yield ($context["lang"] ?? null);
        yield "\">
<head>
  <meta charset=\"UTF-8\"/>
  <title>";
        // line 5
        yield ($context["title"] ?? null);
        yield "</title>
  <base href=\"";
        // line 6
        yield ($context["base"] ?? null);
        yield "\"/>
  ";
        // line 7
        if ((($tmp = ($context["description"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 8
            yield "    <meta name=\"description\" content=\"";
            yield ($context["description"] ?? null);
            yield "\"/>
  ";
        }
        // line 10
        yield "  ";
        if ((($tmp = ($context["keywords"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 11
            yield "    <meta name=\"keywords\" content=\"";
            yield ($context["keywords"] ?? null);
            yield "\"/>
  ";
        }
        // line 13
        yield "  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1, shrink-to-fit=no\"/>
  <meta http-equiv=\"cache-control\" content=\"no-cache\">
  <meta http-equiv=\"expires\" content=\"0\">
  <link href=\"";
        // line 16
        yield ($context["bootstrap"] ?? null);
        yield "\" rel=\"stylesheet\" media=\"screen\"/>
  <link href=\"";
        // line 17
        yield ($context["icons"] ?? null);
        yield "\" rel=\"stylesheet\" type=\"text/css\"/>
  <link href=\"";
        // line 18
        yield ($context["stylesheet"] ?? null);
        yield "\" rel=\"stylesheet\" type=\"text/css\"/>
  <script src=\"";
        // line 19
        yield ($context["jquery"] ?? null);
        yield "\" type=\"text/javascript\"></script>
  <script type=\"text/javascript\" src=\"view/javascript/jquery/datetimepicker/moment.min.js\"></script>
  <script type=\"text/javascript\" src=\"view/javascript/jquery/datetimepicker/moment-with-locales.min.js\"></script>
  <script type=\"text/javascript\" src=\"view/javascript/jquery/datetimepicker/daterangepicker.js\"></script>
  <link href=\"view/javascript/jquery/datetimepicker/daterangepicker.css\" rel=\"stylesheet\" type=\"text/css\"/>
  <script type=\"text/javascript\" src=\"view/javascript/common.js\"></script>
  ";
        // line 25
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["styles"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["style"]) {
            // line 26
            yield "    <link type=\"text/css\" href=\"";
            yield CoreExtension::getAttribute($this->env, $this->source, $context["style"], "href", [], "any", false, false, false, 26);
            yield "\" rel=\"";
            yield CoreExtension::getAttribute($this->env, $this->source, $context["style"], "rel", [], "any", false, false, false, 26);
            yield "\" media=\"";
            yield CoreExtension::getAttribute($this->env, $this->source, $context["style"], "media", [], "any", false, false, false, 26);
            yield "\"/>
  ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['style'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 28
        yield "  ";
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["links"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["link"]) {
            // line 29
            yield "    <link href=\"";
            yield CoreExtension::getAttribute($this->env, $this->source, $context["link"], "href", [], "any", false, false, false, 29);
            yield "\" rel=\"";
            yield CoreExtension::getAttribute($this->env, $this->source, $context["link"], "rel", [], "any", false, false, false, 29);
            yield "\"/>
  ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['link'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 31
        yield "  ";
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["scripts"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["script"]) {
            // line 32
            yield "    <script type=\"text/javascript\" src=\"";
            yield CoreExtension::getAttribute($this->env, $this->source, $context["script"], "href", [], "any", false, false, false, 32);
            yield "\"></script>
  ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['script'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 34
        yield "</head>
<body>
<div id=\"container\">
  <div id=\"alert\" class=\"toast-container position-fixed top-0 end-0 p-3\"></div>
  <header id=\"header\" class=\"navbar navbar-expand navbar-light bg-light\">
    <div class=\"container-fluid\">
      <a href=\"";
        // line 40
        yield ($context["home"] ?? null);
        yield "\" class=\"navbar-brand d-none d-lg-block\"><img src=\"view/image/logo.png\" alt=\"";
        yield ($context["heading_title"] ?? null);
        yield "\" title=\"";
        yield ($context["heading_title"] ?? null);
        yield "\"/></a>
      ";
        // line 41
        if ((($tmp = ($context["logged"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 42
            yield "        <button type=\"button\" id=\"button-menu\" class=\"btn btn-link d-inline-block d-lg-none\"><i class=\"fa-solid fa-bars\"></i></button>
        <ul class=\"nav navbar-nav\">
          <li id=\"nav-notification\" class=\"nav-item dropdown\">
            <a href=\"#\" data-bs-toggle=\"dropdown\" class=\"nav-link dropdown-toggle\"><i class=\"fa-regular fa-bell\"></i>";
            // line 45
            if ((($tmp = ($context["notification_total"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                yield " <span class=\"badge bg-danger\">";
                yield ($context["notification_total"] ?? null);
                yield "</span>";
            }
            yield "</a>
            <div class=\"dropdown-menu dropdown-menu-end\">
              ";
            // line 47
            if ((($tmp = ($context["notifications"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 48
                yield "                ";
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable(($context["notifications"] ?? null));
                foreach ($context['_seq'] as $context["_key"] => $context["notification"]) {
                    // line 49
                    yield "                  <a href=\"";
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["notification"], "href", [], "any", false, false, false, 49);
                    yield "\" data-bs-toggle=\"modal\" class=\"dropdown-item\">";
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["notification"], "title", [], "any", false, false, false, 49);
                    yield "</a>
                ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_key'], $context['notification'], $context['_parent']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 51
                yield "                <a href=\"";
                yield ($context["notification_all"] ?? null);
                yield "\" class=\"dropdown-item text-center text-primary\">";
                yield ($context["text_notification_all"] ?? null);
                yield "</a>
              ";
            } else {
                // line 53
                yield "                <span class=\"dropdown-item text-center\">";
                yield ($context["text_no_results"] ?? null);
                yield "</span>
              ";
            }
            // line 55
            yield "            </div>
          </li>
          <li id=\"nav-language\" class=\"nav-item dropdown\">";
            // line 57
            yield ($context["language"] ?? null);
            yield "</li>
          <li id=\"nav-profile\" class=\"nav-item dropdown\">
            <a href=\"#\" data-bs-toggle=\"dropdown\" class=\"nav-link dropdown-toggle\"><img src=\"";
            // line 59
            yield ($context["image"] ?? null);
            yield "\" alt=\"";
            yield ($context["firstname"] ?? null);
            yield " ";
            yield ($context["lastname"] ?? null);
            yield "\" title=\"";
            yield ($context["username"] ?? null);
            yield "\" class=\"rounded-circle\"/><span class=\"d-none d-md-inline d-lg-inline\">&nbsp;&nbsp;&nbsp;";
            yield ($context["firstname"] ?? null);
            yield " ";
            yield ($context["lastname"] ?? null);
            yield " <i class=\"fa-solid fa-caret-down fa-fw\"></i></span></a>
            <ul class=\"dropdown-menu dropdown-menu-end\">
              <li><a href=\"";
            // line 61
            yield ($context["profile"] ?? null);
            yield "\" class=\"dropdown-item\"><i class=\"fa-solid fa-user-circle fa-fw\"></i> ";
            yield ($context["text_profile"] ?? null);
            yield "</a></li>
              <li><hr class=\"dropdown-divider\"></li>
              <li><h6 class=\"dropdown-header\">";
            // line 63
            yield ($context["text_store"] ?? null);
            yield "</h6></li>
              ";
            // line 64
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["stores"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["store"]) {
                // line 65
                yield "                <a href=\"";
                yield CoreExtension::getAttribute($this->env, $this->source, $context["store"], "href", [], "any", false, false, false, 65);
                yield "\" target=\"_blank\" class=\"dropdown-item\">";
                yield CoreExtension::getAttribute($this->env, $this->source, $context["store"], "name", [], "any", false, false, false, 65);
                yield "</a>
              ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['store'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 67
            yield "              <li><hr class=\"dropdown-divider\"></li>
              <li><h6 class=\"dropdown-header\">";
            // line 68
            yield ($context["text_help"] ?? null);
            yield "</h6></li>
              <li><a href=\"https://www.opencart.com\" target=\"_blank\" class=\"dropdown-item\"><i class=\"fa-brands fa-opencart fa-fw\"></i> ";
            // line 69
            yield ($context["text_homepage"] ?? null);
            yield "</a></li>
              <li><a href=\"http://docs.opencart.com\" target=\"_blank\" class=\"dropdown-item\"><i class=\"fa-solid fa-file fa-fw\"></i> ";
            // line 70
            yield ($context["text_documentation"] ?? null);
            yield "</a></li>
              <li><a href=\"https://forum.opencart.com\" target=\"_blank\" class=\"dropdown-item\"><i class=\"fa-solid fa-comments fa-fw\"></i> ";
            // line 71
            yield ($context["text_support"] ?? null);
            yield "</a></li>
            </ul>
          </li>
          <li id=\"nav-logout\" class=\"nav-item\"><a href=\"";
            // line 74
            yield ($context["logout"] ?? null);
            yield "\" class=\"nav-link\"><i class=\"fa-solid fa-sign-out\"></i> <span class=\"d-none d-md-inline\">";
            yield ($context["text_logout"] ?? null);
            yield "</span></a></li>
        </ul>
      ";
        }
        // line 77
        yield "    </div>
  </header>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "admin/view/template/common/header.twig";
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
        return array (  294 => 77,  286 => 74,  280 => 71,  276 => 70,  272 => 69,  268 => 68,  265 => 67,  254 => 65,  250 => 64,  246 => 63,  239 => 61,  224 => 59,  219 => 57,  215 => 55,  209 => 53,  201 => 51,  190 => 49,  185 => 48,  183 => 47,  174 => 45,  169 => 42,  167 => 41,  159 => 40,  151 => 34,  142 => 32,  137 => 31,  126 => 29,  121 => 28,  108 => 26,  104 => 25,  95 => 19,  91 => 18,  87 => 17,  83 => 16,  78 => 13,  72 => 11,  69 => 10,  63 => 8,  61 => 7,  57 => 6,  53 => 5,  45 => 2,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "admin/view/template/common/header.twig", "/Users/mezbjen/Desktop/meschain-sync-enterprise-1/RESTRUCTURED_UPLOAD/opencart4_clean/admin/view/template/common/header.twig");
    }
}
