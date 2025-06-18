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
 * Module handling for adding a new AI tool.
 *
 * @module     local_ai_manager/instanceaddmodal
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getAiInfo} from 'local_ai_manager/config';
import {getStrings} from 'core/str';
import Modal from 'core/modal';

export const renderInstanceAddModal = async(instanceTableSelector) => {
    const instanceTable = document.querySelector(instanceTableSelector);
    const aiInfo = await getAiInfo(instanceTable.dataset.tenant);
    const toolsContext = [];
    const pluginnameStringsToFetch = [];
    aiInfo.tools.forEach((tool) => {
        pluginnameStringsToFetch.push({key: 'pluginname', component: 'aitool_' + tool.name});
    });
    const pluginNameStrings = await getStrings(pluginnameStringsToFetch);

    const descriptionStringsToFetch = [];
    aiInfo.tools.forEach((tool) => {
        descriptionStringsToFetch.push({key: 'adddescription', component: 'aitool_' + tool.name});
    });
    const descriptionStrings = await getStrings(descriptionStringsToFetch);

    for (let i = 0; i < pluginnameStringsToFetch.length; i++) {
        toolsContext.push({
            linklabel: pluginNameStrings[i],
            addurl: aiInfo.tools[i].addurl,
            adddescription: descriptionStrings[i],
        });
    }
    const templateContext = {
        tools: toolsContext
    };
    document.getElementById('local_ai_manager-instanceadd_button').addEventListener('click', async() => {
        const instanceAddModal = await Modal.create({
            template: 'local_ai_manager/instanceaddmodal',
            large: true,
            templateContext
        });
        await instanceAddModal.show();
    });
};
