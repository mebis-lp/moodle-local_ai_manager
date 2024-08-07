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
use core_plugin_manager;
use local_ai_manager\local\prompt_response;
use local_ai_manager\local\request_response;
use local_ai_manager\local\unit;
use Psr\Http\Client\ClientExceptionInterface;
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

    protected base_instance $instance;

    /**
     * Define available models.
     *
     * @return array names of the available models
     */
    public abstract function get_models_by_purpose(): array;

    public final function get_models(): array {
        $models = [];
        foreach ($this->get_models_by_purpose() as $modelarray) {
            $models = array_merge($models, $modelarray);
        }
        return array_unique($models);
    }

    public abstract function get_unit(): unit;

    protected function get_endpoint_url(): string {
        return $this->instance->get_endpoint();
    }

    protected function get_api_key(): string {
        return $this->instance->get_apikey();
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

    public function get_instance(): base_instance {
        return $this->instance;
    }

    public function get_available_options(): array {
        return [];
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
    public function make_request(array $data): request_response {
        $client = new http_client([
            'timeout' => get_config('local_ai_manager', 'requesttimeout'),
            'verify' => !empty(get_config('local_ai_manager', 'verifyssl')),
        ]);

        $options['headers'] = $this->get_headers();
        $options['body'] = json_encode($data);

        try {
            $response = $client->post($this->get_endpoint_url(), $options);
        } catch (ClientExceptionInterface $exception) {
            return $this->create_error_response_from_exception($exception);
        }
        if ($response->getStatusCode() === 200) {
            $return = request_response::create_from_result($response->getBody());
        } else {
            // TODO localize
            $return = request_response::create_from_error(
                    $response->getStatusCode(),
                    'Sending request to tool api endpoint failed',
                    $response->getBody(),
            );
        }
        return $return;
    }

    public static final function get_all_connectors(): array {
        return core_plugin_manager::instance()->get_enabled_plugins('aitool');
    }

    protected function create_error_response_from_exception(ClientExceptionInterface $exception): request_response {
        $message = '';
        // This is actually pretty bad, but it does not seem possible to get to these kind of errors through some kind of
        // Guzzle API functions, so we have to hope the cURL error messages are kinda stable.
        if (str_contains($exception->getMessage(), 'cURL error')) {
            if (str_contains($exception->getMessage(), 'cURL error 28')) {
                $message = get_string('exception_curl28', 'local_ai_manager');
            } else if (str_contains($exception->getMessage(), 'cURL error')) {
                $message = get_string('exception_curl', 'local_ai_manager');
            }
        } else {
            switch ($exception->getCode()) {
                case 401:
                    $message = get_string('exception_http401', 'local_ai_manager');
                    break;
                case 429:
                    $message = get_string('exception_http429', 'local_ai_manager');
                    break;
                case 500:
                    $message = get_string('exception_http500', 'local_ai_manager');
                    break;
                default:
                    $message = get_string('exception_default', 'local_ai_manager');
            }
        }
        $debuginfo = $exception->getMessage() . '\n' . $exception->getTraceAsString() . '\n';
        if (method_exists($exception, 'getResponse') && !empty($exception->getResponse())) {
            $debuginfo .= $exception->getResponse()->getBody()->getContents();
        }
        return request_response::create_from_error($exception->getCode(), $message, $debuginfo);
    }

    protected function get_headers(): array {
        return [
                'Authorization' => 'Bearer ' . $this->get_api_key(),
                'Content-Type' => 'application/json;charset=utf-8',
        ];
    }
}
