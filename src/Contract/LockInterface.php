<?php

declare(strict_types=1);

namespace Lixiaokai\RedisLock\Contract;

/**
 * 锁接口.
 *
 * @see https://github.com/laravel/framework/blob/9.x/src/Illuminate/Contracts/Cache/Lock.php
 */
interface LockInterface
{
    /**
     * 获取锁.
     *
     * @param  null|callable $callback 回调函数
     * @return mixed
     */
    public function get($callback = null);

    /**
     * 在给定秒数内获取锁.
     *
     * @param  int           $seconds  秒数
     * @param  null|callable $callback 回调函数
     * @return mixed
     */
    public function block($seconds, $callback = null);

    /**
     * 释放锁.
     *
     * @return bool
     */
    public function release();

    /**
     * 返回当前锁的拥有者标识.
     *
     * @return string
     */
    public function owner();

    /**
     * 强制释放锁而不考虑拥有者.
     */
    public function forceRelease();
}
