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
 * @returns {mixed}
 */
const execMakeRequest = (
    purpose,
    prompt
) => fetchMany([{
    methodname: 'local_ai_manager_post_query',
    args: {
        purpose,
        prompt
    },
}])[0];

/**
 * Executes the call to store input value.
 * @param {string} purpose
 * @param {string} prompt
 * @returns {string}
 */
export const makeRequest = async (purpose, prompt) => {

    const response = await execMakeRequest(purpose, prompt);
    if (response.code != 200) {
        Log.error(response.string);
    }
    if (response.code == 200) {
        Log.info(response.string);
    }
    return response.result;
};