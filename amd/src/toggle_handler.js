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

import Log from 'core/log';

/**
 * Toggle handler.
 *
 * @module      local_ai_manager/toggle_handler
 * @copyright   2024, ISB Bayern
 * @author      Philipp Memmel
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export const init = (inputid) => {
    const toggle = document.getElementById(inputid);
    if (toggle) {
        toggle.addEventListener('change', (e) => {

            Log.error(toggle.checked)
            Log.error(toggle.dataset)
            // New state incoming.
            if (!toggle.checked) {
                Log.error("Ich ruf targetwhenchecked")
                //console.log(toggle.dataset.targetwhenchecked)
                //return;

                window.location.replace(toggle.dataset.targetwhenchecked);
            } else {
                //console.log(toggle.dataset.targetwhennotchecked)
                //return;
                window.location.replace(toggle.dataset.targetwhennotchecked);
            }
            return false;
        })
    }
}


