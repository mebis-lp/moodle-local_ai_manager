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
 * Module providing functions to send requests to the AI tools.
 *
 * @module     local_ai_manager/make_request
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {call as fetchMany} from 'core/ajax';
import ModalSaveCancel from 'core/modal_save_cancel';
import ModalEvents from 'core/modal_events';

/**
 * Call to store input value
 * @param {string} purpose the purpose to use for the request
 * @param {string} prompt the prompt of the request
 * @param {string} component the component from which the request is being done
 * @param {number} contextid the id of the context from which the request is being done, or 0 for system context
 * @param {string} options additional options, as json encoded string, or an empty string if no additional options
 * @returns {Promise} the request promise
 */
const execMakeRequest = (
    purpose,
    prompt,
    component,
    contextid,
    options
) => fetchMany([{
    methodname: 'local_ai_manager_post_query',
    args: {
        purpose,
        prompt,
        component,
        contextid,
        options
    },
}])[0];

/**
 * Executes the call to store input value.
 *
 * @param {string} purpose the purpose to use for the request
 * @param {string} prompt the prompt of the request
 * @param {string} component the component from which the request is being done
 * @param {number} contextid the id of the context from which the request is being done,
 *  will default to 0 (which means system context)
 * @param {object} options additional options
 * @returns {mixed}
 */
export const makeRequest = async(purpose, prompt, component, contextid = 0, options = {}) => {
    return new Promise((resolve, reject) => {
        return execMakeRequest(purpose, prompt, component, contextid, JSON.stringify(options))
            .then((result) => {
                if (result.code === 477) {
                    return ModalSaveCancel.create({
                        title: "PERSONENBEZOGENE DATEN GEFUNDEN",
                        body: "ES WURDEN PERSONENBEZOGENE DATEN GEFUNDEN. MOECHTEST DU DIESEN PROMPT WIRKLICH ABSCHICKEN?",
                        large: true,
                        buttons: {
                            'save': "ABSCHICKEN",
                            'cancel': "ABBRECHEN",
                        },
                        show: true,
                    });
                } else {
                    return result;
                }
            })
            .then(result => {
                if (result instanceof ModalSaveCancel) {
                    result.getRoot().on(ModalEvents.save, () => {
                        options.disableprecheck = true;
                        resolve(execMakeRequest(purpose, prompt, component, contextid, JSON.stringify(options)));
                    });
                    result.getRoot().on(ModalEvents.cancel, () => {
                        resolve(null);
                    });
                } else {
                    resolve(result);
                }
                return result;
            })
            .catch(error => {
                reject(error);
                return error;
            });
    });
};
