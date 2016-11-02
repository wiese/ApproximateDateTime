<?php
declare(strict_types=1);

namespace wiese\ApproximateDateTime\Tests;

use wiese\ApproximateDateTime\ApproximateDateTime;
use wiese\ApproximateDateTime\ClueParser;
use wiese\ApproximateDateTime\Clue;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use DateTime;
use DateTimeZone;

class ClueParserTest extends PHPUnit_Framework_TestCase {

	public function testCastClue() {
		$sut = new ClueParser();
	
		$class = new ReflectionClass($sut);
		$method = $class->getMethod('processClue');
		$method->setAccessible(true);
	
		$clue = new Clue;
		$clue->type = 'y';
		$clue->value = 2014;
		$clue->first = new DateTime("2014-01-01T00:00:00+0000");
		$clue->last = new DateTime("2014-12-31T23:59:59+0000");
	
		$this->assertEquals($clue, $method->invoke($sut, '2014'));

	}

	public function testAddClue() {
		$sut = new ClueParser();
		$this->assertEquals($sut, $sut->addClue('1985'));
		$this->assertEquals(['1985'], $sut->getClues());
	}
}
