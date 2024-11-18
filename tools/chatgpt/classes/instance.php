<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,https://github.com/mebis-lp/moodle-local_ai_manager/blob/MBS-9445_local_ai_manager_add_trimming_to_instance_edit_form/tools/chatgpt/classes/instance.php
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace aitool_chatgpt;

use local_ai_manager\base_instance;
use local_ai_manager\local\aitool_option_azure;
use local_ai_manager\local\aitool_option_temperature;
use stdClass;

/**
 * Instance class for the connector instance of aitool_chatgpt.
 *
 * @package    aitool_chatgpt
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class instance extends base_instance {

    #[\Override]
    protected function extend_form_definition(\MoodleQuickForm $mform): void {
        aitool_option_temperature::extend_form_definition($mform);
        aitool_option_azure::extend_form_definition($mform);
    }

    #[\Override]
    protected function get_extended_formdata(): stdClass {
        $data = new stdClass();
        $temperature = floatval($this->get_customfield1());
        $temperaturedata = aitool_option_temperature::add_temperature_to_form_data($temperature);
        foreach ($temperaturedata as $key => $value) {
            $data->{$key} = $value;
        }
        foreach (aitool_option_azure::add_azure_options_to_form_data($this->get_customfield2(), $this->get_customfield3(),
                $this->get_customfield4(), $this->get_customfield5()) as $key => $value) {
            $data->{$key} = $value;
        }
        return $data;
    }

    #[\Override]
    protected function extend_store_formdata(stdClass $data): void {
        $temperature = aitool_option_temperature::extract_temperature_to_store($data);
        $this->set_customfield1($temperature);

        [$enabled, $resourcename, $deploymentid, $apiversion] = aitool_option_azure::extract_azure_data_to_store($data);

        if (!empty($enabled)) {
            $endpoint = 'https://' . $resourcename .
                    '.openai.azure.com/openai/deployments/'
                    . $deploymentid . '/chat/completions?api-version=' . $apiversion;
            // We have an empty model because the model is preconfigured if we're using azure.
            // So we overwrite the default "preconfigured" value by a better model name.
            $this->set_model(aitool_option_azure::get_azure_model_name($this->get_connector()));
        } else {
            $endpoint = 'https://api.openai.com/v1/chat/completions';
        }
        $this->set_endpoint($endpoint);

        $this->set_customfield2($enabled);
        $this->set_customfield3($resourcename);
        $this->set_customfield4($deploymentid);
        $this->set_customfield5($apiversion);
    }

    #[\Override]
    protected function extend_validation(array $data, array $files): array {
        $errors = [];
        $errors = array_merge($errors, aitool_option_temperature::validate_temperature($data));
        $errors = array_merge($errors, aitool_option_azure::validate_azure_options($data));
        return $errors;
    }

    /**
     * Getter for the temperature value.
     *
     * @return float the temperature value as float
     */
    public function get_temperature(): float {
        return floatval($this->get_customfield1());
    }

    /**
     * Return if azure is enabled.
     *
     * @return bool true if azure is enabled
     */
    public function azure_enabled(): bool {
        return !empty($this->get_customfield2());
    }
}
