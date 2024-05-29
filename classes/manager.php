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

use core_date;
use core_plugin_manager;
use DateInterval;
use dml_exception;
use local_ai_manager\local\config_manager;
use local_ai_manager\local\prompt_response;
use local_ai_manager\local\tenant;
use local_ai_manager\local\userinfo;
use local_bycsauth\school;
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

    private base_purpose $purpose;

    /** @var base_connector $toolconnector The tool connector object. */
    private base_connector $toolconnector;
    /**
     * @var local\connector_factory|mixed
     */
    private \local_ai_manager\local\connector_factory $factory;
    /**
     * @var local\config_manager|mixed
     */
    private config_manager $configmanager;

    /**
     * Constructor of manager class.
     *
     * @param string $purpose
     * @param string $tooltouse
     * @return string|void
     * @throws dml_exception
     */
    public function __construct(string $purpose, private readonly array $options = []) {
        $this->factory = \core\di::get(\local_ai_manager\local\connector_factory::class);
        $this->purpose = $this->factory->get_purpose_by_purpose_string($purpose);
        $this->toolconnector = $this->factory->get_connector_by_purpose($purpose);
        $this->configmanager = \core\di::get(config_manager::class);
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

    /**
     * Get the prompt completion from the LLM.
     *
     * @param string $prompttext The prompt text.
     * @param array $options Options to be used during processing.
     * @return prompt_response The generated prompt response object
     */
    public function perform_request(string $prompttext, array $options = []): prompt_response {
        global $DB, $USER;

        if ($options === null) {
            $options = new \stdClass();
        }

        $userinfo = new userinfo($USER->id);
        if ($userinfo->get_currentusage() >= $this->configmanager->get_max_requests($userinfo->get_role())) {
            $period = format_time($this->configmanager->get_config('max_requests_period'));
            return prompt_response::create_from_error(429, 'You have reached the maximum amount of requests. '
                . 'You are only allowed to send ' . $this->configmanager->get_max_requests($userinfo->get_role())
                . ' requests in a period of ' . $period . '.',
                    '');
        }

        $requestoptions = $this->purpose->get_request_options($options);
        $promptdata = $this->toolconnector->get_prompt_data($prompttext, $requestoptions);
        try {
            $requestresult = $this->toolconnector->make_request($promptdata, !empty($options['multipart']));
        } catch (\Exception $exception) {
            return prompt_response::create_from_error(500, $exception->getMessage(), $exception->getTraceAsString());
        }
        if ($requestresult->get_code() !== 200) {
            return prompt_response::create_from_error($requestresult->get_code(), $requestresult->get_errormessage(), $requestresult->get_debuginfo());
        }
        $promptcompletion = $this->toolconnector->execute_prompt_completion($requestresult->get_response(), $options);
        if (!empty($options['forcenewitemid']) && !empty($options['component']) && !empty($options['contextid'] && !empty($options['itemid']))) {
            if ($DB->record_exists('local_ai_manager_request_log', ['component' => $options['component'], 'contextid' => $options['contextid'], 'itemid' => $options['itemid']])) {
                $existingitemid = $options['itemid'];
                unset($options['itemid']);
                $this->log_request($prompttext, $promptcompletion, $requestoptions, $options);
                return prompt_response::create_from_error(409, 'The itemid ' . $existingitemid . ' already taken', '');
            }
        }

        $this->log_request($prompttext, $promptcompletion, $requestoptions, $options);
        return $promptcompletion;
    }

    public function log_request(string $prompttext, prompt_response $promptcompletion, array $requestoptions = [], array $options = []): void {
        global $DB, $USER;

        if ($promptcompletion->get_code() !== 200) {
            // TODO We probably used some tokens despite an error? Need to properly log this.
            return;
        }

        $data = new stdClass();
        $data->userid = $USER->id;
        $data->value = $promptcompletion->get_usage()->value;
        if ($this->toolconnector->has_customvalue1()) {
            $data->customvalue1 = $promptcompletion->get_usage()->customvalue1;
        }
        if ($this->toolconnector->has_customvalue2()) {
            $data->customvalue2 = $promptcompletion->get_usage()->customvalue2;
        }
        $data->model = $this->toolconnector->get_instance()->get_model();
        $data->modelinfo = $promptcompletion->get_modelinfo();
        $data->prompttext = $prompttext;
        $data->promptcompletion = $promptcompletion->get_content();
        if (!empty($requestoptions)) {
            $data->requestoptions = json_encode($requestoptions);
        }
        if (array_key_exists('component', $options)) {
            $data->component = $options['component'];
        }
        if (array_key_exists('contextid', $options)) {
            $data->contextid = intval($options['contextid']);
        }
        if (array_key_exists('itemid', $options)) {
            $data->itemid = intval($options['itemid']);
        }
        $data->timecreated = time();
        $DB->insert_record('local_ai_manager_request_log', $data);

        $userinfo = new userinfo($data->userid);
        $userinfo->set_currentusage($userinfo->get_currentusage() + 1);
        // TODO Extract this into a hook.
        // TODO Make this more performant
        $idmteacherrole = $DB->get_record('role', ['shortname' => 'idmteacher']);
        $coordinatorrole = $DB->get_record('role', ['shortname' => 'schulkoordinator']);
        $school = new school($USER->institution);
        if (user_has_role_assignment($USER->id, $coordinatorrole->id, \context_coursecat::instance($school->get_school_categoryid())->id)) {
            $userinfo->set_role(userinfo::ROLE_UNLIMITED);
        } else if (user_has_role_assignment($USER->id. $idmteacherrole->id, \context_system::instance()->id)) {
            $userinfo->set_role(userinfo::ROLE_EXTENDED);
        } else {
            $userinfo->set_role(userinfo::ROLE_BASIC);
        }
        $userinfo->store();
    }
}
