<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

use wiese\ApproximateDateTime\Data\Vehicle;

class Clue extends Vehicle
{
    /**
     * Options describing the effect of the clue (in $filter)
     *
     * @var integer
     */
    const FILTER_WHITELIST = 1;
    const FILTER_BLACKLIST = 2;
    const FILTER_BEFOREEQUALS = 3;
    const FILTER_AFTEREQUALS = 4;

    /**
     * The type of decription desired (inclusive, exclusive, comparison, ...)
     *
     * @tutorial Defaults to whitelist - it's most likely, hence convenience
     *
     * @var int
     */
    public $filter = self::FILTER_WHITELIST;

    /**
     * Value as provided by user (ClueParser)
     *
     * @var mixed
     */
    public $rawValue;
}
