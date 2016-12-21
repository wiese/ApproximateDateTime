<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\OptionFilter;

use wiese\ApproximateDateTime\Clues;
use wiese\ApproximateDateTime\Config;
use wiese\ApproximateDateTime\Log;
use wiese\ApproximateDateTime\Ranges;
use DateTimeZone;

abstract class Base implements OptionFilterInterface
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
        $this->log = Log::get();
    }

    /**
     * {@inheritDoc}
     * @see OptionFilterInterface::setUnit()
     */
    public function setUnit(string $unit) : void
    {
        $this->unit = $unit;
    }

    /**
     * {@inheritDoc}
     * @see OptionFilterInterface::setClues()
     */
    public function setClues(Clues $clues) : void
    {
        $this->clues = $clues;
    }

    /**
     * {@inheritDoc}
     * @see OptionFilterInterface::setCalendar()
     */
    public function setCalendar(int $calendar) : void
    {
        $this->calendar = $calendar;
    }

    /**
     * {@inheritDoc}
     * @see OptionFilterInterface::setTimezone()
     */
    public function setTimezone(DateTimeZone $timezone) : void
    {
        $this->timezone = $timezone;
    }

    /**
     * {@inheritDoc}
     * @see OptionFilterInterface::apply()
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

        $max = is_int($overrideMax) ? $overrideMax : $this->config->getMax($this->unit);
        $min = $this->config->getMin($this->unit);
        $ltEq = $this->clues->getBefore($this->unit);
        $gtEq = $this->clues->getAfter($this->unit);

        $this->log->debug('bounds', [$min, $max, $gtEq, $ltEq]);

        $options = $this->clues->getWhitelist($this->unit);

        if (is_int($min) && is_int($max)) { // e.g. y does not know extremes
            $minToMax = range($min, $max);

            if (empty($options)) {
                $options = $minToMax;
            } else {
                $options = array_intersect($options, $minToMax);
            }
        }

        $validPerBeforeAfter = [];

        if (is_int($gtEq) && is_int($ltEq) && $gtEq < $ltEq) {
            $validPerBeforeAfter = range($gtEq, $ltEq);
        } else {
            if (is_int($gtEq)) {
                $validPerBeforeAfter = array_unique(array_merge($validPerBeforeAfter, range($gtEq, $max)));
            }
            if (is_int($ltEq)) {
                $validPerBeforeAfter = array_unique(array_merge($validPerBeforeAfter, range($min, $ltEq)));
            }
        }

        $this->log->debug('validPerBeforeAfter', [$validPerBeforeAfter]);

        if (!empty($validPerBeforeAfter)) {
            $options = array_intersect($options, $validPerBeforeAfter);
        }

        $options = array_diff($options, $this->clues->getBlacklist($this->unit));
        $options = array_values($options); // resetting keys to be sequential
        // array_unique?

        $this->log->debug('options ' . $this->unit, [$options]);

        return $options;
    }
}
