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

namespace local_ai_manager;

use local_ai_manager\local\tenant;
use local_ai_manager\local\userinfo;
use local_ai_manager\local\userusage;
use moodle_url;
use stdClass;

/**
 * Base class for connector subplugins.
 *
 * @package    local_ai_manager
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ai_manager_utils {
    public static function get_log_entries(string $component, int $contextid, int $userid = 0, int $itemid = 0, bool $includedeleted = true): array {
        global $DB;
        $params = [
                'component' => $component,
                'contextid' => $contextid,
        ];
        if (!empty($userid)) {
            $params['userid'] = $userid;
        }
        if (!empty($itemid)) {
            $params['itemid'] = $itemid;
        }
        if (empty($includedeleted)) {
            // The column 'deleted' is defined to have the value 0 by default, so we should be safe to use this as query param.
            $params['deleted'] = 0;
        }
        $records = $DB->get_records('local_ai_manager_request_log', $params, 'timecreated DESC');
        return !empty($records) ? $records : [];
    }

    public static function mark_log_entries_as_deleted(string $component, int $contextid, int $userid = 0, int $itemid = 0): void {
        global $DB;
        $params = [
                'component' => $component,
                'contextid' => $contextid,
        ];
        if (!empty($userid)) {
            $params['userid'] = $userid;
        }
        if (!empty($itemid)) {
            $params['itemid'] = $itemid;
        }
        // We intentionally do this one by one despite maybe not being very efficient to avoid running into transaction size limit
        // on DB layer.
        $rs = $DB->get_recordset('local_ai_manager_request_log', $params, '', 'id, deleted');
        foreach ($rs as $record) {
            $record->deleted = 1;
            $DB->update_record('local_ai_manager_request_log', $record);
        }
        $rs->close();
    }

    public static function itemid_exists(string $component, int $contextid, int $itemid): bool {
        global $DB;
        return $DB->record_exists('local_ai_manager_request_log',
                [
                        'component' => $component,
                        'contextid' => $contextid,
                        'itemid' => $itemid,
                ]);
    }

    public static function get_next_free_itemid(string $component, int $contextid): int {
        global $DB;
        $sql = "SELECT MAX(itemid) as maxitemid FROM {local_ai_manager_request_log} "
                . "WHERE component = :component AND contextid = :contextid";
        $max =
                intval($DB->get_field_sql($sql, ['component' => $component, 'contextid' => $contextid]));
        return empty($max) ? 1 : $max + 1;
    }

    public static function get_connector_instance_by_purpose(string $purpose, int $userid = null): base_instance {
        if (is_null($userid)) {
            $tenant = \core\di::get(tenant::class);
        } else {
            $user = \core_user::get_user($userid);
            $tenant = new tenant($user->institution);
            \core\di::set(tenant::class, $tenant);
        }
        $factory = \core\di::get(\local_ai_manager\local\connector_factory::class);
        return $factory->get_connector_instance_by_purpose($purpose);
    }

    public static function get_ai_config(stdClass $user): array {
        $configmanager = \core\di::get(\local_ai_manager\local\config_manager::class);
        $tenant = \core\di::get(tenant::class);
        $userinfo = new userinfo($user->id);
        $purposes = [];
        $purposeconfig = $configmanager->get_purpose_config();
        $factory = \core\di::get(\local_ai_manager\local\connector_factory::class);
        foreach (base_purpose::get_all_purposes() as $purpose) {
            $purposeinstance = $factory->get_purpose_by_purpose_string($purpose);
            $userusage = new userusage($purposeinstance, $user->id);
            $purposes[] = [
                    'purpose' => $purpose,
                    'isconfigured' => !empty($purposeconfig[$purpose]),
                    'limitreached' => $userusage->get_currentusage() >=
                            $configmanager->get_max_requests($purposeinstance, $userinfo->get_role()),
                    'lockedforrole' => $configmanager->get_max_requests($purposeinstance, $userinfo->get_role()) === 0,
            ];
        }

        $tools = [];
        foreach (\local_ai_manager\plugininfo\aitool::get_enabled_plugins() as $toolname) {
            $tool['name'] = $toolname;
            $addurl = new moodle_url('/local/ai_manager/edit_instance.php',
                    [
                            'tenant' => $tenant->get_tenantidentifier(),
                            'returnurl' => (new moodle_url('/local/ai_manager/tenant_config.php',
                                    ['tenant' => $tenant->get_tenantidentifier()]))->out(),
                            'connectorname' => $toolname
                    ]);
            $tool['addurl'] = $addurl->out(false);
            $tools[] = $tool;
        }

        return [
                'tenantenabled' => $configmanager->is_tenant_enabled(),
                'userlocked' => $userinfo->is_locked(),
                'role' => userinfo::get_role_as_string($userinfo->get_role()),
                'purposes' => $purposes,
                'tools' => $tools,
        ];
    }
}
