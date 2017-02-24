<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Data\Type;

class DateTimeData
{

    /**
     * All-digit representation of a year
     *
     * @example 44 | 2016
     *
     * @var int|null
     */
    public $y;

    /**
     * Representation of a month
     *
     * @var int|null
     */
    public $m;

    /**
     * Representation of a day
     *
     * @example 1 | 31
     *
     * @var int|null
     */
    public $d;

    /**
     * Representation of an hour
     *
     * @example 0 | 23
     *
     * @var int|null
     */
    public $h;

    /**
     * Representation of a minute
     *
     * @example 0 | 59
     *
     * @var int|null
     */
    public $i;

    /**
     * Representation of a second
     *
     * @example 0 | 59
     *
     * @var int|null
     */
    public $s;
}
