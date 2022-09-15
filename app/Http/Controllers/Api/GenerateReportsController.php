<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\NoDataException;
use App\Http\Controllers\Controller;
use App\Http\Resources\GenerateReportsResource;
use App\Repositories\Eloquent\RequestRepositoryInterface;
use App\Repositories\Reports\RequestsByConsumerReportRepository;
use App\Repositories\Reports\RequestsByServiceReportRepository;
use App\Repositories\Reports\RequestsWithAverageLatencyByServiceReportRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class GenerateReportsController extends Controller
{
    const WRITE = 'w';

    public function __construct(
        private readonly RequestRepositoryInterface $requestRepository
    ) {
    }

    public function handle(): JsonResponse
    {
        $this->checkIfThereAreRecordsInTheDatabase();

        $reportsPath = $this->generateReports();

        return (new GenerateReportsResource($reportsPath))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    private function checkIfThereAreRecordsInTheDatabase(): void
    {
        $recordCount = $this->requestRepository->count();

        if (! $this->hasRecords($recordCount)) {
            throw new NoDataException(
                'There is no processed data. Make sure to process the data via the endpoint: /api/process_requests.'
            );
        }
    }

    private function hasRecords(int $recordCount): bool
    {
        return $recordCount > 0;
    }

    private function generateReports(): array
    {
        $requestsByConsumerReportPath = $this->generateRequestsByConsumerReport();
        $requestsByServiceReportPath = $this->generateRequestsByServiceReport();
        $requestsWithAverageLatencyByServiceReportPath =
            $this->generateRequestsWithAverageLatencyByServiceReport();

        return [
            'requests-by-consumer' => ['filepath' => $requestsByConsumerReportPath],
            'requests-by-service' => ['filepath' => $requestsByServiceReportPath],
            'requests-with-average-latency-by-service' => [
                'filepath' => $requestsWithAverageLatencyByServiceReportPath,
            ],
        ];
    }

    private function generateRequestsByConsumerReport(): string
    {
        $requestsByConsumerReport =
            new RequestsByConsumerReportRepository($this->requestRepository);

        return $this->createCsvFile(
            $requestsByConsumerReport->filename(),
            array_merge(
                $requestsByConsumerReport->header(), $requestsByConsumerReport->data()
            ),
        );
    }

    private function generateRequestsByServiceReport(): string
    {
        $requestsByServiceReport =
            new RequestsByServiceReportRepository($this->requestRepository);

        return $this->createCsvFile(
            $requestsByServiceReport->filename(),
            array_merge(
                $requestsByServiceReport->header(), $requestsByServiceReport->data()
            ),
        );
    }

    private function generateRequestsWithAverageLatencyByServiceReport(): string
    {
        $requestsWithAverageLatencyByServiceReport =
            new RequestsWithAverageLatencyByServiceReportRepository($this->requestRepository);

        return $this->createCsvFile(
            $requestsWithAverageLatencyByServiceReport->filename(),
            array_merge(
                $requestsWithAverageLatencyByServiceReport->header(),
                $requestsWithAverageLatencyByServiceReport->data()
            ),
        );
    }

    private function createCsvFile(string $filename, array $content): string
    {
        $filepath = app()->storagePath('reports/').$filename;

        $file = fopen($filepath, self::WRITE);
        array_walk($content, fn ($line) => fputcsv($file, $line, ';'));
        fclose($file);

        return substr($filepath, 9);
    }
}
