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

namespace local_ai_manager;

use stdClass;

/**
 * Test class for the ai_manager_utils functions.
 *
 * @package    local_ai_manager
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class ai_manager_utils_test extends \advanced_testcase {

    /**
     * Tests the method get_next_free_itemid.
     *
     * @covers \local_ai_manager\ai_manager_utils::get_next_free_itemid
     */
    public function test_get_next_free_itemid(): void {
        global $DB;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();

        $this->assertEquals(1, ai_manager_utils::get_next_free_itemid('block_ai_chat', 12));

        $record = new stdClass();
        $record->userid = $user->id;
        $record->value = 3.8;
        $record->model = 'testmodel';
        $record->modelinfo = 'testmodel-3.5';
        $record->prompttext = 'some prompt';
        $record->promptcompletion = 'some prompt response';
        $record->component = 'block_ai_chat';
        $record->contextid = 12;
        $record->itemid = 5;
        $record->timecreated = time();
        $DB->insert_record('local_ai_manager_request_log', $record);

        $record = new stdClass();
        $record->userid = $user->id;
        $record->value = 2.3;
        $record->model = 'anothertestmodel';
        $record->modelinfo = 'anothertestmodel-4.0';
        $record->prompttext = 'some other prompt';
        $record->promptcompletion = 'some prompt response';
        $record->component = 'block_ai_chat';
        $record->contextid = 12;
        $record->itemid = 7;
        $record->timecreated = time();
        $DB->insert_record('local_ai_manager_request_log', $record);

        $this->assertEquals(8, ai_manager_utils::get_next_free_itemid('block_ai_chat', 12));

        $record = new stdClass();
        $record->userid = $user->id;
        $record->value = 2.3;
        $record->model = 'anothertestmodel';
        $record->modelinfo = 'anothertestmodel-4.0';
        $record->prompttext = 'some other prompt';
        $record->promptcompletion = 'some prompt response';
        $record->component = 'block_ai_chat';
        // Other context id, so this record should not be relevant.
        $record->contextid = 23;
        $record->itemid = 10;
        $record->timecreated = time();
        $DB->insert_record('local_ai_manager_request_log', $record);

        $this->assertEquals(8, ai_manager_utils::get_next_free_itemid('block_ai_chat', 12));
        $this->assertEquals(1, ai_manager_utils::get_next_free_itemid('mod_ai', 23));
        $this->assertEquals(11, ai_manager_utils::get_next_free_itemid('block_ai_chat', 23));
    }

    /**
     * Tests the function to calculate the closest parent course context.
     *
     * @covers \local_ai_manager\ai_manager_utils::find_closest_parent_course_context
     */
    public function test_find_closest_parent_course_context(): void {
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();
        $coursecat = $this->getDataGenerator()->create_category();
        $subcoursecat = $this->getDataGenerator()->create_category(['parent' => $coursecat->id]);
        $pagecoursemodule = $this->getDataGenerator()->get_plugin_generator('mod_page')->create_instance(
                ['course' => $course->id]
        );
        $pagecoursemodule2 = $this->getDataGenerator()->get_plugin_generator('mod_page')->create_instance(
                ['course' => $course2->id]
        );
        $blockusercontext = $this->getDataGenerator()->create_block('html',
                ['parentcontextid' => \context_user::instance($user->id)->id]);
        $blocksystemcontext = $this->getDataGenerator()->create_block('html',
                ['parentcontextid' => \context_system::instance()->id]);
        $blockcoursecontext = $this->getDataGenerator()->create_block('html',
                ['parentcontextid' => \context_course::instance($course->id)->id]);
        $blockcoursemodulecontext = $this->getDataGenerator()->create_block('html',
                ['parentcontextid' => \context_module::instance($pagecoursemodule->cmid)->id]);

        $coursecontext = \context_course::instance($course->id);
        $course2context = \context_course::instance($course2->id);
        $coursecatcontext = \context_coursecat::instance($coursecat->id);
        $subcoursecatcontext = \context_coursecat::instance($subcoursecat->id);
        $pagecoursemodulecontext = \context_module::instance($pagecoursemodule->cmid);
        $pagecoursemodule2context = \context_module::instance($pagecoursemodule2->cmid);
        $blockusercontextcontext = \context_block::instance($blockusercontext->id);
        $blocksystemcontextcontext = \context_block::instance($blocksystemcontext->id);
        $blockcoursecontextcontext = \context_block::instance($blockcoursecontext->id);
        $blockcoursemodulecontextcontext = \context_block::instance($blockcoursemodulecontext->id);

        $this->assertEquals($coursecontext->id, ai_manager_utils::find_closest_parent_course_context($coursecontext)->id);
        $this->assertNull(ai_manager_utils::find_closest_parent_course_context($coursecatcontext));
        $this->assertNull(ai_manager_utils::find_closest_parent_course_context($subcoursecatcontext));
        $this->assertEquals($coursecontext->id, ai_manager_utils::find_closest_parent_course_context($pagecoursemodulecontext)->id);
        $this->assertNotEquals($coursecontext->id,
                ai_manager_utils::find_closest_parent_course_context($pagecoursemodule2context)->id);
        $this->assertEquals($course2context->id,
                ai_manager_utils::find_closest_parent_course_context($pagecoursemodule2context)->id);
        $this->assertNull(ai_manager_utils::find_closest_parent_course_context($blockusercontextcontext));
        $this->assertNull(ai_manager_utils::find_closest_parent_course_context($blocksystemcontextcontext));
        $this->assertNull(ai_manager_utils::find_closest_parent_course_context($blocksystemcontextcontext));
        $this->assertEquals($coursecontext->id,
                ai_manager_utils::find_closest_parent_course_context($blockcoursecontextcontext)->id);
        $this->assertEquals($coursecontext->id,
                ai_manager_utils::find_closest_parent_course_context($blockcoursemodulecontextcontext)->id);
    }

    /**
     * Test for the get_log_entries method.
     *
     * @covers \local_ai_manager\ai_manager_utils::get_log_entries
     */
    public function test_get_log_entries(): void {
        global $DB;
        $this->resetAfterTest();

        $user = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $record = new stdClass();
        $record->userid = $user->id;
        $record->value = 3.8;
        $record->purpose = 'chat';
        $record->model = 'testmodel';
        $record->modelinfo = 'testmodel-3.5';
        $record->prompttext = 'some prompt 1';
        $record->promptcompletion = 'some prompt response 1';
        $record->component = 'block_ai_chat';
        $record->contextid = 12;
        $record->itemid = 5;
        $record->timecreated = time();
        $DB->insert_record('local_ai_manager_request_log', $record);

        $record = new stdClass();
        $record->userid = $user->id;
        $record->value = 2.3;
        $record->purpose = 'chat';
        $record->model = 'testmodel';
        $record->modelinfo = 'testmodel-3.5';
        $record->prompttext = 'some prompt 2';
        $record->promptcompletion = 'some prompt response 2';
        $record->component = 'block_ai_chat';
        $record->contextid = 13;
        $record->itemid = 7;
        $record->timecreated = time();
        $DB->insert_record('local_ai_manager_request_log', $record);

        $record = new stdClass();
        $record->userid = $user->id;
        $record->value = 1.2;
        $record->purpose = 'translate';
        $record->model = 'testmodel';
        $record->modelinfo = 'testmodel-3.5';
        $record->prompttext = 'some prompt 3';
        $record->promptcompletion = 'some prompt response 3';
        $record->component = 'tiny_ai';
        $record->contextid = 12;
        $record->itemid = 5;
        $record->timecreated = time();
        $DB->insert_record('local_ai_manager_request_log', $record);

        $record = new stdClass();
        $record->userid = $user->id;
        $record->value = 1.2;
        $record->purpose = 'itt';
        $record->model = 'testmodel';
        $record->modelinfo = 'testmodel-3.5';
        $record->prompttext = 'some prompt 4';
        $record->promptcompletion = 'some prompt response 4';
        $record->component = 'tiny_ai';
        $record->contextid = 12;
        $record->itemid = 5;
        $record->timecreated = time();
        $DB->insert_record('local_ai_manager_request_log', $record);

        // Same as the first, but now with different user.
        $record = new stdClass();
        $record->userid = $user2->id;
        $record->value = 1.2;
        $record->purpose = 'chat';
        $record->model = 'testmodel';
        $record->modelinfo = 'testmodel-3.5';
        $record->prompttext = 'some prompt 5';
        $record->promptcompletion = 'some prompt response 5';
        $record->component = 'block_ai_chat';
        $record->contextid = 12;
        $record->itemid = 5;
        $record->timecreated = time();
        $DB->insert_record('local_ai_manager_request_log', $record);

        // Same as the first, but now with different itemid.
        $record = new stdClass();
        $record->userid = $user2->id;
        $record->value = 1.2;
        $record->purpose = 'chat';
        $record->model = 'testmodel';
        $record->modelinfo = 'testmodel-3.5';
        $record->prompttext = 'some prompt 6';
        $record->promptcompletion = 'some prompt response 6';
        $record->component = 'block_ai_chat';
        $record->contextid = 12;
        $record->itemid = 10;
        $record->timecreated = time();
        $DB->insert_record('local_ai_manager_request_log', $record);

        // Same as the first, but now with deleted.
        $record = new stdClass();
        $record->userid = $user2->id;
        $record->value = 1.2;
        $record->purpose = 'chat';
        $record->model = 'testmodel';
        $record->modelinfo = 'testmodel-3.5';
        $record->prompttext = 'some prompt 7';
        $record->promptcompletion = 'some prompt response 7';
        $record->component = 'block_ai_chat';
        $record->contextid = 12;
        $record->itemid = 5;
        $record->deleted = 1;
        $record->timecreated = time();
        $DB->insert_record('local_ai_manager_request_log', $record);

        $logentries = ai_manager_utils::get_log_entries('block_ai_chat', 12);
        $this->assertCount(4, $logentries);
        $this->assertCount(1, array_filter($logentries, fn($logentry) => $logentry->prompttext === 'some prompt 1'));
        $this->assertCount(1, array_filter($logentries, fn($logentry) => $logentry->prompttext === 'some prompt 5'));
        $this->assertCount(1, array_filter($logentries, fn($logentry) => $logentry->prompttext === 'some prompt 6'));
        $this->assertCount(1, array_filter($logentries, fn($logentry) => $logentry->prompttext === 'some prompt 7'));

        $logentries = ai_manager_utils::get_log_entries('block_ai_chat', 13);
        $this->assertCount(1, $logentries);
        $this->assertCount(1, array_filter($logentries, fn($logentry) => $logentry->prompttext === 'some prompt 2'));

        $logentries = ai_manager_utils::get_log_entries('tiny_ai', 13);
        $this->assertCount(0, $logentries);

        $logentries = ai_manager_utils::get_log_entries('tiny_ai', 12);
        $this->assertCount(2, $logentries);
        $this->assertCount(1, array_filter($logentries, fn($logentry) => $logentry->prompttext === 'some prompt 3'));
        $logentries = ai_manager_utils::get_log_entries('tiny_ai', 12, 0, 0, true, '*', ['translate']);
        $this->assertCount(1, $logentries);
        $this->assertCount(1, array_filter($logentries, fn($logentry) => $logentry->prompttext === 'some prompt 3'));

        $logentries = ai_manager_utils::get_log_entries('tiny_ai', 12, 0, 0, true, '*', ['chat']);
        $this->assertCount(0, $logentries);

        $logentries = ai_manager_utils::get_log_entries('block_ai_chat', 12, 0, 0, false);
        $this->assertCount(3, $logentries);
        // Should not contain the deleted entry.
        $this->assertCount(0, array_filter($logentries, fn($logentry) => $logentry->prompttext === 'some prompt 7'));

        // Finally, test selection of database fields.
        $logentries = ai_manager_utils::get_log_entries('block_ai_chat', 12, $user->id, 5, true, 'id,prompttext,promptcompletion');
        $this->assertCount(1, $logentries);
        $entry = reset($logentries);
        $this->assertTrue(property_exists($entry, 'prompttext'));
        $this->assertTrue(property_exists($entry, 'promptcompletion'));
        $this->assertFalse(property_exists($entry, 'component'));
    }
}
