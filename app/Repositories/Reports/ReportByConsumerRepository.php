<?php

namespace App\Repositories\Reports;

use App\Repositories\Eloquent\RequestRepositoryInterface;

class ReportByConsumerRepository implements ReportRepositoryInterface
{
    public function __construct(
        private readonly RequestRepositoryInterface $requestRepository
    ) {
    }

    public function filename(): string
    {
        return 'report-by-consumer.csv';
    }

    public function header(): array
    {
        return [
            ['consumer_id', 'quantity_requests'],
        ];
    }

    public function data(): array
    {
        return $this->requestRepository->findByConsumer();
    }
}
