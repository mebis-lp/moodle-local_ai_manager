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

use aipurpose_chat\purpose;
use aitool_chatgpt\instance;
use context_system;
use GuzzleHttp\Psr7\Stream;
use local_ai_manager\local\config_manager;
use local_ai_manager\local\connector_factory;
use local_ai_manager\local\prompt_response;
use local_ai_manager\local\request_response;
use local_ai_manager\local\tenant;
use local_ai_manager\local\usage;
use local_ai_manager\local\userinfo;
use local_ai_manager\local\userusage;
use stdClass;

/**
 * Test class for the ai_manager_utils functions.
 *
 * @package    local_ai_manager
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class manager_test extends \advanced_testcase {

    /**
     * Tests the method perform_request.
     *
     * @covers       \local_ai_manager\ai_manager::perform_request
     * @dataProvider perform_request_provider
     */
    public function test_perform_request(array $configuration, int $expectedcode, string $message): void {
        $this->resetAfterTest();

        $tenant = new tenant('1234');

        // Set the capability based on the $configuration.
        $systemcontext = context_system::instance();
        $user = $this->getDataGenerator()->create_user();
        $aiuserroleid = $this->getDataGenerator()->create_role(['shortname' => 'aiuser']);
        role_assign($aiuserroleid, $user->id, $systemcontext->id);
        $permission = $configuration['hasusecapability'] ? CAP_ALLOW : CAP_PROHIBIT;
        assign_capability('local/ai_manager:use', $permission, $aiuserroleid, $systemcontext->id);
        $this->setUser($user);

        // Set if the tenant is allowed based on the $configuration.
        set_config('restricttenants', 1, 'local_ai_manager');
        $allowedtenants = $configuration['tenantallowed'] ? '1234' : '';
        set_config('allowedtenants', $allowedtenants, 'local_ai_manager');

        // Set if the tenant is enabled based on the $configuration.
        // CARE: If the tenant is not allowed this will not have any effect.
        $configmanager = new config_manager($tenant);
        $configmanager->set_config('tenantenabled', $configuration['tenantenabled'] ? 1 : 0);

        // Set locked and confirmed value based on the $configuration.
        $userinfo = new userinfo($user->id);
        $userinfo->set_locked($configuration['locked']);
        $userinfo->set_confirmed($configuration['confirmed']);

        $userinfo->set_scope($configuration['scopecourses'] ? userinfo::SCOPE_COURSES_ONLY : userinfo::SCOPE_EVERYWHERE);

        // Setup some objects for checking contexts.
        $course = $this->getDataGenerator()->create_course();
        switch ($configuration['context']) {
            case 'course':
                $contextid = \context_course::instance($course->id)->id;
                break;
            case 'block_in_course':
                $block = $this->getDataGenerator()->create_block('html',
                        ['parentcontextid' => \context_course::instance($course->id)->id]);
                $contextid = \context_block::instance($block->id)->id;
                break;
            case 'user':
                $contextid = \context_user::instance($user->id)->id;
                break;
            case 'site':
                $contextid = SYSCONTEXTID;
                break;
            case 'block_systemcontext':
                $block = $this->getDataGenerator()->create_block('html',
                        ['parentcontextid' => SYSCONTEXTID]);
                $contextid = \context_block::instance($block->id)->id;
                break;
            case 'block_usercontext':
                $block = $this->getDataGenerator()->create_block('html',
                        ['parentcontextid' => \context_user::instance($user->id)->id]);
                $contextid = \context_block::instance($block->id)->id;
                break;
            default:
                $contextid = null;
        }

        $userinfo->set_role(userinfo::ROLE_BASIC);
        $userinfo->store();

        $configmanager->set_config('chat_max_requests_basic', $configuration['maxrequests']);

        $userusage = new userusage(\core\di::get(connector_factory::class)->get_purpose_by_purpose_string('chat'), $user->id);
        $userusage->set_currentusage($configuration['currentusage']);
        $userusage->store();

        $chatgptinstance = new instance();
        $chatgptinstance->set_model('gpt-4o');

        // Fake a stream object, because we will mock the method that access it anyway.
        $streamresponse = new Stream(fopen('php://temp', 'r+'));
        $requestresponse = request_response::create_from_result($streamresponse);

        // Fake usage object.
        $usage = new usage(50.0, 30.0, 20.0);
        // Fake prompt_response object.
        $promptresponse = prompt_response::create_from_result('gpt-4o', $usage, $message);

        $chatgptconnector =
                $this->getMockBuilder('\aitool_chatgpt\connector')->setConstructorArgs([$chatgptinstance])->getMock();
        $chatgptconnector->expects($this->any())->method('make_request')->willReturn($requestresponse);
        $chatgptconnector->expects($this->any())->method('execute_prompt_completion')->willReturn($promptresponse);
        $connectorfactory =
                $this->getMockBuilder(connector_factory::class)->setConstructorArgs([$configmanager])->getMock();
        $connectorfactory->expects($this->any())->method('get_connector_by_purpose')->willReturn($chatgptconnector);
        $chatpurpose = new purpose();
        $connectorfactory->expects($this->any())->method('get_purpose_by_purpose_string')->willReturn($chatpurpose);
        \core\di::set(config_manager::class, $configmanager);
        \core\di::set(connector_factory::class, $connectorfactory);

        $manager = new manager('chat');

        // Now we finally finished our setup. Call the perform_request method and check the result.

        $options = [];
        if (!is_null($contextid)) {
            $options['contextid'] = $contextid;
        }
        $result = $manager->perform_request('Random string that is irrelevant', $options);
        $this->assertEquals($expectedcode, $result->get_code());
        if ($result->get_code() == 200) {
            $this->assertTrue(str_contains($result->get_content(), $message));
        } else {
            $this->assertTrue(str_contains($result->get_errormessage(), $message));
        }
    }

    /**
     * Data Provider for {@see self::test_perform_request}.
     *
     * It will just test if the manager correctly handles all the different conditions (disabled tenant, locked user, no quota
     * for user left etc.).
     *
     * @return array[] the different test cases
     */
    public static function perform_request_provider(): array {
        $defaultoptions = [
                'hasusecapability' => true,
                'tenantallowed' => true,
                'tenantenabled' => true,
                'locked' => false,
                'confirmed' => true,
                'scopecourses' => false,
                'context' => null,
            // That means that there are more than 0 requests. 0 requests would mean that this role is locked.
                'maxrequests' => 10,
                'currentusage' => 5,
        ];
        return [
                'everythingok' => [
                        'configuration' => $defaultoptions,
                        'expectedcode' => 200,
                        'message' => 'Test result',
                ],
                'userhasnocapability' => [
                        'configuration' => [...$defaultoptions, 'hasusecapability' => false],
                        'expectedcode' => 403,
                        'message' => 'You do not have the capability to use the AI manager',
                ],
                'tenantnotallowed' => [
                        'configuration' => [...$defaultoptions, 'tenantallowed' => false],
                        'expectedcode' => 403,
                        'message' => 'Your tenant manager has not enabled the AI tools feature',
                ],
                'tenantnotenabled' => [
                        'configuration' => [...$defaultoptions, 'tenantenabled' => false],
                        'expectedcode' => 403,
                        'message' => 'Your tenant manager has not enabled the AI tools feature',
                ],
                'userlocked' => [
                        'configuration' => [...$defaultoptions, 'locked' => true],
                        'expectedcode' => 403,
                        'message' => 'Your tenant manager has blocked access to the AI tools for you',
                ],
                'usernotconfirmed' => [
                        'configuration' => [...$defaultoptions, 'confirmed' => false],
                        'expectedcode' => 403,
                        'message' => 'You have not yet confirmed the terms of use',
                ],
                'userscopecourses_course' => [
                        'configuration' => [...$defaultoptions, 'scopecourses' => true, 'context' => 'course'],
                        'expectedcode' => 200,
                        'message' => 'Test result',
                ],
                'userscopecourses_block_in_course' => [
                        'configuration' => [...$defaultoptions, 'scopecourses' => true, 'context' => 'block_in_course'],
                        'expectedcode' => 200,
                        'message' => 'Test result',
                ],
                'userscopecourses_user' => [
                        'configuration' => [...$defaultoptions, 'scopecourses' => true, 'context' => 'user'],
                        'expectedcode' => 403,
                        'message' => 'You do not have the permission to use AI tool outside courses',
                ],
                'userscopecourses_site' => [
                        'configuration' => [...$defaultoptions, 'scopecourses' => true, 'context' => 'site'],
                        'expectedcode' => 403,
                        'message' => 'You do not have the permission to use AI tool outside courses',
                ],
                'userscopecourses_block_systemcontext' => [
                        'configuration' => [...$defaultoptions, 'scopecourses' => true, 'context' => 'block_systemcontext'],
                        'expectedcode' => 403,
                        'message' => 'You do not have the permission to use AI tool outside courses',
                ],
                'userscopecourses_block_usercontext' => [
                    // This for example are blocks you added to your own dashboard. They have user context.
                        'configuration' => [...$defaultoptions, 'scopecourses' => true, 'context' => 'block_usercontext'],
                        'expectedcode' => 403,
                        'message' => 'You do not have the permission to use AI tool outside courses',
                ],
                'purposedisabledforrole' => [
                        'configuration' => [...$defaultoptions, 'maxrequests' => 0],
                        'expectedcode' => 403,
                        'message' => 'Your tenant manager has disabled this purpose for your user type',
                ],
                'usagelimitreached' => [
                        'configuration' => [...$defaultoptions, 'currentusage' => $defaultoptions['maxrequests'] + 1],
                        'expectedcode' => 429,
                        'message' => 'You have reached the maximum amount of requests',
                ],
        ];
    }
}
