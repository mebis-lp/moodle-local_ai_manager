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

use local_ai_manager\hook\custom_tenant;
use moodle_exception;
use stdClass;

/**
 * Data object class for handling usage information when using an AI tool.
 *
 * @package    local_ai_manager
 * @copyright  2024, ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tenant {

    public const DEFAULT_IDENTIFIER = 'default';

    private string $identifier;

    /**
     * Tenant class constructor.
     *
     * @param string $identifier
     * @return void
     */
    public function __construct(string $identifier = '') {
        global $USER;
        if (empty($identifier)) {
            $identifier = !empty($USER->institution) ? $USER->institution : '';
            if (empty($identifier)) {
                $identifier = self::DEFAULT_IDENTIFIER;
            }
        }
        $this->identifier = $identifier;
    }

    /**
     * Get the tenant identifier.
     *
     * @return string
     */
    public function get_identifier(): string {
        return $this->identifier;
    }

    public function is_default_tenant(): bool {
        return $this->identifier === self::DEFAULT_IDENTIFIER;
    }

    /**
     * Get the tenant context.
     *
     * @return \context the context the tenant is associated with
     */
    public function get_context(): \context {
        $customtenant = new custom_tenant($this);
        \core\di::get(\core\hook\manager::class)->dispatch($customtenant);
        return $customtenant->get_tenant_context();

        /*$school = new \local_bycsauth\school($this->get_identifier());
        return \context_coursecat::instance($school->get_school_categoryid());*/
    }

    public function is_tenant_allowed(): bool {
        $restricttenants = !empty(get_config('local_ai_manager', 'restricttenants'));
        if (!$restricttenants) {
            return true;
        }
        $allowedtenantsconfig = get_config('local_ai_manager', 'allowedtenants');
        $allowedtenantsconfig = explode(PHP_EOL, $allowedtenantsconfig);
        foreach ($allowedtenantsconfig as $tenant) {
            if ($this->get_identifier() === trim($tenant)) {
                return true;
            }
        }
        return false;
    }

    public function get_fullname(): string {
        $customtenant = new custom_tenant($this);
        \core\di::get(\core\hook\manager::class)->dispatch($customtenant);
        return $customtenant->get_fullname();
    }

    public function get_defaultcontext(): \context {
        return \context_system::instance();
    }

    public function get_defaultfullname(): string {
        return $this->identifier === self::DEFAULT_IDENTIFIER
                ? get_string('defaulttenantname', 'local_ai_manager')
                : $this->identifier;
    }
}
