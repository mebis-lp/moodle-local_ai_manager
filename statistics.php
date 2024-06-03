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

use local_ai_manager\local\userinfo;

require_once(dirname(__FILE__) . '/../../config.php');

global $CFG, $DB, $OUTPUT, $PAGE, $USER;

$tenant = optional_param('tenant', '', PARAM_ALPHANUM);
$purpose = optional_param('purpose', '', PARAM_ALPHANUM);

$url = new moodle_url('/local/ai_manager/statistics.php');
$PAGE->set_url($url);

$returnurl = new moodle_url('/course/index.php');

// Check permissions.
require_login();

if (!empty($tenantid)) {
    $tenant = new \local_ai_manager\local\tenant($tenantid);
    \core\di::set(\local_ai_manager\local\tenant::class, $tenant);
}
$tenant = \core\di::get(\local_ai_manager\local\tenant::class);
$accessmanager = \core\di::get(\local_ai_manager\local\access_manager::class);
$accessmanager->require_tenant_manager();

$PAGE->set_context($tenant->get_tenant_context());

$strtitle = 'STATISTIK';
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);
$PAGE->navbar->add($strtitle);

//$download = optional_param('download', '', PARAM_ALPHA);

$purposes = [];
foreach (\local_ai_manager\base_purpose::get_all_purposes() as $availablepurpose) {
    $purposes[] = ['purpose' => $availablepurpose];
}
$statisticsnavbarcontext['purposes'] = $purposes;


$statisticsform = new \local_ai_manager\form\statistics_form(null, ['tenant' => $tenant, 'purpose' => $purpose]);
// Will return the config manager for the current user.
//$configmanager = \core\di::get(\local_ai_manager\local\config_manager::class);

// Standard form processing if statement.
if ($statisticsform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $statisticsform->get_data()) {

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strtitle);
    echo $OUTPUT->render_from_template('local_ai_manager/tenantconfignavbar', []);
    echo $OUTPUT->render_from_template('local_ai_manager/statisticssubnavbar', $statisticsnavbarcontext);

    $userids = explode(';', $data->userids);
    foreach ($userids as $userid) {
        $user = \core_user::get_user($userid);
        if (!$user) {
            throw new moodle_exception('User with userid ' . $userid . ' does not exist!');
        }
        if ($user->institution !== $tenant->get_tenantidentifier()) {
            throw new moodle_exception('You must not change the status of the user with the id ' . $userid);
        }
        $userinfo = new userinfo($userid);
        if (isset($data->lockusers)) {
            $userinfo->set_locked(true);
        } else if (isset($data->unlockusers)) {
            $userinfo->set_locked(false);
        }
        $userinfo->store();
    }

    redirect($PAGE->url, 'USER STATUS UPDATED');
} else {
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strtitle);
    echo $OUTPUT->render_from_template('local_ai_manager/tenantconfignavbar', []);
    echo $OUTPUT->render_from_template('local_ai_manager/statisticssubnavbar', $statisticsnavbarcontext);

    $startpage = empty($purpose);
    $emptytable = $startpage ? $DB->count_records('local_ai_manager_request_log') === 0 :
            $DB->count_records('local_ai_manager_request_log', ['purpose' => $purpose]) === 0;

    if (!$emptytable) {
        $uniqid = $startpage ? 'statistics-table-all-purposes' : 'statistics-table-purpose-' . $purpose;
        $urlparams = [];
        if (!$startpage) {
            $urlparams['purpose'] = $purpose;
        }
        $baseurl = new moodle_url('/local/ai_manager/statistics.php', $urlparams);
        if (!$startpage) {
            echo html_writer::div('Only user who already have used this purpose are being shown');
        }
        $table = new \local_ai_manager\local\userstats_table($uniqid, $purpose, $tenant, $baseurl);
        $table->out(5, false);
    }

    $statisticsform->display();
    $PAGE->requires->js_call_amd('local_ai_manager/statistics_table', 'init', ['id' => $uniqid]);
}

echo $OUTPUT->footer();
