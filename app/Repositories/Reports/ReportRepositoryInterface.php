<?php

namespace App\Repositories\Reports;

interface ReportRepositoryInterface
{
    public function filename(): string;

    public function header(): array;

    public function data(): array;
}
