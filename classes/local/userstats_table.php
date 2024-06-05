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
use moodle_url;
use table_sql;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/tablelib.php');

class userstats_table extends table_sql {
    private bool $shownames;

    /**
     * Constructor.
     */
    public function __construct(
        string $uniqid,
        private readonly string $purpose,
        private readonly tenant $tenant,
        moodle_url $baseurl
    ) {
        parent::__construct($uniqid);
        $this->set_attribute('id', $uniqid);
        $this->define_baseurl($baseurl);
        // Define the list of columns to show.
        $columns = ['checkbox', 'lastname', 'firstname', 'locked', 'requestcount'];
        $headers = [
            '',
            get_string('lastname'),
            get_string('firstname'),
            get_string('locked', 'local_ai_manager'),
            get_string('request_count', 'local_ai_manager')
        ];
        if (!empty($purpose)) {
            $columns[] = 'currentusage';
            $headers[] = get_string('token_used', 'local_ai_manager');
        }
        $this->define_columns($columns);
        // Define the titles of columns to show in header.
        $this->define_headers($headers);

        if (!empty($purpose)) {
            $fields = 'u.id as id, lastname, firstname, locked, COUNT(value) AS requestcount, SUM(value) AS currentusage';
            $from =
                '{local_ai_manager_request_log} rl LEFT JOIN {local_ai_manager_userinfo} ui ON rl.userid = ui.userid JOIN {user} u ON u.id = rl.userid';
            $where = 'institution = :tenant AND purpose = :purpose GROUP BY u.id';
            $params = ['tenant' => $this->tenant->get_tenantidentifier(), 'purpose' => $purpose];
            $this->set_count_sql(
                "SELECT COUNT(DISTINCT userid) FROM {local_ai_manager_request_log} rl JOIN {user} u ON rl.userid = u.id "
                    . "WHERE institution = :tenant AND purpose = :purpose",
                ['tenant' => $this->tenant->get_tenantidentifier(), 'purpose' => $purpose]
            );
        } else {
            $fields = 'u.id as id, lastname, firstname, locked, COUNT(value) AS requestcount';
            $from =
                '{user} u LEFT JOIN {local_ai_manager_request_log} rl ON u.id = rl.userid LEFT JOIN {local_ai_manager_userinfo} ui ON u.id = ui.userid';
            $where = 'institution = :tenant GROUP BY u.id';
            $params = ['tenant' => $this->tenant->get_tenantidentifier()];
            $this->set_count_sql(
                "SELECT COUNT(DISTINCT id) FROM {user} WHERE institution = :tenant",
                ['tenant' => $this->tenant->get_tenantidentifier()]
            );
        }
        $this->set_sql($fields, $from, $where, $params);
        parent::setup();

        $this->shownames = has_capability('local/ai_manager:viewusernames', $tenant->get_tenant_context());
    }

    function col_lastname($value) {
        return $this->shownames ? $value->lastname : 'MUSTERMANN';
    }

    function col_firstname($value) {
        return $this->shownames ? $value->firstname : 'MUSTERMANN';
    }

    /**
     * Get the icon representing the lockes state.
     *
     * @param mixed $value
     * @return string
     */
    function col_locked($value) {
        if (empty($value->locked)) {
            return '<i class="fa fa-unlock ai_manager_green"></i>';
        } else {
            return '<i class="fa fa-lock ai_manager_red"></i>';
        }
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
