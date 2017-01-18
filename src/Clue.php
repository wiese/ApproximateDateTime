<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

use wiese\ApproximateDateTime\Data\Vehicle;

class Clue extends Vehicle
{
    /**
     * Options describing the effect of the clue (in $type)
     */
    public const IS_WHITELIST = 1;
    public const IS_BLACKLIST = 2;
    public const IS_BEFOREEQUALS = 4;
    public const IS_AFTEREQUALS = 8;

    /**
     * The type of decription desired (inclusive, exclusive, comparison, ...)
     *
     * @tutorial Defaults to whitelist - it's most likely, hence convenience
     *
     * @var int
     */
    public $type = self::IS_WHITELIST;

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
     */
    public function setN(int $n = null) : void
    {
        $this->n = $n;
    }
}
