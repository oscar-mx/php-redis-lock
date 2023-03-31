<?php

namespace Mx\RedisLock;

interface LockContract
{
    /**
     * @param callable|null $callback
     * @return mixed
     */
    public function get(callable $callback = null): mixed;


    /**
     * @param int $seconds
     * @param callable|null $callback
     * @return mixed
     */
    public function block(int $seconds, callable $callback = null): mixed;


    /**
     * @return bool
     */
    public function release(): bool;


    /**
     * @return string
     */
    public function owner(): string;


    /**
     * @return void
     */
    public function forceRelease(): void;
}