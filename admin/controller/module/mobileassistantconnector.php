<?php

class ControllerModuleMobileAssistantConnector extends Controller {
    const MODULE_CODE = 20;
    const MODULE_VERSION = '1.3.2';

    private $error = array();
    private $is_ver20;
    private $opencart_version;


    public function index() {
        if(isset($this->request->request['call_function'])) {
            $call_function = strval($this->request->request['call_function']);
            if (!method_exists($this, $call_function)) {
                $this->generate_output('old_module');
            }
            $result = call_user_func(array($this, $call_function));
            $this->generate_output($result);
        }


        $this->check_version();

        $this->reset_events();

        $this->load->model('setting/setting');
        $s = $this->model_setting_setting->getSetting('mobassist');
        $s['mobassist_module_code'] = self::MODULE_CODE;
        $s['mobassist_module_version'] = self::MODULE_VERSION;

        $this->load->model('mobileassistant/connector');
        $this->model_mobileassistant_connector->create_tables();

        $this->checkUpdateModule();

        $this->model_setting_setting->editSetting('mobassist', $s);

        $this->createForm();
    }


    public function install() {
        $this->check_version();

        $this->load->model('setting/setting');

        $module_settings = array(
            'mobassist_status' => '1',
            'mobassist_module_code' => self::MODULE_CODE,
            'mobassist_module_version' => self::MODULE_VERSION,
            'mobassist_cl_date' => 1
        );

        $this->model_setting_setting->editSetting('mobassist', $module_settings);

        $this->reset_events();

        $this->load->model('mobileassistant/connector');
        $this->model_mobileassistant_connector->create_tables();

        $default_user = array('username' => 1);
        $default_user['user_status'] = 1;
        $default_user['password'] = 1;
        $default_user['allowed_actions'] = array('push_new_order' => 1, 'push_order_status_changed' => 1, 'push_new_customer' => 1, 'store_statistics' => 1, 'order_list' => 1, 'order_details' => 1, 'order_status_updating' => 1, 'customer_list' => 1, 'customer_details' => 1, 'product_list' => 1, 'product_details' => 1);
        $this->saveUsers(array($default_user));
    }


    public function uninstall() {
        $this->check_version();

        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('mobassist');

        if($this->is_ver20) {
            if(version_compare($this->opencart_version, '2.0.1.0', '>=')) {
                $this->load->model('extension/event');
                $this->model_extension_event->deleteEvent('mobileassistantconnector');

            } else {
                $this->load->model('tool/event');
                $this->model_tool_event->deleteEvent('mobileassistantconnector');
            }
        }

        $this->load->model('mobileassistant/connector');
        $this->model_mobileassistant_connector->drop_tables();
    }


