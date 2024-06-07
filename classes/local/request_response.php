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

use Psr\Http\Message\StreamInterface;

/**
 * Data object class for storing prompt result information in a defined way.
 *
 * @package    local_ai_manager
 * @copyright  2024, ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
readonly class request_response {

    private StreamInterface $response;
    private float $executiontime;

    private int $code;
    private string $errormessage;

    private string $debuginfo;

    private function __construct(
    ) {}

    public function set_response(StreamInterface $response): void {
        $this->response = $response;
    }

    public function set_executiontime(float $executiontime): void {
        $this->executiontime = $executiontime;
    }

    public function set_errormessage(string $errormessage): void {
        $this->errormessage = $errormessage;
    }

    public function set_debuginfo(string $debuginfo): void {
        $this->debuginfo = $debuginfo;
    }


    public function set_code(int $code): void {
        $this->code = $code;
    }

    public function get_errormessage(): string {
        return $this->errormessage;
    }

    public function get_debuginfo(): string {
        return $this->debuginfo;
    }

    public function get_response(): StreamInterface {
        return $this->response;
    }

    public function get_executiontime(): float {
        return $this->executiontime;
    }

    public function get_code(): int {
        return $this->code;
    }

    public static function create_from_error(int $code, string $errormessage, string $debuginfo): request_response {
        $requestresponse = new self();
        $requestresponse->set_code($code);
        $requestresponse->set_errormessage($errormessage);
        $requestresponse->set_debuginfo($debuginfo);
        return $requestresponse;
    }

    public static function create_from_result(StreamInterface $response, float $executiontime): request_response {
        $requestresponse = new self();
        $requestresponse->set_code(200);
        $requestresponse->set_response($response);
        $requestresponse->set_executiontime($executiontime);
        return $requestresponse;
    }


}
