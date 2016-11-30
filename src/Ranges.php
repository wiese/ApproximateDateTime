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
        $this->uasort(function ($range1, $range2) {
            $a = $range1->getStart()->toString();
            $b = $range2->getStart()->toString();
            if ($a == $b) {
                return 0;
            }
            return ($a < $b) ? -1 : 1;
        });

        // @fixme mental way of ordering keys after value ordering
        $this->exchangeArray(array_values($this->getArrayCopy()));
    }
}
