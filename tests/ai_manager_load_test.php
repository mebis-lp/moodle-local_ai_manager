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

use local_ai_manager\ai_manager_utils;
use local_ai_manager\local\userstats_table;
use local_ai_manager\local\userusage;
use local_ai_manager\local\userinfo;
use local_ai_manager\local\tenant;
use aipurpose_chat\purpose;

require_once(__DIR__ . '/generator/lib.php');

/**
 * Load tests for AI Manager
 *
 * @package    local_ai_manager
 * @category   test
 * @copyright  2024 ISB Bayern
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class ai_manager_load_test extends \advanced_testcase {
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Create a huge load of request log entries and test the performance of the userstats_table and userusage classes.
     */
    public function test_huge_load(): void {
        global $DB;

        $institution = tenant::DEFAULT_TENANTIDENTIFIER;
        $limit = 1000000;

        $tenant = new tenant($institution);
        \core\di::set(\local_ai_manager\local\tenant::class, $tenant);

        $instanceid = $DB->insert_record('local_ai_manager_instance', (object) [
            'connector' => 'chatgpt',
            'tenant' => $institution,
            'model' => 'gpt-4-turbo',
        ]);

        $config = [
            'tenantenabled' => 1,
            'purpose_chat_tool' => $instanceid,
        ];

        foreach ($config as $key => $value) {
            $DB->insert_record('local_ai_manager_config', (object) [
                'configkey' => $key,
                'configvalue' => $value,
                'tenant' => $institution,
            ]);
        }
        $user = $this->getDataGenerator()->create_user(['institution' => tenant::DEFAULT_TENANTIDENTIFIER]);

        $this->setUser($user);
        $manager = new manager('chat');
        $generator = new local_ai_manager_generator($manager);

        for ($i=0; $i < $limit; $i++) {
            $generator->create_request_log_entry($i % ($limit / 5));
        }

        $this->setAdminUser();

        $start = microtime(true);
        $userstats = new userstats_table(rand(), 'chat', $tenant, new \moodle_url('/'));
        $stop = microtime(true);

        $this->assertLessThanOrEqual(1, $stop - $start);

        $start = microtime(true);
        $userinfo = new userusage(new purpose(), $user->id);
        $stop = microtime(true);

        $this->assertLessThanOrEqual(1, $stop - $start);
    }
}
