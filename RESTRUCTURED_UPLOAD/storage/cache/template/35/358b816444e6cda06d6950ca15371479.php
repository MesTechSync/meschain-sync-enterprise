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

/* admin/view/template/common/login.twig */
class __TwigTemplate_d00f8706ebc7abba765ec8f774ffbab0 extends Template
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
        yield "
<div id=\"content\">
  <div class=\"container-fluid\">
    <br/>
    <br/>
    <div class=\"row justify-content-sm-center\">
      <div class=\"col-sm-10 col-md-8 col-lg-5\">
        <div class=\"card\">
          <div class=\"card-header\"><i class=\"fa-solid fa-lock\"></i> ";
        // line 9
        yield ($context["text_login"] ?? null);
        yield "</div>
          <div class=\"card-body\">
            <form id=\"form-login\" action=\"";
        // line 11
        yield ($context["login"] ?? null);
        yield "\" method=\"post\" data-oc-toggle=\"ajax\">
              ";
        // line 12
        if ((($tmp = ($context["error_warning"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 13
            yield "                <div class=\"alert alert-danger alert-dismissible\"><i class=\"Dfa-circle-exclamation\"></i> ";
            yield ($context["error_warning"] ?? null);
            yield " <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>
              ";
        }
        // line 15
        yield "              ";
        if ((($tmp = ($context["success"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 16
            yield "                <div class=\"alert alert-success alert-dismissible\"><i class=\"fa-solid fa-check-circle\"></i> ";
            yield ($context["success"] ?? null);
            yield " <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>
              ";
        }
        // line 18
        yield "              <div class=\"mb-3\">
                <label for=\"input-username\" class=\"form-label\">";
        // line 19
        yield ($context["entry_username"] ?? null);
        yield "</label>
                <div class=\"input-group\">
                  <div class=\"input-group-text\"><i class=\"fa-solid fa-user\"></i></div>
                  <input type=\"text\" name=\"username\" value=\"\" placeholder=\"";
        // line 22
        yield ($context["entry_username"] ?? null);
        yield "\" id=\"input-username\" class=\"form-control\"/>
                </div>
              </div>
              <div class=\"mb-3\">
                <label for=\"input-password\" class=\"form-label\">";
        // line 26
        yield ($context["entry_password"] ?? null);
        yield "</label>
                <div class=\"input-group mb-2\">
                  <div class=\"input-group-text\"><i class=\"fa-solid fa-lock\"></i></div>
                  <input type=\"password\" name=\"password\" value=\"\" placeholder=\"";
        // line 29
        yield ($context["entry_password"] ?? null);
        yield "\" id=\"input-password\" class=\"form-control\"/>
                </div>
                ";
        // line 31
        if ((($tmp = ($context["forgotten"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 32
            yield "                <div class=\"mb-3\"><a href=\"";
            yield ($context["forgotten"] ?? null);
            yield "\">";
            yield ($context["text_forgotten"] ?? null);
            yield "</a></div>
                ";
        }
        // line 34
        yield "              </div>
              <div class=\"text-end\">
                <button type=\"submit\" class=\"btn btn-primary\"><i class=\"fa-solid fa-key\"></i> ";
        // line 36
        yield ($context["button_login"] ?? null);
        yield "</button>
              </div>
              ";
        // line 38
        if ((($tmp = ($context["redirect"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 39
            yield "                <input type=\"hidden\" name=\"redirect\" value=\"";
            yield ($context["redirect"] ?? null);
            yield "\"/>
              ";
        }
        // line 41
        yield "            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
";
        // line 48
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
        return "admin/view/template/common/login.twig";
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
        return array (  142 => 48,  133 => 41,  127 => 39,  125 => 38,  120 => 36,  116 => 34,  108 => 32,  106 => 31,  101 => 29,  95 => 26,  88 => 22,  82 => 19,  79 => 18,  73 => 16,  70 => 15,  64 => 13,  62 => 12,  58 => 11,  53 => 9,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "admin/view/template/common/login.twig", "/Users/mezbjen/Desktop/meschain-sync-enterprise-1/RESTRUCTURED_UPLOAD/opencart4_clean/admin/view/template/common/login.twig");
    }
}
