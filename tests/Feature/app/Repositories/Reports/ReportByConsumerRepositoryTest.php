<?php

namespace Tests\Feature\app\Repositories\Reports;

use App\Models\Request;
use App\Repositories\Eloquent\RequestEloquentRepository;
use App\Repositories\Eloquent\RequestRepositoryInterface;
use App\Repositories\Reports\RequestsByConsumerReportRepository;

class ReportByConsumerRepositoryTest extends ReportTestCase
{
    private readonly RequestRepositoryInterface $requestRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestRepository = new RequestEloquentRepository(new Request());
    }

    protected function report(): RequestsByConsumerReportRepository
    {
        return new RequestsByConsumerReportRepository($this->requestRepository);
    }

    protected function filename(): string
    {
        return 'requests-by-consumer.csv';
    }

    protected function header(): array
    {
        return [
            ['consumer_id', 'quantity_requests'],
        ];
    }

    protected function data(): array
    {
        return $this->requestRepository->findByConsumer();
    }
}
