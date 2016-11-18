<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\OptionFilter;

use wiese\ApproximateDateTime\DateTimeData;
use wiese\ApproximateDateTime\Range;
use wiese\ApproximateDateTime\Ranges;

class Numeric extends Base
{
    public function apply(Ranges $ranges) : Ranges
    {
        $options = $this->getAllowableOptions();

        $newRanges = new Ranges();
        foreach ($options as $key => $value) {
            if (!isset($options[$key - 1]) // first overall
                || $options[$key - 1] != $value - 1 // first of a block
            ) {
                $start = new DateTimeData($this->timezone);
                $start->{$this->unit} = $value;
                $range = new Range();
                $range->setStart($start);
            }
            if (!isset($options[$key + 1]) // last
                || $options[$key + 1] != $value + 1 // last of a block
            ) {
                $end = new DateTimeData($this->timezone);
                $end->{$this->unit} = $value;
                $range->setEnd($end);
                $newRanges->append($range);
            }
        }

        return $ranges->merge($newRanges);
    }
}
