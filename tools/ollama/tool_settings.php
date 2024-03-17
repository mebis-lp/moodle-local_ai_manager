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
 * Admin page for aitool_ollama.
 *
 * @package    aitool_ollama
 * @copyright  ISB Bayern
 * @author     Stefan Hanauska <stefan.hanauska@csg-in.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_OUTPUT_BUFFERING', true);

require_once("../../../../config.php");
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/tablelib.php');

$PAGE->set_context(\context_system::instance());
$PAGE->set_url('/local/ai_manager/tools/ollama/tool_settings.php');
require_login(null, false);

admin_externalpage_setup('aitool_ollama');

if (optional_param('action', '', PARAM_TEXT) == 'storesettings') {
    set_config('url', required_param('url', PARAM_TEXT), 'aitool_ollama');
    set_config('apikey', required_param('apikey', PARAM_TEXT), 'aitool_ollama');
    set_config('temperature', required_param('temperature', PARAM_TEXT), 'aitool_ollama');
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('ollamasettings', 'aitool_ollama'), 1);

echo $OUTPUT->render_from_template('aitool_ollama/settings', get_config('aitool_ollama'));;

echo $OUTPUT->footer();
