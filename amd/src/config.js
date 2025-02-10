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
 * Module handling the retrieving of the ai config object.
 *
 * @module     local_ai_manager/config
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {call as fetchMany} from 'core/ajax';

let aiConfig = null;

/**
 * Make request for retrieving the purpose configuration for current tenant.
 *
 * @param {string} tenant the tenant identifier or null, if the tenant of the user should be used
 */
const fetchAiConfig = (tenant = null) => fetchMany([{
    methodname: 'local_ai_manager_get_ai_config',
    args: {
        tenant
    },
}])[0];

const fetchPurposeOptions = (purpose) => fetchMany([{
    methodname: 'local_ai_manager_get_purpose_options',
    args: {
        purpose
    },
}])[0];

/**
 * Executes the call to store input value.
 *
 * @param {string} tenant the tenant identifier or null, if the tenant of the user should be used
 */
export const getAiConfig = async(tenant = null) => {
    if (aiConfig === null) {
        aiConfig = await fetchAiConfig(tenant);
    }
    return aiConfig;
};

export const getPurposeOptions = async(purpose) => {
    return await fetchPurposeOptions(purpose);
};
