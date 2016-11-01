<?php


class ControllerModuleMobileAssistantConnector extends Controller
{
    private $is_ver20;
    private $opencart_version;
    private $call_function;
    private $callback;
    private $hash;
    private $s;
    private $currency;
    private $module_user;

    private $show;
    private $page;
    private $search_order_id;
    private $orders_from;
    private $orders_to;
    private $customers_from;
    private $customers_to;
//    private $date_from;
//    private $date_to;
    private $graph_from;
    private $graph_to;
    private $stats_from;
    private $stats_to;
    private $products_to;
    private $products_from;
    private $order_id;
    private $user_id;
    private $params;
    private $val;
    private $search_val;
    private $statuses;
    private $sort_by;
    private $product_id;
    private $get_statuses;
    private $cust_with_orders;
    private $data_for_widget;
    private $registration_id;
    private $registration_id_old;
    private $api_key;
    private $push_new_order;
    private $push_order_statuses;
    private $push_new_customer;
    private $app_connection_id;
    private $push_currency_code;
    private $action;
    private $custom_period;
    private $store_id;
    private $new_status;
    private $currency_code;
    private $notify_customer;
    private $change_order_status_comment;
    private $param;
    private $new_value;
    private $account_email;
    private $device_name;
    private $last_activity;
    private $key;
    private $device_unique_id;

    private $without_thumbnails;
    private $only_items;
    private $order_by;
    private $group_by_product_id;
    private $qr_hash;


    const MODULE_CODE = 20;
    const MODULE_VERSION = '1.3.2';
    const PUSH_TYPE_NEW_ORDER = "new_order";
    const PUSH_TYPE_CHANGE_ORDER_STATUS = "order_changed";
    const PUSH_TYPE_NEW_CUSTOMER = "new_customer";
    const DEBUG_MODE = false;
    const MOB_ASSIST_API_KEY = "AIzaSyDIq4agB70Zv7AkB9pVuF2KxcU4WQ94CVI";

    const HASH_ALGORITHM       = 'sha256';
    const MAX_LIFETIME         = 86400; /* 24 hours */
    const T_SESSION_KEYS       = 'mobassistantconnector_session_keys';
    const T_FAILED_LOGIN       = 'mobassistantconnector_failed_login';
    const T_PUSH_NOTIFICATIONS = 'mobileassistant_push_settings';
    const T_DEVICES            = 'mobassistantconnector_devices';
    const T_USERS              = 'mobassistantconnector_users';

    public function index()
    {
        @date_default_timezone_set('UTC');
        $this->check_version();

        $this->load->model('mobileassistant/setting');

        $this->s = $this->model_mobileassistant_setting->getSetting('mobassist');

//        if (!isset($this->s['mobassist_status']) || $this->s['mobassist_status'] == 0) {
//            $this->generate_output('module_disabled');
//        }

        $request = $this->request->request;
        $this->_validate_types($request);

        if (empty($this->call_function)) {
            $this->run_self_test();
        }

        if ($this->call_function == 'get_qr_code') {
            $this->get_qr_code();
        }

        $this->load->model('mobileassistant/connector');
        $this->model_mobileassistant_connector->create_tables();
        $this->_checkUpdateModule();

        $this->clear_old_data();

        if ($this->call_function == 'get_version') {
            $this->get_version();
        }

        if ($this->hash) {
            if (!$this->check_auth()) {
                $this->add_failed_login();
                $this->generate_output('auth_error');
            }

            $key = $this->get_session_key();

            $this->generate_output(array('session_key' => $key));

        } elseif ($this->key) {
            if (!$this->check_session_key($this->key)) {
                $this->generate_output(array('bad_session_key' => true));
            }

        } else {
            $this->add_failed_login();
            $this->generate_output('auth_error');
        }


        if ($this->call_function == 'test_config') {
            $this->generate_output(array('test' => 1));
        }


        $this->map_push_notification_to_device();
        //$this->update_device_last_activity();


        if (empty($this->currency_code) || $this->currency_code == 'not_set') {
            $this->currency = '';

        } else if ($this->currency_code == 'base_currency') {
            $this->currency = $this->config->get('config_currency');

        } else {
            $this->currency = $this->currency_code;
        }

        if (empty($this->push_currency_code) || $this->push_currency_code == 'not_set') {
            $this->push_currency_code = '';
        }

        if ($this->store_id == '') {
            $this->store_id = -1;
        }

        $this->store_id = intval($this->store_id);

        if (!method_exists($this, $this->call_function)) {
            $this->generate_output('old_module');
        }

        if($this->_check_allowed_actions($this->call_function)) {
            $result = call_user_func(array($this, $this->call_function));
            $this->generate_output($result);
        } else {
            $this->generate_output('action_forbidden');
        }
    }


    private function _check_allowed_actions($function) {
        if($this->module_user && isset($this->module_user['user_status']) && $this->module_user['user_status'] == 1 && isset($this->module_user['allowed_actions'])) {
            $actions = array(
                'push_new_order'      => 'push_new_order',
                'push_new_order_156x' => 'push_new_order',

                'push_new_customer'      => 'push_new_customer',
                'push_new_customer_156x' => 'push_new_customer',

                'push_change_status'      => 'push_order_status_changed',
                'push_change_status_pre'  => 'push_order_status_changed',
                'push_change_status_156x' => 'push_order_status_changed',

                'get_store_stats'  => 'store_statistics',
                'get_data_graphs'  => 'store_statistics',
                'get_status_stats' => 'store_statistics',

                'get_orders'          => 'order_list',
                'get_orders_statuses' => 'order_list',
                'get_orders_info'     => 'order_details',
                'set_order_action'    => 'order_status_updating',

                'get_customers'      => 'customer_list',
                'get_customers_info' => 'customer_details',

                'search_products'         => 'product_list',
                'search_products_ordered' => 'product_list',

                'get_products_info'  => 'product_details',
                'get_products_descr' => 'product_details',
            );

            if(isset($actions[$function])) {
                $action = $actions[$function];
                if(isset($this->module_user['allowed_actions'][$action]) && $this->module_user['allowed_actions'][$action] == 1) {
                    return true;
                }
            } else {
                return true;
            }
        }

        return false;
    }

    private function _validate_types($array) {
        $names = array(
            'show' => 'INT',
            'page' => 'INT',
            'search_order_id' => 'STR',
            'orders_from' => 'STR',
            'orders_to' => 'STR',
            'customers_from' => 'STR',
            'customers_to' => 'STR',
            'date_from' => 'STR',
            'date_to' => 'STR',
            'graph_from' => 'STR',
            'graph_to' => 'STR',
            'stats_from' => 'STR',
            'stats_to' => 'STR',
            'products_to' => 'STR',
            'products_from' => 'STR',
            'order_id' => 'INT',
            'user_id' => 'INT',
            'params' => 'STR',
            'val' => 'STR',
            'search_val' => 'STR',
            'statuses' => 'STR',
            'sort_by' => 'STR',
            'last_order_id' => 'STR',
            'product_id' => 'INT',
            'get_statuses' => 'INT',
            'cust_with_orders' => 'INT',
            'data_for_widget' => 'INT',
            'registration_id' => 'STR',
            'registration_id_old' => 'STR',
            'api_key' => 'STR',
            'push_new_order' => 'INT',
            'push_order_statuses' => 'STR',
            'push_new_customer' => 'INT',
            'app_connection_id' => 'STR',
            'push_currency_code' => 'STR',
            'action' => 'STR',
            'carrier_code' => 'STR',
            'custom_period' => 'INT',
            'store_id' => 'STR',
            'new_status' => 'INT',
            'notify_customer' => 'INT',
            'currency_code' => 'STR',
            'account_email' => 'STR',
            'device_name' => 'STR',
            'last_activity' => 'STR',
            'fc' => 'STR',
            'module' => 'STR',
            'controller' => 'STR',
            'change_order_status_comment' => 'STR',
            'param' => 'STR',
            'new_value' => 'STR',
            'hash' => 'STR',
            'device_unique_id' => 'STR',
            'call_function' => 'STR',
            'order_by' => 'STR',
            'key' => 'STR',
            'qr_hash' => 'STR',
            'without_thumbnails' => 'INT',
            'only_items' => 'INT',
            'group_by_product_id' => 'INT',
        );

        foreach ($names as $name => $type) {
            if (isset($array["$name"])) {
                switch ($type) {
                    case 'INT':
                        $array["$name"] = intval($array["$name"]);
                        break;
                    case 'FLOAT':
                        $array["$name"] = floatval($array["$name"]);
                        break;
                    case 'STR':
                        $array["$name"] = str_replace(array("\r", "\n"), ' ', addslashes(htmlspecialchars(trim(urldecode($array["$name"])))));
                        break;
                    case 'STR_HTML':
                        $array["$name"] = addslashes(trim(urldecode($array["$name"])));
                        break;
                    default:
                        $array["$name"] = '';
                }
            } else {
                $array["$name"] = '';
            }

            $this->{$name} = $array["$name"];
        }

        return $array;
    }


