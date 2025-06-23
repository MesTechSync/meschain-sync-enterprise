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

/* admin/view/template/common/language.twig */
class __TwigTemplate_9eb5a562e5e3bdd5c5477476444f6fed extends Template
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
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["languages"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["language"]) {
            // line 2
            yield "  ";
            if ((CoreExtension::getAttribute($this->env, $this->source, $context["language"], "code", [], "any", false, false, false, 2) == ($context["code"] ?? null))) {
                // line 3
                yield "    <a href=\"";
                yield CoreExtension::getAttribute($this->env, $this->source, $context["language"], "code", [], "any", false, false, false, 3);
                yield "\" class=\"nav-link dropdown-toggle\" data-bs-toggle=\"dropdown\"><img src=\"";
                yield CoreExtension::getAttribute($this->env, $this->source, $context["language"], "image", [], "any", false, false, false, 3);
                yield "\" alt=\"";
                yield CoreExtension::getAttribute($this->env, $this->source, $context["language"], "name", [], "any", false, false, false, 3);
                yield "\" title=\"";
                yield CoreExtension::getAttribute($this->env, $this->source, $context["language"], "name", [], "any", false, false, false, 3);
                yield "\"/></a>
  ";
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['language'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 6
        yield "<ul class=\"dropdown-menu\">
  ";
        // line 7
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["languages"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["language"]) {
            // line 8
            yield "    <li><a href=\"";
            yield CoreExtension::getAttribute($this->env, $this->source, $context["language"], "code", [], "any", false, false, false, 8);
            yield "\" class=\"dropdown-item\"><img src=\"";
            yield CoreExtension::getAttribute($this->env, $this->source, $context["language"], "image", [], "any", false, false, false, 8);
            yield "\" alt=\"";
            yield CoreExtension::getAttribute($this->env, $this->source, $context["language"], "name", [], "any", false, false, false, 8);
            yield "\" title=\"";
            yield CoreExtension::getAttribute($this->env, $this->source, $context["language"], "name", [], "any", false, false, false, 8);
            yield "\"/> ";
            yield CoreExtension::getAttribute($this->env, $this->source, $context["language"], "name", [], "any", false, false, false, 8);
            yield "</a></li>
  ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['language'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 10
        yield "</ul>
<input type=\"hidden\" name=\"redirect\" value=\"";
        // line 11
        yield ($context["redirect"] ?? null);
        yield "\" id=\"input-redirect\"/>
<script type=\"text/javascript\"><!--
\$('#nav-language .dropdown-item').on('click', function (e) {
    e.preventDefault();

    var element = this;

    \$.ajax({
        url: 'index.php?route=common/language.save&user_token=";
        // line 19
        yield ($context["user_token"] ?? null);
        yield "',
        type: 'post',
        data: 'code=' + \$(element).attr('href') + '&redirect=' + encodeURIComponent(\$('#input-redirect').val()),
        dataType: 'json',
        success: function (json) {
            console.log(\$(element).attr('href'));
            console.log(\$('input-redirect').val());

            if (json['redirect']) {
                location = json['redirect'];
            }

            if (json['error']) {
                \$('#alert').prepend('<div class=\"alert alert-danger alert-dismissible\"><i class=\"fa-solid fa-circle-exclamation\"></i> ' + json['error'] + ' <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>');
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log(thrownError + \"\\r\\n\" + xhr.statusText + \"\\r\\n\" + xhr.responseText);
        }
    });
});
//--></script>";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "admin/view/template/common/language.twig";
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
        return array (  103 => 19,  92 => 11,  89 => 10,  72 => 8,  68 => 7,  65 => 6,  49 => 3,  46 => 2,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "admin/view/template/common/language.twig", "/Users/mezbjen/Desktop/meschain-sync-enterprise-1/RESTRUCTURED_UPLOAD/opencart4_clean/admin/view/template/common/language.twig");
    }
}
