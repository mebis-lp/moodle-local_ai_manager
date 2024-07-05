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
use local_ai_manager\output\tenantnavbar;

require_once(dirname(__FILE__) . '/../../config.php');

global $CFG, $DB, $OUTPUT, $PAGE, $USER;

$purpose = optional_param('purpose', '', PARAM_ALPHANUM);

\local_ai_manager\local\tenant_config_output_utils::setup_tenant_config_page(new moodle_url('/local/ai_manager/statistics.php'));

$tenant = \core\di::get(\local_ai_manager\local\tenant::class);
$returnurl = new moodle_url('/local/ai_manager/tenant_config.php', ['tenant' => $tenant->get_tenantidentifier()]);

$statisticsform = new \local_ai_manager\form\statistics_form(null, ['tenant' => $tenant, 'purpose' => $purpose]);

// Standard form processing if statement.
if ($statisticsform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $statisticsform->get_data()) {
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

    redirect($PAGE->url, get_string('user_status_updated', 'local_ai_manager'));
} else {
    echo $OUTPUT->header();
    $tenantnavbar = new tenantnavbar();
    echo $OUTPUT->render($tenantnavbar);
    $statisticsnavbar = new \local_ai_manager\output\statistics_navbar();
    echo $OUTPUT->render($statisticsnavbar);

    echo $OUTPUT->heading(get_string('statisticsoverview', 'local_ai_manager'), 2, 'text-center');

    $startpage = empty($purpose);
    $urlparams = [];
    if (!$startpage) {
        $urlparams['purpose'] = $purpose;
    }
    $baseurl = new moodle_url('/local/ai_manager/statistics.php', $urlparams);

    if ($startpage) {
        $baseurl = new moodle_url('/local/ai_manager/statistics.php');
        $overviewtable = new \local_ai_manager\local\statistics_overview_table('statistics-overview-table', $tenant, $baseurl);
        $overviewtable->out(100, false);
    }

    if (has_capability('local/ai_manager:viewuserstatistics', $tenant->get_tenant_context())) {
        $emptytable = $startpage ? $DB->count_records('local_ai_manager_request_log') === 0 :
                $DB->count_records('local_ai_manager_request_log', ['purpose' => $purpose]) === 0;

        if (!$emptytable) {
            $uniqid = $startpage ? 'statistics-table-all-purposes' : 'statistics-table-purpose-' . $purpose;
            if (!$startpage) {
                echo html_writer::div('Only user who already have used this purpose are being shown');
            }

            $table = new \local_ai_manager\local\userstats_table($uniqid, $purpose, $tenant, $baseurl);
            $table->out(5, false);
            $statisticsform->display();
            $PAGE->requires->js_call_amd('local_ai_manager/statistics_table', 'init', ['id' => $uniqid]);
        }
    }

}

echo $OUTPUT->footer();
