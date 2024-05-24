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
use local_ai_manager\local\config_manager;

require_once(dirname(__FILE__) . '/../../config.php');

global $CFG, $DB, $OUTPUT, $PAGE, $USER;

$tenant = optional_param('tenant', '', PARAM_ALPHANUM);

$url = new moodle_url('/local/ai_manager/purpose_config.php');
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

$strtitle = 'SCHULKONFIGURATION';
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);
$PAGE->navbar->add($strtitle);

$purposeconfig = new purpose_config_form(null, ['tenant' => $tenant, 'returnurl' => $PAGE->url]);
$configmanager = new config_manager($tenant);

// Standard form processing if statement.
if ($purposeconfig->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $purposeconfig->get_data()) {

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strtitle);
    echo $OUTPUT->render_from_template('local_ai_manager/tenantconfignavbar', []);

    // As the restore process is being done asynchronously, the user should get notified, that the process has successfully been
    // started or that trying to trigger it caused an error.
    foreach (base_purpose::get_all_purposes() as $purpose) {
        if (!empty($data->{base_purpose::get_purpose_tool_config_key($purpose)})) {
            $configmanager->set_config(base_purpose::get_purpose_tool_config_key($purpose),
                    $data->{base_purpose::get_purpose_tool_config_key($purpose)});
        }
    }
    echo $OUTPUT->notification('Config saved', NOTIFICATION::NOTIFY_SUCCESS);
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
    $purposeconfig->set_data($data);
    $purposeconfig->display();
}

echo $OUTPUT->footer();
