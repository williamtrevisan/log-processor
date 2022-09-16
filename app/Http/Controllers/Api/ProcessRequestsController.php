<?php

namespace App\Http\Controllers\Api;

use App\EventLoop\EventLoop;
use App\Exceptions\InvalidFileException;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\RequestRepositoryInterface;
use App\Transformers\RequestTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProcessRequestsController extends Controller
{
    const MAX_REQUEST_QUANTITY = 1000;
    const READ = 'r';

    public function __construct(
        private RequestRepositoryInterface $requestRepository,
        private EventLoop $eventLoop
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $this->validateRequest($request);

        $filePath = $request->file('requests')->path();

        $this->checkIfAnInvalidFileHasBeenSubmitted($filePath);
        $this->deleteRecords();
        $this->processFile($filePath);

        return response()->json(['message' => 'Processed file.'], Response::HTTP_OK);
    }

    private function validateRequest(Request $request): void
    {
        $request->validate(['requests' => 'required']);
    }

    private function checkIfAnInvalidFileHasBeenSubmitted(string $filePath): void
    {
        $file = fopen($filePath, self::READ);
        $request = fgets($file);
        fclose($file);

        if (! $this->existsConsumerIdKey($request)) {
            throw new InvalidFileException('Invalid file submitted.');
        }
    }

    private function existsConsumerIdKey(string $request): bool
    {
        return array_key_exists('started_at', RequestTransformer::toArray($request));
    }

    private function deleteRecords(): void
    {
        $this->requestRepository->deleteAll();
    }

    private function processFile(string $filePath): void
    {
        $file = fopen($filePath, self::READ);

        $requests = [];
        while ($request = fgets($file)) {
            if ($this->hasMaxQuantity($requests)) {
                $this->eventLoop->register($this->saveRequests($requests));
                $requests = [];
            }

            $requests[] = RequestTransformer::transform($request);
        }

        fclose($file);

        if (! $requests) {
            $this->eventLoop->execute();

            return;
        }

        $this->eventLoop->register($this->saveRequests($requests));
        $this->eventLoop->execute();
    }

    private function hasMaxQuantity(array $requests): bool
    {
        return count($requests) === self::MAX_REQUEST_QUANTITY;
    }

    private function saveRequests(array $requests): callable
    {
        return function () use ($requests) {
            $this->requestRepository->create($requests);
            $this->eventLoop->next();
        };
    }
}
