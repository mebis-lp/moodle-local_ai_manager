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

namespace aitool_gemini;

use local_ai_manager\connector_instance;
use stdClass;

/**
 * Instance class for the connector instance of aitool_gemini.
 *
 * @package    local_ai_manager
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class instance extends connector_instance {

    protected function extend_form_definition(\MoodleQuickForm $mform): void {
        $mform->addElement('text', 'temperature', get_string('temperature', 'aitool_gemini'));
        $mform->setType('temperature', PARAM_FLOAT);
        $mform->setDefault('temperature', '1.0');

        $mform->addElement('text', 'top_p', get_string('top_p', 'aitool_gemini'));
        $mform->setType('top_p', PARAM_FLOAT);
        $mform->setDefault('top_p', '1.0');

        $mform->setDefault('endpoint', 'https://generativelanguage.googleapis.com/v1beta/models');
        $mform->freeze('endpoint');
    }

    protected function get_extended_formdata(): stdClass {
        $data = new stdClass();
        $data->temperature = floatval($this->get_customfield1());
        $data->top_p = floatval($this->get_customfield2());
        return $data;
    }

    protected function extend_store_formdata(stdClass $data): void {
        // TODO eventually detect , or . as float separator and handle accordingly
        $this->set_customfield1(strval($data->temperature));
        $this->set_customfield2(strval($data->top_p));
    }

    protected function extend_validation(array $data, array $files): array {
        $errors = [];
        if (floatval($data['temperature']) < 0 || floatval($data['temperature']) > 2.0) {
            $errors['temperature'] = 'Temperature must be between 0 und 2';
        }
        if (floatval($data['top_p']) < 0 || floatval($data['top_p']) > 1.0) {
            $errors['top_p'] = 'Top_p must be between 0 und 1';
        }
        return $errors;
    }

    public function get_temperature(): float {
        return floatval($this->get_customfield1());
    }
    public function get_top_p(): float {
        return floatval($this->get_customfield2());
    }

    public function get_endpoint(): string {
        return parent::get_endpoint() . '/' . $this->get_model() . ':generateContent';
    }
}
