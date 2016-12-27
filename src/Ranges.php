<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

use wiese\ApproximateDateTime\Range;
use ArrayObject;

class Ranges extends ArrayObject
{
    public function merge(self $ranges) : self
    {
        $combined = new self;

        if (!$this->count()) { // on "highest level"/first run
            return $ranges;
        }

        foreach ($this as $range1) {
            /**
             * @var Range $range1
             */
            foreach ($ranges as $range2) {
                /**
                 * @var Range $range2
                 */
                $newRange = clone $range1;
                $newRange->getStart()->merge($range2->getStart());
                $newRange->getEnd()->merge($range2->getEnd());

                $combined->append($newRange);
            }
        }

        return $combined;
    }

    public function sort() : void
    {
        $this->uasort(function(Range $range1, Range $range2) {
            return ($range1->getStart()->compareTo($range2->getStart()));
        });

        // @fixme mental way of ordering keys after value ordering
        $this->exchangeArray(array_values($this->getArrayCopy()));
    }
}
