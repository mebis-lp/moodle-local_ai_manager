<?php
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
 * Admin config settings page
 *
 * @package    local_ai_manager
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ai_manager\admin;

use admin_setting_heading;
use html_writer;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/moodlelib.php');

/**
 * Settings for label type admin setting.
 *
 * @package    local_ai_manager
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class setting_button extends admin_setting_heading {
    /** @var string Button label */
    protected $label;
    /** @var string Button href */
    protected $href;
    /** @var string Button css */
    protected $additionalcssclasses;

    /**
     * A button element
     *
     * @param string $name                  unique ascii name.
     * @param string $visiblename           heading
     * @param string $description           description of what the button does
     * @param string $label                 what is written on the button
     * @param string $href                  the URL directed to on click
     * @param string $additionalcssclasses  additional css classes
     */
    public function __construct(string $name, string $visiblename, string $description, string $label, string $href, string $additionalcssclasses) {
        $this->nosave = true;
        $this->label = $label;
        $this->href = $href;
        $this->additionalcssclasses = $additionalcssclasses;
        parent::__construct($name, $visiblename, $description, '');
    }

    /**
     * Returns an HTML string
     * @param mixed $data
     * @param string $query
     * @return string Returns an HTML string
     */
    public function output_html($data, $query = '') {
        global $OUTPUT;
        $context = (object)[
            'label'    => $this->label,
            'href'     => $this->href,
            'additionalcssclasses'     => $this->additionalcssclasses,
            'forceltr' => $this->get_force_ltr(),
        ];

        $element = $OUTPUT->render_from_template('local_ai_manager/setting_configbutton', $context);

        return format_admin_setting($this, $this->visiblename, $element, $this->description);
    }
}
