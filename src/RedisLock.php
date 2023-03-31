<?php

namespace Mx\RedisLock;

use Redis;

class RedisLock extends Lock
{
    /**
     * @var Redis
     */
    protected Redis $redis;

    public function __construct($redis, $name, $seconds, $owner = null)
    {
        parent::__construct($name, $seconds, $owner);
        $this->redis = $redis;
    }

    /**
     * @return bool
     */
    public function acquire(): bool
    {
        if (version_compare($this->redis->info()['redis_version'], '2.6.7') < 0) {
            $result = $this->redis->setnx($this->name, $this->owner);
            if(intval($result) === 1 && $this->seconds > 0) {
                $this->redis->expire($this->name, $this->seconds);
            }
        }else{
            $result = $this->redis->set($this->name, $this->owner,['nx', 'ex' => $this->seconds]);
        }
        return intval($result) === 1;
    }

    /**
     * @return bool
     */
    public function release(): bool
    {
        if ($this->isOwnedByCurrentProcess()) {
            $this->redis->eval(LuaScript::checkRelease(), ['name' => $this->name, 'owner' => $this->owner],1);
        }
        return false;
    }

    /**
     * @return void
     */
    public function forceRelease(): void
    {
        $this->redis->del($this->name);
    }

    /**
     * @return string
     */
    protected function getCurrentOwner(): string
    {
        return $this->redis->get($this->name);
    }
}