<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\OptionFilter;

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

    abstract public function apply(array & $starts, array & $ends) : void;
}
