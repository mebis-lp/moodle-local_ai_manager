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

namespace aitool_chatgpt;

use local_ai_manager\base_instance;
use local_ai_manager\local\aitool_option_temperature;
use stdClass;

/**
 * Instance class for the connector instance of aitool_chatgpt.
 *
 * @package    local_ai_manager
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class instance extends base_instance {

    protected function extend_form_definition(\MoodleQuickForm $mform): void {
        $mform->addElement('selectyesno', 'azure_enabled', 'USE OPENAI VIA AZURE');
        $mform->setDefault('azure_enabled', false);

        $mform->addElement('text', 'azure_resourcename', 'AZURE RESOURCE NAME');
        $mform->setType('azure_resourcename', PARAM_TEXT);
        $mform->hideIf('azure_resourcename', 'azure_enabled', 'eq', '0');

        $mform->addElement('text', 'azure_deploymentid', 'AZURE DEPLOYMENT ID');
        $mform->setType('azure_deploymentid', PARAM_TEXT);
        $mform->hideIf('azure_deploymentid', 'azure_enabled', 'eq', '0');

        aitool_option_temperature::extend_form_definition($mform);

        // We leave the endpoint empty on creation, because it depends if azure is being used or not.
        $mform->setDefault('endpoint', '');
        $mform->freeze('endpoint');

        $mform->hideIf('model', 'azure_enabled', 'eq', 1);
    }

    protected function get_extended_formdata(): stdClass {
        $data = new stdClass();
        $temperature = floatval($this->get_customfield1());
        $temperaturedata = aitool_option_temperature::add_temperature_to_form_data($temperature);
        foreach ($temperaturedata as $key => $value) {
            $data->{$key} = $value;
        }
        $data->azure_enabled = !empty($this->get_customfield2());
        $data->azure_resourcename = $this->get_customfield3();
        $data->azure_deploymentid = $this->get_customfield4();
        return $data;
    }

    protected function extend_store_formdata(stdClass $data): void {
        if (!empty($data->azure_enabled)) {
            // TODO Eventually make api version an admin setting.
            $endpoint = 'https://' . $data->azure_resourcename .
                    '.openai.azure.com/openai/deployments/'
            . $data->azure_deploymentid . '/chat/completions?api-version=2024-02-01';
        } else {
            $endpoint = 'https://api.openai.com/v1/chat/completions';
        }
        $this->set_endpoint($endpoint);
        // TODO eventually detect , or . as float separator and handle accordingly
        $temperature = aitool_option_temperature::extract_temperature_to_store($data);
        $this->set_customfield1($temperature);
        $this->set_customfield2(!empty($data->azure_enabled));
        $this->set_customfield3(trim($data->azure_resourcename));
        $this->set_customfield4(trim($data->azure_deploymentid));
    }

    protected function extend_validation(array $data, array $files): array {
        $errors = [];
        $errors = array_merge($errors, aitool_option_temperature::validate_temperature($data));
        if (!empty($data['azure_enabled'])) {
            if (empty($data['azure_resourcename'])) {
                $errors['azure_resourcename'] = 'Azure Resource Name is required';
            }
            if (empty($data['azure_deploymentid'])) {
                $errors['azure_deploymentid'] = 'Azure Deployment ID is required';
            }
        }
        return $errors;
    }

    public function get_temperature(): float {
        return floatval($this->get_customfield1());
    }

    public function azure_enabled(): bool {
        return !empty($this->get_customfield2());
    }
}
