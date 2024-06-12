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
 * @package    aitool_chatgpt
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace aitool_chatgpt;

use local_ai_manager\local\prompt_response;
use local_ai_manager\local\unit;
use local_ai_manager\local\usage;
use Psr\Http\Message\StreamInterface;

/**
 * Connector - chatgpt
 *
 * @package    aitool_chatgpt
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class connector extends \local_ai_manager\base_connector {

    public function __construct(instance $instance) {
        $this->instance = $instance;
    }

    public function get_models_by_purpose(): array {
        $chatgptmodels = ['gpt-3.5-turbo', 'gpt-4-turbo', 'gpt-4o'];
        return [
                'chat' => $chatgptmodels,
                'feedback' => $chatgptmodels,
                'singleprompt' => $chatgptmodels,
                'translate' => $chatgptmodels,
        ];
    }

    public function get_unit(): unit {
        return unit::TOKEN;
    }

    public function execute_prompt_completion(StreamInterface $result, array $options = []): prompt_response {
        // TODO error handling: check if answer contains "stop", then the LLM will have successfully done something.
        //  If not, we need to do some error handling and return prompt_response::create_from_error(...
        $content = json_decode($result->getContents(), true);

        return prompt_response::create_from_result(
                $content['model'],
                new usage(
                        (float) $content['usage']['total_tokens'],
                        (float) $content['usage']['prompt_tokens'],
                        (float) $content['usage']['completion_tokens']),
                $content['choices'][0]['message']['content']
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
                        $role = 'assistant';
                        break;
                    case 'system':
                        $role = 'system';
                        break;
                    default:
                        throw new \moodle_exception('Bad message format');
                }
                $messages[] = [
                        'role' => $role,
                        'content' => $message['message'],
                ];
            }
        }
        $messages[] = ['role' => 'user', 'content' => $prompttext];
        return [
                'model' => $this->instance->get_model(),
                'temperature' => $this->instance->get_temperature(),
                'top_p' => $this->instance->get_top_p(),
                'messages' => $messages,
        ];
    }

    public function has_customvalue1(): bool {
        return true;
    }

    public function has_customvalue2(): bool {
        return true;
    }

}
