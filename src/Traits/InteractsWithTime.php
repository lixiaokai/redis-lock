<?php

declare(strict_types=1);

namespace Lixiaokai\RedisLock\Traits;

use Carbon\Carbon;
use DateInterval;
use DateTimeInterface;

/**
 * @see \Hyperf\Support\Traits\InteractsWithTime Hyper 3.0
 * @see https://github.com/laravel/framework/blob/9.x/src/Illuminate/Support/InteractsWithTime.php
 */
trait InteractsWithTime
{
    /**
     * Get the number of seconds until the given DateTime.
     *
     * @param DateInterval|DateTimeInterface|int $delay
     */
    protected function secondsUntil($delay): int
    {
        $delay = $this->parseDateInterval($delay);

        return $delay instanceof DateTimeInterface
                            ? max(0, $delay->getTimestamp() - $this->currentTime())
                            : (int) $delay;
    }

    /**
     * Get the "available at" UNIX timestamp.
     *
     * @param DateInterval|DateTimeInterface|int $delay
     */
    protected function availableAt($delay = 0): int
    {
        $delay = $this->parseDateInterval($delay);

        return $delay instanceof DateTimeInterface
                            ? $delay->getTimestamp()
                            : Carbon::now()->addSeconds($delay)->getTimestamp();
    }

    /**
     * If the given value is an interval, convert it to a DateTime instance.
     *
     * @param  DateInterval|DateTimeInterface|int $delay
     * @return DateTimeInterface|int
     */
    protected function parseDateInterval($delay)
    {
        if ($delay instanceof DateInterval) {
            $delay = Carbon::now()->add($delay);
        }

        return $delay;
    }

    /**
     * Get the current system time as a UNIX timestamp.
     */
    protected function currentTime(): int
    {
        return Carbon::now()->getTimestamp();
    }
}
