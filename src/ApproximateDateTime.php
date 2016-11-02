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

	public function __construct(string $timezone = self::DEFAULT_TIMEZONE) {
		$this->timezone = new DateTimeZone($timezone);
	}

	public function addClue(string $clue) : ApproximateDateTimeInterface {

		$this->resetProcessedClues();
		$this->clues[] = $clue;

		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getClues() : array {
		return $this->clues;
	}

	public function getEarliest() : DateTime {

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

	public function getLatest() : DateTime {

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
	 * Does this make sense when there is separate valid bits? (e.g. May or July)
	 */
	public function getInterval() : DateInterval {

		$this->processClues();

		$diff = $this->getEarliest()->diff($this->getLatest());
		
		return $diff;
	}

	/**
	 * 
	 * @return DatePeriod[] List of compatible periods (start + interval)
	 */
	public function getPossibilites() : array {

		$periods = [];

		$this->processClues();

		return $periods;
	}

	public function isPossible(DateTimeInterface $scrutinize) : bool {

		$verdict = false;

		$this->processClues();

		$verdict = ($scrutinize >= $this->getEarliest() && $scrutinize <= $this->getLatest());

		return $verdict;
	}

	public function getLuckyShot() : DateTime {
		return $this->getEarliest();
	}

	protected function resetProcessedClues() {
		$this->processedClues = [];
	}

	protected function processClues() {

		if (!empty($this->processedClues)) {
			return false;
		}

		foreach ($this->clues as $key => $clue) {
			$this->processedClues[$key] = $this->processClue($clue);
		}

		return true;
	}

	protected function processClue($input) {
		
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

