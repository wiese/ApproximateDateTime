<?php
declare(strict_types = 1);

namespace wiese\ApproximateDateTime\Tests;

use wiese\ApproximateDateTime\ApproximateDateTime;
use wiese\ApproximateDateTime\Clue;
use wiese\ApproximateDateTime\Clues;
use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

class ApproximateDateTimeTest extends TestCase
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

    public function testConstruct() : void
    {
        $tz = new DateTimeZone('Indian/Maldives');

        $sut = new ApproximateDateTime($tz, CAL_JEWISH);

        $this->assertEquals($tz, $sut->getTimezone());
        $this->assertEquals(CAL_JEWISH, $sut->getCalendar());
    }

    public function testDefault() : void
    {
        $this->assertEquals($this->tz, $this->sut->getTimezone());
        $this->assertEquals(CAL_GREGORIAN, $this->sut->getCalendar());

        $this->assertEquals(new DateTimeImmutable(date('Y') . '-01-01 00:00:00', $this->tz), $this->sut->getEarliest());
        $this->assertEquals(new DateTimeImmutable(date('Y') . '-12-31 23:59:59', $this->tz), $this->sut->getLatest());

        $this->sut->setDefaultYear(333);

        $this->assertEquals(new DateTimeImmutable('333-01-01 00:00:00', $this->tz), $this->sut->getEarliest());
        $this->assertEquals(new DateTimeImmutable('333-12-31 23:59:59', $this->tz), $this->sut->getLatest());

        $europeTz = new DateTimeZone('Europe/Berlin');
        $this->assertEquals($this->sut, $this->sut->setTimezone($europeTz));
        $this->assertEquals($europeTz, $this->sut->getTimezone());
        $this->assertEquals(new DateTimeImmutable('333-01-01 00:00:00', $europeTz), $this->sut->getEarliest());
        $this->assertEquals(new DateTimeImmutable('333-12-31 23:59:59', $europeTz), $this->sut->getLatest());
    }

    public function testReturnTypes() : void
    {
        $this->sut->setDefaultYear(1999);

        $periods = $this->sut->getPeriods();
        $this->assertInternalType('array', $periods);

        $period = $periods[0];
        $this->assertInstanceOf('DatePeriod', $period);

        foreach ($period as $day) {
            $this->assertInstanceOf('DateTimeImmutable', $day);
            break; // point made if we've done this once
        }

        $this->assertInstanceOf('DateTimeImmutable', $this->sut->getEarliest());
        $this->assertInstanceOf('DateTimeImmutable', $this->sut->getLatest());
    }

    public function testDefaultYearBlacklisted() : void
    {
        $clues = new Clues();

        $clues->setDefaultYear(77);

        $clue = new Clue(Clue::IS_BLACKLIST);
        $clue->setY(77);
        $clues->append($clue);

        $this->sut->setClues($clues);

        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage(
            'Tried applying the default year, but it is blacklisted. Please, whitelist a year.'
        );

        $this->sut->getPeriods();
    }

    public function testOneLeapYear() : void
    {
        // '2016-??-??T??-??-??'
        $clue = new Clue();
        $clue->setY(2016);

        $this->setClues([$clue]);
        $this->assertEquals(new DateTimeImmutable('2016-01-01 00:00:00', $this->tz), $this->sut->getEarliest());
        $this->assertEquals(new DateTimeImmutable('2016-12-31 23:59:59', $this->tz), $this->sut->getLatest());

        $this->assertTrue($this->sut->isPossible(new DateTimeImmutable('2016-04-03', $this->tz)));
        $this->assertTrue($this->sut->isPossible(new DateTimeImmutable('2016-02-29', $this->tz)));
        $this->assertFalse($this->sut->isPossible(new DateTimeImmutable('2017-01-02', $this->tz)));
        $this->assertFalse($this->sut->isPossible(new DateTimeImmutable('2015-12-31', $this->tz)));

        $actualPeriods = $this->sut->getPeriods();

        $this->assertCount(1, $actualPeriods);
        $this->assertEquals(new DateTimeImmutable('2016-01-01 00:00:00', $this->tz), $actualPeriods[0]->getStartDate());
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
        $clue1 = new Clue();
        $clue1->setY(1985);

        $clue2 = new Clue();
        $clue2->setY(1986);

        $this->setClues([$clue1, $clue2]);

        $this->assertEquals(new DateTimeImmutable('1985-01-01 00:00:00', $this->tz), $this->sut->getEarliest());
        $this->assertEquals(new DateTimeImmutable('1986-12-31 23:59:59', $this->tz), $this->sut->getLatest());

        $this->assertTrue($this->sut->isPossible(new DateTimeImmutable('1985-04-03', $this->tz)));
        $this->assertTrue($this->sut->isPossible(new DateTimeImmutable('1986-12-31', $this->tz)));
        $this->assertFalse($this->sut->isPossible(new DateTimeImmutable('1984-01-03', $this->tz)));
        $this->assertFalse($this->sut->isPossible(new DateTimeImmutable('1990-07-12', $this->tz)));

        $actualPeriods = $this->sut->getPeriods();
        $this->assertCount(1, $actualPeriods);
        $this->assertEquals(new DateTimeImmutable('1985-01-01 00:00:00', $this->tz), $actualPeriods[0]->getStartDate());
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
        $clue1 = new Clue();
        $clue1->setY(2001);

        $clue2 = new Clue();
        $clue2->setM(3);

        $clue3 = new Clue();
        $clue3->setM(4);

        $this->setClues([$clue1, $clue2, $clue3]);

        $this->assertEquals(new DateTimeImmutable('2001-03-01 00:00:00', $this->tz), $this->sut->getEarliest());
        $this->assertEquals(new DateTimeImmutable('2001-04-30 23:59:59', $this->tz), $this->sut->getLatest());

        $actualPeriods = $this->sut->getPeriods();
        $this->assertCount(1, $actualPeriods);
        $this->assertEquals(new DateTimeImmutable('2001-03-01 00:00:00', $this->tz), $actualPeriods[0]->getStartDate());
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
        $clue1 = new Clue();
        $clue1->setY(2010);

        $clue2 = new Clue();
        $clue2->setM(3);

        $clue3 = new Clue();
        $clue3->setD(28);

        $clue4 = new Clue();
        $clue4->setD(30);

        $this->setClues([$clue1, $clue2, $clue3, $clue4]);

        $this->assertEquals(new DateTimeImmutable('2010-03-28 00:00:00', $this->tz), $this->sut->getEarliest());
        $this->assertEquals(new DateTimeImmutable('2010-03-30 23:59:59', $this->tz), $this->sut->getLatest());

        $this->assertEquals(
            [
                new DatePeriod(
                    new DateTimeImmutable('2010-03-28 00:00:00', $this->tz),
                    new DateInterval('PT23H59M59S'),
                    new DateTimeImmutable('2010-03-28 23:59:59', $this->tz)
                ),
                new DatePeriod(
                    new DateTimeImmutable('2010-03-30 00:00:00', $this->tz),
                    new DateInterval('PT23H59M59S'),
                    new DateTimeImmutable('2010-03-30 23:59:59', $this->tz)
                )
            ],
            $this->sut->getPeriods()
        );
    }

    public function testRealworldExampleConsecutiveMonth() : void
    {
        $clue1 = new Clue();
        $clue1->setY(2010);

        $clue2 = new Clue();
        $clue2->setM(3);

        $clue3 = new Clue();
        $clue3->setM(4);

        $clue4 = new Clue();
        $clue4->setD(28);

        $clue5 = new Clue();
        $clue5->setD(30);

        $this->setClues([$clue1, $clue2, $clue3, $clue4, $clue5]);

        $this->assertEquals(
            [
                new DatePeriod(
                    new DateTimeImmutable('2010-03-28 00:00:00', $this->tz),
                    new DateInterval('PT23H59M59S'),
                    new DateTimeImmutable('2010-03-28 23:59:59', $this->tz)
                ),
                new DatePeriod(
                    new DateTimeImmutable('2010-03-30 00:00:00', $this->tz),
                    new DateInterval('PT23H59M59S'),
                    new DateTimeImmutable('2010-03-30 23:59:59', $this->tz)
                ),
                new DatePeriod(
                    new DateTimeImmutable('2010-04-28 00:00:00', $this->tz),
                    new DateInterval('PT23H59M59S'),
                    new DateTimeImmutable('2010-04-28 23:59:59', $this->tz)
                ),
                new DatePeriod(
                    new DateTime('2010-04-30 00:00:00', $this->tz),
                    new DateInterval('PT23H59M59S'),
                    new DateTime('2010-04-30 23:59:59', $this->tz)
                ),
            ],
            $this->sut->getPeriods()
        );
    }

    public function testNotSoApproximate() : void
    {
        // '1985-01-23T07-11-32'
        $clue1 = new Clue();
        $clue1->setY(1985);

        $clue2 = new Clue();
        $clue2->setM(1);

        $clue3 = new Clue();
        $clue3->setD(23);

        $clue4 = new Clue();
        $clue4->setH(7);

        $clue5 = new Clue();
        $clue5->setI(11);

        $clue6 = new Clue();
        $clue6->setS(32);

        $this->setClues([$clue1, $clue2, $clue3, $clue4, $clue5, $clue6]);

        $this->assertEquals(new DateTimeImmutable('1985-01-23 07:11:32', $this->tz), $this->sut->getEarliest());
        $this->assertEquals(new DateTimeImmutable('1985-01-23 07:11:32', $this->tz), $this->sut->getLatest());

        $this->assertEquals(
            [
                new DatePeriod(
                    new DateTimeImmutable('1985-01-23 07:11:32', $this->tz),
                    new DateInterval('PT0S'),
                    new DateTimeImmutable('1985-01-23 07:11:32', $this->tz)
                )
            ],
            $this->sut->getPeriods()
        );
    }

    public function testMiniExample() : void
    {
        $clue1 = new Clue();
        $clue1->setY(2007);

        $clue2 = new Clue();
        $clue2->setM(8);

        $clue3 = new Clue();
        $clue3->setD(15);

        $clue4 = new Clue();
        $clue4->setH(9);

        $clue5 = new Clue();
        $clue5->setI(36);

        $clue6 = new Clue();
        $clue6->setI(34);

        $clue7 = new Clue();
        $clue7->setI(39);

        $this->setClues([$clue1, $clue2, $clue3, $clue4, $clue5, $clue6, $clue7]);

        $this->assertEquals(
            [
                new DatePeriod(
                    new DateTimeImmutable('2007-08-15 09:34:00', $this->tz),
                    new DateInterval('PT59S'),
                    new DateTimeImmutable('2007-08-15 09:34:59', $this->tz)
                ),
                new DatePeriod(
                    new DateTimeImmutable('2007-08-15 09:36:00', $this->tz),
                    new DateInterval('PT59S'),
                    new DateTimeImmutable('2007-08-15 09:36:59', $this->tz)
                ),
                new DatePeriod(
                    new DateTimeImmutable('2007-08-15 09:39:00', $this->tz),
                    new DateInterval('PT59S'),
                    new DateTimeImmutable('2007-08-15 09:39:59', $this->tz)
                ),
            ],
            $this->sut->getPeriods()
        );
    }

    public function testMiniExampleWithDefaultYear() : void
    {
        $clue1 = new Clue();
        $clue1->setM(7);

        $this->setClues([$clue1]);

        $actualPeriods = $this->sut->getPeriods();
        $this->assertCount(1, $actualPeriods);
        $this->assertEquals(
            new DateTimeImmutable(date('Y') . '-07-01 00:00:00', $this->tz),
            $actualPeriods[0]->getStartDate()
        );
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
        $clue1 = new Clue();
        $clue1->setY(2012);

        $clue2 = new Clue();
        $clue2->setM(7);

        $clue3 = new Clue(Clue::IS_BLACKLIST);
        $clue3->setD(1);

        $this->setClues([$clue1, $clue2, $clue3]);

        $this->assertEquals(new DateTimeImmutable('2012-07-02 00:00:00', $this->tz), $this->sut->getEarliest());
        $this->assertEquals(new DateTimeImmutable('2012-07-31 23:59:59', $this->tz), $this->sut->getLatest());

        $this->assertCount(1, $this->sut->getPeriods());
    }

    public function testComplexBlacklist() : void
    {
        $clue1 = new Clue();
        $clue1->setY(2018);

        $clue2 = new Clue();
        $clue2->setM(7);

        $clue3 = new Clue();
        $clue3->setM(8);

        $clue4 = new Clue();
        $clue4->setM(9);

        $clue5 = new Clue(Clue::IS_BLACKLIST);
        $clue5->setM(8);

        $clue6 = new Clue(Clue::IS_BLACKLIST);
        $clue6->setD(1);

        $clue7 = new Clue(Clue::IS_BLACKLIST);
        $clue7->setD(30);

        $this->setClues([$clue1, $clue2, $clue3, $clue4, $clue5, $clue6, $clue7]);

        $periods = $this->sut->getPeriods();

        $this->assertCount(3, $periods);
        $this->assertEquals(new DateTimeImmutable('2018-07-02 00:00:00', $this->tz), $periods[0]->getStartDate());
        $this->assertEquals(new DateTimeImmutable('2018-07-31 00:00:00', $this->tz), $periods[1]->getStartDate());
        $this->assertEquals(new DateTimeImmutable('2018-09-02 00:00:00', $this->tz), $periods[2]->getStartDate());
    }

    public function testWeekday() : void
    {
        $clue1 = new Clue();
        $clue1->setY(2017);

        $clue2 = new Clue();
        $clue2->setM(10);

        $clue3 = new Clue(Clue::IS_BLACKLIST);
        $clue3->setD(14);

        $clue4 = new Clue();
        $clue4->setN(6);

        $this->setClues([$clue1, $clue2, $clue3, $clue4]);

        $periods = $this->sut->getPeriods();

        $this->assertCount(3, $periods);
        $this->assertEquals(new DateTimeImmutable('2017-10-07 00:00:00', $this->tz), $periods[0]->getStartDate());
        $this->assertEquals(new DateTimeImmutable('2017-10-21 00:00:00', $this->tz), $periods[1]->getStartDate());
        $this->assertEquals(new DateTimeImmutable('2017-10-28 00:00:00', $this->tz), $periods[2]->getStartDate());
    }

    public function testWorkday() : void
    {
        $clue1 = new Clue();
        $clue1->setY(2001);

        $clue2 = new Clue(Clue::IS_BLACKLIST);
        $clue2->setM(10);

        $clue3 = new Clue(Clue::IS_BLACKLIST);
        $clue3->setN(6);

        $clue4 = new Clue(Clue::IS_BLACKLIST);
        $clue4->setN(7);

        $this->setClues([$clue1, $clue2, $clue3, $clue4]);
        $periods = $this->sut->getPeriods();

        $this->assertCount(49, $periods);
        $this->assertEquals(new DateTimeImmutable('2001-01-01 00:00:00', $this->tz), $this->sut->getEarliest());
        $this->assertEquals(new DateTimeImmutable('2001-12-31 23:59:59', $this->tz), $this->sut->getLatest());
    }

    public function testTrickyWeekdays() : void
    {
        $clue1 = new Clue();
        $clue1->setY(2001);

        $clue2 = new Clue();
        $clue2->setM(3);

        $clue3 = new Clue();
        $clue3->setN(5);

        $clue4 = new Clue();
        $clue4->setN(6);

        $clue5 = new Clue();
        $clue5->setN(7);

        $this->setClues([$clue1, $clue2, $clue3, $clue4, $clue5]);
        $periods = $this->sut->getPeriods();

        $this->assertCount(5, $periods);

        // @todo DatePeriod wrapper with direct access to end, calculated from start and interval?

        $period = $periods[0];

        $this->assertEquals(new DateTimeImmutable('2001-03-02 00:00:00', $this->tz), $period->getStartDate());
        $this->assertEquals(
            new DateTimeImmutable('2001-03-04 23:59:59', $this->tz),
            $period->getStartDate()->add($period->getDateInterval())
        );

        $period = $periods[2];
        $this->assertEquals(new DateTimeImmutable('2001-03-16 00:00:00', $this->tz), $period->getStartDate());
        $this->assertEquals(
            new DateTimeImmutable('2001-03-18 23:59:59', $this->tz),
            $period->getStartDate()->add($period->getDateInterval())
        );

        $period = $periods[4];
        $this->assertEquals(new DateTimeImmutable('2001-03-30 00:00:00', $this->tz), $period->getStartDate());
        $this->assertEquals(
            new DateTimeImmutable('2001-03-31 23:59:59', $this->tz),
            $period->getStartDate()->add($period->getDateInterval())
        );
    }

    public function testGenerousWhitelistStillOneRange() : void
    {
        $clues = [];

        $clue = new Clue();
        $clue->setY(2016);
        $clues[] = $clue;

        foreach (range(1, 12) as $month) {
            $clue = new Clue();
            $clue->setM($month);
            $clues[] = $clue;
        }

        $this->setClues($clues);
        $actualPeriods = $this->sut->getPeriods();

        $this->assertCount(1, $actualPeriods, 'after month whitelist');

        foreach (range(1, 31) as $day) {
            $clue = new Clue();
            $clue->setD($day);
            $clues[] = $clue;
        }

        $this->setClues($clues);
        $actualPeriods = $this->sut->getPeriods();
        $this->assertCount(1, $actualPeriods, 'after day whitelist');

        foreach (range(1, 7) as $weekday) {
            $clue = new Clue();
            $clue->setN($weekday);
            $clues[] = $clue;
        }

        $this->setClues($clues);

        $actualPeriods = $this->sut->getPeriods();
        $this->assertCount(1, $actualPeriods, 'after weekday whitelist');

        $this->assertEquals(new DateTimeImmutable('2016-01-01 00:00:00', $this->tz), $actualPeriods[0]->getStartDate());
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
        $clue1 = new Clue();
        $clue1->setY(1954);

        $clue2 = new Clue(Clue::IS_BEFOREEQUALS);
        $clue2->setM(5);

        $clue3 = new Clue(Clue::IS_BEFOREEQUALS);
        $clue3->setD(10);

        $this->setClues([$clue1, $clue2, $clue3]);

        $periods = $this->sut->getPeriods();

        $this->assertCount(5, $periods);

        $period = $periods[0];
        $this->assertEquals(new DateTimeImmutable('1954-01-01 00:00:00', $this->tz), $period->getStartDate());
        $this->assertEquals(
            new DateTimeImmutable('1954-01-10 23:59:59', $this->tz),
            $period->getStartDate()->add($period->getDateInterval())
        );

        $period = $periods[1];
        $this->assertEquals(new DateTimeImmutable('1954-02-01 00:00:00', $this->tz), $period->getStartDate());
        $this->assertEquals(
            new DateTimeImmutable('1954-02-10 23:59:59', $this->tz),
            $period->getStartDate()->add($period->getDateInterval())
        );

        $period = $periods[2];
        $this->assertEquals(new DateTimeImmutable('1954-03-01 00:00:00', $this->tz), $period->getStartDate());
        $this->assertEquals(
            new DateTimeImmutable('1954-03-10 23:59:59', $this->tz),
            $period->getStartDate()->add($period->getDateInterval())
        );

        $period = $periods[3];
        $this->assertEquals(new DateTimeImmutable('1954-04-01 00:00:00', $this->tz), $period->getStartDate());
        $this->assertEquals(
            new DateTimeImmutable('1954-04-10 23:59:59', $this->tz),
            $period->getStartDate()->add($period->getDateInterval())
        );

        $period = $periods[4];
        $this->assertEquals(new DateTimeImmutable('1954-05-01 00:00:00', $this->tz), $period->getStartDate());
        $this->assertEquals(
            new DateTimeImmutable('1954-05-10 23:59:59', $this->tz),
            $period->getStartDate()->add($period->getDateInterval())
        );
    }

    public function testComplexBeforeAndAfter() : void
    {
        $clue1 = new Clue();
        $clue1->setY(1960);

        $clue2 = new Clue();
        $clue2->setM(2);

        $clue3 = new Clue();
        $clue3->setM(3);

        $clue4 = new Clue(Clue::IS_AFTEREQUALS);
        $clue4->setD(28);

        $clue5 = new Clue(Clue::IS_BEFOREEQUALS);
        $clue5->setD(3);

        $clue6 = new Clue(Clue::IS_AFTEREQUALS);
        $clue6->setH(8);

        $clue7 = new Clue(Clue::IS_BEFOREEQUALS);
        $clue7->setH(18);

        $this->setClues([$clue1, $clue2, $clue3, $clue4, $clue5, $clue6, $clue7]);

        $actualPeriods = $this->sut->getPeriods();

        $this->assertCount(3, $actualPeriods);

        $this->assertEquals(new DateTimeImmutable('1960-02-01 08:00:00', $this->tz), $actualPeriods[0]->getStartDate());
        $actualInterval = $actualPeriods[0]->getDateInterval();

        $this->assertEquals(0, $actualInterval->y);
        $this->assertEquals(0, $actualInterval->m);
        $this->assertEquals(2, $actualInterval->d);
        $this->assertEquals(10, $actualInterval->h, 'up until the 18. hour, incl all minutes, hence 10 h, not 9');
        $this->assertEquals(59, $actualInterval->i);
        $this->assertEquals(59, $actualInterval->s);

        $this->assertEquals(new DateTimeImmutable('1960-02-28 08:00:00', $this->tz), $actualPeriods[1]->getStartDate());
        $actualInterval = $actualPeriods[1]->getDateInterval();
        $this->assertEquals(0, $actualInterval->y);
        $this->assertEquals(0, $actualInterval->m);
        $this->assertEquals(4, $actualInterval->d);
        $this->assertEquals(10, $actualInterval->h);
        $this->assertEquals(59, $actualInterval->i);
        $this->assertEquals(59, $actualInterval->s);

        $this->assertEquals(new DateTimeImmutable('1960-03-28 08:00:00', $this->tz), $actualPeriods[2]->getStartDate());
        $actualInterval = $actualPeriods[2]->getDateInterval();
        $this->assertEquals(0, $actualInterval->y);
        $this->assertEquals(0, $actualInterval->m);
        $this->assertEquals(3, $actualInterval->d);
        $this->assertEquals(10, $actualInterval->h);
        $this->assertEquals(59, $actualInterval->i);
        $this->assertEquals(59, $actualInterval->s);
    }

    public function testPayday() : void
    {
        $clue1 = new Clue();
        $clue1->setY(2007);

        $clue2 = new Clue();
        $clue2->setD(15);

        $this->setClues([$clue1, $clue2]);
        $this->assertCount(12, $this->sut->getPeriods());
    }

    public function testMultipleClueValuesNotAllowed() : void
    {
        $clue1 = new Clue();
        $clue1->setY(2007);
        $clue1->setD(27);

        $this->setClues([$clue1]);

        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage('Clues can only carry one piece of information. Given: y, d');

        $this->sut->getPeriods();
    }

    public function testIsPossible() : void
    {
        $clue1 = new Clue();
        $clue1->setY(2005);

        $clue2 = new Clue(Clue::IS_BLACKLIST);
        $clue2->setM(6);

        $this->setClues([$clue1, $clue2]);

        $this->assertTrue($this->sut->isPossible(new DateTimeImmutable('2005-05-31 23:59:59', $this->tz)));
        $this->assertFalse($this->sut->isPossible(new DateTimeImmutable('2005-06-01 00:00:00', $this->tz)));
        $this->assertFalse($this->sut->isPossible(new DateTimeImmutable('2005-06-15 12:13:14', $this->tz)));
        $this->assertFalse($this->sut->isPossible(new DateTimeImmutable('2005-06-30 23:59:59', $this->tz)));
        $this->assertTrue($this->sut->isPossible(new DateTimeImmutable('2005-07-01 00:00:00', $this->tz)));
        $continentalTime = new DateTimeZone('Europe/Berlin');
        $this->assertFalse($this->sut->isPossible(new DateTimeImmutable('2005-07-01 00:30:00', $continentalTime)));
    }

    /**
     * Utility function to set Clues on ApproximateDateTime
     *
     * @param Clue[] $clues
     */
    protected function setClues(array $clues) : void
    {
        $cluesObject = new Clues;
        foreach ($clues as $clue) {
            $cluesObject->append($clue);
        }
        $this->sut->setClues($cluesObject);
    }
}
