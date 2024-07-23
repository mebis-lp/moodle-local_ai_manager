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

use local_bycsauth\school;
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

    public const ROLE_BASIC = 1;

    public const ROLE_EXTENDED = 2;

    public const ROLE_UNLIMITED = 3;

    private false|stdClass $record;

    private int $role;

    private bool $locked;
    private bool $confirmed;

    public function __construct(private readonly int $userid) {
        $this->load();
    }

    public function load(): void {
        global $DB;
        $this->record = $DB->get_record('local_ai_manager_userinfo', ['userid' => $this->userid]);
        $this->role = !empty($this->record->role) ? $this->record->role : $this->get_default_role();
        $this->locked = !empty($this->record->locked);
        $this->confirmed = !empty($this->record->confirmed);
    }

    public function get_default_role() {
        global $DB;
        // TODO Extract this into a hook.
        // TODO Make this more performant
        $user = \core_user::get_user($this->userid);
        $accessmanager = \core\di::get(access_manager::class);
        if (\core\di::get(tenant::class)->is_default_tenant()) {
            return $accessmanager->is_tenant_manager() ? self::ROLE_UNLIMITED : self::ROLE_BASIC;
        }
        $idmteacherrole = $DB->get_record('role', ['shortname' => 'idmteacher']);
        $coordinatorrole = $DB->get_record('role', ['shortname' => 'schulkoordinator']);
        $school = new school($user->institution);
        if (user_has_role_assignment($this->userid, $coordinatorrole->id,
                \context_coursecat::instance($school->get_school_categoryid())->id)) {
            return self::ROLE_UNLIMITED;
        } else if (user_has_role_assignment($this->userid, $idmteacherrole->id, \context_system::instance()->id)) {
            return self::ROLE_EXTENDED;
        } else {
            return self::ROLE_BASIC;
        }
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
        $newrecord->role = $this->role;
        $newrecord->locked = $this->locked ? 1 : 0;
        $newrecord->confirmed = $this->confirmed ? 1 : 0;
        $newrecord->timemodified = time();
        if ($this->record) {
            $newrecord->id = $this->record->id;
            $DB->update_record('local_ai_manager_userinfo', $newrecord);
        } else {
            $newrecord->id = $DB->insert_record('local_ai_manager_userinfo', $newrecord);
        }
        $this->record = $newrecord;
    }

    public function set_role(int $role): void {
        if (!in_array($role, [self::ROLE_BASIC, self::ROLE_EXTENDED, self::ROLE_UNLIMITED])) {
            throw new \coding_exception('Wrong role specified, use one of ROLE_BASIC, ROLE_EXTENDED or ROLE_UNLIMITED');
        }
        $this->role = $role;
    }

    public function set_locked(bool $locked): void {
        $this->locked = $locked;
    }

    public function set_confirmed(bool $confirmed): void {
        $this->confirmed = $confirmed;
    }

    public function get_role(): int {
        return $this->role;
    }

    public function is_locked(): bool {
        return $this->locked;
    }

    public function is_confirmed(): bool {
        return $this->confirmed;
    }

    public static function get_tenant_for_user($userid): ?tenant {
        $user = \core_user::get_user($userid);
        if (empty($user->institution)) {
            return null;
        }
        return new tenant($user->institution);
    }

    public static function get_role_as_string(int $role): string {
        switch ($role) {
            case 1:
                return 'role_basic';
            case 2:
                return 'role_extended';
            case 3:
                return 'role_unlimited';
            default:
                throw new \coding_exception('Role integers must be 1, 2 or 3');
        }
    }

}
