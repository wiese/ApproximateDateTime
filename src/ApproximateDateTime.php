<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

use wiese\ApproximateDateTime\OptionFilter\Factory as FilterFactory;
use Psr\Log\LoggerInterface;
use DatePeriod;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

class ApproximateDateTime
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
     * @return self
     */
    public function setTimezone(DateTimeZone $timezone) : self
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
     * @return self
     */
    public function setCalendar(int $calendar) : self
    {
        $this->calendar = $calendar;

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
     * Set the clues to describe the date and time
     *
     * @param Clues $clues
     * @return self
     */
    public function setClues(Clues $clues) : self
    {
        $this->clues = $clues;

        return $this;
    }

    /**
     * @return Clues
     */
    public function getClues() : Clues
    {
        return $this->clues;
    }

    /**
     * Get the first valid moment described by the clues
     *
     * @throws RuntimeException
     *
     * @return null|DateTimeInterface
     */
    public function getEarliest() : ? DateTimeInterface
    {
        $this->calculateBoundaries();

        if (!$this->ranges) {
            return null;
        }

        $earliest = $this->ranges[0]->getStart()->toDateTime($this->timezone);

        return DateTimeImmutable::createFromMutable($earliest);
    }

    /**
     * Get the last valid moment described by the clues
     *
     * @throws RuntimeException
     *
     * @return null|DateTimeInterface
     */
    public function getLatest() : ? DateTimeInterface
    {
        $this->calculateBoundaries();

        if (!$this->ranges) {
            return null;
        }

        $latest = $this->ranges[$this->ranges->count() - 1]->getEnd()->toDateTime($this->timezone);

        return DateTimeImmutable::createFromMutable($latest);
    }

    /**
     * Get all valid periods, i.e. start & interval, matching the clues
     *
     * @throws RuntimeException
     *
     * @return DatePeriod[]
     */
    public function getPeriods() : array
    {
        $this->calculateBoundaries();

        $periods = [];
        foreach ($this->ranges as $range) {
            $start = $range->getStart()->toDateTime($this->timezone);
            $end = $range->getEnd()->toDateTime($this->timezone);
            $periods[] = new DatePeriod(
                DateTimeImmutable::createFromMutable($start),
                $start->diff($end),
                DateTimeImmutable::createFromMutable($end)
            );

            // @todo identify patterns, set recurrences, and avoid redundancy
        }

        return $periods;
    }

    /**
     * Check if the given DateTime is within the allowable range(s) described
     *
     * @throws RuntimeException
     *
     * @param DateTimeInterface $scrutinize
     * @return bool
     */
    public function isPossible(DateTimeInterface $scrutinize) : bool
    {
        $periods = $this->getPeriods();
        foreach ($periods as $period) {
            if ($scrutinize >= $period->getStartDate() && $scrutinize <= $period->getEndDate()) {
                return true;
            }
        }

        return false;
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
