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

use local_ai_manager\hook\usertable_extend;
use local_ai_manager\local\userinfo;
use local_ai_manager\output\tenantnavbar;
use local_bycsauth\idmgroup;

require_once(dirname(__FILE__) . '/../../config.php');

global $CFG, $DB, $OUTPUT, $PAGE, $USER;

\local_ai_manager\local\tenant_config_output_utils::setup_tenant_config_page(new moodle_url('/local/ai_manager/rights_config.php'));

$tenant = \core\di::get(\local_ai_manager\local\tenant::class);
$returnurl = new moodle_url('/local/ai_manager/tenant_config.php', ['tenant' => $tenant->get_identifier()]);

$rightsconfigform = new \local_ai_manager\form\rights_config_form(null, ['tenant' => $tenant]);

// Standard form processing if statement.
if ($rightsconfigform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $rightsconfigform->get_data()) {
    $userids = explode(';', $data->userids);
    foreach ($userids as $userid) {
        $user = \core_user::get_user($userid);
        $tenantfield = get_config('local_ai_manager', 'tenantcolumn');
        if (!$user) {
            throw new moodle_exception('User with userid ' . $userid . ' does not exist!');
        }
        if ($user->{$tenantfield} !== $tenant->get_identifier()) {
            throw new moodle_exception('You must not change the status of the user with the id ' . $userid);
        }
        $userinfo = new userinfo($userid);
        if (isset($data->lockusers)) {
            $userinfo->set_locked(true);
        } else if (isset($data->unlockusers)) {
            $userinfo->set_locked(false);
        } else if (isset($data->changerole) && isset($data->role)) {
            $role = intval($data->role);
            $userinfo->set_role($role);
        }
        $userinfo->store();
    }

    redirect($PAGE->url, get_string('userstatusupdated', 'local_ai_manager'));
} else {
    echo $OUTPUT->header();
    $tenantnavbar = new tenantnavbar('rights_config.php');
    echo $OUTPUT->render($tenantnavbar);

    echo $OUTPUT->heading(get_string('rightsconfig', 'local_ai_manager'), 2, 'text-center');

    $usertablefilter = new \local_ai_manager\hook\usertable_filter($tenant);
    \core\di::get(\core\hook\manager::class)->dispatch($usertablefilter);

    $filterform =
            new \local_ai_manager\form\rights_config_filter_form(null, ['filteroptions' => $usertablefilter->get_filter_options()]);

    $filterids = [];
    if (!empty($usertablefilter->get_filter_options())) {
        if ($filterform->is_cancelled() || empty($filterform->get_data())) {
            $filterids = [];
        } else {
            $filterids = [];
            if (!empty($filterform->get_data())) {
                $filterids = $filterform->get_data()->filterids;
                // TODO: Evtl. add validation possibility in usertable_filter
            }
        }
        $filterform->display();
    }

    $uniqid = 'rights-config-table-' . uniqid();
    $rightstable = new \local_ai_manager\local\rights_config_table($uniqid, $tenant, $PAGE->url, $filterids);
    $rightstable->out(100, false);
    $rightsconfigform->display();
    $PAGE->requires->js_call_amd('local_ai_manager/rights_config_table', 'init', ['id' => $uniqid]);
}

echo $OUTPUT->footer();
