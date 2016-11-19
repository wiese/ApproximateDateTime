<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

use DateTimeInterface;
use DateTime;
use DateInterval;
use DatePeriod;
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
     * @var integer
     */
    protected $calendar = CAL_GREGORIAN;

    /**
     * Year to base clues on, if no year specified
     *
     * @var integer
     */
    protected $defaultYear;

    /**
     * @var Clue[]
     */
    protected $clues = [];

    /**
     * @var array
     */
    protected $compoundUnits = [
        'y' => 'y',
        'm' => 'm',
        'd' => 'd',
        'y-m' => 'm',
        'y-m-d' => 'd',
        'h' => 'h',
        'i' => 'i',
        's' => 's',
        'h-i' => 'i',
        'h-i-s' => 's',
    ];

    /**
     * @var array
     */
    protected $units = [
        'y' => [
            'filter' => 'Numeric',
            'min' => null,
            'max' => null
        ],
        'm' => [
            'filter' => 'Numeric',
            'min' => 1,
            'max' => 12
        ],
        'd' => [
            'filter' => 'Day',
            'min' => 1,
            'max' => null // dynamic based on y, m, and calendar
        ],
        'n' => [
            'filter' => 'Weekday',
            'min' => 1,
            'max' => 7
        ],
        'h' => [
            'filter' => 'Numeric',
            'min' => 0,
            'max' => 23
        ],
        'i' => [
            'filter' => 'Numeric',
            'min' => 0,
            'max' => 59
        ],
        's' => [
            'filter' => 'Numeric',
            'min' => 0,
            'max' => 59
        ],
    ];

    /**
     * @var array Combined clue information on whitelisted dates
     */
    protected $whitelist = [];

    /**
     * @var array Combined clue information on blacklisted dates
     */
    protected $blacklist = [];

    /**
     * @var array Periods compatible with given clues
     */
    protected $periods = [];

    /**
     * @var array
     */
    protected $starts = [];

    /**
     * @var array
     */
    protected $ends = [];

    /**
     * @param string $timezone
     */
    public function __construct(string $timezone = self::DEFAULT_TIMEZONE)
    {
        $this->setTimezone(new DateTimeZone($timezone));
        $this->setDefaultYear((int) (new DateTime())->format('Y'));
    }

    /**
     * @return \DateTimeZone
     */
    public function getTimezone() : DateTimeZone
    {
        return $this->timezone;
    }

    /**
     * @return \DateTimeZone
     */
    public function setTimezone(DateTimeZone $timezone) : self
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Get the default year used when no respective clue given
     *
     * @return int
     */
    public function getDefaultYear() : int
    {
        return $this->defaultYear;
    }

    /**
     * Set the default year used when no respective clue given
     *
     * @param int $year
     * @return self
     */
    public function setDefaultYear(int $year) : self
    {
        $this->defaultYear = $year;

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
        $this->clues = $clues;

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

        $this->periods = [];
        foreach ($this->ranges as $range) {
            $start = $range->getStart()->toDateTime();
            $end = $range->getEnd()->toDateTime();
            $this->periods[] = new DatePeriod($start, $start->diff($end), 1);

            // @todo identify patterns, set recurrences correctly, and avoid redundancy
        }

        return $this->periods;
    }

    /**
     * @todo So far only works with one single, consecutive interval
     *
     * {@inheritDoc}
     * @see \wiese\ApproximateDateTime\ApproximateDateTimeInterface::isPossible()
     */
    public function isPossible(DateTimeInterface $scrutinize) : bool
    {
        $verdict = false;

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
        $this->generateFilterListsFromClues();

        $starts = $ends = [];
        $ranges = new Ranges();
        foreach ($this->units as $unit => $settings) {
            $className = __NAMESPACE__ . '\\OptionFilter\\' . $settings['filter'];
            /**
             * @var OptionFilter\Base $filter
             */
            $filter = new $className;
            $filter->setUnit($unit);
            $filter->setWhitelist($this->whitelist[$unit]);
            $filter->setBlacklist($this->blacklist[$unit]);
            $filter->setMin($settings['min']);
            $filter->setMax($settings['max']);
            $filter->setCalendar($this->calendar);
            $filter->setTimezone($this->timezone);

            $ranges = $filter->apply($ranges);
        }

        // @todo remove specific times (compound units)
        // @todo what about time lost/inexisting due to daylight saving time?

        // @fixme sort by range start
        //sort($ranges);

        $this->ranges = $ranges;
    }

    protected function checkPossible(DateTimeInterface $moment) : bool
    {
        $this->generateFilterListsFromClues();

        foreach ($this->whitelist as $type => $range) {
            if (count($range) && !in_array($moment->format($type), $range)) {
                return false;
            }
        }

        return true;
    }

    protected function generateFilterListsFromClues() : void
    {
        $this->whitelist = [];

        foreach (array_keys($this->units) as $unit) {
            $this->whitelist[$unit] = [];
            $this->blacklist[$unit] = [];
        }

        foreach ($this->clues as $clue) {
            // @todo validate value

            switch ($clue->filter) {
                case Clue::FILTER_WHITELIST:
                    $this->whitelist[$clue->type][] = $clue->value;
                    break;
                case Clue::FILTER_BLACKLIST:
                    $this->blacklist[$clue->type][] = $clue->value;
                    break;
            }
        }

        $sanitizeArray = function (& $value, $key) {
            array_unique($value, SORT_REGULAR);
            sort($value); // list in order of values
        };

        array_walk($this->whitelist, $sanitizeArray);
        array_walk($this->blacklist, $sanitizeArray);

        if (empty($this->whitelist['y'])) {
            $this->whitelist['y'][] = $this->defaultYear;
        }
    }
}
