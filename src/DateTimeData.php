<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

use wiese\ApproximateDateTime\Data\Vehicle;
use wiese\ApproximateDateTime\DateTimeFormat;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Exception;

/**
 * A data vehicle for partially known information about a DateTime.
 * Typically information gets added from greater to smaller unit, adding precision.
 */
class DateTimeData extends Vehicle
{
    const TO_STRING_FORMAT = '%d-%02d-%02dT%02d:%02d:%02d';

    /**
     * Indicator if the day (d) is the last one in the month (m) & year (y) - to avoid recomputation
     *
     * @var bool
     */
    public $dayIsLastInMonth = false;

    /**
     * @var \DateTimeZone
     */
    protected $timezone;

    public function __construct(DateTimeZone $timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * Convert current information into a DateTime object.
     * Incomplete time values yield 00:00:00
     *
     * @return DateTimeInterface
     */
    public function toDateTime() : DateTimeInterface
    {
        if (!is_int($this->y) || !is_int($this->m) || !is_int($this->d)) {
            throw new \LogicException('DateTime can not be created from incompletely populated DateTimeData.');
        }

        $datetime = new DateTime();
        $datetime->setTimezone($this->timezone);
        $datetime->setDate($this->y, $this->m, $this->d);
        $datetime->setTime(0, 0, 0);

        if (is_int($this->h) && is_int($this->i) && is_int($this->s)) {
            $datetime->setTime($this->h, $this->i, $this->s);
        }

        return $datetime;
    }

    /**
     * Output current information as string, e.g. for sorting - ain't beautiful
     *
     * @return string
     */
    public function toString() : string
    {
        return sprintf(
            self::TO_STRING_FORMAT,
            $this->y,
            $this->m,
            $this->d,
            $this->h,
            $this->i,
            $this->s
        );
    }

    /**
     * Merge data from another DateTimeDate object into the current one
     *
     * @throws \Exception
     * @param self $other
     * @return self
     */
    public function merge(self $other) : self
    {
        $properties = get_object_vars($other);
        foreach ($properties as $property => $value) {
            if ($property === 'dayIsLastInMonth') {
                continue;
            } elseif ($property === 'timezone') {
                if ($value->getName() !== $this->timezone->getName()) {
                    throw new Exception('Can not merge DateTimeData objects w/ different timezones!');
                }
                continue;
            }

            if (!is_null($value)) {
                $this->{$property} = $value;
            }
        }

        return $this;
    }

    /**
     * Set the date information part to a different date
     *
     * @param int $year
     * @param int $month
     * @param int $day
     * @return DateTimeData
     */
    public function setDate(int $year, int $month, int $day) : self
    {
        $this->y = $year;
        $this->m = $month;
        $this->d = $day;

        return $this;
    }

    /**
     * Set the time information part to a different time
     *
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @return DateTimeData
     */
    public function setTime(int $hour, int $minute, int $second) : self
    {
        $this->h = $hour;
        $this->i = $minute;
        $this->s = $second;

        return $this;
    }

    /**
     * Create an instance from the information contained in a DateTime object
     *
     * @param \DateTimeInterface $dateTime
     * @return self
     */
    public static function fromDateTime(DateTimeInterface $dateTime) : self
    {
        $instance = new self($dateTime->getTimezone());

        $instance->y = (int) $dateTime->format(DateTimeFormat::YEAR);
        $instance->m = (int) $dateTime->format(DateTimeFormat::MONTH);
        $instance->d = (int) $dateTime->format(DateTimeFormat::DAY);
        $instance->h = (int) $dateTime->format(DateTimeFormat::HOUR);
        $instance->i = (int) $dateTime->format(DateTimeFormat::MINUTE);
        $instance->s = (int) $dateTime->format(DateTimeFormat::SECOND);

        return $instance;
    }

    /**
     * Get the highest level unit with an unset value
     *
     * @return string
     */
    public function getNextUnit() : string
    {
        $targetUnit = null;
        $properties = get_object_vars($this);
        foreach ($properties as $unit => $value) {
            if (is_null($value)) {
                break;
            }
            $targetUnit = $unit;
        }

        return $targetUnit;
    }
}
