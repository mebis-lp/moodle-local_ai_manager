<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Upgrade functions for local_ai_manager.
 *
 * @package   local_ai_manager
 * @copyright 2024 ISB Bayern
 * @author    Philipp Memmel
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define upgrade steps to be performed to upgrade the plugin from the old version to the current one.
 *
 * @param int $oldversion Version number the plugin is being upgraded from.
 */
function xmldb_local_ai_manager_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2024080101) {
        $table = new xmldb_table('local_ai_manager_instance');
        $field = new xmldb_field('customfield5', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'customfield4');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2024080101, 'local', 'ai_manager');
    }

    if ($oldversion < 2024080900) {

        // Changing precision of field duration on table local_ai_manager_request_log to (20, 3).
        $table = new xmldb_table('local_ai_manager_request_log');
        $field = new xmldb_field('duration', XMLDB_TYPE_NUMBER, '20, 3', null, null, null, null, 'modelinfo');

        // Launch change of precision for field duration.
        $dbman->change_field_precision($table, $field);

        // Ai_manager savepoint reached.
        upgrade_plugin_savepoint(true, 2024080900, 'local', 'ai_manager');
    }

    if ($oldversion < 2024091800) {
        $table = new xmldb_table('local_ai_manager_request_log');
        $field = new xmldb_field('connector', XMLDB_TYPE_CHAR, '50', null, null, null, null, 'purpose');

        // Conditionally launch add field connector.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Migrate existing records.
        $rs = $DB->get_recordset('local_ai_manager_request_log');
        foreach ($rs as $record) {
            if ($record->model === 'preconfigured') {
                if ($record->purpose === 'tts') {
                    $record->model = 'openaitts_preconfigured_azure';
                    $record->modelinfo = 'openaitts_preconfigured_azure';
                } else if ($record->purpose === 'imggen') {
                    $record->model = 'dalle_preconfigured_azure';
                    $record->modelinfo = 'dalle_preconfigured_azure';
                } else {
                    $record->model = 'chatgpt_preconfigured_azure';
                }
            }
            if ($record->purpose === 'tts') {
                if ($record->model === 'openaitts_preconfigured_azure' || $record->model === 'tts-1') {
                    $record->connector = 'openaitts';
                } else {
                    $record->connector = 'googlesynthesize';
                }
            } else if ($record->purpose === 'imggen') {
                $record->connector = 'dalle';
            } else {
                // We have a text based language model.
                if (str_starts_with($record->model, 'gemini-')) {
                    $record->connector = 'gemini';
                } else if (str_starts_with($record->model, 'gpt-') || $record->model === 'chatgpt_preconfigured_azure') {
                    $record->connector = 'chatgpt';
                } else {
                    $record->connector = 'ollama';
                }
            }
            $DB->update_record('local_ai_manager_request_log', $record);
        }
        $rs->close();

        $rs = $DB->get_recordset('local_ai_manager_instance');
        foreach ($rs as $record) {
            if ($record->model === 'preconfigured') {
                if ($record->connector === 'chatgpt') {
                    $record->model = 'chatgpt_preconfigured_azure';
                } else if ($record->connector === 'openaitts') {
                    $record->model = 'openaitts_preconfigured_azure';
                } else if ($record->connector === 'dalle') {
                    $record->model = 'dalle_preconfigured_azure';
                }
            }
            $DB->update_record('local_ai_manager_instance', $record);
        }

        $rs->close();

        upgrade_plugin_savepoint(true, 2024091800, 'local', 'ai_manager');
    }
    return true;
}
