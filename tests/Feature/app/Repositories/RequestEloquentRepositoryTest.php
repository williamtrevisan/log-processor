<?php

namespace Tests\Feature\app\Repositories;

use App\Models\Request;
use App\Repositories\Eloquent\RequestEloquentRepository;
use App\Repositories\Eloquent\RequestRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class RequestEloquentRepositoryTest extends TestCase
{
    use RefreshDatabase;

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
     * @dataProvider requestProvider
     */
    public function should_be_able_to_create_new_requests(
        int $quantityRequests,
        array $requests
    ): void {
        $this->requestRepository->create($requests);

        $this->assertDatabaseCount('requests', $quantityRequests);
    }

    /**
     * @test
     */
    public function should_be_able_to_find_requests_by_consumer(): void
    {
        $consumerOneId = Uuid::uuid4()->toString();
        Request::factory(3)->create(['consumer_id' => $consumerOneId]);
        $consumerTwoId = Uuid::uuid4()->toString();
        Request::factory(4)->create(['consumer_id' => $consumerTwoId]);
        $consumerThreeId = Uuid::uuid4()->toString();
        Request::factory(6)->create(['consumer_id' => $consumerThreeId]);

        $requestsByConsumer = $this->requestRepository->findByConsumer();

        $this->assertDatabaseCount('requests', 13);
        $this->assertEquals(3, $requestsByConsumer[0]['quantity_requests']);
        $this->assertEquals(4, $requestsByConsumer[1]['quantity_requests']);
        $this->assertEquals(6, $requestsByConsumer[2]['quantity_requests']);
    }

    /**
     * @test
     */
    public function should_be_able_to_find_requests_by_service(): void
    {
        $serviceOneId = Uuid::uuid4()->toString();
        Request::factory(2)->create([
            'service_id' => $serviceOneId,
            'service_name' => 'service one',
        ]);
        $serviceTwoId = Uuid::uuid4()->toString();
        Request::factory(3)->create([
            'service_id' => $serviceTwoId,
            'service_name' => 'service two',
        ]);
        $serviceThreeId = Uuid::uuid4()->toString();
        Request::factory(5)->create([
            'service_id' => $serviceThreeId,
            'service_name' => 'service three',
        ]);

        $requestsByService = $this->requestRepository->findByService();

        $this->assertDatabaseCount('requests', 10);
        $this->assertEquals(2, $requestsByService[0]['quantity_requests']);
        $this->assertEquals(3, $requestsByService[1]['quantity_requests']);
        $this->assertEquals(5, $requestsByService[2]['quantity_requests']);
    }

    /**
     * @test
     */
    public function should_be_able_to_find_requests_with_average_latency_by_service(): void
    {
        $serviceOneId = Uuid::uuid4()->toString();
        $expectedRequestsServiceOne = Request::factory(4)->create([
            'service_id' => $serviceOneId,
            'service_name' => 'service one',
        ]);
        $serviceTwoId = Uuid::uuid4()->toString();
        $expectedRequestsServiceTwo = Request::factory(5)->create([
            'service_id' => $serviceTwoId,
            'service_name' => 'service two',
        ]);

        $requestsWithAverageLatencyByService =
            $this->requestRepository->findByService(true);

        $expectedAverageProxyLatencyServiceOne = $expectedRequestsServiceOne->avg('proxy_latency');
        $expectedAverageKongLatencyServiceOne = $expectedRequestsServiceOne->avg('kong_latency');
        $expectedAverageRequestLatencyServiceOne = $expectedRequestsServiceOne->avg('request_latency');
        $expectedAverageProxyLatencyServiceTwo = $expectedRequestsServiceTwo->avg('proxy_latency');
        $expectedAverageKongLatencyServiceTwo = $expectedRequestsServiceTwo->avg('kong_latency');
        $expectedAverageRequestLatencyServiceTwo = $expectedRequestsServiceTwo->avg('request_latency');
        $this->assertDatabaseCount('requests', 9);
        $this->assertCount(2, $requestsWithAverageLatencyByService);
        $this->assertEquals(4, $requestsWithAverageLatencyByService[0]['quantity_requests']);
        $this->assertEquals(5, $requestsWithAverageLatencyByService[1]['quantity_requests']);
        $this->assertEquals(
            $expectedAverageProxyLatencyServiceOne,
            $requestsWithAverageLatencyByService[0]['average_proxy_latency']
        );
        $this->assertEquals(
            $expectedAverageKongLatencyServiceOne,
            $requestsWithAverageLatencyByService[0]['average_kong_latency']
        );
        $this->assertEquals(
            $expectedAverageRequestLatencyServiceOne,
            $requestsWithAverageLatencyByService[0]['average_request_latency']
        );
        $this->assertEquals(
            $expectedAverageProxyLatencyServiceTwo,
            $requestsWithAverageLatencyByService[1]['average_proxy_latency']
        );
        $this->assertEquals(
            $expectedAverageKongLatencyServiceTwo,
            $requestsWithAverageLatencyByService[1]['average_kong_latency']
        );
        $this->assertEquals(
            $expectedAverageRequestLatencyServiceTwo,
            $requestsWithAverageLatencyByService[1]['average_request_latency']
        );
    }

    /**
     * @test
     */
    public function should_be_able_to_get_records_count(): void
    {
        $expectedCount = 14;
        Request::factory($expectedCount)->create();

        $recordsCount = $this->requestRepository->count();

        $this->assertDatabaseCount('requests', $expectedCount);
        $this->assertEquals($expectedCount, $recordsCount);
    }

    /**
     * @test
     */
    public function should_be_able_to_delete_all_records(): void
    {
        Request::factory(17)->create();
        $this->assertDatabaseCount('requests', 17);

        $response = $this->requestRepository->deleteAll();

        $this->assertDatabaseCount('requests', 0);
        $this->assertTrue($response);
    }

    private function requestProvider(): array
    {
        return [
            'sending three' => [
                3,
                [
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
                ],
            ],
            'sending two' => [
                2,
                [
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
                ],
            ],
            'sending only one' => [
                1,
                [
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
                ],
            ]
        ];
    }
}
