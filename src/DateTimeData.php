<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

use wiese\ApproximateDateTime\Data\DataCarrier;
use wiese\ApproximateDateTime\Data\DateTimeDataAccessors;
use wiese\ApproximateDateTime\Data\Type\DateTimeData as Data;
use wiese\ApproximateDateTime\DateTimeFormat;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use LogicException;

/**
 * Partially known information about a DateTime.
 * Typically information gets added from greater to smaller unit, adding precision.
 */
class DateTimeData
{
    use DataCarrier;
    use DateTimeDataAccessors;

    /**
     * @var Data
     */
    protected $data;

    protected const TO_STRING_FORMAT = '%d-%02d-%02dT%02d:%02d:%02d';

    /**
     * Indicator if the day (d) is the last one in the month (m) & year (y) - to avoid recomputation
     *
     * @todo How can we lose this?
     *
     * @var bool
     */
    public $dayIsLastInMonth = false;

    public function __construct()
    {
        $this->data = new Data();
    }

    /**
     * Convert current information into a DateTime object.
     * Incomplete time values yield 00:00:00
     *
     * @param DateTimeZone $timezone
     * @return DateTimeInterface
     */
    public function toDateTime(DateTimeZone $timezone) : DateTimeInterface
    {
        if (!is_int($this->data->y) || !is_int($this->data->m) || !is_int($this->data->d)) {
            throw new LogicException('DateTime can not be created from incompletely populated DateTimeData.');
        }

        $datetime = new DateTime();
        $datetime->setTimezone($timezone);
        $datetime->setDate($this->data->y, $this->data->m, $this->data->d);
        $datetime->setTime(0, 0, 0);

        if (is_int($this->data->h) && is_int($this->data->i) && is_int($this->data->s)) {
            $datetime->setTime($this->data->h, $this->data->i, $this->data->s);
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
            $this->data->y,
            $this->data->m,
            $this->data->d,
            $this->data->h,
            $this->data->i,
            $this->data->s
        );
    }

    /**
     * Merge data from another DateTimeDate object into the current one
     *
     * @param self $other
     * @return self
     */
    public function merge(self $other) : self
    {
        $units = $other->getUnits();
        foreach ($units as $unit) {
            $value = $other->get($unit);
            if (!is_null($value)) {
                $this->data->{$unit} = $value;
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
        $this->data->y = $year;
        $this->data->m = $month;
        $this->data->d = $day;

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
        $this->data->h = $hour;
        $this->data->i = $minute;
        $this->data->s = $second;

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
        $instance = new self;

        $instance->data->y = (int) $dateTime->format(DateTimeFormat::YEAR);
        $instance->data->m = (int) $dateTime->format(DateTimeFormat::MONTH);
        $instance->data->d = (int) $dateTime->format(DateTimeFormat::DAY);
        $instance->data->h = (int) $dateTime->format(DateTimeFormat::HOUR);
        $instance->data->i = (int) $dateTime->format(DateTimeFormat::MINUTE);
        $instance->data->s = (int) $dateTime->format(DateTimeFormat::SECOND);

        return $instance;
    }

    /**
     * Get the highest level unit with an unset value
     *
     * @return string
     */
    public function getHighestUnit() : ? string
    {
        $units = $this->getUnits();
        $targetUnit = null;
        foreach ($units as $unit) {
            if (is_null($this->data->{$unit})) {
                break;
            }
            $targetUnit = $unit;
        }

        return $targetUnit;
    }
}
