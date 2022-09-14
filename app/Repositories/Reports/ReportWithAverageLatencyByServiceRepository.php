<?php

namespace App\Repositories\Reports;

use App\Repositories\Eloquent\RequestRepositoryInterface;

class ReportWithAverageLatencyByServiceRepository implements ReportRepositoryInterface
{
    public function __construct(
        private readonly RequestRepositoryInterface $requestRepository
    ) {
    }

    public function filename(): string
    {
        return 'requests-with-average-latency-by-service.csv';
    }

    public function header(): array
    {
        return [
            [
                'service_id',
                'service_name',
                'quantity_requests',
                'average_proxy_latency',
                'average_kong_latency',
                'average_request_latency',
            ],
        ];
    }

    public function data(): array
    {
        return $this->requestRepository->findByService(true);
    }
}
