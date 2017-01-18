<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

use wiese\ApproximateDateTime\OptionFilter\Factory as FilterFactory;
use wiese\ApproximateDateTime\Log;
use DatePeriod;
use DateTimeInterface;
use DateTimeZone;

class ApproximateDateTime implements ApproximateDateTimeInterface
{
    const DEFAULT_TIMEZONE = 'UTC';

    /**
     * Timezone to use
     *
     * @var \DateTimeZone
     */
    protected $timezone;

    /**
     * Calendar to base date calculation on
     *
     * @var int
     */
    protected $calendar = CAL_GREGORIAN;

    /**
     * @var Clues
     */
    protected $clues;

    /**
     * Calculated matching ranges
     *
     * @var Ranges
     */
    protected $ranges;

    /**
     * @param string $timezone
     */
    public function __construct(string $timezone = self::DEFAULT_TIMEZONE)
    {
        $this->setTimezone(new DateTimeZone($timezone));
        $this->setClues([]);
    }

    /**
     * @return \DateTimeZone
     */
    public function getTimezone() : DateTimeZone
    {
        return $this->timezone;
    }

    /**
     * @param \DateTimeZone $timezone
     * @return ApproximateDateTimeInterface
     */
    public function setTimezone(DateTimeZone $timezone) : ApproximateDateTimeInterface
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Set the default year used when no respective clue given
     *
     * @param int $year
     * @return ApproximateDateTimeInterface
     */
    public function setDefaultYear(int $year) : ApproximateDateTimeInterface
    {
        $this->clues->setDefaultYear($year);

        return $this;
    }

    /**
     * Set the clues to digest
     *
     * @fixme This is for prototyping only. Should probably take Clues object or solved differently altogether
     *
     * @param array $clues
     * @return ApproximateDateTimeInterface
     */
    public function setClues(array $clues) : ApproximateDateTimeInterface
    {
        $this->clues = Clues::fromArray($clues);

        return $this;
    }

    public function getClues(): Clues
    {
        return $this->clues;
    }

    /**
     * {@inheritDoc}
     * @see ApproximateDateTimeInterface::getEarliest()
     */
    public function getEarliest() : ? DateTimeInterface
    {
        $this->calculateBoundaries();

        return $this->ranges[0]->getStart()->toDateTime($this->timezone);
    }

    /**
     * {@inheritDoc}
     * @see ApproximateDateTimeInterface::getLatest()
     */
    public function getLatest() : ? DateTimeInterface
    {
        $this->calculateBoundaries();

        return $this->ranges[$this->ranges->count() - 1]->getEnd()->toDateTime($this->timezone);
    }

    /**
     * {@inheritDoc}
     * @see ApproximateDateTimeInterface::getPeriods()
     */
    public function getPeriods() : array
    {
        $this->calculateBoundaries();

        $periods = [];
        foreach ($this->ranges as $range) {
            $start = $range->getStart()->toDateTime($this->timezone);
            $end = $range->getEnd()->toDateTime($this->timezone);
            $periods[] = new DatePeriod($start, $start->diff($end), 1);

            // @todo identify patterns, set recurrences correctly, and avoid redundancy
        }

        return $periods;
    }

    /**
     * @todo So far only works with one single, consecutive interval
     *
     * {@inheritDoc}
     * @see ApproximateDateTimeInterface::isPossible()
     */
    public function isPossible(DateTimeInterface $scrutinize) : bool
    {
        $verdict = ($scrutinize >= $this->getEarliest() && $scrutinize <= $this->getLatest());

        return $verdict;
    }

    /**
     * {@inheritDoc}
     * @see ApproximateDateTimeInterface::getLuckyShot()
     */
    public function getLuckyShot() : DateTimeInterface
    {
        return $this->getEarliest();
    }

    /**
     * Use all filters to calculate the ranges
     */
    protected function calculateBoundaries() : void
    {
        $ranges = new Ranges();

        $filterFactory = new FilterFactory;
        foreach (Config::$compoundUnits as $unit => $filter) {
            $filter = $filterFactory->produce($filter);
            $filter->setUnit($unit);
            $filter->setClues($this->clues);
            $filter->setCalendar($this->calendar);

            $ranges = $filter($ranges);

            Log::get()->debug('+++ ' . $unit . ' complete. ranges:', [count($ranges)]);
        }

        // @todo remove specific times (compound units)
        // @todo what about time lost/inexisting due to daylight saving time?

        // @fixme sort by range start - do we have to?
        //sort($ranges);

        $this->ranges = $ranges;
    }
}
