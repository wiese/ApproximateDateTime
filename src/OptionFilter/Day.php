<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\OptionFilter;

use wiese\ApproximateDateTime\Range;
use wiese\ApproximateDateTime\Ranges;
use cal_days_in_month;

class Day extends Base
{
    public function apply(Ranges $ranges) : Ranges
    {
        if (empty($this->clues->getWhitelist($this->unit)) && empty($this->clues->getBlacklist($this->unit))) { // all days
            foreach ($ranges as & $range) {
                $range->getStart()->d = $this->min;
                $range->getEnd()->d = cal_days_in_month($this->calendar, $range->getEnd()->m, $range->getEnd()->y);
            }

            return $ranges;
        }

        $newRanges = new Ranges();

        foreach ($ranges as $range) {
            $filets = $range->filet();

            $options = $this->getAllowableOptions(
                cal_days_in_month($this->calendar, $range->getEnd()->m, $range->getEnd()->y)
            );

            foreach ($filets as $filet) {
                /**
                 * @var \wiese\ApproximateDateTime\Range $filet
                 */
                foreach ($options as $key => $value) {
                    /**
                     * @var int $value
                     */
                    if (!isset($options[$key - 1]) // first overall
                        || $options[$key - 1] != $value - 1 // first of a block
                    ) {
                        $newRange = clone $filet;
                        $newRange->getStart()->d = $value;
                    }
                    if (!isset($options[$key + 1]) // last
                        || $options[$key + 1] != $value + 1 // last of a block
                    ) {
                        $newRange->getEnd()->d = $value;

                        $newRanges->append($newRange);
                    }
                }
            }
        }

        return $newRanges;
    }
}
