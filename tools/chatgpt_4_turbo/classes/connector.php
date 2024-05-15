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
 * Connector - chatgpt_4_turbo
 *
 * @package    aitool_chatgpt_4_turbo
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace aitool_chatgpt_4_turbo;

use dml_exception;

/**
 * Connector - chatgpt_4
 *
 * @package    aitool_chatgpt_4_turbo
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class connector extends \aitool_chatgpt_35\connector {

    public function get_model_name(): string {
        return 'gpt-4-turbo';
    }

    public function get_endpoint_url(): string {
        return 'https://api.openai.com/v1/chat/completions';
    }
    protected function get_api_key(): string {
        return get_config('aitool_chatgpt_4_turbo', 'openaiapikey');
    }

    /**
     * Construct the connector class for chatgpt_4.
     *
     * @return void
     * @throws dml_exception
     */
    public function __construct() {
        parent::__construct();
        $this->temperature = floatval(get_config('aitool_chatgpt_4_turbo', 'temperature'));
    }
}
