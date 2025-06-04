<?php

class ControllerCommonHeader extends Controller {
    public function index() {
        $data['title'] = $this->document->getTitle();

        if ($this->request->server['HTTPS']) {
            $data['base'] = HTTPS_SERVER;
        } else {
            $data['base'] = HTTP_SERVER;
        }

        $data['description'] = $this->document->getDescription();
        $data['keywords'] = $this->document->getKeywords();
        $data['links'] = $this->document->getLinks();
        $data['styles'] = $this->document->getStyles();
        $data['scripts'] = $this->document->getScripts();
        $data['lang'] = $this->language->get('code');
        $data['direction'] = $this->language->get('direction');

        $this->load->language('common/header');

        $data['text_logged'] = sprintf($this->language->get('text_logged'), $this->user->getUserName());

        if (!isset($this->request->get['user_token']) || !isset($this->session->data['user_token']) || ($this->request->get['user_token'] != $this->session->data['user_token'])) {
            $data['logged'] = '';

            $data['home'] = $this->url->link('common/login', '', true);
        } else {
            $data['logged'] = true;

            $data['home'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true);
            $data['logout'] = $this->url->link('common/logout', 'user_token=' . $this->session->data['user_token'], true);
            $data['profile'] = $this->url->link('common/profile', 'user_token=' . $this->session->data['user_token'], true);
            
            $this->load->model('user/user');
    
            $this->load->model('tool/image');
    
            $user_info = $this->model_user_user->getUser($this->user->getId());
    
            if ($user_info) {
                $data['firstname'] = $user_info['firstname'];
                $data['lastname'] = $user_info['lastname'];
                $data['username']  = $user_info['username'];
                $data['user_group'] = $user_info['user_group'];
    
                if (is_file(DIR_IMAGE . $user_info['image'])) {
                    $data['image'] = $this->model_tool_image->resize($user_info['image'], 45, 45);
                } else {
                    $data['image'] = $this->model_tool_image->resize('profile.png', 45, 45);
                }
            } else {
                $data['firstname'] = '';
                $data['lastname'] = '';
                $data['user_group'] = '';
                $data['image'] = '';
            }            
            
            // Online Stores
            $data['stores'] = array();
            
            $data['stores'][] = array(
                'name' => $this->config->get('config_name'),
                'href' => HTTP_CATALOG
            );
                
            $this->load->model('setting/store');
    
            $results = $this->model_setting_store->getStores();
            
            foreach ($results as $result) {
                $data['stores'][] = array(
                    'name' => $result['name'],
                    'href' => $result['url']
                );
            }
        }
        
        // MesChain Sync - Duyuru Popup
        if ($this->config->get('module_announcement_status')) {
            try {
                // Model dosyası var mı kontrol et
                $model_file = DIR_APPLICATION . 'model/extension/module/announcement.php';
                if (file_exists($model_file)) {
                    $this->load->model('extension/module/announcement');
                    
                    // Model nesnesini güvenli şekilde al
                    $model_name = 'model_extension_module_announcement';
                    if (isset($this->{$model_name}) && method_exists($this->{$model_name}, 'getActiveAnnouncementsForUser')) {
                        // Kullanıcı için aktif duyuruları getir
                        $announcement = $this->{$model_name}->getActiveAnnouncementsForUser($this->user->getId(), $this->user->getGroupId());
                        
                        if ($announcement) {
                            // Duyuru şablonları
                            $templates = array(
                                'klasik' => array('bg' => '#fffbe7', 'icon' => '', 'border' => '#b89b82'),
                                'uyari' => array('bg' => '#fff0f0', 'icon' => '&#9888;', 'border' => '#d33'),
                                'basari' => array('bg' => '#eafbe7', 'icon' => '&#10004;', 'border' => '#3ad833'),
                                'bilgi' => array('bg' => '#eaf6fb', 'icon' => '&#9432;', 'border' => '#3a8fd8'),
                                'ozel' => array('bg' => 'linear-gradient(90deg,#f5eee6,#eaf6fb)', 'icon' => '&#128161;', 'border' => '#b89b82')
                            );
                            
                            $data['announcement'] = $announcement;
                            $data['templates'] = $templates;
                            $data['user_token'] = $this->session->data['user_token'];
                            $data['attachment_url'] = $this->config->get('config_url') . 'image/catalog/announcements/';
                            
                            // Template dosyası var mı kontrol et
                            $template_file = DIR_TEMPLATE . 'extension/module/announcement_popup.twig';
                            if (file_exists($template_file)) {
                                $data['announcement_popup'] = $this->load->view('extension/module/announcement_popup', $data);
                            } else {
                                $data['announcement_popup'] = '';
                            }
                        } else {
                            $data['announcement_popup'] = '';
                        }
                    } else {
                        $data['announcement_popup'] = '';
                    }
                } else {
                    $data['announcement_popup'] = '';
                }
            } catch (Exception $e) {
                // Hata durumunda boş popup
                $data['announcement_popup'] = '';
            }
        } else {
            $data['announcement_popup'] = '';
        }
        
        // MesChain Sync - Kullanıcı Tema
        if ($this->config->get('module_user_settings_status')) {
            $this->load->model('extension/module/user_settings');
            $theme = $this->model_extension_module_user_settings->getUserTheme($this->user->getId());
            
            if ($theme && $theme != 'default') {
                $data['styles'][] = array(
                    'href' => 'view/template/extension/module/meschain_theme.css',
                    'rel' => 'stylesheet'
                );
            }
        }

        return $this->load->view('common/header', $data);
    }
} 