<?php

namespace Tests\Feature\app\Repositories;

use App\Models\Request;
use App\Repositories\Eloquent\RequestEloquentRepository;
use App\Repositories\Eloquent\RequestRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Tests\TestCase;

class RequestEloquentRepositoryTest extends TestCase
{
    protected RequestRepositoryInterface $requestRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestRepository = new RequestEloquentRepository(request: new Request());
    }

    /**
     * @test
     */
    public function must_implements_interface(): void
    {
        $this->assertInstanceOf(
            RequestEloquentRepository::class, $this->requestRepository
        );
    }

    /**
     * @test
     */
    public function should_be_able_to_create_a_new_request(): void
    {
        $requests = [
            [
                'consumer_id' => 'd83ff610-3461-36d7-be57-7e909cfce8b8',
                'service_id' => 'c3e86413-648a-3552-90c3-b13491ee07d6',
                'service_name' => 'ritchie',
                'method' => 'GET',
                'uri' => '/',
                'url' => 'http://ohara.com',
                'size' => 162,
                'response_status' => Response::HTTP_OK,
                'proxy_latency' => 1852,
                'kong_latency' => 11,
                'request_latency' => 1837,
                'client_ip' => '191.95.67.184',
                'started_at' => Carbon::createFromTimestamp(1563597919)->format('Y-m-d'),
            ],
            [
                'consumer_id' => '0e1baae1-d822-3acc-878e-dee07e07fc25',
                'service_id' => '22f8e3a6-01f7-3264-b4b5-9d178df11d06',
                'service_name' => 'sauer',
                'method' => 'GET',
                'uri' => '/',
                'url' => 'http://spinka.biz',
                'size' => 139,
                'response_status' => Response::HTTP_NO_CONTENT,
                'proxy_latency' => 938,
                'kong_latency' => 16,
                'request_latency' => 1417,
                'client_ip' => '58.237.156.9',
                'started_at' => Carbon::createFromTimestamp(1548866961)->format('Y-m-d'),
            ],
            [
                'consumer_id' => '7d560fac-abc7-3cca-a1d4-b754e408e51e',
                'service_id' => 'a5bf08bd-c030-30d5-8009-83a8c30103bf',
                'service_name' => 'orn',
                'method' => 'GET',
                'uri' => '/',
                'url' => 'http://wintheiser.net',
                'size' => 182,
                'response_status' => Response::HTTP_NOT_FOUND,
                'proxy_latency' => 804,
                'kong_latency' => 20,
                'request_latency' => 2298,
                'client_ip' => '201.120.59.28',
                'started_at' => Carbon::createFromTimestamp(1538014617)->format('Y-m-d'),
            ],
        ];

        $this->requestRepository->create($requests);

        $this->assertDatabaseCount('requests', 3);
    }

    /**
     * @test
     */
    public function should_be_able_to_find_requests_by_consumer_id(): void
    {
        $consumerId = 'd83ff610-3461-36d7-be57-7e909cfce8b8';
        Request::factory(6)->create(['consumer_id' => $consumerId]);
        Request::factory(4)->create();

        $requestsByConsumer = $this->requestRepository->findByConsumerId($consumerId);

        $this->assertDatabaseCount('requests', 10);
        $this->assertCount(6, $requestsByConsumer);
    }

    /**
     * @test
     */
    public function should_be_able_to_find_requests_by_service_id(): void
    {
        $serviceId = '0e1baae1-d822-3acc-878e-dee07e07fc25';
        Request::factory(4)->create(['service_id' => $serviceId]);
        Request::factory(6)->create();

        $requestsByService = $this->requestRepository->findByServiceId($serviceId);

        $this->assertDatabaseCount('requests', 10);
        $this->assertCount(4, $requestsByService);
    }

    /**
     * @test
     */
    public function should_be_able_to_find_requests_with_average_latency_by_service_id(): void
    {
        $serviceId = '7d560fac-abc7-3cca-a1d4-b754e408e51e';
        $expectedRequests = Request::factory(2)->create([
            'service_id' => $serviceId, 'service_name' => 'myservice'
        ]);
        Request::factory(6)->create();

        $requestsByServiceWithAverageLatency =
            $this->requestRepository->findByServiceIdWithAverageLatency($serviceId);

        $expectedAverageProxyLatency = $expectedRequests->avg('proxy_latency');
        $expectedAverageKongLatency = $expectedRequests->avg('kong_latency');
        $expectedAverageRequestLatency = $expectedRequests->avg('request_latency');

        $this->assertDatabaseCount('requests', 8);
        $this->assertCount(1, [$requestsByServiceWithAverageLatency]);
        $this->assertEquals(
            $expectedAverageProxyLatency,
            $requestsByServiceWithAverageLatency['average_proxy_latency']
        );
        $this->assertEquals(
            $expectedAverageKongLatency,
            $requestsByServiceWithAverageLatency['average_kong_latency']
        );
        $this->assertEquals(
            $expectedAverageRequestLatency,
            $requestsByServiceWithAverageLatency['average_request_latency']
        );
    }
}