    private function get_version() {
        $session_key = false;

        if ($this->hash) {
            if ($this->check_auth()) {
                $this->add_failed_login();
//                $this->generate_output('auth_error');
                if ($this->key && $this->check_session_key($this->key)) {
                    $session_key = $this->key;
                } else {
                    $session_key = $this->get_session_key();
                }
            } else {
                $this->add_failed_login();
            }

        } elseif ($this->key) {
            if(!$this->check_session_key($this->key)) {
                $session_key = $this->key;
//                $this->generate_output('auth_error');
            } else {
                $this->add_failed_login();
            }
        }

        if ($session_key) {
            $this->generate_output(array('session_key' => $session_key));
        }

        $this->add_failed_login();
        $this->generate_output(array());
    }


    private function run_self_test()
    {
        $html = '<h2>Mobile Assistant Connector (v. ' . self::MODULE_VERSION . ')</h2>';

        if (class_exists('MijoShop')) {
            $base = MijoShop::get('base');

            $installed_ms_version = (array)$base->getMijoshopVersion();
            $mijo_version = $installed_ms_version[0];

            $html .= '<table cellpadding=4><tr><th>Test Title</th><th>Result</th></tr>';
            $html .= '<tr><td>MijoShop Version</td><td>' . $mijo_version . '</td><td></td></tr>';
            $html .= '<tr><td>MijoShop Opencart Version</td><td>' . VERSION . '</td><td></td></tr>';
            $html .= '</table><br/>';
        }


        $html .= '<div style="margin-top: 15px; font-size: 13px;">Mobile Assistant Connector by <a href="http://platformx.tech" target="_blank" style="color: #15428B">eMagicOne</a></div>';

        die($html);
    }

    private function get_qr_code()
    {
        if(empty($this->qr_hash)) {
            return;
        }

        $data = array("qr_hash" => $this->qr_hash);

        $this->load->model('mobileassistant/connector');
        $user = $this->model_mobileassistant_connector->getModuleUser($data);
        if(!$user) {
            return;
        }

        $qrcode = $this->generate_QR_code($user);

        $html = '<script type="text/javascript" src="admin/view/javascript/qrcode.min.js"></script>';
        $html .= '<h3>Mobile Assistant Connector (v. ' . self::MODULE_VERSION . ')</h3>';

        $html .= '<div id="mobassist_qr_code" style="margin-left: 20px; margin-top: 20px;"></div>';

        $html .= '
        <script type="text/javascript">
        var qrcode = new QRCode(document.getElementById("mobassist_qr_code"), {
            width : 250,
            height : 250
        });
        qrcode.makeCode("' . $qrcode  . '");
        </script>';

        die($html);
    }


    private function generate_QR_code($user) {
        $url = "";
        if(defined("HTTP_CATALOG")) {
            $url = HTTP_CATALOG;
        } else if(defined("HTTP_SERVER")) {
            $url = HTTP_SERVER;
        }

        $url = str_replace("http://", "", $url);
        $url = str_replace("https://", "", $url);

        $config = array(
            'url' => $url,
            'login' => $user['username'],
            'password' => $user['password'],
        );

        $config = base64_encode(json_encode($config));

        return $config;
    }


    private function test_default_password_is_changed()
    {
        return !($this->s['mobassist_login'] == '1' && $this->s['mobassist_pass'] == 'c4ca4238a0b923820dcc509a6f75849b');
    }

    private function generate_output($data)
    {
        $add_bridge_version = false;

        if (in_array($this->call_function, array("test_config", "get_store_title", "get_store_stats", "get_data_graphs", "get_version"))) {
            if (is_array($data) && $data != 'auth_error' && $data != 'connection_error' && $data != 'old_bridge') {
                $add_bridge_version = true;
            }
        }

        if (!is_array($data)) {
            $data = array($data);
        }

        if ($add_bridge_version) {
            $data['module_version'] = self::MODULE_CODE;
        }

        if (is_array($data)) {
            array_walk_recursive($data, array($this, 'reset_null'));
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


    private function reset_null(&$item)
    {
        if (empty($item) && $item != 0) {
            $item = '';
        }
        $item = trim($item);
    }


    private function check_auth()
    {
        $this->load->model('mobileassistant/connector');
        $user = $this->model_mobileassistant_connector->checkAuth($this->hash);


//        if (md5($this->s['mobassist_login'] . $this->s['mobassist_pass']) == $this->hash) {
//        if (hash('sha256', $this->s['mobassist_login'] . $this->s['mobassist_pass']) == $this->hash) {
//            return true;
//        }
        if($user) {
            if(isset($user['user_status']) && $user['user_status'] == 1) {
                $this->module_user = $user;
                $this->clear_failed_login();
                return true;
            } else {
                $this->generate_output('user_disabled');
            }
        }

        return false;
    }



//===============================================================================

    private function get_stores()
    {
        $this->load->model('setting/store');
        $all_stores[] = array('store_id' => 0, 'name' => $this->config->get('config_name'));

        $stores = $this->model_setting_store->getStores();

        foreach ($stores as $store) {
            $all_stores[] = array('store_id' => $store['store_id'], 'name' => $store['name']);
        }

        return $all_stores;
    }


    private function get_currencies()
    {
        $this->load->model('localisation/currency');

        $currencies = $this->model_localisation_currency->getCurrencies();

        $all_currencies = array();

        foreach ($currencies as $currency) {
            $all_currencies[] = array('code' => $currency['code'], 'name' => $currency['title']);
        }

        return $all_currencies;
    }


    private function get_store_title()
    {
        if ($this->store_id > -1) {
            $this->load->model('setting/setting');
            $settings = $this->model_setting_setting->getSetting('config', $this->store_id);
            $title = $settings['config_name'];

        } else {
            $title = $this->config->get('config_name');
        }

        return array('test' => 1, 'title' => $title);
    }

    private function get_store_stats()
    {
        $data_graphs = '';
        $order_status_stats = array();
        $store_stats = array('count_orders' => "0", 'total_sales' => "0", 'count_customers' => "0", 'count_products' => "0", "last_order_id" => "0", "new_orders" => "0");
        $today = date("Y-m-d", time());
        $date_from = $date_to = $today;

        $data = array();

        if (!empty($this->stats_from)) {
            $date_from = $this->stats_from;
        }

        if (!empty($this->stats_to)) {
            $date_to = $this->stats_to;
        }

        if (isset($this->custom_period) && strlen($this->custom_period) > 0) {
            $custom_period = $this->get_custom_period($this->custom_period);

            $date_from = $custom_period['start_date'];
            $date_to = $custom_period['end_date'];
        }

        if (!empty($date_from)) {
            $data['date_from'] = $date_from . " 00:00:00";
        }

        if (!empty($date_to)) {
            $data['date_to'] = $date_to . " 23:59:59";
        }

        if ($this->statuses != '') {
            $data['statuses'] = $this->get_filter_statuses($this->statuses);
        }

        if ($this->store_id > -1) {
            $data['store_id'] = $this->store_id;
        }

        if (!empty($this->currency)) {
            $data['currency_code'] = $this->currency;
        }

        $this->load->model('mobileassistant/connector');


        $orders_stats = $this->model_mobileassistant_connector->getTotalOrders($data);
        $store_stats = array_merge($store_stats, $orders_stats);

        $customers_stats = $this->model_mobileassistant_connector->getTotalCustomers($data);
        $store_stats = array_merge($store_stats, $customers_stats);

        $products_stats = $this->model_mobileassistant_connector->getTotalSoldProducts($data);
        $store_stats = array_merge($store_stats, $products_stats);


        if (!isset($this->data_for_widget) || empty($this->data_for_widget) || $this->data_for_widget != 1) {
            $data_graphs = $this->get_data_graphs();
        }

        if (!isset($this->data_for_widget) || $this->data_for_widget != 1) {
            $order_status_stats = $this->get_status_stats();
        }

        $result = array_merge($store_stats, array('data_graphs' => $data_graphs), array('order_status_stats' => $order_status_stats));

        return $result;
    }


    private function get_data_graphs()
    {
        $data = array();

        if (empty($this->graph_from)) {
            $this->graph_from = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - 7, date("Y")));
        }
        $data['graph_from'] = $this->graph_from . " 00:00:00";

        if (empty($this->graph_to)) {
            if (!empty($this->stats_to)) {
                $this->graph_to = $this->stats_to;
            } else {
                $this->graph_to = date("Y-m-d", time());
            }
        }
        $data['graph_to'] = $this->graph_to . " 23:59:59";

        if (isset($this->custom_period) && strlen($this->custom_period) > 0) {
            $data['custom_period'] = $this->custom_period;
            $data['custom_period_date'] = $this->get_custom_period($this->custom_period);
        }

        if ($this->store_id > -1) {
            $data['store_id'] = $this->store_id;
        }

        if ($this->statuses != '') {
            $data['statuses'] = $this->get_filter_statuses($this->statuses);
        }

        if (!empty($this->currency)) {
            $data['currency_code'] = $this->currency;
        }

        $this->load->model('mobileassistant/connector');
        $chart_data = $this->model_mobileassistant_connector->getChartData($data);

        return $chart_data;
    }


