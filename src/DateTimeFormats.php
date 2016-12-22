<?php

namespace wiese\ApproximateDateTime;

/**
 * Named constants for repeatedly used DateTime formatting strings
 *
 * @see \DateTime::format()
 *
 * @package wiese\ApproximateDateTime
 */
interface DateTimeFormats
{
    const YEAR = 'Y';
    const MONTH = 'n';
    const DAY = 'j';
    const HOUR = 'H';
    const MINUTE = 'i';
    const SECOND = 's';
    const WEEKDAY = 'N';
}
