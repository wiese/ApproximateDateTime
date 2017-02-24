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
    public const YEAR = 'Y';
    public const MONTH = 'n';
    public const DAY = 'j';
    public const HOUR = 'G';
    public const MINUTE = 'i';
    public const SECOND = 's';
    public const WEEKDAY = 'N';
}
