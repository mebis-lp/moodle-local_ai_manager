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
class aitool_option_temperature {

    public static function extend_form_definition(\MoodleQuickForm $mform): void {
        $mform->addElement('static', '', get_string('temperature', 'local_ai_manager'));
        $radioarray = [];
        $radioarray[] = $mform->createElement('radio', 'temperatureprechoice', '', get_string('temperature_more_creative', 'local_ai_manager'), 'selection_creative');
        $radioarray[] = $mform->createElement('radio', 'temperatureprechoice', '', get_string('temperature_creative_balanced', 'local_ai_manager'), 'selection_balanced');
        $radioarray[] = $mform->createElement('radio', 'temperatureprechoice', '', get_string('temperature_more_precise', 'local_ai_manager'), 'selection_precise');
        $mform->addGroup($radioarray, 'temperatureprechoicearray', get_string('temperature_defaultsetting', 'local_ai_manager'), ['<br/>'], false);
        $mform->setDefault('temperatureprechoice', 'selection_balanced');

        $mform->addElement('checkbox', 'temperatureusecustom', get_string('temperature_use_custom_value', 'local_ai_manager'));
        $mform->setDefault('temperatureusecustom', 0);
        $mform->addElement('text', 'temperaturecustom', get_string('temperature_custom_value', 'local_ai_manager'));
        $mform->disabledIf('temperaturecustom', 'temperatureusecustom');
        $mform->setType('temperaturecustom', PARAM_FLOAT);
        $mform->disabledIf('temperatureprechoicearray', 'temperatureusecustom', 'checked');
    }

    public static function add_temperature_to_form_data(string $temperature): stdClass {
        $temperature = floatval($temperature);
        $data = new stdClass();
        $data->temperatureusecustom = 0;
        switch ($temperature) {
            case 0.8:
                $data->temperatureprechoice = 'selection_creative';
                break;
            case 0.5:
                $data->temperatureprechoice = 'selection_balanced';
                break;
            case 0.2:
                $data->temperatureprechoice = 'selection_precise';
                break;
            default:
                $data->temperatureusecustom = 1;
                $data->temperaturecustom = $temperature;
        }
        return $data;
    }

    public static function extract_temperature_to_store(stdClass $data): string {
        // TODO Handle float vs. string somehow
        $temperature = null;
        if (empty($data->temperatureusecustom)) {
            switch ($data->temperatureprechoice) {
                case 'selection_creative':
                    $temperature = 0.8;
                    break;
                case 'selection_balanced':
                    $temperature = 0.5;
                    break;
                case 'selection_precise':
                    $temperature = 0.2;
                    break;
            }
        } else {
            $temperature = $data->temperaturecustom;
        }
        return $temperature;
    }

    public static function validate_temperature(array $data): array {
        $errors = [];
        if (!empty($data['temperaturecustom']) && (floatval($data['temperaturecustom']) < 0 || floatval($data['temperaturecustom']) > 1.0)) {
            $errors['temperature'] = 'Temperature must be between 0 und 1';
        }
        return $errors;
    }

}
