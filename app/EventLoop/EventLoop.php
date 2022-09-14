<?php

namespace App\EventLoop;

use App\EventLoop\BaseEventLoop;
use Fiber;

class EventLoop
{
    public function __construct(private array $callStack = [])
    {
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

    private function run(int $id, Fiber $fiber): mixed
    {
        if (! $fiber->isStarted()) {
            return $fiber->start($id);
        }

        if (! $fiber->isTerminated()) {
            return $fiber->resume();
        }

        unset($this->callStack[$id]);
        return $fiber->getReturn();
    }
}
