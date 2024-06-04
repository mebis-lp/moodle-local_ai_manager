import {call as fetchMany} from 'core/ajax';

/**
 * Make request for retrieving the purpose configuration for current tenant.
 * @param {string} tenant the tenant identifier or nothing if it should be determined from the current user
 */
const execGetPurposeConfig = (
    tenant
) => fetchMany([{
    methodname: 'local_ai_manager_get_purpose_config',
    args: {
        tenant
    },
}])[0];

/**
 * Executes the call to store input value.
 * @param {string} tenant the tenant identifier or nothing if it should be determined from the current user
 */
export const getPurposeConfig = async(tenant) => {
    return execGetPurposeConfig(tenant);
};
