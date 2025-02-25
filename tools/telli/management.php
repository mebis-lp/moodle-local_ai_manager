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
 * Allows the admin to get information about the AIS API.
 *
 * @package    aitool_telli
 * @copyright  2025 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use aitool_telli\form\management_form;
use core\http_client;
use core\output\html_writer;
use Psr\Http\Client\ClientExceptionInterface;

require_once(__DIR__ . '/../../../../config.php');

require_admin();

$PAGE->set_context(context_system::instance());

// Execute the controller.
$PAGE->set_heading('AI Manager Subplugins');
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/ai_manager/tools/telli/management.php');

$managementform = new management_form(null);

if ($managementform->is_cancelled()) {
    redirect(new moodle_url('/admin/settings.php', ['section' => 'aitoolpluginsmanagement']));
} else if ($data = $managementform->get_data()) {

    $client = new http_client([
        // We intentionally do not use the global local_ai_manager timeout setting, because here
        // we are not requesting any AI processing, but just query information from the API endpoints.
            'timeout' => 10,
    ]);

    $options['headers'] = [
            'Authorization' => 'Bearer ' . optional_param('apikey', '', PARAM_TEXT),
            'Content-Type' => 'application/json;charset=utf-8',
    ];

    $baseurl = optional_param('baseurl', '', PARAM_URL);
    if (!str_ends_with($baseurl, '/')) {
        $baseurl .= '/';
    }

    $usageendpoint = $baseurl . 'v1/usage';

    try {
        $response = $client->get($usageendpoint, $options);
    } catch (ClientExceptionInterface $exception) {
        throw new \moodle_exception('err_apiresult', 'aitool_telli', '', $exception->getMessage());
    }
    if ($response->getStatusCode() === 200) {
        $usagereturn = $response->getBody()->getContents();
    } else {
        throw new \moodle_exception('err_apiresult', 'aitool_telli', '',
                get_string('statuscode', 'aitool_telli') . ': ' . $response->getStatusCode() . ': ' .
                $response->getReasonPhrase());
    }

    $modelsendpoint = $baseurl . 'v1/models';

    try {
        $response = $client->get($modelsendpoint, $options);
    } catch (ClientExceptionInterface $exception) {
        throw new \moodle_exception('err_apiresult', 'aitool_telli', '', $exception->getMessage());
    }
    if ($response->getStatusCode() === 200) {
        $modelsreturn = $response->getBody()->getContents();
    } else {
        throw new \moodle_exception('err_apiresult', 'aitool_telli', '',
                get_string('statuscode', 'aitool_telli') . $response->getStatusCode() . ': ' . $response->getReasonPhrase());
    }

    echo $OUTPUT->header();

    $managementform->display();

    echo html_writer::tag('hr', '', ['class' => 'mt-5 mb-5']);

    echo $OUTPUT->render_from_template('aitool_telli/management',
            [
                    'usagejson' => json_encode(json_decode($usagereturn),
                            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'modelsjson' => json_encode(json_decode($modelsreturn),
                            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]
    );

    echo $OUTPUT->footer();

} else {
    echo $OUTPUT->header();
    $managementform->display();
    echo $OUTPUT->footer();
}
