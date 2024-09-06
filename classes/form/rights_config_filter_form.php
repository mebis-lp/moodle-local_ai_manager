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
 * User config config form.
 *
 * This form handles the locking and unlocking of users on the statistics overview pages.
 *
 * @package    local_ai_manager
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_manager\form;

defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * A form for filtering IDM groups.
 *
 * @copyright  2021, ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rights_config_filter_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        $tenant = \core\di::get(\local_ai_manager\local\tenant::class);
        $mform = &$this->_form;
        $filteroptions = $this->_customdata['filteroptions'];

        $mform->addElement('hidden', 'tenant', $tenant->get_identifier());
        $mform->setType('tenant', PARAM_ALPHANUM);

        $elementarray = [];

        $filteroptionsmultiselect = $mform->createElement('select', 'filterids', '', $filteroptions,
                ['size' => 2, 'class' => 'local_ai_manager-filter_select pr-1']);
        $filteroptionsmultiselect->setMultiple(true);
        $elementarray[] = $filteroptionsmultiselect;

        $elementarray[] = $mform->createElement('submit', 'applyfilter', get_string('applyfilter', 'local_ai_manager'));
        $elementarray[] = $mform->createElement('cancel', 'resetfilter', get_string('resetfilter', 'local_ai_manager'));
        $mform->addGroup($elementarray, 'elementarray', get_string('filterheading', 'local_ai_manager'), [' '], false);
    }
}
