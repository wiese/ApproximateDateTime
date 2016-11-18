<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\OptionFilter;

use wiese\ApproximateDateTime\Ranges;
use DateTimeZone;

abstract class Base
{
    /**
     * @var string
     */
    protected $unit;
    /**
     * @var array
     */
    protected $whitelist = [];
    /**
     * @var array
     */
    protected $blacklist = [];
    /**
     * @var int
     */
    protected $min = null;
    /**
     * @var int
     */
    protected $max = null;

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

    public function setWhitelist(array $whitelist) : self
    {
        $this->whitelist = $whitelist;
        return $this;
    }

    public function setBlacklist(array $blacklist) : self
    {
        $this->blacklist = $blacklist;
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

        if (empty($this->whitelist)) {
            $options = range($this->min, $max);
        } else {
            $options = $this->whitelist;
        }

        $options = array_diff($options, $this->blacklist);
        $options = array_values($options); // resetting keys to be sequential
        // array_unique?

        return $options;
    }

    abstract public function apply(Ranges $ranges) : Ranges;
}
