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
                $maxrequests = $this->get_config($purpose->get_plugin_name() . '_max_requests_basic');
                break;
            case userinfo::ROLE_EXTENDED:
                $maxrequests = $this->get_config($purpose->get_plugin_name() . 'max_requests_basic');
                break;
            case userinfo::ROLE_UNLIMITED:
                $maxrequests = userusage::UNLIMITED_REQUESTS_PER_USER;
                break;
        }
        return $maxrequests !== false ? $maxrequests : 0;

    }

    public function get_max_requests_period(): int {
        $period = $this->get_config('max_requests_period');
        if (!$period) {
            return userusage::MAX_REQUESTS_DEFAULT_PERIOD;
        }
        return $period;
    }

}
