<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

use wiese\ApproximateDateTime\Clues;
use DateInterval;
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
     * @return self
     */
    public function setTimezone(DateTimeZone $timezone) : self
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Set the default year used when no respective clue given
     *
     * @param int $year
     * @return self
     */
    public function setDefaultYear(int $year) : self
    {
        $this->clues->setDefaultYear($year);

        return $this;
    }

    /**
     * Set the clues to digest
     *
     * @param array $clues
     * @return self
     */
    public function setClues(array $clues) : self
    {
        $this->clues = Clues::fromArray($clues);

        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \wiese\ApproximateDateTime\ApproximateDateTimeInterface::getEarliest()
     */
    public function getEarliest() : ? DateTimeInterface
    {
        $this->calculateBoundaries();

        return $this->ranges[0]->getStart()->toDateTime();
    }

    /**
     * {@inheritDoc}
     * @see \wiese\ApproximateDateTime\ApproximateDateTimeInterface::getLatest()
     */
    public function getLatest() : ? DateTimeInterface
    {
        $this->calculateBoundaries();

        return $this->ranges[$this->ranges->count() - 1]->getEnd()->toDateTime();
    }

    /**
     * {@inheritDoc}
     * @see \wiese\ApproximateDateTime\ApproximateDateTimeInterface::getInterval()
     */
    public function getInterval() : DateInterval
    {
        $diff = $this->getEarliest()->diff($this->getLatest());

        return $diff;
    }

    /**
     * {@inheritDoc}
     * @see \wiese\ApproximateDateTime\ApproximateDateTimeInterface::getPeriods()
     */
    public function getPeriods() : array
    {
        $this->calculateBoundaries();

        $periods = [];
        foreach ($this->ranges as $range) {
            $start = $range->getStart()->toDateTime();
            $end = $range->getEnd()->toDateTime();
            $periods[] = new DatePeriod($start, $start->diff($end), 1);

            // @todo identify patterns, set recurrences correctly, and avoid redundancy
        }

        return $periods;
    }

    /**
     * @todo So far only works with one single, consecutive interval
     *
     * {@inheritDoc}
     * @see \wiese\ApproximateDateTime\ApproximateDateTimeInterface::isPossible()
     */
    public function isPossible(DateTimeInterface $scrutinize) : bool
    {
        $verdict = ($scrutinize >= $this->getEarliest() && $scrutinize <= $this->getLatest());

        return $verdict;
    }

    /**
     * {@inheritDoc}
     * @see \wiese\ApproximateDateTime\ApproximateDateTimeInterface::getLuckyShot()
     */
    public function getLuckyShot() : DateTimeInterface
    {
        return $this->getEarliest();
    }

    protected function calculateBoundaries() : void
    {
        $ranges = new Ranges();
        foreach (Config::$units as $unit => $settings) {
            $filter = OptionFilter\Base::fromName($settings['filter']);
            $filter->setUnit($unit);
            $filter->setClues($this->clues);
            $filter->setCalendar($this->calendar);
            $filter->setTimezone($this->timezone);

            $ranges = $filter->apply($ranges);
        }

        // @todo remove specific times (compound units)
        // @todo what about time lost/inexisting due to daylight saving time?

        // @fixme sort by range start - do we have to?
        //sort($ranges);

        $this->ranges = $ranges;
    }
}
