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
import {exception as displayException} from 'core/notification';


let aiConfig = null;
let aiInfo = null;
let selectedPurposes = null;

/**
 * Make a request for retrieving the purpose configuration for current tenant.
 *
 * @param {int} contextid the id of the context for which we need the ai configuration
 * @param {string} tenant the tenant identifier or null, if the tenant of the user should be used
 * @param {array} purposes array of purpose strings
 */
const fetchAiConfig = (contextid, tenant = null, purposes) => fetchMany([{
    methodname: 'local_ai_manager_get_ai_config',
    args: {
        contextid,
        tenant,
        purposes
    },
}])[0];

/**
 * Make a request for retrieving general information for the current tenant.
 *
 * @param {string} tenant the tenant identifier or null, if the tenant of the user should be used
 */
const fetchAiInfo = (tenant = null) => fetchMany([{
    methodname: 'local_ai_manager_get_ai_info',
    args: {
        tenant,
    },
}])[0];

const fetchPurposeOptions = (purpose) => fetchMany([{
    methodname: 'local_ai_manager_get_purpose_options',
    args: {
        purpose
    },
}])[0];

/**
 * Executes the call that returns the AI config object with detailed user specific configuration.
 *
 * @param {int} contextid the id of the context for which we need the ai configuration
 * @param {string} tenant the tenant identifier or null, if the tenant of the user should be used
 * @param {array} purposes array of purpose strings
 */
export const getAiConfig = async(contextid, tenant = null, purposes = []) => {
    if (aiConfig === null || purposes !== selectedPurposes) {
        // Store purposes, so we can detect if another call requests different purposes.
        selectedPurposes = purposes;
        aiConfig = await fetchAiConfig(contextid, tenant, purposes);
    }
    return aiConfig;
};

/**
 * Executes the call to get the general info object.
 *
 * @param {string} tenant the tenant identifier or null, if the tenant of the user should be used
 */
export const getAiInfo = async(tenant = null) => {
    if (aiInfo === null) {
        aiInfo = await fetchAiInfo(tenant);
    }
    return aiInfo;
};

export const getPurposeOptions = async(purpose) => {
    let purposeOptions = null;
    try {
        purposeOptions = await fetchPurposeOptions(purpose);
    } catch (exception) {
        await displayException(exception);
    }
    return purposeOptions;
};
