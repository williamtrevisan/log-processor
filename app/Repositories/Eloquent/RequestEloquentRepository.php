<?php

namespace App\Repositories\Eloquent;

use App\Models\Request;
use Illuminate\Support\Facades\DB;

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

    public function findByConsumer(): array
    {
        return $this->request
            ->select('consumer_id')
            ->selectRaw('count(*) AS quantity_requests')
            ->groupBy('consumer_id')
            ->orderBy('quantity_requests')
            ->get()
            ->toArray();
    }

    public function findByService(bool $showAverage = false): array
    {
        $query = $this->request
            ->select([
                'service_id',
                'service_name',
            ])
            ->selectRaw('count(*) AS quantity_requests');

        if ($showAverage) {
            $query->selectRaw('avg(proxy_latency) AS average_proxy_latency')
                ->selectRaw('avg(kong_latency) AS average_kong_latency')
                ->selectRaw('avg(request_latency) AS average_request_latency');
        }

        return $query->groupBy([
                'service_id',
                'service_name',
            ])
            ->orderBy('quantity_requests')
            ->get()
            ->toArray();
    }

    public function count(): int
    {
        $requests = $this->request->all();
        return count($requests);
    }

    public function deleteAll(): bool
    {
        return $this->request->whereNotNull('consumer_id')->delete();
    }
}
