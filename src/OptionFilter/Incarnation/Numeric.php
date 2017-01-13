<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\OptionFilter\Incarnation;

use wiese\ApproximateDateTime\OptionFilter\Base;
use wiese\ApproximateDateTime\DateTimeData;
use wiese\ApproximateDateTime\Range;
use wiese\ApproximateDateTime\Ranges;

/**
 * Apply restrictions to existing ranges that can by expressed by static numbers, e.g. 'month 2' is allowed.
 * Is repeatedly applied to ranges for all units that do not have ticks, like the dynamic number of days, or
 * date-dependency of a weekday.
 *
 * @package wiese\ApproximateDateTime\OptionFilter\Incarnation
 */
class Numeric extends Base
{
    /**
     * {@inheritDoc}
     * @see Base::apply()
     */
    public function apply(Ranges $ranges) : Ranges
    {
        $options = $this->getAllowableOptions();

        $newRanges = new Ranges();
        foreach ($options as $key => $value) {
            if (!isset($options[$key - 1]) // first overall
                || $options[$key - 1] != $value - 1 // first of a block
            ) {
                $start = new DateTimeData();
                $start->set($this->unit, $value);
                $range = new Range();
                $range->setStart($start);
            }
            if (!isset($options[$key + 1]) // last
                || $options[$key + 1] != $value + 1 // last of a block
            ) {
                $end = new DateTimeData();
                $end->set($this->unit, $value);
                $range->setEnd($end);
                $newRanges->append($range);
            }
        }

        return $ranges->merge($newRanges);
    }
}
