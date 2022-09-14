<?php

namespace App\Transformers;

class RequestTransformer
{
    public static function transform(string $data): array
    {
        $data = json_decode($data);

        return [
            'consumer_id' => $data->authenticated_entity->consumer_id->uuid,
            'service_id' => $data->service->id,
            'service_name' => $data->service->name,
            'proxy_latency' => $data->latencies->proxy,
            'kong_latency' => $data->latencies->kong,
            'request_latency' => $data->latencies->request,
        ];
    }

    public static function toArray(string $data): array
    {
        return (array) json_decode($data);
    }
}
