<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

use wiese\ApproximateDateTime\DateTimeData;
use wiese\ApproximateDateTime\Ranges;

class Range
{
    /**
     * @var \wiese\ApproximateDateTime\DateTimeData
     */
    protected $start;
    /**
     * @var \wiese\ApproximateDateTime\DateTimeData
     */
    protected $end;

    /**
     * Get the start of the range
     *
     * @return \wiese\ApproximateDateTime\DateTimeData
     */
    public function getStart() : DateTimeData
    {
        return $this->start;
    }

    /**
     * Set the start of the range
     *
     * @param \wiese\ApproximateDateTime\DateTimeData $start
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
     * @return \wiese\ApproximateDateTime\DateTimeData
     */
    public function getEnd() : DateTimeData
    {
        return $this->end;
    }

    /**
     * Set the end of the range
     *
     * @param \wiese\ApproximateDateTime\DateTimeData $end
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
     * @return \wiese\ApproximateDateTime\Ranges
     */
    public function filet() : Ranges
    {
        $ranges = new Ranges();

        $targetUnit = $this->start->getNextUnit();

        $min = $this->start->{$targetUnit};
        $max = $this->end->{$targetUnit};

        for ($value = $min; $value <= $max; $value++) {
            $range = clone $this;
            $range->getStart()->{$targetUnit} = $value;
            $range->getEnd()->{$targetUnit} = $value;

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
