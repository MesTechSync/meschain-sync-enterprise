<?php
namespace Opencart\Admin\Controller\User;

class UserGroup extends \Opencart\System\Engine\Controller {
    private $error = array();

    public function index(): void {
        $this->load->language('user/user_group');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('user/user_group');

        $this->getList();
    }

    public function add(): void {
        $this->load->language('user/user_group');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('user/user_group');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_user_user_group->addUserGroup($this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('user/user_group', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm();
    }

    public function edit(): void {
        $this->load->language('user/user_group');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('user/user_group');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_user_user_group->editUserGroup($this->request->get['user_group_id'], $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('user/user_group', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm();
    }

    public function delete(): void {
        $this->load->language('user/user_group');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('user/user_group');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ($this->request->post['selected'] as $user_group_id) {
                $this->model_user_user_group->deleteUserGroup($user_group_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('user/user_group', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getList();
    }

    protected function getList(): void {
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'name';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('user/user_group', 'user_token=' . $this->session->data['user_token'] . $url, true)
        );

        $data['add'] = $this->url->link('user/user_group/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = $this->url->link('user/user_group/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

        $data['user_groups'] = array();

        $filter_data = array(
            'sort'  => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $user_group_total = $this->model_user_user_group->getTotalUserGroups();

        $results = $this->model_user_user_group->getUserGroups($filter_data);

        foreach ($results as $result) {
            $data['user_groups'][] = array(
                'user_group_id' => $result['user_group_id'],
                'name'          => $result['name'],
                'edit'          => $this->url->link('user/user_group/edit', 'user_token=' . $this->session->data['user_token'] . '&user_group_id=' . $result['user_group_id'] . $url, true)
            );
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->request->post['selected'])) {
            $data['selected'] = (array)$this->request->post['selected'];
        } else {
            $data['selected'] = array();
        }

        $url = '';

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_name'] = $this->url->link('user/user_group', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $data['pagination'] = '';
        $data['results'] = sprintf($this->language->get('text_pagination'), ($user_group_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($user_group_total - $this->config->get('config_limit_admin'))) ? $user_group_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $user_group_total, ceil($user_group_total / $this->config->get('config_limit_admin')));

        $data['sort'] = $sort;
        $data['order'] = $order;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('user/user_group_list', $data));
    }

    protected function getForm(): void {
        $data['text_form'] = !isset($this->request->get['user_group_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = '';
        }

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('user/user_group', 'user_token=' . $this->session->data['user_token'] . $url, true)
        );

        if (!isset($this->request->get['user_group_id'])) {
            $data['action'] = $this->url->link('user/user_group/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        } else {
            $data['action'] = $this->url->link('user/user_group/edit', 'user_token=' . $this->session->data['user_token'] . '&user_group_id=' . $this->request->get['user_group_id'] . $url, true);
        }

        $data['cancel'] = $this->url->link('user/user_group', 'user_token=' . $this->session->data['user_token'] . $url, true);

        if (isset($this->request->get['user_group_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $user_group_info = $this->model_user_user_group->getUserGroup($this->request->get['user_group_id']);
        }

        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } elseif (!empty($user_group_info)) {
            $data['name'] = $user_group_info['name'];
        } else {
            $data['name'] = '';
        }

        $ignore = array(
            'common/dashboard',
            'common/startup',
            'common/login',
            'common/logout',
            'common/forgotten',
            'common/reset',
            'common/footer',
            'common/header',
            'common/column_left',
            'error/not_found',
            'error/permission'
        );

        $data['permissions'] = array();

        $files = array();

        // Controllers
        $path = DIR_APPLICATION . 'controller/';

        $directories = array($path);

        while (count($directories) != 0) {
            $next = array_shift($directories);

            foreach (glob(trim($next, '/') . '/{*,.[!.]*,..?*}', GLOB_BRACE) as $file) {
                if (is_dir($file)) {
                    $directories[] = $file . '/';
                }

                if (is_file($file)) {
                    $files[] = $file;
                }
            }
        }

        foreach ($files as $file) {
            $controller = substr($file, strlen($path));

            if (substr($controller, strrpos($controller, '.'), 4) == '.php') {
                $permission = substr($controller, 0, strrpos($controller, '.'));

                if (!in_array($permission, $ignore)) {
                    $data['permissions'][] = $permission;
                }
            }
        }

        if (isset($this->request->post['permission']['access'])) {
            $data['access'] = $this->request->post['permission']['access'];
        } elseif (isset($user_group_info['permission']['access'])) {
            $data['access'] = $user_group_info['permission']['access'];
        } else {
            $data['access'] = array();
        }

        if (isset($this->request->post['permission']['modify'])) {
            $data['modify'] = $this->request->post['permission']['modify'];
        } elseif (isset($user_group_info['permission']['modify'])) {
            $data['modify'] = $user_group_info['permission']['modify'];
        } else {
            $data['modify'] = array();
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('user/user_group_form', $data));
    }

    protected function validateForm(): bool {
        if (!$this->user->hasPermission('modify', 'user/user_group')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((strlen($this->request->post['name']) < 3) || (strlen($this->request->post['name']) > 64)) {
            $this->error['name'] = $this->language->get('error_name');
        }

        return !$this->error;
    }

    protected function validateDelete(): bool {
        if (!$this->user->hasPermission('modify', 'user/user_group')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        $this->load->model('user/user');

        foreach ($this->request->post['selected'] as $user_group_id) {
            if ($this->user->getGroupId() == $user_group_id) {
                $this->error['warning'] = $this->language->get('error_account');
            }

            $user_total = $this->model_user_user->getTotalUsersByGroupId($user_group_id);

            if ($user_total) {
                $this->error['warning'] = sprintf($this->language->get('error_user'), $user_total);
            }
        }

        return !$this->error;
    }
}
