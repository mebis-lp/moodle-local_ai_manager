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

require_once(dirname(__FILE__) . '/../../config.php');

global $CFG, $DB, $PAGE, $USER;

$tenant = optional_param('tenant', '', PARAM_ALPHANUM);
$connector = optional_param('connector', '', PARAM_TEXT);
$id = optional_param('id', 0, PARAM_INT);

$url = new moodle_url('/local/ai_manager/edit_instance.php');
$PAGE->set_url($url);

$returnurl = new moodle_url('/course/index.php');

// Check permissions.
require_login();

if (isguestuser()) {
    throw new moodle_exception('guestsarenotallowed', '', $returnurl);
}

if (empty($tenant)) {
    $tenant = $USER->institution;
}
if (empty($tenant)) {
    throw new moodle_exception('No tenant could be found. Please specify the "tenant" parameter.');
}

$school = new \local_bycsauth\school($tenant);
if (!$school->record_exists()) {
    throw new moodle_exception('Invalid tenant "' . $tenant . '"!');
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

if (!empty($id)) {
    // Make sure we have the correct connector, no matter what has been passed as parameter.
        $connector = new \local_ai_manager\connector_instance($id);
        if (!$connector->record_exists()) {
            throw new moodle_exception('ID COULD NOT BE FOUND');
        }
        $connector = $connector->get_connector();
} else {
    if (empty($connector) || !in_array($connector, \local_ai_manager\plugininfo\aitool::get_enabled_plugins())) {
        throw new moodle_exception('No valid connector specified');
    }
}

$editinstanceform = new \local_ai_manager\form\edit_instance_form(new moodle_url('/local/ai_manager/edit_instance.php', ['id' => $id, 'connector' => $connector]),
        ['id' => $id, 'tenant' => $tenant, 'connector' => $connector, 'returnurl' => $PAGE->url]);


// Standard form processing if statement.
if ($editinstanceform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $editinstanceform->get_data()) {

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strtitle);
    // As the restore process is being done asynchronously, the user should get notified, that the process has successfully been
    // started or that trying to trigger it caused an error.
    echo $OUTPUT->notification('Config saved', NOTIFICATION::NOTIFY_SUCCESS);
    // Reset the form for maybe a new course restore. We have to create a new object to force the form to reread the list of backup
    // files.
    $classname = '\\aitool_' . $connector . '\\instance';
    $id = empty($data->id) ? 0 : $data->id;
    $connectorinstance = new $classname($id);
    $connectorinstance->store_formdata($data);
    redirect(new moodle_url('/local/ai_manager/tenantconfig.php'), 'DATA SAVED', '');
} else {
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strtitle);

    if (!empty($id)) {
        $instance = new \local_ai_manager\connector_instance($id);
        $instance->set_connector($connector);
        $editinstanceform->set_data($instance->get_formdata());
    }
    $editinstanceform->display();
}

echo $OUTPUT->footer();
