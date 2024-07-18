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
 * @module     local_ai_manager/render_infobox
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import * as Templates from 'core/templates';
import LocalStorage from 'core/localstorage';
import ModalConfirm from 'local_ai_manager/confirm_modal';

/**
 * Inserts the infobox into the beginning of element with the given selector.
 *
 * Also triggers a confirmation modal the first time it is being rendered by a component.
 *
 * @param {string} component The component name from which this is being called
 * @param {int} userId id of the user
 * @param {string} selector the id of the element to insert the infobox
 * @param {string[]} purposes the purposes which are being used
 */
export const renderInfoBox = async(component, userId, selector, purposes) => {
    const targetElement = document.querySelector(selector);
    const templateContext = {
        'purposes': purposes
    };
    const {html, js} = await Templates.renderForPromise('local_ai_manager/infobox', templateContext);
    Templates.prependNodeContents(targetElement, html, js);
    // We do not want to store the userId in plain text in the local storage, so we hash it.
    const hashKey = await hash(userId + component);
    const localStorageContent = LocalStorage.get(hashKey);
    const currentTime = new Date().getTime();
    // If the box has not been shown for more than 2 hours, we show it again.
    if (!localStorageContent || (currentTime - localStorageContent > 120 * 60 * 1000)) {
        //await ModalConfirm.create({});
        const date = new Date();
        LocalStorage.set(hashKey, date.getTime());
    }
};

/**
 * Hash function to get a hash of a string.
 *
 * @param {string} stringToHash the string to hash
 * @returns {Promise<string>} the promise containing a hex representation of the string encoded by SHA-256
 */
export const hash = async(stringToHash) => {
    const encoder = new TextEncoder();
    const data = encoder.encode(stringToHash);
    const hashAsArrayBuffer = await window.crypto.subtle.digest("SHA-256", data);
    const uint8ViewOfHash = new Uint8Array(hashAsArrayBuffer);
    return Array.from(uint8ViewOfHash)
        .map((b) => b.toString(16).padStart(2, "0"))
        .join("");
};