    private function get_status_stats()
    {
        $today = date("Y-m-d", time());
        $date_from = $date_to = $today;

        $data = array();

        if (!empty($this->stats_from)) {
            $date_from = $this->stats_from;
        }

        if (!empty($this->stats_to)) {
            $date_to = $this->stats_to;
        }

        if (isset($this->custom_period) && strlen($this->custom_period) > 0) {
            $custom_period = $this->get_custom_period($this->custom_period);

            $date_from = $custom_period['start_date'];
            $date_to = $custom_period['end_date'];
        }

        if (!empty($date_from)) {
            $data['date_from'] = $date_from . " 00:00:00";
        }

        if (!empty($date_to)) {
            $data['date_to'] = $date_to . " 23:59:59";
        }

        if ($this->store_id > -1) {
            $data['store_id'] = $this->store_id;
        }

        if (!empty($this->currency)) {
            $data['currency_code'] = $this->currency;
        }


        $this->load->model('mobileassistant/connector');
        $order_statuses = $this->model_mobileassistant_connector->getOrderStatusStats($data);

        return $order_statuses;
    }


    private function get_orders()
    {
        $data = array();

        $this->load->model('mobileassistant/connector');

        if ($this->store_id > -1) {
            $data['store_id'] = $this->store_id;
        }

        if ($this->statuses !== null && $this->statuses != '') {
            $data['statuses'] = $this->get_filter_statuses($this->statuses);
        }

        if (!empty($this->search_order_id)) {
            $data['search_order_id'] = $this->search_order_id;
        }

        if ($this->orders_from !== null && !empty($this->orders_from)) {
            $data['orders_from'] = $this->orders_from . " 00:00:00";
        }

        if ($this->orders_to !== null && !empty($this->orders_to)) {
            $data['orders_to'] = $this->orders_to . " 23:59:59";
        }

        if (!empty($this->currency)) {
            $data['currency_code'] = $this->currency;
        }

        if (!empty($this->get_statuses)) {
            $data['get_statuses'] = $this->get_statuses;
        }

        if ($this->page !== null && !empty($this->page) && $this->show !== null && !empty($this->show)) {
            $data['page'] = ($this->page - 1) * $this->show;
            $data['show'] = $this->show;
        }

        if (!empty($this->sort_by)) {
            $data['sort_by'] = $this->sort_by;
        } else {
            $data['sort_by'] = "id";
        }

        if (!empty($this->order_by)) {
            $data['order_by'] = $this->order_by;
        }

        $orders = $this->model_mobileassistant_connector->getOrders($data);
        return $orders;
    }

    private function get_orders_statuses()
    {
        $this->load->model('mobileassistant/connector');
        $order_statuses = $this->model_mobileassistant_connector->getOrdersStatuses();
        return $order_statuses;
    }


    private function get_orders_info()
    {
        $data = array();

        $this->load->model('mobileassistant/connector');

        $data['order_id'] = $this->order_id;
        $data['page'] = ($this->page - 1) * $this->show;
        $data['show'] = $this->show;

        if (!empty($this->currency)) {
            $data['currency_code'] = $this->currency;
        }

        $data['without_thumbnails'] = false;
        if (!empty($this->without_thumbnails) && $this->without_thumbnails == 1) {
            $data['without_thumbnails'] = true;
        }

        $only_items = false;
        if (!empty($this->only_items) && $this->only_items == 1) {
            $only_items = true;
        }

        $order_products = $this->model_mobileassistant_connector->getOrderProducts($data);
        $order_full_info = array("order_products" => $order_products);

        if (!$only_items) {
            $order_info = $this->model_mobileassistant_connector->getOrdersInfo($data);
            $order_full_info['order_info'] = $order_info;
        }
        if (!$only_items) {
            $count_prods = $this->model_mobileassistant_connector->getOrderCountProducts($data);
            $order_full_info['o_products_count'] = $count_prods;
        }
        if (!$only_items) {
            $order_total = $this->model_mobileassistant_connector->getOrderTotals($data);
            $order_full_info['order_total'] = $order_total;
        }

        return $order_full_info;
    }


    private function get_customers()
    {
        $data = array();

        if (!empty($this->customers_from)) {
            $data['customers_from'] = $this->customers_from . " 00:00:00";
        }

        if (!empty($this->customers_to)) {
            $data['customers_to'] = $this->customers_to . " 23:59:59";
        }

        if (!empty($this->search_val)) {
            $data['search_val'] = $this->search_val;
        }

        if (!empty($this->cust_with_orders)) {
            $data['cust_with_orders'] = $this->cust_with_orders;
        }

        if ($this->store_id > -1) {
            $data['store_id'] = $this->store_id;
        }

        $data['page'] = ($this->page - 1) * $this->show;
        $data['show'] = $this->show;

        if (empty($this->sort_by)) {
            $data['sort_by'] = "id";
        } else {
            $data['sort_by'] = $this->sort_by;
        }

        if (!empty($this->order_by)) {
            $data['order_by'] = $this->order_by;
        }

        $this->load->model('mobileassistant/connector');

        $customers = $this->model_mobileassistant_connector->getCustomers($data);

        return $customers;
    }


    private function get_customers_info()
    {
        $data = array();

        $data['page'] = ($this->page - 1) * $this->show;
        $data['show'] = $this->show;

        $data['user_id'] = $this->user_id;

        if (!empty($this->currency)) {
            $data['currency_code'] = $this->currency;
        }

        $data['only_items'] = false;
        if (!empty($this->only_items) && $this->only_items == 1) {
            $data['only_items'] = true;
        }

        $this->load->model('mobileassistant/connector');

        return $this->model_mobileassistant_connector->getCustomersInfo($data);
    }


