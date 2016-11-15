<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\OptionFilter;

class Day extends Base
{
    public function apply(array & $starts, array & $ends) : void
    {
        $newStarts = $newEnds = [];
        foreach ($ends as $endkey => $end) {
            if (empty($this->whitelist)) {
                $options = range($this->min, $this->daysInMonth($end['m'], $end['y']));
            } else {
                $options = $this->whitelist;
            }

            $options = array_diff($options, $this->blacklist);
            $options = array_values($options); // resetting keys to be sequential

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

    /**
     * Get number of days in the month of this year
     *
     * @param int $month
     * @param int $year
     * @return int
     */
    protected function daysInMonth($month, $year) : int
    {
        return cal_days_in_month($this->calendar, $month, $year);
    }
}
