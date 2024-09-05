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
 * Purpose genai methods
 *
 * @package    aipurpose_genai
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace aipurpose_genai;

use local_ai_manager\base_purpose;

/**
 * Purpose genai methods
 *
 * @package    aipurpose_genai
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class purpose extends base_purpose {

    /**
     * Get the request options.
     *
     * @param array $options
     * @return array
     */
    #[\Override]
    public function get_request_options(array $options): array {

        if (array_key_exists('messages', $options)) {
            $messages = [];
            foreach ($options['messages'] as $message) {
                switch ($message['role']) {
                    case 'user':
                        $messages[] = ['sender' => 'user', 'message' => $message['content']];
                        break;
                    case 'system':
                        $messages[] = ['sender' => 'system', 'message' => $message['content']];
                        break;
                }
            }
            return ['conversationcontext' => $messages];
        }
        return [];
    }

    /**
     * Get the additional purpose options
     *
     * @return array
     */
    #[\Override]
    public function get_additional_purpose_options(): array {
        return ['messages' => base_purpose::PARAM_ARRAY];
    }
}
