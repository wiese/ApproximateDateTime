<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

use wiese\ApproximateDateTime\Clue;
use wiese\ApproximateDateTime\Config;
use wiese\ApproximateDateTime\DateTimeFormat;
use ArrayObject;
use DateTime;

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

        foreach ($this as $clue) {
            // @todo validate value

            $typeId = implode('-', $clue->getSetUnits());

            switch ($clue->type) {
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
                case Clue::IS_BEFOREEQUALS:
                    if (!isset($this->before[$typeId])) {
                        $this->before[$typeId] = null;
                    }
                    $this->before[$typeId] = $this->getSmallerClueValue($this->before[$typeId], $clue);
                    break;
                case Clue::IS_AFTEREQUALS:
                    if (!isset($this->after[$typeId])) {
                        $this->after[$typeId] = null;
                    }
                    $this->after[$typeId] = $this->getBiggerClueValue($this->after[$typeId], $clue);
                    break;
            }
        }

        array_walk($this->whitelists, [$this, 'listSanitizingCallback']);
        array_walk($this->blacklists, [$this, 'listSanitizingCallback']);

        // @todo what if default year is blacklisted?
        if (empty($this->whitelists['y'])) {
            $yClue = new Clue();
            $yClue->setY($this->defaultYear);

            $this->whitelists['y'][] = $yClue;
        }

        $this->cachedFilterLists = true;
    }

    protected function getSmallerClueValue(Clue $existing = null, Clue $new) : Clue
    {
        if (is_null($existing)) {
            return $new;
        }

        return $new->isSmaller($existing) ? $new : $existing;
    }

    protected function getBiggerClueValue(Clue $existing = null, Clue $new) : Clue
    {
        if (is_null($existing)) {
            return $new;
        }

        return $new->isBigger($existing) ? $new : $existing;
    }

    /**
     * Sanitize the (cache) lists of clue information - avoid redundancy, ...
     *
     * @param $arrayValue
     */
    protected function listSanitizingCallback(& $arrayValue)
    {
        array_unique($arrayValue, SORT_REGULAR);
        sort($arrayValue); // list in order of values
    }
}
