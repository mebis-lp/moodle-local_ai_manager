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
$string['ai_info_table_row_highlighted'] = 'The highlighted AI tools are the ones which are being used by the plugin you were using when clicking the link to this page.';
$string['ai_manager:manage'] = 'Configure AI manager settings for a tenant';
$string['ai_manager:managetenants'] = 'Configure AI manager settings for all tenants';
$string['ai_manager:use'] = 'Use ai_manager';
$string['ai_manager:viewstatistics'] = 'View statistics';
$string['ai_manager:viewusage'] = 'View usage information';
$string['ai_manager:viewusernames'] = 'View non anonymized usernames in statistics';
$string['ai_manager:viewuserstatistics'] = 'View statistics of single users';
$string['aiadministrationlink'] = 'AI tools administration';
$string['aiinfotitle'] = 'AI tools in your learning plattform';
$string['aiisbeingused'] = 'You are using an AI tool. The entered data will be sent to an external AI tool.';
$string['aitool'] = 'AI tool';
$string['aitooldeleted'] = 'AI tool deleted';
$string['aitoolsaved'] = 'AI tool data saved';
$string['aiwarning'] = 'AI generated content should always be validated.';
$string['allowedtenants'] = 'Allowed tenants';
$string['allowedtenantsdesc'] = 'Specify a list of allowed tenants: One identifier per line.';
$string['apikey'] = 'API key';
$string['applyfilter'] = 'Apply filter';
$string['assignpurposes'] = 'Assign purposes';
$string['assignrole'] = 'Assign role';
$string['basicsettings'] = 'Basic settings';
$string['basicsettingsdesc'] = 'Configure basic settings for the AI manager plugin';
$string['configure_instance'] = 'Configure AI Tool Instances';
$string['configureaitool'] = 'Configure AI tool';
$string['configurepurposes'] = 'Configure the purposes';
$string['confirm'] = 'Confirm';
$string['confirmaitoolsusage_heading'] = 'Confirm AI usage';
$string['confirmed'] = 'Terms of use accepted';
$string['currentlyusedaitools'] = 'Currently configured AI tools';
$string['defaultrole'] = 'default role';
$string['defaulttenantname'] = 'Default tenant';
$string['disabletenant'] = 'Disable tenant';
$string['empty_api_key'] = 'Empty API Key';
$string['enable_ai_integration'] = 'Enable AI integration';
$string['enabletenant'] = 'Enable tenant';
$string['endpoint'] = 'API endpoint';
$string['error_http400'] = 'Error sanitizing passed options';
$string['error_http403blocked'] = 'Your ByCS admin has blocked access to the AI tools for you';
$string['error_http403disabled'] = 'Your ByCS admin has not enabled the AI tools feature';
$string['error_http403notconfirmed'] = 'You have not yet confirmed the terms of use';
$string['error_http403usertype'] = 'Your ByCS admin has disabled this purpose for your user type';
$string['error_http409'] = 'The itemid {$a} is already taken';
$string['error_http429'] = 'You have reached the maximum amount of requests. You are only allowed to send {$a->count} requests in a period of {$a->period}';
$string['error_limitreached'] = 'You have reached the maximum amount of requests for this purpose. Please wait until the counter has been reset.';
$string['error_noaitoolassignedforpurpose'] = 'There is no AI tool assigned for the purpose "{$a}"';
$string['error_pleaseconfirm'] = 'Please accept them before using.';
$string['error_purposenotconfigured'] = 'There is no AI tool configured for this purpose. Please contact your ByCS admin.';
$string['error_tenantdisabled'] = 'The AI tools are not enabled for your school. Please contact your ByCS admin.';
$string['error_unavailable_noselection'] = 'This tool is only available if text has been selected.';
$string['error_unavailable_selection'] = 'This tool is only available if no text has been selected.';
$string['error_userlocked'] = 'Your user has been locked by your ByCS admin.';
$string['error_usernotconfirmed'] = 'You have not accepted the terms of use yet.';
$string['exception_curl'] = 'A connection error to the external API endpoint has occurred';
$string['exception_curl28'] = 'The API took too long to process your request or could not be reached in a reasonable time';
$string['exception_default'] = 'A general error occurred while trying to send the request to the AI tool';
$string['exception_http401'] = 'Access to the API has been denied because of invalid credentials';
$string['exception_http429'] = 'There have been sent too many or too big requests to the AI tool in a certain amount of time. Please try again later.';
$string['exception_http500'] = 'An internal server error of the AI tool occurred';
$string['female'] = 'Female';
$string['filteridmgroups'] = 'Klassen/Gruppen filtern';
$string['formvalidation_editinstance_azureapiversion'] = 'You must provide the api version of your Azure Resource';
$string['formvalidation_editinstance_azuredeploymentid'] = 'You must provide the deployment id of your Azure Resource';
$string['formvalidation_editinstance_azureresourcename'] = 'You must provide the resource name of your Azure Resource';
$string['formvalidation_editinstance_name'] = 'Please insert a name for the AI tool';
$string['formvalidation_editinstance_endpointnossl'] = 'For security and data privacy reasons only HTTPS endpoints are allowed';
$string['formvalidation_editinstance_temperaturerange'] = 'Temperature value must be between 0 und 1';
$string['general_information_heading'] = 'General Information';
$string['general_information_text'] = 'As of now, BayernCloud Schule and thus also the mebis learning platform does not provide any AI tools. However, the mebis learning platform offers interfaces through which AI tools can be used within the mebis learning platform. For this to be possible for students and teachers of a school, the school must acquire or provide such a tool. The ByCS admin of the respective school can then store the access data via a configuration page and thus enable the AI functions offered in the mebis learning platform.';
$string['general_user_settings'] = 'General user settings';
$string['get_ai_response_failed'] = 'Retrieving AI response failed';
$string['get_ai_response_failed_desc'] = 'While trying to get a result from the endpoint of an external AI tool an error occurred';
$string['get_ai_response_succeeded'] = 'Successfully received AI response';
$string['get_ai_response_succeeded_desc'] = 'The attempt to retrieve a response from an endpoint of an external AI tool was successful';
$string['heading_home'] = 'AI tools';
$string['heading_purposes'] = 'Purposes';
$string['heading_statistics'] = 'Statistics';
$string['infolink'] = 'Link for further information';
$string['instanceaddmodal_heading'] = 'Which AI tool do you want to add?';
$string['instancedeleteconfirm'] = 'Are you sure that you want to delete this AI tool?';
$string['instancename'] = 'Internal identifier';
$string['landscape'] = 'landscape';
$string['large'] = 'large';
$string['locked'] = 'Locked';
$string['lockuser'] = 'Lock user';
$string['male'] = 'Male';
$string['max_request_time_window'] = 'Time window for maximum number of requests';
$string['max_requests_purpose_heading'] = 'Purpose {$a}';
$string['max_requests_purpose'] = 'Maximum number of requests per time window ({$a})';
$string['medium'] = 'medium';
$string['model'] = 'Model';
$string['nodata'] = 'No data to show';
$string['notconfirmed'] = 'Not confirmed';
$string['notselected'] = 'Disabled';
$string['per'] = 'per';
$string['pluginname'] = 'AI Manager';
$string['portrait'] = 'portrait';
$string['preconfiguredmodel'] = 'Preconfigured model';
$string['privacy_terms_heading'] = 'Privacy and Terms of Use';
$string['privacy_terms_of_usage'] = '';
$string['privacy_terms_text1'] = 'The general terms of use of the mebis learning platform apply, especially AI tools, see '. $string['privacy_terms_of_usage'];;
$string['privacy_terms_text2'] = 'In the table below, you can see an overview of the AI tools configured by your school. Your ByCS admin may have provided additional notes on the terms of use and privacy notices of the respective AI tools in the "Info link" column.';
$string['privacy:metadata'] = 'The local ai_manager plugin does not store any personal data.';
$string['purpose'] = 'Purpose';
$string['purposesdescription'] = 'Which of your configured AI tools should be used for which purpose?';
$string['purposesheading'] = 'Purposes ({$a->currentcount}/{$a->maxcount} assigned)';
$string['quotaconfig'] = 'Limits configuration';
$string['quotadescription'] = 'Set the time window and the maximum number of requests per student and teacher here. After the time window expires, the number of requests will automatically reset.';
$string['request_count'] = 'Request count';
$string['requesttimeout'] = 'Timeout for request to the AI endpoints';
$string['requesttimeoutdesc'] = 'Maximum amount of time in seconds for requests to the external AI endpoints';
$string['resetfilter'] = 'Reset filter';
$string['resetuserusagetask'] = 'Reset AI manager user usage data';
$string['restricttenants'] = 'Lock access for certain tenants';
$string['restricttenantsdesc'] = 'Enable to limit the AI tools to specific tenants which can be defined by the "allowedtenants" config option.';
$string['revokeconfirmation'] = 'Revoke confirmation';
$string['rightsconfig'] = 'Rights configuration';
$string['role'] = 'Role';
$string['role_basic'] = 'Student';
$string['role_extended'] = 'Teacher';
$string['role_unlimited'] = 'Unlimited';
$string['schoolconfig_heading'] = 'School Configuration of the AI tools';
$string['select_tool_for_purpose'] = 'Purpose {$a}';
$string['selecteduserscount'] = '{$a} selected';
$string['small'] = 'small';
$string['squared'] = 'squared';
$string['statistics_for'] = 'Statistic for {$a}';
$string['statisticsoverview'] = 'Global overview';
$string['subplugintype_aipurpose_plural'] = 'AI purposes';
$string['subplugintype_aitool_plural'] = 'AI tools';
$string['table_heading_infolink'] = 'Info link';
$string['table_heading_instance_name'] = 'AI tool name';
$string['table_heading_model'] = 'Model';
$string['table_heading_purpose'] = 'Purpose';
$string['technical_function_heading'] = 'Technical Functionality';
$string['technical_function_step1'] = 'The ByCS admin stores a configuration for a specific purpose, for example, configuring the option for image generation, because his school has a contract with OpenAI, so the school can use the Dall-E tool.';
$string['technical_function_step2'] = 'A student or teacher of this school then finds the corresponding AI function in the mebis learning platform, for example, the ability to generate an image via a prompt directly in the editor and insert it into the editor.';
$string['technical_function_step3'] = 'If a teacher, for example, now uses this function, the prompt is sent to the servers of the learning platform and evaluated by them.';
$string['technical_function_step4_emphasized'] = 'In this process, the learning platform acts as the end-user of the external tool, meaning that the external tool cannot trace which individual user made the corresponding request to the AI tool. Only the school to which the user belongs is identifiable to the AI tool.';
$string['technical_function_step4'] = 'The servers of the mebis learning platform use the stored access data for the school’s AI tool and send the request on behalf of the user to the servers of the external AI tool.';
$string['technical_function_step5'] = 'The response from the AI tool is sent back to the user by the learning platform or the result, such as a generated image, is directly integrated into the respective activity.';
$string['technical_function_text'] = 'When using the AI functions within the learning platform, the technical process is as follows:';
$string['temperature_creative_balanced'] = 'Balanced';
$string['temperature_custom_value'] = 'Custom value (between 0 and 1)';
$string['temperature_defaultsetting'] = 'Temperature default';
$string['temperature_desc'] = 'This describes "randomness" or "creativity". Low temperature will generate more coherent but predictable text. High numbers means more creative but not accurate. The range is from 0 to 1.';
$string['temperature_more_creative'] = 'More creative';
$string['temperature_more_precise'] = 'More precise';
$string['temperature_use_custom_value'] = 'Use custom temperature value';
$string['tenantconfig_heading'] = 'AI at your school';
$string['tenantdisabled'] = 'disabled';
$string['tenantenabled'] = 'enabled';
$string['tenantenabledescription'] = 'For your school - teachers as well as students - to gain access to all AI features of the mebis Lernplattform you need to enable and configure the features here.';
$string['tenantenablednextsteps'] = 'The AI features of the mebis Lernplattform are now enabled for your school. Please note that you now have to define the tools and purposes for the features to be actually usable.<br/>All teachers and students will have access to the AI features. However, by going to {$a} you can disable users (and classes).';
$string['tenantenableheading'] = 'AI tools in your school';
$string['tenantnotallowed'] = 'The feature is globally disabled for your tenant and thus not usable.';
$string['unit_count'] = 'request(s)';
$string['unit_token'] = 'token';
$string['unlockuser'] = 'Unlock user';
$string['usage'] = 'Usage';
$string['userconfig'] = 'User configuration';
$string['userconfirmation_description'] = 'By enabling the switch below you accept the additional terms of use related to the usage of the AI tools.';
$string['userconfirmation_headline'] = 'Confirmation for usage of AI tools';
$string['userstatistics'] = 'User overview';
$string['userstatusupdated'] = 'The user\'s/users\' status has been updated';
$string['userwithusageonlyshown'] = 'Only users who already have used this purpose are being shown in this table.';
$string['use_openai_by_azure_heading'] = 'Use OpenAI via Azure';
$string['use_openai_by_azure_name'] = 'Name of the Azure resource';
$string['use_openai_by_azure_deploymentid'] = 'Deployment ID of the Azure resource';
$string['use_openai_by_azure_apiversion'] = 'API version of the Azure resource';
$string['verifyssl'] = 'Verify SSL certificates';
$string['verifyssldesc'] = 'If enabled, connections to the AI tools will only be established if the certificates can properly be verified. Only recommended to disable for development use!';
$string['within'] = 'in';
