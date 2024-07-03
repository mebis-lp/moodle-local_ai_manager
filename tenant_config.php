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

use local_ai_manager\form\tenant_config_form;
use local_ai_manager\output\tenantnavbar;

require_once(dirname(__FILE__) . '/../../config.php');

global $CFG, $DB, $OUTPUT, $PAGE, $USER;

$tenantid = optional_param('tenant', '', PARAM_ALPHANUM);
$enabletenant = optional_param('enabletenant', 'not_set', PARAM_ALPHANUM);

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

$url = new moodle_url('/local/ai_manager/tenant_config.php', ['tenantid' => $tenant->get_tenantidentifier()]);
$PAGE->set_url($url);
$PAGE->set_context($tenant->get_tenant_context());

$strtitle = get_string('schoolconfig_heading', 'local_ai_manager');
$strtitle .= ' (' . $tenant->get_tenantidentifier() . ')';
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);
$PAGE->navbar->add($strtitle);
$PAGE->set_secondary_navigation(false);

/** @var \local_ai_manager\local\config_manager $configmanager */
$configmanager = \core\di::get(\local_ai_manager\local\config_manager::class);
$istenantenabled = $configmanager->is_tenant_enabled();
if ($enabletenant !== 'not_set') {
    $configmanager->set_config('tenantenabled', !empty($enabletenant) ? 1 : 0);
    redirect($PAGE->url);
}

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_ai_manager/tenantenable',
        [
                'checked' => $istenantenabled,
                'description' => $istenantenabled ? get_string('disabletenant', 'local_ai_manager') :
                        get_string('enabletenant', 'local_ai_manager'),
                'text' => $istenantenabled ? get_string('tenantenabled', 'local_ai_manager') :
                        get_string('tenantdisabled', 'local_ai_manager'),
                'targetwhenchecked' => (new moodle_url('/local/ai_manager/tenant_config.php',
                        ['tenant' => $tenant->get_tenantidentifier(), 'enabletenant' => 0]))->out(false),
                'targetwhennotchecked' => (new moodle_url('/local/ai_manager/tenant_config.php',
                        ['tenant' => $tenant->get_tenantidentifier(), 'enabletenant' => 1]))->out(false),
        ]);

echo $OUTPUT->footer();
