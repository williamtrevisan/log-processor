<?php

namespace Tests\Feature\app\Http\Controllers\Api;

use App\Models\Request;
use Illuminate\Http\Response;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class GenerateReportsApiTest extends TestCase
{
    private string $uri = '/api/generate_reports';

    /**
     * @test
     */
    public function should_be_return_no_data_exception_if_dont_has_record_in_database(): void
    {
        $response = $this->getJson($this->uri);

        $response->assertJsonStructure(['message']);
        $this->assertEquals(
            'There is no processed data. Make sure to process the data via the endpoint: /api/process_requests.',
            $response['message']
        );
    }

    /**
     * @test
     */
    public function should_be_able_to_generate_reports(): void
    {
        Request::factory(10)->create(['consumer_id' => Uuid::uuid4()->toString()]);
        Request::factory(10)->create([
            'service_id' => Uuid::uuid4()->toString(),
            'service_name' => 'my service',
        ]);

        $response = $this->getJson($this->uri);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                'requests-by-consumer' => ['filepath'],
                'requests-by-service' => ['filepath'],
                'requests-with-average-latency-by-service' => ['filepath'],
            ],
        ]);
        $this->assertTrue(
            file_exists($response['data']['requests-by-consumer']['filepath'])
        );
        $this->assertTrue(
            file_exists($response['data']['requests-by-service']['filepath'])
        );
        $this->assertTrue(
            file_exists($response['data']['requests-with-average-latency-by-service']['filepath'])
        );
    }
}
