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
 * aipurpose_singleprompt Form class to test request.
 *
 * @package    aipurpose_singleprompt
 * @copyright  ISB Bayern, 2024
* @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace aipurpose_singleprompt\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/tablelib.php');

/**
 * aipurpose_singleprompt privacy provider class.
 *
 * @package    aipurpose_singleprompt
 * @copyright  ISB Bayern, 2024
* @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class settings_test_request_form extends \moodleform {
    /**
     * Define a textarea to define a sql query.
     */
    public function definition() {

        $mform = $this->_form;
        $mform->addElement('submit', 'back', get_string('back'));

        $mform->addElement(
            'text',
            'prompt',
            get_string('prompt', 'aipurpose_singleprompt'),
            'size="320"'
        );

        $this->add_action_buttons(false, get_string('api_test_connection', 'aipurpose_singleprompt'));
    }
}
