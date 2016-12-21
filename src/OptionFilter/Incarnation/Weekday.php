<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\OptionFilter\Incarnation;

use wiese\ApproximateDateTime\OptionFilter\Base;
use wiese\ApproximateDateTime\DateTimeData;
use wiese\ApproximateDateTime\Range;
use wiese\ApproximateDateTime\Ranges;
use DateInterval;
use DatePeriod;

/**
 * Apply weekday restrictions (e.g. 'sunday') to existing ranges.
 * Trickier than the ordinary numeric operation as the weekday depends on a complete date of year, month, and day.
 *
 * @package wiese\ApproximateDateTime\OptionFilter\Incarnation
 */
class Weekday extends Base
{
    const DATE_FORMAT_WEEKDAY = 'N';

    /**
     * {@inheritDoc}
     * @see Base::apply()
     */
    public function apply(Ranges $ranges) : Ranges
    {
        $options = $this->getAllowableOptions();

        switch (count($options)) {
            case 7: // all days allowed
                return $ranges; // keep given ranges intact, nothing to do
            case 0: // no days allowed
                return new Ranges();
        }

        $newRanges = new Ranges();
        foreach ($ranges as $range) {
            /**
             * @var Range $range
             */
            $this->patchRanges($newRanges, $range, $options);
        }

        return $newRanges;
    }

    /**
     * Add allowabled dates (defined by) $options from $range to $ranges
     *
     * @param Ranges $ranges Ranges to append matching ranges to
     * @param Range $range The existing to check for matching weekdays
     * @param array $options Allowable weekdays
     */
    protected function patchRanges(Ranges & $ranges, Range $range, array $options) : void
    {
        $dayIterationInterval = new DateInterval('P1D');

        $period = new DatePeriod(
            $range->getStart()->toDateTime(),
            $dayIterationInterval,
            $range->getEnd()->toDateTime()->add($dayIterationInterval) // work with end day, too
        );

        /**
         * Indicator to identify value ranges
         * @var bool $gap
         */
        $gap = true;
        /**
         * @var \DateTimeInterface $previous
         */
        $previous = null;
        /**
         * @var \DateTimeInterface $moment
         */
        $moment = null;
        /**
         * @var Range $newRange
         */
        $newRange = null;
        foreach ($period as $moment) {
            /**
             * @var \DateTimeInterface $moment
             */
            if (in_array($moment->format(self::DATE_FORMAT_WEEKDAY), $options)) {
                if ($gap) {
                    $newRange = new Range();
                    $ranges->append($newRange); // append, keep var for edit
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
        if ($newRange && $moment) { // dangling end
            $newRange->setEnd(DateTimeData::fromDateTime($moment));
        }
    }
}
