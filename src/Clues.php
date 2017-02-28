<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

use ArrayObject;
use DateTime;
use UnexpectedValueException;

/**
 * @todo implement ArrayAccess interface instead
 */
class Clues extends ArrayObject
{

    /**
     * Year to base clues on, if no year specified
     *
     * @var int
     */
    protected $defaultYear;

    /**
     * @var array Combined clue information on whitelisted dates
     */
    protected $whitelists = [];

    /**
     * @var array Combined clue information on blacklisted dates
     */
    protected $blacklists = [];

    /**
     * @var array Combined clue information on "before" values for individual units
     */
    protected $before = [];

    /**
     * @var array Combined clue information on "after" values for individual units
     */
    protected $after = [];

    /**
     * @var bool Flag to avoid repeated runs of self::generateFilterLists()
     */
    protected $cachedFilterLists = false;

    public function __construct()
    {
        $this->setDefaultYear((int) (new DateTime())->format(DateTimeFormat::YEAR));
        parent::__construct();
    }

    public function setDefaultYear(int $defaultYear) : self
    {
        $this->defaultYear = $defaultYear;
        $this->cachedFilterLists = false;

        return $this;
    }

    /**
     * Get options explicitly allowed for the unit given
     *
     * @param string $unit
     * @return Clue[]
     */
    public function getWhitelist(string $unit) : array
    {
        $this->generateFilterLists();

        if (empty($this->whitelists[$unit])) {
            return [];
        }

        return $this->whitelists[$unit];
    }

    /**
     * Get options explicitly disallowed for the unit given
     *
     * @param string $unit
     * @return Clue[]
     */
    public function getBlacklist(string $unit) : array
    {
        $this->generateFilterLists();

        if (empty($this->blacklists[$unit])) {
            return [];
        }

        return $this->blacklists[$unit];
    }

    /**
     * Get value the unit given has to be smaller/equal than
     *
     * @param string $unit
     * @return Clue|null
     */
    public function getBefore(string $unit) : ? Clue
    {
        $this->generateFilterLists();

        if (!isset($this->before[$unit])) {
            return null;
        }

        return $this->before[$unit];
    }

    /**
     * Get value the unit given has to be bigger/equal than
     *
     * @param string $unit
     * @return Clue|null
     */
    public function getAfter(string $unit) : ? Clue
    {
        $this->generateFilterLists();

        if (!isset($this->after[$unit])) {
            return null;
        }

        return $this->after[$unit];
    }

    /**
     * Check if clues provide a restriction for the given unit (e.g. a blacklisted day)
     *
     * @param string $unit
     * @return bool
     */
    public function unitHasRestrictions(string $unit) : bool
    {
        return !empty($this->getWhitelist($unit))
            || !empty($this->getBlacklist($unit))
            || !empty($this->getAfter($unit))
            || !empty($this->getBefore($unit));
    }

    public function append($value)
    {
        parent::append($value);

        $this->cachedFilterLists = false;
    }

    protected function generateFilterLists() : void
    {
        if ($this->cachedFilterLists) {
            return;
        }

        $this->whitelists = $this->blacklists = $this->before = $this->after = [];

        $validator = new ClueValidator();

        foreach ($this as $clue) {
            /**
             * @var Clue $clue
             */

            $validator->validate($clue);

            $typeId = $this->getTypeId($clue);

            switch ($clue->getType()) {
                case Clue::IS_WHITELIST:
                    if (!isset($this->whitelists[$typeId]) || !is_array($this->whitelists[$typeId])) {
                        $this->whitelists[$typeId] = [];
                    }
                    $this->whitelists[$typeId][] = $clue;
                    break;
                case Clue::IS_BLACKLIST:
                    if (!isset($this->blacklists[$typeId]) || !is_array($this->blacklists[$typeId])) {
                        $this->blacklists[$typeId] = [];
                    }
                    $this->blacklists[$typeId][] = $clue;
                    break;
                case Clue::IS_BEFOREEQUALS: // don't store anything but the smallest value for before
                    if (!isset($this->before[$typeId])) {
                        $this->before[$typeId] = null;
                    }
                    $this->before[$typeId] = $this->getSmallerClueValue($this->before[$typeId], $clue);
                    break;
                case Clue::IS_AFTEREQUALS: // don't store anything but the biggest value for after
                    if (!isset($this->after[$typeId])) {
                        $this->after[$typeId] = null;
                    }
                    $this->after[$typeId] = $this->getBiggerClueValue($this->after[$typeId], $clue);
                    break;
            }
        }

        $sanitizeList = function (array & $arrayValue) {
            array_unique($arrayValue, SORT_REGULAR);
            sort($arrayValue); // list in order of values
        };
        array_walk($this->whitelists, $sanitizeList);
        array_walk($this->blacklists, $sanitizeList);

        $this->guaranteeDefaultClue();

        $this->cachedFilterLists = true;
    }

    /**
     * Build the identifier describing the units a clue hints on
     *
     * @param Clue $clue
     * @return string
     */
    protected function getTypeId(Clue $clue) : string
    {
        return implode('-', $clue->getSetUnits());
    }

    /**
     * Make sure the default year is used in the absence of other clues
     */
    protected function guaranteeDefaultClue() : void
    {
        if (!empty($this->whitelists['y']) || !empty($this->whitelists['y-m']) || !empty($this->whitelists['y-m-d'])) {
            return;
        }

        if (!empty($this->blacklists['y'])) {
            foreach ($this->blacklists['y'] as $blacklistClue) {
                if ($blacklistClue->getY() === $this->defaultYear) {
                    throw new UnexpectedValueException(
                        'Tried applying the default year, but it is blacklisted. Please, whitelist a year.'
                    );
                }
            }
        }

        $yClue = new Clue();
        $yClue->setY($this->defaultYear);

        $this->whitelists['y'][] = $yClue;
    }

    /**
     * Get the clue with the smaller value
     *
     * @param Clue|null $existing
     * @param Clue $new
     * @return Clue
     */
    protected function getSmallerClueValue(Clue $existing = null, Clue $new) : Clue
    {
        if (is_null($existing)) {
            return $new;
        }

        return $new->isSmaller($existing) ? $new : $existing;
    }

    /**
     * Get the clue with the bigger value
     *
     * @param Clue|null $existing
     * @param Clue $new
     * @return Clue
     */
    protected function getBiggerClueValue(Clue $existing = null, Clue $new) : Clue
    {
        if (is_null($existing)) {
            return $new;
        }

        return $new->isBigger($existing) ? $new : $existing;
    }
}
