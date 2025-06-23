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

/* catalog/view/template/common/language.twig */
class __TwigTemplate_026c5dad391e8f35e45ca0b93549ea3a extends Template
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
        if ((Twig\Extension\CoreExtension::length($this->env->getCharset(), ($context["languages"] ?? null)) > 1)) {
            // line 2
            yield "  <div class=\"dropdown\">
    <div class=\"dropdown-toggle\" data-bs-toggle=\"dropdown\">
      ";
            // line 4
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["languages"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["language"]) {
                // line 5
                yield "        ";
                if ((CoreExtension::getAttribute($this->env, $this->source, $context["language"], "code", [], "any", false, false, false, 5) == ($context["code"] ?? null))) {
                    // line 6
                    yield "          <img src=\"";
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["language"], "image", [], "any", false, false, false, 6);
                    yield "\" alt=\"";
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["language"], "name", [], "any", false, false, false, 6);
                    yield "\" title=\"";
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["language"], "name", [], "any", false, false, false, 6);
                    yield "\">
        ";
                }
                // line 8
                yield "      ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['language'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 9
            yield "      <span class=\"d-none d-md-inline\">";
            yield ($context["text_language"] ?? null);
            yield "</span> <i class=\"fa-solid fa-caret-down\"></i>
    </div>
    <ul class=\"dropdown-menu\">
      ";
            // line 12
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["languages"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["language"]) {
                // line 13
                yield "        <li><a href=\"";
                yield CoreExtension::getAttribute($this->env, $this->source, $context["language"], "href", [], "any", false, false, false, 13);
                yield "\" class=\"dropdown-item\"><img src=\"";
                yield CoreExtension::getAttribute($this->env, $this->source, $context["language"], "image", [], "any", false, false, false, 13);
                yield "\" alt=\"";
                yield CoreExtension::getAttribute($this->env, $this->source, $context["language"], "name", [], "any", false, false, false, 13);
                yield "\" title=\"";
                yield CoreExtension::getAttribute($this->env, $this->source, $context["language"], "name", [], "any", false, false, false, 13);
                yield "\"/> ";
                yield CoreExtension::getAttribute($this->env, $this->source, $context["language"], "name", [], "any", false, false, false, 13);
                yield "</a></li>
      ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['language'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 15
            yield "    </ul>
  </div>
";
        }
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "catalog/view/template/common/language.twig";
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
        return array (  99 => 15,  82 => 13,  78 => 12,  71 => 9,  65 => 8,  55 => 6,  52 => 5,  48 => 4,  44 => 2,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "catalog/view/template/common/language.twig", "/Users/mezbjen/Desktop/meschain-sync-enterprise-1/RESTRUCTURED_UPLOAD/opencart4_clean/catalog/view/template/common/language.twig");
    }
}
