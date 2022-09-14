<?php

namespace App\Http\Controllers\Api;

use App\Enums\FileModes;
use App\Http\Controllers\Controller;
use App\Http\Resources\GenerateReportsResource;
use App\Repositories\Reports\ReportByConsumerRepository;
use App\Repositories\Reports\ReportByServiceRepository;
use App\Repositories\Reports\ReportWithAverageLatencyByServiceRepository;
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
                'There is no processed data. Make sure to process the data via the endpoint: /api/process-requests.'
            );
        }
    }

    private function hasRecords(int $recordCount): bool
    {
        return $recordCount > 0;
    }

    private function generateReports(): array
    {
        $reportByConsumerPath = $this->generateReportByConsumer();
        $reportByServicePath = $this->generateReportByService();
        $reportWithAverageLatencyByServicePath =
            $this->generateReportWithAverageLatencyByService();

        return [
            'requests-by-consumer' => ['filepath' => $reportByConsumerPath],
            'requests-by-service' => ['filepath' => $reportByServicePath],
            'requests-with-average-latency-by-service' => [
                'filepath' => $reportWithAverageLatencyByServicePath,
            ],
        ];
    }

    private function generateReportByConsumer(): string
    {
        $reportByConsumer = new ReportByConsumerRepository($this->requestRepository);

        $reportHeader = $reportByConsumer->header();
        $requestsByConsumer = $reportByConsumer->data();

        return $this->createCsvFile(
            $reportByConsumer->filename(), array_merge($reportHeader, $requestsByConsumer)
        );
    }

    private function generateReportByService(): string
    {
        $reportByService = new ReportByServiceRepository($this->requestRepository);

        $reportHeader = $reportByService->header();
        $requestsByService = $reportByService->data();

        return $this->createCsvFile(
            $reportByService->filename(), array_merge($reportHeader, $requestsByService)
        );
    }

    private function generateReportWithAverageLatencyByService(): string
    {
        $reportWithAverageLatencyByService =
            new ReportWithAverageLatencyByServiceRepository($this->requestRepository);

        $reportHeader = $reportWithAverageLatencyByService->header();
        $requestsWithAverageLatencyByService =
            $reportWithAverageLatencyByService->data();

        return $this->createCsvFile(
            $reportWithAverageLatencyByService->filename(),
            array_merge($reportHeader, $requestsWithAverageLatencyByService)
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
