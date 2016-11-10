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
     /**
      * @var bool Have periods been calculated? To avoid redundant iterations
      */
     protected $periodsCalculated = false;
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
    public function getEarliest() : DateTimeInterface
    {
        $defaultMins = ['y' => $this->defaultYear, 'm' => 1, 'd' => 1, 'h' => 0, 'i' => 0, 's' => 0];
        $mins = ['y' => null, 'm' => null, 'd' => null, 'h' => null, 'i' => null, 's' => null];

        foreach ($mins as $type => & $currentMin) {
            foreach ($this->clues as $clue) {
                if ($clue->type !== $type) {
                    continue;
                }

                if (is_null($currentMin) || $clue->value < $currentMin) {
                    $currentMin = $clue->value;
                }
            }
        }

        foreach ($mins as $type => & $currentMin) {
            if (is_null($currentMin)) {
                $currentMin = $defaultMins[$type];
            }
        }

        extract($mins);

        $str = "${y}-${m}-${d}T${h}:${i}:${s}";

        return new DateTime($str, $this->timezone);
    }

    /**
     * {@inheritDoc}
     * @see \wiese\ApproximateDateTime\ApproximateDateTimeInterface::getLatest()
     */
    public function getLatest() : DateTimeInterface
    {
        $defaultMaxs = ['y' => $this->defaultYear, 'm' => 12, 'd' => null, 'h' => 23, 'i' => 59, 's' => 59];
        $maxs = ['y' => null, 'm' => null, 'd' => null, 'h' => null, 'i' => null, 's' => null];

        foreach ($maxs as $type => & $currentMax) {
            foreach ($this->clues as $clue) {
                if ($clue->type !== $type) {
                    continue;
                }

                if (is_null($currentMax) || $clue->value > $currentMax) {
                    $currentMax = $clue->value;
                }
            }
        }

        foreach ($maxs as $type => & $currentMax) {
            // no intervention needed if we have information
            if (!is_null($currentMax)) {
                continue;
            }

            // Special treatment for the highest day (dynamic) of a month
            // Bit shaky as we rely on this being processed after m & y defaults
            if ($type === 'd') {
                $defaultMaxs[$type] = $this->daysInMonth($maxs['m'], $maxs['y']);
            }

            $currentMax = $defaultMaxs[$type];
        }

        extract($maxs);

        $str = "${y}-${m}-${d}T${h}:${i}:${s}";

        return new DateTime($str, $this->timezone);
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
        if ($this->periodsCalculated) {
            return $this->periods;
        }

        $this->generateFilterListsFromClues();

        $starts = $ends = [];
        foreach ($this->units as $unit => $info) {
            $borders = $this->getBorders($this->whitelist[$unit], $info['min'], $info['max'], $unit);

            $starts = $this->combineValues($starts, $borders['starts']);
            $ends = $this->combineValues($ends, $borders['ends']);
        }

        foreach ($starts as $key => $startInfo) {
            $start = $this->momentToDateTime($startInfo);
            $end = $this->momentToDateTime($ends[$key]);

            $this->periods[] = new DatePeriod($start, $start->diff($end), 1);

            // @todo identify patterns, set recurrences correctly, and avoid redundancy
        }

        $this->periodsCalculated = true;

        return $this->periods;
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

    protected function generateFilterListsFromClues() : bool
    {
        if ($this->whitelist) {
            return false;
        }

        foreach (array_keys($this->units) as $unit) {
            $this->whitelist[$unit] = [];
        }

        foreach ($this->clues as $clue) {
            if ($clue->filter === Clue::FILTER_WHITELIST) {
                $this->whitelist[$clue->type][$clue->value] = $clue->value;
            }
        }

        array_walk($this->whitelist, function(& $value, $key) {
            sort($value);
        });

        // @todo create white list from black list

        return true;
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
