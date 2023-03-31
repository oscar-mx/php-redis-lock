<?php

namespace Mx\RedisLock;

trait InteractsWithTime
{
    /**
     * Get current Time
     * @return int
     */
    protected function currentTime(): int
    {
        return time();
    }
}
