<?php

namespace App\EventLoop;

use Fiber;

abstract class BaseEventLoop
{
    protected array $callStack;

    public abstract function register(callable $callable): void;
    public abstract function execute(): void;
    public abstract function next(): mixed;

    protected function run(int $id, Fiber $fiber): mixed
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
