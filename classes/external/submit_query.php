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
 * Web service to submit a query and retrieve the result.
 *
 * @package    local_ai_manager
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_manager\external;

use external_api;
use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/externallib.php');

/**
 * Web service to get a state.
 *
 * @package    local_ai_manager
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class submit_query extends external_api {
    /**
     * Describes the parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'purpose' => new external_value(PARAM_TEXT, 'The purpose of the promt.', VALUE_REQUIRED),
            'prompt' => new external_value(PARAM_RAW, 'The prompt', VALUE_REQUIRED),
            'options' => new external_value(PARAM_RAW, 'Options array', VALUE_REQUIRED),
        ]);
    }

    /**
     * Execute the service.
     *
     * @param string $purpose
     * @param string $prompt
     * @param string $options
     * @return array
     */
    public static function execute(string $purpose, string $prompt, string $options): array {

        [
            'purpose' => $purpose,
            'prompt' => $prompt,
            'options' => $options,
        ] = self::validate_parameters(self::execute_parameters(), [
            'purpose' => $purpose,
            'prompt' => $prompt,
            'options' => $options,
        ]);

        try {
            $aimanager = new \local_ai_manager\manager($purpose);
            $options = json_decode($options);
            \local_debugger\performance\debugger::print_debug('test','submit_query execute',$options);
            if (empty($options)) {
                $options = new \stdClass();
            }
            $result = $aimanager->make_request($prompt, $options);

            if (!empty(json_decode($result, true)['error'])) {
                $return = ['code' => 204, 'string' => 'error', 'result' => json_decode($result, true)['error']['message']];

            } else {
                $return = ['code' => 200, 'string' => 'ok', 'result' => $result];
            }
        } catch (\Exception $e) {

            $return = ['code' => 500, 'string' => 'error', 'result' => $e->getMessage()];
        }
        return $return;
    }

    /**
     * Describes the return structure of the service..
     *
     * @return external_multiple_structure
     */
    public static function execute_returns() {
        return new external_single_structure(
            [
                'code' => new external_value(PARAM_INT, 'Return code of process.'),
                'string' => new external_value(PARAM_TEXT, 'Return string of process.'),
                'result' => new external_value(PARAM_RAW, 'The query result'),
            ],
            'Result of a query'
        );
    }
}
