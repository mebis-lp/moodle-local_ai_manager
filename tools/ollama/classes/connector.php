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


    private float $temperature;

    /**
     * Construct the connector class for ollama
     *
     * @return void
     */
    public function __construct() {
        $this->temperature = floatval(get_config('aitool_ollama', 'temperature'));
    }

    public function get_models(): array {
        return ['tinyllama', 'mixtral'];
    }

    protected function get_endpoint_url(): string {
        return get_config('aitool_ollama', 'url');
    }

    protected function get_api_key(): string {
        return get_config('aitool_ollama', 'apikey');
    }

    public function get_unit(): unit {
        return unit::TOKEN;
    }

    public function execute_prompt_completion(StreamInterface $result, array $options = []): prompt_response {

        $content = json_decode($result->getContents(), true);

        // On cached results there is no prompt token count in the response.
        $prompttokencount = isset($content['prompt_eval_count']) ? $content['prompt_eval_count'] : 0.0;
        $responsetokencount = $content['eval_count'];
        $totaltokencount = $prompttokencount + $responsetokencount;

        return prompt_response::create_from_result($content['model'],
            new usage($totaltokencount, $prompttokencount, $prompttokencount),
            $content['response']);
    }

    /**
     * Retrieves the data for the prompt based on the prompt text.
     *
     * @param string $prompttext The prompt text.
     * @return array The prompt data.
     */
    public function get_prompt_data(string $prompttext): array {
        $data = [
            'model' => $this->get_models(),
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
