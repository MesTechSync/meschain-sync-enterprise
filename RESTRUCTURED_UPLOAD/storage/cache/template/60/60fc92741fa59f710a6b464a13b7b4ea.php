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

/* admin/view/template/extension/analytics.twig */
class __TwigTemplate_53def251db94a32f2cb641e84d219651 extends Template
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
        yield ($context["promotion"] ?? null);
        yield "
<fieldset>
\t<legend>";
        // line 3
        yield ($context["heading_title"] ?? null);
        yield "</legend>
\t<div class=\"table-responsive\">
\t\t<table class=\"table table-bordered table-hover\">
\t\t\t<thead>
\t\t\t\t<tr>
\t\t\t\t\t<td class=\"text-start\">";
        // line 8
        yield ($context["column_name"] ?? null);
        yield "</td>
\t\t\t\t\t<td class=\"text-start\">";
        // line 9
        yield ($context["column_status"] ?? null);
        yield "</td>
\t\t\t\t\t<td class=\"text-end\">";
        // line 10
        yield ($context["column_action"] ?? null);
        yield "</td>
\t\t\t\t</tr>
\t\t\t</thead>
\t\t\t<tbody>
\t\t\t\t";
        // line 14
        if ((($tmp = ($context["extensions"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 15
            yield "\t\t\t\t\t";
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["extensions"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["extension"]) {
                // line 16
                yield "\t\t\t\t\t\t<tr>
\t\t\t\t\t\t\t<td class=\"text-start\" colspan=\"2\"><b>";
                // line 17
                yield CoreExtension::getAttribute($this->env, $this->source, $context["extension"], "name", [], "any", false, false, false, 17);
                yield "</b></td>
\t\t\t\t\t\t\t<td class=\"text-end\">";
                // line 18
                if ((($tmp =  !CoreExtension::getAttribute($this->env, $this->source, $context["extension"], "installed", [], "any", false, false, false, 18)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 19
                    yield "\t\t\t\t\t\t\t\t\t<a href=\"";
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["extension"], "install", [], "any", false, false, false, 19);
                    yield "\" data-bs-toggle=\"tooltip\" title=\"";
                    yield ($context["button_install"] ?? null);
                    yield "\" class=\"btn btn-success\"><i class=\"fa-solid fa-plus-circle\"></i></a>
\t\t\t\t\t\t\t\t";
                } else {
                    // line 21
                    yield "\t\t\t\t\t\t\t\t\t<a href=\"";
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["extension"], "uninstall", [], "any", false, false, false, 21);
                    yield "\" data-bs-toggle=\"tooltip\" title=\"";
                    yield ($context["button_uninstall"] ?? null);
                    yield "\" class=\"btn btn-danger\"><i class=\"fa-solid fa-minus-circle\"></i></a>
\t\t\t\t\t\t\t\t";
                }
                // line 22
                yield "</td>
\t\t\t\t\t\t</tr>
\t\t\t\t\t\t";
                // line 24
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["extension"], "installed", [], "any", false, false, false, 24)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 25
                    yield "\t\t\t\t\t\t\t";
                    $context['_parent'] = $context;
                    $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, $context["extension"], "store", [], "any", false, false, false, 25));
                    foreach ($context['_seq'] as $context["_key"] => $context["store"]) {
                        // line 26
                        yield "\t\t\t\t\t\t\t\t<tr>
\t\t\t\t\t\t\t\t\t<td class=\"text-start\">&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;";
                        // line 27
                        yield CoreExtension::getAttribute($this->env, $this->source, $context["store"], "name", [], "any", false, false, false, 27);
                        yield "</td>
\t\t\t\t\t\t\t\t\t<td class=\"text-start\">";
                        // line 28
                        yield CoreExtension::getAttribute($this->env, $this->source, $context["store"], "status", [], "any", false, false, false, 28);
                        yield "</td>
\t\t\t\t\t\t\t\t\t<td class=\"text-end text-nowrap\"><a href=\"";
                        // line 29
                        yield CoreExtension::getAttribute($this->env, $this->source, $context["store"], "edit", [], "any", false, false, false, 29);
                        yield "\" data-bs-toggle=\"tooltip\" title=\"";
                        yield ($context["button_edit"] ?? null);
                        yield "\" class=\"btn btn-primary\"><i class=\"fa-solid fa-pencil\"></i></a></td>
\t\t\t\t\t\t\t\t</tr>
\t\t\t\t\t\t\t";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_key'], $context['store'], $context['_parent']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 32
                    yield "\t\t\t\t\t\t";
                }
                // line 33
                yield "\t\t\t\t\t";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['extension'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 34
            yield "\t\t\t\t";
        } else {
            // line 35
            yield "\t\t\t\t\t<tr>
\t\t\t\t\t\t<td class=\"text-center\" colspan=\"3\">";
            // line 36
            yield ($context["text_no_results"] ?? null);
            yield "</td>
\t\t\t\t\t</tr>
\t\t\t\t";
        }
        // line 39
        yield "\t\t\t</tbody>
\t\t</table>
\t</div>
</fieldset>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "admin/view/template/extension/analytics.twig";
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
        return array (  156 => 39,  150 => 36,  147 => 35,  144 => 34,  138 => 33,  135 => 32,  124 => 29,  120 => 28,  116 => 27,  113 => 26,  108 => 25,  106 => 24,  102 => 22,  94 => 21,  86 => 19,  84 => 18,  80 => 17,  77 => 16,  72 => 15,  70 => 14,  63 => 10,  59 => 9,  55 => 8,  47 => 3,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "admin/view/template/extension/analytics.twig", "/Users/mezbjen/Desktop/meschain-sync-enterprise-1/RESTRUCTURED_UPLOAD/opencart4_clean/admin/view/template/extension/analytics.twig");
    }
}
