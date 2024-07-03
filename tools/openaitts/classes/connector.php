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
 * @package    aitool_openaitts
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace aitool_openaitts;

use local_ai_manager\local\prompt_response;
use local_ai_manager\local\unit;
use local_ai_manager\local\usage;
use Psr\Http\Message\StreamInterface;

/**
 * Connector - whisper
 *
 * @package    aitool_openaitts
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class connector extends \local_ai_manager\base_connector {

    public function __construct(instance $instance) {
        $this->instance = $instance;
    }

    public function get_models_by_purpose(): array {
        return [
                'tts' => ['tts-1'],
        ];
    }

    /**
     * Retrieves the data for the prompt based on the prompt text.
     *
     * @param string $prompttext The prompt text.
     * @return array The prompt data.
     */
    public function get_prompt_data(string $prompttext, array $requestoptions): array {
        $data = [
                'model' => $this->instance->get_model(),
                'input' => $prompttext,
                'voice' => empty($requestoptions['voices'][0]) ? 'alloy' : $requestoptions['voices'][0],
        ];

        return $data;
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
                'filearea' => 'draft',
                'itemid' => $options['itemid'],
                'filepath' => '/',
                'filename' => $options['filename'],
        ];
        $file = $fs->create_file_from_string($fileinfo, $result);

        $filepath = \moodle_url::make_draftfile_url(
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename()
        )->out();

        return prompt_response::create_from_result($this->instance->get_model(), new usage(1.0), $filepath);
    }

    public function get_available_options(): array {
        return [
                'voices' => [
                         ['key' => 'alloy', 'displayname' => 'Alloy'],
                         ['key' => 'echo', 'displayname' => 'Echo'],
                         ['key' => 'fable', 'displayname' => 'Fable'],
                         ['key' => 'onyx', 'displayname' => 'Onyx'],
                         ['key' => 'nova', 'displayname' => 'Nova'],
                         ['key' => 'shimmer', 'displayname' => 'Shimmer'],
                ],
        ];
    }

}
