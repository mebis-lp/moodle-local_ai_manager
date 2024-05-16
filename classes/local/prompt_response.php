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

namespace local_ai_manager\local;

/**
 * Data object class for storing prompt result information in a defined way.
 *
 * @package    local_ai_manager
 * @copyright  2024, ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class prompt_response {

    private string $model;
    private usage $usage;
    private string $content;
    private string $errormessage;
    private string $debuginfo;



    private function __construct(
    ) {}

    public function set_model(string $model): void {
        $this->model = $model;
    }

    public function set_usage(usage $usage): void {
        $this->usage = $usage;
    }

    public function set_content(string $content): void {
        $this->content = $content;
    }

    public function set_errormessage(string $errormessage): void {
        $this->errormessage = $errormessage;
    }

    public function set_debuginfo(string $debuginfo): void {
        $this->errormessage = $debuginfo;
    }

    public function get_modelinfo(): string {
        return $this->model;
    }

    public function get_usage(): usage {
        return $this->usage;
    }

    public function get_content(): string {
        return $this->content;
    }

    public function get_errormessage(): string {
        return $this->errormessage;
    }

    public function get_debuginfo(): string {
        return $this->debuginfo;
    }



    public function is_error(): bool {
        return !empty($this->errormessage);
    }

    public static function create_from_error(string $errormessage, string $debuginfo): prompt_response {
        $promptresponse = new self();
        $promptresponse->set_errormessage($errormessage);
        $promptresponse->set_debuginfo($debuginfo);
        return $promptresponse;
    }

    public static function create_from_result(string $model, usage $usage, string $content): prompt_response {
        $promptresponse = new self();
        $promptresponse->set_model($model);
        $promptresponse->set_usage($usage);
        $promptresponse->set_content($content);
        return $promptresponse;
    }


}
