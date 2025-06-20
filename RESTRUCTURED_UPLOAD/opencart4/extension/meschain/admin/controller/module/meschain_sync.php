<?php
namespace Opencart\Admin\Controller\Extension\Meschain\Module;

class MeschainSync extends \Opencart\System\Engine\Controller {
    private array $error = [];

    public function index(): void {
        $this->load->language("extension/meschain/module/meschain_sync");
        $this->document->setTitle($this->language->get("heading_title"));

        // Check permissions
        if (!$this->user->hasPermission('access', 'extension/meschain/module/meschain_sync')) {
            $this->session->data['error'] = 'You do not have permission to access this page!';
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module'));
        }

        $data["breadcrumbs"] = [];
        $data["breadcrumbs"][] = [
            "text" => $this->language->get("text_home"),
            "href" => $this->url->link("common/dashboard", "user_token=" . $this->session->data["user_token"])
        ];
        $data["breadcrumbs"][] = [
            "text" => $this->language->get("text_extension"),
            "href" => $this->url->link("marketplace/extension", "user_token=" . $this->session->data["user_token"] . "&type=module")
        ];
        $data["breadcrumbs"][] = [
            "text" => $this->language->get("heading_title"),
            "href" => $this->url->link("extension/meschain/module/meschain_sync", "user_token=" . $this->session->data["user_token"])
        ];

        // Add navigation links for the buttons
        $data["manage_marketplaces"] = $this->url->link("extension/meschain/module/meschain_sync.marketplaces", "user_token=" . $this->session->data["user_token"]);
        $data["analytics"] = $this->url->link("extension/meschain/module/meschain_sync.analytics", "user_token=" . $this->session->data["user_token"]);
        $data["settings"] = $this->url->link("extension/meschain/module/meschain_sync.settings", "user_token=" . $this->session->data["user_token"]);

        $data["save"] = $this->url->link("extension/meschain/module/meschain_sync.save", "user_token=" . $this->session->data["user_token"]);
        $data["back"] = $this->url->link("marketplace/extension", "user_token=" . $this->session->data["user_token"] . "&type=module");

        $data["module_meschain_sync_status"] = $this->config->get("module_meschain_sync_status");
        
        // Get marketplace statistics
        $this->load->model("extension/meschain/module/meschain_sync");
        $data["marketplace_count"] = $this->model_extension_meschain_module_meschain_sync->getTotalMarketplaces();
        $data["product_count"] = $this->model_extension_meschain_module_meschain_sync->getTotalProducts();
        $data["order_count"] = $this->model_extension_meschain_module_meschain_sync->getTotalOrders();

        $data["header"] = $this->load->controller("common/header");
        $data["column_left"] = $this->load->controller("common/column_left");
        $data["footer"] = $this->load->controller("common/footer");

        $this->response->setOutput($this->load->view("extension/meschain/module/meschain_sync", $data));
    }

    public function marketplaces(): void {
        $this->load->language("extension/meschain/module/meschain_sync");
        $this->document->setTitle("Manage Marketplaces - " . $this->language->get("heading_title"));

        // Check permissions
        if (!$this->user->hasPermission('access', 'extension/meschain/module/meschain_sync')) {
            $this->session->data['error'] = 'You do not have permission to access this page!';
            $this->response->redirect($this->url->link('extension/meschain/module/meschain_sync', 'user_token=' . $this->session->data['user_token']));
        }

        $data["breadcrumbs"] = [];
        $data["breadcrumbs"][] = [
            "text" => $this->language->get("text_home"),
            "href" => $this->url->link("common/dashboard", "user_token=" . $this->session->data["user_token"])
        ];
        $data["breadcrumbs"][] = [
            "text" => $this->language->get("heading_title"),
            "href" => $this->url->link("extension/meschain/module/meschain_sync", "user_token=" . $this->session->data["user_token"])
        ];
        $data["breadcrumbs"][] = [
            "text" => "Manage Marketplaces",
            "href" => $this->url->link("extension/meschain/module/meschain_sync.marketplaces", "user_token=" . $this->session->data["user_token"])
        ];

        $data["back"] = $this->url->link("extension/meschain/module/meschain_sync", "user_token=" . $this->session->data["user_token"]);

        // Load marketplaces data
        $this->load->model("extension/meschain/module/meschain_sync");
        $data["marketplaces"] = $this->model_extension_meschain_module_meschain_sync->getMarketplaces();

        $data["header"] = $this->load->controller("common/header");
        $data["column_left"] = $this->load->controller("common/column_left");
        $data["footer"] = $this->load->controller("common/footer");

        $this->response->setOutput($this->load->view("extension/meschain/module/meschain_sync_marketplaces", $data));
    }

