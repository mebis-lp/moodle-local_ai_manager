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
class tenant {

    private string $tenantidentifier;

    public function __construct(string $tenantidentifier = '') {
        global $USER;
        if (empty($tenantidentifier)) {
            if (empty($USER->institution)) {
                throw new \moodle_exception('Cannot determine tenant identifier based on the current user');
            }
            $this->tenantidentifier = $USER->institution;
        } else {
            $this->tenantidentifier = $tenantidentifier;
        }
    }

    public function get_tenantidentifier(): string {
        return $this->tenantidentifier;
    }

    public function get_tenant_context(): \context {
        if (empty($this->get_tenantidentifier())) {
            return \context_system::instance();
        }
        $school = new \local_bycsauth\school($this->get_tenantidentifier());
        return \context_coursecat::instance($school->get_school_categoryid());
    }



}
