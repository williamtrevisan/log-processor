<?php

namespace App\Transformers;

use Carbon\Carbon;
use stdClass;

class RequestTransformer
{
    public static function transform(string $data): array
    {
        $data = json_decode($data);

        return [
            'consumer_id' => $data->authenticated_entity->consumer_id->uuid,
            'service_id' => $data->service->id,
            'service_name' => $data->service->name,
            'method' => $data->request->method,
            'uri' => $data->request->uri,
            'url' => $data->request->url,
            'size' => $data->request->size,
            'response_status' => $data->response->status,
            'proxy_latency' => $data->latencies->proxy,
            'kong_latency' => $data->latencies->kong,
            'request_latency' => $data->latencies->request,
            'client_ip' => $data->client_ip,
            'started_at' => Carbon::createFromTimestamp($data->started_at)->format('Y-m-d'),
        ];
    }

    public static function toArray(string $data): array
    {
        return (array) json_decode($data);
    }
}
