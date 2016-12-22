<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests;

use wiese\ApproximateDateTime\ApproximateDateTime;
use wiese\ApproximateDateTime\Clue;
use wiese\ApproximateDateTime\ClueParser;
use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeZone;
use PHPUnit_Framework_TestCase;

class ApproximateDateTimeTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var DateTimeZone
     */
    protected $tz;

    /**
     * @var ApproximateDateTime
     */
    protected $sut;

    public function setUp() : void
    {
        $this->tz = new DateTimeZone('UTC');

        $this->sut = new ApproximateDateTime();
    }

    public function testDefault() : void
    {
        $this->assertEquals(new DateTimeZone('UTC'), $this->sut->getTimezone());
        $this->assertEquals(new DateTime(date('Y') . '-01-01 00:00:00', $this->tz), $this->sut->getEarliest());
        $this->assertEquals(new DateTime(date('Y') . '-12-31 23:59:59', $this->tz), $this->sut->getLatest());

        $this->sut->setDefaultYear(333);

        $this->assertEquals(new DateTime('333-01-01 00:00:00', $this->tz), $this->sut->getEarliest());
        $this->assertEquals(new DateTime('333-12-31 23:59:59', $this->tz), $this->sut->getLatest());

        $europeTz = new DateTimeZone('Europe/Berlin');
        $this->assertEquals($this->sut, $this->sut->setTimezone($europeTz));
        $this->assertEquals($europeTz, $this->sut->getTimezone());
        $this->assertEquals(new DateTime('333-01-01 00:00:00', $europeTz), $this->sut->getEarliest());
        $this->assertEquals(new DateTime('333-12-31 23:59:59', $europeTz), $this->sut->getLatest());
    }

    public function testOneLeapYear() : void
    {
        // '2016-??-??T??-??-??'
        $clue = new Clue;
        $clue->setY(2016);

        $this->sut->setClues([$clue]);
        $this->assertEquals(new DateTimeZone('UTC'), $this->sut->getTimezone());
        $this->assertEquals(new DateTime('2016-01-01 00:00:00', $this->tz), $this->sut->getEarliest());
        $this->assertEquals(new DateTime('2016-12-31 23:59:59', $this->tz), $this->sut->getLatest());

        $luckyShot = $this->sut->getLuckyShot();
        $this->assertEquals(new DateTime('2016-01-01 00:00:00', $this->tz), $luckyShot);
        $this->assertTrue($this->sut->isPossible($luckyShot));

        $this->assertTrue($this->sut->isPossible(new DateTime('2016-04-03', $this->tz)));
        $this->assertTrue($this->sut->isPossible(new DateTime('2016-02-29', $this->tz)));
        $this->assertFalse($this->sut->isPossible(new DateTime('2017-01-02', $this->tz)));
        $this->assertFalse($this->sut->isPossible(new DateTime('2015-12-31', $this->tz)));

        $actualPeriods = $this->sut->getPeriods();

        $this->assertCount(1, $actualPeriods);
        $this->assertEquals(new DateTime('2016-01-01 00:00:00', $this->tz), $actualPeriods[0]->getStartDate());
        $actualInterval = $actualPeriods[0]->getDateInterval();
        $this->assertEquals(0, $actualInterval->y);
        $this->assertEquals(11, $actualInterval->m);
        $this->assertEquals(30, $actualInterval->d);
        $this->assertEquals(23, $actualInterval->h);
        $this->assertEquals(59, $actualInterval->i);
        $this->assertEquals(59, $actualInterval->s);
        $this->assertEquals(365, $actualInterval->days);
    }

    public function testTwoYears() : void
    {
        // '1985-??-??T??-??-??'
        // '1986-??-??T??-??-??'
        $clue1 = new Clue;
        $clue1->setY(1985);

        $clue2 = new Clue;
        $clue2->setY(1986);

        $this->sut->setClues([$clue1, $clue2]);

        $this->assertEquals(new DateTime('1985-01-01 00:00:00', $this->tz), $this->sut->getEarliest());
        $this->assertEquals(new DateTime('1986-12-31 23:59:59', $this->tz), $this->sut->getLatest());

        $luckyShot = $this->sut->getLuckyShot();
        $this->assertEquals(new DateTime('1985-01-01 00:00:00', $this->tz), $luckyShot);
        $this->assertTrue($this->sut->isPossible($luckyShot));

        $this->assertTrue($this->sut->isPossible(new DateTime('1985-04-03', $this->tz)));
        $this->assertTrue($this->sut->isPossible(new DateTime('1986-12-31', $this->tz)));
        $this->assertFalse($this->sut->isPossible(new DateTime('1984-01-03', $this->tz)));
        $this->assertFalse($this->sut->isPossible(new DateTime('1990-07-12', $this->tz)));

        $actualPeriods = $this->sut->getPeriods();
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

    public function testSimpleRealworldExample() : void
    {
        $clue1 = new Clue;
        $clue1->setY(2001);

        $clue2 = new Clue;
        $clue2->setM(3);

        $clue3 = new Clue;
        $clue3->setM(4);

        $this->sut->setClues([$clue1, $clue2, $clue3]);

        $this->assertEquals(new DateTime('2001-03-01 00:00:00', $this->tz), $this->sut->getEarliest());
        $this->assertEquals(new DateTime('2001-04-30 23:59:59', $this->tz), $this->sut->getLatest());

        $actualPeriods = $this->sut->getPeriods();
        $this->assertCount(1, $actualPeriods);
        $this->assertEquals(new DateTime('2001-03-01 00:00:00', $this->tz), $actualPeriods[0]->getStartDate());
        $actualInterval = $actualPeriods[0]->getDateInterval();
        $this->assertEquals(1, $actualInterval->m);
        $this->assertEquals(29, $actualInterval->d);
        $this->assertEquals(23, $actualInterval->h);
        $this->assertEquals(59, $actualInterval->i);
        $this->assertEquals(59, $actualInterval->s);
        $this->assertEquals(60, $actualInterval->days);
    }

    public function testRealworldExample() : void
    {
        $clue1 = new Clue;
        $clue1->setY(2010);

        $clue2 = new Clue;
        $clue2->setM(3);

        $clue3 = new Clue;
        $clue3->setD(28);

        $clue4 = new Clue;
        $clue4->setD(30);

        $this->sut->setClues([$clue1, $clue2, $clue3, $clue4]);

        $this->assertEquals(new DateTime('2010-03-28 00:00:00', $this->tz), $this->sut->getEarliest());
        $this->assertEquals(new DateTime('2010-03-30 23:59:59', $this->tz), $this->sut->getLatest());

        $this->assertEquals(
            [
                new DatePeriod(new DateTime('2010-03-28 00:00:00', $this->tz), new DateInterval('PT23H59M59S'), 1),
                new DatePeriod(new DateTime('2010-03-30 00:00:00', $this->tz), new DateInterval('PT23H59M59S'), 1)
            ],
            $this->sut->getPeriods()
        );
    }

    public function testRealworldExampleConsecutiveMonth() : void
    {
        $clue1 = new Clue;
        $clue1->setY(2010);

        $clue2 = new Clue;
        $clue2->setM(3);

        $clue3 = new Clue;
        $clue3->setM(4);

        $clue4 = new Clue;
        $clue4->setD(28);

        $clue5 = new Clue;
        $clue5->setD(30);

        $this->sut->setClues([$clue1, $clue2, $clue3, $clue4, $clue5]);

        $this->assertEquals(
            [
                new DatePeriod(new DateTime('2010-03-28 00:00:00', $this->tz), new DateInterval('PT23H59M59S'), 1),
                new DatePeriod(new DateTime('2010-03-30 00:00:00', $this->tz), new DateInterval('PT23H59M59S'), 1),
                new DatePeriod(new DateTime('2010-04-28 00:00:00', $this->tz), new DateInterval('PT23H59M59S'), 1),
                new DatePeriod(new DateTime('2010-04-30 00:00:00', $this->tz), new DateInterval('PT23H59M59S'), 1),
            ],
            $this->sut->getPeriods()
        );
    }

    public function testNotSoApproximate() : void
    {
        // '1985-01-23T07-11-32'
        $clue1 = new Clue;
        $clue1->setY(1985);

        $clue2 = new Clue;
        $clue2->setM(1);

        $clue3 = new Clue;
        $clue3->setD(23);

        $clue4 = new Clue;
        $clue4->setH(7);

        $clue5 = new Clue;
        $clue5->setI(11);

        $clue6 = new Clue;
        $clue6->setS(32);

        $this->sut->setClues([$clue1, $clue2, $clue3, $clue4, $clue5, $clue6]);

        $this->assertEquals(new DateTime('1985-01-23 07:11:32', $this->tz), $this->sut->getEarliest());
        $this->assertEquals(new DateTime('1985-01-23 07:11:32', $this->tz), $this->sut->getLatest());

        $this->assertEquals(
            [
                new DatePeriod(new DateTime('1985-01-23 07:11:32', $this->tz), new DateInterval('PT0S'), 1),
            ],
            $this->sut->getPeriods()
        );
    }

    public function testMiniExample() : void
    {
        $clue1 = new Clue;
        $clue1->setY(2007);

        $clue2 = new Clue;
        $clue2->setM(8);

        $clue3 = new Clue;
        $clue3->setD(15);

        $clue4 = new Clue;
        $clue4->setH(9);

        $clue5 = new Clue;
        $clue5->setI(36);

        $clue6 = new Clue;
        $clue6->setI(34);

        $clue7 = new Clue;
        $clue7->setI(39);

        $this->sut->setClues([$clue1, $clue2, $clue3, $clue4, $clue5, $clue6, $clue7]);

        $this->assertEquals(
            [
                new DatePeriod(new DateTime('2007-08-15 09:34:00', $this->tz), new DateInterval('PT59S'), 1),
                new DatePeriod(new DateTime('2007-08-15 09:36:00', $this->tz), new DateInterval('PT59S'), 1),
                new DatePeriod(new DateTime('2007-08-15 09:39:00', $this->tz), new DateInterval('PT59S'), 1),
            ],
            $this->sut->getPeriods()
        );
    }

    public function testMiniExampleWithDefaultYear() : void
    {
        $clue1 = new Clue;
        $clue1->setM(7);

        $this->sut->setClues([$clue1]);

        $actualPeriods = $this->sut->getPeriods();
        $this->assertCount(1, $actualPeriods);
        $this->assertEquals(new DateTime(date('Y') . '-07-01 00:00:00', $this->tz), $actualPeriods[0]->getStartDate());
        $actualInterval = $actualPeriods[0]->getDateInterval();
        $this->assertEquals(0, $actualInterval->y);
        $this->assertEquals(0, $actualInterval->m);
        $this->assertEquals(30, $actualInterval->d);
        $this->assertEquals(23, $actualInterval->h);
        $this->assertEquals(59, $actualInterval->i);
        $this->assertEquals(59, $actualInterval->s);
        $this->assertEquals(30, $actualInterval->days);
    }

    public function testBlacklist() : void
    {
        $clue1 = new Clue;
        $clue1->setY(2012);

        $clue2 = new Clue;
        $clue2->setM(7);

        $clue3 = new Clue;
        $clue3->filter = Clue::FILTER_BLACKLIST;
        $clue3->setD(1);

        $this->sut->setClues([$clue1, $clue2, $clue3]);

        $this->assertEquals(new DateTime('2012-07-02 00:00:00', $this->tz), $this->sut->getEarliest());
        $this->assertEquals(new DateTime('2012-07-31 23:59:59', $this->tz), $this->sut->getLatest());

        $this->assertCount(1, $this->sut->getPeriods());
    }

    public function testComplexBlacklist() : void
    {
        $clue1 = new Clue;
        $clue1->setY(2018);

        $clue2 = new Clue;
        $clue2->setM(7);

        $clue3 = new Clue;
        $clue3->setM(8);

        $clue4 = new Clue;
        $clue4->setM(9);

        $clue5 = new Clue;
        $clue5->filter = Clue::FILTER_BLACKLIST;
        $clue5->setM(8);

        $clue6 = new Clue;
        $clue6->filter = Clue::FILTER_BLACKLIST;
        $clue6->setD(1);

        $clue7 = new Clue;
        $clue7->filter = Clue::FILTER_BLACKLIST;
        $clue7->setD(30);

        $this->sut->setClues([$clue1, $clue2, $clue3, $clue4, $clue5, $clue6, $clue7]);

        $periods = $this->sut->getPeriods();

        $this->assertCount(3, $periods);
        $this->assertEquals(new DateTime('2018-07-02 00:00:00', $this->tz), $periods[0]->getStartDate());
        $this->assertEquals(new DateTime('2018-07-31 00:00:00', $this->tz), $periods[1]->getStartDate());
        $this->assertEquals(new DateTime('2018-09-02 00:00:00', $this->tz), $periods[2]->getStartDate());
    }

    public function testWeekday() : void
    {
        $clue1 = new Clue;
        $clue1->setY(2017);

        $clue2 = new Clue;
        $clue2->setM(10);

        $clue3 = new Clue;
        $clue3->filter = Clue::FILTER_BLACKLIST;
        $clue3->setD(14);

        $clue4 = new Clue;
        $clue4->setN(6);

        $this->sut->setClues([$clue1, $clue2, $clue3, $clue4]);

        $periods = $this->sut->getPeriods();

        $this->assertCount(3, $periods);
        $this->assertEquals(new DateTime('2017-10-07 00:00:00', $this->tz), $periods[0]->getStartDate());
        $this->assertEquals(new DateTime('2017-10-21 00:00:00', $this->tz), $periods[1]->getStartDate());
        $this->assertEquals(new DateTime('2017-10-28 00:00:00', $this->tz), $periods[2]->getStartDate());
    }

    public function testWorkday() : void
    {
        $clue1 = new Clue;
        $clue1->setY(2001);

        $clue2 = new Clue;
        $clue2->filter = Clue::FILTER_BLACKLIST;
        $clue2->setM(10);

        $clue3 = new Clue;
        $clue3->filter = Clue::FILTER_BLACKLIST;
        $clue3->setN(6);

        $clue4 = new Clue;
        $clue4->filter = Clue::FILTER_BLACKLIST;
        $clue4->setN(7);

        $this->assertEquals($this->sut, $this->sut->setClues([$clue1, $clue2, $clue3, $clue4]));
        $periods = $this->sut->getPeriods();

        $this->assertCount(49, $periods);
        $this->assertEquals(new DateTime('2001-01-01 00:00:00', $this->tz), $this->sut->getEarliest());
        $this->assertEquals(new DateTime('2001-12-31 23:59:59', $this->tz), $this->sut->getLatest());
    }

    public function testTrickyWeekdays() : void
    {
        $clue1 = new Clue;
        $clue1->setY(2001);

        $clue2 = new Clue;
        $clue2->setM(3);

        $clue3 = new Clue;
        $clue3->setN(5);

        $clue4 = new Clue;
        $clue4->setN(6);

        $clue5 = new Clue;
        $clue5->setN(7);

        $this->assertEquals($this->sut, $this->sut->setClues([$clue1, $clue2, $clue3, $clue4, $clue5]));
        $periods = $this->sut->getPeriods();

        $this->assertCount(5, $periods);

        // @todo DatePeriod wrapper with direct access to end, calculated from start and interval?

        $period = $periods[0];

        $this->assertEquals(new DateTime('2001-03-02 00:00:00', $this->tz), $period->getStartDate());
        $this->assertEquals(
            new DateTime('2001-03-04 23:59:59', $this->tz),
            $period->getStartDate()->add($period->getDateInterval())
        );

        $period = $periods[2];
        $this->assertEquals(new DateTime('2001-03-16 00:00:00', $this->tz), $period->getStartDate());
        $this->assertEquals(
            new DateTime('2001-03-18 23:59:59', $this->tz),
            $period->getStartDate()->add($period->getDateInterval())
        );

        $period = $periods[4];
        $this->assertEquals(new DateTime('2001-03-30 00:00:00', $this->tz), $period->getStartDate());
        $this->assertEquals(
            new DateTime('2001-03-31 23:59:59', $this->tz),
            $period->getStartDate()->add($period->getDateInterval())
        );
    }

    public function testGenerousWhitelistStillOneRange() : void
    {
        $clues = [];

        $clue = new Clue;
        $clue->setY(2016);
        $clues[] = $clue;

        foreach (range(1, 12) as $month) {
            $clue = new Clue;
            $clue->setM($month);
            $clues[] = $clue;
        }

        $this->sut->setClues($clues);
        $actualPeriods = $this->sut->getPeriods();

        $this->assertCount(1, $actualPeriods, 'after month whitelist');

        foreach (range(1, 31) as $day) {
            $clue = new Clue;
            $clue->setD($day);
            $clues[] = $clue;
        }

        $this->sut->setClues($clues);
        $actualPeriods = $this->sut->getPeriods();
        $this->assertCount(1, $actualPeriods, 'after day whitelist');

        foreach (range(1, 7) as $weekday) {
            $clue = new Clue;
            $clue->setN($weekday);
            $clues[] = $clue;
        }

        $this->sut->setClues($clues);

        $actualPeriods = $this->sut->getPeriods();
        $this->assertCount(1, $actualPeriods, 'after weekday whitelist');

        $this->assertEquals(new DateTime('2016-01-01 00:00:00', $this->tz), $actualPeriods[0]->getStartDate());
        $actualInterval = $actualPeriods[0]->getDateInterval();
        $this->assertEquals(0, $actualInterval->y);
        $this->assertEquals(11, $actualInterval->m);
        $this->assertEquals(30, $actualInterval->d);
        $this->assertEquals(23, $actualInterval->h);
        $this->assertEquals(59, $actualInterval->i);
        $this->assertEquals(59, $actualInterval->s);
        $this->assertEquals(365, $actualInterval->days);
    }

    public function testBeforeDay() : void
    {
        $clue1 = new Clue;
        $clue1->setY(1954);

        $clue2 = new Clue;
        $clue2->setM(5);
        $clue2->filter = Clue::FILTER_BEFOREEQUALS;

        $clue3 = new Clue;
        $clue3->setD(10);
        $clue3->filter = Clue::FILTER_BEFOREEQUALS;

        $this->sut->setClues([$clue1, $clue2, $clue3]);

        $periods = $this->sut->getPeriods();

        $this->assertCount(5, $periods);

        $period = $periods[0];
        $this->assertEquals(new DateTime('1954-01-01 00:00:00', $this->tz), $period->getStartDate());
        $this->assertEquals(
            new DateTime('1954-01-10 23:59:59', $this->tz),
            $period->getStartDate()->add($period->getDateInterval())
        );

        $period = $periods[1];
        $this->assertEquals(new DateTime('1954-02-01 00:00:00', $this->tz), $period->getStartDate());
        $this->assertEquals(
            new DateTime('1954-02-10 23:59:59', $this->tz),
            $period->getStartDate()->add($period->getDateInterval())
        );

        $period = $periods[2];
        $this->assertEquals(new DateTime('1954-03-01 00:00:00', $this->tz), $period->getStartDate());
        $this->assertEquals(
            new DateTime('1954-03-10 23:59:59', $this->tz),
            $period->getStartDate()->add($period->getDateInterval())
        );

        $period = $periods[3];
        $this->assertEquals(new DateTime('1954-04-01 00:00:00', $this->tz), $period->getStartDate());
        $this->assertEquals(
            new DateTime('1954-04-10 23:59:59', $this->tz),
            $period->getStartDate()->add($period->getDateInterval())
        );

        $period = $periods[4];
        $this->assertEquals(new DateTime('1954-05-01 00:00:00', $this->tz), $period->getStartDate());
        $this->assertEquals(
            new DateTime('1954-05-10 23:59:59', $this->tz),
            $period->getStartDate()->add($period->getDateInterval())
        );
    }

    public function testComplexBeforeAndAfter() : void
    {
        $clue1 = new Clue;
        $clue1->setY(1960);
        $clue1->filter = Clue::FILTER_WHITELIST;

        $clue2 = new Clue;
        $clue2->setM(2);
        $clue2->filter = Clue::FILTER_WHITELIST;

        $clue3 = new Clue;
        $clue3->setM(3);
        $clue3->filter = Clue::FILTER_WHITELIST;

        $clue4 = new Clue;
        $clue4->setD(28);
        $clue4->filter = Clue::FILTER_AFTEREQUALS;

        $clue5 = new Clue;
        $clue5->setD(3);
        $clue5->filter = Clue::FILTER_BEFOREEQUALS;

        $clue6 = new Clue;
        $clue6->setH(8);
        $clue6->filter = Clue::FILTER_AFTEREQUALS;

        $clue7 = new Clue;
        $clue7->setH(18);
        $clue7->filter = Clue::FILTER_BEFOREEQUALS;

        $this->sut->setClues([$clue1, $clue2, $clue3, $clue4, $clue5, $clue6, $clue7]);

        $actualPeriods = $this->sut->getPeriods();

        $this->assertCount(3, $actualPeriods);

        $this->assertEquals(new DateTime('1960-02-01 08:00:00', $this->tz), $actualPeriods[0]->getStartDate());
        $actualInterval = $actualPeriods[0]->getDateInterval();

        $this->assertEquals(0, $actualInterval->y);
        $this->assertEquals(0, $actualInterval->m);
        $this->assertEquals(2, $actualInterval->d);
        $this->assertEquals(10, $actualInterval->h, 'up until the 18. hour, incl all minutes, hence 10 h, not 9');
        $this->assertEquals(59, $actualInterval->i);
        $this->assertEquals(59, $actualInterval->s);

        $this->assertEquals(new DateTime('1960-02-28 08:00:00', $this->tz), $actualPeriods[1]->getStartDate());
        $actualInterval = $actualPeriods[1]->getDateInterval();
        $this->assertEquals(0, $actualInterval->y);
        $this->assertEquals(0, $actualInterval->m);
        $this->assertEquals(4, $actualInterval->d);
        $this->assertEquals(10, $actualInterval->h);
        $this->assertEquals(59, $actualInterval->i);
        $this->assertEquals(59, $actualInterval->s);

        $this->assertEquals(new DateTime('1960-03-28 08:00:00', $this->tz), $actualPeriods[2]->getStartDate());
        $actualInterval = $actualPeriods[2]->getDateInterval();
        $this->assertEquals(0, $actualInterval->y);
        $this->assertEquals(0, $actualInterval->m);
        $this->assertEquals(3, $actualInterval->d);
        $this->assertEquals(10, $actualInterval->h);
        $this->assertEquals(59, $actualInterval->i);
        $this->assertEquals(59, $actualInterval->s);
    }

    public function testAfterDayInMonth() : void
    {
        $this->markTestIncomplete();
        return;

        $clue1 = new Clue;
        $clue1->setY(2004);
        $clue1->filter = Clue::FILTER_WHITELIST;

        $clue2 = new Clue;
        $clue2->setM(2);
        $clue2->setD(29);
        $clue2->filter = Clue::FILTER_BEFOREEQUALS;

        $this->sut->setClues([$clue1, $clue2]);

        $actualPeriods = $this->sut->getPeriods();

        $this->assertCount(1, $actualPeriods);

        $this->assertEquals(new DateTime('2004-01-01 00:00:00', $this->tz), $actualPeriods[0]->getStartDate());
        $actualInterval = $actualPeriods[0]->getDateInterval();

        $this->assertEquals(0, $actualInterval->y);
        $this->assertEquals(1, $actualInterval->m);
        $this->assertEquals(28, $actualInterval->d);
        $this->assertEquals(23, $actualInterval->h);
        $this->assertEquals(59, $actualInterval->i);
        $this->assertEquals(59, $actualInterval->s);
    }

    public function testCompoundUnits() : void
    {
        $this->markTestIncomplete();
        return;

        $clue1 = new Clue;
        $clue1->setY(2007);
        $clue1->setM(10);
        $clue1->setD(30);

        $clue2 = new Clue;
        $clue2->setH(9);
        $clue2->setI(37);
        $clue2->setS(14);

        $this->assertEquals($this->sut, $this->sut->setClues([$clue1, $clue2]));

        $this->assertEquals(
            [
                new DatePeriod(new DateTime('2007-10-30 09:37:14', $this->tz), new DateInterval('PT0S'), 1),
            ],
            $this->sut->getPeriods()
        );
    }

    public function testWinterHolidayPicture() : void
    {
        $this->markTestIncomplete();
        return;

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
        $parser->addClue('Weekend'); // boo - needs extended calendar definition
        $parser->addClue('Summer'); // boo - needs geo-awareness


        $this->assertEquals($this->sut, $this->sut->setClues($parser->getProcessedClues()));
        $this->assertCount(6, $this->sut->getClues());
        $this->assertEquals(new DateTime('2016-02-05 00:00:00', $this->tz), $this->sut->getEarliest());
        $this->assertEquals(new DateTime('2016-13-31 23:59:59', $this->tz), $this->sut->getLatest());
    }

    public function testPayday() : void
    {
        $clue1 = new Clue;
        $clue1->setY(2007);

        $clue2 = new Clue;
        $clue2->setD(15);

        $this->assertEquals($this->sut, $this->sut->setClues([$clue1, $clue2]));
        $this->assertCount(12, $this->sut->getPeriods());
    }
}
