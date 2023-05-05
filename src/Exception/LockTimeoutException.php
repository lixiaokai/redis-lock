<?php

declare(strict_types=1);

namespace Lixiaokai\RedisLock\Exception;

use Exception;

/**
 * 锁超时异常.
 *
 * @see https://github.com/laravel/framework/blob/9.x/src/Illuminate/Contracts/Cache/LockTimeoutException.php
 */
class LockTimeoutException extends Exception
{

}
