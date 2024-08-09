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
use stdClass;
use table_sql;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/tablelib.php');

/**
 * Table class representing the table showing the user statistics.
 *
 * @package    local_ai_manager
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class userstats_table extends table_sql {

    /** @var bool if the names of the user should be shown (otherwise they will be anonymized). */
    private bool $shownames;

    /**
     * Constructor.
     *
     * @param string $uniqid a unique id for the table to use
     * @param string $purpose the purpose identifier to create the table for
     * @param tenant $tenant the tenant for which the table should be created
     * @param moodle_url $baseurl the base url where this table is being rendered
     */
    public function __construct(
            string $uniqid,
            string $purpose,
            tenant $tenant,
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
        if (has_capability('local/ai_manager:viewusage', $tenant->get_context())) {
            $columns[] = 'requestcount';
            $headers[] = get_string('request_count', 'local_ai_manager');
            if (!empty($purpose)) {
                // phpcs:disable moodle.Commenting.TodoComment.MissingInfoInline
                /* TODO This is actually a bad thing: When designing the structure we did not think of the case that for a purpose
                    there can be multiple connectors with different units.
                    So we must not use the unit of the current connector here, but have to use the connectors of each single record
                    before aggregating. This is somehow tough. Probably we need to fall back to the case that we cannot show any
                    information at all, if there are connectors with different units.
                */
                // phpcs:enable moodle.Commenting.TodoComment.MissingInfoInline
                $connector = \core\di::get(\local_ai_manager\local\connector_factory::class)->get_connector_by_purpose($purpose);
                if (!empty($connector) && $connector->get_unit() !== unit::COUNT) {
                    $columns[] = 'currentusage';
                    $headers[] = $connector->get_unit()->to_string();
                }
            }
        }

        $this->define_columns($columns);
        // Define the titles of columns to show in header.
        $this->define_headers($headers);
        $this->collapsible(false);

        $tenantfield = get_config('local_ai_manager', 'tenantcolumn');
        if (!empty($purpose)) {
            $fields = 'u.id as id, lastname, firstname, locked, COUNT(value) AS requestcount, SUM(value) AS currentusage';
            $from = '{local_ai_manager_request_log} rl LEFT JOIN {local_ai_manager_userinfo} ui ON rl.userid = ui.userid'
                    . ' JOIN {user} u ON u.id = rl.userid';
            $where = $tenantfield . ' = :tenant AND purpose = :purpose GROUP BY u.id';
            $params = ['tenant' => $tenant->get_identifier(), 'purpose' => $purpose];
            $this->set_count_sql(
                    "SELECT COUNT(DISTINCT userid) FROM {local_ai_manager_request_log} rl JOIN {user} u ON rl.userid = u.id "
                    . "WHERE " . $tenantfield . " = :tenant AND purpose = :purpose",
                    ['tenant' => $tenant->get_identifier(), 'purpose' => $purpose]
            );
        } else {
            $fields = 'u.id as id, lastname, firstname, COUNT(value) AS requestcount';
            $from =
                    '{user} u LEFT JOIN {local_ai_manager_request_log} rl ON u.id = rl.userid';
            $where = $tenantfield . ' = :tenant GROUP BY u.id';
            $params = ['tenant' => $tenant->get_identifier()];
            $this->set_count_sql(
                    "SELECT COUNT(DISTINCT id) FROM {user} WHERE " . $tenantfield . " = :tenant",
                    ['tenant' => $tenant->get_identifier()]
            );
        }
        $this->set_sql($fields, $from, $where, $params);
        parent::setup();

        $this->shownames = has_capability('local/ai_manager:viewusernames', $tenant->get_context());
    }

    /**
     * Get the eventually anonymized last name of the user.
     *
     * @param stdClass $value the object containing the information of the current row
     * @return string the resulting string for the lastname column
     */
    public function col_lastname(stdClass $value): string {
        return $this->shownames ? $value->lastname : get_string('anonymized', 'local_ai_manager');
    }

    /**
     * Get the eventually anonymized first name of the user.
     *
     * @param stdClass $value the object containing the information of the current row
     * @return string the resulting string for the firstname column
     */
    public function col_firstname(stdClass $value): string {
        return $this->shownames ? $value->firstname : get_string('anonymized', 'local_ai_manager');
    }

    /**
     * Get the string representation of the current usage.
     *
     * @param stdClass $value the object containing the information of the current row
     * @return string the resulting string for the lastname column
     */
    public function col_currentusage(stdClass $value): string {
        return strval(intval($value->currentusage));
    }
}
