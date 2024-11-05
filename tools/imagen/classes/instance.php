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

namespace aitool_imagen;

use local_ai_manager\base_instance;
use stdClass;

/**
 * Instance class for the connector instance of aitool_imagen.
 *
 * @package    aitool_imagen
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class instance extends base_instance {

    #[\Override]
    protected function extend_form_definition(\MoodleQuickForm $mform): void {
        $mform->freeze('apikey');
        $mform->freeze('endpoint');
        $mform->addElement('textarea', 'serviceaccountjson',
                get_string('serviceaccountjson', 'aitool_imagen'), ['rows' => '20']);
    }

    #[\Override]
    protected function get_extended_formdata(): stdClass {
        $data = new stdClass();
        $data->serviceaccountjson = $this->get_customfield1();
        return $data;
    }

    #[\Override]
    protected function extend_store_formdata(stdClass $data): void {
        $serviceaccountjson = trim($data->serviceaccountjson);
        $this->set_customfield1($serviceaccountjson);
        $serviceaccountinfo = json_decode($serviceaccountjson);
        $projectid = $serviceaccountinfo->project_id;

        $endpoint = 'https://europe-west3-aiplatform.googleapis.com/v1/projects/' . $projectid
                . '/locations/europe-west3/publishers/google/models/'
                . $this->get_model() . ':predict';
        $this->set_endpoint($endpoint);
    }

    #[\Override]
    protected function extend_validation(array $data, array $files): array {
        $errors = [];
        if (empty($data['serviceaccountjson'])) {
            $errors['serviceaccountjson'] = get_string('err_serviceaccountjsonempty', 'aitool_imagen');
            return $errors;
        }

        $serviceaccountinfo = json_decode(trim($data['serviceaccountjson']));
        foreach (['private_key_id', 'private_key', 'client_email'] as $field) {
            if (!property_exists($serviceaccountinfo, $field)) {
                $errors['serviceaccountjson'] = get_string('err_serviceaccountjsoninvalid', 'aitool_imagen', $field);
                break;
            }
        }

        return $errors;
    }
}