    private function reset_events() {
        if($this->is_ver20) {
            if(version_compare($this->opencart_version, '2.2.0.0', '>=')) {
                $this->load->model('extension/event');
                $this->model_extension_event->deleteEvent('mobileassistantconnector');

                $this->model_extension_event->addEvent('mobileassistantconnector', 'catalog/model/checkout/order/addOrder/after', 'module/mobileassistantconnector/push_new_order');
                $this->model_extension_event->addEvent('mobileassistantconnector', 'catalog/model/checkout/order/addOrderHistory/before', 'module/mobileassistantconnector/push_change_status_pre');
                $this->model_extension_event->addEvent('mobileassistantconnector', 'catalog/model/checkout/order/addOrderHistory/after', 'module/mobileassistantconnector/push_change_status');
                $this->model_extension_event->addEvent('mobileassistantconnector', 'admin/controller/sale/order/history/before', 'module/mobileassistantconnector/push_change_status_pre');
                $this->model_extension_event->addEvent('mobileassistantconnector', 'admin/controller/sale/order/history/after', 'module/mobileassistantconnector/push_change_status');
                $this->model_extension_event->addEvent('mobileassistantconnector', 'catalog/model/account/customer/addCustomer/after', 'module/mobileassistantconnector/push_new_customer');

            } else if(version_compare($this->opencart_version, '2.0.1.0', '>=')) {
                $this->load->model('extension/event');
                $this->model_extension_event->deleteEvent('mobileassistantconnector');

                $this->model_extension_event->addEvent('mobileassistantconnector', 'post.order.add', 'module/mobileassistantconnector/push_new_order');
                $this->model_extension_event->addEvent('mobileassistantconnector', 'pre.order.history.add', 'module/mobileassistantconnector/push_change_status_pre');
                $this->model_extension_event->addEvent('mobileassistantconnector', 'post.order.history.add', 'module/mobileassistantconnector/push_change_status');
                $this->model_extension_event->addEvent('mobileassistantconnector', 'post.customer.add', 'module/mobileassistantconnector/push_new_customer');

            } else {
                $this->load->model('tool/event');
                $this->model_tool_event->deleteEvent('mobileassistantconnector');

                $this->model_tool_event->addEvent('mobileassistantconnector', 'post.order.add', 'module/mobileassistantconnector/push_new_order');
                $this->model_tool_event->addEvent('mobileassistantconnector', 'pre.order.history.add', 'module/mobileassistantconnector/push_change_status_pre');
                $this->model_tool_event->addEvent('mobileassistantconnector', 'post.order.history.add', 'module/mobileassistantconnector/push_change_status');
                $this->model_tool_event->addEvent('mobileassistantconnector', 'post.customer.add', 'module/mobileassistantconnector/push_new_customer');
            }
        }
    }


    private function checkUpdateModule() {
        $this->load->model('setting/setting');

        $s = $this->model_setting_setting->getSetting('mobassist');

        if (isset($s['mobassist_module_code']) && $s['mobassist_module_code'] < self::MODULE_CODE) {
            $this->load->model('mobileassistant/connector');
            $this->model_mobileassistant_connector->update_module($s);

            if(!isset($s['mobassist_cl_date'])) $s['mobassist_cl_date'] = 1;

            $settings = array(
                'mobassist_status' => $s['mobassist_status'],
                'mobassist_module_code' => self::MODULE_CODE,
                'mobassist_module_version' => self::MODULE_VERSION,
                'mobassist_cl_date' => $s['mobassist_cl_date']
            );

            $this->model_setting_setting->editSetting('mobassist', $settings);
        }
    }