    private function search_products($ordered = false)
    {
        $data = array();

        if (!empty($this->params)) {
            $data['params'] = explode("|", $this->params);
        }

        if (!empty($this->val)) {
            $data['val'] = $this->val;
        }

        if (!empty($this->products_from)) {
            $data['products_from'] = $this->products_from . " 00:00:00";
        }

        if (!empty($this->products_to)) {
            $data['products_to'] = $this->products_to . " 23:59:59";
        }

        if (empty($this->sort_by)) {
            $data['sort_by'] = "id";
        } else {
            $data['sort_by'] = $this->sort_by;
        }

        if (!empty($this->order_by)) {
            $data['order_by'] = $this->order_by;
        }

        if (!empty($this->currency)) {
            $data['currency_code'] = $this->currency;
        }

        if ($this->store_id > -1) {
            $data['store_id'] = $this->store_id;
        }

        if ($this->statuses != '') {
            $data['statuses'] = $this->get_filter_statuses($this->statuses);
        }

        $data['without_thumbnails'] = false;
        if (!empty($this->without_thumbnails) && $this->without_thumbnails == 1) {
            $data['without_thumbnails'] = true;
        }

        $data['page'] = ($this->page - 1) * $this->show;
        $data['show'] = $this->show;

        $this->load->model('mobileassistant/connector');

        if ($ordered) {
            $data['group_by_product_id'] = false;
            if (!empty($this->group_by_product_id) && $this->group_by_product_id == 1) {
                $data['group_by_product_id'] = true;
            }

            return $this->model_mobileassistant_connector->getOrderedProducts($data);
        }

        return $this->model_mobileassistant_connector->getProducts($data);
    }


    private function search_products_ordered()
    {
        return $this->search_products(true);
    }


    private function get_products_info()
    {
        $data = array('currency_code' => $this->currency, 'product_id' => $this->product_id);

        $data['without_thumbnails'] = false;
        if (!empty($this->without_thumbnails) && $this->without_thumbnails == 1) {
            $data['without_thumbnails'] = true;
        }

        $this->load->model('mobileassistant/connector');

        return $this->model_mobileassistant_connector->getProductInfo($data);
    }


    private function get_products_descr()
    {
        $data = array('product_id' => $this->product_id);

        $this->load->model('mobileassistant/connector');

        return $this->model_mobileassistant_connector->getProductDescr($data);
    }


//-----------------------------------

    private function set_order_action()
    {
        $this->load->model('mobileassistant/helper');

        if ($this->order_id <= 0) {
            $error = 'Order ID cannot be empty!';
            $this->model_mobileassistant_helper->write_log('ORDER ACTION ERROR: ' . $error);
            return array('error' => $error);
        }

        if (empty($this->action)) {
            $error = 'Action is not set!';
            $this->model_mobileassistant_helper->write_log('ORDER ACTION ERROR: ' . $error);
            return array('error' => $error);
        }

        $this->load->model('checkout/order');
        $order = $this->model_checkout_order->getOrder($this->order_id);

        if (!$order) {
            $error = 'Order not found!';
            $this->model_mobileassistant_helper->write_log('ORDER ACTION ERROR: ' . $error);
            return array('error' => $error);
        }

        if ($this->action == 'change_status') {
            if (!isset($this->new_status) || intval($this->new_status) < 0) {
                $error = 'New order status is not set!';
                $this->model_mobileassistant_helper->write_log('ORDER ACTION ERROR: ' . $error);
                return array('error' => $error);
            }

            $notify = false;
            if (isset($this->notify_customer) && $this->notify_customer == 1) {
                $notify = true;
            }


            if ($this->is_ver20) {
                $this->model_checkout_order->addOrderHistory(
                    $this->order_id,
                    $this->new_status,
                    $this->change_order_status_comment,
                    $notify
                );
            } else {
                $this->load->model('mobileassistant/connector');

                if (version_compare($this->opencart_version, '1.5.4.1', '<=')) {
                    $this->model_mobileassistant_connector->addOrderHistory_154x(
                        $this->order_id,
                        $this->new_status,
                        $this->change_order_status_comment,
                        $notify
                    );
                } else {
                    $this->model_mobileassistant_connector->addOrderHistory_156x(
                        $this->order_id,
                        $this->new_status,
                        $this->change_order_status_comment,
                        $notify
                    );
                }
            }

            return array('success' => 'true');
        }

        $error = 'Unknown error!';
        $this->model_mobileassistant_helper->write_log('ORDER ACTION ERROR: ' . $error);
        return array('error' => $error);
    }


    private function push_notification_settings()
    {
        $data = array();
        $this->load->model('mobileassistant/helper');

        if (empty($this->registration_id)) {
            $error = 'Empty device ID';
            $this->model_mobileassistant_helper->write_log('PUSH SETTINGS ERROR: ' . $error);
            return array('error' => $error);
        }

        if (empty($this->app_connection_id) || $this->app_connection_id < 0) {
            $error = 'Wrong app connection ID: ' . $this->app_connection_id;
            $this->model_mobileassistant_helper->write_log('PUSH SETTINGS ERROR: ' . $error);
            return array('error' => $error);
        }

        if (empty($this->api_key)) {
            $error = 'Empty application API key';
            $this->model_mobileassistant_helper->write_log('PUSH SETTINGS ERROR: ' . $error);
            return array('error' => $error);
        }

        if(!$this->module_user || !isset($this->module_user['user_id'])) {
            $error = 'User not found!';
            $this->model_mobileassistant_helper->write_log('PUSH SETTINGS ERROR: ' . $error);
            return array('error' => $error);
        }


        $this->load->model('mobileassistant/setting');
        $s = $this->model_mobileassistant_setting->getSetting('mobassist');

        $s['mobassist_api_key'] = $this->api_key;

        $this->model_mobileassistant_setting->editSetting('mobassist', $s);

        $data['registration_id'] = $this->registration_id;
        $data['app_connection_id'] = $this->app_connection_id;
        $data['store_id'] = $this->store_id;
        $data['push_new_order'] = $this->push_new_order;
        $data['push_order_statuses'] = $this->push_order_statuses;
        $data['push_new_customer'] = $this->push_new_customer;
        $data['push_currency_code'] = $this->push_currency_code;
        $data['user_id'] = $this->module_user['user_id'];

        if (!empty($this->registration_id_old)) {
            $data['registration_id_old'] = $this->registration_id_old;
        }

        $this->load->model('mobileassistant/connector');

        if ($this->model_mobileassistant_connector->savePushNotificationSettings($data)) {
            $this->map_push_notification_to_device();
            return array('success' => 'true');
        }

        $error = 'Unknown occurred!';

        $this->model_mobileassistant_helper->write_log('PUSH SETTINGS ERROR: ' . $error);
        return array('error' => $error);
    }


//==========================================
//=============== push


    public function push_new_order($route, $order_id = 0)
    {
        if (!$this->check_module_installed()) {
            return;
        }

        $this->check_version();
        if(version_compare($this->opencart_version, '2.2.0.0', '<')) {
            $order_id = $route;
        }

        $this->load->model('checkout/order');

        $order = $this->model_checkout_order->getOrder($order_id);

        if (!$order) {
            if (self::DEBUG_MODE) {
                $this->load->model('mobileassistant/helper');
                $this->model_mobileassistant_helper->write_log('PUSH REQUEST DATA: function: ' . __FUNCTION__ . ': Order not found');
            }
            return;
        }

        $type = self::PUSH_TYPE_NEW_ORDER;
        $this->sendOrderPushMessage($order, $type);
    }


