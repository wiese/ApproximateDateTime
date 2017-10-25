<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

use wiese\ApproximateDateTime\Data\Type\ClueData as Data;
use wiese\ApproximateDateTime\Data\DataCarrier;
use wiese\ApproximateDateTime\Data\DateTimeDataAccessors;
use UnexpectedValueException;

class Clue
{
    use DataCarrier;
    use DateTimeDataAccessors;

    /**
     * @var Data
     */
    private $data;

    /**
     * Options describing the effect of the clue (in $type)
     */
    public const IS_WHITELIST = 1;
    public const IS_BLACKLIST = 2;
    public const IS_BEFOREEQUALS = 4;
    public const IS_AFTEREQUALS = 8;

    private const VALID_TYPES = [
        self::IS_WHITELIST,
        self::IS_BLACKLIST,
        self::IS_BEFOREEQUALS,
        self::IS_AFTEREQUALS
    ];

    /**
     * The type of description desired (inclusive, exclusive, comparison, ...)
     *
     * @tutorial Defaults to whitelist - it's most likely, hence convenience
     *
     * @var int
     */
    private $type = self::IS_WHITELIST;

    /**
     * @param int $type The type of description desired (inclusive, exclusive, comparison, ...)
     */
    public function __construct(int $type = self::IS_WHITELIST)
    {
        if (!in_array($type, self::VALID_TYPES)) {
            throw new UnexpectedValueException('Bad Clue type given: ' . $type);
        }

        $this->type = $type;

        $this->data = new Data();
    }

    /**
     * @return int
     */
    public function getType() : int
    {
        return $this->type;
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
