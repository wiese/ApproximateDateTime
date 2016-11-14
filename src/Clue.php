<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime;

use \DateTimeInterface;

class Clue
{
    const FILTER_WHITELIST = 1;
    const FILTER_BLACKLIST = 2;

    /**
     *
     * @var string
     */
    public $type;
    /**
     *
     * @var mixed
     */
    public $value;
    /**
     *
     * @var mixed
     */
    public $rawValue;
    /**
     *
     * @var int
     */
    public $filter = self::FILTER_WHITELIST;
}
