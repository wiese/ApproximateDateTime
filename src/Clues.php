<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

use wiese\ApproximateDateTime\Clue;
use wiese\ApproximateDateTime\Config;
use ArrayObject;
use DateTime;

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

    public function getWhitelist(string $unit) : array
    {
        $this->generateFilterLists();

        return $this->whitelists[$unit];
    }

    public function getBlacklist(string $unit) : array
    {
        $this->generateFilterLists();

        return $this->blacklists[$unit];
    }

    public static function fromArray(array $clues)
    {
        $instance = new self();
        $instance->exchangeArray($clues);

        return $instance;
    }

    protected function generateFilterLists() : void
    {
        if ($this->cachedFilterLists) {
            return;
        }

        $this->whitelists = $this->blacklists = [];

        foreach (array_keys(Config::$units) as $unit) {
            $this->whitelists[$unit] = [];
            $this->blacklists[$unit] = [];
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
            }
        }

        $sanitizeArray = function (& $value) {
            array_unique($value, SORT_REGULAR);
            sort($value); // list in order of values
        };

        array_walk($this->whitelists, $sanitizeArray);
        array_walk($this->blacklists, $sanitizeArray);

        if (empty($this->whitelists['y'])) {
            $this->whitelists['y'][] = $this->defaultYear;
        }

        $this->cachedFilterLists = true;
    }
}
