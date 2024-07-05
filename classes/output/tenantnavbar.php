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

namespace local_ai_manager\output;

use renderable;
use renderer_base;
use stdClass;

/**
 * Navbar for tenant config pages.
 *
 * @package    local_ai_manager
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tenantnavbar implements renderable, \templatable {
    public function export_for_template(renderer_base $output): stdClass {
        $data = new stdClass();
        $tenant = \core\di::get(\local_ai_manager\local\tenant::class);
        $data->showstatistics = has_capability('local/ai_manager:viewstatistics', $tenant->get_tenant_context());
        return $data;
    }
}
