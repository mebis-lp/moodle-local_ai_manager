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
use local_ai_manager\local\userusage;
use local_ai_manager\output\tenantnavbar;

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

$url = new moodle_url('/local/ai_manager/user_config.php', ['tenant' => $tenant->get_tenantidentifier()]);
$PAGE->set_url($url);
$returnurl = new moodle_url('/local/ai_manager/tenant_config.php', ['tenant' => $tenant->get_tenantidentifier()]);
$PAGE->set_context($tenant->get_tenant_context());

$strtitle = get_string('heading_user_config', 'local_ai_manager');
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);
$PAGE->navbar->add($strtitle);
$PAGE->set_secondary_navigation(false);


$userconfigform = new user_config_form(null, ['tenant' => $tenantid, 'returnurl' => $PAGE->url]);
// Will return the config manager for the current user.
/** @var \local_ai_manager\local\config_manager $configmanager */
$configmanager = \core\di::get(\local_ai_manager\local\config_manager::class);

// Standard form processing if statement.
if ($userconfigform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $userconfigform->get_data()) {
    foreach (base_purpose::get_all_purposes() as $purpose) {

        foreach ([$purpose . '_max_requests_basic', $purpose . '_max_requests_extended'] as $configkey) {
            if (property_exists($data, $configkey)) {
                // Negative values are interpreted as unlimited requests.
                $configmanager->set_config($configkey,
                        intval($data->{$configkey}) >= 0 ? intval($data->{$configkey}) : userusage::UNLIMITED_REQUESTS_PER_USER);
            } else {
                $configmanager->unset_config($configkey);
            }
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
    $tenantnavbar = new tenantnavbar();
    echo $OUTPUT->render($tenantnavbar);

    $data = new stdClass();
    foreach (base_purpose::get_all_purposes() as $purpose) {
        $purposeobject = \core\di::get(\local_ai_manager\local\connector_factory::class)->get_purpose_by_purpose_string($purpose);
        //if ($configmanager->get_max_requests_raw($purposeobject, userinfo::ROLE_BASIC) !== false) {
            $data->{$purpose . '_max_requests_basic'} = $configmanager->get_max_requests($purposeobject, userinfo::ROLE_BASIC);
        //}
        //if ($configmanager->get_max_requests_raw($purposeobject, userinfo::ROLE_EXTENDED) !== false) {
            $data->{$purpose . '_max_requests_extended'} = $configmanager->get_max_requests($purposeobject, userinfo::ROLE_EXTENDED);
        //}
    }
    if ($configmanager->get_max_requests_period()) {
        $data->max_requests_period = $configmanager->get_max_requests_period();
    }

    $userconfigform->set_data($data);
    $userconfigform->display();
}

echo $OUTPUT->footer();
