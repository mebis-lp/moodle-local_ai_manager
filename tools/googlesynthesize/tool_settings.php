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
 * Admin page for local_mbscleanup LDAP comparison.
 *
 * @package    local_ai_manager
 * @copyright  ISB Bayern
 * @author     Peter Mayer, peter.mayer@isb.bayern.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_OUTPUT_BUFFERING', true);

require_once("../../../../config.php");
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/tablelib.php');

$PAGE->set_context(\context_system::instance());
$PAGE->set_url('/local/ai_manager/tools/chatgpt/tool_settings.php');
require_login(null, false);

admin_externalpage_setup('aitool_googlesynthesize');

if (optional_param('action', '', PARAM_TEXT) == 'storesettings') {
    set_config('openaiapikey', required_param('openaiapikey', PARAM_TEXT), 'aitool_googlesynthesize');
    set_config('sourceoftruth', optional_param('sourceoftruth', '', PARAM_TEXT), 'aitool_googlesynthesize');
    set_config('temperature', optional_param('temperature',0.5, PARAM_TEXT), 'aitool_googlesynthesize');
    set_config('top_p', optional_param('top_p',0.5, PARAM_TEXT), 'aitool_googlesynthesize');
    set_config('frequency_penalty', optional_param('frequency_penalty',0.5, PARAM_TEXT), 'aitool_googlesynthesize');
    set_config('presence_penalty', optional_param('presence_penalty',0.5, PARAM_TEXT), 'aitool_googlesynthesize');
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('openaisettings', 'aitool_googlesynthesize'), 1);

echo $OUTPUT->render_from_template('aitool_googlesynthesize/settings', get_config('aitool_googlesynthesize'));;

echo $OUTPUT->footer();
