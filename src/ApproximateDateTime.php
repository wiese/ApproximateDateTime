<?php
declare(strict_types=1);

namespace wiese\ApproximateDateTime;

use \DateTimeInterface;
use \DateTime;
use \DateInterval;
use \DateTimeZone;

class ApproximateDateTime implements ApproximateDateTimeInterface {

	const DEFAULT_TIMEZONE = 'UTC';
	
	/**
	 * @var DateTimeZone
	 */
	protected $timezone;

	/**
	 * @var Clue[]
	 */
	protected $processedClues = [];

	/**
	 * @param string $timezone
	 */
	public function __construct(string $timezone = self::DEFAULT_TIMEZONE) {
		$this->timezone = new DateTimeZone($timezone);
	}

	/**
	 * @return DateTimeZone
	 */
	public function getTimezone() : DateTimeZone {
		return $this->timezone;
	}

	public function setClues(array $clues) {
		$this->processedClues = $clues;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \wiese\ApproximateDateTime\ApproximateDateTimeInterface::getEarliest()
	 */
	public function getEarliest() : DateTimeInterface {

		$moment = null;

		foreach ($this->processedClues as $clue) {
			$clueMoment = $clue->first;
			if (is_null($moment) || $clueMoment < $moment) {
				$moment = $clueMoment;
			}
		}

		return $moment;
	}

	/**
	 * {@inheritDoc}
	 * @see \wiese\ApproximateDateTime\ApproximateDateTimeInterface::getLatest()
	 */
	public function getLatest() : DateTimeInterface {

		$moment = null;

		foreach ($this->processedClues as $clue) {
			$clueMoment = $clue->last;
			if (is_null($moment) || $clueMoment > $moment) {
				$moment = $clueMoment;
			}
		}

		return $moment;
	}

	/**
	 * {@inheritDoc}
	 * @see \wiese\ApproximateDateTime\ApproximateDateTimeInterface::getInterval()
	 */
	public function getInterval() : DateInterval {

		$diff = $this->getEarliest()->diff($this->getLatest());
		
		return $diff;
	}

	/**
	 * {@inheritDoc}
	 * @see \wiese\ApproximateDateTime\ApproximateDateTimeInterface::getPossibilites()
	 */
	public function getPossibilites() : array {

		$periods = [];

		return $periods;
	}

	/**
	 * @todo So far only works with one single, consecutive interval
	 *
	 * {@inheritDoc}
	 * @see \wiese\ApproximateDateTime\ApproximateDateTimeInterface::isPossible()
	 */
	public function isPossible(DateTimeInterface $scrutinize) : bool {

		$verdict = false;

		$verdict = ($scrutinize >= $this->getEarliest() && $scrutinize <= $this->getLatest());

		return $verdict;
	}

	/**
	 * {@inheritDoc}
	 * @see \wiese\ApproximateDateTime\ApproximateDateTimeInterface::getLuckyShot()
	 */
	public function getLuckyShot() : DateTimeInterface {
		return $this->getEarliest();
	}
}

