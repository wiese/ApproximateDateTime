<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

use wiese\ApproximateDateTime\Clue;
use wiese\ApproximateDateTime\Config;
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
        $this->setDefaultYear((int) (new DateTime())->format(DateTimeData::FORMAT_YEAR));
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
     * @return array
     */
    public function getWhitelist(string $unit) : array
    {
        $this->generateFilterLists();

        return $this->whitelists[$unit];
    }

    /**
     * Get options explicitly disallowed for the unit given
     *
     * @param string $unit
     * @return array
     */
    public function getBlacklist(string $unit) : array
    {
        $this->generateFilterLists();

        return $this->blacklists[$unit];
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

        return $this->before[$unit];
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

    protected function generateFilterLists() : void
    {
        if ($this->cachedFilterLists) {
            return;
        }

        // resetting from potential previous run
        $this->whitelists = $this->blacklists = $this->before = $this->after = [];

        // initialize once to avoid repeated checks later
        foreach (Config::$units as $unit => $settings) {
            $this->whitelists[$unit] = $this->blacklists[$unit] = [];

            $this->before[$unit] = $this->after[$unit] = null;
        }

        foreach ($this as $clue) {
            // @todo validate value

            switch ($clue->filter) {
                case Clue::FILTER_WHITELIST:
                    $this->whitelists[$clue->type][] = $clue->value;
                    break;
                case Clue::FILTER_BLACKLIST:
                    $this->blacklists[$clue->type][] = $clue->value;
                    break;
                case Clue::FILTER_BEFOREEQUALS:
                    if (is_array($clue->value)) {
                        foreach ($clue->value as $unit => $value) {
                            // hoops as null always wins min()
                            if (is_null($this->before[$unit])) {
                                $this->before[$unit] = $value;
                            } else {
                                $this->before[$unit] = min($value, $this->before[$unit]);
                            }
                        }
                    } else {
                        if (is_null($this->before[$clue->type])) {
                            $this->before[$clue->type] = $clue->value;
                        } else {
                            $this->before[$clue->type] = min($clue->value, $this->before[$clue->type]);
                        }
                    }

                    break;
                case Clue::FILTER_AFTEREQUALS:
                    if (is_array($clue->value)) {
                        foreach ($clue->value as $unit => $value) {
                            $this->after[$unit] = max($value, $this->after[$unit]);
                        }
                    } else {
                        $this->after[$clue->type] = max($clue->value, $this->after[$clue->type]);
                    }
                    break;
            }
        }

        $sanitizeArray = function (& $value) {
            array_unique($value, SORT_REGULAR);
            sort($value); // list in order of values
        };

        array_walk($this->whitelists, $sanitizeArray);
        array_walk($this->blacklists, $sanitizeArray);

        // @todo what if default year is blacklisted?
        if (empty($this->whitelists['y'])) {
            $this->whitelists['y'][] = $this->defaultYear;
        }

        $this->cachedFilterLists = true;
    }
}
