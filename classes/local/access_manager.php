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
use local_ai_manager\hook\custom_tenant;
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

    public function is_tenant_manager(tenant $tenant = null): bool {
        global $USER;
        if (has_capability('local/ai_manager:managetenants', \context_system::instance())) {
            return true;
        }

        if (is_null($tenant)) {
            $tenant = $this->tenant;
        }

        $customtenant = new custom_tenant($tenant);
        \core\di::get(\core\hook\manager::class)->dispatch($customtenant);

        // In case of default tenant we get system context here, admin should have all capabilities, so we need no admin check.
        $tenantcontext = $tenant->get_context();

        if ($tenantcontext === \context_system::instance()) {
            // If the context of the tenant is systemwide, we distinguish between the capabilities "manage" and "managetenants":
            // If someone has the manage capability on system context, he/she will also have to be member of the tenant to be able
            // to manage it.
            return has_capability('local/ai_manager:manage', $tenantcontext) && $tenant->is_tenant_allowed()
                    && $USER->institution === $tenant->get_identifier();
        }
        return has_capability('local/ai_manager:manage', $tenantcontext) && $tenant->is_tenant_allowed();
    }

    public function require_tenant_member(): void {
        global $USER;
        if (!$this->tenant->is_tenant_allowed()) {
            throw new \moodle_exception('Tenant is not allowed.');
        }
        if ($this->tenant->is_default_tenant() && has_capability('local/ai_manager:use', $this->tenant->get_context())) {
            return;
        }

        $customtenant = new custom_tenant($this->tenant);
        \core\di::get(\core\hook\manager::class)->dispatch($customtenant);

        if (empty($USER->institution) || $USER->institution !== $this->tenant->get_identifier()) {
            throw new \moodle_exception('You must not access information for the tenant '
                    . $this->tenant->get_identifier() . '!');
        }
    }

    public function can_manage_connectorinstance(base_instance $instance) {
        if (has_capability('local/ai_manager:managetenants', \context_system::instance())) {
            return true;
        }
        if ($this->is_tenant_manager(new tenant($instance->get_tenant()))) {
            return has_capability('local/ai_manager:manage', $this->tenant->get_context());
        }
        return false;
    }
}
