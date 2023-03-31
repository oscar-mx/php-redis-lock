<?php

namespace Mx\RedisLock;


abstract class Lock implements LockContract
{

    use InteractsWithTime;

    /**
     * @var string
     */
    protected string $name;


    /**
     * @var int
     */
    protected int $seconds;


    /**
     * @var string|null
     */
    protected ?string $owner;


    /**
     * @var int
     */
    protected int $sleepMilliseconds = 250;


    /**
     * @param string $name
     * @param int $seconds
     * @param string|null $owner
     */
    public function __construct(string $name, int $seconds, string $owner = null)
    {
        if (is_null($owner)) {
            $owner = (new \Godruoyi\Snowflake\Snowflake)
                ->setSequenceResolver('RedisSequenceResolver')
                ->id();
        }

        $this->name = $name;
        $this->owner = $owner;
        $this->seconds = $seconds;
    }


    /**
     * @return bool
     */
    abstract public function acquire(): bool;


    /**
     * @return bool
     */
    abstract public function release(): bool;


    /**
     * @return string
     */
    abstract protected function getCurrentOwner(): string;


    /**
     * @param null $callback
     * @return mixed
     */
    public function get($callback = null): mixed
    {
        $result = $this->acquire();

        if ($result && is_callable($callback)) {
            try {
                return $callback();
            } finally {
                $this->release();
            }
        }

        return $result;
    }


    /**
     * @param int $seconds
     * @param null $callback
     * @return mixed
     * @throws \Exception
     */
    public function block(int $seconds, $callback = null): mixed
    {
        $starting = $this->currentTime();

        while (! $this->acquire()) {
            usleep($this->sleepMilliseconds * 1000);

            if ($this->currentTime() - $seconds >= $starting) {
                throw new \Exception();
            }
        }

        if (is_callable($callback)) {
            try {
                return $callback();
            } finally {
                $this->release();
            }
        }

        return true;
    }


    /**
     * @return string
     */
    public function owner(): string
    {
        return $this->owner;
    }


    /**
     * @return bool
     */
    protected function isOwnedByCurrentProcess(): bool
    {
        return $this->getCurrentOwner() === $this->owner;
    }
}