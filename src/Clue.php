<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

use wiese\ApproximateDateTime\Data\Type\ClueData as Data;
use wiese\ApproximateDateTime\Data\DataCarrier;
use wiese\ApproximateDateTime\Data\DateTimeDataAccessors;

class Clue
{
    use DataCarrier;
    use DateTimeDataAccessors;

    /**
     * @var Data
     */
    protected $data;

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

    public function __construct()
    {
        $this->data = new Data();
    }

    /**
     * @return int|null
     */
    public function getN() : ? int
    {
        return $this->data->n;
    }

    /**
     * Set the numeric representation of a weekday
     *
     * @param int|null $n The week. 1 for Monday through 7 for Sunday
     */
    public function setN(int $n = null) : void
    {
        $this->data->n = $n;
    }

    /**
     * Get the units with non-null values
     *
     * @return array
     */
    public function getSetUnits() : array
    {
        $units = $this->getUnits();

        $set = [];
        foreach ($units as $unit) {
            if (!is_null($this->data->{$unit})) {
                $set[] = $unit;
            }
        }

        return $set;
    }
}
