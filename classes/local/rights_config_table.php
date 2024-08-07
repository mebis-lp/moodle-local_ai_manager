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

use html_writer;
use local_ai_manager\hook\usertable_extend;
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
            $filterids,
    ) {
        parent::__construct($uniqid);
        $this->set_attribute('id', $uniqid);
        $this->define_baseurl($baseurl);
        // Define the list of columns to show.
        $columns = ['checkbox', 'lastname', 'firstname', 'role', 'locked', 'confirmed'];
        $checkboxheader = html_writer::div('', 'rights-table-selection_info', ['id' => 'rights-table-selection_info']);
        $checkboxheader .= html_writer::empty_tag('input', ['type' => 'checkbox', 'id' => 'rights-table-selectall_checkbox']);
        $headers = [
                $checkboxheader,
                get_string('lastname'),
                get_string('firstname'),
                get_string('role', 'local_ai_manager'),
                get_string('locked', 'local_ai_manager'),
                get_string('confirmed', 'local_ai_manager'),
        ];

        $tenantfield = get_config('local_ai_manager', 'tenantcolumn');

        $fields = 'u.id as id, lastname, firstname, role, locked, ui.confirmed';
        $from =
                '{user} u LEFT JOIN {local_ai_manager_userinfo} ui ON u.id = ui.userid';
        $where = 'u.deleted != 1 AND u.suspended != 1 AND ' . $tenantfield . ' = :tenant';
        $params = ['tenant' => $this->tenant->get_identifier()];

        $usertableextend = new usertable_extend($tenant, $columns, $headers, $filterids, $fields, $from, $where, $params);
        \core\di::get(\core\hook\manager::class)->dispatch($usertableextend);

        $this->define_columns($usertableextend->get_columns());
        // Define the titles of columns to show in header.
        $this->define_headers($usertableextend->get_headers());

        $this->no_sorting('checkbox');
        $this->collapsible(false);

        $this->set_count_sql(
                "SELECT COUNT(DISTINCT id) FROM {user} WHERE " . $tenantfield . " = :tenant",
                ['tenant' => $this->tenant->get_identifier()]
        );

        $this->set_sql($usertableextend->get_fields(), $usertableextend->get_from(),
                $usertableextend->get_where() . ' GROUP BY u.id',
                $usertableextend->get_params());
        parent::setup();
    }

    /**
     * Convert the role identifier to a display name.
     */
    function col_role($value) {
        $role = $value->role;
        if (empty($role)) {
            $userinfo = new userinfo($value->id);
            $role = $userinfo->get_default_role();
        }
        return get_string(userinfo::get_role_as_string($role), 'local_ai_manager');
    }

    /**
     * Get the icon representing the lockes state.
     *
     * @param mixed $value
     * @return string
     */
    function col_locked($value) {
        if (empty($value->locked)) {
            return '<i class="fa fa-unlock local_ai_manager-green"></i>';
        } else {
            return '<i class="fa fa-lock local_ai_manager-red"></i>';
        }
    }

    /**
     * Get the icon representing the user confirmed state.
     *
     * @param mixed $value
     * @return string
     */
    function col_confirmed($value) {
        if (!empty($value->confirmed)) {
            return '<i class="fa fa-unlock local_ai_manager-green"></i>';
        } else {
            return '<i class="fa fa-lock local_ai_manager-red"></i>';
        }
    }

    function other_cols($column, $row) {
        if ($column === 'checkbox') {
            return '<input type="checkbox" data-userid="' . $row->id . '"/>';
        }
        return null;
    }

    function show_hide_link($column, $index) {
        if ($column === 'checkbox') {
            return '';
        }
        return parent::show_hide_link($column, $index);
    }

}
