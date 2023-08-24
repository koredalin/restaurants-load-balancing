<?php

namespace Drivers\Helpers;

use DateTime;

/**
 * The most positive case of a separated DateTime class is that if time zone change is needed - it will happen 
 * in one place only.
 *
 * @author H1
 */
class DateTimeManager
{
    public static function getDateTime(): DateTime
    {
        return new DateTime();
    }

    public static function getTimeStamp(): int
    {
        return self::getDateTime()->getTimestamp();
    }
}
