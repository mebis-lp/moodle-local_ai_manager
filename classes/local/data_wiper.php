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

namespace local_ai_manager\local;

use core\clock;
use local_ai_manager\base_instance;
use local_ai_manager\hook\custom_tenant;
use stdClass;

/**
 * Class for handling anonymizing user data.
 *
 * @package    local_ai_manager
 * @copyright  2025 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class data_wiper {

    /** @var int the date until which all data should be anonymized. */
    private int $anonymizedate;

    /** @var int the date until which all data should be deleted. */
    private int $deletedate;

    /** @var string String that is being used for overwriting prompts and prompt completions to anonymize data. */
    const ANONYMIZE_STRING = 'anonymized';

    /**
     * Creates the anonymizer object.
     */
    public function __construct() {
        $this->anonymizedate = intval(get_config('local_ai_manager', 'datawiperanonymizedate'));
        $this->deletedate = intval(get_config('local_ai_manager', 'datawiperdeletedate'));
    }

    public function wipe_data(): void {
        if ($this->anonymizedate > $this->deletedate) {
            // Only if the records will not be deleted anyway by the delete_data method, it makes sense
            // to anonymize them.
            $this->anonymize_data();
        }
        $this->delete_data();
    }

    public function anonymize_data(): void {
        global $DB;
        $rs = $DB->get_recordset_select('local_ai_manager_request_log', "timecreated < :anonymizedate",
                ['anonymizedate' => $this->anonymizedate]
        );
        foreach ($rs as $record) {
            $this->anonymize_request_log_record($record);
        }
        $rs->close();
    }

    public function delete_data(): void {
        global $DB;
        $rs = $DB->get_recordset_select('local_ai_manager_request_log', "timecreated < :deletedate",
                ['deletedate' => $this->deletedate]
        );
        foreach ($rs as $record) {
            $DB->delete_records('local_ai_manager_request_log', ['id' => $record->id]);
        }
        $rs->close();
    }

    /*public static function randomize_string(string $string): string {
        $length = mb_strlen($string, 'UTF-8');
        if ($length === 0) {
            return '';
        }

        $random = strtr(
                base64_encode(random_bytes((int)ceil($length * 0.75))),
                '+/', '-_'
        );

        // Ensure that the output has the correct utf-8 length.
        return mb_substr($random, 0, $length, 'UTF-8');
    }*/

    public function anonymize_request_log_record(stdClass $record): void {
        global $DB;
        $record->userid = null;
        $record->prompttext = self::ANONYMIZE_STRING;
        $record->promptcompletion = self::ANONYMIZE_STRING;
        $record->requestoptions = self::ANONYMIZE_STRING;
        $DB->update_record('local_ai_manager_request_log', $record);
    }

    public function anonymize_request_log_for_user(int $userid): void {
        global $DB;
        $rs = $DB->get_recordset_select('local_ai_manager_request_log', "userid = :userid AND timecreated < :anonymizedate",
                ['userid' => $userid, 'anonymizedate' => $this->anonymizedate]
        );
        foreach ($rs as $record) {
            $this->anonymize_request_log_record($record);
        }
        $rs->close();
    }

    // TODO Anonymize/remove record from userinfo table, userusage table
}
