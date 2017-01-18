<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

use wiese\ApproximateDateTime\Data\Vehicle;

class Clue extends Vehicle
{
    /**
     * @todo visibility
     * @todo rename IS_*
     *
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

    /**
     * Numeric representation of a weekday, Monday through Sunday
     *
     * @example 1 | 7
     *
     * @var int|null
     */
    protected $n;

    protected static $options = ['y', 'm', 'd', 'n', 'h', 'i', 's'];

    /**
     * @return int|null
     */
    public function getN() : ? int
    {
        return $this->n;
    }

    /**
     * Set the numeric representation of a weekday
     *
     * @param int|null $n The week. 1 for Monday through 7 for Sunday
     * @return self
     */
    public function setN(int $n = null) : self
    {
        $this->n = $n;
        return $this;
    }
}
