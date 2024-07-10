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

use block_mbsnewcourse\local\mbsnewcourse;
use block_mbsnewcourse\form\mbs_restore_form;
use core\output\notification;
use local_ai_manager\local\tenant;

require_once(dirname(__FILE__) . '/../../config.php');

global $CFG, $DB, $PAGE, $OUTPUT, $USER;

$tenantid = optional_param('tenant', '', PARAM_ALPHANUM);
$connectorname = optional_param('connectorname', '', PARAM_TEXT);
$id = optional_param('id', 0, PARAM_INT);
$del = optional_param('del', 0, PARAM_INT);

\local_ai_manager\local\tenant_config_output_utils::setup_tenant_config_page(new moodle_url('/local/ai_manager/edit_instance.php'));

$factory = \core\di::get(\local_ai_manager\local\connector_factory::class);
$tenant = \core\di::get(tenant::class);
$returnurl = new moodle_url('/local/ai_manager/tenant_config.php', ['tenant' => $tenant->get_tenantidentifier()]);

if (!empty($del)) {
    if (empty($id)) {
        throw new moodle_exception('You have to specify the id of the instance to delete');
    }

    $factory->get_connector_instance_by_id($id)->delete();
    // After deleteing we have to remove all purpose assignments to this instance, if there are any.
    $configmanager = \core\di::get(\local_ai_manager\local\config_manager::class);
    foreach ($configmanager->get_purpose_config() as $purpose => $instanceid) {
        if (intval($instanceid) === $id) {
            $configmanager->unset_config(\local_ai_manager\base_purpose::get_purpose_tool_config_key($purpose));
        }
    }
    redirect($returnurl, 'Instance with id ' . $id . ' deleted');
}

if (!empty($id)) {
    $connectorinstance = $factory->get_connector_instance_by_id($id);
    $connectorname = $connectorinstance->get_connector();
} else {
    if (empty($connectorname) || !in_array($connectorname, \local_ai_manager\plugininfo\aitool::get_enabled_plugins())) {
        throw new moodle_exception('No valid connector specified');
    }
    $connectorinstance = $factory->get_new_instance($connectorname);
}

$editinstanceform = new \local_ai_manager\form\edit_instance_form(new moodle_url('/local/ai_manager/edit_instance.php',
        ['id' => $id, 'connectorname' => $connectorname]),
        ['id' => $id, 'tenant' => $tenant->get_tenantidentifier(), 'connector' => $connectorname]);

// Standard form processing if statement.
if ($editinstanceform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $editinstanceform->get_data()) {
    $connectorinstance->store_formdata($data);
    redirect(new moodle_url('/local/ai_manager/tenant_config.php', ['tenant' => $tenant->get_tenantidentifier()]), 'DATA SAVED',
            '');
} else {
    echo $OUTPUT->header();
    echo html_writer::start_div('w-75 d-flex flex-column align-items-center ml-auto mr-auto');
    echo $OUTPUT->render_from_template('local_ai_manager/edit_instance_heading',
            [
                    'heading' => $OUTPUT->heading(get_string('configureaitool', 'local_ai_manager')),
                    'showdeletebutton' => !empty($id),
                    'deleteurl' => new moodle_url('/local/ai_manager/edit_instance.php', ['id' => $id, 'del' => 1]),
            ]);
    $editinstanceform->set_data($connectorinstance->get_formdata());
    echo html_writer::start_div('w-100');
    $editinstanceform->display();
    echo html_writer::end_div();
    echo html_writer::end_div();
}

echo $OUTPUT->footer();
