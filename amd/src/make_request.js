import {call as fetchMany} from 'core/ajax';

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
    return execMakeRequest(purpose, prompt, component, contextid, JSON.stringify(options));
};
