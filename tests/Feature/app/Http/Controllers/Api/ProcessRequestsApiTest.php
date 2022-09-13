<?php

namespace Tests\Feature\app\Http\Controllers\Api;

use Illuminate\Http\Response;
use Illuminate\Http\Testing\File;
use Tests\TestCase;

class ProcessRequestsApiTest extends TestCase
{
    private string $uri = '/api/process_requests';

    /**
     * @test
     */
    public function should_return_error_if_dont_receive_any_file(): void
    {
        $response = $this->postJson($this->uri, []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonStructure(['message', 'errors' => ['file']]);
    }

    /**
     * @test
     */
    public function should_return_error_if_receive_invalid_file(): void
    {
        $file = File::create('logs.csv', 100);

        $response = $this->postJson($this->uri, ['file' => $file]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJsonStructure(['message', 'errors' => ['file']]);
    }

    /**
     * @test
     */
    public function must_be_able_to_process_file_received(): void
    {
        $fileContent = file_get_contents(__DIR__ . '/logs.txt');
        $file = File::createWithContent('logs.txt', $fileContent);

        $response = $this->postJson($this->uri, ['file' => $file]);

        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseCount('requests', 10);
    }
}
