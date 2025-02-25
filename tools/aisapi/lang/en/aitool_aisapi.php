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
 * Lang strings for aitool_aisapi - EN.
 *
 * @package    aitool_aisapi
 * @copyright  2025 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['adddescription'] = 'The AIS API provides a lot of different large language models for German schools.';
$string['availablemodelssetting'] = 'Available models';
$string['availablemodelssettingdesc'] = 'Enter the names of the models that should be available for selection by the tenant manager. One model per line. Names must be identical to returned "name" attribute of the model description returned by the /v1/models endpoint. Add a "#VISION" at the end of the name to declare it as vision model. Please note that changing this setting will only affect the select field in the instance edit form, it will not affect already configured AI tools.';
$string['baseurlsetting'] = 'Base URL for the API';
$string['baseurlsettingdesc'] = 'Insert the base URL of the AIS API here, stopping before "/v1..."';
$string['err_contentfilter'] = 'Your request was rejected as a result of the content filter of the external tool. Your prompt probably requests something that is not allowed.';
$string['err_retrievingmodels'] = 'There was an error while trying to retrieve the list of models from the AIS API. Error code: {$a->code}.';
$string['globalapikeysetting'] = 'Global API key';
$string['globalapikeysettingdesc'] = 'If this setting is set, this API key will be used for all requests. If not, the tenant manager will be able to insert own API keys.';
$string['pluginname'] = 'AIS API';
$string['privacy:metadata'] = 'The local ai_manager tool subplugin "AIS API" does not store any personal data.';
