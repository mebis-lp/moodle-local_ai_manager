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

use core\http_client;
use Firebase\JWT\JWT;
use Psr\Http\Client\ClientExceptionInterface;

/**
 * Class responsible for handling authentication with Vertex AI using Google's OAuth mechanism.
 */
class aitool_option_vertexai_authhandler {

    /**
     * Constructor for the auth handler.
     */
    public function __construct(
            /** @var int The ID of the instance being used. Will be used as key for the cache handling. */
            private readonly int $instanceid,
            /** @var string The serviceaccountinfo stringified JSON */
            private readonly string $serviceaccountinfo
    ) {
    }

    /**
     * Retrieves a fresh access token from the Google oauth endpoint.
     *
     * @return array of the form ['access_token' => 'xxx', 'expires' => 1730805678] containing the access token and the time at
     *  which the token expires. If there has been an error, the array is of the form
     *  ['error' => 'more detailed info about the error']
     */
    public function retrieve_access_token(): array {
        $clock = \core\di::get(\core\clock::class);
        $serviceaccountinfo = json_decode($this->serviceaccountinfo);
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
     * Gets an access token for accessing Vertex AI endpoints.
     *
     * This will check if the cached access token still has not expired. If cache is empty or the token has expired
     * a new access token will be fetched by calling {@see self::retrieve_access_token} and the new token will be stored
     * in the cache.
     *
     * @return string the access token as string, empty if no
     */
    public function get_access_token(): string {
        $clock = \core\di::get(\core\clock::class);
        $authcache = \cache::make('local_ai_manager', 'googleauth');
        $cachedauthinfo = $authcache->get($this->instanceid);
        if (empty($cachedauthinfo) || json_decode($cachedauthinfo)->expires < $clock->time()) {
            $authinfo = $this->retrieve_access_token();
            if (!empty($authinfo['error'])) {
                throw new \moodle_exception('Error retrieving access token', '', '', '', $authinfo['error']);
            }
            $cachedauthinfo = json_encode($authinfo);
            $authcache->set($this->instanceid, $cachedauthinfo);
            $accesstoken = $authinfo['access_token'];
        } else {
            $accesstoken = json_decode($cachedauthinfo, true)['access_token'];
        }
        return $accesstoken;
    }

    /**
     * Refreshes the current access token.
     *
     * Clears the existing access token and retrieves a new one by invoking {@see self::get_access_token}.
     *
     * @return string the newly fetched access token as a string
     */
    public function refresh_access_token(): string {
        $this->clear_access_token();
        return $this->get_access_token();
    }

    /**
     * Clears the access token from the cache for the current instance.
     */
    public function clear_access_token(): void {
        $authcache = \cache::make('local_ai_manager', 'googleauth');
        $authcache->delete($this->instanceid);
    }

}
