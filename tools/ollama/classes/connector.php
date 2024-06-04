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

use local_ai_manager\local\prompt_response;
use local_ai_manager\local\unit;
use local_ai_manager\local\usage;
use Psr\Http\Message\StreamInterface;

/**
 * Connector - ollama
 *
 * @package    aitool_ollama
 * @copyright  ISB Bayern, 2024
 * @author     Stefan Hanauska <stefan.hanauska@csg-in.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class connector extends \local_ai_manager\base_connector {

    public function __construct(instance $instance) {
        $this->instance = $instance;
    }

    public function get_models_by_purpose(): array {
        return [
                'chat' => ['gemma', 'llama3', 'mistral', 'codellama', 'qwen', 'phi3', 'mixtral', 'dolphin-mixtral', 'llava',
                        'tinyllama'],
                'feedback' => ['gemma', 'llama3', 'mistral', 'codellama', 'qwen', 'phi3', 'mixtral', 'dolphin-mixtral', 'llava',
                        'tinyllama'],
                'singleprompt' => ['gemma', 'llama3', 'mistral', 'codellama', 'qwen', 'phi3', 'mixtral', 'dolphin-mixtral', 'llava',
                        'tinyllama'],
                'translate' => ['gemma', 'llama3', 'mistral', 'codellama', 'qwen', 'phi3', 'mixtral', 'dolphin-mixtral', 'llava',
                        'tinyllama'],
        ];
    }

    public function get_unit(): unit {
        return unit::TOKEN;
    }

    public function execute_prompt_completion(StreamInterface $result, array $options = []): prompt_response {

        $content = json_decode($result->getContents(), true);

        // On cached results there is no prompt token count in the response.
        $prompttokencount = isset($content['prompt_eval_count']) ? $content['prompt_eval_count'] : 0.0;
        $responsetokencount = isset($content['eval_count']) ? $content['eval_count'] : 0.0;
        $totaltokencount = $prompttokencount + $responsetokencount;

        return prompt_response::create_from_result($content['model'],
                new usage($totaltokencount, $prompttokencount, $prompttokencount),
                $content['message']['content']);
    }

    /**
     * Retrieves the data for the prompt based on the prompt text.
     *
     * @param string $prompttext The prompt text.
     * @return array The prompt data.
     */
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
        $data = [
                'model' => $this->instance->get_model(),
                'messages' => $messages,
                'stream' => false,
                'keep_alive' => '60m',
                'options' => [
                        'temperature' => $this->instance->get_temperature(),
                ],
        ];
        return $data;
    }
}
