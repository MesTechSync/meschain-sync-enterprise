<?php
namespace Opencart\Admin\Controller\Extension;
class MeschainSync extends \Opencart\System\Engine\Controller {
    public function index(): void {
        $this->load->language("extension/meschain_sync");
        
        $this->document->setTitle($this->language->get("heading_title"));
        
        $data["breadcrumbs"] = [];
        
        $data["breadcrumbs"][] = [
            "text" => $this->language->get("text_home"),
            "href" => $this->url->link("common/dashboard", "user_token=" . $this->session->data["user_token"])
        ];
        
        $data["breadcrumbs"][] = [
            "text" => $this->language->get("text_extension"),
            "href" => $this->url->link("marketplace/extension", "user_token=" . $this->session->data["user_token"])
        ];
        
        $data["breadcrumbs"][] = [
            "text" => $this->language->get("heading_title"),
            "href" => $this->url->link("extension/meschain_sync", "user_token=" . $this->session->data["user_token"])
        ];
        
        // Direct link to MesChain-Sync Enterprise
        $data["meschain_sync_edit"] = $this->url->link("extension/meschain/module/meschain_sync", "user_token=" . $this->session->data["user_token"]);
        $data["meschain_sync_status"] = $this->config->get("module_meschain_sync_status") ? $this->language->get("text_enabled") : $this->language->get("text_disabled");
        
        // Add language variables for template
        $data["text_enabled"] = $this->language->get("text_enabled");
        $data["text_disabled"] = $this->language->get("text_disabled");
        $data["text_list"] = $this->language->get("text_list");
        $data["column_name"] = $this->language->get("column_name");
        $data["column_status"] = $this->language->get("column_status");
        $data["column_action"] = $this->language->get("column_action");
        
        $data["user_token"] = $this->session->data["user_token"];
        
        $data["header"] = $this->load->controller("common/header");
        $data["column_left"] = $this->load->controller("common/column_left");
        $data["footer"] = $this->load->controller("common/footer");
        
        $this->response->setOutput($this->load->view("extension/meschain_sync", $data));
    }
}
?>