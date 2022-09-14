<?php

namespace Tests\Unit\app\Models;

use App\Models\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use PHPUnit\Framework\TestCase;

class RequestUnitTest extends TestCase
{
    /**
     * @test
     */
    public function incrementing_property_must_be_false(): void
    {
        $incrementing = $this->model()->incrementing;
        $this->assertFalse($incrementing);
    }

    /**
     * @test
     */
    public function must_have_expected_traits(): void
    {
        $traits = class_uses($this->model());

        $expectedTraits = [HasFactory::class];
        $this->assertEquals($expectedTraits, array_keys($traits));
    }

    /**
     * @test
     */
    public function must_have_expected_fillables(): void
    {
        $fillables = $this->model()->getFillable();

        $expectedFillables = [
            'consumer_id',
            'service_id',
            'service_name',
            'proxy_latency',
            'kong_latency',
            'request_latency',
        ];
        $this->assertEquals($expectedFillables, $fillables);
    }

    /**
     * @test
     */
    public function must_have_expected_casts(): void
    {
        $casts = $this->model()->getCasts();

        $expectedCasts = [
            'consumer_id' => 'string',
            'service_id' => 'string',
        ];
        $this->assertEquals($expectedCasts, $casts);
    }

    private function model(): Request
    {
        return new Request();
    }
}
