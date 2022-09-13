<?php

namespace App;

use App\EventLoop\BaseEventLoop;
use Fiber;

class EventLoop extends BaseEventLoop
{
    public function __construct(array $callStack = [])
    {
        $this->callStack = $callStack;
    }

    public function register(callable $callable): void
    {
        $this->callStack[] = new Fiber($callable);
    }

    public function execute(): void
    {
        while ($this->callStack) {
            foreach ($this->callStack as $id => $fiber) {
                $this->run($id, $fiber);
            }
        }
    }

    public function next(): mixed
    {
        return Fiber::suspend();
    }
}
