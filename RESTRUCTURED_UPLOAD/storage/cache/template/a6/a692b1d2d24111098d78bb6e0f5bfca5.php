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

/* admin/view/template/common/column_left.twig */
class __TwigTemplate_1959cde3db39346a7a7644823e0e8d83 extends Template
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
        yield "<nav id=\"column-left\">
\t<div id=\"navigation\"><span class=\"fa-solid fa-bars\"></span> ";
        // line 2
        yield ($context["text_navigation"] ?? null);
        yield "</div>
\t<ul id=\"menu\">
\t\t";
        // line 4
        $context["i"] = 0;
        // line 5
        yield "\t\t";
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["menus"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["menu"]) {
            // line 6
            yield "\t\t\t<li id=\"";
            yield CoreExtension::getAttribute($this->env, $this->source, $context["menu"], "id", [], "any", false, false, false, 6);
            yield "\">
\t\t\t\t";
            // line 7
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["menu"], "href", [], "any", false, false, false, 7)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 8
                yield "\t\t\t\t\t<a href=\"";
                yield CoreExtension::getAttribute($this->env, $this->source, $context["menu"], "href", [], "any", false, false, false, 8);
                yield "\"><i class=\"";
                yield CoreExtension::getAttribute($this->env, $this->source, $context["menu"], "icon", [], "any", false, false, false, 8);
                yield "\"></i> ";
                yield CoreExtension::getAttribute($this->env, $this->source, $context["menu"], "name", [], "any", false, false, false, 8);
                yield "</a>
\t\t\t\t";
            } else {
                // line 10
                yield "\t\t\t\t\t<a href=\"#collapse-";
                yield ($context["i"] ?? null);
                yield "\" data-bs-toggle=\"collapse\" class=\"parent collapsed\"><i class=\"";
                yield CoreExtension::getAttribute($this->env, $this->source, $context["menu"], "icon", [], "any", false, false, false, 10);
                yield "\"></i> ";
                yield CoreExtension::getAttribute($this->env, $this->source, $context["menu"], "name", [], "any", false, false, false, 10);
                yield "</a>
\t\t\t\t";
            }
            // line 12
            yield "\t\t\t\t";
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["menu"], "children", [], "any", false, false, false, 12)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 13
                yield "\t\t\t\t\t<ul id=\"collapse-";
                yield ($context["i"] ?? null);
                yield "\" class=\"collapse\">
\t\t\t\t\t\t";
                // line 14
                $context["j"] = 0;
                // line 15
                yield "\t\t\t\t\t\t";
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, $context["menu"], "children", [], "any", false, false, false, 15));
                foreach ($context['_seq'] as $context["_key"] => $context["children_1"]) {
                    // line 16
                    yield "\t\t\t\t\t\t\t<li>";
                    if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["children_1"], "href", [], "any", false, false, false, 16)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                        // line 17
                        yield "\t\t\t\t\t\t\t\t\t<a href=\"";
                        yield CoreExtension::getAttribute($this->env, $this->source, $context["children_1"], "href", [], "any", false, false, false, 17);
                        yield "\">";
                        yield CoreExtension::getAttribute($this->env, $this->source, $context["children_1"], "name", [], "any", false, false, false, 17);
                        yield "</a>
\t\t\t\t\t\t\t\t";
                    } else {
                        // line 19
                        yield "\t\t\t\t\t\t\t\t\t<a href=\"#collapse-";
                        yield ($context["i"] ?? null);
                        yield "-";
                        yield ($context["j"] ?? null);
                        yield "\" data-bs-toggle=\"collapse\" class=\"parent collapsed\">";
                        yield CoreExtension::getAttribute($this->env, $this->source, $context["children_1"], "name", [], "any", false, false, false, 19);
                        yield "</a>
\t\t\t\t\t\t\t\t";
                    }
                    // line 21
                    yield "\t\t\t\t\t\t\t\t";
                    if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["children_1"], "children", [], "any", false, false, false, 21)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                        // line 22
                        yield "\t\t\t\t\t\t\t\t\t<ul id=\"collapse-";
                        yield ($context["i"] ?? null);
                        yield "-";
                        yield ($context["j"] ?? null);
                        yield "\" class=\"collapse\">
