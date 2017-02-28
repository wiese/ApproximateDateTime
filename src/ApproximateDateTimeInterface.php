<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

use DatePeriod;
use DateTimeInterface;
use RuntimeException;

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
     * @throws RuntimeException
     *
     * @return DateTimeInterface
     */
    public function getEarliest() : ? DateTimeInterface;

    /**
     * Get the last valid moment described by the clues
     *
     * @throws RuntimeException
     *
     * @return DateTimeInterface
     */
    public function getLatest() : ? DateTimeInterface;

    /**
     * Get all valid periods, i.e. start & interval, matching the clues
     *
     * @throws RuntimeException
     *
     * @return DatePeriod[]
     */
    public function getPeriods() : array;

    /**
     * Check if the given DateTime is within the allowable range(s) described
     *
     * @throws RuntimeException
     *
     * @param DateTimeInterface $scrutinize
     * @return bool
     */
    public function isPossible(DateTimeInterface $scrutinize) : bool;
}
