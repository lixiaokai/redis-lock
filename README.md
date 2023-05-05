# 介绍

Redis 分布式锁 For [Hyperf 3.0](https://hyperf.wiki/3.0/#/)，该组件衍生于 [Laravel](https://laravelacademy.org/post/22000#toc-12) 缓存系统的原子锁并做了 Hyperf 适配。

# 安装

注意：Redis 服务端的版本需 >= 2.6.12 ( 该版本开始 SET 命令才支持设置 `EX` `PX` 过期选项 )

```
composer require lixiaokai/redis-lock
```

# 使用

## 原理

- `NX`：建不存在才设置
- `EX`: 自动过期秒数

```redis
SET lockKey uniqueValue NX EX 10
```

## 例子

原子锁允许对分布式锁进行操作而不必担心竞争条件。你可以使用如下方法来创建和管理锁：

```php
use Hyperf\Context\ApplicationContext;
use Lixiaokai\RedisLock\RedisLock;

$redis = ApplicationContext::getContainer()->get(Redis::class);
$lock = new RedisLock($redis, 'lock:test', 10);

if ($lock->get()) {
    // 锁定时间为 10 秒...

    $lock->release();
}
```

`get` 方法可以接受一个闭包。在闭包执行之后，将会自动释放锁

```php
$lock->get(function () {
    // 锁定时间为 10 秒...
});
```

如果你在请求时锁无法使用，你可以控制等待指定的秒数。

如果在指定的时间限制内无法获取锁，则会抛出 `Lixiaokai\RedisLock\Exception\LockTimeoutException`

```php
use Hyperf\Context\ApplicationContext;
use Lixiaokai\RedisLock\RedisLock;
use Lixiaokai\RedisLock\Exception\LockTimeoutException;

$redis = ApplicationContext::getContainer()->get(Redis::class);
$lock = new RedisLock($redis, 'lock:test', 10);

try {
    $lock->block(5);
    // 为获得锁等待最多 5 秒...
} catch (LockTimeoutException $e) {
    // 无法获取锁...
} finally {
    $lock->release();
}
```

通过向 `block` 方法传递闭包，可以简化上述示例。当闭包传递给此方法时，将尝试在指定的秒数内获取锁，并在执行闭包后自动释放锁：

```php
use Hyperf\Context\ApplicationContext;
use Lixiaokai\RedisLock\RedisLock;

$redis = ApplicationContext::getContainer()->get(Redis::class);
$lock = new RedisLock($redis, 'lock:test', 10);

$lock->block(5, function () {
    // 为获得锁等待最多 5 秒...
});
```
