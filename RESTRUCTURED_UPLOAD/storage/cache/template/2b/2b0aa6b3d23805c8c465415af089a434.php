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

/* catalog/view/template/common/footer.twig */
class __TwigTemplate_ee93672b058dec43fa1e6d35494bd7fd extends Template
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
        yield "</main>
<footer>
  <div class=\"container\">
    <div class=\"row\">
      <div class=\"col-sm-3\">
        <h5>";
        // line 6
        yield ($context["text_information"] ?? null);
        yield "</h5>
        <ul class=\"list-unstyled\">
          ";
        // line 8
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["informations"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["information"]) {
            // line 9
            yield "            <li><a href=\"";
            yield CoreExtension::getAttribute($this->env, $this->source, $context["information"], "href", [], "any", false, false, false, 9);
            yield "\">";
            yield CoreExtension::getAttribute($this->env, $this->source, $context["information"], "title", [], "any", false, false, false, 9);
            yield "</a></li>
          ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['information'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 11
        yield "        </ul>
      </div>
      <div class=\"col-sm-3\">
        <h5>";
        // line 14
        yield ($context["text_service"] ?? null);
        yield "</h5>
        <ul class=\"list-unstyled\">
          <li><a href=\"";
        // line 16
        yield ($context["contact"] ?? null);
        yield "\">";
        yield ($context["text_contact"] ?? null);
        yield "</a></li>
          <li><a href=\"";
        // line 17
        yield ($context["return"] ?? null);
        yield "\">";
        yield ($context["text_return"] ?? null);
        yield "</a></li>
          ";
        // line 18
        if ((($tmp = ($context["gdpr"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 19
            yield "            <li><a href=\"";
            yield ($context["gdpr"] ?? null);
            yield "\">";
            yield ($context["text_gdpr"] ?? null);
            yield "</a></li>
          ";
        }
        // line 21
        yield "          <li><a href=\"";
        yield ($context["sitemap"] ?? null);
        yield "\">";
        yield ($context["text_sitemap"] ?? null);
        yield "</a></li>
        </ul>
      </div>
      <div class=\"col-sm-3\">
        <h5>";
        // line 25
        yield ($context["text_extra"] ?? null);
        yield "</h5>
        <ul class=\"list-unstyled\">
          <li><a href=\"";
        // line 27
        yield ($context["manufacturer"] ?? null);
        yield "\">";
        yield ($context["text_manufacturer"] ?? null);
        yield "</a></li>
          <li><a href=\"";
        // line 28
        yield ($context["voucher"] ?? null);
        yield "\">";
        yield ($context["text_voucher"] ?? null);
        yield "</a></li>
          ";
        // line 29
        if ((($tmp = ($context["affiliate"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 30
            yield "            <li><a href=\"";
            yield ($context["affiliate"] ?? null);
            yield "\">";
            yield ($context["text_affiliate"] ?? null);
            yield "</a></li>
          ";
        }
        // line 32
        yield "          <li><a href=\"";
        yield ($context["special"] ?? null);
        yield "\">";
        yield ($context["text_special"] ?? null);
        yield "</a></li>
        </ul>
      </div>
      <div class=\"col-sm-3\">
        <h5>";
        // line 36
        yield ($context["text_account"] ?? null);
        yield "</h5>
        <ul class=\"list-unstyled\">
          <li><a href=\"";
        // line 38
        yield ($context["account"] ?? null);
        yield "\">";
        yield ($context["text_account"] ?? null);
        yield "</a></li>
          <li><a href=\"";
        // line 39
        yield ($context["order"] ?? null);
        yield "\">";
        yield ($context["text_order"] ?? null);
        yield "</a></li>
          <li><a href=\"";
        // line 40
        yield ($context["wishlist"] ?? null);
        yield "\">";
        yield ($context["text_wishlist"] ?? null);
        yield "</a></li>
          <li><a href=\"";
        // line 41
        yield ($context["newsletter"] ?? null);
        yield "\">";
        yield ($context["text_newsletter"] ?? null);
        yield "</a></li>
        </ul>
      </div>
    </div>
    <hr>
    <p>";
        // line 46
        yield ($context["powered"] ?? null);
        yield "</p>
    <!--
OpenCart is open source software and you are free to remove the powered by OpenCart if you want, but its generally accepted practise to make a small donation.
Please donate via PayPal to donate@opencart.com
//-->
  </div>
</footer>
";
        // line 53
        yield ($context["cookie"] ?? null);
        yield "
<script src=\"";
        // line 54
        yield ($context["bootstrap"] ?? null);
        yield "\" type=\"text/javascript\"></script>
";
        // line 55
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["scripts"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["script"]) {
            // line 56
            yield "  <script src=\"";
            yield CoreExtension::getAttribute($this->env, $this->source, $context["script"], "href", [], "any", false, false, false, 56);
            yield "\" type=\"text/javascript\"></script>
";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['script'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 58
        yield "</body></html>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "catalog/view/template/common/footer.twig";
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
        return array (  212 => 58,  203 => 56,  199 => 55,  195 => 54,  191 => 53,  181 => 46,  171 => 41,  165 => 40,  159 => 39,  153 => 38,  148 => 36,  138 => 32,  130 => 30,  128 => 29,  122 => 28,  116 => 27,  111 => 25,  101 => 21,  93 => 19,  91 => 18,  85 => 17,  79 => 16,  74 => 14,  69 => 11,  58 => 9,  54 => 8,  49 => 6,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "catalog/view/template/common/footer.twig", "/Users/mezbjen/Desktop/meschain-sync-enterprise-1/RESTRUCTURED_UPLOAD/opencart4_clean/catalog/view/template/common/footer.twig");
    }
}
