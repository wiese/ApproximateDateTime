<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

class ClueParser
{

    const RULE_YEAR = '/^\d{1,4}$/';

    /**
     * @var string[]
     */
    protected $clues = [];

    /**
     * @var Clue[]
     */
    protected $processedClues = [];

    public function addClue(string $clue) : self
    {
        $this->resetProcessedClues();
        $this->clues[] = $clue;

        return $this;
    }

    /**
     * {@inheritDoc}
     * @see ApproximateDateTimeInterface::getClues()
     */
    public function getClues() : array
    {
        return $this->clues;
    }

    public function getProcessedClues() : array
    {
        return $this->processedClues;
    }

    /**
     * Void machine-readable internal information to maintain consistent state
     */
    protected function resetProcessedClues()
    {
        $this->processedClues = [];
    }

    /**
     * Convert provided clues to machine-readable internal information
     *
     * @return boolean If processing was done (true) or cache could be used
     */
    protected function processClues()
    {
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
     * @param string $rawClue
     *
     * @return Clue|null
     */
    protected function processClue(string $rawClue) : ? Clue
    {
        $clue = null;

        if (preg_match(self::RULE_YEAR, $rawClue)) {
            $clue = new Clue;
            $clue->type = 'y';
            $clue->rawValue = $rawClue;
        }

        return $clue;
    }
}
