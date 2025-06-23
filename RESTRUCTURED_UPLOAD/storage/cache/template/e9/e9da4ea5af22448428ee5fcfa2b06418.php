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

/* admin/view/template/extension/module/meschain_sync.twig */
class __TwigTemplate_e3efcea91b7a5328ac85b4a529ac6289 extends Template
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
        // line 2
        yield ($context["header"] ?? null);
        yield ($context["column_left"] ?? null);
        yield "

<div id=\"content\">
\t<div class=\"page-header\">
\t\t<div class=\"container-fluid\">
\t\t\t<div class=\"float-end\">
\t\t\t\t<button type=\"submit\" form=\"form-module\" data-bs-toggle=\"tooltip\" title=\"";
        // line 8
        yield ($context["button_save"] ?? null);
        yield "\" class=\"btn btn-primary\">
\t\t\t\t\t<i class=\"fa-solid fa-save\"></i>
\t\t\t\t</button>
\t\t\t\t<a href=\"";
        // line 11
        yield ($context["cancel"] ?? null);
        yield "\" data-bs-toggle=\"tooltip\" title=\"";
        yield ($context["button_cancel"] ?? null);
        yield "\" class=\"btn btn-light\">
\t\t\t\t\t<i class=\"fa-solid fa-reply\"></i>
\t\t\t\t</a>
\t\t\t</div>
\t\t\t<h1>";
        // line 15
        yield ($context["heading_title"] ?? null);
        yield "</h1>
