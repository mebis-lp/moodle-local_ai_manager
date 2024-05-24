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

use core_plugin_manager;
use dml_exception;
use local_ai_manager\local\config_manager;
use local_ai_manager\local\prompt_response;
use local_ai_manager\local\userinfo;
use stdClass;
use function Sabre\Event\Loop\instance;

/**
 * Helper
 *
 * @package    local_ai_manager
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {

    /** @var base_connector $toolconnector The tool connector object. */
    private base_connector $toolconnector;

    /**
     * Constructor of manager class.
     *
     * @param string $purpose
     * @param string $tooltouse
     * @return string|void
     * @throws dml_exception
     */
    public function __construct(string $purpose, private readonly array $options = []) {

        // This will automatically inject the tenant object which will default to the institution of the current user.
        $configmanager = \core\di::get(\local_ai_manager\local\config_manager::class);
        $instanceid = $configmanager->get_config(base_purpose::get_purpose_tool_config_key($purpose));
        self::setup_connector_and_instance($instanceid);
        $connectorname = \core\di::get(\local_ai_manager\connector_instance::class)->get_connector();
        $this->toolconnector = \core\di::get('\\aitool_' . $connectorname . '\\connector');
    }

    public static function get_default_tool(string $purpose): string {
        return get_config('local_ai_manager', 'default_' . $purpose);
    }

    public static function get_tools_for_purpose(string $purpose): array {
        $tools = [];
        foreach (core_plugin_manager::instance()->get_enabled_plugins('aitool') as $tool) {
            $toolplugininfo = core_plugin_manager::instance()->get_plugin_info('aitool_' . $tool);
            $classname = "\\aitool_" . $tool . "\\connector";
            $toolconnector = new $classname();
            $supportspurpose = in_array($purpose, $toolconnector->supported_purposes());
            if ($supportspurpose) {
                $tools[$tool] = $toolplugininfo->displayname;
            }
        }
        return $tools;
    }

    public static function get_connector_instances_for_purpose(string $purpose): array {
        global $DB;
        $instances = [];
        foreach (core_plugin_manager::instance()->get_enabled_plugins('aitool') as $tool) {
            $classname = '\\aitool_' . $tool . '\\connector';
            $connector = \core\di::get($classname);
            if (in_array($purpose, $connector->supported_purposes())) {
                $instancerecords = $DB->get_records('local_ai_manager_instance', ['connector' => $tool]);
                foreach ($instancerecords as $instancerecord) {
                    $instances[$instancerecord->id] = $instancerecord->name;
                }
            }
        }
        return $instances;
    }

    public static function setup_connector_and_instance(int $instanceid) {
        global $DB;
        $instancerecord = $DB->get_record('local_ai_manager_instance', ['id' => $instanceid]);
        if (!$instancerecord) {
            throw new \moodle_exception('Cannot find connector instance with id ' . $instanceid);
        }
        $connectorname = $instancerecord->connector;
        $classname = '\\aitool_' . $connectorname . '\\instance';
        $connectorinstance = new $classname($instanceid);
        \core\di::set(\local_ai_manager\connector_instance::class, $connectorinstance);
    }

    /**
     * Get the prompt completion from the LLM.
     *
     * @param string $prompttext The prompt text.
     * @param array $options Options to be used during processing.
     * @return prompt_response The generated prompt response object
     */
    public function perform_request(string $prompttext, array $options = []): prompt_response {

        if ($options === null) {
            $options = new \stdClass();
        }
        \local_debugger\performance\debugger::print_debug('test', 'make_request options', $options);
        $promptdata = $this->toolconnector->get_prompt_data($prompttext);
        try {
            $requestresult = $this->toolconnector->make_request($promptdata, !empty($options['multipart']));
        } catch (\Exception $exception) {
            return prompt_response::create_from_error($exception->getMessage(), $exception->getTraceAsString());
        }
        if ($requestresult->is_error()) {
            return prompt_response::create_from_error($requestresult->get_errormessage(), $requestresult->get_debuginfo());
        }
        $promptcompletion = $this->toolconnector->execute_prompt_completion($requestresult->get_response(), $options);
        $this->log_request($promptcompletion);
        return $promptcompletion;
    }

    private function log_request(prompt_response $promptcompletion): void {
        global $DB, $USER;

        if ($promptcompletion->is_error()) {
            // TODO We probably used some tokens despite an error? Need to properly log this.
            return;
        }

        $data = new stdClass();
        $data->userid = $USER->id;
        $data->value = $promptcompletion->get_usage()->value;
        $data->model = $this->toolconnector->get_instance()->get_model();
        if (!$this->toolconnector->has_customvalue1()) {
            $data->customvalue1 = $promptcompletion->get_usage()->customvalue1;
        }
        if (!$this->toolconnector->has_customvalue2()) {
            $data->customvalue2 = $promptcompletion->get_usage()->customvalue2;
        }
        $data->modelinfo = $promptcompletion->get_modelinfo();
        $data->timecreated = time();
        $DB->insert_record('local_ai_manager_request_log', $data);

        $userinfo = new userinfo($data->userid);
        $userinfo->set_currentusage($userinfo->get_currentusage() + 1);
        $userinfo->store();

    }
}
