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
 * @package    aitool_ollama
 * @copyright  ISB Bayern, 2024
 * @author     Stefan Hanauska <stefan.hanauska@csg-in.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$settings->add(new admin_setting_configtext(
    'aitool_ollama/url',
    get_string('url', 'aitool_ollama'),
    get_string('url_desc', 'aitool_ollama'),
    ''
));

$settings->add(new admin_setting_configtext(
    'aitool_ollama/apikey',
    get_string('apikey', 'aitool_ollama'),
    get_string('apikey_desc', 'aitool_ollama'),
    ''
));

$settings->add(new admin_setting_configtext(
    'aitool_ollama/temperature',
    get_string('temperature', 'aitool_ollama'),
    get_string('temperature_desc', 'aitool_ollama'),
    ''
));
