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

/* admin/view/template/marketplace/promotion.twig */
class __TwigTemplate_359b7b5cf31db2b9078a272f1cca6531 extends Template
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
        if ((($tmp = ($context["banner"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 2
            yield "\t<div class=\"text-center\">";
            yield ($context["banner"] ?? null);
            yield "</div>
\t<br/>
";
        }
        // line 5
        if ((($tmp = ($context["extensions"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 6
            yield "\t<fieldset id=\"recommended\">
\t\t<legend>";
            // line 7
            yield ($context["text_recommended"] ?? null);
            yield "</legend>
\t\t<div class=\"table-responsive\">
\t\t\t<table class=\"table table-bordered table-hover\">
\t\t\t\t<thead>
\t\t\t\t\t<tr>
\t\t\t\t\t\t<td class=\"text-start\">";
            // line 12
            yield ($context["column_name"] ?? null);
            yield "</td>
\t\t\t\t\t\t<td class=\"text-end\">";
            // line 13
            yield ($context["column_action"] ?? null);
            yield "</td>
\t\t\t\t\t</tr>
\t\t\t\t</thead>
\t\t\t\t<tbody>
\t\t\t\t\t";
            // line 17
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["extensions"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["extension"]) {
                // line 18
                yield "\t\t\t\t\t\t<tr>
\t\t\t\t\t\t\t<td class=\"text-start\"><a href=\"";
                // line 19
                yield CoreExtension::getAttribute($this->env, $this->source, $context["extension"], "href", [], "any", false, false, false, 19);
                yield "\">";
                yield CoreExtension::getAttribute($this->env, $this->source, $context["extension"], "name", [], "any", false, false, false, 19);
                yield "</a></td>
\t\t\t\t\t\t\t<td class=\"text-end\">
\t\t\t\t\t\t\t\t<div class=\"dropdown\">
\t\t\t\t\t\t\t\t\t<div class=\"btn-group\">
\t\t\t\t\t\t\t\t\t\t<button type=\"button\" value=\"";
                // line 23
                yield CoreExtension::getAttribute($this->env, $this->source, $context["extension"], "download", [], "any", false, false, false, 23);
                yield "\" data-bs-toggle=\"tooltip\" title=\"";
                yield ($context["button_download"] ?? null);
                yield "\" class=\"btn btn-primary\"";
                if ((($tmp =  !CoreExtension::getAttribute($this->env, $this->source, $context["extension"], "download", [], "any", false, false, false, 23)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    yield " disabled";
                }
                yield "><i class=\"fa-solid fa-download\"></i></button>
\t\t\t\t\t\t\t\t\t\t<button type=\"button\" data-bs-toggle=\"dropdown\" class=\"btn btn-outline-dark dropdown-toggle dropdown-toggle-split\"><i class=\"fa-solid fa-caret-down\"></i></button>
\t\t\t\t\t\t\t\t\t\t<div class=\"dropdown-menu dropdown-menu-end\">
\t\t\t\t\t\t\t\t\t\t\t<a href=\"";
                // line 26
                yield CoreExtension::getAttribute($this->env, $this->source, $context["extension"], "install", [], "any", false, false, false, 26);
                yield "\" class=\"dropdown-item";
                if ((($tmp =  !CoreExtension::getAttribute($this->env, $this->source, $context["extension"], "install", [], "any", false, false, false, 26)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    yield " disabled";
                }
                yield "\"><i class=\"fa-solid fa-plus-circle fa-fw\"></i> ";
                yield ($context["text_install"] ?? null);
                yield "</a> <a href=\"";
                yield CoreExtension::getAttribute($this->env, $this->source, $context["extension"], "delete", [], "any", false, false, false, 26);
                yield "\" class=\"dropdown-item";
                if ((($tmp =  !CoreExtension::getAttribute($this->env, $this->source, $context["extension"], "delete", [], "any", false, false, false, 26)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    yield " disabled";
                }
                yield "\"><i class=\"fa-regular fa-trash-can fa-fw\"></i> ";
                yield ($context["text_delete"] ?? null);
                yield "</a>
\t\t\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t</td>
\t\t\t\t\t\t</tr>
\t\t\t\t\t";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['extension'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 33
            yield "\t\t\t\t</tbody>
\t\t\t</table>
\t\t</div>
\t</fieldset>
";
        }
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "admin/view/template/marketplace/promotion.twig";
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
        return array (  130 => 33,  103 => 26,  91 => 23,  82 => 19,  79 => 18,  75 => 17,  68 => 13,  64 => 12,  56 => 7,  53 => 6,  51 => 5,  44 => 2,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "admin/view/template/marketplace/promotion.twig", "/Users/mezbjen/Desktop/meschain-sync-enterprise-1/RESTRUCTURED_UPLOAD/opencart4_clean/admin/view/template/marketplace/promotion.twig");
    }
}
