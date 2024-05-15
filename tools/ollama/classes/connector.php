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
 * Connector - ollama
 *
 * @package    aitool_ollama
 * @copyright  ISB Bayern, 2024
 * @author     Stefan Hanauska <stefan.hanauska@csg-in.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace aitool_ollama;

/**
 * Connector - ollama
 *
 * @package    aitool_ollama
 * @copyright  ISB Bayern, 2024
 * @author     Stefan Hanauska <stefan.hanauska@csg-in.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class connector extends \local_ai_manager\base_connector {

    const MODEL = 'mixtral';

    private string $model;
    private string $endpointurl;
    private float $temperature;
    private string $apikey;

    /**
     * Construct the connector class for ollama
     *
     * @return void
     */
    public function __construct() {
        $this->model = self::MODEL;
        $this->endpointurl = get_config('aitool_ollama', 'url');
        $this->temperature = floatval(get_config('aitool_ollama', 'temperature'));
        $this->apikey = get_config('aitool_ollama', 'apikey');
    }

    /**
     * Makes a request to the specified URL with the given data and API key.
     *
     * @param string $url The URL to make the request to.
     * @param array $data The data to send with the request.
     * @return array The response from the request.
     */
    private function make_request($url, $data) {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $headers = ['Content-Type: application/json;charset=utf-8'];

        if (!empty($this->apikey)) {
            $headers[] = 'Authorization: Bearer ' . $this->apikey;
        }

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
     * @return string|array The generated completion or null if the model is empty.
     * @throws moodle_exception If the model is empty.
     */
    public function prompt_completion($prompttext) {

        if (empty($this->model)) {
            throw new \moodle_exception('prompterror', 'local_ai_manager', '', null, 'Empty query model.');
        }

        $data = $this->get_prompt_data($prompttext);
        $result = $this->make_request($this->endpointurl, $data, '');

        if (!empty($result['response']['usage'])) {
            \local_ai_manager\manager::log_request(
                $result['response']['prompt_eval_count'],
                0,
                $result['response']['eval_count'],
                $result['response']['model']
            );
        }

        if (!empty($result['response']['response'])) {
            return $result['response']['response'];
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
            'prompt' => $prompttext,
            'stream' => false,
            'keep_alive' => '60m',
            'options' => [
                'temperature' => $this->temperature,
            ],
        ];
        return $data;
    }
}
