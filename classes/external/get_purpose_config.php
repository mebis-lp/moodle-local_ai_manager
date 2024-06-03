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

namespace local_ai_manager\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use local_ai_manager\base_purpose;
use local_ai_manager\local\tenant;

/**
 * Web service to submit a query to an AI tool.
 *
 * @package    local_ai_manager
 * @copyright  ISB Bayern, 2024
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_purpose_config extends external_api {
    /**
     * Describes the parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
                'tenant' => new external_value(PARAM_ALPHANUM, 'The tenant identifier', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Retrieve the purpose config.
     *
     * @param string $tenant The tenant identifier to use
     * @return array associative array containing the result of the request
     */
    public static function execute(string $tenantid): array {
        [
                'tenant' => $tenantid,
        ] = self::validate_parameters(self::execute_parameters(), [
                'tenant' => $tenantid,
        ]);
        if (!empty($tenant)) {
            $tenant = new tenant($tenantid);
            \core\di::set(\local_ai_manager\local\tenant::class, $tenant);
        }
        $tenant = \core\di::get(\local_ai_manager\local\tenant::class);
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('local/ai_manager:use', $context);

        $configmanager = \core\di::get(\local_ai_manager\local\config_manager::class);
        return $configmanager->get_purpose_config();
    }

    /**
     * Describes the return structure of the service.
     *
     * @return external_single_structure the return structure
     */
    public static function execute_returns(): external_single_structure {
        $purposes = base_purpose::get_all_purposes();
        $singlestructuredefinition = [];
        foreach ($purposes as $purpose) {
            $singlestructuredefinition[$purpose] =
                    new external_value(PARAM_ALPHANUM, 'Name of the tool configured for purpose "' . $purpose . '"', VALUE_OPTIONAL);
        }
        return new external_single_structure(
                $singlestructuredefinition,
                'Object containing the tools configured for each purpose'
        );
    }
}
