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
 * Lang strings for local_ai_manager - EN.
 *
 * @package    local_ai_manager
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['addinstance'] = 'Add AI tool';
$string['addnavigationentry'] = 'Add navigation entry';
$string['addnavigationentrydesc'] = 'Enable if the AI manager configuration should be accessible by the primary navigation';
$string['aitool'] = 'AI tool';
$string['allowedtenants'] = 'Allowed tenants';
$string['allowedtenantsdesc'] = 'Specify a list of allowed tenants: One identifier per line.';
$string['ai_manager:manage'] = 'Manage configuration of ai_manager';
$string['ai_manager:use'] = 'Use ai_manager';
$string['aiadministrationlink'] = 'AI tools administration';
$string['apikey'] = 'API key';
$string['assignpurposes'] = 'Assign purposes';
$string['basicsettings'] = 'Basic settings';
$string['basicsettingsdesc'] = 'Configure basic settings for the AI manager plugin';
$string['configureaitool'] = 'Configure AI tool';
$string['configurepurposes'] = 'Configure the purposes';
$string['confirmaitoolsusage_header'] = 'Confirm AI usage';
$string['confirmaitoolsusage_description'] = 'You are about to use an AI tool. Whenever you are about to use such an tool from inside
the mebis Lernplattform you will be informed by an info box which contains specific information about the usage of your data.';
$string['confirmaitoolsusage_details'] = 'Detailed information';
$string['currentlyusedaitools'] = 'Currently configured AI tools';
$string['defaulttenantname'] = 'Default tenant';
$string['disabletenant'] = 'Disable tenant';
$string['empty_api_key'] = 'Empty API Key';
$string['enabletenant'] = 'Enable tenant';
$string['endpoint'] = 'API endpoint';
$string['female'] = 'Female';
$string['heading_home'] = 'AI tools';
$string['heading_statistics'] = 'Statistics';
$string['infolink'] = 'Link for further information';
$string['instanceaddmodal_heading'] = 'Which AI tool do you want to add?';
$string['instancedeleteconfirm'] = 'Are you sure that you want to delete this AI tool?';
$string['instancename'] = 'Internal identifier';
$string['male'] = 'Male';
$string['model'] = 'Model';
$string['per'] = 'per';
$string['pleaseselect'] = 'Please, select';
$string['pluginname'] = 'AI Manager';
$string['privacy:metadata'] = 'The local ai_manager plugin does not store any personal data.';
$string['prompterror'] = 'Prompt error';
$string['purposesheading'] = 'Purposes ({$a->currentcount}/{$a->maxcount} assigned)';
$string['purposesdescription'] = 'Which of your configured AI tools should be used for which purpose?';
$string['quotaconfig'] = 'limits configuration';
$string['resetuserusagetask'] = 'Reset AI manager user usage data';
$string['restricttenants'] = 'Lock access for certain tenants';
$string['restricttenantsdesc'] = 'Enable to limit the AI tools to specific tenants which can be defined by the "allowedtenants" config option.';
$string['rightsconfig'] = 'Rights configuration';
$string['settingsgeneral'] = 'Allgemein';
$string['statisticsoverview'] = 'Statistics';
$string['temperature'] = 'Temperature';
$string['temperature_desc'] = 'This describes "randomness" or "creativity". Low temperature will generate more coherent but predictable text. High numbers means more creative but not accurate. The range is from 0 to 1.';
$string['tenantenabled'] = 'enabled';
$string['tenantenableheading'] = 'AI tools in your school';
$string['tenantenabledescription'] = 'For your school - teachers as well as students - to gain access to all AI features of the mebis Lernplattform you need to enable and configure the features here.';
$string['tenantenablednextsteps'] = 'The AI features of the mebis Lernplattform are now enabled for your school. Please note that you now have to define the tools and purposes
 for the features to be actually usable.<br/>All teachers and students will have access to the AI features. However, by going to {$a} you can disable users (and classes).';
$string['tenantdisabled'] = 'disabled';
$string['tenantnotallowed'] = 'The feature is globally disabled for your tenant and thus not usable.';
$string['userconfig'] = 'User configuration';
$string['userusagestatistics'] = 'Usage overview of users ';
$string['empty'] = 'empty';
$string['generalsettings'] = 'General settings';
$string['unit_token'] = 'token';
$string['unit_count'] = 'request(s)';

// General strings.
$string['student'] = 'Student';
$string['teacher'] = 'Teacher';
$string['enable_ai_integration'] = 'Enable AI integration';
$string['configure_instance'] = 'Configure AI Tool Instances';

// Schoolconfiguration.
$string['schoolconfig_heading'] = 'School Configuration of the AI tools';

// User config.
$string['heading_home'] = 'AI tools';
$string['heading_purposes'] = 'Purposes';
$string['general_user_settings'] = 'General user settings';
$string['max_request_time_window'] = 'Time window for maximum number';
$string['max_requests_purpose_heading'] = 'Settings for purpose {$a}';
$string['max_requests_purpose'] = 'Maximum number of requests per time window ({$a})';
$string['select_tool_for_purpose'] = 'Select ai tool for purpose {$a}';
$string['not_selected'] = 'Not selected';
$string['disable_user'] = 'Disable User';
$string['enable_user'] = 'Enable User';
$string['statistics_for'] = 'Statistic for {$a}';
$string['user_status_updated'] = 'The user status was updated';
$string['request_count'] = 'Request count';
$string['locked'] = 'Locked';
$string['token_used'] = 'Used token';
