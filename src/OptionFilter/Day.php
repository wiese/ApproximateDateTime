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
        // @todo desired behaviour on empty $ranges?

        // fast lane
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
        $newRange = null;

        foreach ($ranges as $range) {
            /**
             * @var Range $range
             */
            $filets = $range->filet();

            foreach ($filets as $filet) {

                /**
                 * @var Range $filet A range of one month
                 */

                $this->log->info('filet: ' . $filet->getStart()->toString() . ' - ' . $filet->getEnd()->toString());

                $daysInMonth = $this->daysInMonth($filet->getEnd());
                $options = $this->getAllowableOptions($daysInMonth);

                $this->log->debug('daysInMonth', [$daysInMonth]);
                $this->log->debug('options', [$options]);
/*
                if (count($options) === $daysInMonth) { // all days this m - fast lane
                    if ($border) {  // start of a new range
                        $newRange = clone $filet;
                        $newRanges->append($newRange);
                        $newRange->getEnd()->d = array_pop($options);
                        $newRange->getStart()->d = array_shift($options);
                    } else { // mending existing range that spans across multiple months
                        $newRange->getEnd()->y = $filet->getEnd()->y;
                        $newRange->getEnd()->m = $filet->getEnd()->m;
                        $newRange->getEnd()->d = array_pop($options);
                        $newRange->getEnd()->dayIsLastInMonth = true;
                    }

                    $border = false;

                    continue;
                }
*/

                foreach ($options as $key => $value) { // laborious one-by-one processing
                    /**
                     * @var int $key
                     * @var int $value
                     */

                    $this->log->info('one-by-one processing d ' . $value);


                    if (!$newRange
                        || ($newRange && $newRange->getEnd()->dayIsLastInMonth && $value !== 1)
                    ) {
                        $newRange = clone $filet;
                        $newRange->getStart()->d = $value;
                        $newRanges->append($newRange);
                    }

                    $newRange->getEnd()->y = $filet->getEnd()->y;
                    $newRange->getEnd()->m = $filet->getEnd()->m;
                    $newRange->getEnd()->d = $value;
                    $newRange->getEnd()->dayIsLastInMonth = false;

                    if (!isset($options[$key + 1]) // last
                        || $options[$key + 1] != $value + 1 // last of a block
                    ) {
                        if ($value === $daysInMonth) {
                            $newRange->getEnd()->dayIsLastInMonth = true;
                        } else {
                            $newRange = null;
                        }
                    }

                    if ($newRange) {
                        $this->log->info('range', [$newRange->getStart()->toString(), $newRange->getEnd()->toString()]);
                    } else {
                        $this->log->info('reset newRange after ' . $value);
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
