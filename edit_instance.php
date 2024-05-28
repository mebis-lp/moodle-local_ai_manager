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

global $CFG, $DB, $PAGE, $USER;

$tenantid = optional_param('tenant', '', PARAM_ALPHANUM);
$connectorname = optional_param('connectorname', '', PARAM_TEXT);
$id = optional_param('id', 0, PARAM_INT);
$del = optional_param('del', 0, PARAM_INT);

$url = new moodle_url('/local/ai_manager/edit_instance.php');
$PAGE->set_url($url);

$returnurl = new moodle_url('/local/ai_manager/instances_config.php');

// Check permissions.
require_login();

if (isguestuser()) {
    throw new moodle_exception('guestsarenotallowed', '', $returnurl);
}

if (empty($tenantid)) {
    // Will throw an exception if no tenant can be found.
    $tenant = \core\di::get(tenant::class);
} else {
    $tenant = new tenant($tenantid);
    \core\di::set(tenant::class, $tenant);
}
$tenantid = $tenant->get_tenantidentifier();

$school = new \local_bycsauth\school($tenantid);
if (!$school->record_exists()) {
    throw new moodle_exception('Invalid tenant "' . $tenantid . '"!');
}
$schoolcategorycontext = \context_coursecat::instance($school->get_school_categoryid());
$coordinatorrole = $DB->get_record('role', ['shortname' => 'schulkoordinator']);
if (!user_has_role_assignment($USER->id, $coordinatorrole->id, $schoolcategorycontext->id) && !is_siteadmin()) {
    throw new moodle_exception('Only admins and ByCS admins have access to this page');
}

$PAGE->set_context($schoolcategorycontext);

$strtitle = 'INSTANZ BEARBEITEN';
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);
$PAGE->navbar->add($strtitle);

$factory = \core\di::get(\local_ai_manager\local\connector_factory::class);

if (!empty($del)) {
    if (empty($id)) {
        throw new moodle_exception('You have to specify the id of the instance to delete');
    }

    $factory->get_connector_instance_by_id($id)->delete();
    redirect($returnurl, 'Instance with id ' . $id . ' deleted');
}

if (!empty($id)) {
    $connectorinstance = $factory->get_connector_instance_by_id($id);
    $connectorname = $connectorinstance->get_connector();
} else {
    if (empty($connectorname) || !in_array($connectorname, \local_ai_manager\plugininfo\aitool::get_enabled_plugins())) {
        throw new moodle_exception('No valid connector specified');
    }
    $connectorinstance = $factory->get_connector_by_connectorname($connectorname);
}

$editinstanceform = new \local_ai_manager\form\edit_instance_form(new moodle_url('/local/ai_manager/edit_instance.php',
        ['id' => $id, 'connectorname' => $connectorname]),
        ['id' => $id, 'tenant' => $tenant->get_tenantidentifier(), 'connector' => $connectorname, 'returnurl' => $PAGE->url]);

// Standard form processing if statement.
if ($editinstanceform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $editinstanceform->get_data()) {

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strtitle);
    // As the restore process is being done asynchronously, the user should get notified, that the process has successfully been
    // started or that trying to trigger it caused an error.

    $connectorinstance->store_formdata($data);
    redirect(new moodle_url('/local/ai_manager/instances_config.php'), 'DATA SAVED', '');
} else {
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strtitle);

    $editinstanceform->set_data($connectorinstance->get_formdata());
    $editinstanceform->display();
}

echo $OUTPUT->footer();
