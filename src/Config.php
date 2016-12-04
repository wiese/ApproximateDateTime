<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

class Config
{

    /**
     * @var array
     */
    public static $units = [
        'y' => [
            'filter' => 'Numeric',
            'min' => null,
            'max' => null
        ],
        'm' => [
            'filter' => 'Numeric',
            'min' => 1,
            'max' => 12
        ],
        'd' => [
            'filter' => 'Day',
            'min' => 1,
            'max' => null // dynamically based on y, m, and calendar
        ],
        'n' => [
            'filter' => 'Weekday',
            'min' => 1,
            'max' => 7
        ],
        'h' => [
            'filter' => 'Numeric',
            'min' => 0,
            'max' => 23
        ],
        'i' => [
            'filter' => 'Numeric',
            'min' => 0,
            'max' => 59
        ],
        's' => [
            'filter' => 'Numeric',
            'min' => 0,
            'max' => 59
        ],
    ];

    /**
     * @var array
     */
    public static $compoundUnits = [
        'y' => 'y',
        'm' => 'm',
        'd' => 'd',
        'y-m' => 'm',
        'y-m-d' => 'd',
        'h' => 'h',
        'i' => 'i',
        's' => 's',
        'h-i' => 'i',
        'h-i-s' => 's',
    ];

    public static $logHandler = 'Monolog\\Handler\\NullHandler';
    //public static $logHandler = null;

    /**
     * Get the minimum valid value for the given unit
     *
     * @param string $unit
     * @return int|null
     */
    public function getMin(string $unit) : ? int
    {
        return self::$units[$unit]['min'];
    }

    /**
     * Get the maximum valid value for the given unit
     *
     * @param string $unit
     * @return int|null
     */
    public function getMax(string $unit) : ? int
    {
        return self::$units[$unit]['max'];
    }
}
