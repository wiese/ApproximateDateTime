<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

class Range
{

    /**
     * @var DateTimeData
     */
    private $start;

    /**
     * @var DateTimeData
     */
    private $end;

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
     */
    public function setStart(DateTimeData $start) : void
    {
        $this->start = $start;
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
     */
    public function setEnd(DateTimeData $end) : void
    {
        $this->end = $end;
    }

    /**
     * Dissect the range into individual ranges of the most detailed unit set
     *
     * @return Ranges
     */
    public function filet() : Ranges
    {
        $ranges = new Ranges();

        $targetUnit = $this->start->getHighestUnit();

        for ($value = $this->start->get($targetUnit); $value <= $this->end->get($targetUnit); $value++) {
            $range = clone $this;
            $range->getStart()->set($targetUnit, $value);
            $range->getEnd()->set($targetUnit, $value);

            $ranges->append($range);
        }

        return $ranges;
    }

    /**
     * Make sure children are not manipulated via references in copies. ImmutableDateTimeData containers as alternative?
     */
    public function __clone()
    {
        $this->start = clone $this->start;
        $this->end = clone $this->end;
    }
}
