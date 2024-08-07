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

use local_ai_manager\base_instance;
use local_bycsauth\school;
use stdClass;

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
        if (!$this->is_tenant_manager()) {
            // TODO Make a clean require_capability_exception out of this
            throw new \moodle_exception('You do not have the rights to manage the AI tools or the tenant is not allowed to use them');
        }
    }

    public function is_tenant_manager(): bool {
        // TODO Convert this into a hook.
        if (has_capability('local/ai_manager:managetenants', \context_system::instance())) {
            return true;
        }
        if (!$this->tenant->is_default_tenant()) {
            $school = new school($this->tenant->get_tenantidentifier());
            if (!$school->record_exists()) {
                throw new \moodle_exception('Invalid tenant "' . $this->tenant->get_tenantidentifier() . '"!');
            }
        }
        // In case of default tenant we get system context here, admin should have all capabilities, so we need no admin check.
        $tenantcontext = $this->tenant->get_tenant_context();
        return has_capability('local/ai_manager:manage', $tenantcontext) && $this->tenant->is_tenant_allowed();
    }

    public function require_tenant_member(): void {
        global $USER;
        if (!$this->tenant->is_tenant_allowed()) {
            throw new \moodle_exception('Tenant is not allowed.');
        }
        if ($this->tenant->is_default_tenant() && has_capability('local/ai_manager:use', $this->tenant->get_tenant_context())) {
            return;
        }
        $school = new school($this->tenant->get_tenantidentifier());
        if (!$school->record_exists()) {
            throw new \moodle_exception('Invalid tenant "' . $this->tenant->get_tenantidentifier() . '"!');
        }
        if (empty($USER->institution) || $USER->institution !== $this->tenant->get_tenantidentifier()) {
            throw new \moodle_exception('You must not access information for the tenant '
                    . $this->tenant->get_tenantidentifier() . '!');
        }
    }

    public function can_manage_connectorinstance(base_instance $instance, stdClass $USER) {
        if (has_capability('local/ai_manager:managetenants', \context_system::instance())) {
            return true;
        }
        if ($instance->get_tenant() === $this->tenant->get_tenantidentifier()) {
            return has_capability('local/ai_manager:manage', $this->tenant->get_tenant_context());
        }
        return false;
    }
}
