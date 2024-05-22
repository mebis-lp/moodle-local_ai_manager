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
 * Connector - dalle
 *
 * @package    aitool_dalle
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace aitool_dalle;

use aitool_whisper_1\language_codes;
use dml_exception;
use local_ai_manager\base_connector;
use local_ai_manager\local\prompt_response;
use local_ai_manager\local\unit;
use local_ai_manager\local\usage;
use Psr\Http\Message\StreamInterface;

/**
 * Connector - dalle
 *
 * @package    aitool_dalle
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class connector extends base_connector {

    private float $temperature;


    /**
     * Construct the connector class for dalle
     *
     * @return void
     * @throws dml_exception
     */
    public function __construct() {
        $this->temperature = floatval(get_config('aitool_dalle', 'temperature'));
    }

    public function get_models(): array {
        return ['dall-e-2', 'dall-e-3'];
    }

    protected function get_endpoint_url(): string {
        return 'https://api.openai.com/v1/images/generations';
    }

    protected function get_api_key(): string {
        return get_config('aitool_dalle', 'openaiapikey');
    }


    /**
     * Retrieves the data for the prompt based on the prompt text.
     *
     * @param string $prompttext The prompt text.
     * @return array The prompt data.
     */
    public function get_prompt_data(string $prompttext): array {
        // TODO we do not have options here yet, but apparently will need them both here and in the execute_promptcompletion method,
        //  so we probably need this in the connector object inserted
        return [
                'prompt' => $prompttext,
                'size' => (empty($options->imagesize)) ? "256x256" : $options->imagesize,
                'n' => (empty($options->numberofresponses)) ? 1 : $options->numberofresponses,
        ];
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
        $content = json_decode($result->getContents(), true);
        $fs = get_file_storage();
        $fileinfo = [
                'contextid' => \context_user::instance($USER->id)->id,
                'component' => 'user',
                'filearea'  => 'draft',
                'itemid'    => $options['itemid'],
                'filepath'  => '/',
                'filename'  => $options['filename'],
        ];
        $file = $fs->create_file_from_url($fileinfo, $content['data'][0]['url'], [], true);

        $filepath = \moodle_url::make_draftfile_url(
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename()
        )->out();

        return prompt_response::create_from_result($this->get_models(), new usage(1.0), $filepath);
    }


}
