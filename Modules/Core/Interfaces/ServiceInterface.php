<?php

namespace Modules\Core\Interfaces;

interface ServiceInterface
{
    public function findById(int $id);

    public function findOrFail(int $id);

    public function findAll(array $filters = []): array;

    public function findAllPaginated(int $perPage = 15, array $filters = []);

    public function create(array $data);

    public function update(int $id, array $data);

    public function delete(int $id): bool;
}
