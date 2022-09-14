<?php

namespace Tests\Feature\app\Repositories\Reports;

use App\Repositories\Reports\ReportRepositoryInterface;
use Tests\TestCase;

abstract class ReportTestCase extends TestCase
{
    abstract protected function report(): ReportRepositoryInterface;
    abstract protected function filename(): string;
    abstract protected function header(): array;
    abstract protected function data(): array;

    /**
     * @test
     */
    public function should_have_correclty_registered_filename(): void
    {
        $filename = $this->report()->filename();

        $expectedFilename = $this->filename();
        $this->assertEquals($expectedFilename, $filename);
    }

    /**
     * @test
     */
    public function should_have_correclty_registered_header(): void
    {
        $header = $this->report()->header();

        $expectedHeader = $this->header();
        $this->assertEquals($expectedHeader, $header);
    }

    /**
     * @test
     */
    public function should_have_correclty_registered_data(): void
    {
        $data = $this->report()->data();

        $expectedData = $this->data();
        $this->assertEquals($expectedData, $data);
    }
}
