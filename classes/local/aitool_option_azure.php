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

use stdClass;

/**
 * Helper class for providing the necessary extension functions to implement the temperature parameter into an ai tool.
 *
 * @package    local_ai_manager
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class aitool_option_azure {

    public static function extend_form_definition(\MoodleQuickForm $mform): void {
        $mform->addElement('selectyesno', 'azure_enabled', get_string('use_openai_by_azure_heading', 'aitool_chatgpt'));
        $mform->setDefault('azure_enabled', false);

        $mform->addElement('text', 'azure_resourcename', get_string('use_openai_by_azure_name', 'aitool_chatgpt'));
        $mform->setType('azure_resourcename', PARAM_TEXT);
        $mform->hideIf('azure_resourcename', 'azure_enabled', 'eq', '0');

        $mform->addElement('text', 'azure_deploymentid', get_string('use_openai_by_azure_deploymentid', 'aitool_chatgpt'));
        $mform->setType('azure_deploymentid', PARAM_TEXT);
        $mform->hideIf('azure_deploymentid', 'azure_enabled', 'eq', '0');

        // We leave the endpoint empty on creation, because it depends if azure is being used or not.
        $mform->setDefault('endpoint', '');
        $mform->freeze('endpoint');

        $mform->hideIf('model', 'azure_enabled', 'eq', 1);
    }

    public static function add_azure_options_to_form_data(bool $enabled, ?string $resourcename, ?string $deploymentid): stdClass {
        $data = new stdClass();
        $data->azure_enabled = $enabled;
        if ($enabled) {
            $data->azure_resourcename = $resourcename;
            $data->azure_deploymentid = $deploymentid;
        }
        return $data;
    }

    public static function extract_azure_data_to_store(stdClass $data): array {
        $resourcename = empty($data->azure_resourcename) ? null : $data->azure_resourcename;
        $deploymentid = empty($data->azure_deploymentid) ? null : $data->azure_deploymentid;
        return [$data->azure_enabled, $resourcename, $deploymentid];
    }

    public static function validate_azure_options(array $data): array {
        $errors = [];
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

}
