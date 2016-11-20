<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\OptionFilter;

use wiese\ApproximateDateTime\DateTimeData;
use wiese\ApproximateDateTime\Range;
use wiese\ApproximateDateTime\Ranges;
use DateInterval;
use DatePeriod;

class Weekday extends Base
{
    public function apply(Ranges $ranges) : Ranges
    {
        if (count($this->clues->getWhitelist($this->unit)) === 7 && count($this->clues->getBlacklist($this->unit)) === 0) { // all days allowed
            return $ranges;
        }

        $newRanges = new Ranges();

        $options = $this->getAllowableOptions();

        switch (count($options)) {
            case 7: // all days allowed
                return $ranges;
            case 0: // no days allowed
                return $newRanges;
        }

        $oneDayInterval = new DateInterval('P1D');

        foreach ($ranges as $range) {
            /**
             * @var \wiese\ApproximateDateTime\Range $range
             */
            $start = $range->getStart()->toDateTime();
            $end = $range->getEnd()->toDateTime();
            $end->add($oneDayInterval); // work with end day, too
            $period = new DatePeriod($start, $oneDayInterval, $end);
            $gap = true;
            $previous = null;
            foreach ($period as $moment) {
                if (in_array($moment->format('N'), $options)) {
                    if ($gap) {
                        $newRange = new Range();
                        $newRanges->append($newRange); // append, keep var for edit
                        $newRange->setStart(DateTimeData::fromDateTime($moment));
                    }
                    $gap = false;
                    $previous = $moment;
                } else {
                    $gap = true;
                    if ($previous) {
                        $newRange->setEnd(DateTimeData::fromDateTime($previous));
                        $previous = null;
                    }
                }
            }
            if ($newRange) { // dangling end
                $newRange->setEnd(DateTimeData::fromDateTime($moment));
            }
        }

        return $newRanges;
    }
}
