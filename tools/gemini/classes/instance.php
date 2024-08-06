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

use local_ai_manager\base_instance;
use local_ai_manager\local\aitool_option_temperature;
use stdClass;

/**
 * Instance class for the connector instance of aitool_gemini.
 *
 * @package    local_ai_manager
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class instance extends base_instance {

    protected function extend_form_definition(\MoodleQuickForm $mform): void {
        $mform->setDefault('endpoint', 'https://generativelanguage.googleapis.com/v1beta/models');
        $mform->freeze('endpoint');

        aitool_option_temperature::extend_form_definition($mform);
    }

    protected function get_extended_formdata(): stdClass {
        $temperature = $this->get_customfield1();
        $data = new stdClass();
        $temperaturedata = aitool_option_temperature::add_temperature_to_form_data($temperature);
        foreach ($temperaturedata as $key => $value) {
            $data->{$key} = $value;
        }
        return $data;
    }

    protected function extend_store_formdata(stdClass $data): void {
        // TODO eventually detect , or . as float separator and handle accordingly
        $temperature = aitool_option_temperature::extract_temperature_to_store($data);
        $this->set_customfield1($temperature);
    }

    protected function extend_validation(array $data, array $files): array {
        return aitool_option_temperature::validate_temperature($data);
    }

    public function get_temperature(): float {
        return floatval($this->get_customfield1());
    }

    public function get_endpoint(): string {
        return parent::get_endpoint() . '/' . $this->get_model() . ':generateContent';
    }
}
