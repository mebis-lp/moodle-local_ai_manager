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

$url = new moodle_url('/local/ai_manager/instances_config.php', ['tenant' => $tenant->get_tenantidentifier()]);
$PAGE->set_url($url);
$returnurl = new moodle_url('/local/ai_manager/tenant_config.php', ['tenant' => $tenant->get_tenantidentifier()]);
$PAGE->set_context($tenant->get_tenant_context());

$strtitle = get_string('configure_instance', 'local_ai_manager');
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);
$PAGE->navbar->add($strtitle);
$PAGE->set_secondary_navigation(false);


echo $OUTPUT->header();
echo $OUTPUT->heading($strtitle);
$tenantnavbar = new tenantnavbar();
echo $OUTPUT->render($tenantnavbar);
$instanceaddbuttons = [];
foreach (\local_ai_manager\plugininfo\aitool::get_enabled_plugins() as $tool) {
    $instanceaddbuttons[] = [
            'label' => $tool,
            'addurl' => (new moodle_url('/local/ai_manager/edit_instance.php',
                    ['tenant' => $tenant->get_tenantidentifier(), 'returnurl' => $PAGE->url, 'connectorname' => $tool]))->out()
    ];
}
$instances = [];
foreach (\local_ai_manager\base_instance::get_all_instances() as $instance) {
    $instances[] = [
            'id' => $instance->get_id(),
            'name' => $instance->get_name(),
            'tenant' => $instance->get_tenant(),
            'connector' => $instance->get_connector(),
            'endpoint' => $instance->get_endpoint(),
            'apikey' => $instance->get_apikey(),
            'model' => $instance->get_model(),
            'infolink' => $instance->get_infolink(),
    ];
}
echo $PAGE->get_renderer('core')->render_from_template('local_ai_manager/instancetable',
        [
                'instances' => $instances,
                'instanceaddbuttons' => $instanceaddbuttons,
        ]
);



echo $OUTPUT->footer();
