<?php

namespace App\Repositories\Eloquent;

use App\Models\Request;

class RequestEloquentRepository implements RequestRepositoryInterface
{
    public function __construct(
        private readonly Request $request
    ) {
    }

    public function create(array $request): void
    {
        $this->request->insert($request);
    }

    public function findByConsumerId(string $consumerId): array
    {
        return $this->request
            ->select([
                'consumer_id',
                'client_ip',
                'started_at',
                'method',
                'url',
                'response_status',
                'size',
                'proxy_latency',
                'kong_latency',
                'request_latency',
            ])
            ->where('consumer_id', '=', $consumerId)
            ->get()
            ->toArray();
    }

    public function findByServiceId(string $serviceId): array
    {
        return $this->request
            ->select([
                'service_id',
                'service_name',
                'client_ip',
                'started_at',
                'method',
                'url',
                'response_status',
                'size',
                'proxy_latency',
                'kong_latency',
                'request_latency',
            ])
            ->where('service_id', '=', $serviceId)
            ->get()
            ->toArray();
    }

    public function findByServiceIdWithAverageLatency(string $serviceId): array
    {
        return $this->request
            ->select(['service_id', 'service_name'])
            ->selectRaw('avg(proxy_latency) AS average_proxy_latency')
            ->selectRaw('avg(kong_latency) AS average_kong_latency')
            ->selectRaw('avg(request_latency) AS average_request_latency')
            ->where('service_id', '=', $serviceId)
            ->groupBy(['service_id', 'service_name'])
            ->first()
            ->toArray();
    }
}
