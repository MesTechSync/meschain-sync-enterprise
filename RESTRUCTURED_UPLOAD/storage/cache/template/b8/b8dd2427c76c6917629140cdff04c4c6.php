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

/* catalog/view/template/common/menu.twig */
class __TwigTemplate_54d25b3b9fa6220345d20c23caa5f574 extends Template
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
        if ((($tmp = ($context["categories"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 2
            yield "  <div class=\"container\">
    <nav id=\"menu\" class=\"navbar navbar-expand-lg navbar-light bg-primary\">
      <div id=\"category\" class=\"d-block d-sm-block d-lg-none\">";
            // line 4
            yield ($context["text_category"] ?? null);
            yield "</div>
      <button class=\"navbar-toggler\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#narbar-menu\"><i class=\"fa-solid fa-bars\"></i></button>
      <div class=\"collapse navbar-collapse\" id=\"narbar-menu\">
        <ul class=\"nav navbar-nav\">
          ";
            // line 8
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["categories"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["category"]) {
                // line 9
                yield "            ";
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["category"], "children", [], "any", false, false, false, 9)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 10
                    yield "              <li class=\"nav-item dropdown\"><a href=\"";
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["category"], "href", [], "any", false, false, false, 10);
                    yield "\" class=\"nav-link dropdown-toggle\" data-bs-toggle=\"dropdown\">";
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["category"], "name", [], "any", false, false, false, 10);
                    yield "</a>
                <div class=\"dropdown-menu\">
                  <div class=\"dropdown-inner\">
                    ";
                    // line 13
                    $context['_parent'] = $context;
                    $context['_seq'] = CoreExtension::ensureTraversable(Twig\Extension\CoreExtension::batch(CoreExtension::getAttribute($this->env, $this->source, $context["category"], "children", [], "any", false, false, false, 13), (Twig\Extension\CoreExtension::length($this->env->getCharset(), CoreExtension::getAttribute($this->env, $this->source, $context["category"], "children", [], "any", false, false, false, 13)) / Twig\Extension\CoreExtension::round(CoreExtension::getAttribute($this->env, $this->source, $context["category"], "column", [], "any", false, false, false, 13), 1, "ceil"))));
                    foreach ($context['_seq'] as $context["_key"] => $context["children"]) {
                        // line 14
                        yield "                      <ul class=\"list-unstyled\">
                        ";
                        // line 15
                        $context['_parent'] = $context;
                        $context['_seq'] = CoreExtension::ensureTraversable($context["children"]);
                        foreach ($context['_seq'] as $context["_key"] => $context["child"]) {
                            // line 16
                            yield "                          <li><a href=\"";
                            yield CoreExtension::getAttribute($this->env, $this->source, $context["child"], "href", [], "any", false, false, false, 16);
                            yield "\" class=\"nav-link\">";
                            yield CoreExtension::getAttribute($this->env, $this->source, $context["child"], "name", [], "any", false, false, false, 16);
                            yield "</a></li>
                        ";
                        }
                        $_parent = $context['_parent'];
                        unset($context['_seq'], $context['_key'], $context['child'], $context['_parent']);
                        $context = array_intersect_key($context, $_parent) + $_parent;
                        // line 18
                        yield "                      </ul>
                    ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_key'], $context['children'], $context['_parent']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 20
                    yield "                  </div>
                  <a href=\"";
                    // line 21
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["category"], "href", [], "any", false, false, false, 21);
                    yield "\" class=\"see-all\">";
                    yield ($context["text_all"] ?? null);
                    yield " ";
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["category"], "name", [], "any", false, false, false, 21);
                    yield "</a>
                </div>
              </li>
            ";
                } else {
                    // line 25
                    yield "              <li class=\"nav-item\"><a href=\"";
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["category"], "href", [], "any", false, false, false, 25);
                    yield "\" class=\"nav-link\">";
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["category"], "name", [], "any", false, false, false, 25);
                    yield "</a></li>
            ";
                }
                // line 27
                yield "          ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['category'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 28
            yield "        </ul>
      </div>
    </nav>
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
        return "catalog/view/template/common/menu.twig";
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
        return array (  128 => 28,  122 => 27,  114 => 25,  103 => 21,  100 => 20,  93 => 18,  82 => 16,  78 => 15,  75 => 14,  71 => 13,  62 => 10,  59 => 9,  55 => 8,  48 => 4,  44 => 2,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "catalog/view/template/common/menu.twig", "/Users/mezbjen/Desktop/meschain-sync-enterprise-1/RESTRUCTURED_UPLOAD/opencart4_clean/catalog/view/template/common/menu.twig");
    }
}
