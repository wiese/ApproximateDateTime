<?php
declare(strict_types=1);

namespace wiese\ApproximateDateTime;

use \DateTimeInterface;

class Clue {
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
	 * @var DateTimeInterface
	 */
	public $first;
	/**
	 * 
	 * @var DateTimeInterface
	 */
	public $last;
}
