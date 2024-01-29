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
 * Helper
 *
 * @package    local_ai_manager
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_manager;

global $CFG;

require_once($CFG->libdir . '/filelib.php');

/**
 * Helper
 *
 * @package    local_ai_manager
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {

    /** @var Purpose chat */
    const PURPOSE_CHAT = 'chat';

    /** @var Purpose image generation */
    const PURPOSE_IMAGEGENERATION = 'imggen';

    /** @var Purpose text to speech */
    const PURPOSE_TTS = 'tts';

    /** @var Purpose speach to text */
    const PURPOSE_STT = 'stt';

    /**
     * Get an array of all purposes defined.
     *
     * @return array
     */
    public static function get_all_purposes(): array {
        return [self::PURPOSE_CHAT, self::PURPOSE_IMAGEGENERATION, self::PURPOSE_STT, self::PURPOSE_TTS];
    }
}
