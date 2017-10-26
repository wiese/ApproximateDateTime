<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\OptionFilter;

use Psr\Log\LoggerInterface;
use wiese\ApproximateDateTime\Clue;
use wiese\ApproximateDateTime\Clues;
use wiese\ApproximateDateTime\Config;
use LogicException;

abstract class Base implements OptionFilterInterface
{

    /**
     * @var string
     */
    protected $unit;

    /**
     * @var Clues
     */
    protected $clues;

    /**
     * @todo Not really a property of all filters (e.g. Numeric) - move away?
     *
     * @var int
     */
    protected $calendar;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var LoggerInterface
     */
    protected $log;

    public function __construct(Config $config, LoggerInterface $log)
    {
        $this->config = $config;
        $this->log = $log;
    }

    /**
     * {@inheritDoc}
     * @see OptionFilterInterface::setUnit()
     */
    public function setUnit(string $unit) : void
    {
        $this->unit = $unit;
    }

    /**
     * {@inheritDoc}
     * @see OptionFilterInterface::setClues()
     */
    public function setClues(Clues $clues) : void
    {
        $this->clues = $clues;
    }

    /**
     * {@inheritDoc}
     * @see OptionFilterInterface::setCalendar()
     */
    public function setCalendar(int $calendar) : void
    {
        $this->calendar = $calendar;
    }

    /**
     * Determine the range of allowable values from all clues, and limits
     *
     * @param int|null $overrideMax
     * @return int[]
     */
    protected function getAllowableOptions(? int $overrideMax = null) : array
    {
        $this->log->debug('getAllowableOptions', [$this->unit, $overrideMax]);

        $max = is_int($overrideMax) ? $overrideMax : $this->config->getMax($this->unit);
        $min = $this->config->getMin($this->unit);
        $ltEq = $this->getNumericClueValue($this->clues->getBefore($this->unit), $this->unit);
        $gtEq = $this->getNumericClueValue($this->clues->getAfter($this->unit), $this->unit);

        $this->log->debug('bounds', [$min, $max, $gtEq, $ltEq]);

        $options = $this->getNumericCluesValues($this->clues->getWhitelist($this->unit), $this->unit);

        if (is_int($min) && is_int($max)) { // e.g. y does not know extremes
            $minToMax = range($min, $max);

            if (empty($options)) {
                $options = $minToMax;
            } else {
                $options = array_intersect($options, $minToMax);
            }
        }

        $validPerBeforeAfter = [];

        if (is_int($gtEq) && is_int($ltEq) && $gtEq < $ltEq) {
            $validPerBeforeAfter = range($gtEq, $ltEq);
        } else {
            if (is_int($gtEq)) {
                $validPerBeforeAfter = array_unique(array_merge($validPerBeforeAfter, range($gtEq, $max)));
            }
            if (is_int($ltEq)) {
                $validPerBeforeAfter = array_unique(array_merge($validPerBeforeAfter, range($min, $ltEq)));
            }
        }

        $this->log->debug('validPerBeforeAfter', [$validPerBeforeAfter]);

        if (!empty($validPerBeforeAfter)) {
            $options = array_intersect($options, $validPerBeforeAfter);
        }

        $options = array_diff(
            $options,
            $this->getNumericCluesValues($this->clues->getBlacklist($this->unit), $this->unit)
        );
        $options = array_values($options); // resetting keys to be sequential
        // array_unique?

        $this->log->debug('options ' . $this->unit, [$options]);

        return $options;
    }

    /**
     * Get the value of the given unit of the given clue
     *
     * @param Clue|null $clue
     * @param string $unit
     * @return int|null
     */
    private function getNumericClueValue(? Clue $clue, string $unit) : ? int
    {
        if (is_null($clue)) {
            return null;
        }

        if ($clue->getSetUnits() !== [$unit]) {
            throw new LogicException('Clue not fit for this simplification');
        }

        return $clue->get($unit);
    }

    /**
     * Get the values of the given unit of the given clues
     *
     * @param Clue[] $clues
     * @param string $unit
     * @return array
     */
    private function getNumericCluesValues(array $clues, string $unit) : array
    {
        $results = [];

        foreach ($clues as $clue) {
            $results[] = $this->getNumericClueValue($clue, $unit);
        }

        return $results;
    }
}
