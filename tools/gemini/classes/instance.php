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
        $mform->addElement('text', 'temperature', 'TEMPERATURE');
        $mform->setType('temperature', PARAM_FLOAT);

        $mform->setDefault('endpoint', 'https://generativelanguage.googleapis.com/v1beta/models');
        $mform->freeze('endpoint');
    }

    protected function get_extended_formdata(): stdClass {
        $data = new stdClass();
        $data->temperature = floatval($this->get_customfield1());
        $data->model = $this->get_model();
        return $data;
    }

    protected function extend_store_formdata(stdClass $data): void {
        $this->set_customfield1(strval($data->temperature));
    }

    public function get_temperature(): float {
        return floatval($this->get_customfield1());
    }

    public function get_endpoint(): string {
        return parent::get_endpoint() . '/' . $this->get_model() . ':generateContent';
    }
}
