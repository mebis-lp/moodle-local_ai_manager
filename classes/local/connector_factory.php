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

use local_ai_manager\base_connector;
use local_ai_manager\base_purpose;
use local_ai_manager\connector_instance;
use mod_unilabel\factory;

/**
 * Class for managing the configuration of tenants.
 *
 * @package    local_ai_manager
 * @copyright  2024, ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class connector_factory {

    private base_purpose $purpose;

    private connector_instance $connectorinstance;

    private base_connector $connector;

    public function __construct(private readonly config_manager $configmanager) {
    }

    public function get_connector_instance_by_id(int $id): connector_instance {
        global $DB;
        if (!empty($this->connectorinstance) && $this->connectorinstance->get_id() === $id) {
            return $this->connectorinstance;
        }
        $instancerecord = $DB->get_record('local_ai_manager_instance', ['id' => $id], '*', MUST_EXIST);
        $instanceclassname = '\\aitool_' . $instancerecord->connector . '\\instance';
        $this->connectorinstance = new $instanceclassname($id);
        return $this->connectorinstance;
    }

    public function get_connector_instance_by_purpose(string $purpose): ?connector_instance {
        $instanceid = $this->configmanager->get_config(base_purpose::get_purpose_tool_config_key($purpose));
        if (empty($instanceid)) {
            return null;
        }
        return $this->get_connector_instance_by_id($instanceid);
    }

    public function get_connector_by_instanceid(int $id): base_connector {
        $instance = $this->get_connector_instance_by_id($id);
        $connectorclassname = '\\aitool_' . $instance->get_connector() . '\\connector';
        $this->connector = new $connectorclassname($instance);
        return $this->connector;
    }

    public function get_new_instance(string $connectorname): connector_instance {
        $instanceclassname = '\\aitool_' . $connectorname . '\\instance';
        $this->connectorinstance = new $instanceclassname();
        $this->connectorinstance->set_connector($connectorname);
        return $this->connectorinstance;
    }

    public function get_connector_by_connectorname(string $connectorname): base_connector {
        $connectorclassname = '\\aitool_' . $connectorname . '\\connector';
        $instance = $this->get_new_instance($connectorname);
        $this->connector = new $connectorclassname($instance);
        return $this->connector;
    }

    public function get_connector_by_purpose(string $purpose): base_connector {
        $instance = $this->get_connector_instance_by_purpose($purpose);
        $connectorclassname = '\\aitool_' . $instance->get_connector() . '\\connector';
        $this->connector = new $connectorclassname($instance);
        return $this->connector;
    }

    public function get_connector_by_model(string $model): ?base_connector {
        foreach (base_connector::get_all_connectors() as $connectorname) {
            $connector = $this->get_connector_by_connectorname($connectorname);
            if (in_array($model, $connector->get_models())) {
                return $connector;
            }
        }
        return null;
    }

    public function instance_exists(int $id): bool {
        global $DB;
        return $DB->record_exists('local_ai_manager_instance', ['id' => $id]);
    }

    public function get_purpose_by_purpose_string(string $purpose): base_purpose {
        if (empty($purpose) || !in_array($purpose, \local_ai_manager\plugininfo\aipurpose::get_enabled_plugins())) {
            throw new \coding_exception('Purpose ' . $purpose . ' does not exist or is not enabled');
        }
        $purposeclassname = '\\aipurpose_' . $purpose . '\\purpose';
        $this->purpose = new $purposeclassname();
        return $this->purpose;
    }

    public static function get_connector_instances_for_purpose(string $purpose): array {
        $instances = [];
        foreach (connector_instance::get_all_instances() as $instance) {
            if (in_array($purpose, $instance->supported_purposes())) {
                $instances[$instance->get_id()] = $instance;
            }
        }
        return $instances;
    }

}
