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

namespace aitool_openaitts;

use local_ai_manager\local\prompt_response;
use local_ai_manager\local\unit;
use local_ai_manager\local\usage;
use local_ai_manager\request_options;
use Psr\Http\Message\StreamInterface;

/**
 * Connector for OpenAI TTS.
 *
 * @package    aitool_openaitts
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class connector extends \local_ai_manager\base_connector {

    #[\Override]
    public function get_models_by_purpose(): array {
        return [
                'tts' => ['tts-1', 'gpt-4o-mini-tts'],
        ];
    }

    #[\Override]
    public function get_prompt_data(string $prompttext, request_options $requestoptions): array {
        $options = $requestoptions->get_options();
        $data = [
                'input' => $prompttext,
                'voice' => empty($options['voices'][0]) ? 'alloy' : $options['voices'][0],
        ];
        if (!$this->instance->azure_enabled()) {
            // If azure is enabled, the model will be preconfigured in the azure resource, so we do not need to send it.
            //$data['model'] = $this->instance->get_model();
            $data['model'] = 'gpt-4o-mini-tts';
            if ($this->instance->get_model() === 'gpt-4o-mini-tts') {
                $data['instructions'] = 'Shout this text in a very high pitched voice';
            }
        } else {
            // OpenAI via Azure expects the model to be sent despite being preconfigured in the resource. So we hardcode "tts".
            $data['model'] = 'tts';
        }

        return $data;
    }

    #[\Override]
    protected function get_headers(): array {
        $headers = parent::get_headers();
        if (!$this->instance->azure_enabled()) {
            // If azure is not enabled, we just use the default headers for the OpenAI API.
            return $headers;
        }
        if (in_array('Authorization', array_keys($headers))) {
            unset($headers['Authorization']);
            $headers['api-key'] = $this->instance->get_apikey();
        }
        return $headers;
    }

    #[\Override]
    public function get_unit(): unit {
        return unit::COUNT;
    }

    #[\Override]
    public function execute_prompt_completion(StreamInterface $result, request_options $requestoptions): prompt_response {
        global $USER;
        $options = $requestoptions->get_options();
        $fs = get_file_storage();
        $fileinfo = [
                'contextid' => \context_user::instance($USER->id)->id,
                'component' => 'user',
                'filearea' => 'draft',
                'itemid' => $options['itemid'],
                'filepath' => '/',
                'filename' => $options['filename'],
        ];



        //$file = $fs->create_file_from_string($fileinfo, $result);

        $tmpdir = make_request_directory();
        $tempfile = $tmpdir . '/temp.mp3';
        file_put_contents($tempfile, $result);

        $mp3 = new php_mp3($tempfile);
        $mp3->striptags();
        $mp3->mergeBehind($mp3);
        $mp3->mergeBehind($mp3);
        $mp3->save($tempfile);;

        $file = $fs->create_file_from_pathname($fileinfo, $tempfile);

        $filepath = \moodle_url::make_draftfile_url(
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename()
        )->out();

        return prompt_response::create_from_result($this->instance->get_model(), new usage(1.0), $filepath);
    }

    #[\Override]
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
