<?php
declare(strict_types=1);

namespace wiese\ApproximateDateTime;

use \DateTimeInterface;
use \DateTime;
use \DateInterval;

interface ApproximateDateTimeInterface {
	
	public function addClue(string $clue) : self;

	public function getClues() : array;

	public function getEarliest() : DateTime;

	public function getLatest() : DateTime;

	public function getInterval() : DateInterval;

	public function getPossibilites() : array;

	public function isPossible(DateTimeInterface $scrutinize) : bool;

	public function getLuckyShot() : DateTime;
}

