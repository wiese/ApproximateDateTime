<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

class Clue
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
     * The unit this clue further describes
     *
     * @var string
     */
    public $type;

    /**
     * The type of decription desired (inclusive, exclusive, comparison, ...)
     *
     * @tutorial Defaults to whitelist - it's most likely, hence convenience
     *
     * @var int
     */
    public $filter = self::FILTER_WHITELIST;

    /**
     * Value to base internal calculations on (sanitized)
     *
     * @var mixed
     */
    public $value;

    /**
     * Value as provided by user (ClueParser)
     *
     * @var mixed
     */
    public $rawValue;
}
