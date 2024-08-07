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
 * @copyright  2024, ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_manager\form;

use core_plugin_manager;
use local_ai_manager\base_connector;
use local_ai_manager\base_purpose;
use local_ai_manager\local\tenant;
use local_ai_manager\local\userinfo;
use local_ai_manager\manager;
use local_bycsauth\idmgroup;
use local_bycsauth\school;

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

        $mform->addElement('hidden', 'tenant', $tenant->get_tenantidentifier());
        $mform->setType('tenant', PARAM_ALPHANUM);

        $elementarray = [];

        $school = new school($tenant->get_tenantidentifier());
        $idmgrouplist = $school->get_idmgroup_names([idmgroup::IDM_GROUP_TYPE['class'], idmgroup::IDM_GROUP_TYPE['team']]);
        $idmgroupmultiselect = $mform->createElement('select', 'idmgroupids', '', $idmgrouplist,
                ['size' => 2, 'class' => 'local_ai_manager-idmgroupfilter_select pr-1']);
        $idmgroupmultiselect->setMultiple(true);
        $elementarray[] = $idmgroupmultiselect;

        $elementarray[] = $mform->createElement('submit', 'applyfilter', get_string('applyfilter', 'local_ai_manager'));
        $elementarray[] = $mform->createElement('cancel', 'resetfilter', get_string('resetfilter', 'local_ai_manager'));
        $mform->addGroup($elementarray, 'elementarray', get_string('filteridmgroups', 'local_ai_manager'), [' '], false);
    }
}
