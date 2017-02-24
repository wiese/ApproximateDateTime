<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Data\Type;

class ClueData extends DateTimeData
{

    /**
     * Numeric representation of a weekday, Monday through Sunday
     *
     * @example 1 | 7
     *
     * @var int|null
     */
    public $n;
}
