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
 * Connector - whisper
 *
 * @package    aitool_openaitts
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace aitool_openaitts;

use coding_exception;
use dml_exception;
use moodle_exception;
use invalid_dataroot_permissions;
use Error;
use file_exception;
use stored_file_creation_exception;

/**
 * Connector - whisper
 *
 * @package    aitool_openaitts
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class settings {

    public function get_settings($settings) {
        $settings->add(new \admin_setting_configtext(
            'aitool_openaitts/openaiapikey',
            get_string('openaiapikey', 'aitool_openaitts'),
            get_string('openaiapikey_desc', 'aitool_openaitts'),
            ''
        ));

        $settings->add(new \admin_setting_configtextarea(
            'aitool_openaitts/source_of_truth',
            get_string('sourceoftruth', 'aitool_openaitts'),
            get_string('sourceoftruth_desc', 'aitool_openaitts'),
            ''
        ));

        $settings->add(new \admin_setting_configtext(
            'aitool_openaitts/temperature',
            get_string('temperature', 'aitool_openaitts'),
            get_string('temperature_desc', 'aitool_openaitts'),
            '0.5',
            PARAM_FLOAT
        ));

        $settings->add(new \admin_setting_configtext(
            'aitool_openaitts/top_p',
            get_string('top_p', 'aitool_openaitts'),
            get_string('top_p_desc', 'aitool_openaitts'),
            ''
        ));

        $settings->add(new \admin_setting_configtext(
            'aitool_openaitts/frequency_penalty',
            get_string('frequency_penalty', 'aitool_openaitts'),
            get_string('frequency_penalty_desc', 'aitool_openaitts'),
            ''
        ));

        $settings->add(new \admin_setting_configtext(
            'aitool_openaitts/presence_penalty',
            get_string('presence_penalty', 'aitool_openaitts'),
            get_string('presence_penalty_desc', 'aitool_openaitts'),
            ''
        ));
        return $settings;
    }
}
