<?php

namespace App\Http\Controllers\Api;

use App\EventLoop;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\RequestRepositoryInterface;
use App\Transformers\RequestTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProcessRequestsController extends Controller
{
    const MAX_REQUEST_QUANTITY = 1000;

    public function __construct(
        private RequestRepositoryInterface $requestRepository,
        private EventLoop $eventLoop
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required']);

        $filePath = $request->file('file')->path();

        $isCorrectFile = $this->checkIsCorrectFile($filePath);
        if (! $isCorrectFile) {
            return response()->json([
                'message' => 'Invalid file received.',
                'errors' => [
                    'file' => ['Invalid file received.']
                ],
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->processFile($filePath);

        return response()->json(['message' => 'Processed file.'], Response::HTTP_OK);
    }

    private function checkIsCorrectFile(string $filePath): bool
    {
        $file = fopen($filePath, 'r');

        $request = fgets($file);

        return key_exists('started_at', RequestTransformer::toArray($request));
    }

    private function processFile(string $filePath): void
    {
        $file = fopen($filePath, 'r');

        $requests = [];
        while ($request = fgets($file)) {
            if ($this->hasMaxQuantity($requests)) {
                $this->eventLoop->register($this->saveRequests($requests));
                $requests = [];
            }

            $requests[] = RequestTransformer::transform($request);
        }

        if (! $requests) {
            $this->eventLoop->execute();
            return;
        }

        $this->eventLoop->register($this->saveRequests($requests));
        $this->eventLoop->execute();
    }

    private function saveRequests(array $requests): callable
    {
        return function () use ($requests) {
            $this->requestRepository->create($requests);
            $this->eventLoop->next();
        };
    }

    private function hasMaxQuantity(array $requests): bool
    {
        return count($requests) === self::MAX_REQUEST_QUANTITY;
    }
}
