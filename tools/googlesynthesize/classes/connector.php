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
 * Connector - chatgpt.
 *
 * @package    aitool_googlesynthesize
 * @copyright  ISB Bayern, 2024
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace aitool_googlesynthesize;

use core\http_client;
use local_ai_manager\local\prompt_response;
use local_ai_manager\local\request_response;
use local_ai_manager\local\unit;
use local_ai_manager\local\usage;
use Psr\Http\Message\StreamInterface;

/**
 * Connector - chatgpt
 *
 * @package    aitool_googlesynthesize
 * @copyright  ISB Bayern, 2024
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class connector extends \local_ai_manager\base_connector {

    public function __construct(instance $instance) {
        $this->instance = $instance;
    }

    public function get_models_by_purpose(): array {
        return [
                'tts' => ['googletts'],
        ];
    }

    public function get_unit(): unit {
        return unit::COUNT;
    }

    public function make_request(array $data): request_response {
        $client = new http_client([
            // TODO Make timeout higher, LLM requests can take quite a bit of time
                'timeout' => 60,
        ]);

        $options['headers'] = [
                'x-goog-api-key' => $this->get_api_key(),
                'Content-Type' => 'application/json;charset=utf-8',
        ];
        $options['body'] = json_encode($data);

        $start = microtime(true);

        $response = $client->post($this->get_endpoint_url(), $options);
        $end = microtime(true);
        $executiontime = round($end - $start, 2);
        if ($response->getStatusCode() === 200) {
            $return = request_response::create_from_result($response->getBody(), $executiontime);
        } else {
            // TODO localize
            $return = request_response::create_from_error(
                    'Sending request to tool api endpoint failed with code ' . $response->getStatusCode(),
                    $response->getBody(),
                    ''
            );
        }
        return $return;
    }

    public function execute_prompt_completion(StreamInterface $result, array $options = []): prompt_response {
        global $USER;

        $content = json_decode($result->getContents(), true);

        $fs = get_file_storage();
        $fileinfo = [
                'contextid' => \context_user::instance($USER->id)->id,
                'component' => 'user',
                'filearea' => 'draft',
                'itemid' => $options['itemid'],
                'filepath' => '/',
                'filename' => $options['filename'],
        ];
        $file = $fs->create_file_from_string($fileinfo, base64_decode($content['audioContent']));

        $filepath = \moodle_url::make_draftfile_url(
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename()
        )->out();

        return prompt_response::create_from_result($this->instance->get_model(), new usage(1.0), $filepath);
    }

    public function get_prompt_data(string $prompttext, array $requestoptions): array {
        return [
                'input' => [
                        'text' => $prompttext,
                ],
                'voice' => [
                        'voice' => $requestoptions['voices'][0],
                        'languageCode' => $requestoptions['languages'][0],
                ],
                'audioConfig' => [
                        'audioEncoding' => 'MP3',
                ],
        ];
    }

    public function has_customvalue1(): bool {
        return true;
    }

    public function has_customvalue2(): bool {
        return true;
    }

    public function get_available_options(): array {
        return [
                'voices' => [
                        // TODO Retrieve Voices from google api
                        ['key' => 'DUMMYGOOGLEVOICE', 'displayname' => 'DUMMYGOOGLEVOICE'],
                        ['key' => 'DUMMYGOOGLEVOICE2', 'displayname' => 'DUMMYGOOGLEVOICE2'],
                ],
                'languages' => [
                        ['key' => 'de-DE', 'displayname' => 'Deutsch'],
                ],
        ];
    }

}
