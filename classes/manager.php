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
use local_ai_manager\local\userusage;
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
    public function __construct(string $purpose) {
        $this->factory = \core\di::get(\local_ai_manager\local\connector_factory::class);
        $this->purpose = $this->factory->get_purpose_by_purpose_string($purpose);
        $this->toolconnector = $this->factory->get_connector_by_purpose($purpose);
        $this->configmanager = \core\di::get(config_manager::class);
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
            $options = [];
        }
        try {
            $options = $this->sanitize_options($options);
        } catch (\Exception $exception) {
            return prompt_response::create_from_error(
                    400,
                    get_string('error_http400', 'local_ai_manager'),
                    $exception->getMessage()
            );
        }

        if (!$this->configmanager->is_tenant_enabled()) {
            return prompt_response::create_from_error(403, get_string('error_http403disabled', 'local_ai_manager'), '');
        }

        $userinfo = new userinfo($USER->id);
        if ($userinfo->is_locked()) {
            return prompt_response::create_from_error(403, get_string('error_http403blocked', 'local_ai_manager'), '');
        }

        if (!$userinfo->is_confirmed()) {
            return prompt_response::create_from_error(403, get_string('error_http403notconfirmed', 'local_ai_manager'), '');
        }

        if (intval($this->configmanager->get_max_requests($this->purpose, $userinfo->get_role())) === 0) {
            return prompt_response::create_from_error(403, get_string('error_http403usertype', 'local_ai_manager'), '');
        }

        $userusage = new userusage($this->purpose, $USER->id);

        if ($userusage->get_currentusage() >= $this->configmanager->get_max_requests($this->purpose, $userinfo->get_role())) {
            $period = format_time($this->configmanager->get_max_requests_period());
            return prompt_response::create_from_error(
                    429,
                    get_string(
                            'error_http429',
                            'local_ai_manager',
                            ['count' => $this->configmanager->get_max_requests($this->purpose, $userinfo->get_role()),
                                    'period' => $period]
                    ),
                    ''
            );
        }

        $requestoptions = $this->purpose->get_request_options($options);
        $promptdata = $this->toolconnector->get_prompt_data($prompttext, $requestoptions);
        try {
            $requestresult = $this->toolconnector->make_request($promptdata);
        } catch (\Exception $exception) {
            // This hopefully very rarely happens, because we catch exceptions already inside the make_request method.
            // So we do not do any more beautifying of exceptions here.
            return prompt_response::create_from_error(500, $exception->getMessage(), $exception->getTraceAsString());
        }
        if ($requestresult->get_code() !== 200) {
            return prompt_response::create_from_error($requestresult->get_code(), $requestresult->get_errormessage(),
                    $requestresult->get_debuginfo());
        }
        $promptcompletion = $this->toolconnector->execute_prompt_completion($requestresult->get_response(), $options);
        if (!empty($options['forcenewitemid']) && !empty($options['component']) &&
                !empty($options['contextid'] && !empty($options['itemid']))) {
            if ($DB->record_exists('local_ai_manager_request_log',
                    ['component' => $options['component'], 'contextid' => $options['contextid'], 'itemid' => $options['itemid']])) {
                $existingitemid = $options['itemid'];
                unset($options['itemid']);
                $this->log_request($prompttext, $promptcompletion, $requestoptions, $options);
                return prompt_response::create_from_error(409, get_string('error_http409', 'local_ai_manager'), '');
            }
        }

        $this->log_request($prompttext, $promptcompletion, $requestoptions, $options);
        return $promptcompletion;
    }

    public function log_request(string $prompttext, prompt_response $promptcompletion, array $requestoptions = [],
            array $options = []): void {
        global $DB, $USER;

        if ($promptcompletion->get_code() !== 200) {
            // TODO We probably used some tokens despite an error? Need to properly log this.
            return;
        }

        // TODO Move this handling to a data class "log_entry".

        $data = new stdClass();
        $data->userid = $USER->id;
        $data->value = $promptcompletion->get_usage()->value;
        if ($this->toolconnector->has_customvalue1()) {
            $data->customvalue1 = $promptcompletion->get_usage()->customvalue1;
        }
        if ($this->toolconnector->has_customvalue2()) {
            $data->customvalue2 = $promptcompletion->get_usage()->customvalue2;
        }
        $data->purpose = $this->purpose->get_plugin_name();
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

        // Check if we already have a userinfo object for this. If not we need to create one to initially set the correct role.
        $userinfo = new userinfo($data->userid);
        if (!$userinfo->record_exists()) {
            $userinfo->store();
        }

        $userusage = new userusage($this->purpose, $USER->id);
        $userusage->set_currentusage($userusage->get_currentusage() + 1);
        $userusage->store();
    }

    private function sanitize_options(array $options): array {
        foreach ($options as $key => $value) {
            if (!array_key_exists($key, $this->purpose->get_available_purpose_options())) {
                throw new \coding_exception('Option ' . $key . ' is not allowed for the purpose ' .
                        $this->purpose->get_plugin_name());
            }
            if (is_array($this->purpose->get_available_purpose_options()[$key])) {
                if (!in_array($value[0], array_map(fn($valueobject) => $valueobject['key'],
                        $this->purpose->get_available_purpose_options()[$key]))) {
                    throw new \coding_exception('Value ' . $value[0] . ' for option ' . $key . ' is not allowed for the purpose ' .
                            $this->purpose->get_plugin_name());
                }
            } else {
                if ($this->purpose->get_available_purpose_options()[$key] === base_purpose::PARAM_ARRAY) {
                    array_walk_recursive($value, fn($text) => clean_param($text, PARAM_NOTAGS));
                } else {
                    $options[$key] = clean_param($value, $this->purpose->get_available_purpose_options()[$key]);
                }
            }
        }
        return $options;
    }
}
