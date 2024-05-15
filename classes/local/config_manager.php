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

    public const ALLOWED_CONFIG_KEYS = [
        ''
    ];

    public function __construct(private readonly string $tenant){}


    public function get_config(string $key): false|string {
        global $DB;
        return $DB->get_record('local_ai_manager_config', ['key' => $key, 'tenant' => $this->tenant]);
    }

    public function set_config(string $key, string $value): void {
        global $DB;
        if (!in_array($key, self::ALLOWED_CONFIG_KEYS)) {
            throw new \coding_exception('Key "' . $key . '" is not an allowed config key to be set!');
        }
        $configrecord = $DB->get_record('local_ai_manager_config', ['key' => $key, 'tenant' => $this->tenant]);
        if ($configrecord) {
            $configrecord->value = $value;
            $DB->update_record('local_ai_manager_config', $configrecord);
        } else {
            $configrecord = new \stdClass();
            $configrecord->key = $key;
            $configrecord->value = $value;
            $configrecord->tenant = $this->tenant;
            $DB->insert_record('local_ai_manager_config', $configrecord);
        }
    }

    /**
     * @return string
     */
    public function get_tenant(): string {
        return $this->tenant;
    }

}
