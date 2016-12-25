<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

use wiese\ApproximateDateTime\DateTimeData;
use wiese\ApproximateDateTime\Ranges;

class Range
{

    /**
     * @var DateTimeData
     */
    protected $start;

    /**
     * @var DateTimeData
     */
    protected $end;

    /**
     * Get the start of the range
     *
     * @return DateTimeData
     */
    public function getStart() : DateTimeData
    {
        return $this->start;
    }

    /**
     * Set the start of the range
     *
     * @param DateTimeData $start
     * @return self
     */
    public function setStart(DateTimeData $start) : self
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get the end of the range
     *
     * @return DateTimeData
     */
    public function getEnd() : DateTimeData
    {
        return $this->end;
    }

    /**
     * Set the end of the range
     *
     * @param DateTimeData $end
     * @return self
     */
    public function setEnd(DateTimeData $end) : self
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Disect the range into individual ranges on the lowest set unit
     *
     * @return Ranges
     */
    public function filet() : Ranges
    {
        $ranges = new Ranges();

        $targetUnit = $this->start->getNextUnit();

        $min = $this->start->get($targetUnit);
        $max = $this->end->get($targetUnit);

        for ($value = $min; $value <= $max; $value++) {
            $range = clone $this;
            $range->getStart()->set($targetUnit, $value);
            $range->getEnd()->set($targetUnit, $value);

            $ranges->append($range);
        }

        return $ranges;
    }

    public function __clone()
    {
        $this->start = clone $this->start;
        $this->end = clone $this->end;
    }
}
