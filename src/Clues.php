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
     * @return int[]
     */
    public function getWhitelist(string $unit) : array
    {
        $this->generateFilterLists();

        if (strpos($unit, '-') !== false) {
            throw new \Exception('Not implemented for compound units, yet.');
        }

        $whitelists = [];
        foreach ($this->whitelists[$unit] as $clue) {
            $whitelists[] = $clue->get($unit);
        }

        return $whitelists;
    }

    /**
     * Get options explicitly disallowed for the unit given
     *
     * @param string $unit
     * @return int[]
     */
    public function getBlacklist(string $unit) : array
    {
        $this->generateFilterLists();

        if (strpos($unit, '-') !== false) {
            throw new \Exception('Not implemented for compound units, yet.');
        }

        $blacklist = [];
        foreach ($this->blacklists[$unit] as $clue) {
            $blacklist[] = $clue->get($unit);
        }

        return $blacklist;
    }

    /**
     * Get value the unit given has to be smaller/equal than
     *
     * @param string $unit
     * @return int|null
     */
    public function getBefore(string $unit) : ? int
    {
        $this->generateFilterLists();

        if (strpos($unit, '-') !== false) {
            throw new \Exception('Not implemented for compound units, yet.');
        }

        if (is_null($this->before[$unit])) {
            return null;
        }

        return $this->before[$unit]->get($unit);
    }

    /**
     * Get value the unit given has to be bigger/equal than
     *
     * @param string $unit
     * @return int|null
     */
    public function getAfter(string $unit) : ? int
    {
        $this->generateFilterLists();

        if (strpos($unit, '-') !== false) {
            throw new \Exception('Not implemented for compound units, yet.');
        }

        if (is_null($this->after[$unit])) {
            return null;
        }

        return $this->after[$unit]->get($unit);
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

    public static function fromArray(array $clues) : self
    {
        $instance = new self();
        $instance->exchangeArray($clues);

        return $instance;
    }

    public function append($value)
    {
        parent::append($value);

        $this->cachedFilterLists = false;
    }

    /**
     * Find set clues based on criteria
     *
     * @fixme Am I needed in this abstracted form? If at all, shouldn't the return type be Clues, too?
     * @todo Possibility to filter for multiple types by bitmask
     *
     * @param int $type The type of clue to match, e.g. Clue::FILTER_WHITELIST
     * @param array $units Units that have to be set to match; array of unit names, e.g. ['y', 'm']
     *
     * @return array
     */
    public function find(int $type, array $units = [])
    {
        $clues = [];
        foreach ($this->storage as $clue) {
            /**
             * @var Clue $clue
             */
            if ($clue->filter !== $type) {
                continue;
            }

            if (!empty($units) && $units !== $clue->getSetUnits()) {
                continue;
            }

            $result[] = $clue;
        }

        return $clues;
    }

    protected function generateFilterLists() : void
    {
        if ($this->cachedFilterLists) {
            return;
        }

        $this->initializeLists();

        foreach ($this as $clue) {
            // @todo validate value

            $typeId = implode('-', $clue->getSetUnits());

            switch ($clue->filter) {
                case Clue::FILTER_WHITELIST:
                    $this->whitelists[$typeId][] = $clue;
                    break;
                case Clue::FILTER_BLACKLIST:
                    $this->blacklists[$typeId][] = $clue;
                    break;
                case Clue::FILTER_BEFOREEQUALS:
                    $this->before[$typeId] = $this->getSmallerClueValue($this->before[$typeId], $clue);
                    break;
                case Clue::FILTER_AFTEREQUALS:
                    $this->after[$typeId] = $this->getBiggerClueValue($this->after[$typeId], $clue);
                    break;
            }
        }

        array_walk($this->whitelists, [$this, 'listSanitizingCallback']);
        array_walk($this->blacklists, [$this, 'listSanitizingCallback']);

        // @todo what if default year is blacklisted?
        if (empty($this->whitelists['y'])) {
            $this->whitelists['y'][] = (new Clue())->setY($this->defaultYear);
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
     * Reset lists from potential previous runs, initialize them once to avoid repeated checks later
     */
    protected function initializeLists() : void
    {
        $this->whitelists = $this->blacklists = $this->before = $this->after = [];

        $allUnits = array_keys(Config::$compoundUnits);
        foreach ($allUnits as $unit) {
            $this->whitelists[$unit] = $this->blacklists[$unit] = [];

            $this->before[$unit] = $this->after[$unit] = null;
        }
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
