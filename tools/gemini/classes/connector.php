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
 * @package    aitool_gemini
 * @copyright  ISB Bayern, 2024
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace aitool_gemini;

use core\http_client;
use local_ai_manager\local\prompt_response;
use local_ai_manager\local\request_response;
use local_ai_manager\local\unit;
use local_ai_manager\local\usage;
use Psr\Http\Message\StreamInterface;

/**
 * Connector - chatgpt
 *
 * @package    aitool_gemini
 * @copyright  ISB Bayern, 2024
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class connector extends \local_ai_manager\base_connector {

    public function __construct(instance $instance) {
        $this->instance = $instance;
    }

    public function get_models_by_purpose(): array {
        $textmodels = ['gemini-1.0-pro-latest', 'gemini-1.0-pro-vision-latest', 'gemini-1.5-flash-latest', 'gemini-1.5-pro-latest'];
        return [
                'chat' => $textmodels,
                'feedback' => $textmodels,
                'singleprompt' => $textmodels,
                'translate' => $textmodels,
        ];
    }

    public function get_unit(): unit {
        return unit::TOKEN;
    }

    public function make_request(array $data, bool $multipart = false): request_response {
        $client = new http_client([
            // TODO Make timeout higher, LLM requests can take quite a bit of time
                'timeout' => 60,
        ]);

        $contenttype = $multipart ? 'multipart/form-data' : 'application/json;charset=utf-8';

        $options['headers'] = [
                'Content-Type' => $contenttype,
                'x-goog-api-key' => $this->get_api_key(),
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
        // TODO error handling: check if answer contains "stop", then the LLM will have successfully done something.
        //  If not, we need to do some error handling and return prompt_response::create_from_error(...
        $content = json_decode($result->getContents(), true);

        $textanswer = '';
        foreach ($content['candidates'][0]['content']['parts'] as $part) {
            $textanswer .= $part['text'];
        }
        return prompt_response::create_from_result(
                $this->instance->get_model(),
                new usage(
                        (float) $content['usageMetadata']['totalTokenCount'],
                        (float) $content['usageMetadata']['promptTokenCount'],
                        (float) $content['usageMetadata']['candidatesTokenCount']),
                $textanswer,
        );
    }

    public function get_prompt_data(string $prompttext, array $requestoptions): array {
        $messages = [];
        if (array_key_exists('conversationcontext', $requestoptions)) {
            foreach ($requestoptions['conversationcontext'] as $message) {
                switch ($message['sender']) {
                    case 'user':
                        $role = 'user';
                        break;
                    case 'ai':
                        $role = 'model';
                        break;
                    case 'system':
                        // Gemini does not have a system role. It's just a simple preprompt as user telling the AI how to behave.
                        $role = 'user';
                        break;
                    default:
                        throw new \moodle_exception('Bad message format');
                }
                $messages[] = [
                        'role' => $role,
                        'parts' => [
                                ['text' => $message['message']],
                        ]
                ];
            }
        }
        $messages[] = [
                'role' => 'user',
                'parts' => [
                        ['text' => $prompttext],
                ]
        ];
        return [
                'contents' => $messages,
                'generationConfig' => [
                        'temperature' => $this->instance->get_temperature(),
                        'topP' => $this->instance->get_top_p(),
                ]
        ];
    }

    public function has_customvalue1(): bool {
        return true;
    }

    public function has_customvalue2(): bool {
        return true;
    }

}
