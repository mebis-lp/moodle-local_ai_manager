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

/**
 * Class for managing the configuration of tenants.
 *
 * @package    local_ai_manager
 * @copyright  2024, ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class config_manager {

    public function __construct(private readonly tenant $tenant) {
    }

    public function get_config(string $configkey): false|string {
        global $DB;
        return $DB->get_field('local_ai_manager_config', 'configvalue',
                ['configkey' => $configkey, 'tenant' => $this->tenant->get_tenantidentifier()]);
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
    }

    /**
     * @return string
     */
    public function get_tenant(): tenant {
        return $this->tenant;
    }

}
