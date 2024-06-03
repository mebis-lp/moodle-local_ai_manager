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
 * @module     local_ai_manager/statistics_table
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Pending from 'core/pending';

export const selectors = {
    CHECKBOX: 'input[data-userid]',
    USERIDS_INPUT_FIELD: '#statistics-table-userids'
};

/**
 * Initialize the bulk handling on the statistics table.
 * @param {string} id the id of the table to operate on
 */
export const init = (id) => {
    const pendingPromise = new Pending('local_ai_manager/statistics_table');
    const table = document.getElementById(id);
    table.querySelectorAll(selectors.CHECKBOX).forEach(checkbox => {
        checkbox.addEventListener('change', event => {
            updateUserIds(event.target);
        });
    });
    pendingPromise.resolve();
};

/**
 * Update the user ids.
 *
 * @param {string} checkbox the checkbox object which has been changed
 */
const updateUserIds = (checkbox) => {
    const userIdsInputField = document.querySelector(selectors.USERIDS_INPUT_FIELD);
    const currentValue = userIdsInputField.value.trim().length === 0 ? '' : userIdsInputField.value.trim();
    const currentUserIds = currentValue.length === 0 ? [] : currentValue.split(';');
    const userid = checkbox.dataset.userid;
    if (checkbox.checked && !currentUserIds.includes(userid)) {
        currentUserIds.push(checkbox.dataset.userid);
    }
    if (!checkbox.checked && currentUserIds.includes(userid)) {
        const index = currentUserIds.indexOf(userid);
        currentUserIds.splice(index, 1); // Remove item.
    }
    userIdsInputField.value = currentUserIds.join(';');
};
