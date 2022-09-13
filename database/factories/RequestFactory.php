<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Request>
 */
class RequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'consumer_id' => Str::uuid()->toString(),
            'service_id' => Str::uuid()->toString(),
            'service_name' => $this->faker->name,
            'method' => 'GET',
            'uri' => '/',
            'url' => $this->faker->url(),
            'size' => rand(1, 999),
            'response_status' => Response::HTTP_OK,
            'proxy_latency' => rand(1, 9999),
            'kong_latency' => rand(1, 99),
            'request_latency' => rand(1, 9999),
            'client_ip' => $this->faker->ipv4(),
            'started_at' => $this->faker->date(),
        ];
    }
}
