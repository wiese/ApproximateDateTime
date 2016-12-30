<?php

namespace wiese\ApproximateDateTime\OptionFilter\Incarnation;

use wiese\ApproximateDateTime\Config;
use wiese\ApproximateDateTime\Clue;
use wiese\ApproximateDateTime\DateTimeData;
use wiese\ApproximateDateTime\OptionFilter\Base;
use wiese\ApproximateDateTime\Range;
use wiese\ApproximateDateTime\Ranges;

class Compound extends Base
{
    /**
     * @todo
     * before Clue
     * after Clue
     * whitelist Clue[]
     * blacklist Clue[]
     *
     * {@inheritDoc}
     * @see Base::apply()
     */
    public function apply(Ranges $ranges): Ranges
    {
//        if (!$this->clues->unitHasRestrictions($this->unit)) {
//            return $ranges;
//        }

        $processUnits = explode('-', $this->unit);

        foreach ($this->clues as $clue) {
            /**
             * @var $clue Clue
             */

            $units = $clue->getSetUnits();
            if (count($units) < 2) {
                continue;
            }

            if ($processUnits !== $units) {
                continue;
            }

            if ($clue->filter === Clue::FILTER_BEFOREEQUALS) {
                $ranges = $this->applyBefore($ranges, $clue);
            } elseif ($clue->filter === Clue::FILTER_AFTEREQUALS) {
                $ranges = $this->applyAfter($ranges, $clue);
            }

            // before
            // after



            // blacklist
            // whitelist
        }

        return $ranges;
    }

    public function applyAfter(Ranges $ranges, Clue $clue): Ranges
    {
        $newRanges = new Ranges();

        foreach ($ranges as $range) {
            /**
             * @var $range Range
             */
            if ($range->getStart()->isBigger($clue)) {   // completely later
                $newRanges->append($range);
                continue;
            } elseif ($range->getEnd()->isSmaller($clue)) {   // completely earlier
                break;  // ignore all following, as ranges should be in order
            } else {  // start earlier, but end later, i.e. overlapping
                // ignore
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

    public function applyBefore(Ranges $ranges, Clue $clue): Ranges
    {
        $newRanges = new Ranges();

        foreach ($ranges as $range) {
            /**
             * @var $range Range
             */
            if ($range->getEnd()->isSmaller($clue)) {
                $newRanges->append($range);
                continue;
            } elseif ($range->getStart()->isBigger($clue)) {
                break;  // ignore all following, as ranges should be in order
            } else {  // start earlier, but end later, i.e. overlapping
                // ignore
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

    protected function getCompoundUnits() : array
    {
        $units = [];
        foreach (Config::$compoundUnits as $unit => $smallestUnit) {
            if ($unit === $smallestUnit) {  // "normal" unit
                continue;
            }

            $units[] = $unit;
        }

        return $units;
    }
}
