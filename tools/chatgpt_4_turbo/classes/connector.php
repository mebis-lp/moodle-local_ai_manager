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
class connector extends \local_ai_manager\helper {

    const MODEL = 'gpt-4-turbo';
    const ENDPOINTURL = 'https://api.openai.com/v1/chat/completions';

    private $model;
    private $endpointurl;
    private float $temperature;
    private $apikey;

    /**
     * Construct the connector class for chatgpt_4
     *
     * @return void
     * @throws dml_exception
     */
    public function __construct() {
        $this->model = self::MODEL;
        $this->endpointurl = self::ENDPOINTURL;
        $this->temperature = get_config('aitool_chatgpt_4_turbo', 'temperature', 0.5);
        $this->apikey = get_config('aitool_chatgpt_4_turbo', 'openaiapikey');
    }

    /**
     * Makes a request to the specified URL with the given data and API key.
     *
     * @param string $url The URL to make the request to.
     * @param array $data The data to send with the request.
     * @param string $apikey The API key to authenticate the request.
     * @return array The response from the request.
     * @throws moodle_exception If the API key is empty.
     */
    private function make_request($url, $data, $apikey, $multipart = null) {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');
        if (empty($apikey)) {
            throw new \moodle_exception('prompterror', 'local_ai_connector', '', null, 'Empty API Key.');
        }
        $headers = $multipart ? [
            "Content-Type: multipart/form-data"
        ] : [
            "Content-Type: application/json;charset=utf-8"
        ];

        $headers[] = "Authorization: Bearer $apikey";
        $curl = new \curl();
        $options = [
            "CURLOPT_RETURNTRANSFER" => true,
            "CURLOPT_HTTPHEADER" => $headers,
        ];
        $start = microtime(true);

        $response = $curl->post($url, json_encode($data), $options);

        $end = microtime(true);
        $executiontime = round($end - $start, 2);

        if (json_decode($response) == null) {
            return ['curl_error' => $response, 'execution_time' => $executiontime];
        }
        return ['response' => json_decode($response, true), 'execution_time' => $executiontime];
    }

    /**
     * Generates a completion for the given prompt text.
     *
     * @param string $prompttext The prompt text.
     * @param array $options Options to be used during processing.
     * @return string|array The generated completion or null if the model is empty.
     * @throws moodle_exception If the model is empty.
     */
    public function prompt_completion($prompttext, $options = []) {

        if (empty($this->model)) {
            throw new \moodle_exception('prompterror', 'local_ai_connector', '', null, 'Empty query model.');
        }

        $data = $this->get_prompt_data($prompttext);
        $result = $this->make_request($this->endpointurl, $data, $this->apikey);

        if (!empty($result['response']['usage'])) {
            \local_ai_manager\manager::log_request(
                $result['response']['usage']['prompt_tokens'],
                $result['response']['usage']['completion_tokens'],
                $result['response']['usage']['total_tokens'],
                $result['response']['model']
            );
        }

        if (!empty($result['response']['choices'][0]['text'])) {
            return $result['response']['choices'][0]['text'];
        } else if (!empty($result['response']['choices'][0]['message'])) {
            return $result['response']['choices'][0]['message']['content'];
        } else {
            return $result;
        }
    }

    /**
     * Retrieves the data for the prompt based on the prompt text.
     *
     * @param string $prompttext The prompt text.
     * @return array The prompt data.
     */
    private function get_prompt_data($prompttext): array {
        $data = [
            'model' => $this->model,
            'temperature' => $this->temperature,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $prompttext
                ],
            ],
        ];
        return $data;
    }
}
