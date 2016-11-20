<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\OptionFilter;

use wiese\ApproximateDateTime\Ranges;
use DateTimeZone;
use wiese\ApproximateDateTime\Clues;

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
    protected $min = null;
    /**
     * @var int
     */
    protected $max = null;
    /**
     * @var int
     */
    protected $calendar;

    /**
     * @var \DateTimeZone
     */
    protected $timezone;

    public function setUnit(string $unit) : self
    {
        $this->unit = $unit;
        return $this;
    }

    public function setClues(Clues $clues) : self
    {
        $this->clues = $clues;
        return $this;
    }

    public function setMin(int $min = null) : self
    {
        $this->min = $min;
        return $this;
    }

    public function setMax(int $max = null) : self
    {
        $this->max = $max;
        return $this;
    }

    public function setCalendar(int $calendar) : self
    {
        $this->calendar = $calendar;
        return $this;
    }

    public function setTimezone(\DateTimeZone $timezone) : self
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * @param int $overrideMax
     * @return array
     */
    public function getAllowableOptions(int $overrideMax = null): array
    {
        if (is_int($overrideMax)) {
            $max = $overrideMax;
        } else {
            $max = $this->max;
        }

        $whitelist = $this->clues->getWhitelist($this->unit);
        if (empty($whitelist)) {
            $options = range($this->min, $max);
        } else {
            $options = $whitelist;
        }

        $options = array_diff($options, $this->clues->getBlacklist($this->unit));
        $options = array_values($options); // resetting keys to be sequential
        // array_unique?

        return $options;
    }

    abstract public function apply(Ranges $ranges) : Ranges;
}
