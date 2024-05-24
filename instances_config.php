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

require_once(dirname(__FILE__) . '/../../config.php');

global $CFG, $DB, $OUTPUT, $PAGE, $USER;

$tenant = optional_param('tenant', '', PARAM_ALPHANUM);

$url = new moodle_url('/local/ai_manager/instances_config.php');
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
    throw new moodle_exception('Invalid tenant "' . $tenant. '"!');
}
$schoolcategorycontext = \context_coursecat::instance($school->get_school_categoryid());
$coordinatorrole = $DB->get_record('role', ['shortname' => 'schulkoordinator']);
if (!user_has_role_assignment($USER->id, $coordinatorrole->id, $schoolcategorycontext->id) && !is_siteadmin()) {
    throw new moodle_exception('Only admins and ByCS admins have access to this page');
}

$PAGE->set_context($schoolcategorycontext);

$strtitle = 'INSTANCES KONFIGURATION';
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);
$PAGE->navbar->add($strtitle);


echo $OUTPUT->header();
echo $OUTPUT->heading($strtitle);
echo $OUTPUT->render_from_template('local_ai_manager/tenantconfignavbar', []);
$instanceaddbuttons = [];
foreach (\local_ai_manager\plugininfo\aitool::get_enabled_plugins() as $tool) {
    $instanceaddbuttons[] = [
            'label' => $tool,
            'addurl' => (new moodle_url('/local/ai_manager/edit_instance.php',
                    ['tenant' => $tenant, 'returnurl' => $PAGE->url, 'connector' => $tool]))->out()
    ];
}
$instances = [];
foreach (\local_ai_manager\connector_instance::get_all_instances() as $instance) {
    $instances[] = [
            'id' => $instance->get_id(),
            'name' => $instance->get_name(),
            'tenant' => $instance->get_tenant(),
            'connector' => $instance->get_connector(),
            'endpoint' => $instance->get_endpoint(),
            'model' => $instance->get_model(),
            'customfield1' => $instance->get_customfield1(),
            'customfield2' => $instance->get_customfield2(),
            'customfield3' => $instance->get_customfield3(),
            'customfield4' => $instance->get_customfield4(),
    ];
}
echo $PAGE->get_renderer('core')->render_from_template('local_ai_manager/instancetable',
        [
                'instances' => $instances,
                'instanceaddbuttons' => $instanceaddbuttons,
        ]
);



echo $OUTPUT->footer();
