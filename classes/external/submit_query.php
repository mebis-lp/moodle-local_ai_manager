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

namespace local_ai_manager\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use stdClass;

/**
 * Web service to submit a query to an AI tool.
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
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'purpose' => new external_value(PARAM_TEXT, 'The purpose of the prompt.', VALUE_REQUIRED),
            'prompt' => new external_value(PARAM_TEXT, 'The prompt', VALUE_REQUIRED),
            'options' => new external_value(PARAM_TEXT, 'Options array', VALUE_DEFAULT, []),
            'contextid' => new external_value(PARAM_INT, 'Context id for the call', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Execute the service.
     *
     * @param string $purpose the purpose to use
     * @param string $prompt the user's prompt
     * @param array $options additional options which should be passed to the request to the AI tool
     * @return array associative array containing the result of the request
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
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/ai_manager:use_ai_manager', $context);

        try {
            if (!empty($options)) {
                $options = json_decode($options, true);
            }
            $aimanager = new \local_ai_manager\manager($purpose);
            //\core\di::set('\local_ai_manager\manager', $aimanager);

            $result = $aimanager->perform_request($prompt, $options);

            if ($result->is_error()) {
                // TODO Eventually also use debuginfo or remove debuginfo completely also from prompt_reponse class
                $return = ['code' => 204, 'string' => 'error', 'result' => $result->get_errormessage()];
            } else {
                $return = ['code' => 200, 'string' => 'ok', 'result' => $result->get_content()];
            }
        } catch (\Exception $e) {

            $return = ['code' => 500, 'string' => 'error', 'result' => $e->getMessage()];
        }
        return $return;
    }

    /**
     * Describes the return structure of the service.
     *
     * @return external_single_structure the return structure
     */
    public static function execute_returns(): external_single_structure {
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
