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
$string['aiisbeingused'] = 'Sie verwenden ein KI-Tool. Die eingegebenen Daten werden an ein externes KI-Tool gesendet.';
$string['aiinfotitle'] = 'AI tools in your learning plattform';
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
$string['confirm'] = 'Confirm';
$string['confirmaitoolsusage_heading'] = 'Confirm AI usage';
$string['confirmaitoolsusage_description'] = 'You are about to use an AI tool. Whenever you are about to use such an tool from inside
the mebis Lernplattform you will be informed by an info box which contains specific information about the usage of your data.';
$string['confirmaitoolsusage_details'] = 'Detailed information';
$string['confirmed'] = 'Terms of use accepted';
$string['currentlyusedaitools'] = 'Currently configured AI tools';
$string['defaulttenantname'] = 'Default tenant';
$string['disabletenant'] = 'Disable tenant';
$string['empty_api_key'] = 'Empty API Key';
$string['enabletenant'] = 'Enable tenant';
$string['endpoint'] = 'API endpoint';
$string['error_http400'] = 'Error sanitizing passed options';
$string['error_http403disabled'] = 'Your ByCS admin has not enabled the AI tools feature';
$string['error_http403blocked'] = 'Your ByCS admin has blocked access to the AI tools for you';
$string['error_http403notconfirmed'] = 'You have not yet confirmed the terms of use';
$string['error_http403usertype'] = 'Your ByCS admin has disabled this purpose for your user type';
$string['error_http429'] = 'You have reached the maximum amount of requests. You are only allowed to send {$a->count} requests in a period of {$a->period}';
$string['error_http409'] = 'The itemid {$a} already taken';
$string['error_noaitoolassignedforpurpose'] = 'There is no AI tool assigned for the purpose "{$a}"';
$string['exception_curl28'] = 'The API took too long to process your request or could not be reached in a reasonable time';
$string['exception_http401'] = 'Access to the API has been denied because of invalid credentials';
$string['exception_http429'] = 'There have been sent too many or too big requests to the AI tool in a certain amount of time. Please try again later.';
$string['exception_http500'] = 'An internal server error of the AI tool occurred';
$string['exception_default'] = 'A general error occurred while trying to send the request to the AI tool';
$string['female'] = 'Female';
$string['heading_home'] = 'AI tools';
$string['heading_statistics'] = 'Statistics';
$string['infolink'] = 'Link for further information';
$string['instanceaddmodal_heading'] = 'Which AI tool do you want to add?';
$string['instancedeleteconfirm'] = 'Are you sure that you want to delete this AI tool?';
$string['instancename'] = 'Internal identifier';
$string['nodata'] = 'No data to show';
$string['male'] = 'Male';
$string['model'] = 'Model';
$string['notconfirmed'] = 'Not confirmed';
$string['per'] = 'per';
$string['pleaseselect'] = 'Please, select';
$string['pluginname'] = 'AI Manager';
$string['preconfiguredmodel'] = 'Preconfigured model';
$string['privacy:metadata'] = 'The local ai_manager plugin does not store any personal data.';
$string['prompterror'] = 'Prompt error';
$string['purpose'] = 'Purpose';
$string['purposesheading'] = 'Purposes ({$a->currentcount}/{$a->maxcount} assigned)';
$string['purposesdescription'] = 'Which of your configured AI tools should be used for which purpose?';
$string['quotaconfig'] = 'limits configuration';
$string['quotadescription'] = 'Set the time window and the maximum number of requests per student and teacher here. After the time window expires, the number of requests will automatically reset.';
$string['request_count'] = 'Request count';
$string['resetuserusagetask'] = 'Reset AI manager user usage data';
$string['restricttenants'] = 'Lock access for certain tenants';
$string['restricttenantsdesc'] = 'Enable to limit the AI tools to specific tenants which can be defined by the "allowedtenants" config option.';
$string['revokeconfirmation'] = 'Revoke confirmation';
$string['rightsconfig'] = 'Rights configuration';
$string['selecteduserscount'] = '{$a} selected';
$string['subplugintype_aipurpose_plural'] = 'AI purposes';
$string['subplugintype_aitool_plural'] = 'AI tools';
$string['statisticsoverview'] = 'Global overview';
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
$string['userconfirmation_headline'] = 'Confirmation for usage of AI tools';
$string['userconfirmation_description'] = 'By enabling the switch below you accept the additional terms of use related to the usage of the AI tools.';
$string['userstatistics'] = 'User overview';
$string['usernotfound'] = 'Cannot update the user - user doesn\'t exist';
$string['within'] = 'in';
$string['empty'] = 'empty';
$string['generalsettings'] = 'General settings';
$string['unit_token'] = 'token';
$string['unit_count'] = 'request(s)';
$string['usage'] = 'Usage';

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
$string['locked'] = 'Locked';

// Temperature.
$string['temperature_more_creative'] = 'More creative';
$string['temperature_creative_balanced'] = 'Balanced';
$string['temperature_more_precise'] = 'More precise';
$string['temperature_defaultsetting'] = 'Temperature default';
$string['temperature_use_custom_value'] = 'Use custom temperature value';
$string['temperature_custom_value'] = 'Own value ( between 0 and 1 )';
