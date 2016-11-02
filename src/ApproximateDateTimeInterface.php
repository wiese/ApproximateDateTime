<?php
declare(strict_types=1);

namespace wiese\ApproximateDateTime;

use \DateTimeInterface;
use \DateTime;
use \DateInterval;

interface ApproximateDateTimeInterface {
	
	/**
	 * Add a clue to (further) describe the date and time
	 *
	 * @param string $clue
	 * @return self
	 */
	public function addClue(string $clue) : self;

	/**
	 * @return string[]
	 */
	public function getClues() : array;

	/**
	 * Get the last valid moment described by the clues
	 *
	 * @return DateTime
	 */
	public function getEarliest() : DateTime;

	/**
	 * Get the last valid moment described by the clues
	 *
	 * @return DateTime
	 */
	public function getLatest() : DateTime;

	/**
	 * Get the interval in between earliest and latest possible moment
	 *
	 * @todo Does and can not cover holes in between. Lose and let user
	 * calculate herself if she needs this information of questionable quality?
	 *
	 * @return DateInterval
	 */
	public function getInterval() : DateInterval;

	/**
	 * Get all valid periods, i.e. start & interval, described by the clues
	 *
	 * @return DatePeriod[]
	 */
	public function getPossibilites() : array;

	/**
	 * Check if the given DateTime is withing the allowable range described
	 *
	 * @param DateTimeInterface $scrutinize
	 * @return bool
	 */
	public function isPossible(DateTimeInterface $scrutinize) : bool;

	/**
	 * Make an educated guess for a respresentative of all possible moments
	 *
	 * @return DateTime
	 */
	public function getLuckyShot() : DateTime;
}
