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

/**
 * Class for managing the configuration of tenants.
 *
 * @package    local_ai_manager
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class access_manager {

    /**
     * Creates the access_manager object.
     *
     * @param tenant $tenant the tenant the access manager should use
     */
    public function __construct(
            /** @var tenant $tenant the tenant the access manager should use */
            private readonly tenant $tenant
    ) {
    }

    /**
     * Requires the current user to be a manager of the current tenant.
     *
     * @throws \moodle_exception in case of the current user does not have sufficient permissions for managing the current tenant
     */
    public function require_tenant_manager(): void {
        if (!$this->is_tenant_manager()) {
            // phpcs:disable moodle.Commenting.TodoComment.MissingInfoInline
            // TODO Make a clean require_capability_exception out of this.
            // phpcs:enable moodle.Commenting.TodoComment.MissingInfoInline
            throw new \moodle_exception('You do not have the rights to manage the AI tools or the tenant is not allowed'
                    . ' to use them');
        }
    }

    /**
     * Determines if the current user is a tenant manager.
     *
     * @param ?tenant $tenant the tenant to use, if not passed or null the currently used tenant is being used
     * @return bool true if the current user is a tenant manager
     */
    public function is_tenant_manager(?tenant $tenant = null): bool {
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
            $tenantfield = get_config('local_ai_manager', 'tenantcolumn');
            return has_capability('local/ai_manager:manage', $tenantcontext) && $tenant->is_tenant_allowed()
                    && $USER->{$tenantfield} === $tenant->get_identifier();
        }
        return has_capability('local/ai_manager:manage', $tenantcontext) && $tenant->is_tenant_allowed();
    }

    /**
     * Requires the current user to be a member of the currently set tenant.
     *
     * @throws \moodle_exception if the tenant is not allowed or the user must not use this tenant
     */
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

        $tenantfield = get_config('local_ai_manager', 'tenantcolumn');
        if (empty($USER->{$tenantfield}) || $USER->{$tenantfield} !== $this->tenant->get_identifier()) {
            throw new \moodle_exception('You must not access information for the tenant '
                    . $this->tenant->get_identifier() . '!');
        }
    }

    /**
     * Helper function to determine if the current user has the capability to manage a connector instance.
     *
     * @param base_instance $instance The connector instance the capability should be checked for
     * @return bool true if the current user is allowed to manage the instance
     */
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
