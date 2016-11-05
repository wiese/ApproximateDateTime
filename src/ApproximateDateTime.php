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
     * Year to base clues on, if no specific year specified
     *
     * @var integer
     */
    protected $defaultYear;

    /**
     * @var Clue[]
     */
    protected $clues = [];

    protected $whitelist;
    protected $blacklist;
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
        $defaultMins = ['Y' => $this->defaultYear, 'm' => 1, 'd' => 1, 'h' => 0, 'i' => 0, 's' => 0];
        $mins = ['Y' => null, 'm' => null, 'd' => null, 'h' => null, 'i' => null, 's' => null];

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

        $str = "${Y}-${m}-${d}T${h}:${i}:${s}";

        return new DateTime($str, $this->timezone);
    }

    /**
     * {@inheritDoc}
     * @see \wiese\ApproximateDateTime\ApproximateDateTimeInterface::getLatest()
     */
    public function getLatest() : DateTimeInterface
    {
        $defaultMaxs = ['Y' => $this->defaultYear, 'm' => 12, 'd' => null, 'h' => 23, 'i' => 59, 's' => 59];
        $maxs = ['Y' => null, 'm' => null, 'd' => null, 'h' => null, 'i' => null, 's' => null];

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
                $defaultMaxs[$type] = $this->daysInMonth($maxs['m'], $maxs['Y']);
            }

            $currentMax = $defaultMaxs[$type];
        }

        extract($maxs);

        $str = "${Y}-${m}-${d}T${h}:${i}:${s}";

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
        $periods = [];

        $moment = $this->getEarliest();
        $start = clone $moment;

        $step = new DateInterval('PT1S');	// @todo determine biggest possible step
        $i = 0;
        $fronteer = true;
        while ($moment <= $this->getLatest()) {
            if ($this->checkPossible($moment)) {
                $lastPossible = clone $moment;

                if ($fronteer) {
                    $start = clone $moment;
                    $fronteer = false;
                }
            }
            else {
                if ($lastPossible) {
                    $periods[] = new DatePeriod($start, $start->diff($lastPossible), 1);
                }

                $lastPossible = false;
                $fronteer = true;
            }

            $moment->add($step);
            $i++;
        }

        $periods[] = new DatePeriod($start, $start->diff($lastPossible), 1);
var_dump("$i loops");

        return $periods;
    }

    protected function checkPossible(DateTimeInterface $scrutinize) : bool
    {
        $verdict = true;

        $this->generateFilterListsFromClues();

        foreach ($this->whitelist AS $type => $range) {
            $challenge = $scrutinize->format($type);
            if (count($range) && !in_array($challenge, $range)) {
                $verdict = false;
                break;
            }
        }

        return $verdict;
    }

    protected function generateFilterListsFromClues() : bool
    {
        if ($this->whitelist) {
            return false;
        }

        foreach ($this->clues as $clue) {
            if ($clue->filter === Clue::FILTER_WHITELIST) {
                if (!is_array($this->whitelist[$clue->type])) {
                    $this->whitelist[$clue->type] = [];
                }
                $this->whitelist[$clue->type][] = $clue->value;
            }
        }

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
