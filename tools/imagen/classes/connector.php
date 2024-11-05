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

namespace aitool_imagen;

use core\http_client;
use Firebase\JWT\JWT;
use local_ai_manager\base_connector;
use local_ai_manager\local\prompt_response;
use local_ai_manager\local\request_response;
use local_ai_manager\local\unit;
use local_ai_manager\local\usage;
use local_ai_manager\manager;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Connector for Imagen.
 *
 * @package    aitool_imagen
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class connector extends base_connector {

    /** @var string The access token to use for authentication against the Google imagen API endpoint. */
    private string $accesstoken = '';

    #[\Override]
    public function get_models_by_purpose(): array {
        return [
                'imggen' => ['imagegeneration@006', 'imagen-3.0-generate-001'],
        ];
    }

    #[\Override]
    public function get_prompt_data(string $prompttext, array $requestoptions): array {
        $promptdata = [
                'instances' => [
                        [
                                'prompt' => $prompttext,
                        ],
                ],
                'parameters' => [
                        'sampleCount' => 1,
                        'safetySetting' => 'block_few',
                        'language' => 'en',
                        'aspectRatio' => $requestoptions['sizes'][0],
                ],
        ];

        return $promptdata;
    }

    #[\Override]
    protected function get_headers(): array {
        $headers = parent::get_headers();
        $headers['Authorization'] = 'Bearer ' . $this->accesstoken;
        return $headers;
    }

    #[\Override]
    public function get_unit(): unit {
        return unit::COUNT;
    }

    #[\Override]
    public function make_request(array $data): request_response {
        // Currently, imagen does not support many languages. So we first translate the prompt into English and hardcode the
        // language to "English" later on in the request options.
        $translatemanager = new manager('translate');
        $translaterequestresult = $translatemanager->perform_request(
                'Translate the following words into English, only return the translated text: '
                . $data['instances'][0]['prompt']);
        if ($translaterequestresult->get_code() !== 200) {
            return request_response::create_from_error($translaterequestresult->get_code(),
                    get_string('err_translationfailed', 'aitool_imagen'), $translaterequestresult->get_debuginfo());
        }
        $translatedprompt = $translaterequestresult->get_content();

        // Subsitute the current prompt by the translated one.
        $data['instances'][0]['prompt'] = $translatedprompt;

        try {
            // Composing the "Authorization" header is not that easy as just looking up a Bearer token in the database.
            // So we here explicitly retrieve the access token from cache or the Google OAuth API and do some proper error handling.
            // After we stored it in $this->accesstoken it can be properly set into the header by the self::get_headers method.
            $this->accesstoken = $this->get_access_token();
        } catch (\moodle_exception $exception) {
            return request_response::create_from_error(0, $exception->getMessage(), $exception->getTraceAsString());
        }
        // We keep track of the time the cached access token expires. However, due latency, different clocks
        // on different servers etc. we could end up sending a request with an actually expired access token.
        // So we clear the cached access token and re-submit the request ONE TIME if we receive a 401 response code
        // with an ACCESS_TOKEN_EXPIRED error code.

        $requestresponse = parent::make_request($data);
        if ($requestresponse->get_code() === 401) {
            // We need to reset the stream, so we can again read it.
            $requestresponse->get_response()->rewind();
            $content = json_decode($requestresponse->get_response()->getContents(), true);
            if (!empty(array_filter($content['error']['details'], fn($details) => $details['reason'] === 'ACCESS_TOKEN_EXPIRED'))) {
                $authcache = \cache::make('aitool_imagen', 'auth');
                $authcache->delete($this->instance->get_id());
                $requestresponse = parent::make_request($data);
            }
        }
        return $requestresponse;
    }

    #[\Override]
    public function execute_prompt_completion(StreamInterface $result, array $options = []): prompt_response {
        global $USER;
        $content = json_decode($result->getContents(), true);
        if (empty($content['predictions']) || !array_key_exists('bytesBase64Encoded', $content['predictions'][0])) {
            return prompt_response::create_from_error(400,
                    get_string('err_predictionmissing', 'aitool_imagen'), '');
        }
        $fs = get_file_storage();
        $fileinfo = [
                'contextid' => \context_user::instance($USER->id)->id,
                'component' => 'user',
                'filearea' => 'draft',
                'itemid' => $options['itemid'],
                'filepath' => '/',
                'filename' => $options['filename'],
        ];
        $file = $fs->create_file_from_string($fileinfo, base64_decode($content['predictions'][0]['bytesBase64Encoded']));

        $filepath = \moodle_url::make_draftfile_url(
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename()
        )->out();

        return prompt_response::create_from_result($this->instance->get_model(), new usage(1.0), $filepath);
    }

    #[\Override]
    public function get_available_options(): array {
        $options['sizes'] = [
                ['key' => '1:1', 'displayname' => '1:1 (1536 x 1536)'],
                ['key' => '9:16', 'displayname' => '9:16 (1152 x 2016)'],
                ['key' => '16:9', 'displayname' => '16:9 (2016 x 1134)'],
                ['key' => '3:4', 'displayname' => '3:4 (1344 x 1792)'],
                ['key' => '4:3', 'displayname' => '4:3 (1792 x 1344)'],
        ];
        return $options;
    }

    /**
     * Retrieves a fresh access token from the Google oauth endpoint.
     *
     * @return array of the form ['access_token' => 'xxx', 'expires' => 1730805678] containing the access token and the time at
     *  which the token expires. If there has been an error, the array is of the form
     *  ['error' => 'more detailed info about the error']
     * @throws \dml_exception
     */
    public function retrieve_access_token(): array {
        $clock = \core\di::get(\core\clock::class);
        $serviceaccountinfo = json_decode($this->instance->get_customfield1());
        $kid = $serviceaccountinfo->private_key_id;
        $privatekey = $serviceaccountinfo->private_key;
        $clientemail = $serviceaccountinfo->client_email;
        $jwtpayload = [
                'iss' => $clientemail,
                'sub' => $clientemail,
                'scope' => 'https://www.googleapis.com/auth/cloud-platform',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $clock->time(),
                'exp' => $clock->time() + HOURSECS,
        ];
        $jwt = JWT::encode($jwtpayload, $privatekey, 'RS256', null, ['kid' => $kid]);

        $client = new http_client([
                'timeout' => get_config('local_ai_manager', 'requesttimeout'),
        ]);
        $options['query'] = [
                'assertion' => $jwt,
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        ];

        try {
            $response = $client->post('https://oauth2.googleapis.com/token', $options);
        } catch (ClientExceptionInterface $exception) {
            return ['error' => $exception->getMessage()];
        }
        if ($response->getStatusCode() === 200) {
            $content = $response->getBody()->getContents();
            if (empty($content)) {
                return ['error' => 'Empty response'];
            }
            $content = json_decode($content, true);
            if (empty($content['access_token'])) {
                return ['error' => 'Response does not contain "access_token" key'];
            }
            return [
                    'access_token' => $content['access_token'],
                // We set the expiry time of the access token and reduce it by 10 seconds to avoid some errors caused
                // by different clocks on different servers, latency etc.
                    'expires' => $clock->time() + intval($content['expires_in']) - 10,
            ];
        } else {
            return ['error' => 'Response status code is not OK 200, but ' . $response->getStatusCode() . ': ' .
                    $response->getBody()->getContents()];
        }
    }

    /**
     * Gets an access token for accessing the imagen API.
     *
     * This will check if the cached access token still has not expired. If cache is empty or the token has expired
     * a new access token will be fetched by calling {@see self::retrieve_access_token} and the new token will be stored
     * in the cache.
     *
     * @return string the access token as string, empty if no
     */
    public function get_access_token(): string {
        $clock = \core\di::get(\core\clock::class);
        $authcache = \cache::make('aitool_imagen', 'auth');
        $cachedauthinfo = $authcache->get($this->instance->get_id());
        if (empty($cachedauthinfo) || json_decode($cachedauthinfo)->expires < $clock->time()) {
            $authinfo = $this->retrieve_access_token();
            if (!empty($authinfo['error'])) {
                throw new \moodle_exception('Error retrieving access token', '', '', '', $authinfo['error']);
            }
            $cachedauthinfo = json_encode($authinfo);
            $authcache->set($this->instance->get_id(), $cachedauthinfo);
            $accesstoken = $authinfo['access_token'];
        } else {
            $accesstoken = json_decode($cachedauthinfo, true)['access_token'];
        }
        return $accesstoken;
    }

    #[\Override]
    protected function get_custom_error_message(int $code, ?ClientExceptionInterface $exception = null): string {
        $message = '';
        switch ($code) {
            case 400:
                if (method_exists($exception, 'getResponse') && !empty($exception->getResponse())) {
                    $responsebody = json_decode($exception->getResponse()->getBody()->getContents());
                    if (property_exists($responsebody, 'error') && property_exists($responsebody->error, 'status')
                            && $responsebody->error->status === 'INVALID_ARGUMENT') {
                        $message = get_string('err_contentpolicyviolation', 'aitool_imagen');
                    }
                }
                break;
        }
        return $message;
    }
}
