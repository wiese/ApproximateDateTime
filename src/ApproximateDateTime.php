<?php
declare(strict_types=1);

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

    protected $units = [
        'y' => [
            'min' => null,
            'max' => null
        ],
        'm' => [
            'min' => 1,
            'max' => 12
        ],
        'd' => [
            'min' => 1,
            'max' => null	// depends on y, m, and the calendar
        ],
        'h' => [
            'min' => 0,
            'max' => 23
        ],
        'i' => [
            'min' => 0,
            'max' => 59
        ],
        's' => [
            'min' => 0,
            'max' => 59
        ],
    ];

    /**
     * caches
     */

     /**
      * @var array Combined clue information on whitelisted dates
      */
     protected $whitelist = [];

     protected $blacklist = [];

     /**
      * @var array Periods compatible with given clues
      */
     protected $periods = [];
     protected $starts = [];
     protected $ends = [];
/*
    [
        'y' => [],
        'm' => [],
        'd' => [],
        'y-m' => [],
        'y-m-d' => [],
        'h' => [],
        'i' => [],
        's' => [],
        'h-i' => [],
        'h-i-s' => [],
    ]

    '1980-??-??T??-??-??'
    '????-08-??T??-??-??'
    '????-??-17T??-??-??'
    '????-05-23T??-??-??'
    '1993-05-23T??-??-??'
    '????-??-??T14-??-??'
    '????-??-??T??-21-??'
    '????-??-??T??-??-57'
    '????-??-??T11-53'
    '????-??-??T17-12-42'
*/
    /**
     * @param string $timezone
     */
    public function __construct(string $timezone = self::DEFAULT_TIMEZONE)
    {
        $this->setTimezone(new DateTimeZone($timezone));
        $this->setDefaultYear((int)(new DateTime())->format('Y'));
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

        sort($this->starts);

        return $this->starts ? $this->starts[0] : null;
    }

    /**
     * {@inheritDoc}
     * @see \wiese\ApproximateDateTime\ApproximateDateTimeInterface::getLatest()
     */
    public function getLatest() : ? DateTimeInterface
    {
        $this->calculateBoundaries();

        sort($this->ends);

        return $this->ends ? end($this->ends) : null;
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
        foreach ($this->starts as $key => $start) {
            $this->periods[] = new DatePeriod($start, $start->diff($this->ends[$key]), 1);

            // @todo identify patterns, set recurrences correctly, and avoid redundancy
        }

        return $this->periods;
    }

    protected function calculateBoundaries() : void
    {
        $this->generateFilterListsFromClues();

        $starts = $ends = [];
        foreach ($this->units as $unit => $info) {
            $borders = $this->getBorders($this->whitelist[$unit], $info['min'], $info['max'], $unit);

            $starts = $this->combineValues($starts, $borders['starts']);
            $ends = $this->combineValues($ends, $borders['ends']);
        }

        $this->starts = [];
        foreach ($starts as $start) {
            $this->starts[] = $this->momentToDateTime($start);
        }

        $this->ends = [];
        foreach ($ends as $end) {
            $this->ends[] = $this->momentToDateTime($end);
        }
    }

    protected function momentToDateTime(array $moment) : DateTime
    {
        if (is_null($moment['d'])) {
            $moment['d'] = $this->daysInMonth($moment['m'], $moment['y']);
        }

        $datetime = new DateTime();
        $datetime->setTimezone($this->timezone);
        $datetime->setDate($moment['y'], $moment['m'], $moment['d']);
        $datetime->setTime($moment['h'], $moment['i'], $moment['s']);

        return $datetime;
    }

    protected function combineValues(array $array1, array $array2)
    {
        $combined = [];
        foreach ($array1 as $value1) {
            foreach ($array2 as $value2) {
                $combined[] = $value1 + $value2;
            }
        }

        if (empty($array1)) {
            $combined = $array2;
        }

        return $combined;
    }

    protected function getBorders(array $array, int $defaultMin = null, int $defaultMax = null, string $unit) : array
    {
        $starts = [];
        $ends = [];

        if (empty($array)) {
            $starts[] = [$unit => $defaultMin];
            $ends[] = [$unit => $defaultMax];
        }
        else {
            $previous = null;
            foreach ($array as $key => $value) {
                if (is_null($previous) || $array[$key - 1 ] != $value - 1) {
                    $starts[] = [$unit => $value];
                    $previous = $value;
                }
                if ($array[$key + 1 ] != $value + 1) {
                    $ends[] = [$unit => $value];
                }
            }
        }

        return [
            'starts' => $starts,
            'ends' => $ends
        ];
    }

    protected function checkPossible(DateTimeInterface $moment) : bool
    {
        $this->generateFilterListsFromClues();

        foreach ($this->whitelist AS $type => $range) {
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
        }

        foreach ($this->clues as $clue) {
            if ($clue->filter === Clue::FILTER_WHITELIST) {
                $this->whitelist[$clue->type][] = $clue->value;
            }
        }

        if (empty($this->whitelist['y'])) {
            $this->whitelist['y'][] = $this->defaultYear;
        }

        array_walk($this->whitelist, function(& $value, $key) {
            sort($value);
        });

        // @todo create white list from black list
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

    /**
     * Get number of days in the month of this year
     *
     * @param int $month
     * @param int $year
     * @return int
     */
    protected function daysInMonth($month, $year) : int
    {
        // calculate number of days in a month
        return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);

        // @todo Cheap polyfill. Use actual function, declare dependency on cal
        // \cal_days_in_month($this->calendar, $month, $year);
    }
}