    public function analytics(): void {
        $this->load->language("extension/meschain/module/meschain_sync");
        $this->document->setTitle("Analytics - " . $this->language->get("heading_title"));

        // Check permissions
        if (!$this->user->hasPermission('access', 'extension/meschain/module/meschain_sync')) {
            $this->session->data['error'] = 'You do not have permission to access this page!';
            $this->response->redirect($this->url->link('extension/meschain/module/meschain_sync', 'user_token=' . $this->session->data['user_token']));
        }

        $data["breadcrumbs"] = [];
        $data["breadcrumbs"][] = [
            "text" => $this->language->get("text_home"),
            "href" => $this->url->link("common/dashboard", "user_token=" . $this->session->data["user_token"])
        ];
        $data["breadcrumbs"][] = [
            "text" => $this->language->get("heading_title"),
            "href" => $this->url->link("extension/meschain/module/meschain_sync", "user_token=" . $this->session->data["user_token"])
        ];
        $data["breadcrumbs"][] = [
            "text" => "Analytics",
            "href" => $this->url->link("extension/meschain/module/meschain_sync.analytics", "user_token=" . $this->session->data["user_token"])
        ];

        $data["back"] = $this->url->link("extension/meschain/module/meschain_sync", "user_token=" . $this->session->data["user_token"]);

        // Load analytics data
        $this->load->model("extension/meschain/module/meschain_sync");
        $data["analytics"] = $this->model_extension_meschain_module_meschain_sync->getAnalytics();

        $data["header"] = $this->load->controller("common/header");
        $data["column_left"] = $this->load->controller("common/column_left");
        $data["footer"] = $this->load->controller("common/footer");

        $this->response->setOutput($this->load->view("extension/meschain/module/meschain_sync_analytics", $data));
    }

    public function settings(): void {
        $this->load->language("extension/meschain/module/meschain_sync");
        $this->document->setTitle("Settings - " . $this->language->get("heading_title"));

        // Check permissions
        if (!$this->user->hasPermission('modify', 'extension/meschain/module/meschain_sync')) {
            $this->session->data['error'] = 'You do not have permission to modify settings!';
            $this->response->redirect($this->url->link('extension/meschain/module/meschain_sync', 'user_token=' . $this->session->data['user_token']));
        }

        $data["breadcrumbs"] = [];
        $data["breadcrumbs"][] = [
            "text" => $this->language->get("text_home"),
            "href" => $this->url->link("common/dashboard", "user_token=" . $this->session->data["user_token"])
        ];
        $data["breadcrumbs"][] = [
            "text" => $this->language->get("heading_title"),
            "href" => $this->url->link("extension/meschain/module/meschain_sync", "user_token=" . $this->session->data["user_token"])
        ];
        $data["breadcrumbs"][] = [
            "text" => "Settings",
            "href" => $this->url->link("extension/meschain/module/meschain_sync.settings", "user_token=" . $this->session->data["user_token"])
        ];

        $data["save_settings"] = $this->url->link("extension/meschain/module/meschain_sync.saveSettings", "user_token=" . $this->session->data["user_token"]);
        $data["back"] = $this->url->link("extension/meschain/module/meschain_sync", "user_token=" . $this->session->data["user_token"]);

        // Load current settings
        $data["settings"] = [
            "sync_interval" => $this->config->get("meschain_sync_interval") ?: 60,
            "auto_sync" => $this->config->get("meschain_auto_sync") ?: 0,
            "debug_mode" => $this->config->get("meschain_debug_mode") ?: 0,
            "api_timeout" => $this->config->get("meschain_api_timeout") ?: 30,
            "log_level" => $this->config->get("meschain_log_level") ?: "info"
        ];

        $data["header"] = $this->load->controller("common/header");
        $data["column_left"] = $this->load->controller("common/column_left");
        $data["footer"] = $this->load->controller("common/footer");

        $this->response->setOutput($this->load->view("extension/meschain/module/meschain_sync_settings", $data));
    }

    public function saveSettings(): void {
        $this->load->language("extension/meschain/module/meschain_sync");

        $json = [];

        if (!$this->user->hasPermission("modify", "extension/meschain/module/meschain_sync")) {
            $json["error"] = $this->language->get("error_permission");
        }

        if (!$json) {
            $this->load->model("setting/setting");
            
            $settings = [
                "meschain_sync_interval" => (int)$this->request->post["sync_interval"],
                "meschain_auto_sync" => isset($this->request->post["auto_sync"]) ? 1 : 0,
                "meschain_debug_mode" => isset($this->request->post["debug_mode"]) ? 1 : 0,
                "meschain_api_timeout" => (int)$this->request->post["api_timeout"],
                "meschain_log_level" => $this->request->post["log_level"]
            ];
            
            $this->model_setting_setting->editSetting("meschain", $settings);
            $json["success"] = "Settings saved successfully!";
        }

        $this->response->addHeader("Content-Type: application/json");
        $this->response->setOutput(json_encode($json));
    }

    public function save(): void {
        $this->load->language("extension/meschain/module/meschain_sync");

        $json = [];

        if (!$this->user->hasPermission("modify", "extension/meschain/module/meschain_sync")) {
            $json["error"] = $this->language->get("error_permission");
        }

        if (!$json) {
            $this->load->model("setting/setting");
            $this->model_setting_setting->editSetting("module_meschain_sync", $this->request->post);
            $json["success"] = $this->language->get("text_success");
        }

        $this->response->addHeader("Content-Type: application/json");
        $this->response->setOutput(json_encode($json));
    }

    public function install(): void {
        $this->load->model("extension/meschain/module/meschain_sync");
        $this->model_extension_meschain_module_meschain_sync->install();
    }

    public function uninstall(): void {
        $this->load->model("extension/meschain/module/meschain_sync");
        $this->model_extension_meschain_module_meschain_sync->uninstall();
    }
}