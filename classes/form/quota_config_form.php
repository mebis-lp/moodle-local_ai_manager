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

namespace local_ai_manager\form;

use core_plugin_manager;
use local_ai_manager\base_connector;
use local_ai_manager\base_purpose;
use local_ai_manager\local\userinfo;
use local_ai_manager\local\userusage;
use local_ai_manager\manager;

defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * User config form.
 *
 * This form gathers information for configuring user specific configurations for local_ai_manager.
 *
 * @package    local_ai_manager
 * @copyright  2024, ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quota_config_form extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        $tenant = $this->_customdata['tenant'];

        $mform = &$this->_form;

        $mform->addElement('hidden', 'tenant', $tenant);
        $mform->setType('tenant', PARAM_ALPHANUM);

        $mform->addElement(
                'header',
                'general_user_config_settings_header',
                get_string('general_user_settings', 'local_ai_manager')
        );

        $mform->addElement(
                'duration',
                'max_requests_period',
                get_string('max_request_time_window', 'local_ai_manager'),
                ['units' => [HOURSECS, DAYSECS, WEEKSECS]]
        );
        $mform->setType('max_requests_period', PARAM_INT);
        $mform->setDefault('max_requests_period', userusage::MAX_REQUESTS_DEFAULT_PERIOD);

        foreach (base_purpose::get_all_purposes() as $purpose) {
            $mform->addElement(
                    'header',
                    $purpose . '_purpose_config_header',
                    get_string('max_requests_purpose_heading', 'local_ai_manager',
                            get_string('pluginname', 'aipurpose_' . $purpose))
            );
            $mform->addElement(
                    'text',
                    $purpose . '_max_requests_basic',
                    get_string('max_requests_purpose', 'local_ai_manager', get_string('role_basic', 'local_ai_manager'))
            );
            $mform->setType($purpose . '_max_requests_basic', PARAM_INT);
            $mform->setDefault($purpose . '_max_requests_basic', userusage::MAX_REQUESTS_DEFAULT_ROLE_BASE);

            //$purposegroup[] = $mform->createElement('text', $purpose . '_max_requests_extended', 'MAXIMALE REQUESTS EXTENDED');
            $mform->addElement(
                    'text',
                    $purpose . '_max_requests_extended',
                    get_string('max_requests_purpose', 'local_ai_manager', get_string('role_extended', 'local_ai_manager'))
            );
            $mform->setType($purpose . '_max_requests_extended', PARAM_INT);
            $mform->setDefault($purpose . '_max_requests_extended', userusage::MAX_REQUESTS_DEFAULT_ROLE_EXTENDED);
            //$mform->addGroup($purposegroup, $purpose . '_maxrequests_config_group', 'test', [' '], false);
        }

        $this->add_action_buttons();
        /*


        $mform->addElement('header', 'choosecategoryheader', get_string('choosecategory', 'block_mbsnewcourse'));

        // ...print out a select box with appropriate course cats (i. e. below school cat).
        $categories = \local_mbs\local\schoolcategory::make_schoolcategories_list($schoolcategory);
        $mform->addElement('select', 'category', '', $categories);
        $mform->setDefault('category', $schoolcategory->id);
        $mform->addHelpButton('choosecategoryheader', 'choosecategory', 'block_mbsnewcourse');

        $mform->addElement('header', 'choosecoursefileheader', get_string('choosecoursefile', 'block_mbsnewcourse'));
        $mform->addElement('static', 'errornocoursefile');
        $this->backupfilesinformation = $this->generate_backupfiles_list();
        $mform->addElement('html', '<div class="block_mbsnewcourse-restorefilelist">');
        foreach ($this->backupfilesinformation as $pathnamehash => $filename) {
            $mform->addElement('radio', 'course', '', $filename, $pathnamehash, '');
        }
        $mform->setDefault('course', self::UPLOAD_FILE_OPTION);
        $mform->addHelpButton('choosecoursefileheader', 'choosecoursefile', 'block_mbsnewcourse');
        $mform->addElement('html', '</div>');

        $categorycontext = \context_coursecat::instance($categoryid);
        $usercontext = \context_user::instance($USER->id);
        $managefilesurl = new \moodle_url('/backup/backupfilesedit.php',
                ['contextid' => $usercontext->id, 'currentcontext' => $categorycontext->id, 'filearea' => 'backup',
                        'component' => 'user', 'returnurl' => $returnurl, ]);
        $buttonhtml = \html_writer::link($managefilesurl, get_string('managefiles', 'backup'),
                ['class' => 'btn btn-secondary']);
        $mform->addElement('static', 'managefilesbutton', '', $buttonhtml);

        $mform->addElement('filemanager', 'backupfile', get_string('uploadbackupfile', 'block_mbsnewcourse'), null,
                ['subdirs' => 0, 'maxfiles' => 1,
                        'accepted_types' => ['.mbz'], 'return_types' => FILE_INTERNAL, ]);
        $mform->addHelpButton('backupfile', 'uploadbackupfile', 'block_mbsnewcourse');
        $mform->hideIf('backupfile', 'course', 'notchecked', self::UPLOAD_FILE_OPTION);

        $this->add_action_buttons(true, get_string('restorecourse', 'block_mbsnewcourse'));*/
    }

    /**
     * Some extra validation.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files): array {
        $errors = [];
        if (isset($data['max_requests_period']) && intval($data['max_requests_period']) < userusage::MAX_REQUESTS_MIN_PERIOD) {
            // TODO localize
            $errors['max_requests_period'] = get_string('error_max_requests_period', 'local_ai_manager');
            'Period needs to be at least 1 day';
        }
        // TODO validate
        return $errors;
    }

    /**
     * Resets the form to its default values.
     *
     * @return void
     */
    public function reset_form() {
        $this->_form->updateSubmission(null, null);
    }
}
