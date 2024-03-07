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

/**
 * local_ai_manager upgrade related helper functions
 *
 * @package    local_ai_manager
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Create table local_ai_manager_request_log.
 * @return void
 */
function create_local_ai_manager_request_log(): void {
    global $DB;
    $dbman = $DB->get_manager();
    $table = new xmldb_table('local_ai_manager_request_log');

    // Define table local_ai_manager_request_log to be created.
    $table = new xmldb_table('local_ai_manager_request_log');

    // Adding fields to table local_ai_manager_request_log.
    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('prompttoken', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
    $table->add_field('completiontoken', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
    $table->add_field('tokentotal', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
    $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
    $table->add_field('model', XMLDB_TYPE_CHAR, '255', null, null, null, null);

    // Adding keys to table local_ai_manager_request_log.
    $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

    // Adding indexes to table local_ai_manager_request_log.
    $table->add_index('userid_tokentotal', XMLDB_INDEX_NOTUNIQUE, ['userid', 'tokentotal']);
    $table->add_index('model', XMLDB_INDEX_NOTUNIQUE, ['model']);

    // Conditionally launch create table for local_ai_manager_request_log.
    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }
}
