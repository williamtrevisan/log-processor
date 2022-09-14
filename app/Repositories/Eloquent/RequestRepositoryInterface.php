<?php

namespace App\Repositories\Eloquent;

interface RequestRepositoryInterface
{
    public function create(array $request): void;

    public function findByConsumer(): array;

    public function findByService(bool $showAverage = false): array;

    public function count(): int;

    public function deleteAll(): bool;
}
