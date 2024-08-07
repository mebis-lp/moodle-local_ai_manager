<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_ai_manager\local;

use local_ai_manager\base_purpose;

/**
 * Class for managing the configuration of tenants.
 *
 * @package    local_ai_manager
 * @copyright  2024, ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class config_manager {

    private array $config = [];

    // TODO Add all keys with a separate getter
    private function get_separate_getter_config_keys(): array {
        $keys = [];
        foreach (base_purpose::get_all_purposes() as $purpose) {
            $keys[] = $purpose . '_max_requests_basic';
            $keys[] = $purpose . '_max_requests_extended';
        }
        $keys[] = 'max_requests_period';
        $keys[] = 'tenantenabled';
        return $keys;
    }

    public function __construct(private readonly tenant $tenant) {
        $this->load_config();
    }

    private function load_config(): void {
        global $DB;
        if (empty($this->tenant->get_tenantidentifier())) {
            $this->config = [];
            return;
        }
        $records = $DB->get_records('local_ai_manager_config', ['tenant' => $this->tenant->get_tenantidentifier()]);
        foreach ($records as $record) {
            $this->config[$record->configkey] = $record->configvalue;
        }
    }

    public function get_config(string $configkey): false|string {
        if (in_array($configkey, $this->get_separate_getter_config_keys())) {
            throw new \coding_exception('You must not access this config key directly. Please use the separate getter function.');
        }
        if (!array_key_exists($configkey, $this->config)) {
            return false;
        }
        return $this->config[$configkey];
    }

    public function unset_config(string $configkey): void {
        global $DB;
        if (empty($this->tenant->get_tenantidentifier())) {
            return;
        }
        $DB->delete_records('local_ai_manager_config',
                [
                        'tenant' => $this->tenant->get_tenantidentifier(),
                        'configkey' => $configkey,
                ]
        );
    }

    public function get_purpose_config(): array {
        $purposeconfig = [];
        foreach (base_purpose::get_all_purposes() as $purpose) {
            if (array_key_exists(base_purpose::get_purpose_tool_config_key($purpose), $this->config)) {
                $purposeconfig[$purpose] = $this->config[base_purpose::get_purpose_tool_config_key($purpose)];
            } else {
                $purposeconfig[$purpose] = null;
            }
        }
        return $purposeconfig;
    }

    public function set_config(string $configkey, string $configvalue): void {
        global $DB;
        // TODO Eventually do a validation of which config keys are allowed
        $configrecord = $DB->get_record('local_ai_manager_config',
                ['configkey' => $configkey, 'tenant' => $this->tenant->get_tenantidentifier()]);
        if ($configrecord) {
            $configrecord->configvalue = $configvalue;
            $DB->update_record('local_ai_manager_config', $configrecord);
        } else {
            $configrecord = new \stdClass();
            $configrecord->configkey = $configkey;
            $configrecord->configvalue = $configvalue;
            $configrecord->tenant = $this->tenant->get_tenantidentifier();
            $DB->insert_record('local_ai_manager_config', $configrecord);
        }
        $this->load_config();
    }

    /**
     * @return string
     */
    public function get_tenant(): tenant {
        return $this->tenant;
    }

    public function get_max_requests(base_purpose $purpose, int $role): int {
        $maxrequests = false;
        switch ($role) {
            case userinfo::ROLE_BASIC:
                $maxrequests = $this->get_max_requests_raw($purpose, userinfo::ROLE_BASIC);
                if ($maxrequests === false) {
                    $maxrequests = userusage::MAX_REQUESTS_DEFAULT_ROLE_BASE;
                }
                break;
            case userinfo::ROLE_EXTENDED:
                $maxrequests = $this->get_max_requests_raw($purpose, userinfo::ROLE_EXTENDED);
                if ($maxrequests === false) {
                    $maxrequests = userusage::MAX_REQUESTS_DEFAULT_ROLE_EXTENDED;
                }
                break;
            case userinfo::ROLE_UNLIMITED:
                $maxrequests = userusage::UNLIMITED_REQUESTS_PER_USER;
                break;
        }
        return $maxrequests;
    }

    public function get_max_requests_raw(base_purpose $purpose, int $role): int|false {
        $rolesuffix = '';
        switch ($role) {
            case userinfo::ROLE_BASIC:
                $rolesuffix = 'basic';
                break;
            case userinfo::ROLE_EXTENDED:
                $rolesuffix = 'extended';
        }
        $configkey = $purpose->get_plugin_name() . '_max_requests_' . $rolesuffix;
        if (!array_key_exists($configkey, $this->config)) {
            return false;
        }
        return intval($this->config[$configkey]);
    }

    public function get_max_requests_period(): int {
        if (!array_key_exists('max_requests_period', $this->config)) {
            return userusage::MAX_REQUESTS_DEFAULT_PERIOD;
        }
        return $this->config['max_requests_period'];
    }

    public function is_tenant_enabled(): bool {
        if (!array_key_exists('tenantenabled', $this->config)) {
            return false;
        }
        if (!$this->tenant->is_tenant_allowed()) {
            return false;
        }
        return $this->config['tenantenabled'];
    }

}
