<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\OptionFilter\Incarnation;

use wiese\ApproximateDateTime\OptionFilter\Base;
use wiese\ApproximateDateTime\DateTimeData;
use wiese\ApproximateDateTime\DateTimeFormat;
use wiese\ApproximateDateTime\Range;
use wiese\ApproximateDateTime\Ranges;
use DateInterval;
use DatePeriod;
use DateTimeZone;

/**
 * Apply weekday restrictions (e.g. 'sunday') to existing ranges.
 * Trickier than the ordinary numeric operation as the weekday depends on a complete date of year, month, and day.
 *
 * @package wiese\ApproximateDateTime\OptionFilter\Incarnation
 */
class Weekday extends Base
{

    /**
     * {@inheritDoc}
     * @see OptionFilterInterface::__invoke()
     */
    public function __invoke(Ranges $ranges) : Ranges
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
     * Add allowable dates (defined by) $options from $range to $ranges
     *
     * @tutorial We take a detour from DataTimeData to DateTime and back to calculate the weekdays.
     * Weekday should not depend on timezone so we can work w/ a dummy timezone (UTC)
     *
     * @param Ranges $ranges Ranges to append matching ranges to
     * @param Range $range The existing to check for matching weekdays
     * @param array $options Allowable weekdays
     */
    protected function patchRanges(Ranges & $ranges, Range $range, array $options) : void
    {
        $dayIterationInterval = new DateInterval('P1D');
        $timezone = new DateTimeZone('UTC');

        $period = new DatePeriod(
            $range->getStart()->toDateTime($timezone),
            $dayIterationInterval,
            $range->getEnd()->toDateTime($timezone)->add($dayIterationInterval) // work with end day, too
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
            if (in_array($moment->format(DateTimeFormat::WEEKDAY), $options)) {
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
