<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\OptionFilter;

use wiese\ApproximateDateTime\ApproximateDateTime;
use wiese\ApproximateDateTime\Clues;
use wiese\ApproximateDateTime\Config;
use wiese\ApproximateDateTime\Ranges;
use DateTimeZone;

abstract class Base
{

    /**
     * @var string
     */
    protected $unit;

    /**
     * @var Clues
     */
    protected $clues;

    /**
     * @var int
     */
    protected $calendar;

    /**
     * @var \DateTimeZone
     */
    protected $timezone;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var LoggerInterface
     */
    protected $log;

    public function __construct()
    {
        $this->config = new Config;
        $this->log = ApproximateDateTime::getLog();
    }

    /**
     * Set the unit the OptionFilter is supposed to be working on
     *
     * @param string $unit
     * @return self
     */
    public function setUnit(string $unit) : self
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * Set the clues to be used for the restriction of ranges
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
     * Set the calendar to be used for date calculations
     *
     * @param int $calendar
     * @return self
     */
    public function setCalendar(int $calendar) : self
    {
        $this->calendar = $calendar;

        return $this;
    }

    /**
     * Set the timezone to be used for date/time calculations
     *
     * @param \DateTimeZone $timezone
     * @return self
     */
    public function setTimezone(DateTimeZone $timezone) : self
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Instantiate a concrete OptionFilter by name
     *
     * @param string $name
     * @return self
     */
    public static function fromName(string $name) : self
    {
        $className = __NAMESPACE__ . '\\' . $name;

        return new $className();
    }

    /**
     * Mend the given ranges as per the restrictions defined through clues
     *
     * @param Ranges $ranges
     * @return Ranges
     */
    abstract public function apply(Ranges $ranges) : Ranges;

    /**
     * Determine the range of allowable values from all clues, and limits
     *
     * @param int $overrideMax
     * @return int[]
     */
    protected function getAllowableOptions(int $overrideMax = null) : array
    {
        $this->log->debug('getAllowableOptions', [$this->unit, $overrideMax]);

        if (is_int($overrideMax)) {
            $max = $overrideMax;
        } else {
            $max = $this->config->getMax($this->unit);
        }

        $min = $this->config->getMin($this->unit);
        $ltEq = $this->clues->getBefore($this->unit);
        $gtEq = $this->clues->getAfter($this->unit);

        $this->log->debug('bounds', [$min, $max, $ltEq, $gtEq]);

        $options = $this->clues->getWhitelist($this->unit);

        if (is_int($min) && is_int($max)) { // y does not know extremes
            $minToMax = range($min, $max);

            if (empty($options)) {
                $options = $minToMax;
            } else {
                $options = array_intersect($options, $minToMax);
            }
        }

        $validPerBeforeAfter = [];
        if ($ltEq) {
            $validPerBeforeAfter = array_merge($validPerBeforeAfter, range($min, $ltEq));
        }
        if ($gtEq) {
            $validPerBeforeAfter = array_merge($validPerBeforeAfter, range($gtEq, $max));
        }

        if ($validPerBeforeAfter) {
            $options = array_intersect($options, $validPerBeforeAfter);
        }

        $options = array_diff($options, $this->clues->getBlacklist($this->unit));
        $options = array_values($options); // resetting keys to be sequential
        // array_unique?

        $this->log->debug('options ' . $this->unit, [$options]);

        return $options;
    }
}
