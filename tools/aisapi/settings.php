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
 * Settings for aitool_aisapi.
 *
 * @package    aitool_aisapi
 * @copyright  2025 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

    $settings->add(new admin_setting_configtext('aitool_aisapi/baseurl',
            new lang_string('baseurlsetting', 'aitool_aisapi'),
            new lang_string('baseurlsettingdesc', 'aitool_aisapi'), ''));

    $settings->add(new admin_setting_configtext('aitool_aisapi/globalapikey',
            new lang_string('globalapikeysetting', 'aitool_aisapi'),
            new lang_string('globalapikeysettingdesc', 'aitool_aisapi'), ''));

    $settings->add(new admin_setting_configtextarea('aitool_aisapi/availablemodels',
            new lang_string('availablemodelssetting', 'aitool_aisapi'),
            new lang_string('availablemodelssettingdesc', 'aitool_aisapi'),
            'gpt-3.5-turbo
                gpt-4-turbo#VISION
                gpt-4o-mini#VISION
                gpt-4o-mini#VISION
                meta-llama/CodeLlama-13b-Instruct-hf
                meta-llama/Meta-Llama-3.1-405B-Instruct-FP8
                meta-llama/Meta-Llama-3.1-70B-Instruct
                meta-llama/Meta-Llama-3.1-8B-Instruct
                mistralai/Mistral-7B-Instruct-v0.3
                mistralai/Mixtral-8x7B-Instruct-v0.1'
    ));
}
