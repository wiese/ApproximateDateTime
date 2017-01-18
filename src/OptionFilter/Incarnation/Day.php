<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\OptionFilter\Incarnation;

use wiese\ApproximateDateTime\OptionFilter\Base;
use wiese\ApproximateDateTime\DateTimeData;
use wiese\ApproximateDateTime\Range;
use wiese\ApproximateDateTime\Ranges;
use UnexpectedValueException;
use function cal_days_in_month;

/**
 * Apply day restrictions (n-th day of month, that is) to existing ranges.
 * Trickier than the ordinary numeric operation as the number of days in a month depends on calendar, year, and month.
 *
 * @package wiese\ApproximateDateTime\OptionFilter\Incarnation
 */
class Day extends Base
{

    /**
     * {@inheritDoc}
     * @see OptionFilterInterface::__invoke()
     */
    public function __invoke(Ranges $ranges) : Ranges
    {
        // @todo desired behaviour on empty $ranges?

        // fast lane
        if (!$this->clues->unitHasRestrictions($this->unit)) { // all days in all m
            return $this->applyWithoutRestrictions($ranges);
        }

        return $this->applyWithRestrictions($ranges);
    }

    /**
     * Modify the ranges per day options for simple cases where entire months are covered
     *
     * @param Ranges $ranges
     * @return Ranges
     */
    protected function applyWithoutRestrictions(Ranges $ranges) : Ranges
    {
        foreach ($ranges as & $range) {
            $range->getStart()->setD($this->config->getMin($this->unit));
            $range->getEnd()->setD($this->daysInMonth($range->getEnd()));
        }

        return $ranges;
    }

    /**
     * Modify the ranges per day options for complex cases, including e.g. blacklisted days, ...
     *
     * @param Ranges $ranges
     * @return Ranges
     */
    protected function applyWithRestrictions(Ranges $ranges) : Ranges
    {
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
                $this->log->info('filet', [$filet->getStart()->toString(), $filet->getEnd()->toString()]);

                $daysInMonth = $this->daysInMonth($filet->getEnd());
                $options = $this->getAllowableOptions($daysInMonth);

                $this->log->debug('daysInMonth', [$daysInMonth]);
                $this->log->debug('options', [$options]);

                foreach ($options as $key => $value) { // laborious one-by-one processing
                    /**
                     * @var int $key
                     * @var int $value
                     */

                    $this->log->info('one-by-one processing d', [$value]);

                    if (!$newRange
                        || ($newRange instanceof Range && $newRange->getEnd()->dayIsLastInMonth && $value !== 1)
                    ) {
                        $newRange = clone $filet;
                        $newRange->getStart()->setD($value);
                        $newRanges->append($newRange);
                    }

                    $newRange->getEnd()->setDate($filet->getEnd()->getY(), $filet->getEnd()->getM(), $value);
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
                        $this->log->info('reset newRange', [$value]);
                    }
                }
            }
        }

        return $newRanges;
    }

    /**
     * Get the number of days in the month as per given $data (its y & m)
     *
     * @todo Scientifically wrong in early years AD? http://php.net/manual/de/function.cal-days-in-month.php#41554
     *
     * @param DateTimeData $data
     * @return int
     * @throws \UnexpectedValueException
     */
    protected function daysInMonth(DateTimeData $data)
    {
        if (!is_int($data->getY()) || !is_int($data->getM())) {
            throw new UnexpectedValueException('Can not calculate days in month on DateTimeData without y or m.');
        }

        return cal_days_in_month($this->calendar, $data->getM(), $data->getY());
    }
}
