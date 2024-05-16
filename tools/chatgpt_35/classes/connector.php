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
 * Connector - chatgpt_35.
 *
 * @package    aitool_chatgpt_35
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace aitool_chatgpt_35;

use local_ai_manager\local\prompt_response;
use local_ai_manager\local\unit;
use local_ai_manager\local\usage;
use Psr\Http\Message\StreamInterface;

/**
 * Connector - chatgpt_35
 *
 * @package    aitool_chatgpt_35
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class connector extends \local_ai_manager\base_connector {

    protected float $temperature;

    /**
     * Construct for the connector class for chatgpt_35
     */
    public function __construct() {
        $this->temperature = floatval(get_config('aitool_chatgpt_35', 'temperature'));
    }

    public function get_model_name(): string {
        return 'gpt-3.5-turbo';
    }

    protected function get_endpoint_url(): string {
        return 'https://api.openai.com/v1/chat/completions';
    }

    protected function get_api_key(): string {
        return get_config('aitool_chatgpt_35', 'openaiapikey');
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

    public function get_prompt_data(string $prompttext): array {
        return [
                'model' => $this->get_model_name(),
                'temperature' => $this->temperature,
                'messages' => [
                        [
                                'role' => 'system',
                                'content' => $prompttext
                        ],
                ],
        ];
    }

    public function has_customvalue1(): bool {
        return true;
    }

    public function has_customvalue2(): bool {
        return true;
    }

    public function get_temperature(): float {
        return $this->temperature;
    }



}
