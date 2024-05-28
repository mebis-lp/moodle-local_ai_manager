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
 * Connector - whisper
 *
 * @package    aitool_whisper
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace aitool_whisper;

use local_ai_manager\local\prompt_response;
use local_ai_manager\local\unit;
use local_ai_manager\local\usage;
use Psr\Http\Message\StreamInterface;

/**
 * Connector - whisper
 *
 * @package    aitool_whisper
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class connector extends \local_ai_manager\base_connector {

    public function __construct(instance $instance) {
        $this->instance = $instance;
    }

    public function get_models(): array {
        return ['tts-1'];
    }

    public function supported_purposes(): array {
        return array_filter(parent::supported_purposes(), fn($purpose) => in_array($purpose, ['tts']));
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
    /*public function make_request(array $data, bool $multipart = false): request_response {
        $client = new http_client();

        $contenttype = $multipart ? 'multipart/form-data' : 'application/json;charset=utf-8';
        $options['headers'] = [
                'Authorization' => 'Bearer ' . $this->get_api_key(),
                'Content-Type' => $contenttype,
        ];
        $options['body'] = json_encode($data);

        $start = microtime(true);

        $response = $client->post($this->get_endpoint_url(), $options);
        $end = microtime(true);
        $executiontime = round($end - $start, 2);
        if ($response->getStatusCode() !== 200) {
            // TODO localize
            return request_response::create_from_error(
                    'Sending request to tool api endpoint failed with code ' . $response->getStatusCode(),
                    $response->getBody()
            );
        } else {
            return request_response::create_from_result(['response' => $response->getBody(),
                    'execution_time' => $executiontime]);
        }
    }*/




    /**
     * Retrieves the data for the prompt based on the prompt text.
     *
     * @param string $prompttext The prompt text.
     * @return array The prompt data.
     */
    public function get_prompt_data(string $prompttext, array $requestoptions): array {

        // If empty, use text language, else translate to the mentioned language.
        if (!empty($options->language)) {
            $data['language'] = $options->language;
            $prompttext = 'Translate the following text into ' . $options->language .':' . $prompttext;
        }

        $data = [
            'model' => $this->instance->get_model(),
            'input' => $prompttext,
            'voice' => 'alloy',
        ];

        return $data;
    }

    /**
     * Getter method to get additional, language model specific options.
     * @return array
     */
    public function get_additional_options(): array {
        return ['languagecodes' => language_codes::LANGUAGECODES];
    }

    public function get_unit(): unit {
        // TODO Think about this again.
        return unit::COUNT;
    }

    public function execute_prompt_completion(StreamInterface $result, array $options = []): prompt_response {
        global $USER;
        $fs = get_file_storage();
        $fileinfo = [
                'contextid' => \context_user::instance($USER->id)->id,
                'component' => 'user',
                'filearea'  => 'draft',
                'itemid'    => $options['itemid'],
                'filepath'  => '/',
                'filename'  => $options['filename'],
        ];
        // TODO: Entweder separat handeln für dalle etc. oder hier schön allgemein auseinanderdröseln
        /*if ($fileformat == 'png') {
            $file = $fs->create_file_from_url($fileinfo, json_decode($response,true)['data'][0]['url'], [], true);
            $filecreated = true;
        } else {
            $filecreated = file_put_contents($filepath, $response);
            $file = $fs->create_file_from_pathname($fileinfo, $filepath);
        }*/
        $file = $fs->create_file_from_string($fileinfo, $result);

        $filepath = \moodle_url::make_draftfile_url(
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename()
        )->out();

        return prompt_response::create_from_result($this->instance->get_model(), new usage(1.0), $filepath);
    }
}
