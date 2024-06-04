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

/**
 * Class for managing the configuration of tenants.
 *
 * @package    local_ai_manager
 * @copyright  2024, ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class access_manager {

    public function __construct(private readonly tenant $tenant) {
    }

    public function require_tenant_manager(): void {
        // TODO Convert this into a hook.
        if (empty($this->tenant->get_tenantidentifier())) {
            require_admin();
        }
        $school = new school($this->tenant->get_tenantidentifier());
        if (!$school->record_exists()) {
            throw new \moodle_exception('Invalid tenant "' . $this->tenant->get_tenantidentifier() . '"!');
        }
        $tenantcontext = $this->tenant->get_tenant_context();
        require_capability('local/ai_manager:manage', $tenantcontext);
    }

    public function require_tenant_member(): void {
        global $USER;
        $school = new school($this->tenant->get_tenantidentifier());
        if (!$school->record_exists()) {
            throw new \moodle_exception('Invalid tenant "' . $this->tenant->get_tenantidentifier() . '"!');
        }
        if (empty($USER->institution) || $USER->institution !== $this->tenant->get_tenantidentifier()) {
            throw new \moodle_exception('You must not access information for the tenant '
                    . $this->tenant->get_tenantidentifier() . '!');
        }

    }
}
