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

namespace local_ai_manager;

use core\http_client;
use local_ai_manager\local\prompt_response;
use local_ai_manager\local\request_response;
use local_ai_manager\local\unit;
use Psr\Http\Message\StreamInterface;

/**
 * Base class for connector subplugins.
 *
 * @package    local_ai_manager
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base_connector {

    protected connector_instance $instance;

    /**
     * Define available models.
     *
     * @return array names of the available models
     */
    public abstract function get_models(): array;

    public abstract function get_unit(): unit;

    private function get_endpoint_url(): string {
        return $this->instance->get_endpoint();
    }

    private function get_api_key(): string {
        return $this->instance->get_apikey();
    }

    public function supported_purposes(): array {
        return base_purpose::get_all_purposes();
    }

    /**
     * Retrieves the data for the prompt based on the prompt text.
     *
     * @param string $prompttext The prompt text.
     * @return array The prompt data.
     */
    public abstract function get_prompt_data(string $prompttext, array $requestoptions): array;

    public abstract function execute_prompt_completion(StreamInterface $result, array $options = []): prompt_response;

    public function has_customvalue1(): bool {
        return false;
    }

    public function has_customvalue2(): bool {
        return false;
    }

    public function get_instance(): connector_instance {
        return $this->instance;
    }

    /**
     * Makes a request to the specified URL with the given data and API key.
     *
     * Can be used for most tools without any changes. In case changes are needed, it's possible to overwrite, but please only do
     * if really necessary.
     *
     * @param array $data The data to send with the request.
     * @return array The response from the request.
     * @throws \moodle_exception If the API key is empty.
     */
    public function make_request(array $data, bool $multipart = false): request_response {
        $client = new http_client([
                // TODO Make timeout higher, LLM requests can take quite a bit of time
                'timeout' => 20,
        ]);

        $contenttype = $multipart ? 'multipart/form-data' : 'application/json;charset=utf-8';

        $options['headers'] = [
                'Authorization' => 'Bearer ' . $this->get_api_key(),
                'Content-Type' => $contenttype,
        ];
        $options['body'] = json_encode($data);

        $start = microtime(true);

        $response = $client->post($this->get_endpoint_url(), $options);
        $end = microtime(true);
        $executiontime = round($end - $start, 2);
        if ($response->getStatusCode() === 200) {
            $return = request_response::create_from_result($response->getBody(), $executiontime);
        } else {
            // TODO localize
            $return = request_response::create_from_error(
                    'Sending request to tool api endpoint failed with code ' . $response->getStatusCode(),
                    $response->getBody()
            );
        }
        return $return;
    }
}
