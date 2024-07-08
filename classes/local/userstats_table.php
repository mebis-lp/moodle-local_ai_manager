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
        $columns = ['lastname', 'firstname'];
        $headers = [
            get_string('lastname'),
            get_string('firstname'),
        ];
        if (has_capability('local/ai_manager:viewusage', $tenant->get_tenant_context())) {
            $columns[] = 'requestcount';
            $headers[] = get_string('request_count', 'local_ai_manager');
            if (!empty($purpose)) {
                $columns[] = 'currentusage';
                $headers[] = get_string('token_used', 'local_ai_manager');
            }
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
            $fields = 'u.id as id, lastname, firstname, COUNT(value) AS requestcount';
            $from =
                '{user} u LEFT JOIN {local_ai_manager_request_log} rl ON u.id = rl.userid';
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

}
