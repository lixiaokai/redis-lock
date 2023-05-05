<?php

declare(strict_types=1);

namespace Lixiaokai\RedisLock;

use Hyperf\Redis\Redis;
use Lixiaokai\RedisLock\Lua\LuaScripts;

/**
 * Redis 分布式锁.
 *
 * @see https://github.com/laravel/framework/blob/9.x/src/Illuminate/Cache/RedisLock.php
 */
class RedisLock extends AbstractLock
{
    /**
     * @var Redis redis-实例
     */
    protected $redis;

    /**
     * 创建 1 个新锁实例.
     *
     * @param Redis       $redis   redis-实例
     * @param string      $name    锁名称
     * @param int         $seconds 锁过期秒数
     * @param null|string $owner   拥有者标识
     */
    public function __construct($redis, $name, $seconds = 0, $owner = null)
    {
        parent::__construct($name, $seconds, $owner);

        $this->redis = $redis;
    }

    /**
     * {@inheritDoc}
     */
    public function acquire()
    {
        if ($this->seconds > 0) {
            // 注意：$this->redis->set() 方法传参和 Laravel 有所区别
            return $this->redis->set($this->name, $this->owner, ['NX', 'EX' => $this->seconds]) === true;
        }

        return $this->redis->setnx($this->name, $this->owner) === true;
    }

    /**
     * {@inheritDoc}
     */
    public function release()
    {
        // 注意：$this->redis->eval() 方法传参和 Laravel 有所区别
        return (bool) $this->redis->eval(LuaScripts::releaseLock(), ['name' => $this->name, 'owner' => $this->owner], 1);
    }

    /**
     * {@inheritDoc}
     */
    public function forceRelease()
    {
        $this->redis->del($this->name);
    }

    /**
     * {@inheritDoc}
     */
    protected function getCurrentOwner()
    {
        return $this->redis->get($this->name);
    }
}
