<?php
declare(strict_types=1);

namespace wiese\ApproximateDateTime\Tests;

use \wiese\ApproximateDateTime\ApproximateDateTime;
use \wiese\ApproximateDateTime\Clue;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use DateTime;

class ApproximateDateTimeTest extends PHPUnit_Framework_TestCase {

	public function testCastClue() {
		$sut = new ApproximateDateTime();

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


	public function testYearOnly() {
		$sut = new ApproximateDateTime();
		$this->assertEquals($sut, $sut->addClue('1985'));
		$this->assertEquals(['1985'], $sut->getClues());
		$this->assertEquals(new DateTime('1985-01-01 00:00:00'), $sut->getEarliest());
		$this->assertEquals(new DateTime('1985-12-31 23:59:59'), $sut->getLatest());
		$luckyShot = $sut->getLuckyShot();
		$this->assertEquals(new DateTime('1985-01-01 00:00:00'), $luckyShot);
		$this->assertTrue($sut->isPossible($luckyShot));
		$this->assertTrue($sut->isPossible(new DateTime('1985-04-03')));
		$this->assertFalse($sut->isPossible(new DateTime('1986-12-31')));
		$this->assertEquals(364, $sut->getInterval()->days);
		$this->assertEquals(23, $sut->getInterval()->h);
		$this->assertEquals(59, $sut->getInterval()->i);
		$this->assertEquals(59, $sut->getInterval()->s);
	}
/*

	public function testDefault() {
		$sut = new ApproximateDateTime\ApproximateDateTime();
		$this->assertEquals([new DateTime('today', new DateTimeZone('UTC'))], $sut->getPossibilites());
	}

	public function testWinterPicture() {
		$sut = new ApproximateDate();
		$sut->addClue('2016');
		$sut->addClue('Weekend');
		$sut->addClue('afternoon');
		$sut->addClue('<April');
		$sut->addClue('>February-04');
		$sut->addClue('!2016-03-14');
	}

	public function testMoreClues() {
		$sut = new ApproximateDate();
		$sut->addClue('!1985-03');
		// $sut->addClue('Summer'); // boo - needs geo-awareness
		$sut->addClue('!May');
		$sut->addClue('Thursday');
		$sut->addClue('Wednesday');
	}
*/
}

