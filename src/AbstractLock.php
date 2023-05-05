<?php

declare(strict_types=1);

namespace Lixiaokai\RedisLock;

use Hyperf\Stringable\Str;
use Lixiaokai\RedisLock\Contract\LockInterface;
use Lixiaokai\RedisLock\Exception\LockTimeoutException;
use Lixiaokai\RedisLock\Traits\InteractsWithTime;

/**
 * 锁抽象.
 *
 * @see https://github.com/laravel/framework/blob/9.x/src/Illuminate/Cache/Lock.php
 */
abstract class AbstractLock implements LockInterface
{
    use InteractsWithTime;

    /**
     * 锁名称.
     *
     * @var string
     */
    protected $name;

    /**
     * 锁过期秒数.
     *
     * @var int
     */
    protected $seconds;

    /**
     * 拥有者标识符.
     *
     * 也可以理解为当前线程请求标识
     *
     * @var string
     */
    protected $owner;

    /**
     * 在阻塞时每次重新尝试获取锁之前等待的毫秒数.
     *
     * @var int
     */
    protected $sleepMilliseconds = 250;

    /**
     * 创建 1 个新锁实例.
     *
     * @param string      $name    锁名称
     * @param int         $seconds 锁过期秒数
     * @param null|string $owner   拥有者标识
     */
    public function __construct($name, $seconds = 0, $owner = null)
    {
        if (is_null($owner)) {
            $owner = Str::random();
        }

        $this->name = $name;
        $this->owner = $owner;
        $this->seconds = $seconds;
    }

    /**
     * 获取锁.
     *
     * @return bool
     */
    abstract public function acquire();

    /**
     * 释放锁.
     *
     * @return bool
     */
    abstract public function release();

    /**
     * 获取锁.
     *
     * @param  null|callable $callback 回调函数
     * @return mixed
     */
    public function get($callback = null)
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
     * 在给定的秒数内获取锁.
     *
     * 注意：
     * 1. 参数 1 一般设置几秒即可
     * 2. 参数 2 没有使用回调函数，需要手动调用锁释放
     *
     * @param  int           $seconds  给定的秒数
     * @param  null|callable $callback 回调函数
     * @return mixed
     *
     * @throws LockTimeoutException
     */
    public function block($seconds, $callback = null)
    {
        $starting = $this->currentTime();

        // 努力的尝试获取锁，如果获取失败则在给定的时间内每隔 250 毫秒 ( 默认值 ) 再次重试，直到给定的秒数超时抛出异常
        while (! $this->acquire()) {
            usleep($this->sleepMilliseconds * 1000);

            if ($this->currentTime() - $seconds >= $starting) {
                throw new LockTimeoutException();
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
     * 当前锁的拥有者.
     *
     * @return string
     */
    public function owner()
    {
        return $this->owner;
    }

    /**
     * 指定在阻塞时每次重新尝试获取锁之前等待的毫秒数.
     *
     * @param  int   $milliseconds 毫秒数
     * @return $this
     */
    public function betweenBlockedAttemptsSleepFor($milliseconds)
    {
        $this->sleepMilliseconds = $milliseconds;

        return $this;
    }

    /**
     * 获取当前拥有者的值.
     *
     * @return string
     */
    abstract protected function getCurrentOwner();

    /**
     * 是否属于当前请求的拥有者.
     *
     * @return bool
     */
    protected function isOwnedByCurrentProcess()
    {
        return $this->getCurrentOwner() === $this->owner;
    }
}
