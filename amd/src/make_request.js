import {call as fetchMany} from 'core/ajax';
import Log from 'core/log';

export const init = (purpose, prompt) => {

    // Nothing to do if the purpose is empty.
    if (!purpose) {
        return;
    }

    // Nothing to do if the prompt is empty.
    if (!prompt) {
        return;
    }

    return makeRequest(purpose, prompt);
};

/**
 * Call to store input value
 * @param {string} purpose
 * @param {string} prompt
 * @param {array} options
 * @returns {mixed}
 */
const execMakeRequest = (
    purpose,
    prompt,
    options
) => fetchMany([{
    methodname: 'local_ai_manager_post_query',
    args: {
        purpose,
        prompt,
        options
    },
}])[0];

/**
 * Executes the call to store input value.
 * @param {string} purpose
 * @param {string} prompt
 * @param {array} options
 * @returns {mixed}
 */
export const makeRequest = async (purpose, prompt, options = {}) => {

    const response = await execMakeRequest(purpose, prompt, options);
    return response;

};
