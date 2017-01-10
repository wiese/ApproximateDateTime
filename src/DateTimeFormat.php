<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

/**
 * Named constants for repeatedly used DateTime formatting strings
 *
 * @see \DateTime::format()
 *
 * @package wiese\ApproximateDateTime
 */
interface DateTimeFormat
{
    const YEAR = 'Y';
    const MONTH = 'n';
    const DAY = 'j';
    const HOUR = 'G';
    const MINUTE = 'i';
    const SECOND = 's';
    const WEEKDAY = 'N';
}
