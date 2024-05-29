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

    $tabs = new \local_ai_manager\admin_settingspage_tabs('local_ai_manager', get_string('generalsettings', 'local_ai_manager'));
    $ADMIN->add('local_ai_manager_cat', $tabs);

}
