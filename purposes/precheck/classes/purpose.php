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

namespace aipurpose_precheck;

use local_ai_manager\base_purpose;

/**
 * Purpose precheck methods.
 *
 * @package    aipurpose_precheck
 * @copyright  2025 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class purpose extends base_purpose {
    public function manipulate_prompt(string $prompt): string {
        // TODO Move preprompt to admin setting
        $prompt = 'Check if the following text wrapped in [[ ... ]] contains personal data. Answer with the exact string "OK" if you could not find anything, and with "NOT OK" if you found anything: ' . '[[' . $prompt . ']]';
        return $prompt;
    }

}
