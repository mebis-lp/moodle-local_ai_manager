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

namespace local_ai_manager\hook;

use local_ai_manager\local\tenant;

/**
 * Hook for customizing the rights config table.
 *
 * This hook will be dispatched when it's about to show the rights config table.
 *
 * @package    local_ai_manager
 * @copyright  2024 ISB Bayern
 * @author     Philipp Memmel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\core\attribute\label('Allows plugins to customize the user rights management table in the tenant config.')]
#[\core\attribute\tags('local_ai_manager')]
class usertable_extend {


    /**
     * Constructor for the hook.
     */
    public function __construct(
            private tenant $tenant,
            private array $columns,
            private array $headers,
            private array $filterids,
        /** @var string $fields */
            private string $fields,
            private string $from,
            private string $where,
            private array $params
    ) {
    }

    public function get_tenant(): tenant {
        return $this->tenant;
    }

    public function get_columns(): array {
        return $this->columns;
    }

    public function get_headers(): array {
        return $this->headers;
    }

    public function get_filterids(): array {
        return $this->filterids;
    }

    public function get_fields(): string {
        return $this->fields;
    }

    public function get_from(): string {
        return $this->from;
    }

    public function get_where(): string {
        return $this->where;
    }

    public function get_params(): array {
        return $this->params;
    }

    public function set_columns(array $columns): void {
        $this->columns = $columns;
    }

    public function set_headers(array $headers): void {
        $this->headers = $headers;
    }

    public function set_filterids(array $filterids): void {
        $this->filterids = $filterids;
    }

    public function set_fields(string $fields): void {
        $this->fields = $fields;
    }

    public function set_from(string $from): void {
        $this->from = $from;
    }

    public function set_where(string $where): void {
        $this->where = $where;
    }

    public function set_params(array $params): void {
        $this->params = $params;
    }



}
