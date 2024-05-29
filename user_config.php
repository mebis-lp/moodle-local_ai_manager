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
use local_ai_manager\form\user_config_form;
use local_ai_manager\local\userinfo;

require_once(dirname(__FILE__) . '/../../config.php');

global $CFG, $DB, $OUTPUT, $PAGE, $USER;

$tenantid = optional_param('tenant', '', PARAM_ALPHANUM);

$url = new moodle_url('/local/ai_manager/user_config.php');
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

$strtitle = 'USER CONFIG';
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);
$PAGE->navbar->add($strtitle);

$userconfigform = new user_config_form(null, ['tenant' => $tenantid, 'returnurl' => $PAGE->url]);
// Will return the config manager for the current user.
/** @var \local_ai_manager\local\config_manager $configmanager */
$configmanager = \core\di::get(\local_ai_manager\local\config_manager::class);

// Standard form processing if statement.
if ($userconfigform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $userconfigform->get_data()) {

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strtitle);
    echo $OUTPUT->render_from_template('local_ai_manager/tenantconfignavbar', []);

    foreach (['max_requests_basic', 'max_requests_extended'] as $configkey) {
        if (property_exists($data, $configkey)) {
            $configmanager->set_config($configkey,
                    intval($data->{$configkey}) > 0 ? intval($data->{$configkey}) : userinfo::UNLIMITED_REQUESTS_PER_USER);
        } else {
            $configmanager->unset_config($configkey);
        }
    }

    if (property_exists($data, 'max_requests_period')) {
        $configmanager->set_config('max_requests_period', intval($data->max_requests_period));
    } else {
        $configmanager->unset_config('max_requests_period');
    }

    redirect($PAGE->url, 'CONFIG SAVED');
} else {
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strtitle);
    echo $OUTPUT->render_from_template('local_ai_manager/tenantconfignavbar', []);

    $data = new stdClass();
    if ($configmanager->get_config('max_requests_basic')) {
        $data->max_requests_basic = $configmanager->get_config('max_requests_basic');
    }
    if ($configmanager->get_config('max_requests_extended')) {
        $data->max_requests_extended = $configmanager->get_config('max_requests_extended');
    }
    if ($configmanager->get_config('max_requests_period')) {
        $data->max_requests_period = $configmanager->get_config('max_requests_period');
    }

    $userconfigform->set_data($data);
    $userconfigform->display();
}

echo $OUTPUT->footer();
