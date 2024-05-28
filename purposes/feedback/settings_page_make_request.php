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
 * Admin page for local_bycs_webportal api testing.
 *
 * @package    aipurpose_feedback
 * @copyright  ISB Bayern, 2024
* @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../../../config.php");
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');

$PAGE->set_context(\context_system::instance());
$PAGE->set_url('/local/ai_manager/purposes/feedback/settings_page_make_request.php');
require_login(null, false);

$form = new \aipurpose_feedback\form\settings_test_request_form();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('settings_test_tool_heading', 'aipurpose_feedback'), 2);

echo $form->render();
echo $OUTPUT->footer();
