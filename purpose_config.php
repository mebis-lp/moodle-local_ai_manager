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

/**
 * Configuration page for tenants.
 *
 * @package    local_ai_manager
 * @copyright  2024, ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;
use local_ai_manager\base_purpose;
use local_ai_manager\form\purpose_config_form;
use local_ai_manager\output\tenantnavbar;

require_once(dirname(__FILE__) . '/../../config.php');

global $CFG, $DB, $OUTPUT, $PAGE, $USER;

\local_ai_manager\local\tenant_config_output_utils::setup_tenant_config_page(new moodle_url('/local/ai_manager/purpose_config.php'));
$tenant = \core\di::get(\local_ai_manager\local\tenant::class);
$returnurl = new moodle_url('/local/ai_manager/tenant_config.php', ['tenant' => $tenant->get_tenantidentifier()]);
$purposeconfigform = new purpose_config_form(null, ['returnurl' => $PAGE->url]);
// Will return the config manager for the current user.
$configmanager = \core\di::get(\local_ai_manager\local\config_manager::class);

// Standard form processing if statement.
if ($purposeconfigform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $purposeconfigform->get_data()) {
    foreach (base_purpose::get_all_purposes() as $purpose) {
        if (property_exists($data, base_purpose::get_purpose_tool_config_key($purpose)) && intval($data->{base_purpose::get_purpose_tool_config_key($purpose)}) === 0) {
            $configmanager->unset_config(base_purpose::get_purpose_tool_config_key($purpose));
        }
        if (!empty($data->{base_purpose::get_purpose_tool_config_key($purpose)})) {
            $configmanager->set_config(base_purpose::get_purpose_tool_config_key($purpose),
                    $data->{base_purpose::get_purpose_tool_config_key($purpose)});
        }
    }
    redirect($PAGE->url, 'CONFIG SAVED');
} else {
    echo $OUTPUT->header();
    $tenantnavbar = new tenantnavbar('purpose_config.php');
    echo $OUTPUT->render($tenantnavbar);

    echo $OUTPUT->heading(get_string('configurepurposes', 'local_ai_manager'), 2, 'text-center');
    echo html_writer::div(get_string('purposesdescription', 'local_ai_manager'), 'text-center mb-3');

    $data = new stdClass();
    foreach (base_purpose::get_all_purposes() as $purpose) {
        if (!empty($configmanager->get_config(base_purpose::get_purpose_tool_config_key($purpose)))) {
            $data->{base_purpose::get_purpose_tool_config_key($purpose)} =
                    $configmanager->get_config(base_purpose::get_purpose_tool_config_key($purpose));
        }
    }
    $purposeconfigform->set_data($data);
    $purposeconfigform->display();
}

echo $OUTPUT->footer();