    public function push_new_order_156x($order_id, $total = 0)
    {
        if (!$this->check_module_installed()) {
            return;
        }

        $this->load->model('sale/order');
        $order = $this->model_sale_order->getOrder($order_id);

        if (!$order) {
            if (self::DEBUG_MODE) {
                $this->model_mobileassistant_helper->write_log('PUSH REQUEST DATA: function: ' . __FUNCTION__ . ': Order not found');
            }
            return;
        }

        if (!isset($order['total']) || $order['total'] == 0) {
            $order['total'] = $total;
        }

        $this->load->model('mobileassistant/helper');
        if (!isset($order['order_status'])) {
            if ($order['order_status_id'] == 0) {

                $default_attrs = $this->model_mobileassistanthelper->_get_default_attrs();
                $order['order_status'] = $default_attrs['text_missing'];
            } else {
                $sql = "SELECT name FROM " . DB_PREFIX . "order_status WHERE language_id = '" . $this->model_mobileassistanthelper->getAdminLanguageId() . "' AND order_status_id = '" . $order['order_status_id'] . "'";
                $query = $this->db->query($sql);
                if ($query->num_rows) {
                    $order['order_status'] = $query->row['name'];
                } else {
                    $order['order_status'] = '';
                }
            }
        }

        $type = self::PUSH_TYPE_NEW_ORDER;
        $this->sendOrderPushMessage($order, $type);
    }


    public function push_change_status($route, $response = "", $order_id = 0, $data = "")
    {
        if (!$this->check_module_installed()) {
            return;
        }

        $this->check_version();
        if(version_compare($this->opencart_version, '2.2.0.0', '<')) {
            $order_id = $route;
        } else {
            if (self::DEBUG_MODE) {
                $this->load->model('mobileassistant/helper');
                $this->model_mobileassistant_helper->write_log('PUSH REQUEST DATA: $route: ' . $route . " | function: " . __FUNCTION__);
            }
        }

        $this->load->model('checkout/order');

        $order = $this->model_checkout_order->getOrder($order_id);

        if (!$order) {
            if (self::DEBUG_MODE) {
                $this->load->model('mobileassistant/helper');
                $this->model_mobileassistant_helper->write_log('PUSH REQUEST DATA: function: ' . __FUNCTION__ . ': Order not found');
            }
            return;
        }

        $type = self::PUSH_TYPE_CHANGE_ORDER_STATUS;
        $this->sendOrderPushMessage($order, $type);
    }


    public function push_change_status_pre($route, $order_id = 0)
    {
        if (!$this->check_module_installed()) {
            return;
        }

        $this->check_version();
        if(version_compare($this->opencart_version, '2.2.0.0', '<')) {
            $order_id = $route;
        } else {
            if (self::DEBUG_MODE) {
                $this->load->model('mobileassistant/helper');
                $this->model_mobileassistant_helper->write_log('PUSH REQUEST DATA: $route: ' . $route . " | function: " . __FUNCTION__);
            }
        }


        $this->load->model('checkout/order');

        $order = $this->model_checkout_order->getOrder($order_id);

        if (!$order) {
            if (self::DEBUG_MODE) {
                $this->load->model('mobileassistant/helper');
                $this->model_mobileassistant_helper->write_log('PUSH REQUEST DATA: function: ' . __FUNCTION__ . ': Order not found');
            }
            return;
        }

        if ($order['order_status_id'] == 0) {
            $type = self::PUSH_TYPE_CHANGE_ORDER_STATUS;
            $this->sendOrderPushMessage($order, $type);
        }
    }


    public function push_change_status_156x($order_id, $data)
    {
        if (!$this->check_module_installed()) {
            return;
        }

        $this->load->model('sale/order');
        $order = $this->model_sale_order->getOrder($order_id);

        if (!$order) {
            if (self::DEBUG_MODE) {
                $this->load->model('mobileassistant/helper');
                $this->model_mobileassistant_helper->write_log('PUSH REQUEST DATA: function: ' . __FUNCTION__ . ': Order not found');
            }
            return;
        }

        $order['order_status_id'] = $data['order_status_id'];

        $this->load->model('mobileassistant/helper');
        if (!isset($order['order_status'])) {
            if ($order['order_status_id'] == 0) {
                $default_attrs = $this->model_mobileassistanthelper->_get_default_attrs();
                $order['order_status'] = $default_attrs['text_missing'];
            } else {
                $sql = "SELECT name FROM " . DB_PREFIX . "order_status WHERE language_id = '" . $this->model_mobileassistanthelper->getAdminLanguageId() . "' AND order_status_id = '" . $order['order_status_id'] . "'";
                $query = $this->db->query($sql);
                if ($query->num_rows) {
                    $order['order_status'] = $query->row['name'];
                } else {
                    $order['order_status'] = '';
                }
            }
        }

        $type = self::PUSH_TYPE_CHANGE_ORDER_STATUS;
        $this->sendOrderPushMessage($order, $type);
    }


    public function push_new_customer($route, $customer_id = 0)
    {
        if (!$this->check_module_installed()) {
            return;
        }

        $this->check_version();
        if(version_compare($this->opencart_version, '2.2.0.0', '<')) {
            $customer_id = $route;
        } else {
            if (self::DEBUG_MODE) {
                $this->load->model('mobileassistant/helper');
                $this->model_mobileassistant_helper->write_log('PUSH REQUEST DATA: $route: ' . $route . " | function: " . __FUNCTION__);
            }
        }


        $this->load->model('account/customer');
        $customer = $this->model_account_customer->getCustomer($customer_id);

        if (!$customer) {
            if (self::DEBUG_MODE) {
                $this->load->model('mobileassistant/helper');
                $this->model_mobileassistant_helper->write_log('PUSH REQUEST DATA: function: ' . __FUNCTION__ . ': Customer not found');
            }
            return;
        }

        $this->sendCustomerPushMessage($customer);
    }


    public function push_new_customer_156x($customer_id)
    {
        if (!$this->check_module_installed()) {
            return;
        }

        $this->load->model('sale/customer');
        $customer = $this->model_sale_customer->getCustomer($customer_id);

        if (!$customer) {
            return;
        }

        $this->sendCustomerPushMessage($customer);
    }


    private function sendOrderPushMessage($order, $type)
    {
        $data = array("store_id" => $this->config->get('config_store_id'));

        if ($type == self::PUSH_TYPE_NEW_ORDER && $order['order_status_id'] == 0) {
            $data['missing_new_order'] = true;

        } else if ($type == self::PUSH_TYPE_CHANGE_ORDER_STATUS) {
            $data['status'] = $order['order_status_id'];
            if ($order['order_status_id'] == 0) {
                $type = self::PUSH_TYPE_NEW_ORDER;
                $data['real_new_order'] = true;
            }
        }

        $data['type'] = $type;

        $push_devices = $this->getPushDevices($data);

        if (self::DEBUG_MODE) {
            $this->load->model('mobileassistant/helper');
            $this->model_mobileassistant_helper->write_log('PUSH REQUEST DATA: $type: ' . $type);
        }

        if (!$push_devices || count($push_devices) <= 0) {
            if (self::DEBUG_MODE) {
                $this->load->model('mobileassistant/helper');
                $this->model_mobileassistant_helper->write_log('PUSH REQUEST DATA: function: ' . __FUNCTION__ . ': Devices not found');
            }
            return;
        }


        $this->load->model('mobileassistant/helper');

        if (defined("HTTP_CATALOG")) {
            $url = HTTP_CATALOG;
        } else {
            $url = HTTP_SERVER;
        }

        $url = str_replace("http://", "", $url);
        $url = str_replace("https://", "", $url);

        foreach ($push_devices as $push_device) {
            if (empty($push_device['push_currency_code']) || $push_device['push_currency_code'] == 'not_set') {
                $currency_code = (isset($order['currency_code']) ? $order['currency_code'] : $order['currency']);

            } else if ($push_device['push_currency_code'] == 'base_currency') {
                $currency_code = $this->config->get('config_currency');

            } else {
                $currency_code = $push_device['push_currency_code'];
            }

            $message = array(
                "push_notif_type" => $type,
                "order_id" => $order['order_id'],
                "customer_name" => $order['firstname'] . ' ' . $order['lastname'],
                "email" => $order['email'],
                "new_status" => $order['order_status'],
                "total" => $this->model_mobileassistant_helper->nice_price($order['total'], $currency_code),
                "store_url" => $url,
                "app_connection_id" => $push_device['app_connection_id']
            );

            if ($type == self::PUSH_TYPE_CHANGE_ORDER_STATUS) {
                $message['new_status_code'] = $order['order_status_id'];
            }

            $this->sendPush2Google($push_device['setting_id'], $push_device['registration_id'], $message);
        }
    }


