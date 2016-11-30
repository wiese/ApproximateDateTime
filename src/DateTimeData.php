<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Exception;

/**
 * A data vehicle for partially known information about a DateTime.
 * Typically information gets added from greater to smally unit, adding precision.
 */
class DateTimeData
{
    const FORMAT_YEAR = 'Y';
    const FORMAT_MONTH = 'm';
    const FORMAT_DAY = 'd';
    const FORMAT_HOUR = 'h';
    const FORMAT_MINUTE = 'i';
    const FORMAT_SECOND = 's';

    const TO_STRING_FORMAT = '%d-%02d-%02dT%02d:%02d:%02d';

    /**
     * Year
     *
     * @var int|null
     */
    public $y;

    /**
     * Month
     *
     * @var int|null
     */
    public $m;

    /**
     * Day
     *
     * @var int|null
     */
    public $d;

    /**
     * Hour
     *
     * @var int|null
     */
    public $h;

    /**
     * Minute
     *
     * @var int|null
     */
    public $i;

    /**
     * Second
     *
     * @var int|null
     */
    public $s;

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
     * Unset time values yield 00:00:00
     *
     * @return \DateTime
     */
    public function toDateTime() : DateTime
    {
        $datetime = new DateTime();
        $datetime->setTimezone($this->timezone);
        $datetime->setDate($this->y, $this->m, $this->d);

        if (isset($this->h)) {
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
        foreach ($other as $property => $value) {
            if ($property === 'timezone') {
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
     * Create an instance from the information contained in a DateTime object
     *
     * @param \DateTimeInterface $dateTime
     * @return self
     */
    public static function fromDateTime(DateTimeInterface $dateTime) : self
    {
        $instance = new self($dateTime->getTimezone());

        $instance->y = (int) $dateTime->format(self::FORMAT_YEAR);
        $instance->m = (int) $dateTime->format(self::FORMAT_MONTH);
        $instance->d = (int) $dateTime->format(self::FORMAT_DAY);
        $instance->h = (int) $dateTime->format(self::FORMAT_HOUR);
        $instance->i = (int) $dateTime->format(self::FORMAT_MINUTE);
        $instance->s = (int) $dateTime->format(self::FORMAT_SECOND);

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
        // determine highest level non-set unit
        foreach ($this as $unit => $value) {
            if (is_null($value)) {
                break;
            }
            $targetUnit = $unit;
        }

        return $targetUnit;
    }
}
