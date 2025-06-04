<?php
/**
 * help.php
 *
 * Amaç: MesChain Sync modülü için yardım ve dokümantasyon controller dosyası.
 *
 * Loglama: Yardım sayfası görüntülendiğinde help.log dosyasına kaydedilir.
 * Log formatı: [YYYY-MM-DD HH:MM:SS] [KULLANICI/ROL] [İŞLEM] [AÇIKLAMA]
 */

class ControllerExtensionModuleHelp extends Controller {
    /**
     * Ana sayfa
     */
    public function index() {
        $this->load->language('extension/module/help');
        $this->document->setTitle($this->language->get('heading_title'));
        
        // CSS dosyasını yükle
        $this->document->addStyle('view/template/extension/module/meschain_theme.css');
        
        // Dil değişkenlerini yükle
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_help'] = $this->language->get('text_help');
        $data['text_general'] = $this->language->get('text_general');
        $data['text_marketplace'] = $this->language->get('text_marketplace');
        $data['text_announcement'] = $this->language->get('text_announcement');
        $data['text_user'] = $this->language->get('text_user');
        $data['text_theme'] = $this->language->get('text_theme');
        $data['text_logs'] = $this->language->get('text_logs');
        $data['text_faq'] = $this->language->get('text_faq');
        
        $data['text_about'] = $this->language->get('text_about');
        $data['text_version'] = $this->language->get('text_version');
        $data['text_description'] = $this->language->get('text_description');
        $data['text_support'] = $this->language->get('text_support');
        $data['text_getting_started'] = $this->language->get('text_getting_started');
        $data['text_step_1'] = $this->language->get('text_step_1');
        $data['text_step_2'] = $this->language->get('text_step_2');
        $data['text_step_3'] = $this->language->get('text_step_3');
        $data['text_step_4'] = $this->language->get('text_step_4');
        
        $data['text_marketplace_integration'] = $this->language->get('text_marketplace_integration');
        $data['text_marketplace_desc'] = $this->language->get('text_marketplace_desc');
        $data['text_supported_marketplaces'] = $this->language->get('text_supported_marketplaces');
        $data['text_trendyol_desc'] = $this->language->get('text_trendyol_desc');
        $data['text_n11_desc'] = $this->language->get('text_n11_desc');
        $data['text_amazon_desc'] = $this->language->get('text_amazon_desc');
        $data['text_ebay_desc'] = $this->language->get('text_ebay_desc');
        $data['text_hepsiburada_desc'] = $this->language->get('text_hepsiburada_desc');
        $data['text_ozon_desc'] = $this->language->get('text_ozon_desc');
        $data['text_api_setup'] = $this->language->get('text_api_setup');
        $data['text_api_setup_desc'] = $this->language->get('text_api_setup_desc');
        $data['text_api_security'] = $this->language->get('text_api_security');
        $data['text_api_security_desc'] = $this->language->get('text_api_security_desc');
        
        $data['text_announcement_system'] = $this->language->get('text_announcement_system');
        $data['text_announcement_desc'] = $this->language->get('text_announcement_desc');
        $data['text_creating_announcements'] = $this->language->get('text_creating_announcements');
        $data['text_announcement_step_1'] = $this->language->get('text_announcement_step_1');
        $data['text_announcement_step_2'] = $this->language->get('text_announcement_step_2');
        $data['text_announcement_step_3'] = $this->language->get('text_announcement_step_3');
        $data['text_announcement_step_4'] = $this->language->get('text_announcement_step_4');
        $data['text_announcement_templates'] = $this->language->get('text_announcement_templates');
        $data['text_template_classic'] = $this->language->get('text_template_classic');
        $data['text_template_classic_desc'] = $this->language->get('text_template_classic_desc');
        $data['text_template_warning'] = $this->language->get('text_template_warning');
        $data['text_template_warning_desc'] = $this->language->get('text_template_warning_desc');
        $data['text_template_success'] = $this->language->get('text_template_success');
        $data['text_template_success_desc'] = $this->language->get('text_template_success_desc');
        $data['text_template_info'] = $this->language->get('text_template_info');
        $data['text_template_info_desc'] = $this->language->get('text_template_info_desc');
        $data['text_template_special'] = $this->language->get('text_template_special');
        $data['text_template_special_desc'] = $this->language->get('text_template_special_desc');
        
        $data['text_user_settings'] = $this->language->get('text_user_settings');
        $data['text_user_settings_desc'] = $this->language->get('text_user_settings_desc');
        $data['text_profile_management'] = $this->language->get('text_profile_management');
        $data['text_profile_management_desc'] = $this->language->get('text_profile_management_desc');
        $data['text_permissions'] = $this->language->get('text_permissions');
        $data['text_permissions_desc'] = $this->language->get('text_permissions_desc');
        
        $data['text_theme_system'] = $this->language->get('text_theme_system');
        $data['text_theme_desc'] = $this->language->get('text_theme_desc');
        $data['text_selecting_theme'] = $this->language->get('text_selecting_theme');
        $data['text_theme_step_1'] = $this->language->get('text_theme_step_1');
        $data['text_theme_step_2'] = $this->language->get('text_theme_step_2');
        $data['text_theme_step_3'] = $this->language->get('text_theme_step_3');
        $data['text_customizing_theme'] = $this->language->get('text_customizing_theme');
        $data['text_customizing_theme_desc'] = $this->language->get('text_customizing_theme_desc');
        
        $data['text_log_system'] = $this->language->get('text_log_system');
        $data['text_log_desc'] = $this->language->get('text_log_desc');
        $data['text_log_locations'] = $this->language->get('text_log_locations');
        $data['text_main_log_desc'] = $this->language->get('text_main_log_desc');
        $data['text_announcement_log_desc'] = $this->language->get('text_announcement_log_desc');
        $data['text_user_settings_log_desc'] = $this->language->get('text_user_settings_log_desc');
        $data['text_trendyol_log_desc'] = $this->language->get('text_trendyol_log_desc');
        $data['text_error_log_desc'] = $this->language->get('text_error_log_desc');
        $data['text_log_format'] = $this->language->get('text_log_format');
        $data['text_common_errors'] = $this->language->get('text_common_errors');
        $data['text_error_code'] = $this->language->get('text_error_code');
        $data['text_solution'] = $this->language->get('text_solution');
        $data['text_error_e001'] = $this->language->get('text_error_e001');
        $data['text_solution_e001'] = $this->language->get('text_solution_e001');
        $data['text_error_a001'] = $this->language->get('text_error_a001');
        $data['text_solution_a001'] = $this->language->get('text_solution_a001');
        $data['text_error_u001'] = $this->language->get('text_error_u001');
        $data['text_solution_u001'] = $this->language->get('text_solution_u001');
        
        $data['text_faq_1'] = $this->language->get('text_faq_1');
        $data['text_faq_answer_1'] = $this->language->get('text_faq_answer_1');
        $data['text_faq_2'] = $this->language->get('text_faq_2');
        $data['text_faq_answer_2'] = $this->language->get('text_faq_answer_2');
        $data['text_faq_3'] = $this->language->get('text_faq_3');
        $data['text_faq_answer_3'] = $this->language->get('text_faq_answer_3');
        $data['text_faq_4'] = $this->language->get('text_faq_4');
        $data['text_faq_answer_4'] = $this->language->get('text_faq_answer_4');
        $data['text_faq_5'] = $this->language->get('text_faq_5');
        $data['text_faq_answer_5'] = $this->language->get('text_faq_answer_5');
        
        $data['text_support_desc'] = $this->language->get('text_support_desc');
        $data['text_email'] = $this->language->get('text_email');
        $data['text_website'] = $this->language->get('text_website');
        
        $data['button_cancel'] = $this->language->get('button_cancel');
        
        // Breadcrumb
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/help', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        // Linkler
        $data['cancel'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true);
        
        // Versiyon bilgisi
        $version_file = DIR_SYSTEM . '../VERSION';
        if (file_exists($version_file)) {
            $data['version'] = file_get_contents($version_file);
        } else {
            $data['version'] = '1.0.0';
        }
        
        // Şablonu yükle
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        // Log
        $this->writeLog($this->user->getUserName(), 'VIEW', 'Yardım sayfası görüntülendi');
        
        $this->response->setOutput($this->load->view('extension/module/help', $data));
    }
    
    /**
     * Kurulum
     */
    public function install() {
        // Yetki ekle
        $this->load->model('user/user_group');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/help');
        
        $this->writeLog('SYSTEM', 'INSTALL', 'Yardım modülü kuruldu');
    }
    
    /**
     * Kaldırma
     */
    public function uninstall() {
        $this->writeLog('SYSTEM', 'UNINSTALL', 'Yardım modülü kaldırıldı');
    }
    
    /**
     * Log kaydı
     */
    private function writeLog($user, $action, $message) {
        $log_file = DIR_LOGS . 'help.log';
        $date = date('Y-m-d H:i:s');
        $log = "[$date] [$user] [$action] $message\n";
        file_put_contents($log_file, $log, FILE_APPEND);
    }
} 