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
 * Connector - whisper_1
 *
 * @package    aitool_whisper_1
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace aitool_whisper_1;

use coding_exception;
use dml_exception;
use moodle_exception;
use invalid_dataroot_permissions;
use Error;
use file_exception;
use stored_file_creation_exception;

/**
 * Connector - whisper_1
 *
 * @package    aitool_whisper_1
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class connector extends \local_ai_manager\helper {

    const MODEL = 'tts-1';
    const ENDPOINTURL = 'https://api.openai.com/v1/audio/speech';

    private $model;
    private $endpointurl;
    private float $temperature;
    private $apikey;

    /**
     * Construct the connector class for whisper_1
     *
     * @return void
     * @throws dml_exception
     */
    public function __construct() {
        $this->model = self::MODEL;
        $this->endpointurl = self::ENDPOINTURL;
        $this->temperature = get_config('aitool_whisper_1', 'temperature', 0.5);
        $this->apikey = get_config('aitool_whisper_1', 'openaiapikey');
    }

    /**
     * Makes a request to the specified URL with the given data and API key.
     *
     * @param mixed $url
     * @param mixed $data
     * @param mixed $apikey
     * @param mixed $multipart
     * @param object|null $options
     * @param string $fileformat
     * @return array
     */
    public function make_request($url, $data, $apikey, $multipart = null, object $options = null, string $fileformat = 'mp3') {
        global $CFG, $USER;

        if ($options === null) {
            $options = new \stdClass();
        }

        require_once($CFG->libdir . '/filelib.php');

        if (empty($apikey)) {
            throw new \moodle_exception('prompterror', 'local_ai_connector', '', null, 'Empty API Key.');
        }

        $headers = $multipart ? ["Content-Type: multipart/form-data"] : ["Content-Type: application/json;charset=utf-8"];
        $headers[] = "Authorization: Bearer $apikey";

        // Store the file to a temporary location.
        $filedir = make_temp_directory(true);
        $filepath = $filedir . '/' . uniqid() . "." . $fileformat;

        if ($fileformat == 'mp3') {
            $data['file'] = curl_file_create($filepath);
        }

        $curl = new \curl();
        $curloptions = [
            "CURLOPT_RETURNTRANSFER" => true,
            "CURLOPT_HTTPHEADER" => $headers,
        ];

        $response = $curl->post($url, json_encode($data), $curloptions);

        if (!empty(json_decode($response, true)['error'])) {
            return ['error' => json_decode($response, true)['error']];
        }

        $fs = get_file_storage();
        $fileinfo = [
            'contextid' => \context_user::instance($USER->id)->id,
            'component' => 'user',
            'filearea'  => 'draft',
            'itemid'    => $options->itemid,
            'filepath'  => '/',
            'filename'  => $options->filename,
        ];

        if ($fileformat == 'png') {
            $file = $fs->create_file_from_url($fileinfo, json_decode($response,true)['data'][0]['url'], [], true);
            $filecreated = true;
        } else {
            $filecreated = file_put_contents($filepath, $response);
            $file = $fs->create_file_from_pathname($fileinfo, $filepath);
        }

        // Finally delete the temporarily file.
        unlink($filepath);

        $filepath = \moodle_url::make_draftfile_url(
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename(),
            false
        )->out();

        // if (!empty($response['response']['usage'])) {
        //     \local_ai_manager\manager::log_request(
        //         $result['response']['usage']['prompt_tokens'],
        //         $result['response']['usage']['completion_tokens'],
        //         $result['response']['usage']['total_tokens'],
        //         $result['response']['model']
        //     );
        // }

        if ($filecreated != true) {
            return ['curl_error' => $response, 'execution_time' => $executiontime];
        }
        return ['response' => $filepath, 'execution_time' => $executiontime];
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

        $data = $this->get_prompt_data($prompttext, $options);
        $result = $this->make_request($this->endpointurl, $data, $this->apikey, null,  $options);

        if (!empty($result['response']['choices'][0]['text'])) {
            return $result['response']['choices'][0]['text'];
        } else if (!empty($result['response']['choices'][0]['message'])) {
            return $result['response']['choices'][0]['message']['content'];
        } else if (empty($result['response']['choices']) && !empty ($result['response'])) {
            return $result['response'];
        }else {
            return $result;
        }
    }

    /**
     * Retrieves the data for the prompt based on the prompt text.
     *
     * @param string $prompttext The prompt text.
     * @return array The prompt data.
     */
    private function get_prompt_data(string $prompttext, object $options): array {

        // If empty, use text language, else translate to the mentioned language.
        if (!empty($options->language)) {
            $data['language'] = $options->language;
            $manager = new \local_ai_manager\manager('chat');
            $prompt = 'Translate the follwing text into ' . $options->language .':' . $prompttext;
            $prompttext = $manager->make_request($prompt);
        }

        $data = [
            'model' => $this->model,
            'input' => $prompttext,
            'voice' => (empty($options->voice)) ? 'alloy' : $options->voice,
        ];

        return $data;
    }

    /**
     * Getter method to get additional, language model specific options.
     * @return array
     */
    public function get_additional_options(): array {
        global $CFG;
        require_once($CFG->dirroot . '/local/ai_manager/tools/whisper_1/classes/language_codes.php');

        return ['languagecodes' => $languagecodes];
    }
}