\t\t\t\t\t\t\t\t\t\t";
                        // line 23
                        $context["k"] = 0;
                        // line 24
                        yield "\t\t\t\t\t\t\t\t\t\t";
                        $context['_parent'] = $context;
                        $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, $context["children_1"], "children", [], "any", false, false, false, 24));
                        foreach ($context['_seq'] as $context["_key"] => $context["children_2"]) {
                            // line 25
                            yield "\t\t\t\t\t\t\t\t\t\t\t<li>";
                            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["children_2"], "href", [], "any", false, false, false, 25)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                                // line 26
                                yield "\t\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"";
                                yield CoreExtension::getAttribute($this->env, $this->source, $context["children_2"], "href", [], "any", false, false, false, 26);
                                yield "\">";
                                yield CoreExtension::getAttribute($this->env, $this->source, $context["children_2"], "name", [], "any", false, false, false, 26);
                                yield "</a>
\t\t\t\t\t\t\t\t\t\t\t\t";
                            } else {
                                // line 28
                                yield "\t\t\t\t\t\t\t\t\t\t\t\t\t<a href=\"#collapse-";
                                yield ($context["i"] ?? null);
                                yield "-";
                                yield ($context["j"] ?? null);
                                yield "-";
                                yield ($context["k"] ?? null);
                                yield "\" data-bs-toggle=\"collapse\" class=\"parent collapsed\">";
                                yield CoreExtension::getAttribute($this->env, $this->source, $context["children_2"], "name", [], "any", false, false, false, 28);
                                yield "</a>
\t\t\t\t\t\t\t\t\t\t\t\t";
                            }
                            // line 30
                            yield "\t\t\t\t\t\t\t\t\t\t\t\t";
                            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["children_2"], "children", [], "any", false, false, false, 30)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                                // line 31
                                yield "\t\t\t\t\t\t\t\t\t\t\t\t\t<ul id=\"collapse-";
                                yield ($context["i"] ?? null);
                                yield "-";
                                yield ($context["j"] ?? null);
                                yield "-";
                                yield ($context["k"] ?? null);
                                yield "\" class=\"collapse\">
\t\t\t\t\t\t\t\t\t\t\t\t\t\t";
                                // line 32
                                $context['_parent'] = $context;
                                $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, $context["children_2"], "children", [], "any", false, false, false, 32));
                                foreach ($context['_seq'] as $context["_key"] => $context["children_3"]) {
                                    // line 33
                                    yield "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<li><a href=\"";
                                    yield CoreExtension::getAttribute($this->env, $this->source, $context["children_3"], "href", [], "any", false, false, false, 33);
                                    yield "\">";
                                    yield CoreExtension::getAttribute($this->env, $this->source, $context["children_3"], "name", [], "any", false, false, false, 33);
                                    yield "</a></li>
\t\t\t\t\t\t\t\t\t\t\t\t\t\t";
                                }
                                $_parent = $context['_parent'];
                                unset($context['_seq'], $context['_key'], $context['children_3'], $context['_parent']);
                                $context = array_intersect_key($context, $_parent) + $_parent;
                                // line 35
                                yield "\t\t\t\t\t\t\t\t\t\t\t\t\t</ul>
\t\t\t\t\t\t\t\t\t\t\t\t";
                            }
                            // line 36
                            yield "</li>
\t\t\t\t\t\t\t\t\t\t\t";
                            // line 37
                            $context["k"] = (($context["k"] ?? null) + 1);
                            // line 38
                            yield "\t\t\t\t\t\t\t\t\t\t";
                        }
                        $_parent = $context['_parent'];
                        unset($context['_seq'], $context['_key'], $context['children_2'], $context['_parent']);
                        $context = array_intersect_key($context, $_parent) + $_parent;
                        // line 39
                        yield "\t\t\t\t\t\t\t\t\t</ul>
\t\t\t\t\t\t\t\t";
                    }
                    // line 41
                    yield "\t\t\t\t\t\t\t</li>
