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
 * Connector - dalle_3
 *
 * @package    aitool_dalle_3
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace aitool_dalle_3;

use coding_exception;
use dml_exception;
use moodle_exception;
use invalid_dataroot_permissions;
use Error;
use file_exception;
use stored_file_creation_exception;

/**
 * Connector - dalle_3
 *
 * @package    aitool_dalle_3
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class connector extends \aitool_whisper_1\connector {

    const MODEL = 'dall-e-3';
    const EDIT_ENDPOINT = 'https://api.openai.com/v1/images/edits';
    const ENDPOINTURL = 'https://api.openai.com/v1/images/generations';
    const VARIATIONS_ENDPOINT = 'https://api.openai.com/v1/images/variations';

    private $model;
    private $endpointurl;
    private float $temperature;
    private $apikey;

    /**
     * Construct the connector class for dalle_3
     *
     * @return void
     * @throws dml_exception
     */
    public function __construct() {
        $this->model = self::MODEL;
        $this->endpointurl = self::ENDPOINTURL;
        $this->temperature = floatval(get_config('aitool_dalle_3', 'temperature'));
        $this->apikey = get_config('aitool_dalle_3', 'openaiapikey');
    }

    /**
     * Generates a completion for the given prompt text.
     *
     * @param string $prompttext The prompt text.
     * @param object $options Options to be used during processing.
     * @return string|array The generated completion or null if the model is empty.
     * @throws moodle_exception If the model is empty.
     */
    public function prompt_completion($prompttext, object $options = null) {

        if ($options === null) {
            $options = new \stdClass();
        }

        if (empty($this->model)) {
            throw new \moodle_exception('prompterror', 'local_ai_connector', '', null, 'Empty query model.');
        }

        $data = [
            'prompt' => $prompttext,
            'size' => (empty($options->imagesize)) ? "256x256" : $options->imagesize,
            'n' => (empty($options->numberofresponses)) ? 1 : $options->numberofresponses,
        ];


        \local_debugger\performance\debugger::print_debug('test', 'prompt completion dalle', $data);
        $result = $this->make_request($this->endpointurl, $data, $this->apikey, null, $options, 'png');

        if (!empty($result['error'])) {
            return $result;
        }

        if (!empty($result)) {
            if (!empty($result['response'])) {
                return $result['response'];
            }
        }
    }
}
