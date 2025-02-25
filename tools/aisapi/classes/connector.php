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

namespace aitool_aisapi;

use core\http_client;
use local_ai_manager\local\prompt_response;
use local_ai_manager\local\request_response;
use local_ai_manager\local\unit;
use local_ai_manager\local\usage;
use local_ai_manager\request_options;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Connector for the AIS API.
 *
 * @package    aitool_aisapi
 * @copyright  2025 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class connector extends \aitool_chatgpt\connector {

    #[\Override]
    public function get_models_by_purpose(): array {
        $models = [];
        $visionmodels = [];
        $availablemodelssetting = get_config('aitool_aisapi', 'availablemodels');
        foreach (explode("\n", $availablemodelssetting) as $model) {
            $model = trim($model);
            if (str_ends_with($model, '#VISION')) {
                $model = trim(preg_replace('/#VISION$/', '', $model));
                $visionmodels[] = $model;
            }
            $models[] = $model;
        }

        asort($models);
        asort($visionmodels);

        return [
                'chat' => $models,
                'feedback' => $models,
                'singleprompt' => $models,
                'translate' => $models,
                'itt' => $visionmodels,
        ];
    }

    #[\Override]
    protected function get_endpoint_url(): string {
        $baseurl = get_config('aitool_aisapi', 'baseurl');
        if (!str_ends_with($baseurl, '/')) {
            $baseurl .= '/';
        }
        return $baseurl . 'v1/chat/completions';
    }

    #[\Override]
    public function get_unit(): unit {
        return unit::TOKEN;
    }

    #[\Override]
    protected function get_api_key(): string {
        $globalapikey = get_config('aitool_aisapi', 'globalapikey');
        return !empty($globalapikey) ? $globalapikey : $this->instance->get_apikey();
    }

    #[\Override]
    public function has_customvalue1(): bool {
        return true;
    }
}
