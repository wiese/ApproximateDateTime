<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

use DateInterval;
use DateTime;
use DateTimeInterface;

interface ApproximateDateTimeInterface
{

    /**
     * Set the clues to describe the date and time
     *
     * @param Clues $clues
     * @return self
     */
    public function setClues(Clues $clues) : self;

    /**
     * @return Clues
     */
    public function getClues() : Clues;

    /**
     * Get the first valid moment described by the clues
     *
     * @return DateTimeInterface
     */
    public function getEarliest() : ? DateTimeInterface;

    /**
     * Get the last valid moment described by the clues
     *
     * @return DateTimeInterface
     */
    public function getLatest() : ? DateTimeInterface;

    /**
     * Get all valid periods, i.e. start & interval, matching the clues
     *
     * @return DatePeriod[]
     */
    public function getPeriods() : array;

    /**
     * Check if the given DateTime is within the allowable range described
     *
     * @param DateTimeInterface $scrutinize
     * @return bool
     */
    public function isPossible(DateTimeInterface $scrutinize) : bool;
}
