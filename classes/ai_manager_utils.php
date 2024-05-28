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

/**
 * Base class for connector subplugins.
 *
 * @package    local_ai_manager
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ai_manager_utils {
    public static function get_log_entries(string $component, int $contextid, int $userid = 0, int $itemid = 0): array {
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
        $records = $DB->get_records('local_ai_manager_request_log', $params, 'timecreated DESC');
        return !empty($records) ? $records : [];
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
                . "WHERE 'component' = :component AND 'contextid' = :contextid";
        $max =
                intval($DB->get_field_sql($sql, ['component' => $component, 'contextid' => $contextid]));
        return empty($max) ? 1 : $max + 1;
    }
}
