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
 * Module handling and rendering the prompt views.
 *
 * @module     local_ai_manager/viewprompts
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getString} from 'core/str';
import {call as fetchMany} from 'core/ajax';
import Modal from 'core/modal';
import Templates from 'core/templates';

const templateContext = {};


export const init = (tableWrapperId) => {
    const table = document.getElementById(tableWrapperId);
    table.querySelectorAll('[data-view-prompts-userid]').forEach(viewLink => {
        viewLink.addEventListener('click', async() => {
            const userId = viewLink.dataset.viewPromptsUserid;
            const contextId = table.dataset.contextid;
            const contextDisplayName = table.dataset.contextdisplayname;
            const userDisplayName = viewLink.dataset.viewPromptsUserdisplayname;

            const currentTime = new Date();
            const lastDay = Math.floor(new Date(currentTime.getTime() - 24 * 60 * 60 * 1000).getTime() / 1000);
            const lastWeek = Math.floor(new Date(currentTime.getTime() - 7 * 24 * 60 * 60 * 1000).getTime() / 1000);
            const lastMonth = Math.floor(new Date(currentTime.getTime() - 30 * 24 * 60 * 60 * 1000).getTime() / 1000);
            templateContext.lastday = lastDay;
            templateContext.lastweek = lastWeek;
            templateContext.lastmonth = lastMonth;

            // By default, we load prompts of the last 24 hours.
            const resultObject = await getPrompts(contextId, userId, templateContext.lastday);

            templateContext.userid = userId;
            templateContext.contextid = contextId;
            templateContext.heading =
                await getString('promptsmodalheading', 'local_ai_manager', {contextDisplayName, userDisplayName});
            templateContext.promptsobjects = resultObject.result;
            templateContext.promptsdatesavailable =
                templateContext.promptsobjects.reduce((acc, cur) => acc || cur.viewpromptsdates, false);
            templateContext.noprompts = templateContext.promptsobjects.length === 0;
            templateContext.classes = 'local_ai_manager-prompts_view_modal';
            const modal = await Modal.create({
                template: 'local_ai_manager/promptsmodal',
                templateContext,
                show: true,
                removeOnClose: true,
            });
            registerTimeselectorListener(modal, contextId, userId);
            modal.registerCloseOnCancel();
        });
    });
};

/**
 * Registers a listener on the timeselector select that updates the prompts table.
 *
 * @param {object} modal the modal object
 * @param {int} contextId the context id of the main context to load prompts for
 * @param {int} userId the user id of the current user
 */
const registerTimeselectorListener = (modal, contextId, userId) => {
    const timeselector = modal.getModal()[0].querySelector('[data-local_ai_manager-prompts_view="timeselector"]');
    timeselector.addEventListener('change', async() => {
        const resultObject = await getPrompts(contextId, userId, timeselector.value);
        templateContext.promptsobjects = resultObject.result;
        templateContext.promptsdatesavailable =
            templateContext.promptsobjects.reduce((acc, cur) => acc || cur.viewpromptsdates, false);
        templateContext.noprompts = templateContext.promptsobjects.length === 0;

        const {html, js} = await Templates.renderForPromise('local_ai_manager/promptsmodal_table', {...templateContext});
        Templates.replaceNode(modal.getModal()[0].querySelector('[data-local_ai_manager-prompts_view="table"]'), html, js);
    });
};

/**
 * Fetch the prompts from the backend.
 *
 * @param {int} contextid The context id of the context to retrieve the prompts in
 * @param {int} userid the id of the user to retrieve the prompts for
 * @param {int} time the timestamp since when we want to retrieve prompts
 */
const getPrompts = async(contextid, userid, time) => fetchMany([{
    methodname: 'local_ai_manager_get_prompts',
    args: {
        contextid,
        userid,
        time
    },
}])[0];


