<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\OptionFilter;

use wiese\ApproximateDateTime\Clues;
use wiese\ApproximateDateTime\Ranges;

interface OptionFilterInterface
{

    /**
     * Mend the given ranges as per the restrictions defined through clues
     *
     * @param Ranges $ranges
     * @return Ranges
     */
    public function __invoke(Ranges $ranges) : Ranges;

    /**
     * Set the unit the OptionFilter is supposed to be working on
     *
     * @param string $unit
     */
    public function setUnit(string $unit) : void;

    /**
     * Set the clues to be used for the restriction of ranges
     *
     * @param Clues $clues
     */
    public function setClues(Clues $clues) : void;

    /**
     * Set the calendar to be used for date calculations
     *
     * @param int $calendar
     */
    public function setCalendar(int $calendar) : void;
}
