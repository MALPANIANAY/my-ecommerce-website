<?php


class ModelMobileAssistantSetting extends Model {
    private $is_ver20;
    private $opencart_version;

    public function getSetting($group, $store_id = 0) {
        $this->check_version();

        $group_field = 'group';
        if(version_compare($this->opencart_version, '2.0.1.0', '>=')) {
            $group_field = 'code';
        }

        $data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `".$group_field."` = '" . $this->db->escape($group) . "'");

        foreach ($query->rows as $result) {
            if (!$result['serialized']) {
                $data[$result['key']] = $result['value'];
            } else {
                $data[$result['key']] = unserialize($result['value']);
            }
        }

        return $data;
    }

    public function editSetting($group, $data, $store_id = 0) {
        $this->check_version();

        $group_field = 'group';
        if(version_compare($this->opencart_version, '2.0.1.0', '>=')) {
            $group_field = 'code';
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `".$group_field."` = '" . $this->db->escape($group) . "'");

        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `".$group_field."` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
            } else {
                $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `".$group_field."` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(serialize($value)) . "', serialized = '1'");
            }
        }
    }

    public function deleteSetting($group, $store_id = 0) {
        $this->check_version();

        $group_field = 'group';
        if(version_compare($this->opencart_version, '2.0.1.0', '>=')) {
            $group_field = 'code';
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `".$group_field."` = '" . $this->db->escape($group) . "'");
    }

    public function editSettingValue($group = '', $key = '', $value = '', $store_id = 0) {
        $this->check_version();

        $group_field = 'group';
        if(version_compare($this->opencart_version, '2.0.1.0', '>=')) {
            $group_field = 'code';
        }

        if (!is_array($value)) {
            $this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape($value) . "' WHERE `".$group_field."` = '" . $this->db->escape($group) . "' AND `key` = '" . $this->db->escape($key) . "' AND store_id = '" . (int)$store_id . "'");
        } else {
            $this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape(serialize($value)) . "' WHERE `".$group_field."` = '" . $this->db->escape($group) . "' AND `key` = '" . $this->db->escape($key) . "' AND store_id = '" . (int)$store_id . "', serialized = '1'");
        }
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
}