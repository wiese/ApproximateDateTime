<?php
declare(strict_types=1);

namespace wiese\ApproximateDateTime;

use \DateTimeInterface;
use \DateTime;
use \DateInterval;
use \DateTimeZone;

class Clue {
	public $type;
	public $value;
	public $first;
	public $last;
}
