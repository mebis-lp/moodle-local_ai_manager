import {call as fetchMany} from 'core/ajax';

let aiConfig = null;

/**
 * Make request for retrieving the purpose configuration for current tenant.
 */
const fetchAiConfig = () => fetchMany([{
    methodname: 'local_ai_manager_get_ai_config',
    args: {},
}])[0];

const fetchPurposeOptions = (
    purpose
) => fetchMany([{
    methodname: 'local_ai_manager_get_purpose_options',
    args: {
        purpose
    },
}])[0];

/**
 * Executes the call to store input value.
 */
export const getAiConfig = async() => {
    if (aiConfig === null) {
        aiConfig = await fetchAiConfig();
    }
    return aiConfig;
};

export const getPurposeOptions = async(purpose) => {
    return await fetchPurposeOptions(purpose);
};
