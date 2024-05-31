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

namespace local_ai_manager\local;

use local_ai_manager\base_purpose;
use stdClass;

/**
 * Data object class for handling user usage information when using an AI tool.
 *
 * @package    local_ai_manager
 * @copyright  2024, ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class userusage {

    public const UNLIMITED_REQUESTS_PER_USER = 999999;

    public const MAX_REQUESTS_DEFAULT_PERIOD = DAYSECS;

    public const MAX_REQUESTS_MIN_PERIOD = DAYSECS;

    private false|stdClass $record;
    private int $currentusage;
    private int $lastreset;

    public function __construct(private readonly base_purpose $purpose, private readonly int $userid) {
        $this->load();
    }

    public function load(): void {
        global $DB;
        $this->record = $DB->get_record('local_ai_manager_userusage',
                ['purpose' => $this->purpose->get_plugin_name(), 'userid' => $this->userid]);
        $this->currentusage = !empty($this->record->currentusage) ? $this->record->currentusage : 0;
        $this->lastreset = !empty($this->record->lastreset) ? $this->record->lastreset : 0;
    }

    public function record_exists(): bool {
        return !empty($this->record);
    }

    public function get_userid(): int {
        return $this->userid;
    }

    public function store() {
        global $DB;
        $this->record = $DB->get_record('local_ai_manager_userusage',
                ['purpose' => $this->purpose->get_plugin_name(), 'userid' => $this->userid]);
        $newrecord = new stdClass();
        $newrecord->purpose = $this->purpose->get_plugin_name();
        $newrecord->userid = $this->userid;
        $newrecord->currentusage = $this->currentusage;
        $newrecord->lastreset = $this->lastreset;
        $newrecord->timemodified = time();
        if ($this->record) {
            $newrecord->id = $this->record->id;
            $DB->update_record('local_ai_manager_userusage', $newrecord);
        } else {
            $newrecord->id = $DB->insert_record('local_ai_manager_userusage', $newrecord);
        }
        $this->record = $newrecord;
    }

    public function get_currentusage(): int {
        return $this->currentusage;
    }

    public function set_currentusage(int $currentusage): void {
        $this->currentusage = $currentusage;
    }

    public function get_lastreset(): int {
        return $this->lastreset;
    }

    public function set_lastreset(int $lastreset): void {
        $this->lastreset = $lastreset;
    }

}
