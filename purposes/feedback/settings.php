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
 * Settings page to be included as tab in ai_managers settings page
 *
 * @package    aipurpose_feedback
 * @copyright  ISB Bayern, 2024
* @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use aipurpose_feedback\settings_test_requests;

defined('MOODLE_INTERNAL') || die();

$settings = new admin_settingpage('tab_aipurpose_feedback', get_string('pluginname', 'aipurpose_feedback'));

$name = new lang_string('pluginname', 'aipurpose_feedback');
$settings->add(new admin_setting_heading('pluginname', $name, ''));

require_once($CFG->dirroot . '/local/ai_manager/purposes/feedback/settings_test_requests.php');
$settings->add(new settings_test_requests());

// $settings->add(new admin_setting_heading(
//     'aipurpose_feedback/chatheading',
//     get_string('chatheading', 'aipurpose_feedback'),
//     get_string('chatheadingdesc', 'aipurpose_feedback')
// ));

// $settings->add(new admin_setting_configtextarea(
//     'aipurpose_feedback/prompt',
//     get_string('prompt', 'aipurpose_feedback'),
//     get_string('promptdesc', 'aipurpose_feedback'),
//     "Below is a conversation between a user and a support assistant for a Moodle site, where users go for online learning.",
//     PARAM_TEXT
// ));

// $settings->add(new admin_setting_configtextarea(
//     'aipurpose_feedback/sourceoftruth',
//     get_string('sourceoftruth', 'aipurpose_feedback'),
//     get_string('sourceoftruthdesc', 'aipurpose_feedback'),
//     '',
//     PARAM_TEXT
// ));

$tabs->add($settings);
