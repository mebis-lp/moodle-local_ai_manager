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
 * Settings page
 *
 * @package    local_ai_manager
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_ai_manager\admin\setting_button;

if ($hassiteconfig) {

    // $localmbscleanupcategory = $ADMIN->add(
    //     'localplugins',
    //     new admin_category('localmbscleanupcategory', get_string('pluginname', 'local_mbscleanup'))
    // );

    // $tabs = new local_mbs\admin_settingspage_tabs('local_mbscleanup', get_string('pluginname', 'local_mbscleanup'));
    // $ADMIN->add('localmbscleanupcategory', $tabs);


    $ADMIN->add('localplugins', new admin_category('local_ai_manager_cat', new lang_string('pluginname', 'local_ai_manager')));
    // $settingspage = new admin_settingpage('managelocalhelloworld', new lang_string('manage', 'local_ai_manager'));

    $tabs = new \local_ai_manager\admin_settingspage_tabs('local_ai_manager', get_string('pluginname', 'local_ai_manager'));
    $ADMIN->add('local_ai_manager_cat', $tabs);

    $settings = new admin_settingpage('settingsgeneral', get_string('settingsgeneral', 'local_ai_manager'));

    $helper = new local_ai_manager\helper();
    $plugininfo = new local_ai_manager\plugininfo\aitool();
    $enabledtools = $plugininfo->get_enabled_plugins();

    // Set for each tool a new settings page.
    foreach ($enabledtools as $tool) {
        $ADMIN->add('local_ai_manager_cat', new admin_externalpage(
            'aitool_' . $tool,
            get_string('pluginname', 'aitool_' . $tool),
            $CFG->wwwroot . '/local/ai_manager/tools/' . $tool . '/tool_settings.php'
        ));
    }

    // Heading for section to set default model for each porpuse.
    $settings->add(new admin_setting_heading(
        'purpose_defaults_heading',
        get_string('purpose_defaults_heading', 'local_ai_manager'),
        get_string('purpose_defaults_heading_desc', 'local_ai_manager')
    ));


    $options = ['' => get_string('pleaseselect', 'local_ai_manager')];
    foreach ($enabledtools as $tool) {
        $options[$tool] = get_string('pluginname', 'aitool_' . $tool);
    }

    $purposes = $helper->get_all_purposes();
    foreach ($purposes as $purpose) {

        $settings->add(new admin_setting_configselect(
            'local_ai_manager/default_' . $purpose,
            get_string('purpose_' . $purpose, 'local_ai_manager'),
            '',
            '',
            $options
        ));
    }

    $tabs->add($settings);

    $settings = new admin_settingpage('settingstools', get_string('tools', 'local_ai_manager'));
    foreach ($enabledtools as $tool) {
        $settings->add(
            new setting_button(
                'local_ai_manager/href_aitool_' . $tool,
                get_string('pluginname', 'aitool_' . $tool),
                '',
                get_string('settings', 'local_ai_manager'),
                $CFG->wwwroot . '/local/ai_manager/tools/' . $tool . '/tool_settings.php',
                "btn btn-secondary"
            ),
        );
    }
    $tabs->add($settings);

    // Include all setting pages of the subplugins purposes to a single tab each.
    $plugininfo = new local_ai_manager\plugininfo\aipurpose();
    $enabledpurposes = $plugininfo->get_enabled_plugins();
    // print_r($enabledpurposes);die;
    foreach ($enabledpurposes as $purpose) {
        include_once($CFG->dirroot . "/local/ai_manager/purposes/" . $purpose . "/settings.php");
    }
}
