<?php

namespace App\Repositories\Eloquent;

interface RequestRepositoryInterface
{
    public function create(array $request): void;
    public function findByConsumerId(string $consumerId): array;
    public function findByServiceId(string $serviceId): array;
    public function findByServiceIdWithAverageLatency(string $serviceId): array;
}
