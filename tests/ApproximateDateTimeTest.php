<?php
declare(strict_types=1);

namespace wiese\ApproximateDateTime\Tests;

use \wiese\ApproximateDateTime\ApproximateDateTime;
use \wiese\ApproximateDateTime\ClueParser;
use \wiese\ApproximateDateTime\Clue;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use DateTime;
use DateTimeZone;

class ApproximateDateTimeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var DateTimeZone
     */
    protected $tz;

    public function setUp()
    {
        $this->tz = new DateTimeZone('UTC');
    }

    public function testDefault()
    {
        $sut = new ApproximateDateTime();

        $this->assertEquals(new DateTimeZone('UTC'), $sut->getTimezone());
        $this->assertEquals(new DateTime(date('Y') . '-01-01 00:00:00', $this->tz), $sut->getEarliest());
        $this->assertEquals(new DateTime(date('Y') . '-12-31 23:59:59', $this->tz), $sut->getLatest());

        $sut->setDefaultYear(333);

        $this->assertEquals(new DateTime('333-01-01 00:00:00', $this->tz), $sut->getEarliest());
        $this->assertEquals(new DateTime('333-12-31 23:59:59', $this->tz), $sut->getLatest());

        $europeTz = new DateTimeZone('Europe/Berlin');
        $sut->setTimezone($europeTz);
        $this->assertEquals(new DateTime('333-01-01 00:00:00', $europeTz), $sut->getEarliest());
        $this->assertEquals(new DateTime('333-12-31 23:59:59', $europeTz), $sut->getLatest());
    }

    public function testOneYear()
    {
        $sut = new ApproximateDateTime();

        // '1985-??-??T??-??-??'
        $clue = new Clue;
        $clue->type = 'y';
        $clue->value = 1985;

        $sut->setClues([$clue]);
        $this->assertEquals(new DateTimeZone('UTC'), $sut->getTimezone());
        $this->assertEquals(new DateTime('1985-01-01 00:00:00', $this->tz), $sut->getEarliest());
        $this->assertEquals(new DateTime('1985-12-31 23:59:59', $this->tz), $sut->getLatest());

        $luckyShot = $sut->getLuckyShot();
        $this->assertEquals(new DateTime('1985-01-01 00:00:00', $this->tz), $luckyShot);
        $this->assertTrue($sut->isPossible($luckyShot));

        $this->assertTrue($sut->isPossible(new DateTime('1985-04-03', $this->tz)));
        $this->assertFalse($sut->isPossible(new DateTime('1986-01-02', $this->tz)));
        $this->assertFalse($sut->isPossible(new DateTime('1984-12-31', $this->tz)));

        $this->assertEquals(364, $sut->getInterval()->days);
        $this->assertEquals(23, $sut->getInterval()->h);
        $this->assertEquals(59, $sut->getInterval()->i);
        $this->assertEquals(59, $sut->getInterval()->s);
    }

    public function testTwoYears()
    {
        $sut = new ApproximateDateTime();

        // '1985-??-??T??-??-??'
        // '1986-??-??T??-??-??'
        $clue1= new Clue;
        $clue1->type = 'y';
        $clue1->value = 1985;

        $clue2 = new Clue;
        $clue2->type = 'y';
        $clue2->value = 1986;

        $sut->setClues([$clue1, $clue2]);

        $this->assertEquals(new DateTime('1985-01-01 00:00:00', $this->tz), $sut->getEarliest());
        $this->assertEquals(new DateTime('1986-12-31 23:59:59', $this->tz), $sut->getLatest());

        $luckyShot = $sut->getLuckyShot();
        $this->assertEquals(new DateTime('1985-01-01 00:00:00', $this->tz), $luckyShot);
        $this->assertTrue($sut->isPossible($luckyShot));

        $this->assertTrue($sut->isPossible(new DateTime('1985-04-03', $this->tz)));
        $this->assertTrue($sut->isPossible(new DateTime('1986-12-31', $this->tz)));
        $this->assertFalse($sut->isPossible(new DateTime('1984-01-03', $this->tz)));
        $this->assertFalse($sut->isPossible(new DateTime('1990-07-12', $this->tz)));

        $this->assertEquals(729, $sut->getInterval()->days);
        $this->assertEquals(23, $sut->getInterval()->h);
        $this->assertEquals(59, $sut->getInterval()->i);
        $this->assertEquals(59, $sut->getInterval()->s);
    }

    public function testSimpleRealworldExample()
    {
        $sut = new ApproximateDateTime();

        $clue1= new Clue;
        $clue1->type = 'y';
        $clue1->value = 2001;

        $clue2 = new Clue;
        $clue2->type = 'm';
        $clue2->value = 3;

        $clue3 = new Clue;
        $clue3->type = 'm';
        $clue3->value = 4;

        $sut->setClues([$clue1, $clue2, $clue3]);

        $this->assertEquals(new DateTime('2001-03-01 00:00:00', $this->tz), $sut->getEarliest());
        $this->assertEquals(new DateTime('2001-04-30 23:59:59', $this->tz), $sut->getLatest());

        $this->assertEquals(60, $sut->getInterval()->days);
        $this->assertEquals(23, $sut->getInterval()->h);
        $this->assertEquals(59, $sut->getInterval()->i);
        $this->assertEquals(59, $sut->getInterval()->s);
    }

    public function testNotSoApproximate()
    {
        $sut = new ApproximateDateTime();

        // '1985-01-23T07-11-32'
        $clue1= new Clue;
        $clue1->type = 'y';
        $clue1->value = 1985;

        $clue2 = new Clue;
        $clue2->type = 'm';
        $clue2->value = 1;

        $clue3 = new Clue;
        $clue3->type = 'd';
        $clue3->value = 23;

        $clue4 = new Clue;
        $clue4->type = 'h';
        $clue4->value = 7;

        $clue5 = new Clue;
        $clue5->type = 'i';
        $clue5->value = 11;

        $clue6 = new Clue;
        $clue6->type = 's';
        $clue6->value = 32;

        $sut->setClues([$clue1, $clue2, $clue3, $clue4, $clue5, $clue6]);

        $this->assertEquals(new DateTime('1985-01-23 07:11:32', $this->tz), $sut->getEarliest());
        $this->assertEquals(new DateTime('1985-01-23 07:11:32', $this->tz), $sut->getLatest());

        $this->assertEquals(0, $sut->getInterval()->days);
        $this->assertEquals(0, $sut->getInterval()->h);
        $this->assertEquals(0, $sut->getInterval()->i);
        $this->assertEquals(0, $sut->getInterval()->s);
    }

    public function testWinterHolidayPicture()
    {
        $this->markTestIncomplete();

        $sut = new ApproximateDateTime();

        $parser = new ClueParser();
        // '2016-??-??T??-??-??'
        $parser->addClue('2016');
        // '????-??-??T(12,13,14,15,16,17,18)-??-??'
        $parser->addClue('afternoon');
        // '????-(01,02,03)-??T??-??-??'
        $parser->addClue('<April');
        // '????-02-04T??-??-??'
        $parser->addClue('>February-04');
        $parser->addClue('!2016-03-14');
        $parser->addClue('Weekend');

        $this->assertEquals($sut, $sut->setClues($parser->getProcessedClues()));
        $this->assertCount(6, $sut->getClues());
        $this->assertEquals(new DateTime('2016-02-05 00:00:00', $this->tz), $sut->getEarliest());
        $this->assertEquals(new DateTime('2016-13-31 23:59:59', $this->tz), $sut->getLatest());
    }

    public function testWorkday()
    {
        $this->markTestIncomplete();

        $sut = new ApproximateDateTime();

        $parser = new ClueParser();
        $parser->addClue('2001');
        $parser->addClue('Tuesday');
        $parser->addClue('!October');
        // $parser->addClue('Summer'); // boo - needs geo-awareness

        $this->assertEquals($sut, $sut->setClues($parser->getProcessedClues()));
        $this->assertCount(45, $sut->getPossibilites());
        $this->assertEquals(new DateTime('2001-01-02 00:00:00', $this->tz), $sut->getEarliest());
        $this->assertEquals(new DateTime('2001-12-25 23:59:59', $this->tz), $sut->getLatest());
    }
}
