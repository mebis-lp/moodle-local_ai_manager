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
 *Lang strings for aitool_dalle_3 - EN.
 *
 * @package    aitool_dalle_3
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Dall-E v3';
$string['openaisettings'] = 'Dall-E v3 settings';
$string['openaiapikey'] = 'OpenAI API Key';
$string['openaiapikey_desc'] = 'The API Key for your OpenAI account, from https://platform.openai.com/account/api-keys . Sample key looks like this: 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';
$string['sourceoftruth'] = 'Source of truth';
$string['sourceoftruth_desc'] = 'Information that is specific for your organization. It will be passed to Whisper as facts that should be used when crafting the response.';
$string['model'] = 'Model';
$string['model_desc'] = 'The model used to generate the completion.';
$string['temperature'] = 'Temperature';
$string['temperature_desc'] = 'In other words this is "randomness" or "creativity". Low temperature will generate more coherent but predictable text. The range is from 0 to 1.';
$string['max_length'] = 'Maximum length';
$string['top_p'] = 'Top P';
$string['top_p_desc'] = 'It\'s used for similar purpose as temperature - the lower the setting, the more correct and deterministic the output. The range is also from 0 to 1.';
$string['frequency_penalty'] = 'Frequency penalty';
$string['frequency_penalty_desc'] = 'Reduces repetition of words that have already been generated. It counts how many times the word was already used.';
$string['presence_penalty'] = 'Presence penalty';
$string['presence_penalty_desc'] = 'Similar to frequency penalty, it reduces probability of using a word that was already used. The difference is that is does not matter how many times the word was used - just if it was or not.';
$string['privacy:metadata'] = 'The local ai_managers tool subplugin "' . $string['pluginname'] . '" does not store any personal data.';
