<?php
declare(strict_types=1);

namespace wiese\ApproximateDateTime\Tests;

use \wiese\ApproximateDateTime\ApproximateDateTime;
use \wiese\ApproximateDateTime\ClueParser;
use \wiese\ApproximateDateTime\Clue;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use DateTime;
use DatePeriod;
use DateInterval;
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

    public function testOneLeapYear()
    {
        $sut = new ApproximateDateTime();

        // '1985-??-??T??-??-??'
        $clue = new Clue;
        $clue->type = 'Y';
        $clue->value = 2016;

        $sut->setClues([$clue]);
        $this->assertEquals(new DateTimeZone('UTC'), $sut->getTimezone());
        $this->assertEquals(new DateTime('2016-01-01 00:00:00', $this->tz), $sut->getEarliest());
        $this->assertEquals(new DateTime('2016-12-31 23:59:59', $this->tz), $sut->getLatest());

        $luckyShot = $sut->getLuckyShot();
        $this->assertEquals(new DateTime('2016-01-01 00:00:00', $this->tz), $luckyShot);
        $this->assertTrue($sut->isPossible($luckyShot));

        $this->assertTrue($sut->isPossible(new DateTime('2016-04-03', $this->tz)));
        $this->assertTrue($sut->isPossible(new DateTime('2016-02-29', $this->tz)));
        $this->assertFalse($sut->isPossible(new DateTime('2017-01-02', $this->tz)));
        $this->assertFalse($sut->isPossible(new DateTime('2015-12-31', $this->tz)));

        $this->assertEquals(365, $sut->getInterval()->days);
        $this->assertEquals(23, $sut->getInterval()->h);
        $this->assertEquals(59, $sut->getInterval()->i);
        $this->assertEquals(59, $sut->getInterval()->s);

        $this->markTestIncomplete('2y not iterable w/ current algorithm');
        return;

        $actualPeriods = $sut->getPeriods();
        $this->assertCount(1, $actualPeriods);
        $this->assertEquals(new DateTime('2014-01-01 00:00:00', $this->tz), $actualPeriods[0]->getStartDate());
        $actualInterval = $actualPeriods[0]->getDateInterval();
        $this->assertEquals(0, $actualInterval->y);
        $this->assertEquals(11, $actualInterval->m);
        $this->assertEquals(30, $actualInterval->d);
        $this->assertEquals(23, $actualInterval->h);
        $this->assertEquals(59, $actualInterval->i);
        $this->assertEquals(59, $actualInterval->s);
        $this->assertEquals(365, $actualInterval->days);
    }

    public function testTwoYears()
    {
        $sut = new ApproximateDateTime();

        // '1985-??-??T??-??-??'
        // '1986-??-??T??-??-??'
        $clue1= new Clue;
        $clue1->type = 'Y';
        $clue1->value = 1985;

        $clue2 = new Clue;
        $clue2->type = 'Y';
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

        $this->markTestIncomplete('2y not iterable w/ current algorithm');
        return;

        $actualPeriods = $sut->getPeriods();
        $this->assertCount(1, $actualPeriods);
        $this->assertEquals(new DateTime('1985-01-01 00:00:00', $this->tz), $actualPeriods[0]->getStartDate());
        $actualInterval = $actualPeriods[0]->getDateInterval();
        $this->assertEquals(1, $actualInterval->y);
        $this->assertEquals(11, $actualInterval->m);
        $this->assertEquals(30, $actualInterval->d);
        $this->assertEquals(23, $actualInterval->h);
        $this->assertEquals(59, $actualInterval->i);
        $this->assertEquals(59, $actualInterval->s);
        $this->assertEquals(729, $actualInterval->days);
    }

    public function testSimpleRealworldExample()
    {
        $sut = new ApproximateDateTime();

        $clue1= new Clue;
        $clue1->type = 'Y';
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

        $actualPeriods = $sut->getPeriods();
        $this->assertCount(1, $actualPeriods);
        $this->assertEquals(new DateTime('2001-03-01 00:00:00', $this->tz), $actualPeriods[0]->getStartDate());
        $actualInterval = $actualPeriods[0]->getDateInterval();
        $this->assertEquals(1, $actualInterval->m);
        $this->assertEquals(29, $actualInterval->d);
        $this->assertEquals(23, $actualInterval->h);
        $this->assertEquals(59, $actualInterval->i);
        $this->assertEquals(59, $actualInterval->s);
        $this->assertEquals(60, $actualInterval->days);

        if ($sut->periodDeterminationLoops >= 5270400) {
            $this->fail('Inefficient algorithm. Seems to check every second.');
        }
    }

    public function testRealworldExample()
    {
        $sut = new ApproximateDateTime();

        $clue1= new Clue;
        $clue1->type = 'Y';
        $clue1->value = 2010;

        $clue2 = new Clue;
        $clue2->type = 'm';
        $clue2->value = 3;

        $clue3 = new Clue;
        $clue3->type = 'd';
        $clue3->value = 28;

        $clue4 = new Clue;
        $clue4->type = 'd';
        $clue4->value = 30;

        $sut->setClues([$clue1, $clue2, $clue3, $clue4]);

        $this->assertEquals(new DateTime('2010-03-28 00:00:00', $this->tz), $sut->getEarliest());
        $this->assertEquals(new DateTime('2010-03-30 23:59:59', $this->tz), $sut->getLatest());

        //$this->assertEquals(1, $sut->getInterval()->days);
        $this->assertEquals(23, $sut->getInterval()->h);
        $this->assertEquals(59, $sut->getInterval()->i);
        $this->assertEquals(59, $sut->getInterval()->s);

        $this->assertEquals(
            [
                new DatePeriod(new DateTime('2010-03-28 00:00:00', $this->tz), new DateInterval('PT23H59M59S'), 1),
                new DatePeriod(new DateTime('2010-03-30 00:00:00', $this->tz), new DateInterval('PT23H59M59S'), 1)
            ],
            $sut->getPeriods()
        );

        if ($sut->periodDeterminationLoops >= 259200) {
            $this->fail('Inefficient algorithm. Seems to check every second.');
        }
    }

    public function testNotSoApproximate()
    {
        $sut = new ApproximateDateTime();

        // '1985-01-23T07-11-32'
        $clue1= new Clue;
        $clue1->type = 'Y';
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

        $this->assertEquals(
            [
                new DatePeriod(new DateTime('1985-01-23 07:11:32', $this->tz), new DateInterval('PT0S'), 1),
            ],
            $sut->getPeriods()
        );
    }

    public function testMiniExample()
    {
        $sut = new ApproximateDateTime();

        $clue1= new Clue;
        $clue1->type = 'Y';
        $clue1->value = 2007;

        $clue2 = new Clue;
        $clue2->type = 'm';
        $clue2->value = 8;

        $clue3 = new Clue;
        $clue3->type = 'd';
        $clue3->value = 15;

        $clue4 = new Clue;
        $clue4->type = 'h';
        $clue4->value = 9;

        $clue5 = new Clue;
        $clue5->type = 'i';
        $clue5->value = 34;

        $clue6 = new Clue;
        $clue6->type = 'i';
        $clue6->value = 36;

        $clue7 = new Clue;
        $clue7->type = 'i';
        $clue7->value = 39;

        $sut->setClues([$clue1, $clue2, $clue3, $clue4, $clue5, $clue6, $clue7]);

        $this->assertEquals(
            [
                new DatePeriod(new DateTime('2007-08-15 09:34:00', $this->tz), new DateInterval('PT59S'), 1),
                new DatePeriod(new DateTime('2007-08-15 09:36:00', $this->tz), new DateInterval('PT59S'), 1),
                new DatePeriod(new DateTime('2007-08-15 09:39:00', $this->tz), new DateInterval('PT59S'), 1),
            ],
            $sut->getPeriods()
        );

        if ($sut->periodDeterminationLoops >= 180) {
            $this->fail('Inefficient algorithm. Seems to check every second.');
        }
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
        $this->assertCount(45, $sut->getPeriods());
        $this->assertEquals(new DateTime('2001-01-02 00:00:00', $this->tz), $sut->getEarliest());
        $this->assertEquals(new DateTime('2001-12-25 23:59:59', $this->tz), $sut->getLatest());
    }
}