    public function delete_push_config()
    {
        if (!empty($this->app_connection_id) && !empty($this->registration_id)) {
            $sql = "SELECT setting_id, device_id FROM " . DB_PREFIX . self::T_PUSH_NOTIFICATIONS . " WHERE registration_id = '%s' AND app_connection_id = '%s' AND user_id = '%d' GROUP BY device_id";
            $sql = sprintf($sql, $this->registration_id, $this->app_connection_id, $this->module_user['user_id']);
            $query = $this->db->query($sql);

            if ($query->num_rows) {
                foreach ($query->rows as $row) {
                    $device_id = $query->row['device_id'];

                    if ($this->deletePushRegId($row['setting_id'])) {
                        $this->delete_empty_devices($device_id);
                    }
                }
            }

            return array('success' => 'true');
        }

        return array('error' => 'missing_parameters');
    }


    public function delete_empty_devices($device_id)
    {
        $sql_d = "SELECT setting_id FROM " . DB_PREFIX . self::T_PUSH_NOTIFICATIONS . " WHERE device_id = '%d' AND user_id = '%d'";
        $sql_d = sprintf($sql_d, $device_id, $this->module_user['user_id']);
        $query_d = $this->db->query($sql_d);

        if ($query_d->num_rows <= 0) {
            $sql = "DELETE FROM " . DB_PREFIX . self::T_DEVICES . " WHERE device_id = '%d'";
            $sql = sprintf($sql, $device_id);
            $this->db->query($sql);
        }
    }


    public function sendCustomerPushMessage($customer)
    {
        $type = self::PUSH_TYPE_NEW_CUSTOMER;
        $data = array("store_id" => $this->config->get('config_store_id'), "type" => $type);

        $push_devices = $this->getPushDevices($data);

        if (!$push_devices || count($push_devices) <= 0) {
            if (self::DEBUG_MODE) {
                $this->load->model('mobileassistant/helper');
                $this->model_mobileassistant_helper->write_log('PUSH REQUEST DATA: function: ' . __FUNCTION__ . ': Devices not found');
            }
            return;
        }


        $url = "";
        if (defined("HTTP_CATALOG")) {
            $url = HTTP_CATALOG;
        } else if (defined("HTTP_SERVER")) {
            $url = HTTP_SERVER;
        }

        $url = str_replace("http://", "", $url);
        $url = str_replace("https://", "", $url);

        foreach ($push_devices as $push_device) {
            $message = array(
                "push_notif_type" => $type,
                "customer_id" => $customer['customer_id'],
                "customer_name" => $customer['firstname'] . ' ' . $customer['lastname'],
                "email" => $customer['email'],
                "store_url" => $url,
                "app_connection_id" => $push_device['app_connection_id']
            );

            $this->sendPush2Google($push_device['setting_id'], $push_device['registration_id'], $message);
        }
    }


    private function sendPush2Google($setting_id, $registration_id, $message)
    {
        $this->load->model('mobileassistant/setting');
        $s = $this->model_mobileassistant_setting->getSetting('mobassist');

        if (isset($s['mobassist_api_key'])) {
            $apiKey = $s['mobassist_api_key'];
        } else {
            $apiKey = self::MOB_ASSIST_API_KEY;
        }
        $headers = array('Authorization: key=' . $apiKey, 'Content-Type: application/json');

        $post_data = array(
            'registration_ids' => array($registration_id),
            'data' => array("message" => $message)
        );
        $post_data = json_encode($post_data);

        if (self::DEBUG_MODE) {
            $this->load->model('mobileassistant/helper');
            $this->model_mobileassistant_helper->write_log('PUSH REQUEST DATA: ' . $post_data);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $response = curl_exec($ch);

        $info = curl_getinfo($ch);

        $this->onResponse($setting_id, $response, $info);
    }


    public function onResponse($setting_id, $response, $info)
    {
        $code = $info != null && isset($info['http_code']) ? $info['http_code'] : 0;

        $this->load->model('mobileassistant/helper');

        $codeGroup = (int)($code / 100);
        if ($codeGroup == 5) {
            $this->model_mobileassistant_helper->write_log('PUSH RESPONSE: code: ' . $code . ' :: GCM server not available');
            return;
        }
        if ($code !== 200) {
            $this->model_mobileassistant_helper->write_log('PUSH RESPONSE: code: ' . $code);
            return;
        }
        if (!$response || strlen(trim($response)) == null) {
            $this->model_mobileassistant_helper->write_log('PUSH RESPONSE: null response');
            return;
        }

        if ($response) {
            $json = json_decode($response, true);
            if (!$json) {
                $this->model_mobileassistant_helper->write_log('PUSH RESPONSE: json decode error');
            }
        }

        $failure = isset($json['failure']) ? $json['failure'] : null;
        $canonicalIds = isset($json['canonical_ids']) ? $json['canonical_ids'] : null;

        if ($failure || $canonicalIds) {
            $results = isset($json['results']) ? $json['results'] : array();
            foreach ($results as $result) {
                $newRegId = isset($result['registration_id']) ? $result['registration_id'] : null;
                $error = isset($result['error']) ? $result['error'] : null;
                if ($newRegId) {
                    $this->updatePushRegId($setting_id, $newRegId);

                } else if ($error) {
                    if ($error == 'NotRegistered' || $error == 'InvalidRegistration') {
                        $this->deletePushRegId($setting_id);
                    }
                    $this->model_mobileassistant_helper->write_log('PUSH RESPONSE: error: ' . $error);
                }
            }
        }
    }


    public function updatePushRegId($setting_id, $new_reg_id)
    {
        $sql = "UPDATE " . DB_PREFIX . self::T_PUSH_NOTIFICATIONS . " SET registration_id = '%s' WHERE setting_id = '%d'";
        $sql = sprintf($sql, $new_reg_id, $setting_id);
        $this->db->query($sql);
    }


    public function deletePushRegId($setting_id)
    {
        $sql = "DELETE FROM " . DB_PREFIX . self::T_PUSH_NOTIFICATIONS . " WHERE setting_id = '%d'";
        $sql = sprintf($sql, $setting_id);
        return $this->db->query($sql);
    }


    public function getPushDevices($data = array())
    {
        $column = "status";
        $sql = "SHOW COLUMNS FROM `" . DB_PREFIX . self::T_PUSH_NOTIFICATIONS . "` WHERE `Field` = '".$column."'";
        $q = $this->db->query($sql);
        if(!$q->num_rows) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . self::T_PUSH_NOTIFICATIONS . "` ADD ".$column." INT(1) NOT NULL DEFAULT '1'");
        }

        $action_type = "push_new_order";
        if($data['type'] == self::PUSH_TYPE_CHANGE_ORDER_STATUS) {
            $action_type = "push_order_status_changed";
        } else if($data['type'] == self::PUSH_TYPE_NEW_CUSTOMER) {
            $action_type = "push_new_customer";
        }

        $sql = "SELECT pn.setting_id, pn.registration_id, pn.app_connection_id, pn.push_currency_code
                FROM " . DB_PREFIX . self::T_PUSH_NOTIFICATIONS . " AS pn
                LEFT JOIN `" . DB_PREFIX . self::T_USERS . "` AS u ON u.user_id = pn.user_id
                WHERE u.allowed_actions LIKE '%" . $action_type . "%'";
//                WHERE user_id IN (
//                    SELECT user_id FROM `" . DB_PREFIX . self::T_USERS . "` WHERE allowed_actions LIKE '%" . $action_type . "%')";

