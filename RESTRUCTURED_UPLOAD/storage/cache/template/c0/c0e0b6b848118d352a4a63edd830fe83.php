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

/* catalog/view/template/common/cart.twig */
class __TwigTemplate_ea2edfafde1a7a951edd5a5c383b6104 extends Template
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
        yield "<div class=\"dropdown d-grid\">
  <button type=\"button\" data-bs-toggle=\"dropdown\" class=\"btn btn-lg btn-inverse btn-block dropdown-toggle\"><i class=\"fa-solid fa-cart-shopping\"></i> ";
        // line 2
        yield ($context["text_items"] ?? null);
        yield "</button>
  <ul class=\"dropdown-menu dropdown-menu-end p-2\">
    ";
        // line 4
        if ((($context["products"] ?? null) || ($context["vouchers"] ?? null))) {
            // line 5
            yield "      <li>
        <table class=\"table table-striped mb-2\">
          ";
            // line 7
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["products"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["product"]) {
                // line 8
                yield "            <tr>
              <td class=\"text-center\">";
                // line 9
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["product"], "thumb", [], "any", false, false, false, 9)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    yield "<a href=\"";
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["product"], "href", [], "any", false, false, false, 9);
                    yield "\"><img src=\"";
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["product"], "thumb", [], "any", false, false, false, 9);
                    yield "\" alt=\"";
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["product"], "name", [], "any", false, false, false, 9);
                    yield "\" title=\"";
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["product"], "name", [], "any", false, false, false, 9);
                    yield "\" class=\"img-thumbnail\"/></a>";
                }
                yield "</td>
              <td class=\"text-start\"><a href=\"";
                // line 10
                yield CoreExtension::getAttribute($this->env, $this->source, $context["product"], "href", [], "any", false, false, false, 10);
                yield "\">";
                yield CoreExtension::getAttribute($this->env, $this->source, $context["product"], "name", [], "any", false, false, false, 10);
                yield "</a>
                ";
                // line 11
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["product"], "option", [], "any", false, false, false, 11)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 12
                    yield "                  ";
                    $context['_parent'] = $context;
                    $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, $context["product"], "option", [], "any", false, false, false, 12));
                    foreach ($context['_seq'] as $context["_key"] => $context["option"]) {
                        // line 13
                        yield "                    <br/>
                    <small> - ";
                        // line 14
                        yield CoreExtension::getAttribute($this->env, $this->source, $context["option"], "name", [], "any", false, false, false, 14);
                        yield ": ";
                        yield CoreExtension::getAttribute($this->env, $this->source, $context["option"], "value", [], "any", false, false, false, 14);
                        yield "</small>
                  ";
                    }
                    $_parent = $context['_parent'];
                    unset($context['_seq'], $context['_key'], $context['option'], $context['_parent']);
                    $context = array_intersect_key($context, $_parent) + $_parent;
                    // line 16
                    yield "                ";
                }
                // line 17
                yield "                ";
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["product"], "reward", [], "any", false, false, false, 17)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 18
                    yield "                  <br/>
                  <small> - ";
                    // line 19
                    yield ($context["text_points"] ?? null);
                    yield ": ";
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["product"], "reward", [], "any", false, false, false, 19);
                    yield "</small>
                ";
                }
                // line 21
                yield "                ";
                if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["product"], "subscription", [], "any", false, false, false, 21)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                    // line 22
                    yield "                  <br/>
                  <small> - ";
                    // line 23
                    yield ($context["text_subscription"] ?? null);
                    yield ": ";
                    yield CoreExtension::getAttribute($this->env, $this->source, $context["product"], "subscription", [], "any", false, false, false, 23);
                    yield "</small>
                ";
                }
                // line 25
                yield "              </td>
              <td class=\"text-end\">x ";
                // line 26
                yield CoreExtension::getAttribute($this->env, $this->source, $context["product"], "quantity", [], "any", false, false, false, 26);
                yield "</td>
              <td class=\"text-end\">";
                // line 27
                yield CoreExtension::getAttribute($this->env, $this->source, $context["product"], "total", [], "any", false, false, false, 27);
                yield "</td>
              <td class=\"text-end\">

                <form action=\"";
                // line 30
                yield ($context["product_remove"] ?? null);
                yield "\" method=\"post\" data-oc-toggle=\"ajax\" data-oc-load=\"";
                yield ($context["list"] ?? null);
                yield "\" data-oc-target=\"#header-cart\">
                  <input type=\"hidden\" name=\"key\" value=\"";
                // line 31
                yield CoreExtension::getAttribute($this->env, $this->source, $context["product"], "cart_id", [], "any", false, false, false, 31);
                yield "\">
                  <button type=\"submit\" data-bs-toggle=\"tooltip\" title=\"";
                // line 32
                yield ($context["button_remove"] ?? null);
                yield "\" class=\"btn btn-danger\"><i class=\"fa-solid fa-circle-xmark\"></i></button>
                </form>
              </td>
            </tr>
          ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['product'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 37
            yield "
          ";
            // line 38
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["vouchers"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["voucher"]) {
                // line 39
                yield "            <tr>
              <td class=\"text-center\"></td>
              <td class=\"text-start\">";
                // line 41
                yield CoreExtension::getAttribute($this->env, $this->source, $context["voucher"], "description", [], "any", false, false, false, 41);
                yield "</td>
              <td class=\"text-end\">x&nbsp;1</td>
              <td class=\"text-end\">";
                // line 43
                yield CoreExtension::getAttribute($this->env, $this->source, $context["voucher"], "amount", [], "any", false, false, false, 43);
                yield "</td>
              <td class=\"text-end\">
                <form action=\"";
                // line 45
                yield ($context["voucher_remove"] ?? null);
                yield "\" method=\"post\" data-oc-toggle=\"ajax\" data-oc-load=\"";
                yield ($context["list"] ?? null);
                yield "\" data-oc-target=\"#header-cart\">
                  <input type=\"hidden\" name=\"key\" value=\"";
                // line 46
                yield CoreExtension::getAttribute($this->env, $this->source, $context["voucher"], "key", [], "any", false, false, false, 46);
                yield "\"/>
                  <button type=\"submit\" data-bs-toggle=\"tooltip\" title=\"";
                // line 47
                yield ($context["button_remove"] ?? null);
                yield "\" class=\"btn btn-danger\"><i class=\"fa-solid fa-circle-xmark\"></i></button>
                </form>
              </td>
            </tr>
          ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['voucher'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 52
            yield "
        </table>

        <div>
          <table class=\"table table-sm table-bordered mb-2\">
            ";
            // line 57
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["totals"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["total"]) {
                // line 58
                yield "              <tr>
                <td class=\"text-end\"><strong>";
                // line 59
                yield CoreExtension::getAttribute($this->env, $this->source, $context["total"], "title", [], "any", false, false, false, 59);
                yield "</strong></td>
                <td class=\"text-end\">";
                // line 60
                yield CoreExtension::getAttribute($this->env, $this->source, $context["total"], "text", [], "any", false, false, false, 60);
                yield "</td>
              </tr>
            ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['total'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 63
            yield "          </table>
          <p class=\"text-end\"><a href=\"";
            // line 64
            yield ($context["cart"] ?? null);
            yield "\"><strong><i class=\"fa-solid fa-cart-shopping\"></i> ";
            yield ($context["text_cart"] ?? null);
            yield "</strong></a>&nbsp;&nbsp;&nbsp;<a href=\"";
            yield ($context["checkout"] ?? null);
            yield "\"><strong><i class=\"fa-solid fa-share\"></i> ";
            yield ($context["text_checkout"] ?? null);
            yield "</strong></a></p>
        </div>
      </li>
    ";
        } else {
            // line 68
            yield "      <li class=\"text-center p-4\">";
            yield ($context["text_no_results"] ?? null);
            yield "</li>
    ";
        }
        // line 70
        yield "  </ul>
</div>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "catalog/view/template/common/cart.twig";
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
        return array (  257 => 70,  251 => 68,  238 => 64,  235 => 63,  226 => 60,  222 => 59,  219 => 58,  215 => 57,  208 => 52,  197 => 47,  193 => 46,  187 => 45,  182 => 43,  177 => 41,  173 => 39,  169 => 38,  166 => 37,  155 => 32,  151 => 31,  145 => 30,  139 => 27,  135 => 26,  132 => 25,  125 => 23,  122 => 22,  119 => 21,  112 => 19,  109 => 18,  106 => 17,  103 => 16,  93 => 14,  90 => 13,  85 => 12,  83 => 11,  77 => 10,  63 => 9,  60 => 8,  56 => 7,  52 => 5,  50 => 4,  45 => 2,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "catalog/view/template/common/cart.twig", "/Users/mezbjen/Desktop/meschain-sync-enterprise-1/RESTRUCTURED_UPLOAD/opencart4_clean/catalog/view/template/common/cart.twig");
    }
}
