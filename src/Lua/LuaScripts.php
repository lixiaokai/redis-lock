<?php

declare(strict_types=1);

namespace Lixiaokai\RedisLock\Lua;

/**
 * Lua 脚本.
 *
 * @see https://github.com/laravel/framework/blob/9.x/src/Illuminate/Cache/LuaScripts.php
 */
class LuaScripts
{
    /**
     * 获取 Lua 脚本，保证原子性，释放锁.
     *
     * KEYS[1] - 锁 key
     * ARGV[1] - 拥有者标识
     *
     * @return string
     */
    public static function releaseLock()
    {
        return <<<'LUA'
if redis.call("get",KEYS[1]) == ARGV[1] then
    return redis.call("del",KEYS[1])
else
    return 0
end
LUA;
    }
}
