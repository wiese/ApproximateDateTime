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
    const DATE_FORMAT_WEEKDAY = 'N';

    /**
     * @var DateInterval
     */
    protected $dayIterationInterval;

    public function __construct()
    {
        $this->dayIterationInterval = new DateInterval('P1D');
    }

    /**
     * {@inheritDoc}
     * @see \wiese\ApproximateDateTime\OptionFilter\Base::apply()
     */
    public function apply(Ranges $ranges) : Ranges
    {
        $options = $this->getAllowableOptions();

        switch (count($options)) {
            case 7: // all days allowed
                return $ranges;
            case 0: // no days allowed
                return new Ranges();
        }

        $newRanges = new Ranges();
        foreach ($ranges as $range) {
            /**
             * @var \wiese\ApproximateDateTime\Range $range
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
    protected function patchRanges(Ranges & $ranges, Range $range, array $options)
    {
        $period = new DatePeriod(
            $range->getStart()->toDateTime(),
            $this->dayIterationInterval,
            $range->getEnd()->toDateTime()->add($this->dayIterationInterval) // work with end day, too
        );

        $gap = true;
        /**
         * @var \DateTime $previous
         */
        $previous = null;
        /**
         * @var \wiese\ApproximateDateTime\Range $newRange
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
        if ($newRange) { // dangling end
            $newRange->setEnd(DateTimeData::fromDateTime($moment));
        }
    }
}
