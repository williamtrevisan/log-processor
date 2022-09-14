<?php

namespace Tests\Feature\app\Repositories\Reports;

use App\Models\Request;
use App\Repositories\Eloquent\RequestEloquentRepository;
use App\Repositories\Eloquent\RequestRepositoryInterface;
use App\Repositories\Reports\ReportByServiceRepository;

class ReportByServiceRepositoryTest extends ReportTestCase
{
    private readonly RequestRepositoryInterface $requestRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestRepository = new RequestEloquentRepository(new Request());
    }

    protected function report(): ReportByServiceRepository
    {
        return new ReportByServiceRepository($this->requestRepository);
    }

    protected function filename(): string
    {
        return 'report-by-service.csv';
    }

    protected function header(): array
    {
        return [
            ['service_id', 'service_name', 'quantity_requests'],
        ];
    }

    protected function data(): array
    {
        return $this->requestRepository->findByService();
    }
}
