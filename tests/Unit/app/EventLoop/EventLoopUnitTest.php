<?php

namespace Tests\Unit\app\EventLoop;

use App\EventLoop\EventLoop;
use Fiber;
use PHPUnit\Framework\TestCase;

class EventLoopUnitTest extends TestCase
{
    protected EventLoop $eventLoop;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventLoop = new EventLoop();
    }

    /**
     * @test
     * @dataProvider callablesRegisterProvider
     */
    public function should_be_able_to_register_callables_to_callstack(
        int $expectedCount,
        array $callables
    ): void {
        array_walk($callables, fn ($callable) => $this->eventLoop->register($callable));
        $callStack = $this->eventLoop->getCallStack();

        $expectedCallstack = array_map(fn ($callable) => new Fiber($callable), $callables);
        $this->assertEquals($expectedCallstack, $callStack);
        $this->assertCount($expectedCount, $callStack);
    }

    /**
     * @test
     */
    public function should_be_able_to_execute_callstack(): void
    {
        $callables = [
            function () {
                foreach (range(1, 5) as $number) {
                    echo $number;
                    $this->eventLoop->next();
                }
            },
            function () {
                foreach (range(1, 10) as $number) {
                    echo $number;
                    $this->eventLoop->next();
                }
            },
        ];
        array_walk($callables, fn ($callable) => $this->eventLoop->register($callable));

        $this->eventLoop->execute();

        $expectedOutput = '1122334455678910';
        $this->expectOutputString($expectedOutput);
    }

    private function callablesRegisterProvider(): array
    {
        return [
            'register one callable' => [
                1,
                [
                    function () {
                        foreach (range(1, 5) as $number) {
                            echo $number;
                        }
                    },
                ],
            ],
            'register two callable' => [
                2,
                [
                    function () {
                        foreach (range(1, 5) as $number) {
                            echo $number;
                        }
                    },
                    function () {
                        foreach (range(1, 7) as $number) {
                            echo $number;
                            $this->eventLoop->next();
                        }
                    },
                ],
            ],
        ];
    }
}