    private function createForm() {
        $this->load->language('module/mobileassistantconnector');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

//        $s = $this->model_setting_setting->getSetting('mobassist');

//        if(isset($this->request->get['disable_setting_id'])) {
//            var_dump($this->request->get['disable_setting_id']);
//            $url = $this->url->link('module/mobileassistantconnector', 'token=' . $this->session->data['token'], 'SSL');
//            if($this->is_ver20) {
//                $this->response->redirect($url);
//            } else {
//                $this->redirect($url);
//            }
//        }

        if ($this->request->server['REQUEST_METHOD'] == 'GET'  && $this->validate()) {
            $args = array('token' => $this->session->data['token']);
            if(isset($this->request->get['user_id']) && $this->request->get['user_id'] > 0) {
                $args['user_id'] = $this->request->get['user_id'];
            }

            if(version_compare($this->opencart_version, '2.2.0.0', '<')) {
                $args = http_build_query($args);
            }

            if(isset($this->request->get['enable_setting_id'])) {
                $this->actionPushDevices(1, $this->request->get['enable_setting_id']);
                $url = $this->url->link('module/mobileassistantconnector', $args, 'SSL');
                if($this->is_ver20) {
                    $this->response->redirect($url);
                } else {
                    $this->redirect($url);
                }
            }

            if(isset($this->request->get['disable_setting_id'])) {
                $this->actionPushDevices(2, $this->request->get['disable_setting_id']);
                $url = $this->url->link('module/mobileassistantconnector', $args, 'SSL');
                if($this->is_ver20) {
                    $this->response->redirect($url);
                } else {
                    $this->redirect($url);
                }
            }

            if(isset($this->request->get['delete_setting_id'])) {
                $this->actionPushDevices(3, $this->request->get['delete_setting_id']);
                $url = $this->url->link('module/mobileassistantconnector', $args, 'SSL');
                if($this->is_ver20) {
                    $this->response->redirect($url);
                } else {
                    $this->redirect($url);
                }
            }
        }

        if ($this->request->server['REQUEST_METHOD'] == 'POST'  && $this->validate()) {
/*
            if(isset($this->request->post['bulk_actions']) && $this->request->post['bulk_actions'] > 0 && isset($this->request->post['device_conn_id'])) {
                $this->actionPushDevices((int) $this->request->post['bulk_actions'], $this->request->post['device_conn_id']);
            } else if (isset($s['mobassist_pass']) && isset($this->request->post['mobassist_pass'])) {
                if ($this->request->post['mobassist_pass'] != "" && $s['mobassist_pass'] != $this->request->post['mobassist_pass']) {
                    $this->request->post['mobassist_pass'] = md5($this->request->post['mobassist_pass']);
                }

                $this->model_setting_setting->editSetting('mobassist', $this->request->post);
            }
*/

//            if(isset($this->request->post['user']) && !empty($this->request->post['user'])) {
                $users = $this->request->post['user'];
                if($this->checkUsers($users)) {
                    $this->saveUsers($users);

                    $this->session->data['success'] = $this->language->get('text_success');

                    if (isset($this->request->post['save_continue']) && $this->request->post['save_continue'] == 1) {
                        $url = $this->url->link('module/mobileassistantconnector', 'token=' . $this->session->data['token'], 'SSL');
                    } else {
                        $url = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');
                    }

                    if ($this->is_ver20) {
                        $this->response->redirect($url);
                    } else {
                        $this->redirect($url);
                    }
                }
//            }
        }

        $d = array();

        $d['token'] = $this->session->data['token'];

        $d['is_ver20'] = $this->is_ver20;

        if (isset($this->session->data['success'])) {
            $d['saving_success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $d['saving_success'] = '';
        }

//        if(!isset($s['mobassist_status'])) {
//            $s['mobassist_status'] = 1;
//        }
//        if(!isset($s['mobassist_login'])) {
//            $s['mobassist_login'] = 1;
//        }
//        if(!isset($s['mobassist_pass'])) {
//            $s['mobassist_pass'] = md5(1);
//        }

//        $d['settings'] = $s;

//        if ($s['mobassist_login'] == 1 && $s['mobassist_pass'] == md5(1)) {
//            $d['message_info'] = $this->language->get('error_default_cred');
//        } else {
//            $d['message_info'] = '';
//        }


//        $this->load->model('mobileassistant/qrcode');
//        if($qrcode_url = $this->model_mobileassistant_qrcode->get_QR_img()) {
//            $d['qrcode_url'] = $qrcode_url;
//        }

//        $d['qrcode_settings'] = $this->getQRSettings();

        $d['heading_title'] = $this->language->get('heading_title');

        $d['text_enabled'] = $this->language->get('text_enabled');
        $d['text_disabled'] = $this->language->get('text_disabled');
        $d['text_edit'] = $this->language->get('text_edit');

        $d['entry_login'] = $this->language->get('entry_login');
        $d['help_login'] = $this->language->get('help_login');

        $d['entry_pass'] = $this->language->get('entry_pass');
        $d['help_pass'] = $this->language->get('help_pass');

        $d['entry_disable_mis_ord_notif'] = $this->language->get('entry_disable_mis_ord_notif');

        $d['entry_qr'] = $this->language->get('entry_qr');
        $d['help_qr'] = $this->language->get('help_qr');

        $d['entry_status'] = $this->language->get('entry_status');

        $d['module_version'] = $this->language->get('module_version');
        $d['connector_version'] = self::MODULE_VERSION;

        $d['useful_links'] = $this->language->get('useful_links');
        $d['check_new_version'] = $this->language->get('check_new_version');
        $d['submit_ticket'] = $this->language->get('submit_ticket');
        $d['documentation'] = $this->language->get('documentation');

        $d['button_save'] = $this->language->get('button_save');
        $d['button_save_continue'] = $this->language->get('button_save_continue');
        $d['button_cancel'] = $this->language->get('button_cancel');

        $d['error_login_details_changed'] = $this->language->get('error_login_details_changed');


        $d['push_messages_settings'] = $this->language->get("push_messages_settings");
        $d['push_messages_settings_help'] = $this->language->get("push_messages_settings_help");

        $d['device_name']       = $this->language->get("device_name");
        $d['account_email']     = $this->language->get("account_email");
        $d['last_activity']     = $this->language->get("last_activity");
        $d['select_all_none']   = $this->language->get("select_all_none");
        $d['app_connection_id'] = $this->language->get("app_connection_id");
        $d['store']             = $this->language->get("store");
        $d['new_order']         = $this->language->get("new_order");
        $d['new_customer']      = $this->language->get("new_customer");
        $d['order_statuses']    = $this->language->get("order_statuses");
        $d['currency']          = $this->language->get("currency");
        $d['status']            = $this->language->get("status");
        $d['delete']            = $this->language->get("delete");
        $d['unknown']           = $this->language->get("unknown");

        $d['disable']           = $this->language->get("disable");
        $d['enabled']           = $this->language->get("enabled");
        $d['enable']            = $this->language->get("enable");
        $d['disabled']          = $this->language->get("disabled");
        $d['are_you_sure']      = $this->language->get("are_you_sure");
        $d['no_data']           = $this->language->get("no_data");

        $d['bulk_actions']             = $this->language->get("bulk_actions");
        $d['enable_selected_devices']  = $this->language->get("enable_selected_devices");
        $d['disable_selected_devices'] = $this->language->get("disable_selected_devices");
        $d['delete_selected_devices']  = $this->language->get("delete_selected_devices");

        $d['please_select_push_settings'] = $this->language->get("please_select_push_settings");

        $d['mac_user']                  = $this->language->get('mac_user');
        $d['mac_add_user']              = $this->language->get('mac_add_user');
        $d['mac_get_app_from_gp']       = $this->language->get('mac_get_app_from_gp');
        $d['mac_click_or_scan_qr']      = $this->language->get('mac_click_or_scan_qr');
        $d['mac_permissions']           = $this->language->get('mac_permissions');
        $d['mac_regenerate_hash_url']   = $this->language->get('mac_regenerate_hash_url');
        $d['mac_push_notifications']    = $this->language->get('mac_push_notifications');
        $d['mac_new_order_created']     = $this->language->get('mac_new_order_created');
        $d['mac_order_status_changed']  = $this->language->get('mac_order_status_changed');
        $d['mac_new_customer_created']  = $this->language->get('mac_new_customer_created');
        $d['mac_store_statistics']      = $this->language->get('mac_store_statistics');
        $d['mac_view_store_statistics'] = $this->language->get('mac_view_store_statistics');
        $d['mac_orders']                = $this->language->get('mac_orders');
        $d['mac_view_order_list']       = $this->language->get('mac_view_order_list');
        $d['mac_view_order_details']    = $this->language->get('mac_view_order_details');
        $d['mac_change_order_status']   = $this->language->get('mac_change_order_status');
        $d['mac_customers']             = $this->language->get('mac_customers');
        $d['mac_view_customer_list']    = $this->language->get('mac_view_customer_list');
        $d['mac_view_customer_details'] = $this->language->get('mac_view_customer_details');
        $d['mac_products']              = $this->language->get('mac_products');
        $d['mac_view_product_list']     = $this->language->get('mac_view_product_list');
        $d['mac_view_product_details']  = $this->language->get('mac_view_product_details');
        $d['mac_all']                   = $this->language->get('mac_all');
        $d['mac_not_set']               = $this->language->get('mac_not_set');
        $d['mac_base_currency']               = $this->language->get('mac_base_currency');


        if (isset($this->error['warning'])) {
            $d['error_warning'] = $this->error['warning'];
        } else {
            $d['error_warning'] = '';
        }

        $d['breadcrumbs'] = array();

        $d['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $d['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_module'),
            'href'      => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $d['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('module/mobileassistantconnector', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => ' :: '
        );

        $d['action'] = $this->url->link('module/mobileassistantconnector', 'token=' . $this->session->data['token'], 'SSL');

        $d['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');


        $users_info = $this->getUsers();
        $d['users'] = $users_info['users'];

        if (isset($users_info['message_info'])&& $users_info['message_info'] == 'error_default_cred') {
            $d['message_info'] = $this->language->get('error_default_cred');
        } else {
            $d['message_info'] = '';
        }

        if($this->is_ver20) {
            if(!isset($data) || !is_array($data)) {
                $data = array();
            }
            $data = array_merge($data, $d);

            $data['header'] = $this->load->controller('common/header');
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['footer'] = $this->load->controller('common/footer');

            $this->response->setOutput($this->load->view('module/mobileassistant.tpl', $data));

        } else {
            $this->data = array_merge($this->data, $d);

            $this->load->model('design/layout');

            $this->data['layouts'] = $this->model_design_layout->getLayouts();

            $this->template = 'module/mobileassistant.tpl';
            $this->children = array(
                'common/header',
                'common/footer'
            );
            $this->response->setOutput($this->render());
        }
    }



    private function getUsers() {
        $this->load->model('mobileassistant/connector');
        $users_info = $this->model_mobileassistant_connector->get_users();

        // array('users' => $users, 'message_info' => $message_info);
        return $users_info;
    }


    private function checkUsers($users) {
        foreach($users as $user) {
            if (!isset($user['username']) || strlen($user['username']) <= 0) {
                $this->error['warning'] = $this->language->get('error_empty_login');
                return false;
            }
        }
        return true;
    }


    private function saveUsers($users) {
        $this->load->model('mobileassistant/connector');
        $users = $this->model_mobileassistant_connector->save_users($users);

        return $users;
    }



    function generate_qr_code_hash() {
        if(isset($this->request->request['user_id']) && $this->request->request['user_id'] > 0) {
            $user_id = intval($this->request->request['user_id']);

            $qr_code_hash = hash('sha256', md5(time() . rand(1111, 99999)));

            $this->load->model('mobileassistant/connector');
            if($this->model_mobileassistant_connector->update_qr_code_hash($user_id, $qr_code_hash)) {
                return array("qr_code_hash" => $qr_code_hash);
            }
        }
        return false;
    }

    private function getPushDevices() {
        $this->load->model('mobileassistant/connector');
        $push_devices = $this->model_mobileassistant_connector->get_push_devices();
        $this->load->language('module/mobileassistantconnector');

        $devices = array();

        foreach($push_devices as $push_device) {
            if(!$push_device['device_id'] || $push_device['device_id'] == '') {
                $push_device['device_name'] = "Unknown";
            }

            $devices[$push_device['device_unique_id']]['device_id'] = $push_device['device_id'];
            $devices[$push_device['device_unique_id']]['device_name'] = $push_device['device_name'];
            $devices[$push_device['device_unique_id']]['account_email'] = $push_device['account_email'];
            $devices[$push_device['device_unique_id']]['last_activity'] = $push_device['last_activity'];

            $push_device['store_id_name'] = $this->language->get('mac_all');
            if (isset($push_device['store_id']) && $push_device['store_id'] != "" && $push_device['store_id'] != -1) {
                $this->load->model('setting/store');
                $all_stores[0] = $this->config->get('config_name');
                $stores = $this->model_setting_store->getStores();

                foreach ($stores as $store) {
                    $all_stores[$store['store_id']] = $store['name'];
                }

                if (count($all_stores) > 0) {
                    $push_device['store_id_name'] = $all_stores[$push_device['store_id']];
                }
            }

            $push_device['push_currency_name'] = $this->language->get('mac_not_set');
            if (isset($push_device['push_currency_code']) && $push_device['push_currency_code'] != "" && $push_device['push_currency_code'] != "not_set") {
                if ($push_device['push_currency_code'] == "base_currency") {
                    $push_device['push_currency_name'] = $this->language->get('mac_base_currency');

                } else {
                    $this->load->model('localisation/currency');
                    $currencies = $this->model_localisation_currency->getCurrencies();
                    $all_currencies = array();

                    foreach ($currencies as $currency) {
                        $all_currencies[$currency['code']] = $currency['title'];
                    }

                    if (count($all_currencies) > 0) {
                        $push_device['push_currency_name'] = $all_currencies[$push_device['push_currency_code']];
                    }
                }
            }

            $push_device['push_order_statuses_names'] = "-";
            if (isset($push_device['push_order_statuses']) && $push_device['push_order_statuses'] != "") {
                if ($push_device['push_order_statuses'] == "-1") {
                    $push_device['push_order_statuses_names'] = $this->language->get('mac_all');
                } else {
                    $orders_statuses = $this->get_orders_statuses($push_device['push_order_statuses']);

                    $push_device['push_order_statuses_names'] = implode(", ", $orders_statuses);
                }
            }

            $devices[$push_device['device_unique_id']]['push_settings'][] = array(
                'setting_id' => $push_device['setting_id'],
                'registration_id' => $push_device['registration_id'],
                'app_connection_id' => $push_device['app_connection_id'],
                'store_id_name' => $push_device['store_id_name'],
                'push_new_order' => $push_device['push_new_order'],
                'push_order_statuses_names' => $push_device['push_order_statuses_names'],
                'push_new_customer' => $push_device['push_new_customer'],
                'push_currency_name' => $push_device['push_currency_name'],
                'status' => $push_device['status'],
            );
        }

        return $devices;
    }


    private function actionPushDevices($action, $ids) {
        if(!is_array($ids)) $ids = array($ids);

        if(count($ids) <= 0) {
            return;
        }

        $this->load->model('mobileassistant/connector');

        switch($action) {
            case 1:
                $this->model_mobileassistant_connector->enable_push_devices($ids);
                break;
            case 2:
                $this->model_mobileassistant_connector->disable_push_devices($ids);
                break;
            case 3:
                $this->model_mobileassistant_connector->delete_push_devices($ids);
                break;
        }
    }


    protected function validate() {
        $error = true;
        if (!$this->user->hasPermission('modify', 'module/mobileassistantconnector')) {
            $this->error['warning'] = $this->language->get('error_permission');
            $error = false;
        }
/*
        if (isset($this->request->post['mobassist_login']) && strlen($this->request->post['mobassist_login']) <= 0) {
            $this->error['warning'] = $this->language->get('error_empty_login');
            $error = false;
        }
*/
        return $error;
    }


    private function check_version() {
        if(class_exists('MijoShop')) {
            $base = MijoShop::get('base');

            $installed_ms_version = (array) $base->getMijoshopVersion();
            $mijo_version = $installed_ms_version[0];
            if(version_compare($mijo_version, '3.0.0', '>=') && version_compare(VERSION, '2.0.0.0', '<')) {
                $this->opencart_version = '2.0.1.0';
            } else {
                $this->opencart_version = VERSION;
            }

        } else {
            $this->opencart_version = VERSION;
        }

        $this->is_ver20 = version_compare($this->opencart_version, '2.0.0.0', '>=');
    }


    private function generate_output($data)
    {
        if (!is_array($data)) {
            $data = array($data);
        }

        $data = json_encode($data);

        if ($this->callback) {
            header('Content-Type: text/javascript;charset=utf-8');
            die($this->callback . '(' . $data . ');');
        } else {
            header('Content-Type: text/javascript;charset=utf-8');
            die($data);
        }
    }
}