<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

use DateTime;
use DateTimeZone;
use Exception;

class DateTimeData
{
    /**
     * @var \DateTimeZone
     */
    public $timezone;

    public $y;
    public $m;
    public $d;
    public $h;
    public $i;
    public $s;

    public function __construct(DateTimeZone $timezone)
    {
        $this->timezone = $timezone;
    }

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

    public static function fromDateTime(DateTime $dateTime) : self
    {
        $instance = new self($dateTime->getTimezone());
        $instance->y = (int) $dateTime->format('Y');
        $instance->m = (int) $dateTime->format('m');
        $instance->d = (int) $dateTime->format('d');
        $instance->h = (int) $dateTime->format('h');
        $instance->i = (int) $dateTime->format('i');
        $instance->s = (int) $dateTime->format('s');
        return $instance;
    }

    public function toString()
    {
        return sprintf(
            '%d-%2d-%2dT%2d-%2d-%2d',
            $this->y,
            $this->m,
            $this->d,
            $this->h,
            $this->i,
            $this->s
        );
    }

    public function merge(self $other) : void
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
    }
}
