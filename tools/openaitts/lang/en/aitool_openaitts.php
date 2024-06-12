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
 *Lang strings for aitool_openaitts - EN.
 *
 * @package    aitool_openaitts
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'OpenAI TTS';
$string['openaisettings'] = 'OpenAI TTS settings';
$string['openaiapikey'] = 'OpenAI API Key';
$string['openaiapikey_desc'] = 'The API Key for your OpenAI account, from https://platform.openai.com/account/api-keys . Sample key looks like this: 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';
$string['sourceoftruth'] = 'Source of truth';
$string['sourceoftruth_desc'] = 'Information that is specific for your organization. It will be passed to Whisper as facts that should be used when crafting the response.';
$string['model'] = 'Model';
$string['model_desc'] = 'The model used to generate the completion.';
$string['max_length'] = 'Maximum length';
$string['privacy:metadata'] = 'The local ai_managers tool subplugin "' . $string['pluginname'] . '" does not store any personal data.';
$string['tools'] = 'Languagemodells';