        switch ($data['type']) {
            case self::PUSH_TYPE_NEW_ORDER:
                $query_where[] = " pn.push_new_order = '1' ";
                break;

            case self::PUSH_TYPE_CHANGE_ORDER_STATUS:
                $query_where[] = sprintf(" (pn.push_order_statuses = '%s' OR pn.push_order_statuses LIKE '%%|%s' OR pn.push_order_statuses LIKE '%s|%%' OR pn.push_order_statuses LIKE '%%|%s|%%' OR pn.push_order_statuses = '-1') ", $data['status'], $data['status'], $data['status'], $data['status']);
                break;

            case self::PUSH_TYPE_NEW_CUSTOMER:
                $query_where[] = " pn.push_new_customer = '1' ";
                break;

            default:
                return false;
        }

        $query_where[] = sprintf(" (pn.store_id = '-1' OR pn.store_id = '%d') ", $data['store_id']);
        $query_where[] = " pn.status = '1' ";
        $query_where[] = " u.user_status = '1' ";


        if (isset($data['missing_new_order']) && $data['missing_new_order']) {
            $query_where[] = " u.mobassist_disable_mis_ord_notif = '0' ";
        }

        if (isset($data['real_new_order']) && $data['real_new_order']) {
            $query_where[] = " u.mobassist_disable_mis_ord_notif = '1' ";
        }

        if (!empty($query_where)) {
            $sql .= " AND " . implode(" AND ", $query_where);
        }

        if (self::DEBUG_MODE) {
            $this->load->model('mobileassistant/helper');
            $this->model_mobileassistant_helper->write_log('PUSH REQUEST DATA: getPushDevices: ' . $sql);
        }

        $query = $this->db->query($sql);
        $rows = $query->rows;

        return $rows;
    }


//-------//-------//-------//-------//-------

    private function check_module_installed()
    {
        $this->load->model('mobileassistant/setting');
        $s = $this->model_mobileassistant_setting->getSetting('mobassist');

        if ($s && isset($s['mobassist_status']) && $s['mobassist_status'] == 1) {
            return true;
        }
        return false;
    }

    private function get_filter_statuses($statuses)
    {
        $statuses = explode("|", $statuses);
        if (!empty($statuses)) {
            $stat = array();
            foreach ($statuses as $status) {
                if ($status != "") {
                    $stat[] = $status;
                }
            }
            $parse_statuses = implode("','", $stat);
            return $parse_statuses;
        }

        return $statuses;
    }

    private function get_custom_period($period = 0)
    {
        $custom_period = array('start_date' => "", 'end_date' => "");
        $format = "m/d/Y";

        switch ($period) {
            case 0: //3 days
                $custom_period['start_date'] = date($format, mktime(0, 0, 0, date("m"), date("d") - 2, date("Y")));
                $custom_period['end_date'] = date($format, mktime(23, 59, 59, date("m"), date("d"), date("Y")));
                break;

            case 1: //7 days
                $custom_period['start_date'] = date($format, mktime(0, 0, 0, date("m"), date("d") - 6, date("Y")));
                $custom_period['end_date'] = date($format, mktime(23, 59, 59, date("m"), date("d"), date("Y")));
                break;

            case 2: //Prev week
                $custom_period['start_date'] = date($format, mktime(0, 0, 0, date("n"), date("j") - 6, date("Y")) - ((date("N")) * 3600 * 24));
                $custom_period['end_date'] = date($format, mktime(23, 59, 59, date("n"), date("j"), date("Y")) - ((date("N")) * 3600 * 24));
                break;

            case 3: //Prev month
                $custom_period['start_date'] = date($format, mktime(0, 0, 0, date("m") - 1, 1, date("Y")));
                $custom_period['end_date'] = date($format, mktime(23, 59, 59, date("m"), date("d") - date("j"), date("Y")));
                break;

            case 4: //This quarter
                $m = date("n");
                $start_m = 1;
                $end_m = 3;

                if ($m <= 3) {
                    $start_m = 1;
                    $end_m = 3;
                } else if ($m >= 4 && $m <= 6) {
                    $start_m = 4;
                    $end_m = 6;
                } else if ($m >= 7 && $m <= 9) {
                    $start_m = 7;
                    $end_m = 9;
                } else if ($m >= 10) {
                    $start_m = 10;
                    $end_m = 12;
                }

                $custom_period['start_date'] = date($format, mktime(0, 0, 0, $start_m, 1, date("Y")));
                $custom_period['end_date'] = date($format, mktime(23, 59, 59, $end_m + 1, date(1) - 1, date("Y")));
                break;

            case 5: //This year
                $custom_period['start_date'] = date($format, mktime(0, 0, 0, date(1), date(1), date("Y")));
                $custom_period['end_date'] = date($format, mktime(23, 59, 59, date(1), date(1) - 1, date("Y") + 1));
                break;

            case 6: //Last year
                $custom_period['start_date'] = date($format, mktime(0, 0, 0, date(1), date(1), date("Y") - 1));
                $custom_period['end_date'] = date($format, mktime(23, 59, 59, date(1), date(1) - 1, date("Y")));
                break;

            case 8: //Last quarter
                $m = date("n");
                $start_m = 1;
                $end_m = 3;
                $year_offset = 0;

                if ($m <= 3) {
                    $start_m = 10;
                    $end_m = 12;
                    $year_offset = -1;
                } else if ($m >= 4 && $m <= 6) {
                    $start_m = 1;
                    $end_m = 3;
                } else if ($m >= 7 && $m <= 9) {
                    $start_m = 4;
                    $end_m = 6;
                } else if ($m >= 10) {
                    $start_m = 7;
                    $end_m = 9;
                }

                $custom_period['start_date'] = date($format, mktime(0, 0, 0, $start_m, 1, date("Y")));
                $custom_period['end_date'] = date($format, mktime(23, 59, 59, $end_m + 1, date(1) + $year_offset, date("Y")));
                break;
        }

        return $custom_period;
    }

    private function check_version()
    {
        if (class_exists('MijoShop')) {
            $base = MijoShop::get('base');

            $installed_ms_version = (array)$base->getMijoshopVersion();
            $mijo_version = $installed_ms_version[0];
            if (version_compare($mijo_version, '3.0.0', '>=') && version_compare(VERSION, '2.0.0.0', '<')) {
                $this->opencart_version = '2.0.1.0';
            } else {
                $this->opencart_version = VERSION;
            }

        } else {
            $this->opencart_version = VERSION;
        }

        $this->is_ver20 = version_compare($this->opencart_version, '2.0.0.0', '>=');
    }



