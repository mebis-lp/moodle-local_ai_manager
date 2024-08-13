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
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_ai_manager\base_instance;
use local_ai_manager\local\tenant_config_output_utils;
use local_ai_manager\output\tenantnavbar;

require_once(dirname(__FILE__) . '/../../config.php');
require_login();

global $CFG, $DB, $OUTPUT, $PAGE, $USER;

$PAGE->add_body_class('limitcontentwidth');

$enabletenant = optional_param('enabletenant', 'not_set', PARAM_ALPHANUM);

$url = new moodle_url('/local/ai_manager/tenant_config.php');
tenant_config_output_utils::setup_tenant_config_page($url);

/** @var \local_ai_manager\local\config_manager $configmanager */
$configmanager = \core\di::get(\local_ai_manager\local\config_manager::class);
$istenantenabled = $configmanager->is_tenant_enabled();
if ($enabletenant !== 'not_set') {
    $configmanager->set_config('tenantenabled', !empty($enabletenant) ? 1 : 0);
    redirect($PAGE->url);
}

$tenant = \core\di::get(\local_ai_manager\local\tenant::class);

$rightsconfiglink = html_writer::link(new moodle_url('/local/ai_manager/statistics.php'),
        get_string('rightsconfig', 'local_ai_manager'));

echo $OUTPUT->header();
if ($configmanager->is_tenant_enabled()) {
    $tenantnavbar = new tenantnavbar('tenant_config.php');
    echo $OUTPUT->render($tenantnavbar);
}
echo $OUTPUT->render_from_template('local_ai_manager/tenantenable',
    [
        'checked' => $istenantenabled,
        'text' => $istenantenabled ? get_string('tenantenabled', 'local_ai_manager') :
                get_string('tenantdisabled', 'local_ai_manager'),
        'targetwhenchecked' => (new moodle_url('/local/ai_manager/tenant_config.php',
                ['tenant' => $tenant->get_identifier(), 'enabletenant' => 0]))->out(false),
        'targetwhennotchecked' => (new moodle_url('/local/ai_manager/tenant_config.php',
                ['tenant' => $tenant->get_identifier(), 'enabletenant' => 1]))->out(false),
        'tenantfullname' => $tenant->get_fullname(),
        'rightsconfiglink' => $rightsconfiglink,
    ]);

if ($configmanager->is_tenant_enabled()) {
    $instances = [];

    $purposeconfig = $configmanager->get_purpose_config();
    $purposeswithtool = [];
    foreach ($purposeconfig as $purpose => $instanceid) {
        if (!is_null($instanceid)) {
            $purposeswithtool[] = $purpose;
        }
    }

    $purposesheading = get_string('purposesheading', 'local_ai_manager', [
            'currentcount' => count($purposeswithtool),
            'maxcount' => count($purposeconfig),
    ]);

    foreach (\local_ai_manager\base_instance::get_all_instances() as $instance) {
        $purposes = [];
        foreach ($purposeconfig as $purpose => $instanceid) {
            if (intval($instanceid) === $instance->get_id()) {
                $purposes[] = ['fullname' => get_string('pluginname', 'aipurpose_' . $purpose)];
            }
        }
        $linkedname = html_writer::link(new moodle_url('/local/ai_manager/edit_instance.php',
                ['id' => $instance->get_id(), 'tenant' => $tenant->get_identifier()]), $instance->get_name());

        $instances[] = [
                'name' => $linkedname,
                'toolname' => get_string('pluginname', 'aitool_' . $instance->get_connector()),
                'model' => $instance->get_model() === base_instance::PRECONFIGURED_MODEL
                        ? get_string('preconfiguredmodel', 'local_ai_manager')
                        : $instance->get_model(),
                'purposes' => $purposes,
                'nopurposeslink' => html_writer::link(new moodle_url('/local/ai_manager/purpose_config.php',
                        ['tenant' => $tenant->get_identifier()]),
                        '<i class="fa fa-arrow-right"></i> ' . get_string('assignpurposes', 'local_ai_manager')),
        ];
    }
    echo $PAGE->get_renderer('core')->render_from_template('local_ai_manager/instancetable',
            [
                    'tenant' => $tenant->get_identifier(),
                    'purposesheading' => $purposesheading,
                    'instances' => $instances,
            ]
    );
}

echo $OUTPUT->footer();
