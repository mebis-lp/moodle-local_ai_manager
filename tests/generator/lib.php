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

use local_ai_manager\manager;
use local_ai_manager\local\prompt_response;
use local_ai_manager\local\usage;

/**
 * Data generator class
 *
 * @package    local_ai_manager
 * @category   test
 * @copyright  2024 ISB Bayern
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_ai_manager_generator extends \component_generator_base{
    /** @var manager manager instance */
    private manager $manager;

    /**
     * Constructor
     * @param manager $manager manager instance
     */
    public function __construct(manager $manager) {
        $this->manager = $manager;
    }
    /**
     * 
     */
    public function create_request_log_entry($itemid = 0) {
        $prompttext = 'Please tell me a joke';
        $usage = new usage(10);
        $promptcompletion = prompt_response::create_from_result('chatgpt', $usage, 'This is very funny');
        $promptcompletion->set_model('chatgpt');
        $promptcompletion->set_content('This is very funny');
        $requestoptions = [];
        $options = [
            'itemid' => $itemid,
        ];
            
        $this->manager->log_request($prompttext, $promptcompletion, $requestoptions, $options);
    }
}
