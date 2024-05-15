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

use stdClass;

/**
 * Data object class for handling usage information when using an AI tool.
 *
 * @package    local_ai_manager
 * @copyright  2024, ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class userinfo {

    public const ROLE_UNPRIVILEGED = 1;

    public const ROLE_PRIVILEGED = 2;

    public const ROLE_ADMIN = 3;

    private false|stdClass $record;

    private int $currentusage;

    private int $role;

    private bool $locked;

    public function __construct(private readonly int $userid) {
        $this->load();
    }

    public function load(): void {
        global $DB;
        $this->record = $DB->get_record('local_ai_manager_userinfo', ['userid' => $this->userid]);
        $this->currentusage = !empty($this->record->currentusage) ? $this->record->currentusage : 0;
        $this->role = !empty($this->record->role) ? $this->record->role : self::ROLE_UNPRIVILEGED;
        $this->locked = !empty($this->record->locked);
    }

    public function record_exists(): bool {
        return !empty($this->record);
    }

    public function get_userid(): int {
        return $this->userid;
    }

    public function store() {
        global $DB;
        $this->record = $DB->get_record('local_ai_manager_userinfo', ['userid' => $this->userid]);
        $newrecord = new stdClass();
        $newrecord->userid = $this->userid;
        $newrecord->currentusage = $this->currentusage;
        $newrecord->role = $this->role;
        $newrecord->locked = $this->locked ? 1 : 0;
        $newrecord->timemodified = time();
        if ($this->record) {
            $newrecord->id = $this->record->id;
            $DB->update_record('local_ai_manager_userinfo', $newrecord);
        } else {
            $newrecord->id = $DB->insert_record('local_ai_manager_userinfo', $newrecord);
        }
        $this->record = $newrecord;
    }

    public function set_currentusage(int $currentusage): void {
        $this->currentusage = $currentusage;
    }

    public function set_role(int $role): void {
        $this->role = $role;
    }

    public function set_locked(bool $locked): void {
        $this->locked = $locked;
    }

    public function get_currentusage(): int {
        return $this->currentusage;
    }

    public function get_role(): int {
        return $this->role;
    }

    public function is_locked(): bool {
        return $this->locked;
    }





}
