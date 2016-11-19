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

    public function getStart() : DateTimeData
    {
        return $this->start;
    }

    public function setStart(DateTimeData $start) : self
    {
        $this->start = $start;
        return $this;
    }

    public function getEnd() : DateTimeData
    {
        return $this->end;
    }

    public function setEnd(DateTimeData $end) : self
    {
        $this->end = $end;
        return $this;
    }

    public function filet() : Ranges
    {
        $ranges = new Ranges();

        $targetUnit = null;
        foreach ($this->start as $unit => $value) {
            if (is_null($value)) {
                break;
            }
            $targetUnit = $unit;
        }

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
