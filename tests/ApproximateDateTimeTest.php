<?php
declare(strict_types=1);

namespace wiese\ApproximateDateTime\Tests;

use \wiese\ApproximateDateTime\ApproximateDateTime;
use \wiese\ApproximateDateTime\Clue;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use DateTime;
use DateTimeZone;

class ApproximateDateTimeTest extends PHPUnit_Framework_TestCase {

	public function testOneYear() {
		$sut = new ApproximateDateTime();
		
		$clue = $clue = new Clue;
		$clue->type = 'y';
		$clue->value = 1985;
		$clue->first = new DateTime("1985-01-01T00:00:00+0000");
		$clue->last = new DateTime("1985-12-31T23:59:59+0000");

		$sut->setClues([$clue]);
		$this->assertEquals(new DateTimeZone('UTC'), $sut->getTimezone());
		$this->assertEquals(new DateTime('1985-01-01 00:00:00'), $sut->getEarliest());
		$this->assertEquals(new DateTime('1985-12-31 23:59:59'), $sut->getLatest());
		$luckyShot = $sut->getLuckyShot();
		$this->assertEquals(new DateTime('1985-01-01 00:00:00'), $luckyShot);
		$this->assertTrue($sut->isPossible($luckyShot));
		$this->assertTrue($sut->isPossible(new DateTime('1985-04-03')));
		$this->assertFalse($sut->isPossible(new DateTime('1986-01-02')));
		$this->assertFalse($sut->isPossible(new DateTime('1984-12-31')));
		$this->assertEquals(364, $sut->getInterval()->days);
		$this->assertEquals(23, $sut->getInterval()->h);
		$this->assertEquals(59, $sut->getInterval()->i);
		$this->assertEquals(59, $sut->getInterval()->s);
	}
	
	public function testTwoYears() {
		$sut = new ApproximateDateTime();
		
		$clue1= new Clue;
		$clue1->type = 'y';
		$clue1->value = 1985;
		$clue1->first = new DateTime("1985-01-01T00:00:00+0000");
		$clue1->last = new DateTime("1985-12-31T23:59:59+0000");
		
		$clue2 = $clue = new Clue;
		$clue2->type = 'y';
		$clue2->value = 1986;
		$clue2->first = new DateTime("1986-01-01T00:00:00+0000");
		$clue2->last = new DateTime("1986-12-31T23:59:59+0000");
		
		$sut->setClues([$clue1, $clue2]);
		$this->assertEquals(new DateTime('1985-01-01 00:00:00'), $sut->getEarliest());
		$this->assertEquals(new DateTime('1986-12-31 23:59:59'), $sut->getLatest());
		$luckyShot = $sut->getLuckyShot();
		$this->assertEquals(new DateTime('1985-01-01 00:00:00'), $luckyShot);
		$this->assertTrue($sut->isPossible($luckyShot));
		$this->assertTrue($sut->isPossible(new DateTime('1985-04-03')));
		$this->assertTrue($sut->isPossible(new DateTime('1986-12-31')));
		$this->assertFalse($sut->isPossible(new DateTime('1984-01-03')));
		$this->assertFalse($sut->isPossible(new DateTime('1990-07-12')));
		$this->assertEquals(729, $sut->getInterval()->days);
		$this->assertEquals(23, $sut->getInterval()->h);
		$this->assertEquals(59, $sut->getInterval()->i);
		$this->assertEquals(59, $sut->getInterval()->s);
	}

	public function testWinterHolidayPicture() {
		$sut = new ApproximateDateTime();
		$this->assertEquals($sut, $sut->addClue('2016'));
		$this->assertEquals($sut, $sut->addClue('Weekend'));
		$this->assertEquals($sut, $sut->addClue('afternoon'));
		$this->assertEquals($sut, $sut->addClue('<April'));
		$this->assertEquals($sut, $sut->addClue('>February-04'));
		$this->assertEquals($sut, $sut->addClue('!2016-03-14'));
		$this->assertCount(6, $sut->getClues());
		$this->assertEquals(new DateTime('2016-02-05 00:00:00'), $sut->getEarliest());
		$this->assertEquals(new DateTime('2016-13-31 23:59:59'), $sut->getLatest());
	}

	public function testWorkday() {
		$sut = new ApproximateDateTime();
		$sut->addClue('2001');
		$sut->addClue('Tuesday');
		$sut->addClue('!October');
		// $sut->addClue('Summer'); // boo - needs geo-awareness
		$this->assertCount(45, $sut->getPossibilites());
		$this->assertEquals(new DateTime('2001-01-02 00:00:00'), $sut->getEarliest());
		$this->assertEquals(new DateTime('2001-12-25 23:59:59'), $sut->getLatest());
	}
}