\t\t\t\t\t\t\t";
                    // line 42
                    $context["j"] = (($context["j"] ?? null) + 1);
                    // line 43
                    yield "\t\t\t\t\t\t";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_key'], $context['children_1'], $context['_parent']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 44
                yield "\t\t\t\t\t</ul>
\t\t\t\t";
            }
            // line 46
            yield "\t\t\t</li>
\t\t\t";
            // line 47
            $context["i"] = (($context["i"] ?? null) + 1);
            // line 48
            yield "\t\t";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['menu'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 49
        yield "\t</ul>
\t";
        // line 50
        if ((($tmp = ($context["statistics_status"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 51
            yield "\t\t<div id=\"stats\">
\t\t\t<ul>
\t\t\t\t<li>
\t\t\t\t\t<div>";
            // line 54
            yield ($context["text_complete_status"] ?? null);
            yield " <span class=\"float-end\">";
            yield ($context["complete_status"] ?? null);
            yield "%</span></div>
\t\t\t\t\t<div class=\"progress\">
\t\t\t\t\t\t<div class=\"progress-bar bg-success\" role=\"progressbar\" aria-valuenow=\"";
            // line 56
            yield ($context["complete_status"] ?? null);
            yield "\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: ";
            yield ($context["complete_status"] ?? null);
            yield "%\"><span class=\"sr-only\">";
            yield ($context["complete_status"] ?? null);
            yield "%</span></div>
\t\t\t\t\t</div>
\t\t\t\t</li>
\t\t\t\t<li>
\t\t\t\t\t<div>";
            // line 60
            yield ($context["text_processing_status"] ?? null);
            yield " <span class=\"float-end\">";
            yield ($context["processing_status"] ?? null);
            yield "%</span></div>
\t\t\t\t\t<div class=\"progress\">
\t\t\t\t\t\t<div class=\"progress-bar bg-warning\" role=\"progressbar\" aria-valuenow=\"";
            // line 62
            yield ($context["processing_status"] ?? null);
            yield "\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: ";
            yield ($context["processing_status"] ?? null);
            yield "%\"><span class=\"sr-only\">";
            yield ($context["processing_status"] ?? null);
            yield "%</span></div>
\t\t\t\t\t</div>
\t\t\t\t</li>
\t\t\t\t<li>
\t\t\t\t\t<div>";
            // line 66
            yield ($context["text_other_status"] ?? null);
            yield " <span class=\"float-end\">";
            yield ($context["other_status"] ?? null);
            yield "%</span></div>
\t\t\t\t\t<div class=\"progress\">
\t\t\t\t\t\t<div class=\"progress-bar bg-danger\" role=\"progressbar\" aria-valuenow=\"";
            // line 68
            yield ($context["other_status"] ?? null);
            yield "\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: ";
            yield ($context["other_status"] ?? null);
            yield "%\"><span class=\"sr-only\">";
            yield ($context["other_status"] ?? null);
            yield "%</span></div>
\t\t\t\t\t</div>
\t\t\t\t</li>
\t\t\t</ul>
\t\t</div>
\t";
        }
        // line 74
        yield "</nav>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "admin/view/template/common/column_left.twig";
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
        return array (  298 => 74,  285 => 68,  278 => 66,  267 => 62,  260 => 60,  249 => 56,  242 => 54,  237 => 51,  235 => 50,  232 => 49,  226 => 48,  224 => 47,  221 => 46,  217 => 44,  211 => 43,  209 => 42,  206 => 41,  202 => 39,  196 => 38,  194 => 37,  191 => 36,  187 => 35,  176 => 33,  172 => 32,  163 => 31,  160 => 30,  148 => 28,  140 => 26,  137 => 25,  132 => 24,  130 => 23,  123 => 22,  120 => 21,  110 => 19,  102 => 17,  99 => 16,  94 => 15,  92 => 14,  87 => 13,  84 => 12,  74 => 10,  64 => 8,  62 => 7,  57 => 6,  52 => 5,  50 => 4,  45 => 2,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "admin/view/template/common/column_left.twig", "/Users/mezbjen/Desktop/meschain-sync-enterprise-1/RESTRUCTURED_UPLOAD/opencart4_clean/admin/view/template/common/column_left.twig");
    }
}
