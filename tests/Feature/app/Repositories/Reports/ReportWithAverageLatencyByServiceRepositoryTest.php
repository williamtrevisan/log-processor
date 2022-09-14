<?php

namespace Tests\Feature\app\Repositories\Reports;

use App\Models\Request;
use App\Repositories\Eloquent\RequestEloquentRepository;
use App\Repositories\Eloquent\RequestRepositoryInterface;
use App\Repositories\Reports\RequestsWithAverageLatencyByServiceReportRepository;

class ReportWithAverageLatencyByServiceRepositoryTest extends ReportTestCase
{
    private readonly RequestRepositoryInterface $requestRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestRepository = new RequestEloquentRepository(new Request());
    }

    protected function report(): RequestsWithAverageLatencyByServiceReportRepository
    {
        return new RequestsWithAverageLatencyByServiceReportRepository($this->requestRepository);
    }

    protected function filename(): string
    {
        return 'requests-with-average-latency-by-service.csv';
    }

    protected function header(): array
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

    protected function data(): array
    {
        return $this->requestRepository->findByService(true);
    }
}
