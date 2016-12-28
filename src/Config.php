<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

class Config
{

    /**
     * @var array
     */
    public static $unitBounds = [
        'y' => [
            'min' => null,
            'max' => null
        ],
        'm' => [
            'min' => 1,
            'max' => 12
        ],
        'd' => [
            'min' => 1,
            'max' => null // dynamically based on y, m, and calendar
        ],
        'n' => [
            'min' => 1,
            'max' => 7
        ],
        'h' => [
            'min' => 0,
            'max' => 23
        ],
        'i' => [
            'min' => 0,
            'max' => 59
        ],
        's' => [
            'min' => 0,
            'max' => 59
        ],
    ];

    /**
     * @var array
     */
    public static $compoundUnits = [
        'y' => 'Numeric',
        'm' => 'Numeric',
        'y-m' => 'Compound',
        'd' => 'Day',
        'm-d' => 'Compound',
        'y-m-d' => 'Compound',
        'n' => 'Weekday',
        'h' => 'Numeric',
        'i' => 'Numeric',
        'h-i' => 'Compound',
        's' => 'Numeric',
        'i-s' => 'Compound',
        'h-i-s' => 'Compound',
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
        return self::$unitBounds[$unit]['min'];
    }

    /**
     * Get the maximum valid value for the given unit
     *
     * @param string $unit
     * @return int|null
     */
    public function getMax(string $unit) : ? int
    {
        return self::$unitBounds[$unit]['max'];
    }
}
