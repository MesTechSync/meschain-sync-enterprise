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

/* admin/view/template/common/security.twig */
class __TwigTemplate_27f36131ce7ae3ddd5e47d09b74580c5 extends Template
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
        yield "<div id=\"modal-security\" class=\"modal show\">
  <div class=\"modal-dialog\">
    <div class=\"modal-content\">
      <div class=\"modal-header\">
        <h5 class=\"modal-title text-danger\"><i class=\"fa-solid fa-triangle-exclamation\"></i> ";
        // line 5
        yield ($context["heading_title"] ?? null);
        yield "</h5>
        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"modal\"></button>
      </div>
      <div id=\"accordion\" class=\"accordion\">

        ";
        // line 10
        if ((($tmp = ($context["install"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 11
            yield "          <div id=\"security-install\" class=\"accordion-item\">
            <h5 class=\"accordion-header\"><button type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#accordion-install\" class=\"accordion-button collapsed\"><span class=\"fa-solid fa-folder\"></span>&nbsp;&nbsp;";
            // line 12
            yield ($context["text_install"] ?? null);
            yield "</button></h5>
            <div id=\"accordion-install\" class=\"accordion-collapse collapse\" data-bs-parent=\"#accordion\">
              <div class=\"modal-body\">
                <p>";
            // line 15
            yield ($context["text_install_description"] ?? null);
            yield "</p>
                <div class=\"mb-3\">
                  <div class=\"input-group\">
                    <div class=\"input-group-text\">";
            // line 18
            yield ($context["text_path"] ?? null);
            yield "</div>
                    <input type=\"text\" value=\"";
            // line 19
            yield ($context["install"] ?? null);
            yield "\" class=\"form-control is-invalid bg-white\" readonly/>
                  </div>
                </div>
                <div class=\"text-end\">
                  <button type=\"button\" id=\"button-install\" class=\"btn btn-danger\"><i class=\"fa-regular fa-trash-can\"></i> ";
            // line 23
            yield ($context["button_delete"] ?? null);
            yield "</button>
                </div>
              </div>
            </div>
          </div>
        ";
        }
        // line 29
        yield "
        ";
        // line 30
        if ((($tmp = ($context["storage"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 31
            yield "          <div id=\"security-storage\" class=\"accordion-item\">
            <h2 class=\"accordion-header\"><button type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#accordion-storage\" class=\"accordion-button collapsed\"><i class=\"fa-solid fa-circle-right\"></i>&nbsp;&nbsp;";
            // line 32
            yield ($context["text_storage"] ?? null);
            yield "</button></h2>
            <div id=\"accordion-storage\" class=\"accordion-collapse collapse\" data-bs-parent=\"#accordion\">
              <div class=\"modal-body\">
                <form id=\"form-storage\">
                  <p>";
            // line 36
            yield ($context["text_storage_description"] ?? null);
            yield "</p>
                  <div class=\"mb-3\">
                    <label class=\"form-label\">";
            // line 38
            yield ($context["entry_path_current"] ?? null);
            yield "</label>
                    <input type=\"text\" value=\"";
            // line 39
            yield ($context["storage"] ?? null);
            yield "\" class=\"form-control is-invalid bg-white\" readonly/>
                  </div>
                  <div class=\"mb-3\">
                    <label class=\"form-label\">";
            // line 42
            yield ($context["entry_path_new"] ?? null);
            yield "</label>
                    <div class=\"input-group dropdown\">
                      <button type=\"button\" id=\"button-path\" data-bs-toggle=\"dropdown\" class=\"btn btn-outline-secondary dropdown-toggle\">";
            // line 44
            yield ($context["document_root"] ?? null);
            yield " <span class=\"fa-solid fa-caret-down\"></span></button>
                      <ul class=\"dropdown-menu\">
                        ";
            // line 46
            $context['_parent'] = $context;
            $context['_seq'] = CoreExtension::ensureTraversable(($context["paths"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["path"]) {
                // line 47
                yield "                          <li><a href=\"";
                yield $context["path"];
                yield "\" class=\"dropdown-item\">";
                yield $context["path"];
                yield "</a></li>
                        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_key'], $context['path'], $context['_parent']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 49
            yield "                      </ul>
                      <input type=\"text\" name=\"name\" value=\"storage\" placeholder=\"";
            // line 50
            yield ($context["text_path"] ?? null);
            yield "\" id=\"input-storage\" class=\"form-control\"/>
                    </div>
                    <input type=\"hidden\" name=\"path\" value=\"";
            // line 52
            yield ($context["document_root"] ?? null);
            yield "\" id=\"input-path\"/>
                  </div>
                  <div class=\"text-end\">
                    <button type=\"button\" id=\"button-storage\" class=\"btn btn-danger\"><span class=\"fa-solid fa-circle-right\"></span> ";
            // line 55
            yield ($context["button_move"] ?? null);
            yield "</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        ";
        }
        // line 62
        yield "
        ";
        // line 63
        if ((($tmp = ($context["admin"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 64
            yield "        <div id=\"security-admin\" class=\"accordion-item\">
          <h2 class=\"accordion-header\"><button type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#accordion-admin\" class=\"accordion-button collapsed\"><span class=\"fa-solid fa-lock\"></span>&nbsp;&nbsp;";
            // line 65
            yield ($context["text_admin"] ?? null);
            yield "</button></h2>
          <div id=\"accordion-admin\" class=\"accordion-collapse collapse\" data-bs-parent=\"#accordion\">
            <div class=\"modal-body\">
              <form id=\"form-admin\">
                <p>";
            // line 69
            yield ($context["text_admin_description"] ?? null);
            yield "</p>
                <div class=\"mb-3\">
                  <div class=\"input-group\">
                    <div class=\"input-group-text\">";
            // line 72
            yield ($context["text_path"] ?? null);
            yield "</div>
                    <input type=\"text\" name=\"name\" value=\"admin\" placeholder=\"";
            // line 73
            yield ($context["entry_name"] ?? null);
            yield "\" id=\"input-admin\" class=\"form-control is-invalid\"/>
                  </div>
                </div>
                <div class=\"text-end\">
                  <button type=\"button\" id=\"button-admin\" class=\"btn btn-danger\"><i class=\"fa-solid fa-pencil\"></i> ";
            // line 77
            yield ($context["button_rename"] ?? null);
            yield "</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
      ";
        }
        // line 85
        yield "    </div>
  </div>
</div>
<script type=\"text/javascript\"><!--
\$(document).ready(function () {
    // Show modal
    var modal = new bootstrap.Modal(\$('#modal-security'));

    modal.show();

    \$('#accordion .accordion-header:first button').trigger('click');
});

\$('#button-install').on('click', function () {
    var element = this;

    \$.ajax({
        url: 'index.php?route=common/security.install&user_token=";
        // line 102
        yield ($context["user_token"] ?? null);
        yield "',
        dataType: 'json',
        beforeSend: function () {
            \$(element).button('loading');
        },
        complete: function () {
            \$(element).button('reset');
        },
        success: function (json) {
            \$('.alert-dismissible').remove();

            if (json['error']) {
                \$('#alert').prepend('<div class=\"alert alert-danger alert-dismissible\"><i class=\"fa-solid fa-circle-exclamation\"></i> ' + json['error'] + ' <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>');
            }

            if (json['success']) {
                \$('#alert').prepend('<div class=\"alert alert-success alert-dismissible\"><i class=\"fa-solid fa-check-circle\"></i> ' + json['success'] + ' <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>');

                \$('#security-install').remove();

                \$('#accordion .accordion-header:first button').trigger('click');
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log(thrownError + \"\\r\\n\" + xhr.statusText + \"\\r\\n\" + xhr.responseText);
        }
    });
});

\$('#form-storage .dropdown-menu a').on('click', function (e) {
    e.preventDefault();

    \$('#input-path').val(\$(this).attr('href'));

    \$('#button-path').html(\$(this).attr('href') + ' <span class=\"fa-solid fa-caret-down\"></span>');
});

\$('#button-storage').on('click', function () {
    var element = this;

    \$(element).button('loading');

    var next = 'index.php?route=common/security.storage&user_token=";
        // line 144
        yield ($context["user_token"] ?? null);
        yield "&name=' + encodeURIComponent(\$('#input-storage').val()) + '&path=' + encodeURIComponent(\$('#input-path').val());

    var storage = function () {
        return \$.ajax({
            url: next,
            dataType: 'json',
            contentType: 'application/x-www-form-urlencoded',
            success: function (json) {
                console.log(json);

                \$('.alert-dismissible').remove();

                if (json['error']) {
                    \$('#alert').prepend('<div class=\"alert alert-danger alert-dismissible\"><i class=\"fa-solid fa-circle-exclamation\"></i> ' + json['error'] + ' <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>');

                    \$(element).button('reset');
                }

                if (json['text']) {
                    \$('#alert').prepend('<div class=\"alert alert-success alert-dismissible\"><i class=\"fa-solid fa-check-circle-circle\"></i> ' + json['text'] + ' <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>');
                }

                if (json['success']) {
                    \$('#alert').prepend('<div class=\"alert alert-success alert-dismissible\"><i class=\"fa-solid fa-check-circle\"></i> ' + json['success'] + ' <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>');

                    \$(element).button('reset');

                    \$('#security-storage').remove();

                    \$('#accordion .accordion-header:first button').trigger('click');
                }

                if (json['next']) {
                    next = json['next'];

                    chain.attach(storage);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(thrownError + \"\\r\\n\" + xhr.statusText + \"\\r\\n\" + xhr.responseText);

                \$(element).button('reset');
            }
        });
    };

    chain.attach(storage);
});

\$('#button-admin').on('click', function () {
    var element = this;

    \$(element).button('loading');

    var next = 'index.php?route=common/security.admin&user_token=";
        // line 198
        yield ($context["user_token"] ?? null);
        yield "&name=' + encodeURIComponent(\$('#input-admin').val());

    var admin = function () {
        return \$.ajax({
            url: next,
            dataType: 'json',
            contentType: 'application/x-www-form-urlencoded',
            success: function (json) {
                console.log(json);

                \$('.alert-dismissible').remove();

                if (json['redirect']) {
                    location = json['redirect'];
                }

                if (json['error']) {
                    \$('#alert').prepend('<div class=\"alert alert-danger alert-dismissible\"><i class=\"fa-solid fa-circle-exclamation\"></i> ' + json['error'] + ' <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>');

                    \$(element).button('reset');
                }

                if (json['text']) {
                    \$('#alert').prepend('<div class=\"alert alert-success alert-dismissible\"><i class=\"fa-solid fa-check-circle-circle\"></i> ' + json['text'] + ' <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>');
                }

                if (json['success']) {
                    \$('#alert').prepend('<div class=\"alert alert-success alert-dismissible\"><i class=\"fa-solid fa-check-circle\"></i> ' + json['success'] + ' <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>');

                    \$(element).button('reset');

                    \$('#security-admin').remove();

                    \$('#accordion .accordion-header:first button').trigger('click');
                }

                if (json['next']) {
                    next = json['next'];

                    chain.attach(admin);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(thrownError + \"\\r\\n\" + xhr.statusText + \"\\r\\n\" + xhr.responseText);

                \$(element).button('reset');
            }
        });
    };

    chain.attach(admin);
});
//--></script>
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "admin/view/template/common/security.twig";
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
        return array (  336 => 198,  279 => 144,  234 => 102,  215 => 85,  204 => 77,  197 => 73,  193 => 72,  187 => 69,  180 => 65,  177 => 64,  175 => 63,  172 => 62,  162 => 55,  156 => 52,  151 => 50,  148 => 49,  137 => 47,  133 => 46,  128 => 44,  123 => 42,  117 => 39,  113 => 38,  108 => 36,  101 => 32,  98 => 31,  96 => 30,  93 => 29,  84 => 23,  77 => 19,  73 => 18,  67 => 15,  61 => 12,  58 => 11,  56 => 10,  48 => 5,  42 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "admin/view/template/common/security.twig", "/Users/mezbjen/Desktop/meschain-sync-enterprise-1/RESTRUCTURED_UPLOAD/opencart4_clean/admin/view/template/common/security.twig");
    }
}