////////////////////////////////////////////////////////////////////////////////////////////////
    public function clear_old_data() {
        $timestamp = time();
        $date      = date('Y-m-d H:i:s', ($timestamp - self::MAX_LIFETIME));

        $this->load->model('mobileassistant/setting');
        $s = $this->model_mobileassistant_setting->getSetting('mobassist');

        $date_clear_prev = $s['mobassist_cl_date'];

        if ($date_clear_prev === false || ($timestamp - (int)$date_clear_prev) > self::MAX_LIFETIME) {
            $sql = "DELETE FROM `" . DB_PREFIX . self::T_SESSION_KEYS . "` WHERE `date_added` < '%s'";
            $sql = sprintf($sql, $date);
            $this->db->query($sql);

            $sql = "DELETE FROM `" . DB_PREFIX . self::T_FAILED_LOGIN . "` WHERE `date_added` < '%s'";
            $sql = sprintf($sql, $date);
            $this->db->query($sql);

            $s['mobassist_cl_date'] = $timestamp;

            $this->model_mobileassistant_setting->editSetting('mobassist', $s);
        }
    }


    public function get_session_key() {
        $timestamp = time();
        $key = hash(self::HASH_ALGORITHM, $this->module_user['username'] . $timestamp . rand(1111, 99999));

        $column = "user_id";
        $sql = "SHOW COLUMNS FROM `" . DB_PREFIX . self::T_SESSION_KEYS . "` WHERE `Field` = '".$column."'";
        $q = $this->db->query($sql);
        if(!$q->num_rows) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . self::T_SESSION_KEYS . "` ADD ".$column." INT(10) NOT NULL");
        }

        $sql = "INSERT INTO `" . DB_PREFIX . self::T_SESSION_KEYS . "` SET session_key = '%s', date_added = '%s', user_id = '%d'";
        $sql = sprintf($sql, $key, date('Y-m-d H:i:s', $timestamp), $this->module_user['user_id']);

        $this->db->query($sql);

        return $key;
    }


    public function check_session_key($key) {
        if(!$key || empty($key)) {
            return false;
        }

        $timestamp = time();
        $sql = "SELECT `session_key`, user_id FROM `" . DB_PREFIX . self::T_SESSION_KEYS . "` WHERE `session_key` = '%s' AND `date_added` > '%s'";

        if($this->module_user && isset($this->module_user['user_id'])) {
            $sql .= " AND `user_id` = '%d'";
            $sql = sprintf($sql, $key, date('Y-m-d H:i:s', ($timestamp - self::MAX_LIFETIME)), $this->module_user['user_id']);
        } else {
            $sql = sprintf($sql, $key, date('Y-m-d H:i:s', ($timestamp - self::MAX_LIFETIME)));
        }

        $q = $this->db->query($sql);

        if($q->num_rows) {
            if(!$this->module_user) {
                $row = $q->row;
                $this->load->model('mobileassistant/connector');
                $this->module_user = $this->model_mobileassistant_connector->getModuleUser(array("user_id" => $row['user_id']));
            }

            if($this->module_user) {
                if (isset($this->module_user['user_status']) && $this->module_user['user_status'] == 1) {
                    $this->clear_failed_login();
                    return true;
                } else {
                    $this->generate_output('user_disabled');
                    return false;
                }
            }
        }

        $this->add_failed_login();

        return false;
    }


    private function add_failed_login() {
        $timestamp = time();

        $sql = "INSERT INTO `" . DB_PREFIX . self::T_FAILED_LOGIN . "` SET `ip` = '%s', `date_added` = '%s'";
        $sql = sprintf($sql, $_SERVER['REMOTE_ADDR'], date('Y-m-d H:i:s', $timestamp));

        $this->db->query($sql);


        $sql = "SELECT COUNT(`row_id`) AS count_row_id FROM `" . DB_PREFIX . self::T_FAILED_LOGIN . "` WHERE `ip` = '%s' AND `date_added` > '%s'";
        $sql = sprintf($sql, $_SERVER['REMOTE_ADDR'], date('Y-m-d H:i:s', ($timestamp - self::MAX_LIFETIME)));

        $query = $this->db->query($sql);
        if($query->num_rows) {
            $row = $query->row;

            $this->set_delay((int)$row['count_row_id']);
        }
    }


    private function clear_failed_login() {
        $sql = "DELETE FROM  `" . DB_PREFIX . self::T_FAILED_LOGIN . "` WHERE `ip` = '%s'";
        $sql = @sprintf($sql, $_SERVER['REMOTE_ADDR']);

        @$this->db->query($sql);
    }


    private function set_delay($count_attempts) {
        if ($count_attempts > 50) {
            sleep(15);

        } elseif ($count_attempts > 30) {
            sleep(8);

        } elseif ($count_attempts > 20) {
            sleep(5);
        }
    }



    private function map_push_notification_to_device() {
        if (!$this->device_unique_id && !$this->account_email) {
            return;
        }

        $date = date('Y-m-d H:i:s');
        $device_id = 0;

        $sql = "SELECT `device_id` FROM `" . DB_PREFIX . self::T_DEVICES . "` WHERE `device_unique_id` = '%s' AND account_email = '%s' AND user_id = '%d'";
        $sql = sprintf($sql, $this->device_unique_id, $this->account_email, $this->module_user['user_id']);
        $query = $this->db->query($sql);

        if($query->num_rows <= 0) {
            $sql = "INSERT INTO `" . DB_PREFIX . self::T_DEVICES . "` (device_unique_id, account_email, device_name, last_activity, user_id)
			        VALUES ('%s', '%s', '%s', '%s', '%d')";
            $sql = sprintf($sql, $this->device_unique_id, $this->account_email, $this->device_name, $date, $this->module_user['user_id']);

            $this->db->query($sql);

            $device_id = $this->db->getLastId();

        } else {
            $row = $query->row;
            $device_id = $row['device_id'];

            $sql = "UPDATE `" . DB_PREFIX . self::T_DEVICES . "` SET device_name = '%s', last_activity = '%s', user_id = '%d' WHERE device_id = '%d'";
            $sql = sprintf($sql, $this->device_name, $date, $this->module_user['user_id'], $device_id);
            $this->db->query($sql);
        }

        if (!$this->registration_id || $this->call_function == 'delete_push_config') {
            return;
        }

        if($device_id > 0) {
            $column = "device_id";
            $sql = "SHOW COLUMNS FROM `" . DB_PREFIX . self::T_PUSH_NOTIFICATIONS . "` WHERE `Field` = '".$column."'";
            $q = $this->db->query($sql);
            if(!$q->num_rows) {
                $this->db->query("ALTER TABLE `" . DB_PREFIX . self::T_PUSH_NOTIFICATIONS . "` ADD ".$column." INT(10) NOT NULL");
            }

            $column = "user_id";
            $sql = "SHOW COLUMNS FROM `" . DB_PREFIX . self::T_PUSH_NOTIFICATIONS . "` WHERE `Field` = '".$column."'";
            $q = $this->db->query($sql);
            if(!$q->num_rows) {
                $this->db->query("ALTER TABLE `" . DB_PREFIX . self::T_PUSH_NOTIFICATIONS . "` ADD ".$column." INT(10) NOT NULL");
            }

            $column = "status";
            $sql = "SHOW COLUMNS FROM `" . DB_PREFIX . self::T_PUSH_NOTIFICATIONS . "` WHERE `Field` = '".$column."'";
            $q = $this->db->query($sql);
            if(!$q->num_rows) {
                $this->db->query("ALTER TABLE `" . DB_PREFIX . self::T_PUSH_NOTIFICATIONS . "` ADD ".$column." INT(1) NOT NULL DEFAULT '1'");
            }

            if($this->registration_id != '') {
                $sql_upd = "UPDATE " . DB_PREFIX . self::T_PUSH_NOTIFICATIONS . " SET device_id = '%s' WHERE registration_id = '%s' AND user_id = '%d'";
                $sql_upd = sprintf($sql_upd, $device_id, $this->registration_id, $this->module_user['user_id']);
                $this->db->query($sql_upd);
            }
        }
    }

    private function update_device_last_activity() {
        if ($this->device_unique_id) {
            $sql = "UPDATE " . DB_PREFIX . self::T_DEVICES . " SET last_activity = '%s' WHERE device_unique_id = '%s' AND account_email = '%s'";
            $sql = sprintf($sql, date('Y-m-d H:i:s'), $this->device_unique_id, $this->account_email);
            $this->db->query($sql);
        }
    }


    private function _checkUpdateModule() {
        $this->load->model('mobileassistant/setting');
        $s = $this->model_mobileassistant_setting->getSetting('mobassist');

        if (isset($s['mobassist_module_code']) && $s['mobassist_module_code'] < self::MODULE_CODE) {
            $this->load->model('mobileassistant/connector');
            $this->model_mobileassistant_connector->update_module($s);
            $this->model_mobileassistant_connector->reset_events();

            if(!isset($s['mobassist_cl_date'])) $s['mobassist_cl_date'] = 1;

            $settings = array(
                'mobassist_status' => $s['mobassist_status'],
                'mobassist_module_code' => self::MODULE_CODE,
                'mobassist_module_version' => self::MODULE_VERSION,
                'mobassist_cl_date' => $s['mobassist_cl_date']
            );

            $this->model_mobileassistant_setting->editSetting('mobassist', $settings);
        }
    }
}
