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
 * Module handling the form submission of the statistics tables of local_ai_manager.
 *
 * @module     local_ai_manager/userquota
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {call as fetchMany} from 'core/ajax';
import Log from 'core/log';
import Templates from 'core/templates';

const constants = {
    MAXUSAGE_UNLIMITED: 999999
};

const fetchUserquotaData = () => fetchMany([{
    methodname: 'local_ai_manager_get_user_quota',
    args: {},
}])[0];

/**
 * Renders the current user usage information into the element identified by the given selector.
 *
 * @param {string} selector the id of the element to insert the infobox
 * @param {string[]} purposes the purposes to show user quota for
 */
export const renderUserQuota = async(selector, purposes) => {
    const targetElement = document.querySelector(selector);
    const userquotaData = await fetchUserquotaData();
    const purposeInfo = [];
    purposes.forEach(purpose => {
        // TODO convert 'UNLIMITED' to proper lang string
        const maxusage = userquotaData.usage[purpose].maxusage === constants.MAXUSAGE_UNLIMITED ?
            'UNLIMITED' : userquotaData.usage[purpose].maxusage;
        purposeInfo.push({purpose, 'currentusage': userquotaData.usage[purpose].currentusage, maxusage});
    });
    const userquotaContentTemplateContext = {
        purposes: purposeInfo,
        period: userquotaData.period
    };
    const {html, js} = await Templates.renderForPromise('local_ai_manager/userquota', userquotaContentTemplateContext);
    Templates.appendNodeContents(targetElement, html, js);
};
