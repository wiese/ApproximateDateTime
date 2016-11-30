<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\OptionFilter;

use wiese\ApproximateDateTime\DateTimeData;
use wiese\ApproximateDateTime\Range;
use wiese\ApproximateDateTime\Ranges;
use function cal_days_in_month;

class Day extends Base
{
    /**
     * {@inheritDoc}
     * @see Base::apply()
     */
    public function apply(Ranges $ranges) : Ranges
    {
        // @todo desired behaviour on empty($ranges)?
        if (empty($this->clues->getWhitelist($this->unit))
            && empty($this->clues->getBlacklist($this->unit))
            && empty($this->clues->getAfter($this->unit))
            && empty($this->clues->getBefore($this->unit))
        ) { // all days in all m
            foreach ($ranges as & $range) {
                $range->getStart()->d = $this->config->getMin($this->unit);
                $range->getEnd()->d = $this->daysInMonth($range->getEnd());
            }

            return $ranges;
        }

        $newRanges = new Ranges();
        $border = true; // start of a new range - always at the beginning

        foreach ($ranges as $range) {
            /**
             * @var Range $range
             */
            $filets = $range->filet();

            foreach ($filets as $filet) {
                /**
                 * @var Range $filet
                 */

                $daysInMonth = $this->daysInMonth($filet->getEnd());
                $options = $this->getAllowableOptions($daysInMonth);

                if (count($options) === $daysInMonth) { // all days this m
                    if ($border) {
                        $newRange = clone $filet;
                        $newRanges->append($newRange);
                        $newRange->getEnd()->d = array_pop($options);
                        $newRange->getStart()->d = array_shift($options);
                    } else {
                        $newRange->getEnd()->y = $filet->getEnd()->y;
                        $newRange->getEnd()->m = $filet->getEnd()->m;
                        $newRange->getEnd()->d = array_pop($options);
                    }

                    $border = false;

                    continue;
                }

                foreach ($options as $key => $value) {
                    /**
                     * @var int $key
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
                        // @todo do we have to copy y & m from current filet?
                        $newRange->getEnd()->d = $value;

                        $newRanges->append($newRange);

                        $border = true;
                    }
                }
            }
        }

        return $newRanges;
    }

    /**
     * Get the number of days in the month as per given $data (its y & m)
     *
     * @todo Exception handling if DateTimeData not qualified (yet)
     *
     * @param DateTimeData $data
     * @return int
     */
    protected function daysInMonth(DateTimeData $data)
    {
        return cal_days_in_month($this->calendar, $data->m, $data->y);
    }
}
