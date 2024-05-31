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
 * Configuration page for tenants.
 *
 * @package    local_ai_manager
 * @copyright  2024, ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');

global $CFG, $DB, $OUTPUT, $PAGE, $USER;

$tenant = optional_param('tenant', '', PARAM_ALPHANUM);
$purpose = optional_param('purpose', '', PARAM_ALPHANUM);

$url = new moodle_url('/local/ai_manager/statistics.php');
$PAGE->set_url($url);

$returnurl = new moodle_url('/course/index.php');

// Check permissions.
require_login();

if (!empty($tenantid)) {
    $tenant = new \local_ai_manager\local\tenant($tenantid);
    \core\di::set(\local_ai_manager\local\tenant::class, $tenant);
}
$tenant = \core\di::get(\local_ai_manager\local\tenant::class);
$accessmanager = \core\di::get(\local_ai_manager\local\access_manager::class);
$accessmanager->require_tenant_manager();

$PAGE->set_context($tenant->get_tenant_context());

$strtitle = 'STATISTIK';
$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitle);
$PAGE->navbar->add($strtitle);


echo $OUTPUT->header();
echo $OUTPUT->heading($strtitle);
echo $OUTPUT->render_from_template('local_ai_manager/tenantconfignavbar', []);


$download = optional_param('download', '', PARAM_ALPHA);

$table = new \local_ai_manager\local\userstats_table($purpose, $tenant);

$rows = [];

$pagesize = 5;
$recordscount = 50;

for ($i = 0; $i <= $recordscount; $i++) {
    $testentry = [];
    $testentry['id'] = $i;
    $testentry['firstname'] = "Hans " . $i;
    $testentry['lastname'] = "Mustermann " . $i;
    $testentry['locked'] = 0;
    $testentry['currentusage'] = rand();
    $rows[] = $testentry;
}
$pagedrows = [];
for ($i = 0; $i <= $recordscount; $i++) {
    if ($i >= $table->get_page_start() && $i < $table->get_page_start() + $pagesize) {
        $pagedrows[] = $rows[$i];
    }
}
$table->format_and_add_array_of_rows($pagedrows, false);


$table->finish_output();


echo $OUTPUT->footer();
