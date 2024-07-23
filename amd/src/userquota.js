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
import {getStrings} from 'core/str';
import Templates from 'core/templates';

const constants = {
    MAXUSAGE_UNLIMITED: 999999
};

const queryCountStrings = {
    chat: 'chat requests',
    feedback: 'feedback requests',
    imggen: 'image generation generation requests',
    singleprompt: 'text requests',
    translate: 'translation requests',
    tts: 'audio requests'
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
export const renderUserQuota = async (selector, purposes) => {
    await localizeQueryCountTexts();

    const targetElement = document.querySelector(selector);
    const userquotaData = await fetchUserquotaData();
    const purposeInfo = [];
    console.log(userquotaData)
    purposes.forEach(purpose => {
        purposeInfo.push(
            {
                purpose,
                'currentusage': userquotaData.usage[purpose].currentusage,
                maxusage: userquotaData.usage[purpose].maxusage,
                'querycounttext': queryCountStrings[purpose],
                showmaxusage: userquotaData.usage[purpose].maxusage !== constants.MAXUSAGE_UNLIMITED,
                islastelement: false
            });
    });
    purposeInfo[purposeInfo.length - 1].islastelement = true;
    console.log(purposeInfo)
    const userquotaContentTemplateContext = {
        purposes: purposeInfo,
        period: userquotaData.period,
        unlimited: userquotaData.role === 'role_unlimited'
    };
    console.log(userquotaContentTemplateContext.unlimited)
    const {html, js} = await Templates.renderForPromise('local_ai_manager/userquota', userquotaContentTemplateContext);
    Templates.appendNodeContents(targetElement, html, js);
};

const localizeQueryCountTexts = async () => {
    const stringsToFetch = [];
    Object.keys(queryCountStrings).forEach((key) => {
        stringsToFetch.push({key: 'requestcount', component: 'aipurpose_' + key});
    });
    const strings = await getStrings(stringsToFetch);
    let i = 0;
    for (const key in queryCountStrings) {
        queryCountStrings[key] = strings[i];
        i++;
    }
}
