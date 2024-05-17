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
 * block_mbsnewcourse course restore form
 *
 * This form gathers information for a quick and fast course restore into a new course.
 *
 * @package    block_mbsnewcourse
 * @copyright  2021, ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_manager\form;


use core_plugin_manager;
use local_ai_manager\base_connector;
use local_ai_manager\base_purpose;
use local_ai_manager\manager;

defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * A form for a user to restore a course instantly into a new one.
 *
 * @copyright  2021, ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class modelconfigform extends \moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        global $USER;
        $tenant = $this->_customdata['tenant'];
        $returnurl = $this->_customdata['returnurl'];

        $mform = &$this->_form;

        $mform->addElement('hidden', 'tenant', $tenant, PARAM_ALPHANUM);

        $mform->addElement('header', 'purposeheader', 'PURPOSES');
        foreach (base_purpose::get_all_purposes() as $purpose) {
            $mform->addElement('select', 'purpose_' . $purpose . '_tool', 'Modell auswählen', manager::get_tools_for_purpose($purpose));

            // TODO Select-Element
        }



        $mform->addElement('text', $purpose . '_apikey', 'Hier API-Key eintragen', ['size'=>'20']);
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
        $draftitemid = file_get_submitted_draft_itemid('backupfile');
        if ($data['course'] == self::UPLOAD_FILE_OPTION && empty(file_get_all_files_in_draftarea($draftitemid))) {
            $errors['errornocoursefile'] = get_string('error_nocoursefilechosen', 'block_mbsnewcourse');
        }
        if (file_get_all_files_in_draftarea($draftitemid)
                && file_get_all_files_in_draftarea($draftitemid)[0]
                && strpos(implode('', array_values($this->backupfilesinformation)),
                        file_get_all_files_in_draftarea($draftitemid)[0]->filename)) {
            $errors['backupfile'] = get_string('error_filealreadyexists', 'block_mbsnewcourse');
        }
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
