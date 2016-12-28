<?php

namespace wiese\ApproximateDateTime\OptionFilter\Incarnation;

use wiese\ApproximateDateTime\Config;
use wiese\ApproximateDateTime\OptionFilter\Base;
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

        return $ranges;
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
