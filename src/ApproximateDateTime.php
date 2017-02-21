<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

use Psr\Log\LoggerInterface;
use wiese\ApproximateDateTime\OptionFilter\Factory as FilterFactory;
use DatePeriod;
use DateTimeInterface;
use DateTimeZone;

class ApproximateDateTime implements ApproximateDateTimeInterface
{

    /**
     * Timezone to use
     *
     * @var DateTimeZone
     */
    protected $timezone;

    /**
     * Calendar to base date calculation on
     *
     * @var int
     */
    protected $calendar;

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
     * @var Config
     */
    protected $config;

    /**
     * @var LoggerInterface
     */
    protected $log;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @param string $timezone
     * @param int    $calendar
     */
    public function __construct(? string $timezone = null, ? int $calendar = null)
    {
        $this->manager = new Manager();

        $this->config = $this->manager->config;
        $this->log = $this->manager->log;

        if (is_null($timezone)) {
            $this->setTimezone(new DateTimeZone($this->config->defaultTimezone));
        }
        if (is_null($calendar)) {
            $this->setCalendar($this->config->defaultCalendar);
        }

        $this->setClues(new Clues());
    }

    /**
     * @return \DateTimeZone
     */
    public function getTimezone() : DateTimeZone
    {
        return $this->timezone;
    }

    /**
     * @param DateTimeZone $timezone
     * @return ApproximateDateTimeInterface
     */
    public function setTimezone(DateTimeZone $timezone) : ApproximateDateTimeInterface
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * @return int
     */
    public function getCalendar() : int
    {
        return $this->calendar;
    }

    /**
     * @param int $calendar
     * @return ApproximateDateTimeInterface
     */
    public function setCalendar(int $calendar) : ApproximateDateTimeInterface
    {
        $this->calendar = $calendar;

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
     * @param Clues $clues
     * @return ApproximateDateTimeInterface
     */
    public function setClues(Clues $clues) : ApproximateDateTimeInterface
    {
        $this->clues = $clues;

        return $this;
    }

    public function getClues() : Clues
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
     * Use all filters to calculate the ranges
     */
    protected function calculateBoundaries() : void
    {
        $ranges = new Ranges();

        $filterFactory = new FilterFactory($this->manager);
        foreach ($this->config->units as $unit => $filter) {
            $this->log->debug('+++ ' . $unit);

            $filter = $filterFactory->produce($filter);
            $filter->setUnit($unit);
            $filter->setClues($this->clues);
            $filter->setCalendar($this->calendar);

            $ranges = $filter($ranges);

            $this->log->debug('resulting ranges', [count($ranges)]);
        }

        // @todo remove specific times (compound units)
        // @todo what about time lost/inexisting due to daylight saving time?

        // @fixme sort by range start - do we have to?
        //sort($ranges);

        $this->ranges = $ranges;
    }
}
