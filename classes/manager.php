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
 * Helper
 *
 * @package    local_ai_manager
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_manager;

use dml_exception;
use stdClass;

/**
 * Helper
 *
 * @package    local_ai_manager
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {

    /** @var Toolconnector object */
    var $toolconnector;

    /**
     * Constructor of manager class.
     *
     * @param string $purpose
     * @param string $usetool
     * @return string|void
     * @throws dml_exception
     */
    public function __construct(string $purpose, string $usetool = '') {

        if (!empty($usetool)) {
            $tool = $usetool;
        }

        if (empty($usetool)) {
            $tool = self::get_default_tool($purpose);
        }

        $classname = "\\aitool_" . $tool . "\\connector";
        if (!class_exists($classname)) {
            return "Class '\aitool_" . $tool . "\connector' is missing in tool " . $tool;
        }
        \local_debugger\performance\debugger::print_debug('test', 'make_request constructor', [$purpose, $tool, $classname]);
        $this->toolconnector = new $classname();
    }

    public static function get_default_tool(string $purpose): string {
        return get_config('local_ai_manager', 'default_' . $purpose);
    }

    /**
     * Get the completion of the LLM.
     *
     * @param string $prompttext The prompt text.
     * @param object $options Options to be used during processing.
     * @return string The generated completion.
     */
    public function make_request(string $prompttext, object $options = null): string {

        if ($options === null) {
            $options = new \stdClass();
        }
        \local_debugger\performance\debugger::print_debug('test', 'make_request options', $options);

        $result = $this->toolconnector->prompt_completion($prompttext, $options);

        if (is_array($result)) {
            $result = json_encode($result);
        }

        return $result;
    }

    public static function log_request(int $prompttoken, int $completiontoken, int $tokentotal, string $model) {
        global $DB, $USER;

        $data = new stdClass();
        $data->userid = $USER->id;
        $data->prompttoken = $prompttoken;
        $data->completiontoken = $completiontoken;
        $data->tokentotal = $tokentotal;
        $data->model = $model;
        $data->timecreated = time();

        $DB->insert_record('local_ai_manager_request_log',$data);

    }
}
