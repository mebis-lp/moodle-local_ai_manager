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

require_once(dirname(__FILE__) . '/../../config.php');

global $CFG, $DB, $OUTPUT, $PAGE, $USER;

$tenantid = optional_param('tenant', '', PARAM_ALPHANUM);

// Check permissions.
require_login();

if (!empty($tenantid)) {
    $tenant = new \local_ai_manager\local\tenant($tenantid);
    \core\di::set(\local_ai_manager\local\tenant::class, $tenant);
}
$tenant = \core\di::get(\local_ai_manager\local\tenant::class);
$accessmanager = \core\di::get(\local_ai_manager\local\access_manager::class);
$accessmanager->require_tenant_manager();

$url = new moodle_url('/local/ai_manager/purpose_config.php', ['tenant' => $tenant->get_tenantidentifier()]);
$PAGE->set_url($url);
$PAGE->set_context($tenant->get_tenant_context());
$returnurl = new moodle_url('/local/ai_manager/tenant_config.php', ['tenant' => $tenant->get_tenantidentifier()]);

$strtitle = 'SCHULKONFIGURATION';
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);
$PAGE->navbar->add($strtitle);

$purposeconfigform = new purpose_config_form(null, ['returnurl' => $PAGE->url]);
// Will return the config manager for the current user.
$configmanager = \core\di::get(\local_ai_manager\local\config_manager::class);

// Standard form processing if statement.
if ($purposeconfigform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $purposeconfigform->get_data()) {

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strtitle);
    echo $OUTPUT->render_from_template('local_ai_manager/tenantconfignavbar', []);

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
    echo $OUTPUT->heading($strtitle);
    echo $OUTPUT->render_from_template('local_ai_manager/tenantconfignavbar', []);

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