\t\t\t<ol class=\"breadcrumb\">
\t\t\t\t";
        // line 17
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["breadcrumbs"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["breadcrumb"]) {
            // line 18
            yield "\t\t\t\t\t<li class=\"breadcrumb-item\">
\t\t\t\t\t\t<a href=\"";
            // line 19
            yield CoreExtension::getAttribute($this->env, $this->source, $context["breadcrumb"], "href", [], "any", false, false, false, 19);
            yield "\">";
            yield CoreExtension::getAttribute($this->env, $this->source, $context["breadcrumb"], "text", [], "any", false, false, false, 19);
            yield "</a>
\t\t\t\t\t</li>
\t\t\t\t";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['breadcrumb'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 22
        yield "\t\t\t</ol>
\t\t</div>
\t</div>

\t<div class=\"container-fluid\">
\t\t";
        // line 27
        if ((($tmp = ($context["error_warning"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 28
            yield "\t\t\t<div class=\"alert alert-danger alert-dismissible\">
\t\t\t\t<i class=\"fa-solid fa-circle-exclamation\"></i>
\t\t\t\t";
            // line 30
            yield ($context["error_warning"] ?? null);
            yield "
\t\t\t\t<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>
\t\t\t</div>
\t\t";
        }
        // line 34
        yield "
\t\t";
        // line 35
        if ((($tmp = ($context["success"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            // line 36
            yield "\t\t\t<div class=\"alert alert-success alert-dismissible\">
\t\t\t\t<i class=\"fa-solid fa-circle-check\"></i>
\t\t\t\t";
            // line 38
            yield ($context["success"] ?? null);
            yield "
\t\t\t\t<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button>
\t\t\t</div>
\t\t";
        }
        // line 42
        yield "
\t\t<!-- Dashboard Stats -->
\t\t<div class=\"row mb-4\" id=\"dashboard-stats\">
\t\t\t<div class=\"col-md-3 col-sm-6\">
\t\t\t\t<div class=\"card text-center\">
\t\t\t\t\t<div class=\"card-body\">
\t\t\t\t\t\t<h3 class=\"text-primary\">
\t\t\t\t\t\t\t<i class=\"fa-solid fa-boxes-stacked\"></i>
\t\t\t\t\t\t</h3>
\t\t\t\t\t\t<h4 id=\"total-products\">0</h4>
\t\t\t\t\t\t<p class=\"text-muted\">";
        // line 52
        yield ($context["text_total_products"] ?? null);
        yield "</p>
\t\t\t\t\t</div>
\t\t\t\t</div>
\t\t\t</div>
\t\t\t<div class=\"col-md-3 col-sm-6\">
\t\t\t\t<div class=\"card text-center\">
\t\t\t\t\t<div class=\"card-body\">
\t\t\t\t\t\t<h3 class=\"text-success\">
\t\t\t\t\t\t\t<i class=\"fa-solid fa-sync\"></i>
\t\t\t\t\t\t</h3>
\t\t\t\t\t\t<h4 id=\"synced-products\">0</h4>
\t\t\t\t\t\t<p class=\"text-muted\">";
        // line 63
        yield ($context["text_synced_products"] ?? null);
        yield "</p>
\t\t\t\t\t</div>
\t\t\t\t</div>
\t\t\t</div>
\t\t\t<div class=\"col-md-3 col-sm-6\">
\t\t\t\t<div class=\"card text-center\">
\t\t\t\t\t<div class=\"card-body\">
\t\t\t\t\t\t<h3 class=\"text-info\">
\t\t\t\t\t\t\t<i class=\"fa-solid fa-store\"></i>
\t\t\t\t\t\t</h3>
\t\t\t\t\t\t<h4 id=\"active-marketplaces\">0</h4>
\t\t\t\t\t\t<p class=\"text-muted\">";
        // line 74
        yield ($context["text_active_marketplaces"] ?? null);
        yield "</p>
\t\t\t\t\t</div>
\t\t\t\t</div>
\t\t\t</div>
\t\t\t<div class=\"col-md-3 col-sm-6\">
\t\t\t\t<div class=\"card text-center\">
\t\t\t\t\t<div class=\"card-body\">
\t\t\t\t\t\t<h3 class=\"text-warning\">
\t\t\t\t\t\t\t<i class=\"fa-solid fa-chart-line\"></i>
\t\t\t\t\t\t</h3>
\t\t\t\t\t\t<h4 id=\"sync-rate\">0%</h4>
\t\t\t\t\t\t<p class=\"text-muted\">";
        // line 85
        yield ($context["text_sync_rate"] ?? null);
        yield "</p>
\t\t\t\t\t</div>
\t\t\t\t</div>
\t\t\t</div>
\t\t</div>

\t\t<!-- Main Content -->
\t\t<div class=\"card\">
\t\t\t<div class=\"card-header\">
\t\t\t\t<h3 class=\"card-title\">
\t\t\t\t\t<i class=\"fa-solid fa-pencil\"></i>
\t\t\t\t\t";
        // line 96
        yield ($context["text_edit"] ?? null);
        yield "</h3>
\t\t\t</div>
\t\t\t<div class=\"card-body\">
\t\t\t\t<form
\t\t\t\t\tid=\"form-module\" action=\"";
        // line 100
        yield ($context["action"] ?? null);
        yield "\" method=\"post\" enctype=\"multipart/form-data\">

\t\t\t\t\t<!-- Navigation Tabs -->
\t\t\t\t\t<ul class=\"nav nav-tabs\" role=\"tablist\">
\t\t\t\t\t\t<li class=\"nav-item\">
\t\t\t\t\t\t\t<a class=\"nav-link active\" href=\"#tab-general\" data-bs-toggle=\"tab\">
\t\t\t\t\t\t\t\t<i class=\"fa-solid fa-cog\"></i>
\t\t\t\t\t\t\t\t";
        // line 107
        yield ($context["tab_general"] ?? null);
        yield "
\t\t\t\t\t\t\t</a>
\t\t\t\t\t\t</li>
\t\t\t\t\t\t<li class=\"nav-item\">
\t\t\t\t\t\t\t<a class=\"nav-link\" href=\"#tab-marketplaces\" data-bs-toggle=\"tab\">
\t\t\t\t\t\t\t\t<i class=\"fa-solid fa-store\"></i>
\t\t\t\t\t\t\t\t";
        // line 113
        yield ($context["tab_marketplaces"] ?? null);
        yield "
\t\t\t\t\t\t\t</a>
\t\t\t\t\t\t</li>
\t\t\t\t\t\t<li class=\"nav-item\">
\t\t\t\t\t\t\t<a class=\"nav-link\" href=\"#tab-products\" data-bs-toggle=\"tab\">
\t\t\t\t\t\t\t\t<i class=\"fa-solid fa-boxes-stacked\"></i>
\t\t\t\t\t\t\t\t";
        // line 119
        yield ($context["tab_products"] ?? null);
        yield "
\t\t\t\t\t\t\t</a>
\t\t\t\t\t\t</li>
\t\t\t\t\t\t<li class=\"nav-item\">
\t\t\t\t\t\t\t<a class=\"nav-link\" href=\"#tab-orders\" data-bs-toggle=\"tab\">
\t\t\t\t\t\t\t\t<i class=\"fa-solid fa-shopping-cart\"></i>
\t\t\t\t\t\t\t\t";
        // line 125
        yield ($context["tab_orders"] ?? null);
        yield "
\t\t\t\t\t\t\t</a>
\t\t\t\t\t\t</li>
\t\t\t\t\t\t<li class=\"nav-item\">
\t\t\t\t\t\t\t<a class=\"nav-link\" href=\"#tab-analytics\" data-bs-toggle=\"tab\">
\t\t\t\t\t\t\t\t<i class=\"fa-solid fa-chart-line\"></i>
\t\t\t\t\t\t\t\t";
        // line 131
        yield ($context["tab_analytics"] ?? null);
        yield "
\t\t\t\t\t\t\t</a>
\t\t\t\t\t\t</li>
\t\t\t\t\t\t<li class=\"nav-item\">
\t\t\t\t\t\t\t<a class=\"nav-link\" href=\"#tab-logs\" data-bs-toggle=\"tab\">
\t\t\t\t\t\t\t\t<i class=\"fa-solid fa-file-lines\"></i>
\t\t\t\t\t\t\t\t";
        // line 137
        yield ($context["tab_logs"] ?? null);
        yield "
\t\t\t\t\t\t\t</a>
\t\t\t\t\t\t</li>
\t\t\t\t\t</ul>

\t\t\t\t\t<!-- Tab Content -->
\t\t\t\t\t<div
\t\t\t\t\t\tclass=\"tab-content mt-3\">

\t\t\t\t\t\t<!-- General Tab -->
\t\t\t\t\t\t<div class=\"tab-pane fade show active\" id=\"tab-general\">
\t\t\t\t\t\t\t<div class=\"row mb-3\">
\t\t\t\t\t\t\t\t<label class=\"col-sm-2 col-form-label\" for=\"input-status\">";
        // line 149
        yield ($context["entry_status"] ?? null);
        yield "</label>
\t\t\t\t\t\t\t\t<div class=\"col-sm-10\">
\t\t\t\t\t\t\t\t\t<select name=\"module_meschain_sync_status\" id=\"input-status\" class=\"form-select\">
\t\t\t\t\t\t\t\t\t\t<option value=\"0\" ";
        // line 152
        if ((($tmp =  !($context["module_meschain_sync_status"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield " selected ";
        }
        yield ">";
        yield ($context["text_disabled"] ?? null);
        yield "</option>
\t\t\t\t\t\t\t\t\t\t<option value=\"1\" ";
        // line 153
        if ((($tmp = ($context["module_meschain_sync_status"] ?? null)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
            yield " selected ";
        }
        yield ">";
        yield ($context["text_enabled"] ?? null);
        yield "</option>
\t\t\t\t\t\t\t\t\t</select>
\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t</div>

\t\t\t\t\t\t\t<div class=\"row mb-3\">
\t\t\t\t\t\t\t\t<label class=\"col-sm-2 col-form-label\">";
        // line 159
        yield ($context["entry_cron_url"] ?? null);
        yield "</label>
\t\t\t\t\t\t\t\t<div class=\"col-sm-10\">
\t\t\t\t\t\t\t\t\t<div class=\"input-group\">
\t\t\t\t\t\t\t\t\t\t<input type=\"text\" value=\"";
        // line 162
        yield ($context["cron_url"] ?? null);
        yield "\" class=\"form-control\" readonly>
\t\t\t\t\t\t\t\t\t\t<button type=\"button\" class=\"btn btn-secondary\" onclick=\"copyToClipboard('";
        // line 163
        yield ($context["cron_url"] ?? null);
        yield "')\">
\t\t\t\t\t\t\t\t\t\t\t<i class=\"fa-solid fa-copy\"></i>
\t\t\t\t\t\t\t\t\t\t\t";
        // line 165
        yield ($context["button_copy"] ?? null);
        yield "
\t\t\t\t\t\t\t\t\t\t</button>
\t\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t\t<div class=\"form-text\">";
        // line 168
        yield ($context["help_cron_url"] ?? null);
        yield "</div>
\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t</div>

\t\t\t\t\t\t\t<div class=\"row mb-3\">
\t\t\t\t\t\t\t\t<label class=\"col-sm-2 col-form-label\">";
        // line 173
        yield ($context["entry_system_status"] ?? null);
        yield "</label>
\t\t\t\t\t\t\t\t<div class=\"col-sm-10\">
\t\t\t\t\t\t\t\t\t<div id=\"system-status-container\">
\t\t\t\t\t\t\t\t\t\t<div class=\"spinner-border text-primary\" role=\"status\">
\t\t\t\t\t\t\t\t\t\t\t<span class=\"visually-hidden\">Loading...</span>
\t\t\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t</div>

\t\t\t\t\t\t<!-- Marketplaces Tab -->
\t\t\t\t\t\t<div class=\"tab-pane fade\" id=\"tab-marketplaces\">
\t\t\t\t\t\t\t<div
\t\t\t\t\t\t\t\tclass=\"row\" id=\"marketplace-cards\"><!-- Marketplace cards will be loaded dynamically -->
\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t</div>

\t\t\t\t\t\t<!-- Products Tab -->
\t\t\t\t\t\t<div class=\"tab-pane fade\" id=\"tab-products\">
\t\t\t\t\t\t\t<div class=\"row mb-3\">
\t\t\t\t\t\t\t\t<div class=\"col-md-6\">
\t\t\t\t\t\t\t\t\t<div class=\"input-group\">
\t\t\t\t\t\t\t\t\t\t<input type=\"text\" id=\"product-search\" class=\"form-control\" placeholder=\"";
        // line 196
        yield ($context["text_search_products"] ?? null);
        yield "\">
\t\t\t\t\t\t\t\t\t\t<button type=\"button\" class=\"btn btn-primary\" onclick=\"searchProducts()\">
\t\t\t\t\t\t\t\t\t\t\t<i class=\"fa-solid fa-search\"></i>
\t\t\t\t\t\t\t\t\t\t\t";
        // line 199
        yield ($context["button_search"] ?? null);
        yield "
\t\t\t\t\t\t\t\t\t\t</button>
\t\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t<div class=\"col-md-6 text-end\">
\t\t\t\t\t\t\t\t\t<button type=\"button\" class=\"btn btn-success\" onclick=\"syncAllProducts()\">
\t\t\t\t\t\t\t\t\t\t<i class=\"fa-solid fa-sync\"></i>
\t\t\t\t\t\t\t\t\t\t";
        // line 206
        yield ($context["button_sync_all"] ?? null);
        yield "
\t\t\t\t\t\t\t\t\t</button>
\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t</div>

\t\t\t\t\t\t\t<div class=\"table-responsive\">
\t\t\t\t\t\t\t\t<table class=\"table table-hover\" id=\"products-table\">
\t\t\t\t\t\t\t\t\t<thead>
\t\t\t\t\t\t\t\t\t\t<tr>
\t\t\t\t\t\t\t\t\t\t\t<th>";
        // line 215
        yield ($context["column_image"] ?? null);
        yield "</th>
\t\t\t\t\t\t\t\t\t\t\t<th>";
        // line 216
        yield ($context["column_name"] ?? null);
        yield "</th>
\t\t\t\t\t\t\t\t\t\t\t<th>";
        // line 217
        yield ($context["column_model"] ?? null);
        yield "</th>
\t\t\t\t\t\t\t\t\t\t\t<th>";
        // line 218
        yield ($context["column_price"] ?? null);
        yield "</th>
\t\t\t\t\t\t\t\t\t\t\t<th>";
        // line 219
        yield ($context["column_quantity"] ?? null);
        yield "</th>
\t\t\t\t\t\t\t\t\t\t\t<th>";
        // line 220
        yield ($context["column_sync_status"] ?? null);
        yield "</th>
\t\t\t\t\t\t\t\t\t\t\t<th class=\"text-end\">";
        // line 221
        yield ($context["column_action"] ?? null);
        yield "</th>
\t\t\t\t\t\t\t\t\t\t</tr>
\t\t\t\t\t\t\t\t\t</thead>
\t\t\t\t\t\t\t\t\t<tbody>
\t\t\t\t\t\t\t\t\t\t<!-- Products will be loaded dynamically -->
\t\t\t\t\t\t\t\t\t</tbody>
\t\t\t\t\t\t\t\t</table>
\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t</div>

\t\t\t\t\t\t<!-- Orders Tab -->
\t\t\t\t\t\t<div class=\"tab-pane fade\" id=\"tab-orders\">
\t\t\t\t\t\t\t<div class=\"alert alert-info\">
\t\t\t\t\t\t\t\t<i class=\"fa-solid fa-info-circle\"></i>
\t\t\t\t\t\t\t\t";
        // line 235
        yield ($context["text_orders_info"] ?? null);
        yield "
\t\t\t\t\t\t\t</div>

\t\t\t\t\t\t\t<div class=\"table-responsive\">
\t\t\t\t\t\t\t\t<table class=\"table table-hover\" id=\"orders-table\">
\t\t\t\t\t\t\t\t\t<thead>
\t\t\t\t\t\t\t\t\t\t<tr>
\t\t\t\t\t\t\t\t\t\t\t<th>";
        // line 242
        yield ($context["column_order_id"] ?? null);
        yield "</th>
\t\t\t\t\t\t\t\t\t\t\t<th>";
        // line 243
        yield ($context["column_marketplace"] ?? null);
        yield "</th>
\t\t\t\t\t\t\t\t\t\t\t<th>";
        // line 244
        yield ($context["column_customer"] ?? null);
        yield "</th>
\t\t\t\t\t\t\t\t\t\t\t<th>";
        // line 245
        yield ($context["column_total"] ?? null);
        yield "</th>
\t\t\t\t\t\t\t\t\t\t\t<th>";
        // line 246
        yield ($context["column_status"] ?? null);
        yield "</th>
\t\t\t\t\t\t\t\t\t\t\t<th>";
        // line 247
        yield ($context["column_date_added"] ?? null);
        yield "</th>
\t\t\t\t\t\t\t\t\t\t\t<th class=\"text-end\">";
        // line 248
        yield ($context["column_action"] ?? null);
        yield "</th>
\t\t\t\t\t\t\t\t\t\t</tr>
\t\t\t\t\t\t\t\t\t</thead>
\t\t\t\t\t\t\t\t\t<tbody>
\t\t\t\t\t\t\t\t\t\t<!-- Orders will be loaded dynamically -->
\t\t\t\t\t\t\t\t\t</tbody>
\t\t\t\t\t\t\t\t</table>
\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t</div>

\t\t\t\t\t\t<!-- Analytics Tab -->
\t\t\t\t\t\t<div class=\"tab-pane fade\" id=\"tab-analytics\">
\t\t\t\t\t\t\t<div class=\"row\">
\t\t\t\t\t\t\t\t<div class=\"col-md-6\">
\t\t\t\t\t\t\t\t\t<div class=\"card\">
\t\t\t\t\t\t\t\t\t\t<div class=\"card-header\">
\t\t\t\t\t\t\t\t\t\t\t<h5>
\t\t\t\t\t\t\t\t\t\t\t\t<i class=\"fa-solid fa-chart-bar\"></i>
\t\t\t\t\t\t\t\t\t\t\t\t";
        // line 266
        yield ($context["text_sales_by_marketplace"] ?? null);
        yield "</h5>
\t\t\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t\t\t<div class=\"card-body\">
\t\t\t\t\t\t\t\t\t\t\t<canvas id=\"sales-chart\"></canvas>
\t\t\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t<div class=\"col-md-6\">
\t\t\t\t\t\t\t\t\t<div class=\"card\">
\t\t\t\t\t\t\t\t\t\t<div class=\"card-header\">
\t\t\t\t\t\t\t\t\t\t\t<h5>
\t\t\t\t\t\t\t\t\t\t\t\t<i class=\"fa-solid fa-chart-pie\"></i>
\t\t\t\t\t\t\t\t\t\t\t\t";
        // line 278
        yield ($context["text_product_distribution"] ?? null);
        yield "</h5>
\t\t\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t\t\t<div class=\"card-body\">
\t\t\t\t\t\t\t\t\t\t\t<canvas id=\"distribution-chart\"></canvas>
\t\t\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t</div>

\t\t\t\t\t\t\t<div class=\"row mt-4\">
\t\t\t\t\t\t\t\t<div class=\"col-12\">
\t\t\t\t\t\t\t\t\t<div class=\"card\">
\t\t\t\t\t\t\t\t\t\t<div class=\"card-header\">
\t\t\t\t\t\t\t\t\t\t\t<h5>
\t\t\t\t\t\t\t\t\t\t\t\t<i class=\"fa-solid fa-chart-line\"></i>
\t\t\t\t\t\t\t\t\t\t\t\t";
        // line 293
        yield ($context["text_sync_performance"] ?? null);
        yield "</h5>
\t\t\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t\t\t<div class=\"card-body\">
\t\t\t\t\t\t\t\t\t\t\t<canvas id=\"performance-chart\"></canvas>
\t\t\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t</div>

\t\t\t\t\t\t<!-- Logs Tab -->
\t\t\t\t\t\t<div class=\"tab-pane fade\" id=\"tab-logs\">
\t\t\t\t\t\t\t<div class=\"row mb-3\">
\t\t\t\t\t\t\t\t<div class=\"col-md-4\">
\t\t\t\t\t\t\t\t\t<select id=\"log-level\" class=\"form-select\">
\t\t\t\t\t\t\t\t\t\t<option value=\"\">";
        // line 308
        yield ($context["text_all_levels"] ?? null);
        yield "</option>
\t\t\t\t\t\t\t\t\t\t<option value=\"debug\">Debug</option>
\t\t\t\t\t\t\t\t\t\t<option value=\"info\">Info</option>
\t\t\t\t\t\t\t\t\t\t<option value=\"warning\">Warning</option>
\t\t\t\t\t\t\t\t\t\t<option value=\"error\">Error</option>
\t\t\t\t\t\t\t\t\t\t<option value=\"critical\">Critical</option>
\t\t\t\t\t\t\t\t\t</select>
\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t<div class=\"col-md-4\">
\t\t\t\t\t\t\t\t\t<select id=\"log-marketplace\" class=\"form-select\">
\t\t\t\t\t\t\t\t\t\t<option value=\"\">";
        // line 318
        yield ($context["text_all_marketplaces"] ?? null);
        yield "</option>
\t\t\t\t\t\t\t\t\t\t<option value=\"trendyol\">Trendyol</option>
\t\t\t\t\t\t\t\t\t\t<option value=\"n11\">N11</option>
\t\t\t\t\t\t\t\t\t\t<option value=\"hepsiburada\">Hepsiburada</option>
\t\t\t\t\t\t\t\t\t\t<option value=\"amazon\">Amazon</option>
\t\t\t\t\t\t\t\t\t\t<option value=\"ebay\">eBay</option>
\t\t\t\t\t\t\t\t\t</select>
\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t\t<div class=\"col-md-4\">
\t\t\t\t\t\t\t\t\t<button type=\"button\" class=\"btn btn-primary\" onclick=\"loadLogs()\">
\t\t\t\t\t\t\t\t\t\t<i class=\"fa-solid fa-filter\"></i>
\t\t\t\t\t\t\t\t\t\t";
        // line 329
        yield ($context["button_filter"] ?? null);
        yield "
\t\t\t\t\t\t\t\t\t</button>
\t\t\t\t\t\t\t\t\t<button type=\"button\" class=\"btn btn-danger\" onclick=\"clearLogs()\">
\t\t\t\t\t\t\t\t\t\t<i class=\"fa-solid fa-trash\"></i>
\t\t\t\t\t\t\t\t\t\t";
        // line 333
        yield ($context["button_clear"] ?? null);
        yield "
\t\t\t\t\t\t\t\t\t</button>
\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t</div>

\t\t\t\t\t\t\t<div class=\"table-responsive\">
\t\t\t\t\t\t\t\t<table class=\"table table-sm\" id=\"logs-table\">
\t\t\t\t\t\t\t\t\t<thead>
\t\t\t\t\t\t\t\t\t\t<tr>
\t\t\t\t\t\t\t\t\t\t\t<th>";
        // line 342
        yield ($context["column_datetime"] ?? null);
        yield "</th>
\t\t\t\t\t\t\t\t\t\t\t<th>";
        // line 343
        yield ($context["column_level"] ?? null);
        yield "</th>
\t\t\t\t\t\t\t\t\t\t\t<th>";
        // line 344
        yield ($context["column_type"] ?? null);
        yield "</th>
\t\t\t\t\t\t\t\t\t\t\t<th>";
        // line 345
        yield ($context["column_message"] ?? null);
        yield "</th>
\t\t\t\t\t\t\t\t\t\t\t<th>";
        // line 346
        yield ($context["column_marketplace"] ?? null);
        yield "</th>
\t\t\t\t\t\t\t\t\t\t</tr>
\t\t\t\t\t\t\t\t\t</thead>
\t\t\t\t\t\t\t\t\t<tbody>
\t\t\t\t\t\t\t\t\t\t<!-- Logs will be loaded dynamically -->
\t\t\t\t\t\t\t\t\t</tbody>
\t\t\t\t\t\t\t\t</table>
\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t</div>
\t\t\t\t\t</div>

\t\t\t\t</form>
\t\t\t</div>
\t\t</div>
\t</div>
</div>

<!-- JavaScript -->
<script type=\"text/javascript\">
\t// API endpoints
const API_SEARCH_PRODUCTS = '";
        // line 366
        yield ($context["api_search_products"] ?? null);
        yield "';
const API_SYNC_MARKETPLACE = '";
        // line 367
        yield ($context["api_sync_marketplace"] ?? null);
        yield "';
const API_SYSTEM_STATUS = '";
        // line 368
        yield ($context["api_system_status"] ?? null);
        yield "';

// Initialize on page load
\$(document).ready(function () {
loadSystemStatus();
loadMarketplaces();
loadDashboardStats();
initializeCharts();

// Auto-refresh system status every 30 seconds
setInterval(loadSystemStatus, 30000);
});

// Load system status
function loadSystemStatus() {
\$.ajax({
url: API_SYSTEM_STATUS,
method: 'GET',
dataType: 'json',
success: function (response) {
updateSystemStatus(response);
},
error: function () {
\$('#system-status-container').html('<div class=\"alert alert-danger\"> ";
        // line 391
        yield ($context["error_loading_status"] ?? null);
        yield "</div>');
}
});
}

// Update system status display
function updateSystemStatus(status) {
let html = '<div class=\"row\">';

// System health
html += '<div class=\"col-md-3\"><strong> ";
        // line 401
        yield ($context["text_system"] ?? null);
        yield ":</strong> ';
html += '<span class=\"badge bg-' + (
status.system === 'operational' ? 'success' : 'danger'
) + '\">' + status.system + '</span></div>';

// Health checks
if (status.health) {
Object.keys(status.health).forEach(function (check) {
html += '<div class=\"col-md-3\"><strong>' + check + ':</strong> ';
html += '<span class=\"badge bg-' + (
status.health[check] === 'healthy' ? 'success' : 'danger'
) + '\">' + status.health[check] + '</span></div>';
});
}

html += '</div>';
\$('#system-status-container').html(html);
}

// Load marketplaces
function loadMarketplaces() { // This would typically load from API, but for now we'll use static data
const marketplaces = [
{
id: 'trendyol',
name: 'Trendyol',
status: 'active',
color: 'primary'
},
{
id: 'n11',
name: 'N11',
status: 'active',
color: 'info'
},
{
id: 'hepsiburada',
name: 'Hepsiburada',
status: 'active',
color: 'warning'
},
{
id: 'amazon',
name: 'Amazon',
status: 'inactive',
color: 'secondary'
}, {
id: 'ebay',
name: 'eBay',
status: 'inactive',
color: 'secondary'
}
];

let html = '';
marketplaces.forEach(function (mp) {
html += '<div class=\"col-md-4 mb-3\">';
html += '<div class=\"card border-' + mp.color + '\">';
html += '<div class=\"card-header bg-' + mp.color + ' text-white\">';
html += '<h5><i class=\"fa-solid fa-store\"></i> ' + mp.name + '</h5>';
html += '</div>';
html += '<div class=\"card-body\">';
html += '<p><strong> ";
        // line 462
        yield ($context["text_status"] ?? null);
        yield ":</strong> ';
html += '<span class=\"badge bg-' + (
mp.status === 'active' ? 'success' : 'secondary'
) + '\">' + mp.status + '</span></p>';
html += '<button type=\"button\" class=\"btn btn-sm btn-primary\" onclick=\"syncMarketplace(\\'' + mp.id + '\\')\">';
html += '<i class=\"fa-solid fa-sync\"></i>  ";
        // line 467
        yield ($context["button_sync"] ?? null);
        yield "</button> ';
html += '<button type=\"button\" class=\"btn btn-sm btn-secondary\" onclick=\"configureMarketplace(\\'' + mp.id + '\\')\">';
html += '<i class=\"fa-solid fa-cog\"></i>  ";
        // line 469
        yield ($context["button_configure"] ?? null);
        yield "</button>';
html += '</div></div></div>';
});

\$('#marketplace-cards').html(html);
}

// Search products
function searchProducts() {
const query = \$('#product-search').val();

\$.ajax({
url: API_SEARCH_PRODUCTS + '&q=' + encodeURIComponent(query),
method: 'GET',
dataType: 'json',
success: function (response) {
if (response.success) {
displayProducts(response.products);
}
}
});
}

// Display products
function displayProducts(products) {
let html = '';

products.forEach(function (product) {
html += '<tr>';
html += '<td><img src=\"' + (
product.image || 'placeholder.png'
) + '\" width=\"50\" class=\"img-thumbnail\"></td>';
html += '<td>' + product.name + '</td>';
html += '<td>' + product.model + '</td>';
html += '<td>' + product.price + '</td>';
html += '<td>' + product.quantity + '</td>';
html += '<td><span class=\"badge bg-secondary\"> ";
        // line 505
        yield ($context["text_not_synced"] ?? null);
        yield "</span></td>';
html += '<td class=\"text-end\">';
html += '<button type=\"button\" class=\"btn btn-sm btn-primary\" onclick=\"syncProduct(' + product.product_id + ')\">';
html += '<i class=\"fa-solid fa-sync\"></i></button>';
html += '</td></tr>';
});

\$('#products-table tbody').html(html);
}

// Sync marketplace
function syncMarketplace(marketplace) {
if (!confirm('";
        // line 517
        yield ($context["text_confirm_sync"] ?? null);
        yield "')) 
return;


\$.ajax({
url: API_SYNC_MARKETPLACE + '&marketplace=' + marketplace,
method: 'POST',
dataType: 'json',
beforeSend: function () { // Show loading
},
success: function (response) {
if (response.success) {
alert('";
        // line 529
        yield ($context["text_sync_success"] ?? null);
        yield "');
loadDashboardStats();
} else {
alert('";
        // line 532
        yield ($context["text_sync_error"] ?? null);
        yield "' + (
response.error || ''
));
}
}
});
}

// Load dashboard stats
function loadDashboardStats() { // This would typically load from API
\$('#total-products').text('1,234');
\$('#synced-products').text('987');
\$('#active-marketplaces').text('3');
\$('#sync-rate').text('80%');
}

// Initialize charts (placeholder)
function initializeCharts() { // Would initialize Chart.js charts here
}

// Utility function to copy to clipboard
function copyToClipboard(text) {
navigator.clipboard.writeText(text).then(function () {
alert('";
        // line 555
        yield ($context["text_copied"] ?? null);
        yield "');
});
}
</script>

";
        // line 560
        yield ($context["footer"] ?? null);
        yield "
";
        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "admin/view/template/extension/module/meschain_sync.twig";
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
        return array (  846 => 560,  838 => 555,  812 => 532,  806 => 529,  791 => 517,  776 => 505,  737 => 469,  732 => 467,  724 => 462,  660 => 401,  647 => 391,  621 => 368,  617 => 367,  613 => 366,  590 => 346,  586 => 345,  582 => 344,  578 => 343,  574 => 342,  562 => 333,  555 => 329,  541 => 318,  528 => 308,  510 => 293,  492 => 278,  477 => 266,  456 => 248,  452 => 247,  448 => 246,  444 => 245,  440 => 244,  436 => 243,  432 => 242,  422 => 235,  405 => 221,  401 => 220,  397 => 219,  393 => 218,  389 => 217,  385 => 216,  381 => 215,  369 => 206,  359 => 199,  353 => 196,  327 => 173,  319 => 168,  313 => 165,  308 => 163,  304 => 162,  298 => 159,  285 => 153,  277 => 152,  271 => 149,  256 => 137,  247 => 131,  238 => 125,  229 => 119,  220 => 113,  211 => 107,  201 => 100,  194 => 96,  180 => 85,  166 => 74,  152 => 63,  138 => 52,  126 => 42,  119 => 38,  115 => 36,  113 => 35,  110 => 34,  103 => 30,  99 => 28,  97 => 27,  90 => 22,  79 => 19,  76 => 18,  72 => 17,  67 => 15,  58 => 11,  52 => 8,  42 => 2,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "admin/view/template/extension/module/meschain_sync.twig", "/Users/mezbjen/Desktop/meschain-sync-enterprise-1/RESTRUCTURED_UPLOAD/opencart4_clean/admin/view/template/extension/module/meschain_sync.twig");
    }
}
