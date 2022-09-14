<?php

namespace App\Repositories\Reports;

use App\Repositories\Eloquent\RequestRepositoryInterface;

class ReportByServiceRepository implements ReportRepositoryInterface
{
    public function __construct(
        private readonly RequestRepositoryInterface $requestRepository
    ) {
    }

    public function filename(): string
    {
        return 'report-by-service.csv';
    }

    public function header(): array
    {
        return [
            ['service_id', 'service_name', 'quantity_requests'],
        ];
    }

    public function data(): array
    {
        return $this->requestRepository->findByService();
    }
}