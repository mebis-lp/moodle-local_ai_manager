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

namespace local_ai_manager\hook;

use local_ai_manager\local\tenant;

/**
 * Hook for providing information for the rights config table filter.
 *
 * This hook will be dispatched when it's rendering the rights config table.
 *
 * @package    local_ai_manager
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\core\attribute\label('Allows plugins to provide a list of options for the filter of the user rights management table in the tenant config.')]
#[\core\attribute\tags('local_ai_manager')]
class usertable_filter {

    private array $filteroptions = [];

    /**
     * Constructor for the hook.
     */
    public function __construct(private tenant $tenant) {
    }

    public function get_tenant(): tenant {
        return $this->tenant;
    }

    public function get_filter_options(): array {
        return $this->filteroptions;
    }

    public function set_filter_options(array $filteroptions): void {
        $this->filteroptions = $filteroptions;
    }

}
