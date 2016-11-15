<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\OptionFilter;

use cal_days_in_month;

class Day extends Base
{
    public function apply(array & $starts, array & $ends) : void
    {
        $newStarts = $newEnds = [];
        foreach ($ends as $endkey => $end) {
            $options = $this->getAllowableOptions(
                cal_days_in_month($this->calendar, $end['m'], $end['y'])
            );

            foreach ($options as $key => $value) {
                if (!isset($options[$key - 1]) // first overall
                    || $options[$key - 1] != $value - 1 // first of a block
                ) {
                    $newStarts[] = $starts[$endkey] + [$this->unit => $value];
                }
                if (!isset($options[$key + 1]) // last
                    || $options[$key + 1] != $value + 1 // last of a block
                ) {
                    $newEnds[] = $end + [$this->unit => $value];
                }
            }
        }

        $starts = $newStarts;
        $ends = $newEnds;
    }
}
