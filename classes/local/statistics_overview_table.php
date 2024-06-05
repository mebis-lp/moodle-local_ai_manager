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

use moodle_url;
use stdClass;
use table_sql;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/tablelib.php');

class statistics_overview_table extends table_sql {

    /**
     * Constructor.
     */
    public function __construct(
            string $uniqid,
            private readonly tenant $tenant,
            moodle_url $baseurl
    ) {
        parent::__construct($uniqid);
        $this->set_attribute('id', $uniqid);
        $this->define_baseurl($baseurl);
        // Define the list of columns to show.
        $columns = ['modelinfo', 'requestcount', 'usage'];
        $headers = [
                'MODEL',
                get_string('request_count', 'local_ai_manager'),
                'VERBRAUCH',
        ];
        $this->define_columns($columns);
        // Define the titles of columns to show in header.
        $this->define_headers($headers);

        $fields = 'model, modelinfo, COUNT(modelinfo) AS "requestcount", SUM(value) AS "usage"';
        $from = '{local_ai_manager_request_log}';
        $where = '1=1 GROUP BY modelinfo';
        $this->set_sql($fields, $from, $where);
        $this->set_count_sql('SELECT COUNT(DISTINCT modelinfo) FROM {local_ai_manager_request_log}');

        parent::setup();
    }

    /**
     * Get the icon representing the lockes state.
     *
     * @param stdClass $row the data object of the current row
     * @return string the string representation
     */
    function col_usage(stdClass $row): string {
        $connector = \core\di::get(\local_ai_manager\local\connector_factory::class)->get_connector_by_model($row->model);
        if ($connector === null) {
            // This should only be in case we have disabled or removed a connector plugin. In this case we cannot provide a unit.
            return $row->usage;
        }
        return $row->usage . " " . $connector->get_unit()->to_string();
    }

    function other_cols($column, $row) {
        if ($column === 'checkbox') {
            return '<input type="checkbox" data-userid="' . $row->id . '"/>';
        }
    }


}
