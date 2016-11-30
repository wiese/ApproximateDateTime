<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\OptionFilter;

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
     * @var \wiese\ApproximateDateTime\Config
     */
    protected $config;

    public function __construct()
    {
        $this->config = new Config;
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
     * @param \wiese\ApproximateDateTime\Clues $clues
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
    public function setTimezone(\DateTimeZone $timezone) : self
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
     * @param \wiese\ApproximateDateTime\Ranges $ranges
     * @return \wiese\ApproximateDateTime\Ranges
     */
    abstract public function apply(Ranges $ranges) : Ranges;

    /**
     * Determine the range of allowable values from all clues, and limits
     *
     * @param int $overrideMax
     * @return array
     */
    protected function getAllowableOptions(int $overrideMax = null) : array
    {
        if (is_int($overrideMax)) {
            $max = $overrideMax;
        } else {
            $max = $this->config->getMax($this->unit);
        }

        $gtEq = $this->clues->getAfter($this->unit);
        if (!is_int($gtEq)) {
            $gtEq = $this->config->getMin($this->unit);
        }
        $ltEq = $this->clues->getBefore($this->unit);
        if (!is_int($ltEq)) {
            $ltEq = $max;
        }

        $whitelist = $this->clues->getWhitelist($this->unit);
        if (empty($whitelist)) {
            $options = range($gtEq, $ltEq);
        } else {
            $options = $whitelist;
        }

        $options = array_diff($options, $this->clues->getBlacklist($this->unit));
        $options = array_values($options); // resetting keys to be sequential
        // array_unique?

        return $options;
    }
}
