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
use local_bycsauth\idmgroup;
use moodle_url;
use table_sql;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/tablelib.php');

class rights_config_table extends table_sql {
    /**
     * Constructor.
     */
    public function __construct(
            string $uniqid,
            private readonly tenant $tenant,
            moodle_url $baseurl,
            $filteridmgroups,
    ) {
        global $DB;
        parent::__construct($uniqid);
        $this->set_attribute('id', $uniqid);
        $this->define_baseurl($baseurl);
        // Define the list of columns to show.
        $columns = ['checkbox', 'lastname', 'firstname', 'idmgroupnames', 'locked'];
        $headers = [
                '<input type="checkbox" id="rights-table-selectall_checkbox"/>',
                get_string('lastname'),
                get_string('firstname'),
                get_string('department'),
                get_string('locked', 'local_ai_manager'),
        ];

        $this->define_columns($columns);
        // Define the titles of columns to show in header.
        $this->define_headers($headers);

        $this->no_sorting('checkbox');
        // TODO implement filter
        if (empty($filteridmgroups)) {
            $filtersql = '';
            $filtersqlparams = [];
        } else {
            [$filtersql, $filtersqlparams] = $DB->get_in_or_equal($filteridmgroups, SQL_PARAMS_NAMED);
            $filtersql = 'AND bag.id ' . $filtersql;
        }

        $sqlgroupconcat = $DB->sql_group_concat('name', ', ', 'name ASC');
        $fields = 'u.id as id, lastname, firstname, ' . $sqlgroupconcat . ' AS idmgroupnames, locked';
        $from =
                '{user} u LEFT JOIN {local_ai_manager_userinfo} ui ON u.id = ui.userid'
                    . ' LEFT JOIN {local_bycsauth_membership} bam ON u.id = bam.userid'
                    . ' LEFT JOIN {local_bycsauth_idmgroup} bag ON bam.idmgroupid = bag.id';
        $where = 'institution = :tenant AND (bag.idmgrouptype = :idmgrouptype OR bag.idmgrouptype IS NULL) ' . $filtersql . ' GROUP BY u.id';
        $params = ['tenant' => $this->tenant->get_tenantidentifier(), 'idmgrouptype' => idmgroup::IDM_GROUP_TYPE['class']];
        $params = array_merge($params, $filtersqlparams);
        $this->set_count_sql(
                "SELECT COUNT(DISTINCT id) FROM {user} WHERE institution = :tenant",
                ['tenant' => $this->tenant->get_tenantidentifier()]
        );

        $this->set_sql($fields, $from, $where, $params);
        parent::setup();
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
        return null;
    }

}
