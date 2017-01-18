<?php

namespace wiese\ApproximateDateTime\OptionFilter\Incarnation;

use wiese\ApproximateDateTime\Clue;
use wiese\ApproximateDateTime\DateTimeData;
use wiese\ApproximateDateTime\OptionFilter\Base;
use wiese\ApproximateDateTime\Range;
use wiese\ApproximateDateTime\Ranges;

class Compound extends Base
{
    /**
     * @todo What on empty range but e.g. before and after given, making it a solvable problem
     * @todo Lose that double loop?!
     *
     * {@inheritDoc}
     * @see Base::apply()
     */
    public function apply(Ranges $ranges) : Ranges
    {
        $processUnits = explode('-', $this->unit);

        $before = $this->clues->findOne(Clue::IS_BEFOREEQUALS, $processUnits);
        if ($before instanceof Clue) {
            $ranges = $this->applyBefore($ranges, $before);
        }

        $after = $this->clues->findOne(Clue::IS_AFTEREQUALS, $processUnits);
        if ($after instanceof Clue) {
            $ranges = $this->applyAfter($ranges, $after);
        }

        $filters = [
            Clue::IS_WHITELIST => 'applyWhitelist',
            Clue::IS_BLACKLIST => 'applyBlacklist'
        ];
        foreach ($filters as $filter => $method) {
            $clues = $this->clues->find($filter, $processUnits);
            foreach ($clues as $clue) {
                $ranges = call_user_func([$this, $method], $ranges, $clue);
            }
        }

        return $ranges;
    }

    protected function applyAfter(Ranges $ranges, Clue $clue) : Ranges
    {
        $newRanges = new Ranges();

        foreach ($ranges as $range) {
            /**
             * @var $range Range
             */
            if ($range->getStart()->isBigger($clue)) { // completely later
                $newRanges->append($range);
                continue;
            } elseif ($range->getEnd()->isSmaller($clue)) { // completely earlier
                break; // ignore all following, as ranges should be in order
            } else { // overlapping
                $newRange = clone $range;
                $start = $range->getStart();
                foreach ($clue->getSetUnits() as $unit) {
                    $start->set($unit, $clue->get($unit));
                }
                $newRange->setStart($start);
                $newRanges->append($newRange);

                break;
            }
        }

        return $newRanges;
    }

    protected function applyBefore(Ranges $ranges, Clue $clue) : Ranges
    {
        $newRanges = new Ranges();

        foreach ($ranges as $range) {
            /**
             * @var $range Range
             */
            if ($range->getEnd()->isSmaller($clue)) { // completely earlier
                $newRanges->append($range);
                continue;
            } elseif ($range->getStart()->isBigger($clue)) { // completely later
                break; // ignore all following, as ranges should be in order
            } else { // overlapping
                $newRange = clone $range;
                $end = $range->getEnd();
                foreach ($clue->getSetUnits() as $unit) {
                    $end->set($unit, $clue->get($unit));
                }
                $newRange->setEnd($end);
                $newRanges->append($newRange);

                break;
            }
        }

        return $newRanges;
    }

    /**
     * New whitelisted times are to become part of possible ranges.
     *
     * @param Ranges $ranges
     * @param Clue $clue
     * @return Ranges
     */
    protected function applyWhitelist(Ranges $ranges, Clue $clue) : Ranges
    {
        $ranges = clone $ranges; // don't manipulate the input

        $setUnits = $clue->getSetUnits();

        if (in_array('y', $setUnits)) {
            $range = new Range();
            $start = new DateTimeData();
            $end = new DateTimeData();

            foreach ($setUnits as $unit) {
                $start->set($unit, $clue->get($unit)); // @fixme ::merge() instead? But not for Vehicle (yet)
                $end->set($unit, $clue->get($unit));
            }
            $range->setStart($start);
            $range->setEnd($end);
            $ranges->append($range);
        } else {
            // @todo Merge info into existing higher-level unit ranges. See ApproximateDateTimeTest::testCompoundUnits()
        }

        $ranges = $this->sanitizeRanges($ranges);

        return $ranges;
    }

    /**
     * Clean up overlapping ranges
     *
     * @param Ranges $ranges
     * @return Ranges
     */
    protected function sanitizeRanges(Ranges $ranges) : Ranges
    {
        $ranges->sort();

        $nRanges = count($ranges);

        $newRanges = new Ranges;

        for ($i = 0; $i < $nRanges; $i++) {
            /**
             * @var Range $current
             */
            $current = $ranges[$i];

            if (isset($ranges[$i + 1])) { // not the last
                /**
                 * @var DateTimeData $currentEnd
                 */
                $currentEnd = $current->getEnd();
                /**
                 * @var Range $current
                 */
                $next = $ranges[$i + 1];
                /**
                 * @var DateTimeData $nextStart
                 */
                $nextStart = $next->getStart();
                /**
                 * @var DateTimeData $nextEnd
                 */
                $nextEnd = $next->getEnd();
                if ($nextStart->isSmaller($currentEnd) || $nextStart->equals($currentEnd)) {
                    $this->log->debug('overlapping ranges', [$nextStart->toString(), $currentEnd->toString()]);
                    $end = $currentEnd->isBigger($nextEnd) ? $currentEnd : $nextEnd;
                    $current->setEnd($end); // manipulate the current to merge current and next
                    $i++; // skip next as it is covered already
                }
            }

            $newRanges->append($current);
        }

        return $newRanges;
    }

    /**
     * Blacklisted times must not be part of possible ranges.
     *
     * @param Ranges $ranges
     * @param Clue $clue
     * @return Ranges
     */
    protected function applyBlacklist(Ranges $ranges, Clue $clue) : Ranges
    {
        $newRanges = new Ranges();

        foreach ($ranges as $range) {
            /**
             * @var $range Range
             */
            if ($range->getStart()->isBigger($clue) || $range->getEnd()->isSmaller($clue)) {
                $newRanges->append($range); // leave range as it is
            } elseif ($range->getStart()->equals($clue) && $range->getStart()->equals($clue)) {
                continue; // a very short range, completely blacklisted
            } elseif ($range->getStart()->equals($clue)) {
                $range->getStart()->increment();
                $newRanges->append($range);
            } elseif ($range->getEnd()->equals($clue)) {
                $range->getEnd()->decrement();
                $newRanges->append($range);
            } elseif ($range->getStart()->isSmaller($clue) && $range->getEnd()->isBigger($clue)) {
                $newRange = clone $range;

                $end = $range->getEnd();
                foreach ($clue->getSetUnits() as $unit) {
                    $end->set($unit, $clue->get($unit));  // @fixme ::merge() instead? But not for Vehicle (yet)
                }
                $end->decrement();
                $newRanges->append($range);


                // @todo Make Vehicle instance a member (->data) of Clue again, add ability to extract it
                $start = $newRange->getStart();
                foreach ($clue->getSetUnits() as $unit) {
                    $start->set($unit, $clue->get($unit));  // @fixme ::merge() instead? But not for Vehicle (yet)
                }
                $start->increment();

                $newRanges->append($newRange);
            } else {
                die('can we even get here?');
            }
        }

        return $newRanges;
    }
}
