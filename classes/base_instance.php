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

use local_ai_manager\local\connector_factory;
use local_ai_manager\local\tenant;
use stdClass;

/**
 * Instance class for a connector instance.
 *
 * @package    local_ai_manager
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class base_instance {

    public const PRECONFIGURED_MODEL = 'preconfigured';

    protected ?stdClass $record = null;

    protected int $id = 0;

    protected ?string $name = null;

    protected ?string $tenant = null;

    protected ?string $connector = null;

    protected ?string $endpoint = null;

    protected ?string $apikey = null;

    protected ?string $model = null;

    protected ?string $infolink = null;

    /** @var ?string First customfield attribute. */
    protected ?string $customfield1 = null;

    /** @var string Second customfield attribute. */
    protected ?string $customfield2 = null;

    /** @var ?string Third customfield attribute. */
    private ?string $customfield3 = null;

    /** @var ?string Fourth customfield attribute. */
    protected ?string $customfield4 = null;

    /** @var ?string Fifth customfield attribute. */
    protected ?string $customfield5 = null;

    public function __construct(int $id = 0) {
        $this->id = $id;
        $this->load();
    }

    public final function load(): void {
        global $DB;
        $record = $DB->get_record('local_ai_manager_instance', ['id' => $this->id]);
        if (!$record) {
            return;
        }
        $this->record = $record;
        [
                $this->id,
                $this->name,
                $this->tenant,
                $this->connector,
                $this->endpoint,
                $this->apikey,
                $this->model,
                $this->infolink,
                $this->customfield1,
                $this->customfield2,
                $this->customfield3,
                $this->customfield4,
                $this->customfield5,
        ] = [
                $record->id,
                $record->name,
                $record->tenant,
                $record->connector,
                $record->endpoint,
                $record->apikey,
                $record->model,
                $record->infolink,
                $record->customfield1,
                $record->customfield2,
                $record->customfield3,
                $record->customfield4,
                $record->customfield5,
        ];
    }

    public final function store(): void {
        global $DB;
        $record = new stdClass();
        $record->name = $this->name;
        $record->tenant = $this->tenant;
        $record->connector = $this->connector;
        $record->endpoint = $this->endpoint;
        $record->apikey = $this->apikey;
        $record->model = $this->model;
        $record->infolink = $this->infolink;
        $record->customfield1 = $this->customfield1;
        $record->customfield2 = $this->customfield2;
        $record->customfield3 = $this->customfield3;
        $record->customfield4 = $this->customfield4;
        $record->customfield5 = $this->customfield5;
        if (is_null($this->record)) {
            $record->timecreated = time();
            $record->id = $DB->insert_record('local_ai_manager_instance', $record);
            $this->id = $record->id;
        } else {
            $record->id = $this->id;
            $record->timemodified = time();
            $DB->update_record('local_ai_manager_instance', $record);
        }
        $this->record = $record;
    }

    /**
     * @param bool $allinstances true if all instances should be returned, by default only the instances of the current tenant are
     *  returned
     * @return array
     * @throws \dml_exception
     */
    public static function get_all_instances(bool $allinstances = false): array {
        global $DB;

        $params = [];
        if (!$allinstances) {
            $params['tenant'] = \core\di::get(tenant::class)->get_tenantidentifier();
        }
        $records = $DB->get_records('local_ai_manager_instance', $params, '', 'id');
        $instances = [];
        foreach ($records as $record) {
            $instances[] = new self($record->id);
        }
        return $instances;
    }

    public function get_id(): int {
        return $this->id;
    }

    public function get_name(): string {
        return $this->name;
    }

    public function set_name(string $name): void {
        $this->name = $name;
    }

    public function get_tenant(): string {
        return $this->tenant;
    }

    public function set_tenant(string $tenant): void {
        $this->tenant = $tenant;
    }

    public function get_connector(): ?string {
        return $this->connector;
    }

    public function set_connector(string $connector): void {
        $this->connector = $connector;
    }

    public function get_endpoint(): string {
        return $this->endpoint;
    }

    public function set_endpoint(string $endpoint): void {
        $this->endpoint = $endpoint;
    }

    public function get_apikey(): ?string {
        return $this->apikey;
    }

    public function set_apikey(?string $apikey): void {
        $this->apikey = $apikey;
    }

    public function get_model(): string {
        return $this->model;
    }

    public function set_model(string $model): void {
        $this->model = $model;
    }

    public function get_infolink(): ?string {
        return $this->infolink;
    }

    public function set_infolink(?string $infolink): void {
        $this->infolink = $infolink;
    }

    public function get_customfield1(): ?string {
        return $this->customfield1;
    }

    public function set_customfield1(?string $customfield1): void {
        $this->customfield1 = $customfield1;
    }

    public function get_customfield2(): ?string {
        return $this->customfield2;
    }

    public function set_customfield2(?string $customfield2): void {
        $this->customfield2 = $customfield2;
    }

    public function get_customfield3(): ?string {
        return $this->customfield3;
    }

    public function set_customfield3(?string $customfield3): void {
        $this->customfield3 = $customfield3;
    }

    public function get_customfield4(): ?string {
        return $this->customfield4;
    }

    public function set_customfield4(?string $customfield4): void {
        $this->customfield4 = $customfield4;
    }


    public function get_customfield5(): ?string {
        return $this->customfield5;
    }
    public function set_customfield5(?string $customfield5): void {
        $this->customfield5 = $customfield5;
    }

    public function record_exists(): bool {
        if (!is_null($this->record)) {
            return true;
        } else {
            $this->load();
            return is_null($this->record);
        }
    }

    public final function get_formdata(): stdClass {
        $this->load();
        $data = new stdClass();
        if (is_null($this->record)) {
            return $data;
        }
        $data->name = $this->get_name();
        $data->connector = $this->get_connector();
        $data->endpoint = $this->get_endpoint();
        $data->apikey = $this->get_apikey();
        $data->model = $this->get_model();
        $data->infolink = $this->get_infolink();
        foreach ($this->get_extended_formdata() as $key => $value) {
            $data->{$key} = $value;
        }
        return $data;
    }

    protected function get_extended_formdata(): stdClass {
        return new stdClass();
    }

    public final function edit_form_definition(\MoodleQuickForm $mform, array $customdata): void {
        $textelementparams = ['style' => 'width: 100%'];
        $mform->addElement('text', 'name', get_string('instancename', 'local_ai_manager'), $textelementparams);
        $mform->setType('name', PARAM_TEXT);
        $mform->addElement('text', 'tenant', get_string('institution'), $textelementparams);
        $mform->setType('tenant', PARAM_ALPHANUM);
        if (empty($this->_customdata['id'])) {
            $mform->setDefault('tenant', $customdata['tenant']);
        }
        if (!is_siteadmin()) {
            $mform->freeze('tenant');
        }

        $connector = $customdata['connector'];
        $mform->addElement('text', 'connector', get_string('aitool', 'local_ai_manager'), $textelementparams);
        $mform->setType('connector', PARAM_TEXT);
        // That we have a valid connector here is being ensured by edit_instance.php.
        $mform->setDefault('connector', $connector);
        $mform->freeze('connector');


        $mform->addElement('text', 'endpoint', get_string('endpoint', 'local_ai_manager'), $textelementparams);
        $mform->setType('endpoint', PARAM_URL);

        $mform->addElement('passwordunmask', 'apikey', get_string('apikey', 'local_ai_manager'), $textelementparams);
        $mform->setType('apikey', PARAM_TEXT);

        $classname = '\\aitool_' . $connector . '\\connector';
        $connectorobject = \core\di::get($classname);
        $availablemodels = [];
        foreach ($connectorobject->get_models() as $modelname) {
            // TODO maybe add lang strings for models, so we have
            //  $availablemodels[$modelname] = get_string($modelname); or sth similar
            $availablemodels[$modelname] = $modelname;
        }
        $mform->addElement('select', 'model', get_string('model', 'local_ai_manager'), $availablemodels, $textelementparams);

        $mform->addElement('text', 'infolink', get_string('infolink',  'local_ai_manager'), $textelementparams);
        $mform->setType('infolink', PARAM_URL);

        $this->extend_form_definition($mform);
    }

    public final function store_formdata($data): void {
        $this->set_name(trim($data->name));
        $this->set_endpoint(trim($data->endpoint));
        $this->set_apikey(trim($data->apikey));
        $this->set_connector($data->connector);
        $this->set_tenant($data->tenant);
        if (empty($data->model)) {
            $data->model = self::PRECONFIGURED_MODEL;
        }
        $this->set_model($data->model);
        $this->set_infolink(trim($data->infolink));
        $this->extend_store_formdata($data);
        $this->store();
    }

    protected function extend_store_formdata(stdClass $data): void {
    }

    public final function validation(array $data, array $files): array {
        $errors = [];
        if (empty($data['name'])) {
            $errors['name'] = get_string('formvalidation_editinstance_name', 'local_ai_manager');
        }
        if (str_starts_with($data['endpoint'], 'http://') && !str_starts_with($data['endpoint'], 'https://')) {
            $errors['endpoint'] = get_string('formvalidation_editinstance_endpointnossl', 'local_ai_manager');
        }
        return $errors + $this->extend_validation($data, $files);
    }

    protected function extend_validation(array $data, array $files): array {
        return [];
    }

    public function delete(): void {
        global $DB;
        if (empty($this->id)) {
            $this->load();
            if (empty($this->id)) {
                throw new \moodle_exception('Instance with id ' . $this->id . ' does not exist');
            }
        }
        $DB->delete_records('local_ai_manager_instance', ['id' => $this->id]);
    }

    public final function supported_purposes(): array {
        if (empty($this->get_model())) {
            return [];
        }
        $connector = \core\di::get(connector_factory::class)->get_connector_by_connectorname($this->connector);
        if ($this->get_model() === self::PRECONFIGURED_MODEL) {
            // In case we have a preconfigured model (for example via Microsoft Azure) we have no information what the preconfigured
            // model is capable of, so we just allow every purpose and have to deal with the fact that there might be errors when
            // trying to use the preconfigured model for a purpose which it isn't capable of.
            return array_keys($connector->get_models_by_purpose());
        }
        $purposesofcurrentmodel = [];
        foreach ($connector->get_models_by_purpose() as $purpose => $models) {
            if (in_array($this->get_model(), $models)) {
                $purposesofcurrentmodel[] = $purpose;
            }
        }
        return $purposesofcurrentmodel;
    }
}
