<?php

namespace App\Http\Controllers\Api;

use App\Enums\FileModes;
use App\Http\Controllers\Controller;
use App\Http\Resources\GenerateReportsResource;
use App\Repositories\Reports\RequestsByConsumerReportRepository;
use App\Repositories\Reports\RequestsByServiceReportRepository;
use App\Repositories\Reports\RequestsWithAverageLatencyByServiceReportRepository;
use App\Repositories\Eloquent\RequestRepositoryInterface;
use App\Exceptions\NoDataException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class GenerateReportsController extends Controller
{
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
        $reportRepository =
            new RequestsByConsumerReportRepository($this->requestRepository);

        $reportHeader = $reportRepository->header();
        $reportData = $reportRepository->data();

        return $this->createCsvFile(
            $reportRepository->filename(), array_merge($reportHeader, $reportData)
        );
    }

    private function generateRequestsByServiceReport(): string
    {
        $reportRepository =
            new RequestsByServiceReportRepository($this->requestRepository);

        $reportHeader = $reportRepository->header();
        $reportData = $reportRepository->data();

        return $this->createCsvFile(
            $reportRepository->filename(), array_merge($reportHeader, $reportData)
        );
    }

    private function generateRequestsWithAverageLatencyByServiceReport(): string
    {
        $reportRepository =
            new RequestsWithAverageLatencyByServiceReportRepository($this->requestRepository);

        $reportHeader = $reportRepository->header();
        $reportData = $reportRepository->data();

        return $this->createCsvFile(
            $reportRepository->filename(), array_merge($reportHeader, $reportData)
        );
    }

    private function createCsvFile(string $filename, array $content): string
    {
        $filepath = app()->storagePath('reports/') . $filename;

        $file = fopen($filepath, FileModes::Write->value);
        array_walk($content, fn($line) => fputcsv($file, $line, ';'));
        fclose($file);

        return substr($filepath, 9);
    }
}
