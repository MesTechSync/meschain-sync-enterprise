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

/* catalog/view/template/common/currency.twig */
class __TwigTemplate_6482a0bc161bb261167c094fec5ea40b extends Template
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
        if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["currencies"] ?? null)) > 1)) {
            // line 2
            yield "  <form action=\"";
            yield ($context["action"] ?? null);
            yield "\" method=\"post\" enctype=\"multipart/form-data\" id=\"form-currency\">
    <div class=\"dropdown\">
      <a href=\"#\" data-bs-toggle=\"dropdown\" class=\"dropdown-toggle\">";
            // line 4
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["currencies"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["currency"]) {
                if ((CoreExtension::getAttribute($this->env, $this->source, $context["currency"], "symbol_left", [], "any", false, false, false, 4) && (CoreExtension::getAttribute($this->env, $this->source, $context["currency"], "code", [], "any", false, false, false, 4) == ($context["code"] ?? null)))) {
                    yield "<strong>";
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["currency"], "symbol_left", [], "any", false, false, false, 4);
                    yield "</strong>";
                } elseif ((CoreExtension::getAttribute($this->env, $this->source, $context["currency"], "symbol_right", [], "any", false, false, false, 4) && (CoreExtension::getAttribute($this->env, $this->source, $context["currency"], "code", [], "any", false, false, false, 4) == ($context["code"] ?? null)))) {
                    yield "<strong>";
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["currency"], "symbol_right", [], "any", false, false, false, 4);
                    yield "</strong>";
                }
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['currency'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            yield " <span class=\"d-none d-md-inline\">";
            yield ($context["text_currency"] ?? null);
            yield "</span> <i class=\"fa-solid fa-caret-down\"></i></a>
      <ul class=\"dropdown-menu\">
        ";
            // line 6
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["currencies"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["currency"]) {
                // line 7
                yield "          ";
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["currency"], "symbol_left", [], "any", false, false, false, 7)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 8
                    yield "            <li><a href=\"";
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["currency"], "code", [], "any", false, false, false, 8);
                    yield "\" class=\"dropdown-item\">";
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["currency"], "symbol_left", [], "any", false, false, false, 8);
                    yield " ";
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["currency"], "title", [], "any", false, false, false, 8);
                    yield "</a></li>
          ";
                } else {
                    // line 10
                    yield "            <li><a href=\"";
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["currency"], "code", [], "any", false, false, false, 10);
                    yield "\" class=\"dropdown-item\">";
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["currency"], "symbol_right", [], "any", false, false, false, 10);
                    yield " ";
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["currency"], "title", [], "any", false, false, false, 10);
                    yield "</a></li>
          ";
                }
                // line 12
                yield "        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['currency'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 13
            yield "      </ul>
    </div>
    <input type=\"hidden\" name=\"code\" value=\"\"/> <input type=\"hidden\" name=\"redirect\" value=\"";
            // line 15
            yield ($context["redirect"] ?? null);
            yield "\"/>
  </form>
";
        }
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "catalog/view/template/common/currency.twig";
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
        return array (  109 => 15,  105 => 13,  99 => 12,  89 => 10,  79 => 8,  76 => 7,  72 => 6,  50 => 4,  44 => 2,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "catalog/view/template/common/currency.twig", "/Users/mezbjen/Desktop/meschain-sync-enterprise-1/RESTRUCTURED_UPLOAD/opencart4_clean/catalog/view/template/common/currency.twig");
    }
}
