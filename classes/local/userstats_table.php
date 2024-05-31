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

use flexible_table;
use html_writer;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require $CFG->libdir . '/tablelib.php';

class userstats_table extends flexible_table {

    /**
     * Constructor.
     */
    public function __construct(private readonly string $purpose, private readonly tenant $tenant) {
        global $CFG;
        parent::__construct('userusage-' . $purpose);
        $this->define_baseurl($CFG->wwwroot . '/local/ai_manager/statistics.php');
        // Define the list of columns to show.
        $columns = ['checkbox', 'lastname', 'firstname', 'locked', 'currentusage'];
        $this->define_columns($columns);

        // Define the titles of columns to show in header.
        $headers = ['', 'NACHNAME', 'VORNAME', 'GESPERRT', 'ANZAHL REQUESTS'];
        $this->define_headers($headers);
        $this->pageable(true);
        $this->pagesize(5, 20);
        parent::setup();

    }

    function col_locked($value) {
        return empty($value->locked) ? 'GRUENER HAKEN' : 'ROTES KREUZ';

    }

    function other_cols($column, $row) {
        if ($column === 'checkbox') {
            return '<input type="checkbox" data-userid="' . $row->id . '"/>';
        }
    }





    ///**
    // * Constructor
    // * @param int $uniqueid all tables have to have a unique id, this is used
    // *      as a key when storing table properties like sort order in the session.
    // */
    //function __construct($uniqueid, string $purpose, tenant $tenant) {
    //    parent::__construct($uniqueid);
    //    // Define the list of columns to show.
    //    $columns = ['checkbox', 'lastname', 'firstname', 'locked', 'currentusage'];
    //    $this->define_columns($columns);
    //
    //    // Define the titles of columns to show in header.
    //    $headers = ['', 'NACHNAME', 'VORNAME', 'GESPERRT', 'ANZAHL REQUESTS'];
    //    $this->define_headers($headers);
    //    // TODO Could become tricky when trying to implement this generally without forcing "institution".
    //    $this->set_sql('u.id, lastname, firstname, locked, sum', '{local_ai_manager_userinfo} ui JOIN {user} u ON u.id = ui.userid JOIN '
    //    . '(SELECT userid, SUM(value) as sum FROM {local_ai_manager_request_log} WHERE purpose = :purpose GROUP BY userid) uu ON u.id = uu.userid', 'institution = ' . $tenant->get_tenantidentifier(), ['purpose' => $purpose]);
    //
    //    $this->sortable(true, 'lastname');
    //    $this->text_sorting('lastname');
    //    $this->text_sorting('firstname');
    //    $this->text_sorting('locked');
    //}
    //
    ///**
    // * This function is called for each data row to allow processing of the
    // * username value.
    // *
    // * @param object $values Contains object with all the values of record.
    // * @return $string Return username with link to profile or username only
    // *     when downloading.
    // */
    //function col_username($values) {
    //    return $values->username;
    //}
    //
    ///**
    // * This function is called for each data row to allow processing of
    // * columns which do not have a *_cols function.
    // * @return string return processed value. Return NULL if no change has
    // *     been made.
    // */
    //function other_cols($colname, $value) {
    //    // For security reasons we don't want to show the password hash.
    //    if ($colname === 'currentusage') {
    //        return rand();
    //    } else if ($colname === 'checkbox') {
    //        return '<input type="checkbox" data-userid="' . $value->id . '"/>';
    //    } else if ($colname === 'locked') {
    //        return empty($value->locked) ? 'GRUENER HAKEN' : 'ROTES KREUZ';
    //    }
    //}

}
