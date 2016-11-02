<?php
declare(strict_types=1);

namespace wiese\ApproximateDateTime;

use \DateTimeInterface;
use \DateTime;
use \DateInterval;
use \DateTimeZone;

class ApproximateDateTime implements ApproximateDateTimeInterface {

	const DEFAULT_TIMEZONE = 'UTC';

	const RULE_YEAR = '/^\d{1,4}$/';

	/**
	 * @var string[]
	 */
	protected $clues = [];

	/**
	 * @var Clue[]
	 */
	protected $processedClues = [];
	
	/**
	 * @var DateTimeZone
	 */
	protected $timezone;

	/**
	 * @param string $timezone
	 */
	public function __construct(string $timezone = self::DEFAULT_TIMEZONE) {
		$this->timezone = new DateTimeZone($timezone);
	}

	/**
	 * {@inheritDoc}
	 * @see \wiese\ApproximateDateTime\ApproximateDateTimeInterface::addClue()
	 */
	public function addClue(string $clue) : ApproximateDateTimeInterface {

		$this->resetProcessedClues();
		$this->clues[] = $clue;

		return $this;
	}

	/**
	 * {@inheritDoc}
	 * @see \wiese\ApproximateDateTime\ApproximateDateTimeInterface::getClues()
	 */
	public function getClues() : array {
		return $this->clues;
	}

	/**
	 * {@inheritDoc}
	 * @see \wiese\ApproximateDateTime\ApproximateDateTimeInterface::getEarliest()
	 */
	public function getEarliest() : DateTimeInterface {

		$this->processClues();

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

		$this->processClues();

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

		$this->processClues();

		$diff = $this->getEarliest()->diff($this->getLatest());
		
		return $diff;
	}

	/**
	 * {@inheritDoc}
	 * @see \wiese\ApproximateDateTime\ApproximateDateTimeInterface::getPossibilites()
	 */
	public function getPossibilites() : array {

		$periods = [];

		$this->processClues();

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

		$this->processClues();

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

	protected function resetProcessedClues() {
		$this->processedClues = [];
	}

	/**
	 * Convert provided clues to machine-readable internal information
	 *
	 * @return boolean If processing was done (true) or cache could be used
	 */
	protected function processClues() {

		if (!empty($this->processedClues)) {
			return false;
		}

		foreach ($this->clues as $key => $clue) {
			$this->processedClues[$key] = $this->processClue($clue);
		}

		return true;
	}

	/**
	 * Convert a single provided clue into internal information
	 *
	 * @param string $input
	 *
	 * @return NULL|\wiese\ApproximateDateTime\Clue
	 */
	protected function processClue(string $input) : ? Clue {
		
		$clue = null;

		if (preg_match(self::RULE_YEAR, $input)) {
			$clue = new Clue;
			$clue->type = 'y';
			$clue->value = $input;
			$clue->first = new DateTime("$input-01-01T00:00:00", $this->timezone);
			$clue->last = new DateTime("$input-12-31T23:59:59", $this->timezone);
		}

		return $clue;
	}
}

